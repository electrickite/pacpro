<?php

namespace Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use PHPUnit\Framework\TestCase;
use Tests\Support\PackageVfs;

class BaseTestCase extends TestCase
{
    use PackageVfs;

    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    public function setUp()
    {
        if ($this->usePackageVfs) {
            $this->createVfs();
            $this->populateVfs();
        }
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function request($requestMethod, $requestUri, $settings = null, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri,
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Create application configuration
        $settings = $settings ?: $this->getSettings();
        $settings['request'] = $request;

        // Instantiate the application
        $app = new App($settings);

        // Set up dependencies
        require __DIR__ . '/../../src/dependencies.php';

        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../../src/middleware.php';
        }

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    protected function getSettings()
    {
        return require __DIR__ . '/../settings.php';
    }

    public function assertXmlContentType($response)
    {
        $this->assertEquals('application/xml', $response->getHeaderLine('Content-Type'));
    }

    protected function parseXml($response)
    {
        return simplexml_load_string((string)$response->getBody());
    }

    protected function xmlFixture($fixture)
    {
        return file_get_contents(__DIR__ . '/../fixtures/xml/' . $fixture);
    }
}
