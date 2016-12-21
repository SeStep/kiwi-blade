<?php

namespace KiwiBlade\Mail;

class MailService
{
    protected $mailFrom;

    protected $sent = 0;

    protected $lastError = null;

    public function __construct($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    public function getSentMails()
    {
        return $this->sent;
    }

    /**
     * @param string $subject
     * @param string|string[] $to
     * @return Message
     */
    public function create($subject = '', $to = [])
    {
        $mail = new Message(true);
        $mail->setLanguage('cz');
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($this->mailFrom);
        $mail->Subject = $subject;
        $mail->isSMTP();

        if (!is_array($to)) {
            $to = [$to];
        }
        foreach ($to as $emailAddress) {
            $mail->addAddress($emailAddress);
        }

        return $mail;
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function send(Message $message)
    {
        $result = $message->send();

        if ($result === true) {
            $this->sent++;
        } else {
            $this->lastError = $result;
            $result = false;
        }

        return $result;
    }

    public function getLastError($clear = true)
    {
        $error = $this->lastError;
        if ($clear) {
            $this->lastError = null;
        }

        return $error;

    }
}
