<?php

use Symfony\Component\Yaml\Yaml;

abstract class ProviderBase
{
    protected static $base_path;

    protected $path;
    protected $info = [];

    public static function setBasePath($path) {
        self::$base_path = $path;
    }

    public function __construct($path) {
        $this->path = self::$base_path . $path;
        $this->setInfo();
    }

    public function __get($name) {
        return isset($this->info[$name]) ? $this->info[$name] : null;
    }

    public function __isset($property)
    {
        return property_exists($this, $property) || isset($this->info[$property]);
    }

    protected function setInfo() {
        $info = Yaml::parseFile($this->path . DIRECTORY_SEPARATOR . 'info.yml');

        if (is_array($info)) {
            $this->info = $info;
        }
    }
}
