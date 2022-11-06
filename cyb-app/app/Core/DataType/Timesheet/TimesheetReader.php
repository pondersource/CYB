<?php

namespace App\Core\DataType\Timesheet;

interface TimesheetReader
{
    public function getData($from_date, $to_date);
}
