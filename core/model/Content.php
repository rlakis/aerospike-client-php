<?php
namespace Core\Model;

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
    const MESSAGE               = 'msg';
    const QUALIFIED             = 'qualified';
    const VERSION               = 'version';
    
    protected $content;
    private $ad;
    private $profile;
    private $countryId;
    private $cityId;
    private $old;
    private $originalVersion;
    
    
    public function __construct(?Ad $ad=null) {
        if ($ad) { $this->ad=$ad; }
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
            self::MESSAGE           => '',
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
    
    
    public function setAd(Ad $ad) : Content {
        $this->ad = $ad;
        return $this;
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
    
    
    public function getState() : int {
        return $this->content[self::STATE];
    }
    
    
    public function setState(int $state) : Content {
        $this->content[self::STATE]=$state;       
        return $this;
    }
    
    
    public function getMessage() : string {
        return $this->content[self::MESSAGE];
    }
    
    
    public function setMessage(string $message) : Content {
        $this->content[self::MESSAGE] = $message;
        return $this;
    }        
   
    
    public function getCountryId() : int {
        return $this->countryId;
    }
    
    
    public function setCountryId(int $kCountryId) : Content {
        $this->countryId = $kCountryId;
        return $this;
    }
    
    
    public function getCityId() : int {
        return $this->cityId;
    }
    
    
    public function setCityId(int $kCityId) : Content {
        $this->cityId = $kCityId;
        return $this;
    }
    
    
    public function getRootId() : int {
        return $this->content[self::ROOT_ID];
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
    
    
    public function getAppShortName() : string {
        if (\strlen($this->content[self::APP_NAME])>=0) {
            return \strtoupper(\substr($this->content[self::APP_NAME], 0, 1));
        }
        return 'W';
    }
    
    public function setApp(string $name, string $version) : Content {
        if (\strlen($name)===1) {
            $name = ($name==='w'?'web':($name==='a'?'android':($name==='i'?'ios':'unk')));
        }
        $this->content[self::APP_NAME]=$name;
        $this->content[self::APP_VERSION]=$version;        
        return $this;
    }
    
    
    public function getVerion() : int {
        return $this->content[self::VERSION];
    }
    
    
    public function setVersion(int $version) : Content {
        if ($version!==$this->content[self::VERSION]) {
            $this->content[self::VERSION]=$version;
        }
        return $this;
    }
    
    
    public function getUserAgent() : string {
        return $this->content[self::USER_AGENT];
    }
    
    
    public function setUserAgent(string $user_agent) : Content {
        $this->content[self::USER_AGENT]=$user_agent;
        return $this;
    }
    
    
    public function getBudget() : int {
        return $this->content[self::BUDGET];
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
    
    
    public function getIpScore() : float {
        return $this->content[self::IP_SCORE];
    }
    
    
    public function setIpScore(float $score) : Content {
        $this->content[self::IP_SCORE]=$score;
        return $this;
    }
    
    
    public function getContactInfo() : array {
        return $this->content[Content::CONTACT_INFO];
    }
    
    
    public function setContactInfo(array $cui) : Content {
        $this->content[Content::CONTACT_INFO] = $cui;
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
    
    
    public function getUserLanguage() : string {
        return $this->content[self::UI_LANGUAGE];
    }
    
    
    public function setUserLanguage(string $language) : Content {
        $this->content[self::UI_LANGUAGE]= \in_array($language, ['ar','en'])?$language:'ar';
        return $this;
    }
    
    
    public function setUserLevel(int $level) : Content {
        $this->content[self::USER_LEVEL]= $level;
        return $this;
    }
        
    
    public function getUserLocation() : string {
        return $this->content[self::USER_LOCATION];
    }
    
    
    public function setUserLocation(string $location='') : Content {
        if ($location) {
            $this->content[self::USER_LOCATION] = $location;
        }
        else {
            $this->content[self::USER_LOCATION] = \IPQuality::ipLocation($this->getIpAddress());
        }
        return $this;
    }
    
    
    private function rtl(string $text) : int {
        if ( !empty($text) ) {
            $success = \preg_match_all('/\p{Arabic}/u', $text);
            $spaces = \preg_match_all('/\s/u', $text);
            if ($success/(\mb_strlen($text)-$spaces)>=0.3) {
                return 1;
            }
        }
        return 0;
    }
    
    
    public function getNativeText() : string {
        error_log(PHP_EOL."Version {$this->getVerion()} was {$this->originalVersion}");
        if (preg_match("/\x{200b}/u", $this->content[self::NATIVE_TEXT])) {
           return $this->content[self::NATIVE_TEXT]; 
        }
       
        
        
        $t = $this->content[self::NATIVE_TEXT] . '\u{200b} / ';
        
        return $t;
    }
    
    
    public function setNativeText(string $text) : Content {
        $this->content[self::NATIVE_TEXT] = \trim($text);
        $this->content[self::NATIVE_RTL] = $this->rtl($this->content[self::NATIVE_TEXT]);
        
        return $this;
    }
    
    
    public function getNativeRTL() : int {
        return $this->content[self::NATIVE_RTL];
    }
    
    
    public function getForeignText() : string {
        return $this->content[self::FOREIGN_TEXT];
    }
    
    
    public function setForeignText(string $text) : Content {
        $this->content[self::FOREIGN_TEXT] = \trim($text);
        $this->content[self::FOREIGN_RTL] = $this->rtl($this->content[self::FOREIGN_TEXT]);
        return $this;
    }
    
    
    public function getPictures() : array {
        return $this->content[self::PICTURES];
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
        $this->content[self::REGIONS] = \array_merge($this->content[self::REGIONS], \array_values($regions));
        return $this;
    }
    
    
    public function getRegions() : array {
        return $this->content[self::REGIONS];
    }
    
    
    public function setRegions(array $regions) : Content {
        $this->content[self::REGIONS] = \array_values($regions);
        return $this;
    }

    
    public function setCoordinate(float $lat, float $lng) : Content {
        $this->content[self::LATITUDE]=$lat;
        $this->content[self::LONGITUDE]=$lng;
        return $this;        
    }
    
    
    public function getLatitude() : float {
        return $this->content[self::LATITUDE] ?? 0;
    }
    
    
    public function getLongitude() : float {
        return $this->content[self::LONGITUDE] ?? 0;
    }
    
    
    public function getLocation() : string {
        if ($this->content[self::LOCATION]) {
            return $this->content[self::LOCATION];
        }
        else {
            return $this->content[self::LOCATION_ARABIC]??$this->content[self::LOCATION_ENGLISH];
        }
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

    
    public function isQualified() : bool {
        return $this->content[self::QUALIFIED];
    }
        
    
    public function setQualified(bool $value) : Content {
        $this->content[self::QUALIFIED]=$value;
        return $this;
    }
    
    
    public function setOld(array $kContent) : Content {
        $this->old = $kContent;
        $this->originalVersion=$this->old[Content::VERSION]??2;
        return $this;
    }
    
    
    public function getOldContent() : array {
        return $this->old;
    }

    public function getData() : array {
        unset($this->content[self::ATTRIBUTES]);        
        return $this->content;
    }
    
    
    public function toJsonString(int $options) : string {
        unset($this->content[self::ATTRIBUTES]);        
        return \json_encode($this->content, $options);        
    }
    
    
    public function prepare() : void {        
    	if (!Router::instance()->countryExists($this->countryId)) {
            $this->countryId = 0;
            $this->cityId = 0;
            if ( ! empty($this->content[self::REGIONS]) ) {
                $this->cityId = $this->content[self::REGIONS][0];
                $this->countryId = Router::instance()->getCountryId($this->cityId);
            }
    	}
        if ($this->countryId>0 && $this->cityId===0) {
            
        }
    }
    
    
    public function save(int $state=0, int $version=3) : bool {
        $this->prepare();
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
        
        $st = $db->prepareQuery($q);
        $ms = $db->prepareQuery('select id from media where filename=?');
        
        
        $pictures = array_keys($this->content[self::PICTURES]);
        foreach ($pictures as $picfile) {
            $ms->bindValue(1, $picfile, \PDO::PARAM_STR);
            if ($ms->execute()) {
                
                if ($ms->fetch()===FALSE) {
                    //error_log(PHP_EOL. $picfile . ' not found!!');
                    unset($this->content[self::PICTURES][$picfile]);
                }
            }            
        }
        
        $st->bindValue(1, \json_encode($this->getAsVersion(3)), \PDO::PARAM_STR);
        $st->bindValue(2, $this->getPurposeID(), \PDO::PARAM_INT);
        $st->bindValue(3, $this->getSectionID(), \PDO::PARAM_INT);
        $st->bindValue(4, $this->getNativeRTL(), \PDO::PARAM_INT);
        $st->bindValue(5, $this->countryId, \PDO::PARAM_INT);
        $st->bindValue(6, $this->cityId, \PDO::PARAM_INT);
        $st->bindValue(7, $this->content[self::LATITUDE]);
        $st->bindValue(8, $this->content[self::LONGITUDE]);
        $st->bindValue(9, $this->content[self::STATE], \PDO::PARAM_INT);        
        $st->bindValue(10, (\count($this->content[self::PICTURES])>0?1:0), \PDO::PARAM_INT);
        $st->bindValue(11, $this->getID()>0 ? $this->getID() : $this->getUID(), \PDO::PARAM_INT);
        if ($st->execute()) {
            if (($result = $st->fetch(\PDO::FETCH_ASSOC))!==FALSE) {
                if ($this->getID()>0) {
                    $this->setState($result['STATE']);
                }
                else {
                    $this->setID($result['ID']);
                }
            }
            unset($st);
            
            //error_log(PHP_EOL . ' ad id '.$this->getID());
            $images = $db->get('select AD_MEDIA.ID, AD_MEDIA.MEDIA_ID, MEDIA.FILENAME from ad_media left join media on media.ID=AD_MEDIA.MEDIA_ID where ad_id=?', [$this->getID()]);
            
            $to_delete_images=[];
            $no_change_images=[];
            foreach ($images as $image) {
                //error_log(PHP_EOL . 'Image ' . var_export($image, true));
                if ( !isset($this->content[self::PICTURES][$image['FILENAME']])) {
                    $to_delete_images[] = $image['ID'];
                }
                else {
                    $no_change_images[]=$image['FILENAME'];
                }
            }
            
            if ( ! empty($this->content[self::PICTURES])) {                
                $is = $db->prepareQuery('INSERT INTO AD_MEDIA (AD_ID, MEDIA_ID) VALUES (?, ?)');
                $mfiles = array_keys($this->content[self::PICTURES]);                
                foreach ($mfiles as $file) {
                    if (\in_array($file, $no_change_images)) { continue; }
                    
                    //error_log($file);
                    $ms->bindValue(1, $file, \PDO::PARAM_STR);
                    if ($ms->execute()) {
                        //error_log(PHP_EOL . $file . ' selected');
                        if (($rs = $ms->fetch(\PDO::FETCH_ASSOC))!==FALSE) {                            
                            $is->bindValue(1, $this->getID(), \PDO::PARAM_INT);
                            $is->bindValue(2, $rs['ID'], \PDO::PARAM_INT);
                            if ($is->execute()) {
                                //error_log(PHP_EOL .$file . ' inserted');
                            }
                            else {
                                error_log(PHP_EOL .$file . ' failed');
                            }
                        }                        
                    }
                }
            }
            
            if ( !empty($to_delete_images)) {
                //error_log(var_export($to_delete_images, true));
                
                $dm = $db->prepareQuery('delete from ad_media where id=?');
                foreach ($to_delete_images as $id) {
                    $dm->bindValue(1, $id, \PDO::PARAM_INT);
                    $dm->execute();
                }
            }
            
            return $db->commit();
        }
        else {
            $db->rollback();
        }
        unset($st);
        return false;        
    }
    
    
    
    public function getAsVersion(int $version) : array {
        switch ($version) {
            case 2: return $this->getAsVersion2();
            case 3: return $this->getAsVersion3();
        }
        return [];
    }
    
    
    private function getAsVersion3() : array {
        $rs=[
            self::CONTACT_INFO  => $this->content[self::CONTACT_INFO],            
            self::USER_LEVEL    => $this->content[self::USER_LEVEL],
            self::USER_LOCATION => $this->content[self::USER_LOCATION],
            self::USER_AGENT    => $this->content[self::USER_AGENT],
            self::UI_LANGUAGE   => $this->content[self::UI_LANGUAGE],
            self::IP_ADDRESS    => $this->getIpAddress(),
            self::IP_SCORE      => $this->content[self::IP_SCORE],
            self::QUALIFIED     => $this->content[self::QUALIFIED]?1:0,
            self::BUDGET        => $this->content[self::BUDGET],
            self::NATIVE_TEXT   => $this->content[self::NATIVE_TEXT],
            
            self::APP_NAME      => $this->content[self::APP_NAME][0].'-'.$this->content[self::APP_VERSION],
            self::VERSION       => 3,
            
        ];
        unset($rs[self::CONTACT_INFO][self::CONTACT_INFO_BLACKBERRY]);
        unset($rs[self::CONTACT_INFO][self::CONTACT_INFO_TWITTER]);
        unset($rs[self::CONTACT_INFO][self::CONTACT_INFO_SKIPE]);
        
        if ($this->content[self::FOREIGN_TEXT]) {
            $rs[self::FOREIGN_TEXT] = $this->content[self::FOREIGN_TEXT];
            $rs[self::FOREIGN_RTL] = $this->content[self::FOREIGN_RTL];
        }
        
        if (\count($this->content[self::REGIONS])) {
            $rs[self::REGIONS] = $this->content[self::REGIONS];
        }
        
        if ($this->content[self::PICTURES]) {
            $rs[self::PICTURES] = $this->content[self::PICTURES];
        }
        
        if ($this->content[self::LOCATION]) {
            $rs[self::LOCATION] = $this->content[self::LOCATION];
        }
        
        if ($this->content[self::LOCATION_ARABIC]) {
            $rs[self::LOCATION_ARABIC] = $this->content[self::LOCATION_ARABIC];
        }
        
        if ($this->content[self::LOCATION_ENGLISH]) {
            $rs[self::LOCATION_ENGLISH] = $this->content[self::LOCATION_ENGLISH];
        }

        if ($this->content[self::STATE]>0) {
            $rs[self::ATTRIBUTES] = $this->content[self::ATTRIBUTES]??[];
        }
        
        return $rs;
    }
    
    
    private function getAsVersion2() : array {
        
    }
    
    
    public function getForEditor() : array {
        $rs = [
            self::ID                => $this->getID(),
            self::UID               => $this->getUID(),
            self::ROOT_ID           => $this->content[self::ROOT_ID],
            self::SECTION_ID        => $this->content[self::SECTION_ID],
            self::PURPOSE_ID        => $this->content[self::PURPOSE_ID],
            self::STATE             => $this->content[self::STATE],
            
            self::NATIVE_TEXT       => $this->content[self::NATIVE_TEXT],
            self::NATIVE_RTL        => $this->content[self::NATIVE_RTL],
            self::FOREIGN_TEXT      => $this->content[self::FOREIGN_TEXT],
            self::FOREIGN_RTL       => $this->content[self::FOREIGN_RTL],
            
            self::CONTACT_INFO      => $this->content[self::CONTACT_INFO],
            self::PICTURES          => $this->content[self::PICTURES],
            self::REGIONS           => $this->content[self::REGIONS],

            self::BUDGET            => $this->content[self::BUDGET],
            self::USER_LEVEL        => $this->content[self::USER_LEVEL],

            self::UI_LANGUAGE       => $this->content[self::UI_LANGUAGE],
            
            self::LATITUDE          => $this->content[self::LATITUDE],
            self::LONGITUDE         => $this->content[self::LONGITUDE],
            self::LOCATION          => $this->content[self::LOCATION],
            self::LOCATION_ARABIC   => $this->content[self::LOCATION_ARABIC],
            self::LOCATION_ENGLISH  => $this->content[self::LOCATION_ENGLISH],

            self::APP_NAME          => $this->content[self::APP_NAME],
            self::APP_VERSION       => $this->content[self::APP_VERSION],
            self::VERSION           => $this->content[self::VERSION]                
        ];
        
        return $rs;
    }

}
