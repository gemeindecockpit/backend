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

    $app->get('/config[/{nuts0}[/{nuts1}[/{nuts2}[/{nuts3}[/{org_type}[/{org_name}]]]]]]',
        \ConfigRouteController::class . ':get_organisation_config')->setName('conf'); //TESTED, verified for working

    $app->get('/config' . ORG_FULL_LINK . FIELD_NAME,
        \ConfigRouteController::class . ':get_org_full_link_field_name'); //TESTED, verified for working


    $app->post('/config/organisation',
        \ConfigRouteController::class . ':post_org'); // TODO
    $app->post('/config' . ORG_FULL_LINK . FIELD_NAME,
        \ConfigRouteController::class . ':post_org_full_link_field_name'); // TODO

    $app->put('/config' . ORG_FULL_LINK,
        \ConfigRouteController::class . ':put_org_full_link');
    $app->put('/config' . ORG_FULL_LINK . FIELD_NAME,
        \ConfigRouteController::class . ':put_org_full_link_field_name');

    $app->delete('/config' . ORG_FULL_LINK,
        \ConfigRouteController::class . ':delete_org_full_link'); // TODO
    $app->delete('/config' . ORG_FULL_LINK . FIELD_NAME,
        \ConfigRouteController::class . ':delete_org_full_link_field_name'); // TODO



    $app->get('/data',
        \DataRouteController::class . ':home'); //TESTED, verified for working

    $app->get('/data/field',
        \DataRouteController::class . ':get_field'); // TODO: NOT WORKING

    $app->get('/data/field/{field_id:[0-9]+}',
        \DataRouteController::class . ':get_field_field_id'); // TODO: NOT WORKING

    $app->get('/data/{nuts0}[/{nuts1}[/{nuts2}[/{nuts3}[/{org_type}]]]]',
        \DataRouteController::class . ':get_organisation_data'); //TESTED, verified for working

    $app->get('/data' . ORG_FULL_LINK,
        \DataRouteController::class . ':get_org_full_link'); //TESTED, TODO: add links for data/.../field_name

    $app->get('/data' . ORG_FULL_LINK . YEAR . '[/{month:[0-1][0-9]}[/{day:[0-3][0-9]}]]',
        \DataRouteController::class . ':get_org_full_link_date');


    $app->get('/data' . ORG_FULL_LINK . FIELD_NAME,
        \DataRouteController::class . ':get_org_full_link_field_name'); //TESTED, verified for working
    $app->get('/data' . ORG_FULL_LINK . FIELD_NAME . YEAR . '[/{month:[0-1][0-9]}[/{day:[0-3][0-9]}]]',
        \DataRouteController::class . ':get_org_full_link_field_name_date');


    $app->post('/data' . ORG_FULL_LINK . DATE_FULL,
        \DataRouteController::class . ':post_org_full_link');
    $app->post('/data' . ORG_FULL_LINK . FIELD_NAME . DATE_FULL,
        \DataRouteController::class . ':post_org_full_link_field_name');

    $app->put('/data' . ORG_FULL_LINK . DATE_FULL,
        \DataRouteController::class . ':put_org_full_link');
    $app->put('/data' . ORG_FULL_LINK . FIELD_NAME . DATE_FULL,
        \DataRouteController::class . ':put_org_full_link_field_name');

    $app->get('/users', \UserRouteController::class . ':get_home'); //get fuer users
    $app->post('/users', \UserRouteController::class . '/post_home');

    $app->get('/users/{id:[0-9]+}', \UserRouteController::class . ':get_user_id'); //get fuer users id

    $app->post('/users/{id:[0-9]+}', \UserRouteController::class . '/post_user_id');
    $app->put('/users/{id:[0-9]+}', \UserRouteController::class . '/put_user_id');

    $app->delete('/users/{id:[0-9]+}', \UserRouteController::class . ':delete_user_id'); //delete fuer users id

    $app->get('/users/me', \UserRouteController::class . ':get_me');



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
