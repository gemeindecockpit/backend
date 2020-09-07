<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class ConfigRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function get_organisation_config($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $response->getBody()->write(json_encode($org_controller->get_organisation_config($_SESSION['user_id'], ...$args_indexed)));
       return $response->withHeader('Content-type', 'application/json');
   }

   public function get_org_full_link_field_name($request, $response, $args) {
       $org_controller = new OrganisationController();
       $fieldController = new FieldController();
       $field_name = $args['field_name'];
       unset($args['field_name']);
       $args_indexed = assoc_array_to_indexed($args);
       $org = $org_controller->get_organisation_config($_SESSION['user_id'], ...$args_indexed);
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

   /**
    * Updates the organisation.
    * @param $request
    * @param $response
    * @param $args
    * @return mixed
    */
  public function put_org_full_link($request, $response, $args) {
      $org_controller = new OrganisationController();

      $args_indexed = assoc_array_to_indexed($args);

      $org_id_array =$org_controller->get_org_ids($_SESSION['user_id'], ...$args_indexed);

      if(sizeof($org_id_array) == 0) {
          return $response->withStatus(404);
      } else {
          $org_id = $org_id_array[0];
      }

      $entity = json_decode($request->getBody(), true);
      if (!isset($entity['organisation_id'])
          || !isset($entity['name'])
          || !isset($entity['type'])
          || !isset($entity['description'])
          || !isset($entity['contact'])
          || !isset($entity['zipcode'])
          || !isset($entity['active'])) {
          $response->getBody()->write("key in organisation json is missing");
          return $response->withStatus(500);
      }

      if($org_id != $entity['organisation_id']) {
          $response->getBody()->write('Organisation ID does not match the organisation in the link');
          return $response->withStatus(500);
      }

      $errno = $org_controller->put_org_config($_SESSION['user_id'],
          $entity['organisation_id'],
          $entity['name'],
          $entity['description'],
          $entity['type'],
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

   public function put_org_full_link_field_name($request, $response, $args) {
       $field_controller = new FieldController();
       $args_indexed = assoc_array_to_indexed($args);
       $field_id_array = $field_controller->get_field_ids($_SESSION['user_id'], ...$args_indexed);

       if(sizeof($field_id_array) == 0) {
           return $response->withStatus(404);
       } else {
           $field_id = $field_id_array[0];
       }

       $field = json_decode($request->getBody(), true);

       if (!isset($field['field_id'])
            || !isset($field['field_name'])
            || !isset($field['max_value'])
            || !isset($field['yellow_value'])
            || !isset($field['red_value'])
            || !isset($field['relational_flag'])) {
            $response->getBody()->write("key in field json is missing");
            return $response->withStatus(500);
       }

       if($field_id != $field['field_id']) {
           $response->getBody()->write('Field ID does not match the field in the link');
           return $response->withStatus(500);
       }

       $errno = $field_controller->put_field_config($_SESSION['user_id'],
            $field['field_id'],
            $field['field_name'],
            $field['max_value'],
            $field['yellow_value'],
            $field['red_value'],
            $field['relational_flag']
       );

       if ($errno) {
           $response->getBody()->write($errno);
           return $response->withStatus(500);
       } else {
           return $response->withStatus(200);
       }
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
