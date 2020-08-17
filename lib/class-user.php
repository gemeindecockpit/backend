<?php

# PHP class
# usermanagement
# connected to MySQL database

class UserData {

    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_user_password;

    protected $salt;



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

        if(defined('SALT'))
            $this->salt = SALT;
        

        return;
    }


	public function login($username, $password)
    {
		$dataOp = new DataOperations();
  		$result = $dataOp->get_user($username);
  		$row = $result->fetch_assoc();

  		$password = hash('sha512', $password . $this->salt);

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
    public function register($name, $pass)
    {
		$dataOp = new DataOperations();
		$result = dataOp->getUserCount($name);
  		$row = $result->fetch_assoc();


  		if($row['counter'] == 0)
  		{
  			$password = hash('sha512', $pass . $this->salt);

  			$dataOp->insertNewUser($name, $password );

    		return true;
  		}

    	else
    		return false;
    }


    public function change_password()
    {
        $userid = $_SESSION['userid'];
        $password = hash('sha512', $_POST['pass'].$this->salt);

        $db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        $query = 'UPDATE user SET userpassword = "'.$password.'" WHERE id = "'.$userid.'"';
        $db->query($query);
        $db->close();

        return;
    }


    public function get_by_id($id)
    {
        #returns userdata by an given userid

        $db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        $query = 'SELECT username, email, realname FROM user WHERE id= "'.$id.'"';
        $result = $db->query($query);
        $user_data = $result->fetch_assoc();
        $db->close();

    	return $user_data;
    }

} 


?>
