<?php

require_once('DatabaseOps.php');
require_once('DatabaseAccess.php');

/*
* To be renamed and refactored as a model
*/
abstract class AbstractController {

    protected $db_ops;

    public function __construct() {
        $this->db_ops = new DatabaseOps();
    }

    protected function execute_stmt($stmt_string, $param_string, ...$args) {
        $db_access = new DatabaseAccess();
        $no_error = $db_access->prepare_stmt($stmt_string);
        if($no_error) {
            $no_error = $db_access->bind_param($param_string, ...$args);
        }

        $query_result;
        if($no_error) {
            $query_result = $db_access->execute();
        } else {
            return [];
        }
        if(!$query_result) {
            return [];
        }
        $result = [];
        while($row = $query_result->fetch_assoc()) {
            $result[] = $row;
        }

        $db_access->close_db();

        return $result;
    }

    public static function assoc_array_to_indexed($assoc_array) {
        $indexed_array = [];
        foreach($assoc_array as $value) {
            $indexed_array[] = $value;
        }
        return $indexed_array;
    }

    protected function format_query_result($query_result) {
        $result = [];
        while($row = $query_result->fetch_assoc()) {
            array_walk_recursive($row, [$this, 'encode_items']);
            array_push($result, $row);
        }
        return $result;
    }

    protected function get_link(...$args) {
      array_walk_recursive($args, [$this, 'encode_items_url']);
      return $_SERVER['SERVER_NAME'] . '/' . implode('/', $args);
    }

    /**
    * Creates the array that is latter encoded as a JSON
    * @param $self_link
    *   The link to the current resource
    * @param $query_result
    *   The current resource
    * @param $next_entity_types
    *   The types of resources that are linked in the JSON
    * @param $next_entities
    *   The resources that are linked in the JSON
    */
    abstract protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []);

    function encode_items_url(&$item, $key){
      $item = rawurlencode($item);
    }
    function encode_items(&$item, $key){
       $item = utf8_encode($item);
     }
}


 ?>
