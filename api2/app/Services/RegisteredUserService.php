<?php

namespace App\Services;

use App\Models\Mfl_registered_users;
use App\Models\Mfl_registered_user_meta;
use App\Classes\SlackClass;

class RegisteredUserService
{
	
	/**
	* Gets the JSON of the the players in mfl and imports them
	*/
	public static function update(){

		//Let's get all users that want updates
		$registeredUsers = Mfl_registered_users::all();

		//Need to loop through each user registered
		foreach($registeredUsers as $registeredUser){
            
            //Get the MFL League ID for retreiving data
		    $userID = $registeredUser->id;
			$mflUserCookie = $registeredUser->mfl_cookie;

			$userLeagues = Mfl_registered_user_meta::where(
								'user_id', $userID)
								->get();
			
			foreach($userLeagues as $league){

				$metaID = $league->meta_id;
				$leagueID = $league->league_id;
				$slackChannel = $league->webhook;
            	$mostRecentProposal = $league->last_proposal;

		    	$mflDataUrl = SlackClass::getMflLeagueDataUrl(
								'transactions', 
								$leagueID,
								'',
								'&TRANS_TYPE=trade_proposal&COUNT=10');

				$mflDataObj = SlackClass::getMflData($mflDataUrl, $mflUserCookie);

				//Make sure we always put this in an array for simplicity
				if(!is_array($mflDataObj->transactions->transaction)){
					$transactions[0] = $mflDataObj->transactions->transaction;
				}else{
					$transactions = $mflDataObj->transactions->transaction;
				}

				foreach($transactions as $transaction){
                
					//These will need to be time comparisons
					if( (int)$transaction->timestamp > (int)$mostRecentProposal 
						|| !$mostRecentProposal){
						

						$franchiseOne = SlackClass::getFranchiseName("{$leagueID}_{$transaction->franchise}");
						$franchiseTwo = SlackClass::getFranchiseName("{$leagueID}_{$transaction->franchise2}");
						$franchiseOneGave = SlackClass::separatePlayersPicks($transaction->franchise1_gave_up);
						$franchiseTwoGave = SlackClass::separatePlayersPicks($transaction->franchise2_gave_up);

			
						$franchiseTwoMsg = "*{$franchiseTwo}* Receives:\n";

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

						$franchiseOneMsg = "*{$franchiseOne}* Receives:\n";

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
				
						$fullSlackMsg = "Trade proposed:\n";
						$fullSlackMsg .= "{$franchiseOneMsg}\n{$franchiseTwoMsg}";

						//Send slack message and update
						SlackClass::sendSlackMsg($fullSlackMsg, $slackChannel);

						Mfl_registered_user_meta::updateOrCreate(
							['meta_id' => $metaID],
						 	['last_proposal' => $transaction->timestamp]);
					}
                }
		    }
        }
	}
}

?>