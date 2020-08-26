<?php

#-- Todo liste --
#TODO: cors policy für alle freigeben

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
		//root domain
		if(empty($uri_info->path_vars[0])){
			header('Content-type: application/json');
			$data_out->add_keyvalue_to_links_array('config', $data_out->get_current_self_link() . 'config' );
			$data_out->add_keyvalue_to_links_array('data', $data_out->get_current_self_link() . 'data' );
			echo $data_out->output_as_json([]);
		} else {
			if(sizeof($uri_info->path_vars) == 1 && isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config'){		 //config path
				//output all plz the user has access to
				header('Content-type: application/json');
				$data_out->add_keyvalue_to_links_array('config', $data_out->get_current_self_link() . '/' .PLZ );
				echo $data_out->output_as_json([]);
				//#TODO: output all plz the user has access to
			} else if (sizeof($uri_info->path_vars) > 1 && isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config') {
								switch (sizeof($uri_info->path_vars)){
					case 2:
						if($uri_info->path_vars[1] == PLZ){
							echo_json_all_types_for_user();
						} else {
							header("HTTP/1.0 404 Not Found");
						}
						break;
					case 3:
						if($uri_info->path_vars[1] == PLZ && in_array($uri_info->path_vars[2], get_user_types())){
							echo_json_all_orgs_for_user_for_type($uri_info->path_vars[2]);
						} else {
							header("HTTP/1.0 404 Not Found");
						}
						break;
					case 4:
						//TODO: Was wenn in der zb 2 Krankenhäuser mit dem selben namen sind? irgendwie muss die id mit in den url
						if($uri_info->path_vars[1] == PLZ && in_array($uri_info->path_vars[2], get_user_types()) && in_array($uri_info->path_vars[3], get_all_orgs_for_user_for_type($uri_info->path_vars[2]))){
							$results = $data_operation->get_one_data_organizations_for_user_by_name($_SESSION['userid'], $uri_info->path_vars[3]);
							$row = $results->fetch_assoc();
							try{
								echo_json_one_org_for_user($row['id']);
							} catch (Exception $e){
								header("HTTP/1.0 404 Not Found");
							}
						} else {
							header("HTTP/1.0 404 Not Found");
						}
						break;
				}
			} else if (sizeof($uri_info->path_vars) == 1 && isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'data'){ //data path
				//output all plz the user has access to
				//TODO: wenn plz nicht fest ist, dann muss hier geschaut werden auf welche plz der user zugriff hat ansonsten kann man einfach die plz aus der define anzeigen
				header('Content-type: application/json');
				$data_out->add_keyvalue_to_links_array('config', $data_out->get_current_self_link() . '/' . PLZ );
				echo $data_out->output_as_json([]);
				//#TODO: output all plz the user has access to
			} else if (sizeof($uri_info->path_vars) > 1 && isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'data'){
				
				switch (sizeof($uri_info->path_vars)){
					case 2:
						if($uri_info->path_vars[1] == PLZ){
							echo_json_all_types_for_user();
						} else {
							header("HTTP/1.0 404 Not Found");
						}
						break;
					case 3:
						if($uri_info->path_vars[1] == PLZ && in_array($uri_info->path_vars[2], get_user_types())){
							echo_json_all_orgs_for_user_for_type($uri_info->path_vars[2]);
						} else {
							header("HTTP/1.0 404 Not Found");
						}
						break;
				}				
			} else if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'logout') {
				session_destroy();
			} else {
				header("HTTP/1.0 404 Not Found");
			}
		}
		
	} else if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login'){
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				//#TODO: muss man hier eventuell die post sanitizen? 
				if(isset($_POST['name']) && isset($_POST['pass'])){
					$user->register($_POST['name'], $_POST['pass'], 'test@email', 'realus', '123');

					if($user->login($_POST['name'],$_POST['pass'])){
						header("HTTP/1.0 200 Login Successfull");
					} else {
						header("HTTP/1.0 403 Forbidden");
					}
						
				} else {
					header("HTTP/1.0 400 Bad Request - pass and name are required");
				}
			} else {
				header("HTTP/1.0 405 Method Not Allowed");
				header("Access-Control-Allow-Methods: POST");
			}
	} else {
		header("HTTP/1.0 404 Not Found");
	}

}

function echo_json_all_types_for_user(){
	$data_operation = new DataOperations();
	$data_out = new DataOutput();
	$result =  $data_operation->get_all_config_types_for_user($_SESSION['userid']);
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['type']); 
	}
	$typearray_links = Array();
	foreach($typearray as $val){
		$typearray_links[$val] =  $data_out->get_current_self_link() . '/' . $val;
	}
	 $data_out->add_keyvalue_to_links_array('types', $typearray_links);						
	header('Content-type: application/json');
	echo  $data_out->output_as_json($typearray);
}
function echo_json_all_orgs_for_user_for_type($type){
	$data_out = new DataOutput();
	$typearray_links = Array();
	$typearray = get_all_orgs_for_user_for_type($type);
	foreach($typearray as $val){
		$typearray_links[$val] =  $data_out->get_current_self_link() . '/' . $val;
	}
	$data_out->add_keyvalue_to_links_array($type, $typearray_links);						
	header('Content-type: application/json');
	echo  $data_out->output_as_json($typearray);
}
//returns the names of the orgs the user has access to
function get_all_orgs_for_user_for_type($type){
	$data_operation = new DataOperations();
	$data_out = new DataOutput();
	$result =  $data_operation->get_all_data_organizations_for_user_for_type($_SESSION['userid'], $type);
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['name']); 
	}
	return $typearray;
}

//returns the ids of the orgs the user has access to
function get_all_orgids_for_user_for_type($type){
	$data_operation = new DataOperations();
	$data_out = new DataOutput();
	$result =  $data_operation->get_all_data_organizations_for_user_for_type($_SESSION['userid'], $type);
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['id']); 
	}
	return $typearray;
}


function echo_json_one_org_for_user($orgid){
	$data_operation = new DataOperations();
	$data_out = new DataOutput();
	$result =  $data_operation->get_one_data_organizations_for_user_by_id($_SESSION['userid'], $orgid);
	$typearray = Array();
	$typearray_full = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray_full, $row); 
	}
	$result = $data_operation->get_all_fields_for_org($orgid);
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['name']); 
	}
	$typearray_links = Array();

	foreach($typearray as $val){
		$typearray_links[$val] =  $data_out->get_current_self_link() . '/' . $val;
	}
	$data_out->add_keyvalue_to_links_array('fields', $typearray_links);	
	
	header('Content-type: application/json');
	echo  $data_out->output_as_json($typearray_full);
}
//returns all types the user has access to
function get_user_types(){
	$data_operation = new DataOperations();
	$result =  $data_operation->get_all_data_types_for_user($_SESSION['userid']);
	$typearray = Array();
	while($row = $result->fetch_assoc()) {
		array_push($typearray, $row['type']); 
	}
	return $typearray;
}

?>
