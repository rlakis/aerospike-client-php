<?php
require_once 'vendor/autoload.php';
$dir = get_cfg_var('mourjan.path');
include_once $dir.'/core/lib/nexmo/NexmoMessage.php';
include_once $dir.'/core/model/NoSQL.php';

use checkmobi\CheckMobiRest;
use Core\Model\NoSQL;


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
    public static function send($to, $message, $clientReference=null, $unicode=null, $full_response=false)
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
            /*
            case 20:
            case 212:
            case 971:                
                $response = CheckMobiRequest::sendSMS($to, $message);
                if ($full_response)
                {
                    $response['messagecount'] = isset($response['status']) && $response['status']==200 && isset($response['response']['id']);
                    return $response;
                }
                else
                {
                    return isset($response['status']) && $response['status']==200 && isset($response['response']['id']);
                }
               */
            case 212:
                $from = '33644630401';
                break;

            case 974:
                if ($carrier=='ooredoo')
                {
                    $from = '12242144077';
                }
                break;
        }
        //$from = ($countryCode==1 || ($countryCode==974 && $carrier=='ooredoo')) ? '12242144077' : ($countryCode==212) ? '33644630401' : 'mourjan';
        if (is_array($clientReference))
        {
            $clientReference = json_encode($clientReference);
        }
        
        $provider = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
        $response = $provider->sendText($to, $from, $message, $clientReference, $unicode);
      
        return $full_response ? $response : $response->messagecount ?? 0;
    }
}


class CheckMobiRequest
{
    private static $secret = "D38D5D58-572B-49EC-BAB5-63B6081A55E6";
    // platform	string	Optional. One of the following values: ios, android, web, desktop
    
    public static function getCallerId($to, $uid=0, $platform='web')
    {
        $number = intval($to);
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        $response = $api->RequestValidation(["type" => "cli", "number" => "+{$number}", "platform"=>$platform]);    // , "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"
        if ($uid>0)
        {
            $flag = ($platform=='web') ? 1 : ($platform=='ios' ? 2 : 0);
            if (isset($response['status']) && $response['status']==200 && isset($response['response']))
            {
                $bins = [\Core\Model\ASD\USER_UID => $uid, 
                        \Core\Model\ASD\USER_MOBILE_NUMBER => $number,
                        \Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => intval($response['response']['dialing_number']),
                        \Core\Model\ASD\USER_MOBILE_FLAG => $flag,
                        \Core\Model\ASD\USER_MOBILE_REQUEST_ID => $response['response']['id'],
                        \Core\Model\ASD\USER_MOBILE_VALIDATION_TYPE => 1,
                    ];
                
                if (NoSQL::getInstance()->mobileInsert($bins)>0)
                {
                    $response['saved']=1;
                }
            }
        }
        return $response;
    }

    
    public static function reverseCallerId($to, $uid=0, $platform='web')
    {
        $number = intval($to);
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        $response = $api->RequestValidation(["type" => "reverse_cli", "number" => "+{$number}"]); //, "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"
        
        if ($uid>0)
        {
            $flag = ($platform=='web') ? 1 : ($platform=='ios' ? 2 : 0);
            if (isset($response['status']) && $response['status']==200 && isset($response['response']))
            {
                $bins = [\Core\Model\ASD\USER_UID => $uid, 
                        \Core\Model\ASD\USER_MOBILE_NUMBER => $number,
                        \Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => intval($response['response']['cli_prefix']),
                        \Core\Model\ASD\USER_MOBILE_FLAG => $flag,
                        \Core\Model\ASD\USER_MOBILE_REQUEST_ID => $response['response']['id'],
                        \Core\Model\ASD\USER_MOBILE_VALIDATION_TYPE => 2
                    ];

                if (NoSQL::getInstance()->mobileExists($uid, $number))
                {
                    if (NoSQL::getInstance()->mobileUpdate($uid, $number, $bins))
                    {
                        $response['saved']=1;
                    }

                }
                else
                {
                    if (NoSQL::getInstance()->mobileInsert($bins))
                    {
                        $response['saved']=1;
                    }
                }
            }
        }
        return $response;
    }

    
    public static function SMS($to, $uid=0, $platform='web')
    {
        $number = intval($to);
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        $response = $api->RequestValidation(["type" => "sms", "number" => "+{$number}", "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"]); //, 
        
        if ($uid>0)
        {
            $flag = ($platform=='web') ? 1 : ($platform=='ios' ? 2 : 0);
            if (isset($response['status']) && $response['status']==200 && isset($response['response']))
            {
                $bins = [\Core\Model\ASD\USER_UID => $uid, 
                        \Core\Model\ASD\USER_MOBILE_NUMBER => $number,
                        \Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => intval($response['response']['validation_info']['country_code']),
                        \Core\Model\ASD\USER_MOBILE_FLAG => $flag,
                        \Core\Model\ASD\USER_MOBILE_REQUEST_ID => $response['response']['id'],
                        \Core\Model\ASD\USER_MOBILE_VALIDATION_TYPE => 0,
                    ];
                
                if (NoSQL::getInstance()->mobileInsert($bins)>0)
                {
                    $response['saved']=1;
                }
            }
        }
        var_dump($response);
        
        return $response;
    }
    
    public static function verifyStatus($requestId)
    {
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        //error_log($requestId);
        $response = $api->ValidationStatus(['id'=>$requestId]);
        //error_log(json_encode($response));
        
        return $response;
    }

    
    public static function verifyPin($requestId, $pin)
    {
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        $response = $api->VerifyPin(['id'=>$requestId, 'pin'=>$pin]);
        error_log(json_encode($response));
        
        return $response;
    }

    
    public static function hangUpCall($requestId)
    {
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        return $api->HangUpCall( ['id'=>$requestId] );
    }
    
    
    public static function sendSMS($to, $message)
    {
        $number = intval($to);
        $api = new CheckMobiRest(CheckMobiRequest::$secret);
        $response = $api->SendSMS(array("to" => "+{$number}", "text"=>$message, "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"));
        return $response;
    }
}

//CheckMobiRequest::SMS(201110110013);