<?php

namespace App\Connectors\LetsPeppol\PonderSource\SMP;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::WSA, prefix="wsa")
 */
class EndpointReference 
{

    /**
     * @SerializedName("Address")
     * @XmlElement(cdata=false, namespace=Namespaces::WSA)
     * @Type("string")
     */
    private $address;

    public function __construct($address = null) {
        $this->address = $address;
        return $this;
    }

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function getAddress() {
        return $this->address;
    }

}