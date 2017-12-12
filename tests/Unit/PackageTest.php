<?php

namespace Tests\Unit;

use App\Model\Package;
use App\Error\BadRequestException;
use App\Error\NotFoundException;

class PackageTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->subject = new Package('main', 'sample');
    }

    public function testAll()
    {
        $all = Package::all();
        $this->assertInternalType('array', $all);
        $this->assertCount(3, $all);
    }

    public function testFromRepo()
    {
        $all = Package::fromRepo('main');
        $this->assertInternalType('array', $all);
        $this->assertCount(2, $all);
    }

    public function testSearchSingleMatch()
    {
        $all = Package::search('Sample');
        $this->assertInternalType('array', $all);
        $this->assertCount(1, $all);
    }

    public function testSearchMultipleMatch()
    {
        $all = Package::search('s');
        $this->assertInternalType('array', $all);
        $this->assertCount(2, $all);
    }

    public function testSearchNoMatch()
    {
        $all = Package::search('nonesuch');
        $this->assertInternalType('array', $all);
        $this->assertCount(0, $all);
    }

    public function testFromSignature()
    {
        $expected_signature = 'sample-0.0.2-pl';
        $package = Package::fromSignature($expected_signature);

        $this->assertEquals($expected_signature, $package->signature);
    }

    public function testFromInvalidSignature()
    {
        $this->expectException(BadRequestException::class);
        $package = Package::fromSignature('samp-pl');
    }

    public function testFromSignatureForMissingTransportPackage()
    {
        $this->expectException(NotFoundException::class);
        $package = Package::fromSignature('sample-0.0.3-pl');
    }

    public function testFromSignatureForNonexistantPackage()
    {
        $this->expectException(NotFoundException::class);
        $package = Package::fromSignature('nonesuch-1.0.0-pl');
    }

    public function testVersionSpecified()
    {
        $package = new Package('main', 'sample', '0.0.1-pl');
        $this->assertEquals('sample-0.0.1-pl', $package->signature);
    }

    public function testBadVersion()
    {
        $this->expectException(NotFoundException::class);
        $package = new Package('main', 'sample', '0.0.3-pl');
    }

    public function testTransportPackagePath()
    {
        $expected_path = 'vfs://packages/main/sample/sample-0.0.2-pl.transport.zip';
        $this->assertEquals($expected_path, $this->subject->transportPackagePath());
    }

    public function testTransportPackageFileName()
    {
        $expected_name = 'sample-0.0.2-pl.transport.zip';
        $this->assertEquals($expected_name, $this->subject->transportPackageFileName());
    }

    public function testTransportPackageModTime()
    {
        $expected_time = '2017-12-11T02:42:31+00:00';
        $this->assertEquals($expected_time, $this->subject->transportPackageModTime());
    }

    public function testCurrentSignature()
    {
        $expected_sig = 'sample-0.0.2-pl';
        $this->assertEquals($expected_sig, $this->subject->currentSignature());
    }

    public function testCurrentPackage()
    {
        $this->subject = new Package('main', 'sample', '0.0.1-pl');
        $current_package = $this->subject->currentPackage();
        $this->assertEquals('sample-0.0.2-pl', $current_package->signature);
    }

    public function testRequiresUpdateForCurrentSignature()
    {
        $this->assertFalse($this->subject->requiresUpdate());
    }

    public function testRequiresUpdateForOutdatedSignature()
    {
        $this->subject = new Package('main', 'sample', '0.0.1-pl');
        $this->assertTrue($this->subject->requiresUpdate());
    }

    public function testPackageInfo()
    {
        $this->subject = new Package('main', 'testing');
        $this->assertEquals('testing', $this->subject->id);
        $this->assertEquals('main', $this->subject->repo);
        $this->assertEquals('testing-1.2.3-pl', $this->subject->signature);
        $this->assertEquals('1.2.3', $this->subject->version);
        $this->assertEquals('pl', $this->subject->release);
        $this->assertEquals('1', $this->subject->major_version);
        $this->assertEquals('2', $this->subject->minor_version);
        $this->assertEquals('3', $this->subject->patch_version);
    }
}
