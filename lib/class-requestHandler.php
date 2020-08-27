<?php

abstract class RequestHandler {

  public $path_vars;
  protected $db_connection;
  protected $data_operator;

  public function __construct($path_vars,$data_operator)
  {
        $this->path_vars = $path_vars;
        $this->db_connection = $data_operator->get_db_connection();
        $this->data_operator = $data_operator;
  }

  public function handleRequest()
  {
    $resultSet;
    if($this->path_vars[0] == 'config') {
      $resultSet = $this->handleConfigRequest();
    } else if ($this->path_vars[0] == 'data') {
      $resultSet = $this->handleDataRequest();
    } else if ($this->path_vars[0] == 'user') {
      $resultSet = $this->handleUserRequest();
    } else {
      $resultSet = array('error_msg'=>'Invalid Request');
    }

    return json_encode($resultSet);
  }

  abstract protected function handleConfigRequest();
  abstract protected function handleDataRequest();
  abstract protected function handleUserRequest();

}

?>
