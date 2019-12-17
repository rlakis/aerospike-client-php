<?php
Config::instance()->incLayoutFile('Page');

use Core\Model\Classifieds;
use Core\Lib\SphinxQL;

class Search extends Page {    

    protected int $id=0;
    protected int $adCount=0;
    protected string $subTitle='';
    protected string $paginationString='';
    
    private int $tmpRootId=0;
    private int $tmpPurposeId=0;
    
    /*var $classifieds = null,*/
    public $breadString = '', $crumbTitle = '', $crumbString = '', $adRef = '', $dynamicTitle = '',
        $pageThumb = '', $partnerSection = '', $watchName = '', $formatNumbers=false, $mobileValidator=null, $phoneNumber=null;
    var $isRT = 0;
    
    
    function __construct() {
        header('Vary: User-Agent');
        parent::__construct(); 
        
        $this->tmpPurposeId=0;
        $this->tmpRootId=0;
        
        if (isset($_GET['rt'])) { $this->isRT = 1; }
        
        if (!$this->user()->isLoggedIn()) {
            if ($this->userFavorites || $this->router->watchId) {
                $this->router->config->disableAds();
            }
        }
        else if ($this->user()->isLoggedIn(9)) {
            $this->router->config->disableAds();
        }
        
            
       
        /*     
        if (!$this->isMobile && !$this->router->userId && !$this->userFavorites && !$this->router->watchId) {
            $this->hasLeadingPane=true;
            if ($this->user->info['id'] && $this->user->info['level']==9) {
                $this->isAdminSearch=isset($_SERVER['HTTP_REFERER']) && preg_match('/\/myads\/\?sub=pending$/', $_SERVER['HTTP_REFERER']) ? 1 : 0;
            }                        
        }              
        */
        if ($this->isMobile && ($this->router->watchId || $this->userFavorites)) {
            $this->router->config->disableAds();
        }
                
        $this->stat = array();
        if ($this->userFavorites) { $this->checkBlockedAccount(5); }
        if ($this->router->watchId) { $this->checkBlockedAccount(); }
        
        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        if (!isset($this->user->params['user_country'])) { $this->checkUserGeo(); }

        $isBot = preg_match('/googlebot/i',$_SERVER['HTTP_USER_AGENT']);
        if ($isBot) {
            if ($this->router->countryId) {
                $this->formatNumbers=strtoupper($this->router->countries[$this->router->countryId]['uri']);
            }
            elseif ($this->user->params['user_country']) {
                $this->formatNumbers=strtoupper($this->user->params['user_country']);                    
            }
        }
        else {
            if ($this->user->params['user_country']) {   
                $this->formatNumbers=strtoupper($this->user->params['user_country']);
            } elseif($this->router->countryId) {
                $this->formatNumbers=strtoupper($this->router->countries[$this->router->countryId]['uri']);
            }
        }
        
        
        if ($this->router->watchId !== null) {
            if ($this->router->watchId > 0) {
                $this->watchInfo = $this->user->getWatchInfo($this->router->watchId);
            }
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
            }
            else{
                $this->inlineCss.='.brd .rss{margin:9px 10px}';
            }
        } 
        elseif ($this->router->userId) {
            if (isset($_GET['preview'])) { $this->pagePreview = true; }
            $this->title = '';
            $this->router->config->disableAds();
            $this->partnerInfo = $this->user->getPartnerInfo($this->router->userId);
            if (!is_array($this->partnerInfo)) {
                $this->router->userId = 0;
                $this->user->redirectTo('/invalid/' . ($this->router->language == 'ar' ? '' : $this->router->language . '/'));
            }
            $this->set_require('css', 'profile');
            $descAr = isset($this->partnerInfo['descAr']) && $this->partnerInfo['descAr'] ? $this->partnerInfo['descAr'] : '';
            $descEn = isset($this->partnerInfo['descEn']) && $this->partnerInfo['descEn'] ? $this->partnerInfo['descEn'] : '';

            if ($this->router->sectionId) {
                switch ($this->router->purposeId) {
                    case 1:
                    case 2:
                    case 8:
                    case 999:
                        $this->partnerSection = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex] . ' ' . $this->router->purposes[$this->router->purposeId][$this->fieldNameIndex];
                        break;
                    case 6:
                    case 7:
                        $this->partnerSection = $this->router->purposes[$this->router->purposeId][$this->fieldNameIndex] . ' ' . $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
                        break;
                    case 3:
                        if ($this->router->language == 'ar') {
                            $this->partnerSection = 'وظائف ' . $sectionName = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
                        } else {
                            $this->partnerSection = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex] . ' ' . $this->router->purposes[$this->router->purposeId][$this->fieldNameIndex];
                        }
                        break;
                    case 4:
                        $in = ' ';
                        if ($this->router->language == "en")
                            $in = ' ' . $this->lang['in'] . ' ';
                        $this->partnerSection = $this->router->purposes[$this->router->purposeId][$this->fieldNameIndex] . $in . $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
                        break;
                    case 0;
                    case 5:
                        $this->partnerSection = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
                        break;
                    default:
                        break;
                }
            }
            
            $this->forceNoIndex = true;
            if ($this->router->language == 'ar') {
                if (isset($this->partnerInfo['t']['ar']) && $this->partnerInfo['t']['ar']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['ar'];
                    $this->forceNoIndex = false;
                    $this->title = $this->partnerInfo['title'];
                } elseif (isset($this->partnerInfo['t']['en']) && $this->partnerInfo['t']['en']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['en'];
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
                    $this->title = $this->partnerInfo['title'];
                } elseif (isset($this->partnerInfo['t']['ar']) && $this->partnerInfo['t']['ar']) {
                    $this->partnerInfo['title'] = $this->partnerInfo['t']['ar'];
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
            if (is_numeric($this->partnerInfo['uri']) || $this->router->module == 'detail')
                $this->forceNoIndex = true;
            if ($this->router->userId == $this->user->info['id'] ||
                    $this->lang['description'] ||
                    (isset($this->partnerInfo['a']['ar']) && $this->partnerInfo['a']['ar']) ||
                    (isset($this->partnerInfo['a']['en']) && $this->partnerInfo['a']['en']) ||
                    (isset($this->partnerInfo['email']) && $this->partnerInfo['email']) ||
                    (isset($this->partnerInfo['contact']) && !empty($this->partnerInfo['contact']))
            ) {
                $this->hasPartnerInfo = true;
            }
        }
        /*
        if ($this->isMobile) {            
            $this->inlineCss.='.yad{text-align:center;display:block}.lbad{text-align:center;overflow:visible!important;background-color:transparent!important;border:0!important}.lbad.responsive{margin:-5px auto 5px}';      
            
            $this->num = 5;
            if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && strstr($_SERVER['HTTP_USER_AGENT'], 'iPad;')) {
                $this->num = 10;
            }
        }
        else {            
            $this->globalScript.='var sic=[];';
            $this->set_ad(array('zone_6' => array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0-' . $this->router->config->serverId),
                        'zone_10'=>array('/1006833/HalfPage', 300, 600, 'div-gpt-ad-1351783135410-0-'.$this->router->config->serverId),
                        'zone_11'=>array('/1006833/HalfPage2', 300, 600, 'div-gpt-ad-1505307438459-0'.$this->router->config->serverId)                        
                        ));
        }
        if ($this->router->sectionId==10) { $this->num = $this->num * 2; }
        */
       
        if ($this->router->rootId && isset($this->router->roots[$this->router->rootId]))
            $this->rootName = $this->router->roots[$this->router->rootId][$this->fieldNameIndex];
        else
            $this->router->rootId = 0;

        if ($this->router->sectionId && isset($this->router->sections[$this->router->sectionId]))
            $this->sectionName = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
        else
            $this->router->sectionId = 0;
        if ($this->router->purposeId && isset($this->router->purposes[$this->router->purposeId]))
            $this->purposeName = $this->router->purposes[$this->router->purposeId][$this->fieldNameIndex];

        if (!$this->router->userId && !$this->router->watchId && !$this->userFavorites) {
            if ($this->router->rootId == 1 && $this->router->sectionId &&
                    ($this->router->countryId == 1 ||
                    $this->router->countryId == 2 || 
                    $this->router->countryId == 3 || 
                    $this->router->countryId == 4 ||
                    $this->router->countryId == 7 ||
                    $this->router->countryId == 9)
            ) {
                $this->localities = $this->router->db->getLocalitiesData($this->router->countryId, $this->router->sectionId, NULL, $this->router->language);                
                if ($this->router->cityId) {
                    foreach ($this->localities as $loc) {
                        if ($loc['city_id'] == $this->router->cityId) {
                            $this->forceNoIndex = true;
                            break;
                        }
                    }
                }

                if (isset($this->router->params['loc_id']))
                    $this->localityId = $this->router->params['loc_id']+0;
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
                
            }
            elseif ($this->router->sectionId) {
                $this->extended = $this->router->db->getSectionTagsData($this->router->countryId, $this->router->cityId, $this->router->sectionId, $this->router->language);
            }
        }
        
        if ($this->router->module == 'search' &&
                (($this->router->purposeId && !$this->router->rootId && !$this->router->sectionId) ||
                ($this->router->rootId == 99 && !$this->router->sectionId))) {
            $this->forceNoIndex = true;
        }
        
        if (!$this->router->userId && !$this->router->watchId) {
            $this->buildTitle();
        }
        
        if(!$this->router->isPriceList){
            $this->num = 18;
            $this->execute(true);           
            if ($this->pageUserId && !$this->searchResults['body']['total_found']) {
                $this->router->config->disableAds();
                $this->router->cfg['enabled_sharing']=0;
            }
        }
        
        if (in_array($this->router->sectionId, $this->router->config->get('restricted_section_ads'))) {
            $this->router->config->disableAds();
        }
        
        if ($this->user->info['id'] && $this->user->info['level']==9) {
            $this->inlineCss.='.ls li{height:auto !important;';
        }
        
        $this->render();
    } // end of constructor

        
    function classifieds() : Classifieds {
        return $this->classifieds;
    }
    
    
    function renderSidePage() {
        $isOwner = $this->router->userId == $this->user->info['id'] && $this->router->module != 'detail' && !$this->pagePreview;
        $isOwner = 0;
        $editLink = '/page/' . ($this->router->language == 'ar' ? '' : 'en/');
        $descAr = isset($this->partnerInfo['descAr']) && $this->partnerInfo['descAr'] ? $this->partnerInfo['descAr'] : '';
        $descEn = isset($this->partnerInfo['descEn']) && $this->partnerInfo['descEn'] ? $this->partnerInfo['descEn'] : '';
        $website = isset($this->partnerInfo['url']) && $this->partnerInfo['url'] ? $this->partnerInfo['url'] : '';

        ?><ul class='list'><?php
        if ($descAr || $descEn || $isOwner) {
            /* ?><h4><?= $this->lang['aboutUs'] ?></h4><?php */
                if ($descAr || $descEn) {
                    ?><li <?= 'class="' . ($this->router->language == 'ar' ? ($descAr ? 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr : 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn) : ($descEn ? 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn : 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr) ) . '</p>' . ($isOwner ? '<p class="pk ' . $this->router->language . '"><a href="' . $editLink . '?edit=desc#desc"><span class="ek"></span>' . $this->lang['addPageDescE'] . '</a></p>' : '') ?></li><?php
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
        $numbers = isset($this->partnerInfo['contact']) && !empty($this->partnerInfo['contact']) ? $this->partnerInfo['contact'] : false;
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
                (isset($this->partnerInfo['loc']['lon']) && $this->partnerInfo['loc']['lon'])) && $this->router->module != 'detail';
        if ($descAr || $descEn || $isOwner || $hasMap) {
            ?><li class="h"><?= $this->lang['address'] ?></li><?php
                if ($descAr || $descEn) {
                    if ($descAr || $descEn) {
                        ?><li <?= 'class="f ' . ($this->router->language == 'ar' ? ($descAr ? 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr : 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn) : ($descEn ? 'en"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descEn : 'ar"><p' . ($isOwner ? ' class="pb"' : '') . '>' . $descAr) ) . '</p>'
                        ?><?= $isOwner ? '<p class="pk ' . $this->router->language . '"><a href="' . $editLink . '?edit=address#address"><span class="ek"></span>' . $this->lang['addPageAddrE'] . '</a></p>' : '' ?></li><?php
                        }
                } elseif ($isOwner) {
                    ?><li class="f"><?= '<p class="pb">' . $this->lang['addPageAddr'] . '</p><p class="pk"><a href="' . $editLink . '?edit=address#address"><span class="ek"></span>' . $this->lang['addPageAddrE'] . '</a></p>' ?></li><?php
            }
            if (0 && $hasMap) {
                ?><div class="mpa" id="mapo"></div><?php
                    $this->inlineScript.='var script = document.createElement("script");script.type = "text/javascript";script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initializeMap&language=' . $this->router->language . '";document.body.appendChild(script);';
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
                                        $socialString.="<a target='blank' href='http://www.facebook.com/".$value."'><span class='fb-link'></span> Facebook</a><br />";
                                        break;
                                    case 'tw':
                                        $socialString.="<a target='blank' href='http://twitter.com/".$value."'><span class='tw-link'></span> Twitter</a><br />";
                                        break;
                                    case 'gp':
                                        $socialString.="<a target='blank' href='http://plus.google.com/".$value."'><span class='gp-link'></span> Google Plus</a><br />";
                                        break;
                                    case 'lk':
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
        if ($this->router->userId) {
            //$this->renderSidePage();
        } elseif ($this->router->watchId && !$this->user->info['id']) {
            $this->renderSideSite();
        } else {
            /*if ($this->router->module == 'search' && !$this->userFavorites && !$this->router->watchId) {
                    if (($this->router->countryId && $this->router->sectionId && $this->router->purposeId) || ($this->router->params['q'] && $this->searchResults['total_found'] < 100)) {
                        if ($this->user->info['id']) {
                            $key = $this->router->countryId . '-' . $this->router->cityId . '-' . $this->router->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->router->purposeId . '-' . crc32($this->router->params['q']);
                            if (isset($this->user->info['options']['watch'][$key])) {
                                ?><div onclick="document.location = '/watchlist<?= ($this->router->language == 'ar' ? '' : '/en') ?>/'" class="eck rck"><?= $this->lang['nowWatching'] . '<b>' . $this->title . '</b>' . $this->lang['goWatch'] ?></div><?php
                            } else {
                                ?><div onclick="ti()" class="eck"><?= $this->lang['watchLink'] . '<b>' . $this->title . '</b>' ?></div><?php
                            }
                        } else {
                            ?><div onclick="ti()" class="eck"><?= $this->lang['watchLink'] . '<b>' . $this->title . '</b>' ?></div><?php
                        }
                    }
                }*/
                //$this->renderSideUserPanel();
                /*if ($this->router->module != 'detail' && !$this->router->watchId && $this->router->params['start'] > 1)
                    echo $this->fill_ad('zone_3', 'ad_s ad_ts');
                
                */
                
                if ($this->router->module != 'detail'){
                    $this->renderSearchSettings();
                }
                if (!$this->router->watchId) {
                    ?> <!--googleoff: index --> <?php 
                    $this->renderExtendedLinks();
                    $this->renderLocalityLinks();
                    $this->renderSideRoots();
                    ?> <!--googleon: index --> <?php 
                    //$this->renderSideCountries();
                }
                /*
                if (!$this->router->watchId && ($this->router->module == 'detail' || $this->router->params['start'] < 2))
                    echo $this->fill_ad('zone_3', 'ad_s');
*/
                /*if ($this->router->module == 'search' && ($this->router->countryId || $this->userFavorites || $this->router->watchId))
                    $this->renderSideLike();*/
            }
    }

        
    function main_pane() {
        if ($this->router->isPriceList) {
            $this->priceResults();
        }
        elseif ($this->userFavorites && !$this->user->info['id'] && (!$this->pageUserId || ($this->pageUserId && !$this->searchResults['body']['total_found']))) {
            $this->lang['hint_login'] = $this->lang['hint_login_favorites'];
            $this->renderLoginPage();
        } 
        elseif ($this->router->watchId && !$this->user->info['id'] && (!$this->pageUserId || ($this->pageUserId && !$this->searchResults['body']['total_found']))) {
            $this->lang['hint_login'] = $this->lang['hint_login_watch'];
            $this->renderLoginPage();
        } 
        else {                
            $this->results();                
        }
    }

        
       
    function weightedRand($weights, $weight_sum = 100) {
        $r = rand(1,$weight_sum);
        $n = count($weights);
        $i = 0;
        while ($r > 0 && $i < $n) {
            $r -= $weights[$i];
            $i++;
        }
        return $i - 1;
    }
        
   
    function renderMobileSublist(){           
        if ($this->router->module=='search' && !$this->userFavorites && !$this->router->watchId && !$this->router->userId){

            $hasResults = $this->searchResults['body']['total_found'] > 0 && isset($this->searchResults['body']['matches']) && !empty($this->searchResults['body']['matches']);
            if (!$this->router->purposeId && $hasResults)
                $this->filterPurposesMobile();

            if ($this->router->rootId==1 && $this->router->countryId && ($this->searchResults['body']['total_found']>20 || $this->localityId) && !empty($this->localities)) {
                if ($this->searchResults['body']['total_found']>5 || $this->localityId) {
                    $this->renderMobileLocalityLinks();
                }

            }
            else if ($this->router->sectionId && ($this->searchResults['body']['total_found']>5 || $this->extendedId) && !empty($this->extended)) {
                $prefix_uri = '/';
                if ($this->router->countryId) {
                    $prefix_uri.=$this->router->countries[$this->router->countryId]['uri'] . '/';
                    $keyIndex = 2;
                }
                else {
                    $keyIndex = 1;
                }


                $suffix_uri = '/';
                $prefix = '';
                $suffix = '';

               if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                    $keyIndex++;
                    $prefix_uri.=$this->router->cities[$this->router->cityId][3] . '/';
                }

                $sectionName = $this->sectionName;
                if ($this->router->purposeId) {
                    $suffix_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                    switch ($this->router->purposeId) {
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
                if ($this->router->language != 'ar') {
                    $suffix_uri.=$this->router->language . '/';
                }
                
                include_once $this->router->cfg['dir'] . '/core/lib/SphinxQL.php';
 
                $sphinx = new SphinxQL($this->router->cfg['sphinxql'], $this->router->cfg['search_index']);
                if ($this->router->purposeId) {                                        
                    $q = "select groupby(), count(*) from {$this->router->cfg['search_index']} where hold=0 and canonical_id=0 "
                    . "and section_id={$this->router->sectionId} "
                    . "and purpose_id={$this->router->purposeId} ";
                    if ($this->router->countryId) $q.="and country={$this->router->countryId} ";
                    if ($this->router->cityId) $q.="and city={$this->router->cityId} ";
                    $q.=" group by section_tag_id limit 1000";
                }else{                    
                    $q = "select groupby(), count(*), group_concat(purpose_id) from {$this->router->cfg['search_index']} where hold=0 and canonical_id=0 and section_id={$this->router->sectionId} ";
                    if ($this->router->countryId) $q.="and country={$this->router->countryId} ";
                    if ($this->router->cityId) $q.="and city={$this->router->cityId} ";
                    $q.=" group by section_tag_id limit 1000";
                }
                $query = $sphinx->search($q);
                if (isset($query['matches']) && count($query['matches'])) {
                    $tags=[];
                    $query = $query['matches'];
                    $count__=0;
                    foreach ($query as $tag) {
                        if ($this->router->purposeId) {
                            $pus=$this->router->purposeId;
                            $count__=1;
                        }
                        else {
                            $pus=[];
                            $pul = explode(",",$tag["group_concat(purpose_id)"]);
                            foreach($pul as $puid){
                                $pus[$puid+0]=1;
                                $count__++;
                            }
                        }
                        $tags[$tag["groupby()"]+0]=[$tag['count(*)'], $count__];
                    }
                }
                
                ?> <!--googleoff: index --> <?php
                ?><span onclick="subList(this)" class="rbt subit ic"></span><?php
                ?><div id="sublist"><?php
                    ?><h2 class="ctr"><?= $this->lang['suggestion'] ?></h2><?php
                    ?><ul class="ls"><?php 
                    if ($this->extendedId) { 
                        ?><li class="bbr"><a href="<?= $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) ?>"><?= $sectionName ?><span class="to"></span><span class="n"><?= $this->router->pageSections[$this->router->sectionId]['counter'] ?></span></a></li><?php 
                    } 
                    else {
                        ?><li class="on bbr"><b><?= $sectionName ?><span class="n"><?= $this->router->pageSections[$this->router->sectionId]['counter'] ?></span></b></li><?php
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
                            ?><li><a href="<?= $prefix_uri . $this->router->sections[$this->router->sectionId][3] . '-' . $sub['uri'] . $suffix_uri . $append ?>"><?= $prefix . $sub['name'] . $suffix ?><span class="to"></span><?php
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
        if ($this->router->language != 'ar')
            $lang = $this->router->language . '/';
        ?><ul class="bwz bwz<?= $this->router->language ?>"><?php
        ?><li onclick="document.location = '/post/<?= $lang ?>';" class="poa"><?= $this->lang['postFree'] ?></li><?php
        ?><li onclick="document.location = '/<?= $lang ?>';" class="boa"><?= $this->lang['browseAd'] ?></li><?php
        ?></ul><?php
    }
    
   
    function footerMobile(){
        $this->renderMobileSublist();
        parent::footerMobile();
    }

    
    function processTextNumbers(&$text,$pubId=0,$countryCode=0,&$matches=array()){
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        if(!($content && count($content))){
            $tmpTxt=preg_replace('/\<.*?>/', '', $text);
            preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt, $content);
        }
        
        if ($content && count($content)) {
            $str=$content[1];
            if ($str) {
                if ($this->formatNumbers) {
                    $nums=array();
                    $numInst=array();
                    $numbers = null;
                    preg_match_all($phone, $str, $numbers);
                
                    if ($numbers && count($numbers[1])) {
                        foreach ($numbers[1] as $match) {
                            $number = $match;
                            try {
                                if ($pubId==1){
                                    $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                                }
                                else{
                                    $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                                }
                                if ($num && $this->mobileValidator->isValidNumber($num)) {
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if ($rCode==$this->formatNumbers) {
                                        $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                    }
                                    else {
                                        $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                    }
                                    $nums[]=array($number, $num);
                                }
                                else {
                                    $hasCCode = preg_match('/^\+/', $number);
                                    switch ($countryCode) {
                                        case 'SA':                                    
                                            if ($hasCCode) {
                                                $num = substr($number,4);
                                            }
                                            else{
                                                $num = $number;
                                            }
                                            if (strlen($num)==7) {
                                                switch ($pubId) {
                                                    case 9:
                                                        $num='011'.$num;
                                                        break;
                                                    case 12:
                                                    case 18:
                                                        $tmp='013'.$num;
                                                        $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                        if($tmp && $this->mobileValidator->isValidNumber($tmp)){
                                                            $num='013'.$num;
                                                        }
                                                        else {
                                                            $num='011'.$num;
                                                        }
                                                    break;
                                                }
                                            }
                                            break;
                                        case 'EG':
                                            if ($hasCCode) {
                                                $num = substr($number,3);
                                            }
                                            else {
                                                $num = $number;
                                            }
                                            if (strlen($num)==7) {
                                                switch ($pubId) {
                                                    case 13:
                                                        $num='2'.$num;
                                                        break;
                                                    case 14:
                                                        $num='3'.$num;
                                                        break;
                                                }
                                            }
                                            elseif (strlen($num)==8) {
                                                switch ($pubId) {
                                                    case 13:
                                                        $num='2'.$num;
                                                        break;
                                                }
                                            }
                                            break;
                                    }
                                    
                                    if ($num!=$number) {
                                        $num = $this->mobileValidator->parse($num, $countryCode);
                                        if ($num && $this->mobileValidator->isValidNumber($num)) {
                                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                            if ($rCode==$this->formatNumbers) {
                                                $num=$this->mobileValidator->formatInOriginalFormat($num,$this->formatNumbers );
                                            }
                                            else {
                                                $num=$this->mobileValidator->formatOutOfCountryCallingNumber($num,$this->formatNumbers);
                                            }
                                            $nums[]=array($number, $num);
                                        }
                                        else {
                                            $nums[]=array($number, $number);
                                        }
                                    }
                                    else {
                                        $nums[]=array($number, $number);
                                    }                            
                                }
                            }
                            catch(Exception $ex) {
                                $nums[]=array($number, $number);
                            }
                        }
                               
                        if (preg_match('/\<span class/',$text)) {
                            foreach ($nums as $num) {
                                if ($num[0]!=$num[1]) {
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $text = preg_replace('/'.$num[0].'/', $num[1], $text);
                                    $matches[]=$num[1];
                                }
                                else {
                                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                                    $text = preg_replace('/\<span class="pn(?:[a-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$num[1].'</span>', $text);
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
        } 
        else {
            $formatNumber = $this->mobileValidator->formatOutOfCountryCallingNumber($phoneNumber, $this->formatNumbers);
        }
        return $formatNumber;
    }
    

    function replacePhonetNumbers(&$text, $countryCode=0, $mobiles, $phones, $undefined, $email=null, &$matches=''){
        $REGEX_MATCH='/((?:(?:[ .,;:\-\/،])(?:mobile|viber|whatsapp|phone|fax|telefax|للتواصل|جوال|موبايل|واتساب|للاستفسار|للأستفسار|للإستفسار|فايبر|هاتف|فاكس|تلفاكس|الاتصال|للتواصل|للمفاهمة|للاتصال|الاتصال على|اتصال|(?:(?:tel|call|ت|ه|ج)(?:\s|):))(?:(?:\s|):|\+|\/|)(?: |$)))/ui';
        $REGEX_CATCH='/(?:(?:(?:[ .,;:\-\/،])(?:mobile|viber|whatsapp|phone|fax|telefax|للتواصل|جوال|موبايل|واتساب|للاستفسار|للأستفسار|للإستفسار|فايبر|هاتف|فاكس|تلفاكس|الاتصال|للتواصل|للمفاهمة|للاتصال|الاتصال على|اتصال|(?:(?:tel|call|ت|ه|ج)(?:\s|):))(?:(?:\s|):|\+|\/|)(?: |$)))(.*)/ui';
        
        if (!isset($this->formatNumbers) || empty($this->formatNumbers)){
            return;
        }
        $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
        if (preg_match('/\<span class/', $text)) {
            foreach ($mobiles as $num) {
                $number = $this->formatPhoneNumber($num[0]);
                if ($num[0]!=$number) {
                    $org = $num[0];
                    $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/'.$num[0].'/', $number, $text);
                    $matches .= '<a class="bt" href=\'javascript:void(0);\' onclick=\'callNum("'.$org.'","'.$number.'")\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                } else {
                    $org = $num[0];
                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'javascript:void(0);\' onclick=\'callNum("'.$org.'","'.$number.'");\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                }
            }
            foreach ($phones as $num) {
                $number = $this->formatPhoneNumber($num[0]);
                 if ($num[0]!=$number) {
                     $org = $num[0];
                    $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/'.$num[0].'/', $number, $text);
                    $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                } 
                else {
                    $org = $num[0];
                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                }
            }
        } 
        else {
            foreach ($mobiles as $num) {
                $number = $this->formatPhoneNumber($num[1]);
                if ($num[0]!=$number) {
                     $org = $num[0];
                    $num[0]= preg_replace('/\+/','\\+' , $num[0]);                        
                    $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'javascript:void(0);\' onclick=\'callNum("'.$org.'","'.$number.'");\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                } 
                else {
                    $org = $num[0];
                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'javascript:void(0);\' onclick=\'callNum("'.$org.'","'.$number.'");\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                }
            }
            foreach ($phones as $num) {
                $number = $this->formatPhoneNumber($num[1]);
                 if ($num[0]!=$number) {
                    $org = $num[0];
                    $num[0]= preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                } 
                else {
                    $org = $num[0];
                    $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                    $text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$number.'</span>', $text);
                    $matches .= '<a class="bt" href=\'tel:'.$org.'\'><span class="k call"></span> <span class="pn">'.$number.'</span></a>';
                }
            }
        }
        return $text;                                                                        
    }
    
    
    function renderMobileFeature(){       
        if (!isset($this->searchResults['zone2']) || $this->searchResults['zone2']['total_found']==0) {
            return;
        }

        $ad_count = count($this->searchResults['zone2']['matches']);
        $ad_cache = $this->router->db->getCache()->getMulti($this->searchResults['zone2']['matches']);
        if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = []; }
        
        for ($ptr = 0; $ptr < $ad_count; $ptr++) {
            $id = $this->searchResults['zone2']['matches'][$ptr];
            $ad = $this->classifieds->getById($id,false,$ad_cache);
            if (isset($ad[Classifieds::ID]) && ( $ad[Classifieds::USER_ID]!=220906 || $ad[Classifieds::ID]==6889499 || ($ptr == $ad_count-1) )) {
                $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);

                if (!empty($ad[Classifieds::ALT_CONTENT])) {
                    if ($this->router->language == "en" && $ad[Classifieds::RTL]) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 0;
                        $this->appendLocation = false;
                    } 
                    elseif ($this->router->language == "ar" && $ad[Classifieds::RTL] == 0) {
                        $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 1;
                        $this->appendLocation = false;
                    }
                }

                $pic = '';
                $textPlacement = 0;//no picture
                if ($ad[Classifieds::PICTURES] && is_array($ad[Classifieds::PICTURES])  && count($ad[Classifieds::PICTURES])) {
                    $z=0;
                    $bestPicIdx=0;
                    $bestWidth=320;
                    $width = 0;
                    $height = 0;
                    $textPlacement = 1;//1 over//2 side
                    foreach ($ad[Classifieds::PICTURES] as $pic) {
                        if (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) {
                            if ($ad[Classifieds::PICTURES_DIM][$z][0] > $this->user->params['screen'][0]) {
                                $bestWidth = $this->user->params['screen'][0];
                            }
                            if ($ad[Classifieds::PICTURES_DIM][$z][0] >= $bestWidth) {
                                $width = $bestWidth;
                                $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                            }
                            else {
                                $width = $bestWidth / 2;
                                $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                            }
                            if ($height > 300) {
                                $textPlacement = 2;
                                $height = 300;
                                $width = floor( $height*$ad[Classifieds::PICTURES_DIM][$z][0]/$ad[Classifieds::PICTURES_DIM][$z][1]);
                            }
                            if ($width > $this->user->params['screen'][0]*0.66) {
                                $textPlacement = 1;
                            }
                            if ($width < $this->user->params['screen'][0]/2) {  
                                $textPlacement = 2;
                            }
                        }
                        else {
                            if ($ad[Classifieds::PICTURES_DIM][$z][0] >= $bestWidth) {
                                $width = $bestWidth;
                                $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                            }
                            else {
                                $width = $bestWidth / 2;
                                $height = floor( $width*$ad[Classifieds::PICTURES_DIM][$z][1]/$ad[Classifieds::PICTURES_DIM][$z][0]);
                            }
                            if ($height > 300) {
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
                    if ($this->router->isAcceptWebP) {
                        $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                    }
                    $pic = '<img style="width:'.$width.'px;height:'.$height.'px" src="' . $this->router->cfg['url_ad_img'] . '/repos/m/' . $pic . '" />';
                } 
                else {
                    $pic = '<img class="d" src="' . $this->router->cfg['url_img'] . '/90/' . $ad[Classifieds::SECTION_ID] . $this->router->_png . '" />';
                }
                $textClass = ($ad[Classifieds::RTL]) ? "ar" : "en";                

                $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->router->language == "ar" ? "" : "{$this->router->language}/"), $ad[Classifieds::ID]).'?ref=mediaside';

                ?><div id="<?= $ad[Classifieds::ID] ?>" onclick="wo('<?= $_link ?>')" class="prem"><?php
                if ($textPlacement==0) {         
                    ?><div class="hdr pdf"><?php 
                    echo $this->getFeatureAdSection($ad);
                    ?><div><span class="ic r102"></span><?= $this->lang['premium_ad_dt'] ?></div><?php
                    ?></div><?php  
                    ?><p class="<?=  $textClass ?> pdf"><?php 
                        echo $pic . $ad[Classifieds::CONTENT];
                    ?></p><?php 
                }
                elseif ($textPlacement==1) {        
                    ?><div class="hdr"><?php 
                    echo $this->getFeatureAdSection($ad);
                    ?><div><span class="ic r102"></span><?= $this->lang['premium_ad_dt'] ?></div><?php
                    ?></div><?php            
                    echo $pic; 
                    ?><p class="btm <?=  $textClass ?>"><?php 
                        echo $ad[Classifieds::CONTENT];
                    ?></p><?php 
                }
                else {                 
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
        if ((isset($this->user->info['level']) && $this->user->info['level']==9) || !isset($this->searchResults['zone2']) || $this->searchResults['zone2']['total_found']==0) {
            return;
        }
               
        $ad_count = count($this->searchResults['zone2']['matches']);
        $ad_cache = $this->router->db->getCache()->getMulti($this->searchResults['zone2']['matches']);
        if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = array(); }
        
        for ($ptr=0; $ptr<$ad_count; $ptr++) {
            $id = $this->searchResults['zone2']['matches'][$ptr];
            $ad = $this->classifieds->getById($id,false,$ad_cache);
            if (isset($ad[Classifieds::ID]) && ( $ad[Classifieds::USER_ID]!=220906 || $ad[Classifieds::ID]==6889499 || ($ptr==$ad_count-1) )) {
                $this->replacePhonetNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::COUNTRY_CODE], $ad[Classifieds::TELEPHONES][0], $ad[Classifieds::TELEPHONES][1], $ad[Classifieds::TELEPHONES][2],$ad[Classifieds::EMAILS]);

                if (isset($this->user->info['level'])) {
                    if (!($this->user->info['level']==9 || $this->user->info['id']==$ad[Classifieds::USER_ID])) {
                        $this->stat['ad-imp'][]=$id;
                    }
                }
                else {
                    $this->stat['ad-imp'][]=$id;
                }
                
                if (!empty($ad[Classifieds::ALT_CONTENT])) {
                    if (!$this->router->isArabic() && $ad[Classifieds::RTL]) {
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 0;
                        $this->appendLocation = false;
                    } 
                    elseif ($this->router->isArabic() && $ad[Classifieds::RTL]==0) {
                        $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                        $ad[Classifieds::RTL] = 1;
                        $this->appendLocation = false;
                    }
                }

                if (isset($ad[Classifieds::PICTURES]) && $ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                    $rand = rand(0, count($ad[Classifieds::PICTURES])-1);
                    $hasAnimation = 0;
                    if ($ad[Classifieds::PICTURES_DIM][$rand][0]>$ad[Classifieds::PICTURES_DIM][$rand][1]) {
                        $hasAnimation = 1;
                    }
                    $pic = $ad[Classifieds::PICTURES][$rand];
                    if($this->router->isAcceptWebP){
                        $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                    }
                    $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img height=\"98\" src=\"'.$this->router->config->adImgURL.'/repos/d/' . $pic . '\" />";';
                    //$pic = '<span class="ig"></span>';
                    $pic = '<div class=card-image>'."\n".'<img src="'.
                            $this->router->config->adImgURL.'/repos/m/' . $pic . '" />'. "\n".
                            '<div class="ripple-container"></div>'. "\n" .'</div>'."\n";
                } 
                else {
                    $this->globalScript.='sic[' . $ad[Classifieds::ID] . ']="<img height=\"90\" class=\"ir\" src=\"'.$this->router->config->imgURL.'/200/' . $ad[Classifieds::SECTION_ID] . $this->router->_png . '\" />";';
                    $pic = '<span class="ig igr"></span>';
                }
                $textClass = ($ad[Classifieds::RTL]) ? "ar" : "en";                

                $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->router->isArabic() ? '' : "{$this->router->language}/"), $ad[Classifieds::ID]).'?ref=mediaside';
                echo '<li onclick="wo(\'', $_link, '\')" id=sideFtr class="lsf">', "\n";
                echo '<div id="sf', $ad[Classifieds::ID], '" class="card card-product drg"> <!--googleoff: index --> ',"\n";
                echo $pic;
                echo '<div class=card-content>', "\n";
                echo '<div class="adc block-with-text card-description ', $textClass, '" ';
                if ($this->router->id!=$ad[Classifieds::ID]) {
                    echo ' onclick="wo(\'', $_link, '\')" ';
                }
                echo '>', "\n";
                echo $ad[Classifieds::CONTENT], '</div>', "\n";
                //echo '<p class="ani ', $textClass, '">';                                       
                
                //echo $ad[Classifieds::CONTENT];
                echo '<span class=crd><span class="vpdi ', $this->router->language, '"></span>', $this->lang['premium_ad'], '</span></p>', "\n";
                echo '<!--googleon: index --> </div></li>', "\n";
                
                break;
            }
        }
    }
    

    function mergeResults(&$topFeatureCount, &$ad_keys) {
        $featured_ids = [];
        if (isset($this->searchResults['zone1'])) {
            $ad_keys = $this->searchResults['zone1']['matches'];
            $featured_ids = $this->searchResults['zone1']['matches'];
        }
                        
        $topFeatureCount = count($featured_ids);
        foreach ($this->searchResults['body']['matches'] as $ad_id) {
            if (!in_array($ad_id, $featured_ids)) {
                $ad_keys[] = $ad_id;
            }
        }
        
        $max = $this->num+2;
        if ($this->searchResults['body']['total_found']>$max && count($ad_keys)<$max && isset($this->searchResults['zone0'])) {
            //error_log(var_export($this->searchResults['zone0'], true));
            $count=count($this->searchResults['zone0']['matches']);
            $fc=count($ad_keys);
            for ($i=0; $i<$count && $fc+$i<$max; $i++) {
                $ad_keys[] = $this->searchResults['zone0']['matches'][$i];
                $this->user()->params['feature'][] = $this->searchResults['zone0']['matches'][$i];
            }
        }
    }
    
    
    private function adSlot() : void {
        if ($this->router->config->enabledAds()) {
            //<div class="card card-product">
            //echo '<div class="adslot">';
            ?>
<ins class="adsbygoogle" data-ad-client="ca-pub-2427907534283641" data-ad-slot="7030570808" data-ad-format="auto" data-full-width-responsive="true"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script><?php
            //echo '</div>', "\n";//</div>
        }
    }
    
    
    function renderDResults($keywords) : void {
        $ad_keys = [];
        $topFeatureCount = 0;
        if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = []; }        
        if (!isset($this->user->params['feature'])) { $this->user->params['feature']=[]; }
        $this->mergeResults($topFeatureCount, $ad_keys);
        $ad_cache = $this->router->db->getCache()->getMulti($ad_keys);
        $ad_count = count($ad_keys);
        $end_user=true;
        if ($ad_count) {
            $cmp = filter_input(INPUT_GET, 'cmp', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
            $aid = filter_input(INPUT_GET, 'aid', FILTER_VALIDATE_INT, ['options'=>['default'=>0]]);
            if ($cmp>0 || $aid>0) {
                $end_user=false;
                $aid = $cmp>0 ? $cmp : $aid;
                $ad = $this->user()->getPendingAds($aid);
                if (!empty($ad)) {
                    
                    $ad=$ad[0];
                    $content=json_decode($ad['CONTENT'], true);
                    $clang = $content['rtl'] ? 'ar' : 'en';
                    ?><div class=compare id="<?= $ad['ID'] ?>" style="height:auto;background-image:none;background-color:#FFF;width:320px;position:fixed;top:0;left:0;z-index:100000;border:4px solid #000"><?php
                    ?><p class="<?= $clang ?>" style="height:auto;padding:4px;line-height: 1.5em;"><?= $content['other'] ?></p><?php 
                    if (isset($content['altother']) && $content['altother']!='') {
                        $clang = $content['altRtl'] ? 'ar' : 'en';
                        ?><p class="<?= $clang ?>" style="height:auto;margin-top:5px;border-top:1px solid #999;padding:5px;"><?= $content['altother'] ?></p><?php 
                    }
                    ?><div class=tbs style="margin-top:5px;padding:0 5px;border-top:1px solid #ccc;line-height:30px;height:30px;background-color:#bdc9dc;overflow:hidden;color:#333;"><?= $this->getAdCmpSection($ad) ?></div><?php
                    $count=$this->searchResults['body']['total_found'];
                    
                    ?><div style="margin:5px"><?= $this->formatPlural($count, 'ad');?></div><?php
                    
                    $strip = $_GET['strip'] ?? 0;
                    $link = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
                    //background-image:none;padding-bottom:10px;background:transparent;border:0;box-shadow: none;box-shadow:none;width:300px;position:fixed;top:160px;left:20px;z-index:100000;
                    ?><div style="height:auto;text-align:center;margin-bottom: 8px;"><?php
                    if($strip){
                        $link = trim(preg_replace('/&strip=1/', '', $link));
                        ?><a href="<?= $link ?>" class="bt"><?= $this->lang['show_premium'] ?></a><?php
                    }else{
                        $link .= '&strip=1';
                        ?><a href="<?= $link ?>" class="bt"><?= $this->lang['hide_premium'] ?></a><?php
                    }
                    ?></div><?php
                    
                    ?></div><?php
                }
            }
        }
        
        for ($ptr=0; $ptr<$ad_count; $ptr++) {
            $id = $ad_keys[$ptr];
            
            if ($end_user && ($ptr==4||$ptr==14)) { $this->adSlot(); }
            
            $ad = $this->classifieds()->getAd($id, $ad_cache);            
            if ($ad->id()==0) { 
                error_log("Could not fetch ad {$id} from cache");
                continue;                 
            }
            
            $pic = null;
            $this->appendLocation = true;
            
            if (!($this->user()->level()===9||$this->user->info['id']==$ad->uid())) {
                $this->stat['ad-imp'][]=$id;
            }
            
            if ($ad->hasAltContent()) {
                $langSortIdx = $this->langSortingMode > -1 ? $this->langSortingMode : 0;
                
                if (($langSortIdx==2||!$this->router->isArabic()) && $ad->rtl()) {
                    $ad->reverseContent()->setLTR();
                    $this->appendLocation = false;
                } 
                elseif (($langSortIdx==1||$this->router->isArabic()) && $ad->rtl()==0) {
                    $ad->reverseContent()->setRTL();
                    $this->appendLocation = false;
                }
            }

            $itemScope = '';
            $itemDesc = '';
            $hasSchema = false;
            if ($ad->isRealEstate()||$ad->isCar()) {
                $hasSchema = true;
                $itemDesc = 'itemprop="description" ';
                $itemScope = ' itemscope itemtype="https://schema.org/Product"';
            }
            
            $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit'] < $ad->epoch());            
            $textClass = "en";
            $liClass = "";
            
            if ($this->router->module=='detail' && $this->router->id==$ad->id()) {
                $liClass.="on ";
            }

            if ($ad->rtl()) { $textClass = "ar"; }
            
            if ($this->router->siteTranslate) { $textClass = ''; }
            
            $pix_count = $ad->picturesCount();
            if ($pix_count) {
                $pic = '<div class=card-image><div class="cbox footer"></div>';
                $pix = $ad->picturePath();
                if ($this->router->isAcceptWebP) { $pix = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pix); }                
                $pic.= '<img src="'.$this->router->config->adImgURL.'/repos/m/'.$pix.'" />';                
                if ($pix_count>1) {
                    $pic.='<div class="cbox ctr">'.$pix_count.'&nbsp;<span class="icn icnsmall icn-camera"></span></div>';                  
                }
            }
            else {
                $pic= '<div class="card-image seclogo"><div class="cbox footer"></div>';
                $pic.='<img src="'.$this->router->config->imgURL.'/200/'.$ad->sectionId().$this->router->_png.'" />';
            }
            
            if ($isNewToUser) { $pic.='<div class="cbox ctl new">NEW</div>'; }
            
            if ($ad->publisherType() && in_array($ad->rootId(), [1,2,3]) && (!$ad->isJob() || ($ad->isJob() && $ad->isVacancies()))) {
                switch ($ad->publisherType()) {
                    case 3:
                        $pic.='<div class="cbox cbr" value="a';
                        $pic.=$ad->rootId(). '">'.$this->lang['pub_3_'.$ad->rootId()].'</div>';
                        break;
                    case 1:
                        $pic.='<div class="cbox cbr" value="p';
                        if ($ad->isJob()) {                            
                            $pic.='1">'.$this->lang['bpub_1'].'</div>';
                        }
                        else {
                            $pic.='0">'.$this->lang['pub_1'].'</div>';
                        }
                        break;
                    default:
                        break;
                }
            }
            
            $pic.='<div class="cbox cbl">'.$ad->formattedSinceDate($this->lang, $this->router->isArabic()).'</div></div>';            
            
            $favLink = '';

            if ($this->user()->isLoggedIn()) {
                if ($this->userFavorites) {
                    $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                    $liClass.= 'fon ';
                }
                elseif ($this->user->favorites) {
                    if (in_array($ad->id(), $this->user->favorites)) {
                        $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                        $liClass.= 'fon ';
                    }
                }
            }
            
            echo '<div class=', $end_user?'ad ':'"ad full" ', $ad->htmlDataAttributes($this->formatNumbers);
            echo ' onclick=oad(this);';
            echo '>';
            echo '<div class="card card-product', ($ad->isFeatured()?' premium':''),'" id=', $ad->id(), ' itemprop="itemListElement" ',  $itemScope, '>', "\n";                
            echo $pic, "\n";
            
            echo '<div class=card-content>', "\n";
            echo '<div class="adc block-with-text card-description ', $textClass, '" ';
            
            echo $itemDesc, '>', "\n";
            if ($ad->latitude()||$ad->longitude()) {
                echo '<a href="#" title="', $ad->location(), '"><i class="icn icnsmall icn-map-marker"></i></a>', "\n"; 
            }
            echo $ad->text(), '</div>', "\n";
            
            echo '</div>', "\n";
            if ($this->user()->isSuperUser() && isset($this->searchResults['body']['scores'][$ad->id()])) {
                echo '<span style="direction:ltr;display:block;padding-left:20px">', $this->searchResults['body']['scores'][$ad->id()], '</span>';
            }
            echo '<div class=card-footer>', "\n";    
            echo $this->getAdSection($ad->data(), $hasSchema);
            //if ($debug) { echo "<div style=\"display:inline;font-size:9pt;\">&nbsp;{$ad[Classifieds::ID]} - {$ad[Classifieds::PRICE]}</div>"; }
            //if (!$end_user && $this->user()->isLoggedIn(9)) {
            echo '<div title="', $this->lang['reportAbuse'], '" class=abuse onclick="event.stopPropagation();report(this);"><i class="icn icn-ban"></i></div>';
            //}
            echo $favLink, '</div>', "\n", '</div>', "\n";            
            echo '</div>', "\n";
        }              
    }
    
  
    function priceResults() { 
        ?><div class="rs"><?php
        echo $this->summarizeSearch();
        $this->renderSideSections();
        $car = $this->extended[$this->extendedId][1];
        $priceRange = $this->router->db->getSectionPriceRange($this->router->countryId,$this->extendedId,1);
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
        if (!$this->userFavorites && $this->router->module!='detail') {
            $this->updateUserSearchCriteria();
        }
                        
        ?><div id=results class=row><?php
        $hasResults = $this->searchResults['body']['total_found']>0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches'])>0;
        
        if ($hasResults) {
            echo $this->summarizeSearch();
            if ($this->router->userId) {
                $this->renderSidePage();
            }
            else {
                // restore later 
                //$this->renderSideSections();
            }
                
            if ($this->router->module=='detail' && !$this->detailAdExpired) {
                $this->displayDetail();
            }
            echo '<div class=row><div class="col-2 side">', 
                $this->renderSearchSettings(),
                $this->renderExtendedLinks(),
                $this->renderLocalityLinks(), 
                $this->renderSideRoots(),
                '</div>';
            echo '<div class="row col-10 ff-cols"><div id=cards class="ls col-12" ';
            if ($this->router->module!='detail') { echo 'itemprop="mainContentOfPage" '; }
            echo 'itemscope itemtype="https://schema.org/ItemList">';
            echo '<meta itemprop="name" content="', $this->subTitle, '" />';
                
            $this->renderDResults($keywords);
            echo '</div>', $this->mt_pagination(), '</div></div>';                
          
            //echo $this->mt_pagination();
                                
            if (($this->router->module=='search'||$this->router->module=='detail') && !$this->userFavorites && !$this->router->watchId && !$this->router->userId) {
                $followStr='';
                if ($this->router->sectionId) {
                    $followUp = $this->router->db->getSectionFollowUp($this->router->countryId,$this->router->cityId,$this->router->sectionId,$this->router->purposeId);      
                    $fup = array();
                    if (isset($this->router->sections[$this->router->sectionId][6]) && $this->router->sections[$this->router->sectionId][6]) {
                        $tmpSec = explode(',', $this->router->sections[$this->router->sectionId][6]);
                        $fup = array();
                        foreach ($tmpSec as $sec) {
                            $fup[] = array($sec,0);
                        }
                    }
                        
                    $followUp = ($followUp) ? array_merge($fup, $followUp) : $fup;                        
                    if ($followUp && count($followUp)) {
                        $procSec=array();
                        $k=0;
                        foreach($followUp as $section){
                            if(!isset($procSec[$section[0]])){
                                $uri=$this->router->getURL($this->router->countryId,$this->router->cityId,0,$section[0],$section[1]);
                                $sName=$this->router->sections[$section[0]][$this->fieldNameIndex];
                                if($section[1]){
                                    $pName=$this->router->purposes[$section[1]][$this->fieldNameIndex];
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
                                            if ($this->router->language == "ar")
                                                $sName = 'وظائف ' . $sName;
                                            else
                                                $sName = $sName . ' jobs';
                                            break;
                                        case 4:
                                            $in = "";
                                            if ($this->router->language == "en")
                                                $in = " {$this->lang['in']}";
                                            $sName = $pName . $in . " " . $sName;
                                            break;
                                        case 5:
                                            if ($this->router->language == "ar"){                                               
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
                                            $sName = $sName . ' ' . ($this->router->language =='en' ? 'misc':'متفرقات');
                                            break;
                                    }
                                }
                                    
                                $iTmp='';
                                if ($this->router->sections[$section[0]][4]==1) {
                                    $iTmp.='<span class="x x'.$section[0].'"></span>';
                                }
                                elseif ($this->router->sections[$section[0]][4]==2) {
                                    $iTmp.='<span class="z z'.$section[0].'"></span>';
                                }
                                elseif ($this->router->sections[$section[0]][4]==3) {
                                    $iTmp.='<span class="v v'.$section[0].'"></span>';
                                }
                                elseif ($this->router->sections[$section[0]][4]==4) {
                                    $iTmp.='<span class="y y'.$section[0].'"></span>';
                                }
                                elseif($this->router->sections[$section[0]][4]==99){
                                    $iTmp.='<span class="u u'.$section[0].'"></span>';
                                }
                                else {
                                    $iTmp.='<span class="v'.$section[0].'"></span>';
                                }
                                    
                                $followStr.='<li class="btn sim"><a href="'.$uri.'">'.$iTmp.$sName.'</a></li>';
                                $procSec[$section[0]]=1;
                                $k++;
                                if($k==5)break;
                            }
                        }
                        if ($followStr) {
                            $followStr='<div class=card-header><div class=card-title><h5>'.$this->lang['interestSection'].'</h5></div></div><div class=card-content><ul>'.$followStr.'</ul></div></div>';
                        }
                    }
                }
                $hasExt=0;
                  
                if ($followStr) {
                    $followStr='<div class=row><div class=col-12><div class="card card-menu">'.$followStr.'</div></div>';
                    echo ' <!--googleoff: index --> ', $followStr, ' <!--googleon: index --> ';
                }
            }                
        }
        else {
            if ($this->router->watchId) {
                echo $this->summarizeSearch();
                echo $this->renderSideSections();
            }
            elseif ($this->router->userId) {
                    if ($this->router->params['q'] || $this->router->sectionId) {
                        echo $this->summarizeSearch();                    
                    }
                    $this->renderSideSections();
                    echo '<div class="no">';
                    if ($this->router->params['q']) {
                        echo $this->lang['noPartnerQAds'];                    
                    }
                    else {
                        echo $this->lang['noPartnerAds'];                    
                    }
                    echo '</div>';
                }
                elseif ($this->userFavorites) {
                    echo $this->summarizeSearch();
                } 
            else {                            
                if ($this->searchResults['body']['total_found']==0) {
                    echo $this->summarizeSearch();
                }
                $purposeId = $this->router->purposeId;
                $sectionId = $this->router->sectionId;
                $rootId = $this->router->rootId;
                $q = $this->router->params['q'];
                $extendedId = $this->extendedId;
                $localityId = $this->localityId;

                $this->alternate_search($keywords);
                                                        
                $this->router->purposeId = $purposeId;
                $this->router->sectionId = $sectionId;
                $this->router->rootId = $rootId;
                $this->router->params['q'] = $q;
                $this->extendedId=  $extendedId;
                $this->localityId=$localityId;
            }
        }
        
        
        if ($this->router->watchId && $this->searchResults!==false) {
            $cSec = count($this->watchInfo);
            if ($cSec) {
                $lang = $this->router->language == 'ar' ? '' : 'en/';
                $isOwner = $this->user->info['id'] == $this->router->watchId;
                $idKey = $this->user->encodeId($this->pageUserId);
                ?><ul id="watchbox" class='seli h sh rct<?= $this->searchResults['body']['total_found']>10 ?' selip':'' ?>'><?php
                    ?><li><?php  
                    if ($this->channelId==0) {
                        ?><b><?=$this->lang['myList'] ?></b><span class="nb"><?= $this->lang['maxWatchlistNb'] ?></span><?php
                    } 
                    else {
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
                        if ($info['COUNTRY_ID']) {
                            $uri = '/' . $this->router->countries[$info['COUNTRY_ID']]['uri'] . '/';
                            $idx=2;
                            if ($info['LOCALITY_ID']) {
                                if (!isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']])) {
                                    $localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']] = $this->router->db->getLocalitiesData($info['COUNTRY_ID'], $info['SECTION_ID'], NULL, $this->router->language);
                                }
                                if (isset($localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']])) {
                                    $uri.=$localitiesArray[$info['COUNTRY_ID'].'_'.$info['SECTION_ID']][$info['LOCALITY_ID']]['uri'] . '/';
                                    $ext = 'c-' . $info['LOCALITY_ID'] . '-' . 2 . '/';
                                }
                            }
                            elseif ($info['CITY_ID']) {
                                $idx=3;
                                $uri.=$this->router->cities[$info['CITY_ID']][3].'/';
                            }
                            if ($info['SECTION_TAG_ID']) {
                                if (!isset($extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']])) {
                                    $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']] = $this->router->db->getSectionTagsData($info['COUNTRY_ID'], $info['CITY_ID'], $info['SECTION_ID'], $this->router->language);
                                }
                                $uri.=$this->router->sections[$info['SECTION_ID']][3] . '-' . $extendedArray[$info['COUNTRY_ID'].'_'.$info['CITY_ID'].'_'.$info['SECTION_ID']][$info['SECTION_TAG_ID']]['uri'] . '/';
                                $ext = 'q-' . $info['SECTION_TAG_ID'] . '-' . $idx . '/';
                            }
                            elseif ($info['SECTION_ID']) {
                                $uri.=$this->router->sections[$info['SECTION_ID']][3] . '/';
                            }
                            if ($info['PURPOSE_ID'])
                                $uri.=$this->router->purposes[$info['PURPOSE_ID']][3] . '/';
                        }
                        $uri.=($this->router->language == 'ar' ? '' : $this->router->language . '/');
                        if ($ext) { $uri.=$ext; }
                        if ($info['QUERY_TERM']) {
                            $uri.='?q='.urlencode($info['QUERY_TERM']);
                        }
                                            
                        $iTmp='<span class="z z117"></span>';
                        if ($info['SECTION_ID'] && isset($this->router->sections[$info['SECTION_ID']])) {  
                            $rootId=$this->router->sections[$info['SECTION_ID']][4];
                            if ($rootId==1) {
                                $iTmp='<span class="x x'.$info['SECTION_ID'].'"></span>';
                            }
                            elseif($rootId==2) {
                                $iTmp='<span class="z z'.$info['SECTION_ID'].'"></span>';
                            }
                            elseif($rootId==3) {
                                $iTmp='<span class="v v'.$info['SECTION_ID'].'"></span>';
                            }
                            elseif($rootId==4) {
                                $iTmp='<span class="y y'.$info['SECTION_ID'].'"></span>';
                            }
                            elseif($rootId==99) {
                                $iTmp='<span class="u u'.$info['SECTION_ID'].'"></span>';
                            }
                            else {
                                $iTmp='<span class="z z117"></span>';
                            }
                        }
                                            
                        $info['TITLE']=preg_replace('/\\\["\']/', '',$info['TITLE']);
                    
                        if ($this->channelId==$info['ID'] || $cSec==1) {
                            ?><li><b id="<?= $info['ID'] ?>"><?= $iTmp ?><?= ($info['EMAIL'] ? '<span class="d mail"></span>':'') . $info['TITLE'] ?></b><?php
                        } 
                        else {
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
        
        if ($this->searchResults['body']['total_found']) {
            ?> <!--googleoff: all--> <div id=report class="rpd cct"><?php
            ?><b><?= $this->lang['abuseTitle'] ?></b><?php
            ?><textarea onkeydown="idir(this)" onchange="idir(this,1)"></textarea><?php
            ?><b><?= $this->lang['abuseContact'] ?></b><?php
            ?><input type=email placeholder="your.email@gmail.com" /><?php
            ?><input type=button onclick="rpa(this,2)" class="btn" value="<?= $this->lang['send'] ?>" /><?php
            ?><input type=button onclick="rpa(this,1)" class="btn cl" value="<?= $this->lang['cancel'] ?>" /><?php
            ?></div> <!--googleon: all--> <?php
        }
        $this->inlineJS('util.js')->inlineJS('ad-detail.js');
    }                
    
    
    function alternate_search($keywords = "") {
        
        if($this->router->rootId){
            $this->tmpRootId = $this->router->rootId;
        }
        if($this->router->purposeId){
            $this->tmpPurposeId = $this->router->purposeId;
        }
        if ($this->searchResults['body']['total_found'] == 0) {
            if ($this->extendedId || $this->localityId) {
                $this->extendedId = 0;
                $this->localityId = 0;
            } elseif ($this->router->purposeId) {
                $this->router->purposeId = 0;
                $this->purposeName = "";
            } elseif ($this->router->sectionId) {
                $this->router->sectionId = 0;
                $this->sectionName = "";
            } elseif ($this->router->rootId) {
                $this->router->rootId = 0;
                $this->rootName = "";
            } else {
                $this->renderSideSections(1);
                $q = "";
                if ($this->router->params['q']) 
                    $q = htmlspecialchars($this->router->params['q'], ENT_QUOTES);
                ?><div class="row"><div class="col-12"><div class="card oops"><ul class="ph"><?php
                ?><li class="title warn"><i class="icn icn-alert float-left"></i><?= $this->lang['no_result_pre'].' <b>'.$q.'</b> '.$this->lang['no_result_after'] ?></li><?php
                ?><li class="hint"><?= $this->lang['search_help_1'] ?></li><?php    
                    if ($this->publisherTypeSorting && in_array($this->tmpRootId,[1,2,3])){
                            
                        $uri = $this->getPageUri().'?';
                            if ($this->router->params['q']) {
                                $uri .= 'q=' . urlencode($this->router->params['q']).'&';
                            }  
                            $uri .= 'xd=0';
                            
                            switch($this->publisherTypeSorting){
                                case 2: 
                                    ?><li><?= ' <a href="'.$uri.'">'.($this->router->language == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mpub_3_'.$this->tmpRootId]) : ($this->lang['remove'].' '.strtolower($this->lang['spub_3_'.$this->tmpRootId]).' '.$this->lang['filter'])); ?></a></li><?php
                                    break;
                                case 1:
                                    if($this->tmpRootId == 3){
                                        ?><li><?= ' <a href="'.$uri.'">'.($this->router->language == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mbpub_1']):($this->lang['remove'].' '.strtolower($this->lang['sbpub_1']).' '.$this->lang['filter'])); ?></a></li><?php
                                    }else{
                                        ?><li><?= ' <a href="'.$uri.'">'.($this->router->language == 'ar' ? ($this->lang['npub_cancel_in'].' '.$this->lang['mpub_1']):($this->lang['remove'].' '.strtolower($this->lang['spub_1']).' '.$this->lang['filter'])); ?></a></li><?php
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    
                    
                    ?><li><?= $this->lang['search_help_2'] ?></li><?php
                    ?><li><?= $this->lang['search_help_3'] ?></li><?php
                    ?><li><?= $this->lang['search_help_4'] ?></li><?php
                ?></ul></div></div></div><?php
                return false;
            }
        }
        if (!$this->router->params['q'] && !$this->extendedId && !$this->localityId && !$this->router->purposeId && !$this->router->sectionId && !$this->router->rootId) {
            ?><div class="nad"><p><br /><br /><?= $this->lang['noAdsAtAll'] ?></p></div><?php
            ?><span class='naf'></span><?php
            return false;
        }
        $this->router->params['start'] = 0;
        $this->execute(true);
        if ($this->searchResults['body']['total_found']>0 && isset($this->searchResults['body']['matches']) && count($this->searchResults['body']['matches'])>0) {
            $this->updateUserSearchCriteria();
            $this->renderSideSections();
            echo $this->alternateSummery($this->searchResults['body']['total_found']);
                    
            if($this->router->module=='detail'){
                if($this->detailAdExpired){} else { $this->displayDetail(); }
            }
            echo '<ul class="ls">';
            $this->renderDResults($keywords);
            echo '</ul>';
            echo $this->pagination();
        } 
        else {
            $this->alternate_search($keywords);
        }
    }

            
    function getAdSection($ad, bool $hasSchema=false, bool $isDetail=false) : string {
        $section = '';
        $hasLink = true;
        if (!empty($this->router->sections)) {
            $extIDX = $this->router->isArabic() ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN;
            $locIDX = $this->router->isArabic() ? Classifieds::LOCALITIES_AR : Classifieds::LOCALITIES_EN;
            $extID = (isset($ad[$extIDX]) && isset($this->extended[$ad[$extIDX]])) ? $ad[$extIDX] : $this->extendedId;
            $hasLoc = (isset($ad[$locIDX]) && (is_array($ad[$locIDX]) && count($ad[$locIDX])>0) && (is_array($this->localities) && count($this->localities)>0));
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
    
            if ($isDetail || (!($extID && !$this->extendedId) && (!$hasLoc || ($hasLoc && $locID == $this->localityId)) && $this->router->purposeId && $this->router->sectionId)) {
                $hasLink=false;
            }

            $section = $this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
            if ($extID) { $section = $this->extended[$extID]['name']; }
            
            switch ($ad[Classifieds::PURPOSE_ID]) {
                case 1:
                case 2:
                case 8:
                case 999:
                    if ($hasSchema) {
                        $section = '<span itemprop="name">' . $section . '</span>&nbsp;' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    }
                    else {
                        $section = $section . ' ' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    }
                    break;
                case 6:
                case 7:
                    if ($hasSchema) {
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . ' <span itemprop="name">' . $section . '</span>';
                    }
                    else {
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . ' ' . $section;
                    }
                    break;
                case 3:
                    if ($this->router->isArabic()) {
                        $in = ' ';
                        if ($hasSchema)
                            $section = 'وظائف <span itemprop="name">' . $section . '</span>';
                        else
                            $section = 'وظائف ' . $section;
                    }
                    else {
                        if ($hasSchema)
                            $section = '<span itemprop="name">' . $section . '</span>&nbsp;' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                        else
                            $section = $section . ' ' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    }
                    break;
                case 4:
                    $in = ' ';
                    if (!$this->router->isArabic())
                        $in = ' ' . $this->lang['in'] . ' ';
                    if ($hasSchema)
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . $in . '<span itemprop="name">' . $section . '</span>';
                    else
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex] . $in . $section;
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
                $iter = 0;
                foreach ($ad[$locIDX] as $id=>$loc) {
                    if (isset($this->localities[$id])) {
                        if ($iter==0) {
                            $tempSection = $section . ' ' . $this->lang['in'] . ' ' . $this->localities[$id]['name'];
                        }
                        else {
                            $tempSection = $this->localities[$id]['name'];
                        }
                        $iter++;
                        if ($iter>2) {break;}
                        if ($hasLink) {
                            $idx = 2;
                            $uri = '/' . $this->router->countries[$ad[Classifieds::COUNTRY_ID]]['uri'] . '/';
                            $uri.=$this->localities[$id]['uri'] . '/';
                            $uri.=$this->router->sections[$ad[Classifieds::SECTION_ID]][3] . '/';
                            $uri.=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][3] . '/';
                            $uri.=($this->router->isArabic()?'':$this->router->language . '/') . 'c-' . $id . '-' . $idx . '/';
                            $res.='<li><a href="' . $uri . '">' . $tempSection . '</a></li>';
                        }
                        else {
                            if ($res) { $res .= '  »|  '; }
                            $res.=$tempSection;
                        }
                    }
                }
                $section = $res;
            } 
            else {
                if ($this->router->countryId && $ad[Classifieds::COUNTRY_ID] != $this->router->countryId) {
                    $ad[Classifieds::COUNTRY_ID] = $this->router->countryId;
                    $ad[Classifieds::CITY_ID] = 0;
                }

                if ($this->router->cityId && $ad[Classifieds::CITY_ID] != $this->router->cityId){
                    $ad[Classifieds::CITY_ID] = $this->router->cityId;
                }
                
                if (isset($this->router->countries[$ad[Classifieds::COUNTRY_ID]])) {
                    $countryId = $ad[Classifieds::COUNTRY_ID];
                    $cityId = 0;
                    if (!empty($this->router->countries[$countryId]['cities']) && $ad[Classifieds::CITY_ID] && isset($this->router->countries[$countryId]['cities'][$ad[Classifieds::CITY_ID]])) {
                        $cityId = $ad[Classifieds::CITY_ID];
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->countries[$countryId]['cities'][$cityId]['name'];

                        if (!mb_strstr($section, $this->router->countries[$countryId]['name'], true, "utf-8")) {
                            $section.=' ' . $this->router->countries[$countryId]['name'];
                        }                                
                    } else {
                        $section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->countries[$countryId]['name'];                    
                    }
                    if ($hasLink) {
                        if ($extID) {
                            $idx = 2;
                            $uri = '/' . $this->router->countries[$ad[Classifieds::COUNTRY_ID]]['uri'] . '/';
                            if ($cityId) {
                                $idx = 3;
                                $uri.=$this->router->countries[$ad[Classifieds::COUNTRY_ID]]['cities'][$cityId]['uri'] . '/';
                            }
                            $uri.=$this->router->sections[$ad[Classifieds::SECTION_ID]][3] . '-' . $this->extended[$extID]['uri'] . '/';
                            $uri.=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][3] . '/';
                            $uri.=($this->router->language == 'ar' ? '' : $this->router->language . '/') . 'q-' . $extID . '-' . $idx . '/';
                            $section = '<li><a href="' . $uri . '">' . $section . '</a></li>';
                        }
                        else {
                            $section = '<li><a href="' . $this->router->getURL($countryId, $cityId, $ad[Classifieds::ROOT_ID], $ad[Classifieds::SECTION_ID], $ad[Classifieds::PURPOSE_ID]) . '">' . $section . '</a></li>';
                        }
                    }
                }
            }
            
            if ($isDetail && $section) {
                return '<b>' . $section . '</b>';
            } 
            elseif(!$isDetail) {
                return (stristr($section,'<li>')) ? '<ul>'.$section.'</ul>' : $section;
            }
        }
    }

            
    function sectionSummeryMobile($forceRebuild = false) {
        if ($this->breadString && !$forceRebuild) { return $this->breadString; }
        $bread = "";
        if ($this->router->language == "ar") {
            if ($this->router->purposeId) {
                $bread.= " {$this->purposeName}";
            }
            if ($this->router->rootId) {
                if ($this->router->sectionId) {
                    $bread .= " {$this->sectionName}";
                    $bread .= " {$this->lang['included']} {$this->rootName}";
                } 
                else {
                    $bread .= " {$this->rootName}";
                }
            }
            if ($this->router->countryId || $this->router->rootId) {
                if ($this->router->countryId) {
                    if ($this->hasCities && $this->router->cityId) {
                        $bread.= " {$this->cityName}، ";
                    }
                    $bread.= " {$this->countryName} ";
                } 
                else {
                    $bread.= " {$this->countryName} ";
                }
            }
            else {
                $bread.=" {$this->lang['opt_all_countries']}";
            }
        } 
        else {
            if ($this->router->rootId) {
                if ($this->router->sectionId) {
                    $bread .= " {$this->sectionName}";
                    if ($this->router->purposeId)
                        $bread.= " {$this->purposeName}";
                    $bread .= " {$this->lang['included']} {$this->rootName}";
                }
                else {
                    $bread .= " {$this->rootName}";
                    if ($this->router->purposeId)
                        $bread.= " {$this->purposeName}";
                }
            }
            if ($this->router->countryId || $this->router->rootId) {
                if ($this->router->countryId) {
                    if ($this->hasCities && $this->router->cityId) {
                        $bread.= " {$this->cityName}, ";
                    }
                    $bread.= " {$this->countryName} ";
                } 
                else {
                    $bread.= " {$this->countryName} ";
                }
            } 
            else {
                $bread.=" {$this->lang['opt_all_countries']}";
            }
        }
        $this->breadString = $bread;
        return $this->breadString;
    }

    
    

    
    function getResulstHint(bool $forceRebuild=false) : string {
        $count = $this->searchResults['body']['total_found'];
        $hasShare=0;
        $result='';               
                        
        if ($this->userFavorites) {
            //$result.= "<p class='ph phb'>";
        }
        else {
            //$result.= '<p class=" ph'. ($hasEye ? ' phx':''). $count. '">';
        }
        
        $result.='<span><b>';                        
        if ($this->router->isArabic()) {
            if ($count>10) {
                $result.= number_format($count) ." ".$this->lang['ads'];
            }
            elseif ($count>2) {
                $result.= number_format($count)." ".$this->lang['3ad'];
            }
            elseif ($count==1) {
                $result.= $this->lang['ad'];
            }
            else {
                $result.= $this->lang['2ad'];
            }
        }
        else {
            $result.= $this->formatPlural($count, "ad");
        }        
        $result.= '</b> ';
                       
        if ($this->router->params['q']) {
            $result.=' '.$this->lang['for'].' '.$this->crumbTitle.($hasShare?'&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');
        }
        elseif ($this->userFavorites) {
            $result.=' '.$this->lang['in'].' '. $this->lang['myFavorites'];
        } 
        else { 
            $result.=' '.$this->crumbTitle.($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');                             
        }
                      
        $result.='</span>';
        return $result;
    }
            
    
    function summarizeSearch(bool $forceRebuild=false) : string {
        $count=$this->searchResults['body']['total_found'];
        $adLang='';
        if (!$this->router->isArabic()) { $adLang=$this->router->language.'/'; }
        if ($this->router->watchId) {
            $hasShare = $this->router->cfg['enabled_sharing'] && $this->router->module == 'search' && $count;
            $formatted = number_format($count);
            $bread='';
            if ($this->channelId && $this->watchName) {
                $idKey=$this->user->encodeId($this->pageUserId);
                $bread.='<div class="brd">';
                $bread.='<a href="/watchlist/?u='.$idKey.'">' . $this->lang['myList'] . '</a>';
                $bread.= '<a class="i rss" target="_blank" href="/watchlist/?u='.$idKey.'&channel='.$this->channelId.'&rss=1" id="rss-link"></a>';
                $bread.='<b>' . $this->watchName . '</b>';
                $bread .= '</div>';
            }
            else {
                $idKey=$this->user->encodeId($this->pageUserId);
                $bread.='<div class="brd">';
                $bread.= '<a class="i rss" target="_blank" href="/watchlist/?u='.$idKey.'&rss=1" id="rss-link"></a>';
                $bread.='<b>' . $this->lang['myList'] . '</b>';
                $bread .= '</div>';
            }
            $bread.= "<p class='ph'>";
            if ($this->router->isArabic()) {
                if ($count > 10) {
                    $bread.= $formatted . " " . $this->lang['ads'];
                } 
                elseif ($count > 2) {
                    $bread.= $formatted . " " . $this->lang['3ad'];
                } 
                else if ($count == 1) {
                    $bread.= $this->lang['ad'];
                } 
                elseif ($count == 2) {
                    $bread.= $this->lang['2ad'];
                } 
                else {
                    $bread.= $this->lang['0ad'];
                }
                if ($this->channelId && $this->watchName) {
                    $bread.= ' ' . $this->lang['in'] . ' ' . $this->watchName;
                }
                if (isset($this->user->params['last_visit'])) {
                    $bread.=' ' . $this->lang['sinceLast'] . ' ' . $this->formatSinceDate($this->user->params['last_visit']);
                }
            }
            else {
                if ($count) {
                    $bread.= $this->formatPlural($count, "ad");
                }
                else {
                    $bread.=$this->lang['0ad'];
                }
                if ($this->channelId && $this->watchName) {
                    $bread.= ' ' . $this->lang['in'] . ' ' . $this->watchName;
                }
                if (isset($this->user->params['last_visit'])) {
                    $bread.=' ' . $this->lang['sinceLast'] . ' ' . $this->formatSinceDate($this->user->params['last_visit']);
                }
            }
            $bread .= '</p>';
            if (!$count) {
                        $bread.='<span class="naf"></span>';
            }
        }
        elseif ($this->router->userId) {
            $hasShare = $count && $this->router->cfg['enabled_sharing'] && $this->router->module=='search';
            $formatted = number_format($count);
            $q = $this->router->params['q'];
            $uri = '/' . $this->partnerInfo['uri'] . $this->router->getURL(0, 0, 0, $this->router->sectionId, $this->router->purposeId);
            $bread='<div class="srch w">';
            $bread.='<form onsubmit="if(document.getElementById(\'q\').value)return true;return false" action="'.$uri.'" method="get">';
            $bread.='<div class="q">'; 
            $bread.='<input id="q" name="q" value="'. htmlspecialchars($q,ENT_QUOTES).'" type="text" placeholder="'.$this->lang['search_what'].'" />';
            if ($this->router->params['q']) {
                $bread.='<a class="qx" href="'.$uri.'"></a>';
            }
            $bread.='</div>';
            $bread.='<input class="bt" type="submit" value="'.$this->lang['search'].'" />';
            $bread.='</form>';
            $bread.='</div>';
            $bread.= "<p class='ph'><b>";
            if ($this->router->isArabic()) {
                if ($count > 10) {
                    $bread.= $formatted . " " . $this->lang['ads'];
                } 
                elseif ($count > 2) {
                    $bread.= $formatted . " " . $this->lang['3ad'];
                } 
                else if ($count == 1) {
                    $bread.= $this->lang['ad'];
                } 
                elseif ($count == 0) {
                    $bread.= '0 ' . $this->lang['ads'];
                } 
                else {
                    $bread.= $this->lang['2ad'];
                }
            } 
            else {
                $bread.= $this->formatPlural($count, "ad");
            }
            $bread.= '</b> ';
            if ($this->router->params['q']) {
                $bread.=$this->lang['for'] . ' ' . htmlspecialchars($this->router->params['q'], ENT_QUOTES);
                if ($this->partnerInfo['title'] || $this->router->sectionId) {
                    $bread.=' ' . $this->lang['in'] . ' ';
                    if ($this->router->isArabic()) {
                        $bread.=$this->lang['pclassifieds'] . ' ';
                    }                    
                }
            }
            
            if ($this->router->sectionId) {
                $bread.=$this->partnerSection;
                if ($this->router->params['q'] && !$this->router->isArabic()) {
                    $bread.=' ' . $this->lang['pclassifieds'];
                }
                if ($this->partnerInfo['title']) {
                    $bread.=' - ' . $this->partnerInfo['title'];
                }
            }
            $bread.='</p>';
        }
        else {
            // no signed user
            $hasShare = false;
            $bread='';
            $hasPost = (!$forceRebuild && $count);
            if ($this->userFavorites && $count==0) {                
                $bread.= "<p class='ph phb'>";
                $bread.=$this->lang['noFavoritesBrd'];
                $bread.= '</p>';
                $bread.='<div class="sid">';
                $bread.='<p class="phc">'.$this->lang['favBenefit'].'</p>';
                $bread.='<span class="naf"></span>';
                $bread.='</div>';
                $bread.='<div class="nad">';
                $bread.='<p>' . $this->lang['addF0'] . '</p><br />';
                $bread.='<img height="150" width="480" src="' . $this->router->cfg['url_css_mobile'] . '/i/t/fav' . ($this->router->language == 'ar' ? '' : 'e') . $this->router->_jpg .'" /><br />';
                $bread.='<a class="bt" href="' . $this->router->getURL($this->router->countryId, $this->router->cityId) . '"><span class="i h"></span> ' . $this->lang['noFavBackLink'] . '</a>';                        
                $bread.='</div>';
                return $bread;
            } 
            else {
                $hasShare=false;
                $formatted = number_format($count);
                if (!$this->userFavorites) { 
                    $bread=$this->getBreadCrumb($forceRebuild, $count);                     
                }
                
                if (!$this->router->isPriceList && ($this->router->module!='detail' || ($this->router->module=='detail' && $this->detailAdExpired))) {
                    if ($count) {
                        //$bread.='<div class="row col-12 target">';
                        
                        $hasEye=0;
                        if ($this->router->module=='search' && !$this->userFavorites && !$this->router->watchId && !$forceRebuild) {
                            if (($this->router->countryId && $this->router->sectionId && $this->router->purposeId) || ($this->router->params['q'] && $this->searchResults['body']['total_found']<100)) {
                                $hasEye=1;
                            }
                        }
                        if ($this->user->info['id'] && ($this->user->info['level']==6||$this->user->info['id']==5)) {
                            $hasEye=0;                        
                        }
                         /*
                        if ($this->userFavorites) {
                            $bread.= "<p class='ph phb'>";
                        }
                        else {
                            $bread.= '<p class="ph'. ($hasEye ? ' phx':''). $count. '">';
                        }
                        $bread.='<span><b>';
                       
                        if ($this->router->isArabic()) {
                            if ($count>10) {
                                $bread.= $formatted." ".$this->lang['ads'];
                            }
                            elseif ($count>2) {
                                $bread.= $formatted." ".$this->lang['3ad'];
                            }
                            elseif ($count==1) {
                                $bread.= $this->lang['ad'];
                            }
                            else {
                                $bread.= $this->lang['2ad'];
                            }
                        }
                        else {
                            $bread.= $this->formatPlural($count, "ad");
                       
                        $bread.= '</b> ';
                       
                        if ($this->router->params['q']) {
                            $bread.=' '.$this->lang['for'].' '.$this->crumbTitle.($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');
                        }
                        elseif ($this->userFavorites) {
                            $bread.=' '.$this->lang['in'].' '. $this->lang['myFavorites'];
                        } 
                        else { 
                            $bread.=' '.$this->crumbTitle.($hasShare ? '&nbsp;<span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_sharethis"></span>' : '');                             
                        }
                      
                        $bread .= '</span></p></div>';
                        }*/
                        //$bread .= '</div>';
                    }
                }                        
            }
        }
        return $bread;
    }

         
    function alternateSummery($count){
                $bread= "<p class='ph pha'>";
                if ($this->router->params['q'])
                    $bread.=$this->lang['no_listing_q'].' '.$this->lang['for'].' '.$this->crumbTitle;
                else $bread.=$this->lang['no_listing'].' '.$this->crumbTitle;
                $this->getBreadCrumb(1, $count);
                $bread.='<br />';
                $bread.=$this->lang['might_interest'].' ';
                $bread.='<b>';
                $found='';
                    if ($this->router->language=="ar") {
                        $formatted = number_format($count);
                        if (!$this->router->params['q'])
                            $found='وارد ضمن ';
                        if ($count>10) {
                                $bread.= $formatted." ".$this->lang['ads'];
                            }elseif ($count>2){
                                if (!$this->router->params['q'])
                                    $found='واردة ضمن ';
                                $bread.= $formatted." ".$this->lang['3ad'];
                            }else if ($count==1){
                                $bread.= $this->lang['ad'];
                            }else {
                                if (!$this->router->params['q'])
                                    $found='وردا ضمن ';
                                $bread.= $this->lang['2ad'];
                            }
                            $bread.= '</b> '.$found;
                        }else {
                            $bread.= $this->formatPlural($count, "ad");
                            $bread.= '</b> found in ';
                        }
                        if ($this->router->params['q'])
                          $bread.=' '.$this->lang['for'].' '.$this->crumbTitle;
                        else $bread.=' '.$this->crumbTitle;
                $bread .= '</p>';
                
                if ($this->router->module == 'search' && $this->publisherTypeSorting && in_array($this->tmpRootId,[1,2,3])){
                            $bread .= "<!--googleoff: snippet--><div class='mnb phx rc'><p>";
                            
                            $uri = $this->getPageUri().'?';
                            if ($this->router->params['q']) {
                                $uri .= 'q=' . urlencode($this->router->params['q']).'&';
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
                                    break;
                            }
                            $bread .= "</p></div><!--googleon: snippet-->";
                        }
                
                return $bread;
            }

            
    function updateUserSearchCriteria() {
        $time = time();
        if (!isset($this->user->params['search']) || (isset($this->user->params['search']['time']) && ($time-$this->user->params['search']['time']>3))) {
            $this->user->params['search'] = array(
                'cn' => $this->router->countryId,
                'c' => $this->router->cityId,
                'ro' => $this->router->rootId,
                'se' => $this->router->sectionId,
                'pu' => $this->router->purposeId,
                'start' => $this->router->params['start'],
                'q' => $this->router->params['q'],
                'exId' => $this->extendedId,
                'locId' => $this->localityId,
                'uId' => $this->router->userId,
                'time' => $time
            );
            $this->user->update();
        }
    }

    
    function renderSubSections() {
        if ($this->router->rootId && count($this->router->pageSections)>1) {
            $hasQuery = false;
            $q = '';
            if ($this->router->params['q']) {
                $hasQuery = true;
                $q = '?q='.urlencode($this->router->params['q']);
            }
            echo "<ul>";
            $i = 0;
            
            $sectionClass='<span class="v';            
            switch ($this->router->rootId) {
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
                $pId = $this->router->rootId==3 ? 3 : 0;                
                
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, 0, $pId) . $q, $this->router->sectionId == 0,'ovh'), '</li>';
                foreach ($this->router->pageSections as $section_id=>$section) {
                    $selected = ($this->router->sectionId == $section_id ? true : false);
                    if ($this->extendedId || $this->localityId)
                        $selected = false;
                    $purposeId = 0;
                    if ($this->router->rootId==3) {
                        $purposeId = 3;
                    }
                    elseif (count($section['purposes'])==1) {
                        $purposeId = array_keys($section['purposes'])[0];
                    }
                    elseif ($this->router->purposeId && isset ($section['purposes'][$this->router->purposeId])) {
                        $purposeId = $this->router->purposeId;
                    }                
                    echo '<li>', $this->renderSubListLink($sectionClass.$section_id.'"></span>'.$section['name'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section_id, $purposeId) . $q, $selected), '</li>';
                    $i++;
                }              
            }
            else {                
                $pId = $this->router->rootId == 3 ? 3 : 0;
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, 0, $pId).$q, $this->router->sectionId==0, 'ovh'), '</li>';
                
                foreach ($this->router->pageSections as $section_id=>$section) {
                    $selected = $this->router->sectionId==$section_id;
                    if ($this->extendedId||$this->localityId) {
                        $selected = false;
                    }
                    $purposeId = 0;
                    if ($this->router->rootId==3) {
                        $purposeId = 3;
                    }
                    elseif (count($section['purposes'])==1) {
                        $purposeId = array_keys($section['purposes'])[0];
                    }
                    elseif ($this->router->purposeId && isset($section['purposes'][$this->router->purposeId])) {
                        $purposeId = $this->router->purposeId;
                        $section['counter'] = $section['purposes'][$purposeId]['counter'];
                    }
                    $isNew = (!$selected && $this->checkNewUserContent($section['unixtime']));
                    $iTmp=$sectionClass.$section_id.'"></span>';
                    echo '<li', ($isNew ? ' class="nl">' : '>'),
                    $this->renderSubListLink($iTmp.$section['name'].'&nbsp;<span class=c>('.$section['counter'].')</span>', $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section_id, $purposeId), $selected), '</li>';
                    $i++;
                }
            }
            echo '</ul>';
        }
    }
        
    
    function renderMobileLocalityLinks(){
        if ($this->router->rootId == 1 && $this->router->countryId && count($this->localities)) {
            $q = '';
            if ($this->router->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->router->params['q']);
            }
            $citiesList = '';
            $prefix_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';
            $keyIndex = 2;
            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';
            if ($this->router->sectionId) {
                $suffix_uri.=$this->router->sections[$this->router->sectionId][3] . '/';
                $prefix = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex] . ' ';
            } else {
                $suffix_uri.=$this->router->roots[$this->router->rootId][3] . '/';
                $prefix = $this->router->roots[$this->router->rootId][$this->fieldNameIndex] . ' ';
            }
            $sectionName = $this->sectionName;
            if ($this->router->purposeId) {
                $suffix_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                switch ($this->router->purposeId) {
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
            if ($this->router->language != 'ar') {
                $suffix_uri.=$this->router->language . '/';
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
                    ?><h2 class="ctr"><?= $this->lang['suggestionLocation'] . ($this->localityId ? $this->localities[$this->localityId]['name'] . $this->lang['?'] : $this->router->countries[$this->router->countryId]['name'] . $this->lang['?']) ?></h2><?php
                    
                $hasExt=1;
                if($locId && isset($this->localities[$locId])){                    
                    ?><ul class='ls'><?php
                    /*
                    ?><li class="bbr"><a href="<?= $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) ?>"><?= $this->lang['opt_all_areas'] ?><span class="to"></span><span class="n"><?= $this->router->pageSections[$this->router->sectionId]['counter'] ?></span></a></li><?php 
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
                            ?><li class="bbr"><a href='<?= $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId).$q ?>'><?= $this->cityName ? $this->cityName : $this->countryName ?><span class='to'></span></a></li><?php
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
                    if($this->router->sectionId){
                        ?><span class="n"><?= $this->router->pageSections[$this->router->sectionId]['counter'] ?></span><?php
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
    
    
    function renderLocalityLinks() {
        if ($this->router->rootId==1 && $this->router->countryId && is_array($this->localities) && count($this->localities)) {
            $q = '';
            if ($this->router->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->router->params['q']);
            }
            $citiesList = '';
            $prefix_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';
            $keyIndex = 2;
            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';
            if ($this->router->sectionId) {
                $suffix_uri.=$this->router->sections[$this->router->sectionId][3] . '/';
                $prefix = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex] . ' ';
            } else {
                $suffix_uri.=$this->router->roots[$this->router->rootId][3] . '/';
                $prefix = $this->router->roots[$this->router->rootId][$this->fieldNameIndex] . ' ';
            }
            $sectionName = $this->sectionName;
            if ($this->router->purposeId) {
                $suffix_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                switch ($this->router->purposeId) {
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
            if ($this->router->language != 'ar') {
                $suffix_uri.=$this->router->language . '/';
            }
            $prefix.=$this->lang['in'] . '.. ';
            $pass = false;
            $locId = $this->localityId;
            $isParent = true;
            $hasParent = false;
            $hasChildren = false;
            if ($locId && isset($this->localities[$locId])) {
                if ($this->localities[$locId]['parent_geo_id'] && isset($this->localities[$this->localities[$locId]['parent_geo_id']])) {
                    $hasParent=true;
                }
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
            
            
            if ($locId && isset($this->localities[$locId]) && !$citiesList) {
                $isParent=false;
                $parentId = $this->localities[$locId]['parent_geo_id'];
                if ($parentId) {
                    if (isset($this->localities[$parentId])) {
                        $childCount = 0;
                        $children = array();
                        foreach ($this->localities as $lid=>$sub) {
                            if ($sub['parent_geo_id'] == $parentId) {
                                $selected = false;
                                if($locId == $lid) $selected = true;
                                $append = 'c-' . $lid . '-' . $keyIndex . '/'.$q;
                                if ($selected) {
                                    $citiesList.='<li class="ov">' . $sub['name'] . '</li>';
                                }
                                else {
                                    $citiesList.='<li><a href="' . $prefix_uri . $sub['uri'] . $suffix_uri . $append . '">' . $sub['name'] . '</a></li>';
                                }
                                $pass = true;
                                $children[]=$lid;
                                $childCount++;
                            }
                        }
                        
                        if ($childCount==1) {
                            $citiesList = '';
                            $pass = false;
                        }
                    }
                }
                else {
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
                if ($locId && isset($this->localities[$locId])) {  
                    ?><ul class='sm smn'><?php
                    
                    $alocId = (int)$locId;
                    $parentIds=array();
                    if ($isParent) { $parentIds[]=(int)$locId; }
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
                    foreach ($parentIds as $pid) {
                        if($pid){
                            if($pid == $locId){
                                ?><li class='sm<?= $i++ ?> on'><b><?= $this->localities[$pid]['name'] ?></b></li><?php
                            }else{                                
                                //$append = 'c-' . $this->localities[$pid][0] . '-' . $keyIndex . '/'.$q;
                                $append = 'c-' . $pid . '-' . $keyIndex . '/'.$q;
                                ?><li class='sm<?= $i++ ?>'><a href='<?= $prefix_uri . $this->localities[$pid]['uri'] . $suffix_uri . $append ?>'><?= $this->localities[$pid]['name'] ?></a></li><?php
                            }
                        }else{
                            ?><li><a href='<?= $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId).$q ?>'><?= $this->cityName ? $this->cityName : $this->countryName ?></a></li><?php
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
                    
                }
                else {
                    echo '<div class="title"><h5>', $prefix, '</h5></div>', '<ul>', $citiesList, '</ul>';
                }
            }
        }
    }
    
    
    function renderSearchSettings() : void {
        $q = $this->getPageUri().'?';
        if ($this->router->params['q']) {
            $q .= 'q='.\urlencode($this->router->params['q']).'&';
        }
        $ql = $q;
        $pl = $q;
        $q .= 'sort=';
        $ql .= 'hr=';
        $pl .= 'xd=';
        
        echo '<div class=title><h5>', $this->lang['search_settings'], '</h5></div>';
        $purposes=$this->filterPurposesArray();
        if (\count($purposes)>1) {
            echo '<ul>';
            foreach ($purposes as $purpose) {
                echo $purpose;
            }
            echo '</ul>', '<hr>';
        }
        echo '<div class=select><select class="select-text" onchange="sorting(this)">';
        echo '<option value="', $q, '0"', ($this->sortingMode==0)?' selected':'', '>', $this->lang['sorting_0'], '</option>';
        echo '<option value="', $q, '1"', ($this->sortingMode==1)?' selected':'', '>', $this->lang['sorting_1'], '</option>';
        echo '</select>';
        echo '<span class="select-highlight"></span>', '<span class="select-bar"></span>', '<label class="select-label">', $this->lang['sorting'],'</label>';
        echo '</div>';
              
        echo '<div class=select><select class="select-text" onchange="sorting(this)">';
        echo '<option value="', $ql, '0"', ($this->langSortingMode==0)?' selected':'', '>', $this->lang['lg_sorting_0'], '</option>';
        echo '<option value="', $ql, '1"', ($this->langSortingMode==1)?' selected':'', '>', $this->lang['lg_sorting_1'], '</option>';
        echo '<option value="', $ql, '2"', ($this->langSortingMode==2)?' selected':'', '>', $this->lang['lg_sorting_2'], '</option>';
        echo '</select>';
        echo '<span class="select-highlight"></span>', '<span class="select-bar"></span>', '<label class="select-label">', $this->lang['lg_sorting'],'</label>';
        echo '</div>';
        
        if (\in_array($this->router->rootId, [1,2,3])) {
            echo '<div class=select style="margin:8px 0"><select class="select-text" onchange="sorting(this)">';
            echo '<option value="', $pl, '0"', ($this->publisherTypeSorting==0)?' selected':'', '>', $this->lang['spub_0'], '</option>';
            echo '<option value="', $pl, '1"', ($this->publisherTypeSorting==1)?' selected':'', '>', $this->router->rootId==3?$this->lang['sbpub_1']:$this->lang['spub_1'], '</option>';          
            echo '<option value="', $pl, '2"', ($this->publisherTypeSorting==2)?' selected':'', '>', $this->lang['spub_3_'.$this->router->rootId], '</option>';
            echo '</select>';
            echo '<span class="select-highlight"></span>', '<span class="select-bar"></span>' /*, '<label class="select-label">', $this->lang['lg_sorting'],'</label>'*/;
            echo '</div>';          
        }         
    }
    
    
    
    function renderExtendedLinks() {
        if ($this->router->sectionId && is_array($this->extended) && count($this->extended)) {
            $prefix_uri = '/';
            if ($this->router->countryId) {
                $prefix_uri.=$this->router->countries[$this->router->countryId]['uri'] . '/';
                $keyIndex = 2;
            }
            else {
                $keyIndex = 1;
            }

            $q = '';
            if ($this->router->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->router->params['q']);
            }

            $suffix_uri = '/';
            $prefix = '';
            $suffix = '';

            if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                $keyIndex++;
                $prefix_uri.=$this->router->cities[$this->router->cityId][3] . '/';
            }

            if ($this->router->purposeId) {
                $suffix_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                /*switch ($this->router->purposeId) {
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
            if ($this->router->language != 'ar') {
                $suffix_uri.=$this->router->language . '/';
            }                            
            $hasExt=1;
            
            $iTmp='';
            if($this->router->rootId==1){
                $iTmp.='<span class="x x'.$this->router->sectionId.'"></span>';
            }elseif($this->router->rootId==2){
                $iTmp.='<span class="z z'.$this->router->sectionId.'"></span>';
            }elseif($this->router->rootId==3){
                $iTmp.='<span class="v v'.$this->router->sectionId.'"></span>';
            }elseif($this->router->rootId==4){
                $iTmp.='<span class="y y'.$this->router->sectionId.'"></span>';
            }elseif($this->router->rootId==99){
                $iTmp.='<span class="u u'.$this->router->sectionId.'"></span>';
            }else {
                $iTmp.='<span class="v'.$this->router->sectionId.'"></span>';
            }
            
            echo '<div class="title"><h5>', $iTmp, $this->sectionName, '</h5></div>', '<ul>';
            ?><li><ul><?php 
                    if($this->extendedId==0){
                        echo "<li class='f ov'>".$this->lang['opt_all_categories']."</li>";
                    }else{
                        echo "<li class='f'>".$this->renderSubListLink($this->lang['opt_all_categories'], $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId), 0)."</li>";
                    }
                        foreach ($this->extended as $eid=>$sub) {
                            $append = 'q-' . $eid . '-' . $keyIndex . '/'.$q;
                            if ($this->extendedId == $eid) {
                                ?><li class="ov"><?= $prefix . $sub['name'] . $suffix ?></li><?php
                            } else {
                                ?><li><a href="<?= $prefix_uri . $this->router->sections[$this->router->sectionId][3] . '-' . $sub['uri'] . $suffix_uri . $append ?>"><?= $prefix . $sub['name'] . $suffix ?></a></li><?php
                            }
                        }
                        ?></ul></li></ul><?php
        }
    }

    
    function renderSideSections($noAds=false) {
        $noAds = $this->router->config->enabledAds() ? $noAds : 0;
        $countAds=0;
        if (isset($this->searchResults['body']['matches'])) { $countAds=count($this->searchResults['body']['matches']); }
        if ($this->router->module=='detail') {
            if (!$this->detailAdExpired){
                if ($this->detailAd[Classifieds::PUBLICATION_ID]==1) {
                    $countAds+=4;
                }
                else {
                    $countAds+=2;
                }
            }            
        }
        
        echo '<ul id="siAd" class="list">', "\n";            
        if ($this->user->info['id'] && $this->user->info['level']==9 && !$this->router->userId && $this->router->module=='detail' && isset($this->detailAd[($this->router->isArabic()?Classifieds::EXTENTED_AR:Classifieds::EXTENTED_EN)]) && !$this->detailAdExpired && $this->extended) {
            $extId=$this->detailAd[( $this->router->isArabic() ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN )];
            $detailYear = Classifieds::detectYear($this->detailAd[Classifieds::CONTENT]);
            if ($detailYear) {
                $currency='$';
                $priceRange = $this->router->db->getSectionPriceRange($this->detailAd[Classifieds::COUNTRY_ID], $extId, 1);
                //var_dump($priceRange);
                if (isset($priceRange[$detailYear])) {                            
                    $append_uri = ($this->router->language != 'ar' ? $this->router->language . '/' : '') . 'q-' . $extId . '-' . ($this->router->countryId ? ($this->hasCities && $this->router->cityId ? 3 : 2) : 1).'/price/';
                    $extended_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';
                    if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                        $extended_uri.=$this->router->cities[$this->router->cityId][3] . '/';
                    }
                    $extended_uri.=$this->router->sections[$this->router->sectionId][3] . '-' . $this->extended[$extId]['uri'] . '/';
                            
                    echo '<li><div class="prx ',  $this->router->language,'">';
                    echo '<p>';
                    echo $this->lang['price_box_pre'],'<b>',$this->extended[$extId]['name'],'</b>',$this->lang['price_model'],'<b>',$detailYear,'</b>',$this->lang['price_box_suf'];
                    echo $priceRange[$detailYear][1],$currency,  $this->lang['and'] ,$priceRange[$detailYear][2],$currency;
                    echo '</p>';
                    echo '<a href="',$extended_uri,$append_uri,'" class="bt">',  ($this->router->language =='ar' ? $this->lang['price_more'].$this->extended[$extId][1] : $this->extended[$extId][1].$this->lang['price_more']),'</a>';
                    echo '<p class="nb">';
                    echo $this->lang['price_claimer'];
                    echo '</p>';
                    echo '</div></li>';
                }
            }
        }

        if (!$noAds) {
            switch ($countAds) {
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
                    if ($countAds>8) {
                        ?><li class="lad a600"><?php echo $this->fill_ad("zone_11", 'ad_x') ?></li><?php
                    }
                    break;
            }
        }
        if ($this->router->config->enabledAds() && $countAds>=10) {
            ?><li><?php
            ?><ins class="adsbygoogle" class="ad600" data-ad-client="ca-pub-2427907534283641" data-ad-slot="9190558623"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script><?php
            ?></li><?php             
        }
        
        if(0 && $this->router->countryId==1){
            ?><li><iframe class="adsawa" src="/web/gosawa.html"></iframe></li><?php
        }
                
        if(0 && $countAds>7) {
            ?><li><div class="g-page" data-href="https://plus.google.com/+MourjanAds/posts" data-width="300" data-layout="landscape" data-rel="publisher"></div></li><?php
            ?><li><div class="fb-like-box fb-like-side" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="300" data-show-faces="true" data-stream="false" data-show-border="false" data-header="false"></div></li><?php 
        }
        ?></ul><?php
    }
   
    
    function getBreadCrumb(bool $forceSetting=false, int $count=0) : string {        
        if ($this->crumbString && !$forceSetting) { return $this->crumbString; }
        if (!$forceSetting || $this->router->module=='detail') {
            if (isset($this->router->params['tag_id']) && isset($this->extended[$this->router->params['tag_id']])) {
                $this->extendedId = $this->router->params['tag_id'];
            }
        }
        
        $isDynamic = false;
        $q = "";
        
        if ($this->router->params['q']) {
            $q = htmlspecialchars($this->router->params['q'], ENT_QUOTES);
        } 
        elseif ($this->router->force_search) {
            $q = $this->lang['search_general'];
        }
        
        $tempTitle = '';
        $subPurpose = '';
        if ($this->extendedId && $this->router->sectionId) {
            
            $append_uri = (!$this->router->isArabic()?$this->router->language.'/q-':'q-').$this->extendedId.'-'.($this->router->countryId ? ($this->hasCities && $this->router->cityId ? 3 : 2) : 1);
            $extended_uri = ($this->router->countryId) ? '/'.$this->router->countries[$this->router->countryId]['uri'].'/' : '/';
            $this->title = $this->extended[$this->extendedId]['name'];
            
            if ($this->router->isPriceList) {
                $this->title= ($this->router->isArabic() ? $this->lang['price_more'].$this->title : $this->title.$this->lang['price_more']);
            }
            elseif ($this->router->purposeId) {
                switch ($this->router->purposeId) {
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
            if ($this->router->countryId) { $this->title.=' '.$this->lang['in'].' '; }

            if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                $extended_uri.=$this->router->cities[$this->router->cityId][3] . '/';
                $this->title.=$this->router->cities[$this->router->cityId][$this->fieldNameIndex] . ' ';
            }

            $extended_uri.=$this->router->sections[$this->router->sectionId][3] . '-' . $this->extended[$this->extendedId]['uri'] . '/';
            if ($this->router->purposeId) {
                $extended_uri.=$this->router->purposes[$this->router->purposeId][3].'/';                
            }
            $extended_uri.=$append_uri . '/';
            $this->extended_uri = $extended_uri;
            if ($this->router->countryId) {
                $this->title.=$this->router->countries[$this->router->countryId]['name'];
            }
        }
        elseif ($this->localityId && $this->router->sectionId) {
            $prefix_parent_name = '';
            $suffix_parent_uri = '';
            $prefix_append_uri = '';
            $suffix_append_uri = '';
            $prefix_append_uri = ($this->router->language != 'ar' ? $this->router->language . '/' : '') . 'c-';
            $append_uri = $prefix_append_uri . $this->localityId . '-';
            $extended_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';

            $keyIndex = 2;
            $append_uri.=$keyIndex;
            $suffix_append_uri = '-' . $keyIndex . '/';
            $prefix_parent_uri = $extended_uri;

            $extended_uri.=$this->localities[$this->localityId]['uri'] . '/';
            $this->title = $this->localities[$this->localityId]['name'];
            
            if ($this->router->sectionId) {
                $extended_uri.=$this->router->sections[$this->router->sectionId][3] . '/';
                $suffix_parent_uri = '/' . $this->router->sections[$this->router->sectionId][3] . '/';
                $this->title = $this->router->sections[$this->router->sectionId][$this->fieldNameIndex];
            } 
            else {
                $extended_uri.=$this->router->roots[$this->router->rootId][3] . '/';
                $suffix_parent_uri = '/' . $this->router->roots[$this->router->rootId][3] . '/';
                $this->title = $this->router->roots[$this->router->rootId][$this->fieldNameIndex];
            }
            
            if ($this->router->purposeId) {
                $extended_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                $suffix_parent_uri.=$this->router->purposes[$this->router->purposeId][3] . '/';
                switch ($this->router->purposeId) {
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
        }
        else {
            $this->extendedId=0;
            $this->localityId=0;
            if ($forceSetting) {
                $uri = rtrim($this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId, false), '/');

                $url_codes = $this->router->FetchUrl($uri);
                if ($url_codes) {
                    $this->router->pageTitle['en'] = $url_codes[6];
                    $this->router->pageTitle['ar'] = $url_codes[7];
                    $this->title = $this->router->pageTitle[$this->router->language];
                }
            }
            $tempTitle = '';
            if ($this->router->pageTitle[$this->router->language] == '' || $q || (!$this->router->sectionId && !$this->router->rootId)) {
                if (!$q && !$this->router->sectionId && !$this->router->rootId) {
                    $tempTitle = $this->router->pageTitle[$this->router->language];
                }
                $this->title = $this->getDynamicTitle($forceSetting);
                $isDynamic = true;
            }
            elseif ($count) {
                $this->title = preg_replace('/' . $this->lang['mourjan'] . '\s/i', '', $this->title);
            }
            $pos = strrpos($this->title, ' ' . $this->lang['in'] . ' ');
            $sub_title = (empty($pos) ? $this->title : substr($this->title, 0, $pos));
        }
        $bc = array();

        $countryId = $this->router->countryId;
        $countryName = $this->countryName;
        if (!$countryId) { $countryName=$this->lang['mourjan']; }
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId = $this->user->params["country"];
            $countryName = $this->router->countries[$this->user->params["country"]]['name'];
        }
        
        $bc[] = "<div class='brd' itemprop='breadcrumb'><div><a href='" . $this->router->getURL($countryId) . "'>{$countryName}</a>";

        if ($this->hasCities && $this->router->cityId) {
            $bc[] = "<a href='" . $this->router->getURL($this->router->countryId, $this->router->cityId) . "'>{$this->cityName}</a>";
        }

        if ($this->userFavorites) {
            $bc[] = "<b>" . $this->lang['myFavorites'] . "</b>";
        } 
        elseif($this->router->watchId) {
            $bc[] = "<b>" . $this->lang['myList'] . "</b>";
        } 
        else {
            if ($this->router->rootId) {
                $purposeId = 0;
                if (isset($this->router->pageRoots[$this->router->rootId]) && 
                    is_array($this->router->pageRoots[$this->router->rootId]['purposes']) && 
                    count($this->router->pageRoots[$this->router->rootId]['purposes'])==1 ) {
                    $purposeId = array_keys($this->router->pageRoots[$this->router->rootId]['purposes'])[0];
                }
                
                if (($q || $this->router->purposeId || $this->router->sectionId) && !($this->router->sectionId == 0 && $purposeId)) {
                    $bc[] = "<a href='" .
                            $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, 0, $purposeId) .
                            "'>{$this->rootName}</a>";
                }
                if ($this->router->sectionId) {
                    if (array_key_exists($this->router->sectionId, $this->router->pageSections)) {
                        $purposeId = $this->router->pageSections[$this->router->sectionId]['purposes'];
                        if (is_numeric($purposeId))
                            $purposeId = (int) $purposeId;
                        else
                            $purposeId = 0;
                    } else {
                        $purposeId = 0;
                    }
                    if ($this->extendedId || $this->localityId || (($q || $this->router->purposeId) && !$purposeId)) {
                        $bc[] = "<a href='" .
                                $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $purposeId) .
                                "'>{$this->sectionName}</a>";
                    }
                }
            }

            if ($q) {
                if ($subPurpose) {
                    $bc[] = "<a href='" .
                            $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) .
                            "'>" . $subPurpose . "</a>";
                }
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

                if ($forceSetting || $this->router->module=="detail") {
                    $qStr = '';
                    if ($this->router->params['q']) {
                        $qStr = '?q=' . urlencode($this->router->params['q']);                        
                    }
                    if ($this->extendedId || $this->localityId) {
                        $bc[] = "<a href='" .
                                $extended_uri . $qStr .
                                "'>" . $q . ' ' . $this->lang['in'] . ' ' . $sub_title . "</a>";
                    } else {
                        $bc[] = "<a href='" .
                                $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) . $qStr .
                                "'>" . $q . ' ' . $this->lang['in'] . ' ' . $sub_title . "</a>";
                    }
                } 
                else {
                    $qStr = '';
                    if ($this->router->params['q']) {
                        $qStr = '?q='.urlencode($this->router->params['q']);
                    }
                    if ($this->router->rootId || $this->router->sectionId || $this->router->purposeId) {
                        $bc[] = '<a class="icn icnsmall icn-rss" target="_blank" href="' .
                                (($this->extendedId || $this->localityId) ? $extended_uri . $qStr : $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) . $qStr ) .
                                '&rss=1" id="rss-link">' . '</a><b itemprop="headline">' . ($isDynamic ? '' : $q . " " . $this->lang['in'] . " ") . $sub_title . '</b>';
                        if (!$isDynamic)
                            $this->title = $q . ' ' . $this->lang['in'] . ' ' . $this->title;
                    }
                    else {
                        $bc[] = '<b itemprop="headline">' . $q . '</b>';
                        if (!$isDynamic)
                            $this->title = $q . ' ' . $this->lang['in'] . ' ' . $this->title;
                    }
                }
            }
            else {
                if ($subPurpose && ($this->localityId || $this->extendedId)) {
                    $bc[] = "<a href='" .
                            $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) .
                            "'>" . $subPurpose . "</a>";
                }
                if ($forceSetting || $this->router->module == "detail" || $this->router->isPriceList) {
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
                                for ($j=$k-1; $j>=0; $j--) {
                                    $bc[] = $tmp[$j];
                                }
                            }
                        }                     
                        
                        if($this->router->isPriceList){
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
                                $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId) .
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
                    $bc[] = '<b itemprop="headline name">' . $sub_title . '</b>';
                }
                $this->subTitle = $sub_title;
            }
        }

        $this->crumbTitle = $this->title;       
        
        $this->crumbString = '<div class=row><div class=col-12>' . implode("", $bc) . '</div><span style="margin:0 8px;">'. $this->getResulstHint($forceSetting). '</span>'.  '</div></div></div>';
      
        if ($tempTitle) { $this->title = $tempTitle;  }
        return $this->crumbString;
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
        if ($this->router->params['q']) {
            $q = htmlspecialchars($this->router->params['q'], ENT_QUOTES);
        } elseif ($this->router->force_search) {
            $q = $this->lang['search_general'];
        }
        $countryId = $this->router->countryId;
        $countryName = $this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId = $this->user->params["country"];
            $countryName = $this->router->countries[$this->user->params["country"]]['name'];
        }
        $location = $current = $this->countryName;
        if ($this->hasCities && $this->router->cityId) {
            $last = $current;
            $current = $this->cityName;
            $location = $this->cityName . " " . $location;
        }
        if ($this->userFavorites) {
            $summery = $current = $this->lang['myFavorites'];
        } elseif($this->router->watchId) {
            $summery = $current = $this->lang['myList'];
        } else {
            $defPurpose = count($this->router->purposes) > 1 ? false : true;
            if ($this->router->rootId) {
                $last = $current;
                $summery = $current = $this->rootName;

                if ($this->router->sectionId) {
                    $last = $current;
                    $summery = $current = $this->sectionName;
                }
                $appendLocation = true;
            }

            if ($this->router->purposeId) {
                if ($this->router->rootId || $this->router->sectionId) {
                    switch ($this->router->purposeId) {
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
                            if ($this->router->language == "en")
                                $in = " {$this->lang['in']}";

                            if ($this->router->sectionId) {
                                $last = $current;
                                if ($this->router->language == "ar")
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
                            if ($this->router->language == "en")
                                $in = " {$this->lang['in']}";
                            if ($this->router->sectionId) {
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
                $section=$this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex].' '.$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].' '.$this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].'/', $this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex])){
                    $section=$this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                }else {
                    $in=' ';
                    if ($this->router->language=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].$in.$this->router->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
                }
                break;
        }           
        return $section;
    }

    
    function buildTitle() {
        $title = '';
        if ($this->router->module == 'detail') {
            $title = $this->title;
            if ($this->detailAdExpired) {
                if (empty($this->detailAd)) {
                    header("HTTP/1.0 410 Gone");
                    $title = $this->title;
                } 
                else {
                    $this->router->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                }
            } 
            else {
                $this->router->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                $title=$this->BuildExcerpts($this->detailAd[Classifieds::CONTENT],40,'');
            }
        } 
        else {
            $title = $this->title;
            if ($this->localityId && !preg_match('/'.$this->router->countries[$this->router->countryId]['name'].'/',  $this->title)) {
                $title.=' ' . $this->router->countries[$this->router->countryId]['name'];
            }
            if ($this->router->params['start'] > 1) {
                $title .= $this->lang['search_suffix'] . $this->router->params['start'];
            }
            if (!$this->extendedId && !$this->localityId && $this->router->naming != NULL && !empty($this->router->naming[2])) {
                if ($this->hasCities && $this->router->cityId) {
                    $location = $this->lang['in'] . ' ' . $this->cityName . ' ' . $this->countryName;
                }
                else {
                    $location = $this->lang['in'] . ' ' . $this->countryName;
                }                
                
                if (strpos($this->router->naming[2], "%s")) {
                    $this->lang['description'] = sprintf($this->router->naming[2], $this->purposeName, $location);
                }
                else {
                    $this->lang['description'] = $this->router->naming[2];
                    $patterns = array('/{p}/', '/{l}/', '/{d}/', '/\s+/');
                    $replacements = array($this->purposeName, $location, $this->router->count, ' ');
                    $this->lang['description'] = preg_replace($patterns, $replacements, $this->lang['description']);
                }
                
                if ($this->router->params['start'] > 1) {
                    $this->lang['description'] .= $this->lang['search_suffix'] . $this->router->params['start'];
                }
            }
            else {
                $this->lang['description'] = $this->lang['search_description'] . $title;
            }
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
                $section=$this->router->sections[$ad['SECTION_ID']][$this->fieldNameIndex].' '.$this->router->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->router->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].' '.$this->router->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if (preg_match('/'.$this->router->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].'/', $this->router->sections[$ad['SECTION_ID']][$this->fieldNameIndex])) {
                    $section=$this->router->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                else {
                    $in=' ';
                    if ($this->router->language=='en') { $in=' '.$this->lang['in'].' '; }
                    $section=$this->router->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].$in.$this->router->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                break;
        }
           
       $adContent = json_decode($ad['CONTENT'], true);
       $countries = $this->router->db->getCountriesDictionary(); 
       if (isset($adContent['pubTo'])) {
            $fieldIndex=2;
            $comma=',';
            if ($this->router->language=='ar') {
                $fieldIndex=1;
                $comma='،';
            }
            $countriesArray=array();
            $cities = $this->router->cities;
   
            $content='';
            foreach ($adContent['pubTo'] as $city => $value) {                    
                if (isset($cities[$city]) && isset($cities[$city][4])) {
                    $country_id=$cities[$city][4];                        
                    if (!isset($countriesArray[$cities[$city][4]])) {                            
                        $ccs = $countries[$country_id][6];
                        if ($ccs && count($ccs)>0) {
                            $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],array());
                        }
                        else {
                            $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],false);
                        }
                    }
                    if ($countriesArray[$country_id][1]!==false) {
                        $countriesArray[$country_id][1][]=$cities[$city][$fieldIndex];
                    }
                }
            }
            
            $i=0;
            foreach ($countriesArray as $key => $value) {
                if ($i) { $content.=' - '; }
                $content.=$value[0];
                if ($value[1]!==false) {
                    $content.=' ('.implode ($comma, $value[1]).')';
                }
                $i++;
            }
                
            if ($content) {
                $section=$section.' '.$this->lang['in'].' '.$content;
            }
        }
        elseif(isset ($countries[$ad['COUNTRY_ID']])) {
            $countryId=$ad['COUNTRY_ID'];
            $countryCities=$countries[$countryId][6];
            if (count($countryCities)>0 && isset($this->router->cities[$ad['CITY_ID']])) {
                $section=$section.' '.$this->lang['in'].' '.$this->router->cities[$ad['CITY_ID']][$this->fieldNameIndex].' '.$countries[$countryId][$this->fieldNameIndex];
            }
            else {
                $section=$section.' '.$this->lang['in'].' '.$countries[$countryId][$this->fieldNameIndex];
            }
        }
        return $section;
    }
    
}

?>