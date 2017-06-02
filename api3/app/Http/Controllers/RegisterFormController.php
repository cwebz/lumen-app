<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

use App\Models\Mfl_temporary_url;

class RegisterFormController extends Controller
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


    public function index(Request $request){
        //Check the timestamp of the query param
        $tokenParam = $request->query('token');

        //Check the DB for this param and get the timestamp
        $validUrl = Mfl_temporary_url::where(
                            'param', $tokenParam
                            )
                            ->where(
                                'created_at', '>', Carbon::now()->subHour()->toDateTimeString()
                            )
                            ->get();
        
        //If this returns nothing they expired, no form for them.
        if( $validUrl->isEmpty() ){
            return "404";
        }
        
        //Give them the form to fill out
        var_dump($request->query('token'));
        return view('mfl-slack-form');
    }
    
    public function verify(Request $request){
        var_dump($request->input());
        return "hazaaarr";
    }
    /**
    * Use the form to get MFL user cookie
    *
    * @param Request $request
    * @return json
    */
    public static function registerUser(Request $request){
        //Get form field info, attempt to sign-in, get cookie
        //slack/email me, return view of success
        
    }
}
