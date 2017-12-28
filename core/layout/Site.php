<?php

include_once get_cfg_var('mourjan.path') .'/core/model/NoSQL.php';

use Core\Model\Router;
use Core\Model\Classifieds;
use Core\Model\NoSQL;
use Core\Lib\SphinxQL;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;


class Site 
{
    var $lang=array(),$xss_hash='',$watchInfo=false; 
    var $user,$userFavorites,$pageUserId=0;
    var $num=10;
    var $isMobile=false;
    var $isMobileAd=false;
    var $lnIndex=0;
    var $channelId=0;
    public $urlRouter;
    var $sortingMode = 0;
    var $langSortingMode = 0;
    var $publisherTypeSorting = 0;


    function __construct(Router $router) 
    {
        global $argc;
        include_once $router->cfg['dir'].'/core/model/Classifieds.php';
        include_once $router->cfg['dir'].'/core/model/User.php';
        include_once $router->cfg['dir'].'/core/lib/SphinxQL.php';
      
        
        $this->urlRouter = $router;
        if ($router->siteLanguage=='en')
            $this->lnIndex=1;
        if (isset($argc)) 
        {
            return;
        }
        $this->initSphinx();
        $this->user=new User($router->db, $router->cfg, $this);
        if(!isset($this->user->params['list_lang'])){
            $this->langSortingMode = -1;
        }
        if(!isset($this->user->params['list_publisher'])){
            $this->publisherTypeSorting = 0;
        }
        $this->classifieds = new Classifieds($router->db);
    }

    public function __destruct() {
    }
    
    
    public function router() : Router
    {
        return $this->urlRouter;
    }
    
    
    public function user() : User
    {
        return $this->user;
    }
    
    
    function checkUserGeo()
    {
        $geo = $this->urlRouter->getIpLocation();
        if (isset($geo['country'])) 
        {
            $country_code=strtolower(trim($geo['country']['iso_code']));
            $this->user->params['user_country']=$country_code;
            if(strlen($country_code)!=2)$this->user->params['user_country']='';
        }
        else
        {
            $this->user->params['user_country']='';
        }
        $this->user->update();
    }
    
    
    function isRTL($text)
    {
        $rtlChars = preg_replace('/[^\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        $ltrChars = preg_replace('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        if(strlen($rtlChars) > (strlen($ltrChars)*0.5)){
            return true;
        }else{
            return false;
        }
        return false;
    }

    
    function BuildExcerpts($text, $length = 0, $separator = '...')
    {
        if($length){
            $str_len = mb_strlen($text, 'UTF-8');
            if($str_len > $length){
                $tmp = mb_substr($text, 0, $length, 'UTF-8');
                $lastSpace = strrpos($tmp, ' ');
                $tmp   = substr($tmp, 0, $lastSpace);
                $str_len_2 = 0;
                $tmp_2 = '';
                do{       
                    if($str_len_2){
                        $tmp = $tmp_2;
                    }
                    $str_len = mb_strlen($tmp, 'UTF-8');
                    $tmp_2 = trim(mb_ereg_replace('<.*', '', trim($tmp)));
                    $tmp_2 = trim(mb_ereg_replace('[\s\-+=<>\\&:;,.]$', '', trim($tmp_2)));
                    $str_len_2 = mb_strlen($tmp_2, 'UTF-8');
                }while($str_len != $str_len_2);
                $text = trim($tmp_2).$separator;
            }
        }
        return $text;
    }
    
    function findFlashUrl($entry){
        foreach ($entry->mediaGroup->content as $content) {
            if ($content->type === 'application/x-shockwave-flash') {
                return $content->url;
            }
        }
        return null;
    }

    function get($str='', $type='', $ignoreCase=false){
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

    function load_lang($langArray, $langStr=''){
        if ($langStr=='')$langStr=$this->urlRouter->siteLanguage;
        if($this->urlRouter->extendedLanguage)$langStr=$this->urlRouter->extendedLanguage;
        foreach ($langArray as $langFile) {
            include_once "{$this->urlRouter->cfg['dir']}/core/lang/{$langFile}.php";
            $this->lang=array_merge($this->lang, $lang);
            if ($langStr!='en') {
                include_once "{$this->urlRouter->cfg['dir']}/core/lang/{$langStr}/{$langFile}.php";
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
        if ($this->urlRouter->module=="search") {
        $this->user->params['search'] = array(
            'cn' =>   $this->urlRouter->countryId,
            'c' =>   $this->urlRouter->cityId,
            'ro' =>   $this->urlRouter->rootId,
            'se' =>   $this->urlRouter->sectionId,
            'pu' =>   $this->urlRouter->purposeId,
            'start'     =>  $this->urlRouter->params['start'],
            'q'     =>  $this->urlRouter->params['q']
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
            if ($this->urlRouter->siteLanguage=='ar') {
                $stamp=$sinceText.$this->formatPlural($days, 'day');
            }else {
                $stamp=$this->formatPlural($days, 'day').$agoText;
            }
        }else {
            $hours=floor($seconds/3600);
            if ($hours){
                if ($this->urlRouter->siteLanguage=='ar') {
                    $stamp=$sinceText.$this->formatPlural($hours, 'hour');
                }else {
                    $stamp=$this->formatPlural($hours, 'hour').$agoText;
                }
            }else {
                $minutes=floor($seconds/60);
                if (!$minutes) $minutes=1;
                if ($this->urlRouter->siteLanguage=='ar') {
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
            if ($this->urlRouter->siteLanguage=='ar') {
                $str=$this->lang[$fieldName];
            }else {
                $str='1 '.$this->lang[$fieldName];
            }
        }elseif ($number==2) {
            if ($this->urlRouter->siteLanguage=='ar') {
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


    function initSphinx($forceInit=false)
    {
        if ($this->urlRouter->db->ql) 
        {
            if ($forceInit) 
            {
                $this->urlRouter->db->ql->resetFilters();               
            }
            return;
        }

        $this->urlRouter->db->ql = new SphinxQL($this->urlRouter->cfg['sphinxql'], $this->urlRouter->cfg['search_index']);        
    }
    
    
    function runQueries($queries, &$matches)
    {
        $this->urlRouter->db->ql->_batch=[];
        foreach ($queries as $row) 
        {
            if (!$this->channelId || $row['ID']==$this->channelId) 
            {
                $this->urlRouter->db->ql->resetFilters(true);
                if ($row['COUNTRY_ID'])
                    $this->urlRouter->db->ql->setFilter("country", $row['COUNTRY_ID']);
                if ($row['CITY_ID'])
                    $this->urlRouter->db->ql->setFilter("city", $row['CITY_ID']);
                if ($row['SECTION_ID'])
                    $this->urlRouter->db->ql->setFilter("section_id", $row['SECTION_ID']);
                if ($row['SECTION_TAG_ID'])
                    $this->urlRouter->db->ql->setFilter("section_tag_id", $row['SECTION_TAG_ID']);
                if ($row['LOCALITY_ID'])
                    $this->urlRouter->db->ql->setFilter("locality_id", $row['LOCALITY_ID']);
                if ($row['PURPOSE_ID'])
                    $this->urlRouter->db->ql->setFilter("purpose_id", $row['PURPOSE_ID']);

                $this->urlRouter->db->ql->setSelect('id, date_added, '.$row['ID'].' as info_id');
                $lastVisit = isset($this->user->params['last_visit']) && $this->user->params['last_visit'] ? $this->user->params['last_visit'] : time()-3600;
                $this->urlRouter->db->ql->setFilterRange('date_added',$lastVisit,time()+3600);
                //$this->urlRouter->db->ql->SetMatchMode ( SPH_MATCH_EXTENDED2 );
                //if($this->sortingMode){
                //    $this->sphinx->SetSortMode(SPH_SORT_EXTENDED, 'media desc, date_added');
                //}else{                    
                $this->urlRouter->db->ql->setSortBy('date_added desc');
                //}
                $this->urlRouter->db->ql->SetLimits(0, 1000);
                $this->urlRouter->db->ql->addQuery($row['ID'], $row['QUERY_TERM'], TRUE);
                if ($this->channelId) break;
            }
        }
        $matches = $this->urlRouter->db->ql->executeBatch(TRUE);
        //if ($results){
        //    $matches=array_merge($matches, $results);
        //}
    }
    
    
    function execute(bool $forceInit=false) 
    {
        //error_log(__CLASS__.'->'.__FUNCTION__);
        $offset = ($this->router()->params['start'] ? ($this->router()->params['start']-1) : 0) * $this->num;

        $this->initSphinx($forceInit);
        
        //$countryId=$this->urlRouter->countryId;
        //$cityId=$this->urlRouter->cityId;
        $rootId=$this->urlRouter->rootId;
        $q=preg_replace('/@/', '\@', $this->urlRouter->params['q']);
        
        if ($this->router()->watchId)
        {
            $this->searchResults=false;
            if (!$this->watchInfo || !count($this->watchInfo)) {
                return;
            }
            //$withKeys=array();
            //$noKeys=array();
            //foreach ($this->watchInfo as $row) {
            //    if ($row['QUERY_TERM']) $withKeys[]=$row;
            //    else $noKeys[]=$row;
            //}
            $results=array();
            
            //if (count($noKeys))
            $this->runQueries ($this->watchInfo, $results);
            //if (count($withKeys))$this->runQueries ($withKeys, $results);
                        
            $matches = array('total_found'=>0,'matches'=>array(),'sub_total'=>array());
            if ($results && count($results)) { 
                if ($this->num>1){
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
                }else {
                    foreach ($results as $result) {
                        if (isset($result['matches'])) {
                            $count=count($result['matches']);
                            $matches['total_found']+=$count;
                            foreach ($result['matches'] as $id => $values) {
                                if (!isset($matches['sub_total'][$values['info_id']])){
                                    $matches['sub_total'][$values['info_id']]=$count;
                                    $matches['matches'][$id] = $values['info_id'];
                                }
                            }
                        }
                    }
                    //$matches['matches']=array_slice($matches['matches'], $offset, $this->num+$plusAd, TRUE);
                }
            }
            //$matches = array('total_found'=>0,'matches'=>array());
            if (!strstr($_SERVER["SCRIPT_FILENAME"], 'cronWatchMailer')) {
                $matches['matches']=  array_keys($matches['matches']);
            } 
            $this->searchResults['body']=$matches;

        } /* End of WatchId */
        else {
            if (($this->user->info['id'] || $this->pageUserId) && $this->userFavorites) 
            {
                $id = $this->user->info['id'] ? $this->user->info['id'] : $this->pageUserId;                
                $this->router()->database()->index()
                        ->setSelect('id')
                        ->starred($id)
                        ->setSortBy('date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', '');
                $this->searchResults = $this->router()->database()->index()->executeBatch();
            } 
            else 
            {
                $__compareID = $this->router()->getPositiveVariable('cmp', INPUT_GET);// filter_input(INPUT_GET, 'cmp', FILTER_VALIDATE_INT, ["options" => ["default" => 0, "min_range" => 0]]);
                $__compareAID = $this->router()->getPositiveVariable('aid', INPUT_GET);// filter_input(INPUT_GET, 'aid', FILTER_VALIDATE_INT, ["options" => ["default" => 0, "min_range" => 0]]);
                
                $this->router()->database()->index()
                        ->region($this->router()->countryId, $this->router()->cityId)
                        ->id($__compareID, TRUE)
                        ->uid($this->router()->userId)                        
                        ->root($rootId)
                        ->section($this->router()->sectionId)
                        ->pupose($this->urlRouter->purposeId)
                        ->locality($this->localityId)
                        ->tag($this->extendedId)
                        ;
                                
                if ($this->publisherTypeSorting && 
                    in_array($rootId,[1,2,3]) && 
                    ($rootId!=3 || ($rootId==3 && $this->urlRouter->purposeId==3)) && 
                    ($rootId!=2 || ($rootId==2 && $this->urlRouter->purposeId==1)) )
                {
                    $this->router()->database()->index()->publisherType($this->publisherTypeSorting == 1 ? 1 : 3);
                }
                
                switch ($this->langSortingMode) 
                {
                    case 0:
                        $lng = '0 as lngmask';
                    case 1:
                        $lng = 'IF(rtl>0,0,1) as lngmask';
                        break;
                    case 2:
                        $lng = 'IF(rtl<>1,0,1) as lngmask';
                        break;
                    default:
                        $lng = ($this->urlRouter->siteLanguage=='ar') ? 'IF(rtl>0,0,1) as lngmask' : 'IF(rtl<>1,0,1) as lngmask';
                        break;
                }
           

                $fields = "id, 0 as newad, date_added, {$lng}";
                if (($last_visited = $this->user()->getLastVisited())) 
                {
                    $fields = "id, if(date_added>{$last_visited}, 1, 0) newad, date_added, {$lng}";                    
                } 
                
                $this->router()->database()->index()
                        ->setSelect($fields)
                        ->setSortBy($this->sortingMode ? 'lngmask asc, media desc, date_added desc' : 'lngmask asc, date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', $q);
                                
                if($this->urlRouter->module=='search' && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId) 
                {
                    $this->getFeaturedAds();
                    $this->getMediaAds();
                }                
        
                                
                if($__compareAID)
                {                    
                    include_once $this->urlRouter->cfg['dir'] . '/core/lib/MCSaveHandler.php';
                    $handler = new MCSaveHandler($this->router()->cfg);
                    $this->searchResults = $handler->searchByAdId($__compareAID);
                }
                else
                {
                    $this->searchResults = $this->router()->database()->index()->executeBatch();   
                }                
            }       
        }                
    }
       
    
    function getMediaAds()
    { 
        $this->router()->database()->index()->resetFilters()
                ->region($this->router()->countryId, $this->router()->cityId)
                ->native()->media()
                ->setFilter(Core\Lib\SphinxQL::SECTION, [834,1079,1314,1112,617,513,293,298,343,350,515,539,108,84,85,114,214,116,123,125,135,144,279])
                ->rtl($this->router()->isArabic() ? [1,2] : [0,2])
                ->setSelect('id')
                ->setSortBy('rand()')
                ->setLimits(0, 4)
                ->addQuery('media');
    }


    function getFeaturedAds()
    {
            
        $q = preg_replace('/@/', '\@', $this->urlRouter->params['q']);        
        $currentPage=($this->urlRouter->params['start']?$this->urlRouter->params['start']:1);
        
        // 1 - get top column paid ads related to query
        if ($this->urlRouter->module=='search' && $currentPage==1)
        {
            $publisher_type=0;
            if ($this->publisherTypeSorting && in_array($this->urlRouter->rootId,[1,2,3]) && 
               ($this->urlRouter->rootId!=3 || ($this->urlRouter->rootId==3 && $this->urlRouter->purposeId==3)) && 
               ($this->urlRouter->rootId!=2 || ($this->urlRouter->rootId==2 && $this->urlRouter->purposeId==1)) )
            {
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
            
            //if ($this->publisherTypeSorting && in_array($this->urlRouter->rootId,[1,2,3]) && 
            //   ($this->urlRouter->rootId!=3 || ($this->urlRouter->rootId==3 && $this->urlRouter->purposeId==3)) && 
            //   ($this->urlRouter->rootId!=2 || ($this->urlRouter->rootId==2 && $this->urlRouter->purposeId==1)) )
            //{
            //    $this->router()->database()->index()->publisherType($this->publisherTypeSorting == 1 ? 1 : 3);
            //}

            //$this->router()->database()->index()
            //        ->setSelect("id")
            //        ->setSortBy("rand()")
            //        ->setLimits(0, 40)
            //        ->addQuery('zone1', $q);
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
        if ($this->urlRouter->module=='search'){ // && $this->searchResults['body']['total_found'] > 20
            $publisher_type=0;
            if($this->publisherTypeSorting && in_array($this->router()->rootId,[1,2,3])){
                $publisher_type = $this->publisherTypeSorting == 1 ? 1 : 3;
            }
            
            $rtlFilter = [];
            switch ($this->langSortingMode) 
            {
                case 1:
                    $rtlFilter=[1,2];
                    break;
                case 2:
                    $rtlFilter=[0,2];
                    break;

                default:
                    $rtlFilter=($this->urlRouter->siteLanguage=='ar')?[1,2]:[0,2];
                    break;
            }
            
            $this->router()->database()->index()->resetFilters()
                ->native()
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

    
    function zammad($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0) : int 
    {
        $client = new Client([
            'url'           => 'http://ws.mourjan.com', // URL to your Zammad installation
            'username'      => 'admin@berysoft.com',  // Username to use for authentication
            'password'      => 'GQ71BUT2',           // Password to use for authentication
            'debug'         => false,                // Enables debug output
        ]);      
        
        $users = $client->resource( ResourceType::USER )->search($fromEmail);
        if ( !is_array($users) ) 
        {
            if ( $users->hasError() ) 
            {
                error_log( $users->getError() );                
            }
            return 0;
        }
        else
        {
            //error_log( 'Found ' . count($users) . ' user(s) with email address ' . $fromEmail );
            if ($users)
            {
                $user = $users[0];
            }
            else
            {
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
                if ( $user->hasError() ) 
                {
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
        
        if ( $ticket->hasError() ) 
        {
            error_log( $ticket->getError() );
            return 0;
        }                
                
        return 1;                
    }
    
    
    function faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0)
    {
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
        if ($res === FALSE || $status!=200)
        {
            $err = curl_error($ch);
            error_log($err);
            return 0;
        }
        curl_close($ch);
        
        //error_log($status);
        
        return 1;
        
        //if(preg_match('/HTTP\/.* ([0-9]+) .*/', $response, $status)) 
        //{
        //    if ($status[1]!=200)
        //    {
        //        error_log($response);
        //        return 0;
        //    }
        //}
        
        //return 1;
        
    }
      
        
        
      
    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0, $helpTopic=1)
    {
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
        if (!empty($this->urlRouter->sections)) {

            $section = $this->urlRouter->sections[$ad['se']][$fieldNameIndex];

            switch ($ad['pu']) {
                case 1:
                case 2:
                case 8:
                case 999:
                    $section = $section . ' ' . $this->urlRouter->purposes[$ad['pu']][$fieldNameIndex];
                    break;
                case 6:
                case 7:
                    $section = $this->urlRouter->purposes[$ad['pu']][$fieldNameIndex] . ' ' . $section;
                    break;
                case 3:
                    if ($this->urlRouter->siteLanguage == 'ar') {
                        $in = ' ';
                        $section = 'وظائف ' . $section;
                    }else {
                        $section = $section . ' ' . $this->urlRouter->purposes[$ad['pu']][$fieldNameIndex];
                    }
                    break;
                case 4:
                    $in = ' ';
                    if ($this->urlRouter->siteLanguage == "en")
                        $in = ' ' . $this->lang['in'] . ' ';
                    $section = $this->urlRouter->purposes[$ad['pu']][$fieldNameIndex] . $in . $section;
                    break;
                case 5:
                    $section = $section;
                    break;
            }

            if (count($ad['pubTo'])==1){
                $cityId=  array_keys($ad['pubTo']);
                $cityId=$cityId[0];
                if(isset($this->urlRouter->cities[$cityId])) {
                    $countryId = $this->urlRouter->cities[$cityId][4];
                    $cId = 0;
                    if (count($this->urlRouter->countries[$countryId]['cities']) > 0) {
                        $cId = $cityId;
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->cities[$cityId][$fieldNameIndex];

                        if (!mb_strstr($section, $this->urlRouter->countries[$countryId]['name'], true, "utf-8")) {
                            $section.=' ' . $this->urlRouter->countries[$countryId]['name'];
                        }
                    } else {
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->countries[$countryId]['name'];
                    }
                }
            }
            
            return  $section ;
        }
    }

}

?>