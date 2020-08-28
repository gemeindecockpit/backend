<?php

interface IController {

    // Blablabla


  //needed for GET-Requests
  //returns one entity
  public getOne($id);
  //returns all entities of this type
  public getAll();


  //needed for POST-Requests
  //creates a new Entity and saves it to the DB
  public createNew($array);

  //needed for PUT-Requests
  //edits one entity
  public edit($id);

  //needed for DELETE-Requests
  //sets one entity to inactive
  public delete($id);

}


 ?>
