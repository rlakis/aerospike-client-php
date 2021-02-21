<?php

namespace Core\Model\ASD;

const TS_DEVICE = 'devices';

const USER_DEVICE_ISO_COUNTRY       = 'iso_country';
const USER_DEVICE_VISITS_COUNT      = 'counter';
const USER_DEVICE_CHANGE_TO_UID     = 'cuid';
const USER_DEVICE_APP_SETTINGS      = 'app_prefs';
const USER_DEVICE_BAN_TRANSACTIONS  = 'ban_tran';


trait DeviceTrait {
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, int &$sequence) : int;
    abstract public function getBins(array $pk, array $bins=[]);
    abstract public function setBins(array $pk, array $bins);
    abstract public function getRecord(array $pk, ?array &$record, array $bins=[]) : int;
    abstract public function exists($pk) : int;
    
    
    private function initDeviceKey(string $uuid) {
        return $this->getConnection()->initKey(\Core\Data\NS_USER, TS_DEVICE, $uuid);
    }
    
    
    private function as_key(string $uuid) : array {
        $key = $this->getConnection()->initKey(\Core\Data\NS_USER, TS_DEVICE, $uuid);        
        return $key;
    }
    
    
    public function deviceInsert(array $bins) : bool {
        if (!isset($bins[USER_DEVICE_UUID]) || empty($bins[USER_DEVICE_UUID]) || !isset($bins[USER_UID]) || $bins[USER_UID]<=0) {
            error_log("Could not insert device: " . json_encode($bins));
            return false;
        }
        
        $pk = $this->initDeviceKey($bins[USER_DEVICE_UUID]);
       
        if ($this->exists($pk)) {
            $bins[USER_DEVICE_LAST_VISITED] = time();
            if ($this->getConnection()->increment($pk, USER_DEVICE_VISITS_COUNT, 1)==\Aerospike::OK) {
            }

            if (isset($bins[USER_DEVICE_PUSH_TOKEN]) && !isset($bins[USER_DEVICE_PUSH_ENABLED])) {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
        }
        else {
            $bins[USER_DEVICE_DATE_ADDED] = time();
            $bins[USER_DEVICE_LAST_VISITED] = time();
            $bins[USER_DEVICE_VISITS_COUNT] = 1;
            if (isset($bins[USER_DEVICE_PUSH_TOKEN]) && !isset($bins[USER_DEVICE_PUSH_ENABLED])) {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
        }
                
        return $this->setBins($pk, $bins);                
    }
    
    
    public function deviceUpdate(string $uuid, array $bins) {
        if (empty($uuid) || (isset($bins[USER_UID]) && $bins[USER_UID]<=0)) {
            \Core\Model\NoSQL::Log(['error'=>"Could not update device: {$uuid}", 'bins'=>$bins]);
            return false;
        }
        
        $pk = $this->initDeviceKey($uuid);
        $options = [\Aerospike::OPT_MAX_RETRIES => 1,
                    \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_UPDATE];
        $status = $this->getConnection()->put($pk, $bins, 0, $options);
        if ($status !== \Aerospike::OK) {
            \Core\Model\NoSQL::Log(['key'=>$pk['key'], 'bins'=>$bins, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
            return FALSE;
        }
        
        return TRUE;                
    }


    public function deviceFetch(string $uuid) {
        $pk = $this->initDeviceKey($uuid);
        return $this->getBins($pk);
    }
    
    
    public function getDeviceRecord(string $uuid, &$record) : int {
        $pk = $this->initDeviceKey($uuid);
        return $this->getRecord($pk, $record);
    }
    
    
    public function deviceExists(string $uuid) : bool {
        $pk = $this->initDeviceKey($uuid);
        return ($this->exists($pk)>0);        
    }
    
        
    public function deviceSetToken(string $uuid, string $token) : bool {
        $bins = [USER_DEVICE_PUSH_TOKEN => $token];
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk)) {
            if ($token) {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
            return $this->setBins($pk, $bins);
        }
        return FALSE;
    }
    
    
    public function deviceSetNotificationStatus(string $uuid, int $status) : bool {
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk)) {
            return $this->setBins($pk, [USER_DEVICE_PUSH_ENABLED => $status]);
        }
        return FALSE;
    }

    
    public function deviceSetUID(string $uuid, int $uid, int $oldUID=0) : bool {
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk)) {
            if (($record = $this->getBins($pk, [USER_UID, USER_DEVICE_SYS_NAME]))!==FALSE) {            
                $bins = ['duid'=>$record[USER_UID], 'uid'=>$uid];
                
                if ($record[USER_UID]==$oldUID || $oldUID==0) {
                    return $this->setBins($pk, [USER_UID => $uid]);
                }
            }
        }
        return false;
    }
    
    
    public function getUserDevices(int $uid, bool $any=FALSE) : array {
        $matches = [];
        $where = \Aerospike::predicateEquals(USER_UID, $uid);
        $this->getConnection()->query(\Core\Model\NoSQL::NS_USER, TS_DEVICE, $where,  
                function ($record) use (&$matches, $any) {
                    if ($any==FALSE) {
                        $deleted = $record['bins'][USER_DEVICE_UNINSTALLED] ?? 0;
                        
                        if (!$deleted) {
                            $matches[] = $record['bins'];
                        }
                        else {
                            /*
                            $deviceKey = $this->initDeviceKey($record['bins'][USER_DEVICE_UUID]);
                            if ($this->getConnection()->remove($deviceKey) == \Aerospike::OK)
                            {
                                error_log("Deleted device ".$record['bins'][USER_DEVICE_UUID]);
                            }
                            */
                        }
                    }
                    else {
                        /*
                        $matches[] = $record['bins'];
                        if ($record['bins'][USER_DEVICE_UNINSTALLED] ?? 0)
                        {
                            $deviceKey = $this->initDeviceKey($record['bins'][USER_DEVICE_UUID]);
                            if ($this->getConnection()->remove($deviceKey) == \Aerospike::OK)
                            {
                                error_log("Deleted device ".$record['bins'][USER_DEVICE_UUID]);
                            }
                        }*/
                    }
                });
        return $matches;
    }

    
}
