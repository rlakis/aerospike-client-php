<?php
require_once 'vendor/autoload.php';
require_once get_cfg_var('mourjan.path').'/core/model/NoSQL.php';
require_once get_cfg_var('mourjan.path').'/core/lib/Jabber/JabberClient.php';

use Firebase\JWT\JWT;
use lib\Jabber\JabberClient;
use Core\Model\NoSQL;
use Core\Model\ASD;
        
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
    protected $xmpp = 0;
     
    private $jwt = ['token'=>false, 'secret'=>'', 'claim'=>[]];
    
    public $device = null; // used to deal with current logged in device
    
    function __construct($source_data=false) 
    {
        $this->metadata = ['devices'=>'MCDevice'];
        $this->opts = new MCUserOptions();
        $this->mobile = new MCMobile();

        if (is_numeric($source_data)) 
        {
            $this->parseArray(NoSQL::getInstance()->fetchUser($source_data));
        }         
        elseif (is_string($source_data) && $source_data) 
        {
            if ($source_data[0]=='{')
            {
                $this->set($source_data);
            }
            else
            {
                $this->parseArray(NoSQL::getInstance()->fetchUserByUUID($source_data));
            }
        }   
    }
    


    public function set($json)
    {
        $this->mapper($this, $json);
    }


    private function parseArray(array $record)
    {
        if (empty($record))
        {
            return;
        }
        
        $this->id = $record[ASD\USER_PROFILE_ID];
        $this->pid = isset($record[ASD\USER_PROVIDER_ID])?$record[ASD\USER_PROVIDER_ID]:$record['provide_id'];
        $this->email = $record['email'];
        $this->prvdr = $record['provider'];
        $this->fn = $record['full_name'];
        $this->dn = $record['display_name'];
        $this->pu = $record['profile_url'];
        $this->rd = $record['date_added'];
        $this->lvts = $record['last_visited'];
        $this->lvl = $record['level'];
        $this->name = $record['name'];
        $this->um = $record['user_email'];
        $this->up = $record['password'];
        $this->rnk = $record['rank'];
        $this->pvts = $record['prior_visited'];
        $this->ps = $record['pblshr_status'];
        $this->lrts = $record['last_renewed'];
        $this->dependants = $record[ASD\USER_DEPENDANTS];

        $this->opts->parseAssoc($record['options']);

        $this->mobile = isset($record['mobile']) ? new MCMobile($record['mobile']) : new MCMobile();

        $uuid = '';
        if (isset($record['logged_by_device']))
        {
            $this->device = new MCDevice($record['logged_by_device']);
            $uuid = $this->device->getUUID();
        }
        
        foreach ($record['devices'] as $value)
        {
            if ($value[ASD\USER_DEVICE_UUID]!==$uuid)
            {
                $this->devices[]=new MCDevice($value);
            }
        }

        $this->jwt['token'] = isset($record['jwt']['token']) ? $record['jwt']['token'] : FALSE;
        $this->jwt['secret'] = isset($record['jwt']['secret']) ? $record['jwt']['secret'] : '';
        $this->jwt['claim'] = isset($record['jwt']['claim']) ? $record['jwt']['claim'] : [];
        
        $this->xmpp = isset($record[ASD\USER_XMPP_CREATED]) ? $record[ASD\USER_XMPP_CREATED] : 0;
    }   
    
    
    public function getAsArray() : array
    {
        $result = [
            ASD\USER_PROFILE_ID => $this->getID(), 
            ASD\USER_PROVIDER_ID => $this->getProviderIdentifier(),
            ASD\USER_PROVIDER_EMAIL => $this->getEMail(),
            ASD\USER_PROVIDER => $this->getProvider(),
            ASD\USER_FULL_NAME => $this->getFullName(),
            ASD\USER_DISPLAY_NAME => $this->getDisplayName(),
            ASD\USER_PROFILE_URL => $this->getProfileURL(),
            ASD\USER_DATE_ADDED => $this->getRegisterUnixtime(),
            ASD\USER_LAST_VISITED => $this->getLastVisitUnixtime(),
            ASD\USER_LEVEL => $this->getLevel(),
            ASD\USER_NAME => $this->getUserName(),
            ASD\USER_EMAIL => $this->getUserMail(),
            ASD\USER_PASSWORD => $this->getPassword(),
            ASD\USER_RANK => $this->getRank(),
            ASD\USER_PRIOR_VISITED => $this->getPreviousVisitUnixtime(),
            ASD\USER_PUBLISHER_STATUS => $this->getPublisherStatus(),
            ASD\USER_LAST_AD_RENEWED => $this->getLastAdRenewUnixtime(),
            ASD\USER_DEPENDANTS => $this->getDependants(),
            ASD\USER_OPTIONS => $this->opts->getAsArray(),
            ASD\USER_MOBILE => $this->mobile->getAsArray(),
            ASD\USER_DEVICES => [],
            ASD\USER_XMPP_CREATED => $this->xmpp,
        ];
        foreach ($this->getDevices() as $dvc) 
        {
            $result[ASD\USER_DEVICES][] = $dvc->getAsArray();
        }
        return $result;
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
        if ($this->lvl==null)
            $this->lvl=0;
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
        return $this->um;
    }
    
    
    public function getPassword()
    {
        return $this->up;
    }
    
    public function getRank()
    {
        return $this->rnk;
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
        return $this->ps;        
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
        return $this->dependants;
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
            
            NoSQL::getInstance()->setJsonWebToken($this->getID(), $this->jwt);
            
        
            $jabber = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
            if ($this->xmpp)
            {
                $jabber->changePassword((string)$this->getID(), $this->jwt['token']);
            } 
            else 
            {            
                if ($jabber->checkAccount( (string) $this->getID()) )
                {
                    NoSQL::getInstance()->setEnabledXMPP($this->getID());
                    $jabber->changePassword((string)$this->getID(), $this->jwt['token']);
                }
                else
                {
                    try
                    {
                        $jabber->createUser((string)$this->getID(), $this->jwt['token']);
                        NoSQL::getInstance()->setEnabledXMPP($this->getID());
                    } 
                    catch (Exception $e) {}
                }
            }
            
        }
    }
    
    
    public function isValidJsonWebToken(string $token) : bool
    {
        if (!is_string($token)) return false;
        if (empty($this->jwt['secret']) || empty($this->jwt['claim'])) return false;
                
        try
        {
            JWT::$leeway = 60; // $leeway in seconds
            $decoded = (array) JWT::decode($token, $this->jwt['secret'], array('HS256'));
        }
        catch (Exception $e)
        {
            //error_log(__CLASS__.".".__FUNCTION__.' <'.$e->getMessage().'>');
            return FALSE;
        }
        
        return ($this->jwt['claim']==$decoded && $token===$this->jwt['token']);
    }
    
    
    public function destroyToken()
    {
        $jabber = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
        if ($jabber->checkAccount( (string) $this->getID()) )
        {
            $jabber->kickUser((string) $this->getID());
        }
        NoSQL::getInstance()->unsetJsonWebTocken($this->getID());            
    }
    
    
    
    public function getToken() : string
    {        
        $this->createToken();
        return $this->jwt['token'];
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
    
    
    public function getAsArray() : array
    {
        return [
            'e'=> $this->e, 
            'lang'=> $this->lang, 
            'bmail'=> $this->bmail,
            'bmailKey'=> $this->bmailKey,
            'subscriptions'=> $this->watch,
            'calling_time'=> $this->cut->getAsArray(),
            'contact_info'=> $this->cui->getAsArray(),
            'user_agent'=> $this->UA,
            'cts'=> $this->cts,
            'suspend'=> $this->suspend
            ];
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
    
    
    public function getAsArray() : array
    {
        if ($this->number)
            return [
                'number'=> $this->number,
                'code'=> $this->code,
                'date_requested'=> $this->rts,
                'date_activated'=> $this->ats,
                'delivered'=> $this->dlvrd,
                'sms_count'=> $this->sc,
                'flag'=> $this->flag,
                'secret'=> $this->secret
            ];
        else
            return [];
    }
    
    
    public function getNumber() : int
    {
        return $this->number ? $this->number : 0;
    }


    public function getSecret() : string
    {
        return $this->secret ? $this->secret : '';
    }

    
    public function isVerified() : bool
    {
        //error_log("verified: <".($this->number && ($this->ats+31556926)>time()).">");

        return ($this->number && ($this->ats+31556926)>time());
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
    protected $cuid;
    

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
            if (isset($as_array[ASD\USER_DEVICE_CARRIER_COUNTRY]))
            {
                $this->ccc = $as_array[ASD\USER_DEVICE_CARRIER_COUNTRY];
            }
            if (isset($as_array[ASD\USER_DEVICE_ISO_COUNTRY]))
            {
                $this->ccc = $as_array[ASD\USER_DEVICE_ISO_COUNTRY];
            }
            
            
            $this->iav = $as_array['app_version'];
            if (isset($as_array[ASD\USER_DEVICE_APP_PREFERENCES]))
            {
                $this->prefs = $as_array[ASD\USER_DEVICE_APP_PREFERENCES];
            }
            if (isset($as_array[ASD\USER_DEVICE_APP_SETTINGS]))
            {
                $this->prefs = $as_array[ASD\USER_DEVICE_APP_SETTINGS];
            }

            $this->rmvd = isset($as_array[ASD\USER_DEVICE_UNINSTALLED]) ? $as_array[ASD\USER_DEVICE_UNINSTALLED] : 0;
            $this->dats = $as_array['date_added'];
            if (isset($as_array[ASD\USER_DEVICE_PURCHASE_ENABLED]))
            {
                $this->pa = $as_array[ASD\USER_DEVICE_PURCHASE_ENABLED];
            }
            if (isset($as_array[ASD\USER_DEVICE_BAN_TRANSACTIONS]))
            {
                $this->pa = $as_array[ASD\USER_DEVICE_BAN_TRANSACTIONS] ? 0 : 1;
            }
        }
    }
    
    
    public function getAsArray() : array
    {
        return [
            'uuid'=> $this->uuid,
            'model'=> $this->model,
            'name'=> $this->name,
            'sys_name'=> $this->sn,
            'sys_version'=> $this->sv,
            'last_visited'=> $this->lvts,
            'token'=> $this->tk,
            'push_enabled'=> $this->pn,
            'carrier_country'=> $this->ccc,
            'app_version'=> $this->iav,
            'app_preferences'=> $this->prefs,
            'removed'=> $this->rmvd,
            'date_added'=> $this->dats,
            'purchase_enabled'=> $this->pa
        ];
    }

    
    public function getUUID() : string
    {
        return $this->uuid;
    }
    
    
    public function getLastVisitedUnixtime() : int
    {
        return $this->lvts;
    }


    public function getPreferences() : array
    {
        if ($this->prefs)
        {
            if ($this->prefs[0]=='{')
                return json_decode($this->prefs, true);
            else
                return json_decode(base64_decode($this->prefs), true);
        }
        
        return [];
    }
    
    
    public function isPurchaseEnabled() : int
    {
        return $this->pa ? $this->pa : 1;
    }
    
    
    public function getToken() : string
    {
        return $this->tk;
    }


    public function getAppVersion() : string
    {
        return $this->iav;
    }
    
    
    public function getChangedToUID() : int
    {
        return $this->cuid ? $this->cuid : 0;
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
        if (isset($array['phones']) && is_array($array['phones']))
        {
            foreach ($array['phones'] as $phone)
            {
                $pc = new MCPhoneData();
                $pc->parseAssoc($phone);
                $this->p[] = $pc;
            }
        }
        $this->b = $array['blackberry'];
        $this->e = $array['email'];
        $this->s = $array['skype'];
        $this->t = $array['twiter'];
    }
    
    public function getAsArray() : array
    {
        $pp=[];
        foreach ($this->p as $pc) {
            $pp[]=$pc->getAsArray();
            
        }
        return [
            'phones'=> $pp,
            'blackberry'=> $this->b,
            'email'=> $this->e,
            'skype'=> $this->s,
            'twiter'=> $this->t
        ];
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
    
    public function getAsArray() : array
    {
        return [
            'type'=> $this->t,
            'before'=> $this->b,
            'after'=> $this->a
        ];
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
    
    public function getAsArray() : array
    {
        return [
            'humain'=> $this->v,
            'type'=> $this->t,
            'country_key'=> $this->c,
            'raw_input'=> $this->r,
            'country_iso'=> $this->i
        ];
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
