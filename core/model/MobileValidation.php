<?php
namespace Core\Model;

$dir = get_cfg_var('mourjan.path');
require_once $dir.'/deps/autoload.php';
require_once __DIR__.'/../../config/cfg.php';
\Config::instance()->incModelFile('NoSQL');

use \Core\Model\NoSQL;
use Berysoft;


class MobileValidation {
    use CheckMobiTrait;
    use EdigearTrait;
        
    const CHECK_MOBI                    = 1;
    const EDIGEAR                       = 3;
    
    const ANDROID                       = 0;
    const WEB                           = 1;
    const IOS                           = 2;
    
    const RESULT_OK                     = 1;
    const RESULT_ERR_ALREADY_ACTIVE     = 2;
    const RESULT_ERR_SENT_FEW_MINUTES   = 3;
    const RESULT_ERR_QOTA_EXCEEDED      = 4;
    const RESULT_ERR_NO_USER_ID         = 5;
    const RESULT_ERR_DB_FAILURE         = 6;
    const RESULT_ERR_CALL_DONE          = 7;
    const RESULT_ERR_UNKNOWN            = 9;

    const SMS_TYPE                      = 0;
    const CLI_TYPE                      = 1;
    const REVERSE_CLI_TYPE              = 2;
    
    private $uid;
    private $pin;
    private $platform;
    private $provider;
    
    private static $check_mobi_api;
    private static $edigear_api;
    
    private static $instance = null;
    
    
    function __construct(int $provider=MobileValidation::EDIGEAR, int $platform=MobileValidation::WEB) {
        $this->uid = 0;
        $this->provider = $provider;

        switch ($this->provider) {            
            case static::CHECK_MOBI:
                $this->getCheckMobiClient();
                break;
            
            case static::EDIGEAR:
                $this->getEdigearClient();
                break;
            
            default:
                break;
        }
        
        $this->platform = $platform;    
        return $this;
    }
    
    
    
    protected function getCheckMobiClient() {
        if (!(MobileValidation::$check_mobi_api)) {
            MobileValidation::$check_mobi_api = new CheckMobiRest('D38D5D58-572B-49EC-BAB5-63B6081A55E6');
        }
        return MobileValidation::$check_mobi_api;        
    }

    
    protected function getEdigearClient() : Berysoft\Edigear {
        if (!(MobileValidation::$edigear_api)) {
            MobileValidation::$edigear_api = \Berysoft\Edigear::getInstance()->
                    setSecretKey("D38D5D58-572B-49EC-BAB5-63B6081A55E6");
        }
        return MobileValidation::$edigear_api;        
    }
    
    
    public static function getInstance(int $provider=MobileValidation::EDIGEAR) : MobileValidation {        
        if (!MobileValidation::$instance) {
            static::$instance = new MobileValidation($provider);            
        }
        else {
            static::$instance->provider=$provider;   
        }
        return static::$instance;
        
    }
    
    
    public function setUID(int $userId) {
        $this->uid = $userId;
        return $this;
    }
    
    
    public function setPin(int $pinCode) {
        $this->pin = $pinCode;
        return $this;
    }
    

    public function setPlatform(int $platform) {
        $this->platform = $platform;
        return $this;
    }
    
    
    
    protected function getPlatform() : int {
        return $this->platform;
    }
    
    
    protected function getPlatformName() : string {
        switch ($this->platform) {
            case 0:
                $result = "android";
                break;
            case 2:
                $result = "ios";
                break;
            default:
                $result = "web";
                break;
        }        
        return $result;
    }
    
    
    protected function getUID() : int {
        return $this->uid;
    }
    
    
    protected function getPin() : int {
        return $this->pin;
    }
    
    
    protected function getE164($number, $as_int=false) {
        $num= intval($number);        
        return $as_int ? $num : "+{$num}";
    }
        
    
    public function getNumberCountryCode(int $number) : int {
        $num = \libphonenumber\PhoneNumberUtil::getInstance()->parse("+{$number}", '');
        return $num->getCountryCode();
    }
    
    
    public function getNumberRegionCode(int $number) : string {
        $num = \libphonenumber\PhoneNumberUtil::getInstance()->parse("+{$number}", '');
        return \libphonenumber\PhoneNumberUtil::getInstance()->getRegionCodeForNumber($num);
        //$geoCoder = \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance();
        //$geoCoder->getDescriptionForNumber($number, $locale)
        //return $num->getCountryCodeSource();
    }
    
    
    public function getNumberCarrierName(int $number) : string {
        $num = \libphonenumber\PhoneNumberUtil::getInstance()->parse("+{$number}", 'LB');
        return \libphonenumber\PhoneNumberToCarrierMapper::getInstance()->getNameForNumber($num, "en");
    }

    
    public static function send($to, $message, $userId, $pin, $clientReference=null, $unicode=null) {
        return MobileValidation::getInstance(MobileValidation::EDIGEAR)->setPin($pin)->setUID($userId)->sendSMS($to, $message, $clientReference);
    }


    private function checkUserMobileStatus(int $to, int $vt, &$record=[]) : int {
        if (!($this->uid)) {
            return MobileValidation::RESULT_ERR_NO_USER_ID;
        }
        
        $record = NoSQL::instance()->mobileFetch($this->getUID(), $to);
        if ($record) {
            $activation_time = $record[ASD\USER_MOBILE_DATE_ACTIVATED] ?? 0;
            if ($activation_time+31536000 > time()) { // more than one year          
                return MobileValidation::RESULT_ERR_ALREADY_ACTIVE;
            }

            $type = $record[ASD\USER_MOBILE_VALIDATION_TYPE] ?? 0;
            if ($type==$vt) {
                if (isset($record[ASD\USER_MOBILE_DATE_REQUESTED])) {
                    $age = time()-$record[ASD\USER_MOBILE_DATE_REQUESTED];
                    if ($age<=180) {
                        if (($vt!= MobileValidation::SMS_TYPE) && (($call=NoSQL::instance()->getCall($record[ASD\USER_MOBILE_REQUEST_ID]))!==FALSE)) {                           
                            if (isset($call['completed']) && $call['completed']==1) {
                                $record['from'] = $call['from'] ?? '00000000000000';
                                return MobileValidation::RESULT_ERR_CALL_DONE;
                            }
                        }
                        if ($vt>0) {                            
                            return MobileValidation::RESULT_ERR_SENT_FEW_MINUTES;
                        }
                        else if (isset($record[ASD\USER_MOBILE_SENT_SMS_COUNT]) && $record[ASD\USER_MOBILE_SENT_SMS_COUNT]>0) {
                            return MobileValidation::RESULT_ERR_SENT_FEW_MINUTES;
                        }
                    }
                }

                if ($record[ASD\USER_MOBILE_SENT_SMS_COUNT]>10) {
                    return MobileValidation::RESULT_ERR_QOTA_EXCEEDED;
                }
            }
        }

        return MobileValidation::RESULT_OK;
    }
    
    
    public function verifyStatus(string $requestId) : int {
        if (substr($requestId, 0, 3)=='CLI') {
            $response = $this->getEdigearRequestStatus($requestId);
            
            //$response = $this->getCheckMobiClient()->ValidationStatus(['id'=>$requestId]);            
            if (isset($response['status']) && $response['status']==200 && isset($response['response']) && isset($response['response']['validated'])) {
                if ($response['response']['validated']) {
                    return 1;
                }
            }
        }
        else if (substr($requestId, 0, 2)=='1-') {
            $response = $this->nexmoStatus($requestId);
            if (isset($response['status']) && $response['status']==200 && isset($response['response']) && isset($response['response']['validated'])) {
                if ($response['response']['validated']) {
                    return 1;
                }
            }
        }
        return 0;
    }
    
        
    
    public function sendCallerId($to) {
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::CLI_TYPE, $record);

        if ($status!=MobileValidation::RESULT_OK && $status!=MobileValidation::RESULT_ERR_ALREADY_ACTIVE) {
            return $status;
        }

        $bins = [ASD\USER_UID=>$this->uid,
                ASD\USER_MOBILE_NUMBER=>$num,
                ASD\USER_MOBILE_ACTIVATION_CODE=>0,
                ASD\USER_MOBILE_FLAG=>$this->platform];

        switch ($this->provider) {
            case static::EDIGEAR:
                if ($this->sendEdigearCallerId($to, $bins)) {
                    $res = ($record) ? 
                            NoSQL::instance()->mobileUpdate($this->getUID(), $num, $bins) : 
                            NoSQL::instance()->mobileInsert($bins);
                    return ($res) ? static::RESULT_OK : static::RESULT_ERR_DB_FAILURE;
                }
                break;
            
            case static::CHECK_MOBI:
                if ($this->sendCheckMobiCallerId($to, $bins)) {
                    $res = ($record) ? NoSQL::instance()->mobileUpdate($this->getUID(), $num, $bins) : NoSQL::instance()->mobileInsert($bins);
                    if ($res) {
                        return MobileValidation::RESULT_OK;
                    }
                    return MobileValidation::RESULT_ERR_DB_FAILURE;
                }
                break;
            
            default:
                break;
                        
        }
                        
        return MobileValidation::RESULT_ERR_UNKNOWN;        
    }


    public function requestReverseCLI($to, &$response) : int {
        $num = $this->getE164($to, TRUE);
        $status = $this->checkUserMobileStatus($num, MobileValidation::REVERSE_CLI_TYPE, $record);
       
        if ($status==MobileValidation::RESULT_ERR_SENT_FEW_MINUTES && isset($record[ASD\USER_MOBILE_REQUEST_ID])) {
            $response = $this->getEdigearRequestStatus($record[ASD\USER_MOBILE_REQUEST_ID]);
            return $status;
        }
        
        
        if ($status!=MobileValidation::RESULT_OK && $status!==MobileValidation::RESULT_ERR_CALL_DONE) {
            return $status;
        }
        
        if ($status==MobileValidation::RESULT_ERR_CALL_DONE) {
            $data = [
                'id'=>$record[ASD\USER_MOBILE_REQUEST_ID],
                'type'=>'reverse_cli',
                'cli_prefix'=>substr($record['from'], 0, 5),
                'cli_full'=>$record['from'],
                'pin_hash'=>$record[ASD\USER_MOBILE_PIN_HASH],
                'length'=>strlen($record['from']),
                'called'=>1
            ];
        
            $response = array("status" => 200, "response" => $data);
            return MobileValidation::RESULT_OK;
        }

        switch ($this->provider) {
                
            case static::EDIGEAR:
                return $this->sendEdigearReverseCLI($to, $response);
            
            case static::CHECK_MOBI:
                break;
            default:
                break;
        }

        return MobileValidation::RESULT_ERR_UNKNOWN;        
    }
    
    
    public function sendSMS($to, $text, $reference=null, $unicode=null) : int {
        $num=$this->getE164($to, TRUE);
        $record=[];
        $status=$this->checkUserMobileStatus($num, MobileValidation::SMS_TYPE, $record);
        if ($status!=MobileValidation::RESULT_OK && $status!=MobileValidation::RESULT_ERR_ALREADY_ACTIVE) {
            return $status;
        }
        
        $bins = [ASD\USER_UID=>$this->uid, 
                ASD\USER_MOBILE_NUMBER=>$num, 
                ASD\USER_MOBILE_ACTIVATION_CODE=>$this->pin, 
                ASD\USER_MOBILE_FLAG=>$this->platform];
            
        switch ($this->provider) {
                
            case static::CHECK_MOBI:
                if ($this->sendCheckMobiMessage($to, $text, $bins)) {            
                    $res = ($record) ? NoSQL::instance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::instance()->mobileInsert($bins);
                    if ($res) {
                        NoSQL::instance()->mobileIncrSMS($this->uid, $num);
                        return MobileValidation::RESULT_OK;
                    }
                    return MobileValidation::RESULT_ERR_DB_FAILURE;
                }
                break;
            
            case static::EDIGEAR:
                
                if ($this->sendEdigearVerficationSMS($to, $bins)) {
                    $res = ($record) ? NoSQL::instance()->mobileUpdate($this->uid, $num, $bins) : NoSQL::instance()->mobileInsert($bins);
                    if ($res) {
                        NoSQL::instance()->mobileIncrSMS($this->uid, $num);
                        return MobileValidation::RESULT_OK;
                    }
                    return MobileValidation::RESULT_ERR_DB_FAILURE;                    
                }
                break;
            
        }
                
        return MobileValidation::RESULT_ERR_UNKNOWN;
    }
    
}


trait EdigearTrait {
    abstract protected function getEdigearClient() : Berysoft\Edigear;
    abstract protected function getPlatformName();
    abstract protected function getE164($number);
    
    private function getEdigearPlatform() {
        switch ($this->getPlatformName()) {
            case 'ios':
                return \Berysoft\EGPlatform::IOS;
            case 'android':
                return \Berysoft\EGPlatform::Android;
        }
        return \Berysoft\EGPlatform::Website;
    }
    
    
    public function sendEdigearCallerId($to, &$bins) : bool {               
        $req = Berysoft\Edigear::createInboundCallRequest($this->getEdigearPlatform())
                    ->setPhoneNumber(intval($to));
        
        $response = $this->getEdigearClient()->getInstance()->send($req);
        if ($response['status']==200 && isset($response['data'])) {
            $data = $response['data'];
            
            $bins[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE] = intval($data['allocated_number']);
            $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $data['id'];
            $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::CLI_TYPE;
            return TRUE;
        }
        
        return FALSE;
    }
    
    
    public function sendEdigearReverseCLI($to, &$response) : int {
        $bins = [];
        if (($record = NoSQL::instance()->mobileFetch($this->getUID(), $to))!==FALSE) {
            if (empty($record)) {
                $bins[ASD\USER_UID] = $this->getUID();
                $bins[ASD\USER_MOBILE_NUMBER] = $to;
            }
        }
        $bins[ASD\USER_MOBILE_ACTIVATION_CODE] = 0;
        $bins[ASD\USER_MOBILE_FLAG] = $this->getPlatform();
        $bins[ASD\USER_MOBILE_DATE_REQUESTED] = time();
        $bins[ASD\USER_MOBILE_REQUEST_ID] = "";
        $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::REVERSE_CLI_TYPE;                
        
        $req = Berysoft\Edigear::createOutboundCallRequest($this->getEdigearPlatform())
                    ->setPhoneNumber($this->getE164($to, TRUE));
        $res = $this->getEdigearClient()->getInstance()->send($req);
        
        if ($res['status']==200 && isset($res['data'])) {
            $data = $res['data'];
            $an = substr($data['allocated_number'], 1);
            $response = [
                "status" => $res['status'], 
                "response" => [
                    'id'=>$data['id'],
                    'type'=>'reverse_cli',
                    'cli_prefix'=>substr($an, 0, 5),
                    'cli_full'=>$an,
                    'hint'=>$data['allocated_number'],
                    'length'=>strlen($data['allocated_number'])
            ]];
            
            
            $bins[ASD\USER_MOBILE_ACTIVATION_CODE] = 0;
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $data['id'];
            
            if (isset($bins[ASD\USER_UID])) {
                $ok = NoSQL::instance()->mobileInsert($bins);
            }
            else {
                $ok = NoSQL::instance()->mobileUpdate($this->getUID(), $to, $bins);
            }            
            
            return MobileValidation::RESULT_OK;
        }
        
        return MobileValidation::RESULT_ERR_UNKNOWN;
        
    }

    
    public function setTextMessage(int $to, string $text, string $sender="mourjan") : bool {
        $req = Berysoft\Edigear::createTextRequest($this->getEdigearPlatform())                
                ->setPhoneNumber($to)
                ->setSender($sender)
                ->setMessage($text);
        $res = $this->getEdigearClient()->getInstance()->send($req);
        return $res['status']==200;
    }

    
    public function sendEdigearVerficationSMS($to, &$bins) : bool {        
        $req = Berysoft\Edigear::createSMSRequest($this->getEdigearPlatform())
                    ->setSender("mourjan")
                    ->setPhoneNumber(intval($to));
        
        if (isset($bins[ASD\USER_MOBILE_ACTIVATION_CODE])) {
            $req->setPin(strval($bins[ASD\USER_MOBILE_ACTIVATION_CODE]));
        }
        
        $res = $this->getEdigearClient()->getInstance()->send($req);
        
        $status = $res['status'];

        if ($status==200 && isset($res['data'])) {
            $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $res['data']['id'];
            $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = 0; 
            return TRUE;
        }
        return FALSE;
    }

    
    public function sendEdigearRTPRequest($to, int $uid, int $activated_number, array &$bins) : bool {
        $req = Berysoft\Edigear::createRTPRequest($this->getEdigearPlatform())
                    ->setSender("mourjan")
                    ->setRefrence($activated_number)
                    ->setUUID($uid)
                    ->setPhoneNumber(intval($to));
        
        $res = $this->getEdigearClient()->getInstance()->send($req);
        
        $status = $res['status'];

        if ($status==200 && isset($res['data'])) {
            error_log(json_encode($res['data']));
            $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
            $bins[ASD\USER_MOBILE_REQUEST_ID] = $res['data']['id'];
            $bins[ASD\USER_MOBILE_ACTIVATION_CODE] = $res['data']['code'];
            $bins["to"] = $res['data']['to'];
            return TRUE;
        }
        return FALSE;
    }

    
    public function verifyEdigearPin(string $id, int $pin) : array {
        //$status = 400;
        $response=["number"=>NULL, "validated"=>false, "validation_date"=>NULL, "charged_amount"=>0];
        $req=Berysoft\Edigear::createVerifyRequest($id, $pin);
        $res=$this->getEdigearClient()->getInstance()->send($req);
        
        $status=$res['status'];
        if ($status==200 && isset($res['data'])) {
            $response['number']=$this->getE164( $res['data']['number'] );
            $response['charged_amount']=$res['data']['price'];
            $response['validated']=$res['data']['verified'];
            $response['validation_date']=$res['data']['timestamp'];                                    
        }
                          
        return ['status'=>$status, 'response'=>$response];
    }
    
    
    public function getEdigearRequestStatus(string $id) : array {
        $req = Berysoft\Edigear::createStatusRequest($id);
        $res = Berysoft\Edigear::getInstance()->send($req);

        $status = $res['status'];
        $response = [];
        if ($status==200 && isset($res['data'])) {
            $data = $res['data'];
            $an = substr($data['more']['allocated_number'], 1);
            $response['id'] = $id;
            $response['number'] = $this->getE164( $res['data']['number'] );        
            $response['charged_amount'] = $res['data']['price'];
            $response['validated'] = $res['data']['verified'];
            $response['validation_date'] = $res['data']['timestamp'];        
            $response['type'] = $res['data']['more']['channel'];
            $response['cli_prefix'] =substr($an, 0, 5);
            $response['cli_full']= $an;
            $response['hint'] = $data['more']['allocated_number'];
            $response['length'] = strlen($response['hint']);
        }
        return ['status'=>$status, 'response'=>$response];
    }
}


trait CheckMobiTrait {
    abstract protected function getCheckMobiClient();
    abstract protected function getE164($number);
    abstract protected function getPlatformName();
    abstract protected function getPin();
    
    protected function isCheckMobiOk(array $response) : bool {
        if (isset($response['status'])) {
            if ($response['status']==200) {
                return isset($response['response']) && is_array($response['response']);
            }
            else return ($response['status']==204);                
        }
        return FALSE;
    }
    
    
    public function sendCheckMobiMessage($to, $text, &$bins) : bool {
        $response = $this->getCheckMobiClient()->SendSMS(["to"=>$this->getE164($to), "text"=>$text, "platform"=>$this->getPlatformName(), "notification_callback"=>"https://dv.mourjan.com/api/checkMobi.php"]);    
        if ($this->isCheckMobiOk($response)) {
            if (isset($response['response']['id'])) {
                $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
                $bins[ASD\USER_MOBILE_REQUEST_ID] = $response['response']['id'];
                $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = 0;
                return TRUE;
            }
        }
        return FALSE;
    }


    public function sendCheckMobiCallerId($to, &$bins) : bool {
        $response = $this->getCheckMobiClient()->RequestValidation(["type"=>"cli", "number"=>$this->getE164($to), "platform"=>$this->getPlatformName()]);
        if ($this->isCheckMobiOk($response)) {
            if (isset($response['response']['id'])) {
                $bins[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE] = intval($response['response']['dialing_number']);
                $bins[ASD\USER_MOBILE_DATE_REQUESTED]=time();
                $bins[ASD\USER_MOBILE_REQUEST_ID] = $response['response']['id'];
                $bins[ASD\USER_MOBILE_VALIDATION_TYPE] = MobileValidation::CLI_TYPE;
                return TRUE;
            }
        }
        return FALSE;
    }
    
}



if (php_sapi_name()=='cli') {
    
    //echo MobileValidation::getInstance()->tt(), "\n";
    //NoSQL::getInstance()->tkey();
    //var_dump( MobileValidation::getInstance(MobileValidation::NEXMO)->setUID(2)->setPin(1234)->fastCallText(442039061160, 447520619658) );
    //echo MobileValidation::getInstance()->setUID(2)->setPin(1234)->verifyNexmoCallPin("CON-8403beab-327c-4945-abb2-45e3b4627b08", 4077), "\n";
    
    //MobileValidation::getInstance(MobileValidation::EDIGEAR)->setTextMessage(9613287168, "lab lanfi eiuweyr iuweyriu ewriue");
    //MobileValidation::getInstance(MobileValidation::EDIGEAR)->setTextMessage(966504403618, "6787 is your vouvherek confirmation code"/*, "VOUCHEREK"*/);
}