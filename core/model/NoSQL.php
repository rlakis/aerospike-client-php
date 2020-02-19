<?php
namespace Core\Model;

include_once 'Singleton.php';
include_once dirname(__DIR__) .'/data/Schema.php';
include_once 'asd/UserTrait.php';
include_once 'asd/MobileTrait.php';
include_once 'asd/DeviceTrait.php';
include_once 'asd/BlackListTrait.php';
include_once 'asd/CallTrait.php';
include_once 'asd/CacheTrait.php';

include_once 'asd/CountryTrait.php';
include_once 'asd/CityTrait.php';


class NoSQL extends Singleton {
    use \Core\Model\ASD\UserTrait;    
    use \Core\Model\ASD\MobileTrait;  
    use \Core\Model\ASD\DeviceTrait;
    use \Core\Model\ASD\BlackListTrait;
    use \Core\Model\ASD\CallTrait;
    
    use \Core\Model\ASD\CacheTrait;
    
    use \Core\Model\ASD\CountryTrait;
    use \Core\Model\ASD\CityTrait;
    
    const NS_USER               = 'users';
    const NS_MOURJAN            = 'mourjan';
    const NS_EDIGEAR            = 'edigear';
    
    const ERR_INVALID_HOST              = \Aerospike::ERR_INVALID_HOST;
    const ERR_PARAM                     = \Aerospike::ERR_PARAM;
    const ERR_CLIENT                    = \Aerospike::ERR_CLIENT;
    const OK                            = \Aerospike::OK;
    const ERR_SERVER                    = \Aerospike::ERR_SERVER;
    const ERR_RECORD_NOT_FOUND          = \Aerospike::ERR_RECORD_NOT_FOUND;
    const ERR_RECORD_GENERATION         = \Aerospike::ERR_RECORD_GENERATION;
    const ERR_REQUEST_INVALID           = \Aerospike::ERR_REQUEST_INVALID;
    const ERR_RECORD_EXISTS             = \Aerospike::ERR_RECORD_EXISTS;
    //const ERR_BIN_EXISTS                = \Aerospike::ERR_BIN_EXISTS;
    const ERR_CLUSTER_CHANGE            = \Aerospike::ERR_CLUSTER_CHANGE;
    const ERR_SERVER_FULL               = \Aerospike::ERR_SERVER_FULL;
    const ERR_TIMEOUT                   = \Aerospike::ERR_TIMEOUT;
    //const ERR_NO_XDR                    = \Aerospike::ERR_NO_XDR;
    const ERR_CLUSTER                   = \Aerospike::ERR_CLUSTER;
    const ERR_BIN_INCOMPATIBLE_TYPE     = \Aerospike::ERR_BIN_INCOMPATIBLE_TYPE;
    const ERR_RECORD_TOO_BIG            = \Aerospike::ERR_RECORD_TOO_BIG;
    const ERR_RECORD_BUSY               = \Aerospike::ERR_RECORD_BUSY;
    const ERR_SCAN_ABORTED              = \Aerospike::ERR_SCAN_ABORTED;
    const ERR_UNSUPPORTED_FEATURE       = \Aerospike::ERR_UNSUPPORTED_FEATURE;
    //const ERR_BIN_NOT_FOUND             = \Aerospike::ERR_BIN_NOT_FOUND;
    const ERR_DEVICE_OVERLOAD           = \Aerospike::ERR_DEVICE_OVERLOAD;
    const ERR_RECORD_KEY_MISMATCH       = \Aerospike::ERR_RECORD_KEY_MISMATCH;
    const ERR_NAMESPACE_NOT_FOUND       = \Aerospike::ERR_NAMESPACE_NOT_FOUND;
    const ERR_BIN_NAME                  = \Aerospike::ERR_BIN_NAME;
    const QUERY_END                     = \Aerospike::ERR_QUERY_END;
    const ERR_UDF                       = \Aerospike::ERR_UDF;
    const ERR_LARGE_ITEM_NOT_FOUND      = \Aerospike::ERR_LARGE_ITEM_NOT_FOUND;
    const ERR_INDEX_FOUND               = \Aerospike::ERR_INDEX_FOUND;
    const ERR_INDEX_NOT_FOUND           = \Aerospike::ERR_INDEX_NOT_FOUND;
    const ERR_INDEX_OOM                 = \Aerospike::ERR_INDEX_OOM;
    const ERR_INDEX_NOT_READABLE        = \Aerospike::ERR_INDEX_NOT_READABLE;
    const ERR_INDEX                     = \Aerospike::ERR_INDEX;
    const ERR_INDEX_NAME_MAXLEN         = \Aerospike::ERR_INDEX_NAME_MAXLEN;
    const ERR_INDEX_MAXCOUNT            = \Aerospike::ERR_INDEX_MAXCOUNT;
    const ERR_QUERY_ABORTED             = \Aerospike::ERR_QUERY_ABORTED;
    const ERR_QUERY_QUEUE_FULL          = \Aerospike::ERR_QUERY_QUEUE_FULL;
    const ERR_QUERY_TIMEOUT             = \Aerospike::ERR_QUERY_TIMEOUT;
    const ERR_QUERY                     = \Aerospike::ERR_QUERY;
    const ERR_UDF_NOT_FOUND             = \Aerospike::ERR_UDF_NOT_FOUND;
    const ERR_LUA_FILE_NOT_FOUND        = \Aerospike::ERR_LUA_FILE_NOT_FOUND;
    const SECURITY_NOT_SUPPORTED        = \Aerospike::ERR_SECURITY_NOT_SUPPORTED;
    const SECURITY_NOT_ENABLED          = \Aerospike::ERR_SECURITY_NOT_ENABLED;
    const SECURITY_SCHEME_NOT_SUPPORTED = \Aerospike::ERR_SECURITY_SCHEME_NOT_SUPPORTED;
    const INVALID_USER                  = \Aerospike::ERR_INVALID_USER;
    const USER_ALREADY_EXISTS           = \Aerospike::ERR_USER_ALREADY_EXISTS;
    const INVALID_PASSWORD              = \Aerospike::ERR_INVALID_PASSWORD;
    const EXPIRED_PASSWORD              = \Aerospike::ERR_EXPIRED_PASSWORD;
    const FORBIDDEN_PASSWORD            = \Aerospike::ERR_FORBIDDEN_PASSWORD;
    const INVALID_CREDENTIAL            = \Aerospike::ERR_INVALID_CREDENTIAL;
    const INVALID_ROLE                  = \Aerospike::ERR_INVALID_ROLE;
    const INVALID_PRIVILEGE             = \Aerospike::ERR_INVALID_PRIVILEGE;
    const INVALID_COMMAND               = \Aerospike::ERR_INVALID_COMMAND;
    const INVALID_FIELD                 = \Aerospike::ERR_INVALID_FIELD;
    const ILLEGAL_STATE                 = \Aerospike::ERR_ILLEGAL_STATE;
    const NOT_AUTHENTICATED             = \Aerospike::ERR_NOT_AUTHENTICATED;
    const ROLE_VIOLATION                = \Aerospike::ERR_ROLE_VIOLATION;
    const ROLE_ALREADY_EXISTS           = \Aerospike::ERR_ROLE_ALREADY_EXISTS;
    const ERR_GEO_INVALID_GEOJSON       = \Aerospike::ERR_GEO_INVALID_GEOJSON;
        
    private \Aerospike $cluster;
    private array $configuration = ["hosts" => [["addr"=>"88.198.67.89", "port"=>3000], ["addr"=>"138.201.28.229", "port"=>3000], ["addr"=>"88.99.164.79", "port"=>3000]]];
    private array $options = [
                \Aerospike::OPT_POLICY_KEY      => \Aerospike::POLICY_KEY_SEND, 
                \Aerospike::OPT_POLICY_EXISTS   => \Aerospike::POLICY_EXISTS_IGNORE, 
                \Aerospike::OPT_MAX_RETRIES     => 2
                ];
    
    protected \Core\Data\Schema $schema;
    protected function __construct() {
        $this->cluster = new \Aerospike($this->configuration, TRUE, $this->options);
        $this->schema = \Core\Data\Schema::instance();
    }


    public function __destruct() {
        $this->close();
    }


    public static function instance() : NoSQL {
        return static::getInstance();
    }
    
    
    /*
    public static function getInstance() : NoSQL {
        if (!self::$instance) {
            self::$instance = new NoSQL();
            
            if (!self::$instance->cluster->isConnected()) {
                error_log( "Failed to connect to the Aerospike server [" . self::$instance->cluster->errorno() . "]: " . self::$instance->cluster->error());
            }
        }
        
        return self::$instance;
    }
    */
    
    public function getConnection() : \Aerospike {
        if (!$this->cluster->isConnected()) {
            error_log( "Tryring to connect the Aerospike server...");
            $this->cluster = new \Aerospike($this->configuration, TRUE, $this->options);
        }
        return $this->cluster;
    }
    
    
    public function genId(string $generator, int &$sequence) : int {
        $sequence = 0;
        $record = [];
        $key = $this->getConnection()->initKey(NoSQL::NS_USER, "generators", $generator);
        $operations = [
            ['op' => \Aerospike::OPERATOR_INCR, 'bin' => 'sequence', 'val' => 1],
            ['op' => \Aerospike::OPERATOR_READ, 'bin' => 'sequence'],
        ];
        
        $status = $this->getConnection()->operate($key, $operations, $record);
                
        if ($status==\Aerospike::OK) {
            $sequence = $record['sequence'];
        }
        return $status;
    }
            
    
    public function initLongKey(string $ns, string $set, int $id) : array {
        return $this->getConnection()->initKey($ns, $set, $id);
    }

    
    public function initStringKey(string $ns, string $set, string $id) : array {
        return $this->getConnection()->initKey($ns, $set, $id);
    }
    
    
    public function isReadError(int $status) : bool {
        return ($status!=NoSQL::OK && $status!=NoSQL::ERR_RECORD_NOT_FOUND);
    }
    
    
    public function getBins(array $pk, array $bins=[]) {
        if (isset($pk['digest']) && !empty($pk['digest']) && $pk['key']==NULL) {
            $pk = $this->getConnection()->initKey($pk['ns'], $pk['set'], $pk['digest'], true);          
        }
        $record=[];
        if ($bins) {
            $status = $this->getConnection()->get($pk, $record, $bins);
        }         
        else {
            $status = $this->getConnection()->get($pk, $record, NULL);           
        }

        if ($status != \Aerospike::OK) {
            if ($status!= \Aerospike::ERR_RECORD_NOT_FOUND) {
                NoSQL::Log(['Key'=>$pk, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
                return FALSE;
            }
            return [];
        }
        
        return $record['bins'];        
    }
    
    
    public function get(array $key, array &$record, array $bins=[]) : int {
        if (isset($key['digest']) && !empty($key['digest']) && $key['key']==NULL) {
            $key=$this->getConnection()->initKey($key['ns'], $key['set'], $key['digest'], true);          
        }
        $status=$this->getConnection()->get($key, $record, $bins);
        return $status;
    }
    
    
    public function getRecord(array $pk, ?array &$record, array $bins=[]) : int {        
        $status = $this->getConnection()->get($pk, $record, empty($bins) ? NULL : $bins);

        if ($status!=\Aerospike::OK && $status!=\Aerospike::ERR_RECORD_NOT_FOUND) {
            $this->logError(__CLASS__ .'->'. __FUNCTION__, $pk);
            NoSQL::Log(['Key'=>$pk, 'Error'=>"[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}"]);
        } 
        else {
            if ($status==\Aerospike::OK) {
                $record = $record['bins'];
            }
        }
        
        return $status; 
    }
    
    
    public function setBins(array $pk, array $bins) : bool {
        
        if (isset($pk['digest']) && !empty($pk['digest']) && !isset($pk['pk'])) {
            $pk = $this->getConnection()->initKey($pk['ns'], $pk['set'], $pk['digest'], true);          
        }
        //error_log(var_export($pk, TRUE));
        $status = $this->getConnection()->put($pk, $bins);

        if ($status != \Aerospike::OK) {
            $this->logError(__CLASS__ .'->'. __FUNCTION__, $pk, $bins);            
            return FALSE;
        }
        return TRUE;
    }
    

    public function write(array $pk, array $bins, int $ttl=0, array $options=[]) : int {        
        if (isset($pk['digest']) && !empty($pk['digest']) && !isset($pk['pk'])) {
            $pk = $this->getConnection()->initKey($pk['ns'], $pk['set'], $pk['digest'], true);          
        }
        if (!empty($options)) {
            $status = $this->getConnection()->put($pk, $bins, $ttl, $options);
        }
        else if ($ttl>0) {
            $status = $this->getConnection()->put($pk, $bins, $ttl);
        }
        else {
            $status = $this->getConnection()->put($pk, $bins);
        }
        
        if ($status !== \Aerospike::OK) {
            $this->logError(__CLASS__ .'->'. __FUNCTION__, $pk, $bins);            
        }
        return $status;
    }


    
    public function exists($pk) : int {
        $metadata = null;
        if ($this->getConnection()->exists($pk, $metadata) != \Aerospike::OK) {
            return 0;
        }
        return intval($metadata['generation'])?intval($metadata['generation']):1; 
    }
        
    
    private function logError($fnc, $obj, $bins=NULL) {
        error_log( "Error {$fnc} [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}".PHP_EOL. var_export($obj, TRUE).(($bins)?PHP_EOL.json_encode($bins):''));
    }
    
    
    public function update(string $namespace, string $tablespace, array $bins, array $keys, array $orderby, array $returning) {
        
    }
    
    
    protected function orderBy(array &$rowset, array $sortOptions) {
        
    }
    
    
    public function close() {
        if ($this->cluster->isConnected()) {
            $this->cluster->close();
        }
    }
    
    
    protected function beforeInsert(string $table, array &$bins, array &$errors) : bool {
        $errors=[];
        return empty($errors);
    }
    
    
    protected function beforeUpdate() {
        
    }
    
    public static function Log($message) {
        $dbt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0);
        if (!empty($dbt)) {
            $msg=[];
            foreach ($dbt as $t) {
                $msg[] = ['file'=>$t['file'], 'line'=>$t['line'], 'func'=>$t['function'], 'class'=>$t['class'], 'args'=> isset($t['args'])?$t['args']:NULL];
            }
            error_log(json_encode($msg));
            error_log(PHP_EOL.json_encode($msg, JSON_PRETTY_PRINT).PHP_EOL, 3, "/var/log/mourjan/LogFile.txt");
            /*
            unset($dbt[0]['function']);
            unset($dbt[0]['class']);
            unset($dbt[0]['type']);
            if (isset($dbt[0]['object'])) {
                unset($dbt[0]['object']);
            }
            
            error_log(__CLASS__. PHP_EOL.json_encode($dbt[0], JSON_PRETTY_PRINT).PHP_EOL.'>');            
            error_log(PHP_EOL.json_encode($dbt[0], JSON_PRETTY_PRINT).PHP_EOL, 3, "/var/log/mourjan/LogFile.txt");
             * 
             */
            //if (isset($dbt[1])) {
               // error_log(PHP_EOL.json_encode($dbt[1], JSON_PRETTY_PRINT));
            //}
        }
    }
    
    
}