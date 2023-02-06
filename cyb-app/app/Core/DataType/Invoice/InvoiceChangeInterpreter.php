<?php

namespace App\Core\DataType\Invoice;

use App\Core\ChangeInterpreter;

class InvoiceChangeInterpreter implements ChangeInterpreter
{
    public function getStateChanges(InvoiceReader $src_reader, InvoiceReader $dst_reader, int $since_time)
    {
        $changes = $src_reader->getChanges();
        $src_reader->changesNoLongerNeeded($since_time);
        return $changes;
    }
}
