<?php

namespace App\Connectors\LetsPeppol\PonderSource\SMP;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::SMP, prefix="smp")
 * @XmlNamespace(uri=Namespaces::DS, prefix="ds")
 * @XmlNamespace(uri=Namespaces::ID, prefix="id")
 * @XmlNamespace(uri=Namespaces::WSA, prefix="wsa")
 * @XmlRoot("smp:SignedServiceMetadata")
 */
class SignedServiceMetadata 
{

    /**
     * @SerializedName("ServiceMetadata")
     * @XmlElement(namespace=Namespaces::SMP)
     * @Type("App\Connectors\LetsPeppol\PonderSource\SMP\ServiceMetadata")
     */
    private $serviceMetadata;

    /**
     * @SerializedName("Signature")
     * @Type("App\Connectors\LetsPeppol\PonderSource\SMP\Signature")
     */
    private $signature;

    public function __construct($serviceMetadata = null, $signature = null){
        $this->serviceMetadata = $serviceMetadata;
        $this->signature = $signature;
        return $this;
    }
    
    public function setServiceMetadata($serviceMetadata) {
        $this->serviceMetadata = $serviceMetadata;
        return $this;
    }

    public function getServiceMetadata() {
        return $this->serviceMetadata;
    }

    public function setSignature($signature) {
        $this->signature = $signature;
        return $this;
    }

    public function getSignature() {
        return $this->signature;
    }

}