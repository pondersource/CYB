<?php

namespace App\Connectors\LetsPeppol\PonderSource\WSSec;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{XmlRoot,XmlElement,XmlNamespace,Type,SerializedName,XmlList};
use App\Connectors\LetsPeppol\PonderSource\WSSec\CanonicalizationMethod\C14NExclusive;

/**
 * @XmlNamespace(uri=Namespaces::DS, prefix="ds")
 * @XmlRoot("ds:SignedInfo")
 */
class SignedInfo {
    /**
     * @SerializedName("CanonicalizationMethod")
     * @Type("App\Connectors\LetsPeppol\PonderSource\WSSec\CanonicalizationMethod\C14NExclusive")
     * @XmlElement(namespace=Namespaces::DS)
     */
    private $canonicalizationMethod;

    /**
     * @SerializedName("SignatureMethod")
     * @Type("App\Connectors\LetsPeppol\PonderSource\WSSec\SignatureMethod\RsaSha256")
     * @XmlElement(namespace=Namespaces::DS)
     */
    private $signatureMethod;

    /**
     * @XmlList(inline=true, entry="Reference", namespace=Namespaces::DS)
     * @Type("array<App\Connectors\LetsPeppol\PonderSource\WSSec\DSigReference>")
     */
    private $references = [];

    public function __construct($signatureMethod, $canonicalizationMethod=null){
        $this->signatureMethod = $signatureMethod;
        if(is_null($canonicalizationMethod)){
            $this->canonicalizationMethod = new C14NExclusive();  // CanonicalizationMethod\C14NExcCanonicalizationMethod();
        } else {
            $this->canonicalizationMethod = $canonicalizationMethod;
        }
        return $this;
    }

    public function addReference($reference){
        array_push($this->references, $reference);
        return $this;
    }

    public function removeReference($reference){
        array_filter($this->references, function($r) use($reference) { return $r != $reference; });
    }

    public function getReferences(){
        return $this->references;
    }

    public function getCanonicalizationMethod(){
        return $this->canonicalizationMethod;
    }

    public function getSignatureMethod(){
        return $this->signatureMethod;
    }
}