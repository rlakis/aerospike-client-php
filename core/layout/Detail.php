<?php

Config::instance()->incLayoutFile('Search');

use Core\Model\Classifieds;

class Detail extends Search {

    function __construct() {
        parent::__construct();
    }

    
    function header() : void {
        
        if ($this->detailAdExpired) {
            parent::header();
            return;
        }
        
        $description=\trim(\preg_replace("/<\s*\w.*?>(.*?)(<\s*\/\s*\w\s*.*?>|<\s*br\s*>)/", "", $this->detailAd->text()));
        
        $text=$this->detailAd->content().' - '.$this->detailAd->altContent();
        if (\preg_match('/(?:tobacco|cigarette|shisha|gun|bullet|شيشة|شيشه|سلاح|رشاش|بارود|فحم)/i', $text)) {
            $this->router->config->disableAds();
        }
        
        $pos=\mb_strrpos($description,'/',0,'UTF-8');
        if ($pos) {
            $description=\mb_substr($description, 0, $pos-1,'UTF-8');
        }
        
        $this->lang['description']=\trim($description);
        
        $this->title=\trim($this->title);

        $this->lang['description']=\trim(\mb_substr($this->lang['description'], \mb_strlen($this->title,'UTF-8'), \mb_strlen($this->lang['description'], 'UTF-8'), 'UTF-8'));
        
        parent::header();
                
        $pageThumb=$this->router->config->imgURL."/200/".$this->detailAd->sectionId().$this->router->_png;
                
        ?><meta property="og:title" content="<?=$this->title?>" /><?php 
        ?><meta property="og:url" content="<?=$this->router->config->host.$this->router->uri.$this->detailAd->id().'/'?>" /><?php 
        ?><meta property="og:type" content="product" /><?php 
        ?><meta property="og:site_name" content="Mourjan.com" /><?php
        if ($this->detailAd->hasPictures()) {
            $pc=$this->detailAd->picturesCount();
            for ($i=0; $i<$pc; $i++) {
                ?><meta property="og:image" content="<?=$this->router->config->assetsURL.'/repos/d/'.$this->detailAd->picturePath($i)?>" /><?php
            }           
            
        }     
        else { 
            ?><meta property="og:image" content="<?=$pageThumb?>" /><?php
        }
        ?><meta property="og:description" content="<?=$this->lang['description']?>" /><?php
        ?><meta property="og:admins" content="support@mourjan.com" /><?php 
        ?><meta property="fb:admins" content="682495312" /><?php 
    }



    function mainMobile() {
        $this->displayDetailMobile();
        $iDir=  $this->urlRouter->siteLanguage=='ar' ? 'ad_r' :'ad_l';
        if (!$this->detailAdExpired) {
            echo '<br />'.$this->fill_ad('Square','ad_dt '.$iDir).'<br />';
        }
        parent::resultsMobile();
        if($this->detailAdExpired && $this->searchResults['body']['total_found'])
            echo '<br />'.$this->fill_ad('Square', $iDir).'<br />';
    }
    
    
    function displayDetail() : void {
        if ($this->detailAdExpired) {  return;  }
        
        if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp']=[]; }
        $loggedId= $this->user->isLoggedIn();
                    
        if ($loggedId) {
            if (!($this->user->level()===9||$this->user->id()===$this->detailAd->uid())) {
                $this->stat['ad-clk']=$this->detailAd->id();
                $this->stat['ad-imp'][]=$this->detailAd->id();
            }
        } 
        else {
            $this->stat['ad-clk']=$this->detailAd->id();
            $this->stat['ad-imp'][]=$this->detailAd->id();
        }
            
        $picsCount=0;
        $vWidth=250;
        //$showMap=(isset ($_GET['map']) && $_GET['map']==='on');
            
        if ($this->detailAd->hasPictures()) {
            $picsCount= $this->detailAd->picturesCount();
        }
        else {
            $vWidth+=40;
        }
                                 
        $para_class=$this->detailAd->rtl() ? 'ar': 'en';
        if ($this->router->siteTranslate) { $para_class=''; }
            
        $adSection=$this->getAdSection($this->detailAd, false, true);
            
        $favLink = '';
        $isFavorite=0;
        
        if ($loggedId && $this->user->favorites) {
            if (\in_array($this->detailAd->id(), $this->user->favorites)) {
                $favLink = "<span class='i fav on'></span><span>{$this->lang['removeFav']}</span>";
                //$divClass.= 'fon ';
                $isFavorite=true;
            }                
        }
        
        if (!$favLink) {
            $favLink = "<span class='i fav'></span><span>{$this->lang['addFav']}</span>";
        }
        $favLink='<div class="d1" onclick="fv(this,1)">'.$favLink.'</div>';
        
        $abuseLink='';
        if ($this->user->isLoggedIn(9)) {
            if (!$this->detailAd->isFeatured() && !$this->detailAd->isBookedFeature()) {
                $abuseLink="<div class='d2' onclick='rpa(this,0,1)'><span class='i ab'></span><span>{$this->lang['reportAbuse']}</span></div>";
                if($this->user->isSuperUser() && $this->detailAd->uid() && $this->detailAd->data()[Classifieds::USER_RANK]<2) {
                    $abuseLink.="<div class='d2' onclick='rpa(this,0,1,".$this->detailAd->uid().")'><span class='fail'></span><span>{$this->lang['block']}</span></div>";
                }
            }
        }
        else {
            $abuseLink="<div class='d2' onclick='rpa(this,0,1)'><span class='i ab'></span><span>{$this->lang['reportAbuse']}</span></div>";
        }                               
                          
        ?><div class="dtad ff-rows"><div class="col-8 ff-cols cntnt"><?php 
            
        if ($this->detailAd->isFeatured()) {
            ?><div class="dtf"><span class="vpdi ar"></span> <?= $this->lang['premium_ad_dt'] ?></div><?php
        }
            
        if ($this->user->isLoggedIn(9)) {
            echo '<b class="dhr">Admin Controls </b>';
            ?><div><ul style="overflow:hidden"><?php
            ?><li class="fr" style="margin:5px 10px"><a href="/myads/?u=<?=$this->detailAd->uid()?>" class="bt fl">user ads: <?=$this->detailAd->uid()?></a></li><?php 
            ?></ul></div><?php
        }
            
        /*
        if ($this->router->config->get('enabled_sharing')) {
            echo '<!--googleoff: all-->';
            ?><div class='sha shas'><label><?= $this->lang['shareFriends'] ?></label><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php
            echo '<!--googleoff: all-->';
        }
        */  
                        
        if ($picsCount) {
            //echo '<b class=dhr>', $this->lang['adPics'], '</b>';
            $oPics=$this->detailAd->data()[Classifieds::PICTURES_DIM];
            $widths=[];
            if (\is_array($oPics) && !empty($oPics)) {
                for ($i=0; $i<$picsCount; $i++) {                        
                    if (isset($oPics[$i][0]) && $oPics[$i][1]) {
                        $oPics[$i][2]=$this->detailAd->picturePath($i);
                        $widths[$i]=$oPics[$i][0];
                    }
                }
                    
                if (!empty($widths)) {
                    if (!\array_multisort($widths, SORT_DESC, $oPics)) {
                        error_log($this->detailAd->id() . ' -> ' . \json_encode($widths));
                        error_log(\json_encode($oPics));
                    }
                }                
                
                ?><style type="text/css"><?php
                for ($i=0; $i<$picsCount; $i++) {
                    if (isset($oPics[$i][0]) && $oPics[$i][1]) {
                        if ($oPics[$i][0] > 448) {
                            $width=448;
                            $height=\floor($width*$oPics[$i][1]/$oPics[$i][0]); 
                            ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $width ?>px;height:<?= $height ?>px;display:inline-block}<?php
                        }
                        else {
                            ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $oPics[$i][0] ?>px;height:<?= $oPics[$i][1] ?>px;display:inline-block}<?php
                        }
                    }
                }
                
                ?>@media all and (min-width:1250px) {<?php
                    for ($i=0; $i<$picsCount; $i++) {
                        if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                if($oPics[$i][0] > 650){
                                    $width = 650;
                                    $height = floor($width * $oPics[$i][1] / $oPics[$i][0]); 
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $width ?>px;height:<?= $height ?>px;display:inline-block}<?php
                                }else{
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $oPics[$i][0] ?>px;height:<?= $oPics[$i][1] ?>px;display:inline-block}<?php
                                }
                            }
                        }
                        ?>}<?php
                ?></style><?php
                
                ?><div id=pics class=pics><?php
                $pix=$this->detailAd->picturePath();
                if ($this->router->isAcceptWebP) { $pix=\preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pix); }
                ?><img class=col-12 src="<?=$this->router->config->adImgURL.'/repos/d/'.$pix?>" /><?php
                if ($picsCount>1) {
                    ?><div class="thumbs"><?php
                    for ($i=0; $i<$picsCount; $i++) {
                        $pix=$this->detailAd->picturePath($i);
                        if ($this->router->isAcceptWebP) { $pix=\preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pix); }
                        ?><img class=col-12 src="<?=$this->router->config->adImgURL.'/repos/s/'.$pix?>" /><?php
                    }
                    ?></div><?php
                }
                ?></div><?php 
            }
        }

        ?><div class="social"><?php
        echo $favLink;
        if ($this->router->cfg['enabled_sharing']){
            echo '<!--googleoff: all-->';
            ?><div class='sha shas shab'><label><?= $this->lang['shareFriends'] ?></label><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php
            echo '<!--googleoff: all-->';
        }
        ?></div><?php
        
        ?><p class=info>Details & Description</p><?php
        ?><div class="attrs"><?php
        ?><div class="widget"><?php
        ?></div><?php
        ?><div class="widget"><?php
        ?></div><?php
        ?><div class="widget"><?php
        ?></div><?php
        ?><div class="widget"><?php
        ?></div><?php
        ?></div><?php
        
        $hasMap=($this->detailAd->latitude()!==0||$this->detailAd->longitude()!==0);
            
        ?><div class="txt"><?php 
        echo $adSection;
        //$text=$this->detailAd->content();
        //$this->replacePhonetNumbers($text, $this->detailAd->countryCode(), $this->detailAd->[Classifieds::TELEPHONES][0], $this->detailAd[Classifieds::TELEPHONES][1], $this->detailAd[Classifieds::TELEPHONES][2], $this->detailAd->emails());
        ?><p class='dtp <?= $para_class ?>'><?= $this->detailAd->content() ?></p><?php 
        ?></div><?php
               
        ?><div class="opt"><?php
        
                echo $abuseLink;
                if ($this->detailAd->publisherType()!==0 && \in_array($this->detailAd->rootId(), [1,2,3])) {
                    switch ($this->detailAd->publisherType()) {
                        case 3:
                            echo '<div class="d2 ut" onclick="doChat()"><span class="i i'.$this->detailAd->rootId().'"></span><span>'.$this->lang['pub_3_'.$this->detailAd->rootId()].'</span></div>';
                            break;
                        case 1:
                            if ($this->detailAd->isJob()) {
                                echo '<div class="d2 ut"><span class="i p"></span><span>'.$this->lang['bpub_1'].'</span></div>';
                            }
                            else {
                                echo '<div class="d2 ut"><span class="i p"></span><span>'.$this->lang['pub_1'].'</span></div>';
                            }
                            break;
                        default:
                            //echo '<div class="ms ut"><span class="i i1"></span> '.$this->lang['pub_1'].'</div>';
                            break;
                    }
                }
            ?></div><?php
            ?><div class="drd"><?php
                    echo '<b class="fl" st="'.$this->detailAd->epoch().'"></b>';
                    //echo $pub_link;
                    //echo ($this->hasCities && $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? "<a class='fl' href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID],$this->detailAd[Classifieds::CITY_ID])."'>" . $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]."</a>":"")."<a class='fl' href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID])."'>" . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]."</a>" 
            ?></div><?php
            
            
            if ($hasMap) {
                echo '<b class=dhr>', $this->lang['adMap'], '</b>';
                if ($this->detailAd->location()) {
                    ?><div class="oc ocl"><span class="i loc"></span><?= $this->detailAd->location() ?></div><?php
                }
                ?><div class="mph"><div id="map" class="load"></div></div><?php
            }
            ?></div><div class="col-4 banners">Banners here<?php
            ?></div></div><?php
        
    }
    

    
    function displayDetailMobile() {
        if (!$this->detailAdExpired) {
                        
            $current_time = time();
            $isFeatured = $current_time < $this->detailAd[Classifieds::FEATURE_ENDING_DATE];
            $isFeatureBooked = $current_time < $this->detailAd[Classifieds::BO_ENDING_DATE];            
            
            //if ($this->detailAd[Classifieds::PUBLICATION_ID]==1) {
            if (isset($this->user->info['level'])) {
                if (!($this->user->info['level']==9 || $this->user->info['id']==$this->detailAd[Classifieds::USER_ID])) {
                    if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = array(); }
                     $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                     $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
                }
            } 
            else {
                if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = array(); }
                $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
            }
            //}
            $favSpan='';
            if ($this->user->info['id']) {
                if ($this->user->favorites) {
                    if (in_array($this->detailAd[Classifieds::ID],$this->user->favorites)) {
                        $favSpan='<span class="k fav on"></span>';
                    }
                }
            }
            
            $pics=array();
            $picsCount=0;
            $hasVideo=0;
            if ($this->detailAd[Classifieds::PICTURES]) {
                $pics=$this->detailAd[Classifieds::PICTURES];
            }
            $picsCount=count($pics);
            if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]) {
                $hasVideo=1;
            }
                        
            $hasEmail=false;
            $hasPhone=false;
            $itemScope='';
            $itemDesc='';
            $hasSchema=false;
            if ($this->detailAd[Classifieds::ROOT_ID]==1) {
                $hasSchema=true;
                $itemDesc='itemprop="description" ';
                $itemScope=' itemscope itemtype="https://schema.org/Product"';
            }
            elseif ($this->detailAd[Classifieds::ROOT_ID]==2) {
                $hasSchema=true;
                $itemDesc='itemprop="description" ';
                $itemScope=' itemscope itemtype="https://schema.org/Product"';
            }
            elseif ($this->detailAd[Classifieds::ROOT_ID]==3) {
                if ($this->detailAd[Classifieds::PURPOSE_ID]==3) {
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="https://schema.org/JobPosting"';
                }
                elseif ($this->detailAd[Classifieds::PURPOSE_ID]==4) {
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="https://schema.org/Person"';
                }
            }
    
            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';

            $emails=$this->detectEmail($this->detailAd[Classifieds::CONTENT]);
            if (count($emails) && !empty ($emails[0])) {
                $emails=$emails[0];
                $hasEmail=true;
            }
            $numMatches=null;
        
            //$this->replacePhonetNumbers($this->detailAd[Classifieds::CONTENT], $this->detailAd[Classifieds::PUBLICATION_ID], $this->detailAd[Classifieds::COUNTRY_CODE], $this->detailAd[Classifieds::TELEPHONES][0], $this->detailAd[Classifieds::TELEPHONES][1], $this->detailAd[Classifieds::TELEPHONES][2],$this->detailAd[Classifieds::EMAILS],$numMatches);        
            ?><div id="<?= $this->detailAd[Classifieds::ID] ?>" class="dt sh"<?= $itemScope ?>><?php 
                                
            if($isFeatured) {
                ?><div class="dtf"><span class="ic r102"></span> <?= $this->lang['premium_ad_dt'] ?></div><?php
            }

            $hasMap=false;
            $hasMap = ($this->detailAd[Classifieds::PUBLICATION_ID]==1 && ($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]));
            $os=0;
            if ($hasVideo || $hasMap){
                $os=preg_match('/(android|iphone)/i', $_SERVER['HTTP_USER_AGENT'], $matches);
                if($os) { $os=strtolower($matches[1]); }
            }
                                
            if ($hasVideo) {
                //if ($picsCount) {
                echo '<h3 class="ctr">'.$this->lang['adVid'].'</h3>';
                ?><div class="dim"><?php
                    ?><div id="vid"><?php
                    $pic=$this->detailAd[Classifieds::VIDEO][2];
                    $matches=null;
                    //var_dump($this->detailAd[Classifieds::VIDEO][1]);
                    $vId=preg_match('/\/v\/([a-zA-Z0-9]*?)\?/', $this->detailAd[Classifieds::VIDEO][1], $matches);

                    $vurl=$this->detailAd[Classifieds::VIDEO][1];
                    $os=0;
                    if ($vId) {
                        $vId=$matches[1];

                        if ($os) {
                            switch($os){
                                case 'iphone':
                                    $vurl='youtube:'.$vId;
                                    break;
                                case 'android':
                                    $vurl='vnd.youtube:'.$vId;
                                    break;
                                default:
                                    break;
                            }
                        }
                    }    

                    ?><a href="<?= $vurl ?>"><img width="300px" src="<?= $pic ?>" /><span class="play"></span></a><?php 
                    ?></div><?php
                    ?></div><?php
                }
            
                $this->globalScript.='var imgs=[];';

                if ($picsCount) {
                    echo '<h3 class="ctr">'.$this->lang['adPics'].'</h3>';
                    $oPics=$this->detailAd[Classifieds::PICTURES_DIM];
                    $widths=array();
                    if (is_array($oPics) && count($oPics)) {
                        for ($i=0; $i<$picsCount; $i++) {
                            if (isset($oPics[$i][0]) && $oPics[$i][1]) {
                                $oPics[$i][2]=$pics[$i];
                                $widths[$i]=$oPics[$i][0];
                            }
                        }
                        if (!array_multisort($widths, SORT_DESC, $oPics)) {
                            error_log(__CLASS__.'.'.__FUNCTION__.' id:'.$this->detailAd[Classifieds::ID]. ', line ['.__LINE__.'] '. PHP_EOL . json_encode($widths) . PHP_EOL . json_encode($oPics));
                        }

                        ?><style type="text/css"><?php
                        for ($i=0; $i<$picsCount; $i++) {
                            if (isset($oPics[$i][0]) && $oPics[$i][1]) {
                                if($oPics[$i][0] > 300) {
                                    $width = 300;
                                    $height = floor($width * $oPics[$i][1] / $oPics[$i][0]); 
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $width ?>px;height:<?= $height ?>px;display:inline-block}<?php
                                }
                                else {
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $oPics[$i][0] ?>px;height:<?= $oPics[$i][1] ?>px;display:inline-block}<?php
                                }
                            }
                        }
                        ?></style><?php
                        ?><div id="pics" class="dim"><?php
                        for ($i=1;$i<=$picsCount;$i++) {
                            if(isset($oPics[$i-1][0]) && $oPics[$i-1][1]){
                                if($this->urlRouter->isAcceptWebP){
                                    $oPics[$i-1][2] = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $oPics[$i-1][2]);
                                }
                                $this->globalScript.='imgs['.$i.']="'.$oPics[$i-1][2].'";';
                                ?><span class="sp<?= $i ?> load"></span><?php
                            }
                        }
                        ?></div><?php 
                    }
                }
                
                                
                ?><p <?= $itemDesc ?>class='<?= $para_class ?>'><?= $this->detailAd[Classifieds::CONTENT] ?></p><?php                
                ?><div class="pad"><?php 
                
                if ($hasMap) {
                    $link='q='.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].'&ll='.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].'&z=17';
                    $isBlank=true;
                    if ($os) {
                        switch ($os) {
                                case 'iphone':
                                    $link='http//maps.apple.com/?'.$link;
                                    break;
                                case 'android':
                                    $link='maps:'.$link;
                                default:
                                    $link='http://maps.google.com/maps?'.$link;
                                    break;
                        }
                    }
                    else {
                        $link='http://maps.google.com/maps?'.$link;                            
                    }
                    echo '<a '.( $isBlank ? 'target="_blank" ':'').'class="bt lk" href=\''.$link.'\'><span class="k loc"></span>',$this->lang['locOnMap'],'</a>';
                }  
                
                if ($numMatches) {
                    echo $numMatches;
                    /*foreach ($numMatches as $num){
                        //if ($initNum) echo '<br /><br />';
                        //echo '<a class="bt" href=\'tel:',$num,'\'>',$this->lang['call'],' <span class="pn">',$num,'</span></a>';
                        echo '<a class="bt" href=\'tel:',$num,'\'><span class="k call"></span> <span class="pn">',$num,'</span></a>';
                        //$initNum=true;
                    }*/
                }
                if ($hasEmail) {
                    foreach ($emails as $email){
                        $email=preg_replace('/ /', '', $email);
                        //if ($initNum) echo '<br /><br />';
                        echo "<a class='bt' href='mailto:{$email}'><span class='k mail'></span> ", $this->lang['emailOwner'], '</a>';
                        //$initNum=true;
                    }
                }
                ?></div><?php 
                
                /*if ($this->urlRouter->cfg['enabled_sharing']) {
                    ?><div class="sha"><span  class='st_email_large'></span><span  class='st_twitter_large' ></span><span  class='st_facebook_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php 
                   /* ?><div class='hd sha rct'><div class="addthis_toolbox addthis_32x32_style"><a class="addthis_button_email"></a><a class="addthis_button_twitter"></a><a class="addthis_button_facebook"></a><a class="addthis_button_google_plusone_share"></a><a class="addthis_button_linkedin"></a><a class="addthis_button_compact"></a></div></div><?php */
                //} 
                
                ?><div class="sbx"><?php 
                ?><div class="bts"><?php
                    if ($this->user->info['id']) {
                        ?><div onclick="aF(this)" class="button" <?= $favSpan ? 'style="display:none"':'' ?>><span class="k fav"></span><label><?= $this->lang['m_addFav'] ?></label></div><?php
                        ?><div onclick="rF(this)"  class="button" <?= $favSpan ? '':'style="display:none"' ?>><span class="k fav on"></span><label><?= $this->lang['removeFav'] ?></label></div><?php    
                    }else {
                        /*?><div id="dFB" onclick="pF(this)" class="button"><span class="k fav"></span><label><?= $this->lang['m_addFav'] ?></label></div><?php*/
                    }
                    /*?><div onclick="share(this)" class="button"><span class="k share"></span><label><?= $this->lang['share'] ?></label></div><?php */
                    if($this->user->info['id'] && $this->user->info['level']==9){
                        if(!$isFeatured && !$isFeatureBooked){
                            ?><div onclick="rpA(this)" class="button"><span class="k spam"></span><label><?= $this->lang['reportAbuse'] ?></label></div><?php 
                        }
                    }else{
                        ?><div onclick="rpA(this)" class="button"><span class="k spam"></span><label><?= $this->lang['reportAbuse'] ?></label></div><?php
                    }
                    
                    $subj=($this->urlRouter->siteLanguage=='ar'?'وجدت هذا الاعلان على مرجان':'found this ad on mourjan');
                    $whats_msg=urlencode($subj.' '.'https://www.mourjan.com/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage)."/{$this->detailAd[Classifieds::ID]}/?utm_source=whatsapp");
                    $viber_msg=urlencode($subj.' '.'https://www.mourjan.com/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage)."/{$this->detailAd[Classifieds::ID]}/?utm_source=viber");
                    
                    ?><a class="shr shr-wats" href="whatsapp://send?text=<?= $whats_msg ?>" data-action="share/whatsapp/share"></a><?php
                    ?><a class="shr shr-vb" href="viber://forward?text=<?= $viber_msg ?>"></a><?php
                    /*?><div><span class="k eye"></span><label><?= $this->lang['m_addFollow'] ?></label></div><?php 
                    ?><div><span class="k eye on"></span><label><?= $this->lang['m_Followed'] ?></label></div><?php*/
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
                if(!$this->user->info['id']) {
                    ?><div class="txtd bts lu"><?php 
                    ?><h2><?= $this->lang['signin_f'] ?></h2><?php
                    ?></div><?php
                }
                ?></div><?php 
                
                ?><div class='src <?= $this->urlRouter->siteLanguage ?>'><span><?= /*($this->urlRouter->siteLanguage=='ar' ? 'موقع مرجان':'mourjan.com') .*/
                ($this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]]['name'] ? "{$this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]}":"")." - ".$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]]['name'];?></span> <time st='<?= $this->detailAd[Classifieds::UNIXTIME] ?>'></time><?= $favSpan ?></div><?php 
                                                
                ?></div><?php
                
                ?><div id="call_node" class="dialog rc sh"><?php
                            ?><div class="dialog-title">call </div><?php
                            ?><div class="dialog-box"></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /></div><?php 
                    ?></div><?php
    /* ?></div><?php */
            }
    }

    
    function renderTextLinks($str){
        if (!$this->isMobile) {
            $email='/([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})*)(\s|$)/i';
            $str=preg_replace($email,' <a href="mailto:$1">$1</a> ', $str);
        }
        $url='/\s(www\.[a-zA-Z0-9\-\.]+\.(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)(\s|$))/i';
        $str=preg_replace($url,' <a href="http://$1">$1</a> ', $str);
        
        $phone='/(\+(?:[0-9] ?){6,14}[0-9])/';
        $str=preg_replace($phone,' <span dir="ltr">$1</span> ', $str);
        
        return $str;
    }

    
    function detectPhone($ad){
        $matches=null;
        preg_match_all('/([+\/0-9-]{8,})/', $ad, $matches);
        return $matches;
    }

    
    function detectEmail($ad){
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/i', $ad,$matches);
        return $matches;
    }

    
    function BuildExcerpts(string $text, int $length=0, string $separator = '...') : string {
        if ($length) {
            $str_len=\mb_strlen($text, 'UTF-8');
            if ($str_len>$length) {                
                $text=\trim(preg_replace('/\x{200B}.*/u', '', $text));
                $text=\trim(preg_replace('/[\-+=<>\\&:;,.]$/', '', $text));
                
                $str_len=\mb_strlen($text, 'UTF-8');
                if ($str_len>$length) {
                    $text=\mb_substr($text, 0, $length, 'UTF-8');
                    $lastSpace=\strrpos($text, ' ');
                    $text=\substr($text, 0, $lastSpace);
                    $text=\trim(preg_replace('/[\-+=<>\\&:;,.]$/', '', $text));
                }
                
                $text=\trim($text).$separator;
            }
        }
        return $text;
    }
}

?>