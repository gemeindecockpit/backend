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
           'organisation_type' => $self . '/organisation-type',
           'organisation_group' => $self . '/organisation-group',
           'field' => $self . '/field',
           'links' => array('self' => $self)
       );

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }

    private function get_org_config($endpoint, $args) {
        $org_controller = new OrganisationController();
        $org_ids = $org_controller->get_org_ids($endpoint, $_SESSION['user_id'], $args);
        $orgs = $org_controller->get_orgs_by_id($org_ids);
        $orgs = $this->add_fields_to_org_array($orgs);
        return $orgs;
    }

    private function add_field_links($org, $links) {
        foreach($org['fields'] as $field) {
            $field_link = $links['self'] . '/' . rawurlencode($field['field_name']);
            $links['fields'][] = array(
                'field_id' => $field['field_id'],
                'field_name' => $field['field_name'],
                'href' => $field_link
            );
        }
        return $links;
    }

    private function add_org_links($orgs, $links, $referenced_by = 'name') {
        switch ($referenced_by) {
            case 'name':
                foreach($orgs as $org) {
                    $org_link = $links['self'] . '/' . rawurlencode($org['organisation_name']);
                    $links['organisations'][] = array(
                        'organisation_id' => $org['organisation_id'],
                        'organisation_name' => $org['organisation_name'],
                        'href' => $org_link
                    );
                }
                break;
            case 'id':
                foreach($orgs as $org) {
                    $org_link = $links['self'] . '/' . rawurlencode($org['organisation_name']);
                    $links['organisations'][] = array(
                        'organisation_id' => $org['organisation_id'],
                        'organisation_name' => $org['organisation_name'],
                        'href' => $org_link
                    );
                }
                break;
        }
        return $links;
    }

    private function add_nuts_links($args, $links) {
        $nuts_controller = new NutsController();

        $next_nuts_layer = 'nuts' . sizeof($args);
        $links[$next_nuts_layer] = [];
        $next_nuts_codes = $nuts_controller->get_next_NUTS_codes($_SESSION['user_id'], $args);
        foreach ($next_nuts_codes as $nuts_code) {
            $nuts_link = $links['self'] . '/' . rawurlencode($nuts_code);
            $links[$next_nuts_layer][] = array(
                'nuts_region' => $nuts_code,
                'href' => $nuts_link
            );
        }
        return $links;
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
        $user_controller = new UserController();


        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $orgs = $this->get_org_config('location', $args);

        $json_array = [];
        $links['self'] = RouteController::get_link('config', 'location', ...$args_indexed);

        if(isset($args['org_name']) && sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation', $json_array['organisation_id']);
            $links = $this->add_field_links($json_array, $links);
        } else {
            $json_array = array('organisations' => $orgs);
            $links = $this->add_org_links($orgs, $links);
        }

        if(sizeof($args) < 4) {
            $links = $this->add_nuts_links($args, $links);
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
    *    Must include nuts0, nuts1, nuts2, nuts3, org_name and field_name
    * @return Response
    *    The response body is a JSON with all fields associated with the organisation
    *    and links to the data for the fields
    */
    public function get_field_by_org_location($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $field_controller = new FieldController();
        $user_controller = new UserController();

        $field_name = $args['field_name'];
        unset($args['field_name']);
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $orgs = $org_controller->get_org_by_location(...$args_indexed);

        if(sizeof($orgs) == 0) {
            $response->getBody()->write('Not found');
            return $response->withStatus(500);
        }

        $org_id = $orgs[0]['organisation_id'];

        $field = $field_controller->get_field_by_name($org_id, $field_name);
        if(sizeof($field) == 0) {
            $response->getBody()->write('Not found');
            return $response->withStatus(500);
        }

        if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org_id)
            || !$user_controller->can_see_field($_SESSION['user_id'], $field['field_id']))
        {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        $args_indexed[] = $field_name;
        $links['self'] = RouteController::get_link('config', 'location', ...$args_indexed);
        $links['data'] = RouteController::get_link('data', 'field', $field['field_id']);
        $field['links'] = $links;

        $response->getBody()->write(json_encode($field));
        return $response->withHeader('Content-type', 'application/json');
    }

    //////////////////////////////////////////////////////////////
    ////////////////ORGANISTAION-TYPE/////////////////////////////////////

    public function get_all_types($request, $response, $args) {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();
        $query_result = $org_controller->get_all();
        $orgs = [];
        foreach($query_result as $org) {
            if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $orgs[] = $org;
                $org_link = RouteController::get_link(
                    'config',
                    'organisation-type',
                    $org['organisation_type'],
                    $org['organisation_name']
                );
                $links['organistaions'][] = array(
                    'organisation_id' => $org['organisation_id'],
                    'organisation_name' => $org['organisation_name'],
                    'href' => $org_link
                );
            }
        }
        $types = $org_controller->get_organisation_types($_SESSION['user_id']);

        $links['self'] = RouteController::get_link('config', 'organisation-type');
        foreach($types as $type) {
            $type_link = RouteController::get_link('config', 'organisation-type', $type['organisation_type_name']);
            $links['organisation_types'][] = array(
                'organisation_type_id' => $type['organisation_type_id'],
                'organisation_type_name' => $type['organisation_type_name'],
                'href' => $type_link
            );
        }
        $json_array = array('organisations' => $orgs, 'links' => $links);

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function get_type($request, $response, $args) {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();

        if(!$org_type = $org_controller->get_type_by_name($args['org_type']))
            return $response->withStatus(500);
        if(!$user_controller->can_see_type($_SESSION['user_id'], $org_type['organisation_type_id']))
            return $response->withStatus(403);
        $links['self'] = RouteController::get_link('config', 'organisation-type', $args['org_type']);

        $query_result = $org_controller->get_all_orgs_by_type($args['org_type']);
        $orgs = [];
        foreach($query_result as $org) {
            if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $orgs[] = $org;
                $org_link = RouteController::get_link('config', 'organisation-type', $args['org_type'], $org['organisation_name']);
                $links['organistaions'][] = array(
                    'organisation_id' => $org['organisation_id'],
                    'organisation_name' => $org['organisation_name'],
                    'href' => $org_link
                );
            }
        }
        $org_type['required_fields'] = $org_controller->get_required_fields($org_type['organisation_type_id']);
        $org_type['organisations'] = $orgs;
        $org_type['links'] = $links;

        $response->getBody()->write(json_encode($org_type));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function get_org_by_type($request, $response, $args) {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();

        if(!$org = $org_controller->get_org_by_type($args['org_type'], $args['org_name'])) {
                return $response->withStatus(500);
        }
        $org['fields'] = $org_controller->get_fields($_SESSION['user_id'], $org['organisation_id']);
        $links['self'] = RouteController::get_link('config', 'organistaion-type', $args['org_type'], $args['org_name']);
        $links['data'] = RouteController::get_link('data', 'organistaion-type', $args['org_type'], $args['org_name']);
        foreach($org['fields'] as $field) {
            $field_link = RouteController::get_link('config', 'organistaion-type', $args['org_type'], $args['org_name'], $field['field_name']);
            $links['fields'][] = array(
                'field_id' => $field['field_id'],
                'field_name' => $field['field_name'],
                'href' => $field_link
            );
        }
        $org['links'] = $links;
        $response->getBody()->write(json_encode($org));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function get_field_by_org_type($request, $response, $args) {
        $org_controller = new OrganisationController();
        $field_controller = new FieldController();
        $user_controller = new UserController();

        if(!$org = $org_controller->get_org_by_type($args['org_type'], $args['org_name'])) {
                return $response->withStatus(500);
        }
        if(!$user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        if(!$field = $field_controller->get_field_by_name($org['organisation_id'], $args['field_name'])) {
            $response->getBody()->write('Field not found or field name is ambiguous');
            return $response->withStatus(500);
        }
        if(!$user_controller->can_see_field($_SESSION['user_id'], $field['field_id'])) {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        $links['self'] = RouteController::get_link(
            'config',
            'organisation-type',
            $args['org_type'],
            $args['org_name'],
            $args['field_name']
        );
        $links['data'] = RouteController::get_link('data', 'field', $field['field_id']);
        $field['links'] = $links;

        $response->getBody()->write(json_encode($field));
        return $response->withHeader('Content-type', 'application/json');
    }

    //////////////////////////////////////////////////////////////
    ////////////////ORGANISTAION-GROUP/////////////////////////////////////


    public function get_org_group($request, $response, $args) {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();

        $json_array = $org_controller->get_group_by_name($args['org_group']);
        if(!isset($json_array['organisation_group_id'])) {
            $response->getBody()->write('No matching group found');
            return $response->withStatus(500);
        }
        if(!$user_controller->can_see_group($_SESSION['user_id'], $json_array['organisation_group_id'])) {
            return $response->withStatus(403);
        }
        $links['self'] = RouteController::get_link('config', 'organisation-group', $args['org_group']);
        $links['data'] = RouteController::get_link('data', 'organisation-group', $args['org_group']);

        $query_result = $org_controller->get_org_by_group($args['org_group']);
        $orgs = [];
        foreach($query_result as $org) {
            if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $orgs[] = $org;
                $org_link = RouteController::get_link('config', 'organisation-group', $args['org_group'], $org['organisation_name']);
                $links['organistaions'][] = array(
                    'organisation_id' => $org['organisation_id'],
                    'organisation_name' => $org['organisation_name'],
                    'href' => $org_link
                );
            }
        }
        $json_array['organisations'] = $orgs;
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_org_by_group($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();
        $args_indexed = RouteController::assoc_array_to_indexed($args);

        $query_result = $org_controller->get_org_by_group(...$args_indexed);
        $orgs = [];
        foreach($query_result as $org) {
            if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $orgs[] = $org;
            }
        }
        $orgs = $this->add_fields_to_org_array($orgs);
        $links['self'] = RouteController::get_link('config', 'organisation-group', ...$args_indexed);

        $json_array = [];
        if (sizeof($args_indexed) < 2) {
            $json_array = array('organisations' => $orgs);
            foreach ($orgs as $org) {
                $org_link = ConfigRouteController::get_org_group_link($org);
                $links['organistaions'][] = array(
                    'organisation_id' => $org['organisation_id'],
                    'organisation_name' => $org['organisation_name'],
                    'href' => $org_link
                );
            }
            if (sizeof($args_indexed) == 0) {
                $organisation_groups = $org_controller->get_org_groups($_SESSION['user_id']);
                foreach ($organisation_groups as $group) {
                    $org_group_link = RouteController::get_link('config', 'organisation-group', $group['organisation_group_name']);
                    $links['organistaions_groups'][] = array(
                        'organisation_group_id' => $group['organisation_group_id'],
                        'organisation_group_name' => $group['organisation_group_name'],
                        'href' => $org_group_link
                    );
                }
            }
        } elseif (sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation-group', ...$args_indexed);
            foreach($json_array['fields'] as $field) {
                $field_link = $links['self'] . '/' . rawurlencode($field['field_name']);
                $links['fields'][] = array(
                    'field_id' => $field['field_id'],
                    'field_name' => $field['field_name'],
                    'href' => $field_link
                );
            }
        }

        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_field_by_org_group($request, $response, $args)
    {
        $org_controller = new OrganisationController();
        $field_controller = new FieldController();
        $user_controller = new UserController();

        $orgs = $org_controller->get_org_by_group($args['org_group'], $args['org_name']);
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
            $response->getBody()->write('Field not found or field name is ambiguous');
            return $response->withStatus(403);
        }
        if(!$user_controller->can_see_field($_SESSION['user_id'], $field['field_id'])) {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        $links['self'] = RouteController::get_link(
            'config',
            'organisation-group',
            $args['org_group'],
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
        $user_controller = new UserController();

        if (!isset($request->getQueryParams()['date']))
            $date = date('Y-m-d');
        else
            $date = $request->getQueryParams()['date'];

        $query_result = $org_controller->get_org_by_id(...$args_indexed);
        $orgs = [];
        foreach($query_result as $org) {
            if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
                $orgs[] = $org;
            }
        }
        $orgs = $this->add_fields_to_org_array($orgs);
        $links['self'] = RouteController::get_link('config', 'organisation', ...$args_indexed);

        $json_array = [];
        if (!isset($args['org_id'])) {
            $json_array = array('organisations' => $orgs);
            foreach ($orgs as $org) {
                $org_link = ConfigRouteController::get_org_id_link($org);
                $links['organistaions'][] = array(
                    'organisation_id' => $org['organisation_id'],
                    'organisation_name' => $org['organisation_name'],
                    'href' => $org_link
                );
            }
        } elseif (sizeof($orgs) > 0) {
            $json_array = $orgs[0];
            $links['data'] = RouteController::get_link('data', 'organisation', ...$args_indexed);
            foreach($json_array['fields'] as $field) {
                $field_link = $links['self'] . '/' . rawurlencode($field['field_name']);
                $field_link .= (isset($request->getQueryParams()['date']) ? "?date=$date" : "");
                $links['fields'][] = array(
                    'field_id' => $field['field_id'],
                    'field_name' => $field['field_name'],
                    'href' => $field_link
                );
            }
        }

        $links['self'] .= (isset($request->getQueryParams()['date']) ? "?date=$date" : "");
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }


    public function get_field_by_org_id($request, $response, $args)
    {
        $field_controller = new FieldController();
        $user_controller = new UserController();

        if (!isset($request->getQueryParams()['date']))
            $date = date('Y-m-d');
        else
            $date = $request->getQueryParams()['date'];

        $field = $field_controller->get_field_by_name($args['org_id'], $args['field_name'], $date);

        if(!$field) {
            $response->getBody()->write('Not found');
            return $response->withStatus(500);
        }

        if(!$user_controller->can_see_organisation($_SESSION['user_id'], $args['org_id'])
            || !$user_controller->can_see_field($_SESSION['user_id'], $field['field_id']))
        {
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        if (isset($request->getQueryParams()['date'])) {
            $split_date = preg_split('/-/', $date);
            $links['self'] = RouteController::get_link('config', 'organisation', $args['org_id'], $args['field_name']);
            $links['data'] = RouteController::get_link('data', 'field', $field['field_id'], $split_date[0], $split_date[1], $split_date[2]);
            $links['self'] .= "?date=$date";
        } else {
            $links['self'] = RouteController::get_link('config', 'organisation', $args['org_id'], $args['field_name']);
            $links['data'] = RouteController::get_link('data', 'field', $field['field_id']);
        }
        $field['links'] = $links;

        $response->getBody()->write(json_encode($field));
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
            $field_link = $self_link . '/' . $field['field_id'];
            $links['fields'][] = array(
                'field_id' => $field['field_id'],
                'field_name' => $field['field_name'],
                'href' => $field_link
            );
        }
        if(sizeof($fields) > 0) {
            $json_array['fields'] = $fields;
        }
        $json_array['links'] = $links;

        $response->getBody()->write(json_encode($json_array));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function get_field_by_id($request, $response, $args) {

        $field_controller = new FieldController();
        $user_controller = new UserController();

        if(!$user_controller->can_see_field($_SESSION['user_id'], $args['field_id']))
            return $response->withStatus(403);

        $field = $field_controller->get_field_by_id_and_date($args['field_id']);

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
        $field_controller = new FieldController();

        if (!$user_controller->can_create_organisation($_SESSION['user_id'])) {
            $response->getBody()->write('not allowed!');
            return $response->withStatus(403);
        }

        $body = json_decode($request->getBody(), true);

        if ($err_msg = $this->is_valid_post_org_body($body)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }

        if($org_type = $org_controller->get_type_by_name($body['organisation_type'])) {
            $org_type_id = (int)$org_type['organisation_type_id'];
            $required_fields = $org_controller->get_required_fields($org_type_id);
            if(sizeof($required_fields) > 0) {
                if(!isset($body['fields'])) {
                    $response->getBody()->write('initial fields not set, but required');
                    return $response->withStatus(500);
                } else if ($err_msg = $this->all_required_fields_set($body['fields'], $required_fields)) {
                    $response->getBody()->write($err_msg);
                    return $response->withStatus(500);
                }
            }
        } else {
            $org_type_id = (int)$org_controller->create_new_type($body['organisation_type']);
        }

        if($org_group = $org_controller->get_group_by_name($body['organisation_group'])) {
            $org_group_id = (int)$org_group['organisation_group_id'];
        } else {
            $org_group_id = (int)$org_controller->create_new_group($body['organisation_group']);
        }

        if($org_type = $org_controller->get_type_by_name($body['organisation_type'])) {
            $org_type_id = (int)$org_type['organisation_type_id'];
        } else {
            $org_type_id = (int)$org_controller->create_new_type($body['organisation_type']);
        }

        if($org_group = $org_controller->get_group_by_name($body['organisation_group'])) {
            $org_group_id = (int)$org_group['organisation_group_id'];
        } else {
            $org_group_id = (int)$org_controller->create_new_group($body['organisation_group']);
        }

        $org_id = $org_controller->insert_organisation(
            $body['organisation_name'],
            $org_type_id,
            $org_group_id,
            $body['description'],
            $body['contact'],
            $body['zipcode']
        );

        if ($org_id == 0) {
            return $response->withStatus(500);
        }
        $permissions = array('can_see_organisation' => array(array('organisation_id' => $org_id, 'can_alter' => 1, 'priority' => 0)));        $user_controller->insert_permissions($_SESSION['user_id'], $permissions);

        if(isset($body['fields']))
        {
            $fields = $body['fields'];
            foreach($fields as $field) {
                $sid = $field_controller->insert_field($field);
                $org_controller->add_field($org_id, $sid);
                $permissions = array('can_see_field' => array(array('field_id' => $sid, 'can_alter' => 1)));
                $user_controller->insert_permissions($_SESSION['user_id'], $permissions);
            }
        }
        return $response->withStatus(200);
    }

    public function post_org_type($request, $response, $args) {
        $user_controller = new UserController();
        $org_controller = new OrganisationController();

        if (!$user_controller->can_create_organisation_type($_SESSION['user_id'])) {
            $response->getBody()->write('not allowed!');
            return $response->withStatus(403);
        }

        $body = json_decode($request->getBody(), true);

        if ($err_msg = $this->is_valid_post_org_type_body($body)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }

        if($org_type = $org_controller->get_type_by_name($body['organisation_type_name'])) {
            $response->getBody()->write('Type already exists');
            return $response->withStatus(500);
        }

        $org_type_id = (int)$org_controller->create_new_type($body['organisation_type_name'], $body['required_fields']);
        $response->getBody()->write(json_encode(array('organisation_type_id' => $org_type_id, 'organisation_type_name' => $body['organisation_type_name'])));
        return $response->withStatus(200);
    }


    public function post_org_group($request, $response, $args) {
      $org_controller = new OrganisationController();

      $body = json_decode($request->getBody(), true);

      if($err_msg = $this->is_valid_post_org_group_body($body)) {
        $response->getBody()->write($err_msg);
        return $response->withStatus(500);
      }

      if($org_type = $org_controller->get_group_by_name($body['organisation_group_name'])) {
          $response->getBody()->write('Group already exists');
          return $response->withStatus(500);
      }


      $org_group_id = (int)$org_controller->create_new_group($body['organisation_group_name']);
      $response->getBody()->write(json_encode(array('organisation_group_id' => $org_group_id, 'organisation_group_name' => $body['organisation_group_name'])));
      return $response->withStatus(200);
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


        if ($err_msg = $this->is_valid_post_field_body($field)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }

        $sid = $field_controller->insert_field($field);
        $org_controller->add_field($args['org_id'], $sid);
        $permissions = array('can_see_field' => array(array('field_id' => $sid, 'can_alter' => 1)));
        $user_controller->insert_permissions($_SESSION['user_id'], $permissions);
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

        $body = json_decode($request->getBody(), true);

        if ($err_msg = $this->is_valid_put_org_body($body)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }

        if(!$user_controller->can_alter_organisation($_SESSION['user_id'], $body['organisation_id'])) {
            $response->getBody()->write('Not allowed to modify the organisation');
            return $response->withStatus(403);
        }

        if($org_type = $org_controller->get_organisation_type($body['organisation_type'])) {
            $org_type_id = $org_type['organisation_type_id'];
        } else {
            $org_type_id = $org_controller->create_new_type($body['organisation_type']);
        }

        if($org_group = $org_controller->get_organisation_group($body['organisation_group'])) {
            $org_group_id = $org_type['organisation_group_id'];
        } else {
            $org_group_id = $org_controller->create_new_group($body['organisation_group']);
        }

        $errno = $org_controller->put_org_config(
            $body['organisation_id'],
            $body['organisation_name'],
            $body['description'],
            $org_type_id,
            $org_group_id,
            $body['contact'],
            $body['zipcode'],
            $body['active']
        );

        if(isset($body['add_fields'])) {
            $fields = $body['add_fields'];
            foreach($fields as $field) {
                $org_controller->add_field($body['organisation_id'], $field['field_id'], $field['priority']);
            }
        }

        if(isset($body['remove_fields'])) {
            $fields = $body['remove_fields'];
            foreach($fields as $field) {
                $org_controller->remove_field($body['organisation_id'], $field['field_id']);
            }
        }

        if ($errno) {
            $response->getBody()->write(json_encode($errno));
            return $response->withStatus(500);
        } else {
            return $response->withStatus(200);
        }
    }

    public function put_org_type($request, $response, $args) {
        $org_controller = new OrganisationController();
        $user_controller = new UserController();

        $body = json_decode($request->getBody(), true);

        if ($err_msg = $this->is_valid_put_org_type_body($body)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }
        $errno = null;
        if($org_type = $org_controller->get_type_by_id($body['organisation_type_id'])) {
            if($body['organisation_type_name'] != $org_type['organisation_type_name']) {
                if(is_null($org_controller->get_type_by_name($body['organisation_type_name']))) {
                    $errno = $org_controller->put_org_type(
                        $body['organisation_type_id'],
                        $body['organisation_type_name']
                    );
                } else {
                    $response->getBody()->write('new name is already taken');
                    return $response->withStatus(500);
                }
            }
        }



        if(!$errno) {
            $errno = $org_controller->update_required_fields(
                $body['organisation_type_id'],
                $body['required_fields']);
        }
        if($errno) {
            $response->getBody()->write(json_encode($errno));
            return $response->withStatus(500);
        }
        return $response->withStatus(200);
    }


    public function put_org_group($request, $response, $args) {
      $org_controller = new OrganisationController();

      $body = json_decode($request->getBody(), true);

      if ($err_msg = $this->is_valid_put_org_group_body($body)) {
          $response->getBody()->write($err_msg);
          return $response->withStatus(500);
      }
      $errno = null;
      if($org_group = $org_controller->get_group_by_id($body['organisation_group_id'])) {
          if(!$org_controller->get_group_by_name($body['organisation_group_name'])) {
              $errno = $org_controller->put_org_group($body);
          } else {
              $response->getBody()->write('new name is already taken');
              return $response->withStatus(500);
          }
      }

      if($errno) {
          $response->getBody()->write(json_encode($errno));
          return $response->withStatus(500);
      }
      return $response->withStatus(200);
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

        if ($err_msg = $this->is_valid_put_field_body($field)) {
            $response->getBody()->write($err_msg);
            return $response->withStatus(500);
        }

        $permission = $user_controller->can_alter_field($_SESSION['user_id'], $field['field_id']);
        if(!$permission) {
            $response->getBody()->write('Not allowed to alter the field');
            return $response->withStatus(403);
        }

        $errno = $field_controller->put_field_config($field);

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

    private function is_valid_post_org_body($body) {
        if(is_null($body))
            return 'not a valid JSON';
        if(!isset($body['organisation_name']))
            return 'organisation_name not set';
        if(!isset($body['description']))
            return 'description not set';
        if(!isset($body['organisation_type']))
            return 'organisation_type not set';
        if(!isset($body['organisation_group']))
            return 'organisation_group not set';
        if(!isset($body['contact']))
            return 'contact not set';
        if(!isset($body['zipcode']))
            return 'zipcode not set';
        if(!isset($body['fields']))
            return;
        $fields = $body['fields'];
        if(!is_array($fields))
            return 'fields must be an array';
        foreach($fields as $field) {
            if(!isset($field['priority']))
                return 'priority must set for each field';
            if($err_msg = $this->is_valid_post_field_body($field))
                return $err_msg;
        }

        return;
    }

    private function all_required_fields_set($initial_fields, $required_fields) {
        $initial_fields_map = array();

        foreach($initial_fields as $initial_field) {
            $initial_fields_map[$initial_field['field_name']]['relational_flag'] = $initial_field['relational_flag'];
        }
        foreach($required_fields as $required_field) {
            if(!isset($initial_fields_map[$required_field['field_name']]))
                return 'all required fields must be set';
            if($initial_fields_map[$required_field['field_name']]['relational_flag'] != $required_field['relational_flag'])
                return 'at least one relational_flag is set incorrectly';
        }
        return;
    }

    private function is_valid_post_org_type_body($body) {
        if(is_null($body))
            return 'not a valid JSON';
        if(!isset($body['organisation_type_name']))
            return 'organisation_type_name must be set';
        if(!isset($body['required_fields']))
            return 'required_fields must be set';
        if(!is_array($body['required_fields']))
            return 'required_fields must be given as an array';
        foreach($body['required_fields'] as $field) {
            if(!isset($field['field_name']))
                return 'field_name must be set for each field';
            if(!isset($field['relational_flag']))
                return 'relational_flag must be set for each field';
        }
        return;
    }

    private function is_valid_post_org_group_body($body) {
      if(is_null($body))
        return 'not a valid JSON';
      if(!isset($body['organisation_group_name']))
        return 'organisation_group_name must be set';
      return;
    }

    private function is_valid_post_field_body($body) {
        if(is_null($body))
            return 'not a valid JSON';
        if(!isset($body['field_name']))
            return 'field_name not set';
        if(!isset($body['yellow_limit']))
            return 'yellow_limit not set';
        if(!isset($body['red_limit']))
            return 'red_limit not set';
        if(!isset($body['relational_flag']))
            return 'relational_flag not set';
        if($body['relational_flag'] == 1 && !isset($body['reference_value']))
            return 'reference_value must be set for relational field';
        return;
    }

    private function is_valid_put_org_body($body) {
        if($err_msg = $this->is_valid_post_org_body($body))
            return $err_msg;
        if(!isset($body['organisation_id']))
            return 'organisation_id not set';


        if(isset($body['add_fields'])) {
            $add_fields = $body['add_fields'];
            if(!is_array($add_fields))
                return 'added fields must be given as an array';
            foreach($add_fields as $field) {
                if(!isset($field['field_id']))
                    return 'each added field must be identified by an id';
                if(!isset($field['priority']))
                    return 'priority must set for each field';
            }
        }

        if(isset($body['remove_fields'])) {
            $add_fields = $body['add_fields'];
            if(!is_array($add_fields))
                return 'removed fields must be given as an array';
            foreach($add_fields as $field) {
                if(!isset($field['field_id']))
                    return 'each removed field must be identified by an id';
            }
        }
        return;
    }

    private function is_valid_put_org_type_body($body) {
        if($err_msg = $this->is_valid_post_org_type_body($body))
            return $err_msg;
        if(!isset($body['organisation_type_id']))
            return 'organisation_type_id not set';
        return;
    }


    private function is_valid_put_org_group_body($body) {
        if($err_msg = $this->is_valid_post_org_group_body($body))
            return $err_msg;
        if(!isset($body['organisation_group_id']))
            return 'organisation_group_id not set';
        return;
    }

    private function is_valid_put_field_body($body) {
        if($err_msg = $this->is_valid_post_field_body($body))
            return $err_msg;
        if(!isset($body['field_id']))
            return 'field_id not set';

        return;
    }

    public static function get_org_location_link($org) {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'].'/config/location/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['organisation_type'].'/'.$org['organisation_name'];
    }

    public static function get_org_group_link($org)
    {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'] . '/config/organisation-group/' . $org['organisation_group'] . '/' . $org['organisation_name'];
    }

    public static function get_org_id_link($org)
    {
        array_walk_recursive($org, \RouteController::class . '::encode_items_url');
        return $_SERVER['SERVER_NAME'] . '/config/organisation/' . $org['organisation_id'];
    }
}
?>
