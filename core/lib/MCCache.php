<?php

class MCCache extends Redis 
{
    
    private $buffer;
    private $writeBuffering;
    
    
    function __construct($conf) 
    {
        parent::__construct();
        $this->buffer = [];
        $this->writeBuffering = TRUE;
        if ($conf['memstore']['tcp']) 
        {
            $success = $this->connect($conf['memstore']['host'], $conf['memstore']['port'], 1, NULL, 100);
        } 
        else 
        {
            $success = $this->connect($conf['memstore']['socket']);           
        }

        if (!$success) 
        {
            error_log("Could not connect to redis ". (($conf['memstore']['tcp']) ? $conf['memstore']['host']."/".$conf['memstore']['port'] : "unix socket " . $conf['memstore']['socket']));
        }
            
        $this->setOption(Redis::OPT_SERIALIZER, $conf['memstore']['serializer']);
        $this->setOption(Redis::OPT_PREFIX, $conf['memstore']['prefix']);
        $this->select($conf['memstore']['db']);
    }

    
    function __destruct() 
    {
        if ($this->writeBuffering && !empty($this->buffer)) 
        {
            parent::ping();
            parent::mSet($this->buffer);
        }
        
        if ($this->isConnected()) 
        {
            parent::close();
        }

        parent::__destruct();
    }

    
    public function setWriteBuffering($mode=TRUE) 
    {
        $this->writeBuffering = $mode;        
    }

    
    public function getMulti($keys) 
    {
        $ret = [];
        $values = $this->getMultiple($keys);
        $len = count($keys);
        for ($i=0; $i<$len; $i++) 
        {
            if ($values[$i] !== FALSE) 
            {
                $ret[$keys[$i]] = $values[$i];
            }
        }
        return $ret;
    }


    function touch($key, $ttl) 
    {
        parent::expire($key, $ttl);
    }


    function set($key, $value, $buffer_it=TRUE) 
    {
        if ($this->writeBuffering && $buffer_it) 
        {
            $this->buffer[$key] = $value;
            return TRUE;
        } 
        else 
        {
            return parent::set($key, $value);
        }
    }

        
}