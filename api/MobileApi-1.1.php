<?php

require_once 'vendor/autoload.php';
use MaxMind\Db\Reader;

class MobileApi
{
    var $config;
    var $result = array('e'=>'', 'c'=>0, 'd'=>array(), 'l'=>0); // l:log

    var $db;
    public $command;
    var $lang;
    var $demo;
    var $countryId;
    var $cityId;

    var $formatNumbers=1;
    var $mobileValidator=null;

    private static $stmt_get_ad = null;
    private static $stmt_get_ext = null;
    private static $stmt_get_loc = null;

    var $uid;
    var $uuid;
    

    function __construct($config) {
        $this->lang = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
        $this->demo = filter_input(INPUT_GET, 'demo', FILTER_VALIDATE_INT)+0;
        $this->unixtime = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT, ['options'=>['default'=>-1, 'min_range'=>1388534400, 'max_range'=>PHP_INT_MAX]]);
        $this->countryId = filter_input(INPUT_GET, 'country', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);
        $this->cityId = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);

        $this->uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
        $this->uuid = filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

        $this->config=$config;

        $this->db = new DB($this->config, TRUE);
        $this->result['server'] = $this->config['server_id'];
    }

    
    function getUID() {
        return $this->uid;
    }

    
    function getUUID() {
        return $this->uuid;
    }


    function getDatabase() {
        $this->result['unixtime']=$this->db->queryResultArray(
                "SELECT DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', CURRENT_TIMESTAMP) FROM RDB\$DATABASE",
                null, false, PDO::FETCH_NUM)[0][0];
        /*
        $this->result['d']['country']=$this->db->queryResultArray(
                "SELECT a.ID, a.NAME_AR, a.NAME_EN, trim(a.ID_2), a.BLOCKED, a.LONGITUDE,
                 a.LATITUDE, a.CODE, trim(a.CURRENCY_ID), a.LOCKED  
                 FROM COUNTRY a
                 left join SYNC_GEO_XREF x on x.table_id=1 and x.geo_id=a.ID
                 where a.UNIXTIME > ?
                 and not x.ID is null",
                [$this->unixtime], false, PDO::FETCH_NUM);
                */
        $dict = $this->db->getCountriesDictionary();
        $ds = [];
        foreach ($dict as $id=>$record) 
        {
            if ($record[10] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], strtoupper($record[3]), 0, $record[7], $record[8], $record[5], $record[4], $record[9]];
            }
        }
        $this->result['d']['country'] = $ds;
        /*
        $this->result['d']['city']=$this->db->queryResultArray(
                "SELECT a.ID, a.NAME_AR, a.NAME_EN, a.BLOCKED, a.COUNTRY_ID, a.LATITUDE, a.LONGITUDE, a.LOCKED, a.uri 
                 FROM CITY a
                 left join SYNC_GEO_XREF x on x.table_id=2 and x.geo_id=a.ID 
                 where a.UNIXTIME > ?
                 and not x.ID is null",
                [$this->unixtime], false, PDO::FETCH_NUM);
         * 
         */
        $dict = $this->db->getCitiesDictionary(TRUE); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[8] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], 0, $record[4], $record[5], $record[6], $record[7], $record[3]];
            }            
        }
        $this->result['d']['city'] = $ds;

        /*
        $this->result['d']['purpose']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, BLOCKED, uri FROM PURPOSE WHERE UNIXTIME > ?",
                [$this->unixtime], false, PDO::FETCH_NUM);
         * 
         */
        $dict = $this->db->getPurposes(); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[4] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], 0, $record[3]];
            }            
        }
        $this->result['d']['purpose']= $ds;
        
        /*
        $this->result['d']['root']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, BLOCKED, uri FROM ROOT WHERE UNIXTIME > ?",
                [$this->unixtime], false, PDO::FETCH_NUM);
        */
        $dict = $this->db->getRoots(); $ds = [];
        foreach ($dict as $id=>$record) {
            if ($record[6] > $this->unixtime) {
                $ds[] = [$id, $record[1], $record[2], $record[3], $record[4]];
            }            
        }
        $this->result['d']['root']=$ds;
   
        $this->result['d']['root_purpose_xref']=$this->db->queryResultArray(
                "SELECT ID, ROOT_ID, PURPOSE_ID FROM ROOT_PURPOSE_XREF WHERE UNIXTIME > ?",
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['section']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, ROOT_ID, BLOCKED, uri FROM SECTION WHERE UNIXTIME > ?",
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['tag'] = $this->db->queryResultArray(
                "SELECT ID, SECTION_ID, LANG, NAME, BLOCKED, uri FROM SECTION_TAG WHERE UNIXTIME > ?",
                [$this->unixtime], FALSE, PDO::FETCH_NUM);

        $this->result['d']['geo'] = $this->db->queryResultArray(
                "SELECT ID, COUNTRY_ID, CITY_ID, PARENT_ID, LANG, NAME, BLOCKED, ALTER_ID, uri FROM GEO_TAG WHERE COUNTRY_ID=? and UNIXTIME>?",
                [$this->countryId, $this->unixtime], FALSE, PDO::FETCH_NUM);

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
        if ($ad) {
            return $ad;
        }

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
                where ad.id = ?"
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
                    if ($is_set){
                        if(is_array($is_set)){
                            $ad[Classifieds::PICTURES_DIM][]=$is_set;
                        }
                        $ad[Classifieds::PICTURES][] = $pic;
                    }
                }
            }

            if(isset($decoder['cui'])){
                $ad[Classifieds::CONTACT_INFO] = $decoder['cui'];
            }
            
            if(isset($decoder['cut'])){
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
    

    function search($forceFavorite = false) {
        include_once $this->config['dir'].'/core/lib/SphinxQL.php';
        include_once $this->config['dir'].'/core/model/Classifieds.php';
        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();

        $num = 20;
        $filters = array();
        $keywords = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING, ['options'=>['default'=>""]]);
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        $favorite = filter_input(INPUT_GET, 'favorite', FILTER_VALIDATE_INT, ['options'=>['default'=>0]])+0;
        $sortLang = filter_input(INPUT_GET, 'sl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $sortBy = filter_input(INPUT_GET, 'sm', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
        
        $publisherType = filter_input(INPUT_GET, 'pt', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
        if(!in_array($publisherType, [0,1,2])){
            $publisherType = 0;
        }
        if($publisherType == 2){
            $publisherType = 3;
        }
        
        $isWatchlist = filter_input(INPUT_GET, 'iws', FILTER_SANITIZE_STRING, ['options'=>['default'=>0]]);

        $sphinxQL = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);
        $sphinxQL->setLimits($offset, $num);

        if ($favorite || $forceFavorite) {
            if($forceFavorite){
                $num=20;
            }
            $sphinxQL->setFilter('starred', $this->uid);
            $filters['starred'] = $this->uid;
        } else {
            $rootId = filter_input(INPUT_GET, 'root', FILTER_VALIDATE_INT)+0;
            $sectionId = filter_input(INPUT_GET, 'section', FILTER_VALIDATE_INT)+0;
            $purposeId = filter_input(INPUT_GET, 'purpose', FILTER_VALIDATE_INT)+0;
            $tagId = filter_input(INPUT_GET, 'tag', FILTER_VALIDATE_INT)+0;
            $localityId = filter_input(INPUT_GET, 'locality', FILTER_VALIDATE_INT)+0;
            $adId = filter_input(INPUT_GET, 'aid', FILTER_VALIDATE_INT)+0;

            if ($adId) $sphinxQL->setFilter('id', $adId);
            if ($this->countryId) $sphinxQL->setFilter('country', $this->countryId);
            if ($this->cityId)    $sphinxQL->setFilter('city', $this->cityId);
            if ($rootId)          $sphinxQL->setFilter('root_id', $rootId);
            if ($sectionId)       $sphinxQL->setFilter('section_id', $sectionId);
            if ($tagId)           $sphinxQL->setFilter('section_tag_id', $tagId);
            if ($localityId)      $sphinxQL->setFilter('locality_id', $localityId);
            if ($purposeId)       $sphinxQL->setFilter('purpose_id', $purposeId);
            if ($publisherType)   $sphinxQL->setFilter('publisher_type', $publisherType); 
        }

        if ($sortLang=='ar') {
            $sphinxQL->setSelect("id, date_added, IF(rtl>0,0,1) as lngmask, IF(featured_date_ended>NOW(),1,0) as featured, media");
            $sphinxQL->SetSortBy("lngmask asc, featured desc, date_added desc");
        } elseif ($sortLang=='en') {
            $sphinxQL->setSelect("id, date_added, IF(rtl<>1,0,1) as lngmask, IF(featured_date_ended>NOW(),1,0) as featured, media");
            $sphinxQL->SetSortBy('lngmask asc, featured desc, date_added desc');
        } else {
            $sphinxQL->setSelect("id, date_added, IF(featured_date_ended>NOW(),1,0) as featured, media");
            if($isWatchlist){
                if($sortBy==0){
                    $sphinxQL->SetSortBy('date_added desc');
                }elseif($sortBy==1){
                    $sphinxQL->SetSortBy('media desc,date_added desc');
                }else{
                    $sphinxQL->SetSortBy('date_added desc');
                }
            }else{
                if($sortBy==0){
                    $sphinxQL->SetSortBy('featured desc, date_added desc');
                }elseif($sortBy==1){
                    $sphinxQL->SetSortBy('featured desc,media desc,date_added desc');
                }else{
                    $sphinxQL->SetSortBy('date_added desc');
                }
            }
        }

        $query = $sphinxQL->Query($keywords, MYSQLI_NUM);

        if ($sphinxQL->getLastError()) {
            $this->result['e'] = $sphinxQL->getLastError();
        } else {
            $this->result['total']=$query['total_found']+0;
            if (isset($query['matches'])) {
                //$is_ios_client = isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'CFNetwork');
                
                $model = new Classifieds($this->db);
                
                foreach ($query['matches'] as $matches) {
                    $count = count($matches);

                    //$ad = $this->getClassified($matches[0]+0);
                    $ad = $model->getById($matches[0]+0);
                    if ($ad) {
                        unset($ad[Classifieds::TITLE]);
                        unset($ad[Classifieds::ALT_TITLE]);
                        
                        unset($ad[Classifieds::CANONICAL_ID]);
                        unset($ad[Classifieds::CATEGORY_ID]);
                        unset($ad[Classifieds::SECTION_NAME_AR]);
                        unset($ad[Classifieds::SECTION_NAME_EN]);
                        unset($ad[Classifieds::HELD]);

                        $emails = $ad[Classifieds::EMAILS];
                        //$telNumbers = $ad[Classifieds::TELEPHONES];
                        //$this->result['tels'][] = $ad[Classifieds::TELEPHONES];
                        /*
                        if ($is_ios_client && is_array($telNumbers) && count($telNumbers)>2 && empty($telNumbers[0]) && !empty($telNumbers[2])) 
                        {
                            $telNumbers[0]=$telNumbers[2];
                            unset($telNumbers[2]);
                            $ad[Classifieds::TELEPHONES] = $telNumbers;
                        }                                                                                           
                        */
                        //$tmpContent = $ad[Classifieds::CONTENT];

                        //$telNumbers = [];
                        $this->cutOfContacts($ad[Classifieds::CONTENT]);
                        //$this->processTextNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $telNumbers);

                        $ad[Classifieds::CONTENT] = strip_tags($ad[Classifieds::CONTENT]);

                        if($ad[Classifieds::ALT_CONTENT]!=""){
                            //$tmpTel = [];
                            //$this->processTextNumbers($ad[Classifieds::ALT_CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $telNumbers);
                            $this->cutOfContacts($ad[Classifieds::ALT_CONTENT]);
                            $ad[Classifieds::ALT_CONTENT] = strip_tags($ad[Classifieds::ALT_CONTENT]);
                        }
                        
                        if (!empty($emails)) {
                            $j=0;
                            $email_regex='';
                            foreach ($emails as $email){
                                if($j++)$email_regex.='|';
                                $email_regex .= addslashes($email);
                            }
                            
                            //check if email still exists after stripping phone numbers
                            $strpos = strpos($ad[Classifieds::CONTENT], $email);
                            if($strpos){
                                $ad[Classifieds::CONTENT] = trim(substr($ad[Classifieds::CONTENT],0, $strpos));
                                $ad[Classifieds::CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::CONTENT]));
                            }

                            if($ad[Classifieds::ALT_CONTENT]!=""){
                                $strpos = strpos($ad[Classifieds::ALT_CONTENT], $email);
                                if($strpos){
                                    $ad[Classifieds::ALT_CONTENT] = trim(substr($ad[Classifieds::ALT_CONTENT],0, $strpos));
                                    $ad[Classifieds::ALT_CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::ALT_CONTENT]));
                                }
                            }
                            
                        }
/*
                        $emails = $this->detectEmail($tmpContent);
                        if($emails && count($emails)){
                            $emails = $emails[0];
                            if($emails && count($emails)){
                                $j=0;
                                $email_regex='';
                                foreach ($emails as $email){
                                    if($j++)$email_regex.='|';
                                    $email_regex .= addslashes($email);
                                }
                                //check if email still exists after stripping phone numbers
                                $strpos = strpos($ad[Classifieds::CONTENT], $email);
                                if($strpos){
                                    $ad[Classifieds::CONTENT] = trim(substr($ad[Classifieds::CONTENT],0, $strpos));
                                    $ad[Classifieds::CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::CONTENT]));
                                }

                                if($ad[Classifieds::ALT_CONTENT]!=""){
                                    $strpos = strpos($ad[Classifieds::ALT_CONTENT], $email);
                                    if($strpos){
                                        $ad[Classifieds::ALT_CONTENT] = trim(substr($ad[Classifieds::ALT_CONTENT],0, $strpos));
                                        $ad[Classifieds::ALT_CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::ALT_CONTENT]));
                                    }
                                }
                            }
                        }
                        
                        $ad[Classifieds::EMAILS] = $emails;
                        $ad[Classifieds::TELEPHONES] = $telNumbers;
*/
                        
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
                            isset($ad[Classifieds::PICTURES]) ? $ad[Classifieds::PICTURES] : "",//19
                            isset($ad[Classifieds::VIDEO]) ? $ad[Classifieds::VIDEO] : "",//20
                            isset($ad[Classifieds::EXTENTED_AR]) ? $ad[Classifieds::EXTENTED_AR] : "",//21
                            isset($ad[Classifieds::EXTENTED_EN]) ? $ad[Classifieds::EXTENTED_EN] : "",//22
                            isset($ad[Classifieds::LOCALITY_ID]) ? $ad[Classifieds::LOCALITY_ID] : 0,//23
                            isset($ad[Classifieds::LOCALITIES_AR]) ? $ad[Classifieds::LOCALITIES_AR] : "",//24
                            isset($ad[Classifieds::LOCALITIES_EN]) ? $ad[Classifieds::LOCALITIES_EN] : "",//25
                            isset($ad[Classifieds::USER_LEVEL]) ? $ad[Classifieds::USER_LEVEL] : 0,//26
                            isset($ad[Classifieds::LOCATION]) ? $ad[Classifieds::LOCATION] : "",//27
                            isset($ad[Classifieds::PICTURES_DIM]) ? $ad[Classifieds::PICTURES_DIM] : "",//28
                            $ad[Classifieds::TELEPHONES], //29
                            $ad[Classifieds::EMAILS],//30
                            //featured flag
                            $matches[2]+0,//31
                            isset($ad[Classifieds::CONTACT_INFO]) ? $ad[Classifieds::CONTACT_INFO] : "",//32 (revise for production)
                            isset($ad[Classifieds::CONTACT_TIME]) ? $ad[Classifieds::CONTACT_TIME] : "",//33 (revise for production)
                            isset($ad[Classifieds::PUBLISHER_TYPE]) ? $ad[Classifieds::PUBLISHER_TYPE] : 0//34
                        ];


                    }
                }
            }
        }

    }


    function sphinxTotalsQL() {
        if ($this->countryId==0) return;
        //$this->db->getCache()->setOption(Memcached::OPT_PREFIX_KEY, $this->config['cache_prefix']);
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
            //$sphinx->setFacet('section_id', TRUE);
        }

        if ($sectionId) {
            $sphinx->setFilter('section_id', $sectionId);
            $group = "purpose_id";
            $sphinx->clearFacets();
            //$sphinx->setFacet('section_tag_id', TRUE);

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
                //$sphinx->setFacet("section_id, locality_id by locality_id");
            }
        } else {
           // $sphinx->setFacet("{$group}, purpose_id", TRUE);

            //    $sphinx->setSelect('groupby() AS attr_id, count(*) AS cnt');
        //} else {

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
        } else {
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
                    } else {
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
                    } else {
                        $this->result['d'][$i][]=[];
                    }
                }
            }
        }

        $this->db->getCache()->set($MCKey, $this->result['d'], $this->config['ttl_short']);
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
            $group_count = count($query['matches'][$i]);
            for ($g=0; $g<$group_count; $g++) {
                $sectionId = $query['matches'][$i][$g]['section_id']+0;
                if (!isset($arr[ $sectionId ])) {
                    $arr[$sectionId] = [];
                }
                $row=[$query['matches'][$i][$g]['groupby()']+0, $query['matches'][$i][$g]['count(*)']+0,[]];
                foreach (array_unique(explode(',', $query['matches'][$i][$g]['group_concat(purpose_id)'])) as $purposeId) {
                    $row[2][]=$purposeId+0;
                }
                $arr[$sectionId][]=$row;
            }
        }
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
    


    function userStatus(&$status, &$name=null, $device_name = null) {
        $name=null;
        if ($this->uid>0 && !empty($this->uuid)) {
            $status = 0;
            $q = $this->db->queryResultArray(
                    "select d.uid, u.opts, u.full_name,u.identifier,u.email,u.provider,u.profile_url,IIF(m.STATUS IS NULL, 10, m.STATUS) STATUS, "
                    . "IIF(m.SECRET is null, '', m.SECRET) secret, IIF(m.MOBILE is null, 0, m.MOBILE) mobile, u.lvl, "
                    . "DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', d.last_visit) as device_last_visit, "
                    . "DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', u.last_visit) as user_last_visit, "
                    . "d.disallow_purchase, d.cuid "
                    . "from web_users_device d "
                    . "left join web_users_mobile m on m.uid=d.uid "
                    . "left join web_users u on u.id = d.uid "
                    . "where d.uuid=? order by IIF(m.STATUS=1,1,0) desc, m.id desc", [$this->uuid]);
            //var_dump($q);
            //error_log(var_export($q,true));
            if ($q && count($q) ) {
                $opts = json_decode($q[0]['OPTS']);
                if(is_null($opts) || !is_object($opts)){
                    $opts = json_decode("{}");
                }
                
                //$opts->dump = $q;
                $opts->device_last_visit = $q[0]['DEVICE_LAST_VISIT'];
                $opts->user_last_visit = $q[0]['USER_LAST_VISIT'];
                $opts->user_status = $q[0]['STATUS']+0;
                $opts->user_level = $q[0]['LVL']+0;
                $opts->secret = $q[0]['SECRET'];
                $opts->phone_number = $q[0]['MOBILE']+0;
                $opts->disallow_purchase = $q[0]['DISALLOW_PURCHASE']+0;
                $opts->cuid = $q[0]['CUID'];
                
                if(in_array($q[0]['PROVIDER'], array('mourjan','facebook','twitter','yahoo','google','live','linkedin')))
                {
                    $opts->provider=$q[0]['PROVIDER'];
                    if($opts->provider=='mourjan'){
                        $opts->account=$q[0]['IDENTIFIER'];
                    }else if($opts->provider=='twitter'){
                        $opts->account=preg_replace('/http(?:s|)::\/\/twitter\.com\//','',$q[0]['PROFILE_URL']);
                    }else{
                        $opts->account=$q[0]['EMAIL'];
                    }
                }
                
                if ($this->uid==$q[0]['UID']) 
                {
                    if ($q[0]['LVL']!=5) 
                    {
                        $status=1;
                        $name=$q[0]['FULL_NAME'];
                    } 
                    else 
                    {
                        $status=9;
                        $name='';
                    }
                    return $opts;
                }
            }
            
            /*
            $q=$this->db->queryResultArray(
                    "select u.IDENTIFIER, u.USER_EMAIL, u.OPTS, u.FULL_NAME, IIF(m.STATUS IS NULL, 10, m.STATUS) STATUS, m.SECRET "
                    . "from web_users u "
                    . "left join WEB_USERS_MOBILE m on m.UID=u.id "
                    . "where u.id = {$this->uid}");

            if ($q && is_array($q) && count($q)==1) {
                $opts = json_decode($q[0]['OPTS']);
                $opts->user_status = $q[0]['STATUS']+0;
                $opts->secret=$q[0]['SECRET'];
                if (!empty($this->uuid) &&
                        ($this->uuid==$q[0]['USER_EMAIL'] ||
                        $this->uuid==$q[0]['IDENTIFIER'] ||
                        (isset($opts->iphone->uuid) && $opts->iphone->uuid==$uuid)))
                {
                    $status=1;
                    $name=$q[0]['FULL_NAME'];
                }
                return $opts;
            }
             *
             */
            return null;
        }else{
            $status = -9;
            return null;
        }
        //DO NOT DELETE <<<<<<<<<<<<<---------------------
        /*
         *  else if($device_name == 'Android' && $this->uid == 0 && !empty($this->uuid)){
            $status = 0;
            $q = $this->db->queryResultArray(
                "select d.uid, u.opts, u.full_name, IIF(m.STATUS IS NULL, 10, m.STATUS) STATUS, "
                . "IIF(m.SECRET is null, '', m.SECRET) secret, IIF(m.MOBILE is null, 0, m.MOBILE) mobile, u.lvl, "
                . "DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', d.last_visit) as device_last_visit, "
                . "DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', u.last_visit) as user_last_visit "
                . "from web_users_device d "
                . "left join web_users_mobile m on m.uid=d.uid "
                . "left join web_users u on u.id = d.uid "
                . "where d.uuid=? order by m.id desc", [$this->uuid]);
            if (!empty($q)) {
                $opts = json_decode($q[0]['OPTS']);
                //$opts->dump = $q;
                $opts->device_last_visit = $q[0]['DEVICE_LAST_VISIT'];
                $opts->user_last_visit = $q[0]['USER_LAST_VISIT'];
                $opts->user_status = $q[0]['STATUS']+0;
                $opts->user_level = $q[0]['LVL']+0;
                $opts->secret = $q[0]['SECRET'];
                $opts->phone_number = $q[0]['MOBILE']+0;
                $this->uid = $q[0]['UID'];
                if ($q[0]['LVL']!=5) {
                    $status=1;
                    $name=$q[0]['FULL_NAME'];
                } else {
                    $status=9;
                    $name='';
                }
                $this->cleanWebuserDeviceRecord($this->uid);
                return $opts;
            }else{
                $status=-9;
                return null;
            }
        }
         */
        
    }

    function clearWebuserDeviceRecord($uid=0){
        if($uid > 0) {
            
            //delete subscriptions
            $q="delete from subscription where web_user_id = ?";
            $this->db->queryResultArray($q, [$uid]);
            
            
            // delete favorites and update index
            $q="update web_users_favs set deleted = 1 where web_user_id = ? and deleted = 0 returning ad_id";
            $rs = $this->db->queryResultArray($q, [$uid], true);
            if ($rs && is_array($rs) && count($rs)>0) {
                include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id=?";
                $st = $this->db->getInstance()->prepare($q); 
                
                foreach ($rs as $rec) {        
                    if ($st->execute([$rec['AD_ID']])) {                        
                        if ($users=$st->fetch(PDO::FETCH_NUM)) {
                            $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$rec['AD_ID']}";
                        } else {
                            $q = "update {$this->config['search_index']} set starred=() where id={$rec['AD_ID']}";   
                        }
                        $sphinx->directUpdateQuery($q);
                    }
                }
                $sphinx->close();
                $st->closeCursor();
            }
            //delete active ads
            $this->db->queryResultArray(
                "update ad a set a.hold = 1 where a.hold = 0 and ((select d.web_user_id from ad_user d where d.id = ?) = ?)", [$uid, $uid],true
            );
            //delete ad_user ads
            $this->db->queryResultArray(
                "update ad_user set state = 8 where web_user_id = ?", [$uid]
            );
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
                        $rs = $this->db->queryResultArray($q, [$this->uid, $adid, $state], TRUE);

                        if ($rs && is_array($rs) && count($rs)==1) {
                            include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                            $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                            $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id={$adid}";
                            $st = $this->db->getInstance()->query($q);                    
                            if ($st) {                        
                                if ($users=$st->fetch(PDO::FETCH_NUM)) {
                                    $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$adid}";
                                } else {
                                    $q = "update {$this->config['search_index']} set starred=() where id={$adid}";   
                                }
                                $succeed= $sphinx->directUpdateQuery($q);
                            }
                    
                            if (!$succeed) {
                                $this->result['e'] = 'Could not add this advert to our search engine';
                            }               

                            $this->result['d']['id']=$rs[0]['ID']+0;

                        } else {
                            $this->result['d']=0;
                            $this->result['e']='Unable to add this advert to your favorite list';
                        }
                
                        break;
                    
                    case 1:
                        // Note and Farorite
                        $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id) returning id";
                        $rs = $this->db->queryResultArray($q, [$this->uid, $adid, $state], TRUE);
                        
                        if ($rs && is_array($rs) && count($rs)==1) {
                            include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                            $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                            $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id={$adid}";
                            $st = $this->db->getInstance()->query($q);                    
                            if ($st) {
                                if ($users=$st->fetch(PDO::FETCH_NUM)) {
                                    $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$adid}";
                                } else {
                                    $q = "update {$this->config['search_index']} set starred=() where id={$adid}";   
                                }
                                $succeed= $sphinx->directUpdateQuery($q);
                            }
                    
                            if (!$succeed) {
                                $this->result['e'] = 'Could not add this advert to our search engine';
                            } else {
                                $n = $this->db->queryResultArray(
                                    "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,?) matching(web_user_id, ad_id) returning id", 
                                    [$this->uid, $adid, $note, $state], true
                                );  
                            }

                            $this->result['d']['id']=$rs[0]['ID']+0;

                        } else {
                            $this->result['d']=0;
                            $this->result['e']='Unable to add this advert to your favorite list';
                        }
                        
                        break;
                    
                    case 2:
                        // Note Only
                        $this->db->queryResultArray(
                            "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,?) matching(web_user_id, ad_id) returning id", 
                            [$this->uid, $adid, $note, $state], true
                        );  
                        
                        break;

                    default:
                        break;
                }
                
                /*
                $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id) returning id";
                $rs = $this->db->queryResultArray($q, [$this->uid, $adid, $state], TRUE);

                if ($rs && is_array($rs) && count($rs)==1) {
                    $succeed=false;
                    include_once $this->config['dir'] . '/core/lib/SphinxQL.php';
                    $sphinx = new SphinxQL($this->config['sphinxql'], $this->config['search_index']);

                    $attributes = array('starred');
                    $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id={$adid}";
                    $st = $this->db->getInstance()->query($q);                    
                    if ($st) {                        
                        if ($users=$st->fetch(PDO::FETCH_NUM)) {
                            $q = "update {$this->config['search_index']} set starred=({$users[0]}) where id={$adid}";
                        } else {
                            $q = "update {$this->config['search_index']} set starred=() where id={$adid}";   
                        }
                        $succeed= $sphinx->directUpdateQuery($q);
                    }
                    
                    if (!$succeed) {
                        $this->result['e'] = 'Could not add this advert to our search engine';
                    } else {
                       
                        $n = $this->api->db->queryResultArray(
                                    "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,?) matching(web_user_id, ad_id) returning id", [$this->api->getUID(), $ad_id, $note], true
                                );  
                        
                    }

                    $this->result['d']['id']=$rs[0]['ID']+0;

                } else {
                    $this->result['d']=0;
                    $this->result['e']='Unable to add this advert to your favorite list';
                }
                */
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
                $rs = $this->db->queryResultArray(
                        "update or insert into SUBSCRIPTION "
                        . "(WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, TITLE, ADDED, EMAIL, PUBLISHER_TYPE) "
                        . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, 0, ?) "
                        . "matching (WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, PUBLISHER_TYPE) "
                        . "returning id", [$this->uid, $countryId, $cityId, $sectionId, $section_tag_id, $locality_id, $purpose_id, $terms, '', $pt], TRUE);

                if ($rs && is_array($rs) && count($rs)==1) {
                    $this->result['d']['id']=$rs[0]['ID']+0;
                } else {
                    $this->result['d']['id']=0;
                    $this->result['e']='Unable to add to your watch list';
                }
            } else {
                if ($wId>0) {
                    $rs = $this->db->queryResultArray(
                        "delete from SUBSCRIPTION WHERE id=? and web_user_id=?", [$wId, $this->uid], TRUE);


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
                    . "left join section_tag tag_ar on tag_ar.id=subs.section_tag_id and tag_ar.lang='ar' "
                    . "left join section_tag tag_en on tag_en.id=subs.section_tag_id and tag_en.lang='en' "
                    . "left join geo_tag geo_ar on geo_ar.id=subs.locality_id and geo_ar.lang='ar' "
                    . "left join geo_tag geo_en on geo_en.id=subs.locality_id and geo_en.lang='en' "
                    . "left join purpose on purpose.id=subs.purpose_id "
                    . "where subs.web_user_id=? "
                    . "order by subs.id  ", [$this->uid], TRUE, PDO::FETCH_NUM);
            $this->result['d']=$rs;
            //var_dump($this->result);
        }
    }


    function watchListVisited() {
        $wId = filter_input(INPUT_GET, 'wid', FILTER_VALIDATE_INT)+0;
        if ($wId) {
            $this->db->setWriteMode();
            $this->db->queryResultArray("update subscription set badge_count=0, last_visit=current_timestamp where id=?", [$wId], TRUE);
        }
    }


    function register() {
        $this->result['d']['info']=[
                'version'=>'1.0.2',
                'force_update'=>0, 
                'upload'=>'upload.mourjan.com',
                'images'=>'c1.mourjan.com'
                ];
            
        $this->db->setWriteMode();
        include_once $this->config['dir'] . '/core/lib/MCSessionHandler.php';
        new MCSessionHandler(TRUE);
        //$handler = new MCSessionHandler(TRUE);
        //session_set_save_handler($handler, true);
        //session_start();
        $current_name="";
        
        $device_name  = filter_input(INPUT_GET, 'dn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        if(strlen($device_name) > 50) {
            $device_name = substr($device_name, 0, 50);
        }
        $device_model = filter_input(INPUT_GET, 'dm', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        if(strlen($device_model) > 50) {
            $device_model = substr($device_model, 0, 50);
        }

        if ($device_model=='Calypso AppCrawler') {
            //error_log("Calypso AppCrawler {$this->uuid}");
            $this->uid = 284300;
            $this->uuid = '31D052EF-DCC8-4FBA-B180-4C7C50AECBC6';

        }

        $device_sysname  = filter_input(INPUT_GET, 'sn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        if(strlen($device_sysname) > 50) {
            $device_sysname = substr($device_sysname, 0, 50);
        }
        $device_sysversion = filter_input(INPUT_GET, 'sv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $carrier_country = filter_input(INPUT_GET, 'cc', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_appversion = filter_input(INPUT_GET, 'bv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $is_ping = filter_input(INPUT_GET, 'ping', FILTER_VALIDATE_INT)+0;
        $app_prefs = html_entity_decode(filter_input(INPUT_GET, 'prefs', FILTER_SANITIZE_STRING, ['options'=>['default'=>'{}']]));
        
        //Android Fix for lost UID
        if($device_sysname == 'Android' && $this->uid == 0 && $this->uuid){
            //error_log("Verifying if previous record exists for UUID {$this->uuid} with UID NIL\n");
            $oldUid = $this->db->queryResultArray("select uid from WEB_USERS_DEVICE "
                    . "where uuid = ? and device_sysname = 'Android'",
                    [$this->uuid], TRUE);
            if ($oldUid && count($oldUid)) {
                $this->uid = $oldUid[0]['UID'];
                //error_log("{$this->uuid} Old UID has been retrieved\n");
            //}else{
            //    error_log("{$this->uuid} no previous records\n");
            }
        }
        //End of Android Fix for lost UID
        
        
        $opts = $this->userStatus($status, $current_name, $device_name);


        $this->result['status']=9;
        if($device_sysname == 'Android'){
            //setting app params
            $this->result['d']['u_up'] = $this->config['android_url_upload'];
            $this->result['d']['u_web'] = $this->config['android_url_web'];
            $this->result['d']['u_img'] = $this->config['android_url_img'];
            $this->result['d']['u_api'] = $this->config['android_url_api'];
            $this->result['d']['u_nas'] = $this->config['android_url_node_ad_stage'];
            $this->result['d']['e_support'] = $this->config['android_email_support'];
            $this->result['d']['a_release'] = $this->config['android_app_release'];
            $this->result['d']['a_force'] = $this->config['android_app_release_enforce'];
            $this->result['d']['ed'] = $this->config['android_enabled_banner_detail']+0;
            $this->result['d']['es'] = $this->config['android_enabled_banner_search']+0;
            $this->result['d']['ee'] = $this->config['android_enabled_banner_exit']+0;
            $this->result['d']['epi'] = $this->config['android_enabled_banner_pending']+0;
            $this->result['d']['edi'] = $this->config['android_enabled_banner_detail_inter']+0;
        }

        if (empty($carrier_country)) {
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            //$geo = @geoip_record_by_name($ip);
        	$databaseFile = '/home/db/GeoLite2-City.mmdb';
        	$reader = new Reader($databaseFile);
        	$geo = $reader->get($ip);
        	$reader->close();
        
            if ($geo) {
                $country_code = trim(strtoupper(trim($geo['country']['iso_code'])));
                if(strlen($country_code)!=2)$country_code='';
            }
            
            $carrier_country = (isset($geo['country']['iso_code']) && strlen(trim($geo['country']['iso_code']))==2) ? strtoupper(trim($geo['country']['iso_code'])) : 'XX';
        }
        
        if ($status==1) {
            /* opts->user_status
             * 9: retired
             * 10: does not have web_users_mobile record (not activated mobile user)
             */
            if ($is_ping==0) {
            	$isUTF8 = preg_match('//u', $device_name);
                $this->db->queryResultArray("update or insert into WEB_USERS_DEVICE "
                    . "(uuid, uid, device_model, device_name, device_sysname, "
                    . "device_sysversion, last_visit, CARRIER_COUNTRY, APP_VERSION, APP_PREFS) "
                    . "values (?, ?, ?, ?, ?, ?, current_timestamp, ?, ?, ?)",
                    [$this->uuid, $this->uid, $device_model, ($isUTF8 ? $device_name : ''), $device_sysname,
                    $device_sysversion, $carrier_country, $device_appversion, $app_prefs], TRUE);
            }
            
            if($device_sysname == 'Android'){
                $this->result['d']['uid']=  $this->uid;
                //device last visit
                $this->result['d']['dlv'] = $opts->device_last_visit+0;
                //user last visit
                $this->result['d']['ulv'] = $opts->user_last_visit+0;
                //user level
                $this->result['d']['level'] = $opts->user_level+0;
                //provider
                if(isset($opts->provider)){
                    $this->result['d']['provider']=$opts->provider;
                }else{
                    $this->result['d']['provider']='';
                }
                //account name
                if(isset($opts->account)){
                    $this->result['d']['account']=$opts->account;
                }else{
                    $this->result['d']['account']='';
                }
                if (isset($opts->suspend)){
                    $time = time();
                    if ($opts->suspend > $time){
                        $this->result['d']['suspend']=$opts->suspend+0;
                    }
                }
            }
            
            $this->result['d']['blp'] = $opts->disallow_purchase+0;            
            $this->result['d']['status']=$opts->user_status;
            $this->result['d']['pwset']=!empty($opts->secret);
            
            if ($opts->cuid>0) 
            {
            	include_once $this->config['dir'] .'/core/model/User.php';
                
                $user = new User($this->db, $this->config, null, 0);
                        
                $ok = $user->mergeDeviceToAccount($this->uuid, $this->uid, $opts->cuid);
                if ($ok) 
                {
                    //$this->db->getInstance()->commit();
                    $this->uid=$opts->cuid;
                    $opts = $this->userStatus($status);
                    $this->result['d']['pwset']=!empty($opts->secret);
                    $this->result['d']['uid']=$opts->cuid;
                }
                
            	/*
            	$this->result['d']['uid'] = $opts->cuid;
            	update WEB_USERS_DEVICE set uid=?, cuid=0 where UUID = '?';
 
            	*/
            }

            $uname = filter_input(INPUT_GET, 'uname', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

            if ($uname && $uname!=$current_name) {
                $this->db->queryResultArray("update web_users set full_name=?, display_name=? where id=?", [$uname, $uname, $this->uid], true);
            }

            if (empty($uname) && $is_ping==0) {
                $this->db->queryResultArray("update web_users set last_visit=current_timestamp where id=?", [$this->uid], TRUE);
            }

            //error_log(json_encode($this->result['d']), 0);
            //!empty($opts->secret) &&
            if ( $opts->user_status==1) {

                include $this->config['dir'] .'/core/model/User.php';
                $user = new User($this->db, $this->config, null, 0);
                $user->sysAuthById($this->uid);
                $user->params['app']=1;
                $user->update();
                $this->result['d']['kuid'] = $user->encodeId($this->uid);
                $this->result['d']['uid']=  $this->uid;
                
                $this->getBalance();
                //error_log('App: ' . PHP_EOL . json_encode($user->info), 0);
                //error_log('App: ' . PHP_EOL . json_encode($_SESSION), 0);
            }
            
            
        } elseif ($status==-9 && !empty ($this->uuid)) {
            
            $q = $this->db->queryResultArray(
                    "insert into web_users (identifier, email, provider, full_name, profile_url, opts, user_name, user_email) "
                    . "values (?, '', '".($device_sysname == 'Android'?'mourjan-android':'mourjan-iphone')."', '', 'http://www.mourjan.com/', '{}', '', '')  returning id,lvl", [$this->uuid], TRUE);
            if ($q && is_array($q) && count($q)==1) {

                $this->result['d']['uid']=$q[0]['ID']+0;
                
                //return user level. nb: even if it's a new a record, taking into consideration
                //any triggers that might be implemented in future that might affect user status
                if($device_sysname == 'Android'){
                    $this->result['d']['level']=$q[0]['LVL'];
                    $this->result['d']['status']=10;
                    
                    //device last visit
                    $this->result['d']['dlv'] = 0;
                    //user last visit
                    $this->result['d']['ulv'] = 0;
                }
                //disallow purchase default 0
                $this->result['d']['blp'] = 0;

                $this->db->queryResultArray(
                        "insert into WEB_USERS_DEVICE "
                        . "(uuid, uid, device_model, device_name, device_sysname, device_sysversion, "
                        . "last_visit, push_id, NOTIFICATION_ENABLED, SNS_ID, CARRIER_COUNTRY, APP_VERSION) "
                        . "values (?, ?, ?, ?, ?, ?, current_timestamp, '', 1, '', ?, ?)",
                        [$this->uuid, $q[0]['ID'], $device_model, $device_name, $device_sysname,
                            $device_sysversion, $carrier_country, $device_appversion], TRUE);
            } else {
                $this->result['e'] = 'System error!';
                print_r($this->db->getInstance()->errorInfo());
            }

        } else {
            //$this->result['e']='Invalid user request!';
            //$this->result['d']=[0];
        }

    }


    function setApnsToken() {        
        $opts = $this->userStatus($status);
        $this->result['status']=$status;

        if ($status==1 || $status==-9) {
            $this->db->setWriteMode();
            $token=filter_input(INPUT_GET, 'tk', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            $rs = $this->db->queryResultArray("update WEB_USERS_DEVICE set PUSH_ID=? where uuid=? and PUSH_ID<>?", [$token, $this->uuid, $token], TRUE);
            if ($rs===FALSE) {
                $this->result['e']='Could not register notification token';
            }
        } else
            $this->result['e']='Invalid user status';
        $this->db->close();
    }


    function setNotification() {        
        $this->userStatus($status);
        $this->result['status']=$status;
        if ($status==1) {
            $this->db->setWriteMode();
            $enabled=filter_input(INPUT_GET, 'enabled', FILTER_VALIDATE_INT)+0;
            $this->db->queryResultArray("update WEB_USERS_DEVICE set NOTIFICATION_ENABLED=? where uuid=?", [$enabled, $this->uuid], TRUE);
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
                $this->db->setWriteMode();
                $rs = $this->db->queryResultArray("update WEB_USERS_MOBILE set SECRET=? where uid=? returning status, secret", [$np, $this->uid], TRUE);
                if ($rs) {
                    $this->result['d']['status']=$rs[0]['STATUS']+0;
                    $this->result['d']['pwset']=!empty($rs[0]['SECRET']);
                    return;
                }
            }
        }
        $this->result['e']='Could not set new password!';
    }


    function authenticate() {        
        $opts = $this->userStatus($status);

        $mobile_no = filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
        if ($status==1) {
            $secret=filter_input(INPUT_GET, 'secret', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

            if ($mobile_no>0 && !empty($secret)) {
                $rs = $this->db->queryResultArray("select * from WEB_USERS_MOBILE where mobile=? and secret=?", [$mobile_no, $secret]);
                //var_dump($rs);

                if (!empty($rs) && isset($rs[0]['STATUS']) && $rs[0]['STATUS']==1) {
                    $userId=$rs[0]['UID']+0;

                    $this->result['d']['status']=1;
                    $this->result['d']['uid']=($this->uid!=$userId) ? $userId : 0;


                    if ($this->uid!=$userId) {
                        $this->db->setWriteMode();

                        if ($this->db->queryResultArray("update web_users_device set uid=? where uuid=?", [$userId, $this->uuid], true)) {

                            $ok = $this->db->queryResultArray(
                                    "update web_users_favs a set a.web_user_id=? "
                                    . "where a.web_user_id=? "
                                    . "and not exists (select 1 from web_users_favs b "
                                    . "where b.web_user_id=? and b.ad_id=a.ad_id)", [$userId, $this->uid, $userId], true);
                            if ($ok) {
                                $ok = $this->db->queryResultArray(
                                    "update subscription a set a.web_user_id=? "
                                    . "where a.web_user_id = ? and "
                                    . "not exists (select 1 from subscription b "
                                    . "where b.web_user_id=? "
                                    . "and b.country_id=a.country_id and b.city_id=a.city_id and b.section_id=a.section_id "
                                    . "and b.purpose_id=a.purpose_id and b.section_tag_id=a.section_tag_id "
                                    . "and b.locality_id=a.locality_id and b.purpose_id=a.purpose_id and b.query_term=a.query_term)",
                                    [$userId, $this->uid, $userId], true);

                                if ($ok) {
                                    
                                    $this->db->queryResultArray("update T_PROMOTION_USERS t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    $this->db->queryResultArray("update T_TRAN t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    
                                    $ok = $this->db->queryResultArray("delete from web_users_favs where web_user_id=?", [$this->uid], true);
                                    if ($ok) {
                                        $ok = $this->db->queryResultArray("delete from subscription where web_user_id=?", [$this->uid], true);
                                        if ($ok) {
                                            $ok = $this->db->queryResultArray("delete from web_users where id=?", [$this->uid], true);
                                        }
                                    }
                                }
                            }
                            //error_log("Status: ". $ok ? "1":"0");

                            if ($ok) {

                                $this->db->getInstance()->commit();
                                $this->uid=$userId;
                                $opts = $this->userStatus($status);
                                $this->result['d']['pwset']=!empty($opts->secret);

                            } else {
                                $this->db->getInstance()->rollBack();
                                $this->result['e']="Could not activate your device due to internal system error!";
                                error_log($this->result['e'] . " " . $mobile_no . " to uid: " . $userId);
                            }
                        }

                    }

                    return;
                }
            }

            $this->result['e']="Invalid user and password for {$mobile_no}!";
        } else $this->result['e']="Not a valid user and/or password for {$mobile_no}!";

    }

/*
    function testNumberStarting() {
        $mobile_no = filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
        $sender = (strval($mobile_no)[0]=='1') ? '12165044111' : 'Mourjan';
        $this->result['d']['number']=$mobile_no;
        $this->result['d']['from']=$sender;
    }
*/
    function activate() {
        
        $opts = $this->userStatus($status);
        if ($status==1) {
            if ($opts->user_status==9) {
                $this->result['e'] = 'Your account is retired.'.chr(10).'Please remove Mourjan app and install it again to reactivate it.';
                return;
            }
            $mobile_no = filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
            $sender = (strval($mobile_no)[0]=='1') ? '12165044111' : 'Mourjan';

            if ($mobile_no<999999) {
                $this->result['e'] = 'Invalid mobile registration request';
                return;
            }

            include_once $this->config['dir'].'/core/lib/nexmo/NexmoMessage.php';

            $rs = $this->db->queryResultArray(
                    "select m.ID, m.UID, m.MOBILE, m.STATUS, m.DELIVERED, m.CODE, m.SMS_COUNT, "
                    . "datediff(SECOND from m.REQUEST_TIMESTAMP to CURRENT_TIMESTAMP) req_age "
                    . "from WEB_USERS_MOBILE m "
                    . "left join WEB_USERS_DEVICE d on d.UID=m.UID "
                    . "where m.mobile=? and d.uuid=?", [$mobile_no, $this->uuid]);

            //$this->result['d']['rs']=$rs;

            if (is_array($rs)) {
                $this->db->setWriteMode();
                
                if (count($rs)==0) {                    
                    //echo $mobile_no;
                    $pin = mt_rand(1000, 9999);
                    $iq = $this->db->queryResultArray(
                            "INSERT INTO WEB_USERS_MOBILE (UID, MOBILE, CODE, STATUS, DELIVERED, SMS_COUNT)
                            VALUES (?, ?, ?, 5, 0, 0) RETURNING ID", [$this->uid, $mobile_no, $pin], TRUE);
                    if ($iq[0]['ID']>0) {
                        $sms = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
                        
                        $response = $sms->sendText( "+{$mobile_no}", $sender,
                                "Your Mourjan code is:\n{$pin}\nClose this message and enter the code into Mourjan to activate your account.",
                                        $iq[0]['ID']);
                        //var_dump($response);
                        if ($response) {
                            $this->db->queryResultArray("update WEB_USERS_MOBILE set status=0, sms_count=sms_count+1 where id=?", [$iq[0]['ID']], TRUE);
                            $this->result['d']['status']='sent';
                        }
                    }
                } else {
                    //var_dump($rs);
                    if ($rs[0]['MOBILE']!=$mobile_no) {
                        $this->result['e']='Not a valid mobile for you';
                        return;
                    }

                    // Must not pass anymore after left join device matching uuid
                    if ($rs[0]['UID']!=$this->uid) {
                        if ($rs[0]['STATUS']==1) {
                            $this->result['d']['status']='validate';
                            $this->result['e']='This mobile number is already exists. Enter your password to activate your device';
                            return;
                        }
                    }

                    $pin_code = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT)+0;
                    if ($pin_code>999) {
                        if ($pin_code==$rs['0']['CODE']+0) {
                            
                            if ($this->db->queryResultArray("update WEB_USERS_MOBILE set status=1, activation_timestamp=current_timestamp where id=? returning status", [$rs[0]['ID']], TRUE)) {
                                $this->result['d']['status']='activated';

                                include $this->config['dir'] .'/core/model/User.php';
                                $user = new User($this->db, $this->config, null, 0);
                                $user->sysAuthById($this->uid);
                                $user->params['app']=1;
                                $user->update();
                                $this->result['d']['kuid'] = $user->encodeId($this->uid);
                                $this->getBalance();
                                
                                return;
                            } else {
                                $this->result['e'] = 'This mobile number is used on different device';
                                $this->result['d']['status']='invalid';
                                return;
                            }
                        } else {
                            $this->result['e'] = 'Activation code is not valid';
                            $this->result['d']['status']='invalid';
                            return;
                        }
                    } else {
                        if ($rs[0]['STATUS']==5) { // No sent SMS
                            $sms = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
                            $pin = $rs[0]['CODE'];
                            $response = $sms->sendText( "+{$mobile_no}", $sender,
                                "Your Mourjan code is:\n{$pin}\nClose this message and enter the code into Mourjan to activate your account.",
                                        $rs[0]['ID']);
                            //var_dump($response);
                            if ($response) {
                                $this->db->queryResultArray("update WEB_USERS_MOBILE set status=0, sms_count=sms_count+1 where id=?", [$rs[0]['ID']], TRUE);
                                $this->result['d']['status']='sent';
                            }

                        }
                    }

                    if ($rs[0]['DELIVERED']==1) {
                        $this->result['e'] = 'Activation code is already delivered to this mobile sms inbox';
                        $this->result['d']['status']='delivered';
                        return;
                    }

                    if ($rs[0]['REQ_AGE']<120) {
                        $this->result['e'] = 'Activation code is already sent, but not delivered yet.\nPlease wait a few minutes';
                        return;
                    }

                    if ($rs[0]['STATUS']==0 && $rs[0]['DELIVERED']==0 && $rs[0]['SMS_COUNT']>0) {
                        $this->result['e'] = 'Invalid mobile number! Please enter well formed mobile number to proceed.';
                        return;
                    }
                }
            }
        } else {
            $this->result['e'] = 'Invalid user status';
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


    function detectEmail($ad){
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/i', $ad, $matches);
        return $matches;
    }


    function cutOfContacts(&$text) {
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        
        if(!($content && count($content))){
            preg_match($phone, $text, $content);
            if(!($content && count($content))){
                return $text;
            }
        }

        if($content && count($content)){
            //$str=$content[1];

            $strpos = strpos($text, $content[0]);
            $text = trim(substr($text,0, $strpos));
            $text = trim(preg_replace('/[-\/\\\]$/', '', $text));
        
        }
    }

    
    function processTextNumbers(&$text, $pubId=0, $countryCode=0, &$matches=array()){
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        //preg_match('/( للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);

        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text,$content);
        if(!($content && count($content))){
            /*$tmpTxt=preg_replace('/\<.*?>/', '', $text);
            preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt,$content);*/
            preg_match($phone, $text, $content);
            if(!($content && count($content))){
                return $text;
            }
        }

        if($content && count($content)){
        $str=$content[1];

        $strpos = strpos($text, $content[0]);
        $text = trim(substr($text,0, $strpos));
        $text = trim(preg_replace('/[-\/\\\]$/', '', $text));

        if($str){
        if($this->formatNumbers){
            $nums=array();
            $numInst=array();
            $numbers = null;
            preg_match_all($phone, $str, $numbers);
            if($numbers && count($numbers[1])){
                foreach($numbers[1] as $match){
                    $number = $match;
                    try{
                        if($pubId==1){
                            $numInst[] = $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                        }else{
                            $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                        }
                        if($num && $this->mobileValidator->isValidNumber($num)){
                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                            if($rCode==$this->formatNumbers){
                                $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                            }else{
                                $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                            }
                            $nums[]=array($number, $num);
                        }else{
                            $hasCCode = preg_match('/^\+/', $number);
                            switch($countryCode){
                                case 'SA':
                                    if($hasCCode){
                                        $num = substr($number,4);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 9:
                                                $num='011'.$num;
                                                break;
                                            case 12:
                                            case 18:
                                                    $tmp='013'.$num;
                                                    $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                    if($tmp && $this->mobileValidator->isValidNumber($tmp)){
                                                        $num='013'.$num;
                                                    }else{
                                                        $num='011'.$num;
                                                    }
                                                break;
                                        }
                                    }
                                    break;
                                case 'EG':
                                    if($hasCCode){
                                        $num = substr($number,3);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                            case 14:
                                                $num='3'.$num;
                                                break;
                                        }
                                    }elseif(strlen($num)==8){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                        }
                                    }
                                    break;
                            }
                            if($num != $number){
                                $num = $this->mobileValidator->parse($num, $countryCode);
                                if($num && $this->mobileValidator->isValidNumber($num)){
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if($rCode==$this->formatNumbers){
                                        $num=$this->mobileValidator->formatInOriginalFormat($num, $this->formatNumbers);
                                    }else{
                                        $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num, $this->formatNumbers);
                                    }
                                    $nums[]=array($number, $num);
                                }else{
                                    $nums[]=array($number, $number);
                                }
                            }else{
                                $nums[]=array($number, $number);
                            }

                        }
                    }catch(Exception $ex){
                        $nums[]=array($number, $number);
                    }
                }
                $mobile=array();
                $phone=array();
                $undefined = array();
                $i=0;

                foreach($nums as $num){
                    if($num[0]!=$num[1]){
                        $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                        if($type==1 || $type==2)
                            $mobile[]=$num;
                        elseif($type==0 || $type==2)
                            $phone[]=$num;
                        else $undefined[]=$num;
                    }else{
                        $undefined[]=$num;
                    }
                }
                $matches=array(
                    $mobile,
                    $phone,
                    $undefined
                );
                
            }
        }else{
            if($pubId!=1){
                if(!preg_match('/\<span class/',$text)){
                    preg_match_all($phone, $str, $numbers);
                    if($numbers && count($numbers[1])){
                        foreach($numbers[1] as $match){
                            $number = $match;
                            $number =  preg_replace('/\+/','\\+' , $number);
                            ////$text = preg_replace('/('.$number.')/', '<span class="pn">$1</span>', $text);
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
        if ($status==1 && $opts->phone_number>0) {
            $phone_number=filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
            $current_phone_number=filter_input(INPUT_GET, 'ctel', FILTER_VALIDATE_INT)+0;

            if ($current_phone_number!=$opts->phone_number ) {
                $this->result['e']='Invalid user old phone number!';
                return;
            }

            if ($phone_number<999999) {
                $this->result['e'] = 'Invalid mobile registration request';
                return;
            }

            include_once $this->config['dir'].'/core/lib/nexmo/NexmoMessage.php';
            $rs = $this->db->queryResultArray(
                    "select m.ID, m.UID, m.MOBILE, m.STATUS, m.DELIVERED, m.CODE, SMS_COUNT, "
                    . "datediff(SECOND from m.REQUEST_TIMESTAMP to CURRENT_TIMESTAMP) req_age "
                    . "from WEB_USERS_MOBILE m where m.mobile=?", [$phone_number]);
            $this->result['d']['rs']=$rs;
            if (is_array($rs)) {
                $this->db->setWriteMode();
                if (count($rs)==0) {
                    //echo $mobile_no;
                    $pin = mt_rand(1000, 9999);
                    $iq = $this->db->queryResultArray(
                            "UPDATE WEB_USERS_MOBILE
                             SET CODE=?, MOBILE=?, STATUS=5, DELIVERED=0, SMS_COUNT=0
                             WHERE UID=? AND MOBILE=?
                             RETURNING ID", [$pin, $phone_number, $this->uid, $current_phone_number], TRUE);
                    if ($iq[0]['ID']>0) {
                        $sms = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
                        $response = $sms->sendText( "+{$phone_number}", 'Mourjan',
                                "Your Mourjan code is:\n{$pin}\nClose this message and enter the code into Mourjan to activate your account.",
                                        $iq[0]['ID']);
                        //var_dump($response);
                        if ($response) {
                            $this->db->queryResultArray("update WEB_USERS_MOBILE set status=0, sms_count=sms_count+1 where id=?", [$iq[0]['ID']], TRUE);
                            $this->result['d']['status']='sent';
                        }
                    }
                } else {
                    //var_dump($rs);
                    if ($rs[0]['MOBILE']!=$phone_number) {
                        $this->result['e']='Not a valid mobile for you';
                        return;
                    }

                    if ($rs[0]['UID']!=$this->uid) {
                        if ($rs[0]['STATUS']==1) {
                            $this->result['d']['status']='validate';
                            $this->result['e']='This mobile number is already exists. Enter your password to activate your device';
                            return;
                        }
                    }

                    $pin_code = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT)+0;
                    if ($pin_code>999) {
                        if ($pin_code==$rs['0']['CODE']) {
                            if ($this->db->queryResultArray("update WEB_USERS_MOBILE set status=1, activation_timestamp=current_timestamp where id=? returning status", [$rs[0]['ID']], TRUE)) {
                                $this->result['d']['status']='activated';
                                return;
                            } else {
                                $this->result['e'] = 'This mobile number is used on different device';
                                $this->result['d']['status']='invalid';
                                return;
                            }
                        } else {
                            $this->result['e'] = 'Activation code is not valid';
                            $this->result['d']['status']='invalid';
                            return;
                        }
                    }

                    if ($rs[0]['DELIVERED']==1) {
                        $this->result['e'] = 'Activation code is already delivered to this mobile sms inbox';
                        $this->result['d']['status']='delivered';
                        return;
                    }

                    if ($rs[0]['REQ_AGE']<120) {
                        $this->result['e'] = 'Activation code is already sent, but not delivered yet.\nPlease wait a few seconds';
                        return;
                    }
                }
            } else $this->result['e']='Internal system error!';
            //$this->result['opts']=$opts;
        }
        else $this->result['e']='Invalid user request!';
    }


    public function unregister() {        
        $opts = $this->userStatus($status);
        if ($status==1 && $opts->phone_number>0) {
            $phone_number=filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;

            if ($phone_number!=$opts->phone_number ) {
                $this->result['e']='Not a valid phone number!';
                return;
            }

            $this->db->setWriteMode();
            $q = $this->db->queryResultArray("update WEB_USERS_MOBILE set status=9 where uid=? and mobile=? returning status", [$this->uid, $phone_number], TRUE);
            if ($q[0]['STATUS']==9) {
                $this->result['d']['status']='deleted';
            } else {
                $this->result['d']['status']='failed';
            }
        }
        else $this->result['e']='Invalid user request!';

    }


    public function getCountryIsoByIp(){
        $ip = false;
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else {
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
        }else $this->result['e']='Bad Request!';
    }

    
    public function validatePhoneNumber(){
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

        if($phone_number && $country_code){
            
            require_once $this->config['dir'].'/core/lib/libphonenumber/MetadataLoader.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/MetadataLoaderInterface.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/DefaultMetadataLoader.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneNumberUtil.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/CountryCodeToRegionCodeMap.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneNumber.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneNumberFormat.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneMetadata.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneNumberDesc.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/NumberFormat.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/CountryCodeSource.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/Matcher.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/PhoneNumberType.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/NumberParseException.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/ValidationResult.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/MetadataSourceInterface.php';
            require_once $this->config['dir'].'/core/lib/libphonenumber/MultiFileMetadataSourceImpl.php';

            $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();


            $region_code = $this->mobileValidator->getRegionCodeForCountryCode($country_code);

            if($region_code){

                $number = $this->mobileValidator->parse($phone_number, $region_code);
                if($number && $this->mobileValidator->isValidNumber($number)){
                    $this->result['d']=array(
                        'type'  =>  $this->mobileValidator->getNumberType($number)
                    );
                }else{
                    $this->result['c']=ERR_INVALID_PHONE_NUMBER;
                }

            }else{
                $this->result['c']=ERR_INVALID_COUNTRY_CODE;
            }

        }else{
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
            if ( $opts->user_status==1) {
                include_once $this->config['dir'] . '/core/lib/MCSessionHandler.php';
                new MCSessionHandler(TRUE);
                //$handler = new MCSessionHandler(TRUE);
                //session_set_save_handler($handler, true);
    
                //session_start();

                include $this->config['dir'] .'/core/model/User.php';
                $user = new User($this->db, $this->config, null, 0);
                $user->sysAuthById($this->uid);
                $user->params['app']=1;
                $user->update();
            
            }
            
            $lang=filter_input(INPUT_GET, 'dl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);
            $rs = $this->db->queryResultArray(
                "select state id,
                count(*) ads
                from AD_USER
                where web_user_id=? and state in (0,1,2,3,7,9)
                group by 1", [$this->uid], TRUE, PDO::FETCH_NUM);

            foreach ($rs as $row) {
                $name = "";
                switch ($row[0]) {
                    case 0:
                        $name = $lang=='en' ? 'Draft ads' : 'المسودات';
                        break;
                    case 1:
                        $name = $lang=='en' ? 'Pending ads' : 'قيد التدقيق';
                        break;
                    case 2:
                        $name = $lang=='en' ? 'Approved ads' : 'موافق عليه';
                        break;
                    case 3:
                        $name = $lang=='en' ? 'Rejected ads' : 'مرفوض';
                        break;
                    case 4:
                        $name = $lang=='en' ? '' : '';
                        break;
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
        $sphinxQL->setFilter("publication_id", 1);        
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
                if(!is_object($data)){
                    $data = json_decode("{}");
                }
                if (isset($data->other)) {
                    $tl = mb_strlen(strip_tags($data->other));
                    if ($tl<60) {
                        $data->other.=mb_substr("                                                                                                            ",0,60-$tl);                   
                    }                    
                } else {
                    if (isset($data->text)) {
                        $tl = mb_strlen(strip_tags($data->text));
                        if ($tl<60) {
                            $data->other=$data->text. mb_substr("                                                                                                            ",0,60-$tl);                   
                        } else $data->other = $data->text;
                        
                    } else
                    $data->other='';
                }
                
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
                $rs = $this->db->queryResultArray("select WEB_USER_ID from ad_user where id=?", [$id], FALSE, PDO::FETCH_ASSOC);
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
                if (empty($rs)==false) {
                    $this->result['d']['state']=$rs[0]['STATE'];
                }
            }
        }
    }

    
    public function userRenewAd() {        
        $opts = $this->userStatus($status);
        $this->result['d']['state']=-1;
        if ($status==1) {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;
            if ($id) {
                $this->db->setWriteMode();
                $rs = $this->db->queryResultArray("update AD_USER set state=1 where id=? returning state", [$id], FALSE, PDO::FETCH_ASSOC);
                if (empty($rs)==false) {
                    $this->result['d']['state']=$rs[0]['STATE'];
                }
            }
        }
    }


    public function getBalance() {
        $rs = $this->db->queryResultArray("select sum(credit-debit) balance from T_TRAN where uid=?", [($this->demo===1?93778:$this->uid)]);
        $this->result['balance'] = $rs[0]['BALANCE']+0.0;
    }


    public function getStatment() {
        $opts = $this->userStatus($status);
        $this->result['d']['state']=-1;
        if ($status==1) {
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
            $rs = $this->db->queryResultArray(
                "SELECT sum(r.credit - r.debit)
                FROM T_TRAN r
                where r.UID=?", [$this->uid], true);
            if($rs && count($rs) && $rs[0]['SUM']!=null){
                if($rs[0]['SUM']){                    
                    $this->result['d']=$rs[0]['SUM']+0;
                }else{
                    $this->result['d']=0;                    
                }
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
            
        } else {
            $opts = $this->userStatus($status);
            $this->result['d']['order_id']=0;
            if ($status==1) {
                $start_date = date('Y-m-d h:i:s', $start);
                $this->result['start']=$start_date;

                $rs = $this->db->queryResultArray(
                    "INSERT INTO T_AD_BO (AD_ID, OFFER_ID, CREDIT, DATED, START_DATE, BLOCKED, DEMO) VALUES ".
                    "(?, ?, ?, current_timestamp, ?, 0, 0) RETURNING ID", [$adId, 1, $days, $start_date], TRUE);
                $this->result['d']['order_id']=$rs[0]['ID']+0;
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
            include_once $this->config['dir'].'/core/lib/nexmo/NexmoMessage.php';
            $sms = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');
            $response = $sender = (strval($phone_number)[0]=='1') ? '12165044111' : 'Mourjan';
            $sms->sendText( "+{$phone_number}", $sender, $text, $callback_reference);
            return $response;    
        } catch (Exception $e) {
            
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
                        $host = "https://dev.mourjan.com";
                    } else {
                        //$host = "https://h{$host_id}.mourjan.com";
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
                    
                    if ($userId>0 && $userId!=$this->uid) {
                        /*
                        
                        
                        $this->db->setWriteMode();

                        if ($this->db->queryResultArray("update web_users_device set uid=? where uuid=?", [$userId, $this->uuid], true)) {

                            $ok = $this->db->queryResultArray(
                                    "update web_users_favs a set a.web_user_id=? "
                                    . "where a.web_user_id=? "
                                    . "and not exists (select 1 from web_users_favs b "
                                    . "where b.web_user_id=? and b.ad_id=a.ad_id)", [$userId, $this->uid, $userId],true);
                            if ($ok) {
                                $ok = $this->db->queryResultArray(
                                    "update subscription a set a.web_user_id=? "
                                    . "where a.web_user_id = ? and "
                                    . "not exists (select 1 from subscription b "
                                    . "where b.web_user_id=? "
                                    . "and b.country_id=a.country_id and b.city_id=a.city_id and b.section_id=a.section_id "
                                    . "and b.purpose_id=a.purpose_id and b.section_tag_id=a.section_tag_id "
                                    . "and b.locality_id=a.locality_id and b.purpose_id=a.purpose_id and b.query_term=a.query_term)",
                                    [$userId, $this->uid, $userId],true);

                                if ($ok) {
                                    
                                    $this->db->queryResultArray("update T_PROMOTION_USERS t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    $this->db->queryResultArray("update T_TRAN t set t.UID=? where t.UID=?", [$userId, $this->uid], true);
                                    
                                    $ok = $this->db->queryResultArray("delete from web_users_favs where web_user_id=?", [$this->uid], true);
                                    if ($ok) {
                                        $ok = $this->db->queryResultArray("delete from subscription where web_user_id=?", [$this->uid], true);
                                        if ($ok) {
                                            $ok = $this->db->queryResultArray("delete from web_users where id=?", [$this->uid], true);
                                        }
                                    }
                                }
                            }
                         * 
                         * 
                         */
                            //error_log("Status: ". $ok ? "1":"0");

                        
                        include $this->config['dir'] .'/core/model/User.php';
                        $user = new User($this->db, $this->config, null, 0);
                        
                        $ok = $user->mergeDeviceToAccount($this->uuid, $this->uid, $userId);
                        if ($ok) {

                            //$this->db->getInstance()->commit();
                            $this->uid=$userId;
                            $opts = $this->userStatus($status);
                            $this->result['d']['pwset']=!empty($opts->secret);
                            $this->result['d']['uid']=$userId;

                        } else {
                            //$this->db->getInstance()->rollBack();
                            $this->result['e']="Could not activate your device due to internal system error!";
                            error_log($this->result['e'] . " " . $this->uid . " to uid: " . $userId);
                        }
                         
                    }
                }
            } else {
                $this->result['e'] = 'Your session is expired!';
            }
        }
        
    }
    
    
    function getFavoriteAds() {
        
    }
    
    
    function getAdUserNote() {
        $ad_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)+0;

        
        if ($ad_id>0)
            $rs = $this->db->queryResultArray("select ad_id, web_users_notes.content from web_users_notes left join ad on ad.id=web_users_notes.ad_id where web_user_id=? and deleted=0 and ad.hold=0 and ad_id=?", [$this->uid, $ad_id]);
        else 
            $rs = $this->db->queryResultArray("select ad_id, web_users_notes.content from web_users_notes left join ad on ad.id=web_users_notes.ad_id where web_user_id=? and deleted=0 and ad.hold=0", [$this->uid]);
                    
        
        if ($rs)
            $this->result['d'] = $rs;
        
        
    }
}

