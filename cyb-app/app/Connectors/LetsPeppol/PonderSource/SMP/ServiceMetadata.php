<?php

namespace App\Connectors\LetsPeppol\PonderSource\SMP;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::SMP, prefix="smp")
 */
class ServiceMetadata 
{

    /**
     * @SerializedName("ServiceInformation")
     * @XmlElement(namespace=Namespaces::SMP)
     * @Type("App\Connectors\LetsPeppol\PonderSource\SMP\ServiceInformation")
     */
    private $serviceInformation;

    public function __construct($serviceInformation = null){
        $this->serviceInformation = $serviceInformation;
        return $this;
    }

    public function setServiceInformation($serviceInformation){
        $this->serviceInformation = $serviceInformation;
        return $this;
    }

    public function getServiceInformation(){
        return $this->serviceInformation;
    }

}