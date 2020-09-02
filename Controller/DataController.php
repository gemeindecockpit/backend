<?php

require_once("AbstractController.php");
require_once("OrganisationController.php");
require_once("FieldController.php");
require_once(__DIR__ . '/../app/db.php');

class DataController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_organisation_and_field_ids($user_id) {
        $orgController = new OrganisationController();
        $organisations = $orgController->get_all($user_id)['organisations'];
        $org_ids = [];
        foreach ($organisations AS $org) {
            $org_ids[] = $org['organisation_id'];
        }

        $query_result = $this->db_ops->get_all_fields($user_id);
        $query_result = $this->format_query_result($query_result);
        $field_ids = [];
        foreach ($query_result AS $row) {
            $field_ids[] = $row['field_id'];
        }

        $self_link = $this->get_self_link('data');
        $links['self'] = $self_link;
        $links['organisation_id'] = [];
        $links['field_id'] = [];
        foreach ($org_ids as $id) {
            $links['organisation_id'][] = $self_link . '/organisation/' . $id;
        }
        foreach ($field_ids as $id) {
            $links['field_id'][] = $self_link . '/field/' . $id;
        }
        $json_array = array('links' => $links);

        return $json_array;
    }

    public function get_fields_by_organisation_id($user_id, $org_id) {
        $field_controller = new FieldController();
        $fields = $field_controller->get_config_for_fields_by_organisation_id($user_id, $org_id);

        $self_link = $this->get_self_link('data/organisation', $org_id);
        $links['self'] = $self_link;
        $links['field_name'] = [];
        foreach ($fields['fields'] as  $field) {
            $links['field_name'][] = $self_link . '/' . $field['field_name'];
        }

        $json_array = array('links' => $links);
        return $json_array;
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

    public function get_latest_data_by_full_organisation_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
        $args = func_get_args();
        $query_result = $this->db_ops->get_latest_data_by_full_organisation_link(...$args);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        unset($args[0]);
        $self_link = $this->get_self_link('data/', ...$args);

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
