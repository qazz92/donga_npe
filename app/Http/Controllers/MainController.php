<?php

namespace App\Http\Controllers;

use App\Device;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    //
    public function index(){
//        $user = new User();
//        $user->email = 'test@test.com';
//        $user->password = app('hash')->make('1234');
//        $user->save();
//        echo "success";
        echo "Hello";
    }
//    public function test(){
//        echo Auth::user()["email"].' 님 반갑습니다.';
//    }
    public function reg(Request $request){
        $email = $request->input("email");
        $pw = $request->input("password");
        $user = new User();
        $user->email = $email;
        $user->password = app('hash')->make($pw);
        $result = $user->save();

        if ($result>0){
            return response()->json(["result_code"=>1]);
        } else {
            return response()->json(["result_code"=>0]);
        }

    }
    public function deviceInsert(Request $request){
        $device_id = $request->input("device_id");
        $os_enum = $request->input("os_enum");
        $model = $request->input("model");
        $operator = $request->input("operator");
        $api_level = $request->input("api_level");
        $push_service_id = $request->input("push_service_id");
        try {
            $device = new Device();
            $device->user_id = Auth::user()["id"];
            $device->device_id = $device_id;
            $device->os_enum = $os_enum;
            $device->model = $model;
            $device->operator = $operator;
            $device->api_level = $api_level;
            $device->push_service_id = $push_service_id;
            $result = $device->save();

            if ($result>0){
                return response()->json(["result_code"=>1]);
            } else {
                return response()->json(["result_code"=>0]);
            }
        } catch (\Exception $e){
            Log::info("DEVICE INSERT ERROR !! : ".$e);
        }
    }
}
