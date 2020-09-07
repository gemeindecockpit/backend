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
        $field_ids = [];
        while($row = $query_result->fetch_assoc()) {
            $field_ids[] = $row['field_id'];
        }
        return $field_ids;
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

    public function put_field_config($user_id, ...$args) {
        if(!$this->db_ops->user_can_modify_field($user_id, $args[0])) {
            return 'Forbidden'; // TODO: implement fail case
        }
        $errno = $this->db_ops->insert_field_by_sid(...$args);
        return $errno;
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;
        for($i = 0; $i < sizeof($next_entity_types); $i++) {
            $links[$next_entity_types[$i]] = [];
            foreach ($next_entities[$i] as $entity) {
                $links[$next_entity_types[$i]][] = $self_link . '/' . $entity;
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
