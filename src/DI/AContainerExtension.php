<?php

namespace KiwiBlade\DI;


abstract class AContainerExtension
{
    /** @var string */
    private $name;

    private $actualArgs;

    /**
     * AContainerExtension constructor.
     *
     * @param string $name
     * @throws ConfiguratorException
     */
    public function __construct($name, $args = [])
    {
        if (!$name) {
            throw new ConfiguratorException("Extension name '$name' is not valid");
        }
        $this->name = $name;
        $this->actualArgs = $args;
    }

    public abstract function registerServices(Container $container);

    protected function prefix($name)
    {
        return "$this->name.$name";
    }

    protected function getArgs()
    {
        return $this->actualArgs;
    }
}
