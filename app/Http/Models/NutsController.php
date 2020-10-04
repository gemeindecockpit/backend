<?php

require_once("AbstractController.php");


/*
* To be renamed and refactored as a model
*/
class NUTSController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_next_NUTS_codes($user_id, ...$args) {
        $db_access = DatabaseAccess::get_instance();
        $stmt_string =
            'SELECT DISTINCT (nuts' . sizeof($args) .
            ') FROM view_organisation_visible_for_user
			WHERE user_id = ?';
        $param_string = 'i';
        for($i = 0; $i < sizeof($args); $i++) {
            $stmt_string .= ' AND nuts' . $i . ' = ?';
            $param_string .= 's';
        }
        $db_access->prepare($stmt_string);
        $db_access->bind_param($param_string, $user_id, ...$args);
        $query_result = $db_access->execute();
        $next_nuts = [];
        while($row = $query_result->fetch_array()) {
            $next_nuts[] = $row[0];
        }
        return $next_nuts;
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {}

}

?>
