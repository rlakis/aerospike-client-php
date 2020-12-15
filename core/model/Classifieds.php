<?php
namespace Core\Model;

\Config::instance()->incModelFile('Ad');

class Classifieds {

    const ID                    = 0;
    const HELD                  = 1;
    
    const PUBLICATION_ID        = 3;
    const COUNTRY_ID            = 4;
    const CITY_ID               = 5;
    const CATEGORY_ID           = 6;
    const PURPOSE_ID            = 7;
    const ROOT_ID               = 8;
    const CONTENT               = 9;
    const RTL                   = 10;
    const DATE_ADDED            = 11;
    const SECTION_ID            = 12;
    const COUNTRY_CODE          = 13;
    const UNIXTIME              = 14;
    const CANONICAL_ID          = 15;
    const EXPIRY_DATE           = 16;
    
    const URI_FORMAT            = 18;
    const SECTION_NAME_AR       = 19;
    const SECTION_NAME_EN       = 20;
    const LAST_UPDATE           = 21;
    const LATITUDE              = 22;
    const LONGITUDE             = 23;
    
    const ALT_CONTENT           = 25;
    const USER_ID               = 26;
    const PICTURES              = 27;
    
    const EXTENTED_AR           = 29;
    const EXTENTED_EN           = 30;

    const LOCALITY_ID           = 31;

    const LOCALITIES_AR         = 32;
    const LOCALITIES_EN         = 33;
    const USER_LEVEL            = 34;
    
    const LOCATION              = 35;
    
    const PICTURES_DIM          = 36;
    
    const TELEPHONES            = 37;
    const EMAILS                = 38;
    const FEATURED              = 39;
    const CONTACT_INFO          = 40;
    
    const FEATURE_ENDING_DATE   = 42;
    const BO_ENDING_DATE        = 43;
    const USER_RANK             = 44;
    
    const PUBLISHER_TYPE        = 45;
    const PRICE                 = 46;
    
    const RERA                  = 47;
    const IP_FRAUD_SCORE        = 48;
    const IP_COUNTRY_CODE       = 49;
    const AM_COUNTRY_CODE       = 50;
    const ATTRS                 = 80;
    const DONE                  = 99;

    private $stmt_get_ad = null;
    private $stmt_get_media = null;
    private $stmt_get_ext = null;
    private $stmt_get_loc = null;

    private $db;
    
    private string $formatNumbers='LB';
    private $mobileValidator=null;
    
    private bool $isDebugMode=false;

    function __construct($database){
        $this->db=$database;
    }

    
    function __destruct() {                
        unset($this->stmt_get_loc);
        unset($this->stmt_get_ext);
        unset($this->stmt_get_media);
        unset($this->stmt_get_ad);
    }
            
    
    function getAd(int $id, array $cache=[]) : Ad {
        $result=$cache[$id] ?? $this->db->getCache()->get($id);
        if ($result===false) { $result=$this->getById($id); }
        return new Ad($result===false?[]:$result);
    }
    
    
    function getById(int $id, bool $forceCache=false, array $cacheSet=[]) {
        if ($id<=0) { return FALSE; }
        
        if (!$this->isDebugMode && !$forceCache) {
            $ad = (\count($cacheSet)>0 && isset($cacheSet[$id])) ? $cacheSet[$id] : $this->db->getCache()->get($id);        
            if ($ad) {
                if ($this->isDebugMode || $ad[Classifieds::DONE]!=1) {
                    $this->normalizeContacts($ad);                
                    $this->db->getCache()->setEx($id, 180*86400, $ad);
                }
                
                if (!isset($ad[Classifieds::FEATURE_ENDING_DATE])) {                    
                    $ad[Classifieds::FEATURE_ENDING_DATE]=0;
                    $ad[Classifieds::BO_ENDING_DATE]=0;
                }
                
                return $ad;
            }
        }
        
        if ($this->stmt_get_ad && !$this->db->inTransaction()) {
            \error_log("Lost FB transaction ad id: {$id}");
        }
        
        \error_log(__FUNCTION__. " fetch ad {$id} from database".PHP_EOL);
        
        if (!$this->stmt_get_ad || !$this->db->inTransaction()) {
            $this->stmt_get_ad = $this->db->prepareQuery(
                "select ad.id, ad.hold, ad.country_id, ad.city_id, 
                    section.category_id, ad.purpose_id, section.root_id, ad.content, ad.rtl, 
                    ad.date_added, ad.section_id, trim(country.id_2) country_code, 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.DATE_ADDED) UNIXTIME, 
                    ad.canonical_id, ad.expiry_date, /*link.url flink,*/
                    '/'||lower(country.ID_2)||'/'||city.uri||'/'||section.uri||'/'||purpose.uri||'/%s%d/' uri,
                    section.name_ar, section.name_en, 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.LAST_UPDATE) LAST_UPDATE,
                    ad_user.latitude, ad_user.longitude,
                    ad_translated.content alter_content,
                    ad_user.web_user_id,                      
                    wu.user_rank, wu.lvl,
                    IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', featured.ended_date)) featured_date_ended, 
                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', bo.end_date)) bo_date_ended, 
                    ad.publisher_type, 
                    ad_user.content user_content
                from ad
                left join country on country.id=ad.country_id 
                left join city on city.id=ad.city_id
                left join section on section.id=ad.section_id
                left join purpose on purpose.id=ad.purpose_id
                /*left join link on link.ad_id=ad.id*/
                left join ad_user on ad_user.id=ad.id
                left join ad_translated on ad_translated.ad_id=ad.id 
                left join t_ad_bo bo on bo.ad_id=ad.id and bo.blocked+0=0 
                left join web_users wu on wu.id=ad_user.web_user_id 
                left join t_ad_featured featured on featured.ad_id=ad.id and current_timestamp between featured.added_date and featured.ended_date 
                where ad.id=?");

            unset($this->stmt_get_ext);
            $this->stmt_get_ext = $this->db->prepareQuery("SELECT r.SECTION_TAG_ID, t.LANG FROM AD_TAG r left join SECTION_TAG t on t.ID=r.SECTION_TAG_ID where r.AD_ID=?");

            unset($this->stmt_get_loc);
            $this->stmt_get_loc = $this->db->prepareQuery(
                "SELECT r.LOCALITY_ID, g.NAME, g.CITY_ID, g.PARENT_ID, g.LANG FROM AD_LOCALITY r
                left join GEO_TAG g on g.ID=r.LOCALITY_ID
                where r.AD_ID=?");
            
            unset($this->stmt_get_media);
            $this->stmt_get_media = $this->db->prepareQuery(
                "select AD_MEDIA.MEDIA_ID, MEDIA.FILENAME, MEDIA.WIDTH, MEDIA.HEIGHT from AD_MEDIA
                left join media on media.ID=AD_MEDIA.MEDIA_ID
                where AD_MEDIA.AD_ID=?");
                    
        }
        
        $ad=false;
        
        $this->stmt_get_ad->execute( [$id] );
        
        if (($row=$this->stmt_get_ad->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $ad=[];
            $ad[Classifieds::ID]=$row['ID'];
            $ad[Classifieds::HELD]=$row['HOLD'];
            $ad[Classifieds::PUBLICATION_ID]=1; // deprecated
            $ad[Classifieds::COUNTRY_ID]=$row['COUNTRY_ID'];
            $ad[Classifieds::CITY_ID]=$row['CITY_ID'];
            $ad[Classifieds::CATEGORY_ID]=$row['CATEGORY_ID']; // deprecated
            $ad[Classifieds::PURPOSE_ID]=$row['PURPOSE_ID'];
            $ad[Classifieds::ROOT_ID]=$row['ROOT_ID'];
            $ad[Classifieds::CONTENT]=$row['CONTENT'];
            $ad[Classifieds::RTL]=$row['RTL'];
            $ad[Classifieds::DATE_ADDED]=$row['DATE_ADDED'];
            $ad[Classifieds::SECTION_ID]=$row['SECTION_ID'];
            $ad[Classifieds::COUNTRY_CODE]=$row['COUNTRY_CODE'];
            
            $ad[Classifieds::UNIXTIME]=$row['UNIXTIME'];
            $ad[Classifieds::CANONICAL_ID]=$row['CANONICAL_ID'];
            $ad[Classifieds::EXPIRY_DATE]=$row['EXPIRY_DATE'];
            $ad[Classifieds::URI_FORMAT]=$row['URI'];
            $ad[Classifieds::SECTION_NAME_AR]=$row['NAME_AR'];
            $ad[Classifieds::SECTION_NAME_EN]=$row['NAME_EN'];
            $ad[Classifieds::LAST_UPDATE]=$row['LAST_UPDATE'];
            $ad[Classifieds::LATITUDE]=$row['LATITUDE']+0;
            $ad[Classifieds::LONGITUDE]=$row['LONGITUDE']+0;
                    
            $ad[Classifieds::ALT_CONTENT]=$row['ALTER_CONTENT'];
            $ad[Classifieds::USER_ID]=$row['WEB_USER_ID'];
            
            $ad[Classifieds::EXTENTED_AR]=0;
            $ad[Classifieds::EXTENTED_EN]=0;
            
            
            $ad[Classifieds::PICTURES]=NULL;
            $ad[Classifieds::PICTURES_DIM]=NULL;
            $ad[Classifieds::LOCALITIES_AR]=NULL;
            $ad[Classifieds::LOCALITIES_EN]=NULL;
            $ad[Classifieds::LOCATION]=NULL;
            
            $ad[Classifieds::USER_LEVEL]=$row['LVL'];                        
            $ad[Classifieds::USER_RANK]=$row['USER_RANK'];
            $ad[Classifieds::FEATURE_ENDING_DATE]=$row['FEATURED_DATE_ENDED'];
            $ad[Classifieds::BO_ENDING_DATE]=$row['BO_DATE_ENDED'];
            $ad[Classifieds::PUBLISHER_TYPE]=$row['PUBLISHER_TYPE'];
            $ad[Classifieds::DONE]=0;
            
            // parser
            $decoder=\json_decode($row['USER_CONTENT'], true);    
            
            $this->stmt_get_media->execute([$id]);
            while (($media=$this->stmt_get_media->fetch(\PDO::FETCH_ASSOC))!==false) {
                $ad[Classifieds::PICTURES][]=$media['FILENAME'];
                $ad[Classifieds::PICTURES_DIM][]=[$media['WIDTH'], $media['HEIGHT']];
            }
                        
            if(isset($decoder['cui'])) { $ad[Classifieds::CONTACT_INFO] = $decoder['cui']; }            
            //if(isset($decoder['cut'])) { $ad[Classifieds::CONTACT_TIME] = $decoder['cut']; }
            if (isset($decoder['loc']) && $decoder['loc']) { $ad[Classifieds::LOCATION] = $decoder['loc']; }       
            //if (isset($decoder['video']) && is_array($decoder['video']) && count($decoder['video'])) {
            //    $ad[Classifieds::VIDEO] = $decoder['video'];
            //}
            
            //if (isset($decoder['userLvl']) && $decoder['userLvl']) {
            //    $ad[Classifieds::USER_LEVEL] = $decoder['userLvl'];
            //}
            
            if (isset($decoder['rera'])) { $ad[Classifieds::RERA] = $decoder['rera']; }
            
           
            $ad[Classifieds::PRICE]=(isset($decoder['attrs']['price']) && $decoder['attrs']['price']>0)?$decoder['attrs']['price']:0;
        
            if ($ad[Classifieds::ROOT_ID]==1) {
                $this->stmt_get_loc->execute([$id]);
                while (($locRow=$this->stmt_get_loc->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $ad[$locRow['LANG']==='ar'?Classifieds::LOCALITIES_AR:Classifieds::LOCALITIES_EN][$locRow['LOCALITY_ID']+0] =
                            [$locRow['NAME'], $locRow['CITY_ID'], $locRow['PARENT_ID']];
                }
            }
            elseif ($ad[Classifieds::ROOT_ID]==2) {
                $this->stmt_get_ext->execute([$id]);
                while (($extRow=$this->stmt_get_ext->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $ad[$extRow['LANG']==='ar'?Classifieds::EXTENTED_AR:Classifieds::EXTENTED_EN]=$extRow['SECTION_TAG_ID']+0;
                }
            }
                        
            $this->normalizeContacts($ad);

            $res=$this->db->getCache()->setEx($id, 180*86400, $ad);
            if ($res===false) { \error_log("Classifieds->getById: Cound not set ad {$id} to redis cache"); }
        }
        return $ad;
    }

    
    public function normalizeContacts(&$ad) {
        if ($this->mobileValidator==NULL) {
            $this->mobileValidator = \libphonenumber\PhoneNumberUtil::getInstance();
        }
        $telNumbers = [];
        $tmpContent = $ad[Classifieds::CONTENT];
        $this->processTextNumbers($tmpContent, $ad[Classifieds::COUNTRY_CODE], $telNumbers);

        if($ad[Classifieds::ALT_CONTENT]!="") {
            $tmpTel = [];
            $tmpContent = $ad[Classifieds::ALT_CONTENT];
            $this->processTextNumbers($tmpContent, $ad[Classifieds::COUNTRY_CODE], $telNumbers);
        }
        $tmpContent = $ad[Classifieds::CONTENT];

        $emails = $this->detectEmail($tmpContent);
        if($emails && count($emails)) {
            $emails = $emails[0];
        }
        
        $ad[Classifieds::EMAILS] = $emails;
        if (empty($telNumbers)) { $telNumbers = [[],[],[]]; }
        $ad[Classifieds::TELEPHONES] = $telNumbers;
        $ad[Classifieds::DONE] = 1;
    }
    
    
    private function detectEmail($ad){
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/ui', $ad, $matches);
        return $matches;
    }
    

    private function processTextNumbers(&$text, $countryCode=0, &$matches=array()) {                
        $phone = '/((?:\+|\s)(?:[0-9\\/\-]{7,16})|(?:\d{4}\s\d{4}))/ui';
        $content=null;
        $str=null;
        $subPattern='(?:(?:(?:[ .,;:\-\/،])(?:mobile|viber|whatsapp|phone|fax|telefax|للتواصل|جوال|موبايل|هاتف|فاكس|تلفاكس|واتساب|للاستفسار|للأستفسار|للإستفسار|فايبر|الاتصال|للتواصل|للمفاهمة|للاتصال|الاتصال على|اتصال|(?:tel|call|ت|ه|ج))(?:(?:\s|):|\+|\+|\/|) ))';
        
        $hasMatch=preg_match('/\x{200B}.*/u',$text,$content);
        if($hasMatch) {            
            $str=$content[0];            
        }
        else {
            $hasMatch=preg_match('/'.$subPattern.'((?!.*'.$subPattern.'))/ui', $text, $content);        
            if (!$hasMatch) {
                $hasMatch=preg_match_all($phone, $text, $content);
                if (!$hasMatch) {
                    return $text;
                }
                else {
                    if(is_array($content) && isset($content[0][0])){
                        $pattern= $content[0][0];
                        $pattern = trim(preg_replace('/[\-\/\\\]/', '', $pattern));
                        $pattern =  preg_replace('/\+/','\\+' , $pattern);
                        preg_match('/('.$pattern.'.*$)/',$text,$content);
                        if(!($content && is_array($content))) {
                            return $text;
                        }else{
                            $str=$content[1];
                        }
                    }else{
                        return $text;
                    }
                }
            }else{
                $str=$content[0];
            }
        }
        
        if ($str) {
            $strpos = strpos($text, $str);
            
            
            $str = trim(substr($text, $strpos));
            
            $text = trim(substr($text,0, $strpos));
            $text = trim(preg_replace('/[\-\/\\\]$/', '', $text));
            
            if ($str) {
                if ($this->formatNumbers) {
                    $nums=array();
                    $numInst=array();
                    $numbers = null;
                    preg_match_all($phone, $str, $numbers);
                    if ($numbers && count($numbers[1])) {
                
                        $mobile=array();
                        $phone=array();
                        $undefined = array();
                        
                        foreach($numbers[1] as $match) {
                            $number = trim($match);
                            /*quick fix for bahrain number flipped example 1234 4567*/
                            $leading=null;
                            if(preg_match('/(\d{4})\s(\d{4})/u',$number,$leading)){
                                if($leading && is_array($leading) && count($leading)==3){
                                    $number=$leading[2].$leading[1];
                                }
                            }
                            /*end of fix*/
                            $number = preg_replace('/[\s,\-\/]/','',$number);
                            /*quick fix for arabic number flipped example 123456/03*/
                            $leading=null;
                            preg_match('/[\\%\/\-](?:[0-9]){1,2}$/',$number,$leading);
                            if($leading && is_array($leading)){
                                $number=substr($number,0,-1*strlen($leading[0]));
                                $number = substr($leading[0],1).$number;
                            }
                            /*end of fix*/
                            try {
                                
                                $numInst[] = $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                                
                                if ($num && $this->mobileValidator->isValidNumber($num)) {
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if ($rCode==$this->formatNumbers) {
                                        $num=preg_replace('/ /','',$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers ));
                                    } else {
                                        $num=preg_replace('/ /','',$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers));
                                    }
                                    $nums[]=array($number, $num);
                                } else {
                                    $num = $number;
                                    $hasCCode = preg_match('/^\+/', $number);
                                    switch ($countryCode) {
                                        case 'BH':
                                            if (strlen($number)==16) {
                                                $num=substr($number, 0, 8);
                                            }
                                            break;
                                        case 'SA':
                                            $num = $hasCCode ? substr($number, 4) : $number;
                                            break;
                                        
                                        case 'EG':
                                            $num = $hasCCode ? substr($number,3) : $number;
                                            break;
                                    }
                                    
                                    if ($num != $number) {
                                        $numInst[count($numInst)-1] = $num = $this->mobileValidator->parse($num, $countryCode);
                                        if ($num && $this->mobileValidator->isValidNumber($num)) {
                                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                            if ($rCode==$this->formatNumbers) {
                                                $num=preg_replace('/ /','',$this->mobileValidator->formatInOriginalFormat($num, $this->formatNumbers));
                                            } else {
                                                $num=preg_replace('/ /','',$this->mobileValidator->formatOutOfCountryCallingNumber($num, $this->formatNumbers));
                                            }
                                            $nums[]=array($number, $num);
                                        } 
                                        else {
                                            $nums[]=array($number, $number);
                                        }
                                    } else {
                                        $nums[]=array($number, $number);
                                    }
                                }                                
                            } 
                            catch(Exception $ex) {
                                $undefined[]=array($number, $number);
                            }
                        }
                        $i=0;

                        foreach ($nums as $num) {
                            $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                            if ($type==1 || $type==2)
                                $mobile[]=$num;
                            elseif ($type==0 || $type==2)
                                $phone[]=$num;
                            else $undefined[]=$num;
                        }
                        
                        $matches=[$mobile, $phone, $undefined]; 
                        
                    }
                }
            }
        }
        //var_dump($matches);
        return $text;
    }
    
    
    static function detectYear($text) {
        $year=0;
        $matches=null;
        preg_match_all('/\s(?:mod|model|modl|year|م|مودل|موديل|مودال|مدل|مديل|مدال)(?:\s|)([0-9]{2,4})\s/u', $text, $matches);
        if(isset($matches[1]) && count($matches[1])){
            foreach ($matches[1] as $yr){
                if($yr && strlen($yr)==3) $yr=0;
                if($yr && strlen($yr)==2) $yr='19'.$yr;
                $yr=(int)$yr;
                if($yr>=1980 && $yr<=2013){
                    $year=$yr;
                    break;
                }
            }
        }
        if(!$year){
            preg_match_all('/\s([0-9]{2,4})(?:\s|)(?:mod|model|modl|year|م|مودل|موديل|مودال|مدل|مديل|مدال)/u', $text, $matches);
            if(isset($matches[1]) && count($matches[1])){
                foreach ($matches[1] as $yr){
                    if($yr && strlen($yr)==3) $yr=0;
                    if($yr && strlen($yr)==2) $yr='19'.$yr;
                    $yr=(int)$yr;
                    if($yr>=1980 && $yr<=2013){
                        $year=$yr;
                        break;
                    }
                }
            }
        }
        if(!$year){
            preg_match_all('/^([0-9]{2,4})\s/u', $text, $matches);
            if(isset($matches[1]) && count($matches[1])){
                foreach ($matches[1] as $yr){
                    if($yr && strlen($yr)==3) $yr=0;
                    if($yr && strlen($yr)==2) $yr='19'.$yr;
                    $yr=(int)$yr;
                    if($yr>=1980 && $yr<=2013){
                        $year=$yr;
                        break;
                    }
                }
            }
        }
        if(!$year){
            preg_match_all('/\s([0-9]{4})\s/u', $text, $matches);
            if(isset($matches[1]) && count($matches[1])){
                foreach ($matches[1] as $yr){
                    $yr=(int)$yr;
                    if($yr>=1980 && $yr<=2013){
                        $year=$yr;
                        break;
                    }
                }
            }
        }
        return $year;
    }
    
}
