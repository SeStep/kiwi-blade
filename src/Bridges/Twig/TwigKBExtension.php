<?php

namespace KiwiBlade\Bridges\Twig;

use KiwiBlade\Http\LinkGenerator;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFunction;

class TwigKBExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var array */
    private $globals;

    public function __construct(LinkGenerator $linkGenerator, $baseUrl, $rootUrl = '')
    {
        $this->linkGenerator = $linkGenerator;
        $this->globals = [
            'baseUrl' => $baseUrl,
            'rootUrl' => $rootUrl,
        ];
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('link', [$this->linkGenerator, 'link']),
        ];
    }
    public function getGlobals()
    {
        return $this->globals;
    }
}
