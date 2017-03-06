<?php

namespace App\Http\Controllers;

use App\Circle_Noti;
use App\Device;
use App\Normal_User;
use App\Noti;
use App\User;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
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

    public function index(Request $request)
    {

    }

    //동아리 회장 회원가입
    public function reg(Request $request)
    {
        $identi_ok = 1;
        $identi_error = 0;
        $identi_exist = 2;
        $email = $request->input("email");
        $pw = $request->input("password");
        $circle_id = $request->input('circle_id');
        if (User::where('email', '=', $email)->get()->isEmpty()) {
            try {
                $user = new User();
                $user->email = $email;
                $user->password = app('hash')->make($pw);
                $user->circle_id = $circle_id;
                $result = $user->save();
                if ($result) {
                    return response()->json(["result_code" => $identi_ok]);
                } else {
//                    echo $result;
                    return response()->json(["result_code" => $identi_error]);
                }
            } catch (\Exception $e) {
//                Log::info("USER INSERT ERROR !! : " . $e);
//                echo $e;
                return response()->json(["result_code" => $identi_error]);
            }
        } else {
            Log::info("이미 존재합니다. !!");
            return response()->json(["result_code" => $identi_exist]);
        }

    }
    public function deviceConfirm(Request $request){
        $user_id = $request->input('id');
        $deivce_id = $request->input('device_id');

        try {
            $confirm = DB::table('devices')
                ->select('user_id')
                ->where('users.id', '=', $user_id)
                ->where('id','=',$deivce_id)
                ->get();

            if ($confirm->isEmpty()){
                DB::table('devices')
                    ->where('users.id', '=', $user_id)
                    ->update(['device_id'=>$deivce_id]);
                return response()->json(['result_code'=>2]);
            } else {
                return response()->json(['result_code'=>1]);
            }
        } catch (\Exception $e) {
            return response()->json(['result_code'=>500]);
        }
    }
    // fcm token update
    public function deviceUpdate(Request $request)
    {
        $identi_ok = 1;
        $identi_error = 0;
        $device_id = $request->input("device_id");
        $push_service_id = $request->input("push_service_id");
        try {
            $getDevice = Device::where('device_id', '=', $device_id)->get();
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code" => $identi_ok]);
        } catch (\Exception $e) {
            return response()->json(["result_code" => $identi_error]);
        }

    }

    // fcm device insert
    public function deviceInsert(Request $request)
    {
        $identi_ok = 1;
        $identi_error = 0;
        $identi_exist = 2;
        $device_id = $request->input("device_id");
        $os_enum = $request->input("os_enum");
        $model = $request->input("model");
        $operator = $request->input("operator");
        $api_level = $request->input("api_level");
        $push_service_id = $request->input("push_service_id");
        $normal_user_id = $request->input("normal_user_id");
        $getDevice = Device::where('device_id', '=', $device_id)->get();
        if ($getDevice->isEmpty()) {
            try {
                $device = new Device();
                $device->user_id = $normal_user_id;
                $device->device_id = $device_id;
                $device->os_enum = $os_enum;
                $device->model = $model;
                $device->operator = $operator;
                $device->api_level = $api_level;
                $device->push_service_id = $push_service_id;
                $result = $device->save();

                if ($result) {
                    Log::info("성공!");
                    return response()->json(["result_code" => $identi_ok]);
                } else {
                    return response()->json(["result_code" => $identi_error]);
                }
            } catch (\Exception $e) {
                Log::info("DEVICE INSERT ERROR !! : " . $e);
                return response()->json(["result_code" => $identi_error]);
            }
        } else {
            Log::info("OLD : " . $getDevice[0]->push_service_id.'| NEW : '.$push_service_id);
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code" => $identi_exist]);
        }

    }


    public function privacy(){
        return view('privacy');
    }
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
    public function change_att(Request $request){
        $circle_notis_id = $request->input('circle_notis_id');
        $att = $request->input('att');
        try {
            $check_att = Circle_Noti::find($circle_notis_id);
            $check_att->check_att = $att;
            $check_att->save();
            return response()->json(array('result_code'=>1));
        } catch (\Exception $e) {
            return response()->json(array('result_code'=>500));
        }
    }
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
}
