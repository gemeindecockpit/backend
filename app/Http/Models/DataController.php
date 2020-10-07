<?php

require_once("AbstractController.php");
require_once("OrganisationController.php");
require_once("FieldController.php");

/*
* To be renamed and refactored as a model
*/
class DataController extends AbstractController {

    private $select_data_skeleton =
        'SELECT
            field_id,
            field_name,
            field_value,
            realname,
            date
        FROM view_up_to_date_data_from_all_fields
        WHERE field_id = ?
    ';

    public function __construct() {
        parent::__construct();
    }


    /**
    * Gets all field names associated with the organisation, returns a JSON with links of the form:
    * data/organisation/org_id/field_name
    * @param $user_id
    * @param $org_id
    * @return
    */
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

    public function get_data_by_field_ids($field_ids, $last='latest') {
        $stmt_string = $this->select_data_skeleton;
        $param_string = 'i';
        if($last !== 'latest' && $last !== 'all') { // TODO: What happens if gibberish input?
            $stmt_string .= ' AND date >= (date_add(curdate(), INTERVAL -? DAY))';
            $param_string .= 'i';
        }
        $stmt_string .= ' ORDER BY date DESC';
        if($last == 'latest') {
            $stmt_string .= ' LIMIT 1';
        }
        $this->db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            if($last !== 'latest' && $last !== 'all')
                $this->db_access->bind_param($param_string, $id, $last);
            else
                $this->db_access->bind_param($param_string, $id);
            $query_result = $this->db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function get_data_by_field_ids_and_day($field_ids, $org_id, $day) {
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .= ' AND date = ?';
        $this->db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $this->db_access->bind_param('is', $id, $day);
            $query_result = $this->db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function get_data_by_field_ids_and_month($field_ids, $org_id, $month) {
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .=
            ' AND date >= ?
            AND date < date_add(?, INTERVAL 1 MONTH)
            ORDER BY date DESC';
        $this->db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $this->db_access->bind_param('iss', $id, $month, $month);
            $query_result = $this->db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function get_data_by_field_ids_and_year($field_ids, $org_id, $year) {
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .=
            ' AND date >= ?
            AND date < date_add(?, INTERVAL 1 YEAR)
            ORDER BY date DESC';
        $this->db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $this->db_access->bind_param('iss', $id, $year, $year);
            $query_result = $this->db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }


    public function insert_data($data) {
        $stmt_string =
            "INSERT INTO
                field_values (field_id, user_id, field_value, date)
            VALUES
                (?,?,?,?)";
        $this->db_access->prepare($stmt_string);
        $errno = null;
        foreach($data as $insert) {
            $this->db_access->bind_param('iiis', $insert['field_id'], $insert['user_id'], $insert['field_value'], $insert['date']);
            $errno = $this->db_access->execute();
        }
        return $errno;
    }
}

?>
