<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SlackClass;

use App\Models\Mfl_slack_integration;
use App\Models\Mfl_franchise_map;
use App\Models\Mfl_players_table;
use App\Models\Mfl_temporary_url;
use App\Http\Controllers\Controller;

class SlackController extends Controller
{
    //protected $slackClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->slackClass = new SlackClass();
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
        if($request->input('command') !== '/mfl'){
            return "Not a mfl slash command";
        }

        //Make sure a command was submitted
        if(!$request->input('text')){
            return "No command was submitted";
        }

        //Get the parts of the comman, could be 1 or 2 commands
        $textParts = explode( " ", $request->input('text'));
        
        //The base command
        $command = $textParts[0];
        
        switch ($command) {
            case 'help':
                echo $this->help($request);
                break;
            case 'whois':
                echo $this->getFranchiseMap($request);
                break;
            case 'roster':
                echo $this->getFranchiseRoster($request, $textParts[1]);           
                break;
            case 'picks':
                echo $this->getFranchisePicks($request, $textParts[1]);
                break;
            case 'assets':
                $slackMessage = $this->getFranchiseRoster($request, $textParts[1]);
                $slackMessage .= "\n";
                $slackMessage .= $this->getFranchisePicks($request, $textParts[1]);
                echo $slackMessage;
            case 'register':
                //Send link to the register page to their slack
                //Some sort of cookie to expire if they take to long 
                $this->sendRegisterLink($request);
                break;
            default:
                # code...
                break;
        }
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

        return $slackMessage;
    }

    /**
    * Return slack message displaying team ID's and name
    *
    * @param Rquest $request 
    * @return JSON
    */
    public function getFranchiseMap($request){
           
        //Get the integrated league based on the request
        $integratedLeague = $this->getIntegratedLeagueFromRequest($request);

        $leagueID = $integratedLeague->mfl_league_id;
        
        //Retreive all franchises that belong to this team
        $franchises = Mfl_franchise_map::where(
            "league_franchise", "LIKE", "%{$leagueID}_%" )
            ->orderBy("league_franchise", "asc")
            ->get();
        
        $slackMessage = 'Franchises and IDs:';
        
        foreach( $franchises as $franchise){
            //Returns League ID and Franchise ID
            $parts = explode('_', $franchise->league_franchise);
            
            $franchiseID = ltrim($parts[1], '0');

            $slackMessage .= "\n" . ">{$franchiseID} - *{$franchise->franchise_name}*";
        }
        
        return $slackMessage;
    }

    /**
    * Return slack message displaying the requested franchises team
    *
    * @param Rquest $request
    * @param array $textParts
    * @return JSON
    */
    public function getFranchiseRoster($request, $franchiseID){

        //Get the integrated league based on the request
        $integratedLeague = $this->getIntegratedLeagueFromRequest($request);

        $leagueID = $integratedLeague->mfl_league_id;
        
        $commishCookie = $integratedLeague->commish_cookie;

        //Convert franchiseID to correct format 000#
        $franchiseID = SlackClass::formatFranchiseID($franchiseID);

        //Retreive all franchises that belong to this team
        $franchiseName = Mfl_franchise_map::findOrFail("{$leagueID}_{$franchiseID}")
                                                ->franchise_name;
        
        ////!!!!!Add error log/slack to msg if team not found

        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('assets', $leagueID);

        //We now need to get the commish cookie for the league
        $mflDataObj = SlackClass::getMflData($mflDataUrl, $commishCookie);

        
        $franchises = $mflDataObj->assets->franchise;

        foreach($franchises as $franchise){
            if($franchise->id === $franchiseID){
                $playerObjs = $franchise->players->player;
                $playerIDs = array_map(function($o){ return $o->id; }, $playerObjs);

                $prettyPlayers = SlackClass::getPrettyRoster($playerIDs);
                $slackMessage = ">*{$franchiseName}* roster:\n>";
                $slackMessage .= implode("\n>", $prettyPlayers);
            }
        }

        return $slackMessage;

    }

    /**
    * Return slack message displaying the requested franchises draft picks
    *
    * @param Rquest $request
    * @param array $textParts
    * @return String $slackMessage
    */
    public function getFranchisePicks($request, $franchiseID){

        //Get the integrated league based on the request
        $integratedLeague = $this->getIntegratedLeagueFromRequest($request);

        //Get the team_id from the request
        $leagueID =  $integratedLeague->mfl_league_id;

        $commishCookie = $integratedLeague->commish_cookie;

        //Convert franchiseID to correct format 000#
        $franchiseID = SlackClass::formatFranchiseID($franchiseID);

        //Retreive all franchises that belong to this team
        $franchiseName = Mfl_franchise_map::findOrFail("{$leagueID}_{$franchiseID}")
                                                ->franchise_name;
        
        ////!!!!!Add error log/slack to msg if team not found

        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('assets', $leagueID);
        $mflDataObj = SlackClass::getMflData($mflDataUrl, $commishCookie);

        $franchises = $mflDataObj->assets->franchise;

        foreach($franchises as $franchise){
            if($franchise->id === $franchiseID){
                //do work
                $draftPickObjs = $franchise->futureYearDraftPicks->draftPick;
                $draftPickIDs = array_map(function($o){ return $o->pick; }, $draftPickObjs);
                $prettyDraftPicks = SlackClass::getPrettyDraftPicks($draftPickIDs, $leagueID);

                $slackMessage = ">*{$franchiseName}* draft picks:\n>";
                $slackMessage .= implode("\n>", $prettyDraftPicks);              
            }
            
        }
        return $slackMessage;
    }

    /**
    * Register some data and send a temporary link to user 
    *
    * @param Request $request 
    * @return String $slackMessage
    */
    public function sendRegisterLink(Request $request){

        //Generate a random string to be used as the temp token
        $token = str_random(12);
        $url = url("register?token=$token");
        var_dump($url);
        
        Mfl_temporary_url::updateOrCreate(
            [ "url" => url("register") ,
             "param" => $token ,
             "slack_team" => $request->input('team_id') ]
        );
    }

    public function getIntegratedLeagueFromRequest($request){
        
        //Check the request 
        if ( ! $slackTeamID = $request->input('team_id') ){
            return "Slack Team ID missing.";
        }

        //Get the team_id from the request
        return Mfl_slack_integration::findOrFail($slackTeamID);

    }
}
