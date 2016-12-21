<?php

namespace KiwiBlade\Core;


interface IController
{
    /**
     * Initialisation method. Overriding methods must always call parent::startUp();
     * @return null
     */
    public function startUp();

    /**
     * Returns name of layout to be used for current view.
     * @return string
     * @internal
     */
    public function getLayout();

    /**
     * Gets current template variables.
     * @return mixed[]
     * @internal
     */
    public function getTemplate();
}
