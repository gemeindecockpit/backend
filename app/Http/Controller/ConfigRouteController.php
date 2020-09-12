<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class ConfigRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

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
    public function get_organisation_config($request, $response, $args) {
       $org_controller = new OrganisationController();
       $args_indexed = assoc_array_to_indexed($args);
       $json_array = $org_controller->get_organisation_config($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
    }

    /**
    * Controller-function for all config/ calls regarding fields referenced by org->field_name
    * @param $request
    * @param $response
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
    * @return Response
    *    The response body is a JSON with all fields associated with the organisation
    *    and links to the data for the fields
    */
    public function get_org_full_link_field_name($request, $response, $args) {
       $fieldController = new FieldController();
       $args_indexed = assoc_array_to_indexed($args);

       $json_array = $fieldController->get_config_for_field_by_full_link($_SESSION['user_id'], ...$args_indexed);

       $response->getBody()->write(json_encode($json_array));
       return $response->withHeader('Content-type', 'application/json');
    }

    public function post_org($request, $response, $args) {
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


    /**
    * Updates the field.
    * @param $request
    * @param $response
    * @param $args
    * @return mixed
    */
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
