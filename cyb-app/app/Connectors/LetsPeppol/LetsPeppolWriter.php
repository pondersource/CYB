<?php

namespace App\Connectors\LetsPeppol;

use App\Connectors\LetsPeppol\Models\Identity;
use App\Core\DataType\Invoice\Invoice;
use App\Core\Writer;

class LetsPeppolWriter implements Writer
{

    private LetsPeppolService $service;
    private Identity $identity;

    public function __construct(LetsPeppolService $service, Identity $identity)
    {
        $this->service = $service;
        $this->identity = $identity;
    }

    public function applyStateChanges($changes)
    {
        foreach ($changes as $invoice) {
            $this->applyInvoice($invoice);        
        }
    }

    private function applyInvoice(Invoice $invoice)
    {
        if ($invoice->getDirection() == Invoice::DIRECTION_INCOMING) {
            $this->service->addIncomingMessage($identity['user_id'], $invoice->getContent());
        }
        else if ($invoice->getDirection() == Invoice::DIRECTION_OUTGOING) {
            $this->service->sendMessage($identity['user_id'], $invoice->getContent());
        }
    }

}