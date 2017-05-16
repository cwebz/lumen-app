<?php

use App\Http\Controllers\SlackController;
use Illuminate\Http\Request;
use App\Models\Mfl_franchise_map;
use App\Models\Mfl_slack_integration;
use App\Classes\SlackClass;

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

$app->get('test', function() use ($app) {
    
    // $count = Mfl_franchise_map::query()->get()->count();
    // var_dump($count);
    Mfl_franchise_map::updateOrCreate(
        ['league_franchise' => '12958_0001'],
        ['franchise_name' => 'Commish']
    );
    return "working";
});

$app->get('cron', function() use ($app) {
    
    //Let's get all integrations we have
    $slacksIntegrated = Mfl_slack_integration::all();

    //Loop through each integration and 
    foreach($slacksIntegrated as $integration){
        $leagueID = $integration->mfl_league_id;
        $mflDataUrl = SlackClass::getMflLeagueDataUrl('league', $leagueID);
        $mflDataObj = SlackClass::getMflData($mflDataUrl);

        //Get array of franchises
        $franchises = $mflDataObj->league->franchises->franchise;

        //Loop through and build the maps
        foreach($franchises as $franchise){
            Mfl_franchise_map::updateOrCreate(
                ["league_franchise" => "{$leagueID}_{$franchise->id}"],
                ["franchise_name" => $franchise->name]
            );
        }
    }

    return "working";
});