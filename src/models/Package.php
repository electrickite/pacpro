<?php

class Package extends ProviderBase
{
    protected $repo;

    public static function all() {
        return self::packageQuery('*/*');
    }

    public static function fromRepo($repo) {
        return self::packageQuery($repo . '/*');
    }

    public static function search($query) {
        return self::packageQuery('*/*' . $query . '*');
    }

    protected static function packageQuery($pattern) {
        $packages = [];
        foreach (glob(self::$base_path . '/' . $pattern . '/info.yml') as $file) {
            $path = basename(dirname($file));
            $repo = basename(dirname(dirname($file)));
            $packages[] = new Package($repo, $path);
        }
        return $packages;
    }

    public function __construct($repo, $path) {
        $this->repo = self::$base_path . $repo;
        $this->path = $this->repo . DIRECTORY_SEPARATOR . $path;
        $this->setInfo();
        $this->addPackageInfo();
    }

    public function transportPackagePath() {
        return $this->path . DIRECTORY_SEPARATOR . $this->package_name . '.transport.zip';
    }

    public function transportPackageModTime() {
        $file = $this->transportPackagePath();
        if (file_exists($file)) {
            return date('c', filemtime($file));
        }
    }

    protected function addPackageInfo() {
        $this->info['repo'] = basename($this->repo);

        $this->package_name = $this->id . '-' . $this->current;

        $version_parts = explode('-', $this->current);
        $this->info['version'] = $version_parts[0];
        $this->info['release'] = $version_parts[1];

        $version_numbers = explode('.', $this->version);
        $this->info['major_version'] = $version_numbers[0];
        $this->info['minor_version'] = $version_numbers[1];
        $this->info['patch_version'] = $version_numbers[2];
    }
}
