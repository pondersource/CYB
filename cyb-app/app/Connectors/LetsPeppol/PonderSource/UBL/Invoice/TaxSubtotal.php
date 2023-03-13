<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class TaxSubtotal 
{
    
    /**
     * @SerializedName("TaxableAmount")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Amount")
     */
    private $taxableAmount;
    
    /**
     * @SerializedName("TaxAmount")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Amount")
     */
    private $taxAmount;

    /**
     * @SerializedName("TaxCategory")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\TaxCategory")
     */
    private $taxCategory;
    
    public function __construct($taxableAmount = null, $taxAmount = null, $taxCategory = null) {
        $this->taxableAmount = $taxableAmount;
        $this->taxAmount = $taxAmount;
        $this->taxCategory = $taxCategory;
        return $this;
    }

    public function setTaxableAmount($taxableAmount) {
        $this->taxableAmount = $taxableAmount;
        return $this;
    }

    public function getTaxableAmount() {
        return $this->taxableAmount;
    }

    public function setTaxAmount($taxAmount) {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTaxAmount() {
        return $this->taxAmount;
    }

    public function setTaxCategory($taxCategory) {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    public function getTaxCategory() {
        return $this->taxCategory;
    }

}