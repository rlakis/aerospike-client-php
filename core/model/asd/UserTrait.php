<?php

namespace Core\Model\ASD;


const NS_USER               = 'users';
const NS_EDIGEAR            = 'edigear';
const TS_USER               = 'profiles';
const TS_PROFILE            = 'profile';

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
const USER_MOBILE_REQUEST_ID        = 'request_id';
const USER_MOBILE_VALIDATION_TYPE   = 'request_type';
const USER_MOBILE_PIN_HASH          = 'pin_hash';
const USER_MOBILE_REQUEST_TYPE      = 'request_type';



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

const USER_PAYFORT_TOKEN            = 'payfort';

const USER_PROVIDER_MOURJAN         = 'mourjan';
const USER_PROVIDER_ANDROID         = 'mourjan-android';
const USER_PROVIDER_IPHONE          = 'mourjan-iphone';


trait UserTrait {

    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, &$sequence) : int;
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;
    abstract public function isReadError(int $status) : bool;

    
    public function addProfile(array &$bins, int $uid=0) : int {
        if (!isset($bins[USER_PROVIDER_ID]) || !isset($bins[USER_PROVIDER])) {
            \Core\Model\NoSQL::Log(['Invalid Profile Key!', $bins]);
            //error_log("Invalid Profile key!!! ". PHP_EOL . json_encode($bins));
            return \Aerospike::ERR_INVALID_COMMAND;
        }

        if (isset($bins[USER_MOBILE])) unset ($bins[USER_MOBILE]);
        if (isset($bins[USER_DEVICES])) unset ($bins[USER_DEVICES]);
        if (isset($bins[USER_DEPENDANTS])) unset ($bins[USER_DEPENDANTS]);
        
        $primaryKey = $this->asUserPrimaryKey($bins[USER_PROVIDER_ID], $bins[USER_PROVIDER]);                
        
        if (!in_array($bins[USER_PROVIDER], ['facebook', 'mourjan', 'mourjan-android', 'mourjan-iphone', 'twitter', 'google', 'linkedin', 'yahoo', 'live', 'aol'])) {
            error_log("Invalid Profile provider!!! ". PHP_EOL . json_encode($bins));
            return \Aerospike::ERR_INVALID_COMMAND;
        }

        $now = time();
        $options = [\Aerospike::OPT_POLICY_KEY => \Aerospike::POLICY_KEY_DIGEST,
                    \Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE,
                    \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_CREATE];
        
        if (version_compare(phpversion("aerospike"), '7.2.0') >= 0) {
            unset($options[\Aerospike::OPT_POLICY_RETRY]);
            $options[\Aerospike::OPT_MAX_RETRIES]=1;            
        }
        
        $status=$this->genId('profile_id', $uid);
        if ($status==\Aerospike::OK) {        
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
                USER_XMPP_CREATED => 0,
                USER_OPTIONS => [USER_OPTIONS_CTS=>$now],
                ];

            foreach ($bins as $binName => $binValue) {
                $record[$binName] = $binValue;
            }
                                  
            $status = $this->getConnection()->put($primaryKey, $record, 0, $options);
                
            if ($status==\Aerospike::OK) {
                if (($status=$this->getConnection()->get($primaryKey, $record))==\Aerospike::OK) {
                    $bins=$record['bins'];                    
                    
                    try {
                        
                        include_once get_cfg_var('mourjan.path') . '/config/cfg.php';
                        include_once get_cfg_var('mourjan.path') . '/core/model/Db.php';
                        global $config;
                        $DB = new \Core\Model\DB($config);

                        $db_q = "UPDATE OR INSERT INTO WEB_USERS " .
                            "(ID, IDENTIFIER, EMAIL, PROVIDER, FULL_NAME, DISPLAY_NAME, " .
                            "PROFILE_URL, LVL, " .
                            "USER_NAME, USER_EMAIL, USER_PASS, USER_RANK, " .
                            "USER_PUBLISHER) " .
                            " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt=$DB->prepareQuery($db_q);
                        
                        $stmt->execute([
                            $bins[USER_PROFILE_ID],
                            $bins[USER_PROVIDER_ID],
                            $bins[USER_EMAIL],
                            $bins[USER_PROVIDER],
                            $bins[USER_FULL_NAME],
                            $bins[USER_DISPLAY_NAME],
                            $bins[USER_PROFILE_URL],
                            $bins[USER_LEVEL],
                            $bins[USER_NAME],
                            $bins[USER_EMAIL],
                            $bins[USER_PASSWORD],
                            $bins[USER_RANK],
                            $bins[USER_PUBLISHER_STATUS]
                        ]);
                        
                        unset($stmt);                                            
                    } 
                    catch (Exception $ex) {
                        error_log($ex->getMessage());
                    }                                        
                }
            }
            else {
                error_log(__CLASS__.'->'.__FUNCTION__. " [{$this->getConnection()->errorno()}] ".$this->getConnection()->error());
            }
        } 
        else {
            \Core\Model\NoSQL::Log(['Message'=>'Id Generator Failure', 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        }
        return $status;
    }    
    
    
    public function modProfile(array $keyMap, array $bins, bool $setNewVisit=FALSE) : int {
        $pk=$this->getProfileKeyFromParams($keyMap);
                        
        if (isset($bins[USER_PROVIDER_ID])) { unset($bins[USER_PROVIDER_ID]); }
        if (isset($bins[USER_PROVIDER])) { unset($bins[USER_PROVIDER]); }
        if (isset($bins[USER_PROFILE_ID])) { unset($bins[USER_PROFILE_ID]); }

        if (empty($bins)) {
            \Core\Model\NoSQL::Log(['Key'=>$pk['key'], 'Error'=>"Empty user profile bins"]);
            return \Aerospike::ERR_INVALID_COMMAND;
        }

        $options = [\Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE,
                    \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_UPDATE];
        if (version_compare(phpversion("aerospike"), '7.2.0') >= 0) { 
            $options[\Aerospike::OPT_MAX_RETRIES]=1;
            unset($options[\Aerospike::OPT_POLICY_RETRY]);
        }
        $status = $this->getConnection()->put($pk, $bins, 0, $options);
        
        if ($status !== \Aerospike::OK) {
            \Core\Model\NoSQL::Log(['key'=>$pk['key'], 'bins'=>$bins, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        }
        else if ($setNewVisit) {
            $this->updateProfileVisitTime(['key'=>$pk]);
        }
        
        return $status;
    }
    
    
    public function getProfile(array $keyMap, &$record) : int {        
        $pk = $this->getProfileKeyFromParams($keyMap);
        $status = $this->getConnection()->get($pk, $record);
        if ($status!==\Aerospike::OK && $status!==\Aerospike::ERR_RECORD_NOT_FOUND) {
            \Core\Model\NoSQL::Log(['Key'=>$pk['key'], 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        }
        return $status;
    }
    
    
    public function getProfileRecord(array $keyMap, &$record) : int {
        $pk = $this->getProfileKeyFromParams($keyMap);
        if (isset($pk['digest']) && !empty($pk['digest']) && $pk['key']==NULL) {
            $pk = $this->getConnection()->initKey($pk['ns'], $pk['set'], $pk['digest'], true);
        }
        $status = $this->getConnection()->get($pk, $record);
        if ($status==\Aerospike::OK) {
            $record = $record['bins'];
        }
        else if ($status!==\Aerospike::ERR_RECORD_NOT_FOUND) {
            
            \Core\Model\NoSQL::Log(['Key'=>$pk['key'], 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        }
        
        return $status;
    }

    
    private function getPrimaryKey(int $uid, &$result) : int {
        return $this->getProfileBasicRecord($uid, $result, [USER_PROFILE_ID, USER_PROVIDER, USER_PROVIDER_ID]);
    }
    
    
    public function getProfileBasicRecord(int $uid, &$record, $bins=[]) : int {
        $where = \Aerospike::predicateEquals(USER_PROFILE_ID, $uid);
        $status = $this->getConnection()->query(NS_USER, TS_PROFILE, $where, function ($_record) use (&$record) {$record=$_record;}, $bins);
        if ($status!==\Aerospike::OK) {
            \Core\Model\NoSQL::Log(['UID'=>$uid, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        }// else if (version_compare(phpversion("aerospike"), '7.2.0') >= 0) {
            //error_log(var_export($record, TRUE));
        //}
        if (empty($record)) {
            error_log(__FUNCTION__." {$uid} not queried!");
        }
        return $status;
    }
    
    
    public function fetchUser(int $uid) {
        $record = [];
        $trial = 0;
        do {
            $status = $this->getProfileBasicRecord($uid, $record);
            
            if ($status==\Aerospike::OK) {
                if (!isset($record['bins'])) {
                    return [];
                }
                return $record['bins'];
            }
            else if ($status==\Aerospike::ERR_RECORD_NOT_FOUND) {
                return [];
            }
            
            if ($status==\Aerospike::ERR_TIMEOUT) {
                usleep(100);
                $trial++;
            }
        } while ($trial<3);
        \Core\Model\NoSQL::Log(['UID'=>$uid, 'trials'=>$trial, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        return FALSE;
    }
    
    
    public function fetchUserByUUID(string $uuid, &$record) {
        $status = \Core\Model\NoSQL::getInstance()->getDeviceRecord($uuid, $device);
        
        if ($status==\Aerospike::OK) {
            $status= $this->getProfileBasicRecord($device[USER_UID], $record);
            if ($status==\Aerospike::OK && !empty($record)) {
                $record=$record['bins'];
                $record['logged_by_device'] = $device;
            }
        }

        return $status;
    }


    public function fetchUserByProviderId(string $identifier, string $provider, &$bins) : int {
        $pk = $this->asUserPrimaryKey($identifier, $provider);

        $status = $this->getConnection()->get($pk, $record);            
        if ($status==\Aerospike::OK) {
            $bins = $record['bins'];
        }
        else if ($status!==\Aerospike::ERR_RECORD_NOT_FOUND) {
            error_log(__FUNCTION__ . PHP_EOL . $identifier.'-'.$provider. ' Error');
        }
        
        return $status;
    }
    
    
    public function fetchUserByProviderIdOld(string $identifier, string $provider='mourjan') {
        $pk = $this->asUserPrimaryKey($identifier, $provider);
        
        $status = $this->getConnection()->get($pk, $record);            
        if ($status==\Aerospike::OK) {
            return $record['bins'];
        }
        else if ($status==\Aerospike::ERR_RECORD_NOT_FOUND) {
            return [];
        }
        else {
            error_log(__FUNCTION__ . PHP_EOL . $identifier.'-'.$provider. ' Error');
            return FALSE;
        }                
    }
    
    
    private function asUserPrimaryKey(string &$uuid, string &$provider) {
        $provider = trim(strtolower($provider));
        if ($provider===USER_PROVIDER_MOURJAN && preg_match('/@/', $uuid)) {
            $uuid = strtolower(filter_var($uuid, FILTER_SANITIZE_EMAIL));
        }
        $uuid = trim($uuid);
        return $this->getConnection()->initKey(NS_USER, TS_PROFILE, "{$uuid}-{$provider}");
    }
    
        
    public function profileExists(array $keyMap) : bool {
        $key=$this->getProfileKeyFromParams($keyMap);
        return $this->exists($key);
    }
    
    
    private function getProfileKeyFromParams(array $params) {        
        if (isset($params['key'])) {
            $key = $params['key'];            
            if (isset($key['digest']) && !empty($key['digest'])) {
                $key = $this->getConnection()->initKey(NS_USER, TS_PROFILE, $key['digest'], TRUE);
            }
        }
        else if (isset($params[USER_UID])) {
            if ($this->getPrimaryKey($params[USER_UID], $result)==\Aerospike::OK) {
                $key = $result['key'];
            }
        }
        else {
            $key = $this->asUserPrimaryKey($params[USER_PROVIDER_ID], $params[USER_PROVIDER]);
        }
        
        if (!isset($key)) {
            throw new Exception('Profile key not defined -- '.json_encode($params));
        }
        
        
        if ($key==null || empty($key)) {
            error_log(json_encode($params));            
        }
        
        return $key;   
    }
    
    
    public function updateProfileVisitTime(array $params) {
        $pk=$this->getProfileKeyFromParams($params);
        if (($record = $this->getBins($pk, [USER_LAST_VISITED]))!==FALSE) {        
            $this->setBins($pk, [USER_LAST_VISITED=>time(), USER_PRIOR_VISITED=>$record[USER_LAST_VISITED]]);
        }
    }               
    
    
    public function setUserBin(int $uid, string $bin, $value) : bool {
        if ($uid<=0)  {
            error_log("Could not set user bin for zero uid");
        }
        else if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [$bin => $value]);
        }

        return FALSE;
    }
    
    
    public function setOptions(int $uid, array $opts) : bool {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [USER_OPTIONS => $opts]);
        }
        return FALSE;
    }
    
    
    public function setPassword(int $uid, string $password) : bool {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [USER_PASSWORD => $password]);
        }
        return FALSE;
    }
    
    
    public function setUserLevel(int $uid, int $level) : bool {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [USER_LEVEL => $level]);
        }
        return FALSE;
    }
    

    public function setUserPublisherStatus(int $uid, int $type) : bool {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [USER_PUBLISHER_STATUS => $type]);
        }
    }

    
    public function setEnabledXMPP(int $uid) {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            $this->setBins($result['key'], [USER_XMPP_CREATED => 1]);
        }
    }
    
    
    public function setJsonWebToken(int $uid, array $jwt) : bool {   
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            return $this->setBins($result['key'], [USER_JWT => $jwt]);
        }
        return FALSE;
    }
    
    
    public function unsetJsonWebTocken(int $uid) {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            $this->getConnection()->removeBin($result['key'], [USER_JWT]);
        }
    }
    
    /*
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
    */
    
    public function getOptions(int $uid) : array {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            if (($record=$this->getBins($result['key'], [USER_OPTIONS]))!==FALSE) {
                return $record[USER_OPTIONS];
            }
        }
        error_log("Could nor get user options for UID {$uid}");
        return [];
    }

    
    public function getRank(int $uid) : int {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            if (($record=$this->getBins($result['key'], [USER_RANK]))!==FALSE) {
                return $record[USER_RANK];
            }
        }
        return 1;
    }
    
    
    public function getUserPublisherStatus(int $uid) {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            if (($record=$this->getBins($result['key'], [USER_PUBLISHER_STATUS]))!==FALSE) {
                return $record[USER_PUBLISHER_STATUS];
            }
        }
        return FALSE;
    }


    public function getUserLastVisited(int $uid) : int {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            if (($record=$this->getBins($result['key'], [USER_LAST_VISITED]))!==FALSE) {
                return $record[USER_LAST_VISITED];
            }
        }
        return -1;
    }


    public function getUserPayfortToken(int $uid) {
        if ($this->getPrimaryKey($uid, $result)==\Aerospike::OK) {
            if (($record=$this->getBins($result['key'], [USER_PAYFORT_TOKEN]))!==FALSE) {
                return isset($record[USER_PAYFORT_TOKEN]) ? $record[USER_PAYFORT_TOKEN] : FALSE;
            }
        }
        return FALSE;
    }
    
    
    /* Debug section */
    
    public function scan(string $setName, array $bins) {
        $options = [\Aerospike::OPT_SCAN_PRIORITY=>\Aerospike::SCAN_PRIORITY_MEDIUM, \Aerospike::OPT_SCAN_PERCENTAGE=>100];
        $result = [];
        $i=0;
        $status = $this->getConnection()->scan(
                    NS_USER, $setName,
                    function ($record) use (&$result, &$i) {
                        //$record['bins']['digest']=base64_encode($record['key']['digest']);
                        $result[] = $record['bins'];   
                        $i++;
                        if ($i % 1000==0) {
                            echo ".";
                        }
                        //$this->addProfile($record['bins'], $record['bins'][USER_PROFILE_ID]);
                    },
                    $bins, $options);

        if ($status == \Aerospike::ERR_SCAN_ABORTED) {
            echo "Aborted", "\n";
        }
        else if ($status !== \Aerospike::OK) {
            error_log(__FUNCTION__ . ": An error occured [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}");
        }

        return $result;
    }

    /*
    public function delUser(int $uid) : bool {
        $status = $this->getConnection()->remove($this->initKey($uid));
        if ($status==\Aerospike::OK) {
            return TRUE;
        }
        return FALSE;
    }
    */
    

    public function debugDeviceIntegrity() {
        $_devices = $this->scan(TS_DEVICE, [USER_DEVICE_UUID, USER_UID]);
        foreach ($_devices as $_dv) {
            if (($_u=$this->fetchUser($_dv[USER_UID]))!==FALSE) {
                if (empty($_u)) {
                    echo __FUNCTION__, "\tNot found user record!\t". json_encode($_dv), "\n";
                }
            }
            else {
                echo __FUNCTION__, "\tError getting user record!\t". json_encode($_dv);
            }
        }
    }


    public function debugMobileIntegrity() {
        $_records = $this->scan(TS_MOBILE, [USER_MOBILE_NUMBER, USER_UID]);
        echo count($_records), " ", TS_MOBILE, " records", "\n";
        $no_user_err=0;
        $sys_err=0;
        foreach ($_records as $_rec) {
            if (($_u=$this->fetchUser($_rec[USER_UID]))!==FALSE) {
                if (empty($_u)) {
                    $no_user_err++;
                    echo __FUNCTION__, "\tNot found user record!\t". json_encode($_rec), "\n";
                    //$mk = $this->getConnection()->initKey(NS_USER, TS_MOBILE, "{$_rec[USER_UID]}-{$_rec[USER_MOBILE_NUMBER]}");
                    //$this->getConnection()->remove($mk);
                }              
            }
            else {
                $sys_err++;
                echo __FUNCTION__, "\tError getting user record!\t". json_encode($_rec);
            }
        }

        echo "\nUsers not found: ", $no_user_err, "\n";
        echo "\nUsers err fetch: ", $sys_err, "\n";
    }

    
    public function debugUniqueUserIntegrity() {
        $no_user_err=0;
        $sys_err=0;
/*        
        $_records = $this->scan(TS_USER_PROVIDER, [USER_UID]);
        echo count($_records), " ", TS_USER_PROVIDER, " records", "\n";
        foreach ($_records as $_rec)
        {
            $pk = $this->asUserKey($_rec[USER_UID]);
            if (!$this->exists($pk))
            {
                $no_user_err++;
                echo __FUNCTION__, "\tNot found user record!\t". json_encode($_rec), "\n";
                //$mk = $this->getConnection()->initKey(NS_USER, TS_MOBILE, "{$_rec[USER_UID]}-{$_rec[USER_MOBILE_NUMBER]}");
                //$this->getConnection()->remove($mk);
            }
        }

        echo "\nUsers not found: ", $no_user_err, "\n";
        echo "\nUsers err fetch: ", $sys_err, "\n";
        */
        $_records = $this->scan(TS_USER, [USER_PROVIDER_ID, USER_PROVIDER, USER_PROFILE_ID]);
        echo count($_records), " ", TS_USER, " records", "\n";
        foreach ($_records as $_rec) {
            $pk = $this->asUserUniqueKey($_rec[USER_PROVIDER_ID], $_rec[USER_PROVIDER]);
            if (!$this->exists($pk)) {
                $no_user_err++;
                echo __FUNCTION__, "\tUnique Key Not found user for record!\t". json_encode($_rec), "\n";             
            }
        }
        echo "\nUsers not found: ", $no_user_err, "\n";        
    }
    
    
    public function build() {        
        $_records = $this->scan(TS_USER, []);
        echo count($_records), " ", TS_USER, " records", "\n";
        foreach ($_records as $_rec) {
            //$this->addProfile($_rec, $_rec[USER_PROFILE_ID]);            
        }        
    }


    public function genDistributedUID() {
        $millis = round(microtime(true) * 1000)-1483228800000;

        $server=get_cfg_var('mourjan.server_id');
        $this->genId("user_id-{$server}", $sq);
        $id = $millis << 22 | $server << 11 | $sq;
        return $id;
    }


}