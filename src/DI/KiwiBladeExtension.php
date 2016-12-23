<?php

namespace KiwiBlade\DI;

use KiwiBlade\Core\Dispatcher;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use KiwiBlade\Http\RequestFactory;
use KiwiBlade\Mail\MailService;
use KiwiBlade\View\ControllerFactory;

class KiwiBladeExtension extends AContainerExtension
{
    public function registerServices(Container $container)
    {
        $container->registerService(Request::class, function (Container $container) {
            $params = $container->getParams();
            $factory = new RequestFactory($params['niceUrl'], $params['wwwSubfolder'], $params['defaultController']);

            return $factory->create();
        });

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
        }, 'dispatcher');

        $container->registerService(\Twig_Environment::class, [TwigFactory::class, 'create'], 'twig');

        $container->autoregisterService(MailService::class, 'mail');
    }
}
