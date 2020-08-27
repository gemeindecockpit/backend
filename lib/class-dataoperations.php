<?php

# PHP class
# Operations
# Data input and output to MySQL database
# and data analysis

class DataOperations {

	protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_user_password;
	protected $db_connection;


	public function __construct()
	{
		if(defined('DB_HOST'))
            $this->db_host = DB_HOST;
        if(defined('DB_NAME'))
            $this->db_name = DB_NAME;
        if(defined('DB_USER'))
            $this->db_user = DB_USER;
        if(defined('DB_USER_PASSWORD'))
            $this->db_user_password = DB_USER_PASSWORD;

		$this->db_connection = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);

        return;
    }

	public function get_db_connection() {
		return $this->db_connection;
	}

	//returns a resultset containing the userdata for $user
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function get_user($user) {
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT * FROM user WHERE username = ?');
		$stmt->bind_param('s', $user); // 's' specifies the variable type => 'string'
		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	//returns the number of occurrences of users with the username $user as a resultset
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function getUserCount($user){
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT COUNT(*) as counter FROM user WHERE username = ?');
		$stmt->bind_param('s', $user); // 's' specifies the variable type => 'string'
		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}
	//returns the error code of the insert querry. 0 if there was no error
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function insertNewUser($username, $userpassword, $email, $realname, $salt){
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('INSERT INTO user (username, userpassword, email, realname, salt) VALUES (?, ?, ?, ?, ?)');
		//$errors = $db->error_list;
		$stmt->bind_param('sssss', $username, $userpassword, $email, $realname, $salt);

		$stmt->execute();
		$error = $stmt->errno;
		$db->close();
		return $error;
	}

	public function selectFromOrganisation($path_vars) {
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);

		if (sizeof($path_vars) >= 4) {
	        $stmt = $db->prepare("SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND zipcode = ? AND type = ? AND name = ?");
	        $stmt->bind_param("isss", $_SESSION['user_id'],$path_vars[1],$path_vars[2],$path_vars[3]);
	    } elseif (sizeof($path_vars) == 3) {
	        $stmt = $db->prepare("SELECT DISTINCT(name) FROM view_organisation_visible_for_user WHERE user_id = ? AND zipcode=? AND type=?");
	        $stmt->bind_param("iss", $_SESSION['user_id'],$path_vars[1],$path_vars[2]);
	    } elseif (sizeof($path_vars) == 2) {
	        $stmt = $db->prepare("SELECT DISTINCT(type) FROM view_organisation_visible_for_user WHERE user_id = ? AND zipcode=?");
	        $stmt->bind_param("is", $_SESSION['user_id'],$path_vars[1]);
	    } else {
	        $stmt = $db->prepare("SELECT DISTINCT(zipcode) FROM view_organisation_visible_for_user WHERE user_id = ?");
			$stmt->bind_param("i", $_SESSION['user_id']);
	    }

	    $stmt->execute();
		$result = $stmt->get_result();

		$db->close();
		return $result;
	}

	public function getFieldsFromOrganisation($organisation_id) {
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);

		$stmt = $db->prepare("SELECT * FROM organisations_and_fields WHERE organisation_id = ?");
		$stmt->bind_param("s",$organisation_id);

		$stmt->execute();
		$result = $stmt->get_result();

		$db->close();
		return $result;
	}

	public function getFieldFromName($organisation_id, $field_name) {
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);

		$stmt = $db->prepare("SELECT * FROM organisations_and_fields WHERE organisation_id = ? AND field_name = ?");
		$stmt->bind_param("ss",$organisation_id,$field_name);

		$stmt->execute();
		$result = $stmt->get_result();

		$db->close();
		return $result;
	}


}

?>
