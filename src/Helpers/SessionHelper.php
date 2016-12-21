<?php

namespace KiwiBlade\Helpers;

/**
 * Description of SessionManager
 *
 * @author Stepan
 */
class SessionHelper
{

    const SESSION_TIMEOUT = 1800; // 30 minutes
    const SESSION_REVALIDATE = 600; // 10 minutes

    public static function start()
    {
        session_start();

        $time = time();
        if (isset($_SESSION['LAST_ACTIVITY']) && ($time - $_SESSION['LAST_ACTIVITY'] > self::SESSION_TIMEOUT)) {
            // last request was more than 30 minutes ago
            session_unset();  // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
        }
        $_SESSION['LAST_ACTIVITY'] = $time; // update last activity time stamp

        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = $time;
        } else {
            if ($time - $_SESSION['CREATED'] > self::SESSION_TIMEOUT) {
                // session started more than 30 minutes ago
                session_regenerate_id(true); // change session ID for the current session and invalidate old session ID
                $_SESSION['CREATED'] = $time;  // update creation time
            }
        }
    }
}
