<?php

use Core\Model\DB;
use Core\Lib\SphinxQL;

error_reporting(E_ALL);
ini_set('display_errors', php_sapi_name()=='cli'?'1':'0');

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);
ob_implicit_flush();

$address = 'h8.mourjan.com';
$port = 1337;



class MCSaveHandler {
    var $_host;			///< searchd host (default is "db.mourjan.com")
    var $_port;			///< searchd port (default is 1337)

    private $_socket;

    var $_error;		///< last error message
    var $_warning;		///< last warning message
    var $_connerror;		///< connection error vs remote error flag

    function __construct() {
        // per-client-object settings
        $this->_host		= 'h8.mourjan.com'; /* $this->cfg['db_host'];*/
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


    function __destruct() {
        if ($this->_socket !== false) {
            fclose($this->_socket);
        }
    }


    /// set server connection timeout (0 to remove)
    function SetConnectTimeout ( $timeout ) {
        assert ( is_numeric($timeout) );
        $this->_timeout = $timeout;
    }


    function _Send ( $handle, $data, $length ) {
        if ( feof($handle) || fwrite ( $handle, $data, $length ) !== $length ) {
            $this->_error = 'connection unexpectedly closed (timed out?)';
            $this->_connerror = true;
            return false;
        }
        return true;
    }


     
    function _Connect() {
        if ( $this->_socket!==false ) {
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
        if ( $this->_path ) {
            $host = $this->_path;
            $port = 0;
        }
        else {
            $host = $this->_host;
            $port = $this->_port;
        }
        
        if ( $this->_timeout<=0 )
            $fp = @fsockopen ( $host, $port, $errno, $errstr );
        else
            $fp = @fsockopen ( $host, $port, $errno, $errstr, $this->_timeout );

        if ( !$fp ) {
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
    function _GetResponse ( $fp, $client_ver ) {
        $response = "";
        $len = 0;
        $header = fread ( $fp, 2 );
        if ( strlen($header)==2 ) {
            $ll = unpack('nlen', $header);
            $len = $ll['len'];
            //echo $len, "\n";
            
            //list ( $status, $ver, $len ) = array_values ( unpack ( "n2a/Nb", $header ) );
            $left = $len;
            while ( $left>0 && !feof($fp) ) {
                $chunk = fread ( $fp, min ( 8192, $left ) );
                if ( $chunk ) {
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
        if ( !$response || $read!=$len ) {
            $this->_error = $len
                    ? "failed to read normalizer response (len=$len, read=$read)"
                    : "received zero-sized searchd response";
            return false;
        }

        if (substr($response, 0, 6)=='error:') {
            $this->_error = $response;
            return false;
        }
      
        return $response;
    }


    public function getFromDatabase($reference) {
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
        if ($this->Open()) {
            if ($this->_Send($this->_socket, $buffer, strlen($buffer))) {

                $response = $this->_GetResponse($this->_socket, '');
                if ($response) {
                    //echo $response, "\n";
                    $j = json_decode($response);
                    print_r($j);
                    if (isset($j->attrs)) {
                        $ps = $db->prepareQuery("update ad_user set section_id=?, purpose_id=?, content=? where id=?");
                        $po = $db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
        
                        $ps->execute([$j->se, $j->pu, $response, $reference]);
                        unset($ps);
                        
                        $po->bindValue(1, $reference, PDO::PARAM_INT);
                        $po->bindValue(2, preg_replace('/\s+/', ' ', json_encode($j->attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                        $po->execute();
                        unset($po);
                        
                        if ($rs['STATE']=='1') {
                            $po = $db->prepareQuery("INSERT INTO INVALIDATE (TABLE_ID, RECORD_ID) VALUES (12, ?)");
                            $po->bindValue(1, $reference, PDO::PARAM_INT);
                            $po->execute();
                            unset($po);
                        }
                    }
                    
                } 
                else {
                    echo $this->_error, "\n";
                }
            }
            else {
                echo $this->_error, "\n";
            }
            $this->Close();
        }        
    }

    
    public function checkFromDatabase(int $reference) {
        $db = new DB(true);
        $rs = $db->queryResultArray("select * from ad_user where id=?", [$reference], TRUE);
        if ($rs && count($rs)==1) {
            $rs = $rs[0];
            $rs['CONTENT'] = json_decode($rs['CONTENT']);
            if (isset($rs['CONTENT']->attrs)) {
                unset($rs['CONTENT']->attrs);
            }
            $command = ['command'=>'normalize', 'json'=>json_encode($rs['CONTENT'])];
            $buffer = json_encode($command);
            $len = pack('N', strlen($buffer));
            $buffer = $len.$buffer;
            if ($this->Open()) {
                if ($this->_Send($this->_socket, $buffer, strlen($buffer))) {
                    $response = $this->_GetResponse($this->_socket, '');
                                        
                    if ($response) {
                        //echo $response, "\n";
                        $j = json_decode($response, TRUE);
                        //$j['other'] = json_encode($j['other'], JSON_UNESCAPED_UNICODE);
                        //$j = json_encode($j, JSON_UNESCAPED_UNICODE);
                        $rs['CONTENT']=$j;
                        //$j = json_decode($response);
                        //print_r($j);
                        //if (isset($j->attrs))
                        //{
                            //$ps = $db->prepareQuery("update ad_user set section_id=?, purpose_id=?, content=? where id=?");
                            //$po = $db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
        
                            //$ps->execute([$j->se, $j->pu, $response, $reference]);
                                                
                            //$po->bindValue(1, $reference, PDO::PARAM_INT);
                            //$po->bindValue(2, preg_replace('/\s+/', ' ', json_encode($j->attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                            //$po->execute();   
                        
                          //  if ($rs['STATE']=='1')
                          //  {
                                //$po = $db->prepareQuery("INSERT INTO INVALIDATE (TABLE_ID, RECORD_ID) VALUES (12, ?)");
                                //$po->bindValue(1, $reference, PDO::PARAM_INT);
                                //$po->execute(); 
                           // }
                        //}   
                    
                    } 
                    else {
                        echo $this->_error, "\n";
                    }
                }
                else {
                    echo $this->_error, "\n";
                }
                $this->Close();
                return $rs;
            }    
        }
        return FALSE;
    }
    
    
    public function testRealEstate($country_id=1) {
        $myfile = fopen("/tmp/testfile.txt", "w") ;
        $db = new DB($this->cfg);
        
        $ps = $db->prepareQuery("update ad_user set content=? where id=?");
        $po = $db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
        
        $rs = $db->queryResultArray("SELECT ad_user.*
                    from ad
                    left join AD_USER on AD_USER.ID=ad.ID
                    left join section s on s.Id=ad.SECTION_ID
                    where ad.COUNTRY_ID=?
                    and s.ROOT_ID=1
                    and ad.section_id!=748
                    and ad.HOLD=0 
                    order by ad_user.id", [$country_id]);
        
        $c = count($rs);
        for ($i=0; $i<$c; $i++) {
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
           
            if ($connection->_Send($connection->_socket, $buffer, strlen($buffer))) {            
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
    
    
    
    protected function apiV1normalizer($json_encoded) : array {
        $result = ['status'=>0, 'data'=>[]];
        try {
            $userAgent = 'Edigear-PHP/' . '1.0' . ' (+https://github.com/edigear/edigear-php)';
            $userAgent .= ' PHP/' . PHP_VERSION;
            $curl_version = curl_version();
            $userAgent .= ' curl/' . $curl_version['version'];
            $options = [
                CURLOPT_URL => "http://h8.mourjan.com:8080/v1/ad/mourjan",
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_HEADER => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_VERBOSE => FALSE];
            $ch = curl_init();
            $headers = array('Authorization: '.'$this->secretKey');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_encoded);
            array_push($headers, "Accept: application/json");
            array_push($headers, "Content-Type: application/json");
            array_push($headers, 'Content-Length: '.strlen($json_encoded));
            $options[CURLOPT_HTTPHEADER] = $headers;
            curl_setopt_array($ch, $options);        
            $resp = \curl_exec($ch);
            $result['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            $is_json = is_string($resp) && is_array(json_decode($resp, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
            if ($is_json) { $result['data'] = \json_decode($resp, TRUE); }
        }
        catch (Exception $ex) {
            $result['error']=1;
            $result['except']=$ex->getMessage();
        }
        finally {
            if (is_resource($ch)) {
                curl_close($ch);
            }
        }        
        return $result;        
    }
    
    
    public function getFromContentObject(array $ad_content, bool $extras=false) {
        
        if (isset($ad_content['attrs'])) { unset($ad_content['attrs']); }        
        if (!isset($ad_content['other'])) { $ad_content['other']=""; }        
        if (isset($ad_content['pics']) && empty($ad_content['pics'])) { $ad_content['pics']=new \stdClass(); }
        if (isset($ad_content['pubTo']) && empty($ad_content['pubTo'])) { $ad_content['pubTo']=new \stdClass(); }
        
        $command=['command'=>'normalize', 'json'=>\json_encode($ad_content)];
        
        $res=$this->apiV1normalizer($command['json']);
        if (isset($res['status']) && $res['status']==200) {
            if (!$extras) {
                if (isset($res['data']['wordsList'])) { unset($res['data']['wordsList']); }
                if (isset($res['data']['alterWordsList'])) { unset($res['data']['alterWordsList']); }
            }
            if (isset($res['data']['log'])) { unset($res['data']['log']); }
            if (isset($res['data']['formatA'])) { 
                $res['data']['other']=$res['data']['formatA'];
                unset($res['data']['formatA']);                 
            }
            if (isset($res['data']['formatB'])) { 
                $res['data']['altother']=$res['data']['formatB'];
                unset($res['data']['formatB']);                 
            }
                            
            if (!isset($res['data']['media'])) { $res['data']['media'] = 0; }
          
            return $res['data'];
        }
        else if (isset($res['error']) && $res['error']==1) {
            error_log($res['except']);
        }
        return FALSE;        
    }

    
    function searchByAdId($reference) {
        $db = new DB(true);
        Config::instance()->incLibFile('SphinxQL');
        $sphinx = new SphinxQL(['host'=>'p1.mourjan.com', 'port'=>9307, 'socket'=>NULL], 'ad');
        $sphinx->connect();
        $rs = $db->queryResultArray("select * from ad_user where id=?", [$reference])[0];
        $db->close();        

        $obj = \json_decode($rs['CONTENT'], false);
        $obj->pu=$rs['PURPOSE_ID'];
        $obj->se=$rs['SECTION_ID'];
        $obj->rtl=$rs['RTL'];
        
        $words = \explode(' ', $obj->attrs->ar);
        
        $q = "select id, attrs, locality_id, IF(featured_date_ended>=NOW(),1,0) featured, section_id, purpose_id";
        $sbPhones = "";
        $sbMails = "";
        $sbGeoKeys = "";
        
        if (isset($obj->attrs->geokeys) && !empty($obj->attrs->geokeys)) {
            $sbGeoKeys.=", ANY(";
            $len = count($obj->attrs->geokeys);
            for ($i=0; $i<$len; $i++) {
                if ($i>0) $sbGeoKeys.=" OR ";
                $sbGeoKeys.="x={$obj->attrs->geokeys[$i]}";
            }
            $sbGeoKeys.=" FOR x IN attrs.geokeys) gfilter";
            $q.=$sbGeoKeys;    
        }
        else {
            if (isset($obj->attrs->locality)) {
                $q.=", attrs.locality.id={$obj->attrs->locality->id} gfilter";
            }
            else {
                //error_log($rs['ID']);
                //error_log(\var_export($obj->attrs, true));
                
                $obj->attrs->locality = new stdClass();
                $obj->attrs->locality->id=-1;
            }
        }
        
        $names = [];
        if (!isset($obj->attrs->geokeys)) { $obj->attrs->geokeys=[]; }        
        if (isset($obj->attrs->price)) { $names['price']=0; }        
        if (isset($obj->attrs->rooms)) { $names['rooms']=0; }
        if (isset($obj->attrs->space)) { $names['space']=0; }        
        
        if (isset($obj->attrs->phones) && isset($obj->attrs->phones->n) && !empty($obj->attrs->phones->n)) {
            $len=\count($obj->attrs->phones->n);
            $sbPhones.="ANY(";
            for ($i=0; $i<$len; $i++) {
                $sbPhones.= ($i>0) ? " OR " : "";
                $sbPhones.="BIGINT(x)={$obj->attrs->phones->n[$i]}";                    
            }
            $sbPhones.=" FOR x IN attrs.phones.n)";
        }
        
        if (isset($obj->attrs->mails) && !empty($obj->attrs->mails)) {
            $len=\count($obj->attrs->mails);
            $sbMails.="ANY(";
            for ($i=0; $i<$len; $i++) {
                $sbMails.=($i>0) ? " OR " : "";
                $sbMails.="x='{$obj->attrs->mails[$i]}'";
            }
            $sbMails.=" FOR x IN attrs.mails)";
        }
            
        $contactFilter=false;
        if ($sbPhones && $sbMails) {
            $q.=", ({$sbPhones} OR {$sbMails}) cfilter";
            $contactFilter=true;
        }
        else if ($sbPhones && empty($sbMails)) {
            $q.=", {$sbPhones} cfilter";
            $contactFilter=true;
        }
        else if (empty($sbPhones) && $sbMails) {
            $q.=", {$sbMails} cfilter";
            $contactFilter=true;
        }
        $q.=" FROM ad WHERE id!={$reference} and hold=0 ";
        if ($contactFilter) { $q.='and cfilter=1 '; }
        $q.= 'limit 1000';

        //echo $q, "\n";
        $res=$sphinx->search($q);
        $sphinx->close();

        $len=\count($res['matches']);
        $scores=[];
        $messages=[];
        
        //$x = preg_split('//u', $obj->attrs->ar, null, PREG_SPLIT_NO_EMPTY);
        for ($i=0; $i<$len; $i++) {
            $desc = "";
            $scores[ $res['matches'][$i]['id'] ] = 0;
            
            $attrs = \json_decode($res['matches'][$i]['attrs']);
            
            if (isset($attrs->locality) && isset($obj->attrs->locality) && $attrs->locality->id==$obj->attrs->locality->id) {
                $desc.="G: 100% ";
                $scores[ $res['matches'][$i]['id'] ] += 1;
            }
            elseif (isset($attrs->geokeys)) {
                //error_log(json_encode($attrs->geokeys) . "\t" . json_encode($obj->attrs->geokeys));
                if (empty($obj->attrs->geokeys)) {
                    $geo_score = 0;
                } 
                else {
                    $geo_score = (empty($obj->attrs->geokeys)) ? 0 : count(array_intersect($attrs->geokeys, $obj->attrs->geokeys)) / count($obj->attrs->geokeys);
                }
                
                $scores[ $res['matches'][$i]['id'] ] += $geo_score;
                $desc.="G: ".number_format($geo_score*100) ."% ";
            }                        

            $att_score = 0;
            foreach ($names as $key => $value) {
                if (isset($attrs->$key)) {
                    
                    $att_score+=(ceil($attrs->$key)==ceil($obj->attrs->$key))?1:0;
                    $desc.="[".$key.": " . ((ceil($attrs->$key)== ceil($obj->attrs->$key))?'Y':'N') . "] ";                    
                }
            }
            $scores[$res['matches'][$i]['id']] += count($names)>0 ? $att_score / count($names) : 0;
            
            $desc.=" A: ".  number_format( (count($names)>0 ? $att_score / count($names) : 0)*100)."%";
            
            if (isset($attrs->ar)) {
                $jaccard = $this->jaccardIndex($words, explode(' ', $attrs->ar));
                $scores[$res['matches'][$i]['id']] += $jaccard;
                $desc.= " Similarity: ".number_format($jaccard*100,2).'%';
            }
            $scores[$res['matches'][$i]['id']] += ($res['matches'][$i]['section_id']==$obj->se) ? 1 : 0;
            $scores[$res['matches'][$i]['id']] += ($res['matches'][$i]['purpose_id']==$obj->pu) ? 1 : 0;

            $desc.=" Total: ".number_format($scores[$res['matches'][$i]['id']],2);
            $messages[$res['matches'][$i]['id']] = $desc;             
        }
        
        
        arsort($scores, SORT_NUMERIC);

        $searchResults = ['body'=>['matches'=>[], 'scores'=>[] ]];

        foreach ($scores as $key => $value) {
            if ($value>=0.25) {
                $searchResults['body']['scores'][$key] = $messages[$key];
                $searchResults['body']['matches'][] = $key;
            }
        }
        
        $searchResults['body']['facet'] = $obj->attrs;
        $searchResults['body']['total'] = count($searchResults['body']['matches']);
        $searchResults['body']['total_found'] = $searchResults['body']['total'];
                
        return $searchResults;
    }
    
    
    private function jaccardIndex($a1, $a2) {
        $index = 0.0;
                
        $intersection = array_intersect($a1, $a2);
        if (!empty($intersection)) {
            $union =  array_unique(array_merge($a1,  $a2));        
            $index = count($intersection) /  count($union) ;
            if ($index>1) $index=1.0;
        }
        return $index;
        
    }


    private function longestCommonSubsequence($X,  $s) {
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

}


if (php_sapi_name()=='cli' && get_cfg_var('mourjan.server_id')=='99') {
    //$saveHandler = new MCSaveHandler();
    //$saveHandler->getFromDatabase($argv[1]);
    //$saveHandler->searchByAdId($argv[1]);
    //$saveHandler->testRealEstate(9);
}
