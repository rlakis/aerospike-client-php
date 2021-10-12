<?php
\ini_set('display_errors', 1);
\ini_set('error_reporting', \E_ALL);

if (\php_sapi_name()!=='cli' || $argc!==2 || !\file_exists($argv[1])) { exit (0); }

include_once dirname(__DIR__).'/config/cfg.php';
include_once dirname(__DIR__).'/deps/autoload.php';


$path=\pathinfo($argv[1]);

if ($path['extension']==='ip') {
    //exec('/usr/bin/firewall-cmd  --zone=block --list-sources', $lines);
    \exec('/usr/bin/fail2ban-client get mourjan banip', $lines);
   
    foreach ($lines as $line) {
        $ips=\explode(' ', $line);
        foreach ($ips as $addr) {
            $blocked[$addr]=1;
        }
    }
    $range=\str_replace("-", "/", $path['filename']);
    if (\str_ends_with($range, "/24")) {
        $range=\preg_replace('/\\.\\d+\\/24$/', '.0/24', $range);
    }
    
    if (!isset($blocked[$range])) {
        //system("/usr/bin/firewall-cmd --zone=block --add-source={$path['filename']}");
        \system("/usr/bin/sudo /usr/bin/fail2ban-client set mourjan banip {$range}");
        //syslog(LOG_INFO, "/usr/bin/firewall-cmd --zone=block --add-source={$path['filename']}");
        \syslog(\LOG_INFO, "/usr/bin/sudo /usr/bin/fail2ban-client set mourjan banip {$range}");
    }
    
    \system("mv {$argv[1]} /tmp/");
    exit(0);    
}


Config::instance()->incModelFile('Db')->incModelFile('Classifieds');

if ($path['extension']==='tsv') {
    $redis=new Redis;
    try {
        $redis->connect($config['rs-host'], $config['rs-port'], 1, NULL, 100);
    } 
    catch (Exception $ex) {
        echo $ex->getMessage(), "\n", $ex->getTraceAsString(), "\n";
    }
    exit(0);
}

if ($path['extension']==='stx') {
    $ds=[];
    try {
        $fp=\fopen($argv[1], 'r');
        if (\is_resource($fp)) {
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
            }
        }        
    } 
    catch (Exception $ex) {
         \syslog(\LOG_INFO, "Fail to process file {$argv[1]}");
    }
    finally {
        if (isset($fp) && \is_resource($fp)) {  fclose($fp);  }
    }
    
    if (\count($ds)>0) {
        $redis=new Redis;
        //echo Config::instance()->get('rs-host'), ":", Config::instance()->get('rs-port'), "\t", Config::instance()->get('rs-prefix'), "\t",  Config::instance()->get('rs-index'), "\n";
        try {
            $redis->connect(Config::instance()->get('rs-host'), Config::instance()->get('rs-port'), 1, NULL, 100);
            $redis->setOption(Redis::OPT_PREFIX, Config::instance()->get('rs-prefix'));
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);
            $redis->select( Config::instance()->get('rs-index') );
    
            $db=new Core\Model\DB(true);
            $classifieds=new Core\Model\Classifieds($db);
            foreach ($ds as $record) {
                $ad=$classifieds->getById($record->id);
                if (($ad[Core\Model\Classifieds::USER_ID]??0)>0) {
                    $key=($record->isImpression()?'AI':'AC').$record->id;
                    //echo $ad[Core\Model\Classifieds::USER_ID], ' ', $record->id, ' ', $key, PHP_EOL;
                    if ($redis->isConnected()) {        
                        $redis->sAdd('U'.$ad[Core\Model\Classifieds::USER_ID], $record->id);
                        $redis->hIncrBy($key, $record->date, 1);
                    }
                    else {
                        echo "Redis is not connected";
                    }
                }
            }
            $db->close();
            
            unlink($argv[1]);
        }
        catch (RedisException $e) {
            echo $e->getMessage(), "\n";
        }
    }
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