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
        $response->getBody()->write(json_encode(array('data' => 'http://litwinow.xyz/data', 'config' => 'http://litwinow.xyz/config', 'user' => 'http://litwinow.xyz/user', 'login' => 'http://litwinow.xyz/login', 'logout' => 'http://litwinow.xyz/logout')));
        return $response->withHeader('Content-type', 'application/json');
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
		$fieldController = new FieldController();
        $field_name = $args_assoc['field'];
        unset($args_assoc['field']);
        $args_indexed = assoc_array_to_indexed($args_assoc);
        $org = $orgController->get_config_for_organisations_by_nuts0123_type_name($_SESSION['user_id'], ...$args_indexed);
        $org_id = -1;
        if(isset($org['organisations'][0])) { // $org is already formatted as the json_array... (See "AbstractController::format_json")
            $org_id = $org['organisations'][0]['organisation_id'];
        }
        $response->getBody()->write(json_encode($fieldController->get_config_for_field_by_name($_SESSION['user_id'], $org_id, $field_name)));
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


    ////////////////
    ///// GET  /////
    ////////////////

	$app->get('/data', function (Request $request, Response $response) {
		$data_controller = new DataController();
		$json_array = $data_controller->get_organisation_and_field_ids($_SESSION['user_id']);
		$response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
	});

    $app->get('/data/field/{field_id:[0-9]+}', function (Request $request, Response $response, $args_assoc) {
        $data_controller = new DataController();
        $query_parameters = $request->getQueryParams();
        $json_array;
        if(isset($query_parameters['last'])) {
            $json_array = $data_controller->get_data_from_past_x_days_by_field_id($_SESSION['user_id'], $args_assoc['field_id'], $query_parameters['last']);
        } else {
            $json_array = $data_controller->get_latest_data_by_field_id($_SESSION['user_id'], $args_assoc['field_id']);
        }

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    });

	$app->get('/data/organisation/{organisation_id:[0-9]+}', function (Request $request, Response $response, $args_assoc) {
		$data_controller = new DataController();
		$json_array = $data_controller->get_fields_by_organisation_id($_SESSION['user_id'], $args_assoc['organisation_id']);
		$response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
	});

    $app->get('/data/organisation/{organisation_id:[0-9]+}/{field_name}', function (Request $request, Response $response, $args_assoc) {
        $data_controller = new DataController();
        $query_parameters = $request->getQueryParams();
        if(isset($query_parameters['last'])) {
            $json_array = $data_controller->get_data_from_past_x_days_by_field_name(
                $_SESSION['user_id'], $args_assoc['organisation_id'], $args_assoc['field_name'], $query_parameters['last']
            );
        } else {
            $json_array = $data_controller->get_latest_data_by_field_name(
                $_SESSION['user_id'], $args_assoc['organisation_id'], $args_assoc['field_name']
            );
        }

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    });
/*
	$app->get('/data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{type}/{name}', function (Request $request, Response $response, $args_assoc) {
		$data_controller = new DataController();
        $query_parameters = $request->getQueryParams();
		$args_indexed = assoc_array_to_indexed($args_assoc);
		if(isset($query_parameters['last'])) {
			$args_indexed[] = $query_parameters['last'];
            $json_array = $data_controller->get_data_from_past_x_days_by_full_organisation_link($_SESSION['user_id'], ...$args_indexed);
        } else {
        }

		return $response;
	});


	$app->get('/data', function (Request $request, Response $response) {
		$orgController = new OrganisationController();
		$response->getBody()->write(json_encode($orgController->get_all_data($_SESSION['user_id'])));
		return $response->withHeader('Content-type', 'application/json');
	});
*/

	$app->get('/data/{nuts0}', function (Request $request, Response $response, $args_assoc) {
		$orgController = new OrganisationController();
		$args_indexed = assoc_array_to_indexed($args_assoc);
		$response->getBody()->write(json_encode($orgController->get_data_for_organisations_by_nuts0($_SESSION['user_id'], ...$args_indexed)));
		return $response->withHeader('Content-type', 'application/json');
	});

	$app->get('/data/{nuts0}/{nuts1}', function (Request $request, Response $response, $args_assoc) {
		$orgController = new OrganisationController();
		$args_indexed = assoc_array_to_indexed($args_assoc);
		$response->getBody()->write(json_encode($orgController->get_data_for_organisations_by_nuts01($_SESSION['user_id'], ...$args_indexed)));
		return $response->withHeader('Content-type', 'application/json');
	});

	$app->get('/data/{nuts0}/{nuts1}/{nuts2}', function (Request $request, Response $response, $args_assoc) {
		$orgController = new OrganisationController();
		$args_indexed = assoc_array_to_indexed($args_assoc);
		$response->getBody()->write(json_encode($orgController->get_data_for_organisations_by_nuts012($_SESSION['user_id'], ...$args_indexed)));
		return $response->withHeader('Content-type', 'application/json');
	});

	$app->get('/data/{nuts0}/{nuts1}/{nuts2}/{nuts3}', function (Request $request, Response $response, $args_assoc) {
		$orgController = new OrganisationController();
		$args_indexed = assoc_array_to_indexed($args_assoc);
		$response->getBody()->write(json_encode($orgController->get_data_for_organisations_by_nuts0123($_SESSION['user_id'], ...$args_indexed)));
		return $response->withHeader('Content-type', 'application/json');
		});



    ////////////////
    ///// POST /////
    ////////////////

    $app->post('/data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{organisation_type}/{organisation_name}/{year:[1|2|3][0-9][0-9][0-9]}/{month:[0-9][0-9]}/{day:[0-9][0-9]}',
        function (Request $request, Response $response, $args_assoc) {
            $data_controller = new DataController();
            if (!isset($_POST['fields'])) {
                return $response->withHeader("HTTP/1.0 400 Bad Request - fields need to be set", '{error: No field values set}');
            }
            $field_values = json_decode($_POST['fields'], true);
            $date = $args_assoc['year'] . '-' . $args_assoc['month'] . '-' . $args_assoc['day'];
            $data_controller->insert_multiple_values_for_date($_SESSION['user_id'], $field_values, $date);
            $response->getBody()->write($_POST['fields']);
            return $response;
        });

        $app->post('/data/{year:[1|2|3][0-9][0-9][0-9]}/{month:[0-9][0-9]}/{day:[0-9][0-9]}',
            function (Request $request, Response $response, $args_assoc) {
                $data_controller = new DataController();
                if (!isset($_POST['fields'])) {
                    return $response->withHeader("HTTP/1.0 400 Bad Request - fields need to be set", '{error: No field values set}');
                }
                $field_values = json_decode($_POST['fields'], true);
                $date = $args_assoc['year'] . '-' . $args_assoc['month'] . '-' . $args_assoc['day'];
                $data_controller->insert_multiple_values_for_date($_SESSION['user_id'], $field_values, $date);
                $response->getBody()->write($_POST['fields']);
                return $response;
            });

        $app->post('/data/{organisation_id:[0-9]+}/{year:[1|2|3][0-9][0-9][0-9]}/{month:[0-9][0-9]}/{day:[0-9][0-9]}',
            function (Request $request, Response $response, $args_assoc) {
                $data_controller = new DataController();
                if (!isset($_POST['fields'])) {
                    return $response->withHeader("HTTP/1.0 400 Bad Request - fields need to be set", '{error: No field values set}');
                }
                $field_values = json_decode($_POST['fields'], true);
                $date = $args_assoc['year'] . '-' . $args_assoc['month'] . '-' . $args_assoc['day'];
                $data_controller->insert_multiple_values_for_date_by_field_name($_SESSION['user_id'], $args_assoc['organisation_id'], $field_values, $date);
                $response->getBody()->write($_POST['fields']);
                return $response;
            });




    //////////////////////////////////////////////////////////////////////////////////
    //                          LOGIN                                              //
    ////////////////////////////////////////////////////////////////////////////////


    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->post('/login', function(Request $request, Response $response) {
        $loginController = new LoginController();
				$status = 200;
        if(isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] !== ''
		&& isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] !== ''){
			if($loginController->login($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])){
				$header = "HTTP/1.0 200 Login Successfull";
				$status = 200;
			} else {
				$header = "HTTP/1.0 403 Forbidden";
				$status = 403;
			}

		} else {
			$header = "HTTP/1.0 400 Bad Request - 'username' and 'password' are required";
		}
        $response->getBody()->write($header);
        return $response->withStatus($status, 'test')->withHeader('Login-Response', $header);
    });

    $app->post('/logout', function(Request $request, Response $response) {
        $loginController = new LoginController();
				$status = 200;
        if($loginController->logout()) {
            $header = "HTTP/1.0 200 Logout Successfull";
						$status = 200;
        } else {
            $header = "HTTP/1.0 400 Bad Request - need to be logged in first";
						$status = 400;
        }
        $response->getBody()->write($header);
        return $response->withStatus($status, 'test')->withHeader('Login-Response', $header);
    });
};
