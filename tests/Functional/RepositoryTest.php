<?php

namespace Tests\Functional;

class RepositoryTest extends BaseTestCase
{
    public function setUp()
    {
        if ($this->getName() == 'testNoRepositories') {
            $this->createVfs();
        } else {
            parent::setUp();
        }
    }

    public function testGetRepositoryIndex()
    {
        $response = $this->request('GET', '/repository');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('repositories.xml'), (string)$response->getBody());
    }

    public function testNoRepositories()
    {
        $response = $this->request('GET', '/repository');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(empty($xml));
    }

    public function testGetRepository()
    {
        $response = $this->request('GET', '/repository/main');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertXmlStringEqualsXmlString($this->xmlFixture('repository.xml'), (string)$response->getBody());
    }

    public function testRepositoryNotFound()
    {
        $response = $this->request('GET', '/repository/nonesuch');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertXmlContentType($response);
    }
}
