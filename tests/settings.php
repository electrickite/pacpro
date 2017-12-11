<?php

use \Monolog\Logger;
use org\bovigo\vfs\vfsStream;

// Configure application
return [
    'settings' => [
        'displayErrorDetails' => false,
        'addContentLengthHeader' => false,
        'packages_path' => vfsStream::url('packages'),

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
