<?php

use \Monolog\Logger;

// Configure application
return [
    'settings' => [
        'displayErrorDetails' => false,
        'addContentLengthHeader' => false,
        'packages_path' => __DIR__ . '/fixtures/packages/',

        'logger' => [
            'name' => 'pacpro',
            'path' => __DIR__ . '/../log/test.log',
            'level' => Logger::DEBUG,
        ],

        'view' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
    ],
];
