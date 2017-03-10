<?php
namespace Core\Model;

require_once 'asd/UserTrait.php';


class NoSQL 
{
    use \Core\Model\ASD\UserTrait;

    private static $instance = null;
    private $cluster;
    private $configuration = ["hosts" => [["addr"=>"148.251.184.77", "port"=>3000], ["addr"=>"138.201.28.229", "port"=>3000]]];
    
    private function __construct() 
    {
        $connection_config = ["hosts" => [
                    [ "addr" => "h5.mourjan.com", "port" => 3000 ],
                    [ "addr" => "h8.mourjan.com", "port" => 3000 ],
                  ]];
        $this->cluster = new \Aerospike($this->configuration, FALSE);
    }


    public function __destruct()
    {
        $this->close();
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
        if (!$this->cluster->isConnected())
        {
            error_log( "Tryring to connect the Aerospike server...");
            $this->cluster = new \Aerospike($this->configuration, FALSE);
        }
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