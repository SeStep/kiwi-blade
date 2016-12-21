<?php

namespace KiwiBlade\Bridges\Tracy;


use Tracy\IBarPanel;

class RequestPanel implements IBarPanel
{

    /** @var string */
    private $controller;
    /** @var string */
    private $action;
    /** @var bool */
    private $error;

    public function __construct($controller, $action)
    {
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * @param bool $value
     */
    public function setError($value)
    {
        $this->error = (bool)$value;
    }

    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    function getTab()
    {
        $controller = $this->controller;
        $action = $this->action;
        $error = (bool)$this->error;

        ob_start();
        require __DIR__ . '/requestPanel.tab.phtml.php';

        return ob_get_clean();
    }

    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    function getPanel()
    {
        return '';
    }
}
