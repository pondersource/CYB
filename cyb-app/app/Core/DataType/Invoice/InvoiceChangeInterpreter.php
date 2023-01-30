<?php

namespace App\Core\DataType\Invoice;

use App\Core\ChangeInterpreter;

class InvoiceChangeInterpreter implements ChangeInterpreter
{
    public function getStateChanges($src_reader, $dst_reader)
    {
        // TODO
        error_log('Change interpreter started');
        sleep(1);
        error_log('Change interpreter ended');
        return null;
    }
}
