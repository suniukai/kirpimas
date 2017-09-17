<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        'db' => [
            'host' => 'localhost',
            'user' => 'madingas',
            'pass' => 'madingas',
            'dbname' => 'madingas'
        ]
    ],
];
