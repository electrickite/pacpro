<?php

namespace Tests\Functional;

class PackageTest extends BaseTestCase
{
    public function setUp()
    {
        if ($this->getName() == 'testNoPackages') {
            $this->createVfs();
        } else {
            parent::setUp();
        }
    }

    public function testGetPackageIndex()
    {
        $response = $this->request('GET', '/package');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('packages.xml'), (string)$response->getBody());
    }

    public function testNoPackages()
    {
        $response = $this->request('GET', '/package');
        $this->assertEmptyPackageSet($response);
    }

    public function testGetPackagesFromRepo()
    {
        $response = $this->request('GET', '/package?tag=main');
        $xml = $this->parseXml($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertCount(2, $xml->package);
    }

    public function testGetPackagesByNonexistantRepo()
    {
        $response = $this->request('GET', '/package?tag=nonesuch');
        $this->assertEmptyPackageSet($response);
    }

    public function testPackageSearchSingleMatch()
    {
        $response = $this->request('GET', '/package?query=foo');
        $xml = $this->parseXml($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertCount(1, $xml->package);
        $this->assertEquals('foo', $xml->package->package);
    }

    public function testPackageSearchMultipleMatch()
    {
        $response = $this->request('GET', '/package?query=s');
        $xml = $this->parseXml($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, $xml->package);
    }

    public function testPackageSearchNoMatch()
    {
        $response = $this->request('GET', '/package?query=baz');
        $this->assertEmptyPackageSet($response);
    }

    public function testGetPackageBySignature()
    {
        $response = $this->request('GET', '/package?signature=sample-0.0.1-pl');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('package.xml'), (string)$response->getBody());
    }

    public function testGetPackageByNonexistantSignature()
    {
        $response = $this->request('GET', '/package?signature=nonesuch-0.0.1-pl');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }

    public function testGetPackageByInvalidSignature()
    {
        $response = $this->request('GET', '/package?signature=foo-pl');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }


    protected function assertEmptyPackageSet($response)
    {
        $xml = $this->parseXml($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertCount(1, $xml->package);
        $this->assertTrue(empty($xml->package));
    }
}
