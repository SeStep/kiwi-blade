<?php

namespace KiwiBlade\Security;


interface IUser
{
    /** @return boolean */
    public function isLoggedIn();
}
