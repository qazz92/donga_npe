<?php

namespace App\Http\Controllers;

use App\Meal;
use App\Room;
use App\TimeTable;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DongaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function meal($year,$month,$day){
        $date =  $year.'/'.$month.'/'.$day;
        $findMeal = Meal::where('meal_date', '=', $date)->first();
        if (! $findMeal) {
            Log::info($date." | 크롤링 시작");
            $client = new \Goutte\Client();
            $crawler = $client->request('GET',"http://www.donga.ac.kr/MM_PAGE/SUB007/SUB_007005005.asp?PageCD=007005005&seldate=2017-02-01");
            $result =  $crawler->filter('div#subContext > table')->eq(0)->filter('tr')
                ->eq(1)->filter('table.sk01TBL')->eq(1)->
                filter('table.sk03TBL')->filter('td');
//        $kyo = $this->explodeResult($result,0);
//        $student = $this->explodeResult($result,1);
            $inter = $this->explodeResult($result,7);
            $bumin_kyo = $this->explodeResult($result,8);
            $gang = $this->explodeResult($result,9);
            $resultArr = array(["inter"=>$inter,"bumin_kyo"=>$bumin_kyo,"gang"=>$gang]);

            try{
                $insertResult = Meal::create(array('meal_date' => $date,'meal_contents'=>$resultArr));
                return response()->json($insertResult);
            } catch (\Exception $e) {
                return response()->json(array(["result_code"=>"db_error"]));
            }

        } else {
            Log::info($date." |  db 출력");
            return response()->json($findMeal);
        }

//        echo $dbResult;
    }
    function explodeResult($result,$index){
        $result = $result->eq($index)->text();
        $fixResult =  preg_replace('/\)/', ") $1", $result);
        $fixResult = str_replace("\n"," ",$fixResult);
        $fixResult = str_replace("  "," ",$fixResult);
        $pieces = explode (" ", trim($fixResult));
        return $pieces;
    }

    public function dongaUnivLogin(Request $request){
        $user_id = $request->input("stuId");
        $user_pw = $request->input("stuPw");
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
        $crawlerTable = $client->request('GET', 'https://student.donga.ac.kr/Univ/SUD/SSUD0000.aspx?m=1');
        try {
            $name = $crawlerTable->filter('table#Table4 > tr')->eq(0)->filter('td')->eq(2)->filter('span#lblKorNm')->text();
            return response()->json(array("name"=>$name));
        } catch (\Exception $e) {
            return response()->json(array("name"=>"0"));
        }
    }

    public function getEmptyClass()
    {
        echo "error";
//        $user_id = "1124305";
//        $user_pw = "Ekfqlc152!";
//        $client = new \Goutte\Client();
//        $guzzleClient = new \GuzzleHttp\Client(array(
//            'timeout' => 90,
//            'verify' => false,
//        ));
//        $client->setClient($guzzleClient);
//        $crawlerLogin = $client->request('GET', 'https://student.donga.ac.kr/Login.aspx');
//        $form = $crawlerLogin->selectButton('ibtnLogin')->form();
//        $crawler = $client->submit($form, array('txtStudentCd' => $user_id, 'txtPasswd' => $user_pw));
//        $cookies = $client->getCookieJar()->all();
//        $client->getCookieJar()->set($cookies[0]);
//        $crawlerTable = $client->request('GET', 'https://student.donga.ac.kr/Univ/SUE/SSUE0040.aspx?m=2&bld=63');
//        $crawlerRoom = $crawlerTable->filter('table#dgRoomList');
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
//        $rooms =  Room::all('id','room_no');
//        $auto = 1;
//        foreach ($rooms as $room)
//        {
//
//            $crawlerTable = $client->request('GET', 'https://student.donga.ac.kr/Univ/SUE/SSUE0041_75.aspx?m=2&id='.$room->room_no.'&bld=63');
//            $form2 = $crawlerTable->selectButton('ibtnSearch')->form();
//            $crawler2 = $client->submit($form2, array('ddlYear' => "2017", 'ddlSmt' => "10"));
//            for ($j=1;$j<6;$j++){
//                $arr = array();
//                $crawler2->filter('table#gvList')->filter('tr')
//                    ->each(function ($node) use (&$arr,&$j){
//                        $arr[] = $node->filter('td')->eq($j)->text();
//                    });
//                for ($i=1;$i<count($arr);$i++){
//                    if ($arr[$i]===" "){
////                        echo $i." 빈 강의실<br/>";
////                        TimeTable::create(array("day"=>$j,"time"=>$i,"subject_code"=>"빈 강의실","subject_name"=>"빈 강의실","room_id"=>$room->id));
//                        file_put_contents('/Users/qazz/test.txt',$auto.','.$j.','.$i.','.'빈 강의실,빈 강의실,'.$room->id.';',FILE_APPEND);
//                        $auto = $auto + 1;
//                    }else {
//                        $removeRoom = str_replace($room->room_no,"",$arr[$i]);
//                        $splitNo = str_replace('&nbsp'," ",$removeRoom);
//                        $splitNo = preg_replace('/^([^\s]+)\s/','$1,',$splitNo);
//                        $finalResult = explode(',',$splitNo);
////                        echo $i.' '.$finalResult[0].'  |   '.$finalResult[1].'<br/>';
////                        TimeTable::create(array("day"=>$j,"time"=>$i,"subject_code"=>$finalResult[0],"subject_name"=>$finalResult[1],"room_id"=>$room->id));
//                        file_put_contents('/Users/qazz/test.txt',$auto.','.$j.','.$i.','.$finalResult[0].','.$finalResult[1].','.$room->id.';',FILE_APPEND);
//                        $auto = $auto + 1;
//                    }
//                }
//            }
//        }

    }

    public function getEmptyRoom(Request $request){
//        $results = DB::select( DB::raw('SELECT b.room_no as room_no FROM timeTables a LEFT JOIN rooms b ON a.room_id = b.id WHERE a.day=1 AND (a.time BETWEEN 3 and 5) AND a.subject_code=\'빈 강의실\' GROUP BY a.room_id HAVING count(*)=3') );
        $day = $request->input("day");
        $from = $request->input("from");
        $to = $request->input("to");

        $result = DB::table('timeTables')->select(DB::raw('count(*) as count, room_no'))
            ->leftJoin('rooms', 'room_id', '=', 'rooms.id')
            ->where('day','=',$day)
            ->where('subject_code','=','빈 강의실')
            ->whereBetween('time', [$from, $to])
            ->groupBy('room_id')
            ->having('count', '=', ($from-$to+1))
            ->get();
        return response()->json(array("result_code"=>"ok","result_body"=>$result));
    }
    public function getWebSeat(){
        $client = new \Goutte\Client();
        $crawler = $client->request('GET',"http://168.115.33.207/WebSeat");
        $crawlerTable = $crawler->filter('table')->eq(1);
        $arrResult = array();
        for ($j=12;$j<23;$j++){
            $temp = array();
            $row = $crawlerTable->filter('tr')->eq($j);
            for ($i=0;$i<5;$i++){
                $result = $row->filter('td')->eq($i)->text();
                $removeResult = str_replace("\xc2\xa0",'',$result);
                switch ($i) {
                    case 0:
                        $temp["loc"]=$removeResult;
                        break;
                    case 1:
                        $temp["all"]=$removeResult;
                        break;
                    case 2:
                        $temp["use"]=$removeResult;
                        break;
                    case 3:
                        $temp["remain"]=$removeResult;
                        break;
                    case 4:
                        $removeResult = preg_replace('/\s/','',$removeResult);
                        $temp["util"]=$removeResult;
                        break;
                }
            }
            $arrResult[$j-12] = $temp;
        }
        return response()->json(array("result_code"=>"ok","result_body"=>$arrResult));
    }
}
