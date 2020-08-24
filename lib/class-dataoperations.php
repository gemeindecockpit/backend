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
	//returns a resultset containing the userdata for $user where $user is the username
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
	#returns userdata by a given userid
	#TODO::make it a prepaired statement
	public function get_by_id($id)
    {

        $db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        $query = 'SELECT username, email, realname FROM user WHERE id= "'.$id.'"';
        $result = $db->query($query);
        $user_data = $result->fetch_assoc();
        $db->close();

    	return $user_data;
    }
	
	public function update_password($userid, $password){
		#TODO:
	}
	/* brauchen wir glaube ich garnicht, da wir ja eh nur eine plz haben oder? Wenn doch muss das auch noch ins datenmodell eingebaut werden
	public function get_all_config_PLZ_for_user($userid){
		#TODO:
	}
	
	public function get_all_data_PLZ_for_user($userid){
		#TODO:
	}
	*/
	public function get_all_data_types_for_user($userid){
		#TODO:
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select DISTINCT type from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id 
		where can_see_organisation.user_id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('i', $userid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}
	
	public function get_all_config_types_for_user($userid){
		#TODO:
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select DISTINCT type from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id 
		where can_see_organisation.user_id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('i', $userid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}
	
	public function get_all_data_organizations_for_user_for_type($userid, $type){
		#TODO:
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select name from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id 
		where can_see_organisation.user_id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('i', $userid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}
	
	public function get_all_config_organizations_for_user_for_type($userid, $type){
		#TODO:
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select name from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id 
		where can_see_organisation.user_id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('i', $userid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}
}

?>