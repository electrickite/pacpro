<?php

/**
 * Patch core functions in App namespace to support VFS mock filesystem
 */
namespace App\Model;

function realpath($path)
{
    return rtrim($path, '/');
}

function glob($pattern, $flags = 0)
{
    return \Webmozart\Glob\Glob::glob($pattern, $flags);
}


namespace Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

class BaseTestCase extends TestCase
{
    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    protected $usePackageVfs = true;

    public function setUp()
    {
        if ($this->usePackageVfs) {
            $this->createVfs();
            $this->populateVfs();
        }
    }

    public function packagesPath()
    {
        return $this->usePackageVfs ? vfsStream::url('packages') : __DIR__ . '/../fxtures/packages';
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function request($requestMethod, $requestUri, $requestData = null)
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
        $settings = require __DIR__ . '/../settings.php';
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

    protected function createVfs()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('packages'));
    }

    protected function populateVfs()
    {
        vfsStream::copyFromFileSystem(__DIR__ . '/../fixtures/packages');
        $packages = vfsStream::url('packages');

        touch($packages . '/main/sample/sample-0.0.1-pl.transport.zip', 1512739800);
        touch($packages . '/main/sample/sample-0.0.2-pl.transport.zip', 1512960151);
        touch($packages . '/main/testing/testing-1.2.3-pl.transport.zip', 1512739801);
        touch($packages . '/other/foo/foo-2.0.4-pl.transport.zip', 1512739802);
    }
}
