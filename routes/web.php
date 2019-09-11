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
$router->post('staff/login', 'StaffController@login');
$router->post('staff/register', 'StaffController@register');

$router->group(['middleware' => 'auth', 'prefix' => 'api'], function () use ($router) {
    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix' => '/user'], function () use ($router) {
            $router->post('info', 'StaffController@info');
            $router->post('updateStatus', 'StaffController@updateStatus');
        });

        $router->group(['prefix' => 'service'], function () use ($router) {
            $router->post('addMessage', 'CustomerServiceMessageController@addMessage');
            $router->post('getMessage', 'CustomerServiceMessageController@getMessage');
            $router->post('updateMessage', 'CustomerServiceMessageController@updateMessage');


        });

        $router->group(['prefix' => 'message'], function () use ($router) {
            $router->post('sendMessage', 'MessageController@sendMessage');
            $router->post('getMessage', 'MessageController@getMessage');


        });

        $router->group(['prefix' => 'communication'], function () use ($router) {
            $router->post('end', 'CommunicationController@end');
            // 同客户获取会话
            $router->post('communicationWithClient', 'CommunicationController@cCommunication');


        });

    });

});

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->group(['prefix' => 'client/message'], function () use ($router) {
        $router->post('cSendMessage', 'MessageController@cSendMessage');
        $router->post('cGetMessage', 'MessageController@cGetMessage');


    });
    $router->group(['prefix' => 'client/communication'], function () use ($router) {
        $router->post('cEnd', 'CommunicationController@cEnd');
        $router->post('cCommunication', 'CommunicationController@cCommunication');


    });

});


