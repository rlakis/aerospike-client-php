<?php
namespace Core\Model;

\Config::instance()->incModelFile('Ad')->incModelFile('Content');

include_once 'Singleton.php';

class Router extends \Singleton {
    const POSITIVE_VALUE = ["options" => ["default" => 0, "min_range" => 0]];
    
    var $db;
    var $uri;
    
    private $config;
    public $cookie = null;
    var $pageTitle = ['ar'=>'', 'en'=>''];
    var $language = '';
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
    public $isAMP = 0;
    public $isApp = 0;
    public $client_ip;
    var $host = 'www.mourjan.com';
    var $referer = '';
    var $session_key;
    var $internal_referer = false;
    var $http_status = 200;
    var $last_modified = false;
    var $count = 0;
    var $isPriceList = 0;
    var $isAcceptWebP = 0;
    
    public $_png = '.png';
    public $_jpg = '.jpg';
    public $_jpeg = '.jpeg';
    private $canonical = false;
    private $explodedRequestURI;
    
    private $user;
    private $logger;
    
    
    public static function instance() : Router {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        global $argc;       
        $this->config = \Config::instance();
        $this->db = new DB();
            
        if (isset($argc)) { return; }

        if (isset($_GET['shareapp'])) {
            $device = new \Detection\MobileDetect();
            if($device->isMobile()) {
                if( $device->isAndroidOS() ) {
                    header("Location:https://play.google.com/store/apps/details?id=com.mourjan.classifieds");
                }
                elseif( $device->isiOS() && preg_replace('/_.*/', '', $device->version('iPhone'))>8) {
                    header("Location:https://itunes.apple.com/us/app/mourjan-mrjan/id876330682?ls=1&mt=8");
                }
            }
        }
        
        $cmu=\filter_input(INPUT_COOKIE, 'mourjan_user', FILTER_DEFAULT, ['options'=>['default'=>'{}']]);
        $this->cookie= \json_decode($cmu);
        
        $this->session_key = \session_id();
        $_session_params = $_SESSION['_u']['params'] ?? [];
                
        $this->isAcceptWebP = (isset($_SERVER['HTTP_ACCEPT']) && \strstr($_SERVER['HTTP_ACCEPT'], 'image/webp'));
        if (isset($_SESSION['webp']) && $_SESSION['webp']) {
            $this->isAcceptWebP = 1;            
        } 
        elseif ($this->isAcceptWebP) {
            $_SESSION['webp'] = 1;
        }
        
        if (0 && $this->isAcceptWebP) {
            $this->_png = ".webp";
            $this->_jpg = ".webp";
            $this->_jpeg = ".webp";
        }
        
        
        //if(isset($_GET['app'])) {
        //    $this->isApp = $_GET['app'];
        //}
        //elseif(isset($_session_params['app'])) {
        //    $this->isApp = $_session_params['app'];
        //}

        if (\array_key_exists('HTTP_HOST', $_SERVER)) {
            $this->host = $_SERVER['HTTP_HOST'];
        }
        
        if (\array_key_exists('HTTP_REFERER', $_SERVER)) {
            $this->referer=$_SERVER['HTTP_REFERER'];
        }

        if (\array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            if (\array_key_exists($_SERVER['HTTP_USER_AGENT'], $this->config->get('blocked_agents')) || empty($_SERVER['HTTP_USER_AGENT'])) {
                \header("HTTP/1.1 403 Forbidden");
                exit(0);
            }            
        } 
        else {
            \header("HTTP/1.1 403 Forbidden");
            exit(0);            
        }

        $pos = \strpos($this->referer, $this->config->get('site_domain'));
        if (!($pos===FALSE)) {
            $this->internal_referer = ($pos>0 && $pos<13);
        }
        
        //if ($this->isApp) {
        //    $this->isMobile = TRUE;
        //    $_session_params['mobile']=1;
        //    $_session_params['app']=1;
        //}
      
        if (!isset($_session_params['mobile'])) {
            if (isset($this->cookie->m) && \in_array($this->cookie->m, [0,1])) {
                $this->isMobile = (int)$this->cookie->m ? true : false;
                $_session_params['mobile']=(int)$this->cookie->m;
            }
            else {
                /*
                $device = new \Detection\MobileDetect();

                if ($device->isMobile() && !$device->isTablet()) {
                    $this->isMobile = TRUE;
                    $_session_params['mobile']=1;
                } 
                else {
                    $this->isMobile = FALSE;
                    $_session_params['mobile']=0;
                }*/
            }
        }

        if (isset($_POST['mobile'])) {
            if ($_POST['mobile']) {
                $this->isMobile = TRUE;
                $_session_params['mobile']=1;
            }
            else {
                $this->isMobile = false;
                $_session_params['mobile']=0;
            }
        }
        elseif (isset($_session_params['mobile'])) {
            $this->isMobile = $_session_params['mobile'];
        }
        
        $this->explodedRequestURI = \explode('/', \ltrim(\rtrim(\parse_url(\filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL), PHP_URL_PATH), '/'), '/'));
        $this->language = 'ar';
        
        $len = \count($this->explodedRequestURI);
        
        if ($len>0) {        
            $lastIdx=$len-1;
            
            if ($this->explodedRequestURI[$lastIdx]==='amp') {
                $this->isAMP=1;
                unset($this->explodedRequestURI[$lastIdx]);
                $len--;
                $lastIdx--;
                $this->isMobile=TRUE;
                $this->isApp=FALSE;
                $_session_params['mobile']=1;
            }
            
            $___p=0;
            if (\is_numeric($this->explodedRequestURI[$lastIdx])) {
                $___p=$this->explodedRequestURI[$lastIdx]+0;
                unset($this->explodedRequestURI[$lastIdx]);
                $lastIdx--;
                $len--;          
            }
                                                
            
            if ($len>0) {
                if ($this->explodedRequestURI[$lastIdx]==='en') {
                    $this->language='en';  
                    unset($this->explodedRequestURI[$lastIdx]);
                }
                elseif ($this->explodedRequestURI[$lastIdx]==='fr') {
                    $this->language = 'en';
                    $this->extendedLanguage = 'fr';
                    unset($this->explodedRequestURI[$lastIdx]);
                }
                elseif ($len>1) {
                    $lastIdx=$len-2;
                    if ($this->explodedRequestURI[$lastIdx]==='en') {
                        $this->language='en';  
                        $this->explodedRequestURI[$lastIdx]= $this->explodedRequestURI[$len-1];
                        unset($this->explodedRequestURI[$len-1]);
                    }
                    elseif ($this->explodedRequestURI[$lastIdx]==='fr') {
                        $this->language = 'en';
                        $this->extendedLanguage = 'fr';
                        $this->explodedRequestURI[$lastIdx]= $this->explodedRequestURI[$len-1];
                        unset($this->explodedRequestURI[$len-1]);
                    }                    
                }
            }
            
            if ($___p) { $this->explodedRequestURI[\count($this->explodedRequestURI)]=$___p; }
        }        
        $this->uri = '/'.\implode('/', $this->explodedRequestURI);
        
        $_session_params['lang']=$this->language;
                
        if (isset($_SERVER['HTTP_REFERER']) && \preg_match('/translate\.google\.com/', $_SERVER['HTTP_REFERER'])) {
            $toLang=null;
            \preg_match('/&langpair\=[a-z]{2}(?:\||%7C)([a-z]{2})/', $_SERVER['HTTP_REFERER'], $toLang);
            if ($toLang && \count($toLang)>1) {
                $this->siteTranslate=$toLang[1];
            }
            else {
                \preg_match('/&tl\=([a-z]{2})/', $_SERVER['HTTP_REFERER'], $toLang);
                if ($toLang && \count($toLang)>1) {
                    $this->siteTranslate=$toLang[1];
                }
            }
        }                   

        if (\preg_match('/\/(?:houses|villas)(?:\/|$)/i', $this->uri)) {
            $this->uri = \preg_replace('/\/(?:houses|villas)(\/|$)/','/villas-and-houses$1',$this->uri);
            if ($this->uri[\strlen($this->uri)-1]!='/') {
                $this->uri .= '/';
            }
            $_SESSION['_u']['params'] = $_session_params;
            $this->redirect($this->uri, 301);
        }
        
        if (\substr($this->uri, -10)=='/index.php') {
            $this->uri = \substr($this->uri, 0, strlen ($this->uri)-10);
        }
                    
        $_args = \explode('&', $_SERVER['QUERY_STRING']);
                        
        $count = \count($_args);
        for ($i=0; $i<$count; ++$i) {
            $node = \explode('=', $_args[$i]);
            if (!empty($node[1]) && \array_key_exists($node[0], $this->params)) {                
                $this->params[$node[0]] = \trim(\urldecode($node[1]));
                
                switch ($node[0]) {
                    case 'rss':
                        $this->params['rss'] = TRUE;
                        $this->force_search=true;
                        break;
                    case 'id':
                        //workaround for youtube upload callback
                        if (!isset($_GET['status'])) {
                            $this->id = \intval($node[1]);                        
                            $ad_url = $this->getAdURI($this->id);
                            $this->module = 'detail';
                            $this->http_status = 410;
                            \header('HTTP/1.1 410 Gone');
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
        
        if (isset($_GET['aid']) && isset($_GET['q'])) {
            $this->force_search=true;
        }
            
        if ($this->params['start'] && !\array_key_exists('start', $_GET)) {
            $_GET['start']=$this->params['start'];
        }
             
        $_args = \explode('/', $this->uri);
        if (!empty($_args)) {
            $idx=\count($_args)-1;
                
            if (\is_numeric($_args[$idx])) {
                $this->id=(int)$_args[$idx];
                $rpos=\strrpos($this->uri, '/');
                if ($rpos) {
                    $this->uri = \substr($this->uri, 0, $rpos);
                }
                
                if ($this->id<1000000000) {
                    if ($this->id>1000) {
                        $this->module='detail';
                        $ad_url = $this->getAdURI($this->id);
                        if ($ad_url!==$this->getLanguagePath($this->config->host.$this->uri).$this->id.'/') {
                            if ($ad_url!=$this->config->baseURL) {
                                $_SESSION['_u']['params'] = $_session_params;
                                $this->redirect($ad_url, 301);
                            } 
                            else {
                                $this->id=0;
                                $this->http_status=410;
                                $this->module = 'notfound';
                            }
                        }
                        $idx=-1;
                    } 
                    else {
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
            
            if ($idx>=0 && isset($_args[1]) && \is_numeric($_args[1])) {
                $id=(int)$_args[1];
                if ($id>2000000000) {
                    $this->watchId=$id-$this->baseUserId;
                    $this->module='search';
                    $this->force_search=true;
                    $this->id=0;
                    unset($_args[0]);
                    $this->uri = \substr($this->uri, (strlen($id)+1));
                }
                elseif ($id>1000000000) {//partner id handling
                    $this->userId=$id-$this->basePartnerId;
                    $this->module='search';
                    $this->force_search=true;
                    $this->id=0;
                    unset($_args[0]);
                    $this->uri = \substr($this->uri, (strlen($id)+1));
                }
            }
            
            if ($idx>1 && \substr($_args[$idx],0,2)=="q-") {
                $tag_info = \explode("-", $_args[$idx]);
                if (\count($tag_info)==3 && \is_numeric($tag_info[1]) && \is_numeric($tag_info[2])) {
                    $this->params['tag_id']=$tag_info[1];
                    if ($_args[ $tag_info[2] ]=='nissan-nissan-z') {
                        $_args[ $tag_info[2] ] = 'nissan';
                    }
                    else {
                        $_args[ $tag_info[2] ] = \substr($_args[$tag_info[2]], 0, \strrpos($_args[$tag_info[2]], "-"));
                    }
                    
                    unset($_args[$idx]);                
                    $tmp=array();
                    foreach ($_args as $arg) {
                        $tmp[]=$arg;
                    }
                    $_args=$tmp;                    
                    
                    $this->uri = \implode("/", $_args);
                }
            }
            elseif ($idx>1 && \substr($_args[$idx],0,2)=="c-") {
                $tag_info = \explode("-", $_args[$idx]);
                
                if (\count($tag_info)==3 && \is_numeric($tag_info[1]) && \is_numeric($tag_info[2])) {
                    $this->params['loc_id']=$tag_info[1];
                    unset($_args[$tag_info[2]]);
                    unset($_args[$idx]);
                    $tmp=array();
                    foreach ($_args as $arg) { $tmp[]=$arg; }
                    $_args=$tmp;
                    $this->uri=  \implode("/", $_args);
                }
            }
        }    
                

        if ((!$this->internal_referer || \strstr($this->referer, '/oauth/')) &&
            empty($_GET) && ($this->uri===''||$this->uri==='/') && 
            !$this->userId && !$this->watchId) {
            $this->countries = $this->db->getCountriesData($this->language);
            error_log('internal');
            
            if (isset($_session_params['visit']) && isset($_session_params['user_country'])) { 
                if (!$this->countryId && \strpos($this->config->baseURL, 'dv.mourjan.com')===false && \strpos($this->config->baseURL, 'h1.mourjan.com')===false) {  
                    $curi = $this->uri;
                    if (isset($this->cookie->cn) && $this->cookie->cn) {
                        if (!isset($_GET['app']) && isset($this->cookie->lg) && \in_array($this->cookie->lg, ['ar','en'])) {
                            $this->language=$_session_params['lang']=$this->cookie->lg;                            
                        }

                        $this->countryId=$_session_params['country']=$this->cookie->cn;
                        $this->cityId=$_session_params['city']=0;
                        $this->uri='/'. $this->countries[$this->cookie->cn]['uri'];
                        if(isset($this->cookie->c) && $this->cookie->c) {
                            if (isset($this->countries[$this->countryId]['cities'][$this->cookie->c])) {
                                $this->uri.='/'.$this->countries[$this->countryId]['cities'][$this->cookie->c]['uri'];
                                $this->cityId=$_session_params['city']=$this->cookie->c;
                            }
                        }
                        
                        if ($this->uri!==$curi) {
                            $_SESSION['_u']['params'] = $_session_params;
                            $this->redirect($this->config->baseURL.$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->language != 'ar' ? $this->language .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    } 
                    else {
                        $_SESSION['_u']['params'] = $_session_params;
                        $this->setGeoByIp();
                        if ($this->uri!=$curi) {                            
                            $this->redirect($this->config->baseURL.$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->language != 'ar' ? $this->language .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    }                    
                }
            }    
                    
            if (!isset($_session_params['visit'])) {
                $current_uri = $this->uri;
                $_SESSION['_u']['params'] = $_session_params;
                $this->setGeoByIp();
                if ($current_uri!==$this->uri) {                    
                    $this->redirect($this->config->baseURL.$this->uri.( \strlen($this->uri)>1 && (\substr($this->uri, -1)==='/') ? '':'/' ).($this->language!=='ar' ? $this->language .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                }
                
                if ($this->countryId===0) {
                    if (isset($this->cookie->cn) && $this->cookie->cn) {
                        $this->countryId=$_session_params['country']=$this->cookie->cn;
                        $this->cityId=$_session_params['city']=0;
                        $this->uri='/'. $this->countries[$this->cookie->cn]['uri'];
                        if (isset($this->cookie->c) && $this->cookie->c) {
                            if (isset($this->countries[$this->countryId]['cities'][$this->cookie->c])) {
                                $this->uri.='/'.$this->countries[$this->countryId]['cities'][$this->cookie->c]['uri'];
                                $this->cityId=$_session_params['city']=$this->cookie->c;
                            }
                        }
                        if ($current_uri!=$this->uri) {
                            $_SESSION['_u']['params'] = $_session_params;
                            $this->redirect($this->config->baseURL.$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->language != 'ar' ? $this->language .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
                        }
                    }
                }
            }                            
        }

        if ($this->countryId===0 && isset($this->cookie->cn) && $this->cookie->cn>0) {
            $_SESSION['_u']['params']['country']=$this->cookie->cn;
            $_session_params=$_SESSION['_u']['params'];           
        }
        
        
        if (!isset($_GET['app']) && !isset($_session_params['user_country'])) {
            $geo = $this->getIpLocation();
            if (isset($geo['country'])) {
                    $country_code=\strtolower(trim($geo['country']['iso_code']));
                    $_session_params['user_country']=$country_code;
                    $_session_params['latitude'] = isset($geo['location']['latitude']) ? $geo['location']['latitude'] : 0.0;
                    $_session_params['longitude'] = isset($geo['location']['longitude']) ? $geo['location']['longitude'] : 0.0;
            }
            else {
                $_session_params['user_country']='';
            }
        }
        
        if (!isset($_session_params['lang'])) {
            if (!isset($_GET['app']) && isset($this->cookie->lg) && \in_array($this->cookie->lg, ['ar','en'])) {
                $this->language=$_session_params['lang']=$this->cookie->lg;
                $_SESSION['_u']['params'] = $_session_params;
                $this->redirect($this->config->baseURL.$this->uri.( \strlen($this->uri)>1 && (\substr($this->uri, -1)=='/') ? '':'/' ).$this->getLanguagePath().($this->id ? $this->id.'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
            } 
            else {
                $_session_params['lang']=$this->language;
            }
        } 
        else {
            $_session_params['lang']=$this->language;
        }
        
        $_SESSION['_u']['params'] = $_session_params;
        
        //error_log(__CLASS__.'.'.__FUNCTION__."\n".\json_encode($_session_params, JSON_PRETTY_PRINT));
        //error_log('cookie '. json_encode($this->cookie));
    }
    
    
    public function user() : \User {
        return $this->user;
    }
    
    
    public function setUser(\User $kUser) : void {
        $this->user = $kUser;
    }
    
    
    public function logger() : \Core\Lib\Logger {
        return $this->logger;
    }
    
    
    public function setLogger(\Core\Lib\Logger $klogger) : void {
        $this->logger = $klogger;  
    }
    
    
    public function getLanguagePath(string $url='') : string {
        if ($url) {
            if (\substr($url, -1)!=='/') {
                $url.='/';
            }
            if ($this->language!=='ar') {
                $url.=$this->language.'/';
            }
            return $url;
        }
        return ($this->language==='ar'?'':$this->language).'/';
    }
    
    
    public function isArabic() : bool {
        return ($this->language==='ar');
    }
    
    
    public function getIpLocation($ip=NULL) {
        if (empty($ip)) {
            if (\array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $this->client_ip = $ip;
        }
        
        $databaseFile = '/home/db/GeoLite2-City.mmdb';
        $reader = new \MaxMind\Db\Reader($databaseFile);
        $ips = \explode(',', $ip);
        foreach ($ips as $addr) {
            $geo = $reader->get(trim($addr));
            if (isset($geo['country'])) break;
        }
        $reader->close();
        
        //error_log(json_encode($geo));
        //$this->isBehindProxy();
        return $geo;
    }
	
    
    public function config() : \Config {
        return $this->config;
    }
	
    
    private function setGeoByIp() : void {
        $geo = $this->getIpLocation();
			
        $_session_params = $_SESSION['_u']['params'];
        error_log(__FUNCTION__ . ' ' . \json_encode($_session_params));
        
        if (!empty($geo) && isset($geo['country']['iso_code'])) {
            $country_code=\strtolower(\trim($geo['country']['iso_code']));
            $_session_params['user_country']=$country_code;
            $_session_params['latitude'] = $geo['location']['latitude'] ?? 0.0;
            $_session_params['longitude'] = $geo['location']['longitude'] ?? 0.0;
                
            if (\array_key_exists($country_code, $this->config->get('iso_countries'))) {
                $this->countryId = $this->config->get('iso_countries')[$country_code];                    
                    
                $this->uri='/'.$country_code; 
                $_session_params['city']=0;
                
                if (\count($this->countries[$this->countryId]['cities']) > 1) {
                    $this->cache();
                    $default_city = -1;
                    $min = PHP_INT_MAX;
                    foreach ($this->countries[$this->countryId]['cities'] as $city_id=>$city) {
                        $dist = $this->distance($city['latitude'], $city['longitude']);
                        if ($dist<$min) {
                            $default_city=$city_id;
                            $min=$dist;
                        }
                    }                        
                    if ($default_city>0) {
                        $this->uri.='/'.$this->countries[$this->countryId]['cities'][$default_city]['uri'];
                        $_session_params['city']=$default_city;
                    }
                }
                
                $_session_params['country'] = $this->countryId;
                    
                if(isset($this->cookie->lg) && in_array($this->cookie->lg,array('ar','en'))) {
                    $this->language=$_session_params['lang']=$this->cookie->lg;                        
                }
                else {
                    $_session_params['lang']=$this->language;
                }                    
                //$this->redirect($this->cfg['url_base'].$this->uri.( strlen($this->uri)>1 && (substr($this->uri, -1)=='/') ? '':'/' ).($this->language != 'ar' ? $this->language .'/':'').(isset($this->params['q']) && $this->params['q'] ? '?q='.$this->params['q']:'') );
            } 
            else {
                $_session_params['country'] = $this->countryId;
                $_session_params['city']=0;
            }
        }
        else {
            $_session_params['user_country']='';
            $_session_params['latitude'] = 0.0;
            $_session_params['longitude'] = 0.0;
        }        
        $_SESSION['_u']['params'] = $_session_params;
    }
   

    function __destruct() {
    }
    
    
    public function database() : DB {
        return $this->db;
    }
    
    
    function getAdURI($ad_id=0) {
        $result = '';
        $this->config->incModelFile('Classifieds');
        $ad_class = new Classifieds($this->db);
        $row = $ad_class->getById($ad_id);
        
        if (!empty($row)) {
            if ($this->language==='ar') {
                $result = sprintf($row[18], '', $ad_id);
            }
            else {
                $result = sprintf($row[18], $this->language.'/', $ad_id);
            }
            $this->countryId = (int)$row[4];
            $this->cityId = (int)$row[5];
            $this->rootId = (int)$row[8];
            $this->sectionId = (int)$row[12];
            $this->purposeId = (int)$row[7];
        } 
        else {
            $url_codes = $this->FetchUrl();
            if ($url_codes) {
                $result = $this->uri.'/'.$this->getLanguagePath();
            } 
            else {
                $_args = explode('/', $this->uri);
                unset($_args[2]);
                $sss=  implode('/', $_args);
                $url_codes = $this->FetchUrl($sss);
                if ($url_codes) {
                    $result = $sss.'/'. $this->getLanguagePath();
                }
            }
        }

        return $this->config->baseURL.$result;
    }
    
    
    function redirect(string $url, int $status=301) : void {
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
        exit(0);
    }
    
    
    function cacheHeaders($lastModifiedDate) {
//        
//        if ($this->cfg['modules'][$this->module][1]==0) return;
//        if (!$this->cfg['site_production']) return;
//        if(isset($_GET['provider']))return;
//        //header("X-Mourjan-ID: ".$_SESSION['info']['id'] );
//        //error_log($_SESSION['info']['id']);
//        $SESSION = $_SESSION['_u'];
//        if ( isset($SESSION['info']['id']) && $SESSION['info']['id'] && $this->module!='homescreen') return;
//        if ($lastModifiedDate) {
//            $etag = isset($SESSION['params']['etag']) && isset($SESSION['params']['mobile']) && $SESSION['params']['mobile'] ? $SESSION['params']['etag'] : $this->cfg['etag'];
//            //$ifModifiedSince=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
//                           
//            $etagFile = sprintf('%x%x-%x-%x-%x-%x-%x-%x-%x', $this->isMobile, $etag,
//                $this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->purposeId, $this->id, 
//                str_pad($lastModifiedDate, 16, '0'));
//
//            $etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
//
//            header("Last-Modified: ". gmdate("D, d M Y H:i:s", $lastModifiedDate)." GMT");
//            header("Etag: {$etagFile}");
//            header("Cache-Control: public, must-revalidate");
//            if ($etagHeader) {
//                if ($etagHeader===$etagFile) {
//                    include_once $this->cfg['dir']. '/core/layout/Site.php';
//                    $site = new \Site($this);
//                    $site->handleCacheActions();
//
//                    header("HTTP/1.1 304 Not Modified");
//                    exit;               
//                } 
//                else {
//                    return;
//                }
//            }
//
//            //check if page has changed. If not, send 304 and exit            
//            if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])>=$lastModifiedDate) {          
//                include_once $this->cfg['dir']. '/core/layout/Site.php';
//                $site = new \Site($this);
//                $site->handleCacheActions();
//                header("HTTP/1.1 304 Not Modified");
//               exit;      
//            }
//        }
    }
    
        
    function cache($force=false) : void {
        if (empty($this->language)) {
            $this->language = 'en';
        }

        if ($this->module=='detail' && empty($this->countryId)) {
            $cc=substr($this->uri, 1, 2);
            if ($cc && array_key_exists($cc, $this->config->get('iso_countries'))) {
                $this->countryId = $this->config->get('iso_countries')[$cc];
            }
        }

        if ($force) {
            $result = array();
            $this->countries = NULL;
            $this->cities = NULL;
            $this->publications = NULL;
            $this->sections = NULL;
            $this->purposes = NULL;
        } 
        else {
            $countries_label = "country-data-{$this->language}-".Db::$SectionsVersion;
            $roots_label = "root-data-{$this->countryId}-{$this->cityId}-{$this->language}-".Db::$SectionsVersion;
            
            $result = $this->db->getCache()->getMulti(['roots', 'sections', 'purposes', 'cities-dictionary', 'last', $countries_label, $roots_label]); 
            if (isset($result['cities-dictionary'])) {
                $this->cities = $result['cities-dictionary'];
            }
            if (isset($result['roots'])) {
                $this->roots = $result['roots'];
            }
            if (isset($result['sections'])) { 
                $this->sections = $result['sections'];
            }
            if (isset($result['purposes'])) {
                $this->purposes = $result['purposes'];
            }                                   
            if (isset($result[$countries_label])) {
                $this->countries = $result[$countries_label];
            }
            
            if (isset($result[$roots_label])) {
                $this->pageRoots = $result[$roots_label];
            }
            
            if (isset($result['last'])) $this->last_modified = $result['last'][1][2]; 
        }
        
        if (!$this->last_modified) {
            $q = $this->db->queryCacheResultSimpleArray('last',
                "SELECT 1, r.MAX_ID, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', r.LAST_UPDATE)
                FROM SPHINX r
                where r.SPHINX_NAME='ACTIVE'", null, 0, $this->cfg['ttl_medium'], $force);
            $this->last_modified = $q[1][2];
        }
    
        if ($this->countries===NULL || empty($this->countries)) {
            $this->countries = $this->db->getCountriesData($this->language);
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
            $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->language);
        } 
        else {
            if ($this->cityId && !isset($this->countries[$this->countryId]['cities'][$this->cityId])) {
                $this->cityId=0;
                $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->language);
            }
        }
        
        if ($this->pageRoots==NULL) {
            $this->pageRoots = $this->db->getRootsData($this->countryId, $this->cityId, $this->language);
        }
        
        if ($this->module=='search' || $this->module=='detail' || $this->module=='cache') {
            $this->cacheExtension($force);
        }
    }

    
    function cacheExtension($force=false) {       
        if ($this->rootId && $this->pageSections==NULL) {
            $this->pageSections = $this->db->getSectionsData($this->countryId, $this->cityId, $this->rootId, $this->language);
        }
            
        if ($this->sectionId && isset($this->pageSections[$this->sectionId])) {
            $this->pagePurposes = $this->pageSections[$this->sectionId]['purposes'];
                                
            if ($this->naming==NULL) {
                $this->naming = $this->db->queryCacheResultSimpleArray(
                    "naming_{$this->language}_{$this->countryId}_{$this->sectionId}",
                    "select single, plural, description
                    from naming
                    where naming.TYPE_ID=2
                    and naming.ORIGIN_ID={$this->sectionId}
                    and naming.LANG='{$this->language}'
                    and (country_id={$this->countryId} or country_id=0)
                    order by naming.COUNTRY_ID desc",
                    null, -1, $this->config()->get('ttl_long'), $force);
            }
                                
        } 
        else {
            if ($this->rootId && isset($this->pageRoots[$this->rootId])) {
                $this->pagePurposes = $this->pageRoots[$this->rootId]['purposes'];
            }
            else {
                $this->pagePurposes = $this->db->getPurpusesData($this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->language);
            }
        }
    }
    
    
    function FetchUrl($url=NULL) {
        if ($url==NULL) {
            $url = $this->uri;
        }
        $result=$this->db->queryCacheResultSimpleArray($url, "
                SELECT r.COUNTRY_ID, r.CITY_ID, r.ROOT_ID, r.SECTION_ID, r.PURPOSE_ID, trim(r.MODULE),
                iif(r.TITLE_EN>'', r.TITLE_EN, SUBSTRING(r.title from POSITION(ascii_char(9) , r.title) for 128)),
                iif(r.TITLE_AR>'', r.title_ar, SUBSTRING(r.title from 1 for POSITION(ascii_char(9), r.title))),
                r.REFERENCE, r.REDIRECT_TO
                FROM URI r
                where r.PATH=?
                and r.BLOCKED=0
                ", [$url], -1, 0, FALSE, TRUE);
        return $result;
    }
    
    
    function getCanonicalURL() {
        if ($this->module==='index') {
            $path=$this->getUrl($this->countryId, $this->cityId);
            $this->canonical = 'https://www.mourjan.com'.$path;        
        }
        else {
            $this->canonical = FALSE;
        }
        
        return $this->canonical;               
    }


    function decode() : void {        
        if ($this->id) {
            $this->module='detail';
        } 
        else {
            if ($this->uri) {
                $url_codes = $this->FetchUrl($this->uri);
                if ($url_codes) {
                    $this->countryId = $url_codes[0];
                    $this->cityId = $url_codes[1];
                    $this->rootId = $url_codes[2];
                    $this->sectionId = $url_codes[3];
                    $this->purposeId = $url_codes[4];
                    $this->module = $url_codes[5];
                    $this->pageTitle['en'] = trim($url_codes[6]);
                    $this->pageTitle['ar'] = trim($url_codes[7]);
                    if (!$this->userId) {
                        $this->userId = $url_codes[8];
                    }
                    
                    if ($this->module=='cache' || ($this->module=='watchlist' && $this->params['rss'])) {
                        $this->force_search=false;
                    }

                    if ($this->force_search)  {
                        $this->module='search';
                    } 
                    elseif ($this->isMobile && $this->rootId>0 && $this->sectionId===0) {
                        $this->module='index';
                    } 
                    elseif ($this->purposeId>0 || $this->rootId>0 || ($this->force_search)) {
                        $this->module='search';
                    }

                    if (($this->module==='search' || $this->module==='index') &&
                        $this->purposeId==0 &&
                        $this->rootId==0 &&
                        $this->sectionId==0 &&
                        empty($this->params['q']) &&
                        $this->params['start']>0) {
                        header('HTTP/1.1 410 Gone');
                        $this->http_status=410;
                    }
                    
                    if (isset($url_codes[9]) && !empty($url_codes[9])) {
                        $this->redirect($url_codes[9], 301);
                    }
                } 
                else {
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
            } 
            else {
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
        
        $this->getCanonicalURL();        
    }
    
    
    function encodeCurrent($a=0) : string {
        return $this->encode($this->countryId, $this->cityId, $this->rootId, $this->sectionId, $this->purposeId, $a);
    }
    
    
    function encode($cn=0, $c=0, $ro=0, $se=0, $pu=0, $a=0) : string {
        $result = $this->config->baseURL;
        if ($ro===4) { $pu=0; }
        $rs = $this->db->queryResultArray("select path||'/' PATH from uri where country_id={$cn} and city_id={$c} and root_id={$ro} and section_id={$se} and purpose_id={$pu} and lang='{$this->language}'");
        if (!empty($rs)) {            
            $result.= $rs[0]['PATH'];
        } 
        return $result;
    }
    
    
    function getURL(int $cn=0, int $c=0, int $ro=0, int $se=0, int $pu=0, bool $appendLanguage=true) : string {
        $result = '/';

        if ($cn) {
            if (isset($this->countries[$cn])) {
                $result.=$this->countries[$cn]['uri'].'/';
            }
            else {
                $cn=0;
                $c=0;
            }
        }

        if ($c && isset($this->cities[$c])) {
            if ($cn===0) {
                $cn = $this->cities[$c][4];
                if (isset($this->countries[$cn])) {
                    $result.=$this->countries[$cn]['uri'].'/'.$this->cities[$c][3].'/';
                }
            } 
            else {
                $result.=$this->cities[$c][3].'/';
            }
        }
       
        if ($se && isset($this->sections[$se])) {
            $result.=$this->sections[$se][3].'/';
        }
        else if($ro) {
            $result.=$this->roots[$ro][3].'/';
        }
        
        if ($ro!==4 && $pu && isset($this->purposes[$pu])) {
            $result.=$this->purposes[$pu][3].'/';
        }
        
        if ($appendLanguage && $this->language!='ar') {
            $result.=$this->language.'/';
        }
            
        return $result;        
    }

    
    public function countryExists(int $country_id) : bool {
        return isset($this->countries[$country_id]);
    }
    
    
    public function getRootId(int $section_id) : int {
        return \intval($this->sections[$section_id][4]);
    }
    
    
    public function getCountryId(int $city_id) : int {
        return \intval($this->cities[$city_id][4]);
    }
    
    function close() : void {
        if ($this->db) { $this->db->close(); }
    }
    
    
    function isBot( $http_user_agent=null, $ip=null ) {
        if($ip==null) { 
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $bots = [
                    ['name'=>'Google', 'bot'=>'http://www.google.com/bot.html', 'domain'=>'googlebot.com'],
                    ['name'=>'Media-google', 'bot'=>'Mediapartners-Google', 'domain'=>'googlebot.com'],
                    ['name'=>'Baidu', 'bot'=>'http://www.baidu.com/search/spider.htm', 'domain'=>'crawl.baidu.com'],
                    ['name'=>'Yahoo', 'bot'=>'http://help.yahoo.com/help/us/ysearch/slurp', 'domain'=>'crawl.yahoo.net'],
                    ['name'=>'Msn', 'bot'=>'http://search.msn.com/msnbot.htm', 'domain'=>'search.msn.com'],
                    ['name'=>'Teoma', 'bot'=>'http://about.ask.com/en/docs/about/webmasters.shtml', 'domain'=>'ask.com'],
                    ['name'=>'Alexa', 'bot'=>'Alexa Verification Agent', 'domain'=>'amazonaws.com'],
                    ['name'=>'TweetmemeBot', 'bot'=>'TweetmemeBot', 'domain'=>'favsys.net']
                    ];

        $http_user_agent = strtolower( $http_user_agent ); 
        foreach( $bots as $bot ) {      
            if( stripos( $http_user_agent, $bot['bot'] ) !== false ) {            
                $name = gethostbyaddr( $ip );
                $host = gethostbyname( $bot['domain'] );
                
                if(strpos( $name, $bot['domain'])) {
                    if ($host==$ip) { 
                        return $bot['name'];                         
                    }
                    else {
                        return false;
                    }
                }            
            }
        }
        return null;
    }

    
    function isBehindProxy() {
        $HTTP_proxy_headers = [
            'HTTP_VIA',
            'VIA',
            'Proxy-Connection',
            'HTTP_X_FORWARDED_FOR',  
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'X-PROXY-ID',
            'MT-PROXY-ID',
            'X-TINYPROXY',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT-IP',
            'CLIENT_IP',
            'PROXY-AGENT',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'FORWARDED_FOR_IP',
            'HTTP_PROXY_CONNECTION'];
        
        foreach ($HTTP_proxy_headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                error_log("Proxy {$this->client_ip}, Header {$header}, Please disable your proxy connection!");
                return true;
            }
	}
        return FALSE;
    }
    
    
    function isProxyIP(string $ip) {
        $proxy_ports = [80, 81, 8080, 443, 1080, 6588, 3128];
        foreach ($proxy_ports as $port) {
            if(@fsockopen($ip??$_SERVER['REMOTE_ADDR'], $port, $errno, $errstr, 5)) {
                error_log("Port {$port}. Please disable your proxy connection!");
            }
        }
    }
    
    
    private function checkBot($domain, $ip) {    
        $name = gethostbyaddr( $ip );
        $host = gethostbyname( $name );
        if (strpos($name, $domain)) {        
            if ($host==$ip) { return true; }
        }
        return false;
    }
    
    
    function distance($lat, $lon, $ulat=0, $ulon=0) {
        $_session_params = $_SESSION['_u']['params'];
        if (!$ulat) { $ulat = $_session_params['latitude'] ?? 0.0; }
        if (!$ulon) { $ulon = $_session_params['longitude'] ?? 0.0; }
        $theta = $ulon - $lon;
        $dist = rad2deg(acos(sin(deg2rad($ulat)) * sin(deg2rad($lat)) +  cos(deg2rad($ulat)) * cos(deg2rad($lat)) * cos(deg2rad($theta))));
        $miles = $dist * 60 * 1.1515;
        return $miles;
    }

    
    public static function getPositiveVariable($variable, int $type=-1) : int {
        if ($type<0) {
            return filter_var($variable, FILTER_VALIDATE_INT, static::POSITIVE_VALUE);
        }
        else {
            return filter_input( $type, $variable, FILTER_VALIDATE_INT, static::POSITIVE_VALUE);            
        }
    }
    
    
   
    /**
     * This function takes a css-string and compresses it, removing
     * unneccessary whitespace, colons, removing unneccessary px/em
     * declarations etc.
     *
     * @param string $css
     * @return string compressed css content
     * @author Steffen Becker
     */
    public static function minifyCss($css) {
        // some of the following functions to minimize the css-output are directly taken
        // from the awesome CSS JS Booster: https://github.com/Schepp/CSS-JS-Booster
        // all credits to Christian Schaefer: http://twitter.com/derSchepp
        // remove comments
        $css = \preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // backup values within single or double quotes
        \preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, \PREG_PATTERN_ORDER);
        for ($i=0; $i < \count($hit[1]); $i++) {
            $css = \str_replace($hit[1][$i], '##########' . $i . '##########', $css);
        }
        // remove traling semicolon of selector's last property
        $css = \preg_replace('/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css);
        // remove any whitespace between semicolon and property-name
        $css = \preg_replace('/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css);
        // remove any whitespace surrounding property-colon
        $css = \preg_replace('/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css);
        // remove any whitespace surrounding selector-comma
        $css = \preg_replace('/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css);
        // remove any whitespace surrounding opening parenthesis
        $css = \preg_replace('/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css);
        // remove any whitespace between numbers and units
        $css = \preg_replace('/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css);
        // shorten zero-values
        $css = \preg_replace('/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css);
        // constrain multiple whitespaces
        $css = \preg_replace('/\p{Zs}+/ims',' ', $css);
        // remove newlines
        $css = \str_replace(array("\r\n", "\r", "\n"), '', $css);
        // Restore backupped values within single or double quotes
        for ($i=0; $i < \count($hit[1]); $i++) {
            $css = \str_replace('##########' . $i . '##########', $hit[1][$i], $css);
        }
        return $css;
    }
    
}
