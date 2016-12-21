<?php

namespace KiwiBlade\Core;

/**
 * MessageBuffer is used to store and track messages
 *
 * @author Stepan
 */
class Alerter
{
    const LVL_PRI = 1;
    const LVL_SUC = 2;
    const LVL_INF = 4;
    const LVL_WAR = 8;
    const LVL_DNG = 16;
    const LVL_ALL = 31;
    const PRT_LVL = 'level';
    const PRT_MSG = 'text';
    const PRT_LNK = 'link';

    /**
     * Array which holds string equivalents to translate the defined keys
     * @var array
     */
    static $MSG_LEVELS = [
        self::LVL_PRI => 'primary',
        self::LVL_SUC => "success",
        self::LVL_INF => 'info',
        self::LVL_WAR => 'warning',
        self::LVL_DNG => 'danger'
    ];

    /** @var Alerter[] */
    private static $instances = [];

    private $session_key;

    /**
     * Private constructor to disable free creating of instances. To generate instance
     * use static method getInstance.
     *
     * @param string $session_key session identifier for current instance
     */
    protected function __construct($session_key)
    {
        $this->session_key = $session_key;

        if (!isset($_SESSION[$this->session_key])) {
            $_SESSION[$this->session_key] = [];
        }
    }

    /**
     * Factory method that returns either singleton or completely new instance based
     * on param $newInstance.
     *
     * @param string $sessionKey decides if new instance should be created
     * @return Alerter
     */
    public static function getInstance($sessionKey = '')
    {
        $sessionKey = $sessionKey ?: __CLASS__;

        if (!isset(self::$instances[$sessionKey])) {
            self::$instances[$sessionKey] = new Alerter($sessionKey);
        }

        return self::$instances[$sessionKey];
    }

    /**
     * Stores provided message and it's level as an array in the $messages property.
     * Checks wether the provided level is valid and has it's string
     * representation and if not, changes it to default value.
     *
     * @param string $message
     * @param int $level
     * @param array $link a link to be shown along with net alert
     */
    protected function alert($message, $level = self::LVL_INF, $link = null)
    {
        if (!self::validLevel($level)) {
            $level = self::LVL_INF;
        }
        $_SESSION[$this->session_key][] = [
            self::PRT_MSG => $message,
            self::PRT_LVL => $level,
            self::PRT_LNK => self::validLink($link) ? $link : null,
        ];
    }

    public function alertPrimary($message, $link = null)
    {
        $this->alert($message, Alerter::LVL_PRI, $link);
    }

    public function alertSuccess($message, $link = null)
    {
        $this->alert($message, Alerter::LVL_SUC, $link);
    }

    public function alertInfo($message, $link = null)
    {
        $this->alert($message, Alerter::LVL_INF, $link);
    }

    public function alertWarning($message, $link = null)
    {
        $this->alert($message, Alerter::LVL_WAR, $link);
    }

    public function alertDanger($message, $link = null)
    {
        $this->alert($message, Alerter::LVL_DNG, $link);
    }

    /**
     * Filters messages based on provided $levelFilter and possibly fetches
     * just their text part, leaving their level behind, for easier array
     * accessing.
     *
     * @param int $levelFilter Bit flag that decides for each message level if
     * it is to be included in final array. By default all types are included.
     *
     * @return array
     */
    public function getAlerts($levelFilter = self::LVL_ALL)
    {
        if (!isset($_SESSION[$this->session_key])) {
            return [];
        }

        $filtered = [];
        foreach ($_SESSION[$this->session_key] as $entry) {
            if ($entry[self::PRT_LVL] & $levelFilter) {
                $filtered[] = $entry;
            }
        }

        unset($_SESSION[$this->session_key]);

        return $this->numToLvl($filtered);
    }

    public function mergeFrom(Alerter $other, $clearOther = true)
    {
        if (!isset($_SESSION[$other->session_key])) {
            return;
        }

        foreach ($_SESSION[$other->session_key] as $item) {
            $_SESSION[$this->session_key][] = $item;
        }
        if ($clearOther) {
            unset($_SESSION[$other->session_key]);
        }
    }

    private function numToLvl($messages)
    {
        foreach ($messages as $key => $msg) {
            $messages[$key][self::PRT_LVL] = self::$MSG_LEVELS[$msg[self::PRT_LVL]];
        }

        return $messages;
    }


    /**
     * Checks if provided message level has it's string representation.
     *
     * @param int $level
     * @return boolean
     */
    private static function validLevel($level)
    {
        return array_key_exists($level, self::$MSG_LEVELS);
    }

    private static function validLink($link)
    {
        return is_array($link) && isset($link['url']) && isset($link['label']);
    }
}

