<?php
namespace Core\Model;

class Ad {
    private $data;              // raw classified array
    private $text;              // ad text without contacts
    private $translation;       // ad text alter language without contacts
    private $profile;           // MCUser instance
    private $numberValidator = null;
    private $dataset;
    
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
        if (isset(Router::instance()->sections[$value])) {
            $this->data[Classifieds::SECTION_ID]=$value;
            $this->data[Classifieds::ROOT_ID]=\intval(Router::instance()->sections[$value][4]);
        }
        else {
            $this->data[Classifieds::SECTION_ID]=0;
            $this->data[Classifieds::ROOT_ID]=0;
        }                
        return $this;
    }
    
    
    public function purposeId() : int {
        return $this->data[Classifieds::PURPOSE_ID]??0;
    }
    
    
    public function setPurposeId(int $value) : Ad {
        $this->data[Classifieds::PURPOSE_ID]=$value;
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
    
    /*
    public function setText(string $value) : Ad {
        $this->text=\trim($value);
        $success = \preg_match_all('/\p{Arabic}/u', $this->text);
        if ($success/\mb_strlen($this->text)>0.4) {
            $this->setRTL();
        }
        else {
            $this->setLTR();
        }
        return $this;
    }


    public function setTranslation(string $value) : Ad {
        $this->translation=\trim($value);
        return $this;
    }
    */
    
    public function check() : Ad {
        if ($this->data[Classifieds::SECTION_ID]>0 && $this->data[Classifieds::PURPOSE_ID]===0) {
            $this->setPurposeId(5);
        }
        return $this;
    }
    
    
    public function countryCode() : string {        
        return $this->data[Classifieds::COUNTRY_CODE] ?? '';
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
    
    
    public function latitude() : float {
        return $this->data[Classifieds::LATITUDE] ?? 0;
    }
    
    
    public function longitude() : float {
        return $this->data[Classifieds::LONGITUDE] ?? 0;
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
        return sprintf($this->data[Classifieds::URI_FORMAT], (Router::getInstance()->language=='ar' ? '' : Router::getInstance()->language.'/'), $this->id());    
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
    
    
    public function setRTL() : Ad {
        $this->data[Classifieds::RTL] = 1;
        return $this;
    }
    
    
    public function setLTR() : Ad {
        $this->data[Classifieds::RTL] = 0;
        return $this;
    }
    
    
    public function profile() : \MCUser {
        if ($this->profile===null) {
            $this->profile = new \MCUser($this->uid());
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
        $isArabicInterface = Router::getInstance()->isArabic();
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
        $isArabicInterface = Router::getInstance()->isArabic();
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
}


class Content {
    const VERSION_NUMBER        = 3;
    
    const ID                    = 'id';
    const STATE                 = 'state';
    const ROOT_ID               = 'ro';
    const SECTION_ID            = 'se';
    const PURPOSE_ID            = 'pu';
    const APP_NAME              = 'app';
    const APP_VERSION           = 'app_v';
    const USER_AGENT            = 'agent';
    const NATIVE_RTL            = 'rtl';
    const FOREIGN_RTL           = 'altRtl';
    const ATTRIBUTES            = 'attrs';
    const ATTR_NATIVE           = 'ar';
    const ATTR_FOREIGN          = 'en';
    const ATTR_GEO_KEYS         = 'geokeys';
    const ATTR_LOCALES          = 'locales';
    const ATTR_LOCALITY         = 'locality';
    const ATTR_LOCALITY_CITIES  = 'cities';
    const ATTR_LOCALITY_ID      = 'id';
    const ATTR_PHONES           = 'phones';
    const ATTR_PHONES_NUMBERS   = 'n';
    const ATTR_PHONES_TYPES     = 't';
    const ATTR_PRICE            = 'price';
    const ATTR_SPACE            = 'space';
    const ATTR_ROOMS            = 'rooms';
    const BUDGET                = 'budget';
    
    const CONTACT_INFO          = 'cui';
    const CONTACT_INFO_BLACKBERRY = 'b'; // deprecated
    const CONTACT_INFO_EMAIL    = 'e';
    const CONTACT_INFO_PHONE    = 'p';
    const CONTACT_INFO_PHONE_COUNTRY_CODE   = 'c';
    const CONTACT_INFO_PHONE_COUNTRY_ISO    = 'i';
    const CONTACT_INFO_PHONE_RAW_NUMBER     = 'r';
    const CONTACT_INFO_PHONE_TYPE           = 't';
    const CONTACT_INFO_PHONE_INTERNATIONAL  = 'v';
    const CONTACT_INFO_PHONE_X              = 'x'; // deprecated
    const CONTACT_INFO_SKIPE                = 's'; // deprecated
    const CONTACT_INFO_TWITTER              = 't'; // deprecated
    
    const CONTACT_TIME          = 'cut'; // deprecated
    const CONTACT_TIME_AFTER    = 'a'; // deprecated
    const CONTACT_TIME_BEFORE   = 'b'; // deprecated
    const CONTACT_TIME_HOUR     = 't'; // deprecated
    
    const UI_CONTROL            = 'extra'; // deprecated
    const UI_CONTROL_MAP        = 'm'; // deprecated
    const UI_CONTROL_PICTURES   = 'p'; // deprecated
    const UI_CONTROL_VIDEO      = 'v'; // deprecated
    const UI_CONTROL_TRANSLATION= 't'; // deprecated
    const UI_LANGUAGE           = 'hl';
    const IP_ADDRESS            = 'ip';
    const IP_SCORE              = 'ipfs';
    const NATIVE_TEXT           = 'other';
    const FOREIGN_TEXT          = 'altother';
    const LATITUDE              = 'lat';    // deprecated
    const LONGITUDE             = 'lon';    // deprecated
    const LOCATION              = 'loc';
    const LOCATION_ARABIC       = 'loc_ar';
    const LOCATION_ENGLISH      = 'loc_en';
    const MEDIA                 = 'media'; // deprecated
    const PICTURE_INDEX         = 'pix_idx';   
    const DEFAULT_PICTURE       = 'pix_def';    // deprecated
    const PICTURES              = 'pics';
    const REGIONS               = 'pubTo';
    const UID                   = 'user';
    const USER_LEVEL            = 'userLvl';
    const USER_LOCATION         = 'userLOC';
    const QUALIFIED             = 'qualified';
    const VERSION               = 'version';
    
    private $content;
    private $profile;
    
    public function __construct() {
        $this->content = [
            self::ID                => 0,
            self::UID               => 0,
            self::STATE             => 0,
            self::ROOT_ID           => 0,
            self::SECTION_ID        => 0,
            self::PURPOSE_ID        => 0,
            self::APP_NAME          => '',
            self::APP_VERSION       => '',
            self::VERSION           => self::VERSION_NUMBER,
            self::USER_AGENT        => '',
            self::IP_ADDRESS        => '',
            self::IP_SCORE          => 0,                                    
            self::BUDGET            => 0,
            self::CONTACT_INFO      => [self::CONTACT_INFO_PHONE=>[], self::CONTACT_INFO_EMAIL=>'', self::CONTACT_INFO_BLACKBERRY=>'', self::CONTACT_INFO_SKIPE=>'', self::CONTACT_INFO_TWITTER=>''],
            self::CONTACT_TIME      => [self::CONTACT_TIME_BEFORE=>6, self::CONTACT_TIME_AFTER=>24, self::CONTACT_TIME_HOUR=>0],
            self::UI_CONTROL        => [self::UI_CONTROL_MAP=>2, self::UI_CONTROL_PICTURES=>2, self::UI_CONTROL_TRANSLATION=>2, self::UI_CONTROL_VIDEO=>2],
            self::UI_LANGUAGE       => 'ar',
            self::NATIVE_TEXT       => '',
            self::NATIVE_RTL        => 0,
            self::FOREIGN_TEXT      => '',
            self::FOREIGN_RTL       => 0,
            self::MEDIA             => 0,
            self::DEFAULT_PICTURE   => 0,
            self::PICTURES          => [],
            self::REGIONS           => [],
            self::LATITUDE          => 0,
            self::LONGITUDE         => 0,
            self::LOCATION          => '',
            self::LOCATION_ARABIC   => '',
            self::LOCATION_ENGLISH  => '',
            self::USER_LEVEL        => 0,
            self::USER_LOCATION     => '',
            self::ATTRIBUTES        => [
                                    self::ATTR_NATIVE => '',
                                    self::ATTR_FOREIGN => '',
                                    self::ATTR_GEO_KEYS => [],
                                    self::ATTR_LOCALES => [],
                                    self::ATTR_LOCALITY => [self::ATTR_LOCALITY_ID=>0, self::ATTR_LOCALITY_CITIES=>[]],
                                    self::ATTR_PHONES => [],                
                                   ],
            self::QUALIFIED     => false            
        ];
    }
    
    
    public function setID(int $id) : Content {
        $this->content[self::ID]=$id;
        return $this;
    }
    
    
    public function getID() : int {
        return $this->content[self::ID];
    }
    
    
    public function setUID(int $uid) : Content {
        if ($uid!==$this->content[self::UID]) {
            $this->profile=null;
        }
        $this->content[self::UID]=$uid;
        return $this;
    }
    

    public function getUID() : int {
        return $this->content[self::UID];
    }
    
    
    public function getProfile() : \MCUser {
        if ($this->profile===null) {
            $this->profile = new \MCUser($this->getUID());
        }
        return $this->profile;
    }
    
    
    public function setState(int $state) : Content {
        $this->content[self::STATE]=$state;
        return $this;
    }
    
    
    public function getSectionID() : int {
        return $this->content[self::SECTION_ID];
    }
    
    
    public function setSectionID(int $id) : Content {
        if (isset(Router::instance()->sections[$id])) {
            $this->content[self::SECTION_ID]=$id;
            $this->content[self::ROOT_ID]=\intval(Router::instance()->sections[$id][4]);
        }
        else {
            $this->content[self::SECTION_ID]=0;
            $this->content[self::ROOT_ID]=0;
        }               
        return $this;
    }


    public function getPurposeID() : int {
        return $this->content[self::PURPOSE_ID];
    }

    
    public function setPurposeID(int $id) : Content {
        $this->content[self::PURPOSE_ID]=$id;
        return $this;
    }
    
    
    public function setApp(string $name, string $version) : Content {
        $this->content[self::APP_NAME]=$name;
        $this->content[self::APP_VERSION]=$version;
        return $this;
    }
    
    
    public function setVersion(int $version) : Content {
        if ($version!==$this->content[self::VERSION]) {
            $this->content[self::VERSION]=$version;
        }
        return $this;
    }
    
    
    public function setUserAgent(string $user_agent) : Content {
        $this->content[self::USER_AGENT]=$user_agent;
        return $this;
    }
    
    
    public function setBudget(int $budget) : Content {
        if ($this->getProfile()->getBalance()<=0) {
            $budget=0;
        }
        $this->content[self::BUDGET] = $budget;
        return $this;
    }
    
    
    public function getIpAddress() : string {
        return $this->content[self::IP_ADDRESS];
    }
    
    
    public function setIpAddress(string $ip) : Content {
        $this->content[self::IP_ADDRESS]=$ip;
        return $this;
    }
    
    
    public function setIpScore(float $score) : Content {
        $this->content[self::IP_SCORE]=$score;
        return $this;
    }
    
    
    public function addPhone(int $country_callkey, string $country_iso_code, string $raw_number, int $number_type, string $international_number) : Content {
        $this->content[self::CONTACT_INFO][self::CONTACT_INFO_PHONE][]=[
            self::CONTACT_INFO_PHONE_COUNTRY_CODE   => $country_callkey,
            self::CONTACT_INFO_PHONE_COUNTRY_ISO    => $country_iso_code,
            self::CONTACT_INFO_PHONE_RAW_NUMBER     => $raw_number,
            self::CONTACT_INFO_PHONE_TYPE           => $number_type,
            self::CONTACT_INFO_PHONE_INTERNATIONAL  => $international_number
        ];
        return $this;
    }
    
    
    public function setEmail(string $email) : Content {
        $this->content[self::CONTACT_INFO][self::CONTACT_INFO_EMAIL]=$email;
        return $this;
    }
    
    
    public function setUserLanguage(string $language) : Content {
        $this->content[self::UI_LANGUAGE]= \in_array($language, ['ar','en'])?$language:'ar';
        return $this;
    }
    
    
    public function setUserLevel(int $level) : Content {
        $this->content[self::USER_LEVEL]= $level;
        return $this;
    }
        
    
    public function setUserLocation() : Content {
        $this->content[self::USER_LOCATION] = \IPQuality::ipLocation($this->getIpAddress());
        return $this;
    }
    
    private function rtl(string $text) : int {
        $success = \preg_match_all('/\p{Arabic}/u', $text);
        $spaces = \preg_match_all('/\s/u', $text);
        if ($success/(\mb_strlen($text)-$spaces)>=0.5) {
            return 1;
        }
        return 0;
    }
    
    
    public function setNativeText(string $text) : Content {
        $this->content[self::NATIVE_TEXT] = \trim($text);
        $this->content[self::NATIVE_RTL] = $this->rtl($this->content[self::NATIVE_TEXT]);
        return $this;
    }
    
    
    public function getNativeRTL() : int {
        return $this->content[self::NATIVE_RTL];
    }
    
    
    public function setForeignText(string $text) : Content {
        $this->content[self::FOREIGN_TEXT]= \trim($text);
        $this->content[self::FOREIGN_RTL] = $this->rtl($this->content[self::FOREIGN_TEXT]);
        return $this;
    }
    
    
    public function setPictures(array $pictures) : Content {
        $this->content[self::PICTURES]=$pictures;
        $this->content[self::MEDIA]=\count($pictures)>0?1:0;
        return $this;
    }
    
    
    public function addRegion(int $region) : Content {
        if (!\in_array($region, $this->content[self::REGIONS])) {
            $this->content[self::REGIONS][]=$region;
        }
        return $this;
    }
    
    
    public function addRegions(array $regions) : Content {
        $this->content[self::REGIONS]=array_merge($this->content[self::REGIONS], \array_values($regions));
        return $this;
    }
    
    
    public function setCoordinate(float $lat, float $lng) : Content {
        $this->content[self::LATITUDE]=$lat;
        $this->content[self::LONGITUDE]=$lng;
        return $this;        
    }
    

    public function setLocation(string $location) : Content {
        if ($this->content[self::APP_NAME]==='web') {
            $this->content[self::LOCATION]=$location;
        }
        else {        
            if ($this->rtl($location)) {
                $this->content[self::LOCATION_ARABIC]=$location;            
            }
            else {
                $this->content[self::LOCATION_ENGLISH]=$location;
            }
        }
        return $this;
    }

    
    public function setQualified(bool $value) : Content {
        $this->content[self::QUALIFIED]=$value;
        return $this;
    }
    

    public function getData() : array {
        unset($this->content[self::ATTRIBUTES]);        
        return $this->content;
    }
    
    
    public function toJsonString(int $options) : string {
        unset($this->content[self::ATTRIBUTES]);        
        return \json_encode($this->content, $options);        
    }
    
    
    
    public function save(int $state=0) : bool {
        $db = Router::instance()->database();
        if ($this->getID()>0) {
            $q = 'UPDATE ad_user set /* ' . __CLASS__ . '.' . __FUNCTION__ . ' */ ';
            $q.= 'content=?, purpose_id=?, section_id=?, rtl=?, country_id=?, city_id=?, latitude=?, longitude=?, state=?, media=? ';
            $q.= 'where id=? returning state';
        }
        else {
            $q = 'INSERT INTO ad_user (content, purpose_id, section_id, rtl, country_id, city_id, latitude, longitude, state, media, web_user_id) ';
            $q.= 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) returning ID';
        }
        $st=$db->prepareQuery($q);
        $st->bindValue(1, \json_encode($adContent), \PDO::PARAM_STR);
        $st->bindValue(2, $this->getPurposeID(), \PDO::PARAM_INT);
        $st->bindValue(3, $this->getSectionID(), \PDO::PARAM_INT);
        $st->bindValue(4, $this->getNativeRTL(), \PDO::PARAM_INT);
        //$st->bindValue(5, $this->pending['post']['cn']);
        //$st->bindValue(6, $this->pending['post']['c']);
        $st->bindValue(7, $this->content[self::LATITUDE]);
        $st->bindValue(8, $this->content[self::LONGITUDE]);
        $st->bindValue(9, $this->content[self::STATE], PDO::PARAM_INT);        
        $st->bindValue(10, (\count($this->content[self::PICTURES])>0?1:0), PDO::PARAM_INT);
        $st->bindValue(11, $this->getID()>0 ? $this->getID() : $this->getUID(), PDO::PARAM_INT);
        if ($st->execute()) {
            if (($result = $st->fetch(PDO::FETCH_ASSOC))!==FALSE) {
                if ($this->getID()>0) {
                    $this->setState($result['STATE']);
                }
                else {
                    $this->setID($result['ID']);
                }
            }
            unset($st);
            return $db->commit();
        } 
        else {
            $db->rollback();
        }
        unset($st);
        return false;        
    }
    
}
