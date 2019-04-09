namespace Mourjan;

class Config extends Singleton {
	protected config;

	public serverId;
    public host;
    public baseDir;
    public cssDir;
    public jsURL;
    public baseURL;
    public cssURL;
    public imgURL;
    public assetsURL;
    //public jQueryURL; // deprectated
    public adImgURL;
    public imgLibURL;
    public modules;

    protected FbURI { get };
    protected layoutDir;
    protected modelDir;
    protected libDir;

	public static function instance() -> <Config> {
		return static::getInstance();
	}


	protected function __construct() -> void {
		let this->config = [];
	}


	public function init(array parameters) -> void {
		error_log( "Config.init " . get_called_class() );
		var key, value;
		for key, value in parameters {
			this->setValue(key, value);
			let this->config[key] = value;
		}

		error_log( json_encode(this->config));
		
		let this->serverId 	= this->get("server_id");
		let this->baseDir	= this->{"dir"};
        let this->host		= this->get("host");
        let this->cssDir	= this->get("dir_css");
        let this->baseURL	= this->get("url_base");
        let this->jsURL		= this->get("url_js");
        let this->cssURL 	= this->get("url_css");
        let this->imgURL 	= this->get("url_img");
        let this->modules 	= this->get("modules");
        let this->assetsURL	= this->get("url_resources");
        let this->adImgURL	= this->get("url_ad_img");
        let this->imgLibURL = this->get("url_image_lib");
        
        let this->adImgURL = "https://c6.mourjan.com";

        let this->libDir = this->config["dir"] . "/core/lib/";
        let this->modelDir = this->get("dir") . "/core/model/";
        let this->layoutDir = this->get("dir") . "/core/layout/";
        if ( this->{"db_host"} ) {
        	let this->FbURI = "firebird:dbname=" . this->get("db_host") . ":" . this->get("db_name") . ";charset=UTF8";
        	error_log(this->FbURI);
        }
        error_log( " dir " . this->{"dir"} );
	}


	public function setValue(string index, var value) -> void {
        let this->{index} = value;
    }
    
    
    public function get(string key) -> var {
        return this->{key};
    }
    
    
    public function enabledUsers() -> bool {
        return isset(this->{"enabled_users"}) && (this->get("enabled_users"));
    }
    
    
    public function enabledAds() -> bool {
        return (this->get("enabled_ads"));
    }
    
    
    public function enableAds() -> void {
    	this->setValue("enabled_ads", 1);
    }


    public function disableAds() -> void {
    	this->setValue("enabled_ads", 0);
    }
    
    
    public function isMaintenanceMode() -> bool {
        return (this->get("active_maintenance")!=0);
    }
    
    
    public function modelFile(string file) -> string {
        return this->modelDir . file . ".php";
    }
    

    public function libFile(string file) -> string {
        return this->libDir . file . ".php";
    }
    

    public function layoutFile(string file) -> string {
        return this->layoutDir . file . ".php";
    }

}