<?php
namespace App\Core;

class Routes
{
  public function run()
  {
    $router = new App();
    $router->setDefaultController('DefaultApp');
    $router->setDefaultMethod('index');
    $router->setNamespace('App\Controllers');

    $router->post('/register', ['Auth', 'register']);
    $router->post('/login', ['Auth', 'login']);
    $router->get('/refresh', ['Auth', 'refreshToken']);

    $router->run();
  }
}