<?php

namespace App\Connectors\LetsPeppol\PonderSource\SMP;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlList,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::SMP, prefix="smp")
 * @XmlNamespace(uri=Namespaces::ID, prefix="id")
 */
class Process
{

    /**
     * @SerializedName("ProcessIdentifier")
     * @XmlElement(namespace=Namespaces::ID)
     * @Type("App\Connectors\LetsPeppol\PonderSource\SMP\ProcessIdentifier")
     */
    private $processIdentifier;

    /**
     * @SerializedName("ServiceEndpointList")
     * @XmlList(inline=false, entry="Endpoint", namespace=Namespaces::SMP)
     * @Type("array<App\Connectors\LetsPeppol\PonderSource\SMP\Endpoint>")
     * @XmlElement(namespace=Namespaces::SMP)
     */
    private $endpointList = [];

    public function __construct($processIdentifier = null, $endpointList = []){
        $this->processIdentifier = $processIdentifier;
        $this->endpointList = $endpointList;
        return $this;
    }
    
    public function setProcessIdentifier($processIdentifier) {
        $this->processIdentifier = $processIdentifier;
        return $this;
    }

    public function getProcessIdentifier() {
        return $this->processIdentifier;
    }

    public function addEndpoint($endpoint) {
        array_push($this->endpointList, $endpoint);
        return $this;
    }

    public function removeEndpoint($endpoint) {
        array_filter($this->endpointList, function($t) { return $t != $endpoint; });
        return $this;
    }

    public function getEndpointList() {
        return $this->endpointList;
    }

}