<?php 
//if (!isset($argc)) {tideways_xhprof_enable();}
include_once __DIR__ . '/deps/autoload.php';
include_once __DIR__ . '/config/cfg.php';

Config::instance()->incModelFile('Router')->incModelFile('Db')->incLibFile('MCSessionHandler');

use Core\Model\Router;
use Core\Model\DB;

if (isset($_GET['provider']) && isset($_GET['connect'])) {
    try {        
        $connect = strtolower(filter_input(INPUT_GET, 'connect', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
        $provider = strtolower(filter_input(INPUT_GET, 'provider', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));

    	$uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
    	$uuid = urldecode(filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
    	
    	if ($uid>0 && $uuid && $connect=='android' && in_array($provider, ['facebook', 'twitter', 'google', 'linkedin', 'yahoo', 'live'])) {
            new MCSessionHandler();
            try {
            	$hybridauth = new Hybrid_Auth( $config['dir'].'/web/lib/hybridauth/config.php' );
            	$adapter = $hybridauth->authenticate( $provider );
          
            	$failed=0;
            	if ($hybridauth->isConnectedWith( $provider )) {
                    require_once($config['dir'].'/core/model/User.php');
                    $auth_info = $adapter->getUserProfile();
                    $db = new DB($config);
                    $user = new User($db, $config, null, 0);
                    $newId = $user->connectDeviceToAccount($auth_info, $provider, $uid, $uuid);
                    if ($newId==0) {
                        $failed = 1;
                    }
            	} 
                else {
                    $failed=1;
            	}
            } 
            catch( Exception $e ) {
            	error_log($e->getMessage());
            	$failed = 1;
            }
        
            header('Location: connect://' . ($failed==1 ? '0' : $newId));
    	}
    } 
    catch(Exception $e) {
        header('Location: connect://0');
    }
    exit(0);
}


if (php_sapi_name()!='cli') {
    MCSessionHandler::getInstance();
    //require_once( $config['dir'].'/core/model/User.php');
    //$user = new User(new DB($config), $config, null, 0);
    //$user->sysAuthById(717151);
}


$router = Router::instance();

if (!isset($argc)) {
    $router->decode();
    $stop=false;
    $provider = strtolower(filter_input(INPUT_GET, 'provider', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));

    if ($provider) {
        if (in_array($provider, ['facebook', 'twitter', 'google', 'linkedin', 'yahoo', 'live', 'mourjan'])) {
            try {
                $hybridauth = new Hybrid_Auth( $config['dir'].'/web/lib/hybridauth/config.php' );
                $adapter = $hybridauth->authenticate( $provider );
                $failed=0;
                
                if ($hybridauth->isConnectedWith($provider)) {
                    require_once( $config['dir'].'/core/layout/Site.php' );
                    require_once( $config['dir'].'/core/model/User.php' );
                    $site = new Site($router);
                    $auth_info = $adapter->getUserProfile();
                    $user = new User($router->db, $config, $site);
                    $user->updateUserRecord($auth_info, $provider);
                }
                else {
                    $failed=1;
                }
                
                if (!$failed && isset($user->pending['social_new'])) {
                    $uri='/welcome/';
                    if($router->siteLanguage!='ar')$uri.=$router->siteLanguage.'/';
                }
                elseif (isset($user->pending['redirect_login'])) {
                    $uri=$user->pending['redirect_login'];
                    unset($user->pending['redirect_login']);
                    if (strpos($uri, '/install/')) {
                        if (isset($user->info['options']['HS']['lang'])) {
                            if ($user->info['options']['HS']['lang']!='ar') {
                                $uri.=$user->info['options']['HS']['lang'].'/';
                            }
                        }
                        else {
                            if($router->siteLanguage!='ar') { $uri.=$router->siteLanguage.'/'; }
                        }
                    }
                    $user->update();
                }
                else {
                    if (!$router->isMobile && !in_array($router->uri, ['/favorites/','/account/','/myads/','/post/','/watchlist/','/statement/','/buy/','/buyu/'])) {
                        $uri='/home/';
                    }
                    else {
                        $uri = $router->uri;
                    }
                    
                    $hasParam=0;
                    if($router->siteLanguage!='ar')$uri.=$router->siteLanguage.'/';
                    if($router->id)$uri.=$router->id.'/';
                    if($router->params['start'])$uri.=$router->params['start'].'/';
                    
                    if($router->module=='watchlist'||$router->module=='favorites') {
                        $uri.='?u='.$user->info['idKey'];
                        $hasParam=1;
                    }
                    
                    if($failed) {
                        $uri.=($hasParam)?'&':'?';                      
                        $uri .='signin=error';
                    }
                }
                $router->redirect($uri, 302);
            }
            catch( Exception $e ) {
                error_log($e->getMessage());
                $uri = $router->uri;
                switch ($e->getCode()) {
                    case 5:
                        break;
                    default:
                        $uri.='?signin=error';
                        break;
                }
                $router->redirect($uri, 302);
            }
        }
        else {
            $stop=true;
        }            
    }
    
    if (!$stop && array_key_exists($router->module, $config['modules'])) {
        $mod_class = $config['modules'][$router->module][0];
        include_once $config['dir'].($router->module=='cache'?'/core/gen/':'/core/layout/').$mod_class.'.php';        
        $object = new $mod_class( $router );    
    }     
    else {
        include_once Prefs::$dir.'/core/layout/NotFound.php';
        header('HTTP/1.0 404 Not Found');
        new NotFound($router);    
    }
    
    $router->close();
    /*
    $data = tideways_xhprof_disable();
    //file_put_contents(sys_get_temp_dir() . "/" . uniqid() . ".mourjan.xhprof", serialize($data));
    $XHPROF_ROOT = realpath(dirname(__FILE__).'/web/xhprof');
    include_once $XHPROF_ROOT . "/lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/lib/utils/xhprof_runs.php";
    
    $xhprof_runs = new XHProfRuns_Default();

    // save the run under a namespace "xhprof_foo"
    $run_id = $xhprof_runs->save_run($data, "xhprof_mourjan");
    echo '<p>&nbsp;&nbsp;<a target=_blank href="', "https://h1.mourjan.com/web/xhprof/html/index.php?run=$run_id&source=xhprof_mourjan", '">Page profiler</a></p><br/>';
    
     * 
     */
}
