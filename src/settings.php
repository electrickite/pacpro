<?php

use \Monolog\Logger;

// Load environment from .env
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
try {
  $dotenv->load();
} catch (InvalidArgumentException $e) {}

// Configure application
return [
    'settings' => [
        'displayErrorDetails' => (strtolower(getenv('DEBUG')) == 'true'),
        'addContentLengthHeader' => false,

        'logger' => [
            'name' => 'pacpro',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../log/app.log',
            'level' => strtolower(getenv('DEBUG')) == 'true' ? Logger::DEBUG : Logger::INFO,
        ],

        'view' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        'errors' => [
            'template' => 'error.xml.twig',
            'not_found' => 'The requested resource was not found',
            'not_allowed' => 'Method must be one of: ',
            'internal' => 'Internal servr error'
        ],
    ],
];
