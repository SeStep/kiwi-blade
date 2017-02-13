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

        $container->registerService(LinkGenerator::class, function (Container $container) {
            /** @var Request $request */
            $request = $container->get(Request::class);
            $niceUrl = (bool)$container->getParams()['niceUrl'];

            return new LinkGenerator($request->getBaseUrl(), $niceUrl, $request->getController());
        });

        $container->registerService(Dispatcher::class, function (Container $container, $args = []) {
            $args += [
                'controllerFormat' => '',
                'errorController' => '',
            ];
            $controllerFactory = new ControllerFactory($args['controllerFormat'], $args['errorController']);
            unset($args['controllerFormat'], $args['errorController']);

            return new Dispatcher($container, $controllerFactory);
        }, $this->prefix('dispatcher'));

        $container->autoregisterService(MailService::class, $this->prefix('mail'));
    }
}
