<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Permanently redirect paths with a trailing slash to their non-trailing
// counterpart
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        $uri = $uri->withPath(substr($path, 0, -1));

        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});

// Log all requests
$app->add(function (Request $request, Response $response, callable $next) {
    $this->logger->info($request->getMethod() . ' ' . $request->getUri());
    foreach ($request->getHeaders() as $name => $values) {
        $this->logger->info($name . ": " . implode(", ", $values));
    }
    $this->logger->info('Request body: ' . $request->getBody());

    return $next($request, $response);
});

// Set global response content type
$app->add(function ($request, $response, $next) {
    return $next($request, $response)->withHeader('Content-Type', 'application/xml');
});
