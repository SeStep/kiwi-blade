<?php

namespace KiwiBlade\Storage;


interface IFileStorage
{

    public function __construct($file);

    /**
     * @return mixed[]
     */
    public function load();

    /**
     * @return bool
     */
    public function save($vars);
}
