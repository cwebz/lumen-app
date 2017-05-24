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
		$players = $mflDataObj->players->player;

		foreach( $players as $player){
			Mfl_players_table::updateOrCreate(
				["id" => $player->id],
				["name" => $player->name,
				'position' => $player->position,
				'team' => $player->team
				]
			);
		}
	}
}

?>