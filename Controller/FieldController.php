<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');

class FieldController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($user_id) {
        $query_result = $this->db_ops->get_config_all_fields($user_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data', 'field');
        $field_ids = [];
        foreach ($query_result as $field) {
            $field_ids[] = $field['field_id'];
        }

        return $this->format_json($self_link, $query_result, 'fields', $field_ids);
    }

    public function get_field_ids($user_id, ...$args) {
        $query_result = $this->db_ops->get_field_ids($user_id, ...$args);
        return $this->format_query_result($query_result);
    }

    public function get_config_for_field_by_name($user_id, $org_id, $field_name) {
        $query_result = $this->db_ops->get_config_for_field_by_name($user_id, $org_id, $field_name);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data', 'field', $org_id, $field_name);

        return $this->format_json($self_link, $query_result);
    }

    public function function_get_config_for_field_by_full_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name) {}

    public function get_config_for_fields_by_organisation_id($user_id, $org_id) {
        $query_result = $this->db_ops->get_config_for_fields_by_organisation_id($user_id, $org_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data', 'field', $org_id);

        return $this->format_json($self_link, $query_result);
    }

    private function get_field_link($content_type, $args) {
        array_walk_recursive($args, [$this, 'encode_items_url']);
        return $_SERVER['SERVER_NAME'].$conten_type.'/'.implode('/',$args);
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;
        if ($next_entity_type !== '') {
            $links[$next_entity_type] = [];
            foreach ($next_entities as $value) {
                $links[$next_entity_type][] = $self_link . '/' . $value;
            }
        }

        $json_array = array('fields'=>$query_result, 'links'=>$links);
        return $json_array;
    }

    protected function format_config_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;

        foreach ($next_entities as $value) {
            $links[$next_entity_type][$value] = $self_link . '/' . $value;
        }
        $json_array = array('fields'=>$query_result, 'links'=>$links);
        return $json_array;
    }
}

?>
