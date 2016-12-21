<?php

namespace KiwiBladeTests\DI;


class ABDummy
{
    public $a;
    public $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
