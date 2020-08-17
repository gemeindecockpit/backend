<?php

#-- config --
require_once("inc/config.php");

#-- classes --
require_once("lib/class-urisplit.php");
require_once("lib/class-user.php");
require_once("lib/class-dataoutput.php");
require_once("lib/class-dataoperations.php");


#Informations about servers and methods
$my_hostname = $_SERVER['HTTP_HOST'];
$my_uri = $_SERVER['REQUEST_URI'];
$my_method = $_SERVER['REQUEST_METHOD'];


session_start();

$uri_info = new URISplit();
$user = new UserData();
$data_operation = new DataOperations();
$data_out = new DataOutput();



# destroy session after logout
if($uri_info->path_vars[0] == 'logout') 
{
	session_destroy();
}


else
{
	if(isset($_SESSION['userid']))
	{
		
		
	}

	else
	{
		if($_POST['submit'] == "login")
		{
			if($user->login())
				
			else
				
		}
		else
		{
				
		}
	}
}


?>
