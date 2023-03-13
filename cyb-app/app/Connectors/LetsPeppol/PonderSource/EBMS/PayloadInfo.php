<?php

namespace App\Connectors\LetsPeppol\PonderSource\EBMS;

use JMS\Serializer\Annotation\{Type,SerializedName,XmlNamespace,XmlRoot,XmlElement};
use App\Connectors\LetsPeppol\PonderSource\EBMS\PartInfo;
use App\Connectors\LetsPeppol\PonderSource\Namespaces;

class PayloadInfo {
    /**
     * @XmlElement(cdata=false, namespace=Namespaces::EB)
     * @SerializedName("PartInfo");
     * @Type("App\Connectors\LetsPeppol\PonderSource\EBMS\PartInfo")
     */
    private $partInfo;

    public function __construct($partInfo){
        $this->partInfo = $partInfo;
        return $this;
    }

    public function setPartInfo($partInfo){
        $this->partInfo = $partInfo;
        return $this;
    }

    public function getPartInfo(){
        return $this->partInfo;
    }
}