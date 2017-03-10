<?php
if (ini_get('session.save_handler')!='aerospike')
{
    ini_set('session.save_handler', 'aerospike');
    ini_set('session.save_path', 'users|sessions|148.251.184.77:3000,138.201.28.229:3000');
}

if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}


class MCSessionHandler
{
    function __construct($autoStart=TRUE)
    {

        if ($autoStart)
        {
            //session_start();
        }        
    }
    
    public static function checkSuspendedMobile($number)
    {   
        $redis = new Redis();
            
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
        {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $ttl = $redis->ttl($number);            
            if($ttl<0){
                $ttl=0;
            }
        }
        return $ttl;
    }
    
    public static function setSuspendMobile($uid, $number, $secondsToSuspend)
    {   
        $pass = false;
        $redis = new Redis();
            
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
        {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $pass = $redis->set($number, 1, $secondsToSuspend);
            
            if($pass){
                $redisPublisher = new Redis();
                if($redisPublisher->connect('p1.mourjan.com',6379,2,NULL,20)){
                    $redisPublisher->publish('FBEventManager','{"event":"cache","action":"suspend","id":'.$uid.'}');
                }
            }
        }
        return $pass;
    }

    
    public static function getUser($user_id)
    {
        $user= json_decode('{}');
        try 
        {
            $timer= -microtime(true);
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'mu_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
                
                $user = json_decode($redis->get($user_id) ? : '{}');
                $timer+= microtime(true);
                //var_dump($timer*1000.0);
                if (!isset($user->id))
                {
                    $redisPublisher = new Redis();
                    if ($redisPublisher->connect('p1.mourjan.com', 6379, 2, NULL, 20))
                    {
                        $redisPublisher->publish('FBEventManager','{"event":"cache","action":"user","id":'.$user_id.'}');
                        $time = 0;
                        do
                        {
                            usleep(500);
                            $time+=500;
                            $user = json_decode($redis->get($user_id) ? : '{}');
                        } while ($time < 2000000 && !isset($user->id));
                    }
                }
                
                if (isset($user->id) && isset($user->mobile->number) && $user->mobile->number)
                {
                    $redis->setOption(Redis::OPT_PREFIX, 'mm_');
                    $suspended = $redis->ttl($user->mobile->number);
                    if ($suspended<0)
                    {
                        $suspended=0;
                    }
                    else
                    {
                        $suspended = time()+$suspended;
                    }
                    
                    $user->opts->suspend = $suspended+0;
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
        return $user;
    }
}

/*
class MCSessionHandler implements \SessionHandlerInterface 
{
    const SESSION_PREFIX = 'ms_';
    const FULL_REDIS = 1;
    
    private $savePath;
    private $storage;
    private $ttl;
    private $shared;
    
    function __construct($useMemcached=TRUE, $autoStart=TRUE)
    {
        $this->shared = $useMemcached;
        $this->ttl = intval(get_cfg_var("session.gc_maxlifetime"), 10);
        session_set_save_handler($this, TRUE);
        if ($autoStart)
        {
            session_start();
        }
    }
    
    public static function checkSuspendedMobile($number)
    {   
        $redis = new Redis();
            
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
        {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $ttl = $redis->ttl($number);            
            if($ttl<0){
                $ttl=0;
            }
        }
        return $ttl;
    }
    
    public static function setSuspendMobile($uid, $number, $secondsToSuspend)
    {   
        $pass = false;
        $redis = new Redis();
            
        if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
        {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $redis->setOption(Redis::OPT_PREFIX, 'mm_');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
            
            $pass = $redis->set($number, 1, $secondsToSuspend);
            
            if($pass){
                $redisPublisher = new Redis();
                if($redisPublisher->connect('p1.mourjan.com',6379,2,NULL,20)){
                    $redisPublisher->publish('FBEventManager','{"event":"cache","action":"suspend","id":'.$uid.'}');
                }
            }
        }
        return $pass;
    }

    
    public static function getUser($user_id)
    {
        $user= json_decode('{}');
        try 
        {
            $timer= -microtime(true);
            $redis = new Redis();
            
            if ($redis->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                $redis->setOption(Redis::OPT_PREFIX, 'mu_');
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 10);
                
                $user = json_decode($redis->get($user_id) ? : '{}');
                $timer+= microtime(true);
                //var_dump($timer*1000.0);
                if (!isset($user->id))
                {
                    $redisPublisher = new Redis();
                    if ($redisPublisher->connect('p1.mourjan.com', 6379, 2, NULL, 20))
                    {
                        $redisPublisher->publish('FBEventManager','{"event":"cache","action":"user","id":'.$user_id.'}');
                        $time = 0;
                        do
                        {
                            usleep(500);
                            $time+=500;
                            $user = json_decode($redis->get($user_id) ? : '{}');
                        } while ($time < 2000000 && !isset($user->id));
                    }
                }
                
                if (isset($user->id) && isset($user->mobile->number) && $user->mobile->number)
                {
                    $redis->setOption(Redis::OPT_PREFIX, 'mm_');
                    $suspended = $redis->ttl($user->mobile->number);
                    if ($suspended<0)
                    {
                        $suspended=0;
                    }
                    else
                    {
                        $suspended = time()+$suspended;
                    }
                    
                    $user->opts->suspend = $suspended+0;
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
        return $user;
    }
    
    
   
    
    private function openMem() 
    {
        try 
        {
            $this->storage = new Redis();
            	
            if ($this->storage->connect('138.201.28.229', 6379, 2, NULL, 20)) 
            {
                $this->storage->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $this->storage->setOption(Redis::OPT_PREFIX, self::SESSION_PREFIX);
                $this->storage->setOption(Redis::OPT_READ_TIMEOUT, 10);
            }             
            else 
            {
                error_log("Could not connect to redis session store! " . $this->storage->getLastError(). '?!?!');
                if (MCSessionHandler::FULL_REDIS!=1) 
                {
                    $this->shared = FALSE;
                    $this->storage = NULL;
                }
            }
            	
        } 
        catch (RedisException $re) 
        {           
            error_log(PHP_EOL . PHP_EOL . $re->getCode() . PHP_EOL . $re->getMessage() . PHP_EOL . $re->getTraceAsString() . PHP_EOL);
            if (MCSessionHandler::FULL_REDIS!=1) 
            {
                $this->shared = FALSE;
                $this->storage = NULL;
            }
        }
    }

    public function open($savePath, $sessionName) 
    {        
        $this->savePath = $savePath;

        if (MCSessionHandler::FULL_REDIS === 0) 
        {
            if (!is_dir($this->savePath)) 
            {
                mkdir($this->savePath, 0777);
            }

            if ($this->shared) 
            {
                $this->openMem();
            } 
            else if (isset($_COOKIE['mourjan_user'])) 
            {
                $cook = json_decode($_COOKIE['mourjan_user']);
                if (is_object($cook) && isset($cook->mu)) 
                {
                    $this->shared = true;
                    $this->openMem();
                }
            }
        } 
        else 
        {
            $this->openMem();
        }

        return true;
    }

    
    public function close() 
    {
    	if ($this->storage) 
        {
            $this->storage->close();
            unset($this->storage);
        }
        return true;
    }

    
    public function read($id) 
    {
        if (MCSessionHandler::FULL_REDIS)
        {
            $this->storage->expire($id, $this->ttl);
            return $this->storage->get($id) ? : '';    
        }
        
        $sess_file = $this->savePath . '/' . self::SESSION_PREFIX . $id;
        if ($this->storage && $this->storage->IsConnected()) 
        {            
            if (file_exists($sess_file)) 
            {
                $data = (string)@file_get_contents($sess_file);
                if ($this->storage->setex($id, $this->ttl, $data) === TRUE) 
                {
                    error_log("session {$id} forwarded from hard disk to redis");
                    @unlink($sess_file);
                } 
                else
                {
                    $this->shared = false;
                }

                return $data;
            }
            
            $this->storage->expire($id, $this->ttl);
            return $this->storage->get($id) ? : '';
        } 
        else 
        {
            error_log("session {$id} read from hard disk");
            return (string)@file_get_contents($sess_file);
        }
    }

    
    public function write($id, $data) 
    {
        if (MCSessionHandler::FULL_REDIS)
        {            
            return $this->storage->setex($id, $this->ttl, $data);
        }
        
        $success = false;
        if ($this->storage && $this->shared) 
        {
            if ($this->storage->setex($id, $this->ttl, $data)) 
            {
                $this->storage->expire($id, $this->ttl);
                $success = true;
            }
        }
        
        if (!$success)
        {
            error_log("session write from hard disk");
            return file_put_contents($this->savePath.'/'.self::SESSION_PREFIX.$id, $data) === false ? false : true;
        }
        
        return true;
    }

    
    public function destroy($id) 
    {
        if (MCSessionHandler::FULL_REDIS==0)
        {
            $file = $this->savePath . '/' . self::SESSION_PREFIX . $id;
            if (file_exists($file)) 
            {
                @unlink($file);
            }
        }
        else
        {
        	$this->storage->delete($id);
        }
        
        return true;
    }

    
    public function gc($maxlifetime) 
    {
        if (MCSessionHandler::FULL_REDIS==0) 
        {
            foreach (glob($this->savePath.'/' . self::SESSION_PREFIX . '*') as $file) {
                if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                    @unlink($file);
                }
            }
        }
        return true;
    }
    
}*/
