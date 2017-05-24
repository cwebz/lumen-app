<?php

namespace App\Services;

use App\Models\Mfl_players_table;
use App\Models\Mfl_slack_integration;
use App\Models\Mfl_checktrade_timestamps;
use App\Classes\SlackClass;

class CheckTradeService
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
            $slackChannel = $integration->checktrade_channel;

		    //Build URL and retrieve the data
		    $mflDataUrl = SlackClass::getMflLeagueDataUrl('transactions', $leagueID, '', '&TRANS_TYPE=trade&COUNT=5');
		    $mflDataObj = SlackClass::getMflData($mflDataUrl);

            //Make sure we always put this in an array for simplicity
            if(!is_array($mflDataObj->transactions->transaction)){
                $transactions[0] = $mflDataObj->transactions->transaction;
            }else{
                $transactions = $mflDataObj->transactions->transaction;
            }

            //Get the most trade timestamp for the league
            $mostRecentTrade = Mfl_checktrade_timestamps::find($leagueID)
                                ->lasttrade_timestamp;

            foreach($transactions as $transaction){
                
                //These will need to be time comparisons
                if( (int)$transaction->timestamp > (int)$mostRecentTrade 
                    || !$mostRecentTrade){


                    $franchiseOne = SlackClass::getFranchiseName("{$leagueID}_{$transaction->franchise}");
                    $franchiseTwo = SlackClass::getFranchiseName("{$leagueID}_{$transaction->franchise2}");
                    $franchiseOneGave = SlackClass::separatePlayersPicks($transaction->franchise1_gave_up);
                    $franchiseTwoGave = SlackClass::separatePlayersPicks($transaction->franchise2_gave_up);

        
                    $franchiseTwoMsg = "*{$franchiseTwo}* Received:\n";

                    if($franchiseOneGave->players){
                        $franchiseOneGave->prettyPlayers = SlackClass::getPrettyPlayers($franchiseOneGave->players);
                        $franchiseTwoMsg .= implode("\n", $franchiseOneGave->prettyPlayers);
                    }
                    //Pretty formating for slack
                    $franchiseTwoMsg .= "\n";

                    if($franchiseOneGave->draftPicks){
                        $franchiseOneGave->prettyPicks = SlackClass::getPrettyDraftPicks(
                            $franchiseOneGave->draftPicks, $leagueID);
                        $franchiseTwoMsg .= implode("\n", $franchiseOneGave->prettyPicks);
                    }

                    $franchiseOneMsg = "*{$franchiseOne}* Received:\n";

                    if($franchiseTwoGave->players){
                        $franchiseTwoGave->prettyPlayers = SlackClass::getPrettyPlayers($franchiseTwoGave->players);
                        $franchiseOneMsg .= implode("\n", $franchiseTwoGave->prettyPlayers);
                    }
                    //Pretty formatting for slack
                    $franchiseOneMsg .= "\n";

                    if($franchiseTwoGave->draftPicks){
                        $franchiseTwoGave->prettyPicks = SlackClass::getPrettyDraftPicks(
                            $franchiseTwoGave->draftPicks, $leagueID);
                        $franchiseOneMsg .= implode("\n", $franchiseTwoGave->prettyPicks);
                    }         
            
                    $fullSlackMsg = "Trade completed between *{$franchiseOne}* and *{$franchiseTwo}*\n";
                    $fullSlackMsg .= "{$franchiseOneMsg}\n{$franchiseTwoMsg}";

                    SlackClass::sendSlackMsg($fullSlackMsg, $slackChannel);

                    Mfl_checktrade_timestamps::updateOrCreate(
                        ['mfl_league_id' => $leagueID],
                        ['lasttrade_timestamp' => $transaction->timestamp]);

                }
		    }
        }
    }
}
?>