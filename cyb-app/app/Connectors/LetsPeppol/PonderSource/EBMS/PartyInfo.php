<?php

namespace App\Connectors\LetsPeppol\PonderSource\EBMS;

use App\Connectors\LetsPeppol\PonderSource\EBMS\Party;
use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlElement,XmlNamespace,SerializedName};

class PartyInfo {
    /**
     * @SerializedName("From")
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\Party")
     */
    private $from;

    /**
     * @SerializedName("To")
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\Party")
     */
    private $to;

    public function __construct($from, $to){
        $this->from = $from;
        $this->to = $to;
    }

    public function setFrom($from){
        $this->from = $from;
        return $this;
    }

    public function getFrom(){
        return $this->from;
    }

    public function setTo($to){
        $this->to = $to;
        return $this;
    }
    
    public function getTo(){
        return $this->to;
    }
}