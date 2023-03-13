<?php

namespace App\Connectors\LetsPeppol\PonderSource\WSSec\DigestMethod;

interface IDigestMethod {
    public function getUri();
    public function getDigest($value);
}