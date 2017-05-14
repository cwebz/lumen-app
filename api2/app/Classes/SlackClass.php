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
    * Function to post a message to slack
    * 
    * @param string $message
    * @param string $slackWebhook
    */
    public function sendSlackMsg($slackMessage, $slackWebhook)
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