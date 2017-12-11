<?php

namespace App\Http\Controllers;
use App\Circle_Noti;
use App\Normal_User;
use App\PublicNotice;
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
    // 현재 동아리 회원 보기
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


}
