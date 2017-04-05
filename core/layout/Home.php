<?php
include $config['dir']. '/core/layout/Page.php';

class Home extends Page{
    
    var $hasBottomBanner = false;

    function __construct($router){        
        header('Vary: User-Agent');
        parent::__construct($router);
        $this->lang['description']=$this->lang['home_description'];
        if ($this->isMobile) {
            $this->isMobileCssLegacy=false;
            $this->clear_require('css');
            $this->set_require('css', 's_home');
            if($this->urlRouter->rootId){
                $this->set_require('css', 's_ro');
                $this->set_require('css', 's_root_'.$this->urlRouter->rootId.'_m');
            }
            if($this->user->info['id']){
                $this->set_require('css', 's_user');
            }
            //$this->set_ad(array('Leaderboard'=>array('/1006833/M320x50', 320, 50, 'div-gpt-ad-1326381096859-0-'.$this->urlRouter->cfg['server_id'])));
        }else{
            $this->inlineCss.='
                .col2w .col1 {
                    width:765px;
                }
@media all and (min-width:1250px) {
    body{
        min-width:1206px
    }
    .w,.col2w,.colw{
        width:1206px;
    }
    .ftr .w{
        width:970px;
    }
    .tpb{
        width:1200px
    }
    .col2w .col1{
        width:990px;
    }
    .mav,.ph,.lgs,.lgt,.sug{
        width:663px
    }
    .phx{
        width:613px
    }
    .ls{
        width:673px
    }
    .dur{
        width:860px
    }
    .u2,.u2.uc1{
        width:317px!important
    }
    .mul{
        width:1176px
    }
    .mul li{
        width:230px
    }
    .mul ul{
        width:230px
    }
    .tz{
        left:330px
    }
    .crd{
        top:89px;
    }
    .ls p{
        height:95px;
    }
    .ls li{
        height:130px;
    }
    .nav{
        width:673px
    }
    .nav .prev{
        width:110px;
    }
    .nav li{
        width:60px;
    }
    .dt{
        width:669px
    }
    .shas label{
        width:auto;
    }
    .tbs{
        width:1204px;
    }
    .tbs.t2 li{width:602px}
    .tbs.t3 li{width:401px}
    .tbs.t4 li{width:300px}
    .tbs.t5 li{width:240px}
    .tbs.t6 li{width:200px}
    '.($this->urlRouter->siteLanguage=='ar' ? '
    .dl ul{
        margin-left:9px;
        float:left;
    }
    .uhl,.u2{
        float:right!important
    }':'
    .dl ul{
        margin-right:9px;
        float:right;
    }
    .uhl,.u2{
        float:left!important
    }').'
}
            ';
            $this->set_ad(array(
                'zone_2'=>array('/1006833/LargeRectangle', 336, 280, 'div-gpt-ad-1319707248075-0-'.$this->urlRouter->cfg['server_id'])
                //'zone_3'=>array('/1006833/mourjan-navigator-square', 200, 200, 'div-gpt-ad-1349258304441-0-'.$this->urlRouter->cfg['server_id']),
                ));
            
            /*
            if (count($this->urlRouter->cfg['campaign'])){    
                $adKey=array();
                $adKey[]=  $this->urlRouter->countryId.'_x_';
                $adKey[]=  $this->urlRouter->countryId.'_'.$this->urlRouter->cityId.'_';
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
                            $set++;
                            break;
                        }
                    }
                    if ($set>3) break;
                }
            }
            */
        }
        
        if ($this->urlRouter->countryId) {
            $this->lang['description'].=' '.$this->lang['in'].' '.$this->title;
        }
        else {
            $this->lang['description'].=$this->lang['home_description_all'];
        }
        $this->render();
    }

    
    function mainMobile(){
        if ($this->urlRouter->rootId)
            $this->urlRouter->cacheExtension();
        $isHome = !$this->urlRouter->rootId && $this->urlRouter->countryId;
        $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
        if($isHome && $this->user->info['id']) {
            ?><ul class="ls us"><?php 
                ?><li><a href="/post/<?= $lang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
            ?></ul><?php
            ?><ul class="ls us br"><?php 
                ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?><span class="to"></span></a></li><?php 
          /*  ?></ul><?php
            ?><ul class="ls us br"><?php */
                ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?><span class="to"></span></a></li><?php 
            ?></ul><?php
            ?><ul class="ls us br"><?php
                ?><li class="h"><b><?= $this->lang['myAds'] ?></b></li><?php 
                ?><li><a href="/myads/<?= $lang ?>"><span class="ic aon"></span><?= $this->lang['ads_active'] ?><span class="to"></span></a></li><?php
                ?><li><a href="/myads/<?= $lang ?>?sub=pending"><span class="ic apd"></span><?= $this->lang['ads_pending'] ?><span class="to"></span></a></li><?php
                ?><li><a href="/myads/<?= $lang ?>?sub=drafts"><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?><span class="to"></span></a></li><?php
                ?><li><a href="/myads/<?= $lang ?>?sub=archive"><span class="ic afd"></span><?= $this->lang['ads_archive'] ?><span class="to"></span></a></li><?php
            ?></ul><?php
        }
        $this->renderMobileCountry();
        $this->renderMobileSections();
        $this->renderMobileRoots();
        if($isHome && $this->user->info['id']) {
        ?><ul class="ls us br"><?php 
            ?><li><a href="/account/<?= $lang ?>"><?= $this->lang['myAccount'] ?><span class="et"></span></a></li><?php
        ?></ul><?php
        }elseif($isHome) {
            ?><ul class="ls us br"><?php 
                ?><li><a href="/post/<?= $lang ?>"><span class="ic apb"></span><?= $this->lang['placeAd'] ?><span class="to"></span></a></li><?php 
            ?></ul><?php
        }
        $this->renderMobileLinks();
        if (0 && !$this->urlRouter->rootId && $this->urlRouter->countryId) {
            ?><div id='fb-box' class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-show-faces="true" data-stream="false" data-border-color="#E7E9F0" data-header="true"></div><?php
        }
        
        //$this->renderMobileLike();
        //echo $this->fill_ad('zone_4','ad_m');
    }
   
    
    function renderMobileCountry(){
        if ($this->urlRouter->rootId) return;
        if ($this->urlRouter->countryId){
            echo '<ul class="ls">';
            echo '<li><a href="/', $this->appendLang ,'"><span class="cf c', $this->urlRouter->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->urlRouter->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></a></li>';
        }else {
            echo '<h2 class="ctr">'.$this->lang['mobileChooseCountry'].'</h2>';
            echo '<ul class="cls">';
            foreach ($this->urlRouter->countries as $country_id => $country) {
                echo '<li><a href="/', $country['uri'], '/'.$this->appendLang.'"><span class="cf c'.$country_id.'"></span>', $country['name'], '<span class="to"></span></a>';
                if (!empty($country['cities'])) {
                    echo '<ul class="sls">';
                    foreach ($country['cities'] as $city_id => $city) {
                        echo '<li><a href="/'.$country['uri'].'/'.$city['uri'].'/'.$this->appendLang.'">'.$city['name'].'<span class="to"></span></a></li>';                    
                    }
                    echo '</ul>';
                }
                echo '</li>';                
            }                        
        }
        echo '</ul>';
    }
    

    function renderMobileRoots(){
        if (!$this->urlRouter->countryId || $this->urlRouter->rootId) return;
        
            echo '<ul class="ls br">';
            $i=0;

            foreach ($this->urlRouter->pageRoots as $key=>$root) {
                $count=$this->checkNewUserContent($root['unixtime']) ? '<b>'.$root['counter'].'</b>' : $root['counter'];
                $_link = $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $key);
                        
                echo '<li><a href="', $_link, '"><span class="ic r',$key,'"></span>',
                    $root['name'], '<span class="to"></span><span class="n">', $count, '</span></a></li>';
                $i++;
            }
            
            echo '</ul>';
    }
    
    
    function renderMobileSections(){
        if (!$this->urlRouter->rootId) return;
        echo "<ul class='ls oh'>";
        $i=0;
        //$hasLogoSpan=$this->urlRouter->rootId==2?true:false;
        $cssPre='';
        switch($this->urlRouter->rootId){
                        case 1:
                            $cssPre='x x';
                            break;
                        case 2:
                            $cssPre='z z';
                            break;
                        case 3:
                            $cssPre='v v';
                            break;
                        case 4:
                            $cssPre='y y';
                            break;
                        case 99:
                            $cssPre='u u';
                            break;
        } 
        foreach ($this->urlRouter->pageSections as $key=>$section) {
            $this->urlRouter->pageSections[$key]['id']=$key;
        }
        if(isset($this->user->params['catsort']) && $this->user->params['catsort']){
            usort($this->urlRouter->pageSections, function($a, $b){
                return $b['counter'] > $a['counter'];
            });
        }
        foreach ($this->urlRouter->pageSections as $section) {
            $count=$this->checkNewUserContent($section['unixtime']) ? '<b>'.$section['counter'].'</b>' : $section['counter'];
            $purposeId = (count($section['purposes'])==1) ? array_keys($section['purposes'])[0] : 0;
            //$purposeId=(is_numeric($section[3]) ? (int)$section[3]:0);
            $_link = $this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $section['id'], $purposeId);
            echo '<li'.(in_array($section['id'],[29,63,105,117]) ? ' class="ozer"':'').'><a href="', $_link, '">',
                    "<span class='{$cssPre}{$section['id']}'></span>",
                    $section['name'], '<span class="to"></span><span class="n">', $count, '</span></a></li>';
            $i++;
        }
        echo '</ul>';
        ?> <!--googleoff: index --> <?php
        if(isset($this->user->params['catsort']) && $this->user->params['catsort']==1){
            ?><span onclick="setOrder(this)" class="rbt subit az">Az</span><?php  
        }else{
            ?><span onclick="setOrder(this)" class="rbt subit numz">9-1</span><?php            
        }
        $this->globalScript.='
            
var ulList=[];
var setOrder=function(e)
{
    var elem = $(e);
    var ul = elem.prev();
    if(ulList.length==0){
        ul.children().each(function(i,f){
            var g = f.childNodes[0];
            if(g.childNodes.length==3){
                ulList[ulList.length] = {
                    c : parseInt(g.childNodes[2].innerHTML.replace(/[^0-9]/g,"")),
                    t : g.childNodes[0].nodeValue,
                    n : f
                };
            }else{
                ulList[ulList.length] = {
                    c : parseInt(g.childNodes[3].innerHTML.replace(/[^0-9]/g,"")),
                    t : g.childNodes[1].nodeValue,
                    n : f
                };
            }
        });
    }
    window.scroll(0,0);
    var f=function(){    
        elem.removeClass("spin").addClass("rspin");
        setTimeout(function(){elem.html("Az")},250);
    };    
    var z=function(){        
        f();
        history.back()
    };
    var s = elem.hasClass("numz");
    var l = ulList.length;
    var sort=0;
    if(s){   
        sort=1;
        ulList.sort(function(a,b){
            if (b.c > a.c) return 1;
            if (b.c < a.c) return -1;
            if(a.t < b.t) return -1;
            if(a.t > b.t) return 1;
            return 0; 
        });
        ul.empty();
        for(var i=0; i<l; i++){
            ul.append(ulList[i].n);
        }
        elem.removeClass("numz");
        elem.addClass("az");
        toast(lang=="ar"?"تم الترتيب حسب عدد الاعلانات":"order by ads\" count");
        f();
    }else{
        ulList.sort(function(a,b){
            if(a.t < b.t) return -1;
            if(a.t > b.t) return 1;
            if (b.c > a.c)return 1;
            if (b.c < a.c)return -1;
            return 0;
        });
        ul.empty();
        var t,n;
        for(var i=0; i<l; i++){
            n=ulList[i].n;
            if(n.className=="ozer"){
                t=n;
            }else{
                ul.append(ulList[i].n);
            }
        }
        ul.append(t);
        elem.removeClass("az");
        elem.addClass("numz");        
        toast(lang=="ar"?"تم الترتيب حسب الابجدية":"order by alphabets");
        
        elem.removeClass("rspin").addClass("spin");
        setTimeout(function(){elem.html("9-1")},250);
    }
    $.ajax({
        type:"GET",
        url:"/ajax-sorting/?or="+sort,           
        dataType:"json"
    })
}
        ';
        ?> <!--googleon: index --> <?php
    }

    
    function side_pane(){
        //$this->renderSideUserPanel();
        $this->renderSideCountries();
        //$this->renderSideLike();
        //echo $this->fill_ad('zone_3', 'ad_s');
    }

    
    function _main_pane(){
        $adLang='';
        //$header=($this->urlRouter->cityId? $this->urlRouter->cities[$this->urlRouter->cityId][$this->fieldNameIndex]:($this->urlRouter->countryId ? $this->urlRouter->countries[$this->urlRouter->countryId][$this->fieldNameIndex] : $this->lang['mourjan_header']));
        if ($this->urlRouter->siteLanguage!="ar") $adLang=$this->urlRouter->siteLanguage.'/';
        /*if ($this->urlRouter->countryId || $this->urlRouter->cityId) {
            if ($adLang) $header.=' '.$this->lang['title_home'];
            else $header=$this->lang['title_home'].' '.$header;
        }*/
        if(0 && $this->urlRouter->countryId==1){
            $rand = rand(0, 1);
            
            ?><div onclick="ga('send', 'event', 'OutLinks', 'click', 'Servcorp-HomeBanner');wn('http://www.servcorp.com.lb/en/locations/beirut/beirut-souks-louis-vuitton-building/virtual-offices/');" class="tvs tvs<?= $rand ?>"></div><?php 
            /* ?><div class="tvf"></div><?php */
        }else{
            ?><div class="tv rcb"><div class="tx sh"><div class="tz"><?= $this->lang['billboard'] ?><p class="ctr"><?php
            if (!$this->urlRouter->siteTranslate) {
                if ($this->user->info['id']){
                    echo '<a class="bt" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
                }else {
                    echo '<a class="bt login" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
                }
            }
            ?></p></div></div></div><?php 
        }
        parent::_main_pane();
    }


    function main_pane()
    {
    	$file= dirname( $this->urlRouter->cfg['dir'] ) . '/tmp/gen/index-' . $this->includeHash . '2.php';
        if (file_exists($file)) 
        {
            echo '<!--googleoff: snippet-->';
            include($file);
            echo '<!--googleon: snippet-->';
        }
    }
}
?>