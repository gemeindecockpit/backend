<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class LoginRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function login ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function logout ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

}

?>
