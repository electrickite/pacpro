<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

class ErrorHandler
{
    const NOT_FOUND = 1;
    const NOT_ALLOWED = 2;
    const EXCEPTION = 3;
    const RUNTIME = 4;

    protected $type;
    protected $status;
    protected $message;
    protected $logger;

    static public function notFound(Logger $logger)
    {
        return new static(self::NOT_FOUND);
    }

    static public function notAllowed(Logger $logger)
    {
        return new static(self::NOT_ALLOWED);
    }

    static public function exception(Logger $logger)
    {
        return new static(self::EXCEPTION, $logger);
    }

    static public function runtimeError(Logger $logger)
    {
        return new static(self::RUNTIME, $logger);
    }

    public function __construct($type, Logger $logger)
    {
        $this->type = intval($type);
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, $data=null)
    {
        switch ($this->type) {
            case self::NOT_FOUND:
                $this->notFoundHandler();
                break;
            case self::NOT_ALLOWED:
                $response = $this->notAllowedHandler($response, $data);
                break;
            case self::RUNTIME:
                $this->runtimeHandler($data);
                break;
            default:
                $this->exceptionHandler($data);
        }

        $response = $response
            ->withHeader('Content-Type', 'application/xml')
            ->withStatus($this->status)
            ->write($this->renderXml());

        $this->logResponse($response);
        return $response;
    }

    protected function notFoundHandler()
    {
        $this->status = 404;
        $this->message = 'The requested resource was not found.';
    }

    protected function notAllowedHandler($response, $methods)
    {
        $allowed = implode(', ', $methods);
        $this->status = 405;
        $this->message = "Method must be one of: $allowed";

        return $response->withHeader('Allow', $allowed);
    }

    protected function exceptionHandler($exception)
    {
        switch (get_class($exception)) {
        case 'NotFoundException':
            $this->notFoundHandler();
            break;
        case 'BadRequestException':
            $this->status = 400;
            $this->message = 'There was an error in the format of your request.';
            break;
        case 'ForbiddenException':
            $this->status = 403;
            $this->message = 'You are not authorized to access this resource.';
            break;
        default:
            $this->logException($exception);
            $this->status = 500;
            $this->message = 'The server encountered a problem responding to your request and cannot continue.';
        }
    }

    protected function runtimeHandler($error)
    {
        $this->logException($error);
        $this->status = 500;
        $this->message = 'The server encountered a problem responding to your request and cannot continue.';
    }

    public function logException($exception)
    {
        $this->logger->error($exception->getMessage(), [
            'exception' => get_class($exception),
            'code'      => $exception->getCode(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }

    public function logResponse($response)
    {
        $this->logger->info('Status: ' . $response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            $this->logger->debug($name . ": " . implode(", ", $values));
        }
    }

    protected function renderXml()
    {
        $xml = new SimpleXMLElement("<error></error>");
        $xml->addChild('status', $this->status);
        $xml->addChild('message', $this->message);
        return $xml->asXML();
    }
}
