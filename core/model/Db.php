<?php
include_once dirname(__DIR__).'/lib/MCCache.php';
include_once dirname(__DIR__).'/lib/SphinxQL.php';

class DB 
{

    private static $Instance;    
    private static $Cache;
    private static $user, $pass, $dbUri;

    private static $Readonly;
    private static $IsolationLevel;
    private static $WaitTimeout;
    
    public static $SectionsVersion;
    public static $TagsVersion;
    public static $LocalitiesVersion;
    
    private $slaveOfRedis;
    public $ql;
    
    public function __construct($cfg, $readonly=TRUE) 
    {
        $this->slaveOfRedis = (get_cfg_var('mourjan.server_id')!='1');
        self::$dbUri = 'firebird:dbname='.$cfg['db_host'].':'.$cfg['db_name'].';charset=UTF8';
        self::$user = $cfg['db_user'];
        self::$pass = $cfg['db_pass'];
        self::$WaitTimeout = 10;
        
        $this->setTransactionIsolation($readonly);

        self::getCacheStorage($cfg);
        //if (self::$Cache === NULL)
        //{
        //    self::$Cache = new MCCache($cfg);
        //}
        
        self::$SectionsVersion=FALSE;
        self::$TagsVersion=FALSE;
        self::$LocalitiesVersion=FALSE;

        $versions=self::$Cache->getMulti(['section-counts-version', 'locality-counts-version', 'tag-counts-version']);

        self::$SectionsVersion = $versions['section-counts-version'];
        self::$LocalitiesVersion = $versions['locality-counts-version'];
        self::$TagsVersion = $versions['tag-counts-version'];
        
        $this->ql = new SphinxQL($cfg['sphinxql'], $cfg['search_index']);    
    }


    public static function getCacheStorage($config)
    {
        if (!isset(self::$Cache)) {
            self::$Cache = new MCCache($config);
        }
        return self::$Cache;
    }


    public function __destruct() {
        $this->close();        
    }

    
    public function setWriteMode($on=TRUE) {
        $this->setTransactionIsolation(!$on);
    }
    
    
    
    private function setTransactionIsolation($read) {
        if ($read!=self::$Readonly) {
            $this->commit();
        }
        
        
        if ($read) {
            self::$Readonly=TRUE;
            self::$IsolationLevel = PDO::FB_TRANS_CONCURRENCY;
            self::$WaitTimeout = 0;
        } else {
            self::$Readonly=FALSE;
            self::$IsolationLevel = PDO::FB_TRANS_COMMITTED;
            self::$WaitTimeout=10;       
        }
    }
    
    
    public function inTransaction() {
        if (self::$Instance === NULL) {
            return FALSE;
        }
        return self::$Instance->inTransaction();
    }
    
    
    public function commit($restartTransaction=FALSE) {
        if($this->inTransaction()) {
            try {
                self::$Instance->commit();
                if ($restartTransaction==TRUE) {
                    self::$Instance->beginTransaction();           
                }
            } catch (Exception $ex) {
                error_log($ex->getMessage());
            }
        }
        return FALSE;
    }
    
    
    private function newInstance() 
    {
        self::$Instance = new PDO(self::$dbUri, self::$user, self::$pass,
                    array(
                        PDO::ATTR_PERSISTENT=>TRUE,
                        PDO::ATTR_AUTOCOMMIT=>FALSE,
                        PDO::ATTR_EMULATE_PREPARES=>FALSE,
                        PDO::ATTR_STRINGIFY_FETCHES=>FALSE,
                        PDO::ATTR_TIMEOUT=>5,
                        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,

                        PDO::FB_ATTR_COMMIT_RETAINING=>FALSE,
                        PDO::FB_ATTR_READONLY=>self::$Readonly,
                        PDO::FB_TRANS_ISOLATION_LEVEL=>self::$IsolationLevel,
                        PDO::FB_ATTR_TIMEOUT=>self::$WaitTimeout
                    )
                );
    }


    public function getInstance() {
       if (self::$Instance === NULL) {            
            $this->newInstance();
            if (!self::$Instance->inTransaction()) {
                //error_log("new Instance beginTransaction Read: ". (self::$Readonly ? 'YES' : 'NO') ." Wait timeout: ".self::$WaitTimeout. PHP_EOL);
                self::$Instance->beginTransaction();
            }
            /*
            try {
                $res = $this->getInstance()->query("select 1 from rdb\$database");
                if ($res===FALSE) {

                } else {
                    $res->closeCursor();
                }
            } catch (Exception $e){
                error_log($e->getMessage());
                $this->newInstance();
            }
             * 
             */
        } else {            
            if (!self::$Instance->inTransaction()) {   
                //error_log("old Instance beginTransaction Read: ". (self::$Readonly ? 'YES' : 'NO') ." Wait timeout: ".self::$WaitTimeout. PHP_EOL);
                self::$Instance->setAttribute(PDO::FB_ATTR_READONLY, self::$Readonly);                
                self::$Instance->setAttribute(PDO::FB_TRANS_ISOLATION_LEVEL, self::$IsolationLevel);
                self::$Instance->setAttribute(PDO::FB_ATTR_TIMEOUT, self::$WaitTimeout);
                self::$Instance->beginTransaction();
            } 
        }
        //error_log(self::getTransactionIsolationMessage());
        return self::$Instance;
    }
    
    
    public static function getTransactionIsolationMessage() {
    	return 'FB Transaction mode [Isolation: ' . self::$IsolationLevel .', Read only: ' . (self::$Readonly ? 'YES' : 'NO') . ', Wait timeout: ' . self::$WaitTimeout . ' seconds]' . PHP_EOL;
    }
    
    
    public static function getCache() {
        return self::$Cache;
    }
    

    function close(){
        if ($this->inTransaction()) {
            try {
                self::$Instance->commit();
            } catch (Exception $ex) {
                error_log("Db commit: " . $ex->getMessage() . PHP_EOL);
                self::$Instance->rollBack();
            }
        }
        self::$Instance=NULL;
    }	


    function checkCorrectWriteMode($query){
        if (stristr($query, "update ") || stristr($query, "insert ") || stristr($query, "execute procedure") || stristr($query, "delete ") ){
            $this->setTransactionIsolation(false);
        }
    }


    function queryResultArray($query, $params=null, $commit=false, $fetch_mode=PDO::FETCH_ASSOC, $runtime=0) {
        $this->checkCorrectWriteMode($query);
        $this->getInstance();
        $result=array();
        
        try {
            $stmt = $this->getInstance()->prepare($query);
            if ($params)
                $stmt->execute($params);
            else                      
                $stmt->execute();
            if ($stmt) {
                $query = trim($query);
                if (!stristr($query, " returning ") && ( preg_match('/^update/', $query) ||preg_match('/^insert/', $query) || stristr($query, "execute procedure") || preg_match('/^delete/', $query) ) ){
                    $result = TRUE;
                }else {

                    if ($fetch_mode==PDO::FETCH_NUM) {
                        if (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
                            $count = count($row);
                            do {
                                for ($i=0; $i < $count; $i++)
                                    if(is_numeric($row[$i])) $row[$i] = $row[$i] + 0;
                                $result[]=$row;
                                //break;
                            } while($row = $stmt->fetch(PDO::FETCH_NUM));
                        }
                    } else $result = $stmt->fetchAll($fetch_mode);
                }
            }
            if ($commit) {                
                self::$Instance->commit();
                $stmt=null;
            }
                    
        } catch (Exception $ex) {
            self::$Instance->rollBack();          
            $result=FALSE;
            if( (strpos($ex->getMessage(), '913 deadlock') > -1) && $runtime < 5){
                //error_log('RETRY: '. $runtime . ' | CODE: '. $ex->getCode() . ' | '.$ex->getMessage() . PHP_EOL . $query . PHP_EOL . var_export($params, TRUE));
                usleep(100);
                return $this->queryResultArray($query, $params, $commit, $fetch_mode, $runtime+1);
            }else{
                error_log('CODE: '. $ex->getCode() . ' | '.$ex->getMessage() . PHP_EOL . $query . PHP_EOL . var_export($params, TRUE));
            }
        }
        return $result;
    }
    
    
    function stmtCacheResultSimpleArray($label, $stmt, $params=null, $key=0, $lifetime=86400, $forceSetting=false){
        $records=array();
        $foo = self::$Cache->get($label);
                        
        if ($forceSetting || $foo===FALSE) { 
            
            try {
                
                if ($params)
                    $stmt->execute($params);
                else
                    $stmt->execute();

                if (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
                    $count = count($row);
                    do {
                        for ($i=0; $i < $count; $i++)
                            if(is_numeric($row[$i])) $row[$i] = $row[$i] + 0;
                        if ($key>=0)
                            $records[$row[$key]]=$row;
                        else {
                            $records=$row;
                            break;
                        }
                    }
                    while($row = $stmt->fetch(PDO::FETCH_NUM));
                }
            }catch (Exception $ex) {
                error_log($ex->getMessage() . PHP_EOL . $stmt->queryString . PHP_EOL . var_export($params, TRUE));
                self::$Instance->rollBack();
                return false;
            }
      
            self::$Cache->set($label, $records);
                
            return $records;
        } else {
            return $foo;
        }              
    }
    
    
    function prepare($stmt, $q) {
        if (!$stmt || !$this->inTransaction()){
            $this->checkCorrectWriteMode($q);
            $stmt = $this->getInstance()->prepare($q);
        }
        return $stmt;
    }
    
    
    function prepareQuery($q) {
        $this->checkCorrectWriteMode($q);
        return $this->getInstance()->prepare($q);
    }
    
    
    function queryCacheResultSimpleArray($label, $query, $params=null, $key=0, $lifetime=86400, $forceSetting=false, $forceIfEmpty=false)
    {
        $records=array();        

        $foo = self::$Cache->get($label);
        
        if ($forceSetting || ($foo===FALSE) || ($forceIfEmpty && empty($foo))) {
            //error_log("Failed to load from cache: " . $label);
            //error_log($query);
            $this->checkCorrectWriteMode($query);
            $stmt = $this->getInstance()->prepare($query);
            
            try{
                if ($params)
                    $stmt->execute($params);
                else
                    $stmt->execute();

                if(($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
                    $count = count($row);
                    $simpleArray=is_null($key) ? true : false;
                    
                    do {
                        //error_log(var_export($row, TRUE));
                        for ($i=0; $i < $count; $i++)
                            if(is_numeric($row[$i])) $row[$i] = $row[$i]+0;

                        if($simpleArray){
                            $records[]=$row;
                        }else {
                            if ($key>=0)
                                $records[$row[$key]]=$row;
                            else {
                                $records=$row;
                                break;
                            }
                        }
                    }
                    while($row = $stmt->fetch(PDO::FETCH_NUM));
                    
                }
            }catch (Exception $ex) {
                error_log($ex->getMessage() . PHP_EOL . $query . PHP_EOL . var_export($params, TRUE));
                self::$Instance->rollBack();
                return false;
            }
            self::$Cache->set($label, $records);
            return $records;
        } else {
            return $foo;
        }        
    }
    
    
    function getSectionFollowUp($countryId,$cityId=0,$sectionId,$purposeId=0,$force=0){
        return $this->queryCacheResultSimpleArray(
            "follow_{$countryId}_{$cityId}_{$sectionId}_{$purposeId}", 
            "select to_section_id,to_purpose_id from section_follow s where 
                s.section_id = {$sectionId} and 
                s.country_id = {$countryId} and 
                s.city_id = {$cityId} and 
                s.purpose_id = {$purposeId} and 
                s.counter > 20 
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
    
    
    function getCountries($lang='ar',$force=0){
        return $this->queryCacheResultSimpleArray(
                'countries_'.$lang,
                'select c.ID, NAME_AR, NAME_EN, lower(trim(id_2)) URI, d.counter, d.UNIXTIME,c.currency_id,code 
                from country c
                left join counts d 
                    on c.id=d.country_id 
                    and d.city_id=0 
                    and d.root_id=0 
                    and d.section_id=0 
                    and d.purpose_id=0 
                    where c.blocked=0 
                    and d.counter>0 
                    order by NAME_'. $lang,
                    null, 0, 86400, $force);
    }
    
    
    function getCities($force=0){
        return $this->queryCacheResultSimpleArray(
                'cities',
                'select c.ID, c.NAME_AR, c.NAME_EN, c.URI, s.counter, s.UNIXTIME, c.COUNTRY_ID, c.LATITUDE, c.LONGITUDE
                from city c 
                left join counts s 
                        on s.country_id=c.country_id 
                        and s.city_id=c.id
                        and s.root_id=0 
                        and s.section_id=0 
                        and s.purpose_id=0 
                where s.counter>=0 
                and c.blocked=0',
                null, 0, 86400,$force);
    }
    
    
    function getCountryCities($countryId,$lang='ar',$force=0){
        return $this->queryCacheResultSimpleArray("cities_{$countryId}_{$lang}",
                "select c.ID 
                 from city c
                 where c.country_id={$countryId} 
                 and c.blocked=0
                 order by NAME_".  strtoupper($lang),
                null, 0, 86400,$force);
    }
    
    
    function queryQLCacheResultSimpleArray($label, $query, $params=null, $key=0, $lifetime=86400, $forceSetting=false, $forceIfEmpty=false){
        $records=array();        

        $foo = self::$Cache->get($label);

        if ($forceSetting || ($foo===FALSE) || ($forceIfEmpty && empty($foo))) {
            
            $this->checkCorrectWriteMode($query);
            $stmt = $this->getInstance()->prepare($query);
            
            try{
                if ($params)
                    $stmt->execute($params);
                else
                    $stmt->execute();

                if(($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {

                    $count = count($row);
                    $simpleArray=is_null($key) ? true : false;
                    do {
                        for ($i=0; $i < $count; $i++)
                            if(is_numeric($row[$i])) $row[$i] = $row[$i]+0;

                        if($simpleArray){
                            $records[]=$row;
                        }else {
                            if ($key>=0)
                                $records[$row[$key]]=$row;
                            else {
                                $records=$row;
                                break;
                            }
                        }
                    }
                    while($row = $stmt->fetch(PDO::FETCH_NUM));
                }
            }catch (Exception $ex) {
                error_log($ex->getMessage() . PHP_EOL . $query . PHP_EOL . var_export($params, TRUE));
                self::$Instance->rollBack();
                return false;
            }
            self::$Cache->set($label, $records);
            return $records;
        } else {
            return $foo;
        }        
    }

    /* New data block */
    function getCountriesDictionary($force=FALSE) {
        if(!$this->slaveOfRedis){
            $force = true;
        }
        $countries = $this->queryCacheResultSimpleArray('countries-dictionary',
                    'select ID, NAME_AR, NAME_EN, lower(trim(id_2)) URI, ' .
                    'trim(currency_id) currency_id, code, '.
                    '(select list(ID) from city where country_id=country.id and blocked=0) cities, '.
                    'LONGITUDE, LATITUDE, LOCKED, UNIXTIME ' .
                    'from country where blocked=0',
                    null, 0, 86400, $force);

        if (!empty($countries) && !is_array($countries[key($countries)][6])) {
            foreach ($countries as $country_id => $country) {
                $cities = explode(",", $country[6]);
                if (count($cities)>1) {
                    $countries[$country_id][6]=$cities;
                } else {
                    $countries[$country_id][6]=[];
                }
            }
            
            self::$Cache->set('countries-dictionary', $countries);
        }
        return $countries;
    }
    
    
    function getCitiesDictionary($force=FALSE) 
    {
        if(!$this->slaveOfRedis){
            $force = true;
        }
        return $this->queryCacheResultSimpleArray('cities-dictionary',
                    'select ID, NAME_AR, NAME_EN, URI, COUNTRY_ID, LATITUDE, LONGITUDE, locked, UNIXTIME from city where blocked=0',
                    null, 0, 86400, $force);        
    }
    
    
    function getPublications($force=FALSE) {
        if(!$this->slaveOfRedis){
            $force = true;
        }
        return $this->queryCacheResultSimpleArray('publications',
                    'select ID, NAME_AR, NAME_EN, BRAND_AR, BRAND_EN, WEBSITE, URL, country_id, city_id, language, period, ad_price, currency_id   
                    from publication where blocked=0 order by BRAND_EN',
                    null, 0, 86400, $force);    
    }
    
    
    function getPurposes($force=FALSE) {
        if(!$this->slaveOfRedis){
            $force = true;
        }
        return $this->queryCacheResultSimpleArray('purposes',
                    'select ID, NAME_AR, NAME_EN, URI, UNIXTIME from purpose where blocked=0',
                    null, 0, 86400, $force);
    }
    
    
    function getRoots($force=FALSE) {
        if(!$this->slaveOfRedis){
            $force = true;
        }
        return $this->queryCacheResultSimpleArray('roots',
                    'select ID, NAME_AR, NAME_EN, URI, DIFFER_SECTION_ID, BLOCKED, UNIXTIME from root order by 1',
                    null, 0, 86400,  $force);
    }
    
    
    function getSections($forceSetting=false){
        $label = 'sections';
        $records = false;
        if(!$forceSetting){
            $records = self::$Cache->get($label);
        }
        
        if ($forceSetting || $records===FALSE) {
            $records = $this->ql->directQuery(
                    "select id, section_name_ar as name_ar, section_name_en as name_en, "
                    . " section_uri as uri, root_id, "
                    . " related_id, related, purposes, related_purpose_id, related_to_purpose_id "
                    . "from section limit 100000");
        }
        
        self::$Cache->set($label, $records);        
        return $records;
    }

        
    function getCountriesData($lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "country-data-{$lang}-{$vv}";        
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
        
        $result=array();
        /*
        if ($this->slaveOfRedis) {
        	error_log("Could not get v1:{$label} from cache!!!");
        	usleep(100);
        	$result = self::$Cache->get($label);
        	if ($result!==FALSE) {
        		error_log("Got v1:{$label} from cache after 0.1 ms!!!");
        	}
            return $result;
        }
        */
        $countries = $this->getCountriesDictionary();
        $f=($lang=='ar')?1:2;
        $resource = $this->ql->getConnection()->query("select groupby(), count(*), max(date_added) from ad group by country limit 1000");
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$label.']');
        }
        
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($row[0], 0, 0, 0, $lang);
                $result[$row[0]]=['name'=>$countries[$row[0]][$f], 'counter'=>$row[1], 'unixtime'=>$row[2], 
                                  'uri'=>$countries[$row[0]][3], 'currency'=>$countries[$row[0]][4], 'code'=>$countries[$row[0]][5],
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

        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }
        $cities = $this->getCitiesDictionary();
        $f=($lang=='ar')?1:2;
        $resource = $this->ql->getConnection()->query("select groupby(), sum(counter), max(unixtime) from section_counts where country_id={$countryId} and city_id>0 group by city_id limit 1000");
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($countryId, $row[0], 0, 0, $lang);
                $result[$row[0]]=['name'=>$cities[$row[0]][$f], 'counter'=>$row[1], 'unixtime'=>$row[2], 
                                  'uri'=>$cities[$row[0]][3], 'latitude'=>$cities[$row[0]][5], 
                                  'longitude'=>$cities[$row[0]][6], 'purposes'=>$purposes];                
            }
            $resource->free_result();                
        }
        if (!empty($result)) {
            asort($result);
            self::$Cache->set($label, $result);
        }
        return $result;
        
    }
    
    
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
    
    
    function getSectionsData($countryId, $cityId, $rootId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "section-data-{$countryId}-{$cityId}-{$rootId}-{$lang}-{$vv}";
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
        
        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }
        $q = "select groupby(), section_name_{$lang}, sum(counter), max(unixtime) from section_counts where root_id={$rootId} ";
        if ($cityId) {
            $q.="and city_id={$cityId} ";
        } elseif ($countryId) {
            $q.="and country_id={$countryId} and city_id=0 ";
        }
        $q.="group by section_id order by section_name_{$lang} asc limit 1000";
        
        $resource = $this->ql->getConnection()->query($q);
        if ($this->ql->getConnection()->error) {
            throw new Exception('['.$this->ql->getConnection()->errno.'] '.$this->ql->getConnection()->error.' [ '.$q.']');
        }
        
        if ($resource instanceof \mysqli_result) {                
            while ($row = $resource->fetch_array()) {
                $purposes = $this->getPurpusesData($countryId, $cityId, $rootId, $row[0], $lang);
                $result[$row[0]]=['name'=>$row[1], 'counter'=>$row[2], 'unixtime'=>$row[3], 'purposes'=>$purposes];                
            }
            $resource->free_result();                
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

        return $result;
    }
    
    
    function getPurpusesData($countryId, $cityId, $rootId, $sectionId, $lang) {
        $vv = ($this->slaveOfRedis) ? self::$SectionsVersion : self::$SectionsVersion+1;
        $label = "purpose-data-{$countryId}-{$cityId}-{$rootId}-{$sectionId}-{$lang}-{$vv}";

        
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
            return $result;
        }
                
        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }

        $q = "select groupby(), purpose_name_{$lang}, sum(counter), max(unixtime) from section_counts ";
        $and='where';
        if ($sectionId) {
            $q.="{$and} section_id={$sectionId} ";
            $and='and';
        } elseif($rootId) {
            $q.="{$and} root_id={$rootId} ";
            $and='and';
        }
        if ($cityId) {
            $q.="{$and} city_id={$cityId} ";
            $and='and';
        } elseif ($countryId) {
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
        //error_log($label);
        $result = self::$Cache->get($label);
        if ($result!==FALSE) {
                return $result;
        }
        
        
        $f = strtolower($lang)=='ar'?1:2;
        $result=array();
        if ($this->slaveOfRedis) {
            return $result;
        }

        $q = "select groupby(), count(*), max(date_added) from ad where hold=0 and canonical_id=0 ";
        if ($localityId) {
            $q.="and locality_id={$localityId} ";
        } elseif($tagId) {
            $q.="and section_tag_id={$tagId} ";
        }
        if ($cityId) {
            $q.="and city_id={$cityId} ";
        } elseif ($countryId) {
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

?>