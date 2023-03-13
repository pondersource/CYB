<?php

namespace App\Connectors\LetsPeppol\PonderSource\SBD;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};
use App\Connectors\LetsPeppol\PonderSource\SBD\Any;

/**
 */
class Receiver 
{

    /**
     * @SerializedName("Identifier")
     * @XmlElement(namespace=Namespaces::SBD)
     * @Type("App\Connectors\LetsPeppol\PonderSource\SBD\Identifier")
     */
    private $identifier;

    public function __construct($identifier = null){
        $this->identifier = $identifier;
        return $this;
    }
    
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
        return $this;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

}