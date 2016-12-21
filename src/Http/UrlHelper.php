<?php

namespace KiwiBlade\Http;


class UrlHelper
{
    public static function parseNiceString($string, $separator = '/')
    {
        $parts = explode($separator, $string);

        $controller = array_shift($parts) ?: '';
        $action = array_shift($parts) ?: '';

        $parts['controller'] = $controller;
        $parts['action'] = $action;

        return $parts;
    }

    public static function buildNiceUrl($params = [], $separator = '/')
    {
        $return = '';
        if (empty($params)) {
            return $return;
        }
        if (isset($params['controller'])) {
            $return .= $params['controller'] . $separator;
            unset($params['controller']);
        }
        if (isset($params['action'])) {
            $return .= $params['action'] . $separator;
            unset($params['action']);
        }

        return $return . self::buildQuery($params);
    }

    public static function buildQuery($params)
    {
        $keyVals = [];

        foreach ($params as $parKey => $parVal) {
            $keyVals[] = $parKey . ($parVal ? '=' . $parVal : '');
        }

        return implode('&', $keyVals);
    }
}
