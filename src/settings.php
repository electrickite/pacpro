<?php

use \Monolog\Logger;

// Load environment from .env
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
try {
  $dotenv->load();
} catch (InvalidArgumentException $e) {}

$debug = (strtolower(getenv('DEBUG')) == 'true');

// Configure application
return [
    'settings' => [
        'displayErrorDetails' => $debug,
        'addContentLengthHeader' => false,
        'packages_path' => getenv('PACKAGES_PATH') ?: __DIR__ . '/../packages/',

        'logger' => [
            'name' => 'pacpro',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../log/app.log',
            'level' => $debug ? Logger::DEBUG : Logger::INFO,
        ],

        'view' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
    ],
];
