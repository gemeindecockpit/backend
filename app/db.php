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
		$results = $stmt->get_result();
		$stmt->close();
		return $results;
	}

	private function execute_insert_stmt($stmt) {
		$stmt->execute();
		$errno = $stmt->errno;
		//$stmt->close();
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

		public function get_organisations_by_nuts0($user_id, $nuts0) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND nuts0 = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('is', $user_id, $nuts0);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations_by_nuts01($user_id, $nuts0, $nuts1) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND nuts0 = ? AND nuts1 = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('iss', $user_id, $nuts0, $nuts1);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations_by_nuts012($user_id, $nuts0, $nuts1, $nuts2) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('isss', $user_id, $nuts0, $nuts1, $nuts2);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations_by_nuts0123($user_id, $nuts0, $nuts1, $nuts2, $nuts3) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('issss', $user_id, $nuts0, $nuts1, $nuts2, $nuts3);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations_by_nuts0123_type($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND type = ?';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param('isssss', $user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisations_by_nuts0123_type_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT * FROM view_organisation_visible_for_user
				WHERE user_id = ?
				AND nuts0 = ?
				AND nuts1 = ?
				AND nuts2 = ?
				AND nuts3 = ?
				AND type = ?
				AND name = ?'
			);
			$stmt->bind_param('issssss', $user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_config_for_field_by_name($user_id, $org_id, $field_name) {
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT view_fields_visible_for_user.*
				FROM view_fields_visible_for_user
				JOIN view_organisations_and_fields
					ON view_fields_visible_for_user.field_id = view_organisations_and_fields.field_id
				WHERE view_fields_visible_for_user.user_id = ?
				AND view_organisations_and_fields.organisation_id = ?
				AND view_fields_visible_for_user.field_name = ?'
			);
			$stmt->bind_param('iis', $user_id, $org_id, $field_name);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_organisation_by_name($user_id, $type, $entity) {
			$db = $this->get_db_connection();
			$stmt_string = 'SELECT * FROM view_organisation_visible_for_user WHERE user_id = ? AND type = ? AND name = ?';
			$param_string = 'iss';
			$stmt = $db->prepare($stmt_string);
			$stmt->bind_param($param_string, $user_id, $type, $entity);
			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_alterable_organisations_by_type($user_id, $org_type){
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT
					organisation_id, name, type, description, contact, zipcode, nuts0, nuts1, nuts2, nuts3
				FROM view_organisation_visible_for_user
				WHERE user_id = ? AND type = ? AND can_alter = 1');
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
				WHERE user_id = ? AND organisation_id = ? AND can_alter = 1');
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
				WHERE user_id = ? AND name = ? AND can_alter = 1');
			//$errors = $db->error_list;
			$stmt->bind_param('is', $user_id, $org_name);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_all_types($user_id, $nuts0, $nuts1, $nuts2, $nuts3) {
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT DISTINCT type
				FROM view_organisation_visible_for_user
				WHERE user_id = ?
				AND nuts0 = ?
				AND nuts1 = ?
				AND nuts2 = ?
				AND nuts3 = ?'
			);
			$stmt->bind_param('issss', $user_id, $nuts0, $nuts1, $nuts2, $nuts3);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
		}

		public function get_all_fields_from_organisation_by_id($user_id, $organisation_id) {
			$db = $this->get_db_connection();
			$stmt = $db->prepare(
				'SELECT
					field_id, field_name, max_value, yellow_value, red_value, relational_flag, view_organisations_and_fields.priority, can_see_organisation.can_alter
				FROM view_organisations_and_fields
				JOIN can_see_organisation ON can_see_organisation.organisation_id = view_organisations_and_fields.organisation_id
				WHERE can_see_organisation.user_id = ?
				AND can_see_organisation.organisation_id = ?');
			$stmt->bind_param('ii', $user_id, $organisation_id);

			$result = $this->execute_select_stmt($stmt);
			$db->close();
			return $result;
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
	public function insert_new_user($username, $userpassword, $email, $realname, $salt){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('INSERT INTO user (username, userpassword, email, realname, salt) VALUES (?, ?, ?, ?, ?)');
		//$errors = $db->error_list;
		$stmt->bind_param('sssss', $username, $userpassword, $email, $realname, $salt);
		$error = $this->execute_insert_stmt($stmt);
		$db->close();

		return $error;
	}

	public function get_user_by_name($active_user_id, $passive_user_name){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT passive_user_id  AS \'user_id\', username, email, realname, can_alter
			FROM user JOIN can_see_user ON user.id = can_see_user.passive_user_id
			WHERE active_user_id = ?
			AND user.username = ?');
		$stmt->bind_param('is', $active_user_id, $passive_user_name);
		$result = $this->execute_select_stmt($stmt);
		$db->close();

		return $result;
	}

	#returns userdata by a given userid
	public function get_user_by_id($active_user_id, $passive_user_id){
        $db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT passive_user_id  AS \'user_id\', username, email, realname, can_alter
			FROM user JOIN can_see_user ON user.id = can_see_user.passive_user_id
			WHERE active_user_id = ?
			AND user.id = ?');
		$stmt->bind_param('ii', $active_user_id, $passive_user_id);
		$result = $this->execute_select_stmt($stmt);
        $db->close();

    	return $result;
    }

	public function get_all_users_visible_for_user($user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT passive_user_id  AS \'user_id\', username, email, realname, can_alter
			FROM user JOIN can_see_user ON user.id = can_see_user.passive_user_id
			WHERE active_user_id = ?');
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_login_info($username) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT id, username, userpassword, salt FROM user WHERE username = ?');
		$stmt->bind_param('s', $username);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function update_password($user_id, $password){
		#TODO:
	}

	public function get_all_data_types_for_user($user_id){
		#TODO:
		return $this->get_all_config_types_for_user($user_id);
	}

	public function get_all_types_for_user($user_id){
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT DISTINCT(type) FROM view_organisation_visible_for_user WHERE user_id = ?');
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		$results = $stmt->get_result();
		$stmt->close();
		$db->close();
		return $results;
	}



	public function get_all_config_organisations_for_user_for_type($userid, $type){
		#TODO:
		return get_all_data_organisations_for_user_for_type($userid, $type);
	}

	public function get_configfields_by_organisation_id($org_id){
		$db = $this->get_db_connection();
		$stmt_string = 'SELECT field_id, field_name, max_value, yellow_value, red_value, relational_flag, priority from view_organisations_and_fields WHERE organisation_id = ?';
		$stmt = $db->prepare($stmt_string);
		//$errors = $db->error_list;
		$stmt->bind_param('i', $org_id);

		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}


	public function get_datafields_by_organisation_name($user_id, $org_name, $org_type){
		$db = $this->get_db_connection();
		$stmt_string = 'SELECT view_up_to_date_data_from_all_fields.field_id, view_organisations_and_fields.field_name, view_up_to_date_data_from_all_fields.field_value, view_up_to_date_data_from_all_fields.date FROM view_organisations_and_fields JOIN view_organisation_visible_for_user ON view_organisation_visible_for_user.organisation_id = view_organisations_and_fields.organisation_id JOIN view_up_to_date_data_from_all_fields ON view_up_to_date_data_from_all_fields.field_id = view_organisations_and_fields.field_id INNER JOIN (SELECT field_id , MAX(date) AS date FROM view_up_to_date_data_from_all_fields GROUP BY field_id) AS max_date ON max_date.field_id = view_up_to_date_data_from_all_fields.field_id AND view_up_to_date_data_from_all_fields.date = max_date.date where user_id = ? AND organisation_name = ? AND organisation_type = ?';
		$stmt = $db->prepare($stmt_string);
		//$errors = $db->error_list;
		$stmt->bind_param('iss',$user_id , $org_name, $org_type);

		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_next_NUTS_codes($user_id, ...$args) {
		$db = $this->get_db_connection();
		$stmt_string =
			'SELECT DISTINCT (nuts' . sizeof($args) .
			') FROM view_organisation_visible_for_user
			WHERE user_id = ?';
		$param_string = 'i';
		for($i = 0; $i < sizeof($args); $i++) {
			$stmt_string .= ' AND nuts' . $i . ' = ?';
			$param_string .= 's';
		}
		$stmt = $db->prepare($stmt_string);
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
		$stmt = $db->prepare($stmt_string);
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	function utf8_converter($array){
	array_walk_recursive($array, function(&$item, $key){
		$item = mb_convert_encoding($item, 'UTF-8');
	});

	return $array;
	}
}

?>
