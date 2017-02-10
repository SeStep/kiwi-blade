<?php

namespace KiwiBladeTests\DI;


use KiwiBlade\Forms\FormFactory;

class ComplexServiceDummy
{
    public $formFactory;
    public $path;
    public $b;

    public function __construct(FormFactory $formFactory, $path, $b)
    {
        $this->formFactory = $formFactory;
        $this->path = $path;
        $this->b = $b;
    }
}
