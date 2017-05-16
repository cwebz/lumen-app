<?php

include_once('mfl-integration-task.php');

//Get the franchise map file
global $franchiseMap;
$mapFile = @file_get_contents("files/franchise-map.json");
$franchiseMap = json_decode($mapFile);

define('DRAFT', 'draftResults');
define('SLACK_DRAFT_WEBHOOK', 'https://hooks.slack.com/services/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');

//Get the most recent trade timestamp on file
$mapFile = @file_get_contents("files/most-recent-draft.txt");
$mostRecentDraft = $mapFile;

$updateDraftFile = false;

//Get the URL and Data
$mflDataUrl = getMflLeagueDataUrl(DRAFT, LEAGUE_ID, WEEK, '');
$mflDataObj = getMflData($mflDataUrl);

$draftPicks = $mflDataObj->draftResults->draftUnit->draftPick;

foreach($draftPicks as $draftPick){
    //These will need to be time comparisons
    if( (int)$draftPick->timestamp > (int)$mostRecentDraft 
        || !$mostRecentDraft){
        
        $updateDraftFile = true;

        if($draftPick->player){
            $playerPicked = getPrettyPlayers( array($draftPick->player) );
        }else{
            exit();
        }

        $franchiseName = getFranchiseName($draftPick->franchise);
        $round = ltrim($draftPick->round, "0");
        $pick = ltrim($draftPick->pick, "0");
        $timestamp = $draftPick->timestamp;

        switch((int)$round % 10){
            case 1: $round .= 'st'; break;
            case 2: $round .= 'nd'; break;
            case 3: $round .= 'rd'; break;
            default: $round .= 'th'; break;
        }

        switch((int)$pick % 10){
            case 1: $pick .= 'st'; break;
            case 2: $pick .= 'nd'; break;
            case 3: $pick .= 'rd'; break;
            default: $pick .= 'th'; break;
        }

        $fullSlackMsg = "With the *{$pick}* pick in the *{$round}* round";
        $fullSlackMsg .= " *{$franchiseName}* has selected...\n";
        $fullSlackMsg .= implode("\n", $playerPicked);

        slack($fullSlackMsg, SLACK_DRAFT_WEBHOOK);

        //Write timestamp to file
        if($updateDraftFile === true){
            $draftFile = fopen("files/most-recent-draft.txt", "w") or die("Unable to open file!");
            fwrite($draftFile, $timestamp);
            fclose($draftFile);
        }

        exit();
    }
}

exit("hi");
?>
