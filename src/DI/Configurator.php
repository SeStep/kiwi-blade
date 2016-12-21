<?php

namespace KiwiBlade\DI;

use Exception;

class Configurator
{
    /** @var mixed[] */
    private $params = [];
    /** @var string */
    private $configDirectory;
    /** @var string */
    private $configFileSuffix;

    /** @var Container */
    private $container;

    public function __construct($configDirectory, $configFileSuffix = '.config.php')
    {
        if (substr($configDirectory, -1) != '/') {
            $configDirectory .= '/';
        }
        $this->configDirectory = $configDirectory;
        $this->configFileSuffix = $configFileSuffix;

        $this->container = new Container();
    }

    public function addFirstExistingConfig($files = [])
    {
        foreach ($files as $file) {
            if ($this->addConfig($file, false)) {
                return true;
            }
        }

        $filenames = array_map(function ($item) {
            return $item . $this->configFileSuffix;
        }, $files);
        die('No config file could be loaded. tried to look for ' . implode(', ', $filenames));
    }

    public function addConfig($file, $need = true)
    {
        $filename = $file . $this->configFileSuffix;
        $path = $this->configDirectory . $filename;
        if (!file_exists($path)) {
            if (!$need) {
                return false;
            }
            throw new Exception("File $filename was not found in the config folder.");
        }
        $params = include $path;
        if (!is_array($params)) {
            throw new Exception("Config file $filename did not return proper configuration");
        }

        // All new values + previously set values that weren't to be overwritten
        $this->params = array_merge($this->params, $params);

        return $params;
    }

    /** @return Container */
    public function createContainer()
    {
        $container = $this->container;

        $container->setParameters($this->params);

        array_unshift($this->params['extensions'], KiwiBladeExtension::class);
        foreach ($this->params['extensions'] as $extensionClass) {
            /** @var AConfiguratorExtension $extension */
            $extension = new $extensionClass();
            $extension->registerServices($this, $container);
        }

        return $container;
    }
}
