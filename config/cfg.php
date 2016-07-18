<?php
ini_set('error_reporting', E_ALL);

if (get_cfg_var('mourjan.server_id')=='99')
{
	ini_set('display_errors', 1);
} 
else 
{
	ini_set('display_errors', 0);
}

//ini_set('magic_quotes_gpc', 0);
//ini_set('magic_quotes_runtime', 0);
//ini_set('magic_quotes_sybase', 1);

include_once get_cfg_var('mourjan.path') . '/core/model/Db.php';


class Configuration 
{
    const site_domain   = 'www.mourjan.com';
    const site_key      = 'mrj';
    const slogan        = '';// ' <span>faster than 82% of websites</span>',
    const slogan_en     = '<span>The Best Classifieds Website</span>';
    const slogan_ar     = '<span>أفضل موقع للإعلانات</span>';

    public $dir; // base directory
    public $serverId;
    public $baseUrl;
    public $binUrl;

    private $android;
    private $ios;
    
    private $restrictedEMailPatterns = null;
    
    public function __construct() 
    {
        $this->serverId = intval(get_cfg_var('mourjan.server_id'));
        $this->dir = get_cfg_var('mourjan.path');
        $this->baseUrl = $this->isProductionServer() ? 'https://www.mourjan.com' : 'https://dv.mourjan.com';
        $this->binUrl = $this->baseUrl . '/bin';
    }
    
    
    public function isProductionServer()
    {
        return ($this->serverId>1 && $this->serverId<99);
    }
    
    
}


$aws = 'https://doxplxe8wce37.cloudfront.net';

$config=array(
    //Site parameters
    'site_domain'           => 'www.mourjan.com',
    'slogan'                => '',// ' <span>faster than 82% of websites</span>',
    'slogan_en'             => '<span>The Best Classifieds Website</span>',
    'slogan_ar'             => '<span>أفضل موقع للإعلانات</span>',
    'site_key'              => 'mjn',
    'server_id'             => intval(get_cfg_var('mourjan.server_id')),
    'site_production'       => 0,
    'enabled_js_log'        => 0,
    //'enabled_ads'           => 1,
    'restricted_section_ads'=> array(
        350
    ),
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
    'android_enabled_banner_exit'   =>  0,
    'android_enabled_banner_pending'=>  1,
    'android_enabled_banner_detail_inter'=>10,
    
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
    
    //youtube credentials
    'gapp_key_browser'       =>  'AIzaSyClGjkP8kdPPU5FVyayTrj8I3uc9ry6p1Y',        
    'gapp_key_server'       =>  'AIzaSyBV4Y9v8fGOpn4FK_CZ3Kpgp2QftOx9l7Q', 
    'yt_user'               =>  'basselmourjan@gmail.com',
    'yt_pass'               =>  'GQ71BUT2',
    'yt_dev_key'            =>  'AI39si6l-SDNBuop-xW28phLINwESX-vVOVCMhX_zrWE8sIWAm-qMURpLiJASlGz7tX1iqt4xxSpC_tjCCRSw1iGws_yZHfnyw',
  
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

    'notifier_mail'         =>  array(
                                'noreply@mourjan.com',
                                'no-reply@mourjan.com',
                                ),
    
    //search parameters
    'search_index'          => 'ad',
    'search_results_max'    => 1000,

    'sphinxql'		    => array('host'=>'p1.mourjan.com', 'port'=>9307, 'socket'=>'/var/run/mourjanQL'),
    
    //resourses parameters
    'host'                  => 'https://dv.mourjan.com',
    'dir'                   => '/home/www/mourjan',
            
            
    'ttl_short'             => 3600,
    'ttl_medium'            => 14400,
    'ttl_long'              => 86400,
    'ttl_unlimited'         => 0,


	'url_uploader'         => 'https://up.mourjan.com',    
	'url_resources'         => $aws,
	'url_ad_img'            => $aws,
    'url_img'               => $aws.'/img/1.0.0',
    'url_js'                => 'https://dv.mourjan.com/web/js/1.0.0',
    'url_js_mobile'         => $aws.'/js/2.1.8d',
    'url_css'               => $aws.'/css/5.3.7',
    'url_css'               => 'https://dv.mourjan.com/web/css/1.0.0',
    'url_css_mobile'        => $aws.'/css/5.2.8c',
    'url_jquery'            => $aws.'/jquery/1.10.2.3/js/',
    'url_jquery'            => 'https://dv.mourjan.com/web/jquery/1.10.2.2/js/',
    'url_jquery_mobile'     => $aws.'/jquery/2.1.0/',
    'url_image_lib'         => $aws.'/lix/2.0.0',
    'url_highcharts'        => $aws.'/hc/3.0.9',
    
    //iso countries
    'iso_countries'         => [
                                'lb'=>1,
                                'ae'=>2,
                                'bh'=>3,
                                'sa'=>4,
                                'eg'=>5,
                                'sy'=>6,
                                'kw'=>7,
                                'jo'=>8,
                                'qa'=>9, 
                                'sd'=>10,
                                'tn'=>11,
                                'ye'=>12,
                                'dz'=>15,
                                'iq'=>106,
                                'ly'=>122,
                                'ma'=>145,
                                'om'=>161
								],

    'modules'               => array(
                                'admin'        => array('Admin',0),
                                'ajax-keyword'        => array('Bin',0),
                                'ajax-propspace'        => array('Bin',0),
                                'detail'        => array('Detail',1),
                                'index'         => array('Home',1),
                                'home'         => array('Panel',1),
                                'search'        => array('Search',1),
                                'contact'       => array('Contact',1),
                                'terms'        => array('Doc',1),
                                'privacy'       => array('Doc',1),
                                'about'       => array('Doc',1),
                                'blocked'          => array('Blocked',1),
                                'publication-prices'          => array('Doc',1),
                                'advertise'         => array('Doc',1),
                                'gold'              => array('Doc',1),
                                'buy'               => array('Doc',1),
                                'checkout'          => array('Checkout',0),
                                'premium'           => array('Doc',1),
                                'guide'          => array('Doc',1),
                                'iguide'          => array('Doc',1),
                                'statement'  => array('Balance',0),
                                'favorites'     => array('Search',0),
                                'watchlist'     => array('Search',0),
                                'post'          => array('PostAd',0),
                                'myads'          => array('MyAds',0),
                                'oauth'          => array('OAuth',0),
                                'account'          => array('Account',0),
                                'ajax-help'         => array('Bin',0),  
                                'ajax-home'         => array('Bin',0),  
                                'ajax-mute'         => array('Bin',0),                              
                                'ajax-changepu'         => array('Bin',0),
                                'ajax-getad'         => array('Bin',1),
                                'ajax-delshout'         => array('Bin',0),
                                'page'          => array('Profile',0),
                                'ajax-user-type'         => array('Bin',0),
                                'ajax-sorting'         => array('Bin',0),
                                'ajax-mpre'         => array('Bin',0),
                                'ajax-spre'         => array('Bin',0),
                                'ajax-balance'  => array('Bin',0),
                                'ajax-screen'         => array('Bin',0),
                                'ajax-pi'         => array('Bin',0),
                                'ajax-code-list'      => array('Bin',0),
                                'ajax-post-se'         => array('Bin',0),
                                'ajax-ads'         => array('Bin',0),
                                'ajax-page'         => array('Bin',0),
                                'ajax-page-loc'         => array('Bin',0),
                                'ajax-stat'         => array('Bin',0),
                                'ajax-logo'         => array('Bin',0),
                                'ajax-gdel'         => array('Bin',0),
                                'ajax-banner'         => array('Bin',0),
                                'ajax-bdel'         => array('Bin',0),
                                'ajax-section-update'  => array('Bin',0),
                                'ajax-section-delete'  => array('Bin',0),
                                'ajax-country-cities'  => array('Bin',0),
                                'ajax-cc-remove'       => array('Bin',0),
                                'ajax-cc-add'          => array('Bin',0),
                                'ajax-account'         => array('Bin',0),
                                'ajax-menu'  => array('Bin',1),
                                'ajax-report'  => array('Bin',0),
                                'ajax-contact'  => array('Bin',0),
                                'ajax-support'  => array('Bin',0),
                                'ajax-favorite' => array('Bin',0),
                                'ajax-location' => array('Bin',0),
                                'ajax-sections' => array('Bin',0),
                                'ajax-upload'   => array('Bin',0),
                                'ajax-idel'     => array('Bin',0),
                                'ajax-ahold'     => array('Bin',0),
                                'ajax-ifav'     => array('Bin',0),
                                'ajax-adel'     => array('Bin',0),
                                'ajax-ublock'     => array('Bin',0),
                                'ajax-arenew'     => array('Bin',0),
                                'ajax-adsave'   => array('Bin',0),
                                'ajax-pending'  => array('Bin',0),
                                'ajax-approve'  => array('Bin',0),
                                'ajax-reject'  => array('Bin',0),
                                'ajax-watch'  => array('Bin',0),
                                'ajax-remove-watch'  => array('Bin',0),
                                'ajax-watch-update'  => array('Bin',0),
                                'video-upload'  => array('Bin',0),
                                'ajax-video-upload'  => array('Bin',0),
                                'video-upload-ready'  => array('Bin',0),
                                'ajax-upload-ready'  => array('Bin',0),
                                'video-upload-check'  => array('Bin',0),
                                'ajax-upload-check'  => array('Bin',0),
                                'video-delete'  => array('Bin',0),
                                'ajax-video-delete'  => array('Bin',0),
                                'video-link'  => array('Bin',0),
                                'ajax-video-link'  => array('Bin',0),
                                'ajax-js-error'  => array('Bin',0),
                                'ajax-ga'  => array('Bin',0),
                                'ajax-register'  => array('Bin',0),
                                'ajax-password'  => array('Bin',0),
                                'ajax-preset'  => array('Bin',0),
                                'ajax-prog'  => array('Bin',0),
                                'cache'         => array('GenModules',0),
                                'notfound'      => array('NotFound',1),
                                'suspended'      => array('Blocked',1),
                                'invalid'      => array('NotFound',1),
                                'redirect'      => array('Redirect',0),
                                'signin'      => array('Signin',0),
                                'signup'      => array('Register',0),
                                'password'      => array('Register',0),
                                'welcome'      => array('Register',0),
                                'homescreen'      => array('Homescreen',1),
                                'maintenance'      => array('NotFound',1),
                                'held'      => array('Blocked',1),
                                'ajax-ususpend'     =>  array('Bin',0),
                                'ajax-close-banner'     =>  array('Bin',0),
                                'install'      => array('Homescreen',0),
                                'ajax-ilogo'      => array('Bin',0),
                                'ajax-ipage'      => array('Bin',0)
                            ),
    
	'blocked_agents'        => array(
                                '-'             => 1,
                                'Mozilla/4.0 (compatible; MSIE 5.0; Windows NT; DigExt; DTS Agent'  => 1,
                                'OKArabia search engine/Nutch-1.4' => 1,
                                'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98)' => 1
                            ),
);

$config['url_base']         = $config['host'];
$config['url_bin']          = $config['url_base'].'/bin';
$config['url_upload']       = $config['url_bin'].'/uploadLogo.php';

$globalSettings = DB::getCacheStorage($config)->get("global-settings");
if ($globalSettings!==FALSE)
{
    foreach ($globalSettings as $key => $value)
    {
        $config[$key] = $value;
    }
}

?>
