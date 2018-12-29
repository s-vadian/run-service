<?php

return [
    'db' => [
        'driver' => 'pgsql',
        'dbname' => 'test_app',
        'host' => 'localhost',
        'port' => '5432',
        'user' => 'postgres',
        'password' => 'postgres',
    ],
    'query' => 'SELECT DISTINCT "id_service" FROM "queue" WHERE status IN (0,-1,-2);',
    'query_ids' => 'SELECT id_service, id FROM "queue" WHERE id_service = ${SERVICE_ID} AND (status IN (0,-1,-2)) ORDER BY id LIMIT 50000;',
    'pidfile' => '/tmp/run-service-daemon.pid',
    'logfile' => '/var/log/run-service-php/run-service.log',
    'interval' => 10,
    'threads_cnt' => 4,
    'tools' => [
        'php' => [
            'path' => '/usr/bin/php',
        ],
    ],
    'services' => [
        'service1' => [
            'id' => 1,
            'min_ids' => 1,
            'max_ids' => 50,
            'tool' => 'php',
            'threads_cnt' => 4,
            'path' => '/test_projects/run-service/Tests/Services/service1/',
        ],
    ],
];


