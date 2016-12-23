<?php

namespace KiwiBlade\DI;

/**
 * Class Container
 */
class Container
{
    /** @var mixed[] */
    private $params = [];
    /** @var array service container with individual services or callbacks to get them */
    private $services = [];
    /** @var array name aliases for individual services */
    private $aliases = [];

    private $callstack = [];

    public function getByName($name)
    {
        if (!isset($this->aliases[$name])) {
            throw new ContainerException("No service named $name was registered");
        }

        return $this->get($this->aliases[$name]);
    }

    /** @inheritdoc */
    public function has($id)
    {
        return isset($this->services[$id]);
    }

    /** @inheritdoc */
    public function get($id, $forceNewInstance = false)
    {
        if($id == Container::class){
            return $this;
        }
        if (!$this->has($id)) {
            $parent = current($this->callstack) . ':' ?: '';
            throw new NotFoundException("$parent Service of type $id was not registered.");
        }

        if (in_array($id, $this->callstack)) {
            throw new ContainerException("Dependency loop detected: " . implode(', ', $this->callstack));
        }

        array_push($this->callstack, $id);

        $service = $this->services[$id];

        if ($forceNewInstance || !$service['instance']) {
            $service['instance'] = $this->createService($service);
            if ($service['instance'] === false) {
                throw new ContainerException($id, "No method to create instance provided");
            }

            if (!($service['instance'] instanceof $id)) {
                throw new ContainerException("Service $id callback returned errorneous type: " . get_class($service['instance']));
            }
            $this->services[$id] = $service;
        }

        array_pop($this->callstack);

        return $service['instance'];
    }

    private function createService(&$service)
    {
        $args = $service['name'] ? $this->getServiceArgs($service['name']) : [];

        if (isset($service['callback'])) {
            return $this->createServiceByCallback($service['callback'], $args);
        } elseif (isset($service['factory'])) {
            return $this->createServiceByFactory($service['factory'], $args);
        }

        return false;
    }

    private function getServiceArgs($name)
    {
        $args = $this->getParams($name);
        foreach ($args as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $length = strlen($value);
            if ($length < 2 || $value[0] != '%' || $value[$length - 1] != '%') {
                continue;
            }
            $field = substr($value, 1, $length - 2);
            if (!isset($this->params['parameters'][$field])) {
                throw new ContainerException("Dynamically requested parameter $value does not exist");
            }
            $args[$key] = $this->params['parameters'][$field];
        }

        return $args;
    }

    private function createServiceByCallback($callback, $args = [])
    {
        $func = new \ReflectionFunction($callback);
        $funcArgs = [];
        foreach ($func->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();
            if ($parameterClass && $parameterClass->getName() == Container::class) {
                $funcArgs[$parameter->getName()] = $this;
            } elseif ($parameter->getName() == 'args') {
                $funcArgs[$parameter->getName()] = $args;
            } else {
                throw new ContainerException("Could not assign callback argument " . $parameter->getName());
            }
        }

        return call_user_func_array($callback, $funcArgs);
    }

    private function createServiceByFactory($factory, $args = [])
    {
        $class = new \ReflectionClass($factory[0]);
        $func = $class->getMethod($factory[1]);

        $constructor = $class->getConstructor();
        $parameters = $constructor ? $constructor->getParameters() : [];

        $arguments = $this->getDependencies($factory[0], $parameters, ['args' => $args]);

        $factoryInstance = $class->newInstanceArgs($arguments);

        return $func->invoke($factoryInstance);

    }

    function setParameters($parameters)
    {
        $this->params = $parameters;
    }

    public function getParams($section = 'parameters', $default = null)
    {
        if (!isset($this->params[$section])) {
            if (!is_array($default)) {
                throw new ContainerException("Parameters section $section has to be specified");
            }

            return $default;
        }

        return $this->params[$section];
    }

    /**
     * @param $id
     * @param $callback callable($args = [])
     * @param string|null $name
     * @throws ContainerException
     */
    public function registerService($id, $callback, $name = null)
    {
        if ($name) {
            if (!is_string($name)) {
                throw new ContainerException($id, 'Name must be a string, ' . gettype($name) . ' given');
            }
            if (isset($this->aliases[$name])) {
                throw new ContainerException($id, 'Duplicate name detected: ' . $name);
            }
        }

        $service = [
            'instance' => null,
            'name' => $name,
        ];

        if (is_array($callback)) {
            if (!isset($callback[0]) || !isset($callback[1])) {
                throw new ContainerException($id, 'Factory callback invalid fields');
            }
            if (!class_exists($callback[0])) {
                throw new ContainerException($id, "Factory class '$callback[0]' does not exist");
            }
            if (!method_exists($callback[0], $callback[1])) {
                throw new ContainerException($id,
                    "Factory class '$callback[0]' does not contain method '$callback[1]'");
            }
            $service['factory'] = $callback;
        } else {
            $service['callback'] = $callback;
        }


        $this->services[$id] = $service;

        if ($name) {
            $this->aliases[$name] = $id;
        }
    }

    public function autoregisterService($className, $name = null)
    {
        $this->registerService($className, function ($args = []) use ($className) {
            try {
                $class = new \ReflectionClass($className);

                $parameters = $class->getConstructor()->getParameters();

                $surplus = $this->verifyArguments($parameters, $args);
                if (!empty($surplus)) {
                    throw new ContainerException("Config arguments of service $className contain these invalid fields " .
                        implode(', ', $surplus));
                }

                $arguments = $this->getDependencies($className, $parameters, $args);


                return $class->newInstanceArgs($arguments);
            } catch (\ReflectionException $ex) {
                throw new ContainerException($className, "Instantiation failed", $ex);
            }

        }, $name);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @throws ContainerException
     */
    private function getDependencies($className, $parameters, $args = [])
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (isset($args[$name])) {
                $arguments[$name] = $args[$name];
                continue;
            }

            $parClass = $parameter->getClass() ? $parameter->getClass()->getName() : '';

            if ($parClass) {
                try {
                    $arguments[$name] = $this->get($parClass);
                } catch (NotFoundException $ex) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $arguments[$name] = $parameter->getDefaultValue();
                    } else {
                        throw new ContainerException($ex->getMessage(), 0, $ex);
                    }

                }

            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[$name] = $parameter->getDefaultValue();
            } else {
                throw new ContainerException("Argument $name of class $className does not have type hint or " .
                    "default value.");
            }
        }

        return $arguments;
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array $args
     * @return array
     */
    private function verifyArguments($parameters, $args = [])
    {
        $assocParameters = [];
        foreach ($parameters as $parameter) {
            $assocParameters[$parameter->getName()] = $parameter;
        }

        $diff = array_diff_key($args, $assocParameters);

        return array_keys($diff);

    }
}
