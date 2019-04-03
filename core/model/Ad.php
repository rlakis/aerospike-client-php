<?php
namespace Core\Model;

class Ad {
    private $data;              // raw classified array
    private $text;              // ad text without contacts
    private $translation;       // ad text alter language without contacts
    private $profile;
    private $numberValidator = null;
    
    
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
        return $this->data[Classifieds::SECTION_ID];
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
        //error_log(var_export($this->data[Classifieds::TELEPHONES][0][0], true));
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
            //error_log("cached ".$value);
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
        $seconds=time()-$this->data[Classifieds::UNIXTIME];
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
