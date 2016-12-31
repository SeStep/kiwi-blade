<?php

namespace KiwiBlade\View;

interface IErrorController extends IController
{
    const NO_CONTROLLER_FOUND = 1;
    const NOT_RECOGNISED_ACTION = 2;
    const NO_TEMPLATE = 3;
    const NO_RENDER_OR_REDIRECT = 4;
    const UNCAUGHT_EXCEPTION = 5;

    /**
     * @param int $errType
     * @param string $c
     * @param string $a
     * @return void mixed
     */
    public function renderError($errType, $c, $a);
}
