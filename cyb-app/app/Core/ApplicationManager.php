<?php

namespace App\Core;

use Illuminate\Support\Facades\Auth;

use App\Applications\Prejournal\PrejournalAuthenticationAdapter;
use App\Applications\Teamwork\TeamworkAuthenticationAdapter;
use App\Core\AuthFunction;
use App\Core\DataType\DataTypeManager;
use App\Core\Task;
use App\Models\Authentication;
use App\Models\User;

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

            $has_user = Auth::hasUser();

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
                        
                        $matching_auth->save();

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
                            $request->session()->regenerate();
    
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
                    
                    $matching_auth->save();

                    if (Auth::loginUsingId($matching_auth['user_id'], $remember = false) != null) {
                        $request->session()->regenerate();

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

    public static function getAuthentications() {
        // TODO Query from authentication table
        return [
            new Authentication(['id'=>1, 'app_code_name'=>'teamwork', 'display_name'=>'Ismoil', 'user_id'=>1, 'metadata'=>null]),
            new Authentication(['id'=>2, 'app_code_name'=>'prejournal', 'display_name'=>'Ismoil', 'user_id'=>1, 'metadata'=>null])
        ];
    }

    public static function getAuthentication($auth_id) {
        // TODO Query from authentication table
        if ($auth_id == 1) {
            return new Authentication(['id'=>1, 'app_code_name'=>'teamwork', 'display_name'=>'Ismoil', 'user_id'=>1, 'metadata'=>null]);
        }
        else {
            return new Authentication(['id'=>2, 'app_code_name'=>'prejournal', 'display_name'=>'Ismoil', 'user_id'=>1, 'metadata'=>null]);
        }
    }

    public static function getFunction($auth_id, $data_type) {
        // TODO Query from auth function table
        if ($data_type != 'timesheet') {
            return new AuthFunction(['id'=>0, 'auth_id'=>$auth_id, 'data_type'=>$data_type, 'read'=>false, 'write'=>false]);
        }

        if ($auth_id == 1) {
            return new AuthFunction(['id'=>1, 'auth_id'=>1, 'data_type'=>$data_type, 'read'=>false, 'write'=>false]);
        }

        if ($auth_id == 2) {
            return new AuthFunction(['id'=>2, 'auth_id'=>2, 'data_type'=>$data_type, 'read'=>false, 'write'=>true]);
        }

        return new AuthFunction(['id'=>0, 'auth_id'=>$auth_id, 'data_type'=>$data_type, 'read'=>false, 'write'=>false]);
    }

    public static function getWriteAuthentications($data_type, $except) {
        // TODO Query from authentication table
        return [ new Authentication(['id'=>2, 'app_code_name'=>'prejournal', 'display_name'=>'Ismoil', 'user_id'=>1, 'metadata'=>null]) ];
    }

    public static function hookOn($auth_id, $data_type) {
        $auth = ApplicationManager::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $app = ApplicationManager::getApplication($auth->app_code_name);

        if ($app === null) {
            return 'ERROR: App not found!';
        }

        $app->registerUpdateNotifier($auth, $data_type);

        return 'success!';
    }

    public static function onNewUpdate($auth, $data_type) {
        $write_auths = ApplicationManager::getWriteAuthentications($data_type, $auth);

        // Fake in memory queue
        $queue = [];

        foreach($write_auths as $write_auth) {
            $task = new Task($auth, $write_auth, $data_type);

            // Add task to the queue
            $queue[] = $task;
        }

        // Immediately call queue to be processed
        ApplicationManager::processQueue($queue);
    }

    public static function processQueue($queue) {
        foreach($queue as $task) {
            $data_type = $task->data_type;
            
            $src_app = ApplicationManager::getApplication($task->from_auth->app_code_name);
            $dst_app = ApplicationManager::getApplication($task->to_auth->app_code_name);
            
            $src_reader = $src_app->getReader($task->from_auth, $data_type);
            $dst_reader = $dst_app->getReader($task->to_auth, $data_type);
            $writer = $dst_app->getWriter($task->to_auth, $data_type);

            // In the future, we should support custom implementations
            $change_interpreter = DataTypeManager::getChangeInterpreter($data_type);

            $changes = $change_interpreter->getStateChanges($src_reader, $dst_reader);
            $writer->applyStateChanges($changes);

            // TODO Remove task from the actual queue
        }
    }

}