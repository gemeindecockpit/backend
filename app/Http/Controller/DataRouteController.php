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
           $response->getBody()->write('Acces denied');
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
       $args['URI'] = $_SERVER['REQUEST_URI'];
       $response->getBody()->write(json_encode($args));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_data_by_field_and_date($request, $response, $args) {
       $args['URI'] = $_SERVER['REQUEST_URI'];
       $response->getBody()->write(json_encode($args));
       return $response->withHeader('Content-type', 'application/json');
   }


   /**
   * Handles the GET /data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{org_type}/{org_name}/{field_name} endpoint
   * The response contains the data of the field
   * The timeframe is specified by '?last=x'
   * can be either num_of_days, 'all' or nothing
   * @param $request
   * @param $response
   * @param $args
   *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
   */
   public function get_org_full_link_field_name($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);

       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       $json_array = $data_controller->get_data_by_org_link_field_name($_SESSION['user_id'], $last, ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   /**
   * Handles the GET /data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{org_type}/{org_name}/{field_name}/{year}[/{month}[/{day}]] endpoints
   * The response contains the data of the field for the date
   * @param $request
   * @param $response
   * @param $args
   *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name, field_name and year
   *    Can include month and day
   */
   public function get_org_full_link_field_name_date($request, $response, $args) {
       $data_controller = new DataController();

       $args_indexed = [$args['nuts0'], $args['nuts1'], $args['nuts2'], $args['nuts3'], $args['org_type'], $args['org_name'], $args['field_name']];

       if(isset($args['day'])) {
           $args_indexed[] = 'day';
           $args_indexed[] = $args['year'];
           $args_indexed[] = $args['month'];
           $args_indexed[] = $args['day'];
       } else if (isset($args['month'])) {
           $args_indexed[] = 'month';
           $args_indexed[] = $args['year'];
           $args_indexed[] = $args['month'];
       } else {
           $args_indexed[] = 'year';
           $args_indexed[] = $args['year'];
       }
       $json_array = $data_controller->get_data_org_full_link_field_name_date($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }





   /**
   * Handles the GET /data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{org_type}/{org_name} endpoint
   * The response contains the data of the organisation
   * The timeframe is specified by '?last=x'
   * can be either num_of_days, 'all' or nothing
   * @param $request
   * @param $response
   * @param $args
   *    Must include nuts0, nuts1, nuts2, nuts3, org_type and org_name
   */
   public function get_org_full_link($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);

       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       $json_array = $data_controller->get_data_by_org($_SESSION['user_id'], $last, ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   /**
   * Handles the GET /data/{nuts0}/{nuts1}/{nuts2}/{nuts3}/{org_type}/{org_name}/{year}[/{month}[/{day}]] endpoints
   * The response contains the data of the organisation for the date
   * @param $request
   * @param $response
   * @param $args
   *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and year
   *    Can include month and day
   */
   public function get_org_full_link_date($request, $response, $args) {
       $data_controller = new DataController();

       $args_indexed = [$args['nuts0'], $args['nuts1'], $args['nuts2'], $args['nuts3'], $args['org_type'], $args['org_name']];

       if(isset($args['day'])) {
           $args_indexed[] = 'day';
           $args_indexed[] = $args['year'];
           $args_indexed[] = $args['month'];
           $args_indexed[] = $args['day'];
       } else if (isset($args['month'])) {
           $args_indexed[] = 'month';
           $args_indexed[] = $args['year'];
           $args_indexed[] = $args['month'];
       } else {
           $args_indexed[] = 'year';
           $args_indexed[] = $args['year'];
       }
       $json_array = $data_controller->get_data_org_full_link_date($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   public function post_org_data($request, $response, $args) {
       $args['URI'] = $_SERVER['REQUEST_URI'];
       $response->getBody()->write(json_encode($args));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function post_field_data($request, $response, $args) {
       $args['URI'] = $_SERVER['REQUEST_URI'];
       $response->getBody()->write(json_encode($args));
       return $response->withHeader('Content-type', 'application/json');
   }





}

?>
