<?php 

namespace App\Connectors\LetsPeppol\PonderSource\WSSec\CanonicalizationMethod;

use JMS\Serializer\Annotation\{XmlRoot, Type,XmlNamespace,XmlAttribute,SerializedName,XmlValue,XmlElement};
use App\Connectors\LetsPeppol\PonderSource\WSSec\InclusiveNamespaces;
use App\Connectors\LetsPeppol\PonderSource\Namespaces;

/**
 * @XmlNamespace(uri=Namespaces::DS, prefix="ds")
 * @XmlNamespace(uri=Namespaces::EC, prefix="ec")
 * @XmlRoot("ds:CanonicalizationMethod")
 */
class C14NExclusive implements ICanonicalizationMethod {
    /**
     * @XmlAttribute
     * @Type("string")
     * @SerializedName("Algorithm")
     */
    private $uri = "http://www.w3.org/2001/10/xml-exc-c14n#";

    /**
     * @SerializedName("InclusiveNamespaces")
     * @XmlElement(cdata=false, namespace=Namespaces::EC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\WSSec\InclusiveNamespaces")
     */
    private $childElements;

    public function __construct(){
        $this->childElements = new InclusiveNamespaces();
    }

    public function getAlgorithmUri(){
        return $this->uri;
    }
    public function getChildElements(){
        return $this->childElements;
    }
    public function applyAlgorithm($element){
        return $element->C14N(true, false, null, explode(' ', $this->childElements->getPrefixList()));
    }
}