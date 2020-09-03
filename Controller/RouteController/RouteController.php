<?php

use Psr\Container\ContainerInterface;

foreach(glob(__DIR__ . "/../*.php") as $filename) {
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
}
