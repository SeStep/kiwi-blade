<?php

namespace KiwiBlade\DI;

use Exception;

class ConfiguratorException extends Exception
{

    public static function extensionAlreadyRegistered($extClass)
    {
        return new ConfiguratorException("Duplicate extension '$extClass' occurence");
    }
}
