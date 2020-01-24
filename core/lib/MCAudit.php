<?php

namespace Core\Lib;
use Core\Lib\MCUser;

\Config::instance()->incLibFile('MCUser')->incLibFile('IPQuality');

class Event {
    const PURCHASE  = 'purchase';
    const SIGNIN    = 'sign in';
    const SIGNUP    = 'sign up';
    const NEWAD     = 'new ad';
    const EDITAD    = 'edit ad';
    const RENEWAD   = 'renew ad';
}

class Platform {
    const ANDROID   = 'android';
    const IOS       = 'ios';
    const DESKTOP   = 'desktop';    
    const MOBILE    = 'mobile';    
}


class Audit {
    private $action;
    private $platform;
    private $user;
    private $data;
    private $message;
    private $object;
    
    private function __construct() {
        $path_only = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($path_only==='/api/app.php' || $path_only==='/api/') {
            $this->object = \IPQuality::fetchJson(TRUE);
            if (preg_match('/^Mourjan(\/.*?\s*)CFNetwork(\/.*?\s*)Darwin(\/.*?\s*)([^)(;]+)/im', $this->object['agent'])) {
                $this->platform = Platform::IOS;
            }
            else {
                $this->platform = Platform::ANDROID;
            }            
        }
        else {
            $this->object = \IPQuality::fetchJson(FALSE);
        }
    }

    
    public static function instance() : Audit {
        $audit = new Audit();
        $audit->message = '-';
        return $audit;
    }
    
    
    public static function signIn() : Audit {
        $audit = new Audit();
        $audit->action = Event::SIGNIN;
        $audit->message = '-';
        $audit->object['event'] = Event::SIGNIN;
        return $audit;
    }
    
    
    public static function newAd() : Audit {
        $audit = new Audit();
        $audit->action = Event::NEWAD;
        $audit->message = '-';
        $audit->object['event'] = Event::NEWAD;
        return $audit;
    }    
    
    
    public static function editAd() : Audit {
        $audit = new Audit();
        $audit->action = Event::EDITAD;
        $audit->message = '-';
        $audit->object['event'] = Event::EDITAD;
        return $audit;
    }    
    
   
    private function device() : \MCDevice {
        if ($this->user->device!=null) {
            return $this->user->device;
        }
        
        $devices = $this->user->getDevices();
        if ($devices && !empty($devices)) {
            return $devices[0];
        }
        return NULL;
    }
    
    
    public function user(MCUser $user) {
        $this->user = $user;
        return $this;
    }
    
    
    public function event(string $event) : Audit {
        $this->action = $event;
        return $this;
    }
    
    
    public function message(string $message) : Audit {
        $this->message = $message;
        return $this;
    }
    
    
    public function platform(string $platform) : Audit {
        $this->platform = $platform;
        return $this;
    }
    
    
    public function action(string $event, string $platform) : Audit {
        $this->action = $event;
        $this->platform = $platform;
        return $this;
    }
    
    
    public function log($obj) : Audit {
        if (is_string($obj)) {
            $this->data = $obj;            
        }
        else if (is_array($obj)) {
            $this->data = json_encode($obj);
        }
        else if ($obj instanceof \stdClass) {
            $this->data = json_encode($obj);
        }
        else {
            $this->data = 'data error';
        }
        return $this;
    }
    
    
    public function add(string $name, $value) : Audit {
        $this->object['data'][$name]=$value;
        return $this;
    }
    
    
    public function ok() : Audit {
        $this->object['data']['ok']=1;
        return $this;
    }
    
    
    public function fail() : Audit {
        $this->object['data']['ok']=0;
        return $this;
    }
    
    
    public function end() : void {
        if ($this->user) {
            $this->object['data']['uid']=$this->user->getID();
            $this->object['data']['mobile']=$this->user->getMobileNumber();
            //$this->object['data']['']
            if ($this->platform===Platform::ANDROID || $this->platform===Platform::IOS) {
                try {
                    $device = $this->device();
                    if ($device) {                        
                        $this->object['data']['app_version'] = $device->getAppVersion();
                        $this->object['data']['uuid'] = $device->getUUID();
                    }
                }
                catch (\Exception $ex) {                    
                    error_log(__CLASS__.'.'.__FUNCTION__.': '.$ex->getMessage());
                }   
            }            
        }
        
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        array_shift($trace);
        if (!empty($trace)) {
            $this->object['data']['trace']=['class'=>$trace[0]['class'], 'function'=>$trace[0]['function'], 'line'=>$trace[0]['line']];
        }
             
        if ($this->platform!=Platform::ANDROID && $this->platform!=Platform::IOS) {
            $this->object['sess_id'] = session_id();
        }
        
        error_log(\json_encode($this->object).PHP_EOL.PHP_EOL, 3, '/var/log/mourjan/audit.log');

    }
    
    
    public function write(bool $success) : void {
        try {
            $ip = \IPQuality::getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT']??'-';
            if (preg_match('/^Mourjan(\/.*?\s*)CFNetwork(\/.*?\s*)Darwin(\/.*?\s*)([^)(;]+)/im', $user_agent)) {
                $this->platform = Platform::IOS;
            }
            $uuid = "-";
            $uid = 0;
            $mobile = 0;
            if ($this->user) {
                $uid = $this->user->getID();
                $mobile = $this->user->getMobileNumber();
                if ($this->platform===Platform::ANDROID || $this->platform===Platform::IOS) {
                    try {
                        $device = $this->device();
                        if ($device) {
                            $device->getAppVersion();
                            $uuid = $device->getUUID();
                        }
                    }
                    catch (\Exception $ex) {                    
                        error_log(__CLASS__.'.'.__FUNCTION__.': '.$ex->getMessage());
                    }   
                }
            }                       
            
            if (!$this->data) {
                $this->data = \IPQuality::fetch($this->platform===Platform::ANDROID || $this->platform===Platform::IOS);
            }
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            array_shift($trace);
            //error_log(var_export($trace, TRUE));
            $class='-';
            $func='-';
            $line=0;
            if (!empty($trace)) {
                $class = $trace[0]['class'];
                $func = $trace[0]['function'];
                $line = $trace[0]['line'];
            }
                                    
            error_log(sprintf("%s\t%s\t%d\t%d\t%s\t%s\t%s\t%s\t%s\t%d\t%s\t%s\t%s\t%s", 
                    date("Y-m-d H:i:s"), 
                    $this->action,
                    $uid, 
                    $mobile,                     
                    $this->platform,
                    $ip,
                    session_id(),
                    $class,
                    $func,
                    $line,
                    $uuid,
                    $this->message,
                    $_SERVER['HTTP_USER_AGENT']??'-',
                    $this->data).PHP_EOL.PHP_EOL, 3, "/var/log/mourjan/audit.log");
        } 
        catch (\Exception $e) {
            error_log(__CLASS__.'.'.__FUNCTION__.' at line ' . __LINE__. ': '.$e->getMessage());
        }
    }
            
}