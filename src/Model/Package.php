<?php

namespace App\Model;

class Package extends ProviderBase
{
    protected $repo;

    public static function fromSignature($sig)
    {
        $package_info = self::parseSignature($sig);
        $packages = self::packagePathQuery('*/' . $package_info['id']);
        if (empty($packages)) {
            throw new NotFoundException('Package not found');
        }

        return new Package($packages[0]['repo'], $packages[0]['path'], $package_info['version']);
    }

    public static function all()
    {
        return self::packageQuery('*/*');
    }

    public static function fromRepo($repo)
    {
        return self::packageQuery($repo . '/*');
    }

    public static function search($query)
    {
        return array_filter(self::all(), function($package, $key) use ($query) {
            return strpos(strtolower($package->name), strtolower($query)) !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected static function packageQuery($pattern)
    {
        return array_map(function($match) {
            return new Package($match['repo'], $match['path']);
        }, self::packagePathQuery($pattern));
    }

    protected static function packagePathQuery($pattern)
    {
        return array_map(function($file) {
            $path = basename(dirname($file));
            $repo = basename(dirname(dirname($file)));
            return ['repo' => $repo, 'path' => $path];
        }, glob(self::$base_path . '/' . $pattern . '/info.yml'));
    }

    protected static function parseSignature($sig)
    {
        preg_match('/(.+)-([0-9]*\.[0-9]*\.[0-9]*-.*)/', $sig, $matches);

        if (isset($matches[1])) {
            return ['id' => $matches[1], 'version' => $matches[2]];
        } else {
            throw new BadRequestException('Invalid package signature');
        }
    }

    public function __construct($repo, $path, $version=null)
    {
        $this->repo = $repo;
        $this->setPath($this->repo . DIRECTORY_SEPARATOR . $path);
        $this->setInfo();
        $this->addPackageInfo($version);

        if (!file_exists($this->transportPackagePath())) {
            throw new NotFoundException('Invaid package version: ' . $this->signature);
        }
    }

    public function transportPackagePath()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->transportPackageFileName();
    }

    public function transportPackageFileName()
    {
        return $this->signature . '.transport.zip';
    }

    public function transportPackageModTime()
    {
        $file = $this->transportPackagePath();
        if (file_exists($file)) {
            return date('c', filemtime($file));
        }
    }

    public function currentSignature()
    {
        return $this->buildSignature();
    }

    public function requiresUpdate()
    {
        return $this->signature != $this->currentSignature();
    }

    public function currentPackage()
    {
        return new self($this->repo, $this->id);
    }

    protected function addPackageInfo($version)
    {
        $this->info['repo'] = $this->repo;
        $this->info['signature'] = $this->buildSignature($version);

        $version_parts = explode('-', $this->current);
        $this->info['version'] = $version_parts[0];
        $this->info['release'] = $version_parts[1];

        $version_numbers = explode('.', $this->version);
        $this->info['major_version'] = $version_numbers[0];
        $this->info['minor_version'] = $version_numbers[1];
        $this->info['patch_version'] = $version_numbers[2];
    }

    protected function buildSignature($version=null)
    {
        return $this->id . '-' . ($version ?: $this->current);
    }
}
