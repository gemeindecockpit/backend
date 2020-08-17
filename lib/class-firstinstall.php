<?php

# PHP class
# First installation
# contains all operations for the first 
# configuration of the MySQL database

class FirstInstall {

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


    public create_user_tbl()
    {
        #create table "user" in the MySQL database

        return;
    }


}

?>