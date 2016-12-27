<?php

namespace KiwiBlade\DI;


class AContainerExtension
{
    /** @var string */
    private $name;

    /**
     * AContainerExtension constructor.
     * @param string $name
     * @throws ConfiguratorException
     */
    public function __construct($name)
    {
        if(!$name){
            throw new ConfiguratorException("Extension name '$name' is not valid");
        }
        $this->name = $name;
    }

    public function registerServices(Container $container)
    {

    }

    protected function prefix($name){
        return "$this->name.$name";
    }
}
