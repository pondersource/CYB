<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class PartyIdentification 
{

    /**
     * @SerializedName("ID")
     * @XmlElement(cdata=false, namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\ID")
     */
    private $id;
    
    public function __construct($id = null) {
        $this->id = $id;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

}