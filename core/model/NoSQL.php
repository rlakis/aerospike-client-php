<?php
namespace Core\Model;

require_once 'asd/UserTrait.php';
require_once 'asd/MobileTrait.php';
require_once 'asd/DeviceTrait.php';
require_once 'asd/BlackListTrait.php';


class NoSQL 
{
    use \Core\Model\ASD\UserTrait;    
    use \Core\Model\ASD\MobileTrait;  
    use \Core\Model\ASD\DeviceTrait;
    use \Core\Model\ASD\BlackListTrait;
    
    private static $instance = null;
    private $cluster;
    private $configuration = ["hosts" => [["addr"=>"148.251.184.77", "port"=>3000], ["addr"=>"138.201.28.229", "port"=>3000]]];
    private $options = [
                \Aerospike::OPT_READ_TIMEOUT => 1500,
                \Aerospike::OPT_WRITE_TIMEOUT => 2000,
                \Aerospike::OPT_POLICY_KEY => \Aerospike::POLICY_KEY_SEND, 
                \Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE, 
                ];
    
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
    
   
    public function getBins($pk, array $bins=[]) 
    {
        $record=[];
        if ($bins)
        {
            $status = $this->getConnection()->get($pk, $record, $bins);
        }         
        else 
        {
            $status = $this->getConnection()->get($pk, $record);
        }
        
        if ($status != \Aerospike::OK)
        {
            if ($status!= \Aerospike::ERR_RECORD_NOT_FOUND)
            {
                $this->logError(__FUNCTION__, $pk);
                return FALSE;
            }
            return [];
        }
        
        return $record['bins'];        
    }
    
    
    public function setBins($pk, array $bins) : bool
    {
        $status = $this->getConnection()->put($pk, $bins);
        if ($status != \Aerospike::OK) 
        {
            $this->logError(__FUNCTION__, $pk);
            error_log(json_encode($bins));
            
            return FALSE;
        }
        error_log(sprintf("%s", json_encode(['mt'=> microtime(TRUE), 'pk'=>$pk, 'bn'=>$bins])).PHP_EOL, 3, "/var/log/mourjan/aerospike.set");      
        return TRUE;
    }
    
    
    public function exists($pk) : int
    {
        $metadata = null;
        if ($this->getConnection()->exists($pk, $metadata) != \Aerospike::OK)
        {
            return 0;
        }
        return intval($metadata['generation']);        
    }
    
    
    
    private function logError($fnc, $obj)
    {
        error_log( "Error {$fnc} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
        error_log(json_encode($obj));
    }
    
    
    public function update(string $namespace, string $tablespace, array $bins, array $keys, array $orderby, array $returning)
    {
        
    }
    
    
    protected function orderBy(array &$rowset, array $sortOptions)
    {
        
    }
    
    
    public function close()
    {
        if (self::$instance && self::$instance->cluster->isConnected()) 
        {
            self::$instance->cluster->close();
        }
    }
    
    
    
}