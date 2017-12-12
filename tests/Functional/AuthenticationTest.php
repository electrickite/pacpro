<?php

namespace Tests\Functional;

class AuthenticationTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        copy(__DIR__ . '/../fixtures/users.yml', $this->packagesPath() . '/users.yml');
    }

    public function testAuthenticatedWithValidCredentials()
    {
        $response = $this->request('GET', '/verify?username=myuser&api_key=mypassword');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAuthenticatedWithoutValidCredentials()
    {
        $response = $this->request('GET', '/verify');
        $this->assertForbidden($response);
    }

    public function testAuthenticatedWithInvalidCredentials()
    {
        $response = $this->request('GET', '/verify?username=none&api_key=bad');
        $this->assertForbidden($response);
    }

    public function testAuthenticatedDownloadUrl()
    {
        $response = $this->request('GET', '/download/sample-0.0.2-pl?username=myuser&api_key=mypassword&getUrl=1');
        $this->assertEquals('http://localhost/download/sample-0.0.2-pl?api_key=mypassword&username=myuser', (string)$response->getBody());
    }

    public function testAuthenticatedPackageDownloadUrl()
    {
        $response = $this->request('GET', '/package?signature=sample-0.0.2-pl&username=myuser&api_key=mypassword');
        $xml = $this->parseXml($response);

        $this->assertEquals('http://localhost/download/sample-0.0.2-pl?api_key=mypassword&username=myuser', (string) $xml->location);
    }

    protected function assertForbidden($response)
    {
        $xml = $this->parseXml($response);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertXmlContentType($response);
        $this->assertequals('403', (string)$xml->status);
    }
}
