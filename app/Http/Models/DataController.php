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
    * Gets all ids for fields and organisations the return an array with links in the form:
    * data/organisation/org_id
    * data/field/field_id
    * @param $user_id
    * @return
    */
    public function get_organisation_and_field_ids($user_id) {
        $org_controller = new OrganisationController();
        $organisations = $org_controller->get_org_by_location($user_id);
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


    /**
    * Gets all the data from an organisation (identified by the full link)
    * First a FieldController fetches all field_ids associated with the organisation,
    * then for each field_id the data is fetched from the DB
    * the $self_link is of the form data/{org_params}[?last=x]
    * @param $user_id
    * @param $last
    *   Either 'latest', 'num_of_days' or 'all'
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type and org_name
    * @return
    */
    public function get_data_by_org($user_id, $last, ...$args) {
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

    public function get_data_by_field_ids($field_ids, $last='latest') {
        $db_access = new DatabaseAccess();
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
        $db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            if($last !== 'latest' && $last !== 'all')
                $db_access->bind_param($param_string, $id, $last);
            else
                $db_access->bind_param($param_string, $id);
            $query_result = $db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $db_access->close();
        return $data;
    }

    public function get_data_by_field_ids_and_day($field_ids, $org_id, $day) {
        $db_access = new DatabaseAccess();
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .= ' AND date = ?';
        $db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $db_access->bind_param('is', $id, $day);
            $query_result = $db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $db_access->close();
        return $data;
    }

    public function get_data_by_field_ids_and_month($field_ids, $org_id, $month) {
        $db_access = new DatabaseAccess();
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .=
            ' AND date >= ?
            AND date < date_add(?, INTERVAL 1 MONTH)
            ORDER BY date DESC';
        $db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $db_access->bind_param('iss', $id, $month, $month);
            $query_result = $db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $db_access->close();
        return $data;
    }

    public function get_data_by_field_ids_and_year($field_ids, $org_id, $year) {
        $db_access = new DatabaseAccess();
        $stmt_string = $this->select_data_skeleton;
        $stmt_string .=
            ' AND date >= ?
            AND date < date_add(?, INTERVAL 1 YEAR)
            ORDER BY date DESC';
        $db_access->prepare($stmt_string);
        $data = [];
        foreach($field_ids as $id) {
            $db_access->bind_param('iss', $id, $year, $year);
            $query_result = $db_access->execute();
            while($row = $query_result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $db_access->close();
        return $data;
    }


    /**
    * Gets the data for a field (identified by org_params + field_name)
    * First a FieldController fetches the field_id, then the data is fetched from DB
    * The self link is of the form data/org_params/field_name[?last=x]
    * @param $user_id
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
    * @param $last
    *   Either 'latest', 'num_of_days' or 'all'
    * @return
    */
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


    /**
    * Gets the data for an organisation for a specific date(-range).
    * If $interval_type == 'year' all data for that year is fetched
    * If $interval_type == 'month' all data for that month is fetched
    * If $interval_type == 'day' all data for that day is fetched
    * @param $user_id
    * @param ...$args
    * @param $interval_type
    *   Either 'year', 'month' or 'day'
    * @return
    */
    public function get_data_org_full_link_date($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $interval_type, $year, $month = '01', $day = '01') {
        $field_controller = new FieldController();

        $args = [$nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name];

        $date = $year . '-' . $month . '-' . $day;
        $field_ids = $field_controller->get_field_ids($user_id, ...$args);
        $data = [];
        switch ($interval_type) {
            case 'year':
                $data = $this->get_data_field_ids_year($user_id, $field_ids, $date);
                $args[] = $year;
                break;
            case 'month':
                $data = $this->get_data_field_ids_month($user_id, $field_ids, $date);
                $args[] = $year;
                $args[] = $month;
                break;
            case 'day':
                $data = $this->get_data_field_ids_date($user_id, $field_ids, $date);
                $args[] = $year;
                $args[] = $month;
                $args[] = $day;
                break;
            default:
                return null;
                break;
        }

        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $data);
    }


    /**
    * Gets the data for a field for a specific date(-range).
    * If $interval_type == 'year' all data for that year is fetched
    * If $interval_type == 'month' all data for that month is fetched
    * If $interval_type == 'day' all data for that day is fetched
    * @param $user_id
    * @param ...$args
    * @param $interval_type
    *   Either 'year', 'month' or 'day'
    * @return
    */
    public function get_data_org_full_link_field_name_date($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name, $interval_type, $year, $month = '01', $day = '01') {
        $field_controller = new FieldController();
        $args = [$nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name];

        $date = $year . '-' . $month . '-' . $day;
        $field_ids = $field_controller->get_field_ids($user_id, ...$args);
        $field_id = -1;
        if(sizeof($field_ids) > 0) {
            $field_id = $field_ids[0];
        }
        $query_result;
        switch ($interval_type) {
            case 'year':
                $query_result = $this->db_ops->get_data_field_id_year($user_id, $field_id, $date);
                $args[] = $year;
                break;
            case 'month':
                $query_result = $this->db_ops->get_data_field_id_month($user_id, $field_id, $date);
                $args[] = $year;
                $args[] = $month;
                break;
            case 'day':
                $query_result = $this->db_ops->get_data_field_id_date($user_id, $field_id, $date);
                $args[] = $year;
                $args[] = $month;
                $args[] = $day;
                break;
            default:
                return null;
                break;
        }
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', ...$args);

        return $this->format_json($self_link, $query_result);
    }


    /**
    * Fetches the data for all ids in $field_ids for the year
    * @param
    * @return
    */
    private function get_data_field_ids_year($user_id, $field_ids, $date) {
        $data = [];
        foreach ($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_year($user_id, $field_id, $date);
            $query_result = $this->format_query_result($query_result);
            foreach($query_result as $entry) {
                $data[] = $entry;
            }
        }
        return $data;
    }


    /**
    * Fetches the data for all ids in $field_ids for the month
    * @param
    * @return
    */
    private function get_data_field_ids_month($user_id, $field_ids, $date) {
        $data = [];
        foreach ($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_month($user_id, $field_id, $date);
            $query_result = $this->format_query_result($query_result);
            foreach($query_result as $entry) {
                $data[] = $entry;
            }
        }
        return $data;
    }


    /**
    * Fetches the data for all ids in $field_ids for the day
    * @param
    * @return
    */
    private function get_data_field_ids_day($user_id, $field_ids, $date) {
        $data = [];
        foreach ($field_ids as $field_id) {
            $query_result = $this->db_ops->get_data_field_id_day($user_id, $field_id, $date);
            $query_result = $this->format_query_result($query_result);
            foreach($query_result as $entry) {
                $data[] = $entry;
            }
        }
        return $data;
    }

    public function insert_data($data) {
        $db_access = new DatabaseAccess();
        $stmt_string =
            "INSERT INTO
                field_values (field_id, user_id, field_value, date)
            VALUES
                (?,?,?,?)";
        $db_access->prepare($stmt_string);
        $errno = null;
        foreach($data as $insert) {
            $db_access->bind_param('iiis', $insert['field_id'], $insert['user_id'], $insert['field_value'], $insert['date']);
            $errno = $db_access->execute();
        }
        $db_access->close();
        return $errno;
    }

    //TODO: First mockup, not functional
    /**
    *
    * @param
    * @return
    */
    public function insert_multiple_values_for_date($user_id, $field_values, $date) {
        foreach ($field_values as $field_id => $field_value) {
            $this->db_ops->insert_value_for_date($user_id, $field_id, $field_value, $date);
        }
    }

    //TODO: First mockup, not functional
    /**
    *
    * @param
    * @return
    */
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
