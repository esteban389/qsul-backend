<?php

namespace App\Lib;

class Generator
{
    public function random($size)
    {
        return unpack('C*', \random_bytes($size));
    }
}
