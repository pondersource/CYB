<?php

namespace App\Core\DataType\Invoice;

interface InvoiceReader
{

    public function getChanges(): array;

    public function changesNoLongerNeeded(int $until);

}