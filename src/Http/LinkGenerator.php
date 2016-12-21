<?php

namespace KiwiBlade\Http;

use InvalidArgumentException;

class LinkGenerator
{
    /** @var string */
    private $baseUrl;
    /** @var boolean */
    private $niceUrls;
    /** @var Request */
    private $request;

    public function __construct(Request $request, $niceUrls)
    {
        $this->niceUrls = $niceUrls;
        $this->request = $request;
        $this->baseUrl = $request->getBaseUrl();
    }

    public function linkSegmented($controller, $action = '', $parameters = [])
    {
        if (!is_array($parameters)) {
            throw new InvalidArgumentException("Arguument parameters must be array, " . gettype($parameters) . ' given');
        }

        $fields = [
            'controller' => $controller,
        ];
        if ($action) {
            $fields['action'] = $action;
        }

        if (!empty($parameters)) {
            foreach ($parameters as $k => $v) {
                $fields[$k] = $v;
            }
        }


        return $this->buildQuery($fields);
    }

    public function buildQuery($fields, $baseUrl = null)
    {
        if(!$baseUrl){
            $baseUrl = $this->baseUrl;
        }

        if ($this->niceUrls) {
            return $baseUrl . UrlHelper::buildNiceUrl($fields);
        } else {
            return $baseUrl . '?' . UrlHelper::buildQuery($fields);
        }
    }

    public function link($target, $parameters = [])
    {
        $actionOnly = strpos($target, ':') === false;
        if ($actionOnly) {
            return $this->linkSegmented($this->request->getController(), $target, $parameters);
        } else {
            $parts = explode(':', $target);

            return $this->linkSegmented($parts[0], $parts[1], $parameters);
        }
    }

    public function css($file)
    {
        return $this->baseUrl . "css/" . $file;
    }

    public function js($file)
    {
        return $this->baseUrl . "js/" . $file;
    }
}
