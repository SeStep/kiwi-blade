<?php

namespace KiwiBlade\UrlAlias;


use KiwiBlade\DI\AContainerExtension;
use KiwiBlade\DI\Container;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use KiwiBlade\Http\RequestFactory;

class UrlAliasExtension extends AContainerExtension
{
    public function registerServices(Container $container)
    {
        $container->autoregisterService(UrlAliaser::class, 'urlAliaser');
        $container->registerService(LinkGenerator::class, function (Container $container){
            /** @var Request $request */
            $request = $container->get(Request::class);
            $niceUrl = (bool)$container->getParams()['niceUrl'];
            $aliaser = $container->get(UrlAliaser::class);

            return new AliasedLinkGenerator($request->getBaseUrl(), $niceUrl, $request->getController(), $aliaser);
        });

        /** @var UrlAliaser $aliaser */
        $aliaser = $container->get(UrlAliaser::class);
        /** @var Request $request */
        $request = $container->get(Request::class);
        $url = $request->getController();


        if ($aliaser->aliasExists($url)) {
            $parts = $aliaser->decodeAlias($url);
            $parts = $aliaser->decodeParts($parts['controller'], $parts['action']);
        } else {
            $parts = $aliaser->decodeParts($request->getController(), $request->getAction());
        }

        $request->setController($parts['controller']);
        $request->setAction($parts['action']);
    }
}
