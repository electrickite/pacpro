<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Support\PackageVfs;
use App\Model\ProviderBase;

class BaseTestCase extends TestCase
{
    use PackageVfs;

    protected $subject;

    public function setUp()
    {
        if ($this->usePackageVfs) {
            $this->createVfs();
            $this->populateVfs();
        }

        ProviderBase::setBasePath($this->packagesPath());
    }
}
