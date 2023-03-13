<?php

namespace App\Connectors\LetsPeppol\PonderSource\SBD;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};
use App\Connectors\LetsPeppol\PonderSource\SBD\Any;

/**
 * @XmlNamespace(uri=Namespaces::SBD)
 * @XmlNamespace(uri=Namespaces::XS, prefix="xs")
 * @XmlRoot("StandardBusinessDocument")
 */
class StandardBusinessDocument
{
    /**
     * @SerializedName("StandardBusinessDocumentHeader")
     * @XmlElement(namespace=Namespaces::SBD)
     * @Type("App\Connectors\LetsPeppol\PonderSource\SBD\StandardBusinessDocumentHeader")
     */
    private $header;

    /**
     * @SerializedName("Invoice")
     * @XmlElement(namespace=Namespaces::UBL)
     * @Type("App\Connectors\LetsPeppol\PonderSource\UBL\Invoice\Invoice")
     */
    private $invoice;

    /**
     * SerializedName("Any")
     * XmlElement()
     * Type("App\Connectors\LetsPeppol\PonderSource\SBD\Any")
     */
    private $any;

    public function __construct($header = null, $invoice = null, $any = null){
        $this->header = $header;
        $this->invoice = $invoice;
        $this->any = $any;
        return $this;
    }

    public function setHeader($header){
        $this->header = $header;
        return $this;
    }

    public function getHeader(){
        return $this->header;
    }

    public function setInvoice($invoice){
        $this->invoice = $invoice;
        return $this;
    }

    public function getInvoice(){
        return $this->invoice;
    }

}