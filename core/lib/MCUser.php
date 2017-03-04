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
    protected $id;
    protected $pid;               // Provider identifier
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
     
    private $jwt;
    
    function __construct($json=false) 
    {
        $this->metadata = ['devices'=>'MCDevice'];
        $this->opts = new MCUserOptions();
        $this->mobile = new MCMobile();
        $this->jwt = FALSE;
        if ($json) 
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

    
    public function createToken() : string
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
                        error_log($jabber->changePassword((string)$this->getID(), $this->jwt)==0 ? "changed ".getmypid()  : "fail to change ".getmypid() );
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
        if ($this->jwt===FALSE)
        {
            return $this->createToken();
        }
        return $this->jwt;
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
    
    
    public function destroyToken()
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
}


class MCCallingTime extends MCJsonMapper
{
    protected $t; // type (before, after, between)
    protected $b; // before
    protected $a; // after   
}


class MCPhoneData extends MCJsonMapper
{
    protected $v;
    protected $t;
    protected $c;
    protected $r;
    protected $i;    
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
