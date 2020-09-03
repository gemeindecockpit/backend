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

    protected function format_query_result_datafields($org, $fields_array) {
        $result = [];
        //foreach($query_results as $ar){
          //$result_temp = [];

            $row = $org->fetch_assoc();
            $fields = [];
              array_walk_recursive($row, [$this, 'encode_items']);
              while ($row2 = $fields_array->fetch_assoc()){
                array_walk_recursive($row2, [$this, 'encode_items']);
                $fields[] = $row2;
              }
              $row['fields'] = $fields;
              array_push($result, $row);

          //$result[] = $result_temp;
        //}

        return $result;
    }

    protected function get_self_link(...$args) {
      array_walk_recursive($args, [$this, 'encode_items_url']);
      return $_SERVER['SERVER_NAME'] . '/' . implode('/', $args);
    }

    abstract protected function format_json($self_link, $query_result, $next_entity_type = '', $next_entities = []);

    function encode_items_url(&$item, $key){
      $item = rawurlencode($item);
    }
    function encode_items(&$item, $key){
       $item = utf8_encode($item);
     }
/*
  //needed for GET-Requests
  //returns one entity
  abstract public function get_one($user_id, ...$args);
  //returns all entities of this type
  abstract public function get_all($user_id);


  //needed for POST-Requests
  //creates a new Entity and saves it to the DB
  abstract public function createNew($user_id, $array);

  //needed for PUT-Requests
  //edits one entity
  abstract public function edit($user_id, $id);

  //needed for DELETE-Requests
  //sets one entity to inactive
  abstract public function delete($user_id, $id);
*/
}


 ?>
