<?php

namespace App\Core;

use App\Connectors\LetsPeppol\LetsPeppolConnector;
use App\Connectors\Prejournal\PrejournalConnector;
use App\Connectors\Teamwork\TeamworkConnector;
use App\Core\DataType\DataTypeManager;
use App\Jobs\TaskProcess;
use App\Models\Authentication;
use App\Models\AuthFunction;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class ApplicationManager
{
    public static function getConnectors()
    {
        // Read from an static array
        return [new LetsPeppolConnector, new PrejournalConnector(), new TeamworkConnector()];
    }

    public static function getConnector($code_name): ?Connector
    {
        $connectors = ApplicationManager::getConnectors();

        foreach ($connectors as $connector) {
            if ($connector->getAppCodeName() == $code_name) {
                return $connector;
            }
        }

        return null;
    }

    public static function finalizeAuthentication($request, $app_code_name)
    {
        $connector = ApplicationManager::getConnector($app_code_name);

        if ($connector !== null) {
            $auth_info = $connector->finalizeAuthentication($request);

            // store auth object
            if ($auth_info == null) {
                return 'Authentication failed!';
            }

            return self::createAuthentication($auth_info);
        }

        return 'ERROR: App not found!';
    }

    public static function createAuthentication(AuthInfo $auth_info)
    {
        $has_user = Auth::check();

        $auths = Authentication::query()
                ->where('app_user_id', $auth_info->app_user_id)
                ->where('app_code_name', $auth_info->app_code_name)
                ->get();

        $matching_auth = null;

        foreach ($auths as $auth) {
            if ($connector->areTheSame($auth->getModel(), $auth_info)) {
                $matching_auth = $auth;
                break;
            }
        }

        if ($has_user) {
            $user = Auth::user();

            if ($matching_auth != null) {
                if ($user['id'] == $matching_auth['user_id']) {
                    // Using pointing to the same data source again? Let's just update the auth we have.
                    $matching_auth['display_name'] = $auth_info->display_name;
                    $matching_auth['metadata'] = $auth_info->metadata;

                    $matching_auth->update();

                    return 'Auth updated!';
                } else {
                    // TODO Merge this user with the other user.
                    return 'To be merged!';
                }
            } else {
                $auth = $auth_info->asAuthentication($user['id']);

                if ($auth->save()) {
                    return 'New auth added!';
                } else {
                    return 'Failed to add new auth.';
                }
            }
        } else {
            if ($matching_auth == null) {
                // Create a new user
                $user = new User();
                $user['name'] = $auth_info->display_name;

                if ($user->save()) {
                    $user_id = $user['id'];

                    $auth = $auth_info->asAuthentication($user_id);

                    if ($auth->save()) {
                        Auth::login($user, $remember = false);
                        //$request->session()->regenerate();

                        return 'success: new user!';
                    } else {
                        return 'Failed to save the auth after user created.';
                    }
                } else {
                    return 'Saving user in db failed!';
                }
            } else {
                // We already have a user. Authenticate them.
                $matching_auth['display_name'] = $auth_info->display_name;
                $matching_auth['metadata'] = $auth_info->metadata;

                $matching_auth->update();

                if (Auth::loginUsingId($matching_auth['user_id'], $remember = false) != null) {
                    //$request->session()->regenerate();

                    return 'success: returning user!';
                } else {
                    return 'Failed to login using user id.';
                }
            }
        }
    }

    public static function getAuthentications(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $authentications = Authentication::query()
                ->where('user_id', $user['id'])
                ->get();

        return $authentications->all();
    }

    public static function getAuthentication($auth_id): ?Authentication
    {
        return Authentication::query()->where('id', $auth_id)->first();
    }

    public static function getFunction($auth_id, $data_type): ?AuthFunction
    {
        $function = AuthFunction::query()
                ->where('auth_id', $auth_id)
                ->where('data_type', $data_type)
                ->first();

        if ($function) {
            return $function;
        }

        return new AuthFunction(['auth_id' => $auth_id, 'data_type' => $data_type, 'read' => false, 'write' => false]);
    }

    public static function getWriteAuthentications($data_type, $except): array
    {
        $auth_ids = AuthFunction::query()
                ->where('data_type', $data_type)
                ->where('write', true)
                ->where('auth_id', '!=', $except['id'])
                ->pluck('auth_id')
                ->all();

        $authentications = Authentication::query()->find($auth_ids)->all();

        return $authentications;
    }

    public static function readOn($auth_id, $data_type)
    {
        return ApplicationManager::readToggle($auth_id, $data_type, true);
    }

    public static function readOff($auth_id, $data_type)
    {
        return ApplicationManager::readToggle($auth_id, $data_type, false);
    }

    public static function readToggle($auth_id, $data_type, $read)
    {
        $auth = ApplicationManager::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $connector = ApplicationManager::getConnector($auth->app_code_name);

        if ($connector === null) {
            return 'ERROR: Connector not found!';
        }

        $function = AuthFunction::query()
                ->where('auth_id', $auth_id)
                ->where('data_type', $data_type)
                ->first();

        if ($function == null) {
            $function = new AuthFunction();
            $function['auth_id'] = $auth_id;
            $function['data_type'] = $data_type;
            $function['write'] = false;
        } elseif ($function['read'] == $read) {
            return 'Read already in the desired state!';
        }

        $function['read'] = $read;

        if ($function->save()) {
            if ($read) {
                if ($connector->registerUpdateNotifier($auth, $data_type)) {
                    return 'success!';
                } else {
                    $function['read'] = false;
                    $function->save();
                    // TODO state might get lost here.

                    return 'Registering update notifier failed!';
                }
            } else {
                if ($connector->unregisterUpdateNotifier($auth, $data_type)) {
                    return 'success!';
                } else {
                    $function['read'] = true;
                    $function->save();
                    // TODO state might get lost here.

                    return 'Unregistering update notifier failed!';
                }
            }
        } else {
            return 'Failed to save/update function!';
        }
    }

    public static function writeOn($auth_id, $data_type)
    {
        return ApplicationManager::writeToggle($auth_id, $data_type, true);
    }

    public static function writeOff($auth_id, $data_type)
    {
        return ApplicationManager::writeToggle($auth_id, $data_type, false);
    }

    public static function writeToggle($auth_id, $data_type, $write)
    {
        error_log('write toggle '.$auth_id.' '.$write);
        $auth = ApplicationManager::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $connector = ApplicationManager::getConnector($auth->app_code_name);

        if ($connector === null) {
            return 'ERROR: Connector not found!';
        }

        $function = AuthFunction::query()
                ->where('auth_id', $auth_id)
                ->where('data_type', $data_type)
                ->first();

        if ($function == null) {
            $function = new AuthFunction();
            $function['auth_id'] = $auth_id;
            $function['data_type'] = $data_type;
            $function['read'] = false;
        } elseif ($function['write'] == $write) {
            return 'Write already in the desired state!';
        }

        $function['write'] = $write;

        if ($function->save()) {
            return 'success!';
        } else {
            return 'Failed to save/update function!';
        }
    }

    public static function onNewUpdate($auth, $data_type)
    {
        $write_auths = ApplicationManager::getWriteAuthentications($data_type, $auth);

        foreach ($write_auths as $write_auth) {
            $task = new Task(['from_auth' => $auth, 'to_auth' => $write_auth, 'data_type' => $data_type]);
            TaskProcess::dispatch($task);
        }
    }

    public static function test($success)
    {
        $from = ApplicationManager::getAuthentication(1);
        $to = ApplicationManager::getAuthentication(2);

        foreach ($success as $s) {
            $task = new Task(['from_auth' => $from, 'to_auth' => $to, 'data_type' => 'timesheet', 'behavior' => $s]);
            TaskProcess::dispatch($task);
        }
    }

    public static function taskHandler(Task $task)
    {
        $src_connector = ApplicationManager::getConnector($task->from_auth->app_code_name);
        $dst_connector = ApplicationManager::getConnector($task->to_auth->app_code_name);

        $src_reader = $src_connector->getReader($task->from_auth, $task->data_type);
        $dst_reader = $dst_connector->getReader($task->to_auth, $task->data_type);
        $writer = $dst_connector->getWriter($task->to_auth, $task->data_type);

        // In the future, we should support custom implementations.
        $change_interpreter = DataTypeManager::getChangeInterpreter($task->data_type);

        $changes = $change_interpreter->getStateChanges($src_reader, $dst_reader, $task->create_time);

        if ($task->behavior == 0) {
            throw new Exception('Manual fail!');
        }

        $writer->applyStateChanges($changes);
    }
}
