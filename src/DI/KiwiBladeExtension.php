<?php

namespace KiwiBlade\DI;

use KiwiBlade\Bridges\Twig\TwigKBExtension;
use KiwiBlade\Core\Dispatcher;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use KiwiBlade\Http\UrlHelper;
use KiwiBlade\Mail\MailService;

class KiwiBladeExtension extends AConfiguratorExtension
{
    public function registerServices(Configurator $configurator, Container $container)
    {
        $container->registerService(Request::class, function (Container $container) {
            $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? "https" : "http";
            $rootUrl = $baseUrl = $protocol . "://$_SERVER[SERVER_NAME]/";

            $subfolder = $container->getParams()['wwwSubfolder'];
            if ($subfolder) {
                $baseUrl .= "$subfolder/";
            }

            $request = new Request([
                INPUT_POST => $_POST,
                INPUT_GET => $_GET,
            ], $baseUrl, $rootUrl);

            if ($container->getParams()['niceUrl']) {
                $input = UrlHelper::parseNiceString(filter_input(INPUT_GET, 'q'));
                $controller = $input['controller'];
                $action = $input['action'];
            } else {
                $controller = filter_input(INPUT_GET, 'controller') ?: '';
                $action = filter_input(INPUT_GET, 'action') ?: '';
            }
            $request->setController($controller ?: $container->getParams()['defaultController']);
            $request->setAction($action);


            return $request;
        });

        $container->registerService(LinkGenerator::class, function (Container $container) {
            /** @var Request $request */
            $request = $container->get(Request::class);

            return new LinkGenerator($request, (bool)$container->getParams()['niceUrl']);
        });

        $container->registerService(Dispatcher::class, function (Container $container) {
            $pars = $container->getParams();
            return new Dispatcher($container, $pars['wwwDir'], $pars['errorController']);
        });

        $container->registerService(\Twig_Environment::class, function (Container $container, $args) {
            /** @var LinkGenerator $linkGenerator */
            $linkGenerator = $container->get(LinkGenerator::class);
            /** @var Request $request */
            $request = $container->get(Request::class);


            $loader = new \Twig_Loader_Filesystem($args['templateDir']);
            $twig = new \Twig_Environment($loader, array(
                /* 'cache' => __DIR__.'/cache/', */
                'debug' => $args['debug'],
            ));

            $twig->addExtension(new TwigKBExtension($linkGenerator, $request->getBaseUrl(), $request->getRootUrl()));
            if ($args['debug']) {
                $twig->addExtension(new \Twig_Extension_Debug());

            }
            if (is_array($args['extensions'])) {
                foreach ($args['extensions'] as $extClass) {
                    $twig->addExtension(new $extClass());
                }
            }

            return $twig;
        }, 'twig');

        $container->autoregisterService(MailService::class, 'mail');
    }
}
