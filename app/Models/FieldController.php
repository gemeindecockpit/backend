<?php

require_once("AbstractController.php");

/*
* To be renamed and refactored as a model
*/
class FieldController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    /**
    * Constructs an array that contains the config for all fields visible for $user_id
    * Also has links to the 'data' endpoints of these fields
    */
    public function get_all($user_id) {
        $query_result = $this->db_ops->get_config_all_fields($user_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'field');
        $field_ids = [];
        foreach ($query_result as $field) {
            $field_ids[] = $field['field_id'];
        }

        return $this->format_json($self_link, $query_result, 'fields', $field_ids);
    }

    /**
    * Getter function for all field_ids visible for $user_id in the current layer
    */
    public function get_field_ids($user_id, ...$args) {
        $query_result = $this->db_ops->get_field_ids($user_id, ...$args);
        $field_ids = [];
        while($row = $query_result->fetch_assoc()) {
            $field_ids[] = $row['field_id'];
        }
        return $field_ids;
    }

    /**
    * Gets the config for all fields associated with an organisation (specified by the full link)
    * @param $user_id
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
    * @return
    *   Returns the formatted JSON array with the fields and links to further resources
    */
    public function get_config_for_field_by_full_link($user_id, ...$args) {
        $query_result = $this->db_ops->get_config_for_field_by_full_link($user_id, ...$args);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('config', ...$args);
        $data_link = $this->get_link('data', ...$args);

        $next_entity_types = ['data'];
        $next_entities = array($data_link);

        return $this->format_json($self_link, $query_result, $next_entity_types, $next_entities);
    }

    /**
    * Gets the config for all fields associated with an organisation (specified by org_id)
    * @param $user_id
    * @param $org_id
    * @return
    *   Returns the formatted JSON array with the fields and links to further resources
    */
    public function get_config_for_fields_by_organisation_id($user_id, $org_id) {
        $query_result = $this->db_ops->get_config_for_fields_by_organisation_id($user_id, $org_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'field', $org_id);

        return $this->format_json($self_link, $query_result);
    }

    /**
    * Updates the field with specified by a field_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include field_id, field_name, max_value, yellow_value, red_value, relational_flag
    * @return
    *   Returns an error code or null;
    */
    public function put_field_config($user_id, ...$args) {
        if(!$this->db_ops->user_can_modify_field($user_id, $args[0])) {
            return 'Forbidden'; // TODO: implement fail case
        }
        $errno = $this->db_ops->insert_field_by_sid(...$args);
        return $errno;
    }

    /*
    * Inherited from AbstractController. $query_result contains either config for fields or data and builds the core of the JSON
    * After that the links have to be put together. The general case is a resource of the next layer (e.g. nuts1 regions)
    * that has to be added to the self link (/config/nuts0 -> /config/nuts0/nuts1)
    * 'data' and 'config' links are a special case, as they are formatted prior in the respective method
    * TODO: This is hardly readable. We need a better solution
    */
    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;

        for($i = 0; $i < sizeof($next_entity_types); $i++) {
            if($next_entity_types[$i] === 'data' || $next_entity_types[$i] === 'config') {
                $links[$next_entity_types[$i]] = $next_entities[$i];
            } else {
                foreach($next_entities[$i] as $entity) {
                    $links[$next_entity_types[$i]] = $self_link . '/' . $entity;
                }
            }
        }
        $json_array = array('fields'=>$query_result, 'links'=>$links);
        return $json_array;
    }
}

?>
