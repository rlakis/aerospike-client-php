<?php
namespace Core\Model;

class Ad {
    private $data;
        
    function __construct(array $data=[]) {
        $this->data = $data;
        if (!isset($this->data[Classifieds::RTL])) { $this->data[Classifieds::RTL] = 0; }
        if (!isset($this->data[Classifieds::ROOT_ID])) { $this->data[Classifieds::ROOT_ID] = 0; }
        if (!isset($this->data[Classifieds::SECTION_ID])) { $this->data[Classifieds::SECTION_ID] = 0; }
    }
    
    
    public function data() : array {
        return $this->data;
    }
    
    
    public function id() : int {
        return $this->data[Classifieds::ID] ?? 0;
    }
    
    
    public function rootId() : int {
        return $this->data[Classifieds::ROOT_ID];
    }
    
    
    public function sectionId() : int {
        return $this->data[Classifieds::SECTION_ID];
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
        return preg_split("/\x{200b}/u", $this->data[Classifieds::CONTENT])[0];
    }
    
    public function mobiles() : array {
        return $this->data[Classifieds::TELEPHONES][0];
    }
    
    
    public function landlines() : array {
        return $this->data[Classifieds::TELEPHONES][1];
    }
    
    
    public function otherlines() : array {
        return $this->data[Classifieds::TELEPHONES][2];
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
    
    
    public function latitude() : float {
        return $this->data[Classifieds::LATITUDE] ?? 0;
    }
    
    
    public function longitude() : float {
        return $this->data[Classifieds::LONGITUDE] ?? 0;
    }
    

    public function location() : string {        
        return $this->data[Classifieds::LOCATION] ?? '';
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
    
    
    public function reverseContent() : Ad {
        $content = $this->data[Classifieds::ALT_CONTENT];
        $this->data[Classifieds::ALT_CONTENT] = $this->data[Classifieds::CONTENT];
        $this->data[Classifieds::CONTENT] = $content;
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
            $hours=floor($seconds/3600);
            if ($hours) {
                if ($isArabicInterface) {
                    $stamp=$sinceText.$this->formatPlural($hours, 'hour', $lang);
                }
                else {
                    $stamp=$this->formatPlural($hours, 'hour', $lang).$agoText;
                }
            }
            else {
                $minutes=max(1, floor($seconds/60));
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
            $str=number_format($number).' '.$lang[$fieldName.'s'];
        }
        return $str;
    }
    
}
