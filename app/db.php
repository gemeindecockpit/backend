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


	// Helper functions to tidy up the other functions
	private function get_db_connection() {
		return new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
	}

	private function execute_select_stmt($stmt) {
		$stmt->execute();
		$result = $this->execute_select_stmt($stmt);
		$stmt->close();
		return $result;
	}

	private function execute_insert_stmt($stmt) {
		$stmt->execute();
		$errno = $stmt->errno
		$stmt->close();
		return $errno;
	}



		public function get_all_organisations($user_id) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('i', $user_id);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisation_by_id($user_id, $org_id) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND organisation_id = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('iis', $user_id, $org_id);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations($user_id, ...$args) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ?';
			$param_string = 'i';
			foreach($args as $key=>$value) {
				$param_string .= 's';
				$stmt_string .= ' AND ' . $key . ' = ' . $value;
			}
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param($param_string, $user_id, ...$args);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_alterable_organisations_by_type($user_id, $org_type){
			#TODO: richtig?
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT
					organisation_id, name, type, description, contact, zipcode, nuts0, nuts1, nuts2, nuts3
				FROM view_organisation_visible_for_user
				WHERE user_id = ? AND type = ? AND can_alter = 1')
			//$errors = $db->error_list;
			$stmt->bind_param('is', $user_id, $org_type);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_alterable_organisation_by_id($userid, $orgid){
			#TODO: richtig?
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT
					organisation_id, name, type, description, contact, zipcode, nuts0, nuts1, nuts2, nuts3
				FROM view_organisation_visible_for_user
				WHERE user_id = ? AND organisation_id = ? AND can_alter = 1')
			//$errors = $db->error_list;
			$stmt->bind_param('ii', $user_id, $org_id);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_alterable_organisations_by_name($user_id, $org_name){
			#TODO: richtig?
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT
					organisation_id, name, type, description, contact, zipcode, nuts0, nuts1, nuts2, nuts3
				FROM view_organisation_visible_for_user
				WHERE user_id = ? AND name = ? AND can_alter = 1')
			//$errors = $db->error_list;
			$stmt->bind_param('is', $user_id, $org_name);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		//TODO:
		public function get_data_for_fields_for_org_for_user($user_id, $org_name, $org_type){

		}




	//returns a resultset containing the userdata for $user where $user is the username
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function get_user($username) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT * FROM user WHERE username = ?');
		$stmt->bind_param('s', $username); // 's' specifies the variable type => 'string'
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	//returns the number of occurrences of users with the username $user as a resultset
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function get_user_count($user){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT COUNT(*) as counter FROM user WHERE username = ?');
		$stmt->bind_param('s', $user); // 's' specifies the variable type => 'string'
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}
	//returns the error code of the insert querry. 0 if there was no error
	//TESTED: verified for working. if any changes are made to the method either retest or remove the 'TESTED'-tag
	public function insert_new_user($username, $userpassword, $email, $realname, $salt){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('INSERT INTO user (username, userpassword, email, realname, salt) VALUES (?, ?, ?, ?, ?)');
		//$errors = $db->error_list;
		$stmt->bind_param('sssss', $username, $userpassword, $email, $realname, $salt);
		$error = $this->execute_insert_stmt($stmt);
		$db->close();

		return $error;
	}
	#returns userdata by a given userid
	public function get_user_by_id($user_id)
    {
        $db = $this->get_db_connection();
		$stmt_string = 'SELECT username, email, realname FROM user WHERE id = ?';
        $stmt = $db->prepare($stmt_string);
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
        $db->close();

    	return $result;
    }

	public function update_password($user_id, $password){
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
	public function get_all_data_types_for_user($user_id){
		#TODO:
		return $this->get_all_config_types_for_user($user_id);
	}

	public function get_all_types_for_user($user_id){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT DISTINCT(type) FROM view_organisation_visible_for_user WHERE user_id = ?');
		$stmt->bind_param('i', $user_id);

		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}



	public function get_all_config_organisations_for_user_for_type($userid, $type){
		#TODO:
		return get_all_data_organisations_for_user_for_type($userid, $type);
	}
	#oof
	public function get_fields_by_organisation_id($org_id){
		$db = $this->get_db_connection();
		$stmt_string = 'SELECT * from view_organisations_and_fields WHERE organisation_id = ?';
		$stmt = $db->prepare($stmt_string);
		//$errors = $db->error_list;
		$stmt->bind_param('i', $org_id);

		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_NUTS_codes($user_id, ...$args) {
		$db = $this->get_db_connection();
		$stmt_string =
			'SELECT DISTINCT nuts0,nuts1,nuts2,nuts3
			FROM view_organisation_visible_for_user
			WHERE user_id = ?';
		$param_string = 'i';
		for($i = 0; $i < sizeof($args); $i++) {
			$stmt_string .= ' AND nuts' . $i . ' = ?';
			$param_string .= 's';
		}
		$stmt_string .= ')'; // End the query_string
		$stmt = $this->get_stmt($stmt_string, $db);
		$stmt->bind_param($param_string,$user_id, ...$args);

		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_all_NUTS_codes_for_user($user_id) {
		$db = $this->get_db_connection();
		$stmt_string =
				'SELECT DISTINCT nuts0,nuts1,nuts2,nuts3
				FROM view_nuts
				WHERE user_id = ?)';
		$stmt = $this->get_stmt($stmt_string, $db);
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}
}

?>
