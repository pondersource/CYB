<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Task;
use App\Core\ApplicationManager;

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
        ApplicationManager::taskHandler($this->task);
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
