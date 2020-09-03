<?php

require_once("AbstractController.php");
require_once("NutsController.php");
require_once(__DIR__ . '/../app/db.php');
class OrganisationController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

  public function get_all($user_id){
    $query_result = $this->db_ops->get_all_organisations($user_id);
    $query_result = $this->format_query_result($query_result);

    $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id);
    $next_nuts = [];
    while($row = $next_nuts_query_result->fetch_array()) {
        array_walk_recursive($row, [$this, 'encode_items']);
        $next_nuts[] = $row[0];
    }

    $self_link = $this->get_self_link('config');
    return $this->format_json($self_link, $query_result, 'nuts0', $next_nuts);
  }

  public function get_all_data($user_id){
    $query_result = $this->db_ops->get_all_organisations($user_id);
    $query_result = $this->format_query_result($query_result);

    $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id);
    $next_nuts = [];
    while($row = $next_nuts_query_result->fetch_array()) {
        array_walk_recursive($row, [$this, 'encode_items']);
        $next_nuts[] = $row[0];
    }

    $self_link = $this->get_self_link('data');
    return $this->format_data_json($self_link, $query_result, 'nuts0', $next_nuts);
  }

  public function get_one($user_id, ...$args){
  }

  public function get_config_for_organisations_by_nuts0($user_id, $nuts0) {
      $query_result = $this->db_ops->get_organisations_by_nuts0($user_id, $nuts0);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('config', $nuts0);
      return $this->format_json($self_link, $query_result, 'nuts1', $next_nuts);
  }
//TODO: repepetive code
  public function get_data_for_organisations_by_nuts0($user_id, $nuts0) {
      $query_result = $this->db_ops->get_organisations_by_nuts0($user_id, $nuts0);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('data', $nuts0);
      return $this->format_data_json($self_link, $query_result, 'nuts1', $next_nuts);
  }

  public function get_config_for_organisations_by_nuts01($user_id, $nuts0, $nuts1) {
      $query_result = $this->db_ops->get_organisations_by_nuts01($user_id, $nuts0, $nuts1);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0, $nuts1);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('config', $nuts0, $nuts1);
      return $this->format_json($self_link, $query_result, 'nuts2', $next_nuts);
  }
//TODO: maybe combine this function with the one above by outsourcing the last part with the 'config'
  public function get_data_for_organisations_by_nuts01($user_id, $nuts0, $nuts1) {
      $query_result = $this->db_ops->get_organisations_by_nuts01($user_id, $nuts0, $nuts1);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0, $nuts1);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('data', $nuts0, $nuts1);
      return $this->format_data_json($self_link, $query_result, 'nuts2', $next_nuts);
  }

  public function get_config_for_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2) {
      $query_result = $this->db_ops->get_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0, $nuts1, $nuts2);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('config', $nuts0, $nuts1, $nuts2);
      return $this->format_json($self_link, $query_result, 'nuts3', $next_nuts);
  }

  public function get_data_for_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2) {
      $query_result = $this->db_ops->get_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2);
      $query_result = $this->format_query_result($query_result);

      $next_nuts_query_result = $this->db_ops->get_next_NUTS_codes($user_id, $nuts0, $nuts1, $nuts2);
      $next_nuts = [];
      while($row = $next_nuts_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_nuts[] = $row[0];
      }

      $self_link = $this->get_self_link('data', $nuts0, $nuts1, $nuts2);
      return $this->format_data_json($self_link, $query_result, 'nuts3', $next_nuts);
  }

  public function get_config_for_organisations_by_nuts0123($user_id, $nuts0, $nuts1, $nuts2, $nuts3) {
      $args = func_get_args();
      $query_result = $this->db_ops->get_organisations_by_nuts0123(...$args);
      $query_result = $this->format_query_result($query_result);

      $next_entities_query_result = $this->db_ops->get_all_types(...$args);
      $next_entities = [];
      while($row = $next_entities_query_result->fetch_array()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_entities[] = $row[0];
      }

      unset($args[0]);
      $self_link = $this->get_self_link('config', ...$args);

      return $this->format_json($self_link, $query_result, 'orgatype', $next_entities);
  }

  public function get_data_for_organisations_by_nuts0123($user_id, $nuts0, $nuts1, $nuts2, $nuts3) {
      $args = func_get_args();
      $query_result = $this->db_ops->get_organisations_by_nuts0123(...$args);
      $query_result = $this->format_query_result($query_result);

      $next_entities_query_result = $this->db_ops->get_all_types(...$args);
      $next_entities = [];
      while($row = $next_entities_query_result->fetch_array()) {

          array_walk_recursive($row, [$this, 'encode_items']);
          $next_entities[] = $row[0];
      }

      unset($args[0]);
      $self_link = $this->get_self_link('data', ...$args);
      return $this->format_json($self_link, $query_result, 'orgatype', $next_entities);
  }

  public function get_config_for_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type) {
      $args = func_get_args();
      $query_result = $this->db_ops->get_organisations_by_nuts0123_type(...$args);
      $query_result = $this->format_query_result($query_result);

      $next_entities = [];
      foreach ($query_result as $row) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_entities[] = $row['name'];
      }

      unset($args[0]);
      $self_link = $this->get_self_link('config', ...$args);

      return $this->format_json($self_link, $query_result, 'organisations', $next_entities);
  }

  public function get_data_for_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type) {
      $json_array = $this->get_config_for_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type);
      array_walk_recursive($json_array, function(&$value, $key){
          $value = str_replace('config', 'data', $value);
      });
      return $json_array;
  }

  public function get_config_for_organisations_by_nuts0123_type_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
      $args = func_get_args();
      $query_result = $this->db_ops->get_organisations_by_nuts0123_type_name(...$args);
      $query_result = $this->format_query_result($query_result);

      $next_entities_query_result = $this->db_ops->get_all_fields_from_organisation_by_id($user_id, $query_result[0]['organisation_id']);
      $next_entities = [];
      while($row = $next_entities_query_result->fetch_assoc()) {
          array_walk_recursive($row, [$this, 'encode_items']);
          $next_entities[] = $row['field_name'];
      }

      unset($args[0]);
      $self_link = $this->get_self_link('config', ...$args);

      return $this->format_json($self_link, $query_result, 'fields', $next_entities);
  }

  private function get_org_link($org) {
      array_walk_recursive($org, [$this, 'encode_items_url']);
      return $_SERVER['SERVER_NAME'].'/config/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name'];
  }

  private function get_org_data_link($org) {
      array_walk_recursive($org, [$this, 'encode_items_url']);
      return $_SERVER['SERVER_NAME'].'/data/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name'];
  }

  protected function format_data_json($self_link, $query_result, $next_entity_type = '', $next_entities = []) {
      $links['self'] = $self_link;

      $json_array;
      if($next_entity_type == 'fields') {
          $json_array = $query_result[0];
          $links['data'] = str_replace('config','data',$self_link);
      } else {
          $json_array = array('organisations' => $query_result);
          $links['organisations'] = [];
          foreach($query_result as $org) {
              $links['organisations'][] = $this->get_org_data_link($org);
          }
      }
      if($next_entities !== []) {
          array_walk_recursive($next_entities, [$this, 'encode_items_url']);
          $links[$next_entity_type] = [];
          foreach ($next_entities as $value) {
              $links[$next_entity_type][] = $self_link . '/' . $value;
          }
      }

      $json_array['links'] = $links;
      return $json_array;
  }

  protected function format_json($self_link, $query_result, $next_entity_type = '', $next_entities = []) {
      $links['self'] = $self_link;

      $json_array;
      if($next_entity_type == 'fields') {
          $json_array = $query_result[0];
          $links['data'] = str_replace('config','data',$self_link);
      } else {
          $json_array = array('organisations' => $query_result);
          $links['organisations'] = [];
          foreach($query_result as $org) {
              $links['organisations'][] = $this->get_org_link($org);
          }
      }
      if($next_entities !== []) {
          array_walk_recursive($next_entities, [$this, 'encode_items_url']);
          $links[$next_entity_type] = [];
          foreach ($next_entities as $value) {
              $links[$next_entity_type][] = $self_link . '/' . $value;
          }
      }

      $json_array['links'] = $links;
      return $json_array;
  }

}
