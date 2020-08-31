<?php

require_once("AbstractController.php");

class NUTSController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_one($user_id, ...$args) {
        $nuts_codes = array();
        if(is_null($args)) {
            $nuts_codes = $this->getAll($user_id);
        } else {
            $query_result = $this->db_ops->get_NUTS_codes($user_id, ...$args);

            while($row = $query_result->fetch_assoc()) {
                $nuts_codes[] = $row;
            }
        }
        return $nuts_codes;
    }

    public function get_all($user_id) {
        $query_result = $this->db_ops->get_all_NUTS_codes_for_user($user_id);
        $nuts_codes = array();

        while($row = $query_result->fetch_assoc()) {
            $nuts_codes[] = $row;
        }
        return $nuts_codes;
    }
}

?>
