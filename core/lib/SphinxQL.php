<?php

class SphinxQL {
    
    private $indexName;
    private $server;
    
    public $connection = null;
    private $_sphinx = null;
    private $clause = '*';
    private $sortby = '';
    
    private $offset = 0;
    private $limit = 10;
    private $max_matches = 1000;
    private $filters = array();
    private $groupby = array();
    private $facets = array();
    public $metaData = null;
    public $_query;
    public $_batch = array();
    
    
    function __construct($host, $index, $port=0) {
        $this->indexName = $index;
        if (is_array($host)) {
            $this->server = $host;
            
        } else {
            if ($port==0) {
                $this->server = $host;
            } else {
                $this->server = "mysql:host={$host};port={$port};";            
            }
        }
        $this->connect();
    }
    
    
    function __destruct() {
        $this->connection=NULL;
        if ($this->_sphinx!=NULL) {
            try {
                $this->_sphinx->close();
            } catch (Exception $e) {
                error_log($e->getMessage()+" (Server {$this->server})");
            }
        }
    }
    
    
    function connect() {
        if ($this->_sphinx==NULL) {        	 	
            $this->_sphinx = new mysqli($this->server['host'], '', '', '', $this->server['port'], $this->server['socket']);
        	//if ($this->_sphinx->get_warnings())
        	//{
        		
           	//}
          	if ($this->_sphinx->connect_error) {
          	error_log($this->server['host']);
            	die('Connect Error ' . $this->server['host'] .' (' . $this->_sphinx->connect_errno . ') ' . $this->_sphinx->connect_error);
        	}
        }        
    }
        
    /**
    * Pings the Sphinx server.
    *
    * @return boolean True if connected, false otherwise
    */
    public function ping()
    {
        try {
            $this->getConnection();
        } catch (Exception $e) {
            $this->connect();
        }

        return $this->getConnection()->ping();
    }

    /**
     * Closes and unset the connection to the Sphinx server.
     */
    public function close()
    {
        $this->_sphinx->close();
        $this->_sphinx = null;
    }


    function setSelect($clause='*') {
        $this->clause = $clause;
    }
    
   
    /**
     * Returns the current \MySQLi connection established.
     *
     *
     * @return \MySQLi MySQLi connection
     * @throws Exception If no connection has been established or open
     */
    public function getConnection()
    {
        if ($this->_sphinx !== null) {
            return $this->_sphinx;
        }

        throw new Exception('The connection to the server has not been established yet.');
    }

    
    function setFilter($attribute, $values, $exclude=FALSE) {
        assert(is_string($attribute));
        $condition="";
        
        if ($exclude) {
            if (is_array($values)) {
                if (count($values)>1) {
                    $condition = " not in (". implode(",", $values) . ")";
                } else {
                    $condition = "<>{$values[0]}";
                }
            } else {
                $condition = "<>{$values}";
            }
            
        } else {
            if (is_array($values)) {
                if (count($values)>1) {
                    $condition = " in (". implode(",", $values) .")";                    
                } else {
                    $condition = "={$values[0]}";
                }
            } else {
                $condition = "={$values}";
            }
        }
        $this->filters[$attribute] = $condition;
    }
    
    
    function setFilterRange($attribute, $min, $max, $exclude=FALSE) {
        assert(is_string($attribute));
        assert(is_numeric($min));
        assert(is_numeric($max));
        assert($min<=$max);
        if ($exclude) {
            $this->filters[$attribute]=" not between {$min} and {$max}";
        } else {
            $this->filters[$attribute]=" between {$min} and {$max}";
        }
    }
    
    
    function setFilterCondition($attribute, $condition, $value) {
        if (is_array($value)) {
            $this->filters[$attribute]=" {$condition} (".implode(",", $value).")";
        } else {
            $this->filters[$attribute]="{$condition} {$value}";
        }
    }
    
    
    function resetFilters($ressetGroupBy=TRUE) {
        $this->filters = array();
        $this->groupby = array();
        $this->facets = array();
    }
    
    
    function setGroupBy($attribute) {
        $attribute = strtolower($attribute);
        if (!in_array($attribute, $this->groupby)) {
            $this->groupby[]=$attribute;
        }
    }
    
    
    function clearFacets() {
        $this->facets = array();
    }
    
    
    function setFacet($facet, $clearALL=FALSE) {
        if ($clearALL)            
            $this->facets = array();
        $facet = strtolower($facet);
        $this->facets[$facet]="facet {$facet} limit 10000";
       
    }
    
    
    function addDirectQuery($name, $q, $assoc=FALSE) {
        $this->_batch[$name] = [$q, $assoc];
    }
    
    
    function addQuery($name, $keywords='', $assoc=FALSE) {
        $this->build($keywords);
        $this->_batch[$name] = [$this->_query, $assoc];

        $this->_query="";
    }
    
    
    function execute($queryQL) {           
        return ($this->_sphinx->multi_query($queryQL.'; SHOW META;'));
    }
    
    function directQuery($queryQL, $fetchMode=MYSQLI_NUM){
        $records = array();
        try {
            if ($this->_sphinx->multi_query($queryQL)) {
                do {
                    $i= 0;
                    if ($rs = $this->_sphinx->store_result()) {
                        while($row = $rs->fetch_row()){
                            $row[0] = (int)$row[0];
                            $records[$i][$row[0]] = $row;
                        }
                        $i++;
                        $rs->free();
                    }
                    if(!$this->_sphinx->more_results()){
                        break;
                    }
                } while ($this->_sphinx->next_result());
            }
            
            if (count($records)==1) {
                $records = $records[0];
            }
        } catch (Exception $ex) {
            error_log( $ex->getMessage() );
        } 
        
        return $records;
    }
    
    function search($queryQL, $fetchMode=MYSQLI_ASSOC) {
        $result = array('error' => '', 'warning' => '', 'total' => 0, 'total_found' => 0, 'time' => 0, 'matches' => array (), 'sql'=>$queryQL);
        try {
            if ($this->_sphinx->multi_query($queryQL)) {
                do {
                    if ($rs = $this->_sphinx->store_result()) {
                        $result['matches'][] = $rs->fetch_all($fetchMode);
                        $rs->free();
                    }
                    if(!$this->_sphinx->more_results()){
                        break;
                    }
                } while ($this->_sphinx->next_result());
            }
            
            if (count($result['matches'])==1) {
                    $result['matches']=$result['matches'][0];
            }
        } 
        catch (Exception $ex) 
        {            
            error_log( $ex->getMessage() );
            error_log($queryQL);
        } finally {
            $this->fetchMetaData($result);
        }
        //error_log('Search: ' . PHP_EOL . var_export($result, TRUE) . PHP_EOL);
        return $result;
    }
    
    
    function query($keywords="", $fetchMode=MYSQLI_ASSOC) {        
        $this->build($keywords);
        return $this->search($this->_query, $fetchMode);
    }
    
    
    function executeBatch($fullRow=FALSE) {
        $result = [];        
        foreach ($this->_batch as $name => $info) {
            $q = $info[0];
            $assoc = $info[1];
            $rs = array('error' => '', 'warning' => '', 'total' => 0, 'total_found' => 0, 'time' => 0, 'matches' => array (), 'facet'=>array(), 'sql'=>$q);
            $resource = $this->_sphinx->multi_query($q);
            if ($this->_sphinx->error) {
                $rs['error'] = '['.$this->_sphinx->errno.'] '.$this->_sphinx->error.' [ '.$q.']';
                error_log(PHP_EOL . $rs['error'] . PHP_EOL);
                $result[$name] = $rs;
                continue;
            }
            $facet_index = -1;
            do {
                if ($res = $this->_sphinx->store_result()) {
                    $field_names = mysqli_fetch_fields ($res);
                    if ($fullRow||$assoc) {
                        while ($row=$res->fetch_assoc()) {
                            
                            $rs['matches'][ $row[$field_names[0]->name] ]=$row;
                        }                        
                    } else {
                        if ($field_names[0]->name=='id') {
                            while ($row=$res->fetch_assoc()) {
                                $rs['matches'][]=$row['id'];
                            }
                        } else {
                            $facet_index++;
                            while ($row=$res->fetch_assoc()) {
                                $rs['facet'][$facet_index]=$row;
                            }
                        }
                    }
                    $res->free();
                }
                
                if (!$this->_sphinx->more_results()) {
                    $this->fetchMetaData($rs);                    
                    $result[$name]=$rs;
                    //error_log(PHP_EOL.$name. ' Elapsed time '.$rs['time']);
                    break;
                }
            } while ($this->_sphinx->next_result());
            
        }
        return $result;
    }
    
    
    function singleSelectQuery($q, $assoc=FALSE) {
        $result = array('error' => '', 'warning' => '', 'total' => 0, 'total_found' => 0, 'time' => 0, 'matches' => array (), 'sql'=>  $q);
        try {
            $resource = $this->_sphinx->query($q);
            
            if ($this->_sphinx->error) {
                throw new Exception('['.$this->_sphinx->errno.'] '.$this->_sphinx->error.' [ '.$this->_query.']');
            }
            
            if ($resource instanceof \mysqli_result) {                
                $field_names = mysqli_fetch_fields ($resource);
                while ($row = $resource->fetch_assoc()) {
                    if ($assoc) {
                        $result['matches'][ $row[ $field_names[0]->name] ]=$row;
                    } else {
                        $result['matches'][] = $row[$field_names[0]->name];
                    }
                }

                $resource->free_result();                
            }
            $this->fetchMetaData($result);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return FALSE;
        }
        return $result;
    }
    
    
    function getAds($keywords="") {        
        $this->build($keywords);
        $result = array('error' => '', 'warning' => '', 'total' => 0, 'total_found' => 0, 'time' => 0, 'matches' => array (), 'sql'=>  $this->_query);
        try {
            $resource = $this->_sphinx->query($this->_query);
            
            if ($this->_sphinx->error) {
                throw new Exception('['.$this->_sphinx->errno.'] '.$this->_sphinx->error.' [ '.$this->_query.']');
            }
            
            if ($resource instanceof \mysqli_result) {                

                while ($row = $resource->fetch_assoc()) {
                    $result['matches'][$row['id']] = $row;
                }

                $resource->free_result();

                
                
            }
            $this->fetchMetaData($result);
            // Sphinx doesn't return insert_id and only the number of rows affected.
            //return $this->_sphinx->affected_rows;
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return FALSE;
        }
        return $result;
    }
    
    

    
    function setLimits ($offset=0, $limit=10, $max = 1000) {
        assert( is_int($offset) );
        assert( is_int($limit) );
        assert( $offset>=0 );
        assert( $limit>=0 );
        assert( $max>0 );
        $this->offset = $offset+0;
        $this->limit = $limit+0;
    }
    
    
    function setSortBy ($sortby) {
        assert(is_string($sortby));
        $this->sortby = $sortby;
    }
    
    
    public function getLastError() {
        return (isset($this->metaData['error'])) ? $this->metaData['error'] : '';
    }
    
    
    function getLastWarning() {
        return (isset($this->metaData['warning'])) ? $this->metaData['warning'] : '';
    }
    
    
    function status() {
        $result = array();
        if ($rs = $this->_sphinx->query('SHOW STATUS')) {
            while ($value = $rs->fetch_array(MYSQLI_NUM)) {
                $result[$value[0]] = $value[1];
            }
            $rs->close();
        }
        return $result;
    }
    
    
    function updateAttributes ( string $index , array $attributes , array $values , $mva = false ) {
        echo "update Attributes deprecated!";
        if ($this->connection==NULL) {
            $this->connection = new PDO( $this->server, '', '' );
        }       
        return FALSE;
    }
    
    
    function updateHoldAd($id) {
        if ($this->_sphinx->real_query("update {$this->indexName} set hold=1 where id={$id} and hold=0")) {
            //echo "ad: {$id} has been set on hold state in index" . PHP_EOL;
        } else {
            //echo "Failed to set ad: {$id} on hold state!" . PHP_EOL;
        }
    }
    
    function directUpdateQuery($q) {
        if ($this->_sphinx->real_query($q)) {
            return $this->_sphinx->affected_rows;
        } else {
            return FALSE;
        }
    }
    

    /**
     * Escapes the input with \MySQLi::real_escape_string.
     * Based on FuelPHP's escaping function.
     *
     * @param string $value The string to escape
     *
     * @return string The escaped string
     * @throws Exception If an error was encountered during server-side escape
     */
    public function escape($value)
    {
        //$this->ping();

        if (($value = $this->_sphinx->real_escape_string((string) $value)) === false) {
            throw new Exception($this->_sphinx->error, $this->_sphinx->errno);
        }

        return "'".$value."'";
    }


    public function build($keywords="") {
        if ($this->indexName=='classifier') {
            $this->_query = "select {$this->clause} from {$this->indexName} where hold=0 ";
        } else {
            $this->_query = "select {$this->clause} from {$this->indexName} where hold=0 and canonical_id=0 ";
        }
        foreach ($this->filters as $key => $value) {
            $this->_query.="and {$key}{$value} ";
        }
        if ($keywords) {
            $keywords=$this->escape($keywords);
            $this->_query.="and match({$keywords}) ";
        }
        if (count($this->groupby)>0) {
            $this->_query.="group by " . implode(',', $this->groupby) . ' ';
        }
        if ($this->sortby) {
            $this->_query.="order by {$this->sortby} ";
        }
        $this->_query.="LIMIT {$this->offset}, {$this->limit} ";
        
        if ($this->max_matches) {
            $this->_query.="OPTION max_matches={$this->max_matches} "; 
        }

        if (count($this->facets)>0) {
            $this->_query.=implode(' ', array_values( $this->facets ));
        }
    }
    
    
    public function fetchMetaData(&$resultQuery=NULL) {
        if (($rs = $this->_sphinx->query('SHOW META'))!==FALSE) {
            while ($value = $rs->fetch_array(MYSQLI_NUM)) {
                $resultQuery[$value[0]] = $value[1];
            }
            $rs->close();
        }
    }
}
