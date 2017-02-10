<?php

namespace KiwiBlade\DI;


use KiwiBlade\Bridges\Twig\TwigKBExtension;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;

class TwigFactory
{
    public function create(LinkGenerator $linkGenerator, Request $request, $args)
    {
        $loader = new \Twig_Loader_Filesystem([], $args['appDir']);
        foreach ($args['templatePaths'] as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->addPath($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }

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
    }
}
