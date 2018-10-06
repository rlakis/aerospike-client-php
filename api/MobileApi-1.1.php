<?php

use \MaxMind\Db\Reader;
use \Core\Model\DB;
use \Core\Model\NoSQL;
use \Core\Model\Classifieds;
use \Core\Lib\SphinxQL;
use \Core\Lib\Audit;
use \Core\Model\MobileValidation;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;


class MobileApi {
    var $config;
    var $result = array('e'=>'', 'c'=>0, 'd'=>array(), 'l'=>0); // l:log

    var $db;
    public $command;
    var $lang;
    var $demo;
    var $countryId;
    var $cityId;
    public $systemName;
    public $appVersion;

    var $formatNumbers=1;
    var $mobileValidator=null;

    private static $stmt_get_ad = null;
    private static $stmt_get_ext = null;
    private static $stmt_get_loc = null;

    var $uid;
    var $uuid;
    public $provider;
    public $user = null;

    function __construct($config) {
        $this->lang         = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
        $this->demo         = filter_input(INPUT_GET, 'demo', FILTER_VALIDATE_INT)+0;
        $this->unixtime     = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT, ['options'=>['default'=>-1, 'min_range'=>1388534400, 'max_range'=>PHP_INT_MAX]]);
        $this->countryId    = filter_input(INPUT_GET, 'country', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);
        $this->cityId       = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);

        $this->uid          = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
        $this->uuid         = filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $this->systemName   = filter_input(INPUT_GET, 'sn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $this->appVersion   = filter_input(INPUT_GET, 'apv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        
        $this->command      = filter_input(INPUT_GET, 'm', FILTER_VALIDATE_INT);
        
        if (!$this->isAndroid()) {
            $this->systemName = strtolower($this->systemName);
        }
        
        if ($this->isIOS()) {
            $this->lang     = filter_input(INPUT_GET, 'dl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
        }
        
        $this->config=$config;
        $this->db = new DB($this->config, TRUE);
        
        $this->result['server'] = $this->config['server_id']; 
        
        if ($this->uuid && $this->command!=API_DATA && $this->command!=API_TOTALS) {
            $this->user = MCUser::getByUUID($this->uuid);
            if ($this->user->getID()<=0 && !($this->command==API_REGISTER && $this->uid==0) && $this->command!==API_APNS_TOKEN) {            
                NoSQL::Log(['Error'=>'User device not found!', 'Command'=>$this->command, 'UUID'=> $this->uuid]);
            }            
        }
        else {
            $this->user = new MCUser();            
        }
    }
    
    
    function __destruct() {
        if ($this->db) {
            if (self::$stmt_get_loc) { self::$stmt_get_loc=NULL; }
            if (self::$stmt_get_ext) { self::$stmt_get_ext=NULL; }
            if (self::$stmt_get_ad) { self::$stmt_get_ad=NULL; }
        }
    }
        

    function getUID() : int {
        if ($this->uid) {
            return $this->uid;
        }

        $this->uid = $this->user->getID();
        return $this->uid;
    }

    
    function getUUID() : string {
        return $this->uuid;
    }

    
    function getUser() : MCUser {
        return $this->user;
    }

    
    function getDatabase() {
        $this->result['unixtime']=$this->db->get(
                "SELECT DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', CURRENT_TIMESTAMP) FROM RDB\$DATABASE",
                null, false, \PDO::FETCH_NUM)[0][0];

        $dict = $this->db->getCountriesDictionary();
        $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[10] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], strtoupper($record[3]), 0, $record[7], $record[8], $record[5], $record[4], $record[9]];
            }
        }
        $this->result['d']['country'] = $ds;
     
        $dict = $this->db->getCitiesDictionary(TRUE); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[8] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], 0, $record[4], $record[5], $record[6], $record[7], $record[3]];
            }            
        }
        $this->result['d']['city'] = $ds;

       
        $dict = $this->db->getPurposes(); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[4] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], 0, $record[3]];
            }            
        }
        $this->result['d']['purpose']= $ds;
        
     
        $dict = $this->db->getRoots(); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[6] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], $record[3], $record[4]];
            }            
        }
        $this->result['d']['root']=$ds;
   
        $this->result['d']['root_purpose_xref']=$this->db->get(
                "SELECT ID, ROOT_ID, PURPOSE_ID FROM ROOT_PURPOSE_XREF WHERE UNIXTIME>?",
                [$this->unixtime], false, \PDO::FETCH_NUM);

        $this->result['d']['section']=$this->db->get(
                "SELECT ID, NAME_AR, NAME_EN, ROOT_ID, BLOCKED, uri FROM SECTION WHERE UNIXTIME>?",
                [$this->unixtime], false, \PDO::FETCH_NUM);

        $this->result['d']['tag'] = $this->db->get(
                "SELECT ID, SECTION_ID, LANG, NAME, BLOCKED, uri FROM SECTION_TAG WHERE UNIXTIME>?",
                [$this->unixtime], FALSE, \PDO::FETCH_NUM);

        $this->result['d']['geo'] = $this->db->get(
                "select r.LOC_EN_ID ID, r.COUNTRY_ID, r.ID CITY_ID, r.PARENT_ID, cast('en' as varchar(2)) LANG, r.NAME, 0 BLOCKED, r.LOC_AR_ID ALTER_ID, r.URI
                from F_CITY r
                where COUNTRY_ID=? and UNIXTIME>? and r.uri>''
                union ALL
                select r.LOC_AR_ID ID, r.COUNTRY_ID, r.ID CITY_ID, r.PARENT_ID, CAST('ar' AS VARCHAR(2)) LANG, l.NAME, 0 BLOCKED, r.LOC_EN_ID ALTER_ID, r.URI
                from F_CITY r
                left join NLANG l on l.TABLE_ID=201 and l.ID=r.ID
                where COUNTRY_ID=? and UNIXTIME>? and r.uri>''",
                [$this->countryId, $this->unixtime, $this->countryId, $this->unixtime], FALSE, PDO::FETCH_NUM);

        $this->db->close();

    }


    public function getCountryLocalities() {
        $this->result['unixtime']=$this->db->queryResultArray(
                "SELECT DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', CURRENT_TIMESTAMP) FROM RDB\$DATABASE",
                null, false, PDO::FETCH_NUM)[0][0];
        
        $this->result['d']['geo'] = $this->db->queryResultArray(
                "SELECT ID, COUNTRY_ID, CITY_ID, PARENT_ID, LANG, NAME, BLOCKED, ALTER_ID FROM GEO_TAG WHERE COUNTRY_ID=?",
                [$this->countryId], TRUE, PDO::FETCH_NUM);
    }


    function getCounts() {
        $this->result['unixtime']=$this->db->queryResultArray(
                "SELECT DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', CURRENT_TIMESTAMP) FROM RDB\$DATABASE", null, false, PDO::FETCH_NUM)[0][0];
        
        $cut = filter_input(INPUT_GET, 'tc', FILTER_VALIDATE_INT, ['options'=>['default'=>-1, 'min_range'=>1388534400, 'max_range'=>PHP_INT_MAX]]);

        $this->result['d']['counts']=$this->db->queryResultArray(
                "SELECT COUNTRY_ID, CITY_ID, ROOT_ID, SECTION_ID, PURPOSE_ID, COUNTER
                 FROM COUNTS where country_id=? and city_id=? and UNIXTIME>?",
                [$this->countryId, $this->cityId, $cut], false, PDO::FETCH_NUM);

        $this->db->close();
    }

    
    function getClassified($id) {
        $ad = $this->db->getCache()->get($id);
        if ($ad) { return $ad; }
        
        if (!self::$stmt_get_ad || !$this->db->inTransaction()) {
            self::$stmt_get_ad = $this->db->getInstance()->prepare(
                "select 
                    ad.id, ad.hold, '' title, ad.publication_id, ad.country_id, ad.city_id, 
                    section.category_id, ad.purpose_id, section.root_id, ad.content, ad.rtl, 
                    ad.date_added, ad.section_id, trim(country.id_2), 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.DATE_ADDED), 
                    ad.canonical_id, ad.expiry_date, link.url flink, 
                    '/'||lower(country.ID_2)||'/'||city.uri||'/'||section.uri||'/'||purpose.uri||'/%s%d/' uri,
                    section.name_ar, section.name_en, 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.LAST_UPDATE),
                    ad_user.latitude, ad_user.longitude,
                    '' alter_title, ad_translated.content alter_content,
                    ad_user.web_user_id,                     
                    wu.user_rank,
                    IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', featured.ended_date)) featured_date_ended, 
                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', bo.end_date)) bo_date_ended, 
                    ad.publisher_type, 
                    ad_user.content user_content
                from ad
                left join country on country.id=ad.country_id 
                left join city on city.id=ad.city_id
                left join section on section.id=ad.section_id
                left join purpose on purpose.id=ad.purpose_id
                left join link on link.ad_id=ad.id
                left join ad_user on ad_user.id=ad.id
                left join ad_translated on ad_translated.ad_id=ad.id 
                left join t_ad_bo bo on bo.ad_id=ad.id and bo.blocked=0 
                left join web_users wu on wu.id = ad_user.web_user_id 
                left join t_ad_featured featured on featured.ad_id=ad.id and current_timestamp between featured.added_date and featured.ended_date  
                where ad.id=?"
                );

            self::$stmt_get_ext = $this->db->getInstance()->prepare("
                SELECT r.SECTION_TAG_ID, t.LANG
                FROM AD_TAG r
                left join SECTION_TAG t on t.ID=r.SECTION_TAG_ID
                where r.AD_ID=?
                ");

            self::$stmt_get_loc = $this->db->getInstance()->prepare("
                SELECT r.LOCALITY_ID, g.NAME, g.CITY_ID, g.PARENT_ID, g.LANG
                FROM AD_LOCALITY r
                left join GEO_TAG g on g.ID=r.LOCALITY_ID
                where r.AD_ID=?
                ");
        }

        self::$stmt_get_ad->execute([$id]);
        
        if (($row = self::$stmt_get_ad->fetch(PDO::FETCH_NUM)) !== false) {
            $count = count($row);
            for ($i=0; $i<$count; $i++) {
                if(is_numeric($row[$i])) $row[$i] = $row[$i]+0;
            }

            $user_content = $row[$count-1];
            unset($row[$count-1]);

            $ad=$row;

            $ad[Classifieds::PICTURES] = NULL;
            $ad[Classifieds::PICTURES_DIM] = NULL;
            $ad[Classifieds::VIDEO] = NULL;
            $ad[Classifieds::LOCALITIES_AR] = NULL;
            $ad[Classifieds::LOCALITIES_EN] = NULL;
            $ad[Classifieds::USER_LEVEL] = 0;
            $ad[Classifieds::DONE] = 0;
            $ad[Classifieds::LOCATION] = NULL;
            $ad[Classifieds::USER_RANK] = $row[$count-5];
            $ad[Classifieds::FEATURE_ENDING_DATE] = $row[$count-4];
            $ad[Classifieds::BO_ENDING_DATE] = $row[$count-3];
            $ad[Classifieds::PUBLISHER_TYPE] = $row[$count-2];

            // parser
            $decoder = json_decode($user_content, TRUE);
            if (isset($decoder['pics']) && is_array($decoder['pics']) && count($decoder['pics'])) {

                foreach ($decoder['pics'] as $pic => $is_set) {
                    if ($is_set) {
                        if (is_array($is_set)) {
                            $ad[Classifieds::PICTURES_DIM][]=$is_set;
                        }
                        $ad[Classifieds::PICTURES][] = $pic;
                    }
                }
            }

            if (isset($decoder['cui'])) {
                $ad[Classifieds::CONTACT_INFO] = $decoder['cui'];
            }
            
            if (isset($decoder['cut'])) {
                $ad[Classifieds::CONTACT_TIME] = $decoder['cut'];
            }

            if (isset($decoder['loc']) && $decoder['loc']) {
                $ad[Classifieds::LOCATION] = $decoder['loc'];
            }

            if (isset($decoder['video']) && is_array($decoder['video']) && count($decoder['video'])) {
                $ad[Classifieds::VIDEO] = $decoder['video'];
            }

            if (isset($decoder['userLvl']) && $decoder['userLvl']) {
                $ad[Classifieds::USER_LEVEL] = $decoder['userLvl'];
            }
            
            $ad[Classifieds::PRICE] = (isset($decoder['attrs']['price']) && $decoder['attrs']['price']>0) ? $decoder['attrs']['price'] : 0;

            if ($ad[Classifieds::ROOT_ID]==1) {
                self::$stmt_get_loc->execute(array($id));
                while (($locRow = self::$stmt_get_loc->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $ad[$locRow['LANG']=='ar' ? Classifieds::LOCALITIES_AR : Classifieds::LOCALITIES_EN][$locRow['LOCALITY_ID']+0] =
                            array($locRow['NAME'], $locRow['CITY_ID'], $locRow['PARENT_ID']);
                }
            }
            elseif ($ad[Classifieds::ROOT_ID]==2) {
                self::$stmt_get_ext->execute(array($id));
                while (($extRow = self::$stmt_get_ext->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $ad[$extRow['LANG']=='ar' ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN] = $extRow['SECTION_TAG_ID']+0;
                }
            }

            // cache it
            $this->db->getCache()->set($id, $ad);
            return $ad;
        }
        return FALSE;
    }
    
    
    
    function fetchPremiumAds($sphinxQL, $keywords, $rootId, $sectionId) {
        $sphinxQL->resetFilters();
        //$sphinxQL->setFilter('publication_id', 1);
        if ($this->countryId) {
            $sphinxQL->setFilter('country', $this->countryId);
        }
        
        if ($this->cityId) {
            $sphinxQL->setFilter('city', $this->cityId);
        }
        
        if ($sectionId) {
            $sphinxQL->setFilter('section_id', $sectionId, TRUE);
        }
        elseif ($rootId) {
            $sphinxQL->setFilter('root_id', $rootId, TRUE);
        }
        
        $sphinxQL->setFilterCondition('featured_date_ended', '>=', time());
        $sphinxQL->setSelect("id" );
        $sphinxQL->setSortBy("RAND()");
        $sphinxQL->setLimits(0, 3);
        
        if (!$sectionId && !$rootId && $keywords!='') {
            $words = preg_split('/ /', $keywords);
            $keywords='';
            foreach ($words as $word) {
                $keywords .= ' -'.$word;
            }
            $keywords = trim($keywords);
        }
        
        return $sphinxQL->Query($keywords, MYSQLI_NUM);
    }

    
    function isIOS() {
        return $this->systemName=='ios';
    }
    
    
    function isAndroid() {
        return $this->systemName=='Android';
    }
    
    
    function search($forceFavorite = false) {
        include_once $this->config['dir'].'/core/lib/SphinxQL.php';
        include_once $this->config['dir'].'/core/model/Classifieds.php';
        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        
        //this variable specifies if app is Android 1.2.1+    
        if ($this->isIOS()) {
            $this->result['cdn'] = 'https://c6.mourjan.com';
        }

        $num = 20;
        $filters = array();
        $keywords = preg_replace('/@/', '\@', trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING, ['options'=>['default'=>""]])));
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        $favorite = filter_input(INPUT_GET, 'favorite', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        $sortLang = filter_input(INPUT_GET, 'sl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $sortBy = filter_input(INPUT_GET, 'sm', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
        $adMobAlreadySent = filter_input(INPUT_GET, 'admob', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        
        $publisherType = filter_input(INPUT_GET, 'pt', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
        
        //added for android 2+
        $device_appversion = filter_input(INPUT_GET, 'bv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $adMobAdsCount = filter_input(INPUT_GET, 'admob', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        
        
        if(!in_array($publisherType, [0,1,2])) {
            $publisherType = 0;
        }
        
        if($publisherType == 2) {
            $publisherType = 3;
        }
        
        $isWatchlist = filter_input(INPUT_GET, 'iws', FILTER_SANITIZE_STRING, ['options'=>['default'=>0]]);

        $sphinxQL = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
        $sphinxQL->setLimits($offset, $num);
        
        $canDisplayAdmobAds = true;

        if ($favorite || $forceFavorite) {
            if($forceFavorite) {
                $num=20;
            }
            $sphinxQL->setFilter('starred', $this->uid);
            $filters['starred'] = $this->uid;
        } 
        else {
            $rootId = filter_input(INPUT_GET, 'root', FILTER_VALIDATE_INT)+0;
            $sectionId = filter_input(INPUT_GET, 'section', FILTER_VALIDATE_INT)+0;
            $purposeId = filter_input(INPUT_GET, 'purpose', FILTER_VALIDATE_INT)+0;
            $tagId = filter_input(INPUT_GET, 'tag', FILTER_VALIDATE_INT)+0;
            $localityId = filter_input(INPUT_GET, 'locality', FILTER_VALIDATE_INT)+0;
            $adId = filter_input(INPUT_GET, 'aid', FILTER_VALIDATE_INT)+0;
            
            $disabledSections = array_keys($this->config['disabled_ad_sections']);
            if(in_array($sectionId, $disabledSections)){
                $canDisplayAdmobAds = false;
            }

            if ($adId)              {$sphinxQL->setFilter('id', $adId);}
            if ($this->countryId)   {$sphinxQL->setFilter('country', $this->countryId);}
            if ($this->cityId)      {$sphinxQL->setFilter('city', $this->cityId);}
            if ($rootId)            {$sphinxQL->setFilter('root_id', $rootId);}
            if ($sectionId)         {$sphinxQL->setFilter('section_id', $sectionId);}
            if ($tagId)             {$sphinxQL->setFilter('section_tag_id', $tagId);}
            if ($localityId)        {$sphinxQL->setFilter('locality_id', $localityId);}
            if ($purposeId)         {$sphinxQL->setFilter('purpose_id', $purposeId);}
            if ($publisherType)     {$sphinxQL->setFilter('publisher_type', $publisherType);} 
        }
        
        $isSorted = false;
        $featureIndex = 3;

        if ($sortLang=='ar') {
            $sphinxQL->setSelect("id, date_added, IF(rtl>0,0,1) as lngmask, IF(featured_date_ended>NOW(),1,0) as featured, media");
            $sphinxQL->SetSortBy("lngmask asc, featured desc, date_added desc");
            $isSorted = true;
        } 
        elseif ($sortLang=='en') {
            $sphinxQL->setSelect("id, date_added, IF(rtl<>1,0,1) as lngmask, IF(featured_date_ended>NOW(),1,0) as featured, media");
            $sphinxQL->SetSortBy('lngmask asc, featured desc, date_added desc');
            $isSorted = true;
        } 
        else {
            $featureIndex = 2;            
            
            $sphinxQL->setSelect("id, date_added, IF(featured_date_ended>NOW(),1,0) as featured, media");
            if($isWatchlist) {
                $isSorted = true;
                if($sortBy==0) {
                    $sphinxQL->SetSortBy('date_added desc');
                }
                elseif($sortBy==1) {
                    $sphinxQL->SetSortBy('media desc,date_added desc');
                }
                else {
                    $sphinxQL->SetSortBy('date_added desc');
                }
            }
            else {
                if($sortBy==0) {
                    $sphinxQL->SetSortBy('featured desc, date_added desc');
                }
                elseif($sortBy==1) {
                    $sphinxQL->SetSortBy('featured desc,media desc,date_added desc');
                }
                else {
                    $isSorted = true;
                    $sphinxQL->SetSortBy('date_added desc');
                }
            }
        }
        
        $query = $sphinxQL->Query($keywords, MYSQLI_NUM);
        
        if ($sphinxQL->getLastError()) {
            $this->result['e'] = $sphinxQL->getLastError();
        } 
        else {
            $this->result['total']=$query['total_found']+0;
            if (isset($query['matches'])) {
                $model = new Classifieds($this->db);  
                
                /**
                 * apply shuffling to premium ads
                 */
                $newMatches = [];
                $premiumMatches = [];
                $current_time=time();
                foreach ($query['matches'] as $matches) {
                    $ad = $model->getById($matches[0]+0);
                    if ($ad) {  
                        $isFeatured = $current_time < $ad[Classifieds::FEATURE_ENDING_DATE];
                        if($isFeatured){
                            $premiumMatches[] = $matches;
                        }
                        else{
                            $newMatches[] = $matches;
                        }
                    }
                }
                shuffle($premiumMatches);
                $query['matches'] = array_merge($premiumMatches, $newMatches);
                unset ($premiumMatches);                
                unset ($newMatches);                
                /**
                 * end of apply shuffling to premium ads
                 */
                
                //fetch premium ads
                $premiumAds = [];
                $hasPremium = false;
                if (($this->isAndroid() || $this->isIOS()) && $this->result['total'] > 0 && !($favorite || $forceFavorite)) {
                    $premiumQuery = $this->fetchPremiumAds($sphinxQL, $keywords, $rootId, $sectionId);
                    if(!$sphinxQL->getLastError() && $premiumQuery['total_found'] && isset($premiumQuery['matches'])) {
                        foreach ($premiumQuery['matches'] as $matches) {
                            $premiumAds[] = $matches[0];
                            $hasPremium = true;
                        }
                    }
                }
                $i = 0;
                $j = 0;
                $premiumGap = 14;
                $pOff = floor($offset / $premiumGap);
                $j = $pOff * $premiumGap + $premiumGap;
                $numberOfAds = floor( ($j-1) / $premiumGap);                
                $j += $numberOfAds;
                $numberofPremium = 0;
                
                $adMobGap = 5;
                $adMob_off = floor($offset / $adMobGap);
                $adMob_j = $adMob_off * $adMobGap + $adMobGap;
                $numberOfAdmobAds = $adMobAdsCount; 
                $adMob_j += $numberOfAdmobAds;   
                
                for($index = 0, $length = count($query['matches']); $index < $length; $index++){
                    $ad = $model->getById($query['matches'][$index][0]+0);
                    if ($ad) { 
                        $this->addAdToResultArray($ad, $query['matches'][$index][$featureIndex]);
                        $i++;
                        
                        $isNextFeatured = false;
                        if(!$isSorted){                        
                            if(isset($query['matches'][$index+1]) && $query['matches'][$index+1][0]){
                                $nextAd = $model->getById($query['matches'][$index+1][0]+0);

                                $isNextFeatured = $current_time < $nextAd[Classifieds::FEATURE_ENDING_DATE];
                            }
                        }
                        
                        if(!$isNextFeatured){
                            
                            
                            if ($canDisplayAdmobAds && $this->isIOS() && $adMobAlreadySent<5 && ($i+$offset)%7==0 && ($i+$offset)%2==1 && !($favorite || $forceFavorite)) {                           
                                $adUnitID = version_compare($this->appVersion, '1.0.9')>0 ? "ca-app-pub-2427907534283641/8260964224" : "ca-app-pub-2427907534283641/4099192620";                         
                                $this->result['d'][] = [-1*($i+$offset), $adUnitID];
                                $adMobAlreadySent++;
                            }

                            if($canDisplayAdmobAds && $this->isAndroid() && $device_appversion && $device_appversion > '1.9.9'){
                                $translated_i = $i + $offset + $numberOfAdmobAds;   
                                if($adMob_j - 3 == $translated_i) {
                                    $adMob_j += $adMobGap;
                                    $numberOfAdmobAds++;
                                    
                                    $this->result['d'][] = [0];
                                }
                            }
                            
                            if($hasPremium && count($premiumAds)) {
                                $translated_i = $i + $offset + $numberOfAds;                        
                                if($j==$translated_i) {
                                    $j += $premiumGap+1;
                                    $numberOfAds++;
                                    $adId = array_pop($premiumAds);
                                    $ad = $model->getById($adId);
                                    if ($ad) {
                                        $this->addAdToResultArray($ad,$query['matches'][$index][$featureIndex],true);
                                        $numberofPremium++;
                                    }
                                }
                            }                            
                        
                        }else{
                            $translated_i = $i + $offset + $numberOfAds;                        
                            if($j==$translated_i) {
                                $j += $premiumGap;
                            }
                            
                            $translated_i = $i + $offset + $numberOfAdmobAds;
                            if($adMob_j - 3 == $translated_i) {
                                //error_log($translated_i);
                                $adMob_j += $adMobGap;
                            }
                        }
                    }
                }
                /*
                foreach ($query['matches'] as $matches) {
                    $ad = $model->getById($matches[0]+0);
                    if ($ad) { 
                        $isFeatured = $current_time < $ad[Classifieds::FEATURE_ENDING_DATE];
                        
                        $this->addAdToResultArray($ad, $matches[2]);
                        $i++;
                        
                        if ($this->isIOS() && $adMobAlreadySent<5 && ($i+$offset)%7==0 && ($i+$offset)%2==1 && !($favorite || $forceFavorite)) {                           
                            $adUnitID = version_compare($this->appVersion, '1.0.9')>0 ? "ca-app-pub-2427907534283641/8260964224" : "ca-app-pub-2427907534283641/4099192620";                         
                            $this->result['d'][] = [-1*($i+$offset), $adUnitID];
                            $adMobAlreadySent++;
                        }
                        
                        if($hasPremium && count($premiumAds)) {
                            $translated_i = $i + $offset + $numberOfAds;                        
                            if($j==$translated_i) {
                                $j += $premiumGap+1;
                                $numberOfAds++;
                                $adId = array_pop($premiumAds);
                                $ad = $model->getById($adId);
                                if ($ad) {
                                    $this->addAdToResultArray($ad,$matches[2],true);
                                    $numberofPremium++;
                                }
                            }
                        }
                    }
                }*/
                
                if($numberofPremium > 0) {
                    $this->result['p']=[$numberofPremium,$premiumGap];
                }
            }
        }
    }
    
    
    function addAdToResultArray($ad, $isFeatured=0, $isPremium=false) {
        unset($ad[Classifieds::TITLE]);
        unset($ad[Classifieds::ALT_TITLE]);

        unset($ad[Classifieds::CANONICAL_ID]);
        unset($ad[Classifieds::CATEGORY_ID]);
        unset($ad[Classifieds::SECTION_NAME_AR]);
        unset($ad[Classifieds::SECTION_NAME_EN]);
        unset($ad[Classifieds::HELD]);

        $emails = $ad[Classifieds::EMAILS];          
       
        $this->cutOfContacts($ad[Classifieds::CONTENT]);   

        $ad[Classifieds::CONTENT] = strip_tags($ad[Classifieds::CONTENT]);

        if($ad[Classifieds::ALT_CONTENT]!="") {
            $this->cutOfContacts($ad[Classifieds::ALT_CONTENT]);
            $ad[Classifieds::ALT_CONTENT] = strip_tags($ad[Classifieds::ALT_CONTENT]);
        }

        if (!empty($emails)) {
            $j=0;
            $email_regex='';
            foreach ($emails as $email) {
                if($j++)$email_regex.='|';
                $email_regex .= addslashes($email);
            }

            //check if email still exists after stripping phone numbers
            $strpos = strpos($ad[Classifieds::CONTENT], $email);
            if($strpos) {
                $ad[Classifieds::CONTENT] = trim(substr($ad[Classifieds::CONTENT],0, $strpos));
                $ad[Classifieds::CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::CONTENT]));
            }

            if($ad[Classifieds::ALT_CONTENT]!="") {
                $strpos = strpos($ad[Classifieds::ALT_CONTENT], $email);
                if($strpos) {
                    $ad[Classifieds::ALT_CONTENT] = trim(substr($ad[Classifieds::ALT_CONTENT],0, $strpos));
                    $ad[Classifieds::ALT_CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::ALT_CONTENT]));
                }
            }

        }

        $this->result['d'][] = [
            $ad[Classifieds::ID],//0
            $ad[Classifieds::PUBLICATION_ID],//1
            $ad[Classifieds::COUNTRY_ID],//2
            $ad[Classifieds::CITY_ID],//3
            $ad[Classifieds::PURPOSE_ID],//4
            $ad[Classifieds::ROOT_ID],//5
            $ad[Classifieds::CONTENT],//6
            $ad[Classifieds::RTL],//7
            $ad[Classifieds::DATE_ADDED],//8
            $ad[Classifieds::SECTION_ID],//9
            $ad[Classifieds::COUNTRY_CODE],//10
            $ad[Classifieds::UNIXTIME],//11
            $ad[Classifieds::EXPIRY_DATE],//12
            $ad[Classifieds::URI_FORMAT],//13
            $ad[Classifieds::LAST_UPDATE],//14
            $ad[Classifieds::LATITUDE],//15
            $ad[Classifieds::LONGITUDE],//16
            $ad[Classifieds::ALT_CONTENT],//17
            $ad[Classifieds::USER_ID],//18
            isset($ad[Classifieds::PICTURES]) && count($ad[Classifieds::PICTURES]) ? $ad[Classifieds::PICTURES] : "",//19
            isset($ad[Classifieds::VIDEO]) ? $ad[Classifieds::VIDEO] : "",//20
            isset($ad[Classifieds::EXTENTED_AR]) ? $ad[Classifieds::EXTENTED_AR] : "",//21
            isset($ad[Classifieds::EXTENTED_EN]) ? $ad[Classifieds::EXTENTED_EN] : "",//22
            isset($ad[Classifieds::LOCALITY_ID]) ? $ad[Classifieds::LOCALITY_ID] : 0,//23
            isset($ad[Classifieds::LOCALITIES_AR]) ? $ad[Classifieds::LOCALITIES_AR] : "",//24
            isset($ad[Classifieds::LOCALITIES_EN]) ? $ad[Classifieds::LOCALITIES_EN] : "",//25
            isset($ad[Classifieds::USER_LEVEL]) ? $ad[Classifieds::USER_LEVEL] : 0,//26
            isset($ad[Classifieds::LOCATION]) ? $ad[Classifieds::LOCATION] : "",//27
            isset($ad[Classifieds::PICTURES_DIM]) && count($ad[Classifieds::PICTURES_DIM]) ? $ad[Classifieds::PICTURES_DIM] : "",//28
            $ad[Classifieds::TELEPHONES], //29
            $ad[Classifieds::EMAILS],//30
            //featured flag
            $isFeatured+0,//31
            isset($ad[Classifieds::CONTACT_INFO]) ? $ad[Classifieds::CONTACT_INFO] : "",//32 (revise for production)
            isset($ad[Classifieds::CONTACT_TIME]) ? $ad[Classifieds::CONTACT_TIME] : "",//33 (revise for production)
            isset($ad[Classifieds::PUBLISHER_TYPE]) ? $ad[Classifieds::PUBLISHER_TYPE] : 0,//34
            $isPremium ? 1:0,//35
            isset($ad[Classifieds::PRICE]) ? $ad[Classifieds::PRICE] : 0//36
        ];
    }


    function adPostingPreferences() {
        include_once $this->config['dir'].'/core/lib/MCPostPreferences.php';
        $pref = new MCPostPreferences();
        $this->result['version']=$pref->getVersion();
        $dataVersion = filter_input(INPUT_GET, 'version', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        if ($dataVersion != $pref->getVersion()) {
            $pref->setup();
            $this->result['d']=$pref;    
            return;
        }
        $this->result['no-change']=1;        
    }
    
    
    function reloadIndex() {
        include_once $this->config['dir'] . '/core/lib/SphinxQL.php'; 
        $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
        $index_name = filter_input(INPUT_GET, 'index', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        if (strlen($index_name)>0) {
            $partition = substr($index_name, -1);
            $this->result['d']=$sphinx->rotate($partition, $index_name);
        }
    }
    
    
    function sphinxTotalsQL() {
        if ($this->countryId==0) return;
        $apiMemVersion = $this->db->getCache()->incrBy('api-mem-version', 0);

        if (!is_numeric($apiMemVersion)) {
            $this->db->getCache()->incr('api-mem-version');
            $apiMemVersion=1;
        }
        $this->result['version'] = $apiMemVersion+0;

        $dataVersion = filter_input(INPUT_GET, 'v', FILTER_VALIDATE_INT)+0;
        if ($dataVersion>0 && $dataVersion==$apiMemVersion) {
            $this->result['no-change']=1;
            return;
        }

        $rootId = filter_input(INPUT_GET, 'root', FILTER_VALIDATE_INT)+0;
        $sectionId = filter_input(INPUT_GET, 'section', FILTER_VALIDATE_INT)+0;
        $purposeId = filter_input(INPUT_GET, 'purpose', FILTER_VALIDATE_INT)+0;
        $tagId = filter_input(INPUT_GET, 'tag', FILTER_VALIDATE_INT)+0;
        $localityId = filter_input(INPUT_GET, 'locality', FILTER_VALIDATE_INT)+0;

        $MCKey="API-{$apiMemVersion}-{$this->countryId}-{$this->cityId}-{$rootId}-{$sectionId}-{$purposeId}-{$tagId}-{$localityId}";

        $cached = $this->db->getCache()->get($MCKey);

        if ($cached) {
            $this->result['d']=$cached;
            return;
        }

        include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
 
        $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
        $sphinx->setLimits(0, 1000);

        $group = '';

        if ($this->countryId) {
            $sphinx->setFilter('country', $this->countryId);
            $group='root_id';
            $sphinx->setFacet('root_id');
        }

        if ($this->cityId) {
            $sphinx->setFilter('city', $this->cityId);
            $group='root_id';
            $sphinx->setFacet('root_id');
        }

        if ($rootId) {
            $sphinx->setFilter('root_id', $rootId);
            $group='section_id';
        }

        if ($sectionId) {
            $sphinx->setFilter('section_id', $sectionId);
            $group = "purpose_id";
            $sphinx->clearFacets();
        }

        if ($tagId) {
            $sphinx->setFilter('section_tag_id', $tagId);
            $group = "purpose_id";
        }

        if ($localityId) {
            $sphinx->setFilter('locality_id', $localityId);
            $group = "purpose_id";
        }

        if ($purposeId) {
            $sphinx->setFilter('purpose_id', $purposeId);
        }

        if(!empty($group)) $sphinx->setGroupBy ($group);

        if ($sectionId==0) {
            $sphinx->setFacet("{$group}, purpose_id", TRUE);
            if ($rootId==1 && $this->countryId>0) {
            }
        } 
        else {
        }
        
        $sphinx->setSelect("groupby() as {$group}, count(*)");
        $query = $sphinx->query("", MYSQLI_ASSOC);
        if ($sphinx->getLastError()) {
            $this->result['e']=$sphinx->getLastError();
            return;
        }

        $this->result['query']=$query;
        $purposes = array();
        
        if ($sectionId>0) {
            foreach ($query['matches'] as $item) {
                $this->result['d'][]=[$item['purpose_id']+0, $item['count(*)']+0];
            }
        } 
        else {
            foreach ($query['matches'][1] as $item) {
                $purposes[$item[$group]+0][]=[$item['purpose_id']+0, $item['count(*)']+0];
            }

            $sections=[];
            foreach ($query['matches'][0] as $item) {
                $_id = $item[$group]+0;
                $rs = [$_id, $item['count(*)']+0, isset($purposes[$_id]) ? $purposes[$_id] : []];

                if (($rootId==1||$rootId==2) && $sectionId==0 && $this->countryId>0) {
                    $sections[]=$_id;
                }

                $this->result['d'][]=$rs;
                unset($rs);
            }
        }

        if ($sectionId==0 && $this->countryId>0) {
            if ($rootId==1) {
                $locs = $this->sphinxLocalitiesQL($sections, $sphinx);
                $num = count($this->result['d']);
                for ($i=0; $i<$num; $i++) {
                    if (isset($locs[$this->result['d'][$i][0]])) {
                        $this->result['d'][$i][]=$locs[$this->result['d'][$i][0]];
                    } 
                    else {
                        $this->result['d'][$i][]=[];
                    }
                }
            }

            if ($rootId==2) {
                $tags = $this->sphinxTagsQL($sections, $sphinx);

                $num = count($this->result['d']);
                for ($i=0; $i<$num; $i++) {
                    if (isset( $tags[$this->result['d'][$i][0]] )) {
                        $this->result['d'][$i][] = $tags[ $this->result['d'][$i][0] ];
                    } 
                    else {
                        $this->result['d'][$i][]=[];
                    }
                }
            }
        }

        $this->db->getCache()->setEx($MCKey, $this->config['ttl_short'], $this->result['d']);
    }


    function sphinxTagsQL($sections, $sphinx) {
        $arr=[];
        $q = "select groupby(), count(*), group_concat(purpose_id), section_id from {$this->config['search_index']} where hold=0 and canonical_id=0 and section_id=%sectionId% ";
        if ($this->countryId) $q.="and country={$this->countryId} ";
        if ($this->cityId) $q.="and city={$this->cityId} ";
        $q.=" group by section_tag_id limit 1000";

        $batch="";
        foreach ($sections as $sectionId) {
            $batch.= preg_replace('/%sectionId%/', $sectionId, $q) . ";\n";
        }
        
        $query = $sphinx->search($batch);
        $matches_count = count($query['matches']);
        for ($i=0; $i<$matches_count; $i++) {
            if (!isset($query['matches'][$i]) && !isset($msg)) {
                $msg = var_export($batch, TRUE);
            }
            $group_count = count($query['matches'][$i]);
            for ($g=0; $g<$group_count; $g++) {
                if (!isset($query['matches'][$i][$g]) && !isset($msg)) {
                    $msg = var_export($batch, TRUE);
                }
                $sectionId = intval($query['matches'][$i][$g]['section_id']);
                if (!isset($arr[ $sectionId ])) { $arr[$sectionId] = []; }
                $row=[intval($query['matches'][$i][$g]['groupby()']), $query['matches'][$i][$g]['count(*)']+0, []];
                foreach (array_unique(explode(',', $query['matches'][$i][$g]['group_concat(purpose_id)'])) as $purposeId) {
                    $row[2][]=$purposeId+0;
                }
                $arr[$sectionId][]=$row;
            }
     
        }
        
        if (isset($msg)) { error_log (__FUNCTION__ . ' ' . $msg); }
        
        return $arr;
    }

    

    function sphinxLocalitiesQL($sections, $sphinx) {
        $arr=[];
        $q = "select groupby() AS locality_id, count(*), group_concat(purpose_id), section_id from {$this->config['search_index']} "
        . "where hold=0 and canonical_id=0 and section_id=%sectionId% ";
        if ($this->countryId) $q.="and country={$this->countryId} ";
        if ($this->cityId) $q.="and city={$this->cityId} ";
        $q.=" group by locality_id limit 0,1000";
        
        $batch="";
        foreach ($sections as $sectionId) {
            $batch.= preg_replace('/%sectionId%/', $sectionId, $q) . ";\n";
        }

        $query = $sphinx->search($batch);

        $matches_count = count($query['matches']);
        
        for ($i=0; $i<$matches_count; $i++) {
            $group_count = count($query['matches'][$i]);
            for ($g=0; $g<$group_count; $g++) {
                $sectionId = $query['matches'][$i][$g]['section_id']+0;
                if (!isset($arr[ $sectionId ])) {
                    $arr[$sectionId] = [];
                }
                $row=[$query['matches'][$i][$g]['locality_id']+0, $query['matches'][$i][$g]['count(*)']+0,[]];
                foreach (array_unique(explode(',', $query['matches'][$i][$g]['group_concat(purpose_id)'])) as $purposeId) {
                    $row[2][]=$purposeId+0;
                }
                $arr[$sectionId][]=$row;
            }
        }
        return $arr;
    }
    


    function userStatus(&$status, &$name=null, $device_name=null) {
        $name=null;
        $status = 0;
        $opts = new \stdClass();
        $opts->disallow_purchase = 0;
           
        if (!empty($this->uuid) && $this->getUID()>0 && $this->user->getID()==$this->getUID()) {            
            $opts->prefs = $this->user->device->getPreferences();
            $opts->device_last_visit = $this->user->device->getLastVisitedUnixtime();
            $opts->user_last_visit = $this->user->getLastVisitUnixtime();
            $opts->user_status = $this->user->getMobile()->getStatus();
            $opts->user_level = $this->user->getLevel();
            $opts->secret = $this->user->getMobile()->getSecret();
            $opts->phone_number = $this->user->getMobile()->getNumber();
            $opts->disallow_purchase = !$this->user->device->isPurchaseEnabled();
            $opts->cuid = $this->user->device->getChangedToUID();
            $opts->full_name = $this->user->getFullName();
            $opts->email = $this->user->getUserMail() ? $this->user->getUserMail() : $this->user->getEMail();                
            $opts->push = $this->user->device->getToken();
            $opts->appVersion = $this->user->device->getAppVersion();
                
            if (in_array($this->user->getProvider(), ['mourjan','facebook','twitter','yahoo','google','live','linkedin'])) {
                $opts->provider = $this->user->getProvider();
                if($opts->provider=='mourjan') {
                    $opts->account = $this->user->getProviderIdentifier();
                }
                else if($opts->provider=='twitter') {
                    $opts->account = preg_replace('/http(?:s|)::\/\/twitter\.com\//', '', $this->user->getProfileURL());
                }
                else {
                    $opts->account = $this->user->getEMail();
                }
            }
                
            $opts->suspend = $this->user->isSuspended() ? time() + $this->user->getMobile()->getSuspendSeconds() : 0;
                
            if ($this->user->getLevel()!=5) {
                $status = 1;
                $name = $this->user->getFullName(); 
            }
            else {
                $status = 9;
                $name = '';
            }                    
        }
        else {
            $status = -9;
        }
        
        return $opts;               
    }
    

    function clearWebuserDeviceRecord($uid=0) {
        if($uid > 0) {      
            //delete subscriptions
            $this->db->get("delete from subscription where web_user_id=?", [$uid]);            
            
            // delete favorites and update index
            $q="update web_users_favs set deleted=1 where web_user_id=? and deleted=0 returning ad_id";
            $rs = $this->db->get($q, [$uid], true);
            if ($rs && is_array($rs) && count($rs)>0) {
                include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
                
                $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id=?";
                $st = $this->db->getInstance()->prepare($q); 
                
                foreach ($rs as $rec) {                     
                    if ($st->execute([$rec['AD_ID']])) { 
                        if ($users=$st->fetch(PDO::FETCH_NUM)) {
                            $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$rec['AD_ID']}";
                        } 
                        else {
                            $q = "update {$this->config['search_index']} set starred=() where id={$rec['AD_ID']}";   
                        }
                        $sphinx->directUpdateQuery($q);
                    }
                }
                $sphinx->close();
                $st->closeCursor();
            }
            //delete active ads
            $this->db->get("update ad a set a.hold=1 where a.hold=0 and ((select d.web_user_id from ad_user d where d.id=?)=?)", [$uid, $uid], true);
            
            //delete ad_user ads
            $this->db->get("update ad_user set state=8 where web_user_id=?", [$uid]);
        }
    }
    

    function editFavorites() {      
        $this->userStatus($status);
        if ($status==1) {
            $adid = filter_input(INPUT_GET, 'adid', FILTER_VALIDATE_INT)+0;
            $state = filter_input(INPUT_GET, 'del', FILTER_VALIDATE_INT)+0;
            $note = filter_input(INPUT_GET, 'note', FILTER_SANITIZE_STRING, ['options'=>['default'=>""]]);
            $flag = filter_input(INPUT_GET, 'flag', FILTER_VALIDATE_INT)+0;
                        
            if ($adid) {
                $this->db->setWriteMode();
                $succeed=false;
                
                switch ($flag) {
                    case 0:
                        // Favorite Only
                        $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id) returning id";
                        $rs = $this->db->get($q, [$this->uid, $adid, $state], TRUE);

                        if ($rs && is_array($rs) && count($rs)==1) {
                            include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                            $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                            $users = $this->db->get("select list(web_user_id) ULIST from web_users_favs where deleted=0 and ad_id=?", [$adid], TRUE);
                            if ($users && is_array($users)) {
                                if (count($users)) {
                                    $q = "update {$this->config['search_index']} set starred=({$users[0]['ULIST']}) where id={$adid}";
                                } 
                                else {
                                    $q = "update {$this->config['search_index']} set starred=() where id={$adid}";   
                                }
                                $succeed= $sphinx->directUpdateQuery($q);
                            }
                    
                            if (!$succeed) {
                                $this->result['e'] = 'Could not add this advert to our search engine';
                            }               

                            $this->result['d']['id']=$rs[0]['ID']+0;

                        } 
                        else {
                            $this->result['d']=0;
                            $this->result['e']='Unable to add this advert to your favorite list';
                        }
                
                        break;
                    
                    case 1:
                        // Note and Farorite
                        $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id) returning id";
                        $rs = $this->db->get($q, [$this->uid, $adid, $state], TRUE);
                        
                        if ($rs && is_array($rs) && count($rs)==1) {
                            include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                            $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                            $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id={$adid}";
                            $st = $this->db->getInstance()->query($q);                    
                            if ($st) {
                                if ($users=$st->fetch(PDO::FETCH_NUM)) {
                                    $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$adid}";
                                } 
                                else {
                                    $q = "update {$this->config['search_index']} set starred=() where id={$adid}";   
                                }
                                $succeed= $sphinx->directUpdateQuery($q);
                            }
                    
                            if (!$succeed) {
                                $this->result['e'] = 'Could not add this advert to our search engine';
                            } 
                            else {
                                $n = $this->db->queryResultArray(
                                    "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,?) matching(web_user_id, ad_id) returning id", 
                                    [$this->uid, $adid, $note, $state], true);  
                            }

                            $this->result['d']['id']=$rs[0]['ID']+0;

                        } 
                        else {
                            $this->result['d']=0;
                            $this->result['e']='Unable to add this advert to your favorite list';
                        }
                        
                        break;
                    
                    case 2:
                        // Note Only
                        $this->db->queryResultArray(
                            "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,?) matching(web_user_id, ad_id) returning id", 
                            [$this->uid, $adid, $note, $state], true);  
                        
                        break;

                    default:
                        break;
                }                             
            }
        }
    }


    function bookMark() { 
        $this->userStatus($status);

        if ($status==1) {
            $wId = filter_input(INPUT_GET, 'wid', FILTER_VALIDATE_INT)+0;
            $delete = filter_input(INPUT_GET, 'del', FILTER_VALIDATE_INT)+0;

            $countryId = filter_input(INPUT_GET, 'country_id', FILTER_VALIDATE_INT)+0;
            $cityId = filter_input(INPUT_GET, 'city_id', FILTER_VALIDATE_INT)+0;
            $sectionId = filter_input(INPUT_GET, 'section_id', FILTER_VALIDATE_INT)+0;
            $section_tag_id = filter_input(INPUT_GET, 'tag_id', FILTER_VALIDATE_INT)+0;
            $locality_id = filter_input(INPUT_GET, 'loc_id', FILTER_VALIDATE_INT)+0;
            $purpose_id = filter_input(INPUT_GET, 'purpose_id', FILTER_VALIDATE_INT)+0;
            $terms = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            $pt = filter_input(INPUT_GET, 'publisher_type', FILTER_VALIDATE_INT)+0;

            $this->db->setWriteMode();
            if ($delete!=1) {
                $rs = $this->db->get(
                        "update or insert into SUBSCRIPTION "
                        . "(WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, TITLE, ADDED, EMAIL, PUBLISHER_TYPE) "
                        . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, 0, ?) "
                        . "matching (WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, PUBLISHER_TYPE) "
                        . "returning id", [$this->uid, $countryId, $cityId, $sectionId, $section_tag_id, $locality_id, $purpose_id, $terms, '', $pt], TRUE);

                if ($rs && is_array($rs) && count($rs)==1) {
                    $this->result['d']['id']=$rs[0]['ID']+0;
                } 
                else {
                    $this->result['d']['id']=0;
                    $this->result['e']='Unable to add to your watch list';
                }
            } 
            else {
                if ($wId>0) {
                    $this->db->get("delete from SUBSCRIPTION WHERE id=? and web_user_id=?", [$wId, $this->uid], TRUE);
                    $this->result['d']['id']=$wId;
                }
            }
        }
    }


    function watchList() {
        $this->userStatus($status);

        if ($status==1) {
            $rs = $this->db->queryResultArray(
                    "select subs.id, subs.country_id, subs.city_id, subs.section_id, "
                    . "subs.section_tag_id, subs.locality_id, subs.purpose_id, subs.query_term, "
                    . "country.name_ar, country.name_en, "
                    . "IIF(city.name_ar is null, '', city.name_ar), iif(city.name_en is null, '', city.name_en), "
                    . "section.name_ar, section.name_en, "
                    . "purpose.name_ar, purpose.name_en, "
                    . "IIF(tag_ar.name is null, '', tag_ar.name), IIF(tag_en.name is null, '', tag_en.name), "
                    . "IIF(geo_ar.name is null, '', geo_ar.name), IIF(geo_en.name is null, '', geo_en.name), "
                    . "subs.badge_count "
                    . "from subscription subs "
                    . "left join country on country.id=subs.country_id "
                    . "left join city on city.id=subs.city_id "
                    . "left join section on section.id=subs.section_id "
                    . "left join section_tag tag_en on tag_en.id=subs.section_tag_id and tag_en.lang='en' "
                    . "left join section_tag tag_ar on tag_ar.id=tag_en.ALTER_ID and tag_ar.lang='ar' "
                    . "left join geo_tag geo_en on geo_en.id=subs.locality_id and geo_en.lang='en' "
                    . "left join geo_tag geo_ar on geo_ar.id=geo_en.ALTER_ID and geo_ar.lang='ar' "
                    . "left join purpose on purpose.id=subs.purpose_id "
                    . "where subs.web_user_id=? "
                    . "order by subs.id  ", [$this->uid], TRUE, PDO::FETCH_NUM);
            $this->result['d']=$rs;
        }
    }


    function watchListVisited() {
        $wId = filter_input(INPUT_GET, 'wid', FILTER_VALIDATE_INT)+0;
        if ($wId) {
            $this->db->setWriteMode();
            $this->db->get("update subscription set badge_count=0, last_visit=current_timestamp where id=?", [$wId], TRUE);
        }
    }


    function register() {
       
        $current_name="";
        
        $device_name = filter_input(INPUT_GET, 'dn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);        
        if (strlen($device_name) > 50) {
            $device_name = substr($device_name, 0, 50);
        }
        
        $device_model = filter_input(INPUT_GET, 'dm', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        if (strlen($device_model) > 50) {
            $device_model = substr($device_model, 0, 50);
        }

        if ($device_model=='Calypso AppCrawler') {
            //error_log("Calypso AppCrawler {$this->uuid}");
            $this->uid = 284300;
            $this->uuid = '31D052EF-DCC8-4FBA-B180-4C7C50AECBC6';
        }

        if (strlen($this->systemName) > 50) {
            $this->systemName = substr($this->systemName, 0, 50);
        }
        
        $isAndroid = $this->isAndroid();
        $this->provider = $isAndroid ? Core\Model\ASD\USER_PROVIDER_ANDROID : Core\Model\ASD\USER_PROVIDER_IPHONE;
        
        $device_sysversion = filter_input(INPUT_GET, 'sv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $carrier_country = filter_input(INPUT_GET, 'cc', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_appversion = filter_input(INPUT_GET, 'bv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $app_prefs = html_entity_decode(filter_input(INPUT_GET, 'prefs', FILTER_SANITIZE_STRING, ['options'=>['default'=>'{}']]));
        
        //Android Fix for lost UID
        if ($isAndroid  && $this->uuid){
            if ($this->uid==0 && $this->user->getID()>0) {
                //error_log("Verifying if previous record exists for UUID {$this->uuid} with UID NIL\n");
                $_device = NoSQL::getInstance()->deviceFetch($this->uuid);

                if ($_device && isset($_device[\Core\Model\ASD\USER_DEVICE_SYS_NAME]) && $_device[\Core\Model\ASD\USER_DEVICE_SYS_NAME]=='Android') {
                    $this->uid = $_device[\Core\Model\ASD\USER_UID];
                    $this->provider = Core\Model\ASD\USER_PROVIDER_ANDROID;   
                }
            }
            else if ($this->uid != 0 && $this->user->getID() > 0 && $this->uid != $this->user->getID()) {
                error_log("fix for UUID {$this->uuid} with corrupted uid\n");
                $_device = NoSQL::getInstance()->deviceFetch($this->uuid);

                if ($_device && isset($_device[\Core\Model\ASD\USER_DEVICE_SYS_NAME]) && $_device[\Core\Model\ASD\USER_DEVICE_SYS_NAME]=='Android') {                    
                    $oldUid = $this->uid;
                    
                    $this->uid = $_device[\Core\Model\ASD\USER_UID];
                    $this->provider = Core\Model\ASD\USER_PROVIDER_ANDROID;   
                    
                    NoSQL::getInstance()->deviceSetUID($this->uuid, $this->uid, $oldUid);
                    
                    $this->user = MCUser::getByUUID($this->uuid);
                }
            }
        }
        //End of Android Fix for lost UID
                
        $opts = $this->userStatus($status, $current_name, $device_name);
        $this->result['status']=9;
        $this->result['d']['level'] = isset($opts->user_level) ? $opts->user_level:0;
        
        if ($this->isIOS()) {
            $this->result['d']['duid'] = $this->user->getID();
            $this->result['d']['aepoch'] = $this->user->getMobile()->getNumber() ? $this->user->getMobile()->getActicationUnixtime() : 0;
            $this->result['d']['sepoch'] = time();
            $this->result['d']['cdn'] = 'https://c6.mourjan.com';
            $this->result['d']['upload'] = 'https://www.mourjan.com';
            $this->result['d']['detail_ad_unit'] = 'ca-app-pub-2427907534283641/4303349312';
            $this->result['d']['listing_ad_unit'] = 'ca-app-pub-2427907534283641/8260964224';            
            
            if ($this->user->getMobile()->getNumber()) {
                $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                $num = $this->mobileValidator->parse("+{$this->user->getMobile()->getNumber()}", 'LB');
                $this->result['d']['amcc'] = $this->mobileValidator->getRegionCodeForNumber($num);
            }                        
        }
        
        
        if($isAndroid) {
            //setting app params
            $this->result['d']['u_up'] = $this->config['android_url_upload'];
            $this->result['d']['u_web'] = $this->config['android_url_web'];
            $this->result['d']['u_img'] = $this->config['android_url_img'];
            $this->result['d']['u_xx'] = $this->config['android_url_img_xx'];
            $this->result['d']['u_api'] = $this->config['android_url_api'];
            $this->result['d']['u_nas'] = $this->config['android_url_node_ad_stage'];
            $this->result['d']['e_support'] = $this->config['android_email_support'];
            
            if($device_sysversion < '6'){
                $this->result['d']['u_img'] = 'https://doxplxe8wce37.cloudfront.net/repos';
                unset($this->result['d']['u_xx']);
            }
            
            if($device_appversion > '1.3.0') {
                $this->result['d']['a_release'] = $this->config['android_app_release'];
                $this->result['d']['a_rel_en'] = '';
                $this->result['d']['a_rel_ar'] = '';
                foreach ($this->config['android_releases_en'] as $release => $msg) {
                    if($device_appversion < $release) {
                        if($this->result['d']['a_rel_en']!='') {
                            $this->result['d']['a_rel_en'].='<br><br>';
                        }
                        $this->result['d']['a_rel_en'] .= $msg;
                    }
                }
                foreach ($this->config['android_releases_ar'] as $release => $msg) {
                    if($device_appversion < $release) {
                        if($this->result['d']['a_rel_ar']!='') {
                            $this->result['d']['a_rel_ar'].='<br><br>';
                        }
                        $this->result['d']['a_rel_ar'] .= $msg;
                    }
                }
                
            }
            else {
                $this->result['d']['a_release'] = '1.0.0';
            }
            
            $this->result['d']['a_force'] = $this->config['android_app_release_enforce'];
            $this->result['d']['ed'] = $this->config['android_enabled_banner_detail']+0;
            $this->result['d']['edn'] = $this->config['android_enabled_banner_detail_native']+0;
            $this->result['d']['es'] = $this->config['android_enabled_banner_search']+0;
            $this->result['d']['esn'] = $this->config['android_enabled_banner_search_native']+0;
            $this->result['d']['ee'] = $this->config['android_enabled_banner_exit']+0;
            $this->result['d']['epi'] = $this->config['android_enabled_banner_pending']+0;
            $this->result['d']['edi'] = $this->config['android_enabled_banner_detail_inter']+0;
            $this->result['d']['esl'] = $this->config['android_enabled_banner_search_native_list']+0;
            $this->result['d']['eslf'] = $this->config['android_banner_search_native_list_first_idx']+0;
            $this->result['d']['eslg'] = $this->config['android_banner_search_native_list_gap']+0;
            $this->result['d']['eslz'] = $this->config['android_banner_search_native_list_freq']+0;
            $this->result['d']['evc'] = $this->config['android_enabled_cli_verification']+0;
            $this->result['d']['evrc'] = $this->config['android_enabled_reverse_cli_verification']+0;
            $this->result['d']['evs'] = $this->config['android_enabled_sms_verification']+0;
            if($device_appversion < '1.4.8' && $device_appversion != '1.8.8') {                
                $this->result['d']['edn'] = 0;
                $this->result['d']['esn'] = 0;
                $this->result['d']['esl'] = 0;
            }
            if($device_appversion > '1.5.8'){
                $this->result['d']['lgt'] = $this->config['android_legacy_text_processing'];
            }
            if($device_appversion > '1.4.7' && $device_appversion != '1.8.8'){
                $this->result['d']['esl'] = $this->config['android_enabled_banner_search_native_list']+0;
                $this->result['d']['eslf'] = $this->config['android_banner_search_native_list_first_idx']+0;
                $this->result['d']['eslg'] = $this->config['android_banner_search_native_list_gap']+0;
                $this->result['d']['eslz'] = $this->config['android_banner_search_native_list_freq']+0;
            }
            if (isset($opts->push)) {
                $this->result['d']['push'] = $opts->push;
            }
            
            //check if android user has mobile validated
            if($this->getUID()) {
                $_mobile = $this->user->getMobile();
                if ($_mobile && $_mobile->isVerified()) {
                    $this->result['d']['mobile']=$_mobile->getNumber();
                    if(trim($this->result['d']['mobile'])==''){
                        error_log('EMPTY MOBILE IS VALID FOR UID '.$this->getUID());
                    }
                }
            }
        }

        if (empty($carrier_country)) {
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $databaseFile = '/home/db/GeoLite2-City.mmdb';
            $reader = new Reader($databaseFile);
            $geo = $reader->get($ip);
            $reader->close();
        
            if ($geo && isset($geo['country'])) {
                $country_code = trim(strtoupper(trim($geo['country']['iso_code'])));
                if (strlen($country_code)!=2) {
                    $country_code='';
                }
            }
            
            $carrier_country = (isset($geo['country']['iso_code']) && strlen(trim($geo['country']['iso_code']))==2) ? strtoupper(trim($geo['country']['iso_code'])) : 'XX';
        }
        
        if ($status==1) {
            /* opts->user_status
             * 9: retired
             * 10: does not have web_users_mobile record (not activated mobile user)
             */
            $isUTF8 = preg_match('//u', $device_name);
                
            if (NoSQL::getInstance()->deviceInsert([
                    Core\Model\ASD\USER_DEVICE_UUID => $this->uuid,
                    Core\Model\ASD\USER_UID => $this->getUID(),
                    Core\Model\ASD\USER_DEVICE_MODEL => $device_model,
                    Core\Model\ASD\USER_DEVICE_NAME => ($isUTF8 ? $device_name : ''),
                    Core\Model\ASD\USER_DEVICE_SYS_NAME => $this->systemName,
                    Core\Model\ASD\USER_DEVICE_SYS_VERSION => $device_sysversion,             
                    Core\Model\ASD\USER_DEVICE_ISO_COUNTRY => $carrier_country,
                    Core\Model\ASD\USER_DEVICE_APP_VERSION => $device_appversion,
                    Core\Model\ASD\USER_DEVICE_APP_SETTINGS => $app_prefs
                ])){
            }      
            
            if($isAndroid) {
                $this->result['d']['uid']=  $this->getUID();
                $this->result['d']['dlv'] = $opts->device_last_visit+0;
                $this->result['d']['ulv'] = $opts->user_last_visit+0;
                $this->result['d']['level'] = $opts->user_level+0;
                if(isset($opts->provider)) {
                    $this->result['d']['provider']=$opts->provider;
                }
                else {
                    $this->result['d']['provider']='';
                }
                //account name
                if(isset($opts->account)) {
                    $this->result['d']['account']=$opts->account;
                }
                else {
                    $this->result['d']['account']='';
                }

                if ($this->user->isSuspended()) {
                    $this->result['d']['suspend'] = time()+$this->user->getSuspensionTime();
                }               
            }
            
            $this->result['d']['blp'] = $opts->disallow_purchase+0;            
            $this->result['d']['status']=$opts->user_status;
            $this->result['d']['pwset']=!empty($opts->secret);
            
            if ($opts->cuid>0) {
            	include_once $this->config['dir'] .'/core/model/User.php';
                
                $user = new User($this->db, $this->config, null, 0);
                        
                $ok = $user->mergeDeviceToAccount($this->uuid, $this->getUID(), $opts->cuid);
                if ($ok) {
                    $this->uid=$opts->cuid;
                    $opts = $this->userStatus($status);
                    $this->result['d']['pwset']=!empty($opts->secret);
                    $this->result['d']['uid']=$opts->cuid;
                }                
            }

            $uname = filter_input(INPUT_GET, 'uname', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            if ($uname && $uname!=$current_name) {
                NoSQL::getInstance()->modProfile(
                            [\Core\Model\ASD\USER_PROVIDER_ID=>$this->uuid, \Core\Model\ASD\USER_PROVIDER=>$this->provider], 
                            [\Core\Model\ASD\USER_FULL_NAME=>$uname, Core\Model\ASD\USER_DISPLAY_NAME=>$uname]);
            }
            
            if (empty($uname)) {
                NoSQL::getInstance()->updateProfileVisitTime([\Core\Model\ASD\USER_PROVIDER_ID=>$this->uuid, \Core\Model\ASD\USER_PROVIDER=>$this->provider]);
            }
            
            
            if ( $opts->user_status==1) {
                include $this->config['dir'] .'/core/model/User.php';
                if ($this->isIOS() && (session_status()==PHP_SESSION_NONE) && version_compare($this->appVersion, '1.1.0', '<')) {                    
                    new MCSessionHandler(TRUE);
                    
                    $user = new User($this->db, $this->config, null, 0);
                    $user->sysAuthById($this->uid);
                    $user->params['app']=1;
                    $user->update();
                   // error_log(json_encode($_SESSION));
                }
                

                $this->result['d']['kuid'] = User::encodeUID($this->getUID());
                $this->result['d']['uid']=  $this->uid;
                
                $this->getBalance();                
            }
                        
        } 
        elseif (!$this->user->exists() && !empty($this->uuid)) {
            
            $bins = [
                \Core\Model\ASD\USER_PROVIDER_ID=>$this->uuid,
                \Core\Model\ASD\USER_PROVIDER=>$this->provider,
                \Core\Model\ASD\USER_PROFILE_URL=>'https://www.mourjan.com/',                
                ];
            
            if (NoSQL::getInstance()->profileExists($bins)) {
                $ret = NoSQL::getInstance()->getProfileRecord($bins, $record);
                
                if ($ret==NoSQL::OK && !NoSQL::getInstance()->deviceExists($this->uuid)) {
                    //$this->result['e'] = 'System error [1002]!';
                    error_log(__FUNCTION__ . " Device record is missed [1002]: ".json_encode($bins));
                    
                    $this->uid=$record[\Core\Model\ASD\USER_PROFILE_ID];
                    $this->result['d']['uid'] = $this->uid;
                
                    if ($isAndroid) {
                        $this->result['d']['level']=$record[\Core\Model\ASD\USER_LEVEL];
                        $this->result['d']['status']=10;
                    
                        //device last visit
                        $this->result['d']['dlv'] = 0;
                        //user last visit
                        $this->result['d']['ulv'] = 0;
                    }
                    //disallow purchase default 0
                    $this->result['d']['blp'] = 0;
                    
                    $isUTF8 = preg_match('//u', $device_name);
                    if (!NoSQL::getInstance()->deviceInsert([
                        Core\Model\ASD\USER_DEVICE_UUID => $this->uuid,
                        Core\Model\ASD\USER_UID => $this->uid,
                        Core\Model\ASD\USER_DEVICE_MODEL => $device_model,
                        Core\Model\ASD\USER_DEVICE_NAME => ($isUTF8 ? $device_name : ''),
                        Core\Model\ASD\USER_DEVICE_SYS_NAME => $this->systemName,
                        Core\Model\ASD\USER_DEVICE_SYS_VERSION => $device_sysversion,
                        Core\Model\ASD\USER_DEVICE_ISO_COUNTRY => $carrier_country,
                        Core\Model\ASD\USER_DEVICE_APP_VERSION => $device_appversion,
                        Core\Model\ASD\USER_DEVICE_APP_SETTINGS => '{}'
                        ])) {
                        $this->result['e'] = 'System error [1001]!';
                        error_log(__FUNCTION__ . ' DEVIVE ADDED Failed');
                    }
                }
                else {
                    $this->result['e'] = 'System error [1011]!';
                }
            }
            else if (NoSQL::getInstance()->addProfile($bins)==NoSQL::OK) {
                $this->uid = $bins[\Core\Model\ASD\USER_PROFILE_ID];
                $this->result['d']['uid'] = $this->uid;
                
                if ($isAndroid) {
                    $this->result['d']['level']=$bins[\Core\Model\ASD\USER_LEVEL];
                    $this->result['d']['status']=10;
                    
                    //device last visit
                    $this->result['d']['dlv'] = 0;
                    
                    //user last visit
                    $this->result['d']['ulv'] = 0;
                }
                
                //disallow purchase default 0
                $this->result['d']['blp'] = 0;

                $isUTF8 = preg_match('//u', $device_name);
                
                $deviceAdded = NoSQL::getInstance()->deviceInsert([
                        Core\Model\ASD\USER_DEVICE_UUID => $this->uuid,
                        Core\Model\ASD\USER_UID => $this->uid,
                        Core\Model\ASD\USER_DEVICE_MODEL => $device_model,
                        Core\Model\ASD\USER_DEVICE_NAME => ($isUTF8 ? $device_name : ''),
                        Core\Model\ASD\USER_DEVICE_SYS_NAME => $this->systemName,
                        Core\Model\ASD\USER_DEVICE_SYS_VERSION => $device_sysversion,
                        Core\Model\ASD\USER_DEVICE_ISO_COUNTRY => $carrier_country,
                        Core\Model\ASD\USER_DEVICE_APP_VERSION => $device_appversion,
                        Core\Model\ASD\USER_DEVICE_APP_SETTINGS => '{}'
                        ]);                           
            } 
            else {
                $this->result['e'] = 'System error [1010]!';
                error_log(__FUNCTION__ . " could not write [1010]: ".json_encode($bins));                
            }
                               
        }
        
        if ($isAndroid && isset($this->result['d']['uid']) && $this->result['d']['uid']==0) {
            unset($this->result['d']['uid']);
        }
        
        if(false && isset($this->result['d']['uid'])){  
            $path = '/opt/firebase_credentials.json';
            $content = file_get_contents($path);
            $apiKey = '1017340605957-6poi0tsvqoib7e3ig68gvc7uslq83sn0.apps.googleusercontent.com';
            
            $serviceAccount = ServiceAccount::fromValue($content);
            
            $firebase = (new Factory)                    
                ->withServiceAccountAndApiKey($serviceAccount, $apiKey)
                ->create();

            $auth = $firebase->getAuth();

            $customToken = $auth->createCustomToken($this->result['d']['uid']);
            $this->result['d']['fbx'] = '>>>'.$customToken.'<<<';
            //error_log($this->result['d']['fbx']);
        }
        
        if(isset($this->result['d']['uid']) && $this->result['d']['uid']>0) {
            NoSQL::getInstance()->updateProfileVisitTime([Core\Model\ASD\USER_UID=>$this->result['d']['uid']]);
        }
        
        //if ($this->user->getMobile()->getNumber()) {
        //    Audit::signin()->add('register', ['status'=>$this->result['d']['status'], 'error', $this->result['e']])->end();        
        //}
    }


    function setApnsToken() {
        $opts = $this->userStatus($status);
        $this->result['status']=$status;

        if ($status==1 || $status==-9) {
            $token=filter_input(INPUT_GET, 'tk', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                                           
            if (NoSQL::getInstance()->deviceSetToken($this->uuid, $token)) {
                //$this->db->setWriteMode();
                //$this->db->get("update WEB_USERS_DEVICE set PUSH_ID=? where uuid=? and PUSH_ID!=?", [$token, $this->uuid, $token], TRUE);
            }
            else {
                $this->result['e']='Could not register notification token';                
            }
        } 
        else {            
            $this->result['e']='Invalid user status';
        }
        $this->db->close();
    }


    function setNotification() {
        $this->userStatus($status);
        $this->result['status']=$status;
        if ($status==1) {            
            $enabled=filter_input(INPUT_GET, 'enabled', FILTER_VALIDATE_INT)+0;            
            
            if (NoSQL::getInstance()->deviceSetNotificationStatus($this->uuid, $enabled)) {
                //$this->db->setWriteMode();
                //$this->db->get("update WEB_USERS_DEVICE set NOTIFICATION_ENABLED=? where uuid=?", [$enabled, $this->uuid], TRUE);
            }
        }
        $this->db->close();
    }

    
    function setPassword() {   
        $opts = $this->userStatus($status, $current_name);
        if ($status==1) {
            $op=filter_input(INPUT_GET, 'op', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            $np=filter_input(INPUT_GET, 'np', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            $cp=filter_input(INPUT_GET, 'cp', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            
            if (!empty($cp) && !empty($np) && strlen($cp)==32 && ($cp==$np) && ($op==$opts->secret || empty($opts->secret))) {
                if ($this->user->getMobile()->setSecret($np)) {
                   $this->result['d']['status'] = $this->user->getMobile(TRUE)->getStatus();
                   $this->result['d']['pwset'] = !empty($this->user->getMobile()->getSecret());
                   return;
                }
            }
        }
        $this->result['e']='Could not set new password!';
    }


    function authenticate() {
        $opts = $this->userStatus($status);
        $mobile_no = intval(filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT));
        
        if ($status==1) {
            $secret=filter_input(INPUT_GET, 'secret', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

            if ($mobile_no>0 && !empty($secret)) {                
                $userId=0;
                if (NoSQL::getInstance()->mobileVerifySecret($mobile_no, $secret, $userId) && $this->user->getMobile()->getActicationUnixtime()) {
                    $this->result['d']['status']=1;
                    $this->result['d']['uid']=($this->uid!=$userId) ? $userId : 0;
                    if ($this->getUID()!=$userId && $userId>0) {
                        if (NoSQL::getInstance()->deviceSetUID($this->uuid, $userId, $this->getUID())) {
                            $this->db->setWriteMode();
                            $ok = $this->db->get(
                                    "update web_users_favs a set a.web_user_id=? "
                                    . "where a.web_user_id=? "
                                    . "and not exists (select 1 from web_users_favs b "
                                    . "where b.web_user_id=? and b.ad_id=a.ad_id)", [$userId, $this->uid, $userId], true);
                            if ($ok) {
                                $ok = $this->db->get(
                                    "update subscription a set a.web_user_id=? "
                                    . "where a.web_user_id=? and "
                                    . "not exists (select 1 from subscription b "
                                    . "where b.web_user_id=? "
                                    . "and b.country_id=a.country_id and b.city_id=a.city_id and b.section_id=a.section_id "
                                    . "and b.purpose_id=a.purpose_id and b.section_tag_id=a.section_tag_id "
                                    . "and b.locality_id=a.locality_id and b.purpose_id=a.purpose_id and b.query_term=a.query_term)",
                                    [$userId, $this->getUID(), $userId], true);

                                if ($ok) {
                                    
                                    $this->db->get("update T_PROMOTION_USERS t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    $this->db->get("update T_TRAN t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    
                                    $ok = $this->db->get("delete from web_users_favs where web_user_id=?", [$this->uid], true);
                                    if ($ok) {
                                        $ok = $this->db->get("delete from subscription where web_user_id=?", [$this->uid], true);
                                        if ($ok) {
                                            $ok = $this->db->get("delete from web_users where id=?", [$this->uid], true);
                                        }
                                    }
                                }
                            }                

                            if ($ok) {
                                $this->db->commit();
                                $this->uid=$userId;
                                $opts = $this->userStatus($status);
                                $this->result['d']['pwset']=!empty($opts->secret);
                            } 
                            else {
                                $this->db->rollback();
                                $this->result['e']="Could not activate your device due to internal system error!";
                                error_log(__FUNCTION__ . ' ' .$this->result['e'] . " " . $mobile_no . " to uid: " . $userId);
                            }
                        }
                        
                    }
                    return;
                }
            }
                           
            $this->result['e']="Invalid user and password for {$mobile_no}!";
        } 
        else {
            $this->result['e']="Not a valid user and/or password for {$mobile_no}!";
        }

    }

    
    function activate() {   
        $opts = $this->userStatus($status);
        if ($status!=1) {
            $this->result['e'] = 'Invalid user status';
            return;
        }
        
        if ($opts->user_status==9) {
            $this->result['e'] = 'Your account is retired.'.chr(10).'Please remove Mourjan app and install it again to reactivate it.';
            return;
        }

        $this->result['d']['status']='invalid';
        $mobile_no = intval(filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT));                       
        $val_type = intval(filter_input(INPUT_GET, 'vtype',  FILTER_VALIDATE_INT, ["options" => ["default" => 0, "min_range" => 0, "max_range"=>2]]));        
        
        if ($val_type==MobileValidation::REVERSE_CLI_TYPE) {
            $pin_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING, ["options" => ["default" => "0"]]);
            if (is_numeric($pin_code)) {
                $pin_code= intval($pin_code);
            }
            else {
                $pin_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING, ["options" => ["default" => ""]]);
                return;
            }
        }
        else {
            $pin_code = intval(filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING));
        }
                
        try {
            $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
            $num = $this->mobileValidator->parse("+{$mobile_no}", 'LB');
            if($num && $this->mobileValidator->isValidNumber($num)) {
                $numberType = $this->mobileValidator->getNumberType($num);
                if (!($numberType==libphonenumber\PhoneNumberType::MOBILE || $numberType==libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE)) {
                    $this->result['e'] = "+{$mobile_no} is not a valid mobile number!";
                    return;
                }            
            }
            else {
                $this->result['e'] = "+{$mobile_no} is not a valid telephone number!";
                return;
            }

            $mobile_no = intval($this->mobileValidator->format($num, \libphonenumber\PhoneNumberFormat::E164));

            if ($mobile_no<999999) {
                $this->result['e'] = 'Invalid mobile registration request';
                return;
            }
        }
        catch (Exception $ex ) {
            $this->result['e'] = $ex->getMessage();
            return;
        }

        $record = NoSQL::getInstance()->mobileFetch($this->getUID(), $mobile_no);

        if ($record) {       
            
            if (isset($record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]>(time()-31536000)) {
                $this->result['e'] = $this->lang=='ar' ? 'سبق وتم التحقق من رقم الجوال' : 'Mobile number already validated';
                $this->result['d']['status']='validated';
                return;
            }
            
            if ($pin_code) {
                switch ($val_type) {
                    case MobileValidation::CLI_TYPE:
                        if (MobileValidation::getInstance()->verifyStatus($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID])) {
                            $activated = NoSQL::getInstance()->mobileActivationByRequestId($this->getUID(), $mobile_no, $pin_code, $record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]);
                        }
                        else {
                            $this->result['e'] = 'Not a valid activation request';
                            $this->result['d']['status']='invalid';
                            return;
                        }                            
                        break;

                    case MobileValidation::SMS_TYPE:
                    case MobileValidation::REVERSE_CLI_TYPE:                                             
                        $response = MobileValidation::getInstance()->setUID($this->getUID())->verifyEdigearPin($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID], $pin_code);                                                
                        if (isset($response['status']) && $response['status']==200 && isset($response['response'])) {
                            if ($response['response']['validated']) {
                                $activated = NoSQL::getInstance()->mobileActivationByRequestId($this->getUID(), $mobile_no, $pin_code, $record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]);
                            }
                            else {
                                $this->result['e'] = 'Activation code is not valid';
                                $this->result['d']['status']='invalid';
                            }
                        }
                        break;                    
                }
                                                                             
                if (empty($this->result['e'])) {                    
                    if ($activated) {
                        $rec = NoSQL::getInstance()->mobileFetch($this->getUID(), $mobile_no);
                        $this->result['d']['status']='activated';
                        $this->result['d']['aepoch']=$rec[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED];
                        include $this->config['dir'] .'/core/model/User.php';
                        $user = new User($this->db, $this->config, null, 0);
                        $user->sysAuthById($this->uid);
                        $user->params['app']=1;
                        $user->update();
                        $this->result['d']['kuid'] = $user->encodeId($this->uid);
                        $this->getBalance();                        
                    }
                    else {
                        $this->result['e'] = 'This mobile number is used on different device';
                        $this->result['d']['status']='invalid';                        
                    }
                    
                } 
              
                return;
            } // end of pin code received
            

            switch ($val_type) {
                case MobileValidation::CLI_TYPE:
                    $mv_result = MobileValidation::getInstance()->
                        setUID($this->getUID())->
                        setPlatform(MobileValidation::IOS)->
                        sendCallerId($mobile_no);
                
                    switch ($mv_result) {
                        case MobileValidation::RESULT_OK:
                        case MobileValidation::RESULT_ERR_SENT_FEW_MINUTES:
                            //MobileValidation::getInstance()->getIssuedData($id)
                            $record = NoSQL::getInstance()->mobileFetch($this->getUID(), $mobile_no);
                            $this->result['d']['status']='sent';
                            $this->result['d']['dialing_number'] = '+'.$record[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                            $this->result['d']['request_id'] = $record[Core\Model\ASD\USER_MOBILE_REQUEST_ID];
                            $this->result['e'] = '';
                            break;

                        case MobileValidation::RESULT_ERR_ALREADY_ACTIVE:
                            $this->result['d']['status'] = 'validated';
                            $this->result['e'] = 'Hey, your number is already validated...';                        
                            break;
                    
                        default:
                            $this->result['e'] = 'Error, could not complete activation process! Please try again after few seconds...';
                            break;
                    }              
                    return;
            
                case \Core\Model\MobileValidation::REVERSE_CLI_TYPE:
                    $ret = MobileValidation::getInstance()->setUID($this->getUID())->setPlatform(MobileValidation::IOS)->requestReverseCLI($mobile_no, $response);
                    switch ($ret) {
                        case MobileValidation::RESULT_OK:
                        case MobileValidation::RESULT_ERR_SENT_FEW_MINUTES:
                            $this->result['d']['status']='sent';
                            $this->result['d']['pin_hash'] = '';//$response['response']['pin_hash'];  
                            $this->result['d']['request_id'] = $response['response']['id'];
                            if ($this->lang=='ar') {
                                $this->result['d']['message'] = "يرجى عدم الرد او قطع الاتصال". "\n" . "لقد تم طلب الاتصال برقمك، نأمل التحقق من سجل المكالمات الخاص بك لآخر مكالمة لم يرد عليها رقم وأدخل آخر 4 أرقام من هذا الرقم {$response['response']['cli_prefix']}";
                            }
                            else {
                                $this->result['d']['message'] = "PLEASE DO NOT ANSWER OR HANGUP\nA call has been made to your number, please check your mobile call log for the last missed call number and enter the last 4 digits of that number {$response['response']['cli_prefix']}";
                            }
                            if (isset($response['response']['length'])) {
                                $x = substr('xxxxxxxxxxxxxxxxxxxx', -1*($response['response']['length']-9));
                                $this->result['d']['message'].=$x.'XXXX'; // $response['response']['allocated_number'];
                            }
                            if (isset($response['response']['called'])) {
                                $this->result['d']['called']=1;
                            }
                            if ($ret==MobileValidation::RESULT_ERR_SENT_FEW_MINUTES) {
                                $this->result['e'] = 'Error, if you do not receive a call within 3 minutes try again...';    
                            }
                            break;                        
                    
                        case MobileValidation::RESULT_ERR_ALREADY_ACTIVE:
                            $this->result['d']['status'] = 'activated';
                            $this->result['e'] = 'Hey, your account is already active...';
                            break;
                    
                        default:
                            $this->result['e'] = 'Error ['.$ret.'], could not complete activation process! Please try again after few seconds...';
                            break;
                    }                                 
                    return;
            
                case \Core\Model\MobileValidation::SMS_TYPE:
                    
                    if ($record[Core\Model\ASD\USER_MOBILE_SENT_SMS_COUNT]==0 || $record[\Core\Model\ASD\USER_MOBILE_VALIDATION_TYPE]!=MobileValidation::SMS_TYPE) {                        
                        $pin = mt_rand(1000, 9999); 
                        $msg_text = "{$pin} is your mourjan confirmation code";
                        
                        if (MobileValidation::getInstance()->
                                setPlatform(MobileValidation::IOS)->
                                setPin($pin)->setUID($this->getUID())->
                                sendSMS($mobile_no, $msg_text, ['uid'=>$this->getUID()]) == MobileValidation::RESULT_OK) {
                            $this->result['d']['status']='sent';
                        }
                        else {
                            $this->result['d']['status'] = 'Error';
                            $this->result['e'] = 'Failed to send SMS verification code';
                            return;
                        }
                    }


                    if ($record[Core\Model\ASD\USER_MOBILE_CODE_DELIVERED]) {
                        $this->result['e'] = 'An sms has already been delivered with the verification code';
                        $this->result['d']['status']='delivered';
                        return;
                    }

                    if (time()-$record[Core\Model\ASD\USER_MOBILE_DATE_REQUESTED]<120) {
                        $this->result['e'] = 'An sms has already been sent with the verification code.\nPlease wait a few minutes to recieve it';
                        return;
                    }

                    if (!isset($record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $record[Core\Model\ASD\USER_MOBILE_CODE_DELIVERED]==0 && $record[Core\Model\ASD\USER_MOBILE_SENT_SMS_COUNT]>0) {
                        $this->result['e'] = 'Invalid mobile number! Please enter well formed mobile number to proceed.';
                        return;
                    }
                    return;
            }
        } // end of mobile record exists
        else {
            // New mobile record
            //error_log("Three Type: {$val_type}, Pin: {$pin_code}, Mobile:{$mobile_no}");
            // Mobile not exists
            $bins = [\Core\Model\ASD\USER_UID => $this->getUID(), 
                    \Core\Model\ASD\USER_MOBILE_NUMBER => $mobile_no,
                    \Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => 0,
                    \Core\Model\ASD\USER_MOBILE_FLAG => 2];
            
            switch ($val_type) {                
                case MobileValidation::CLI_TYPE: 
                    $ret = MobileValidation::getInstance()->
                        setPlatform(MobileValidation::IOS)->
                        setUID($this->getUID())->
                        sendCallerId($mobile_no);
                    
                    switch ($ret) {
                        case MobileValidation::RESULT_OK:
                        case MobileValidation::RESULT_ERR_SENT_FEW_MINUTES:
                            $record = NoSQL::getInstance()->mobileFetch($this->getUID(), $mobile_no);
                            $this->result['d']['status']='sent';
                            $this->result['d']['dialing_number'] = '+'.$record[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                            $this->result['d']['request_id'] = $record[Core\Model\ASD\USER_MOBILE_REQUEST_ID];
                            $this->result['e'] = '';
                            break;

                        default:
                            $this->result['e'] = 'Error, could not complete activation process! Please try again after few seconds...';
                            break;
                    }                  
                    break;
                
                case MobileValidation::REVERSE_CLI_TYPE:
                    $ret = MobileValidation::getInstance()->setUID($this->getUID())->setPlatform(MobileValidation::IOS)->requestReverseCLI($mobile_no, $response); 
                    //error_log(json_encode($response, JSON_PRETTY_PRINT));
                    if ($ret==MobileValidation::RESULT_OK) {
                        $this->result['d']['status']='sent';
                        $this->result['d']['pin_hash'] = '';//$response['response']['pin_hash'];  
                        $this->result['d']['request_id'] = $response['response']['id'];
                                                  
                        if ($this->lang=='ar') {
                            $this->result['d']['message'] = "يرجى عدم الرد او قطع الاتصال\nلقد تم طلب الاتصال برقمك، نأمل التحقق من سجل المكالمات الخاص بك لآخر مكالمة لم يرد عليها رقم وأدخل آخر 4 أرقام من هذا الرقم {$response['response']['cli_prefix']}";
                        }
                        else {
                            $this->result['d']['message'] = "PLEASE DO NOT ANSWER OR HANGUP\nA call has been made to your number, please check your mobile call log for the last missed call number and enter the last 4 digits of that number {$response['response']['cli_prefix']}";
                        }

                        if (isset($response['response']['length'])) {
                            $x = substr('XXXXXXXXXXXXXXXXXXXX', -1*($response['response']['length']-9));
                            $this->result['d']['message'].=$x.'XXXX';
                        }
                    }
                    else {
                        $this->result['e'] = 'Error ['.$ret.'-'.__LINE__.'], could not complete activation process! Please try again after few seconds...';
                    }
                    break;
                
                default: // SMS
                    $pin = mt_rand(1000, 9999);                                        
                    $msg_text = "{$pin} is your mourjan confirmation code";
                
                    if (MobileValidation::getInstance()->
                            setUID($this->getUID())->
                            setPlatform(MobileValidation::IOS)->
                            setPin($pin)->
                            sendSMS($mobile_no, $msg_text, ['uid'=>$this->getUID()])== MobileValidation::RESULT_OK) {
                        $this->result['d']['status']='sent';
                    }
                                   
                    break;
            }
                       
        }
       
    }


    function hasError() {    
        return !empty($this->result['e']);
    }


    function done() {
        $this->db->close();
        if ($this->uuid=="B066D32F-08F6-4C2E-973C-9658CA745F09") {
            $this->result['l']=1;
        }
        echo json_encode($this->result, JSON_UNESCAPED_UNICODE );
        flush();
    }

    
    function detectEmail($ad) {
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/i', $ad, $matches);
        return $matches;
    }


    function cutOfContacts(&$text) {
        $text = preg_replace('/\x{200B}.*/u', '', $text);
        return;
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        
        if(!($content && count($content))) {
            preg_match($phone, $text, $content);
            if(!($content && count($content))) {
                return $text;
            }
        }

        if($content && count($content)) {
            $strpos = strpos($text, $content[0]);
            $text = trim(substr($text,0, $strpos));
            $text = trim(preg_replace('/[-\/\\\]$/', '', $text));        
        }
    }

    
    function processTextNumbers(&$text, $pubId=0, $countryCode=0, &$matches=[]) {
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';    
        $content=null;

        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text,$content);
        if (!($content && count($content))) {
            preg_match($phone, $text, $content);
            if(!($content && count($content))) {
                return $text;
            }
        }

        if ($content && count($content)) {        
            $str=$content[1];

            $strpos = strpos($text, $content[0]);
            $text = trim(substr($text,0, $strpos));
            $text = trim(preg_replace('/[-\/\\\]$/', '', $text));

            if($str) {            
                if($this->formatNumbers) {                
                    $nums=array();
                    $numInst=array();
                    $numbers = null;
                    preg_match_all($phone, $str, $numbers);
                    if ($numbers && count($numbers[1])) {                    
                        foreach($numbers[1] as $match) {                        
                            $number = $match;
                            try {                            
                                if ($pubId==1) {                                
                                    $numInst[] = $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                                }
                                else {
                                    $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                                }
                                
                                if ($num && $this->mobileValidator->isValidNumber($num)) {                            
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if ($rCode==$this->formatNumbers) {                                
                                        $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                    }
                                    else {
                                        $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                    }
                                    $nums[]=array($number, $num);
                                }
                                else {                                
                                    $hasCCode = preg_match('/^\+/', $number);
                                    switch($countryCode) {                                    
                                        case 'SA':
                                            $num = ($hasCCode) ? substr($number,4) : $number;
                                            if(strlen($num)==7) {
                                                switch($pubId) {
                                                    case 9:
                                                        $num='011'.$num;
                                                        break;
                                                    
                                                    case 12:
                                                    case 18:
                                                        $tmp='013'.$num;
                                                        $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                        if ($tmp && $this->mobileValidator->isValidNumber($tmp)) {
                                                                $num='013'.$num;
                                                        }
                                                        else {
                                                            $num='011'.$num;
                                                        }
                                                        break;
                                                }
                                            }
                                            break;
                                            
                                        case 'EG':
                                            $num = ($hasCCode) ? substr($number, 3) : $number;
                                            if (strlen($num)==7) {
                                                switch($pubId) {
                                                    case 13:
                                                        $num='2'.$num;
                                                        break;
                                                    
                                                    case 14:
                                                        $num='3'.$num;
                                                        break;
                                                }
                                            }
                                            elseif (strlen($num)==8) {
                                                switch($pubId) {
                                                    case 13:
                                                        $num='2'.$num;
                                                        break;
                                                }
                                            }
                                            break;
                                    }
                                    
                                    if ($num != $number) {
                                        $num = $this->mobileValidator->parse($num, $countryCode);
                                        if ($num && $this->mobileValidator->isValidNumber($num)) {
                                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                            if ($rCode==$this->formatNumbers) {
                                                $num=$this->mobileValidator->formatInOriginalFormat($num, $this->formatNumbers);
                                            }
                                            else {
                                                $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num, $this->formatNumbers);
                                            }
                                            $nums[]=array($number, $num);
                                        }
                                        else {
                                            $nums[]=array($number, $number);
                                        }
                                    } 
                                    else {
                                        $nums[]=array($number, $number);
                                    }
                                    
                                }
                            } 
                            catch(Exception $ex) {
                                $nums[]=array($number, $number);
                            }
                        }
                        
                        $mobile=array();
                        $phone=array();
                        $undefined = array();
                        $i=0;

                        foreach ($nums as $num) {
                            if ($num[0]!=$num[1]) {
                                $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                                if ($type==1 || $type==2) {
                                    $mobile[]=$num;
                                }
                                elseif ($type==0 || $type==2) {
                                    $phone[]=$num;
                                }
                                else {
                                    $undefined[]=$num;
                                }
                            }
                            else {
                                $undefined[]=$num;
                            }
                        }
                        
                        $matches = [$mobile, $phone, $undefined];               
                    }
                }
                else {
                    if ($pubId!=1) {
                        if (!preg_match('/\<span class/',$text)) {
                            preg_match_all($phone, $str, $numbers);
                            if ($numbers && count($numbers[1])) {
                                foreach ($numbers[1] as $match) {
                                    $number = $match;
                                    $number =  preg_replace('/\+/','\\+' , $number);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $text;
    }


    public function changeNumber() {      
        $opts = $this->userStatus($status);
               
        $phone_number=filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
        $current_phone_number=filter_input(INPUT_GET, 'ctel', FILTER_VALIDATE_INT)+0;

        if ($status==1 && $opts->phone_number>0) {
            
            if ($current_phone_number!=$opts->phone_number) {
                $this->result['e']='Old used phone number is not valid!';
                return;
            }

            try {
                $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                $num = $this->mobileValidator->parse("+{$phone_number}", 'LB');
                if($num && $this->mobileValidator->isValidNumber($num)) {
                    $numberType = $this->mobileValidator->getNumberType($num);
                    if (!($numberType==libphonenumber\PhoneNumberType::MOBILE || $numberType==libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE)) {
                        $this->result['e'] = "+{$phone_number} is not a valid mobile number!";
                        return;
                    }
                }
                else {
                    $this->result['e'] = "+{$phone_number} is not a valid telephone number!";
                    return;
                }

                $phone_number = intval($this->mobileValidator->format($num, \libphonenumber\PhoneNumberFormat::E164));

                if ($phone_number<999999) {
                    $this->result['e'] = 'Invalid mobile registration request';
                    return;
                }
            }
            catch (Exception $ex ) {
                $this->result['e'] = $ex->getMessage();
                return;
            }
            
            $this->result['d']['current']=$opts->phone_number;
            $this->result['d']['new']=$phone_number;
            $this->result['d']['status']="accepted";                       
        }
        else {
            $this->result['e']='Invalid user request!';
        }
    }


    public function unregister() { 
        $opts = $this->userStatus($status);
        if ($status==1 && $opts->phone_number>0) {
            $phone_number=filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;

            if ($phone_number!=$opts->phone_number ) {
                $this->result['e']='Not a valid phone number!';
                return;
            }

            if (NoSQL::getInstance()->mobileUpdate($this->getUID(), $phone_number, [Core\Model\ASD\USER_DEVICE_UNINSTALLED=>1])) {                
                $this->result['d']['status']='deleted';
                NoSQL::getInstance()->setUserLevel($this->getUID(), 5);                
            } 
            else {
                $this->result['d']['status']='failed';
            }
        }
        else {
            $this->result['e']='Invalid user request!';
        }
    }


    public function getCountryIsoByIp() {
        $ip = false;
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $country_code='';		
        if ($ip) {
            $databaseFile = '/home/db/GeoLite2-City.mmdb';
            $reader = new Reader($databaseFile);
            $geo = $reader->get($ip);
            $reader->close();
        
            if ($geo) {
                $country_code = trim(strtoupper(trim($geo['country']['iso_code'])));
                if(strlen($country_code)!=2)$country_code='';
            }
            
            $this->result['d']['iso']=$country_code;
        }
        else $this->result['e']='Bad Request!';
    }

    
    public function validatePhoneNumber() {
        $phone_number=filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_FLOAT)+0;
        $country_code=filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT)+0;

        /*
        const FIXED_LINE = 0;
        const MOBILE = 1;
        // In some regions (e.g. the USA), it is impossible to distinguish between fixed-line and
        // mobile numbers by looking at the phone number itself.
        const FIXED_LINE_OR_MOBILE = 2;
        // Freephone lines
        const TOLL_FREE = 3;
        const PREMIUM_RATE = 4;
        // The cost of this call is shared between the caller and the recipient, and is hence typically
        // less than PREMIUM_RATE calls. See // http://en.wikipedia.org/wiki/Shared_Cost_Service for
        // more information.
        const SHARED_COST = 5;
        // Voice over IP numbers. This includes TSoIP (Telephony Service over IP).
        const VOIP = 6;
        // A personal number is associated with a particular person, and may be routed to either a
        // MOBILE or FIXED_LINE number. Some more information can be found here:
        // http://en.wikipedia.org/wiki/Personal_Numbers
        const PERSONAL_NUMBER = 7;
        const PAGER = 8;
        // Used for "Universal Access Numbers" or "Company Numbers". They may be further routed to
        // specific offices, but allow one number to be used for a company.
        const UAN = 9;
        // A phone number is of type UNKNOWN when it does not fit any of the known patterns for a
        // specific region.
        const UNKNOWN = 10;

        // Emergency
        const EMERGENCY = 27;

        // Voicemail
        const VOICEMAIL = 28;

        // Short Code
        const SHORT_CODE = 29;

        // Standard Rate
        const STANDARD_RATE = 30;
         * */

        if ($phone_number && $country_code) {            
            $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();

            $region_code = $this->mobileValidator->getRegionCodeForCountryCode($country_code);

            if($region_code) {
                $number = $this->mobileValidator->parse($phone_number, $region_code);
                if($number && $this->mobileValidator->isValidNumber($number)) {
                    $this->result['d']=['type'  =>  $this->mobileValidator->getNumberType($number)];                    
                }
                else {
                    $this->result['c']=ERR_INVALID_PHONE_NUMBER;
                }
            }
            else {
                $this->result['c']=ERR_INVALID_COUNTRY_CODE;
            }
        }
        else {
            $this->result['e']='Invalid user request!';
            $this->result['c']=ERR_INVALID_REQUEST_PARAMS;
        }
    }


    public function getUserAdStat() {
        if ($this->demo) {
            $this->getDemoUserAdStat();
            return;
        }
        
        $opts = $this->userStatus($status);
        
        if ($status==1) {            
            // Register session            
            if ( $opts->user_status==1 && version_compare($this->appVersion, '1.1.0', '<')) {
                new MCSessionHandler(TRUE);

                include $this->config['dir'] .'/core/model/User.php';
                $user = new User($this->db, $this->config, null, 0);
                $user->sysAuthById($this->getUID());
                $user->params['app']=1;
                $user->update();                
            }
            
            $lang=filter_input(INPUT_GET, 'dl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
            $rs = $this->db->get(
                    "select state id, count(*) ads from AD_USER where web_user_id=? and state+0 in (0,1,2,3,4,7,9) group by 1", 
                    [$this->uid], TRUE, \PDO::FETCH_NUM);

            foreach ($rs as $row) {
                $name = "";
                switch ($row[0]) {
                    case 0:
                        $name = $lang=='en' ? 'Draft ads' : 'المسودات';
                        break;
                    case 1:
                    case 4:
                        $name = $lang=='en' ? 'Pending ads' : 'قيد التدقيق';
                        if ($row[0]==4) { $row[0]=1; }
                        break;
                    case 2:
                        $name = $lang=='en' ? 'Approved ads' : 'موافق عليه';
                        break;
                    case 3:
                        $name = $lang=='en' ? 'Rejected ads' : 'مرفوض';
                        break;
                    //case 4:
                    //    $name = $lang=='en' ? '' : '';
                    //    break;
                    case 5:
                        $name = $lang=='en' ? '' : '';
                        break;
                    case 6:
                        $name = $lang=='en' ? '' : '';
                        break;
                    case 7:
                        $name = $lang=='en' ? 'Active ads' : 'الاعلانات الفعاله';
                        break;
                    case 8:
                        $name = $lang=='en' ? 'Deleted ads' : 'الاعلانات الملغات';
                        break;
                    case 9:
                        $name = $lang=='en' ? 'Archived ads' : 'الارشيف';
                        break;


                    default:
                        break;
                }
                $this->result['d'][] = [$row[0], $name, $row[1]];
            }
        }
    }


    public function getDemoUserAdStat() {
        $lang=filter_input(INPUT_GET, 'dl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
        $name = $lang=='en' ? 'Demo Active ads' : 'تجربه - الاعلانات الفعاله';
        $this->result['d'][] = [7, $name, 10];
    }
    
    
    public function getDemoMyAds() {
        include_once $this->config['dir'].'/core/lib/SphinxQL.php';
        
        $sphinxQL = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
        $sphinxQL->setLimits(0, 100);
        //$sphinxQL->setFilter("publication_id", 1);        
        $sphinxQL->setFilter("country_id", $this->countryId);
        $sphinxQL->setSelect("id, impressions");
        $sphinxQL->setSortBy("date_added asc");
        $rs = $sphinxQL->query("", MYSQLI_ASSOC);
        $views = [];
        foreach ($rs['matches'] as $obj) {
            $views[$obj['id']]=$obj['impressions']+0;
        }
        
        $rs = $this->db->queryResultArray(
                "select first 10 a.content, a.state, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', a.date_added) date_added, 
                a.id, ad.purpose_id, ad.section_id,
                DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.expiry_date) date_ended, 
                IIF(bo.id is null, 0, bo.id) order_id, f.name_en, f.name_ar 
                from ad_user a 
                left join ad on ad.id=a.id 
                left join T_AD_BO bo on bo.ad_id=a.id and bo.blocked=0 
                left join T_OFFER f on f.id=bo.offer_id 
                where a.ACTIVE_COUNTRY_ID=? and a.state=7 and ad.hold=0 
                and ad.expiry_date >= dateadd(30 minute to current_timestamp)
                and ((bo.demo is null) or (bo.demo=1))
                order by a.date_added asc", [$this->countryId], TRUE, PDO::FETCH_NUM);
        
        foreach ($rs as $row) {
            $data = json_decode($row[0]);
            $data->state = $row[1];
            $data->date_added = $row[2];
            $data->id = $row[3];
            $data->pu = $row[4];
            $data->se = $row[5];
            
            
            $data->date_ended = $row[6];
            $data->order_id = $row[7];
            if ($row[7]) {
                $data->feature_name=new stdClass();
                $data->feature_name->ar = $row[9];
                $data->feature_name->en = $row[8];
            }

            $data->impressions = isset($views[$row[3]]) ? $views[$row[3]] : 0;

            $this->result['d'][] = $data;
        }
        $this->getBalance();
    }
    
    
    public function getMyAds($states) {
        if ($this->demo==1 && $states=='7') {
            $this->getDemoMyAds();
            return;
        }
        
        $opts = $this->userStatus($status);
        if ($status==1) {
            
            if ($states=="7") {
                $this->getStatsAdSummary($opts, $status);
                $views = $this->result['d']['ads'];
                unset($this->result['d']['ads']);                
            }
            if ($states=='1') { $states = "1, 4"; }
            $rs = $this->db->queryResultArray(
                    "select a.content, a.state, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', a.date_added) date_added, a.id, ad.purpose_id, ad.section_id, ".
                    "DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.expiry_date) date_ended, ".
                    "IIF(bo.id is null, 0, bo.id) order_id, f.name_en, f.name_ar " .
                    "from ad_user a ".
                    "left join ad on ad.id=a.id ".
                    "left join T_AD_BO bo on bo.ad_id=a.id and bo.blocked=0 ".
                    "left join T_OFFER f on f.id=bo.offer_id " .
                    "where a.web_user_id=? and a.state in ({$states}) order by a.date_added desc", [$this->uid], TRUE, PDO::FETCH_NUM);
                        
            foreach ($rs as $row) {
                $data = json_decode($row[0]);
                if(!is_object($data)) {
                    $data = json_decode("{}");
                }
                
                if (isset($data->other)) {
                    //$tl = mb_strlen(strip_tags($data->other));
                    //if ($tl<60) {
                    //    $data->other.=mb_substr("                                                                                                            ",0,60-$tl);                   
                    //}                    
                } 
                else {
                    if (isset($data->text)) {
                        $tl = mb_strlen(strip_tags($data->text));
                        if ($tl<60) {
                            //$data->other=$data->text. mb_substr("                                                                                                            ",0,60-$tl);                   
                        } 
                        else $data->other = $data->text;
                        
                    } 
                    else $data->other='';
                }
                
                $data->state = $row[1];
                $data->date_added = $row[2];
                $data->id = $row[3];
                if (!empty($row[4])) { $data->pu = $row[4]; }
                if (!empty($row[5])) { $data->se = $row[5]; }                     
                $data->date_ended = $row[6];
                $data->order_id = $row[7];
                
                if ($row[7]) {
                    $data->feature_name=new stdClass();
                    $data->feature_name->ar = $row[9];
                    $data->feature_name->en = $row[8];
                }
                
                if ($states=="7") {
                    $data->impressions = isset($views[$row[3]]) ? $views[$row[3]] : 0;
                }
                
                $this->result['d'][] = $data;
            }
        }
    }


    public function userHoldAd() { 
        $opts = $this->userStatus($status);
        if ($status==1) {
            $this->result['d']=[0];
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
            if ($id) {
                $this->db->setWriteMode();
                $rs = $this->db->get("select WEB_USER_ID from ad_user where id=?", [$id], FALSE, PDO::FETCH_ASSOC);
                if ((empty($rs)==false) && ($rs[0]['WEB_USER_ID']==$this->uid)) {
                    $rs = $this->db->queryResultArray("update ad set hold=1 where id=? and hold=0 returning id", [$id], TRUE, PDO::FETCH_NUM);
                    $this->result['d']=$rs[0];
                }
            }
        }
    }


    public function userDeleteAd() {
        $opts = $this->userStatus($status);
        $this->result['d']['state']=-1;
        if ($status==1) {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
            if ($id) {
                $this->db->setWriteMode();
                $rs = $this->db->queryResultArray("update AD_USER set state=8 where id=? returning state", [$id], FALSE, PDO::FETCH_ASSOC);
                if (is_array($rs) && count($rs)) {
                    $this->result['d']['state']=$rs[0]['STATE'];
                    $this->db->queryResultArray("update ad set hold=1 where id=? and hold=0 returning id", [$id], TRUE, PDO::FETCH_NUM);
                }
            }
        }
    }

    
    public function userRenewAd() {        
        $opts = $this->userStatus($status);
        $this->result['d']['state']=-1;
        if ($status==1 && !$this->user->isBlocked()) {
            if ($this->user->isSuspended()) {
                $this->result['d']['suspend'] = $this->user->getSuspensionTime();                
                return;
            }
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
            if ($id) {                
                $this->db->setWriteMode();
                
                include_once $this->config['dir'] . '/core/model/User.php';
                $user = new User($this->db, $this->config, null, 0);
                $user->info['id'] = $this->uid;
                $user->info['level'] = 0;
                $rs = $user->renewAd($id, 1);
                                
                if (empty($rs)==false) {
                    $this->result['d']['state']=1;//$rs[0]['STATE'];
                }
            }
        }else{
            error_log("error on status");
        }
    }


    public function getBalance() 
    {
        $rs = $this->db->get("select sum(credit-debit) balance from T_TRAN where uid=?", [($this->demo===1?93778:$this->uid)]);
        $this->result['balance'] = $rs[0]['BALANCE']+0.0;
    }


    public function getStatment() 
    {
        $opts = $this->userStatus($status);
        $this->result['d']['state']=-1;
        if ($status==1) 
        {
            $rs = $this->db->queryResultArray(
                "SELECT r.ID, r.DATED, r.CURRENCY_ID, r.AMOUNT, r.DEBIT,
                r.CREDIT, r.USD_VALUE, r.XREF_ID, r.PRODUCT_ID, r.TRANSACTION_ID,
                r.TRANSACTION_DATE, f.BO_ID, o.AD_ID, p.NAME_AR, p.NAME_EN
                FROM T_TRAN r
                left join T_AD_FEATURED f on f.ID=r.XREF_ID
                left join T_AD_BO o on o.ID=f.BO_ID
                left join T_OFFER p on p.ID=o.OFFER_ID
                where r.UID=?
                order by r.ID", [$this->uid]);
            $balance=0.0;
            $count=count($rs);
            for ($i=0; $i<$count; $i++) {
                $rs[$i]['CREDIT']=$rs[$i]['CREDIT']+0.0;
                $rs[$i]['DEBIT']=$rs[$i]['DEBIT']+0.0;
                $balance = $balance+$rs[$i]['CREDIT']-$rs[$i]['DEBIT'];
                $rs[$i]['BALANCE']=$balance;
            }
            $this->result['d']=  array_reverse($rs);
            $this->result['balance'] = $balance;
        }
    }
    
    
    public function getCreditTotal() {
        $opts = $this->userStatus($status);
        $this->result['d']=-1;
        if ($status==1) {
            $rs = $this->db->get("SELECT sum(r.credit-r.debit) FROM T_TRAN r where r.UID=?", [$this->uid], true);
            if ($rs && count($rs) && $rs[0]['SUM']!=null) {
                $this->result['d']=($rs[0]['SUM'])?$rs[0]['SUM']+0:0;
            }
        }
    }


    public function iPhonePurchase() {
        $this->db->setWriteMode();
        $adId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
        $start=filter_input(INPUT_GET, 'start', FILTER_VALIDATE_FLOAT)+0;
        $days=filter_input(INPUT_GET, 'days', FILTER_VALIDATE_INT)+0;

        if ($this->demo===1) {
            $rs = $this->db->queryResultArray(
                    "INSERT INTO T_AD_BO (AD_ID, OFFER_ID, CREDIT, DATED, START_DATE, BLOCKED, DEMO) VALUES ".
                    "(?, ?, ?, current_timestamp, current_timestamp, 0, 1) RETURNING ID", [$adId, 1, 0.1], TRUE);
                $this->result['d']['order_id']=$rs[0]['ID']+0;
            
        } 
        else {
            $opts = $this->userStatus($status);
            $this->result['d']['order_id']=0;
            if ($status==1) {       
                $ad = $this->db->get('select * from ad_user where id = ?', [$adId], TRUE);
                if($ad && count($ad)) {
                    $content = json_decode($ad[0]['CONTENT'],true);                    
                    $currentCid = 0;
                    $isMultiCountry = false;
                    $cities = $this->db->getCitiesDictionary();
                    foreach($content['pubTo'] as $key => $val) {
                        if($key && isset($cities[$key])) {
                            if($currentCid && $currentCid != $cities[$key][4]) {
                                $isMultiCountry = true;
                                break;
                            }
                            $currentCid = $cities[$key][4];
                        }
                    }
                    
                    if($isMultiCountry) {
                        if (isset($opts->prefs['lang']) && $opts->prefs['lang']=='ar') {
                            $msg = 'عذراً ولكن لا يمكن تمييز الاعلان في اكثر من بلد واحد';
                        }
                        else {
                            $msg = 'Sorry, you cannot publish premium ads targetting more than ONE country';
                        }
                        $this->result['e']=$msg;
                    }
                    else {
                        $start_date = date('Y-m-d h:i:s', $start);
                        $this->result['start']=$start_date;
                        $rs = $this->db->get(
                            "INSERT INTO T_AD_BO (AD_ID, OFFER_ID, CREDIT, DATED, START_DATE, BLOCKED, DEMO) VALUES ".
                            "(?, ?, ?, current_timestamp, ?, 0, 0) RETURNING ID", [$adId, 1, $days, $start_date], TRUE);
                        $this->result['d']['order_id']=$rs[0]['ID']+0;
                    }
                }
            }
        }
        $this->getBalance();
    }


    public function stopAdFeature() {
        $adId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
        $orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT)+0;        
        $opts = $this->userStatus($status);
        $this->result['d']['blocked']=-1;
        if ($status==1) {
            $this->db->setWriteMode();
            $rs = $this->db->queryResultArray(
                "UPDATE T_AD_BO SET BLOCKED=1 WHERE AD_ID=? and ID=? RETURNING BLOCKED", [$adId, $orderId], TRUE);
            $this->result['d']['blocked']=$rs[0]['BLOCKED']+0;
        }
        $this->getBalance();
    }


    public function iPhoneTransaction() {
        include_once 'ITransaction.php';
        $itran = new ITransaction($this);
    }
    
    
    public function androidTransaction($appVersion="1.1") {
        include_once 'AndroidApi-'.$appVersion.'.php';
        $itran = new AndroidApi($this);
    }
    

    public function dbPostEvent() {
        $ev_name=filter_input(INPUT_GET, 'evn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $ev_count=filter_input(INPUT_GET, 'evc', FILTER_VALIDATE_INT)+0;
        if (!empty($ev_name)) {
            $this->db->queryResultArray('execute procedure SP$EVENT(?, ?)', [$ev_name, $ev_count<1?1:$ev_count], TRUE);
        }
    }


    public function logger() {
        $log = filter_input(INPUT_GET, 'log', FILTER_SANITIZE_STRING);
        error_log(sprintf("%s\t%s\t%s", date("Y-m-d H:i:s"), $this->uuid, $log).PHP_EOL, 3, "/var/log/mourjan/app.log");
    }
    
    
    public function sendSMS($phone_number, $text, $callback_reference=0) {
        try {
            include_once $this->config['dir'].'/core/lib/MourjanNexmo.php';
            return ShortMessageService::send(strval($phone_number), $text, $callback_reference);
        }                 
        catch (Exception $e) {            
        }
        return FALSE;
    }
    
    
    public function getStatsAdSummary($opts=NULL, $status=0) {
        $fbal = FALSE;
        if ($opts==NULL) {
            $opts = $this->userStatus($status);
            $fbal=TRUE;
        }
        
        $archive = filter_input(INPUT_POST, 'x', FILTER_VALIDATE_INT)+0;
        //$archive=1;
        if ($status==1) {
            $redis = new Redis();
            
            $redis->connect($this->config['rs-host'], $this->config['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
            $redis->setOption(Redis::OPT_PREFIX, $this->config['rs-prefix']);
            $redis->select($this->config['rs-index']);
            
            $ads = $redis->sGetMembers('U'.$this->uid);
            
            $summary = [];
            foreach ($ads as $id) {                            
                $impressions = $redis->hGetAll('AI'.$id);
                foreach ($impressions as $date => $value) {
                    if (isset($summary[$id])) {
                        $summary[$id]+=$value+0;
                    } else {
                        $summary[$id]=$value+0;
                    }
                }                                
            }
            $this->result['d']['ads'] = $summary;
            $redis->close();
            
        }
        //error_log(var_export($this->result, TRUE));
        if ($fbal)
            $this->getBalance();
    }
    
    
    public function getStatsByAdId() {
        $opts = $this->userStatus($status);
        $aid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)+0;
        //$showInteractions = filter_input(INPUT_POST, 'x', FILTER_VALIDATE_INT)+0;
        $showInteractions = 0;
        if ($status==1 && $aid > 0) {
            $redis = new Redis();
            
            $redis->connect($this->config['rs-host'], $this->config['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
            $redis->setOption(Redis::OPT_PREFIX, $this->config['rs-prefix']);
            $redis->select($this->config['rs-index']);
            
            $sdate = time()-2592000; // 30 days
            $res = $redis->hGetAll('AI'.$aid);
            ksort($res, SORT_STRING);
                    
            $redis->close();
            
                        
            $count=0;
           
            $data = array();
            $cdata = array();
            $dt=0;
            $this->result['d'] = [];
            if($res && count($res)){
                $i=0;
                $curDt=0;
                $prevDt=0;
                foreach($res as $date=>$value){
                    $curDt=strtotime($date);
                    if($i==0){
                        $dt=$curDt;
                    }else{
                        $ddif = $curDt-$prevDt;
                        if($ddif>86400){
                            $span = $ddif / 86400;
                            for($k=0;$k<$span-1;$k++){
                                $data[]=0;   
                                $i++;
                            }
                        }
                    }
                    $prevDt=$curDt;
                    $data[]=(int)$value;
                    $count+=(int)$value;
                    $i++;
                }
                
                if($showInteractions == 1){                    
                    $rc = $redis->hGetAll('AC'.$aid);
                    ksort($rc, SORT_STRING);
                    if($rc && count($res)){
                        $j=0;
                        $curDt=0;
                        $prevDt=$dt-86400;
                        foreach($rc as $date=>$value){
                            $curDt=strtotime($date);
                            $ddif = $curDt-$prevDt;
                            if($ddif>86400){
                                $span = $ddif / 86400;
                                for($k=0;$k<$span-1;$k++){
                                    $cdata[]=0;   
                                    $j++;
                                }
                            }
                            //echo '<br>';
                            $prevDt=$curDt;
                            $cdata[]=(int)$value;
                            $j++;
                        }
                        if($j<$i){
                            for($k=$j;$k<$i;$k++){
                                $cdata[]=0;   
                            }
                        }
                    }else{
                        foreach($data as $imp){
                            $cdata[]=0;
                        }
                    }
                }
                $this->result['d']['stats'] = [];
                $this->result['d']['stats']['i'] = $data;
                $this->result['d']['stats']['t'] = $count;
                $this->result['d']['stats']['s'] = $dt;
                if(!empty($cdata)){
                    $this->result['d']['stats']['c'] = $cdata;
                }
            }
            $redis->close();
        }
        $this->getBalance();
    }
    
    
    function signInAsMobile() {
        $opts = $this->userStatus($status);
        if ($status==1) {
            $sess_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
            $host_id = filter_input(INPUT_GET, 'host', FILTER_VALIDATE_INT)+0;
            $ux_time = filter_input(INPUT_GET, 'uxt', FILTER_VALIDATE_INT)+0;

            $redis = new Redis();
            $redis->connect($this->config['rs-host'], $this->config['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
            $redis->setOption(Redis::OPT_PREFIX, 'SESS:');
            $redis->select(1);
            
            $obj = $redis->get($sess_id);
            if ($obj) {
                $arr = preg_split('/:/', $obj);
            
                if (count($arr)==2 && $arr[0]==$host_id) {
                    $this->result['d']=1;
                
                    if ($host_id==99) {
                        $host = "https://dv.mourjan.com";
                    } 
                    else {
                        $host = "https://www.mourjan.com";
                    }
                
                    //error_log("{$host}/web/index.php?sid={$sess_id}&sh=".$arr[1]."&uid={$this->uid}");
                
                    file_get_contents("{$host}/web/index.php?sid={$sess_id}&sh=".$arr[1]."&uid={$this->uid}");
                }
            }
            $redis->close();
            
        }
    }
    
    
    function makeMobileUserIdAsOfDesktop() {
        $opts = $this->userStatus($status);
        if ($status==1) {
            $sess_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
            $host_id = filter_input(INPUT_GET, 'host', FILTER_VALIDATE_INT)+0;
            $ux_time = filter_input(INPUT_GET, 'uxt', FILTER_VALIDATE_INT)+0;

            $redis = new Redis();
            $redis->connect($this->config['rs-host'], $this->config['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
            $redis->setOption(Redis::OPT_PREFIX, 'SESS:');
            $redis->select(1);
            
            $obj = $redis->get('m-'.$sess_id);
            
            //error_log($obj);
            
            if ($obj) {
                $arr = preg_split('/:/', $obj);
            
                if (count($arr)==3 && $arr[0]==$host_id) {
                    
                    $userId = $arr[2]+0;
                    if ($this->uid==$userId) {
                        $this->result['e'] = 'You are already connected';
                        return;
                    }
                    
                    if ($userId>0 && $userId!=$this->getUID()) {                                             
                        include $this->config['dir'] .'/core/model/User.php';
                        $user = new User($this->db, $this->config, null, 0);
                        
                        $ok = $user->mergeDeviceToAccount($this->uuid, $this->getUID(), $userId, $this->isIOS());
                        if ($ok) {
                            $this->uid=$userId;
                            $opts = $this->userStatus($status);
                            $this->result['d']['pwset']=!empty($opts->secret);
                            $this->result['d']['uid']=$userId;
                            $this->getBalance(); 
                        } 
                        else { 
                            $this->result['e']="Could not activate your device due to internal system error!";
                            error_log($this->result['e'] . " " . $this->uid . " to uid: " . $userId);
                        }
                         
                    }
                }
            } 
            else {
                $this->result['e'] = 'Your session is expired!';
            }
        }
        
    }
    
    
    function getFavoriteAds() {        
    }
    
    
    function getAdUserNote() {
        $ad_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
        
        if ($ad_id>0)
            $rs = $this->db->get("select ad_id, web_users_notes.content from web_users_notes left join ad on ad.id=web_users_notes.ad_id where web_user_id=? and deleted=0 and ad.hold=0 and ad_id=?", [$this->uid, $ad_id]);
        else 
            $rs = $this->db->get("select ad_id, web_users_notes.content from web_users_notes left join ad on ad.id=web_users_notes.ad_id where web_user_id=? and deleted=0 and ad.hold=0", [$this->uid]);
                    
        
        if ($rs) {
            $this->result['d'] = $rs;
        }                
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
    }
    
    
    function userPostAd() {
        if ($this->config['active_maintenance']) {
            $this->result['e'] = "503";
            return;
        }
                
        $normalized = NULL;
        $opts = $this->userStatus($status);
   
        if ($status==1 && !$this->user->isBlocked()) {
            if ($this->user->isSuspended()) {
                $this->result['d']['suspend'] = $this->user->getSuspensionTime();                
                return;
            }
            $this->db->setWriteMode();
            
            
            $handle = fopen("php://input", "rb");
            $raw_post_data = '';
            while (!feof($handle)) {
                $raw_post_data .= fread($handle, 8192);
            }
            fclose($handle);
            if (empty($raw_post_data)) {
                $raw_post_data='{}';
            }
            
            //error_log($raw_post_data);
            $ad = json_decode($raw_post_data, TRUE);

            $direct_publish = filter_input(INPUT_POST, 'pub', FILTER_VALIDATE_INT) + 0;                    
            $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
            $device_lang = filter_input(INPUT_GET, 'hl');
            if (!in_array($device_lang, ['ar','en'])) {
                $device_lang = 0;
            }
            
            $state = 0;
            //$ad = json_decode(urldecode(filter_input(INPUT_POST, 'ad', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']])), true);
                    
            //error_log(\json_encode($ad));
            $userState = 0;                    
            $hasFailure = 0;
            $hasMajorFailure = 0;
                    
            $stmt = null;
                    
            if (count($ad)>0) {                        
                if ($ad['se']>0 && $ad['pu']==0) {
                    $ad['pu']=5;
                }
                if (isset($ad['id']) && intval($ad['id'])) {
                    $ad_id = $ad['id']+0;
                }
                  
                /*
                if($ad['rtl'] == 1){
                    $ad['other'] .= "\u200B / ".$ad['contact_ar'];
                    if(strlen($ad['altother']) >= 30){
                        $ad['altother'] .= "\u200B / ".$ad['contact_en'];
                    }
                    }else{
                        $ad['other'] .= "\u200B / ".$ad['contact_en'];
                    }
                */

                $_original_ad=$ad;
                
                
                if (isset($ad['cui']) && isset($ad['cui']['p'])) {
                    $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                    
                    for ($i=0; $i<count($ad['cui']['p']);$i++) {
                        $phoneInfo = $ad['cui']['p'][$i];
                        $phoneInfo['v']="+".intval(trim($phoneInfo['v']));
                        $num = $this->mobileValidator->parse($phoneInfo['v'], 'LB');                            
                        if ($num && $this->mobileValidator->isValidNumber($num)) {
                            $phoneInfo['r']=preg_replace('/\s+/', '', $this->mobileValidator->format($num, \libphonenumber\PhoneNumberFormat::NATIONAL));
                            $phoneInfo['c']=$num->getCountryCode();
                            $phoneInfo['i']=$this->mobileValidator->getRegionCodeForNumber($num);
                        }
                        $ad['cui']['p'][$i]=$phoneInfo;
                    }                    
                }
                //error_log(\json_encode($ad));
                
                include_once $this->config['dir'] . '/core/lib/MCSaveHandler.php';
                include_once $this->config['dir'] . '/core/lib/IPQuality.php'; 
                $ad['ipfs'] = IPQuality::ipScore(TRUE);
                $normalizer = new MCSaveHandler($this->config);
                //error_log($ad['other']);
                $normalized = $normalizer->getFromContentObject($ad);
                $attrs = [];
                if ($normalized) {
                    $ad = $normalized;
                    $attrs = $normalized['attrs'];
                    //error_log(json_encode($ad));
                }                
                //error_log($ad['other']);
                if (!isset($ad['other'])) {
                    NoSQL::Log($ad);
                    NoSQL::Log($_original_ad);
                }
                        
                if ($device_lang) {
                    $ad['hl']=$device_lang;
                }
                
                $ad['rtl'] = ($this->isRTL($ad['other'])) ? 1 : 0;
                        
                if (isset($ad['altother']) && $ad['altother']) {
                    //error_log($ad['altother']);
                    $ad['extra']['t']=1;
                    $ad['altRtl'] = ($this->isRTL($ad['altother'])) ? 1 : 0;

                    if ($ad['rtl'] == $ad['altRtl']) {
                        $ad['extra']['t']=2;
                        unset($ad['altRtl']);
                        unset($ad['altother']);
                    }

                    if (isset($ad['altRtl']) && $ad['altRtl']) {
                        $tmp=$ad['other'];
                        $ad['other']=$ad['altother'];
                        $ad['altother']=$tmp;
                        $ad['rtl']=1;
                        $ad['altRtl']=0;
                    }
                }
                        
                //if (isset($ad['extra']['m']) && !$ad['extra']['m'] && $ad['lat']==0 && $ad['lon']==0) {
                //    $ad['extra']['m']=2;
                //}
                        
                $requireReview = 0;
                /*        
                $ip ='';
                if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                 * 
                 */
                $ad['ip']= IPQuality::getClientIP();                            
                $databaseFile = '/home/db/GeoLite2-City.mmdb';
                $reader = new Reader($databaseFile);
                $geo = $reader->get($ad['ip']);
                $reader->close(); 	
                        
                $XX='';                
                if($geo) {
                    $ad['userLOC'] = isset($geo['city']['names']['en']) && $geo['city']['names']['en'] ? $geo['city']['names']['en'].', ' : '';
                    $ad['userLOC'].=$geo['country']['iso_code'];
                    $ad['userLOC'].=': '. implode(" ,",$geo['location']);                            
                    $XX=$geo['country']['iso_code'];                            
                } else $ad['userLOC']=0;
                        
                if ($this->user->isMobileVerified()) {
                    $uNum = $this->user->getMobileNumber();
                    if($uNum) {
                        $validator = libphonenumber\PhoneNumberUtil::getInstance();
                        $uNum = $validator->parse('+'.$uNum, 'LB');
                        $TXX = $validator->getRegionCodeForNumber($uNum);
                        if ($TXX) {
                            $XX=$TXX;
                        }
                    }
                }
                        
                if (!$XX || !in_array($XX, ['AE', 'BH', 'DZ', 'YE', 'EG', 'IQ', 'JO', 'KW', 'LB', 'LY', 
                    'MA', 'QA', 'SA', 'SD', 'SY', 'TN', 'OM'])) {
                    $requireReview = 995;
                }
                            
                $city_id = 0;
                $country_id = 0;
                $currentCid = 0;
                $isMultiCountry = false;
                $cities = $this->db->getCitiesDictionary();
                        
                foreach ($ad['pubTo'] as $key => $val) {
                    if (!$city_id && isset($cities[$city_id])) {
                        $city_id=$key;
                    }
                            
                    if ($key && isset($cities[$key])) {
                        if ($currentCid && $currentCid != $cities[$key][4]) {
                            $isMultiCountry = true;
                        }
                        $currentCid = $cities[$key][4];
                    }
                }
                        
                foreach($ad['pubTo'] as $key => $val) {
                    $city_id=$key;
                    break;
                }
                        
                if($city_id) { $country_id=$cities[$city_id][4]; }
                        
                                                             
                $isSCAM = 0;
                if (isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0) {
                    $blockedEmailPatterns = addcslashes(implode('|', $this->config['restricted_email_domains']),'.');
                    $isSCAM = preg_match('/'.$blockedEmailPatterns.'/ui', $ad['cui']['e']);
                }
                elseif ($requireReview && $country_id && !$isMultiCountry) {
                    $countries = $this->db->getCountriesData('en');
                    if(isset($countries[$country_id]['code'])) {
                        $countryCode = '+'.$countries[$country_id]['code'];
                        $differentCodes = false;
                        foreach ($ad['cui']['p'] as $number) {
                            if (substr($number['v'], 0, strlen($countryCode)) != $countryCode) {
                                $differentCodes = true;
                            }
                        }
                                
                        if (!$differentCodes) {
                            $requireReview = 0;
                        }
                    }
                }
                        
                if (!$isSCAM && !$requireReview && isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0) {
                    $requireReview = preg_match('/\+.*@/', $ad['cui']['e']);
                    if (!$requireReview) {
                        $requireReview = preg_match('/hotel/', $ad['cui']['e']);
                    }else{
                        $requireReview = 998;
                    }
                    if (!$requireReview) {
                        $requireReview = preg_match('/\..*\..*@/', $ad['cui']['e']);
                        if($requireReview){
                            $requireReview = 996;
                        }
                    }else{
                        $requireReview = 997;
                    }
                }
                        
                if ($ad['se']==0 || $ad['pu']==0 || count($ad['pubTo'])==0) {
                    $hasFailure=1;
                    //$hasMajorFailure=1;
                    if ($device_lang=='ar' || $ad['rtl']) {
                        $msg = 'يرجى تعديل الاعلان وادخال التفاصيل الناقصة';
                    }
                    else {
                        $msg = 'please edit ad and complete missing details';
                    }
                    $ad['msg'] = $msg;
                }
                        
                if (isset($ad['SYS_CRAWL']) && $ad['SYS_CRAWL']) {
                    $hasFailure=1;
                    if ($device_lang=='ar' || $ad['rtl']) {
                        $msg = 'يرجى استخدام PropSpace لتعديل هذا الاعلان';
                    }
                    else {
                        $msg = 'please use PropSpace to edit this ad';
                    }
                    $ad['msg'] = $msg;
                }
                                       
                if ($isSCAM) {
                    if ($this->user->isMobileVerified()) {
                        $this->block($this->getUID(), $this->user->getMobileNumber(), 'scam detection by system based on certain email keywords');
                    }
                    else {
                        $this->setLevel($this->getUID(),5);
                    }                            
                }
                elseif ($requireReview && $ad_id) {
                    $this->db->get('update ad_object set super_admin=? where id=?', [$requireReview, $ad_id], false);
                    //$this->referrToSuperAdmin($ad_id);
                }
                else if($hasMajorFailure) {
                    $ad_id = 0;
                    $state = 3;
                }
                else { 
                    if ($ad_id>0) {
                        //cleanup ad_media xref 
                        if (isset($ad['pics']) && is_array($ad['pics']) && count($ad['pics'])) {
                            $keys = array_keys($ad['pics']);
                            $filenames = '';
                            foreach ($ad['pics'] as $key => $values) {
                                if ($filenames!='') {
                                    $filenames.=',';
                                }
                                $filenames .= "'{$key}'";
                            }

                            $records = $this->db->queryResultArray("select id from media where filename in ({$filenames})", null, false);
                            if ($records !== false) {
                                $mediaIds = [];
                                if ($records && is_array($records)) {
                                    foreach ($records as $media) {
                                        $mediaIds[] = $media['ID'];
                                    }
                                }

                                if (count($mediaIds)) {
                                    $mediaIds = implode(",", $mediaIds);

                                    $this->db->queryResultArray("delete from ad_media where ad_id=? and media_id not in ({$mediaIds})", [$ad_id], false);
                                }
                                else {
                                    $this->db->queryResultArray("delete from ad_media where ad_id=?", [$ad_id], false);
                                }
                            }

                        }
                        else {
                            $this->db->queryResultArray("delete from ad_media where ad_id=?", [$ad_id], false);
                        }
                        //end of ad_media cleanup
                                                                
                        $this->db->queryResultArray(
                                    "update ad set hold=1 where id=? and hold=0 and (exists (select 1 from ad_user d where d.id=? and d.web_user_id=?)) returning id", 
                                    [$ad_id, $ad_id, $this->getUID()], false);
                                
                        if ($ad['state'] == 1 && isset($ad['budget']) && $ad['budget']+0 > 0) {
                            $ad['state'] = 4;
                        }
                        $state = $ad['state'];
                                
                        if ($hasFailure) {
                            $state = 3;
                        }                        
                        $ad['state']=$state;

                        $encodedAd = json_encode($ad);
                        $json_error = json_last_error();
                        if ($json_error==5) {
                            error_log("JSON ERROR");
                            if (isset($ad['userLOC']) && $ad['userLOC']) {
                                $ad['userLOC']=$ad['ip'];
                                $encodedAd = json_encode($ad);
                                $json_error = json_last_error();
                            }
                        }

                        $q='update ad_user set
                            content=?,purpose_id=?,section_id=?,rtl=?,
                            country_id=?,city_id=?,latitude=?,longitude=?,state=?,media=?,date_added=current_timestamp 
                            where id=? and web_user_id+0=? 
                            returning state, id';
                        $stmt = $this->db->getInstance()->prepare($q);

                        $result=null;
                        if ($this->db->executeStatement($stmt, [
                                        $encodedAd,
                                        $ad['pu'],
                                        $ad['se'],
                                        $ad['rtl'],
                                        $country_id,
                                        $city_id,
                                        $ad['lat'],
                                        $ad['lon'],
                                        $state,
                                        $ad['media'],
                                        $ad_id,
                                        $this->getUID()
                                    ])) {
                            $result=$stmt->fetchAll(PDO::FETCH_ASSOC);                                    
                        }   
                        unset($stmt);
                        
                        if (!empty($result)) {                                        
                            $state=$result[0]['STATE'];                                                              
                            $st = $this->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                            $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                            $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                            $this->db->executeStatement($st);
                            unset($st);
                        }                                

                        if ( $ad['state']==1 ) {
                            if ($this->user->isMobileVerified()) {
                                $userState = $this->user->isSuspended() ? 1:0;
                            }
                            else {
                                $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                            }
                        }
                    }
                    else { // ad id is nil
                        $state = 0;
                        if ($direct_publish) { $state = 1; }

                        if ($state==1 && isset($ad['budget']) && $ad['budget']+0 > 0) {
                            $state = 4;
                        }
                                
                        if ($hasFailure) { $state = 3; }
                        $ad['state']=$state;

                        $encodedAd = json_encode($ad);
                        $json_error = json_last_error();
                        if ($json_error==5) {
                            error_log("JSON ERROR");
                            if (isset($ad['userLOC']) && $ad['userLOC']) {
                                $ad['userLOC']=$ad['ip'];
                                $encodedAd = json_encode($ad);
                                $json_error = json_last_error();
                            }
                        }

                        $result=$this->db->queryResultArray(
                                    "insert into ad_user
                                    (web_user_id, content, title, purpose_id, section_id, rtl, 
                                    country_id, city_id, latitude, longitude, media, state)
                                    values (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, {$state}) returning id,state", 
                                    [$this->getUID(), $encodedAd, $ad['pu'], $ad['se'], $ad['rtl'], 
                                     $country_id , $city_id, $ad['lat'], $ad['lon'], $ad['media']], 
                                    true);

                        if (!empty ($result)) {
                            $ad_id=$result[0]['ID'];
                            $state=(int)$result[0]['STATE'];
                                    
                            $st = $this->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                            $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                            $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                            $this->db->executeStatement($st);
                            unset($st);
                            
                            $pst = $this->db->getInstance()->prepare("INSERT INTO AD_MEDIA (AD_ID, MEDIA_ID) values (?, ?)");
                                                        
                            if (isset($ad['pics']) && is_array($ad['pics']) && count($ad['pics'])) {
                                $keys = array_keys($ad['pics']);
                                $filenames = '';
                                foreach ($ad['pics'] as $key => $values) {
                                    if ($filenames!='') { $filenames.=','; }
                                    $filenames .= "'{$key}'";
                                }

                                $records = $this->db->queryResultArray("select id from media where filename in ({$filenames})", null, false);
                                if ($records!==false && $records && is_array($records)) {
                                    foreach ($records as $media) {
                                        $pst->bindValue(1, $ad_id, PDO::PARAM_INT);
                                        $pst->bindValue(2, $media['ID'], PDO::PARAM_INT);       
                                        $this->db->executeStatement($pst);
                                    }                                                   
                                }
                            }
                            unset($pst);
                        }
                        
                        if ($requireReview && $ad_id) {
                            $this->db->get('update ad_object set super_admin=? where id=?', [$requireReview, $ad_id], false);
                            //$this->referrToSuperAdmin($ad_id);
                        }
                                
                        if ($state==1) {
                            if ($this->user->isMobileVerified()) {
                                $userState = $this->user->isSuspended() ? 1:0;
                            }
                            else {
                                $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                            }                          
                        }
                    }
                    
                    
                    if ($ad_id && $state==1) { 
                        $dbAd = $this->db->queryResultArray(
                                        'select a.id,
                                        IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) featured_date_ended, 
                                        IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended '                         
                                        . 'from ad_user a '
                                        . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 '
                                        . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '                                                       
                                        . 'where a.id = ?', [$ad_id], true);
                        
                        if (isset($dbAd[0]['ID']) && $dbAd[0]['ID']) {
                            $dbAd=$dbAd[0];
                            $current_time = time();
                            $isFeatured = isset($dbAd['FEATURED_DATE_ENDED']) && $dbAd['FEATURED_DATE_ENDED'] ? ($current_time < $dbAd['FEATURED_DATE_ENDED']) : false;
                            $isFeatureBooked = isset($dbAd['BO_DATE_ENDED']) && $dbAd['BO_DATE_ENDED'] ? ($current_time < $dbAd['BO_DATE_ENDED']) : false;
                            if ($isFeatured || $isFeatureBooked) { $state = 4; }
                        }
                    }
                    
                    if ($ad_id && $state==4 && $isMultiCountry) {
                        $q='update ad_user set content=?, state=? where id=?';
                        $suspendStmt = $this->db->getInstance()->prepare($q);
                        $state = 3;
                        if ($device_lang=='ar' || $ad['rtl']) {
                            $msg = 'عذراً ولكن لا يمكن تمييز الاعلان في اكثر من بلد واحد';
                        }
                        else {
                            $msg = 'Sorry, you cannot publish premium ads targetting more than ONE country';
                        }
                        $ad['state']=$state;
                        $ad['msg'] = $msg;

                        $encodedAd = json_encode($ad);
                        $result=null;
                        $this->db->executeStatement($suspendStmt, [$encodedAd, $state, $ad_id]);
                        unset($suspendStmt);
                    }
                    elseif ($ad_id && $userState==1) {
                        $state=3;
                        $q='update ad_user set content=?, state=? where id=?';
                        $suspendStmt = $this->db->getInstance()->prepare($q);

                        if ($device_lang=='ar' || $ad['rtl']) {
                            $msg = 'لقد تم ايقاف حسابك بشكل مؤقت نظراً للتكرار';
                        }
                        else {
                            $msg = 'your account is suspended due to repetition';
                        }
                        $ad['state']=$state;
                        $ad['msg'] = $msg;

                        $encodedAd = json_encode($ad);
                        $result=null;
                        $this->db->executeStatement($suspendStmt, [$encodedAd, $state, $ad_id]);
                        unset($suspendStmt);
                    } 
                    else if ($ad_id && in_array($ad['se'], array(190,1179,540,1114))) {
                        $dupliactePending = $this->detectIfAdInPending($ad_id, $ad['se'], $ad['cui']);
                        $state = 3;
                        if ($dupliactePending) {
                            $q='update ad_user set content=?, state=? where id=?';
                            $suspendStmt = $this->db->getInstance()->prepare($q);
                            if ($device_lang=='ar' || $ad['rtl']) {
                                $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                            }
                            else {
                                $msg = 'There is another similar ad pending Editors\' approval';
                            }
                            $ad['state'] = $state;
                            $ad['msg'] = $msg;

                            $encodedAd = json_encode($ad);
                            $result=null;
                            $this->db->executeStatement($suspendStmt, [$encodedAd, $state, $ad_id]);
                            unset($suspendStmt);
                        }
                    }
                }
            }
            
            $ad['state']=$state;        
            $this->result['d'] = [];                    
            $this->result['d']['adid'] = $ad_id;
            $this->result['d']['normalized'] = is_array($ad) ? $ad : []; 
                    
            unset($stmt);
        }
    }
    
        
    function detectIfAdInPending($adId, $sectionId, $contactInfo=array()){
        $active_ads = 0;
        if (count($contactInfo) && $this->getUID()) {
            $q='select a.id from ad_user a where (a.id!='.$adId.' and a.section_id ='.$sectionId.' and a.state in (1,2)) and ( ';
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
    
    
    function normalizeText(){
        if ($this->config['active_maintenance']) {
            $this->result['e'] = "503";
            return;
        }
                
        $normalized = NULL;
        $opts = $this->userStatus($status);
   
        if ($status==1 && !$this->user->isBlocked()) {
            
            $handle = fopen("php://input", "rb");
            $raw_post_data = '';
            while (!feof($handle)) {
                $raw_post_data .= fread($handle, 8192);
            }
            fclose($handle);
            if (empty($raw_post_data)) {
                $raw_post_data='{}';
            }else{
                if(substr($raw_post_data, 0, 3) == 'ad='){
                    $raw_post_data = urldecode(substr($raw_post_data, 3));
                    $raw_post_data = preg_replace('/&$/', '', $raw_post_data);
                }
            }
            
            $ad = json_decode($raw_post_data, TRUE);
            include_once $this->config['dir'] . '/core/lib/MCSaveHandler.php';                
            $normalizer = new MCSaveHandler($this->config);
            
            $normalized = $normalizer->getFromContentObject($ad, true);
            if ($normalized) {
                $ad = $normalized;
            }    
        }        
        
        $this->result['d']['ad'] = is_array($ad) ? $ad : new stdClass(); 
    }
    
    function getUserAlbum(){
        if ($this->config['active_maintenance']) {
            $this->result['e'] = "503";
            return;
        }
        $opts = $this->userStatus($status);
   
        if ($status==1 && !$this->user->isBlocked()) {
            $uid = $this->getUID();
            $this->result['d']['imgs']=[];
            
            $q = 'select distinct x.id, x.filename from ad_user a
left join ad_media m on m.ad_id=a.id
left join media x on x.id=m.media_id
where a.web_user_id=? and a.state!=6 and a.state!=8 and x.id>0';
            
            $images = $this->db->get($q, [$uid], true, \PDO::FETCH_NUM);
            
            if($images !== false && is_array($images) && $count = count($images)){
                for($i = 0; $i < $count; $i++){
                    $this->result['d']['imgs'][] = $images[$i][0].'#mourjan'.$images[$i][1];
                }
            }
        }
    }
    
    function forwardNormalizeText(){
        if ($this->config['active_maintenance']) {
            $this->result['e'] = "503";
            return;
        }
                
        $opts = $this->userStatus($status);
   
        if ($status==1 && !$this->user->isBlocked()) {
            $text = $_GET['text'];  
            
            $this->result['d']['original']=$text;
            //for android
            if(isset($_GET['rid']) && is_numeric($_GET['rid'])){
                $this->result['d']['rid'] = $_GET['rid'];
            }
            
            $this->result['d']['text']='';
            $this->result['d']['words']=[];
                        
            try {
                $userAgent = 'Edigear-PHP/' . '1.0' . ' (+https://github.com/edigear/edigear-php)';
                $userAgent .= ' PHP/' . PHP_VERSION;
                $curl_version = curl_version();
                $userAgent .= ' curl/' . $curl_version['version'];
                
                if (empty($text)) {
                    return;
                }
                
                $ch = curl_init();   
                
                $p = [$text];
                //$arg = curl_escape($ch, $text);
                //error_log($arg);
                //curl_setopt($ch, CURLOPT_URL, "http://h8.mourjan.com:8080/v1/ad/text/{$arg}");
                curl_setopt($ch, CURLOPT_URL, "http://h8.mourjan.com:8080/v1/ad/spell");
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, 
                        ["Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", 
                         "Content-Type: application/json", "Accept-Encoding: gzip, deflate", 
                         "Pragma: no-cache",
                         "Cache-Control: no-cache"]);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                          
                $data_string = json_encode($p);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                $resp = \curl_exec($ch);                                
                
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if ($status==200 && is_string( $resp )) {                    
                    $json = \json_decode($resp, true);
                    if (json_last_error()==JSON_ERROR_NONE) {
                        $this->result['d']['text']= isset($json['text']) ? $json['text'] : "";
                        $this->result['d']['words']= isset($json['words']) ? $json['words'] : [];
                    }
                    else {
                        $this->result['d']['error_no']= json_last_error();
                        $this->result['e']= json_last_error_msg();
                    }
                }
                else {
                    $this->result['d']['error_no']=$status;
                }
                                                
            }
            catch (Exception $ex) {
                $this->result['d']['error_no']= json_last_error();
                $this->result['e']= $ex->getMessage();
            }
            finally {
                if (isset($ch) && is_resource($ch)) {
                    curl_close($ch);
                }
            }                        
        }        
        
    }
    
    
}

