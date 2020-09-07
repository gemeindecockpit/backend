<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class ConfigRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function get_organisation_config($request, $response, $args) {
       $orgController = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $response->getBody()->write(json_encode($orgController->get_organisation_config($_SESSION['user_id'], ...$args_indexed)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_field_name($request, $response, $args) {
       $orgController = new OrganisationController();
       $fieldController = new FieldController();
       $field_name = $args['field_name'];
       unset($args['field_name']);
       $args_indexed = assoc_array_to_indexed($args);
       $org = $orgController->get_config_for_organisations_by_nuts0123_type_name($_SESSION['user_id'], ...$args_indexed);
       $org_id = -1;
       if(isset($org['organisation_id'])) { // $org is already formatted as the json_array... (See "OrganisationController::format_json")
           $org_id = $org['organisation_id'];
       }
       $response->getBody()->write(json_encode($fieldController->get_config_for_field_by_name($_SESSION['user_id'], $org_id, $field_name)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function post_org_full_link($request, $response, $args) {
       $response->getBody()->write('post/'.implode('/', $args));
       return $response;
   }

   public function post_org_full_link_field_name($request, $response, $args) {
       $response->getBody()->write('post/'.implode('/', $args));
       return $response;
   }

   public function put_org_full_link($request, $response, $args) {
       $response->getBody()->write('put/'.implode('/', $args));
       return $response;
   }

   public function put_org_full_link_field_name($request, $response, $args) {
       $response->getBody()->write('put/'.implode('/', $args));
       return $response;
   }

   public function delete_org_full_link($request, $response, $args) {
       $response->getBody()->write('delete/'.implode('/', $args));
       return $response;
   }

   public function delete_org_full_link_field_name($request, $response, $args) {
       $response->getBody()->write('delete/'.implode('/', $args));
       return $response;
   }

}

?>
