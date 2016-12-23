<?php

namespace KiwiBlade\View;


use KiwiBlade\DI\Container;

class ControllerFactory
{
    /** @var string */
    private $controllerFormat;
    /** @var string */
    private $errorController;

    /**
     * ControllerFactory constructor.
     * @param string $controllerFormat
     * @param string $errorController
     */
    public function __construct($controllerFormat, $errorController)
    {
        $this->controllerFormat = $controllerFormat;
        $this->errorController = $errorController;
    }

    /**
     * @param String $name
     * @return Controller
     */
    public function getControler($name, Container $container)
    {
        $class = str_replace("%c", ucfirst($name), $this->controllerFormat);

        if (!class_exists($class)) {
            return null;
        }

        return new $class($container);
    }

    public function getErrorController(Container $container)
    {
        return new $this->errorController($container);
    }
}
