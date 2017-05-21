<?php
//Stuff to add 
 //Decode JSon file
 //wanting message
 //Turn this into a class, more legit looking
define('SLACK_WEBHOOK', 'https://hooks.slack.com/services/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');
define('SLACK_TRADE_CHANNEL', 'https://hooks.slack.com/services/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');
define('MFL_BASE_URL', 'https://www74.myfantasyleague.com/2017/export?');
define('TRADE_BAIT', 'tradeBait');
define('TRANSACTIONS', 'transactions');
define('LEAGUE', 'league');
define('LEAGUE_ID', '73514');
define('WEEK', '');
$franchiseMap;

//checkTradeBait();
checkRecentTrades();
function checkRecentTrades(){

    //Get the URL and Data
    $mflDataUrl = getMflLeagueDataUrl('rosters', LEAGUE_ID, WEEK, '&FRANCHISE=0001');
    $mflDataObj = getMflData($mflDataUrl);

$players = $mflDataObj->rosters->franchise->player;

$test = array_map(function($o){ return $o->id; }, $players);
$aaa = implode(',', $test);
    //Make sure we always put this in an array for simplicity
    if(!is_array($mflDataObj->transactions->transaction)){
        $transactions[0] = $mflDataObj->transactions->transaction;
    }else{
        $transactions = $mflDataObj->transactions->transaction;
    }

    //Get the most recent trade timestamp on file
    $mapFile = @file_get_contents("files/most-recent-trade.txt");
    $mostRecentTrade = $mapFile;

    $updateTradeFile = false;

    foreach($transactions as $transaction){
        //These will need to be time comparisons
        if( (int)$transaction->timestamp > (int)$mostRecentTrade 
            || !$mostRecentTrade){
            
            $updateTradeFile = true;

            $franchiseOne = getFranchiseName($transaction->franchise);
            $franchiseTwo = getFranchiseName($transaction->franchise2);
            $franchiseOneGave = separatePlayersPicks($transaction->franchise1_gave_up);
            $franchiseTwoGave = separatePlayersPicks($transaction->franchise2_gave_up);

            
            $franchiseTwoMsg = "*{$franchiseTwo}* Received:\n";

            if($franchiseOneGave->players){
                $franchiseOneGave->prettyPlayers = getPrettyPlayers($franchiseOneGave->players);
                $franchiseTwoMsg .= implode("\n", $franchiseOneGave->prettyPlayers);
            }
            //Pretty formating for slack
            $franchiseTwoMsg .= "\n";

            if($franchiseOneGave->draftpicks){
                $franchiseOneGave->prettyPicks = getPrettyDraftPicks($franchiseOneGave->draftpicks);
                $franchiseTwoMsg .= implode("\n", $franchiseOneGave->prettyPicks);
            }

            $franchiseOneMsg = "*{$franchiseOne}* Received:\n";

            if($franchiseTwoGave->players){
                $franchiseTwoGave->prettyPlayers = getPrettyPlayers($franchiseTwoGave->players);
                $franchiseOneMsg .= implode("\n", $franchiseTwoGave->prettyPlayers);
            }
            //Pretty formatting for slack
            $franchiseOneMsg .= "\n";

            if($franchiseTwoGave->draftpicks){
                $franchiseTwoGave->prettyPicks = getPrettyDraftPicks($franchiseTwoGave->draftpicks);
                $franchiseOneMsg .= implode("\n", $franchiseTwoGave->prettyPicks);
            }         
                  
            $fullSlackMsg = "Trade completed between *{$franchiseOne}* and *{$franchiseTwo}*\n";
            $fullSlackMsg .= "{$franchiseOneMsg}\n{$franchiseTwoMsg}";

            slack($fullSlackMsg, SLACK_TRADE_CHANNEL);

            //Write timestamp to file
            if($updateTradeFile === true){
                $tradeFile = fopen("files/most-recent-trade.txt", "w") or die("Unable to open file!");
                fwrite($tradeFile, $transaction->timestamp);
                fclose($tradeFile);
            }

        }

    }

}

/*
* Separate out the picks in the string and return*/
function separatePlayersPicks($combinedString){
    $splitArray = new stdClass();
    $playerIds = [];
    $draftPickIds = [];

    $playersPicksArray = explode(",", $combinedString);
    //Loop through and assign to proper array
    foreach($playersPicksArray as $key => $id){
        
        if(strpos($id, 'DP_') === 0 || strpos($id, 'FP_') === 0){
            array_push($draftPickIds, $id);
        }elseif($id !== ""){
            array_push($playerIds, $id);
        }
    }

    $splitArray->draftpicks = $draftPickIds;
    $splitArray->players = $playerIds;
    return $splitArray;
}

function checkTradeBait(){
    
    //Get the franchise map file
    global $franchiseMap;
    $mapFile = @file_get_contents("files/franchise-map.json");
    $franchiseMap = json_decode($mapFile);

    $mflDataUrl = getMflLeagueDataUrl(TRADE_BAIT, LEAGUE_ID, WEEK, '&INCLUDE_DRAFT_PICKS=1');
    $mflDataObj = getMflData($mflDataUrl);

    $tradeBaits = $mflDataObj->tradeBaits->tradeBait;

    //Pull in trade bait timestamps
    $tradeBaitTimestamps = json_decode(@file_get_contents("files/trade-bait-timestamp.json"));
    $tradeBaitTimestampsArr = [];

    //Flag for whether or not to update the file
    $updateTimestampFile = false;
        
    foreach($tradeBaits as $tradeBait){

        $timestamp = $tradeBait->timestamp;
        $franchiseId = $tradeBait->franchise_id;
        $franchiseName = getFranchiseName($franchiseId);
        $offering = $tradeBait->willGiveUp;
        $wanting = $tradeBait->inExchangeFor;

        //Add to our timestamp array to update
        $tradeBaitTimestampsArr[$franchiseId] = $timestamp;
        
        //Check whether to proccess the current trade bait
        if($tradeBaitTimestamps && isset($tradeBaitTimestamps->$franchiseId)){
            $proccessTradeBait = ($timestamp !== $tradeBaitTimestamps->$franchiseId ? true : false);
        }else{
            $proccessTradeBait = true;
        }

        if($proccessTradeBait){
            
            //Flag that we need up update the file 
            $updateTimestampFile = true;

            $playerIds = [];
            $draftPickIds = [];

            //Now Offering could have palyers and picks so we will need to clean that up
            //Get an array from the string 
            $offeringArray = explode(",", $offering);
            //Loop through and assign to proper array
            foreach($offeringArray as $key => $id){
                
                if(strpos($id, 'DP_') === 0 || strpos($id, 'FP_') === 0){
                    array_push($draftPickIds, $id);
                }else{
                    array_push($playerIds, $id);
                }
            }

            //Get player names, team, pos and 
            $playersSlackMsg = '';
            if($playerIds){
                $players = getPrettyPlayers($playerIds);
                $playersSlackMsg = "Players On Trading Block:\n";
                $playersSlackMsg .= implode("\n", $players);
            }
            
            //Get draft picks in human readable format
            $draftPicksSlackMsg = '';
            if($draftPickIds){
                $draftPicks = getPrettyDraftPicks($draftPickIds);
                $draftPicksSlackMsg = "Draft Picks On Trading Block:\n";
                $draftPicksSlackMsg .= implode("\n", $draftPicks);
            }

            $fullSlackMsg = "*{$franchiseName}* has updated their trading block...";
            $fullSlackMsg .= "\n{$playersSlackMsg}\n{$draftPicksSlackMsg}";
           
            ////Add wanting message with html normailized
            $prettyText = html_entity_decode(strip_tags($wanting), ENT_QUOTES, 'UTF-8');
            $fullSlackMsg .= "\nNotes:";
            $fullSlackMsg .= "\n    {$prettyText}";
            
            slack($fullSlackMsg, SLACK_TRADE_CHANNEL);
        }
    }

    //Write timestamp array to file
    if($updateTimestampFile === true){
        $tradeBaitFile = fopen("files/trade-bait-timestamp.json", "w") or die("Unable to open file!");
        fwrite($tradeBaitFile, json_encode($tradeBaitTimestampsArr));
        fclose($tradeBaitFile);
    }

}

//maybe we want to pull players nightly.... maybe not though
//anyway...



/*
* Gets player ID's from offering and get player objects from MFL
*/
function getPrettyPlayers($playerIds){
    //String for IDs for MFL url params
    $playersString = implode(",", $playerIds);
    
    //Generate the URL and get the data objects
    $playersDataUrl = getMflPlayerDataUrl($playersString);
    $playerDataObj = getMflData($playersDataUrl);

    //Array for adding players
    $playersArr = [];

    $players = $playerDataObj->players->player;

    if(is_array($players)){
        foreach($players as $player){
            //Put this in a string format to display in slack
            $playerInfoString = "    *{$player->name}*"
                                . "    _{$player->team}_  "
                                . "    _{$player->position}_";
            array_push($playersArr, $playerInfoString);
        }
    }else{
        //There was only one player
        $player = $players;
        //Put this in a string format to display in slack
        $playerInfoString = "    *{$player->name}*"
                            . "    _{$player->team}_"
                            . "    _{$player->position}_";
        array_push($playersArr, $playerInfoString);
    }

    return $playersArr;
}

/*
* Gets trade pick ID's from offering and convert them to human readable
*/
function getPrettyDraftPicks($draftPickIds){
    //Array for adding players
    $draftPicksArr = [];

    foreach($draftPickIds as $draftPick){
        //Put this in a string format to display in slack
        $draftPickString = decodeDraftPick($draftPick);
        array_push($draftPicksArr, $draftPickString);
    }

    return $draftPicksArr;
}

/*
* Decode the draft pick
*/
function decodeDraftPick($draftPick){
    //Get the parts from the draft pick
    $draftPickParts = explode("_", $draftPick);

    //This is a pick in the current year
    if($draftPickParts[0] === "DP"){
        //Add 1 to get the round and pick num
        $round = (int)$draftPickParts[1] + 1;
        $pickNum = (int)$draftPickParts[2] + 1;
        $pickNum = ($pickNum < 10? "0{$pickNum}" : $pickNum);

        return "    {$round}.$pickNum"; //Spacing for formatting
    }else{
        $team = getFranchiseName($draftPickParts[1]);
        $year = $draftPickParts[2];
        $round = $draftPickParts[3];

        switch($round % 10){
            case 1: $round .= 'st'; break;
            case 2: $round .= 'nd'; break;
            case 3: $round .= 'rd'; break;
            case 4: $round .= 'th'; break;
        }

        return "    {$year} {$round} {$team}"; //Spacing for formatting
    }
}

/*
* Get league franchise names
*/
function getFranchiseName($teamId){
    global $franchiseMap;
    return $franchiseMap->$teamId;
}


/*
* Create Franchise ID -> Name map
* This should run nightly to stay updated on team names
*/
function createFranchiseMap(){
    
    $franchiseMap = [];
    $mflDataUrl = getMflLeagueDataUrl(LEAGUE, LEAGUE_ID, WEEK);
    $mflDataObj = getMflData($mflDataUrl);
    //Get array of franchises
    $franchises = $mflDataObj->league->franchises->franchise;

    //Loop through and build the maps
    foreach($franchises as $franchise){
        $franchiseMap[$franchise->id] = $franchise->name;
    }

    $mapFile = fopen("files/franchise-map.json", "w") or die("Unable to open file!");
    fwrite($mapFile, json_encode($franchiseMap));
    fclose($mapFile);
}

/*
* Generate the URL for getting data related to the league
*/
function getMflLeagueDataUrl($dataType, $leagueId, $week, $additionalArgs = ''){
    //Type of data to export
    $typeUrlArg = "TYPE={$dataType}";
    $leagueUrlArg = "&L={$leagueId}";
    $weekUrlArg = "&W={$week}";
    $jsonUrlArg = "&JSON=1";

    //Build the full URL
    return MFL_BASE_URL . $typeUrlArg . $leagueUrlArg . $weekUrlArg . $additionalArgs . $jsonUrlArg;

}

/*
* Generate the URL for getting information on players
*/
function getMflPlayerDataUrl($players){
    //Type of data to export
    $typeUrlArg = "TYPE=players";
    $playersUrlArg = "&PLAYERS={$players}";
    $jsonUrlArg = "&JSON=1";

    return MFL_BASE_URL . $typeUrlArg . $playersUrlArg . $jsonUrlArg;
}

/*
* Function to return the JSON object of the data requested
*/
function getMflData($dataUrl){
    //Get the contents of the url
    $mflData = @file_get_contents($dataUrl);
    
    //Check to make sure there is data
    if(!$mflData){
        exit("Failed to retrieve data from MFL");
    }
    
    //Make sure we can decode the json
    $mflDataObj = json_decode($mflData);
    if(!$mflData){
        exit("Failed to decode the data from MFL");
    }

    return $mflDataObj;
}


/**
* Function to post a message to slack
*/
function slack($message, $slackWebhook)
{
  // Make your message
  $data = array('payload' => json_encode(array('text' => $message)));

  // Use curl to send your message
  $c = curl_init($slackWebhook);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($c, CURLOPT_POST, true);
  curl_setopt($c, CURLOPT_POSTFIELDS, $data);
  curl_exec($c);
  curl_close($c);
}

?>