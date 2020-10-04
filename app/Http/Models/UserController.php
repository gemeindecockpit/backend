<?php

require_once("AbstractController.php");


/*
* To be renamed and refactored as a model
*/
class UserController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Inserts a new user if the operating user is allowed to do it.
     * @param $session_user_id
     * @param $username
     * @param $email
     * @param $realname
     * @param $userpassword
     * @return mixed|string
     */
    public function create_new_user($username, $email, $realname, $userpassword, $permissions) {
        $password_hash = hash('sha256', $userpassword . SALT . 'salty');
        $errno = $this->insert_into_user($username, $password_hash, $email, $realname, 'salty'); // TODO salt value

        if ($errno)
            return true;

        $user_id = $this->get_user_id_by_username($username);

        if (!$user_id)
            return true;

        $this->insert_permissions($user_id, $permissions);

        return false;
    }

    public function get_user_by_id($user_id) {
        $this->db_access->prepare(
            'SELECT id_user, username, email, realname, active, req_pw_reset
            FROM user
            WHERE id_user = ?'
        );
        $this->db_access->bind_param('i', $user_id);
        $query_result = $this->db_access->execute();
        return $this->format_query_result($query_result)[0];
    }

    public function get_user_id_by_username($username) {
        $this->db_access->prepare(
            'SELECT id_user
            FROM user
            WHERE username=?'
        );
        $this->db_access->bind_param('s', $username);
        $query_result = $this->db_access->execute();
        return $this->format_query_result($query_result)[0]['id_user'];
    }

    public function get_permissions_by_id($user_id) {
        $permissions['can_create_field'] = ($this->can_create_field($user_id) ? 1 : 0);
        $permissions['can_create_organisation'] = ($this->can_create_organisation($user_id) ? 1 : 0);
        $permissions['can_create_user'] = ($this->can_create_user($user_id) ? 1 : 0);
        $permissions['can_create_organisation_type'] = ($this->delete_from_can_create_organisation_type($user_id) ? 1 : 0);
        $permissions['can_create_organisation_group'] = ($this->delete_from_can_create_organisation_group($user_id) ? 1 : 0);
        $permissions['can_insert_into_field'] = $this->get_can_insert_into_field($user_id);
        $permissions['can_see_field'] = $this->get_can_see_field($user_id);
        $permissions['can_see_user'] = $this->get_can_see_user($user_id);
        $permissions['can_see_organisation'] = $this->get_can_see_organisation($user_id);
        return $permissions;
    }

    public function insert_into_user($username, $userpassword, $email, $realname, $salt) {
        $this->db_access->prepare(
            'INSERT INTO user (username, userpassword, email, realname, salt) 
            VALUES (?, ?, ?, ?, ?)'
        );
        $this->db_access->bind_param('sssss', $username, $userpassword, $email, $realname, $salt);
        return $this->db_access->execute();
    }

    public function update_user($id, $username, $email, $realname, $active, $req_pw_reset) {
        $this->db_access->prepare(
            'UPDATE user
			SET username = ?, email = ?, realname = ?, active = ?, req_pw_reset = ?
			WHERE id_user = ?'
        );
        $this->db_access->bind_param('sssiii', $username, $email, $realname, $active, $req_pw_reset, $id);
        return $this->db_access->execute();
    }

    public function update_user_active($user_id, $active) {
        $this->db_access->prepare(
            'UPDATE user
			SET active = ?
			WHERE id_user = ?'
        );
        $this->db_access->bind_param('ii', $active, $user_id);
        return $this->db_access->execute();
    }

    public function update_user_password($user_id, $userpassword) {
        $this->db_access->prepare(
            'UPDATE user
			SET userpassword = ?
			WHERE id_user = ?'
        );
        $this->db_access->bind_param('si', $userpassword, $user_id);
        return $this->db_access->execute();
    }

    public function modify_user($session_user_id, $user_id, $username, $email, $realname, $active, $req_pw_reset, $permissions) {

        $this->update_user($user_id, $username, $email, $realname, $active, $req_pw_reset);
        $this->delete_permissions($user_id);
        $this->insert_permissions($user_id, $permissions);

        return false;
    }

    public function update_password($user_id, $new_password, $salt) {
        $password_hash = hash('sha256', $new_password . SALT . $salt);
        return $this->update_user_password($user_id, $password_hash);
    }

    /**
     * Revokes all rights of user with $user_id.
     * @param $user_id
     */
    private function delete_permissions($user_id) {

        $this->delete_from_can_create_organisation($user_id);

        $this->delete_from_can_create_field($user_id);

        $this->delete_from_can_create_user($user_id);

        $this->delete_from_can_insert_into_field($user_id);

        $this->delete_from_can_see_organisation($user_id);

        $this->delete_from_can_see_field($user_id);

        $this->delete_from_can_see_user($user_id);

        $this->delete_from_can_create_organisation_type($user_id);

        $this->delete_from_can_create_organisation_group($user_id);

    }

    /**
     * Grand all $permissions to user with $user_id.
     * @param $user_id
     * @param $permissions
     */
    public function insert_permissions($user_id, $permissions) {
        foreach ($permissions as $perm => $perm_val) {
            switch ($perm) {
                case 'can_create_organisation':
                    if ($perm_val)
                        $this->insert_into_can_create_organisation($user_id);
                    break;
                case 'can_create_field':
                    if ($perm_val)
                        $this->insert_into_can_create_field($user_id);
                    break;
                case 'can_create_user':
                    if ($perm_val)
                        $this->insert_into_can_create_user($user_id);
                    break;
                case 'can_create_organisation_type':
                    if ($perm_val)
                        $this->insert_into_can_create_organisation_type($user_id);
                    break;
                case 'can_create_organisation_group':
                    if ($perm_val)
                        $this->insert_into_can_create_organisation_group($user_id);
                    break;
                case 'can_see_field':
                    foreach ($perm_val as $rec) {
                        $this->insert_into_can_see_field($user_id, $rec['field_id'], $rec['can_alter']);
                    }
                    break;
                case 'can_see_organisation':
                    foreach ($perm_val as $rec) {
                        $this->insert_into_can_see_organisation($user_id, $rec['organisation_id'], $rec['priority'], $rec['can_alter']);
                    }
                    break;
                case 'can_insert_into_field':
                    foreach ($perm_val as $value) {
                        $this->insert_into_can_insert_into_field($user_id, $value);
                    }
                    break;
                case 'can_see_user':
                    foreach ($perm_val as $rec) {
                        $this->insert_into_can_see_user($user_id, $rec['passive_user_id'], $rec['can_alter']);
                    }
            }
        }
    }

    private function insert_into_can_create_type($type_table, $user_id) {
        $this->db_access->prepare(
            "INSERT INTO $type_table
				(user_id)
				VALUES(?)"
        );
        $this->db_access->bind_param('i', $user_id);
        return $this->db_access->execute();
    }

    private function delete_from_table_where_user_id($table, $user_id) {
        $this->db_access->prepare(
            "DELETE FROM $table
            WHERE user_id=?"
        );
        $this->db_access->bind_param('i', $user_id);
        return $this->db_access->execute();
    }

    private function insert_into_can_create_field($user_id) {
        return $this->insert_into_can_create_type('can_create_field', $user_id);
    }

    private function delete_from_can_create_field($user_id) {
        return $this->delete_from_table_where_user_id('can_create_field', $user_id);
    }

    private function insert_into_can_create_user($user_id) {
        return $this->insert_into_can_create_type('can_create_user', $user_id);
    }

    private function delete_from_can_create_user($user_id) {
        return $this->delete_from_table_where_user_id('can_create_user', $user_id);
    }

    private function insert_into_can_create_organisation($user_id) {
        return $this->insert_into_can_create_type('can_create_organisation', $user_id);
    }

    private function delete_from_can_create_organisation($user_id) {
        return $this->delete_from_table_where_user_id('can_create_organisation', $user_id);
    }

    private function insert_into_can_create_organisation_type($user_id) {
        return $this->insert_into_can_create_type('can_create_organisation_type', $user_id);
    }

    private function delete_from_can_create_organisation_type($user_id) {
        return $this->delete_from_table_where_user_id('can_create_organisation_type', $user_id);
    }

    private function insert_into_can_create_organisation_group($user_id) {
        return $this->insert_into_can_create_type('can_create_organisation_group', $user_id);
    }

    private function delete_from_can_create_organisation_group($user_id) {
        return $this->delete_from_table_where_user_id('can_create_organisation_group', $user_id);
    }

    private function insert_into_can_insert_into_field($user_id, $field_id) {
        $this->db_access->prepare(
            'INSERT into can_insert_into_field(user_id, field_id) 
            VALUES(?,?)'
        );
        $this->db_access->bind_param('ii', $user_id, $field_id);
        return $this->db_access->execute();
    }

    private function delete_from_can_insert_into_field($user_id) {
        return $this->delete_from_table_where_user_id('can_insert_into_field', $user_id);
    }

    public function insert_into_can_see_user($active_user, $passive_user, $can_alter) {
        $this->db_access->prepare(
            'INSERT INTO can_see_user(active_user_id, passive_user_id, can_alter)
				VALUES(?,?,?)'
        );
        $this->db_access->bind_param('iii', $active_user, $passive_user, $can_alter);
        return $this->db_access->execute();
    }

    private function delete_from_can_see_user($user_id) {
        $this->db_access->prepare(
            'DELETE FROM can_see_user
            WHERE active_user_id=?'
        );
        $this->db_access->bind_param('i', $user_id);
        return $this->db_access->execute();
    }

    private function insert_into_can_see_field($user_id, $field_id, $can_alter) {
        $this->db_access->prepare(
            'INSERT INTO can_see_field(user_id, field_id, can_alter) 
            VALUES (?, ?, ?)'
        );
        $this->db_access->bind_param('iii', $user_id, $field_id, $can_alter);
        return $this->db_access->execute();
    }

    private function delete_from_can_see_field($user_id) {
        return $this->delete_from_table_where_user_id('can_see_field', $user_id);
    }

    private function insert_into_can_see_organisation($user_id, $org_id, $priority, $can_alter) {
        $this->db_access->prepare(
            'INSERT INTO can_see_organisation(user_id, organisation_id, priority, can_alter)
 			VALUES(?,?,?,?)'
        );
        $this->db_access->bind_param('iiii', $user_id, $org_id, $priority, $can_alter);
        return $this->db_access->execute();
    }

    private function delete_from_can_see_organisation($user_id) {
        return $this->delete_from_table_where_user_id('can_see_organisation', $user_id);
    }

    public function can_see_group($user_id, $group_id) {
        $stmt_string =
            'SELECT *
            FROM organisation
            JOIN can_see_organisation
                ON organisation.id_organisation = can_see_organisation.organisation_id
            JOIN organisation_group
                ON organisation.organisation_group_id = organisation_group.id_organisation_group
            WHERE can_see_organisation.user_id = ?
            AND organisation_group.id_organisation_group = ?
        ';
        return $this->exists_entry($stmt_string, 'ii', $user_id, $group_id);
    }

    public function can_see_type($user_id, $type_id) {
        $stmt_string =
            'SELECT *
            FROM organisation
            JOIN can_see_organisation
                ON organisation.id_organisation = can_see_organisation.organisation_id
            JOIN organisation_type
                ON organisation.organisation_type_id = organisation_type.id_organisation_type
            WHERE can_see_organisation.user_id = ?
            AND organisation_type.id_organisation_type = ?
        ';
        return $this->exists_entry($stmt_string, 'ii', $user_id, $type_id);
    }


    public function can_see_organisation($user_id, $organisation_id){
           $stmt_string = 'SELECT * FROM can_see_organisation WHERE user_id = ? AND organisation_id=?';
           return $this->exists_entry($stmt_string, 'ii', $user_id, $organisation_id);
    }

    public function can_alter_organisation($user_id, $organisation_id){
           $stmt_string = 'SELECT * FROM can_see_organisation WHERE user_id = ? AND organisation_id=? AND can_alter=1';
           return $this->exists_entry($stmt_string, 'ii', $user_id, $organisation_id);
    }

    public function can_see_field($user_id, $field_id) {
        $stmt_string = 'SELECT * FROM can_see_field WHERE user_id = ? AND field_id=?';
        return $this->exists_entry($stmt_string, 'ii', $user_id, $field_id);
    }

    public function can_alter_field($user_id, $field_id){
       $stmt_string = 'SELECT * FROM can_see_field WHERE user_id = ? AND field_id=? AND can_alter=1 ';
       return $this->exists_entry($stmt_string, 'ii', $user_id, $field_id);
    }

    public function can_insert_into_field($user_id, $field_id) {
        $stmt_string = 'SELECT * FROM can_insert_into_field WHERE user_id = ? AND field_id=?';
        return $this->exists_entry($stmt_string, 'ii', $user_id, $field_id);
    }

    public function can_alter_user($active_user_id, $passive_user_id) {
        $stmt_string = 'SELECT * 
                        FROM can_see_user
                        WHERE active_user_id=?
                        AND passive_user_id=?
                        AND can_alter=1';
        return $this->exists_entry($stmt_string, 'ii', $active_user_id, $passive_user_id);
    }

    public function can_create_field($user_id) {
        $stmt_string = 'SELECT * FROM can_create_field WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_create_organisation($user_id) {
        $stmt_string = 'SELECT * FROM can_create_organisation WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_create_organisation_type($user_id) {
        $stmt_string = 'SELECT * FROM can_create_organisation_type WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_create_organisation_group($user_id) {
        $stmt_string = 'SELECT * FROM can_create_organisation_group WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_create_user($user_id) {
        $stmt_string = 'SELECT * FROM can_create_user WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_see_user($active_user_id, $passive_user_id) {
        $stmt_string = 'SELECT * FROM can_see_user WHERE active_user_id=? AND passive_user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $active_user_id, $passive_user_id);
    }

    public function exists_user_for_id($user_id) {
        $stmt_string = 'SELECT * FROM user WHERE id_user = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function exists_user_for_username($username) {
        $stmt_string = 'SELECT * FROM user WHERE username = ?';
        return $this->exists_entry($stmt_string, 's', $username);
    }

    private function exists_entry($stmt_string, $param_string, ...$params) {
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, ...$params);
        $query_result = $this->db_access->execute();
        return $query_result->num_rows > 0;
    }

    private function get_can_insert_into_field($user_id) {
        $stmt_string = 'SELECT field_id FROM can_insert_into_field WHERE user_id = ?';
        return $this->format_query_result_to_indexed_array($this->get_permissions($stmt_string, 'i', $user_id), true);
    }

    private function get_can_see_field($user_id) {
        $stmt_string = 'SELECT field_id, can_alter FROM can_see_field WHERE user_id = ?';
        return $this->format_query_result($this->get_permissions($stmt_string, 'i', $user_id));
    }

    private function get_can_see_user($user_id) {
        $stmt_string = 'SELECT passive_user_id, can_alter FROM can_see_user WHERE active_user_id = ?';
        return $this->format_query_result($this->get_permissions($stmt_string, 'i', $user_id));
    }

    public function get_can_see_user_ids($user_id) {
        $stmt_string = 'SELECT passive_user_id FROM can_see_user WHERE active_user_id = ?';
        return $this->format_query_result_to_indexed_array($this->get_permissions($stmt_string, 'i', $user_id));
    }

    private function get_can_see_organisation($user_id) {
        $stmt_string = 'SELECT organisation_id, can_alter, priority FROM can_see_organisation WHERE user_id = ?';
        return $this->format_query_result($this->get_permissions($stmt_string, 'i', $user_id));
    }

    private function get_permissions($stmt_string, $param_string, ...$params) {
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, ...$params);
        $query_result = $this->db_access->execute();
        return $query_result;
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {}
}

?>
