<?php

namespace App\Connectors\LetsPeppol\PonderSource\Envelope;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::S12, prefix="S12")
 * @XmlRoot("S12:Envelope")
 */
class Envelope 
{

    /**
     * @SerializedName("Header")
     * @XmlElement(namespace=Namespaces::S12)
     * @Type("App\Connectors\LetsPeppol\PonderSource\Envelope\Header")
     */
    private $header;

    /**
     * @SerializedName("Body")
     * @XmlElement(namespace=Namespaces::S12)
     * @Type("App\Connectors\LetsPeppol\PonderSource\Envelope\Body")
     */
    private $body;

    public function __construct($header = null, $body = null){
        $this->header = $header;
        $this->body = $body;
        return $this;
    }

    public function setHeader($header){
        $this->header = $header;
        return $this;
    }

    public function getHeader(){
        return $this->header;
    }

    public function setBody($body){
        $this->body = $body;
        return $this;
    }

    public function getBody(){
        return $this->body;
    }

}