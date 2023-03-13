<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class AccountingCustomerParty 
{

    /**
     * @SerializedName("Party")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Party")
     */
    private $party;
    
    public function __construct($party = null) {
        $this->party = $party;
        return $this;
    }

    public function setParty($party) {
        $this->party = $party;
        return $this;
    }

    public function getParty() {
        return $this->party;
    }

}