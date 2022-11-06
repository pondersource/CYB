<?php

namespace App\Core;

use Illuminate\Support\Facades\Auth;

use App\Applications\Prejournal\PrejournalAuthenticationAdapter;
use App\Applications\Teamwork\TeamworkAuthenticationAdapter;
use App\Core\DataType\DataTypeManager;
use App\Models\Task;
use App\Models\Authentication;
use App\Models\AuthFunction;
use App\Models\User;
use App\Jobs\TaskProcess;


class ApplicationManager {

    public static function getApplications() {
        // Read from an static array
        return [ new PrejournalAuthenticationAdapter(), new TeamworkAuthenticationAdapter() ];
    }

    public static function getApplication($code_name): ?AuthenticationAdapter {
        $apps = ApplicationManager::getApplications();

        foreach($apps as $app) {
            if ($app->getAppCodeName() == $code_name) {
                return $app;
            }
        }

        return null;
    }

    public static function finalizeAuthentication($request, $app_code_name) {
        $app = ApplicationManager::getApplication($app_code_name);

        if ($app !== null) {
            $auth_info = $app->finalizeAuthentication();

            // TODO store auth object
            if ($auth_info == null) {
                return 'Authentication failed!';
            }

            $has_user = Auth::check();

            $auths = Authentication::query()
                    ->where('app_user_id', $auth_info->app_user_id)
                    ->where('app_code_name', $auth_info->app_code_name)
                    ->get();

            $matching_auth = null;

            foreach ($auths as $auth) {
                if ($app->areTheSame($auth->getModel(), $auth_info)) {
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
                    }
                    else {
                        // TODO Merge this user with the other user.
                        return 'To be merged!';
                    }
                }
                else {
                    $auth = $auth_info->asAuthentication($user['id']);

                    if ($auth->save()) {
                        return 'New auth added!';
                    }
                    else {
                        return 'Failed to add new auth.';
                    }
                }
            }
            else {
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
                        }
                        else {
                            return 'Failed to save the auth after user created.';
                        }
                    }
                    else {
                        return 'Saving user in db failed!';
                    }
                }
                else {
                    // We already have a user. Authenticate them.
                    $matching_auth['display_name'] = $auth_info->display_name;
                    $matching_auth['metadata'] = $auth_info->metadata;

                    $matching_auth->update();

                    if (Auth::loginUsingId($matching_auth['user_id'], $remember = false) != null) {
                        //$request->session()->regenerate();

                        return 'success: returning user!';
                    }
                    else {
                        return 'Failed to login using user id.';
                    }
                }
            }
        }

        return 'ERROR: App not found!';
    }

    public static function getAuthentications(): array {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $authentications = Authentication::query()
                ->where('user_id', $user['id'])
                ->get();

        return $authentications->all();
    }

    public static function getAuthentication($auth_id): ?Authentication {
        return Authentication::query()->where('id', $auth_id)->first();
    }

    public static function getFunction($auth_id, $data_type): ?AuthFunction {
        $function = AuthFunction::query()
                ->where('auth_id', $auth_id)
                ->where('data_type', $data_type)
                ->first();

        if ($function) {
            return $function;
        }

        return new AuthFunction(['auth_id'=>$auth_id, 'data_type'=>$data_type, 'read'=>false, 'write'=>false]);
    }

    public static function getWriteAuthentications($data_type, $except): array {
        $auth_ids = AuthFunction::query()
                ->where('data_type', $data_type)
                ->where('write', true)
                ->where('auth_id', '!=', $except['id'])
                ->pluck('auth_id')
                ->all();

        $authentications = Authentication::query()->find($auth_ids)->all();

        return $authentications;
    }

    public static function readOn($auth_id, $data_type) {
        return ApplicationManager::readToggle($auth_id, $data_type, true);
    }

    public static function readOff($auth_id, $data_type) {
        return ApplicationManager::readToggle($auth_id, $data_type, false);
    }

    public static function readToggle($auth_id, $data_type, $read) {
        $auth = ApplicationManager::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $app = ApplicationManager::getApplication($auth->app_code_name);

        if ($app === null) {
            return 'ERROR: App not found!';
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
        }
        else if ($function['read'] == $read) {
            return 'Read already in the desired state!';
        }

        $function['read'] = $read;

        if ($function->save()) {
            if ($read) {
                if ($app->registerUpdateNotifier($auth, $data_type)) {
                    return 'success!';
                }
                else {
                    $function['read'] = false;
                    $function->save();
                    // TODO state might get lost here.

                    return 'Registering update notifier failed!';
                }
            }
            else {
                if ($app->unregisterUpdateNotifier($auth, $data_type)) {
                    return 'success!';
                }
                else {
                    $function['read'] = true;
                    $function->save();
                    // TODO state might get lost here.

                    return 'Unregistering update notifier failed!';
                }
            }
        }
        else {
            return 'Failed to save/update function!';
        }
    }

    public static function writeOn($auth_id, $data_type) {
        return ApplicationManager::writeToggle($auth_id, $data_type, true);
    }

    public static function writeOff($auth_id, $data_type) {
        return ApplicationManager::writeToggle($auth_id, $data_type, false);
    }

    public static function writeToggle($auth_id, $data_type, $write) {
        error_log('write toggle '.$auth_id. ' '.$write);
        $auth = ApplicationManager::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $app = ApplicationManager::getApplication($auth->app_code_name);

        if ($app === null) {
            return 'ERROR: App not found!';
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
        }
        else if ($function['write'] == $write) {
            return 'Write already in the desired state!';
        }

        $function['write'] = $write;

        if ($function->save()) {
            return 'success!';
        }
        else {
            return 'Failed to save/update function!';
        }
    }

    public static function onNewUpdate($auth, $data_type) {
        $write_auths = ApplicationManager::getWriteAuthentications($data_type, $auth);

        foreach ($write_auths as $write_auth) {
            $task = new Task(['from_auth' => $auth, 'to_auth' => $write_auth, 'data_type' => $data_type]);
            TaskProcess::dispatch($task);
        }
    }

    public static function taskHandler(Task $task) {
        $src_app = ApplicationManager::getApplication($task->from_auth->app_code_name);
        $dst_app = ApplicationManager::getApplication($task->to_auth->app_code_name);

        $src_reader = $src_app->getReader($task->from_auth, $task->data_type);
        $dst_reader = $dst_app->getReader($task->to_auth, $task->data_type);
        $writer = $dst_app->getWriter($task->to_auth, $task->data_type);

        // In the future, we should support custom implementations.
        $change_interpreter = DataTypeManager::getChangeInterpreter($task->data_type);

        $changes = $change_interpreter->getStateChanges($src_reader, $dst_reader);
        $writer->applyStateChanges($changes);
    }
}
