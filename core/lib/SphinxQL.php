<?php
namespace Core\Lib;

class SphinxQL {
    const ID                = 'id';
    const UID               = 'user_id';
    const HOLD              = 'hold';
    const COUNTRY           = 'country';
    const CITY              = 'city';
    const CANONICAL         = 'canonical_id';

    const ROOT              = 'root_id';
    const SECTION           = 'section_id';
    const PURPOSE           = 'purpose_id';
    const LOCALITY          = 'locality_id';
    const TAG               = 'section_tag_id';
    const RTL               = 'rtl';
    // date_added | date_ended  | user_rank | impressions | section_name_ar | section_name_en | root_name_ar | root_name_en | attrs                                                                                                                                                                                                                                                                                                                                                                                                                                          | country | city | section_tag_id | locality_id     
    const MEDIA             = 'media';
    const STARRED           = 'starred';
    const PUBLISHER_TYPE    = 'publisher_type';
    const FEATURED_TTL      = 'featured_date_ended';
    
    private string $indexName;
    private array $server;
    
    private bool $noAttrs=false;
    private bool $fetchMeta=true;
    private ?\mysqli $_sphinx = null;
    private string $clause = '*';
    private string $sortby = '';
    
    private int $offset = 0;
    private int $limit = 10;
    private int $max_matches = 2000;
    private array $filters = [];
    private array $groupby = [];
    private array $facets = [];
    public ?array $metaData = null;
    public string $_query;
    public array $_batch = [];
    
    /**
     * An array of escaped characters for escapeMatch()
     * @var array
     */
    protected array $escape_full_chars = [
        '\\' => '\\\\',
        '(' => '\(',
        ')' => '\)',
        '|' => '\|',
        '-' => '\-',
        '!' => '\!',
        '@' => '\@',
        '~' => '\~',
        '"' => '\"',
        '&' => '\&',
        '/' => '\/',
        '^' => '\^',
        '$' => '\$',
        '=' => '\=',
        '<' => '\<',
    ];

    /**
     * An array of escaped characters for fullEscapeMatch()
     * @var array
     */
    protected array $escape_half_chars = [
        '\\' => '\\\\',
        '(' => '\(',
        ')' => '\)',
        '!' => '\!',
        '@' => '\@',
        '~' => '\~',
        '&' => '\&',
        '/' => '\/',
        '^' => '\^',
        '$' => '\$',
        '=' => '\=',
        '<' => '\<',
        '\'' => ' '
    ];


    function __construct(array $host, string $index) {
        \mysqli_report(\MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT);
        $this->indexName=$index;
        //if (\is_array($host)) {
            $this->server=$host;    
        //} 
        //else {
        //    $this->server = ($port==0) ? $host : "mysql:host={$host};port={$port};";
        //}
        //$this->connect();
    }
    
    
    function __destruct() {
        //$this->connection=NULL;
        if ($this->_sphinx!=NULL) {
            try {
                $this->_sphinx->close();
            } 
            catch (Exception $e) {
                $this->Log(['server'=> $this->server, 'Exception'=>$e]);
            }
        }
    }
    
    
    function connect() : void {
        if ($this->_sphinx==NULL) {
            $this->_sphinx=new \mysqli($this->server['host'], '', '', '', $this->server['port'], $this->server['socket']);
            if ($this->_sphinx->connect_error) {
                $this->Log(['host'=>$this->server['host'], 'error'=>'['.$this->_sphinx->connect_errno . '] ' . $this->_sphinx->connect_error]);
                die('Connect Error ' . $this->server['host'] .' (' . $this->_sphinx->connect_errno . ') ' . $this->_sphinx->connect_error);
            }
            else {
                $this->_sphinx->options(\MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
            }
        }
    }

    /**
    * Pings the Sphinx server.
    *
    * @return boolean True if connected, false otherwise
    */
    public function ping() {
        try {
            $this->getConnection();
        } 
        catch (Exception $e) {
            $this->connect();
        }

        return $this->getConnection()->ping();
    }

    /**
     * Closes and unset the connection to the Sphinx server.
     */
    public function close() : bool {        
        $result=$this->_sphinx->close();
        $this->_sphinx = null;
        return $result;
    }


    function setSelect(string $clause='*') : SphinxQL {
        $this->clause = $clause;
        return $this;
    }
    
   
    function skipAttributes(bool $value) : self {
        $this->noAttrs=$value;
        return $this;
    }
    
    
    function skipMetadata(bool $value) : self {
        $this->fetchMeta=!$value;
        return $this;
    }
    
    
    /**
     * Returns the current \MySQLi connection established.
     *
     *
     * @return \MySQLi MySQLi connection
     * @throws Exception If no connection has been established or open
     */
    public function getConnection() : ?\mysqli {
        if ($this->_sphinx !== null) {
            return $this->_sphinx;
        }

        throw new Exception('The connection to the server has not been established yet.');
    }

    
    public function id(int $value, bool $exclude=false) : SphinxQL {
        if ($value>0) {
            $this->intFilter(static::ID, $value, $exclude);
        }
        return $this;
    }
    
    
    public function uid(int $value, bool $exclude=false) : SphinxQL {
        if ($value>0) {
            $this->intFilter(static::UID, $value, $exclude);
            unset($this->filters[static::COUNTRY]);
            unset($this->filters[static::CITY]);
        }
        return $this;
    }
    
    
    public function region(int $country_id, int $city_id=0) : SphinxQL {
        if ($city_id>0) {
            return $this->setFilter('ANY('.static::CITY.')', $city_id);
        }        
        elseif ($country_id>0) {
            return $this->setFilter('ANY('.static::COUNTRY.')', $country_id);
        }        
        return $this;
    }
    
    
    
    public function media(int $value=1, bool $exclude=false) : SphinxQL {
        $this->boolFilter(static::MEDIA, $value, $exclude);
        return $this;
    }
    

    public function root(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {
            $this->intFilter(static::ROOT, $value, $exclude);
        }
        return $this;
    }
    
    
    public function section(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {            
            $this->intFilter('ANY('.static::SECTION.')', $value, $exclude);
            if (!$exclude) {
                unset($this->filters[static::ROOT]);
            }
        }
        return $this;
    }

    
    public function purpose(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {
            $this->intFilter(static::PURPOSE, $value, $exclude);
        }
        return $this;
    }

    
    public function starred(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {            
            $this->intFilter(static::STARRED, $value, $exclude);
        }
        return $this;
    }
    
    
    public function locality(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {            
            $this->intFilter(static::LOCALITY, $value, $exclude);
        }
        return $this;
    }
    
    
    public function tag(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {            
            $this->intFilter(static::TAG, $value, $exclude);
            if (!$exclude) {
                unset($this->filters[static::SECTION]);
            }
        }
        return $this;
    }
    
    
    public function rtl(array $value, bool $exclude=false) : SphinxQL {
        $length = count($value);
        if ($length>0) {
            if ($length==1) {
                $this->intFilter(static::RTL, $value[0], $exclude);
            }
            else {
                $this->arrayFilter(static::RTL, $value, $exclude);
            }
        }
        return $this;
    }
    
    
    public function publisherType(int $value, bool $exclude=false) : SphinxQL {
        if ($value) {            
            $this->intFilter(static::PUBLISHER_TYPE, $value, $exclude);
        }
        return $this;
    }
    
    
    public function featured(bool $exclusive=true) : SphinxQL {
        $this->filters[static::FEATURED_TTL]=($exclusive?'>=':'<').time();
        return $this;
    }
    
    

    
    public function exclude(array $ids) : SphinxQL {
        $size = count($ids);
        if ($size==1) {
            $this->filters[static::ID]='!='.$ids[0];
        }
        elseif ($size>0) {
            $this->filters[static::ID]=' not in ('.implode(',', $ids).')';
        }
                
        return $this;
    }
    
    
    public function sectionSet(array $value, bool $exclude=false) : SphinxQL {
        if ($value) {
            return $this->setFilter(static::SECTION, $value, $exclude);
        }
        return $this;
    }


    private function intFilter(string $attribute, $value, bool $exclude) : void {
        $this->filters[$attribute] = $exclude ? "!={$value}" : "={$value}";       
    }

    
    private function boolFilter(string $attribute, $value, bool $exclude) : void {
        $bool_value = $value>0?1:0;
        $this->filters[$attribute] = $exclude ? "!={$bool_value}" : "={$bool_value}";       
    }

    
    private function arrayFilter(string $attribute, array $value, bool $exclude) : void {
        $this->filters[$attribute] = ($exclude ? " not in (" : " in (") . implode(',', $value) . ')';        
    }
    
    
    
    function setFilter(string $attribute, $values, bool $exclude=FALSE) : SphinxQL {
        assert(is_string($attribute));
        $condition="";
        
        if ($exclude) {
            if (\is_array($values)) {
                if (\count($values)>1) {
                    $condition = " not in (". \implode(",", $values) . ")";
                } 
                else {
                    $condition = "!={$values[0]}";
                }
            } 
            else {
                $condition = "!={$values}";
            }            
        }
        else {
            if (\is_array($values)) {
                if (\count($values)>1) {
                    $condition = " in (". \implode(",", $values) .")";                    
                } 
                else {
                    $condition = "={$values[0]}";
                }
            } 
            else {
                $condition = "={$values}";
            }
        }

        $this->filters[$attribute] = $condition;
        return $this;
    }
    
    
    function setFilterRange(string $attribute, $min, $max, bool $exclude=FALSE) : void {
        assert(is_string($attribute));
        assert(is_numeric($min));
        assert(is_numeric($max));
        assert($min<=$max);
        if ($exclude) {
            $this->filters[$attribute]=" not between {$min} and {$max}";
        } 
        else {
            $this->filters[$attribute]=" between {$min} and {$max}";
        }
    }
    
    
    function setFilterCondition(string $attribute, string $condition, $value) : SphinxQL {
        if (is_array($value)) {
            $this->filters[$attribute]=" {$condition} (".implode(",", $value).")";
        }
        else {
            $this->filters[$attribute]="{$condition}{$value}";
        }
        return $this;
    }
    
    
    function resetFilters(bool $resetGroupBy=true) : SphinxQL {
        $this->filters = [];
        $this->groupby = [];
        $this->facets = [];
        return $this;
    }
    
    
    function setGroupBy($attribute) {
        $attribute = strtolower($attribute);
        if (!in_array($attribute, $this->groupby)) {
            $this->groupby[]=$attribute;
        }
    }
    
    
    function clearFacets() : void {
        $this->facets = [];
    }
    
    
    function setFacet(string $facet, bool $clearALL=FALSE) : void {
        if ($clearALL) {
            $this->facets = [];
        }
        $facet = \strtolower($facet);
        $this->facets[$facet]="facet {$facet} limit 10000";       
    }
    
    
    function addDirectQuery(string $name, string $q, bool $assoc=FALSE) : void {
        $this->_batch[$name] = [$q, $assoc];
    }
    
    
    function addQuery(string $name, string $keywords='', bool $assoc=false) : void {
        $this->build($keywords);
        $this->_batch[$name]=[$this->_query, $assoc, 0];
        $this->_query='';
    }
    
    
    function execute(string $queryQL) : bool {
        $this->connect();
        return ($this->_sphinx->multi_query($queryQL."; SHOW META LIKE 'total%';"));
    }


    function directQuery(string $queryQL, int $fetchMode=\MYSQLI_NUM) : array {
        $this->connect();
        $records = [];
        try {
            if ($this->_sphinx->multi_query($queryQL)) {
                do {
                    $i= 0;
                    if ($rs = $this->_sphinx->store_result()) {
                        while($row = $rs->fetch_row()) {
                            $row[0] = (int)$row[0];
                            $records[$i][$row[0]] = $row;
                        }
                        $i++;
                        $rs->free();
                    }

                    if (!$this->_sphinx->more_results()) {
                        break;
                    }
                } while ($this->_sphinx->next_result());
            }
            
            if (\count($records)===1) {
                $records = $records[0];
            }
        } 
        catch (Exception $ex) {
            $this->Log($ex);
        } 
        
        return $records;
    }
    
    
    function search(string $queryQL='', int $fetchMode=\MYSQLI_ASSOC) : array {
        $this->connect();
        if (empty($queryQL)) {
            $this->build();
            $queryQL = $this->_query;            
        }

        if ($this->noAttrs===false && $this->clause==='id') {
            $this->noAttrs=true;
        }
        
        $result = ['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'sql'=>$queryQL];
        try {
            if ($this->_sphinx->multi_query($queryQL)) {
                do {
                    if ($rs=$this->_sphinx->store_result()) {
                        if ($this->noAttrs) {
                            while ($row=$rs->fetch_row()) {
                                $result['matches'][]=$row[0];
                            }
                        }
                        else {
                            $result['matches'][] = $rs->fetch_all($fetchMode);
                        }
                        $rs->free();
                    }                 
                    if(!$this->_sphinx->more_results()) {
                        break;
                    }                    
                } while ($this->_sphinx->next_result());
            }
            
            if ($this->_sphinx->error) {
                $this->Log(['host'=>$this->server['host'],
                            'function'=>__FUNCTION__,
                            'error'=>'['.$this->_sphinx->connect_errno . '] ' . $this->_sphinx->connect_error,
                            'query'=>$queryQL,
                            'result'=>$result]);
            }
            
            if (\count($result['matches'])===1 && \is_array($result['matches'][0])) {
                $result['matches']=$result['matches'][0];
            }            
            
        } 
        catch (\Exception $ex) {
            \error_log(__FUNCTION__.__LINE__.PHP_EOL.$queryQL.PHP_EOL);
            $this->Log($queryQL.PHP_EOL.$ex->getTraceAsString());
        } 
        finally {
            if ($this->fetchMeta) {
                $this->fetchMetaData($result);
            }
        }
        return $result;
    }
    
    
    function query(string $keywords="", int $fetchMode=MYSQLI_ASSOC) {        
        $this->build($keywords);
        return $this->search($this->_query, $fetchMode);
    }
    
    
    function executeBatchNew() : array {
        $result=[];
        $q='';
        $i=0;
        $running=[];
        foreach ($this->_batch as $name => $info) {
            if ($name!='body') {
                $running[$i]=[$info[1], $info[0], $name];
                $q.=$info[0].';'.PHP_EOL;
                $this->_batch[$name][2]=$i;
                $i++;
            }
        }
        
        $running[$i]=[$this->_batch['body'][1], $this->_batch['body'][0],'body'];
        $q.=$this->_batch['body'][0].';'.PHP_EOL;
        
        $rs=['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'facet'=>[], 'sql'=>$q];               
        
        $this->_sphinx->multi_query($q);
        if ($this->_sphinx->error) {
            $this->Log(['query'=>$q, 'error'=>'['.$this->_sphinx->connect_errno . '] ' . $this->_sphinx->connect_error]);
            $result[$name] = '['.$this->_sphinx->errno.'] '.$this->_sphinx->error.' [ '.$q.']';
            $result[$name]=$rs;
            return $result;
        }
        
        $i=0;
        do {
            $name=$running[$i][2];
            $rs = ['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'sql'=>$running[$i][1]];
            if ($res = $this->_sphinx->store_result()) {
                while ($row=$res->fetch_assoc()) {
                    $rs['matches'][]=$row['id'];
                }
                $res->free();
            }

            if ($name=='body') {
                $this->fetchMetaData($rs);
            }
            else {
                $rs['total'] = count($rs['matches']);
                $rs['total_found'] = count($rs['matches']);
            }
            $result[$name]=$rs;
            
            if (!$this->_sphinx->more_results()) { break; }
            
            $i++;
        } while ($this->_sphinx->next_result());
        
        return $result;       
    }
    
    
    function executeBatch(bool $fullRow=FALSE) : array {
        $result = [];        
        foreach ($this->_batch as $name=>$info) {
            $q = $info[0];
            $assoc = $info[1];
            $rs = ['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'facet'=>[], 'sql'=>$q];
            $this->_sphinx->multi_query($q);
            if ($this->_sphinx->error) {
                $this->Log(['query'=>$q, 'error'=>'['.$this->_sphinx->connect_errno . '] ' . $this->_sphinx->connect_error]);
                $result[$name] = '['.$this->_sphinx->errno.'] '.$this->_sphinx->error.' [ '.$q.']';
                $result[$name]=$rs;
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
                    } 
                    else {
                        if ($field_names[0]->name=='id') {
                            while ($row=$res->fetch_assoc()) {
                                $rs['matches'][]=$row['id'];
                            }
                        } 
                        else {
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
                    break;
                }
            } 
            while ($this->_sphinx->next_result());
            
        }
        return $result;
    }
    
    
    function singleSelectQuery($q, $assoc=FALSE) {
        $result = ['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'sql'=>$q];

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
                    } 
                    else {
                        $result['matches'][] = $row[$field_names[0]->name];
                    }
                }

                $resource->free_result();                
            }
            $this->fetchMetaData($result);
        }
        catch (Exception $ex) {
            $this->Log($ex);
            return FALSE;
        }
        return $result;
    }
    
    
    function getAds(string $keywords='') {
        $this->build($keywords);
        $result = ['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'matches'=>[], 'sql'=>$this->_query];

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
        } 
        catch (Exception $ex) {
            $this->Log($ex);
            return FALSE;
        }

        return $result;
    }
    
        
    function setLimits (int $offset=0, int $limit=10, int $max=2000) : SphinxQL {
        assert( is_int($offset) );
        assert( is_int($limit) );
        assert( $offset>=0 );
        assert( $limit>=0 );
        assert( $max>0 );
        $this->offset = $offset+0;
        $this->limit = $limit+0;
        if ($max>0) {
            $this->max_matches=$max;
        }
        return $this;
    }
    
    
    
    function setSortBy(string $sortby) : SphinxQL {
        assert(is_string($sortby));
        $this->sortby = $sortby;
        return $this;
    }
    
    
    public function getLastError() : string {
        return (isset($this->metaData['error'])) ? $this->metaData['error'] : '';
    }
    
    
    function getLastWarning() : string {
        return (isset($this->metaData['warning'])) ? $this->metaData['warning'] : '';
    }
    
    
    function status() : array {
        $result = [];
        if ($rs = $this->_sphinx->query('SHOW STATUS')) {
            while ($value = $rs->fetch_array(\MYSQLI_NUM)) {
                $result[$value[0]] = $value[1];
            }
            $rs->close();
        }
        return $result;
    }
    
    
    
    function rotate(string $partition='x', string $index_name='') : array {
        $result =[];
        $cmd = NULL;
        switch ($index_name) {
            case 'ad0':
                $cmd = "RELOAD INDEX ad{$partition} FROM '/home/db/sphinx/new/mourjan-ad-partition-0'";
                break;
            
            case 'ad1':
                $cmd = "RELOAD INDEX ad{$partition} FROM '/home/db/sphinx/new/mourjan-ad-partition-1'";
                break;
            
            case 'ad2':
                $cmd = "RELOAD INDEX ad{$partition} FROM '/home/db/sphinx/new/mourjan-ad-partition-2'";
                break;
            
            case 'adx':
                $cmd = "RELOAD INDEX ad{$partition} FROM '/home/db/sphinx/new/mourjan-ad-partition-x'";
                break;
            
            case 'section_counts':
                $cmd = "RELOAD INDEX {$index_name} FROM '/home/db/sphinx/new/{$index_name}'";
                break;
            
            case 'locality_counts':
                $cmd = "RELOAD INDEX {$index_name} FROM '/home/db/sphinx/new/{$index_name}'";
                break;
            
            case 'section_tag_counts':
                $cmd = "RELOAD INDEX {$index_name} FROM '/home/db/sphinx/new/{$index_name}'";
                break;
                

            default:
                break;
        }
        //$cmd = "RELOAD INDEX ad{$partition} FROM '/home/db/sphinx/new/mourjan-ad-partition-{$partition}'";
        if ($cmd && $this->_sphinx->query($cmd)) {
            $result[]=$cmd;           
        }
        //error_log($cmd);
        return $result;
    }

    
    function updateHoldAd($id) {
        if ($this->_sphinx->real_query("update {$this->indexName} set hold=1 where id={$id} and hold=0")) {
            //echo "ad: {$id} has been set on hold state in index" . PHP_EOL;
        } 
        else {
            //echo "Failed to set ad: {$id} on hold state!" . PHP_EOL;
        }
    }
    
    
    function directUpdateQuery($q) {
        if ($this->_sphinx->real_query($q)) {
            return $this->_sphinx->affected_rows;
        } 
        else {
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
    public function escape($value) {
        if (($value = $this->_sphinx->real_escape_string((string) $value)) === false) {
            throw new Exception($this->_sphinx->error, $this->_sphinx->errno);
        }

        return "'".$value."'";
    }

    /**
     * Escapes the query for the MATCH() function
     * Allows some of the control characters to pass through for use with a search field: -, |, "
     * It also does some tricks to wrap/unwrap within " the string and prevents errors
     *
     * @param string $string The string to escape for the MATCH
     *
     * @return string The escaped string
     */
    public function halfEscapeMatch(string $string) : string {
        //if ($string instanceof Expression) {
        //    return $string->value();
        //}
        $string = str_replace(array_keys($this->escape_half_chars), array_values($this->escape_half_chars), $string);
        // this manages to lower the error rate by a lot
        if (mb_substr_count($string, '"', 'utf8') % 2 !== 0) {
            $string .= '"';
        }

        $string = preg_replace('/-[\s-]*-/u', '-', $string);
        $from_to_preg = [
            '/([-|])\s*$/u'        => '\\\\\1',
            '/\|[\s|]*\|/u'        => '|',
            '/(\S+)-(\S+)/u'       => '\1\-\2',
            '/(\S+)\s+-\s+(\S+)/u' => '\1 \- \2',
        ];

        $string = mb_strtolower(preg_replace(array_keys($from_to_preg), array_values($from_to_preg), $string), 'utf8');
        return $string;
    }


    public function build(string $keywords='') : void {
        $arabic_indic_digits = [
            "\xD9\xA0",
            "\xD9\xA1",
            "\xD9\xA2",
            "\xD9\xA3",
            "\xD9\xA4",
            "\xD9\xA5",
            "\xD9\xA6",
            "\xD9\xA7",
            "\xD9\xA8",
            "\xD9\xA9",
        ];
        $keywords=str_replace($arabic_indic_digits, array_keys($arabic_indic_digits), \trim($keywords));
        
        if ($this->indexName==='classifier') {
            $this->_query = "select {$this->clause} from {$this->indexName} where hold=0 ";
        } 
        else {
            $this->_query = "select {$this->clause} from {$this->indexName} where hold=0 and canonical_id=0 ";
        }
        
        foreach ($this->filters as $key => $value) {
            $this->_query.="and {$key}{$value} ";
        }
        
        if ($keywords) {
            if ($keywords[0]==='-') {
                $keywords = 'qwerty '.$keywords;
            }

            $keywords=$this->halfEscapeMatch($keywords);
            $this->_query.="and match('{$keywords}') ";
        }
        
        if (!empty($this->groupby)) {
            $this->_query.="group by " . \implode(',', $this->groupby) . ' ';
        }
        
        if ($this->sortby) {
            $this->_query.="order by {$this->sortby} ";
        }
        $this->_query.="LIMIT {$this->offset}, {$this->limit} ";
        
        if ($this->max_matches) {
            $this->_query.="OPTION max_matches={$this->max_matches} "; 
        }

        if (!empty($this->facets)) {
            $this->_query.=\implode(' ', \array_values( $this->facets ));
        }
    }
    
    
    public function fetchMetaData(?array &$resultQuery=NULL) : void {
        if (($rs = $this->_sphinx->query('SHOW META LIKE \'total%\''))!==FALSE) {
            $res = $rs->fetch_all();
            foreach ($res as $rec) {
                $resultQuery[$rec[0]] = $rec[1]+0;
            }
            $rs->close();
        }
    }


    private function Log(string $message) : void {
        $dbt=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        if (!empty($dbt)) {
            unset($dbt[0]['function']);
            unset($dbt[0]['class']);
            unset($dbt[0]['type']);
            if (isset($dbt[0]['object'])) {
                unset($dbt[0]['object']);
            }

            error_log(__CLASS__.PHP_EOL.json_encode($dbt[0], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL.'>');
            error_log(PHP_EOL.json_encode($dbt[0], JSON_PRETTY_PRINT).PHP_EOL, 3, "/var/log/mourjan/LogFile.txt");
        }
    }
    
}

class MCConnection {
    
}

class MCQuery {
    
}
