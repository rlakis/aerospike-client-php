<?php
require_once 'vendor/autoload.php';
$dir = get_cfg_var('mourjan.path');
include_once $dir.'/core/lib/nexmo/NexmoMessage.php';

class MourjanNexmo extends NexmoMessage
{
        
    function __construct () 
    {
        parent::__construct('8984ddf8', 'CVa3tHey3js6');
    }
    
    
    function sendSMS($to, $message, $clienRef=null, $unicode=null)
    {
        if(!preg_match('/^(?:00|\+)/', $to))
        {
            $to = '+'.$to;
        }
        
        $validator = libphonenumber\PhoneNumberUtil::getInstance();
        $num = $validator->parse($to, 'LB');
        $countryCode = $num->getCountryCode();
        $carrierMapper = libphonenumber\PhoneNumberToCarrierMapper::getInstance();
        $carrier = $carrierMapper->getNameForNumber($num, "en");
        $from = ($countryCode==1 || ($countryCode==974 && $carrier=='ooredoo')) ? '12242144077' : 'mourjan';
        
        if($countryCode==212)
        {
            $from = '33644630401';
        }
        
        return parent::sendText($to, $from, $message, $clienRef, $unicode);
    }
    
}

class ShortMessageService
{
    public static function send($to, $message, $clientReference=null, $unicode=null)
    {
        if(!preg_match('/^(?:00|\+)/', $to))
        {
            $to = '+'.$to;
        }
        
        $num = libphonenumber\PhoneNumberUtil::getInstance()->parse($to, 'LB');
        $countryCode = $num->getCountryCode();
        $carrier = libphonenumber\PhoneNumberToCarrierMapper::getInstance()->getNameForNumber($num, "en");
        $from = 'mourjan';
        switch ($countryCode) 
        {
            case 1:
                $from = '12242144077';
                break;
            
            case 212:
                $from = '33644630401';
                break;

            case 974:
                if ($carrier=='ooredoo')
                {
                    $from = '12242144077';
                }
                break;
                
            default:
                break;
        }
        //$from = ($countryCode==1 || ($countryCode==974 && $carrier=='ooredoo')) ? '12242144077' : ($countryCode==212) ? '33644630401' : 'mourjan';
        if (is_array($clientReference))
        {
            $clientReference = json_encode($clientReference);
        }
        
        $provider = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
        $response = $provider->sendText($to, $from, $message, $clientReference, $unicode);
        return $response->messagecount ?? 0;
    }
}