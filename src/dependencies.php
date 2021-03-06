<?php

use App\Model\ProviderBase;
use App\Error\ErrorHandler;

// DIC configuration
$container = $app->getContainer();

// Set timezone if needed
if ($container->get('settings')['timezone']) {
    date_default_timezone_set($container->get('settings')['timezone']);
}

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
    $uri = $c['request']->getUri()->withBasePath('');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $uri));

    return $view;
};

// Error handling
$container['notFoundHandler'] = function($c) {
    return ErrorHandler::notFound($c['logger']);
};
$container['notAllowedHandler'] = function($c) {
    return ErrorHandler::notAllowed($c['logger']);
};
$container['errorHandler'] = function($c) {
    return ErrorHandler::exception($c['logger']);
};
$container['phpErrorHandler'] = function($c) {
    return ErrorHandler::runtimeError($c['logger']);
};
