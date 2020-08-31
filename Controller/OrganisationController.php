<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');
class OrganisationController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

  public function get_all($user_id){
    $query_result = $this->db_ops->get_all_organisations($user_id);
    $query_result = $this->format_query_result($query_result);
    return $query_result;
  }

  public function get_one($user_id, ...$args){
    $id = $args[0];
    $result = $this->db_ops->get_organisation_by_id($user_id, $org_id);
    return $result->fetch_assoc();
  }

  public function get_config_for_organisations_by_nuts0($user_id, $nuts0) {
      $query_result = $this->db_ops->get_organisations_by_nuts0($user_id, $nuts0);
      $query_result = $this->format_query_result($query_result);
      $links['self'] = $this->get_self_link('config', $nuts0);
      foreach($query_result as $org) {
          $links['organisations'][$org['name']] = $this->get_org_link($org);
      }
      $json_array = array('organisations'=>$query_result, 'links'=>$links);
      return $json_array;
  }

  public function get_config_for_organisations_by_nuts01($user_id, $nuts0, $nuts1) {
      $query_result = $this->db_ops->get_organisations_by_nuts01($user_id, $nuts0, $nuts1);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2) {
      $query_result = $this->db_ops->get_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_organisations_by_nuts0123($user_id, $nuts0, $nuts1, $nuts2, $nuts3) {
      $query_result = $this->db_ops->get_organisations_by_nuts0123($user_id, $nuts0, $nuts1, $nuts2, $nuts3);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type) {
      $query_result = $this->db_ops->get_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_organisations_by_nuts0123_type_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
      $query_result = $this->db_ops->get_organisations_by_nuts0123_type_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_field_by_name($user_id, $org_id, $field_name) {
      $query_result = $this->db_ops->get_config_for_field_by_name($user_id, $org_id, $field_name);
      $query_result = $this->format_query_result($query_result);
      return $query_result;
  }

  public function get_config_for_organisation_by_name($user_id, $entity, $org_type){
    $db = new DatabaseOps();
    $result = $db->get_organisation_by_name($user_id, $org_type, $entity);
    $fields_array = [];
    $ret = $result->fetch_assoc();
    $field_result = $this->get_configfields_for_org($user_id, $entity, $org_type);

    while($row = $field_result->fetch_assoc()){
      array_walk_recursive($row, [$this, 'encode_items']);
      array_push($fields_array, $row);
    }
    $ret['fields'] = $fields_array ;

    return $ret;

  }

  public function get_data_for_organisation_by_name($user_id, $entity, $org_type){
    $db = new DatabaseOps();
    $result = $db->get_organisation_by_name($user_id, $org_type, $entity);
    $fields_array = [];
    $ret = $result->fetch_assoc();
    $field_result = $db->get_datafields_by_organisation_name($user_id, $entity, $org_type);

    while($row = $field_result->fetch_assoc()){
      array_walk_recursive($row, [$this, 'encode_items']);
      array_push($fields_array, $row);
    }
    $ret['fields'] = $fields_array ;

    return $ret;

  }

  public function get_data_for_field($user_id, $entity, $org_type, $field_name){
    $db = new DatabaseOps();
    $field_result = $db->get_datafields_by_organisation_name($user_id, $entity, $org_type);
    while($row = $field_result->fetch_assoc()){
      if($row['field_name'] == $field_name){
        array_walk_recursive($row, [$this, 'encode_items']);
        return $row;
      }
    }
    return [];
  }

  public function get_config_for_field($user_id, $entity, $org_type, $field_name){
    $field_result = $this->get_configfields_for_org($user_id, $entity, $org_type);

    while($row = $field_result->fetch_assoc()){
      if($row['field_name'] == $field_name){
        array_walk_recursive($row, [$this, 'encode_items']);
        return $row;
      }
    }
    return [];
  }

  function encode_items(&$item, $key){
     $item = utf8_encode($item);
   }

  public function get_configfields_for_org($user_id, $entity, $org_type){

    $db = new DatabaseOps();
    //gettign id
    $result = $db->get_organisation_by_name($user_id, $org_type, $entity);

    $fields_array = [];
    $ret = $result->fetch_assoc();
    $orgid = $ret['organisation_id'];
    $result = $db->get_configfields_by_organisation_id($orgid);
    return $result;
  }

  public function get_all_organisations_by_type($user_id, $org_type){
    $db = new DatabaseOps();
    $result = $db->get_all_organisations($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      if($row['type'] == $org_type){
        $org_array[] = $row;
      }
    }
    return $org_array;
  }

  public function get_all_types($user_id){
    $db = new DatabaseOps();
    $result = $db->get_all_types_for_user($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      $org_array[] = $row['type'];
    }
    return $org_array;
  }

  private function get_org_link($org) {
      return htmlentities($_SERVER['SERVER_NAME'].'/config/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['type'].'/'.$org['name']);
  }

}
