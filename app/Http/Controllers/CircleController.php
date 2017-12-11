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

class CircleController extends Controller
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

    public function removeCircle(Request $request){
        $ids = $request->input('targets');
        try {
            $result = User_Circle::destroy($ids);
            if ($result>0){
                $olds = $request->input('olds');

            }
            return response()->json(array('result_code'=>1,'result_body'=>$result));
        } catch (QueryException $e){
            return response()->json(array("result_code" => 500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }
    }
    public function checkCircle(Request $request){
        $user_id = $request->input('user_id');
        try {
            $check = User_Circle::where('user_id','=',$user_id)->get();


            if ($check->isEmpty()){
                return response()->json(array('result_code'=>0));
            } else {
                return response()->json(array('result_code'=>1,'result_body'=>$check));
            }
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        }

    }
    public function getCircle(Request $request){
        $major = $request->input('major');
        try {
            $circles = Circle::where('major','=',$major)->get();
            if ($circles->isEmpty()){
                return response()->json(array('result_code'=>500));
            }
            return response()->json(array('result_code'=>1,'result_body'=>$circles));
        } catch (QueryException $e){
            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }
    }
    public function setCircle(Request $request){
        $user_id = $request->input('user_id');
        $circles = $request->input('circles');
//        $circle_id = $request->input('circle_id');
        try {
            foreach ($circles as $circle){
                $setCircle = new User_Circle();
                $setCircle->user_id = $user_id;
                $setCircle->circle_id = $circle;
                $setCircle->save();
            }
            return response()->json(array('result_code'=>1));
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            Log::info($e);
            return response()->json(array('result_code'=>0));
        }

    }
    public function getUserCircle(Request $request){
        $user_id = $request->input('user_id');
        try {
            $result =
                DB::table('user_circles')
                    ->join('circles','circles.id','user_circles.circle_id')
                    ->select('circles.id as id','circles.name as name')
                    ->where('user_circles.user_id','=',$user_id)
                    ->get();
            return response()->json(array('result_code'=>1,'result_body'=>$result));
        } catch (QueryException $e){
            echo $e;
//            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }
    }
    public function updateCircle(Request $request){
        $user_id = $request->input('user_id');
        $news = $request->input('news');

        try {
            $result = User_Circle::where('user_id',$user_id)->delete();
            if ($result>0){
                foreach ($news as $new){
                    $setCircle = new User_Circle();
                    $setCircle->user_id = $user_id;
                    $setCircle->circle_id = $new;
                    $setCircle->save();
                }
            }
            return response()->json(array('result_code'=>1));
        } catch (QueryException $e){
            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }
    }
    public function setNoneCircle(Request $request){
        $user_id = $request->input('user_id');
        try {
            $setCircle = new User_Circle();
            $setCircle->user_id = $user_id;
            $setCircle->circle_id = 8;
            $setCircle->save();
            return response()->json(array('result_code'=>1));
        } catch (QueryException $e) {
            return response()->json(array('result_code'=>500));
        } catch (\Exception $e){
            return response()->json(array('result_code'=>0));
        }
    }
}
