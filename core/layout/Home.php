<?php
\Config::instance()->incLayoutFile('Page');

class Home extends Page {
    
    var $hasBottomBanner = false;

    function __construct(\Core\Model\Router $router) {
        header('Vary: User-Agent');
        parent::__construct($router);
        $this->lang['description']=$this->lang['home_description'];
        if ($this->router()->countryId) {
            $this->lang['description'].=' '.$this->lang['in'].' '.$this->title;
        }
        else {
            $this->lang['description'].=$this->lang['home_description_all'];
        }
        $this->render();
    }
    
    /*
    function __construct($router){       
        header('Vary: User-Agent');
        parent::__construct($router);
        $this->lang['description']=$this->lang['home_description'];
        if ($this->isMobile) {
            $this->inlineCss.='h2{margin-top:10px}';
            $this->isMobileCssLegacy=false;
            $this->clear_require('css');
            $this->set_require('css', 's_home');
            if($this->router()->rootId){
                $this->set_require('css', 's_ro');
                $this->set_require('css', 's_root_'.$this->router()->rootId.'_m');
            }
            if($this->user->info['id']){
                $this->set_require('css', 's_user');
            }
        }
        else {
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
    '.($this->router()->isArabic() ? '
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
                'zone_2'=>array('/1006833/LargeRectangle', 336, 280, 'div-gpt-ad-1319707248075-0-'.$this->router()->config()->serverId)
                //'zone_3'=>array('/1006833/mourjan-navigator-square', 200, 200, 'div-gpt-ad-1349258304441-0-'.$this->router()->cfg['server_id']),
                ));                     
        }
        
        if ($this->router()->countryId) {
            $this->lang['description'].=' '.$this->lang['in'].' '.$this->title;
        }
        else {
            $this->lang['description'].=$this->lang['home_description_all'];
        }
        $this->render();
    }*/

    
    function mainMobile(){
        if ($this->router()->rootId)
            $this->router()->cacheExtension();
        $isHome = !$this->router()->rootId && $this->router()->countryId;
        $lang=$this->router()->language=='ar'?'':$this->router()->language.'/';
        if($isHome && $this->user->info['id']) {
            ?><ul class="ls us"><?php 
                ?><li><a href="/post/<?= $lang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
            ?></ul><?php
            ?><ul class="ls us br"><?php 
                ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?><span class="to"></span></a></li><?php 
          /*  ?></ul><?php
            ?><ul class="ls us br"><?php */
                /* ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?><span class="to"></span></a></li><?php */
            ?></ul><?php
            
            ?><ul class="ls us br"><?php 
                ?><li><a href="/statement/<?= $lang ?>"><span class="mc24"></span><?= $this->lang['myBalance'] ?><span class="to"></span></a></li><?php 
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
        if (0 && !$this->router()->rootId && $this->router()->countryId) {
            ?><div id='fb-box' class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-show-faces="true" data-stream="false" data-border-color="#E7E9F0" data-header="true"></div><?php
        }
        
        //$this->renderMobileLike();
        //echo $this->fill_ad('zone_4','ad_m');
    }
   
    function renderMobileCountry(){
        if ($this->router()->rootId) return;
        if ($this->router()->countryId){
            echo '<ul class="ls">';
            echo '<li><a href="/', $this->appendLang ,'"><span class="flag-icon large c', $this->router()->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router()->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></a></li>';
            echo '</ul>';
        }else {
            echo '<h2 class="ctr">'.$this->lang['mobileChooseCountry'].'</h2>';
            
            $userCountry = $this->user->params['user_country'] ?? 0;
            if($userCountry){
                foreach ($this->router()->countries as $country_id => $country) {
                    if($userCountry == $country['uri']){
                        echo '<ul class="cls bbr">';
                        echo '<li><a href="/', $country['uri'], '/'.$this->appendLang.'"><span class="flag-icon large c'.$country_id.'"></span>', $country['name'], '<span class="to"></span></a>';
                        if (!empty($country['cities'])) {
                            echo '<ul class="sls">';
                            foreach ($country['cities'] as $city_id => $city) {
                                echo '<li><a href="/'.$country['uri'].'/'.$city['uri'].'/'.$this->appendLang.'">'.$city['name'].'<span class="to"></span></a></li>';                    
                            }
                            echo '</ul>';
                        }
                        echo '</li></ul>';  
                        break;
                    }
                }         
            }
            echo '<ul class="cls">';
            foreach ($this->router()->countries as $country_id => $country) {
                if(!$userCountry || $userCountry != $country['uri']){
                    echo '<li><a href="/', $country['uri'], '/'.$this->appendLang.'"><span class="flag-icon large c'.$country_id.'"></span>', $country['name'], '<span class="to"></span></a>';
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
    }
       
    function renderMobileRoots(){
        if (!$this->router()->countryId || $this->router()->rootId) return;
        
            echo '<ul class="ls br">';
            $i=0;

            foreach ($this->router()->pageRoots as $key=>$root) {
                $count=$this->checkNewUserContent($root['unixtime']) ? '<b>'.$root['counter'].'</b>' : $root['counter'];
                $_link = $this->router()->getURL($this->router()->countryId, $this->router()->cityId, $key);
                        
                echo '<li><a href="', $_link, '"><span class="ic r',$key,'"></span>',
                    $root['name'], '<span class="to"></span><span class="n">', $count, '</span></a></li>';
                $i++;
            }
            
            echo '</ul>';
    }
    
    
    function renderMobileSections(){
        if (!$this->router()->rootId) return;
        echo "<ul class='ls oh'>";
        $i=0;
        //$hasLogoSpan=$this->router()->rootId==2?true:false;
        $cssPre='';
        switch($this->router()->rootId){
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
        foreach ($this->router()->pageSections as $key=>$section) {
            $this->router()->pageSections[$key]['id']=$key;
        }
        if(isset($this->user->params['catsort']) && $this->user->params['catsort']){
            usort($this->router()->pageSections, function($a, $b){
                return $b['counter'] > $a['counter'];
            });
        }
        foreach ($this->router()->pageSections as $section) {
            $count=$this->checkNewUserContent($section['unixtime']) ? '<b>'.$section['counter'].'</b>' : $section['counter'];
            $purposeId = (count($section['purposes'])==1) ? array_keys($section['purposes'])[0] : 0;
            //$purposeId=(is_numeric($section[3]) ? (int)$section[3]:0);
            $_link = $this->router()->getURL($this->router()->countryId, $this->router()->cityId, $this->router()->rootId, $section['id'], $purposeId);
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
        if ($this->router()->language!="ar") $adLang=$this->router()->language.'/';
        if(0 && $this->router()->countryId==1){
            $rand = rand(0, 1);            
            ?><div onclick="ga('send', 'event', 'OutLinks', 'click', 'Servcorp-HomeBanner');wn('http://www.servcorp.com.lb/en/locations/beirut/beirut-souks-louis-vuitton-building/virtual-offices/');" class="tvs tvs<?= $rand ?>"></div><?php 
        }
        else {
            ?><div class="tv rcb"><div class="tx sh"><div class="tz"><?= $this->lang['billboard'] ?><p class="ctr"><?php
            if (!$this->router()->siteTranslate) {
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


    function main_pane() : void {
        echo '<!--googleoff: snippet-->';
        echo '<div class="row home">', "\n";
        $sections = [];
        foreach ($this->router()->pageRoots as $id=>$root) {
            $count = $root['counter'];
            $link = $this->router()->getURL($this->router()->countryId, $this->router()->cityId, $id);
            //echo '<li><a href="', $link,'"><i class="icn icn-', $id, '"></i>', $root['name'], '<span class="float-right">', number_format($count, 0), '</span></a></li>';
            $sections[$id] = $this->router()->database()->getSectionsData($this->router()->countryId, $this->router()->cityId, $id, $this->router()->language, true);
        }
        $count = count($sections);
        $odd = ($count % 2)==1;
        $j=0;       
        foreach ($sections as $root_id => $items) {
            //if ($odd) {
            //    $j++;
            //    echo '<div class="col-', ($j==$count)?'8':'4', '"><div class="card">';
            //}
            //else {
                echo '<div class="col-4">', '<div class="card">';
            //}
            echo '<div class="card-header" style="background-color:var(--color-',$root_id,');"><i class="icn icn-', $root_id, '"></i></div>';
            echo '<div class="card-content">';
            echo '<h4 class="card-title">', $this->router()->pageRoots[$root_id]['name'],'</h4>';
            echo '<ul>';
            $i=0;
            foreach ($items as $section_id => $section) {
                if ($section['counter']==0) { break; }
                $link = $this->router()->getURL($this->router()->countryId, $this->router()->cityId, $root_id, $section_id);
                $cls = $this->checkNewUserContent($section['unixtime']) ? ' hot': '';
                echo '<li><a href="', $link,'">', $section['name'], '<span class="float-right', $cls, '">', number_format($section['counter'],0), '</span></a></li>';
                $i++;
                if ($i>=10) { break; }
            }
            echo '</ul>';
            echo '</div>';
            echo '</div></div>';
        }
        

        echo '<div class="col-4">';
        echo '<div class="card test">', '<div class="card-content">';
        echo '<ul>';
        echo '<li><i class="icn icnsmall icn-82"></i><span>', $this->lang['postFree'], '</span></li>';
        if ($this->user()->info['id']) {
            echo '<li><i class="icn icnsmall icn-84"></i><span>', $balance_label, '</span></li>';
        }
        echo '<li><i class="icn icnsmall icn-88"></i><span>', $this->lang['contactUs'], '</span></li>';
        echo '<li><i class="icn icnsmall icn-83"></i><span>', $this->lang['aboutUs'], '</span></li>';
        echo '<li><i class="icn icnsmall icn-85"></i><span>', $this->lang['termsConditions'], '</span></li>';
        echo '<li><i class="icn icnsmall icn-81"></i><span>', $this->lang['privacyPolicy'], '</span></li>';
        echo '</ul></div></div>', "\n"; // card
        echo '</div>'; // col-4
        
        echo '</div>', "\n";
        
        echo '<div class="row">';
        echo '<div class="col-12">';

        $cc=['ae'=>null, 'sa'=>null, 'kw'=>null, 'bh'=>null, 'qa'=>null, 'om'=>null, 'ye'=>null, 
            'lb'=>null, 'jo'=>null, 'iq'=>null, 'sy'=>null, 
            'eg'=>null, 'ma'=>null, 'tn'=>null, 'dz'=>null, 'sd'=>null, 'ly'=>null];
        foreach ($this->router()->countries as $id => $cn) {
            if (!isset($cc[$cn['uri']])) { $cc['uri']=null; }
            if ($cc[$cn['uri']]==null) {
                $cc[$cn['uri']] = "<dt><a href={$this->router()->getURL($id)}><i class=\"icn icnsmall icn-{$cn['uri']}\"></i><span>{$cn['name']}</span></a></dt>\n";
            }
            foreach ($cn['cities'] as $cid=>$city) {
                $href = $this->router()->getURL($id, $cid);
                $cc[$cn['uri']].= "<dd><a href={$href}>{$city['name']}</a></dd>\n";
            }
        }
        echo '<div class=card>', '<div class="card-header" style="background-color:navy;"><i class="icn icn-globe"></i></div>', '<div class=card-content><h4 class=card-title>', $this->lang['countries_regions'], '</h4>';
        echo '<div class=col-4><dl class=dl>', $cc['ae'], $cc['bh'], $cc['qa'], $cc['kw'], '</dl></div>', "\n"; 
        echo '<div class=col-4><dl class=dl>', $cc['sa'], $cc['om'], $cc['ye'], $cc['iq'], '</dl></div>', "\n"; 
        echo '<div class=col-4><dl class=dl>', $cc['lb'], $cc['jo'], $cc['eg'], $cc['ma'], $cc['tn'], $cc['dz'], $cc['sd'], $cc['ly'],  $cc['sy'], '</dl></div>', "\n"; 
        echo '</div>'; // card-content

        echo '</div>' /* card */, '</div>'; // col-8
        echo '</div>', "\n";
        echo '<!--googleon: snippet-->';
    }
    
    function main_pane_OLD() {
    	$file= dirname( $this->router()->config()->baseDir ) . '/tmp/gen/index-' . $this->includeHash . '2.php';
    	$file= dirname( '/home/www/mourjan' ) . '/tmp/gen/index-' . $this->includeHash . '2.php';
        if (file_exists($file)) {
            echo '<!--googleoff: snippet-->';
            include($file);
            echo '<!--googleon: snippet-->';
        }
    }
}
?>