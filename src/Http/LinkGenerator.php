<?php

namespace KiwiBlade\Http;

use InvalidArgumentException;

class LinkGenerator
{
    /** @var string */
    private $baseUrl;
    /** @var boolean */
    private $niceUrl;
    /** @var string */
    private $currentController;

    public function __construct($baseUrl, $niceUrl, $currentController)
    {
        $this->baseUrl = $baseUrl;
        $this->niceUrl = $niceUrl;
        $this->currentController = $currentController;
    }

    public function link($target, $parameters = [])
    {
        $actionOnly = strpos($target, ':') === false;
        if ($actionOnly) {
            return $this->linkSegmented($this->currentController, $target, $parameters);
        } else {
            $parts = explode(':', $target);

            return $this->linkSegmented($parts[0], $parts[1], $parameters);
        }
    }

    public function linkSegmented($controller, $action = '', $parameters = [])
    {
        if (!is_array($parameters)) {
            throw new InvalidArgumentException("Arguument parameters must be array, " . gettype($parameters) . ' given');
        }

        $fields = ['controller' => $controller];
        if ($action) {
            $fields['action'] = $action;
        }

        if (!empty($parameters)) {
            foreach ($parameters as $k => $v) {
                $fields[$k] = $v;
            }
        }

        return $this->createLink($fields);
    }

    public function createLink($fields, $baseUrl = null)
    {
        if (!$baseUrl) {
            $baseUrl = $this->baseUrl;
        }

        if ($this->niceUrl) {
            return $baseUrl . UrlHelper::buildNiceUrl($fields);
        } else {
            return $baseUrl . '?' . UrlHelper::buildQuery($fields);
        }
    }
}
