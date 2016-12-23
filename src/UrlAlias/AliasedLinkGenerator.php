<?php

namespace KiwiBlade\UrlAlias;


use KiwiBlade\Http\LinkGenerator;

class AliasedLinkGenerator extends LinkGenerator
{
    /** @var UrlAliaser */
    private $aliaser;

    public function __construct($baseUrl, $niceUrl, $currentController, UrlAliaser $aliaser)
    {
        parent::__construct($baseUrl, $niceUrl, $currentController);
        $this->aliaser = $aliaser;
    }

    public function linkSegmented($controller, $action = '', $parameters = [])
    {
        $parts = $this->aliaser->encode($controller, $action);
        return parent::linkSegmented($parts['controller'], $parts['action'], $parameters);
    }
}
