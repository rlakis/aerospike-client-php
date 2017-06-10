<?php
namespace Core\Model;

require_once "vendor/autoload.php";
$dir = get_cfg_var('mourjan.path');
include_once $dir.'/core/model/NoSQL.php';


use \checkmobi\CheckMobiRest;
use \Core\Model\NoSQL;

use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Signer\Key;
use \Lcobucci\JWT\Signer\Rsa\Sha256;


class MobileValidation
{
    use CheckMobiTrait;
    use NexmoTrait;
    
    
    const CHECK_MOBI                    = 1;
    const NEXMO                         = 2;
    
    const ANDROID                       = 0;
    const WEB                           = 1;
    const IOS                           = 2;
    
    const RESULT_OK                     = 1;
    const RESULT_ERR_ALREADY_ACTIVE     = 2;
    const RESULT_ERR_SENT_FEW_MINUTES   = 3;
    const RESULT_ERR_QOTA_EXCEEDED      = 4;
    const RESULT_ERR_NO_USER_ID         = 5;
    const RESULT_ERR_DB_FAILURE         = 6;
    const RESULT_ERR_UNKNOWN            = 9;

    const SMS_TYPE                      = 0;
    const CLI_TYPE                      = 1;
    const REVERSE_CLI_TYPE              = 2;
    
    private static $check_mobi_api;
    private static $nexmo_api;
    
    private $uid;
    private $pin;
    private $platform;
    private $provider;
    
    
    private $call_center = [
        12035802081, 
        12035802081, 
        12048192528, 
        12242144077, 
        18198035589, 
        19892591790, 
        33644630401, 
        447520619658, 
        447520632358, 
        447520635627, 
        46769436340, 
        48732232145, 
        48799353706, 
        601117227104];
    
    private $cli_numbers = [
        358841542210,
        442039061160,
        46850927966];
    
    
    private static $instance = null;
    
    function __construct(int $provider=MobileValidation::NEXMO, int $platform=MobileValidation::WEB) 
    {
        $this->provider = $provider;
        if ($provider==static::NEXMO)
        {
            $this->getNexmoClient();            
        }
        
        if ($provider==static::CHECK_MOBI)
        {
            $this->getCheckMobiClient();
        }
        
        $this->platform = $platform;    
        return $this;
    }
    
    
    protected function getNexmoClient()
    {
        if (!(MobileValidation::$nexmo_api))
        {
            MobileValidation::$nexmo_api = new \Nexmo\Client(new \Nexmo\Client\Credentials\Basic('8984ddf8', 'CVa3tHey3js6'));
        }
        return MobileValidation::$nexmo_api;        
    }
    
    
    protected function getCheckMobiClient()
    {
        if (!(MobileValidation::$check_mobi_api))
        {
            MobileValidation::$check_mobi_api = new CheckMobiRest('D38D5D58-572B-49EC-BAB5-63B6081A55E6');
        }
        return MobileValidation::$check_mobi_api;        
    }

    
    public static function getInstance(int $provider=MobileValidation::NEXMO) : MobileValidation
    {        
        if (!MobileValidation::$instance)
        {
            static::$instance = new MobileValidation($provider);            
        }
        else
        {
            static::$instance->provider=$provider;   
        }
        return static::$instance;
        
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
    

    public function setPlatform(int $platform)
    {
        $this->platform = $platform;
        return $this;
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
    
    
    protected function getUID() : int
    {
        return $this->uid;
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
    
    
    protected function getAllocatedNumber(bool $reverse=true) : int
    {
        if ($reverse)
        {
            $i = rand(0, count($this->call_center)-1);
            return $this->call_center[$i];
        }
        else
        {
            $i = rand(0, count($this->cli_numbers)-1);
            return $this->cli_numbers[$i];
        }
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
        return MobileValidation::getInstance(MobileValidation::NEXMO)->setPin($pin)->setUID($userId)->sendSMS($to, $message, $clientReference);
        //$mv = new MobileValidation(static::NEXMO);
        //return $mv->setPin($pin)->setUID($userId)->sendSMS($to, $message, $clientReference);
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

        return MobileValidation::RESULT_OK;
    }
    
    
    public function verifyStatus(string $requestId) : int 
    {
        if (substr($requestId, 0, 3)=='CLI') 
        {
            $response = $this->getCheckMobiClient()->ValidationStatus(['id'=>$requestId]);            
            //error_log(json_encode($response, JSON_PRETTY_PRINT));
            if (isset($response['status']) && $response['status']==200 && isset($response['response']) && isset($response['response']['validated']))
            {
                if ($response['response']['validated'])
                {
                    return 1;
                }
            }
        }
        else if (substr($requestId, 0, 5)=='NXCLI')
        {
            error_log("here");
        }    
        return 0;
    }
    
    
    public function sendCallerId($to)
    {
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::CLI_TYPE, $record);
        if ($status!=MobileValidation::RESULT_OK)
        {
            // mira
            //return $status;
        }

        $bins = [ASD\USER_UID=>$this->uid,
                ASD\USER_MOBILE_NUMBER=>$num,
                ASD\USER_MOBILE_ACTIVATION_CODE=>0,
                ASD\USER_MOBILE_FLAG=>$this->platform];

        if ($this->provider==MobileValidation::CHECK_MOBI)
        {
            if ($this->sendCheckMobiCallerId($to, $bins))
            {
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->getUID(), $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    return MobileValidation::RESULT_OK;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }

        }
        else if ($this->provider==static::NEXMO)
        {
            if ($this->nexmoCLI($to, $bins))
            {
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->getUID(), $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    return MobileValidation::RESULT_OK;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }            
        }
        
        return MobileValidation::RESULT_ERR_UNKNOWN;
        
    }


    public function requestReverseCLI($to, &$response) : int
    {
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::REVERSE_CLI_TYPE, $record);
        //if ($this->uid==2 && $status==MobileValidation::RESULT_ERR_ALREADY_ACTIVE)
        //{
        //    $status = MobileValidation::RESULT_OK;
        //}

        if ($status!=MobileValidation::RESULT_OK)
        {
            return $status;
        }
        
        if ($this->provider==static::NEXMO)
        {
            $response = $this->reverseNexmoCLI($to);
            return MobileValidation::RESULT_OK;
        }
        else if ($this->provider==static::CHECK_MOBI)
        {
            
        }
        return MobileValidation::RESULT_ERR_UNKNOWN;
        
    }
    
    
    public function sendSMS($to, $text, $reference=null, $unicode=null)
    {   
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::SMS_TYPE, $record);
        if ($status!= MobileValidation::RESULT_OK)
        {
            return $status;
        }
        
        $bins = [ASD\USER_UID=>$this->uid, 
                ASD\USER_MOBILE_NUMBER=>$num, 
                ASD\USER_MOBILE_ACTIVATION_CODE=>$this->pin, 
                ASD\USER_MOBILE_FLAG=>$this->platform];
            
        if ($this->provider==static::NEXMO)
        {
            if ($this->sendNexmoMessage($num, $text, $reference, $unicode, $bins))
            {
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    NoSQL::getInstance()->mobileIncrSMS($this->uid, $num);
                    return MobileValidation::RESULT_OK;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }
        }
        
        else if ($this->provider==static::CHECK_MOBI)
        {            
            if ($this->sendCheckMobiMessage($to, $text, $bins))
            {            
                $res = ($record) ? NoSQL::getInstance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::getInstance()->mobileInsert($bins);
                if ($res)
                {
                    NoSQL::getInstance()->mobileIncrSMS($this->uid, $num);
                    return MobileValidation::RESULT_OK;
                }
                return MobileValidation::RESULT_ERR_DB_FAILURE;
            }
        }
        
        return MobileValidation::RESULT_ERR_UNKNOWN;
    }
    
}


trait CheckMobiTrait
{
    abstract protected function getCheckMobiClient();
    abstract protected function getE164($number);
    abstract protected function getPlatformName();
    abstract protected function getPin();
    
    protected function isCheckMobiOk(array $response) : bool
    {
        if (isset($response['status']))
        {
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
        $response = $this->getCheckMobiClient()->SendSMS(["to"=>$this->getE164($to), "text"=>$text, "platform"=>$this->getPlatformName(), "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"]);    
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
        $response = $this->getCheckMobiClient()->RequestValidation(["type"=>"cli", "number"=>$this->getE164($to), "platform"=>$this->getPlatformName()]);
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
    abstract public function getNexmoClient();
    abstract protected function getUID() : int;
    abstract protected function getPlatform() : int;
    abstract protected function getE164($number);
    abstract protected function getAllocatedNumber(bool $reverse) : int;
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
        
        
        $message = $this->getNexmoClient()->message()->send([
                        'to' => $to,
                        'from' => $from,
                        'text' => $text,
                        'client-ref'=>$reference
                    ]);
        
        if ($message->count())
        {
            $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $message->getMessageId();
            $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::SMS_TYPE;
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


    private function genValidationUUID() 
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    public function nexmoCLI($to, &$bins) : bool
    {
        //$response = $this->getCheckMobiClient()->RequestValidation(["type"=>"cli", "number"=>$this->getE164($to), "platform"=>$this->getPlatformName()]);
        //if ($this->isCheckMobiOk($response))
        //{
        //    if (isset($response['response']['id']))
        //    {
                $bins[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE] = $this->getAllocatedNumber(FALSE);;
                $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
                $bins[ASD\USER_MOBILE_REQUEST_ID] = 'NXCLI-'.$this->genValidationUUID();
                $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::CLI_TYPE;
                NoSQL::getInstance()->inboundCall([
                    'conversation_uuid'=>$bins[ASD\USER_MOBILE_REQUEST_ID],
                    'direction'=>'inbound',
                    'date_added'=>time(),
                    'status'=>'started',
                    'from'=>$to,
                    'to'=>$bins[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE]
                    ], $this->getUID());
                return TRUE;
        //    }
        //}
        //return FALSE;
    }
    

    function fastCallText(int $from, int $to)
    {
        $action = '/calls';
        $application_id = "905c1bc6-ff6c-4767-812c-1b39d756bda6";
        $jwt = $this->generate_jwt($application_id, '/opt/ssl/nexmo.key');
        //Add the JWT to the request headers
        $headers =  array('Content-Type: application/json', "Authorization: Bearer " . $jwt ) ;

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

        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo $status, "\n";
        var_dump($res);
    }


    function reverseNexmoCLI(int $to) : array
    {
        
        $action = '/calls';

        $from = $this->getAllocatedNumber(TRUE);        
      
        $bins = [];
        if (($record = NoSQL::getInstance()->mobileFetch($this->getUID(), $to))!==FALSE)
        {
            if (empty($record))
            {
                $bins[ASD\USER_UID] = $this->getUID();
                $bins[ASD\USER_MOBILE_NUMBER] = $to;
            }
        }
        $bins[ASD\USER_MOBILE_ACTIVATION_CODE] = 0;
        $bins[ASD\USER_MOBILE_FLAG] = $this->getPlatform();
        $bins[ASD\USER_MOBILE_DATE_REQUESTED] = time();
        $bins[ASD\USER_MOBILE_REQUEST_ID] = "";
        $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::REVERSE_CLI_TYPE;
        
        //User and application information
        $application_id = "905c1bc6-ff6c-4767-812c-1b39d756bda6";
        $jwt = $this->generate_jwt($application_id, '/opt/ssl/nexmo.key');
        
        //Add the JWT to the request headers
        $headers =  array('Content-Type: application/json', "Authorization: Bearer " . $jwt ) ;
        
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

        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($res === FALSE)
        {
            $err = curl_error($ch);
            return array("status" => $status, "response" => array("error" => $err));
        }
        
        $result = json_decode($res, TRUE);
        NoSQL::getInstance()->outboundCall($result, $this->getUID());

        $response = [
            'id'=>$result['conversation_uuid'],
            'type'=>'reverse_cli',
            'cli_prefix'=>substr($from, 0, 5),
            'pin_hash'=>sha1(substr($from, -3, 3))
            ];
        
        if ($status==201)
        {            
            $bins[ASD\USER_MOBILE_ACTIVATION_CODE] = intval(substr($from, -4, 4));
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $response['id'];
            $bins[ASD\USER_MOBILE_PIN_HASH] = $response['pin_hash'];   
            
            if (isset($bins[ASD\USER_UID]))
            {
                $ok = NoSQL::getInstance()->mobileInsert($bins);
            }
            else
            {
                $ok = NoSQL::getInstance()->mobileUpdate($this->getUID(), $to, $bins);
            }
        }
        return array("status" => $status, "response" => $response);
    }
    
    
    public function verifyNexmoCallPin(string $id, int $pin) : array
    {
        $response = ["number"=>NULL, "validated"=>false, "validation_date"=>NULL, "charged_amount"=>0];
        $status = 400;
        if (($call = NoSQL::getInstance()->getCall($id))!==FALSE && !empty($call))
        {
            $status = 200;
            $response['number'] = $this->getE164( $call['to'] );
            $response['uid'] = $call[ASD\USER_UID];
            $response['charged_amount'] = 2.0*$call['price'];
            if (isset($call['validated']) && $call['validated'])
            {
                $response['validated'] = true;
                $response['validation_date'] = $call['valid_epoch'];     
            }
            else if ($pin===intval(substr($call['from'],-4,4)))
            {
                $response['validated'] = true;
                $response['validation_date'] = time();        
                NoSQL::getInstance()->outboundCall(['conversation_uuid'=>$id, 'direction'=>'outbound', 'status'=>'validated', 'validation_date'=>time()]);
            }
        }
        
        return ['status'=>$status, 'response'=>$response];
    }
    
    
    public function modifyNexmoCall(string $uuid)
    {
        $action = '/calls';
        $application_id = "905c1bc6-ff6c-4767-812c-1b39d756bda6";
        $basic  = new \Nexmo\Client\Credentials\Basic('8984ddf8', 'CVa3tHey3js6');
        
        $keypair = new \Nexmo\Client\Credentials\Keypair(file_get_contents('/opt/ssl/nexmo.key'), $application_id);
        $api = new \Nexmo\Client(new \Nexmo\Client\Credentials\Container($basic, $keypair));
        if ($uuid)
        {            
            //$call = $api->calls->get($id);
            $jwt = $this->generate_jwt($application_id, '/opt/ssl/nexmo.key');
       
            //Hangup the call
            $payload = '{
              "action": "hangup"
            }';
            //error_log(json_encode($call, JSON_PRETTY_PRINT));
            $hangup = new \Nexmo\Call\Hangup();
            //$call->put(json_encode($hangup));
            error_log($this->base_url . $this->version . $action  . "/" . $uuid);
            $ch = curl_init($this->base_url . $this->version . $action  . "/" . $uuid );
            curl_setopt($ch, CURLOPT_PUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($hangup));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization: Bearer " . $jwt ));
            $response = curl_exec($ch);
            error_log(json_encode($response));

        }
        
        //$json = json_decode(json_encode($hangup));
        //var_dump($json);
        
    }
    
}

if (php_sapi_name()=='cli')
{
    var_dump( MobileValidation::getInstance(MobileValidation::NEXMO)->setUID(2)->setPin(1234)->fastCallText(442039061160, 447520619658) );
    //MobileValidation::getInstance()->modifyNexmoCall("");
    //echo MobileValidation::getInstance()->setUID(2)->setPin(1234)->verifyNexmoCallPin("CON-8403beab-327c-4945-abb2-45e3b4627b08", 4077), "\n";
}
