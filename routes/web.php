<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => '/api/v1'], function () use ($router) {
    $router->group(['prefix' => '/auth'], function () use ($router) {
        $router->post('/register', 'AuthController@register');
        $router->post('/login', 'AuthController@login');

        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->get('/me', 'AuthController@me');
        });
    });

    $router->group(['prefix' => '/members', 'middleware' => 'auth'], function () use ($router) {
        $router->get('/', "MemberController@index");
        $router->post('/', "MemberController@store");
        $router->get('/{id}', "MemberController@show");
        $router->put('/{id}', "MemberController@update");
        $router->delete('/{id}', "MemberController@destroy");
    });
});