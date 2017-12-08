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
    protected $code;
    protected $status;
    protected $message;
    protected $logger;

    static public function notFound()
    {
        return new static(self::NOT_FOUND);
    }

    static public function notAllowed()
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

    public function __construct($type, Logger $logger=null)
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

        return $response
            ->withHeader('Content-Type', 'application/xml')
            ->withStatus($this->code)
            ->write($this->renderXml());
    }

    protected function notFoundHandler()
    {
        $this->code = 404;
        $this->status = 'Not Found';
        $this->message = 'The requested resource was not found.';
    }

    protected function notAllowedHandler($response, $methods)
    {
        $allowed = implode(', ', $methods);
        $this->code = 405;
        $this->status = 'Not Allowed';
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
            $this->code = 400;
            $this->status = 'Bad Request';
            $this->message = 'There was an error in the format of your request.';
            break;
        case 'ForbiddenException':
            $this->code = 403;
            $this->status = 'Forbidden';
            $this->message = 'You are not authorized to access this resource.';
            break;
        default:
            $this->logException($exception);
            $this->code = 500;
            $this->status = 'Internal Server Error';
            $this->message = 'The server encountered a problem responding to your request and cannot continue.';
        }
    }

    protected function runtimeHandler($error)
    {
        $this->logException($error);
        $this->code = 500;
        $this->status = 'Internal Server Error';
        $this->message = 'The server encountered a problem responding to your request and cannot continue.';
    }

    public function logException($exception)
    {
        if ($this->logger) {
            $this->logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'code'      => $exception->getCode(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => $exception->getTraceAsString(),
            ]);
        }
    }

    protected function renderXml()
    {
        $xml = new SimpleXMLElement("<error></error>");
        $xml->addChild('code', $this->code);
        $xml->addChild('type', $this->status);
        $xml->addChild('message', $this->message);
        return $xml->asXML();
    }
}
