<?php

namespace KiwiBlade\UrlAlias;


class UrlAliaser
{
    private $aliases;
    private $aliasMap;

    private $controllers;
    private $localisedControllers;

    public function __construct($aliases, $controllers = [])
    {
        $this->setAliases($aliases);
        $this->setControllers($controllers);
    }

    /**
     * @param string $controller raw controller name
     * @param string $action raw action name
     * @return array specifier array including 'controller' and 'action' offsets
     */
    public function encode($controller, $action)
    {
        try {
            $localised = $this->translate($this->controllers, $controller, $action, 'Real => Localised');
            if ($this->aliasMapContains($localised['controller'], $localised['action'])) {
                return [
                    'controller' => $this->aliasMap[$localised['controller']][$localised['action']],
                    'action' => '',
                ];
            }

            return $localised;
        } catch (AliasException $ex) {
            throw new AliasException("No alias for $controller:$action was found");
        }
    }

    /**
     * @param $controller
     * @param $action
     * @return array
     */
    public function tryEncodeAlias($controller, $action)
    {
        $exists = $this->aliasMapContains($controller, $action);

        return [
            'controller' => $exists ? $this->aliasMap[$controller][$action] : $controller,
            'action' => $exists ? '' : $action,
        ];
    }


    public function aliasExists($alias)
    {
        return array_key_exists($alias, $this->aliases);
    }

    public function aliasMapContains($controller, $action)
    {
        return array_key_exists($controller, $this->aliasMap) &&
        array_key_exists($action, $this->aliasMap[$controller]);
    }

    public function decodeAlias($alias)
    {
        return $this->aliasExists($alias) ? $this->aliases[$alias] : null;
    }

    /**
     * @param string $controller Localised controller name
     * @param string $action Localised action name
     * @return array
     */
    public function decodeParts($controller, $action)
    {
        return $this->translate($this->localisedControllers, $controller, $action, 'Localised => Real');
    }

    private function translate($array, $controller, $action, $direction)
    {
        if (!isset($array[$controller])) {
            throw new AliasException("Localised controller $controller does not exist in '$direction'");
        }

        $parts = [
            'controller' => $array[$controller]['name'],
            'action' => '',
        ];

        if ($action) {
            if (!isset($array[$controller]['actions'][$action])) {
                throw new AliasException("Localised action $controller:$action does not exist in '$direction'");
            }
            $parts['action'] = $array[$controller]['actions'][$action];
        }

        return $parts;
    }

    private function setAliases($aliases)
    {
        $this->aliases = [];
        $this->aliasMap = [];
        foreach ($aliases as $name => $alias) {
            if (!is_array($alias) || sizeof($alias) != 2) {
                throw new \InvalidArgumentException("Alias $name is not valid");
            }
            list($controller, $action) = $alias;

            if (isset($this->aliasMap[$controller][$name])) {
                throw new \InvalidArgumentException("Duplicate alias for $controller:$name");
            }
            $this->aliases[$name] = [
                'controller' => $controller,
                'action' => $action,
            ];
            $this->aliasMap[$controller][$action] = $name;
        }
    }

    private function setControllers($controllers)
    {
        $this->controllers = [];
        $this->localisedControllers = [];

        foreach ($controllers as $controller => $definition) {
            if (!is_array($definition) || !array_key_exists('name', $definition) ||
                !array_key_exists('actions', $definition) || !is_array($definition['actions'])
            ) {
                throw new \InvalidArgumentException("Controller alias $controller is not valid");
            }
            $this->putController($controller, $definition['name'], $definition['actions']);
        }
    }

    private function putController($name, $localisedName, $actions)
    {
        if (isset($this->localisedControllers[$localisedName])) {
            throw new \InvalidArgumentException("Localised controller name $localisedName is already defined");
        }

        $this->controllers[$name] = ['name' => $localisedName];
        $this->localisedControllers[$localisedName] = ['name' => $name];


        foreach ($actions as $action => $localisedAction) {
            if (isset($this->localisedControllers[$localisedName][$localisedAction])) {
                throw new \InvalidArgumentException("Localised controller action $localisedName:$localisedAction is already defined");
            }
            $this->controllers[$name]['actions'][$action] = $localisedAction;
            $this->localisedControllers[$localisedName]['actions'][$localisedAction] = $action;
        }
    }
}
