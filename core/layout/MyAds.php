<?php
\Config::instance()->incLayoutFile('Page');

class MyAds extends Page {
    
    var $subSection='',$userBalance=0, $redis = null, $admins_online=[];
    
    var $editors = [
        1 => 'Bassel', 43905 => 'Bassel',
        2 => 'Robert', 69905 => 'Robert',
        2100 => 'Nooralex',
        477618 => 'Samir',
        38813 => 'Editor 1',
        44835 => 'Editor 2',
        53456 => 'Editor 3',
        166772 => 'Editor 4',
        516064 => 'Editor 5',
        897143 => 'Editor 6',
        897182 => 'Editor 7',
        1028732 => 'Editor 8'
    ];

    
    function __construct(Core\Model\Router $router) {
        parent::__construct($router);       
        
        if ($this->router()->config()->isMaintenanceMode()) {
            $this->user->redirectTo($this->router()->getLanguagePath('/maintenance/'));
        }

        $this->checkBlockedAccount();
        $this->forceNoIndex=true;
        $this->router()->config()->disableAds();
        $this->title=$this->lang['myAds'];
        
        if (isset ($this->user->pending['post'])) {
            unset($this->user->pending['post']);
            $this->user->update();
        }
        
        $sub = filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                
        if ($sub=='deleted' && $this->user()->level()!=9) { $sub = ''; }

        $this->globalScript.='var SOUND="beep.mp3",';

        if (isset($this->user->params['mute'])&&$this->user->params['mute']) {
            $this->globalScript.='MUTE=1';
        }
        else {
            $this->globalScript.='MUTE=0';
        }
            
        $this->globalScript.=';';
            
        if ($this->user()->isLoggedIn(9)) {                                
            $this->inlineCss .= '.oc .lnk {padding:0 15px!important}li.owned{background-color: #D9FAC8 !important}';
                
            if ($sub=='pending') {
                $this->inlineCss.='li.owned .oc{visibility:visible}.oc:not(.ocl), li.activeForm .oc{visibility:hidden}';
            }
                
            $this->inlineCss.='
                    .png{background-color:#BFEE90}
                    .btmask{
                    position:fixed;
                    display:block;
                    left:0;
                    top:0;
                    width:100%;
                    height:100%;
                    z-index:50000;
                    background-color:#000;
                    opacity:0.6;
                    }
                    .btzone{
                    position:absolute;
                    display:block;
                    width:150px;
                    z-index:50001;
                    }
                    .btzone .bt{
                        margin:10px 15px;
                    }
                    li.focus{
                        z-index:50003;
                    }
                    li.focus .oc{
                        display:none
                    }
                    .sndTgl{
                        width:45px;
                        height:45px;
                        background-color:green;
                        background-repeat:no-repeat;
                        background-position:center;
                        position:absolute;
                        top:10px;
                        left:558px;
                        -moz-border-radius:45px;
                        -webkit-border-radius:45px;
                        border-radius:45px;
                        cursor:pointer
                    }
                    .sndTgl.off{
                        background-color:#ea0000;
                    }
                    .ls p{
                        min-height:90px;
                    }
                    p.pimgs{
                        margin-bottom:10px;
                        border-bottom:1px solid #ccc;
                    }
                    .pimgs .ig{
                        height:auto!important;
                        margin:0 3px;
                    }
                    .pimgs img{                    
                        border:2px solid transparent;
                    }
                    .pimgs img.on{    
                        border:2px solid red;
                    }
                    .pimgs .del{
                        width:24px;
                        height:24px;
                        background-position:center;
                        backgroun-repeat:center;
                        position:absolute;
                        right:0;
                        top:0;
                        cursor:pointer;
                    }
                    .tapl{
                    position:absolute;
                    width:645px;
                    z-index:50001;
                    padding:5px
                    }
                    .oct a{
                        overflow:hidden;white-space:nowrap;max-width:230px;
                        text-overflow: ellipsis;padding:0 10px
                    }
                    .ton{
                        width:840px;
                        border:1px solid #ff9000;
                        background-color:#EEE8AA;
                        position:absolute;
                        z-index: 60000;
                    }
                    .roots{
                        width:111px;
                        float:right;
                        background-color:#143D55;
                        color:#FFF
                    }
                    .roots ul{
                        width:100%;
                        display:block;
                        border-left:1px solid #FF9000
                    }
                    .roots li{
                        border-bottom:1px solid #FF9000
                    }
                    .roots li.on{
                        background-color:indianred
                    }
                    .roots li,.sections li{
                        width:100%;
                        display:block;
                        padding:10px 0;
                        cursor:pointer;
                    }
                    .roots li > span{
                        margin:5px 10px
                    }
                    .sections{
                        width:728px;
                        float:right
                    }
                    .sections > ul{float:right}
                    .sections li{width:182px}
                    .sections li:hover,.sections li.on{background-color:#fefefe;color:orange}
                    .roots li:hover{background-color:#ff9000}
                    .en .roots,.en .sections,.en .sections > ul{float:left}
                    .en .roots ul{border:0;border-right:1px solid #FF9000}
                    .btzone .bt.on{
                        -moz-box-shadow:none;
                        -o-box-shadow:none;
                        -webkit-box-shadow:none;
                        box-shadow:none;
                        background-color:indianred!important
                    }
                    .iprev{
                        position: fixed;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                    }
                    .iprev img{
                        height:100%;
                        position:fixed;
                        z-index:100000;
                    }
                ';
                
            if ($this->router()->isArabic()) {
                $this->inlineCss.='
                        .btzone .bt{
                            float:right
                        }
                        .sndTgl{
                            right:555px;
                            left:auto;
                        }';                    
            }
                
            $this->globalScript.='
                    var setUT=function(e,id){
                        if(e.value>0){
                            $.ajax({
                                url:"/ajax-user-type/",
                                type:"GET",
                                data:{
                                    u:id,
                                    t:e.value
                                },
                                success:function(rp){
                                    if(!rp.RP){ 
                                        e.value=0;
                                    }
                                }
                            });
                        }
                    };
                ';
                
            if ($sub==='') {
                $this->globalScript.='
                        function mCPrem(){
                            Dialog.show("alert_dialog",\'<span class="fail"></span>'.$this->lang['multi_premium_no'].'\');
                        };
                    ';
            }
        }
            
        $this->set_ad(array('zone_0'=>array('/1006833/PublicRelation', 728, 90, 'div-gpt-ad-1319709425426-0-'.$this->router()->config()->serverId)));
        
        $this->inlineCss.='.htf.db{width:720px!important}.ig{-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none}.ww{font-size:16px}.cct .k{float:none}.rpd{padding-top:5px!important}.rpd .bt{width:auto!important}.cct{height:auto}.ls p{padding-bottom:5px}.alt{border-top:1px solid #ccc;padding:5px!important}.en .ig{float:left;margin:0 10px 5px 0}.ar .ig{float:right;margin:0 0 5px 10px}';
            
        if ($this->user()->isLoggedIn(9)) {
            $this->inlineCss.='#rejS{width:640px;padding:5px;margin-bottom:10px;}.adminNB{position:fixed;cursor:pointer;bottom:0;'.($this->router()->isArabic()?'left':'right').':5px;background-color:#D9FAC8;padding:5px 15px;border:1px solid #ccc;border-bottom:0;font-weight:bold;color:#00e}.adminNB:hover{text-decoration:underline}';
        }
        
        
        if ($this->user()->isLoggedIn(9) && ($sub=='pending')) {
            $this->redis = $redis = new Redis();
            $redis->connect("p1.mourjan.com", 6379, 1, NULL, 100);
            $redis->select(5);
            
            if (!$this->user->isSuperUser()) {
                $redis->setex('ADMIN-'.$this->user->info['id'], 300, $this->user->info['id']);
            }
            $this->admins_online = $redis->keys('ADMIN-*');
            
            //error_log(json_encode($this->admins_online));
        }
        
        $this->render();
                
        if (isset($this->user->params['hold'])) {
            unset($this->user->params['hold']);
            $this->user->update();
        }
        
    }    

    
    function assignAdToAdmin($ad_id, $admin_id) {
        $admin_id = 0;
        if ($this->redis && count($this->admins_online)) {
            $redis = $this->redis;
            $ad = $redis->mGet(array('AD-'.$ad_id));
            if ($ad[0]===false) {
                
                $lastIndex = $redis->mGet(array('LAST_IDX'));
                if ($lastIndex[0]===false) {
                    $lastIndex = 0;
                }
                else {
                    $lastIndex = $lastIndex[0];
                }
                
                if ($lastIndex+1 < count($this->admins_online)) {
                    $lastIndex++;
                }
                else {
                    $lastIndex = 0;
                }
                
                $admin_id = $this->admins_online[$lastIndex];
                
                //error_log('assign to '.$admin_id);               
                $redis->setex('AD-'.$ad_id, 120, $admin_id);
                $redis->setex('LAST_IDX', 86400, $lastIndex);                
            }
            else {
                $admin_id = $ad[0];
            }
            $admin_id = substr($admin_id,6)+0;
        }
        return $admin_id;
    }
    
    
    function getAssignedAdmin($ad_id){
        $admin = 0;
        if ($this->redis) {
            $redis = $this->redis;
            $ad = $redis->mGet(array('AD-'.$ad_id));
            if ($ad[0] !== false) {                
                $admin = substr($ad[0],6)+0;
            }
        }
        return $admin;
    }
    
    
    function _body() : void {
        parent::_body();        
        if ($this->redis) { $this->redis->close(); }
    }
    
    
    function mainMobile() {
        if ($this->user->info['id']) {
            $sub='';
            switch($this->subSection) {
                case '':
                    $this->pendingMobileAds(7);
                    break;
                case 'pending':
                    $this->pendingMobileAds(1);
                    break;
                case 'drafts':
                    $this->pendingMobileAds();
                    break;
                case 'archive':
                    $this->pendingMobileAds(9);
                    break;
                default:
                    $this->pendingMobileAds(7);
                    break;
            }
        }
    }
      
    
    function pendingMobileAds($state=0) {
        $lang='';
        $this->userBalance = $this->user->getStatement(0, 0, true);
        if (isset($this->userBalance['balance'])) {
            $this->userBalance = $this->userBalance['balance'];
        }
        else {
            $this->userBalance = 0;
        }
        $current_time = time();
        if ($this->router()->siteLanguage!='ar') $lang=$this->router()->siteLanguage.'/';
        $ads=$this->user->getPendingAds(0,$state);
        $count=0;
        if (!empty($ads))$count=count($ads);
        if ($count) {
            if($this->router()->cfg['enabled_charts'] && $state==7){
                ?><div class="statH rc sh relative"><div id="statDv" class="load"></div></div><?php
            }
            $idx=0;
            $linkLang=  $this->router()->siteLanguage == 'ar' ? '':$this->router()->siteLanguage.'/';
            ?><div class="sum"><?= $this->summerizeAds($count) ?></div><?php
            $this->globalScript.='var sic=[];';
            ?><ul id="resM" class="ls card"><?php
                for($i=0;$i<$count;$i++){
                    $ad=$ads[$i];
                    $isFeatured = isset($ad['FEATURED_DATE_ENDED']) && $ad['FEATURED_DATE_ENDED'] ? ($current_time < $ad['FEATURED_DATE_ENDED']) : false;
                    $isFeatureBooked = isset($ad['BO_DATE_ENDED']) && $ad['BO_DATE_ENDED'] ? ($current_time < $ad['BO_DATE_ENDED']) : false;
                    
                    if (!$isFeatureBooked && $ad['STATE']==4) {
                        $isFeatureBooked = true;
                    }
                    $ad_state=$ad['STATE'];
                    $content=json_decode($ad['CONTENT'],true);
                    if(!isset($content['other']) && isset($content['fields']['other']))
                        $content['other']=$content['fields']['other'];
                    if(!isset($content['altother']) && isset($content['fields']['altother']))
                        $content['altother']=$content['fields']['altother'];
                    
                    $text = isset($content['text']) ? $content['text'] : (isset($content['other'])?$content['other']:'');
                    $altText='';
                    if(isset($content['extra']['t']) && $content['extra']['t']!=2 && isset($content['altother']) && $content['altother']){
                        $altText=$content['altother'];
                    }
                    
                    if(!isset($content['other'])) $content['other']='';
                    if(!isset($content['altother'])) $content['altother']='';
                    if(!isset($content['ro'])) $content['ro']=0;
                    $ad['ro']=$content['ro'];
                    $link='';
                    $liClass='button ';
                    $textClass='en';
                    if($isFeatured || $isFeatureBooked){
                        $liClass.="vp ";
                    }
                    if ($idx%2) {
                        $liClass.="alt ";
                    }
                    if ($ad['RTL']) {
                        $textClass='ar';
                        $liClass.='par ';
                    }else {
                        $liClass.='pen ';
                    }
                    $pic=false;
                    $picCount='';
                    /*
                    if (isset($content['video']) && $content['video'] && count($content['video'])) {
                        $picCount='';
                        if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                            $picCount='<span class=\"cnt\">'.count($content['pics']).'<span class=\"i sp\"></span></span>';
                        }
                        $pic = $content['video'][2];
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img width=\"120\" height=\"93\" src=\"' . $pic . '\" /><span class=\"play\"></span>'.$picCount.'";';
                        $pic = '<span class="ig"></span>';
                    } elseif (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                        $picCount=count($content['pics']);
                        $pic = $content['pic_def'];
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img width=\"120\" src=\"'.$this->router()->cfg['url_ad_img'].'/repos/d/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'<span class=\"i sp\"></span></span>";';
                        $pic = '<span class="ig"></span>';
                    } else {
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\"ir\" src=\"'.$this->router()->cfg['url_img'].'/90/' . $ad['SECTION_ID'] . '.png\" />";';
                        $pic = '<span class="ig"></span>';
                    }
                    */
                    if (isset($content['video']) && $content['video'] && count($content['video'])) {
                        $picCount='';
                        if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                            $picCount='<span class=\"cnt\">'.count($content['pics']).'</span>';
                        }
                        $pic=$content['video'][2];
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\''.$textClass.'\' src=\''.$pic.'\' /><span class=\'play\'></span><span class=\'cnt\'>'.$picCount.'";';
                        
                        $pic = '<span class="thb"></span>';
                    }elseif (isset ($content['pic_def']) && $content['pic_def']!='') {
                        $picCount=count($content['pics']);
                        $pic=$content['pic_def'];
                        
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\''.$textClass.'\' src=\''.$this->router()->cfg['url_ad_img'].'/repos/s/'.$pic.'\' /><span class=\'cnt\'>'.$picCount.'</span>";';
                        
                        $pic = '<span class="thb"></span>';
                    }else{
                        $pic='<img class=\'d\' src=\''.$this->router()->cfg['url_img'].'/90/'.$ad['SECTION_ID'].$this->router()->_png.'\' />';
                        
                        $this->globalScript.='sic[' . $ad['ID'] . ']="'.$pic.'";';
                        
                        $pic = '<span class="thb"></span>';
                    }
                    if ($liClass) $liClass='class="'.trim($liClass).'"';

                    if ($ad_state==7) {
                        $link=$this->router()->getURL($ad['ACTIVE_COUNTRY_ID'],$ad['ACTIVE_CITY_ID'],$content['ro'],$ad['SECTION_ID'],$ad['PURPOSE_ID'],false).($ad['RTL']?'':'en/').$ad['ID'].'/';
                    }
                    if($ad_state>6) {
                        $ad['CITY_ID']=$ad['ACTIVE_CITY_ID'];
                        $ad['COUNTRY_ID']=$ad['ACTIVE_COUNTRY_ID'];
                    }
                    $ad_hold = 0;
                    if($state==7 && isset($this->user->params['hold']) && $this->user->params['hold']==$ad['ID']){
                        if($this->user->holdAd($ad['ID'])){
                            $ad_hold=1;
                        }
                    }
                    
                    $isMultiCountry = false;
                    $adSection = $this->getAdSection($ad, $content['ro'], $isMultiCountry);
                    if($isMultiCountry){
                        $liClass.='multi ';
                    }
                    
                    ?><li id="<?= $ad['ID'] ?>" <?= $ad_hold ?'' :' onclick="ado(this,'.$ad['ID'].',event)"' ?> <?= $liClass ?>><?php 
                    echo '<p class="'.$textClass.'">'.$pic.$text.'</p>';
                    if($altText){
                        echo '<p class="en nd">'.$pic.$altText.'</p>';                       
                    }
                    
                    echo ($ad_state==2? '<b class="ah ok '.$this->router()->siteLanguage.'"><span class="k pub"></span>'.$this->lang['approvedMsg'].'</b>':'').($ad_state==3?'<b class="ah no '.$this->router()->siteLanguage.'"><span class="k spam"></span>'.$this->lang['rejectedMsg'].(isset($content['msg']) && $content['msg']? ': '.$content['msg']:'').'</b>':'');
                    //echo '</p>'; 
                    if($ad_hold){
                        ?><div class="ctr"><b><span class="done"></span><?= $this->lang['retired'] ?></div><?php
                    }else {
                        /* ?><span class="src <?= $this->router()->siteLanguage ?>"><?= $this->getAdSection($ad) ?><time st='<?= strtotime($ad['DATE_ADDED']) ?>'></time><span class="adn"></span><?= $this->router()->cfg['enabled_ad_stats'] && ($state == 7 || $state==9) ? '<span class="ata load"></span>' :'' ?></span><?php */
                        ?><span class="src <?= $this->router()->siteLanguage ?>"><?= $adSection ?><time st='<?= strtotime($ad['DATE_ADDED']) ?>'></time><?= $this->router()->cfg['enabled_ad_stats'] && ($state == 7 || $state==9) ? '<span class="ata load"></span>' :'' ?></span><?php 
                    }
                    ?></li><?php
                    
                    $idx++;
                }
                ?></ul><?php
                $isSystemAd = (isset($ad['DOC_ID']) && $ad['DOC_ID']) ? true : false;
                $state=$ad['STATE'];
                $isSuspended = FALSE;
                if ($this->user->getProfile())
                {
                    $isSuspended = $this->user->getProfile()->isSuspended();
                } else {
                    error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                }
                
                ?><div id="ad_options" class="dialog"><?php
                    ?><div class="dialog-box"><?php 
                        ?><ul><?php
                        switch($state){
                            case 9:
                                if($this->router()->cfg['enabled_charts']){
                                    ?><li><div onclick="aStat(this,event)"><span class="dic stats"></span><?= $this->lang['stats'] ?></div></li><?php
                                }
                                if(!$isSystemAd)
                                {
                                    if(!$isSuspended)
                                    {
                                        ?><li><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                                            ?><input type="hidden" name="adr" /><?php
                                            ?><div onclick="$b(this).value=eid;$p(this).submit()"><span class="dic edit"></span><?= $this->lang['edit_republish'] ?></div><?php
                                        ?></form></li><?php 
                                        if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                            ?><li><div onclick="are(this,event)"><span class="dic renew"></span><?= $this->lang['renew'] ?></div></li><?php
                                        }
                                    }
                                    ?><li><div onclick="adel(this,1,event)"><span class="dic stop"></span><?= $this->lang['delete'] ?></div></li><?php
                                }
                                break;
                            case 7:
                                ?><li><a id="ad_cancel_pre" href="javascript:void(0)" onclick="cancelPremium(this)"><span class="dic stop"></span><?= $this->lang['premium_cancel'] ?></a></li><?php
                                ?><li><a id="ad_make_pre" href="javascript:void(0)" <?php
                                if($this->userBalance){
                                    //check to see if ad is published to multi countries
                                    ?>onclick="makePre()"<?php
                                }else{
                                    ?>onclick="noPremium()"<?php
                                }
                                ?>><span class="dic coin"></span><?= $this->lang['make_premium'] ?></a></li><?php
                                
                                if($this->router()->cfg['enabled_charts']){
                                    ?><li><div onclick="aStat(this,event)"><span class="dic stats"></span><?= $this->lang['stats'] ?></div></li><?php
                                }
                                ?><li><a id="ad_detail"><span class="dic detail"></span><?= $this->lang['ad_detail'] ?></a></li><?php
                                ?><li><a id="ad_wats" data-action="share/whatsapp/share"><span class="dic wats"></span><?= $this->lang['ad_share_wats'] ?></a></li><?php
                                ?><li><a id="ad_viber"><span class="dic viber"></span><?= $this->lang['ad_share_viber'] ?></a></li><?php
                                if(!$isSystemAd){
                                    ?><li><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                                        ?><input type="hidden" name="adr" /><?php
                                        ?><div onclick="$b(this).value=eid;$p(this).submit()"><span class="dic edit"></span><?= $this->lang['edit_ad'] ?></div><?php
                                    ?></form></li><?php
                                    ?><li><div onclick="ahld(this,event)"><span class="dic stop"></span><?= $this->lang['hold'] ?></div></li><?php                                    
                                }
                                break;
                            case 0:
                            case 1:
                            case 2:
                            case 3:
                            case 4:
                                if (!$isSystemAd){
                                    if(!$isSuspended)
                                    {
                                        ?><li><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method="post"><?php
                                            ?><input type="hidden" name="ad" /><?php 
                                            ?><div onclick="$b(this).value=eid;$p(this).submit()" class="button"><span class="dic edit"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></div><?php
                                        ?></form></li><?php
                                    }
                                    ?><li><a href="javascript:adel(this,0,event)"><span class="dic stop"></span><?= $this->lang['delete'] ?></a></li><?php
                                }
                                break;
                        }
                        ?></ul><?php
                    ?></div><?php 
                    ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /></div><?php 
                ?></div><?php
                if($this->router()->cfg['enabled_charts'] && ($state==7 || $state==9)){
                    ?><div class="txtd statH rc" onclick="se()"><div id="statAiv" class="statDiv load"></div><div class="close"></div></div><?php 
                }
                
                if($this->userBalance && $state == 7){
                    ?><div id="make_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['balance'].': '.$this->userBalance ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-hint"><?= $this->lang['premium_hint'] ?></div><?php 
                            ?><div class="dialog-box"><?php 
                                ?><ul><?php
                                ?><li><?= $this->lang['premium_days'] ?>:</li><?php
                                ?><li><select id="spinner" max="<?= $this->userBalance ?>"></select></li><?php
                                ?></ul><?php
                            ?></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['make'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="confirm_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['please_confirm'] ?>:</div><?php
                            ?><div class="dialog-box"></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['deal'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="what_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><span class='mc24'></span><?= $this->lang['make_premium'] ?></div><?php
                            ?><div class="dialog-box"><?= $this->lang['no_balance_dialog'] ?></div><?php 
                            ?><div class="dialog-action"><input type="button" value="<?= $this->lang['back'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="stop_premium" class="dialog premium"><?php
                        ?><div class="dialog-box"><?= $this->lang['stop_premium'] ?></div><?php 
                        ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                    $this->globalScript.='
                            function mCPrem(){
                                Dialog.show("alert_dialog",\'<span class="fail"></span>'.$this->lang['multi_premium_no'].'\');
                            };
                        ';
                }
            
            if(0){
                ?><div id="aopt" class="sbx"><?php 
                    ?><div class="bts"><?php
                        $state=$ad['STATE'];                                                
                        $isSuspended = FALSE;
                        if ($this->user->getProfile()) {
                            $isSuspended = $this->user->getProfile()->isSuspended();
                        } 
                        else {
                            error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                        }
                        if ($state<7 && !$isSystemAd) {
                            if (!$isSuspended) {
                                ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method="post"><?php
                                    ?><input type="hidden" name="ad" /><?php 
                                    ?><div onclick="$b(this).value=eid;$p(this).submit()" class="button"><span class="k aedi"></span><label><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></label></div><?php
                                ?></form><?php
                            }
                            ?><div onclick="adel(this,0,event)" class="button"><span class="k spam"></span><label><?= $this->lang['delete'] ?></label></div><?php
                        }
                        elseif ($state==7) {
                            if($this->router()->cfg['enabled_charts']){
                                ?><div onclick="aStat(this,event)" class="button"><span class="k stat"></span><label><?= $this->lang['stats'] ?></label></div><?php
                            }
                            if(!$isSystemAd){
                            ?><div onclick="ahld(this,event)" class="button"><span class="k spam"></span><label><?= $this->lang['hold'] ?></label></div><?php
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                                    ?><input type="hidden" name="adr" /><?php
                                    ?><div onclick="$b(this).value=eid;$p(this).submit()" class="button"><span class="k aedi"></span><label><?= $this->lang['edit_ad'] ?></label></div><?php
                                ?></form><?php
                            }
                        }
                        elseif ($state==9) {
                            if($this->router()->cfg['enabled_charts']){
                                ?><div onclick="aStat(this,event)" class="button"><span class="k stat"></span><label><?= $this->lang['stats'] ?></label></div><?php
                            }
                            if (!$isSystemAd) {
                                if (!$isSuspended) {
                                    ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                                        ?><input type="hidden" name="adr" /><?php
                                        ?><div onclick="$b(this).value=eid;$p(this).submit()" class="button"><span class="k aedi"></span><label><?= $this->lang['edit_republish'] ?></label></div><?php
                                    ?></form><?php 
                                    if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                        ?><div onclick="are(this,event)" class="button"><span class="k ref"></span><label><?= $this->lang['renew'] ?></label></div><?php
                                    }
                                }
                                ?><div onclick="adel(this,1,event)" class="button"><span class="k spam"></span><label><?= $this->lang['delete'] ?></label></div><?php
                            }
                        }
                    ?></div><?php
                    if ($this->router()->cfg['enabled_charts'] && ($state==7 || $state==9)) {
                        ?><div class="txtd statH rc" onclick="se()"><div id="statAiv" class="statDiv load"></div></div><?php 
                    }
                ?></div><?php 
            }
        }
        else {
            echo '<br /><h2 class="ctr">';
            switch($this->subSection){
                case 'pending':
                    echo $this->lang['no_ads_pending'];
                    break;
                case 'drafts':
                    echo $this->lang['no_ads_drafts'];
                    break;
                case 'archive':
                    echo $this->lang['no_ads_archive'];
                    break;
                case '':
                default:
                    echo $this->lang['no_ads_active'];
                    break;
            }
            echo '</h2>';
            echo '<p class="ctr"><span class="na"></span></p><br />';
            echo '<p class="ctr"><a class="bt btw ok" href="/post/">'.$this->lang['start_publish'].'</a></p><br />';
        }
    }
    
    
    function summerizeAds($count){
        $formatted=number_format($count);
        $bread= "";
        if ($this->router()->siteLanguage=="ar") {
            if ($count>10) {
                $bread.= $formatted." ".$this->lang['ads'];
            }
            elseif ($count>2) {
                $bread.= $formatted." ".$this->lang['3ad'];
            }
            else if ($count==1) {
                $bread.= $this->lang['ad'];
            }
            else {
                $bread.= $this->lang['2ad'];
            }
        }
        else {
            $bread.= $this->formatPlural($count, "ad");
        }
        $bread.=" ";
        if ($this->router()->siteLanguage=='ar') {
            $bread.='ضمن ';
        }
        else {
            $bread.='in ';
        }
        return $bread.' '.$this->title;
    }

    
    function side_pane() {
        $this->renderSideSite();
    }
    
    
    function main_pane() : void {
        if ($this->user()->isLoggedIn()) {
            if (!$this->router()->config()->get('enabled_post') && $this->topMenuIE) {
                $this->renderDisabledPage();
                return;
            }

            $sub= filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            if ($sub=='deleted' && $this->user()->level()!=9) { $sub = ''; }
            switch($sub) {
                case '':
                    $this->pendingAds(7);
                    break;
                
                case 'pending':
                    $this->pendingAds(1);
                    if ($this->user()->level()===9) {                    
                    $this->globalScript.="                            
var rtMsgs={
    'ar':[
        'إختر السبب...',
        'group=نص الاعلان',
        'يرجى تصحيح نص الاعلان والفصل بين العربية والانجليزية',
        'يرجى النشر في قسم العقارات الدولية',
        'يرجى النشر في قسم خدمات - استقدام العمالة',
        'يرجى النشر في قسم خدمات - خدمات عقارية',
        'نص الاعلان لا يمكن ان يحتوي على او ان يشير الى رابط لموقع الكتروني',
        'يرجى اعادة ادخال نص الاعلان',
        'يرجى تصحيح نص الاعلان',
        'نص الإعلان غير صالح للنشر',
        'نص الإعلان غير صالح للنشر في هذا القسم',
        'group=تفاصيل ناقصة',
        'يرجى تحديد المنطقة',        
        'يرجى تحديد المدينة أو المنطقة التي تتوفر فيها فرصة العمل ضمن نص الاعلان',        
        'group=وساءل التواصل',
        'يرجى اضافة رقم الهاتف للتواصل معك ضمن خانة معلومات التواصل',
        'يرجى تصحيح رقم الهاتف',
        'يرجى تصحيح رقم الهاتف أو تحديد رمز المنطقة إن لزم',
        'group=تكرار',
        'تكرار',
        'اعلان مكرر',
        'يرجى عدم التكرار لتجنب تعليق حسابك بشكل آلي',
        'لقد تم نشر الإعلان في القسم الأنسب ونرجو عدم التكرار',
        'لقد تخطيت الحد الاقصى لنشر اعلانات طلب العمل',
        'group=البلد',
        'يرجى النشر ضمن قسم العقارات الدولية',
        'لا يمكن نشر هذا الاعلان سوى في البلد الذي تتواجد فيه مكاتبكم وخدماتكم',
        'لا يمكن نشر هذا الإعلان سوى في البلد حيث يتواجد فيه العقار، السيارة أو السلعة',
        'group=سياسة الموقع',
        'لا يمكن نشر اعلانات مماثلة دون ادراج رقم الموبايل المستخدم لتفعيل حسابك مع مرجان (فقط) ضمن وسائل التواصل',
        'اعلانات زواج المسيار والمتعة مخالفة لسياسة الموقع ولا يمكن نشرها',
        'لا يمكن نشر إعلانات مماثلة طبقاً لسياسة الموقع',
        'اعلانات المتاجرة بالتأشيرات والإقامات مجرمة قانونيا',
        'مضمون هذا الاعلان يتعارض مع مضمون اعلاناتك المنشورة وهذا تحذير قبل ايقاف حسابك بسبب الاحتيال',
        'يحظر نشر هذا الاعلان ما دمت تتصل بالانترنت بواسطة بروكسي'
    ],
    'en':[
        'specify the reason...',
        'group=Ad Text',
        'please correct the ad text and seperate Arabic from English',
        'please edit and change section to International Real Estate',
        'please edit and change section to Services - Labor Recruitment',
        'please edit and change section to Services - Real estate services',
        'website links are not allowed within the ad text',
        'please re-enter the ad text',
        'please correct the ad text',
        'the ad text is not suitable for publishing',
        'the ad text is not suitable for publishing in this section',
        'group=Ad Details',
        'please specify the location',
        'please specify the city or the place within the ad text where the work opportunity is located',
        'group=Contact Info',
        'please specify a phone number within the contact info',
        'please correct the phone number',
        'please correct the phone number or specify the area code if applicable',
        'group=Repetition',
        'repetition',
        'repeated ad',
        'please do not repeat ads so that your account does not get suspended automatically',
        'this ad has already been published in a more suitable section',
        'you have reached the maximum number of ads that can be posted under LOOKING FOR A JOB',
        'group=Inapplicable Country',
        'please choose \"international real estate\" section to publish your ad',
        'this ad can only be published in countries where your offices and services are located',
        'this ad cannot be published in countries other than the country of origin (cars, real estate, goods)',
        'group=Website Policy',        
        'cannot publish similar ads unless you add ONLY the mobile number (used to activate your mourjan account) to the contact information',
        'Temporary marriages (Mesyar, Muta\') ads are against the website policy and caanot be published',
        'This type of ads is against the website policy and cannot be published',
        'Selling Visas and work permits is against the law',
        'This ad contradicts with the sense of your other published ads, and this is a warning before blocking your account for fraud',
        'The user behind proxy ad posting is prohibited'
    ]
};
                            ";
                }
                break;
            case 'drafts':
                $this->pendingAds();
                break;
            case 'archive':
                $this->pendingAds(9);
                break;
            case 'deleted':
                $this->pendingAds(8);
                break;
            default:
                $this->pendingAds(7);
                break;
        }
        
        }
        else{
            $this->renderLoginPage();
        }
    }    
    
    
    function renderEditorsBox($state=0, $standalone=false) {
        $isSuperUser = $this->user()->isSuperUser();
        if ($isSuperUser) {
            $filters = $this->user()->getAdminFilters();
        }
        
        if ($this->user()->isLoggedIn(9)) {
            ?><style><?php
            ?>.stin,.phc{display:none}.prx h4{margin-bottom:5px}.prx{display:block;clear:both;width:300px}.prx a{color:#00e}.prx a:hover{text-decoration:underline}<?php
            ?>.pfrx{height:auto}.prx select{width:260px;padding:3px 5px;margin:10px}.pfrx input{padding:5px 20px;margin:5px 0 10px}<?php            
            if (isset($filters['active']) && $filters['active']) {
                ?>.pfrx{background-color:#D9FAC8}<?php
            }
            ?></style><?php
        }
        
        if ($isSuperUser) {
            if ($standalone) { echo '<div class=fl>'; }
            $link='';
            switch ($state) {
                case 9:
                    $link='?sub=archive&a=';
                    break;
                case 7:
                    $link='?a=';
                    break;
                case 1:
                case 2:
                case 3:
                    $link='?sub=pending&a=';
                    break;
                case 0:
                default:
                    return;
            }
            
            if ($state==1) {
                $baseUrl = $this->router()->getLanguagePath('/myads/');
                echo '<div class=col-12>';
                echo '<form action="', $baseUrl, '" method=GET class=account><input type=hidden name=sub value=pending />';
                if ($filters['uid']) {
                    echo '<input type=hidden name=fuid value="', $filters['uid'], ' />', $this->router()->isArabic()?'مستخدم':'user', ': <b>', $filters['uid'], '</b>';                    
                }
                
                echo '<select name=fhl onchange="this.form.submit()">';
                echo '<option value=0', $filters['lang']==0?' selected':'', '>', $this->lang['lg_sorting_0'],'</option>';
                echo '<option value=1', $filters['lang']==1?' selected':'', '>العربي فقط</option>';
                echo '<option value=2', $filters['lang']==2?' selected':'', '>الانجليزي فقط</option>';
                echo '</select>';

                echo '<select name=fro onchange="this.form.submit()">';
                echo '<option value=0', $filters['root']==0 ? ' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                foreach ($this->router()->pageRoots as $id=>$root) {
                    echo '<option value=', $id, $filters['root']==$id ? ' selected':'', '>', $root['name'], '</option>';
                }
                echo '</select>';
                
                if ($filters['root']==3){
                    echo '<select name=fpu onchange="this.form.submit()">';
                    echo '<option value=0', $filters['purpose']==0?' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                    foreach ($this->router()->pageRoots[3]['purposes'] as $id=>$purpose) {
                        echo '<option value=', $id, $filters['purpose']==$id?' selected>':'>', $purpose['name'], '</option>';
                    }
                    echo '</select>';
                }
                
                if ($filters['active']) {
                    echo '<input type=reset onclick="location.href=', $baseUrl, '?sub=pending" value="', $this->lang['search_cancel'], '" />';
                }
                echo '</form></div>';
            }
        }

        if (($this->user()->isLoggedIn(9) && filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING)==='pending') || $isSuperUser) {
            if (!isset($filters) || !$filters['active']) {   
                echo '<div id=editors class=account style="padding-top:12px">';
                ?><span class="hvn50okt2 d2d9s5pl1g n2u2hbyqsn"><?= $isSuperUser ? '<a href="'. $link .'69905">Robert</a>' : 'Robert' ?></span><?php
                ?><span class="f3iw09ojp5 a1zvo4t2vk"><?= $isSuperUser ? '<a href="'. $link .'1">Bassel</a>' : 'Bassel' ?></span><?php
                ?><span class="a1zvo4t4b8"><?= $isSuperUser ? '<a href="'. $link .'2100">Nooralex</a>':'Nooralex' ?></span><?php
                ?><span class="n2u2hc8xil"><?= $isSuperUser ? '<a href="'. $link .'477618">Samir</a>':'Samir'?></span><?php
                ?><span class="x1arwhzqsl"><?= $isSuperUser ? '<a href="'. $link .'38813">Editor 1</a>':'Editor 1'?></span><?php
                ?><span class="d2d9s5p1p2"><?= $isSuperUser ? '<a href="'. $link .'44835">Editor 2</a>':'Editor 2'?></span><?php
                ?><span class="b2ixe8tahr"><?= $isSuperUser ? '<a href="'. $link .'53456">Editor 3</a>':'Editor 3'?></span><?php
                ?><span class="hvn50s5hk"><?= $isSuperUser ? '<a href="'. $link .'166772">Editor 4</a>':'Editor 4'?></span><?php
                ?><span class="j1nz09nf5t"><?= $isSuperUser ? '<a href="'. $link .'516064">Editor 5</a>':'Editor 5'?></span><?php
                ?><span class="x1arwii533"><?= $isSuperUser ? '<a href="'. $link .'897143">Editor 6</a>':'Editor 6'?></span><?php
                ?><span class="hvn517t2q"><?= $isSuperUser ? '<a href="'. $link .'897182">Editor 7</a>':'Editor 7'?></span><?php
                ?><span class="hvn51amkw"><?= $isSuperUser ? '<a href="'. $link .'1028732">Editor 8</a>':'Editor 8'?></span><?php
                echo '</div>';                               
            }
            
            if ($standalone) { echo '</div>'; }
        }
    }

    
    function getAdSection($ad, int $rootId=0, &$isMultiCountry=false) {
        $section='';
        switch($ad['PURPOSE_ID']) {
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex].' '.$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].' '.$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].'/', $this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex])){
                    $section=$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                else {
                    $in=' ';
                    if ($this->router()->language=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].$in.$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                break;
        }
           
       $adContent = json_decode($ad['CONTENT'], true);
       $countries = $this->router()->db->getCountriesDictionary(); // $this->router()->countries;
       if (isset($adContent['pubTo'])) {
            $fieldIndex=2;
            $comma=',';
            if ($this->router()->language=='ar'){
                $fieldIndex=1;
                $comma='،';
            }
            $countriesArray=array();
            $cities = $this->router()->cities;
                
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
                    if ($countriesArray[$country_id][1]!==false) $countriesArray[$country_id][1][]=$cities[$city][$fieldIndex];
                }
            }
   
            $i=0;
            foreach ($countriesArray as $key => $value) {
                if ($i) {
                    $content.=' - ';
                    $isMultiCountry = true;
                }
                $content.=$value[0];
                if ($value[1]!==false) $content.=' ('.implode ($comma, $value[1]).')';
                $i++;
            }
                
            if ($content) {
                $section=$section.' '.$this->lang['in'].' '.$content;
            }
        }
        elseif(isset ($countries[$ad['COUNTRY_ID']])) {
            $countryId=$ad['COUNTRY_ID']; 
            $countryCities=$countries[$countryId][6];
            if (count($countryCities)>0 && isset($this->router()->cities[$ad['CITY_ID']])) {
                $section=$section.' '.$this->lang['in'].' '.$this->router()->cities[$ad['CITY_ID']][$this->fieldNameIndex].' '.$countries[$countryId][$this->fieldNameIndex];
            }
            else {
                $section=$section.' '.$this->lang['in'].' '.$countries[$countryId][$this->fieldNameIndex];
            }
        }

        if ($section) {
            if ($this->isMobile) {
                $section='<b class="ah">'.$section.'</b>';
            }
            else {
                $section='<span class="k">'.$section.' - <b>' . $this->formatSinceDate(strtotime($ad['LAST_UPDATE'])) . '</b></span>';
            }
        }
        return $section;
    }
    
    
    private function accountButton($href, $text, $active, $count) : void {
        echo '<a href="', $href, '" class="btn', $active?' current">':'">', $text, $active?' ('.$count.')':'', '</a>';
    }
    
    
    function pendingAds($state=0) {
        $isAdmin = false;
        $isAdminOwner = false;
        $isSuperAdmin = $this->user->isSuperUser();
        $current_time = time();
        
        if ($this->user()->level()===9) {
            $isAdmin = true;
            $mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        }
        
        $filters = $this->user()->getAdminFilters();
        $sub = filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);  

        $ads = $this->user()->getPendingAds(0, $state, true);
        $count= !empty($ads) ? count($ads) : 0; 
        $allCounts = $count>0 ? $this->user()->getPendingAdsCount($state) : 0;
        $this->user()->update();
        
        
        echo '<div class=row><div class=col-12><div class=card>';
        $this->renderBalanceBar();
        echo '<div class=account>';
        echo '<a href="', $this->router()->getLanguagePath('/post/'), '" class="btn half"><span class="j pub"></span>', $this->lang['button_ad_post_m'], '</a>';
        echo '<a id=active href="', $this->router()->getLanguagePath('/myads/'), '" class="btn active', $sub==''?' current':'', '"><span class="pj ads1"></span>', $this->lang['ads_active'], '</a>';

        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=pending', $this->lang['home_pending'], $sub=='pending', $allCounts);
        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=drafts', $this->lang['home_drafts'], $sub=='drafts', $allCounts);
        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=archive', $this->lang['home_archive'], $sub=='archive', $allCounts);
            
        echo '<a id=favorite href="', $this->router()->getLanguagePath('/favorites/'), '?u=', $this->user->info['idKey'], '" class="btn half favorite', $sub=='favorite'?' current':'', '"><span class="j fva"></span>', $this->lang['myFavorites'], '</a>';
        echo '<a href="', $this->router()->getLanguagePath('/statement/'), '" class="btn half balance"><span class="pj coin"></span>', $this->lang['myBalance'], '</a>';
        echo '<a href="', $this->router()->getLanguagePath('/account/'), '" class="btn full settings"><span class="j sti"></span>', $this->lang['myAccount'], '</a>';
        echo '</div>';
                   
        echo '<div class=card-footer><div class=account>';
        $this->renderEditorsBox($state);
        echo '</div></div></div></div></div>';
        
        
        if ($count) {            
            $currentOffset = $this->get('o', 'uint');
            $hasNext=false;
            $recNum=50;
            
            $hasPrevious = (is_numeric($currentOffset) && $currentOffset);
            
            if ($count==51) {
                $hasNext=true;
                $count=50;
            }
            
            //$allCounts = $this->user()->getPendingAdsCount($state);            
            $renderAssignedAdsOnly = false;
        
            ?><p class="ph phb"><?php
            
            switch ($state) {
                case 8:
                    ?><?= $this->lang['ads_deleted'].($allCounts ? ' ('.$allCounts.')':'').' '.$this->renderUserTypeSelector() ?></p><div class="fl"><p class="phc"><?php
                    break;
                
                case 7:                    
                    $this->userBalance = $this->user->getStatement(0, 0, true);
                    if (isset($this->userBalance['balance'])) {
                        $this->userBalance = $this->userBalance['balance'];
                    }
                    ?><?= $this->lang['ads_active'].($allCounts ? ' ('.$allCounts.')':'').' '.$this->renderUserTypeSelector() ?></p><div class="fl"><p class="phc"><?= $this->lang['ads_active_desc'] ?><?php
                    break;
                    
                case 1:
                case 2:
                case 3:
                    $renderAssignedAdsOnly = true;
                    break;

                default:
                    break;
            }
            ?></p><?php
            $isAdminProfiling = (boolean)($this->get('a') && $this->user->info['level']==9);
            if ($isAdminProfiling) {
                $renderAssignedAdsOnly = false;
            }
            if (!$isAdminProfiling && $this->user->info['level']==9 && in_array($state,[1,2,3])) {
                $this->globalScript.='
                    var SETN={
                    ';
                $rdx=0;
                $lnIndex= $this->router()->isArabic() ? 4 : 3;
                foreach ($this->router()->config()->get('smart_section_fix') as $SID => $switches) {  
                    if($rdx++>0)$this->globalScript.=',';
                    $this->globalScript.=$SID.':[';
                    $pdx=0;
                    foreach ($switches as $switch){
                        if($pdx++>0)$this->globalScript.=',';
                        $this->globalScript.='['.$switch[0].','.$switch[1].','.$switch[2].',"'.$switch[$lnIndex].'"]';
                    }
                    $this->globalScript.=']';
                }                
                $this->globalScript.='
                    };
                ';
                
                
                
                $this->globalScript.='
                    var ROTN={
                    ';
                $rdx=0;
                foreach ($this->router()->config()->get('smart_root_fix') as $SID => $switches) {
                    if($rdx++>0)$this->globalScript.=',';
                    $this->globalScript.=$SID.':[';
                    $pdx=0;
                    foreach ($switches as $switch) {
                        if($pdx++>0)$this->globalScript.=',';
                        $this->globalScript.='['.$switch[0].','.$switch[1].','.$switch[2].',"'.$switch[$lnIndex].'"]';
                    }
                    $this->globalScript.=']';
                }                
                $this->globalScript.='  
                    };
                ';
                
                                                
                $this->globalScript.='
                    var ROPU={
                    ';
                $rdx=0;
                foreach ($this->router()->pageRoots as $Rid => $root) {  
                    if($rdx++>0)$this->globalScript.=',';
                    $this->globalScript.=$Rid.':[';
                    $pdx=0;
                    foreach ($root['purposes'] as $Pid => $pu) {
                        if($Pid != 999){
                            if($pdx++>0)$this->globalScript.=',';
                            $this->globalScript.='['.$Pid.',"'.$pu['name'].'"]';
                        }
                    }
                    $this->globalScript.=']';
                }
                $this->globalScript.='
                    };
                ';
                
                $this->globalScript.=' 
                ';
                 
            }
            
            if ($state==7) {
                if ($this->router()->cfg['enabled_charts'] && !$isAdminProfiling) {                    
                    ?><div class="stin <?= $this->router()->siteLanguage ?>"></div><?php                    
                    $this->renderEditorsBox($state);
                    ?></div><?php
                    ?><div class="phld"><?php
                        ?><div id="statDv" class="load"></div><?php
                    ?></div><?php
                } else {
                    $this->renderEditorsBox($state);
                    ?></div><?php
                }
            } 
            else {
                ?></div><?php
            }

            echo '<div class=row><div class=col-12>';            
            $idx=0;
            $this->globalScript.='var sic=[];';
            $linkLang = $this->router()->language=='ar' ? '':$this->router()->language.'/';
            
            $displayIdx = 0;
            for ($i=0; $i<$count; $i++) {
                $phoneValidErr=false;
                $link='';
                $altlink='';
                $ad=$ads[$i];
                $liClass='';
                $textClass='en';
                    
                if ($isAdmin) {
                    $isAdminOwner = ($ad['WEB_USER_ID']==$this->user()->id() ? true : false );
                }
                
                $assignedAdmin = '';
                if ($isAdmin && $renderAssignedAdsOnly && !$isAdminOwner) {
                    $assignedAdmin = $this->assignAdToAdmin($ad['ID'], $this->user()->id());
                    if (!$isSuperAdmin && $assignedAdmin && $assignedAdmin!=$this->user()->id()) {
                        continue;
                    }
                    if ($isSuperAdmin && $assignedAdmin) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin = '<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin = '';
                    }
                    $displayIdx++;
                }
                
                if ($ad['RTL']) { $textClass='ar'; }

                $content=json_decode($ad['CONTENT'],true);  
                                        
                $isFeatured = isset($ad['FEATURED_DATE_ENDED']) && $ad['FEATURED_DATE_ENDED'] ? ($current_time < $ad['FEATURED_DATE_ENDED']) : false;
                $isFeatureBooked = isset($ad['BO_DATE_ENDED']) && $ad['BO_DATE_ENDED'] ? ($current_time < $ad['BO_DATE_ENDED']) : false;
                    
                if (!$isFeatureBooked && ($ad['STATE']==4 || (isset($content['budget']) && $content['budget']>0) )) {
                    $isFeatureBooked = true;
                }
                                                 
                if (!isset($content['ro'])) { $content['ro']=0; }
                if ($ad['SECTION_ID']>0) {
                    $content['ro']=$this->router()->sections[$ad['SECTION_ID']][4];
                    $content['se']=$ad['SECTION_ID'];
                }
                    
                $text='';
                $text = isset($content['text']) && trim($content['text']) ? $content['text'] : (isset($content['other'])?$content['other']:'');
                $altText='';
                if (isset($content['extra']['t']) && $content['extra']['t']!=2 && isset($content['altother']) && $content['altother']) {
                    $altText=$content['altother'];
                }
                    
                $pic=false;
                $picCount='';
                
                $thumbs='';
                $hasAdminImgs = 0;
                
                if ($isAdmin) {
                    $images='';
                    if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                        foreach($content['pics'] as $img => $dim){
                            if ($images) { $images.="||"; }
                            $images.='<img width=\"118\" src=\"'.$this->router()->config()->adImgURL.'/repos/s/' . $img . '\" />';
                            $thumbs .= '<span class=ig data-pix="'.$img.'"></span>';
                            $hasAdminImgs = 1;
                        }
                    }
                    
                    if ($images) { $images.="||"; }
                    $images.='<img class=\"ir\" src=\"'.$this->router()->config()->imgURL.'/90/' . $ad['SECTION_ID'] . $this->router()->_png .'\" />';
                    $pic = '<span class="ig"></span>';
                    
                    $this->globalScript.='sic[' . $ad['ID'] . ']="'.$images.'";';                    
                }
                else {
                    if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])>0) {
                        $picCount=count($content['pics']);
                        $pic = isset($content['pic_def']) ? $content['pic_def'] : array_keys($content['pics'])[0];
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img width=\"120\" src=\"'.$this->router()->cfg['url_ad_img'].'/repos/s/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'<span class=\"i sp\"></span></span>";';
                        $pic = '<span class="ig"></span>';
                    } 
                    else {
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\"ir\" src=\"'.$this->router()->cfg['url_img'].'/90/' . $ad['SECTION_ID'] .$this->router()->_png. '\" />";';
                        $pic = '<span class="ig"></span>';
                    }
                }
                
                
                $onlySuper = ($isAdmin && isset($ad['SUPER_ADMIN']) && $ad['SUPER_ADMIN']) ? $ad['SUPER_ADMIN']+0 : 0;
                if ($onlySuper) {
                    if ($onlySuper && $onlySuper < 1000) {                        
                        switch($onlySuper) {
                            case 998:
                                $onlySuper = "email contains + sign";
                                break;
                            case 997:
                                $onlySuper = "email contains hotel word";
                                break;
                            case 996:
                                $onlySuper = "email contains more than one dot";
                                break;
                            case 995:
                                $onlySuper = "user verified number is from other country";
                                break;
                            case 999:
                                $onlySuper = "System General";
                                break;
                            case 1:
                                $onlySuper = "contains stop words";
                                break;
                            default:
                                $onlySuper = "not specified";
                                break;
                        }
                        
                    }
                    else {
                        if (isset($this->editors[$onlySuper])) {
                            $onlySuper = "requested by ".$this->editors[$onlySuper];
                        }
                        else {
                            $onlySuper = "requested by user #".$onlySuper."#";
                        }
                    }
                }
                                
                if ($this->user()->level()===9) {
                    $mcUser = new MCUser((int)$ad['WEB_USER_ID']);
                    $userMobile = $mcUser->getMobile(TRUE)->getNumber();
                    
                    $needNumberDisplayFix=false;
                    if (!preg_match('/span class="pn/u', $text)) {
                        $needNumberDisplayFix = true;
                    }
                    
                    if (isset($content['cui']['p']) && is_array($content['cui']['p'])) {
                        foreach ($content['cui']['p'] as $p) { 
                            $isUserMobile = false;
                            try {
                                $num = $mobileValidator->parse($p['v'],$p['i']);
                                if ($num && $mobileValidator->isValidNumber($num)) {
                                    if ($userMobile && '+'.$userMobile == $p['v']) {
                                        $isUserMobile=true;
                                    }
                                
                                    $type=$mobileValidator->getNumberType($num);  
                                    $phoneValidErr=0;
                                    switch((int)$p['t']){
                                        case 1:
                                        case 2:
                                        case 3:
                                        case 4:
                                        case 5:
                                        case 13:
                                            if($type!==1 && $type!==2)
                                                $phoneValidErr=1;
                                            break;
                                        case 7:
                                        case 8:
                                        case 9:
                                            if($type!==0 && $type!==2)
                                                $phoneValidErr=1;
                                            break;
                                        default:
                                            $phoneValidErr=2;
                                            break;
                                    }
                                }
                                else {
                                    $phoneValidErr=2;
                                }
                            }
                            catch (Exception $ex) {
                                $phoneValidErr=2;
                            }
                            if ($needNumberDisplayFix) {
                                if (strlen($p['v'])==0) {
                                    $p['v'] = $p['r'];
                                }
                                $text = preg_replace('/\\'.$p['v'].'/', '<span class="pn">'.$p['v'].'</span>', $text);
                                if($altText){
                                    $altText = preg_replace('/\\'.$p['v'].'/', '<span class="pn">'.$p['v'].'</span>', $altText);
                                }
                            }
                            if($isUserMobile){
                                $text = preg_replace('/\<span class="pn">\\'.$p['v'].'\<\/span\>/', '<span class="pn png">'.$p['v'].'</span>', $text);
                                if($altText){
                                    $altText = preg_replace('/\<span class="pn">\\'.$p['v'].'\<\/span\>/', '<span class="pn png">'.$p['v'].'</span>', $altText);
                                }
                            }
                            if($phoneValidErr){
                                $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">\\'.$p['v'].'\<\/span\>/', '<span class="vn">'.$p['v'].'</span>', $text);
                                if($altText){
                                    $altText = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">\\'.$p['v'].'\<\/span\>/', '<span class="vn">'.$p['v'].'</span>', $altText);
                                }
                            }
                        }
                    }
                        
                    $name=$ad['WEB_USER_ID'].'#'.($ad['FULL_NAME']?$ad['FULL_NAME']:$ad['DISPLAY_NAME']);
                    $style='';
                    if ($ad['LVL']==4) $style=' style="color:orange"';
                    elseif ($ad['LVL']==5) $style=' style="color:red"';
                    
                    $profileLabel =  isset($ad['PROVIDER']) ? $ad['PROVIDER']:'profile';
                    if ($userMobile) {
                        $unum = $mobileValidator->parse('+'.$userMobile,'LB');
                        $XX = $mobileValidator->getRegionCodeForNumber($unum);
                        $profileLabel = '+'.$userMobile;
                        if ($XX) { $profileLabel = '('.$XX. ')' . $profileLabel; }
                    }
                    
                    $title='<div class=user><a target=_blank onclick="openW(this.href);return false" href="'.
                            ($isSuperAdmin ? $this->router()->getLanguagePath('/admin/').'?p='.$ad['WEB_USER_ID'] : $ad['PROFILE_URL']).
                            '">'.$profileLabel.'</a><a target=_blank'.$style.' onclick="openW(this.href);return false;" href="'.
                            $this->router()->getLanguagePath('/myads/').'?u='.$ad['WEB_USER_ID'].'">'.$name.'</a>';
                    if (isset($content['userLOC'])) {
                        $geo = preg_replace('/[0-9\.]|(?:^|\s|,)[a-zA-Z]{1,3}\s/','',$content['userLOC']);
                        $geo = preg_replace('/,/', '' , $geo);
                        $title.='<span class=inf>'.$geo.'</span>';
                    }
                    else {
                        $title.='<span class="inf err">No Geo</span>';
                    }
                    
                    if ($state==1 && $ad['DATE_ADDED']==$ad['LAST_UPDATE']) {
                        $title.='<span class=inf><span class="rj ren"></span></span>';
                    }
                    
                    $title.=($phoneValidErr!==false ? ($phoneValidErr==0 ? '<span class=inf>T<i class="icn m icn-done"></i></span>' : '<span class=inf>T<i class="icn m icn-fail"></i></span>'):'' );
                    $class= '';
                    
                    if($isFeatured) {
                        $class = ' style="color:green"';
                    }
                    else if($isFeatureBooked) {
                        $class = ' style="color:blue"';
                    }
                    
                    $ss = 'W';
                    if (isset($content['app'])) {
                        if ($content['app']=='ios') {
                            $ss = 'I';
                        }
                        else if ($content['app']=='android') {
                            $ss = 'A';
                        }
                    }
                    
                    if (isset($content['ipfs']) && $content['ipfs']>=50) {
                        $ss.="/{$content['ipfs']}";
                    }
                   
                    $title.='<b'.$class.'>#'.$ad['ID'].'#' . $ss. '</b>';
                    $title.='</div>';
                
                }
                    

                if ($state==7) {
                    $liClass.='atv';
                    $link=($ad['RTL']?'/':'/en/').$ad['ID'].'/';
                    if($altText) $altlink='/en/'.$ad['ID'].'/';                        
                        
                    if ($isFeatured || $isFeatureBooked) {
                        $liClass.= ' vp';
                    }
                }
                
                if($state>6) {
                    $ad['CITY_ID']=$ad['ACTIVE_CITY_ID'];
                    $ad['COUNTRY_ID']=$ad['ACTIVE_COUNTRY_ID'];
                }
                    
                //if ($liClass) $liClass='class="'.trim($liClass).'"';
                $adClass='card myad';
                
                if ($isFeatured||$isFeatureBooked) {
                    $adClass.=' feature';
                }
                
                if ($ad['STATE']==1||$ad['STATE']==4) {
                    $adClass.=' pending';
                }
                elseif ($ad['STATE']==2) {
                    $adClass.=' approved';
                }
                if ($onlySuper) {
                    $adClass.=' alert';
                }
                // new look
                echo '<article id=', $ad['ID'], ' class="', $adClass, '" data-status=', $ad['STATE'], ' data-fetched=0';
                if ($this->user()->level()==9) {
                    echo ' data-ro=', $content['ro'], ' data-se=', $content['se'], ' pu='.$content['pu'];
                }
                if (isset($content['hl']) && in_array($content['hl'], ['en','ar'])) { echo ' data-hl="',$content['hl'], '"'; }
                echo '>';
                echo '<header>';//, $ad['STATE']==2?' class=approved>':'>';
                switch ($ad['STATE']) {
                    case 1:
                    case 4:
                        echo '<div><i class="icn m icon-state"></i>';
                        if ($onlySuper) {
                            echo '<span title="', $onlySuper, '" onmouseover="ipCheck(this)"></span>';
                        }
                        echo '<span class=msg>', $this->lang['pendingMsg'],'</span></div>';
                        echo '<span class=alloc>', ($assignedAdmin?$assignedAdmin:''), '</span>';
                        break;

                    case 2:
                        echo '<div><i class="icn m icon-state"></i><span class=msg>', $this->lang['approvedMsg'], '</span></div>';
                        if ($assignedAdmin) { echo '<span class=alloc>', $assignedAdmin, '</span>'; }
                        //echo '<div class="nb nbg"><span class="done"></span>',$this->lang['approvedMsg'],($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                        break;
                    
                    case 3:
                        echo '<div class="nb nbr"><span class="fail"></span>',$this->lang['rejectedMsg'],(isset($content['msg']) && $content['msg']? ': '.$content['msg']:''),($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                        break;
                    
                    default:
                        break;
                }
                
                echo '</header>';
                
                if ($this->user()->level()===9) { echo $title; }
                
                $userLang = '';
                if (isset($content['hl']) && in_array($content['hl'], ['en','ar'])) {
                    $userLang = $content['hl'];
                }
                
                if ($hasAdminImgs) { echo '<p class=pimgs>', $thumbs, '</p>'; }
                
                echo '<section class="card-content ', $ad['RTL']?'ar"':'en"';
                echo $link?' onclick="wo('.$link.')"' : '';
                echo '>', ($pic ? $pic :'').$text;
                echo '</section>';
                
                if ($altText) {
                    echo '<hr /><section class="card-content en"';
                    if ($altlink) {
                        echo ' onclick="wo(', $altlink, ')"';
                    }
                    elseif ($isAdmin) {
                        echo ' onselect="MSAD(this)" ';
                    }
                    echo '>',  ($pic ? $pic :''), $altText;
                    echo '</section>';
                }

                if (isset($content['extra']['m']) && $content['extra']['m']!=2 && ($content['lat']||$content['lon']) && isset($content['loc'])) {
                    echo '<hr>';
                    ?><div class='oc ocl'><span class="i loc"></span><?= $content['loc'] ?></div><?php
                }
                
                echo '<div class=note';
                if (!$isAdminProfiling && $this->user()->level()==9 && in_array($state,[1,2,3])) {
                    echo ' onclick="quickSwitch(this)"';
                }
                $isMultiCountry = false;
                echo '>', $this->getAdSection($ad, $content['ro'], $isMultiCountry), '</div>';
                //if ($state>6) {
                //    echo '<a class=com href="'.$link.'#disqus_thread" data-disqus-identifier="'.$ad['ID'].'" rel="nofollow"></a>';
                //}

                $isSuspended = $this->user->getProfile() ? $this->user->getProfile()->isSuspended() : FALSE;
                if (!$this->user->getProfile()) {
                    error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                }
                
                $isSystemAd = (isset($ad['DOC_ID']) && $ad['DOC_ID']) ? true : false;

                echo '<footer>';                
                if ($state<7) {                                        
                    if (!$isSystemAd) {
                        if(!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="ad" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class=lnk onclick="fsub(this)"><span class="rj edi"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></span><?php
                            ?></form><?php
                        }
                    
                        if (!$isAdmin || ($isAdmin && $isAdminOwner)) {
                            ?><span class=lnk onclick="adel(this)"><span class="rj del"></span><?= $this->lang['delete'] ?></span><?php
                        }
                    }
                }
                elseif ($state==7) {
                    $ad_hold=0;
                    if (isset($this->user->params['hold']) && $this->user->params['hold']==$ad['ID']) {
                        if ($this->user->holdAd($ad['ID'])) {
                            $ad_hold=1;
                            ?><b class=anb><span class="done"></span><?= $this->lang['retired'] ?></b><?php
                        }
                    }
                    
                    if (!$ad_hold) {
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && (!$isFeatured || !$isFeatureBooked)) {
                            ?><span class="lnk" onclick="<?= $isMultiCountry ? 'mCPrem()' : ($this->userBalance ? 'askPremium(this)':'noPremium()') ?>"><span class=mc24></span><?= $this->lang['make_premium'] ?></span><?php                                    
                        }
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && $isFeatureBooked) {
                            ?><span class="lnk" onclick="cancelPremium(this)"><span class="mc24"></span><?= $this->lang['stop_premium_bt'] ?></span><?php                                    
                        }                        
                        if (!$isSystemAd && (!$isAdmin || ($isAdmin && !$isFeatured && !$isFeatureBooked) || ($isAdmin && $isAdminOwner))) {
                            ?><span class="lnk" onclick="ahld(this)"><span class="rj hod"></span><?= $this->lang['hold'] ?></span><?php                                    
                        }
                        if (!$isSystemAd) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_ad'] ?></span><?php
                            ?></form><?php 
                        }
                        if ($this->router()->cfg['enabled_ad_stats'] && !$isAdminProfiling) {
                            ?><span class="stad load"></span><?php
                        }
                    }                            
                }
                elseif ($state==9) {
                    if (!$isSystemAd) {
                        if (!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_republish'] ?></span><?php
                            ?></form><?php 
                            if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                ?><span class="lnk" onclick="are(this)"><span class="rj ren"></span><?= $this->lang['renew'] ?></span><?php
                            }
                        }
                    }
                    if (!$isSystemAd && (!$isAdmin || ($isAdmin && $isAdminOwner))) {
                        ?><span class="lnk" onclick="adel(this,1)"><span class="rj del"></span><?= $this->lang['delete'] ?></span><?php 
                    }
                    if ($this->router()->config()->get('enabled_ad_stats') && !$isAdminProfiling) {
                        ?><span class="stad load"></span><?php
                    }
                }
                
               
                if ($this->user()->level()===9) {
                    if ($ad['STATE']==2 && (!$isSystemAd || $isSuperAdmin)) {
                        ?><input type="button" class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)" value="<?= $this->lang['reject'] ?>" /><?php
                        
                        if ($isSuperAdmin) {
                            ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                        }
                        $contactInfo=$this->getContactInfo($content);
                        if (!$isSystemAd || $isSuperAdmin) {
                            if ($contactInfo) {                        
                                ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                            }
                        }
                    }
                    else { 
                        if ($state>0 && $state<7) {
                            if (!$isSystemAd || $isSuperAdmin) {         
                                ?><span onclick="d.approve(this)"><?= $this->lang['approve'] ?></span><?php
                                if ($isSuperAdmin) {
                                    ?><span class="lnk" onclick="rtp(this,)">RTP</span><?php                                    
                                }
                                ?><span class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['reject'] ?></span><?php 
                            }
                            if (!$isSuperAdmin && !$onlySuper && !$isSystemAd) {
                                ?><span class="lnk" onclick="help(this)"><?= $this->lang['ask_help'] ?></span><?php
                            }                            
                            if ($isSuperAdmin && $ad['USER_RANK'] < 2) {
                                ?><span class="lnk" onclick="banF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['block'] ?></span><?php 
                            }
                            if (!$isSystemAd) {
                                if ($ad['USER_RANK'] < 3) {
                                    ?><span class="lnk" onclick="suspF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['suspend'] ?></span><?php
                                }
                            }
                            if ($isSuperAdmin && $filters['uid']==0) {
                                ?><a class="lnk" href="/myads/<?= $this->router()->language=='ar'?'':'en/' ?>?sub=pending&fuid=<?= $ad['WEB_USER_ID'] ?>"><?= $this->lang['user_type_option_1'] ?></a><?php
                            }
                            
                            $contactInfo=$this->getContactInfo($content);                          
                            if ($isSuperAdmin) {
                                //onclick="openW(this.href);return false"
                                ?><a target=_blank href="<?= $this->router()->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                            }
                            if (!$isSystemAd || $isSuperAdmin) {
                                if ($contactInfo) {                        
                                    ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                                }
                            }                            
                        }
                    }
                }     
               
                
                echo '</footer>';
                echo '</article>';
                
                /*
                ?><li id="<?= $ad['ID'] ?>" <?= $liClass ?><?= $ad['STATE']==2 ? ' status="2" class="approved"' : ($ad['STATE']==3 ? ' status="3" class="approved"' : '') ?><?= ($this->user->info['level']==9 ? ' ro="'.$content['ro'].'" se="'.$content['se'].'" pu="'.$content['pu'].'"':'') ?>><?php
                
                
                if ($ad['STATE']==1||$ad['STATE']==4) {
                    echo '<div class="nb nbw">' .($onlySuper ? '<span title="'.$onlySuper.'" onmouseover="ipCheck(this)" class="fail"></span>' : '<span class="wait"></span>') ,$this->lang['pendingMsg'], ($assignedAdmin ? $assignedAdmin:'') , '</div>';
                    
                    $this->globalScript.='var adReqs={};var ipCheck=function(e){
                        var e = $(e);
                        var id=e.parent().parent()[0].id;
                        if(typeof adReqs[id] === "undefined"){
                            $.ajax({
                                url:"/ajax-changepu/",
                                type:"GET",
                                data:{
                                    fraud:id
                                },
                                success:function(rp){
                                    if(typeof rp["fraud_score"] !== "undefined"){
                                        adReqs[id]=1;
                                        var ttl = e.attr("title"); 
                                        
                                        ttl += " | score: "+ rp["fraud_score"];
                                        if(rp["mobile"]) ttl += " | mobile";
                                        if(rp["recent_abuse"]) ttl += " | abuse";
                                        if(rp["proxy"]) ttl += " | proxy";
                                        if(rp["vpn"])   ttl += " | VPN";
                                        if(rp["tor"])   ttl += " | TOR";
                                        ttl += " | "+rp["city"]+", "+rp["country_code"];
                                        e.attr("title",ttl);
                                        e.mouseover();
                                    }
                                }
                            });
                        }
                            };';
                    
                }
                elseif ($ad['STATE']==2) {
                    echo '<div class="nb nbg"><span class="done"></span>',$this->lang['approvedMsg'],($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                }
                elseif ($ad['STATE']==3) {
                    echo '<div class="nb nbr"><span class="fail"></span>',$this->lang['rejectedMsg'],(isset($content['msg']) && $content['msg']? ': '.$content['msg']:''),($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                }
                if ($this->user()->level()===9) { echo $title; }
                
                
                $userLang = '';
                if (isset($content['hl']) && in_array($content['hl'], ['en','ar'])) {
                    $userLang = $content['hl'];
                }
                
                if ($hasAdminImgs) { echo '<p class=pimgs>', $thumbs, '</p>'; }
                
                ?><p<?= ($link ? ' onclick="wo(\''.$link.'\')"': ($isAdmin ? ' onclick="EAD(this,1)" onselect="MSAD(this)" ' : '') ).($userLang ? ' lang="'.$userLang.'"':'') ?> class='<?= $textClass ?>'><?= ($pic ? $pic :'').$text ?></p><?php
                if($altText){
                    ?><p<?= ($altlink ? ' onclick="wo(\''.$altlink.'\')"': ($isAdmin ? ' onclick="EAD(this,2)" onselect="MSAD(this)" ' : '') ) ?> class='en alt'><?= ($pic ? $pic :'').$altText ?></p><?php
                }
                
                if(isset($content['extra']['m']) && $content['extra']['m']!=2 && ($content['lat']||$content['lon']) && isset($content['loc'])){
                    ?><div class='oc ocl'><span class="i loc"></span><?= $content['loc'] ?></div><?php
                }
                   
                ?><div class="cct"<?php 
                if(!$isAdminProfiling && $this->user->info['level']==9 && in_array($state,[1,2,3])){
                    echo ' onclick="quickSwitch(this)"';
                }
                $isMultiCountry = false;
                ?>><?=  $this->getAdSection($ad, $content['ro'],$isMultiCountry).($state>6?'<a class="com" href="'.$link.'#disqus_thread" data-disqus-identifier="'.$ad['ID'].'" rel="nofollow"></a>':'') ?></div><?php
                
                $isSuspended = $this->user->getProfile() ? $this->user->getProfile()->isSuspended() : FALSE;
                if (!$this->user->getProfile()) {
                    error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                }
                
                $isSystemAd = (isset($ad['DOC_ID']) && $ad['DOC_ID']) ? true : false;
                
                ?><div class='oc'><?php
                if ($state<7) {                                        
                    if(!$isSystemAd) {
                        if(!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="ad" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></span><?php
                            ?></form><?php
                        }
                    }
                    if(!$isSystemAd && (!$isAdmin || ($isAdmin && $isAdminOwner))){
                        ?><span class="lnk" onclick="adel(this)"><span class="rj del"></span><?= $this->lang['delete'] ?></span><?php
                    }
                }
                elseif ($state==7) {
                    $ad_hold=0;
                    if (isset($this->user->params['hold']) && $this->user->params['hold']==$ad['ID']) {
                        if ($this->user->holdAd($ad['ID'])) {
                            $ad_hold=1;
                            ?><b class="anb"><span class="done"></span><?= $this->lang['retired'] ?></b><?php
                        }
                    }
                    
                    if (!$ad_hold) {
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && (!$isFeatured || !$isFeatureBooked)) {
                            ?><span class="lnk" onclick="<?= $isMultiCountry ? 'mCPrem()' : ($this->userBalance ? 'askPremium(this)':'noPremium()') ?>"><span class="mc24"></span><?= $this->lang['make_premium'] ?></span><?php                                    
                        }
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && $isFeatureBooked) {
                            ?><span class="lnk" onclick="cancelPremium(this)"><span class="mc24"></span><?= $this->lang['stop_premium_bt'] ?></span><?php                                    
                        }                        
                        if (!$isSystemAd && (!$isAdmin || ($isAdmin && !$isFeatured && !$isFeatureBooked) || ($isAdmin && $isAdminOwner))) {
                            ?><span class="lnk" onclick="ahld(this)"><span class="rj hod"></span><?= $this->lang['hold'] ?></span><?php                                    
                        }
                        if (!$isSystemAd) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_ad'] ?></span><?php
                            ?></form><?php 
                        }
                        if ($this->router()->cfg['enabled_ad_stats'] && !$isAdminProfiling) {
                            ?><span class="stad load"></span><?php
                        }
                    }                            
                }
                elseif ($state==9) {
                    if (!$isSystemAd) {
                        if (!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_republish'] ?></span><?php
                            ?></form><?php 
                            if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                ?><span class="lnk" onclick="are(this)"><span class="rj ren"></span><?= $this->lang['renew'] ?></span><?php
                            }
                        }
                    }
                    if (!$isSystemAd && (!$isAdmin || ($isAdmin && $isAdminOwner))) {
                        ?><span class="lnk" onclick="adel(this,1)"><span class="rj del"></span><?= $this->lang['delete'] ?></span><?php 
                    }
                    if ($this->router()->cfg['enabled_ad_stats'] && !$isAdminProfiling) {
                        ?><span class="stad load"></span><?php
                    }
                }
                       
                if ($this->user->info['level']==9) {
                    if ($ad['STATE']==2 && (!$isSystemAd || $isSuperAdmin)) {
                        ?><input type="button" class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)" value="<?= $this->lang['reject'] ?>" /><?php
                        
                        if ($isSuperAdmin) {
                            ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                        }
                        $contactInfo=$this->getContactInfo($content);
                        if (!$isSystemAd || $isSuperAdmin) {
                            if ($contactInfo) {                        
                                ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                            }
                        }
                    }
                    else { 
                        if ($state>0 && $state<7) {
                            if (!$isSystemAd || $isSuperAdmin) {         
                                ?><span class="lnk" onclick="app(this)"><?= $this->lang['approve'] ?></span><?php
                                if ($isSuperAdmin) {
                                    ?><span class="lnk" onclick="rtp(this,)">RTP</span><?php                                    
                                }
                                ?><span class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['reject'] ?></span><?php 
                            }
                            if (!$isSuperAdmin && !$onlySuper && !$isSystemAd) {
                                ?><span class="lnk" onclick="help(this)"><?= $this->lang['ask_help'] ?></span><?php
                            }                            
                            if ($isSuperAdmin && $ad['USER_RANK'] < 2) {
                                ?><span class="lnk" onclick="banF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['block'] ?></span><?php 
                            }
                            if (!$isSystemAd) {
                                if ($ad['USER_RANK'] < 3) {
                                    ?><span class="lnk" onclick="suspF(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['suspend'] ?></span><?php
                                }
                            }
                            if ($isSuperAdmin && $filters['uid']==0) {
                                ?><a class="lnk" href="/myads/<?= $this->router()->language=='ar'?'':'en/' ?>?sub=pending&fuid=<?= $ad['WEB_USER_ID'] ?>"><?= $this->lang['user_type_option_1'] ?></a><?php
                            }
                            
                            $contactInfo=$this->getContactInfo($content);                          
                            if ($isSuperAdmin) {
                                ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                            }
                            if (!$isSystemAd || $isSuperAdmin) {
                                if ($contactInfo) {                        
                                    ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                                }
                            }                            
                        }
                    }
                }    
                
                ?></div><?php                                        
                ?></li><?php*/
                $idx++;
            }
            
            //echo '</div';
            
            if ($state==7) {
                if($this->userBalance){
                    ?><div id="make_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['balance'].': '.$this->userBalance ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-hint"><?= $this->lang['premium_hint'] ?></div><?php 
                            ?><div class="dialog-box"><?php 
                                ?><ul><?php
                                ?><li><?= $this->lang['premium_days'] ?>:</li><?php
                                ?><li><select id="spinner" max="<?= $this->userBalance ?>"></select></li><?php
                                ?></ul><?php
                            ?></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['make'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="confirm_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['please_confirm'] ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-box"></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['deal'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="stop_premium" class="dialog premium"><?php
                        ?><div class="dialog-box"><?= $this->lang['stop_premium'] ?></div><?php 
                        ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                }else{                
                    ?><div id="what_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['make_premium'] ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-box"><?= $this->lang['no_balance_dialog'] ?></div><?php 
                            ?><div class="dialog-action"><input type="button" value="<?= $this->lang['back'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="stop_premium" class="dialog premium"><?php
                        ?><div class="dialog-box"><?= $this->lang['stop_premium'] ?></div><?php 
                        ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                }
                ?><div id="stop_ad" class="dialog"><?php
                    ?><div class="dialog-box"><?= $this->lang['stop_ad'] ?></div><?php 
                    ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                ?></div><?php
            }
            
            ?><div id=delete_ad class=dialog style="display:none"><?php
                ?><div class=dialog-box><?= $this->lang['delete_ad'] ?></div><?php 
                ?><div class=dialog-action><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= ucfirst($this->lang['delete']) ?>" /></div><?php 
            ?></div><?php
                
            if ($isSuperAdmin) { 
                ?><div id=rtp_dialog class=dialog style="display:none"><?php
                    ?><div class="dialog-box ctr"><input type="button" class="approve bt" value="<?= ucfirst($this->lang['approve']) ?>" /></div><?php 
                    ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= ucfirst($this->lang['reject']) ?>" /></div><?php 
                ?></div><?php
            }    
            
            if($hasNext || $hasPrevious){
                ?><div class="pgn"><div class="card"><?php 
                echo ($currentOffset+1).' '.$this->lang['of'].' '.ceil($allCounts/$recNum);
                $appendOp = '?';
                $link = $this->router()->uri.($this->router()->isArabic()?'':$this->router()->language.'/');
                
                
                $sub=filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                switch ($sub) {
                    case 'pending':
                        $link.='?sub=pending';
                        $appendOp='&';
                        break;
                    
                    case 'drafts':
                        $link.='?sub=drafts';
                        $appendOp='&';
                        break;
                    
                    case 'archive':
                        $link.='?sub=archive';
                        $appendOp='&';
                        break;
                    
                    case '':
                    default:
                        break;
                }
                
                if(isset($_GET['u']) && $_GET['u']){
                    $link.=$appendOp.'u='.$_GET['u'];
                    $appendOp='&';
                }
                
                if(isset($_GET['a']) && $_GET['a']){
                    $link.=$appendOp.'a='.$_GET['a'];
                    $appendOp='&';
                }
                
                if($filters['uid']){
                    $link.=$appendOp.'fuid='.$filters['uid'];
                    $appendOp='&';
                }                
                if($filters['root']){
                    $link.=$appendOp.'fro='.$filters['root'];
                    $appendOp='&';
                }                
                if($filters['purpose']){
                    $link.=$appendOp.'fpu='.$filters['purpose'];
                    $appendOp='&';
                }               
                if($filters['lang']){
                    $link.=$appendOp.'fhl='.$filters['lang'];
                    $appendOp='&';
                }
                
                if($hasPrevious){
                    $offset = $currentOffset - 1;
                    ?><a class=float-left href='<?= $link.($offset ? $appendOp.'o='.$offset : '') ?>'><?= $this->lang['prev_50'] ?></a><?php
                }
                if($hasNext){
                    ?><a class=float-right href='<?= $link.$appendOp.'o='.($currentOffset + 1)  ?>'><?= $this->lang['next_50'] ?></a><?php
                }
                ?></div></div><?php
            }
            
            if ($this->user->info['level']==9 && $state<7) {
                ?><div id=rejForm class="rpd cct" style="display:none"><select id="rejS" onchange="psrej(this)"></select><?php
                ?><textarea id="rejT" onkeydown="idir(this)" onchange="idir(this,1)"></textarea><?php
                ?><input type="button" class="bt" value="<?= $this->lang['reject'] ?>" /><?php
                ?><input class="bt cl" type="button" value="<?= $this->lang['cancel'] ?>" /><?php 
                ?></div><?php
                ?><div id=suspForm class="rpd cct" style="display:none"><select id="suspT"></select><?php
                ?><textarea style="height:100px" onkeydown="idir(this)" onchange="idir(this,1)" id="suspM" placeholder="<?= $this->lang['reason_suspension'] ?>"></textarea><?php
                ?><input type="button" class="bt" onclick="suspA(this)" value="<?= $this->lang['suspend'] ?>" /><?php
                ?><input class="bt cl" type="button" onclick="suspC(this)"  value="<?= $this->lang['cancel'] ?>" /></div><?php
                ?><div id="banForm" class="rpd cct" style="display:none"><textarea id="banT" onkeydown="idir(this)" onchange="idir(this,1)"></textarea><?php
                ?><input type="button" class="bt" value="<?= $this->lang['block'] ?>" /><?php
                ?><input class="bt cl" type="button" value="<?= $this->lang['cancel'] ?>" /></div><?php
            }
        } // end ad count>0
        else {
            ?><p class="ph phb db"><?php
            $msg='';
            $mcUser = null;
            switch ($state){
                case 9:
                    $msg=  $this->lang['no_archive'];
                    $this->user->info['archive_ads']=$count;
                    echo $this->lang['ads_archive'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 7:
                    $msg=  $this->lang['no_active'];
                    $this->user->info['active_ads']=$count;
                    echo $this->lang['ads_active'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 1:
                case 2:
                case 3:
                    $msg=  $this->lang['no_pending'];
                    $this->user->info['pending_ads']=$count;
                    echo $this->lang['ads_pending'].($count ? ' ('.$count.')':'');
                    break;
                case 0:
                default:
                    $msg=  $this->lang['no_drafts'];
                    $this->user->info['draft_ads']=$count;
                    echo $this->lang['ads_drafts'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
            }
            ?></p><?php            
            $this->renderEditorsBox($state, true);
            
            if($this->user->info['level']==9 && $mcUser && $mcUser->isBlocked()) {
                $msg = 'User is Blocked';
                $reason = preg_replace(['/\</','/\>/'],['&#60;','&#62;'], Core\Model\NoSQL::getInstance()->getBlackListedReason($mcUser->getMobileNumber()));
                if ($reason) {
                    $msg = $msg.'<br />'.$reason;
                }
            }
            
            ?><div class="htf db"><?= $msg ?><br /><br /><?php
            ?><input onclick="document.location='/post/<?= $lang ?>'" class="bt" type="button" value="<?= $this->lang['create_ad'] ?>" /><?php
            ?></div><?php
        }
        include $this->router()->config()->baseDir.'/web/js/includes/myads.js';
    }
    
    
    function getContactInfo($content) {
        $contactInfo='';
        if (isset($content['cui'])) {
            if (isset($content['cui']['p'])) { 
                $phone=$content['cui']['p'];
                if (count($phone)) {
                    foreach ($phone as $p) {
                        if($contactInfo) $contactInfo.='|';
                        $contactInfo.=urlencode('"'.substr($p['v'],1).'"');
                    }
                }
            }

            if(isset($content['cui']['e']) && $content['cui']['e']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['e'].'"');
            }
            if(isset($content['cui']['b']) && $content['cui']['b']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['b'].'"');
            }
            if(isset($content['cui']['t']) && $content['cui']['t']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['t'].'"');
            }
            if(isset($content['cui']['s']) && $content['cui']['s']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['s'].'"');
            }
        }
        return $contactInfo;
    }

    
    function renderUserTypeSelector(&$user=null) {
        if ($this->user->info['id'] && $this->user->info['level']==9) {
            $userId = $this->user->info['id'];
            $type = 0;
            if (isset($_GET['u']) && is_numeric($_GET['u']) && $_GET['u']) {
                $userId = $_GET['u'];
                $type = \Core\Model\NoSQL::getInstance()->getUserPublisherStatus($userId); 
            }
            $user = new MCUser($userId);
            if ($user->isSuspended()) {
                $time = MCSessionHandler::checkSuspendedMobile($user->getMobileNumber());
                $hours=0;
                $lang=$this->router()->siteLanguage;
                if ($time) {
                    $hours = $time / 3600;
                    if (ceil($hours)>1) {
                        $hours = ceil($hours);
                        if ($lang=='ar') {
                            if ($hours==2) {
                                $hours='ساعتين';
                            }
                            elseif ($hours>2 && $hours<11) {
                                $hours=$hours.' ساعات';
                            }
                            else {
                                $hours = $hours.' ساعة';
                            }
                        }
                        else {
                            $hours = $hours.' hours';
                        }
                    }
                    else {
                        $hours = ceil($time / 60);
                        if ($lang=='ar') {
                            if ($hours==1) {
                                $hours='دقيقة';
                            }
                            elseif ($hours==2) {
                                $hours='دقيقتين';
                            }
                            elseif ($hours>2 && $hours<11) {
                                $hours=$hours.' دقائق';
                            } 
                            else {
                                $hours = $hours.' دقيقة';
                            }
                        }
                        else {
                            if ($hours>1) {  
                                $hours = $hours.' minutes';
                            }
                            else {                        
                                $hours = $hours.' minute';
                            }
                        }
                    }
                }
                echo '<span class="fl"><span class="wait"></span>'.$hours.'</span>';
            }
            echo '<span class="fl">'.$this->lang['user_type_label'].': <select onchange="setUT(this,'.$userId.')"><option value="0">'.$this->lang['user_type_option_0'].'</option><option value="1"'.($type == 1 ? ' selected':'').'>'.$this->lang['user_type_option_1'].'</option><option value="2"'.($type == 2 ? ' selected':'').'>'.$this->lang['user_type_option_2'].'</option></select></span>';            
        }
    }
    
}
            

?>