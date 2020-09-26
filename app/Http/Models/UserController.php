<?php

require_once("AbstractController.php");


/*
* To be renamed and refactored as a model
*/
class UserController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function get_all_by_id($user_ids) {
        $users = [];
        foreach ($user_ids as $user_id) {
            array_push($users, $this->get_user_with_permissions_by_id($user_id));
        }
        return $users;
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
    public function create_new_user($session_user_id, $username, $email, $realname, $userpassword, $permissions) {
        if (!$this->db_ops->can_insert_user($session_user_id)) {
            return ResponseCodes::FORBIDDEN;
        }

        $password_hash = hash('sha256', $userpassword . SALT . 'salty');
        $errno = $this->db_ops->insert_new_user($username, $password_hash, $email, $realname, 'salty'); // TODO salt value

        if ($errno)
            return true;

        $user_id = $this->db_ops->get_user_id_by_username($username);

        if (!$user_id)
            return true;

        $this->insert_permissions($user_id, $permissions);

        return false;
    }

    public function get_user_by_id($user_id) {
        $db_access = new DatabaseAccess();
        $db_access->prepare(
            'SELECT id_user, username, email, realname, active, req_pw_reset
            FROM user 
            WHERE id_user = ?'
        );
        $db_access->bind_param('i', $user_id);
        $query_result = $db_access->execute();
        $db_access->close();
        return $this->format_query_result($query_result)[0];
    }

    public function get_permissions_by_id($user_id) {
        $permissions['can_create_field'] = ($this->can_create_field($user_id) ? 1 : 0);
        $permissions['can_create_organisation'] = ($this->can_create_organisation($user_id) ? 1 : 0);
        $permissions['can_create_user'] = ($this->can_create_user($user_id) ? 1 : 0);
        $permissions['can_insert_into_field'] = $this->get_can_insert_into_field($user_id);
        $permissions['can_see_field'] = $this->get_can_see_field($user_id);
        $permissions['can_see_user'] = $this->get_can_see_user($user_id);
        $permissions['can_see_organisation'] = $this->get_can_see_organisation($user_id);
        return $permissions;
    }

    public function get_user_with_permissions_by_id($user_id) {
        $full_user = $this->get_user_by_id($user_id);
        $full_user['permissions'] = $this->get_permissions_by_id($user_id);
        return $full_user;
    }

    public function set_user_inactive($session_user_id, $user_id) {
        if (!$this->db_ops->exists_user($user_id)) {
            return ResponseCodes::NOT_FOUND;
        } else if (!$this->db_ops->can_alter_user($session_user_id, $user_id)) {
            return ResponseCodes::FORBIDDEN;
        }

        return $this->db_ops->update_user_active($user_id, 0);

    }

    public function modify_user($session_user_id, $user_id, $username, $email, $realname, $active, $req_pw_reset, $permissions) {
        if (!$this->db_ops->exists_user($user_id)) {
            return ResponseCodes::NOT_FOUND;
        } else if (!$this->db_ops->can_alter_user($session_user_id, $user_id)) {
            return ResponseCodes::FORBIDDEN;
        }

        $this->db_ops->update_user($user_id, $username, $email, $realname, $active, $req_pw_reset);
        $this->delete_permissions($user_id);
        $this->insert_permissions($user_id, $permissions);

        return false;
    }

    public function update_password($user_id, $new_password, $salt) {
        $password_hash = hash('sha256', $new_password . SALT . $salt);
        return $this->db_ops->update_password($user_id, $password_hash);
    }

    /**
     * Revokes all rights of user with $user_id.
     * @param $user_id
     */
    private function delete_permissions($user_id) {

        $this->db_ops->delete_can_create('can_create_user', $user_id);

        $this->db_ops->delete_can_create('can_create_organisation', $user_id);

        $this->db_ops->delete_can_create('can_create_field', $user_id);

        $this->db_ops->delete_from_can_insert_into_field($user_id);

        $this->db_ops->delete_from_can_see_organisation($user_id);

        $this->db_ops->delete_from_can_see_field($user_id);

        $this->db_ops->delete_can_see_user($user_id);

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
                case 'can_create_field':
                case 'can_create_user':
                    if ($perm_val)
                        $this->db_ops->insert_can_create($perm, $user_id);
                    break;
                case 'can_see_field':
                    foreach ($perm_val as $rec) {
                        $this->db_ops->insert_into_can_see_field($user_id, $rec['field_id'], $rec['can_alter']);
                    }
                    break;
                case 'can_see_organisation':
                    foreach ($perm_val as $rec) {
                        $this->db_ops->insert_into_can_see_organisation($user_id, $rec['organisation_id'], $rec['priority'], $rec['can_alter']);
                    }
                    break;
                case 'can_insert_into_field':
                    foreach ($perm_val as $value) {
                        $this->db_ops->insert_into_can_insert_into_field($user_id, $value);
                    }
                    break;
                case 'can_see_user':
                    foreach ($perm_val as $rec) {
                        $this->db_ops->insert_can_see_user($user_id, $rec['passive_user_id'], $rec['can_alter']);
                    }
            }
        }
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
           $stmt_string = 'SELECT * FROM can_see_organisation WHERE user_id = ? AND organisation_id=? AND can_alter=1 ';
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


    public function can_create_field($user_id) {
        $stmt_string = 'SELECT * FROM can_create_field WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_create_organisation($user_id) {
        $stmt_string = 'SELECT * FROM can_create_organisation WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    private function can_create_user($user_id) {
        $stmt_string = 'SELECT * FROM can_create_user WHERE user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function can_see_user($user_id) {
        $stmt_string = 'SELECT * FROM can_see_user WHERE passive_user_id = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    public function exists_user($user_id) {
        $stmt_string = 'SELECT * FROM user WHERE id_user = ?';
        return $this->exists_entry($stmt_string, 'i', $user_id);
    }

    private function exists_entry($stmt_string, $param_string, ...$params) {
        $db_access = new DatabaseAccess();
        $db_access->prepare($stmt_string);
        $db_access->bind_param($param_string, ...$params);
        $query_result = $db_access->execute();
        $db_access->close();
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
        $db_access = new DatabaseAccess();
        $db_access->prepare($stmt_string);
        $db_access->bind_param($param_string, ...$params);
        $query_result = $db_access->execute();
        $db_access->close();
        return $query_result;
    }

    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = []) {}
}

?>
