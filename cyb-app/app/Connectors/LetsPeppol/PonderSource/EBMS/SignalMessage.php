<?php

namespace App\Connectors\LetsPeppol\PonderSource\EBMS;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{Type,XmlAttribute,XmlNamespace,SerializedName,XmlRoot,XmlElement};

/**
 * @XmlNamespace(uri=Namespaces::EB, prefix="eb")
 */
class SignalMessage 
{
    /**
     * @SerializedName("MessageInfo");
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\MessageInfo")
     */
    private $messageInfo;

    /**
     * @SerializedName("Receipt");
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\Receipt")
     */
    private $receipt;

    /**
     * @SerializedName("Error");
     * @XmlElement(namespace=Namespaces::EB)
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\Error")
     */
    private $error;

    public function __construct($messageInfo = null, $receipt = null, $error = null){
        $this->messageInfo = $messageInfo;
        $this->receipt = $receipt;
        $this->error = $error;
        return $this;
    }
    
    public function setMessageInfo($messageInfo) {
        $this->messageInfo = $messageInfo;
        return $this;
    }

    public function getMessageInfo() {
        return $this->messageInfo;
    }

    public function setReceipt($receipt) {
        $this->receipt = $receipt;
        return $this;
    }

    public function getReceipt() {
        return $this->receipt;
    }

    public function setError($error) {
        $this->error = $error;
        return $this;
    }

    public function getError() {
        return $this->error;
    }
    
}