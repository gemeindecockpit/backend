<?php

require_once(__DIR__ . "/../app/db.php");

abstract class AbstractController {

    protected $db_ops;

    public function __construct() {
        $this->db_ops = new DatabaseOps();
    }


  //needed for GET-Requests
  //returns one entity
  abstract public function get_one($user_id, ...$args);
  //returns all entities of this type
  abstract public function get_all($user_id);

/*
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
