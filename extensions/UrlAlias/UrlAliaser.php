<?php

namespace KiwiBladeExtensions\UrlAlias;


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

    public function encode($controller, $action)
    {
        if (array_key_exists($controller, $this->aliasMap)) {
            if (array_key_exists($action, $this->aliasMap[$controller])) {
                return $this->aliasMap[$controller][$action];
            }
        }


        return null;
    }

    public function aliasExists($alias)
    {
        return array_key_exists($alias, $this->aliases);
    }

    public function decodeAlias($alias)
    {
        if ($this->aliasExists($alias)) {
            return $this->aliases[$alias];
        }

        return null;
    }

    public function decodeParts($controller, $action){
        if(!isset($this->localisedControllers[$controller])){
            throw new \InvalidArgumentException("Localised controller $controller does not exist");
        }
        if(!isset($this->localisedControllers[$controller]['actions'][$action])){
            throw new \InvalidArgumentException("Localised action $controller:$action does not exist");
        }
        return [
            'controller' => $this->localisedControllers[$controller]['name'],
            'action' => $this->localisedControllers[$controller]['actions'][$action],
        ];
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
