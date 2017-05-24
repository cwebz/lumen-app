<?php

namespace App\Services;

use App\Models\Mfl_franchise_map;
use App\Models\Mfl_slack_integration;
use App\Classes\SlackClass;

class FranchiseMapService
{

	/**
	* Gets the JSON of the league and imports ID=>Franchise name 
	*/
	public static function update(){
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
	}
}

?>