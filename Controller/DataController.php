<?php

require_once("AbstractController.php");
require_once(__DIR__ . '/../app/db.php');

class DataController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_latest_data_by_field_name($user_id, $organisation_id, $field_name) {
        $query_result = $this->db_ops->get_latest_data_by_field_name($user_id, $organisation_id, $field_name);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/organisation', $organisation_id, $field_name);

        return $this->format_json($self_link, $query_result);
    }

    public function get_latest_data_by_field_id($user_id, $field_id) {
        $query_result = $this->db_ops->get_latest_data_by_field_id($user_id, $field_id);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/field', $field_id);

        return $this->format_json($self_link, $query_result);
    }

    public function get_data_from_past_x_days_by_field_id($user_id, $field_id, $lastX) {
        $query_result = $this->db_ops->get_data_from_past_x_days_by_field_id($user_id, $field_id, $lastX);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/field_id', $field_id) . '?last=' . $lastX;

        return $this->format_json($self_link, $query_result);
    }

    public function get_data_from_past_x_days_by_field_name($user_id, $organisation_id, $field_name, $lastX) {
        $query_result = $this->db_ops->get_data_from_past_x_days_by_field_name($user_id, $organisation_id, $field_name, $lastX);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_self_link('data/organisation', $organisation_id, $field_name) . '?last=' . $lastX;

        return $this->format_json($self_link, $query_result);
    }

    public function insert_multiple_values_for_date($user_id, $field_values, $date) {
        foreach ($field_values as $field_id => $field_value) {
            $this->db_ops->insert_value_for_date($user_id, $field_id, $field_value, $date);
        }
    }

    public function insert_multiple_values_for_date_by_field_name($user_id, $organisation_id, $field_values, $date) {
        foreach ($field_values as $field_name => $field_value) {
            $this->db_ops->insert_value_for_date_by_field_name($user_id, $organisation_id, $field_name, $field_value, $date);
        }
    }

    protected function format_json($self_link, $query_result, $next_entity_type = '', $next_entities = []) {
        $links['self'] = $self_link;
        $json_array = array("data"=>$query_result, "links"=>$links);
        return $json_array;
    }
}

?>
