<?php

require_once("AbstractController.php");

class NUTSController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($user_id) {
        $query_result = $this->db_ops->get_all_NUTS_codes_for_user($user_id);
        $nuts_codes = array();

        while($row = $query_result->fetch_assoc()) {
            $nuts_codes[] = $row;
        }
        return $nuts_codes;
    }

    public function get_next_NUTS_codes($user_id, ...$args) {
        $query_result = $this->db_ops->get_next_NUTS_codes($user_id, ...$args);
        return $query_result->fetch_all();
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {}

}

?>
