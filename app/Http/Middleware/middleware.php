<?php
// can be deleted
declare(strict_types=1);

use SessionMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(SessionMiddleware::class);
};
