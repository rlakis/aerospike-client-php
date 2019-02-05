<?php

include_once __DIR__ . '/../../../deps/autoload.php';
include_once __DIR__ . '/../../../config/cfg.php';
Config::instance()->incModelFile('Router')->incModelFile('Db')->incModelFile('User')->incLibFile('MCSessionHandler');
include_once __DIR__.'/conf.php';

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

use Core\Model\Router;

function redirectTo($user) : void {    
    $router = Router::getInstance();
    $router->language = $user->params['slang'];
    $router->cache();
    $url = $router->getURL($user->params['country'], $user->params['city']);  
    $router->close();
    
    HttpClient\Util::redirect($url);
    exit(0);
}


function redirectToUrl($url) {
    HttpClient\Util::redirect($url);
    exit(0);
}


MCSessionHandler::getInstance();

$storage = new Session();
$isAndroid = $storage->get('android');

$user = new User(null, 0);
$user->populate();

$newId = 0;

if ($user->id() && isset($_GET['logout'])) {   
    $provider = filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_STRING);
    if ($provider=='mourjan' || $provider=="mourjan-iphone" || $provider=='Android' || $provider=='mourjan-android') {        
        $user->logout();
        redirectTo($user);
    }
}
    

try {
    if (isset($_GET['provider'])) {
        $pro = trim($_GET['provider']);
        
        if ($pro=='live') {
            $pro = 'WindowsLive';
        }
        elseif(strtolower($pro)=='linkedin') {
            $pro = 'LinkedIn';
        }
        elseif(strtolower($pro)=='yahoo') {
            $pro = 'YahooOpenID';
        }
        else {
            $pro = ucfirst($pro);
        }
        
        $storage->set('provider', $pro);
        
        if ($_GET['provider']=='Twitter') {
            $hybridConfig['callback'].='?hauth.done=Twitter';
        }
    }
    
    if (isset($_GET['connect'])) {
        $storage->set('android', true);  
        $isAndroid = true;
    
        $uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
        $uuid = urldecode(filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
        
        $storage->set('uid',$uid);  
        $storage->set('uuid',$uuid);   
    }
    
    
    $hybridauth = new Hybridauth($hybridConfig);   
    
    if ($provider=$storage->get('provider')) {
        
        $uid = 0;
        $uuid = 0;
        if ($isAndroid) {
            $uid = $storage->get('uid');
            $uuid = $storage->get('uuid');
        }       
        
        $hybridauth->authenticate($provider);
        
        $failed=0;
     
        $storage->set('provider', null);
        
        $adapter = $hybridauth->getAdapter($provider);                
                
        if ($adapter->isConnected()) {                      
            
            $provider=strtolower(trim($provider));
            if($provider == 'windowslive') $provider = 'live';            
            if($provider == 'yahooopenid') $provider = 'yahoo';            
            
            $info = $auth_info = $adapter->getUserProfile();  
            
//            error_log("1 ".$info->identifier);
//            error_log("2 ".(is_null($info->email) ? '' : $info->email));
//            error_log("3 ".trim(($info->firstName ? $info->firstName : '').' '.($info->lastName ? $info->lastName : '')));
//            error_log("4 ".(!is_null($info->displayName) ? $info->displayName : ''));
//            error_log("5 ".(!is_null($info->profileURL) ? $info->profileURL : '')); 
            
            if ($isAndroid) {
                if ($uid>0 && $uuid) {
                    $newId = $user->connectDeviceToAccount($auth_info, $provider, $uid, $uuid);
                    if ($newId==0) { $failed = 1; }
                }
                else {
                    $failed = 1;
                }                
            }
            else {                      
                $user->updateUserRecord($auth_info, $provider);
            }
         
        }
        else {
            $failed = 1;
        }
        
        if ($isAndroid) {            
            $storage->set('android', null); 
            $storage->set('uid', null); 
            $storage->set('uuid', null);             
            header('Location: connect://' . ($failed==1 ? '0' : $newId));
            exit(0);            
        }
        else {
            if (isset($user->pending['redirect_login'])) {
                $uri=$user->pending['redirect_login'];
                unset($user->pending['redirect_login']);
                if (strpos($uri, '/install/')) {
                    if (isset($user->info['options']['HS']['lang'])) {
                        if ($user->info['options']['HS']['lang']!='ar') {
                            $uri.=$user->info['options']['HS']['lang'].'/';
                        }
                    }
                    else {
                        if ($user->params['slang']!='ar') {
                            $uri.=$user->params['slang'].'/';
                        }
                    }
                }
                $user->update();
            }
            else {
                if (!isset($user->params['uri']) || !in_array($user->params['uri'], ['/favorites/', '/account/', '/myads/', '/post/', '/watchlist/', '/statement/', '/buy/', '/buyu/'])) {
                    $uri='/home/';
                }
                else {
                    $uri=$user->params['uri'];
                }

                $hasParam=0;
                if ($user->params['slang']!='ar') {
                    $uri.=$user->params['slang'].'/';
                }            

                if ($failed) {
                    $uri.=($hasParam)?'&':'?';                      
                    $uri.='signin=error';
                }
            }
            
            redirectToUrl($uri);        
        }
    }
    
    
    /**
     * This will erase the current user authentication data from session, and any further
     * attempt to communicate with provider.
     */
    if ($user->id() && isset($_GET['logout'])) {   
        $provider=$_GET['logout'];
        if ($provider=='live') { 
            $provider = 'WindowsLive';             
        }
        elseif ($provider=='yahoo') { 
            $provider = 'YahooOpenID';             
        }
        elseif ($provider=='linkedin') { 
            $provider = 'LinkedIn';
        }
        else {
            $provider = ucfirst($provider);
        }
        
        if ($provider!='mourjan' && $provider!='mourjan-iphone' && $provider!='Android' && $provider!='mourjan-android') {
            $adapter = $hybridauth->getAdapter($provider);
            $adapter->disconnect();
        }
        $user->logout();
        
        redirectTo($user);
    }
    
    /**
     * Redirects user to home page (i.e., index.php in our case)
     */
    $url = '/';
    if (isset($user->params['slang']) && $user->params['slang']!='ar') {
        $url .= $user->params['slang'].'/';
    }
    error_log($url);
    HttpClient\Util::redirect($url);
} 
catch (Exception $e) {
    error_log('HYBRIDAUTH:: '.$e->getMessage());  
    if ($isAndroid) {
        header('Location: connect://0');
    }
    else {
        HttpClient\Util::redirect('/');        
    }  
}