<?php
namespace Core\Model;

//require_once get_cfg_var('mourjan.path').'/deps/autoload.php';

class Router 
{
    var $db;
    var $uri;
    
    var $cfg,$cookie;
    var $pageTitle = array('ar'=>'', 'en'=>'');
    var $siteLanguage = '';
    var $extendedLanguage = '';
    var $siteTranslate = '';
    var $module = 'index';
    var $userId = 0;
    var $watchId=null;
    var $basePartnerId=1016458799;
    var $baseUserId=2016458799;
    var $countryId = 0;
    var $cityId = 0;
    var $purposeId = 0;
    var $rootId = 0;
    var $sectionId = 0;
    var $id=0;
    
    var $params= ['start'=>0,'q'=>'','iq'=>'', 'id'=>FALSE, 'cn'=>FALSE, 'c'=>FALSE, 'ro'=>FALSE, 'se'=>FALSE, 'pu'=>FALSE, 'rss'=>FALSE];
    
    var $countries=NULL;
    var $cities=NULL;
    var $publications=NULL;
    var $roots=NULL;
    var $sections=NULL;
    var $purposes=NULL;
    
    var $pageRoots=NULL;
    var $pageSections=NULL;
    var $pagePurposes=NULL;
    var $naming=NULL;
    
    
    var $force_search = false;
    var $isDynamic = false;
    var $isMobile = false;
    var $isApp = 0;
    var $host = 'www.mourjan.com';
    var $referer = '';
    var $session_key;
    var $internal_referer = false;
    var $http_status = 200;
    var $last_modified = false;
    var $count = 0;
    var $isPriceList = 0;
    
    
    function __construct($params) 
    {
        global $argc;
        $this->cfg=$params;
        $this->db = new DB($params);
        
        if (isset($argc)) return;   

	if(isset($_GET['shareapp']))
        {
            $device = new \Detection\MobileDetect();
            if($device->isMobile())
            {
                if( $device->isiOS() )
                {
                    if(preg_replace('/_.*/','',$device->version('iPhone')) > 7)
                    {
                        header("Location:https://itunes.apple.com/us/app/mourjan-mrjan/id876330682?ls=1&mt=8");
                    }
                }
                if( $device->isAndroidOS() )
                {
                    header("Location:https://play.google.com/store/apps/details?id=com.mourjan.classifieds");
                }
            }
        }
        
        if (isset ($_COOKIE['mourjan_user'])) 
        {
            $this->cookie=json_decode($_COOKIE['mourjan_user']);            
            if (!is_object($this->cookie)) 
            {
                $this->cookie=null;
            }
        }
        
        $this->session_key = session_id();
        $_session_params = $_SESSION['_u']['params'] ?? [];
                
        if(isset($_GET['app']))
        {
            $this->isApp = $_GET['app'];
        }
        elseif(isset($_session_params['app']))
        {
            $this->isApp = $_session_params['app'];
        }

        if (array_key_exists('HTTP_HOST', $_SERVER))
        {
            $this->host = $_SERVER['HTTP_HOST'];
        }
        

        if (array_key_exists('HTTP_REFERER', $_SERVER))
        {
            $this->referer=$_SERVER['HTTP_REFERER'];
        }

        
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) 
        {
            if (array_key_exists($_SERVER['HTTP_USER_AGENT'], $this->cfg['blocked_agents']) || empty($_SERVER['HTTP_USER_AGENT'])) {
                header("HTTP/1.1 403 Forbidden");
                exit(0);
            }            
        } 
        else 
        {
            header("HTTP/1.1 403 Forbidden");
            exit(0);            
        }

        $pos = strpos($this->referer, $this->cfg['site_domain']);
        if (!($pos===FALSE)) {
            $this->internal_referer = ($pos>0 && $pos<13);
        }
        
        if ($this->isApp) 
        {
            $this->isMobile = TRUE;
            $_session_params['mobile']=1;
            $_session_params['app']=1;
        }
      
        if (!isset($_session_params['mobile'])) 
        {
            if(isset($this->cookie->m) && in_array($this->cookie->m,array(0,1)))
            {
                $this->isMobile = (int)$this->cookie->m ? true : false;
                $_session_params['mobile']=(int)$this->cookie->m;
            }
            else
            {
                $device = new \Detection\MobileDetect();

                if ($device->isMobile() && !$device->isTablet()) 
                {
                    $this->isMobile = TRUE;
                    $_session_params['mobile']=1;
                } else {
                    $this->isMobile = FALSE;
                    $_session_params['mobile']=0;
                }
            }
        }

        if (isset($_POST['mobile'])) 
        {
            if ($_POST['mobile']) {
                $this->isMobile = TRUE;
                $_session_params['mobile']=1;
            }else {
                $this->isMobile = false;
                $_session_params['mobile']=0;
            }
        }
        elseif (isset($_session_params['mobile'])) 
        {
            $this->isMobile = $_session_params['mobile'];
        }

        $_request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);

        if (\preg_match('/\/en(?:\/|$)/',$_request_uri)) 
        {
            $this->siteLanguage = 'en';
            //$this->uri = rtrim(parse_url(str_replace('/en/', '/', $_SERVER['REQUEST_URI']), PHP_URL_PATH), '/');
            $this->uri = rtrim(parse_url(preg_replace('/\/en(?:\/|$)/', '/', $_request_uri), PHP_URL_PATH), '/');
        } 
        elseif (preg_match('/\/fr(?:\/|$)/', $_request_uri)) 
        {
            $this->siteLanguage = 'en';
            $this->extendedLanguage = 'fr';
            //$this->uri = rtrim(parse_url(str_replace('/en/', '/', $_SERVER['REQUEST_URI']), PHP_URL_PATH), '/');
            $this->uri = rtrim(parse_url(preg_replace('/\/fr(?:\/|$)/','/', $_request_uri), PHP_URL_PATH), '/');
        } 
        else 
        {
            $this->siteLanguage = 'ar';
            $this->uri = rtrim( parse_url($_request_uri, PHP_URL_PATH), '/');
        }
        
        $_session_params['lang']=$this->siteLanguage;
                
        if (isset($_SERVER['HTTP_REFERER']) && preg_match('/translate\.google\.com/', $_SERVER['HTTP_REFERER']))
        {
            $toLang=null;
            preg_match('/&langpair\=[a-z]{2}(?:\||%7C)([a-z]{2})/', $_SERVER['HTTP_REFERER'], $toLang);
            if ($toLang && count($toLang)>1)
            {
                $this->siteTranslate=$toLang[1];
            }
            else 
            {
                preg_match('/&tl\=([a-z]{2})/', $_SERVER['HTTP_REFERER'], $toLang);
                if ($toLang && count($toLang)>1)
                {
                    $this->siteTranslate=$toLang[1];
                }
            }
        }                   

	if(preg_match('/\/(?:houses|villas)(?:\/|$)/i', $this->uri))
        {
            $this->uri = preg_replace('/\/(?:houses|villas)(\/|$)/','/villas-and-houses$1',$this->uri);
            if($this->uri[strlen($this->uri)-1]!='/'){
                $this->uri .= '/';
            }
            $_SESSION['_u']['params'] = $_session_params;
            $this->redirect($this->uri, 301);
        }
        
        if (substr($this->uri, -10)=='/index.php')
        {
            $this->uri = substr($this->uri, 0, strlen ($this->uri)-10);
        }
                    
        $_args = explode('&', $_SERVER['QUERY_STRING']);
                        
        $count = count($_args);
        for ($i = 0; $i < $count; ++$i) {
            $node = explode('=', $_args[$i]);
            if (!empty($node[1]) && array_key_exists($node[0], $this->params)) {
                
                $this->params[$node[0]]=  trim(urldecode($node[1]));
                
                switch ($node[0]) {
                    case 'rss':
                        $this->params['rss'] = TRUE;
                        $this->force_search=true;
                        break;
                    case 'id':
                        //workaround for youtube upload callback
                        if (!isset($_GET['status'])) {
                            $this->id = intval($node[1]);                        
                            $ad_url = $this->getAdURI($this->id);
                            $this->module = 'detail';
                            $this->http_status = 410;
                            header('HTTP/1.1 410 Gone');
                            return;
                        }
                        break;
                    
                    case 'q':
                        $this->force_search=true;
                        break;
                    default:
                        $this->isDynamic = true;
                        break;
                }
            }                        
        }
        
        if(isset($_GET['aid']) && isset($_GET['q']))
        {
            $this->force_search=true;
        }
            
        if ($this->params['start'] && !array_key_exists('start', $_GET))
        {
            $_GET['start']=  $this->params['start'];
        }
             

        $_args = explode('/', $this->uri);
        if (!empty($_args)) 
        {
            $idx=count($_args)-1;
                
            if (is_numeric($_args[$idx]) ) 
            {
                $this->id=(int)$_args[$idx];
                $rpos=strrpos($this->uri, '/');
                if ($rpos)
                    $this->uri = substr($this->uri, 0, $rpos);
                
                if ($this->id<1000000000) 
                {
                    if ($this->id>1000) 
                    {
                        $this->module='detail';
                        $ad_url = $this->getAdURI($this->id);
                        if ($ad_url!=$this->cfg['host'].$this->uri.'/'.($this->siteLanguage=='ar'?'':$this->siteLanguage.'/').$this->id.'/')
                        {
                            if ($ad_url!=$this->cfg['url_base']) 
                            {
                                $_SESSION['_u']['params'] = $_session_params;
                                $this->redirect($ad_url, 301);
                            } 
                            else 
                            {
                                $this->id=0;
                                $this->http_status=410;
                                $this->module = 'notfound';
                            }
                        }
                        $idx=-1;
                    } 
                    else 
                    {
                        $this->module="search";
                        $this->params['start'] = $this->id;
                        $this->id=0;

                        if ($this->params['start']<1) {
                            $this->http_status=410;
                                $this->module = 'notfound';
                        }
                        unset($_args[$idx]);
                        $idx--;
                    }
                }
            }
            
            if ($idx>=0 && isset($_args[1]) && is_numeric($_args[1])) {
                $id=(int)$_args[1];
                if ($id>2000000000){
                    $this->watchId=$id-$this->baseUserId;
                    $this->module='search';
                    $this->force_search=true;
                    $this->id=0;
                    unset($_args[0]);
                    $this->uri = substr($this->uri, (strlen($id)+1));
                }elseif ($id>1000000000){//partner id handling
                    $this->userId=$id-$this->basePartnerId;
                    $this->module='search';
                    $this->force_search=true;
                    $this->id=0;
                    unset($_args[0]);
                    $this->uri = substr($this->uri, (strlen($id)+1));
                }
            }

            if ($idx>1 && substr($_args[$idx],0,2)=="q-") {
                $tag_info = explode("-", $_args[$idx]);
                
                if (count($tag_info)==3 && is_numeric($tag_info[1]) && is_numeric($tag_info[2])) {
                    $this->params['tag_id']=$tag_info[1];
                    $_args[$tag_info[2]] = substr($_args[$tag_info[2]], 0, strrpos($_args[$tag_info[2]], "-"));
                    unset($_args[$idx]);                
                    $tmp=array();
                    foreach ($_args as $arg){
                        $tmp[]=$arg;
                    }
                    $_args=$tmp;                    
                    $this->uri=  implode("/", $_args);                
                }
            }elseif ($idx>1 && substr($_args[$idx],0,2)=="c-") {
                $tag_info = explode("-", $_args[$idx]);
                
                if (count($tag_info)==3 && is_numeric($tag_info[1]) && is_numeric($tag_info[2])) {
                    $this->params['loc_id']=$tag_info[1];
                    unset($_args[$tag_info[2]]);
                    unset($_args[$idx]);
                    $tmp=array();
                    foreach ($_args as $arg){
                        $tmp[]=$arg;
                    }
                    $_args=$tmp;                    
                    $this->uri=  implode("/", $_args);
                }
            }            
        }        
                

	if ((!$this->internal_referer || strstr($this->referer, '/oauth/')) &&  empty($_GET) && ($this->uri=='' || $this->uri=='/')  && !$this->userId && !$this->watchId) 
        {
            $this->countries = $this->db->getCountriesData($this->siteLanguage);
            
            if (isset($_session_params['visit']) && isset($_session_params['user_country'])) 
            { 
                if (!$this->countryId) 
                {  
                    $curi = $this->uri;
                    if (isset($this->cookie->cn) && $this->cookie->cn)
                    {
		        if (!isset($_GET['app']) && isset($this->cookie->lg) && in_array($this->cookie->lg, array('ar','en'))) 
                        {
                            $this->siteLanguage=$_session_params['lang']=$this->cookie->lg;                            
                        }

                        $this->countryId=$_session_params['country']=$this->cookie->cn;
                        $this->cityId=$_session_params['city']=0;
                        $this->uri='/'. $this->countries[$this->cookie->cn]['uri'];
                        if(isset($this->cookie->c) && $this->cookie->c)
                        {
                            if (isset($this->countries[$this->countryId]['cities'][$this->cookie->c])) 
                            {
                                $this->uri.='/'.$this->countries[$this->countryId]['cities'][$this->cookie->c]['uri'];
                    		$this->cityId=$_session_params['city']=$this->cookie->c;
                            }
                        }
                        
                        if ($this->uri!=$curi) 
                        {
                            $_SESSION['_u']['params'] = $_session_params;
                            $this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    } 
                    else                         
                    {
                        $_SESSION['_u']['params'] = $_session_params;
                        $this->setGeoByIp();
                        if ($this->uri!=$curi) 
                        {                            
                            $this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    }                    
                }
            }    
            
        
            if ( !isset($_session_params['visit'])) 
            {
                $current_uri = $this->uri;
                $_SESSION['_u']['params'] = $_session_params;
                $this->setGeoByIp();
                if ($current_uri!=$this->uri) 
                {                    
                    $this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );                
                }
                
                if (!$this->countryId)
                {                
                    if(isset($this->cookie->cn) && $this->cookie->cn)
                    {
                        $this->countryId=$_session_params['country']=$this->cookie->cn;
                        $this->cityId=$_session_params['city']=0;
                        $this->uri='/'. $this->countries[$this->cookie->cn]['uri'];
                        if(isset($this->cookie->c) && $this->cookie->c){
                            if (isset($this->countries[$this->countryId]['cities'][$this->cookie->c])) {
                    		$this->uri.='/'.$this->countries[$this->countryId]['cities'][$this->cookie->c]['uri'];
                    		$this->cityId=$_session_params['city']=$this->cookie->c;
                            }
                        }
                        if ($current_uri!=$this->uri) 
                        {
                            $_SESSION['_u']['params'] = $_session_params;
                            $this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    }
                }    
            }
        }

        if(!isset($_GET['app']) && !isset($_session_params['user_country']))
        {
            $geo = $this->getIpLocation();
            if (isset($geo['country'])) 
            {
            	$country_code=strtolower(trim($geo['country']['iso_code']));
            	$_session_params['user_country']=$country_code;
            	$_session_params['latitude'] = isset($geo['location']['latitude']) ? $geo['location']['latitude'] : 0.0;
            	$_session_params['longitude'] = isset($geo['location']['longitude']) ? $geo['location']['longitude'] : 0.0;
            }
            else
            {
                $_session_params['user_country']='';
            }
        }
        
        if (!isset($_session_params['lang']) ) 
        {
            if (!isset($_GET['app']) && isset($this->cookie->lg) && in_array($this->cookie->lg, array('ar','en'))) 
            {
                $this->siteLanguage=$_session_params['lang']=$this->cookie->lg;
                $_SESSION['_u']['params'] = $_session_params;
                $this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').($this->id ? $this->id.'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
            } 
            else 
            {
                $_session_params['lang']=$this->siteLanguage;
            }
        } 
        else 
        {
            $_session_params['lang']=$this->siteLanguage;
        }
        $_SESSION['_u']['params'] = $_session_params;
    }
    
    
    public function getIpLocation($ip=NULL) 
    {
        if (empty($ip)) 
        {
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
            	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            else 
            {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
            
        $databaseFile = '/home/db/GeoLite2-City.mmdb';
        $reader = new \MaxMind\Db\Reader($databaseFile);
        $ips = explode(',', $ip);
        foreach ($ips as $addr) 
        {
            $geo = $reader->get(trim($addr));
            if (isset($geo['country'])) break;
        }
        $reader->close();
        
        //error_log(json_encode($geo));
        return $geo;
    }
	
	
    private function setGeoByIp() 
    {
    	$geo = $this->getIpLocation();
			
        $_session_params = $_SESSION['_u']['params'];
        if (!empty($geo) && isset($geo['country']['iso_code'])) 
        {
            $country_code=strtolower(trim($geo['country']['iso_code']));
            $_session_params['user_country']=$country_code;
            $_session_params['latitude'] = $geo['location']['latitude'] ?? 0.0;
            $_session_params['longitude'] = $geo['location']['longitude'] ?? 0.0;
                
            if (array_key_exists($country_code, $this->cfg['iso_countries'])) 
            {
                $this->countryId = $this->cfg['iso_countries'][$country_code];                    
                    
                $this->uri='/'.$country_code; 
                $_session_params['city']=0;
                
                if (count($this->countries[$this->countryId]['cities']) > 1) 
                {
                    $this->cache();
                    $default_city = -1;
                    $min = PHP_INT_MAX;
                    foreach ($this->countries[$this->countryId]['cities'] as $city_id=>$city) 
                    {
                        $dist = $this->distance($city['latitude'], $city['longitude']);
                        if ($dist<$min)
                        {
                            $default_city=$city_id;
                            $min=$dist;
                        }
                    }                        
                    if ($default_city>0) 
                    {
                        $this->uri.='/'.$this->countries[$this->countryId]['cities'][$default_city]['uri'];
                        $_session_params['city']=$default_city;
                    }
                }
                
                $_session_params['country'] = $this->countryId;
                    
                if(isset($this->cookie->lg) && in_array($this->cookie->lg,array('ar','en')))
                {
                    $this->siteLanguage=$_session_params['lang']=$this->cookie->lg;                        
                }else{
                    $_session_params['lang']=$this->siteLanguage;
                }
                    
                //$this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->siteLanguage != 'ar' ? $this->siteLanguage .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
            } else {
                $_session_params['country'] = $this->countryId;
                $_session_params['city']=0;
            }
        }
        else
        {
            $_session_params['user_country']='';
            $_session_params['latitude'] = 0.0;
            $_session_params['longitude'] = 0.0;
        }        
        $_SESSION['_u']['params'] = $_session_params;
    }
   

    function __destruct() 
    {
    }
    
    
    function getAdURI($ad_id=0) 
    {
        $result = '';
        include_once $this->cfg['dir'].'/core/model/Classifieds.php';
        $ad_class = new Classifieds($this->db);
        $row = $ad_class->getById($ad_id);
        
        if (!empty($row)) {
            if ($this->siteLanguage=='ar'){
                $result = sprintf($row[18], '', $ad_id);
            }else {
                $result = sprintf($row[18], $this->siteLanguage.'/', $ad_id);
            }
            $this->countryId = (int)$row[4];
            $this->cityId = (int)$row[5];
            $this->rootId = (int)$row[8];
            $this->sectionId = (int)$row[12];
            $this->purposeId = (int)$row[7];
        } else {
            $url_codes = $this->FetchUrl();
            if ($url_codes) {
                $result = $this->uri.'/'.($this->siteLanguage=='ar'?'':$this->siteLanguage.'/');
            } else {
                $_args = explode('/', $this->uri);
                unset($_args[2]);
                $sss=  implode('/', $_args);
                $url_codes = $this->FetchUrl($sss);
                if ($url_codes) {
                    $result = $sss.'/'.($this->siteLanguage=='ar'?'':$this->siteLanguage.'/');
                }
            }
        }

        return $this->cfg['url_base'].$result;
    }
    
    
    function redirect($url, $status=301) 
    {
        switch ($status) {
            case 302:
                header('HTTP/1.1 302 Found');
                break;
            case 301:
                header('HTTP/1.1 301 Moved Permanently');
                break;
            case 401:
                header ('HTTP/1.1 401 Unauthorized');
                break;
            case 404:
                header('HTTP/1.1 404 Not Found');
                break;
            case 410:
                header('HTTP/1.1 410 Gone');
                break;
            default:
                break;
        }
        $this->close();
        
        header('Location: '. $url);
        
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();
    
        error_log($trace);
        
        exit(0);
    }
    
    
    function cacheHeaders($lastModifiedDate) {
        if ($this->cfg['modules'][$this->module][1]==0) return;
        if (!$this->cfg['site_production']) return;
        if(isset($_GET['provider']))return;
        //header("X-Mourjan-ID: ".$_SESSION['info']['id'] );
        //error_log($_SESSION['info']['id']);
        $SESSION = $_SESSION['_u'];
        if ( isset($SESSION['info']['id']) && $SESSION['info']['id'] && $this->module!='homescreen') return;
        if ($lastModifiedDate) {
            $etag = isset($SESSION['params']['etag']) && isset($SESSION['params']['mobile']) && $SESSION['params']['mobile'] ? $SESSION['params']['etag'] : $this->cfg['etag'];
            //$ifModifiedSince=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
                           
            $etagFile = sprintf('%x%x-%x-%x-%x-%x-%x-%x-%x', $this->isMobile, $etag,
                $this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->purposeId, $this->id, 
                str_pad($lastModifiedDate, 16, '0'));

            $etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

            header("Last-Modified: ". gmdate("D, d M Y H:i:s", $lastModifiedDate)." GMT");
            header("Etag: {$etagFile}");
            header("Cache-Control: public, must-revalidate");
            if ($etagHeader) {
                if ($etagHeader===$etagFile) {
                    include_once $this->cfg['dir']. '/core/layout/Site.php';
                    $site = new \Site($this);
                    $site->handleCacheActions();

                    header("HTTP/1.1 304 Not Modified");
                    exit;               
                } else {
                    return;
                }
            }

            //check if page has changed. If not, send 304 and exit            
            if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])>=$lastModifiedDate) 
            {          
                include_once $this->cfg['dir']. '/core/layout/Site.php';
                $site = new \Site($this);
                $site->handleCacheActions();
                header("HTTP/1.1 304 Not Modified");
               exit;      
            }
        }
    }
    
        
    function cache($force=false) {
        if (empty($this->siteLanguage)) {
            $this->siteLanguage = 'en';
        }

        if ($this->module=='detail' && empty($this->countryId)) {
            $cc=substr($this->uri, 1, 2);
            if ($cc && array_key_exists($cc, $this->cfg['iso_countries'])) {
                $this->countryId = $this->cfg['iso_countries'][$cc];
            }
        }

        if ($force) {
            $result = array();
            $this->countries = NULL;
            $this->cities = NULL;
            $this->publications = NULL;
            $this->sections = NULL;
            $this->purposes = NULL;
        } else {
            $countries_label = "country-data-{$this->siteLanguage}-".Db::$SectionsVersion;
            $roots_label = "root-data-{$this->countryId}-{$this->cityId}-{$this->siteLanguage}-".Db::$SectionsVersion;
            
            $result = $this->db->getCache()->getMulti(['publications', 'roots', 'sections', 'purposes', 'cities-dictionary', 'last', $countries_label, $roots_label]); 
            if (isset($result['cities-dictionary'])) $this->cities = $result['cities-dictionary'];
            if (isset($result['publications'])) $this->publications = $result['publications'];
            if (isset($result['roots'])) $this->roots = $result['roots'];
            if (isset($result['sections'])) $this->sections = $result['sections'];
            if (isset($result['purposes'])) $this->purposes = $result['purposes'];                
            if (isset($result['last'])) $this->last_modified = $result['last'][1][2]; 
            
            if (isset($result[$countries_label])) $this->countries = $result[$countries_label];
            if (isset($result[$roots_label])) $this->pageRoots = $result[$roots_label];

        }
        
        if (!$this->last_modified) {
            $q = $this->db->queryCacheResultSimpleArray('last',
                "SELECT 1, r.MAX_ID, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', r.LAST_UPDATE)
                FROM SPHINX r
                where r.SPHINX_NAME='ACTIVE'", null, 0, $this->cfg['ttl_medium'], $force);
            $this->last_modified = $q[1][2];
        }
    
        if ($this->countries===NULL || empty($this->countries)) {
            $this->countries = $this->db->getCountriesData($this->siteLanguage);
        }
                
        if ($this->publications===NULL || empty($this->publications)) {
            $this->publications = $this->db->getPublications($force);
        }
        
        if ($this->roots===NULL) {
            $this->roots = $this->db->getRoots($force);
        }

        if ($this->sections===NULL){
            $this->sections = $this->db->getSections($force);
        }

        if ($this->purposes===NULL) {
            $this->purposes = $this->db->getPurposes($force);
        }
        
        if ($this->cities===NULL || empty($this->cities)) {
            $this->cities = $this->db->getCitiesDictionary($force);
        }
        
        
        if (!$this->countryId) {
            $this->countryId=0;
            $this->cityId=0;
            $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->siteLanguage);
        } else {
            if ($this->cityId && !isset($this->countries[$this->countryId]['cities'][$this->cityId])) {
                $this->cityId=0;
                $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->siteLanguage);
            }
        }
        
        if ($this->pageRoots==NULL) {
            $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->siteLanguage);
        }
        
        if ($this->module=='search' || $this->module=='detail' || $this->module=='cache') {
            $this->cacheExtension($force);
        }
    }

    
    function cacheExtension($force=false) {       
        if ($this->rootId && $this->pageSections==NULL) {
            $this->pageSections = $this->db->getSectionsData($this->countryId, $this->cityId, $this->rootId, $this->siteLanguage);
        }
            
        if ($this->sectionId && isset($this->pageSections[$this->sectionId])) {
            $this->pagePurposes = $this->pageSections[$this->sectionId]['purposes'];
                                
            if ($this->naming==NULL)
                $this->naming = $this->db->queryCacheResultSimpleArray(
                "naming_{$this->siteLanguage}_{$this->countryId}_{$this->sectionId}",
                "select single, plural, description
                from naming
                where naming.TYPE_ID=2
                and naming.ORIGIN_ID={$this->sectionId}
                and naming.LANG='{$this->siteLanguage}'
                and (country_id={$this->countryId} or country_id=0)
                order by naming.COUNTRY_ID desc",
                null, -1, $this->cfg['ttl_long'], $force);
                                
        } else {
            if ($this->rootId && isset($this->pageRoots[$this->rootId]))
                $this->pagePurposes = $this->pageRoots[$this->rootId]['purposes'];
            else {
                $this->pagePurposes = $this->db->getPurpusesData($this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->siteLanguage);
                //if ($this->rootId) {
                //    error_log(PHP_EOL."Root: ". $this->rootId.PHP_EOL. var_export($this->pageRoots, TRUE));
                //}
            }
        }
    }
    

    function FetchUrl($url=NULL) {
        if (!$url) $url=  $this->uri;

        $result=$this->db->queryCacheResultSimpleArray($url, "
                SELECT r.COUNTRY_ID, r.CITY_ID, r.ROOT_ID, r.SECTION_ID, r.PURPOSE_ID, trim(r.MODULE),
                iif(r.TITLE_EN>'', r.TITLE_EN, SUBSTRING(r.title from POSITION(ascii_char(9) , r.title) for 128)),
                iif(r.TITLE_AR>'', r.title_ar, SUBSTRING(r.title from 1 for POSITION(ascii_char(9), r.title))),
                r.REFERENCE, r.REDIRECT_TO
                FROM URI r
                where r.PATH=?
                and r.BLOCKED=0
                ", array($url), -1, 0, FALSE, TRUE);
        
        return $result;
    }


    function decode() {
        if ($this->id) {
            $this->module='detail';
        } else {
            if ($this->uri) {
                $url_codes = $this->FetchUrl();
                if ($url_codes) {
                    $this->countryId = $url_codes[0];
                    $this->cityId = $url_codes[1];
                    $this->rootId = $url_codes[2];
                    $this->sectionId = $url_codes[3];
                    $this->purposeId = $url_codes[4];
                    $this->module = $url_codes[5];
                    $this->pageTitle['en'] = trim($url_codes[6]);
                    $this->pageTitle['ar'] = trim($url_codes[7]);
                    if (!$this->userId)
                        $this->userId = $url_codes[8];


                    if ($this->module=='cache' ||
                            ($this->module=='watchlist' && $this->params['rss'])) $this->force_search=false;

                    if ($this->force_search) {
                        $this->module='search';
                    } elseif ($this->isMobile && $this->rootId>0 && $this->sectionId==0) {
                        $this->module='index';
                    } elseif ($this->purposeId>0 || $this->rootId>0 || ($this->force_search)) {
                        $this->module='search';
                    }


                    if (($this->module=='search' || $this->module=='index') &&
                        $this->purposeId==0 &&
                        $this->rootId==0 &&
                        $this->sectionId==0 &&
                        empty($this->params['q']) &&
                        $this->params['start']>0) {
                        header('HTTP/1.1 410 Gone');
                        $this->http_status=410;
                    }

                } else {
                    if (strstr($this->uri, '/facebook')) $this->module='facebook';
                    elseif (strstr($this->uri, '/cse')) $this->module='cse';                    
                    else {
                        if ($this->module=='search' && $this->purposeId==0 && $this->rootId==0 && $this->sectionId==0 && empty($this->params['q']) && $this->params['start']>0) {
                            header('HTTP/1.1 410 Gone');
                            $this->http_status=410;
                            $this->module = 'notfound';
                        }
                        elseif ($this->module!='search') {
                            header('HTTP/1.1 404 Not Found');
                            $this->http_status=404;
                            $this->module = 'notfound';
                        }
                    }
                }
            } else {
                if ($this->force_search) $this->module='search';
            }
        }
        $this->uri.='/';
        $this->cache();               
        
        if ($this->http_status==200 && $this->module!='detail' && !$this->force_search) {
            if ($this->module=='search') {
                if ($this->rootId && isset ($this->pageRoots[$this->rootId])) {
		    $this->count = $this->pageRoots[$this->rootId]['counter'];
                    $this->last_modified = $this->pageRoots[$this->rootId]['unixtime'];
                    if ($this->purposeId && isset($this->pageRoots[$this->rootId]['purposes'][$this->purposeId])) {
			$this->count = $this->pageRoots[$this->rootId]['purposes'][$this->purposeId]['counter'];
                        $this->last_modified = $this->pageRoots[$this->rootId]['purposes'][$this->purposeId]['unixtime'];
		    }
                }
                if ($this->sectionId && isset($this->pageSections[$this->sectionId])) {
		    $this->count = $this->pageSections[$this->sectionId]['counter'];
                    $this->last_modified = $this->pageSections[$this->sectionId]['unixtime'];
                    if ($this->purposeId && isset($this->pageSections[$this->sectionId]['purposes'][$this->purposeId])) {
			$this->count = $this->pageSections[$this->sectionId]['purposes'][$this->purposeId]['counter'];
                        $this->last_modified = $this->pageSections[$this->sectionId]['purposes'][$this->purposeId]['unixtime'];
		    }
                }
            }
            elseif ($this->module=='index') {
                if ($this->countryId && isset ($this->countries[$this->countryId]))
                    $this->last_modified = $this->countries[$this->countryId]['unixtime'];
  				if ($this->cityId && isset ($this->countries[$this->countryId]['cities'][$this->cityId]))
                    $this->last_modified = $this->countries[$this->countryId]['cities'][$this->cityId]['unixtime'];
                if ($this->rootId && isset($this->pageRoots[$this->rootId]))
                    $this->last_modified = $this->pageRoots[$this->rootId]['unixtime'];

            }

        
            $this->cacheHeaders($this->last_modified);
        }
        
    }
    
    
    function encodeCurrent($a=0) 
    {
        return $this->encode($this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->purposeId, $a);
    }
    
    
    function encode($cn=0, $c=0, $ro=0, $se=0, $pu=0, $a=0) 
    {
        $result = $this->cfg['url_base'];
        if ($ro==4) $pu=0;
        $rs = $this->db->queryResultArray("select path||'/' PATH from uri where country_id={$cn} and city_id={$c} and root_id={$ro} and section_id={$se} and purpose_id={$pu} and lang='{$this->siteLanguage}'");
        if (!empty($rs)) {            
            $result.= $rs[0]['PATH'];
        } 
        return $result;
    }
    
    
    function getURL($cn=0, $c=0, $ro=0, $se=0, $pu=0, $appendLanguage=true, $mustFound=false) 
    {
        $result = '/';

        if ($cn) 
        {
            if (isset($this->countries[$cn]))
                $result.=$this->countries[$cn]['uri'].'/';
            else {
                $cn=0;
                $c=0;
            }
        }

        if ($c) {
            if (isset($this->cities[$c])) {
                if ($cn==0) {
                    $cn = $this->cities[$c][4];
                    if (isset($this->countries[$cn])) {
                        $result.=$this->countries[$cn]['uri'].'/';
                        $result.=$this->cities[$c][3].'/';
                    }
                } else
                $result.=$this->cities[$c][3].'/';
            }
        }
       
        if ($se && isset($this->sections[$se])) 
            $result.=$this->sections[$se][3].'/';
        else if($ro) {
            $result.=$this->roots[$ro][3].'/';
        }
        
        if ($ro!=4 && $pu && isset($this->purposes[$pu]))
            $result.=$this->purposes[$pu][3].'/';
        
        if ($appendLanguage && $this->siteLanguage!='ar') {
            $result.=$this->siteLanguage.'/';
        }
            
        return $result;        
    }

    
    function close() 
    {
        if ($this->db)
        {
            $this->db->close();            
        }
    }


    function distance($lat, $lon, $ulat=0, $ulon=0) 
    {
        $_session_params = $_SESSION['_u']['params'];
        if (!$ulat) $ulat = $_session_params['latitude'] ?? 0.0;
        if (!$ulon) $ulon = $_session_params['longitude'] ?? 0.0;
        $theta = $ulon - $lon;
        $dist = sin(deg2rad($ulat)) * sin(deg2rad($lat)) +  cos(deg2rad($ulat)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles;
    }
    
}
