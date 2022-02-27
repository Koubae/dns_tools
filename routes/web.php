<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/[index]', 'AppController@version');
$router->get('/version', 'AppController@version');
$router->get('/home', 'AppController@version'); // todo: merge these 2 routes

/*
|--------------------------------------------------------------------------
| WHOIS LookUp
|--------------------------------------------------------------------------
|
*/

$router->group(['prefix' => 'whois'], function () use ($router) {
    $router->get('/', 'WHOISController@info');
    $router->get('/info', 'WHOISController@info');
    $router->get('/get/{domain}/{tld}', 'WHOISController@get');
});


/*
|--------------------------------------------------------------------------
| DIG LookUp
|--------------------------------------------------------------------------
|
*/

$router->group(['prefix' => 'dig'], function () use ($router) {
    $router->get('/', 'DigController@info');
    $router->get('/info', 'DigController@info');
    $router->get('/get', 'DigController@get');
});
