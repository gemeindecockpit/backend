<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class ConfigRouteController extends RouteController
{

   // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function home($request, $response, $args)
    {
        $self = $_SERVER['SERVER_NAME'] . '/config';
        $json_array = array(
           'organisation' => $self . '/organisation',
           'location' => $self . '/location',
           'organisation_unit' => $self . '/organisation_unit',
           'field' => $self . '/field',
           'links' => array('self' => $self)
       );

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }

//////////////////////////////////////////////////////////////
////////////////ORGANISTAION-LOCATION/////////////////////////////////////

    /**
    * Controller-function for all config/ calls regarding organisations.
    * @param $request
    * @param $response
    * @param $args
    *    Can include nuts0, nuts1, nuts2, nuts3, org_type, org_name
    * @return Response
    *    The reponse body is a JSON with all organisations visible at this layer,
    *    links to said organisations and a link to all resources in the next layer
    */
    public function get_org_by_location($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $orgs = $org_controller->get_org_by_location(...$args_indexed);
        foreach($orgs as $org) {
            if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $response->getBody()->write('Access denied');
                return $response->withStatus(403);
            }
        }
        $orgs = $this->add_fields_to_org_array($orgs);
        $json_array = [];
        $links['self'] = RouteController::get_link('config', 'location', ...$args_indexed);
        if(isset($args['org_name']) && sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation', $json_array['organisation_id']);
            foreach($json_array['fields'] as $field) {
                $links['fields'][] = $links['self'] . '/' . rawurlencode($field['field_name']);
            }
        } else {
            $json_array = array('organisations' => $orgs);
            foreach($orgs as $org) {
                $links['organisations'][] = ConfigRouteController::get_org_location_link($org);
            }
        }

        $num_args = sizeof($args);
        switch ($num_args) {
            case 0:
            case 1:
            case 2:
            case 3:
                $nuts_controller = new NutsController();
                $next_nuts_layer = 'nuts' . $num_args;
                $links[$next_nuts_layer] = [];
                $next_nuts_codes = $nuts_controller->get_next_NUTS_codes($_SESSION['user_id'], ...$args_indexed);
                foreach ($next_nuts_codes as $nuts_code) {
                    $links[$next_nuts_layer][] = $links['self'] . '/' . rawurlencode($nuts_code);
                }
                break;
            case 4:
                $links['organisation_types'] = [];
                $organisation_types = $org_controller->get_organisation_types($_SESSION['user_id'], ...$args_indexed);
                foreach($organisation_types as $type) {
                    $links['organisation_types'][] = $links['self'] . '/' . rawurlencode($type);
                }
                break;
        }

        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    /**
    * Controller-function for all config/ calls regarding fields referenced by org->field_name
    * @param $request
    * @param $response
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
    * @return Response
    *    The response body is a JSON with all fields associated with the organisation
    *    and links to the data for the fields
    */
    public function get_field_by_org_location($request, $response, $args)
    {
        $fieldController = new FieldController();
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $json_array = $fieldController->get_config_for_field_by_full_link($_SESSION['user_id'], ...$args_indexed);

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    //////////////////////////////////////////////////////////////
    ////////////////ORGANISTAION-UNIT/////////////////////////////////////


    public function get_org_unit($request, $response, $args) {
        $org_controller = new OrganisationController();

        $json_array = $org_controller->get_org_unit_config($_SESSION['user_id'], $args['org_unit']);
        $links['self'] = RouteController::get_link('config', 'organisation-unit', $args['org_unit']);
        $orgs = $org_controller->get_org_by_unit($_SESSION['user_id'], $args['org_unit']);
        foreach ($orgs as $org) {
            $links['organisations'][] = ConfigRouteController::get_org_unit_link($org);
        }
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_org_by_unit($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $orgs = $org_controller->get_org_by_unit(...$args_indexed);
        foreach($orgs as $org) {
            if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $response->getBody()->write('Access denied');
                return $response->withStatus(403);
            }
        }
        $orgs = $this->add_fields_to_org_array($orgs);
        $links['self'] = RouteController::get_link('config', 'organisation-unit', ...$args_indexed);

        $json_array = [];
        if (sizeof($args_indexed) < 2) {
            $json_array = array('organisations' => $orgs);
            foreach ($orgs as $org) {
                $links['organisations'][] = ConfigRouteController::get_org_unit_link($org);
            }
            if (sizeof($args_indexed) == 0) {
                $organisation_units = $org_controller->get_org_units($_SESSION['user_id']);
                foreach ($organisation_units as $unit) {
                    $links['organisation_units'][] = RouteController::get_link('config', 'organisation-unit', $unit);
                }
            }
        } elseif (sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation-unit', ...$args_indexed);
            foreach($json_array['fields'] as $field) {
                $links['fields'][] = $links['self'] . '/' . rawurlencode($field['field_name']);
            }
        }

        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_field_by_org_unit($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $field_controller = new FieldController();
        $user_controller = new UserController();

        $orgs = $org_controller->get_org_by_unit($args['org_unit'], $args['org_name']);
        if(sizeof($orgs) == 0) {
            return $response->getBody()->write('No organisation found');
        } else {
            $org = $orgs[0];
        }
        if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        if(!$field = $field_controller->get_field_by_name($org['organisation_id'], $args['field_name'])) {
            return $response->getBody()->write('Field not found or field name is ambiguous');
        }
        if(!$user_controller->can_see_field($_SESSION['user_id'], $field['field_id'])) {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        $links['self'] = RouteController::get_link(
            'config',
            'organisation-unit',
            $args['org_unit'],
            $args['org_name'],
            $args['field_name']
        );
        $links['data'] = RouteController::get_link('data', 'field', $field['field_id']);
        $field['links'] = $links;

        $response->getBody()->write(json_encode($field));
        return $response->withHeader('Content-type', 'application/json');
    }


    //////////////////////////////////////////////////////////////
    ////////////////ORGANISTAION-ID/////////////////////////////////////

    public function get_org_by_id($request, $response, $args)
    {
        $args_indexed = RouteController::assoc_array_to_indexed($args);
        $org_controller = new OrganisationController();

        $orgs = $org_controller->get_org_by_id(...$args_indexed);
        foreach($orgs as $org) {
            if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $response->getBody()->write('Access denied');
                return $response->withStatus(403);
            }
        }
        $orgs = $this->add_fields_to_org_array;
        $links['self'] = RouteController::get_link('config', 'organisation', ...$args_indexed);

        $json_array = [];
        if (!isset($args['org_id'])) {
            $json_array = array('organisations' => $orgs);
            foreach ($orgs as $org) {
                $links['organisations'][] = ConfigRouteController::get_org_id_link($org);
            }
        } elseif (sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation', ...$args_indexed);
            foreach($json_array['fields'] as $field) {
                $links['fields'][] = $links['self'] . '/' . rawurlencode($field['field_name']);
            }
        }

        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_field_by_org_id($request, $response, $args)
    {
        $args['URI'] = $_SERVER['REQUEST_URI'];
        $response->getBody()->write(json_encode($args));
        return $response->withHeader('Content-type', 'application/json');
    }

    private function add_fields_to_org_array($orgs) {
        $org_controller = new OrganisationController();
        for($i = 0; $i < sizeof($orgs); $i++) {
            $fields = $org_controller->get_fields($_SESSION['user_id'], $orgs[$i]['organisation_id']);
            $orgs[$i]['fields'] = $fields;
        }
        return $orgs;
    }


    //////////////////////////////////////////////////////////////
    ////////////////FIELDS/////////////////////////////////////


    public function get_field($request, $response, $args) {
        $field_controller = new FieldController();
        $fields = $field_controller->get_all();
        $self_link = RouteController::get_link('config', 'field');
        $links['self'] = $self_link;
        foreach($fields as $field) {
            $links['fields'][] = $self_link . '/' . $field['field_id'];
        }
        if(sizeof($fields) > 0) {
            $json_array['fields'] = $fields;
        }
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function get_field_by_id($request, $response, $args)
    {
        $field_controller = new FieldController();
        $field = $field_controller->get_field_by_id($_SESSION['user_id'], $args['field_id']);

        $json_array = [];
        if(sizeof($field) > 0) {
            $json_array = $field[0];
        }
        $links['self'] = RouteController::get_link('config', 'field', $args['field_id']);
        $links['data'] = RouteController::get_link('data', 'field', $args['field_id']);
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


////////////////////////////////////////////////////////////////
//
//
//
//
///////////////////////////////////////////////////////////////////////

    public function post_org($request, $response, $args)
    {
        $user_controller = new UserController();
        $org_controller = new OrganisationController();
        if (!$user_controller->can_create_organisation($_SESSION['user_id'])) {
            $response->getBody()->write('not allowed!');
            return $response->withStatus(403);
        }

        $entity = json_decode($request->getBody(), true);

        if (!isset($entity['name'])
       || !isset($entity['description'])
       || !isset($entity['organisation_unit_id'])
       || !isset($entity['contact'])
       || !isset($entity['zipcode'])) {
            $response->getBody()->write("key in organisation json is missing");
            return $response->withStatus(500);
        }

        $errno = $org_controller->insert_organisation($entity);
        if ($errno) {
            $response->getBody()->write(json_encode($errno));
            return $response->withStatus(500);
        } else {
            return $response->withStatus(200);
        }
    }

    public function post_field_by_org_id($request, $response, $args)
    {
        $user_controller = new UserController();
        $field_controller = new FieldController();
        $org_controller = new OrganisationController();

        if (!$user_controller->can_alter_organisation($_SESSION['user_id'], $args['org_id'])) {
            $response->getBody()->write('not allowed!');
            return $response->withStatus(403);
        }
        $field = json_decode($request->getBody(), true);

        if (!isset($field['field_name'])
//            || !isset($field['reference_value'])
            || !isset($field['yellow_limit'])
            || !isset($field['red_limit'])
            || !isset($field['relational_flag'])) {
            $response->getBody()->write("key in field json is missing");
            return $response->withStatus(500);
        }

        $sid = $field_controller->insert_field($field);
        $org_controller->add_field($args['org_id'], $sid);
        $response->getBody()->write(json_encode(array('field_id' => $sid)));
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * Updates the organisation.
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function put_org($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();

        $entity = json_decode($request->getBody(), true);
        if (!isset($entity['organisation_id'])
          || !isset($entity['name'])
          || !isset($entity['description'])
          || !isset($entity['organisation_unit_id'])
          || !isset($entity['contact'])
          || !isset($entity['zipcode'])
          || !isset($entity['active'])) {
            $response->getBody()->write("key in organisation json is missing");
            return $response->withStatus(500);
        }

        if(!$user_controller->can_alter_organisation($_SESSION['user_id'], $entity['organisation_id'])) {
            $response->getBody()->write('Not allowed to modify the organisation');
            return $response->withStatus(403);
        }

        $errno = $org_controller->put_org_config(
            $entity['organisation_id'],
            $entity['name'],
            $entity['description'],
            $entity['organisation_unit_id'],
            $entity['contact'],
            $entity['zipcode'],
            $entity['active']
        );

        if ($errno) {
            $response->getBody()->write(json_encode($errno));
            return $response->withStatus(500);
        } else {
            return $response->withStatus(200);
        }
    }


    /**
    * Updates the field.
    * @param $request
    * @param $response
    * @param $args
    * @return mixed
    */
    public function put_field($request, $response, $args)
    {
        $field_controller = new FieldController();
        $user_controller = new UserController();
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $field = json_decode($request->getBody(), true);

        if (!isset($field['field_id'])
            || !isset($field['field_name'])
            || !isset($field['reference_value'])
            || !isset($field['yellow_limit'])
            || !isset($field['red_limit'])
            || !isset($field['relational_flag'])) {
            $response->getBody()->write("key in field json is missing");
            return $response->withStatus(500);
        }

        $permission = $user_controller->can_alter_field($_SESSION['user_id'], $field['field_id']);
        if(!$permission) {
            $response->getBody()->write('Not allowed to alter the field');
            return $response->withStatus(403);
        }

        $errno = $field_controller->put_field_config(
            $field['field_id'],
            $field['field_name'],
            $field['reference_value'],
            $field['yellow_limit'],
            $field['red_limit'],
            $field['relational_flag']
        );

        if ($errno) {
            $response->getBody()->write($errno);
            return $response->withStatus(500);
        } else {
            return $response->withStatus(200);
        }
    }

    public function delete_org($request, $response, $args)
    {
        $org_controller = new OrganisationController();
       $user_controller = new UserController();
       $permission = $user_controller->can_alter_organisation($_SESSION['user_id'], $args['org_id']);
       if(!$permission) {
           return $response->withStatus(403);
       }

       $errno = $org_controller->delete_organisation($args['org_id']);
       if($errno) {
           $response->getBody()->write($errno);
           return $response->withStatus(500);
       } else {
           return $response->withStatus(200);
       }
    }

    public function delete_field($request, $response, $args)
    {
        $field_controller = new FieldController();
       $user_controller = new UserController();

       $permission = $user_controller->can_alter_field($_SESSION['user_id'], $args['field_id']);
       if(!$permission) {
           return $response->withStatus(403);
       }

       $errno = $field_controller->delete_field($args['field_id']);

       if($errno) {
           $response->getBody()->write($errno);
           return $response->withStatus(500);
       } else {
           return $response->withStatus(200);
       }
    }

    public static function get_org_location_link($org) {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'].'/config/location/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['organisation_type'].'/'.$org['organisation_name'];
    }

    public static function get_org_unit_link($org)
    {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'] . '/config/organisation-unit/' . $org['organisation_unit'] . '/' . $org['organisation_name'];
    }

    public static function get_org_id_link($org)
    {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'] . '/config/organisation/' . $org['organisation_id'];
    }
}
?>
