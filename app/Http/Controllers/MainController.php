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
        $now = Carbon::now();
        echo $now;

    }
    public function privacy(){
        return view('privacy');
    }

}
