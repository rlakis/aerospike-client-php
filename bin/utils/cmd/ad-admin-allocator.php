<?php

include_once __DIR__.'/../../../config/cfg.php';
include_once __DIR__.'/../../../deps/autoload.php';
Config::instance()->incModelFile('Router')->incModelFile('Db')->incModelFile('Classifieds')
        ->incLibFile('MCUser')->incLibFile('IPQuality')->incModelFile('NoSQL')->incModelFile('User');

$u=new User(null, 0);
$sessions=[];
$where=\Aerospike::predicateBetween(Core\Model\ASD\USER_UID, 1, 9999999);

$status=\Core\Model\NoSQL::instance()->getConnection()->query(
        \Core\Model\NoSQL::NS_CACHE, "sessions", $where, 
        function ($_record) use (&$sessions) {
            $data=unserialize_session($_record['bins']['PHP_SESSION']);
            $info=$data['_u']['info']??[];            
            if (($info['level']??0)===9) {
                $last_pending_epoch=$info['lft']??-1;
                //echo $info['lft']??-1,"\n";
                $age=28800-($_record['metadata']['ttl']??0);
                if ($age<300 && $last_pending_epoch!==-1) {
                    $sessions[$info['id']]=['rank'=>$info['rank'], 'level'=>$info['level'], 'seconds'=>time()-$last_pending_epoch, 'email'=>$info['email']??'', 'ads'=>[]];
                    //$sessions[]
                }
            }
        }, ['PHP_SESSION', "uid"]);
        
if ($status!==\Aerospike::OK) exit (-1);

//print_r($sessions);

$pool=new AdminPool;
$admins=[];
foreach ($sessions as $id => $data) {
    $u->info['id']=$id;
    if ($u->isSuperUser()) {
        $sessions[$id]['type']=2;
        $pool->super[]=$id;
        continue;
    }
    else if ($u->isAdvancedUser()) {
        $sessions[$id]['type']=1;
        $pool->advanced[]=$id;
        $pool->regular[]=$id;
        continue;
    }
    $sessions[$id]['type']=0;
    $admins[]=$id;
    $pool->regular[]=$id;
}

$db=new Core\Model\DB;
$rs=$db->get("select AD_USER.ID, AD_OBJECT.SUPER_ADMIN 
from ad_user
left join AD_OBJECT on AD_OBJECT.ID=AD_USER.ID 
where state=1 or state=4", null, true, \PDO::FETCH_NUM);

if (!empty($admins)) {
    foreach ($rs as $ad) {
        if ($pool->isAllocated($ad[0])) {  continue;  }
        if ($ad[1]===0) {
            $i=$pool->next();
            if ($i!==-1) {
                $sessions[$pool->regular[$i]]['ads'][]=$ad[0];
            }
        }
        else {
            $i=$pool->nextAdvanced();
             if ($i!==-1) {
                $sessions[$pool->advanced[$i]]['ads'][]=$ad[0];
            }
            echo $ad[0], ' sent to admin code: ', $ad[1], "\n";
            
        }
    }
}
print_r($sessions);
//print_r($pool);


function unserialize_session($session_data, $start_index=0, &$dict=null) {
   isset($dict) or $dict = array();

   $name_end = strpos($session_data, '|', $start_index);

   if ($name_end !== FALSE) {
       $name = substr($session_data, $start_index, $name_end - $start_index);
       $rest = substr($session_data, $name_end + 1);

       $value = unserialize($rest);      // PHP will unserialize up to "|" delimiter.
       $dict[$name] = $value;

       return unserialize_session($session_data, $name_end + 1 + strlen(serialize($value)), $dict);
   }

   return $dict;
}


class AdminPool {
    private \Redis $redis;
    private int $currentAdvanced=-1;
    private int $currentAdmin=-1;
    
    public array $super=[];
    public array $advanced=[];
    public array $regular=[];
    
    
    public function __construct() {
        $this->redis=$redis=new Redis;
        $redis->connect("p1.mourjan.com", 6379, 1, NULL, 100);
        $redis->select(5);
    }
    
    
    public function nextAdvanced() : int {
        if (empty($this->advanced)) return -1;
        $this->currentAdvanced++;
        if ($this->currentAdvanced===\count($this->advanced)) $this->currentAdvanced=0;
        return $this->currentAdvanced;
    }
    
    
    public function next() : int {
        if (empty($this->regular)) return -1;
        $this->currentAdmin++;
        if ($this->currentAdmin===\count($this->regular)) $this->currentAdmin=0;
        return $this->currentAdmin;
    }
    
    
    public function isAllocated(int $adId) : bool {
        $ad=$this->redis->mGet(array('AD-'.$adId));
        if (\is_array($ad)) {
            return true;
        }
        return false;
    }
}