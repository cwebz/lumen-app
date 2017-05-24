<?php

namespace App\Services;

use App\Models\Mfl_players_table;
use App\Models\Mfl_slack_integration;
use App\Models\Mfl_tradebait_timestamps;
use App\Classes\SlackClass;

class TradeBaitService
{
	//public static $slackChannel = 'https://hooks.slack.com/services/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';

	/**
	* Gets the JSON of the the players in mfl and imports them
	*/
	public static function update(){

		//Let's get all integrations we have
		$slacksIntegrated = Mfl_slack_integration::all();

		//Need to loop through each site we have integrated
		foreach($slacksIntegrated as $integration){
            
            //Get the MFL League ID for retreiving data
		    $leagueID = $integration->mfl_league_id;

            //Get the slack channel webhook that we are talking to
            $slackChannel = $integration->tradebait_channel;

		    //Build URL and retrieve the data
		    $mflDataUrl = SlackClass::getMflLeagueDataUrl('tradeBait', $leagueID, '', '&INCLUDE_DRAFT_PICKS=1');
		    $mflDataObj = SlackClass::getMflData($mflDataUrl);

            //Make sure we always put this in an array for simplicity
            if(!is_array($mflDataObj->tradeBaits->tradeBait)){
                $tradeBaits[0] = $mflDataObj->tradeBaits->tradeBait;
            }else{
                $tradeBaits = $mflDataObj->tradeBaits->tradeBait;
            }

            foreach($tradeBaits as $tradeBait){

                //Get the franchise ID of the teams tradebait
                $franchiseID = $tradeBait->franchise_id;
                $timestamp = $tradeBait->timestamp;
                
                //See if we have a match and whether or not to proceed
                $dbTimestamp = MFl_tradebait_timestamps::find("{$leagueID}_{$franchiseID}")
                            ->tradebait_timestamp;

                //If they exist and timestamp isn't different break out
                if($dbTimestamp === $timestamp){
                    break;
                }

                $offering = SlackClass::separatePlayersPicks($tradeBait->willGiveUp);

                $wanting = $tradeBait->inExchangeFor;
                $franchiseName = SlackClass::getFranchiseName("{$leagueID}_{$franchiseID}");
                
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
                    $prettyDraftPicks = SlackClass::getPrettyDraftPicks($offering->draftPicks, $leagueID);
                    $draftPicksSlackMsg = "Draft Picks On Trading Block:\n";
                    $draftPicksSlackMsg .= implode("\n", $prettyDraftPicks);
                }

                $fullSlackMsg = "*{$franchiseName}* has updated their trading block...";
                $fullSlackMsg .= "\n{$playersSlackMsg}\n{$draftPicksSlackMsg}";
            
                ////Add wanting message with html normailized
                $prettyText = html_entity_decode(strip_tags($wanting), ENT_QUOTES, 'UTF-8');
                $fullSlackMsg .= "\nNotes:";
                $fullSlackMsg .= "\n    {$prettyText}";
                
                SlackClass::sendSlackMsg($fullSlackMsg, $slackChannel);

                Mfl_tradebait_timestamps::updateOrCreate(
                	['league_franchise' => "{$leagueID}_{$franchiseID}"],
                	['tradebait_timestamp' => $timestamp]);
            }
		}
    }
}
?>