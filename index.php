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
		//$user->register($_POST['name'], $_POST['pass'], 'test@email', 'realus', '123');
		$user->login($_POST['name'],$_POST['pass']);
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
} else if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'data' && !isset($_SESSION['userid'])) {
	header('Content-type: application/json');
	$temp = new DataOutput();
	echo $temp->output_as_json([]);
} else {
	
	// check if the user id is set and return a json with the links to the resources available to the user
	if(isset($_SESSION['userid'])){
		switch (sizeof($uri_info->path_vars)){
			case 1:
			//this is the case when the base domain is called AND when one path_vars is set
				switch ($uri_info->path_vars[0]) {
					case 'config':
					
						header('Content-type: application/json');
						//TODO output all plz the user has access to
						break;
						
					case 'data':
					header('Content-type: application/json');
					//TODO output all plz the user has access to
						break;
						
					case ''; //domain only
					
						header('Content-type: application/json');
						$data_out->add_keyvalue_to_links_array('config', $data_out->get_current_self_link() . 'config/' );
						$data_out->add_keyvalue_to_links_array('data', $data_out->get_current_self_link() . 'data/' );
						echo $data_out->output_as_json([]);
						break;
						
					default:
					
						header("HTTP/1.0 404 Not Found");
						break;
						
				}
				break;
				
			case 2:
				break;
				
			case 3:
				break;
				
			case 4:
				break;
				
			default:
				header("HTTP/1.0 404 Not Found");
				break;
			
		}
	} else {
		echo 'you are not logged in';
	}

}


?>
