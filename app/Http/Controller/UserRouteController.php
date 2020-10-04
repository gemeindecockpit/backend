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

       $visible_user_ids = $user_controller->get_can_see_user_ids($_SESSION['user_id']);
       $visible_users = [];
       foreach ($visible_user_ids as $user_id) {
           $visible_user = $user_controller->get_user_by_id($user_id);
           $visible_user['permissions'] = $user_controller->get_permissions_by_id($user_id);
           array_push($visible_users, $visible_user);
       }

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
        $user_controller = new UserController();

        if (!$user_controller->can_create_user($_SESSION['user_id'])) {
            return $this->return_response($response, ResponseCodes::FORBIDDEN);
        }

        $new_user = json_decode($request->getBody(), true);
        if (!$this->check_post_users_request_format($new_user)) {
            $response->getBody()->write("Request not match required format.");
            return $this->return_response($response, ResponseCodes::SERVER_ERROR);
        }

        if ($user_controller->exists_user_for_username($new_user['username']))
            return $this->return_response($response, ResponseCodes::SERVER_ERROR);

        if (!$this->can_grant_this_rights($_SESSION['user_id'], $new_user['permissions'])) {
            return $this->return_response($response, ResponseCodes::FORBIDDEN);
        }

        $user_controller->insert_into_user($new_user['username'],
            $new_user['email'],
            $new_user['realname'],
            $new_user['userpassword'],
            'salty');

        $new_user_id = $user_controller->get_user_id_by_username($new_user['username']);

        $user_controller->insert_permissions($new_user_id, $new_user['permissions']);

        $user_controller->insert_into_can_see_user($_SESSION['user_id'], $new_user_id, 1);

        return $this->return_response($response, ResponseCodes::OK);

    }

   public function get_user_id ($request, $response, $args) {

       $user_controller = new UserController();
       if (!$user_controller->exists_user_for_id($args['id']))
           return $this->return_response($response, ResponseCodes::FORBIDDEN);
       if (!$user_controller->can_see_user($_SESSION['user_id'], $args['id']))
            return $this->return_response($response, ResponseCodes::FORBIDDEN);

       $user = $user_controller->get_user_by_id($args['id']);
       $user['permissions'] = $user_controller->get_permissions_by_id($args['id']);

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
       } else if ($parsed_request['id_user'] != $args['id']) {
           return $this->return_response($response, ResponseCodes::NO_MATCH);
       }
       $user_controller = new UserController();

       if (!$user_controller->can_alter_user($_SESSION['user_id'], $args['id']))
            return $this->return_response($response, ResponseCodes::FORBIDDEN);

       if ($user_controller->exists_user_for_username($parsed_request['username']))
           return $this->return_response($response, ResponseCodes::SERVER_ERROR);

       if (!$this->can_grant_this_rights($_SESSION['user_id'], $parsed_request['permissions']))
       return $this->return_response($response, ResponseCodes::FORBIDDEN);


       $errno = $user_controller->modify_user(
           $_SESSION['user_id'],
           $parsed_request['id_user'],
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
        if (!$this->check_correct_format($parsed_request, ['id_user' => false])) {
            return $this->return_response($response, ResponseCodes::BAD_REQUEST);
        } else if ($parsed_request['id_user'] != $args['id']) {
            return $this->return_response($response, ResponseCodes::NO_MATCH);
        }

        $user_controller = new UserController();

        if (!$user_controller->can_alter_user($_SESSION['user_id'], $parsed_request['id_user']))
            return $this->return_response($response, ResponseCodes::FORBIDDEN);

        $errno = $user_controller->update_user_active($parsed_request['id_user'], 0);

        return $this->return_response($response, $errno);
   }

   public function get_users_me($request, $response) {

        $user_controller = new UserController();
        $me = $user_controller->get_user_by_id($_SESSION['user_id']);
        $old_perms = $user_controller->get_permissions_by_id($_SESSION['user_id']);
        $me['permissions'] = array_merge($old_perms, $this->create_permissions($_SESSION['user_id']));

        $response->getBody()->write(json_encode($me));
        return $this->return_response($response, ResponseCodes::OK);

   }

   public function put_users_me($request, $response, $args)
   {

       $parsed_request = json_decode($request->getBody(), true);

       if (!$this->check_correct_format($parsed_request, [
           'id_user' => false,
           'userpassword' => null
       ])) {
           return $this->return_response($response, ResponseCodes::BAD_REQUEST);
       } else if ($_SESSION['user_id'] != $parsed_request['id_user']) {
           return $this->return_response($response, ResponseCodes::NO_MATCH);
       }

       $user_controller = new UserController();
       $errno = $user_controller->update_password($parsed_request['id_user'], $parsed_request['userpassword'], 'salty');

       return $this->return_response($response, $errno);

   }

    //Helper

    public function create_permissions($user_id) {

        $user_controller = new UserController();
        $permissions['users'] = [];
        foreach ($user_controller->get_can_see_user_ids($user_id) as $visible_user_id) {
            array_push($permissions['users'], ['id_user' => $visible_user_id, 'username' => $user_controller->get_username_by_id($visible_user_id)]);
        }

        $field_controller = new FieldController();
        $permissions['visible_fields'] = $field_controller->get_fields_visible_for_user($user_id);

        $permissions['writeable_fields'] = [];
        foreach ($user_controller->get_can_insert_into_field($user_id) as $writeable_field_id) {
            array_push($permissions['writeable_fields'], $field_controller->get_field_by_id($writeable_field_id));
        }

        $organisation_controller = new OrganisationController();
        $permissions['visible_organisations'] = $organisation_controller->get_orgs_visble_for_user($user_id);

        $permissions['organisation_groups'] = $organisation_controller->get_org_groups($user_id);

        return $permissions;

    }

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
            'can_create_organisation_type' => false,
            'can_create_organisation_group' => false,
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
        $request_format = ['id_user' => false, 'username' => null, 'email' => null, 'realname' => null, 'active' => false, 'req_pw_reset' => false,'permissions' => [
            'can_create_field' => false,
            'can_create_organisation' => false,
            'can_create_user' => false,
            'can_create_organisation_type' => false,
            'can_create_organisation_group' => false,
            'can_insert_into_field' => true,
            'can_see_user' => [['passive_user_id' => false, 'can_alter' => false]],
            'can_see_field' => [['field_id' => false, 'can_alter' => false]],
            'can_see_organisation' => [['organisation_id' => false, 'priority' => false, 'can_alter' => false]]]];
        return $this->check_correct_format($request, $request_format);
    }

}

?>
