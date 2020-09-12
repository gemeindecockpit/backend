<?php

    require_once(__DIR__ . '/../app/db.php');

    class LoginController {
        private $db_ops;

        public function __construct() {
            $this->db_ops = new DatabaseOps();
        }

        public function login($username, $password) {
          try{
            $query_result = $this->db_ops->get_login_info($username);
            $user = $query_result->fetch_assoc();
            //no user with the given params found
            if(!isset($user)){
              return false;
            }
            $password_hash = hash('sha256', $password . SALT . $user['salt']);
            if(isset($user['userpassword']) && $password_hash === $user['userpassword']) {
                $_SESSION['user_id'] = $user['id'];
                return true;
            } else {
                return false;
            }
          } catch (Exception $e){
            error_log('Exception in LoginController: ' . $e->getMessage());
            return false;
          }
        }

        public function logout() {
            $ret;
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
