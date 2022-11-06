<?php

namespace App\Core\DataType\Timesheet;

use App\Core\DataType\DataType;

class TimesheetType implements DataType
{
    public function getCodeName()
    {
        return 'timesheet';
    }

    public function getDisplayName()
    {
        return 'Timesheet data';
    }
}
