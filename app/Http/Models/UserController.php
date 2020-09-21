<?php

require_once("AbstractController.php");


/*
* To be renamed and refactored as a model
*/
class UserController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function can_create_organisation($user_id) {
        $db_ops = new DatabaseOps();
        $db_connection = $db_ops->get_db_connection();
        $stmt = $db_connection->prepare('SELECT * FROM can_create_organisation WHERE user_id = ?');
        $stmt->bind_param("i", $user_id);
        $query_result = $db_ops->execute_select_stmt($stmt);
        $db_connection->close();
        return $query_result->num_rows > 0;
    }

    public function get_one($user_id, ...$args) {
        
        $arg = $args[0];
        if(isset($arg['user_id'])) {
            $query_result = $this->db_ops->get_user_by_id($user_id, $arg['user_id']);
        } else if (isset($arg['username'])) {
            $query_result = $this->db_ops->get_user_by_name($user_id, $arg['username']);
        } else {
            return; // TODO: Define the error
        }

        return $query_result->fetch_assoc();
    }

    public function get_all($user_id) {
        $query_result = $this->db_ops->get_all_users_visible_for_user($user_id);
        $user_array = array();
        while($row = $query_result->fetch_assoc()) {
            $user_array[] = $row;
        }
        return $user_array;
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {}
}

?>
