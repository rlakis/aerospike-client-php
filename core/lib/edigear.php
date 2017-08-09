<?php

namespace Berysoft;

class Edigear
{
    const GEAR_URL      = "https://h9.mourjan.com";
    const GEAR_VERSION  = "v1";
    const GEAR_AGENT    = "edigear api client";
    const VERSION       = "1.0";
      
    private $userAgent  = null;
    
    private $secretKey;
    
    private static $instance;
    
    
    private function __construct()
    {                
    }
    
    
    public static function getInstance() : Edigear
    {
        if (Edigear::$instance==null)
        {
            Edigear::$instance = new Edigear();            
        }
        return Edigear::$instance;
    }
               
    
    public function setSecretKey(string $secret)
    {
        $this->secretKey = $secret;
        return $this;
    }
    
        
    
    public function send(EdigearRequest $request)
    {
        $result = ['status'=>0, 'data'=>[]];
        if (!extension_loaded('curl')) 
        {
            $result['data']=['error'=>'cURL library is not loaded'];
            return $result;
        }
        
        if ((!isset($this->secretKey)) || (!$this->secretKey))
        {
            $result['data']=['error'=>'no secret key is specified'];
            return $result;
        }
        
        if (!$request->isValid())
        {
            $result['data']=['error'=>'Invalid request payload. '.$request->getLastError()];            
            return $result;
        }
        
        
        if ($this->userAgent==NULL)
        {
            $this->userAgent = 'Edigear-PHP/' . self::VERSION . ' (+https://github.com/berysoft/edigear-php)';
            $this->userAgent .= ' PHP/' . PHP_VERSION;
            $curl_version = curl_version();
            $this->userAgent .= ' curl/' . $curl_version['version'];
        }
        
        $options = [
            CURLOPT_URL => $request->getURL(),
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_SSL_VERIFYPEER => TRUE];
        
        $ch = curl_init();
        $headers = array('Authorization: '.$this->secretKey);
              
        try
        {
            curl_setopt($ch, CURLOPT_URL, $request->getUrl());
            
            switch ($request->getMethod()) 
            {
                case EGMethod::POST:
                    curl_setopt($ch, CURLOPT_POST, true);
                    $jsonPayload = $request->getPayload();
                    //error_log($jsonPayload);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                    array_push($headers, "Content-Type: application/json");
                    array_push($headers, 'Content-Length: '.strlen($jsonPayload));
                    break;

                default:
                    break;
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
            curl_setopt_array($ch, $options);
            
            $response = \curl_exec($ch);

            //error_log($response);
            
            //Retrieve Response Status
            $result['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            $is_json = is_string($response) && is_array(json_decode($response, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
            if ($is_json)
            {                
                $result['data'] = json_decode($response, TRUE);
            }
            
        } 
        catch (Exception $ex) 
        {
            throw new EdigearError ($ex->getMessage());
        }
        finally 
        {
            if (is_resource($ch)) 
            {
                curl_close($ch);
            }
        }
        
        return $result;
    }
        
}


abstract class EGChannel
{
    const Undefined = 0;
    const Message   = 1;
    const Inbound   = 2;
    const Outbound  = 3;    
    
    public static function validate(array $data)
    {
        if (!isset($data['channel']))
        {
            return 'Channel is not set [EdigearRequest->setChannel( EGChannel::Message | EGChannel::Inbound | EGChannel::Outbound )]';
        }
        
        if ($data['channel']!==EGChannel::Message && $data['channel']!==EGChannel::Inbound && $data['channel']!==EGChannel::Outbound)
        {
            return 'Invalid channel value [EdigearRequest->setChannel( EGChannel::Message | EGChannel::Inbound | EGChannel::Outbound )]';
        }
        
        return TRUE;
    }
}


abstract class EGAction
{
    const Request   = 1;
    const Verify    = 2;
    const Status    = 3;
}


abstract class EGPlatform
{
    const Undefined = 0;
    const IOS       = 1;
    const Android   = 2;
    const Website   = 3;
    const Desktop   = 4;
}


abstract class EGMethod
{
    const GET       = "GET";
    const POST      = "POST";
}


       

class EGResponse
{
    private $result = [];
    
    public function getAsJson()
    {
        return json_decode($this->result);
    }
    
    public function getAsArray()
    {
        return $this->result;
    }
}


class EdigearRequest 
{
    use ShortMessageService;
    
    protected $action;
    protected $method;    
    protected $payload;
    
    private $error = null;
    
    private function __construct() 
    {
        $this->method = EGMethod::POST;
        $this->payload = ['number'=>0, 'id'=>'', 'pin'=>'', 'channel'=>EGChannel::Undefined, 'platform'=>EGPlatform::Undefined];
    }
    
    
    public static function Create() : EdigearRequest
    {
        $_instance = new EdigearRequest();
        return $_instance;
    }

    
    public function getURL() : string
    {
        $url = Edigear::GEAR_URL . '/' . Edigear::GEAR_VERSION;
        switch ($this->action) 
        {
            case EGAction::Request:
                $url.='/validation/request';
                break;
            
            case EGAction::Verify:
                $url.='/validation/verify';
                break;
            
            case EGAction::Status:
                $url.="/validation/status/{$this->payload['id']}";               
                break;

            default:
                throw new EdigearError("no action key is specified (request, verify, status)");
        }
               
        return $url;
    }
    
    
    public function setChannel(int $channel) : EdigearRequest
    {
        $this->payload['channel'] = $channel;        
        return $this;
    }
    
    
    public function setAction(int $action) : EdigearRequest
    {
        $this->action = $action;
        
        if ($this->action === EGAction::Request)
        {
            $this->setMethod(EGMethod::POST);
        }
        else if ($this->action=== EGAction::Status)
        {
            $this->setMethod(EGMethod::GET);
        }
        
        return $this;
    }
    
    
    public function setPlatform(int $platform) : EdigearRequest
    {
        $this->payload['platform'] = $platform;
        return $this;
    }

    
    public function setSender(string $sender) : EdigearRequest
    {
        if ($sender)
        {
            $this->payload['sender'] = $sender;
        }
        else
        {
            unset($this->payload['sender']);
        }
        return $this;
    }
    
    protected function setMethod(string $method) : EdigearRequest
    {
        $this->method = $method;
        return $this;
    }
    
    
    public function getMethod() : string
    {
        return $this->method;
    }
   
    
    public function setPhoneNumber(int $phoneNumber) : EdigearRequest
    {
        $this->payload['number'] = $phoneNumber;
        return $this;                
    }
       
    
    public function setId(string $id) : EdigearRequest
    {
        $this->payload['id'] = $id;
        return $this;
    }
    
    public function setPin(string $pin) : EdigearRequest
    {
        $this->payload['pin'] = $pin;
        return $this;
    }
    
    
    public function getPayload()
    {
        switch ($this->action) 
        {
            case EGAction::Request:
                if ($this->payload['channel']==EGChannel::Message && isset($this->payload['sender']) && !empty($this->payload['sender']))
                {
                    $payl = [
                        'number'=>$this->payload['number'], 
                        'channel'=>$this->payload['channel'], 
                        'platform'=>$this->payload['platform'], 
                        'sender'=> $this->payload['sender']];
                    if (isset($this->payload['pin']) && $this->payload['pin'])
                    {
                        $payl['code']=$this->payload['pin'];
                    }
                    return json_encode($payl);
                }
                return json_encode([
                        'number'=>$this->payload['number'], 
                        'channel'=>$this->payload['channel'], 
                        'platform'=>$this->payload['platform']]);                

            case EGAction::Verify:
                return json_encode(['id'=>$this->payload['id'], 'pin'=>$this->payload['pin']]);                

            default:
                break;
        }
        return json_encode($this->payload);
    }
    
    
    public function isValid() : bool
    {
        switch ($this->action) 
        {
            case EGAction::Request:
                if ($this->payload['number']<99999)
                {
                    return FALSE;
                }
                
                if (($error = EGChannel::validate($this->payload))!==TRUE)
                {
                    echo $error, "[",__LINE__, "]\n";
                    return FALSE;
                }
                break;

            case EGAction::Verify:
//                if (($error = EGChannel::validate($this->payload))!==TRUE)
//                {
//                    echo $error, "\n";
//                    return FALSE;
//                }
//                
                if ($this->payload['id']==null || empty($this->payload['id']))
                {
                    $this->error = "Invalid request id!";
                    return FALSE;
                }
                
               
                if ($this->payload['channel']!==EGChannel::Inbound && 
                        (!isset($this->payload['pin']) ||
                        $this->payload['pin']==NULL || 
                        $this->payload['pin']<"0000" || 
                        $this->payload['pin']>"9999"))
                {
                    $this->error = "Invalid pin code!";
                    return FALSE;
                }
                break;

                
            case EGAction::Status:
                if (!isset($this->payload['id']) || $this->payload['id']==null || empty($this->payload['id']))
                {
                    return FALSE;
                }
                break;
            
            default:
                return FALSE;
        }
        
        return TRUE;
    }
    
    
    public function getLastError() : string
    {
        return $this->error ?? '';
    }
        
}


trait ShortMessageService 
{
    abstract function setId(string $id);
    abstract protected function isValid() : bool;
    
    public function SMSValidation()
    {
        $this->channel = EdiChannel::Message;
        if ($this->compiled())
        {
            $prepare = 
                    [
                        'number' => $this->number, 
                        'type' => $this->channel, 
                        'platform' => $this->platform
                    ];
            echo json_encode($prepare), "\n";
        }        
    }
}


class EdigearError extends \Exception
{
}


if (PHP_SAPI=='cli')
{
    $channel = "cli";
    $test = $argv[1] ?? "s";
    
    switch ($channel) 
    {
        case "sms":
            switch ($test) 
            {
                case "s":
                    $request = EdigearRequest::Create()->setAction(EGAction::Status)->setId("SMS-b35893ee-204a-4687-9c4b-d487c2cf443f");
                    break;
                case "v":
                    $request = EdigearRequest::Create()->setAction(EGAction::Verify)->setId("SMS-b35893ee-204a-4687-9c4b-d487c2cf443f")->setPin("2022");
                    break;
                case "r":
                    $request = EdigearRequest::Create()->
                        setAction(EGAction::Request)->
                        setChannel(EGChannel::Message)->
                        setPlatform(EGPlatform::Website)->
                        setSender("mourjan")->
                        setPhoneNumber(353830399895);
                    break;
            }
            break;

        case "cli":
            switch ($test) 
            {
                case "s":               
                    $request = EdigearRequest::Create()->setAction(EGAction::Status)->setId("CLI-6d64637a-0414-416f-a068-228a26fb9d3c");
                    break;

                case "v":
                    $request = EdigearRequest::Create()->setAction(EGAction::Verify)->setId("CLI-6d64637a-0414-416f-a068-228a26fb9d3c")->setPin(1790);                
                    break;

                case "r":
                    $request = EdigearRequest::Create()->
                        setAction(EGAction::Request)->
                        setChannel(EGChannel::Inbound)->
                        setPlatform(EGPlatform::IOS)->
                        setPhoneNumber(353871985414);
                    break;
            }

            break;

        case "rvc":
            switch ($test) 
            {
                case "s":
                    $request = EdigearRequest::Create()->setAction(EGAction::Status)->setId("RVC-5f119886-6599-4a96-80bd-623a86a99cd9");
                    break;
                case "v":
                    $request = EdigearRequest::Create()->
                        setAction(EGAction::Verify)->
                        setId("RVC-5f119886-6599-4a96-80bd-623a86a99cd9")
                        ->setPin(4077); 
                    break;
                case "r":
                    $request = EdigearRequest::Create()->
                        setAction(EGAction::Request)->
                        setChannel(EGChannel::Outbound)->
                        setPlatform(EGPlatform::Website)->
                        setPhoneNumber(353830399895);
                    break;
            }
            break;
    }        

    $response = Edigear::getInstance()->setSecretKey("D38D5D58-572B-49EC-BAB5-63B6081A55E6")->send($request);
    var_dump($response);
}