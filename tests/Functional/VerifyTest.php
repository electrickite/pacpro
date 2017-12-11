<?php

namespace Tests\Functional;

class VerifyTest extends BaseTestCase
{
    protected $usePackageVfs = false;

    public function testGetVerify()
    {
        $response = $this->request('GET', '/verify');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('verify.xml'), (string)$response->getBody());
    }
}
