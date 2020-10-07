<?php

require_once("AbstractController.php");

/*
* To be renamed and refactored as a model
*/
class FieldController extends AbstractController {

    private $select_field_skeleton =
        'SELECT
            view_latest_field.field_id as field_id,
            view_latest_field.field_name as field_name,
            view_latest_field.reference_value as reference_value,
            view_latest_field.yellow_limit as yellow_limit,
            view_latest_field.red_limit as red_limit,
            view_latest_field.relational_flag as relational_flag,
            view_latest_field.valid_from as valid_from
        FROM view_latest_field';

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
        $stmt_string = 'SELECT * FROM view_fields_visible_for_user WHERE user_id = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $user_id);
        $query_result = $this->format_query_result($this->db_access->execute());
        return $query_result;
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
