<?php

namespace App\Http\Controllers;

use App\Circle;
use App\Professor;
use App\Services\GetDonga;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Normal_User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Room;
use App\User_Circle;

class DongaController extends Controller
{
    protected $set;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // 식단표
    public function meal(Request $request)
    {
        $date = $request->input("date");
        $redisMeal = Redis::get('meal_' . $date);
        if ($redisMeal == null) {
            $client = new \Goutte\Client();
            try {
                Log::info("crawler");
                $crawler = $client->request('GET', "http://www.donga.ac.kr/MM_PAGE/SUB007/SUB_007005005.asp?PageCD=007005005&seldate=" . $date);
                $result = $crawler->filter('div#subContext > table')->eq(0)->filter('tr')
                    ->eq(1)->filter('table.sk01TBL')->eq(1)->
                    filter('table.sk03TBL')->filter('td');
                $inter = $this->returnHtml($result, 7);
                $bumin_kyo = $this->returnHtml($result, 8);
                $gang = $this->returnHtml($result, 9);
                $resultArr = array("inter" => $inter, "bumin_kyo" => $bumin_kyo, "gang" => $gang);
                Redis::set('meal_' . $date, json_encode($resultArr));
                return response()->json(array("result_code" => 1, "result_body" => $resultArr));
            } catch (\Exception $e) {
                return response()->json(array("result_code" => 0, "result_body" => "none"));
            }
        } else {
            Log::info("REDIS_MEAL");
            return response()->json(array("result_code" => 1, "result_body" => json_decode($redisMeal)));
        }
    }

    function returnHtml($result, $index)
    {
        return $result = $result->eq($index)->html();
    }

    public function dongaUnivLogin(Request $request, GetDonga $getDonga)
    {
        $loginPage = 'https://student.donga.ac.kr/Login.aspx';
        $result = $getDonga->getUserInfo($request)->getDongaPage($loginPage);
        if ($result["result_code"] == 1) {
            $targetPage = 'https://student.donga.ac.kr/Univ/SUD/SSUD0000.aspx?m=1';
            $user_id = $result["user_id"];
            $client = $result["client"];

            $crawlerTable = $client->request('GET', $targetPage);
            try {
                $infoTable = $crawlerTable->filter('table#Table4')->filter('tr');
                $name = $infoTable->eq(0)->filter('td')->eq(2)->filter('span#lblKorNm')->text();
                $coll = $infoTable->eq(1)->filter('span#lblCollegeNm')->text();
                $major = $infoTable->eq(2)->filter('span#lblDeptNm')->text();
                $getID = Normal_User::where('stuId', '=', $user_id)->get();
                if ($getID->isEmpty()) {
                    $user = new Normal_User();
                    try {
                        $user->stuId = $user_id;
                        $user->name = $name;
                        $user->coll = $coll;
                        $user->major = $major;
                        $result = $user->save();
                        if ($result) {
                            return response()->json(["result_code" => 1, "result_body" => $user]);
                        } else {
                            return response()->json(["result_code" => 0, "result_body" => "DB 에러!"]);
                        }
                    } catch (\Exception $e) {
                        echo $e;
                        return response()->json(["result_code" => 0, "result_body" => "DB 에러!"]);
                    }
                } else {
                    return response()->json(array("result_code" => 1, "result_body" => $getID[0]));
                }
            } catch (\Exception $e){
                return response()->json(array("result_code" => 500));
            }

        } else {
            return response()->json(["result_code" => $result["result_code"]]);
        }
    }



    public function getGraduated(Request $request, GetDonga $getDonga)
    {
        $stiId = $request->input('stuId');
        $cached = Redis::get('getGraduated_'.$stiId);
        if ($cached != null){
            Log::info('GRA CACHED');
            return response()->json(json_decode($cached));
        } else {
            Log::info('GRA CRAWLER');
            $loginPage = 'https://student.donga.ac.kr/Login.aspx';
            $result = $getDonga->getUserInfo($request)->getDongaPage($loginPage);
            if ($result["result_code"] == 1) {
                $targetPage = 'https://student.donga.ac.kr/Univ/SUI/SSUI0050.aspx?m=7';
                $user_id = $result["user_id"];
                $client = $result["client"];
                $crawlerTable = $client->request('GET', $targetPage);
                try {
                    $keys = array('multi', 'sub', 'rel', 'year', 'avgGrade', 'early', 'smart');
                    $values = array();
                    $title = array();
                    $title2 = array();
                    $need = array();
                    $get = array();
                    $pm = array();
                    $crawlerTable->filter('table#dListEtcInfo')->filter('table.xTBL1')->filter('tr')->filter('td')->each(function ($node, $i) use (&$keys, &$values) {

                        switch ($i % 2) {
                            case 1:
                                if (strcmp($node->text(), "")) {
                                    $values[] = $node->text();
                                } else {
                                    $values[] = '없음';
                                }
                                break;
                        }

                    });
                    $crawlerTable->filter('table#printtable')
                        ->filter('tr')->filter('table.xTBL1')->eq(2)
                        ->filter('tr')->filter('td')->each(function ($node, $i) use (&$title, &$title2, &$need, &$get, &$pm) {
                            if ($i < 6) {
                                if ($i === 4) {
                                    $title[] = preg_split('/\\r\\n\s+/', trim($node->text()))[0];
                                } else {
                                    $title[] = trim($node->text());
                                }
                            } elseif (6 <= $i && $i < 15) {
                                $title2[] = trim($node->text());
                            } elseif (15 <= $i && $i < 27) {
                                $need[] = trim($node->text());
                            } elseif (27 <= $i && $i < 39) {
                                $get[] = trim($node->text());
                            } elseif (39 <= $i) {
                                $pm[] = trim($node->text());
                            }
                        });
                    $info = array_combine($keys, $values);
                    $result = array('result_code' => 1, 'result_body' => array('info' => $info, 'title' => $title, 'title2' => $title2, 'need' => $need,
                        'get' => $get, 'pm' => $pm));
                    Redis::set('getGraduated_' . $user_id, json_encode($result));
                    return response()->json($result);
                } catch (\Exception $e) {
                    $fail = $result["page"]->filter("span#lblError")->text();
                    if (str_contains($fail,"학번")){
                        $result_code = 3;
                    }
                    else {
                        $result_code = 500;
                    }
                    return response()->json(array('result_code' => $result_code));
                }
            }else {
                return response()->json(["result_code" => $result["result_code"]]);
            }
        }
    }

    public function getAllGrade(Request $request, GetDonga $getDonga)
    {
        $stiId = $request->input('stuId');
        $cached = Redis::get('getAllGrade_' . $stiId);
//        $cached = Cache::get('getAllGrade_' . $stiId);
        if (!$cached == null) {
            Log::info('ALL CACHE');
            return response()->json(json_decode($cached));
        } else {
            Log::info('ALL CRAWLER');

            $loginPage = 'https://student.donga.ac.kr/Login.aspx';
            $result = $getDonga->getUserInfo($request)->getDongaPage($loginPage);
            if ($result["result_code"] == 1) {
                $targetPage = 'https://student.donga.ac.kr/Univ/SUH/SSUH0011.aspx?m=6';
                $user_id = $result["user_id"];
                $client = $result["client"];
                $crawlerTable = $client->request('GET', $targetPage);
                try {
                    $result = $getDonga->getGrade($crawlerTable, 8);
                    Redis::set('getAllGrade_' . $user_id, json_encode($result));
                    return response()->json($result);
                } catch (\Exception $e) {
                    $fail = $result["page"]->filter("span#lblError")->text();
                    if (str_contains($fail,"학번")){
                        $result_code = 3;
                    }
                    else {
                        $result_code = 500;
                    }
                    return response()->json(array('result_code' => $result_code));
                }
            } else {
                return response()->json(["result_code" => $result["result_code"]]);
            }
        }

    }

    function getSpeGrade(Request $request, GetDonga $getDonga)
    {
        $stiId = $request->input('stuId');
        $year = $request->input('year');
        $smt = $request->input('smt');
        $cached = Redis::get('getSpeGrade_' . $year . $smt .'|'. $stiId);

//        $cached = Cache::get('getSpeGrade_' . $year . $smt . $stiId);
        if (!$cached == null) {
            Log::info('SPE CACHE');
            return response()->json(json_decode($cached));
        } else {
            Log::info('SPE CRAWLER');
            $loginPage = 'https://student.donga.ac.kr/Login.aspx';
            $result = $getDonga->getUserInfo($request)->getDongaPage($loginPage);
            if ($result["result_code"] == 1) {
                $targetPage = 'https://student.donga.ac.kr/Univ/SUH/SSUH0012.aspx?m=6&year=' . $year . '&smt=' . $smt;
                $client = $result["client"];
                $user_id = $result["user_id"];
                $crawlerTable = $client->request('GET', $targetPage);
                try {
                    $result = $getDonga->getGrade($crawlerTable, 10);
                    Redis::set('getSpeGrade_' . $year . $smt .'|'. $stiId, json_encode($result));
//                    $expiresAt = Carbon::now()->addMinutes(60);
//                    Cache::put('getSpeGrade_'.$year.$smt.$user_id, $result, $expiresAt);
                    return response()->json($result);
                } catch (\Exception $e) {
                    $fail = $result["page"]->filter("span#lblError")->text();
                    if (str_contains($fail,"학번")){
                        $result_code = 3;
                    }
                    else {
                        $result_code = 500;
                    }
                    return response()->json(array('result_code' => $result_code));
                }
            }else {
                return response()->json(["result_code" => $result["result_code"]]);
            }
        }
    }

    public function getTimeTable(Request $request, GetDonga $getDonga)
    {
        $stiId = $request->input('stuId');
        $redisTimeTable = Redis::get('getTimeTable_'.$stiId);
        if ($redisTimeTable != null){
            return response()->json(array('result_code' => 1, 'result_body' => json_decode($redisTimeTable)));
        } else {
            $dis = 'student';
            $loginPage = 'https://student.donga.ac.kr/Login.aspx';
            $result = $getDonga->getUserInfo($request)->getDongaPage($loginPage,$dis);
            if ($result["result_code"] == 1) {
                $targetPage = 'https://student.donga.ac.kr/Univ/SUG/SSUG0020.aspx?m=3';
                $user_id = $result["user_id"];
                $client = $result["client"];

                try {
                    $crawlerTable = $client->request('GET', $targetPage);
                    $form = $crawlerTable->selectButton('ImageButton1')->form();
                    $crawler = $client->submit($form, array('ddlYear' => 2017, 'ddlSmt' => 20));
                    $arr = array();
                    $crawler->filter('table#dgRep')->filter('tr')->filter('td')->each(function ($node, $i) use (&$arr) {
                        if ($i>15){
                            $arr[] = trim($node->text());
                        }
                    });
                    $chArr = array_chunk($arr, 15);
//                    $expiresAt = Carbon::now()->addMinutes(60);
//                    Cache::put('getTimeTable_'.$user_id, json_encode($chArr), $expiresAt);
                    Redis::set('getTimeTable_'.$user_id,  json_encode($chArr));
                    return response()->json(array('result_code' => 1, 'result_body' => $chArr));
                } catch (\Exception $e){
                    $fail = $result["page"]->filter("span#lblError")->text();
                    if (str_contains($fail,"학번")){
                        $result_code = 3;
                    }
                    else {
                        $result_code = 500;
                    }
                    return response()->json(array('result_code' => $result_code));
                }
            } else {
                return response()->json(["result_code" => $result["result_code"]]);
            }
        }
    }

    //클래스 획득
    public function getEmptyClass()
    {
        $user_id = "1124305";
        $user_pw = "Ekfqlc152!";
        $client = new \Goutte\Client();
        $guzzleClient = new \GuzzleHttp\Client(array(
            'timeout' => 90,
            'verify' => false,
        ));
        $client->setClient($guzzleClient);
        $crawlerLogin = $client->request('GET', 'https://student.donga.ac.kr/Login.aspx');
        $form = $crawlerLogin->selectButton('ibtnLogin')->form();
        $crawler = $client->submit($form, array('txtStudentCd' => $user_id, 'txtPasswd' => $user_pw));
        $cookies = $client->getCookieJar()->all();
        $client->getCookieJar()->set($cookies[0]);
//        $crawlerTable = $client->request('GET', 'https://student.donga.ac.kr/Univ/SUE/SSUE0040.aspx?m=2');
//        $form = $crawlerTable->selectButton('ibtnSearch')->form();
//        $page = $client->submit($form, array('ddlBld' => "B04"));
//        $crawlerRoom = $page->filter('table#dgRoomList');
//        $keys = array();
//        $values = array();
//        $crawlerRoom->filter('a')->each(function ($node) use(&$keys) {
//           $keys[] = $node->text();
//        });
//         $crawlerRoom->filter('tr')->each(function ($node) use(&$values) {
//             $values[] = $node->filter('td')->eq(1)->text();
//         });
//         for ($i = 0;$i<count($keys);$i++){
//             Room::create(array('room_no'=>$values[$i],'room_name'=>$keys[$i]));
//         }
        $rooms =  Room::all('id','room_no');
        $auto = 1;
        foreach ($rooms as $room)
        {

            $crawlerTable = $client->request('GET', 'https://student.donga.ac.kr/Univ/SUE/SSUE0041_75.aspx?m=2&id='.$room->room_no.'&bld=B04');
            $form2 = $crawlerTable->selectButton('ibtnSearch')->form();
            $crawler2 = $client->submit($form2, array('ddlYear' => "2017", 'ddlSmt' => "20"));
            for ($j=1;$j<6;$j++){
                $arr = array();
                $crawler2->filter('table#gvList')->filter('tr')
                    ->each(function ($node) use (&$arr,&$j){
                        $arr[] = $node->filter('td')->eq($j)->text();
                    });
                for ($i=1;$i<count($arr);$i++){
                    if ($arr[$i]===" "){
                        file_put_contents('/Users/qazz92/test2.txt',$auto.','.$j.','.$i.','.'빈 강의실,빈 강의실,'.$room->id.';',FILE_APPEND);
                        $auto = $auto + 1;
                    }else {
                        $removeRoom = str_replace($room->room_no,"",$arr[$i]);
                        $splitNo = str_replace('&nbsp'," ",$removeRoom);
                        $splitNo = preg_replace('/^([^\s]+)\s/','$1,',$splitNo);
                        $finalResult = explode(',',$splitNo);
                        file_put_contents('/Users/qazz92/test2.txt',$auto.','.$j.','.$i.','.$finalResult[0].','.$finalResult[1].','.$room->id.';',FILE_APPEND);
                        $auto = $auto + 1;
                    }
                }
            }
        }

    }

    // 빈강의실
    public function getEmptyRoom(Request $request)
    {
        $day = intval($request->input("day"));
        $from = intval($request->input("from"));
        $to = intval($request->input("to"));

        $redisGetEmptyRoom = Redis::get('empty_' . $day . '_' . $from . '_' . $to);
        if ($redisGetEmptyRoom == null) {
            try {
                $result = DB::table('timeTables')->select(DB::raw('count(*) as count, room_no'))
                    ->leftJoin('rooms', 'room_id', '=', 'rooms.id')
                    ->where('day', '=', $day)
                    ->where('subject_code', '=', '빈 강의실')
                    ->whereBetween('time', [$from, $to])
                    ->groupBy('room_id')
                    ->having('count', '=', ($to - $from + 1))
                    ->get();
                Redis::set('empty_' . $day . '_' . $from . '_' . $to, $result);
                Log::info('empty_' . $day . '_' . $from . '_' . $to . ' | REDIS SET');
                return response()->json(array("result_code" => 1, "result_body" => $result));
            } catch (\Exception $e) {
                return response()->json(array("result_code" => 0, "result_body" => "none"));
            }
        } else {
            Log::info('empty_' . $day . '_' . $from . '_' . $to . ' | REDIS GET');
            return response()->json(array("result_code" => 1, "result_body" => json_decode($redisGetEmptyRoom)));
        }
    }

    // 열람실
    public function getWebSeat()
    {
        $client = new \Goutte\Client();
        try {
            $crawler = $client->request('GET', "http://168.115.33.207/WebSeat");
            $crawlerTable = $crawler->filter('table')->eq(1);
            $arrResult = array();
            for ($j = 12; $j < 23; $j++) {
                $temp = array();
                $row = $crawlerTable->filter('tr')->eq($j);
                for ($i = 0; $i < 5; $i++) {
                    $result = $row->filter('td')->eq($i)->text();
                    $removeResult = str_replace("\xc2\xa0", '', $result);
                    switch ($i) {
                        case 0:
                            $temp["loc"] = $removeResult;
                            break;
                        case 1:
                            $temp["all"] = $removeResult;
                            break;
                        case 2:
                            $temp["use"] = $removeResult;
                            break;
                        case 3:
                            $temp["remain"] = $removeResult;
                            break;
                        case 4:
                            $removeResult = preg_replace('/^\s+|\s+%/', '', $removeResult);
                            $temp["util"] = $removeResult;
                            break;
                    }
                }
                $arrResult[$j - 12] = $temp;
            }
            return response()->json(array("result_code" => 1, "result_body" => $arrResult));
        } catch (\Exception $e) {
            return response()->json(array("result_code" => 0, "result_body" => "none"));
        }
    }

    public function getPro(Request $request){
        $major = $request->input('major');
        $redisPro = Redis::get('professor_list_'.$major);
        if ($redisPro == null){
            try {
                $result = Professor::where('major',trim($major))->get();
                Redis::set('professor_list_'.$major,$result);
                return response()->json(['result_code'=>1,'result_body'=>$result]);
            } catch (\Exception $e) {
                return response()->json(['result_code'=>0]);
            }
        } else {
            return response()->json(['result_code'=>1,'result_body'=>json_decode($redisPro)]);
        }
    }
}
