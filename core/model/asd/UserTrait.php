<?php

namespace Core\Model\ASD;

const NS_USER               = 'users';
const TS_USER               = 'profiles';

const SET_RECORD_ID         = 'id';
const USER_UID              = 'uid';

const USER_PROFILE_ID       = 'id';
const USER_PROVIDER_ID      = 'provider_id';
const USER_PROVIDER_EMAIL   = 'email';
const USER_PROVIDER         = 'provider';
const USER_FULL_NAME        = 'full_name';
const USER_DISPLAY_NAME     = 'display_name';
const USER_PROFILE_URL      = 'profile_url';
const USER_DATE_ADDED       = 'date_added';
const USER_LAST_VISITED     = 'last_visited';
const USER_LEVEL            = 'level';
const USER_NAME             = 'name';
const USER_EMAIL            = 'user_email';
const USER_PASSWORD         = 'password';
const USER_RANK             = 'rank';
const USER_PRIOR_VISITED    = 'prior_visited';
const USER_PUBLISHER_STATUS = 'pblshr_status';
const USER_LAST_AD_RENEWED  = 'last_renewed';
const USER_DEPENDANTS       = 'dependants';

const USER_OPTIONS          = 'options';
const USER_OPTIONS_E                = 'e';
const USER_OPTIONS_LANG             = 'lang';
const USER_OPTIONS_BMAIL            = 'bmail';
const USER_OPTIONS_MMAIL_KEY        = 'bmailKey';
const USER_OPTIONS_SUBSCRIPTIONS    = 'subscriptions';
const USER_OPTIONS_CALLING_TIME     = 'calling_time';
const USER_OPTIONS_CONTACT_INFO     = 'contact_info';
const USER_OPTIONS_USER_AGENT       = 'user_agent';
const USER_OPTIONS_CTS              = 'cts';
const USER_OPTIONS_SUSPEND          = 'suspend';

const USER_MOBILE           = 'mobile';
const USER_MOBILE_NUMBER            = 'number';
const USER_MOBILE_ACTIVATION_CODE   = 'code';
const USER_MOBILE_DATE_REQUESTED    = 'date_requested';
const USER_MOBILE_DATE_ACTIVATED    = 'date_activated';
const USER_MOBILE_CODE_DELIVERED    = 'delivered';
const USER_MOBILE_SENT_SMS_COUNT    = 'sms_count';
const USER_MOBILE_FLAG              = 'flag';
const USER_MOBILE_SECRET            = 'secret';

const USER_DEVICES          = 'devices';
const USER_DEVICE_UUID              = 'uuid';
const USER_DEVICE_MODEL             = 'model';
const USER_DEVICE_NAME              = 'name';
const USER_DEVICE_SYS_NAME          = 'sys_name';
const USER_DEVICE_SYS_VERSION       = 'sys_version';
const USER_DEVICE_LAST_VISITED      = 'last_visited';
const USER_DEVICE_PUSH_TOKEN        = 'token';
const USER_DEVICE_PUSH_ENABLED      = 'push_enabled';
const USER_DEVICE_CARRIER_COUNTRY   = 'carrier_country'; // iso country code
const USER_DEVICE_APP_VERSION       = 'app_version';
const USER_DEVICE_APP_PREFERENCES   = 'app_preferences';
const USER_DEVICE_UNINSTALLED       = 'removed';
const USER_DEVICE_DATE_ADDED        = 'date_added';
const USER_DEVICE_PURCHASE_ENABLED  = 'purchase_enabled';

const USER_XMPP_CREATED     = 'xmpp';
const USER_JWT              = 'jwt';
const USER_JWT_SECRET               = 'secret';
const USER_JWT_TOKEN                = 'token';




trait UserTrait
{

    abstract public function getConnection();
    abstract public function genId(string $generator, &$sequence);
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;

    
    public function fetchUser(int $uid) : array
    {
        $record = [];
        if ($this->getConnection()->get($this->initKey($uid), $record) != \Aerospike::OK)
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return [];
        }
        
        //error_log(var_export($record['bins'], TRUE));
        
        if (isset($record['bins'][USER_DEVICES]) && !empty($record['bins'][USER_DEVICES]))
        {
            $record['bins'][USER_DEVICES]=[];
        }
        
        
        return $record['bins'];
    }

    
    
    public function fetchUserByUUID(string $uuid) : array
    {
        $record = [];
        $device = \Core\Model\NoSQL::getInstance()->deviceFetch($uuid);
        if (isset($device[USER_UID]) && $device[USER_UID])
        {
            $time = 0;
            do
            {
                $record = $this->fetchUser($device[USER_UID]);                
                if (!isset($record[USER_PROFILE_ID]))
                {
                    usleep(500);
                    $time+=500;
                }
            }
            while (!isset($record[USER_PROFILE_ID]) && $time<2000000);
            
            $record['logged_by_device'] = $device;
                       
            return $record;
        }

        return $record;
    }

        
    public function fetchUserByProviderId(string $identifier, string $provider='mourjan') : array
    {
        $bins = [];
        $where = \Aerospike::predicateEquals(USER_PROVIDER_ID, $identifier);
        $this->getConnection()->query(NS_USER, TS_USER, $where,  
                function ($record) use (&$bins, &$uid, $provider) 
                {
                    if ($record['bins'][USER_PROVIDER]==$provider)
                    {
                        $bins[] = $record['bins'];
                    }
                });
        return $bins;
    }
    
    
    public function userUpdate(array $bins, int $uid=0, bool $as_visit=FALSE)
    {
        if ($uid==0)
        {
            $this->genId('profile_id', $uid);
        }
        
        if ($uid>0)
        {
            $pk = $this->initKey($uid);
            
            if (!$this->exists($pk))
            {            
                $now = time();
                $record = [
                    USER_PROFILE_ID => $uid,
                    USER_PROVIDER_ID => '',
                    USER_PROVIDER_EMAIL => '',
                    USER_PROVIDER => '',
                    USER_FULL_NAME => '',
                    USER_DISPLAY_NAME => '',
                    USER_PROFILE_URL => '',
                    USER_DATE_ADDED => $now,
                    USER_LAST_VISITED => $now,
                    USER_LEVEL => 0,
                    USER_NAME => '',
                    USER_EMAIL => '',
                    USER_PASSWORD => '',
                    USER_RANK => 1,
                    USER_PRIOR_VISITED => $now,
                    USER_PUBLISHER_STATUS => 0,
                    USER_LAST_AD_RENEWED => 0,
                    USER_XMPP_CREATED => 0,
                    USER_DEPENDANTS => [],
                    USER_OPTIONS => [],
                    USER_MOBILE => [],
                    USER_DEVICES => []
                ];
                
                foreach ($bins as $k => $v)
                {
                    $record[$k] = $v;
                }
                $bins = $record;
            } 
            else 
            {
                if ($as_visit && isset($bins[USER_LAST_VISITED]))
                {
                    $this->setVisitUnixtime($uid, $pk);
                }
            }

            if ($this->setBins($pk, $bins))
            {
                return $this->getBins($pk);
            }
            
        }
        else
        {
            error_log("Invalid UID - Counld not update user record!!");
        }
        
        return FALSE;
    }
    
    
    public function updateUser($info, string $provider)
    {
        $provider = strtolower($provider);
        $identifier = $info->identifier;
        $email = is_null($info->emailVerified) ? (is_null($info->email ? '' : $info->email)) : $info->emailVerified;
        if (strpos($email, '@')===false) 
        {
            $email='';
        }
        $fullName = trim(($info->firstName ? $info->firstName : '').' '.($info->lastName ? $info->lastName : ''));
        $dispName = (!is_null($info->displayName) ? $info->displayName : '');
        $infoStr = (!is_null($info->profileURL) ? $info->profileURL : '');
        $uid = 0;
        $where = \Aerospike::predicateEquals(USER_PROVIDER_ID, $identifier);
        $this->getConnection()->query(NS_USER, TS_USER, $where,  
                function ($record) use (&$uid, $provider) 
                {
                    if ($record['bins'][USER_PROVIDER]==$provider)
                    {
                        $uid = $record['bins'][USER_PROFILE_ID];                        
                    }
                }, [USER_PROFILE_ID, USER_PROVIDER]);
                
        $bins = [USER_PROVIDER_EMAIL=>$email, USER_FULL_NAME=>$fullName, USER_DISPLAY_NAME=>$dispName, USER_PROFILE_URL=>$infoStr];        
        if ($uid)
        {
            $pk = $this->initKey($uid);            
            $this->setBins($pk, $bins);
            $this->setVisitUnixtime($uid, $pk);            
        }
        else
        {
            $bins[USER_PROVIDER_ID] = $identifier;
            $bins[USER_PROVIDER] = $provider;
            $this->userUpdate($bins);
        }
                
    }
    
    
    private function initKey(int $uid) 
    {
        return $this->getConnection()->initKey(NS_USER, TS_USER, $uid);
    }    
    
    
    public function setVisitUnixtime(int $uid, array $pk=[])
    {
        if (empty($pk))
        {
            $pk = $this->initKey($uid);
        }
        
        if (($record = $this->getBins($pk, [USER_LAST_VISITED]))!==FALSE)        
        {        
            $this->setBins($pk, [USER_LAST_VISITED=>time(), USER_PRIOR_VISITED=>$record[USER_LAST_VISITED]]);
        }
    }
    
    
    public function setUserBin(int $uid, string $bin, $value) : bool
    {
        if ($uid<=0)
        {
            error_log("Could not set user bin for zero uid");
            return FALSE;
        }
        $pk = $this->initKey($uid);
        return $this->setBins($pk, [$bin => $value]);
    }
    
    
    public function setOptions(int $uid, array $opts) : bool
    {
        $pk = $this->initKey($uid);
        return $this->setBins($pk, [USER_OPTIONS => $password]);
    }
    
    
    public function setPassword(int $uid, string $password) : bool
    {
        $pk = $this->initKey($uid);
        return $this->setBins($pk, [USER_PASSWORD => $password]);
    }
    
    
    public function setUserLevel(int $uid, int $level) : bool
    {
        $pk = $this->initKey($uid);
        return $this->setBins($pk, [USER_LEVEL => $level]);
    }
    

    public function setUserPublisherStatus(int $uid, int $type) : bool
    {
        $pk = $this->initKey($uid);
        return $this->setBins($pk, [USER_PUBLISHER_STATUS => $type]);
    }

    
    public function setEnabledXMPP(int $uid)
    {
        $pk = $this->initKey($uid);
        $this->setBins($pk, [USER_XMPP_CREATED => 1]);
    }
    
    
    public function setJsonWebToken(int $uid, array $jwt) : bool
    {
        return $this->setBins($this->initKey($uid), [USER_JWT => $jwt]);
    }
    
    
    public function unsetJsonWebTocken(int $uid)
    {
        $this->getConnection()->removeBin($this->initKey($uid), [USER_JWT]);
    }
    
    
    public function updateLinkedMobile(int $uid, int $number) : bool
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, [USER_MOBILE]);
        if ($record && isset($record[USER_MOBILE][USER_MOBILE_NUMBER]) && $record[USER_MOBILE][USER_MOBILE_NUMBER]==$number)
        {
            $record[USER_MOBILE][USER_MOBILE_DATE_ACTIVATED] = time();
            return $this->setBins($pk, $record);            
        }
        return FALSE;
    }
  
    
    
    public function getVerifiedMobile(int $uid)
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, [USER_MOBILE]);
        if ($record && isset($record[USER_MOBILE][USER_MOBILE_NUMBER]) && isset($record[USER_MOBILE][USER_MOBILE_DATE_ACTIVATED]))
        {
            $year_in_seconds = 31556926;
            if ($record[USER_MOBILE][USER_MOBILE_DATE_ACTIVATED]+$year_in_seconds>time()) 
            {
                return $record[USER_MOBILE][USER_MOBILE_NUMBER];
            }
        }
        return FALSE;        
    }
    
    
    public function getOptions(int $uid) : array
    {
        $pk = $this->initKey($uid);
        if (($record=$this->getBins($pk, [USER_OPTIONS]))!==FALSE)
        {
            return $record[USER_OPTIONS];
        }
        error_log("Could nor get user options for UID {$uid}");
        return [];
    }

    
    public function getRank(int $uid) : int
    {
        $pk = $this->initKey($uid);
        if (($record=$this->getBins($pk, [USER_RANK]))!==FALSE)
        {
            return $record[USER_RANK];
        }
        return 1;
    }
    
    
    public function getUserPublisherStatus(int $uid) 
    {
        $pk = $this->initKey($uid);
        if (($record=$this->getBins($pk, [USER_PUBLISHER_STATUS]))!==FALSE)
        {
            return $record[USER_PUBLISHER_STATUS];
        }
        return FALSE;
    }
}