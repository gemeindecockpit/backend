<?php

require_once("AbstractController.php");

class NUTSController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function getOne($user_id, $id) {

    }

    public function getAll($user_id) {
        $query_result = $this->db_ops->get_all_NUTS_codes_for_user($user_id);
        $nuts_codes = array();

        while($row = $query_result->fetch_assoc()) {
            $nuts_codes[] = $row;
        }
        return $nuts_codes;
    }
}

?>
