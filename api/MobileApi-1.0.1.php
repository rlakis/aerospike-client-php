<?php

class MobileApi {
    var $config;
    var $result = array('e'=>'', 'c'=>0,'d'=>array());

    var $db;
    var $command;
    var $lang;
    var $countryId;
    var $cityId;
    
    var $formatNumbers=1;
    var $mobileValidator=null;

    private static $stmt_get_ad = null;
    private static $stmt_get_ext = null;
    private static $stmt_get_loc = null;

    private $uid;
    private $uuid;
    
    
    function __construct($config) 
    {
        $this->lang = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_STRING, ['options'=>['default'=>'en']]);        
        $this->unixtime = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT, ['options'=>['default'=>-1, 'min_range'=>1388534400, 'max_range'=>PHP_INT_MAX]]);
        $this->countryId = filter_input(INPUT_GET, 'country', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);
        $this->cityId = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_INT, ['options'=>['default'=>0, 'min_range'=>0, 'max_range'=>100000]]);
        
        $this->uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
        $this->uuid = filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        
        $this->config=$config;

        $this->db = new DB($this->config);
        $this->result['server'] = $this->config['server_id'];
    }

    
    function getDatabase() {
        $this->result['unixtime']=$this->db->queryResultArray(
                "SELECT DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', CURRENT_TIMESTAMP) FROM RDB\$DATABASE", 
                null, false, PDO::FETCH_NUM)[0][0];

        $this->result['d']['country']=$this->db->queryResultArray(
                "SELECT a.ID, a.NAME_AR, a.NAME_EN, trim(a.ID_2), a.BLOCKED, a.LONGITUDE,
                 a.LATITUDE, a.CODE, trim(a.CURRENCY_ID), a.LOCKED
                 FROM COUNTRY a
                 left join SYNC_GEO_XREF x on x.table_id=1 and x.geo_id=a.ID
                 where a.UNIXTIME > ?
                 and not x.ID is null", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['city']=$this->db->queryResultArray(
                "SELECT a.ID, a.NAME_AR, a.NAME_EN, a.BLOCKED, a.COUNTRY_ID, a.LATITUDE, a.LONGITUDE, a.LOCKED
                 FROM CITY a
                 left join SYNC_GEO_XREF x on x.table_id=2 and x.geo_id=a.ID
                 where a.UNIXTIME > ?
                 and not x.ID is null", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['purpose']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, BLOCKED FROM PURPOSE WHERE UNIXTIME > ?", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['root']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, BLOCKED FROM ROOT WHERE UNIXTIME > ?", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['root_purpose_xref']=$this->db->queryResultArray(
                "SELECT ID, ROOT_ID, PURPOSE_ID FROM ROOT_PURPOSE_XREF WHERE UNIXTIME > ?", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['section']=$this->db->queryResultArray(
                "SELECT ID, NAME_AR, NAME_EN, ROOT_ID, BLOCKED FROM SECTION WHERE UNIXTIME > ?", 
                [$this->unixtime], false, PDO::FETCH_NUM);

        $this->result['d']['tag'] = $this->db->queryResultArray(
                "SELECT ID, SECTION_ID, LANG, NAME, BLOCKED FROM SECTION_TAG WHERE UNIXTIME > ?", 
                [$this->unixtime], FALSE, PDO::FETCH_NUM);
        
        $this->result['d']['geo'] = $this->db->queryResultArray(
                "SELECT ID, COUNTRY_ID, CITY_ID, PARENT_ID, LANG, NAME, BLOCKED, ALTER_ID FROM GEO_TAG WHERE COUNTRY_ID=? and UNIXTIME>?", 
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

    private function getClassified($id) {
        $ad = $this->db->cacheGet($id);
        if ($ad) {
            return $ad;
        }
        
        if (!self::$stmt_get_ad) {
            self::$stmt_get_ad = $this->db->getInstance()->prepare(
                "select 
                    ad.id, ad.hold, ad.title, ad.publication_id, ad.country_id, ad.city_id, 
                    section.category_id, ad.purpose_id, section.root_id, ad.content, ad.rtl, 
                    ad.date_added, ad.section_id, trim(country.id_2), 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.DATE_ADDED), 
                    ad.canonical_id, ad.expiry_date, link.url flink, 
                    '/'||lower(country.ID_2)||'/'||city.uri||'/'||section.uri||'/'||purpose.uri||'/%s%d/' uri,
                    section.name_ar, section.name_en, 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.LAST_UPDATE),
                    ad_user.latitude, ad_user.longitude,
                    ad_translated.title alter_title, ad_translated.content alter_content,
                    ad_user.web_user_id,                     
                    wu.user_rank,
                    IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', featured.ended_date)) featured_date_ended, 
                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', bo.end_date)) bo_date_ended, 
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
                left join t_ad_featured featured on featured.bo_id=bo.id and featured.ended_date>=current_timestamp  
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
            $ad[Classifieds::USER_RANK] = $row[$count-4];
            $ad[Classifieds::FEATURE_ENDING_DATE] = $row[$count-3];
            $ad[Classifieds::BO_ENDING_DATE] = $row[$count-2];
                
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
            $this->db->cacheSet($id, $ad, $this->config['ttl_long']);
            return $ad;
        }
        return FALSE;
    }
    
    private function getSphinx() {
        $sphinx = new SphinxClient();
        $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
        $sphinx->SetConnectTimeout(1000);
        $sphinx->SetServer($this->config['search_host'], $this->config['search_port']);
        $sphinx->SetFilter('hold', array(0));
        $sphinx->SetFilter('canonical_id', array(0));
        return $sphinx;
    }
    
    
    function search() {
            require_once  $this->config['dir'].'/core/lib/libphonenumber/MetadataLoader.php';
            require_once  $this->config['dir'].'/core/lib/libphonenumber/DefaultMetadataLoader.php';
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
        
        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        $this->formatNumbers="LB";
        
        $keywords = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
 
        $num = 20;
        
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT)+0;
        
        $favorite = filter_input(INPUT_GET, 'favorite', FILTER_VALIDATE_INT)+0;
        
        $sortLang = filter_input(INPUT_GET, 'sl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        
        $sphinx = $this->getSphinx();
        if ($favorite) {
            $sphinx->setFilter('starred', [$this->uid]);
        } else {
            $rootId = filter_input(INPUT_GET, 'root', FILTER_VALIDATE_INT)+0;
            $sectionId = filter_input(INPUT_GET, 'section', FILTER_VALIDATE_INT)+0;
            $purposeId = filter_input(INPUT_GET, 'purpose', FILTER_VALIDATE_INT)+0;
            $tagId = filter_input(INPUT_GET, 'tag', FILTER_VALIDATE_INT)+0;
            $localityId = filter_input(INPUT_GET, 'locality', FILTER_VALIDATE_INT)+0;
            
            if ($this->countryId) {
                $sphinx->setFilter('country', [$this->countryId]);
            }
            if ($this->cityId) {
                $sphinx->setFilter('city', [$this->cityId]);
            }
             if ($rootId) {
                $sphinx->setFilter('root_id', [$rootId]);
            }
            if ($sectionId) {
                $sphinx->setFilter('section_id', [$sectionId]);
            }
            if ($tagId) {
                $sphinx->setFilter('section_tag_id', [$tagId]);
            }
            if ($localityId) {
                $sphinx->setFilter('locality_id', [$localityId]);
            }
            if ($purposeId) {
                $sphinx->setFilter('purpose_id', [$purposeId]);
            }
        }
        
        if ($sortLang=='ar') {
            $sphinx->SetSelect("date_added, IF(rtl>0,0,1) as lngmask" );
            $sphinx->SetSortMode(SPH_SORT_EXTENDED, 'lngmask asc, date_added desc');
        } elseif ($sortLang=='en') {
            $sphinx->SetSelect("date_added, IF(rtl<>1,0,1) as lngmask" );
            $sphinx->SetSortMode(SPH_SORT_EXTENDED, 'lngmask asc, date_added desc');
        } else {
            $sphinx->SetSortMode(SPH_SORT_EXTENDED, 'date_added desc');
        }
        
        /*
        if (isset($this->user->params['last_visit']) && $this->user->params['last_visit']) {
                    $cl->SetSelect(
                        "impressions,if(date_added>{$this->user->params['last_visit']}, 1, 0) newad,
                        date_added, {$lng}");
                    $cl->SetSortMode(SPH_SORT_EXTENDED, 'lngmask asc, newad desc, date_added desc');
                } else {
                    $cl->SetSelect(
                            "impressions,0 as newad,
                            date_added, {$lng}" );
                    $cl->SetSortMode(SPH_SORT_EXTENDED, 'lngmask asc, date_added desc');
                }
        */
        //$sphinx->SetSelect("impressions,0 as newad, date_added, {$lng}" );
        //$sphinx->SetSortMode(SPH_SORT_EXTENDED, 'lngmask asc, date_added desc');
        
        $sphinx->SetLimits($offset, $num, $this->config['search_results_max']);

        $query = $sphinx->Query($keywords, $this->config['search_index']);


        if ($sphinx->getLastError()) {
            $this->result['e'] = $sphinx->getLastError();
        } else {
            //var_dump($query);
            include_once $this->config['dir'].'/core/model/Classifieds.php';
            $this->result['total']=$query['total_found']+0;
            if (isset($query['matches'])) {
                //$this->result['ads'] = [];
                $keys = array_keys($query['matches']);
                $count = count($keys);
                for ($i=0; $i<$count; $i++) {
                    $ad = $this->getClassified($keys[$i]+0);
                    if ($ad) {
                        //var_dump($ad);
                        
                       
                        //var_dump();
                        
                        unset($ad[Classifieds::TITLE]);
                        unset($ad[Classifieds::ALT_TITLE]);
                        unset($ad[Classifieds::CANONICAL_ID]);
                        unset($ad[Classifieds::CATEGORY_ID]);
                        unset($ad[Classifieds::SECTION_NAME_AR]);
                        unset($ad[Classifieds::SECTION_NAME_EN]);
                        unset($ad[Classifieds::HELD]);
                        
                        
                        $tmpContent = $ad[Classifieds::CONTENT];
                        //echo "\nProcessing\n",$ad[Classifieds::CONTENT],"\n";
                        
                        
                        $telNumbers = [];
                        $this->processTextNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $telNumbers);
                        
                        $ad[Classifieds::CONTENT] = strip_tags($ad[Classifieds::CONTENT]);
                               
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
                            }
                        }
                        $ad[Classifieds::EMAILS] = $emails;
                        
                        
                        if(count($telNumbers)==0){
                            //echo "\nNo Number Match\n",
                            //        $ad[Classifieds::CONTENT],
                            //        "\n-----------------------------------------------------------------\n\n";
                        }else{
                            //echo "\n";
                            //var_dump($telNumbers);
                            //        echo "Number Match\n",$ad[Classifieds::CONTENT],
                            //        "\n-----------------------------------------------------------------\n\n";
                        }
                        $ad[Classifieds::TELEPHONES] = $telNumbers;
                        
                        
                        $this->result['d'][] = [
                            $ad[Classifieds::ID], 
                            $ad[Classifieds::PUBLICATION_ID],
                            $ad[Classifieds::COUNTRY_ID],
                            $ad[Classifieds::CITY_ID],
                            $ad[Classifieds::PURPOSE_ID],
                            $ad[Classifieds::ROOT_ID],
                            $ad[Classifieds::CONTENT],
                            $ad[Classifieds::RTL],
                            $ad[Classifieds::DATE_ADDED],
                            $ad[Classifieds::SECTION_ID],
                            $ad[Classifieds::COUNTRY_CODE],
                            $ad[Classifieds::UNIXTIME],
                            $ad[Classifieds::EXPIRY_DATE],
                            $ad[Classifieds::URI_FORMAT],
                            $ad[Classifieds::LAST_UPDATE],
                            $ad[Classifieds::LATITUDE],
                            $ad[Classifieds::LONGITUDE],
                            $ad[Classifieds::ALT_CONTENT],
                            $ad[Classifieds::USER_ID],
                            $ad[Classifieds::PICTURES],
                            $ad[Classifieds::VIDEO],
                            isset($ad[Classifieds::EXTENTED_AR]) ? $ad[Classifieds::EXTENTED_AR] : "",
                            isset($ad[Classifieds::EXTENTED_EN]) ? $ad[Classifieds::EXTENTED_EN] : "",
                            isset($ad[Classifieds::LOCALITY_ID]) ? $ad[Classifieds::LOCALITY_ID] : 0,
                            isset($ad[Classifieds::LOCALITIES_AR]) ? $ad[Classifieds::LOCALITIES_AR] : "",
                            isset($ad[Classifieds::LOCALITIES_EN]) ? $ad[Classifieds::LOCALITIES_EN] : "",
                            $ad[Classifieds::USER_LEVEL],
                            $ad[Classifieds::LOCATION],
                            $ad[Classifieds::PICTURES_DIM],
                            $ad[Classifieds::TELEPHONES], 
                            $ad[Classifieds::EMAILS],
			    0];
                        //var_dump($ad);
                        //var_dump(array_values($ad));
                    }
                }
                //$this->result['d']['results'] = $this->db->getCache()->getMulti(array_keys($this->result['d']['matches']));
            }

            //var_dump(array_keys($this->result['d']['matches']));
        }
        $this->db->close();
    }

    
    function sphinxTotalsQL() {
        $this->sphinxTotals();
    }


    function sphinxTotals() {
        $kmemPrefix = 'w1_';
        if ($this->config['server_id']>1)
            $kmemPrefix = $this->config['cache_prefix'];
        $this->db->getCache()->setOption(Memcached::OPT_PREFIX_KEY, $kmemPrefix);
        
        $apiMemVersion = $this->db->cacheGet('api-mem-version');
        
        if (!is_numeric($apiMemVersion)) {
            $this->db->cacheSet('api-mem-version', 0, 0);
            $apiMemVersion=0;
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
        
        $cached = $this->db->cacheGet($MCKey);
        //$cached=false;
        
        if (!$cached) {
            //$conn = new PDO( 'mysql:host=127.0.0.1;port=9306;', '', '' );
            $conn = new PDO( 'mysql:unix_socket=/var/run/mourjanQL', '', '' );
            $filters = array();
            $group = '';
            if ($this->countryId) {
                $filters[] = "country={$this->countryId}";
                $group='root_id';
            }
        
            if ($this->cityId) {
                $filters[] = "city={$this->cityId}";
                $group='root_id';
            }

            if ($rootId) {
                $filters[] = "root_id={$rootId}";
                $group='section_id';
            }

            if ($sectionId) {
                $filters[] = "section_id={$sectionId}";
                $group = "purpose_id";
            }

            if ($tagId) {
                $filters[] = "section_tag_id={$tagId}";
                $group = "purpose_id";
            }

            if ($localityId) {
                $filters[] = "locality_id={$localityId}";
                $group = "purpose_id";
            }

            if ($purposeId) {
                $filters['purpose_id'] = $purposeId;
            }
            
            if ($sectionId==0)
                $q = "select groupby() AS attr_id, count(*) AS cnt, group_concat(purpose_id) as p_list from {$this->config['search_index']} where hold=0 and canonical_id=0 and ";
            else 
                $q = "select groupby() AS attr_id, count(*) AS cnt from {$this->config['search_index']} where hold=0 and canonical_id=0 and ";
            $q.= implode(" and ", $filters);
            $q.=" group by {$group} limit 0,1000";


            $stmt = $conn->prepare($q);
           
            //$this->result['query']=$q;
            if ($stmt->execute()) {
                $lkeys = [];
                $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($docs as $doc) {
                    if (isset($doc['p_list'])) {
                        $exp = explode(",", $doc['p_list']);
                        $unq=[];
                        foreach ($exp as $p) {
                            $p=$p+0;
                            if (!array_key_exists($p, $unq)) $unq[$p]=$p;
                        }
                    }
                    $attrId=$doc['attr_id']+0;
                    
                    if ($rootId==2 && $sectionId==0) {     
                        $this->result['d'][]=[$attrId, $doc['cnt']+0, array_keys($unq), $this->sphinxTags($attrId, $conn)]; 
                    } elseif ($rootId==1 && $sectionId==0 && $this->countryId>0) {
                        $this->result['d'][]=[$attrId, $doc['cnt']+0, array_keys($unq), $this->sphinxLocalities($attrId, $conn)]; 
                    } else {
                        if (isset($unq))
                            $this->result['d'][]=[$attrId, $doc['cnt']+0, array_keys($unq)];  
                        else
                            $this->result['d'][]=[$attrId, $doc['cnt']+0]; 
                    }
                }
                $this->db->cacheSet($MCKey, $this->result['d'], $this->config['ttl_short']);
            } else {
                //echo $conn->errorCode();
            }
            //$conn->Close();
           
        } else {
            $this->result['d']=$cached;
        }
        //$this->result['11']=$geo;
        
    }
    
    /*
    private function getChilds(&$geo, $obj, &$keys) {
        foreach ($obj[3] as $key => $value) {
            $keys[$value[1]]=$obj[1];
            if (count($value[3])>0) {
                $geo[1][$value[1]]=[$value[2], []];
                
            } else {
                $geo[1][$value[1]]=[$value[2]];
            }
            $this->getChilds($geo[1][$value[1]], $value, $keys);
        }
        
    }
        */
    
    private function sphinxTags($sectionId, $sphinx) {
        $ret=[];
        $q = "select groupby() AS attr_id, count(*) AS cnt, group_concat(purpose_id) as p_list from {$this->config['search_index']} where hold=0 and canonical_id=0 and section_id={$sectionId} ";
        if ($this->countryId) $q.="and country={$this->countryId} ";
        if ($this->cityId) $q.="and city={$this->cityId} ";
        $q.=" group by section_tag_id limit 0,1000";
        //$ret[]=$q;
        $stmt = $sphinx->prepare($q);
        
        if ($stmt->execute()) {
            $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($docs as $doc) {
                $exp = explode(",", $doc['p_list']);
                $unq=[];
                foreach ($exp as $p) {
                    $p=$p+0;
                    if (!array_key_exists($p, $unq))
                        $unq[$p]=$p;
                }                               
                $ret[]=[$doc['attr_id']+0, $doc['cnt']+0, array_keys($unq)];  
            }            
        }
        return $ret;
    }
    
    /*
    private function createLocalitiesTree(&$list, $parent){
        $tree = array();
        foreach ($parent as $k=>$l){
            if(isset($list[$l['id']])){
                $l['children'] = $this->createLocalitiesTree($list, $list[$l['id']]);
            }
            $tree[] = $l;           
        }               
        return $tree;
    }

    private function convertLocalitiesTree(&$list) { 
        $tree=array();
        
        foreach ($list as $value) {
            $l=[$value['id'], $value['count'], $value['purposes'], []];
            if (isset($value['children'])) {
                $l[3]=  $this->convertLocalitiesTree($value['children']);
            }
            $tree[] = $l;
        }        
        return $tree;        
    }
     * 
     */
    
   
    private function sphinxLocalities($sectionId, $sphinx) {       
        $arr=[];
        $q = "select groupby() AS attr_id, count(*) AS cnt, group_concat(purpose_id) as p_list from {$this->config['search_index']} where hold=0 and canonical_id=0 and section_id={$sectionId} ";
        if ($this->countryId) $q.="and country={$this->countryId} ";
        if ($this->cityId) $q.="and city={$this->cityId} ";
        $q.=" group by locality_id limit 0,1000";

        $stmt = $sphinx->prepare($q);
        
        if ($stmt->execute()) {
            $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($docs as $doc) {
                $exp = explode(",", $doc['p_list']);
                $unq=[];
                foreach ($exp as $p) {
                    $p=$p+0;
                    if (!array_key_exists($p, $unq)) $unq[$p]=$p;
                }                               
                $arr[]=[$doc['attr_id']+0, $doc['cnt']+0, array_keys($unq)];  
            }            
        }
        return $arr;
    }
    
    
    function userStatus(&$status, &$name=null) {
        $name=null;
        if ($this->uid>0 && !empty($this->uuid)) {
            $status = 0;
            $q = $this->db->queryResultArray(
                    "select d.uid, u.opts, u.full_name, IIF(m.STATUS IS NULL, 10, m.STATUS) STATUS, "
                    . "IIF(m.SECRET is null, '', m.SECRET) secret, IIF(m.MOBILE is null, 0, m.MOBILE) mobile, u.lvl "
                    . "from web_users_device d "
                    . "left join web_users_mobile m on m.uid=d.uid "
                    . "left join web_users u on u.id = d.uid "
                    . "where d.uuid=?", [$this->uuid]);
            //var_dump($q);
            if (!empty($q)) {
                $opts = json_decode($q[0]['OPTS']);
                //$opts->dump = $q;
                $opts->user_status = $q[0]['STATUS']+0;
                $opts->secret = $q[0]['SECRET'];
                $opts->phone_number = $q[0]['MOBILE']+0;
                if ($this->uid==$q[0]['UID']) {
                    if ($q[0]['LVL']!=5) {
                        $status=1;
                        $name=$q[0]['FULL_NAME'];
                    } else {
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
        } else {
            $status=-9;
            return null;
        }
    }
    
    
    function editFavorites() 
    {
        $this->userStatus($status);
        if ($status==1) {
            $adid = filter_input(INPUT_GET, 'adid', FILTER_VALIDATE_INT)+0;
            $state = filter_input(INPUT_GET, 'del', FILTER_VALIDATE_INT)+0;
            if ($adid) 
            {
                $q="update or insert into web_users_favs (web_user_id, ad_id, deleted) values (?, ?, ?) matching (web_user_id, ad_id) returning id";
                $rs = $this->db->get($q, [$this->uid, $adid, $state], TRUE);
                
                if ($rs && is_array($rs) && count($rs)==1) {
                    $succeed=false;
                    $sphinx = $this->getSphinx();
                    
                    $attributes = array('starred');
                    $q="select web_user_id from web_users_favs where deleted=0 and ad_id={$adid}";
                    $st = $this->db->getInstance()->query($q);
                    $attrval = array();
                    if ($st) {
                        while ($users=$st->fetch(PDO::FETCH_NUM)) {
                            $attrval[]=(int)$users[0];
                        }
                        $values=  array($adid=>array($attrval));
                        if ($sphinx->UpdateAttributes($this->config['search_index'], $attributes, $values, true)){
                            $succeed=true;
                        }
                        //var_dump("last message" . $this->site->sphinx->getLastError());
                    }
                    if (!$succeed) {
                        $this->result['e'] = 'Could not add this advert to our search engine';
                    }
                    
                    $this->result['d']['id']=$rs[0]['ID']+0;
                    
                }
                else 
                {
                    $this->result['d']=0;
                    $this->result['e']='Unable to add this advert to your favorite list';
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
            
            if ($delete!=1) {
                $rs = $this->db->queryResultArray(
                        "update or insert into SUBSCRIPTION "
                        . "(WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, TITLE, ADDED, EMAIL) "
                        . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, 0) "
                        . "matching (WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM) "
                        . "returning id", [$this->uid, $countryId, $cityId, $sectionId, $section_tag_id, $locality_id, $purpose_id, $terms, ''], TRUE);
                
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
            $this->db->queryResultArray("update subscription set badge_count=0, last_visit=current_timestamp where id=?", [$wId], TRUE);
        }
    }
    
    
    function register() {
        session_start();
        $current_name="";
        
        $opts = $this->userStatus($status, $current_name);
        
        $device_name  = filter_input(INPUT_GET, 'dn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_model = filter_input(INPUT_GET, 'dm', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_sysname  = filter_input(INPUT_GET, 'sn', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_sysversion = filter_input(INPUT_GET, 'sv', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $carrier_country = filter_input(INPUT_GET, 'cc', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $device_appversion = filter_input(INPUT_GET, 'av', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $is_ping = filter_input(INPUT_GET, 'ping', FILTER_VALIDATE_INT)+0;
        $app_prefs = html_entity_decode(filter_input(INPUT_GET, 'prefs', FILTER_SANITIZE_STRING, ['options'=>['default'=>'{}']]));

        $this->result['status']=9;

        if (empty($carrier_country)) {
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $geo = @geoip_record_by_name($ip);
            $carrier_country = empty($geo) ? 'XX' : strtoupper(trim($geo['country_code']));
        }

        if ($status==1) {
            /* opts->user_status 
             * 9: retired
             * 10: does not have web_users_mobile record (not activated mobile user)
             */
            if ($is_ping==0) {
                $this->db->queryResultArray("update or insert into WEB_USERS_DEVICE "
                    . "(uuid, uid, device_model, device_name, device_sysname, "
                    . "device_sysversion, last_visit, CARRIER_COUNTRY, APP_VERSION, APP_PREFS) "
                    . "values (?, ?, ?, ?, ?, ?, current_timestamp, ?, ?, ?)",
                    [$this->uuid, $this->uid, $device_model, $device_name, $device_sysname,
                    $device_sysversion, $carrier_country, $device_appversion, $app_prefs], TRUE);
            }

            $this->result['d']['status']=$opts->user_status;
            $this->result['d']['pwset']=!empty($opts->secret);
            
            $uname = filter_input(INPUT_GET, 'uname', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

            if ($uname && $uname!=$current_name) {
                $this->db->queryResultArray("update web_users set full_name=?, display_name=? where id=?", [$uname, $uname, $this->uid]);
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
                
                //error_log('App: ' . PHP_EOL . json_encode($user->info), 0);
                //error_log('App: ' . PHP_EOL . json_encode($_SESSION), 0);
            }
            
        } elseif ($status==-9 && !empty ($this->uuid)) {
            $q = $this->db->queryResultArray(
                    "insert into web_users (identifier, email, provider, full_name, profile_url, opts, user_name, user_email) "
                    . "values (?, '', 'mourjan-iphone', '', 'http://www.mourjan.com/', '{}', '', '')  returning id", [$this->uuid], TRUE);
            if ($q && is_array($q) && count($q)==1) {
                
                $this->result['d']['uid']=$q[0]['ID']+0;
                
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
        if ($status==1) {
            $token=filter_input(INPUT_GET, 'tk', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            $this->db->queryResultArray("update WEB_USERS_DEVICE set PUSH_ID=? where uuid=? and PUSH_ID<>?", [$token, $this->uuid, $token], TRUE);            
        }
        $this->db->close();
    }
    
    
    function setNotification() {
        $this->userStatus($status);
        $this->result['status']=$status;
        if ($status==1) {
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
        //var_dump($opts);
  
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
                        
                        if ($this->db->queryResultArray("update web_users_device set uid=? where uuid=?", [$userId, $this->uuid])) {

                            $ok = $this->db->queryResultArray(
                                    "update web_users_favs a set a.web_user_id=? "
                                    . "where a.web_user_id=? "
                                    . "and not exists (select 1 from web_users_favs b "
                                    . "where b.web_user_id=? and b.ad_id=a.ad_id)", [$userId, $this->uid, $userId]);
                            if ($ok) {
                                $ok = $this->db->queryResultArray(
                                    "update subscription a set a.web_user_id=? "
                                    . "where a.web_user_id = ? and "
                                    . "not exists (select 1 from subscription b "
                                    . "where b.web_user_id=? "
                                    . "and b.country_id=a.country_id and b.city_id=a.city_id and b.section_id=a.section_id "
                                    . "and b.purpose_id=a.purpose_id and b.section_tag_id=a.section_tag_id "
                                    . "and b.locality_id=a.locality_id and b.purpose_id=a.purpose_id and b.query_term=a.query_term)", 
                                    [$userId, $this->uid, $userId]);
                                
                                if ($ok) {
                                    $ok = $this->db->queryResultArray("delete from web_users_favs where web_user_id=?", [$this->uid]);
                                    if ($ok) {
                                        $ok = $this->db->queryResultArray("delete from subscription where web_user_id=?", [$this->uid]);
                                        if ($ok) {
                                            $ok = $this->db->queryResultArray("delete from web_users where id=?", [$this->uid]);
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
    
    
    function activate() {
        $opts = $this->userStatus($status);
        if ($status==1) {
            if ($opts->user_status==9) {
                $this->result['e'] = 'Your account is retired';
                return;
            }
            $mobile_no = filter_input(INPUT_GET, 'tel', FILTER_VALIDATE_INT)+0;
            
            if ($mobile_no<999999) {
                $this->result['e'] = 'Invalid mobile registration request';
                return;
            }
            
            include_once $this->config['dir'].'/core/lib/nexmo/NexmoMessage.php';
            
            $rs = $this->db->queryResultArray(
                    "select m.ID, m.UID, m.MOBILE, m.STATUS, m.DELIVERED, m.CODE, SMS_COUNT, "
                    . "datediff(SECOND from m.REQUEST_TIMESTAMP to CURRENT_TIMESTAMP) req_age "
                    . "from WEB_USERS_MOBILE m where m.mobile=?", [$mobile_no]);
            
            //$this->result['d']['rs']=$rs;
            
            if (is_array($rs)) {
                if (count($rs)==0) {
                    //echo $mobile_no;
                    $pin = mt_rand(1000, 9999);
                    $iq = $this->db->queryResultArray(
                            "INSERT INTO WEB_USERS_MOBILE (UID, MOBILE, CODE, STATUS, DELIVERED, SMS_COUNT)
                            VALUES (?, ?, ?, 5, 0, 0) RETURNING ID", [$this->uid, $mobile_no, $pin], TRUE);
                    if ($iq[0]['ID']>0) {
                        $sms = new NexmoMessage('8984ddf8', 'ee02b1df');
                        $response = $sms->sendText( "+{$mobile_no}", 'Mourjan',
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
                        if ($rs[0]['STATUS']==5) {
                            $sms = new NexmoMessage('8984ddf8', 'ee02b1df');
                            $pin = $rs[0]['CODE'];
                            $response = $sms->sendText( "+{$mobile_no}", 'Mourjan',
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
                        $this->result['e'] = 'Activation code is already sent, but not delivered yet.\nPlease wait a few seconds';
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
        echo json_encode($this->result, JSON_UNESCAPED_UNICODE );
        flush();
    }
    
 
    private function detectEmail($ad){
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/i', $ad, $matches);
        return $matches;
    }
    
     
    private function processTextNumbers(&$text, $pubId=0, $countryCode=0, &$matches=array()){
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        //preg_match('/( للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text,$content);
        if(!($content && count($content))){
            /*$tmpTxt=preg_replace('/\<.*?>/', '', $text);
            preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt,$content);*/
            return $text;
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
                /*if(preg_match('/\<span class/',$text)){
                    if(in_array($pubId, [9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,34,36,37,38,39,40,41,46,47,48,52,54,55])){ // Waseet
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
                        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                        $res = '';
                        if(count($mobile) || count($phone)){
                            if(count($mobile)){
                                $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                $i=0;
                                foreach($mobile as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                            if(count($phone)){
                                if($res)$res.='- ';
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($phone as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                        }elseif(count($undefined)){
                            $res.=($isArabic ? 'هاتف':'Phone').': ';
                            $i=0;
                            foreach($undefined as $mob){
                                if($i)$res.=($isArabic ? 'او ':'or ');
                                $res.='<span class="vn">'.$mob[1].'</span> ';
                                $matches[]=$mob[1];
                                $i++;
                            }
                        }
                        $divider=null;
                        preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        $pos=0;
                        if($divider && count($divider)){
                            $pos = strpos($text, $divider[1]);
                            if(!$pos){
                                $divider=null;
                                preg_match('/(<span)/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if(!$pos){
                            $srh='';
                            foreach($nums as $num){
                                $srh .= $num[0].'|';
                            }
                            if($srh){
                                $srh.=substr($srh,0,-1);
                                $srh=  preg_replace('/\+/','\\+' , $srh);
                                $divider=null;
                                preg_match('/(<span class="pn">'.$srh.')/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        ////if($pos)
                            ////$text = substr($text,0,$pos);
                        ////if($res)
                            ////$text.=' / '.$res;
                    }else{
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                ////$text = preg_replace('/'.$num[0].'/', $num[1], $text);
                                $matches[]=$num[1];
                            }else{
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                ////$text = preg_replace('/\<span class="pn(?:[a-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$num[1].'</span>', $text);
                            }
                        }
                    }
                }else{
                    if(in_array($pubId, [9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,34,36,37,38,39,40,41,46,47,48,52,54,55])){ // Waseet
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
                        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                        $res = '';
                        if(count($mobile) || count($phone)){
                            if(count($mobile)){
                                $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                $i=0;
                                foreach($mobile as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                            if(count($phone)){
                                if($res)$res.='- ';
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($phone as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                        }elseif(count($undefined)){
                            $res.=($isArabic ? 'هاتف':'Phone').': ';
                            $i=0;
                            foreach($undefined as $mob){
                                if($i)$res.=($isArabic ? 'او ':'or ');
                                $res.='<span class="vn">'.$mob[1].'</span> ';
                                $matches[]=$mob[1];
                                $i++;
                            }
                        }
                        $divider=null;
                        preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        $pos=0;
                        if($divider && count($divider)){
                            $pos = strpos($text, $divider[1]);
                            if(!$pos){
                                $divider=null;
                                preg_match('/(<span)/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if(!$pos){
                            $srh='';
                            foreach($nums as $num){
                                $srh .= $num[0].'|';
                            }
                            if($srh){
                                $srh.=substr($srh,0,-1);
                                $srh=  preg_replace('/\+/','\\+' , $srh);
                                $divider=null;
                                preg_match('/('.$srh.')/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if($pos)
                            ////$text = substr($text,0,$pos);
                        if($res){
                            ////$text.=' / '.$res;
                        }
                    }else{
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                ////$text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$num[1].'</span>', $text);
                                $matches[]=$num[1];
                            }else{
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                ////$text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$num[1].'</span>', $text);
                            }
                        }
                    }
                }*/
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
                if (count($rs)==0) {
                    //echo $mobile_no;
                    $pin = mt_rand(1000, 9999);
                    $iq = $this->db->queryResultArray(
                            "UPDATE WEB_USERS_MOBILE 
                             SET CODE=?, MOBILE=?, STATUS=5, DELIVERED=0, SMS_COUNT=0
                             WHERE UID=? AND MOBILE=?
                             RETURNING ID", [$pin, $phone_number, $this->uid, $current_phone_number], TRUE);
                    if ($iq[0]['ID']>0) {
                        $sms = new NexmoMessage('8984ddf8', 'ee02b1df');
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
            
            $q = $this->db->queryResultArray("update WEB_USERS_MOBILE set status=9 where uid=? and mobile=? returning status", [$this->uid, $phone_number], TRUE);
            if ($q[0]['STATUS']==9) {
                $this->result['d']['status']='deleted';
            } else {
                $this->result['d']['status']='failed';
            }
        }
        else $this->result['e']='Invalid user request!';

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

}