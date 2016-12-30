<?php

require_once 'vendor/autoload.php';
use mourjan\Hybrid;
use mobiledetect\MobileDetect;

class User {

    var $session_id=null;

    var $info=array();
    var $params=array();
    var $pending=array();
    var $favorites=array();

    var $db=null, $cfg=null, $site=null;
    
    var $md5_prefix='ZGr63LE02Ad';
    
    var $idBase=array(
        5632795166432,
        9943557972664,
        2479658558292,
        3665489771464,
        4699688756641,
        6682247563955,
        7125469862287,
        8897523644763,
        7998546321915,
        9123568714121
    );
    
    var $encryptIdx=array(
        array(6, 5, 22, 15, 29, 7, 14, 30, 27, 18, 25, 2, 12, 31, 1, 0, 8, 20, 28, 3, 16, 13, 26, 17, 10, 19, 9, 24, 4, 11, 21, 23),
        array(17, 20, 19, 12, 23, 26, 7, 22, 28, 13, 4, 16, 21, 31, 6, 3, 8, 30, 15, 11, 9, 18, 29, 2, 1, 0, 25, 24, 14, 27, 10, 5),
        array(14, 7, 22, 2, 13, 15, 16, 21, 28, 18, 19, 17, 4, 27, 10, 29, 26, 24, 12, 11, 8, 9, 5, 20, 0, 3, 1, 6, 30, 25, 31, 23),
        array(11, 8, 27, 1, 12, 10, 30, 21, 4, 2, 22, 16, 20, 18, 7, 17, 6, 19, 28, 26, 29, 3, 31, 24, 0, 15, 5, 25, 14, 9, 13, 23),
        array(28, 19, 4, 0, 10, 11, 16, 20, 27, 26, 8, 22, 31, 15, 13, 25, 18, 6, 1, 5, 3, 24, 14, 12, 29, 23, 2, 30, 9, 17, 21, 7),
        array(25, 29, 11, 13, 14, 3, 30, 20, 2, 9, 28, 18, 23, 6, 8, 5, 24, 31, 17, 10, 7, 22, 16, 27, 12, 1, 26, 0, 15, 19, 21, 4),
        array(24, 8, 26, 14, 28, 19, 1, 25, 16, 23, 7, 6, 10, 29, 5, 18, 20, 0, 21, 17, 4, 27, 15, 13, 2, 11, 3, 12, 22, 9, 30, 31),
        array(29, 2, 27, 21, 8, 13, 30, 12, 25, 14, 15, 20, 5, 31, 18, 7, 24, 17, 9, 23, 19, 16, 3, 4, 6, 1, 26, 22, 28, 11, 0, 10),
        array(12, 21, 30, 18, 1, 31, 27, 13, 19, 23, 3, 26, 14, 11, 28, 24, 20, 4, 5, 17, 8, 15, 22, 16, 7, 10, 0, 29, 6, 2, 25, 9),
        array(2, 11, 16, 27, 15, 29, 4, 9, 23, 20, 1, 31, 26, 13, 18, 3, 0, 6, 17, 7, 30, 8, 24, 5, 10, 14, 28, 12, 22, 19, 25, 21)
    );
    
    var $reqHash=array(
        'e461e788d74de3f3a49cc04a36eb72c2'  =>  'my_archive',
        '8de2b6a12c67d956cdf00cd318daa226'  =>  'my_account',
        'a77c840f2bd1078cbc0a356540fbfa08'  =>  'my_ads',
        '525cb07e4cf3d425cb9c41220df97c40'  =>  'my_watch',
        'd92cc5dae6d97b6d592650e83b32b251'  =>  'ad_renew',
        'f58f7fef6a1b988d6ce19e529e332040'  =>  'ad_stop',
        '9c8b9060876a9c4d9bd79b6118a6c666'  =>  'ad_page',
        '479bc15c9c81fb1ee680f0487dba7e06'  =>  'home',
        '523294970dfaa9fb670c21b954756f4b'  =>  'contact',
        'fae0e60c8cde5b6084dedf58834be71b'  =>  'email_verify',
        '49d000e990e4a7d1f51e8294cfdfb0da'  =>  'channel',
        '57b98a1e868cec85e78bb8497b022af3'  =>  'reset_password',
        '391dde51be78c5961597bcdb01db6b9e'  =>  'keepme_in'
    );
    
    function encodeRequest($request,$params=array()){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $intToChar=array('a','f','h','x','j','d','b','o','n','k');
        $key = md5('ZGr63LE02Ad'.$request);
        $pCnt = count($params);
        if($pCnt) {
            foreach($params as $param){
                $idx = $param % 10;
                $param = $this->encodeId($param);
                $len=strlen($param);
                for($i=0;$i<$len;$i++){
                    $x=$this->encryptIdx[$idx][$i];
                    $key = substr($key, 0, $x).$param[$i].substr($key, $x);
                }
                $key = $len.$intToChar[$idx].$key;
            }
            $key = $pCnt.$chars[rand(0,50)].$key;
        }
        return $key;
    }
    
    function decodeRequest($key){
        $params=array(
            'request'=>'',
            'params'=>array()
        );
        $charToInt=array('a'=>0,'f'=>1,'h'=>2,'x'=>3,'j'=>4,'d'=>5,'b'=>6,'o'=>7,'n'=>8,'k'=>9);
        $matches=null;
        preg_match('/^([0-9]*)/',$key,$matches);
        if($matches && count($matches)){
            $paramsLen = $matches[1];
            $key = substr($key, strlen($paramsLen)+1);
            $paramsLen = (int)$paramsLen;
            
            for ($k=0;$k<$paramsLen;$k++){                
                $matches=null;
                preg_match('/^([0-9]*)/',$key,$matches);
                if($matches && count($matches)){
                    $idLen = $matches[1];
                    $key = substr($key, strlen($idLen));

                    $idLen = (int)$idLen;
                    $idx = $key[0];
                    if(isset($charToInt[$idx])){
                        $idx = $charToInt[$idx];
                        $key = substr($key, 1);
                        $rId='';
                        for($i=$idLen-1;$i>=0;$i--){
                            $x=$this->encryptIdx[$idx][$i];
                            $rId=$key[$x].$rId;
                            if($x){
                                $key = substr($key, 0, $x).substr($key, $x+1);
                            }else {                        
                                $key = substr($key, 1);
                            }
                        }
                        $params['params'][]=$this->decodeId($rId);
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
            if(isset($this->reqHash[$key])){
                $params['request']=$this->reqHash[$key];
            }else{
                return false;
            }
        }else {
            return false;
        }
        if(count($params['params'])){
            $params['params']=array_reverse($params['params']);
        }
        return $params;
    }

    function __construct($db, $config, $site, $init=1){

        $this->db=$db;
        $this->cfg=$config;
        $this->reset();
        if($init){
            $this->site=$site;
            
            $this->populate();
            $this->getSessionHandlerCookieData();
            if($this->info['id']){
                $refreshUser=$this->db->getCache()->get('re'.$this->info['id']);
                if($refreshUser){
                    $this->reloadData($this->info['id']);
                    $this->db->getCache()->delete('re'.$this->info['id']);
                }
                $this->params['mourjan_user']=1;
            }
            
            if(isset($_GET['sort']) && in_array($_GET['sort'], array(0,1,2))){
                $this->params['sorting'] = (int)$_GET['sort'];
            }
            if(isset($this->params['sorting'])){                
                $site->sortingMode = $this->params['sorting'];
            }
            if(isset($_GET['hr']) && in_array($_GET['hr'], array(0,1,2))){
                $this->params['list_lang'] = (int)$_GET['hr'];
            }
            if(isset($this->params['list_lang'])){                
                $site->langSortingMode = $this->params['list_lang'];
            }
            if(isset($_GET['xd']) && in_array($_GET['xd'], array(0,1,2))){
                $this->params['list_publisher'] = (int)$_GET['xd'];
            }
            if(isset($this->params['list_publisher'])){                
                $site->publisherTypeSorting = $this->params['list_publisher'];
            }
            
            $this->authenticate();
            $this->setCookieData();
            if (!isset($this->params['visit']) || $site->urlRouter->module=='oauth') {
                //$countryId=0;
                //if (isset($this->params['country'])) $countryId=  $this->params['country'];
                $this->getCookieData();
                
                if(!$this->info['id'] && isset($_COOKIE['__uvme']) && $_COOKIE['__uvme']){
                    $cmd = $this->decodeRequest($_COOKIE['__uvme']);
                    if($cmd && $cmd['request']=='keepme_in'){
                        if(is_numeric($cmd['params'][0])){
                            $this->sysAuthById($cmd['params'][0]);
                        }
                    }
                }
                
                //if (!$countryId && ($site->urlRouter->uri=='' || $site->urlRouter->uri=='/') && isset($this->params['country']) && !$this->isSpider()){
                //    $site->urlRouter->countryId=$this->params['country'];
                //}

                $device = new Mobile_Detect();
                if($device->isMobile()){
                    if( $device->isiOS() ){
                        if(preg_replace('/_.*/','',$device->version('iPhone')) > 7){
                            $this->params['mobile_ios_app_bottom_banner']=1;
                        }
                    }
                    if( $device->isAndroidOS() ){
                        $this->params['mobile_android_app_bottom_banner']=1;
                    }
                }else{
                
                    include_once $config['dir'].'/core/lib/Browser.php';
                    $browser = new Browser();
                    $bname = strtolower($browser->getBrowser());
                    $bversion = (int)$browser->getVersion();
                    $bplatform=  strtolower($browser->getPlatform());

                    if($bplatform=='windows'){
                        switch($bname){
                            case 'internet explorer':
                                if($bversion < 9){
                                    $this->params['browser_alert']=1;
                                }
                                if($bversion < 8){
                                    $this->params['include_JSON']=1;
                                }
                                break;
                            case 'firefox':
                                if($bversion < 18){
                                    $this->params['browser_alert']=1;
                                }
                                break;
                            case 'chrome':
                                if($bversion < 10){
                                    $this->params['browser_alert']=1;
                                }
                                break;
                            default:
                                break;
                        }
                        if(isset($this->params['browser_alert'])){
                            if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
                                $blang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                                $this->params['browser_link']='https://www.google.com/intl/'.$blang.'/chrome/browser/?brand=CHMO#eula';
                            }else{
                                $this->params['browser_link']='https://www.google.com/intl/en/chrome/browser/?brand=CHMO#eula';
                            }
                        }
                    }
                }
            }
            //if (isset($this->pending['fav'])) unset($this->pending['fav']);
            $this->update();

            if (isset( $_GET["connected_with"] )) {
                unset($_GET["connected_with"]);
                if ($this->info['level']==5) $currentUrl='/blocked/'.($site->urlRouter->siteLanguage=='ar'?'':$site->urlRouter->siteLanguage.'/');
                elseif ($this->info['level']==6) $currentUrl='/suspended/'.($site->urlRouter->siteLanguage=='ar'?'':$site->urlRouter->siteLanguage.'/');
                elseif(isset($this->pending['social_new'])) {
                    $currentUrl.='/welcome/'.($site->urlRouter->siteLanguage=='ar'?'':$site->urlRouter->siteLanguage.'/');
                }elseif(isset($this->pending['redirect_login'])){
                    $currentUrl=$this->pending['redirect_login'];
                    unset($this->pending['redirect_login']);
                    $this->update();
                }else{
                    $currentUrl=$this->site->urlRouter->cfg['host'].$_SERVER['REQUEST_URI'];
                    $currentUrl=  preg_replace('/([?&])connected_with=.*?(?:&|$)/', '$1', $currentUrl);
                    $lastChar=substr($currentUrl, -1);
                    if ($lastChar=='&' || $lastChar=='?') $currentUrl= substr($currentUrl, 0, strlen ($currentUrl)-1);                    
                }
                $this->redirectTo($currentUrl);
            }
        }
    }
    
    function isSuperUser(){
        return in_array($this->info['id'],array(1,2,2100));
    }
    
    function getAdminFilters(){
        $filters=[];
        $filters['root'] = (isset($_GET['fro']) && $_GET['fro']) ? $_GET['fro'] : 0;
        $filters['purpose'] = (isset($_GET['fpu']) && $_GET['fpu']) ? $_GET['fpu'] : 0;
        $filters['lang'] = (isset($_GET['fhl']) && $_GET['fhl']) ? $_GET['fhl'] : 0;
        $filters['uid'] = (isset($_GET['fuid']) && $_GET['fuid']) ? $_GET['fuid'] : 0;
        $filters['active']=false;
        foreach ($filters as $key => $value){
            $filters['active'] = $value>0;
            if($filters['active']){
                break;
            }
        }
        return $filters;
    }
    
    function setReloadFlag($id){
        $ssh=null;
        $this->db->getCache()->set('re'.$id, 1);  
        if ($this->cfg['server_id']==2) {//h2
            $ssh = ssh2_connect('h3.mourjan.com', 22);
            if (ssh2_auth_password($ssh, 'root', '4CqHwDqf')) {
                $stream = ssh2_exec($ssh, 'php /usr/local/bin/mcset_user_reload.php '.$id);
            }
        }    
        if ($this->cfg['server_id']==3) {//h3
            $ssh = ssh2_connect('h2.mourjan.com', 22);
            if (ssh2_auth_password($ssh, 'root', 'LwA4Qaeb')) {
                $stream = ssh2_exec($ssh, 'php /usr/local/bin/mcset_user_reload.php '.$id);
            }
        }
    }
    
    function encodeId($id){
        $intToChar=array('a','f','h','x','j','d','b','o','n','k');
        $idx=$id % 10;
        $id = $id + $this->idBase[$idx];
        $id = base_convert( $id , 10, 36);
        $id = $intToChar[$idx].$id;
        return $id;
    }
    
    function decodeId($id){
        $charToInt=array('a'=>0,'f'=>1,'h'=>2,'x'=>3,'j'=>4,'d'=>5,'b'=>6,'o'=>7,'n'=>8,'k'=>9);
        $idx = substr($id, 0, 1);
        if(isset($charToInt[$idx])){
            $idx = $charToInt[$idx];
            $id = substr($id, 1);
            $id = base_convert( $id , 36, 10);
            $id = $id - $this->idBase[$idx];
            if($id<0) $id =0;
        }else {
            $id = 0;
        }
        return $id;
    }
    
    function checkAccount($email){
        $user = $this->db->queryResultArray(
            'select * from web_users where IDENTIFIER=?',
            array($email));
        return $user;
    }
    
    function getAccount($id){
        $user = $this->db->queryResultArray(
            'select * from web_users where id=?',
            array($id));
        return $user;
    }
    
    function resetPassword($userId, $pass){
        $original = $pass;
        $pass = md5($this->md5_prefix.$pass);
        $user = $this->db->queryResultArray(
            "update web_users set user_pass = ? where id = ? returning id,opts",
            array($pass,  $userId));
        $passOk=0;
        try{
            if(isset($user[0]['ID']) && $user[0]['ID']){
                $opt = json_decode($user[0]['OPTS'],true);
                if(isset($opt['validating'])) unset($opt['validating']);
                if(isset($opt['resetting'])) unset($opt['resetting']);
                if(isset($opt['accountKey'])) unset($opt['accountKey']);
                if(isset($opt['resetKey'])) unset($opt['resetKey']);
                if(is_null($opt) || count($opt)==0) $opt=array();
                $this->updateOptions($user[0]['ID'], $opt);
                $passOk=1;
            }else{
                $passOk=0;                
            }
        }catch(Exception $e){
            $passOk=0; 
        }
        return $passOk;
    }
    
    function createNewByEmail($email){
        $user = $this->db->queryResultArray(
            "insert into web_users
            (IDENTIFIER, email,user_email, provider, full_name, display_name, profile_url, last_visit)
            values (?,'',?, 'mourjan', '', '', 'https://www.mourjan.com/', current_timestamp)
            returning id,opts",
            array($email,$email));
        return $user;
    }
    
    function createNewByPhone($number){
        $user = $this->db->queryResultArray(
            "insert into web_users
            (IDENTIFIER, email,user_email, provider, full_name, display_name, profile_url, last_visit)
            values (?,'','', 'mourjan', '', '', 'https://www.mourjan.com/', current_timestamp)
            returning id,opts",
            array($number));
        return $user;
    }
    
    function createNewAccount($account){
        if(preg_match('/@/ui', $account)){
            $user = $this->createNewByEmail($account);
        }else{
            $user = $this->createNewByPhone($account);
        }
        return $user;
    }
    
    function updatePassword($pass){
        $this->db->setWriteMode();
        $original = $pass;
        $userId = $this->pending['user_id'];
        $pass = md5($this->md5_prefix.$pass);
        $user = $this->db->queryResultArray("update web_users set user_pass = ? where id = ? returning id,opts", [$pass, $this->pending['user_id']], true);
        $passOk=0;
        try{
            if($user && count($user)){
                $opt = json_decode($user[0]['OPTS'],true);
                if(isset($opt['validating'])) unset($opt['validating']);
                if(isset($opt['resetting'])) unset($opt['resetting']);
                if(isset($opt['accountKey'])) unset($opt['accountKey']);
                if(isset($opt['resetKey'])) unset($opt['resetKey']);
                if(is_null($opt) || count($opt)==0) $opt=array();
                $this->updateOptions($user[0]['ID'], $opt);
                if(isset($this->pending['user_id']))unset($this->pending['user_id']);
                if($this->sysAuthById($user[0]['ID'])){
                    $passOk=1;
                }else{
                    $passOk=0;
                    error_log('password reset sysauth failure | >'.$userId .':'.$original.'< >'.$pass.'<'."\n", 3, "/var/log/nginx/mourjan.password.log");
                    error_log(var_export($this->db->getInstance()->errorInfo(),true)."\n",3, "/var/log/nginx/mourjan.password.log");
                }
            }else{
                error_log('password reset failed > on query | >'.$userId .':'.$original.'< >'.$pass.'<'."\n", 3, "/var/log/nginx/mourjan.password.log");
                error_log(var_export($this->db->getInstance()->errorInfo(),true)."\n",3, "/var/log/nginx/mourjan.password.log");
            }
        }catch(Exception $e){
            error_log('password reset failed > on exception | >'.$e->getMessage()."\n", 3, "/var/log/nginx/mourjan.password.log");
        }
        return $passOk;
    }
    
    function getWatchInfo($id, $force=false, $onlyEmail=false){
        if (!is_numeric($id)) return FALSE;
        if($onlyEmail){
            $info = $this->db->queryResultArray(
                    'select * from subscription where web_user_id=? and email=1',
                    array($id));
        }else {
            if(!isset($this->params['loadedWatch'])){
                $this->params['loadedWatch']=1;
                $this->update();
                $force=true;
            }
            if (!$force){
                $info=$this->db->getCache()->get('watch_'.$id);
                if ($info) return $info;
            }               
            $info = $this->db->queryResultArray(
                'select * from subscription where web_user_id=?',
                array($id));
            $this->db->getCache()->set('watch_'.$id, $info);
        }
        return $info;
    }
    
    function checkUriAvailability($uri){
        $uri='/'.$uri;
        $available=false;
        $stmt = $this->db->prepareQuery(
                "select * 
                from uri 
                where path = ?"
                );        
        $stmt->execute(array($uri));
        if ($stmt->fetch() === false) {
            $available=true;
        }
        return $available;
    }

    function getPartnerInfo($id, $force=false){
        if (!is_numeric($id)) return FALSE;
        
        if (!$force){
            $info=$this->db->getCache()->get('partner_'.$id);
            if ($info) return $info;
        }
        
        $info = false;        
        $stmt = $this->db->prepareQuery(
                "select * 
                from web_users 
                where id = ? and lvl in (1,2,3,9)"
                );        
        $stmt->execute(array($id));
        if (($user = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            $options=json_decode($user['OPTS'], true);
            $info=isset($options['page']) ? $options['page']:array();
            if(isset($options['stats']) && count($options['stats'])) {
                $info['stats']=$options['stats'];
            }
            if(!(isset($info['uri']) && $info['uri'])) {
                $info['uri']=$id+$this->site->urlRouter->basePartnerId;
            }
            
            $this->db->getCache()->set('partner_'.$id, $info);
        }
        return $info;
    }
    
    function loadAdToSession($id=0){
        if(isset($this->pending['post'])) unset($this->pending['post']);
        $ad=null;
        $this->pending['post']=array(
            'id'=>0,
            'user'=>$this->info['id'],
            'ro'=>0,
            'pu'=>0,
            'se'=>0,
            'rtl'=>0,
            'cn'=>0,
            'c'=>0,
            'lon'=>0,
            'lat'=>0,
            'state'=>0,
            'content'=>json_encode(array()),
            'title'=>''
        );
        if($id>0){
            $ad=$this->getPendingAds($id);
            if (!empty($ad)) {
                $ad=$ad[0];
                $content=json_decode($ad['CONTENT'],true);
                if (isset($content['attrs'])) unset($content['attrs']);
                if (!isset($content['pic_idx'])) {
                    $content['pic_idx']=0;
                    if (isset($content['pics']))
                        $content['pic_idx'] = count($content['pics']);
                    $ad['CONTENT'] = json_encode($content);
                }
                
                $this->pending['post']['id']=$ad['ID'];
                $this->pending['post']['user']=$ad['WEB_USER_ID'];
                $this->pending['post']['content']=$ad['CONTENT'];
                $this->pending['post']['title']=$ad['TITLE'];
                $this->pending['post']['rtl']=$ad['RTL'];
                $this->pending['post']['lon']=$ad['LONGITUDE'];
                $this->pending['post']['lat']=$ad['LATITUDE'];
                $this->pending['post']['cn']=$ad['COUNTRY_ID'];
                $this->pending['post']['c']=$ad['CITY_ID'];
                $this->pending['post']['se']=$ad['SECTION_ID'];
                $this->pending['post']['pu']=$ad['PURPOSE_ID'];
                $this->pending['post']['state']=$ad['STATE'];
                $this->pending['post']['ro']=$content['ro'];
            }
        }
        $this->update();
        return $ad;
    }

    function getPendingAds($id=0, $state=0,$pagination=0, $commit=false){
        $res=false;
        
        $pagination_str = '';
        if($pagination){
            $recNum = 50;
            $offset = $this->site->get('o','uint');
            if(is_numeric($offset) && $offset){
                $pagination_str = 'first '.($recNum+1).' skip '.($offset*$recNum);
            }else{
                $pagination_str = 'first '.($recNum+1);
            }
        }        
        
        if ($this->info['id']) {
            if ($id) {
                if ($this->info['level']==9){
                    $res=$this->db->queryResultArray(
                        'select a.*,u.full_name,u.lvl,u.provider,u.email,u.DISPLAY_NAME,'
                            . 'u.user_name,u.user_email,u.profile_url, u.user_rank, 
                            IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                            IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended '
                            . 'from ad_user a '
                            . 'left join web_users u on u.id=a.web_user_id 
                               left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 
                               left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date '
                            . 'where a.id=?',
                        array($id), $commit);
                }else {
                    $res=$this->db->queryResultArray(
                        'select a.*, 
                         IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                         IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended 
                         from ad_user a                             
                         left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 
                         left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date 
                         where a.web_user_id=? and a.id=?',
                        array($this->info['id'],$id), $commit);
                }
            }else {
                if ($this->info['level']==9){
                    
                    $aid=0;
                    if (isset ($_GET['a']) && is_numeric($_GET['a'])) $aid=(int)$_GET['a'];
                    if($aid){
                        if ($state>6){
                            $res=$this->db->queryResultArray(
                                'select '.$pagination_str.' a.*, u.full_name, u.lvl, 
                                    u.DISPLAY_NAME, u.profile_url, u.user_rank
                                from ad_user a
                                left join web_users u on u.id=a.web_user_id
                                where a.admin_id=? and a.state=? 
                                ORDER BY a.DATE_ADDED desc
                                ', array($aid,$state), $commit);
                        }elseif ($state){
                            $res=$this->db->queryResultArray(
                            'select '.$pagination_str.' a.*,u.full_name,u.lvl,u.DISPLAY_NAME,u.profile_url, u.user_rank from ad_user a left join web_users u on u.id=a.web_user_id where a.state=3 and a.admin_id='.$aid.' order by a.state asc,a.date_added desc');
                        }else {
                            $res=$this->db->queryResultArray(
                            'select '.$pagination_str.' a.*,u.full_name,u.lvl,u.DISPLAY_NAME,u.profile_url, u.user_rank from ad_user a left join web_users u on u.id=a.web_user_id where a.admin_id=? and a.state=? order by a.date_added desc',
                            array($aid,$state), $commit);
                        }
                        
                    }else{
                        
                        $uid=$this->info['id'];
                        if (isset ($_GET['u']) && is_numeric($_GET['u'])) $uid=(int)$_GET['u'];
                        if ($state>6){
                            $res=$this->db->queryResultArray(
                                'select '.$pagination_str.' a.*, u.full_name, u.lvl, 
                                    u.DISPLAY_NAME, u.profile_url, u.user_rank, 
                                    IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended 
                                from ad_user a
                                left join web_users u on u.id=a.web_user_id 
                                left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 
                                left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date 
                                where a.web_user_id=? and a.state=? 
                                ORDER BY bo_date_ended desc, a.DATE_ADDED desc
                                ', array($uid,$state), $commit);
                        }elseif ($state){
                            $adLevel=0;
                            if($this->isSuperUser()){
                                $adLevel=1;
                            }
                            $filters = $this->getAdminFilters();
                            $q='select '.$pagination_str.' a.*,ao.super_admin,u.full_name,u.lvl,'
                                . 'u.DISPLAY_NAME,u.profile_url, '
                                . 'iif((a.section_id = 190 or a.section_id = 1179 or a.section_id = 540),1,0) ppn, '
                                . 'iif(a.state = 4,1,0) primo, '
                                . 'u.user_rank,IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended  '
                                . 'from ad_user a '
                                . 'left join web_users u on u.id=a.web_user_id '
                                . 'left join ad_object ao on ao.id = a.id '
                                . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 '
                                . 'left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date ';
                                if($filters['root']){
                                    $q .= 'left join section s on a.section_id = s.id ';
                                }
                                $q .= 'where ';
                                    
                                    if($filters['uid']){
                                        $q.= '( (a.state in (1,2,4)) and a.web_user_id='.$filters['uid'].' ) ';
                                    }else{
                                        $q.= '( (a.state in (1,2,4)) or (a.state=3 and a.web_user_id='.$uid.') ) ';
                                    }
                                    $q .= ' and (ao.super_admin is null or ao.super_admin <= '.$adLevel.') ';
                                    if($filters['purpose']){
                                        $q.='and a.purpose_id='.$filters['purpose'].' ';
                                    }
                                    if($filters['lang']==1){                                        
                                        $q.='and (a.rtl in (1,2)) ';
                                    }
                                    if($filters['lang']==2){                                        
                                        $q.='and (a.rtl in (0,2)) ';
                                    }
                                    if($filters['root']){
                                        $q.='and s.root_id = '.$filters['root'].' ';
                                    }
                                    $q.= 'order by primo desc,a.state asc,bo_date_ended desc,ao.super_admin desc, ppn,a.date_added desc';                               
                            //echo $q;
                            $res=$this->db->queryResultArray($q,null, $commit);
                        }else {
                            $res=$this->db->queryResultArray(
                            'select '.$pagination_str.' a.*,u.full_name,u.lvl,u.DISPLAY_NAME,u.profile_url, u.user_rank from ad_user a left join web_users u on u.id=a.web_user_id where a.web_user_id=? and a.state=? order by a.date_added desc',
                            array($uid,$state), $commit);
                        }
                        
                    }
                }else {
                    if ($state>6) {
                        // 7: Active, 8: Deleted, 9:Archived
                        $res=$this->db->queryResultArray(
                        //'select '.$pagination_str.' * from ad_user where web_user_id=? and state=? order by date_added desc',
                        'select '.$pagination_str.' a.*,  
                         IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                         IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended 
                         from ad_user a
                         left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 
                         left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date 
                         where a.web_user_id=? and a.state=? 
                                ORDER BY bo_date_ended desc, a.DATE_ADDED desc
                                ',
                        array($this->info['id'],$state), $commit);
                    }elseif ($state) {
                        // 1: Pending to apprive, 2: approved not published, 3: rejected
                        $res=$this->db->queryResultArray(
                        //'select '.$pagination_str.' * from ad_user where web_user_id=? and state in (1,2,3) order by date_added desc',
                        'select '.$pagination_str.' a.*,
                        IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', featured.ended_date)) featured_date_ended, 
                        IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended  '
                        . 'from ad_user a '
                        . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 
                        left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date '
                        . 'where a.web_user_id=? and a.state in (1,2,3,4) order by a.date_added desc' ,       
                        array($this->info['id']), $commit);
                    }else {
                        // Draft
                        $res=$this->db->queryResultArray(
                        'select '.$pagination_str.' * from ad_user where web_user_id=? and state=? order by date_added desc',
                        array($this->info['id'],$state), $commit);
                    }
                }
            }
        }
        return $res;
    }
    
    function getPendingAdsCount($state=0){
        $res=false;
        if ($this->info['id']) {
            if ($this->info['level']==9){
                $aid=0;
                if (isset ($_GET['a']) && is_numeric($_GET['a'])) $aid=(int)$_GET['a'];
                if($aid){
                    if ($state>6){
                        $res=$this->db->queryResultArray(
                            'select count(*) 
                            from ad_user 
                            where admin_id=? and state=?', array($aid,$state));
                    }elseif ($state){
                        $res=$this->db->queryResultArray(
                        'select count(*)
                            from ad_user 
                            where state=3 and admin_id='.$aid);
                    }else {
                        $res=$this->db->queryResultArray(
                        'select count(*) from ad_user 
                            where admin_id=? and state=?',
                        array($aid,$state));
                    }

                }else{

                    $uid=$this->info['id'];
                    if (isset ($_GET['u']) && is_numeric($_GET['u'])) $uid=(int)$_GET['u'];
                    if ($state>6){
                        $res=$this->db->queryResultArray(
                            'select count(*) 
                            from ad_user 
                            where web_user_id=? and state=?
                            ', array($uid,$state));
                    }elseif ($state){
                        
                        $adLevel=0;
                        if($this->isSuperUser()){
                            $adLevel=1;
                        }
                        $filters=  $this->getAdminFilters();
                        $q = 'select count(*) from ad_user a ';
                        if($filters['root']){
                            $q .= 'left join section s on a.section_id = s.id ';
                        }
                        $q .= 'left join ad_object ao on ao.id = a.id ';
                        $q .= 'where ';
                        if($filters['uid']){
                            $q.= '( (a.state in (1,2,4)) and a.web_user_id='.$filters['uid'].' ) ';
                        }else{
                            $q.= '( (a.state in (1,2,4)) or (a.state=3 and a.web_user_id='.$uid.') ) ';
                        }
                        $q .= ' and (ao.super_admin is null or ao.super_admin <= '.$adLevel.') ';
                                    
                        if($filters['purpose']){
                            $q.='and a.purpose_id='.$filters['purpose'].' ';
                        }
                        if($filters['lang']==1){                                        
                            $q.='and (a.rtl in (1,2)) ';
                        }
                        if($filters['lang']==2){                                        
                            $q.='and (a.rtl in (0,2)) ';
                        }
                        if($filters['root']){
                            $q.='and s.root_id = '.$filters['root'].' ';
                        }
                        $res=$this->db->queryResultArray($q);
                    }else {
                        $res=$this->db->queryResultArray(
                        'select count(*) from ad_user 
                            where web_user_id=? and state=?',
                        array($uid,$state));
                    }

                }
            }else {
                if ($state>6) {
                    $res=$this->db->queryResultArray(
                    'select count(*) from ad_user where web_user_id=? and state=?',
                    array($this->info['id'],$state));
                }elseif ($state) {
                    $res=$this->db->queryResultArray(
                    'select count(*) from ad_user where web_user_id=? and state in (1,2,3,4)',
                    array($this->info['id']));
                }else {
                    $res=$this->db->queryResultArray(
                    'select count(*) from ad_user where web_user_id=? and state=?',
                    array($this->info['id'],$state));
                }
            }
        }
        if($res && isset($res[0]['COUNT']))
            return $res[0]['COUNT'];
        else return false;
    }

    function hideAd($id){
        $res=false;
        $res=$this->db->queryResultArray(
            'update ad_user set state=8 where id=? and web_user_id=? and state=9 returning id,content,state',
            array($id,$this->info['id']), true);
        if($res) {
            //$this->info['archive_ads']--;
            $this->update();
        }
        return $res;
    }

    function approveAd($id){
        $result=false;
        $res=$this->db->queryResultArray(
                    'update ad_user set state=2, admin_id=?, admin_stamp=current_timestamp where id=? returning state',
                    array($this->info['id'],$id),true);
        if (!empty($res)) {
            $result=true;
        }
        return $result;
    }
    
    function referrToSuperAdmin($id){
        $result=false;
        $res=$this->db->queryResultArray(
                    'update ad_object set super_admin=1 where id=?',
                    array($id),true);
        if ($res!==false) {
            $result=true;
        }
        return $result;
    }

    function holdAd($id){
        $result=false;
        if ($this->info['level']==9){
            $res=$this->db->queryResultArray(
            'update ad set hold=1 where id=? and hold=0 returning id',
            array($id),true);
        }else {
          $res=$this->db->queryResultArray(
            'update ad set hold=1 where id=? and hold=0 and exists(select 1 from ad_user where id=ad.id and web_user_id=?) returning id',
            array($id,$this->info['id']),true);
        }
        if (!empty($res)) {
            $result=true;
        }
        return $result;
    }

    function rejectAd($id,$msg='',$warn=0){
        $result=false;
        $ad=$this->getPendingAds($id);
        if (!empty($ad)){
            $ad=$ad[0];
            $adContent=json_decode($ad['CONTENT'],true);
            $lang='en';
            if ($ad['RTL']) $lang='ar';
            $loadedLang=false;
            if (preg_match('/(?:http|https):\/\/www\.mourjan\.com\//u', $msg)){
                $loadedLang=true;
                $this->site->load_lang(array('ad_notices'), $lang);
                $msg=preg_replace('/{link}/u', $msg, $this->site->lang['dup']);
            }
            if ($warn) {
                if (!$loadedLang) $this->site->load_lang(array('ad_notices'), $lang);
                $msg.=$this->site->lang['warn'];
            }
            $adContent['msg']=$msg;
            $adContent=json_encode($adContent);
            $res=$this->db->queryResultArray(
                'update ad_user set state=3,admin_id=?,admin_stamp=current_timestamp,content=? where id=? returning state',
                array($this->info['id'],$adContent,$id),true);
            if (!empty($res)) {
                $result=true;
                if ($warn) {
                    if(!$this->setLevel($warn, 4)) $result=false;
                }
            }
            $result=true;
        }
        return $result;
    }

    
    function renewAd($id,$state=1)
    {
        $result=false;
        $ad=$this->getPendingAds($id);
        
        if (!empty($ad)){
            $ad=$ad[0];
            include_once $this->cfg['dir'] . '/core/lib/MCSaveHandler.php';                
            $normalizer = new MCSaveHandler($this->cfg);

            if ($ad['ID'] < 3134500)
            {
                
                $sectionId= $ad['SECTION_ID'];
                $purposeId= $ad['PURPOSE_ID'];
                
                $sections = $this->db->getSections();
                
                $content=json_decode($ad['CONTENT'],true);
                
                if (isset($sections[$sectionId]) && $sections[$sectionId][5] && $sections[$sectionId][8]==$purposeId)
                {
                    $content['ro']=$sections[$sections[$sectionId][5]][4];
                    $purposeId=$content['pu']=$sections[$sectionId][9];
                    $sectionId=$content['se']=$sections[$sectionId][5];
                }
                
                
                
                if(isset($content['altother']) && $content['altother']){
                    if($this->site->isRTL($content['altother'])){
                        $content['altRtl']=1;
                    }else {
                        $content['altRtl']=0;
                    }

                    if($this->site->isRTL($content['other'])){
                        $content['rtl']=1;
                    }else{ $content['rtl']=0;}

                    if($content['rtl'] == $content['altRtl']){
                        $content['extra']['t']=2;
                        unset($content['altRtl']);
                        unset($content['altother']);
                    }

                    if(isset($content['altRtl']) && $content['altRtl']){
                        $tmp=$content['other'];
                        $content['other']=$content['altother'];
                        $content['altother']=$tmp;
                        $content['rtl']=1;
                        $content['altRtl']=0;
                    }
                }
                
                $normalized = $normalizer->getFromContentObject($content);
                if ($normalized)                    
                {
                    $content = $normalized;
                    if ($content['se']!=$sectionId)
                    {
                        $sectionId=$content['se'];
                    }
                    if ($content['pu']=$purposeId)
                    {
                        $purposeId=$content['pu'];
                    }
                }                
                
                $content = json_encode($content);
                
                if($this->info['level']==9)
                {
                    $res=$this->db->queryResultArray(
                        'update ad_user set content=?, section_id=?, purpose_id=?, state = '.$state.', date_added=current_timestamp where id=? returning id',
                        array($content, $sectionId, $purposeId, $id),true);
                }
                else
                {
                    $res=$this->db->queryResultArray(
                        'update ad_user set content=?, section_id=?, purpose_id=?, state='.$state.', date_added=current_timestamp where id=? and web_user_id=? returning id',
                        array($content, $sectionId, $purposeId, $id,$this->info['id']),true);
                }
                
            }else{
            
                $normalized = $normalizer->getFromContentObject(json_decode($ad['CONTENT'], true));
                if ($normalized)
                {
                    
                    if($this->info['level']==9)
                    {
                        $res=$this->db->queryResultArray('update ad_user set state='.$state.', content=?, date_added=current_timestamp where id=? returning id', [json_encode($normalized), $id],true);
                    }
                    else
                    {
                        $res=$this->db->queryResultArray('update ad_user set state='.$state.', content=?, date_added=current_timestamp where id=? and web_user_id=? returning id', [json_encode($normalized), $id, $this->info['id']], true);
                    }                    
                }
                else
                {
                    if($this->info['level']==9)
                    {
                        $res=$this->db->queryResultArray('update ad_user set state='.$state.', date_added=current_timestamp where id=? returning id', [$id],true);
                    }
                    else
                    {
                        $res=$this->db->queryResultArray('update ad_user set state='.$state.', date_added=current_timestamp where id=? and web_user_id=? returning id', [$id, $this->info['id']], true);
                    }
                }

            }
            
            if (!empty ($res)) 
            {
                $result = $res;
                if ($normalized)
                {
                    $st = $this->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                    $st->bindValue(1, $id, PDO::PARAM_INT);
                    $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($normalized['attrs'], JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                    $st->execute();
                }
                
            }
            
            /*$q='insert into ad_user
                (web_user_id,state,content,title,purpose_id,section_id,
                rtl,country_id,city_id,latitude,longitude,active_country_id,active_city_id)
                values (?,'.$state.',?,?,?,?,?,?,?,?,?,?,?) returning id';
            $stmt=$this->db->prepareQuery($q);
            $stmt->bindValue(1, $ad['WEB_USER_ID']);
            $stmt->bindValue(2, $ad['CONTENT']);
            $stmt->bindValue(3, $ad['TITLE']);
            $stmt->bindValue(4, $ad['PURPOSE_ID']);
            $stmt->bindValue(5, $ad['SECTION_ID']);
            $stmt->bindValue(6, $ad['RTL']);
            $stmt->bindValue(7, $ad['COUNTRY_ID']);
            $stmt->bindValue(8, $ad['CITY_ID']);
            $stmt->bindValue(9, $ad['LATITUDE']);
            $stmt->bindValue(10,$ad['LONGITUDE']);
            $stmt->bindValue(11, $ad['ACTIVE_COUNTRY_ID']);
            $stmt->bindValue(12, $ad['ACTIVE_CITY_ID']);
            $result=null;
            if ($stmt->execute()) $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty ($res)) {
                $this->info['pending_ads']++;
                $this->hideAd($id);
                $result=$res;
            }else{
                $result=false;
            }*/
        }
        return $result;
    }

    function deletePendingAd($id,$hide=false){
        $result=false;
        if($hide){
            $res=$this->hideAd($id);
        }else{
        if ($this->info['level']==9){
        $res=$this->db->queryResultArray(
                    'update ad_user set state = 6, admin_id = ?, admin_stamp = current_timestamp where id=? returning id,content,state',
                    array($this->info['id'],$id),true);
        }else {
            $res=$this->db->queryResultArray(
                    'update ad_user set state = 6 where id=? and web_user_id=? returning id,content,state',
                    array($id,$this->info['id']),true);
        }
        }
        if ($res!==false && !empty($res)) {
            $state=$res[0]['STATE'];
            $this->update();
            /*$content=$res[0]['CONTENT'];
            $content=json_decode($content,true);
            if (isset($content['pics']) && count($content['pics'])) {
                $path=$this->cfg['dir'].'/web/repos/';
                foreach ($content['pics'] as $pic=>$val){
                    @unlink($path.'l/'.$pic);
                    @unlink($path.'d/'.$pic);
                    @unlink($path.'s/'.$pic);
                    if ($this->cfg['server_id']>1) {
                        $ssh = ssh2_connect('h1.mourjan.com', 22);
                        if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                            $sftp = ssh2_sftp($ssh);
                            ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/l/'.$pic);
                            ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/s/'.$pic);
                            ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/d/'.$pic);
                        }
                    }
                }
            }
            if (isset($content['video']) && $content['video'][0]){
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass('Zend_Gdata_YouTube');
                Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->cfg['yt_user'],$this->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->cfg['yt_dev_key']);
                $yt->setMajorProtocolVersion(2);
                $pass=true;
                try {
                    $entry = $yt->getVideoEntry($content['video'][0],null,true);
                    $httpResponse = $yt->delete($entry);
                } catch (Zend_Gdata_App_HttpException $httpException) {
                    $pass=false;
                    if ($httpException->getCode()==0){
                        $pass=true;
                    }
                } catch (Zend_Gdata_App_Exception $e) {
                    $pass=false;
                }
            }*/
            $result=true;
        }
        return $result;
    }
    
    function getOptions($id){
        $q = 'select opts from web_users where id = ?';
        $result=false;
        try {
            $result = $this->db->queryResultArray($q, array($id));
            if ($result && count($result)){
                $result=$result[0]['OPTS'];
            }
        }catch(Exception $e){
            error_log($e->getMessage());
            $result=false;
        }
        return $result;
    }
    
    function getRank($uid){
        $result = false;
        $q='select user_rank from web_users where id = ?';
        $res=$this->db->queryResultArray($q,array($uid));
        if($res && isset($res[0]['USER_RANK']) && $res[0]['USER_RANK']!=null){
            $result=(int)$res[0]['USER_RANK'];
        }
        return $result;
    }
    
    function getStatement($user_id=0, $offset = 0, $balanceOnly=false, $startDate=null, $language = 'ar'){
        if(isset($this->info['level']) && $this->info['level']==9 && $user_id){
            $userId=$user_id;
        }else{
            $userId=$this->info['id'];
            if(isset($this->info['level']) && $this->info['level']==9){
                if (isset ($_GET['u']) && is_numeric($_GET['u'])) $userId=(int)$_GET['u'];
            }
        }
        $result = false;
        
        if($userId){
            $q='select sum(credit - debit) as balance from t_tran where uid = ?';
            $res=$this->db->queryResultArray($q,array($userId));
            if($res && isset($res[0]['BALANCE']) && $res[0]['BALANCE']!=null){
                $result['balance']=(int)$res[0]['BALANCE'];
                
                if(!$balanceOnly){
                    
                    
                    
                    
                    $rs = $this->db->queryResultArray(
                            "SELECT count(*) as total 
                            FROM T_TRAN r
                            where r.UID=?", [$userId]);
                            $total = 0;
                            if($rs && isset($rs[0]['TOTAL']) && $rs[0]['TOTAL'] > 0){
                                $total = $rs[0]['TOTAL'];
                                $result['total'] = $total;

                                $rs = $this->db->queryResultArray(
                                "SELECT skip {$offset} r.id, o.ad_id, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', r.dated) DATED, r.AMOUNT, r.DEBIT, r.CREDIT,
                                r.USD_VALUE, r.currency_id, p.NAME_{$language} as offer_name,
                                m.name_{$language} as product_name, 
                                s.name_{$language} as section_name,
                                pu.name_{$language} as purpose_name,
                                a.content, a.state, o.offer_id  
                                FROM T_TRAN r
                                left join T_AD_FEATURED f on f.ID=r.XREF_ID and r.CURRENCY_ID='MCU' and r.DEBIT>0 and r.PRODUCT_ID='' 
                                left join T_AD_BO o on o.ID=f.BO_ID
                                left join T_OFFER p on p.ID=o.OFFER_ID
                                left join product m on m.product_id = r.product_id
                                left join ad_user a on a.id = o.ad_id
                                left join section s on a.section_id = s.id 
                                left join purpose pu on pu.id = a.purpose_id
                                where r.UID=?
                                order by r.ID desc", [$userId]);
                                
                                $balance=0;
                                
                                $balance = $this->db->queryResultArray(
                                "SELECT sum(credit - debit) as balance "
                                . "from t_tran where uid = ? and id <= ? ", [$userId, $rs[0]['ID']]);
                                $count=count($rs);
                                $newRs = [];
                                if($balance && isset($balance[0]['BALANCE'])){
                                    $balance = $balance[0]['BALANCE'] == null ? 0 : $balance[0]['BALANCE']+0;
                                    for ($i=0; $i<$count; $i++) {

                                        $rs[$i]['CREDIT'] = $rs[$i]['CREDIT']+0;
                                        $rs[$i]['DEBIT'] = $rs[$i]['DEBIT']+0;
                                        $newRs[$i] = [];
                                        $newRs[$i][] = $rs[$i]['AD_ID'];
                                        $newRs[$i][] = $rs[$i]['DATED'];
                                        $newRs[$i][] = $rs[$i]['CREDIT']+0;
                                        $newRs[$i][] = $rs[$i]['DEBIT']+0;
                                        $newRs[$i][] = $balance;
                                        
                                        $balance = $balance+$rs[$i]['DEBIT']-$rs[$i]['CREDIT'];

                                        $label = '';
                                        if($rs[$i]['CREDIT'] > 0){
                                            if(!$rs[$i]['USD_VALUE'] || !$rs[$i]['PRODUCT_NAME']){
                                                $label = $rs[$i]['PRODUCT_NAME'];
                                                if($language == 'en'){
                                                    $label = $rs[$i]['CREDIT'].' free gold';
                                                }else{
                                                    if($rs[$i]['CREDIT'] == 1){
                                                        $label = 'ذهبية واحدة';
                                                    }else if($rs[$i]['CREDIT'] == 2){
                                                        $label = 'ذهبيتان';
                                                    }else if ($rs[$i]['CREDIT'] >2 && $rs[$i]['CREDIT'] < 11){
                                                        $label = $rs[$i]['CREDIT'].' ذهبيات';
                                                    }else{
                                                        $label = $rs[$i]['CREDIT'].' ذهبية';
                                                    }
                                                    $label .= ' مجانية';
                                                }
                                            }else{
                                                $label = $rs[$i]['PRODUCT_NAME'];
                                            }
                                        }else{
                                            if($rs[$i]['OFFER_ID'] == 1){
                                                if($language == 'ar'){
                                                    $label = 'تمييز لمدة يوم';
                                                }else{
                                                    $label = '1 day Premium';
                                                }
                                            }else{
                                                $label = $rs[$i]['OFFER_NAME'];
                                            }
                                        }

                                        $newRs[$i][] = $label;

                                        $label = '';
                                        if($rs[$i]['DEBIT'] > 0){
                                            if(stristr($rs[$i]['SECTION_NAME'], $rs[$i]['PURPOSE_NAME'])){
                                                $label = $rs[$i]['SECTION_NAME'];
                                            }else{
                                                $label = $rs[$i]['SECTION_NAME'].' | '.$rs[$i]['PURPOSE_NAME'];
                                            }

                                            $newRs[$i][] = $label;
                                            $rtl = 0;
                                            if($rs[$i]['CONTENT']){
                                                $content = json_decode($rs[$i]['CONTENT'], true);
                                                if(isset($content['other'])){
                                                    if($language == 'ar'){
                                                        $newRs[$i][] = $content['other'];
                                                        $rtl = $content['rtl'];
                                                    }else{
                                                        if(isset($content['altother']) && $content['altother']!=''){
                                                            $newRs[$i][] = $content['altother'];
                                                            $rtl = $content['altRtl'];
                                                        }else{
                                                            $newRs[$i][] = $content['other'];
                                                            $rtl = $content['rtl'];
                                                        }
                                                    }
                                                }else{
                                                    $newRs[$i][] = 'NA';
                                                }
                                            }else{
                                                $newRs[$i][] = '';
                                            }
                                            $newRs[$i][] = $rtl;

                                            $newRs[$i][] = $rs[$i]['STATE'];
                                            $newRs[$i][] = trim($rs[$i]['CURRENCY_ID']);
                                        }else{
                                            $newRs[$i][] = '';
                                            $newRs[$i][] = '';
                                            $newRs[$i][] = 0;
                                            $newRs[$i][] = 0;
                                            $newRs[$i][] = trim($rs[$i]['CURRENCY_ID']);
                                        }
                                    }
                                    $result['recs'] = $newRs;
                                }
                            }
                    
                    /*       
                            
                    $q='select sum(credit - debit) as balance from t_tran where uid = ? ';
                        $result['forward_balance']=0;

                    $q='select t.dated, t.currency_id,t.amount,t.credit,t.debit,a.id,a.state,a.content,b.offer_id from t_tran t
                    left join t_ad_featured f on f.id = t.xref_id
                    left join t_ad_bo b on b.id = f.bo_id
                    left join ad_user a on a.id = b.ad_id
                    where t.uid = ? ';
                    $result['recs']=$this->db->queryResultArray($q,array($userId));*/
                    
                }
                
            }else{
                $result['balance']=0;
            }
        }
        
        return $result;
    }

    function saveAd($publish=0,$user_id=0,$runtime=0){
        $id=0;
        if($user_id)
            $userId=$user_id;
        else
            $userId=isset($this->pending['post']['user']) && $this->pending['post']['user'] ? $this->pending['post']['user'] : 0;
        $this->pending['post']['title']='test';
        try{

            if ($userId) {
                //include_once $this->cfg['dir'] . '/core/lib/MCAdTextHandler.php';
                include_once $this->cfg['dir'] . '/core/lib/MCSaveHandler.php';
                //$textHandler = new AdTextFormatter();
                
                $normalizer = new MCSaveHandler($this->cfg);

                $content = json_decode($this->pending['post']['content'],true);

                $normalized = $normalizer->getFromContentObject($content);

                if ($normalized)                    
                {
                    $content = $normalized;
                    if ($content['se']!=$this->pending['post']['se'])
                    {
                        $this->pending['post']['se']=$content['se'];
                    }
                    if ($content['pu']=$this->pending['post']['pu'])
                    {
                        $this->pending['post']['pu']=$content['pu'];
                    }
                }
                
                /*
                if (isset($content['other'])) {
                    $textHandler->setText($content['other']);
                    $textHandler->format();                
                    $content['other'] = $textHandler->text;
                    //error_log($textHandler->text);
                }

                if (isset($content['altother'])) {
                    $textHandler->setText($content['altother']);
                    $textHandler->format();                
                    $content['altother'] = $textHandler->text;
                }
                
                */
                
                $this->pending['post']['content']=json_encode($content);

                $hasVideo=(isset($content['video']) && is_array($content['video']) && count($content['video'])) ?  1 : 0;
                $hasPics=(isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) ?  1 : 0;
                $media=0;
                if ($hasVideo && $hasPics){
                    $media=3;
                }elseif($hasVideo){
                    $media=2;
                }elseif($hasPics){
                    $media=1;
                }
                //error_log('ADSAVE>>>'.$this->info['id'].'<<<>>>'.PHP_EOL.$this->pending['post']['content'].PHP_EOL.'<<<'.PHP_EOL.PHP_EOL);
                if (isset ($this->pending['post']['id']) && $this->pending['post']['id']) {

                    $id=$this->pending['post']['id'];
                    
                    $attrs = isset($content['attrs']) ? $content['attrs'] : NULL;
                    
                    $q='update ad_user set
                        content=?,title=?,purpose_id=?,section_id=?,rtl=?,
                        country_id=?,city_id=?,latitude=?,longitude=?,state=?,media=? ';
                    if( $this->info['id']==$userId && ($publish==1 || $publish==4)){
                        $q.=',date_added=current_timestamp ';
                    }
                    $q.='where id=? ';
                    if ($this->info['level']!=9) $q.='and web_user_id+0=? ';
                    $q.='returning state, web_user_id';
                    
                    $tries = 0;
                    if ($this->pending['post']['se']>0) {
                    	//do {
                    		$tries++;
                    		
                    		$stmt=$this->db->prepareQuery($q);
	                    	$stmt->bindValue(1, $this->pending['post']['content'],PDO::PARAM_STR);
    	                	$stmt->bindValue(2, $this->pending['post']['title']);
        	            	$stmt->bindValue(3, $this->pending['post']['pu']);
            	        	$stmt->bindValue(4, $this->pending['post']['se']);
                	    	$stmt->bindValue(5, $this->pending['post']['rtl']);
                    		$stmt->bindValue(6, $this->pending['post']['cn']);
                    		$stmt->bindValue(7, $this->pending['post']['c']);
                    		$stmt->bindValue(8, $this->pending['post']['lat']);
                    		$stmt->bindValue(9, $this->pending['post']['lon']);
	                    	$stmt->bindValue(10, $publish, PDO::PARAM_INT);
    	                	$stmt->bindValue(11, $media, PDO::PARAM_INT);
        	            	$stmt->bindValue(12, $id, PDO::PARAM_INT);

            	        	if ($this->info['level']!=9)$stmt->bindValue(13,$userId, PDO::PARAM_INT);

                	    	$result=null;
                                
                    		try 
                                {
                                    if (!$this->db->inTransaction())
                                    {
                                        error_log('User.SaveAd ('. $id .'): Not in transaction (1)' );
                                    }
                                    if ($this->db->executeStatement($stmt)) {
                                        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                        if ($attrs)
                                        {
                                            if (!$this->db->inTransaction())
                                            {
                                                error_log('User.SaveAd ('. $id .'): Not in transaction (2)' );
                                            }
                                            
                                            $st=$this->db->prepareQuery("update or insert into ad_object (id, attributes) values (?, ?)");
                                            $st->bindValue(1, $id, PDO::PARAM_INT);
                                            $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                                            $st->execute();  
                                            $this->db->executeStatement($st);
                                        }
                                    }
                    		} catch (Exception $e) {
                                    error_log('User.SaveAd ('. $id .'): ' .  $e->getMessage());
                                    error_log('User.SaveAd ('. $id .'): ' .  $e->getTraceAsString());
                                    error_log('User.SaveAd ('. $id .') Transaction: ' . $this->db->getTransactionIsolationMessage());

                                    $this->db->getInstance()->rollBack();
                    		}	
							
                    	//} 
                    	//while (isset($result) || $tries>2);
                    }
                    
                    
                    if (!empty($result)){
                        $state=(int)$result[0]['STATE'];
                        if ($this->pending['post']['state']!=$state) {
                            $this->pending['post']['state']=$state;
                            $updateOptions=false;
                            if ($state==1 || $state==2){
                                $uId = (int)$result[0]['WEB_USER_ID'];
                                $content = json_decode($this->pending['post']['content'],true);
                                if(isset($content['version']) && $content['version']==2){
                                    if ($this->info['id']==$uId) {
                                        $options=$this->info['options'];
                                        if ( (!isset($options['cut']) || json_encode($options['cut']) !=  json_encode($content['cut'])) ||
                                              (!isset($options['cui']) || json_encode($options['cui']) !=  json_encode($content['cui']))  ){
                                            $options['cut']=$content['cut'];
                                            $options['cui']=$content['cui'];
                                            $options['cts']=time();
                                            if(isset($options['contact']))unset($options['contact']);
                                            $this->info['options']=$options;
                                            $updateOptions=true;
                                        }
                                    }else {                            
                                        $options = $this->getOptions($uId);
                                        if ($options!==false) {
                                            if ($options) $options = json_decode($options, true);
                                            else $options=array();
                                            if ( (!isset($options['cut']) || json_encode($options['cut']) !=  json_encode($content['cut'])) ||
                                              (!isset($options['cui']) || json_encode($options['cui']) !=  json_encode($content['cui']))  ){
                                                $options['cut']=$content['cut'];
                                                $options['cui']=$content['cui'];
                                                $options['cts']=time();
                                                if(isset($options['contact']))unset($options['contact']);
                                                $this->updateOptions($uId, $options);
                                                //$this->setReloadFlag($uId);
                                            }
                                        }
                                    }
                                }else {
                                    if ($this->info['id']==$uId) {
                                        $options=$this->info['options'];
                                        if (!isset($options['contact']))
                                            $options['contact']=array();
                                        $options['contact']['ct']=$content['fields']['ct'];
                                        $options['contact']['ct1']=$content['fields']['ct1'];
                                        $options['contact']['ct2']=$content['fields']['ct2'];
                                        $options['contact']['pc1']=$content['fields']['pc1'];
                                        $options['contact']['pc2']=$content['fields']['pc2'];
                                        $options['contact']['pc3']=$content['fields']['pc3'];
                                        $options['contact']['ph1']=$content['fields']['ph1'];
                                        $options['contact']['ph2']=$content['fields']['ph2'];
                                        $options['contact']['fax']=$content['fields']['fax'];
                                        $options['contact']['email']=$content['fields']['email'];
                                        $this->info['options']=$options;
                                        $updateOptions=true;
                                    }else {                            
                                        $options = $this->getOptions($uId);
                                        if ($options!==false) {
                                            if ($options) $options = json_decode ($options, true);
                                            else $options=array();
                                            if (!isset($options['contact']))
                                                $options['contact']=array();
                                            $options['contact']['ct']=$content['fields']['ct'];
                                            $options['contact']['ct1']=$content['fields']['ct1'];
                                            $options['contact']['ct2']=$content['fields']['ct2'];
                                            $options['contact']['pc1']=$content['fields']['pc1'];
                                            $options['contact']['pc2']=$content['fields']['pc2'];
                                            $options['contact']['pc3']=$content['fields']['pc3'];
                                            $options['contact']['ph1']=$content['fields']['ph1'];
                                            $options['contact']['ph2']=$content['fields']['ph2'];
                                            $options['contact']['fax']=$content['fields']['fax'];
                                            $options['contact']['email']=$content['fields']['email'];
                                            $this->updateOptions($uId, $options);
                                        }
                                    }
                                }
                            }
                            $this->update();
                            if ($updateOptions) $this->updateOptions();
                        }
                    }
                } else {
                    if ($this->pending['post']['se']>0) {
                    	$q='insert into ad_user
                        	(web_user_id,content,title,purpose_id,section_id,rtl,country_id,city_id,latitude,longitude,media)
                        	values (?,?,?,?,?,?,?,?,?,?,?) returning id';
                    	$stmt=$this->db->prepareQuery($q);
                    	$stmt->bindValue(1, $userId);
                    	$stmt->bindValue(2, $this->pending['post']['content'], PDO::PARAM_STR);
                    	$stmt->bindValue(3, $this->pending['post']['title'], PDO::PARAM_STR);
                    	$stmt->bindValue(4, $this->pending['post']['pu']);
                    	$stmt->bindValue(5, $this->pending['post']['se']);
                    	$stmt->bindValue(6, $this->pending['post']['rtl']);
                    	$stmt->bindValue(7, $this->pending['post']['cn']);
                    	$stmt->bindValue(8, $this->pending['post']['c']);
                    	$stmt->bindValue(9, $this->pending['post']['lat']);
                    	$stmt->bindValue(10, $this->pending['post']['lon']);
                    	$stmt->bindValue(11, $media, PDO::PARAM_INT);
                    	$result=null;
                    	if ($stmt->execute()) $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                    	if (!empty ($result)) {
                        	$this->pending['post']['id']=$id=$result[0]['ID'];
                        	$this->update();
                    	}
                    }
                }
                
                

            }
            $this->db->commit();

        }catch(Exception $e){
            $this->db->getInstance()->rollBack(); 
            $id=0;
        }
        return $id;
    }
    
    function suspend($uid, $hours){
        $options = $this->getOptions($uid);
        if($options) {
            $options =  json_decode($options,true);
            $options['suspend']=time()+($hours*3600);
            $this->updateOptions($uid,$options);
        }
    }
    
    function detectDuplicateSuspension($contactInfo=array()){
        $status = 0;
        if(count($contactInfo) && $this->info['id']){
            $q='
            select distinct u.id,u.lvl,u.opts from ad_attribute t
            left join ad_user a on a.id = t.ad_id
            left join web_users u on u.id = a.web_user_id
            where
            a.id is not null and
            a.id <> '.$this->info['id'].' and (   
            ';
            $params=array();
            $pass = 0;
            if(isset($contactInfo['p']) && count($contactInfo['p'])){
                foreach($contactInfo['p'] as $number){
                    if(isset($number['v']) && trim($number['v'])!=''){
                        if($pass) $q.= ' or ';
                        $q .= '
                            (t.attr_id = 1 
                            and 
                            t.attr_value = ?)
                        ';
                        $params[]=$number['v'];
                        $pass++;
                    }
                }
            }
            if(isset($contactInfo['e']) && count($contactInfo['e'])){
                if($pass) $q.= ' or ';
                $q .= '
                    (t.attr_id = 2 
                    and 
                    t.attr_value = ?)
                ';
                $params[]=$contactInfo['e'];
                $pass++;
            }
            $q.=')';
            
            if(count($params)){
                $users = $this->db->queryResultArray($q, $params);
                $time = $current_time = time();
                $blockAccount = false;
                if($users && count($users)){
                    foreach($users as $user){
                        if($user['LVL']==5){
                            $blockAccount=$user['ID'];
                            break;
                        }elseif($user['OPTS']){
                            $options = json_decode($user['OPTS'],true);
                            if(isset($options['suspend']) && $options['suspend'] > $time){
                                $time = $options['suspend'];
                            }
                        }
                    }
                }
                if($blockAccount){
                    $this->info['level']=5;
                    $status = 5;
                    $this->update();
                    if(!is_array($this->info['options']))
                        $this->info['options']=array();
                    $this->setLevel($this->info['id'],5);
                    $this->info['options']['autoblock']="reference {$blockAccount}";
                    $this->update();
                    $this->updateOptions();
                }elseif($time != $current_time){
                    if(!is_array($this->info['options']))
                        $this->info['options']=array();
                    $this->info['options']['suspend']=$time;
                    $status = 1;
                    error_log('DESKTOP SUSPENDED '.$this->info['id']);
                    $this->update();
                    $this->updateOptions();
                }
            }
        }
        return $status;
    }
    
    function detectIfAdInPending($adId,$sectionId, $contactInfo=array()){
        $active_ads = 0;
        if(count($contactInfo) && $this->info['id']){
            $q='select a.id from ad_user a where (a.id <> '.$adId.' and a.section_id ='.$sectionId.' and a.state in (1,2)) and ( ';
            $params=array();
            $pass = 0;
            if(isset($contactInfo['p']) && count($contactInfo['p'])){
                $q .= "a.content similar to '";
                foreach($contactInfo['p'] as $number){
                    if(isset($number['v']) && trim($number['v'])!=''){
                        //if($pass) $q.= ' or ';
                        if($pass) $q.= '|';
                        //$q .= 'a.content containing ?';                        
                        $q .= '%'.preg_replace('/\+/', '' ,$number['v']).'%';
                        //$params[]=$number['v'];
                        $pass++;
                    }
                }
                $q .= "'";
            }
            $q.=')';
            
            if($pass){
                $active_ads = $this->db->queryResultArray($q, $params);
                if($active_ads && isset($active_ads[0]['ID']) && $active_ads[0]['ID']){
                    $active_ads = count($active_ads);
                }
            }
        }
        return $active_ads;
    }

    function authenticate(){
        if( isset( $_GET["connected_with"]) || isset( $_GET["logout"] )) {
            $config   = $this->cfg['dir'] . '/web/lib/hybridauth/config.php';
            //require_once( $this->cfg['dir'] . '/web/lib/hybridauth/Hybrid/Auth.php' );
            $loaded=true;
            try{
                $hybridauth = new Hybrid_Auth( $config );
            }
            catch( Exception $e ){
                $loaded=false;
            }
            
            if ($loaded){
                if( isset( $_GET["logout"] ) ){
                    if($_GET["logout"]!='mourjan' && $_GET["logout"]!="mourjan-iphone"){
                        $provider = $_GET["logout"];
                        $adapter = $hybridauth->getAdapter( $provider );
                        $adapter->logout();
                    }
                    $this->logout();
                    //unset($_COOKIE['mourjan_sess']);
                    $this->redirectTo($this->site->urlRouter->getURL($this->params['country'],$this->params['city']));
                }elseif (isset( $_GET["connected_with"] ) && $_GET["connected_with"]!='mourjan' && $hybridauth->isConnectedWith( $_GET["connected_with"] ) ){
                    $provider = $_GET["connected_with"];
                    $adapter = $hybridauth->getAdapter( $provider );
                    $auth_info = $adapter->getUserProfile();
                    
                    
                    $this->updateUserRecord($auth_info,$provider);
                }
            }
        }elseif(isset($_GET['identifier'])) {
            $id=$this->site->get('identifier', 'uint');
            $key=$this->site->get('cks');
            if ($id && $key){
                $id=$id-$this->site->urlRouter->baseUserId;
                if ($id>0){
                    $this->authenticateById($id, $key);
                }
            }
        }
    }

    function redirectTo($url){
        $this->site->urlRouter->close();
        header('Location: '.$url);
        exit(0);
    }
    
    function setLevel($id,$level){
        $succeed=false;
        if($id && is_numeric($level)) {
        $q="update web_users set lvl=? where id=?";
        if ($this->db->queryResultArray($q, array($level,$id),true)) {
            $succeed=true;
        }}
        return $succeed;
    }
    
    function setType($id,$type){
        $succeed=false;
        if($id && is_numeric($type)) {
            $q="update web_users set user_publisher=? where id=?";
            if ($this->db->queryResultArray($q, array($type,$id),true)) {
                $succeed=true;
            }
        }
        return $succeed;
    }
    
    function getType($id){
        $q='select id, user_publisher  
            from web_users where id = ?';
        $result=$this->db->queryResultArray($q, array($id));
        if ($result && isset($result[0]) && $result[0]['ID']) {
            return $result[0]['USER_PUBLISHER'];
        }
        return false;
    }
    
    
    function setUserParams($result){        
            $this->info['id']=$result[0]['ID'];
            $this->info['idKey']=$this->encodeId($result[0]['ID']);
            $this->info['name']=$result[0]['USER_NAME'] ? $result[0]['USER_NAME'] :$result[0]['DISPLAY_NAME'];
            $this->info['provider']=$result[0]['PROVIDER'];
            $this->info['level']=$result[0]['LVL'];
            $this->info['rank']=$result[0]['USER_RANK'];
            if($this->info['level']==6){
                $this->info['email']='';
            }else{
                $this->info['email']=$result[0]['USER_EMAIL'] ? $result[0]['USER_EMAIL'] : $result[0]['EMAIL'];
            }
            if(strpos($this->info['email'], '@')===false) $this->info['email']='';
           	/*
            $this->info['active_ads']=0;
            $this->info['pending_ads']=0;
            $this->info['draft_ads']=0;
            $this->info['archive_ads']=0;
            */
            if ($result[0]['PREV_VISIT']) $this->params['last_visit']=  strtotime($result[0]['PREV_VISIT']);
            if ($result[0]['OPTS']=='') {
                $this->info['options']=array();
            }else {
                $this->info['options']=json_decode($result[0]['OPTS'],true);
            }            
    }
    
    function reloadData($id){
        $q='select identifier,id,lvl,display_name,provider, email, user_rank,user_name,user_email,opts,prev_visit,last_visit 
            from web_users where id = ?';
        $result=$this->db->queryResultArray($q, array($id));
        if ($result && isset($result[0]) && $result[0]['ID']) {
            $this->setUserParams($result);
            $this->update();
        }
    }
    
    function authenticateById($id, $key){
        $q='select identifier,id,lvl,display_name,provider, email, user_rank,user_name,user_email,opts,prev_visit,last_visit 
            from web_users where id = ?';
        $result=$this->db->queryResultArray($q, array($id));
        if ($result && isset($result[0]) && $result[0]['ID'] && md5($result[0]['IDENTIFIER'])==$key) {
            $this->setUserParams($result);
            $this->update();
        }
    }
    
    function sysAuthById($id){
        if($this->session_id==''){
            $this->session_id = session_id().$this->cfg['site_key'];
        }
        $q='select identifier,id,lvl,display_name,provider,email,user_rank,user_name,user_email,opts,prev_visit,last_visit 
            from web_users where id = ?';
        $result=$this->db->queryResultArray($q, [$id]);
        if ($result && isset($result[0]) && $result[0]['ID']) {
            $this->setUserParams($result);
            $this->update();
            return 1;
        }else return 0;
    }
    
    function authenticateUserAccount($account, $pass){
        $pass = md5($this->md5_prefix.$pass);
        
        $q='select id from web_users where identifier=? and  user_pass=? and provider=\'mourjan\'';
        $q2='select id from web_users where identifier=? and provider=\'mourjan\'';
        if(!preg_match('/@/',$account)){
            $account = (int)$account;
            $q='select id from web_users where identifier containing ? and  user_pass=? and provider=\'mourjan\'';
            $q2='select id from web_users where identifier containing ? and provider=\'mourjan\'';
        }
        $result=$this->db->queryResultArray($q, array($account,$pass));
        if($result!==false){
            if (isset($result[0]) && $result[0]['ID']) {
                return $result[0]['ID'];//success return user id
            }else {            
                $q=$q2;
                $result=$this->db->queryResultArray($q, array($account));
                if($result!==false){
                    if (isset($result[0]['ID']) && $result[0]['ID']) {
                        return 0;//wrong password
                    }else{
                        return -1;//account not found
                    }                    
                }else{
                    return -2;//server error
                }
            }
        }else{
            return -2;//server error
        }
    }
    
    function authenticateByEmail($email, $pass){
        $pass = md5($this->md5_prefix.$pass);
//        $q='select identifier,id,lvl,display_name,provider, email, active_ads,pending_ads,archive_ads,draft_ads,user_name,user_email,opts,prev_visit,last_visit 
//            from web_users where identifier = ? and  user_pass = ? and provider = \'mourjan\'';
        
        $q='update web_users set last_visit=current_timestamp  
            where identifier=? and  user_pass=? and provider = \'mourjan\' 
             returning id,provider,lvl,display_name, email, user_rank,user_name,user_email,opts,prev_visit,last_visit
        ';
        
        if(!preg_match('/@/',$email)){
            $email = (int)$email;
            $q='update web_users set last_visit = current_timestamp  
                where identifier containing ? and  user_pass = ? and provider = \'mourjan\' 
                 returning id,provider,lvl,display_name, email, user_rank,user_name,user_email,opts,prev_visit,last_visit
            ';
        }
        
        $result=$this->db->queryResultArray($q, array($email,$pass),true);
        if ($result && isset($result[0]) && $result[0]['ID']) {
            $this->setUserParams($result);            
            
            if (isset($this->pending['fav'])) {
                $this->updateFavorite($this->pending['fav'],0);
                unset($this->pending['fav']);
            }
            $checkWatchMail=false;
            $updateOptions=false;
            if (isset($this->pending['watch'])) {
                $this->insertWatch($this->pending['watch']);
                unset($this->pending['watch']);
                $updateOptions=true;
                $checkWatchMail=true;
            }      
            
            $this->update();
            if ($updateOptions){                
                $this->updateOptions();
                if ($checkWatchMail) {
                    $watchArray=isset($this->info['options']['watch']) ? $this->info['options']['watch'] : array();
                    $mailFrequency=(isset($this->info['options']['mailEvery']) && $this->info['options']['mailEvery']) ? $this->info['options']['mailEvery'] : 1;
                    $this->checkWatchMailSetting($this->info['id'], $mailFrequency);
                }
            }
            
            return 1;
        }else return 0;
    }
    
    function copyUserData($id, $pass, $rank, $level, $pubType, $opts){
        $q = 'update web_users set user_pass = ?, user_rank=?, lvl=?, user_publisher=?, opts=? where id = ?';
        $result=$this->db->queryResultArray($q, array($pass,$rank, $level, $pubType, $opts,$id));
        return $result;
    }
    
    function mergeDeviceToAccount($uuid, $uid, $newUid){
        return $this->connectDeviceToAccount(null, null, $uid, $uuid, $newUid);
    }
    
    function connectDeviceToAccount($info,$provider, $uid, $uuid, $newUid=0){
        $newUserId = 0;
        if($newUid==0){
            $provider=strtolower($provider);
            $identifier=$info->identifier;
            $email=is_null($info->emailVerified) ? (is_null($info->email ? '' : $info->email)) :$info->emailVerified;
            if(strpos($email, '@')===false) $email='';
            $fullName=trim(($info->firstName ? $info->firstName : '').' '.($info->lastName ? $info->lastName : ''));
            $dispName=(!is_null($info->displayName) ? $info->displayName : '');
            $infoStr=(!is_null($info->profileURL) ? $info->profileURL : '');
        }
        
        try{
            if($newUid==0){
                $getUserRecord=$this->db->prepareQuery('update or insert into web_users
                    (IDENTIFIER, email, provider, full_name, display_name, profile_url, last_visit)
                    values (?, ?, ?, ?, ?, ?, current_timestamp)
                    matching (identifier, provider) returning id');
            }else{
                //$updateTransRecords = $this->db->prepareQuery('update T_TRAN t set t.UID=? where t.UID=?');
            }
	/*
            $updateDeviceRecord=$this->db->prepareQuery('update web_users_device set uid = ? where (uid = ? or uid = ?) and uuid = ? returning uid');
            $deleteFavorites=$this->db->prepareQuery('delete from web_users_favs where web_user_id = ? and ad_id = ?');
            $deleteNotes=$this->db->prepareQuery('delete from web_users_notes where web_user_id = ? and ad_id = ?');
            $updateFavorites=$this->db->prepareQuery('update web_users_favs set web_user_id = ? where web_user_id = ?');
            $updateNotes=$this->db->prepareQuery('update web_users_notes set web_user_id = ? where web_user_id = ?');
            $selectAllUserFavorite = $this->db->prepareQuery('select ad_id from web_users_favs where web_user_id = ?');
            $selectAllUserNotes = $this->db->prepareQuery('select ad_id from web_users_notes where web_user_id = ?');
            $selectUserFavorite = $this->db->prepareQuery('select ad_id from web_users_favs where web_user_id = ? and deleted = 0');
            $getFavoritesUserList=$this->db->prepareQuery('select list(web_user_id) from web_users_favs where deleted=0 and ad_id=?');
            $updateSubscriptions=$this->db->prepareQuery('update subscription set web_user_id = ? where web_user_id = ?');
            $selectPrevSubscriptions=$this->db->prepareQuery('select * from subscription where web_user_id = ?');
            $deletePrevSubscriptions=$this->db->prepareQuery('delete from subscription where web_user_id = ? and country_id = ? and city_id = ? and section_id = ? and purpose_id = ? and section_tag_id = ? and locality_id = ? and query_term = ?');
            $getMyAds=$this->db->prepareQuery('select id, content from ad_user where web_user_id = ?');
            $updateMyAd=$this->db->prepareQuery('update ad_user set web_user_id = ?, content = ? where id = ?');
            $updateOffers=$this->db->prepareQuery('update t_promotion_users set uid = ? where uid = ? and claimed = 0 and expiry_date > current_timestamp');
         */
   
            $result = false;
            
            if($newUid==0){
                if($getUserRecord->execute([$identifier, $email, $provider, $fullName, $dispName, $infoStr])) {
                	$result = $getUserRecord->fetch(PDO::FETCH_NUM);
                }
                if($result !== false && !empty($result)){
                    $newUserId = $result[0];
                }else{
                    error_log("User Device Update/Insert Failure");
                }				
            }else{
                $result = $this->db->queryResultArray('select id from web_users where id = ?', array($newUid), false, PDO::FETCH_NUM);
                if($result !== false && isset($result[0][0]) && $result[0][0]>0){
                    $newUserId = $result[0][0];
                }else{
                    error_log("User Record Not Found");
                }
            }
            
            if (isset($getUserRecord)) unset($getUserRecord);

            if($newUserId){
            	$updateDeviceRecord=$this->db->prepareQuery('update web_users_device set uid = ?, cuid=0 where (uid = ? or uid = ?) and uuid = ? returning uid');
                if($updateDeviceRecord instanceOf  PDOStatement && 
                   $updateDeviceRecord->execute([$newUserId, $uid, $newUserId, $uuid]))
                {
                    $result = $updateDeviceRecord->fetch(PDO::FETCH_NUM);
                    unset($updateDeviceRecord);
                    
                    if($result !== false && !empty($result)){
                        $newUserId = $result[0];
                        if($newUserId){
                            include_once $this->cfg['dir'] . '/core/lib/SphinxQL.php';
                            $sphinx = new SphinxQL($this->cfg['sphinxql'], $this->cfg['search_index']);
                            
                            //Clean up previous favorites
                            $selectAllUserFavorite = $this->db->prepareQuery('select ad_id from web_users_favs where web_user_id=?');
                            $deleteFavorites = $this->db->prepareQuery('delete from web_users_favs where web_user_id=? and ad_id=?');
                            $favs = $selectAllUserFavorite->execute([$uid]);
                            if($favs !== false){
                                while(($row = $selectAllUserFavorite->fetch(PDO::FETCH_NUM)) !== false){
                                    $ad_id = $row[0];
                                    $deleteFavorites->execute([$newUserId, $ad_id]);
                                }
                            }
                            unset($deleteFavorites);
                            unset($selectAllUserFavorite);
                            
                            //MERGE DEVICE FAVORITES
                            $updateFavorites = $this->db->prepareQuery('update web_users_favs set web_user_id=? where web_user_id=?');                 
                            if($updateFavorites->execute([$newUserId, $uid])){
                            	$selectUserFavorite = $this->db->prepareQuery('select ad_id from web_users_favs where web_user_id=? and deleted=0');
                                if($selectUserFavorite->execute([$newUserId])){                                	
                                    while( ($row = $selectUserFavorite->fetch(PDO::FETCH_NUM)) !== false){
                                        $ad_id = $row[0];
                                        
                                        $getFavoritesUserList = $this->db->prepareQuery('select cast(list(web_user_id) as varchar(2048)) from web_users_favs where deleted=0 and ad_id=?');
                                        if ($getFavoritesUserList->execute([$ad_id])) {                            
                                            if( ($user_list = $getFavoritesUserList->fetch(PDO::FETCH_NUM)) !== false){
                                                if(isset($user_list[0]) && $user_list[0]){
                                                    $ql = "update {$this->cfg['search_index']} set starred=({$user_list[0]}) where id={$ad_id}";
                                                }else{
                                                    $ql = "update {$this->cfg['search_index']} set starred=() where id={$ad_id}"; 
                                                }
                                                $sphinx->directUpdateQuery($ql);
                                            }   
                                        }
                                        unset($getFavoritesUserList);
                                    }
                                    unset($selectUserFavorite);
                                    $sphinx->close();
                                }
                            }
                            unset($updateFavorites);
                            
                            
                            //MERGE PROMOTIONS
                            $updateOffers=$this->db->prepareQuery('update t_promotion_users set uid=? where uid=? and claimed=0 and expiry_date>current_timestamp');
                            $updateOffers->execute([$newUserId, $uid]);
                            unset($updateOffers);
                            
                            //Clean up previous subscription duplicates
                            $selectPrevSubscriptions=$this->db->prepareQuery('select * from subscription where web_user_id=?');
                            $subs = $selectPrevSubscriptions->execute([$uid]);
                            if($subs !== false){
                             	$deletePrevSubscriptions=$this->db->prepareQuery('delete from subscription where web_user_id=? and country_id=? and city_id=? and section_id=? and purpose_id=? and section_tag_id=? and locality_id=? and query_term=?');
                                while(($row = $selectPrevSubscriptions->fetch(PDO::FETCH_ASSOC)) !== false){
                                    $deletePrevSubscriptions->execute([$newUserId, $row['COUNTRY_ID'], $row['CITY_ID'], $row['SECTION_ID'], $row['PURPOSE_ID'], $row['SECTION_TAG_ID'], $row['LOCALITY_ID'], $row['QUERY_TERM'] ]);
                                }
                                unset($deletePrevSubscriptions);
                            }
                            unset($selectPrevSubscriptions);
                            
                            //MERGE SUBSCRIPTION LIST
                            $updateSubscriptions = $this->db->prepareQuery('update subscription set web_user_id=? where web_user_id=?');
                            $updateSubscriptions->execute([$newUserId, $uid]);
                            unset($updateSubscriptions);
                            
                            
                            
                            //MERGE MYADS
                            $getMyAds=$this->db->prepareQuery('select id, content from ad_user where web_user_id=?');
                            if($getMyAds->execute([$uid])){
                            	$updateMyAd=$this->db->prepareQuery('update ad_user set web_user_id=?, content=? where id=?');
                                while(($row = $getMyAds->fetch(PDO::FETCH_NUM)) !== false){
                                    $id = $row[0];
                                    $content = json_decode($row[1], true);
                                    $content['user']=$newUserId;
                                    $content = json_encode($content);
                                    $updateMyAd->execute([$newUserId, $content, $id]);
                                }
                                unset($updateMyAd);
                            }else{
                                error_log("get ads on connect failure");
                            }
                            unset($getMyAds);
                            
                            //Clean up previous notes
                            $selectAllUserNotes = $this->db->prepareQuery('select ad_id from web_users_notes where web_user_id=?');
                            $notes = $selectAllUserNotes->execute([$uid]);
                            if($notes !== false){
                            	$deleteNotes=$this->db->prepareQuery('delete from web_users_notes where web_user_id=? and ad_id=?');
                                while(($row = $selectAllUserNotes->fetch(PDO::FETCH_NUM)) !== false){
                                    $ad_id = $row[0];
                                    $deleteNotes->execute([$newUserId, $ad_id]);
                                }
                                unset($deleteNotes);
                            }
                            unset($selectAllUserNotes);
                            
                            //MERGE DEVICE FAVORITES
                            $updateNotes=$this->db->prepareQuery('update web_users_notes set web_user_id=? where web_user_id=?');
                            $updateNotes->execute([$newUserId, $uid]);
                            unset($updateNotes);
                            
                            
                            if($newUid>0){
                                //MERGE DEVICE FAVORITES
                                $updateTransRecords = $this->db->prepareQuery('update T_TRAN t set t.UID=? where t.UID=?');
                                $updateTransRecords->execute([$newUserId, $uid]);
                                unset($updateTransRecords);
                            }
                            
                        }else{
                            error_log("User ID is null on after connect");
                        }
                    }else{
                        error_log("Device Update Record empty");
                    }
                }else{
                    error_log("Device Update Record Failure");
                }
            }
        }catch(Exception $e){
            error_log( $e->getMessage() );
            $this->db->getInstance()->rollBack();
            $newUserId=0;
        }finally{
            if($newUid==0){
                //$getUserRecord->closeCursor();
            }else{
               // $updateTransRecords->closeCursor();
            }
            //$selectAllUserFavorite->closeCursor();
            //$selectAllUserNotes->closeCursor();
            //$deleteFavorites->closeCursor();
            //$deleteNotes->closeCursor();
            //$updateDeviceRecord->closeCursor();
            //$getFavoritesUserList->closeCursor();
            //$updateFavorites->closeCursor();
            //$updateNotes->closeCursor();
            //$selectPrevSubscriptions->closeCursor();
            //$deletePrevSubscriptions->closeCursor();
            //$updateSubscriptions->closeCursor();
            //$updateMyAd->closeCursor();
            //$getMyAds->closeCursor();
            //$selectUserFavorite->closeCursor();
            //$updateOffers->closeCursor();
        }
        return $newUserId;
    }


    function updateUserRecord($info,$provider){
        $updateOptions=false;
        $provider=strtolower($provider);
        $identifier=$info->identifier;
        $email=is_null($info->emailVerified) ? (is_null($info->email ? '' : $info->email)) :$info->emailVerified;
        if(strpos($email, '@')===false) $email='';
        $fullName=trim(($info->firstName ? $info->firstName : '').' '.($info->lastName ? $info->lastName : ''));
        $dispName=(!is_null($info->displayName) ? $info->displayName : '');
        $infoStr=(!is_null($info->profileURL) ? $info->profileURL : '');
        
        $userRec=$this->db->queryResultArray("select id from web_users where identifier = ? and provider = ?",array($identifier,$provider));
        if($userRec!==false){
            if(empty($userRec)){
                $this->pending['social_new']=1;                
            }
        }
        $q='update or insert into web_users
            (IDENTIFIER, email, provider, full_name, display_name, profile_url, last_visit)
            values (?, ?, ?, ?, ?, ?, current_timestamp)
            matching (identifier, provider) returning id,provider,lvl,display_name, email, user_rank,user_name,user_email,opts,prev_visit,last_visit';
            
        $result=$this->db->queryResultArray($q, array(
            $identifier,
            $email,
            $provider,
            $fullName,
            $dispName,
            $infoStr
        ),true);
        
        if ($result && count($result)) {
            /*
            if ($provider) {
                $info=array();
                $info['dn']=$dispName ? $dispName : ($fullName? $fullName : '' );
                $info['sp']=$provider;
                setcookie('mourjan_login', '', 1,'/','.mourjan.com');
                setcookie('mourjan_login', '', 1,'/',$this->cfg['site_domain']);
                setcookie('mourjan_log', json_encode($info), time()+2592000,'/',$this->cfg['site_domain']);
            }
            */
            
            $this->setUserParams($result);

            if (isset($this->pending['fav'])) {
                $this->updateFavorite($this->pending['fav'],0);
                unset($this->pending['fav']);
            }
            $checkWatchMail=false;
            if (isset($this->pending['watch'])) {
                $this->insertWatch($this->pending['watch']);
                unset($this->pending['watch']);
                $updateOptions=true;
                $checkWatchMail=true;
            }
            
            if (!isset($this->info['options']['lang'])){
                $this->info['options']['lang']=  $this->site->urlRouter->siteLanguage;
                $updateOptions=true;
            }            
            $this->update();
            if ($updateOptions){                
                $this->updateOptions();
                if ($checkWatchMail) {
                    $watchArray=isset($this->info['options']['watch']) ? $this->info['options']['watch'] : array();
                    $mailFrequency=(isset($this->info['options']['mailEvery']) && $this->info['options']['mailEvery']) ? $this->info['options']['mailEvery'] : 1;
                    $this->checkWatchMailSetting($this->info['id'], $mailFrequency);
                }
            }
            /*if (isset ($this->pending['redirect'])) {
                $redirect=$this->pending['redirect'];
                unset($this->pending['redirect']);
                $this->update();
                $this->redirectTo($redirect);
            }*/
        }
    }
    
    function emailVerified(){
        $email=$this->info['options']['email'];
        $emailKey=$this->info['options']['emailKey'];
        unset($this->info['options']['email']);
        unset($this->info['options']['emailKey']);
        if (!isset($this->info['options']['nb'])){
            $this->info['options']['nb']=array('ads'=>1,'coms'=>1,'news'=>1,'third'=>1);
        }
        $succeed=false;
        $q="update web_users set user_email=?,opts=? where id=?";
        $options=json_encode($this->info['options']);
        if ($this->db->queryResultArray($q, array($email,$options,$this->info['id']),true)) {
            $succeed=true;
            if($this->info['level']==6){
                $this->info['level']=0;
                $this->setLevel($this->info['id'],0);
            }
            $this->info['email']=$email;
            $this->update();
            $watchArray=isset($this->info['options']['watch']) ? $this->info['options']['watch'] : array();
            $mailFrequency=(isset($this->info['options']['mailEvery']) && $this->info['options']['mailEvery']) ? $this->info['options']['mailEvery'] : 1;
            $this->checkWatchMailSetting($this->info['id'], $mailFrequency);
        }else {
            $this->info['options']['email']=$email;
            $this->info['options']['emailKey']=$emailKey;
            $this->update();
        }
        return $succeed;
    }
    
    function pageEmailVerified(){
        $email=$this->info['options']['bmail'];
        $emailKey=$this->info['options']['bmailKey'];
        unset($this->info['options']['bmail']);
        unset($this->info['options']['bmailKey']);
        $succeed=false;
        if (!isset($this->user->info['options']))
            $this->info['options']=array();
        if (!isset($this->info['options']['page']))
            $this->info['options']['page']=array();
        $oldEmail='';
        if (isset($this->info['options']['page']['email'])) $oldEmail=$this->info['options']['page']['email'];
        $this->info['options']['page']['email']=$email;
        if ($this->updateOptions()) {
            $succeed=true;
        }else {
            $this->info['options']['page']['email']=$oldEmail;
            $this->info['options']['bmail']=$email;
            $this->info['options']['bmailKey']=$emailKey;
        }
        $this->update();
        return $succeed;
    }
    

    function updateOptions($id=0, $options=null){
        $succeed=false;
        //$q="update web_users set opts=? where id=?";

        $q=$this->db->prepareQuery("update web_users set opts=:options where id=:id");
        if (!is_null($options) && is_array($options))
            $options = json_encode($options);
        else
            $options = json_encode($this->info['options']);
        if (!$id)
            $id= $this->info['id'];
        $q->bindParam(':options', $options, PDO::PARAM_LOB);
        $q->bindParam(':id', $id, PDO::PARAM_INT);

        //if ($this->db->queryResultArray($q, array(json_encode($this->info['options']),$this->info['id']))) {

        if ($q->execute()) {
            $succeed=true;
        }
        $q->closeCursor();
        $this->db->getInstance()->commit();
        
        /*if (in_array($this->info['level'],array(1,2,3,9))){
            $this->getPartnerInfo($this->info['id'], true);
        }*/

        return $succeed;
    }
    
    
    function checkWatchMailSetting($userId, $mailEvery=1,$force=0){
        $q='select * from mail_watchlist where web_user_id = ?';
        $res=$this->db->queryResultArray($q, array($userId), false, PDO::FETCH_NUM );
        $hasRec=($res && count($res) ? true : false);
        $watchInfo = $this->getWatchInfo($userId,$force);
        $hasMail=0;
        if($watchInfo && count($watchInfo)){
            foreach ($watchInfo as $watch){
                if($watch['EMAIL']){
                    $hasMail=1;
                    break;
                }
            }
        }
        if ($hasMail){
            if (!$hasRec) {
                $q='insert into mail_watchlist (web_user_id, mail_every) values (?, ?)';
                $this->db->queryResultArray($q, array($userId, $mailEvery), false, PDO::FETCH_NUM );
            }
        }else {
            if ($hasRec){
                $res=$res[0];
                $q='delete from mail_watchlist where id = ?';
                $this->db->queryResultArray($q, array($res[0]), false, PDO::FETCH_NUM );
            }
        }
        return $watchInfo;
    }

    function removeWatch($id){
        $succeed=false;
        if (is_numeric($id)){
            if($id==-1){
                $q='delete from subscription where web_user_id=?';
                unset($this->info['options']['watch']);
                $this->db->queryResultArray($q, array($this->info['id']));
                $succeed=true;
            }else{
                $index='';
                if(isset($this->info['options']['watch']) && count($this->info['options']['watch'])) {
                    foreach ($this->info['options']['watch'] as $key => $value){
                        if ($value==$id){
                            $index=$key;
                            $succeed=true;
                            break;
                        }
                    }
                }
                if(!$succeed){
                    $options=  $this->getWatchInfo($this->info['id'], true);
                    if ($options !== false) {
                        if (!isset($this->info['options']))
                            $this->info['options']=array();            
                        //$this->info['options']['watch']=$options;
                        $watchArray=array();
                        foreach ($options as $kid => $params){
                            $key=$params['COUNTRY_ID'].'-'.$params['CITY_ID'].'-'.$params['SECTION_ID'].'-'.$params['SECTION_TAG_ID'].'-'.$params['LOCALITY_ID'].'-'.$params['PURPOSE_ID'].'-'.crc32($params['QUERY_TERM']);
                            $watchArray[$key]=$params['ID'];
                        }  
                        $this->info['options']['watch']=$watchArray;
                        foreach ($this->info['options']['watch'] as $key => $value){
                            if ($value==$id){
                                $index=$key;
                                $succeed=true;
                                break;
                            }
                        }
                    }
                }
                $q='delete from subscription where id=? and web_user_id=? returning id';
                if ($succeed) {
                    unset($this->info['options']['watch'][$index]);
                    $this->db->queryResultArray($q, array($id,$this->info['id']), false, PDO::FETCH_NUM );
                }else {
                    $succeed=true;
                }
            }
        }
        return $succeed;
    }
    function insertWatch($params){
        $succeed=false;
        $count=isset($this->info['options']['watch']) ? count($this->info['options']['watch']) : 0;
        $key=$params['cn'].'-'.$params['c'].'-'.$params['s'].'-'.$params['e'].'-'.$params['l'].'-'.$params['p'].'-'.crc32($params['q']);
        if (isset($this->info['options']['watch'][$key])){
            $succeed=$this->info['options']['watch'][$key];
        }elseif($count<=20) {
            $q="insert into subscription (web_user_id, country_id, city_id, section_id, section_tag_id, locality_id, purpose_id, query_term, title, email) values (?,?,?,?,?,?,?,?,?,1) returning id";
            if ($res=$this->db->queryResultArray($q, array($this->info['id'],$params['cn'],$params['c'],$params['s'],$params['e'],$params['l'],$params['p'],$params['q'],$params['t']), false, PDO::FETCH_NUM ) ) {
                $succeed=$res[0][0];
                if (!isset($this->info['options']['watch']))
                    $this->info['options']['watch']=array();            
                $this->info['options']['watch'][$key]=$res[0][0];
            }
        }
        return $succeed;
    }
    
    function updateWatch($id, $title, $email){
        $succeed=false;
        $q="update subscription set title = ?,email=? where id = ? and web_user_id = ?";
        if ($this->db->queryResultArray($q, array($title,$email, $id, $this->info['id']),true) ) {
            $succeed=true;
        }
        return $succeed;
    }
    
    function updateFavorite($id, $state){
        $succeed=false;
        $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id)";
        if ($this->db->queryResultArray($q, array($this->info['id'],$id, $state), true, PDO::FETCH_NUM ) ) {
            $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id=$id";
            $st = $this->db->getInstance()->query($q);
            if ($st) {                
                if ($users=$st->fetch(PDO::FETCH_NUM)) {
                    $q = "update {$this->cfg['search_index']} set starred=({$users[0]}) where id={$id}";
                } else {
                    $q = "update {$this->cfg['search_index']} set starred=() where id={$id}";                    
                }
                $succeed= $this->db->ql->directUpdateQuery($q);
            }
        }
        
        if ($succeed) $this->loadFavorites(true);
        return $succeed;
    }

    function loadFavorites($forceSetting=false){
        $forceSetting=TRUE;
        $label='favs_'.$this->info['id'];
        $foo = $this->db->getCache()->get($label);
        if ($forceSetting || ($foo===FALSE)) {
            $this->favorites=$this->_loadFavorites();
            $this->db->getCache()->set($label, $this->favorites);
        } else {
            $this->info['favCount']=count($foo);
            $this->favorites=$foo;
        }
    }

    function _loadFavorites(){
        $site=$this->site;
        $showFavorites=$site->userFavorites;
        $num=$site->num;
        $start=$site->urlRouter->params['start'];
        $site->userFavorites=1;
        $site->user->info['id']=$this->info['id'];
        $site->num=1000;
        $site->urlRouter->params['start']=0;
        $site->execute();
        $ids=array();
       // if ($site->searchResults['body']['total_found']>0 && isset($site->searchResults['body']['matches']) && count($site->searchResults['body']['matches'])>0) {
            //foreach ($site->searchResults['matches'] as $id=>$stats) {
            //    $ids[]=$id;
            //}
       // }
        $this->info['favCount']=$site->searchResults['body']['total_found'];
        $site->urlRouter->params['start']=$start;
        $site->num=$num;
        $site->userFavorites=$showFavorites;
        return $site->searchResults['body']['matches'];
    }

    function reset(){
        $this->session_id=null;
        unset ($this->info);
        unset ($this->params);
        unset ($this->pending);
        unset ($this->favorites);
        $this->info=array('id'=>0, 'inc'=>0, 'app-user'=>0);
        $this->params=array('last_visit'=>0,'country'=>0,'city'=>0);
        $this->pending=array();
    }

    function setStats(){
        if (!isset($this->params['visit'])) {
            $this->params['visit']=1;
        }else{
            $this->params['visit']++;
        }
        $this->update();
        if($this->info['id']){
            if(!isset($this->info['options']['UA']) || 
                    (isset($this->info['options']['UA']) && $this->info['options']['UA']!=$_SERVER['HTTP_USER_AGENT']) ){
                $this->info['options']['UA']=$_SERVER['HTTP_USER_AGENT'];
                $this->update();
                $this->updateOptions();
            }
        }
    }
    
    function getSessionHandlerCookieData(){
        $data=null;
        if (isset ($_COOKIE['mourjan_user'])) {
            $data=json_decode($_COOKIE['mourjan_user']);
            if (is_object($data)) {
                if (isset($data->mu)) {
                    $this->params['mourjan_user']=1;
                    $this->update();
                }
            }
        }
    }

    function getCookieData(){
        $data=null;
        if (isset ($_COOKIE['mourjan_user'])) {
            $data=json_decode($_COOKIE['mourjan_user']);
            if (is_object($data)) {
                if (isset($data->lv) && is_numeric($data->lv) && $data->lv>0) {
                    $this->params['last_visit']=$data->lv;
                }
                if (isset($data->on) && in_array($data->on,array(0,1))) {
                    $this->params['keepme_in']=$data->on;
                }
                if (isset($data->m) && in_array($data->m,array(0,1))) {
                    $this->params['mobile']=$data->m;
                }
                if (isset($data->or) && in_array($data->or,array(0,1))) {
                    $this->params['catsort']=$data->or;
                }
                //mobile screen dimension
                if (isset($data->sc) && is_array($data->sc) && count($data->sc)==2) {
                    $this->params['screen']=$data->sc;
                }
                /*
                if (isset($data->z) && in_array($data->z,array(0,1,2))) {
                    $this->params['sorting']=$data->z;
                    $this->site->sortingMode=$this->params['sorting'];
                }
                */
                if (isset($data->mu)) {
                    //$this->info['mu']=1;
                    $this->params['mourjan_user']=1;
                }
                
                /*if (isset($data->cn) && is_numeric($data->cn)) {
                    $this->params['country']=$data->cn;
                }*/
                /*if (isset($data->w) && is_numeric($data->w) && isset($data->h) && is_numeric($data->h)){
                    $this->params['screen']=array('w'=>$data->w,'h'=>$data->h);
                }*/
            }
        }
        /*
        if ($this->site->urlRouter->module=='oauth' && isset($_COOKIE['mourjan_log'])){
            $data=json_decode($_COOKIE['mourjan_log']);
            if (is_object($data)) {
                if (isset($data->dn) && isset($data->sp)) {
                    $this->info['name']=$data->dn;
                    $this->info['provider']=$data->sp;
                }
            }
        }*/
    }

    function setCookieData(){
        if($this->cfg['modules'][$this->site->urlRouter->module][0]=='Bin')return;
        $info=array(
            'lv'=>time(), 
            'ap'=>($this->site->urlRouter->isApp?1:0)
        );
        if(isset($this->params['lang']) && $this->params['lang']){
            $info['lg']=$this->params['lang'];
        }
        if (isset($this->params['country']) && $this->params['country']){
            $info['cn']=  $this->params['country'];
        }
        if (isset($this->params['city']) && $this->params['city']){
            $info['c']=  $this->params['city'];
        }
        if(isset($this->params['mobile'])){
            $info['m']=$this->params['mobile'];
        }        
        if(isset($this->params['catsort'])){
            $info['or']=$this->params['catsort'];
        }
        if(isset($this->params['screen'])){//mobile screen dimensions
            $info['sc']=$this->params['screen'];
        }
        /*
        if(isset($this->params['sorting'])){
            $info['z']=$this->params['sorting'];
        }
        */
        if (isset($this->params['keepme_in'])){
            $info['on']=$this->params['keepme_in'];
        }
        
        if (isset($this->params['mourjan_user']) || $this->info['id']>0) {
            $info['mu']=1;    
        }
        
        /*if (isset($this->user->params['screen']['w']) && $this->user->params['screen']['w']) {
            $info['w']=$this->user->params['screen']['w'];
            $info['h']=$this->user->params['screen']['h'];
        }*/
        
        
        //setcookie('mourjan_user', '', 1,'/','.mourjan.com');
        
        if (isset($_COOKIE['mourjan_usr'])) {
            setcookie('mourjan_usr', '', 1,'/','.mourjan.com');
            setcookie('mourjan_usr', '', 1,'/',$this->cfg['site_domain']);
        }
        
        setcookie('mourjan_user', json_encode($info), time()+31536000,'/', $this->cfg['site_domain'], false);
        
        if(isset($_COOKIE['mourjan_log']) || isset($_COOKIE['mourjan_login'])){
            setcookie('mourjan_login', '', 1,'/','.mourjan.com');
            setcookie('mourjan_login', '', 1,'/',$this->cfg['site_domain']);
            setcookie('mourjan_log', '', 1,'/',$this->cfg['site_domain']);
        }
        /*
        if(isset($_GET['debug'])){
            var_dump($_COOKIE);
        }*/
    }
    
    function setActiveCookie(){
        $info=array(
            'on'=>1
        );
        setcookie('mourjan_sess', json_encode($info), 0,'/',$this->cfg['site_domain']);
    }

    function populate(){
        $this->session_id=session_id().$this->cfg['site_key'];
        if (isset($_SESSION[$this->session_id]['info'])) $this->info=$_SESSION[$this->session_id]['info'];
        if (isset($_SESSION[$this->session_id]['params'])) $this->params=$_SESSION[$this->session_id]['params'];
        if (isset($_SESSION[$this->session_id]['pending'])) $this->pending=$_SESSION[$this->session_id]['pending'];
    }

    function isSpider(){
        if (isset ($this->params['spider'])) return $this->params['spider'];
        $pattern="/Googlebot|Yammybot|MJ12bot|Openbot|Yahoo|BingBot|Slurp|msnbot|ia_archiver|Lycos|Scooter|AltaVista|Teoma|Gigabot|Googlebot-Mobile/i";
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && preg_match($pattern, $_SERVER['HTTP_USER_AGENT'])) {
                return TRUE;
                $this->params['spider']=true;
        }
        $this->params['spider']=false;
        return FALSE;
    }

    function isUser(){
        $check = false;
        if (!isset($this->params['is_human']) || (isset($_SERVER['HTTP_USER_AGENT']) &&
                (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Firefox') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Safari') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Camino') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Konqueror') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Netscape') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Opera') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'SeaMonkey') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'SonyEricsson') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Nokia') ||
                    stristr($_SERVER['HTTP_USER_AGENT'], 'Chrome')
                ))) {
                $check=true;
                $this->params['is_human']=$check;
                $this->update();
            }elseif (isset($this->params['is_human'])){
                $check = $this->params['is_human'];
            }else {
                $this->params['is_human']=$check;
                $this->update();
            }
        return $check;
    }

    function toJson(){
        $user=array('info'=>$this->info,'params'=>$this->params);
        return json_encode($user);
    }

    function toArray(){
        $user=array('info'=>$this->info,'params'=>$this->params);
        return $user;
    }

    function update(){
        $_SESSION[$this->session_id]['info']=$this->info;
        $_SESSION[$this->session_id]['params']=$this->params;
        $_SESSION[$this->session_id]['pending']=$this->pending;
    }

    function logout(){
        $countryId=isset($this->params['country'])?$this->params['country']:0;
        $cityId=isset($this->params['city'])?$this->params['city']:0;
        $sorting=isset($this->params['sorting'])?$this->params['sorting']:-1;
        $sortingLang=isset($this->params['list_lang'])?$this->params['list_lang']:-1;
        $mourjanUser = isset($this->params['mourjan_user']) ? 1 : NULL;
        
        if(isset($_COOKIE['__uvme'])){
            setcookie('__uvme', '', 1,'/',$this->cfg['site_domain']);
        }
                
        if (session_destroy()) {
        	session_start();
            session_regenerate_id();
            $this->reset();
            $this->params['country']=$countryId;
            $this->params['city']=$cityId;
            $this->params['visit']=1;
            if($sorting > -1){
                $this->params['sorting']=$sorting;
            }
            if($sortingLang > -1){
                $this->params['list_lang']=$sortingLang;
            }
            if ($mourjanUser!==NULL) {
                $this->params['mourjan_user']=1;
            }
            $this->update();
            return true;
        }
        return false;
    }
    
    function formatContactNumbers($contacts,$lang){
        $result='';
        $mobileStr='';
        $phoneStr='';
        $faxStr='';
        $i=0;
        foreach ($contacts as $contact){
            $code=  preg_split('/\|/', $contact[1]);
            $number=$code[1].$contact[2];
            switch($contact[0]){
                case 0:
                $mobileStr.='<span class="pn">'.$number.'</span><span class="link" onclick="rmN(this,'.$i.')">'.$lang['remove'].'</span><br />';
                break;
                case 1:
                $phoneStr.='<span class="pn">'.$number.'</span><span class="link" onclick="rmN(this,'.$i.')">'.$lang['remove'].'</span><br />';
                break;
                case 2:
                $faxStr.='<span class="pn">'.$number.'</span><span class="link" onclick="rmN(this,'.$i.')">'.$lang['remove'].'</span><br />';
                break;
                default:
                break;
            }
            $i++;
        }
        $result.=($mobileStr ? '<label>'.$lang['labelP0'].'</label><div>'.$mobileStr.'</div>':'');
        $result.=($phoneStr ? '<label>'.$lang['labelP1'].'</label><div>'.$phoneStr.'</div>':'');
        $result.=($faxStr ? '<label>'.$lang['labelP2'].'</label><div>'.$faxStr.'</div>':'');
        if ($result) $result='<hr />'.$result;
        return $result;
    }
    function displayContactNumbers($contacts,$email='',$lang){
        $result='';
        $mobileStr='';
        $phoneStr='';
        $faxStr='';
        $i=0;
        if ($contacts) {
        foreach ($contacts as $contact){
            $code=  preg_split('/\|/', $contact[1]);
            $number=$code[1].$contact[2];
            switch($contact[0]){
                case 0:
                $mobileStr.='<span>'.$number.'</span><br />';
                break;
                case 1:
                $phoneStr.='<span>'.$number.'</span><br />';
                break;
                case 2:
                $faxStr.='<span>'.$number.'</span><br />';
                break;
                default:
                break;
            }
            $i++;
        }
        }
        $result.=($mobileStr ? '<li class="ctl"><label><span class="ico p0"></span>'.$lang['labelP0'].':</label><div class="pnd">'.$mobileStr.'</div></li>':'');
        $result.=($phoneStr ? '<li class="ctl"><label><span class="ico p1"></span>'.$lang['labelP1'].':</label><div class="pnd">'.$phoneStr.'</div></li>':'');
        $result.=($faxStr ? '<li class="ctl"><label><span class="ico p2"></span>'.$lang['labelP2'].':</label><div class="pnd">'.$faxStr.'</div></li>':'');
        $result.=($email ? '<li class="ctl"><label>'.$lang['email'].':</label><div class="pnd"><span>'.$email.'</span></div></li>':'');
        if ($result) $result=$result;
        return $result;
    }
    
    function parseDT($v, $d){
        $n=$v;
        if($d){
            if ($n<12){
                $n.=' صباحاً';
            }else if ($n==12){
                $n.=' ظهراً';
            }else if ($n<16){
                $n=($n-12).' بعد الظهر';
            }else if ($n<18){
                $n=($n-12).' عصراً';
            }else{
                $n=($n-12).' مساءً';
            }
        }else {
            if($n<12){
                $n.=' AM';
            }else{
                $n=($n-12).' PM';
            }
        }
        return $n;
    }
    function parseUserAdTime($cui /*contact user info array*/,$cut /*contact user times array*/,$d=0 /* ad rtl */){
        $l=isset($cui['p']) ? count($cui['p']) : 0;
        $t='';
        $v=$d?' / ':' / ';
        if($l){
            if($cut['t']){
                switch($cut['t']){
                    case 1:
                        $v.=($d?' يرجى الإتصال قبل ':' please call before ').$this->parseDT($cut['b'],$d).' -';
                        break;
                    case 2:
                        if($d)
                            $v.=' يرجى الإتصال بين '.$this->parseDT($cut['a'],$d).' و '.$this->parseDT($cut['b'],$d).' -';
                        else 
                            $v.=' please call between '.$this->parseDT($cut['a'],$d).' and '.$this->parseDT($cut['b'],$d).' -';
                        break;
                    case 3:
                        $v.=($d?' يرجى الإتصال بعد ':' please call after ').  $this->parseDT($cut['a'],$d).' -';
                        break;
                    default:
                        break;
                }
            }
            $s='';
            $g='';
            $k=0;
            $r=array();
            for($i=0;$i<$l;$i++){
                $g=$cui['p'][$i]['v'];
                $k=$cui['p'][$i]['t'];
                $s='';
                if(!isset($r[$k])){
                    switch($k){
                        case 1:
                            $s=($d?'موبايل':'Mobile');
                            break;
                        case 2:
                            $s=($d?'موبايل + فايبر':'Mobile + Viber');
                            break;
                        case 3:
                            $s=($d?'موبايل + واتساب':'Mobile + Whatsapp');
                            break;
                        case 4:
                            $s=($d?'موبايل + فايبر + واتساب':'Mobile + Viber + Whatsapp');
                            break;
                        case 5:
                            $s=($d?'واتساب فقط':'Whatsapp only');
                            break;
                        case 7:
                            $s=($d?'هاتف':'Phone');
                            break;
                        case 8:
                            $s=($d?'تلفاكس':'Telefax');
                            break;
                        case 9:
                            $s=($d?'فاكس':'Fax');
                            break;
                        case 13:
                            $s=($d?'فايبر + واتساب فقط':'Viber + Whatsapp only');
                            break;
                        default:
                            break;
                    }
                    $r[$k]=array(
                        's'=>' - '.$s.': ',
                        'c'=>0
                    );
                }  
                if($r[$k]['c'])
                    $r[$k]['s'].=$d?' او ':' or ';
                $r[$k]['s'].='<span class="pn">'.$g.'</span>';
                $r[$k]['c']++;
            }
            foreach ($r as $key => $value){
                $t.=$value['s'];
            }
        }
        if(isset($cui['b']) && $cui['b']){
            $t.=' - '.($d?'بلاكبيري مسنجر':'BBM pin').': <span class="pn">'.$cui['b'].'</span>';
        }
        if(isset($cui['t']) && $cui['t']){
            $t.=' - '.($d?'تويتر':'Twitter').': <span class="pn">'.$cui['t'].'</span>';
        }
        if(isset($cui['s']) && $cui['s']){
            $t.=' - '.($d?'سكايب':'Skype').': <span class="pn">'.$cui['s'].'</span>';
        }
        if(isset($cui['e']) && $cui['e']){
            $t.=' - '.($d?'البريد الإلكتروني':'Email').': <span class="pn">'.$cui['e'].'</span>';
        }
        if($t)
            $v.=substr($t,2);
        else $v='';
        return $v;
    }
    
}
?>
