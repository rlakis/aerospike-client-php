<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

if (php_sapi_name()!=='cli' || $argc!==2 || !file_exists($argv[1])) { exit (0); }

include_once dirname(__DIR__).'/config/cfg.php';
include_once dirname(__DIR__).'/deps/autoload.php';

if (1) exit(0);
$path=\pathinfo($argv[1]);
if ($path['extension']==='tsv') {
    Config::instance()->incModelFile('Db')->incModelFile('Classifieds');
    $redis=new Redis;
    try {
        $redis->connect($config['rs-host'], $config['rs-port'], 1, NULL, 100);
    } 
    catch (Exception $ex) {
        echo $ex->getMessage(), "\n", $ex->getTraceAsString(), "\n";
    }
    
}


include_once get_cfg_var('mourjan.path').'/deps/autoload.php';
include_once get_cfg_var('mourjan.path').'/config/cfg.php';
include_once $config['dir'].'/core/lib/MCCache.php';
include_once $config['dir'].'/core/lib/SphinxQL.php';
include_once $config['dir'].'/core/model/Db.php';
include_once $config['dir'].'/core/model/Classifieds.php';

$path=\pathinfo($argv[1]);
if ($path['extension']==='ip') {
    //exec('/usr/bin/firewall-cmd  --zone=block --list-sources', $lines);
    exec('/usr/bin/fail2ban-client get mourjan banip', $lines);
   
    foreach ($lines as $line) {
        $ips=explode(' ', $line);
        foreach ($ips as $addr) {
            $blocked[$addr]=1;
        }
    }
    if (!isset($blocked[$path['filename']])) {
        //system("/usr/bin/firewall-cmd --zone=block --add-source={$path['filename']}");
        system("/usr/bin/sudo /usr/bin/fail2ban-client set mourjan banip {$path['filename']}");
        //syslog(LOG_INFO, "/usr/bin/firewall-cmd --zone=block --add-source={$path['filename']}");
        syslog(LOG_INFO, "/usr/bin/sudo /usr/bin/fail2ban-client set mourjan banip {$path['filename']}");
    }
    system("mv {$argv[1]} /tmp/");
    exit(0);    
}

$fp;
$redisOK=0;
$redis=new Redis;
try {
    $redis->connect($config['rs-host'], $config['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
    $redis->setOption(Redis::OPT_PREFIX, $config['rs-prefix']);
    $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);
    $redis->select($config['rs-index']);
    $redisOK=1;
} 
catch (RedisException $e) {
    echo $e->getMessage(), "\n";
}

if ($redisOK!==1) {  exit(0);  }

$mccache=new Core\Lib\MCCache($config);
$stat_servers=$mccache->get("sphinx_servers");
if (!is_array($stat_servers)) {  exit(0);  }

$cluster=[];
try {
    foreach ($stat_servers as $server) {
        if ($server['active']===1) {
            $cluster[]=new \Core\Lib\SphinxQL($server, $config['search_index']);
        } 
    }
}
catch (Exception $ex) {
    echo $ex->getTraceAsString(), PHP_EOL;
    exit(0);
}

$ds=[];
$batch='';
try {
    $fp=\fopen($argv[1], 'r');
    if (!\is_resource($fp)) {
        exit(0);
    }
    while ( !feof($fp) ) {
        $line=fgets($fp, 2048);
        $data=str_getcsv($line, "\t");
        if (count($data)<4) { continue; }
        //var_dump($data);
        
        $record=new Record;
        $record->id=intval($data[2]);
        $record->date=trim(substr($data[0], 0, 10));
        $record->country=$data[3];
        $record->url=$data[4]??'';
        $record->type=$data[1];
        $ds[]=$record;
        if ($record->isImpression()) {
            $batch.="select id, impressions from {$config['search_index']} where id={$record->id};\n"; 
        }
    }
    fclose($fp);
    $fp=null;
        
    $qlCount=0;
    if (!empty($batch)) {
        foreach ($cluster as $ql) {
            $result=$ql->search($batch);
            foreach($result['matches'] as $row) {
                if (isset($row[0])) { $row=$row[0]; }
                if (!isset($row['id'])) { continue; }
                                    
                $id=$row['id']+0;
                $im=$row['impressions']+1;                                                                                        
                //$ql->getConnection()->real_query("update {$config['search_index']} set impressions={$im} where id={$id}");
            }
            $ql->close();
            $qlCount+=1;
        }
    }
    
    if (empty($batch)||$qlCount===count($cluster)) {
        $db=new \Core\Model\DB($config);
        foreach ($ds as $record) {
            $classifieds=new Core\Model\Classifieds($db);
            $ad=$classifieds->getById($record->id);
            if (isset($ad[Core\Model\Classifieds::USER_ID]) && $ad[Core\Model\Classifieds::USER_ID]>0) {                                                    
                if ($redis->isConnected()) {        
                    $redis->sAdd('U'.$ad[Core\Model\Classifieds::USER_ID], $record->id);
                    $key=($record->isImpression()?'AI':'AC').$record->id;
                    //echo $ad[Core\Model\Classifieds::USER_ID], ' ', $record->id, ' ', $key, PHP_EOL;
                    $redis->hIncrBy($key, $record->date, 1);
                }
            }
        }
        unlink($argv[1]);
    }
    
} 
catch (Exception $ex) {
    echo $ex->getTraceAsString(), PHP_EOL;
}
finally {
    if ($fp) {  fclose($fp);  }
    $redis->close();
}

class Record {
    public int $id;
    public $date;
    public string $country;
    public string $url;
    public string $type;
    
    public function isImpression() : bool {
        return $this->type==='ad-imp';        
    }
}