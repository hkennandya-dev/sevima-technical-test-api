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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');
$router->post('/logout', ['middleware' => 'auth', 'uses' => 'AuthController@logout']);

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/me', 'AuthController@me');

    $router->get('/posts', 'PostController@index');
    $router->post('/posts', 'PostController@store');
    $router->delete('/posts/{id}', 'PostController@destroy');

    $router->post('/posts/{postId}/like', 'InteractionController@like');
    $router->delete('/posts/{postId}/like', 'InteractionController@unlike');

    $router->post('/posts/{postId}/comment', 'InteractionController@comment');
});
