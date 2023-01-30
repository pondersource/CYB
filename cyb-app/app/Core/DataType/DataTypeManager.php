<?php

namespace App\Core\DataType;

use App\Core\DataType\Invoice\InvoiceChangeInterpreter;
use App\Core\DataType\Invoice\InvoiceType;
use App\Core\DataType\Timesheet\TimesheetChangeInterpreter;
use App\Core\DataType\Timesheet\TimesheetType;

class DataTypeManager
{

    public static function getDataTypeForName($data_type)
    {
        if ($data_type == 'invoice') {
            return new InvoiceType();
        }

        if ($data_type == 'timesheet') {
            return new TimesheetType();
        }

        return null;
    }

    public static function getChangeInterpreter($data_type)
    {
        if ($data_type == 'invoice') {
            return new InvoiceChangeInterpreter();
        }

        if ($data_type == 'timesheet') {
            return new TimesheetChangeInterpreter();
        }

        return null;
    }
}
