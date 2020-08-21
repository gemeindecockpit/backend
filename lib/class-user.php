<?php

# PHP class
# usermanagement
# connected to MySQL database
# password should put in the following order in the hash function : pass + server salt + user salt

class UserData {

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

	//TODO was passiert, wenn der user nicht in der db ist?
	public function login($username, $password)
    {
		$dataOp = new DataOperations();
  		$result = $dataOp->get_user($username);
  		$row = $result->fetch_assoc();

  		$password = hash('sha256', $password . SALT . $row['salt']);

		if($password == $row['userpassword'])
		{
			$_SESSION['userid'] = $row['id'];
			$_SESSION['username'] = $row['username'];
			
    		return true;
  		}

    	else
    		return false;
    }

	//registers a new user, returns true if the user was generated succesfully
	//user with the same username are not possible
    public function register($name, $pass, $email, $realname, $salt)
    {
		$dataOp = new DataOperations();
		$result = $dataOp->getUserCount($name);
  		$row = $result->fetch_assoc();

  		if($row['counter'] == 0)
  		{
  			$password = hash('sha256', $pass . SALT . $salt);
  			$dataOp->insertNewUser($name, $password, $email, $realname, $salt );

    		return true;
  		}

    	else
    		return false;
    }


    public function change_password() {
        $userid = $_SESSION['userid'];
		$dataOp = new DataOperations();
		
		$result = $dataOp->get_by_id($userid);
		$row = $result->fetch_assoc();
        $password = hash('sha256', $_POST['pass']. SALT . $row['salt']);
		$dataOp->change_password($userid, $password);

        return;
    }

} 


?>
