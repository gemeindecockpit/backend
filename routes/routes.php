<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

foreach (glob('./../app/http/Controller/*.php') as $filename) {
    require_once($filename);
}


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', \RouteController::class . ':home');

    $app->get('/config', \ConfigRouteController::class . ':home');

    $app->get('/config/location[/{nuts0}[/{nuts1}[/{nuts2}[/{nuts3}[/{org_type}[/{org_name}]]]]]]',
        \ConfigRouteController::class . ':get_org_by_location')->setName('conf'); //TESTED, verified for working
    $app->get('/config/location/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{org_type}/{org_name}/{field_name}',
        \ConfigRouteController::class . ':get_field_by_org_location'); //TESTED, verified for working


    $app->get('/config/organisation-unit',
        \ConfigRouteController::class . ':get_org_by_unit');
    $app->get('/config/organisation-unit/{org_unit}',
        \ConfigRouteController::class . ':get_org_unit');
    $app->get('/config/organisation-unit/{org_unit}/{org_name}',
        \ConfigRouteController::class . ':get_org_by_unit');
    $app->get('/config/organisation-unit/{org_unit}/{org_name}/{field_name}', #
        \ConfigRouteController::class . ':get_field_by_org_unit');

    $app->get('/config/organisation[/{org_id:[0-9]+}]',
        \ConfigRouteController::class . ':get_org_by_id');
    $app->get('/config/organisation/{org_id:[0-9]+}/{field_name}',
        \ConfigRouteController::class . ':get_field_by_org_id');

    $app->get('/config/field',
        \ConfigRouteController::class . ':get_field');

    $app->get('/config/field/{field_id:[0-9]+}',
        \ConfigRouteController::class . ':get_field_by_id');


    $app->post('/config/organisation',
        \ConfigRouteController::class . ':post_org'); // TODO
    $app->post('/config/organisation/{org_id:[0-9]+}',
        \ConfigRouteController::class . ':post_field_by_org_id'); // TODO, link is not optimal!

    $app->put('/config/organisation',
        \ConfigRouteController::class . ':put_org');
    $app->put('/config/field',
        \ConfigRouteController::class . ':put_field');



    $app->get('/data',
        \DataRouteController::class . ':home'); //TESTED, verified for working

    $app->get('/data/organisation',
        \DataRouteController::class . ':get_org_id_links');
    $app->get('/data/organisation/{org_id:[0-9]+}',
        \DataRouteController::class . ':get_data_by_org');

    $app->get('/data/field',
        \DataRouteController::class . ':get_field_id_links'); // TODO: NOT WORKINg
    $app->get('/data/field/{field_id:[0-9]+}',
        \DataRouteController::class . ':get_data_by_field'); // TODO: NOT WORKING



    $app->get('/data/organisation/{org_id:[0-9]+}/{year:[1|2|3][0-9][0-9][0-9]}[/{month:[0-1][0-9]}[/{day:[0-3][0-9]}]]',
        \DataRouteController::class . ':get_data_by_org_and_date');
    $app->get('/data/field/{field_id:[0-9]+}/{year:[1|2|3][0-9][0-9][0-9]}[/{month:[0-1][0-9]}[/{day:[0-3][0-9]}]]',
        \DataRouteController::class . ':get_data_by_field_and_date');


    $app->get('/data/organisation/{org_id:[0-9]+}/{field_name}',
        \DataRouteController::class . ':get_org_full_link_field_name'); //TESTED, verified for working
    $app->get('/data/organisation/{org_id:[0-9]+}/{field_name}/{year:[1|2|3][0-9][0-9][0-9]}[/{month:[0-1][0-9]}[/{day:[0-3][0-9]}]]',
        \DataRouteController::class . ':get_org_full_link_field_name_date');


    $app->post('/data/organisation/{org_id:[0-9]+}',
        \DataRouteController::class . ':post_org_data');
    $app->post('/data/field/{field_id:[0-9]+}',
        \DataRouteController::class . ':post_field_data');

    $app->get('/users', \UserRouteController::class . '/get_home');
    $app->post('/users', \UserRouteController::class . ':post_home');

    $app->get('/users/{id:[0-9]+}', \UserRouteController::class . '/get_user_id');
    $app->post('/users/{id:[0-9]+}', \UserRouteController::class . '/post_user_id');
    $app->put('/users/{id:[0-9]+}', \UserRouteController::class . ':put_user_id');
    $app->delete('/users/{id:[0-9]+}', \UserRouteController::class . ':delete_user_id');

    $app->get('/users/me', function ($request, $response, $args) {

      $response->getBody()->write('You are on /users/me, to be implemented');
      return $response;
    });
    $app->put('/users/me', \UserRouteController::class . ':put_users_me');

    $app->get('/test', function ($request, $response, $args) {
      if(isset($_SESSION['user_id'])){
        $response->getBody()->write('User id: ' . $_SESSION['user_id']);
      } else {
        $response->getBody()->write('not logged in');
      }

      return $response;
    });

    $app->post('/login', \LoginRouteController::class . ':login')->setName('login');
    $app->map(['GET', 'PUT', 'DELETE', 'PATCH'], '/login', \LoginRouteController::class . ':wrong_method');
    $app->post('/logout', \LoginRouteController::class . ':logout')->setName('logout');

}
?>
