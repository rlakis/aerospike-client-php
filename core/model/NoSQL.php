<?php
namespace Core\Model;

require_once 'asd/MCUserTrait.php';


class NoSQL 
{
    use \Core\Model\ASD\UserTrait;

    private static $instance = null;
    private $cluster;
    
    private function __construct() 
    {
        $connection_config = ["hosts" => [
                    [ "addr" => "h5.mourjan.com", "port" => 3000 ],
                    [ "addr" => "h8.mourjan.com", "port" => 3000 ],
                  ]];
        $this->cluster = new \Aerospike($connection_config, TRUE);
    }
    
    
    public static function getInstance() : NoSQL
    {
        if (!self::$instance)
        {
            self::$instance = new NoSQL();
            
            if (!self::$instance->cluster->isConnected())
            {
                error_log( "Failed to connect to the Aerospike server [" . self::$instance->cluster->errorno() . "]: " . self::$instance->cluster->error());
            }
        }
        
        return self::$instance;
    }
    
    
    public function getConnection()
    {
        return $this->cluster;
    }
    
    
    
    public function close()
    {
        if (self::$instance && self::$instance->cluster->isConnected()) 
        {
            self::$instance->cluster->close();
        }
    }
    
    
    
}