<?php

#-- config --
require_once("inc/config.php");

#-- classes --
require_once("lib/class-urisplit.php");
require_once("lib/class-user.php");
require_once("lib/class-dataoutput.php");
require_once("lib/class-dataoperations.php");

require_once("lib\class-getRequestHandler.php");
require_once("lib\class-postRequestHandler.php");


#Informations about servers and methods
$my_hostname = $_SERVER['HTTP_HOST'];
$my_uri = $_SERVER['REQUEST_URI'];
$my_method = $_SERVER['REQUEST_METHOD'];


session_start();

if(DEBUG_MODE)
	$_SESSION['user_id'] = 4;

$uri_info = new URISplit();
$user = new UserData();
$data_operation = new DataOperations();
$data_out = new DataOutput();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	$request_handler = new GetRequestHandler($uri_info->path_vars,$data_operation);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$request_handler = new PostRequestHandler($uri_info->path_vars,$data_operation);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
	$request_handler = new PutRequestHandler($uri_info->path_vars,$data_operation);
}

if(!isset($_SESSION['user_id'])) {
	if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login') {
		$request_handler->handleLoginRequest();
	} else {
		header("HTTP/1.0 404 Not Found");
	}
} else {
	echo $request_handler->handleRequest();
}

?>
