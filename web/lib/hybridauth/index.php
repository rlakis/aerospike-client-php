<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2011 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------
require get_cfg_var('mourjan.path'). '/deps/autoload.php';
use mourjan\Hybrid;
use mourjan\EndPoint;
use mourjan\Exception;
use mourjan\Logger;

include_once get_cfg_var('mourjan.path'). '/core/lib/MCSessionHandler.php';
new \MCSessionHandler(TRUE);
//session_set_save_handler($handler, true);
//session_start();

try
{
    Hybrid_Endpoint::process();
}
catch (Exception $e)
{
    error_log($e->getMessage());
}
