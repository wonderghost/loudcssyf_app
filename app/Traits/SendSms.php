<?php

namespace App\Traits;

use Osms\Osms;
use App\Exceptions\AppException;

trait SendSms {

    protected $config = array(
        'clientId' => 'XdkjxfD1BMMskXTWitJ9s8U4a4eALTGO',
        'clientSecret' => 'WwlHkIinvPfvk7hy'
    );

    protected $senderAdress = "tel:+2240000";
    protected $senderName = "AFROCASH";

    public function sendSmsToNumber($phoneNumber,$message) {
        try {

            $sms = new Osms($this->config);
            $response = $sms->getTokenFromConsumerKey();
    
            if(empty($response['access_token'])) {
                throw new AppException("Erreur lors de l'envoi du message !");
            }

            $receiverAddress = 'tel:+224'.$phoneNumber;
            $theResponse = $sms->sendSMS($this->senderAdress,$receiverAddress, $message,$this->senderName);
            
            if(!empty($theResponse['error'])) {
                throw new AppException($theResponse['error']);
            }
    
            return true;
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}