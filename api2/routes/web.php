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

$app->get('trade', function() use ($app) {
    
    Artisan::call('checktrade:update');

    return "TEsting";
});
$app->get('bait', function() use ($app) {
    
    Artisan::call('checktrade:update');

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
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('rosters', $leagueID, '', "&FRANCHISE={$franchiseID}");
        $mflDataObj = SlackClass::getMflData($mflDataUrl);
        
        $playerObjs = $mflDataObj->rosters->franchise->player;
        $playerIDs = array_map(function($o){ return $o->id; }, $playerObjs);
        //$playerIDs = implode(',', $playersArr);

        $prettyPlayers = SlackClass::getPrettyRoster($playerIDs);
        $slackMessage = ">*{$franchiseName}* roster:\n";
        $slackMessage .= implode("\n>", $prettyPlayers);
        return $slackMessage;
});