<?php
namespace Core\Model;
require_once "vendor/autoload.php";
$dir = get_cfg_var('mourjan.path');
include_once $dir.'/core/model/NoSQL.php';


use checkmobi\CheckMobiRest;
use \Core\Model\NoSQL;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;


class MobileValidation
{
    use CheckMobiTrait;
    use NexmoTrait;
    
    
    const CHECK_MOBI                = 1;
    const NEXMO                     = 2;
    
    const AndroidPlatform           = 0;
    const WebPlatform               = 1;
    const IosPlatform               = 2;
    
    const RESULT_Ok                     = 1;
    const RESULT_ERR_ALREADY_ACTIVE     = 2;
    const RESULT_ERR_SENT_FEW_MINUTES   = 3;
    const RESULT_ERR_QOTA_EXCEEDED      = 4;
    const RESULT_ERR_NO_USER_ID         = 5;
    const RESULT_ERR_DB_FAILURE         = 6;
    const RESULT_ERR_UNKNOWN            = 9;

    private static $SMS_TYPE            = 0; 
    
    private $api;
    private $uid;
    private $pin;
    private $platform;
    
    
    private $call_center = [12242144077, 33644630401];
    
    
    function __construct(int $provider=MobileValidation::NEXMO, int $platform=MobileValidation::WebPlatform) 
    {
        if ($provider==static::NEXMO)
        {
            $this->api = new \Nexmo\Client(new \Nexmo\Client\Credentials\Basic('8984ddf8', 'CVa3tHey3js6'));
        }
        
        if ($provider==static::CHECK_MOBI)
        {
            $this->api = new CheckMobiRest('D38D5D58-572B-49EC-BAB5-63B6081A55E6');
        }
        
        $this->platform = $platform;    
        return $this;
    }
    
    
    public function setUID(int $userId)
    {
        $this->uid = $userId;
        return $this;
    }
    
    
    public function setPin(int $pinCode)
    {
        $this->pin = $pinCode;
        return $this;
    }
    
    
    protected function getClient()
    {
        return $this->api;
    }
    
    protected function getPlatform() : int
    {
        return $this->platform;
    }
    
    protected function getPlatformName() : string
    {
        $result = "web";
        if ($this->platform==0) $result = "android";
        if ($this->platform==2) $result = "ios";
        
        return $result;
    }
    
    protected function getPin() : int
    {
        return $this->pin;
    }
    
    protected function getE164($number, $as_int=false)
    {
        $num= intval($number);        
        return $as_int ? $num : "+{$num}";
    }
    
    
    protected function getAllocatedNumber() : int
    {
        $i = rand(0, count($this->call_center)-1);
        return $this->call_center[$i];
    }
    
    public function getNumberCountryCode(int $number) : int
    {
        $num = \libphonenumber\PhoneNumberUtil::getInstance()->parse("+{$number}", 'LB');
        return $num->getCountryCode();
    }
    
    public function getNumberCarrierName(int $number) : string
    {
        $num = \libphonenumber\PhoneNumberUtil::getInstance()->parse("+{$number}", 'LB');
        return \libphonenumber\PhoneNumberToCarrierMapper::getInstance()->getNameForNumber($num, "en");
    }

    
    public static function send($to, $message, $userId, $pin, $clientReference=null, $unicode=null)
    {
        $mv = new MobileValidation(static::NEXMO);
        return $mv->setPin($pin)->setUID($userId)->sendSMS($to, $message, $clientReference);
    }
    
    
    public function sendSMS($to, $text, $reference=null, $unicode=null)
    {   
        if (!($this->uid))
        {
            return MobileValidation::RESULT_ERR_NO_USER_ID;
        }
        
        $num = $this->getE164($to, TRUE);
        $record = NoSQL::getInstance()->mobileFetch($this->uid, $num);
        if ($record)
        {
            $activation_time = $record[ASD\USER_MOBILE_DATE_ACTIVATED] ?? 0;
            if ($activation_time > time()-31536000) // more than one year
            {
                return MobileValidation::RESULT_ERR_ALREADY_ACTIVE;
            }
            
            $type = $record[ASD\USER_MOBILE_VALIDATION_TYPE] ?? 0;
            if ($type==MobileValidation::$SMS_TYPE)
            {
                $age = time()-$record[ASD\USER_MOBILE_DATE_REQUESTED];
                if ($age<=600)
                {
                    return MobileValidation::RESULT_ERR_SENT_FEW_MINUTES;
                }
                if ($record[ASD\USER_MOBILE_SENT_SMS_COUNT]>10)
                {
                    return MobileValidation::RESULT_ERR_QOTA_EXCEEDED;
                }
            }
        }
        
        $bins = [ASD\USER_UID=>$this->uid, 
                ASD\USER_MOBILE_NUMBER=>$num, 
                ASD\USER_MOBILE_ACTIVATION_CODE=>$this->pin, 
                ASD\USER_MOBILE_FLAG=>$this->platform];
        
        if ($this->api instanceof \Nexmo\Client)
        {
            if ($this->sendNexmoMessage($num, $text, $reference, $unicode, $bins))
            {
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    NoSQL::getInstance()->mobileIncrSMS($this->uid, $num);
                    return MobileValidation::RESULT_Ok;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }
        }
        
        if ($this->api instanceof \checkmobi\CheckMobiRest)
        {            
            if ($this->sendCheckMobiMessage($to, $text, $bins))
            {            
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    NoSQL::getInstance()->mobileIncrSMS($this->uid, $num);
                    return MobileValidation::RESULT_Ok;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }
        }
        
        return MobileValidation::RESULT_ERR_UNKNOWN;
    }
    
}


trait CheckMobiTrait
{
    abstract protected function getClient();
    abstract protected function getE164($number);
    abstract protected function getPlatformName();
    abstract protected function getPin();
    
    protected function isCheckMobiOk(array $response) : bool
    {
        if (isset($response['status']))
        {
            var_dump($response);
            if ($response['status']==200)
            {
                return isset($response['response']) && is_array($response['response']);
            }
            else return ($response['status']==204);
                
        }
        return FALSE;
    }
    
    
    public function sendCheckMobiMessage($to, $text, &$bins) : bool
    {
        $response = $this->getClient()->SendSMS(["to" => $this->getE164($to), "text"=>$text, "platform"=>$this->getPlatformName(), "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"]);    
        if ($this->isCheckMobiOk($response))
        {
            if (isset($response['response']['id']))
            {
                $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
                $bins[ASD\USER_MOBILE_REQUEST_ID] = $response['response']['id'];
                $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = 0;
                return TRUE;
            }
        }
        return FALSE;
    }
    
}


trait NexmoTrait
{
    abstract public function getClient();
    abstract protected function getAllocatedNumber() : int;
    abstract public function getNumberCountryCode(int $number) : int;
    abstract public function getNumberCarrierName(int $number) : string; 
    
    function generate_jwt( $application_id, $keyfile) 
    {
        $jwt = false;
        date_default_timezone_set('UTC');    //Set the time for UTC + 0
        $key = file_get_contents($keyfile);  //Retrieve your private key
        $signer = new Sha256();
        $privateKey = new Key($key);

        $jwt = (new Builder())->setIssuedAt(time() - date('Z'))->set('application_id', $application_id)->setId( base64_encode( mt_rand (  )), true)->sign($signer,  $privateKey)->getToken();

        return $jwt;
    }


    public function sendNexmoMessage($to, $text, $reference=null, $unicode=null, &$bins)
    {
        $from = 'mourjan';
        
        $countryCode = $this->getNumberCountryCode($to);
        switch ($countryCode) 
        {
            case 1:
                $from = '12242144077';
                break;
                            
            case 212:
                $from = '33644630401';
                break;

            case 974:
                if ($this->getNumberCarrierName($to)=='ooredoo')
                {
                    $from = '12242144077';
                }
                break;                
        }
        
        if (is_array($reference))
        {
            $reference = json_encode($reference);
        }
        
        
        $message = $this->getClient()->message()->send([
                        'to' => $to,
                        'from' => $from,
                        'text' => $text,
                        'client-ref'=>$reference
                    ]);
        
        if ($message->count())
        {
            $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $message->getMessageId();
            $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = 0;
            return TRUE;
        }
       
        return FALSE;
    }
    
    
    function reverseNexmoCLI(int $to)
    {
        $from = $this->getAllocatedNumber();
        $ncco = '[
        {
            "action": "talk",
            "voiceName": "Russell",
            "text": "Hi, Thank you for using mourjan classifieds"
        }
        ]';
        //header('Content-Type: application/json');
        //echo $ncco;
        
        //Connection information
        $base_url = 'https://api.nexmo.com' ;
        $version = '/v1';
        $action = '/calls';

        //User and application information
        $application_id = "905c1bc6-ff6c-4767-812c-1b39d756bda6";
        $jwt = $this->generate_jwt($application_id, '/root/private.key');
        
        //Add the JWT to the request headers
        $headers =  array('Content-Type: application/json', "Authorization: Bearer " . $jwt ) ;
        
        //Change the to parameter to the number you want to call
    
        $in = ['to'=>[['type'=>'phone', 'number'=> strval($to)]], 'from'=>[['type'=>'phone', 'number'=> strval($from)]], 
            'answer_url'=>["https://nexmo-community.github.io/ncco-examples/first_call_talk.json"],
            'event_url'=>["https://dv.mourjan.com/api/NexmoEvent.php"],
            ];
        $payload = json_encode($in, JSON_UNESCAPED_SLASHES);
        $payload = '{
            "to":[{
                "type": "phone",
                "number": "' .$to.'"
            }],
            "from": {
                "type": "phone",
                "number": "'.$from.'"
            },
            "answer_url": ["https://nexmo-community.github.io/ncco-examples/first_call_talk.json"],
            "event_url": ["https://dv.mourjan.com/api/NexmoEvent.php"],
            "ringing_timer":8
            }';
        
        //Create the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url . $version . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);

        echo $jwt, "\n\n";
        //echo $payload, "\n\n";
        echo "\n", $response, "\n\n";


        
    }
}

(new MobileValidation(MobileValidation::NEXMO, MobileValidation::IosPlatform))->setUID(2)->setPin(1234)->reverseNexmoCLI(9613287168);
//var_dump($mobileValidation->setUID(2)->setPin(1234)->sendSMS(9613287168, "hello", ['uid'=>2]));

//var_dump((new MobileValidation(MobileValidation::CheckMobiProvider))->setUID(2)->sendSMS(9613287168, "hello"));
