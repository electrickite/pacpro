<?php

class Provider extends ProviderBase
{
    public function __construct() {
        $this->path = self::$base_path;
    }

    public function totalPackages() {
        return count(glob($this->path . '/*/*/info.yml'));
    }

    public function repositories() {
        return Repository::all();
    }
}
