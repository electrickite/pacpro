<?php

namespace Tests\Functional;

class VerifyTest extends BaseTestCase
{
    protected $usePackageVfs = false;

    public function testGetVerify()
    {
        $response = $this->request('GET', '/verify');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->getHeaderLine('Content-Type'));
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('verify.xml'), (string)$response->getBody());
    }
}
