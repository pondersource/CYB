<?php

namespace App\Connectors\LetsPeppol\PonderSource\WSSec;

use App\Connectors\LetsPeppol\PonderSource\Namespaces;
use JMS\Serializer\Annotation\{XmlRoot,Type,XmlNamespace,XmlAttribute,SerializedName};

/**
 * @XmlNamespace(uri=Namespaces::XENC, prefix="xenc")
 * @XmlRoot("xenc:DataReference")
 */
class DataReference {
    /**
     * @XmlAttribute
     * @SerializedName("URI")
     * @Type("string")
     */
    private $uri;

    public function __construct($uri){
        $this->uri = $uri;
        return $this;
    }
}