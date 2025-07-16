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
    return response()->json([
        'status' => 200,
        'message' => 'Lumen API is running.',
        'data' => [
            'version' => $router->app->version()
        ]
    ]);
});

$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/me', 'AuthController@me');
    $router->post('/logout', 'AuthController@logout');

    $router->get('/posts', 'PostController@index');
    $router->get('/posts/me', 'PostController@me');
    $router->get('/posts/{id}', 'PostController@show');
    $router->post('/posts', 'PostController@store');
    $router->put('/posts/{id}', 'PostController@update');
    $router->delete('/posts/{id}', 'PostController@destroy');

    $router->get('/posts/{postId}/likes', 'InteractionController@getLikes');
    $router->post('/posts/{postId}/like', 'InteractionController@like');
    $router->delete('/posts/{postId}/like', 'InteractionController@unlike');

    $router->get('/posts/{postId}/comments', 'InteractionController@getComments');
    $router->post('/posts/{postId}/comment', 'InteractionController@comment');
    $router->put('/posts/{postId}/comment/{id}', 'InteractionController@updateComment');
    $router->delete('/posts/{postId}/comment/{id}', 'InteractionController@deleteComment');
});
