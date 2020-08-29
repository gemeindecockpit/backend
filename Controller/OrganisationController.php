<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');
class OrganisationController extends AbstractController {


  public function getAll($user_id){
    $db = new DatabaseOps();
    $result = $db->view_all_organisation_visible_for_user($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      $org_array[$row['organisation_id']] = $row['name'];
    }
    return $org_array;
  }

  public function getOne($user_id, ...$args){
    $id = $args[0];
    $db = new DatabaseOps();
    $result = $db->view_one_organisation_visible_for_user($user_id, $id);
    return $result->fetch_assoc();
  }

  public function get_org_by_name_for_user($user_id, $org_name, $org_type){
    $db = new DatabaseOps();
    $result = $db->view_one_organisation_visible_for_user_by_name($user_id, $org_name, $org_type);
    $fields_array = [];

    //return array_push($result->fetch_assoc(), $fields_array);

  }
  public function get_all_orgs_for_user_for_type($user_id, $type){
    $db = new DatabaseOps();
    $result = $db->view_all_organisation_visible_for_user($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      if($row['type'] == $type){
        $org_array[] = $row;
      }
    }
    return $org_array;
  }

  public function get_all_types_for_user($user_id){
    $db = new DatabaseOps();
    $result = $db->get_all_types_for_user($user_id);
    $org_array = Array();
    while($row = $result->fetch_assoc()){
      $org_array[] = $row['type'];
    }
    return $org_array;
  }

}
