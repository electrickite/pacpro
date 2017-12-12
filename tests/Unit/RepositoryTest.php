<?php

namespace Tests\Unit;

use App\Model\Repository;

class RepositoryTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->subject = new Repository('main');
    }

    public function testAll()
    {
        $all = Repository::all();
        $this->assertInternalType('array', $all);
        $this->assertCount(2, $all);
    }

    public function testInfo()
    {
        $this->assertEquals('main', $this->subject->id);
        $this->assertEquals('Main', $this->subject->name);
    }

    public function testTotalPackages()
    {
        $this->assertEquals(2, $this->subject->totalPackages());

        unlink($this->packagesPath() . '/main/testing/info.yml');
        unlink($this->packagesPath() . '/main/testing/testing-1.2.3-pl.transport.zip');
        rmdir($this->packagesPath() . '/main/testing');

        $this->assertEquals(1, $this->subject->totalPackages());
    }
}
