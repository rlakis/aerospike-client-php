<?php

class Site {

    var $lang=array(),$xss_hash='',$watchInfo=false; 
    var $user,$userFavorites,$pageUserId=0;
    var $num=10;
    var $isMobile=false;
    var $isMobileAd=false;
    var $lnIndex=0;
    var $channelId=0;
    var $urlRouter;
    var $sortingMode = 0;
    var $langSortingMode = 0;
    var $publisherTypeSorting = 0;


    function __construct($router) {
        global $argc;
        include_once $router->cfg['dir'].'/core/model/Classifieds.php';
        include_once $router->cfg['dir'].'/core/model/User.php';
        include_once $router->cfg['dir'].'/core/lib/SphinxQL.php';
      
        
        $this->urlRouter = $router;
        if ($router->siteLanguage=='en')
            $this->lnIndex=1;
        if (isset($argc)) {
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
    
    function checkUserGeo(){
        $geo = $this->urlRouter->getIpLocation();
        if (!empty($geo)) {
            $country_code=strtolower(trim($geo['country']['iso_code']));
            $this->user->params['user_country']=$country_code;
            if(strlen($country_code)!=2)$this->user->params['user_country']='';
        }else{
            $this->user->params['user_country']='';
        }
        $this->user->update();
    }
    
    function isRTL($text){
        /*
        var y=v.replace(/[^\u0621-\u064a\u0750-\u077f]|[:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        var z=v.replace(/[\u0621-\u064a\u0750-\u077f:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        if(y.length > z.length*0.5){
            e.className='ar';
        }else{
            e.className='en';
        }*/
        $rtlChars = preg_replace('/[^\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        $ltrChars = preg_replace('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        if(strlen($rtlChars) > (strlen($ltrChars)*0.5)){
            return true;
        }else{
            return false;
        }
        /*if(preg_match('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]/u', $text)){
            return true;
        }*/
        return false;
    }
    
    function BuildExcerpts($text, $length = 0, $separator = '...'){
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


    function initSphinx($forceInit=false){
        if ($this->urlRouter->db->ql) {
            if ($forceInit) {
                $this->urlRouter->db->ql->resetFilters(TRUE);               
            }
            return;
        }

        $this->urlRouter->db->ql = new SphinxQL($this->urlRouter->cfg['sphinxql'], $this->urlRouter->cfg['search_index']);        
    }
    
    
    function runQueries($queries, &$matches){
        $this->urlRouter->db->ql->_batch=[];
        foreach ($queries as $row) {
            if (!$this->channelId || $row['ID']==$this->channelId) {
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
    
    
    function execute($forceInit=false) {
        $offset = ($this->urlRouter->params['start'] ? ($this->urlRouter->params['start']-1) : 0) * $this->num;

        $this->initSphinx($forceInit);
        $countryId=$this->urlRouter->countryId;
        $cityId=$this->urlRouter->cityId;
        $rootId=$this->urlRouter->rootId;
        $q=  preg_replace('/@/', '\@', $this->urlRouter->params['q']);
        
        if($this->urlRouter->watchId){
            $this->searchResults=false;
            if (!$this->watchInfo || !count($this->watchInfo)) {
                return;
            }
            $withKeys=array();
            $noKeys=array();
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

        }else {
            if (($this->user->info['id'] || $this->pageUserId) && $this->userFavorites) {
                $id = $this->user->info['id'] ? $this->user->info['id'] : $this->pageUserId;                
                $this->urlRouter->db->ql->setSortBy('date_added desc');
                $this->urlRouter->db->ql->setFilter('starred', (int)$id, false);
                $this->urlRouter->db->ql->setLimits($offset, $this->num);
                $this->urlRouter->db->ql->setSelect('id');
                $this->urlRouter->db->ql->addQuery('body', '');
                $this->searchResults = $this->urlRouter->db->ql->executeBatch();
            } else {
                
                if(isset($_GET['cmp']) && is_numeric($_GET['cmp'])){
                    $this->urlRouter->db->ql->setFilter('id', $_GET['cmp'],true);
                }
                
                if ($this->urlRouter->userId) {
                    $this->urlRouter->db->ql->setFilter('user_id', $this->urlRouter->userId);
                    $countryId=0;
                    $cityId=0;
                    $rootId=0;
                }
                
                if ($this->localityId) {
                    $this->urlRouter->db->ql->setFilter('locality_id', $this->localityId);
                }
                
                if ($this->extendedId) {
                    $this->urlRouter->db->ql->setFilter('section_tag_id', $this->extendedId);
                } else {
                    if ($this->urlRouter->sectionId) {
                        $this->urlRouter->db->ql->setFilter('section_id', $this->urlRouter->sectionId);
                    }
                }

                if ($rootId) {
                    $this->urlRouter->db->ql->setFilter('root_id', $rootId);
                }

                if ($this->urlRouter->purposeId) {
                    $this->urlRouter->db->ql->setFilter('purpose_id', $this->urlRouter->purposeId);
                }


                if ($countryId) {
                    $this->urlRouter->db->ql->setFilter('country', $countryId);
                }

                if ($cityId) {
                    $this->urlRouter->db->ql->setFilter('city', $cityId);
                }   
                
                if($this->publisherTypeSorting && in_array($rootId,[1,2,3]) && 
                            ($rootId!= 3 || ($rootId== 3 && $this->urlRouter->purposeId == 3)) && 
                            ($rootId!= 2 || ($rootId== 2 && $this->urlRouter->purposeId == 1)) ){
                    $this->urlRouter->db->ql->setFilter('publisher_type', ($this->publisherTypeSorting == 1 ? 1 : 3));
                }
                
                if($this->langSortingMode > -1){
                    if($this->langSortingMode == 1){
                        $lng = 'IF(rtl>0,0,1) as lngmask';
                    }elseif($this->langSortingMode == 2){
                        $lng = 'IF(rtl<>1,0,1) as lngmask';
                    }else{
                        $lng = '0 as lngmask';
                    }
                }else{
                    $lng = ($this->urlRouter->siteLanguage=='ar') ? 'IF(rtl>0,0,1) as lngmask' : 'IF(rtl<>1,0,1) as lngmask';
                }

                if (isset($this->user->params['last_visit']) && $this->user->params['last_visit']) {
                    $this->urlRouter->db->ql->setSelect("id, if(date_added>{$this->user->params['last_visit']}, 1, 0) newad, date_added, {$lng}");
                    /*
                    if($this->sortingMode){
                        if($this->publisherTypeSorting){
                            if($this->publisherTypeSorting == 1){
                                $this->urlRouter->db->ql->setSortBy('lngmask asc, publisher_type asc, media desc, date_added desc');
                            }else{
                                $this->urlRouter->db->ql->setSortBy('lngmask asc, publisher_type desc, media desc, date_added desc');
                            }
                        }else{
                            $this->urlRouter->db->ql->setSortBy('lngmask asc, media desc, date_added desc');
                        }
                    }else{   
                        if($this->publisherTypeSorting){
                            if($this->publisherTypeSorting == 1){
                                $this->urlRouter->db->ql->setSortBy('lngmask asc, publisher_type asc,  date_added desc');
                            }else{
                                $this->urlRouter->db->ql->setSortBy('lngmask asc, publisher_type desc,  date_added desc');
                            }
                        }else{
                            $this->urlRouter->db->ql->setSortBy('lngmask asc, date_added desc');
                        }
                    }*/
                    
                } else {
                    $this->urlRouter->db->ql->setSelect("id, 0 as newad, date_added, {$lng}" );
                    
                }
                
                if($this->sortingMode){
                    $this->urlRouter->db->ql->setSortBy('lngmask asc, media desc, date_added desc');
                }else{    
                    $this->urlRouter->db->ql->setSortBy('lngmask asc, date_added desc');
                }
                
                $this->urlRouter->db->ql->setLimits($offset, $this->num);
                //$this->urlRouter->db->ql->setFacet('purpose_id', TRUE);
                $this->urlRouter->db->ql->addQuery('body', $q);
                
                if($this->urlRouter->module=='search' && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId) {
                    $this->getFeaturedAds();
                    $this->getMediaAds();
                }

                
        
                if(isset($_GET['aid']) && is_numeric($_GET['aid'])){
                    include_once $this->urlRouter->cfg['dir'] . '/core/lib/MCSaveHandler.php';
                    $handler = new MCSaveHandler($this->urlRouter->cfg);
                    $this->searchResults = $handler->searchByAdId($_GET['aid']);
                    //$this->searchResults = ['body'=>[
                    //                            'total_found'=>0
                    //                            ]
                    //                        ];
                }else{
                    $this->searchResults = $this->urlRouter->db->ql->executeBatch();   
                    //var_dump($this->searchResults);
                }
                /*if (isset($this->searchResults['sections'])) {
                    //error_log(var_export($this->searchResults['sections'], true));
                    
                }*/
            }       
        }
        
        
        //if ($cl->getLastError())
        //    error_log ( $cl->getLastError() );
    }
       
    
    function getMediaAds(){        
        $this->urlRouter->db->ql->resetFilters(TRUE);            
        $this->urlRouter->db->ql->setFilter('media', 1);
        $this->urlRouter->db->ql->setFilter('publication_id', 1);
        $this->urlRouter->db->ql->setFilter('section_id', array(834,1079,1314,1112,617,513,293,298,343,350,515,539,108,84,85,114,214,116,123,125,135,144,279));


        if ($this->urlRouter->countryId) {
            $this->urlRouter->db->ql->setFilter('country', $this->urlRouter->countryId);
        }

        if ($this->urlRouter->cityId) {
            $this->urlRouter->db->ql->setFilter('city', $this->urlRouter->cityId);
        }

        if ($this->urlRouter->siteLanguage=='ar') {
            $this->urlRouter->db->ql->setFilter('rtl', array(1,2));
        } else {
            $this->urlRouter->db->ql->setFilter('rtl', array(0,2));
        }

        $this->urlRouter->db->ql->setSelect('id');
        $this->urlRouter->db->ql->setSortBy('rand()');
        $this->urlRouter->db->ql->setLimits(0, 4);
        $this->urlRouter->db->ql->addQuery('media');
    }


    function getFeaturedAds(){
            
        $q = preg_replace('/@/', '\@', $this->urlRouter->params['q']);        
        $currentPage=($this->urlRouter->params['start']?$this->urlRouter->params['start']:1);
        
        // 1 - get top column paid ads related to query
        if ($this->urlRouter->module=='search' && $currentPage == 1){
            $this->urlRouter->db->ql->resetFilters(TRUE);
            $this->urlRouter->db->ql->setFilter('publication_id', 1);

            if ($this->localityId) {
                $this->urlRouter->db->ql->setFilter('locality_id', $this->localityId);
            }

            if ($this->extendedId) {
                $this->urlRouter->db->ql->setFilter('section_tag_id', $this->extendedId);
            } elseif ($this->urlRouter->sectionId) {
                $this->urlRouter->db->ql->setFilter('section_id', $this->urlRouter->sectionId);
            }

            if ($this->urlRouter->rootId) {
                $this->urlRouter->db->ql->setFilter('root_id', $this->urlRouter->rootId);
            }

            if ($this->urlRouter->purposeId) {
                $this->urlRouter->db->ql->setFilter('purpose_id', $this->urlRouter->purposeId);
            }

            if ($this->urlRouter->countryId) {
                $this->urlRouter->db->ql->setFilter('country', $this->urlRouter->countryId);
            }

            if ($this->urlRouter->cityId) {
                $this->urlRouter->db->ql->setFilter('city', $this->urlRouter->cityId);
            }
            
                if($this->publisherTypeSorting && in_array($this->urlRouter->rootId,[1,2,3]) && 
                            ($this->urlRouter->rootId!= 3 || ($this->urlRouter->rootId== 3 && $this->urlRouter->purposeId == 3)) && 
                            ($this->urlRouter->rootId!= 2 || ($this->urlRouter->rootId== 2 && $this->urlRouter->purposeId == 1)) ){
                    $this->urlRouter->db->ql->setFilter('publisher_type', ($this->publisherTypeSorting == 1 ? 1 : 3));
                }

            if ($this->urlRouter->siteLanguage=='ar') {
                $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(1,2));
            } else {
                $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(0,2));
            }
            
            $this->urlRouter->db->ql->setFilterCondition('featured_date_ended', '>=', time());                        
            $this->urlRouter->db->ql->setSelect("id" );
            $this->urlRouter->db->ql->setSortBy("rand()");            
            $this->urlRouter->db->ql->setLimits(0, 30);
            $this->urlRouter->db->ql->addQuery('zone1', $q);
        }
        
        // 2 - get side column paid ads related
        if (true){
            $this->urlRouter->db->ql->resetFilters(TRUE);
            $this->urlRouter->db->ql->setFilter('publication_id', 1);
            /*
            if ($this->localityId)
                $cl->SetFilter('locality_id', array($this->localityId));
            if ($this->extendedId)
                $cl->SetFilter('section_tag_id', array($this->extendedId));
            elseif ($this->urlRouter->sectionId)
                $cl->SetFilter('section_id', array($this->urlRouter->sectionId));
*/
            
            /*$rand = mt_rand(0, 9);
            if($rand != 0){
                $this->urlRouter->db->ql->setFilter('user_id', array(220906), true);
            }*/

            if ($this->urlRouter->countryId) {
                $this->urlRouter->db->ql->setFilter('country', $this->urlRouter->countryId);
            }
            if ($this->urlRouter->cityId) {
                $this->urlRouter->db->ql->setFilter('city', $this->urlRouter->cityId);
            }
            if ($this->urlRouter->sectionId) {
                $this->urlRouter->db->ql->setFilter('section_id', $this->urlRouter->sectionId, TRUE);
            }elseif ($this->urlRouter->rootId) {
                $this->urlRouter->db->ql->setFilter('root_id', $this->urlRouter->rootId, TRUE);
            }

            $this->urlRouter->db->ql->setFilterCondition('featured_date_ended', '>=', time());
            $this->urlRouter->db->ql->setSelect("id" );
            $this->urlRouter->db->ql->setSortBy("RAND()");
            $this->urlRouter->db->ql->setLimits(0, 4);
            $this->urlRouter->db->ql->addQuery('zone2');
        }
        
        // 1 - get top column featured ads related to query
        if ($this->urlRouter->module=='search'){ // && $this->searchResults['body']['total_found'] > 20
            $this->urlRouter->db->ql->resetFilters(TRUE);
            $this->urlRouter->db->ql->setFilter('publication_id', 1);
         
            if ($this->localityId) {
                $this->urlRouter->db->ql->setFilter('locality_id', $this->localityId);
            }

            if ($this->extendedId) {
                $this->urlRouter->db->ql->setFilter('section_tag_id', $this->extendedId);
            } elseif ($this->urlRouter->sectionId) {
                $this->urlRouter->db->ql->setFilter('section_id', $this->urlRouter->sectionId);
            }

            if ($this->urlRouter->rootId) {
                $this->urlRouter->db->ql->setFilter('root_id', $this->urlRouter->rootId);
            }

            if ($this->urlRouter->purposeId) {
                $this->urlRouter->db->ql->setFilter('purpose_id', $this->urlRouter->purposeId);
            }

            if ($this->urlRouter->countryId) {
                $this->urlRouter->db->ql->setFilter('country', $this->urlRouter->countryId);
            }

            if ($this->urlRouter->cityId) {
                $this->urlRouter->db->ql->setFilter('city', $this->urlRouter->cityId);
            }
            
            if($this->publisherTypeSorting && in_array($this->urlRouter->rootId,[1,2,3])){
                    $this->urlRouter->db->ql->setFilter('publisher_type', ($this->publisherTypeSorting == 1 ? 1 : 3));
                }
            
            $this->urlRouter->db->ql->setFilterCondition('featured_date_ended', '<', time());
            
            if($this->langSortingMode > -1){
                if($this->langSortingMode == 1){
                    $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(1,2));
                }elseif($this->langSortingMode == 2){
                    $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(0,2));
                }
            }else{
                if ($this->urlRouter->siteLanguage=='ar') {
                    $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(1,2));
                } else {
                    $this->urlRouter->db->ql->setFilterCondition('rtl', 'in', array(0,2));
                }
            }
            //$dateFrom = time()-259200;//3 days
            //$this->urlRouter->db->ql->setFilterCondition('date_added', '<', $dateFrom);
            if(isset($this->user->params['feature']) && count($this->user->params['feature'])){
                $this->urlRouter->db->ql->setFilterCondition('id', 'not in', $this->user->params['feature']);
            }
            $this->urlRouter->db->ql->SetSelect('id, (impressions + ((IF(now()-date_added<3600,20,impressions)/(now()-date_added))*(date_ended-now()))) as forecast');
            $this->urlRouter->db->ql->setSortBy('forecast asc');
            $this->urlRouter->db->ql->setLimits(0, 2);
            $this->urlRouter->db->ql->addQuery('zone0', $q);            
        }                      
    }
    
    
    function isEmail($email){
        if(preg_match('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)){
            return true;
        }
        return false;
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
        
        $name = preg_split('/\s+/', trim($fromName), -1, PREG_SPLIT_NO_EMPTY);
        $myvars['first_name']= $name[0];
        $myvars['last_name']='';
        for($i=1; $i<count($name); $i++)
            $myvars['last_name'].=$name[$i]." ";
        $myvars['last_name']= trim($myvars['last_name']);
       //$myvars['first_name']='Sami';
       //$myvars['last_name']='Lakis';
        
        /*
        $myvars.= '&sla=1';
        $myvars.= '&priority=2';
        $myvars.= '&dept=2';
        
        $myvars = 'api_key=' . $key;
        $myvars.= '&user_id=' . $auth->user_id;
        $myvars.= '&token=' . $auth->token;
        
        $myvars.= '&subject=User&nbsp;Feedback';// . htmlentities( $subject );
        $myvars.= '&body=' . 'test';// '<div dir="auto">' . $message . '</div>';
        $myvars.= '&helptopic=1';
        $myvars.= '&sla=1';
        $myvars.= '&priority=2';
        $myvars.= '&dept=2';
        
        $myvars.= '&first_name=robert' ;//. htmlentities($fromName);
        
        error_log($url);
        error_log($myvars);
        */
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-XSRF-TOKEN: '.$auth->token));
        curl_setopt( $ch, CURLOPT_HEADER, 1);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        curl_close($ch);
        
        $code = 75;
        $status = -1;
        //error_log(var_export($result, true));

        if(preg_match('/HTTP\/.* ([0-9]+) .*/', $response, $status)) 
        {
            error_log("Error: ".PHP_EOL.$status[1]);
        }
        error_log($response);
        return 1;
        
    }
    
    
    function createTicket($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0)
    {
        $keys = [99=>'BE3B545DE0E7EAB26360CF633269D99A', 2=>'80061F9B6D72FAC2E9D92D6AC5F705AA', 4=>'BE3B545DE0E7EAB26360CF633269D99A'];
        $osticket = ['url'=>'http://ticket.mourjan.com/api/http.php/tickets.json', 'key'=>$keys[get_cfg_var('mourjan.server_id')]];
        
        $ticket = json_decode('{"alert":true, "autorespond":false}');
        $ticket->autorespond = FALSE;
        $ticket->name = $fromName;
        $ticket->email = $fromEmail;
        $ticket->subject = $subject;
        $ticket->message = "data:text/html;charset=utf-8,{$message}";
        
        if ($this->user->info['id'])
        {
            $ticket->uid = $this->user->info['id'];
            if ($fromName=='')
            {
                $ticket->name = $this->user->info['name'];
            }
            
            if (isset($this->user->info['email']) && strpos($this->user->info['email'], '@')!==FALSE) 
            {
                 $ticket->email = $this->user->info['email'];
            }
            //$ticket->upage = "<a href='https://www.mourjan.com//myads/?u={$ticket->uid}' target=_blank>User Ads</a>";
        }
        
        if ($fromName=='Abusive Report' && $reference>0)
        {
            $ticket->aid = $reference;
            $ticket->topicId=12;
            $ticket->priority='High';
            $ticket->subject.= " - {$reference}";
        }
        
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ticket->ip = "{$_SERVER['HTTP_X_FORWARDED_FOR']}";
        } 
        else 
        {
            $ticket->ip = "{$_SERVER['REMOTE_ADDR']}";
        }
       
        #set timeout
        set_time_limit(10);

        #curl post
        $ch = curl_init();        
        curl_setopt($ch, CURLOPT_URL, $osticket['url']);        
        curl_setopt($ch, CURLOPT_POST, 1);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ticket));
        curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.10');
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-API-Key: '.$osticket['key']));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        $result=curl_exec($ch);
        curl_close($ch);

        //Use postfix exit codes...expected by MTA.
        $code = 75;
        $status = -1;
        //error_log(var_export($result, true));

        if(preg_match('/HTTP\/.* ([0-9]+) .*/', $result, $status)) 
        {
            //error_log (var_export($status, TRUE));
            switch($status[1]) 
            {
                case 201: //Success
                    $code = 0;
                    break;

                case 400:
                    $code = 66;
                    break;

                case 401: /* permission denied */
                case 403:
                    $code = 77;
                    break;

                case 415:
                case 416:
                case 417:
                case 501:
                    $code = 65;
                    break;

                case 503:
                    $code = 69;
                    break;

                case 500: //Server error.
                default: //Temp (unknown) failure - retry 
                    $code = 75;
            }
        }
        return ($code==0) ? 1 : 0;
    }
    
    
    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0)
    {
        return $this->faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account, $reference);
        //return $this->createTicket($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account, $reference);
        /*
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
         $mail->ClearAttachments();
         return $res;
         * 
         */
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