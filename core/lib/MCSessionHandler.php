<?php
include_once dirname(__DIR__) . '/model/Singleton.php';

class MCSessionHandler extends \Core\Model\Singleton implements \SessionHandlerInterface {
    private object $storage;
    private int $ttl;

    
    public static function instance() : MCSessionHandler {
        return static::getInstance();
    }
    
    
    protected function __construct() {        
        $this->ttl=\intval(\get_cfg_var("session.gc_maxlifetime"), 10);
        \session_set_save_handler($this, true);
        \session_start();
    }

    
    public static function checkSuspendedMobile($number, &$reason="") {   
        $redis = new Redis();
            
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $ttl = $redis->ttl($number);            
            if ($ttl<0) {
                $ttl=0;
            } 
            else {
                $reason = $redis->get($number);
            }
        }
        return $ttl;
    }

    
    public static function setSuspendMobile($uid, $number, $secondsToSuspend, $clearLog=false, string $reason='') : bool {   
        $pass = false;
        $redis = new \Redis();
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $pass = $redis->set($number, $reason ? $reason : "not specified", $secondsToSuspend);
            if ($pass) {               
                if ($clearLog) {
                    $redis->setOption(Redis::OPT_PREFIX, 'ua_');
                    $redis->unlink($number);                    
                }
                
                $redisPublisher = new Redis();                
                if ($redisPublisher->connect('p1.mourjan.com', 6379, 2, NULL, 20)) {
                    $redisPublisher->publish('FBEventManager','{"event":"cache","action":"suspend","id":'.$uid.'}');
                }
                                
            }
        }
        return $pass;
    }

    
    public static function getUser($user_id) {
        $user= json_decode('{}');
        try {
            $timer= -microtime(true);
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'mu_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
                
                $user = json_decode($redis->get($user_id) ? : '{}');
                $timer+= microtime(true);
                if (!isset($user->id)) {
                    $redisPublisher = new Redis();
                    if ($redisPublisher->connect('p1.mourjan.com', 6379, 2, NULL, 20)) {
                        $redisPublisher->publish('FBEventManager','{"event":"cache","action":"user","id":'.$user_id.'}');
                        $time = 0;
                        do {
                            usleep(500);
                            $time+=500;
                            $user = json_decode($redis->get($user_id) ? : '{}');
                        } while ($time < 2000000 && !isset($user->id));
                    }
                }
                
                if (isset($user->id) && isset($user->mobile->number) && $user->mobile->number) {
                    $redis->setOption(Redis::OPT_PREFIX, 'mm_');
                    $suspended = $redis->ttl($user->mobile->number);
                    if ($suspended<0) {
                        $suspended=0;
                    }
                    else {
                        $suspended = time()+$suspended;
                    }
                    
                    $user->opts->suspend = $suspended+0;
                }
            }
            else {
                error_log("Could not connect to redis user store! " . $redis->getLastError(). '?!?!');
            }            	
        }
        catch (RedisException $re) {           
            error_log(__CLASS__.'.'.__FUNCTION__. PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
        }
        finally {
            $redis->close();
        }
        return $user;
    }


    private function as_key($id) {
        return $this->storage->initKey("mccache", "sessions", $id);
    }

    
    public function open($savePath, $sessionName) : bool {
        $this->storage=\Core\Model\NoSQL::instance()->getConnection();
        if ($this->storage->isConnected()) {
            return true;
        } 
        else {
            \error_log(__CLASS__ . '.' .__FUNCTION__.PHP_EOL."Failed to connect to the Aerospike server [" . $this->storage->errorno() . "]: " . $this->storage->error());            
        }
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
        $options=[\Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_IGNORE, \Aerospike::OPT_WRITE_TIMEOUT=>5000, \Aerospike::OPT_MAX_RETRIES=>2];                
        if ($this->storage->put($key, ["PHP_SESSION" => $data], $this->ttl, $options)===\Aerospike::OK) {
            return TRUE;
        }                
        \error_log("Session {$id} write error [{$this->storage->errorno()}] ".$this->storage->error());
        return FALSE;
    }

    
    public function destroy($id) {
        \error_log("new site ".__FUNCTION__. " ".$id);
        return ($this->storage->remove($this->as_key($id))===\Aerospike::OK);
    }

    
    public function gc($maxlifetime) {
        return true;
    }
    
}
