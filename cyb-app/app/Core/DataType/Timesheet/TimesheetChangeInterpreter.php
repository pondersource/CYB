<?php

namespace App\Core\DataType\Timesheet;

use App\Core\ChangeInterpreter;

class TimesheetChangeInterpreter implements ChangeInterpreter
{
    public function getStateChanges($src_reader, $dst_reader, int $since_time)
    {
        // TODO
        error_log('Change interpreter started');
        sleep(1);
        error_log('Change interpreter ended');
        return null;
    }
}
