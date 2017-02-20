<?php

namespace App\Http\Controllers;
use App\Circle_Noti;
use App\Normal_User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\PCircle_Noti;
use App\Pnoti;
use App\Noti;
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
        $members = Normal_User::where('circle_id','=',Auth::user()["circle_id"]);
        return response()->json($members);
    }
    public function normal_fcm(Request $request, FCMHandler $fcm)
    {
        $title = $request->input('title');
        $body = $request->input('body');
        $contents = $request->input('contents');
        $circle_id = Auth::user()["circle_id"];
        $admin_id = Auth::user()["id"];
        Log::info($admin_id.'|'.$circle_id);
        $to = DB::table('devices')
            ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
            ->select('normal_users.id as uid', 'devices.push_service_id as pid')
            ->where('normal_users.circle_id', '=', $circle_id)
            ->pluck('pid', 'uid')->toArray();
//        $to = Device::pluck('push_service_id','id')->toArray();
        if (!empty($to)) {
            $message = ['contents' => $contents,'category'=>'normal'];

            $fcm->to(array_values($to))->notification($title, $body)->data($message)->send();

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
                $file_contents = $id . '|'.$pnotis['id'].'|'.$mytime->toDateTimeString() . ';';
                file_put_contents($text, $file_contents, FILE_APPEND);
            }
            $query = "LOAD DATA LOCAL INFILE '" . $text . "'
            INTO TABLE notis
            FIELDS TERMINATED BY '|' LINES TERMINATED BY ';'
            (user_id, pnotis_id ,created_at) SET id = NULL;";
            DB::connection()->getpdo()->exec($query);
        }
        return response()->json([
            'success' => 'HTTP 요청 처리 완료'
        ]);
    }
    //fcm push
    public function circle_fcm(Request $request, FCMHandler $fcm)
    {
        //echo $user->created_at->format('Y-m-d h:i:s');

        $title = $request->input('title');
        $body = $request->input('body');
        $contents = $request->input('contents');
        $circle_id = Auth::user()["circle_id"];
        $admin_id = Auth::user()["id"];
        Log::info($admin_id.'|'.$circle_id);
        $to = DB::table('devices')
            ->join('normal_users', 'normal_users.id', '=', 'devices.user_id')
            ->select('normal_users.id as uid', 'devices.push_service_id as pid')
            ->where('normal_users.circle_id', '=', $circle_id)
            ->pluck('pid', 'uid')->toArray();
//        $to = Device::pluck('push_service_id','id')->toArray();
        if (!empty($to)) {
            $message = ['contents' => $contents,'category'=>'circle'];

            $fcm->to(array_values($to))->notification($title, $body)->data($message)->send();

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
                $file_contents = $id . '|'.$pcnotis['id'].'|0|'.$mytime->toDateTimeString() . ';';
                file_put_contents($text, $file_contents, FILE_APPEND);
            }
            $query = "LOAD DATA LOCAL INFILE '" . $text . "'
            INTO TABLE circle_notis
            FIELDS TERMINATED BY '|' LINES TERMINATED BY ';'
            (user_id, pcircle_notis_id , check_att , created_at) SET id = NULL;";
            DB::connection()->getpdo()->exec($query);
        }
        return response()->json([
            'success' => 'HTTP 요청 처리 완료'
        ]);
    }

    public function getPNormalNotis(){
        $admin_id = Auth::user()["id"];
        $pnotis = Pnoti::where('admin_id','=',$admin_id);
        return response()->json($pnotis);
    }
    public function getPCircleNotis(){
        $admin_id = Auth::user()["id"];
        $pcnotis = PCircle_Noti::where('admin_id','=',$admin_id);
        return response()->json($pcnotis);
    }
    public function admin_getNormalNotis(Request $request){
        $pnotis_id = $request->input('notis_id');
        $notis = Noti::where('pnotis_id','=',$pnotis_id);
        return response()->json($notis);
    }
    public function admin_getCircleNotis(Request $request){
        $pcnotis_id = $request->input('pcnotis_id');
        $pcnotis = Circle_Noti::where('pcircle_notis_id','=',$pcnotis_id);
        return response()->json($pcnotis);
    }

}
