<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class CommodityClassification 
{
    
    /**
     * @SerializedName("ItemClassificationCode")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\ItemClassificationCode")
     */
    private $itemClassificationCode;
    
    public function __construct($itemClassificationCode = null) {
        $this->itemClassificationCode = $itemClassificationCode;
        return $this;
    }

    public function setItemClassificationCode($itemClassificationCode) {
        $this->itemClassificationCode = $itemClassificationCode;
        return $this;
    }

    public function getItemClassificationCode() {
        return $this->itemClassificationCode;
    }

}