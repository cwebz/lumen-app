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

    /*
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
    * Function to post a message to slack
    * 
    * @param string $message
    * @param string $slackWebhook
    */
    static public function sendSlackMsg($slackMessage, $slackWebhook)
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