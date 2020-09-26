<?php

use Psr\Container\ContainerInterface;

require_once('RouteController.php');

class UserRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }

   public function get_home ($request, $response, $args) {

       $user_controller = new UserController();

       $visible_users = $user_controller->get_can_see_user_ids($_SESSION['user_id']);

       $visible_users = $user_controller->get_all_by_id($visible_users);

       $response->getBody()->write(json_encode($visible_users));
       return $this->return_response($response, ResponseCodes::OK);
   }

    /**
     * Creates a new user if the request contains all necessary fields.
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function post_home ($request, $response, $args) {

        $new_user = json_decode($request->getBody(), true);
        if (!$this->check_post_users_request_format($new_user)) {
            $response->getBody()->write("Request not match required format.");
            return $response->withStatus(500);
        }

        $user_controller = new UserController();
        $errno = $user_controller->create_new_user($_SESSION['user_id'],
            $new_user['username'],
            $new_user['email'],
            $new_user['realname'],
            $new_user['userpassword'],
            $new_user['permissions']
        );

        return $this->return_response($response, $errno);

    }

   public function get_user_id ($request, $response, $args) {

       $user_controller = new UserController();
       if ($user_controller->exists_user($args['id']))
           $this->return_response($response, ResponseCodes::NOT_FOUND);
       if (!$user_controller->can_see_user($_SESSION['user_id']))
           $this->return_response($response, ResponseCodes::FORBIDDEN);

       $user = $user_controller->get_user_with_permissions_by_id($args['id']);

       $response->getBody()->write(json_encode($user));
       return $this->return_response($response, ResponseCodes::OK);
   }

   public function post_user_id ($request, $response, $args) {
       $response->getBody()->write('In Progress');
       return $response;
   }

   public function put_user_id ($request, $response, $args) {

       $parsed_request = json_decode($request->getBody(), true);

       if (!$this->check_put_users_id_request_format($parsed_request)) {
           return $this->return_response($response, ResponseCodes::BAD_REQUEST);
       } else if ($parsed_request['id'] != $args['id']) {
           return $this->return_response($response, ResponseCodes::NO_MATCH);
       }

       $user_controller = new UserController();
       $errno = $user_controller->modify_user(
           $_SESSION['user_id'],
           $parsed_request['id'],
           $parsed_request['username'],
           $parsed_request['email'],
           $parsed_request['realname'],
           $parsed_request['active'],
           $parsed_request['req_pw_reset'],
           $parsed_request['permissions']
       );

       return $this->return_response($response, $errno);
   }

   public function delete_user_id ($request, $response, $args) {

        $parsed_request = json_decode($request->getBody(), true);
        if (!$this->check_correct_format($parsed_request, ['id' => false])) {
            return $this->return_response($response, ResponseCodes::BAD_REQUEST);
        } else if ($parsed_request['id'] != $args['id']) {
            return $this->return_response($response, ResponseCodes::NO_MATCH);
        }

        $user_controller = new UserController();
        $errno = $user_controller->set_user_inactive($_SESSION['user_id'], $parsed_request['id']);

        return $this->return_response($response, $errno);
   }

   public function get_users_me($request, $response) {

        $user_controller = new UserController();
        $me = $user_controller->get_user_with_permissions_by_id($_SESSION['user_id']);
        error_log('error where are you');
        $response->getBody()->write(json_encode($me));
        return $this->return_response($response, ResponseCodes::OK);

   }

   public function put_users_me($request, $response, $args)
   {

       $parsed_request = json_decode($request->getBody(), true);

       if (!$this->check_correct_format($parsed_request, [
           'id' => false,
           'userpassword' => null
       ])) {
           $response->getBody()->write('Request not match required format.');
           return $response->withStatus(500);
       } else if ($_SESSION['user_id'] != $parsed_request['id']) {
           $response->getBody()->write('User of request does not match session user.');
           return $response->withStatus(500);
       }

       $user_controller = new UserController();
       $errno = $user_controller->update_password($parsed_request['id'], $parsed_request['userpassword'], 'salty');

       return $this->return_response($response, $errno);

   }

    //Helper

    /**
     * Checks if the request is of proper structure.
     * @param $request
     * @return bool
     */
    private function check_post_users_request_format($request) {
        $request_format = ['username' => null, 'email' => null, 'realname' => null, 'userpassword' => null, 'permissions' => [
            'can_create_field' => false,
            'can_create_organisation' => false,
            'can_create_user' => false,
            'can_insert_into_field' => true,
            'can_see_user' => [['passive_user_id' => false, 'can_alter' => false]],
            'can_see_field' => [['field_id' => false, 'can_alter' => false]],
            'can_see_organisation' => [['organisation_id' => false, 'priority' => false, 'can_alter' => false]]]];
        return $this->check_correct_format($request, $request_format);
    }

    /**
     * Checks if the request is of proper structure.
     * @param $request
     * @return bool
     */
    private function check_put_users_id_request_format($request) {
        $request_format = ['id' => false, 'username' => null, 'email' => null, 'realname' => null, 'active' => false, 'req_pw_reset' => false,'permissions' => [
            'can_create_field' => false,
            'can_create_organisation' => false,
            'can_create_user' => false,
            'can_insert_into_field' => true,
            'can_see_user' => [['passive_user_id' => false, 'can_alter' => false]],
            'can_see_field' => [['field_id' => false, 'can_alter' => false]],
            'can_see_organisation' => [['organisation_id' => false, 'priority' => false, 'can_alter' => false]]]];
        return $this->check_correct_format($request, $request_format);
    }

}

?>
