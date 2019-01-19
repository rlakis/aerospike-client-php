<?php
namespace Core\Lib;

class MCCache extends \Redis {
    private $buffer;
    private $writeBuffering;
    
    
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
            parent::ping();
            parent::mSet($this->buffer);
        }
        
        if ($this->isConnected()) {
            parent::close();
        }

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
        parent::expire($key, $ttl);
    }


    function set($key, $value, $buffer_it=TRUE) : bool {
        if ($this->writeBuffering && $buffer_it) {
            $this->buffer[$key] = $value;
            return TRUE;
        } 
        else {
            return parent::set($key, $value);
        }
    }

        
}