<?php

include_once get_cfg_var('mourjan.path') . '/config/cfg.php';

error_reporting(E_ALL);
ini_set('display_errors', php_sapi_name()=='cli'?'1':'0');

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = 'h8.mourjan.com';
$port = 1337;



class MCSaveHandler
{
    var $_host;			///< searchd host (default is "db.mourjan.com")
    var $_port;			///< searchd port (default is 1337)

    private $address;
    private $_socket;
    private $cfg;

    private $client;

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
        $this->_mbenc		= "";
        $this->_arrayresult	= false;
        $this->_timeout		= 0;

        /* Get the IP address for the target host. */
        //$this->address = gethostbyname('h8.mourjan.com');

        //$this->client = stream_socket_client("tcp://h8.mourjan.com:{$service_port}", $errno, $errstr, 30);
        //if (!$this->client) {
        //    echo "$errstr ($errno)<br />\n";
        //}
    }


    function __destruct()
    {
        if ( $this->_socket !== false )
            fclose ( $this->_socket );
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

    /// enter mbstring workaround mode
    function _MBPush ()
    {
        $this->_mbenc = "";
        if ( ini_get ( "mbstring.func_overload" ) & 2 )
        {
            $this->_mbenc = mb_internal_encoding();
            mb_internal_encoding ( "latin1" );
        }
    }


    /// leave mbstring workaround mode
    function _MBPop ()
    {
        if ( $this->_mbenc )
            mb_internal_encoding ( $this->_mbenc );
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

        /*
        // send my version
        // this is a subtle part. we must do it before (!) reading back from searchd.
        // because otherwise under some conditions (reported on FreeBSD for instance)
        // TCP stack could throttle write-write-read pattern because of Nagle.
        if ( !$this->_Send ( $fp, pack ( "N", 1 ), 4 ) )
        {
            fclose ( $fp );
            $this->_error = "failed to send client protocol version";
            return false;
        }

        // check version
        list(,$v) = unpack ( "N*", fread ( $fp, 4 ) );
        $v = (int)$v;
        if ( $v<1 )
        {
            fclose ( $fp );
            $this->_error = "expected searchd protocol version 1+, got version '$v'";
            return false;
        }
         *
         */
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
        /*
        // check status
        if ( $status==SEARCHD_WARNING )
        {
            list(,$wlen) = unpack ( "N*", substr ( $response, 0, 4 ) );
            $this->_warning = substr ( $response, 4, $wlen );
            return substr ( $response, 4+$wlen );
        }

        if ( $status==SEARCHD_ERROR )
        {
            $this->_error = "searchd error: " . substr ( $response, 4 );
            return false;
        }

        if ( $status==SEARCHD_RETRY )
        {
            $this->_error = "temporary searchd error: " . substr ( $response, 4 );
            return false;
        }

        if ( $status!=SEARCHD_OK )
        {
            $this->_error = "unknown status code '$status'";
            return false;
        }

        // check version
        if ( $ver<$client_ver )
        {
            $this->_warning = sprintf ( "searchd command v.%d.%d older than client's v.%d.%d, some options might not work",
                    $ver>>8, $ver&0xff, $client_ver>>8, $client_ver&0xff );
        }
         * 
         */
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
                    and ad.id>6400000
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
    $saveHandler->getFromDatabase($argv[1]);
    //$saveHandler->testRealEstate(7);
    
}
