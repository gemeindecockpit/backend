<?php

require_once(__DIR__ . '/../../../config/config.php');

class DatabaseAccess
{
    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_user_password;

    private $db_connection;

    private $stmt;


    public function __construct()
    {
        $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_USER_PASSWORD, DB_NAME);
        $this->params = [];
        $this->param_string = '';
        return;
    }

    public function prepare($stmt_string)
    {
        if ($this->stmt) {
            $this->stmt->close();
        }
        if ($this->stmt = $this->db_connection->prepare($stmt_string)) {
            return true;
        } else {
            $this->db_connection->close();
            return false;
        }
    }

    public function bind_param($param_string, ...$params)
    {
        if (!$this->stmt) {
            return false;
        }
        if($this->stmt->bind_param($param_string, ...$params)) {
            return true;
        } else {
            $this->db_connection->close();
            return false;
        }
    }

    public function execute()
    {
        if (!$this->stmt->execute()) {
            return $this->stmt->errno;
        }

        $result = $this->stmt->get_result();
        if (!$result) {
            $result = $this->stmt->errno;
        }

        return $result;
    }

    public function get_insert_id() {
        return $this->stmt->insert_id;
    }

    public function get_error() {
        return $this->stmt->error;
    }


    public function close()
    {
        if($this->stmt) {
            $this->stmt->close();
        }
        return $this->db_connection->close();
    }
}
?>
