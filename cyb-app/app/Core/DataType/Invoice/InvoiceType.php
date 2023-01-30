<?php

namespace App\Core\DataType\Invoice;

use App\Core\DataType\DataType;

class InvoiceType implements DataType
{
    public function getCodeName()
    {
        return 'invoice';
    }

    public function getDisplayName()
    {
        return 'Invoices';
    }
}
