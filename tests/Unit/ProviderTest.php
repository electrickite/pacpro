<?php

namespace Tests\Unit;

use App\Model\Provider;

class ProviderTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->subject = new Provider();
    }

    public function testTotalPackages()
    {
        $this->assertEquals(3, $this->subject->totalPackages());

        unlink($this->packagesPath() . '/main/testing/info.yml');
        unlink($this->packagesPath() . '/main/testing/testing-1.2.3-pl.transport.zip');
        rmdir($this->packagesPath() . '/main/testing');

        $this->assertEquals(2, $this->subject->totalPackages());
    }

    public function testPopularPackages()
    {
        $packages = $this->subject->popularPackages();
        $this->assertCount(1, $packages);
        $this->assertEquals('sample', $packages[0]->id);
    }

    public function testNewestPackages()
    {
        $packages = $this->subject->newestPackages();
        $this->assertCount(1, $packages);
        $this->assertEquals('sample', $packages[0]->id);
    }
}
