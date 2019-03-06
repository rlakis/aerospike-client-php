<?php

\Config::instance()->incModelFile('NoSQL')->incModelFile('Classifieds')
        ->incModelFile('User')->incLibFile('SphinxQL');

        
use Core\Model\Router;
use Core\Model\Classifieds;
use Core\Model\NoSQL;
use Core\Lib\SphinxQL;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;


class Site {
    public $user;
    var $lang=array(),$xss_hash='',$watchInfo=false; 
    var $userFavorites,$pageUserId=0;
    var $num=10;
    var $isMobile=false;
    var $isMobileAd=false;
    var $lnIndex=0;
    var $channelId=0;
    private $router;
    var $sortingMode = 0;
    var $langSortingMode = 0;
    var $publisherTypeSorting = 0;


    function __construct() {
        global $argc;        
        $this->router = Router::getInstance();
        if ($this->router->language=='en') {
            $this->lnIndex=1;
        }
        if (isset($argc)) { return; }
        
        $this->initSphinx();
        
        $this->user=new User(/*$this->router->db, $router->cfg, */$this);
        if (!isset($this->user->params['list_lang'])) {
            $this->langSortingMode = -1;
        }
        if (!isset($this->user->params['list_publisher'])) {
            $this->publisherTypeSorting = 0;
        }
        $this->classifieds = new Classifieds($this->router->db);
    }

    
    public function __destruct() {
    }
    
    
    public function router() : Router {
        return $this->router;
    }
    
    
    public function user() : User {
        return $this->user;
    }
    
    
    function checkUserGeo() {
        $geo = $this->router->getIpLocation();
        if (isset($geo['country'])) {
            $country_code=strtolower(trim($geo['country']['iso_code']));
            $this->user->params['user_country']=$country_code;
            if(strlen($country_code)!=2)$this->user->params['user_country']='';
        }
        else {
            $this->user->params['user_country']='';
        }
        $this->user->update();
    }
    
    
    function isRTL($text) {
        $rtlChars = preg_replace('/[^\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        $ltrChars = preg_replace('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        if (strlen($rtlChars) > (strlen($ltrChars)*0.5)) {
            return true;
        }
        else {
            return false;
        }
        return false;
    }

    
    function BuildExcerpts($text, $length = 0, $separator = '...') {
        if ($length) {
            $str_len = mb_strlen($text, 'UTF-8');
            if ($str_len > $length) {                
                $text = trim(preg_replace('/\x{200B}.*/u', '', $text));
                $text = trim(preg_replace('/[\-+=<>\\&:;,.]$/', '', $text));
                
                $str_len = mb_strlen($text, 'UTF-8');
                if ($str_len > $length) {
                    $text = mb_substr($text, 0, $length, 'UTF-8');
                    $lastSpace = strrpos($text, ' ');
                    $text   = substr($text, 0, $lastSpace);
                    $text = trim(preg_replace('/[\-+=<>\\&:;,.]$/', '', $text));
                }
                
                $text = trim($text).$separator;
            }
        }
        return $text;
    }
    
    
    function findFlashUrl($entry) {
        foreach ($entry->mediaGroup->content as $content) {
            if ($content->type === 'application/x-shockwave-flash') {
                return $content->url;
            }
        }
        return null;
    }

    
    function getGetInt(string $parameter, int $default=0) : int {
        $result = filter_input(INPUT_GET, $parameter, FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>$default]]);
        return $result;
    }
    
    
    function getGetString(string $parameter, string $default='') : string {
        $result = filter_input(INPUT_GET, $parameter, FILTER_SANITIZE_STRING, ['options'=>['default'=>$default]]);
        return $result;
    }
    
    
    function get($str='', $type='', $ignoreCase=false) {
        if (!$ignoreCase)$str=strtolower($str);
        if (!isset($this->params['get'][$str])) {
            if (isset ($_GET[$str]))
                $this->params['get'][$str]=$_GET[$str];
            else $this->params['get'][$str]=false;
        }
        return $this->validate_type($this->params['get'][$str],$type);
    }


    function post($str='', $type='', $ignoreCase=false){
        if (!$ignoreCase)$str=strtolower($str);
        if (!isset($this->params['post'][$str])) {
            if (isset ($_POST[$str]))
                $this->params['post'][$str]=$this->validate_type($_POST[$str],$type);
            else $this->params['post'][$str]=false;
        }
        return $this->params['post'][$str];
    }
    

    function cookie($str='', $type='', $ignoreCase=false){
        if (!$ignoreCase)$str=strtolower($str);
        if (!isset($this->params['cookie'][$str])) {
            if (isset ($_COOKIE[$str]))
                $this->params['cookie'][$str]=$this->validate_type($_COOKIE[$str],$type);
            else $this->params['cookie'][$str]='';
        }
        return $this->params['cookie'][$str];
    }
    

    function load_lang($langArray, $langStr='') : void {
        if ($langStr=='') { $langStr=$this->router()->language; }
        if ($this->router->extendedLanguage)$langStr=$this->router->extendedLanguage;
        foreach ($langArray as $langFile) {
            include_once "{$this->router()->config()->baseDir}/core/lang/{$langFile}.php";
            $this->lang=array_merge($this->lang, $lang);
            if ($langStr!='en') {
                include_once "{$this->router()->config()->baseDir}/core/lang/{$langStr}/{$langFile}.php";
                $this->lang=array_merge($this->lang, $lang);
            }
        }
    }


    function validate_type($str, $type=''){
        if (is_null($str) || $str===false) {
            $str=false;
        }else {
            switch ($type) {
                case 'array':
                    if (is_numeric($str)) {
                        $str = array((int)abs($str));
                    }elseif (is_array($str)) {
                    }else {
                        $str = json_decode($str);
                        if(!is_array($str)) $str = false;
                    }
                    break;
                case 'boolean':
                    if ($str===false) $str=false;
                    else $str = $str ? 1 : 0;
                    break;
                case 'gender':
                    if ($str!='F' && $str!='M') $str=false;
                    break;
                case 'numeric':
                    if (is_numeric($str)) $str=(int)$str;
                    else $str=0;
                    break;
                case 'uint':
                    if (is_numeric($str)) {
                        $str = (int)abs($str);
                    }else {
                        $str=0;
                    }
                    break;
                default:
                    break;
            }
        }
        if ($str && !is_numeric($str) && $type!='array') $str = addslashes($str);

        return $str;
    }


    function checkNewUserContent($diff){
        return (isset($this->user->params['last_visit']) && $this->user->params['last_visit']<$diff);
    }

    
    function handleCacheActions() {
        if ($this->router->module=="search") {
            $this->user->params['search'] = array(
                'cn' =>   $this->router->countryId,
                'c' =>   $this->router->cityId,
                'ro' =>   $this->router->rootId,
                'se' =>   $this->router->sectionId,
                'pu' =>   $this->router->purposeId,
                'start'     =>  $this->router->params['start'],
                'q'     =>  $this->router->params['q']
            );
            $this->user->update();
        }
    }

    
    function formatSinceDate($seconds) {
        $stamp='';
        $seconds=time()-$seconds;
        if ($seconds<0) {
            return $stamp;
        }
        $days = floor($seconds/86400);
        $sinceText=$this->lang['since'].' ';
        $agoText=' '.$this->lang['ago'];
        if ($days) {
            if ($this->router->language=='ar') {
                $stamp=$sinceText.$this->formatPlural($days, 'day');
            }
            else {
                $stamp=$this->formatPlural($days, 'day').$agoText;
            }
        }
        else {
            $hours=floor($seconds/3600);
            if ($hours){
                if ($this->router->language=='ar') {
                    $stamp=$sinceText.$this->formatPlural($hours, 'hour');
                }else {
                    $stamp=$this->formatPlural($hours, 'hour').$agoText;
                }
            }else {
                $minutes=floor($seconds/60);
                if (!$minutes) $minutes=1;
                if ($this->router->language=='ar') {
                    $stamp=$sinceText.$this->formatPlural($minutes, 'minute');
                }else {
                    $stamp=$this->formatPlural($minutes, 'minute').$agoText;
                }
            }
        }
        return $stamp;
    }


    function formatPlural($number, $fieldName){
        $str='';
        if ($number==1) {
            if ($this->router->language=='ar') {
                $str=$this->lang[$fieldName];
            }else {
                $str='1 '.$this->lang[$fieldName];
            }
        }elseif ($number==2) {
            if ($this->router->language=='ar') {
                $str=$this->lang['2'.$fieldName];
            }else {
                $str='2 '.$this->lang['2'.$fieldName];
            }
        }elseif ($number>=3 && $number<11) {
            $str=$number.' '.$this->lang['3'.$fieldName];
        }else {
            $str=number_format($number).' '.$this->lang[$fieldName.'s'];
        }
        return $str;
    }

/*
    function shortenAd($ad, $max_len=0,$ellipsis=true){
        $len = (int)strlen($ad);
        if ($max_len > $len-20) $max_len = 0;
        if ($max_len) $limit = $max_len;
        else $limit = $len * 80 / 100;
        $ad = trim(preg_replace('/[^ ]+$/', '', substr($ad, 0, $limit)));
        $last_char = substr($ad, -1);
        if (in_array( $last_char, array(':', '-'))) {
            $ad=trim(substr($ad, 0, -1));
        }
        return $ad.($ellipsis ? '...':'');
    }
*/

    function initSphinx($forceInit=false) {
        if ($this->router->db->ql) {
            if ($forceInit) {
                $this->router->db->ql->resetFilters();               
            }
            return;
        }

        $this->router->db->ql = new SphinxQL($this->router->cfg['sphinxql'], $this->router->cfg['search_index']);        
    }
    
    
    function runQueries($queries, &$matches) {
        $this->router->db->ql->_batch=[];
        foreach ($queries as $row) {
            if (!$this->channelId || $row['ID']==$this->channelId) {
                $this->router->db->ql->resetFilters(true);
                if ($row['COUNTRY_ID'])
                    $this->router->db->ql->setFilter("country", $row['COUNTRY_ID']);
                if ($row['CITY_ID'])
                    $this->router->db->ql->setFilter("city", $row['CITY_ID']);
                if ($row['SECTION_ID'])
                    $this->router->db->ql->setFilter("section_id", $row['SECTION_ID']);
                if ($row['SECTION_TAG_ID'])
                    $this->router->db->ql->setFilter("section_tag_id", $row['SECTION_TAG_ID']);
                if ($row['LOCALITY_ID'])
                    $this->router->db->ql->setFilter("locality_id", $row['LOCALITY_ID']);
                if ($row['PURPOSE_ID'])
                    $this->router->db->ql->setFilter("purpose_id", $row['PURPOSE_ID']);

                $this->router->db->ql->setSelect('id, date_added, '.$row['ID'].' as info_id');
                $lastVisit = isset($this->user->params['last_visit']) && $this->user->params['last_visit'] ? $this->user->params['last_visit'] : time()-3600;
                $this->router->db->ql->setFilterRange('date_added',$lastVisit,time()+3600);
                $this->router->db->ql->setSortBy('date_added desc');
                $this->router->db->ql->SetLimits(0, 1000);
                $this->router->db->ql->addQuery($row['ID'], $row['QUERY_TERM'], TRUE);
                if ($this->channelId) break;
            }
        }
        $matches = $this->router->db->ql->executeBatch(TRUE);
    }
    
    
    function execute(bool $forceInit=false) {
        $offset = ($this->router()->params['start'] ? ($this->router()->params['start']-1) : 0) * $this->num;
        $this->initSphinx($forceInit);
        
        
        $rootId=$this->router->rootId;
        $q=preg_replace('/@/', '\@', $this->router->params['q']);
        
        if ($this->router()->watchId) {
            $this->searchResults=false;
            if (!$this->watchInfo || !count($this->watchInfo)) {
                return;
            }
            $results=array();
            
            $this->runQueries ($this->watchInfo, $results);
                        
            $matches = array('total_found'=>0,'matches'=>array(),'sub_total'=>array());
            if ($results && count($results)) { 
                if ($this->num>1) {
                    foreach ($results as $result) {
                        if (isset($result['matches'])) {
                            $count=count($result['matches']);
                            $matches['total_found']+=$count;
    
                            foreach ($result['matches'] as $id => $values) {
                                $matches['matches'][$id] = $values['date_added'];
                            }
                        }
                    }
                    krsort($matches['matches'], SORT_NUMERIC);
                    $matches['matches']=array_slice($matches['matches'], $offset, $this->num, TRUE);
                }
                else {
                    foreach ($results as $result) {
                        if (isset($result['matches'])) {
                            $count=count($result['matches']);
                            $matches['total_found']+=$count;
                            foreach ($result['matches'] as $id => $values) {
                                if (!isset($matches['sub_total'][$values['info_id']])) {
                                    $matches['sub_total'][$values['info_id']]=$count;
                                    $matches['matches'][$id] = $values['info_id'];
                                }
                            }
                        }
                    }
                }
            }

            if (!strstr($_SERVER["SCRIPT_FILENAME"], 'cronWatchMailer')) {
                $matches['matches']=  array_keys($matches['matches']);
            } 
            $this->searchResults['body']=$matches;

        } /* End of WatchId */
        else {
            if (($this->user->info['id'] || $this->pageUserId) && $this->userFavorites) {
                $id = $this->user->info['id'] ? $this->user->info['id'] : $this->pageUserId;                
                $this->router()->database()->index()
                        ->setSelect('id')
                        ->starred($id)
                        ->setSortBy('date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', '');
                $this->searchResults = $this->router()->database()->index()->executeBatch();
            } 
            else {
                $__compareID = $this->router()->getPositiveVariable('cmp', INPUT_GET);
                $__compareAID = $this->router()->getPositiveVariable('aid', INPUT_GET);
                
                $this->router()->database()->index()
                        ->region($this->router()->countryId, $this->router()->cityId)
                        ->id($__compareID, TRUE)
                        ->uid($this->router()->userId)                        
                        ->root($rootId)
                        ->section($this->router()->sectionId)
                        ->pupose($this->router->purposeId)
                        ->locality($this->localityId)
                        ->tag($this->extendedId)
                        ;
                                
                if ($this->publisherTypeSorting && in_array($rootId,[1,2,3]) && 
                    ($rootId!=3 || ($rootId==3 && $this->router->purposeId==3)) && 
                    ($rootId!=2 || ($rootId==2 && $this->router->purposeId==1)) ) {
                    $this->router()->database()->index()->publisherType($this->publisherTypeSorting == 1 ? 1 : 3);
                }
                
                switch ($this->langSortingMode) {
                    case 0:
                        $lng = '0 as lngmask';
                    case 1:
                        $lng = 'IF(rtl>0,0,1) as lngmask';
                        break;
                    case 2:
                        $lng = 'IF(rtl<>1,0,1) as lngmask';
                        break;
                    default:
                        $lng = ($this->router->language=='ar') ? 'IF(rtl>0,0,1) as lngmask' : 'IF(rtl<>1,0,1) as lngmask';
                        break;
                }
           

                $fields = "id, 0 as newad, date_added, {$lng}";
                if (($last_visited = $this->user()->getLastVisited())) {
                    $fields = "id, if(date_added>{$last_visited}, 1, 0) newad, date_added, {$lng}";                    
                } 
                
                $this->router()->database()->index()
                        ->setSelect($fields)
                        ->setSortBy($this->sortingMode ? 'lngmask asc, media desc, date_added desc' : 'lngmask asc, date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', $q);
                                
                if($this->router->module=='search' && !$this->userFavorites && !$this->router->watchId && !$this->router->userId) {
                    $this->getFeaturedAds();
                    $this->getMediaAds();
                }                
        
                                
                if($__compareAID) {                    
                    libFile('MCSaveHandler.php');
                    $handler = new MCSaveHandler($this->router()->cfg);
                    $this->searchResults = $handler->searchByAdId($__compareAID);
                }
                else {
                    $this->searchResults = $this->router()->database()->index()->executeBatchNew();   
                }                
            }       
        }
        
        
        //error_log("num {$this->num} vs ". count($this->searchResults['body']['matches']) ." out of {$this->searchResults['body']['total_found']}");
    }
       
    
    function getMediaAds() { 
        $this->router()->database()->index()->resetFilters()
                ->region($this->router()->countryId, $this->router()->cityId)
                ->media()
                ->setFilter(Core\Lib\SphinxQL::SECTION, [834,1079,1314,1112,617,513,293,298,343,350,515,539,108,84,85,114,214,116,123,125,135,144,279])
                ->rtl($this->router()->isArabic() ? [1,2] : [0,2])
                ->setSelect('id')
                ->setSortBy('rand()')
                ->setLimits(0, 4)
                ->addQuery('media');
    }


    function getFeaturedAds() {            
        $q = preg_replace('/@/', '\@', $this->router->params['q']);        
        $currentPage=($this->router->params['start']?$this->router->params['start']:1);
        
        // 1 - get top column paid ads related to query
        if ($this->router->module=='search' && $currentPage==1) {
            $publisher_type=0;
            if ($this->publisherTypeSorting && in_array($this->router->rootId,[1,2,3]) && 
               ($this->router->rootId!=3 || ($this->router->rootId==3 && $this->router->purposeId==3)) && 
               ($this->router->rootId!=2 || ($this->router->rootId==2 && $this->router->purposeId==1)) ) {
                $publisher_type = $this->publisherTypeSorting == 1 ? 1 : 3;
            }
            
            $this->router()->database()->index()->resetFilters()
                    ->featured()
                    ->region($this->router()->countryId, $this->router()->cityId)
                    ->root($this->router()->rootId)
                    ->section($this->router()->sectionId)
                    ->pupose($this->router()->purposeId)
                    ->locality($this->localityId)
                    ->tag($this->extendedId)
                    ->rtl($this->router()->isArabic() ? [1,2] : [0,2])
                    ->publisherType($publisher_type)
                    ->setSelect("id")
                    ->setSortBy("rand()")
                    ->setLimits(0, 40)
                    ->addQuery('zone1', $q);                       
        }
        
        // 2 - get side column paid ads related
        if (true) {
            $this->router()->database()->index()->resetFilters()
                ->region($this->router()->countryId, $this->router()->cityId)
                ->featured()
                ->root($this->router()->rootId)
                ->section($this->router()->sectionId)  
                ->setSelect("id")
                ->setSortBy("rand()")
                ->setLimits(0, 4)
                ->addQuery('zone2');                  
        }
        
        // 1 - get top column featured ads related to query
        if ($this->router->module=='search') {
            $publisher_type=0;
            if ($this->publisherTypeSorting && in_array($this->router()->rootId,[1,2,3])) {
                $publisher_type = $this->publisherTypeSorting == 1 ? 1 : 3;
            }
            
            $rtlFilter = [];
            switch ($this->langSortingMode) {
                case 1:
                    $rtlFilter=[1,2];
                    break;
                case 2:
                    $rtlFilter=[0,2];
                    break;

                default:
                    $rtlFilter=($this->router->language=='ar')?[1,2]:[0,2];
                    break;
            }
            
            $this->router()->database()->index()->resetFilters()
                ->region($this->router()->countryId, $this->router()->cityId)                
                ->root($this->router()->rootId)
                ->section($this->router()->sectionId)
                ->pupose($this->router()->purposeId)
                ->locality($this->localityId)
                ->tag($this->extendedId)
                ->rtl($rtlFilter)
                ->publisherType($publisher_type)
                ->exclude($this->user()->getFeature())
                ->SetSelect('id, (impressions + ((IF(now()-date_added<3600,20,impressions)/(now()-date_added))*(date_ended-now()))) as forecast')
                ->setSortBy('forecast asc')
                ->setLimits(0, 2)
                ->addQuery('zone0', $q);
                        
                                                
            //if(isset($this->user()->params['feature']) && count($this->user()->params['feature']))
            //{
            //    $this->router()->database()->index()->setFilterCondition('id', 'not in', $this->user()->params['feature']);
            //}
            
            //$this->router()->database()->index()
            //        ->rtl($rtlFilter)
            //        ->SetSelect('id, (impressions + ((IF(now()-date_added<3600,20,impressions)/(now()-date_added))*(date_ended-now()))) as forecast')
            //        ->setSortBy('forecast asc')
            //        ->setLimits(0, 2)
            //        ->addQuery('zone0', $q);            
        }                      
    }
    
    
    function isEmail($email){
        if(preg_match('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)){
            return true;
        }
        return false;
    }

    
    function zammad($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0) : int {
        $client = new Client([
            'url'           => 'http://ws.mourjan.com', // URL to your Zammad installation
            'username'      => 'admin@berysoft.com',  // Username to use for authentication
            'password'      => 'GQ71BUT2',           // Password to use for authentication
            'debug'         => false,                // Enables debug output
        ]);      
        
        $users = $client->resource( ResourceType::USER )->search($fromEmail);
        if ( !is_array($users) ) {
            if ( $users->hasError() ) {
                error_log( $users->getError() );                
            }
            return 0;
        }
        else {
            if ($users) {
                $user = $users[0];
            }
            else {
                $name = trim($fromName);
                $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
                $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
                $user_data = [
                    'login' => $fromEmail,
                    'email' => $fromEmail,
                    'firstname' => $first_name,
                    'lastname' => $last_name                
                ];
                
                $user = $client->resource( ResourceType::USER );
                $user->setValues($user_data);
                $user->save();
                if ( $user->hasError() ) {
                    error_log( $user->getError() );
                    return 0;
                }                        
            }
        }        
        
        $ticket_data = [
            'group_id'      => 1,
            'priority_id'   => 2,
            'state_id'      => 1,
            'title'         => $subject,
            'customer_id'   => $user->getID(),
            'article'       => [
                'origin_by_id'  => $user->getID(),
                'reply_to'      => trim($fromName)." <".trim($fromEmail).">",
                'subject'       => $subject,
                'body'          => $message,
                'content_type'  => 'text/html',
                'internal'      => FALSE,                
                'in_reply_to'   => trim($fromName)." <".trim($fromEmail).">",
                'type_id'       => 11,
                'sender_id'     => 2,
                'time_unit'     => 12,
            ],
        ];
        
        //error_log(json_encode($ticket_data, JSON_PRETTY_PRINT));
        
        $ticket = $client->resource( ResourceType::TICKET );
        $ticket->setValues($ticket_data);
        $ticket->save();
        
        if ( $ticket->hasError() ) {
            error_log( $ticket->getError() );
            return 0;
        }                
                
        return 1;                
    }
    
    
    function faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0) {
        $key='mEI5PRfHaBvbn6El48yZcX492NLb5Cu5';
        $url = 'http://io.mourjan.com:8080/api/v1/authenticate';
     
        $myvars = 'username=rlakis@berysoft.com&password=GQ71BUT2&api_key='.$key;
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $auth = json_decode(curl_exec( $ch ));
        
        
        $url = 'http://io.mourjan.com:8080/api/v1/helpdesk/create?user_id='.$auth->user_id.'&token='.$auth->token;
        $myvars = array();
        $myvars['api_key'] = $key;
        $myvars['user_id'] = $auth->user_id;
        $myvars['token'] = $auth->token;
        $myvars['subject'] = $subject;
        $myvars['body'] = $message;
        $myvars['helptopic']=1;
        $myvars['email']=$fromEmail;
        $myvars['sla']=1;
        $myvars['priority']=2;
        $myvars['code']='0';
        $myvars['mobile']=null;//'9611487521';
        $myvars['phone']='';

        if ($this->user->info['id'])
        {
            $user_name = $this->user->info['name'] ? $this->user->info['name'] : 'Anonymous';
            $myvars['subject'].=" - ". $this->user->info['id'] . " - ".$user_name;
            if ($user_name!=='Anonymous'){
                $fromName = $this->user->info['name'];
            }

            if (isset($this->user->info['email']) && strpos($this->user->info['email'], '@')!==FALSE)
            {
                $myvars['email'] = $this->user->info['email'];
            }
        }

        $name = preg_split('/\s+/', trim($fromName), -1, PREG_SPLIT_NO_EMPTY);
        $myvars['first_name']= $name[0];
        $myvars['last_name']='';
        for($i=1; $i<count($name); $i++)
        {
            $myvars['last_name'].=$name[$i]." ";
        }
        $myvars['last_name']= trim($myvars['last_name']);

        if ($fromName=='Abusive Report' && $reference>0)
        {
            //$ticket->aid = $reference;
            //$ticket->topicId=12;
            //$ticket->priority='High';
            $myvars['subject'].= " - {$reference}";
        }
        
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-XSRF-TOKEN: '.$auth->token));
        curl_setopt( $ch, CURLOPT_HEADER, 1);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($res === FALSE || $status!=200) {
            $err = curl_error($ch);
            error_log($err);
            return 0;
        }
        curl_close($ch);
        
        return 1;
    }
      
        
        
      
    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0, $helpTopic=1) {
        $res = $this->zammad($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account);
        //error_log("res {$res}");
        /*
        //return $this->faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account, $reference, $helpTopic);
        require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $res=0;
        try {
            $mail->Host       = $this->urlRouter->cfg['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Port       = $this->urlRouter->cfg['smtp_port'];
            $mail->Username   = ($sender_account) ? $sender_account : $this->urlRouter->cfg['smtp_user'];
            $mail->Password   = $this->urlRouter->cfg['smtp_pass'];
            $mail->SMTPSecure = 'ssl';
            $mail->Sender = $fromEmail;
            $mail->SetFrom($fromEmail, $fromName);
            //$mail->SetFrom($fromName, $fromEmail);
            if (is_array($toEmail)) 
            {
                foreach ($toEmail as $email) 
                {
                    $mail->AddAddress($email,'');
                }
            }
            else
            {
                $mail->AddAddress($toEmail,$toName);
            }
            $mail->IsHTML(true);
            $mail->CharSet='UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $message;
            if ($mail->send())
            {
                $res = 1;
            }
         } catch (phpmailerException $e) {
         	$res= 0;
                //trigger_error($mail->ErrorInfo);
                error_log($e->getMessage());
         } catch (Exception $e) {
            $res= 0;
            error_log($e->getMessage());
         }
         $mail->ClearAddresses();
         $mail->ClearAllRecipients();
         $mail->ClearAttachments();*/
         return $res;
    }
    
    
    function getAdSection($ad) {
        $section = '';
        $fieldNameIndex=1+$this->lnIndex;
        if (!empty($this->router->sections)) {

            $section = $this->router->sections[$ad['se']][$fieldNameIndex];

            switch ($ad['pu']) {
                case 1:
                case 2:
                case 8:
                case 999:
                    $section = $section . ' ' . $this->router->purposes[$ad['pu']][$fieldNameIndex];
                    break;
                case 6:
                case 7:
                    $section = $this->router->purposes[$ad['pu']][$fieldNameIndex] . ' ' . $section;
                    break;
                case 3:
                    if ($this->router->language == 'ar') {
                        $in = ' ';
                        $section = 'وظائف ' . $section;
                    }
                    else {
                        $section = $section . ' ' . $this->router->purposes[$ad['pu']][$fieldNameIndex];
                    }
                    break;
                case 4:
                    $in = ' ';
                    if ($this->router->language == "en")
                        $in = ' ' . $this->lang['in'] . ' ';
                    $section = $this->router->purposes[$ad['pu']][$fieldNameIndex] . $in . $section;
                    break;
                case 5:
                    $section = $section;
                    break;
            }

            if (count($ad['pubTo'])==1) {
                $cityId=  array_keys($ad['pubTo']);
                $cityId=$cityId[0];
                if (isset($this->router->cities[$cityId])) {
                    $countryId = $this->router->cities[$cityId][4];
                    $cId = 0;
                    if (count($this->router->countries[$countryId]['cities']) > 0) {
                        $cId = $cityId;
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->cities[$cityId][$fieldNameIndex];

                        if (!mb_strstr($section, $this->router->countries[$countryId]['name'], true, "utf-8")) {
                            $section.=' ' . $this->router->countries[$countryId]['name'];
                        }
                    } 
                    else {
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->countries[$countryId]['name'];
                    }
                }
            }
            
            return  $section ;
        }
    }

}

?>