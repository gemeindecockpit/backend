<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class LoginRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function login ($request, $response, $args) {
     //is the user already logged in? if yes redirect to user-page
     if(isset($_SESSION['user_id'])){
       //return $response->withStatus(303)->withHeader('Location', '/users/me'); //TODO:once implemented this should be used instead of 403
       return $response->withStatus(403, 'already logged in');
     } else {
       $logCon = new LoginController();
       //geting all post parameter
       $allPostVars = $request->getParsedBody();
       //are the correct post parameter set?
       if(isset($allPostVars['name']) && isset($allPostVars['pass'])) {
         $postName = utf8_encode($allPostVars['name']);
         $postPass = utf8_encode($allPostVars['pass']);
         //setting the Session Parameter is done in the LoginController
         if($logCon->login($postName, $postPass)){
           //if the login is succesfull than the user will be redirected to /users/me to display his account files
           return $response->withStatus(303)->withHeader('Location', '/users/me');
         } else {
           return $response->withStatus(401, 'Wrong User/Pass');
         }
       } else {
        return $response->withStatus(400,'expected \'user\' and \'pass\'');
       }
     }
   }

   public function logout ($request, $response, $args) {
     $logCon = new LoginController();
     $logCon->logout();
     return $response->withStatus(200);
   }

   public function wrong_method ($request, $response, $args) {
     return $response->withStatus(405)->withHeader('Access-Control-Allow-Methods','POST'); //TODO: is currently overwritten by the errorHandler. Either rewrite error handler to ignore 'Wrong Methods' and let the routing handle these or rewrite ErrorHandler to correctly display available methods
   }

}

?>
