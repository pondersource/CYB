<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Applications\Teamwork\Auth;
use App\Applications\Teamwork\Factory;

class TeamworkWriteController extends Controller
{
    public function __invoke()
    {
        $API_KEY = config('services.teamwork.secret');
        $API_URL = config('services.teamwork.domain'); // only required if you have a custom domain

        try {
            // set your keys
            // if you do not have a custom domain:
            Auth::set($API_URL, $API_KEY);
            // if you do have a custom domain:
            // TeamWorkPm\Auth::set(API_URL, API_KEY);

            // create an project
            $project = Factory::build('project');
            $project_id = $project->save([
                'name'=> 'This is a test project',
                'description'=> 'Bla, Bla, Bla'
            ]);

            // create one people and add to project
            //$people = Factory::build('people');
            //var_dump($people);
            /*$person_id = $people->save([
                'first_name'  => 'Test',
                'last_name'   => 'User',
                'user_name'     => 'test',
                'email_address' => 'benz94.94@mail.ru',
                'password'      => 'Alex21_21_21',
                'project_id'    => $project_id
            ]);*/

            // create on milestone
            $milestone = Factory::build('milestone');
            $milestone_id = $milestone->save([
                'project_id'            => $project_id,
                'responsible_party_ids' => 79831,
                'title'                 => 'Test milestone',
                'description'           => 'Bla, Bla, Bla',
                'deadline'              => date('Ymd', strtotime('+10 day'))
            ]);

            // create one task list
            $taskList = Factory::build('task.list');
            $task_list_id = $taskList->save([
                'project_id'  => $project_id,
                'milestone_id' => $milestone_id,
                'name'        => 'My first task list',
                'description' => 'Bla, Bla'
            ]);

            $task = Factory::build('task');
            $task_id = $task->save([
                'task_list_id' => $task_list_id,
                'content'      => 'Test Task',
                'notify'       => false,
                'description'  => 'Bla, Bla, Bla',
                'due_date'     => date('Ymd', strtotime('+10 days')),
                'start_date'   => date('Ymd'),
                'private'      => false,
                'priority'     => 'high',
                'estimated_minutes' => 1000,
                'responsible_party_id' => 79831,
            ]);

            $time = Factory::build('time');
            $time_id = $time->save([
                'task_id' => $task_id,
                'person_id' => 79831,
                'description' => 'Test Time',
                'date' => date('Ymd'),
                'hours' => 5,
                'minutes' => 30,
                'time' => '08:30',
                'isbillable' => false
            ]);


            echo 'Project Id: ' . $project_id . "\n";
            //echo 'Person Id: ' . $person_id . "\n";
            echo 'Milestone Id: ' . $milestone_id . "\n";
            echo 'Task List Id: ' . $task_list_id . "\n";
            echo 'Task Id: ' . $task_id . "\n";
            echo 'Time Id: ' . $time_id . "\n";

        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }
}
