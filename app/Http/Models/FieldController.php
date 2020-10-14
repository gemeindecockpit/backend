<?php

require_once("AbstractController.php");

/*
* To be renamed and refactored as a model
*/
class FieldController extends AbstractController {

    private $select_field_skeleton =
        'SELECT
            DISTINCT view_fields_visible_for_user.field_id as field_id,
            view_fields_visible_for_user.field_name as field_name,
            view_fields_visible_for_user.reference_value as reference_value,
            view_fields_visible_for_user.yellow_limit as yellow_limit,
            view_fields_visible_for_user.red_limit as red_limit,
            view_fields_visible_for_user.relational_flag as relational_flag,
            view_fields_visible_for_user.valid_from as valid_from,
            view_fields_visible_for_user.valid_to as valid_to
        FROM view_fields_visible_for_user
        JOIN view_organisations_and_fields
            ON view_fields_visible_for_user.field_id = view_organisations_and_fields.field_id
        WHERE user_id = ?';

    public function __construct() {
        parent::__construct();
    }

    /**
    * Constructs an array that contains the config for all fields visible for $user_id
    */
    public function get_all() {
        $this->db_access->prepare($this->select_field_skeleton);
        $query_result = $this->format_query_result($this->db_access->execute());
        return $query_result;
    }

    public function get_field_config($endpoint, $user_id, $args, $date = null) {
        switch ($endpoint) {
            case 'field':
                $stmt_array = $this->get_field_stmt($args, $date);
                break;
            case 'organisation':
                $stmt_array = $this->get_organisation_stmt($args, $date);
                break;
            case 'organisation_type':
                $stmt_array = $this->get_type_stmt($args, $date);
                break;
            case 'organisation_group':
                $stmt_array = $this->get_group_stmt($args, $date);
                break;
            case 'location':
                $stmt_array = $this->get_location_stmt($args, $date);
                break;
            default:
                if($date == null) {
                    $stmt_array['stmt_string'] = $this->select_field_skeleton . ' AND ISNULL(valid_to)';
                    $stmt_array['param_string'] = 'i';
                } else {
                    $stmt_array['stmt_string'] = $this->select_field_skeleton . ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
                    $stmt_array['param_string'] = 'iss';
                }
                break;
        }

        $stmt_string = $stmt_array['stmt_string'];
        $param_string = $stmt_array['param_string'];

        $args_indexed = assoc_array_to_indexed($args);

        if($date != null) {
            $args_indexed[] = $date;
            $args_indexed[] = $date;
        }

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, $user_id, ...$args_indexed);

        return $this->format_query_result($this->db_access->execute());
    }

    private function get_field_stmt($args, $date) {
        $stmt_string = $this->select_field_skeleton;
        $param_string = 'i';

        if(isset($args['field_id'])) {
            $stmt_string .= ' AND view_fields_visible_for_user.field_id = ?';
            $param_string .= 'i';
        }

        if($date == null) {
            $stmt_string .= ' AND ISNULL(valid_to)';
        } else {
            $stmt_string .= ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
            $param_string .= 'ss';
        }

        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_organisation_stmt($args, $date) {
        $stmt_string = $this->select_field_skeleton;
        $param_string = 'i';

        if(isset($args['org_id'])) {
            $stmt_string .= ' AND organisation_id = ?';
            $param_string .= 'i';
        }
        if(isset($args['field_name'])) {
            $stmt_string .= ' AND view_fields_visible_for_user.field_name = ?';
            $param_string .= 's';
        }

        if($date == null) {
            $stmt_string .= ' AND ISNULL(valid_to)';
        } else {
            $stmt_string .= ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
            $param_string .= 'ss';
        }

        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_group_stmt($args, $date) {
        $stmt_string = $this->select_field_skeleton;
        $param_string = 'i';


        if(isset($args['org_group'])) {
            $stmt_string .= ' AND organisation_group = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        if(isset($args['field_name'])) {
            $stmt_string .= ' AND view_fields_visible_for_user.field_name = ?';
            $param_string .= 's';
        }

        if($date == null) {
            $stmt_string .= ' AND ISNULL(valid_to)';
        } else {
            $stmt_string .= ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
            $param_string .= 'ss';
        }

        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_type_stmt($args, $date) {
        $stmt_string = $this->select_field_skeleton;
        $param_string = 'i';

        if(isset($args['org_type'])) {
            $stmt_string .= ' AND organisation_type = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        if(isset($args['field_name'])) {
            $stmt_string .= ' AND view_fields_visible_for_user.field_name = ?';
            $param_string .= 's';
        }

        if($date == null) {
            $stmt_string .= ' AND ISNULL(valid_to)';
        } else {
            $stmt_string .= ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
            $param_string .= 'ss';
        }

        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_location_stmt($args, $date) {
        $stmt_string = $this->select_field_skeleton;
        $param_string = 'i';

        if(isset($args['nuts0'])) {
            $stmt_string .= ' AND nuts0 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts1'])) {
            $stmt_string .= ' AND nuts1 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts2'])) {
            $stmt_string .= ' AND nuts2 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts3'])) {
            $stmt_string .= ' AND nuts3 = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        if(isset($args['field_name'])) {
            $stmt_string .= ' AND view_fields_visible_for_user.field_name = ?';
            $param_string .= 's';
        }

        if($date == null) {
            $stmt_string .= ' AND ISNULL(valid_to)';
        } else {
            $stmt_string .= ' AND valid_from <= ? and (valid_to > ? OR ISNULL(valid_to))';
            $param_string .= 'ss';
        }

        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    public function get_field_by_id($field_id) {
        $stmt = $this->select_field_skeleton;
        $stmt .= ' WHERE field_id = ?';
        return AbstractController::execute_stmt($stmt, 'i', $field_id);
    }

    public function get_field_by_id_and_date($field_id, $date = null) {
        if ($date == null)
            $date = date('Y-m-d');
        $this->db_access->prepare(
            'SELECT *
                FROM view_field
                WHERE valid_from <= ?
                AND (valid_to >= ?
                    OR ISNULL(valid_to))
                AND field_id=?'
        );
        $this->db_access->bind_param('ssi', $date, $date, $field_id);
        $query_result = $this->format_query_result($this->db_access->execute());
        return $query_result;
    }

    public function get_field_by_name($org_id, $field_name, $date) {
        $this->db_access->prepare(
            'SELECT *
            FROM view_field_for_date
            WHERE valid_from <= ?
                AND (valid_to >= ?
                    OR ISNULL(valid_to))
                AND field_name=?
                AND organisation_id=?'
        );
        $this->db_access->bind_param('sssi', $date, $date, $field_name, $org_id);
        $query_result = $this->format_query_result($this->db_access->execute());
        if(sizeof($query_result) == 1) {
            return $query_result[0];
        } else {
            return false;
        }
    }

    public function get_fields_visible_for_user($user_id) {
        $stmt_string = $this->select_field_skeleton;
        $stmt_string .=
            ' JOIN can_see_field
            ON
                view_latest_field.field_id = can_see_field.field_id
            WHERE user_id = ?
        ';

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $user_id);

        return $this->format_query_result($this->db_access->execute());
    }

    /**
    * Updates the field with specified by a field_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include field_id, field_name, max_value, yellow_value, red_value, relational_flag
    * @return
    *   Returns an error code or null;
    */
    public function put_field_config($field) {
        $stmt_string =
            'UPDATE field
			SET
                valid_to = CURRENT_TIMESTAMP
			WHERE field_sid = ?
            AND valid_to IS NULL
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $field['field_id']);
        $errno = $this->db_access->execute();

        if ($errno) {
            return $errno;
        }

        $stmt_string =
            'INSERT INTO
                field (field_sid,name,reference_value,yellow_limit,red_limit,relational_flag)
			VALUES (?,?,?,?,?,?)
        ';

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param(
            'isiiii',
            $field['field_id'],
            $field['field_name'],
            $field['reference_value'],
            $field['yellow_limit'],
            $field['red_limit'],
            $field['relational_flag']
        );
        $errno = $this->db_access->execute();
        return $errno;
    }

    public function insert_field($field) {
        $stmt_string =
            'INSERT INTO
                field (field_sid, name, reference_value, yellow_limit, red_limit, relational_flag)
            VALUES
                (?, ?, ?, ?, ?, ?)';
        $sid = $this->get_max_sid() + 1;
        $this->db_access->prepare($stmt_string);
        if(!isset($field['reference_value']))
            $field['reference_value'] = null;
        $this->db_access->bind_param(
            'isiiii',
            $sid,
            $field['field_name'],
            $field['reference_value'],
            $field['yellow_limit'],
            $field['red_limit'],
            $field['relational_flag']
        );
        $errno = $this->db_access->execute();
        return $sid;
    }


    public function get_max_sid(){
        $this->db_access->prepare('SELECT max(field_sid) FROM field');
        $result = $this->db_access->execute();
        $max_sid=$result->fetch_array()[0];
        return $max_sid;
    }


    public function delete_field($field_id){
        $stmt_string =
            'UPDATE field
            SET
                valid_to=CURDATE()
            WHERE field_sid=?
            AND valid_to IS NULL
        ';

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param("i",$field_id);
        $errno = $this->db_access->execute();
        if($errno) {
            return $errno;
        }

        $stmt_string = 'DELETE FROM organisation_has_field WHERE field_id = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param("i",$field_id);
        $errno = $this->db_access->execute();
        return $errno;
    }

}

?>
