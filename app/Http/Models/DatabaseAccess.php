<?php

require_once(__DIR__ . '/../../../config/config.php');

class DatabaseAccess {

	protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_user_password;

	private $db_connection;

	private $stmt;
	private $params;
	private $param_string;


	public function __construct()
	{

		$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_USER_PASSWORD, DB_NAME);
		$this->params = [];
		$this->param_string = '';
        return;
    }

	public function prepare_stmt($stmt_string) {
		if($this->stmt) {
			$this->stmt->close();
			$this->params = [];
			$this->param_string = '';
		}
		if($this->stmt = $this->db_connection->prepare($stmt_string)) {
			return true;
		} else {
			return false;
		}
	}

	public function add_param($type, $value) {
		$this->param_string .= $type;
		$this->params[] = $value;
	}

	public function bind_param($param_string, $user_id, ...$params) {
		if(!$this->stmt) {
			return false;
		}
		return $this->stmt->bind_param($param_string, $user_id, ...$params);
	}

	public function execute() {
		if(!$this->stmt->execute()) {
			return $this->stmt->errno;
		}

		$result = $this->stmt->get_result();
		if(!$result) {
			$result = $this->stmt->errno;
		}

		$this->stmt->close();
		$this->params = [];
		$this->param_string = '';

		return $result;
	}

	public function close_db() {
		return $this->db_connection->close();
	}
}
?>
