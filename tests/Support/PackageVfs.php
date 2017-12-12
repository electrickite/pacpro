<?php

/**
 * Patch core functions in App namespace to support VFS mock filesystem
 */
namespace App\Model;

function realpath($path)
{
    return rtrim($path, '/');
}

function glob($pattern, $flags = 0)
{
    return \Webmozart\Glob\Glob::glob($pattern, $flags);
}


namespace Tests\Support;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

trait PackageVfs
{
    protected $usePackageVfs = true;

    public function packagesPath()
    {
        return $this->usePackageVfs ? vfsStream::url('packages') : __DIR__ . '/../fxtures/packages';
    }

    protected function createVfs()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('packages'));
    }

    protected function populateVfs()
    {
        vfsStream::copyFromFileSystem(__DIR__ . '/../fixtures/packages');
        $packages = vfsStream::url('packages');

        touch($packages . '/main/sample/sample-0.0.1-pl.transport.zip', 1512739800);
        touch($packages . '/main/sample/sample-0.0.2-pl.transport.zip', 1512960151);
        touch($packages . '/main/testing/testing-1.2.3-pl.transport.zip', 1512739801);
        touch($packages . '/other/foo/foo-2.0.4-pl.transport.zip', 1512739802);
    }
}
