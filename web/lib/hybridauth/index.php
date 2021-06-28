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

function redirectTo(User $user) : void {    
    $router=Router::instance();
    if ($router->referer) {
        $items=\explode('/', $router->referer);
        $len=\count($items);
        if ($len>1) {
            if (!empty($items[$len-1])) {
                $lg=$items[$len-1];
            }
            else {
                $len--;
                $lg=$items[$len-1];
            }
        }
    }
    
    if (isset($lg) && ($lg==='en'||$lg==='ar')) {
        $router->language=$lg;
    }
    $router->cache();
    $url=$router->getURL($user->params['country'], $user->params['city']);  
    $router->close();
    
    HttpClient\Util::redirect($url);
    exit(0);
}


function redirectToUrl($url) {
    HttpClient\Util::redirect($url);
    exit(0);
}


MCSessionHandler::instance();

$user=new User(null, 0);
$user->populate();


if ($user->isLoggedIn()) {   
    $provider=\filter_input(\INPUT_GET, 'logout', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
    \error_log($provider.PHP_EOL);
    if ($provider==='mourjan' || $provider==="mourjan-iphone" || $provider==='Android' || $provider==='mourjan-android') {
        $user->logout();
        redirectTo($user);
    }
}


$newId=0;
$storage=new Session();
$isAndroid=$storage->get('android');

try {
    
    $_provider=\strtolower(\trim(\filter_input(\INPUT_GET, 'provider', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']])));
    if ($_provider) {        
        if ($_provider==='live') {
            $pro='WindowsLive';
        }
        elseif($_provider==='linkedin') {
            $pro='LinkedIn';
        }
        elseif($_provider==='yahoo') {
            $pro='YahooOpenID';
        }
        else {
            $pro=\ucfirst($_provider);
        }
        
        $storage->set('provider', $pro);
        
        if ($_provider==='twitter') {
            $hybridConfig['callback'].='?hauth.done=Twitter';
        }
    }
    
    if (isset($_GET['connect'])) {
        $storage->set('android', true);  
        $isAndroid=true;
    
        $uid=\filter_input(\INPUT_GET, 'uid', \FILTER_VALIDATE_INT)+0;
        $uuid=\urldecode(\filter_input(\INPUT_GET, 'uuid', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
        
        $storage->set('uid', $uid);  
        $storage->set('uuid', $uuid);   
    }
    
    
    $hybridauth=new Hybridauth($hybridConfig);   
    
    if ($provider=$storage->get('provider')) {
        
        $uid=$uuid=$failed=0;
        if ($isAndroid) {
            $uid=$storage->get('uid');
            $uuid=$storage->get('uuid');
        }       
        
        $hybridauth->authenticate($provider);
             
        $storage->set('provider', null);
        
        $adapter=$hybridauth->getAdapter($provider);                
                
        if ($adapter->isConnected()) {
            
            $provider=\strtolower(trim($provider));
            if ($provider==='windowslive') $provider = 'live'; 
            if ($provider==='yahooopenid') $provider = 'yahoo';
            
            $info=$auth_info=$adapter->getUserProfile();  
            //\error_log(var_export($info, true));
            //FIX FOR CHANGING API KEY OF FACEBOOK
            if ($provider==='facebook' && $info->email) {
                $usr=$user->getUserByEmailAndProvider($info->email, $provider);
                if (isset($usr[0]['ID'])) {
                    $auth_info->identifier=$usr[0]['IDENTIFIER'];
                }
            }
            
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
                    $failed=1;
                }                
            }
            else {                      
                $user->updateUserRecord($auth_info, $provider);
            }
         
        }
        else {
            $failed=1;
        }
        
        if ($isAndroid) {            
            $storage->set('android', null); 
            $storage->set('uid', null); 
            $storage->set('uuid', null);
            header('Location: connect://' . ($failed==1?'0':$newId));
            exit(0);            
        }
        else {
            \error_log('here'. var_export($user->pending, true));
            
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
                        if ($user->params['lang']!=='ar') {
                            $uri.=$user->params['lang'].'/';
                        }
                    }
                }
                $user->update();
            }
            else {
                if (!isset($user->params['uri']) || !in_array($user->params['uri'], ['/favorites/', '/account/', '/myads/', '/post/', '/watchlist/', '/statement/', '/buy/', '/buyu/'])) {
                    $uri='/myads/';
                }
                else {
                    $uri=$user->params['uri'];
                }

                //\error_log(__FILE__.PHP_EOL.var_export(Router::instance()->cookie, true).PHP_EOL. 'User: ' .var_export($user->params, true));
                $user->params['lang']=Router::instance()->cookie->lg??'ar';
                
                $hasParam=0;
                if ($user->params['lang']!=='ar') {
                    $uri.=$user->params['lang'].'/';
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
    if (isset($user->params['lang']) && $user->params['lang']!='ar') {
        $url .= $user->params['lang'].'/';
    }
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