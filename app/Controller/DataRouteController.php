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


   /**
   * Handles the GET /data/field endpoint.
   * The response is a list of all field_ids
   * visible for the logged in user
   * @param $request
   * @param $response
   * @param $args
   *    Is Empty
   */
   public function get_field($request, $response, $args) {
       $field_controller = new FieldController();

       $json_array = $field_controller->get_all($_SESSION['user_id']);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   /**
   * Handles the GET /data/field/{field_id} endpoint
   * The response contains the data of the field
   * The timeframe is specified by '?last=x'
   * can be either num_of_days, 'all' or nothing
   * @param $request
   * @param $response
   * @param $args
   *    Contains 'field_id'
   */
   public function get_field_field_id($request, $response, $args) {
       $data_controller = new DataController();

       $query_parameters = $request->getQueryParams();
       if(isset($query_parameters['last'])) {
           $last = $query_parameters['last'];
       } else {
           $last = 'latest';
       }

       $json_array = $data_controller->get_data_by_field_id($_SESSION['user_id'], $args['field_id'], $last);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }


   /**
   * Handles the GET /data[/{nuts0}[/{nuts1}[/{nuts2}[/{nuts3}[/{org_type}]]]]] endpoints
   * The response contains an array with all organisations and links to the next layer
   * @param $request
   * @param $response
   * @param $args
   *    Can include nuts0, nuts1, nuts2, nuts3, org_type
   */
   public function get_organisation_data($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);

       $json_array = $org_controller->get_organisation_data($_SESSION['user_id'], ...$args_indexed);

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



   // TODO
   public function post_org_full_link_date_full($request, $response, $args) {
       $response->getBody()->write('post/'.implode('/', $args));
       return $response;
   }

   // TODO
   public function post_org_full_link_field_name_date_full($request, $response, $args) {
       $response->getBody()->write('post/'.implode('/', $args));
       return $response;
   }


   // TODO
   public function put_org_full_link_date_full($request, $response, $args) {
       $response->getBody()->write('put/'.implode('/', $args));
       return $response;
   }

   // TODO
   public function put_org_full_link_field_name_date_full($request, $response, $args) {
       $response->getBody()->write('put/'.implode('/', $args));
       return $response;
   }


}

?>
