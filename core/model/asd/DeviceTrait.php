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
    
    
    public function deviceInsert(array $bins) : bool
    {
        if (!isset($bins[USER_DEVICE_UUID]) || empty($bins[USER_DEVICE_UUID]) || !isset($bins[USER_UID]) || $bins[USER_UID]<=0)
        {
            error_log("Could not insert device: " . json_encode($bins));
            return false;
        }
        
        if (!isset($bins[USER_DEVICE_DATE_ADDED]))
        {
            $bins[USER_DEVICE_DATE_ADDED] = time();
            $bins[USER_DEVICE_LAST_VISITED] = time();
            $bins[USER_DEVICE_VISITS_COUNT] = 1;
        }
        $pk = $this->getConnection()->initKey(NS_USER, TS_DEVICE, $bins[USER_DEVICE_UUID]);
        return $this->setBins($pk, $bins);
                
    }
    
    
    public function deviceFetch(string $uuid) : array
    {
        $pk = $this->getConnection()->initKey(NS_USER, TS_DEVICE, $uuid);
        return $this->getBins($pk);
    }
    
    
    public function deviceSetToken(string $uuid, string $token) : bool
    {
        $pk = $this->getConnection()->initKey(NS_USER, TS_DEVICE, $uuid);
        return $this->setBins($pk, [USER_DEVICE_PUSH_TOKEN => $token]);
    }
    
    
    public function deviceSetNotificationStatus(string $uuid, int $status)
    {
        $pk = $this->getConnection()->initKey(NS_USER, TS_DEVICE, $uuid);
        return $this->setBins($pk, [USER_DEVICE_PUSH_ENABLED => $status]);
    }


    
}
