<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SlackClass;

class SlackController extends Controller
{
    protected $slackClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->slackClass = new SlackClass();
    }

    /**
    * Handle all requests
    *
    * @param Request $request
    * @return JSON
    */
    public function handleRequest(Request $request)
    {   
        //Get the text header field which has the command
        if($request->header('command') !== '/mfl'){
            return "Not a mfl slash command";
        }

        //Make sure a command was submitted
        if(!$request->header('text')){
            return "No command was submitted";
        }

        //Get the parts of the comman, could be 1 or 2 commands
        $textParts = explode( " ", $request->header('text'));
        
        //The base command
        $command = $textParts[0];
        
        switch ($command) {
            case 'help':
                $this->help($request);
                break;
            
            default:
                # code...
                break;
        }
        var_dump( $textParts );
        //var_dump( $request->header() );
        exit();
    }
    
    /**
    * Return slack message for help command
    *
    * @param Request $request
    * @return JSON
    */
    private function help($request){

        $slackMessage = "Here are the MFL integration commands:";
        $slackMessage .= "\n" . ">*whois*  ~ _Get the team names and ID_";
        $slackMessage .= "\n" . ">*roster* [team_#]  ~ _Get the roster of a team e.g. /mfl roster 4_";
        $slackMessage .= "\n" . ">*picks* [team_#]  ~ _Get the picks of a team e.g. /mfl picks 7_";
        $slackMessage .= "\n" . ">*assets* [team_#]  ~ _Get the roster/picks of a team e.g. /mfl roster 4_";

        $this->slackClass->sendSlackMsg($slackMessage, $request->header('response_url'));
        var_dump($request->header('response_url'));
    }
}