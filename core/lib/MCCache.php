<?php
namespace Core\Lib;

class MCCache extends \Redis {
    private $buffer;
    private $writeBuffering;
    
    private $master;
    
    function __construct() {
        parent::__construct();
        $this->buffer = [];
        $this->writeBuffering = TRUE;
        $memstore = \Config::instance()->get('memstore');
        
        if ($memstore['tcp']) {
            $success = $this->connect($memstore['host'], $memstore['port'], 1, NULL, 100);
        } 
        else {
            $success = $this->connect($memstore['socket']);           
        }

        if (!$success) {
            error_log("Could not connect to redis ". (($memstore['tcp']) ? $memstore['host']."/".$memstore['port'] : "unix socket " . $memstore['socket']));
        }
            
        $this->setOption(\Redis::OPT_SERIALIZER, $memstore['serializer']);
        $this->setOption(\Redis::OPT_PREFIX, $memstore['prefix']);
        $this->select($memstore['db']);
    }

    
    function __destruct() {
        if ($this->writeBuffering && !empty($this->buffer)) {
            $this->master()->ping();
            $this->master()->mSet($this->buffer);
        }
        
        if ($this->isConnected()) { parent::close(); }

        parent::__destruct();
    }

    
    public function setWriteBuffering($mode=TRUE) {
        $this->writeBuffering = $mode;        
    }

    
    public function getMulti($keys) {
        $ret = [];
        $values = $this->getMultiple($keys);
        $len = count($keys);
        for ($i=0; $i<$len; $i++) {
            if ($values[$i] !== FALSE) {
                $ret[$keys[$i]] = $values[$i];
            }
        }
        return $ret;
    }


    public function get($key) {
        if (isset($this->buffer[$key])) {
            return $this->buffer[$key];
        }
        return parent::get($key);
    }
    
    
    function touch($key, $ttl) {
        $this->master()->expire($key, $ttl);
    }


    function set($key, $value, $buffer_it=TRUE) : bool {
        if ($this->writeBuffering && $buffer_it) {
            $this->buffer[$key] = $value;
            return TRUE;
        } 
        else {
            return $this->master()->set($key, $value);
        }
    }

        
    function setEx($key, $expire, $value) : bool {
        if ($expire<=0) {
            return $this->master()->set($key, $value);
        }
        
        return $this->master()->setEx($key, $expire, $value);
    }

    
    private function master() : \Redis {
        if (\Config::instance()->serverId==1) {
            return parent;
        }
        
        if (!($this->master instanceof \Redis)) {
            $this->master = new \Redis();
        }
        
        if (!$this->master->isConnected()) {
            $success = $this->master->connect('p1.mourjan.com', 6379, 1, NULL, 100);
            if (!$success) {
                \error_log('Could not connect to redis p1.mourjan.com/6379');
            }
            else {
                $memstore = \Config::instance()->get('memstore');
                $this->master->setOption(\Redis::OPT_SERIALIZER, $memstore['serializer']);
                $this->master->setOption(\Redis::OPT_PREFIX, $memstore['prefix']);
                $this->master->select($memstore['db']);
            }
        }
        return $this->master;
    }
    
}