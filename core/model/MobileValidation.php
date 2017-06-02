<?php
namespace Core\Model;
require_once "vendor/autoload.php";
$dir = get_cfg_var('mourjan.path');
include_once $dir.'/core/model/NoSQL.php';


use checkmobi\CheckMobiRest;
use \Core\Model\NoSQL;


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
    abstract public function getNumberCountryCode(int $number) : int;
    abstract public function getNumberCarrierName(int $number) : string; 
    
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
}

//(new MobileValidation(MobileValidation::NEXMO, MobileValidation::IosPlatform))->setUID(2)->setPin(1234)->sendSMS(9613287168, "hello", ['uid'=>2]);
//var_dump($mobileValidation->setUID(2)->setPin(1234)->sendSMS(9613287168, "hello", ['uid'=>2]));

//var_dump((new MobileValidation(MobileValidation::CheckMobiProvider))->setUID(2)->sendSMS(9613287168, "hello"));
