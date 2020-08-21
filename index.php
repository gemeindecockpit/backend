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

//TODO sent '400 Bad Request' back if post was not used
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login')
{
	//TODO muss man hier eventuell die post sanitizen? 
	if(isset($_POST['name']) && isset($_POST['pass'])){
		$user->register($_POST['name'], $_POST['pass'], 'test@email', 'realus', '123');
		$user->login($_POST['name'],$_POST['pass']);
		echo 'you send a post for the login';
	}
}


# destroy session after logout
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'logout')
{
	session_destroy();
}

if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config' && !isset($_SESSION['userid'])) {
	if(isset($uri_info->path_vars[1]) && $uri_info->path_vars[1] == '38000' &&
			isset($uri_info->path_vars[2]) && $uri_info->path_vars[2] == 'feuerwehr') {

				if(isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'feuerwerk') {
					if(isset($uri_info->path_vars[4]) && $uri_info->path_vars[4] == 'einsatzkraefte') {
						if(isset($uri_info->path_vars[7]) && $uri_info->path_vars[7]  = '11') {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo json_encode(EINSATZKRAEFTE_2020_08_15);
						} else if (isset($uri_info->path_vars[7]) && $uri_info->path_vars[7]  = '12') {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo json_encode(EINSATZKRAEFTE_2020_08_16);
						} else {
							header('Content-type: application/json');
							$temp = new DataOutput();
							echo json_encode(DUMMY_FEUERWEHR_FELD1);
						}
					} else if (isset($uri_info->path_vars[4]) && $uri_info->path_vars[4] == 'autos%20broom%20broom') {
						header('Content-type: application/json');
						$temp = new DataOutput();
						echo json_encode(DUMMY_FEUERWEHR_FELD2);
					} else {
						header('Content-type: application/json');
						$temp = new DataOutput();
						echo json_encode(DUMMY_FEUERWEHR);
					}
				} else if (isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'wir%20machen%20nass'){
					header('Content-type: application/json');
					$temp = new DataOutput();
					echo json_encode(DUMMY_FEUERWEHR2);
				} else {
					echo 'Computer sagt nein';
				}

	} else {
		header('Content-type: application/json');
		$temp = new DataOutput();
		echo json_encode(ALLEN_ORGANISATION);
	}
} else if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'data'){
	header('Content-type: application/json');
	$temp = new DataOutput();
	echo $temp->output_as_json([]);
} else {
	if(isset($_SESSION['userid'])){
		// check if the user id is set and return a json with the links to the resources available to the user
		if(isset($_SESSION['userid'])){
			header('Content-type: application/json');
			echo 'you are logged in';
		}
	} else{
		echo 'you are not logged in ';
	}

}


?>
