<?php

namespace KiwiBlade\Extensions;


use KiwiBlade\DI\AContainerExtension;
use KiwiBlade\DI\Container;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use Twig_SimpleFunction;

class TwigExtension extends AContainerExtension
{
    public function registerServices(Container $container)
    {
        $container->registerService(\Twig_Loader_Filesystem::class, function ($args) {
            $loader = new \Twig_Loader_Filesystem([], $args['appDir']);
            foreach ($args['templatePaths'] as $namespace => $path) {
                if (is_string($namespace)) {
                    $loader->addPath($path, $namespace);
                } else {
                    $loader->addPath($path);
                }
            }

            return $loader;
        }, $this->prefix('loader'));
        $container->registerService(\Twig_Environment::class,
            function (\Twig_Loader_Filesystem $loader, LinkGenerator $linkGenerator, Request $request, $args) {
                $environmentConfig = [
                    'debug' => $args['debug'],
                ];
                if (array_key_exists('cache', $args) && is_string($args['cache'])) {
                    $environmentConfig['cache'] = $args['cache'];
                }

                $twig = new \Twig_Environment($loader, $environmentConfig);

                $twig->addFunction(new Twig_SimpleFunction('link', [$linkGenerator, 'link']));
                $twig->addGlobal('baseUrl', $request->getBaseUrl());
                $twig->addGlobal('rootUrl', $request->getRootUrl());

                if ($args['debug']) {
                    $twig->addExtension(new \Twig_Extension_Debug());
                }
                if (is_array($args['extensions'])) {
                    foreach ($args['extensions'] as $extClass) {
                        $twig->addExtension(new $extClass());
                    }
                }

                return $twig;
            }, $this->prefix('environment'));
    }
}
