<?php

namespace App\Connectors\LetsPeppol\PonderSource\UBL\Invoice;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement,XmlList};

/**
 * @XmlNamespace(uri=Namespaces::CBC, prefix="cbc")
 * @XmlNamespace(uri=Namespaces::CAC, prefix="cac")
 */
class PaymentMandate 
{
    
    /**
     * @SerializedName("ID")
     * @XmlElement(cdata=false,namespace=Namespaces::CBC)
     * @Type("string")
     */
    private $id;

    /**
     * @SerializedName("PayerFinancialAccount")
     * @XmlElement(cdata=false,namespace=Namespaces::CAC)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\PayerFinancialAccount")
     */
    private $payerFinancialAccount;
    
    public function __construct($id = null, $payerFinancialAccount = null) {
        $this->id = $id;
        $this->payerFinancialAccount = $payerFinancialAccount;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setPayerFinancialAccount($payerFinancialAccount) {
        $this->payerFinancialAccount = $payerFinancialAccount;
        return $this;
    }

    public function getPayerFinancialAccount() {
        return $this->payerFinancialAccount;
    }

}