<?php

require_once 'vendor/autoload.php';
require_once 'Site.php';

class Redirect extends Site{
    

    function __construct($router){
        parent::__construct($router);
        $userLogin = $this->post('u');
        if($userLogin && $this->isEmail($userLogin)){
            $ref = $this->post('r');
            if(isset($_SERVER['HTTP_REFERER']) && preg_match('/^(?:http|https)\:\/\/(?:www\.|dev\.|dv\.|rc\.|)mourjan\.com/', $_SERVER['HTTP_REFERER']) 
                   && $ref && preg_match('/\/home\/|\/signin\/|\/favorites\/|\/account\/\|\/myads\/|\/post\/|\/watchlist\/|\/buy\/|\/buyu\/|\/statement\//',$ref) ){
                            $userPass = $this->post('p');
                            $keepme_in = $this->post('o','boolean');
                            
                            if($userPass && strlen($userPass)>=6){
                                $pass=false;
                                $cred = DB::getCacheStorage($router->cfg)->get('recaptcha');
                                $recaptcha = new \ReCaptcha\ReCaptcha($cred['secret']);
                                $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                                if ($resp->isSuccess()){

                                    $pass = $this->user->authenticateByEmail($userLogin,$userPass);
                                    if($pass){
                                        $this->user->params['keepme_in']=$keepme_in ? 1:0;
                                        if($keepme_in){
                                            $sKey=$this->user->encodeRequest('keepme_in',array($this->user->info['id']));
                                            setcookie('__uvme', $sKey, time()+1814400,'/',$router->cfg['site_domain']);
                                        }
                                    }else{
                                        $this->user->pending['login_attempt']=true;
                                    }
                                }else{
                                    $this->user->pending['login_attempt_captcha']=true;
                                }
                                if($this->urlRouter->siteLanguage!='ar' && !preg_match('/\/'.$this->urlRouter->siteLanguage.'\//', $ref))$ref.=$this->urlRouter->siteLanguage.'/';
                                if($pass && isset($this->user->pending['redirect_login'])){
                                    $ref=$this->user->pending['redirect_login'];
                                    unset($this->user->pending['redirect_login']);
                                    if(strpos($ref, '/install/')){
                                        if(isset($this->user->info['options']['HS']['lang'])){
                                            if($this->user->info['options']['HS']['lang']!='ar'){
                                                $ref.=$this->user->info['options']['HS']['lang'].'/';
                                            }
                                        }else{
                                            if($this->urlRouter->siteLanguage!='ar' && !preg_match('/\/'.$this->urlRouter->siteLanguage.'\//', $ref))$ref.=$this->urlRouter->siteLanguage.'/';
                                        }
                                    }           
                                }
                                $this->user->update();
                                $this->urlRouter->redirect($ref);
                            }
                        }
        }
        $addLang = ($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/');
        $key = $this->get('k');
        $uri = '/invalid/'.$addLang;
        if($key && strlen($key)>32){
            $cmd = $this->user->decodeRequest($key);
            $userId=0;
            if ($cmd && count($cmd)){
                switch ($cmd['request']){
                    case 'ad_renew':
                        $userId=$cmd['params'][0];
                        $adId=$cmd['params'][1];
                        if(is_numeric($userId) && $userId && is_numeric($adId) && $adId){
                            $this->user->sysAuthById($userId);
                            $ad = $this->user->renewAd($adId);
                            $uriHash='';
                            if($ad && isset($ad[0])) {
                                $uriHash='#'.$adId;
                            }
                            $uri='/myads/'.$addLang.'?sub=pending'.$uriHash;
                        }
                        break;
                    case 'ad_stop':
                        $userId=$cmd['params'][0];
                        $adId=$cmd['params'][1];
                        if(is_numeric($userId) && $userId && is_numeric($adId) && $adId){
                            $uri='/myads/'.$addLang.'#'.$adId;
                            $this->user->sysAuthById($userId);
                            $this->user->params['hold']=$adId;
                            $this->user->update();
                        }
                        break;
                    case 'my_archive':
                        $userId=$cmd['params'][0];
                        $uri='/myads/'.$addLang.'?sub=archive';
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'my_watch':
                        $userId=$cmd['params'][0];
                        if(is_numeric($userId) && $userId){
                            $uri='/watchlist/'.$addLang.'?u='.$this->user->encodeId($userId);
                            if(isset($_GET['edit'])){
                                $uri.='#watchbox';
                            }
                            $this->user->sysAuthById($userId);
                            $this->user->pending['email_watchlist']=1;
                            $this->user->update();
                        }
                        break;
                    case 'channel':
                        $userId=$cmd['params'][0];
                        $channelId=$cmd['params'][1];
                        if(is_numeric($userId) && $userId && is_numeric($channelId) && $channelId){
                            $uri='/watchlist/'.$addLang.'?u='.$this->user->encodeId($userId).'&channel='.$channelId;
                            $this->user->sysAuthById($userId);
                            $this->user->pending['email_watchlist']=1;
                            $this->user->update();
                        }
                        break;
                    case 'my_account':
                        $userId=$cmd['params'][0];
                        $uri='/account/'.$addLang.'?action=notifications';
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'ad_page':
                        $userId=$cmd['params'][0];
                        $adId=$cmd['params'][1];
                        $uri='/'.$adId.'/'.$addLang;
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'my_ads':
                        $userId=$cmd['params'][0];
                        $uri='/myads/'.$addLang;
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'home':
                        $userId=$cmd['params'][0];
                        $uri='/'.$addLang;
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'contact':
                        $userId=$cmd['params'][0];
                        $uri='/contact/'.$addLang;
                        if(is_numeric($userId) && $userId){
                            $this->user->sysAuthById($userId);
                        }
                        break;
                    case 'reset_password':
                        $userId=$cmd['params'][0];
                        if(is_numeric($userId) && $userId && !$this->user->info['id']){
                            $userOptions = $this->user->getOptions($userId);
                            if($userOptions){
                                $userOptions = json_decode($userOptions,true);
                                if(is_array($userOptions)){
                                    if (isset($userOptions['accountKey'])){
                                        if ((isset($_GET['key']) && $_GET['key']==$userOptions['accountKey'])){
                                            $this->user->pending['password_new']=1;
                                            $this->user->pending['user_id']=$userId;
                                            $this->user->update();
                                            $uri='/password/'.$addLang;
                                        }else{
                                            //wrong ticket
                                        }
                                    }elseif (isset($userOptions['resetKey'])){
                                        if ((isset($_GET['key']) && $_GET['key']==$userOptions['resetKey'])){
                                            $this->user->pending['password_reset']=1;
                                            $this->user->pending['user_id']=$userId;
                                            $this->user->update();
                                            $uri='/password/'.$addLang;
                                        }else{
                                            //wrong ticket
                                        }
                                    }else{
                                        //invalid ticket
                                    }
                                }
                            }
                        }
                        break;
                    case 'email_verify':
                        $userId=$cmd['params'][0];
                        if(is_numeric($userId) && $userId){
                            if($this->user->info['id']==$userId){
                                if (isset($this->user->info['options']['emailKey'])) {
                                    if ((isset($_GET['key']) && $_GET['key']==$this->user->info['options']['emailKey'])){
                                         if ($this->user->emailVerified()){
                                             //email verified
                                             $this->user->pending['email_validation']=1;
                                             $this->user->update();
                                             $uri='/account/'.$addLang;
                                         }else{
                                             //failed to verify
                                             $this->user->pending['email_validation']=0;
                                             $this->user->update();
                                             $uri='/account/'.$addLang;
                                         }
                                    }else{
                                        //wrong ticket
                                        $this->user->pending['email_validation']=3;
                                        $this->user->update();
                                        $uri='/account/'.$addLang;
                                    }
                                }else{
                                    //invalid ticket
                                }
                            }else{
                                $userOptions = $this->user->getOptions($userId);
                                if($userOptions){
                                    $userOptions = json_decode($userOptions,true);
                                    if(is_array($userOptions)){
                                        if (isset($userOptions['emailKey'])){
                                            if ((isset($_GET['key']) && $_GET['key']==$userOptions['emailKey'])){
                                                $this->user->pending['email_validation']=2;
                                                $this->user->pending['email_key']=$_GET['key'];
                                                $this->user->update();
                                                $uri='/account/'.$addLang;
                                            }else{
                                                //wrong ticket
                                            }
                                        }else{
                                            //invalid ticket
                                        }
                                    }
                                }
                            }
                        }else{
                            //invalid request
                        }
                        break;
                }
            }
        }
        $this->urlRouter->redirect($uri);
    }
}
?>
