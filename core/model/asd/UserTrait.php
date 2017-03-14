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
    abstract public function setBins($pk, array $bins);

    public function fetchUser(int $uid) : array
    {
        $record = [];
        if ($this->getConnection()->get($this->initKey($uid), $record) != \Aerospike::OK)
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return [];
        }
        return $record['bins'];
    }


    public function createUser(array $bins)
    {
        $this->getId('profile_id', $uid);
        
        if ($uid>0)
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
                USER_RANK => 0,
                USER_PRIOR_VISITED => 0,
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
            error_log(json_encode($record));
        }
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
            $this->setVisitUnixtime($uid);            
        }
        else
        {
            $bins[USER_PROVIDER_ID] = $identifier;
            $bins[USER_PROVIDER] = $provider;
            $this->createUser($bins);
        }
                
    }
    
    
    private function initKey(int $uid) 
    {
        return $this->getConnection()->initKey(NS_USER, TS_USER, $uid);
    }
    
    
    private function getId(string $generator, &$sequence)
    {
        $sequence = 0;
        $key = $this->getConnection()->initKey(NS_USER, "generators", 'gen_id');
        $operations = [
            ["op" => \Aerospike::OPERATOR_INCR, "bin" => $generator, "val" => 1],
            ["op" => \Aerospike::OPERATOR_READ, "bin" => $generator],
        ];
        
        if ($this->getConnection()->operate($key, $operations, $record)== \Aerospike::OK)
        {
            $sequence = $record[$generator];
        }        
    }

    
    private function getBins($pk, array $bins) : array
    {
        $record=[];
        if ($this->getConnection()->get($pk, $record, $bins) != \Aerospike::OK) 
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return [];
        }
        return $record['bins'];        
    }
    
    
    
    
    
    public function setVisitUnixtime(int $uid)
    {
        $pk = $this->initKey($uid);
        $record = $this->getBins($pk, [USER_LAST_VISITED]);
        if ($record)
        {        
            $this->setBins($pk, [USER_LAST_VISITED=>time(), USER_PRIOR_VISITED=>$record[USER_LAST_VISITED]]);
        }
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
        
    
    public function mobileUpdate(int $uid, int $number, array $bins)
    {
        $mobile_id=FALSE;
        $succes = FALSE;
        $where = \Aerospike::predicateEquals(USER_UID, $uid);
        $this->getConnection()->query(NS_USER, TS_MOBILE, $where, 
                function ($record) use ($number, &$mobile_id) 
                {
                    if ($record['bins'][USER_MOBILE_NUMBER]==$number)
                    {
                        $mobile_id = $record['bins'][SET_RECORD_ID];                    
                    }
                }, [USER_MOBILE_NUMBER, SET_RECORD_ID]);
                
        if ($mobile_id===FALSE)
        {
            $this->getId('mobile_id', $mobile_id);
            $succes = $this->setBins($this->getConnection()->initKey(NS_USER, TS_MOBILE, $mobile_id), [
                        SET_RECORD_ID=>$mobile_id, USER_UID=>$uid, USER_MOBILE_NUMBER=>$number, 
                        USER_MOBILE_ACTIVATION_CODE=>111, 
                        USER_MOBILE_DATE_REQUESTED=>time(), USER_MOBILE_DATE_ACTIVATED=>time(), 
                        USER_MOBILE_CODE_DELIVERED=>1, USER_MOBILE_SENT_SMS_COUNT=>0,
                        USER_MOBILE_FLAG=>0]);
        }
        else
        {
            $succes = $this->setBins($this->getConnection()->initKey(NS_USER, TS_MOBILE, $mobile_id), [USER_MOBILE_DATE_ACTIVATED=>time()]);
        }
        return $succes;
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
        $record=$this->getBins($pk, [USER_OPTIONS]);
        return isset($record[USER_OPTIONS]) ? $record[USER_OPTIONS] : $record;
    }

    
    public function getRank(int $uid) : int
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, [USER_RANK]);        
        return isset($record[USER_RANK]) ? $record[USER_RANK] : 0;
    }
}