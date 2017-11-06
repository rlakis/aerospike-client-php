<?php
ob_start();
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 00:00:00 GMT');
header('Content-type: application/json; charset=UTF-8');

const ERR_INVALID_REQUEST_PARAMS                = 1000;
const ERR_INVALID_PHONE_NUMBER                  = 1001;
const ERR_INVALID_COUNTRY_CODE                  = 1002;

const API_DATA                                  = 1;
const API_COUNTS                                = 2;
const API_REGISTER                              = 3;
const API_SEARCH                                = 4;
const API_FAVORITES                             = 5;
const API_WATCHLIST                             = 6;
const API_BOOKMARK                              = 7;
const API_ACTIVATE                              = 8;
const API_PASSWORD                              = 9;
const API_AUTHENTICATE                          = 10;
const API_TOTALS                                = 11;
const API_SET_NOTIFICATION                      = 12;
const API_APNS_TOKEN                            = 13;
const API_UNREGISTER                            = 14;
const API_CHANGE_NUMBER                         = 15;
const API_COUNTRY_LOCS                          = 16;
const API_PHONE_VALIDATION                      = 17;
const API_WATCHLIST_VISITED                     = 18;

const API_USER_ADS_STAT                         = 19;
const API_USER_DRAFT_ADS                        = 20;
const API_USER_PENDING_ADS                      = 21;
const API_USER_APPROVED_ADS                     = 22;
const API_USER_REJECTCED_ADS                    = 23;
const API_USER_ACTIVE_ADS                       = 27;
const API_USER_ARCHIVED_ADS                     = 29;

const API_USER_SAVE_AD                          = 30;
const API_USER_PUBLISH_AD                       = 31;
const API_USER_RENEW_AD                         = 32;

const API_REVERSE_GEO_IP                        = 33;
const API_USER_HOLD_AD                          = 34;
const API_MAKE_TOP_AD                           = 35;
const API_USER_DELETE_AD                        = 36;
const API_STOP_TOP_AD                           = 37;

const API_IOS_PRODUCTS                          = 40;
const API_IOS_PURCHASE                          = 41;

const API_GET_NOTES                             = 47;
const API_UNIFY_UID                             = 48;
const API_LOGIN_AS_MOBILE                       = 49;
const API_COINS_STATEMENT                       = 50;

const API_ANDROID_WATCHLIST_ADD                 = 51;
const API_ANDROID_WATCHLIST_REMOVE              = 52;
const API_ANDROID_WATCHLIST_TOUCH               = 53;
const API_ANDROID_FAVORITE                      = 54;
const API_ANDROID_FLAG_AD                       = 55;
const API_ANDROID_PARSE_URI                     = 56;
const API_ANDROID_CHECK_FIX_CONNECTION_FAILURE  = 57;
const API_ANDROID_POST_AD                       = 58;
const API_ANDROID_DELETE_AD                     = 59;
const API_ANDROID_GET_AD                        = 60;
const API_ANDROID_HOLD_AD                       = 61;
const API_ANDROID_RENEW_AD                      = 62;

const API_GET_STATS_AD_SUMMARY                  = 63;
const API_GET_STATS_AD_BY_ID                    = 64;

const API_ANDROID_SIGN_OUT                      = 65;
const API_ANDROID_SYNC_ACCOUNT                  = 66;
    
const API_COINS_TOTAL                           = 67;
const API_ANDROID_PURCHASE                      = 68;

const API_ANDROID_CANCEL_PREMIUM                = 69;
const API_ANDROID_MAKE_PREMIUM                  = 70;
const API_ANDROID_STATEMENT                     = 71;
const API_ANDROID_GET_PRODUCTS                  = 72;

const API_ANDROID_GET_PROMO                     = 73;
const API_ANDROID_CLAIM_PROMO                   = 74;

const API_ANDROID_SYNC_WATCHLIST                = 75;

const API_ANDROID_SET_NOTE                      = 76;
const API_ANDROID_VERIFY_BILLING                = 77;

const API_ANDROID_SIGN_IN                       = 78;
const API_ANDROID_SIGN_UP                       = 79;
const API_ANDROID_SET_PASSWORD                  = 80;
const API_ANDROID_CHANGE_ACCOUNT                = 81;
const API_ANDROID_PUSH_RECEIPT                  = 82;
const API_ANDROID_VERIFY_NUMBER                 = 83;
const API_ANDROID_USER_NUMBER                   = 84;
const API_ANDROID_SIGN_IN_GOOGLE                = 85;
const API_ANDROID_USER_MAKE_CALL                = 86;
const API_ANDROID_USER_RECEIVE_CALL             = 87;

const API_DB_EVENT                              = 998;
const API_LOG                                   = 999;


$appVersion=filter_input(INPUT_GET, 'av', FILTER_SANITIZE_STRING, ['options'=>['default'=>'1.1']]);
//if ($appVersion=='1.0.1') 
//{
//    $appVersion='1.0';
//}
require_once get_cfg_var('mourjan.path') . '/deps/autoload.php';
include_once get_cfg_var('mourjan.path') . '/config/cfg.php';

include_once $config['dir']."/api/MobileApi-{$appVersion}.php";
include_once $config['dir'].'/core/model/Db.php';
include_once $config['dir'].'/core/model/NoSQL.php';
include_once $config['dir'].'/core/model/MobileValidation.php';
include_once $config['dir'].'/core/lib/MCSessionHandler.php';
include_once $config['dir'].'/core/lib/MCUser.php';

ini_set('memory_limit', '256M');

class ElapseTime 
{
    private $_total = 0;
    private $_start = 0;
    private $_stop = 0;

    public function start(){
        $this->_start = microtime(TRUE);
    }

    public function stop(){
        $this->_stop = microtime(TRUE);
        $this->_total = $this->_total + $this->_stop - $this->_start;
    }

    public function get_elapse(){
        return sprintf("%.6f",($this->_stop - $this->_start)*1000.0);
    }

    public function get_total_elapse(){
        return sprintf("%.6f", $this->_total*1000.0);
    }
}

$timer = new ElapseTime();
$timer->start();
$api = new MobileApi($config);

if (!$api->hasError()) 
{
    
    $action = filter_input(INPUT_GET, 'm', FILTER_VALIDATE_INT)+0;

    $api->command = $action;
    
    switch ($action) 
    {
        case API_DATA:
            $api->getDatabase();
            break;
        
        case API_COUNTS:
            $api->getCounts();
            break;

        case API_REGISTER:
            $api->register();
            break;
        
        case API_SEARCH:
            $api->search();
            break;

        case API_FAVORITES:
            $api->editFavorites();
            break;
        
        case API_WATCHLIST:
            $api->watchList();
            break;
        
        case API_BOOKMARK:
            $api->bookMark();
            break;
        
        case API_ACTIVATE:
            $api->activate();
            break;
        
        case API_PASSWORD:
            $api->setPassword();
            break;
        
        case API_AUTHENTICATE:
            $api->authenticate();
            break;
        
        case API_TOTALS:
            $api->sphinxTotalsQL();
            break;
        
        case API_SET_NOTIFICATION:
            $api->setNotification();
            break;
        
        case API_APNS_TOKEN:
            $api->setApnsToken();
            break;

        case API_CHANGE_NUMBER:
            $api->changeNumber();
            break;

        case API_UNREGISTER:
            $api->unregister();
            break;
        
        case API_COUNTRY_LOCS:
            $api->getCountryLocalities();
            break;
        
        case API_PHONE_VALIDATION:
            $api->validatePhoneNumber();
            break;
        
        case API_WATCHLIST_VISITED:
            $api->watchListVisited();
            break;
     
        case API_USER_ADS_STAT:
            $api->getUserAdStat();
            break;
        
        case API_USER_DRAFT_ADS:
            $api->getMyAds(0);
            break;
            
        case API_USER_PENDING_ADS:
            $api->getMyAds(1);
            break;
        
        case API_USER_APPROVED_ADS:
            $api->getMyAds(2);
            break;

        case API_USER_REJECTCED_ADS:
            $api->getMyAds(3);
            break;

        case API_USER_ACTIVE_ADS:
            $api->getMyAds(7);
            break;
           
        case API_USER_ARCHIVED_ADS:
            $api->getMyAds(9);
            break;
            
        case API_USER_HOLD_AD:
            $api->userHoldAd();
            break;
        
        case API_USER_DELETE_AD:
            $api->userDeleteAd();
            break;
        
        case API_USER_RENEW_AD:
            $api->userRenewAd();
            break;
        
        case API_REVERSE_GEO_IP:
            $api->getCountryIsoByIp();
            break;

        case API_IOS_PRODUCTS:
        case API_IOS_PURCHASE:
            $api->iPhoneTransaction();
            break;
        
        case API_GET_STATS_AD_SUMMARY:
            $api->getStatsAdSummary();
            break;
        
        case API_GET_STATS_AD_BY_ID:
            $api->getStatsByAdId();
            break;
        
        case API_ANDROID_CANCEL_PREMIUM:
        case API_ANDROID_MAKE_PREMIUM:
        case API_ANDROID_STATEMENT:
        case API_ANDROID_GET_PRODUCTS:
        case API_ANDROID_GET_PROMO:
        case API_ANDROID_CLAIM_PROMO:
        case API_ANDROID_PURCHASE:
        case API_ANDROID_SYNC_ACCOUNT:
        case API_ANDROID_SYNC_WATCHLIST:
        case API_ANDROID_SET_NOTE:
        case API_ANDROID_VERIFY_BILLING:
        case API_ANDROID_RENEW_AD:
        case API_ANDROID_HOLD_AD:
        case API_ANDROID_GET_AD:
        case API_ANDROID_DELETE_AD:
        case API_ANDROID_POST_AD:
        case API_ANDROID_WATCHLIST_ADD:
        case API_ANDROID_WATCHLIST_REMOVE:
        case API_ANDROID_WATCHLIST_TOUCH:
        case API_ANDROID_FAVORITE:
        case API_ANDROID_FLAG_AD:
        case API_ANDROID_PARSE_URI:
        case API_ANDROID_SIGN_OUT:
        case API_ANDROID_SIGN_IN:
        case API_ANDROID_SIGN_IN_GOOGLE:
        case API_ANDROID_SIGN_UP:
        case API_ANDROID_SET_PASSWORD:
        case API_ANDROID_CHANGE_ACCOUNT:
        case API_ANDROID_PUSH_RECEIPT:
        case API_ANDROID_VERIFY_NUMBER:
        case API_ANDROID_USER_NUMBER:
        case API_ANDROID_USER_MAKE_CALL:            
        case API_ANDROID_USER_RECEIVE_CALL:
        case API_ANDROID_CHECK_FIX_CONNECTION_FAILURE:
            $api->androidTransaction($appVersion);
            break;
        
        
        case API_MAKE_TOP_AD:
            $api->iPhonePurchase();
            break;
            
        case API_STOP_TOP_AD:
            $api->stopAdFeature();
            break;
        
        case API_COINS_STATEMENT:
            $api->getStatment();
            break;
        
        case API_COINS_TOTAL:
            $api->getCreditTotal();
            break;
        
        case 100:
            $api->sphinxTotalsQL();
            break;
        
        case 101:
            $api->sendSMS('9613287168', 'SMS test message');
            break;

        case API_DB_EVENT:
            $api->dbPostEvent();
            break;
        
        case API_GET_NOTES:
            $api->getAdUserNote();
            break;
        
        case API_UNIFY_UID:
            $api->makeMobileUserIdAsOfDesktop();
            break;
        
        case API_LOGIN_AS_MOBILE:
            $api->signInAsMobile();
            break;
        
        case API_LOG:
            $api->logger();
            break;
        
        default:
            $api->result['e'] = "Invalid command request!";
            break;
    }
  
}

$timer->stop();
$api->result['elapsed-time']=$timer->get_elapse();
$api->done();


