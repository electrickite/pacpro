<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Yaml\Yaml;

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

// Authenticate users
$app->add(function ($request, $response, $next) {
    $users_file = $this->settings['packages_path'] . 'users.yml';
    if (file_exists($users_file)) {
        $username = $request->getQueryParam('username');
        $key = $request->getQueryParam('api_key');
        $users = Yaml::parseFile($users_file);

        if (empty($username) || !isset($users[$username]) || $users[$username] != $key) {
            throw new ForbiddenException;
        }
    }

    return $next($request, $response);
});

// Set global response content type
$app->add(function ($request, $response, $next) {
    $response = $next($request, $response);
    if ($response->getHeaderLine('Content-Type') != 'application/octet-stream' &&
        $response->getHeaderLine('Content-Type') != 'text/plain'
    ) {
        $response = $response->withHeader('Content-Type', 'application/xml');
    }
    return $response;
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
