<?php

include_once get_cfg_var('mourjan.path') . '/config/cfg.php';

error_reporting(E_ALL);
ini_set('display_errors', php_sapi_name()=='cli'?'1':'0');

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);
ob_implicit_flush();

$address = 'h8.mourjan.com';
$port = 1337;



class MCSaveHandler
{
    var $_host;			///< searchd host (default is "db.mourjan.com")
    var $_port;			///< searchd port (default is 1337)

    //private $address;
    private $_socket;
    private $cfg;

    //private $client;

    var $_error;		///< last error message
    var $_warning;		///< last warning message
    var $_connerror;		///< connection error vs remote error flag

    function __construct($config) 
    {
        $this->cfg = $config;
        // per-client-object settings
        $this->_host		= $this->cfg['db_host'];
        $this->_port		= 1337;
        $this->_path		= false;
        $this->_socket		= false;


        $this->_error		= ""; // per-reply fields (for single-query case)
        $this->_warning		= "";
        $this->_connerror	= false;

        $this->_reqs		= array ();	// requests storage (for multi-query case)
        $this->_arrayresult	= false;
        $this->_timeout		= 0;
    }


    function __destruct()
    {
        if ($this->_socket !== false) {
            fclose($this->_socket);
        }
    }


    /// set server connection timeout (0 to remove)
    function SetConnectTimeout ( $timeout )
    {
        assert ( is_numeric($timeout) );
        $this->_timeout = $timeout;
    }


    function _Send ( $handle, $data, $length )
    {
        if ( feof($handle) || fwrite ( $handle, $data, $length ) !== $length )
        {
            $this->_error = 'connection unexpectedly closed (timed out?)';
            $this->_connerror = true;
            return false;
        }
        return true;
    }


     
    function _Connect ()
    {
        if ( $this->_socket!==false )
        {
            // we are in persistent connection mode, so we have a socket
            // however, need to check whether it's still alive
            if ( !@feof ( $this->_socket ) )
                return $this->_socket;
            // force reopen
            $this->_socket = false;
        }

        $errno = 0;
        $errstr = "";
        $this->_connerror = false;
        if ( $this->_path )
        {
            $host = $this->_path;
            $port = 0;
        }
        else
        {
            $host = $this->_host;
            $port = $this->_port;
        }
        
        if ( $this->_timeout<=0 )
            $fp = @fsockopen ( $host, $port, $errno, $errstr );
        else
            $fp = @fsockopen ( $host, $port, $errno, $errstr, $this->_timeout );

        if ( !$fp )
        {
            if ( $this->_path )
                $location = $this->_path;
            else
                $location = "{$this->_host}:{$this->_port}";

            $errstr = trim ( $errstr );
            $this->_error = "connection to $location failed (errno=$errno, msg=$errstr)";
            $this->_connerror = true;
            return false;
        }
     
        return $fp;
    }


    /// get and check response packet from searchd server
    function _GetResponse ( $fp, $client_ver )
    {
        $response = "";
        $len = 0;
        $header = fread ( $fp, 2 );
        if ( strlen($header)==2 )
        {
            $ll = unpack('nlen', $header);
            $len = $ll['len'];
            //echo $len, "\n";
            
            //list ( $status, $ver, $len ) = array_values ( unpack ( "n2a/Nb", $header ) );
            $left = $len;
            while ( $left>0 && !feof($fp) )
            {
                $chunk = fread ( $fp, min ( 8192, $left ) );
                if ( $chunk )
                {
                    $response .= $chunk;
                    $left -= strlen($chunk);
                }
            }
            //echo "RESPONSE: ", $response, "\n";
        }

        if ($this->_socket === false) {
            fclose($fp);
        }
        
        // check response
        $read = strlen ( $response );
        if ( !$response || $read!=$len )
        {
            $this->_error = $len
                    ? "failed to read normalizer response (len=$len, read=$read)"
                    : "received zero-sized searchd response";
            return false;
        }

        if (substr($response, 0, 6)=='error:')
        {
            $this->_error = $response;
            return false;
        }
      
        return $response;
    }


    public function getFromDatabase($reference)
    {
        $db = new DB($this->cfg);
        $rs = $db->queryResultArray("select * from ad_user where id=?", [$reference])[0];
        $rs['CONTENT'] = json_decode($rs['CONTENT']);
        //print_r($rs['CONTENT']);
        if (isset($rs['CONTENT']->attrs)) {
            unset($rs['CONTENT']->attrs);
        }
        $command = ['command'=>'normalize', 'json'=>json_encode($rs['CONTENT'])];
        //$buffer = "{\"command\":\"normalize\",\"file\":\"/dev/shm/ad.content.{$reference}.json\"}";
        $buffer = json_encode($command);
        $len = pack('N', strlen($buffer));
        $buffer = $len.$buffer;
        if ($this->Open())
        {
            if ($this->_Send($this->_socket, $buffer, strlen($buffer)))
            {

                $response = $this->_GetResponse($this->_socket, '');
                if ($response) {
                    //echo $response, "\n";
                    $j = json_decode($response);
                    print_r($j);
                    if (isset($j->attrs))
                    {
                        $ps = $db->prepareQuery("update ad_user set section_id=?, purpose_id=?, content=? where id=?");
                        $po = $db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
        
                        $ps->execute([$j->se, $j->pu, $response, $reference]);
                        
                        
                        $po->bindValue(1, $reference, PDO::PARAM_INT);
                        $po->bindValue(2, preg_replace('/\s+/', ' ', json_encode($j->attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                        $po->execute();   
                        
                        if ($rs['STATE']=='1')
                        {
                            $po = $db->prepareQuery("INSERT INTO INVALIDATE (TABLE_ID, RECORD_ID) VALUES (12, ?)");
                            $po->bindValue(1, $reference, PDO::PARAM_INT);
                            $po->execute(); 
                        }
                    }
                    
                } else {
                    echo $this->_error, "\n";
                }
            }
            else
            {
                echo $this->_error, "\n";
            }
            $this->Close();
        }        
    }

    
    
    public function testRealEstate($country_id=1)
    {
        $myfile = fopen("/tmp/testfile.txt", "w") ;
        $db = new DB($this->cfg);
        
        $ps = $db->prepareQuery("update ad_user set content=? where id=?");
        $po = $db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
        
        $rs = $db->queryResultArray("SELECT ad_user.*
                    from ad
                    left join AD_USER on AD_USER.ID=ad.ID
                    left join section s on s.Id=ad.SECTION_ID
                    where ad.COUNTRY_ID=?
                    and ad.PUBLICATION_ID=1
                    and s.ROOT_ID=1
                    and ad.section_id!=748
                    and ad.HOLD=0 
                    order by ad_user.id", [$country_id]);
        
        $c = count($rs);
        for ($i=0; $i<$c; $i++)
        {
            $ad = $rs[$i];
            $content = json_decode($ad['CONTENT']);
            if (isset($content->attrs)) {
                unset($content->attrs);
            }
            
            $command = ['command'=>'normalize', 'json'=>json_encode($content)];
            $buffer = json_encode($command);
            $len = pack('N', strlen($buffer));
            $buffer = $len.$buffer;
            
            $connection = new MCSaveHandler($this->cfg);
            $connection->Open();
           
            if ($connection->_Send($connection->_socket, $buffer, strlen($buffer)))
            {            
                $response = $connection->_GetResponse($connection->_socket, '');
                if ($response) 
                {
                    $j = json_decode($response);
                    ///Users/robertallakis/NetBeansProjects/MourjanAdNormalizer/dist/MourjanNormalizer.jar
                    if (!isset($j->attrs->locales) )
                    {
                        fwrite($myfile, "----------------------------------------------------------------------------------------------------------\n");
                        fwrite($myfile, $ad['ID'].PHP_EOL);            
                        fwrite($myfile, $content->other.PHP_EOL);
                        if (isset($content->altother))
                        {
                            fwrite($myfile, $content->altother.PHP_EOL);
                        }
  
                        fwrite($myfile, ">>>>>\n");
                        fwrite($myfile, $j->other .PHP_EOL);
                        if (isset($j->altother))
                        {
                            fwrite($myfile, $j->altother.PHP_EOL);
                        }
                        fwrite($myfile, var_export($j->attrs, TRUE));
                    }
                    
                    if (isset($j->attrs))
                    {
                        $ps->execute([$response, $ad['ID']]);
                        $po->bindValue(1, $ad['ID'], PDO::PARAM_INT);
                        $po->bindValue(2, preg_replace('/\s+/', ' ', json_encode($j->attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                        $po->execute();    
                    }
                } else {
                    echo $connection->_error, "\n";
                    print_r($content);
                }
            }
            else
            {
                echo $connection->_error, "\n";
            }
            
            //$connection->Close();
            //usleep(10);
        }
        $db->commit();
        fclose($myfile);
    }
    
    
    public function getFromContentObject($ad_content)
    {
        if (isset($ad_content['attrs'])) 
        {
            unset($ad_content['attrs']);
        }
        $command = ['command'=>'normalize', 'json'=>json_encode($ad_content)];
        $buffer = json_encode($command);
        $len = pack('N', strlen($buffer));
        $buffer = $len.$buffer;
        
        $this->Open();

        if ($this->_Send($this->_socket, $buffer, strlen($buffer)))
        {            
            $response = $this->_GetResponse($this->_socket, '');
            if ($response) {
                $j = json_decode($response, TRUE);
                return $j;
            } else {
                error_log($this->_error);
            }
            $this->Close();
        }
        else
        {
            error_log($this->_error);
        }
        
        return FALSE;
    }

    
    function searchByAdId($reference)
    {
        $db = new DB($this->cfg);
        include_once $this->cfg['dir'].'/core/lib/SphinxQL.php';
        $sphinx = new SphinxQL(['host'=>'p1.mourjan.com', 'port'=>9307, 'socket'=>NULL], 'ad');
        $sphinx->connect();
        $rs = $db->queryResultArray("select * from ad_user where id=?", [$reference])[0];
        $db->close();        

        $obj = json_decode($rs['CONTENT']);
        //print_r($obj->attrs);
        
        $words = explode(' ', $obj->attrs->ar);
        //print_r($words);
        
        $q = "select id, attrs, locality_id, IF(featured_date_ended>=NOW(),1,0) featured, section_id, purpose_id";
        $sbPhones = "";
        $sbMails = "";
        $sbGeoKeys = "";
        
        if (isset($obj->attrs->geokeys) && !empty($obj->attrs->geokeys))
        {
            $sbGeoKeys.=", ANY(";
            $len = count($obj->attrs->geokeys);
            for ($i=0; $i<$len; $i++)
            {
                if ($i>0) $sbGeoKeys.=" OR ";
                $sbGeoKeys.="x={$obj->attrs->geokeys[$i]}";
            }
            $sbGeoKeys.=" FOR x IN attrs.geokeys) gfilter";
            $q.=$sbGeoKeys;    
        }
        
        $names = [];
        if (!isset($obj->attrs->geokeys))
        {
            $obj->attrs->geokeys = [];
        }
        
        if (isset($obj->attrs->price))
        {
            $names['price'] = 0;
        }
        
        if (isset($obj->attrs->rooms))
        {
            $names['rooms'] = 0;
        }

        if (isset($obj->attrs->space))
        {
            $names['space'] = 0;
        }
        
        
        if (isset($obj->attrs->phones) && isset($obj->attrs->phones->n) && !empty($obj->attrs->phones->n))
        {
            $len = count($obj->attrs->phones->n);
            $sbPhones.="ANY(";
            for ($i=0; $i<$len; $i++)
            {
                $sbPhones.= ($i>0) ? " OR " : "";
                $sbPhones.="BIGINT(x)={$obj->attrs->phones->n[$i]}";                    
            }
            $sbPhones.=" FOR x IN attrs.phones.n)";
        }
        
        if (isset($obj->attrs->mails) && !empty($obj->attrs->mails))
        {
            $len = count($obj->attrs->mails);
            $sbMails.="ANY(";
            for ($i=0; $i<$len; $i++)
            {
                $sbMails.=($i>0) ? " OR " : "";
                $sbMails.="x='{$obj->attrs->mails[$i]}'";
            }
            $sbMails.=" FOR x IN attrs.mails)";
        }
            
        if ($sbPhones && $sbMails)
        {
            $q.=", ({$sbPhones} OR {$sbMails}) cfilter";
        }
        else if ($sbPhones && empty($sbMails))
        {
            $q.=", {$sbPhones} cfilter";
        }
        else if (empty($sbPhones) && $sbMails)
        {
            $q.=", {$sbMails} cfilter";
        }
        $q.=" FROM ad WHERE id!={$reference} AND publication_id=1 and hold=0 and cfilter=1 limit 1000";

        //echo $q, "\n";
        
        $res = $sphinx->search($q);
        $sphinx->close();

        $len = count($res['matches']);
        $scores = [];
        //$x = preg_split('//u', $obj->attrs->ar, null, PREG_SPLIT_NO_EMPTY);
        for ($i=0; $i<$len; $i++)
        {
            $scores[$res['matches'][$i]['id']] = 0;
            
            $res['matches'][$i]['score'] = 0;
            $attrs = json_decode($res['matches'][$i]['attrs']);

            if (isset($attrs->geokeys)) {
                $scores[$res['matches'][$i]['id']] += (empty($obj->attrs->geokeys)) ? 0 : count(array_intersect($attrs->geokeys, $obj->attrs->geokeys)) / count($obj->attrs->geokeys);
            }

            $att_score = 0;
            foreach ($names as $key => $value) 
            {
                if (isset($attrs->$key)){
                    $att_score+=($attrs->$key==$obj->attrs->$key)?1:0;
                }
            }
            $scores[$res['matches'][$i]['id']] += count($names)>0 ? $att_score / count($names) : 0;
            
            if (isset($attrs->ar))
            {
                $scores[$res['matches'][$i]['id']] += $this->jaccardIndex($words, explode(' ', $attrs->ar));
            }            
        }
        
        arsort($scores, SORT_NUMERIC);

        $searchResults = ['body'=>['matches'=>[], 'scores'=>[] ]];

        foreach ($scores as $key => $value) 
        {
            if ($value>=0.25) 
            {
                $searchResults['body']['scores'][$key] = $value;
                $searchResults['body']['matches'][] = $key;
            }
        }
        
        $searchResults['body']['facet'] = $obj->attrs;
        $searchResults['body']['total'] = count($searchResults['body']['matches']);
        $searchResults['body']['total_found'] = $searchResults['body']['total'];
                
        return $searchResults;
    }
    
    
    private function jaccardIndex($a1, $a2) 
    {
        $index = 0.0;
                
        $intersection = array_intersect($a1, $a2);
        if (!empty($intersection)) {
            $union =  array_unique(array_merge($a1,  $a2));        
            $index = count($intersection) /  count($union) ;
        }
        return $index;
        
    }


    private function longestCommonSubsequence($X,  $s)
    {
        $m = count($X);
        //$m = mb_strlen($s1);
        $n = mb_strlen($s);
        //$X = preg_split('//u', $s1, null, PREG_SPLIT_NO_EMPTY);
        $Y = preg_split('//u', $s, null, PREG_SPLIT_NO_EMPTY);

        $C = array(array($m + 1), array($n + 1));


        for ($i = 0; $i <= $m; $i++) {
            $C[$i][0] = 0;
        }

        for ($j = 0; $j <= $n; $j++) {
            $C[0][$j] = 0;
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($X[$i - 1] == $Y[$j - 1]) {
                    $C[$i][$j] = $C[$i - 1][$j - 1] + 1;
                } else {
                    $C[$i][$j] =  max( $C[$i][$j - 1], $C[$i - 1][$j] );
                }
            }
        }

        return $m + $n - 2 * $C[$m][$n];;
    }

    
    /////////////////////////////////////////////////////////////////////////////
    // persistent connections
    /////////////////////////////////////////////////////////////////////////////
    function Open()
    {
        if ( $this->_socket !== false )
        {
            $this->_error = 'already connected';
            return false;
        }
        if ( !$fp = $this->_Connect() )
            return false;
        
        //echo 'Sending request', "\n";

        // command, command version = 0, body length = 4, body = 1
        //$req = pack ( "nnNN", SEARCHD_COMMAND_PERSIST, 0, 4, 1 );
        //if ( !$this->_Send ( $fp, $req, 12 ) )
        //    return false;
        $this->_socket = $fp;
        return true;
    }

    
    function Close()
    {
        if ( $this->_socket === false )
        {
            $this->_error = 'not connected';
            return false;
        }
        fclose ( $this->_socket );
        $this->_socket = false;

        return true;
    }

}


if (php_sapi_name()=='cli')
{

    $saveHandler = new MCSaveHandler($config);
    //$saveHandler->getFromDatabase($argv[1]);
    $saveHandler->searchByAdId($argv[1]);
    //$saveHandler->testRealEstate(9);
    
}
