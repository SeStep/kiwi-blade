<?php

namespace KiwiBlade\Mail;

/**
 * Class Message containing fields required for mail to be composed.
 * Do not call send() directly
 * @package KiwiBlade\Mail
 */
class Message extends \PHPMailer
{
    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * Do not call directyly, use apropriate mailer function
     * @internal
     */
    public function send()
    {
        try {
            return parent::send();
        } catch (\phpmailerException $ex) {
            return $ex->getMessage();
        }
    }
}
