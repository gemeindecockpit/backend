<?php
// can be deleted
declare(strict_types=1);

use App\Http\Middleware\SessionMiddleware;
use App\Http\Middleware\LoginMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(LoginMiddleware::class);
};
