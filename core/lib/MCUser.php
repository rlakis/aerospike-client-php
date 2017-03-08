<?php
require_once 'vendor/autoload.php';
require_once get_cfg_var('mourjan.path').'/core/lib/Jabber/JabberClient.php';
use Firebase\JWT\JWT;
use lib\Jabber\JabberClient;
        
class MCJsonMapper
{
    protected $metadata = [];
    
    protected function mapper(MCJsonMapper $object, stdClass $source)
    {
        foreach ($source as $property => $value) 
        {
            if (property_exists($object, $property)) 
            {
                if ($object->{$property} instanceof MCJsonMapper)
                {
                    $class_name = get_class($object->{$property});
                    $object->{$property} = new $class_name();
                    $this->mapper($object->{$property}, $value);  
                }
                else
                {
                    if (is_array($value) && count($value)>0 && isset($object->metadata[$property]))
                    {
                        foreach ($value as $element) 
                        {
                            $map = new $object->metadata[$property]();
                            $this->mapper($map, $element);
                            $object->{$property}[] = $map;
                        }
                    }
                    else 
                    {
                        $object->{$property} = $value;
                    }                     
                }
            }
        
        }
    }
    
}


class MCUser extends MCJsonMapper
{   
    public $id;
    public $pid;               // Provider identifier
    protected $email;
    protected $prvdr;             // Provider
    protected $fn;                // Full name
    protected $dn;                // Display name
    protected $pu;                // Provider profile URL
    protected $rd;                // registration date
    protected $lvts;              // Last visit timestamp
    protected $lvl;               // User level --> 0: normal, 4: warned, 5: blocked, 6: publisher, 9: administrator
    protected $name;              // User name
    protected $um;                // User mail
    protected $up;                // User Password
    protected $rnk;               // User Rank
    protected $pvts;              // Previous visit timestamp
    protected $ps;                // Publisher status;
    protected $lrts;              // last ad renew timestamp
    
    protected $opts;              // MCUserOptions    
    protected $mobile;            // MCMobile
    protected $dependants = [];   // Related user ids
    protected $devices = [];      // MCDevice ArrayList;
    protected $prps;              // MCPropSpace
     
    private $jwt = ['token'=>false, 'secret'=>'', 'claim'=>[]];
    
    function __construct($json=false) 
    {
        $this->metadata = ['devices'=>'MCDevice'];
        $this->opts = new MCUserOptions();
        $this->mobile = new MCMobile();
        if (is_numeric($json)) 
        {            
            $this->loadFromAreoSpike($json);
        }         
        elseif (is_string($json)) 
        {
            $this->set($json);
        }        
    }
    


    public function set($json)
    {
        //$this->mapper(  );
        //print_r($json);
        $this->mapper($this, $json);
    }

    
    public function loadFromAreoSpike(int $pk)
    {
        $config = [
          "hosts" => [
            [ "addr" => "h5.mourjan.com", "port" => 3000 ],
            [ "addr" => "h8.mourjan.com", "port" => 3000 ],
           ]];
        
        $db = new Aerospike($config, TRUE);
        if (!$db->isConnected()) 
        {
            error_log( "Failed to connect to the Aerospike server [{$db->errorno()}]: {$db->error()}");
            return;
        }
        
        $key = $db->initKey("users", "profiles", $pk);
        $status = $db->get($key, $record);
        if ($status != Aerospike::OK) 
        {
            error_log( "Error [{$db->errorno()}] {$db->error()}" );
            return;
        }
                
        $db->close();        
        
        $this->id = $record['bins']['id'];
        $this->pid = isset($record['bins']['provider_id'])?$record['bins']['provider_id']:$record['bins']['provide_id'];
        $this->email = $record['bins']['email'];
        $this->prvdr = $record['bins']['provider'];
        $this->fn = $record['bins']['full_name'];
        $this->dn = $record['bins']['display_name'];
        $this->pu = $record['bins']['profile_url'];
        $this->rd = $record['bins']['date_added'];
        $this->lvts = $record['bins']['last_visited'];
        $this->lvl = $record['bins']['level'];
        $this->name = $record['bins']['name'];
        $this->um = $record['bins']['user_email'];
        $this->up = $record['bins']['password'];
        $this->rnk = $record['bins']['rank'];
        $this->pvts = $record['bins']['prior_visited'];
        $this->ps = $record['bins']['pblshr_status'];
        $this->lrts = $record['bins']['last_renewed'];
        $this->dependants = $record['bins']['dependants'];
        
        $this->opts->parseAssoc($record['bins']['options']);
        $this->mobile = new MCMobile($record['bins']['mobile']);        
        
        foreach ($record['bins']['devices'] as $value) 
        {
            $this->devices[]=new MCDevice($value);
        }
        
        $this->jwt['token'] = isset($record['bins']['jwt']['token']) ? $record['bins']['jwt']['token'] : FALSE;
        $this->jwt['secret'] = isset($record['bins']['jwt']['secret']) ? $record['bins']['jwt']['secret'] : '';
        $this->jwt['claim'] = isset($record['bins']['jwt']['claim']) ? $record['bins']['jwt']['claim'] : [];
    }
       
    
    public function getID() : int
    {
        return $this->id;
    }
    
    public function isMobileVerified():bool
    {
        return $this->getMobile()->isVerified();
    }
    
    public function getProviderIdentifier() : string
    {
        return $this->pid;
    }
    
    public function isSuspended():bool
    {
        return $this->getOptions()->isSuspended();
    }
    
    public function getSuspensionTime():int
    {
        return $this->getOptions()->getSuspensionTime();
    }
    
    public function getProvider() : string
    {
        return $this->prvdr;
    }
    
    
    public function getEMail() : string
    {
        return $this->email;
    }
    
    
    public function getFullName() : string
    {
        return $this->fn;
    }
    
    
    public function getDisplayName() : string
    {
        return $this->dn;
    }
    
    
    public function getProfileURL() : string
    {
        return $this->pu;
    }
    
    
    public function getRegisterUnixtime() : int
    {
        return $this->rd;
    }
    
    
    public function getRegisterDate() : DateTime
    {
        return new Date($this->rd);
    }
    
    
    public function getLastVisitUnixtime() : int
    {
        return $this->lvts;
    }
    
    
    public function getLastVisitTime() : DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($this->lvts);
        return $date;
    }
    
    
    public function getLevel() : int
    {
        return $this->lvl;
    }
    
    public function isBlocked() : bool
    {
        return $this->getLevel()===5;
    }
    
    
    public function getUserName() : string
    {
        return $this->name;
    }
    
    
    public function getUserMail()
    {
        return $this->data->um;
    }
    
    
    public function getPassword()
    {
        return $this->data->up;
    }
    
    public function getRank()
    {
        return $this->data->rnk;
    }
    
    
    public function getPreviousVisitUnixtime() : int
    {
        return $this->pvts;
    }
    
    
    public function getPreviousVisitTime() : DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($this->pvts);
        return $date;
    }
    
    
    public function getPublisherStatus()
    {
        return $this->data->ps;        
    }
    
    
    public function getLastAdRenewUnixtime() : int
    {
        return $this->lrts;        
    }
    
    
    public function getLastAdRenewTime() : DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($this->lrts);
        return $date;
    }
    
    
    
    public function getOptions() : MCUserOptions
    {
        return $this->opts;
    }
    
    
    public function getMobile() : MCMobile
    {
        return $this->mobile;
    }
    
    public function getMobileNumber() : String
    {
        return $this->getMobile()->getNumber();
    }
    
    
    public function getDependants() : array
    {
        return $this->data->dependants;
    }
    
    
    public function getDevices() : array
    {
        return $this->devices;
    }
    
    
    public function hasPropSpace()
    {
        return ($this->data->prps!=null);
    }
    
    
    public function getPropSpace()
    {
        return $this->data->prps;
    }        

    
    public function createToken()
    {
        if ( !$this->isValidJsonWebToken($this->jwt['token']) )
        {
            $this->jwt['secret'] = hash('sha256', random_bytes(512), FALSE);
            $this->jwt['claim'] = [
                        "iss" => "mourjan", /* issuer */
                        "sub" => "any", /* subject */
                        "nbf" => time(), /* not before time */
                        "exp" => time(NULL) + 86400, /* expiration */
                        "iat" => time(), /* issued at */
                        "typ" => "jabber", /* type */
                        "pid" => getmypid(),
                        "mob" => $this->getMobileNumber(), 
                        "urd" => $this->getRegisterUnixtime(), 
                        "uid" => $this->getProviderIdentifier(), 
                        "pvd" => $this->getProvider()
                    ];
            
            $this->jwt['token'] = JWT::encode($this->jwt['claim'], $this->jwt['secret']);
            
            // The cluster can be located by just one host
            $config = [
              "hosts" => [
                    [ "addr" => "h5.mourjan.com", "port" => 3000 ],
                    [ "addr" => "h8.mourjan.com", "port" => 3000 ],
               ]];
        
            // The new client will connect and learn the cluster layout
            $db = new Aerospike($config, TRUE);
            if ($db->isConnected()) 
            {
                // records are identified as a (namespace, set, primary-key) tuple
                $digest = $db->getKeyDigest("users", "profiles", $this->getID());
                $key = $db->initKey("users", "profiles", $digest, TRUE);
                $db->put($key, array('jwt' => $this->jwt) );
                
                $db->close();
            }        
        
            $jabber = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
            if ($jabber->checkAccount( (string) $this->getID()) )
            {
                error_log("User.... already exists: <" . getmypid() . "> ". $this->getID().PHP_EOL);
                error_log($jabber->changePassword((string)$this->getID(), $this->jwt['token'])==0 ? "changed ".getmypid()  : "fail to change ".getmypid() );
            }
            else
            {
                $jabber->createUser((string)$this->getID(), $this->jwt['token']);
            }
            
        }
    }
    
    
    public function isValidJsonWebToken(string $token) : bool
    {
        if (!is_string($token)) return false;
        if (empty($this->jwt['secret']) || empty($this->jwt['claim'])) return false;
                
        JWT::$leeway = 60; // $leeway in seconds
        $decoded = (array) JWT::decode($token, $this->jwt['secret'], array('HS256'));                    
        return ($this->jwt['claim']==$decoded && $decoded['nbf']<time() &&  $decoded['exp']>time() && $token===$this->jwt['token']);
    }
    
    
    public function destroyToken()
    {
        $jabber = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
        if ($jabber->checkAccount( (string) $this->getID()) )
        {
            $jabber->kickUser((string) $this->getID());
        }
        
        $config = [
              "hosts" => [
                    [ "addr" => "h5.mourjan.com", "port" => 3000 ],
                    [ "addr" => "h8.mourjan.com", "port" => 3000 ],
               ]];
            
        // The new client will connect and learn the cluster layout
        $db = new Aerospike($config, TRUE);
            
        if ($db->isConnected()) 
        {
            // records are identified as a (namespace, set, primary-key) tuple
            $digest = $db->getKeyDigest("users", "profiles", $this->getID());
            $key = $db->initKey("users", "profiles", $digest, TRUE);
            $db->removeBin($key, ['jwt']);
                
            $db->close();
        }        
            
    }
    
    
    
    public function createToken1() : string
    {

        if (is_string($this->jwt) || strpos(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL), 'ajax') !== false )
        {
            return '';
        }
        
        
        $secret = hash('sha256', random_bytes(512), FALSE);
        
        $claim = [
            "iss" => "mourjan", /* issuer */
            "sub" => "any", /* subject */
            "nbf" => time(), /* not before time */
            "exp" => time(NULL) + 86400, /* expiration */
            "iat" => time(), /* issued at */
            "typ" => "jabber", /* type */
            "pid" => getmypid(),
            "mob" => $this->getMobileNumber(), 
            "urd" => $this->getRegisterUnixtime(), 
            "uid" => $this->getProviderIdentifier(), 
            "pvd" => $this->getProvider()];
        
        
        try 
        {
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'jwt_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
                $temp = $redis->get($this->getID());
                //error_log("Redis get:".PHP_EOL.$temp);
                if ($temp)
                {
                    $_temp = json_decode($temp, TRUE);
                    //var_dump($_temp);
                    if ($this->isValidToken($_temp['jwt']))
                    {
                        $this->jwt = $_temp['jwt'];    
                        error_log("Valid token pid<".getmypid().">: ".$this->getID());
                    }
                    else 
                    {
                        error_log("Invalid token pid<".getmypid().">: ".$this->getID());
                    }
                }
                
                if ($this->jwt===FALSE)
                {
                    $this->jwt = JWT::encode($claim, $secret);

                    $claim['key'] = $secret;
                    $claim['jwt'] = $this->jwt;
                    $redis->set($this->getID(), json_encode($claim));
                    $redis->expireAt($this->getID(), $claim['exp']);
                    
                    $jabber = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
                    if ($jabber->checkAccount( (string) $this->getID()) )
                    {
                        error_log("User already exists: <" . getmypid() . "> ". $this->getID().PHP_EOL);
                        //error_log($this->jwt);
                        error_log( $jabber->changePassword((string)$this->getID(), $this->jwt)==0 ? "changed ".getmypid()  : "fail to change ".getmypid() );
                    }
                    else
                    {
                        $jabber->createUser((string)$this->getID(), $this->jwt);
                    }
                }
            }
            else 
            {
                error_log("Could not connect to redis user store! " . $redis->getLastError(). '?!?!');
            }            	
        }
        catch (RedisException $re) 
        {           
            error_log(PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
        }
        finally 
        {
            $redis->close();
        }
        return $this->jwt;
    }
    
    
    public function getToken() : string
    {        
        $this->createToken();
        return $this->jwt['token'];
    }
    
    
    public function isValidToken(string $token) : bool
    {
        $result = FALSE;
        try 
        {
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'jwt_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
         
                $str = $redis->get($this->getID());
                if ($str)
                {
                    $claim = json_decode($str, TRUE);
                    
                    $secret = $claim['key'];
                    $jwt = $claim['jwt'];

                    unset( $claim['key'] );
                    unset( $claim['jwt'] );
                    
                    
                    JWT::$leeway = 60; // $leeway in seconds
                    $decoded = (array) JWT::decode($token, $secret, array('HS256'));
                    
                    $result = ($claim==$decoded && $decoded['nbf']<time() && $token===$jwt);
                }
            }
            else 
            {
                error_log("Could not connect to redis user store! " . $redis->getLastError(). '?!?!');
            }            	
        }
        catch (RedisException $re) 
        {           
            error_log(PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
        }
        catch (Firebase\JWT\SignatureInvalidException $se)
        {
            error_log(getmypid().PHP_EOL . PHP_EOL . $se->getCode() . PHP_EOL . $se->getMessage() . PHP_EOL . $se->getTraceAsString() . PHP_EOL);
        }
        finally 
        {
            $redis->close();
        }
        return $result;
    }
    
    
    public function destroyToken1()
    {
        try 
        {
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'jwt_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
                
                $redis->delete($this->getID());
            }
            else 
            {
                error_log("Could not connect to redis user store! " . $redis->getLastError(). '?!?!');
            }            	
        }
        catch (RedisException $re) 
        {           
            error_log(PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
        }
        finally 
        {
            $redis->close();
        }
    }
    
    
}


class MCUserOptions extends MCJsonMapper
{
    private $data;
    
    protected $e;             // int
    protected $lang;
    protected $bmail;
    protected $bmailKey;
    protected $watch = [];
    protected $cut;           // CallingTime
    protected $cui;           // ContactInfo
    protected $UA;
    protected $cts;
    protected $suspend;

    function __construct() 
    {
        $this->cui = new MCContactInfo();
        $this->cut = new MCCallingTime();
    }
    
    
    public function parseAssoc($array)
    {
        $this->e = $array['e'];
        $this->lang = $array['lang'];
        $this->bmail = $array['bmail'];
        $this->bmailKey = $array['bmailKey'];
        $this->watch = $array['subscriptions'];
        $this->cut->parseAssoc($array['calling_time']);
        $this->cui->parseAssoc($array['contact_info']);
        $this->UA = $array['user_agent'];
        $this->cts = $array['cts'];
        $this->suspend = $array['suspend'];                
    }
    
    
    public function getE() : int
    {
        return $this->e;
    }
    
    
    public function getLanguage() : string
    {
        return $this->lang;
    }
        
        
    public function getCallingTime() : MCCallingTime
    {
        return $this->cut;
    }
        
        
    public function getContactInfo() : MCContactInfo
    {
        return $this->cui;
    }
        
        
    public function getUserAgent() : string
    {
        return $this->UA;
    }
        
        
    public function isSuspended() : bool
    {
        return time() <= $this->suspend;
    }
        
    
    public function resetSuspendTill($tillTime=0)
    {
        $this->suspend = $tillTime;
    }
    
    public function getSuspensionTime():int
    {
        return $this->suspend ? $this->suspend : 0;
    }

}


class MCMobile extends MCJsonMapper
{
    protected $number;
    protected $code;
    protected $rts;       // Validation request timestamp;
    protected $ats;       // Activation timestamp
    protected $dlvrd;
    protected $sc;       // SMS sent count
    protected $flag;     // 2: ios    
    protected $secret;  // ios users only
    
    function __construct($as_array=null) 
    {
        if (is_array($as_array))
        {
            $this->number = $as_array['number'];
            $this->code = $as_array['code'];
            $this->rts = $as_array['date_requested'];
            $this->ats = $as_array['date_activated'];
            $this->dlvrd = $as_array['delivered'];
            $this->sc = $as_array['sms_count'];
            $this->flag = $as_array['flag'];
            $this->secret = $as_array['secret'];
        }
    }
    
    
    public function getNumber() : int
    {
        return $this->number ?: 0;
    }
    
    public function isVerified() : bool
    {
        return ($this->ats != null && $this->ats > time()-31556926 ) ? true: false;
    }
}


class MCDevice extends MCJsonMapper
{
    protected $uuid;
    protected $model;
    protected $name;
    protected $sn;        // System name
    protected $sv;        // System version
    protected $lvts;      // Last vist timestamp
    protected $tk;        // Push notification token
    protected $pn;        // enabled push notification
    protected $ccc;       // Carrier country iso code;
    protected $iav;       // Installed application version
    protected $prefs;
    protected $rmvd;
    protected $dats;      // date added
    protected $pa;        // purchase allowed    
    

    function __construct($as_array=null) 
    {
        if (is_array($as_array))
        {
            $this->uuid = $as_array['uuid'];
            $this->model = $as_array['model'];
            $this->name = $as_array['name'];
            $this->sn = $as_array['sys_name'];
            $this->sv = $as_array['sys_version'];
            $this->lvts = $as_array['last_visited'];
            $this->tk = $as_array['token'];
            $this->pn = $as_array['push_enabled'];
            $this->ccc = $as_array['carrier_country'];
            $this->iav = $as_array['app_version'];
            $this->prefs = $as_array['app_preferences'];
            $this->rmvd = $as_array['removed'];
            $this->dats = $as_array['date_added'];
            $this->pa = $as_array['purchase_enabled'];
        }
    }

}


class MCContactInfo extends MCJsonMapper
{
    protected $p = [];  // List<PhoneData> 
    protected $b;
    protected $e;
    protected $s;
    protected $t; 
    
    function __construct() 
    {
        $this->metadata = ['p'=>'MCPhoneData'];
    }
    
    public function parseAssoc($array)
    {
        foreach ($array['phones'] as $phone) 
        {
            $pc = new MCPhoneData();
            $pc->parseAssoc($phone);
            $this->p[] = $pc;
        }
        $this->b = $array['blackberry'];
        $this->e = $array['email'];
        $this->s = $array['skype'];
        $this->t = $array['twiter'];
    }
}


class MCCallingTime extends MCJsonMapper
{
    protected $t; // type (before, after, between)
    protected $b; // before
    protected $a; // after   
    

    public function parseAssoc($array)
    {
        $this->t = $array['type'];
        $this->b = $array['before'];
        $this->a = $array['after'];
    }
}


class MCPhoneData extends MCJsonMapper
{
    protected $v;
    protected $t;
    protected $c;
    protected $r;
    protected $i;
    
    public function parseAssoc($array)
    {
        $this->v = $array['humain'];
        $this->t = $array['type'];
        $this->c = $array['country_key'];
        $this->r = $array['raw_input'];
        $this->i = $array['country_iso'];
    }
}


class MCPropSpace extends MCJsonMapper
{
    protected $id;
    protected $lnk;
    protected $cntr;      // counter
    protected $lcts;      // last crawled timestamp
    protected $st;        // State
    protected $dats;      // date added ts
}
?>
