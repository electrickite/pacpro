<?php

// DIC configuration
$container = $app->getContainer();

// Configure classes
ProviderBase::setBasePath($container->get('settings')['packages_path']);

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Register Twig View helper
$container['view'] = function ($c) {
    $settings = $c->get('settings')['view'];

    $view = new \Slim\Views\Twig($settings['template_path']);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};


// Error handling
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $settings = $c->get('settings')['errors'];

        return $c->get('view')->render($response, $settings['template'], [
            'status' => 404,
            'message' => $settings['not_found']
        ])->withStatus(404);
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        $settings = $c->get('settings')['errors'];

        return $c->get('view')->render($response, $settings['template'], [
            'status' => 405,
            'message' => $settings['not_allowed'] . implode(', ', $methods)
        ])->withStatus(405)->withHeader('Allow', implode(', ', $methods));;
    };
};

// Use XML errors when in production
if (!$container->get('settings')['displayErrorDetails']) {
    $container['errorHandler'] = function ($c) {
        return function ($request, $response, $exception) use ($c) {
            $settings = $c->get('settings')['errors'];

            return $c->get('view')->render($response, $settings['template'], [
                'status' => 500,
                'message' => $settings['internal']
            ])->withStatus(500);
        };
    };

    $container['phpErrorHandler'] = function ($c) {
        return function ($request, $response, $error) use ($c) {
            $settings = $c->get('settings')['errors'];

            return $c->get('view')->render($response, $settings['template'], [
                'status' => 500,
                'message' => $settings['internal']
            ])->withStatus(500);
        };
    };
}
