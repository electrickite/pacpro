<?php

namespace Tests\Functional;

class UpdateTest extends BaseTestCase
{
    public function testGetOutdatedSignature()
    {
        $response = $this->request('GET', '/package/update?signature=sample-0.0.1-pl');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('update.xml'), (string)$response->getBody());
    }

    public function testGetCurrentSignature()
    {
        $response = $this->request('GET', '/package/update?signature=sample-0.0.2-pl');
        $xml = $this->parseXml($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertEquals('package', $xml->getName());
        $this->assertEquals('0', $xml['total']);
    }

    public function testGetNonexistantSignature()
    {
        $response = $this->request('GET', '/package/update?signature=sample-0.0.3-pl');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }

    public function testGetInvalidSignature()
    {
        $response = $this->request('GET', '/package/update?signature=sam-pl');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }

    public function testGetMissingSignature()
    {
        $response = $this->request('GET', '/package/update');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }
}
