<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class PayeeParty 
{

    /**
     * @SerializedName("PartyIdentification")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\PartyIdentification")
     */
    private $partyIdentification;

    /**
     * @SerializedName("PartyName")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\PartyName")
     */
    private $partyName;

    /**
     * @SerializedName("PartyLegalEntity")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\PartyLegalEntity")
     */
    private $partyLegalEntity;
    
    public function __construct($partyIdentification = null, $partyName = null, $partyLegalEntity = null) {
        $this->partyIdentification = $partyIdentification;
        $this->partyName = $partyName;
        $this->partyLegalEntity = $partyLegalEntity;
        return $this;
    }

    public function setPartyIdentification($partyIdentification) {
        $this->partyIdentification = $partyIdentification;
        return $this;
    }

    public function getPartyIdentification() {
        return $this->partyIdentification;
    }

    public function setPartyName($partyName) {
        $this->partyName = $partyName;
        return $this;
    }

    public function getPartyName() {
        return $this->partyName;
    }

    public function setPartyLegalEntity($partyLegalEntity) {
        $this->partyLegalEntity = $partyLegalEntity;
        return $this;
    }

    public function getPartyLegalEntity() {
        return $this->partyLegalEntity;
    }

}