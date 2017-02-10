<?php

namespace KiwiBlade\Http;


use InvalidArgumentException;

class Request
{
    const INPUT_POST = \INPUT_POST;
    const INPUT_GET = \INPUT_GET;

    /** @var string */
    private $controller = '';
    /** @var string */
    private $action = '';

    /** @var array[] */
    private $input = [];
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $rootUrl;

    public function __construct($input = [], $controller, $action = '', $baseUrl = '', $rootUrl = '')
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException("Argument input must be array, " . gettype($input) . " given");
        }
        $this->controller = $controller;
        $this->action = $action;

        $this->input = array_merge($this->input, $input);
        $this->baseUrl = $baseUrl;
        $this->rootUrl = $rootUrl;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getRootUrl()
    {
        return $this->rootUrl;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }


    public function hasParam($name, $input = INPUT_GET)
    {
        $group = $this->getParams($input);

        return isset($group[$name]);
    }

    public function getParam($name, $input = INPUT_GET)
    {
        if (!$this->hasParam($name, $input)) {
            return null;
        }

        return $this->getParams($input)[$name];
    }

    public function getParams($input)
    {
        if (!isset($this->input[$input])) {
            throw new InvalidArgumentException("Parameter group $input was not set");
        }

        return $this->input[$input];
    }
}
