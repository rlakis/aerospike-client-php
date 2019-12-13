<?php
namespace Core\Model;

use JsonException;

class Ad {
    private $list;
    
    private $data;              // raw classified array
    private $text;              // ad text without contacts
    private $translation;       // ad text alter language without contacts
    private $profile;           // MCUser instance    
    private $numberValidator = null;
    private $dataset;    
    private $dateModified;
    private $superAdmin;
    
    
    function __construct(array $data=[]) {
        $this->data = $data;
        if (!isset($this->data[Classifieds::ID])) { $this->data[Classifieds::ID] = 0; }
        if (!isset($this->data[Classifieds::RTL])) { $this->data[Classifieds::RTL] = 0; }
        if (!isset($this->data[Classifieds::ROOT_ID])) { $this->data[Classifieds::ROOT_ID] = 0; }
        if (!isset($this->data[Classifieds::SECTION_ID])) { $this->data[Classifieds::SECTION_ID] = 0; }
        if (!isset($this->data[Classifieds::PURPOSE_ID])) { $this->data[Classifieds::PURPOSE_ID] = 0; }
        
        if (isset($this->data[Classifieds::CONTENT]) && !empty($this->data[Classifieds::CONTENT])) {
            $this->text = preg_split("/\x{200b}/u", $this->data[Classifieds::CONTENT])[0];
        }
        if (isset($this->data[Classifieds::ALT_CONTENT]) && !empty($this->data[Classifieds::ALT_CONTENT])) {
            $this->translation = preg_split("/\x{200b}/u", $this->data[Classifieds::ALT_CONTENT])[0];
        }
    }
    
    
    public static function create() : Ad {
        return new Ad();
    }
    
    
    public function setParent(AdList $list) : Ad {
        $this->list = $list;
        return $this;
    }
    
    
    
    public function data() : array {
        return $this->data;
    }
    
    
    public function id() : int {
        return $this->data[Classifieds::ID] ?? 0;
    }
    
    
    public function setId(int $value) : Ad {
        $this->data[Classifieds::ID] = ($value>0)?$value:0;
        return $this;        
    }
    
    
    public function rootId() : int {
        return $this->data[Classifieds::ROOT_ID];
    }
    
    
    public function sectionId() : int {
        return $this->data[Classifieds::SECTION_ID]??0;
    }
    
    
    public function setSectionId(int $value) : Ad {
        if ($this->dataset !== null) {
            $this->dataset()->setSectionID($value);
            $this->data[Classifieds::ROOT_ID]=$this->dataset()->getRootId();
            $this->data[Classifieds::SECTION_ID]=$this->dataset()->getSectionID();
        }
        else {
            if (isset(Router::instance()->sections[$value])) {
                $this->data[Classifieds::SECTION_ID]=$value;
                $this->data[Classifieds::ROOT_ID]=\intval(Router::instance()->sections[$value][4]);
            }
            else {
                $this->data[Classifieds::SECTION_ID]=0;
                $this->data[Classifieds::ROOT_ID]=0;
            }
        }
        return $this;
    }
    
    
    public function purposeId() : int {
        return $this->data[Classifieds::PURPOSE_ID]??0;
    }
    
    
    public function setPurposeId(int $value) : Ad {
        $this->data[Classifieds::PURPOSE_ID]=$value;
        if ($this->dataset !== null) {
            $this->dataset()->setPurposeID($value);
        }
        return $this;
    }
    
    
    public function setNativeText(string $value) : Ad {
        $this->data[Classifieds::CONTENT]=\trim($value);
        $success = \preg_match_all('/\p{Arabic}/u', $this->data[Classifieds::CONTENT]);
        if ($success/\mb_strlen($this->data[Classifieds::CONTENT])>0.4) {
            $this->setRTL();
        }
        else {
            $this->setLTR();
        }
        return $this;
    }
    
    
    public function setForeignText(string $value) : Ad {
        if (empty($this->data[Classifieds::CONTENT])) {
            return $this->setNativeText($value);
        }
        $this->data[Classifieds::ALT_CONTENT]=\trim($value);
        //$success = \preg_match_all('/\p{Arabic}/u', $this->data[Classifieds::ALT_CONTENT]);
        return $this;
    }
    
    
    public function setDataSet(Content $object) : Ad {
        $this->dataset = $object;
        $this->dataset->setAd($this);
        if ($this->dataset->getID()>0 && $this->id()===0) {
            $this->data[Classifieds::ID]=$this->dataset->getID();
        }
        if ($this->dataset->getUID()>0 && $this->uid()===0) {
            $this->data[Classifieds::USER_ID]=$this->dataset->getUID();
        }
        if ($this->dataset->getSectionID()>0 && $this->sectionId()===0) {
            $this->data[Classifieds::SECTION_ID]=$this->dataset->getSectionID();
        }
        if ($this->dataset->getPurposeID()>0 && $this->purposeId()===0) {
            $this->data[Classifieds::PURPOSE_ID]=$this->dataset->getPurposeID();
        }
        $this->data[Classifieds::RTL]=$this->dataset->getNativeRTL();
        return $this;
    }
    
    
    public function dataset() : Content {
        return $this->dataset;
    }
   
    
    public function check() : Ad {
        if ($this->data[Classifieds::SECTION_ID]>0 && $this->data[Classifieds::PURPOSE_ID]===0) {
            $this->setPurposeId(5);
        }
        return $this;
    }
    
    
    public function countryCode() : string {        
        return $this->data[Classifieds::COUNTRY_CODE] ?? '';
    }
    
    
    public function documentId() : int {
        return $this->data['DOC_ID']??0;
    }
    
    
    public function rtl() : bool {        
        return $this->data[Classifieds::RTL]==1;
    }
        
    
    public function content() : string {
        return $this->data[Classifieds::CONTENT] ?? '';
    }
    
    
    public function text() : string {        
        return $this->text;
    }
    
    
    public function translation() : string {        
        return $this->translation;
    }
    
    
    public function mobiles() : array {
        return $this->data[Classifieds::TELEPHONES][0][0];
    }
    
    
    public function landlines() : array {
        return $this->data[Classifieds::TELEPHONES][1][0] ?? [];
    }
    
    
    public function otherlines() : array {
        return $this->data[Classifieds::TELEPHONES][2][0] ?? [];
    }
    
    
    public function emails() : array {
        return $this->data[Classifieds::EMAILS];
    }
    
   
    public function epoch() : int {
        return $this->data[Classifieds::UNIXTIME] ?? 0;
    }
    
    
    public function publisherType() : int {
        return $this->data[Classifieds::PUBLISHER_TYPE] ?? 0;
    }
    
    
    public function uid() : int {
        return $this->data[Classifieds::USER_ID] ?? 0;
    }
    
    
    public function setUID(int $uid) : Ad {
        $this->data[Classifieds::USER_ID]=($uid>0)?$uid:0;
        return $this;
    }

    
    public function state() : float {
        return ($this->dataset!==null) ? $this->dataset()->getState() : 7;
    }
    
    
    public function latitude() : float {
        return ($this->dataset!==null) ? $this->dataset()->getLatitude() : ($this->data[Classifieds::LATITUDE] ?? 0);
    }
    
    
    public function longitude() : float {
        return ($this->dataset!==null) ? $this->dataset()->getLongitude() : ($this->data[Classifieds::LONGITUDE] ?? 0);
    }

    
    public function countryId() : int {
        return ($this->dataset!==null) ? $this->dataset()->getCountryId() : ($this->data[Classifieds::COUNTRY_ID] ?? 0);
    }

    
    public function cityId() : int {
        return ($this->dataset!==null) ? $this->dataset()->getCityId() : ($this->data[Classifieds::CITY_ID] ?? 0);
    }
            

    public function location() : string {        
        return $this->data[Classifieds::LOCATION] ?? '';
    }

   
    
    private function formatPhoneNumber($number, $userISO='') : string {                
        $key='P.'.$userISO.$number;
        $value = DB::getCache()->get($key);
        if ($value) { 
            return $value;             
        }
        
        if (!$this->numberValidator) { $this->numberValidator = \libphonenumber\PhoneNumberUtil::getInstance(); }
        $num = $this->numberValidator->parse($number, $userISO);
        $result = '';
        if ($this->numberValidator->getRegionCodeForNumber($num, $userISO)===$userISO) {
            $result = $this->numberValidator->formatInOriginalFormat($num, $userISO);
        }
        else {
            $result = $this->numberValidator->format($num, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
        }
        DB::getCache()->set($key, $result);
        return $result;
    }
    
    
    public function contactInfo($userISO='') : array {
        $result = $this->data[Classifieds::CONTACT_INFO] ?? [];
        if (isset($result['p']) && is_array($result['p'])) {
            
            $nums = [];
            foreach ($result['p'] as $num){
                $nums[] = [
                    'v' => $this->formatPhoneNumber( $num['v'], $userISO),
                    't' => $num['t'],
                    'n' => substr($num['v'], 1)
                ];
            }
            $result['p'] = $nums;
        }
        if (!(isset($result['e']) && $result['e'])) {
            unset($result['e']);
        }
        unset($result['b']);
        unset($result['s']);
        unset($result['t']);
        return $result;
    }
    
    
    public function url() : string {
        return sprintf($this->data[Classifieds::URI_FORMAT], (Router::instance()->language=='ar' ? '' : Router::instance()->language.'/'), $this->id());    
    }
    
    
    public function picturePath(int $index=0) : string {
        return $this->data[Classifieds::PICTURES][$index];
    }
    
    
    public function picturesCount() : int {
        return isset($this->data[Classifieds::PICTURES]) ? count($this->data[Classifieds::PICTURES]) : 0;
    }
    
    
    
    public function hasAltContent() : bool {
        return isset($this->data[Classifieds::ALT_CONTENT]) && !empty($this->data[Classifieds::ALT_CONTENT]);
    }
    
    
    public function hasPictures() : bool {
        return isset($this->data[Classifieds::PICTURES]) && !empty($this->data[Classifieds::PICTURES]);
    }
    
    
    public function isRealEstate() : bool {
        return $this->data[Classifieds::ROOT_ID]==1;
    }
    
    
    public function isCar() : bool {
        return $this->data[Classifieds::ROOT_ID]==2;
    }
    
    
    public function isJob() : bool {
        return $this->data[Classifieds::ROOT_ID]==3;
    }

    
    function isForSale() : int {
        return $this->data[Classifieds::PURPOSE_ID]==1;
    }
    
    
    function isForRent() : int {
        return $this->data[Classifieds::PURPOSE_ID]==2;
    }
    
    
    function isVacancies() : int {
        return $this->data[Classifieds::PURPOSE_ID]==3;
    }
    
    
    function isSeekingWork() : int {
        return $this->data[Classifieds::PURPOSE_ID]==4;
    }
    
    
    function isToRent() : int {
        return $this->data[Classifieds::PURPOSE_ID]==6;
    }
    
    
    function isOfferedService() : int {
        return $this->data[Classifieds::PURPOSE_ID]==5;
    }
    
    
    function isToBuy() : int {
        return $this->data[Classifieds::PURPOSE_ID]==7;
    }
    
    
    function isVarious() : int {
        return $this->data[Classifieds::PURPOSE_ID]==999;
    }
    
    
    function isForTrade() : int {
        return $this->data[Classifieds::PURPOSE_ID]==8;
    }
    

    public function isFeatured() : bool {
        return isset($this->data[Classifieds::FEATURE_ENDING_DATE]) && ($this->data[Classifieds::FEATURE_ENDING_DATE] >= time());
    }


    public function isBookedFeature() : bool {
        return isset($this->data[Classifieds::BO_ENDING_DATE]) && ($this->data[Classifieds::BO_ENDING_DATE] >= time());
    }
    
    
    public function getSuperAdmin() : int {
        return $this->superAdmin;
    }
    
    
    public function setRTL() : Ad {
        $this->data[Classifieds::RTL] = 1;
        return $this;
    }
    
    
    public function setLTR() : Ad {
        $this->data[Classifieds::RTL] = 0;
        return $this;
    }
    
    
    public function getDateAdded() : int {
        return $this->data[Classifieds::DATE_ADDED];
    }
    
    
    public function setDateAdded(int $epoch) : Ad {
        $this->data[Classifieds::DATE_ADDED]=$epoch;
        return $this;
    }
    
    
    public function getDateModified() : int {
        return $this->dateModified ?? $this->getDateAdded();
    }
    
    
    public function setDateModified(int $epoch) : Ad {
        $this->dateModified=$epoch;
        return $this;
    }
    
    
    public function profile() : \MCUser {
        if ($this->profile!==null) {
            return $this->profile;
        }
        
        if ($this->list!==null) {
            $this->profile = $this->list->getCachedProfile($this->uid());
            //if ($profile!==null) {
            //    $this->profile = $profile;
            //}
            //else {
            //    $this->profile = new \MCUser($this->uid());
            //    $this->list->cacheProfile($this->profile);
            //}
        }
                    
        if ($this->profile===null) {
            if ($this->dataset!==null) {
                return $this->dataset()->getProfile();
            }
            $this->profile = new \MCUser($this->uid());
            $this->list->cacheProfile($this->profile);
        }
        return $this->profile;
    }
    
    
    public function reverseContent() : Ad {
        $content = $this->data[Classifieds::ALT_CONTENT];
        $this->data[Classifieds::ALT_CONTENT] = $this->data[Classifieds::CONTENT];
        $this->data[Classifieds::CONTENT] = $content;
        
        $content = $this->translation;
        $this->translation = $this->text;        
        $this->text = $content;
        
        return $this;
    }
    
    
    function formattedSinceDate(array $lang) : string {
        $isArabicInterface = Router::instance()->isArabic();
        $stamp='';
        $seconds=\time()-$this->data[Classifieds::UNIXTIME];
        if ($seconds<0) {
            return $stamp;
        }
        
        $days = floor($seconds/86400);
        $sinceText=$lang['since'].' ';
        $agoText=' '.$lang['ago'];
        if ($days) {
            if ($isArabicInterface) {
                $stamp=$sinceText.$this->formatPlural($days, 'day', $lang);
            }
            else {
                $stamp=$this->formatPlural($days, 'day', $lang).$agoText;
            }
        }
        else {
            $hours=\floor($seconds/3600);
            if ($hours) {
                if ($isArabicInterface) {
                    $stamp=$sinceText.$this->formatPlural($hours, 'hour', $lang);
                }
                else {
                    $stamp=$this->formatPlural($hours, 'hour', $lang).$agoText;
                }
            }
            else {
                $minutes=\max(1, \floor($seconds/60));
                //if (!$minutes) { $minutes=1; }
                if ($isArabicInterface) {
                    $stamp=$sinceText.$this->formatPlural($minutes, 'minute', $lang);
                }
                else {
                    $stamp=$this->formatPlural($minutes, 'minute', $lang).$agoText;
                }
            }
        }
        return $stamp;
    }
    
    
    private function formatPlural(int $number, string $fieldName, array $lang) : string {
        $isArabicInterface = Router::instance()->isArabic();
        $str='';
        if ($number==1) {
            if ($isArabicInterface) {
                $str=$lang[$fieldName];
            }
            else {
                $str='1 '.$lang[$fieldName];
            }
        }
        elseif ($number==2) {
            if ($isArabicInterface) {
                $str=$lang['2'.$fieldName];
            }
            else {
                $str='2 '.$lang['2'.$fieldName];
            }
        }
        elseif ($number>=3 && $number<11) {
            $str=$number.' '.$lang['3'.$fieldName];
        }
        else {
            $str=\number_format($number).' '.$lang[$fieldName.'s'];
        }
        return $str;
    }
    
    
    public function htmlDataAttributes($userISO='') : string {
        $result='';
        if (!empty($this->translation)) {
            $result.='data-alt="' .  \htmlspecialchars($this->translation , \ENT_QUOTES, 'UTF-8') . '" ';
        }
        if ($this->isFeatured()) {
            $result.='data-premuim=1 ';
        }
        /*
        if (!empty($this->mobiles())) {
            $result.='data-mobiles="' . implode(',', $this->mobiles()).'" ';
        }
        if (!empty($this->landlines())) {
            $result.='data-phones="' . implode(',', $this->landlines()).'" ';
        }
        if (!empty($this->otherlines())) {
            $result.='data-otherlines="' . implode(',', $this->otherlines()).'" ';
        }
         * 
         */
        
        if ($this->latitude()||$this->longitude()) {
            $result.='data-coord="'.$this->latitude().','.$this->longitude().'" ';
        }
        if ($this->picturesCount()) {
            $result.='data-pics="' . implode(',', $this->data[Classifieds::PICTURES]).'" ';            
        }
        
        if ($this->contactInfo()) {
            $result.='data-cui="' . htmlspecialchars(json_encode($this->contactInfo($userISO)), ENT_QUOTES, 'UTF-8') . '" ';            
        }
        
        return $result;
    }
    
    
    public function parseDbRow($row) : void {
        $this->setID($row['ID'])
            ->setSectionId($row['SECTION_ID'])
            ->setPurposeId($row['PURPOSE_ID'])
            ->setUID($row['WEB_USER_ID'])
            ->setDateAdded($row['DATE_ADDED'])
            ;
        
        $this->data[Classifieds::COUNTRY_ID] = $row['COUNTRY_ID'];

        $this->dataset = new Content($this);        
        $this->dataset->setID($row['ID'])
                ->setState($row['STATE'])
                ->setSectionID($row['SECTION_ID'])
                ->setPurposeID($row['PURPOSE_ID'])
                ->setCountryId($row['COUNTRY_ID'])
                ->setCityId($row['CITY_ID'])
                ->setUID($row['WEB_USER_ID'])
                ->setCoordinate($row['LATITUDE'], $row['LONGITUDE']);
            

        //if ($row['CONTENT'] && $row['CONTENT'][0]==='"') {
        //    $row['CONTENT']=trim(stripcslashes($row['CONTENT']), '"');
        //}
        
        $ext=\json_decode($row['CONTENT'], true);
        if (\json_last_error()) {
            \error_log(\var_export($ext, true));
        }
        
            
        $ext_version=$ext[Content::VERSION]??2;
        if ($ext_version===3) {
            $this->dataset->setApp(\substr($ext[Content::APP_NAME], 0, 1), \substr($ext[Content::APP_NAME], 2));
        }
        else {
            $this->dataset->setApp($ext[Content::APP_NAME]??'unk', $ext[Content::APP_VERSION]??'');
        }
           
        if ( !empty($row['PICTURES']) ) {
            $pics=\json_decode('{' . $row['PICTURES'] . '}', true);
            $this->dataset->setPictures($pics);
        }
        
        //if ($row['ID']==12920484) {
        //    if (is_string($ext)) {
        //        \error_log(\var_export($ext, true));
        //    }
        //}
       
        $this->dataset->setOld(\is_array($ext)?$ext:[])
                ->setBudget($ext[Content::BUDGET]??0)
                ->setUserAgent($ext[Content::USER_AGENT]??'')
                ->setContactInfo($ext[Content::CONTACT_INFO]??[])
                ->setRegions($ext[Content::REGIONS]??[])
                ->setUserLanguage($ext[Content::UI_LANGUAGE]??'en')
                ->setUserLevel($ext[Content::USER_LEVEL]??0)
                ->setLocation($ext[Content::LOCATION]??'')
                ->setNativeText($ext[Content::NATIVE_TEXT]??'')
                ->setForeignText($ext[Content::FOREIGN_TEXT]??'')
                ->setQualified(($ext[Content::QUALIFIED]??false))
                ->setIpAddress($ext[Content::IP_ADDRESS]??'')
                ->setIpScore($ext[Content::IP_SCORE]??0)
                ->setUserLocation($ext[Content::USER_LOCATION]??'')
                ->setMessage($ext[Content::MESSAGE]??'') 
                ->setRERA($ext[Content::RERA]??[])
                ; 
        
        $this->data[Classifieds::CONTENT] = $this->dataset()->getNativeText();
        $this->data[Classifieds::ALT_CONTENT] = $this->dataset()->getForeignText();
        if ($this->dataset()->getNativeRTL()===1) {
            $this->setRTL();
        }
        else {
            $this->setLTR();
        }
        
        $this->superAdmin=$row['SUPER_ADMIN']??0;
    }
    
    
    public function getAdFromAdUserTableForEditing(int $id) : void {
        $db = Router::instance()->db;
        $ad = $db->get(
                'SELECT AD_USER.ID, AD_USER.CONTENT, AD_USER.PURPOSE_ID, AD_USER.SECTION_ID, ' .
                'AD_USER.RTL, AD_USER.STATE, AD_USER.COUNTRY_ID, AD_USER.CITY_ID, ' .
                'AD_USER.LATITUDE, AD_USER.LONGITUDE, AD_USER.WEB_USER_ID, ' .
                'DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', AD_USER.DATE_ADDED) DATE_ADDED, '.
                '(select list(\'"\'||MEDIA.FILENAME||\'":\'||\'[\'||MEDIA.WIDTH||\',\'||MEDIA.HEIGHT||\']\') PICTURES ' .
                'from AD_MEDIA left join MEDIA on MEDIA.ID=AD_MEDIA.MEDIA_ID where AD_MEDIA.AD_ID=AD_USER.ID) ' .
                'FROM AD_USER WHERE AD_USER.id=?', [$id]);
        
        if (\is_array($ad) && \count($ad)===1) {
            $this->parseDbRow($ad[0]);
            /*
            $this->setID($ad[0]['ID'])
                    ->setSectionId($ad[0]['SECTION_ID'])
                    ->setPurposeId($ad[0]['PURPOSE_ID'])
                    ->setUID($ad[0]['WEB_USER_ID'])
                    ->setDateAdded($ad[0]['DATE_ADDED'])
                    ;
            
            $this->data[Classifieds::COUNTRY_ID] = $ad[0]['COUNTRY_ID'];

            $this->dataset = new Content($this);
            $this->dataset
                    ->setID($id)
                    ->setState($ad[0]['STATE'])
                    ->setSectionID($ad[0]['SECTION_ID'])
                    ->setPurposeID($ad[0]['PURPOSE_ID'])
                    ->setCountryId($ad[0]['COUNTRY_ID'])
                    ->setCityId($ad[0]['CITY_ID'])
                    ->setUID($ad[0]['WEB_USER_ID'])
                    ->setCoordinate($ad[0]['LATITUDE'], $ad[0]['LONGITUDE']);
            
            $ext = \json_decode($ad[0]['CONTENT'], true);
            $ext_version = $ext[Content::VERSION]??2;
            
            if ($ext_version===3) {
                $this->dataset->setApp(\substr($ext[Content::APP_NAME], 0, 1), \substr($ext[Content::APP_NAME], 2));
            }
            else {
                $this->dataset->setApp($ext[Content::APP_NAME]??'unk', $ext[Content::APP_VERSION]??'');
            }
           
            if ( !empty($ad[0]['PICTURES']) ) {
                $pics = \json_decode('{' . $ad[0]['PICTURES'] . '}', true);
                $this->dataset->setPictures($pics);
            }
            $this->dataset
                    ->setOld($ext)
                    ->setBudget($ext[Content::BUDGET]??0)
                    ->setUserAgent($ext[Content::USER_AGENT]??'')
                    ->setContactInfo($ext[Content::CONTACT_INFO]??[])
                    ->setRegions($ext[Content::REGIONS]??[])
                    ->setUserLanguage($ext[Content::UI_LANGUAGE]??'en')
                    ->setLocation($ext[Content::LOCATION]??'')
                    ->setNativeText($ext[Content::NATIVE_TEXT]??'')
                    ->setForeignText($ext[Content::FOREIGN_TEXT]??'')
                    ->setQualified(($ext[Content::QUALIFIED]??false))           
                    ;
            */
            
            $user = Router::instance()->user;
            if ($user!==null && $user->id()>0) {                
                if ($user->id()===$this->dataset->getUID()) {
                    $this->dataset->setUserLevel($user->level());
                }
                else {
                    \Config::instance()->incLibFile('MCUser');
                    $u=new \MCUser($this->dataset->getUID());
                    $this->dataset->setUserActivatedMobileNumber($u->getMobileNumber());                    
                }
                
            }
        }
    }
}
