<?php

namespace App\Connectors\LetsPeppol\PonderSource\EBMS;

use App\Connectors\LetsPeppol\PonderSource\EBMS\PartyId;
use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type, XmlElement, SerializedName};

class Party {
    /**
     * @SerializedName("PartyId");
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\PartyId")
     */
    private $partyId;

    /**
     * @SerializedName("Role");
     * @XmlElement(cdata=false,namespace=Namespaces::EB);
     * @Type("string")
     */
    private $role;

    public function __construct($partyId, $role){
        $this->partyId = $partyId;
        $this->role = $role;
        return $this;
    }

    public function getPartyId(){
        return $this->partyId;
    }

    public function setPartyId($partyId){
        $this->partyId = $partyId;
        return $this;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role){
        $this->role = $role;
        return $this;
    }
}