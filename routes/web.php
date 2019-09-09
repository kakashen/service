<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group([], function () use ($router) {
    $router->get('profile', ['uses' => 'ExampleController@showProfile']);

});


//登录注册
$router->post('user/login', 'UserController@login');
$router->post('user/register', 'UserController@register');

$router->group(['middleware' => 'auth', 'prefix' => 'api'], function () use ($router) {
    $router->post('user/info','UserController@info');
    $router->post('user/logout', 'UserController@logout');
});

