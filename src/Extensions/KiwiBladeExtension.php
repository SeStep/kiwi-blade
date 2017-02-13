<?php

namespace KiwiBlade\Extensions;

use KiwiBlade\Core\Dispatcher;
use KiwiBlade\DI\AContainerExtension;
use KiwiBlade\DI\Container;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use KiwiBlade\Http\RequestFactory;
use KiwiBlade\Mail\MailService;
use KiwiBlade\View\ControllerFactory;

class KiwiBladeExtension extends AContainerExtension
{
    public function registerServices(Container $container)
    {
        $container->registerServiceFactory(Request::class, [RequestFactory::class, 'create'], $this->prefix('request'));

        $container->registerService(LinkGenerator::class, function (Request $request, $niceUrl) {
            return new LinkGenerator($request->getBaseUrl(), $niceUrl, $request->getController());
        }, $this->prefix('linkGenerator'));

        $container->registerService(Dispatcher::class,
            function (Container $container, $controllerFormat, $errorController) {
                $controllerFactory = new ControllerFactory($controllerFormat, $errorController);

                return new Dispatcher($container, $controllerFactory);
            }, $this->prefix('dispatcher'));

        $container->autoregisterService(MailService::class, $this->prefix('mail'));
    }
}
