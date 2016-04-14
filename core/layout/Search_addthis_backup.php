<?php
include_once $config['dir'].'/core/layout/Page.php';
include_once $config['dir'].'/core/model/WebTitle.php';

class Search extends Page{

    var $id=0, $paginationString='',$adCount=0,$subTitle='';
    var $classifieds=null,$breadString='',$crumbTitle='',$crumbString='',$adRef='',$dynamicTitle='';

    function Search($router){
        parent::Page($router);
        if ($this->userFavorites) {
            $this->checkBlockedAccount();
        }
        if ($this->isMobile) {
            if ($this->isMobileAd)
                $this->set_ad(array('Leaderboard'=>array('/1006833/mourjan-mobile', 320, 50, 'div-gpt-ad-1326381096859-0')));
            else
                $this->set_ad(array('Leaderboard'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0')));

            $this->num=5;
            if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && strstr($_SERVER['HTTP_USER_AGENT'], 'iPad;'))
                $this->num=10;

        }else {
            if (!$this->userFavorites) {
                if ($this->urlRouter->module=='detail' && !$this->detailAdExpired) {
                    if ($this->detailAd[Classifieds::PICTURES]==''){
                        $this->set_ad(array('zone_4'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0'),
                        'zone_6'=>array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0'),
                        'zone_9'=>array('/1006833/MediumRectangle', 300, 250, 'div-gpt-ad-1344949423965-0')));
                    }else {
                        $this->set_ad(array('zone_4'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0'),
                        'zone_6'=>array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0'),
                        'zone_8'=>array('/1006833/Square', 250, 250, 'div-gpt-ad-1344947339993-0')));
                    }
                }else {
                    $this->set_ad(array('zone_4'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0'),
                    'zone_6'=>array('/1006833/MeduimRectangle', 300, 250, 'div-gpt-ad-1344944824543-0'),
                    'zone_7'=>array('/1006833/SearchTrailer',728,90,'div-gpt-ad-1334999893723-0')));                    
                }
            }
            if (count($this->urlRouter->cfg['campaign'])){
                $adKey=array();
                $adKey[]=  $this->urlRouter->countryId.'_x_x_x_x_';
                $adKey[]=  $this->urlRouter->countryId.'_'.$this->urlRouter->cityId.'_x_x_x_';
                $adKey[]=  $this->urlRouter->countryId.'_'.$this->urlRouter->cityId.'_'.$this->urlRouter->rootId.'_x_x_';
                $adKey[]=  $this->urlRouter->countryId.'_'.$this->urlRouter->cityId.'_'.$this->urlRouter->rootId.'_'.$this->urlRouter->sectionId.'_x_';
                $adKey[]=  $this->urlRouter->countryId.'_'.$this->urlRouter->cityId.'_'.$this->urlRouter->rootId.'_'.$this->urlRouter->sectionId.'_'.$this->urlRouter->purposeId.'_';
                $set=0;
                for($i=1;$i<5;$i++){
                    foreach ($adKey as $key){
                        if (isset($this->urlRouter->cfg['campaign'][$key.$i])){
                            $this->set_ad(array(
                                $this->urlRouter->cfg['campaign'][$key.$i][0]   =>  array(
                                        $this->urlRouter->cfg['campaign'][$key.$i][1], 
                                        $this->urlRouter->cfg['campaign'][$key.$i][3], 
                                        $this->urlRouter->cfg['campaign'][$key.$i][4],
                                        $this->urlRouter->cfg['campaign'][$key.$i][2]
                                    )
                                )
                            );
                            if ($i==1)unset($this->googleAds['Leaderboard']);
                            if ($i==2)unset($this->googleAds['SmallSquare']);
                            $set++;
                            break;
                        }
                    }
                    if ($set>3) break;
                }
            }
        }

        if ($this->urlRouter->rootId && isset($this->urlRouter->roots[$this->urlRouter->rootId]))
            $this->rootName=$this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex];
        else $this->urlRouter->rootId=0;

        if ($this->urlRouter->sectionId && isset($this->urlRouter->sections[$this->urlRouter->sectionId]))
            $this->sectionName=$this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
        else $this->urlRouter->sectionId=0;
        if ($this->urlRouter->purposeId && isset($this->urlRouter->purposes[$this->urlRouter->purposeId]))
            $this->purposeName=$this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex];

        if ($this->urlRouter->rootId==1 && $this->urlRouter->sectionId &&
                ($this->urlRouter->countryId==1 ||
                 $this->urlRouter->countryId==2 ||
                 $this->urlRouter->countryId==4 ||
                 $this->urlRouter->countryId==7 ||
                 $this->urlRouter->countryId==9)
                ) {
            
            $this->localities=$this->urlRouter->db->queryCacheResultSimpleArray(
                "locality_{$this->urlRouter->countryId}_{$this->urlRouter->sectionId}_{$this->urlRouter->siteLanguage}",
                "select s.id,s.name,s.search_terms,s.uri,s.city_id,s.parent_id,iif(g.id is null,0,g.id) as geo_parent_id, c.counter,c.unixtime 
                from geo_tag s 
                left join geo_tag g on g.city_id = s.parent_id and g.lang='{$this->urlRouter->siteLanguage}' 
                left join geo_tag_counts c on c.geo_tag_id=s.id and c.section_id = {$this->urlRouter->sectionId}
                where s.country_Id={$this->urlRouter->countryId}
                and c.section_id={$this->urlRouter->sectionId}
                and s.lang='{$this->urlRouter->siteLanguage}'
                and s.blocked=0
                and c.counter > 1
                order by s.name",
                null, 0, $this->urlRouter->cfg['ttl_long']);
                
            if ($this->urlRouter->cityId) {
                $found=false;
                foreach ($this->localities as $loc){
                    if($loc[4]==$this->urlRouter->cityId){
                        //$this->localityId=$loc[0];
                        //$found=true;
                        $this->forceNoIndex=true;
                        break;
                    }
                }
                //if (!$found) $this->localities=null;
            }

            //var_dump($this->localities);
            
            if (isset($this->urlRouter->params['loc_id'])) $this->localityId=$this->urlRouter->params['loc_id'];
            if (!($this->localities && $this->localityId && isset($this->localities[$this->localityId]))) $this->localityId=0;
            
        }elseif ($this->urlRouter->sectionId) {
            $this->extended=$this->urlRouter->db->queryCacheResultSimpleArray("tag_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->sectionId}_{$this->urlRouter->siteLanguage}",
                "select s.id,s.name,s.query_term,s.uri,c.counter,c.unixtime from section_tag s
                left join section_tag_counts c on c.section_tag_id=s.id and c.country_id = {$this->urlRouter->countryId} and c.city_id = {$this->urlRouter->cityId}
                where s.section_id={$this->urlRouter->sectionId} and s.lang='{$this->urlRouter->siteLanguage}' and s.blocked=0 and c.counter>1 order by s.name",
                null, 0, $this->urlRouter->cfg['ttl_long']);
        }
        $this->getBreadCrumb($this->urlRouter->module=='detail');
        $this->buildTitle();
        $this->execute(true);
        $this->render();
    }


    function mainMobile(){
        $this->resultsMobile();
        echo $this->fill_ad('Leaderboard','ad_m');
    }


    function side_pane(){
        if ($this->userFavorites && !$this->user->info['id']) {
            $this->renderSideSite();
        }else {
        $this->renderSideUserPanel();
        $this->renderSideRoots();
        $this->renderSideCountries();
        if ($this->urlRouter->module=='search') $this->renderSideLike();
        echo $this->fill_ad('zone_3', 'ad_s');
        }
    }

    function main_pane(){
        if ($this->userFavorites && !$this->user->info['id']) {
            $this->renderLoginPage();
        }else {
        $this->results();
        if ($this->urlRouter->cfg['enabled_sharing']) {?><div class='ms'></div><?php }
        }
    }

    function renderResults($keywords=''){
        if (!$this->userFavorites && $this->urlRouter->module!='detail') $this->updateUserSearchCriteria();
        $idx=0;

        $ad_keys = array_keys( $this->searchResults["matches"] );
        $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
        $ad_count = count($ad_keys);
        if ($ad_count>$this->num) $ad_count=$this->num;
        for ($ptr=0; $ptr<$ad_count; $ptr++) {
            $id=$ad_keys[$ptr];
            //$ad = (isset($ad_cache[$id])) ? reset($ad_cache[$id]) : reset($this->classifieds->getById($id));
            $ad = (isset($ad_cache[$id])) ? $ad_cache[$id] : $this->classifieds->getById($id);
            if (!empty($ad[Classifieds::ALT_TITLE])) {
                if ($this->urlRouter->siteLanguage=="en" && $ad[Classifieds::RTL]) {
                    $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                    $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                    $ad[Classifieds::RTL] = 0;
                } elseif ($this->urlRouter->siteLanguage=="ar" && $ad[Classifieds::RTL]==0) {
                    $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                    $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                    $ad[Classifieds::RTL] = 1;          
                }
            }
                    
            if ($ad[Classifieds::ID]){
                $pub_link = "<b>".$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex].'</b>';
                  
                $ad[Classifieds::CONTENT] = preg_replace('/www(?!\s+)\.(?!\s+).*(?!\s+)\.(?!\s+)(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)/', '', $ad[Classifieds::CONTENT]);
                       
                $feed=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', $keywords,array("limit"=>160));
                $feed[0]=trim($feed[0]);
                if (substr($feed[0],-3)=='...') {
                    $replaces=0;
                    $feed[0]=  preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/','$1'.($this->urlRouter->id==$ad[Classifieds::ID]?'': '<span class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</span>'),$feed[0],-1,$replaces);
                    if(!$replaces && $this->urlRouter->id!=$ad[Classifieds::ID]) $feed[0].='<span class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</span>';
                }
                $ad[Classifieds::CONTENT] = $feed[0];
                
                $itemScope='';
                $itemDesc='';
                $hasSchema=false;
                if ($ad[Classifieds::ROOT_ID]==1){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="http://schema.org/Product"';
                }elseif ($ad[Classifieds::ROOT_ID]==2){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="http://schema.org/Product"';
                }elseif ($ad[Classifieds::ROOT_ID]==3){
                    if ($ad[Classifieds::PURPOSE_ID]==3) {
                        $itemDesc='itemprop="description" ';
                        $itemScope=' itemscope itemtype="http://schema.org/JobPosting"';
                   }elseif ($ad[Classifieds::PURPOSE_ID]==4) {
                      $itemDesc='itemprop="description" ';
                      $itemScope=' itemscope itemtype="http://schema.org/Person"';
                    }
                }

                $isNewToUser=(isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit']<$ad[Classifieds::UNIXTIME]);
                $textClass='en';
                $liClass='';
                $newSpan='';
                $hasLink=true;
                if ($isNewToUser) {
                    $newSpan.="<span class='nw'></span>";
                    $hasLink=false;
                }
                if ($this->urlRouter->id==$ad[Classifieds::ID]){
                    $liClass.="on ";
                }
                if ($idx%2) {
                    $liClass.="alt ";
                }elseif ($idx==0) {
                    $liClass.="f ";
                }
                if ($ad[Classifieds::RTL]) {
                    $textClass="ar";
                }
                if ($liClass) $liClass="class='".trim($liClass)."'";

                $_link= sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage=='ar'?'':  $this->urlRouter->siteLanguage.'/'), $ad[Classifieds::ID]);
                ?><li itemprop="itemListElement" <?= $liClass.$itemScope ?>><a class='<?= $textClass ?>' href="<?= $_link ?>"><?= '<span '.$itemDesc.'>'.$newSpan.$ad[Classifieds::CONTENT].'</span>' ?><span class="<?= $this->urlRouter->siteLanguage ?>"><?= $pub_link . " <time st='".$ad[Classifieds::UNIXTIME]."'></time>" ?></span></a></li><?php
                $idx++;
            }
        }
    }

    function renderRSS($keywords='') {
        $idx=0;

        $ad_keys = array_keys( $this->searchResults["matches"] );
        $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
        $ad_count = count($ad_keys);
        if ($ad_count>$this->num) $ad_count=$this->num;
        
        for ($ptr=0; $ptr<$ad_count; $ptr++) {
            $id=$ad_keys[$ptr];

            $ad = (isset($ad_cache[$id])) ? $ad_cache[$id] : $this->classifieds->getById($id);
            if (!$ad[Classifieds::ID])
                continue;

            if (!empty($ad[Classifieds::ALT_TITLE])) {
                if ($this->urlRouter->siteLanguage=="en" && $ad[Classifieds::RTL]) {
                    $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                    $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                    $ad[Classifieds::RTL] = 0;
                } elseif ($this->urlRouter->siteLanguage=="ar" && $ad[Classifieds::RTL]==0) {
                    $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                    $ad[Classifieds::CONTENT] = $add[Classifieds::ALT_CONTENT];
                    $ad[Classifieds::RTL] = 1;
                }
            }

            $ad[Classifieds::CONTENT] = $this->shortText($ad);

        }
        
    }
    
    function shortText($ad) {
        $pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/i';
        $text = $ad[Classifieds::CONTENT];
        $text = preg_replace($pattern, "", $text);
        
        $pattern = "/[_a-z0-9-]+(\.[_a-z0-9-]+)*(\s|)@(\s|)[a-z0-9-]+(\.[a-z0-9-]+)*((\s|)\.(\s|)[a-z]{2,4})*(\s|$)/i";
        $text = preg_replace($pattern, "", $text);
        
        $pattern = "/\b(ت|للاتصال|للإتصال|تلفون|للمفاهمة|هاتف|للاستفسار)(:|)\b/";
        $text = preg_replace($pattern, "", $text);

        return text;
    }


    function alternateSearchMobile($keywords=""){
        $localityId=  $this->localityId;
        $extendedId= $this->extendedId;
        if ($this->extendedId || $this->localityId){
            $this->extendedId=0;
            $this->localityId=0;
        }elseif ($this->urlRouter->purposeId) {
            $this->urlRouter->purposeId=0;
            $this->purposeName="";
        }elseif ($this->urlRouter->sectionId) {
            $this->urlRouter->sectionId=0;
            $this->sectionName="";
        }elseif ($this->urlRouter->rootId) {
            $this->urlRouter->rootId=0;
            $this->rootName="";
        }elseif ($this->urlRouter->params['q']!='') {
            $keywords="";
            $this->urlRouter->params['q']='';
        }else {
            return false;
        }
        $this->urlRouter->params['start']=0;
        $this->execute(true);
        if ($this->searchResults['total_found']>0 && isset($this->searchResults['matches']) && count($this->searchResults['matches'])>0) {
            ?><!--googleoff: index--><?php
            if ($extendedId || $localityId) {
            //$this->setNotification($this->summerizeSearchMobile(true));
            echo $this->renderNotificationsMobile();
            }
            $this->filterPurposesMobile();
            ?><div class="hd"><?php echo $this->summerizeSearchMobile(true) ?></div><?php
            $sectionId=$this->urlRouter->sectionId;
            $rootId=$this->urlRouter->rootId;
            if (isset($this->user->params['search'])) {
                $this->urlRouter->sectionId=$this->user->params['search']['se'];
                $this->urlRouter->rootId=$this->user->params['search']['ro'];
            }
            $this->urlRouter->sectionId=$sectionId;
            $this->urlRouter->rootId=$rootId;
            ?><ul class='rb ls<?= count($this->searchResults['matches'])<5 ?'':' wpa' ?>'><?php
                $this->renderResults($keywords);
                ?></ul><?php
                echo $this->paginationMobile();
                ?><!--googleon: index--><?php
                if (isset($this->user->params['search'])) {
                    $this->urlRouter->rootId=$this->user->params['search']['ro'];
                    $this->urlRouter->sectionId=$this->user->params['search']['se'];
                    $this->urlRouter->purposeId=$this->user->params['search']['pu'];
                }
        }else {
            $this->alternateSearchMobile($keywords);
        }
        $this->localityId=$localityId;
        $this->extendedId=$extendedId;
    }

    function resultsMobile(){
        $keywords = "";
        if (isset($this->searchResults['words']))
            $keywords=implode(" ", array_keys($this->searchResults['words']) );

        if ($this->searchResults['total_found']>0 && isset($this->searchResults['matches']) && count($this->searchResults['matches'])>0) {
//            $this->setNotification($this->summerizeSearchMobile());
//            $this->renderNotificationsMobile();
            $this->filterPurposesMobile();
            ?><div class="hd"><?php echo $this->summerizeSearchMobile() ?></div><?php
            ?><ul itemscope itemtype="http://schema.org/ItemList" class='rb ls<?= count($this->searchResults['matches'])<5 ?'':' wpa' ?>'><?php
            $this->renderResults($keywords);
            ?></ul><?php
            echo $this->paginationMobile();
            
            
        }else {
            //if ($this->urlRouter->params['q']) {
            //    echo '<div class="hd na">',($this->lang['no_result_pre'].' <b>'.$this->get('q', 'filter').'</b> '.($this->urlRouter->sectionId ? $this->lang['in']:$this->lang['included']).' '.$this->sectionSummeryMobile(). ' ' .$this->lang['no_result_short']),'</div>';
                $this->setNotification($this->lang['no_listing'].' '.$this->sectionSummeryMobile());
            //}
            $this->alternateSearchMobile($keywords);
        }
        
        if ($this->urlRouter->rootId==1 && $this->urlRouter->countryId && count($this->localities)){   
                    $citiesList='';
                    $prefix_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                    $keyIndex=2;
                    $suffix_uri='/';
                    $prefix='';
                    $suffix='';
                    /*if ($this->hasCities && $this->urlRouter->cityId) {
                        $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        $keyIndex=3;
                    }*/
                    if ($this->urlRouter->sectionId){
                        $suffix_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                        $prefix=$this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex].' ';
                    }else{
                        $suffix_uri.=$this->urlRouter->pageRoots[$this->urlRouter->rootId][3].'/';
                        $prefix=$this->urlRouter->pageRoots[$this->urlRouter->rootId][$this->fieldNameIndex].' ';
                    }
                    if ($this->urlRouter->purposeId){
                        $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        switch ($this->urlRouter->purposeId){
                            case 1:
                            case 2:
                            case 8:
                                $prefix.=' '.$this->purposeName.' ';
                                break;
                            case 6:
                            case 7:
                                $prefix=$this->purposeName.' '.$prefix;
                                break;
                            default:
                                break;
                        }
                    }
                    if ($this->urlRouter->siteLanguage!='ar'){
                        $suffix_uri.=$this->urlRouter->siteLanguage.'/';
                    }
                    $prefix.=$this->lang['in'].' ';  
                    if ($this->searchResults['total_found']>5) {
                        foreach ($this->localities as $sub){
                            if ($sub[6]==$this->localityId) {
                                if ($this->localityId && ($sub[7]>=$this->localities[$this->localityId][7]-2 && $sub[7]<=$this->localities[$this->localityId][7])) continue;
                                $append='c-'.$sub[0].'-'.$keyIndex.'/';
                                $citiesList.='<li><a href="'.$prefix_uri.$sub[3].$suffix_uri.$append.'">'.$prefix.$sub[1].$suffix.'<span class="to"></span></a></li>';
                            }
                        }
                    }
                    if ($this->searchResults['total_found']>5 || $this->localityId) {
                        ?><h2 class="ctr"><?= $this->lang['suggestionLocation'].($this->localityId? $this->localities[$this->localityId][1].$this->lang['?']:  $this->urlRouter->countries[$this->urlRouter->countryId][$this->fieldNameIndex].$this->lang['?']) ?></h2><?php 
                        ?><ul class="ls"><?php
                        if ($this->localityId) {
                            ?><li><a href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId) ?>"><?= $prefix.$this->countryName ?><span class="to"></span></a></li><?php 
                            $localityId=$this->localityId;
                            $tmp=array();
                            while($this->localities[$localityId][5]) {
                                $localityId=$this->localities[$localityId][6];
                                $append='c-'.$this->localities[$localityId][0].'-'.$keyIndex.'/';
                                $tmp[]="<li><a href='".
                                $prefix_uri.$this->localities[$localityId][3].$suffix_uri.$append.
                                "'>".$prefix.$this->localities[$localityId][1].$suffix."<span class='to'></span></a></li>";
                            }
                            $k=count($tmp);
                            if ($k){
                                for($j=$k-1;$j>=0;$j--){
                                    echo $tmp[$j];
                                }
                            }
                            if ($this->urlRouter->module=='detail') {
                                $append='c-'.$this->localities[$this->localityId][0].'-'.$keyIndex.'/';
                                ?><li><a href="<?= $prefix_uri.$this->localities[$this->localityId][3].$suffix_uri.$append ?>"><?= $prefix.$this->localities[$this->localityId][1].$suffix ?><span class="to"></span></a></li><?php 
                            }else {
                                ?><li class="on"><b><?= $prefix.$this->localities[$this->localityId][1].$suffix ?></b></li><?php 
                            }
                            
                        }
                        echo  $citiesList;
                        ?></ul><?php  
                    }
                    
                }elseif ($this->urlRouter->sectionId && count($this->extended)) {
                    $prefix_uri='/';
                    if ($this->urlRouter->countryId) {
                        $prefix_uri.=$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                        $keyIndex=2;
                    } else
                        $keyIndex=1;
                    
                    
                    $suffix_uri='/';
                    $prefix='';
                    $suffix='';
                    
                    
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $keyIndex++;
                        $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    }
                    
                    $sectionName=  $this->sectionName;
                    if ($this->urlRouter->purposeId){
                        $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        switch ($this->urlRouter->purposeId){
                            case 1:
                            case 2:
                                $suffix=' '.$this->purposeName;
                                $sectionName.=' '.$this->purposeName;
                                break;
                            case 6:
                            case 7:
                            case 8:
                                $prefix=$this->purposeName.' ';
                                $sectionName.=$this->purposeName.' ';
                                break;
                            default:
                                break;
                        }
                    }
                    if ($this->urlRouter->siteLanguage!='ar'){
                        $suffix_uri.=$this->urlRouter->siteLanguage.'/';
                    }
                    ?><h2 class="ctr"><?= $this->lang['suggestion'] ?></h2><ul class="ls"><?php
                    if ($this->extendedId) {?><li><a href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId) ?>"><?= $sectionName ?><span class="to"></span></a></li><?php }
                        foreach ($this->extended as $sub){
                                $append='q-'.$sub[0].'-'.$keyIndex.'/';
                                if ($this->extendedId==$sub[0]) {
                                    ?><li class="on"><b><?= $prefix.$sub[1].$suffix ?></b></li><?php
                                }else {
                                    ?><li><a href="<?= $prefix_uri.$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$sub[3].$suffix_uri.$append ?>"><?= $prefix.$sub[1].$suffix ?><span class="to"></span></a></li><?php
                                }
                            }
                        ?></ul><?php
                }
    }

    function results(){
        $this->globalScript.='var sic=[];var llk=function(e){var e=e.parentNode.parentNode.firstChild.firstChild;if(e.href)document.location=e.href+"?map=on"};';
        if (isset($this->user->info['level']) && $this->user->info['level']==9) {
            $this->globalScript.='var rpa=function(id,e){e=$(e);if (!e.hasClass("loading")){e.addClass("loading");$.ajax({type:"POST",url:"/bin/ajax-report/",data:{id:id},dataType:"json",success:function(rp){if(rp.RP){e.click=function(){};e.css("background", "0");e.html("Done")}e.removeClass("loading")},error:function(rp){e.removeClass("loading")}})}};';
        }else {
            $this->globalScript.='var abf;var rpa=function(id,e){if(!abf){abf=$("<div class=\"lif rc\"><h4>'.$this->lang['abuseTitle'].'</h4><textarea></textarea><input type=\"button\" onclick=\"rpa_s(this)\" class=\"bt rc\" value=\"'.$this->lang['send'].'\" /><input type=\"button\" onclick=\"rpa_c(this)\" class=\"bt rc\" value=\"'.$this->lang['cancel'].'\" /></div>")};abf.attr("id",id);$(e.parentNode.parentNode).append(abf);};var rpa_c=function(e){e=$(e);var t=e.prev().prev();t.attr("value","");t.removeClass("err");e.parent().remove()};var rpa_s=function(e){e=$(e);var msg=e.prev().attr("value");if(msg.length>10){$.ajax({type:"POST",url:"/bin/ajax-report/",data:{id:e.parent().attr("id"),msg:msg},dataType:"json"});$(".ab",e.parent().parent()).remove();e.parent().replaceWith("<div class=\"lif nb rc\">'.$this->lang['abuseReported'].'</div>");e.prev().attr("value","")}else{e.prev().addClass("err")}};';
        }
        $this->inlineScript.='if(sic.length){$(".sim").each(function(i,e){$(e).replaceWith(sic[e.id])})};';
        if (!$this->userFavorites && $this->urlRouter->module!='detail') $this->updateUserSearchCriteria();
        
        ?><div class="rs"><?php

        echo '<!--googleoff: snippet-->';
            $this->filter_purpose();
                $keywords = '';
                if (isset($this->searchResults['words']))
                	$keywords=implode(' ', array_keys($this->searchResults['words']) );
            if ($this->searchResults['total_found']>0 && isset($this->searchResults['matches']) && count($this->searchResults['matches'])>0) {
                echo $this->summerizeSearch();
                echo $this->pagination();
                $this->renderSideSections();

        echo '<!--googleon: snippet-->';
        ?><ul class="ls"<?= $this->urlRouter->module=='detail' ? '' : ' itemprop="mainContentOfPage" ' ?>itemscope itemtype="http://schema.org/ItemList"><?php
                 echo '<meta itemprop="name" content="'.$this->subTitle.'" />';
                $idx=0;
                $startPtr=0;
                $lastAd=0;
                $lastLink=0;
                $ad_keys = array_keys( $this->searchResults["matches"] );
                $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
                $ad_count = count($ad_keys);
                if ($this->urlRouter->module=='detail') {
                    $prevAd=0;
                    $nextAd=0;
                    $prevPageAd=0;
                    $nextPageAd=0;
                    if ($this->urlRouter->params['start']>1){
                        $prevPageAd=$ad_keys[0];
                        $startPtr=1;
                    }
                    if ($ad_count>$this->num) {
                        $nextPageAd=$ad_keys[$ad_count-1];
                        $ad_count=$this->num;
                    }
                }
                for ($ptr=$startPtr; $ptr<$ad_count; $ptr++) {
                    $id=$ad_keys[$ptr];
                    //$ad = (isset($ad_cache[$id])) ? reset($ad_cache[$id]) : reset($this->classifieds->getById($id));
                    $ad = (isset($ad_cache[$id])) ? $ad_cache[$id] : $this->classifieds->getById($id);

                    $pic=null;
                    $this->appendLocation = true;

                    if (isset($ad[Classifieds::ID])){
                       if (!empty($ad[Classifieds::ALT_TITLE])) {
                            if ($this->urlRouter->siteLanguage=="en" && $ad[Classifieds::RTL]) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                               $ad[Classifieds::RTL] = 0;
                               $this->appendLocation = false;
                           } elseif ($this->urlRouter->siteLanguage=="ar" && $ad[Classifieds::RTL]==0) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                               $ad[Classifieds::RTL] = 1;                           
                               $this->appendLocation = false;
                           }       
                       }
                       $itemScope='';
                       $itemDesc='';
                       $hasSchema=false;
                       if ($ad[Classifieds::ROOT_ID]==1){
                           $hasSchema=true;
                           $itemDesc='itemprop="description" ';
                           $itemScope=' itemscope itemtype="http://schema.org/Product"';
                       }elseif ($ad[Classifieds::ROOT_ID]==2){
                           $hasSchema=true;
                           $itemDesc='itemprop="description" ';
                           $itemScope=' itemscope itemtype="http://schema.org/Product"';
                       }elseif ($ad[Classifieds::ROOT_ID]==3){
                           if ($ad[Classifieds::PURPOSE_ID]==3) {
                                $itemDesc='itemprop="description" ';
                                $itemScope=' itemscope itemtype="http://schema.org/JobPosting"';
                           }elseif ($ad[Classifieds::PURPOSE_ID]==4) {
                               $itemDesc='itemprop="description" ';
                               $itemScope=' itemscope itemtype="http://schema.org/Person"';
                           }
                       }

                       $_link= sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage=="ar"?"":"{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]);
                        if (!isset($this->user->params['search']) && $idx==0) $nextAd=$_link;

                        $phone='/(\+(?:[0-9] ?){6,14}[0-9])/';
                       if ($ad[Classifieds::PUBLICATION_ID]!=1)
                           $ad[Classifieds::CONTENT]=preg_replace($phone,'<span class="pn">$1</span>', $ad[Classifieds::CONTENT]);
                       /*
                        if (!empty($keywords)){
                            $feed=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), "mouftah", $keywords,array("limit"=>512));
                            $ad[Classifieds::CONTENT] = $feed[0];
                        }
                        */
                       
                       if (empty($ad[Classifieds::TITLE]))
                           $this->getAdTitle($ad);

                           
                        $adTitle = $ad[Classifieds::TITLE];
                        if (!empty($adTitle) && $adTitle[0]=='#') {
                            $adTitle=substr($adTitle, 1);
                        }

                       $l_inc=2;
                       $in='in';
                       if ($ad[Classifieds::RTL]) {
                           $l_inc=1;
                           $in="في";
                       }
                       $locIcon='';
                       $pub_link = $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
                       if ($ad[Classifieds::PUBLICATION_ID]==1) {
                           if($ad[Classifieds::LATITUDE] || $ad[Classifieds::LONGITUDE])
                            $locIcon="<span onclick='llk(this)' class='loc'></span>";
                        $pub_link='<span class="mj">'.$pub_link.'</span>';
                       }else {
                           if ($ad[Classifieds::OUTBOUND_LINK])
                               $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$ad[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                           elseif ($ad[Classifieds::PUBLICATION_ID])
                               $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";

                           if ($this->appendLocation) {
                               if ($this->urlRouter->countryId && !$this->hasCities) {
                                   $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                               }else {
                                   $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                               }
/*
                           if ($this->urlRouter->countryId && $this->urlRouter->cityId!=$ad[Classifieds::CITY_ID] && $this->hasCities &&
                                   $this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]!=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc]) 
                                    {
                               $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc];
                                   
                           }elseif (!$this->urlRouter->countryId || $this->urlRouter->countryId!=$ad[Classifieds::COUNTRY_ID]) {
                               $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                           }*/
                           }
                       }

                       $isNewToUser=(isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit']<$ad[Classifieds::UNIXTIME]);
                       $textClass="en";
                       $liClass="";
                       $newSpan="";
                       if ($isNewToUser) {
                        $newSpan.="<span class='nw'></span>";
                       }
                       $noIndex=false;
                       if ($this->urlRouter->module=='detail') {                           
                            if ($this->urlRouter->id==$ad[Classifieds::ID]){
                                $noIndex=true;
                               $liClass.="on ";
                               $prevAd=$lastLink;
                            }elseif($lastAd==$this->urlRouter->id){
                                $nextAd=$_link;
                            }
                       }
                       if ($idx%2) {
                           $liClass.="alt ";
                       }elseif ($idx==0) {
                           $liClass.="f ";
                       }
                       if ($ad[Classifieds::RTL]) {
                           $textClass="ar";
                           $adTitle=preg_replace("/(?:{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][3]}\s-\s)/", "", $adTitle);
                       }else {
                           $adTitle=preg_replace("/(?:{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][4]}\s-\s)/", "", $adTitle);
                       }
                       $lastLink=$_link;
                       if ($this->urlRouter->siteTranslate) $textClass='';
                       if ($ad[Classifieds::PICTURES]!=''){
                            $pic=preg_split('/\|/', $ad[Classifieds::PICTURES]);
                            $pic=$pic[0];
                            $this->globalScript.='sic['.$ad[Classifieds::ID].']="<a href=\"'. $_link .'\"><img class=\"'.$textClass.'\" src=\"'.$this->urlRouter->cfg['url_resources'].'/repos/s/'.$pic.'\" /></a>";';
                            $pic='<span id="'.$ad[Classifieds::ID].'" class="sim '.$textClass.'"></span>';
                           $liClass.='pic ';
                       }
                       if ($liClass) $liClass="class='".trim($liClass)."'";

                       $ad[Classifieds::CONTENT] = preg_replace('/www(?!\s+)\.(?!\s+).*(?!\s+)\.(?!\s+)(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)/', '', $ad[Classifieds::CONTENT]);
                        $feed=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', $keywords,array("limit"=>200));
                        $feed[0]=trim($feed[0]);
                        if (substr($feed[0],-3)=='...') {
                            $replaces=0;
                            $feed[0]=  preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/','$1'.($this->urlRouter->id==$ad[Classifieds::ID]?'': '<a href="'. $_link .'" class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</a>'),$feed[0],-1,$replaces);
                            if(!$replaces && $this->urlRouter->id!=$ad[Classifieds::ID]) $feed[0].='<a href="'. $_link .'" class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</a>';
                        }
                        $ad[Classifieds::CONTENT] = $feed[0];
                        
                       $_link='<a href="'. $_link .'">'.$newSpan.$adTitle .'</a>';
                       if ($this->user->info['id']) {
                           $class='fav';
                           if ($this->user->favorites) {
                                if (in_array($ad[Classifieds::ID],$this->user->favorites)) $class="fav on";
                           }
                            $favLink="<span onclick='fv({$ad[Classifieds::ID]},this)' class='{$class}'></span>";
                       }else {
                           $favLink="<a class='login' href=''><span onclick='fi({$ad[Classifieds::ID]})' class='fav'></span></a>";
                       }
                       if ($ad[Classifieds::HELD]){
                        $_link='<i>'.$newSpan.$adTitle.'</i>';
                       }
                      
                if ($noIndex) echo "<!--googleoff: index-->";
                ?><li itemprop="itemListElement" <?= $liClass.$itemScope ?>><?php
                    if ($pic){
                        echo $pic;
                    }
                    ?><h2 class='<?= $textClass ?>'><?= $_link ?></h2><p <?= $itemDesc ?>class='<?= $textClass ?>'><?= $ad[Classifieds::CONTENT] ?></p><span><?= $pub_link . ($this->urlRouter->siteTranslate ? '':" <b st='" .$ad[Classifieds::UNIXTIME]."'></b><span onclick='rpa({$ad[Classifieds::ID]},this)' class='ab'></span>".$favLink.$locIcon) ?></span><?= $this->getAdSection($ad, $hasSchema) ?></li><?php $idx++;
                   
                    
                if ($noIndex) echo "<!--googleon: index-->";
                   }

                   $lastAd=$ad[Classifieds::ID];
                } ?></ul><?php
                if ($this->searchResults['total_found']>10 && $this->urlRouter->rootId==1 && $this->urlRouter->countryId && count($this->localities)){
                    
                    $citiesList='';
                    $prefix_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                    $keyIndex=2;
                    $suffix_uri='/';
                    $prefix='';
                    $suffix='';
                    /*if ($this->hasCities && $this->urlRouter->cityId) {
                        $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        $keyIndex=3;
                    }*/
                    if ($this->urlRouter->sectionId){
                        $suffix_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                        $prefix=$this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex].' ';
                    }else{
                        $suffix_uri.=$this->urlRouter->pageRoots[$this->urlRouter->rootId][3].'/';
                        $prefix=$this->urlRouter->pageRoots[$this->urlRouter->rootId][$this->fieldNameIndex].' ';
                    }
                    if ($this->urlRouter->purposeId){
                        $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        switch ($this->urlRouter->purposeId){
                            case 1:
                            case 2:
                            case 8:
                                $prefix.=' '.$this->purposeName.' ';
                                break;
                            case 6:
                            case 7:
                                $prefix=$this->purposeName.' '.$prefix;
                                break;
                            default:
                                break;
                        }
                    }
                    if ($this->urlRouter->siteLanguage!='ar'){
                        $suffix_uri.=$this->urlRouter->siteLanguage.'/';
                    }
                    $prefix.=$this->lang['in'].' ';   

                    foreach ($this->localities as $sub){
                        if ($sub[6]==$this->localityId) {
                            if ($this->localityId && ($sub[7]>=$this->localities[$this->localityId][7]-2 && $sub[7]<=$this->localities[$this->localityId][7])) continue;
                            $append='c-'.$sub[0].'-'.$keyIndex.'/';
                            $citiesList.='<li><a href="'.$prefix_uri.$sub[3].$suffix_uri.$append.'">'.$prefix.$sub[1].$suffix.'</a></li>';
                        }
                    }

                    if ($citiesList) { ?><div class="sum rct xl xxl"><div class="brd"><h4><?= $this->lang['suggestionLocation'].($this->localityId? $this->localities[$this->localityId][1].$this->lang['?']:  $this->urlRouter->countries[$this->urlRouter->countryId][$this->fieldNameIndex].$this->lang['?']) ?></h4></div><ul><?= $citiesList ?></ul></div><?php }
                }elseif ($this->urlRouter->sectionId && count($this->extended)) {
                    $prefix_uri='/';
                    if ($this->urlRouter->countryId) {
                        $prefix_uri.=$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                        $keyIndex=2;
                    } else
                        $keyIndex=1;
                    
                    
                    $suffix_uri='/';
                    $prefix='';
                    $suffix='';
                    
                    
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $keyIndex++;
                        $prefix_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    }
                    
                    if ($this->urlRouter->purposeId){
                        $suffix_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        switch ($this->urlRouter->purposeId){
                            case 1:
                            case 2:
                                $suffix=' '.$this->purposeName;
                                break;
                            case 6:
                            case 7:
                            case 8:
                                $prefix=$this->purposeName.' ';
                                break;
                            default:
                                break;
                        }
                    }
                    if ($this->urlRouter->siteLanguage!='ar'){
                        $suffix_uri.=$this->urlRouter->siteLanguage.'/';
                    }
                    ?><div class="sum rct xl"><div class="brd"><h4><?= $this->lang['suggestion'] ?></h4></div><ul><?php
                        foreach ($this->extended as $sub){
                                $append='q-'.$sub[0].'-'.$keyIndex.'/';
                                if ($this->extendedId==$sub[0]) {
                                    ?><li class="on"><b><?= $prefix.$sub[1].$suffix ?></b></li><?php
                                }else {
                                    ?><li><a href="<?= $prefix_uri.$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$sub[3].$suffix_uri.$append ?>"><?= $prefix.$sub[1].$suffix ?></a></li><?php
                                }
                            }
                        ?></ul></div><?php
                }
                echo $this->pagination();
                if ($this->urlRouter->module=='detail') {
                    $sn='';
                    $sp='';
                    if (!$nextAd && $nextPageAd) {
                        $tmp = (isset($ad_cache[$nextPageAd])) ? $ad_cache[$nextPageAd] : $this->classifieds->getById($nextPageAd);
                        if (isset($tmp[Classifieds::ID])){
                        $nextAd=sprintf($tmp[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage=="ar"?"":"{$this->urlRouter->siteLanguage}/"), $tmp[Classifieds::ID]);
                        $sn='?p=next';
                        }else $nextAd=0;
                    }
                    if (!$prevAd && $prevPageAd) {
                        $tmp = (isset($ad_cache[$prevPageAd])) ? $ad_cache[$prevPageAd] : $this->classifieds->getById($prevPageAd);
                        if (isset($tmp[Classifieds::ID])){
                            $prevAd=sprintf($tmp[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage=="ar"?"":"{$this->urlRouter->siteLanguage}/"), $tmp[Classifieds::ID]);
                            $sp='?p=prev';
                        }else $prevAd=0;
                }
                    if ($nextAd || $prevAd) {
                        $this->inlineScript.='var mdd=$(".md");';
                        if($prevAd)$this->inlineScript.='$(".p",mdd).html("<a href=\''.$prevAd.$sp.'\'><span></span>'.$this->lang['prev_ad'].'</a>");';
                        if($nextAd)$this->inlineScript.='$(".n",mdd).html("<a href=\''.$nextAd.$sn.'\'>'.$this->lang['next_ad'].'<span></span></a>");';
                    }
                }
                if ($this->paginationString && $ad_count>9) echo $this->fill_ad('zone_7','tad');
            }else {
                if($this->userFavorites) {
                    echo $this->summerizeSearch();
                }else {
                echo "<!--googleoff: index-->";
                if ($this->urlRouter->params['q']){
                    echo "<div class='na rc'><p>".$this->lang['no_result_pre']." ".$this->crumbTitle." ".$this->lang['no_result']."</div>";
                }else{
                    if ($this->searchResults['total_found']==0) echo $this->summerizeSearch();
                }
                $this->alternate_search($keywords);
                echo "<!--googleon: index-->";
                }
            }
            ?></div><?php
    }


    function getAdSection($ad, $hasSchema=false){
        $section='';
        $hasLink=true;
        if ($this->urlRouter->purposeId && $this->urlRouter->sectionId) return '';
        if (!empty($this->urlRouter->sections)){
            $section=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][$this->fieldNameIndex];
            if ($this->extendedId)
                $section=$this->extended[$this->extendedId][1];
            switch($ad[Classifieds::PURPOSE_ID]){
                case 1:
                case 2:
                case 999:
                    if ($hasSchema)
                        $section='<span itemprop="name">'.$section.'</span> '.$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    else
                        $section=$section.' '.$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
                    break;
                case 6:
                case 7:
                    if ($hasSchema)
                        $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].' <span itemprop="name">'.$section.'</span>';
                    else
                        $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].' '.$section;
                    break;
                case 3:
                case 4:
                case 5:
                    $in=' ';
                    if ($this->urlRouter->siteLanguage=="en")$in=' '.$this->lang['in'].' ';
                    if ($hasSchema)
                        $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].$in.'<span itemprop="name">'.$section.'</span>';
                    else 
                        $section=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$this->fieldNameIndex].$in.$section;
                    break;
           }
           if ($this->localityId){
               $section.=' '.  $this->lang['in'].' '.$this->localities[$this->localityId][1].' '.$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$this->fieldNameIndex];
               if ($hasLink){
                   $idx=2;
                   $uri='/'.$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][3].'/';
                   /*if ($this->hasCities && isset($this->urlRouter->cities[$ad[Classifieds::CITY_ID]])) {
                        $idx=3;
                        $uri.=$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][3].'/';
                   }*/
                   $uri.=$this->localities[$this->localityId][3].'/';
                   $uri.=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][3].'/';
                   $uri.=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][3].'/';
                   $uri.=($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/').'c-'.  $this->localityId .'-'.$idx.'/';
                   $section='<a href="'.$uri.'">'.$section.'</a>';
               }
           }else {
                $countryId=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][0];
                
                $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}",
                             "select c.ID from city c
                             where c.country_id={$countryId} and c.blocked=0",
                             null, 0, $this->urlRouter->cfg['ttl_long']);

                $cityId=0;
                if (count($countryCities)>1 && isset($this->urlRouter->cities[$ad[Classifieds::CITY_ID]])){
                    $cityId=$ad[Classifieds::CITY_ID];
                    $section=$section.' '.$this->lang['in'].' '.$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$this->fieldNameIndex].' '.$this->urlRouter->countries[$countryId][$this->fieldNameIndex];
                }else {
                    $section=$section.' '.$this->lang['in'].' '.$this->urlRouter->countries[$countryId][$this->fieldNameIndex];
                }
                if ($hasLink) {
                    if ($this->extendedId) {
                        $idx=2;
                        $uri='/'.$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][3].'/';
                        if ($cityId) {
                            $idx=3;
                            $uri.=$this->urlRouter->cities[$cityId][3].'/';
                        }
                        $uri.=$this->urlRouter->sections[$ad[Classifieds::SECTION_ID]][3].'-'.$this->extended[$this->extendedId][3].'/';
                        $uri.=$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][3].'/';
                        $uri.=($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/').'q-'.  $this->extendedId .'-'.$idx.'/';
                        $section='<a href="'.$uri.'">'.$section.'</a>';
                    }else {
                        $section='<a href="'.$this->urlRouter->getURL($countryId,$cityId,$ad[Classifieds::ROOT_ID],$ad[Classifieds::SECTION_ID],$ad[Classifieds::PURPOSE_ID]).'">'.$section.'</a>';
                    }
                }
           }

           return '<span class="k">'.$section.'</span>';
       }
    }


    function alternate_search($keywords=""){
        if($this->searchResults['total_found']==0) {
            if ($this->extendedId || $this->localityId){
                $this->extendedId=0;
                $this->localityId=0;
            }elseif ($this->urlRouter->purposeId) {
                $this->urlRouter->purposeId=0;
                $this->purposeName="";
            }elseif ($this->urlRouter->sectionId) {
                $this->urlRouter->sectionId=0;
                $this->sectionName="";
            }elseif ($this->urlRouter->rootId) {
                $this->urlRouter->rootId=0;
                $this->rootName="";
            }else return false;
        }
        $this->urlRouter->params['start']=0;
        $this->execute(true);
        if ($this->searchResults['total_found']>0 && isset($this->searchResults['matches']) && count($this->searchResults['matches'])>0) {
            $this->updateUserSearchCriteria();
            echo $this->summerizeSearch(true);
            echo $this->pagination();
            $this->renderSideSections();
            ?><ul class="ls"><?php
                $idx=0;
                $ad_keys = array_keys( $this->searchResults["matches"] );
                $ad_cache = $this->urlRouter->db->getCache()->getMulti($ad_keys);
                $ad_count = count($ad_keys);
                if ($ad_count>$this->num) $ad_count=$this->num;
                for ($ptr=0; $ptr<$ad_count; $ptr++) {
                    $id=$ad_keys[$ptr];
                    $ad = (isset($ad_cache[$id])) ? $ad_cache[$id] : $this->classifieds->getById($id);

                    $pic=null;
                    $this->appendLocation = true;
                   if (isset($ad[Classifieds::ID])){
                      if (!empty($ad[Classifieds::ALT_TITLE])) {
                            if ($this->urlRouter->siteLanguage=="en" && $ad[Classifieds::RTL]) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                               $ad[Classifieds::RTL] = 0;
                               $this->appendLocation = false;
                           } elseif ($this->urlRouter->siteLanguage=="ar" && $ad[Classifieds::RTL]==0) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                               $ad[Classifieds::RTL] = 1;                           
                               $this->appendLocation = false;
                           }       
                       }


                       $phone='/(\+(?:[0-9] ?){6,14}[0-9])/';
                       if ($ad[Classifieds::PUBLICATION_ID]!=1) $ad[Classifieds::CONTENT]=preg_replace($phone,'<span class="pn">$1</span>', $ad[Classifieds::CONTENT]);

                        /*if (!empty($keywords)){
                            $feed=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), "mouftah", $keywords,array("limit"=>512));
                            $ad[Classifieds::CONTENT] = $feed[0];
                        }*/
                       
                        if (empty($ad[Classifieds::TITLE]))
                           $this->getAdTitle($ad);

                        $adTitle = $ad[Classifieds::TITLE];
                        if (!empty($adTitle) && $adTitle[0]=="#") {
                            $adTitle=substr($adTitle, 1);
                        }

                       $l_inc=2;
                       $in="in";
                       if ($ad[Classifieds::RTL]) {
                           $l_inc=1;
                           $in="في";
                       }
                       $locIcon='';
                       $pub_link = $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
                       if ($ad[Classifieds::PUBLICATION_ID]==1) {
                           if($ad[Classifieds::LATITUDE] || $ad[Classifieds::LONGITUDE]) 
                            $locIcon="<span onclick='llk(this)' class='loc'></span>";
                        $pub_link='<span class="mj">'.$pub_link.'</span>';
                       }else {
                       if ($ad[Classifieds::OUTBOUND_LINK])
                           $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$ad[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                       elseif ($ad[Classifieds::PUBLICATION_ID])
                           $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";

                           if ($this->appendLocation) {
                               if ($this->urlRouter->countryId && !$this->hasCities) {
                                   $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                               }else {
                                   $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                               }
                               /*
                           if ($this->urlRouter->countryId && $this->urlRouter->cityId!=$ad[Classifieds::CITY_ID] && $this->hasCities &&
                                   $this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]!=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc]) 
                                    {
                               $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc];
                                   
                           }elseif (!$this->urlRouter->countryId || $this->urlRouter->countryId!=$ad[Classifieds::COUNTRY_ID]) {
                               $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]." ".$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
                           }*/
                           }

//                       if ($this->urlRouter->countryId && (!$this->hasCities || !$this->urlRouter->cityId || ($this->urlRouter->cityId && $this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]!=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc]))) {
//                           $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc];
//                       }elseif (!$this->urlRouter->countryId) {
//                           $adTitle.=($ad[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".($this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]!=$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc]?$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$l_inc]." ":"").$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$l_inc];
//                       }
                       }

                       $isNewToUser=(isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit']<$ad[Classifieds::UNIXTIME]);
                       $textClass="en";
                       $liClass="";
                       $newSpan="";
                       if ($isNewToUser) {
                        $newSpan.="<span class='nw'></span>";
                       }
                       if ($this->urlRouter->id==$ad[Classifieds::ID]){
                           $liClass.="on ";
                       }
                       if ($idx%2) {
                           $liClass.="alt ";
                       }elseif ($idx==0) {
                           $liClass.="f ";
                       }
                       if ($ad[Classifieds::RTL]) {
                           $textClass="ar";
                           $adTitle=preg_replace("/(?:{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][3]}\s-\s)/", "", $adTitle);
                       }else {
                           $adTitle=preg_replace("/(?:{$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][4]}\s-\s)/", "", $adTitle);
                       }
                       if ($this->urlRouter->siteTranslate) $textClass='';
                       if ($this->user->info['id']) {
                           $class='fav';
                           if ($this->user->favorites) {
                                if (in_array($ad[Classifieds::ID],$this->user->favorites)) $class="fav on";
                           }
                            $favLink="<span onclick='fv({$ad[Classifieds::ID]},this)' class='{$class}'></span>";
                       }else {
                           $favLink="<a class='janrainEngage' href='#'><span onclick='fi({$ad[Classifieds::ID]})' class='fav'></span></a>";
                       }
                       $_link= sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage=="ar"?"":"{$this->urlRouter->siteLanguage}/"), $ad[Classifieds::ID]);
                       
                       $ad[Classifieds::CONTENT] = preg_replace('/www(?!\s+)\.(?!\s+).*(?!\s+)\.(?!\s+)(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)/', '', $ad[Classifieds::CONTENT]);
                        
                       $feed=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', $keywords,array("limit"=>200));
                        $feed[0]=trim($feed[0]);
                        if (substr($feed[0],-3)=='...') {
                            $replaces=0;
                            $feed[0]=  preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/','$1'.($this->urlRouter->id==$ad[Classifieds::ID]?'': '<a href="'. $_link .'" class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</a>'),$feed[0],-1,$replaces);
                            if(!$replaces && $this->urlRouter->id!=$ad[Classifieds::ID]) $feed[0].='<a href="'. $_link .'" class="lnk">'.($ad[Classifieds::RTL] ? $this->lang['readMore_ar']:$this->lang['readMore_en']).'</a>';
                        }
                        $ad[Classifieds::CONTENT] = $feed[0];
                       
                       if ($ad[Classifieds::PICTURES]!=''){
                            $pic=preg_split('/\|/', $ad[Classifieds::PICTURES]);
                            $pic=$pic[0];
                            $this->globalScript.='sic['.$ad[Classifieds::ID].']="<a href=\"'. $_link .'\"><img class=\"'.$textClass.'\" src=\"'.$this->urlRouter->cfg['url_resources'].'/repos/s/'.$pic.'\" /></a>";';
                            $pic='<span id="'.$ad[Classifieds::ID].'" class="sim '.$textClass.'"></span>';
                           $liClass.='pic ';
                       }
                       if ($liClass) $liClass="class='".trim($liClass)."'";
                ?><li <?= $liClass ?>><h2 class='<?= $textClass ?>'><a href="<?= $_link ?>"><?= $newSpan.$adTitle ?></a></h2><p class='<?= $textClass ?>'><?= $ad[Classifieds::CONTENT] ?></p><span><?= $pub_link . ($this->urlRouter->siteTranslate ? '':" <b st='" .$ad[Classifieds::UNIXTIME]."'></b><span onclick='rpa({$ad[Classifieds::ID]},this)' class='ab'></span>".$favLink.$locIcon) ?></span></li><?php $idx++;
                   }
                }?></ul><?php
                echo $this->pagination();
        }else {
            $purposeId=$this->urlRouter->purposeId;
            $sectionId=$this->urlRouter->sectionId;
            $rootId=$this->urlRouter->rootId;
            $q=$this->urlRouter->params['q'];

            $this->alternate_search($keywords);

            $this->urlRouter->purposeId=$purposeId;
            $this->urlRouter->sectionId=$sectionId;
            $this->urlRouter->rootId=$rootId;
            $this->urlRouter->params['q']=$q;
        }
    }

    function sectionSummeryMobile($forceRebuild=false){
        if ($this->breadString && !$forceRebuild) return $this->breadString;
        $bread="";
        if ($this->urlRouter->siteLanguage=="ar") {
            if ($this->urlRouter->purposeId) {
                $bread.= " {$this->purposeName}";
            }
            if ($this->urlRouter->rootId){
                if ($this->urlRouter->sectionId) {
                    $bread .= " {$this->sectionName}";
                    $bread .= " {$this->lang['included']} {$this->rootName}";
                }else {
                    $bread .= " {$this->rootName}";
                }
            }
            if ($this->urlRouter->countryId || $this->urlRouter->rootId) {
                if ($this->urlRouter->countryId) {
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $bread.= " {$this->cityName}، ";
                    }
                    $bread.= " {$this->countryName} ";
                }else {
                    $bread.= " {$this->countryName} ";
                }
            }else {
                $bread.=" {$this->lang['opt_all_countries']}";
            }
        }else {
            if ($this->urlRouter->rootId){
                if ($this->urlRouter->sectionId) {
                    $bread .= " {$this->sectionName}";
                    if ($this->urlRouter->purposeId) $bread.= " {$this->purposeName}";
                    $bread .= " {$this->lang['included']} {$this->rootName}";
                }else {
                    $bread .= " {$this->rootName}";
                    if ($this->urlRouter->purposeId) $bread.= " {$this->purposeName}";
                }
            }
            if ($this->urlRouter->countryId || $this->urlRouter->rootId) {
                if ($this->urlRouter->countryId) {
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $bread.= " {$this->cityName}, ";
                    }
                    $bread.= " {$this->countryName} ";
                }else {
                    $bread.= " {$this->countryName} ";
                }
            }else {
                $bread.=" {$this->lang['opt_all_countries']}";
            }
        }
        $this->breadString=$bread;
        return $this->breadString;
    }

    function summerizeSearchMobile($forceRebuild=false){
        $this->getBreadCrumb($forceRebuild);
        $count = $this->searchResults['total_found'];
        if ($count) {
        $formatted=number_format($count);
        $bread= "<b>";
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
        if ($this->urlRouter->params['q']) $bread.= " {$this->lang['for']} ".$this->urlRouter->params['q'];
        $bread.="</b> {$this->lang['in']} ";
        }else {
            $bread.=$this->lang['no_listing'];
        }
        //$bread .= $this->sectionSummeryMobile($forceRebuild);
        $bread .= $this->crumbTitle;
        return $bread;
    }

    function summerizeSearch($forceRebuild=false){
        $count = $this->searchResults['total_found'];
        if($this->userFavorites && $count==0) {
            $bread="<div class='sum fv rc m_b'>";
            $bread.=$this->getBreadCrumb($forceRebuild);
            $bread.=$this->lang['noFavorites'];
            $bread.='<p><a href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId).'">'.$this->lang['noFavBackLink'].'</a></p>';
            $bread.='</div>';
            return $bread;
        }
        if ($count) {
        $formatted=number_format($count);
        $rc=$count > $this->num ? "rct" :"rc";
        $bread="<div class='sum {$rc}'>";
        $bread.=$this->getBreadCrumb($forceRebuild);
        $bread.= "<b>";
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
        }else {
            $bread="<div class='sum rc e'>";
            $bread.=$this->getBreadCrumb();
            $bread.=$this->lang['no_listing'];
        }

        if ($this->urlRouter->params['q'])
            $bread.=' '.$this->lang['for'].' '.$this->crumbTitle;
        elseif ($this->userFavorites) {
            $bread.=' '.$this->lang['in'].' '. $this->lang['myFavorites'];
        } else $bread.=' '.$this->crumbTitle;
        if ($this->urlRouter->cfg['enabled_sharing'] && $this->urlRouter->module=='search')$bread.='<!--googleoff: snippet--><div class="tsh"><label>'.$this->lang['shareResults'].'</label><span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter"></span><span class="st_googleplus"></span><span class="st_linkedin"></span><span class="st_blogger"></span><span class="st_stumbleupon"></span><span class="st_sharethis"></span></div><!--googleon: snippet-->';
        //<span class="st_google_translate" displayText="Google Translate"></span>
        $bread .= '</div>';
        return $bread;
    }


    function updateUserSearchCriteria(){
        $time=time();
        if (isset($this->user->params['search']['time']) && ($time - $this->user->params['search']['time'] > 3)) {
            $this->user->params['search'] = array(
                'cn' =>   $this->urlRouter->countryId,
                'c' =>   $this->urlRouter->cityId,
                'ro' =>   $this->urlRouter->rootId,
                'se' =>   $this->urlRouter->sectionId,
                'pu' =>   $this->urlRouter->purposeId,
                'start'     =>  $this->urlRouter->params['start'],
                'q'     =>  $this->urlRouter->params['q'],
                'exId'     =>  $this->extendedId,
                'locId'     =>  $this->localityId,
                'time'  =>  $time
            );
            //if (isset ($this->urlRouter->params['tag_id'])) $this->user->params['search']['tag']=$this->urlRouter->params['tag_id'];
            $this->user->update();
        }
    }


    function renderSideSections(){
        if ($this->urlRouter->rootId) {
        $hasQuery=false;
        $q='';
        if ($this->urlRouter->params['q']) {
            $hasQuery=true;
            $q='?q='.urlencode($this->urlRouter->params['q']);
        }
            echo "<ul class='list l2'>";
            if (!$this->userFavorites && $this->urlRouter->module!='detail') echo '<li class="lad">',$this->fill_ad("zone_5",'ad_m'),'</li>';   
            $i=0;
            $tmp=count($this->urlRouter->pageSections)/2;
            $half=ceil($tmp);
            if ($half==$tmp) $half++;
            if ($hasQuery) {
                if ($i==0) echo '<li><ul class="l">';
                echo '<li>' ,$this->renderListLink($this->lang['opt_all_types'], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId).$q, $this->urlRouter->sectionId==0), '</li>';
                foreach ($this->urlRouter->pageSections as $section) {
                    if ($i==($half-1)) echo '</ul></li><li><ul class="l">';
                    $selected = ($this->urlRouter->sectionId == $section[0] ? true : false);
                    if ($this->extendedId || $this->localityId)$selected=false;
                    $purposeId=0;
                    if (is_numeric($section[3])) {
                        $purposeId=(int)$section[3];
                    }elseif ($this->urlRouter->purposeId) {
                        $pps=explode(',',$section[3]);
                        if ($pps && in_array($this->urlRouter->purposeId,$pps)) {
                            $purposeId=$this->urlRouter->purposeId;
                        }
                    }
                    echo '<li>', $this->renderListLink($this->urlRouter->sections[$section[0]][$this->fieldNameIndex], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$section[0],$purposeId).$q, $selected), '</li>';
                    $i++;
                }
                if ($i%2==0) echo '<li>&nbsp;</li>';
                echo '</ul></li>';
            }else {
                if ($i==0) echo '<li><ul class="l">';
                echo '<li>' ,$this->renderListLink($this->lang['opt_all_types'], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId).$q, $this->urlRouter->sectionId==0), '</li>';
                foreach ($this->urlRouter->pageSections as $section) {
                    if ($i==($half-1)) echo '</ul></li><li><ul class="l">';
                    $selected = $this->urlRouter->sectionId == $section[0] ? true : false;
                    if ($this->extendedId || $this->localityId)$selected=false;
                    $purposeId=0;
                    if (is_numeric($section[3])) {
                        $purposeId=(int)$section[3];
                    }elseif ($this->urlRouter->purposeId) {
                        $pps=explode(',',$section[3]);
                        if ($pps && in_array($this->urlRouter->purposeId,$pps)) {
                            $purposeId=$this->urlRouter->purposeId;
                        }
                    }
                    $isNew=false;
                    if (!$selected && $this->checkNewUserContent($section[2])) $isNew=true;
                    echo '<li',($isNew?' class="nl">':'>'),
                    $this->renderListLink($this->urlRouter->sections[$section[0]][$this->fieldNameIndex].' <b>('.$section[1].')</b>', $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$section[0],$purposeId), $selected), '</li>';
                    $i++;
                }
                if ($i%2==0) echo '<li>&nbsp;</li>';
                echo '</ul></li>';
            }
            if (!$this->userFavorites) echo '<li class="lad">',$this->fill_ad("zone_6",'ad_m'),'</li>';
            echo '</ul>';
        }elseif(!$this->userFavorites) {           
            echo '<ul class="list">';
            //echo '<li class="lad">',$this->fill_ad("zone_5",'ad_s'),'</li>';
            echo '<li class="lad">',$this->fill_ad("zone_6",'ad_m'),'</li>';
            echo '</ul>';
        }
    }

    function filterPurposesMobile(){
        if ($this->urlRouter->rootId!=4 && ($this->urlRouter->rootId || $this->urlRouter->sectionId) && count($this->urlRouter->purposes)>1){
            $q='';
            $i=0;
            $hasQuery=false;
            if ($this->urlRouter->params['q']) {
                $hasQuery=true;
                $q='?q='.urlencode($this->urlRouter->params['q']);
            }
            echo "<ul class='ft'>";
            if ($hasQuery) {
                foreach ($this->urlRouter->pagePurposes as $purpose) {
                    if ($purpose[1]) {
                        if ($this->urlRouter->purposeId==$purpose[0])
                            echo '<li><span class="bt rc">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].($q ? '' :" ({$purpose[1]})"),'</span></li>';
                        else echo '<li><a class="bt rc" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$purpose[0]).$q.'">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].($q ? '' :" ({$purpose[1]})"),'</a></li>';
                    }
                }
            }else {
                $append_uri='';
                $extended_uri='';
                if ($this->extendedId){
                    $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1);
                    $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                    if ($this->hasCities && $this->urlRouter->cityId) {
                        $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    }
                    $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId][3].'/';
                }elseif($this->localityId){
                    $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2);
                    $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                    $extended_uri.=$this->localities[$this->localityId][3].'/';
                    $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                }
                foreach ($this->urlRouter->pagePurposes as $purpose) {
                    if ((int)$purpose[1]>0) {
                        $selected=($this->urlRouter->purposeId==$purpose[0]);
                        if ($this->extendedId || $this->localityId) {
                            if ($selected) {
                                echo '<li><span class="bt rc">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex],'</span></li>';
                            }else {
                                echo '<li><a class="bt rc" href="'.$extended_uri.$this->urlRouter->purposes[$purpose[0]][3].$append_uri.'/">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex],'</a></li>';
                            }
                        }else {
                            if ($selected)
                                echo '<li><span class="bt rc">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].($q ? '' :" ({$purpose[1]})"),'</span></li>';
                            else echo '<li><a class="bt rc" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$purpose[0]).$q.'">',$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].($q ? '' :" ({$purpose[1]})"),'</a></li>';
                        }
                        $i++;
                    }
                }
            }
            echo '</ul>';
        }
    }

    function filter_purpose(){
        if (!$this->userFavorites) {
        if ($this->urlRouter->rootId!=4 && count($this->urlRouter->purposes)>0){
            echo "<ul class='tbs'>";
            $i=0;
            $hasQuery=false;
            $q="";
            if ($this->urlRouter->params['q']) {
                $hasQuery=true;
                $q='?q='.urlencode($this->urlRouter->params['q']);
            }

            if ($hasQuery) {
                foreach ($this->urlRouter->pagePurposes as $purpose) {
                    if ((int)$purpose[1]>0) {
                    $selected=($this->urlRouter->purposeId==$purpose[0]);
                    echo "<li>".$this->renderListLink($this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,
                        $this->urlRouter->sectionId,$purpose[0]).$q, $selected)."</li>";
                    $i++;
                    }
                }
            }else {
                if ($this->urlRouter->pagePurposes) {
                    $append_uri='';
                    $extended_uri='';
                    if ($this->extendedId){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1);
                        $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                        if ($this->hasCities && $this->urlRouter->cityId) {
                            $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        }
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId][3].'/';
                        //echo "<b>", $extended_uri, "</b></br>";
                        
                    }elseif($this->localityId){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2);
                        $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                        /*if ($this->hasCities && $this->urlRouter->cityId) {
                            $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        }*/
                        $extended_uri.=$this->localities[$this->localityId][3].'/';
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                    }

                    foreach ($this->urlRouter->pagePurposes as $purpose) {
                        if ((int)$purpose[1]>0) {
                            $isNew=false;
                            $selected=($this->urlRouter->purposeId==$purpose[0]);
                            if ($this->extendedId || $this->localityId) {
                                echo "<li>".
                                $this->renderListLink($this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex] .
                                    " <span>(" . $purpose[1] . ")</span>",
                                    $extended_uri.$this->urlRouter->purposes[$purpose[0]][3].$append_uri.'/', $selected)."</li>";
                                //echo $extended_uri, $this->urlRouter->purposes[$purpose[0]][3], $append_uri, "<br />";
                            }else {
                                if (!$selected && $this->checkNewUserContent($purpose[2])) $isNew=true;
                                echo "<li".($isNew?" class='nl'":"").">".
                                $this->renderListLink($this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex] .
                                    " <span>(" . $purpose[1] . ")</span>",
                                $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,
                                $this->urlRouter->sectionId,$purpose[0]), $selected)."</li>";
                            }
                            $i++;
                        }
                    }
                }
            }
            echo "</ul>";
        }
        }
    }

    function getBreadCrumb($forceSetting=false) {
        if ($this->crumbString && !$forceSetting) return $this->crumbString;
        if (!$forceSetting || $this->urlRouter->module=='detail'){
            if(isset ($this->urlRouter->params['tag_id'])  && !$this->urlRouter->params['q'] && isset($this->extended[$this->urlRouter->params['tag_id']])){
                    $this->extendedId=$this->urlRouter->params['tag_id'];
            }
        }
        $q="";
        $subPurpose='';
        if ($this->extendedId) {
            $append_uri=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId? ($this->hasCities && $this->urlRouter->cityId ? 3:2):1);
            if ($this->urlRouter->countryId)
                $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
            else $extended_uri='/';
            $this->title=$this->extended[$this->extendedId][1];
            if ($this->urlRouter->purposeId){
                switch ($this->urlRouter->purposeId){
                    case 1:
                    case 2:
                    case 8:
                        $subPurpose=  $this->sectionName.' '.  $this->purposeName;
                        $this->title.=' '.$this->purposeName;
                        break;
                    case 6:
                    case 7:
                    case 999:
                        $subPurpose=  $this->purposeName.' '.$this->sectionName;
                        $this->title=$this->purposeName.' '.$this->title;
                        break;
                    default:
                        break;
                }
            }
            $sub_title=$this->title;
            if ($this->urlRouter->countryId)
                $this->title.=' '.$this->lang['in'].' ';

            if ($this->urlRouter->countryId && $this->hasCities && $this->urlRouter->cityId) {
                $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                $this->title.=$this->urlRouter->cities[$this->urlRouter->cityId][$this->fieldNameIndex].' ';
            }

            $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId][3].'/';
            if ($this->urlRouter->purposeId)
                $extended_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
            $extended_uri.=$append_uri.'/';
            $this->extended_uri=$extended_uri;
            if ($this->urlRouter->countryId)
            $this->title.=$this->urlRouter->countries[$this->urlRouter->countryId][$this->fieldNameIndex];

        }elseif($this->localityId){
            $prefix_parent_name='';
            $suffix_parent_uri='';
            $prefix_append_uri='';
            $suffix_append_uri='';
            $prefix_append_uri=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-';
            $append_uri=$prefix_append_uri.$this->localityId.'-';
            $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
            
            $keyIndex=2;
            /*if ($this->hasCities && $this->urlRouter->cityId) {
                $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                $keyIndex=3;
            }*/
            $append_uri.=$keyIndex;
            $suffix_append_uri='-'.$keyIndex.'/';
            $prefix_parent_uri=$extended_uri;
            
            $extended_uri.=$this->localities[$this->localityId][3].'/';
            $this->title=$this->localities[$this->localityId][1];
            if ($this->urlRouter->sectionId){
                $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                $suffix_parent_uri='/'.$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                $this->title=$this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex];
            }else{
                $extended_uri.=$this->urlRouter->pageRoots[$this->urlRouter->rootId][3].'/';
                $suffix_parent_uri='/'.$this->urlRouter->pageRoots[$this->urlRouter->rootId][3].'/';
                $this->title=$this->urlRouter->pageRoots[$this->urlRouter->rootId][$this->fieldNameIndex];
            }
            if ($this->urlRouter->purposeId){
                $extended_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                $suffix_parent_uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                switch ($this->urlRouter->purposeId){
                    case 1:
                    case 2:
                    case 8:
                        $this->title.=' '.$this->purposeName;
                        break;
                    case 6:
                    case 7:
                    case 999:
                        $this->title=$this->purposeName.' '.$this->title;
                        break;
                    default:
                        break;
                }
                $subPurpose=  $this->title;
            }
            $extended_uri.=$append_uri.'/';
            $this->extended_uri=$extended_uri;
            $prefix_parent_name=  $this->title.' '.$this->lang['in'].' ';
            $this->title.=' '.$this->lang['in'].' '.$this->localities[$this->localityId][1];
            $sub_title=$this->title;
            
        }else {
            if ($forceSetting) {
                $uri = rtrim($this->urlRouter->getURL($this->urlRouter->countryId,
                        $this->urlRouter->cityId, $this->urlRouter->rootId,
                        $this->urlRouter->sectionId, $this->urlRouter->purposeId, false), '/');
                $str = dba_fetch ($uri, $this->urlRouter->dbm);
                if ($str) {
                    $rs= explode('|', $str);
                    $raw_title = explode(chr(9), $rs[5]);
                    $this->urlRouter->pageTitle['ar'] = $raw_title[0];
                    $this->urlRouter->pageTitle['en'] = $raw_title[1];
                    $this->title = $this->urlRouter->pageTitle[$this->urlRouter->siteLanguage];
                }
            }
            
            if ($this->urlRouter->pageTitle[$this->urlRouter->siteLanguage]=='') {
                $this->title=$this->getDynamicTitle($forceSetting);
            }else {
                $this->title=preg_replace('/'.$this->lang['mourjan'].'\s/i', '', $this->title);
            }
            
            $pos = strrpos($this->title, ' '.$this->lang['in'].' ');
            $sub_title = (empty($pos) ? $this->title : substr($this->title, 0, $pos));
            
        /*if ($this->title==$this->lang['title_full']) {
            $this->title=$this->lang['mourjan'];
        }*/
        }
        $bc = array();

        if ($this->urlRouter->params['q']) {
            $q=htmlspecialchars($this->urlRouter->params['q'],ENT_QUOTES);
        }elseif ($this->urlRouter->force_search) {
            $q=$this->lang['search_general'];
        }

        $countryId=$this->urlRouter->countryId;
        $countryName=$this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }

        $bc[] = "<div class='brd' itemprop='breadcrumb'><a href='".$this->urlRouter->getURL($countryId)."'>{$countryName}</a>";

        if ($this->hasCities && $this->urlRouter->cityId) {
            $bc[]="<a href='".
                    $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId).
                    "'>{$this->cityName}</a>";
        }

        if ($this->userFavorites) {
            $bc[]="<h1>".$this->lang['myFavorites']."</h1>";
        }else {
            if ($this->urlRouter->rootId) {

                $purposeId=isset($this->urlRouter->pageRoots[$this->urlRouter->rootId]) ? $this->urlRouter->pageRoots[$this->urlRouter->rootId][3] : 0;
                if (is_numeric($purposeId)) $purposeId=(int)$purposeId+0;
                else $purposeId=0;
                if ( ($q || $this->urlRouter->purposeId || $this->urlRouter->sectionId) && !($this->urlRouter->sectionId==0 && $purposeId) ) {
                    $bc[]="<a href='".
                            $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, 0 , $purposeId).
                            "'>{$this->rootName}</a>";
                }
                if ($this->urlRouter->sectionId) {
                    if (array_key_exists($this->urlRouter->sectionId, $this->urlRouter->pageSections)) {
                        $purposeId = $this->urlRouter->pageSections[$this->urlRouter->sectionId][3];
                        if (is_numeric($purposeId)) $purposeId=(int)$purposeId;
                        else $purposeId=0;
                    } else {
                        $purposeId=0;
                    }
                    if ($this->extendedId || $this->localityId || (($q || $this->urlRouter->purposeId) && !$purposeId)) {
                        $bc[]="<a href='".
                                $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $purposeId).
                                "'>{$this->sectionName}</a>";
                    }
                }
            }


            if ($this->urlRouter->purposeId && $q){
                $bc[]="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId)}'>".
                        $sub_title."</a>";
            }


            if ($q) {
                if ($forceSetting || $this->urlRouter->module=="detail") {
                    $qStr='';
                    if ($this->urlRouter->params['q']) $qStr='?q='.urlencode($this->urlRouter->params['q']);
                    $bc[]="<a href='".
                            $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).$qStr.
                            "'>".$q.' '.$this->lang['in'].' '.$sub_title."</a>";
                }else {
                    if ($this->urlRouter->rootId || $this->urlRouter->sectionId || $this->urlRouter->purposeId) {
                        $bc[]="<h1 itemprop='headline'>".$q." ".$this->lang['in']." ".$sub_title."</h1>";
                        $this->title=$q.' '.$this->lang['in'].' '.$this->title;
                    }else {
                        $bc[]="<h1 itemprop='headline'>".$q."</h1>";
                        $this->title=$q.' '.$this->lang['in'].' '.$this->title;
                    }
                }
            }else {
                if ($subPurpose && ($this->localityId || $this->extendedId) ) {
                    $bc[]="<a href='".
                            $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).
                            "'>".$subPurpose."</a>";
                }
                if ($forceSetting || $this->urlRouter->module=="detail") {
                    if ($this->extendedId || $this->localityId) {
                        if ($this->localityId){
                            $localityId=$this->localityId;
                            $tmp=array();
                            while($this->localities[$localityId][5]) {
                                $localityId=$this->localities[$localityId][6];
                                $tmp[]="<a href='".
                                $prefix_parent_uri.$this->localities[$localityId][3].$suffix_parent_uri.$prefix_append_uri.$this->localities[$localityId][0].$suffix_append_uri.
                                "'>".$prefix_parent_name.$this->localities[$localityId][1]."</a>";
                            }
                            $k=count($tmp);
                            if ($k){
                                for($j=$k-1;$j>=0;$j--){
                                    $bc[]=$tmp[$j];
                                }
                            }
                        }
                        $bc[]="<a href='".
                            $extended_uri.
                            "'>".$sub_title."</a>";
                    }else {
                        $bc[]="<a href='".
                            $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).
                            "'>".$sub_title."</a>";
                    }

                } else {
                    if ($this->localityId){
                        $localityId=$this->localityId;
                        $tmp=array();
                        while($this->localities[$localityId][5]) {
                            $localityId=$this->localities[$localityId][6];
                            $tmp[]="<a href='".
                            $prefix_parent_uri.$this->localities[$localityId][3].$suffix_parent_uri.$prefix_append_uri.$this->localities[$localityId][0].$suffix_append_uri.
                            "'>".$prefix_parent_name.$this->localities[$localityId][1]."</a>";
                        }
                        $k=count($tmp);
                        if ($k){
                            for($j=$k-1;$j>=0;$j--){
                                $bc[]=$tmp[$j];
                            }
                        }
                    }
                    $bc[] = '<h1 itemprop="headline name">'.$sub_title.'</h1>';
                }
                $this->subTitle=$sub_title;
            }
        }

        $bread =  implode("<span>{$this->lang['sep']}</span>", $bc).'</div>';

        $this->crumbTitle = $this->title;
        return $this->crumbString=$bread;
    }

    function getDynamicTitle($forceSetting=false){
        if ($this->dynamicTitle && !$forceSetting) return $this->dynamicTitle;
        $current="";
        $last="";
        $summery="";
        $location="";
        $q="";
        $appendLocation=false;
        if ($this->urlRouter->params['q']) {
            $q=htmlspecialchars($this->urlRouter->params['q'],ENT_QUOTES);
        }elseif ($this->urlRouter->force_search) {
            $q=$this->lang['search_general'];
        }
        $countryId=$this->urlRouter->countryId;
        $countryName=$this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        $location=$current=$this->countryName;
        if ($this->hasCities && $this->urlRouter->cityId) {
            $last=$current;
            $current=$this->cityName;
            $location=$this->cityName." ".$location;
        }
        if ($this->userFavorites) {
            $summery=$current=$this->lang['myFavorites'];
        }else {
            $defPurpose=count($this->urlRouter->purposes)>1 ? false : true;
            if ($this->urlRouter->rootId) {
                $last=$current;
                $summery=$current=$this->rootName;
                
                if ($this->urlRouter->sectionId) {
                    $last=$current;
                    $summery=$current=$this->sectionName;
                }
                $appendLocation=true;
            }

            if ($this->urlRouter->purposeId){
                if ($this->urlRouter->rootId || $this->urlRouter->sectionId) {
                    switch($this->urlRouter->purposeId){
                        case 1:
                        case 2:
                        case 999:
                            $last=$current;
                            $summery=$current=$current." ".$this->purposeName;
                            break;
                        case 6:
                        case 7:
                            $last=$current;
                            $summery=$current=$this->purposeName." ".$current;
                            break;
                        case 3:
                            $in="";
                            if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";

                            if ($this->urlRouter->sectionId) {
                                $last=$current;
                                if ($this->urlRouter->siteLanguage=="ar")
                                    $summery=$current= 'مطلوب ' .$current;
                                else
                                    $summery=$current= $current.' '. $this->purposeName;
                            }else {
                                $last=$current;
                                $summery=$current=$this->purposeName;
                            }

                            break;
                        case 4:

                        case 5:
                            $in="";
                            if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                            if ($this->urlRouter->sectionId) {
                                $last=$current;
                                $summery=$current=$this->purposeName.$in." ".$current;
                            }else {
                                $last=$current;
                                $summery=$current=$this->purposeName;
                            }
                            break;
                    }
                }else {
                    $last=$current;
                    $summery=$current=$this->purposeName." ".$this->lang['in']." ".$current;
                }
            }else {
                $appendLocation=true;
            }
            if ($last=="" || $last==$current) $current=$this->lang['search_general'];
            if ($q) {
                if ($summery) {
                    $summery=$q." ".$this->lang['in']." ".$summery;
                }else {
                    $summery=$q;
                }
            }
        }

        if(!$summery) $summery=$this->lang['search_general'];
        if ($this->userFavorites) {
            $this->dynamicTitle=$summery;
        }else {
            if ($appendLocation) {
                $this->dynamicTitle=$summery." ".$this->lang['in']." ".$location;
            }else {
                $this->dynamicTitle=$summery;
            }
        }
        return $this->dynamicTitle;
    }

    function getBreadCrumb1($forceSetting=false){
        if ($this->crumbString && !$forceSetting) return $this->crumbString;
        $current="";
        $last="";
        $summery="";
        $location="";
        $q="";
        $bread="";
        $appendLocation=false;
        if ($this->urlRouter->params['q']) {
            $q=htmlspecialchars($this->urlRouter->params['q'],ENT_QUOTES);
        }elseif ($this->urlRouter->force_search) {
            $q=$this->lang['search_general'];
        }
        $countryId=$this->urlRouter->countryId;
        $countryName=$this->countryName;
        if ($this->userFavorites && $this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        $bread.="<div class='brd'><a href='".$this->urlRouter->getURL($countryId)."'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        $location=$current=$this->countryName;
        if ($this->hasCities && $this->urlRouter->cityId) {
            $bread.="<a href='".$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId)."'>{$this->cityName}</a> <span>{$this->lang['sep']}</span> ";
            $last=$current;
            $current=$this->cityName;
            $location=$this->cityName." ".$location;
        }
        if ($this->userFavorites) {
            $bread.="<h1>".$this->lang['myFavorites']."</h1>";
            $summery=$current=$this->lang['myFavorites'];
        }else {
            $defPurpose=count($this->urlRouter->purposes)>1 ? false : true;
            if ($this->urlRouter->rootId) {
                if ( ($q || $this->urlRouter->purposeId || $this->urlRouter->sectionId) && !($this->urlRouter->sectionId==0 && $defPurpose) ) {
                    $bread.="<a href='".$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId)."'>{$this->rootName}</a> <span>{$this->lang['sep']}</span> ";
                    $last=$current;
                    $summery=$current=$this->rootName;
                }else {
                    $last=$current;
                    $summery=$current=$this->rootName;
                }
                if ($this->urlRouter->sectionId) {
                    if (($q || $this->urlRouter->purposeId) && !$defPurpose) {
                        $bread.="<a href='".$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId)."'>{$this->sectionName}</a> <span>{$this->lang['sep']}</span> ";
                        $last=$current;
                        $summery=$current=$this->sectionName;
                    }else {
                        $last=$current;
                        $summery=$current=$this->sectionName;
                    }
                }
                $appendLocation=true;
            }

            if ($this->urlRouter->purposeId){
                if ($this->urlRouter->rootId || $this->urlRouter->sectionId) {
                    switch($this->urlRouter->purposeId){
                        case 1:
                        case 2:
                        case 999:
                            $last=$current;
                            $summery=$current=$current." ".$this->purposeName;
                            break;
                        case 6:
                        case 7:
                            $last=$current;
                            $summery=$current=$this->purposeName." ".$current;
                            break;
                        case 3:
                            $in="";
                            if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";

                            if ($this->urlRouter->sectionId) {
                                $last=$current;
                                if ($this->urlRouter->siteLanguage=="ar")
                                    $summery=$current= 'مطلوب ' .$current;
                                else
                                    $summery=$current= $current.' '. $this->purposeName;
                                //$summery=$current=$this->purposeName.$in." ".$current;
                            }else {
                                $last=$current;
                                $summery=$current=$this->purposeName;
                            }

                            break;
                        case 4:

                        case 5:
                            $in="";
                            if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                            if ($this->urlRouter->sectionId) {
                                $last=$current;
                                $summery=$current=$this->purposeName.$in." ".$current;
                            }else {
                                $last=$current;
                                $summery=$current=$this->purposeName;
                            }
                            break;
                    }
                }else {
                    if ($q) {
                        $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId)}'>".$this->purposeName." ".$this->lang['in']." ".$current."</a> <span>{$this->lang['sep']}</span>";
                        $last=$current;
                        $summery=$current=$this->purposeName." ".$this->lang['in']." ".$current;
                    }else {
                        $last=$current;
                        $summery=$current=$this->purposeName." ".$this->lang['in']." ".$current;
                    }
                }
            }else {
                $appendLocation=true;
            }
            if ($last=="" || $last==$current) $current=$this->lang['search_general'];
            if ($q) {
                if ($current && $current!=$this->lang['search_general']) {
                    $current="<h1>".$q." ".$this->lang['in']." ".$current."</h1>";
                }else {
                    $current=$q;
                }
                if ($summery) {
                    $summery=$q." ".$this->lang['in']." ".$summery;
                }else {
                    $summery=$q;
                }
                if ($forceSetting || $this->urlRouter->module=="detail") {
                    $qStr='?q='.urlencode($this->urlRouter->params['q']);
                    $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId)}.$qStr'>".$current."</a>";
                }else {
                    $bread.="<h1>".$current."</h1>";
                }
            }else {
                if ($forceSetting || $this->urlRouter->module=="detail" && $current!=$this->lang['search_general']) {
                    $bread.="<a href='{$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId)}'>".$current."</a>";
                }else {
                    $bread.="<h1>".$current."</h1>";
                }
            }
        }

        $bread.="</div>";
        if(!$summery) $summery=$this->lang['search_general'];
        if ($this->userFavorites) {
            $this->title=$this->crumbTitle=$summery;
        }else {
            if ($appendLocation) {
                $this->title=$this->crumbTitle=$summery." ".$this->lang['in']." ".$location;
            }else {
                $this->title=$this->crumbTitle=$summery;
            }
        }
        return $this->crumbString=$bread;
    }


    function _getAdTitle(){
        $title='';
        $ref='';
        $append=false;
        if ($this->detailAd[Classifieds::TITLE])
            $title=preg_replace('/"/', '', $this->detailAd[Classifieds::TITLE]);
        else {
            $title=preg_replace('/["\']/', '', $this->getAdTitle($this->detailAd));
        }

        $append=true;
        if (!empty($title) && $title[0]=='#') {
            $title=substr($title, 1);
        }
        $l=2;
        $in='in';
        $this->adRef=$ref=" - ad {$this->detailAd[Classifieds::ID]}";
        if ($this->detailAd[Classifieds::RTL]) {
            $l=1;
            $in="في";
            $this->adRef=$ref=" - إعلان {$this->detailAd[Classifieds::ID]}";
        }
        $title=trim($title);
        if ($this->detailAd[Classifieds::PUBLICATION_ID]!=1 && $this->appendLocation)
            $title.=($this->detailAd[Classifieds::ROOT_ID]!=1 ?" ".$in:"")." ".($this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$l]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$l]?$this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$l]." ":"").$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$l];
        if ($append) {
            $title.=$ref;
        }
        return $title;
    }


    function buildTitle(){
        $title='';
        if ($this->urlRouter->module=='detail') {
            if ($this->detailAdExpired) {
                if (empty($this->detailAd)) {
                    header("HTTP/1.0 410 Gone");
                    $title=$this->title;
                }else {
                    $this->urlRouter->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                    $title=$this->_getAdTitle();
                }
            }else {
                $this->urlRouter->cacheHeaders($this->detailAd[Classifieds::LAST_UPDATE]);
                $title=$this->_getAdTitle();
            }
            $this->lang['description'].=$this->adRef;
        }else {
            $title=$this->title;
            if($this->localityId){
                $title.=' '.$this->urlRouter->countries[$this->urlRouter->countryId][$this->fieldNameIndex];
            }
            if ($this->urlRouter->params['start']>1) {
                $title .= $this->lang['search_suffix'] . $this->urlRouter->params['start'];
            }
            if (!$this->extendedId && !$this->localityId && $this->urlRouter->naming!=NULL && !empty($this->urlRouter->naming[2])) {
                if ($this->hasCities && $this->urlRouter->cityId)
                    $location = $this->lang['in'] . ' ' . $this->cityName . ' ' . $this->countryName;
                else
                    $location = $this->lang['in'] . ' ' . $this->countryName;
                    
                $this->lang['description'] = sprintf($this->urlRouter->naming[2], $this->purposeName, $location);
                if ($this->urlRouter->params['start']>1) {
                    $this->lang['description'] .= $this->lang['search_suffix'] . $this->urlRouter->params['start'];
                }
            }else
                $this->lang['description']=$this->lang['search_description'].$title;
        }
        $this->title = $title;
    }


    function getAdTitle(&$ad){
        $title="";
        $l="en";
        $nameIndex=2;
        if ($ad[Classifieds::RTL]) {
            $l="ar";
            $nameIndex=1;
        }
        $params=array(
            "purpose_id"=>$ad[Classifieds::PURPOSE_ID],
            "purpose_name"=>$this->urlRouter->purposes[$ad[Classifieds::PURPOSE_ID]][$nameIndex],
            "publication_id"=>$ad[Classifieds::PUBLICATION_ID],
            "publication_name"=>$this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][$nameIndex],
            "country_id"=>$ad[Classifieds::COUNTRY_ID],
            "country_name"=>$this->urlRouter->countries[$ad[Classifieds::COUNTRY_ID]][$nameIndex],
            "city_id"=>$ad[Classifieds::CITY_ID],
            "city_name"=>$this->urlRouter->cities[$ad[Classifieds::CITY_ID]][$nameIndex],
            "category_id"=>$ad[Classifieds::CATEGORY_ID],
            "section_id"=>$ad[Classifieds::SECTION_ID],
            "section_name"=>$ad[Classifieds::SECTION_NAME_AR-1+$nameIndex],
            "root_id"=>$ad[Classifieds::ROOT_ID],
            "root_name"=>$this->urlRouter->roots[$ad[Classifieds::ROOT_ID]][$nameIndex],
            "body"=>$ad[Classifieds::CONTENT],
            "lang"=>$l,
        );
        $title=new WebTitle($params);
        $title=$title->get();
        if ($title=="") {
            $shorten = $this->shortenAd($ad[Classifieds::CONTENT],60,false);
            if ($ad[Classifieds::SECTION_ID]!=63 && !mb_ereg_match($ad[Classifieds::SECTION_NAME_AR-1+$nameIndex], $shorten))
                $title=$ad[Classifieds::SECTION_NAME_AR-1+$nameIndex] . " - " . $shorten;
            else
                $title = $shorten;
        }
        $this->classifieds->updateAdTitle($ad[Classifieds::ID], $title, $ad);
        return $ad[Classifieds::TITLE];
    }

}
?>