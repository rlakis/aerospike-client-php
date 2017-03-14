<?php
namespace Core\Model;

require_once 'asd/UserTrait.php';
require_once 'asd/MobileTrait.php';


class NoSQL 
{
    use \Core\Model\ASD\UserTrait;    
    use \Core\Model\ASD\MobileTrait;    
    
    private static $instance = null;
    private $cluster;
    private $configuration = ["hosts" => [["addr"=>"148.251.184.77", "port"=>3000], ["addr"=>"138.201.28.229", "port"=>3000]]];
    private $options = [\Aerospike::OPT_POLICY_KEY => \Aerospike::POLICY_KEY_SEND, \Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE];
    
    private function __construct() 
    {
        $this->cluster = new \Aerospike($this->configuration, TRUE, $this->options);
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
            $this->cluster = new \Aerospike($this->configuration, TRUE, $this->options);
        }
        return $this->cluster;
    }
    
    
    public function genId(string $generator, &$sequence)
    {
        $sequence = 0;
        $record = [];
        $key = $this->getConnection()->initKey(ASD\NS_USER, "generators", $generator);
        $operations = [
            ["op" => \Aerospike::OPERATOR_INCR, "bin" => "sequence", "val" => 1],
            ["op" => \Aerospike::OPERATOR_READ, "bin" => "sequence"],
        ];
        
        if ($this->getConnection()->operate($key, $operations, $record)== \Aerospike::OK)
        {
            $sequence = $record['sequence'];
        }   
    }
    
   
    public function getBins($pk, array $bins) : array
    {
        $record=[];
        if ($this->getConnection()->get($pk, $record, $bins) != \Aerospike::OK) 
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return [];
        }
        return $record['bins'];        
    }
    
    
    public function setBins($pk, array $bins) : bool
    {
        $status = $this->getConnection()->put($pk, $bins);
        if ($status != \Aerospike::OK) 
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            error_log(json_encode($pk));
            error_log(json_encode($bins));
            
            return FALSE;
        }
        return TRUE;
    }
    
    
    public function close()
    {
        if (self::$instance && self::$instance->cluster->isConnected()) 
        {
            self::$instance->cluster->close();
        }
    }
    
    
    
}