<?php

namespace KiwiBladeTests\DI;


use KiwiBlade\Forms\FormFactory;

class ComplexServiceDummyFactory
{
    /** @var FormFactory */
    private $formFactory;
    private $path;

    public function __construct(FormFactory $formFactory, $path)
    {
        $this->formFactory = $formFactory;
        $this->path = $path;
    }

    public function create($b = null)
    {
        return new ComplexServiceDummy($this->formFactory, $this->path, $b);
    }
}
