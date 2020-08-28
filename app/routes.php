<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
require_once(__DIR__ . '/../app/db.php');
session_start();

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });


    ############################################################################################

    //config route
    $app->get('/config', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world config!');
        return $response;
    });

    $app->get('/config/'. NUTS_FULL, function (Request $request, Response $response) {
        $response->getBody()->write(echo_json_all_types_for_user());
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/'. NUTS_FULL . '/{orgaType}', function (Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode(get_all_orgs_for_user_for_type($args['orgaType'])));
        return $response->withHeader('Content-type', 'application/json');
    });




    ############################################################################################
    //Data route
    $app->get('/data', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world data!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};

function echo_json_all_types_for_user() {
	$data_operation = new DatabaseOps();
	$result =  $data_operation->get_all_config_types_for_user(4); //TODO: $_SESSION['userid'] nutzen und keine hardcode 4 lmao
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['type']);
	}
  return json_encode($typearray);
	 //$data_out->add_keyvalue_to_links_array('types', $typearray_links);
	//header('Content-type: application/json');
	//echo  $data_out->output_as_json($typearray);
}

function get_all_orgs_for_user_for_type($type) {

  $data_operation = new DatabaseOps();
	$result =  $data_operation->get_all_data_organizations_for_user_for_type(4, $type);//TODO: $_SESSION['userid'] nutzen und keine hardcode 4 lmao
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['name']);
	}
	return $typearray;

}
