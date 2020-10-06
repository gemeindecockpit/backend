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

    public function get_field_by_name($org_id, $field_name) {
        $stmt_string = $this->select_field_skeleton;
        $stmt_string .=
            ' JOIN view_organisations_and_fields
                ON view_latest_field.field_id = view_organisations_and_fields.field_id
            WHERE organisation_id = ?
            AND view_latest_field.field_name = ?
            ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('is', $org_id, $field_name);
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
    * Gets the config for all fields associated with an organisation (specified by the full link)
    * @param $user_id
    * @param $args
    *    Must include nuts0, nuts1, nuts2, nuts3, org_type, org_name and field_name
    * @return
    *   Returns the formatted JSON array with the fields and links to further resources
    */
    public function get_config_for_field_by_full_link($user_id, ...$args) {
        $stmt_string = $this->select_field_skeleton;
    }

    /**
    * Gets the config for all fields associated with an organisation (specified by org_id)
    * @param $user_id
    * @param $org_id
    * @return
    *   Returns the formatted JSON array with the fields and links to further resources
    */
    public function get_config_for_fields_by_organisation_id($user_id, $org_id) {
        $query_result = $this->db_ops->get_config_for_fields_by_organisation_id($user_id, $org_id);
        $query_result = $this->format_query_result($query_result);

        $self_link = $this->get_link('data', 'field', $org_id);

        return $this->format_json($self_link, $query_result);
    }

    /**
    * Updates the field with specified by a field_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include field_id, field_name, max_value, yellow_value, red_value, relational_flag
    * @return
    *   Returns an error code or null;
    */
    public function put_field_config(...$args) {
        $errno = $this->db_ops->insert_field_by_sid(...$args);
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
        $db_ops = new DatabaseOps();
        $db_connection = $db_ops->get_db_connection();
        $stmt = $db_connection->prepare('UPDATE field SET valid_to=CURDATE() WHERE sid=? AND valid_to IS NULL');
        $stmt->bind_param("i",$field_id);
        $errno = $db_ops->execute_stmt_without_result($stmt);
        if($errno) {
            $db_connection->close();
            return $errno;
        }
        $stmt = $db_connection->prepare('DELETE FROM organisation_has_field WHERE field_id = ?');
        $stmt->bind_param("i",$field_id);
        $errno = $db_ops->execute_stmt_without_result($stmt);
        $db_connection->close();
        return $errno;
    }

    /*
    * Inherited from AbstractController. $query_result contains either config for fields or data and builds the core of the JSON
    * After that the links have to be put together. The general case is a resource of the next layer (e.g. nuts1 regions)
    * that has to be added to the self link (/config/nuts0 -> /config/nuts0/nuts1)
    * 'data' and 'config' links are a special case, as they are formatted prior in the respective method
    * TODO: This is hardly readable. We need a better solution
    */
    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {
        $links['self'] = $self_link;

        for($i = 0; $i < sizeof($next_entity_types); $i++) {
            if($next_entity_types[$i] === 'data' || $next_entity_types[$i] === 'config') {
                $links[$next_entity_types[$i]] = $next_entities[$i];
            } else {
                foreach($next_entities[$i] as $entity) {
                    $links[$next_entity_types[$i]] = $self_link . '/' . $entity;
                }
            }
        }
        $json_array = array('fields'=>$query_result, 'links'=>$links);
        return $json_array;
    }
}

?>
