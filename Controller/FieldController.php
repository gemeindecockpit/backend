<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');

class FieldController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_config_for_field_by_name($user_id, $org_id, $field_name) {
        $query_result = $this->db_ops->get_config_for_field_by_name($user_id, $org_id, $field_name);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/field', $org_id, $field_name);

        return $this->format_json($self_link, $query_result);
    }

    public function get_config_for_fields_by_organisation_id($user_id, $org_id) {
        $query_result = $this->db_ops->get_config_for_fields_by_organisation_id($user_id, $org_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/field', $org_id);

        return $this->format_json($self_link, $query_result);
    }

    private function get_field_link($field) {
        return htmlspecialchars($_SERVER['SERVER_NAME'].'/config/field/' . $field['field_id']);
    }

    protected function format_json($self_link, $query_result, $next_entity_type = '', $next_entities = []) {
        $links['self'] = $self_link;
        foreach($query_result as $field) {
            $links['fields'][$field['field_id']] = $this->get_field_link($field);
        }
        foreach ($next_entities as $value) {
            $links[$next_entity_type][$value] = $self_link . '/' . $value;
        }
        $json_array = array('fields'=>$query_result, 'links'=>$links);
        return $json_array;
    }
}

?>
