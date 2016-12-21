<?php

namespace KiwiBlade\Security;


use DateTime;

interface IUser
{
    /** @return boolean */
    public function isLoggedIn();

    /**
     * @return DateTime
     */
    public function getLastActive();
}
