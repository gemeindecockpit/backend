<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'determineRouteBeforeAppMiddleware' => true, //Without this, you will not be able to access the name of the route from within the middleware and will retrieve a null object.
            'cookies.encrypt' => true,
            'cookies.lifetime' => '20 minutes',
            'middlewareFifo' => true,
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ],
    ]);
};
