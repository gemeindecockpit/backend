<?php

# PHP class
# Operations
# Data input and output to MySQL database
# and data analysis

class DataOperations {

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
	//returns a resultset containing the userdata for $user
	public static function get_user($user) {
		$db = new mysqli($this->db_host, $this->db_user, $this->db_user_password, $this->db_name);
		$stmt = $db->prepare('SELECT * FROM user WHERE username = ?');
		$stmt->bind_param('s', $user); // 's' specifies the variable type => 'string'
		$stmt->execute();
		$db->close();
		return $stmt->get_result();
	}


}

?>