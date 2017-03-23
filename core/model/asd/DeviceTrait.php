<?php

namespace Core\Model\ASD;

const TS_DEVICE = 'devices';

const USER_DEVICE_ISO_COUNTRY       = 'iso_country';
const USER_DEVICE_VISITS_COUNT      = 'counter';
const USER_DEVICE_CHANGE_TO_UID     = 'cuid';
const USER_DEVICE_APP_SETTINGS      = 'app_prefs';
const USER_DEVICE_BAN_TRANSACTIONS  = 'ban_tran';


trait DeviceTrait
{
    abstract public function getConnection();
    abstract public function genId(string $generator, &$sequence);
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;
    
    
    private function initDeviceKey(string $uuid)
    {
        return $this->getConnection()->initKey(NS_USER, TS_DEVICE, $uuid);
    }
    
    
    public function deviceInsert(array $bins) : bool
    {
        if (!isset($bins[USER_DEVICE_UUID]) || empty($bins[USER_DEVICE_UUID]) || !isset($bins[USER_UID]) || $bins[USER_UID]<=0)
        {
            error_log("Could not insert device: " . json_encode($bins));
            return false;
        }
        
        $pk = $this->initDeviceKey($bins[USER_DEVICE_UUID]);
       
        if ($this->exists($pk))
        {
            $bins[USER_DEVICE_LAST_VISITED] = time();
            if ($this->getConnection()->increment($pk, USER_DEVICE_VISITS_COUNT, 1)==\Aerospike::OK)
            {
                error_log(sprintf("%s", json_encode(['mt'=> microtime(TRUE), 'pk'=>$pk, 'inc'=>[USER_DEVICE_VISITS_COUNT, 1]])).PHP_EOL, 3, "/var/log/mourjan/aerospike.set");
            }
            if (isset($bins[USER_DEVICE_PUSH_TOKEN]) && !isset($bins[USER_DEVICE_PUSH_ENABLED]))
            {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
        }
        else
        {
            $bins[USER_DEVICE_DATE_ADDED] = time();
            $bins[USER_DEVICE_LAST_VISITED] = time();
            $bins[USER_DEVICE_VISITS_COUNT] = 1;
            if (isset($bins[USER_DEVICE_PUSH_TOKEN]) && !isset($bins[USER_DEVICE_PUSH_ENABLED]))
            {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
        }
                
        return $this->setBins($pk, $bins);                
    }
    


    public function deviceFetch(string $uuid) : array
    {
        $pk = $this->initDeviceKey($uuid);
        return $this->getBins($pk);
    }
    
    
    public function deviceSetToken(string $uuid, string $token) : bool
    {
        $bins = [USER_DEVICE_PUSH_TOKEN => $token];
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk))
        {
            if ($token)
            {
                $bins[USER_DEVICE_PUSH_ENABLED] = 1;
            }
            return $this->setBins($pk, $bins);
        }
        return FALSE;
    }
    
    
    public function deviceSetNotificationStatus(string $uuid, int $status) : bool
    {
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk))
        {
            return $this->setBins($pk, [USER_DEVICE_PUSH_ENABLED => $status]);
        }
        return FALSE;
    }

    
    public function deviceSetUID(string $uuid, int $uid) : bool
    {
        $pk = $this->initDeviceKey($uuid);
        if ($this->exists($pk))
        {
            return $this->setBins($pk, [USER_UID => $uid]);
        }
        return false;
    }
    
    
    public function getUserDevices(int $uid, bool $any=FALSE) : array
    {
        $matches = [];
        $where = \Aerospike::predicateEquals(USER_UID, $uid);
        $this->getConnection()->query(NS_USER, TS_DEVICE, $where,  
                function ($record) use (&$matches, $any) 
                {
                    if ($any==FALSE)
                    {
                        if (!(isset($record['bins'][USER_DEVICE_UNINSTALLED]) && $record['bins'][USER_DEVICE_UNINSTALLED]))
                        {
                            $matches[] = $record['bins'];
                        }
                    }
                    else
                    {
                        $matches[] = $record['bins'];
                    }
                });
        return $matches;
    }

    
}