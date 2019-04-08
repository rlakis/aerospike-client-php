namespace Utils;

class Content {
    const VERSION_NUMBER        	= 3;    
    const ID                    	= "id";
    const STATE                 	= "state";
    const ROOT_ID               	= "ro";
    const SECTION_ID            	= "se";
    const PURPOSE_ID            	= "pu";
    const APP_NAME              	= "app";
    const APP_VERSION           	= "app_v";
    const USER_AGENT            	= "agent";
    const NATIVE_RTL            	= "rtl";
    const FOREIGN_RTL           	= "altRtl";
    const ATTRIBUTES            	= "attrs";
    const ATTR_NATIVE           	= "ar";
    const ATTR_FOREIGN          	= "en";
    const ATTR_GEO_KEYS         	= "geokeys";
    const ATTR_LOCALES          	= "locales";
    const ATTR_LOCALITY         	= "locality";
    const ATTR_LOCALITY_CITIES  	= "cities";
    const ATTR_LOCALITY_ID      	= "id";
    const ATTR_PHONES           	= "phones";
    const ATTR_PHONES_NUMBERS   	= "n";
    const ATTR_PHONES_TYPES     	= "t";
    const ATTR_PRICE            	= "price";
    const ATTR_SPACE            	= "space";
    const ATTR_ROOMS            	= "rooms";
    const BUDGET                	= "budget";
    
    const CONTACT_INFO          	= "cui";
    const CONTACT_INFO_BLACKBERRY 	= "b";		// deprecated
    const CONTACT_INFO_EMAIL    	= "e";
    const CONTACT_INFO_PHONE    	= "p";
    const CONTACT_INFO_PHONE_COUNTRY_CODE   = "c";
    const CONTACT_INFO_PHONE_COUNTRY_ISO    = "i";
    const CONTACT_INFO_PHONE_RAW_NUMBER     = "r";
    const CONTACT_INFO_PHONE_TYPE           = "t";
    const CONTACT_INFO_PHONE_INTERNATIONAL  = "v";
    const CONTACT_INFO_PHONE_X              = "x"; // deprecated
    const CONTACT_INFO_SKIPE                = "s"; // deprecated
    const CONTACT_INFO_TWITTER              = "t"; // deprecated
    
    const CONTACT_TIME          	= "cut"; // deprecated
    const CONTACT_TIME_AFTER    	= "a"; // deprecated
    const CONTACT_TIME_BEFORE   	= "b"; // deprecated
    const CONTACT_TIME_HOUR     	= "t"; // deprecated
    
    const UI_CONTROL            	= "extra"; // deprecated
    const UI_CONTROL_MAP        	= "m"; // deprecated
    const UI_CONTROL_PICTURES   	= "p"; // deprecated
    const UI_CONTROL_VIDEO      	= "v"; // deprecated
    const UI_CONTROL_TRANSLATION	= "t"; // deprecated
    const UI_LANGUAGE           	= "hl";
    const IP_ADDRESS            	= "ip";
    const IP_SCORE              	= "ipfs";
    const NATIVE_TEXT           	= "other";
    const FOREIGN_TEXT          	= "altother";
    const LATITUDE              	= "lat";    // deprecated
    const LONGITUDE             	= "lon";    // deprecated
    const LOCATION              	= "loc";
    const LOCATION_ARABIC       	= "loc_ar";
    const LOCATION_ENGLISH      	= "loc_en";
    const MEDIA                 	= "media"; // deprecated
    const PICTURE_INDEX         	= "pix_idx";   
    const DEFAULT_PICTURE       	= "pix_def";    // deprecated
    const PICTURES              	= "pics";
    const REGIONS               	= "pubTo";
    const UID                   	= "user";
    const USER_LEVEL            	= "userLvl";
    const USER_LOCATION         	= "userLOC";
    const QUALIFIED             	= "qualified";
    const VERSION               	= "version";

    private content { get };
    private profile { get };
 
 	private countryId { get };
 	private cityId { get }; 

    public function __construct() {

    	let this->content = [
    			Content::ID: 				0,
    			Content::UID: 				0,
    			Content::STATE: 			0,
    			Content::ROOT_ID:           0,
            	Content::SECTION_ID:		0,
            	Content::PURPOSE_ID:		0,
            	Content::APP_NAME:			"",
            	Content::APP_VERSION:		"",
            	Content::VERSION:			Content::VERSION_NUMBER,
            	Content::USER_AGENT:		"",
            	Content::IP_ADDRESS:		"",
            	Content::IP_SCORE:			0.0,                                    
            	Content::BUDGET:			0,
            	Content::CONTACT_INFO:		[
            		Content::CONTACT_INFO_PHONE:		[], 
            		Content::CONTACT_INFO_EMAIL:		"", 
            		Content::CONTACT_INFO_BLACKBERRY:	"", 
            		Content::CONTACT_INFO_SKIPE:		"", 
            		Content::CONTACT_INFO_TWITTER:		""
            	],
            	Content::CONTACT_TIME:		[Content::CONTACT_TIME_BEFORE: 6, Content::CONTACT_TIME_AFTER: 24, Content::CONTACT_TIME_HOUR: 0],
            	Content::UI_CONTROL: 		[
            		Content::UI_CONTROL_MAP:			2, 
            		Content::UI_CONTROL_PICTURES:		2, 
            		Content::UI_CONTROL_TRANSLATION:	2, 
            		Content::UI_CONTROL_VIDEO:			2
            	],
            	Content::UI_LANGUAGE:		"ar",
            	Content::NATIVE_TEXT:		"",
            	Content::NATIVE_RTL:		0,
            	Content::FOREIGN_TEXT:		"",
            	Content::FOREIGN_RTL:		0,
            	Content::MEDIA:				0,
            	Content::DEFAULT_PICTURE:	0,
            	Content::PICTURES:			[],
            	Content::REGIONS:			[],
            	Content::LATITUDE:			0.0,
            	Content::LONGITUDE:			0.0,
            	Content::LOCATION:			"",
            	Content::LOCATION_ARABIC:	"",
            	Content::LOCATION_ENGLISH:	"",
            	Content::USER_LEVEL:		0,
            	Content::USER_LOCATION:		"",
            	Content::ATTRIBUTES:		[
                    Content::ATTR_NATIVE:	"",
                    Content::ATTR_FOREIGN:	"",
                    Content::ATTR_GEO_KEYS:	[],
                    Content::ATTR_LOCALES:	[],
                    Content::ATTR_LOCALITY:	[Content::ATTR_LOCALITY_ID: 0, Content::ATTR_LOCALITY_CITIES: []],
                    Content::ATTR_PHONES:	[]            
                ],
            	Content::QUALIFIED:			false
    	];
    }


	public function setID(int id) -> <Content> {
        let this->content[Content::ID]=id;
        return this;
    }


    public function getID() -> int {
        return this->content[Content::ID];
    }
   
            
    public function setUID(int uid) -> <Content> {
        if (uid!==this->content[self::UID]) {
            let this->profile=null;
        }
        let this->content[self::UID]=uid;
        return this;
    }
    

    public function getUID() -> int {
        return this->content[self::UID];
    }


    /*
    public function getProfile() : <MCUser> {
        if (this->profile===null) {
            let this->profile = new \MCUser(this->getUID());
        }
        return this->profile;
    }
    */

      
	public function setState(int state) -> <Content> {
        let this->content[self::STATE]=state;
        return this;
    }
    
    
    public function setCountryId( int kCountryId ) -> <Content> {
    	let this->countryId = kCountryId;
    	return this;
    }


    public function setCityId( int kCityId ) -> <Content> {
    	let this->cityId = kCityId;
    	return this;
    }


    public function getSectionID() -> int {
        return this->content[self::SECTION_ID];
    }
    
    
    public function setSectionID(int id) -> <Content> {

    	let this->content[Content::ROOT_ID] = \Utils\Dictionary::instance()->getSectionRootId( id );

        if (this->content[Content::ROOT_ID]>0) {
            let this->content[Content::SECTION_ID]=id;
        }
        else {
            let this->content[Content::SECTION_ID]=0;
        }               
        return this;
    }
    

    public function getPurposeID() -> int {
        return this->content[self::PURPOSE_ID];
    }

    
    public function setPurposeID(int id) -> <Content> {
        let this->content[self::PURPOSE_ID]=id;
        return this;
    }


    public function setApp(string name, string version) -> <Content> {
        let this->content[self::APP_NAME]=name;
        let this->content[self::APP_VERSION]=version;
        return this;
    }
    
    
    public function setVersion(int version) -> <Content> {
        if (version!==this->content[self::VERSION]) {
            let this->content[self::VERSION]=version;
        }
        return this;
    }
    
    
    public function setUserAgent(string user_agent) -> <Content> {
        let this->content[self::USER_AGENT]=user_agent;
        return this;
    }
    
    
    public function setBudget(int budget) -> <Content> {
        //if ($this->getProfile()->getBalance()<=0) {
        //    $budget=0;
        //}
        let this->content[self::BUDGET] = budget;
        return this;
    }
    
    
    public function getIpAddress() -> string {
        return this->content[self::IP_ADDRESS];
    }
    
    
    public function setIpAddress(string ip) -> <Content> {
        let this->content[self::IP_ADDRESS]=ip;
        return this;
    }
    
    
    public function setIpScore(float score) -> <Content> {
    	var ip_score=score;
        let this->content[self::IP_SCORE] = ip_score;
        return this;
    }
    
    
    public function addPhone(int country_callkey, string country_iso_code, string raw_number, int number_type, string international_number) -> <Content> {
        let this->content[self::CONTACT_INFO][self::CONTACT_INFO_PHONE][]=[
            self::CONTACT_INFO_PHONE_COUNTRY_CODE:	country_callkey,
            self::CONTACT_INFO_PHONE_COUNTRY_ISO:	country_iso_code,
            self::CONTACT_INFO_PHONE_RAW_NUMBER:	raw_number,
            self::CONTACT_INFO_PHONE_TYPE:			number_type,
            self::CONTACT_INFO_PHONE_INTERNATIONAL:	international_number
        ];
        return this;
    }
    
    
    public function setEmail(string email) -> <Content> {
        let this->content[self::CONTACT_INFO][self::CONTACT_INFO_EMAIL]=email;
        return this;
    }
    
    
    public function setUserLanguage(string language) -> <Content> {
        let this->content[self::UI_LANGUAGE]= \in_array(language, ["ar","en"])?language:"ar";
        return this;
    }
    
    
    public function setUserLevel(int level) -> <Content> {
        let this->content[self::USER_LEVEL] = level;
        return this;
    }
        
    
    public function setUserLocation() -> <Content> {
        //let this->content[self::USER_LOCATION] = \IPQuality::ipLocation($this->getIpAddress());
        return this;
    }


    private function rtl(string text) -> int {
        var success = \preg_match_all("/\p{Arabic}/u", text);
        var spaces = \preg_match_all("/\s/u", $text);
        if (success/(\mb_strlen(text)-spaces)>=0.5) {
            return 1;
        }
        return 0;
    }
    
    
    public function setNativeText(string text) -> <Content> {
        let this->content[self::NATIVE_TEXT] = text->trim();
        let this->content[self::NATIVE_RTL] = this->rtl(this->content[self::NATIVE_TEXT]);
        return this;
    }
    
    
    public function getNativeRTL() -> int {
        return this->content[self::NATIVE_RTL];
    }
    
    
    public function setForeignText(string text) -> <Content> {
        let this->content[self::FOREIGN_TEXT]= text->trim();
        let this->content[self::FOREIGN_RTL] = this->rtl(this->content[self::FOREIGN_TEXT]);
        return this;
    }
    
    
    public function setPictures(array pictures) -> <Content> {
        let this->content[self::PICTURES]=pictures;
        let this->content[self::MEDIA]=\count(pictures)>0?1:0;
        return this;
    }
    
    
    public function addRegion(int region) -> <Content> {
        if (!\in_array(region, this->content[self::REGIONS])) {
            let this->content[self::REGIONS][]=region;
        }
        return this;
    }
    
    
    public function addRegions(array regions) -> <Content> {
    	var region;
    	for region in regions->keys() {
    		if (! in_array(region, this->content[self::REGIONS])) {
    			let this->content[self::REGIONS][]=region;
    		}
    	}
        return this;
    }
    
    
    public function setCoordinate(float lat, float lng) -> <Content> {
    	var longitude=lng;
    	var latitude=lat;
        let this->content[Content::LATITUDE]=latitude;  
        let this->content[Content::LONGITUDE]=longitude;
        return this;        
    }
    

    public function setLocation(string location) -> <Content> {
        if (this->content[self::APP_NAME]==="web") {
            let this->content[self::LOCATION]=location;
        }
        else {        
            if (this->rtl(location)) {
                let this->content[self::LOCATION_ARABIC]=location;            
            }
            else {
                let this->content[self::LOCATION_ENGLISH]=location;
            }
        }
        return this;
    }

    
    public function setQualified(bool value) -> <Content> {
        let this->content[self::QUALIFIED] = value;
        return this;
    }
    

    public function getClientVersion() -> string {
    	return substr(this->content[self::APP_NAME], 0, 1) . "-" . this->content[self::APP_VERSION];
    }


    public function getData() -> array {
        unset(this->content[self::ATTRIBUTES]);        
        return this->content;
    }
    
    
    public function toJsonString(int options) -> string {
        unset(this->content[self::ATTRIBUTES]);        
        return \json_encode(this->content, options);        
    }


	public function getAsVersion(int version) -> array {
        switch (version) {
            case 2: return this->getAsVersion2();
            case 3: return this->getAsVersion3();
        }
        return [];
    }


    private function getAsVersion3() -> array {
        var rs=[
            self::CONTACT_INFO:		this->content[self::CONTACT_INFO],            
            self::USER_LEVEL:		this->content[self::USER_LEVEL],
            self::USER_LOCATION:	this->content[self::USER_LOCATION],
            self::USER_AGENT:		this->content[self::USER_AGENT],
            self::UI_LANGUAGE:		this->content[self::UI_LANGUAGE],
            self::IP_ADDRESS:		this->getIpAddress(),
            self::IP_SCORE:			this->content[self::IP_SCORE],
            self::QUALIFIED:		this->content[self::QUALIFIED]?1:0,
            self::BUDGET:			this->content[self::BUDGET],
            self::NATIVE_TEXT:		this->content[self::NATIVE_TEXT],
            self::APP_NAME:			this->getClientVersion(),
            self::VERSION:			3
            
        ];

        unset(rs[self::CONTACT_INFO][self::CONTACT_INFO_BLACKBERRY]);
        unset(rs[self::CONTACT_INFO][self::CONTACT_INFO_TWITTER]);
        unset(rs[self::CONTACT_INFO][self::CONTACT_INFO_SKIPE]);
        
        if (this->content[self::FOREIGN_TEXT]) {
            let rs[self::FOREIGN_TEXT] = this->content[self::FOREIGN_TEXT];
            let rs[self::FOREIGN_RTL] = this->content[self::FOREIGN_RTL];
        }
        
        if (\count(this->content[self::REGIONS])) {
            let rs[self::REGIONS] = this->content[self::REGIONS];
        }
        
        if (this->content[self::PICTURES]) {
            let rs[self::PICTURES] = this->content[self::PICTURES];
        }
        
        if (this->content[self::LOCATION]) {
            let rs[self::LOCATION] = this->content[self::LOCATION];
        }
        
        if (this->content[self::LOCATION_ARABIC]) {
            let rs[self::LOCATION_ARABIC] = this->content[self::LOCATION_ARABIC];
        }
        
        if (this->content[self::LOCATION_ENGLISH]) {
            let rs[self::LOCATION_ENGLISH] = this->content[self::LOCATION_ENGLISH];
        }

        if (this->content[self::STATE]>0) {
            let rs[self::ATTRIBUTES] = this->content[self::ATTRIBUTES];
        }
        
        return rs;
    }
    
    
    private function getAsVersion2() -> array {
        return [];
    }



    public function save(int state=0, <\PDO> pdo) -> void {
    	//var_dump(db);
    	var q;
    	if (this->getID()>0) {
            let q = "UPDATE ad_user SET /* Utils/Content *\ ";
            let q.= "content=?, purpose_id=?, section_id=?, rtl=?, country_id=?, city_id=?, latitude=?, longitude=?, state=?, media=? ";
            let q.= "where id=? returning state";
        }
        else {
            let q = "INSERT INTO ad_user (content, purpose_id, section_id, rtl, country_id, city_id, latitude, longitude, state, media, web_user_id) ";
            let q.= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) returning ID";
        }
        echo q, "\n";
        var st=pdo->prepare(q);
        //var_dump(st);
       
        st->bindValue(1, \json_encode(this->getAsVersion3()), \PDO::PARAM_STR);
        st->bindValue(2, this->getPurposeID(), \PDO::PARAM_INT);
        st->bindValue(3, this->getSectionID(), \PDO::PARAM_INT);
        st->bindValue(4, this->getNativeRTL(), \PDO::PARAM_INT);
        //$st->bindValue(5, $this->pending['post']['cn']);
        //$st->bindValue(6, $this->pending['post']['c']);
        st->bindValue(7, this->content[self::LATITUDE]);
        st->bindValue(8, this->content[self::LONGITUDE]);
        st->bindValue(9, this->content[self::STATE], \PDO::PARAM_INT);        
        st->bindValue(10, (\count(this->content[self::PICTURES])>0?1:0), \PDO::PARAM_INT);
        st->bindValue(11, this->getID()>0 ? this->getID() : this->getUID(), \PDO::PARAM_INT);
        if (st->execute()) {
        	var result = st->$fetch(\PDO::FETCH_ASSOC);
        	if (result !== FALSE) {
        		if (this->getID()>0) {
                    this->setState(result["STATE"]);
                }
                else {
                    this->setID(result["ID"]);
                }
        	}
        	st->close();
        	pdo->commit();
        }
        else {
        	st->close();
        	pdo->rollback();
        }
        var_dump(st);
    }

}
