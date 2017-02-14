<?php

namespace App\Http\Controllers;
use App\Normal_User;
use App\Services\ComputingUsage;

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
    public function getIndex(){
        $users = Normal_User::paginate(15);
//        return $users;
        return view('admin')->with('users',$users);
    }
}
