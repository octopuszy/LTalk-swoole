<?php

namespace App\HttpController;


use FastRoute\RouteCollector;

class Router extends \EasySwoole\Core\Http\AbstractInterface\Router
{
    function register(RouteCollector $routeCollector)
    {
        $routeCollector->post('/register', '/Api/Login/register');

        $routeCollector->post('/login', '/Api/Login/login');
    }
}