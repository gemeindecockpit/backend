<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');
class OrganisationController extends AbstractController {


  public function getAll($user_id){
    $db = new DatabaseOps();
    $result = $db->get_all_organisations($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      $org_array[$row['organisation_id']] = $row['name'];
    }
    return $org_array;
  }

  public function getOne($user_id, ...$args){
    $id = $args[0];
    $db = new DatabaseOps();
    $result = $db->get_organisation_by_id($user_id, $org_id);
    return $result->fetch_assoc();
  }

  public function get_organisation_by_name($user_id, $entity, $org_type){
    $db = new DatabaseOps();
    $result = $db->get_organisation_by_name($user_id, $org_type, $entity);
    $fields_array = [];
    $ret = $result->fetch_assoc();
    $field_result = $this->get_fields_for_org($user_id, $entity, $org_type);

    while($row = $field_result->fetch_assoc()){
      array_walk_recursive($row, [$this, 'encode_items']);
      array_push($fields_array, $row);
    }
    $ret['fields'] = $fields_array ;

    return $ret;

  }

  function encode_items(&$item, $key){
     $item = utf8_encode($item);
   }

  public function get_fields_for_org($user_id, $entity, $org_type){
    //gettign id
    $db = new DatabaseOps();
    $result = $db->get_organisation_by_name($user_id, $org_type, $entity);
    $fields_array = [];
    $ret = $result->fetch_assoc();
    $orgid = $ret['organisation_id'];
    $result = $db->get_fields_by_organisation_id($orgid);
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

}
