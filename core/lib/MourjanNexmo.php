<?php
require_once 'vendor/autoload.php';
require_once get_cfg_var('mourjan.path'). '/config/cfg.php';
include_once $config['dir'].'/core/lib/nexmo/NexmoMessage.php';

class MourjanNexmo extends NexmoMessage{
        
    function __construct () {
        parent::__construct('8984ddf8', 'CVa3tHey3js6');
    }
    
    function sendSMS($to, $message, $clienRef=null, $unicode=null){
        $validator = libphonenumber\PhoneNumberUtil::getInstance();
        $num = $validator->parse($to, 'LB');
        $countryCode = $num->getCountryCode();
        $carrierMapper = libphonenumber\PhoneNumberToCarrierMapper::getInstance();
        $carrier = $carrierMapper->getNameForNumber($num, "en");
        $from = ($countryCode==1 || ($countryCode==974 && $carrier=='ooredoo')) ? '12242144077' : 'mourjan';
        if($countryCode==212){
            $from = '33644630401';
        }
        return parent::sendText($to, $from, $message, $clienRef, $unicode);
    }
    
}