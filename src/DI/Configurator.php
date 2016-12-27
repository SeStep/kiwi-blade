<?php

namespace KiwiBlade\DI;


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
            throw new ConfiguratorException("File $filename was not found in the config folder.");
        }
        $params = include $path;
        if (!is_array($params)) {
            throw new ConfiguratorException("Config file $filename did not return proper configuration");
        }

        // All new values + previously set values that weren't to be overwritten
        $this->params = array_merge_recursive($this->params, $params);

        return $params;
    }

    /** @return Container */
    public function createContainer()
    {
        if (array_key_exists('configFiles', $this->params) && is_array($this->params['configFiles'])) {
            foreach ($this->params['configFiles'] as $file) {
                $this->addConfig($file);
            }
        }
        $container = $this->container;

        $container->setParameters($this->params);

        $this->params['extensions'] = array_merge(['kb' => KiwiBladeExtension::class], $this->params['extensions']);

        $registeredExtensions = [];
        foreach ($this->params['extensions'] as $extName => $extClass) {
            if(array_key_exists($extClass, $registeredExtensions)){
                throw ConfiguratorException::extensionAlreadyRegistered($extClass);
            }

            /** @var AContainerExtension $extension */
            $extension = new $extClass($extName);
            $extension->registerServices($container);

            $registeredExtensions[$extClass] = $extName;
        }

        return $container;
    }
}
