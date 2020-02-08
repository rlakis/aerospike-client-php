<?php
\Config::instance()->incLayoutFile('Page');

class Home extends Page {
    
    var $hasBottomBanner = false;

    function __construct() {
        \header('Vary: User-Agent');
        parent::__construct();
        $this->lang['description']=$this->lang['home_description'];
        if ($this->router->countryId>0) {
            $this->lang['description'].=' '.$this->lang['in'].' '.$this->title;
        }
        else {
            $this->lang['description'].=$this->lang['home_description_all'];
        }
        $this->render();
    }
        
    
    function mainMobile(){
        if ($this->router->rootId)
            $this->router->cacheExtension();
        $isHome = !$this->router->rootId && $this->router->countryId;
        $lang=$this->router->language=='ar'?'':$this->router->language.'/';
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
        if (0 && !$this->router->rootId && $this->router->countryId) {
            ?><div id='fb-box' class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-show-faces="true" data-stream="false" data-border-color="#E7E9F0" data-header="true"></div><?php
        }
        
        //$this->renderMobileLike();
        //echo $this->fill_ad('zone_4','ad_m');
    }
   
    function renderMobileCountry(){
        if ($this->router->rootId) return;
        if ($this->router->countryId){
            echo '<ul class="ls">';
            echo '<li><a href="/', $this->appendLang ,'"><span class="flag-icon large c', $this->router->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></a></li>';
            echo '</ul>';
        }else {
            echo '<h2 class="ctr">'.$this->lang['mobileChooseCountry'].'</h2>';
            
            $userCountry = $this->user->params['user_country'] ?? 0;
            if($userCountry){
                foreach ($this->router->countries as $country_id => $country) {
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
            foreach ($this->router->countries as $country_id => $country) {
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
        if (!$this->router->countryId || $this->router->rootId) return;
        
            echo '<ul class="ls br">';
            $i=0;

            foreach ($this->router->pageRoots as $key=>$root) {
                $count=$this->checkNewUserContent($root['unixtime']) ? '<b>'.$root['counter'].'</b>' : $root['counter'];
                $_link = $this->router->getURL($this->router->countryId, $this->router->cityId, $key);
                        
                echo '<li><a href="', $_link, '"><span class="ic r',$key,'"></span>',
                    $root['name'], '<span class="to"></span><span class="n">', $count, '</span></a></li>';
                $i++;
            }
            
            echo '</ul>';
    }
    
    
    function renderMobileSections(){
        if (!$this->router->rootId) return;
        echo "<ul class='ls oh'>";
        $i=0;
        //$hasLogoSpan=$this->router->rootId==2?true:false;
        $cssPre='';
        switch($this->router->rootId){
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
        foreach ($this->router->pageSections as $key=>$section) {
            $this->router->pageSections[$key]['id']=$key;
        }
        if(isset($this->user->params['catsort']) && $this->user->params['catsort']){
            usort($this->router->pageSections, function($a, $b){
                return $b['counter'] > $a['counter'];
            });
        }
        foreach ($this->router->pageSections as $section) {
            $count=$this->checkNewUserContent($section['unixtime']) ? '<b>'.$section['counter'].'</b>' : $section['counter'];
            $purposeId = (count($section['purposes'])==1) ? array_keys($section['purposes'])[0] : 0;
            //$purposeId=(is_numeric($section[3]) ? (int)$section[3]:0);
            $_link = $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section['id'], $purposeId);
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

    
    // deprecated
    function _main_pane(){        
        $adLang='';
        if (!$this->router->isArabic()) { $adLang=$this->router->language.'/'; }
        
        ?><div class="tv rcb"><div class="tx sh"><div class=tz><?= $this->lang['billboard'] ?><p class=ctr><?php
        if (!$this->router->siteTranslate) {
            if ($this->user->info['id']){
                echo '<a class=bt href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
            }
            else {
                echo '<a class="bt login" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
            }
        }
        ?></p></div></div></div><?php 
        
        parent::_main_pane();
    }


    function main_pane() : void {
        echo '<!--googleoff: snippet-->';
        echo '<section class=search-box><div class="viewable ha-center">';
        $sections=[];
        $keys=[];
        $kr=\array_keys($this->router->pageRoots);
        foreach ($kr as $id) {
            $label="section-dat-{$this->router->countryId}-{$this->router->cityId}-{$id}-{$this->router->language}-c";
            $keys[$id]=$this->router->db->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
        }
               
        $status=$this->router->db->as->getConnection()->getMany(\array_values($keys), $recs);
        if ($status===\Aerospike::OK) {
            foreach ($recs as $sec) {
                \preg_match('/section-dat-\d+-\d+-(\d+)-.*/', $sec['key']['key'], $matches);
                if (\is_array($matches) && \count($matches)===2) {
                    $id=$matches[1]+0;                    
                    if ($sec['bins']===null) {
                        $this->router->db->asSectionsData($this->router->countryId, $this->router->cityId, $id, $this->router->language, true);
                    }
                    else {
                        $sections[$id]=$sec['bins']['data'];
                    }
                }
            }
        }
        
        echo '<div class=roots>';
        foreach ($sections as $root_id => $items) {
            echo '<div class=large>';
            echo '<div class=row><i class="icn ro i',$root_id,'"></i></div>';
            echo '<span class=row>', $this->router->roots[$root_id][$this->name], '</span>';
            echo '<div class=arrow></div>';
            echo '</div>';            
        }
        echo '</div>';        
        echo '</section>';
        
        echo '<main>';
        echo '<div class="row viewable home">';
        
        ?><div class="col-12"><h2>What other people are searching now...</h2><a href="#">View All</a></div><?php
        ?><div class="col-12"><h2>Recommended for you</h2><a href="#">View All</a></div><?php
        ?><div class="col-12"><h2>Latest uploads</h2><a href="#">View All</a></div><?php
        
        foreach ($sections as $root_id => $items) {
            echo '<div class=col-4><div class=card>';
            echo '<div class=card-header style="background-color:var(--color-',$root_id,');"><i class="icn icn-', $root_id, '"></i></div>';
            echo '<div class=card-content>';
            echo '<h4 class=card-title>', $this->router->pageRoots[$root_id]['name'],'</h4>';
            echo '<ul>';
            $i=0;
            foreach ($items as $section_id => $section) {
                if ($section['counter']==0) { break; }
                $link = $this->router->getURL($this->router->countryId, $this->router->cityId, $root_id, $section_id);
                $cls = $this->checkNewUserContent($section['unixtime']) ? ' hot': '';
                echo '<li><a href="', $link,'">', $section['name'], '<span class="float-right', $cls, '">', \number_format($section['counter'],0), '</span></a></li>';
                $i++;
                if ($i>=10) { break; }
            }
            echo '</ul>';
            echo '</div></div></div>';
        }
        

        echo '<div class=col-4><div class="card test"><div class=card-content>';
        echo '<ul>';
        echo '<li><a href="', $this->router->getLanguagePath('/post/'), '"><i class="icn s icn-82"></i><span>', $this->lang['postFree'], '</span></a></li>';
        if ($this->user()->id()) {
            $balance_label= $this->lang['myBalance']. ' is '.$this->user()->getBalance() . ' coins';
            echo '<li><a href="', $this->router->getLanguagePath('/statement/'), '"><i class="icn icnsmall icn-84"></i><span>', $balance_label, '</span></a></li>';
        }
        echo '<li><a href="', $this->router->getLanguagePath('/contact/'), '"><i class="icn s icn-88"></i><span>', $this->lang['contactUs'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/about/'), '"><i class="icn s icn-83"></i><span>', $this->lang['aboutUs'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/terms/'), '"><i class="icn s icn-85"></i><span>', $this->lang['termsConditions'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/privacy/'), '"><i class="icn s icn-81"></i><span>', $this->lang['privacyPolicy'], '</span></a></li>';
        echo '</ul></div></div>', "\n"; // card
        echo '</div>'; // col-4
        
        echo '</div>', "\n";
        
        
        $this->mostPopular();
        
        echo '<div class="row viewable">', '<div class=col-12>';

        $cc=['ae'=>null, 'sa'=>null, 'kw'=>null, 'bh'=>null, 'qa'=>null, 'om'=>null, 
             'lb'=>null, 'jo'=>null, 'iq'=>null, 
             'eg'=>null, 'ma'=>null, 'tn'=>null, 'dz'=>null];
        
         
        foreach ($this->router->countries as $id => $cn) {
            if ($cn['uri']==='ye') {  continue;  }
            if (!isset($cc[$cn['uri']])) { $cc['uri']=null; }
            
            if ($cn['uri'] && $cc[$cn['uri']]===null) {
                $cc[$cn['uri']] = "<dt><a href={$this->router->getURL($id)}><i class=\"icn icn-{$cn['uri']}\"></i><span>{$cn['name']}</span></a></dt>\n";
            }
            
            foreach ($cn['cities'] as $cid=>$city) {
                $href = $this->router->getURL($id, $cid);
                $cc[$cn['uri']].= "<dd><a href={$href}>{$city['name']}</a></dd>\n";
            }
        }
        //$this->lang['countries_regions']
        echo '<div class=card>';
        ?><header><i class="icn icn-region invert"></i></span><h4><span style="color: white;font-size: 36pt">mourjan</span> around The Middle East</h4></header><div class="bar"></div><?php
        echo '<div class=card-content><div class=row>';
        echo '<dl class="dl col-4">', $cc['ae'], $cc['bh'], $cc['qa'], $cc['kw'], '</dl>', "\n"; 
        echo '<dl class="dl col-4">', $cc['sa'], $cc['om'], $cc['iq'], '</dl>', "\n"; 
        echo '<dl class="dl col-4">', $cc['lb'], $cc['jo'], $cc['eg'], $cc['ma'], $cc['tn'], $cc['dz'], '</dl>', "\n"; 
        echo '</div></div>'; // card-content

        echo '</div>' /* card */, '</div>'; // col-8
        echo '</div>', "\n";
        echo '<!--googleon: snippet-->';
    }
    
    
    public function mostPopular() : void {
        ?><div class="col-12 viewable"><div class="card"><h3>Most active sections</h3></div></div><?php
        
    }


    public function recommendedForYou() : void {
        
    }


    public function recentUploads() : void {
        
    }
    
    
    function main_pane_OLD() {
    	$file= dirname( $this->router->config()->baseDir ) . '/tmp/gen/index-' . $this->includeHash . '2.php';
    	$file= dirname( '/home/www/mourjan' ) . '/tmp/gen/index-' . $this->includeHash . '2.php';
        if (file_exists($file)) {
            echo '<!--googleoff: snippet-->';
            include($file);
            echo '<!--googleon: snippet-->';
        }
    }
}
