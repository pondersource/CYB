<?php

namespace App\Core\DataType\Invoice;

interface Invoice
{
    public const DIRECTION_INCOMING = 0;
    public const DIRECTION_OUTGOING = 1;

    public function getDirection(): int;
    public function getContent(): string;
}