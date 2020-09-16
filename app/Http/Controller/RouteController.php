<?php

use Psr\Container\ContainerInterface;

foreach(glob(__DIR__ . "/../Models/*.php") as $filename) {
	require_once($filename);
}

function assoc_array_to_indexed($assoc_array) {
    $indexed_array = [];
    foreach($assoc_array as $value) {
        $indexed_array[] = $value;
    }
    return $indexed_array;
}

class RouteController {
    protected $container;

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       $this->container = $container;
   }

   public function home($request, $response, $args) {
       $response->getBody()->write(json_encode(array('data' => 'http://litwinow.xyz/data', 'config' => 'http://litwinow.xyz/config', 'user' => 'http://litwinow.xyz/user', 'login' => 'http://litwinow.xyz/login', 'logout' => 'http://litwinow.xyz/logout')));
       return $response->withHeader('Content-type', 'application/json');
   }

   public static function get_link(...$args) {
	array_walk_recursive($args, \RouteController::class . '::encode_items_url');
	return $_SERVER['SERVER_NAME'] . '/' . implode('/', $args);
   }

   public static function encode_items_url(&$item, $key){
	$item = rawurlencode($item);
   }

   public static function encode_items(&$item, $key){
	  $item = utf8_encode($item);
	}

	public static function assoc_array_to_indexed($assoc_array) {
	    $indexed_array = [];
	    foreach($assoc_array as $value) {
	        $indexed_array[] = $value;
	    }
	    return $indexed_array;
	}
}
