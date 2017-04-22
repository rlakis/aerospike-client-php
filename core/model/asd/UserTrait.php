<?php

namespace Core\Model\ASD;

const NS_USER               = 'users';
const TS_USER               = 'profiles';
const TS_USER_PROVIDER      = 'profiles-uk';

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


const USER_PROVIDER_MOURJAN         = 'mourjan';
const USER_PROVIDER_ANDROID         = 'mourjan-android';
const USER_PROVIDER_IPHONE          = 'mourjan-iphone';


trait UserTrait
{

    abstract public function getConnection();
    abstract public function genId(string $generator, &$sequence);
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;
    abstract public function isReadError(int $status) : bool;

    
    
    public function fetchUser(int $uid)
    {
        $record = [];
        $trial = 0;
        do
        {
            $status = $this->getConnection()->get($this->initKey($uid), $record);
            
            if ($status==\Aerospike::OK)
            {
                return $record['bins'];
            }
            else if ($status==\Aerospike::ERR_RECORD_NOT_FOUND)
            {
                return [];
            }
            
            if ($status==\Aerospike::ERR_TIMEOUT)
            {
                usleep(100);
                $trial++;
            } 
        } while ($trial<3);

        error_log( "UID: {$uid} Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()} -- trial {$trial}" );
        return FALSE;
    }

    
    public function getProfileRecord(int $uid, &$record) : int
    {
        $status = $this->getConnection()->get($this->initKey($uid), $record);
        if ($status!=\Aerospike::OK && $status!=\Aerospike::ERR_RECORD_NOT_FOUND)
        {
            error_log( "UID: {$uid} Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
        }
        else if ($status==\Aerospike::OK)
        {
            $record=$record['bins'];
        }
        return $status;
    }
    
    
    public function fetchUserByUUID(string $uuid, &$record) 
    {
        $status = \Core\Model\NoSQL::getInstance()->getDeviceRecord($uuid, $device);
        if ($status==\Core\Model\NoSQL::OK)
        {
            $time = 0;
            do
            {
                $status = $this->getProfileRecord($device[USER_UID], $record);
                if (!$this->isReadError($status))
                {
                    $record['logged_by_device'] = $device;
                    break;
                }

                usleep(500);
                $time+=500;                
            }
            while ($time<2000000);            
        }
        return $status;
    }


    public function fetchUserByProviderId(string $identifier, string $provider='mourjan') 
    {
        $bins = FALSE;
        $uq = $this->asUserUniqueKey($identifier, $provider);
        if (($uv = $this->getBins($uq))!==FALSE)
        {
            if (!empty($uv))
            {
                return $this->fetchUser($uv[USER_UID]);
            }
        }
        
        $where = \Aerospike::predicateEquals(USER_PROVIDER_ID, "{$identifier}");
        $status = $this->getConnection()->query(NS_USER, TS_USER, $where,  
                    function ($record) use (&$bins, $provider) 
                    {
                        if ($bins==FALSE && (empty($provider) || $record['bins'][USER_PROVIDER]==$provider))
                        {
                            $bins = $record['bins'];
                        }
                    });
             
        if ($status !== \Aerospike::OK) 
        {
            error_log("An error occured while querying {$identifier}:{$provider} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}");
            return FALSE;
        } else
        {
            return ($bins === false ? [] : $bins);
        }        
    }
    
    
    public function fetchUsersByProvider(string $identifier, string $provider) 
    {
        $bins = [];
        $where = \Aerospike::predicateEquals(USER_PROVIDER_ID, strval($identifier));
        $status = $this->getConnection()->query(NS_USER, TS_USER, $where,  
                    function ($record) use (&$bins, $provider) 
                    {
                        if ($record['bins'][USER_PROVIDER]==$provider)
                        {                            
                            $bins[] = $record['bins'];
                        }
                    });
             
        if ($status !== \Aerospike::OK) 
        {
            error_log("An error occured while querying {$identifier}:{$provider} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}");
            return FALSE;
        } 
        return $bins;       
    }


    public function addUser(array &$bins) : int
    {
        if (!isset($bins[USER_PROVIDER_ID]) || !isset($bins[USER_PROVIDER]))
        {
            error_log("Invalid Unique key - Counld not update user record!!! ". PHP_EOL . json_encode($bins));
            return \Aerospike::ERR_INVALID_COMMAND;
        }

        $uk = $this->asUserUniqueKey($bins[USER_PROVIDER_ID], $bins[USER_PROVIDER]);

        $status = $this->getConnection()->exists($uk, $ukMetadata);
        if ($status == \Aerospike::OK)
        {
            error_log("A user with key ". $uk['key']. " exist in the database");
            error_log("Add User Profile Unique key violation!!! ". PHP_EOL . json_encode($uk) . PHP_EOL . json_encode($bins));
        }
        elseif ($status == \Aerospike::ERR_RECORD_NOT_FOUND)
        {
            $options = [\Aerospike::OPT_POLICY_RETRY=>\Aerospike::POLICY_RETRY_ONCE, \Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_CREATE];

            $this->genId('profile_id', $uid);
            if ($uid)
            {
                $now = time();
                $record = [
                    USER_PROFILE_ID => $uid,
                    USER_PROVIDER_EMAIL => '',
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

                foreach ($bins as $binName => $binValue)
                {
                    $record[$binName] = $binValue;
                }
                $pk=$this->asUserKey($uid);
                $status = $this->getConnection()->put($pk, $record, 0, $options);
                if ($status==\Aerospike::OK)
                {
                    if ($this->getConnection()->put($uk, [USER_UID=>$uid], 0, $options) !== \Aerospike::OK)
                    {
                        error_log(__FUNCTION__ . ": An error occured {$uid} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" . PHP_EOL . json_encode($uk));
                    }
                    $status = $this->getConnection()->get($pk, $bins);
                }
            }
        }
        else
        {
            error_log(__CLASS__.'->'.__FUNCTION__. " [{$this->getConnection()->errorno()}] ".$this->getConnection()->error());
        }

        return $status;
    }


    public function modUser(int $uid, array &$bins, bool $setNewVisit=FALSE)
    {
        if (isset($bins[USER_PROVIDER_ID]))
        {
            unset($bins[USER_PROVIDER_ID]);
        }

        if (isset($bins[USER_PROVIDER]))
        {
            unset($bins[USER_PROVIDER]);
        }

        if (isset($bins[USER_PROFILE_ID]))
        {
            unset($bins[USER_PROFILE_ID]);
        }

        if (empty($bins))
        {
            error_log ("Empty user bins for {$uid}");
            return \Aerospike::ERR_INVALID_COMMAND;
        }

        $pk = $this->asUserKey($uid);

        $status = $this->getConnection()->put($pk, $bins, 0,
                        [\Aerospike::OPT_POLICY_RETRY=>\Aerospike::POLICY_RETRY_ONCE,
                         \Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_UPDATE]);

        if ($status != \Aerospike::OK)
        {
            $this->logError(__CLASS__ .'->'. __FUNCTION__, $pk, $bins);
        }
        else if ($setNewVisit)
        {
            $this->setVisitUnixtime($uid);
        }
        return $status;
    }


    public function userUpdate(array $bins, int $uid=0, bool $as_visit=FALSE)
    {
        if ($uid==0)
        {
            if ($this->addUser($bins)==\Aerospike::OK)
            {
                return $bins;
            }
            else
            {
                return FALSE;
            }
        } else {
            if ($this->modUser($uid, $bins, $as_visit)==\Aerospike::OK)
            {
                //return $bins;
                return $this->fetchUser($uid);
            }
            else
            {
                return FALSE;
            }
        }


        if (isset($bins[USER_PROVIDER]) && $bins[USER_PROVIDER]==='mourjan' && preg_match('/@/', $bins[USER_PROVIDER_ID]))
        {
            $bins[USER_PROVIDER_ID] = strtolower(filter_var($bins[USER_PROVIDER_ID], FILTER_SANITIZE_EMAIL));
        }

        if ($uid==0)
        {
            $uk = $this->asUserUniqueKey($bins[USER_PROVIDER_ID], $bins[USER_PROVIDER]);
            if ($this->exists($uk))
            {
                error_log("Add User Profile Unique key violation!!! ". PHP_EOL . json_encode($uk) . PHP_EOL . json_encode($bins));
                return FALSE;
            }

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
                
                if (!isset($record[USER_PROVIDER_ID]) || !isset($record[USER_PROVIDER]))
                {
                    error_log("Invalid Unique key - Counld not update user record!! ". json_encode($record));
                    return FALSE;
                }
                
                $record[USER_PROVIDER] = trim(strtolower($record[USER_PROVIDER]));

                

                $uk = $this->asUserUniqueKey($record[USER_PROVIDER_ID], $record[USER_PROVIDER]);
                if ($this->exists($uk))
                {
                    error_log("User Profile Unique key violation!!! ". PHP_EOL . json_encode($uk) . PHP_EOL . json_encode($record));
                }
                
                $this->setUniqueKey($uk, $uid);
                
                if (isset($record[USER_PROVIDER_ID]) && is_numeric($record[USER_PROVIDER_ID]))
                {
                    $record[USER_PROVIDER_ID] = strval($record[USER_PROVIDER_ID]);
                }               
                
                
                if (($up=$this->fetchUserByProviderId($record[USER_PROVIDER_ID], $record[USER_PROVIDER]))!==FALSE && !empty($up))
                {
                    error_log("User Profile Unique key violation!!! ". json_encode($record));
                    return FALSE;
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
       
    
    public function asUserKey(int $uid) 
    {
        return $this->getConnection()->initKey(NS_USER, TS_USER, $uid);
    }    
    
    
    public function asUserUniqueKey(string &$uuid, string &$provider)
    {
        $provider = trim(strtolower($provider));
        $uuid = ($provider==='mourjan') ? strtolower(trim($uuid)) : trim($uuid);
        return $this->getConnection()->initKey(NS_USER, TS_USER_PROVIDER, "{$uuid}-{$provider}");
    }
    
    
    private function initKey(int $uid) 
    {
        return $this->getConnection()->initKey(NS_USER, TS_USER, $uid);
    }    
    
    
    public function userExists(int $uid) : bool
    {
        return $this->exists($this->initKey($uid));
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
    
    
    private function setUniqueKey(array $uniqueKey, int $uid) : int
    {
        if ($uid<=0)
        {
            error_log("Could not set user bin for zero uid");
            return \Core\Model\NoSQL::INVALID_FIELD;
        }
        if ($this->exists($uniqueKey))
        {
            $rec = $this->getBins($uniqueKey);
            if (($rec[USER_UID])!==$uid)
            {
                echo "{$rec[USER_UID]}, {$uid}", "\n";
                $this->setBins($uniqueKey, [USER_UID=>$uid]);
            }
            return 0;
        }
        $status = $this->getConnection()->put($uniqueKey, [USER_UID=>$uid], 0, [\Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_CREATE]);        
        if ($status !== \Aerospike::OK) 
        {
            error_log(__FUNCTION__ . ": An error occured {$uid} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" . PHP_EOL . json_encode($uniqueKey));
        }
        return $status;
    }
    
    
    public function setUserUniqueKey(string $uuid, string $provider, int $uid) : int
    {
        $pk = $this->asUserUniqueKey($uuid, $provider);
        return $this->setUniqueKey($pk, $uid);
    }
        
    
    public function scan(string $setName, array $bins)
    {
        $options = [\Aerospike::OPT_SCAN_PRIORITY=>\Aerospike::SCAN_PRIORITY_MEDIUM, \Aerospike::OPT_SCAN_PERCENTAGE=>100];
        $result = [];
        
        $status = $this->getConnection()->scan(
                    NS_USER, $setName,
                    function ($record) use (&$result) 
                    {
                        $result[] = $record['bins'];                       
                    },
                    $bins, $options);

        if ($status == \Aerospike::ERR_SCAN_ABORTED)
        {
            echo "Aborted", "\n";
        }
        else if ($status !== \Aerospike::OK)
        {
            error_log(__FUNCTION__ . ": An error occured [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}");
        }

        return $result;
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
        return $this->setBins($pk, [USER_OPTIONS => $opts]);
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


    public function getUserLastVisited(int $uid) : int
    {
        $pk = $this->initKey($uid);
        if (($record=$this->getBins($pk, [USER_LAST_VISITED]))!==FALSE)
        {
            return $record[USER_LAST_VISITED];
        }
        return -1;
    }

    /*
    public function delUser(int $uid) : bool
    {
        $status = $this->getConnection()->remove($this->initKey($uid));
        if ($status==\Aerospike::OK)
        {
            return TRUE;
        }
        return FALSE;

    }
     *
    */
    

    public function debugDeviceIntegrity()
    {
        $_devices = $this->scan(TS_DEVICE, [USER_DEVICE_UUID, USER_UID]);
        foreach ($_devices as $_dv)
        {
            if (($_u=$this->fetchUser($_dv[USER_UID]))!==FALSE)
            {
                if (empty($_u))
                {
                    echo __FUNCTION__, "\tNot found user record!\t". json_encode($_dv), "\n";
                }
            }
            else
            {
                echo __FUNCTION__, "\tError getting user record!\t". json_encode($_dv);
            }
        }
    }


    public function debugMobileIntegrity()
    {
        $_records = $this->scan(TS_MOBILE, [USER_MOBILE_NUMBER, USER_UID]);
        echo count($_records), " ", TS_MOBILE, " records", "\n";
        $no_user_err=0;
        $sys_err=0;
        foreach ($_records as $_rec)
        {
            if (($_u=$this->fetchUser($_rec[USER_UID]))!==FALSE)
            {
                if (empty($_u))
                {
                    $no_user_err++;
                    echo __FUNCTION__, "\tNot found user record!\t". json_encode($_rec), "\n";
                    $mk = $this->getConnection()->initKey(NS_USER, TS_MOBILE, "{$_rec[USER_UID]}-{$_rec[USER_MOBILE_NUMBER]}");
                    $this->getConnection()->remove($mk);
                }
            }
            else
            {
                $sys_err++;
                echo __FUNCTION__, "\tError getting user record!\t". json_encode($_rec);
            }
        }

        echo "\nUsers not found: ", $no_user_err, "\n";
        echo "\nUsers err fetch: ", $sys_err, "\n";
    }

}