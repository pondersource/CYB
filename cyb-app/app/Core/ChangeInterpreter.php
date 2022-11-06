<?php

namespace App\Core;

interface ChangeInterpreter
{
    public function getStateChanges($src_reader, $dst_reader);
}
