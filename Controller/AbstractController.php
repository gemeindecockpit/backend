<?php

require_once(__DIR__ . "/../app/db.php");

abstract class AbstractController {

    protected $db_ops;

    public function __construct() {
        $this->db_ops = new DatabaseOps();
    }

    protected function assoc_array_to_indexed($assoc_array) {
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

    abstract protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []);

    function encode_items_url(&$item, $key){
      $item = rawurlencode($item);
    }
    function encode_items(&$item, $key){
       $item = utf8_encode($item);
     }
}


 ?>
