<?php

(\Dotenv\Dotenv::createImmutable('./'))->safeLoad();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/phinx/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/phinx/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $_ENV['APP_ENVIRONMENT'],
        'production' => [
            'adapter' => $_ENV['DB_ADAPTER'],
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASSWORD'],
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'sqlite',
            'name' => 'development',
            'suffix' => '.db',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'sqlite',
            'memory' => true
        ]
    ],
    'version_order' => 'creation'
];
