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


	public function login()
    {
    	$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        $query = 'SELECT * FROM user WHERE username= "'.$_POST['name'].'"';
  		$result = $db->query($query);
  		$row = $result->fetch_assoc();
  		$db->close();

  		$pass = hash('sha512', $_POST['pass'].$this->salt);

		if($pass == $row['userpassword'])
		{
			$_SESSION['userid'] = $row['id'];
			$_SESSION['username'] = $row['username'];
			
    		return true;
  		}

    	else
    		return false;
    }


    public function register()
    {
    	$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        $query = 'SELECT COUNT(*) as counter FROM user WHERE username= "'.$_POST['name'].'"';
  		$result = $db->query($query);
  		$row = $result->fetch_assoc();
  		$db->close();

  		if($row['counter'] == 0 && !empty($_POST['pass']))
  		{
  			$pass = hash('sha512', $_POST['pass'].$this->salt);

  			$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
        	$query = 'INSERT INTO user (username, userpassword) VALUES ("'.$_POST['name'].'","'.$pass.'")';
  			$db->query($query);
  			$db->close();

    		return true;
  		}

    	else
    		return false;
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
