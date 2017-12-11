<?php

namespace Tests\Functional;

class DownloadTest extends BaseTestCase
{
    public function testGetDownloadUrl()
    {
        $response = $this->request('GET', '/download/sample-0.0.1-pl?getUrl=1');
        $expected_url = 'http://localhost/download/sample-0.0.1-pl';

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertEquals($expected_url, $response->getBody());
    }

    public function testGetDownload()
    {
        $response = $this->request('GET', '/download/sample-0.0.1-pl');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/octet-stream', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('attachment;filename="sample-0.0.1-pl.transport.zip"', $response->getHeaderLine('Content-Disposition'));
        $this->assertEquals('2779', $response->getHeaderLine('Content-Length'));
        $this->assertEquals(2779, $response->getBody()->getSize());
    }

    public function testDownloadNonexistantSignature()
    {
        $response = $this->request('GET', '/download/nonesuch-0.0.1-pl');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }

    public function testDownloadInvalidSignature()
    {
        $response = $this->request('GET', '/download/foo-pl');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }
}
