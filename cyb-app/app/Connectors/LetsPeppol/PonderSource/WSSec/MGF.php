<?php

namespace App\Connectors\LetsPeppol\PonderSource\WSSec;


use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{XmlRoot,Type,XmlNamespace,XmlAttribute,SerializedName};

/**
 * @XmlNamespace(uri=Namespaces::XENC11, prefix="xenc11")
 * @XmlRoot("xenc11:MGF")
 */
class MGF {
    /**
     * @XmlAttribute
     * @SerializedName("Algorithm")
     * @Type("string")
     */
    private $algorithm;

    public function __construct($algorithm){
        $this->algorithm = $algorithm;
        return $this;
    }
} 