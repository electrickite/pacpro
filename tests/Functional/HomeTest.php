<?php

namespace Tests\Functional;

class HomeTest extends BaseTestCase
{
    public function testGetHome()
    {
        $response = $this->request('GET', '/home');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('home.xml'), (string)$response->getBody());
    }
}
