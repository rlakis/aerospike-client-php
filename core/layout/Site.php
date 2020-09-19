<?php

\Config::instance()->incModelFile('NoSQL')->incModelFile('Classifieds')
        ->incModelFile('User')->incLibFile('MCSearch');
        
//->incLibFile('SphinxQL')->
use Core\Model\Router;
use Core\Model\Classifieds;
//use Core\Lib\SphinxQL;
use Core\Lib\MCSearch;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class Site {
    public User $user;
    public array $lang=[];
    var $xss_hash='',$watchInfo=false; 
    var $userFavorites,$pageUserId=0;
    public int $num=10;
    var $isMobileAd=false;
    public int $lnIndex=0;
    var $channelId=0;
    public Router $router;
    public Classifieds $classifieds;
    var $sortingMode = 0;
    var $langSortingMode = 0;
    var $publisherTypeSorting = 0;
        
    function __construct() {
        global $argc;        
        $this->router = Router::instance();
        if ($this->router->language==='en') {
            $this->lnIndex=1;
        }
        if (isset($argc)) { return; }
        
        //$this->initSphinx();
        
        $this->user=new User($this);
        $this->router->setUser($this->user);
        if (!isset($this->user->params['list_lang'])) {
            $this->langSortingMode = -1;
        }
        if (!isset($this->user->params['list_publisher'])) {
            $this->publisherTypeSorting = 0;
        }
        $this->classifieds = new Classifieds($this->router->db);
    }

    
    public function __destruct() {}
    
    
    //public function router() : Router { return $this->router; }
    
    public function user() : User { return $this->user; }
    
    
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

    
    function findFlashUrl($entry) {
        foreach ($entry->mediaGroup->content as $content) {
            if ($content->type === 'application/x-shockwave-flash') {
                return $content->url;
            }
        }
        return null;
    }

    
    function getGetInt(string $parameter, int $default=0) : int {
        $result=\filter_input(\INPUT_GET, $parameter, \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>$default]]);
        return $result;
    }
    
    
    function getGetString(string $parameter, string $default='') : string {
        $result=\filter_input(\INPUT_GET, $parameter, \FILTER_SANITIZE_STRING, ['options'=>['default'=>$default]]);
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
    

    function load_lang(array $langArray, string $langStr='') : void {
        if ($langStr==='') { $langStr=$this->router->language; }
        if ($this->router->extendedLanguage) { $langStr=$this->router->extendedLanguage; }
        foreach ($langArray as $langFile) {
            include_once "{$this->router->config->baseDir}/core/lang/{$langFile}.php";
            $this->lang=\array_merge($this->lang, $lang);
            if ($langStr!=='en') {
                include_once "{$this->router->config->baseDir}/core/lang/{$langStr}/{$langFile}.php";
                $this->lang=\array_merge($this->lang, $lang);
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


    function checkNewUserContent($diff) : bool {
        return (isset($this->user->params['last_visit']) && $this->user->params['last_visit']<$diff);
    }

    
    function handleCacheActions() {
        if ($this->router->module==='search') {
            $this->user->params['search'] = [
                'cn'=> $this->router->countryId,
                'c'=> $this->router->cityId,
                'ro'=> $this->router->rootId,
                'se'=> $this->router->sectionId,
                'pu'=> $this->router->purposeId,
                'start'=> $this->router->params['start'],
                'q'=> $this->router->params['q']
            ];
            $this->user->update();
        }
    }

    
    function formatSinceDate($seconds) : string {
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


    function formatPlural($number, $fieldName) : string {
        $str='';
        if ($number==1) {
            if ($this->router->language=='ar') {
                $str=$this->lang[$fieldName];
            }
            else {
                $str='1 '.$this->lang[$fieldName];
            }
        }
        elseif ($number==2) {
            if ($this->router->language=='ar') {
                $str=$this->lang['2'.$fieldName];
            }
            else {
                $str='2 '.$this->lang['2'.$fieldName];
            }
        }
        elseif ($number>=3 && $number<11) {
            $str=$number.' '.$this->lang['3'.$fieldName];
        }
        else {
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

    function initSphinx($forceInit=false) : void {
        if ($this->router->db->ql) {
            if ($forceInit) { $this->router->db->ql->resetFilters(); }
        }
        else {
            $this->router->db->ql=new SphinxQL($this->router->cfg['sphinxql'], $this->router->cfg['search_index']);
        }
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
        $offset=($this->router->params['start']? ($this->router->params['start']-1):0)*$this->num;
                
        
        $search=new MCSearch($this->router->db->manticore);
        $search->limit($this->num)->offset($offset);
                
        //$this->initSphinx($forceInit);
        
        $rootId=$this->router->rootId;
        $q=\preg_replace('/@/', '\@', $this->router->params['q']);
        
        if ($this->router->watchId) {
            $this->searchResults=false;
            if (!$this->watchInfo || !count($this->watchInfo)) {
                return;
            }
            $results=[];
            
            $this->runQueries($this->watchInfo, $results);
                        
            $matches = ['total_found'=>0, 'matches'=>[], 'sub_total'=>[]];
            if ($results && \count($results)) { 
                if ($this->num>1) {
                    foreach ($results as $result) {
                        if (isset($result['matches'])) {
                            $count=\count($result['matches']);
                            $matches['total_found']+=$count;
    
                            foreach ($result['matches'] as $id => $values) {
                                $matches['matches'][$id] = $values['date_added'];
                            }
                        }
                    }
                    \krsort($matches['matches'], \SORT_NUMERIC);
                    $matches['matches']=\array_slice($matches['matches'], $offset, $this->num, TRUE);
                }
                else {
                    foreach ($results as $result) {
                        if (isset($result['matches'])) {
                            $count=\count($result['matches']);
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

            if (!\strstr($_SERVER["SCRIPT_FILENAME"], 'cronWatchMailer')) {
                $matches['matches']=\array_keys($matches['matches']);
            } 
            $this->searchResults['body']=$matches;

        } /* End of WatchId */
        else {
            if (($this->user->isLoggedIn() || $this->pageUserId) && $this->userFavorites) {
                $id=$this->user->id()>0?$this->user->id():$this->pageUserId;
                $fq=new MCSearch($this->router->db->manticore);
                $fq->starred($id)->setSource('id')->offset($offset)->limit($this->num)->sort('date_added', 'desc');
                $this->searchResults['body']=$fq->result();
                /*
                 * TO BE MIGRATED
                $this->router->db->index()
                        ->setSelect('id')
                        ->starred($id)
                        ->setSortBy('date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', '');
                $this->searchResults=$this->router->db->index()->executeBatch();
                 * 
                 */
            } 
            else {
                $__compareID=$this->router->getPositiveVariable('cmp', \INPUT_GET);
                $__compareAID=$this->router->getPositiveVariable('aid', \INPUT_GET);
                $__stripPremium=$this->router->getPositiveVariable('strip', \INPUT_GET);
                
                $search->regionFilter($this->router->countryId, $this->router->cityId)
                        ->idFilter($__compareID, true)
                        ->uidFilter($this->router->userId)
                        ->rootFilter($rootId)
                        ->sectionFilter($this->router->sectionId)
                        ->purposeFilter($this->router->purposeId)
                        ->localityFilter($this->localityId)
                        ->tagFilter($this->extendedId)
                        ;
                        
                /*
                
                $this->router->db->index()
                        ->region($this->router->countryId, $this->router->cityId)
                        ->id($__compareID, true)
                        ->uid($this->router->userId)                        
                        ->root($rootId)
                        ->section($this->router->sectionId)
                        ->purpose($this->router->purposeId)
                        ->locality($this->localityId)
                        ->tag($this->extendedId)
                        ;
                */
                
                if ($__stripPremium===1) {  
                    //$this->router->db->index()->featured(false);
                    $search->filter(MCSearch::FEATURED_TTL, 'lt', time());
                }
                                
                if ($this->publisherTypeSorting && \in_array($rootId, [1, 2, 3]) && 
                    ($rootId!==3 || ($rootId===3 && $this->router->purposeId===3)) && 
                    ($rootId!==2 || ($rootId===2 && $this->router->purposeId===1)) ) {
                    
                    $search->filter(MCSearch::PUBLISHER_TYPE, 'equals', $this->publisherTypeSorting==1?1:3);
                    //$this->router->db->index()->publisherType($this->publisherTypeSorting==1 ? 1 : 3);
                }
                
                switch ($this->langSortingMode) {
                    case 0:
                        $lexpr=0;
                        //$lng='0 as lngmask';
                        break;
                    case 1:
                        //$lng='IF(rtl>0,0,1) as lngmask';
                        $lexpr='IF(rtl>0,0,1)';
                        break;
                    case 2:
                        //$lng='IF(rtl<>1,0,1) as lngmask';
                        $lexpr='IF(rtl<>1,0,1)';
                        break;
                    default:
                        $lexpr=($this->router->language==='ar') ? 'IF(rtl>0,0,1)' : 'IF(rtl<>1,0,1)';
                        //$lng=($this->router->language==='ar') ? 'IF(rtl>0,0,1) as lngmask' : 'IF(rtl<>1,0,1) as lngmask';
                        break;
                }
                $search->expression('lngmask', $lexpr);

                //\error_log(PHP_EOL.$lng.PHP_EOL);
                
                if (($last_visited=$this->user->getLastVisited())) {
                    //$fields="id, if(date_added>{$last_visited}, 1, 0) newad, date_added, {$lng}";
                    $search->expression('newad', "if(date_added>{$last_visited}, 1, 0)");
                }
                else {
                    //$fields="id, 0 as newad, date_added, {$lng}";
                    $search->expression('newad', 0);
                }
                $search->setSource(['id', 'newad', 'date_added', 'lngmask']);
                
                if ($this->sortingMode) {
                    $search->sort('lngmask')->sort('media', 'desc')->sort('date_added', 'desc');
                }
                else {
                    $search->sort('lngmask')->sort('date_added', 'desc');                    
                }
                
                if (\trim($q)!=='') {
                    $search->search($q);
                }
                
                $this->searchResults['body']=$search->result();// ['total_found'=>$rs->getTotal(), 'matches'=>$rs];
                
                
                //if ($__compareID>0) {
                //    \error_log(PHP_EOL.\json_encode($search->getBody()).PHP_EOL);
                //}
                //\error_log(var_export($search->getBody(), true));
                //var_dump($search->get()->current());
                /*
                $this->router->db->index()
                        ->setSelect($fields)
                        ->setSortBy($this->sortingMode?'lngmask asc, media desc, date_added desc':'lngmask asc, date_added desc')
                        ->setLimits($offset, $this->num)
                        ->addQuery('body', $q);
                */                
                if ($this->router->module==='search' && !$this->userFavorites && !$this->router->watchId && !$this->router->userId && $__compareID===0 && $__compareAID===0) {
                    $this->getFeaturedAds();
                    $this->getMediaAds();
                }                
        
                                
                if ($__compareAID>0) {  
                    $this->router->config->incLibFile('MCSaveHandler');
                    $handler=new MCSaveHandler($this->router->cfg);              
                    $this->searchResults=$handler->similarByAdId($__compareAID, $__stripPremium);
                    //$this->searchResults=$handler->searchByAdId($__compareAID, $__stripPremium);
                }
                //else {
                    //$this->searchResults=$this->router->db->index()->executeBatchNew();   
                //}                
            }       
        }
        
        
        //error_log("num {$this->num} vs ". count($this->searchResults['body']['matches']) ." out of {$this->searchResults['body']['total_found']}");
    }
       
    
    function getMediaAds() : void { 
        /*
        $this->router->db->index()->resetFilters()
                ->region($this->router->countryId, $this->router->cityId)
                ->media()
                ->setFilter(Core\Lib\SphinxQL::SECTION, [834,1079,1314,1112,617,513,293,298,343,350,515,539,108,84,85,114,214,116,123,125,135,144,279])
                ->rtl($this->router->isArabic() ? [1,2] : [0,2])
                ->setSelect('id')
                ->setSortBy('rand()')
                ->setLimits(0, 4)
                ->addQuery('media');
        */
        $search=new MCSearch($this->router->db->manticore);
        
        $search->setSource('id')
                ->regionFilter($this->router->countryId, $this->router->cityId)
                ->mediaFilter()
                ->rtlFilter($this->router->isArabic() ? [1,2] : [0,2])
                ->filter(MCSearch::SECTION, 'in', [834,1079,1314,1112,617,513,293,298,343,350,515,539,108,84,85,114,214,116,123,125,135,144,279])
                ->offset(0)
                ->limit(4)
                ->sort('rand()')
                ;
        
        $rs=$search->get();
        $this->searchResults['media']=['total_found'=>$rs->getTotal(), 'matches'=>$rs];
    }


    function getFeaturedAds() {            
        $q = preg_replace('/@/', '\@', $this->router->params['q']);        
        $currentPage=($this->router->params['start']?$this->router->params['start']:1);
        
        // 1 - get top column paid ads related to query
        if ($this->router->module==='search' && $currentPage==1) {
            $publisher_type=0;
            if ($this->publisherTypeSorting && in_array($this->router->rootId,[1,2,3]) && 
               ($this->router->rootId!=3 || ($this->router->rootId==3 && $this->router->purposeId==3)) && 
               ($this->router->rootId!=2 || ($this->router->rootId==2 && $this->router->purposeId==1)) ) {
                $publisher_type = $this->publisherTypeSorting == 1 ? 1 : 3;
            }
            
            $search=new MCSearch($this->router->db->manticore);
            $search->featuredFilter()
                    ->regionFilter($this->router->countryId, $this->router->cityId)
                    ->rootFilter($this->router->rootId)
                    ->sectionFilter($this->router->sectionId)
                    ->purposeFilter($this->router->purposeId)
                    ->localityFilter($this->localityId)
                    ->tagFilter($this->extendedId)
                    ->rtlFilter($this->router->isArabic()?[1,2]:[0,2])
                    ->publisherTypeFilter($publisher_type)
                    ->setSource("id")
                    ->sort("rand()")
                    ->offset(0)
                    ->limit(40);
            if (!empty($q)) {
                $search->match($q);
            }
            $rs=$search->result();
            $this->searchResults['zone1']=$rs;
                    
            /*
            $this->router->db->index()->resetFilters()
                    ->featured()
                    ->region($this->router->countryId, $this->router->cityId)
                    ->root($this->router->rootId)
                    ->section($this->router->sectionId)
                    ->purpose($this->router->purposeId)
                    ->locality($this->localityId)
                    ->tag($this->extendedId)
                    ->rtl($this->router->isArabic() ? [1,2] : [0,2])
                    ->publisherType($publisher_type)
                    ->setSelect("id")
                    ->setSortBy("rand()")
                    ->setLimits(0, 40)
                    ->addQuery('zone1', $q);         */              
        }
        
        // 2 - get side column paid ads related
        if (true) {
            /*
            $this->router->db->index()->resetFilters()
                ->region($this->router->countryId, $this->router->cityId)
                ->featured()
                ->root($this->router->rootId)
                ->section($this->router->sectionId)  
                ->setSelect("id")
                ->setSortBy("rand()")
                ->setLimits(0, 4)
                ->addQuery('zone2');
            */
            $search=new MCSearch($this->router->db->manticore);
            $search->featuredFilter()
                    ->regionFilter($this->router->countryId, $this->router->cityId)
                    ->rootFilter($this->router->rootId)
                    ->sectionFilter($this->router->sectionId)
                    ->setSource("id")
                    ->sort("rand()")
                    ->offset(0)
                    ->limit(4);
            $rs=$search->result();
            $this->searchResults['zone2']=$rs;
        }
        
        // 1 - get top column featured ads related to query
        if ($this->router->module==='search') {
            $publisher_type=0;
            if ($this->publisherTypeSorting && in_array($this->router->rootId,[1,2,3])) {
                $publisher_type=$this->publisherTypeSorting==1?1:3;
            }
            
            $rtlFilter=[];
            switch ($this->langSortingMode) {
                case 1:
                    $rtlFilter=[1,2];
                    break;
                case 2:
                    $rtlFilter=[0,2];
                    break;
                default:
                    $rtlFilter=($this->router->language==='ar')?[1,2]:[0,2];
                    break;
            }
            /*
            $this->router->db->index()->resetFilters()
                ->region($this->router->countryId, $this->router->cityId)                
                ->root($this->router->rootId)
                ->section($this->router->sectionId)
                ->purpose($this->router->purposeId)
                ->locality($this->localityId)
                ->tag($this->extendedId)
                ->rtl($rtlFilter)
                ->publisherType($publisher_type)
                ->exclude($this->user()->getFeature())
                ->SetSelect('id, (impressions + ((IF(now()-date_added<3600,20,impressions)/(now()-date_added))*(date_ended-now()))) as forecast')
                ->setSortBy('forecast asc')
                ->setLimits(0, 2)
                ->addQuery('zone0', $q);
            */
            
            
            $search=new MCSearch($this->router->db->manticore);
            $search->regionFilter($this->router->countryId, $this->router->cityId)                
                ->rootFilter($this->router->rootId)
                ->sectionFilter($this->router->sectionId)
                ->purposeFilter($this->router->purposeId)
                ->localityFilter($this->localityId)
                ->tagFilter($this->extendedId)
                ->rtlFilter($rtlFilter)
                ->publisherTypeFilter($publisher_type)
                ->expression('forecast', '(impressions + ((IF(now()-date_added<3600,20,impressions)/(now()-date_added))*(date_ended-now())))')
                ->notFilter(MCSearch::ID, 'in', $this->user()->getFeature())
                ->SetSource('id, forecast')
                ->sort('forecast')
                ->limit(2);
            if (!empty($q)) {
                $search->match($q);
            }
            $this->searchResults['zone0']=$search->result();
        }                      
    }
    
    
    function isEmail($email) : bool {
        if (preg_match('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)) {
            return true;
        }
        return false;
    }

    
    function zammad(string $toName, string $toEmail, string $fromName, string $fromEmail, string $subject, string $message, string $sender_account='', int $reference=0) : int {
        $client=new Client([
            'url'           => 'http://ws.mourjan.com', // URL to your Zammad installation
            'username'      => 'admin@berysoft.com',    // Username to use for authentication
            'password'      => 'GQ71BUT2',              // Password to use for authentication
            'debug'         => false,                   // Enables debug output
        ]);
        
        //$users=$client->resource(ResourceType::USER)->search(\trim($fromEmail));        
        
        //\error_log($fromEmail.PHP_EOL.var_export($users, true).PHP_EOL.var_export( $client->getLastResponse(), true));
        
        /*
        if ( !\is_array($users) ) {
            if ( $users->hasError() ) {
                \error_log(__FUNCTION__.'('.__LINE__.') '. $users->getError() );                
            }
            return 0;
        }
                
        if ($users) {
            $user = $users[0];
        }
        else {
            $name = \trim($fromName);
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
                \error_log(__FUNCTION__.'('.__LINE__.') '. $user->getError() );
                return 0;
            }                        
        }*/
               
        $name = \trim($fromName);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        $user_data = [
            'login' => $fromEmail,
            'email' => $fromEmail,
            'firstname' => $first_name,
            'lastname' => $last_name                
        ];
                
        $user = $client->resource(ResourceType::USER);
        $user->setValues($user_data);
        $user->save();
        if ( $user->hasError() ) {
            if (!preg_match('/is already used for other user/', $user->getError())) {
                \error_log(__FUNCTION__.'('.__LINE__.') '. $user->getError() );
                return 0;
            }
        }                        
        
        $client->setOnBehalfOfUser($fromEmail);        
        
        $ticket_data = [
            'group_id'      => 1,
            'priority_id'   => 2,
            'state_id'      => 1,
            'title'         => $subject,
            /*'customer_id'   => $user->getID(),*/
            'article'       => [
                /*'origin_by_id'  => $user->getID(),*/
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
                
        $ticket=$client->resource(ResourceType::TICKET);
        $ticket->setValues($ticket_data);
        $ticket->save();
        
        if ( $ticket->hasError() ) {
            \error_log(__FUNCTION__.'('.__LINE__.') '. $ticket->getError() );
            return 0;
        }                
             
        return 1;                
    }

      
    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0, $helpTopic=1) {
        $res = $this->zammad($toName, $toEmail[0], $fromName, $fromEmail, $subject, $message, $sender_account);
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
    
    
    function getAdSection(Core\Model\Ad $ad) : string {
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
                    if ($this->router->isArabic()) {
                        $in = ' ';
                        $section = 'وظائف ' . $section;
                    }
                    else {
                        $section = $section . ' ' . $this->router->purposes[$ad['pu']][$fieldNameIndex];
                    }
                    break;
                case 4:
                    $in = ' ';
                    if (!$this->router->isArabic()) {
                        $in.= $this->lang['in'] . ' ';
                    }
                    $section = $this->router->purposes[$ad['pu']][$fieldNameIndex] . $in . $section;
                    break;
                case 5:
                    $section = $section;
                    break;
            }

            if (\count($ad['pubTo'])===1) {
                $cityId=\array_keys($ad['pubTo']);
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


