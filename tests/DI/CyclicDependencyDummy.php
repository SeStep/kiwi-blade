<?php

namespace KiwiBladeTests\DI;


class CyclicDependencyDummy
{
    public function __construct(CyclicDependencyDummy $parent)
    {

    }
}
