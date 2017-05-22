<?php

use App\Http\Controllers\SlackController;
use Illuminate\Http\Request;
use App\Models\Mfl_franchise_map;
use App\Models\Mfl_slack_integration;
use App\Models\Mfl_tradebait_timestamps;
use App\Classes\SlackClass;

use App\Services\TradeBaitService;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

//This is the only route for the slack integration
$app->post('slack', 'SlackController@handleRequest');

$app->get('push', function() use ($app) {

    $mflUrl = 'https://www74.myfantasyleague.com/2017/export?TYPE=transactions&L=73514&W=&TRANS_TYPE=trade_proposal&COUNT=10&JSON=1';

    $opts = [
        "http" => [
            "header" => "Cookie: MFL_USER_ID=aRxs2sGavrXmhFT4fBCVPnc="
        ]
    ];

    $context = stream_context_create($opts);
    $file = file_get_contents($mflUrl, false, $context);
    $decoded = json_decode($file);
    return var_dump($decoded);
    // $request = 'https://hooks.slack.com/commands/T5752MTB7/186148094884/ghcSYr8cwZoWPb6PlFqd6IDp';
    // SlackClass::sendSlackMsg('Forcing this', $request);
    });
$app->get('bait', function() use ($app) {
    
    Artisan::call('tradebait:update');

    return "TEsting";
});
$app->get('map', 'SlackController@getFranchiseMap');

$app->get('test', function() use ($app) {

        $franchiseID = '1';
        //Get the team_id from the request
        $leagueID = '73514';

        //Convert franchiseID to correct format 000#
        switch (strlen($franchiseID)) {
            case 1:
                $franchiseID = "000{$franchiseID}";
                break;
            case 2:
                $franchiseID = "00{$franchiseID}";
                break;
        }

        //Retreive all franchises that belong to this team
        $franchiseName = Mfl_franchise_map::find("{$leagueID}_{$franchiseID}")
                                                ->franchise_name;
        
        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('assets', $leagueID);
        $mflDataObj = SlackClass::getMflData($mflDataUrl);
        
        $franchises = $mflDataObj->assets->franchise;

        foreach($franchises as $franchise){
            if($franchise->id === $franchiseID){
                //do work
                $playerObjs = $franchise->players->player;
                $playerIDs = array_map(function($o){ return $o->id; }, $playerObjs);

                $prettyPlayers = SlackClass::getPrettyRoster($playerIDs);
                $slackMessage = ">*{$franchiseName}* roster:\n>";
                $slackMessage .= implode("\n>", $prettyPlayers);

                return $slackMessage;               
            }
            
        }
});

$app->get('proposal', function() use ($app) {

        $franchiseID = '1';
        //Get the team_id from the request
        $leagueID = '73514';

        //Convert franchiseID to correct format 000#
        switch (strlen($franchiseID)) {
            case 1:
                $franchiseID = "000{$franchiseID}";
                break;
            case 2:
                $franchiseID = "00{$franchiseID}";
                break;
        }


        //Build URL and retrieve the data
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('transactions', 
                                    $leagueID, 
                                    '&TRANS_TYPE=trade_proposal&COUNT=10');

        $mflDataObj = SlackClass::getMflData($mflDataUrl, 'aRxs2sGavrXmhFT4fBCVPnc=');
        exit(var_dump($mflDataObj));
        $franchises = $mflDataObj->assets->franchise;

        foreach($franchises as $franchise){
            if($franchise->id === $franchiseID){
                //do work
                $playerObjs = $franchise->players->player;
                $playerIDs = array_map(function($o){ return $o->id; }, $playerObjs);

                $prettyPlayers = SlackClass::getPrettyRoster($playerIDs);
                $slackMessage = ">*{$franchiseName}* roster:\n>";
                $slackMessage .= implode("\n>", $prettyPlayers);

                return $slackMessage;               
            }
            
        }
});