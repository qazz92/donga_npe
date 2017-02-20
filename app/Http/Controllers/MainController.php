<?php

namespace App\Http\Controllers;

use App\Circle_Noti;
use App\Device;
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

    public function index()
    {
        echo "Hello";
    }

    //동아리 회장 회원가입
    public function reg(Request $request)
    {
        $identi_ok = 1;
        $identi_error = 0;
        $identi_exist = 2;
        $email = $request->input("email");
        $pw = $request->input("password");
        if (User::where('email', '=', $email)->get()->isEmpty()) {
            try {
                $user = new User();
                $user->email = $email;
                $user->password = app('hash')->make($pw);
                $user->user_id = 3;
                $result = $user->save();
                if ($result) {
                    return response()->json(["result_code" => $identi_ok]);
                } else {
                    return response()->json(["result_code" => $identi_error]);
                }
            } catch (\Exception $e) {
                Log::info("USER INSERT ERROR !! : " . $e);
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
            Log::info("이미 존재합니다. !!" . $getDevice[0]->push_service_id);
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code" => $identi_exist]);
        }

    }


    public function privacy(){
        return view('privacy');
    }
    public function getNormalNotis(Request $request){
        $user_id = $request->input('id');
        $notis = Noti::where('user_id','=',$user_id);
        return response()->json($notis);
    }
    public function getCircleNotis(Request $request){
        $user_id = $request->input('id');
        $notis = Circle_Noti::where('user_id','=',$user_id);
        return response()->json($notis);
    }
}
