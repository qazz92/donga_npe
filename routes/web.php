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
    $app->get('meal/{year}/{month}/{day}', 'DongaController@meal');
    $app->post('login','DongaController@dongaUnivLogin');
//    $app->get('empty', 'DongaController@getEmptyClass');
    $app->get('empty/room', 'DongaController@getEmptyRoom');
    $app->get('getWebSeat', 'DongaController@getWebSeat');
});

$app->post('/reg', 'MainController@reg');

$app->group(['middleware' => 'auth:api'], function($app)
{
    $app->post('/deviceInsert', 'MainController@deviceInsert');
//    $app->get('/test','MainController@test');
});

$app->POST('/auth/login', 'AuthController@loginPost');

$app->get('fcm', function (Request $request, FCMHandler $fcm) {
    // 푸쉬 메시지를 수신할 단말기의 토큰 목록을 추출한다.
    $user = $request->user();
    $to = $user->devices()->pluck('push_service_id')->toArray();

    if (! empty($to)) {
        // 보낼 내용이 마땅치 않아 로그인한 사용자 모델을 푸쉬 메시지 본몬으로 ㅡㅡ;.
        $message = array_merge(
            $user->toArray(),
            ['foo' => 'bar']
        );

        // FCMHandler 덕분에 코드는 이렇게 한 줄로 간결해졌다.
        $fcm->to($to)->notification('title','본문')->data($message)->send();
    }

    return response()->json([
        'success' => 'HTTP 요청 처리 완료'
    ]);
});
