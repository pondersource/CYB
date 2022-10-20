<?php

namespace App\Core;

use App\Applications\Prejournal\PrejournalAuthenticationAdopter;
use App\Applications\Teamwork\TeamworkAuthenticationAdopter;
use App\Core\Authentication;
use App\Core\DataType\DataTypes;
use App\Core\Task;

class Applications {
   
    public static function getApplications() {
        // Read from an static array
        return [ new PrejournalAuthenticationAdopter(), new TeamworkAuthenticationAdopter() ];
    }

    public static function getApplication($code_name) {
        $apps = Applications::getApplications();

        foreach($apps as $app) {
            if ($app->getAppCodeName() == $code_name) {
                return $app;
            }
        }

        return null;
    }

    public static function finalizeAuthentication($app_code_name) {
        $app = Applications::getApplication($app_code_name);

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
            new Authentication(1, 'teamwork', 'Ismoil', 1, null, false, false),
            new Authentication(2, 'prejournal', 'Ismoil', 1, null, false, true)
        ];
    }

    public static function getAuthentication($auth_id) {
        // TODO Query from authentication table
        if ($auth_id == 1) {
            return new Authentication(1, 'teamwork', 'Ismoil', 1, null, false, false);
        }
        else {
            return new Authentication(2, 'prejournal', 'Ismoil', 1, null, false, true);
        }
    }

    public static function getWriteAuthentications($data_type, $except) {
        // TODO Query from authentication table
        return [ new Authentication(2, 'prejournal', 'Ismoil', 1, null, false, true) ];
    }

    public static function hookOn($auth_id, $data_type) {
        $auth = Applications::getAuthentication($auth_id);

        if ($auth === null) {
            return 'ERROR: Auth not found!';
        }

        $app = Applications::getApplication($auth->app_code_name);

        if ($app === null) {
            return 'ERROR: App not found!';
        }

        $app->registerUpdateNotifier($auth, $data_type);

        return 'success!';
    }

    public static function onNewUpdate($auth, $data_type) {
        $write_auths = Applications::getWriteAuthentications($data_type, $auth);

        // Fake in memory queue
        $queue = [];

        foreach($write_auths as $write_auth) {
            $task = new Task($auth, $write_auth, $data_type);

            // Add task to the queue
            $queue[] = $task;
        }

        // Immediately call queue to be processed
        Applications::processQueue($queue);
    }

    public static function processQueue($queue) {
        foreach($queue as $task) {
            $data_type = $task->data_type;
            
            $src_app = Applications::getApplication($task->from_auth->app_code_name);
            $dst_app = Applications::getApplication($task->to_auth->app_code_name);
            
            $src_reader = $src_app->getReader($data_type);
            $dst_reader = $dst_app->getReader($data_type);
            $writer = $dst_app->getWriter($data_type);

            // In the future, we should support custom implementations
            $change_interpreter = DataTypes::getChangeInterpreter($data_type);

            $changes = $change_interpreter->getStateChanges($src_reader, $dst_reader);
            $writer->applyStateChanges($changes);

            // TODO Remove task from the actual queue
        }
    }

}