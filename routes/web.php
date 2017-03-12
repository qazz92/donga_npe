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
$app->get('/privacy', 'MainController@privacy');
$app->group(['prefix' => 'donga'], function () use ($app) {
    $app->get('meal', 'DongaController@meal');
    $app->post('login','DongaController@dongaUnivLogin');
//    $app->get('empty', 'DongaController@getEmptyClass');
    $app->get('empty/room', 'DongaController@getEmptyRoom');
    $app->get('getWebSeat', 'DongaController@getWebSeat');
    $app->post('getGraduated','DongaController@getGraduated');
    $app->post('getAllGrade','DongaController@getAllGrade');
    $app->post('getSpeGrade','DongaController@getSpeGrade');
    $app->post('getTimeTable','DongaController@getTimeTable');
    $app->get('getPro', 'DongaController@getPro');
    $app->get('getCircle','DongaController@getCircle');
    $app->post('setCircle','DongaController@setCircle');
    $app->post('getUserCircle','DongaController@getUserCircle');
    $app->post('updateCircle','DongaController@updateCircle');
    $app->post('setNoneCirclee','DongaController@setNoneCircle');
    $app->get('checkCircle','DongaController@checkCircle');
});
$app->group(['prefix' => 'admin'], function () use ($app) {
    $app->get('/', 'AdminController@getIndex');
});
$app->post('/deviceInsert', 'MainController@deviceInsert');
$app->post('/deviceConfirm', 'MainController@deviceConfirm');
$app->post('/deviceUpdate', 'MainController@deviceUpdate');
$app->post('/reg', 'MainController@reg');
$app->post('/normal_reg', 'MainController@normal_reg');
$app->get('/getNormalNotis','MainController@getNormalNotis');
$app->get('/getCircleNotis','MainController@getCircleNotis');
$app->post('/normal_read','MainController@normal_read');
$app->post('/circle_read','MainController@circle_read');
$app->post('/change_att','MainController@change_att');
$app->get('/change_push_permit','MainController@change_push_permit');
$app->post('/removeNormalNotis','MainController@removeNormalNotis');
$app->post('/removeCircle','MainController@removeCircle');
$app->get('/removePmk','AdminController@removePmk');

$app->group(['middleware' => 'auth:api'], function($app)
{
    $app->post('/normal/fcm', 'AdminController@normal_fcm');
    $app->post('/circle/fcm', 'AdminController@circle_fcm');
    $app->get('/getMembers','AdminController@getMembers');
    $app->get('/getPNormalNotis','AdminController@getPNormalNotis');
    $app->get('/getPCircleNotis','AdminController@getPCircleNotis');
    $app->get('/adminCircleNotis','AdminController@admin_getCircleNotis');
});

$app->POST('/auth/login', 'AuthController@loginPost');

