<?php

namespace KiwiBlade\Http;


class UrlHelper
{
    const CONTROLLER = 'controller';
    const ACTION = 'action';

    public static function parseNiceString($string, $separator = '/')
    {
        $parts = explode($separator, $string);

        $controller = array_shift($parts) ?: '';
        $action = array_shift($parts) ?: '';

        $parts[self::CONTROLLER] = $controller;
        $parts[self::ACTION] = $action;

        return $parts;
    }

    public static function buildNiceUrl($params = [], $separator = '/')
    {
        $return = '';
        if (empty($params)) {
            return $return;
        }
        if (isset($params[self::CONTROLLER])) {
            $return .= $params[self::CONTROLLER] . $separator;
            unset($params[self::CONTROLLER]);
        }
        if (isset($params[self::ACTION])) {
            $return .= $params[self::ACTION] . $separator;
            unset($params[self::ACTION]);
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
