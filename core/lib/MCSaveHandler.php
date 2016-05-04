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


// important properties of PHP's integers:
//  - always signed (one bit short of PHP_INT_SIZE)
//  - conversion from string to int is saturated
//  - float is double
//  - div converts arguments to floats
//  - mod converts arguments to ints
// the packing code below works as follows:
//  - when we got an int, just pack it
//    if performance is a problem, this is the branch users should aim for
//
//  - otherwise, we got a number in string form
//    this might be due to different reasons, but we assume that this is
//    because it didn't fit into PHP int
//
//  - factor the string into high and low ints for packing
//    - if we have bcmath, then it is used
//    - if we don't, we have to do it manually (this is the fun part)
//
//    - x64 branch does factoring using ints
//    - x32 (ab)uses floats, since we can't fit unsigned 32-bit number into an int
//
// unpacking routines are pretty much the same.
//  - return ints if we can
//  - otherwise format number into a string
/// pack 64-bit signed
function sphPackI64 ( $v )
{
	assert ( is_numeric($v) );

	// x64
	if ( PHP_INT_SIZE>=8 )
	{
		$v = (int)$v;
		return pack ( "NN", $v>>32, $v&0xFFFFFFFF );
	}
	// x32, int
	if ( is_int($v) )
		return pack ( "NN", $v < 0 ? -1 : 0, $v );
	// x32, bcmath
	if ( function_exists("bcmul") )
	{
		if ( bccomp ( $v, 0 ) == -1 )
			$v = bcadd ( "18446744073709551616", $v );
		$h = bcdiv ( $v, "4294967296", 0 );
		$l = bcmod ( $v, "4294967296" );
		return pack ( "NN", (float)$h, (float)$l ); // conversion to float is intentional; int would lose 31st bit
	}
	// x32, no-bcmath
	$p = max(0, strlen($v) - 13);
	$lo = abs((float)substr($v, $p));
	$hi = abs((float)substr($v, 0, $p));
	$m = $lo + $hi*1316134912.0; // (10 ^ 13) % (1 << 32) = 1316134912
	$q = floor($m/4294967296.0);
	$l = $m - ($q*4294967296.0);
	$h = $hi*2328.0 + $q; // (10 ^ 13) / (1 << 32) = 2328
	if ( $v<0 )
	{
		if ( $l==0 )
			$h = 4294967296.0 - $h;
		else
		{
			$h = 4294967295.0 - $h;
			$l = 4294967296.0 - $l;
		}
	}
	return pack ( "NN", $h, $l );
}
/// pack 64-bit unsigned
function sphPackU64 ( $v )
{
	assert ( is_numeric($v) );

	// x64
	if ( PHP_INT_SIZE>=8 )
	{
		assert ( $v>=0 );

		// x64, int
		if ( is_int($v) )
			return pack ( "NN", $v>>32, $v&0xFFFFFFFF );

		// x64, bcmath
		if ( function_exists("bcmul") )
		{
			$h = bcdiv ( $v, 4294967296, 0 );
			$l = bcmod ( $v, 4294967296 );
			return pack ( "NN", $h, $l );
		}

		// x64, no-bcmath
		$p = max ( 0, strlen($v) - 13 );
		$lo = (int)substr ( $v, $p );
		$hi = (int)substr ( $v, 0, $p );

		$m = $lo + $hi*1316134912;
		$l = $m % 4294967296;
		$h = $hi*2328 + (int)($m/4294967296);
		return pack ( "NN", $h, $l );
	}
	// x32, int
	if ( is_int($v) )
		return pack ( "NN", 0, $v );

	// x32, bcmath
	if ( function_exists("bcmul") )
	{
		$h = bcdiv ( $v, "4294967296", 0 );
		$l = bcmod ( $v, "4294967296" );
		return pack ( "NN", (float)$h, (float)$l ); // conversion to float is intentional; int would lose 31st bit
	}
	// x32, no-bcmath
	$p = max(0, strlen($v) - 13);
	$lo = (float)substr($v, $p);
	$hi = (float)substr($v, 0, $p);

	$m = $lo + $hi*1316134912.0;
	$q = floor($m / 4294967296.0);
	$l = $m - ($q * 4294967296.0);
	$h = $hi*2328.0 + $q;
	return pack ( "NN", $h, $l );
}
// unpack 64-bit unsigned
function sphUnpackU64 ( $v )
{
	list ( $hi, $lo ) = array_values ( unpack ( "N*N*", $v ) );
	if ( PHP_INT_SIZE>=8 )
	{
		if ( $hi<0 ) $hi += (1<<32); // because php 5.2.2 to 5.2.5 is totally fucked up again
		if ( $lo<0 ) $lo += (1<<32);
		// x64, int
		if ( $hi<=2147483647 )
			return ($hi<<32) + $lo;
		// x64, bcmath
		if ( function_exists("bcmul") )
			return bcadd ( $lo, bcmul ( $hi, "4294967296" ) );
		// x64, no-bcmath
		$C = 100000;
		$h = ((int)($hi / $C) << 32) + (int)($lo / $C);
		$l = (($hi % $C) << 32) + ($lo % $C);
		if ( $l>$C )
		{
			$h += (int)($l / $C);
			$l  = $l % $C;
		}
		if ( $h==0 )
			return $l;
		return sprintf ( "%d%05d", $h, $l );
	}
	// x32, int
	if ( $hi==0 )
	{
		if ( $lo>0 )
			return $lo;
		return sprintf ( "%u", $lo );
	}
	$hi = sprintf ( "%u", $hi );
	$lo = sprintf ( "%u", $lo );
	// x32, bcmath
	if ( function_exists("bcmul") )
		return bcadd ( $lo, bcmul ( $hi, "4294967296" ) );

	// x32, no-bcmath
	$hi = (float)$hi;
	$lo = (float)$lo;

	$q = floor($hi/10000000.0);
	$r = $hi - $q*10000000.0;
	$m = $lo + $r*4967296.0;
	$mq = floor($m/10000000.0);
	$l = $m - $mq*10000000.0;
	$h = $q*4294967296.0 + $r*429.0 + $mq;
	$h = sprintf ( "%.0f", $h );
	$l = sprintf ( "%07.0f", $l );
	if ( $h=="0" )
		return sprintf( "%.0f", (float)$l );
	return $h . $l;
}
// unpack 64-bit signed
function sphUnpackI64 ( $v )
{
	list ( $hi, $lo ) = array_values ( unpack ( "N*N*", $v ) );
	// x64
	if ( PHP_INT_SIZE>=8 )
	{
		if ( $hi<0 ) $hi += (1<<32); // because php 5.2.2 to 5.2.5 is totally fucked up again
		if ( $lo<0 ) $lo += (1<<32);
		return ($hi<<32) + $lo;
	}
	// x32, int
	if ( $hi==0 )
	{
		if ( $lo>0 )
			return $lo;
		return sprintf ( "%u", $lo );
	}
	// x32, int
	elseif ( $hi==-1 )
	{
		if ( $lo<0 )
			return $lo;
		return sprintf ( "%.0f", $lo - 4294967296.0 );
	}

	$neg = "";
	$c = 0;
	if ( $hi<0 )
	{
		$hi = ~$hi;
		$lo = ~$lo;
		$c = 1;
		$neg = "-";
	}
	$hi = sprintf ( "%u", $hi );
	$lo = sprintf ( "%u", $lo );
	// x32, bcmath
	if ( function_exists("bcmul") )
		return $neg . bcadd ( bcadd ( $lo, bcmul ( $hi, "4294967296" ) ), $c );
	// x32, no-bcmath
	$hi = (float)$hi;
	$lo = (float)$lo;

	$q = floor($hi/10000000.0);
	$r = $hi - $q*10000000.0;
	$m = $lo + $r*4967296.0;
	$mq = floor($m/10000000.0);
	$l = $m - $mq*10000000.0 + $c;
	$h = $q*4294967296.0 + $r*429.0 + $mq;
	if ( $l==10000000 )
	{
		$l = 0;
		$h += 1;
	}
	$h = sprintf ( "%.0f", $h );
	$l = sprintf ( "%07.0f", $l );
	if ( $h=="0" )
		return $neg . sprintf( "%.0f", (float)$l );
	return $neg . $h . $l;
}
function sphFixUint ( $value )
{
	if ( PHP_INT_SIZE>=8 )
	{
		// x64 route, workaround broken unpack() in 5.2.2+
		if ( $value<0 ) $value += (1<<32);
		return $value;
	}
	else
	{
		// x32 route, workaround php signed/unsigned braindamage
		return sprintf ( "%u", $value );
	}
}
function sphSetBit ( $flag, $bit, $on )
{
	if ( $on )
	{
		$flag |= ( 1<<$bit );
	} else
	{
		$reset = 16777215 ^ ( 1<<$bit );
		$flag = $flag & $reset;
	}
	return $flag;
}




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

    function __construct() 
    {
        // per-client-object settings
        $this->_host		= "localhost";
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


    /// set searchd host name (string) and port (integer)
    function SetServer ( $host, $port = 0 )
    {
        assert ( is_string($host) );
        if ( $host[0] == '/')
        {
            $this->_path = 'unix://' . $host;
            return;
        }

        if ( substr ( $host, 0, 7 )=="unix://" )
        {
            $this->_path = $host;
            return;
        }

        $this->_host = $host;
        $port = intval($port);
        assert ( 0<=$port && $port<65536 );
        $this->_port = ( $port==0 ) ? 1337 : $port;
        $this->_path = '';
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
        $header = fgets ( $fp, 5 );
        if ( strlen($header)==4 )
        {
            $ll = unpack('Nlen', $header);
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

    
    public function setConfig($config)
    {
        $this->cfg = $config;
    }


    public function getFromDatabase($reference)
    {
        $db = new DB($this->cfg);
        $rs = $db->queryResultArray("select * from ad_user where id=?", [$reference])[0];
        $rs['CONTENT'] = json_decode($rs['CONTENT']);
        if (isset($rs['CONTENT']->attrs)) {
            unset($rs['CONTENT']->attrs);
        }
        $command = ['command'=>'normalize', 'json'=>json_encode($rs['CONTENT'])];
        //$buffer = "{\"command\":\"normalize\",\"file\":\"/dev/shm/ad.content.{$reference}.json\"}";
        $buffer = json_encode($command);
        $len = pack('N', strlen($buffer));
        $buffer = $len.$buffer;
        if ($this->_Send($this->_socket, $buffer, strlen($buffer)))
        {
            
            $response = $this->_GetResponse($this->_socket, '');
            if ($response) {
                //echo $response, "\n";
                $j = json_decode($response);
                print_r($j);
            } else {
                echo $this->_error, "\n";
            }
        }
        else
        {
            echo $this->_error, "\n";
        }
    }

    
    public function getFromContentObject($ad_content)
    {
        if (isset($ad_content->attrs)) {
            unset($ad_content->attrs);
        }
        $command = ['command'=>'normalize', 'json'=>json_encode($ad_content)];
        $buffer = json_encode($command);
        $len = pack('N', strlen($buffer));
        $buffer = $len.$buffer;
        if ($this->_Send($this->_socket, $buffer, strlen($buffer)))
        {            
            $response = $this->_GetResponse($this->_socket, '');
            if ($response) {
                $j = json_decode($response, TRUE);
                return $j;
            } else {
                error_log($this->_error);
            }
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

    $saveHandler = new MCSaveHandler();
    $saveHandler->setConfig($config);
    $saveHandler->SetServer("db.mourjan.com");
    $saveHandler->Open();

    if ($saveHandler->_error == '') {
        $saveHandler->getFromDatabase($argv[1]);
    }
    
}
