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

   public function get_org_full_link_year($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);

       $json_array = $data_controller->get_data_org_full_link_year($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response;
   }

   public function get_org_full_link_year_month($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $data_controller->get_data_org_full_link_month($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response;
   }

   public function get_org_full_link_date_full($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);

       $json_array = $data_controller->get_data_org_full_link_date_full($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response;
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

   public function get_org_full_link_field_name_year($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $data_controller->get_data_org_full_link_field_name_year($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_field_name_year_month($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $data_controller->get_data_org_full_link_field_name_month($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_field_name_date_full($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);
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
