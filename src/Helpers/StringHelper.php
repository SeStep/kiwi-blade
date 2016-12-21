<?php

namespace KiwiBlade\Helpers;

use DateTime;

class StringHelper
{
    public static function random($length){
        $now = new DateTime();
        $hash = base64_encode(md5($now->format(DateTime::RSS) . microtime(true)));
        return strtolower(substr($hash, 0, $length));
    }
}
