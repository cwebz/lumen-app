<?php

namespace App\Services;

use App\Models\Mfl_players_table;
use App\Models\Mfl_franchise_map;
use App\Models\Mfl_slack_integration;
use App\Models\Mfl_tradebait_timestamps;
use App\Classes\SlackClass;

class TradeBaitService
{
	
	/**
	* Gets the JSON of the the players in mfl and imports them
	*/
	public static function update(){

		//Let's get all integrations we have
		$slacksIntegrated = Mfl_slack_integration::all();

		//Need to loop through each site we have integrated
		foreach($slacksIntegrated as $integration){
		    $leagueID = $integration->mfl_league_id;

		    //Build URL and retrieve the data
		    $mflDataUrl = SlackClass::getMflLeagueDataUrl('tradeBait', $leagueID, '', '&INCLUDE_DRAFT_PICKS=1');
		    $mflDataObj = SlackClass::getMflData($mflDataUrl);

		    $tradeBaits = $mflDataObj->tradeBaits->tradeBait;

            foreach($tradebaits as $tradebait){

                //Get the franchise ID of the teams tradebait
                $franchiseId = $tradeBait->franchise_id;
                $timestamp = $tradeBait->timestamp;
                
                //See if we have a match and whether or not to proceed
                $dbTeam = MFl_tradebait_timestamps::find("{$leagueID}_{$franchiseId}");

                //If they exist and timestamp isn't different break out
                if($dbTeam && $dbTeam->tradebait_timestamp === $timestamp){
                    break;
                }

                $offering = SlackClass::separatePlayersPicks($tradeBait->willGiveUp);
                $wanting = $tradeBait->inExchangeFor;
                $franchiseName = SlackClass::getFranchiseName($franchiseId);

///////////////////////////

                //Get player names, team, pos and 
                $playersSlackMsg = '';
                if($offering->players){
                    $prettyPlayers = SlackClass::getPrettyPlayers($offering->players);
                    $playersSlackMsg = "Players On Trading Block:\n";
                    $playersSlackMsg .= implode("\n", $prettyPlayers);
                }
                
                //Get draft picks in human readable format
                $draftPicksSlackMsg = '';
                if($offering->draftPicks){
                    $prettyDraftPicks = slackClass::getPrettyDraftPicks($offering->draftPicks);
                    $draftPicksSlackMsg = "Draft Picks On Trading Block:\n";
                    $draftPicksSlackMsg .= implode("\n", $prettyDraftPicks);
                }

                $fullSlackMsg = "*{$franchiseName}* has updated their trading block...";
                $fullSlackMsg .= "\n{$playersSlackMsg}\n{$draftPicksSlackMsg}";
            
                ////Add wanting message with html normailized
                $prettyText = html_entity_decode(strip_tags($wanting), ENT_QUOTES, 'UTF-8');
                $fullSlackMsg .= "\nNotes:";
                $fullSlackMsg .= "\n    {$prettyText}";
                
                SlackClass::sendSlackMsg($fullSlackMsg, SLACK_TRADE_CHANNEL);



            }


		}

// 		foreach( $players as $player){
// 			Mfl_players_table::updateOrCreate(
// 				["id" => $player->id],
// 				["name" => $player->name,
// 				'position' => $player->position,
// 				'team' => $player->team
// 				]
// 			);
// 		}
// 	}
// }

//  Get the franchise map file
//     global $franchiseMap;
//     $mapFile = @file_get_contents("files/franchise-map.json");
//     $franchiseMap = json_decode($mapFile);

//     $mflDataUrl = getMflLeagueDataUrl(TRADE_BAIT, LEAGUE_ID, WEEK, '&INCLUDE_DRAFT_PICKS=1');
//     $mflDataObj = getMflData($mflDataUrl);

//     $tradeBaits = $mflDataObj->tradeBaits->tradeBait;

//     //Pull in trade bait timestamps
//     $tradeBaitTimestamps = json_decode(@file_get_contents("files/trade-bait-timestamp.json"));
//     $tradeBaitTimestampsArr = [];

//     //Flag for whether or not to update the file
//     $updateTimestampFile = false;
        
//     foreach($tradeBaits as $tradeBait){

//         $timestamp = $tradeBait->timestamp;
//         $franchiseId = $tradeBait->franchise_id;
//         $franchiseName = getFranchiseName($franchiseId);
//         $offering = $tradeBait->willGiveUp;
//         $wanting = $tradeBait->inExchangeFor;

//         //Add to our timestamp array to update
//         $tradeBaitTimestampsArr[$franchiseId] = $timestamp;
        
//         //Check whether to proccess the current trade bait
//         if($tradeBaitTimestamps && isset($tradeBaitTimestamps->$franchiseId)){
//             $proccessTradeBait = ($timestamp !== $tradeBaitTimestamps->$franchiseId ? true : false);
//         }else{
//             $proccessTradeBait = true;
//         }

//         if($proccessTradeBait){
            
//             //Flag that we need up update the file 
//             $updateTimestampFile = true;

//             $playerIds = [];
//             $draftPickIds = [];

//             //Now Offering could have palyers and picks so we will need to clean that up
//             //Get an array from the string 
//             $offeringArray = explode(",", $offering);
//             //Loop through and assign to proper array
//             foreach($offeringArray as $key => $id){
                
//                 if(strpos($id, 'DP_') === 0 || strpos($id, 'FP_') === 0){
//                     array_push($draftPickIds, $id);
//                 }else{
//                     array_push($playerIds, $id);
//                 }
//             }

//             //Get player names, team, pos and 
//             $playersSlackMsg = '';
//             if($playerIds){
//                 $players = getPrettyPlayers($playerIds);
//                 $playersSlackMsg = "Players On Trading Block:\n";
//                 $playersSlackMsg .= implode("\n", $players);
//             }
            
//             //Get draft picks in human readable format
//             $draftPicksSlackMsg = '';
//             if($draftPickIds){
//                 $draftPicks = getPrettyDraftPicks($draftPickIds);
//                 $draftPicksSlackMsg = "Draft Picks On Trading Block:\n";
//                 $draftPicksSlackMsg .= implode("\n", $draftPicks);
//             }

//             $fullSlackMsg = "*{$franchiseName}* has updated their trading block...";
//             $fullSlackMsg .= "\n{$playersSlackMsg}\n{$draftPicksSlackMsg}";
           
//             ////Add wanting message with html normailized
//             $prettyText = html_entity_decode(strip_tags($wanting), ENT_QUOTES, 'UTF-8');
//             $fullSlackMsg .= "\nNotes:";
//             $fullSlackMsg .= "\n    {$prettyText}";
            
//             slack($fullSlackMsg, SLACK_TRADE_CHANNEL);
//         }
//     }

//     //Write timestamp array to file
//     if($updateTimestampFile === true){
//         $tradeBaitFile = fopen("files/trade-bait-timestamp.json", "w") or die("Unable to open file!");
//         fwrite($tradeBaitFile, json_encode($tradeBaitTimestampsArr));
//         fclose($tradeBaitFile);
   
    }

}
?>