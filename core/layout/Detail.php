<?php

include $config['dir'].'/core/layout/Search.php';

use Core\Model\Classifieds;

class Detail extends Search
{


    function __construct(Core\Model\Router $router)
    {
        parent::__construct($router);
    }

    
    function header()
    {
        
        if ($this->detailAdExpired) 
        {
            parent::header();
            return;
        }
        
        $description = $this->detailAd[Classifieds::CONTENT];
        $description = trim(preg_replace("/<\s*\w.*?>(.*?)(<\s*\/\s*\w\s*.*?>|<\s*br\s*>)/", "", $description));
        
        $text = $this->detailAd[Classifieds::CONTENT]. ' - ' . $this->detailAd[Classifieds::ALT_CONTENT];
        if(preg_match('/(?:tobacco|cigarette|shisha|gun|bullet|شيشة|شيشه|سلاح|رشاش|بارود|فحم)/i', $text)){
            $this->urlRouter->cfg['enabled_ads']=false;
        }
        
        $pos = mb_strrpos($description,'/',0,'UTF-8');
        if($pos){
            $description = mb_substr($description, 0, $pos-1,'UTF-8');
        }
        
        $this->lang['description'] = trim($description);
        
        $this->title = trim($this->title);

        $this->lang['description'] = trim(mb_substr($this->lang['description'], mb_strlen($this->title,'UTF-8'),mb_strlen($this->lang['description'],'UTF-8'),'UTF-8'));
        
        parent::header();
        
        
        //$this->urlRouter->db->increment('read_'.$this->detailAd[Classifieds::ID]);
//        $pageThumb='';
//        if (isset($this->detailAd[Classifieds::PICTURES]) && $this->detailAd[Classifieds::PICTURES]){
//            $pageThumb=$this->urlRouter->cfg['url_ad_img'].'/repos/d/'.$this->detailAd[Classifieds::PICTURES][0];
//        }elseif (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]){
//            $pageThumb=$this->detailAd[Classifieds::VIDEO][2];
//        }else {
            $pageThumb=$this->urlRouter->cfg["url_img"]."/200/".$this->detailAd[Classifieds::SECTION_ID].$this->urlRouter->_png;
//        }
        
        /*$this->adTitle = "<span itemprop='name'>{$this->urlRouter->sections[$this->detailAd[Classifieds::SECTION_ID]][$this->fieldNameIndex]}</span> - " .
            $this->urlRouter->purposes[$this->detailAd[Classifieds::PURPOSE_ID]][$this->fieldNameIndex];*/
        
        
        if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]){
            ?><meta property="og:type" content="video"><?php
            ?><meta property="og:url" content="<?= $this->urlRouter->cfg['host'].$this->urlRouter->uri.$this->detailAd[Classifieds::ID].'/?auto_play=1' ?>" /><?php
            ?><meta property="og:video" content="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&amp;autohide=1"><?php
           /*  ?><meta property="og:image" content="<?= $this->detailAd[Classifieds::VIDEO][2] ?>"><?php */
            ?><meta property="og:title" content="<?= $this->title ?>" /><?php
            ?><meta property="og:description" content="<?= $this->lang['description'] ?>" /><?php
            ?><meta property="og:video:tag" content="<?= $this->sectionName ?>"><?php
           /* ?><meta property="og:admins" content="mourjan@berysoft.com" /><meta property="fb:admins" content="682495312" /><?php */
        }else {
            ?><meta property="og:title" content="<?= $this->title ?>" /><?php 
            ?><meta property="og:url" content="<?= $this->urlRouter->cfg['host'].$this->urlRouter->uri.$this->detailAd[Classifieds::ID].'/' ?>" /><?php 
            ?><meta property="og:type" content="product" /><?php 
            ?><meta property="og:site_name" content="Mourjan.com" /><?php 
            if (isset($this->detailAd[Classifieds::PICTURES]) && count($this->detailAd[Classifieds::PICTURES])){
                foreach($this->detailAd[Classifieds::PICTURES] as $pic){
                    ?><meta property="og:image" content="<?= $this->urlRouter->cfg['url_ad_img'].'/repos/d/'.$pic ?>" /><?php
                }
            }else{ 
                ?><meta property="og:image" content="<?= $pageThumb ?>" /><?php
            }
            ?><meta property="og:description" content="<?= $this->lang['description'] ?>" /><?php    
        }
            ?><meta property="og:admins" content="support@mourjan.com" /><?php 
            ?><meta property="fb:admins" content="682495312" /><?php 
        /* <meta property="og:locale:alternate" content="<?= ($this->detailAd[Classifieds::RTL] ? "ar_LB" : "en_US" ) ?>"/> */
    }
/*
    function _main_pane(){
        //$this->detailAd[Classifieds::PICTURES]="1226930_2.jpg|1226946_3.jpg|1226930_2.jpg|1226946_3.jpg";
        //parent::_main_pane();
        //echo $this->summerizeSearch();
        //$this->renderSideSections();
        $this->displayDetail();
        //parent::_main_pane();
        
    }
    
    function main_pane(){
        echo '<div>';
        parent::main_pane();
        echo '</div>';
    }
*/
    function mainMobile(){
        $this->displayDetailMobile();
        $iDir=  $this->urlRouter->siteLanguage=='ar' ? 'ad_r' :'ad_l';
        if(!$this->detailAdExpired)
            echo '<br />'.$this->fill_ad('Square','ad_dt '.$iDir).'<br />';
        parent::resultsMobile();
        if($this->detailAdExpired && $this->searchResults['body']['total_found'])
            echo '<br />'.$this->fill_ad('Square', $iDir).'<br />';
    }
    
    function displayDetail(){
        if (!$this->detailAdExpired) {
            $current_time = time();
            $isFeatured = $current_time < $this->detailAd[Classifieds::FEATURE_ENDING_DATE];
            $isFeatureBooked = $current_time < $this->detailAd[Classifieds::BO_ENDING_DATE];
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1) {
                if (isset($this->user->info['level'])) {
                    if (!($this->user->info['level']==9 || $this->user->info['id']==$this->detailAd[Classifieds::USER_ID])) {
                        if (!isset($this->stat['ad-imp']))
                            $this->stat['ad-imp'] = array(); 
                        $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                        $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
                    }
                } else {
                    if (!isset($this->stat['ad-imp']))
                            $this->stat['ad-imp'] = array();
                    $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                    $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
                }
            }
            $pics=null;
            $picsCount=0;
            $hasVideo=0;
            $hasMap=false;
            $showMap=false;
            $vWidth=250;
            if (isset ($_GET['map']) && $_GET['map']=='on') $showMap=true;
            if (isset($this->detailAd[Classifieds::PICTURES]) && $this->detailAd[Classifieds::PICTURES]){
                $pics=$this->detailAd[Classifieds::PICTURES];
            }else {
                $vWidth+=40;
            }
            if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]){
                $hasVideo=1;
            }
            $picsCount=count($pics);
            
            $pub_link = $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1 || $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]=='http://www.waseet.net/'){
                if ($this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]!='http://www.waseet.net/'){
                    $partnerInfo=$this->urlRouter->db->getCache()->get('partner_'.$this->detailAd[Classifieds::USER_ID]);
                    if (!$partnerInfo && isset($this->detailAd[Classifieds::USER_LEVEL]) && $this->detailAd[Classifieds::USER_LEVEL]) $partnerInfo=$this->user->getPartnerInfo($this->detailAd[Classifieds::USER_ID],true);
                    if ($partnerInfo){
                        $lang_1='ar';
                        $lang_2='en';
                        if ($this->urlRouter->siteLanguage=='en'){
                            $lang_1='en';
                            $lang_2='ar';
                        }
                        if (isset($partnerInfo['t'][$lang_1]) && $partnerInfo['t'][$lang_1]) {
                            $pub_link='<a class="fr" href="'.$this->urlRouter->cfg['host'].'/'.(isset($partnerInfo['uri']) && $partnerInfo['uri'] ? $partnerInfo['uri']: $this->urlRouter->basePartnerId+$this->detailAd[Classifieds::USER_ID] ).'/">'.$partnerInfo['t'][$lang_1].'</a>';
                        }elseif (isset($partnerInfo['t'][$lang_2]) && $partnerInfo['t'][$lang_2]) {
                            $pub_link='<a class="fr" href="'.$this->urlRouter->cfg['host'].'/'.(isset($partnerInfo['uri']) && $partnerInfo['uri'] ? $partnerInfo['uri']: $this->urlRouter->basePartnerId+$this->detailAd[Classifieds::USER_ID] ).'/">'.$partnerInfo['t'][$lang_2].'</a>';
                        }
                    }else{
                        $pub_link='<span class="mj">'.$pub_link.'</span>';
                    }
                }else{
                    $pub_link='<span class="mj">'.($this->urlRouter->siteLanguage=='ar' ? 'موقع مرجان':'mourjan.com').'</span>';
                }
            }else {
                if ($this->detailAd[Classifieds::OUTBOUND_LINK])
                    //$pub_link = "<a class='fr' onclick=\"ga('send', 'event', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}');_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}']);wn('{$this->detailAd[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                    $pub_link = "<a class='fr' onclick=\"ga('send', 'event', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}');wn('{$this->detailAd[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                elseif ($this->detailAd[Classifieds::PUBLICATION_ID]!=1)
                    $pub_link = "<a class='fr' onclick=\"ga('send', 'event', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}');wn('{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";
            }
            
            //else $pub_link='<b>'.$pub_link.'</b>';

            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';
            if ($this->urlRouter->siteTranslate)$para_class='';
            
            $adSection=$this->getAdSection($this->detailAd, 0, 1);
            
            /*?><div class="pr"<?= $itemScope?>><p <?= $itemDesc ?>class='<?= $para_class ?>'><?= $this->renderTextLinks($this->detailAd[Classifieds::CONTENT]) ?></p><p class='<?= $this->urlRouter->siteLanguage ?> lc'><?= $pub_link. ($this->hasCities && $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? " - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID],$this->detailAd[Classifieds::CITY_ID])."'>" . $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]."</a>":"")." - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID])."'>" . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]."</a> <b st='".$this->detailAd[Classifieds::UNIXTIME]."'></b>";?></p></div></div><?php */
            
            //$divClass='';
            $favLink = '';
            $isFavorite=0;
            if ($this->user->info['id']) {
                if ($this->user->favorites) {
                    if (in_array($this->detailAd[Classifieds::ID], $this->user->favorites)) {
                        $favLink = "<span class='i fav on'></span><span>{$this->lang['removeFav']}</span>";
                        //$divClass.= 'fon ';
                        $isFavorite=true;
                    }
                }
            }
            if(!$favLink){
                $favLink = "<span class='i fav'></span><span>{$this->lang['addFav']}</span>";
            }
            $favLink='<div class="d1" onclick="fv(this,1)">'.$favLink.'</div>';
            $abuseLink='';
            if($this->user->info['id'] && $this->user->info['level']==9){
                if(!$isFeatured && !$isFeatureBooked){
                    $abuseLink="<div class='d2' onclick='rpa(this,0,1)'><span class='i ab'></span><span>{$this->lang['reportAbuse']}</span></div>";
                    if($this->user->isSuperUser() && $this->detailAd[Classifieds::USER_ID] && $this->detailAd[Classifieds::USER_RANK]<2){
                        $abuseLink.="<div class='d2' onclick='rpa(this,0,1,".$this->detailAd[Classifieds::USER_ID].")'><span class='fail'></span><span>{$this->lang['block']}</span></div>";
                    }
                }
            }else{
                $abuseLink="<div class='d2' onclick='rpa(this,0,1)'><span class='i ab'></span><span>{$this->lang['reportAbuse']}</span></div>";
            }
            
            
            $renderedPics=0;
            
                    
                    
            ?><div class="dt sh"><?php 
            
            if($isFeatured){
                ?><div class="dtf"><span class="vpdi ar"></span> <?= $this->lang['premium_ad_dt'] ?></div><?php
            }
            if($this->user->info['id'] && $this->detailAd[Classifieds::USER_ID] && $this->user->info['level']==9){
                /*if(!$isFeatured && !$isFeatureBooked){
                $this->globalScript.=' 
                    var blockUser=function(e,id){
                        if(confirm("Block User?")){
                            $(e).addClass("load");
                            $.ajax({
                                type:"POST",
                                url:"/ajax-ublock/",
                                data:{i:'.$this->detailAd[Classifieds::USER_ID].',msg:"Blocked From Detail Page <'.$this->detailAd[Classifieds::USER_ID].'> By Admin '.$this->user->info['id'].'"},
                                dataType:"json",
                                success:function(rp){
                                    if (rp.RP) {
                                        $(e).parent().parent().parent().html("User Blocked"); 
                                    }else {
                                        $(e).removeClass("load");
                                    }
                                },
                                error:function(){
                                    $(e).removeClass("load");                                   
                                }
                            });
                        }
                    };
                ';
                }*/
                echo '<b class="dhr">Admin Controls </b>';
                ?><div><ul style="overflow:hidden"><?php
                ?><li class="fr" style="margin:5px 10px"><a href="/myads/?u=<?= $this->detailAd[Classifieds::USER_ID] ?>" class="bt fl">user ads: <?= $this->detailAd[Classifieds::USER_ID] ?></a></li><?php 
                /*
                if(!$isFeatured && !$isFeatureBooked && $this->detailAd[Classifieds::USER_RANK]<2){
                ?><li class="fr" style="margin:5px 10px"><input style="height:auto!important;line-height:35px!important" type="button" class="bt" onclick="blockUser(this,<?= $this->detailAd[Classifieds::USER_ID] ?>)" value="Block User" /></li><?php
                }*/
                ?></ul></div><?php
            }
            if ($this->urlRouter->cfg['enabled_sharing']) {
                echo '<!--googleoff: all-->';
                ?><div class='sha shas'><label><?= $this->lang['shareFriends'] ?></label><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php
                echo '<!--googleoff: all-->';
            }
            
            
            //if(isset($this->detailAd[Classifieds::PICTURES_DIM]) && is_array($this->detailAd[Classifieds::PICTURES_DIM]) && (count($this->detailAd[Classifieds::PICTURES_DIM])==$picsCount )){
                
                $autoplay=$this->get('auto_play','boolean');
                $videoId = preg_match('/(?:youtube\.com|youtu\.be).*?v=(.*?)(?:$|&)/i', $this->detailAd[Classifieds::VIDEO][1], $matches);
                        
                if ($hasVideo && isset($matches[1]) && $matches[1]!=''){
                    echo '<b class="dhr">'.$this->lang['adVid'].'</b>';
                    ?><div id="vid" class="video"><?php 
                    ?><iframe width="648" height="366" src="https://www.youtube.com/embed/<?= $matches[1] ?>" frameborder="0" allowfullscreen></iframe><?php
                        /* ?><object width="648" height="366"><param name="movie" value="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1"</param><param name="allowFullScreen" value="true"></param><param name='wmode' value='transparent'></param><param name="allowScriptAccess" value="always"></param><embed src="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="648" height="366"></embed></object><?php */
                    ?></div><?php
                }
                $this->globalScript.='var imgs=[];';
                if($picsCount){
                    echo '<b class="dhr">'.$this->lang['adPics'].'</b>';
                    $oPics=$this->detailAd[Classifieds::PICTURES_DIM];
                    $widths=array();
                    if(is_array($oPics) && count($oPics)){
                        for($i=0;$i<$picsCount;$i++){
                            if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                $oPics[$i][2]=$pics[$i];
                                $widths[$i]=$oPics[$i][0];
                            }
                        }
                        array_multisort($widths, SORT_DESC, $oPics);

                        ?><style type="text/css"><?php
                        for($i=0;$i<$picsCount;$i++){
                            if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                if($oPics[$i][0] > 448){
                                    $width = 448;
                                    $height = floor($width * $oPics[$i][1] / $oPics[$i][0]); 
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $width ?>px;height:<?= $height ?>px;display:inline-block}<?php
                                }else{
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $oPics[$i][0] ?>px;height:<?= $oPics[$i][1] ?>px;display:inline-block}<?php
                                }
                            }
                        }
                        ?>@media all and (min-width:1250px) {<?php
                        for($i=0;$i<$picsCount;$i++){
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
                        ?><div id="pics" class="pics"><?php
                        for($i=1;$i<=$picsCount;$i++){
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

                if( ($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]))  {
                    $hasMap=true;
                }

                //$renderedPics=1;
            //}
            
            ?><div class="txt"><?php 
                echo $adSection;
                $this->replacePhonetNumbers($this->detailAd[Classifieds::CONTENT], $this->detailAd[Classifieds::PUBLICATION_ID], $this->detailAd[Classifieds::COUNTRY_CODE], $this->detailAd[Classifieds::TELEPHONES][0], $this->detailAd[Classifieds::TELEPHONES][1], $this->detailAd[Classifieds::TELEPHONES][2],$this->detailAd[Classifieds::EMAILS]);

                //$this->processTextNumbers($this->detailAd[Classifieds::CONTENT],$this->detailAd[Classifieds::PUBLICATION_ID],$this->detailAd[Classifieds::COUNTRY_CODE]);
                
//                if($this->detailAd[Classifieds::RTL]){
//                    $words = explode(' ', $this->detailAd[Classifieds::CONTENT]);
//                    $this->detailAd[Classifieds::CONTENT] = implode(" ".chr(hexdec('20')).chr(hexdec('67')), $words);
//                }
                
                ?><p class='dtp <?= $para_class ?>'><?= $this->detailAd[Classifieds::CONTENT] ?></p><?php 
            ?></div><?php
                if ($this->urlRouter->cfg['enabled_sharing']){
                    echo '<!--googleoff: all-->';
                    ?><div class='sha shas shab'><label><?= $this->lang['shareFriends'] ?></label><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_sharethis_large' ></span></div><?php
                    echo '<!--googleoff: all-->';
                }
            ?><div class="opt"><?php
                echo $favLink;
                echo $abuseLink;
                if( isset($this->detailAd[Classifieds::PUBLISHER_TYPE]) && in_array($this->detailAd[Classifieds::ROOT_ID],[1,2,3])){
                        switch($this->detailAd[Classifieds::PUBLISHER_TYPE]){
                            case 3:
                                echo '<div class="d2 ut" onclick="doChat()"><span class="i i'.$this->detailAd[Classifieds::ROOT_ID].'"></span><span>'.$this->lang['pub_3_'.$this->detailAd[Classifieds::ROOT_ID]].'</span></div>';
                                break;
                            case 1:
                                if($this->detailAd[Classifieds::ROOT_ID] == 3){
                                    echo '<div class="d2 ut"><span class="i p"></span><span>'.$this->lang['bpub_1'].'</span></div>';
                                }else{
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
                    echo '<b class="fl" st="'.$this->detailAd[Classifieds::UNIXTIME].'"></b>';
                    echo $pub_link;
                    //echo ($this->hasCities && $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? "<a class='fl' href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID],$this->detailAd[Classifieds::CITY_ID])."'>" . $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]."</a>":"")."<a class='fl' href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID])."'>" . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]."</a>" 
            ?></div><?php
            /*
            if(!$renderedPics){
                $autoplay=$this->get('auto_play','boolean');
                if ($hasVideo){
                    ?><div id="vid" class="video"><?php
                        ?><object width="648" height="366"><param name="movie" value="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1"</param><param name="allowFullScreen" value="true"></param><param name='wmode' value='transparent'></param><param name="allowScriptAccess" value="always"></param><embed src="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="648" height="366"></embed></object><?php 
                    ?></div><?php
                }

                $this->globalScript.='var imgs=[];';
                if($picsCount){
                    ?><div id="pics" class="pics"><?php
                    for($i=1;$i<=$picsCount;$i++){
                        $this->globalScript.='imgs['.$i.']="'.$pics[$i-1].'";';
                    }
                    ?></div><?php 
                }

                if( ($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]))  {
                    $hasMap=true;
                }

                if ($hasMap) {
                    if(isset($this->detailAd[Classifieds::LOCATION])){
                        ?><div class="oc ocl"><span class="i loc"></span><?= $this->detailAd[Classifieds::LOCATION] ?></div><?php
                    }
                    ?><div class="mph"><div id="map" class="load"></div></div><?php
                }
            }*/
            
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1 && $this->urlRouter->cfg['enabled_disqus']) {
                ?><div class="dthd"><div id="disqus_thread"></div></div><?php 
            }
            
                if ($hasMap) {
                    echo '<b class="dhr">'.$this->lang['adMap'].'</b>';
                    if(isset($this->detailAd[Classifieds::LOCATION])){
                        ?><div class="oc ocl"><span class="i loc"></span><?= $this->detailAd[Classifieds::LOCATION] ?></div><?php
                    }
                    ?><div class="mph"><div id="map" class="load"></div></div><?php
                }
            ?></div><?php
//            if ($this->urlRouter->userId)
//                $this->partnerHeader ();
        }
    }
    function displayDetail_bk(){
        if (!$this->detailAdExpired) {
            if (isset($this->user->info['level'])) {
                if (!($this->user->info['level']==9 || $this->user->info['id']==$this->detailAd[Classifieds::USER_ID])) {
                     $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                }
            } else {
                $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
            }
//            if (!(isset($this->user->info['level']) && $this->user->info['level']==9) && $this->user->info['id']!=$this->detailAd[Classifieds::USER_ID])
//                $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
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
                    $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="https://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==2){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="https://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==3){
                    if ($this->detailAd[Classifieds::PURPOSE_ID]==3) {
                        $itemDesc='itemprop="description" ';
                        $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="https://schema.org/JobPosting"';
                   }elseif ($this->detailAd[Classifieds::PURPOSE_ID]==4) {
                      $itemDesc='itemprop="description" ';
                      $itemScope=' itemprop="mainContentOfPage" itemscope itemtype="https://schema.org/Person"';
                    }
                }
            if($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]) {
                $hasMap=true;
                /*if ($this->urlRouter->siteLanguage=='ar'){                
                    $detailPagination='<ul id="md" class="md md1"><li class="p rbrc"></li><li onclick="imap(this)" class="lm"><span class="loc"></span></li><li class="n rblc"></li></ul>';
                }else {*/
                    //$detailPagination='<ul id="md" class="md md1 sh"><li class="p"></li><li onclick="imap(this)" class="lm"><span class="loc"></span></li><li class="n"></li></ul>';
                //}
            }else {
                /*if ($this->urlRouter->siteLanguage=='ar'){                
                    $detailPagination='<ul id="md" class="md"><li class="p rbrc"></li><li class="n rblc"></li></ul>';
                }else {*/
                    //$detailPagination='<ul id="md" class="md sh"><li class="p"></li><li class="n"></li></ul>';                
                //}
            }
            ?><div class="dt"><?php 
            if (0 && $this->urlRouter->cfg['enabled_sharing']) {
                echo '<!--googleoff: snippet-->';
                //<span class='st_plusone_large gp'></span>
                ?><div class='share<?= $this->urlRouter->userId ?' rct':'' ?>'><?= $this->urlRouter->siteTranslate? '':'<label>'.$this->lang['shareFriends'].'</label>' ?><span  class='st_email_large' ></span><span  class='st_facebook_large' ></span><span  class='st_twitter_large' ></span><span class='st_googleplus_large'></span><span  class='st_linkedin_large' ></span><span  class='st_blogger_large' ></span><span  class='st_sharethis_large' ></span></div><?php
                /*?><div class='share sh rct'><?= $this->urlRouter->siteTranslate? '':'<label>'.$this->lang['shareFriends'].'</label>' ?><div class="addthis_toolbox addthis_32x32_style"><a class="addthis_button_email"></a><a class="addthis_button_facebook"></a><a class="addthis_button_twitter"></a><a class="addthis_button_google_plusone_share"></a><a class="addthis_button_linkedin"></a><a class="addthis_button_blogger"></a><a class="addthis_button_stumbleupon"></a><a class="addthis_button_compact"></a></div></div><?php*/
                echo '<!--googleon: snippet-->';
            }
            //echo $detailPagination;
            if ($hasMap) {
                ?><div class="mph<?= $showMap?'':' hid' ?>"><div id="map" class="<?= $showMap?' loading':'' ?>"></div></div><?php
                $this->globalScript.='var map,mapd,marker,rmap=false,geocoder,infoWindow;function initMap() {rmap=true;geocoder = new google.maps.Geocoder();infowindow = new google.maps.InfoWindow();var myOptions = {zoom:17,mapTypeId: google.maps.MapTypeId.HYBRID};map = new google.maps.Map(mapd[0], myOptions);marker = new google.maps.Marker({map: map,animation: google.maps.Animation.DROP});pos = new google.maps.LatLng('.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].');map.setCenter(pos);marker.setPosition(pos)};';
                $this->globalScript.='var imap=function(e){if (!rmap){var s=document.createElement("script");s.type="text/javascript";s.src = "//maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initMap&language='.$this->urlRouter->siteLanguage.'";document.body.appendChild(s)};e=$(e);if(e.hasClass("on")){e.removeClass("on");mapd.parent().slideUp()}else{e.addClass("on");mapd.parent().slideDown()}};';
                $this->inlineScript.='mapd=$("#map");';
                if ($showMap)$this->inlineScript.='imap($(".lm"),(mdd?mdd:$("#md")));';
            }
            $autoplay=$this->get('auto_play','boolean');
            if (0 && ($picsCount || $hasVideo)) {
                ?><div class="c cap"><?php
                    ?><div class="ap"><?php 
                        ?><div class="cvs<?= ($hasVideo && !$picsCount)?' cvv':'' ?>"><?php 
                            if ($hasVideo){
                                ?><div id="vid"><?php
                                   /* ?><object width='<?= $vWidth ?>' height='250'><param name='movie' value='<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&fs=1'></param><param name="allowFullScreen" value="true"></param><param name='wmode' value='transparent'></param><param name="allowScriptAccess" value="always"></param><embed src='<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&fs=1' type='application/x-shockwave-flash' wmode='transparent' width='<?= $vWidth ?>' height='250'></embed></object><?php */
                                    ?><object width="250" height="250"><param name="movie" value="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1"</param><param name="allowFullScreen" value="true"></param><param name='wmode' value='transparent'></param><param name="allowScriptAccess" value="always"></param><embed src="<?= $this->detailAd[Classifieds::VIDEO][1] ?>&autoplay=<?= $autoplay ?>&version=2&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="250" height="250"></embed></object><?php 
                                ?></div><?php
                            }
                        ?></div><?php
                $this->globalScript.='var imgs=[];var vdd=null;';
                if ($hasVideo && $picsCount) {
                    echo '<input type="button" onclick="vip(this)" class="bt play on" />';
                    $this->inlineScript.='cvu=$(".bt.play");';
                    if ($picsCount) $this->inlineScript.='vdd=$("#vid",cvu.parent());';
                    $this->globalScript.='var vip=function(e){noslide=1;if(gtm)clearTimeout(gtm);if (cvu){cvu.removeClass("on")};cvu=$(e);cvu.addClass("on");vdd.css("z-index",zx++);var o=vdd.children();o.css("position","relative");o.css("z-index",zx++);vdd.css("display","block")};';
                }
                //if (!$onePhoto) {
                    for($i=1;$i<=$picsCount;$i++){
                        $this->globalScript.='imgs['.$i.']="'.$pics[$i-1].'";';
                        if ($hasVideo || $picsCount>1)echo '<input type="button" onclick="gai('.$i.',this)" class="bt" value="'.$i.'" />';
                    }
                    $this->globalScript.='var cvs,cvu,gtm,cvc='.$picsCount.',zx=100,noslide='.$hasVideo.';var gai=function(i,e){noslide=0;if(vdd){vdd.css("display","none")};if (cvu){cvu.removeClass("on")};cvu=$(e);cvu.addClass("on");var g;if(!cvs) cvs=$(".cvs");if (typeof imgs[i]=="string") {g=$("<div class=\'dg\'></div>");g.css("background-image","url('.$this->urlRouter->cfg['url_ad_img'].'/repos/d/"+imgs[i]+")");g.css("z-index",zx++);cvs.append(g);imgs[i]=g;g.click(gan);g.fadeIn();}else {g=imgs[i];g.css("display","none");g.css("z-index",zx++);g.fadeIn();};if(gtm)clearTimeout(gtm);if(!noslide && cvc>1)gtm=setTimeout("gan()",10000);};var gan=function(){var n=cvu.next();if (n.length) n.trigger("click");else {$(cvu.parent().children()['.($hasVideo ? 2:1).']).trigger("click")}};';
                    if (!$hasVideo)$this->inlineScript.='gai(1,$(".ap > .bt")[0]);';
                //}
                ?></div><?php echo $this->fill_ad("zone_8",'ad_det');
                /*
                 * echo $this->fill_ad("zone_8",'ad_det');
            echo $this->fill_ad("zone_9",'ad_det adx');
                 */
            }
            /*else {
                ?><div class="cpp"><?php echo $this->fill_ad("zone_9",'ad_det adx');
            }*/
            $pub_link = $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1){
                $partnerInfo=$this->urlRouter->db->cacheGet('partner_'.$this->detailAd[Classifieds::USER_ID]);
                if (!$partnerInfo && isset($this->detailAd[Classifieds::USER_LEVEL]) && $this->detailAd[Classifieds::USER_LEVEL]) $partnerInfo=$this->user->getPartnerInfo($this->detailAd[Classifieds::USER_ID],true);
                if ($partnerInfo){
                    $lang_1='ar';
                    $lang_2='en';
                    if ($this->urlRouter->siteLanguage=='en'){
                        $lang_1='en';
                        $lang_2='ar';
                    }
                    if (isset($partnerInfo['t'][$lang_1]) && $partnerInfo['t'][$lang_1]) {
                        $pub_link='<a href="'.$this->urlRouter->cfg['host'].'/'.(isset($partnerInfo['uri']) && $partnerInfo['uri'] ? $partnerInfo['uri']: $this->urlRouter->basePartnerId+$this->detailAd[Classifieds::USER_ID] ).'/">'.$partnerInfo['t'][$lang_1].'</a>';
                    }elseif (isset($partnerInfo['t'][$lang_2]) && $partnerInfo['t'][$lang_2]) {
                        $pub_link='<a href="'.$this->urlRouter->cfg['host'].'/'.(isset($partnerInfo['uri']) && $partnerInfo['uri'] ? $partnerInfo['uri']: $this->urlRouter->basePartnerId+$this->detailAd[Classifieds::USER_ID] ).'/">'.$partnerInfo['t'][$lang_2].'</a>';
                    }
                }
            }else {
                if ($this->detailAd[Classifieds::OUTBOUND_LINK])
                    //$pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->detailAd[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                    $pub_link = "<a onclick=\"ga('send', 'event', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}');_m_sl('{$this->detailAd[Classifieds::OUTBOUND_LINK]}');\">{$pub_link}</a>";
                elseif ($this->detailAd[Classifieds::PUBLICATION_ID]!=1)
                    //$pub_link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}']);_m_sl('{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";
                    $pub_link = "<a onclick=\"ga('send', 'event', 'OutLinks', 'click', '{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][2]}');_m_sl('{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}');\">{$pub_link}</a>";
            }
            
            //else $pub_link='<b>'.$pub_link.'</b>';

            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';
            if ($this->urlRouter->siteTranslate)$para_class='';
            //$this->processTextNumbers($this->detailAd[Classifieds::CONTENT],$this->detailAd[Classifieds::PUBLICATION_ID],$this->detailAd[Classifieds::COUNTRY_CODE]);
            $this->replacePhonetNumbers($this->detailAd[Classifieds::CONTENT], $this->detailAd[Classifieds::PUBLICATION_ID], $this->detailAd[Classifieds::COUNTRY_CODE], $this->detailAd[Classifieds::TELEPHONES][0], $this->detailAd[Classifieds::TELEPHONES][1], $this->detailAd[Classifieds::TELEPHONES][2],$this->detailAd[Classifieds::EMAILS]);

            ?><div class="pr"<?= $itemScope?>><h1 class='<?= $this->urlRouter->siteLanguage ?>'><?= $this->adTitle ?></h1><p <?= $itemDesc ?>class='<?= $para_class ?>'><?= $this->detailAd[Classifieds::CONTENT] ?></p><p class='<?= $this->urlRouter->siteLanguage ?> lc'><?= $pub_link. ($this->hasCities && $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex] ? " - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID],$this->detailAd[Classifieds::CITY_ID])."'>" . $this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]."</a>":"")." - <a href='".$this->urlRouter->getURL($this->detailAd[Classifieds::COUNTRY_ID])."'>" . $this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]][$this->fieldNameIndex]."</a> <b st='".$this->detailAd[Classifieds::UNIXTIME]."'></b>";?></p></div></div><?php
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1 && $this->urlRouter->cfg['enabled_disqus']) {
                ?><div class="dthd"><div id="disqus_thread"><br /></div></div><?php 
            }
            ?></div><?php
            if ($this->urlRouter->userId)
                $this->partnerHeader ();
        }
    }

    
    function displayDetailMobile(){
        if (!$this->detailAdExpired) {
                        
            $current_time = time();
            $isFeatured = $current_time < $this->detailAd[Classifieds::FEATURE_ENDING_DATE];
            $isFeatureBooked = $current_time < $this->detailAd[Classifieds::BO_ENDING_DATE];            
            
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1) {
                if (isset($this->user->info['level'])) {
                    if (!($this->user->info['level']==9 || $this->user->info['id']==$this->detailAd[Classifieds::USER_ID])) {
                        if (!isset($this->stat['ad-imp']))
                            $this->stat['ad-imp'] = array();
                         $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                         $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
                    }
                } else {
                    if (!isset($this->stat['ad-imp']))
                        $this->stat['ad-imp'] = array();
                    $this->stat['ad-clk'] = $this->detailAd[Classifieds::ID];
                    $this->stat['ad-imp'][]=$this->detailAd[Classifieds::ID];
                }
            }
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
            if ($this->detailAd[Classifieds::PICTURES]){
                $pics=$this->detailAd[Classifieds::PICTURES];
            }
            $picsCount=count($pics);
            if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]){
                $hasVideo=1;
            }
            
            
            //$ad = $this->detailAd;
            /* ?><div class="rb ls rc dt"><?php */
            //$pub_link = $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex];
            $hasEmail=false;
            $hasPhone=false;
            /*$link='';
            if ($this->detailAd[Classifieds::PUBLICATION_ID]==1 || $this->urlRouter->publications[$ad[Classifieds::PUBLICATION_ID]][6]=='http://www.waseet.net/'){
                
            }else {
                if ($this->detailAd[Classifieds::OUTBOUND_LINK]) {
                    $link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$pub_link}']);\" href='{$this->detailAd[Classifieds::OUTBOUND_LINK]}' target='_blank' rel='nofollow'><div class='bt'>{$pub_link}</div></a>";
                }
                elseif ($this->detailAd[Classifieds::PUBLICATION_ID]) {
                    $link = "<a onclick=\"_gaq.push(['_trackEvent', 'OutLinks', 'click', '{$pub_link}']);\" href='{$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]}' target='_blank'><div class='bt'>{$pub_link}</div></a>";
                }
            }*/
                $itemScope='';
                $itemDesc='';
                $hasSchema=false;
                if ($this->detailAd[Classifieds::ROOT_ID]==1){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="https://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==2){
                    $hasSchema=true;
                    $itemDesc='itemprop="description" ';
                    $itemScope=' itemscope itemtype="https://schema.org/Product"';
                }elseif ($this->detailAd[Classifieds::ROOT_ID]==3){
                    if ($this->detailAd[Classifieds::PURPOSE_ID]==3) {
                        $itemDesc='itemprop="description" ';
                        $itemScope=' itemscope itemtype="https://schema.org/JobPosting"';
                   }elseif ($this->detailAd[Classifieds::PURPOSE_ID]==4) {
                      $itemDesc='itemprop="description" ';
                      $itemScope=' itemscope itemtype="https://schema.org/Person"';
                    }
                }
    
            $para_class = $this->detailAd[Classifieds::RTL] ? 'ar': 'en';
            /*
            $phoneNumbers=$this->detectPhone($this->detailAd[Classifieds::CONTENT]);
            if (count($phoneNumbers) && !empty($phoneNumbers[1]) ) {
                $phoneNumbers=$phoneNumbers[1];
                $hasPhone=true;
            }
*/
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
        $numMatches=null;
        
        //$this->processTextNumbers($this->detailAd[Classifieds::CONTENT],$this->detailAd[Classifieds::PUBLICATION_ID],$this->detailAd[Classifieds::COUNTRY_CODE],$numMatches);

        $this->replacePhonetNumbers($this->detailAd[Classifieds::CONTENT], $this->detailAd[Classifieds::PUBLICATION_ID], $this->detailAd[Classifieds::COUNTRY_CODE], $this->detailAd[Classifieds::TELEPHONES][0], $this->detailAd[Classifieds::TELEPHONES][1], $this->detailAd[Classifieds::TELEPHONES][2],$this->detailAd[Classifieds::EMAILS],$numMatches);

        
                ?><div id="<?= $this->detailAd[Classifieds::ID] ?>" class="dt sh"<?= $itemScope ?>><?php 
                
                
            if($isFeatured){
                ?><div class="dtf"><span class="ic r102"></span> <?= $this->lang['premium_ad_dt'] ?></div><?php
            }
                $hasMap=false;
                $hasMap = ($this->detailAd[Classifieds::PUBLICATION_ID] == 1 && ($this->detailAd[Classifieds::LATITUDE] || $this->detailAd[Classifieds::LONGITUDE]));
                $os=0;
                if($hasVideo || $hasMap){
                    $os=preg_match('/(android|iphone)/i', $_SERVER['HTTP_USER_AGENT'], $matches);
                    if($os){
                        $os=strtolower($matches[1]);
                    }
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
                                
                                if($os){
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
                            /*?><object type="application/x-shockwave-flash" data="<?= $this->detailAd[Classifieds::VIDEO][1] ?>" width="300" height="250"><?php
                            ?><param name="movie" value="<?= $this->detailAd[Classifieds::VIDEO][1] ?>" /><?php
                            ?><param name="quality" value="high" /><?php
                            ?><param name="allowFullScreen" value="true" /><?php
                            ?><!-- Fallback content --><?php
                            ?><a href="<?= $this->detailAd[Classifieds::VIDEO][1] ?>"><?php
                            ?><img src="<?= $this->detailAd[Classifieds::VIDEO][2] ?>" width="300" height="250" /><?php
                            ?><span class="play"></span><?php
                            ?></a><?php
                            ?></object><?php */
                            ?></div><?php
                        /*for($i=1;$i<=$picsCount;$i++){
                            if($i==1 && $hasVideo) echo '<br />';
                            ?><img src="<?= $this->urlRouter->cfg['url_ad_img'].'/repos/m/'.$pics[$i-1] ?>" /><?php
                        }*/
                    ?></div><?php
                }
                $this->globalScript.='var imgs=[];';
                
                if($picsCount){
                    echo '<h3 class="ctr">'.$this->lang['adPics'].'</h3>';
                    $oPics=$this->detailAd[Classifieds::PICTURES_DIM];
                    $widths=array();
                    if(is_array($oPics) && count($oPics)){
                        for($i=0;$i<$picsCount;$i++){
                            if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                $oPics[$i][2]=$pics[$i];
                                $widths[$i]=$oPics[$i][0];
                            }
                        }
                        array_multisort($widths, SORT_DESC, $oPics);

                        ?><style type="text/css"><?php
                        for($i=0;$i<$picsCount;$i++){
                            if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                if($oPics[$i][0] > 300){
                                    $width = 300;
                                    $height = floor($width * $oPics[$i][1] / $oPics[$i][0]); 
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $width ?>px;height:<?= $height ?>px;display:inline-block}<?php
                                }else{
                                    ?>.sp<?= $i+1 ?>,.sp<?= $i+1 ?> img{width:<?= $oPics[$i][0] ?>px;height:<?= $oPics[$i][1] ?>px;display:inline-block}<?php
                                }
                            }
                        }
                        ?></style><?php
                        ?><div id="pics" class="dim"><?php
                        for($i=1;$i<=$picsCount;$i++){
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
                
                    if($hasMap){
                        $link='q='.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].'&ll='.$this->detailAd[Classifieds::LATITUDE].','.$this->detailAd[Classifieds::LONGITUDE].'&z=17';
                        $isBlank=true;
                        if($os){
                            switch($os){
                                case 'iphone':
                                    $link='http//maps.apple.com/?'.$link;
                                    break;
                                case 'android':
                                    $link='maps:'.$link;
                                default:
                                    $link='http://maps.google.com/maps?'.$link;
                                    break;
                            }
                        }else{
                            $link='http://maps.google.com/maps?'.$link;                            
                        }
                         echo '<a '.( $isBlank ? 'target="_blank" ':'').'class="bt lk" href=\''.$link.'\'><span class="k loc"></span>',$this->lang['locOnMap'],'</a>';
                         //echo '<br /><br />';
                    }  
                
                //$initNum=false;
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
                    $whats_msg= urlencode($subj.' '.'https://www.mourjan.com/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage+'/').$this->detailAd[Classifieds::ID].'/?utm_source=whatsapp');
                    $viber_msg= urlencode($subj.' '.'https://www.mourjan.com/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage+'/').$this->detailAd[Classifieds::ID].'/?utm_source=viber');
                    
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
                
                ?><div class='src <?= $this->urlRouter->siteLanguage ?>'><span><?= (($this->detailAd[Classifieds::PUBLICATION_ID]==1 || $this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][6]=='http://www.waseet.net/')?
                ($this->urlRouter->siteLanguage=='ar' ? 'موقع مرجان':'mourjan.com'):$this->urlRouter->publications[$this->detailAd[Classifieds::PUBLICATION_ID]][$this->fieldNameIndex]) .
                ($this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]!=$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]]['name'] ? " - {$this->urlRouter->cities[$this->detailAd[Classifieds::CITY_ID]][$this->fieldNameIndex]}":"")." - ".$this->urlRouter->countries[$this->detailAd[Classifieds::COUNTRY_ID]]['name'];?></span> <time st='<?= $this->detailAd[Classifieds::UNIXTIME] ?>'></time><?= $favSpan ?></div><?php 
                
                
                
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

}
?>