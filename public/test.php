<?php
  require 'libs/Slim/Slim.php';
  \Slim\Slim::registerAutoloader();

  $app = new \Slim\Slim();
  $app->get('/hallo/:name', function ($name) {
    echo "Hallo $name";
  });
  $app->run();
