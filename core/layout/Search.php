<?php
include_once $config['dir'] . '/core/layout/Page.php';

use Core\Model\Classifieds;
use Core\Lib\SphinxQL;

class Search extends Page 
{    
    const ID                    = 0;
    const CONTENT               = 1;
    const KEYWORDS              = 2;
    const ALT_CONTENT           = 3;
    const ROOT_NAME_EN          = 4;
    const ROOT_NAME_AR          = 5;
    const SECTION_NAME_EN       = 6;
    const SECTION_NAME_AR       = 7;
    const SECTION_KEYWORDS      = 8;
    const COUNTRY_NAME_EN       = 9;
    const COUNTRY_NAME_AR       = 10;
    const COUNTRY_CODE          = 11;
    const CITY_NAME_EN          = 12;
    const CITY_NAME_AR          = 13;
    const PURPOSE_NAME_EN       = 14;
    const PURPOSE_NAME_AR       = 15;
    const PUBLICATION_ID        = 16;
    const HELD                  = 17;
    const COUNTRY_ID            = 18;
    const CITY_ID               = 19;
    const CANONICAL_ID          = 20;
    const DATE_ADDED            = 21;
    const DATE_ENDED            = 22;
    const FEATURED_DATE_ENDED   = 23;
    const PURPOSE_ID            = 24;
    const SECTION_ID            = 25;
    const ROOT_ID               = 26;
    const RTL                   = 27;
    const USER_ID               = 28;
    const USER_RANK             = 29;
    const USER_LEVEL            = 30;
    const URI_FORMAT            = 31;
    const OUTBOUND_LINK         = 32;
    const IMPRESSIONS           = 33;
    const MEDIA                 = 34;
    const COUNTRY               = 35;
    const CITY                  = 36;
    const SECTION_TAG_ID        = 37;
    const LOCALITY_ID           = 38;
    const STARRED               = 39;
    const IS_NEW                = 40;
    
    
    

    var $id = 0, $paginationString = '', $adCount = 0, $subTitle = '';
    var $tmpRootId = 0, $tmpPurposeId = 0;
    var $classifieds = null, $breadString = '', $crumbTitle = '', $crumbString = '', $adRef = '', $dynamicTitle = '',
        $pageThumb = '', $partnerSection = '', $watchName = '', $formatNumbers=false, $mobileValidator=null, $phoneNumber=null;
    var $isRT = 0;
    
    function __construct(Core\Model\Router $router) 
    {
        header('Vary: User-Agent');
        parent::__construct($router); 
        
        $this->tmpPurposeId = 0;
        $this->tmpRootId = 0;
        
        if(isset($_GET['rt']))
        {
            $this->isRT = 1;
        }
        
        if(!$this->isMobile){
            $this->inlineCss .= '.cct > a{white-space:nowrap;float:'.($this->urlRouter->siteLanguage == 'ar' ? 'right':'left').'}';
            $this->inlineCss .= '.sfilter .order,.sfilter .olang{background-color:#f8f8f8}.sfilter .order.ov,.sfilter .olang.ov{background-color:#ff9000}ul.sfilter{background-color:gold}';
            $this->inlineCss.='.ad600{display:inline-block;width:300px;height:600px}.adsawa{border:0;display:inline-block;width:300px;height:250px}';                
            $this->inlineCss .= '#sideFtr{position:relative;z-index:10000}.rpd input[type="email"]{direction:ltr;display: block;width:624px;padding:5px 10px;margin-bottom:15px}';
        }else{
            if(!$this->userFavorites && !$this->urlRouter->watchId){
                $this->isMobileCssLegacy=false;
                $this->clear_require('css');
                $this->set_require('css', 's_home');
                if($this->user->info['id']){
                    $this->set_require('css', 's_user');
                }
                $this->set_require('css', 'search');
            }
            $this->inlineCss .= '.thb.prem{height:50px}h3{margin:10px}';
            $this->inlineCss .= '.txtd input[type="email"]{direction:ltr;width:90%;margin:10px auto;padding:5px 10px;border: 1px solid #CCC}';
        }
        if (!$this->isMobile && !$this->urlRouter->userId && !$this->userFavorites && !$this->urlRouter->watchId) {
            $this->hasLeadingPane=true;
            if($this->user->info['id'] && $this->user->info['level']==9){
                $this->isAdminSearch=isset($_SERVER['HTTP_REFERER']) && preg_match('/\/myads\/\?sub=pending$/', $_SERVER['HTTP_REFERER']) ? 1 : 0;
            }
            
            $this->inlineCss.='                
                .col2w .col1 {width:765px;}
                .ph{width:448px;}
                .nav{width:458px;}
    .nav li{width:40px;}
    .nav .prev{width:90px;}
.ls{width:458px;}
.ls li{height:160px;}
.crd{top:119px;}
.ls p{height:125px;}
.pics img{max-width:448px;}
.kq{background-color:yellow;padding:0 10px;display:block;text-align:left;direction:ltr;position:absolute;top:65px;left:0;color:#666}
@media all and (min-width:1250px) {
    .pics img{max-width:648px;}
    body{min-width:1206px}
    .w,.col2w,.colw{width:1206px;}
    .ftr .w{width:970px;}
    .tpb{width:1200px}
    .col2w .col1{width:990px;}
    .mav,.ph,.lgs,.lgt{width:663px}
    .phx{width:613px!important}
    .ls{width:673px}
    .dur{width:860px}
    .u2,.u2.uc1{width:317px!important}
    .mul{width:1176px}
    .mul li{width:230px}
    .mul ul{width:230px}
    .tz{left:330px}
    .crd{top:89px;}
    .ls p{height:95px;}
    .ls li{height:130px;}
    .nav{width:673px}
    .nav .prev{width:110px;}
    .nav li{width:60px;}
    .dt{width:669px}
    .shas label{width:auto;}
    .tbs{width:1204px;}
    .tbs.t2 li{width:602px}
    .tbs.t3 li{width:401px}
    .tbs.t4 li{width:300px}
    .tbs.t5 li{width:240px}
    .tbs.t6 li{width:200px}
    .sug{width:661px}
    .sug li{width:320px}
    '.($this->urlRouter->siteLanguage=='ar' ? '
    .dl ul{margin-left:9px;float:left;}
    .uhl,.u2{float:right!important}':'
    .dl ul{margin-right:9px;float:right;}
    .uhl,.u2{float:left!important
    }').'
}
';
        }
        
        if($this->isMobile && $this->urlRouter->watchId && $this->channelId==0){
            $this->inlineCss.='
                .po .et{display:inline-block}
                .pi .et{display:none}
                .po .h .et{display:none}
                .pi .h .et{display:inline-block}
                .pi .h{background-color:#666}
                .pi li{background-color:#FFFFBF}
                .pi li.on{background-color:#FFFFBF!important;color:#333!important}
                .ck li.on{background-color:#FFF!important;color:#333!important}
                .pi .cbx{display:none}
                .uno .cbx{background-position:0px 0px}
                .uno .on .cbx{background-position:0px -25px}
                .btw{width:50%}
                .bt{margin-top:10px !important;margin-bottom:10px !important}
                .liw{background-color:#F7E741!important}
                .act2,.nobd,input.bt{border:0!important}
                form{height:auto!important;padding:0!important}
            ';
        }
        
        if($this->isMobile && ($this->urlRouter->watchId || $this->userFavorites)){
            $this->urlRouter->cfg['enabled_ads']=false;
        }
        
        
        $this->stat = array();
        if ($this->userFavorites) {
            $this->checkBlockedAccount(5);
        }
        if ($this->urlRouter->watchId) {
            $this->checkBlockedAccount();
        }
        
        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        if(!isset($this->user->params['user_country']))
            $this->checkUserGeo();
//            if(isset($this->urlRouter->cfg['iso_countries'][$this->user->params['user_country']]) 
//                    && $this->urlRouter->cfg['iso_countries'][$this->user->params['user_country']]==$this->urlRouter->countryId){
            $isBot = preg_match('/googlebot/i',$_SERVER['HTTP_USER_AGENT']);
            if($isBot){
                if($this->urlRouter->countryId){
                    $this->formatNumbers=strtoupper($this->urlRouter->countries[$this->urlRouter->countryId]['uri']);
                }elseif($this->user->params['user_country']){    
                    $this->formatNumbers=strtoupper($this->user->params['user_country']);                    
                }
            }else{
                if($this->user->params['user_country']){    
                    $this->formatNumbers=strtoupper($this->user->params['user_country']);
                }elseif($this->urlRouter->countryId){
                    $this->formatNumbers=strtoupper($this->urlRouter->countries[$this->urlRouter->countryId]['uri']);
                }
            }
        
        
        if ($this->urlRouter->watchId !== null) {
            if ($this->urlRouter->watchId > 0)
                $this->watchInfo = $this->user->getWatchInfo($this->urlRouter->watchId);
//            if ( ($this->user->info['id'] && $this->urlRouter->watchId!=$this->user->info['id']) && !is_array($this->watchInfo)) {
//                $this->urlRouter->watchId=0;
//                $this->user->redirectTo('/invalid/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
//            }
            $this->title = $this->lang['myList'];
            $this->channelId=$this->get('channel');
            if($this->channelId && !is_numeric($this->channelId)) $this->channelId=$this->user->decodeId($this->channelId);
            if(!is_numeric($this->channelId))$this->channelId=0;
            $this->lang['description'] = $this->lang['home_description'] . $this->lang['home_description_all'];
            if ($this->channelId) {
                foreach ($this->watchInfo as $in) {
                    if ($this->channelId == $in['ID']) {
                        $this->watchName = preg_replace('/<.*?>/', '', $in['TITLE']);
                        $this->title.=': ' . $this->watchName;
                        break;
                    }
                }
            }else{
                $this->inlineCss.='.brd .rss{margin:9px 10px}';
            }
        } elseif ($this->urlRouter->userId) {
            if (isset($_GET['preview']))
                $this->pagePreview = true;
            $this->title = '';
            $this->urlRouter->cfg['enabled_ads'] = false;
            $this->partnerInfo = $this->user->getPartnerInfo($this->urlRouter->userId);
            if (!is_array($this->partnerInfo)) {
                $this->urlRouter->userId = 0;
                $this->user->redirectTo('/invalid/' . ($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/'));
            }
            $this->set_require('css', 'profile');
            $descAr = isset($this->partnerInfo['descAr']) && $this->partnerInfo['descAr'] ? $this->partnerInfo['descAr'] : '';
            $descEn = isset($this->partnerInfo['descEn']) && $this->partnerInfo['descEn'] ? $this->partnerInfo['descEn'] : '';

            if ($this->urlRouter->sectionId) {
                switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                    case 999:
                        $this->partnerSection = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex] . ' ' . $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex];
                        break;
                    case 6:
                    case 7:
                        $this->partnerSection = $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex] . ' ' . $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
                        break;
                    case 3:
                        if ($this->urlRouter->siteLanguage == 'ar') {
                            $this->partnerSection = 'وظائف ' . $sectionName = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
                        } else {
                            $this->partnerSection = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex] . ' ' . $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex];
                        }
                        break;
                    case 4:
                        $in = ' ';
                        if ($this->urlRouter->siteLanguage == "en")
                            $in = ' ' . $this->lang['in'] . ' ';
                        $this->partnerSection = $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex] . $in . $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
                        break;
                    case 0;
                    case 5:
                        $this->partnerSection = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
                        break;
                    default:
                        break;
                }
            }
            
            $this->forceNoIndex = true;
            if ($this->urlRouter->siteLanguage == 'ar') {
                if (isset($this->partnerInfo['t']['ar']) && $this->partnerInfo['t']['ar']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['ar'];
                    $this->forceNoIndex = false;
                    //$this->title=$this->lang['pclassifieds_ar'].' '.$this->partnerInfo['title'];
                    $this->title = $this->partnerInfo['title'];
                } elseif (isset($this->partnerInfo['t']['en']) && $this->partnerInfo['t']['en']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['en'];
                    //$this->title=$this->partnerInfo['title'].' '.$this->lang['pclassifieds_en'];
                    $this->title = $this->partnerInfo['title'];
                } else {
                    $this->partnerInfo['title'] = '';
                }
                if ($descAr) {
                    $this->lang['description'] = $descAr;
                } elseif ($descEn) {
                    $this->lang['description'] = $descEn;
                } else {
                    $this->lang['description'] = '';
                }               
            } else {
                if (isset($this->partnerInfo['t']['en']) && $this->partnerInfo['t']['en']) {
                    $this->forceNoIndex = false;
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['en'];
                    //$this->title=$this->partnerInfo['title'].' '.$this->lang['pclassifieds_en'];
                    $this->title = $this->partnerInfo['title'];
                } elseif (isset($this->partnerInfo['t']['ar']) && $this->partnerInfo['t']['ar']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['ar'];
                    //$this->title=$this->lang['pclassifieds_ar'].' '.$this->partnerInfo['title'];
                    $this->title = $this->partnerInfo['title'];
                } else {
                    $this->partnerInfo['title'] = '';
                }
                if ($descEn) {
                    $this->lang['description'] = $descEn;
                } elseif ($descAr) {
                    $this->lang['description'] = $descAr;
                } else {
                    $this->lang['description'] = '';
                }
            }

           
            $this->title = $this->title ? ($this->partnerSection ? $this->partnerSection . ' - ' : '') . $this->title : $this->lang['partner_page_title'];
            if (is_numeric($this->partnerInfo['uri']) || $this->urlRouter->module == 'detail')
                $this->forceNoIndex = true;
            if ($this->urlRouter->userId == $this->user->info['id'] ||
                    $this->lang['description'] ||
                    (isset($this->partnerInfo['a']['ar']) && $this->partnerInfo['a']['ar']) ||
                    (isset($this->partnerInfo['a']['en']) && $this->partnerInfo['a']['en']) ||
                    (isset($this->partnerInfo['email']) && $this->partnerInfo['email']) ||
                    (isset($this->partnerInfo['contact']) && count($this->partnerInfo['contact']))
            ) {
                $this->hasPartnerInfo = true;
            }
        }
        if ($this->isMobile) {
            
            $this->inlineCss.='.yad{text-align:center;display:block}.lbad{text-align:center;overflow:visible!important;background-color:transparent!important;border:0!important}.lbad.responsive{margin:-5px auto 5px}';
            /*
            $this->inlineCss.='.ad > div{display:inline-block}.yad{text-align:center}.lbad{text-align:center;overflow:visible!important;background-color:transparent!important;border:0!important}.lbad.responsive{margin:-5px auto 5px}';
            if (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) {
                $width = $this->user->params['screen'][0];
                if ($width >= 745) {
                    $this->inlineCss .= '.w336 > div{width:336px;height:280px}';
                    $this->inlineCss .= '.w728 > div{width:728px;height:90px}';
                    $this->inlineCss .= '@media all and (max-width:744px){.w728{display: none}}';
                    $this->inlineCss .= '@media all and (max-width:352px){.w336{display: none}}';
                    $this->set_ad(array('Leaderboard' => array('/1006833/M728x90', 728, 90, 'lad-' . $this->urlRouter->cfg['server_id'])));
                    $this->set_ad(array('Square' => array('/1006833/M336x280', 336, 280, 'ad-' . $this->urlRouter->cfg['server_id'])));
                } elseif ($width >= 485) {
                    $this->inlineCss .= '.w336 > div{width:336px;height:280px}';
                    $this->inlineCss .= '.w468 > div{width:468px;height:60px}';
                    $this->inlineCss .= '@media all and (max-width:484px){.w468{display: none}}';
                    $this->inlineCss .= '@media all and (max-width:352px){.w336{display: none}}';
                    $this->set_ad(array('Leaderboard' => array('/1006833/M468x60', 468, 60, 'lad-' . $this->urlRouter->cfg['server_id'])));
                    $this->set_ad(array('Square' => array('/1006833/M336x280', 336, 280, 'ad-' . $this->urlRouter->cfg['server_id'])));
                } elseif ($width >= 353) {
                    $this->inlineCss .= '.w336 > div{width:336px;height:280px}';
                    $this->inlineCss .= '.w320 > div{width:320px;height:50px}';
                    $this->inlineCss .= '.ad100{width:320px;height:100px}';
                    $this->inlineCss .= '@media all and (max-width:352px){.w336{display: none}}';
                    $this->set_ad(array('Square' => array('/1006833/M336x280', 336, 280, 'ad-' . $this->urlRouter->cfg['server_id'])));
                    $this->set_ad(array('Leaderboard' => array('/1006833/M320x50', 320, 50, 'lad-' . $this->urlRouter->cfg['server_id'])));
                } else {
                    $this->inlineCss .= '.w300 > div{width:300px;height:250px}';
                    $this->inlineCss .= '.w320 > div{width:320px;height:50px}';
                    $this->inlineCss .= '.ad100{width:320px;height:100px}';
                    $this->set_ad(array('Square' => array('/1006833/M300x250', 300, 250, 'ad-' . $this->urlRouter->cfg['server_id'])));
                    $this->set_ad(array('Leaderboard' => array('/1006833/M320x50', 320, 50, 'lad-' . $this->urlRouter->cfg['server_id'])));
                }
            } else {
                $this->inlineCss .= '.w300 > div{width:300px;height:250px}';
                $this->inlineCss .= '.w320 > div{width:320px;height:50px}';
                $this->inlineCss .= '.ad100{width:320px;height:100px}';
                $this->set_ad(array('Square' => array('/1006833/M300x250', 300, 250, 'ad-' . $this->urlRouter->cfg['server_id'])));
                $this->set_ad(array('Leaderboard' => array('/1006833/M320x50', 320, 50, 'lad-' . $this->urlRouter->cfg['server_id'])));
            }*/
            
            $this->num = 5;
            if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && strstr($_SERVER['HTTP_USER_AGENT'], 'iPad;'))
                $this->num = 10;
        }else {
            
            $this->globalScript.='var sic=[];';
            //if (!$this->userFavorites) {
                /*if ($this->urlRouter->module == 'detail' && !$this->detailAdExpired) {
                    if (!$this->detailAd[Classifieds::PICTURES] && (!isset($this->detailAd[Classifieds::VIDEO]) || !$this->detailAd[Classifieds::VIDEO])) {
                        $this->set_ad(array('zone_4' => array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_6' => array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_9' => array('/1006833/MediumRectangle', 300, 250, 'div-gpt-ad-1344949423965-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_3' => array('/1006833/mourjan-navigator-square', 200, 200, 'div-gpt-ad-1349258304441-0-' . $this->urlRouter->cfg['server_id'])
                        ));
                    } else {
                        $this->set_ad(array('zone_4' => array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_6' => array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_8' => array('/1006833/Square', 250, 250, 'div-gpt-ad-1344947339993-0-' . $this->urlRouter->cfg['server_id']),
                            'zone_3' => array('/1006833/mourjan-navigator-square', 200, 200, 'div-gpt-ad-1349258304441-0-' . $this->urlRouter->cfg['server_id'])));
                    }
                } else {*/
                    $this->set_ad(array('zone_6' => array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0-' . $this->urlRouter->cfg['server_id']),
                        'zone_10'=>array('/1006833/HalfPage', 300, 600, 'div-gpt-ad-1351783135410-0-'.$this->urlRouter->cfg['server_id']),
                        'zone_11'=>array('/1006833/HalfPage2', 300, 600, 'div-gpt-ad-1505307438459-0'.$this->urlRouter->cfg['server_id'])
                        
                        ));
                        //'zone_7' => array('/1006833/SearchTrailer', 728, 90, 'div-gpt-ad-1334999893723-0-' . $this->urlRouter->cfg['server_id'])));
                        /*'zone_3' => array('/1006833/mourjan-navigator-square', 200, 200, 'div-gpt-ad-1349258304441-0-' . $this->urlRouter->cfg['server_id'])));*/
                //}
        }
        if($this->urlRouter->sectionId == 10){
            $this->num = $this->num * 2;
        }

        if ($this->urlRouter->rootId && isset($this->urlRouter->roots[$this->urlRouter->rootId]))
            $this->rootName = $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex];
        else
            $this->urlRouter->rootId = 0;

        if ($this->urlRouter->sectionId && isset($this->urlRouter->sections[$this->urlRouter->sectionId]))
            $this->sectionName = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
        else
            $this->urlRouter->sectionId = 0;
        if ($this->urlRouter->purposeId && isset($this->urlRouter->purposes[$this->urlRouter->purposeId]))
            $this->purposeName = $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex];

        if (!$this->urlRouter->userId && !$this->urlRouter->watchId && !$this->userFavorites) {
            if ($this->urlRouter->rootId == 1 && $this->urlRouter->sectionId &&
                    ($this->urlRouter->countryId == 1 ||
                    $this->urlRouter->countryId == 2 || 
                    $this->urlRouter->countryId == 3 || 
                    $this->urlRouter->countryId == 4 ||
                    $this->urlRouter->countryId == 7 ||
                    $this->urlRouter->countryId == 9)
            ) {
                $this->localities = $this->urlRouter->db->getLocalitiesData($this->urlRouter->countryId, $this->urlRouter->sectionId, NULL, $this->urlRouter->siteLanguage);                
                if ($this->urlRouter->cityId) {
                    //$found = false;
                    foreach ($this->localities as $loc) {
                        if ($loc['city_id'] == $this->urlRouter->cityId) {
                            //$this->localityId=$loc[0];
                            //$found=true;
                            $this->forceNoIndex = true;
                            break;
                        }
                    }
                    //if (!$found) $this->localities=null;
                }

                if (isset($this->urlRouter->params['loc_id']))
                    $this->localityId = $this->urlRouter->params['loc_id']+0;
                if (!($this->localities && $this->localityId && isset($this->localities[$this->localityId])))
                    $this->localityId = 0;
                
                if($this->localityId && isset($this->localities[$this->localityId])){
                    $hasSiblings = 0;
                    //die(var_export($this->localities[$this->localityId]));
                    $p_id = $this->localities[$this->localityId]['parent_geo_id'];
                    foreach ($this->localities as $loc) {
                        if ($loc['parent_geo_id'] == $p_id) {
                            $hasSiblings++;
                            if($hasSiblings>1)break;
                        }
                    }
                    if($hasSiblings<2){
                        $this->forceNoIndex = true;
                    }
                }
                
            }elseif ($this->urlRouter->sectionId) {
                $this->extended = $this->urlRouter->db->getSectionTagsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->sectionId, $this->urlRouter->siteLanguage);
            }
        }
        if ($this->urlRouter->module == 'search' &&
                (
                ($this->urlRouter->purposeId && !$this->urlRouter->rootId && !$this->urlRouter->sectionId) ||
                ($this->urlRouter->rootId == 99 && !$this->urlRouter->sectionId)
                )
        ) {
            $this->forceNoIndex = true;
        }
        if (!$this->urlRouter->userId && !$this->urlRouter->watchId) {
            $this->getBreadCrumb($this->urlRouter->module == 'detail');
            $this->buildTitle();
        }
        if(!$this->urlRouter->isPriceList){
            if ($this->rss) {
                $this->num = 100;
            }

            $this->execute(true);

            //Mobile inline css handling
            if ($this->isMobile) {
                if ($this->urlRouter->watchId && $this->searchResults === false) {
                    $this->inlineCss.='.ybx{display:block;width:106px;height:107px;float:' . ($this->urlRouter->siteLanguage == 'ar' ? 'left;margin:0 5px 5px 0' : 'right;margin:0 0 5px 5px;') . '}';
                    $this->inlineCss.='.som{display:block;height:95px;margin-bottom:5px}';
                    $this->inlineCss.='.bwz {text-shadow: #FFF -1px 1px 3px;color:#00E;font-weight:bold;text-align:center;display: block;border-top: 2px dashed #4d7187;border-bottom: 2px dashed #4d7187;list-style:none;overflow:hidden;margin-bottom:15px}';
                    $this->inlineCss.='.poa,.boa {display:block;height:150px;width:120px;margin:0 auto 5px auto}';
                    if ($this->urlRouter->siteLanguage == 'ar') {
                        $this->inlineCss.='.bwz li{width:50%;float:right;padding:15px 0}';
                    } else {
                        $this->inlineCss.='.bwz li{width:50%;float:left;padding:15px 0}';
                    }
                }
            }
            if($this->pageUserId && !$this->searchResults['body']['total_found']){
                $this->urlRouter->cfg['enabled_ads']=0;
                $this->urlRouter->cfg['enabled_sharing']=0;
            }
        }
        
        if(in_array($this->urlRouter->sectionId, $this->urlRouter->cfg['restricted_section_ads'])){
            $this->urlRouter->cfg['enabled_ads']=0;
        }
        
        $this->render();
    }

    function mainMobile() {
        if ($this->userFavorites && !$this->user->info['id']) {
            
        } elseif ($this->urlRouter->watchId && !$this->user->info['id']) {
            
        } else {
            $this->resultsMobile();
            // Show out of section featured ad
            if ($this->urlRouter->module=='search' && isset($this->searchResults['zone2']) && isset($this->searchResults['zone2']['matches']) && count($this->searchResults['zone2']['matches'])) {
                //error_log( $this->searchResults['zone2']['matches'][0] );
                ?> <!--googleoff: index --> <?php 
                $this->renderMobileFeature();
                ?> <!--googleon: index --> <?php 
            }
            echo $this->paginationMobile();
        }
    }

    function renderSidePage() {
        $isOwner = $this->urlRouter->userId == $this->user->info['id'] && $this->urlRouter->module != 'detail' && !$this->pagePreview;
        $isOwner = 0;
        /* if (){
          $isOwner=true;
          if ($this->partnerInfo['title']){
          ?><ul class='list pist cp'><?php
          ?><li class="sr <?= $this->urlRouter->siteLanguage ?>"><a href="/page/<?= $this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'' ?>"><?= $this->lang['editPage'] ?></a></li><?php
          }else {
          ?><ul class='list pist cp cg sh'><?php
          ?><li class="<?= $this->urlRouter->siteLanguage ?>"><?= $this->lang['congratsPage'] ?></li><?php
          }
          ?></ul><?php
          } */
        $editLink = '/page/' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'en/');
        $descAr = isset($this->partnerInfo['descAr']) && $this->partnerInfo['descAr'] ? $this->partnerInfo['descAr'] : '';
        $descEn = isset($this->partnerInfo['descEn']) && $this->partnerInfo['descEn'] ? $this->partnerInfo['descEn'] : '';
        $website = isset($this->partnerInfo['url']) && $this->partnerInfo['url'] ? $this->partnerInfo['url'] : '';

        ?><ul class='list'><?php
        if ($descAr || $descEn || $isOwner) {
            /* ?><h4><?= $this->lang['aboutUs'] ?></h4><?php */
                if ($descAr || $descEn) {
                    ?><li <?= 'class="' . ($this->urlRouter->siteLanguage == 'ar' ? ($descAr ? 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr : 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn) : ($descEn ? 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn : 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr) ) . '</p>' . ($isOwner ? '<p class="pk ' . $this->urlRouter->siteLanguage . '"><a href="' . $editLink . '?edit=desc#desc"><span class="ek"></span>' . $this->lang['addPageDescE'] . '</a></p>' : '') ?></li><?php
                        /* if ($website){
                          ?><li class='en ctr'><a href="<?= $website ?>" target="_blank" rel="nofollow"><?= $website ?></a></li><?php
                          } */
                } elseif ($isOwner) {
                    ?><li><?= '<p class="pb">' . $this->lang['addPageDesc'] . '</p><p class="pk"><a href="' . $editLink . '?edit=desc#desc"><span class="ek"></span>' . $this->lang['addPageDescE'] . '</a></p>' ?></li><?php
                }
        }
        if ($website || $isOwner) {
            ?><li class="h"><?= $this->lang['visitUs'] ?></li><?php
            ?><li><?php
                if ($website) {
                    ?><p class="<?= $isOwner ? 'pb ' : '' ?>en ctr"><a href="<?= $website ?>" target="_blank" rel="nofollow"><?= $website ?></a></p><?php
                }
                if ($isOwner) {
                    ?><p class="pk"><a href="<?= $editLink ?>?edit=website#website"><span class="ek"></span><?= $website ? $this->lang['editWebsite'] : $this->lang['hint_website'] ?></a></p><?php
                }
            ?></li><?php
        }
        /*
          $logo=isset($this->partnerInfo['logo']) && $this->partnerInfo['logo'] ? $this->partnerInfo['logo']:'';
          if($logo){
          ?><ul class='list logo cp'><?php
          ?><li class="ctr"><img src="http://dev.mourjan.com/web/usr/logo/1000001.png" /></li><?php
          ?></ul><?php
          }
         */
        $numbers = isset($this->partnerInfo['contact']) && count($this->partnerInfo['contact']) ? $this->partnerInfo['contact'] : false;
        $email = (isset($this->partnerInfo['email']) && $this->partnerInfo['email'] ) ? $this->partnerInfo['email'] : '';
        if ($numbers || $email || $isOwner) {
            ?><li class="h"><?= $this->lang['contactUs'] ?></li><?php
            if ($numbers || $email) {
                    $numbers = $this->user->displayContactNumbers($numbers, $email, $this->lang);
                    echo $numbers;
                    
                    
                    if ($isOwner) {
                        ?><p class="pb pk"><a href="<?= $editLink ?>?edit=contact#contact"><span class="ek"></span><?= $this->lang['addPageNumbersE'] ?></a></p><?php
                        ?><p class="pk"><a href="<?= $editLink ?>?edit=email#email"><span class="ek"></span><?= $this->lang['addPageEmailE'] ?></a></p></li><?php
                    }
                    } elseif ($isOwner) {
                        ?><li><p class="pb"><?= $this->lang['addPageNumbers'] ?></p><?php
                        ?><p class="pb pk"><a href="<?= $editLink ?>?edit=contact#contact"><span class="ek"></span><?= $this->lang['addPageNumbersE'] ?></a></p><?php
                        ?><p class="pk"><a href="<?= $editLink ?>?edit=email#email"><span class="ek"></span><?= $this->lang['addPageEmailE'] ?></a></p></li><?php
            }
        }
        $descAr = isset($this->partnerInfo['adrAr']) && $this->partnerInfo['adrAr'] ? $this->partnerInfo['adrAr'] : '';
        $descEn = isset($this->partnerInfo['adrEn']) && $this->partnerInfo['adrEn'] ? $this->partnerInfo['adrEn'] : '';
        $hasMap = ( (isset($this->partnerInfo['loc']['lat']) && $this->partnerInfo['loc']['lat']) ||
                (isset($this->partnerInfo['loc']['lon']) && $this->partnerInfo['loc']['lon'])) && $this->urlRouter->module != 'detail';
        if ($descAr || $descEn || $isOwner || $hasMap) {
            ?><li class="h"><?= $this->lang['address'] ?></li><?php
                if ($descAr || $descEn) {
                    if ($descAr || $descEn) {
                        ?><li <?= 'class="f ' . ($this->urlRouter->siteLanguage == 'ar' ? ($descAr ? 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr : 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn) : ($descEn ? 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn : 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr) ) . '</p>'
                        ?><?= $isOwner ? '<p class="pk ' . $this->urlRouter->siteLanguage . '"><a href="' . $editLink . '?edit=address#address"><span class="ek"></span>' . $this->lang['addPageAddrE'] . '</a></p>' : '' ?></li><?php
                        }
                } elseif ($isOwner) {
                    ?><li class="f"><?= '<p class="pb">' . $this->lang['addPageAddr'] . '</p><p class="pk"><a href="' . $editLink . '?edit=address#address"><span class="ek"></span>' . $this->lang['addPageAddrE'] . '</a></p>' ?></li><?php
            }
            if (0 && $hasMap) {
                ?><div class="mpa" id="mapo"></div><?php
                    $this->inlineScript.='var script = document.createElement("script");script.type = "text/javascript";script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initializeMap&language=' . $this->urlRouter->siteLanguage . '";document.body.appendChild(script);';
                    $this->globalScript.='function initializeMap() {
                    geocoder=new google.maps.Geocoder();
                    infowindow=new google.maps.InfoWindow();
                    var myOptions={panControl:false,streetViewControl:false,center:new google.maps.LatLng(' . $this->partnerInfo['loc']['lat'] . ',' . $this->partnerInfo['loc']['lon'] . '),zoom:14,scaleControl:true,mapTypeId:google.maps.MapTypeId.ROADMAP};
                    var map = new google.maps.Map(document.getElementById("mapo"), myOptions);
                    var marker = new google.maps.Marker({
                        map: map,
                        clickable:false,
                        position: map.getCenter()
                    });
                };';
                }
            }
            
            
                    
                    
                    
                    $socialString='';
                    if (isset($this->partnerInfo['links']) && is_array($this->partnerInfo['links'])) {

                        foreach ($this->partnerInfo['links'] as $id => $value){
                            if ($id && $value){
                                switch($id){
                                    case 'fb':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://www.facebook.com/".$value."'><span class='fb-link'></span> Facebook</a><br />";
                                        break;
                                    case 'tw':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://twitter.com/".$value."'><span class='tw-link'></span> Twitter</a><br />";
                                        break;
                                    case 'gp':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://plus.google.com/".$value."'><span class='gp-link'></span> Google Plus</a><br />";
                                        break;
                                    case 'lk':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://www.linkedin.com/in/".$value."'><span class='lk-link'></span> LinkedIn</a><br />";
                                        break;                                        
                                }
                            }
                        }
                        if ($socialString) {
                            ?><li class='h'><?= $this->lang['followUs'] ?></li><?php
                            ?><li class="soc"><?= $socialString ?></li><?php
                        }
                    }
            
            ?></ul><?php
        }

        function side_pane() {
            if ($this->urlRouter->userId) {
                //$this->renderSidePage();
            } elseif ($this->urlRouter->watchId && !$this->user->info['id']) {
                $this->renderSideSite();
            } else {
                /*if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) {
                    if (($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) || ($this->urlRouter->params['q'] && $this->searchResults['total_found'] < 100)) {
                        if ($this->user->info['id']) {
                            $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
                            if (isset($this->user->info['options']['watch'][$key])) {
                                ?><div onclick="document.location = '/watchlist<?= ($this->urlRouter->siteLanguage == 'ar' ? '' : '/en') ?>/'" class="eck rck"><?= $this->lang['nowWatching'] . '<b>' . $this->title . '</b>' . $this->lang['goWatch'] ?></div><?php
                            } else {
                                ?><div onclick="ti()" class="eck"><?= $this->lang['watchLink'] . '<b>' . $this->title . '</b>' ?></div><?php
                            }
                        } else {
                            ?><div onclick="ti()" class="eck"><?= $this->lang['watchLink'] . '<b>' . $this->title . '</b>' ?></div><?php
                        }
                    }
                }*/
                //$this->renderSideUserPanel();
                /*if ($this->urlRouter->module != 'detail' && !$this->urlRouter->watchId && $this->urlRouter->params['start'] > 1)
                    echo $this->fill_ad('zone_3', 'ad_s ad_ts');
                
                */
                
                if ($this->urlRouter->module != 'detail'){
                    $this->renderSearchSettings();
                }
                if (!$this->urlRouter->watchId) {
                    ?> <!--googleoff: index --> <?php 
                    $this->renderExtendedLinks();
                    $this->renderLocalityLinks();
                    $this->renderSideRoots();
                    ?> <!--googleon: index --> <?php 
                    //$this->renderSideCountries();
                }
                /*
                if (!$this->urlRouter->watchId && ($this->urlRouter->module == 'detail' || $this->urlRouter->params['start'] < 2))
                    echo $this->fill_ad('zone_3', 'ad_s');
*/
                /*if ($this->urlRouter->module == 'search' && ($this->urlRouter->countryId || $this->userFavorites || $this->urlRouter->watchId))
                    $this->renderSideLike();*/
            }
        }

        function main_pane() {
            if($this->urlRouter->isPriceList){
                $this->priceResults();
            }elseif ($this->userFavorites && !$this->user->info['id'] && (!$this->pageUserId || ($this->pageUserId && !$this->searchResults['body']['total_found']))) {
                $this->lang['hint_login'] = $this->lang['hint_login_favorites'];
                $this->renderLoginPage();
            } elseif ($this->urlRouter->watchId && !$this->user->info['id'] && (!$this->pageUserId || ($this->pageUserId && !$this->searchResults['body']['total_found']))) {
                $this->lang['hint_login'] = $this->lang['hint_login_watch'];
                $this->renderLoginPage();
            } else {                
                $this->results();                
            }
        }

        function load_js_classic() {
            ?><div id="mis" class='ms'></div><?php
            parent::load_js_classic();
        }
        
        function load_js() {
            ?><div id="mis" class='ms'></div><?php
            parent::load_js();
        }

        function renderResults($keywords = '') {
            //$this->globalScript.='var sic=[];';
            /* $this->inlineScript.='if(sic.length){
              for(i in sic){
              var e=document.getElementById(i);
              e.innerHTML=sic[i];
              }};'; */

            if (!$this->userFavorites && $this->urlRouter->module != 'detail')
                $this->updateUserSearchCriteria();
            $idx = 0;
            $nidx = 0;
            $ad_keys=array();
            $this->mergeResults($topFeatureCount, $ad_keys);
            $current_time=time();
            $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
            $ad_count = count($ad_keys);
            if (!isset($this->stat['ad-imp']))
                $this->stat['ad-imp'] = array();
            
            if(!isset($this->user->params['feature'])) {
                $this->user->params['feature']=array();
            }
            
            $renderAd=true;
            $smallBanner = true;
            
            for ($ptr = 0; $ptr < $ad_count; $ptr++) 
            {
                $id = $ad_keys[$ptr];
                $feature=false;
                $paid=false;
                if($topFeatureCount){
                    $topFeatureCount--;
                    if(isset($this->searchResults['zone1']) && in_array($id, $this->searchResults['zone1']['matches'])){
                        $feature=true;
                        $paid=true;
                    }elseif(in_array($id, $this->searchResults['zone0']['matches'])){
                        $this->user->params['feature'][]=$id;
                        $feature=true;
                    }
                }else{
                    if(isset($this->searchResults['zone1']) && in_array($id, $this->searchResults['zone1']['matches'])) continue;
                }
                $this->user->update();
            
                $ad = $this->classifieds->getById($id,false,$ad_cache);

                if (isset($ad[Classifieds::ID])) {                    
                    
                    $isFeatured = $current_time < $ad[Classifieds::FEATURE_ENDING_DATE];
                    $isFeatureBooked = $current_time < $ad[Classifieds::BO_ENDING_DATE];
                    
                    if ($ad[Classifieds::PUBLICATION_ID]==1 && !(isset($this->detailAd[Classifieds::ID]) && $this->detailAd[Classifieds::ID]==$ad[Classifieds::ID]) ) {
                        if (isset($this->user->info['level'])) {
                            if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                                //if(isset($this->searchResults["matches"][$id])){
                                $this->stat['ad-imp'][]=$id;
                                //}
                            }
                        } else {
                            //if(isset($this->searchResults["matches"][$id])){
                            $this->stat['ad-imp'][]=$id;
                            //}
                        }
                    }
                    if (!empty($ad[Classifieds::ALT_CONTENT])) {
                        if ($this->urlRouter->siteLanguage == "en" && $ad[Classifieds::RTL]) {
                            $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                            $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                            $ad[Classifieds::RTL] = 0;
                        } elseif ($this->urlRouter->siteLanguage == "ar" && $ad[Classifieds::RTL] == 0) {
                            $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                            $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                            $ad[Classifieds::RTL] = 1;
                        }
                    }               
                    //$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                    if (!isset($ad[Classifieds::TELEPHONES])) {
                        error_log("wrong cache, ad id: ".$ad[Classifieds::ID]);
                    }
                    $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);

                    //$this->processTextNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE]);

                    if ($ad[Classifieds::PUBLICATION_ID] == 1 || $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][6]=='http://www.waseet.net/') {
                        $pub_link = ($feature?'<b class="b">':'<b>') . ($this->urlRouter->siteLanguage=='ar' ? 'موقع مرجان':'mourjan.com') . '</b>';
                    }else {
                        $pub_link = "<b>" . $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex] . '</b>';
                    }


                    $ad[Classifieds::CONTENT] = preg_replace('/www(?!\s+)\.(?!\s+).*(?!\s+)\.(?!\s+)(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)/', '', $ad[Classifieds::CONTENT]);
                    $ad[Classifieds::CONTENT] = preg_replace('/^[\s-]*/', '', $ad[Classifieds::CONTENT]);
                    //if (!(isset($this->user->info['level']) && $this->user->info['level'] == 9)) {
//                        $feed = $this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', $keywords, array("limit" => 160));
//                        $feed[0] = trim($feed[0]);
                    $feed = $this->BuildExcerpts($ad[Classifieds::CONTENT], 160);
                        if (substr($feed, -3) == '...') {
                            $replaces = 0;
                            $feed = preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/', '$1' . ($this->urlRouter->id == $ad[Classifieds::ID] ? '' : '<span class="lnk">' . ($ad[Classifieds::RTL] ? $this->lang['readMore_ar'] : $this->lang['readMore_en']) . '</span>'), $feed, -1, $replaces);
                            if (!$replaces && $this->urlRouter->id != $ad[Classifieds::ID])
                                $feed.='<span class="lnk"> ' . ($ad[Classifieds::RTL] ? $this->lang['readMore_ar'] : $this->lang['readMore_en']) . '</span>';
                        }
                        $ad[Classifieds::CONTENT] = $feed;
                    //}
                    $itemScope = '';
                    $itemDesc = '';
                    $hasSchema = false;
                    if ($ad[Classifieds::ROOT_ID] == 1) {
                        $hasSchema = true;
                        $itemDesc = 'itemprop="description" ';
                        $itemScope = ' itemscope itemtype="https://schema.org/Product"';
                    } elseif ($ad[Classifieds::ROOT_ID] == 2) {
                        $hasSchema = true;
                        $itemDesc = 'itemprop="description" ';
                        $itemScope = ' itemscope itemtype="https://schema.org/Product"';
                    } elseif ($ad[Classifieds::ROOT_ID] == 3) {
                        if ($ad[Classifieds::PURPOSE_ID] == 3) {
                            $itemDesc = 'itemprop="description" ';
                            $itemScope = ' itemscope itemtype="https://schema.org/JobPosting"';
                        } elseif ($ad[Classifieds::PURPOSE_ID] == 4) {
                            $itemDesc = 'itemprop="description" ';
                            $itemScope = ' itemscope itemtype="https://schema.org/Person"';
                        }
                    }

                    $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit'] < $ad[Classifieds::UNIXTIME]);
                    $textClass = 'en';
                    $liClass = '';
                    $newSpan = '';
                    $optSpan = '';
                    $hasLink = true;
                    if ($isNewToUser) {
                        $newSpan.="<span class='nw'></span>";
                        $hasLink = false;
                    }
                    $detailAd=false;
                    if ($this->urlRouter->id == $ad[Classifieds::ID]) {
                        $detailAd=true;
                        $liClass.="on ";
                    } else {
                        $optSpan = '<span onclick="ado(this)" class="button adn"></span>';
                    }
                    if($isFeatureBooked){
                        $liClass.=' vpz';
                    }
                    if($isFeatured){
                        $liClass.=' vp vpd';
                    }elseif($feature){
                        $liClass.=' vp';
                        $idx=1;
                        if($paid){
                            $liClass.=' vpd';
                        }
                    }elseif ($idx % 2) {
                        //$liClass.=" alt";
                    }elseif ($idx == 0) {
                        $liClass.=" f";
                    }
                    
                    if ($ad[Classifieds::RTL]) {
                        $textClass = "ar";
                    }
                    $numOfRowsToRenderImgs = 3;
                    $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/'), $ad[Classifieds::ID]);

                    $pic = '';
                    if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                        $picCount='';
                        if (isset($ad[Classifieds::PICTURES]) && is_array($ad[Classifieds::PICTURES]) && count($ad[Classifieds::PICTURES])) {
                            $picCount='<span class=\"cnt\">'.count($ad[Classifieds::PICTURES]).'</span>';
                        }
                        $pic = $ad[Classifieds::VIDEO][2];
                        if($idx < $numOfRowsToRenderImgs){
                            $pic = '<span class="thz"><img src="' . $pic . '" /><span class="play"></span>'.$picCount.'</span>';
                        }else{
                            $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img src=\"' . $pic . '\" /><span class=\"play\"></span>'.$picCount.'";';
                            $pic = '<span class="thb"></span>';
                        }
                        $liClass.=' pic';
                    } elseif ($ad[Classifieds::PICTURES] && is_array($ad[Classifieds::PICTURES])  && count($ad[Classifieds::PICTURES])) {
                        $picCount=count($ad[Classifieds::PICTURES]);
                        $pic = $ad[Classifieds::PICTURES][0];
                        if($this->urlRouter->isAcceptWebP){
                            $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                        }
                        if($idx < $numOfRowsToRenderImgs){
                            $pic = '<span class="thz"><img src="' . $this->urlRouter->cfg['url_ad_img'] . '/repos/s/' . $pic . '" /><span class="cnt">'.$picCount.'</span></span>';
                        }else{
                            $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img src=\"' . $this->urlRouter->cfg['url_ad_img'] . '/repos/s/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'</span>";';
                            $pic = '<span class="thb"></span>';
                        }
                        $liClass.=' pic';
                    } else {
                        if($idx < $numOfRowsToRenderImgs){
                            $pic = '<span class="thz"><img class="d" src="' . $this->urlRouter->cfg['url_img'] . '/90/' . $ad[Classifieds::SECTION_ID]. $this->urlRouter->_png . '" /></span>';
                        }else{
                            $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img class=\"d\" src=\"' . $this->urlRouter->cfg['url_img'] . '/90/' . $ad[Classifieds::SECTION_ID] . $this->urlRouter->_png . '\" />";';
                            $pic = '<span class="thb"></span>';
                        }
                        $liClass.=' pic';
                    }

                    $favSpan = '';
                    if ($this->user->info['id']) {
                        if ($this->user->favorites) {
                            if (in_array($ad[Classifieds::ID], $this->user->favorites)) {
                                //$favLink="<span onclick='fv({$ad[Classifieds::ID]},this)' class='fav on'></span>";
                                $favSpan = '<span class="k fav on"></span>';
                                $liClass.=' fav';
                            }
                        }
                    }
                    
                    $locSpan='';
                    if ($ad[Classifieds::PUBLICATION_ID] == 1) {
                        if ($ad[Classifieds::LATITUDE] || $ad[Classifieds::LONGITUDE]) {
                            $locSpan = "<span class='k loc'></span>";
                        }
                    }                  
                    
                    
                    if ($liClass)
                        $liClass = "class='" . trim($liClass) . "'";
                    $id = 'id="d' . $ad[Classifieds::ID] . '"';
                    if ($ad[Classifieds::ID] != $this->urlRouter->id) {
                        $id = ' id="' . $ad[Classifieds::ID] . '"';
                    }
                    /* ?><li itemprop="itemListElement" <?= $liClass.$itemScope ?>><a class='<?= $textClass ?>' href="<?= $_link ?>"><?= '<span '.$itemDesc.'>'.$newSpan.$ad[Classifieds::CONTENT].'</span>' ?><span class="<?= $this->urlRouter->siteLanguage ?>"><?= $pub_link . " <time st='".$ad[Classifieds::UNIXTIME]."'></time>" ?></span></a></li><?php */
                    /* ?><li <?= $id ?> itemprop="itemListElement" <?= $liClass . $itemScope ?>><?= '<p '.( $detailAd ? '': 'onclick="wo(\'' . $_link . '\')" ') . $itemDesc . ' class="button ' . $textClass . '">' . $pic . $newSpan . $ad[Classifieds::CONTENT] . '</p>' ?><span class="src <?= $this->urlRouter->siteLanguage ?>"><?= (($feature||$isFeatured) ? ( ($paid||$isFeatured) ? '<span class="vpdi '.$this->urlRouter->siteLanguage.'"></span><b>'.$this->lang['premium_ad'].'</b>' : '<span class="ovp '.$this->urlRouter->siteLanguage.'"></span>'.$pub_link) : $pub_link . " <time st='" . $ad[Classifieds::UNIXTIME] . "'></time>") . $optSpan. $locSpan . $favSpan  ?></span></li><?php */
                    
                    if(!$isFeatured && !$feature && $idx > 1 && $smallBanner){
                        if($this->urlRouter->cfg['enabled_ads']/* && (!isset($this->user->params['screen'][0]) || $this->user->params['screen'][0]<745)*/){
                            /* ?><li class="lbad"><div class="ad100"><ins class="adsbygoogle" data-ad-client="ca-pub-2427907534283641" data-ad-slot="5711519829"></ins></div></li><?php */
                            $this->renderAdSense++;
                            ?><li class="lbad responsive"><br /><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-2427907534283641" data-ad-slot="7294487825" data-ad-format="auto"></ins><br /></li><?php
                            /*$alterAd = $this->weightedRand([30,70]);
                            
                            if($alterAd){//70% reponsive banner
                                ?><li class="lbad responsive"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-2427907534283641" data-ad-slot="7294487825" data-ad-format="auto"></ins></li><?php
                            }else{//30% native banner
                                if($this->urlRouter->siteLanguage == 'ar'){
                                    ?><li class="lbad"><ins class="adsbygoogle" style="display:block" data-ad-format="fluid" data-ad-layout="image-side" data-ad-layout-key="-ff+5t+69-jv+eq" data-ad-client="ca-pub-2427907534283641" data-ad-slot="6379463641"></ins></li><?php
                                }else{
                                    ?><li class="lbad"><ins class="adsbygoogle" style="display:block" data-ad-format="fluid" data-ad-layout="image-side" data-ad-layout-key="-fg+5e+8s-gl-r" data-ad-client="ca-pub-2427907534283641" data-ad-slot="6674977112"></ins></li><?php
                                }
                            }*/
                        }/*else{
                            $banner = $this->fill_ad('Leaderboard', 'ad_dt');
                            if($banner){
                                echo '<li class="lbad"><br />'.$banner.'<br /></li>';
                            }
                        }*/
                        $smallBanner = false;
                    }
                    
                    ?><li <?= $id ?> itemprop="itemListElement" <?= $liClass . $itemScope ?>><?= '<p '.( $detailAd ? '': 'onclick="wo(\'' . $_link . '\')" ') . $itemDesc . ' class="button ' . $textClass . '">' . $pic . $newSpan . $ad[Classifieds::CONTENT] . '</p>' ?><span class="src <?= $this->urlRouter->siteLanguage ?>"><?= (($feature||$isFeatured) ? ( ($paid||$isFeatured) ? '<span class="ic r102"></span><b>'.$this->lang['premium_ad'].'</b>' : '<span class="ovp '.$this->urlRouter->siteLanguage.'"></span>') : "<time st='" . $ad[Classifieds::UNIXTIME] . "'></time>") . $optSpan. $locSpan . $favSpan  ?></span></li><?php
                    
                    $idx++;
                   
                    
                    if(!$feature) {
                        $nidx++;
                    }
                }
            }
        }

        function weightedRand($weights, $weight_sum = 100){
            $r = rand(1,$weight_sum);
            $n = count($weights);
            $i = 0;
            while($r > 0 && $i < $n){
                $r -= $weights[$i];
                $i++;
            }
            return $i - 1;
        }
        
        function _rss() {
            $feed = parent::_rss();
            if ($this->urlRouter->rootId == 2 && $this->urlRouter->sectionId > 0) {
                $feed->setImage(trim($this->urlRouter->pageTitle[$this->urlRouter->siteLanguage]), $this->urlRouter->cfg['host'] . $this->urlRouter->uri, $this->urlRouter->cfg['url_img'] . "/200/{$this->urlRouter->sectionId}.png");
            }

            if (isset($this->searchResults['body']['matches']) && $this->searchResults['body']['matches']) {
                $ad_keys = $this->searchResults['body']['matches'];
                $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
                $ad_count = count($ad_keys);
                //if ($ad_count>$this->num) $ad_count=$this->num;
                for ($ptr = 0; $ptr < $ad_count; $ptr++) {
                    $id = $ad_keys[$ptr];

                    $ad = $this->classifieds->getById($id,false,$ad_cache);
                    if (!$ad[Classifieds::ID])
                        continue;

                    if (!empty($ad[Classifieds::ALT_CONTENT])) {
                        if ($this->urlRouter->siteLanguage == "en" && $ad[Classifieds::RTL]) {
                            $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                            $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                            $ad[Classifieds::RTL] = 0;
                        } elseif ($this->urlRouter->siteLanguage == "ar" && $ad[Classifieds::RTL] == 0) {
                            $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                            $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                            $ad[Classifieds::RTL] = 1;
                        }
                    }
                    //$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                    /*
                    if (empty($ad[Classifieds::TITLE]) || $ad[Classifieds::TITLE]=='' || $ad[Classifieds::TITLE] == 'test')
                        $this->getAdTitle($ad);
                        */
                    $_link = $this->urlRouter->cfg['host'] . sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == "ar" ? "" : "{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]);

                    $item = $feed->createNewItem();

                    //$item->setTitle('');
                    $item->setLink($_link);
                    $item->setDate($ad[Classifieds::DATE_ADDED]);
                    $item->setDescription($this->shortText($ad));
                    $gfx = false;
                    if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                        $pic = $ad[Classifieds::VIDEO][2];
                        $item->setEncloser($ad[Classifieds::VIDEO][1], '', 'application/x-shockwave-flash');
                        $gfx = true;
                    } elseif ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                        $pic = $ad[Classifieds::PICTURES][0];
                        preg_match("/\.(.*)$/", $pic, $matches);
                        $media_type = '';
                        if ($matches && count($matches)) {
                            switch ($matches[1]) {
                                case 'png' : $media_type = 'image/png';
                                    break;
                                case 'jpeg':
                                case 'jpg' : $media_type = 'image/jpeg';
                                    break;
                                case 'gif' :
                                    $media_type = 'image/gif';
                                    break;
                                default:
                                    break;
                            }
                        }
                        if ($media_type) {
                            $item->setEncloser($this->urlRouter->cfg['url_ad_img'] . "/repos/d/{$pic}", '', $media_type);
                            $gfx = true;
                        }
                    }

                    if (!$gfx && $_GET['rss'] == 'fb') {
                        $pageThumb = $this->urlRouter->cfg['url_img'] . "/90/{$this->urlRouter->sectionId}.png";

                        $item->setEncloser($pageThumb, '', "image/png");
                    }

                    $feed->addItem($item);
                }
            }

            //OK. Everything is done. Now genarate the feed.
            $feed->generateFeed();
        }

        function shortText($ad) {
            $text = $ad[Classifieds::CONTENT];
            $text = preg_replace("/<(.*?)>/", "", $text);

            $pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/i';
            $text = preg_replace($pattern, "", $text);

            $pattern = "/[_a-z0-9-]+(\.[_a-z0-9-]+)*(\s|)@(\s|)[a-z0-9-]+(\.[a-z0-9-]+)*((\s|)\.(\s|)[a-z]{2,4})*(\s|$)/i";
            $text = preg_replace($pattern, "", $text);

            if (preg_match("/للاتصال|للإتصال|للتواصل|للمفاهم(ه|ة)|للاستعلام|للإستعلام|الاتصال|الإتصال|للاستفسار|للإستفسار|للمراجعة|للمراجعه/", $text, $matches))
                $text = trim(strstr($text, $matches[0], true));
            elseif (preg_match("/هاتف:/", $text, $matches)) {
                $text = trim(strstr($text, $matches[0], true));
            } elseif (preg_match("/ت:/", $text, $matches)) {
                $text = trim(strstr($text, $matches[0], true));
            } elseif (preg_match("/Tel:|call(:?)/i", $text, $matches)) {
                $text = trim(strstr($text, $matches[0], true));
            }

            $text = preg_replace("/\d{6,}-0\d+|0\d{1,2}-\d{6,}|0\d{6,}/", "", $text);
            if ($this->urlRouter->countryId == 1) {
                $text = preg_replace("/7\d{6,}/", "", $text);
            }


            $text = trim(preg_replace("/\s\+\d+/", "", $text));

            $text = trim(preg_replace("/-*$/", "", $text));
            $text = trim(preg_replace("/\s(ج|ت)*$/", "", $text));


            return trim($text);
        }

        
    function alternateSearchMobile($keywords = "") {
        $localityId = $this->localityId;
        $extendedId = $this->extendedId;
        if ($this->extendedId || $this->localityId) {
            $this->extendedId = 0;
            $this->localityId = 0;
        } elseif ($this->urlRouter->purposeId) {
            $this->urlRouter->purposeId = 0;
            $this->purposeName = "";
        } elseif ($this->urlRouter->sectionId) {
            $this->urlRouter->sectionId = 0;
            $this->sectionName = "";
        } elseif ($this->urlRouter->rootId) {
            $this->urlRouter->rootId = 0;
            $this->rootName = "";
        } else {
            echo '<br /><h2 class="ctr">' . $this->summerizeSearchMobile(true) . '</h2>';
            echo '<p class="ctr"><span class="na"></span></p>';
            echo '<h2 class="ctr">' . $this->lang['anotherSearch'] . '</h2>';
            return false;
        }
        $this->urlRouter->params['start'] = 0;
        $this->execute(true);
    
        if ($this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0) {
                
            echo '<div class="sum">', $this->summerizeSearchMobile(true), '</div>';

            $sectionId = $this->urlRouter->sectionId;
            $rootId = $this->urlRouter->rootId;
            if (isset($this->user->params['search']['se']))
                $this->urlRouter->sectionId = $this->user->params['search']['se'];
            if (isset($this->user->params['search']['ro']))
                $this->urlRouter->rootId = $this->user->params['search']['ro'];

            $this->urlRouter->sectionId = $sectionId;
            $this->urlRouter->rootId = $rootId;
            $this->paginationMobile();
            ?><ul class='ls card rsl<?= $this->paginationString == '' ? ' sh' : '' ?>'><?php
            $this->renderResults($keywords);
            ?></ul><?php
            echo $this->paginationMobile();
            if (isset($this->user->params['search']['se']))
                $this->urlRouter->sectionId = $this->user->params['search']['se'];
            if (isset($this->user->params['search']['ro']))
                $this->urlRouter->rootId = $this->user->params['search']['ro'];
            if (isset($this->user->params['search']['pu']))
                $this->urlRouter->purposeId = $this->user->params['search']['pu'];
        } else {
            $this->alternateSearchMobile($keywords);
        }
        $this->localityId = $localityId;
        $this->extendedId = $extendedId;
    }

        
    function resultsMobile() {
        $keywords = "";
        $adLang='';
        
        if ($this->urlRouter->siteLanguage != "ar") $adLang=$this->urlRouter->siteLanguage.'/';
        
        if ($this->urlRouter->watchId && $this->searchResults === false) {
            echo '<h2 class="ctr">' . $this->summerizeSearchMobile(true) . '</h2>';
            echo '<p class="ctr"><span class="na"></span></p>';
            echo '<h2 class="ctr">' . $this->lang['addW1'] . '</h2>';
            echo '<p class="ctr"><img src="' . $this->urlRouter->cfg['url_css_mobile'] . '/i/t/wat' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') . $this->urlRouter->_png . '" /></p>';
                //echo '<h2 class="ctr">' . $this->lang['addF2'] . '</h2>';
                //echo '<p class="ctr"><img src="' . $this->urlRouter->cfg['url_css_mobile'] . '/i/t/fav' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') . '2.png" /></p>';
                /*?><div class="str"><?php
                ?><p><span class="ybx"></span><?= $this->lang['wm_desc'] ?></p><?php
                ?><h2 class="ctr"><?= $this->lang['w_slogan'] ?></h2><?php
                ?><div class="som"></div><?php
                ?></div><?php */
                //$this->renderBottomMenMobile();
        } else {
            $purposeId = $this->urlRouter->purposeId;
                //            if (isset($this->searchResults['words']))
                //                $keywords = implode(" ", array_keys($this->searchResults['words']));
            $hasResults = $this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0;
            //if (!$this->urlRouter->purposeId && $hasResults)
            //    $this->filterPurposesMobile();
    
            if ($hasResults) {                    
                echo '<div class="sum">', $this->summerizeSearchMobile(), '</div>';
                //            $this->setNotification($this->summerizeSearchMobile());
                //            $this->renderNotificationsMobile();
                /* ?><div class="hd"><?php echo $this->summerizeSearchMobile() ?></div><?php */
                
                $this->paginationMobile();
                ?><ul itemscope itemtype="https://schema.org/ItemList" class='ls card rsl<?= $this->paginationString == '' ? ' sh' : '' ?>'><?php
                $this->renderResults($keywords);
                ?></ul><?php
                echo $this->paginationMobile();

                /*
                $hasEye=0;
                if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) {
                    if (($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100)) {
                        $hasEye=1;
                    }
                }
                if($this->user->info['id'] && ($this->user->info['level']==6 || $this->user->info['id']==5))
                    $hasEye=0;

                if ($hasEye) {
                    echo '<ul class="ls us bbr">';
                    if ($this->user->info['id']) {
                        $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
                        if (isset($this->user->info['options']['watch'][$key])) {
                            echo '<li><a href="/watchlist/'.$adLang.'?u='.$this->user->info['idKey'].'"><span class="ic k eye on"></span><span class="lnk">'.ucfirst($this->lang['inWatchlist']).'</span><span class="et"></span></a></li>';
                            //echo '<li><b>'. $this->title.'</b> '. $this->lang['inWatchlist'] .'<span class="db ctr"><a href="/watchlist/'.$adLang.'?u='.$this->user->info['idKey'].'" class="bt">'.$this->lang['goWatch'].'</a></span><span id="ewd" title="'.$this->lang['inWatchlist'].'" class="ek ekon"></span></div>';
                        } else {
                            echo '<li><b onclick="owt(this)" class="button"><span class="ic k eye on"></span><span class="lnk">'.$this->lang['addWatch'].'</span><span class="to"></span></b></li>';
                            //echo '<li>'.$this->lang['watchAsk'] .'<b>'. preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title) .'</b>'.$this->lang['?'].'<span class="ctr db"><span class="bt ok" onclick="owt(this)">'.$this->lang['btWatch'].'</span></span><span id="ewd" title="'.$this->lang['addWatch'].'" class="ek"></span></div>';
                        }
                    } else {
                        echo '<li><b onclick="owt(this)" class="button"><span class="ic k eye on"></span><span class="lnk">'.$this->lang['addWatch'].'</span><span class="to"></span></b></li>';
                        //echo '<li>'.$this->lang['watchAsk'] .'<b>'. preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title) .'</b>'.$this->lang['?'].'<span class="ctr db"><span class="bt ok" onclick="owt(this)">'.$this->lang['btWatch'].'</span></span><span id="ewd" title="'.$this->lang['addWatch'].'" class="ek"></span></div>';
                    }
                    echo '</ul>';
                }*/
                
                
                /*
                if (!$this->urlRouter->watchId && !$this->userFavorites){ 
                    ?><ul class="ls us bbr"><?php 
                    ?><li><a href="/post/<?= $adLang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
                    ?></ul><?php
                }
                */
            } else {
                if ($this->userFavorites) {
                    echo '<h2 class="ctr">' . $this->summerizeSearchMobile(true) . '</h2>';
                    echo '<p class="ctr"><span class="na"></span></p>';
                        echo '<h2 class="ctr">' . $this->lang['addF1'] . '</h2>';
                        echo '<p class="ctr"><img src="' . $this->urlRouter->cfg['url_css_mobile'] . '/i/t/fav' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') . '1' . $this->urlRouter->_png .'" /></p>';
                        echo '<h2 class="ctr">' . $this->lang['addF2'] . '</h2>';
                        echo '<p class="ctr"><img src="' . $this->urlRouter->cfg['url_css_mobile'] . '/i/t/fav' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') . '2' . $this->urlRouter->_png .'" /></p>';
                    } elseif($this->urlRouter->watchId) {
                        echo '<h2 class="ctr">' . $this->summerizeSearchMobile(true) . '</h2>';
                        echo '<p class="ctr"><span class="na"></span></p>';
                        //echo '<h2 class="ctr">' . $this->lang['anotherSearch'] . '</h2>';
                    }else{
                        //if ($this->urlRouter->params['q']) {
                        //    echo '<div class="hd na">',($this->lang['no_result_pre'].' <b>'.$this->get('q', 'filter').'</b> '.($this->urlRouter->sectionId ? $this->lang['in']:$this->lang['included']).' '.$this->sectionSummeryMobile(). ' ' .$this->lang['no_result_short']),'</div>';
                        //$this->setNotification($this->lang['no_listing'].' '.$this->sectionSummeryMobile());
                        // $this->setNotification($this->lang['no_listing'].' '.$this->summerizeSearchMobile());
                        //}
                        $this->alternateSearchMobile($keywords);
                    }
                }
                
                if ($this->urlRouter->watchId && $this->searchResults !== false) {
                    $cSec = count($this->watchInfo);
                    if ($cSec) {
                        $this->globalScript.='var cSec='.$cSec.';';
                        $lang = $this->urlRouter->siteLanguage == 'ar' ? '' : 'en/';
                        $isOwner = $this->user->info['id'] == $this->urlRouter->watchId;
                        $idKey = $this->user->encodeId($this->pageUserId);
                        if ($isOwner) {
                            ?><p class="ctr hid"><?= $this->lang['editListDesc'] ?></p><?php
                            ?><ul id="watchbox" class='ls lse br'><?php
                            if ($this->channelId == 0){
                                ?><li class="h"><b><span class="ic k eye on"></span><?=$this->lang['myList'] ?><span class="n"><?= count($this->watchInfo) ?> / 20<span></b><?php
                            } else {
                                ?><li><a class="lnk" href="/watchlist/<?= $lang ?>?u=<?= $idKey ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?><span class="to"></span></a><?php
                            }
                            ?></li><?php
                           /* ?></ul><?php
                            ?><ul class='seli sh'><?php */
                                    $countryCities=array();
                                    $localitiesArray=array();
                                    $extendedArray=array();
                                    foreach ($this->watchInfo as $info) {
                                        $ext='';
                                        if($info['COUNTRY_ID']){
                                            $uri = '/' . $this->urlRouter->countries[$info['COUNTRY_ID']]['uri'] . '/';
        
                                            $idx=2;
                                            if($info['LOCALITY_ID']){
                                                if(!isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']])){
                                                    $localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']] = $this->urlRouter->db->getLocalitiesData($info['COUNTRY_ID'], $info['SECTION_ID'], NULL, $this->urlRouter->siteLanguage);
                                                }
                                                if(isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']])){
                                                    $uri.=$localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']]['uri'] . '/';
                                                    $ext = 'c-' . $info['LOCALITY_ID'] . '-' . 2 . '/';
                                                }
                                            }elseif($info['CITY_ID']){
                                                $idx=3;
                                                $uri.=$this->urlRouter->cities[$info['CITY_ID']][3].'/';
                                            }
                                            if($info['SECTION_TAG_ID']){
                                                if(!isset($extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']])){
                                                    $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']] = $this->urlRouter->db->getSectionTagsData($info['COUNTRY_ID'], $info['CITY_ID'], $info['SECTION_ID'], $this->urlRouter->siteLanguage);
                                                }
                                                $uri.=$this->urlRouter->sections[$info['SECTION_ID']][3] . '-' . $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']][$info['SECTION_TAG_ID']]['uri'] . '/';
                                                $ext = 'q-' . $info['SECTION_TAG_ID'] . '-' . $idx . '/';
                                            }elseif($info['SECTION_ID']){
                                                $uri.=$this->urlRouter->sections[$info['SECTION_ID']][3] . '/';
                                            }
                                            if($info['PURPOSE_ID'])
                                                $uri.=$this->urlRouter->purposes[$info['PURPOSE_ID']][3] . '/';
                                        }
                                            $uri.=($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/');
                                            if($ext){
                                                $uri.=$ext;
                                            }
                                            if($info['QUERY_TERM']){
                                                $uri.='?q='.urlencode($info['QUERY_TERM']);
                                            }
                                            
                                            $iTmp='<span class="z z117"></span>';
                                            if($info['SECTION_ID'] && isset($this->urlRouter->sections[$info['SECTION_ID']])){                                                
                                                $rootId=$this->urlRouter->sections[$info['SECTION_ID']][4];
                                                if($rootId==1){
                                                    $iTmp='<span class="x x'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==2){
                                                    $iTmp='<span class="z z'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==3){
                                                    $iTmp='<span class="v v'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==4){
                                                    $iTmp='<span class="y y'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==99){
                                                    $iTmp='<span class="u u'.$info['SECTION_ID'].'"></span>';
                                                }else {
                                                    $iTmp='<span class="z z117"></span>';
                                                }
                                            }
                                            
                                            $hasEmail=$info['EMAIL'] ? '<span class="ic mail"></span>' : '';

                                            /*if ($this->channelId == $info['ID'] || $cSec == 1) {
                                                ?><li><b id="<?= $info['ID'] ?>"><span class="z z<?= $info['SECTION_ID'] ? $info['SECTION_ID'] : 117 ?>"></span><?= ($info['EMAIL'] ? '<span class="d mail"></span>':'') . $info['TITLE'] ?></b><?php
                                                } else {
                                                    ?><li><a href="/watchlist/<?= $lang . '?u='.$idKey.'&channel=' . $info['ID'] ?>" id="<?= $info['ID'] ?>"><span class="z z<?= $info['SECTION_ID'] ?>"></span><?= ($info['EMAIL'] ? '<span class="d mail"></span>':'') . $info['TITLE'] ?></a><?php
                                                }
                                                ?><span class="kl"><span onclick="eW(this,<?= $info['ID'] ?>)" class="rj edi"></span><a href="<?= $uri ?>" class="rj nxt"></a></span><?php
                                                ?></li><?php */
                                            
                                            //$info['TITLE']=preg_replace('/\["\']/', '',$info['TITLE']);
                                            $info['TITLE']=preg_replace('/\\\["\']/', '',$info['TITLE']);
                                            //var_dump($info['TITLE']);
                                            if ($this->channelId == $info['ID']) {
                                                ?><li class="on"><b id="<?= $info['ID'] ?>"><?= $iTmp.$hasEmail ?><?= $info['TITLE'] ?><span class="to"></span></b></li><?php
                                            }elseif($cSec == 1){
                                                ?><li><b id="<?= $info['ID'] ?>"><?= $iTmp.$hasEmail ?><?= $info['TITLE'] ?><span class="to hid"></span></b></li><?php
                                            }else{
                                                ?><li><a class="button" href="/watchlist/<?= $lang . '?u='.$idKey.'&channel=' . $info['ID'] ?>" id="<?= $info['ID'] ?>"><?= $iTmp.$hasEmail ?><?= $info['TITLE'] ?><span class="to"></span></a></li><?php
                                            }
                                        }
                                        if($this->channelId==0){
                                            ?><li><b class="ah ctr act si"><span onclick="editW(this)" class="button bt ok"><?= $this->lang['editList'] ?></span><span onclick="doneW(this)" class="button bt ok hid"><?= $this->lang['returnList'] ?></span></b></li><?php
                                            ?><li class="hid"><b class="ah ctr act si"><span onclick="delW(-1,this)" class="button bt cl"><?= $this->lang['emptyList'] ?></span><span onclick="doneW(this)" class="button bt ok hid"><?= $this->lang['returnList'] ?></span></b></li><?php
                                        }
                                        ?></ul><?php
                                        if($this->channelId==0){
                                            ?><div id="editPanel" class="hid"><?php
                                                ?><ul class="ls po pi"><?php
                                                    ?><li onclick="enm(this)" class="button h"><b><?= $this->lang['watchLabel'] ?><span class="et"></span></b></li><?php 
                                                    ?><li onclick="enm(this)" class="button"><b id="sLabel"></b></li><?php
                                                    ?><li class="nobd hid"><ul><?php
                                                    ?><li><div class="ipt"><input type="text" id="sEdit" name="ttl" onfocus="idir(this)" onkeyup="idir(this)" onchange="idir(this)" /></div></li><?php
                                                        ?><li class="liw hid"></li><?php 
                                                        ?><li><b class="ah ctr act2"><span onclick="saveW(this)" class="button bt ok"><?= $this->lang['save'] ?></span><span onclick="clF(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                                                    ?></ul></li><?php
                                                ?></ul><?php
                                                ?><ul class="ls po ck"><?php
                                                    ?><li onclick="enm(this)" class="button h"><b><?= $this->lang['watchSettings'] ?></b></li><?php 
                                                    ?><li id="sEmail" onclick="ckO(this)" class="button"><b class="ah"><?= $this->lang['emailNotify'] ?><span class="cbx"></span></b></li><?php
                                                    ?><li><b class="ah ctr act si"><span onclick="delW(0,this)" class="button bt cl"><?= $this->lang['delete'] ?></span></b></li><?php
                                                    ?><li<?= $cSec ==1 ? ' class="hid"':'' ?>><b class="ah ctr act si"><span onclick="backW(this)" class="button bt ok"><?= $this->lang['returnToList'] ?></span></b></li><?php
                                                    ?><li><b class="ah ctr act si"><span onclick="backW(this,1)" class="button bt fb"><?= $this->lang['returnList'] ?></span></b></li><?php
                                                ?></ul><?php
                                            ?></div><?php
                                            
                                            
                                        $this->globalScript.='
                                            var bt,ee,ue,cuw,cul;
                                            function enm(e){
                                                var p=$p(e);
                                                if(p.className.match(/pi/)){
                                                    var c=$c(p);
                                                    fdT(p,1,"pi");
                                                    fdT(c[1]);
                                                    fdT(c[2],1);
                                                    $f(c[2],4).focus();
                                                }
                                            }
                                            function clF(e){
                                                var p=$p(e,3);
                                                var c=$c(p);
                                                fdT(c[0],1);
                                                fdT(c[1]);
                                                p=$p(p,2);
                                                c=$c(p);
                                                fdT(c[1],1);
                                                fdT(c[2],0);
                                                fdT(p,0,"pi");
                                            }
                                            function ckO(e){
                                                var z=$(e);
                                                if(z.hasClass("on")){
                                                    z.removeClass("on");
                                                    cuw.e=0;
                                                }else {
                                                    z.addClass("on");
                                                    cuw.e=1;
                                                }
                                                _sav();
                                            }
                                            function _sav(e){
                                                $.ajax({
                                                    type:"POST",
                                                    url:"/ajax-watch-update/",
                                                    data:cuw,
                                                    dataType:"json",
                                                    success:function(rp){
                                                        if (rp.RP) {
                                                            var la=$("#sLabel",ee);
                                                            la.html(cuw.t);
                                                            la[0].className=cuw.g;
                                                            
                                                            var bx=$(".ar,.en",cul);
                                                            bx.html(cuw.t);
                                                            bx[0].className=cuw.g;
                                                            
                                                            var tx=$(".mail",cul);
                                                            if(cuw.e){
                                                                if(!tx.length){
                                                                    var o=$("<span class=\'ic mail\'></span>");
                                                                    o.insertAfter(cul.firstChild);
                                                                }
                                                            }else{
                                                                if(tx.length){
                                                                    tx.remove();
                                                                }
                                                            }
                                                        }
                                                    }
                                                });
                                                if(e)clF(e);
                                            }
                                            function saveW(e){
                                                var b=$("#sEdit",ee);
                                                var v=b.val();
                                                v=v.replace(/^\s+|\s+$/g, "");
                                                if(v){
                                                    cuw.t=v;
                                                    var cls=b.hasClass("ar") ? "ar":"en";
                                                    cuw.g=cls;
                                                    _sav(e);
                                                }else{
                                                    b.addClass("err");
                                                }
                                            }
                                            var lks=[],edBT;
                                            function editW(e){
                                                edBT=e;
                                                $(e).addClass("hid");
                                                $($a(e)).removeClass("hid");
                                                var z=$p(e,2);
                                                $($a(z)).removeClass("hid");
                                                var u=$p(z);
                                                var b=$b(u);
                                                $(b).removeClass("hid");
                                                b=$b(b);
                                                do{
                                                    b.style.display="none";
                                                    b=$b(b);
                                                }while(b);
                                                var l=$c(u);
                                                var cl=$cL(u)-2;
                                                var sig;
                                                for(var i=1;i<cl;i++){
                                                    var c=$c(l[i],0);
                                                    lks[c.id]=c.href;
                                                    c.href="#";
                                                    (function(c){
                                                        c.onclick = function(){
                                                            editC(c)
                                                        };
                                                    })(c); 
                                                    if(cSec==1)sig=c;
                                                    else $c(c,-1).className="et";
                                                }
                                                if(sig)$(sig).click();
                                                gto($p(u));
                                            }
                                            function backW(e,s){
                                                ee.addClass("hid");
                                                $($b(ue[0])).removeClass("hid");
                                                ue.removeClass("hid");
                                                if(s){
                                                    doneW($a(edBT));
                                                }
                                            }
                                            function doneW(e){
                                                $(e).addClass("hid");
                                                $($b(e)).removeClass("hid");
                                                var z=$p(e,2);
                                                $($a(z)).addClass("hid");
                                                var u=$p(z);
                                                var b=$b(u);
                                                $(b).addClass("hid");
                                                b=$b(b);
                                                do{
                                                    b.style.display="block";
                                                    b=$b(b);
                                                }while(b);
                                                var l=$c(u);
                                                var cl=$cL(u)-2;
                                                for(var i=1;i<cl;i++){
                                                    var c=$c(l[i],0); 
                                                    c.href=lks[c.id];
                                                    c.onclick=function(){};
                                                    if(cSec!=1)$c(c,-1).className="to";
                                                }
                                                gto($p(u));
                                            }
                                            function editC(e){
                                                cul=e;
                                                if(!ee){
                                                    ee=$("#editPanel");
                                                }
                                                if(!ue){
                                                    ue=$("#watchbox");
                                                }
                                                ue.addClass("hid");
                                                $($b(ue[0])).addClass("hid");
                                                ee.removeClass("hid");
                                                var m=$(".mail",e);
                                                var hm=m.length?1:0;
                                                
                                                var tx=$(".ar,.en",e);
                                                var txt=tx.html();
                                                var cls=tx.hasClass("ar") ? "ar":"en";
                                                
                                                var la=$("#sLabel",ee);
                                                la[0].className=cls;
                                                la.html(txt);
                                                
                                                var lm=$("#sEmail",ee);
                                                if(hm)
                                                    lm.addClass("on");
                                                else lm.removeClass("on");
                                                
                                                var le=$("#sEdit",ee);
                                                le[0].className=cls;
                                                le.val(txt);   
                                                
                                                
                                                cuw={
                                                    id:e.id,
                                                    t:txt,
                                                    e:hm,
                                                    g:cls
                                                }
                                            }
                                            function delW(id){
                                                var proc=0;
                                                if(id==-1){
                                                    if(confirm("'.$this->lang['confirmWipe'].'")){
                                                        proc=1;
                                                    }
                                                }else{
                                                    if(confirm("'.$this->lang['confirmWipeOne'].'")){
                                                        proc=1;
                                                        id=cuw.id;
                                                    }
                                                }
                                                if(proc){
                                                    $.ajax({
                                                        type:"POST",
                                                        url:"/ajax-remove-watch/",
                                                        data:{id:id},
                                                        dataType:"json",
                                                        success:function(rp){
                                                            if (rp.RP) {
                                                                document.location="";
                                                            }else{
                                                                alert("'.$this->lang['sys_err'].'");
                                                            }
                                                        },
                                                        error:function(){
                                                            alert("'.$this->lang['sys_err'].'");
                                                        }
                                                    })
                                                }
                                            }
                                        ';
                                        }
                                    } 
                            /* ?><p class="nbi nbi0"><?php 
                                ?><span class="rj edi"></span> <?= $this->lang['watchEditHint'] ?><?php
                            ?></p><?php
                            ?><p class="nbi"><?php 
                                ?><span class="rj nxt"></span> <?= $this->lang['watchNextHint'] ?><?php
                            ?></p><?php
                            ?><p class="nbi"><?php 
                                ?><span class="d mail"></span> <?= $this->lang['watchMailHint'] ?><?php
                            ?></p><?php */
                    }
                }
                
                $this->urlRouter->purposeId = $purposeId;

            if ($this->urlRouter->cfg['enabled_ads'] && $this->searchResults['body']['total_found'] > 3){
               
               $this->renderAdSense++;
                ?><div class="yad"><br /><?php
                    //mobile responsive ad 2
                     ?><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-2427907534283641" data-ad-slot="7030570808" data-ad-format="auto"></ins><?php 
                    //Large Mobile End of List Banner
                    /* ?><ins class="adsbygoogle" style="display:inline-block" data-ad-client="ca-pub-2427907534283641" data-ad-slot="1890774823"></ins><?php */
                ?></div><?php
                /*
                if($this->urlRouter->module=='search'){
                    $iDir = $this->urlRouter->siteLanguage == 'ar' ? 'ad_r' : 'ad_l';
                    echo '<br />'.$this->fill_ad('Square', $iDir);
                 }else{
                    echo '<br />'.$this->fill_ad('Leaderboard', 'ad_dt');
                }*/
            }
            
            
            if ( ($this->urlRouter->module=='search' || $this->urlRouter->module=='detail') && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId ){
                $followStr='';
                if($this->urlRouter->sectionId){
                    $followUp = $this->urlRouter->db->getSectionFollowUp($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);      
                    $fup = array();
                    if(isset($this->urlRouter->sections[$this->urlRouter->sectionId][6]) && $this->urlRouter->sections[$this->urlRouter->sectionId][6]){
                        $tmpSec = explode(',', $this->urlRouter->sections[$this->urlRouter->sectionId][6]);
                        $fup = array();
                        foreach($tmpSec as $sec){
                            $fup[] = array($sec,0);
                        }
                    }
                    if($followUp){
                        $followUp = array_merge($fup,$followUp);
                    }else{
                        $followUp = $fup;
                    }

                    if($followUp && count($followUp)){
                        $procSec=array();
                        $k=0;
                        foreach($followUp as $section){
                            if(!isset($procSec[$section[0]])){
                                $uri=$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,0,$section[0],$section[1]);
                                $sName=$this->urlRouter->sections[$section[0]][$this->fieldNameIndex];
                                if($section[1]){
                                    $pName=$this->urlRouter->purposes[$section[1]][$this->fieldNameIndex];
                                    switch ($section[1]) {
                                        case 1:
                                        case 2:
                                        case 8:
                                            $sName = $sName . ' ' . $pName;
                                            break;
                                        case 6:
                                        case 7:
                                            $sName = $pName . ' ' . $sName;
                                            break;
                                        case 3:
                                            if ($this->urlRouter->siteLanguage == "ar")
                                                    $sName = 'وظائف ' . $sName;
                                            else
                                                    $sName = $sName . ' jobs';
                                            break;
                                        case 4:
                                            $in = "";
                                            if ($this->urlRouter->siteLanguage == "en")
                                                $in = " {$this->lang['in']}";
                                            $sName = $pName . $in . " " . $sName;
                                            break;
                                        case 5:
                                            if ($this->urlRouter->siteLanguage == "ar"){                                               
                                                $tmp='خدمات';
                                                if(!preg_match('/'.$tmp.'/u',$sName)){
                                                    $sName = $tmp . ' ' . $sName;
                                                }
                                            }else{
                                                $tmp='services';  
                                                if(!preg_match('/'.$tmp.'/',$sName)){
                                                    $sName = $sName . ' ' . $tmp;
                                                }
                                            }
                                            break;
                                        case 999:
                                            $sName = $sName . ' ' . ($this->urlRouter->siteLanguage =='en' ? 'misc':'متفرقات');
                                            break;
                                    }
                                }

                                $iTmp='';
                                /*if($this->urlRouter->sections[$section[0]][4]==1){
                                    $iTmp.='<span class="x x'.$section[0].'"></span>';
                                }elseif($this->urlRouter->sections[$section[0]][4]==2){
                                    $iTmp.='<span class="z z'.$section[0].'"></span>';
                                }elseif($this->urlRouter->sections[$section[0]][4]==3){
                                    $iTmp.='<span class="v v'.$section[0].'"></span>';
                                }elseif($this->urlRouter->sections[$section[0]][4]==4){
                                    $iTmp.='<span class="y y'.$section[0].'"></span>';
                                }elseif($this->urlRouter->sections[$section[0]][4]==99){
                                    $iTmp.='<span class="u u'.$section[0].'"></span>';
                                }else {
                                    $iTmp.='<span class="v'.$section[0].'"></span>';
                                }*/

                                $followStr.='<li><a href="'.$uri.'">'.$iTmp.$sName.' <span class="to"></span></a></li>';
                                //echo '<li><a  href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pid) . $q . '">', $purpose['name'], '</a></li>';
                                $procSec[$section[0]]=1;
                                $k++;
                                if($k==5)break;
                            }
                        }
                        if($followStr){
                            ?> <!--googleoff: index --> <?php 
                            echo '<br /><h3>'.$this->lang['interestSection'].'</h3><ul class="ls">'.$followStr.'</ul>';
                            ?> <!--googleon: index --> <?php 
                        }
                    }
                }
            }
/*
            if ($this->urlRouter->purposeId && $hasResults)
                $this->filterPurposesMobile();
*/
                                        
                /*        
                if (!$this->urlRouter->watchId && !$this->userFavorites){ 
                    ?><ul class="ls us bbr"><?php 
                    ?><li><a href="/post/<?= $adLang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
                    ?></ul><?php
                } */              

            if ($this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0) {
                ?> <!--googleoff: index --> <?php                       
                ?><div id="aopt" class="sbx"><?php
                ?><div class="bts"><?php
                
                if ($this->user->info['id']) {
                    ?><div onclick="aF(this)" class="button"><span class="k fav"></span><label><?= $this->lang['m_addFav'] ?></label></div><?php
                    ?><div onclick="rF(this)" class="button"><span class="k fav on"></span><label><?= $this->lang['m_removeFav'] ?></label></div><?php
                } else {
                    /*?><div id="pFB" onclick="pF(this)" class="button"><span class="k fav"></span><label><?= $this->lang['m_addFav'] ?></label></div><?php
                    ?><div><span class="k fav on"></span><label><?= $this->lang['removeFav'] ?></label></div><?php*/
                }
                
                /*?><div onclick="share(this)" class="button"><span class="k share"></span><label><?= $this->lang['share'] ?></label></div><?php*/
                    ?><div onclick="rpA(this)" class="button"><span class="k spam"></span><label><?= $this->lang['reportAbuse'] ?></label></div><?php
                    //$subj=($this->urlRouter->siteLanguage=='ar'?'وجدت هذا الاعلان على مرجان':'found this ad on mourjan');
                    //$msg= urlencode($subj.' '.'https://www.mourjan.com/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage+'/').$this->detailAd[Classifieds::ID]);
                    
                    ?><a class="shr shr-wats" data-action="share/whatsapp/share"></a><?php
                    ?><a class="shr shr-vb"></a><?php
                
                /* ?><div><span class="k eye"></span><label><?= $this->lang['m_addFollow'] ?></label></div><?php 
                  ?><div><span class="k eye on"></span><label><?= $this->lang['m_Followed'] ?></label></div><?php */
                ?></div><?php
                ?><div class="shad bts"></div><?php
                ?><div class="txtd bts"><?php
                ?><h2><?= $this->lang['abuseTitle'] ?></h2><?php
                ?><textarea onkeyup="idir(this)"></textarea><?php
                ?><h2><?= $this->lang['abuseContact'] ?></h2><?php
                ?><input type="email" placeholder="your.email@gmail.com" /><?php
                ?><span onclick="rpS(this)" class="button bt ok"><?= $this->lang['send'] ?></span><?php
                ?></div><?php
                ?><div class="txtd bts"></div><?php 
                
                if (!$this->user->info['id']) {
                    ?><div class="txtd bts lu"><?php
                    ?><h2><?= $this->lang['signin_f'] ?></h2><?php
                    ?></div><?php
                }
                ?></div><?php
                ?> <!--googleon: index --> <?php   
            }
        }
    }

    function renderMobileSublist(){           
        if ($this->urlRouter->module=='search' && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId){

            $hasResults = $this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0;
            if (!$this->urlRouter->purposeId && $hasResults)
                $this->filterPurposesMobile();

            if ($this->urlRouter->rootId==1 && $this->urlRouter->countryId && ($this->searchResults['body']['total_found']>20 || $this->localityId) && count($this->localities)) {
                if ($this->searchResults['body']['total_found'] > 5 || $this->localityId) {
                    $this->renderMobileLocalityLinks();
                }

            }else if ($this->urlRouter->sectionId && ($this->searchResults['body']['total_found']>5 || $this->extendedId) && count($this->extended)) {
                $prefix_uri = '/';
                if ($this->urlRouter->countryId) {
                    $prefix_uri.=$this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
                    $keyIndex = 2;
                }
                else
                    $keyIndex = 1;


                $suffix_uri = '/';
                $prefix = '';
                $suffix = '';


               if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                    $keyIndex++;
                    $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3] . '/';
                }

                $sectionName = $this->sectionName;
                if ($this->urlRouter->purposeId) {
                    $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                    switch ($this->urlRouter->purposeId) {
                        case 1:
                        case 2:
                            $suffix = ' ' . $this->purposeName;
                            $sectionName.=' ' . $this->purposeName;
                            break;
                        case 6:
                        case 7:
                        case 8:
                            $prefix = $this->purposeName . ' ';
                            $sectionName.=$this->purposeName . ' ';
                            break;
                        default:
                            break;
                    }
                }
                if ($this->urlRouter->siteLanguage != 'ar') {
                    $suffix_uri.=$this->urlRouter->siteLanguage . '/';
                }
                
                include_once $this->urlRouter->cfg['dir'] . '/core/lib/SphinxQL.php';
 
                $sphinx = new SphinxQL($this->urlRouter->cfg['sphinxql'], $this->urlRouter->cfg['search_index']);
                if ($this->urlRouter->purposeId) {                                        
                    $q = "select groupby(), count(*) from {$this->urlRouter->cfg['search_index']} where hold=0 and canonical_id=0 "
                    . "and section_id={$this->urlRouter->sectionId} "
                    . "and purpose_id={$this->urlRouter->purposeId} ";
                    if ($this->urlRouter->countryId) $q.="and country={$this->urlRouter->countryId} ";
                    if ($this->urlRouter->cityId) $q.="and city={$this->urlRouter->cityId} ";
                    $q.=" group by section_tag_id limit 1000";
                }else{                    
                    $q = "select groupby(), count(*), group_concat(purpose_id) from {$this->urlRouter->cfg['search_index']} where hold=0 and canonical_id=0 and section_id={$this->urlRouter->sectionId} ";
                    if ($this->urlRouter->countryId) $q.="and country={$this->urlRouter->countryId} ";
                    if ($this->urlRouter->cityId) $q.="and city={$this->urlRouter->cityId} ";
                    $q.=" group by section_tag_id limit 1000";
                }
                $query = $sphinx->search($q);
                if(isset($query['matches']) && count($query['matches'])){
                    $tags=[];
                    $query = $query['matches'];
                    foreach($query as $tag){
                        if ($this->urlRouter->purposeId) {
                            $pus=$this->urlRouter->purposeId;
                        }else{
                            $pus=[];
                            $pul = explode(",",$tag["group_concat(purpose_id)"]);
                            foreach($pul as $puid){
                                $pus[$puid+0]=1;
                            }
                        }
                        $tags[$tag["groupby()"]+0]=[$tag['count(*)'],count($pus)];
                    }
                }
                
                ?> <!--googleoff: index --> <?php
                ?><span onclick="subList(this)" class="rbt subit ic"></span><?php
                ?><div id="sublist"><?php
                    ?><h2 class="ctr"><?= $this->lang['suggestion'] ?></h2><?php
                    ?><ul class="ls"><?php 
                    if ($this->extendedId) { 
                        ?><li class="bbr"><a href="<?= $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) ?>"><?= $sectionName ?><span class="to"></span><span class="n"><?= $this->urlRouter->pageSections[$this->urlRouter->sectionId]['counter'] ?></span></a></li><?php 
                    } 
                    else {
                        ?><li class="on bbr"><b><?= $sectionName ?><span class="n"><?= $this->urlRouter->pageSections[$this->urlRouter->sectionId]['counter'] ?></span></b></li><?php
                    }

                    foreach ($this->extended as $sid=>$sub) {
                        $append = 'q-' . $sid . '-' . $keyIndex . '/';
                        if ($this->extendedId == $sid) {
                            ?><li class="on"><b><?= $prefix . $sub['name'] . $suffix ?>
                            <?php
                            if(isset($tags[$sid])){
                                ?><span class="n"><?= $tags[$sid][0] ?></span><?php
                            }
                            ?></b></li><?php
                        } else {
                            /* url error */
                            ?><li><a href="<?= $prefix_uri . $this->urlRouter->sections[$this->urlRouter->sectionId][3] . '-' . $sub['uri'] . $suffix_uri . $append ?>"><?= $prefix . $sub['name'] . $suffix ?><span class="to"></span><?php
                            if(isset($tags[$sid])){
                                ?><span class="n"><?= $tags[$sid][0] ?></span><?php
                            }
                            ?></a></li><?php
                        }
                    }
                    ?></ul><?php
                ?></div><?php                
                ?> <!--googleon: index --> <?php
            }
        }
    }
        
    function renderBottomMen() {
        $lang = '';
        if ($this->urlRouter->siteLanguage != 'ar')
            $lang = $this->urlRouter->siteLanguage . '/';
        ?><ul class="bwz bwz<?= $this->urlRouter->siteLanguage ?>"><?php
        ?><li onclick="document.location = '/post/<?= $lang ?>';" class="poa"><?= $this->lang['postFree'] ?></li><?php
        ?><li onclick="document.location = '/<?= $lang ?>';" class="boa"><?= $this->lang['browseAd'] ?></li><?php
        ?></ul><?php
    }
    
   
    function footerMobile(){
        $this->renderMobileSublist();
        parent::footerMobile();
    }

    function renderBottomMenMobile() {
        $lang = '';
        if ($this->urlRouter->siteLanguage != 'ar')
            $lang = $this->urlRouter->siteLanguage . '/';
        ?><ul class="bwz bwz<?= $this->urlRouter->siteLanguage ?>"><?php
        ?><li onclick="document.location = '/post/<?= $lang ?>';"><span class="poa"></span><?= $this->lang['postFree'] ?></li><?php
        ?><li onclick="document.location = '<?= $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) ?>';"><span class="boa"></span><?= $this->lang['browseAd'] ?></li><?php
        ?></ul><?php
    }
    
    function processTextNumbers(&$text,$pubId=0,$countryCode=0,&$matches=array()){
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        //preg_match('/( للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        
        //preg_match('/(?: mobile: | viber: | whatsapp: | phone: | fax: | telefax: | جوال: | موبايل: | واتساب: | فايبر: | هاتف: | فاكس: | تلفاكس: | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text,$content);
        
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        if(!($content && count($content))){
            $tmpTxt=preg_replace('/\<.*?>/', '', $text);
            preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt, $content);
        }
        

        if ($content && count($content)) {
            $str=$content[1];
            if ($str) {
                //error_log("str: ".$str);

                if($this->formatNumbers){
                $nums=array();
                $numInst=array();
                $numbers = null;
                preg_match_all($phone, $str, $numbers);
                
                if($numbers && count($numbers[1])){
                    foreach($numbers[1] as $match){
                        $number = $match;
                        try{
                            if($pubId==1){
                                $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                            }else{
                                $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                            }
                        if($num && $this->mobileValidator->isValidNumber($num)){
                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                            if($rCode==$this->formatNumbers){
                                $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                            }else{
                                $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                            }
                            $nums[]=array($number, $num);
                        }else{
                            $hasCCode = preg_match('/^\+/', $number);
                            switch($countryCode){
                                case 'SA':                                    
                                    if($hasCCode){
                                        $num = substr($number,4);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 9:
                                                $num='011'.$num;
                                                break;
                                            case 12:
                                            case 18:
                                                    $tmp='013'.$num;
                                                    $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                    if($tmp && $this->mobileValidator->isValidNumber($tmp)){
                                                        $num='013'.$num;
                                                    }else{
                                                        $num='011'.$num;
                                                    }
                                                break;
                                        }
                                    }
                                    break;
                                case 'EG':
                                    if($hasCCode){
                                        $num = substr($number,3);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                            case 14:
                                                $num='3'.$num;
                                                break;
                                        }
                                    }elseif(strlen($num)==8){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                        }
                                    }
                                    break;
                            }
                            if($num != $number){
                                $num = $this->mobileValidator->parse($num, $countryCode);
                                if($num && $this->mobileValidator->isValidNumber($num)){
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if($rCode==$this->formatNumbers){
                                        $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                    }else{
                                        $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                    }
                                    $nums[]=array($number, $num);
                                }else{
                                    $nums[]=array($number, $number);
                                }
                            }else{
                                $nums[]=array($number, $number);
                            }
                            
                        }
                    }catch(Exception $ex){
                        $nums[]=array($number, $number);
                    }
                }

                

                
                if(preg_match('/\<span class/',$text)){
                    if($this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/'){
                        $mobile=array();
                        $phone=array();
                        $undefined = array();
                        $i=0;
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $type=$this->mobileValidator->getNumberType($numInst[$i++]);  
                                if($type==1 || $type==2)
                                    $mobile[]=$num;
                                elseif($type==0 || $type==2)
                                    $phone[]=$num;
                                else $undefined[]=$num;
                            }else{
                                $undefined[]=$num;
                            }
                        }
                        //error_log('WASEET'. PHP_EOL .var_export($mobile, true));
                        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                        $res = '';
                        if(count($mobile) || count($phone)){
                            if(count($mobile)){
                                $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                $i=0;
                                foreach($mobile as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                            if(count($phone)){
                                if($res)$res.='- ';
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($phone as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                        }elseif(count($undefined)){
                            $res.=($isArabic ? 'هاتف':'Phone').': ';
                            $i=0;
                            foreach($undefined as $mob){
                                if($i)$res.=($isArabic ? 'او ':'or ');
                                $res.='<span class="vn">'.$mob[1].'</span> ';
                                $matches[]=$mob[1];
                                $i++;
                            }
                        }
                        $divider=null;
                        preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        $pos=0;
                        if($divider && count($divider)){
                            $pos = strpos($text, $divider[1]);
                            if(!$pos){
                                $divider=null;
                                preg_match('/(<span)/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if(!$pos){
                            $srh='';
                            foreach($nums as $num){
                                $srh .= $num[0].'|';
                            }
                            if($srh){
                                $srh.=substr($srh,0,-1);
                                $srh=  preg_replace('/\+/','\\+' , $srh);
                                $divider=null;
                                preg_match('/(<span class="pn">'.$srh.')/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if($pos)
                            $text = substr($text,0,$pos);
                        if($res)
                            $text.=' / '.$res;
                    }else{
                        //error_log('NO WASEET'. PHP_EOL .var_export($nums, true));
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                $text = preg_replace('/'.$num[0].'/', $num[1], $text);
                                $matches[]=$num[1];
                            }else{
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                $text = preg_replace('/\<span class="pn(?:[a-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$num[1].'</span>', $text);
                            }
                        }
                    }
                }else{
                    if($this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/'){
                        $mobile=array();
                        $phone=array();
                        $undefined = array();
                        $i=0;
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $type=$this->mobileValidator->getNumberType($numInst[$i++]);  
                                if($type==1 || $type==2)
                                    $mobile[]=$num;
                                elseif($type==0 || $type==2)
                                    $phone[]=$num;
                                else $undefined[]=$num;
                            }else{
                                $undefined[]=$num;
                            }
                        }
                        //error_log('WASEET 2'. PHP_EOL .var_export($mobile, true));
                        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                        $res = '';
                        if(count($mobile) || count($phone)){
                            if(count($mobile)){
                                $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                $i=0;
                                foreach($mobile as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                            if(count($phone)){
                                if($res)$res.='- ';
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($phone as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                        }elseif(count($undefined)){
                            $res.=($isArabic ? 'هاتف':'Phone').': ';
                            $i=0;
                            foreach($undefined as $mob){
                                if($i)$res.=($isArabic ? 'او ':'or ');
                                $res.='<span class="vn">'.$mob[1].'</span> ';
                                $matches[]=$mob[1];
                                $i++;
                            }
                        }
                        $divider=null;
                        preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                        $pos=0;
                        if($divider && count($divider)){
                            $pos = strpos($text, $divider[1]);
                            if(!$pos){
                                $divider=null;
                                preg_match('/(<span)/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if(!$pos){
                            $srh='';
                            foreach($nums as $num){
                                $srh .= $num[0].'|';
                            }
                            if($srh){
                                $srh.=substr($srh,0,-1);
                                $srh=  preg_replace('/\+/','\\+' , $srh);
                                $divider=null;
                                preg_match('/('.$srh.')/',$text,$divider);
                                if($divider && count($divider)){
                                    $pos = strpos($text, $divider[1]);
                                }
                            }
                        }
                        if($pos)
                            $text = substr($text,0,$pos);
                        if($res){
                            $text.=' / '.$res;
                        }
                    }else{
                        //error_log('NO WASEET 2'. PHP_EOL .var_export($nums, true));
                        foreach($nums as $num){
                            if($num[0]!=$num[1]){
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$num[1].'</span>', $text);
                                $matches[]=$num[1];
                            }else{
                                $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$num[1].'</span>', $text);
                            }
                        }
                    }
                }
            }
        }else{
            if($pubId!=1){
                //error_log('Otherwise');
                if(!preg_match('/\<span class/',$text)){
                    preg_match_all($phone, $str, $numbers);
                    if($numbers && count($numbers[1])){
                        foreach($numbers[1] as $match){
                            $number = $match;
                            $number =  preg_replace('/\+/','\\+' , $number);
                            $text = preg_replace('/('.$number.')/', '<span class="pn">$1</span>', $text);
                        }
                    }
                }
            }
        }
        }
        }
        return $text;
    }


    function formatPhoneNumber($number) {
        $phoneNumber = $this->mobileValidator->parse($number, $this->formatNumbers);
        if ($this->mobileValidator->getRegionCodeForNumber($phoneNumber)==$this->formatNumbers) {
            $formatNumber = $this->mobileValidator->formatInOriginalFormat($phoneNumber, $this->formatNumbers);
        } else {
            $formatNumber = $this->mobileValidator->formatOutOfCountryCallingNumber($phoneNumber, $this->formatNumbers);
        }
        return $formatNumber;
    }

  function replacePhonetNumbers(&$text, $pubId=0, $countryCode=0, $mobiles, $phones, $undefined, $email=null,&$matches=''){
        $REGEX_MATCH='/((?:(?:[ .,;:\-\/،])(?:mobile|viber|whatsapp|phone|fax|telefax|للتواصل|جوال|موبايل|واتساب|للاستفسار|للأستفسار|للإستفسار|فايبر|هاتف|فاكس|تلفاكس|الاتصال|للتواصل|للمفاهمة|للاتصال|الاتصال على|اتصال|(?:(?:tel|call|ت|ه|ج)(?:\s|):))(?:(?:\s|):|\+|\/|)(?: |$)))/ui';
        $REGEX_CATCH='/(?:(?:(?:[ .,;:\-\/،])(?:mobile|viber|whatsapp|phone|fax|telefax|للتواصل|جوال|موبايل|واتساب|للاستفسار|للأستفسار|للإستفسار|فايبر|هاتف|فاكس|تلفاكس|الاتصال|للتواصل|للمفاهمة|للاتصال|الاتصال على|اتصال|(?:(?:tel|call|ت|ه|ج)(?:\s|):))(?:(?:\s|):|\+|\/|)(?: |$)))(.*)/ui';
        
        if (!isset($this->formatNumbers) || empty($this->formatNumbers)){
            return;
        }
        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
        if (preg_match('/\<span class/', $text)) {
            //var_dump($mobiles);
            //var_dump($phones);
            /*if ($this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/') {
                $res = '';
                if (count($mobiles) || count($phones)) {

                    if (count($mobiles)) {
                        $res.=($isArabic ? 'موبايل':'Mobile').': ';
                        $i=0;
                        foreach ($mobiles as $mob) {
                            if ($i) $res.=($isArabic ? 'او ':'or ');
                            $res.='<span class="pn o1">'.$mob[1].'</span> ';
                            $matches[]=$mob[1];
                            $i++;
                        }
                    }

                    if (count($phones)) {
                        if ($res) $res.='- ';
                        $res.=($isArabic ? 'هاتف':'Phone').': ';
                        $i=0;
                        foreach ($phones as $mob) {
                            if($i) $res.=($isArabic ? 'او ':'or ');
                            $res.='<span class="pn o7">'.$mob[1].'</span> ';
                            $matches[]=$mob[1];
                            $i++;
                        }
                    }

                } elseif (count($undefined)) {
                    $res.=($isArabic ? 'هاتف':'Phone').': ';
                    $i=0;
                    foreach ($undefined as $mob){
                        if($i)$res.=($isArabic ? 'او ':'or ');
                        $res.='<span class="vn">'.$mob[1].'</span> ';
                        $matches[]=$mob[1];
                        $i++;
                    }
                }

                $divider=null;
                preg_match($REGEX_MATCH, $text, $divider);
                $pos=0;
                if ($divider && count($divider) && $divider[1]) {
                    $pos = strpos($text, $divider[1]);
                    if (!$pos) {
                        $divider=null;
                        preg_match('/(<span)/', $text, $divider);
                        if ($divider && count($divider) && $divider[1]) {
                            $pos = strpos($text, $divider[1]);
                        }
                    }
                }

                if (!$pos) {
                    $srh='';
                    foreach ($mobiles as $num) {
                        if($srh)$srh.='|';
                        $srh .= $num[0];
                    }
                    foreach ($phones as $num) {
                        if($srh)$srh.='|';
                        $srh .= $num[0];
                    }

                    if ($srh) {
                        $srh=  preg_replace('/\+/','\\+' , $srh);
                        $divider=null;
                        preg_match('/(<span class="pn">'.$srh.')/', $text, $divider);
                        if ($divider && count($divider) && $divider[1]) {
                            $pos = strpos($text, $divider[1]);
                        }
                    }
                }

                if ($pos)
                    $text = substr($text,0,$pos);

                if($res)
                    $text.=' / '.$res;

            } else {*/
                foreach ($mobiles as $num) {
                    $number = $this->formatPhoneNumber($num[0]);
                    if ($num[0]!=$number) {
                        $org = $num[0];
                        $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/'.$num[0].'/', $number, $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    } else {
                        $org = $num[0];
                        $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    }
                }
                foreach ($phones as $num) {
                    $number = $this->formatPhoneNumber($num[0]);
                     if ($num[0]!=$number) {
                         $org = $num[0];
                        $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/'.$num[0].'/', $number, $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    } else {
                        $org = $num[0];
                        $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    }
                }
            /*}*/

        } else {
            /*if(isset($this->urlRouter->publications[$pubId]) && $this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/'){
                $res = '';
                if (count($mobiles) || count($phones)) {

                    if(count($mobiles)){
                        $res.=($isArabic ? 'موبايل':'Mobile').': ';
                        $i=0;
                        foreach ($mobiles as $mob){
                            if($i)$res.=($isArabic ? 'او ':'or ');
                            $res.='<span class="pn o1">'.  $this->formatPhoneNumber($mob[1]).'</span> ';
                            $i++;
                            $mob[1]=preg_replace('/\+/','\\+',$mob[1]);
                            $text=preg_replace('/'.$mob[1].'/','',$text);
                        }
                    }

                    if(count($phones)){
                        if($res)$res.='- ';
                        $res.=($isArabic ? 'هاتف':'Phone').': ';
                        $i=0;
                        foreach ($phones as $mob){
                            if($i)$res.=($isArabic ? 'او ':'or ');
                            $res.='<span class="pn o7">'.$this->formatPhoneNumber($mob[1]).'</span> ';
                            $i++;
                            $mob[1]=preg_replace('/\+/','\\+',$mob[1]);
                            $text=preg_replace('/'.$mob[1].'/','',$text);
                        }
                    }

                } elseif (count($undefined)){
                    $res.=($isArabic ? 'هاتف':'Phone').': ';
                    $i=0;
                    foreach ($undefined as $mob) {
                        if($i)$res.=($isArabic ? 'او ':'or ');
                        $res.='<span class="vn">'.$mob[1].'</span> ';
                        $i++;
                        $mob[1]=preg_replace('/\+/','\\+',$mob[1]);
                        $text=preg_replace('/'.$mob[1].'/','',$text);
                    }
                }

                $divider=null;
                preg_match($REGEX_MATCH,$text,$divider);
                $pos=0;
                if ($divider && count($divider) && $divider[1]) {
                    $pos = strpos($text, $divider[1]);
                    if (!$pos) {
                        $divider=null;
                        preg_match('/(<span)/', $text, $divider);
                        if ($divider && count($divider) && $divider[1]) {
                            $pos = strpos($text, $divider[1]);
                        }
                    }
                }

                if(!$pos){
                    $srh='';
                    foreach ($mobiles as $num) {
                        if($srh)$srh.='|';
                        $srh .= $num[0];
                    }
                    foreach ($phones as $num) {
                        if($srh)$srh.='|';
                        $srh .= $num[0];
                    }

                    if ($srh) {
                        $srh= preg_replace('/\+/','\\+' , $srh);
                        $divider=null;
                        preg_match('/((?:\+|)'.$srh.')/', $text, $divider);
                        if ($divider && count($divider) && $divider[1]) {
                            $pos = strpos($text, $divider[1]);
                        }
                    }
                }

                if ($pos) $text = substr($text, 0, $pos);

                if ($res) {
                    $text.=' / '.$res;
                }
            } else {*/
                foreach ($mobiles as $num) {
                    $number = $this->formatPhoneNumber($num[1]);
                     if ($num[0]!=$number) {
                         $org = $num[0];
                        $num[0]= preg_replace('/\+/','\\+' , $num[0]);                        
                        $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    } else {
                        $org = $num[0];
                        $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    }
                }
                foreach ($phones as $num) {
                    $number = $this->formatPhoneNumber($num[1]);
                     if ($num[0]!=$number) {
                         $org = $num[0];
                        $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    } else {
                        $org = $num[0];
                        $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                        $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$number.'</span>', $text);
                        $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                    }
                }

            /*}*/
        }       

        return $text;
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $phone = '/((?:\+|\s)(?:[0-9\\/\-]{7,16}))/ui';
        $content=null;
        preg_match($REGEX_CATCH, $text, $content);
        if (!($content && count($content) && $content[1])) {
            $tmpTxt=preg_replace('/\<.*?>/', '', $text);
            preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt, $content);
        }
        
        if($content && count($content) && $content[1]) {
            $str = $content[1];
            
            if ($str) {
                $nums=array();
                $numInst=array();
                $numbers = null;
                preg_match_all($phone, $str, $numbers);
                if ($numbers && count($numbers[1])) {
                    foreach ($numbers[1] as $match) {
                        $number = trim($match);
                        //var_dump($number);
                        try {
                            if($pubId==1){
                                $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                            }else{
                                $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                            }

                            if($num && $this->mobileValidator->isValidNumber($num)){
                                $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                if($rCode==$this->formatNumbers){
                                    $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                }else{
                                    $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                }
                                $nums[]=array($number, $num);
                            } else {
                                $num=$number;
                                $hasCCode = preg_match('/^\+/', $number);
                                switch($countryCode){
                                case 'SA':
                                    if($hasCCode){
                                        $num = substr($number,4);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 9:
                                                $num='011'.$num;
                                                break;
                                            case 12:
                                            case 18:
                                                    $tmp='013'.$num;
                                                    $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                    if($tmp && $this->mobileValidator->isValidNumber($tmp)){
                                                        $num='013'.$num;
                                                    }else{
                                                        $num='011'.$num;
                                                    }
                                                break;
                                        }
                                    }
                                    break;
                                case 'EG':
                                    if($hasCCode){
                                        $num = substr($number,3);
                                    }else{
                                        $num = $number;
                                    }
                                    if(strlen($num)==7){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                            case 14:
                                                $num='3'.$num;
                                                break;
                                        }
                                    }elseif(strlen($num)==8){
                                        switch($pubId){
                                            case 13:
                                                $num='2'.$num;
                                                break;
                                        }
                                    }
                                    break;
                                }

                                if($num != $number){
                                    $num = $this->mobileValidator->parse($num, $countryCode);
                                    if($num && $this->mobileValidator->isValidNumber($num)){
                                        $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                        if($rCode==$this->formatNumbers){
                                            $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                        }else{
                                            $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                        }
                                        $nums[]=array($number, $num);
                                    }else{
                                        $nums[]=array($number, $number);
                                    }
                                }else{
                                    $nums[]=array($number, $number);
                                }
                            }
                        }catch(Exception $ex){
                            $nums[]=array($number, $number);
                        }
                    }

                    if(preg_match('/\<span class/',$text)){
                        if($this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/'){
                            $mobile=array();
                            $phone=array();
                            $undefined = array();
                            $i=0;
                            foreach($nums as $num){
                                if($num[0]!=$num[1]){
                                    $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                                    if($type==1 || $type==2)
                                        $mobile[]=$num;
                                    elseif($type==0 || $type==2)
                                        $phone[]=$num;
                                    else $undefined[]=$num;
                                }else{
                                    $undefined[]=$num;
                                }
                            }

                            $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                            $res = '';
                            if(count($mobile) || count($phone)){
                                if(count($mobile)){
                                    $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                    $i=0;
                                    foreach($mobile as $mob){
                                        if($i)$res.=($isArabic ? 'او ':'or ');
                                        $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                        $matches[]=$mob[1];
                                        $i++;
                                    }
                                }

                                if(count($phone)){
                                    if($res)$res.='- ';
                                    $res.=($isArabic ? 'هاتف':'Phone').': ';
                                    $i=0;
                                    foreach($phone as $mob){
                                        if($i)$res.=($isArabic ? 'او ':'or ');
                                        $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                        $matches[]=$mob[1];
                                        $i++;
                                    }
                                }
                            }elseif(count($undefined)){
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($undefined as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="vn">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }

                            $divider=null;
                            preg_match($REGEX_MATCH, $text, $divider);
                            $pos=0;
                            //error_log(var_export($content));
                            if($divider && count($divider) && $divider[1]){
                                $pos = strpos($text, $divider[1]);
                                if(!$pos){
                                    $divider=null;
                                    preg_match('/(<span)/',$text,$divider);
                                    if($divider && count($divider) && $divider[1]){
                                        $pos = strpos($text, $divider[1]);
                                    }
                                }
                            }
                            if(!$pos){
                                $srh='';
                                foreach($nums as $num){
                                    if($srh)$srh.='|';
                                    $srh .= $num[0];
                                }
                                if($srh){
                                    $srh=  preg_replace('/\+/','\\+' , $srh);
                                    $divider=null;
                                    preg_match('/(<span class="pn">'.$srh.')/',$text,$divider);
                                    if($divider && count($divider) && $divider[1]){
                                        $pos = strpos($text, $divider[1]);
                                    }
                                }
                            }

                            if($pos)
                                $text = substr($text,0,$pos);
                            if($res)
                                $text.=' / '.$res;
                        }else{
                            foreach($nums as $num){
                                if($num[0]!=$num[1]){
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $num[0]=  preg_replace('/\//','\\/' , $num[0]);
                                    $text = preg_replace('/'.$num[0].'/', $num[1], $text);
                                    $matches[]=$num[1];
                                }else{
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $num[0]=  preg_replace('/\//','\\/' , $num[0]);
                                    $text = preg_replace('/\<span class="pn(?:[a-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$num[1].'</span>', $text);
                                }
                            }
                        }
                    }else{
                        if($this->urlRouter->publications[$pubId][6]=='http://www.waseet.net/'){
                            $mobile=array();
                            $phone=array();
                            $undefined = array();
                            $i=0;
                            foreach($nums as $num){
                                if($num[0]!=$num[1]){
                                    $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                                    if($type==1 || $type==2)
                                        $mobile[]=$num;
                                    elseif($type==0 || $type==2)
                                        $phone[]=$num;
                                    else $undefined[]=$num;
                                }else{
                                    $undefined[]=$num;
                                }
                            }

                            $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                            $res = '';
                            if(count($mobile) || count($phone)){
                                if(count($mobile)){
                                    $res.=($isArabic ? 'موبايل':'Mobile').': ';
                                    $i=0;
                                    foreach($mobile as $mob){
                                        if($i)$res.=($isArabic ? 'او ':'or ');
                                        $res.='<span class="pn o1">'.$mob[1].'</span> ';
                                        $matches[]=$mob[1];
                                        $i++;
                                    }
                                }
                                if(count($phone)){
                                    if($res)$res.='- ';
                                    $res.=($isArabic ? 'هاتف':'Phone').': ';
                                    $i=0;
                                    foreach($phone as $mob){
                                        if($i)$res.=($isArabic ? 'او ':'or ');
                                        $res.='<span class="pn o7">'.$mob[1].'</span> ';
                                        $matches[]=$mob[1];
                                        $i++;
                                    }
                                }
                            }elseif(count($undefined)){
                                $res.=($isArabic ? 'هاتف':'Phone').': ';
                                $i=0;
                                foreach($undefined as $mob){
                                    if($i)$res.=($isArabic ? 'او ':'or ');
                                    $res.='<span class="vn">'.$mob[1].'</span> ';
                                    $matches[]=$mob[1];
                                    $i++;
                                }
                            }
                            $divider=null;
                            preg_match($REGEX_MATCH,$text,$divider);
                            $pos=0;
                            if($divider && count($divider) && $divider[1]){
                                $pos = strpos($text, $divider[1]);
                                if(!$pos){
                                    $divider=null;
                                    preg_match('/(<span)/',$text,$divider);
                                    if($divider && count($divider) && $divider[1]){
                                        $pos = strpos($text, $divider[1]);
                                    }
                                }
                            }

                            if(!$pos){
                                $srh='';
                                foreach($nums as $num){
                                    if($srh)$srh.='|';
                                    $srh .= $num[0];
                                }
                                if($srh){
                                    $srh = preg_replace('/\+/', '\\+' , $srh);
                                    $srh = preg_replace('/\//', '\\/', $srh);
                                    $divider=null;

                                    preg_match('/((?:\+|)'.$srh.')/u', $text, $divider);

                                    if($divider && count($divider) && $divider[1]){
                                        $pos = strpos($text, $divider[1]);
                                    }
                                }
                            }

                            if($pos)
                                $text = substr($text, 0, $pos);

                            if($res){
                                $text.=' / '.$res;
                            }
                        }else{
                            foreach($nums as $num){
                                if($num[0]!=$num[1]){
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $num[0]=  preg_replace('/\\\([0-9])/','$1' , $num[0]);
                                    $num[0]=  preg_replace('/\/.*/','' , $num[0]);
                                    $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$num[1].'</span>', $text);
                                    $matches[]=$num[1];
                                }else{
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $num[0]=  preg_replace('/\\\([0-9])/','$1' , $num[0]);
                                    $num[0]=  preg_replace('/\/.*/','' , $num[0]);
                                    $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$num[1].'</span>', $text);

                                }
                            }
                        }
                    }
                }
            
            }
        }
        if(isset($email[0]) && !preg_match('/'.$email[0].'/',$text)){
            $text .= '/ '.($isArabic ? 'بريد االكتروني':'Email').': <span>'.$email[0].'</span>';
        }
        $text = preg_replace('/(?:\/(?:\s+|)\/)|(?:-(?:\s+)\/)/','/',$text);
        return $text;
    }
    
    
    function renderMobileFeature(){       
        if (!isset($this->searchResults['zone2']) || $this->searchResults['zone2']['total_found']==0) {
            return;
        }
        //var_export($this->searchResults['zone2']);
        $ad_count = count($this->searchResults['zone2']['matches']);
        $ad_cache = $this->urlRouter->db->getCache()->getMulti($this->searchResults['zone2']['matches']);
        if (!isset($this->stat['ad-imp']))
            $this->stat['ad-imp'] = array();
        
        for ($ptr = 0; $ptr < $ad_count; $ptr++) {
            $id = $this->searchResults['zone2']['matches'][$ptr];
            $ad = $this->classifieds->getById($id,false,$ad_cache);
            //if (isset($ad[Classifieds::ID]) ) {
            if (isset($ad[Classifieds::ID]) && ( $ad[Classifieds::USER_ID]!=220906 || $ad[Classifieds::ID]==6889499 || ($ptr == $ad_count-1) )) {
                $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);

                //$this->processTextNumbers($ad[Classifieds::CONTENT],$ad[Classifieds::PUBLICATION_ID],$ad[Classifieds::COUNTRY_CODE]);
                /*if ($ad[Classifieds::PUBLICATION_ID]==1) {
                    if (isset($this->user->info['level'])) {
                        if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                            //if(isset($this->featuredResults["matches"][$id])){
                            $this->stat['ad-imp'][]=$id;
                            //}
                        }
                    } else {
                        $this->stat['ad-imp'][]=$id;
                    }
                }*/
                if (!empty($ad[Classifieds::ALT_CONTENT])) {
                    if ($this->urlRouter->siteLanguage == "en" && $ad[Classifieds::RTL]) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 0;
                        $this->appendLocation = false;
                    } elseif ($this->urlRouter->siteLanguage == "ar" && $ad[Classifieds::RTL] == 0) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 1;
                        $this->appendLocation = false;
                    }
                }
                    //$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                $pic = '';
                $textPlacement = 0;//no picture
                    /*if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                        $picCount='';
                        if (isset($ad[Classifieds::PICTURES]) && is_array($ad[Classifieds::PICTURES]) && count($ad[Classifieds::PICTURES])) {
                            $picCount='<span class=\"cnt\">'.count($ad[Classifieds::PICTURES]).'</span>';
                        }
                        $pic = $ad[Classifieds::VIDEO][2];
                        //$this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img src=\"' . $pic . '\" /><span class=\"play\"></span>'.$picCount.'";';
                        //$pic = '<span class="thb"></span>';
                        $pic = '<img src="' . $pic . '" />'.$picCount;
                        //$liClass.=' pic';
                    } else*/
                    if ($ad[Classifieds::PICTURES] && is_array($ad[Classifieds::PICTURES])  && count($ad[Classifieds::PICTURES])) {
                        $z=0;
                        $bestPicIdx=0;
                        $bestWidth=320;
                        $width = 0;
                        $height = 0;
                        $textPlacement = 1;//1 over//2 side
                        foreach ($ad[Classifieds::PICTURES] as $pic){
                            if (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) {
                                if($ad[Classifieds::PICTURES_DIM][$z][0] > $this->user->params['screen'][0]){
                                    $bestWidth = $this->user->params['screen'][0];
                                }
                                if($ad[Classifieds::PICTURES_DIM][$z][0] >= $bestWidth){
                                    $width = $bestWidth;
                                    $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                                }else{
                                    $width = $bestWidth / 2;
                                    $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                                }
                                if($height > 300){
                                    $textPlacement = 2;
                                    $height = 300;
                                    $width = floor( $height*$ad[Classifieds::PICTURES_DIM][$z][0]/$ad[Classifieds::PICTURES_DIM][$z][1]);
                                }
                                if($width > $this->user->params['screen'][0]*0.66){
                                    $textPlacement = 1;
                                }
                                if($width < $this->user->params['screen'][0]/2){                                    
                                    $textPlacement = 2;
                                }
                            }else{
                                if($ad[Classifieds::PICTURES_DIM][$z][0] >= $bestWidth){
                                    $width = $bestWidth;
                                    $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                                }else{
                                    $width = $bestWidth / 2;
                                    $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                                }
                                if($height > 300){
                                    $textPlacement = 2;
                                    $height = 300;
                                    $width = floor( $height*$ad[Classifieds::PICTURES_DIM][$z][0]/$ad[Classifieds::PICTURES_DIM][$z][1]);
                                }
                            }
                            break;
                            $z++;
                        }
                        $picCount=count($ad[Classifieds::PICTURES]);
                        $pic = $ad[Classifieds::PICTURES][0];
                        if($this->urlRouter->isAcceptWebP){
                            $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                        }
                        //$this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img src=\"' . $this->urlRouter->cfg['url_ad_img'] . '/repos/s/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'</span>";';
                        //$pic = '<span class="thb"></span>';
                        $pic = '<img style="width:'.$width.'px;height:'.$height.'px" src="' . $this->urlRouter->cfg['url_ad_img'] . '/repos/m/' . $pic . '" />';
                        //$liClass.=' pic';
                    } else {
                        //$this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img class=\"d\" src=\"' . $this->urlRouter->cfg['url_img'] . '/90/' . $ad[Classifieds::SECTION_ID] . '.png\" />";';
                        //$pic = '<span class="thb"></span>';
                        $pic = '<img class="d" src="' . $this->urlRouter->cfg['url_img'] . '/90/' . $ad[Classifieds::SECTION_ID] . $this->urlRouter->_png . '" />';
                        //$liClass.=' pic';
                    }
                $textClass = ($ad[Classifieds::RTL]) ? "ar" : "en";                

                $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == "ar" ? "" : "{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]).'?ref=mediaside';

                ?><div id="<?= $ad[Classifieds::ID] ?>" onclick="wo('<?= $_link ?>')" class="prem"><?php
                if($textPlacement == 0){         
                    ?><div class="hdr pdf"><?php 
                    echo $this->getFeatureAdSection($ad);
                    ?><div><span class="ic r102"></span><?= $this->lang['premium_ad_dt'] ?></div><?php
                    ?></div><?php  
                    ?><p class="<?=  $textClass ?> pdf"><?php 
                        echo $pic . $ad[Classifieds::CONTENT];
                    ?></p><?php 
                }elseif($textPlacement == 1){        
                    ?><div class="hdr"><?php 
                    echo $this->getFeatureAdSection($ad);
                    ?><div><span class="ic r102"></span><?= $this->lang['premium_ad_dt'] ?></div><?php
                    ?></div><?php            
                    echo $pic; 
                    ?><p class="btm <?=  $textClass ?>"><?php 
                        echo $ad[Classifieds::CONTENT];
                    ?></p><?php 
                }else{                 
                    ?><div class="hdr sde"><?php 
                    echo $this->getFeatureAdSection($ad);
                    ?><div><span class="ic r102"></span><?= $this->lang['premium_ad_dt'] ?></div><?php
                    ?></div><?php
                    ?><p class="sde <?=  $textClass ?>"><?php       
                        echo $pic; 
                        echo $ad[Classifieds::CONTENT];
                    ?></p><?php 
                }
                ?></div><?php
                
                break;
            }
        }
    }
    
    function renderSideFeatures(){
        if (!isset($this->searchResults['zone2']) || $this->searchResults['zone2']['total_found']==0) {
            return;
        }
               
        $ad_count = count($this->searchResults['zone2']['matches']);
        $ad_cache = $this->urlRouter->db->getCache()->getMulti($this->searchResults['zone2']['matches']);
        if (!isset($this->stat['ad-imp']))
            $this->stat['ad-imp'] = array();
        
        for ($ptr = 0; $ptr < $ad_count; $ptr++) {
            $id = $this->searchResults['zone2']['matches'][$ptr];
            $ad = $this->classifieds->getById($id,false,$ad_cache);
            //if (isset($ad[Classifieds::ID]) {
            if (isset($ad[Classifieds::ID]) && ( $ad[Classifieds::USER_ID]!=220906 || $ad[Classifieds::ID]==6889499 || ($ptr == $ad_count-1) )) {
                $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);

                //$this->processTextNumbers($ad[Classifieds::CONTENT],$ad[Classifieds::PUBLICATION_ID],$ad[Classifieds::COUNTRY_CODE]);
                if ($ad[Classifieds::PUBLICATION_ID]==1) {
                    if (isset($this->user->info['level'])) {
                        if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                            //if(isset($this->featuredResults["matches"][$id])){
                            $this->stat['ad-imp'][]=$id;
                            //}
                        }
                    } else {
                        $this->stat['ad-imp'][]=$id;
                    }
                }
                if (!empty($ad[Classifieds::ALT_CONTENT])) {
                    if ($this->urlRouter->siteLanguage == "en" && $ad[Classifieds::RTL]) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 0;
                        $this->appendLocation = false;
                    } elseif ($this->urlRouter->siteLanguage == "ar" && $ad[Classifieds::RTL] == 0) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 1;
                        $this->appendLocation = false;
                    }
                }
                    //$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                    $cpn='';
                    if ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) $cpn='<span class=\"cnt\">'.count($ad[Classifieds::PICTURES]).'<span class=\"i sp\"></span></span>';
                    $pic = $ad[Classifieds::VIDEO][2];
                    $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img height=\"98\" src=\"' . $pic . '\" /><span class=\"play\"></span>'.$cpn.'";';
                    $pic = '<span class="ig"></span>';
                } elseif (isset($ad[Classifieds::PICTURES]) && $ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                    $rand = rand(0,count($ad[Classifieds::PICTURES])-1);
                    $hasAnimation = 0;
                    if($ad[Classifieds::PICTURES_DIM][$rand][0] > $ad[Classifieds::PICTURES_DIM][$rand][1]){
                        $hasAnimation = 1;
                    }
                    $pic = $ad[Classifieds::PICTURES][$rand];
                    if($this->urlRouter->isAcceptWebP){
                        $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                    }
                    $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img height=\"98\" src=\"'.$this->urlRouter->cfg['url_ad_img'].'/repos/d/' . $pic . '\" />";';
                    $pic = '<span class="ig"></span>';
                } else {
                    $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img height=\"90\" class=\"ir\" src=\"'.$this->urlRouter->cfg['url_img'].'/200/' . $ad[Classifieds::SECTION_ID] . $this->urlRouter->_png . '\" />";';
                    $pic = '<span class="ig igr"></span>';
                }
                $textClass = ($ad[Classifieds::RTL]) ? "ar" : "en";                

                $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == "ar" ? "" : "{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]).'?ref=mediaside';
                ?><li onclick="wo('<?= $_link ?>')" id="sideFtr" class="ls lsf"><div id="sf<?= $ad[Classifieds::ID] ?>" class="drg"> <!--googleoff: index --> <p class="ani <?=  $textClass ?>"><?php 
                echo $pic;
                echo $ad[Classifieds::CONTENT];
                ?><span class="crd"><span class="vpdi <?= $this->urlRouter->siteLanguage ?>"></span><?= $this->lang['premium_ad'] ?></span></p> <!--googleon: index --> </div></li><?php 
                
                break;
            }
        }
    }
    

    function mergeResults(&$topFeatureCount, &$ad_keys) {
        if (isset($this->searchResults['zone1'])) {
            $ad_keys = $this->searchResults['zone1']['matches'];
        }
        
        if ($this->searchResults['body']['total_found']>20 && count($ad_keys)<2 && isset($this->searchResults['zone0'])) {
            $count=count($this->searchResults['zone0']['matches']);
            $fc=count($ad_keys);
            for ($i=0; $i<$count; $i++) {
                if (($fc+$i)<2) {
                    $ad_keys[] = $this->searchResults['zone0']['matches'][$i];
                } else break;
            }
        }
        $topFeatureCount = count($ad_keys);
        $count=count($this->searchResults['body']['matches']);
        for ($i=0;$i<$count;$i++) {
            if (!in_array($this->searchResults['body']['matches'][$i], $ad_keys)) {
                $ad_keys[] = $this->searchResults['body']['matches'][$i];
            }
        }
    }
    
    
    function renderDResults($keywords) 
    {
        // print_r($this->searchResults['body']['scores']);
        // die('found');
        
        $idx = 0;
        $ad_keys = array();
        $this->mergeResults($topFeatureCount, $ad_keys);       
        $current_time = time();
        
        $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);

        $ad_count = count($ad_keys);
        if (!isset($this->stat['ad-imp']))
            $this->stat['ad-imp'] = array();   
        
        if(!isset($this->user->params['feature'])) 
        {
            $this->user->params['feature']=array();
        }
        
        if($ad_count && ( (isset($_GET['cmp']) && is_numeric($_GET['cmp'])) || (isset($_GET['aid']) && is_numeric($_GET['aid']))) )
        {
            $aid = isset($_GET['cmp']) ? $_GET['cmp'] : $_GET['aid'];
            $ad = $this->user->getPendingAds($aid);
            if (!empty($ad)) 
            {
                $ad=$ad[0];
                $content=json_decode($ad['CONTENT'],true);
                $clang = $content['rtl'] ? 'ar' : 'en';
                ?><li style="height:auto;background-image:none;background-color:#FFF;width:300px;position:fixed;top:160px;left:20px;z-index:100000;border:5px solid #000"><?php
                ?><p class="<?= $clang ?>" style="height:auto"><?= $content['other'] ?></p><?php 
                if(isset($content['altother']) && $content['altother']!=''){
                    $clang = $content['altRtl'] ? 'ar' : 'en';
                    ?><p class="<?= $clang ?>" style="height:auto;margin-top: 5px;border-top: 1px solid #999;padding: 5px;"><?= $content['altother'] ?></p><?php 
                }
                ?><div class="tbs" style="margin-top: 5px;padding: 0 5px;border-top: 1px solid #ccc;line-height: 30px;height: 30px;background-color: #bdc9dc;overflow: hidden;color: #333;"><?= $this->getAdCmpSection($ad) ?></div><?php
                ?></li><?php
            }
        }
        
        for ($ptr = 0; $ptr < $ad_count; $ptr++) 
        {
            $id = $ad_keys[$ptr];
            
            $feature=false;
            $paid=false;
            
            if($topFeatureCount){
                $topFeatureCount--;
                if(isset($this->searchResults['zone1']) && in_array($id, $this->searchResults['zone1']['matches'])){
                    $feature=true;
                    $paid=true;
                }elseif(in_array($id, $this->searchResults['zone0']['matches'])){
                    $this->user->params['feature'][]=$id;
                    $feature=true;
                }
            }else{
                if(isset($this->searchResults['zone1']) && in_array($id, $this->searchResults['zone1']['matches'])) continue;
            }
            $this->user->update();
            
            $hasDetail = true;

            $ad = $this->classifieds->getById($id,false,$ad_cache);
            $pic = null;
            $this->appendLocation = true;
            if (isset($ad[Classifieds::ID]) ) {
                if ($ad[Classifieds::PUBLICATION_ID]==1 && !(isset($this->detailAd[Classifieds::ID]) && $this->detailAd[Classifieds::ID]==$ad[Classifieds::ID]) ) {
                    if (isset($this->user->info['level'])) {
                        if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                            $this->stat['ad-imp'][]=$id;
                        }
                    } else {
                        //if(isset($this->featuredResults["matches"][$id])){
                        $this->stat['ad-imp'][]=$id;
                        //}elseif(isset($this->searchResults["matches"][$id])){
                        //    $this->stat['ad-imp'][]=$id;
                        //}
                    }
                }

                if (!empty($ad[Classifieds::ALT_CONTENT])) {
                    $langSortIdx = $this->langSortingMode > -1 ? $this->langSortingMode : 0;
                    if ( ($langSortIdx == 2 || $this->urlRouter->siteLanguage == "en") && $ad[Classifieds::RTL]) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 0;
                        $this->appendLocation = false;
                    } elseif ( ($langSortIdx == 1 || $this->urlRouter->siteLanguage == "ar") && $ad[Classifieds::RTL] == 0) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 1;
                        $this->appendLocation = false;
                    }
                }
                    //$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                
                $itemScope = '';
                $itemDesc = '';
                $hasSchema = false;
                if ($ad[Classifieds::ROOT_ID] == 1) {
                    $hasSchema = true;
                    $itemDesc = 'itemprop="description" ';
                    $itemScope = ' itemscope itemtype="https://schema.org/Product"';
                } elseif ($ad[Classifieds::ROOT_ID] == 2) {
                    $hasSchema = true;
                    $itemDesc = 'itemprop="description" ';
                    $itemScope = ' itemscope itemtype="https://schema.org/Product"';
                } elseif ($ad[Classifieds::ROOT_ID] == 3) {
                    if ($ad[Classifieds::PURPOSE_ID] == 3) {
                        $itemDesc = 'itemprop="description" ';
                        $itemScope = ' itemscope itemtype="https://schema.org/JobPosting"';
                    } elseif ($ad[Classifieds::PURPOSE_ID] == 4) {
                        $itemDesc = 'itemprop="description" ';
                        $itemScope = ' itemscope itemtype="https://schema.org/Person"';
                    }
                }
                
                $isFeatured = $current_time < $ad[Classifieds::FEATURE_ENDING_DATE];
                $isFeatureBooked = $current_time < $ad[Classifieds::BO_ENDING_DATE];

                $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == "ar" ? "" : "{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]);               

                $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);
                
                //$this->processTextNumbers($ad[Classifieds::CONTENT],$ad[Classifieds::PUBLICATION_ID],$ad[Classifieds::COUNTRY_CODE]);

                if ($ad[Classifieds::PUBLICATION_ID] != 1) {
                    $fieldIndex = 1;
                    if (!$ad[Classifieds::RTL])
                        $fieldIndex = 2;
                    if ($ad[Classifieds::ROOT_ID] == 99) {
                        if($ad[Classifieds::PURPOSE_ID]!=999) {
                            $purpose = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex];
                            $lookup = preg_replace('/ /', '|', trim($purpose));
                            if (!preg_match('/' . $lookup . '/', $ad[Classifieds::CONTENT])) {
                                $ad[Classifieds::CONTENT] = $purpose . ' - ' . $ad[Classifieds::CONTENT];
                            }
                        }
                    } elseif ($ad[Classifieds::ROOT_ID] == 3 || in_array($ad[Classifieds::SECTION_ID], array(
                                106, //sharing estate
                                105, //various estate
                                5, //shops
                                121, //home
                                6, //offices
                                3, //villas and houses
                                122, //studios
                                117, //various cars
                            ))) {
                        //do nothing
                    } else {
                        if ($ad[Classifieds::RTL]) {
                            $naming = $this->urlRouter->db->queryCacheResultSimpleArray(
                                    "naming_ar_{$ad[Classifieds::COUNTRY_ID]}_{$ad[Classifieds::SECTION_ID]}", "select single, plural, description
                                     from naming
                                     where naming.TYPE_ID=2
                                     and naming.ORIGIN_ID={$ad[Classifieds::SECTION_ID]}
                                     and naming.LANG='ar'
                                     and (country_id={$ad[Classifieds::COUNTRY_ID]} or country_id=0)
                                     order by naming.COUNTRY_ID desc", null, -1, $this->urlRouter->cfg['ttl_long']);
                        } else {
                            $naming = $this->urlRouter->db->queryCacheResultSimpleArray(
                                    "naming_en_{$ad[Classifieds::COUNTRY_ID]}_{$ad[Classifieds::SECTION_ID]}", "select single, plural, description
                                     from naming
                                     where naming.TYPE_ID=2
                                     and naming.ORIGIN_ID={$ad[Classifieds::SECTION_ID]}
                                     and naming.LANG='en'
                                     and (country_id={$ad[Classifieds::COUNTRY_ID]} or country_id=0)
                                     order by naming.COUNTRY_ID desc", null, -1, $this->urlRouter->cfg['ttl_long']);
                        }
                        if ($naming) {
                            $lookup = preg_replace('/ /', '|', trim($naming[0])) . '|' . preg_replace('/ /', '|', trim($naming[1]));
                            if (preg_match('/' . $lookup . '/', $ad[Classifieds::CONTENT])) {
                                if($ad[Classifieds::PURPOSE_ID]!=999) {
                                    $purpose = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex];
                                    $lookup = preg_replace('/ /', '|', trim($purpose));
                                    if (!preg_match('/' . $lookup . '/', $ad[Classifieds::CONTENT])) {
                                        $ad[Classifieds::CONTENT] = $purpose . ' - ' . $ad[Classifieds::CONTENT];
                                    }
                                }
                            } else {
                                if($ad[Classifieds::PURPOSE_ID]!=999) {
                                    $puReplace = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex];
                                    $ad[Classifieds::CONTENT] = preg_replace('/' . $puReplace . '/i', '', $ad[Classifieds::CONTENT]);
                                }
                                switch ($ad[Classifieds::PURPOSE_ID]) {
                                    case 1:
                                    case 2:
                                    case 8:
                                        $ad[Classifieds::CONTENT] = $naming[0] . ' ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex] . ' - ' . $ad[Classifieds::CONTENT];
                                        break;
                                    case 999:
                                        $ad[Classifieds::CONTENT] = $naming[0] . ' - ' . $ad[Classifieds::CONTENT];
                                        break;
                                    case 6:
                                    case 7:
                                        $ad[Classifieds::CONTENT] = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex] . ' ' . $naming[0] . ' - ' . $ad[Classifieds::CONTENT];
                                        break;
                                    case 3:
                                        if ($this->urlRouter->siteLanguage == 'ar') {
                                            $ad[Classifieds::CONTENT] = 'مطلوب ' . $naming[0] . ' - ' . $ad[Classifieds::CONTENT];
                                        } else {
                                            $ad[Classifieds::CONTENT] = $naming[0] . ' ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex] . ' - ' . $ad[Classifieds::CONTENT];
                                        }
                                        break;
                                    case 4:
                                        $in = ' ';
                                        if ($this->urlRouter->siteLanguage == "en")
                                            $in = ' ' . $this->lang['in'] . ' ';
                                        $ad[Classifieds::CONTENT] = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$fieldIndex] . $in . $naming[0] . ' - ' . $ad[Classifieds::CONTENT];
                                        break;
                                    case 5:
                                        $ad[Classifieds::CONTENT] = $naming[0] . ' - ' . $ad[Classifieds::CONTENT];
                                        break;
                                }
                            }
                        }
                    }
                }

                $l_inc = 2;
                $in = 'in';
                if ($ad[Classifieds::RTL]) {
                    $l_inc = 1;
                    $in = "في";
                }
                //$locIcon = '';
                $pub_link = '';
                if (!$this->urlRouter->userId && isset($this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]])) {
                    $pub_link = $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
                    if ($ad[Classifieds::PUBLICATION_ID] == 1 || $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][6]=='http://www.waseet.net/') {
                        $pub_link = '<div class="crd ' . ($ad[Classifieds::RTL] ? 'l' : 'r') . '"><b st="' . $ad[Classifieds::UNIXTIME] . '"></b> ' . ($this->urlRouter->siteLanguage=='ar' ? 'موقع مرجان':'mourjan.com') . '</div>';
                    } else {
                        $pub_link = $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
                        if ($ad[Classifieds::OUTBOUND_LINK])
                            $pub_link = '<div class="crd ' . ($ad[Classifieds::RTL] ? 'l' : 'r') . '"><b st="' . $ad[Classifieds::UNIXTIME] . '"></b> ' . $pub_link . '</div>';
                        elseif ($ad[Classifieds::PUBLICATION_ID])
                            $pub_link = '<div class="crd ' . ($ad[Classifieds::RTL] ? 'l' : 'r') . '"><b st="' . $ad[Classifieds::UNIXTIME] . '"></b> ' . $pub_link . '</div>';
                    }
                }
            
                $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit'] < $ad[Classifieds::UNIXTIME]);
                $textClass = "en";
                $liClass = "";
                $newSpan = "";
                if ($isNewToUser) {
                    $newSpan.="<span class='i nw'></span>";
                }

                if ($this->urlRouter->module == 'detail') {
                    if ($this->urlRouter->id == $ad[Classifieds::ID]) {
                        $liClass.="on ";
                    }
                }
                if ($ad[Classifieds::RTL]) {
                    $textClass = "ar";
                }
                if ($this->urlRouter->siteTranslate)
                    $textClass = '';

                $numOfRowsToRenderWithImgs = 3;
                if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                    $cpn='';
                    if ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) $cpn='<span class=\"cnt\">'.count($ad[Classifieds::PICTURES]).'<span class=\"i sp\"></span></span>';
                    $pic = $ad[Classifieds::VIDEO][2];
                    if($idx < $numOfRowsToRenderWithImgs){
                        $pic = '<span class="igz"><img width="120" height="93" src="' . $pic . '" /><span class="play"></span>'.$cpn.'</span>';
                    }else{
                        $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img width=\"120\" height=\"93\" src=\"' . $pic . '\" /><span class=\"play\"></span>'.$cpn.'";';
                        $pic = '<span class="ig"></span>';
                    }
                } elseif (isset($ad[Classifieds::PICTURES]) && $ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                    $pic = $ad[Classifieds::PICTURES][0];
                    if($this->urlRouter->isAcceptWebP){
                        $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                    }
                    if($idx < $numOfRowsToRenderWithImgs){
                        $pic = '<span class="igz"><img width="120" src="'.$this->urlRouter->cfg['url_ad_img'].'/repos/s/' . $pic . '" /><span class="cnt">'.count($ad[Classifieds::PICTURES]).'<span class="i sp"></span></span></span>';
                    }else{
                        $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img width=\"120\" src=\"'.$this->urlRouter->cfg['url_ad_img'].'/repos/s/' . $pic . '\" /><span class=\"cnt\">'.count($ad[Classifieds::PICTURES]).'<span class=\"i sp\"></span></span>";';
                        $pic = '<span class="ig"></span>';
                    }
                } else {
                    if($idx < $numOfRowsToRenderWithImgs){
                        $pic = '<span class="igz"><img class="ir" src="'.$this->urlRouter->cfg['url_img'].'/90/' . $ad[Classifieds::SECTION_ID] . $this->urlRouter->_png . '" /></span>';
                    }else{
                        $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img class=\"ir\" src=\"'.$this->urlRouter->cfg['url_img'].'/90/' . $ad[Classifieds::SECTION_ID] . $this->urlRouter->_png . '\" />";';
                        $pic = '<span class="ig"></span>';
                    }
                }

                $ad[Classifieds::CONTENT] = preg_replace('/www(?!\s+)\.(?!\s+).*(?!\s+)\.(?!\s+)(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)/', '', $ad[Classifieds::CONTENT]);

                $feed = $this->BuildExcerpts($ad[Classifieds::CONTENT], 160);
                if (substr($feed, -3) == '...') {
                    $replaces = 0;
                    $feed = preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/', '$1' . ($this->urlRouter->id == $ad[Classifieds::ID] ? '' : '<span class="lnk">' . ($ad[Classifieds::RTL] ? $this->lang['readMore_ar'] : $this->lang['readMore_en']) . '</span>'), $feed, -1, $replaces);
                    if (!$replaces && $this->urlRouter->id != $ad[Classifieds::ID])
                        $feed.='<span class="lnk"> ' . ($ad[Classifieds::RTL] ? $this->lang['readMore_ar'] : $this->lang['readMore_en']) . '</span>';
                }
                if ($this->user->isSuperUser() && isset($this->searchResults['body']['scores'][$ad[Classifieds::ID]]))
                {
                    $feed.="<span class='kq'>".$this->searchResults['body']['scores'][$ad[Classifieds::ID]]."</span>";
                }
                $ad[Classifieds::CONTENT] = $feed; 

                $__link = $_link;
                $favLink = '';

                if ($this->user->info['id']) {
                    if($this->userFavorites){
                        $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                        $liClass.= 'fon ';
                    }elseif ($this->user->favorites) {
                        if (in_array($ad[Classifieds::ID], $this->user->favorites)) {
                            $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                            $liClass.= 'fon ';
                        }
                    }
                }
                if($isFeatureBooked){
                    $liClass.=' vpz';
                }
if($isFeatured){
                        $liClass.=' vp vpd';
                    }else{
                if($feature){
                    $liClass.=' vp';
                }
                if($paid){
                    $liClass.=' vpd';
                }
}
                if ($liClass)
                    $liClass = "class='" . trim($liClass) . "'";
                if(!isset($ad[Classifieds::ID])){
                    error_log("#####################################");
                    error_log($id);
                    error_log("#####################################");
                }
                ?><li id="<?= $ad[Classifieds::ID] ?>" itemprop="itemListElement" <?= $liClass . $itemScope ?>><?php
                              

                    $ccmDiv = $this->getAdSection($ad, $hasSchema);

                    ?><p<?= $this->urlRouter->id == $ad[Classifieds::ID] ? '' : ' onclick="wo(\'' . $__link . '\')"' ?> <?= $itemDesc ?>class='<?= $textClass ?>'><?php
                    if ($pic) {
                        echo $pic;
                    }
                    echo $ad[Classifieds::CONTENT];
                    ?></p><?php
                if($feature||$isFeatured) {
                    echo $newSpan;
                    if($paid||$isFeatured){
                        echo '<span class="crd">'.$this->lang['premium_ad'].'<span class="vpdi '.$this->urlRouter->siteLanguage.'"></span></span>';
                    }
                }else{
                    echo $newSpan;
                    echo $pub_link;
                }
                ?><div class="cct"><?php
                    echo $ccmDiv;
                    if( isset($ad[Classifieds::PUBLISHER_TYPE]) && in_array($ad[Classifieds::ROOT_ID],[1,2,3]) && 
                            ($ad[Classifieds::ROOT_ID]!= 3 || ($ad[Classifieds::ROOT_ID]== 3 && $ad[Classifieds::PURPOSE_ID] == 3))){
                        switch($ad[Classifieds::PUBLISHER_TYPE]){
                            case 3:
                                echo '<div class="ms ut" value="a'.$ad[Classifieds::ROOT_ID].'"><span class="i i'.$ad[Classifieds::ROOT_ID].'"></span> '.$this->lang['pub_3_'.$ad[Classifieds::ROOT_ID]].'</div>';
                                break;
                            case 1:
                                if($ad[Classifieds::ROOT_ID] == 3){
                                    echo '<div class="ms ut" value="p1"><span class="i p"></span> '.$this->lang['bpub_1'].'</div>';
                                }else {
                                    echo '<div class="ms ut" value="p0"><span class="i p"></span> '.$this->lang['pub_1'].'</div>';
                                }
                                break;
                            default:
                                //echo '<div class="ms ut"><span class="i i1"></span> '.$this->lang['pub_1'].'</div>';
                                break;
                        }
                    }
                    echo $favLink;
                    ?></div><?php
                    ?></li><?php
                $idx++;
            }
        }
        $this->user->update();
    }
    
    
  
    function priceResults(){
        ?><div class="rs"><?php
            echo $this->summerizeSearch();
            $this->renderSideSections();
            $car = $this->extended[$this->extendedId][1];
            $priceRange = $this->urlRouter->db->getSectionPriceRange($this->urlRouter->countryId,$this->extendedId,1);
            ?><p class="ph"><?= preg_replace('/{TAG}/',  '<b>'.$car.'</b>', $this->lang['price_note']).'<b>'.$this->countryName.'</b>' ?></p><?php
            ?><ul><?php
            foreach($priceRange as $yr => $pr){
                ?><li><?= $car.' '.$yr ?></li><?php
            }
            ?></ul><?php
        ?></div><?php
    }

    
    function results() {       
        $keywords = '';
//        if (isset($this->searchResults['words']))
//            $keywords = implode(' ', array_keys($this->searchResults['words']));
        /*
          $this->globalScript.='var sic=[];var llk=function(e){var p=e.parentNode.parentNode;var e=p.firstChild;if(!e.href)e=e.firstChild;if(e.href)document.location=e.href+"?map=on"};';
          if (isset($this->user->info['level']) && $this->user->info['level']==9) {
          $this->globalScript.='var rpa=function(id,e){if(confirm("Hold this ad?")){e=$(e);if (!e.hasClass("loading")){e.addClass("loading");$.ajax({type:"POST",url:"/ajax-report/",data:{id:id},dataType:"json",success:function(rp){if(rp.RP){e.click=function(){};e.css("background", "0");e.html("Done")}e.removeClass("loading")},error:function(rp){e.removeClass("loading")}})}}};';
          }else {
          $this->globalScript.='var abf;var rpa=function(id,e){if(!abf){abf=$("<div class=\"lif rc\"><h4>'.$this->lang['abuseTitle'].'</h4><textarea></textarea><input type=\"button\" onclick=\"rpa_s(this)\" class=\"bt rc\" value=\"'.$this->lang['send'].'\" /><input type=\"button\" onclick=\"rpa_c(this)\" class=\"bt rc\" value=\"'.$this->lang['cancel'].'\" /></div>")};abf.attr("id",id);$(e.parentNode.parentNode).append(abf);};var rpa_c=function(e){e=$(e);var t=e.prev().prev();t.attr("value","");t.removeClass("err");e.parent().remove()};var rpa_s=function(e){e=$(e);var msg=e.prev().attr("value");if(msg.length>10){$.ajax({type:"POST",url:"/ajax-report/",data:{id:e.parent().attr("id"),msg:msg},dataType:"json"});$(".ab",e.parent().parent()).remove();e.parent().replaceWith("<div class=\"lif nb rc\">'.$this->lang['abuseReported'].'</div>");e.prev().attr("value","")}else{e.prev().addClass("err")}};';
          }
          $this->inlineScript.='if(sic.length){$(".sim").each(function(i,e){$(e).replaceWith(sic[e.id])})};';
         */
        if (!$this->userFavorites && $this->urlRouter->module != 'detail')
            $this->updateUserSearchCriteria();
        
        
        
        if(isset($_GET['cmp']) && $this->user->isSuperUser() && $this->urlRouter->params['q']){
            $filename = '/var/log/mourjan/keywords.log';
            preg_match('/\s(.*)$/ui', $this->urlRouter->params['q'],$matches);
            if(isset($matches[1]) && $matches[1]){
                $handle = fopen($filename,'a');
                if($handle){
                    $keywords = $matches[1];
                    fwrite($handle, $keywords."\n");
                    fclose($handle);
                }
            }
        }
        
        
        ?><div id="results" class="rs"><?php
            $hasResults = $this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0;

            //echo $this->renderNotifications();

            if ($this->urlRouter->watchId && $this->searchResults === false) {
                ?><p class="ph phb"><?php 
                    echo $this->lang['noSubscribeList'];
                ?></p><?php
                ?><div class="sid"><?php
                ?><p class="phc"><?= $this->lang['watchBenefit'] ?></p><?php
                ?><span class="naf"></span><?php
                ?></div><?php
                ?><div class="nad"><p><?php
                    echo $this->lang['toAddToList'];
                    ?></p><br /><?php
                    ?><img height="143" width="480" src="<?= $this->urlRouter->cfg['url_css_mobile'] . '/i/t/watch' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') ?>0<?= $this->urlRouter->_jpg ?>" /><br /><?php
                    ?><p><?= $this->lang['then'] ?></p><?php
                    ?><img height="165" width="480" src="<?= $this->urlRouter->cfg['url_css_mobile'] . '/i/t/watch' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') ?>1<?= $this->urlRouter->_jpg ?>" /><br /><br /><?php
                    ?><a class="bt" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) ?>"><span class="i h"></span><?= $this->lang['noFavBackLink'] ?></a><?php
                ?></div><?php
                                                
                        
                /*?><div class="io"><?php
                ?><h1><?= $this->lang['w_title'] ?></h1><?php
                ?><p><span class="ybx"></span><?= $this->lang['w_desc'] ?><?php
                ?><?= $this->lang['w_content'] ?><?php
                ?></p><?php
                ?><h2 class="ctr"><?= $this->lang['w_slogan'] ?></h2><?php
                ?><div class="som"></div><?php
                ?></div><?php*/
                //$this->renderBottomMen();
            } else {
                /*
                if ($this->urlRouter->userId && $this->urlRouter->userId == $this->user->info['id'] && $this->user->info['email'] && $this->partnerInfo['title']) {
                    ?><div class="na sh una"><?php
                    ?><b><?= $this->lang['chooseUri'] ?></b><?php
                    ?><div class="en" id="uriF">http://www.mourjan.com/<input type="text" value="" /><input onclick="svU(this)" type="button" class="bt rc" value="<?= $this->lang['save'] ?>" /></div><?php
                    ?><div id="uriN" class="loading"><?= $this->lang['uriCheck'] ?></div><?php
                    ?><div id="uriC"></div><?php
                    ?><b class="bn"><?= $this->lang['uriNotice'] ?></b><?php
                    ?></div><?php
                     $this->globalScript.='var svU=function(e){
                      e=$(e);
                      var i=e.prev();
                      var v=i.attr("value");
                      if (v){
                      v=v.toLowerCase();
                      var d=e.parent();
                      d.css("display","none");
                      var l=d.next();
                      var n=d.next().next();
                      if (v.match(/[^a-z\-\.0-9]/g)) {
                      n.html("'.$this->lang['uriName'].'");
                      n.css("display","block");
                      }else {
                      l.css("display","block");
                      $.ajax({
                      type:"POST",
                      url:"/ajax-page/",
                      data:{fields:{"uri":v},lang:"'.$this->urlRouter->siteLanguage.'",form:"uri"},
                      dataType:"json",
                      success:function(rp){
                      l.css("display","none");
                      n.html(rp.MSG);
                      n.css("display","block");
                      if (rp.DATA.ok)n.next().hide();
                      },
                      error:function(rp){
                      l.css("display","none");
                      n.html("'.$this->lang['uriError'].'");
                      n.css("display","block");
                      }
                      })
                      }
                      }
                      };
                      var svR=function(e){
                      e=$(e.parentNode);
                      var p=e.prev().prev();
                      e.css("display","none");
                      p.css("display","block");
                      };'; 
                }*/
                if ($hasResults) {
                    echo $this->summerizeSearch();
                    if($this->urlRouter->userId){
                        $this->renderSidePage();
                    }else{
                        $this->renderSideSections();
                    }
                    if($this->urlRouter->module=='detail'){
                        if($this->detailAdExpired){
                            
                        }else{
                            $this->displayDetail();
                        }
                    }
                    ?><ul class="ls"<?= $this->urlRouter->module == 'detail' ? '' : ' itemprop="mainContentOfPage" ' ?>itemscope itemtype="https://schema.org/ItemList"><?php
                        echo '<meta itemprop="name" content="' . $this->subTitle . '" />';
                        $this->renderDResults($keywords);
                        ?></ul><?php 
                        //echo $this->fill_ad('zone_5');
                    echo $this->pagination();
                    if ( ($this->urlRouter->module=='search' || $this->urlRouter->module=='detail') && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId ){
                    $followStr='';
                    if($this->urlRouter->sectionId){
                        $followUp = $this->urlRouter->db->getSectionFollowUp($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);      
                        $fup = array();
                        if(isset($this->urlRouter->sections[$this->urlRouter->sectionId][6]) && $this->urlRouter->sections[$this->urlRouter->sectionId][6]){
                            $tmpSec = explode(',', $this->urlRouter->sections[$this->urlRouter->sectionId][6]);
                            $fup = array();
                            foreach($tmpSec as $sec){
                                $fup[] = array($sec,0);
                            }
                        }
                        if($followUp){
                            $followUp = array_merge($fup,$followUp);
                        }else{
                            $followUp = $fup;
                        }
                        
                        if($followUp && count($followUp)){
                            $procSec=array();
                            $k=0;
                            foreach($followUp as $section){
                                if(!isset($procSec[$section[0]])){
                                    $uri=$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,0,$section[0],$section[1]);
                                    $sName=$this->urlRouter->sections[$section[0]][$this->fieldNameIndex];
                                    if($section[1]){
                                        $pName=$this->urlRouter->purposes[$section[1]][$this->fieldNameIndex];
                                        switch ($section[1]) {
                                            case 1:
                                            case 2:
                                            case 8:
                                                $sName = $sName . ' ' . $pName;
                                                break;
                                            case 6:
                                            case 7:
                                                $sName = $pName . ' ' . $sName;
                                                break;
                                            case 3:
                                                if ($this->urlRouter->siteLanguage == "ar")
                                                        $sName = 'وظائف ' . $sName;
                                                else
                                                        $sName = $sName . ' jobs';
                                                break;
                                            case 4:
                                                $in = "";
                                                if ($this->urlRouter->siteLanguage == "en")
                                                    $in = " {$this->lang['in']}";
                                                $sName = $pName . $in . " " . $sName;
                                                break;
                                            case 5:
                                                if ($this->urlRouter->siteLanguage == "ar"){                                               
                                                    $tmp='خدمات';
                                                    if(!preg_match('/'.$tmp.'/u',$sName)){
                                                        $sName = $tmp . ' ' . $sName;
                                                    }
                                                }else{
                                                    $tmp='services';  
                                                    if(!preg_match('/'.$tmp.'/',$sName)){
                                                        $sName = $sName . ' ' . $tmp;
                                                    }
                                                }
                                                break;
                                            case 999:
                                                $sName = $sName . ' ' . ($this->urlRouter->siteLanguage =='en' ? 'misc':'متفرقات');
                                                break;
                                        }
                                    }
                                    
                                    $iTmp='';
                                    if($this->urlRouter->sections[$section[0]][4]==1){
                                        $iTmp.='<span class="x x'.$section[0].'"></span>';
                                    }elseif($this->urlRouter->sections[$section[0]][4]==2){
                                        $iTmp.='<span class="z z'.$section[0].'"></span>';
                                    }elseif($this->urlRouter->sections[$section[0]][4]==3){
                                        $iTmp.='<span class="v v'.$section[0].'"></span>';
                                    }elseif($this->urlRouter->sections[$section[0]][4]==4){
                                        $iTmp.='<span class="y y'.$section[0].'"></span>';
                                    }elseif($this->urlRouter->sections[$section[0]][4]==99){
                                        $iTmp.='<span class="u u'.$section[0].'"></span>';
                                    }else {
                                        $iTmp.='<span class="v'.$section[0].'"></span>';
                                    }
                                    
                                    $followStr.='<li><a href="'.$uri.'">'.$iTmp.$sName.'</a></li>';
                                    $procSec[$section[0]]=1;
                                    $k++;
                                    if($k==5)break;
                                }
                            }
                            if($followStr){
                                $followStr='<b>'.$this->lang['interestSection'].'</b><ul>'.$followStr.'</ul></div>';
                            }
                        }
                    }
                    $hasExt=0;
                  
                        if($followStr){
                            if(0 && $hasExt){
                                $followStr='<div class="sug sugf">'.$followStr;
                            }else{
                                $followStr='<div class="sug">'.$followStr;
                            }
                            echo ' <!--googleoff: index --> ', $followStr, ' <!--googleon: index --> ';
                        }
                }} else {
                        if ($this->urlRouter->watchId) {
                            echo $this->summerizeSearch();
                            echo $this->renderSideSections();
                            /*if (count($this->watchInfo) > 1)
                                echo '<div class="no sno">';
                            else
                                echo '<div class="no">';
                            echo $this->lang['noWatchAds'], ' ';
                            echo $this->formatSinceDate($this->user->params['last_visit']);
                            echo '</div>';
                            $this->renderBottomMen();*/
                        }elseif ($this->urlRouter->userId) {
                            if ($this->urlRouter->params['q'] || $this->urlRouter->sectionId)
                                echo $this->summerizeSearch();
                            $this->renderSideSections();
                            echo '<div class="no">';
                            if ($this->urlRouter->params['q'])
                                echo $this->lang['noPartnerQAds'];
                            else
                                echo $this->lang['noPartnerAds'];
                            echo '</div>';
                        }elseif ($this->userFavorites) {
                            echo $this->summerizeSearch();
                        } else {
                            
                            if ($this->searchResults['body']['total_found'] == 0)
                                echo $this->summerizeSearch();
                            
                            $purposeId = $this->urlRouter->purposeId;
                            $sectionId = $this->urlRouter->sectionId;
                            $rootId = $this->urlRouter->rootId;
                            $q = $this->urlRouter->params['q'];
                            $extendedId = $this->extendedId;
                            $localityId = $this->localityId;

                            $this->alternate_search($keywords);
                            
                            
                            $this->urlRouter->purposeId = $purposeId;
                            $this->urlRouter->sectionId = $sectionId;
                            $this->urlRouter->rootId = $rootId;
                            $this->urlRouter->params['q'] = $q;
                            $this->extendedId=  $extendedId;
                            $this->localityId=$localityId;
                        }
                    }
                }
                if ($this->urlRouter->watchId && $this->searchResults !== false) {
                    $cSec = count($this->watchInfo);
                    if ($cSec) {
                        $lang = $this->urlRouter->siteLanguage == 'ar' ? '' : 'en/';
                        $isOwner = $this->user->info['id'] == $this->urlRouter->watchId;
                        $idKey = $this->user->encodeId($this->pageUserId);
                            ?><ul id="watchbox" class='seli h sh rct<?= $this->searchResults['body']['total_found']>10 ?' selip':'' ?>'><?php
                                ?><li><?php  
                                    if ($this->channelId == 0) {
                                        ?><b><?=$this->lang['myList'] ?></b><span class="nb"><?= $this->lang['maxWatchlistNb'] ?></span><?php
                                    } else {
                                        ?><a class="lnk" href="/watchlist/<?= $lang ?>?u=<?= $idKey ?>"><?= $this->lang['myList'] ?></a><?php
                                    }
                                ?></li><?php
                            ?></ul><?php
                            ?><ul class='seli sh'><?php
                                if ($isOwner) {
                                    $countryCities=array();
                                    $localitiesArray=array();
                                    $extendedArray=array();
                                    foreach ($this->watchInfo as $info) {
                                        $ext='';
                                        if($info['COUNTRY_ID']){
                                            $uri = '/' . $this->urlRouter->countries[$info['COUNTRY_ID']]['uri'] . '/';
                                            /*
                                            if(!isset($countryCities[$info['COUNTRY_ID']])){
                                                $tmp=$this->urlRouter->db->queryCacheResultSimpleArray(
                                                "cities_{$info['COUNTRY_ID']}",
                                                "select c.ID from city c
                                                where c.country_id={$info['COUNTRY_ID']} and c.blocked=0",
                                                null, 0, $this->urlRouter->cfg['ttl_long']);
                                                if (count($countryCities[$info['COUNTRY_ID']])>1) {
                                                    $countryCities[$info['COUNTRY_ID']]=1;
                                                }else{
                                                    $countryCities[$info['COUNTRY_ID']]=0;
                                                }
                                            }*/
                                            $idx=2;
                                            if($info['LOCALITY_ID']){
                                                if(!isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']])){
                                                    $localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']] = $this->urlRouter->db->getLocalitiesData($info['COUNTRY_ID'], $info['SECTION_ID'], NULL, $this->urlRouter->siteLanguage);
                                                }
                                                if(isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']])){
                                                    $uri.=$localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']]['uri'] . '/';
                                                    $ext = 'c-' . $info['LOCALITY_ID'] . '-' . 2 . '/';
                                                }
                                            }elseif($info['CITY_ID']){
                                                $idx=3;
                                                $uri.=$this->urlRouter->cities[$info['CITY_ID']][3].'/';
                                            }
                                            if($info['SECTION_TAG_ID']){
                                                if(!isset($extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']])){
                                                    $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']] = $this->urlRouter->db->getSectionTagsData($info['COUNTRY_ID'], $info['CITY_ID'], $info['SECTION_ID'], $this->urlRouter->siteLanguage);
                                                }
                                                $uri.=$this->urlRouter->sections[$info['SECTION_ID']][3] . '-' . $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']][$info['SECTION_TAG_ID']]['uri'] . '/';
                                                $ext = 'q-' . $info['SECTION_TAG_ID'] . '-' . $idx . '/';
                                            }elseif($info['SECTION_ID']){
                                                $uri.=$this->urlRouter->sections[$info['SECTION_ID']][3] . '/';
                                            }
                                            if($info['PURPOSE_ID'])
                                                $uri.=$this->urlRouter->purposes[$info['PURPOSE_ID']][3] . '/';
                                        }
                                            $uri.=($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/');
                                            if($ext){
                                                $uri.=$ext;
                                            }
                                            if($info['QUERY_TERM']){
                                                $uri.='?q='.urlencode($info['QUERY_TERM']);
                                            }
                                            
                                            $iTmp='<span class="z z117"></span>';
                                            if($info['SECTION_ID'] && isset($this->urlRouter->sections[$info['SECTION_ID']])){                                                
                                                $rootId=$this->urlRouter->sections[$info['SECTION_ID']][4];
                                                if($rootId==1){
                                                    $iTmp='<span class="x x'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==2){
                                                    $iTmp='<span class="z z'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==3){
                                                    $iTmp='<span class="v v'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==4){
                                                    $iTmp='<span class="y y'.$info['SECTION_ID'].'"></span>';
                                                }elseif($rootId==99){
                                                    $iTmp='<span class="u u'.$info['SECTION_ID'].'"></span>';
                                                }else {
                                                    $iTmp='<span class="z z117"></span>';
                                                }
                                            }
                                            
                                            $info['TITLE']=preg_replace('/\\\["\']/', '',$info['TITLE']);
                                            
                                            if ($this->channelId == $info['ID'] || $cSec == 1) {
                                                ?><li><b id="<?= $info['ID'] ?>"><?= $iTmp ?><?= ($info['EMAIL'] ? '<span class="d mail"></span>':'') . $info['TITLE'] ?></b><?php
                                            } else {
                                                ?><li><a href="/watchlist/<?= $lang . '?u='.$idKey.'&channel=' . $info['ID'] ?>" id="<?= $info['ID'] ?>"><?= $iTmp ?><?= ($info['EMAIL'] ? '<span class="d mail"></span>':'') . $info['TITLE'] ?></a><?php
                                            }
                                            ?><span class="kl"><span onclick="eW(this,<?= $info['ID'] ?>)" class="rj edi"></span><a href="<?= $uri ?>" class="rj nxt"></a></span><?php
                                            ?></li><?php
                                        }
                                    } 
                            ?></ul><?php
                            ?><p class="nbi nbi0"><?php 
                                ?><span class="rj edi"></span> <?= $this->lang['watchEditHint'] ?><?php
                            ?></p><?php
                            ?><p class="nbi"><?php 
                                ?><span class="rj nxt"></span> <?= $this->lang['watchNextHint'] ?><?php
                            ?></p><?php
                            ?><p class="nbi"><?php 
                                ?><span class="d mail"></span> <?= $this->lang['watchMailHint'] ?><?php
                            ?></p><?php
                    }
                }
                
                ?></div><?php 
                if($this->searchResults['body']['total_found']){
                ?> <!--googleoff: all--> <div id="rpd" class="rpd cct"><?php
                    ?><b><?= $this->lang['abuseTitle'] ?></b><?php
                    ?><textarea onkeydown="idir(this)" onchange="idir(this,1)"></textarea><?php
                    ?><b><?= $this->lang['abuseContact'] ?></b><?php
                    ?><input type="email" placeholder="your.email@gmail.com" /><?php
                    ?><input type="button" onclick="rpa(this,2)" class="bt" value="<?= $this->lang['send'] ?>" /><?php
                    ?><input type="button" onclick="rpa(this,1)" class="bt cl" value="<?= $this->lang['cancel'] ?>" /><?php
                ?></div> <!--googleon: all--> <?php
                }
            }
            
            
  
    function alternate_search($keywords = "") {
        
        if($this->urlRouter->rootId){
            $this->tmpRootId = $this->urlRouter->rootId;
        }
        if($this->urlRouter->purposeId){
            $this->tmpPurposeId = $this->urlRouter->purposeId;
        }
        if ($this->searchResults['body']['total_found'] == 0) {
            if ($this->extendedId || $this->localityId) {
                $this->extendedId = 0;
                $this->localityId = 0;
            } elseif ($this->urlRouter->purposeId) {
                $this->urlRouter->purposeId = 0;
                $this->purposeName = "";
            } elseif ($this->urlRouter->sectionId) {
                $this->urlRouter->sectionId = 0;
                $this->sectionName = "";
            } elseif ($this->urlRouter->rootId) {
                $this->urlRouter->rootId = 0;
                $this->rootName = "";
            } else {
                $this->renderSideSections(1);
                $q = "";
                if ($this->urlRouter->params['q']) 
                    $q = htmlspecialchars($this->urlRouter->params['q'], ENT_QUOTES);
                ?><ul class="ph"><?php
                    ?><li><?= $this->lang['no_result_pre'].' <b>'.$q.'</b> '.$this->lang['no_result_after'] ?></li><?php
                    ?><li><?= $this->lang['search_help_1'] ?></li><?php
                    
                    if ($this->publisherTypeSorting && in_array($this->tmpRootId,[1,2,3])){
                            
                        $uri = $this->getPageUri().'?';
                            if ($this->urlRouter->params['q']) {
                                $uri .= 'q=' . urlencode($this->urlRouter->params['q']).'&';
                            }  
                            $uri .= 'xd=0';
                            
                            switch($this->publisherTypeSorting){
                                case 2: 
                                    ?><li class="in"><?= ' <a href="'.$uri.'">'.($this->urlRouter->siteLanguage == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mpub_3_'.$this->tmpRootId]) : ($this->lang['remove'].' '.strtolower($this->lang['spub_3_'.$this->tmpRootId]).' '.$this->lang['filter'])); ?></a></li><?php
                                    break;
                                case 1:
                                    if($this->tmpRootId == 3){
                                        ?><li class="in"><?= ' <a href="'.$uri.'">'.($this->urlRouter->siteLanguage == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mbpub_1']):($this->lang['remove'].' '.strtolower($this->lang['sbpub_1']).' '.$this->lang['filter'])); ?></a></li><?php
                                    }else{
                                        ?><li class="in"><?= ' <a href="'.$uri.'">'.($this->urlRouter->siteLanguage == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mpub_1']):($this->lang['remove'].' '.strtolower($this->lang['spub_1']).' '.$this->lang['filter'])); ?></a></li><?php
                                    }
                                    break;
                                default:
                                    //echo '<div class="ms ut"><span class="i i1"></span> '.$this->lang['pub_1'].'</div>';
                                    break;
                            }
                        }
                    
                    
                    ?><li class="in"><?= $this->lang['search_help_2'] ?></li><?php
                    ?><li class="in"><?= $this->lang['search_help_3'] ?></li><?php
                    ?><li class="in"><?= $this->lang['search_help_4'] ?></li><?php
                ?></ul><?php
                ?><span class='naf'></span><?php
                return false;
            }
        }
        if (!$this->urlRouter->params['q'] && !$this->extendedId && !$this->localityId &&
                    !$this->urlRouter->purposeId && !$this->urlRouter->sectionId && !$this->urlRouter->rootId) {
            ?><div class="nad"><p><br /><br /><?= $this->lang['noAdsAtAll'] ?></p></div><?php
            ?><span class='naf'></span><?php
            return false;
        }
        $this->urlRouter->params['start'] = 0;
        $this->execute(true);
        if ($this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches']) > 0) {
            $this->updateUserSearchCriteria();
            $this->renderSideSections();
            echo $this->alternateSummery($this->searchResults['body']['total_found']);
                    
            if($this->urlRouter->module=='detail'){
                if($this->detailAdExpired){
   
                }else{
                    $this->displayDetail();
                }
            }
            ?><ul class="ls"><?php
            $this->renderDResults($keywords);
            ?></ul><?php
            echo $this->pagination();
        } else {
            /*$purposeId = $this->urlRouter->purposeId;
            $sectionId = $this->urlRouter->sectionId;
            $rootId = $this->urlRouter->rootId;
            $q = $this->urlRouter->params['q'];
            $extendedId = $this->extendedId;
            $localityId = $this->localityId;*/

            $this->alternate_search($keywords);
/*
            $this->urlRouter->purposeId = $purposeId;
            $this->urlRouter->sectionId = $sectionId;
            $this->urlRouter->rootId = $rootId;
            $this->urlRouter->params['q'] = $q;
            $this->extendedId=  $extendedId;
            $this->localityId=$localityId;*/
        }
    }

            
    function getAdSection($ad, $hasSchema = false, $isDetail=false) {
        $section = '';
        $hasLink = true;
        if (!empty($this->urlRouter->sections)) {
            $extIDX = $this->urlRouter->siteLanguage == 'ar' ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN;
            $locIDX = $this->urlRouter->siteLanguage == 'ar' ? Classifieds::LOCALITIES_AR : Classifieds::LOCALITIES_EN;
            $extID = (isset($ad[$extIDX]) && isset($this->extended[$ad[$extIDX]])) ? $ad[$extIDX] : $this->extendedId;
            $hasLoc = (isset($ad[$locIDX]) && count($ad[$locIDX]) && count($this->localities));
            $locID = 0;
            if ($hasLoc) {
                $hasSchema = false;
                $locKeys = array_reverse(array_keys($ad[$locIDX]));
                foreach ($locKeys as $key) {
                    if (isset($this->localities[$key])) {
                        $locID = $key;
                        break;
                    }
                }
            }
    
            //$locID = (isset($ad[Classifieds::LOCALITY_ID]) && isset($this->localities[$ad[Classifieds::LOCALITY_ID]])) ? $ad[Classifieds::LOCALITY_ID] : $this->localityId;
            if ($isDetail || (!($extID && !$this->extendedId) && (!$hasLoc || ($hasLoc && $locID == $this->localityId)) && $this->urlRouter->purposeId && $this->urlRouter->sectionId))
            $hasLink=false;

            $section = $this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
            if ($extID)
                $section = $this->extended[$extID]['name']; //[1]
            
            switch ($ad[Classifieds::PURPOSE_ID]) {
                case 1:
                case 2:
                case 8:
                case 999:
                    if ($hasSchema)
                        $section = '<span itemprop="name">' . $section . '</span> ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    else
                        $section = $section . ' ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    break;
                case 6:
                case 7:
                    if ($hasSchema)
                        $section = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . ' <span itemprop="name">' . $section . '</span>';
                    else
                        $section = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . ' ' . $section;
                    break;
                case 3:
                    if ($this->urlRouter->siteLanguage == 'ar') {
                        $in = ' ';
                        if ($hasSchema)
                            $section = 'وظائف <span itemprop="name">' . $section . '</span>';
                        else
                            $section = 'وظائف ' . $section;
                    }else {
                        if ($hasSchema)
                            $section = '<span itemprop="name">' . $section . '</span> ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                        else
                            $section = $section . ' ' . $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    }
                    break;
                case 4:
                    $in = ' ';
                    if ($this->urlRouter->siteLanguage == "en")
                        $in = ' ' . $this->lang['in'] . ' ';
                    if ($hasSchema)
                        $section = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . $in . '<span itemprop="name">' . $section . '</span>';
                    else
                        $section = $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . $in . $section;
                    break;
                case 5:
                    if ($hasSchema)
                        $section = '<span itemprop="name">' . $section . '</span>';
                    else
                        $section = $section;
                    break;
            }
            
            if ($hasLoc) {
                $res = '';
                //                if (isset($this->user->info['level']) && $this->user->info['level']==9) {
                $iter = 0;
                foreach ($ad[$locIDX] as $id => $loc) {
                    if (isset($this->localities[$id])) {
                        if ($iter == 0)
                            $tempSection = $section . ' ' . $this->lang['in'] . ' ' . $this->localities[$id]['name'];
                        else
                            $tempSection = $this->localities[$id]['name'];
                        $iter++;
                        //$section.=' '.  $this->lang['in'].' '.$this->localities[$locID][1];
                        //if (!preg_match("/{$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]}/", $section)) {
                        //    $section.=' '.$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$this->fieldNameIndex];
                        //}
                        if ($hasLink) {
                            $idx = 2;
                            $uri = '/' . $this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]]['uri'] . '/';
                            /* if ($this->hasCities && isset($this->urlRouter->cities[$ad[Classifieds::CITY_ID]])) {
                              $idx=3;
                              $uri.=$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][3].'/';
                              } */
                            $uri.=$this->localities[$id]['uri'] . '/';
                            $uri.=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][3] . '/';
                            $uri.=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][3] . '/';
                            $uri.=($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/') . 'c-' . $id . '-' . $idx . '/';
                            if ($res)
                                $res .= '  »  ';
                            $res.='<a href="' . $uri . '">' . $tempSection . '</a>';
                        }else{
                            if ($res)
                                $res .= '  »  ';
                            $res.=$tempSection;
                        }
                    }
                }
                $section = $res;
            } else {
                if ($this->urlRouter->countryId && $ad[Classifieds::COUNTRY_ID] != $this->urlRouter->countryId) {
                    $ad[Classifieds::COUNTRY_ID] = $this->urlRouter->countryId;
                    $ad[Classifieds::CITY_ID] = 0;
                }

                if ($this->urlRouter->cityId && $ad[Classifieds::CITY_ID] != $this->urlRouter->cityId){
                    $ad[Classifieds::CITY_ID] = $this->urlRouter->cityId;
                }
                
                if (isset($this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]])) {
                    $countryId = $ad[Classifieds::COUNTRY_ID];
                    $cityId = 0;
                    if (!empty($this->urlRouter->countries[$countryId]['cities']) && $ad[Classifieds::CITY_ID] && isset($this->urlRouter->countries[$countryId]['cities'][$ad[Classifieds::CITY_ID]])) {
                        $cityId = $ad[Classifieds::CITY_ID];
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->countries[$countryId]['cities'][$cityId]['name'];

                        if (!mb_strstr($section, $this->urlRouter->countries[$countryId]['name'], true, "utf-8")) {
                            $section.=' ' . $this->urlRouter->countries[$countryId]['name'];
                        }                                
                    } else {
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->countries[$countryId]['name'];                    
                    }
                    /*
                    $countryId = $this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][0];
                    $countryCities = $this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}", "select c.ID from city c
                         where c.country_id={$countryId} and c.blocked=0", null, 0, $this->urlRouter->cfg['ttl_long']);

                    $cityId = 0;
                    if (count($countryCities) > 1 && isset($this->urlRouter->cities[$ad[Classifieds::CITY_ID]])) {
                        $cityId = $ad[Classifieds::CITY_ID];
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$this->fieldNameIndex];

                                if (!mb_strstr($section, $this->urlRouter->countries[$countryId][$this->fieldNameIndex], true, "utf-8")) {
                                    $section.=' ' . $this->urlRouter->countries[$countryId][$this->fieldNameIndex];
                                }
                            } else {
                                $section = $section . ' ' . $this->lang['in'] . ' ' . $this->urlRouter->countries[$countryId][$this->fieldNameIndex];
                                //var_dump($section);
                            }
                             * 
                     */
                    if ($hasLink) {
                        if ($extID) {
                            $idx = 2;
                            $uri = '/' . $this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]]['uri'] . '/';
                            if ($cityId) {
                                $idx = 3;
                                $uri.=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]]['cities'][$cityId]['uri'] . '/';
                            }
                            $uri.=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][3] . '-' . $this->extended[$extID]['uri'] . '/';
                            $uri.=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][3] . '/';
                            $uri.=($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/') . 'q-' . $extID . '-' . $idx . '/';
                            $section = '<a href="' . $uri . '">' . $section . '</a>';
                        } else {
                            $section = '<a href="' . $this->urlRouter->getURL($countryId, $cityId, $ad[Classifieds::ROOT_ID], $ad[Classifieds::SECTION_ID], $ad[Classifieds::PURPOSE_ID]) . '">' . $section . '</a>';
                        }
                    }
                }
            }
            
            if($isDetail && $section){
                return '<b>' . $section . '</b>';
            } elseif(!$isDetail) {
                $locIcon='';
                if ($ad[Classifieds::PUBLICATION_ID] == 1) {
                    if ($ad[Classifieds::LATITUDE] || $ad[Classifieds::LONGITUDE]) {
                        $locIcon = "<span class='i loc'></span>";
                    }
                }
                return '<span class="k">'. $locIcon . $section . '</span>';
            }
        }
    }

            
            function sectionSummeryMobile($forceRebuild = false) {
                if ($this->breadString && !$forceRebuild)
                    return $this->breadString;
                $bread = "";
                if ($this->urlRouter->siteLanguage == "ar") {
                    if ($this->urlRouter->purposeId) {
                        $bread.= " {$this->purposeName}";
                    }
                    if ($this->urlRouter->rootId) {
                        if ($this->urlRouter->sectionId) {
                            $bread .= " {$this->sectionName}";
                            $bread .= " {$this->lang['included']} {$this->rootName}";
                        } else {
                            $bread .= " {$this->rootName}";
                        }
                    }
                    if ($this->urlRouter->countryId || $this->urlRouter->rootId) {
                        if ($this->urlRouter->countryId) {
                            if ($this->hasCities && $this->urlRouter->cityId) {
                                $bread.= " {$this->cityName}، ";
                            }
                            $bread.= " {$this->countryName} ";
                        } else {
                            $bread.= " {$this->countryName} ";
                        }
                    } else {
                        $bread.=" {$this->lang['opt_all_countries']}";
                    }
                } else {
                    if ($this->urlRouter->rootId) {
                        if ($this->urlRouter->sectionId) {
                            $bread .= " {$this->sectionName}";
                            if ($this->urlRouter->purposeId)
                                $bread.= " {$this->purposeName}";
                            $bread .= " {$this->lang['included']} {$this->rootName}";
                        }else {
                            $bread .= " {$this->rootName}";
                            if ($this->urlRouter->purposeId)
                                $bread.= " {$this->purposeName}";
                        }
                    }
                    if ($this->urlRouter->countryId || $this->urlRouter->rootId) {
                        if ($this->urlRouter->countryId) {
                            if ($this->hasCities && $this->urlRouter->cityId) {
                                $bread.= " {$this->cityName}, ";
                            }
                            $bread.= " {$this->countryName} ";
                        } else {
                            $bread.= " {$this->countryName} ";
                        }
                    } else {
                        $bread.=" {$this->lang['opt_all_countries']}";
                    }
                }
                $this->breadString = $bread;
                return $this->breadString;
            }

            function summerizeSearchMobile($forceRebuild = false) {
                $this->getBreadCrumb($forceRebuild);
                $count = $this->searchResults['body']['total_found'];
                $bread = '';
                if ($count) {
                    $formatted = number_format($count);
                    $bread = "<b>";
                    if ($this->urlRouter->siteLanguage == "ar") {
                        if ($count > 10) {
                            $bread.= $formatted . " " . $this->lang['ads'];
                        } elseif ($count > 2) {
                            $bread.= $formatted . " " . $this->lang['3ad'];
                        } else if ($count == 1) {
                            $bread.= $this->lang['ad'];
                        } else {
                            $bread.= $this->lang['2ad'];
                        }
                    } else {
                        $bread.= $this->formatPlural($count, "ad");
                    }
                    //if ($this->urlRouter->params['q']) $bread.= " {$this->lang['for']} ".$this->urlRouter->params['q'];
                    $bread.="</b> ";
                    if ($this->urlRouter->siteLanguage == 'ar') {
                        $bread.='في ';
                    } else {
                        $bread.='in ';
                    }
                } else {
                    $bread.=$this->lang['no_listing_display'] . ' ';
                    //if (!$this->urlRouter->params['q']){
                    if ($this->urlRouter->siteLanguage == 'ar') {
                        $bread.='في ';
                    } else {
                        $bread.='in ';
                    }
                    //}
                }
                if ($this->channelId && $this->watchName){
                    $bread.='<b>' . $this->watchName . '</b>';
                }else{
                    $bread.= $this->crumbTitle;
                }
                
                return $bread;
            }

            function summerizeSearch($forceRebuild = false) {
                $count = $this->searchResults['body']['total_found'];
                $adLang='';
                if ($this->urlRouter->siteLanguage != "ar") $adLang=$this->urlRouter->siteLanguage.'/';
                if ($this->urlRouter->watchId) {
                    $hasShare = $this->urlRouter->cfg['enabled_sharing'] && $this->urlRouter->module == 'search' && $count;
                    $formatted = number_format($count);
                    //$rc = ($count > $this->num ? "rct " : "rc ");
                    /*$bread = "<div class='sum {$rc}ssm'>";
                    $bread.='<div class="brd">';
                    if ($this->urlRouter->watchId == $this->user->info['id']) {
                        $bread.='<b>' . $this->lang['myList'] . '</b>';
                    } else {
                        $bread.='<b>' . $this->lang['noUserList'] . '</b>';
                    }
                    $bread .= '</div>';*/
                    $bread='';
                    if ($this->channelId && $this->watchName){
                        $idKey=$this->user->encodeId($this->pageUserId);
                        $bread.='<div class="brd">';
                        $bread.='<a href="/watchlist/?u='.$idKey.'">' . $this->lang['myList'] . '</a>';
                        $bread.= '<a class="i rss" target="_blank" href="/watchlist/?u='.$idKey.'&channel='.$this->channelId.'&rss=1" id="rss-link"></a>';
                        $bread.='<b>' . $this->watchName . '</b>';
                        $bread .= '</div>';
                    }else{
                        $idKey=$this->user->encodeId($this->pageUserId);
                        $bread.='<div class="brd">';
                        $bread.= '<a class="i rss" target="_blank" href="/watchlist/?u='.$idKey.'&rss=1" id="rss-link"></a>';
                        $bread.='<b>' . $this->lang['myList'] . '</b>';
                        $bread .= '</div>';
                    }
                    $bread.= "<p class='ph'>";
                    if ($this->urlRouter->siteLanguage == "ar") {
                        if ($count > 10) {
                            $bread.= $formatted . " " . $this->lang['ads'];
                        } elseif ($count > 2) {
                            $bread.= $formatted . " " . $this->lang['3ad'];
                        } else if ($count == 1) {
                            $bread.= $this->lang['ad'];
                        } elseif ($count == 2) {
                            $bread.= $this->lang['2ad'];
                        } else {
                            $bread.= $this->lang['0ad'];
                        }
                        if ($this->channelId && $this->watchName)
                            $bread.= ' ' . $this->lang['in'] . ' ' . $this->watchName;
                        if(isset($this->user->params['last_visit'])){
                            $bread.=' ' . $this->lang['sinceLast'] . ' ' . $this->formatSinceDate($this->user->params['last_visit']);
                        }
                    }else {
                        if ($count)
                            $bread.= $this->formatPlural($count, "ad");
                        else
                            $bread.=$this->lang['0ad'];
                        if ($this->channelId && $this->watchName)
                            $bread.= ' ' . $this->lang['in'] . ' ' . $this->watchName;
                        if(isset($this->user->params['last_visit'])){
                            $bread.=' ' . $this->lang['sinceLast'] . ' ' . $this->formatSinceDate($this->user->params['last_visit']);
                        }
                    }
                    //$bread.= '</b> ';
                    //$bread.=($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_blogger"></span><span class="st_sharethis"></span>' : '');
                    $bread .= '</p>';
                    if(!$count)
                        $bread.='<span class="naf"></span>';
                    //if ($hasShare)$bread.='<!--googleoff: snippet--><div class="tsh"><label>'.$this->lang['shareResults'].'</label><span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_blogger"></span><span class="st_sharethis"></span></div><!--googleon: snippet-->';
                    //$bread .= '</div>';
                }elseif ($this->urlRouter->userId) {
                    $hasShare = $count && $this->urlRouter->cfg['enabled_sharing'] && $this->urlRouter->module == 'search';
                    $formatted = number_format($count);
                    $q = $this->urlRouter->params['q'];
                    $uri = '/' . $this->partnerInfo['uri'] . $this->urlRouter->getURL(0, 0, 0, $this->urlRouter->sectionId, $this->urlRouter->purposeId);
                    //$bread = "<div class='sum ssm'>";
                    $bread='<div class="srch w">';
                        $bread.='<form onsubmit="if(document.getElementById(\'q\').value)return true;return false" action="'.$uri.'" method="get">';
                            $bread.='<div class="q">'; 
                                $bread.='<input id="q" name="q" value="'. htmlspecialchars($q,ENT_QUOTES).'" type="text" placeholder="'.$this->lang['search_what'].'" />';
                                if ($this->urlRouter->params['q']) {
                                    $bread.='<a class="qx" href="'.$uri.'"></a>';
                                }
                            $bread.='</div>';
                            $bread.='<input class="bt" type="submit" value="'.$this->lang['search'].'" />';
                        $bread.='</form>';
                    $bread.='</div>';
                    //$bread.='<form onsubmit="if(document.getElementById(\'q\').value)return true;return false" action="' . $uri . '" method="get"><input id="q" class="q rc" name="q" value="' . htmlspecialchars($q, ENT_QUOTES) . '" type="text" placeholder="' . $this->lang['search_what'] . '" /><input class="bt rc" type="submit" value="' . $this->lang['search'] . '" /></form>';
                    //if ($this->urlRouter->params['q']) {
                        //$uri=  preg_replace('/\/[0-9]{1,3}\//','/',$this->urlRouter->uri);
                        //$uri=  $this->urlRouter->uri;
                        //$bread.='<a class="x" href="' . $uri . '"></a>';
                    //}
                    $bread.= "<p class='ph'><b>";
                    if ($this->urlRouter->siteLanguage == "ar") {
                        if ($count > 10) {
                            $bread.= $formatted . " " . $this->lang['ads'];
                        } elseif ($count > 2) {
                            $bread.= $formatted . " " . $this->lang['3ad'];
                        } else if ($count == 1) {
                            $bread.= $this->lang['ad'];
                        } elseif ($count == 0) {
                            $bread.= '0 ' . $this->lang['ads'];
                        } else {
                            $bread.= $this->lang['2ad'];
                        }
                    } else {
                        $bread.= $this->formatPlural($count, "ad");
                    }
                    $bread.= '</b> ';
                    if ($this->urlRouter->params['q']) {
                        $bread.=$this->lang['for'] . ' ' . htmlspecialchars($this->urlRouter->params['q'], ENT_QUOTES);
                        if ($this->partnerInfo['title'] || $this->urlRouter->sectionId) {
                            $bread.=' ' . $this->lang['in'] . ' ';
                            if ($this->urlRouter->siteLanguage == "ar")
                                $bread.=$this->lang['pclassifieds'] . ' ';
                        }
                    }
                    if ($this->urlRouter->sectionId) {
                        $bread.=$this->partnerSection;
                        if ($this->urlRouter->params['q'] && $this->urlRouter->siteLanguage != 'ar')
                            $bread.=' ' . $this->lang['pclassifieds'];
                        if ($this->partnerInfo['title'])
                            $bread.=' - ' . $this->partnerInfo['title'];
                    }
                    //$bread.=($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');
                    $bread.='</p>';
                }else {
                    $hasShare = $hasPost = false;
                    $bread='';
                    if (!$forceRebuild && $count)
                        $hasPost = true;
                    if ($this->userFavorites && $count == 0) {
                        /*$bread = "<div class='sum fv rc m_b'>";
                        $bread.=$this->getBreadCrumb($forceRebuild, $count);
                        $bread.=$this->lang['noFavorites'];
                        $bread.='<p><a href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) . '">' . $this->lang['noFavBackLink'] . '</a></p>';
                        $bread.='</div>';*/
                        $bread.= "<p class='ph phb'>";
                        $bread.=$this->lang['noFavoritesBrd'];
                        $bread.= '</p>';
                        $bread.='<div class="sid">';
                        $bread.='<p class="phc">'.$this->lang['favBenefit'].'</p>';
                        $bread.='<span class="naf"></span>';
                        $bread.='</div>';
                        $bread.='<div class="nad">';
                        $bread.='<p>' . $this->lang['addF0'] . '</p><br />';
                        $bread.='<img height="150" width="480" src="' . $this->urlRouter->cfg['url_css_mobile'] . '/i/t/fav' . ($this->urlRouter->siteLanguage == 'ar' ? '' : 'e') . $this->urlRouter->_jpg .'" /><br />';
                        $bread.='<a class="bt" href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) . '"><span class="i h"></span> ' . $this->lang['noFavBackLink'] . '</a>';                        
                        $bread.='</div>';
                        return $bread;
                    } else {
                        //$hasShare = $this->urlRouter->cfg['enabled_sharing'] && $this->urlRouter->module == 'search';
                        $hasShare=false;
                        $formatted = number_format($count);
                        //if(!$forceRebuild) {
                        if(!$this->userFavorites)
                            $bread=$this->getBreadCrumb($forceRebuild, $count);
                        if(!$this->urlRouter->isPriceList && ($this->urlRouter->module!='detail' || ($this->urlRouter->module=='detail' && $this->detailAdExpired))){
                        if ($count) {
                            $hasEye=0;
                            if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId && !$forceRebuild) {
                                if (($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100)) {
                                    $hasEye=1;
                                }
                            }
                            if($this->user->info['id'] && ($this->user->info['level']==6 || $this->user->info['id']==5))
                                $hasEye=0;
                            if($this->userFavorites){
                                $bread.= "<p class='ph phb'><span>";
                            }else {
                                $bread.= '<p class="ph'.($hasEye ? ' phx':'').'"><span>';
                            }
                          $bread.='<b>';
                          if ($this->urlRouter->siteLanguage=="ar") {
                          if ($count>10) {
                          $bread.= $formatted." ".$this->lang['ads'];
                          }elseif ($count>2){
                          $bread.= $formatted." ".$this->lang['3ad'];
                          }else if ($count==1){
                          $bread.= $this->lang['ad'];
                          }else {
                          $bread.= $this->lang['2ad'];
                          }
                          }else {
                          $bread.= $this->formatPlural($count, "ad");
                          }
                          $bread.= '</b> ';
                       
                          if ($this->urlRouter->params['q'])
                          $bread.=' '.$this->lang['for'].' '.$this->crumbTitle.($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');
                          elseif ($this->userFavorites) {
                          $bread.=' '.$this->lang['in'].' '. $this->lang['myFavorites'];
                          } else $bread.=' '.$this->crumbTitle.($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');
                      
                          /*
                          if ($hasEye) {
                                    if ($this->user->info['id']) {
                                        $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
                                        if (isset($this->user->info['options']['watch'][$key])) {
                                            $bread.='</span><span class="hid"><b>'. $this->title.'</b> '. $this->lang['inWatchlist'] .'<span class="db ctr"><a href="/watchlist/'.$adLang.'?u='.$this->user->info['idKey'].'" class="bt">'.$this->lang['goWatch'].'</a></span></span><span id="ewd" title="'.$this->lang['inWatchlist'].'" class="ek ekon"></span>';
                                        } else {
                                            $bread.='</span><span class="hid">'.$this->lang['watchAsk'] .'<b>'. preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title) .'</b>'.$this->lang['?'].'<span class="ctr db"><span class="bt ok" onclick="owt(this)">'.$this->lang['btWatch'].'</span></span></span><span id="ewd" title="'.$this->lang['addWatch'].'" class="ek"></span>';
                                            
                                        }
                                    } else {
                                        $bread.='</span><span class="hid">'.$this->lang['watchAsk'] .'<b>'. preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title) .'</b>'.$this->lang['?'].'<span class="ctr db"><span class="bt ok" onclick="owt(this)">'.$this->lang['btWatch'].'</span></span></span><span id="ewd" title="'.$this->lang['addWatch'].'" class="ek"></span>';
                                    }
                                    $bread .= '</p>';
                          }else{*/
                              $bread .= '</p>';
                          //}
                    }
                        }
        
                        if ($count && $this->urlRouter->module == 'search' && $this->publisherTypeSorting && in_array($this->urlRouter->rootId,[1,2,3])){
                            $bread .= "<!--googleoff: snippet--><div class='mnb phx rc'><p>";
                            
                            $uri = $this->getPageUri().'?';
                            if ($this->urlRouter->params['q']) {
                                $uri .= 'q=' . urlencode($this->urlRouter->params['q']).'&';
                            }  
                            $uri .= 'xd=0';
                            
                            switch($this->publisherTypeSorting){
                                case 2: 
                                    $bread .= $this->lang['npub_3_'.$this->urlRouter->rootId];
                                    $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';
                                    break;
                                case 1:
                                    if($this->urlRouter->rootId == 3){
                                        $bread .= $this->lang['nbpub_1'];
                                        $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';
                                    }else{                                        
                                        $bread .= $this->lang['npub_1'];
                                        $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';
                                    }
                                    break;
                                default:
                                    //echo '<div class="ms ut"><span class="i i1"></span> '.$this->lang['pub_1'].'</div>';
                                    break;
                            }
                            $bread .= "</p></div><!--googleon: snippet-->";
                        }
                        
                    }
                    //if ($hasShare)$bread.='<!--googleoff: snippet--><div class="tsh"><label>'.$this->lang['shareResults'].'</label><span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_blogger"></span><span class="st_sharethis"></span></div><!--googleon: snippet-->';
                    //$bread .= '</div>';
                }
                return $bread;
            }

            function alternateSummery($count){
                $bread= "<p class='ph pha'>";
                if ($this->urlRouter->params['q'])
                    $bread.=$this->lang['no_listing_q'].' '.$this->lang['for'].' '.$this->crumbTitle;
                else $bread.=$this->lang['no_listing'].' '.$this->crumbTitle;
                $this->getBreadCrumb(1, $count);
                $bread.='<br />';
                $bread.=$this->lang['might_interest'].' ';
                $bread.='<b>';
                $found='';
                    if ($this->urlRouter->siteLanguage=="ar") {
                        $formatted = number_format($count);
                        if (!$this->urlRouter->params['q'])
                            $found='وارد ضمن ';
                        if ($count>10) {
                                $bread.= $formatted." ".$this->lang['ads'];
                            }elseif ($count>2){
                                if (!$this->urlRouter->params['q'])
                                    $found='واردة ضمن ';
                                $bread.= $formatted." ".$this->lang['3ad'];
                            }else if ($count==1){
                                $bread.= $this->lang['ad'];
                            }else {
                                if (!$this->urlRouter->params['q'])
                                    $found='وردا ضمن ';
                                $bread.= $this->lang['2ad'];
                            }
                            $bread.= '</b> '.$found;
                        }else {
                            $bread.= $this->formatPlural($count, "ad");
                            $bread.= '</b> found in ';
                        }
                        if ($this->urlRouter->params['q'])
                          $bread.=' '.$this->lang['for'].' '.$this->crumbTitle;
                        else $bread.=' '.$this->crumbTitle;
                $bread .= '</p>';
                
                if ($this->urlRouter->module == 'search' && $this->publisherTypeSorting && in_array($this->tmpRootId,[1,2,3])){
                            $bread .= "<!--googleoff: snippet--><div class='mnb phx rc'><p>";
                            
                            $uri = $this->getPageUri().'?';
                            if ($this->urlRouter->params['q']) {
                                $uri .= 'q=' . urlencode($this->urlRouter->params['q']).'&';
                            }  
                            $uri .= 'xd=0';
                            
                            switch($this->publisherTypeSorting){
                                case 2: 
                                    $bread .= $this->lang['npub_3_'.$this->tmpRootId];
                                    $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';
                                    break;
                                case 1:
                                    if($this->tmpRootId == 3){
                                        $bread .= $this->lang['nbpub_1'];
                                        $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';
                                    }else{
                                        $bread .= $this->lang['npub_1'];
                                        $bread .= ' <a href="'.$uri.'">'.$this->lang['npub_cancel'].'</a>';                                        
                                    }
                                    break;
                                default:
                                    //echo '<div class="ms ut"><span class="i i1"></span> '.$this->lang['pub_1'].'</div>';
                                    break;
                            }
                            $bread .= "</p></div><!--googleon: snippet-->";
                        }
                
                return $bread;
            }

            function updateUserSearchCriteria() {
                $time = time();
                if (!isset($this->user->params['search']) || (isset($this->user->params['search']['time']) && ($time - $this->user->params['search']['time'] > 3))) {
                    $this->user->params['search'] = array(
                        'cn' => $this->urlRouter->countryId,
                        'c' => $this->urlRouter->cityId,
                        'ro' => $this->urlRouter->rootId,
                        'se' => $this->urlRouter->sectionId,
                        'pu' => $this->urlRouter->purposeId,
                        'start' => $this->urlRouter->params['start'],
                        'q' => $this->urlRouter->params['q'],
                        'exId' => $this->extendedId,
                        'locId' => $this->localityId,
                        'uId' => $this->urlRouter->userId,
                        'time' => $time
                    );
                    //if (isset ($this->urlRouter->params['tag_id'])) $this->user->params['search']['tag']=$this->urlRouter->params['tag_id'];
                    $this->user->update();
                }
                //var_dump($this->user->params['search']);
            }

    function renderSubSections() {
        if ($this->urlRouter->rootId && count($this->urlRouter->pageSections) > 1) {
            $hasQuery = false;
            $q = '';
            if ($this->urlRouter->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->urlRouter->params['q']);
            }
            echo "<ul>";
            $i = 0;
            
            $sectionClass='<span class="v';            
            switch ($this->urlRouter->rootId) {
                case 1:
                    $sectionClass='<span class="x x';
                    break;
                case 2:
                    $sectionClass='<span class="z z';
                    break;
                case 3:
                    $sectionClass='<span class="v v';
                    break;
                case 4:
                    $sectionClass='<span class="y y';
                    break;
                case 99:
                    $sectionClass='<span class="u u';
                    break;
            }
            
            if ($hasQuery) {
                $pId = $this->urlRouter->rootId == 3 ? 3 : 0;                
                
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, 0, $pId) . $q, $this->urlRouter->sectionId == 0,'ovh'), '</li>';
                foreach ($this->urlRouter->pageSections as $section_id=>$section) {
                    $selected = ($this->urlRouter->sectionId == $section_id ? true : false);
                    if ($this->extendedId || $this->localityId)
                        $selected = false;
                    $purposeId = 0;
                    if ($this->urlRouter->rootId==3) {
                        $purposeId = 3;
                    }elseif (count($section['purposes'])==1) {
                        $purposeId = array_keys($section['purposes'])[0];
                    }elseif ($this->urlRouter->purposeId && isset ($section['purposes'][$this->urlRouter->purposeId])) {
                        $purposeId = $this->urlRouter->purposeId;
                    }
                
                    echo '<li>', $this->renderSubListLink($sectionClass.$section_id.'"></span>'.$section['name'], $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $section_id, $purposeId) . $q, $selected), '</li>';
                    $i++;
                }
                /*
                foreach ($this->urlRouter->pageSections as $section) {
                    $selected = ($this->urlRouter->sectionId == $section[0] ? true : false);
                    if ($this->extendedId || $this->localityId)
                        $selected = false;
                    $purposeId = 0;
                    if (is_numeric($section[3])) {
                        $purposeId = (int) $section[3];
                    } elseif ($this->urlRouter->rootId == 3) {
                        $purposeId = 3;
                    } elseif ($this->urlRouter->purposeId) {
                        $pps = explode(',', $section[3]);
                        if ($pps && in_array($this->urlRouter->purposeId, $pps)) {
                            $purposeId = $this->urlRouter->purposeId;
                        }
                    }
                    
                    $iTmp='';
                    if($this->urlRouter->rootId==1){
                        $iTmp.='<span class="x x'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==2){
                        $iTmp.='<span class="z z'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==3){
                        $iTmp.='<span class="v v'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==4){
                        $iTmp.='<span class="y y'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==99){
                        $iTmp.='<span class="u u'.$section[0].'"></span>';
                    }else {
                        $iTmp.='<span class="v'.$section[0].'"></span>';
                    }
                    echo '<li>', $this->renderSubListLink($iTmp.$this->urlRouter->sections[$section[0]][$this->fieldNameIndex], $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $section[0], $purposeId) . $q, $selected), '</li>';
                    $i++;
                }*/
            }else {
                
                $pId = $this->urlRouter->rootId == 3 ? 3 : 0;
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, 0, $pId) . $q, $this->urlRouter->sectionId == 0,'ovh'), '</li>';
                
                foreach ($this->urlRouter->pageSections as $section_id => $section) {
                    $selected = $this->urlRouter->sectionId == $section_id ? true : false;
                    if ($this->extendedId || $this->localityId)
                        $selected = false;
                    $purposeId = 0;
                    if ($this->urlRouter->rootId==3) {
                        $purposeId = 3;
                    }elseif (count($section['purposes'])==1) {
                        $purposeId = array_keys($section['purposes'])[0];
                    }elseif ($this->urlRouter->purposeId && isset ($section['purposes'][$this->urlRouter->purposeId])) {
                        $purposeId = $this->urlRouter->purposeId;
                        $section['counter'] = $section['purposes'][$purposeId]['counter'];
                    }
                    $isNew = false;
                    if (!$selected && $this->checkNewUserContent($section['unixtime']))
                        $isNew = true;
                    $iTmp=$sectionClass.$section_id.'"></span>';
                    /*
                    $iTmp='';
                    if($this->urlRouter->rootId==1){
                        $iTmp.='<span class="x x'.$section_id.'"></span>';
                    }elseif($this->urlRouter->rootId==2){
                        $iTmp.='<span class="z z'.$section_id.'"></span>';
                    }elseif($this->urlRouter->rootId==3){
                        $iTmp.='<span class="v v'.$section_id.'"></span>';
                    }elseif($this->urlRouter->rootId==4){
                        $iTmp.='<span class="y y'.$section_id.'"></span>';
                    }elseif($this->urlRouter->rootId==99){
                        $iTmp.='<span class="u u'.$section_id.'"></span>';
                    }else {
                        $iTmp.='<span class="v'.$section_id.'"></span>';
                    }
                     * 
                     */
                    echo '<li', ($isNew ? ' class="nl">' : '>'),
                    $this->renderSubListLink($iTmp.$section['name'] . ' <b>' . $section['counter'] . '</b>', $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $section_id, $purposeId), $selected), '</li>';
                    $i++;

                }

                /*
                foreach ($this->urlRouter->pageSections as $section) {
                    $selected = $this->urlRouter->sectionId == $section[0] ? true : false;
                    if ($this->extendedId || $this->localityId)
                        $selected = false;
                    $purposeId = 0;
                    if (is_numeric($section[3])) {
                        $purposeId = (int) $section[3];
                    } elseif ($this->urlRouter->rootId == 3) {
                        $purposeId = 3;
                    } elseif ($this->urlRouter->purposeId) {
                        $pps = explode(',', $section[3]);
                        if ($pps && in_array($this->urlRouter->purposeId, $pps)) {
                            $purposeId = $this->urlRouter->purposeId;
                        }
                    }
                    $isNew = false;
                    if (!$selected && $this->checkNewUserContent($section[2]))
                        $isNew = true;
                    
                    $iTmp='';
                    if($this->urlRouter->rootId==1){
                        $iTmp.='<span class="x x'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==2){
                        $iTmp.='<span class="z z'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==3){
                        $iTmp.='<span class="v v'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==4){
                        $iTmp.='<span class="y y'.$section[0].'"></span>';
                    }elseif($this->urlRouter->rootId==99){
                        $iTmp.='<span class="u u'.$section[0].'"></span>';
                    }else {
                        $iTmp.='<span class="v'.$section[0].'"></span>';
                    }
                    
                    echo '<li', ($isNew ? ' class="nl">' : '>'),
                    $this->renderSubListLink($iTmp.$this->urlRouter->sections[$section[0]][$this->fieldNameIndex] . ' <b>' . $section[1] . '</b>', $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $section[0], $purposeId), $selected), '</li>';
                    $i++;
                }
                 * 
                 */
            }
            echo '</ul>';
        }
    }
    
    function renderMobileLocalityLinks(){
        if ($this->urlRouter->rootId == 1 && $this->urlRouter->countryId && count($this->localities)) {
            $q = '';
            if ($this->urlRouter->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->urlRouter->params['q']);
            }
            $citiesList = '';
            $prefix_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
            $keyIndex = 2;
            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';
            if ($this->urlRouter->sectionId) {
                $suffix_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '/';
                $prefix = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex] . ' ';
            } else {
                $suffix_uri.=$this->urlRouter->roots[$this->urlRouter->rootId][3] . '/';
                $prefix = $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex] . ' ';
            }
            $sectionName = $this->sectionName;
            if ($this->urlRouter->purposeId) {
                $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                        $prefix.=' ' . $this->purposeName . ' ';
                        break;
                    case 6:
                    case 7:
                        $prefix = $this->purposeName . ' ' . $prefix. ' ';
                        break;
                    default:
                        break;
                }
            }
            if ($this->urlRouter->siteLanguage != 'ar') {
                $suffix_uri.=$this->urlRouter->siteLanguage . '/';
            }
            $prefix.=$this->lang['in'] . '.. ';
            $pass = false;
            $locId = $this->localityId;
            $isParent = true;
            $hasParent = false;
            $hasChildren = false;
            if($locId && isset($this->localities[$locId])){
                if($this->localities[$locId]['parent_geo_id'] && isset($this->localities[$this->localities[$locId]['parent_geo_id']]))
                    $hasParent=true;
            }
            
            
            $childCount = 0;
            $children = array();
            foreach ($this->localities as $lid=>$sub) {
                if ($sub['parent_geo_id'] == $locId) {
                    $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                    $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '"><span class="w"></span>' . $sub['name'] . '<span class="to"></span></a></li>';
                    $pass = true;
                    $children[]=$lid;
                    $childCount++;
                }
            }
            
            
            if($childCount==1){
                $citiesList = '';
                $pass = false;
                $childId = $children[0];
                $childCount = 0;
                $children = array();
                foreach ($this->localities as $lid=>$sub) {
                    if ($sub['parent_geo_id'] == $childId) {
                        $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                        $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '"><span class="w"></span>' . $sub['name'] . '<span class="to"></span></a></li>';
                        $pass = true;
                        $children[]=$lid;
                        $childCount++;
                    }
                }
            }
            
            
            if($locId && isset($this->localities[$locId]) && !$citiesList){
                $isParent=false;
                $parentId = $this->localities[$locId]['parent_geo_id'];
                if($parentId){
                    if(isset($this->localities[$parentId])){
                        $childCount = 0;
                        $children = array();
                        foreach ($this->localities as $lid=>$sub) {
                            if ($sub['parent_geo_id'] == $parentId) {
                                $selected = false;
                                if($locId == $lid) $selected = true;
                                $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                                if($selected){
                                    $citiesList.='<li class="on"><b><span class="w"></span>' . $sub['name'] . '</b></li>';
                                }else{
                                    $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '"><span class="w"></span>' . $sub['name'] . '<span class="to"></span></a></li>';
                                }
                                $pass = true;
                                $children[]=$lid;
                                $childCount++;
                            }
                        }
                        
                        if($childCount==1){
                            $citiesList = '';
                            $pass = false;
                        }
                    }
                }else{
                    foreach ($this->localities as $lid=>$sub) {
                        if ($sub['parent_geo_id'] == 0) {
                            $selected = false;
                            if($locId == $lid) $selected = true;
                            $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                            if($selected){
                                $citiesList.='<li class="on"><b><span class="w"></span>' . $sub['name'] . '</b></li>';
                            }else{
                                $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '"><span class="w"></span>' . $sub['name'] . '<span class="to"></span></a></li>';
                            }
                            $pass = true;
                        }
                    }
                }
            }else{
                $hasChildren = true;
            }
            
            if ($citiesList) {
                
                ?> <!--googleoff: index --> <?php
                    ?><span onclick="subList(this)" class="rbt subit loc ic"></span><?php
                    ?><div id="sublist"><?php
                    ?><h2 class="ctr"><?= $this->lang['suggestionLocation'] . ($this->localityId ? $this->localities[$this->localityId]['name'] . $this->lang['?'] : $this->urlRouter->countries[$this->urlRouter->countryId]['name'] . $this->lang['?']) ?></h2><?php
                    
                $hasExt=1;
                if($locId && isset($this->localities[$locId])){                    
                    ?><ul class='ls'><?php
                    /*
                    ?><li class="bbr"><a href="<?= $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) ?>"><?= $this->lang['opt_all_areas'] ?><span class="to"></span><span class="n"><?= $this->urlRouter->pageSections[$this->urlRouter->sectionId]['counter'] ?></span></a></li><?php 
                    */
                    $alocId = (int)$locId;
                    $parentIds=array();
                    if($isParent){
                        $parentIds[]=(int)$locId;
                    }
                    if($alocId && isset($this->localities[$alocId])){
                        while($alocId && isset($this->localities[$alocId]) && isset($this->localities[$this->localities[$alocId]['parent_geo_id']])){
                            $p_id = $this->localities[$this->localities[$alocId]['parent_geo_id']]['parent_geo_id'];
                            $hasSiblings = 0;
                            if($p_id){
                                foreach ($this->localities as $sub) {
                                    if ($sub['parent_geo_id'] == $p_id) {
                                        $hasSiblings++;
                                        if($hasSiblings>1)break;
                                    }
                                }
                            }else{
                                $hasSiblings=2;
                            }
                            if($hasSiblings>1){
                                $alocId=$parentIds[]=(int)$this->localities[$alocId]['parent_geo_id'];
                            }else{
                                $alocId=(int)$this->localities[$alocId]['parent_geo_id'];
                            }
                        }
                    }       
                    if($hasParent || $hasChildren)
                        $parentIds[]=0;
                    
                    $parentIds = array_reverse($parentIds);
                    $i=1;
                    foreach($parentIds as $pid){
                        if($pid){
                            if($pid == $locId){
                                ?><li class='sm<?= $i++ ?> on'><b><span class="w"></span><?= $this->localities[$pid]['name'] ?></b></li><?php
                            }else{                                
                                $append = 'c-' . $pid . '-' . $keyIndex . '/'.$q;
                                ?><li class='sm<?= $i++ ?>'><a href='<?= $prefix_uri . $this->localities[$pid]['uri'] . $suffix_uri . $append ?>'><span class="w"></span><?= $this->localities[$pid]['name'] ?><span class='to'></span></a></li><?php
                            }
                        }else{
                            ?><li class="bbr"><a href='<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).$q ?>'><?= $this->cityName ? $this->cityName : $this->countryName ?><span class='to'></span></a></li><?php
                        }
                    }
                    
                    if($hasChildren || $hasParent){
                        ?><li class='sub sn<?= $i ?>'><ul><?php 
                        echo $citiesList;
                        ?></ul></li></ul><?php
                    }else{
                        /* ?><ul class='sm smn'><?php */
                        echo $citiesList;
                         ?></ul><?php  
                    }
                    
                }else{     
                    $citiesList = preg_replace('/\<span class\=\"w\"\>\<\/span\>/','',$citiesList);
                    ?><ul class='ls'><?php 
                    ?><li class="on bbr"><b><?= $this->cityName ? $this->cityName : $this->countryName;
                    if($this->urlRouter->sectionId){
                        ?><span class="n"><?= $this->urlRouter->pageSections[$this->urlRouter->sectionId]['counter'] ?></span><?php
                    }
                    ?></b></li><?php
                    echo $citiesList;
                    ?></ul><?php
                }
                
                ?></div><?php
                ?> <!--googleon: index --> <?php
            }
        }
    }
    function renderLocalityLinks(){
        if ($this->urlRouter->rootId == 1 && $this->urlRouter->countryId && count($this->localities)) {
            $q = '';
            if ($this->urlRouter->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->urlRouter->params['q']);
            }
            $citiesList = '';
            $prefix_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
            $keyIndex = 2;
            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';
            if ($this->urlRouter->sectionId) {
                $suffix_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '/';
                $prefix = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex] . ' ';
            } else {
                $suffix_uri.=$this->urlRouter->roots[$this->urlRouter->rootId][3] . '/';
                $prefix = $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex] . ' ';
            }
            $sectionName = $this->sectionName;
            if ($this->urlRouter->purposeId) {
                $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                        $prefix.=' ' . $this->purposeName . ' ';
                        break;
                    case 6:
                    case 7:
                        $prefix = $this->purposeName . ' ' . $prefix. ' ';
                        break;
                    default:
                        break;
                }
            }
            if ($this->urlRouter->siteLanguage != 'ar') {
                $suffix_uri.=$this->urlRouter->siteLanguage . '/';
            }
            $prefix.=$this->lang['in'] . '.. ';
            $pass = false;
            $locId = $this->localityId;
            $isParent = true;
            $hasParent = false;
            $hasChildren = false;
            if($locId && isset($this->localities[$locId])){
                if($this->localities[$locId]['parent_geo_id'] && isset($this->localities[$this->localities[$locId]['parent_geo_id']]))
                    $hasParent=true;
            }
            
            
            
            $childCount = 0;
            $children = array();
            foreach ($this->localities as $lid=>$sub) {
                if ($sub['parent_geo_id'] == $locId) {
                    $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                    $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '">' . $sub['name'] . '</a></li>';
                    $pass = true;
                    $children[]=$lid;
                    $childCount++;
                }
            }
            
            
            if($childCount==1){
                $citiesList = '';
                $pass = false;
                $childId = $children[0];
                $childCount = 0;
                $children = array();
                foreach ($this->localities as $lid=>$sub) {
                    if ($sub['parent_geo_id'] == $childId) {
                        $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                        $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '">' . $sub['name'] . '</a></li>';
                        $pass = true;
                        $children[]=$lid;
                        $childCount++;
                    }
                }
            }
            
            
            if($locId && isset($this->localities[$locId]) && !$citiesList){
                $isParent=false;
                $parentId = $this->localities[$locId]['parent_geo_id'];
                if($parentId){
                    if(isset($this->localities[$parentId])){
                        $childCount = 0;
                        $children = array();
                        foreach ($this->localities as $lid=>$sub) {
                            if ($sub['parent_geo_id'] == $parentId) {
                                $selected = false;
                                if($locId == $lid) $selected = true;
                                $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                                if($selected){
                                    $citiesList.='<li class="ov">' . $sub['name'] . '</li>';
                                }else{
                                    $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '">' . $sub['name'] . '</a></li>';
                                }
                                $pass = true;
                                $children[]=$lid;
                                $childCount++;
                            }
                        }
                        
                        if($childCount==1){
                            $citiesList = '';
                            $pass = false;
                        }
                    }
                }else{
                    foreach ($this->localities as $lid=>$sub) {
                        if ($sub['parent_geo_id'] == 0) {
                            $selected = false;
                            if($locId == $lid) $selected = true;
                            $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                            if($selected){
                                $citiesList.='<li class="ov">' . $sub['name'] . '</li>';
                            }else{
                                $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '">' . $sub['name'] . '</a></li>';
                            }
                            $pass = true;
                        }
                    }
                }
            }else{
                $hasChildren = true;
            }
            
            if ($citiesList) {
                $hasExt=1;
                ?><h4><?= $prefix ?></h4><?php 
                if($locId && isset($this->localities[$locId])){                    
                    ?><ul class='sm smn'><?php
                    
                    $alocId = (int)$locId;
                    $parentIds=array();
                    if($isParent){
                        $parentIds[]=(int)$locId;
                    }
                    if($alocId && isset($this->localities[$alocId])){
                        while($alocId && isset($this->localities[$alocId]) && isset($this->localities[$this->localities[$alocId]['parent_geo_id']])){
                            $p_id = $this->localities[$this->localities[$alocId]['parent_geo_id']]['parent_geo_id'];
                            $hasSiblings = 0;
                            if($p_id){
                                foreach ($this->localities as $sub) {
                                    if ($sub['parent_geo_id'] == $p_id) {
                                        $hasSiblings++;
                                        if($hasSiblings>1)break;
                                    }
                                }
                            }else{
                                $hasSiblings=2;
                            }
                            if($hasSiblings>1){
                                $alocId=$parentIds[]=(int)$this->localities[$alocId]['parent_geo_id'];
                            }else{
                                $alocId=(int)$this->localities[$alocId]['parent_geo_id'];
                            }
                        }
                    }       
                    if($hasParent || $hasChildren)
                        $parentIds[]=0;
                    
                    $parentIds = array_reverse($parentIds);
                    $i=1;
                    foreach($parentIds as $pid){
                        if($pid){
                            if($pid == $locId){
                                ?><li class='sm<?= $i++ ?> on'><b><?= $this->localities[$pid]['name'] ?></b></li><?php
                            }else{                                
                                //$append = 'c-' . $this->localities[$pid][0] . '-' . $keyIndex . '/'.$q;
                                $append = 'c-' . $pid . '-' . $keyIndex . '/'.$q;
                                ?><li class='sm<?= $i++ ?>'><a href='<?= $prefix_uri . $this->localities[$pid]['uri'] . $suffix_uri . $append ?>'><?= $this->localities[$pid]['name'] ?></a></li><?php
                            }
                        }else{
                            ?><li><a href='<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).$q ?>'><?= $this->cityName ? $this->cityName : $this->countryName ?></a></li><?php
                        }
                    }
                    
                    if($hasChildren || $hasParent){
                        ?><li class='sub sn<?= $i ?>'><ul><?php 
                        echo $citiesList;
                        ?></ul></li></ul><?php
                    }else{
                        /* ?><ul class='sm smn'><?php */
                        echo $citiesList;
                         ?></ul><?php  
                    }
                    
                }else{                    
                    ?><ul class='sm smn'><?php 
                    echo $citiesList;
                    ?></ul><?php
                }
            }
        }
    }
    
    function renderSearchSettings(){
        $q = $this->getPageUri().'?';
        if ($this->urlRouter->params['q']) {
            $q .= 'q=' . urlencode($this->urlRouter->params['q']).'&';
        }
        $ql = $q;
        $pl = $q;
        $q .= 'sort=';
        $ql .= 'hr=';
        $pl .= 'xd=';
        ?><h4><?= $this->lang['search_settings'] ?></h4><?php
            ?><ul class="sm smn sfilter"><?php 
            ?><li class='f Zorder' onclick="oFtr(this,'order')"><a href="javascript:void(0)"><?= $this->lang['sorting'] . ' ' . $this->lang['sorting_'.$this->sortingMode] ?> <span class="im arrowD"></span></a></li><?php
            if($this->sortingMode == 0){
                ?><li class="ov order hid sbr" onclick="cFtr(this,'order')"><?=  $this->lang['sorting_0'] ?></li><?php
            }else{
                ?><li class="order hid"><a class="sbr" href="<?= $q.'0' ?>" rel="nofollow"><?=  $this->lang['sorting_0'] ?></a></li><?php
            }
            if($this->sortingMode == 1){
                ?><li class="ov order hid sbr" onclick="cFtr(this,'order')"><?=  $this->lang['sorting_1'] ?></li><?php
            }else{
                ?><li class="order hid"><a class="sbr" href="<?= $q.'1' ?>" rel="nofollow"><?=  $this->lang['sorting_1'] ?></a></li><?php
            }
            
            $langSortIdx = $this->langSortingMode > -1 ? $this->langSortingMode : ($this->urlRouter->siteLanguage == 'ar' ? 1 : 2);
            ?><li class='f Zolang' onclick="oFtr(this,'olang')"><a href="javascript:void(0)"><?=  $this->lang['lg_sorting'] . ' ' .$this->lang['lg_sorting_'.$langSortIdx] ?> <span class="im arrowD"></span></a></li><?php
            if($langSortIdx == 0){
                ?><li class="ov olang hid sbr" onclick="cFtr(this,'olang')"><?=  $this->lang['lg_sorting_0'] ?></li><?php
            }else{
                ?><li class="olang hid"><a class="sbr" href="<?= $ql.'0' ?>" rel="nofollow"><?=  $this->lang['lg_sorting_0'] ?></a></li><?php
            }
            if($langSortIdx == 1){
                ?><li class="ov olang hid sbr" onclick="cFtr(this,'olang')"><?=  $this->lang['lg_sorting_1'] ?></li><?php
            }else{
                ?><li class="olang hid"><a class="sbr" href="<?= $ql.'1' ?>" rel="nofollow"><?=  $this->lang['lg_sorting_1'] ?></a></li><?php
            }
            if($langSortIdx == 2){
                ?><li class="ov olang hid sbr" onclick="cFtr(this,'olang')"><?=  $this->lang['lg_sorting_2'] ?></li><?php
            }else{
                ?><li class="olang hid"><a class="sbr" href="<?= $ql.'2' ?>" rel="nofollow"><?=  $this->lang['lg_sorting_2'] ?></a></li><?php
            }
            
            if(in_array($this->urlRouter->rootId,[1,2,3])){
                ?><li class='f Zopub' onclick="oFtr(this,'opub')"><a href="javascript:void(0)"><?= ($this->publisherTypeSorting ? '':"<span class='i nw'></span>").$this->lang['lg_sorting'] . ' ' . $this->lang[($this->publisherTypeSorting == 2 ? 'spub_3_'.$this->urlRouter->rootId: ( $this->publisherTypeSorting == 1 ? ($this->urlRouter->rootId == 3 ? 'sbpub_1' : 'spub_1') : 'spub_0') )] ?> <span class="im arrowD"></span></a></li><?php
                if($this->publisherTypeSorting == 0){
                    ?><li class="ov opub hid sbr" onclick="cFtr(this,'opub')"><?=  $this->lang['spub_0'] ?></li><?php
                }else{
                    ?><li class="opub hid"><a class="sbr" href="<?= $pl.'0' ?>" rel="nofollow"><?=  $this->lang['spub_0'] ?></a></li><?php
                }
                if($this->publisherTypeSorting == 1){
                    ?><li class="ov opub hid sbr" onclick="cFtr(this,'opub')"><?=  $this->urlRouter->rootId == 3 ? $this->lang['sbpub_1']:$this->lang['spub_1'] ?></li><?php
                }else{
                    ?><li class="opub hid"><a class="sbr" href="<?= $pl.'1' ?>" rel="nofollow"><?=  $this->urlRouter->rootId == 3 ? $this->lang['sbpub_1']:$this->lang['spub_1'] ?></a></li><?php
                }
                if($this->publisherTypeSorting == 2){
                    ?><li class="ov opub hid sbr" onclick="cFtr(this,'opub')"><?=  $this->lang['spub_3_'.$this->urlRouter->rootId] ?></li><?php
                }else{
                    ?><li class="opub hid"><a class="sbr" href="<?= $pl.'2' ?>" rel="nofollow"><?=  $this->lang['spub_3_'.$this->urlRouter->rootId] ?></a></li><?php
                }
            }
            
            ?></ul><?php
            if(!($this->userFavorites || $this->urlRouter->watchId)){
                $this->menu_app_banner();
            }
    }
    
    function renderExtendedLinks(){
        if ($this->urlRouter->sectionId && count($this->extended)) {
            $prefix_uri = '/';
            if ($this->urlRouter->countryId) {
                $prefix_uri.=$this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
                $keyIndex = 2;
            }
            else
                $keyIndex = 1;

            $q = '';
            if ($this->urlRouter->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->urlRouter->params['q']);
            }

            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';


            if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                $keyIndex++;
                $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3] . '/';
            }

            if ($this->urlRouter->purposeId) {
                $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                /*switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                        $suffix = ' ' . $this->purposeName;
                        break;
                    case 6:
                    case 7:
                    case 8:
                        $prefix = $this->purposeName . ' ';
                        break;
                    default:
                        break;
                }*/
            }
            if ($this->urlRouter->siteLanguage != 'ar') {
                $suffix_uri.=$this->urlRouter->siteLanguage . '/';
            }                            
            $hasExt=1;
            
            $iTmp='';
            if($this->urlRouter->rootId==1){
                $iTmp.='<span class="x x'.$this->urlRouter->sectionId.'"></span>';
            }elseif($this->urlRouter->rootId==2){
                $iTmp.='<span class="z z'.$this->urlRouter->sectionId.'"></span>';
            }elseif($this->urlRouter->rootId==3){
                $iTmp.='<span class="v v'.$this->urlRouter->sectionId.'"></span>';
            }elseif($this->urlRouter->rootId==4){
                $iTmp.='<span class="y y'.$this->urlRouter->sectionId.'"></span>';
            }elseif($this->urlRouter->rootId==99){
                $iTmp.='<span class="u u'.$this->urlRouter->sectionId.'"></span>';
            }else {
                $iTmp.='<span class="v'.$this->urlRouter->sectionId.'"></span>';
            }
            
            ?><h4><?= $iTmp.$this->sectionName ?></h4><?php
            ?><ul class="sm smn"><li class='sub sub2'><ul><?php 
                    if($this->extendedId==0){
                        echo "<li class='f ov'>".$this->lang['opt_all_categories']."</li>";
                    }else{
                        echo "<li class='f'>".$this->renderSubListLink($this->lang['opt_all_categories'], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId), 0)."</li>";
                    }
                        foreach ($this->extended as $eid=>$sub) {
                            $append = 'q-' . $eid . '-' . $keyIndex . '/'.$q;
                            if ($this->extendedId == $eid) {
                                ?><li class="ov"><?= $prefix . $sub['name'] . $suffix ?></li><?php
                            } else {
                                ?><li><a href="<?= $prefix_uri . $this->urlRouter->sections[$this->urlRouter->sectionId][3] . '-' . $sub['uri'] . $suffix_uri . $append ?>"><?= $prefix . $sub['name'] . $suffix ?></a></li><?php
                            }
                        }
                        ?></ul></li></ul><?php
        }
    }
    
    function renderSideSections($noAds=false) {
        //$this->renderLoginBox();
        $noAds = $this->urlRouter->cfg['enabled_ads'] ? $noAds : 0;
        $countAds=0;
        if(isset($this->searchResults['body']['matches'])) $countAds=count($this->searchResults['body']['matches']);
        if($this->urlRouter->module=='detail') {
            if(!$this->detailAdExpired){
                if($this->detailAd[Classifieds::PUBLICATION_ID]==1)
                    $countAds+=4;
                else{
                    $countAds+=2;
                }
            }            
        }
        ?><ul id="siAd" class="list"><?php
            
            
                if($this->user->info['id'] && $this->user->info['level']==9 && !$this->urlRouter->userId && $this->urlRouter->module=='detail' && isset($this->detailAd[( $this->urlRouter->siteLanguage=='ar' ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN )]) && !$this->detailAdExpired 
                        && $this->extended){
                    $extId=$this->detailAd[( $this->urlRouter->siteLanguage=='ar' ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN )];
                    $detailYear = Classifieds::detectYear($this->detailAd[Classifieds::CONTENT]);
                    if($detailYear){
                        $currency='$';
                        $priceRange = $this->urlRouter->db->getSectionPriceRange($this->detailAd[Classifieds::COUNTRY_ID],$extId,1);
                        //var_dump($priceRange);
                        if(isset($priceRange[$detailYear])){
                            
                            $append_uri = ($this->urlRouter->siteLanguage != 'ar' ? $this->urlRouter->siteLanguage . '/' : '') . 'q-' . $extId . '-' . ($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3 : 2) : 1).'/price/';
                            $extended_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
                            if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                                $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3] . '/';
                            }
                            $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '-' . $this->extended[$extId]['uri'] . '/';
                            
                            echo '<li><div class="prx ',  $this->urlRouter->siteLanguage,'">';
                            echo '<p>';
                            echo $this->lang['price_box_pre'],'<b>',$this->extended[$extId]['name'],'</b>',$this->lang['price_model'],'<b>',$detailYear,'</b>',$this->lang['price_box_suf'];
                            echo $priceRange[$detailYear][1],$currency,  $this->lang['and'] ,$priceRange[$detailYear][2],$currency;
                            echo '</p>';
                            echo '<a href="',$extended_uri,$append_uri,'" class="bt">',  ($this->urlRouter->siteLanguage =='ar' ? $this->lang['price_more'].$this->extended[$extId][1] : $this->extended[$extId][1].$this->lang['price_more']),'</a>';
                            echo '<p class="nb">';
                            echo $this->lang['price_claimer'];
                            echo '</p>';
                            echo '</div></li>';
                        }
                    }
                }
                /*
                if($countAds){
                    ?><li class="adLnk"><a href="/post/<?= $this->urlRouter->siteLanguage=='ar'?'':'en/' ?>" class="bt"><?= $this->lang['addAd'] ?></a></li><?php
                }
                */
                if(!$noAds){
                    switch($countAds){
                        case 1:
                            ?><li class="lad"><?php echo $this->fill_ad("zone_6", 'ad_m') ?></li><?php
                            $this->renderSideFeatures();
                            break;
                        case 2:
                        case 3:
                            ?><li class="lad a600"><?php echo $this->fill_ad("zone_10", 'ad_x') ?></li><?php
                            $this->renderSideFeatures();
                            break;
                        default:
                            ?><li class="lad"><?php echo $this->fill_ad("zone_6", 'ad_m') ?></li><?php 
                            $this->renderSideFeatures();
                            ?><li class="lad a600"><?php echo $this->fill_ad("zone_10", 'ad_x') ?></li><?php
                            if($countAds > 8){
                                ?><li class="lad a600"><?php echo $this->fill_ad("zone_11", 'ad_x') ?></li><?php
                            }
                            break;
                    }
                    /*
                    ?><li class="lad"><?php echo $this->fill_ad("zone_6", 'ad_m') ?></li><?php
                    
                    if($countAds>2) {
                        ?><li class="lad"><?php echo $this->fill_ad("zone_10", 'ad_x') ?></li><?php
                    }*/
                }
                /*if(!($this->userFavorites || $this->urlRouter->watchId)){
                    $this->side_app_banner();
                }*/
                
                if($this->urlRouter->cfg['enabled_ads'] && $countAds >= 10) {
                ?><li><?php
                ?><ins class="adsbygoogle" class="ad600" data-ad-client="ca-pub-2427907534283641" data-ad-slot="9190558623"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script><?php
                ?></li><?php }
                if(0 && $this->urlRouter->countryId == 1){
                    ?><li><iframe class="adsawa" src="/web/gosawa.html"></iframe></li><?php
                }
                
                if(0 && $countAds > 7) {
                    ?><li><div class="g-page" data-href="https://plus.google.com/+MourjanAds/posts" data-width="300" data-layout="landscape" data-rel="publisher"></div></li><?php
                    ?><li><div class="fb-like-box fb-like-side" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="300" data-show-faces="true" data-stream="false" data-show-border="false" data-header="false"></div></li><?php 
                }
            ?></ul><?php
    }

    
    function filterPurposesMobile() {return;
        if ($this->urlRouter->rootId!=4 && ($this->urlRouter->rootId || $this->urlRouter->sectionId) && count($this->urlRouter->purposes)>1 && !($this->urlRouter->purposeId && count($this->urlRouter->pagePurposes)==1)) {
            $q = '';
            $i = 0;
            $hasQuery = false;
            if ($this->urlRouter->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->urlRouter->params['q']);
            }
            // fr becomes ls
            if ($this->urlRouter->purposeId && !$this->paginationString)
                echo "<ul class='ls br'>";
            else
                echo "<ul class='ls'>";
            if ($hasQuery) {
                foreach ($this->urlRouter->pagePurposes as $pid=>$purpose) {
                    if ($this->urlRouter->purposeId == $pid) {
                        echo '<li class="on" style="display:block;padding:5px;height:30px;line-height:33px;font-weight:normal;">', $purpose['name'], '</li>';
                        //echo '<li><span class="bt">', $purpose['name'], '</span></li>';
                    } else {
                        //echo '<li><a class="bt" href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pid) . $q . '">', $purpose['name'], '</a></li>';
                        echo '<li><a  href="'.$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pid) . $q . '">', $purpose['name'],'<span class="to"></span></a></li>';
                    }                    
                }
            }else {
                $append_uri = '';
                $extended_uri = '';
                if ($this->extendedId) {
                    $append_uri = '/' . ($this->urlRouter->siteLanguage != 'ar' ? $this->urlRouter->siteLanguage . '/' : '') . 'q-' . $this->extendedId . '-' . ($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3 : 2) : 1);
                    $extended_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3] . '/';
                    }
                    $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '-' . $this->extended[$this->extendedId]['uri'] . '/';
                } elseif ($this->localityId) {
                    $append_uri = '/' . ($this->urlRouter->siteLanguage != 'ar' ? $this->urlRouter->siteLanguage . '/' : '') . 'c-' . $this->localityId . '-' . ($this->hasCities && $this->urlRouter->cityId ? 3 : 2);
                    $extended_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
                    $extended_uri.=$this->localities[$this->localityId]['uri'] . '/';
                    $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '/';
                }
                
                
                
                foreach ($this->urlRouter->pagePurposes as $pid=>$purpose) {
                    $selected = ($this->urlRouter->purposeId == $pid);
                    
                    if ($this->extendedId || $this->localityId) {
                        if ($selected) {
                            //echo '<li><span class="bt">', $purpose['name'], '</span></li>';
                            echo '<li class="on" style="display:block;padding:5px;height:30px;line-height:33px;font-weight:normal;">', $purpose['name'], '</li>';
                        } else {
                            //echo '<li><a class="bt" href="' . $extended_uri . $this->urlRouter->purposes[$pid][3] . $append_uri . '/' . '">', $purpose['name'], '</a></li>';
                            echo '<li><a  href="'. $extended_uri . $this->urlRouter->purposes[$pid][3] . $append_uri . '/' . '">', $purpose['name'], '<span class="to"></span></a></li>';
                        }                        
                    } else {
                        if ($selected) {
                            //echo '<li><span class="bt">', $purpose['name'], '</span></li>';
                            echo '<li class="on" style="display:block;padding:5px;height:30px;line-height:33px;font-weight:normal;">', $purpose['name'], '</li>';
                        } else {
                            //echo '<li><a class="bt" href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pid) . $q . '">', $purpose['name'], '</a></li>';
                            echo '<li><a  href="' . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pid) . $q . '">', $purpose['name'], '<span class="to"></span></a></li>';
                        }

                    }

                    $i++;
                }
            }
            echo '</ul><br />';
        }
    }
   
    function getBreadCrumb($forceSetting = false, $count = 0) {
        if ($this->crumbString && !$forceSetting)
            return $this->crumbString;
        if (!$forceSetting || $this->urlRouter->module == 'detail') {
            if (isset($this->urlRouter->params['tag_id']) && isset($this->extended[$this->urlRouter->params['tag_id']])) {
                $this->extendedId = $this->urlRouter->params['tag_id'];
            }
        }
        $isDynamic = false;
        $q = "";
        if ($this->urlRouter->params['q']) {
            $q = htmlspecialchars($this->urlRouter->params['q'], ENT_QUOTES);
        } elseif ($this->urlRouter->force_search && !$this->rss) {
            $q = $this->lang['search_general'];
        }
        $tempTitle = '';
        $subPurpose = '';
        if ($this->extendedId && $this->urlRouter->sectionId) {
            $append_uri = ($this->urlRouter->siteLanguage != 'ar' ? $this->urlRouter->siteLanguage . '/' : '') . 'q-' . $this->extendedId . '-' . ($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3 : 2) : 1);
            if ($this->urlRouter->countryId)
                $extended_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';
            else
                $extended_uri = '/';
            $this->title = $this->extended[$this->extendedId]['name']; //[1]
            if ($this->urlRouter->isPriceList) {
                $this->title= ($this->urlRouter->siteLanguage=='ar' ? $this->lang['price_more'].$this->title : $this->title.$this->lang['price_more']);
            }elseif ($this->urlRouter->purposeId) {
                switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                        $subPurpose = $this->sectionName . ' ' . $this->purposeName;
                        $this->title.=' ' . $this->purposeName;
                        break;
                    case 6:
                    case 7:
                    case 999:
                        $subPurpose = $this->purposeName . ' ' . $this->sectionName;
                        $this->title = $this->purposeName . ' ' . $this->title;
                        break;
                    default:
                        break;
                }
            }
            $sub_title = $this->title;
            if ($this->urlRouter->countryId)
                $this->title.=' ' . $this->lang['in'] . ' ';

            if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3] . '/';
                $this->title.=$this->urlRouter->cities[$this->urlRouter->cityId][$this->fieldNameIndex] . ' ';
            }

            $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '-' . $this->extended[$this->extendedId]['uri'] . '/';
            if ($this->urlRouter->purposeId)
                $extended_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
            $extended_uri.=$append_uri . '/';
            $this->extended_uri = $extended_uri;
            if ($this->urlRouter->countryId)
                $this->title.=$this->urlRouter->countries[$this->urlRouter->countryId]['name'];
        }elseif ($this->localityId && $this->urlRouter->sectionId) {
            $prefix_parent_name = '';
            $suffix_parent_uri = '';
            $prefix_append_uri = '';
            $suffix_append_uri = '';
            $prefix_append_uri = ($this->urlRouter->siteLanguage != 'ar' ? $this->urlRouter->siteLanguage . '/' : '') . 'c-';
            $append_uri = $prefix_append_uri . $this->localityId . '-';
            $extended_uri = '/' . $this->urlRouter->countries[$this->urlRouter->countryId]['uri'] . '/';

            $keyIndex = 2;
            /* if ($this->hasCities && $this->urlRouter->cityId) {
              $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
              $keyIndex=3;
              } */
            $append_uri.=$keyIndex;
            $suffix_append_uri = '-' . $keyIndex . '/';
            $prefix_parent_uri = $extended_uri;

            $extended_uri.=$this->localities[$this->localityId]['uri'] . '/';
            $this->title = $this->localities[$this->localityId]['name'];
            if ($this->urlRouter->sectionId) {
                $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3] . '/';
                $suffix_parent_uri = '/' . $this->urlRouter->sections[$this->urlRouter->sectionId][3] . '/';
                $this->title = $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
            } else {
                $extended_uri.=$this->urlRouter->roots[$this->urlRouter->rootId][3] . '/';
                $suffix_parent_uri = '/' . $this->urlRouter->roots[$this->urlRouter->rootId][3] . '/';
                $this->title = $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex];
            }
            if ($this->urlRouter->purposeId) {
                $extended_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                $suffix_parent_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3] . '/';
                switch ($this->urlRouter->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                        $this->title.=' ' . $this->purposeName;
                        break;
                    case 6:
                    case 7:
                    case 999:
                        $this->title = $this->purposeName . ' ' . $this->title;
                        break;
                    default:
                        break;
                }
                $subPurpose = $this->title;
            }
            $extended_uri.=$append_uri . '/';
            $this->extended_uri = $extended_uri;
            $prefix_parent_name = $this->title . ' ' . $this->lang['in'] . ' ';
            $this->title.=' ' . $this->lang['in'] . ' ' . $this->localities[$this->localityId]['name'];
            $sub_title = $this->title;
        } else {
            $this->extendedId=0;
            $this->localityId=0;
            if ($forceSetting) {
                $uri = rtrim($this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId, false), '/');

                $url_codes = $this->urlRouter->FetchUrl($uri);
                if ($url_codes) {
                    $this->urlRouter->pageTitle['en'] = $url_codes[6];
                    $this->urlRouter->pageTitle['ar'] = $url_codes[7];
                    $this->title = $this->urlRouter->pageTitle[$this->urlRouter->siteLanguage];
                }
            }
            $tempTitle = '';
            if ($this->urlRouter->pageTitle[$this->urlRouter->siteLanguage] == '' || $q || (!$this->urlRouter->sectionId && !$this->urlRouter->rootId)) {
                if (!$q && !$this->urlRouter->sectionId && !$this->urlRouter->rootId)
                    $tempTitle = $this->urlRouter->pageTitle[$this->urlRouter->siteLanguage];
                $this->title = $this->getDynamicTitle($forceSetting);
                $isDynamic = true;
            }elseif ($count) {
                $this->title = preg_replace('/' . $this->lang['mourjan'] . '\s/i', '', $this->title);
            }
            $pos = strrpos($this->title, ' ' . $this->lang['in'] . ' ');
            $sub_title = (empty($pos) ? $this->title : substr($this->title, 0, $pos));
            /* if ($this->title==$this->lang['title_full']) {
              $this->title=$this->lang['mourjan'];
              } */
        }
        $bc = array();

        $countryId = $this->urlRouter->countryId;
        $countryName = $this->countryName;
        if(!$countryId){
            $countryName=$this->lang['mourjan'];
        }
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId = $this->user->params["country"];
            $countryName = $this->urlRouter->countries[$this->user->params["country"]]['name'];
        }
        $bc[] = "<div class='brd' itemprop='breadcrumb'><a href='" . $this->urlRouter->getURL($countryId) . "'>{$countryName}</a>";

        if ($this->hasCities && $this->urlRouter->cityId) {
            $bc[] = "<a href='" .
                    $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) .
                    "'>{$this->cityName}</a>";
        }

        if ($this->userFavorites) {
            $bc[] = "<b>" . $this->lang['myFavorites'] . "</b>";
        } elseif($this->urlRouter->watchId) {
            $bc[] = "<b>" . $this->lang['myList'] . "</b>";
        } else {
            if ($this->urlRouter->rootId) {
                $purposeId = 0;
                if (isset($this->urlRouter->pageRoots[$this->urlRouter->rootId]) && count($this->urlRouter->pageRoots[$this->urlRouter->rootId]['purposes']==1))
                    $purposeId = array_keys ($this->urlRouter->pageRoots[$this->urlRouter->rootId]['purposes'])[0];
                /*
                $purposeId = isset($this->urlRouter->pageRoots[$this->urlRouter->rootId]) ? $this->urlRouter->pageRoots[$this->urlRouter->rootId][3] : 0;
                if (is_numeric($purposeId))
                    $purposeId = (int) $purposeId + 0;
                else
                    $purposeId = 0;
                */
                if (($q || $this->urlRouter->purposeId || $this->urlRouter->sectionId) && !($this->urlRouter->sectionId == 0 && $purposeId)) {
                    $bc[] = "<a href='" .
                            $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, 0, $purposeId) .
                            "'>{$this->rootName}</a>";
                }
                if ($this->urlRouter->sectionId) {
                    if (array_key_exists($this->urlRouter->sectionId, $this->urlRouter->pageSections)) {
                        $purposeId = $this->urlRouter->pageSections[$this->urlRouter->sectionId]['purposes'];
                        if (is_numeric($purposeId))
                            $purposeId = (int) $purposeId;
                        else
                            $purposeId = 0;
                    } else {
                        $purposeId = 0;
                    }
                    if ($this->extendedId || $this->localityId || (($q || $this->urlRouter->purposeId) && !$purposeId)) {
                        $bc[] = "<a href='" .
                                $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $purposeId) .
                                "'>{$this->sectionName}</a>";
                    }
                }
            }

            if ($q) {
                if ($subPurpose) {
                    $bc[] = "<a href='" .
                            $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) .
                            "'>" . $subPurpose . "</a>";
                }
                //if ($forceSetting || $this->urlRouter->module=="detail") {
                if ($this->extendedId || $this->localityId) {
                    if ($this->localityId) {
                        $localityId = $this->localityId+0;
                        $tmp = array();
                        while ($this->localities[$localityId]['parent_city_id']) {
                            $localityId = $this->localities[$localityId]['parent_geo_id']+0;
                            if(isset($this->localities[$localityId])){
                                $tmp[] = "<a href='" .
                                        $prefix_parent_uri . $this->localities[$localityId]['uri'] . $suffix_parent_uri . $prefix_append_uri . $localityId . $suffix_append_uri .
                                        "'>" . $prefix_parent_name . $this->localities[$localityId]['name'] . "</a>";
                            }else{
                                break;
                            }
                        }
                        $k = count($tmp);
                        if ($k) {
                            for ($j = $k - 1; $j >= 0; $j--) {
                                $bc[] = $tmp[$j];
                            }
                        }
                    }
                    $bc[] = "<a href='" .
                            $extended_uri .
                            "'>" . $sub_title . "</a>";
                }
//                    else {
//                        $bc[]="<a href='".
//                            $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).
//                            "'>".$sub_title."</a>";
//                    }
//                } else {
//                    if ($this->localityId){
//                        $localityId=$this->localityId;
//                        $tmp=array();
//                        while($this->localities[$localityId][5]) {
//                            $localityId=$this->localities[$localityId][6];
//                            $tmp[]="<a href='".
//                            $prefix_parent_uri.$this->localities[$localityId][3].$suffix_parent_uri.$prefix_append_uri.$this->localities[$localityId][0].$suffix_append_uri.
//                            "'>".$prefix_parent_name.$this->localities[$localityId][1]."</a>";
//                        }
//                        $k=count($tmp);
//                        if ($k){
//                            for($j=$k-1;$j>=0;$j--){
//                                $bc[]=$tmp[$j];
//                            }
//                        }
//                    }
//                    $bc[] = '<h1 itemprop="headline name">'.$sub_title.'</h1>';
//                }
//                elseif ($this->urlRouter->purposeId){
//                    $bc[]="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId)}'>".
//                            $this->purposeName."</a>";
//                }
                if ($forceSetting || $this->urlRouter->module == "detail") {
                    $qStr = '';
                    if ($this->urlRouter->params['q'])
                        $qStr = '?q=' . urlencode($this->urlRouter->params['q']);
                    if ($this->extendedId || $this->localityId) {
                        $bc[] = "<a href='" .
                                $extended_uri . $qStr .
                                "'>" . $q . ' ' . $this->lang['in'] . ' ' . $sub_title . "</a>";
                    } else {
                        $bc[] = "<a href='" .
                                $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) . $qStr .
                                "'>" . $q . ' ' . $this->lang['in'] . ' ' . $sub_title . "</a>";
                    }
                } else {
                    $qStr = '';
                    if ($this->urlRouter->params['q'])
                        $qStr = '?q=' . urlencode($this->urlRouter->params['q']);
                    if ($this->urlRouter->rootId || $this->urlRouter->sectionId || $this->urlRouter->purposeId) {
                        $bc[] = '<a class="i rss" target="_blank" href="' .
                                (($this->extendedId || $this->localityId) ? $extended_uri . $qStr : $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) . $qStr ) .
                                '&rss=1" id="rss-link">' . '</a><b itemprop="headline">' . ($isDynamic ? '' : $q . " " . $this->lang['in'] . " ") . $sub_title . '</b>';
                        if (!$isDynamic)
                            $this->title = $q . ' ' . $this->lang['in'] . ' ' . $this->title;
                    }else {
                        $bc[] = '<a class="i rss" target="_blank" href="' .
                                (($this->extendedId || $this->localityId) ? $extended_uri . $qStr : $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) . $qStr ) .
                                '&rss=1" id="rss-link">' . '</a><b itemprop="headline">' . $q . '</b>';
                        if (!$isDynamic)
                            $this->title = $q . ' ' . $this->lang['in'] . ' ' . $this->title;
                    }
                }
            }else {
                if ($subPurpose && ($this->localityId || $this->extendedId)) {
                    $bc[] = "<a href='" .
                            $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) .
                            "'>" . $subPurpose . "</a>";
                }
                //var_dump($this->localityId);
                if ($forceSetting || $this->urlRouter->module == "detail" || $this->urlRouter->isPriceList) {
                    if ($this->extendedId || $this->localityId) {
                        if ($this->localityId && isset($this->localities[$this->localityId])) {
                            $localityId = $this->localityId+0;
                            $tmp = array();
                            while (isset($this->localities[$localityId]) && $this->localities[$localityId]['parent_city_id'] && isset($this->localities[$this->localities[$localityId]['parent_geo_id']])) {
                                $localityId = $this->localities[$localityId]['parent_geo_id']+0;
                                $tmp[] = "<a href='" .
                                        $prefix_parent_uri . $this->localities[$localityId]['uri'] . $suffix_parent_uri . $prefix_append_uri . $localityId . $suffix_append_uri .
                                        "'>" . $prefix_parent_name . $this->localities[$localityId]['name'] . "</a>";
                            }
                            $k = count($tmp);
                            if ($k) {
                                for ($j = $k - 1; $j >= 0; $j--) {
                                    $bc[] = $tmp[$j];
                                }
                            }
                        }                     
                        
                        if($this->urlRouter->isPriceList){
                            $bc[] = "<a href='" .
                                $extended_uri .
                                "'>" . trim(preg_replace('/price|سعر/','',$sub_title)) . "</a>";
                            
                            $bc[] = '<b class="brdb" itemprop="headline name">' . $this->lang['priceList'] . '</b>';
                        }else {
                            
                            $bc[] = "<a href='" .
                                $extended_uri .
                                "'>" . $sub_title . "</a>";   
                        }
                    } else {
                        $bc[] = "<a href='" .
                                $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId) .
                                "'>" . $sub_title . "</a>";
                    }
                } else {
                    if ($this->localityId) {
                        $localityId = $this->localityId+0;
                        $tmp = array();
                        while ($this->localities[$localityId]['parent_city_id']) {
                            $localityId = $this->localities[$localityId]['parent_geo_id']+0;
                            if (isset($this->localities[$localityId])) {
                                $tmp[] = "<a href='" .
                                        $prefix_parent_uri . $this->localities[$localityId]['uri'] . $suffix_parent_uri . $prefix_append_uri . $localityId . $suffix_append_uri .
                                        "'>" . $prefix_parent_name . $this->localities[$localityId]['name'] . "</a>";
                            }
                            else
                                break;
                        }
                        $k = count($tmp);
                        if ($k) {
                            for ($j = $k - 1; $j >= 0; $j--) {
                                $bc[] = $tmp[$j];
                            }
                        }
                    }
                    $bc[] = '<a class="i rss" target="_blank" href="' .
                            ($this->extendedId || $this->localityId ? $extended_uri : $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId)) .
                            '?rss=1" id="rss-link">' .
                            '</a><b itemprop="headline name">' . $sub_title . '</b>';
                }
                $this->subTitle = $sub_title;
            }
        }

        //$bread =  implode("<span>{$this->lang['sep']}</span>", $bc).'</div>';
        $bread = implode("", $bc) . '</div>';

        $this->crumbTitle = $this->title;

        if ($tempTitle)
            $this->title = $tempTitle;
        return $this->crumbString = $bread;
    }

    function getDynamicTitle($forceSetting = false) {
        if ($this->dynamicTitle && !$forceSetting)
            return $this->dynamicTitle;
        $current = "";
        $last = "";
        $summery = "";
        $location = "";
        $q = "";
        $appendLocation = false;
        if ($this->urlRouter->params['q']) {
            $q = htmlspecialchars($this->urlRouter->params['q'], ENT_QUOTES);
        } elseif ($this->urlRouter->force_search) {
            $q = $this->lang['search_general'];
        }
        $countryId = $this->urlRouter->countryId;
        $countryName = $this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId = $this->user->params["country"];
            $countryName = $this->urlRouter->countries[$this->user->params["country"]]['name'];
        }
        $location = $current = $this->countryName;
        if ($this->hasCities && $this->urlRouter->cityId) {
            $last = $current;
            $current = $this->cityName;
            $location = $this->cityName . " " . $location;
        }
        if ($this->userFavorites) {
            $summery = $current = $this->lang['myFavorites'];
        } elseif($this->urlRouter->watchId) {
            $summery = $current = $this->lang['myList'];
        } else {
            $defPurpose = count($this->urlRouter->purposes) > 1 ? false : true;
            if ($this->urlRouter->rootId) {
                $last = $current;
                $summery = $current = $this->rootName;

                if ($this->urlRouter->sectionId) {
                    $last = $current;
                    $summery = $current = $this->sectionName;
                }
                $appendLocation = true;
            }

            if ($this->urlRouter->purposeId) {
                if ($this->urlRouter->rootId || $this->urlRouter->sectionId) {
                    switch ($this->urlRouter->purposeId) {
                        case 1:
                        case 2:
                        case 999:
                            $last = $current;
                            $summery = $current = $current . " " . $this->purposeName;
                            break;
                        case 6:
                        case 7:
                            $last = $current;
                            $summery = $current = $this->purposeName . " " . $current;
                            break;
                        case 3:
                            $in = "";
                            if ($this->urlRouter->siteLanguage == "en")
                                $in = " {$this->lang['in']}";

                            if ($this->urlRouter->sectionId) {
                                $last = $current;
                                if ($this->urlRouter->siteLanguage == "ar")
                                    $summery = $current = 'مطلوب ' . $current;
                                else
                                    $summery = $current = $current . ' ' . $this->purposeName;
                            }else {
                                $last = $current;
                                $summery = $current = $this->purposeName;
                            }

                            break;
                        case 4:

                        case 5:
                            $in = "";
                            if ($this->urlRouter->siteLanguage == "en")
                                $in = " {$this->lang['in']}";
                            if ($this->urlRouter->sectionId) {
                                $last = $current;
                                $summery = $current = $this->purposeName . $in . " " . $current;
                            } else {
                                $last = $current;
                                $summery = $current = $this->purposeName;
                            }
                            break;
                    }
                } else {
                    $last = $current;
                    $summery = $current = $this->purposeName . " " . $this->lang['in'] . " " . $current;
                }
            } else {
                $appendLocation = true;
            }
            if ($last == "" || $last == $current)
                $current = $this->lang['search_general'];
            if ($q) {
                if ($summery) {
                    $summery = $q . " " . $this->lang['in'] . " " . $summery;
                } else {
                    $summery = $q;
                }
            }
        }

        if (!$summery)
            $summery = $this->lang['search_general'];
        if ($this->userFavorites) {
            $this->dynamicTitle = $summery;
        } else {
            if ($appendLocation) {
                $this->dynamicTitle = $summery . " " . $this->lang['in'] . " " . $location;
            } else {
                $this->dynamicTitle = $summery;
            }
        }
        return $this->dynamicTitle;
    }
    
    
    function getFeatureAdSection($ad) {
        $section='';
        switch($ad[Classifieds::PURPOSE_ID]){
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex].' '.$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].' '.$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].'/', $this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex])){
                    $section=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                }else {
                    $in=' ';
                    if ($this->urlRouter->siteLanguage=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].$in.$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                }
                break;
        }           
        return $section;
    }

    function getBreadCrumb1($forceSetting = false) {
        if ($this->crumbString && !$forceSetting)
            return $this->crumbString;
        $current = "";
        $last = "";
        $summery = "";
        $location = "";
        $q = "";
        $bread = "";
        $appendLocation = false;
        if ($this->urlRouter->params['q']) {
            $q = htmlspecialchars($this->urlRouter->params['q'], ENT_QUOTES);
        } elseif ($this->urlRouter->force_search) {
            $q = $this->lang['search_general'];
        }
        $countryId = $this->urlRouter->countryId;
        $countryName = $this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId = $this->user->params["country"];
            $countryName = $this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        $bread.="<div class='brd'><a href='" . $this->urlRouter->getURL($countryId) . "'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        $location = $current = $this->countryName;
        if ($this->hasCities && $this->urlRouter->cityId) {
            $bread.="<a href='" . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId) . "'>{$this->cityName}</a> <span>{$this->lang['sep']}</span> ";
            $last = $current;
            $current = $this->cityName;
            $location = $this->cityName . " " . $location;
        }
        if ($this->userFavorites) {
            $bread.="<h1>" . $this->lang['myFavorites'] . "</h1>";
            $summery = $current = $this->lang['myFavorites'];
        } else {
            $defPurpose = count($this->urlRouter->purposes) > 1 ? false : true;
            if ($this->urlRouter->rootId) {
                if (($q || $this->urlRouter->purposeId || $this->urlRouter->sectionId) && !($this->urlRouter->sectionId == 0 && $defPurpose)) {
                    $bread.="<a href='" . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId) . "'>{$this->rootName}</a> <span>{$this->lang['sep']}</span> ";
                    $last = $current;
                    $summery = $current = $this->rootName;
                } else {
                    $last = $current;
                    $summery = $current = $this->rootName;
                }
                if ($this->urlRouter->sectionId) {
                    if (($q || $this->urlRouter->purposeId) && !$defPurpose) {
                        $bread.="<a href='" . $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId) . "'>{$this->sectionName}</a> <span>{$this->lang['sep']}</span> ";
                        $last = $current;
                        $summery = $current = $this->sectionName;
                    } else {
                        $last = $current;
                        $summery = $current = $this->sectionName;
                    }
                }
                $appendLocation = true;
            }

            if ($this->urlRouter->purposeId) {
                if ($this->urlRouter->rootId || $this->urlRouter->sectionId) {
                    switch ($this->urlRouter->purposeId) {
                        case 1:
                        case 2:
                        case 999:
                            $last = $current;
                            $summery = $current = $current . " " . $this->purposeName;
                            break;
                        case 6:
                        case 7:
                            $last = $current;
                            $summery = $current = $this->purposeName . " " . $current;
                            break;
                        case 3:
                            $in = "";
                            if ($this->urlRouter->siteLanguage == "en")
                                $in = " {$this->lang['in']}";

                            if ($this->urlRouter->sectionId) {
                                $last = $current;
                                if ($this->urlRouter->siteLanguage == "ar")
                                    $summery = $current = 'مطلوب ' . $current;
                                else
                                    $summery = $current = $current . ' ' . $this->purposeName;
                                //$summery=$current=$this->purposeName.$in." ".$current;
                            }else {
                                $last = $current;
                                $summery = $current = $this->purposeName;
                            }

                            break;
                        case 4:

                        case 5:
                            $in = "";
                            if ($this->urlRouter->siteLanguage == "en")
                                $in = " {$this->lang['in']}";
                            if ($this->urlRouter->sectionId) {
                                $last = $current;
                                $summery = $current = $this->purposeName . $in . " " . $current;
                            } else {
                                $last = $current;
                                $summery = $current = $this->purposeName;
                            }
                            break;
                    }
                } else {
                    if ($q) {
                        $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId)}'>" . $this->purposeName . " " . $this->lang['in'] . " " . $current . "</a> <span>{$this->lang['sep']}</span>";
                        $last = $current;
                        $summery = $current = $this->purposeName . " " . $this->lang['in'] . " " . $current;
                    } else {
                        $last = $current;
                        $summery = $current = $this->purposeName . " " . $this->lang['in'] . " " . $current;
                    }
                }
            } else {
                $appendLocation = true;
            }
            if ($last == "" || $last == $current)
                $current = $this->lang['search_general'];
            if ($q) {
                if ($current && $current != $this->lang['search_general']) {
                    $current = "<h1>" . $q . " " . $this->lang['in'] . " " . $current . "</h1>";
                } else {
                    $current = $q;
                }
                if ($summery) {
                    $summery = $q . " " . $this->lang['in'] . " " . $summery;
                } else {
                    $summery = $q;
                }
                if ($forceSetting || $this->urlRouter->module == "detail") {
                    $qStr = '?q=' . urlencode($this->urlRouter->params['q']);
                    $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId)}.$qStr'>" . $current . "</a>";
                } else {
                    $bread.="<h1>" . $current . "</h1>";
                }
            } else {
                if ($forceSetting || $this->urlRouter->module == "detail" && $current != $this->lang['search_general']) {
                    $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId)}'>" . $current . "</a>";
                } else {
                    $bread.="<h1>" . $current . "</h1>";
                }
            }
        }

        $bread.="</div>";
        if (!$summery)
            $summery = $this->lang['search_general'];
        if ($this->userFavorites) {
            $this->title = $this->crumbTitle = $summery;
        } else {
            if ($appendLocation) {
                $this->title = $this->crumbTitle = $summery . " " . $this->lang['in'] . " " . $location;
            } else {
                $this->title = $this->crumbTitle = $summery;
            }
        }
        return $this->crumbString = $bread;
    }


	/*
    function _getAdTitle() {
        $title = '';
        $ref = '';
        //$append=false;
        if ($this->detailAd[Classifieds::TITLE])
            $title = preg_replace('/"/', '', $this->detailAd[Classifieds::TITLE]);
        else {
            $title = preg_replace('/["\']/', '', $this->getAdTitle($this->detailAd));
        }

        //$append=true;
        if (!empty($title) && $title[0] == '#') {
            $title = substr($title, 1);
        }
        $l = 2;
        $in = 'in';
        $this->adRef = $ref = " - ad {$this->detailAd[Classifieds::ID]}";
        if ($this->detailAd[Classifieds::RTL]) {
            $l = 1;
            $in = "في";
            $this->adRef = $ref = " - إعلان {$this->detailAd[Classifieds::ID]}";
        }
        $title = trim($title);
        if ($this->detailAd[Classifieds::PUBLICATION_ID] != 1 && $this->appendLocation)
            $title.=($this->detailAd[Classifieds::ROOT_ID] != 1 ? " " . $in : "") . " " . ($this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$l] != $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$l] ? $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$l] . " " : "") . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$l];
        return $title;
    }
	*/
	
	
    function buildTitle() {
        $title = '';
        if ($this->urlRouter->module == 'detail') {
            $title = $this->title;
            if ($this->detailAdExpired) {
                if (empty($this->detailAd)) {
                    header("HTTP/1.0 410 Gone");
                    $title = $this->title;
                } else {
                    $this->urlRouter->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                    //$title=$this->_getAdTitle();
                }
            } else {
                $this->urlRouter->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                //$title=$this->sphinx->BuildExcerpts(array($this->detailAd[Classifieds::CONTENT]), 'mouftah', '', array("limit" => 40,'chunk_separator'=>''));
                $title=$this->BuildExcerpts($this->detailAd[Classifieds::CONTENT],40,'');
                //$title=$title[0];
            }
            //$this->lang['description'].=$this->adRef;
        } else {
            $title = $this->title;
            if ($this->localityId && !preg_match('/'.$this->urlRouter->countries[$this->urlRouter->countryId]['name'].'/',  $this->title)) {
                $title.=' ' . $this->urlRouter->countries[$this->urlRouter->countryId]['name'];
            }
            if ($this->urlRouter->params['start'] > 1) {
                $title .= $this->lang['search_suffix'] . $this->urlRouter->params['start'];
            }
            if (!$this->extendedId && !$this->localityId && $this->urlRouter->naming != NULL && !empty($this->urlRouter->naming[2])) {
                if ($this->hasCities && $this->urlRouter->cityId)
                    $location = $this->lang['in'] . ' ' . $this->cityName . ' ' . $this->countryName;
                else
                    $location = $this->lang['in'] . ' ' . $this->countryName;
                
                
                
                if (strpos($this->urlRouter->naming[2], "%s")) {
                    $this->lang['description'] = sprintf($this->urlRouter->naming[2], $this->purposeName, $location);
                }else{
                    $this->lang['description'] = $this->urlRouter->naming[2];
                    $patterns       = array(
                        '/{p}/',
                        '/{l}/',
                        '/{d}/',
                        '/\s+/'
                    );
                    $replacements   = array(
                        $this->purposeName,
                        $location,
                        $this->urlRouter->count,
                        ' '
                    );
                    $this->lang['description'] = preg_replace($patterns, $replacements, $this->lang['description']);
                }
                //var_dump( $this->lang['description']);
                //die(var_export($this->urlRouter->naming));
                
                if ($this->urlRouter->params['start'] > 1) {
                    $this->lang['description'] .= $this->lang['search_suffix'] . $this->urlRouter->params['start'];
                }
            }
            else
                $this->lang['description'] = $this->lang['search_description'] . $title;
        }
        $this->title = $title;
    }


    function getAdCmpSection($ad, $rootId=0) {
        $section='';
        switch($ad['PURPOSE_ID']){
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex].' '.$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].' '.$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].'/', $this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex])){
                    $section=$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }else {
                    $in=' ';
                    if ($this->urlRouter->siteLanguage=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].$in.$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                break;
           }
           
           $adContent = json_decode($ad['CONTENT'], true);
           $countries = $this->urlRouter->db->getCountriesDictionary(); // $this->urlRouter->countries;
           if (isset($adContent['pubTo'])) {
                $fieldIndex=2;
                $comma=',';
                if ($this->urlRouter->siteLanguage=='ar'){
                    $fieldIndex=1;
                    $comma='،';
                }
                $countriesArray=array();
                $cities = $this->urlRouter->cities;
                
                $content='';
                foreach ($adContent['pubTo'] as $city => $value){
                    
                    if (isset($cities[$city]) && isset($cities[$city][4])) {
                        $country_id=$cities[$city][4];
                        
                        if (!isset($countriesArray[$cities[$city][4]])){
                            /*
                            $ccs=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$cities[$city][4]}_{$this->urlRouter->siteLanguage}",
                                    "select c.ID 
                                    from city c
                                    where c.country_id={$country_id} 
                                    and c.blocked=0
                                    order by NAME_". $this->urlRouter->siteLanguage,
                                    null, 0, $this->urlRouter->cfg['ttl_long']);
                             * 
                             */
                            
                            $ccs = $countries[$country_id][6];
                            if ($ccs && count($ccs)>0){
                                $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],array());
                            }else {
                                $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],false);
                            }
                        }
                        if ($countriesArray[$country_id][1]!==false) $countriesArray[$country_id][1][]=$cities[$city][$fieldIndex];
                    }
                }
                $i=0;
                foreach ($countriesArray as $key => $value) {
                    if ($i)$content.=' - ';
                    $content.=$value[0];
                    if ($value[1]!==false) $content.=' ('.implode ($comma, $value[1]).')';
                    $i++;
                }
                
                if ($content) {
                    $section=$section.' '.$this->lang['in'].' '.$content;
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,0,$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }
            }elseif(isset ($countries[$ad['COUNTRY_ID']])) {
                $countryId=$ad['COUNTRY_ID']; //$this->urlRouter->countries[$ad['COUNTRY_ID']][0];
                $countryCities=$countries[$countryId][6];/* $this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}",
                     "select c.ID from city c
                     where c.country_id={$countryId} and c.blocked=0",
                     null, 0, $this->urlRouter->cfg['ttl_long']);*/
                if (count($countryCities)>0 && isset($this->urlRouter->cities[$ad['CITY_ID']])){
                    $section=$section.' '.$this->lang['in'].' '.$this->urlRouter->cities[$ad['CITY_ID']][$this->fieldNameIndex].' '.$countries[$countryId][$this->fieldNameIndex];
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,$ad['CITY_ID'],$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }else {
                    $section=$section.' '.$this->lang['in'].' '.$countries[$countryId][$this->fieldNameIndex];
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,0,$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }
            }
        return $section;
    }

	/*
    function getAdTitle(&$ad) {
        $title = "";
        $l = "en";
        $nameIndex = 2;
        if ($ad[Classifieds::RTL]) {
            $l = "ar";
            $nameIndex = 1;
        }
        $params = array(
            "purpose_id" => $ad[Classifieds::PURPOSE_ID],
            "purpose_name" => $this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$nameIndex],
            "publication_id" => $ad[Classifieds::PUBLICATION_ID],
            "publication_name" => $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$nameIndex],
            "country_id" => $ad[Classifieds::COUNTRY_ID],
            "country_name" => $this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]]['name'],
            "city_id" => $ad[Classifieds::CITY_ID],
            "city_name" => $this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$nameIndex],
            "category_id" => $ad[Classifieds::CATEGORY_ID],
            "section_id" => $ad[Classifieds::SECTION_ID],
            "section_name" => $ad[Classifieds::SECTION_NAME_AR - 1 + $nameIndex],
            "root_id" => $ad[Classifieds::ROOT_ID],
            "root_name" => $this->urlRouter->roots[$ad[Classifieds::ROOT_ID]][$nameIndex],
            "body" => $ad[Classifieds::CONTENT],
            "lang" => $l,
        );
        $title = new WebTitle($params);
        $title = $title->get();
        if ($title == "") {
            $shorten = $this->shortenAd($ad[Classifieds::CONTENT], 60, false);
            if ($ad[Classifieds::SECTION_ID] != 63 && !mb_ereg_match($ad[Classifieds::SECTION_NAME_AR - 1 + $nameIndex], $shorten))
                $title = $ad[Classifieds::SECTION_NAME_AR - 1 + $nameIndex] . " - " . $shorten;
            else
                $title = $shorten;
        }
        //$this->classifieds->updateAdTitle($ad[Classifieds::ID], $title, $ad);
        return $ad[Classifieds::TITLE];
    }*/

}
?>