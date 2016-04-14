<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2011 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------
require 'vendor/autoload.php';
use hybridauth\Hybrid;
use hybridauth\EndPoint;
use hybridauth\Exception;
use hybridauth\Logger;

include_once get_cfg_var('mourjan.path'). '/core/lib/MCSessionHandler.php';
new MCSessionHandler();
//session_set_save_handler($handler, true);
//session_start();

try {
	Hybrid_Endpoint::process();
} catch (Exception $e) {
	error_log($e->getMessage());
}
