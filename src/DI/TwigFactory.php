<?php

namespace KiwiBlade\DI;


use KiwiBlade\Bridges\Twig\TwigKBExtension;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;

class TwigFactory
{
    /** @var Container */
    private $container;
    /** @var mixed[] */
    private $args;

    public function __construct(Container $container, $args)
    {
        $this->container = $container;
        $this->args = $args;
    }

    public function create()
    {
        /** @var LinkGenerator $linkGenerator */
        $linkGenerator = $this->container->get(LinkGenerator::class);
        /** @var Request $request */
        $request = $this->container->get(Request::class);


        $loader = new \Twig_Loader_Filesystem([], $this->container->getParams()['appDir']);
        foreach ($this->args['templatePaths'] as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->addPath($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }

        $twig = new \Twig_Environment($loader, array(
            /* 'cache' => __DIR__.'/cache/', */
            'debug' => $this->args['debug'],
        ));

        $twig->addExtension(new TwigKBExtension($linkGenerator, $request->getBaseUrl(), $request->getRootUrl()));
        if ($this->args['debug']) {
            $twig->addExtension(new \Twig_Extension_Debug());

        }
        if (is_array($this->args['extensions'])) {
            foreach ($this->args['extensions'] as $extClass) {
                $twig->addExtension(new $extClass());
            }
        }

        return $twig;
    }
}
