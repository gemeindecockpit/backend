<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class UserRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function get_home ($request, $response, $args) { 
    $response->getBody()->write('In Progress');
    return $response;
   }

   public function post_home ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_user_id ($request, $response, $args) { 
    $response->getBody()->write('In Progress');
       return $response;
   }

   public function post_user_id ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function put_user_id ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function delete_user_id ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function get_me ($request, $response, $args) {
    $userCon = new UserController();
    $userInfo = $userCon->get_one($_SESSION['user_id'], $_SESSION);
    $response->getBody()->write(json_encode($userInfo));
    return $response;
       
   }

}

?>
