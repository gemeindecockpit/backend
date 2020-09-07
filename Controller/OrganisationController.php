<?php

require_once("AbstractController.php");
require_once("NutsController.php");
require_once(__DIR__ . '/../app/db.php');
class OrganisationController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_organisation_config($user_id, ...$args) {
        return $this->get_organisation_json($user_id, 'config', ...$args);
    }

    public function get_organisation_data($user_id, ...$args) {
        return $this->get_organisation_json($user_id, 'data', ...$args);
    }

    private function get_organisation_json($user_id, $request_type, ...$args) {
        $query_result = $this->db_ops->get_organisation_config($user_id, ...$args);
        if($query_result === null) {
            return array("error");
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link($request_type, ...$args);

        $next_entity_types = ['organisations'];
        $organisation_links = $this->get_org_links($request_type, $query_result);
        $next_entity_array = array($organisation_links);
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
                $next_entity_types = ['fields'];
                $next_entity_array = [];
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

    private function get_org_links($endpoint_type, $orgs) {
      $organisation_links = [];
      foreach ($orgs as $org) {
          array_walk_recursive($org, [$this, 'encode_items_url']);
          $organisation_links[] = $_SERVER['SERVER_NAME'].'/'.$endpoint_type.'/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name'];
      }

      return $organisation_links;
    }

    private function get_org_data_link($org) {
      array_walk_recursive($org, [$this, 'encode_items_url']);
      return $_SERVER['SERVER_NAME'].'/data/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name'];
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
      $links['self'] = $self_link;

      $json_array;
      if($next_entity_types[0] == 'fields') {
          $json_array = $query_result[0];
          $links['data'] = str_replace('config','data',$self_link);
      } else {
          $json_array = array('organisations' => $query_result);
      }

      for($i = 0; $i < sizeof($next_entity_types); $i++) {
          if($next_entity_types[$i] === 'organisations') {
              $links['organisations'] = $next_entities[$i];
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

}
