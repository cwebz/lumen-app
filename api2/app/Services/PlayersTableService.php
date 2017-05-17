<?php

namespace App\Services;

use App\Models\Mfl_players_table;
use App\Classes\SlackClass;

class PlayersTableService
{
	
	/**
	* Gets the JSON of the the players in mfl and imports them
	*/
	public static function update(){

		$mflDataUrl = SlackClass::getMflLeagueDataUrl('players');
		$mflDataObj = SlackClass::getMflData($mflDataUrl);

		//Get array of franchises
		$franchises = $mflDataObj->league->franchises->franchise;

		Mfl_franchise_map::updateOrCreate(
			["league_franchise" => "{$leagueID}_{$franchise->id}"],
			["franchise_name" => $franchise->name]
		);
	}
}

?>