<?php

require_once('class-requestHandler.php');

class GetRequestHandler extends RequestHandler
{

  public function handleConfigRequest() {

    $result_organisation = $this->data_operator->selectFromOrganisation($this->path_vars);
    $resource = array();

    if(sizeof($this->path_vars) == 5) {
        $organisation_id = $result_organisation->fetch_assoc()['organisation_id'];
        $result_field = $this->data_operator->getFieldFromName($organisation_id,$this->path_vars[4]);
        $resource = $result_field->fetch_assoc();
        $resource['links']['self']['href'] = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $resource['links']['data']['href'] = $_SERVER['HTTP_HOST'] . str_replace('config','data',$_SERVER['PHP_SELF']);
    } elseif(sizeof($this->path_vars) == 4) {
        $resource = $result_organisation->fetch_assoc();
        $resource['links']['self']['href'] = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

        $fields = $this->data_operator->getFieldsFromOrganisation($resource['organisation_id']);
        while($field = $fields->fetch_assoc()) {
            $resource['links'][$field['field_name']]['href'] = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $field['field_name'];
        }
    } else {
        $resource['links']['self']['href'] = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $result_organisation = $result_organisation->fetch_all();
        foreach($result_organisation as $value) {
            $resource['links'][$value[0]]['href'] = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $value[0];
        }
    }
    return $resource;
  }

  public function handleDataRequest() {


  }

  public function handleUserRequest() {

  }

}


?>
