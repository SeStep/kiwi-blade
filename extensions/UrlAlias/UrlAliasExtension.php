<?php

namespace KiwiBladeExtensions\UrlAlias;


use KiwiBlade\DI\AConfiguratorExtension;
use KiwiBlade\DI\Configurator;
use KiwiBlade\DI\Container;

class UrlAliasExtension extends AConfiguratorExtension
{
    public function registerServices(Configurator $configurator, Container $container)
    {
        $container->autoregisterService(UrlAliaser::class, 'urlAliaser');
    }
}
