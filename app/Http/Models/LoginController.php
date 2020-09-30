<?php

require_once('AbstractController.php');

/*
* To be renamed and refactored as a model
*/
class LoginController {
    private $db_ops;

    public function __construct() {
        $this->db_ops = new DatabaseOps();
    }

    public function login($username, $password) {
        $query_result = $this->db_ops->get_login_info($username);
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
