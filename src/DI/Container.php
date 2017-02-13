<?php

namespace KiwiBlade\DI;

/**
 * Class Container
 */
class Container
{
    /** @var mixed[] */
    private $params = [];
    /** @var mixed[] */
    private $extensionParams = [];
    /** @var array service container with individual services or callbacks to get them */
    private $services = [];
    /** @var array name aliases for individual services */
    private $aliases = [];

    private $callstack = [];

    public function __construct($parameters, $extensionParams = [])
    {
        $this->params = $parameters;
        $this->extensionParams = $extensionParams;
    }

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
        if ($id == Container::class) {
            return $this;
        }
        if (!$this->has($id)) {
            $parent = current($this->callstack) . ':' ?: '';
            throw new NotFoundException("get($parent) failed because service of type $id was not registered.");
        }

        if (in_array($id, $this->callstack)) {
            throw new ContainerException("Dependency loop detected: " . implode(', ', $this->callstack));
        }

        array_push($this->callstack, $id);

        $service = $this->services[$id];

        if ($forceNewInstance || !$service['instance']) {
            $service['instance'] = $this->createService($service, $id);
            $this->services[$id] = $service;
        }

        array_pop($this->callstack);

        return $service['instance'];
    }

    /**
     * @param $service
     * @param $id
     * @return mixed
     * @throws ContainerException
     */
    private function createService(&$service, $id)
    {
        $args = $service['name'] ? $this->getServiceParams($service['name']) : [];

        if (isset($service['callback'])) {
            $instance = $this->createServiceByCallback($service['callback'], $args);
            if (!($instance instanceof $id)) {
                throw new ContainerException("Service $id callback returned errorneous type: " . get_class($instance));
            }

            return $instance;
        } elseif (isset($service['factory'])) {
            return $this->createServiceByFactory($service['factory'], $args);
        }

        throw new ContainerException($id, "No method to create instance provided");
    }

    private function replaceDynamicArguments($array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $array[$key] = $this->replaceDynamicArguments($value);
            }
            if (!is_string($value)) {
                continue;
            }
            $length = strlen($value);
            if ($length < 2 || $value[0] != '%' || $value[$length - 1] != '%') {
                continue;
            }
            $field = substr($value, 1, $length - 2);
            if (!isset($this->params[$field])) {
                throw new ContainerException("Dynamically requested parameter $value does not exist");
            }
            $array[$key] = $this->params[$field];
        }

        return $array;
    }

    private function getServiceParams($identifier)
    {
        list($extName, $name) = explode('.', $identifier);

        if (!array_key_exists($extName, $this->extensionParams) || !array_key_exists($name,
                $this->extensionParams[$extName])
        ) {
            throw new ContainerException("Service parameters specifiaction for $identifier is missing");
        }

        $args = $this->extensionParams[$extName][$name];
        $args = $this->replaceDynamicArguments($args);

        return $args;
    }

    private function createServiceByCallback($callback, $args = [])
    {
        $func = new \ReflectionFunction($callback);
        $funcArgs = $this->getDependencies($func, ['args' => $args]);

        return call_user_func_array($callback, $funcArgs);
    }

    private function createServiceByFactory($factory, $args = [])
    {
        $class = new \ReflectionClass($factory[0]);
        $func = $class->getMethod($factory[1]);

        $constructor = $class->getConstructor();
        if ($constructor) {
            $arguments = $this->getDependencies($constructor, $args);
            $factoryInstance = $class->newInstanceArgs($arguments);
        } else {
            $factoryInstance = $class->newInstance();
        }

        $arguments = $this->getDependencies($func, $args);

        return $func->invokeArgs($factoryInstance, $arguments);
    }

    public function getParams()
    {
        return $this->params;
    }

    private function defineService($id, $type, $creator, $name = '')
    {
        if ($name) {
            if (!is_string($name)) {
                throw new ContainerException($id, 'Name must be a string, ' . gettype($name) . ' given');
            }
            if (isset($this->aliases[$name])) {
                throw new ContainerException($id, 'Duplicate name detected: ' . $name);
            }
        }

        $this->services[$id] = [
            'instance' => null,
            'name' => $name,
            $type => $creator,
        ];

        if ($name) {
            $this->aliases[$name] = $id;
        }
    }

    /**
     * @param             $id
     * @param             $callback callable($args = [])
     * @param string|null $name
     * @throws ContainerException
     */
    public function registerService($id, $callback, $name = '')
    {
        $this->defineService($id, 'callback', $callback, $name);
    }

    public function registerServiceFactory($id, $factory, $name = '')
    {
        if (!isset($factory[0]) || !isset($factory[1])) {
            throw new ContainerException($id, 'Factory callback invalid fields');
        }
        if (!class_exists($factory[0])) {
            throw new ContainerException($id, "Factory class '$factory[0]' does not exist");
        }
        if (!method_exists($factory[0], $factory[1])) {
            throw new ContainerException($id, "Factory class '$factory[0]' does not contain method '$factory[1]'");
        }

        $this->defineService($id, 'factory', $factory, $name);
    }

    public function autoregisterService($className, $name = '')
    {
        $callback = function ($args = []) use ($className) {
            try {
                $class = new \ReflectionClass($className);

                $arguments = $this->getDependencies($class->getConstructor(), $args);

                return $class->newInstanceArgs($arguments);
            } catch (\ReflectionException $ex) {
                throw new ContainerException($className, "Instantiation failed", $ex);
            }
        };
        $this->registerService($className, $callback, $name);
    }

    /**
     * @param \ReflectionMethod|\ReflectionFunction $function
     * @param array             $args
     * @return \mixed[]
     */
    private function getDependencies($function, $args = [])
    {
        $parameters = $function->getParameters();

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
                $functionName = $function->getDeclaringClass()->getName() . "->" . $function->getName();
                throw new ContainerException("Argument $name of function $functionName does not have type hint or default value.");
            }
        }

        return $arguments;
    }

    public function resetCallstack()
    {
        $this->callstack = [];
    }
}
