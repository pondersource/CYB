<?php

namespace App\Connectors\LetsPeppol;

use App\Connectors\LetsPeppol\Models\Identity;
use App\Core\DataType\Invoice\InvoiceReader;

class LetsPeppolReader implements InvoiceReader
{

    private LetsPeppolService $service;
    private Identity $identity;

    public function __construct(LetsPeppolService $service, Identity $identity)
    {
        $this->service = $service;
        $this->identity = $identity;
    }

    public function getChanges(): array
    {
        return $this->service->getMessages($this->identity['user_id']);
    }

    public function changesNoLongerNeeded(int $until)
    {
        $service->removeMessages($this->identity['user_id'], $until);
    }

}