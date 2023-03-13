<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class Price 
{
    
    /**
     * @SerializedName("PriceAmount")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Amount")
     */
    private $priceAmount;
    
    /**
     * @SerializedName("BaseQuantity")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Quantity")
     */
    private $baseQuantity;

    /**
     * @SerializedName("AllowanceCharge")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\AllowanceCharge")
     */
    private $allowanceCharge;
    
    public function __construct($priceAmount = null, $baseQuantity = null, $allowanceCharge = null) {
        $this->priceAmount = $priceAmount;
        $this->baseQuantity = $baseQuantity;
        $this->allowanceCharge = $allowanceCharge;
        return $this;
    }

    public function setPriceAmount($priceAmount) {
        $this->priceAmount = $priceAmount;
        return $this;
    }

    public function getPriceAmount() {
        return $this->priceAmount;
    }

    public function setBaseQuantity($baseQuantity) {
        $this->baseQuantity = $baseQuantity;
        return $this;
    }

    public function getBaseQuantity() {
        return $this->baseQuantity;
    }

    public function setAllowanceCharge($allowanceCharge) {
        $this->allowanceCharge = $allowanceCharge;
        return $this;
    }

    public function getAllowanceCharge() {
        return $this->allowanceCharge;
    }

}