<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class DataRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }


   /**
   * Handles the GET /data endpoint
   * The response is a JSON of all field_ids and org_ids
   * visible for the logged in user
   * @param $request
   * @param $response
   * @param $args
   *    Is Empty
   */
   public function home($request, $response, $args) {
       $org_controller = new OrganisationController();
       $field_controller = new FieldController();

       $orgs = $org_controller->get_orgs_visble_for_user($_SESSION['user_id']);
       $org_groups = $org_controller->get_org_groups($_SESSION['user_id']);
       $fields = $field_controller->get_fields_visible_for_user($_SESSION['user_id']);

       $links['self'] = RouteController::get_link('data');
       $links['config'] = RouteController::get_link('config');

       foreach($orgs as $org) {
           $org_link = RouteController::get_link('data', 'organisation', $org['organisation_id']);
           $links['organisations'][] = array(
               'organisation_id' => $org['organisation_id'],
               'organisation_name' => $org['organisation_name'],
               'href' => $org_link
           );
       }
       foreach($org_groups as $group) {
           $org_group_link = RouteController::get_link('data', 'organisation-group', $group['organisation_group_name']);
           $links['organisation_groups'][] = array(
               'organisation_group_id' => $group['organisation_group_id'],
               'organisation_group_name' => $group['organisation_group_name'],
               'href' => $org_group_link
           );
       }
       foreach($fields as $field) {
           $field_link = RouteController::get_link('data', 'field', $field['field_id']);
           $links['fields'][] = array(
               'field_id' => $field['field_id'],
               'field_name' => $field['field_name'],
               'href' => $field_link
           );
       }
       $json_array['links'] = $links;
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_id_links($request, $response, $args) {
       $org_controller = new OrganisationController();

       $orgs = $org_controller->get_orgs_visble_for_user($_SESSION['user_id']);

       $links['self'] = RouteController::get_link('data', 'organisation');
       $links['config'] = RouteController::get_link('config', 'organisation');
       foreach($orgs as $org) {
           $org_link = RouteController::get_link('data', 'organisation', $org['organisation_id']);
           $links['organisations'][] = array(
               'organisation_id' => $org['organisation_id'],
               'organisation_name' => $org['organisation_name'],
               'href' => $org_link
           );
       }

       $json_array = array('links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_data_by_org($request, $response, $args) {
       $data_controller = new DataController();
       $org_controller = new OrganisationController();

       $fields = $org_controller->get_fields($_SESSION['user_id'], $args['org_id']);
       $field_ids = [];
       foreach($fields as $field) {
           $field_ids[] = $field['field_id'];
       }
       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       $data = $data_controller->get_data_by_field_ids($field_ids, $last);

       $links['self'] = RouteController::get_link('data', 'organisation', $args['org_id']);
       $links['config'] = RouteController::get_link('config', 'organisation', $args['org_id']);

       $json_array = array('data' => $data, 'links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   public function get_org_group_links($request, $response, $args) {
       $org_controller = new OrganisationController();

       $org_groups = $org_controller->get_org_groups($_SESSION['user_id']);

       $links['self'] = RouteController::get_link('data', 'organisation-group');
       $links['config'] = RouteController::get_link('config', 'organisation-group');

       foreach($org_groups as $group) {
           $org_group_link = RouteController::get_link('data', 'organisation-group', $group['organisation_group_name']);
           $links['organisation_groups'][] = array(
               'organisation_group_id' => $group['organisation_group_id'],
               'organisation_group_name' => $group['organisation_group_name'],
               'href' => $org_group_link
           );
       }

       $json_array = array('links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   public function get_data_by_group($request, $response, $args) {
       $user_controller = new UserController();
       $data_controller = new DataController();
       $org_controller = new OrganisationController();

       $orgs = $org_controller->get_org_by_group($args['org_group']);
       $orgs_in_group = [];

       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       foreach($orgs as $org) {
           if($user_controller->can_see_organisation($_SESSION['user_id'], $org['organisation_id'])) {
               $fields = $org_controller->get_fields($_SESSION['user_id'], $org['organisation_id']);
               $field_ids = [];
               foreach($fields as $field) {
                   $field_ids[] = $field['field_id'];
               }
               $data_by_org['organisation_id'] = $org['organisation_id'];
               $data_by_org['organisation_name'] = $org['organisation_name'];
               $data_by_org['data'] = $data_controller->get_data_by_field_ids($field_ids, $last);
               $orgs_in_group[] = $data_by_org;
           }
       }
       $links['self'] = RouteController::get_link('data', 'organisation-group', $args['org_group']);
       $links['config'] = RouteController::get_link('data', 'organisation-group', $args['org_group']);

       $json_array = array('organisations' => $orgs_in_group, 'links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }



   public function get_field_id_links($request, $response, $args) {
       $field_controller = new FieldController();

       $fields = $field_controller->get_fields_visible_for_user($_SESSION['user_id']);

       $links['self'] = RouteController::get_link('data', 'field');
       $links['config'] = RouteController::get_link('config', 'field');
       foreach($fields as $field) {
           $field_link = RouteController::get_link('data', 'field', $field['field_id']);
           $links['fields'][] = array(
               'field_id' => $field['field_id'],
               'field_name' => $field['field_name'],
               'href' => $field_link
           );
       }


       $json_array = array('links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_data_by_field($request, $response, $args) {
       $data_controller = new DataController();
       $user_controller = new UserController();

       if(!$user_controller->can_see_field($_SESSION['user_id'], $args['field_id'])) {
           $response->getBody()->write('Access denied');
           return $response->withHeader(403);
       }

       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       $data = $data_controller->get_data_by_field_ids([$args['field_id']], $last);

       $links['self'] = RouteController::get_link('data', 'field', $args['field_id']);
       $links['config'] = RouteController::get_link('config', 'field', $args['field_id']);

       $json_array = array('data' => $data, 'links' => $links);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   public function get_data_by_org_and_date($request, $response, $args) {
       $data_controller = new DataController();
       $org_controller = new OrganisationController();

       $fields = $org_controller->get_fields($_SESSION['user_id'], $args['org_id']);
       $field_ids = [];
       foreach($fields as $field) {
           $field_ids[] = $field['field_id'];
       }

       $data = [];
       if(isset($args['day'])) {
           $day = $args['year'] . '-' . $args['month'] . '-' . $args['day'];
           $data = $data_controller->get_data_by_field_ids_and_day($field_ids, $args['org_id'], $day);
       } else if (isset($args['month'])) {
           $month = $args['year'] . '-' . $args['month'] . '-01';
           $data = $data_controller->get_data_by_field_ids_and_month($field_ids, $args['org_id'], $month);
       } else {
           $year = $args['year'] . '-01-01';
           $data = $data_controller->get_data_by_field_ids_and_year($field_ids, $args['org_id'], $year);
       }

       $args_indexed = RouteController::assoc_array_to_indexed($args);
       $links['self'] = RouteController::get_link('data', 'organisation', ...$args_indexed);
       $links['config'] = RouteController::get_link('config', 'organisation', $args['org_id']);

       $json_array = array('data' => $data, 'links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_data_by_field_and_date($request, $response, $args) {
       $data_controller = new DataController();
       $user_controller = new UserController();

       if(!$user_controller->can_see_field($_SESSION['user_id'], $args['field_id'])) {
           $response->getBody()->write('Access denied');
           return $response->withHeader(403);
       }
       $field_ids = [$args['field_id']];
       $data = [];
       if(isset($args['day'])) {
           $day = $args['year'] . '-' . $args['month'] . '-' . $args['day'];
           $data = $data_controller->get_data_by_field_ids_and_day($field_ids, $args['field_id'], $day);
       } else if (isset($args['month'])) {
           $month = $args['year'] . '-' . $args['month'] . '-01';
           $data = $data_controller->get_data_by_field_ids_and_month($field_ids, $args['field_id'], $month);

       } else {
           $year = $args['year'] . '-01-01';
           $data = $data_controller->get_data_by_field_ids_and_year($field_ids, $args['field_id'], $year);
       }
       $args_indexed = RouteController::assoc_array_to_indexed($args);
       $links['self'] = RouteController::get_link('data', 'field', ...$args_indexed);
       $links['config'] = RouteController::get_link('config', 'field', $args['field_id']);

       $json_array = array('data' => $data, 'links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   public function post_org_data($request, $response, $args) {
       $data_controller = new DataController();
       $org_controller = new OrganisationController();
       $user_controller = new UserController();

       error_log($request->getBody());
       $body = json_decode($request->getBody(),true);

       $data = [];
       foreach($body as $entry) {
           if(!isset($entry['field_id'])) {
               $response->getBody()->write('field_id required for all entries');
               return $response->withStatus(500);
           }
           if(!isset($entry['field_value'])) {
               $response->getBody()->write('No value given');
               return $response->withStatus(500);
           }
           if(!isset($entry['date'])) {
               $response->getBody()->write('No date specified');
               return $response->withStatus(500);
           }
           if(!$user_controller->can_insert_into_field($_SESSION['user_id'], $entry['field_id'])) {
               $response->getBody()->write('Access denied');
               return $response->withStatus(403);
           }
           $data[] = array(
               'field_id' => $entry['field_id'],
               'user_id' => $_SESSION['user_id'],
               'field_value' => $entry['field_value'],
               'date' => $entry['date']);
       }

       $errno = $data_controller->insert_data($data);


       if($errno) {
           $response->getBody()->write($errno);
           return $response->withStatus(500);
       }
       return $response->withStatus(200);

   }

   public function post_field_data($request, $response, $args) {
       $data_controller = new DataController();
       $user_controller = new UserController();

       if(!$user_controller->can_insert_into_field($_SESSION['user_id'], $args['field_id'])) {
           $response->getBody()->write('Access denied');
           return $response->withStatus(403);
       }

       $body = json_decode($request->getBody(),true);

       if(!isset($body['field_value'])) {
           $response->getBody()->write('No value given');
           return $response->withStatus(500);
       }
       if(!isset($body['date'])) {
           $response->getBody()->write('No date specified');
           return $response->withStatus(500);
       }

       $data[] = array(
           'field_id' => $args['field_id'],
           'user_id' => $_SESSION['user_id'],
           'field_value' => $body['field_value'],
           'date' => $body['date']);

       $errno = $data_controller->insert_data($data);

       if($errno) {
           $response->getBody()->write($errno);
           return $response->withStatus(500);
       }
       return $response->withStatus(200);
   }
}

?>
