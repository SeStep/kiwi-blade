<?php

namespace KiwiBlade\Forms;


use InvalidArgumentException;

class Form
{
    const
        METHOD_POST = 'post',
        METHOD_GET = 'get';

    /** @var string */
    protected $action;
    /** @var string */
    protected $method;

    protected $fields = [];

    /** @var mixed[] */
    protected $defaults = [];

    public function __construct($action, $method = self::METHOD_POST)
    {
        $this->action = $action;
        $this->method = $method;
    }

    /** @return string */
    public function getAction()
    {
        return $this->action;
    }

    /** @param string $action */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /** @return string */
    public function getMethod()
    {
        return $this->method;
    }

    /** @param string $method */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setDefaults($defaults)
    {
        if (!is_array($defaults)) {
            throw new InvalidArgumentException();
        }
        $this->defaults = $defaults;
    }

    public function setField($name, $value)
    {
        $this->fields[$name] = $value;
    }

    /** @return array */
    public function getFields()
    {
        return $this->fields;
    }

    /** @param array $fields */
    protected function setFields($fields)
    {
        if (!is_array($fields)) {
            throw new InvalidArgumentException();
        }
        $this->fields = $fields;
    }

    public function processFormData($data){
        return $data;
    }


}
