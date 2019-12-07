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
$router->post('/api/staff/login', 'StaffController@login');
$router->post('/api/staff/register', 'StaffController@register');
$router->post('/api/staff/info', 'StaffController@info');
$router->get('/api/sts', 'MessageController@sts');

$router->group(['middleware' => 'auth', 'prefix' => 'api'], function () use ($router) {
    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix' => '/staff'], function () use ($router) {
            $router->post('updateStatus', 'StaffController@updateStatus');
            $router->post('getStatus', 'StaffController@getStatus');
            $router->post('updateAvatar', 'StaffController@updateAvatar');
        });

        $router->group(['prefix' => '/admin'], function () use ($router) {
            $router->post('staffList', 'StaffController@staffList');
            $router->post('addStaff', 'StaffController@register');
            $router->post('commList', 'CommunicationController@commList');
            $router->post('index', 'StaffController@index');
            $router->post('admin', 'StaffController@admin');
            $router->post('uploadAvatar', 'StaffController@uploadAvatar');
            $router->post('avatarList', 'StaffController@avatarList');
            $router->post('activeStaff', 'StaffController@activeStaff');
            $router->post('resetPass', 'StaffController@resetPass');
        });

        $router->group(['prefix' => 'service'], function () use ($router) {
            $router->post('add', 'CustomerServiceMessageController@add');
            $router->post('get', 'CustomerServiceMessageController@get');
            $router->post('update', 'CustomerServiceMessageController@update');
            $router->post('delete', 'CustomerServiceMessageController@delete');


        });

        $router->group(['prefix' => 'message'], function () use ($router) {
            $router->post('send', 'MessageController@send');
            $router->post('getAll', 'MessageController@getAll');
            $router->post('getNew', 'MessageController@getNew');
            $router->post('isRead', 'MessageController@isRead');
            $router->post('upload', 'MessageController@upload');


        });

        $router->group(['prefix' => 'chat'], function () use ($router) {
            $router->post('get', 'ActiveChatController@get'); // 客服活动聊天窗口
            $router->post('delete', 'ActiveChatController@delete');


        });


        $router->group(['prefix' => 'communication'], function () use ($router) {
            $router->post('end', 'CommunicationController@end');
            // 同客户获取会话
            $router->post('withClient', 'CommunicationController@communication');


        });

    });

});

$router->group(['prefix' => 'api'], function () use ($router) {

    // 评分
    $router->post('client/grade', 'StaffController@grade');
    // 消息
    $router->group(['prefix' => 'client/message'], function () use ($router) {
        $router->post('cSend', 'MessageController@cSend');
        $router->post('cGet', 'MessageController@cGet');
        $router->post('cGetNew', 'MessageController@cGetNew');
        $router->post('isRead', 'MessageController@isRead');
        $router->post('upload', 'MessageController@upload');




    });

    // 会话
    $router->group(['prefix' => 'client/communication'], function () use ($router) {
        $router->post('cEnd', 'CommunicationController@cEnd');
        $router->post('cGet', 'CommunicationController@cGet');


    });

});


