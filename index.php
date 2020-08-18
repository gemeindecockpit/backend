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
/*
# TODO login aufrufen
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login') 
{
	session_destroy();
}
*/
/*
# destroy session after logout
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'logout') 
{
	session_destroy();
}
*/
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config' && 
	isset($uri_info->path_vars[1]) && $uri_info->path_vars[1] == '38000' && 
	isset($uri_info->path_vars[2]) && $uri_info->path_vars[2] == 'feuerwehr' &&
	isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'feuerwerk' &&
	isset($uri_info->path_vars[4]) && $uri_info->path_vars[4] == 'einsatzkraefte') 
{
	header('Content-type: application/json');
	$temp = new DataOutput();
	echo $temp->output_as_json(DUMMY_FEUERWEHR_FELD1);
	
	
	
} else if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config' && 
	isset($uri_info->path_vars[1]) && $uri_info->path_vars[1] == '38000' && 
	isset($uri_info->path_vars[2]) && $uri_info->path_vars[2] == 'feuerwehr' &&
	isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'feuerwerk') {
	header('Content-type: application/json');
	$temp = new DataOutput();
	echo $temp->output_as_json(DUMMY_FEUERWEHR);
	
	
	
	
}


	if(isset($_SESSION['userid']))
	{
		echo '<br><span> your are logged in</span>';
		
	}

	else
	{
		if(isset($_POST['submit']) && $_POST['submit'] == "login")
		{
			if($user->login()){
				
			} else{
				
			}
				
		}
		else
		{
			//Debug: create a user to test the login and register function
			echo '<br><span> you are not logged in but a User with the username : testus and the pw: testtest has been created for you</span>' . $user->register('testus', 'testtest', 'test@email.com', 'realname', 'wiesoisthiereinsalt');	
		}
	}



?>
