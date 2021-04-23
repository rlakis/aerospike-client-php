<?php

include_once __DIR__.'/../../../config/cfg.php';
include_once __DIR__.'/../../../deps/autoload.php';
Config::instance()->incModelFile('Router')->incModelFile('Db')->incModelFile('Classifieds')
        ->incLibFile('MCUser')->incLibFile('IPQuality')->incModelFile('NoSQL');

$sessions=[];
$where=\Aerospike::predicateBetween(Core\Model\ASD\USER_UID, 1, 9999999);

$status=\Core\Model\NoSQL::instance()->getConnection()->query(
        \Core\Model\NoSQL::NS_CACHE, "sessions", $where, 
        function ($_record) use (&$sessions) {
            $data=unserialize_session($_record['bins']['PHP_SESSION']);
            $info=$data['_u']['info']??[];            
            if (($info['level']??0)===9) {
                $last_pending_epoch=$info['lft']??-1;
                echo $info['lft']??-1,"\n";
                $age=28800-($_record['metadata']['ttl']??0);
                if ($age<300 && $last_pending_epoch!==-1) {
                    $sessions[$info['id']]=['rank'=>$info['rank'], 'level'=>$info['level'], 'seconds'=>time()-$last_pending_epoch, 'email'=>$info['email']??'', 'ads'=>[]];
                    //$sessions[]
                }
            }
        }, ['PHP_SESSION', "uid"]);
        
if ($status!== \Aerospike::OK) exit (-1);

print_r($sessions);


$db=new Core\Model\DB;
$rs=$db->get("select id from ad_user where state=1 or state=4", null, true, \PDO::FETCH_NUM);
print_r($rs);


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

