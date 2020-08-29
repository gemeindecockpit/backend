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
    //    ______   ______   .__   __.  _______  __    _______
    //   /      | /  __  \  |  \ |  | |   ____||  |  /  _____|
    //  |  ,----'|  |  |  | |   \|  | |  |__   |  | |  |  __
    //  |  |     |  |  |  | |  . `  | |   __|  |  | |  | |_ |
    //  |  `----.|  `--'  | |  |\   | |  |     |  | |  |__| |
    //   \______| \______/  |__| \__| |__|     |__|  \______|
    //
    ############################################################################################
    //GET-REQUESTS ##############################################################################################
    $app->get('/config[/' . NUTS0 . '[/' . NUTS1 . '[/' . NUTS2 . ']]]', function (Request $request, Response $response) {
        $response->getBody()->write('TODO: User NutsController to display correct available NUTZ');
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

    $app->get('/config/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: GET – liefert die configdaten zu einer Entität ');
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: GET – liefert die Configdaten zu einem Field ');
        return $response->withHeader('Content-type', 'application/json');
    });

    //POST-REQUESTS ##############################################################################################

    $app->post('/config/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('POST – erzeugt neue entität und rekursiv orgatype falls nicht vorhanden ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    $app->post('/config/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: POST – erzeugt ein neues Field für die Entity ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    //PUT-Requests ##############################################################################################
    $app->put('/config/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: PUT - Verändert die Configdaten zu einer Entität ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    $app->put('/config/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: PUT - ändert die config eines Feld einer Entity ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    //DELETE-Requests ##############################################################################################
    $app->delete('/config/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: DELTE – Makiert eine Entity als inaktiv ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    $app->delete('/config/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
        $response->getBody()->write('TODO: DELETE – makiert das Field als nicht mehr notwendig ');
        return $response->withStatus(302); //TODO: Richtigen code zurück schicken
    });

    ############################################################################################
    //   _______       ___   .___________.    ___
    //  |       \     /   \  |           |   /   \
    //  |  .--.  |   /  ^  \ `---|  |----`  /  ^  \
    //  |  |  |  |  /  /_\  \    |  |      /  /_\  \
    //  |  '--'  | /  _____  \   |  |     /  _____  \
    //  |_______/ /__/     \__\  |__|    /__/     \__\
    //
    ############################################################################################
    //GET-REQUESTS #############################################################################

    $app->get('/data/'. NUTS_FULL , function (Request $request, Response $response) {
        $response->getBody()->write(echo_json_all_types_for_user());
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}', function (Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode(get_all_orgs_for_user_for_type($args['orgaType'])));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response) {
        $response->getBody()->write('TODO: GET – Liefert daten zu einer Entity');
        //Moglichen Parameter
        //Last={all | x} liefert den gesamten Verlauf bzw. Den der letzten x Tage
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}/{entity}/{year:[1|2|3][0-9][0-9][0-9]}[/{month:[0-9][0-9]}[/{day:[0-9][0-9]}]]', function (Request $request, Response $response, $args) {
        if(isset($args['month']) && $args['month'] > 12){
          $response->getBody()->write('invalid month' . $args['month']);
        } else if (isset($args['day']) && $args['day'] > 31){
          $response->getBody()->write('invalid day' . $args['day']);
        } else {
          $response->getBody()->write('TODO: GET – liefert die Daten für ein bestimmtes jahr der ausgewählten entity. btw dein jahr ist: ' . $args['year']);
          //Moglichen Parameter
        }

        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response) {
        $response->getBody()->write('TODO: GET – Liefert daten zu einer Entity');
        //Moglichen Parameter
        // last={all|x}  liefert den gesamten Verlauf bzw. Den der letzten x Tage
        return $response->withHeader('Content-type', 'application/json');
    });


    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/data[/' . NUTS0 . '[/' . NUTS1 . '[/' . NUTS2 . ']]]', function (Request $request, Response $response) {
        $response->getBody()->write('TODO: User NutsController to display correct available NUTZ');
        return $response;
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
