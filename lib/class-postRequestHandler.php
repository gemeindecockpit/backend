<?php

require_once('class-requestHandler.php');
require_once("lib/class-user.php");

class PostRequestHandler extends RequestHandler
{

    public function handleConfigRequest() {

    }

    public function handleDataRequest() {

    }

    public function handleUserRequest() {

    }


    public function handleLoginRequest() {
        $user = new UserData();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //#TODO: muss man hier eventuell die post sanitizen?
            if(isset($_POST['name']) && isset($_POST['pass'])){
                //$user->register($_POST['name'], $_POST['pass'], 'test@email', 'realus', '123');

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
    }
}
