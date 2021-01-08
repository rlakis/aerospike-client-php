<?php
namespace Core\Model;

\Config::instance()->incLibFile('MCCache')->incLibFile('SphinxQL')->incModelFile('NoSQL')->incCoreFile('/data/Schema');
use Core\Lib\MCCache;
//use Core\Lib\SphinxQL;
use Core\Model\NoSQL;

class DB {

    private static $Instance;    
    private static $Cache;
    private static /*$user, $pass,*/ $dbUri;

    private static $Readonly;
    private static $IsolationLevel;
    private static $WaitTimeout;
    
    public static $SectionsVersion;
    public static $TagsVersion;
    public static $LocalitiesVersion;
    
    
    public array $countriesDictionary;
    public array $citiesDictionary;
    
    private bool $slaveOfRedis;
    //public SphinxQL $ql;
    public NoSQL $as;
    
    public \Manticoresearch\Client $manticore;
    public \Manticoresearch\Index $idx;
    //public \Manticoresearch\Search $search;
    
    public \RdKafka\Producer $kafkaProducer;
    
    private $version=0;
    
    public function __construct(bool $readonly=TRUE) {
        $this->as=NoSQL::instance();
        $this->slaveOfRedis=(\get_cfg_var('mourjan.server_id')!=='1');
        $this->countriesDictionary=[];
        $this->citiesDictionary=[];
        self::$dbUri='firebird:dbname='.\Config::instance()->get('db_host').':'.\Config::instance()->get('db_name').';charset=UTF8';
        
        DB::$Readonly=$readonly;
        if ($readonly) {
            DB::$IsolationLevel=\PDO::FB_TRANS_CONCURRENCY;
            DB::$WaitTimeout=0;
        }
        else {
            DB::$IsolationLevel=\PDO::FB_TRANS_COMMITTED;
            DB::$WaitTimeout=10; 
        }
        
        self::getCacheStorage();
        
        self::$SectionsVersion=FALSE;
        self::$TagsVersion=FALSE;
        self::$LocalitiesVersion=FALSE;

        $versions=self::$Cache->getMulti(['section-counts-version', 'locality-counts-version', 'tag-counts-version']);
        self::$SectionsVersion = $versions['section-counts-version'];
        self::$LocalitiesVersion = $versions['locality-counts-version'];
        self::$TagsVersion = $versions['tag-counts-version'];
        
        //\error_log(\json_encode($versions));
        
        //self::$SectionsVersion = $this->version;
        //self::$LocalitiesVersion = $this->version;
        //self::$TagsVersion = $this->version;
        
        //$this->ql=new SphinxQL(\Config::instance()->get('sphinxql'), \Config::instance()->get('search_index')); 
        
        //$conf = new \RdKafka\Conf();
        //$conf->set('log_level', LOG_ERR);
        //$conf->set('debug', 'generic');
        //$this->kafkaProducer = new \RdKafka\Producer($conf);
        //$this->kafkaProducer->addBrokers("a1.mourjan.com:9092,www.edigear.com:9092");

        $params = ['connections'=>
            [                
                ['host' => '148.251.186.42', 'port' => 8308],
                ['host' => '148.251.186.42', 'port' => 8308],
                ['host' => '138.201.142.130', 'port' => 8308],
            ],
            'connectionStrategy' => \Manticoresearch\Connection\Strategy\RoundRobin::class,
            'retries' => 3
        ];
        $this->manticore=new \Manticoresearch\Client($params);
        
        $this->idx=new \Manticoresearch\Index($this->manticore, 'ad');
        //$this->search=new \Manticoresearch\Search($this->manticore);
        //$this->search->setIndex('ad');
        //var_dump($this->idx->search('mbw 320')->get());
        //$this->manticore->se
    }


    public function __call($name, $arguments) {
        \error_log(__CLASS__.'.'.__FUNCTION__.' '.$name);
        /*
         try {
         }
         catch (\PDOException $e) {
            if($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }
            DB::$Instance=null;
            DB::newInstance();
            
         }
         * 
         */
        return call_user_func_array([$this->connection(), $name], $arguments);
    }
    
    
    public static function getCacheStorage() : MCCache {
        if (!isset(DB::$Cache)) { DB::$Cache = new MCCache(); }
        return self::$Cache;
    }


    public function __destruct() {
        $this->close();
        //if ($this->ql!=null) { $this->ql->close(); }
    }

    
    
    public function index() : SphinxQL {
        return $this->ql;
    }
    
    
    public function setWriteMode(bool $on=TRUE) : void {
        $this->setReadOnly(!$on);
    }
    
        
    private function setReadOnly(bool $readonly) : void {
        if ($readonly!==DB::$Readonly) {
            $this->commit();
            DB::$Readonly=$readonly;
            if ($readonly) {
                DB::$IsolationLevel=\PDO::FB_TRANS_CONCURRENCY;
                DB::$WaitTimeout=0;
            }
            else {
                DB::$IsolationLevel=\PDO::FB_TRANS_COMMITTED;
                DB::$WaitTimeout=10; 
            }
        }
    }
    

    /*
    private function setTransactionIsolation($read) : void {
        if ($read != DB::$Readonly) { $this->commit(); }        
        
        if ($read) {
            DB::$Readonly=TRUE;
            DB::$IsolationLevel = \PDO::FB_TRANS_CONCURRENCY;
            DB::$WaitTimeout = 0;
        } 
        else {
            DB::$Readonly=FALSE;
            DB::$IsolationLevel = \PDO::FB_TRANS_COMMITTED;
            DB::$WaitTimeout=10;       
        }
    }
    */
    
    
    public function inTransaction() : bool {
        if (DB::$Instance===NULL) { return FALSE; }
        
        return DB::$Instance->inTransaction();
    }
    
    
    public function commit(bool $restartTransaction=FALSE) : bool {
        if ($this->inTransaction()) {
            try {
                DB::$Instance->commit();
                if ($restartTransaction===TRUE) {
                    DB::$Instance->beginTransaction();           
                }
                return TRUE;
            } 
            catch (\Exception $ex) {
                error_log($ex->getMessage());
            }
        }
        return FALSE;
    }
    
    
    public function rollback(bool $restartTransaction=FALSE) : bool {
        if ($this->inTransaction()) {
            try {
                DB::$Instance->rollBack();
                if ($restartTransaction===TRUE) {
                    DB::$Instance->beginTransaction();           
                }
                return TRUE;
            } 
            catch (Exception $ex) {
                error_log($ex->getMessage());
            }
        }
        return FALSE;
    }
    
    
    private function newInstance() : void {
        DB::$Instance=new \PDO(DB::$dbUri, \Config::instance()->get('db_user'), \Config::instance()->get('db_pass'),
                    [
                        \PDO::ATTR_PERSISTENT=>TRUE,
                        \PDO::ATTR_AUTOCOMMIT=>FALSE,
                        \PDO::ATTR_EMULATE_PREPARES=>FALSE,
                        \PDO::ATTR_STRINGIFY_FETCHES=>FALSE,
                        \PDO::ATTR_TIMEOUT=>5,
                        \PDO::ATTR_PREFETCH=>25,
                        \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,

                        \PDO::FB_ATTR_COMMIT_RETAINING=>FALSE,
                        \PDO::FB_ATTR_READONLY=>DB::$Readonly,
                        \PDO::FB_TRANS_ISOLATION_LEVEL=>DB::$IsolationLevel,
                        \PDO::FB_ATTR_TIMEOUT=>DB::$WaitTimeout
                    ]
                );
    }


    public static function getDatabase() : \PDO {
        if (!DB::$Instance) {
            DB::$Instance=new \PDO(DB::$dbUri, \Config::instance()->get('db_user'), \Config::instance()->get('db_pass'),
                    [
                        \PDO::ATTR_PERSISTENT=>TRUE,
                        \PDO::ATTR_AUTOCOMMIT=>FALSE,
                        \PDO::ATTR_EMULATE_PREPARES=>FALSE,
                        \PDO::ATTR_STRINGIFY_FETCHES=>FALSE,
                        \PDO::ATTR_TIMEOUT=>5,
                        \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,

                        \PDO::FB_ATTR_COMMIT_RETAINING=>FALSE,
                        \PDO::FB_ATTR_READONLY => DB::$Readonly,
                        \PDO::FB_TRANS_ISOLATION_LEVEL => DB::$IsolationLevel,
                        \PDO::FB_ATTR_TIMEOUT => DB::$WaitTimeout
                    ]
                );
        }
        return DB::$Instance;
    }
    
    
    public function getInstance($try=0) {
       if (self::$Instance===null) { 
            $this->newInstance();
            if (!self::$Instance->inTransaction()) {                
                self::$Instance->beginTransaction();
            }      
        } 
        else { 
            if (!self::$Instance->inTransaction()) {   
                self::$Instance->setAttribute(\PDO::FB_ATTR_READONLY, self::$Readonly);                
                self::$Instance->setAttribute(\PDO::FB_TRANS_ISOLATION_LEVEL, self::$IsolationLevel);
                self::$Instance->setAttribute(\PDO::FB_ATTR_TIMEOUT, self::$WaitTimeout);
                self::$Instance->beginTransaction();
            } 
        }
        
        if (self::$Instance->inTransaction()) {            
            return self::$Instance;
        }
        else {
            if($try === 3) {
                error_log("############################");
                error_log("UNABLE TO BEGIN TRANSACTION");
                error_log("############################");
                return false;
            }
            else {
                usleep(100);
                return $this->getInstance($try+1);
            }
        }
    }
    
    
    public static function getTransactionIsolationMessage() : string {
    	return 'FB Transaction mode [Isolation: ' . self::$IsolationLevel .', Read only: ' . (self::$Readonly ? 'YES' : 'NO') . ', Wait timeout: ' . self::$WaitTimeout . ' seconds]' . PHP_EOL;
    }
    
    
    public static function isReadOnly() : bool {
        return DB::$Readonly;
    }
    
    
    public static function getCache() : MCCache {
        return self::$Cache;
    }
    

    function close() {
        if ($this->inTransaction()) {
            try {
                self::$Instance->commit();
            } 
            catch (Exception $ex) {
                error_log("Db commit: " . $ex->getMessage() . PHP_EOL);
                self::$Instance->rollBack();
            }
        }
        self::$Instance=NULL;
    }	


    function checkCorrectWriteMode($query) : void {
        if (\preg_match('/^(insert|update|delete|execute)/i', \trim($query))) {
            $this->setReadOnly(false);
        }
    }

    
    function get(string $query, $params=null, bool $commit=false, $fetch_mode=\PDO::FETCH_ASSOC) {
        $fbquery = new FBQuery($this, $query, $params, [FBQuery::FB_DIRECT_COMMIT=>$commit, FBQuery::FB_FETCH_MODE=>$fetch_mode]);
        return $fbquery->get();        
    }

    
    function queryResultArray(string $query, $params=null, bool $commit=false, int $fetch_mode=\PDO::FETCH_ASSOC, int $runtime=0) {
        $this->checkCorrectWriteMode($query);
        $this->getInstance();
        $result=[];
        $stmt=null;
        try {
            $stmt=$this->getInstance()->prepare($query);                                    
            if ($stmt->execute($params)) {
                $query=\trim($query);               
                if (!\stristr($query, " returning ") && \preg_match('/^(insert|update|delete|execute)\s/i', $query)) {
                    $result=true;
                }
                else {
                    $result=$stmt->fetchAll($fetch_mode);
                }
            }
            else {
                \error_log("DB.".__FUNCTION__."[".__LINE__."] Failed to execute statement".PHP_EOL.$query.PHP_EOL.\json_encode($params));
            }
            unset($stmt);
            if ($commit) { $this->commit(); }                    
        }
        catch (\PDOException $pdoException) {
            if ($commit) {
                error_log(__FUNCTION__ . ' first exception '.$pdoException->getMessage());
                unset($stmt);
                $result = FALSE;

                if ($runtime<5 && preg_match('/913 deadlock/', $pdoException->getMessage())) {
                    self::$Instance->rollBack();
                    usleep(200);
                    error_log('RETRY: '. ($runtime+1) .' | CODE: '.$pdoException->getCode(). ' | '.$pdoException->getMessage().PHP_EOL.$query.PHP_EOL.var_export($params, TRUE));
                    $this->getInstance();
                    return $this->queryResultArray($query, $params, $commit, $fetch_mode, $runtime+1);                
                } 
                else {
                    self::$Instance->rollBack();
                    error_log('CODE: '.$runtime.'/'.$pdoException->getCode().' | '.$pdoException->getMessage().PHP_EOL.$query.PHP_EOL.var_export($params, TRUE));
                }
            }
            else {
                unset($stmt);
                self::$Instance->rollBack();
                \error_log(__LINE__. '-> CODE: '.$runtime.'/'.$pdoException->getCode().' | '.$pdoException->getMessage().PHP_EOL.$query.PHP_EOL.var_export($params, TRUE));
                $result=false;
            }
        }
        catch (\Exception $ex) {
            error_log(__FUNCTION__ . ' second exception '.$ex->getMessage());
            unset($stmt);
            self::$Instance->rollBack();
            $result=FALSE;
        }
        return $result;
    }
    
    
    function closeStatement(\PDOStatement $stmt) : void {     
        $res = $stmt->closeCursor();
        if ($res==FALSE) {
            error_log("close statement failed!");
        }
    }
    
    
    function executeStatement($stmt, $params=null, $runtime=0) {
        $result = false;
        try {
            if($params) {
                $result = $stmt->execute($params);
            }
            else {
                $result = $stmt->execute();
            }            
        } 
        catch (Exception $ex) {             
            if( (strpos($ex->getMessage(), '913 deadlock') > -1) && $runtime < 5) {
                usleep(100);
                return $this->executeStatement($stmt, $params, $runtime+1);
            }
            else {
                error_log('CODE: '. $ex->getCode() . ' | '.$ex->getMessage() . PHP_EOL);
                if ($stmt) {
                    ob_start(); 
                    $stmt->debugDumpParams();
                    error_log(ob_get_clean());
                }
            }
        }
        return $result;
    }
    
    
    function stmtCacheResultSimpleArray(string $label, \PDOStatement $stmt, ?array $params=null, int $key=0, int $expire=2592000, bool $cacheIgnore=false) {
        //$records=array();
        //$foo = self::$Cache->get($label);
                        
        if ($cacheIgnore || ($foo=self::$Cache->get($label)===false)) { 
            $records=[];
            try {      
                //if ($params) { $stmt->execute($params);
                //else
                $stmt->execute($params);

                if (($row=$stmt->fetch(\PDO::FETCH_NUM)) !== false) {
                    do {
                        if ($key>=0) {
                            $records[$row[$key]]=$row;
                        }
                        else {
                            $records=$row;
                            break;
                        }
                    } while($row=$stmt->fetch(\PDO::FETCH_NUM));
                }
               
                self::$Cache->setEx($label, empty($records)?3600:$expire, $records);
            }
            catch (Exception $ex) {
                \error_log($ex->getMessage() . PHP_EOL . $stmt->queryString . PHP_EOL . var_export($params, TRUE));
                self::$Instance->rollBack();
                return false;
            }
            return $records;
        } 
        else {
            \error_log(\json_encode($foo));
            return $foo;
        }              
    }
    
    
    function prepare($stmt, $q) : \PDOStatement {
        if (!$stmt || !$this->inTransaction()){
            $this->checkCorrectWriteMode($q);
            $stmt = $this->getInstance()->prepare($q);
        }
        return $stmt;
    }
    
    
    function prepareQuery($q, ?array $attrs=null) : \PDOStatement {
        $this->checkCorrectWriteMode($q);
        if ($attrs!==null) {
            return $this->getInstance()->prepare($q, $attrs);
        }
        else {
            return $this->getInstance()->prepare($q);
        }
    }
    
    
    function queryCacheResultSimpleArray(string $label, string $query, ?array $params=null, int $key=0, int $lifetime=86400, bool $forceSetting=false, bool $forceIfEmpty=false) : array {
        $records=[];
        $foo = self::$Cache->get($label);
        
        if ($forceSetting || ($foo===FALSE) || ($forceIfEmpty && empty($foo))) {
            //error_log("Failed to load from cache: " . $label);
            //error_log($query);
            $this->checkCorrectWriteMode($query);
            $stmt = $this->getInstance()->prepare($query);
            
            try {
                if ($params)
                    $stmt->execute($params);
                else
                    $stmt->execute();

                if(($row = $stmt->fetch(\PDO::FETCH_NUM)) !== false) {
                    $simpleArray=\is_null($key) ? true : false;
                    
                    do {
                        if ($simpleArray) {
                            $records[]=$row;
                        }
                        else {
                            if ($key>=0) {
                                $records[$row[$key]]=$row;
                            }
                            else {
                                $records=$row;
                                break;
                            }
                        }
                    }
                    while ($row = $stmt->fetch(\PDO::FETCH_NUM));
                }
            }
            catch (Exception $ex) {
                \error_log($ex->getMessage() . PHP_EOL . $query . PHP_EOL . \var_export($params, TRUE));
                self::$Instance->rollBack();
                return [];
            }
            self::$Cache->set($label, $records);
            return $records;
        } 
        else {
            return $foo;
        }        
    }
    
    
    function getSectionFollowUp(int $countryId, int $cityId=0, int $sectionId, int $purposeId=0, bool $force=false){
        return $this->queryCacheResultSimpleArray(
            "follow_{$countryId}_{$cityId}_{$sectionId}_{$purposeId}", 
            "select to_section_id,to_purpose_id from section_follow s where 
                s.section_id={$sectionId} and 
                s.country_id={$countryId} and 
                s.city_id={$cityId} and 
                s.purpose_id={$purposeId} and 
                s.counter>20 
                order by counter desc", null, 0, 86400, $force);
    }
    
    
    function getSectionPriceRange($countryId,$tagId,$force=0){
        return $this->queryCacheResultSimpleArray(
            "ext_pr_{$countryId}_{$tagId}", 
            "select year_make,lower_price,upper_price,samples from section_tag_price_range s 
                where 
                s.section_tag_id = {$tagId} and 
                s.country_id = {$countryId}  
                order by year_make desc", null, 0, 86400, $force);
    }
       
    
    function queryQLCacheResultSimpleArray($label, $query, $params=null, $key=0, $lifetime=86400, $forceSetting=false, $forceIfEmpty=false) {
        $records=array();        

        $foo = self::$Cache->get($label);

        if ($forceSetting || ($foo===FALSE) || ($forceIfEmpty && empty($foo))) {
            
            $this->checkCorrectWriteMode($query);
            $stmt = $this->getInstance()->prepare($query);
            
            try {
                if ($params)
                    $stmt->execute($params);
                else
                    $stmt->execute();

                if(($row = $stmt->fetch(\PDO::FETCH_NUM)) !== false) {

                    //$count = count($row);
                    $simpleArray=is_null($key) ? true : false;
                    do {
                        //for ($i=0; $i < $count; $i++)
                        //    if(is_numeric($row[$i])) $row[$i] = $row[$i]+0;

                        if ($simpleArray) {
                            $records[]=$row;
                        }
                        else {
                            if ($key>=0)
                                $records[$row[$key]]=$row;
                            else {
                                $records=$row;
                                break;
                            }
                        }
                    }
                    while($row = $stmt->fetch(\PDO::FETCH_NUM));
                }
            }
            catch (Exception $ex) {
                error_log($ex->getMessage() . PHP_EOL . $query . PHP_EOL . var_export($params, TRUE));
                self::$Instance->rollBack();
                return false;
            }
            self::$Cache->set($label, $records);
            return $records;
        } 
        else {
            return $foo;
        }        
    }

    /* New data block */
    function asCountriesDictionary() : array {
        if (empty($this->countriesDictionary)) {
            $where=\Aerospike::predicateEquals(\Core\Data\Schema::BIN_BLOCKED, 0);
            $status= NoSQL::instance()->getConnection()->query(\Core\Data\NS_MOURJAN, \Core\Data\TS_COUNTRY, $where,
                function($row)  { 
                    $this->countriesDictionary[$row['bins'][\Core\Data\Schema::BIN_ID]]=$row['bins'];                 
            }, []);
            if ($status!==\Aerospike::OK) { $this->countriesDictionary=[]; }
            if (!empty($this->countriesDictionary)) {
                $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'countries-dic');
                NoSQL::instance()->setBins($key, ['data'=>$this->countriesDictionary]);
            }
        
        }
        return $this->countriesDictionary;
    }
    
    
    function asCitiesDictionary() : array {
        if (empty($this->citiesDictionary)) {
            $where=\Aerospike::predicateEquals(\Core\Data\Schema::BIN_BLOCKED, 0);
            $status= NoSQL::instance()->getConnection()->query(\Core\Data\NS_MOURJAN, \Core\Data\TS_CITY, $where,
                function($row)  { 
                    $this->citiesDictionary[$row['bins'][\Core\Data\Schema::BIN_ID]]=$row['bins'];                 
            }, []);
            if ($status!==\Aerospike::OK) { $this->citiesDictionary=[]; }
            if (!empty($this->citiesDictionary)) {
                $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'cities-dic');
                NoSQL::instance()->setBins($key, ['data'=>$this->citiesDictionary]);
            }
        
        }
        return $this->citiesDictionary;
    }
    
    
    // deprecated
    function getCountriesDictionary(bool $force=false) {
        if (!$this->slaveOfRedis) { $force = true; }
        
        $countries = $this->queryCacheResultSimpleArray('countries-dictionary',
                    'select ID, NAME_AR, NAME_EN, lower(trim(id_2)) URI, ' .
                    'trim(currency_id) currency_id, code, '.
                    '(select list(ID) from f_city where country_id=country.id and blocked=0) cities, '.
                    'LONGITUDE, LATITUDE, LOCKED, UNIXTIME ' .
                    'from country where blocked=0',
                    null, 0, 86400, $force);

        if (!empty($countries) && !\is_array($countries[\key($countries)][6])) {
            
            foreach ($countries as $country_id => $country) {
                $cities = \explode(',', $country[6]);
                $len = \count($cities);
                if ($len>1) {
                    $countries[$country_id][6]=$cities;
                }
                else {
                    $countries[$country_id][6]=[];
                }
            }
            
            self::$Cache->set('countries-dictionary', $countries);
        }
        return $countries;
    }
    
    // deprecated
    function getCitiesDictionary($force=FALSE) {
        if (!$this->slaveOfRedis) { $force = true; }

        return $this->queryCacheResultSimpleArray('cities-dictionary',
                'select f_city.ID, lg.NAME NAME_AR, f_city.NAME NAME_EN, URI, COUNTRY_ID, LATITUDE, LONGITUDE, 1 locked, UNIXTIME 
                from f_city 
                left join nlang lg on lg.TABLE_ID=201 and lg.lang=\'ar\' and lg.ID=F_CITY.ID
                where blocked=0',
                null, 0, 86400, $force);        
    }
    
    
    function asPurposes() : array {
        $record=[];
        $pk=$this->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'purposes');
        if ($this->as->get($pk, $record, ['data'])===\Aerospike::OK) {
            return $record['bins']['data'];
        }
        return [];
    }
    
    
    function getPurposes($force=FALSE) {
        if (!$this->slaveOfRedis) { $force = true; }
  
        return $this->queryCacheResultSimpleArray('purposes',
                    'select ID, NAME_AR, NAME_EN, URI, UNIXTIME from purpose where blocked=0',
                    null, 0, 86400, $force);
    }
    
    
    function asRoots() : array {
        $record=[];
        $pk=$this->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'roots');
        if ($this->as->getBins($pk, $record, ['data'])===\Aerospike::OK) {
            return $record['bins']['data'];
        }
        return [];
    }
    
    
    function getRoots($force=FALSE) {
        if (!$this->slaveOfRedis) { $force = true; }
        return $this->queryCacheResultSimpleArray('roots',
                    'select ID, NAME_AR, NAME_EN, URI, DIFFER_SECTION_ID, BLOCKED, UNIXTIME from root order by 1',
                    null, 0, 86400,  $force);
    }
    
    
    function asSections() : array {
        $record=[];
        $pk=$this->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'sections');
        if ($this->as->getBins($pk, $record, ['data'])===\Aerospike::OK) {
            return $record['bins']['data'];
        }
        
        return [];
    }
    
    
    function getSections($forceSetting=false) {
        $label = 'sections';
        $records = false;
        
        if (!$forceSetting) {
            $records = self::$Cache->get($label);
        }
        
        if ($forceSetting || $records===FALSE) {
            $records = $this->ql->directQuery(
                    "select id, section_name_ar as name_ar, section_name_en as name_en, "
                    . " section_uri as uri, root_id, "
                    . " related_id, related, purposes, related_purpose_id, related_to_purpose_id "
                    . "from section where section_blocked=0 limit 100000");
        }
        
        self::$Cache->set($label, $records);        
        return $records;
    }

        
    function asCountriesData(string $lang) : array {
        $label = "country-data-{$lang}";     
    }
    
    
    function getCountriesData(string $lang) : array {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        
        $label = "country-data-{$lang}-{$vv}";        
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
        else {            
            $result=array();
        }

        $countries = $this->asCountriesDictionary();
        //$f=($lang==='ar')?1:2;
        $name='name_'.$lang;
        $resource = $this->ql->getConnection()->query("select groupby(), count(*), max(date_added) from ad group by country limit 1000");
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$label.']');
        }
        
        if ($resource instanceof \mysqli_result) { 
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($row[0], 0, 0, 0, $lang);
                $result[$row[0]]=['name'=>$countries[$row[0]][$name], 'counter'=>$row[1]+0, 'unixtime'=>$row[2]+0, 
                                'uri'=>\strtolower($countries[$row[0]][\Core\Data\Schema::BIN_URI]), 'currency'=>$countries[$row[0]][\Core\Data\Schema::BIN_CURRENCY], 
                                'code'=>$countries[$row[0]][\Core\Data\Schema::COUNTRY_CODE],
                                'purposes'=>$purposes, 'cities'=> $this->getCitiesData($row[0], $lang)];
            }
            $resource->free_result();                
        }
        
        if (!empty($result)) {
            asort($result);
            self::$Cache->set($label, $result);
        }
        return $result;
    }
    
    
    function getCitiesData($countryId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "city-data-{$countryId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
        else {
            $result=[];
            if ($this->slaveOfRedis) {
                return $result;
            }
        }
        
        $cities = $this->asCitiesDictionary();
        //$f=($lang=='ar')?1:2;
        $name='name_'.$lang;
        
        $resource = $this->ql->getConnection()->query("select groupby(), sum(counter), max(unixtime) from section_counts where country_id={$countryId} and city_id>0 group by city_id limit 1000");        
        if ($resource instanceof \mysqli_result) {        
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($countryId, $row[0], 0, 0, $lang);
                $result[$row[0]]=['name'=>$cities[$row[0]][$name], 'counter'=>$row[1], 'unixtime'=>$row[2], 
                                  'uri'=>$cities[$row[0]][\Core\Data\Schema::BIN_URI], 'latitude'=>$cities[$row[0]][\Core\Data\Schema::BIN_LATITUDE], 
                                  'longitude'=>$cities[$row[0]][\Core\Data\Schema::BIN_LONGITUDE], 'purposes'=>$purposes];                
            }
            $resource->free_result();                
        }
        
        if (!empty($result)) {
            asort($result);
            self::$Cache->set($label, $result);
        }
        return $result;        
    }
    
    
    function asRootsData(int $countryId, int $cityId, string $lang) : array {
        $as=NoSQL::instance();
        $label="root-dat-{$countryId}-{$cityId}-{$lang}";
        $key=$as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
        $rs=$as->getBins($key, ['data']);
        if (\is_array($rs) && !empty($rs)) { return $rs['data']; }
        
        $q = "select groupby(), root_name_{$lang}, sum(counter), max(unixtime) from section_counts ";
        if ($cityId) {
            $q.="where city_id={$cityId} ";
        } 
        elseif ($countryId) {
            $q.="where country_id={$countryId} and city_id=0 ";
        }
        $q.='group by root_id order by root_id asc limit 1000';
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new \Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {    
            while ($row = $resource->fetch_array()) {
                $purposes = $this->asPurpusesData($countryId, $cityId, $row[0], 0, $lang);
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2]+0, 'unixtime'=>$row[3]+0, 'purposes'=>$purposes];                
            }
            $resource->free_result();                
        }
        
        $as->setBins($key, ['data'=>$result]);
        return $result;        
    }
    
    
    // deprecated
    function getRootsData($countryId, $cityId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "root-data-{$countryId}-{$cityId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }

        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }

        $q = "select groupby(), root_name_{$lang}, sum(counter), max(unixtime) from section_counts ";
        if ($cityId||$countryId) $q.="where ";
        if ($cityId) {
            $q.="city_id={$cityId} ";
        } elseif ($countryId) {
            $q.="country_id={$countryId} and city_id=0 ";
        }
        $q.="group by root_id order by root_id asc limit 1000";
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($countryId, $cityId, $row[0], 0, $lang);
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3], 'purposes'=>$purposes];                
            }
            $resource->free_result();                
        }
        if (!empty($result)) {
            self::$Cache->set($label, $result);
        }
        return $result;
    }
    
    
    function asSectionsData(int $countryId, int $cityId, int $rootId, string $lang, bool $sortByCount=false) : array {
        $label = $sortByCount ? "section-dat-{$countryId}-{$cityId}-{$rootId}-{$lang}-c" : "section-dat-{$countryId}-{$cityId}-{$rootId}-{$lang}";
        $key = $this->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
        $rs=$this->as->getBins($key, ['data']);
        if (\is_array($rs) && !empty($rs)) { return $rs['data']; }
        
        $result=[];              
        $q = "select groupby(), section_name_{$lang}, sum(counter) as count, max(unixtime) from section_counts where root_id={$rootId} ";
        if ($cityId>0) {
            $q.="and city_id={$cityId} ";
        } 
        elseif ($countryId>0) {
            $q.="and country_id={$countryId} and city_id=0 ";
        }
        
        $sort_field = $sortByCount ? "count desc" : "section_name_{$lang} asc";
        $q.="group by section_id order by {$sort_field} limit 1000";
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new \Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        $keys=['root'=>$this->as->initLongKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_ROOT, $rootId)];
        if ($resource instanceof \mysqli_result) {     
            while ($row = $resource->fetch_array()) {
                $pl="purpose-dat-{$countryId}-{$cityId}-{$rootId}-{$row[0]}-{$lang}";
                $keys[$pl]=$this->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $pl);
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2]+0, 'unixtime'=>$row[3]+0, 'purposes'=>null];                
            }
            $resource->free_result();                
        }
        
        $status=$this->as->getConnection()->getMany(\array_values($keys), $pas);
        if ($status===\Aerospike::OK) {            
            foreach ($pas as $v) {
                $vkey=$v['key'];
                if ($vkey['set']==='root') {
                    if (isset($result[$v['bins'][\Core\Data\Schema::ROOT_DIFFER_SECTION_ID]])) {
                        $to_move=$result[$v['bins'][\Core\Data\Schema::ROOT_DIFFER_SECTION_ID]];
                        unset($result[$v['bins'][\Core\Data\Schema::ROOT_DIFFER_SECTION_ID]]);
                        $result[$v['bins'][\Core\Data\Schema::ROOT_DIFFER_SECTION_ID]]=$to_move;
                    }
                }
                else {
                    $keys[$vkey['key']]=$v['bins']['data'];   
                }
            }
            unset($keys['root']);
        }
        foreach ($result as $k => $v) {
            $pl="purpose-dat-{$countryId}-{$cityId}-{$rootId}-{$k}-{$lang}";
            $result[$k]['purposes']=$keys[$pl];
        }
        
        if (!empty($result)) {
            /*
            $roots = $this->getRoots();
            $df = $roots[$rootId][4];
            if (isset($result[$df])) {
                $tdf = $result[$df];
                unset($result[$df]);
                $result[$df] = $tdf;
            }*/
            //self::$Cache->set($label, $result);
            $this->as->setBins($key, ['data'=>$result]);
        }
        else {
            \error_log(__FUNCTION__.' empty query result from sphinx!');
        }

        
        return $result;
    }
    
    
    // deprecated
    function getSectionsData(int $countryId, int $cityId, int $rootId, string $lang, bool $sortByCount=false) : array {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = $sortByCount ? "section-data-{$countryId}-{$cityId}-{$rootId}-{$lang}-c-{$vv}" : "section-data-{$countryId}-{$cityId}-{$rootId}-{$lang}-{$vv}";
        $rs = self::$Cache->get($label);
        
        if ($rs!==FALSE) {
            return $rs;
        }
          
        $result=[];              
        $q = "select groupby(), section_name_{$lang}, sum(counter) as count, max(unixtime) from section_counts where root_id={$rootId} ";
        if ($cityId>0) {
            $q.="and city_id={$cityId} ";
        } 
        elseif ($countryId>0) {
            $q.="and country_id={$countryId} and city_id=0 ";
        }
        
        $sort_field = $sortByCount ? "count desc" : "section_name_{$lang} asc";
        $q.="group by section_id order by {$sort_field} limit 1000";
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new \Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        $keys=[];
        $as=NoSQL::instance();
        if ($resource instanceof \mysqli_result) {     
            while ($row = $resource->fetch_array()) {
                $pl="purpose-dat-{$countryId}-{$cityId}-{$rootId}-{$row[0]}-{$lang}";
                $keys[$pl]=$as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $pl);
                //$purposes = $this->asPurpusesData($countryId, $cityId, $rootId, $row[0], $lang);
                //var_dump($purposes);
                //$result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3], 'purposes'=>$purposes];                
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3], 'purposes'=>null];                
            }
            $resource->free_result();                
        }
        $as->getConnection()->getMany(\array_values($keys), $pas);
        foreach ($pas as $v) {
            $keys[$v['key']['key']]=$v['bins']['data'];            
        }
        
        //var_dump($keys);
        foreach ($result as $k => $v) {
            $pl="purpose-dat-{$countryId}-{$cityId}-{$rootId}-{$k}-{$lang}";
            $result[$k]['purposes']=$keys[$pl];
        }
        if (!empty($result)) {
            $roots = $this->getRoots();
            $df = $roots[$rootId][4];
            if (isset($result[$df])) {
                $tdf = $result[$df];
                unset($result[$df]);
                $result[$df] = $tdf;
            }
            self::$Cache->set($label, $result);
        }
        else {
            \error_log(__FUNCTION__.' empty query result from sphinx!');
        }

        return $result;
    }
    
    
    function asPurpusesData(int $countryId, int $cityId, int $rootId, int $sectionId, string $lang) : array {
        $label = "purpose-dat-{$countryId}-{$cityId}-{$rootId}-{$sectionId}-{$lang}";
        $as = NoSQL::instance();
        $key = $as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
        $rs=$as->getBins($key, ['data']);        
        return (\is_array($rs) && !empty($rs)) ? $rs['data'] : [];
    }
    
    
    function getPurpusesData($countryId, $cityId, $rootId, $sectionId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "purpose-data-{$countryId}-{$cityId}-{$rootId}-{$sectionId}-{$lang}-{$vv}";
        
        $result = self::$Cache->get($label);
        if ($result!==FALSE) { return $result; }
                
        $result=array();
        if ($this->slaveOfRedis) { return $result; }

        $q = "select groupby(), purpose_name_{$lang}, sum(counter), max(unixtime) from section_counts ";
        $and='where';
        if ($sectionId) {
            $q.="{$and} section_id={$sectionId} ";
            $and='and';
        } 
        elseif($rootId) {
            $q.="{$and} root_id={$rootId} ";
            $and='and';
        }
        if ($cityId) {
            $q.="{$and} city_id={$cityId} ";
            $and='and';
        } 
        elseif ($countryId) {
            $q.="{$and} country_id={$countryId} and city_id=0 ";
        }
        $q.="group by purpose_id order by purpose_name_{$lang} asc limit 1000";
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {             
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3]];
                
            }
            $resource->free_result();                
        }
        if (!empty($result)) {
            self::$Cache->set($label, $result);
        }
        return $result;
    }
    
    
    function getSectionTagsData($countryId, $cityId, $sectionId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$TagsVersion : self::$TagsVersion+1;
        $label = "tag-data-{$countryId}-{$cityId}-{$sectionId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }

        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }
        
        $q = "select section_tag_id, section_tag_name, counter, unixtime, uri from section_tag_counts where section_id={$sectionId} and lang='{$lang}' ";
        if ($cityId) {
            $q.="and city_id={$cityId} ";
        } elseif ($countryId) {
            $q.="and country_id={$countryId} and city_id=0 ";
        }
        $q.="order by section_tag_name asc limit 1000";
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {
            $p = $this->getPurposes(FALSE);
            while ($row = $resource->fetch_array()) {
                $purposes=$this->getExtendPurposesData($countryId, $cityId, 0, $row[0], $lang, $p);
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3], 'uri'=>$row[4], 'purposes'=>$purposes];
                
            }
            $resource->free_result();                
        }
        if (!empty($result)) {
            self::$Cache->set($label, $result);
        }
        return $result;
    }


    function getLocalitiesData($countryId, $sectionId, $parentId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$LocalitiesVersion : self::$LocalitiesVersion+1;
        $label = "locality-data-{$countryId}-{$sectionId}-{$parentId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
        
        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }
        
        $q = "select locality_id, locality_name, city_id, parent_id, counter, unixtime, uri, geo_parent_id from locality_counts where country_id={$countryId} and lang='{$lang}' and section_id={$sectionId} ";
        if ($parentId!=NULL) {
            $q.="and parent_id={$parentId} ";
        }
        $q.="order by locality_name asc limit 1000";

        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }

        if ($resource instanceof \mysqli_result) {
            $p = $this->getPurposes(FALSE);
            while ($row = $resource->fetch_array()) {
                $purposes=$this->getExtendPurposesData($countryId, 0, $row[0], 0, $lang, $p);
                $result[$row[0]]=['name'=>$row[1], 'city_id'=>$row[2], 
                    'parent_city_id'=>$row[3], 'counter'=>$row[4], 'unixtime'=>$row[5], 
                    'uri'=>$row[6], 'parent_geo_id'=>$row[7], 'purposes'=>$purposes];

            }
            $resource->free_result();
        }
        if (!empty($result)) {
            self::$Cache->set($label, $result);
        }
        return $result;
    }
    
    
    function getExtendPurposesData($countryId, $cityId, $localityId, $tagId, $lang, $purposes) {
        $vv = ($this->slaveOfRedis) ? self::$LocalitiesVersion : self::$LocalitiesVersion+1;
        $label = "extended-purposes-{$countryId}-{$cityId}-{$localityId}-{$tagId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) { return $result; }
                
        $f = strtolower($lang)=='ar'?1:2;
        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }

        $q = "select groupby(), count(*), max(date_added) from ad where hold=0 and canonical_id=0 ";
        if ($localityId) {
            $q.="and locality_id={$localityId} ";
        } 
        elseif ($tagId) {
            $q.="and section_tag_id={$tagId} ";
        }
        if ($cityId) {
            $q.="and city_id={$cityId} ";
        } 
        elseif ($countryId) {
            $q.="and country_id={$countryId} ";
        }
        $q.="group by purpose_id order by purpose_id asc limit 20";

        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {             
                $result[$row[0]]=['name'=>$purposes[$row[0]][$f], 'counter'=>$row[1], 'unixtime'=>$row[2]];                
            }
            $resource->free_result();                
        }
        if (!empty($result)) {
            self::$Cache->set($label, $result);
        }
        return $result;        
    }
    
   
}


class FBQuery {
    const FB_USLEEP         = 1;
    const FB_DIRECT_COMMIT  = 2;
    const FB_MAX_RETRY      = 3;
    const FB_FETCH_MODE     = 4;

    protected $query;
    protected $statement;
    protected $sleepMicroSeconds;
    protected $fetchMode;
    protected $single;
    protected $maxTrials;
    protected $params;
    protected $result;
    
    protected $owner;
    
    private $isReturningMode; 
    private $isWriteMode;
    
    function __construct(DB $db, string $query='', $params, array $options=[]) {        
        $this->owner = $db;
        $this->query = trim($query);
        $this->params = $params;
        $this->sleepMicroSeconds = $options[FBQuery::FB_USLEEP] ?? 200;
        $this->fetchMode = $options[FBQuery::FB_FETCH_MODE] ?? \PDO::FETCH_ASSOC;
        $this->single = $options[FBQuery::FB_DIRECT_COMMIT] ? TRUE : FALSE;
        $this->maxTrials = $options[FBQuery::FB_MAX_RETRY] ?? 5;
        
        if ($this->single===FALSE) { $this->maxTrials = 1; }
        
        $this->isWriteMode = preg_match('/^(insert|update|delete|execute)/i', $this->query);
        $this->isReturningMode = preg_match('/^(select)/i', $this->query) || (preg_match("/\sreturning\s/i", $this->query) && $this->isWriteMode);                
    }
   
    
    function __destruct() {
        if ($this->statement instanceof \PDOStatement) {            
            unset($this->statement);
        }
    }

    private function unprepare() {
        if ($this->statement instanceof \PDOStatement) {
            unset($this->statement);        
            $this->statement = null;
        }
    }

    private function prepare() : bool {        
        try {
            if ($this->isWriteMode && DB::isReadOnly()) {
                $this->unprepare();                
                $this->owner->setWriteMode(TRUE);
                $this->single = TRUE;
                $this->maxTrials = 5;                
            }
            
            if ($this->statement && $this->statement instanceof \PDOStatement) {
                return TRUE;
            }
   
            $this->statement = $this->owner->getInstance()->prepare($this->query);
            return TRUE;
        }
        catch (PDOException $ex) {
            $this->unprepare();
            error_log($ex->getMessage());
        }
        return FALSE;
    }
    
    
    private function execute() : bool {
        $success = FALSE;
        $trial=0;
        do {
            $trial++;
            if ($this->prepare()) {
                try {
                    $executed = $this->statement->execute($this->params);
                    if ($executed && $trial>1) {
                        NoSQL::Log(['query'=>$this->query, 'iteration'=>$trial]);
                    }
                    return $executed;
                } 
                catch (\Exception $ex) {
                    $this->unprepare();
                    if (preg_match('/913 deadlock/', $ex->getMessage())) {
                        usleep($this->sleepMicroSeconds);
                    }
                    else {
                        error_log('CODE retry no: '.$trial.'/'.$ex->getCode().' | '.$ex->getMessage().PHP_EOL.$this->query.PHP_EOL.var_export($this->params, TRUE));
                        if ($this->single) {
                            $this->owner->rollBack();
                        }
                        break;
                    }
                }
            }
        } while ($trial<$this->maxTrials);
        $this->unprepare();
        return FALSE;
    }
    
    
    public function get() {
        $this->result = false;
        if ($this->execute()) {
            $this->result = ($this->isReturningMode) ? $this->statement->fetchAll($this->fetchMode) : TRUE;
            if ($this->statement instanceof \PDOStatement) {
                $this->statement->closeCursor();
            }
            if ($this->single) { $this->owner->commit(); }
        }
        if ($this->single) { $this->unprepare(); }
        return $this->result;
    }
    
}

?>
