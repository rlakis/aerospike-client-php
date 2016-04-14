<?php
include $config['dir'].'/core/layout/Search.php';

class Detail extends Search{


    function Detail($router){
        parent::Search($router);
    }

    function header(){
        parent::header();
        if ($this->detailAdExpired) return;
        
        //$this->urlRouter->db->increment('read_'.$this->detailAd[Classifieds::ID]);
        $pageThumb='';
        if ($this->detailAd[Classifieds::PICTURES]){
            $pageThumb=$this->urlRouter->cfg['url_resources'].'/repos/d/'.$this->detailAd[Classifieds::PICTURES][0];
        }elseif ($this->detailAd[Classifieds::VIDEO]){
            $pageThumb=$this->detailAd[Classifieds::VIDEO][2];
        }else {
        switch($this->detailAd[Classifieds::ROOT_ID]){
            case 1:
                switch ($this->detailAd[Classifieds::CATEGORY_ID]){
                    case 1:
                        switch($this->detailAd[Classifieds::SECTION_ID]){
                            case 1:
                            case 2:
                                $pageThumb='apartment.png';
                                break;
                            case 3:
                                $pageThumb='house.png';
                                break;
                            case 4:
                                $pageThumb='resort.png';
                                break;
                            default:
                                $pageThumb='house.png';
                                break;
                        }
                        break;
                    case 2:
                        switch($this->detailAd[Classifieds::SECTION_ID]){
                            case 6:
                                $pageThumb='office.png';
                                break;
                            case 5:
                                $pageThumb='shop.png';
                                break;
                            case 8:
                            default:
                                $pageThumb='buildings.png';
                                break;
                        }
                        break;
                    case 3:
                        $pageThumb='land.png';
                        break;
                    default:
                        $pageThumb='buildings.png';
                        break;
                }
                break;
            case 2:
                switch ($this->detailAd[Classifieds::CATEGORY_ID]){
                    case 4:
                        $pageThumb='cars.png';
                        break;
                    case 16:
                        switch($this->detailAd[Classifieds::SECTION_ID]){
                            case 62:
                                $pageThumb='pickup.png';
                                break;
                            case 65:
                                $pageThumb='bus.png';
                                break;
                            case 69:
                                $pageThumb='heavy.png';
                                break;
                            default:
                                $pageThumb='cars.png';
                                break;
                        }
                        break;
                    default:
                        $pageThumb='cars.png';
                        break;
                }
                break;
            case 3:
                $pageThumb='jobs.png';
                break;
            case 99:
                switch($this->detailAd[Classifieds::SECTION_ID]){
                    case 63:
                    case 85:
                        $pageThumb='unknown.png';
                        break;
                    case 84:
                        $pageThumb='furniture.png';
                        break;
                    default:
                        $pageThumb='unknown.png';
                        break;
                }
                break;
            default:
                $pageThumb='unknown.png';
                break;
        }
        $pageThumb=$this->urlRouter->cfg["url_resources"]."/img/".$pageThumb;
        }
        $this->adTitle = "<span itemprop='name'>{$this->urlRouter->sections[$this->detailAd[Classifieds::SECTION_ID]][$this->fieldNameIndex]}</span> - " .
            $this->urlRouter->purposes[$this->detailAd[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];
        ?><meta property="og:locale" content="<?= ($this->detailAd[Classifieds::RTL] ? "ar_LB" : "en_US" ) ?>"/><meta property="og:title" content="<?= $this->title ?>" /><meta property="og:url" content="http://www.mourjan.com<?= $this->urlRouter->uri.$this->detailAd[Classifieds::ID].'/' ?>" /><meta property="og:type" content="product" /><meta property="og:site_name" content="Mourjan.com" /><meta property="og:image" content="<?= $pageThumb ?>" /><meta property="og:admins" content="mourjan@berysoft.com" /><meta property="fb:admins" content="682495312" /><meta property="og:description" content="<?= preg_replace("/<\s*\w.*?>(.*?)(<\s*\/\s*\w\s*.*?>|<\s*br\s*>)/", "", $this->detailAd[Classifieds::CONTENT]) ?>" /><?php    
    }

    function _main_pane(){
        //$this->detailAd[Classifieds::PICTURES]="1226930_2.jpg|1226946_3.jpg|1226930_2.jpg|1226946_3.jpg";
        $this->displayDetail();
        parent::_main_pane();
    }
    
    function main_pane(){
        echo '<div>';
        parent::main_pane();
        echo '</div>';
    }

    function mainMobile(){
        $this->displayDetailMobile();
        parent::resultsMobile();
        echo $this->fill_ad('Leaderboard','ad_m');
    }
    
    function displayDetail(){
        if (!$this->detailAdExpired) {
            $pics=null;
            $picsCount=0;
            $hasVideo=0;
            $hasMap=false;
            $showMap=false;
            $vWidth=250;
            if (isset ($_GET['map']) && $_GET['map']=='on') $showMap=true;
            if ($this->detailAd[Classifieds::PICTURES]){
                $pics=$this->detailAd[Classifieds::PICTURES];
            }else {
                $vWidth+=40;
            }
            if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]){
                $hasVideo=1;
            }
            $picsCount=count($pics);
            $onePhoto=$picsCount==1 && !$hasVideo;
            
                $itemScope='';
                $itemDesc='';
                $hasSchema=false;
                if ($this->detailAd[Classifieds::ROOT_ID]==1){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==2){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==3){
                    if ($this->detailAd[Classifieds::PURPOSE_ID]==3) {
                        $itemDesc='itemprop="description" ';
                        $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/JobPosting"';
                   }elseif ($this->detailAd[Classifieds::PURPOSE_ID]==4) {
                      $itemDesc='itemprop="description" ';
                      $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/Person"';
                    }
                }
            
            ?><div class="dt rc"><?php
            if ($this->urlRouter->cfg['enabled_sharing']) {
                echo '<!--googleoff: snippet-->';
                //<span class='st_plusone_large gp'></span>
                ?><div class='share sh rct'><?= $this->urlRouter->siteTranslate? '':'<label>'.$this->lang['shareFriends'].'</label>' ?><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_stumbleupon_large' ></span><span  class='st_blogger_large' ></span><span  class='st_sharethis_large' ></span></div><?php
                echo '<!--googleon: snippet-->';
            }
            $autoplay=$this->get('auto_play','boolean');
            if ($picsCount || $hasVideo) {
                ?><div class="c cap"><?php
                    ?><div class="ap"><?php 
                        ?><div class="cvs<?= ($hasVideo && !$picsCount)?' cvv':'' ?>"><?php 
                            if ($hasVideo){
                                ?><div class="vid"><object width='<?= $vWidth ?>' height='250'><param name='movie' value='<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>'></param><param name='wmode' value='transparent'></param><embed src='<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>' type='application/x-shockwave-flash' wmode='transparent' width='<?= $vWidth ?>' height='250'></embed></object></div><?php
                            }
                        ?></div><?php
                $this->globalScript.='var imgs=[];';
                if ($hasVideo && $picsCount) {
                    echo '<input type="button" onclick="vip(this)" class="bt play on" />';
                    $this->inlineScript.='cvu=$(".bt.play");';
                    $this->globalScript.='var vip=function(e){noslide=1;if(gtm)clearTimeout(gtm);if (cvu){cvu.removeClass("on")};cvu=$(e);cvu.addClass("on");var d=$(".vid",cvu.parent());d.css("z-index",zx++)};';
                }
                //if (!$onePhoto) {
                    for($i=1;$i<=$picsCount;$i++){
                        $this->globalScript.='imgs['.$i.']="'.$pics[$i-1].'";';
                        echo '<input type="button" onclick="gai('.$i.',this)" class="bt" value="'.$i.'" />';
                    }
                    $this->globalScript.='var cvs,cvu,gtm,cvc='.$picsCount.',zx=100,noslide='.$hasVideo.';var gai=function(i,e){noslide=0;if (cvu){cvu.removeClass("on")};cvu=$(e);cvu.addClass("on");var g;if(!cvs) cvs=$(".cvs");if (typeof imgs[i]=="string") {g=$("<div class=\'dg\'></div>");g.css("background-image","url('.$this->urlRouter->cfg['url_resources'].'/repos/d/"+imgs[i]+")");g.css("z-index",zx++);cvs.append(g);imgs[i]=g;g.click(gan);g.fadeIn();}else {g=imgs[i];g.css("display","none");g.css("z-index",zx++);g.fadeIn();};if(gtm)clearTimeout(gtm);if(!noslide && cvc>1)gtm=setTimeout("gan()",10000);};var gan=function(){var n=cvu.next();if (n.length) n.trigger("click");else {$(cvu.parent().children()['.($hasVideo ? 2:1).']).trigger("click")}};';
                    if (!$hasVideo)$this->inlineScript.='gai(1,$(".ap > .bt")[0]);';
                //}
                ?></div><div class="tm"></div><?php
            }else {
                ?><div class="c cpp"><div class="tm tmx"></div><?php
            }
            $pub_link = $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];

            if ($this->urlRouter->siteLanguage=='ar'){                
                $detailPagination='<ul class="md"><li class="p rbrc"></li><li class="n rblc"></li></ul>';
            }else {
                $detailPagination='<ul class="md"><li class="p rblc"></li><li class="n rbrc"></li></ul>';                
            }
            if ($this->detailAd[Classifieds::OUTBOUND_LINK])
                $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->detailAd[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
            elseif ($this->detailAd[Classifieds::PUBLICATION_ID]!=1)
                $pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";
            elseif($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]) {
                $hasMap=true;
                if ($this->urlRouter->siteLanguage=='ar'){                
                    $detailPagination='<ul class="md md1"><li class="p rblc"></li><li onclick="imap(this)" class="lm"><span class="loc"></span></li><li class="n rbrc"></li></ul>';
                }else {
                    $detailPagination='<ul class="md md1"><li class="p rblc"></li><li onclick="imap(this)" class="lm"><span class="loc"></span></li><li class="n rbrc"></li></ul>';
                }
            }
            //else $pub_link='<b>'.$pub_link.'</b>';

            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';
            if ($this->urlRouter->siteTranslate)$para_class='';
            ?><div class="pr"<?= $itemScope?>><h1 class='<?= $this->urlRouter->siteLanguage ?>'><?= $this->adTitle ?></h1><p <?= $itemDesc ?>class='<?= $para_class ?>'><?= $this->renderTextLinks($this->detailAd[Classifieds::CONTENT]) ?></p><p class='<?= $this->urlRouter->siteLanguage ?> lc'><?= $pub_link. ($this->hasCities && $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? " - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID],$this->detailAd[Classifieds::CITY_ID])."'>" . $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]."</a>":"")." - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID])."'>" . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]."</a> <b st='".$this->detailAd[Classifieds::UNIXTIME]."'></b>";?></p></div><?= $detailPagination ?></div></div><?php
            if ($hasMap) {
                ?><div class="mph rcb<?= $showMap?'':' hid' ?>"><div id="map" class="<?= $showMap?' loading':'' ?>"></div></div><?php
                $this->globalScript.='var map,mapd,marker,rmap=false,geocoder,infoWindow;function initMap() {rmap=true;geocoder = new google.maps.Geocoder();infowindow = new google.maps.InfoWindow();var myOptions = {zoom:17,mapTypeId: google.maps.MapTypeId.HYBRID};map = new google.maps.Map(mapd[0], myOptions);marker = new google.maps.Marker({map: map,animation: google.maps.Animation.DROP});pos = new google.maps.LatLng('.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].');map.setCenter(pos);marker.setPosition(pos)};';
                $this->globalScript.='var imap=function(e){if (!rmap){var s=document.createElement("script");s.type="text/javascript";s.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initMap&language='.$this->urlRouter->siteLanguage.'";document.body.appendChild(s)};e=$(e);if(e.hasClass("on")){e.removeClass("on");mapd.parent().slideUp()}else{e.addClass("on");mapd.parent().slideDown()}};';
                $this->inlineScript.='mapd=$("#map");';
                if ($showMap)$this->inlineScript.='imap($(".lm"),$(".md"));';
            }
        }
    }

    function displayDetailMobile(){
        if (!$this->detailAdExpired) {
            //$ad = $this->detailAd;
            /* ?><div class="rb ls rc dt"><?php */
            $pub_link = $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
            $hasEmail=false;
            $hasPhone=false;
            $link='';
            if ($this->detailAd[Classifieds::OUTBOUND_LINK]) {
                $link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$pub_link}']);\" href='{$this->detailAd[Classifieds::OUTBOUND_LINK]}' target='_blank' rel='nofollow'><div class='bt'>{$pub_link}</div></a>";
            }
            elseif ($this->detailAd[Classifieds::PUBLICATION_ID]) {
                $link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$pub_link}']);\" href='{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}' target='_blank'><div class='bt'>{$pub_link}</div></a>";
            }
                $itemScope='';
                $itemDesc='';
                $hasSchema=false;
                if ($this->detailAd[Classifieds::ROOT_ID]==1){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="http://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==2){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="http://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==3){
                    if ($this->detailAd[Classifieds::PURPOSE_ID]==3) {
                        $itemDesc='itemprop="description" ';
                        $itemScope=' itemscope itemtype="http://schema.org/JobPosting"';
                   }elseif ($this->detailAd[Classifieds::PURPOSE_ID]==4) {
                      $itemDesc='itemprop="description" ';
                      $itemScope=' itemscope itemtype="http://schema.org/Person"';
                    }
                }
    
            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';

            $phoneNumbers=$this->detectPhone($this->detailAd[Classifieds::CONTENT]);
            if (count($phoneNumbers) && !empty($phoneNumbers[1]) ) {
                $phoneNumbers=$phoneNumbers[1];
                $hasPhone=true;
            }

            $emails=$this->detectEmail($this->detailAd[Classifieds::CONTENT]);
            if (count($emails) && !empty ($emails[0])) {
                $emails=$emails[0];
                $hasEmail=true;
            }
    /*?><ul class="tl">
            <?= $hasPhone ? "<li class='phone' onclick='iz(this,0)' ontouchstart='iz(this,0)'><div></div></li>":"<li class='phone off'><div></div></li>" ?>
            <?= $hasEmail ? "<li class='email' onclick='iz(this,1)' ontouchstart='iz(this,1)'><div></div></li>":"<li class='email off'><div></div></li>" ?>
            <?= $link ? "<li class='link' onclick='iz(this,2)' ontouchstart='iz(this,2)'><div></div></li>":"<li class='link off'><div></div></li>";
            echo "<li class='share' onclick='iz(this,3)' ontouchstart='iz(this,3)'><div></div></li>"; ?>
    </ul><?php  ?>
    <div id="ado"><div><?php
        if ($hasPhone) {
            foreach ($phoneNumbers as $num){
                $num=preg_split('/[\s\/-]/', $num);
                if (count($num)>1) {
                    if (strlen($num[0]>$num[1])) $num=$num[1].$num[0];
                    else $num=$num[0].$num[1];
                }else {
                    $num=$num[0];
                }
                echo '<a href=\'tel:',$num,'\'><div class=\'bt\'>',$this->lang['call'],' ',$num,'</div></a>';
            }
        }
        ?></div><div><?php
        if ($hasEmail) { 
            foreach ($emails as $email){
                $email=preg_replace('/ /', '', $email);
                echo "<a href='mailto:{$email}'><div class='bt tiny'>", $this->lang['email'], ' <b>', $email, '</b></div></a>';
            }
        } ?></div><div><?php
       if ($link)     { echo $link; }?></div><div class="st"><?php
        if ($this->urlRouter->cfg['enabled_sharing']) {
            ?><span  class='st_email_large'></span><span  class='st_twitter_large' ></span><span  class='st_facebook_large' ></span><span  class='st_blogger_large' ></span><span  class='st_linkedin_large' ></span><span  class='st_stumbleupon_large' ></span><span  class='st_reddit_large' ></span><span  class='st_digg_large' ></span><span  class='st_sharethis_large' ></span><?php
        }
        ?></div><?php
        echo "<div class='bt red fx' ontouchstart='iz()' onclick='iz()'>".$this->lang['cancel']."</div>";?></div> */
        if ($this->urlRouter->cfg['enabled_sharing']) {
            ?><div class="hd sha rct"><span  class='st_email_large'></span><span  class='st_twitter_large' ></span><span  class='st_facebook_large' ></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php
        }
        ?>

                <div class="dt sh rcb"<?= $itemScope ?>><p <?= $itemDesc ?>class='<?= $para_class ?>'><?= $this->renderTextLinks($this->detailAd[Classifieds::CONTENT]) ?></p><?php
                ?><div class="pad"><?php
                if ($hasPhone) {
                    foreach ($phoneNumbers as $num){
                        $num=preg_split('/[\s\/-]/', $num);
                        if (count($num)>1) {
                            if (strlen($num[0]>$num[1])) $num=$num[1].$num[0];
                            else $num=$num[0].$num[1];
                        }else {
                            $num=$num[0];
                        }
                        echo '<a class="bt" href=\'tel:',$num,'\'>',$this->lang['call'],' ',$num,'</a>';
                    }
                }
                if ($hasEmail) {
                    foreach ($emails as $email){
                        $email=preg_replace('/ /', '', $email);
                        echo "<a class='bt sm' href='mailto:{$email}'>", $this->lang['email'], ' <b>', $email, '</b></a>';
                    }
                }
                ?></div><div class='i rcb <?= $this->urlRouter->siteLanguage ?>'><span><?= 
                $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex] .
                ($this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? " - {$this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]}":"")." - ".$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex];?></span> <time st='<?= $this->detailAd[Classifieds::UNIXTIME] ?>'></time></div></div><?php
    /* ?></div><?php */
            }
    }

    function renderTextLinks($str){
        if (!$this->isMobile) {
        $email='/([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})*)(\s|$)/i';
        $str=preg_replace($email,'<a href="mailto:$1">$1</a>', $str);
        }
        $url='/\s(www\.[a-zA-Z0-9\-\.]+\.(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)(\s|$))/i';
        $str=preg_replace($url,' <a href="http://$1">$1</a>', $str);
        
        $phone='/(\+(?:[0-9] ?){6,14}[0-9])/';
        $str=preg_replace($phone,'<span dir="ltr">$1</span>', $str);
        
        return $str;
    }

    function detectPhone($ad){
        $matches=null;
        preg_match_all('/([\/0-9-]{8,})/', $ad, $matches);
        return $matches;
    }

    function detectEmail($ad){
        $matches=null;
        preg_match_all('/(?:[_a-z0-9-]+(?:(?:\s|)\.(?:\s|)[_a-z0-9-]+)*(?:\s|)@(?:\s|)[a-z0-9-]+(?:(?:\s|)\.(?:\s|)[a-z0-9-]+)*(?:(?:\s|)\.(?:\s|)[a-z]{2,4})*)(?:\s|$)/i', $ad,$matches);
        return $matches;
    }

}
?>