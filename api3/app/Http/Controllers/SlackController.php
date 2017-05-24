<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SlackClass;

use App\Models\Mfl_slack_integration;
use App\Models\Mfl_franchise_map;
use App\Models\Mfl_players_table;

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
        
        //Get the team_id from the request
        $leagueID = Mfl_slack_integration::find($request->input('team_id'))
            ->mfl_league_id;
        
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

        //Get the team_id from the request
        $leagueID = Mfl_slack_integration::find($request->input('team_id'))
                                                ->mfl_league_id;

        //Convert franchiseID to correct format 000#
        $franchiseID = SlackClass::formatFranchiseID($franchiseID);

        //Retreive all franchises that belong to this team
        $franchiseName = Mfl_franchise_map::find("{$leagueID}_{$franchiseID}")
                                                ->franchise_name;
        
        ////!!!!!Add error log/slack to msg if team not found

        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('assets', $leagueID);
        $mflDataObj = SlackClass::getMflData($mflDataUrl);

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
    * @return JSON
    */
    public function getFranchisePicks($request, $franchiseID){

        //Get the team_id from the request
        $leagueID = Mfl_slack_integration::find($request->input('team_id'))
                                                ->mfl_league_id;

        //Convert franchiseID to correct format 000#
        $franchiseID = SlackClass::formatFranchiseID($franchiseID);

        //Retreive all franchises that belong to this team
        $franchiseName = Mfl_franchise_map::find("{$leagueID}_{$franchiseID}")
                                                ->franchise_name;
        
        ////!!!!!Add error log/slack to msg if team not found

        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('assets', $leagueID);
        $mflDataObj = SlackClass::getMflData($mflDataUrl);

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

}
