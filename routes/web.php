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

// TEST
$app->get('/', 'MainController@index');

// 약관
$app->get('/privacy', 'MainController@privacy');

// 주요 기능
$app->group(['prefix' => 'donga'], function () use ($app) {
    $app->get('meal', 'DongaController@meal');
    $app->post('login','DongaController@dongaUnivLogin');
    $app->get('empty', 'DongaController@getEmptyClass');
    $app->get('empty/room', 'DongaController@getEmptyRoom');
    $app->get('getWebSeat', 'DongaController@getWebSeat');
    $app->post('getGraduated','DongaController@getGraduated');
    $app->post('getAllGrade','DongaController@getAllGrade');
    $app->post('getSpeGrade','DongaController@getSpeGrade');
    $app->post('getTimeTable','DongaController@getTimeTable');
    $app->get('getPro', 'DongaController@getPro');
});

// 동아리 관련
$app->get('getCircle','CircleController@getCircle');
$app->post('setCircle','CircleController@setCircle');
$app->post('getUserCircle','CircleController@getUserCircle');
$app->post('updateCircle','CircleController@updateCircle');
$app->post('setNoneCirclee','CircleController@setNoneCircle');
$app->get('checkCircle','CircleController@checkCircle');

// FCM Token 관련
$app->post('/deviceInsert', 'DeviceController@deviceInsert');
$app->post('/deviceConfirm', 'DeviceController@deviceConfirm');
$app->post('/deviceUpdate', 'DeviceController@deviceUpdate');



// 동아리 회장 회원 가입
$app->post('/reg', 'AdminController@reg');
// 동아리 회장 로그인
$app->post('/auth/login', 'AuthController@loginPost');
// jwt 인증
$app->group(['middleware' => 'auth:api'], function($app)
{
    // 동아리 회원 보기
    $app->get('/getMembers','AdminController@getMembers');

    // Push 관련 (메시지 보내기)
    $app->post('/normal/fcm', 'PushController@normal_fcm');
    $app->post('/circle/fcm', 'PushController@circle_fcm');
    $app->get('/getPNormalNotis','PushController@getPNormalNotis');
    $app->get('/getPCircleNotis','PushController@getPCircleNotis');
    $app->get('/adminCircleNotis','PushController@admin_getCircleNotis');
    $app->post('/total/fcm','PushController@total_fcm');
});


// Push 관련
$app->get('/getNormalNotis','PushController@getNormalNotis');
$app->get('/getCircleNotis','PushController@getCircleNotis');
$app->post('/normal_read','PushController@normal_read');
$app->post('/circle_read','PushController@circle_read');
$app->post('/change_att','PushController@change_att');
$app->get('/change_push_permit','PushController@change_push_permit');
$app->post('/removeNormalNotis','PushController@removeNormalNotis');
$app->post('/removeCircleNotis','PushController@removeCircleNotis');


