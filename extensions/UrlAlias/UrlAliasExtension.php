<?php

namespace KiwiBladeExtensions\UrlAlias;


use KiwiBlade\DI\AContainerExtension;
use KiwiBlade\DI\Configurator;
use KiwiBlade\DI\Container;
use KiwiBlade\Http\Request;
use KiwiBlade\Http\RequestFactory;

class UrlAliasExtension extends AContainerExtension
{
    public function registerServices(Container $container)
    {
        $container->autoregisterService(UrlAliaser::class, 'urlAliaser');

        /** @var UrlAliaser $aliaser */
        $aliaser = $container->get(UrlAliaser::class);
        /** @var Request $request */
        $request = $container->get(Request::class);
        $query = $request->getParam(RequestFactory::NICE_URL_QUERY_FIELD);

        if ($aliaser->aliasExists($query)) {
            $parts = $aliaser->decodeAlias($query);
        } else {
            $parts = $aliaser->decodeParts($request->getController(), $request->getAction());
        }

        $request->setController($parts['controller']);
        $request->setAction($parts['action']);
    }
}
