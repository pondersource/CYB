<?php

namespace App\Core\DataType;

use App\Core\DataType\Timesheet\TimesheetChangeInterpreter;
use App\Core\DataType\Timesheet\TimesheetType;

class DataTypes {

    public static function getDataTypeForName($data_type) {
        if ($data_type == 'timesheet') {
            return new TimesheetType();
        }

        return null;
    }

    public static function getChangeInterpreter($data_type) {
        // Search in an static array
        return new TimesheetChangeInterpreter();
    }

}