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
    private static $CLI_TYPE            = 1;
    private static $REVERSE_CLI_TYPE    = 2;
    
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


    private function checkUserMobileStatus(int $to, int $vt, &$record) : int
    {
        if (!($this->uid))
        {
            return MobileValidation::RESULT_ERR_NO_USER_ID;
        }
        
        $record = NoSQL::getInstance()->mobileFetch($this->uid, $to);
        if ($record)
        {
            $activation_time = $record[ASD\USER_MOBILE_DATE_ACTIVATED] ?? 0;
            if ($activation_time > time()-31536000) // more than one year
            {
                return MobileValidation::RESULT_ERR_ALREADY_ACTIVE;
            }

            $type = $record[ASD\USER_MOBILE_VALIDATION_TYPE] ?? 0;
            if ($type==$vt)
            {
                $age = time()-$record[ASD\USER_MOBILE_DATE_REQUESTED];
                if ($age<=180)
                {
                    return MobileValidation::RESULT_ERR_SENT_FEW_MINUTES;
                }
                if ($record[ASD\USER_MOBILE_SENT_SMS_COUNT]>10)
                {
                    return MobileValidation::RESULT_ERR_QOTA_EXCEEDED;
                }
            }
        }

        return MobileValidation::RESULT_Ok;
    }
    
    
    public function sendCallerId($to)
    {
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::$CLI_TYPE, $record);
        if ($status!= MobileValidation::RESULT_Ok)
        {
            return $status;
        }

        $bins = [ASD\USER_UID=>$this->uid,
                ASD\USER_MOBILE_NUMBER=>$num,
                ASD\USER_MOBILE_ACTIVATION_CODE=>0,
                ASD\USER_MOBILE_FLAG=>$this->platform];

        if ($this->api instanceof \checkmobi\CheckMobiRest)
        {
            if ($this->sendCheckMobiCallerId($to, $bins))
            {
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    //NoSQL::getInstance()->mobileIncrSMS($this->uid, $num);
                    return MobileValidation::RESULT_Ok;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }

        }
        return MobileValidation::RESULT_ERR_UNKNOWN;
        
    }


    public function sendSMS($to, $text, $reference=null, $unicode=null)
    {   
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::$SMS_TYPE, $record);
        if ($status!= MobileValidation::RESULT_Ok)
        {
            return $status;
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
        $response = $this->getClient()->SendSMS(["to"=>$this->getE164($to), "text"=>$text, "platform"=>$this->getPlatformName(), "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"]);    
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


    public function sendCheckMobiCallerId($to, &$bins) : bool
    {
        $response = $this->getClient()->RequestValidation(["type"=>"cli", "number"=>$this->getE164($to), "platform"=>$this->getPlatformName()]);
        if ($this->isCheckMobiOk($response))
        {
            if (isset($response['response']['id']))
            {
                $bins[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE] = intval($response['response']['dialing_number']);
                $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
                $bins[ASD\USER_MOBILE_REQUEST_ID] = $response['response']['id'];
                $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = 1;
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

    private $api_key = '8984ddf8';
    private $api_secret = 'CVa3tHey3js6';
    private $base_url = 'https://api.nexmo.com';
    private $version = '/v1';


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


    function applicationsInfo()
    {
        $action = '/applications/?';
        $url = $this->base_url . $this->version . $action . http_build_query([
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret
                ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        if (strpos($header, '200'))
        {
            $decoded_response = json_decode($body, true);
            echo("You have " . $decoded_response['count'] . " applications\n");
            echo("Page " . $decoded_response['page_index']
            . " lists " . $decoded_response['page_size'] . " applications\n");
            echo("Use the links to navigate. For example: "
            . $this->base_url .  $decoded_response['_links']['last']['href'] . "\n"  );

            $applications = $decoded_response['_embedded']['applications'] ;

            foreach ( $applications as $application )
            {
                echo "  Application ID is:" . $application['id'] . "\n"  ;
                foreach($application['voice']['webhooks'] as $webhook)
                {
                    echo ( "    " . $webhook['endpoint_type'] . " is " . $webhook['endpoint'] . "\n"  );
                }
            }
        }
        else
        {
            $error = json_decode($body, true);
            echo("Your request failed because:\n");
            echo("  " . $error['type'] . "  " . $error['error_title']   );
        }
    }


    public function updateApplications($app_id)
    {
        $action = '/applications/';
        

        $url = $this->base_url . $this->version . $action .  $app_id .'?'. http_build_query([
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'name' => 'Mourjan',
                'type' => 'voice',
                'answer_url' => 'https://dv.mourjan.com/api/nexmo/ncco.php',
                'event_url' => 'https://dv.mourjan.com/api/nexmo/call_event.php'
            ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        if (strpos($header, '200'))
        {
            $application = json_decode($body, true);
            echo("Application " . $application['name'] . " has an ID of:" . $application['id'] . "\n" ) ;
            echo("  Use the links to navigate. For example: " . $this->base_url .  $application['_links']['self']['href'] . "\n"  );
            foreach($application['voice']['webhooks'] as $webhook)
                echo ( "  " . $webhook['endpoint_type'] . " is " . $webhook['endpoint'] . "\n"  );
            echo("  You use your private_key to connect to Nexmo endpoints:\n" ) ;
            echo("  " . ($application['keys']['private_key'] ?? ''));

        }
        else
        {
            $error = json_decode($body, true);
            echo("Your request failed because:\n");
            echo("  " . $error['type'] . "  " . $error['error_title']   );
        }

        return $this;
    }



    function reverseNexmoCLI(int $to)
    {
        //Connection information
        $action = '/calls';


        $from = $this->getAllocatedNumber();
        $ncco = '[
        {
            "action": "talk",
            "voiceName": "Russell",
            "text": "Hi, Thank you for using mourjan classifieds"
        }
        ]';       
        
      
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
            "answer_url": ["https://dv.mourjan.com/api/nexmo/ncco.php"],
            "event_url": ["https://dv.mourjan.com/api/nexmo/call_event.php"],
            "ringing_timer":10
            }';
        
        //Create the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . $this->version . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);

        //echo $payload, "\n\n";
        echo "\n", $response, "\n\n";


        
    }
}

if (0)
{
    $validator = new MobileValidation(MobileValidation::CHECK_MOBI, MobileValidation::IosPlatform);
    var_dump($validator->setUID(2)->setPin(1234)->sendCallerId(9613287168));
}
        //// reverseNexmoCLI(9613287168);
//var_dump($mobileValidation->setUID(2)->setPin(1234)->sendSMS(9613287168, "hello", ['uid'=>2]));

//var_dump((new MobileValidation(MobileValidation::CheckMobiProvider))->setUID(2)->sendSMS(9613287168, "hello"));
