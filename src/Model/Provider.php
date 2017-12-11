<?php

namespace App\Model;

class Provider extends ProviderBase
{
    public function __construct()
    {
        parent::__construct('');
    }

    public function totalPackages()
    {
        return count(glob($this->path . '/*/*/info.yml'));
    }

    public function popularPackages()
    {
        return $this->parsePackageList($this->popular);
    }

    public function newestPackages()
    {
        return $this->parsePackageList($this->newest);
    }

    public function repositories()
    {
        return Repository::all();
    }

    protected function parsePackageList($list)
    {
        $packages = [];
        if (is_array($list)) {
            foreach ($list as $item) {
                $pkg_parts = explode('/', $item);
                $packages[] = new Package($pkg_parts[0], $pkg_parts[1]);
            }
        }
        return $packages;
    }
}
