<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Task;
use App\Core\ApplicationManager;
use App\Core\DataType\DataTypeManager;

class TaskProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The task instance.
     *
     * @var Task
     */
    public Task $task;

    /**
     * Create a new job instance.
     *
     * @param Task $task
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $src_app = ApplicationManager::getApplication($this->task->from_auth->app_code_name);
        $dst_app = ApplicationManager::getApplication($this->task->to_auth->app_code_name);

        $src_reader = $src_app->getReader($this->task->data_type);
        $dst_reader = $dst_app->getReader($this->task->data_type);
        $writer = $dst_app->getWriter($this->task->data_type);

        // In the future, we should support custom implementations.
        $change_interpreter = DataTypeManager::getChangeInterpreter($this->task->data_type);

        $changes = $change_interpreter->getStateChanges($src_reader, $dst_reader);
        $writer->applyStateChanges($changes);
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(): void
    {
        error_log('failed.');
    }
}
