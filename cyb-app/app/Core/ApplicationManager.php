<?php

namespace App\Core;

use App\Applications\Prejournal\PrejournalAuthenticationAdapter;
use App\Applications\Teamwork\TeamworkAuthenticationAdapter;
use App\Core\Authentication;
use App\Core\AuthFunction;
use App\Core\DataType\DataTypeManager;
use App\Core\Task;

class ApplicationManager {
   
    public static function getApplications() {
        // Read from an static array
        return [ new PrejournalAuthenticationAdapter(), new TeamworkAuthenticationAdapter() ];
    }

    public static function getApplication($code_name) {
        $apps = ApplicationManager::getApplications();

        foreach($apps as $app) {
            if ($app->getAppCodeName() == $code_name) {
                return $app;
            }
        }

        return null;
    }

    public static function finalizeAuthentication($app_code_name) {
        $app = ApplicationManager::getApplication($app_code_name);

        if ($app !== null) {
            $auth = $app->finalizeAuthentication();

            // TODO store auth object

            return 'success!';
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