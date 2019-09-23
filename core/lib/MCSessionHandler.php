<?php
include_once dirname(__DIR__) . '/model/Singleton.php';

class MCSessionHandler extends Singleton implements \SessionHandlerInterface {
    const SESSION_PREFIX = 'ms_';
    const FULL_CACHE = 1;
    const AEROSPIKE_STORAGE = 1;

    private $savePath;
    private $storage;
    private $ttl;
    private $shared;
    private $version;
    
    public static function instance() : MCSessionHandler {
        return static::getInstance();
    }
    
    
    protected function __construct() {        
        $this->version = phpversion("aerospike");
        $this->shared = true;
        $this->ttl = intval(get_cfg_var("session.gc_maxlifetime"), 10);
        session_set_save_handler($this, TRUE);
        session_start();
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
        $redis = new Redis();
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $pass = $redis->set($number, $reason ? $reason : "not specified", $secondsToSuspend);
            if ($pass) {               
                if ($clearLog) {
                    $redis->setOption(Redis::OPT_PREFIX, 'ua_');
                    $redis->delete($number);                    
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
    
    
    private function initAerospike() {
        $configuration = ["hosts" => [["addr"=>"138.201.28.229", "port"=>3000], ["addr"=>"88.99.164.79", "port"=>3000]]];
        $options = [\Aerospike::OPT_READ_TIMEOUT => 1500,
                    \Aerospike::OPT_WRITE_TIMEOUT => 2000,
                    \Aerospike::OPT_POLICY_KEY => \Aerospike::POLICY_KEY_SEND,
                    \Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE];

        $this->storage = new \Aerospike($configuration, TRUE, $options);

        if ($this->storage->isConnected()) {
            return TRUE;
        } 
        else {
            error_log(__CLASS__ . '.' .__FUNCTION__.PHP_EOL."Failed to connect to the Aerospike server [" . $this->storage->errorno() . "]: " . $this->storage->error());
            if (MCSessionHandler::FULL_CACHE!=1) {
                $this->shared = FALSE;
                $this->storage = NULL;
                return TRUE;
            }
        }
        return FALSE;
    }


    private function initRedis() {
        try {
            $this->storage = new Redis();

            if ($this->storage->connect('138.201.28.229', 6379, 2, NULL, 20)) {
                $this->storage->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $this->storage->setOption(Redis::OPT_PREFIX, self::SESSION_PREFIX);
                $this->storage->setOption(Redis::OPT_READ_TIMEOUT, 10);
                return TRUE;
            }
            else {
                error_log("Could not connect to redis session store! " . $this->storage->getLastError(). '?!?!');
                if (MCSessionHandler::FULL_CACHE!=1) {
                    $this->shared = FALSE;
                    $this->storage = NULL;
                    return TRUE;
                }
            }

        }
        catch (RedisException $re) {
            error_log(__CLASS__.'.'.__FUNCTION__. PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
            if (MCSessionHandler::FULL_CACHE!=1) {
                $this->shared = FALSE;
                $this->storage = NULL;
                return TRUE;
            }
        }
        return FALSE;
    }


    private function openMem() {
        return (self::AEROSPIKE_STORAGE) ? $this->initAerospike() : $this->initRedis();
    }

    
    public function open($savePath, $sessionName) {         
        $this->savePath = $savePath;
        if (MCSessionHandler::FULL_CACHE===0) {
            if (!is_dir($this->savePath)) {
                mkdir($this->savePath, 0777);
            }

            if ($this->shared) {                
                $this->openMem();
            } 
            else if (isset($_COOKIE['mourjan_user'])) {
                $cook = json_decode($_COOKIE['mourjan_user']);
                if (is_object($cook) && isset($cook->mu)) {
                    $this->shared = true;
                    return $this->openMem();
                }
            }
        } 
        else {
            return $this->openMem();
        }

        return true;
    }

    
    public function close() {
    	if ($this->storage) {
            $this->storage->close();
            unset($this->storage);
        }
        return true;
    }

    
    private function as_key($id) {
        return $this->storage->initKey("users", "sessions", $id);
    }

    
    public function read($id) {
        if (MCSessionHandler::FULL_CACHE) {
            if (MCSessionHandler::AEROSPIKE_STORAGE) {                
                $status = $this->storage->get($this->as_key($id), $record);                     
                if ($status==\Aerospike::OK && isset($record['bins'])) {                    
                    return $record['bins']['PHP_SESSION'] ?? '';
                }
                return '';
            }
            else {
                $this->storage->expire($id, $this->ttl);
                return $this->storage->get($id) ? : '';
            }
                
        }
        
        $sess_file = $this->savePath . '/' . self::SESSION_PREFIX . $id;
        if ($this->storage && $this->storage->IsConnected()) {            
            if (file_exists($sess_file)) {
                $data = (string)@file_get_contents($sess_file);
                if ($this->storage->setex($id, $this->ttl, $data) === TRUE) {
                    error_log("session {$id} forwarded from hard disk to redis");
                    @unlink($sess_file);
                } 
                else {
                    $this->shared = false;
                }

                return $data;
            }
            
            $this->storage->expire($id, $this->ttl);
            return $this->storage->get($id) ? : '';
        } 
        else {
            error_log("session {$id} read from hard disk");
            return (string)@file_get_contents($sess_file);
        }
    }

    
    public function write($id, $data) {        
        if (MCSessionHandler::FULL_CACHE) {
            if (MCSessionHandler::AEROSPIKE_STORAGE) {
                $key = $this->as_key($id);
                $options = [\Aerospike::OPT_POLICY_EXISTS=>\Aerospike::POLICY_EXISTS_IGNORE,
                        \Aerospike::OPT_WRITE_TIMEOUT=>5000];
                if ($this->version=='7.2.0') {
                    $options[\Aerospike::OPT_MAX_RETRIES]=2;
                }
                else {
                    $options[\Aerospike::OPT_POLICY_RETRY]=\Aerospike::POLICY_RETRY_ONCE;
                }
                
                if ($this->storage->put($key, ["PHP_SESSION" => $data], $this->ttl, $options) == \Aerospike::OK) {
                    return TRUE;
                }
                
                error_log("Session {$id} write error [{$this->storage->errorno()}] ".$this->storage->error());
                return FALSE;
            }
            
            return $this->storage->setex($id, $this->ttl, $data);
        }
        
        $success = false;
        if ($this->storage && $this->shared) {
            if ($this->storage->setex($id, $this->ttl, $data)) {
                $this->storage->expire($id, $this->ttl);
                $success = true;
            }
        }
        
        if (!$success) {
            error_log("session write from hard disk");
            return file_put_contents($this->savePath.'/'.self::SESSION_PREFIX.$id, $data) === false ? false : true;
        }
        
        return true;
    }

    
    public function destroy($id) {
        if (MCSessionHandler::FULL_CACHE==0) {
            $file = $this->savePath . '/' . self::SESSION_PREFIX . $id;
            if (file_exists($file)) { @unlink($file); }
        }
        else {
            if (MCSessionHandler::AEROSPIKE_STORAGE) {
                $status = $this->storage->remove($this->as_key($id));
                return ($status == \Aerospike::OK);
            }
            else {
                $this->storage->delete($id);
            }
        }
        
        return true;
    }

    
    public function gc($maxlifetime) {
        if (MCSessionHandler::FULL_CACHE==0) {
            foreach (glob($this->savePath.'/' . self::SESSION_PREFIX . '*') as $file) {
                if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                    @unlink($file);
                }
            }
        }
        
        return true;
    }
    
}
