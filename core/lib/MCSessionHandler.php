<?php
include_once dirname(__DIR__) . '/model/Singleton.php';

class MCSessionHandler extends \Core\Model\Singleton implements \SessionHandlerInterface {
    private object $storage;
    const TTL=28800;
    
    const SW_OPTIONS=[
        Aerospike::OPT_POLICY_COMMIT_LEVEL=>Aerospike::POLICY_COMMIT_LEVEL_MASTER,        
        Aerospike::OPT_POLICY_RETRY=>Aerospike::POLICY_RETRY_ONCE,
        Aerospike::OPT_WRITE_TIMEOUT=>2000,
        Aerospike::OPT_MAX_RETRIES=>3,
        Aerospike::OPT_SLEEP_BETWEEN_RETRIES=>250,
        Aerospike::OPT_SERIALIZER=>Aerospike::SERIALIZER_NONE,
        Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_IGNORE
    ];

    
    public static function instance() : MCSessionHandler {
        return static::getInstance();
    }
    
    
    protected function __construct() {        
        \session_set_save_handler($this, true);
        \session_start();
    }

    
    public static function checkSuspendedMobile(int $number, ?string &$reason='', ?string &$score='') : int {
        $as=\Core\Model\NoSQL::instance();
        $pk=$as->initLongKey(\Core\Model\NoSQL::NS_CACHE, "suspended", $number);
        if ($as->getConnection()->get($pk, $record, NULL)===Aerospike::OK) {
            $reason=$record['bins']['en'];
            $score=$record['bins']['summary']??'';
            return $record['metadata']['ttl'];
        }
        return 0;
    }

    
    public static function setSuspendMobile(int $uid, int $number, int $secondsToSuspend, bool $clearLog=false, string $reason='') : bool {
        $as=\Core\Model\NoSQL::instance();
        $pk=$as->initLongKey(\Core\Model\NoSQL::NS_CACHE, "suspended", $number);
        $options=MCSessionHandler::SW_OPTIONS;
        $options[Aerospike::OPT_POLICY_KEY]=Aerospike::POLICY_KEY_SEND;
        if ($as->getConnection()->put($pk, ['en'=>$reason ? $reason : "not specified", 'ar'=>$reason ? $reason : "not specified"], $secondsToSuspend, $options)===Aerospike::OK) {
            if ($clearLog) {
                $where=\Aerospike::predicateEquals('uid', $uid);
                $status=$as->getConnection()->query(\Core\Model\NoSQL::NS_CACHE, "activities", $where, function ($record) use ($as) {            
                    $ak=$as->getConnection()->initKey($record['key']['ns'], $record['key']['set'], $record['key']['digest'], true);               
                    $as->getConnection()->remove($ak);
                }, ['mobile']);
            }
            
            $redisPublisher=new Redis; 
            if ($redisPublisher->connect('p1.mourjan.com', 6379, 2, NULL, 20)) {
                $redisPublisher->publish('FBEventManager','{"event":"cache","action":"suspend","id":'.$uid.'}');
            }
            
            return true;
        }
        return false;      
    }

    
    private function as_key(string $id) : array {
        return $this->storage->initKey("mccache", "sessions", $id);
    }

    
    public function open($savePath, $sessionName) : bool {
        $this->storage=\Core\Model\NoSQL::instance()->getConnection();
        if ($this->storage->isConnected()) {
            return true;
        } 
        \error_log(__CLASS__ . '.' .__FUNCTION__.PHP_EOL."Failed to connect to the Aerospike server [" . $this->storage->errorno() . "]: " . $this->storage->error());            
        return false;
    }

    
    public function close() {
        return true;
    }
    
    
    public function read($id) {
        $status = $this->storage->get($this->as_key($id) , $record);                     
        if ($status===\Aerospike::OK && isset($record['bins'])) {
            return $record['bins']['PHP_SESSION'] ?? '';
        }
        return '';                
    }

    
    public function write($id, $data) {        
        $key=$this->as_key($id);
        $max_retries=2;
        while ($max_retries>0) {
            if ($this->storage->put($key, ["PHP_SESSION" => $data], self::TTL, self::SW_OPTIONS)===\Aerospike::OK) {
                return TRUE;
            }
           
            $max_retries--;
            //$this->storage=\Core\Model\NoSQL::instance()->getConnection();
        }
        \error_log(__FUNCTION__." Session {$id} write error [{$this->storage->errorno()}] ".$this->storage->error().\PHP_EOL);
        return true;
    }

    
    public function destroy($id) {
        $ret=($this->storage->remove($this->as_key($id))===\Aerospike::OK);
        \error_log(__FUNCTION__. " new site ".__FUNCTION__. " ".$id." returned ".$ret);
        return $ret;
    }

    
    public function gc($maxlifetime) {
        return true;
    }
    
}
