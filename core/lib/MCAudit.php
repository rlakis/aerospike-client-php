<?php

namespace Core\Lib;
use MCUser;

include_once get_cfg_var('mourjan.path').'/core/lib/MCUser.php';

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
    
    public static function instance(\MCUser $user) : Audit {
        $audit = new Audit();
        $audit->user = $user;
        return $audit;
    }
    
    private function user() : \MCUser {
        return $this->user;
    }
        
    private function device() : \MCDevice {
        if ($this->user()->device!=null) {
            return $this->user()->device;
        }
        
        $devices = $this->user()->getDevices();
        if ($devices && !empty($devices)) {
            return $devices[0];
        }
        return NULL;
    }
    
    
    public function event(string $event) : Audit {
        $this->action = $event;
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
    
        
    public function write(boolean $success) : void {
        try {
            $uuid = "N/A";                        
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
            
            error_log(sprintf("%st%st%dt%dt%st%st%s", 
                    date("Y-m-d H:i:s"), 
                     $this->action,
                    $this->user()->getID(), 
                    $this->user()->getMobileNumber(),                     
                    $this->platform,
                    $uuid,
                    $this->data).PHP_EOL, 3, "/var/log/mourjan/audit.log");
        } 
        catch (\Exception $e) {
            error_log(__CLASS__.'.'.__FUNCTION__.' at line ' . __LINE__. ': '.$e->getMessage());
        }
    }
            
}