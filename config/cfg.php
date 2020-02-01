<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', get_cfg_var('mourjan.server_id')=='99'?1:0);

include_once dirname(__DIR__) . '/core/model/Singleton.php';

class Config extends \Core\Model\Singleton {
    protected $config;
        
    public $serverId;
    public $host;
    public $baseDir;
    public $cssDir;
    public $jsURL;
    public $baseURL;
    public $cssURL;
    public $imgURL;
    public $assetsURL;
    //public $jQueryURL;
    public $adImgURL;
    public $imgLibURL;
    public $modules;
    
    protected string $layoutDir;
    protected string $modelDir;
    protected string $libDir;
    protected string $dataTablesDir;
    
    public static function instance() : Config {
        return static::getInstance();
    }
    
    
    public function init(array $params) {
        $this->config = $params;
        
        $this->serverId = $this->config['server_id'];
        $this->host = $this->config['host'];
        $this->baseDir = $this->config['dir'];
        $this->cssDir = $this->config['dir_css'];
        $this->baseURL = $this->config['url_base'];
        $this->jsURL = $this->config['url_js'];
        $this->cssURL = $this->config['url_css'];
        $this->imgURL = $this->config['url_img'];
        $this->modules = $this->config['modules'];
        $this->assetsURL = $this->config['url_resources'];
        //$this->jQueryURL = $this->config['url_jquery'];
        $this->adImgURL = $this->config['url_ad_img'];
        $this->imgLibURL = $this->config['url_image_lib'];
        
        $this->adImgURL = 'https://c6.mourjan.com';
        //error_log($this->host);
        
        $this->libDir = $this->baseDir . '/core/lib/';
        $this->modelDir = $this->baseDir . "/core/model/";
        $this->layoutDir = $this->baseDir . "/core/layout/";
        $this->dataTablesDir = $this->baseDir . '/core/data/tables/';
    }
    
    
    public function setValue(string $key, $value) : void {
        $this->config[$key] = $value;    
    }
    
    
    public function get(string $key) {
        if (!isset($this->config[$key])) {
            error_log("Invalid config key {$key}");
        }
        return $this->config[$key];
    }
    
    
    public function enabledUsers() : bool {
        return ($this->config['enabled_users']);
    }
    
    
    public function enabledAds() : bool {
        return ($this->config['enabled_ads']);
    }
    
    
    public function enableAds() : void {
        $this->config['enabled_ads']=1;
    }
    
    
    public function disableAds() : void {
        $this->config['enabled_ads']=0;
    }
    
    
    public function isMaintenanceMode() : bool {
        return ($this->config['active_maintenance']!=0);
    }
    
    
    public function incModelFile(string $file) : Config {
        include_once $this->modelDir . $file . '.php';
        return $this;
    }
    
    
    public function incDataTableFile(string $file) : Config {
        include_once $this->dataTablesDir . $file . '.php';
        return $this;
    }
    
    
    public function incCoreFile(string $file) : Config {
        include_once dirname($this->modelDir) . $file . '.php';
        return $this;
    }
    
    
    public function incLibFile(string $file) : Config {
        include_once $this->libDir . $file . '.php';
        return $this;
    }
    
    
    public function incLayoutFile(string $file) : Config {
        include_once $this->layoutDir . $file . '.php';
        return $this;
    }
}


$aws = 'https://doxplxe8wce37.cloudfront.net';

$config=array(
    //Site parameters
    'site_production'       => 0,
    'site_domain'           => 'h1.mourjan.com',
    'slogan'                => '',// ' <span>faster than 82% of websites</span>',
    'slogan_en'             => '<span>The Best Classifieds Website</span>',
    'slogan_ar'             => '<span>أفضل موقع للإعلانات</span>',
    'site_key'              => 'mjn',
    'server_id'             => intval(get_cfg_var('mourjan.server_id')),
    
    'enabled_js_log'        => 0,
    //'enabled_ads'           => 1,
    'restricted_section_ads'=> [350],
    'restricted_email_domains'=>array(
        'crapmail.org',
        'zoho.com',
        'shitmail.org',
        'yandex.com',
        'instancemail.net'
    ),
    'smart_section_fix'=>array(
        //to root | section | purpose | name_en | name_ar
 	1 => [
            [1, 2, 0, "Furnished apartments", "شقق مفروشة"],
            [1, 3, 0, "Villas and houses", "فلل ومنازل"],
            [1, 122, 0, "Studios", "استوديو"]
        ],
        2 => [
            [1, 1, 0, "Apartments", "شقق"],
            [1, 3, 0, "Villas and houses", "فلل ومنازل"],
            [1, 122, 0, "Studios", "استوديو"]
        ],
        3 => [
            [1, 1, 0, "Apartments", "شقق"],
            [1, 2, 0, "Furnished apartments", "شقق مفروشة"],
            [1, 122, 0, "Studios", "استوديو"]
        ],
        122=> [
            [1, 1, 0, "Apartments", "شقق"],
            [1, 2, 0, "Furnished apartments", "شقق مفروشة"],
            [1, 3, 0, "Villas and houses", "فلل ومنازل"]
        ],
        20  =>  [//teaching to private lessons 
            [4,19,5,'private lessons','دروس خصوصية']
        ],
        45  =>  [//driver job to taxi services
            [4,260,5,'taxi services','خدمات تاكسي']
        ],
        431  =>  [//car rental to driver job
            [3,45,3,'driver vacancy','وظائف سائق']
        ],
        586  =>  [//car service to driver job
            [3,45,3,'driver vacancy','وظائف سائق']
        ]
    ),
    'smart_root_fix'=>array(
        //to root | section | purpose | name_en | name_ar
        2  =>  [//cars for rent to car rental
            [4,431,5,'car rental','تأجير سيارات']
        ]
    ),
    'enabled_sharing'       => 1,
    'enabled_facebook'      => 0,
    'enabled_disqus'        => 1,
    'enabled_users'         => 1,
    'enabled_post'          => 1,
    'enabled_ad_stats'      => 1,
    'enabled_charts'        => 1,
    'enabled_interactions'  => array(59801,220906),
    'max_upload'            => 2097152,
    
    
    'android_url_upload'            =>  'https://up.mourjan.com/upload/index.php',
    'android_url_web'               =>  'https://www.mourjan.com/',
    'android_url_img'               =>  'https://doxplxe8wce37.cloudfront.net/repos',
    'android_url_api'               =>  'https://www.mourjan.com/api/app.php',
    'android_url_node_ad_stage'     =>  'https://io.mourjan.com:1313',
    'android_email_support'         =>  'support@mourjan.com',
    'android_app_release'           =>  '1.1.8',
    'android_app_release_enforce'   =>  0,
    'android_enabled_banner_search' =>  1,
    'android_enabled_banner_detail' =>  1,
    'android_enabled_banner_search_native' =>  1,
    'android_enabled_banner_detail_native' =>  1,
    'android_enabled_banner_search_native_list' =>  1,
    'android_banner_search_native_list_first_idx' =>  7,
    'android_banner_search_native_list_gap' =>  14,
    'android_banner_search_native_list_freq' =>  8,
    'android_enabled_banner_exit'   =>  0,
    'android_enabled_banner_pending'=>  1,
    'android_enabled_banner_detail_inter'=>10,
    'android_url_img'               =>  [
        'LB'    =>  'https://doxplxe8wce37.cloudfront.net/repos',
        'AE'    =>  'https://doxplxe8wce37.cloudfront.net/repos',
        'BH'    =>  'https://doxplxe8wce37.cloudfront.net/repos',
        'SA'    =>  'https://doxplxe8wce37.cloudfront.net/repos',
        'EG'    =>  'https://doxplxe8wce37.cloudfront.net/repos',
        'US'    =>  'https://doxplxe8wce37.cloudfront.net/repos'
    ],
    
    'payfor_merchant_id'    =>  'AUCZNGGy',
    'payfor_access_code'    =>  'ou8rcz98spCiypVgz67U',
    'payfor_pass_phrase_out'=>  'ky9BWdcbDZqn2c',
    'payfor_pass_phrase_in' =>  'pWgawXBckxLbuf',
    'payfor_url'            =>  'https://checkout.payfort.com/FortAPI/paymentPage',
    'payfor_url_test'       =>  'https://sbcheckout.payfort.com/FortAPI/paymentPage',
    
    
    //Admin
    'admin_email'           => array('support@mourjan.com'),
    
    
    //oauth key
    'rpx_api_key'           => '9cd1c6a259ad6ec4c6b4460fda0a3f7553e8e1ad',
    'rpx_engage_pro'        => false,
    
    //Environment parameters
    'language'              => '',// '' is default system lang
    'charset'               => 'UTF-8',
    'locale'                => '',
    'uri_protocol'          => 'AUTO',
    'permitted_uri_chars'   => 'a-z 0-9~%.:_\-',
    'enable_query_strings'  => FALSE,
    'url_suffix'            => '',
    
    'gapp_oauth_client_id'  => '1017340605957-4pboe02jriltl0r74v9u6io0bdhrncpd.apps.googleusercontent.com',
    'gapp_client_secret'    => '0lOjpI5kfmqJvYEXGL9lBV5R',
    'gapp_api_key'          => 'AIzaSyBRv6rc-uUbx_pEeUki6g027FzwyZmVGgM',
    
    //youtube credentials
    'gapp_key_browser'      => 'AIzaSyClGjkP8kdPPU5FVyayTrj8I3uc9ry6p1Y',        
    'gapp_key_server'       => 'AIzaSyBV4Y9v8fGOpn4FK_CZ3Kpgp2QftOx9l7Q', 
    'yt_user'               => 'basselmourjan@gmail.com',
    'yt_pass'               => 'GQ71BUT2',
    'yt_dev_key'            => 'AI39si6l-SDNBuop-xW28phLINwESX-vVOVCMhX_zrWE8sIWAm-qMURpLiJASlGz7tX1iqt4xxSpC_tjCCRSw1iGws_yZHfnyw',
  
    //Memory Cache
    'memstore'              => ['tcp'=>FALSE, 'host'=>'127.0.0.1', 'port'=>6380,
                                'socket'=>'/dev/shm/redis.sock', 'db'=>0,
                                'serializer'=>Redis::SERIALIZER_PHP, 'prefix'=>'v1:'],

    //SMTP server parameters
    'smtp_server'           => 'smtp.gmail.com',
    'smtp_user'             => 'no-reply@mourjan.com',
    'smtp_contact'          => 'support@mourjan.com',
    'smtp_pass'             => 'GQ71BUT2',
    'smtp_port'             => 465,

    'notifier_mail'         => array(
                                'noreply@mourjan.com',
                                'no-reply@mourjan.com',
                                ),
    
    //search parameters
    'search_index'          => 'ad',
    'search_results_max'    => 1000,

    'sphinxql'		   => ['host'=>'p1.mourjan.com', 'port'=>8307, 'socket'=>'/var/run/manticore.sock'],
    
    //resourses parameters
    'host'                  => 'https://h1.mourjan.com',
    'dir'                   => '/var/www/mourjan',
    'dir_css'               => '/var/www/mourjan',
            
            
    'ttl_short'             => 3600,
    'ttl_medium'            => 14400,
    'ttl_long'              => 86400,
    'ttl_unlimited'         => 0,


    'url_uploader'          => 'https://up.mourjan.com',    
    'url_resources'         => $aws,
    'url_ad_img'            => $aws,
    'url_img'               => '/img/1.0.0',
    'url_js'                => '/js/1.0.0',
    'url_js_mobile'         => '/js/2.1.8d',
    'url_css'               => '/css/5.3.7',
    'url_css_mobile'        => '/css/5.2.8c',
    'url_css_mobile'        => '/css/5.2.8g',
    'url_jquery'            => '/jquery/3.1.0e/js/',
    'url_jquery_mobile'     => '/jquery/2.1.0/',
    'url_image_lib'         => '/lix/2.0.0',
    'url_highcharts'        => '/hc/3.0.9',
    
    'iso_countries'         => ['lb'=>1, 'ae'=>2, 'bh'=>3, 'sa'=>4, 'eg'=>5,
                                'sy'=>6, 'kw'=>7, 'jo'=>8, 'qa'=>9, 'sd'=>10,
                                'tn'=>11, 'ye'=>12, 'dz'=>15, 'iq'=>106,
                                'ly'=>122, 'ma'=>145, 'om'=>161],

    'modules'               => [
                                'admin'                 => array('Admin',0),
                                'ajax-pay'              => array('Bin',0),
                                'ajax-mobile'           => array('Bin',0),
                                'ajax-keyword'          => array('Bin',0),
                                'ajax-propspace'        => array('Bin',0),
                                'detail'                => array('Detail',1),
                                'index'                 => array('Home',1),
                                'home'                  => array('Panel',1),
                                'search'                => array('Search',1),
                                'contact'               => array('Contact',1),
                                'terms'                 => array('Doc',1),
                                'privacy'               => array('Doc',1),
                                'about'                 => array('Doc',1),
                                'blocked'               => array('Blocked',1),
                                'publication-prices'    => array('Doc',1),
                                'advertise'             => array('Doc',1),
                                'gold'                  => array('Doc',1),
                                'buyu'                  => array('Doc',0),
                                'buy'                   => array('Doc',0),
                                'checkout'              => array('Checkout',0),
                                'premium'               => array('Doc',1),
                                'guide'                 => array('Doc',1),
                                'iguide'                => array('Doc',1),
                                'statement'             => array('Balance',0),
                                'favorites'             => array('Search',0),
                                'watchlist'             => array('Search',0),
                                'post'                  => array('PostAd',0),
                                'myads'                 => array('MyAds',0),
                                'oauth'                 => array('OAuth',0),
                                'account'               => array('Account',0),
                                'ajax-help'             => array('Bin',0),  
                                'ajax-home'             => array('Bin',0),  
                                'ajax-mute'             => array('Bin',0),                              
                                'ajax-changepu'         => array('Bin',0),
                                'ajax-getad'            => array('Bin',1),
                                'ajax-delshout'         => array('Bin',0),
                                'page'                  => array('Profile',0),
                                'ajax-user-type'        => array('Bin',0),
                                'ajax-sorting'          => array('Bin',0),
                                'ajax-mpre'             => array('Bin',0),
                                'ajax-spre'             => array('Bin',0),
                                'ajax-balance'          => array('Bin',0),
                                'ajax-screen'           => array('Bin',0),
                                'ajax-pi'               => array('Bin',0),
                                'ajax-code-list'        => array('Bin',0),
                                'ajax-post-se'          => array('Bin',0),
                                'ajax-ads'              => array('Bin',0),
                                'ajax-page'             => array('Bin',0),
                                'ajax-page-loc'         => array('Bin',0),
                                'ajax-stat'             => array('Bin',0),
                                'ajax-logo'             => array('Bin',0),
                                'ajax-gdel'             => array('Bin',0),
                                'ajax-banner'           => array('Bin',0),
                                'ajax-bdel'             => array('Bin',0),
                                'ajax-section-update'   => array('Bin',0),
                                'ajax-section-delete'   => array('Bin',0),
                                'ajax-country-cities'   => array('Bin',0),
                                'ajax-cc-remove'        => array('Bin',0),
                                'ajax-cc-add'           => array('Bin',0),
                                'ajax-account'          => array('Bin',0),
                                'ajax-menu'             => array('Bin',1),
                                'ajax-report'           => array('Bin',0),
                                'ajax-contact'          => array('Bin',0),
                                'ajax-support'          => array('Bin',0),
                                'ajax-favorite'         => array('Bin',0),
                                'ajax-location'         => array('Bin',0),
                                'ajax-sections'         => array('Bin',0),
                                'ajax-upload'           => array('Bin',0),
                                'ajax-idel'             => array('Bin',0),
                                'ajax-ahold'            => array('Bin',0),
                                'ajax-ifav'             => array('Bin',0),
                                'ajax-adel'             => array('Bin',0),
                                'ajax-ublock'           => array('Bin',0),
                                'ajax-arenew'           => array('Bin',0),
                                'ajax-adsave'           => array('Bin',0),
                                'ajax-pending'          => array('Bin',0),
                                'ajax-approve'          => array('Bin',0),
                                'ajax-reject'           => array('Bin',0),
                                'ajax-watch'            => array('Bin',0),
                                'ajax-remove-watch'     => array('Bin',0),
                                'ajax-watch-update'     => array('Bin',0),
                                'video-upload'          => array('Bin',0),
                                'ajax-video-upload'     => array('Bin',0),
                                'video-upload-ready'    => array('Bin',0),
                                'ajax-upload-ready'     => array('Bin',0),
                                'video-upload-check'    => array('Bin',0),
                                'ajax-upload-check'     => array('Bin',0),
                                'video-delete'          => array('Bin',0),
                                'ajax-video-delete'     => array('Bin',0),
                                'video-link'            => array('Bin',0),
                                'ajax-video-link'       => array('Bin',0),
                                'ajax-js-error'         => array('Bin',0),
                                'ajax-ga'               => array('Bin',0),
                                'ajax-register'         => array('Bin',0),
                                'ajax-password'         => array('Bin',0),
                                'ajax-preset'           => array('Bin',0),
                                'ajax-prog'             => array('Bin',0),
                                'ajax-text'             => array('Bin',0),
                                'cache'                 => array('GenModules',0),
                                'notfound'              => array('NotFound',1),
                                'suspended'             => array('Blocked',1),
                                'invalid'               => array('NotFound',1),
                                'redirect'              => array('Redirect',0),
                                'signin'                => array('Signin',0),
                                'signup'                => array('Register',0),
                                'password'              => array('Register',0),
                                'welcome'               => array('Register',0),
                                'homescreen'            => array('Homescreen',1),
                                'maintenance'           => array('NotFound',1),
                                'held'                  => array('Blocked',1),
                                'ajax-ususpend'         => array('Bin',0),
                                'ajax-close-banner'     => array('Bin',0),
                                'install'               => array('Homescreen',0),
                                'ajax-ilogo'            => array('Bin',0),
                                'ajax-ipage'            => array('Bin',0)
                            ],
    
    'blocked_agents'    => array(
                                '-'             => 1,
                                'Mozilla/4.0 (compatible; MSIE 5.0; Windows NT; DigExt; DTS Agent'  => 1,
                                'OKArabia search engine/Nutch-1.4' => 1,
                                'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98)' => 1
                            ),
);

$config['url_base']         = $config['host'];
$config['url_bin']          = $config['url_base'].'/bin';
$config['url_upload']       = $config['url_bin'].'/uploadLogo.php';

Config::instance()->init($config);
include_once dirname(__DIR__) . '/core/model/Db.php';
$globalSettings = \Core\Model\DB::getCacheStorage($config)->get("global-settings");
if ($globalSettings!==FALSE) {
    //$globalSettings['db_host']='fb.mourjan.com';
    foreach ($globalSettings as $key => $value) {
        $config[$key] = $value;        
    }
}

$config['dir_css']              = '/var/www/mourjan';
$config['url_resources']        = 'https://h1.mourjan.com';
$config['url_js']               = 'https://h1.mourjan.com/web/js/1.0.0';
$config['url_css']              = 'https://h1.mourjan.com/web/css/5.4.3';
//$config['url_jquery']           = 'https://h1.mourjan.com/web/jquery/3.1.0/js/';
//$config['url_jquery_mobile']    = 'https://h1.mourjan.com/web/jquery/4.0.0/js/';
$config['url_css_mobile']       = 'https://h1.mourjan.com/web/css/1.0.2';
$config['url_js_mobile']        = 'https://h1.mourjan.com/web/js/2.0.0';
$config['url_image_lib']        = 'https://h1.mourjan.com/web/lix/2.0.0';
$config['url_img']              = 'https://h1.mourjan.com/img/1.0.3';
$config['url_uploader']         = 'https://h1.mourjan.com';

$config['server_id'] = get_cfg_var('mourjan.server_id');
$config['active_maintenance']=0;


Config::instance()->init($config);



/*
function layout_file(string $file_name) {
    global $config;
    include_once $config['dir'] . '/core/layout/' . $file_name;
}

function model_file(string $file_name) {
    global $config;
    include_once $config['dir'] . '/core/model/' . $file_name;
}

function libFile(string $file_name) {    
    global $config;
    if (!isset($config['lib-dir'])) {
        $config['lib-dir'] = $config['dir'] . '/core/lib/';
    }
    include_once $config['lib-dir'] . $file_name;
}
*/