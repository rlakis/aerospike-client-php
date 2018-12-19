<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2011 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------
use mourjan\Hybrid;
use mourjan\EndPoint;
use mourjan\Exception;
use mourjan\Logger;

require Prefs::$dir . '/deps/autoload.php';
include_once Prefs::$dir . '/core/lib/MCSessionHandler.php';
new \MCSessionHandler(TRUE);

try {
    Hybrid_Endpoint::process();
}
catch (Exception $e) {
    error_log($e->getMessage());
}
