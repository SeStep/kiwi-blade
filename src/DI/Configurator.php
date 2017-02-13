<?php

namespace KiwiBlade\DI;


use KiwiBlade\Extensions\KiwiBladeExtension;

class Configurator
{
    /** @var mixed[] */
    private $config = [];
    /** @var string */
    private $configDirectory;
    /** @var string */
    private $configFileSuffix;

    public function __construct($configDirectory, $configFileSuffix = '.config.php')
    {
        if (substr($configDirectory, -1) != '/') {
            $configDirectory .= '/';
        }
        $this->configDirectory = $configDirectory;
        $this->configFileSuffix = $configFileSuffix;
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

        throw new ConfiguratorException('No config file could be loaded. tried to look for ' . implode(', ', $filenames));
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
        $this->config = array_merge_recursive($this->config, $params);

        return $params;
    }

    /**
     * @return Container
     * @throws ConfiguratorException
     */
    public function createContainer()
    {
        if (array_key_exists('configFiles', $this->config) && is_array($this->config['configFiles'])) {
            foreach ($this->config['configFiles'] as $file) {
                $this->addConfig($file);
            }
        }

        $params = $this->config['parameters'];
        $extensionParams = $this->config;
        unset($extensionParams['parameters']);

        $container = new Container($params, $extensionParams);
        $extensions = array_merge(['kb' => KiwiBladeExtension::class], $this->config['extensions']);

        $this->registerExtensionsTo($container, $extensions, $extensionParams);

        return $container;
    }

    /**
     * @param Container $container
     * @param string[]  $extensions
     * @throws ConfiguratorException
     */
    private function registerExtensionsTo(Container $container, $extensions, $params){
        $registeredExtensions = [];
        foreach ($extensions as $extName => $extClass) {
            if(array_key_exists($extClass, $registeredExtensions)){
                throw ConfiguratorException::extensionAlreadyRegistered($extClass);
            }
            $args = isset($params[$extName]) ? $params[$extName] : [];

            /** @var AContainerExtension $extension */
            $extension = new $extClass($extName, $args);
            $extension->registerServices($container);

            $registeredExtensions[$extClass] = $extName;
        }
    }
}
