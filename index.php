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
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config') {
	if(isset($uri_info->path_vars[1]) && $uri_info->path_vars[1] == '38000' &&
			isset($uri_info->path_vars[2]) && $uri_info->path_vars[2] == 'feuerwehr') {

				if(isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'feuerwerk') {
					if(isset($uri_info->path_vars[4]) && $uri_info->path_vars[4] == 'einsatzkraefte') {
						if(isset($uri_info->path_vars[7]) && $uri_info->path_vars[7]  = '11') {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo $temp->output_as_json(EINSATZKRAEFTE_2020_08_15);
						} else if (isset($uri_info->path_vars[7]) && $uri_info->path_vars[7]  = '12') {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo $temp->output_as_json(EINSATZKRAEFTE_2020_08_16);
						} else {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo $temp->output_as_json(DUMMY_FEUERWEHR_FELD1);
						}
					} else if (isset($uri_info->path_vars[4]) && $uri_info->path_vars[4] == 'autos%20broom%20broom') {
						header('Content-type: application/json');
						$temp = new DataOutput();
						echo $temp->output_as_json(DUMMY_FEUERWEHR_FELD2);
					} else {
						header('Content-type: application/json');
						$temp = new DataOutput();
						echo $temp->output_as_json(DUMMY_FEUERWEHR);
					}
				} else if (isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'wir%20machen%20nass'){
					header('Content-type: application/json');
					$temp = new DataOutput();
					echo $temp->output_as_json(DUMMY_FEUERWEHR2);
				} else {
					echo 'Computer sagt nein';
				}

	} else {
		header('Content-type: application/json');
		$temp = new DataOutput();
		echo $temp->output_as_json(ALLEN_ORGANISATION);
	}
}	else {

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
}


?>
