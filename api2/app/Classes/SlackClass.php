<?php

namespace App\Classes;

class SlackClass{

    protected $slackWebhook;
    protected $slackMessage;

    /**
    * Get the slack message
    *
    * @return string
    */
    public function getSlackMessage(){
        return $this->slackMessage;
    }

    /**
    * Set the slack slackMessage 
    *
    * @param string $message;
    * @return $this
    */
    public function setSlackMessage($slackMessage){
        $this->slackMessage = $slackMessage;
        return $this;
    }



    /**
    * Create the URL for retrieving the data 
    *
    * @param string $dataType [Param for what kind of data to retrieve]
    * @param string $leagueID
    * @param string $week 
    * @param string $additionalArgs
    * @return string
    */
    public static function getMflLeagueDataUrl($dataType, $leagueID = '', $week = '', $additionalArgs = ''){
       
        $mflBaseUrl = 'https://www74.myfantasyleague.com/2017/export?';

        //Type of data to export
        $typeUrlArg = "TYPE={$dataType}";
        $leagueUrlArg = "&L={$leagueID}";
        $weekUrlArg = "&W={$week}";
        $jsonUrlArg = "&JSON=1";

        //Build the full URL
        return $mflBaseUrl . $typeUrlArg . $leagueUrlArg . $weekUrlArg . $additionalArgs . $jsonUrlArg;

    }

    /* DEPRECATED?
    * Generate the URL for getting information on players
    */
    public function getMflPlayerDataUrl($players){
        //Type of data to export
        $typeUrlArg = "TYPE=players";
        $playersUrlArg = "&PLAYERS={$players}";
        $jsonUrlArg = "&JSON=1";

        return MFL_BASE_URL . $typeUrlArg . $playersUrlArg . $jsonUrlArg;
    }

    /**
    * Function to return the JSON object of the data requested
    *
    * @param string $dataUrl 
    * @return string
    */
    public static function getMflData($dataUrl){
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
    * Separate out the picks in the string and return
    *
    * @param string $combinedString
    * @return Array 
    */
    public static function separatePlayersPicks($combinedString){
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

        $splitArray->draftPicks = $draftPickIds;
        $splitArray->players = $playerIds;
        return $splitArray;
    }



    /**
    * Function to post a message to slack
    * 
    * @param string $message
    * @param string $slackWebhook
    */
    public static function sendSlackMsg($slackMessage, $slackWebhook)
    {
    // Make your message
    $data = array('payload' => json_encode(array('text' => $slackMessage)));

    // Use curl to send your message
    $c = curl_init($slackWebhook);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_exec($c);
    curl_close($c);
    }


}


?>