<?php
if (!isset($argc)) {tideways_xhprof_enable();}

include_once __DIR__ . '/config/cfg.php';
include_once __DIR__ . '/deps/autoload.php';

Config::instance()->incModelFile('Router')->incModelFile('Db')->incLibFile('MCSessionHandler')->incLibFile('Logger');

use Core\Model\Router;

if (\filter_has_var(\INPUT_GET, 'provider') && \filter_has_var(\INPUT_GET, 'connect')) {
    $connect=\strtolower(\filter_input(\INPUT_GET, 'connect', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
    $provider=\strtolower(\filter_input(\INPUT_GET, 'provider', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));    
    $uid=\filter_input(\INPUT_GET, 'uid', \FILTER_VALIDATE_INT)+0;
    $uuid=\urldecode(\filter_input(\INPUT_GET, 'uuid', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
    
    \header("Location: /web/lib/hybridauth/?connect={$connect}&provider={$provider}&uid={$uid}&uuid={$uuid}");
    exit(0);
}


if (php_sapi_name()!=='cli') {
    MCSessionHandler::instance();
    //require_once( $config['dir'].'/core/model/User.php');
    //$user = new User(new DB($config), $config, null, 0);
    //$user->sysAuthById(717151);
}


$router=Router::instance();

if (!isset($argc)) {
    $router->setLogger(new \Core\Lib\Logger('/var/log/mourjan', \Psr\Log\LogLevel::DEBUG, ['filename' => 'site.log', 'logFormat'=>false]));
    $router->decode();
    $stop=false;
    $provider=\strtolower(\filter_input(\INPUT_GET, 'provider', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
   
    if (!$stop && \array_key_exists($router->module, $config['modules'])) {
        $mod_class = $config['modules'][$router->module][0];
        include_once Config::instance()->baseDir.($router->module==='cache'?'/core/gen/':'/core/layout/').$mod_class.'.php';        
        $object = new $mod_class();            
    }
    else {
        include_once Config::instance()->baseDir.'/core/layout/NotFound.php';
        \header('HTTP/1.0 404 Not Found');
        new NotFound($router);    
    }
    
    $router->close();
    
    /*
    $contentType=\filter_input(\INPUT_SERVER, 'CONTENT_TYPE', \FILTER_SANITIZE_STRING);
    $requestURI=\filter_input(\INPUT_SERVER, 'REQUEST_URI', \FILTER_SANITIZE_STRING);    
    
    if ($contentType!=='application/json' && \strpos($requestURI, 'ajax-')==false) {
        $data=tideways_xhprof_disable();
        $XHPROF_ROOT= realpath(dirname(__FILE__).'/web/xhprof');
        include_once $XHPROF_ROOT."/lib/utils/xhprof_lib.php";
        include_once $XHPROF_ROOT."/lib/utils/xhprof_runs.php";
    
        $xhprof_runs=new XHProfRuns_Default();

        $run_id=$xhprof_runs->save_run($data, "xhprof_mourjan");
        echo '<p style="background-color:var(--mlc);height:60px;display:flex;justify-content:center;margin:0">&nbsp;&nbsp;<a rel=noopener style="color:white;" target=_blank href="', "https://h1.mourjan.com/web/xhprof/html/index.php?run=$run_id&source=xhprof_mourjan", '">Page profiler</a></p>';
    }*/
}
