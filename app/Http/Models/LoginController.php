<?php

require_once('AbstractController.php');

/*
* To be renamed and refactored as a model
*/
class LoginController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function login($username, $password) {
        $this->db_access->prepare('SELECT id_user, username, userpassword, salt FROM user WHERE username = ?');
        $this->db_access->bind_param('s', $username);
		$query_result = $this->db_access->execute();
        $user = $query_result->fetch_assoc();

        $password_hash = hash('sha256', $password . SALT . $user['salt']);

        if(isset($user['userpassword']) && $password_hash === $user['userpassword']) {
            $_SESSION['user_id'] = $user['id_user'];
            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        $ret = null;
        if(isset($_SESSION['user_id'])) {
            $ret = true;
        } else {
            $ret = false;
        }
        $_SESSION = array();
        session_destroy();
        return $ret;
    }
}

?>
