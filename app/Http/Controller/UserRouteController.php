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
           $visible_user['permissions'] = $this->create_permissions($user_id);
           array_push($visible_users, $visible_user);
       }

       $response->getBody()->write(json_encode($visible_users, JSON_NUMERIC_CHECK));
       return $this->return_response($response, ResponseCodes::OK);
   }

    /**
     * Creates a new user if the request contains all necessary fields.
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function post_home ($request, \Psr\Http\Message\ResponseInterface $response, $args) {

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

        $password_hash = hash('sha256', $new_user['userpassword'] . SALT . 'salty');
        $user_controller->insert_into_user(
            $new_user['username'],
            $password_hash,
            $new_user['email'],
            $new_user['realname'],
            'salty' // TODO random generated
        );


        $new_user_id = $user_controller->get_user_id_by_username($new_user['username']);

        $user_controller->insert_permissions($new_user_id, $new_user['permissions']);

        $user_controller->insert_into_can_see_user($_SESSION['user_id'], $new_user_id, 1);

        return $this->return_response($response->withHeader('Location',"/users/$new_user_id"), ResponseCodes::CREATED);

    }

   public function get_user_id ($request, $response, $args) {

       $user_controller = new UserController();
       if (!$user_controller->exists_user_for_id($args['id']))
           return $this->return_response($response, ResponseCodes::FORBIDDEN);
       if (!$user_controller->can_see_user($_SESSION['user_id'], $args['id']))
            return $this->return_response($response, ResponseCodes::FORBIDDEN);

       $user = $user_controller->get_user_by_id($args['id']);
       $user['permissions'] = $this->create_permissions($args['id']);

       $response->getBody()->write(json_encode($user, JSON_NUMERIC_CHECK));
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

       if (!$this->can_grant_this_rights($_SESSION['user_id'], $parsed_request['permissions']))
            return $this->return_response($response, ResponseCodes::FORBIDDEN);

       $old_username = $user_controller->get_username_by_id($args['id']);
       if ($old_username !== $parsed_request['username'] && $user_controller->exists_user_for_username($parsed_request['username'])) {
           $response->getBody()->write('Username already exists');
           return $this->return_response($response, ResponseCodes::CONFLICT);
       }

       $user_controller->modify_user(
           $parsed_request['id_user'],
           $parsed_request['username'],
           $parsed_request['email'],
           $parsed_request['realname'],
           $parsed_request['active'],
           $parsed_request['req_pw_reset'],
           $parsed_request['permissions']
       );


       return $this->return_response($response->withHeader('Location','/users/' . strval($args['id'])), ResponseCodes::CREATED);
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

        return $this->return_response($response, ResponseCodes::OK);
   }

   public function get_users_me($request, $response) {

        $user_controller = new UserController();
        $me = $user_controller->get_user_by_id($_SESSION['user_id']);
        $me['permissions'] = $this->create_permissions($_SESSION['user_id']);

        $response->getBody()->write(json_encode($me, JSON_NUMERIC_CHECK));
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
       $password_hash = hash('sha256', $parsed_request['userpassword'] . SALT . 'salty');
       $errno = $user_controller->update_user_password($parsed_request['id_user'], $password_hash);

       return $this->return_response($response->withHeader('Location','/users/me'), ResponseCodes::CREATED);

   }

    //Helper

    public function create_permissions($user_id) {

        $user_controller = new UserController();
        $permissions = $user_controller->get_permissions_by_id($user_id);
        foreach ($permissions['can_see_user'] as &$visible_user) {
            $visible_user['username'] = $user_controller->get_username_by_id($visible_user['passive_user_id']);
        }

        $field_controller = new FieldController();
        foreach ($permissions['can_see_field'] as &$visible_field) {
            $visible_field['field_name'] = $field_controller->get_field_by_id($visible_field['field_id'])[0]['field_name'];
        }

        $writeable_field_ids = $permissions['can_insert_into_field'];
        $permissions['can_insert_into_field'] = [];
        foreach ($writeable_field_ids as $writeable_field_id) {
            $field = $field_controller->get_field_by_id($writeable_field_id)[0];
            array_push($permissions['can_insert_into_field'], ['field_id' => $field['field_id'], 'field_name' => $field['field_name']]);
        }

        $organisation_controller = new OrganisationController();
        foreach ($permissions['can_see_organisation'] as &$visible_org) {
            $org = $organisation_controller->get_org_config('organisation', $visible_org);
            $visible_org['organisation_name'] = $org[0]['organisation_name'];
        }

        $permissions['organisation_groups'] = $organisation_controller->get_org_groups($user_id);
        foreach ($permissions['organisation_groups'] as &$org_group) {
            $org_group['organisation_ids'] = $organisation_controller->get_org_ids_by_group_id_for_user($org_group['organisation_group_id'], $user_id);
        }

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
