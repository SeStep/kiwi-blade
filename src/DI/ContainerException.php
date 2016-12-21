<?php

namespace KiwiBlade\DI;

use Exception;
use RuntimeException;

class ContainerException extends RuntimeException
{
    public function __construct($id, $message = '', Exception $previous = null)
    {
        if(!$message){
            $message = $id;
        } else {
            $message = "Error occured on service $id: " . $message;
        }
        parent::__construct($message, 0, $previous);
    }
}
