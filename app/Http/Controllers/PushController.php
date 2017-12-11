<?php

namespace App\Http\Controllers;

use App\Circle_Noti;
use App\Device;
use App\Normal_User;
use App\Noti;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PushController extends Controller
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

    // 전체 FCM 보내기
    public function total_fcm(Request $request, FCMHandler $fcm)
    {
        $article = $request->input('article');
        $title = $article["title"];
        $contents = $article["contents"];
        $os_enum = $request->input("os_enum");
        $isAll = strcmp($os_enum, "ALL");
        if ($isAll){
            try {
                $to = DB::table('devices')
                    ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
                    ->join('user_circles', 'normal_users.id', '=', 'user_circles.user_id')
                    ->select('normal_users.id as uid', 'devices.push_service_id as pid')
                    ->where('normal_users.push_permit', '=', 0)
                    ->where('devices.os_enum', '=', $os_enum)
                    ->pluck('pid', 'uid')->toArray();
            } catch (QueryException $e){
                return response()->json([
                    'result_code' => 500
                ]);
            }
        } else {
            try {
                $to = DB::table('devices')
                    ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
                    ->join('user_circles', 'normal_users.id', '=', 'user_circles.user_id')
                    ->select('normal_users.id as uid', 'devices.push_service_id as pid')
                    ->where('normal_users.push_permit', '=', 0)
                    ->pluck('pid', 'uid')->toArray();
            } catch (QueryException $e){
                return response()->json([
                    'result_code' => 500
                ]);
            }
        }

        if (!empty($to)) {
            $message = ['contents' => $article,'category'=>'total'];
            try {
                $fcm->to(array_values($to))->notification("BOO",$title)->data($message)->send();
            } catch (\Exception $e) {
                return response()->json([
                    'result_code' => 500
                ]);
            }
            try {
                $pubNotice = new PublicNotice();
                $pubNotice->title = $title;
                $pubNotice->contents = $contents;
                $pubNotice->save();


                return response()->json([
                    'result_code' => 1
                ]);
            } catch (QueryException $e){
                return response()->json([
                    'result_code' => 500
                ]);
            }
        }
    }

    // 일반 FCM
    public function normal_fcm(Request $request, FCMHandler $fcm)
    {
        $article = $request->input('article');
        $title = $article["title"];
        $body = $article["body"];
        $contents = $article["contents"];

        try {
            $circle_id = Auth::user()["circle_id"];
            $admin_id = Auth::user()["id"];
        } catch (UnauthorizedException $e) {
            return response()->json([
                'result_code' => 0
            ]);
        }
        try {
            $to = DB::table('devices')
                ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
                ->join('user_circles', 'normal_users.id', '=', 'user_circles.user_id')
                ->select('normal_users.id as uid', 'devices.push_service_id as pid')
                ->where('normal_users.push_permit', '=', 0)
                ->where('user_circles.circle_id', '=', $circle_id)
                ->pluck('pid', 'uid')->toArray();
        } catch (QueryException $e){
            return response()->json([
                'result_code' => 500
            ]);
        }
        if (!empty($to)) {
            $message = ['contents' => $article,'category'=>'normal'];
            try {
//            $fcm->to(array_values($to))->notification($title, $body)->data($message)->send();
                $fcm->to(array_values($to))->notification($title,$body)->data($message)->send();
            } catch (\Exception $e) {
                return response()->json([
                    'result_code' => 500
                ]);
            }
            try {
                $pnotis = new Pnoti();
                $pnotis->admin_id = $admin_id;
                $pnotis->title = $title;
                $pnotis->body = $body;
                $pnotis->data = $contents;
                $pnotis->save();

                $path = storage_path('app/notis/');
                $text = $path . date("Y-m-d h:i:s") . '_' . str_random(16) . '.txt';
                $ids = array_keys($to);
                foreach ($ids as $id) {
                    $mytime = Carbon::now();
                    $file_contents = $id . '|'.$pnotis['id'].'|'.$mytime->toDateTimeString() .'|0'. ';';
                    file_put_contents($text, $file_contents, FILE_APPEND);
                }
                $query = "LOAD DATA LOCAL INFILE '" . $text . "'
            INTO TABLE notis
            FIELDS TERMINATED BY '|' LINES TERMINATED BY ';'
            (user_id, pnotis_id ,created_at, read_check) SET id = NULL;";
                DB::connection()->getpdo()->exec($query);
                return response()->json([
                    'result_code' => 1
                ]);
            } catch (QueryException $e){
                return response()->json([
                    'result_code' => 500
                ]);
            }
        }
    }
    // 동아리 참석 여부 FCM
    public function circle_fcm(Request $request, FCMHandler $fcm)
    {
        $article = $request->input('article');
        $title = $article["title"];
        $body = $article["body"];
        $contents = $article["contents"];
        try {
            $circle_id = Auth::user()["circle_id"];
            $admin_id = Auth::user()["id"];
        } catch (AuthenticationException $e){
            return response()->json([
                'result_code' => 0
            ]);
        }
        try {
            $to = DB::table('devices')
                ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
                ->join('user_circles', 'normal_users.id', '=', 'user_circles.user_id')
                ->select('normal_users.id as uid', 'devices.push_service_id as pid')
                ->where('normal_users.push_permit', '=', 0)
                ->where('user_circles.circle_id', '=', $circle_id)
                ->pluck('pid', 'uid')->toArray();
        } catch (QueryException $e){
//            echo $e;
            return response()->json([
                'result_code' => 500
            ]);
        }
        if (!empty($to)) {
            $message = ['contents' => $article,'category'=>'circle'];

            try {
                $fcm->to(array_values($to))->notification($title,$body)->data($message)->send();
            } catch (\Exception $e){
//                echo $e;
                return response()->json([
                    'result_code' => 500
                ]);
            }

            try {
                $pcnotis = new PCircle_Noti();
                $pcnotis->admin_id = $admin_id;
                $pcnotis->title = $title;
                $pcnotis->body = $body;
                $pcnotis->data = $contents;
                $pcnotis->save();

                $path = storage_path('app/cnotis/');
                $text = $path . date("Y-m-d h:i:s") . '_' . str_random(16) . '.txt';
                $ids = array_keys($to);
                foreach ($ids as $id) {
                    $mytime = Carbon::now();
                    $file_contents = $id . '|'.$pcnotis['id'].'|0|'.$mytime->toDateTimeString() .'|0'.';';
                    file_put_contents($text, $file_contents, FILE_APPEND);
                }
                $query = "LOAD DATA LOCAL INFILE '" . $text . "'
            INTO TABLE circle_notis
            FIELDS TERMINATED BY '|' LINES TERMINATED BY ';'
            (user_id, pcircle_notis_id , check_att , created_at, read_check) SET id = NULL;";
                DB::connection()->getpdo()->exec($query);
                return response()->json([
                    'result_code' => 1
                ]);
            } catch (\Exception $e){
//                echo $e;
                return response()->json([
                    'result_code' => 500
                ]);
            }
        }
    }


    // 보낸 일반 메시지
    public function getPNormalNotis(){
        try{
            $admin_id = Auth::user()["id"];
            $pnotis = Pnoti::where('admin_id','=',$admin_id)->orderBy('created_at','desc')->get();
            return response()->json(array('result_code'=>1,'result_body'=>$pnotis));
        } catch (QueryException $e){
            return response()->json(array('result_code'=>500));
        }
    }

    // 보낸 동아리 메시지
    public function getPCircleNotis(){
        try {
            $admin_id = Auth::user()["id"];
            $pcnotis = PCircle_Noti::where('admin_id','=',$admin_id)->orderBy('created_at','desc')->get();
            return response()->json(array('result_code'=>1,'result_body'=>$pcnotis));
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        }
    }

    // 특정 보낸 동아리 메시지
    public function admin_getCircleNotis(Request $request){
        try {
            $pcnotis_id = $request->input('pcnotis_id');
//            $pcnotis = Circle_Noti::where('pcircle_notis_id','=',$pcnotis_id)->get();

            $pcnotis = DB::table('circle_notis')
                ->join('normal_users', 'normal_users.id', '=', 'circle_notis.user_id')
                ->select('normal_users.stuId as stuId','normal_users.name as name','circle_notis.check_att as att')
                ->where('circle_notis.pcircle_notis_id', '=', $pcnotis_id)
                ->get();

            return response()->json(array('result_code'=>1,'result_body'=>$pcnotis));
        } catch (QueryException $e){
            return response()->json(array('result_code'=>500));
        }
    }

    // 받은 일반 메시지
    public function getNormalNotis(Request $request){
        $user_id = $request->input('user_id');
        try {
            $notis = DB::table('notis')
                ->join('pnotis', 'pnotis.id', '=', 'notis.pnotis_id')
                ->select('notis.id as id','pnotis.title as title','pnotis.body as body','pnotis.data as contents','notis.read_check as read_check','notis.created_at as getTime')
                ->where('notis.user_id', '=', $user_id)
                ->orderBy('notis.created_at','desc')
                ->get();
            return response()->json(array('result_code'=>1,'result_body'=>$notis));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>500));
        }
    }

    // 받은 동아리 메시지
    public function getCircleNotis(Request $request){
        $user_id = $request->input('user_id');
        try {
            $notis = DB::table('circle_notis')
                ->join('pcircle_notis', 'pcircle_notis.id', '=', 'circle_notis.pcircle_notis_id')
                ->select('circle_notis.id as id','pcircle_notis.title as title','pcircle_notis.body as body','pcircle_notis.data as contents','circle_notis.read_check as read_check','circle_notis.created_at as getTime')
                ->where('circle_notis.user_id', '=', $user_id)
                ->orderBy('circle_notis.created_at','desc')
                ->get();
            return response()->json(array('result_code'=>1,'result_body'=>$notis));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>500));
        }
    }

    // 일반 메시지 읽기
    public function normal_read(Request $request){
        $notis_id = $request->input('notis_id');
        try {
            $read = Noti::find($notis_id);
            $read->read_check = 1;
            $read->save();
            return response()->json(array('result_code'=>1));
        } catch (\Exception $e) {
            return response()->json(array('result_code'=>500));
        }
    }

    // 동아리 메시지 읽기
    public function circle_read(Request $request){
        $circle_notis_id = $request->input('circle_notis_id');
        try {
            $read = Circle_Noti::find($circle_notis_id);
            $read->read_check = 1;
            $read->save();
            return response()->json(array('result_code'=>1));
        } catch (\Exception $e) {
            return response()->json(array('result_code'=>500));
        }
    }

    // 참석여부
    public function change_att(Request $request){
        $circle_notis_id = $request->input('circle_notis_id');
        $att = $request->input('att');
        try {
            $check_att = Circle_Noti::find($circle_notis_id);
            $check_att->check_att = $att;
            $check_att->save();
            return response()->json(array('result_code'=>1));
        } catch (\Exception $e) {
            echo $e;
//            return response()->json(array('result_code'=>500));
        }
    }

    // 푸쉬 동의
    public function change_push_permit(Request $request){
        $user_id = $request->input('user_id');
        try {
            $user = Normal_User::find($user_id);
            $permit_flag = $user->push_permit;
            if ($permit_flag == 0){
                $user->push_permit = 1;
                $user->save();
                return response()->json(array('result_code'=>1,'result_body'=>1));
            } else {
                $user->push_permit = 0;
                $user->save();
                return response()->json(array('result_code'=>1,'result_body'=>0));
            }
        } catch (\Exception $e){
            return response()->json(array('result_code'=>500));
        }
    }

    // 일반 메시지 삭제
    public function removeNormalNotis(Request $request){
        $targets = $request->input('targets');
//        Log::info("target : ".$targets);
        try {
            Noti::destroy($targets);
            return response(array('result_code'=>1));
        } catch (QueryException $e){
            return response(array('result_code'=>500));
        } catch (\Exception $e){
            return response(array('result_code'=>0));
        }
    }

    // 동아리 메시지 삭제
    public function removeCircleNotis(Request $request){
        $targets = $request->input('targets');
        try {
            Circle_Noti::destroy($targets);
            return response(array('result_code'=>1));
        } catch (QueryException $e){
            return response(array('result_code'=>500));
        } catch (\Exception $e){
            return response(array('result_code'=>0));
        }
    }
}
