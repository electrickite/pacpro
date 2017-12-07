<?php

class Repository extends ProviderBase
{
    public static function all() {
        $repositories = [];
        foreach (glob(self::$base_path . '/*/info.yml') as $file) {
            $repositories[] = new Repository(basename(dirname($file)));
        }
        return $repositories;
    }

    public function __construct($path) {
        parent::__construct($path);
        $this->setInfo();
    }

    public function totalPackages() {
        return count(glob($this->path . '/*/info.yml'));
    }
}
