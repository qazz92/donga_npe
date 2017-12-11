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

class DeviceController extends Controller
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


    // fcm token 확인
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
    // fcm token 업데이트
    public function deviceUpdate(Request $request)
    {
        $identi_ok = 1;
        $identi_error = 0;


        $device_id = $request->input("device_id");
        $os_enum = $request->input("os_enum");
        $model = $request->input("model");
        $operator = $request->input("operator");
        $api_level = $request->input("api_level");
        $push_service_id = $request->input("push_service_id");
        $normal_user_id = $request->input("normal_user_id");

//        $result_row = DB::table('devices')
//            ->where('user_id','=',$normal_user_id)
//            ->orderBy('updated_at', 'desc')
//            ->limit(100)
//            ->offset(2)
//            ->delete();

        $result_row = DB::table('devices')
            ->select('id')
            ->where('user_id','=',$normal_user_id)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->offset(1)
            ->pluck('id')
            ->toArray();

//        Log::info($result_row->get());
        if (sizeof($result_row)==0){
            Log::info($normal_user_id." 의 디바이스는 1개입니다.");
        } else {
            $deleted = DB::table('devices')->whereIn('id', $result_row)->delete();
            Log::info($normal_user_id."의 devices ".$deleted." 개 지워졌습니다.");
        }

        try {
            $getDevice = Device::where('user_id', '=', $normal_user_id)->orderBy('updated_at', 'desc')->get();
            $getDevice[0]->device_id = $device_id;
            $getDevice[0]->os_enum = $os_enum;
            $getDevice[0]->model = $model;
            $getDevice[0]->operator = $operator;
            $getDevice[0]->api_level = $api_level;
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code" => $identi_ok]);
        } catch (\Exception $e) {
            return response()->json(["result_code" => $identi_error]);
        }
    }

    // fcm device 추가
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
        $getDevice = Device::where('user_id', '=', $normal_user_id)->get();
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
            Log::info($normal_user_id." 의 디바이스가 이미 존재하므로 업데이트 시작합니다.");
            $getDevice[0]->device_id = $device_id;
            $getDevice[0]->os_enum = $os_enum;
            $getDevice[0]->model = $model;
            $getDevice[0]->operator = $operator;
            $getDevice[0]->api_level = $api_level;
            $getDevice[0]->push_service_id = $push_service_id;
            $getDevice[0]->save();
            return response()->json(["result_code" => $identi_exist]);
        }
    }
}
