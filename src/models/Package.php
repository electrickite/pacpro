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
    }
}
