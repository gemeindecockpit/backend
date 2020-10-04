<?php
// can be deleted
declare(strict_types=1);

use App\Http\Middleware\SessionMiddleware;
use App\Http\Middleware\LoginMiddleware;
use App\Http\Middleware\DBCloseMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(DBCloseMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(LoginMiddleware::class);
};
