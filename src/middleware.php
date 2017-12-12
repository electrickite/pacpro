<?php

use App\Error\ForbiddenException;
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
    $auth_params = [];

    if (file_exists($users_file)) {
        $auth = Yaml::parseFile($users_file);
        if (!isset($auth['users'])) {
            throw new RuntimeException('Missing "users" key in users.yml');
        }

        $hashed_keys = isset($auth['hashed_keys']) ? $auth['hashed_keys'] : false;
        $username = $request->getQueryParam('username');
        $supplied_key = $request->getQueryParam('api_key');
        $auth_params = ['api_key' => $supplied_key, 'username' => $username];

        if (empty($username) || empty($supplied_key)) {
            throw new ForbiddenException('Must supply both username and api_key parameters');
        } elseif (!isset($auth['users'][$username])) {
            throw new ForbiddenException('Username not found');
        }

        if ($hashed_keys) {
            $authenticated = password_verify($supplied_key, $auth['users'][$username]);
        } else {
            $authenticated = ($supplied_key === $auth['users'][$username]);
        }

        if (!$authenticated) {
            throw new ForbiddenException('Incorrect key for username');
        }
    }

    $this->view['auth'] = $auth_params;
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
        $this->logger->debug($name . ": " . implode(", ", $values));
    }
    $this->logger->debug('Request body: ' . $request->getBody());

    $response = $next($request, $response);

    $this->logger->info('Status: ' . $response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        $this->logger->debug($name . ": " . implode(", ", $values));
    }

    return $response;
});
