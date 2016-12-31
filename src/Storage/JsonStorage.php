<?php

namespace KiwiBlade\Storage;


class JsonStorage implements IFileStorage
{
    private $path;

    public function __construct($path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $this->path = $path;
    }

    public function load()
    {
        if (!file_exists($this->path)) {
            return [];
        }
        $contents = file_get_contents($this->path);
        return (array)json_decode($contents);
    }

    public function save($vars)
    {
        $contents = json_encode($vars);
        return file_put_contents($this->path, $contents);
    }
}
