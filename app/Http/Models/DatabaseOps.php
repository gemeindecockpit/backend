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


	public function get_login_info($username) {
		$db = $this->get_db_connection();
		$stmt = $db->prepare('SELECT id_user, username, userpassword, salt FROM user WHERE username = ?');
		$stmt->bind_param('s', $username);
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
