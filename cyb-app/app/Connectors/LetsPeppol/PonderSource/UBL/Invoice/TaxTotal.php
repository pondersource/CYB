<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class TaxTotal 
{
    
    /**
     * @SerializedName("TaxAmount")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Amount")
     */
    private $taxAmount;

    /**
     * @XmlList(inline=true, entry="TaxSubtotal", namespace=Namespaces::CAC)
     * @Type("array<App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\TaxSubtotal>")
     */
    private $taxSubtotals;
    
    public function __construct($taxAmount = null, $taxSubtotals = null) {
        $this->taxAmount = $taxAmount;
        $this->taxSubtotals = $taxSubtotals;
        return $this;
    }

    public function setTaxAmount($taxAmount) {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTaxAmount() {
        return $this->taxAmount;
    }

    public function setTaxSubtotals($taxSubtotals) {
        $this->taxSubtotals = $taxSubtotals;
        return $this;
    }

    public function getTaxSubtotals() {
        return $this->taxSubtotals;
    }

}