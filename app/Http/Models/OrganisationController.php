<?php

require_once("AbstractController.php");
require_once("NutsController.php");

/*
* To be renamed and refactored as a model
*/
class OrganisationController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    /**
    * Delegates the call to the get_organisation_json method with the $request_type 'config'
    * @param $user_id
    * @param $args
    *    Can include nuts0, nuts1, nuts2, nuts3, org_type, org_name
    * @return
    *   Returns the formatted JSON array with the organisations and links to further resources
    */
    public function get_organisation_config($user_id, ...$args) {
        return $this->get_organisation_json($user_id, 'config', ...$args);
    }
    
    /**
    * Delegates the call to the get_organisation_json method with the $request_type 'data'
    * @param $user_id
    * @param $args
    *    Can include nuts0, nuts1, nuts2, nuts3, org_type, org_name
    * @return
    *   Returns the formatted JSON array with the organisations and links to further resources
    */
    public function get_organisation_data($user_id, ...$args) {
        return $this->get_organisation_json($user_id, 'data', ...$args);
    }

    /**
    * Gets the config for all organisations in the specific layer (nuts0, nuts1, nuts2, nuts3, org_type, org_name)
    * Gets the resources of the next layer (e.g. nuts2 regions if the layer is nuts1)
    * Constructs the self link and the link to either the 'data' or 'config' resource
    * finally calls format_json to put all resources and links into the json_array
    * @param $user_id
    * @param $request_type
    *   Either 'config' or 'data'
    * @param $args
    *    Can include nuts0, nuts1, nuts2, nuts3, org_type, org_name
    * @return
    *   Returns the formatted JSON array with the organisations and links to further resources
    */
    private function get_organisation_json($user_id, $request_type, ...$args) {
        $query_result = $this->db_ops->get_organisation_config($user_id, ...$args);
        if($query_result === null) {
            return array("error");
        }
        $query_result = $this->format_query_result($query_result);

        $next_entity_types = [];
        $next_entity_array = [];

        $self_link = $this->get_link($request_type, ...$args);
        if($request_type === 'data') {
            $next_entity_types[] = 'config';
            $next_entity_array[] = $this->get_link('config', ...$args);
        } else {
            $next_entity_types[] = 'data';
            $next_entity_array[] = $this->get_link('data', ...$args);
        }

        $next_entity_types[] = 'organisations';
        $organisation_links = $this->get_org_links($request_type, $query_result);
        $next_entity_array[] = $organisation_links;


        $next_entities_query_result = null;
        switch (sizeof($args)) {
            case 0:
            case 1:
            case 2:
            case 3:
                $next_nuts = 'nuts' . sizeof($args);
                $next_entity_types[] = $next_nuts;
                $next_entities_query_result = $this->db_ops->get_next_NUTS_codes($user_id, ...$args);
                break;
            case 4:
                $next_entity_types[] = 'organisation_types';
                $next_entities_query_result = $this->db_ops->get_all_types($user_id, ...$args);
                break;
            case 5:
                // The next entities are the organisations, which is already covered by "$organisation_links";
                // but this is still here so it doesn't go to the default case
                break;
            case 6:
                // There are no "next organisations", so the links have to be reset
                $next_entity_types = array($next_entity_types[0], 'fields');
                $next_entity_array = array($next_entity_array[0]);
                $next_entities_query_result = $this->db_ops->get_field_names($user_id, ...$args);
                break;
            default: // TODO: implement fail case
                return null;
                break;
        }

        $next_entities = [];
        while(!is_null($next_entities_query_result) && $row = $next_entities_query_result->fetch_array()) {
            array_walk_recursive($row, [$this, 'encode_items']);
            $next_entities[] = $row[0];
        }
        $next_entity_array[] = $next_entities;

        return $this->format_json($self_link, $query_result, $next_entity_types, $next_entity_array);
    }

    /**
    * Updates the organisation with org_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include org_id, org_name, org_type, description, contact, zipcode and active-flag
    * @return
    *   Returns an error code or null;
    */
    public function put_org_config($user_id, ...$args) {
        if(!$this->db_ops->user_can_modify_organisation($user_id, $args[0])) {
            return 'Forbidden'; // TODO: implement fail case
        }
        $errno = $this->db_ops->update_organisation_by_id(...$args);
        return $errno;
    }

    /**
    * Getter function for all organisation_ids that a user can see in the current layer
    * @param $user_id
    * @param $args
    *    Can include nuts0, nuts1, nuts2, nuts3, org_type, org_name
    * @return
    *   An array with all org_ids
    */
    public function get_org_ids($user_id, ...$args) {
        $query_result = $this->db_ops->get_org_ids($user_id, ...$args);
        $org_ids = [];
        while ($row = $query_result->fetch_assoc()) {
            $org_ids[] = $row['organisation_id'];
        }
        return $org_ids;
    }

    /**
    * Constructs links to organisations
    * @param $endpoint_type
    *   Either 'config' or 'data'
    * @param $orgs
    *   Array that contains org-arrays. These arrays must contain the keys 'nuts0', 'nuts1', 'nuts2', 'nuts3', 'type', 'name'
    * @return
    *   An array with the links
    */
    private function get_org_links($endpoint_type, $orgs) {
      $organisation_links = [];
      foreach ($orgs as $org) {
          array_walk_recursive($org, [$this, 'encode_items_url']);
          $organisation_links[] = $_SERVER['SERVER_NAME'].'/'.$endpoint_type.'/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name'];
      }

      return $organisation_links;
    }

    /*
    * Inherited Method from AbstractController
    * If the layer is full_nuts/org_type/org_name there is only one organisation, therefore we don't need an organisation-array
    * in the JSON. Since $query_result is always an array with the organisations,
    * we need to get the first (and only) entry in the array which will be the core body of the JSON
    * In all other cases there might be multiple organisations (e.g. all "Feuerwehren" from "Braunschweig")
    * and the JSON will be an array of organisations
    * After that the links have to be put together. The general case is a resource of the next layer (e.g. nuts1 regions)
    * that has to be added to the self link (/config/nuts0 -> /config/nuts0/nuts1)
    * 'data', 'config' and 'organisations' links are a special case, as they are formatted prior in get_organisation_json
    * TODO: This is hardly readable. We need a better solution
    */
    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
      $json_array;
      if($next_entity_types[1] === 'fields') {
          $json_array = $query_result[0];
      } else {
          $json_array = array('organisations' => $query_result);
      }

      $links['self'] = $self_link;
      for($i = 0; $i < sizeof($next_entity_types); $i++) {
          if($next_entity_types[$i] === 'data' || $next_entity_types[$i] === 'config' || $next_entity_types[$i] === 'organisations') {
              $links[$next_entity_types[$i]] = $next_entities[$i];
          } else {
              $links[$next_entity_types[$i]] = [];
              foreach ($next_entities[$i] as $entity) {
                  $links[$next_entity_types[$i]][] = $self_link . '/' . $entity;
              }
          }
      }
      $json_array['links'] = $links;
      return $json_array;
    }

    public function insert_organisation($organisation) {
        $db_ops = new DatabaseOps();
        $db_connection = $db_ops->get_db_connection();
        $stmt = $db_connection->prepare('INSERT INTO organisation(name, type, description, contact, zipcode) VALUES(?, ?, ?, ?, ?)');
        $errno = $db_ops->execute_stmt_without_result();
        $db_connection->close();
        return $errno;
    }

}
