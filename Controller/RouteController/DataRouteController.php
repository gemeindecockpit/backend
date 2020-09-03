<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class DataRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function home ($request, $response, $args) {
       $data_controller = new DataController();
       $json_array = $data_controller->get_organisation_and_field_ids($_SESSION['user_id']);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_field ($request, $response, $args) {
       $field_controller = new FieldController();
       $json_array = $field_controller->get_all($_SESSION['user_id']);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_field_field_id ($request, $response, $args) {
       $data_controller = new DataController();
       $query_parameters = $request->getQueryParams();
       $json_array;
       if(isset($query_parameters['last'])) {
           $json_array = $data_controller->get_data_from_past_x_days_by_field_id($_SESSION['user_id'], $args['field_id'], $query_parameters['last']);
       } else {
           $json_array = $data_controller->get_latest_data_by_field_id($_SESSION['user_id'], $args['field_id']);
       }

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_nuts_0 ($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $response->getBody()->write(json_encode($org_controller->get_data_for_organisations_by_nuts0($_SESSION['user_id'], ...$args_indexed)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_nuts_01 ($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $response->getBody()->write(json_encode($org_controller->get_data_for_organisations_by_nuts01($_SESSION['user_id'], ...$args_indexed)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_nuts_012 ($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $response->getBody()->write(json_encode($org_controller->get_data_for_organisations_by_nuts012($_SESSION['user_id'], ...$args_indexed)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_nuts_full ($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $org_controller->get_data_for_organisations_by_nuts0123($_SESSION['user_id'], ...$args_indexed);
       array_walk_recursive($json_array, function(&$value, $key){
         error_log(json_encode($value));
           $value = str_replace('/config/', '/data/', $value);
       });
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_nuts_full_org_type ($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array= $org_controller->get_data_for_organisations_by_nuts0123_type($_SESSION['user_id'], ...$args_indexed);
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link ($request, $response, $args) {
       $data_controller = new DataController();
       $query_parameters = $request->getQueryParams();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array;
       if(isset($query_parameters['last'])) {
           $args_indexed[] = $query_parameters['last'];
           $json_array = $data_controller->get_data_from_past_x_days_by_org_full_link($_SESSION['user_id'], ...$args_indexed);
       } else {
           $json_array = $data_controller->get_latest_data_by_org_full_link($_SESSION['user_id'], ...$args_indexed);
       }
       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_year ($request, $response, $args) {
       $data_controller = new DataController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $data_controller->get_data_org_full_link_year($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response;
   }

   public function get_org_full_link_year_month ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_org_full_link_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_org_full_link_field_name ($request, $response, $args) {
       $data_controller = new DataController();
       $query_parameters = $request->getQueryParams();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array;
       if(isset($query_parameters['last'])) {
           $args_indexed[] = $query_parameters['last'];
           if($query_parameters['last'] === 'all') {
               $json_array = $data_controller->get_all_data_by_org_full_link_field_name($_SESSION['user_id'], ...$args_indexed);
           } else {
               $json_array = $data_controller->get_data_from_past_x_days_by_org_full_link_field_name($_SESSION['user_id'], ...$args_indexed);
           }
       } else {
           $json_array = $data_controller->get_latest_data_by_org_full_link_field_name($_SESSION['user_id'], ...$args_indexed);
       }

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_field_name_year ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_org_full_link_field_name_year_month ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_org_full_link_field_name_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }




   public function post_org_full_link_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }
   public function post_org_full_link_field_name_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }



   public function put_org_full_link_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }
   public function put_org_full_link_field_name_date_full ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }


}

?>
