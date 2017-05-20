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

$app->get('cron', function() use ($app) {
    
    TradeBaitService::update();
    return "working";
});