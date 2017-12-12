<?php

namespace Tests\Functional;

class ExceptionsTest extends BaseTestCase
{
    protected $usePackageVfs = false;

    public function testNoRoute()
    {
        $response = $this->request('GET', '/nonesuch');
        $xml = $this->parseXml($response);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertequals('404', (string)$xml->status);
    }

    public function testTrailingSlash()
    {
        $response = $this->request('GET', '/verify/');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/verify', $response->getHeaderLine('Location'));
    }

    public function testMethodNotAllowed()
    {
        $response = $this->request('POST', '/verify');
        $xml = $this->parseXml($response);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertequals('405', (string)$xml->status);
    }
}
