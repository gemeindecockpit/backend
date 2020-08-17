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

        return;
    }
	//returns a resultset containing the userdata for $user
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
	public function insertNewUser($username, $userpassword, $email, $realname, $salt){
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('INSERT INTO user (username, userpassword) VALUES (:name, :pass, :email, :realname, :salt)');
		$errors = $db->error_list;
		echo $stmt->debug
		echo gettype($errors) . ' has following errors: ' . sizeof($errors);
		foreach($errors as $temp){
			echo '<br>'.$temp;
		}
		$stmt->bind_param(':name', $username);
		$stmt->bind_param(':pass', $userpassword);
		$stmt->bind_param(':email', $email);
		$stmt->bind_param(':realname', $realname);
		$stmt->bind_param(':salt', $salt);
		$stmt->execute();
		$error = $stmt->errno;
		$db->close();
		return $error;
	}


}

?>