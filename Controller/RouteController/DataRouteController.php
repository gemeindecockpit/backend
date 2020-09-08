<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class DataRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function home($request, $response, $args) {
       $data_controller = new DataController();

       $json_array = $data_controller->get_organisation_and_field_ids($_SESSION['user_id']);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_field($request, $response, $args) {
       $field_controller = new FieldController();

       $json_array = $field_controller->get_all($_SESSION['user_id']);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

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

   public function get_organisation_data($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);

       $json_array = $org_controller->get_organisation_data($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

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




   public function post_org_full_link_date_full($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }
   public function post_org_full_link_field_name_date_full($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }



   public function put_org_full_link_date_full($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }
   public function put_org_full_link_field_name_date_full($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }


}

?>
