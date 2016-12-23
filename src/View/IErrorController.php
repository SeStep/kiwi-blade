<?php

namespace KiwiBlade\View;

interface IErrorController extends IController
{
    const NO_CONTROLLER_FOUND = 1;
    const NOT_RECOGNISED_ACTION = 2;
    const NO_TEMPLATE = 3;
    const NO_RENDER_OR_REDIRECT = 4;

    public function renderError($errType, $c, $a);
}
