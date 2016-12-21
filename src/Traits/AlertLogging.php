<?php

namespace KiwiBlade\Traits;


use KiwiBlade\Core\Alerter;

trait AlertLogging
{
    /** @var Alerter */
    private $alerter;

    protected function alerter()
    {
        if (!$this->alerter) {
            $this->alerter = Alerter::getInstance(get_class($this));
        }

        return $this->alerter;
    }

    public function getAlertLog($filter = Alerter::LVL_ALL)
    {
        return $this->alerter()->getAlerts($filter);
    }

    public function alertPrimary($message, $link = null)
    {
        $this->alerter()->alertPrimary($message, $link);
    }

    public function alertSuccess($message, $link = null)
    {
        $this->alerter()->alertSuccess($message, $link);
    }

    public function alertInfo($message, $link = null)
    {
        $this->alerter()->alertInfo($message, $link);
    }

    public function alertWarning($message, $link = null)
    {
        $this->alerter()->alertWarning($message, $link);
    }

    public function alertDanger($message, $link = null)
    {
        $this->alerter()->alertDanger($message, $link);
    }
}
