<?php

namespace App\Http\Controllers;

use App\Device;
use App\User;
use Illuminate\Http\Request;
use Log;
use App\Services\FCMHandler;

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
    public function index(){
        echo "Hello";
    }

    //동아리 회장 회원가입
    public function reg(Request $request){
        $identi_ok = 1;
        $identi_error = 0;
        $identi_exist = 2;
        $email = $request->input("email");
        $pw = $request->input("password");
        if (User::where('email','=',$email)->get()->isEmpty()){
            try {
                $user = new User();
                $user->email = $email;
                $user->password = app('hash')->make($pw);
                $result = $user->save();
                if ($result){
                    return response()->json(["result_code"=>$identi_ok]);
                } else {
                    return response()->json(["result_code"=>$identi_error]);
                }
            } catch (\Exception $e){
                Log::info("USER INSERT ERROR !! : ".$e);
                return response()->json(["result_code"=>$identi_error]);
            }
        } else {
            Log::info("이미 존재합니다. !!");
            return response()->json(["result_code"=>$identi_exist]);
        }

    }
    // fcm token update
    public function deviceUpdate(Request $request){
        $identi_ok = 1;
        $identi_error = 0;
        $device_id = $request->input("device_id");
        $push_service_id = $request->input("push_service_id");
        try {
            $getDevice = Device::where('device_id','=',$device_id)->get();
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code"=>$identi_ok]);
        }catch (\Exception $e){
            return response()->json(["result_code"=>$identi_error]);
        }

    }

    // fcm device insert
    public function deviceInsert(Request $request){
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
        $getDevice = Device::where('device_id','=',$device_id)->get();
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

                if ($result){
                    Log::info("성공!");
                    return response()->json(["result_code"=>$identi_ok]);
                } else {
                    return response()->json(["result_code"=>$identi_error]);
                }
            } catch (\Exception $e){
                Log::info("DEVICE INSERT ERROR !! : ".$e);
                return response()->json(["result_code"=>$identi_error]);
            }
        } else {
            Log::info("이미 존재합니다. !!".$getDevice[0]->push_service_id);
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code"=>$identi_exist]);
        }

    }

    //fcm push
    public function fcm(Request $request, FCMHandler $fcm){
        $to = Device::pluck('push_service_id')->toArray();

        if (! empty($to)) {
            Log::info("start!");
            $message = array_merge(
                ['foo' => 'bar']
            );
            $fcm->to($to)->notification('title','body')->data($message)->send();
        }
        return response()->json([
            'success' => 'HTTP 요청 처리 완료'
        ]);
    }
}
