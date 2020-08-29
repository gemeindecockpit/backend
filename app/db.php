<?php

# PHP class
# Operations
# Data input and output to MySQL database
# and data analysis
require_once(__DIR__ . '/../app/config.php');

class DatabaseOps {

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

<<<<<<< HEAD

		public function view_all_organisation_visible_for_user($user_id) {
			$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
			$stmt = $db->prepare('SELECT * FROM view_organisation_visible_for_user WHERE user_id = ?');
			$stmt->bind_param('i', $user_id);
			$stmt->execute();
			$result = $stmt->get_result();
			$db->close();
			return $result;
		}

		public function view_one_organisation_visible_for_user($user_id, $org_id, $org_type) {
			$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
			$stmt = $db->prepare('SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND organisation_id = ? AND tpye = ?');
			$stmt->bind_param('iis', $user_id, $org_id, $org_type);
			$stmt->execute();
			$result = $stmt->get_result();
			$db->close();
			return $result;
		}

		//TODO:
		public function view_data_for_fields_for_org_for_user($user_id, $org_name, $org_type){

		}

		public function view_one_organisation_visible_for_user_by_name($user_id, $org_name, $org_type) {
			$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
			$stmt = $db->prepare('SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND name = ? AND type = ?');
			$stmt->bind_param('iss', $user_id, $org_name, $org_type);
			$stmt->execute();
			$result = $stmt->get_result();
			$db->close();
			return $result;
		}
=======
	private function get_db_connection() {
		return new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
	}

	private function get_stmt($stmt_string, $db) {
		$stmt = $db->prepare($stmt_string);
		return $stmt;
	}

	private function execute_stmt($stmt) {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result;
	}

>>>>>>> 706afcc985b300c050a5b907d1e22860eab2d604
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
		return $this->get_all_config_types_for_user($userid);
	}

	public function get_all_types_for_user($user_id){
		#TODO: richtig?
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT DISTINCT(type) FROM view_organisation_visible_for_user WHERE user_id = ?');
		$stmt->bind_param('i', $user_id);
		/*
		$stmt = $db->prepare('select DISTINCT type from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id
		where can_see_organisation.user_id = ?
		Union distinct
		select distinct type from organisation
		inner join can_alter_organisation on can_alter_organisation.organisation_id = organisation.id
		where can_alter_organisation.user_id = ?');
		*/
		//$errors = $db->error_list;
		//$stmt->bind_param('ii', $userid, $userid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	public function get_all_data_organizations_for_user_for_type($userid, $type){
		#TODO: richtig?
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select organisation.* from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id
		where can_see_organisation.user_id = ? and type = ?
		Union distinct select organisation.* from organisation
		inner join can_alter_organisation on can_alter_organisation.organisation_id = organisation.id
		where can_alter_organisation.user_id = ? and type = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('isis', $userid, $type, $userid, $type);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	public function get_one_data_organizations_for_user_by_id($userid, $orgid){
		#TODO: richtig?
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select organisation.* from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id
		where can_see_organisation.user_id = ? and organisation.id = ?
		Union distinct select organisation.* from organisation
		inner join can_alter_organisation on can_alter_organisation.organisation_id = organisation.id
		where can_alter_organisation.user_id = ? and organisation.id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('iiii', $userid, $orgid, $userid, $orgid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	public function get_one_data_organizations_for_user_by_name($userid, $name){
		#TODO: richtig?
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('select organisation.* from organisation
		inner join can_see_organisation on can_see_organisation.organisation_id = organisation.id
		where can_see_organisation.user_id = ? and organisation.name = ?
		Union distinct select organisation.* from organisation
		inner join can_alter_organisation on can_alter_organisation.organisation_id = organisation.id
		where can_alter_organisation.user_id = ? and organisation.name = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('isis', $userid, $name, $userid, $name);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	public function get_all_config_organizations_for_user_for_type($userid, $type){
		#TODO:
		return get_all_data_organizations_for_user_for_type($userid, $type);
	}
	#oof
	public function get_all_fields_for_org($orgid){
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT field.* from field
		inner join organisation_has_field on organisation_has_field.field_id = field.id
		inner join organisation on organisation.id = organisation_has_field.organisation_id
		where organisation.id = ?');
		//$errors = $db->error_list;
		$stmt->bind_param('i', $orgid);

		$stmt->execute();
		$result = $stmt->get_result();
		$db->close();
		return $result;
	}

	public function get_NUTS_codes($user_id, ...$args) {
		$db = $this->get_db_connection();
		$stmt_string =
			'SELECT nuts0,nuts1,nuts2,nuts3
			FROM view_nuts
			WHERE zipcode
			IN (SELECT zipcode
				FROM view_organisation_visible_for_user
				WHERE user_id = ?';
		$param_string = 'i';
		for($i = 0; $i < sizeof($args); $i++) {
			$stmt_string .= ' AND nuts' . $i . ' = ?';
			$param_string .= 's';
		}
		$stmt_string .= ')';
		$stmt = $this->get_stmt($stmt_string, $db);
		$stmt->bind_param($param_string,$user_id, ...$args);

		$result = $this->execute_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_all_NUTS_codes_for_user($user_id) {
		$db = $this->get_db_connection();
		$stmt_string = 'SELECT nuts0,nuts1,nuts2,nuts3
				FROM view_nuts
				WHERE zipcode IN
				(SELECT zipcode FROM view_organisation_visible_for_user WHERE user_id = ?)';
		$stmt = $this->get_stmt($stmt_string, $db);
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_stmt($stmt);
		$db->close();
		return $result;
	}
}

?>
