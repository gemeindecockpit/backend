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


	############################################################################################
	//    ______   ______   .__   __.  _______  __    _______
	//   /      | /  __  \  |  \ |  | |   ____||  |  /  _____|
	//  |  ,----'|  |  |  | |   \|  | |  |__   |  | |  |  __
	//  |  |     |  |  |  | |  . `  | |   __|  |  | |  | |_ |
	//  |  `----.|  `--'  | |  |\   | |  |     |  | |  |__| |
	//   \______| \______/  |__| \__| |__|     |__|  \______|
	//
	############################################################################################

	/////////////////////////////////////////
	//		organisations				  //
	////////////////////////////////////////


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

	public function get_all_fields_from_organisation_by_name($user_id, $organisation_name) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				field_id, field_name, max_value, yellow_value, red_value, relational_flag, view_organisations_and_fields.priority, can_see_organisation.can_alter
			FROM view_organisations_and_fields
			JOIN can_see_organisation ON can_see_organisation.organisation_id = view_organisations_and_fields.organisation_id
			WHERE can_see_organisation.user_id = ?
			AND view_organisations_and_fields.organisation_name = ?');
		$stmt->bind_param('is', $user_id, $organisation_name);

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



	/////////////////////////////////////////
	//		fields				  		  //
	////////////////////////////////////////

	public function get_all_fields($user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
				'SELECT *
				FROM view_fields_visible_for_user
				WHERE user_id = ?'
		);
		$stmt->bind_param('i', $user_id);
		$query_result = $this->execute_select_stmt($stmt);

		$db->close();
		return $query_result;
	}

	public function get_config_for_field_by_name($user_id, $org_id, $field_name) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				view_fields_visible_for_user.user_id AS user_id,
				view_fields_visible_for_user.field_id AS field_id,
				view_fields_visible_for_user.field_name AS field_name,
				view_fields_visible_for_user.max_value AS max_value,
				view_fields_visible_for_user.yellow_value AS yellow_value,
				view_fields_visible_for_user.red_value AS red_value,
				view_fields_visible_for_user.relational_flag AS relational_flag,
				view_fields_visible_for_user.can_alter AS can_alter
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

	public function get_config_for_fields_by_organisation_id($user_id, $org_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT view_fields_visible_for_user.*
			FROM view_fields_visible_for_user
			JOIN view_organisations_and_fields
				ON view_fields_visible_for_user.field_id = view_organisations_and_fields.field_id
			WHERE view_fields_visible_for_user.user_id = ?
			AND view_organisations_and_fields.organisation_id = ?'
		);
		$stmt->bind_param('ii', $user_id, $org_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	public function get_field_ids_by_org_full_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT DISTINCT field_id
			FROM view_fields_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND organisation_type = ?
			AND organisation_name = ?
			ORDER BY field_id'
		);
		$stmt->bind_param('issssss', $user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_field_id_by_org_full_link_field_name($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $field_name) {
		$args = func_get_args();
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT DISTINCT field_id
			FROM view_fields_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND organisation_type = ?
			AND organisation_name = ?
			AND field_name = ?'
		);
		$stmt->bind_param('isssssss', ...$args);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}



	/////////////////////////////////////////
	//		users				  	      //
	////////////////////////////////////////


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


	/////////////////////////////////////////
	//		NUTS 						  //
	////////////////////////////////////////


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
		$stmt = $db->prepare(
			'SELECT DISTINCT nuts0,nuts1,nuts2,nuts3
				FROM view_nuts
				WHERE user_id = ?'
			);
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}


	############################################################################################
    //   _______       ___   .___________.    ___
    //  |       \     /   \  |           |   /   \
    //  |  .--.  |   /  ^  \ `---|  |----`  /  ^  \
    //  |  |  |  |  /  /_\  \    |  |      /  /_\  \
    //  |  '--'  | /  _____  \   |  |     /  _____  \
    //  |_______/ /__/     \__\  |__|    /__/     \__\
    //
    #############################################################################################

	public function get_all_data_by_field_id($user_id, $field_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields ON view_organisations_and_fields.field_id = data.field_id
			JOIN field ON field.id = data.field_id
			WHERE can_see_field.user_id = ?
			AND data.field_id = ?
			ORDER BY date DESC'
		);
		$stmt->bind_param('ii', $user_id, $field_id);

		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_data_field_id_year($user_id, $field_id, $year) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields ON view_organisations_and_fields.field_id = data.field_id
			JOIN field ON field.id = data.field_id
			WHERE can_see_field.user_id = ?
			AND data.field_id = ?
			AND date >= ?
			AND date < date_add(?, INTERVAL 1 YEAR)
			ORDER BY date DESC'
		);
		$stmt->bind_param('iiss', $user_id, $field_id, $year, $year);

		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_data_field_id_month($user_id, $field_id, $month) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields ON view_organisations_and_fields.field_id = data.field_id
			JOIN field ON field.id = data.field_id
			WHERE can_see_field.user_id = ?
			AND data.field_id = ?
			AND date >= ?
			AND date < date_add(?, INTERVAL 1 MONTH)
			ORDER BY date DESC'
		);
		$stmt->bind_param('iiss', $user_id, $field_id, $month, $month);

		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_latest_data_by_field_name($user_id, $organisation_id, $field_name) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				can_see_field.field_id as field_id
			FROM view_organisations_and_fields
			JOIN can_see_field
				ON view_organisations_and_fields.field_id = can_see_field.field_id
			WHERE can_see_field.user_id = ?
			AND view_organisations_and_fields.organisation_id = ?
			AND view_organisations_and_fields.field_name = ?'
		);
		$stmt->bind_param('iis', $user_id, $organisation_id, $field_name);
		$query_result = $this->execute_select_stmt($stmt);

		$field_id = -1;
		if($row = $query_result->fetch_assoc()) {
			$field_id = $row['field_id'];
		}

		$db->close();
		return $this->get_latest_data_by_field_id($user_id, $field_id);
	}

	public function get_latest_data_by_full_organisation_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $type, $name) {
		$db = $this->get_db_connection();
		$args = func_get_args();
		$stmt_get_field_ids = $db->prepare(
			'SELECT
				field_id,
			FROM view_fields_visible_for_user
			WHERE user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND type = ?
			AND name = ?'
		);
		$stmt->bind_param('issssss', ...$args);
		$query_result_field_ids = $this->execute_select_stmt($stmt_get_field_ids);



		$db->close();
		return $query_result;
	}

	public function get_latest_data_by_field_id($user_id, $field_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields ON view_organisations_and_fields.field_id = data.field_id
			JOIN field ON field.id = data.field_id
			WHERE can_see_field.user_id = ?
			AND data.field_id = ?
			ORDER BY date DESC
			LIMIT 1'
		);
		$stmt->bind_param('ii', $user_id, $field_id);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_data_from_past_x_days_by_field_name($user_id, $organisation_id, $field_name, $lastX) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				can_see_field.field_id as field_id
			FROM view_organisations_and_fields
			JOIN can_see_field
				ON view_organisations_and_fields.field_id = can_see_field.field_id
			WHERE can_see_field.user_id = ?
			AND view_organisations_and_fields.organisation_id = ?
			AND view_organisations_and_fields.field_name = ?'
		);
		$stmt->bind_param('iis', $user_id, $organisation_id, $field_name);
		$query_result = $this->execute_select_stmt($stmt);

		$field_id = -1;
		if($row = $query_result->fetch_assoc()) {
			$field_id = $row['field_id'];
		}

		$db->close();
		return $this->get_data_from_past_x_days_by_field_id($user_id, $field_id, $lastX);
	}

	public function get_data_from_past_x_days_by_field_id($user_id, $field_id, $lastX) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields ON view_organisations_and_fields.field_id = data.field_id
			JOIN field ON field.id = data.field_id
			WHERE can_see_field.user_id = ?
			AND data.field_id = ?
			AND date >= (date_add(curdate(), INTERVAL -? DAY))
			ORDER BY date DESC'
		);
		$stmt->bind_param('iii', $user_id, $field_id, $lastX);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_data_from_past_x_days_by_org_full_link($user_id, $nuts0, $nuts1, $nuts2, $nuts3, $org_type, $org_name, $lastX) {
		$args = func_get_args();
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN can_see_field
				ON data.field_id = can_see_field.field_id
			JOIN view_organisations_and_fields
				ON view_organisations_and_fields.field_id = data.field_id
			JOIN field
				ON field.id = data.field_id
			JOIN view_organisations_and_nuts
				ON view_organisations_and_fields.organisation_id = view_organisations_and_nuts.id
			WHERE can_see_field.user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND view_organisations_and_nuts.type = ?
			AND view_organisations_and_nuts.name = ?
			AND date >= (date_add(curdate(), INTERVAL -? DAY))
			ORDER BY date DESC'
		);
		$stmt->bind_param('issssssi', ...$args);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function insert_value_for_date($user_id, $field_id, $field_value, $date) {
		$db = $this->get_db_connection();
		$possible_fields_stmt = $db->prepare('SELECT DISTINCT field_id FROM can_insert_into_field WHERE user_id = ?');
		$possible_fields_stmt->bind_param('i', $user_id);
		$possible_fields_query_result = $this->execute_select_stmt($possible_fields_stmt);
		$possible_fields = [];
		while($row = $possible_fields_query_result->fetch_assoc()) {
			$possible_fields[] = $row['field_id'];
		}
		if(!in_array($field_id, $possible_fields)) {
			return false;
		}
		$stmt = $db->prepare(
			'INSERT into field_values (field_id, user_id, field_value, date) VALUES (?,?,?,?)'
		);
		$stmt->bind_param('iiis',$field_id, $user_id, $field_value, $date);
		$errno = $this->execute_insert_stmt($stmt);
		$db->close();
		return $errno;
	}

	public function insert_value_for_date_by_field_name($user_id, $organisation_id, $field_name, $field_value, $date) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT field_id FROM view_organisations_and_fields WHERE organisation_id = ? AND field_name = ?');
		$stmt->bind_param('is', $organisation_id, $field_name);
		$query_result = $this->execute_select_stmt($stmt);
		$field_id = -1;
		if($row = $query_result->fetch_assoc()) {
			$field_id = $row['field_id'];
		}
		$db->close();
		return $this->insert_value_for_date($user_id, $field_id, $field_value, $date);
	}


//////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// HELPER functions


	function utf8_converter($array){
		array_walk_recursive($array, function(&$item, $key){
			$item = mb_convert_encoding($item, 'UTF-8');
		});

		return $array;
	}
}

?>
