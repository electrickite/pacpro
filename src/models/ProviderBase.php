<?php

use Symfony\Component\Yaml\Yaml;

abstract class ProviderBase
{
    protected static $base_path;

    protected $path;
    protected $info = [];

    public static function setBasePath($path) {
        self::$base_path = realpath($path);
    }

    public function __construct($path) {
        $this->setPath($path);
        $this->setInfo();
    }

    public function __get($name) {
        return isset($this->info[$name]) ? $this->info[$name] : null;
    }

    public function __isset($property)
    {
        return property_exists($this, $property) || isset($this->info[$property]);
    }

    protected function setPath($path) {
        $full_path = realpath(self::$base_path . DIRECTORY_SEPARATOR . $path);

        if ($full_path === false || !file_exists($full_path)) {
            throw new NotFoundException;
        } elseif (strpos($full_path, self::$base_path) !== 0) {
            throw new BadRequestException('Invalid path specified');
        } else {
            $this->path = $full_path;
        }
    }

    protected function setInfo() {
        $info = Yaml::parseFile($this->path . DIRECTORY_SEPARATOR . 'info.yml');

        if (is_array($info)) {
            $this->info = $info;
        }
    }
}
