<?php

# PHP class
# Operations
# Data input and output to MySQL database
# and data analysis
require_once(__DIR__ . '/../../../config/config.php');

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
	public function get_db_connection() {
		return new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
	}

	public function execute_select_stmt($stmt) {
		$stmt->execute();
		$results = $stmt->get_result();
		$stmt->close();
		return $results;
	}

	public function execute_stmt_without_result($stmt) {
		$stmt->execute();
		$errno = $stmt->errno;
		$stmt->close();
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

	public function get_organisation_config($user_id, ...$args) {
		$db = $this->get_db_connection();
		switch (sizeof($args)) {
			case 0:
				$stmt = $db->prepare(
					'SELECT *
					FROM view_organisation_visible_for_user
					WHERE user_id = ?'
				);
				$parameter_types = 'i';
				break;
			case 1:
				$stmt = $db->prepare(
					'SELECT *
					FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?'
				);
				$parameter_types = 'is';
				break;
			case 2:
				$stmt = $db->prepare(
					'SELECT *
					FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?'
				);
				$parameter_types = 'iss';
				break;
			case 3:
				$stmt = $db->prepare(
					'SELECT * FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?'
				);
				$parameter_types = 'isss';
				break;
			case 4:
				$stmt = $db->prepare(
					'SELECT * FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?
					AND nuts3 = ?'
				);
				$parameter_types = 'issss';
				break;
			case 5:
				$stmt = $db->prepare(
					'SELECT * FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?
					AND nuts3 = ?
					AND organisation_type = ?'
				);
				$parameter_types = 'isssss';
				break;
			case 6:
				$stmt = $db->prepare(
					'SELECT * FROM view_organisation_visible_for_user
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?
					AND nuts3 = ?
					AND organisation_type = ?
					AND organisation_name = ?'
				);
				$parameter_types = 'issssss';
				break;
			default: // TODO: Implement fail case
				return null;
				break;
		}

		$stmt->bind_param($parameter_types, $user_id, ...$args);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
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

	public function get_field_names($user_id, ...$args) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT field_name
			FROM view_organisations_and_fields
			JOIN can_see_organisation
				ON view_organisations_and_fields.organisation_id = can_see_organisation.organisation_id
			JOIN can_see_field
				ON view_organisations_and_fields.field_id = can_see_field.field_id
				AND can_see_organisation.user_id = can_see_field.user_id
			WHERE can_see_field.user_id = ?
			AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
			AND organisation_type = ?
			AND organisation_name = ?'
		);
		$stmt->bind_param('issssss', $user_id, ...$args);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}

	/**
	 * Updates the organisation of the passed id with the new values.
	 * @param $id
	 * @param $name
	 * @param $description
	 * @param $type
	 * @param $contact
	 * @param $zipcode
	 * @param $active
	 * @return mixed
	 */
	public function update_organisation_by_id($id, $name, $description, $org_unit_id, $contact, $zipcode, $active) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('UPDATE organisation
									SET name = ?, description = ?, organisation_unit_id = ?, contact = ?, zipcode = ?, active = ?
									WHERE id_organisation = ?');
		$stmt->bind_param('ssssiii',$name, $description, $org_unit_id, $contact, $zipcode, $active, $id);
		$errno = $this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function user_can_modify_organisation($user_id, $organisation_id) { // TODO wrong plcae for this method?
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT *
			FROM can_see_organisation
			WHERE user_id = ?
			AND organisation_id = ?
			AND can_alter = 1'
		);
		$stmt->bind_param('ii', $user_id, $organisation_id);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result->num_rows > 0;
	}



	/////////////////////////////////////////
	//		fields				  		  //
	////////////////////////////////////////

	public function get_field_ids($user_id, ...$args) {
		$db = $this->get_db_connection();
		switch (sizeof($args)) {
			case 0:
				$stmt = $db->prepare(
					'SELECT field_id from can_see_field WHERE user_id = ?'
				);
				$parameter_types = 'i';
				break;
			case 6:
				$stmt = $db->prepare(
					'SELECT DISTINCT view_fields_visible_for_user.field_id
					FROM view_fields_visible_for_user
					JOIN view_organisations_and_fields
						ON view_fields_visible_for_user.field_id = view_organisations_and_fields.field_id
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?
					AND nuts3 = ?
					AND organisation_type = ?
					AND organisation_name = ?
					ORDER BY view_fields_visible_for_user.field_id'
				);
				$parameter_types = 'issssss';
				break;
			case 7:
				$stmt = $db->prepare(
					'SELECT DISTINCT view_fields_visible_for_user.field_id
					FROM view_fields_visible_for_user
					JOIN view_organisations_and_fields
						ON view_fields_visible_for_user.field_id = view_organisations_and_fields.field_id
					WHERE user_id = ?
					AND nuts0 = ?
					AND nuts1 = ?
					AND nuts2 = ?
					AND nuts3 = ?
					AND organisation_type = ?
					AND organisation_name = ?
					AND view_fields_visible_for_user.field_name = ?'
				);
				$parameter_types = 'isssssss';
				break;
			default: // TODO: implement fail case
				return null;
				break;
		}
		$stmt->bind_param($parameter_types, $user_id, ...$args);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_config_all_fields($user_id) {
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

	public function get_config_for_field_by_field_id($user_id, $field_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
				'SELECT *
				FROM view_fields_visible_for_user
				WHERE user_id = ?
				AND field_id = ?'
		);
		$stmt->bind_param('ii', $user_id, $field_id);
		$query_result = $this->execute_select_stmt($stmt);

		$db->close();
		return $query_result;
	}

	public function get_config_for_field_by_full_link($user_id, ...$args) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT field.*
			FROM view_fields_visible_for_user field
			JOIN view_organisations_and_fields orgs_and_fields
				ON field.field_id = orgs_and_fields.field_id
			WHERE user_id = ?
			AND orgs_and_fields.nuts0 = ?
			AND orgs_and_fields.nuts1 = ?
			AND orgs_and_fields.nuts2 = ?
			AND orgs_and_fields.nuts3 = ?
			AND orgs_and_fields.organisation_type = ?
			AND orgs_and_fields.organisation_name = ?
			AND field.field_name = ?'
		);
		$stmt->bind_param('isssssss', $user_id, ...$args);
		$query_result = $this->execute_select_stmt($stmt);

		$db->close();
		return $query_result;
	}



	/**
	 * Updates the field of the delivered sid if the valid_to attribute is null and set it to the current timestamp.
	 * @param $sid
	 * @return mixed
	 */
	private function update_field_valid_to_by_sid($sid) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('UPDATE field
									SET valid_to = CURRENT_TIMESTAMP
									WHERE (sid=? AND valid_to IS NULL)');
		$stmt->bind_param('s', $sid);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	/**
	 * Inserts a new field of the passed values and sets the valid_to timestamp of currently active field to now.
	 * @param $sid
	 * @param $name
	 * @param $max_value
	 * @param $yellow_value
	 * @param $red_value
	 * @param $relational_flag
	 * @return mixed|void
	 */
	public function insert_field_by_sid($sid, $name, $reference_value, $yellow_limit, $red_limit, $relational_flag) {
		$errno = $this->update_field_valid_to_by_sid($sid);
		if ($errno) {
			return; // TODO
		}
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'INSERT INTO field (sid,name,reference_value,yellow_limit,red_limit,relational_flag)
			VALUES (?,?,?,?,?,?)');
		$stmt->bind_param('ssiiii', $sid, $name, $max_value, $yellow_value, $red_value, $relational_flag);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	public function user_can_modify_field($user_id, $field_id) { // TODO wrong plcae for this method?
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT *
			FROM can_see_field
			WHERE user_id = ?
			AND field_id = ?
			AND can_alter = 1'
		);
		$stmt->bind_param('ii', $user_id, $field_id);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result->num_rows > 0;
	}






	/////////////////////////////////////////
	//		users				  	      //
	////////////////////////////////////////

	/**
	* Checks if the passed user can insert users.
	* @param $user_id
	* @return bool
	*/
	public function can_insert_user($user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT *
			FROM can_create_user
			WHERE user_id = ?'
		);
		$stmt->bind_param('i', $user_id);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result->num_rows > 0;
	}

	public function exists_user($user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT *
			FROM user
			WHERE id_user = ?');
		$stmt->bind_param('i', $user_id); // 's' specifies the variable type => 'string'
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result->num_rows > 0;
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

	public function get_user_id_by_username($username) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT id_user
			FROM user
			WHERE username=?');
		$stmt->bind_param('s', $username); // 's' specifies the variable type => 'string'
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		$user = $result->fetch_assoc();
		return count($user) > 0 ? $user['id'] : false;
	}

	//returns the error code of the insert querry. 0 if there was no error
	public function insert_new_user($username, $userpassword, $email, $realname, $salt){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('INSERT INTO user (username, userpassword, email, realname, salt) VALUES (?, ?, ?, ?, ?)');
		//$errors = $db->error_list;
		$stmt->bind_param('sssss', $username, $userpassword, $email, $realname, $salt);
		$error = $this->execute_stmt_without_result($stmt);
		$db->close();

		return $error;
	}


	public function get_user_by_name($active_user_id, $passive_user_name){
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT passive_user_id  AS \'user_id\', username, email, realname, can_alter
			FROM user JOIN can_see_user ON user.id_user = can_see_user.passive_user_id
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
			FROM user JOIN can_see_user ON user.id_user = can_see_user.passive_user_id
			WHERE active_user_id = ?
			AND user.id_user = ?');
		$stmt->bind_param('ii', $active_user_id, $passive_user_id);
		$result = $this->execute_select_stmt($stmt);
        $db->close();

    	return $result;
    }


	public function get_all_users_visible_for_user($user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT passive_user_id  AS \'user_id\', username, email, realname, can_alter
			FROM user JOIN can_see_user ON user.id_user = can_see_user.passive_user_id
			WHERE active_user_id = ?');
		$stmt->bind_param('i', $user_id);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}


	public function get_login_info($username) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT id_user, username, userpassword, salt FROM user WHERE username = ?');
		$stmt->bind_param('s', $username);
		$result = $this->execute_select_stmt($stmt);
		$db->close();
		return $result;
	}


	public function update_password($user_id, $new_password){
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'UPDATE user
			SET userpassword=?
			WHERE id_user=?'
		);
		$stmt->bind_param('si', $new_password, $user_id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}


	public function update_user_active($user_id, $active) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'UPDATE user
			SET active = ?
			WHERE id_user = ?'
		);
		$stmt->bind_param('ii', $active, $user_id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}



	public function update_user($id, $username, $email, $realname, $active, $req_pw_reset) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'UPDATE user
			SET username = ?, email = ?, realname = ?, active = ?, req_pw_reset = ?
			WHERE id_user = ?'
		);
		$stmt->bind_param('sssiii', $username, $email, $realname, $active, $req_pw_reset, $id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	// Permissions


	/**
	 * Checks if the active user is allowed to modify the passive user.
	 * @param $active_user
	 * @param $passive_user
	 * @return bool
	 */
	public function can_alter_user($active_user, $passive_user) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT can_alter
			FROM can_see_user
			WHERE active_user_id = ?
			AND passive_user_id = ?
			AND can_alter = 1'
		);
		$stmt->bind_param("ii", $active_user, $passive_user);
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result->num_rows > 0;
	}


	public function insert_into_can_insert_into_field($userID, $fieldID){
		$db= $this->get_db_connection();
		$stmt = $db->prepare('INSERT into can_insert_into_field(user_id, field_id) VALUES(?,?)');
		$stmt->bind_param('ii', $userID, $fieldID);
		$errno =$this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
    }


	public function insert_can_create($permission_table, $user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
				"INSERT INTO $permission_table
				(user_id)
				VALUES(?)"
		);
		$stmt->bind_param('i', $user_id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	public function delete_can_create($permission_table, $user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			"DELETE FROM $permission_table
			WHERE user_id=?"
		);
		$stmt->bind_param('i', $user_id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	public function insert_can_see_user($active_user_id, $passive_user_id, $can_alter) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'INSERT INTO can_see_user
				(active_user_id, passive_user_id, can_alter)
				VALUES(?,?,?)'
		);
		$stmt->bind_param('iii', $active_user_id, $passive_user_id, $can_alter);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	public function update_can_see_user($active_user_id, $passive_user_id, $can_alter) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'UPDATE can_see_user
				SET active_user_id=?, passive_user_id=?, can_alter=?
				VALUES(?,?,?)'
		);
		$stmt->bind_param('iii', $active_user_id, $passive_user_id, $can_alter);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}

	public function delete_can_see_user($active_user_id) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'DELETE FROM can_see_user
			WHERE active_user_id=?'
		);
		$stmt->bind_param('i', $active_user_id);
		$errno = $this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;
	}



	public function delete_from_can_insert_into_field($userID){
		$db= $this->get_db_connection();
		$stmt = $db->prepare('DELETE FROM can_insert_into_field
											WHERE can_insert_into_field.user_id = ?');
		$stmt->bind_param('i', $userID);
		$errno =$this->execute_stmt_without_result($stmt);
		$db->close();
		return $errno;

	}

	public function insert_into_can_see_field($userID, $fieldID, $canAlter){
		$db=$this->get_db_connection();
		$stmt = $db->prepare('INSERT INTO can_see_field(user_id, field_id, can_alter) VALUES (?, ?, ?)');
		$stmt->bind_param('iii', $userID, $fieldID, $canAlter);
		$errno = $this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function delete_from_can_see_field($userID){
		$db=$this->get_db_connection();
		$stmt = $db ->prepare('DELETE FROM can_see_field WHERE user_id = ?');
		$stmt->bind_param('i',$userID);
		$errno = $this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function update_can_see_field($userID, $fieldID, $canAlter){
		$db=$this->get_db_connection();
		$stmt = $db ->prepare('UPDATE can_see_field
										SET can_alter=?
										WHERE user_id=? AND field_id=?');
		$stmt->bind_param('iii', $canAlter,$userID, $fieldID);
		$errno= $this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function insert_into_can_see_organisation($userID, $orgID, $priority, $canAlter){
		$db= $this->get_db_connection();
		$stmt = $db -> prepare(' INSERT INTO can_see_organisation (user_id, organisation_id, priority, can_alter)
 										VALUES(?,?,?,?)');
		$stmt->bind_param('iiii', $userID, $orgID,$priority, $canAlter);
		$errno=$this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function delete_from_can_see_organisation($userID){
		$db= $this->get_db_connection();
		$stmt = $db->prepare('DELETE FROM can_see_organisation
										WHERE user_id=?');
		$stmt->bind_param('i', $userID);
		$errno =$this->execute_stmt_without_result($stmt);
		return $errno;
	}

	public function update_can_see_organisation($userID, $orgID, $priority, $canAlter){
		$db= $this -> get_db_connection();
		$stmt = $db ->prepare('UPDATE can_see_organisation
										SET priority = ?, can_alter=?
										WHERE user_id = ? AND organisation_id = ?');
		$stmt ->bind_param('iiii', $priority, $canAlter, $userID, $orgID);
		$errno =$this-> execute_stmt_without_result($stmt);
		return $errno;

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


	############################################################################################
    //   _______       ___   .___________.    ___
    //  |       \     /   \  |           |   /   \
    //  |  .--.  |   /  ^  \ `---|  |----`  /  ^  \
    //  |  |  |  |  /  /_\  \    |  |      /  /_\  \
    //  |  '--'  | /  _____  \   |  |     /  _____  \
    //  |_______/ /__/     \__\  |__|    /__/     \__\
    //
    #############################################################################################

	public function get_data_by_field_id($user_id, $field_id, $last='latest') {
		$db = $this->get_db_connection();
		if($last === 'latest') {
			$stmt = $db->prepare(
				'SELECT
					data.field_id as field_id,
					field.field_name as field_name,
					field_value,
					realname,
					date
				FROM view_up_to_date_data_from_all_fields data
				JOIN view_fields_visible_for_user field
					ON data.field_id = field.field_id
				WHERE user_id = ?
				AND data.field_id = ?
				ORDER BY date DESC
				LIMIT 1'
			);
			$stmt->bind_param('ii', $user_id, $field_id);
		} else if ($last === 'all') {
			$stmt = $db->prepare(
				'SELECT
					data.field_id as field_id,
					field.field_name as field_name,
					field_value,
					realname,
					date
				FROM view_up_to_date_data_from_all_fields data
				JOIN view_fields_visible_for_user field
					ON data.field_id = field.field_id
				WHERE user_id = ?
				AND data.field_id = ?
				ORDER BY date DESC'
			);
			$stmt->bind_param('ii', $user_id, $field_id);
		} else if (is_numeric($last)) {
			$stmt = $db->prepare(
				'SELECT
					data.field_id as field_id,
					field.field_name as field_name,
					field_value,
					realname,
					date
				FROM view_up_to_date_data_from_all_fields data
				JOIN view_fields_visible_for_user field
					ON data.field_id = field.field_id
				WHERE user_id = ?
				AND data.field_id = ?
				AND date >= (date_add(curdate(), INTERVAL -? DAY))
				ORDER BY date DESC'
			);
			$stmt->bind_param('iii', $user_id, $field_id, $last);
		}
		$query_result = $this->execute_select_stmt($stmt);
		$db->close();
		return $query_result;
	}

	public function get_data_field_id_year($user_id, $field_id, $year) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.field_name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN view_fields_visible_for_user field
				ON data.field_id = field.field_id
			WHERE field.user_id = ?
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
				field.field_name as field_name,
				field_value,
				realname,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN view_fields_visible_for_user field
				ON data.field_id = field.field_id
			WHERE field.user_id = ?
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

	public function get_data_field_id_date($user_id, $field_id, $date) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare(
			'SELECT
				data.field_id as field_id,
				field.field_name as field_name,
				field_value,
				date
			FROM view_up_to_date_data_from_all_fields data
			JOIN view_fields_visible_for_user field
				ON data.field_id = field.field_id
			WHERE field.user_id = ?
			AND data.field_id = ?
			AND date = ?'
		);
		$stmt->bind_param('iis', $user_id, $field_id, $date);

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
		$errno = $this->execute_stmt_without_result($stmt);
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
