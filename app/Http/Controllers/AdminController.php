<?php

namespace App\Http\Controllers;
use App\Circle_Noti;
use App\Normal_User;
use App\User;
use App\User_Circle;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\PCircle_Noti;
use App\Pnoti;
use App\Noti;
use Illuminate\Validation\UnauthorizedException;
use Log;
use App\Services\FCMHandler;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
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
    public function getMembers(){
        try{
            $circle_id = Auth::user()["circle_id"];
//            $members = Normal_User::where('circle_id',Auth::user()["circle_id"])->orderBy('stuId')->get();
            $members = DB::table('normal_users')
                ->join('user_circles', 'normal_users.id', '=', 'user_circles.user_id')
                ->select('normal_users.id as id','normal_users.stuId as stuId','normal_users.name as name','normal_users.major as major','normal_users.push_permit as push_permit','normal_users.created_at as created_at,normal_users.updated_at')
                ->where('user_circles.circle_id', '=', $circle_id)
                ->orderBy('normal_users.created_at','desc')
                ->get();
            return response()->json(array('result_code'=>1,'result_body'=>$members));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>500));
//            echo $e;
        }
    }
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
            $fcm->to(array_values($to))->data($message)->send();
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
    //fcm push
    public function circle_fcm(Request $request, FCMHandler $fcm)
    {
        //echo $user->created_at->format('Y-m-d h:i:s');

        $title = $request->input('title');
        $body = $request->input('body');
        $contents = $request->input('contents');
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
            return response()->json([
                'result_code' => 500
            ]);
        }
        if (!empty($to)) {
            $message = ['contents' => $contents,'category'=>'circle'];

            try {
                $fcm->to(array_values($to))->notification($title, $body)->data($message)->send();
            } catch (\Exception $e){
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
                return response()->json([
                    'result_code' => 500
                ]);
            }
        }
    }


    public function getPNormalNotis(){
        try{
            $admin_id = Auth::user()["id"];
            $pnotis = Pnoti::where('admin_id','=',$admin_id)->orderBy('created_at','desc')->get();
            return response()->json(array('result_code'=>1,'result_body'=>$pnotis));
        } catch (QueryException $e){
            return response()->json(array('result_code'=>500));
        }
    }
    public function getPCircleNotis(){
        try {
            $admin_id = Auth::user()["id"];
            $pcnotis = PCircle_Noti::where('admin_id','=',$admin_id)->orderBy('created_at','desc')->get();
            return response()->json(array('result_code'=>1,'result_body'=>$pcnotis));
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        }
    }
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
    public function removePmk(){
        try {
            $result = User_Circle::destroy(5);
            return response()->json(array('result_code'=>1,'result_body'=>$result));
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }

    }

}
