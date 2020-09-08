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
        $org_controller = new OrganisationController();
        $organisations = $org_controller->get_organisation_config($user_id)['organisations'];
        $org_ids = [];
        foreach ($organisations AS $org) {
            $org_ids[] = 'organisation/' . $org['organisation_id'];
        }

        $query_result = $this->db_ops->get_field_ids($user_id);
        $query_result = $this->format_query_result($query_result);
        $field_ids = [];
        foreach ($query_result AS $row) {
            $field_ids[] = 'field/' . $row['field_id'];
        }

        $self_link = $this->get_link('data');
        $config_link = $this->get_link('config');

        $json_array = $this->format_json($self_link, null, array('config', 'organisation_id', 'field_id'), array($config_link, $org_ids, $field_ids));

        return $json_array;
    }

    public function get_fields_by_organisation_id($user_id, $org_id) {
        $field_controller = new FieldController();
        $fields = $field_controller->get_config_for_fields_by_organisation_id($user_id, $org_id);

        $self_link = $this->get_link('data/organisation', $org_id);
        $links['self'] = $self_link;
        $links['field_name'] = [];
        foreach ($fields['fields'] as  $field) {
            $links['field_name'][] = $self_link . '/' . $field['field_name'];
        }

        $json_array = array('links' => $links);
        return $json_array;
    }

    public function get_data_by_org($user_id, $last, ...$args) { // args = nuts0123,org_type,org_name,field_name
        $field_controller = new FieldController();
        $data = [];
        $field_ids = $field_controller->get_field_ids($user_id, ...$args);
        foreach ($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_by_field_id($user_id, $field_id, $last);
            $query_result = $this->format_query_result($query_result);
            foreach ($query_result as $datum) {
                $data[] = $datum;
            }
        }

        $self_link = $this->get_link('data', ...$args);
        if($last !== 'latest') {
            $self_link .= '?last=' . $last;
        }
        $config_link = $this->get_link('config', ...$args);

        return $this->format_json($self_link, $data, array('config'), array($config_link));
    }

    public function get_data_by_field_id($user_id, $field_id, $last='latest') {
        $query_result = $this->db_ops->get_data_by_field_id($user_id, $field_id, $last);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'field', $field_id);
        if($last !== 'latest') {
            $self_link .= '?last=' . $last;
        }
        $config_link = $this->get_link('config', 'field', $field_id);

        return $this->format_json($self_link, $query_result, array('config'), array($config_link));
    }

    public function get_data_by_org_link_field_name($user_id, $last, ...$args) {
        $field_controller = new FieldController();
        $field_ids = $field_controller->get_field_ids($user_id, ...$args);

        $field_id = -1;
        if(sizeof($field_ids) > 0) {
            $field_id = $field_ids[0];
        }

        $query_result = $this->db_ops->get_data_by_field_id($user_id, $field_id, $last);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', ...$args);
        if($last !== 'latest') {
            $self_link .= '?last=' . $last;
        }
        $config_link = $this->get_link('config', ...$args);

        return $this->format_json($self_link, $query_result, array('config'), array($config_link));
    }

    public function get_latest_data_by_org_id_field_name($user_id, $organisation_id, $field_name) {
        $query_result = $this->db_ops->get_latest_data_by_field_name($user_id, $organisation_id, $field_name);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'organisation', $organisation_id, $field_name);

        return $this->format_json($self_link, $query_result);
    }

    public function get_latest_data_by_org_full_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
        $args = func_get_args();
        $query_result_field_ids = $this->db_ops->get_field_ids(...$args);
        $data = [];
        while ($row = $query_result_field_ids->fetch_assoc()) {
            $query_result = $this->db_ops->get_latest_data_by_field_id($user_id, $row['field_id']);
            $data_array = $query_result->fetch_assoc();
            array_walk_recursive($data_array, [$this, 'encode_items_url']);
            $data[] = $data_array;
        }

        unset($args[0]);
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_latest_data_by_org_full_link_field_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name) {
        $args = func_get_args();
        $query_result_field_id = $this->db_ops->get_field_ids(...$args);
        $data = [];
        if($row = $query_result_field_id->fetch_assoc()) {
            $query_result = $this->db_ops->get_latest_data_by_field_id($user_id, $row['field_id']);
            if($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data = $entry;
            }
        }

        unset($args[0]);
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_from_past_x_days_by_field_name($user_id, $organisation_id, $field_name, $lastX) {
        $query_result = $this->db_ops->get_data_from_past_x_days_by_field_name($user_id, $organisation_id, $field_name, $lastX);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'organisation', $organisation_id, $field_name) . '?last=' . $lastX;

        return $this->format_json($self_link, $query_result);
    }

    public function get_data_from_past_x_days_by_org_full_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $lastX) {
        $args = func_get_args();
        $query_result = $this->db_ops->get_data_from_past_x_days_by_org_full_link(...$args);
        if($query_result->num_rows == 0) {
            return false;
        }
        $query_result = $this->format_query_result($query_result);
        unset($args[sizeof($args) - 1]);
        unset($args[0]);
        $self_link = $this->get_link('data', ...$args) . '?last=' . $lastX;

        return $this->format_json($self_link, $query_result);
    }

    public function get_data_from_past_x_days_by_org_full_link_field_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name, $lastX) {
        $field_controller = new FieldController();
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        $query_result_field_id = $field_controller->get_field_ids(...$args);
        $data = [];
        foreach($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_from_past_x_days_by_field_id($user_id, $field_id, $lastX);
            while($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_year($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $year) {
        $field_controller = new FieldController();
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        $field_ids = $field_controller->get_field_ids(...$args);
        $data = [];
        $date = $year . '-01-01';
        foreach($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_year($user_id, $field_id, $date);
            while($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_month($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $year, $month) {
        $field_controller = new FieldController();
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);
        $field_ids = $field_controller->get_field_ids(...$args);
        $data = [];
        $date = $year . '-' . $month . '-01';
        foreach($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_month($user_id, $field_id, $date);
            while($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $args[] = $month;
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_date_full($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $year, $month, $day) {
        $field_controller = new FieldController();
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);

        $field_ids = $field_controller->get_field_ids(...$args);
        $data = [];
        $date = $year . '-' . $month . '-' . $day;
        foreach($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_date($user_id, $field_id, $date);
            if($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $args[] = $month;
        $args[] = $day;
        $self_link = $this->get_link('data', ...$args);


        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_field_name_year($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name, $year) {
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        $query_result_field_id = $this->db_ops->get_field_ids(...$args);

        $data = [];
        $date = $year . '-01-01';
        if($row = $query_result_field_id->fetch_assoc()) {
            $query_result = $this->db_ops->get_data_field_id_year($user_id, $row['field_id'], $date);
            while($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_field_name_month($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name, $year, $month) {
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);
        $query_result_field_id = $this->db_ops->get_field_ids(...$args);

        $data = [];
        $date = $year . '-' . $month .'-01';
        if($row = $query_result_field_id->fetch_assoc()) {
            $query_result = $this->db_ops->get_data_field_id_month($user_id, $row['field_id'], $date);
            while($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data[] = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $args[] = $month;
        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }

    public function get_data_org_full_link_field_name_date($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name, $year, $month, $day) {
        $args = func_get_args();
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);
        unset($args[sizeof($args) - 1]);
        $query_result_field_id = $this->db_ops->get_field_ids(...$args);

        $data = [];
        $date = $year . '-' . $month .'-' . $day;
        if($row = $query_result_field_id->fetch_assoc()) {
            $query_result = $this->db_ops->get_data_field_id_date($user_id, $row['field_id'], $date);
            if($entry = $query_result->fetch_assoc()) {
                array_walk_recursive($entry, [$this, 'encode_items_url']);
                $data = $entry;
            }
        }

        unset($args[0]);
        $args[] = $year;
        $args[] = $month;
        $args[] = $day;
        $self_link = $this->get_link('data', ...$args);


        return $this->format_json($self_link, $data);
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

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;
        for($i = 0; $i < sizeof($next_entity_types); $i++) {
            if($next_entity_types[$i] === 'config') {
                $links['config'] = $next_entities[$i];
            } else {
                $links[$next_entity_types[$i]] = [];
                foreach ($next_entities[$i] as $entity) {
                    $links[$next_entity_types[$i]][] = $self_link . '/' . $entity;
                }
            }

        }

        if($query_result != null) {
            $json_array = array("data"=>$query_result, "links"=>$links);
        } else {
            $json_array = array("links"=>$links);
        }

        return $json_array;
    }
}

?>
