<?php

namespace KiwiBlade\Core;


interface IExceptionHandler
{
    /**
     * @param \Throwable|\Exception $ex
     * @return void
     */
    public function handle($ex);
}
