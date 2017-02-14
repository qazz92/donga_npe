<?php
use Illuminate\Http\Request;
use App\Services\FCMHandler;

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

$app->get('/', 'MainController@index');
$app->group(['prefix' => 'donga'], function () use ($app) {
    $app->get('meal', 'DongaController@meal');
    $app->post('login','DongaController@dongaUnivLogin');
    $app->get('empty', 'DongaController@getEmptyClass');
    $app->get('empty/room', 'DongaController@getEmptyRoom');
    $app->get('getWebSeat', 'DongaController@getWebSeat');
    $app->post('async','DongaController@dongaInfoAsync');
});
$app->group(['prefix' => 'admin'], function () use ($app) {
    $app->get('/', 'AdminController@getIndex');
});
$app->post('/deviceInsert', 'MainController@deviceInsert');
$app->post('/deviceUpdate', 'MainController@deviceUpdate');
$app->post('/reg', 'MainController@reg');
$app->post('/normal_reg', 'MainController@normal_reg');

//$app->group(['middleware' => 'auth:api'], function($app)
//{
//    $app->post('/deviceInsert', 'MainController@deviceInsert');
////    $app->get('/test','MainController@test');
//});

$app->POST('/auth/login', 'AuthController@loginPost');
$app->get('/fcm', 'MainController@fcm');
