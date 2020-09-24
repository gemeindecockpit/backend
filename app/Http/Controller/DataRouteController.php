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
       $data_controller = new DataController();

       $json_array = $data_controller->get_organisation_and_field_ids($_SESSION['user_id']);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_id_links($request, $response, $args) {
       $org_controller = new OrganisationController();
       $links['self'] = RouteController::get_link('data', 'organisation');
       $org_ids = $org_controller->get_org_ids($_SESSION['user_id']);
       foreach($org_ids as $id) {
           $links['organisations'][] = RouteController::get_link('data', 'organisation', $id);
       }
       $json_array = array('links' => $links);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_data_by_org($request, $response, $args) {
       $data_controller = new DataController();
       $org_controller = new OrganisationController();

       $field_ids = $org_controller->get_field_ids($_SESSION['user_id'], $args['org_id']);
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

   public function get_field_id_links($request, $response, $args) {
       $org_controller = new FieldController();
       $links['self'] = RouteController::get_link('data', 'field');
       $org_ids = $org_controller->get_field_ids($_SESSION['user_id']);
       foreach($org_ids as $id) {
           $links['organisations'][] = RouteController::get_link('data', 'field', $id);
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

       $field_ids = $org_controller->get_field_ids($_SESSION['user_id'], $args['org_id']);

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
       $response->getBody()->write(json_encode($data));
       return $response->withStatus(200);
   }
}

?>
