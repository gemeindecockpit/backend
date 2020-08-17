<?php

#-- config --
require_once("inc/config.php");

#-- classes --
require_once("lib/class-urisplit.php");
require_once("lib/class-user.php");


session_start();

$uri_info = new URISplit();
$user = new UserData();



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