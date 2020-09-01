<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
require_once(__DIR__ . '/../app/db.php');

foreach(glob(__DIR__ . "/../Controller/*.php") as $filename) {
	require_once($filename);
}

session_start();

// TODO: Delete before pushing to production!
$_SESSION['user_id'] = 4;

function assoc_array_to_indexed($assoc_array) {
    $indexed_array = [];
    foreach($assoc_array as $value) {
        $indexed_array[] = $value;
    }
    return $indexed_array;
}

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
    $app->get('/config', function (Request $request, Response $response) {
        $orgController = new OrganisationController();
        $response->getBody()->write(json_encode($orgController->get_all($_SESSION['user_id'])));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts0($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts01($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}/{nuts2}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts012($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}/{nuts2}/{nuts3}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts0123($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{type}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts0123_type($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{type}/{name}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $response->getBody()->write(json_encode($orgController->get_config_for_organisations_by_nuts0123_type_name($_SESSION['user_id'], ...$args_indexed)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{type}/{name}/{field}', function (Request $request, Response $response, $args_assoc) {
        $orgController = new OrganisationController();
        $field_name = $args_assoc['field'];
        unset($args_assoc['field']);
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $org = $orgController->get_config_for_organisations_by_nuts0123_type_name($_SESSION['user_id'], ...$args_indexed);
        $orgs_id = -1;
        if(isset($org[0])) {
            $org_id = $org[0]['organisation_id'];
        }
        $response->getBody()->write(json_encode($orgController->get_config_for_field_by_name($_SESSION['user_id'], $org_id, $field_name)));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/config/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
      $orgController = new OrganisationController();
      $response->getBody()->write(json_encode($orgController->get_config_for_field($_SESSION['user_id'], $args['entity'], $args['orgaType'], $args['field'])));
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
      $orgController = new OrganisationController();
      $response->getBody()->write(json_encode($orgController->get_all_types($_SESSION['user_id'])));
      return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}', function (Request $request, Response $response, $args) {
      $orgController = new OrganisationController();
      $response->getBody()->write(json_encode($orgController->get_all_organisations_by_type($_SESSION['user_id'], $args['orgaType'])));
      return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/data/'. NUTS_FULL . '/{orgaType}/{entity}', function (Request $request, Response $response, $args) {
      $orgController = new OrganisationController();
      $response->getBody()->write(json_encode($orgController->get_data_for_organisation_by_name($_SESSION['user_id'], $args['entity'], $args['orgaType'])));
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

    $app->get('/data/'. NUTS_FULL . '/{orgaType}/{entity}/{field}', function (Request $request, Response $response, $args) {
      $orgController = new OrganisationController();
      $response->getBody()->write(json_encode($orgController->get_data_for_field($_SESSION['user_id'], $args['entity'], $args['orgaType'], $args['field'])));
      //Moglichen Parameter
      // last={all|x}  liefert den gesamten Verlauf bzw. Den der letzten x Tage
      return $response->withHeader('Content-type', 'application/json');
    });


    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/data[/ ' . NUTS0 . '[/' . NUTS1 . '[/' . NUTS2 . ']]]', function (Request $request, Response $response) {
        $response->getBody()->write('TODO: User NutsController to display correct available NUTZ');
        return $response;
    });





    //////////////////////////////////////////////////////////////////////////////////
    //                          LOGIN                                              //
    ////////////////////////////////////////////////////////////////////////////////

    $app->post('/login', function(Request $request, Response $response) {
        $loginController = new LoginController();
        if(isset($_POST['username']) && isset($_POST['password'])){
			if($loginController->login($_POST['username'],$_POST['password'])){
				$header = "HTTP/1.0 200 Login Successfull";
			} else {
				$header = "HTTP/1.0 403 Forbidden";
			}

		} else {
			$header = "HTTP/1.0 400 Bad Request - pass and name are required";
		}
        $response->getBody()->write($header);
        return $response->withHeader('Login-Response', $header); // TODO: Not sure how to correctly set the header here
    });

    $app->post('/logout', function(Request $request, Response $response) {
        $loginController = new LoginController();
        if($loginController->logout()) {
            $header = "HTTP/1.0 200 Logout Successfull";
        } else {
            $header = "HTTP/1.0 400 Bad Request - need to be logged in first";
        }
        $response->getBody()->write($header);
        return $response->withHeader('Login-Response', $header); // TODO: Not sure how to correctly set the header here
    });
};
