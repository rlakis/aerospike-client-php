<?php
Config::instance()->incLayoutFile('Page');

class PostAd extends Page {
    private $adContent = null;
    var $unit='',$globalScript='',$inlineScript='',$presetTitle=false,$id=0,$advanced=false;
    var $pageGlobal='',$pageInline='',$ad=null, $loadColorBox=false, $sectionCrumb='', $userBalance=0;
        
    function __construct(Core\Model\Router $router) {
        parent::__construct($router);
        
        if($this->router()->config()->isMaintenanceMode()) {
            $this->user()->redirectTo($this->router()->getLanguagePath('/maintenance/'));
        }
        
        $this->checkBlockedAccount();
        $this->checkSuspendedAccount();
        
        $tmp = $this->get('ad');
        if ($tmp=='new') {
            unset($this->user->pending['post']);
            $this->user->update();
        }
        
        //syslog(LOG_INFO, json_encode($this->user->info));        
        $this->router()->config()->setValue('enabled_sharing', 0);
        $this->router()->config()->disableAds();               
        
        if ($this->router()->isArabic()) {
            $this->inlineCss.='.upload_bt input{direction:ltr!important}';
        }
        else {
            $this->inlineCss.='.upload_bt input{direction:rtl!important}';
        }
        $this->inlineCss.='.upload_bt{white-space:nowrap!important;overflow:visible}';
        $this->load_lang(array("post"));
        $this->set_require('css', array('post'));
        $this->title=$this->lang['title'];

        if ($this->user->params['country']) {
            $this->router()->countryId=$this->user->params['country'];
        }
        if ($this->user->params['city']) {
            $this->router()->cityId=$this->user->params['city'];
        }
        //$this->isUserMobileVerified=false;
        $this->hasLeadingPane = $this->user->info['id'] && !$this->isUserMobileVerified;
        if ($this->user()->isLoggedIn()) {
            if(!$this->isUserMobileVerified){
                $this->title=$this->lang['verify_mobile'];
                $this->set_require('css', array('select2'));
                $this->inlineCss.='div.row{display:block}.phwrap{padding:0 20px}p.ph{padding-bottom:15px;line-height:28px;border:0px;background-color:transparent;width:100%}p.corr{padding-bottom:0px;margin-bottom:0px}#main{background-color:#FFF;border:0;padding-bottom:20px}';
                $this->inlineCss.='
                    .ph.num{direction:ltr}
                    .row .bt{width:200px}
                    .row.err{padding:10px 0;color:red}
                    #code{width:272px;visibility:hidden;height:28px}
                        #number,#vcode{direction:ltr;font-size:22px;width:250px;padding:10px;border:1px solid #aaa;border-radius:4px;text-align:center}
                        #mb_check ul{display:block;width:100%;overflow:hidden;background-color:#ececec;margin-bottom:5px}
                        #mb_check li{padding:15px 10px;height: 60px;line-height: 50px}                        
                        ';
                if($this->router()->siteLanguage=='ar'){                    
                    $this->inlineCss.='
                        #mb_check li{float:right}
                        #mb_check li:last-child{float:left}
                    ';
                }else{
                    $this->inlineCss.='
                        #mb_check li{float:left}
                        #mb_check li:last-child{float:right}
                    ';
                }
                if(isset($this->user->pending['mobile'])){
                    $this->inlineCss .= '#mb_notice{display:none}';                
                }else{                
                    $this->inlineCss .= '#mb_validate{display:none}';
                }
                $this->inlineCss .= '#mb_check{display:none}';
                $this->inlineCss .= '#mb_load{display:none}';
                $this->inlineCss .= '#mb_done{display:none}';
                
                if($this->isMobile){
                    $this->inlineCss.='
                        #mb_check li{padding:5px;height:auto;line-height:25px}
                    ';
                    if($this->router()->siteLanguage=='ar'){                    
                        $this->inlineCss.='
                            #mb_check li{text-align:right}
                        ';
                    }else{
                        $this->inlineCss.='
                            #mb_check li{text-align:left}
                        ';
                    }
                }
            }
            else {
                if (isset ($_REQUEST['ad']) && is_numeric($_REQUEST['ad'])) {
                    $this->ad=$this->user->loadAdToSession($_REQUEST['ad']);
                    $this->id=$this->user->pending['post']['id'];
                    $this->countryId=$this->user->pending['post']['cn'];
                    $this->cityId=$this->user->pending['post']['c'];
                    $this->sectionId=$this->user->pending['post']['se'];
                    $this->purposeId=$this->user->pending['post']['pu'];
                    $this->rootId=$this->user->pending['post']['ro'];
                    $this->adContent=json_decode($this->user->pending['post']['content'],true);
                    if ($this->user->info['id']==$this->user->pending['post']['user']) {
                        $this->user->saveAd(0, $this->user->info['id']);
                    }
                }

                if (isset($_REQUEST['adr']) && is_numeric($_REQUEST['adr'])) {
                    //$res = $this->user->renewAd($_POST['adr'],0);
                    $id = $_REQUEST['adr'];
                    if ($id) {
                        $this->user->holdAd($id);
                        $this->ad=$this->user->loadAdToSession($id);
                        $this->id=$this->user->pending['post']['id']; 
                        $this->user->pending['post']['state']=0;
                        $this->countryId=$this->user->pending['post']['cn'];
                        $this->cityId=$this->user->pending['post']['c'];
                        $this->sectionId=$this->user->pending['post']['se'];
                        $this->purposeId=$this->user->pending['post']['pu'];
                        $this->rootId=$this->user->pending['post']['ro'];
                        $this->adContent=json_decode($this->user->pending['post']['content'],true);
                        $this->user->update();
                    }
                }

                if (!isset ($this->user->pending['post'])){
                    $this->user->loadAdToSession(0);
                }

                $this->adContent=json_decode($this->user->pending['post']['content'],true);
                $this->rootId=isset($this->adContent['ro']) ? (int)$this->adContent['ro'] : 0;
                $this->purposeId=(int)$this->user->pending['post']['pu'];
                $this->sectionId=(int)$this->user->pending['post']['se'];
                $this->countryId=(int)$this->user->pending['post']['cn'];
                $this->cityId=(int)$this->user->pending['post']['c'];
            }

            $this->userBalance = $this->user->getStatement(0, 0, true);
            if(isset($this->userBalance['balance'])){
                $this->userBalance = $this->userBalance['balance'];
            }
        }

        if($this->router()->isApp > '1.0.4'){
            $this->inlineCss.='.spimg{width:300px!important}';
        }
        
        if($this->router()->isApp){
            $this->inlineCss.='body{margin-top:0!important}';
        }
        
        $this->render();
    }  
    
    
    function mainMobile() {
        if (!$this->user()->isLoggedIn()) { return; }
            
        if ($this->isUserMobileVerified) {
            
            $seqHide=false;
            $preview='';
            $altPreview='';
            $maximumChars=400;
            $minimumChars=30;
            
            if (!isset($this->adContent['lat'])) $this->adContent['lat']=0;
            if (!isset($this->adContent['lon'])) $this->adContent['lon']=0;
            if (!isset($this->adContent['loc'])) $this->adContent['loc']='';
            
            $uAlt=0;
            if(isset($this->adContent['extra']['t']))$uAlt=$this->adContent['extra']['t'];
            
            $uVideo=0;
            if(isset($this->adContent['extra']['v']))$uVideo=$this->adContent['extra']['v'];
            
            $uPics=0;
            if(isset($this->adContent['extra']['p']))$uPics=$this->adContent['extra']['p'];
            
            $uMap=0;
            if(isset($this->adContent['extra']['m']))$uMap=$this->adContent['extra']['m'];
            
            $hasMap=0;
            if ($this->adContent['lat'] && $this->adContent['lon'] && $this->adContent['loc']) {
                $hasMap=1;
                $uMap=1;
            }
            elseif ($uMap!=2) {
                $uMap=0;
            }
            
            if ($uMap==2) {
                $this->adContent['lat']=0;
                $this->adContent['lon']=0;
                $this->adContent['loc']='';
            }
            
            $adRTL=($this->router()->isArabic() ? 1 : 0);          
            $altRTL=($this->router()->isArabic() ? 0 : 1);     
            
            //load contact time vars
            if (!isset($this->adContent['cut']) || (isset($this->adContent['cut']) && !is_array($this->adContent['cut']))) {
                if (isset($this->user->info['options']['cut']) && is_array($this->user->info['options']['cut'])) {
                    $this->adContent['cut']=$this->user->info['options']['cut'];
                }
                else {
                    $this->adContent['cut']=["t"=>-1, "b"=>24, "a"=>6];
                }
            }
            
            $hasContactTime = ($this->adContent['cut']['t']>=0);
            
            //load contact info
            if (!isset($this->adContent['cui']) || (isset($this->adContent['cui']) && !is_array($this->adContent['cui']))) {
                if (isset($this->user->info['options']['cui']) && is_array($this->user->info['options']['cui'])) {
                    $this->adContent['cui']=$this->user->info['options']['cui'];
                }
                else {
                    $this->adContent['cui']=["p"=>[], "b"=>"", "e"=>"", "s"=>"", "t"=>""];
                }
            }     
            if (!isset($this->adContent['cui']['p']) || (isset($this->adContent['cui']['p']) && !is_array($this->adContent['cui']['p']))) {
                $this->adContent['cui']['p']=array();
            }
            if (!isset($this->adContent['cui']['e'])) { $this->adContent['cui']['e']=''; }
            if (!isset($this->adContent['cui']['b'])) { $this->adContent['cui']['b']=''; }
            if (!isset($this->adContent['cui']['s'])) { $this->adContent['cui']['s']=''; }
            if (!isset($this->adContent['cui']['t'])) { $this->adContent['cui']['t']=''; }
            $hasContactNumbers = (count($this->adContent['cui']['p']));
            $hasContact = ($this->adContent['cui']['e'] || $this->adContent['cui']['s'] || $this->adContent['cui']['t'] || $this->adContent['cui']['b'] || $hasContactNumbers);
            
            if ($hasContactNumbers) {
                $fixedArray = [];
                foreach ($this->adContent['cui']['p'] as $num) { 
                    if (!isset($num['r']) || (isset($num['r']) && $num['r']=='')) {
                        $num['r'] = $num['v'];
                        $fixedArray[] = $num;
                    }
                    else {
                        $fixedArray[] = $num;
                    }
                }
                $this->adContent['cui']['p'] = $fixedArray;
            }
            
            //load ad content
            $other='';
            $altOther='';
            $save=0;
            if (isset($this->adContent['other']) && $this->adContent['other']) {
                $save=1;
                $other=$this->adContent['other'];
                $other=preg_replace('/\x{200B}.*/u', '', $other);
                $adRTL=preg_match('/[\x{0621}-\x{064a}]/u', $other);
                $preview=$other;
                $preview.=$this->user->parseUserAdTime($this->adContent['cui'], $this->adContent['cut'], $adRTL);
            }
            
            if (isset($this->adContent['altother']) && $this->adContent['altother']) {
                $altOther=$this->adContent['altother']; 
                $altOther=preg_replace('/\x{200B}.*/u', '', $altOther);
                $altRTL=preg_match('/[\x{0621}-\x{064a}]/u', $altOther);
                $altPreview=$altOther;
                $altPreview.=$this->user->parseUserAdTime($this->adContent['cui'], $this->adContent['cut'], $altRTL);
            }
            
            $otherLength=mb_strlen($other,'UTF-8');
            $altLength=mb_strlen($altOther,'UTF-8');
            $hasContent=$otherLength<30 ? 0 : 1;
            $hasAltContent=$altLength<30 ? 0 : 1;
            if ($hasAltContent) { $uAlt=1; }

            //load publishing countries
            $hasLocs=(isset($this->adContent['pubTo']) && count($this->adContent['pubTo']));
            if ($hasLocs) {
                foreach ($this->adContent['pubTo'] as $key => $val) {
                    if (!is_numeric($key)) { unset($this->adContent['pubTo'][$key]); }
                }
            }
            
            $hasPics=0;
            if (isset ($this->adContent['pics'])) $hasPics=count($this->adContent['pics']);
            if($hasPics)$uPics=1;
            elseif($uPics!=2) $uPics=0;
            $hasVideo=0;
            if (isset ($this->adContent['video'])) {
                $hasVideo=$this->adContent['video'][0] ? 2 : 1;
                $this->globalScript.='var tvl="<a class=\'ctr ah\' target=\'blank\' href=\''.$this->adContent['video'][1].'&autoplay=1\'><span onclick=\'delV(this)\' title=\''.$this->lang['removeVideo'].'\' class=\'button pz pzd\'></span><img src=\''.$this->adContent['video'][2].'\' width=\'250\' height=\'200\' /><span class=\'play\'></span></a>";';
            }
            else {
                $this->globalScript.='var tvl="";';
            }
            if ($hasVideo) $uVideo=1; elseif($uVideo!=2) $uVideo=0;
            
            $this->globalScript.='var isrc="'.$this->router()->config()->adImgURL.'/repos/",SAVE='.$save.',extra={t:'.$uAlt.',v:'.$uVideo.',p:'.$uPics.',m:'.$uMap.'},hasAT='.$hasAltContent.',brtl='.$altRTL.',hasT='.$hasContent.',artl='.$adRTL.',pro='.$this->rootId.',ppro='.$this->rootId.',ppu='.$this->purposeId.',pse='.$this->sectionId.',pc=[],pcl=0,pzv='.($hasContact ? 1:0).',hNum='.($hasContactNumbers ? 1 : 0).',cui='.json_encode($this->adContent['cui']).',cut='.json_encode($this->adContent['cut']).',cutS="",maxC='.$maximumChars.',minC='.$minimumChars.',picL='.$hasPics.',hasVd='.$hasVideo.',hasM='.$hasMap.',lat='.$this->adContent['lat'].',lon='.$this->adContent['lon'].',HOME="'.$this->router()->getURL($this->router()->countryId,$this->router()->cityId).'";';
            
            $budget = (isset($this->adContent['budget']) && is_numeric($this->adContent['budget']) ? $this->adContent['budget'] : 0);
            
            $this->globalScript.='var ad={
                hl:"'.(isset($this->user->info['options']['lang']) && $this->user->info['options']['lang'] ? $this->user->info['options']['lang']:'').'",
                id:'.$this->user->pending['post']['id'].',
                user:'.($this->user->pending['post']['user'] ? $this->user->pending['post']['user'] : $this->user->info['id']).',
                state:'.$this->user->pending['post']['state'].',
                lat:'.$this->adContent['lat'].',
                lon:'.$this->adContent['lon'].',
                loc:"'.$this->adContent['loc'].'",
                budget:'.$budget.',
                version:2'
                .(isset($this->adContent['app']) ? ',app:"'.$this->adContent['app'].'"':'').
                (isset($this->adContent['app_v']) ? ',app_v:"'.$this->adContent['app_v'].'"':'').
                '
            };';
            
            //prefetch country_code info
            $setccv=0;
            if (isset($this->user->params['country']) && $this->user->params['country']) {
                $cid=$this->user->params['country'];
                $q='select c.code, c.id, c.name_ar, c.name_en, c.locked, trim(id_2) from country c';
                $cc=$this->urlRouter->db->queryCacheResultSimpleArray('country_codes_req', $q, null, 1, $this->router()->config()->get('ttl_long'));
                if (isset($cc[$cid])){
                    $setccv=1;
                    $this->globalScript.='var ccv={c:'.$cc[$cid][0].',n:'.($cc[$cid][4] ? $cc[$cid][1]:0).',en:"'.$cc[$cid][3].'",ar:"'.$cc[$cid][2].'",i:"'.$cc[$cid][5].'"};';
                }
            }
            if(!$setccv) {
                $this->globalScript.='var ccv={c:961,n:1,en:"Lebanon",ar:"لبنان",i:"LB"};';
            }
            if ($hasLocs && $this->rootId && in_array($this->rootId, array(1,2,99)) && (!$this->sectionId || ($this->sectionId && !in_array($this->sectionId,array(748,766,223,924)) )) && count($this->adContent['pubTo'])>1) $hasLocs=false;
            if ($hasLocs) {
                $this->globalScript.='pcl='.count($this->adContent['pubTo']).';';
                foreach ($this->adContent['pubTo'] as $key=>$val){
                    if(isset($this->urlRouter->cities[$key])){
                        $this->globalScript.='pc['.$key.']="'.$this->urlRouter->cities[$key][$this->fieldNameIndex].'";';
                    }
                }
            }
            
            if($hasPics){ 
                $this->globalScript.='var imgs=[];';
                ?><style type="text/css"><?php
                $k=0;
                foreach($this->adContent['pics'] as $key => $val){
                    if(isset($val[0]) && $val[1]){
                        $swi = $val[0];
                        $she = $val[1];
                        if($this->isMobile){
                            if($swi>300){
                                $she = (int)( (300 * $she) / $swi ); 
                            }
                        }
                        ?>.sp<?= $k ?>{width:<?= $swi ?>px;height:<?= $she ?>px!important;display:inline-block}<?php
                        
                    }
                    $k++;
                }
                ?></style><?php
            }
            
            echo '<div class=col-12><div class=card>';
            if ($this->user()->isLoggedIn(9)) {
                $this->globalScript.='var PVC=1;';                
                echo '<div class=card-content>', '<a class="btn blue float-right" href="javascript:void(0)" onclick="toLower()">Lower case</a></div>';               
            }
             echo '</div></div>';
                
            //pictures
            echo '<div class=col-12><div class=card><div class=card-header><div class=card-title><h5>', "Pictures", '</h5></div></div>';
            echo '<div class="card-content pictures">';
            for ($i=0; $i<5; $i++){
                echo '<span class=pix data-index=', $i, '></span>';
            }
            echo '</div>';
            echo '</div></div>';
            
            echo '<div class=col-12><div class=card><div class=card-header><div class=card-title><h5>', "Ad Text 1", '</h5></div></div>';
            echo '<div class="card-content">';
            ?><textarea></textarea><?php
            echo '</div>';
            echo '</div></div>';
            
            echo '<div class=col-12><div class=card><div class=card-header><div class=card-title><h5>', "Ad Text 2", '</h5></div></div>';
            echo '<div class="card-content">';
            ?><textarea></textarea><?php
            echo '</div>';
            echo '</div></div>';

            echo '<div class=col-12><div class=card><ul>';
            echo '<li><a href="javascript:void(0)" onclick="Ed.chooseRootPurpose()">Choose listing section</a></li>';
            echo '<li>Map Address/Location</li>';
            echo '<li>Where to publish your ad?</li>';
            echo '</ul></div></div>';
            
            echo '<div class=col-12><div class=card>';
            ?><ul>
                <li>Phone number 1</li>
                <li>Phone number 2</li>
                <li>Email</li>
            </ul><?php
            echo '</div></div>';
            
            echo '<div class=col-12><div class=card>';
            //echo '<div class="card-content">';
            ?><ul>
                <li><a href=# class="btn blue">Save</a></li>
                <li><a href=# class="btn blue">Publish</a></li>
                <li><a href=# class="btn">Cancel</a></li>
            </ul><?php
            //echo '</div>';
            echo '</div></div>';
            
            $q='select cn.id, cn.NAME_AR, cn.NAME_EN,lower(trim(cn.id_2)), c.id, c.NAME_AR, c.NAME_EN, c.uri,
                    cn.id||\'-\'||c.id
                    from country cn
                    left join city c on c.country_id=cn.id                    
                    where cn.blocked=0
                    and c.blocked=0                    
                    order by cn.NAME_'.$this->router()->language.', c.name_'.$this->router()->language;
                
            $countries = $this->router()->database()->queryCacheResultSimpleArray(
                    'mobile_countries_'.$this->router()->language, $q, NULL, 8, $this->router()->config()->get('ttl_long'));

            
            if (!$this->isMobile && $this->user->info['level']==9 && ((isset($this->ad) && $this->ad) || (isset($this->adContent['ip']) || isset($this->adContent['userLOC']) || isset($this->adContent['agent'])) )) {
                
                ?><ul tabindex="0" class="ls po info"><?php   
                ?><li class="h"><b><?= $this->lang['m_h_user'] ?></b></li><?php
                if(isset($this->ad) && $this->ad) {
                $name=$this->ad['WEB_USER_ID'].'#'.($this->ad['FULL_NAME']?$this->ad['FULL_NAME']:$this->ad['DISPLAY_NAME']);
                if ($this->ad['LVL']==4) $name='<span style="color:orange">'.$name.'</span>';
                elseif ($this->ad['LVL']==5) $name='<span style="color:red">'.$name.'</span>';
                else $name='<span>'.$name.'</span>';
                if(isset($this->adContent['mobile'])){
                ?><li class="en"><b>is Mobile? <?= $this->adContent['mobile'] ? 'yes':'no' ?></b></li><?php
                }
                ?><li class="en"><a target="blank" href="<?= $this->ad['PROFILE_URL'] ?>"><?= $this->ad['PROVIDER'].' Profile' ?></a></li><?php
                ?><li class="en"><b>Email: <?= $this->ad['EMAIL'] ?></b></li><?php
                ?><li class="en"><a target="blank" href="/myads/?u=<?= $this->ad['WEB_USER_ID'] ?>">User: <?= $name ?></a></li><?php
                ?><li class="en"><b>Mourjan Username: <?= $this->ad['USER_NAME'] ?></b></li><?php
                ?><li class="en"><b>Mourjan Email: <?= $this->ad['USER_EMAIL'] ?></b></li><?php
                }
                if (isset($this->adContent['ip']) || isset($this->adContent['userLOC']) || isset($this->adContent['agent'])){
                    if(isset($this->adContent['ip'])) {
                        ?><li class="en"><b class="ah">IP: <?= $this->adContent['ip'] ?></b></li><?php
                    }
                    if(isset($this->adContent['userLOC'])) {
                        ?><li class="en"><b class="ah">GEO: <?= $this->adContent['userLOC'] ?></b></li><?php
                    }
                    if(isset($this->adContent['agent'])) {
                        ?><li class="en"><b class="ah">User Agent: <?= $this->adContent['agent'] ?></b></li><?php
                    }

                }
                
                $tmp='';
                $tmpCn='';
                $cid=0;
                $lastCity=0;
                $cityCount=0;
                $country_index = 2;
                $city_index = 6;
                if ($this->urlRouter->siteLanguage=='ar') {
                    $country_index = 1;
                    $city_index = 5;
                }
                $open=false;
                $checkedCity=false;
                $cityList='';
                $tmpList='';
                $countryName='';
                foreach ($countries as $key=>$country) {
                    if (isset($country[0])) {//fix for italy 110
                    if ($cid != (int)$country[0]) {
                        if ($cityCount>1 && $tmp) {
                            $tmpCn='';
                            $cityList.=$tmpList;
                        }elseif($cid) {
                            $tmpCn='';  
                            if($checkedCity) {
                                $cityList.=' - '.$countryName;
                            }
                        }
                        $tmp='';
                        $tmpList='';
                        $cityCount=0;
                        $open=true;
                        $cid=(int)$country[0];
                        $countryName=$country[$country_index];
                        $tmpCn='<b><span class="cf c'.$cid.'"></span>'. $country[$country_index];
                    }
                    $lastCity=$country[4];
                    $checkedCity = $hasLocs && in_array($country[4], $this->adContent['pubTo']);
                    if($checkedCity) $tmpList.=' - '.$country[$city_index];
                    $tmp.='<li val="'.$country[4].'"'.($checkedCity ? ' class="on"':'').'><b>'.$country[$city_index].'<span class="cbx"></span></b></li>';

                    $cityCount++;
                    }
                }
                if($tmpCn){
                    if ($cityCount>1 && $tmp) {
                        $cityList.=$tmpList;
                    }else {
                        if($checkedCity) $cityList.=' - '.$countryName;
                    }
                }
                ?><li class="en"><b>Selected Cities:</b></li><?php
                ?><li class="<?= $this->urlRouter->siteLanguage ?>"><b class="ah"><?= ($cityList ? substr($cityList, 3) : '') ?></b></li><?php
                
                ?></ul><?php
            }
            
            ?><ul tabindex="0" id="rou" class="ls rct po<?= $this->rootId ? ' pi' : '' ?>"><?php
                ?><li class="h"><b><?= $this->lang['m_h_root'] ?><span class="et"></span></b></li><?php
                foreach ($this->router()->roots as $root) {
                    ?><li val="<?= $root[0] ?>"<?= $this->rootId ? ($this->rootId==$root[0] ? '' : ' class="hid"') : '' ?>><b><span class="ic r<?= $root[0] ?>"></span><?= $root[$this->fieldNameIndex] ?></b></li><?php                    
                }
            ?></ul><?php
            if(!$this->rootId) $seqHide=true;
            ?><ul id="puu" class="ls po<?= !$seqHide ? ($this->purposeId ? ($this->rootId==4 ? ' hid':' pi') : '') : ' hid' ?>"><?php
                ?><li class="h"><b><?= $this->lang['m_h_purpose'] ?><span class="et"></span></b></li><?php
                 ?><li val="1" ro="1"<?= $this->rootId ? ($this->rootId==1 ? ($this->purposeId==1 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m1_1'] ?></b></li><?php 
                 ?><li val="2" ro="1"<?= $this->rootId ? ($this->rootId==1 ? ($this->purposeId==2 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m1_2'] ?></b></li><?php 
                 ?><li val="8" ro="1"<?= $this->rootId ? ($this->rootId==1 ? ($this->purposeId==8 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m1_8'] ?></b></li><?php 
                 ?><li val="7" ro="1"<?= $this->rootId ? ($this->rootId==1 ? ($this->purposeId==7 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m1_7'] ?></b></li><?php 
                 ?><li val="6" ro="1"<?= $this->rootId ? ($this->rootId==1 ? ($this->purposeId==6 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m1_6'] ?></b></li><?php 
                 ?><li val="1" ro="2"<?= $this->rootId ? ($this->rootId==2 ? ($this->purposeId==1 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m2_1'] ?></b></li><?php 
                 ?><li val="2" ro="2"<?= $this->rootId ? ($this->rootId==2 ? ($this->purposeId==2 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m2_2'] ?></b></li><?php 
                 ?><li val="8" ro="2"<?= $this->rootId ? ($this->rootId==2 ? ($this->purposeId==8 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m2_8'] ?></b></li><?php 
                 ?><li val="7" ro="2"<?= $this->rootId ? ($this->rootId==2 ? ($this->purposeId==7 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m2_7'] ?></b></li><?php 
                 ?><li val="6" ro="2"<?= $this->rootId ? ($this->rootId==2 ? ($this->purposeId==6 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m2_6'] ?></b></li><?php 
                 ?><li val="1" ro="99"<?= $this->rootId ? ($this->rootId==99 ? ($this->purposeId==1 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m99_1'] ?></b></li><?php 
                 ?><li val="2" ro="99"<?= $this->rootId ? ($this->rootId==99 ? ($this->purposeId==2 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m99_2'] ?></b></li><?php 
                 ?><li val="8" ro="99"<?= $this->rootId ? ($this->rootId==99 ? ($this->purposeId==8 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m99_8'] ?></b></li><?php 
                 ?><li val="7" ro="99"<?= $this->rootId ? ($this->rootId==99 ? ($this->purposeId==7 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m99_7'] ?></b></li><?php 
                 ?><li val="6" ro="99"<?= $this->rootId ? ($this->rootId==99 ? ($this->purposeId==6 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m99_6'] ?></b></li><?php 
                 ?><li val="3" ro="3"<?= $this->rootId ? ($this->rootId==3 ? ($this->purposeId==3 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m3_3'] ?></b></li><?php 
                 ?><li val="4" ro="3"<?= $this->rootId ? ($this->rootId==3 ? ($this->purposeId==4 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m3_4'] ?></b></li><?php 
                 ?><li val="5" ro="3"<?= $this->rootId ? ($this->rootId==3 ? ($this->purposeId==5 ? '':' class="hid"') : ' class="hid"') : '' ?>><b><?= $this->lang['m3_5'] ?></b></li><?php 
            ?></ul><?php
            if(!$this->purposeId) $seqHide=true;
            ?><ul id="seu" class="ls po<?= !$seqHide ? ($this->sectionId ? ' pi':'') : ' hid' ?>"><?php
                ?><li class="h"><b><?= $this->rootId ? $this->lang['m_h_s'.$this->rootId] : $this->lang['loading'] ?><span class="et"></span></b></li><?php
                if ($this->rootId) {
                    $sections=$this->urlRouter->db->queryCacheResultSimpleArray(
                    "req_sections_{$this->urlRouter->siteLanguage}_{$this->rootId}",
                    "select s.ID,s.name_".$this->urlRouter->siteLanguage."
                    from section s
                    left join category c on c.id=s.category_id
                    where c.root_id={$this->rootId} 
                    order by s.NAME_{$this->urlRouter->siteLanguage}", null, 0, $this->urlRouter->cfg['ttl_long']);
                    $cssPre='';
                    switch($this->rootId){
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
                    if ($this->sectionId){                        
                        foreach ($sections as $section){
                            ?><li val="<?= $section[0] ?>"<?= $this->sectionId==$section[0] ? '':' class="hid"' ?>><b><span class="<?= $cssPre.$section[0] ?>"></span><?= $section[1] ?></b></li><?php
                        }
                    }else {
                        foreach ($sections as $section){
                            ?><li val="<?= $section[0] ?>"><b><span class="<?= $cssPre.$section[0] ?>"></span><?= $section[$this->fieldNameIndex] ?></b></li><?php
                        }
                    }
                    
                }else {
                    ?><li><b class="load"></b></li><?php 
                }
            ?></ul><?php
            if(!$this->sectionId) $seqHide=true;
            //ad location 
            ?><ul id="cnu" class="ls po<?= ($hasLocs ? ' pi':'').( ($this->rootId && (in_array($this->rootId, array(1,2,99))) && (!$this->sectionId || ($this->sectionId && !in_array($this->sectionId,array(748,766,223,924)) )) ) ? ' uno' : '' ).(!$seqHide ? '':' hid') ?>"><?php
                ?><li class="h"><b><?= $this->lang['m_h_city'] ?><span class="et"></span></b></li><?php 
                
                $tmp='';
                $tmpCn='';
                $cid=0;
                $lastCity=0;
                $cityCount=0;
                $country_index = 2;
                $city_index = 6;
                if ($this->router()->isArabic()) {
                    $country_index = 1;
                    $city_index = 5;
                }
                $open=false;
                $checkedCity=false;
                $cityList='';
                $tmpList='';
                $countryName='';
                
                foreach ($countries as $key=>$country) {
                    if (isset($country[0]) && $country[0]!=110) {
                    if ($cid != (int)$country[0]) {
                        if ($cityCount>1 && $tmp) {
                            echo '<li'.($hasLocs?' class="hid"':'').'>'.$tmpCn,'</b>';
                            $tmpCn='';
                            echo '<ul class="sls">',$tmp,'</ul></li>';
                            $cityList.=$tmpList;
                        }elseif($cid) {
                            echo '<li'.($hasLocs?' class="hid'.($checkedCity ? ' on':'').'"':($checkedCity ? ' class="on"':'')).' val="'.$lastCity.'">'.$tmpCn,'<span class="cbx"></span></b></li>';
                            $tmpCn='';  
                            if($checkedCity) {
                                $cityList.=' - '.$countryName;
                                $this->globalScript.='pc['.$lastCity.']="'.$countryName.'";';
                            }
                        }
                        if($open)echo '</li>';
                        $tmp='';
                        $tmpList='';
                        $cityCount=0;
                        $open=true;
                        $cid=(int)$country[0];
                        $countryName=$country[$country_index];
                        $tmpCn='<b><span class="cf c'.$cid.'"></span>'. $country[$country_index];
                    }
                    $lastCity=$country[4];
                    $checkedCity = $hasLocs && in_array($country[4], $this->adContent['pubTo']);
                    if($checkedCity) $tmpList.=' - '.$country[$city_index];
                    $tmp.='<li val="'.$country[4].'"'.($checkedCity ? ' class="on"':'').'><b>'.$country[$city_index].'<span class="cbx"></span></b></li>';

                    $cityCount++;
                    }
                }
                if($tmpCn){
                    if ($cityCount>1 && $tmp) {
                        echo '<li'.($hasLocs?' class="hid"':'').'>',$tmpCn,'</b>';
                        echo '<ul class="sls">',$tmp,'</ul>';
                        $cityList.=$tmpList;
                    }else {
                        if($checkedCity) $cityList.=' - '.$countryName;
                        echo '<li'.($hasLocs?' class="hid'.($checkedCity ? ' on':'').'"':($checkedCity ? ' class="on"':'')).' val="'.$lastCity.'">'.$tmpCn,'<span class="cbx"></span></b></li>';
                    }
                }
                ?><li<?= ($hasLocs?'':' class="hid"') ?>><b class="ah"><?= ($cityList ? substr($cityList, 3) : '') ?></b></li><?php
                ?><li class="hid"><b class="ah ctr"><span onclick="cnT(this.parentNode.parentNode.parentNode)" class="button bt btw ok"><?= $this->lang['next'] ?></span></b></li><?php                
            ?></ul><?php 
            if(!$hasLocs) $seqHide=true;
             ?><ul id="ccu" class="ls po<?= ($hasContact ? ' pi':'').(!$seqHide ? '':' hid') ?>"><?php
                ?><li onclick="wpz(this)" class="button h"><b><?= $this->lang['m_h_contact'] ?><span class="et"></span></b></li><?php 
                ?><li class="nobd<?= $hasContact ? ' hid':'' ?>"><ul><?php
                    ?><li val="1" onclick="pz(this,1)" class="button"><b><span class="pz pz1"></span><?= $this->lang['contact_h_1'] ?></b></li><?php
                    ?><li val="3" onclick="pz(this,1)" class="button"><b><span class="pz pz3"></span><span class="pz pz1"></span><?= $this->lang['contact_h_3'] ?></b></li><?php
                    ?><li val="5" onclick="pz(this,1)" class="button"><b><span class="pz pz3"></span><?= $this->lang['contact_h_5'] ?></b></li><?php
                    ?><li val="7" onclick="pz(this,1)" class="button"><b><span class="pz pz5"></span><?= $this->lang['contact_h_7'] ?></b></li><?php
                    ?><li val="10" onclick="pz(this)" class="button"><b><span class="pz pz7"></span><?= $this->lang['contact_h_10'] ?></b></li><?php
                    ?><li class="hid"><b class="ah ctr"><span onclick="rpz(this)" class="button bt btw cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                ?></ul></li><?php
                ?><li class="hid nobd"><ul><?php
                    ?><li id="CCodeLi" onclick="pzc(this)" class="button"></li><?php
                    ?><li><div class="ipt"><input type="text" class="pn" /></div></li><?php
                    ?><li><b class="ah ctr act2"><span onclick="savC(this)" class="button bt ok"><?= $this->lang['add'] ?></span><span onclick="rpz(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                ?></ul></li><?php
                ?><li class="<?= $hasContact ? '':' hid' ?>"><ul id="phL"><?php 
                    if($hasContact){
                        $i=0;
                        $s='';
                        foreach($this->adContent['cui']['p'] as $m){
                            switch($m['t']){
                                case 1:
                                    $s='<span class="pz pz1"></span>';
                                    break;
                                case 2:
                                    $s='<span class="pz pz2"></span><span class="pz pz1"></span>';
                                    break;
                                case 3:
                                    $s='<span class="pz pz3"></span><span class="pz pz1"></span>';
                                    break;
                                case 4:
                                    $s='<span class="pz pz3"></span><span class="pz pz2"></span><span class="pz pz1"></span>';
                                    break;
                                case 5:
                                    $s='<span class="pz pz3"></span>';
                                    break;
                                case 7:
                                    $s='<span class="pz pz5"></span>';
                                    break;
                                case 8:
                                case 9:
                                    $s='<span class="pz pz6"></span>';
                                    break;
                                default:
                                    break;
                            }
                            echo '<li val="'.$i.'" onclick="wpz(this)" class="button pn"><b>'.$s.'<span title="'.$this->lang['removeContact'].'" onclick="delC('.$i.',this,event)" class="button pz pzd"></span>'.$m['v'].'</b></li>';
                            $i++;
                        }
                        if ($this->adContent['cui']['b']){
                            $s='<span class="pz pz4"></span>';
                            echo '<li val="b" onclick="wpz(this)" class="button pn"><b>'.$s.'<span title="'.$this->lang['removeContact'].'" onclick="delC(\'b\',this,event)" class="button pz pzd"></span>'.$this->adContent['cui']['b'].'</b></li>';
                        }
                        if ($this->adContent['cui']['t']){
                            $s='<span class="pz pz9"></span>';
                            echo '<li val="t" onclick="wpz(this)" class="button pn"><b>'.$s.'<span title="'.$this->lang['removeContact'].'" onclick="delC(\'t\',this,event)" class="button pz pzd"></span>'.$this->adContent['cui']['t'].'</b></li>';
                        }
                        if ($this->adContent['cui']['s']){
                            $s='<span class="pz pz8"></span>';
                            echo '<li val="s" onclick="wpz(this)" class="button pn"><b>'.$s.'<span title="'.$this->lang['removeContact'].'" onclick="delC(\'s\',this,event)" class="button pz pzd"></span>'.$this->adContent['cui']['s'].'</b></li>';
                        }
                        if ($this->adContent['cui']['e']){
                            $s='<span class="pz pz7"></span>';
                            echo '<li val="e" onclick="wpz(this)" class="button pn"><b>'.$s.'<span title="'.$this->lang['removeContact'].'" onclick="delC(\'e\',this,event)" class="button pz pzd"></span>'.$this->adContent['cui']['e'].'</b></li>';
                        }
                    }
                    ?><li class="button pid" onclick="rpz(this,1)"><b class="lnk"><span class="pz pza"></span><?= $this->lang['contact_h_p'] ?></b></li><?php 
                    ?><li class="pid"><b class="ah ctr"><span onclick="npz(this)" class="button bt btw ok"><?= $this->lang['next'] ?></span></b></li><?php
                ?></ul></li><?php
            ?></ul><?php 
            if(!$hasContact) $seqHide=true;
            ?><ul id="cct" class="ls po uno<?= ($hasContactTime ? ' pi':''),(!$seqHide ? '':' hid'),($hasContact && !count($this->adContent['cui']['p']) ? ' off':'') ?>"><?php
                ?><li onclick="stm(this)" class="button h"><b><?= $this->lang['m_h_time'] ?><span class="et"></span></b></li><?php 
                $time_t=$this->adContent['cut']['t']>=0 ? $this->adContent['cut']['t'] : 0;
                $cutS=$this->lang['time_'.$time_t];
                if ($this->adContent['cut']['t']>0){
                    if($this->adContent['cut']['t']==1 || $this->adContent['cut']['t']==3){
                        $i=$this->adContent['cut']['t']==1 ? $this->adContent['cut']['b']:$this->adContent['cut']['a'];
                        $cutS.=' '.($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM']))));
                    }else {
                        $i=$this->adContent['cut']['a'];
                        $cutS.=' '.($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM']))));
                        $cutS.=' '.($this->urlRouter->siteLanguage=='ar'?'و':'and').' ';
                        $i=$this->adContent['cut']['b'];
                        $cutS.=($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM']))));
                    }
                }
                $this->globalScript.='cutS="'.$cutS.'";';
                ?><li onclick="stm(this)" class="button"><b><?= $cutS ?><span class="et"></span></b></li><?php 
                ?><li class="hid nobd"><ul class="hvr"><?php 
                    ?><li onclick="ttm(1,this)" class="button"><b><?= $this->lang['anytime'] ?><span class="to"></span></b></li><?php 
                    ?><li onclick="ttm(2,this)" class="button"><b><?= $this->lang['before'] ?><span class="to"></span></b></li><?php 
                    ?><li onclick="ttm(3,this)" class="button"><b><?= $this->lang['between'] ?><span class="to"></span></b></li><?php 
                    ?><li onclick="ttm(4,this)" class="button"><b><?= $this->lang['after'] ?><span class="to"></span></b></li><?php 
                    ?><li><b class="ah ctr"><span onclick="ttm(0,this)" class="button bt btw cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                ?></ul></li><?php
                ?><li class="hid nobd"><ul class="hvr"><?php 
                if ($this->router()->isArabic()) {
                    for($i=6;$i<=24;$i++){
                        echo '<li onclick="ctm(this)" val="',$i,'" class="button"><b>',($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM'])))),'<span class="to"></span></b></li>';
                    }
                }else {
                    for($i=6;$i<=24;$i++){
                        echo '<li onclick="ctm(this)" val="',$i,'" class="button"><b>',($i>12?($i-12).' '.$this->lang['PM']:$i.' '.$this->lang['AM']),'<span class="to"></span></b></li>';
                    }
                }
                ?><li><b class="ah ctr"><span onclick="ttm(0,this)" class="button bt btw cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                ?></ul></li><?php
                ?><li class="pid"><b class="ah ctr"><span onclick="ntm(this)" class="button bt btw ok"><?= $this->lang['next'] ?></span></b></li><?php
            ?></ul><?php
            if(!$hasContact || ( ($hasContact && count($this->adContent['cui']['p'])) && !$hasContactTime ) ) $seqHide=true;
            $charsLeft=$otherLength;
            ?><ul id="ctx" class="ls po<?= ($hasContent ? ' pi':''),(!$seqHide ? '':' hid') ?>"><?php
                ?><li class="lib nobd<?= ($hasContent ? ' hid':'') ?>"><b class="ah"><?= $this->lang['ad_text_append'] ?></b></li><?php
                ?><li onclick="etxt(this)" class="button h"><b><?= $this->lang['m_h_text'] ?><span class="et"></span></b></li><?php
                ?><li class="lig<?= ($hasContent ? ' hid':'') ?>"><b><?= $this->lang['maximum'] ?> <span><?= $charsLeft.' / '.$maximumChars ?></span> <?= $this->lang['characters']  ?></b></li><?php
                ?><li class="lit ctr<?= ($hasContent ? ' hid':'') ?>"><textarea id="mText" class="<?= ($adRTL ? 'ar':'en') ?>" onchange="rdrT()" onfocus="initT(this)"><?= $other ?></textarea></li><?php
                ?><li class="pid<?= ($hasContent ? ' hid':'') ?>"><b class="ah ctr"><span onclick="nxt(this,0)" class="button bt btw ok<?= $hasContent ? '': ' off' ?>"><?= $this->lang['next'] ?></span></b></li><?php
                ?><li class="button liw hid" onclick="hidNB(this)"><b class="bpd ctr"><?= $this->lang['min_content']  ?></b></li><?php
                ?><li onclick="etxt(this)" class="button <?= ($hasContent ? '':'hid') ?>"><b id="mPreview" class="ah <?= ($adRTL ? 'ar':'en') ?>"><?= $preview ?></b></li><?php
            ?></ul><?php
            if(!$hasContent) $seqHide=true;
            ?><ul id="xnu" class="ls po pi<?= (!$seqHide ? '':' hid') ?>"><?php
                ?><li class="lib nobd"><b class="ah"><?= $this->lang['ext_data'] ?></b></li><?php
            ?></ul><?php 
            $charsLeft=$altLength;
            ?><ul id="xct" class="ls po<?= ($hasAltContent || $uAlt==2 ? ' pi':''),(!$seqHide ? '':' hid') ?>"><?php
                ?><li onclick="edOT(this)" class="button h"><b><?= $this->lang['m_h_alt_'.$adRTL] ?><span class="et"></span></b></li><?php 
                ?><li class="nobd<?= $uAlt==1 ? '': ' hid'?>"><ul><?php 
                    ?><li class="lig<?= ($hasAltContent ? ' hid':'') ?>"><b><?= $this->lang['maximum'] ?> <span><?= $charsLeft.' / '.$maximumChars ?></span> <?= $this->lang['characters']  ?></b></li><?php
                    ?><li class="lit ctr<?= ($hasAltContent ? ' hid':'') ?>"><textarea id="altText" class="<?= ($altRTL ? 'ar':'en') ?>" onblur="capk()" onkeyup="capk()" onchange="rdrT()" onfocus="initT(this)"><?= $altOther ?></textarea></li><?php
                    ?><li class="pid <?= ($hasAltContent ? ' hid':'') ?>"><b class="ah ctr act2"><span onclick="nxt(this,1)" class="button bt ok<?= $hasAltContent ? '': ' off' ?>"><?= $this->lang['next'] ?></span><span onclick="xcnl(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span></b></li><?php 
                    ?><li class="button liw hid" onclick="hidNB(this)"><b class="bpd ctr"><?= $this->lang['min_content']  ?></b></li><?php
                    ?><li onclick="edOT(this)" class="button <?= ($hasAltContent ? '':'hid') ?>"><b id="mAltPreview" class="ah <?= ($altRTL ? 'ar':'en') ?>"><?= $altPreview ?></b></li><?php
                ?></ul></li><?php
                ?><li class="<?= $uAlt==0 ? '': ' hid'?>"><b class="ah ctr act2"><span onclick="edOT(this,1)" class="button bt ok"><?= $this->lang['yes'] ?></span><span onclick="noO(this,'t')" class="button bt cl"><?= $this->lang['no'] ?></span></b></li><?php 
                ?><li onclick="edOT(this)" class="button<?= $uAlt==2 ? '': ' hid'?>"><b><?= $this->lang['no']  ?></b></li><?php
            ?></ul><?php 
            if(!$hasAltContent && $uAlt!=2) $seqHide=true;
            
                                                            
            ?><ul id="xpc" class="ls po<?= ($hasPics || $uPics==2 ? ' pi':''),(!$seqHide ? '':' hid') ?>"><?php
                ?><li onclick="edOP(this)" class="button h"><b><?= $this->lang['m_h_pics'] ?><span class="et"></span></b></li><?php 
                ?><li class="nobd"><ul id="pics" class="imgList"><?php 
                    ?><li class="lig pbr"><b><?= $this->lang['maximum'] ?> <span><?= $hasPics.' / 5' ?></span> <?= $this->lang['pictures']  ?></b></li><?php 
                    if($hasPics) {
                        $k=0;
                        foreach($this->adContent['pics'] as $key => $val){
                            $this->globalScript.='imgs['.$k.']="'.$key.'";';
                        ?><?php
                            ?><li onclick="edOP($p(this,2));" class="button"><b class="ah ctr"><span title="<?= $this->lang['removePic'] ?>" onclick="delP('<?= $key ?>',this)" class="button pz pzd"></span><?= $this->user->info['level']==9 && !$this->isMobile ? '<a onclick="spe()" class="button iah" target="blank" href="'.$this->urlRouter->cfg['url_ad_img'].'/repos/l/'.$key.'"><span id="sp'. $k .'" class="sp'. $k .' load spimg"></span></a>':'<span id="sp'. $k .'" class="sp'. $k .' load spimg"></span>' ?></b></li><?php 
                            $k++;
                        }
                    }
                ?></ul></li><?php 
                ?><li class="pid"><b class="ah ctr act2"><?php 
                    ?><form target="upload"<?= $hasPics < 5 ? '':' class="hid"' ?> id="picF" action="<?= $this->router()->config()->get('url_uploader') ?>/upload/" enctype="multipart/form-data" method="post"><?php 
                    ?><span class="button bt ok upload_bt"><input id="upKey" type="hidden" name="UPLOAD_IDENTIFIER" value="<?= $this->user->info['id'] ?>" /><input id="picB" name="pic" type="file" multiple="multiple" /><?= $this->lang['add_images'] ?></span><?php 
                    ?><input name="picS" type="submit" class="hid" /><?php
                    ?></form><span id="noPBT" onclick="noPO(this)" class="button bt<?= $hasPics ? '' : ' cl' ?>"><?= $hasPics ? $this->lang['next'] : $this->lang['no'] ?></span><?php 
                    ?><iframe id="upForm" class="hid" name="upload" src="/web/blank.html"></iframe><?php
                ?></b></li><?php 
                ?><li onclick="edOP(this)" class="button<?= $uPics==2 ? '': ' hid'?>"><b><?=  $this->lang['no']  ?></b></li><?php
            ?></ul><?php
            
            if(!$hasPics && $uPics!=2) $seqHide=true;
                        
            
            $isPi=($hasVideo || $uVideo==2);
            ?><ul id="xvd" class="ls po<?= ($isPi ? ' pi':''),(!$seqHide ? '':' hid') ?>"><?php
                ?><li onclick="edOV(this)" class="button h"><b><?= $this->lang['m_h_video'] ?><span class="et"></span></b></li><?php 
                ?><li class="nobd <?= $uVideo==1 ? '': ' hid'?>"><ul><?php 
                    ?><li onclick="shV(this,0)" class="button pid<?= $hasVideo ? '': ' hid'?>"><b><span class="pz pzy"></span><?= $this->lang['up_video_link'] ?><span class="to"></span></b></li><?php
                    ?><li onclick="shV(this,1)" class="button pid<?= $hasVideo ? '': ' hid'?>" style="display:none!important"><b><span class="pz pza"></span><?= $this->lang['up_video'] ?><span class="to"></span></b></li><?php
                    ?><li class="pid<?= $hasVideo ? ' hid': ''?>"><b class="ah ctr"><span onclick="noVUp(this)" class="button bt btw cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                    ?><li onclick="edOV(this)" class="button pics<?= $hasVideo ? '': ' hid'?>"><?php
                        if($hasVideo){
                            if($isPi) {
                                ?><b class="ctr ah"><span title="<?= $this->lang['removeVideo'] ?>" onclick='delV(this)' class='button pz pzd'></span><img src='<?= $this->adContent['video'][2] ?>' width='250' height='200' /><span class='play'></span></b><?php
                            }else {
                                ?><a class='ctr ah' target='blank' href='<?= $this->adContent['video'][1] ?>&autoplay=1'><span  title="<?= $this->lang['removeVideo'] ?>" onclick='delV(this)' class='button pz pzd'></span><img src='<?= $this->adContent['video'][2] ?>' width='250' height='200' /><span class='play'></span></a><?php
                            }
                        }
                    ?></li><?php
                    ?><li class="nobd pid<?= $hasVideo ? ' hid': ''?>"><ul><?php
                        ?><li><div class="ipt"><input type="text" class="pn" placeholder="https://www.youtube.com/watch?v={VIDEO-ID}" /></div></li><?php 
                        ?><li class="nobd hid"></li><?php                                    
                        ?><li><b class="ah ctr act2"><?php
                            ?><input onclick="linkVd(this)" class="button bt ok" type="button" value="<?= $this->lang['add'] ?>" /><?php 
                            ?><span onclick="cVUp(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span><?php
                        ?></b></li><?php
                    ?></ul></li><?php
                    ?><li id="hi" class="nobd<?= $hasVideo ? '': ' hid'?>"><?php
                        //video goes here
                    ?></li><?php
                    ?><li class="pid<?= $hasVideo ? '': ' hid'?>"><b class="ah ctr"><span onclick="dvid(this)" class="button bt btw ok"><?= $this->lang['next'] ?></span></b></li><?php
                ?></ul></li><?php
                ?><li class="<?= $uVideo==0 ? '': ' hid'?>"><b class="ah ctr act2"><span onclick="edOV(this,1)" class="button bt ok"><?= $this->lang['yes'] ?></span><span onclick="noO(this,'v')" class="button bt cl"><?= $this->lang['no'] ?></span></b></li><?php 
                ?><li onclick="edOV(this)" class="button<?= $uVideo==2 ? '': ' hid'?>"><b><?= $this->lang['no']  ?></b></li><?php
            ?></ul><?php 
            if(!$hasVideo && $uVideo!=2) $seqHide=true;
            
            if(!$this->router()->isApp){
                ?><ul id="xmp" class="ls po<?= ($hasMap || $uMap==2 ? ' pi':''),(!$seqHide ? '':' hid') ?>"><?php 
                    ?><li onclick="edOM(this)" class="button h"><b><?= $this->lang['m_h_map'] ?><span class="et"></span></b></li><?php 
                    ?><li class="nobd <?= $uMap==1 ? '': ' hid'?>"><ul><?php 
                        ?><li onclick="edOM(this,1)" class="button"><b class="ah"><span title="<?= $this->lang['removeLoc'] ?>" onclick="clearLoc()" class="button pz pzd"></span><?= $this->adContent['loc'] ?></b></li><?php
                        ?><li class="pid"><b class="ah ctr"><span onclick="dmp()" class="button bt btw ok"><?= $this->lang['next'] ?></span></b></li><?php
                    ?></ul></li><?php
                    ?><li class="<?= $uMap==0 ? '': ' hid'?>"><b class="ah ctr act2"><span onclick="edOM(this,1)" class="button bt ok"><?= $this->lang['yes'] ?></span><span onclick="noO(this,'m')" class="button bt cl"><?= $this->lang['no'] ?></span></b></li><?php 
                    ?><li onclick="edOM(this)" class="button<?= $uMap==2 ? '': ' hid'?>"><b><?= $this->lang['no']  ?></b></li><?php
                ?></ul><?php 
            }
            if(!$this->router()->isApp && !$hasMap && $uMap!=2) $seqHide=true;
            ?><ul class="ls cls nsh po<?= (!$seqHide ? '':' hid') ?>"><?php 
                ?><li class="pid nobd"><p><?= $this->lang['ad_review'] ?></p></li><?php  
                if($budget && $this->userBalance && $this->user->pending['post']['user'] == $this->user->info['id']){
                    $with = '';
                    if($this->router()->isArabic()){
                        if($budget == 1){
                            $with = 'ذهبية واحدة';
                        }else if($budget == 2){
                            $with = 'ذهبيتين';
                        }else if($budget < 11){
                            $with = $budget.' ذهبيات';
                        }else{
                            $with = $budget.' ذهبية';
                        }
                    }else{
                        if($budget == 1){
                            $with = '1 Gold';
                        }else{
                            $with = $budget.' Golds';
                        }
                    }
                    ?><li class="pid"><b class="ah ctr"><span onclick="savAd(1)" class="button bt btw gold"><?= $this->lang['publish_ad_premium'].' '.$this->lang['with'].$with ?></span><br /><br /></b></li><?php
                }
                ?><li class="pid"><b class="ah ctr"><span onclick="savAd(1<?= ($this->user->pending['post']['user'] == $this->user->info['id']) ? ',true':'' ?>)" class="button bt btw ok"><?= $this->lang['publish_ad_free'] ?></span></b></li><?php
                if($this->userBalance && $this->user->pending['post']['user'] == $this->user->info['id']){
                    //$this->globalScript.='uqss="'.$this->urlRouter->cfg['url_jquery_ui'].'";';
                    ?><li class="pid"><b class="ah ctr"><span onclick="savAdP()" class="button bt btw gold"><?= $this->lang['publish_ad_premium'] ?></span></b></li><?php
                }
                if($this->user->info['level']==9){
                    ?><li class="pid"><b class="ah ctr"><span onclick="savAd(2)" class="button bt btw ok"><?= $this->lang['approve'] ?></span></b></li><?php
                }
                if($this->user->info['level']!=9 || ($this->user->pending['post']['user'] == $this->user->info['id']) ){
                    ?><li class="pid"><b class="ah ctr"><span onclick="savAd(-1,true)" class="button bt btw cl ah"><?= $this->lang['savePending'] ?></span></b></li><?php
                }
            ?></ul><?php 
            if($this->isMobile || $this->router()->isApp > '1.0.4'){                
                ?><div id="loading_dialog" class="dialog loading"><?php
                    ?><div class="dialog-box"></div><?php 
                ?></div><?php
                ?><div id="alert_dialog" class="dialog"><?php
                    ?><div class="dialog-box"></div><?php 
                    ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                ?></div><?php
            }
            if($this->userBalance && ($this->user->info['level']!=9 || ($this->user->pending['post']['user'] == $this->user->info['id']) )){
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
                    ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /><input type="button" value="<?= $this->lang['modify'] ?>" /></div><?php 
                ?></div><?php
                $this->globalScript.='
                        function mCPrem(){
                            Dialog.show("alert_dialog",\'<span class="fail"></span>'.$this->lang['multi_premium_no'].'\',function(){var c=$("#cnu")[0];cnT(c);gto(c);});
                        };
                    ';
            }
            //$this->user->pending['post']['content']=json_encode($this->adContent);
            //$this->user->update();
            
            
            
            }
            else {
                // unverified user
                $q='select c.code,c.id,c.name_'.$this->urlRouter->siteLanguage.',c.locked,trim(id_2)    
                        from country c 
                        where id != 109 
                        order by c.locked desc,c.name_'.$this->urlRouter->siteLanguage;
                    $cc=$this->urlRouter->db->queryCacheResultSimpleArray(
                        'country_codes_req_'.strtolower($this->urlRouter->siteLanguage),
                        $q,
                        null, null, $this->urlRouter->cfg['ttl_long']);
                    //var_dump($cc);
                if($this->isMobile){
                    ?><div class="phwrap"><?php
                }
                ?><br /><?php
                ?><div id="mb_notice"><?php 
                    ?><p class="ph"><?= $this->lang['notice_mobile_required'] ?><br /></p><?php 
                    ?><form onsubmit="dcheck();return false"><?php
                    ?><div class="ctr row"><?php
                    ?><select id="code" dir="ltr"><?php 
                            foreach($cc as $country){
                                $country[2]= preg_replace('/\x{200E}/u','',trim($country[2]));
                                ?><option value="<?= $country[0] ?>"<?= $this->user->params['country']==$country[1]?' selected':'' ?>><?= $country[2] ?> (<?= ($this->urlRouter->siteLanguage=='ar'?'':'+').$country[0].($this->urlRouter->siteLanguage=='ar'?'+':'') ?>)</option><?php
                            }
                        ?></select><?php
                    ?></div><br /><?php
                    ?><div class="ctr row"><?php
                    ?><input type="tel" placeholder="<?= $this->lang['your_mobile'] ?>" id="number" /><?php
                    ?></div><?php
                    ?><div id="error_msg" class="ctr row err"><br /></div><?php
                    ?><div class="ctr row"><?php
                    ?><input type="button" onclick="dcheck(this)" value="<?= $this->lang['continue'] ?>" class="bt" /><?php
                    ?></div><?php
                    ?></form><?php
                ?></div><?php
                ?><div id="mb_check"><?php 
                    ?><p class="ph ctr num corr" id="num_string"></p><?php 
                    ?><div class="ctr row"><?php
                    ?><a href="javascript:ncorrect()" class="lnk"><?= $this->lang['correct'] ?></a><?php
                    ?></div><br /><?php
                    ?><p class="ph ctr single"><?= $this->lang['notice_check_number'] ?><br /></p><?php 
                    ?><p class="ph ctr multi"><?= $this->lang['choose_mobile_validation'] ?><br /></p><?php                     
                    ?><div id="error_smsg" class="ctr row err"><br /></div><?php
                    ?><div class="ctr row single"><?php
                        ?><input type="button" onclick="verify(0)" value="<?= $this->lang['send_code'] ?>" class="bt ok" /><?php
                    ?><br /><?php
                    ?></div><?php
                    ?><div class="ctr row multi"><?php
                        ?><ul><?php
                            ?><li>1. <?= $this->lang['validate_mobile_by_call'] ?></li><li><input type="button" onclick="verify(1)" value="<?= $this->lang['call_me'] ?>" class="bt ok" /></li><?php
                        ?></ul><?php
                        ?><ul><?php
                            ?><li>2. <?= $this->lang['validate_mobile_by_sms'] ?></li><li><input type="button" onclick="verify(0)" value="<?= $this->lang['send_code'] ?>" class="bt ok" /></li><?php
                        ?></ul><?php
                    ?><br /><?php
                    ?></div><?php
                ?></div><?php
                ?><form onsubmit="validate();return false"><?php
                ?><div id="mb_validate"><?php 
                    ?><p class="ph ctr num" id="val_string"><?= isset($this->user->pending['mobile']) ? '+'.$this->user->pending['mobile'] : '' ?></p><?php 
                    ?><p class="ph ctr" id="sms_text"><?= isset($this->user->pending['mobile']) ? (isset($this->user->pending['mobile_call']) ? preg_replace('/{pre}/','<span dir=ltr>+'.$this->user->pending['mobile_call'].'</span>',$this->lang['notice_sent_call']).$this->lang['notice_sent_call_prev'] : $this->lang['notice_sent_sms_prev']).'<br />' :'' ?></p><?php 
                    ?><div class="ctr row"><?php
                    ?><input type="text" placeholder="0000" id="vcode" /><?php
                    ?></div><?php
                    ?><div id="error_vmsg" class="ctr row err"><br /></div><?php
                    ?><div class="ctr row"><?php
                    ?><input type="button" onclick="validate()" value="<?= $this->lang['verify'] ?>" class="bt ok" /><?php
                    ?></div><br /><?php
                    ?><div class="ctr row"><?php
                    ?><a href="javascript:vcancel()" class="lnk"><?= $this->lang['cancel'] ?></a><?php
                    ?></div><?php
                ?></div><?php
                ?></form><?php                
                ?><div id="mb_done"><?php 
                    ?><p class="ph ctr"><span class="done"></span> <?= $this->lang['notice_mobile_validated'] ?><br /></p><?php                     
                    ?><div class="ctr row"><?php
                    ?><input type="button" onclick="cont()" value="<?= $this->lang['continue'] ?>" class="bt ok" /><?php
                    ?></div><?php
                ?></div><?php
                ?><div class="ctr" id="mb_load"><?php 
                    ?><br /><p class="ph ctr"><?= $this->lang['mobile_wait'] ?></p><br /><?php 
                    ?><img src="<?= $this->urlRouter->cfg['url_css'] ?>/i/mobile-loading.gif" height="200" width="158" /><?php
                ?></div><?php
                if($this->isMobile){
                    ?></div><?php
                }
                $this->globalScript.='
                    var curNumber="'.(isset($this->user->pending['mobile'])?$this->user->pending['mobile']:'').'";
                    var cont=function(){
                        document.location="";
                    };
                    var vCall='.(isset($this->user->pending['mobile'])&&isset($this->user->pending['mobile_call'])?1:0).';
                    var verify=function(vc){
                        vCall=vc;
                        $("#mb_check").hide();
                        $("#mb_load").show();
                        sctop();
                        $("#error_smsg").html("<br />");
                        $.ajax({
                            type:"POST",
                            url:"/ajax-mobile/",
                            data:{
                                tel:curNumber,
                                hl:lang,
                                vc:vCall
                            },
                            dataType:"json",
                            success:function(rp){
                                if(rp.RP){
                                    if(typeof rp.DATA.verified !== "undefined"){
                                        doneW();
                                    }else if(typeof rp.DATA.check !== "undefined"){
                                        if(rp.DATA.check){
                                            sentW(0);
                                        }else{
                                            wNum();
                                            $("#mb_load").hide();
                                            $("#mb_notice").show();
                                        }
                                    }else{
                                        if(rp.DATA.number>0){
                                            if(vCall){
                                                /*setTimeout(function(){hangup(curNumber)},10000);*/
                                                sentW(2,rp.DATA.pre);
                                            }else{
                                                sentW(1);
                                            }
                                        }else{
                                            sysErr();
                                        }
                                    }
                                }else{
                                    var m=0;
                                    switch(rp.MSG){
                                        case "403":
                                            m="'.$this->lang['blocked_mobile'].'";
                                            break;
                                        case "402":
                                            m="'.$this->lang['suspended_mobile'].' "+rp.DATA.time;
                                            break;
                                        default:
                                            break;
                                    }
                                    sysErr(m)
                                }
                            },
                            error:function(rp){sysErr()}
                        })
                    };
                    var hangup=function(num){
                        $.ajax({
                            type:"POST",
                            url:"/ajax-mobile/",
                            data:{
                                tel:curNumber,
                                hl:lang,
                                hang:1
                            },
                            dataType:"json",
                            success:function(rp){
                            }
                        });
                    };
                    var doneW=function(){
                        $("#mb_load").hide();
                        $("#mb_check").hide();
                        $("#mb_validate").hide();
                        $("#mb_done").show();
                        sctop();
                    };
                    var sentW=function(nw,pre){
                        $("#val_string").html(curNumber);
                        var m;
                        switch(nw){
                            case 2:
                                m ="'.$this->lang['notice_sent_call'].'".replace("{pre}","<span dir=ltr>+"+pre+"</span>");
                                break;
                            case 1:
                                m ="'.$this->lang['notice_sent_sms'].'";
                                break;
                            default:
                                m = "'.$this->lang['notice_sent_sms_prev'].'";
                                break;
                        }
                        $("#sms_text").html(m+"<br />");
                        $("#mb_load").hide();
                        $("#mb_check").hide();
                        $("#mb_validate").show();
                        sctop();
                    };
                    var smsOnly=['.implode(',', $this->urlRouter->cfg['nexmoOnlyCountries']).'];
                    var dcheck=function(e){
                        var num=$("#number");
                        var v=num.val();
                        v=v.replace(/[^0-9]/g,"");
                        if(v.length>0){
                            if(isNaN(v)){
                                wNum();
                            }else{
                                var cc=parseInt($("#code").val());
                                curNumber="+"+cc+parseInt(v);                                
                                num.css("border-color","#aaa");
                                $("#error_msg").html("<br />");
                                $("#num_string").html(curNumber);
                                $("#mb_notice").hide();
                                var mb=$("#mb_check");
                                if(smsOnly.indexOf(cc)>=0){
                                    $(".single",mb).show();
                                    $(".multi",mb).hide();
                                }else{
                                    $(".single",mb).hide();
                                    $(".multi",mb).show();                                
                                }
                                mb.show();
                                sctop();
                                $("#number").val(v);
                            }
                        }else{
                            wNum();
                        }
                    };
                    var validate=function(e){
                        var num=$("#vcode");
                        var v=num.val();
                        v=v.replace(/[^0-9]/g,"");
                        if(v.length==4){
                            if(isNaN(v)){
                                wCode();
                            }else{
                                num.css("border-color","#aaa");
                                $("#error_vmsg").html("<br />");
                                $("#mb_validate").hide();
                                $("#mb_load").show();
                                sctop();
                                $.ajax({
                                    type:"POST",
                                    url:"/ajax-mobile/",
                                    data:{
                                        tel:curNumber,
                                        code:v,
                                        vc:vCall
                                    },
                                    dataType:"json",
                                    success:function(rp){
                                        if(rp.RP){
                                            if(rp.DATA.verified){
                                                doneW();
                                            }else{
                                                wCode();
                                                $("#mb_load").hide();
                                                $("#mb_validate").show();
                                            }
                                        }else{sysVErr()}
                                    },
                                    error:function(rp){sysVErr()}
                                })
                            }
                        }else{
                            wCode();
                        }
                    };
                    var sctop=function(){
                        $("html,body").animate({scrollTop:"0px"},300);
                    };
                    var vcancel=function(){                        
                        $("#mb_validate").hide();
                        $("#mb_notice").show();
                        sctop();
                        $.ajax({
                            type:"POST",
                            url:"/ajax-mobile/",
                            data:{},
                            dataType:"json"
                        })
                    };
                    var wNum=function(){
                        setErr("number","error_msg","'.$this->lang['invalid_mobile'].'");
                    };
                    var wCode=function(){
                        setErr("vcode","error_vmsg","'.$this->lang['invalid_code'].'");
                    };
                    var sysErr=function(msg){
                        if(typeof msg === "undefined")msg=0;
                        setErr(0,"error_smsg",msg);
                        $("#mb_load").hide();
                        $("#mb_check").show();
                    };
                    var sysVErr=function(){
                        setErr(0,"error_vmsg",0);
                        $("#mb_load").hide();
                        $("#mb_validate").show();
                    };
                    var setErr=function(field,nb,msg){
                        if(field)$("#"+field).css("border-color","red");
                        $("#"+nb).html(msg?msg:\''.$this->lang['sys_error'].'\');
                    };
                    var ncorrect=function(){
                        $("#mb_check").hide();
                        $("#mb_notice").show();
                        sctop();
                    };
                    ';
            }
            
            
            
        
    }
        
    
    function side_pane(){
        //$this->renderSideUserPanel();
        //if (!$this->user->info['id'] || !$this->isUserMobileVerified){
            $this->renderSideSite();
        //}
    }

    
    function main_pane() {
        if ($this->user()->isLoggedIn()) {
            if (!$this->router()->config()->get('enabled_post')) {
                $this->renderDisabledPage();
                return;
            }
            
            ?><div class=row><div class=col-12><div id=main class="rct"><?php
            $this->mainMobile();    
            ?></div></div></div><?php
            $this->inlineJS('post.js');
        }
        else {
            $this->renderLoginPage();
        }
    }

    
    function getCountryUnit(){
        if ($this->countryId){
        if ($this->unit) return $this->unit;
        $this->unit='$ USD';
        $this->currencies = $this->urlRouter->db->queryCacheResultSimpleArray(
                'currencies',
                'select id,name_ar,name_en from currency',
        null, 0, $this->urlRouter->cfg['ttl_long']);
        $unit=$this->currencies[$this->urlRouter->countries[$this->countryId][6]];
        $this->unit=trim($unit[0]);
        if ($this->urlRouter->siteLanguage=='ar' && $unit[1]!='') $this->unit=trim($unit[1]);
        if ($this->unit=='USD') $this->unit='$';
        else $this->unit=' '.$this->unit;
        }
        return $this->unit;
    }

    function viewContact(){
        $this->countryCodes = $this->urlRouter->db->queryCacheResultSimpleArray(
                'country_codes',
                'select lower(trim(id_2)) URI, code
                from country
                order by 1',
        null, 0, $this->urlRouter->cfg['ttl_long']);
        $pc3=$pc2=$pc1=$this->user->pending['post']['code'];
        $ph1='';
        $ph2='';
        $fax='';
        $email='';
        $site='';
        $ct='';
        $ct1='';
        $ct2='24';
        if (isset ($this->adContent['fields']['ph1']) && $this->adContent['fields']['ph1'])$ph1=$this->adContent['fields']['ph1'];
        if (isset ($this->adContent['fields']['ph2']) && $this->adContent['fields']['ph2'])$ph2=$this->adContent['fields']['ph2'];
        if (isset ($this->adContent['fields']['pc1']) && $this->adContent['fields']['pc1'])$pc1=$this->adContent['fields']['pc1'];
        if (isset ($this->adContent['fields']['pc2']) && $this->adContent['fields']['pc2'])$pc2=$this->adContent['fields']['pc2'];
        if (isset ($this->adContent['fields']['pc3']) && $this->adContent['fields']['pc3'])$pc3=$this->adContent['fields']['pc3'];
        if (isset ($this->adContent['fields']['ct']) && $this->adContent['fields']['ct'])$ct=$this->adContent['fields']['ct'];
        if (isset ($this->adContent['fields']['ct1']) && $this->adContent['fields']['ct1'])$ct1=$this->adContent['fields']['ct1'];
        if (isset ($this->adContent['fields']['ct2']) && $this->adContent['fields']['ct2'])$ct2=$this->adContent['fields']['ct2'];
        if (isset ($this->adContent['fields']['fax']) && $this->adContent['fields']['fax'])$fax=$this->adContent['fields']['fax'];
        if (isset ($this->adContent['fields']['email']) && $this->adContent['fields']['email'])$email=$this->adContent['fields']['email'];
        if (isset ($this->adContent['fields']['site']) && $this->adContent['fields']['site'])$site=$this->adContent['fields']['site'];
        
        if ($this->user->pending['post']['user']==$this->user->info['id'] 
                && isset($this->user->info['options']['contact']) && count($this->user->info['options']['contact'])) {
            $contact=$this->user->info['options']['contact'];
            if (!$ph1) $ph1=$contact['ph1'];
            if (!$ph2) $ph2=$contact['ph2'];
            if (!$ct) $ct=$contact['ct'];
            if (!$ct1) $ct1=$contact['ct1'];
            if (!$ct2) $ct2=$contact['ct2'];
            if (!$pc1 || $pc1=='ad|+376') $pc1=$contact['pc1'];
            if (!$pc2 || $pc2=='ad|+376') $pc2=$contact['pc2'];
            if (!$pc3 || $pc3=='ad|+376') $pc3=$contact['pc3'];
            if (!$email) $email=$contact['email'];
            if (!$fax) $fax=$contact['fax'];
        }
        if ( ($pc1 && $pc1!='ad|+376') || ($pc2 && $pc2!='ad|+376') || ($pc3 && $pc3!='ad|+376')){
            $this->pageGlobal.='var usrt=1;';
        }else {
            $this->pageGlobal.='var usrt=0;';
        }
        
        /* ?><div class="dtr"><h4><?= $this->lang['preview'] ?>:</h4><br /><h4 class="ptt"></h4><p id="pv"></p></div><?php */
        /* ?><div class="dtr"><p id="unb" class="rc loading"><?= $this->lang['loading'] ?></p><input onclick="proceed();" class="bt bta gr rc" type="button" value="<?= $this->lang['publish'] ?>" /></div><?php */
        ?><br /><div id="contactForm" class="sum form tp els rct"><label class="aw"><?= $this->lang['adContact'] ?></label></div><?php      
        ?><div class="sum els rc form m_b"><?php
        ?><div class="dtl"><ul><?php
                        ?><li><label><?= $this->lang['phone'] ?> 1:</label><select onchange="setCC(this);gen()" id="pc1" class="rc aw pcd"><?php
                            foreach($this->countryCodes as $country){
                                $code=$country[0].'|+'.$country[1];
                                echo '<option value="'.$code.'"',($pc1==$code?' selected="selected"':''),'>'.strtoupper($country[0]).' +'.$country[1].'</option>';
                            }
                            ?></select><input onchange="gen()" id="ph1" class="rc en ph" <?= $this->user->info['level']==9 ? '':'maxlength="15" '?>minlength="6" type="text" value="<?= $ph1 ?>" placeholder="<?= $this->lang['hint_phone'] ?>" req/></li><?php
                        ?><li><label><?= $this->lang['phone'] ?> 2:</label><select id="pc2" onchange="setCC(this);gen()" class="rc aw pcd"><?php
                            foreach($this->countryCodes as $country){
                                $code=$country[0].'|+'.$country[1];
                                echo '<option value="'.$code.'"',($pc2==$code?' selected="selected"':''),'>'.strtoupper($country[0]).' +'.$country[1].'</option>';
                            }
                            ?></select><input onchange="gen()" id="ph2" <?= $this->user->info['level']==9 ? '':'maxlength="15" '?>minlength="6" class="rc en ph" type="text" value="<?= $ph2 ?>" placeholder="<?= $this->lang['hint_phone'] ?>" /></li><?php
                ?><li><label><?= $this->lang['calltime'] ?>:</label><select id="ct" class="rc" onchange="gen()"><option value=""><?= $this->lang['optional'] ?></option><?php
                        ?><option id="lgCT1" value="lgCT1"<?= $ct=='lgCT1'?' selected="selected"':'' ?>><?= $this->lang['before'] ?></option><?php
                        ?><option id="lgCT2" value="lgCT2"<?= $ct=='lgCT2'?' selected="selected"':'' ?>><?= $this->lang['between'] ?></option><?php
                        ?><option id="lgCT3" value="lgCT3"<?= $ct=='lgCT3'?' selected="selected"':'' ?>><?= $this->lang['after'] ?></option><?php
                    ?></select></li><?php
                ?><li<?= !$ct ? ' class="hid"':'' ?>><label>&nbsp;</label><?php
                    ?><select onchange="gen()" id="ct1" class="rc aw"><?php
                    if ($this->urlRouter->siteLanguage=='ar'){
                        for($i=6;$i<=24;$i++){
                            echo '<option value="',$i,'"',($i==$ct1?' selected="selected"':''),'>',($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM'])))),'</option>';
                        }
                    }else {
                        for($i=6;$i<=24;$i++){
                            echo '<option value="',$i,'"',($i==$ct1?' selected="selected"':''),'>',($i>12?($i-12).' '.$this->lang['PM']:$i.' '.$this->lang['AM']),'</option>';
                        }
                    }
                    ?></select><label class="ctr<?= $ct!='lgCT2' ? ' hid':'' ?>"><?= $this->lang['and'] ?></label><select onchange="gen()" id="ct2" class="rc aw<?= $ct!='lgCT2' ? ' hid':'' ?>"><?php
                    if ($this->urlRouter->siteLanguage=='ar'){
                        for($i=6;$i<=24;$i++){
                            echo '<option value="',$i,'"',($i==$ct2?' selected="selected"':''),'>',($i<12 ? $i.' '.$this->lang['AM'] : ($i==12 ? $i.' '.$this->lang['NOON']:($i<16 ? ($i-12).' '.$this->lang['ANOON']: ($i<18 ? ($i-12).' '.$this->lang['BPM'] : ($i-12).' '.$this->lang['PM'])))),'</option>';
                        }
                    }else {
                        for($i=6;$i<=24;$i++){
                            echo '<option value="',$i,'"',($i==$ct2?' selected="selected"':''),'>',($i>12?($i-12).' '.$this->lang['PM']:$i.' '.$this->lang['AM']),'</option>';
                        }
                    }
                ?></select></li></ul></div><div class="dtr"><ul><?php
                ?><li><label><?= $this->lang['fax'] ?>:</label><select id="pc3" onchange="setCC(this);gen()" class="rc aw pcd"><?php
                            foreach($this->countryCodes as $country){
                                $code=$country[0].'|+'.$country[1];
                                echo '<option value="'.$code.'"',($pc3==$code?' selected="selected"':''),'>'.strtoupper($country[0]).' +'.$country[1].'</option>';
                            }
                            ?></select><input onchange="gen()" id="fax" <?= $this->user->info['level']==9 ? '':'maxlength="15" '?>class="rc en ph" type="text" value="<?= $fax ?>" placeholder="<?= $this->lang['hint_fax'] ?>" /></li><?php
                ?><li class="ntc rc"><p><?= $this->lang['emailNot'] ?></p></li><?php                
                ?><li><label><?= $this->lang['email'] ?>:</label><input onchange="gen()" id="email" class="rc en ltr" type="text" value="<?= $email ?>" placeholder="<?= $this->lang['hint_email'] ?>" req/></li><?php
                /* <li><label><?= $this->lang['site'] ?>:</label><input onchange="gen()" id="site" class="rc" type="text" value="<?= $site ?>" placeholder="<?= $this->lang['hint_site'] ?>" /></li> */
                ?></ul></div></div><input type="hidden" id="edv" value="<?= $this->advanced ?>" /><?php
    }

    function viewPics(){
        $lang='';
        if ($this->urlRouter->siteLanguage!='ar') {
            $lang=$this->urlRouter->siteLanguage.'/';
            $this->lang['back']=  ucfirst($this->lang['back']);
        }
            $hasPics=0;
            $hasVideo=0;
            
            if (isset ($this->adContent['pics'])) $hasPics=count($this->adContent['pics']);
            if (isset ($this->adContent['video'])) $hasVideo=1;
            
            ?><br /><div id="updH"<?= $hasPics||$hasVideo ? ' class="hid"':'' ?>><div class="sum form tp els rct"><label class="aw"><?php
            echo $this->lang['uploadHint']
            ?></label><span onclick="upd(4,0)" class="uploads<?= $hasPics||$hasVideo ? '': ' hid' ?>"><?= $this->lang['myFiles'] ?></span></div><?php
            ?><div class="sum els rc form m_b m_t"><?php
            ?><ul class="upUl"><li class="vid lnk"><span></span><?php 
            ?><ul><li onclick="upd(1)"><?php
            echo $this->lang['upVideo']            
            ?></li><li onclick="upd(3)"><?php
            echo $this->lang['upLinkVideo']
            ?></li></ul><?php
            ?></li><li class="pid lnk" onclick="upd(2)"><span></span><?php
            echo $this->lang['upPhotos']
            ?></li></ul><?php
            ?></div></div><?php
            
            ?><div id="updV" class="hid"><div class="sum form tp els rct"><label class="aw"><?php
            echo $this->lang['uploadHint']
            ?></label><span onclick="upd(0,1)" class="back"><?= $this->lang['back'] ?></span></div><?php
            ?><div class="sum els rc form m_b m_t vid"><?php
            ?><div id="vLoader" class="loader"><span class="vico"></span></div><?php
            ?></div><?php
            ?><iframe class="hid" id="vupload" name="vupload" src="/web/blank.html"></iframe><?php
            ?></div><?php
            
            ?><div id="updL" class="hid"><div class="sum form tp els rct"><label class="aw"><?php
            echo $this->lang['linkHint']
            ?></label><span onclick="upd(0,3)" class="back"><?= $this->lang['back'] ?></span></div><?php
            ?><div class="sum els rc form m_b m_t vid"><?php
            ?><div class="loader"><span class="vico"></span><?php 
            ?></div><?php
            ?></div></div><?php 
            
            ?><div id="updS"<?= $hasVideo || $hasPics ? '' : ' class="hid"' ?>><div class="sum form tp els rct"><label class="aw"><?php
            echo $this->lang['myUploads']
            ?></label><span onclick="upd(0,4)" class="add"><?= $this->lang['addMore'] ?></span></div><?php
            ?><div class="sum els rc form m_b m_t vid phd"><?php
            ?><div class="loader"><?php
            if ($hasVideo){
                ?><div class='sh vtd'><img class='vth' src='<?= $this->adContent['video'][2] ?>' width='130' height='97' /><span class="play" href="<?= $this->adContent['video'][1] ?>&autoplay=1"></span><span onclick='vdel(this)' class='mx'></span></div><?php
            }
            if ($hasPics) {
                foreach ($this->adContent['pics'] as $pic=>$set) {
                    echo '<span id="'.preg_replace('/\..*/', '', $pic).'" class="img"><img class="imgT" href="'.$this->urlRouter->cfg['url_ad_img'].'/repos/l/'.$pic.'" src="'.$this->urlRouter->cfg['url_ad_img'].'/repos/s/'.$pic.'" /><span onclick="idel(\''.$pic.'\',this)"></span><div class="fav'.($this->adContent['pic_def']==$pic?' on':'').'" title="'.$this->lang['set_fav'].'" onclick="ifav(\''.$pic.'\',this)"></div></span>';
                }
            }
            ?></div></div></div><?php
            
            ?><div id="updP" class="hid"><?php
            ?><div class="sum form tp els rct"><div id="iNote" class="note<?= $hasPics < 5 ? ' hid':'' ?>"><?= $this->lang['img_max'] ?><span onclick="upd(0,2)" class="back"></span></div><div<?= $hasPics >= 5 ? ' class="hid"':'' ?>><label><?= $this->lang['label_upload'] ?>:</label><?php
            ?><form target="upload" id="picF" class="upd" action="/ajax-upload/" enctype="multipart/form-data" method="post"><input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->urlRouter->cfg['max_upload'] ?>" /><input id="picB" type="file" name="pic" class="rc" /><input id="picU" class="bt bta rc hid" type="submit" value="<?= $this->lang['upload'] ?>" /></form><iframe class="hid" name="upload" src="/web/blank.html"></iframe></div><span onclick="upd(0)" class="back"><?= $this->lang['back'] ?></span></div><?php
            ?><div id="iPane" class="sum els rc form m_b m_t phd"><?php
            ?><div id="phtN" class="pht<?= $hasPics >= 5 ? ' hid':'' ?>"></div></div></div><?php
            
/* 
 * if(!iw){
                txt=txt.replace(/((http|https)(:\/\/))?([a-zA-Z0-9]+[.]{1}){2}[a-zA-z0-9]+(\/{1}[a-zA-Z0-9]+)*\/?/ig, "");};
                txt=txt.replace(/(<.*?>)/g, "");
                txt=txt.replace(/[<>=?*()#^~`!,:;،؛|{}\/\[\]\t\n\r]/g, " - ");
                if(iw || ie) {txt=txt.replace(/\s/g, "");};
                if(!ie) {txt=txt.replace(/\b[^\s]*@[^\s$]*\b/g, "");};
                txt=$.trim(txt);
                if(!txt.match(/[^\s\.-]/)) {txt=""}
                else {
                    txt=txt.replace(/\s+/g, " ");
                    if(ie)txt=txt.toLowerCase();
                }
 */
            $this->pageGlobal.='var updH,updV,updP,updL,updS,vLoader,hasP='.$hasPics.',hasV='.$hasVideo.',cupd='.($hasVideo||$hasPics?4:0).',uploaded=0;
            var mvLoaded=function(e){
                if (!uploaded){
                    vLoader.html("<span class=\"vico\"></span><p>'.$this->lang['uploadFail'].'</p>");
                    vLoader.removeClass("loaded");
                }
                $(".back",updV).css("display","block");
            };
            var upd=function(idx,ref){
                if(!updH){
                    updH=$("#updH");
                    updV=$("#updV");
                    updL=$("#updL");
                    updP=$("#updP");
                    updS=$("#updS");
                    var f=$("iframe",updV);
                    f[0].onload=function(){mvLoaded(f)}
                }
                switch(idx){
                    case 4:
                        updV.addClass("hid");
                        updH.addClass("hid");
                        updL.addClass("hid");
                        updP.addClass("hid");
                        var c=$("#iPane",updP);
                        var im=$(".img",c);
                        if (im.length){
                            $(".loader",updS).append(im);
                        }
                        updS.removeClass("hid");
                        break;
                    case 3:
                        updV.addClass("hid");
                        updH.addClass("hid");
                        updP.addClass("hid");
                        updS.addClass("hid");
                        $(".loader",updL).html("<span class=\"vico\"></span><form onsubmit=\"updVl(this);return false\"><input onfocus=\"unErr(this)\" type=\"text\" class=\"pn rc lin\" name=\"url\" /><span class=\"ytbi\"></span><input class=\"rc bt bta\" value=\"'.$this->lang['addLink'].'\" type=\"submit\" /></form>");                
                        updL.removeClass("hid");
                        break;
                    case 2:
                        updV.addClass("hid");
                        updH.addClass("hid");
                        updL.addClass("hid");
                        updS.addClass("hid");
                        var c=$(".loader",updS);
                        var im=$(".img",c);
                        if (im.length){
                            var nd=$("#phtN",updP);
                            nd.before(im);
                        }                        
                        updP.removeClass("hid");
                        break;
                    case 1:
                        updP.addClass("hid");
                        updH.addClass("hid");
                        updL.addClass("hid");
                        updS.addClass("hid");
                        updV.removeClass("hid");
                        updVi();
                        break;
                    case 0:
                    default:
                        updP.addClass("hid");
                        updV.addClass("hid");
                        updL.addClass("hid");
                        updS.addClass("hid");
                        if (hasP || hasV) $(".uploads",updH).removeClass("hid");
                        else $(".uploads",updH).addClass("hid");
                        updH.removeClass("hid");
                        break;
                }
            };
            var updVi=function(){
                uploaded=0;
                if(!vLoader)vLoader=$("#vLoader");
                if(!vLoader.hasClass("loaded")){
                    vLoader.html("<span class=\"vico\"></span><p>'.$this->lang['preparingForm'].'</p>");
                    var p=$("p",vLoader);
                    p.addClass("loading");
                    $.ajax({type:"POST",url:"/video-upload/",data:{action:0,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",
                        success:function(rp){
                            p.removeClass("loading");
                            if (rp.RP) {/*
                                $("form",vLoader).attr("action",rp.DATA.form.action);
                                $("input[name=token]",vLoader).attr("value",rp.DATA.form.token);*/
                                vLoader.addClass("loaded");
                                p.html(rp.DATA.form);
                            }else {
                                p.html(rp.MSG);
                            }
                        },
                        error:function(rp){
                            p.removeClass("loading");
                            p.html("'.$this->lang['systemUpload'].'");
                        }
                    });
                }
            };
            var updVo=function(){
                $(".back",updV).css("display","none");
                vLoader.removeClass("loaded");
                setTimeout("vLoader.html(\'<span class=\"vico\"></span><p class=\"loading\">'.$this->lang['uploadingWait'].'</p>\');",100);
            };
            var updVd=function(ok,res,state){
                uploaded=1;
                if (ok) {
                    hasV=1;
                    var vl=$(".loader",updS);
                    $(".vtd",vl).remove();
                    vLoader.html("<span class=\"vico\"></span><p class=\"loading\">'.$this->lang['uploadingPrepare'].'</p>");
                    setTimeout(\'updCK("\'+res+\'");\',5000);
                }else {
                    hasV=0;
                    vLoader.html("<span class=\"vico\"></span><p>"+res+"</p>");
                }
            };
            var updCK=function(res){
                $.ajax({type:"POST",url:"/video-upload-check/",dataType:"json",
                        success:function(rp){                            
                            if (rp.RP) {
                                if(rp.DATA.P) {                                
                                    setTimeout(\'updCK("\'+res+\'");\',5000);
                                }else {                                
                                    var vl=$(".loader",updS);
                                    vl.prepend(res);
                                    $(".play",vl).colorbox({iframe:true,innerWidth:430,innerHeight:250});
                                    upd(4,1);
                                }
                            }else {
                                var p=$("p",vLoader);
                                p.removeClass("loading");
                                p.html(rp.MSG);
                            }
                        }
                    });
            };
            var updVl=function(e){
                e=$(e);
                var loader=e.parent();
                var f=$("input[name=url]",e);
                f.removeClass("err");
                var v=f.attr("value");
                try{
                var re=new RegExp("youtube\.com.*?v=(.*?)(?:$|&)","gi");
                var m=re.exec(v);
                }catch(g){console.log(g)}
                if (m==null || !m[1]){
                    f.addClass("err");
                }else {
                    loader.html("<span class=\"vico\"></span><p>'.$this->lang['linkWait'].'</p>");
                    var p=$("p",loader);
                    p.addClass("loading");
                    $.ajax({type:"POST",url:"/video-link/",data:{id:m[1],lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",
                        success:function(rp){
                            p.removeClass("loading");
                            if (rp.RP) {
                                hasV=1;
                                var vl=$(".loader",updS);
                                $(".vtd",vl).remove();
                                vl.prepend(rp.DATA.video);
                                $(".play",vl).colorbox({iframe:true, innerWidth:430, innerHeight:250});
                                upd(4,3);
                            }else {
                                hasV=0;
                                p.html(rp.MSG);
                            }
                        },
                        error:function(){
                            p.removeClass("loading");
                            p.html("'.$this->lang['linkSystemFail'].'");
                        }
                    });
                }
                return false;
            };
            var unErr=function(e){
                e=$(e);
                e.removeClass("err");
            };
            var vdel=function(e){
                if (confirm("'.$this->lang['ask_vdel'].'")){
                e=$(e).parent();
                e.css("visibility","hidden");
                $.ajax({type:"POST",url:"/video-delete/",dataType:"json",
                    success:function(rp){
                        if (rp.RP) {
                            hasV=0;
                            e.remove();
                            var im=$(".img",updS);
                            if (!im.length){
                                upd(0,4);
                            }
                        }else {
                            e.css("visibility","visible");
                            e.addClass("err");
                        }
                    },
                    error:function(){
                        e.css("visibility","visible");
                        e.addClass("err");
                    }
                });
                }
            };
            ';
            $this->pageGlobal.='var xhr,dnb,aph=false,setCC,gen,fields,fieldsR,params,data={},text="",title="",favP="'.(isset ($this->adContent['pic_def'])?$this->adContent['pic_def']:'').'";
                var fit=function(txt,ie,iw){
                    txt=txt.replace(/(<.*?>)/g, "");
                    txt=$.trim(txt);
                    if(!txt.match(/[^\s\.-]/)) {txt=""}
                    else {';
            if ($this->sectionId!=113){
                $this->pageGlobal.='p1=params["ph1"];
                        p2=params["ph2"];
                        p1V=(p1.attr("value")) ? parseInt(p1.attr("value"),10) : 0;
                        p2V=(p2.attr("value")) ? parseInt(p2.attr("value"),10) : 0;

                        reg = new RegExp(/[\s\-]\d{8,}\b/g);
                        var result;
                        while((result = reg.exec(txt)) !== null) {
                            pp=parseInt(result,10);
                            if(pp>0 && p1V>0 && pp==p1V) {
                                txt=txt.replace(result," ");
                                pp=0;
                            }
                            if(pp>0 && p2V>0 && pp==p2V) {
                                txt=txt.replace(result," ");
                                pp=0;
                            }
                            if(pp>0 && p1V==0) {
                                p1.attr("value",pp);
                                txt=txt.replace(result," ");
                                pp=0;
                            }
                            if(pp>0 && p2V==0) {
                                p2.attr("value",pp);
                                txt=txt.replace(result," ");
                                pp=0;
                            }
                        }';
            }

            $this->pageGlobal.='txt=txt.replace(/\s+/g, " ");
                        txt=txt.replace(/([\u0600-\u06ff]),/g, "$1،");
                        txt=txt.replace(/\s([\?\!,\.;:،؛])/g, "$1");
                        txt=txt.replace(/–/g, "-");
                        txt=txt.replace(/(\s_|_\s)/g, " - ");
                        txt=txt.replace(/\u0640(?=[\u0600-\u06FF])/g,"");
                        txt=txt.replace(/\s\u0648\s(?=[\u0600-\u06FF])/g," و");
                        
                        txt=txt.replace(/([\u0600-\u06ff])([\+\-])/g,"$1 $2");
                        txt=txt.replace(/([\+\-])([\u0600-\u06ff])/g,"$1 $2");

                        if(!ie && !iw)txt=txt.replace(/([\?\!,;:،؛])([^\s\d])/g, 
                        "$1 $2");
                        if(!ie && !iw)txt=txt.replace(/(\D)([\?\!,\.;:،؛])([\d])/g, 
                        "$1$2 $3");
                        if(ie)txt=txt.toLowerCase();
                    }
                    return txt
                };
                var lCT=[];lCT["lgCT1"]="'.$this->lang['before_alt'].'";lCT["lgCT2"]="'.$this->lang['between_alt'].'";lCT["lgCT3"]="'.$this->lang['after_alt'].'";
                    var cSize=function(max){
                        var f=$("#picB")[0];
                        if(f.files && f.files.length == 1){
                            if (f.files[0].size > max){
                                alert("'.$this->lang['errFileSize'].' " + (max/1024/1024) + "MB");
                                return false;
                            }
                        }
                        return true
                    };
                    var pnum=function(txt){
                        return txt.replace(/^(0+)|[^\d]+/g,"");
                    };
                    var ifav=function(i,e){
                        if (i!=favP){
                            var c=$("div",$("#"+favP.replace(/\..*/,"")));
                            c.removeClass("on");
                            $(e).addClass("on");
                            $.ajax({
                                type:"POST",url:"/ajax-ifav/",
                                data:{i:i},
                                dataType:"json",
                                success:function(rp){
                                    if (rp.RP) {
                                        favP=i
                                    }else {
                                        ifavF(c,e);
                                    }
                                 },
                                 error:function(){
                                    ifavF(c,e);
                                 }
                              });
                          }
                      };
                      var ifavF=function(c,e){
                        c.addClass("on");
                        $(e).removeClass("on");
                      };
                      var idel=function(i,e,r){
                        if(confirm("'.$this->lang['ask_idel'].'")){
                            e=$(e.parentNode);
                            e.css("display","none");
                            e.removeClass("err");
                            $.ajax({
                                type:"POST",
                                url:"/ajax-idel/",
                                data:{i:i},
                                dataType:"json",
                                success:function(rp){
                                    if (rp.RP) {
                                        hasP--;
                                        if(cupd==4 && !hasP && !hasV){
                                            upd(0,4)
                                        }
                                        if (rp.DATA.def!=favP){
                                            favP=rp.DATA.def;
                                            $("div",$("#"+favP.replace(/\..*/,""))).addClass("on");
                                        };
                                        e.remove();
                                        checkIC();
                                    }else {
                                        ifail(e);
                                    }
                                 },
                                 error:function(){
                                    ifail(e);
                                 }
                             });
                         }};
                         var ifail=function(e){
                            e.css("display","block");
                            e.addClass("err");
                            var n=$("#iNote");
                            n.html("'.$this->lang['del_fail'].'");
                            $("#picF").parent().addClass("hid");
                            n.removeClass("hid");
                         };
                         var checkIC=function(e){
                            if (!e)var e=$("#iPane");
                            var c=e.children().length;
                            var n=$("#iNote");
                            if(c>5) {
                                n.html("'.$this->lang['img_max'].'");
                                $("#picF").parent().addClass("hid");
                                e.children().last().addClass("hid");
                                n.removeClass("hid");
                            }else {
                                n.addClass("hid");
                                $("#picF").parent().removeClass("hid");
                                e.children().last().removeClass("hid");
                             }
                          };
                          var uploadCallback=function(fn,def){
                            var c=$("#phtN");
                            c.removeClass("loading");
                            var n=$("#iNote");
                            $("#picB").parent()[0].reset();
                            if(fn) {
                                var p=$(c.parent());
                                hasP++;
                                var s=$("<span id=\'"+fn.replace(/\..*/,"")+"\' class=\'img\'></span>");
                                var m=$("<img class=\'imgT\' href=\''.$this->urlRouter->cfg['url_resources'].'/repos/l/"+fn+"\' src=\''.$this->urlRouter->cfg['url_resources'].'/repos/s/"+fn+"\' />");
                                    m.appendTo(s);
                                    m.colorbox({rel:\'imgT\'});
                                    var x=$("<span></span>");
                                    x.click(function(e){
                                        idel(fn,e.target)
                                    });
                                    x.appendTo(s);
                                    var d=$("<div class=\'fav\'></div>");
                                    if (def){
                                        favP=fn;
                                        d.addClass("on");
                                    }
                                    d.click(function(e){
                                        ifav(fn,e.target)
                                    });
                                    d.attr("title","'.$this->lang['set_fav'].'");
                                    d.appendTo(s);
                                    s.prependTo(p);
                                    checkIC(p);
                             }else {
                                n.html("'.$this->lang['upload_fail'].'");
                             }
                           };
                           var collect=function(){
                                if (!params) {
                                    params={};
                                    fields=$("input:not(:submit,:button),select,textarea",$("#wz"));
                                    fields.each(function(i,e){
                                        params[e.id]=$(e);
                                    });
                                }
                           };
                           var checkReq=function(){
                                hasTitle=false;
                                hasDesc=false;
                                var pass=true;
                                fieldsR=$("input[req]:not(:submit,:button),select[req],textarea[req]",$("#wz"));
                                fieldsR.each(function(i,e){
                                    e=$(e);
                                    var v=e.attr("value");
                                    var min=e.attr("minlength");
                                    if (!min)min=0;
                                    if (!v || v.length<min) {
                                        var p=e;
                                        do {
                                            p=p.prev();
                                        }while(p[0].tagName!="LABEL");
                                        p.addClass("req");
                                        pass=false;
                                        if(e[0].id=="other"){
                                            return false
                                        }
                                    }else{
                                        if(e[0].id=="atitle") hasTitle=true;
                                        if(e[0].id=="other"){
                                            hasDesc=true;
                                            if(!hasTitle){
                                                return false
                                            }
                                        }
                                    }
                                });
                                return pass;
                           };
                           var stErr=function(msg,e){
                                var nb=$("#unb");
                                nb.addClass("err");
                                nb.html(msg);
                                if(e){
                                    e=$(e);
                                    if(!dnb){
                                        dnb=$("<div class=\"sum rc m_b form mi mie\"><p>"+msg+"</p></div>");
                                    }else{
                                        dnb.html("<p>"+msg+"</p>")};
                                        e.before(dnb);
                                        $("html,body").animate({scrollTop:e.offset().top-50},500);
                                    }
                                };
                                var proceed=function(state){
                                    if(!cityId || !countryId){
                                        stErr("'.$this->lang['errLocation'].'","#locHeader")
                                    }else{
                                        if(checkReq()){
                                            collectData(1,state);
                                        }else {
                                            if (hasTitle && hasDesc){
                                                stErr("'.$this->lang['nb_required_contact'].'","#contactForm")
                                            }else{
                                                stErr("'.$this->lang['nb_required'].'","#detailForm")
                                            }
                                        }
                                    }
                                };
                                var collectData=function(save,proc){
                                    var d={};
                                    if (!proc) var proc=0;
                                    fields.each(function(i,e){
                                        if(e.id=="apntc"){
                                            d[e.id]=e.checked;
                                        }else if(e.id)d[e.id]=e.value;
                                        if(e.id=="altother") d[e.id]=e.value+getContact(true)
                                    });
                                    data.fields=d;
                                    data.text=text;
                                    data.title=title;
                                    data.pub=proc;
                                    var nb=$("#unb");
                                    if (save){
                                        nb.html("'.$this->lang['nb_saving'].'");
                                        nb.removeClass("err");
                                        nb.addClass("loading");
                                        if (xhr && xhr.readyState!=4) xhr.abort();
                                        xhr = $.ajax({
                                            type:"POST",
                                            url:"/ajax-adsave/",
                                            data:data,
                                            dataType:"json",
                                            success:function(rp){
                                                if (rp.RP) {
                                                    nb.html("'.$this->lang['nb_saved'].'");
                                                    if(rp.DATA.S>0) 
                                                        document.location="/myads/'.$lang.'?sub=pending";
                                                }else {
                                                    nb.addClass("er");
                                                    nb.html("'.$this->lang['nb_saving_fail'].'");
                                                };
                                                nb.removeClass("loading");
                                            },
                                            error:function(){
                                                nb.addClass("er");
                                                nb.html("'.$this->lang['nb_saving_fail'].'");
                                                nb.removeClass("loading");
                                            }
                                        });
                                    }
                                };
                                var getContact=function(alt){
                                    var ps=params,c="",dup="",tmp="";
                                    ps["ph1"].attr("req",1);
                                    ps["email"].attr("req",1);
                                    if (tmp=ps["ph1"].attr("value")) {
                                        tmp=pnum(tmp);
                                        ps["ph1"].attr("value",tmp);
                                        if (tmp) {
                                            dup=tmp;
                                            var cc=ps["ph1"].prev().attr("value").split("|");
                                            c+=" - "+(alt?"'.$this->lang['tel_alt'].'":"'.$this->lang['tel'].'")+": <span class=\'pn\'>"+cc[1]+tmp+"</span>";
                                            aph=true;
                                            ps["email"].removeAttr("req");
                                            ps["email"].prev().removeAttr("req");
                                            ps["email"].prev().removeClass("req");
                                            ps["ph1"].prev().prev().removeClass("req");
                                        }
                                    };
                                    if (tmp=ps["ph2"].attr("value")) {
                                        if (dup!=tmp) {
                                            tmp=pnum(tmp);
                                            ps["ph2"].attr("value",tmp);
                                            if (tmp) {
                                                dup=tmp;
                                                if (!aph) 
                                                    c+=" - "+(alt?"'.$this->lang['tel_alt'].'":"'.$this->lang['tel'].'")+": <span class=\'pn\'>";
                                                else c+=" <span class=\'pn\'>";
                                                var cc=ps["ph2"].prev().attr("value").split("|");
                                                c+=cc[1]+tmp+"</span>";
                                            }
                                        }else {
                                            ps["ph2"].attr("value","");
                                        }
                                    }
                                    if (dup) {
                                        if (tmp=ps["ct"].attr("value")){
                                            var n1=ps["ct1"].attr("value");
                                            n1 = parseDT(n1,alt);
                                            
                                            var n2=ps["ct2"].attr("value");
                                            n2 = parseDT(n2,alt);
                                            if (tmp=="lgCT2") {
                                                ps["ct2"].prev().removeClass("hid");
                                                ps["ct2"].removeClass("hid");
                                                c+=" "+(alt?lCT[tmp]:$("#"+tmp).html())+" "+n1+" "+(alt?"'.$this->lang['and_alt'].'":"'.$this->lang['and'].'")+" "+n2;
                                            }else {
                                                ps["ct2"].prev().addClass("hid");
                                                ps["ct2"].addClass("hid");
                                                c+=" "+(alt?lCT[tmp]:$("#"+tmp).html())+" "+n1;
                                            };
                                            ps["ct1"].removeClass("hid");
                                            ps["ct1"].parent().removeClass("hid");
                                        }else {
                                            ps["ct1"].parent().addClass("hid");
                                            ps["ct1"].addClass("hid");
                                            ps["ct2"].addClass("hid");
                                        }
                                    };
                                    if (tmp=ps["fax"].attr("value")) {
                                        tmp=pnum(tmp);
                                        ps["fax"].attr("value",tmp);
                                        var cc=ps["fax"].prev().attr("value").split("|");
                                        if (tmp) c+=" - "+(alt?"'.$this->lang['fax_alt'].'":"'.$this->lang['fax'].'")+": <span class=\'pn\'>"+cc[1]+tmp+"</span>";
                                    };
                                    if (tmp=ps["email"].attr("value")) {
                                        tmp=fit(tmp,1);
                                        ps["email"].attr("value",tmp);
                                        if (tmp){
                                            c+=" - "+(alt?"'.$this->lang['email_alt'].'":"'.$this->lang['email'].'")+": <span class=\'pn\'>"+tmp+"</span>";
                                            ps["email"].prev().removeClass("req");
                                            ps["ph1"].removeAttr("req");
                                            ps["ph1"].prev().prev().removeClass("req");
                                        }
                                    };
                                    return c;
                                };
                                var parseDT=function(n,alt){
                                    if (n<12){
                                        n+=" "+(alt?"'.$this->lang['AM_alt'].'":"'.$this->lang['AM'].'")
                                    }else if (n==12){
                                        n+=" "+(alt?"'.$this->lang['NOON_alt'].'":"'.$this->lang['NOON'].'")
                                    }else if (n<16){
                                        n=(n-12)+" "+(alt?"'.$this->lang['ANOON_alt'].'":"'.$this->lang['ANOON'].'")
                                    }else if (n<18){
                                        n=(n-12)+" "+(alt?"'.$this->lang['BPM_alt'].'":"'.$this->lang['BPM'].'")
                                    }else{
                                        n=(n-12)+" "+(alt?"'.$this->lang['PM_alt'].'":"'.$this->lang['PM'].'")
                                    }
                                    return n;
                                };
                                var ckMax=function() {
                                    var t5=$("<canvas></canvas>");
                                    if (!Boolean(t5[0].getContext && t5[0].getContext("2d"))) {
                                        var ignore = [8,9,13,33,34,35,36,37,38,39,40,46];
                                        var eventName = "keypress";
                                        $("textarea[maxlength]").live(eventName, function(event) {
                                            var self = $(this),
                                            maxlength = self.attr("maxlength"),
                                            code = $.data(this, "keycode");
                                            if (maxlength && maxlength > 0) {
                                                return ( self.val().length < maxlength || $.inArray(code, ignore) !== -1 );
                                            }
                                        }).live("keydown", function(event) {
                                            $.data(this, "keycode", event.keyCode || event.which);
                                        });
                                    }
                                    delete t5;
                                };';
        /*
         *
            if (tmp=ps["site"].attr("value")) {
                tmp=fit(tmp,0,1);
                ps["site"].attr("value",tmp);
                if (tmp)c+=" - '.$this->lang['visit'].' "+tmp+" '.$this->lang['more_info'].'";
            }
         */

        $this->pageInline.='$("#picB").change(function(){
            var v=$(this).attr("value");
            if (v){var i=v.lastIndexOf("\\\");
            if (i>0) {v=v.substr(i+1);}
                else {
                    i=v.lastIndexOf("/");
                    if (i>0) v=v.substr(i+1)
                };
                $("#picU").removeClass("hid")
            }else {
                $("#picU").addClass("hid")
            };
            });
            $("#picU").click(function(){
                if (cSize('.$this->urlRouter->cfg['max_upload'].')){
                    var f=$("#picF");
                    var n=$("#iNote");
                    n.html("'.$this->lang['upload_wait'].'");
                    f.parent().addClass("hid");
                    n.removeClass("hid");
                    $("#picU").addClass("hid");
                    $("#phtN").addClass("loading");
                    return true;
                }
                return false
             });';
    }

    function viewCategory(){
        ?><br /><div id="pbx"><?php
        /* ?><ul class="tbs"><?php */
        $purposesX=array();
        $purposes=$this->urlRouter->db->queryCacheResultSimpleArray(
                "post_purposes",
                "select p.ID, p.NAME_AR, p.NAME_EN
                from purpose p
                where p.id != 999
                order by p.id",
                null, 0, $this->urlRouter->cfg['ttl_long']);

        $stmt=$this->urlRouter->db->prepareQuery(
            "select purpose_id from root_purpose_xref
                where root_id=? and purpose_id != 999
                order by purpose_id"
        );
        $purposeList='';

        foreach ($this->urlRouter->roots as $root) {
            //echo '<li><a',($this->rootId==$root[0]? ' class="on" id="r'.$root[0].'"':''),' onclick="bro('.$root[0].',this)">',$root[$this->fieldNameIndex],'</a></li>';

            if($stmt->execute(array($root[0]))){
                $purposeList.='<ul id="p'.$root[0].'" class="tbs'.($this->rootId ? ($this->rootId!=$root[0]?' hid':' cls'):'').'">';
                while($row=$stmt->fetch(PDO::FETCH_NUM)){
                    if ( !($root[0]==99 && in_array($row[0], array(3,4,5)) )  )
                        $purposeList.='<li><a'.($this->rootId==$root[0] && $this->purposeId==$row[0]? ' class="on" id="u'.$root[0].$row[0].'"':'').' onclick="bpu('.$root[0].','.$row[0].',this)">'.$this->lang['p'.$root[0].'_'.$row[0]].'</a></li>';
                }
                $purposeList.='<li><span class="lnk'.(!$this->rootId || $this->rootId!=$root[0]?' hid':'').'" onclick="bro(this)">'.$this->lang['changeCat'].'</span></li></ul>';
            }
        }
        /* ?></ul><?php */

        echo $purposeList;

        ?><form id="wz" method="post"><input id="wzBS" type="submit" class="hid" /><input name="sn" type="hidden" /><input name="ro" type="hidden" /><input name="se" type="hidden" /><input name="pu" type="hidden" /><input name="cn" type="hidden" value="<?= $this->countryId ?>" /><input name="c" type="hidden" value="<?= $this->cityId ?>" /></form><?php

        ?></div><?php
        ?><br /><div id="seo" class="sum form tp els rct<?= $this->rootId && !in_array($this->rootId, array(2))?'':' hid' ?>"><form onsubmit="sde();return false;"><label><?= $this->lang['other'] ?>:</label><input id="newse" onkeydown="lookup(1)" class="rc" type="text" name="section" placeholder="<?= $this->lang['hint_new_section'] ?>" /><input class="bt bta rc" type="submit" value="<?= $this->lang['add_section'] ?>" /><span id="seErr"></span></form></div><?php
        ?><div id="secPH"><?php
        foreach ($this->urlRouter->roots as $root) {
            ?><div id="s<?= $root[0] ?>" class="sum els rcb m_b m_t<?= $this->rootId==$root[0]?'':' hid' ?>"><?php
                    $siteLanguage=$this->urlRouter->siteLanguage;
                    $sections=$this->urlRouter->db->queryCacheResultSimpleArray(
                    "req_sections_{$siteLanguage}_{$root[0]}",
                    "select s.ID,s.name_".$siteLanguage."
                    from section s
                    left join category c on c.id=s.category_id
                    where c.root_id={$root[0]} and s.id not in (19,29,63,105,114)
                    order by s.NAME_{$siteLanguage}", null, 0, $this->urlRouter->cfg['ttl_long']);

                    $countPerColumn=ceil(count($sections)/5);
                    echo '<ul>';
                    $i=0;
                    $lastChar='';
                    $firstChar='';
                    foreach ($sections as $section) {
                        $firstChar=mb_substr($section[1], 0, 1, 'UTF8');
                        if ($lastChar!=$firstChar) {
                            if ($i>=$countPerColumn) {
                                echo '</ul><ul>';
                                $i=0;
                            }
                            $lastChar=$firstChar;
                            echo '<li><h5>',$lastChar,'</h5></li>';
                        }
                        echo '<li><a onclick="sd(',$section[0],')">',$section[1],'</a></li>';
                        $i++;
                    }
                    echo '</ul>';
            ?></div><?php
        }
        ?></div><?php

        $this->pageInline.='tbs=$("ul.tbs");
            newTag=$("#newse");
            nadiv=$("<div class=\'nt\'>'.$this->lang['no_match'].'</div>");';
        if ($this->rootId) {
            $this->pageInline.='ro='.$this->rootId.';pu='.$this->purposeId.';epu=$("#u'.$this->rootId.$this->purposeId.'");';
        }
        $this->pageGlobal.='var ro,tbs,ero,pu,epu,ose,secs=[],ops={},aps={},lmr,newTag,nadiv;
            var fit=function(txt){
                txt=txt.replace(/[<>=?*()@#$%^&_+~`!,.:;،؛|{}\/\[\]]/g, "");
                return txt
            };
            var lookup=function(r){
                if (r) {
                    if (lmr) clearTimeout(lmr);
                    lmr=setTimeout("lookup();",200);
                }else {
                    lmr=null;
                    if (!ops[ro]) {
                        aps[ro]=$("#s"+ro+" h5");
                        ops[ro]=$("#s"+ro+" a")
                    }
                    var s=newTag.attr("value");
                    nadiv.remove();
                    ose=null;
                    if (s) {
                        aps[ro].css("display","none");
                        var re = new RegExp(".*"+s,"i");
                        var pass=false;
                        var x=0;
                        ops[ro].each(function(i,e){
                            if (e.innerHTML.match(re)) {
                                pass=true;
                                ose=e;
                                x++;
                                e.parentNode.style.display="block"
                            }else e.parentNode.style.display="none";
                        });
                        if (x!=1){ose=null};
                        if (!pass) nadiv.appendTo($("#s"+ro));
                    }else {
                        aps[ro].css("display","block");
                        ops[ro].each(function(i,e){
                            e.parentNode.style.display="block";
                        });
                    }
                }
            };
            var sde=function(){
                var err=$("#seErr");
                err.css("display","none");
                var v=$.trim($("#newse").attr("value"));
                if (v.length) {
                    var n=fit(v);
                    if (n!=v) {
                        err.html("'.$this->lang['err_chars'].'");
                        err.css("display","inline-block");
                        return false;
                    };
                    if (ose) {
                        var o=$(ose);
                        if (o.html().toLowerCase()==v.toLowerCase()){
                            o.click();
                            return false;
                        }
                    };
                    var tmp=v.split(" ");
                    if (tmp.length>2) {
                        err.html("'.$this->lang['err_section'].'");
                        err.css("display","inline-block");
                        return false;
                    };
                    var f=$("#wz");
                    var c=f.children();
                    c[1].value=v;
                    c[2].value=ro;
                    c[3].value=0;
                    c[4].value=pu;
                    $(c[0]).trigger("click");
                }
            };
            var sd=function(v){
                var f=$("#wz");
                var c=f.children();
                c[1].value="";
                c[2].value=ro;
                c[3].value=v;
                c[4].value=pu;
                $(c[0]).trigger("click");
            };
            var bro=function(e){
                if (ro) {
                    $("html,body").animate({scrollTop:"250px"},500);
                    if(pu){
                        $(epu).removeClass("on")};
                        $("#seo").addClass("hid");
                        $("#s"+ro).addClass("hid");
                        newTag.attr("value","");
                        lookup();
                    };
                    e=$(e);
                    e.addClass("hid");
                    tbs.each(function(c,d){
                        $(d).removeClass("hid cls")
                    });
                };
                var bpu=function(i,p,e){if (ro && i!=ro) {$("html,body").animate({scrollTop:"250px"},500);$("#seo").addClass("hid");$("#s"+ro).addClass("hid");newTag.attr("value","");lookup();}ro=i;if(pu){$(epu).removeClass("on")};$(e).addClass("on");epu=e;pu=p;tbs.each(function(c,d){var p=e.parentNode.parentNode;if (p.id!=d.id){$(d).addClass("hid")}else{$(p).addClass("cls");$(p).children().last().children().removeClass("hid")}});if ($("#s"+i).hasClass("hid")){$("#s"+i).removeClass("hid");if(ro!=2){$("#seo").removeClass("hid")}}};';
    }

    function viewLocation(){
        ?><div class="sum els rc sh m_b"><?php
        ?><div class="form tp rct"><form onsubmit="mapSrch();return false;" method="post"><label><?= $this->lang['local'] ?>:</label><input id="mapQ" class="rc" type="text" placeholder="<?= $this->lang['hint_location'] ?>" /><input class="bt rc" type="submit" value="<?= $this->lang['lookup'] ?>" /><input class="bt bta rc" onclick="myloc(true)" type="button" value="<?= $this->lang['myloc'] ?>" /></form><?php
        ?><form action="/post/<?= $this->urlRouter->siteLanguage=='ar' ? '':'en/' ?>" method="post"><input type="hidden" name="ro" value="<?= $this->rootId ?>" /><input type="hidden" name="se" value="<?= $this->sectionId ?>" /><input type="hidden" name="pu" value="<?= $this->purposeId ?>" /><input class="bt rc ccl" type="submit" value="<?= $this->lang['cancel'] ?>" /></form></div><?php
        ?><div class="map" id="map"></div><?php
        ?><form id="locF" action="/post/<?= $this->urlRouter->siteLanguage=='ar' ? '':'en/' ?>" method="post"><input type="submit" id="locBS" class="hid" /><input type="hidden" name="ro" value="<?= $this->rootId ?>" /><input type="hidden" name="se" value="<?= $this->sectionId ?>" /><input type="hidden" name="pu" value="<?= $this->purposeId ?>" /><input type="hidden" id="loc_cn" name="cn" value="0" /><input id="loc_c" type="hidden" name="c" value="0" /></form></div><?php
        /* ?><div class="sum edi rc na m_b"><?= $this->lang['note_country'] ?></div><?php */

        $this->pageInline.='cF=$("#loc_c");cnF=$("#loc_cn");mapQ=$("#mapQ");mapQ.keydown(function(){$(this).css("color","#333")});var script = document.createElement("script");script.type = "text/javascript";script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initializeMap&language='.$this->urlRouter->siteLanguage.'";document.body.appendChild(script);';

        $this->pageGlobal.='
            var map,marker,geocoder,infoWindow,mapQ,cnF,cF,mxr;
            var mapClick=function(e){
                geocoder.geocode({"latLng": e.latLng},
                    function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                loc=results;
                                marker.setPosition(e.latLng);
                                setInfo(results);cacheLoc(results);
                            }
                        } else {
                            mapQ.css("color","#ff0000");
                        }
                    });
            };

            var cacheLoc=function(loc){
                var obj=[];
                len=loc.length;
                var k=0;
                for (var i=len-1;i>=0;i--) {
                    obj[k]={latitude:loc[i].geometry.location.lat(),longitude:loc[i].geometry.location.lng(),type:loc[i].types[0],name:loc[i].address_components[0].long_name,short:loc[i].address_components[0].short_name,formatted:loc[i].formatted_address};
                    k++;
                };
                if (mxr && mxr.readyState!=4)
                    mxr.abort();
                mxr=$.ajax({type:"POST",url:"/ajax-location/",data:{loc:obj,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",success:function(rp){if (rp.RP && rp.DATA.loc.c && rp.DATA.loc.cn) {cF.attr("value",rp.DATA.loc.c);cnF.attr("value",rp.DATA.loc.cn);infowindow.setContent(infowindow.content+"<br /><input type=\'button\' class=\'bt rc\' onclick=\'doSelect()\' value=\''.$this->lang['place_ad'].'\' />");}}});
            };
            var setZoom=function(type){
                var cz=map.getZoom();
                switch(type){
                    case "route":
                        if(cz<15)map.setZoom(15);
                        break;
                    case "country":
                        if(cz<7)map.setZoom(7);
                        break;
                    case "sublocality":
                        if(cz<14)map.setZoom(14);
                        break;
                    default:
                        if(cz<13)map.setZoom(13);
                        break;
                }
            };
            var mapSrch=function(){
                var val=mapQ.attr("value");
                if (val) {
                    geocoder.geocode( { "address": val}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                infowindow.close();
                                setZoom(results[0].types[0]);
                                map.setCenter(results[0].geometry.location);
                            } else {
                                mapQ.css("color","#ff0000");
                            }
                        });
                }
                return false;
            };
            var setInfo=function(results){var idx=0;if (results.length>1 && results[0].types[0]=="route" && results[0]["address_components"][1]["short_name"]!=results[1]["address_components"][0]["short_name"]){idx++}var adc=results[idx].address_components;var len=adc.length;if (len==1 && adc[len-1]["short_name"]!="IL") {infowindow.setContent("'.$this->lang['be_specific'].'"+"<b>"+adc[0].long_name+"</b>");infowindow.open(map, marker);return true;};var tmp="",res="";for (var i=len-1;i>=0;i--) {if (tmp!=adc[i].long_name && adc[i].types[0]!="locality"){if(res) {res=", "+res;};res=adc[i].long_name+res;tmp=adc[i].long_name;}};if (adc[len-1]["short_name"]!="IL")setZoom(results[idx].types[0]);infowindow.setContent(res);infowindow.open(map, marker);return true;};function doSelect(){if (cF.attr("value") && cF.attr("value")!="") {$("#locBS").trigger("click");}};
                
function myloc(force){
    var pos;
    if (!force){
        '.(isset($this->user->pending['post']['lon']) ? 'pos = new google.maps.LatLng('.$this->user->pending['post']['lat'].','.$this->user->pending['post']['lon'].');
            map.setCenter(pos);
            mapClick({latLng:pos});
            return true;' :'').'};
           if(navigator.geolocation) {
           navigator.geolocation.getCurrentPosition(function(position) {pos = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);map.setCenter(pos);mapClick({latLng:pos});});}else{'.(false && isset($this->user->params['longitude'])?'pos = new google.maps.LatLng('.$this->user->params['latitude'].','.$this->user->params['longitude'].');map.setCenter(pos);mapClick({latLng:pos});':'').'};return true;};
                
            function initializeMap() {geocoder = new google.maps.Geocoder();infowindow = new google.maps.InfoWindow();var myOptions = {zoom: 13,mapTypeId: google.maps.MapTypeId.HYBRID};map = new google.maps.Map(document.getElementById("map"), myOptions);google.maps.event.addDomListener(map, "click", mapClick);
                marker = new google.maps.Marker({map: map,animation: google.maps.Animation.DROP});
                defView();myloc();
            };
            var defView=function(){
                var df=new google.maps.LatLng('.(isset($_GET['lat'])?$_GET['lat']:'33.8852793').','.(isset($_GET['lon'])?$_GET['lon']:'35.5055758').');
                map.setZoom(4);
                map.setCenter(df);
            };';
    }

    function viewDetail(){
        if ($this->user->info['level']==9) {
            ?><div class="sum m_b rc mi form"><form action="/post/<?= $this->urlRouter->siteLanguage=='ar' ? 'en/':'' ?>" method="post"><input class="bt lk aw" type="submit" value="<?= $this->lang['switchLang'] ?>" /><?php
            ?><input name="ad" value="<?= $this->user->pending['post']['id'] ?>" type="hidden" /><?php
            ?></form><br /><?php
            ?><input class="bt lk aw" onclick="tlc()" type="button" value="to Lower Case" /><?php
            ?></div><?php
            $this->pageGlobal.='var tlc=function(){$.each(params,function(i,e){var v=e.attr("value");if(v){e.attr("value",v.toLowerCase())}})};';
        }
        //country selection
        ?><br /><div id="locHeader" class="sum form tp els rct"><label class="aw"><?= $this->lang['adListing'] ?></label></div><?php
        ?><div class="sum els rc form m_t"><?php        
        $hasCities=count($this->countryCities)>1;
        ?><br /><div class="locs hid"><select onchange="lddc(this)" class="rc"><option value=""><?= $this->lang['chooseCountry'] ?></option><?php
        /* ?><option value="-1"><?= $this->lang['allCountries'] ?></option><?php */
            foreach($this->urlRouter->countries as $key=>$val){
                if (isset($val[0])){
                //if ($this->countryId!=$key || ($hasCities && $this->countryId==$key))
                    echo '<option value="'.$val[0].'"'.($this->countryId==$key?' selected="selected"':'').'>'.$val[$this->fieldNameIndex].'</option>';
                }
            }
            ?></select><select class="rc"<?= $hasCities?'':' disabled="disabled"' ?>><option value=""><?= $this->lang['chooseCity'] ?></option><?php
            $this->pageGlobal.='var cnc={'.$this->countryId.':{}};';
            if ($hasCities) {
                $cityList='';
                foreach($this->countryCities as $key=>$val){
                    //if ($this->user->pending['post']['dc']!=$key) {
                        $this->pageGlobal.='cnc['.$this->countryId.']['.$key.']="'.$this->urlRouter->cities[$key][$this->fieldNameIndex].'";';
                        echo '<option value="'.$val[0].'">'.$this->urlRouter->cities[$key][$this->fieldNameIndex].'</option>';
                    //}
                }
            }
            ?></select><input type="button" onclick="bddc(this)" class="bt rc" value="<?= $this->lang['add'] ?>" /><input type="button" class="bt rc" onclick="cddc(this)" value="<?= $this->lang['cancel'] ?>" /></div><?php
        ?><span onclick="addc(this)" class="lnk"><?= $this->lang['addLocation'] ?></span><?php
        $hasLocs=(isset ($this->adContent['pubTo']) && count($this->adContent['pubTo']));
        ?><h4<?= $hasLocs ? '':' class="hid"' ?>><?= $this->lang['otherHeader'] ?> <span onclick="rddc(-1,this)" class="lnk"><?= $this->lang['clearAll'] ?></span><br /><br /></h4><ul class="lcd"><?php
        if ($hasLocs) {
            foreach ($this->adContent['pubTo'] as $key=>$val){
                echo '<li><span onclick="rddc('.$key.',this)" class="mx"></span>'.$this->urlRouter->countries[$this->urlRouter->cities[$key][6]][$this->fieldNameIndex].' - '.$this->urlRouter->cities[$key][$this->fieldNameIndex].'</span></li>';
            }
        }
        ?></ul><?php 
        $hasMap=(isset($this->user->pending['post']['lat']) && $this->user->pending['post']['lat']>0
                || isset($this->user->pending['post']['lon']) && $this->user->pending['post']['lon']>0);
        ?><div class="mapr<?= $hasMap ? ' on':'' ?>"><form method="post"><input type="hidden" name="map" value="1" /><input type="hidden" name="ro" value="<?= $this->rootId ?>" /><input type="hidden" name="se" value="<?= $this->sectionId ?>" /><input type="hidden" name="pu" value="<?= $this->purposeId ?>" /><?php
        if ($hasMap) {
            echo '<a class="hid" onclick="this.parentNode.submit()">'.$this->lang['mapLocation'].'</a><p><span class="loc"></span>'.$this->user->pending['post']['loc'].' - '.$this->user->pending['post']['tloc'].' - '.$this->user->pending['post']['dcni'].'<span onclick="rddc(0,this)" class="lnk">'.$this->lang['remove'].'</span><a onclick="this.parentNode.parentNode.submit()">'.$this->lang['change_location'].'</a></p>';
        }else {
            echo '<a onclick="this.parentNode.submit()">'.$this->lang['mapLocation'].'</a>';
        }
        ?></form></div><?php
        ?></div><?php
        $this->viewPics();
        $this->generalView();
        /*switch ($this->rootId){
            case 3:
                if($this->purposeId==5) $this->generalView();
                elseif ($this->advanced)$this->jobsView();
                else $this->generalView(true);
                break;
            case 2:
                if ($this->advanced) $this->carsView();
                else $this->generalView(true);
                break;
            case 1:
                if ($this->sectionId==106) {
                    $this->presetTitle=true;
                    $this->generalView();
                }else if ($this->advanced) $this->realestateView();
                else $this->generalView(true);
                break;
            case 4:
            case 99:
                $this->generalView();
                break;
        }*/
        $this->viewContact();
        echo '</form>';
        //ad preview
        ?><br /><div class="sum form tp els rct"><label class="aw"><?= $this->lang['previewLabel'] ?></label></div><?php
        ?><div class="sum els rc form m_b m_t phd"><div id="pv"></div><?php
        ?><p id="unb" class="rc loading"><?= $this->lang['loading'] ?></p><input onclick="proceed(1);" class="bt bta gr rc" type="button" value="<?= $this->lang['publish'] ?>" /><?php
        if ($this->user->info['level']==9) {?><br /><br /><input onclick="proceed(2);" class="bt bta rc" type="button" value="Publish and Approve" /><?php }
        ?></div><?php
        
        if($this->user->info['level']==9){            
            ?><div class='sum tp els rc'><?php
            if(isset($this->ad)) {
                $name=$this->ad['WEB_USER_ID'].'#'.($this->ad['FULL_NAME']?$this->ad['FULL_NAME']:$this->ad['DISPLAY_NAME']);
                if ($this->ad['LVL']==4) $name='<span style="color:orange">'.$name.'</span>';
                elseif ($this->ad['LVL']==5) $name='<span style="color:red">'.$name.'</span>';
                else $name='<span>'.$name.'</span>';
                ?><p class="en"><label><b>Provider:</b></label> <a target="blank" href="<?= $this->ad['PROFILE_URL'] ?>"><?= $this->ad['PROVIDER'].' Profile' ?></a></p><?php
                ?><p class="en"><label><b>Email:</b></label> <?= $this->ad['EMAIL'] ?></p><?php
                ?><p class="en"><label><b>User:</b></label> <a target="blank" href="/myads/?u='<?= $this->ad['WEB_USER_ID'] ?>"><?= $name ?></a></p><?php
                ?><p class="en"><label><b>Mourjan Username:</b></label> <?= $this->ad['USER_NAME'] ?></p><?php
                ?><p class="en"><label><b>Mourjan Email:</b></label> <?= $this->ad['USER_EMAIL'] ?></p><?php
            }
            if (isset($this->adContent['ip']) || isset($this->adContent['userLOC']) || isset($this->adContent['agent'])){
                if(isset($this->adContent['ip'])) {
                    ?><p class="en"><label><b>IP:</b></label> <?= $this->adContent['ip'] ?></p><?php
                }
                if(isset($this->adContent['userLOC'])) {
                    ?><p class="en"><label><b>GEO:</b></label> <?= $this->adContent['userLOC'] ?></p><?php
                }
                if(isset($this->adContent['agent'])) {
                    ?><p class="en"><label><b>User Agent:</b></label> <?= $this->adContent['agent'] ?></p><?php
                }
            }
                ?></div><?php
        }
        
        ?><div class="sum rc m_b form mi"><form onsubmit="log(this);return false;" method="post"><input type="hidden" name="cn" value="<?= $this->countryId ?>" /><input type="hidden" name="c" value="<?= $this->cityId ?>" /><input type="hidden" name="ro" value="<?= $this->rootId ?>" /><input type="hidden" name="se" value="<?= $this->sectionId ?>" /><input type="hidden" name="pu" value="<?= $this->purposeId ?>" /><input type="hidden" name="id" value="<?= $this->id ?>" /><input type="hidden" name="data" /><p><?= $this->lang['err_submit'] ?> <input type="button" class="rc bt aw lk" onclick="supF(this);" value="<?= $this->lang['clickHere'] ?>"/></p><div class="sup hid"><br /><h4><?= $this->lang['supportHeader'] ?></h4><textarea class="ptxt"></textarea><input type="submit" class="bt rc aw" value="<?= $this->lang['send'] ?>" /><input type="button" onclick="supC(this)" class="bt rc aw" value="<?= $this->lang['cancel'] ?>" /></div></form></div><?php
        $this->pageGlobal.='var supB;var supC=function(e){e=$(e.parentNode);e.addClass("hid");supB.style.display="inline-block";};var supF=function(e){supB=e;e.style.display="none";var f=$("div.sup", e.parentNode.parentNode);f.removeClass("hid");};var log=function(e){e=$(e);var d={};e.children().each(function(i,e){if(e.type=="hidden") d[e.name]=e.value;});e.css("visibility","hidden");e.parent().addClass("loading");if (window["data"]) {d["detail"]=data;};d["msg"]=$("textarea",e).attr("value");$.ajax({type:"POST",url:"/ajax-support/",data:{obj:d,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",success:function(rp){e.parent().removeClass("loading");if (rp) {e.replaceWith("<p>"+rp.MSG+"</p>")}},error:function(){e.parent().removeClass("loading");e.css("visibility","visible");}});return false;};';
        

        $this->pageGlobal.='var multi='.($hasLocs?1:0).';
            var cncX,cnL={},countryId='.$this->countryId.',cityId='.$this->cityId.';
            var addc=function(e){
                e=$(e);
                e.addClass("hid");
                e.prev().removeClass("hid");
            };
            var cddc=function(e){
                e=$(e.parentNode);
                e.addClass("hid");
                e.next().removeClass("hid");
            };
            var rddc=function(i,e){
                e=$(e);
                e.addClass("loading");
                $.ajax({type:"POST",url:"/ajax-cc-remove/",data:{i:i,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",
                success:function(rp){
                    if (rp.RP){
                        if (i==0){
                            var p=e.parent();
                            p.parent().parent().removeClass("on");
                            p.prev().removeClass("hid");
                            p.remove();
                            var opt=$("#optLoc");
                            if(opt){opt.remove()}
                            hasMap=0;
                        }else{
                            if (i<0){e.parent().next().empty();e.parent().addClass("hid");e.removeClass("loading")}else{
                            var ul=e.parent().parent();
                            if (ul.children().length<2) {
                            ul.prev().addClass("hid");multi=0}
                            e.parent().remove();
                            }
                        }
                        var def=rp.DATA.D;
                        if(def!=undefined && def[0]!=undefined){
                            countryId=def[0];
                            cityId=def[1];
                            if(def[2]!=undefined)sloc=def[2];
                            if(!usrt && def[3])$(".pcd").attr("value",def[3]);
                            if(def[4]){unit=def[4];$(".pch").html(unit)}
                            gen();
                        }
                    }else{e.removeClass("loading")}
                },
                error:function(){
                   e.removeClass("loading");
                }});
            };
            var bddc=function(e){
                e=$(e);
                var c=e.prev().prev();
                var v=c.attr("value");
                var vc=e.prev().attr("value");
                if(v && vc){
                    $.ajax({type:"POST",url:"/ajax-cc-add/",data:{i:v,c:vc,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",
                        success:function(rp){
                            if (rp.RP){
                                var u=e.parent().next().next().next();
                                u.prev().removeClass("hid");
                                multi=1;
                                u.empty();
                                var d=rp.DATA.L;
                                var x;
                                for (var i in d){
                                    x=$("<li><span onclick=\"rddc("+i+",this)\" class=\"mx\"></span>"+d[i]+"</li>");
                                    u.append(x);
                                }
                                cddc(e.next()[0]);
                                var def=rp.DATA.D;
                                if(def!=undefined && def[0]!=undefined){
                                    countryId=def[0];
                                    cityId=def[1];
                                    if(def[2]!=undefined)sloc=def[2];
                                    if(!usrt && def[3])$(".pcd").attr("value",def[3]);
                                    if(def[4]){unit=def[4];$(".pch").html(unit)}
                                    gen();
                                }
                                if(dnb && dnb.parent()){
                                    dnb.remove()
                                }
                            }
                        },
                        error:function(){
                        }});
                }
            };
            var lddc=function(e){
                e=$(e);
                var n=e.next();
                n.attr("value","-2");
                n.attr("disabled",true);
                var v=e.attr("value");
                if (v>0){
                if (cnc[v]){
                    oddb(cnc[v],e.next());
                }else {
                    if (cncX && cncX.readyState!=4) cncX.abort();
                    cncX=$.ajax({type:"POST",url:"/ajax-country-cities/",data:{i:v,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",
                        success:function(rp){
                            var d=rp.DATA.C;
                            cnc[v]=d;
                            oddb(d,e.next());
                        },
                        error:function(){
                        }});
                }}
            };
            var oddb=function(d,e){
                e.children().each(function(i,c){if(i)$(c).remove()});
                for(var i in d){var o=$("<option value=\""+i+"\">"+d[i]+"</option>");e.append(o)};
                if (e.children().length<2) {
                    var c=$(e.children()[0]);
                    c.attr("value",-1);
                    c.html("'.$this->lang['allCities'].'");
                    e.attr("value","-1");
                    e.attr("disabled",true);
                }else {
                    var c=$(e.children()[0]);
                    c.attr("value","");
                    c.html("'.$this->lang['chooseCity'].'");
                    e.attr("value","");
                    e.attr("disabled",false);
                }
            };
            ';
    }

    function optLocation(){
        $sloc=isset($this->user->pending['post']['zloc']) && $this->user->pending['post']['zloc']?$this->user->pending['post']['zloc']:'';
        $aoc=$this->lang['in'];
        $Ar_b='ب';
        if (isset($this->adContent['fields']['aoc'])){
            $aoc=$this->adContent['fields']['aoc'];
            if ($aoc==="0")$aoc=0;
        }
        if ($this->user->pending['post']['dcn'] && ($this->user->pending['post']['loc'] || $this->user->pending['post']['gloc'] || $this->user->pending['post']['dcni'])) {
            if (isset ($this->adContent['fields']['sloc']) && $this->adContent['fields']['sloc']){
                $sloc=$this->adContent['fields']['sloc'];
            }
            if ($sloc!=$this->user->pending['post']['loc'] &&
                    $sloc!=$this->user->pending['post']['gloc'] &&
                    $sloc!=$this->user->pending['post']['tloc'] &&
                    $sloc!=$this->user->pending['post']['dcni']) $sloc=$this->user->pending['post']['loc'];
            ?><li><label class="vt"><?= $this->lang['listIn'] ?>:</label><?php
                ?><ul class="radio"><?php
                ?><li><input type="radio" onchange="rgen(this)" name="oLoc" value="<?= $this->user->pending['post']['loc'] ?>" <?= ($sloc==$this->user->pending['post']['loc']?' checked':'') ?>/><?= $this->user->pending['post']['loc'] ?></li><?php
                ?><li><input type="radio" onchange="rgen(this)" name="oLoc" value="<?= $this->user->pending['post']['gloc'] ?>" <?= ($sloc==$this->user->pending['post']['gloc']?' checked':'') ?>/><?= $this->user->pending['post']['gloc'] ?></li><?php
                if ($this->user->pending['post']['tloc']!=$this->user->pending['post']['gloc']){ 
                    ?><li><input type="radio" onchange="rgen(this)" name="oLoc" value="<?= $this->user->pending['post']['tloc'] ?>" <?= ($sloc==$this->user->pending['post']['tloc']?' checked':'') ?>/><?= $this->user->pending['post']['tloc'] ?></li><?php 
                }
                ?><li><input type="radio" onchange="rgen(this)" name="oLoc" value="<?= $this->user->pending['post']['dcni'] ?>" <?= ($sloc==$this->user->pending['post']['dcni']?' checked':'') ?>/><?= $this->user->pending['post']['dcni']
                ?><input type="hidden" id="sloc" value="<?= $sloc ?>" /></li><?php
                ?></ul></li><?php 
        }
        if ($this->user->info['level']==9) {
        ?><li><label class="vt"><?= $this->lang['listAppend'] ?>:</label><?php
            ?><ul class="radio"><?php
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value=" " <?= (($aoc===-1 || $aoc==' ')?' checked':'') ?> /><?= '" "' ?></li><?php
            if ($this->urlRouter->siteLanguage=='ar') {
                ?><li><input type="radio" onchange="apc(this)" name="aoc" value="<?= $Ar_b ?>" <?= ($aoc==$Ar_b?' checked':'') ?>/><?= $Ar_b ?></li><?php
            }
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value="<?= $this->lang['in'] ?>" <?= ($aoc==$this->lang['in']?' checked':'') ?>/><?= $this->lang['in'] ?></li><?php
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value="<?= $this->lang['on'] ?>" <?= ($aoc==$this->lang['on']?' checked':'') ?>/><?= $this->lang['on'] ?></li><?php
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value="<?= $this->lang['from'] ?>" <?= ($aoc==$this->lang['from']?' checked':'') ?>/><?= $this->lang['from'] ?></li><?php
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value="<?= $this->lang['to'] ?>" <?= ($aoc==$this->lang['to']?' checked':'') ?>/><?= $this->lang['to'] ?></li><?php
            ?><li><input type="radio" onchange="apc(this)" name="aoc" value="0" <?= ($aoc===0?' checked':'') ?> /><?= $this->lang['noAppend']
            ?><input type="hidden" id="aoc" value="<?= $aoc ?>" /></li><?php
            ?></ul></li><?php
            $this->pageGlobal.='var aoc='.($aoc?'"'.$aoc.'"':"0").';var apc=function(e){aoc=e.value=="0"?0:e.value;$("#aoc",e.parentNode.parentNode).attr("value",aoc);gen()};';
        }else {
            $this->pageGlobal.='var aoc="'.$aoc.'";';
        }
        $this->pageGlobal.='var sloc="'.$sloc.'";var rgen=function(e){sloc=e.value;$("#sloc",e.parentNode.parentNode).attr("value",sloc);gen();};';
        
    }

    function generalView($hasAdvanced=false){
        if ($hasAdvanced) {
            $this->hintForm(1);
        }
        $other='';
        $altOther='';
        $aTitle='';
        $altTitle='';
        $pTitle='...';
        /*if ($this->presetTitle) {
            $this->sectionNameSingle=$this->sectionName;
                    $section=$this->urlRouter->db->queryResultArray(
                        "select single,plural
                        from naming
                        where type_id=? and origin_id=? and lang=? and (country_id=0 or country_id=?) order by country_id desc",array(2,$this->sectionId,$this->urlRouter->siteLanguage,$this->countryId));
            if (!empty($section)) {
                $this->sectionName=$section[0]['PLURAL'];
                $this->sectionNameSingle=$section[0]['SINGLE'];
            }
            $this->purposeName=$this->urlRouter->purposes[$this->purposeId][$this->fieldNameIndex];
            switch($this->purposeId){
                case 1:
                case 2:
                    $pTitle=$this->sectionName.' '.$this->purposeName;
                    break;
                case 6:
                case 7:
                case 8:
                    $pTitle=$this->purposeName.' '.$this->sectionName;
                    break;
            }
        }*/
        $hint='';
        $this->sectionNameSingle=$this->sectionName;
        $section=$this->urlRouter->db->queryResultArray(
            "select single,plural
            from naming
            where type_id=? and origin_id=? and lang=? and (country_id=0 or country_id=?) order by country_id desc",array(2,$this->sectionId,$this->urlRouter->siteLanguage,$this->countryId));
        if (!empty($section)) {
            $this->sectionName=$section[0]['PLURAL'];
            $this->sectionNameSingle=$section[0]['SINGLE'];
        }
        $this->purposeName=$this->urlRouter->purposes[$this->purposeId][$this->fieldNameIndex];
        switch($this->purposeId){
            case 1:
            case 2:
                $hint=$this->sectionNameSingle.' '.$this->purposeName;
                break;
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                $hint=$this->purposeName.' '.$this->sectionNameSingle;
                break;
        }
        if (isset ($this->adContent['fields']['other']) && $this->adContent['fields']['other'])$other=$this->adContent['fields']['other'];
        $apnt=true;
        if (isset ($this->adContent['fields']['apntc']) && $this->adContent['fields']['apntc']=="false")$apnt=false;
        if (isset ($this->adContent['fields']['altother']) && $this->adContent['fields']['altother']){
            $altOther=$this->adContent['fields']['altother'];
            $altOther=  preg_replace('/\s-\s(هاتف|فاكس|البريد الإلكتروني|Tel|Fax|Email).*/', '', $altOther);            
        }
        if (isset ($this->adContent['fields']['atitle']) && $this->adContent['fields']['atitle'])$aTitle=$this->adContent['fields']['atitle'];
        if (isset ($this->adContent['fields']['alttitle']) && $this->adContent['fields']['alttitle'])$altTitle=$this->adContent['fields']['alttitle'];
        
        ?><br /><div id="detailForm" class="sum form tp els rct"><label class="aw"><?= $this->lang['adContent'] ?></label></div><?php
        ?><form id="wz" method="post"><div class="sum els rc form m_b"><?php 
        ?><div class="blc"><div id="optLoc" class="dtl"><ul><?php $this->optLocation(); ?></ul></div><div class="dtr"><ul><li class="ntc rc"><p><?= $this->lang['langOther'] ?></p></li></ul></div></div><?php
        ?><div class="dtl"><ul><?php        
        ?><li><label><?= $this->lang['lgTitle'] ?>:</label><input onchange="gen()" id="atitle" <?= $this->user->info['level']==9 ? '':'maxlength="80" '?>class="rc <?= $this->urlRouter->siteLanguage ?>" type="text" value="<?= $aTitle ?>" placeholder="<?= $hint /*$this->lang['hint_title']*/ ?>" <?= $this->presetTitle?'':'req' ?>/></li><?php
        if ($this->user->info['level']==9) {?><li><input id="apntc" name="apntc" type="checkbox" onchange="apnt=this.checked;gen();" <?= $apnt ? 'checked="checked"':'' ?> style="width:30px;vertical-align:middle" /> Append Title</li><?php }
        ?><li><label class="tp"><?= $this->lang['desc'] ?>:</label><textarea <?= $this->user->info['level']==9 ? '':'maxlength="240" req '?>onchange="gen()" id="other" class="rc <?= $this->urlRouter->siteLanguage ?>"><?= $other ?></textarea></li><?php
        ?></ul></div><div class="dtr <?= $this->urlRouter->siteLanguage=='ar'?'en':'ar' ?>"><ul><?php 
        ?><li><label><?= $this->lang['lgTitle_o'] ?>:</label><input onchange="gen()" id="alttitle" <?= $this->user->info['level']==9 ? '':'maxlength="80" '?>class="rc<?= $this->urlRouter->siteLanguage=='en'?' ar':' en' ?>" type="text" value="<?= $altTitle ?>" /></li><?php
        ?><li><label class="tp"><?= $this->lang['desc_o'] ?>:</label><textarea <?= $this->user->info['level']==9 ? '':'maxlength="240" '?>onchange="gen()" id="altother" class="rc<?= $this->urlRouter->siteLanguage=='en'?' ar':' en' ?>"><?= $altOther ?></textarea></li><?php
        ?></ul></div></div><?php
        $Ar_b='ب';
        $this->pageGlobal.='var apnt='.($apnt?'true':'false').';var hasTitle=false,hasDesc=false,hasMap='.( isset($this->user->pending['post']['dcni']) && $this->user->pending['post']['dcni'] ? 1:0).';';
        $this->pageInline.='ckMax();
            var pst="'.$pTitle.'";
            var pv=$("#pv");
            var pu="'.strtolower($this->purposes[$this->purposeId][$this->fieldNameIndex]).'";
                setCC=function(e){
                    if (!usrt){
                        $(".pcd").attr("value",e.value);
                    }
                    usrt=1;
                };
                gen=function(){
                    collect();
                    var ps=params;
                    var c="";
                    var tmp="";
                    var aTitle="";
                    var aText="";
                    var cText="";
                    var cTitle="";
                    tt="'.$this->user->pending['post']['tloc'].'";
                    var sct="'.$this->sectionCrumb.'";
                    if (ps["atitle"]){
                        tmp=fit(ps["atitle"].attr("value"));
                        ps["atitle"].attr("value",tmp);
                        if (tmp) {
                            title=tmp;
                            ps["atitle"].prev().removeClass("req");
                            cTitle=title
                        }else {
                            title=pst
                        }
                    };
                    if (ps["alttitle"]){
                        tmp=fit(ps["alttitle"].attr("value"));
                        ps["alttitle"].attr("value",tmp);
                        aTitle=tmp;
                    };
                    if(ps["sloc"])ps["sloc"].attr("value",sloc);
                    if(aoc && sloc)title+=(aoc==" "?" ":" "+aoc+(aoc=="'.$Ar_b.'"?"":" "))+sloc+( (hasMap && sloc!="'.$this->user->pending['post']['dcni'].'")?" '.$this->user->pending['post']['dcni'].'":"");
                    if(title!=pst && apnt){
                        c=title
                    }
                    if (tmp=ps["other"].attr("value")) {
                        tmp=fit(tmp);
                        ps["other"].attr("value",tmp);
                        if (tmp) {
                            c+=(c==""?"":" - ")+tmp;
                            ps["other"].prev().removeClass("req");
                        }
                    };
                    if (tmp=ps["altother"].attr("value")) {
                        tmp=fit(tmp);
                        ps["altother"].attr("value",tmp);
                        aText=tmp;
                    };
                    text=c+getContact();  
                    cText=text;                  
                    if (!cTitle)cTitle="...";
                    if (!cText)cText="...";
                    if (aTitle || aText){
                    if (!aTitle)aTitle="...";
                    if (!aText)aText="...";
                    aText+=getContact(true);
                    '.($this->urlRouter->siteLanguage=='ar' ? 
                        'pv.html("<ul><li class=\'ar\'><h4 class=\'ptt\'>"+title+"</h4><p>"+cText+"</p></li><li class=\'en\'><h4 class=\'ptt\'>"+aTitle+"</h4><p>"+aText+"</p></li></ul><p>"+sct+"</p>");'
                            :
                        'pv.html("<ul><li class=\'en\'><h4 class=\'ptt\'>"+title+"</h4><p>"+cText+"</p></li><li class=\'ar\'><h4 class=\'ptt\'>"+aTitle+"</h4><p>"+aText+"</p></li></ul><p>"+sct+"</p>");'
                        ).'
                    }else {
                        pv.html("<h4 class=\'ptt\'>"+title+"</h4><p>"+cText+"</p><p>"+sct+"</p>");
                    }
                    
                    collectData(true)
                };gen();';
    }

    function hintForm($state=0){
        ?><div class="sum m_b rc mi form"><form action="/post/<?= $this->urlRouter->siteLanguage=='ar' ? '':$this->urlRouter->siteLanguage.'/' ?>" method="post"><p><?= $state ? $this->lang['use_advanced']:$this->lang['use_simple'] ?><input type="hidden" name="edv" value="<?= $state ?>" /><input class="bt lk aw" type="submit" value="<?= $this->lang['clickHere'] ?>" /></p><?php
        if (isset ($this->user->pending['post']['id']) && $this->user->pending['post']['id']) {
            ?><input name="ad" value="<?= $this->user->pending['post']['id'] ?>" type="hidden" /><?php
        }else {
            ?><input name="se" type="hidden" value="<?= $this->sectionId ?>" /><input name="ro" type="hidden" value="<?= $this->rootId ?>" /><input name="pu" type="hidden" value="<?= $this->purposeId ?>" /><input name="cn" type="hidden" value="<?= $this->countryId ?>" /><input name="c" type="hidden" value="<?= $this->cityId ?>" /><?php
        }
        ?></form></div><?php
    }

    function jobsView(){

        $this->hintForm(0);

        $other='';
        $company='';
        $job='';
        $jobType='';
        $jobExp='';
        $aTitle='';
        if (isset ($this->adContent['fields']['company']) && $this->adContent['fields']['company'])$company=$this->adContent['fields']['company'];
        if (isset ($this->adContent['fields']['job']) && $this->adContent['fields']['job'])$job=$this->adContent['fields']['job'];
        if (isset ($this->adContent['fields']['jobtype']) && $this->adContent['fields']['jobtype'])$jobType=$this->adContent['fields']['jobtype'];
        if (isset ($this->adContent['fields']['exp']) && $this->adContent['fields']['exp'])$jobExp=$this->adContent['fields']['exp'];
        if (isset ($this->adContent['fields']['other']) && $this->adContent['fields']['other'])$other=$this->adContent['fields']['other'];
        if (isset ($this->adContent['fields']['atitle']) && $this->adContent['fields']['atitle'])$aTitle=$this->adContent['fields']['atitle'];
        echo '<div class="sum els rc form m_b"><form id="wz" method="post"><div class="dtl"><ul>';
        $this->optLocation();
        if ($this->purposeId==3) {
        ?><li><label><?= $this->lang['company'] ?>:</label><input onchange="gen()" id="company" <?= $this->user->info['level']==9 ? '':'maxlength="40" '?>class="rc" type="text" value="<?= $company ?>" placeholder="<?= $this->lang['hint_company'] ?>" /></li><?php
        }
        if ($this->purposeId!=5){
        ?><li><label><?= $this->lang['job'] ?>:</label><input onchange="gen()" id="job" <?= $this->user->info['level']==9 ? '':'maxlength="40" '?>class="rc" type="text" value="<?= $job ?>" placeholder="<?= $this->lang['hint_job'] ?>" req/></li><?php
        ?><li><label><?= $this->lang['exp'] ?>:</label><select onchange="gen()" id="exp" class="rc"><option value=""><?= $this->lang['optional'] ?></option>
            <?php
            $d=0;
            for ($i=0; $i<8;$i++) {
                if ($this->purposeId==5 && ($i<3)) continue;
                if ($this->purposeId==4 && $i==0) continue;
                if ($this->purposeId==4 && $i==2) {
                    $i=8;
                    $d=2;
                }
                echo '<option id="lgExp'.$i.'" value="lgExp'.$i.'"'.($jobExp=='lgExp'.$i?' selected="selected"':'').'>'.$this->lang['lgExp'.$i].'</option>';
                if ($d){
                    $i=2;
                    $d=0;
                }
            }
            ?>
        </select></li><?php
        ?><li><label><?= $this->lang['jobType'] ?>:</label><select onchange="gen()" id="jobtype" class="rc" req><option value=""><?= $this->lang['specify'] ?></option>
            <?php
            for ($i=1; $i<5;$i++) {
                echo '<option id="lgJobT'.$i.'" value="lgJobT'.$i.'"'.($jobType=='lgJobT'.$i?' selected="selected"':'').'>'.$this->lang['lgJobT'.$i].'</option>';
            }
            ?>
        </select></li><?php }else {
            ?><li><label><?= $this->lang['lgTitle'] ?>:</label><input onchange="gen()" id="atitle" <?= $this->user->info['level']==9 ? '':'maxlength="80" '?>class="rc" type="text" value="<?= $aTitle ?>" placeholder="<?= $this->lang['hint_title'] ?>" req/></li><?php
        }
        ?><li><label class="tp"><?= $this->lang['desc'] ?>:</label><textarea <?= $this->user->info['level']==9 ? '':'maxlength="240" '?>onchange="gen()" id="other" class="rc"<?= $this->purposeId==5 ? ' req':'' ?>><?= $other ?></textarea></li><?php
        echo '</ul></div>';
        $this->sectionNameSingle=$this->sectionName;
                $section=$this->urlRouter->db->queryResultArray(
                    "select single,plural
                    from naming
                    where type_id=? and origin_id=? and lang=? and (country_id=0 or country_id=?) order by country_id desc",array(2,$this->sectionId,$this->urlRouter->siteLanguage,$this->countryId));
        if (!empty($section)) {
            $this->sectionName=$section[0]['PLURAL'];
            $this->sectionNameSingle=$section[0]['SINGLE'];
        }
        $this->pageInline.='ckMax();var gx=0;var pv=$("#pv");var unit="'.$this->getCountryUnit().'";var se="'.$this->sectionName.'";var ses="'.$this->sectionNameSingle.'";var pu="'.(in_array($this->purposeId,array(6,7))? $this->lang['looking_prefix'].' ':'').strtolower($this->purposes[$this->purposeId][$this->fieldNameIndex]).'";gen=function(){gx++;collect();var ps=params;var c=ses;var tmp="",tmp2="";var tt=se;title="";if (ps["company"]) {if (tmp=ps["company"].attr("value")) {tmp=fit(tmp);ps["company"].attr("value",tmp);c=tmp;}else {c="'.$this->lang['aCompany'].'";}};if(aoc && sloc)tt=" "+sloc+(sloc!="'.$this->user->pending['post']['dcni'].'"?" '.$this->user->pending['post']['dcni'].'":"");';
                if ($this->purposeId==3){
                    $this->pageInline.='c+=" '.$this->lang['located'].'";c+=tt;c=c+" '.$this->lang['isHiring'].'";if (ps["job"]) {title="'.$this->lang['jobTitlePrefix'].'";if (tmp=ps["job"].attr("value")) {tmp=fit(tmp);ps["job"].attr("value",tmp);if(tmp){c+=" "+tmp;title+=" "+tmp;ps["job"].prev().removeClass("req")}}else {title+=ses;}};if (ps["exp"]) {if (tmp=ps["exp"].attr("value")) {c+=" - "+$("#"+tmp).html();}};if (ps["jobtype"]){if (tmp=ps["jobtype"].attr("value")) {c+=" - "+$("#"+tmp).html();ps["jobtype"].prev().removeClass("req")}};title+="'.$this->lang['jobTitleSuffix'].' '.$this->lang['in'].'";title+=tt;if (tmp=ps["other"].attr("value")) {tmp=fit(tmp);ps["other"].attr("value",tmp);if (tmp) c+=" - "+tmp;};';
                }elseif ($this->purposeId==4){
                    $this->lang['jobTitlePrefix']='';
                    $this->pageInline.='title="'.$this->lang['jobTitlePrefix'].'";if (ps["job"]) {if (tmp=ps["job"].attr("value")) {tmp=fit(tmp);ps["job"].attr("value",tmp);if(tmp){c=tmp;title+=" "+tmp;}}else {title+=ses;}};if (ps["jobtype"]){if (tmp=ps["jobtype"].attr("value")) {c+=" '.$this->lang['seeking'].' "+$("#"+tmp).html();}else {c+=" '.$this->lang['seekingWork'].'";}};c+=" '.$this->lang['in'].'";c+=tt;if (ps["exp"]) {if (tmp=ps["exp"].attr("value")) {c+=" - "+$("#"+tmp).html();}};title+="'.$this->lang['jobLookingFor'].' '.$this->lang['in'].'";title+=tt;if (tmp=ps["other"].attr("value")) {tmp=fit(tmp);ps["other"].attr("value",tmp);if (tmp) c+=" - "+tmp;};';

                }else {
                    $this->pageInline.='if (ps["atitle"]){if (tmp=ps["atitle"].attr("value")) {title=tmp;}};c="";if (tmp=ps["other"].attr("value")) {tmp=fit(tmp);ps["other"].attr("value",tmp);if (tmp) c+=tmp;};';
                }
                $this->pageInline.='text=c+getContact();pv.prev().html(title);pv.html(text);collectData(true);};gen();';
        $lingo=array();
        $this->pageGlobal.='var lang=[];';
        foreach ($lingo as $val){
            for ($i=1;$i<4;$i++){
                $this->pageGlobal.='lang["'.$val.$i.'"]="'.$this->lang['mx'.$val.$i].'";';
            }
        }
    }

    function realestateView(){

        $this->hintForm(0);

        $locale='';
        $cond='';
        $floor='';
        $price='';
        $pty='';
        $bedrooms='';
        $salons='';
        $bathrooms='';
        $parking='';
        $area='';
        $areau='';
        $floor='';
        $floors='';
        $rooms='';
        $other='';
        if (isset ($this->adContent['fields']['locale']) && $this->adContent['fields']['locale'])$locale=$this->adContent['fields']['locale'];
        if (isset ($this->adContent['fields']['cond']) && $this->adContent['fields']['cond'])$cond=$this->adContent['fields']['cond'];
        if (isset ($this->adContent['fields']['floor']) && $this->adContent['fields']['floor'])$floor=$this->adContent['fields']['floor'];
        if (isset ($this->adContent['fields']['parking']) && $this->adContent['fields']['parking'])$parking=$this->adContent['fields']['parking'];
        if (isset ($this->adContent['fields']['price']) && $this->adContent['fields']['price'])$price=$this->adContent['fields']['price'];
        if (isset ($this->adContent['fields']['pty']) && $this->adContent['fields']['pty'])$pty=$this->adContent['fields']['pty'];
        if (isset ($this->adContent['fields']['bedrooms']) && $this->adContent['fields']['bedrooms'])$bedrooms=$this->adContent['fields']['bedrooms'];
        if (isset ($this->adContent['fields']['salons']) && $this->adContent['fields']['salons'])$salons=$this->adContent['fields']['salons'];
        if (isset ($this->adContent['fields']['bathrooms']) && $this->adContent['fields']['bathrooms'])$bathrooms=$this->adContent['fields']['bathrooms'];
        if (isset ($this->adContent['fields']['area']) && $this->adContent['fields']['area'])$area=$this->adContent['fields']['area'];
        if (isset ($this->adContent['fields']['areau']) && $this->adContent['fields']['areau'])$areau=$this->adContent['fields']['areau'];
        if (isset ($this->adContent['fields']['floor']) && $this->adContent['fields']['floor'])$floor=$this->adContent['fields']['floor'];
        if (isset ($this->adContent['fields']['floors']) && $this->adContent['fields']['floors'])$floors=$this->adContent['fields']['floors'];
        if (isset ($this->adContent['fields']['rooms']) && $this->adContent['fields']['rooms'])$rooms=$this->adContent['fields']['rooms'];
        if (isset ($this->adContent['fields']['other']) && $this->adContent['fields']['other'])$other=$this->adContent['fields']['other'];
        echo '<div class="sum els rc form m_b"><form id="wz" method="post"><div class="dtl"><ul>';
        $this->optLocation();
        ?><li><label><?= $this->lang['local'] ?>:</label><input onchange="gen()" id="locale" <?= $this->user->info['level']==9 ? '':'maxlength="40" '?>class="rc" type="text" value="<?= $locale ?>" placeholder="<?= $this->lang['hint_locale'] ?>" /></li><?php
        if ($this->sectionId!=106) {
        ?><li><label><?= $this->lang['area'] ?>:</label><input onchange="gen()" id="area" class="rc num" type="text" value="<?= $area ?>" placeholder="<?= $this->lang['hint_area'] ?>" <?= in_array($this->purposeId,array(1,2)) ? 'req':'' ?>/> <select id="areau" onchange="gen()" class="aw rc"><option id="lgSA1" value="lgSA1"<?= $areau=='lgSA1'?' selected="selected"':'' ?>><?= $this->lang['lgSA1'] ?></option><option id="lgSA2" value="lgSA2"<?= $areau=='lgSA2'?' selected="selected"':'' ?>><?= $this->lang['lgSA2'] ?></option></select></li><?php
        }
        switch($this->purposeId){
            case 1:
                $neg='';
                if (isset ($this->adContent['fields']['neg']) && $this->adContent['fields']['neg'])$neg=$this->adContent['fields']['neg'];
                ?><li><label><?= $this->lang['price'] ?>:</label><?php
                ?><select onchange="gen()" id="pty" class="rc short"><?php
                        for ($i=1; $i<6;$i++) {
                            echo '<option id="lgPty'.$i.'" value="lgPty'.$i.'"'.($pty=='lgPty'.$i?' selected="selected"':'').'>'.$this->lang['lgPty'.$i].'</option>';
                        }
                        ?></select><?php
                ?><input onchange="gen()" id="price" class="rc num" type="text" value="<?= $price ?>" placeholder="<?= $this->lang['hint_price'] ?>"/><span class="pch"><?= $this->getCountryUnit() ?></span><?php
                ?><select onchange="gen()" id="neg" class="rc short" disabled="disabled"><?php
                        ?><option value=""><?= $this->lang['optional'] ?></option><?php
                        for ($i=1; $i<5;$i++) {
                            echo '<option id="lgNeg'.$i.'" value="lgNeg'.$i.'"'.($neg=='lgNeg'.$i?' selected="selected"':'').'>'.$this->lang['lgNeg'.$i].'</option>';
                        }
                        ?></select></li><?php
                break;
            case 2:
                $per='';
                if (isset ($this->adContent['fields']['per']) && $this->adContent['fields']['per'])$per=$this->adContent['fields']['per'];
                        ?><li><label><?= $this->lang['price'] ?>:</label><?php
                        ?><select onchange="gen()" id="pty" class="rc short"><?php
                        for ($i=1; $i<6;$i++) {
                            echo '<option id="lgPty'.$i.'" value="lgPty'.$i.'"'.($pty=='lgPty'.$i?' selected="selected"':'').'>'.$this->lang['lgPty'.$i].'</option>';
                        }
                        ?></select><?php
                        ?><input onchange="gen()" id="price" class="rc num" type="text" value="<?= $price ?>" placeholder="<?= $this->lang['hint_price'] ?>"/><span class="pch"><?= $this->getCountryUnit().'</span> '.$this->lang['per'].' '
                        ?><select onchange="gen()" id="per" class="rc short" disabled="disabled"><?php
                        for ($i=1; $i<6;$i++) {
                            echo '<option id="lgPer'.$i.'" value="lgPer'.$i.'"'.($per=='lgPer'.$i?' selected="selected"':'').'>'.$this->lang['lgPer'.$i].'</option>';
                        }
                        ?></select></li><?php
                break;
            case 6:
                break;
            case 7:
            $pgs='';
            $pge='';
            if (isset ($this->adContent['fields']['pgs']) && $this->adContent['fields']['pgs'])$pgs=$this->adContent['fields']['pgs'];
            if (isset ($this->adContent['fields']['pge']) && $this->adContent['fields']['pge'])$pge=$this->adContent['fields']['pge'];
            $unit=$this->getCountryUnit();
            ?><li><label><?= $this->lang['price_range'] ?>:</label><?php
            ?><input onchange="gen()" id="pgs" class="rc num" type="text" value="<?= $pgs ?>" placeholder="<?= $this->lang['minimum'] ?>"/><span class="pch"><?= $unit ?></span><?php
                ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $this->lang['and'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
                ?><input onchange="gen()" id="pge" class="rc num" type="text" value="<?= $pge ?>" placeholder="<?= $this->lang['maximum'] ?>"/><span class="pch"><?= $unit ?></span><?php
            ?></li><?php
            break;
        }
        if (in_array($this->sectionId,array(3,8))) {
        ?><li><label><?= $this->lang['floors'] ?>:</label><select onchange="gen()" id="floors" class="rc"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<21;$i++) {
                echo '<option value="'.$i.'"'.($floors==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        }
        if (in_array($this->sectionId,array(1,2,6,106))) {
        ?><li><label><?= $this->lang['floor'] ?>:</label><select onchange="gen()" id="floor" class="rc"><option value=""><?= $this->lang['optional'] ?></option><?php
            echo '<option value="0"'.($floor==0?' selected="selected"':'').'>'.$this->lang['groundFloor'].'</option>';
            for ($i=1; $i<21;$i++) {
                echo '<option value="'.$i.'"'.($floor==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        }
        if (in_array($this->sectionId,array(6))) {
        ?><li><label><?= $this->lang['rooms'] ?>:</label><select onchange="gen()" id="rooms" class="rc"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<21;$i++) {
                echo '<option value="'.$i.'"'.($rooms==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        }
        if (in_array($this->sectionId,array(1,2,3,106))) {
        ?><li><label><?= $this->lang['salons'] ?>:</label><select onchange="gen()" id="salons" class="rc"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<11;$i++) {
                echo '<option value="'.$i.'"'.($salons==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        }
        if (in_array($this->sectionId,array(1,2,3,4,106))) {
        ?><li><label><?= $this->lang['bedrooms'] ?>:</label><select onchange="gen()" id="bedrooms" class="rc" req><option value="">0</option><?php
            for ($i=1; $i<11;$i++) {
                echo '<option value="'.$i.'"'.($bedrooms==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        ?><li><label><?= $this->lang['bathrooms'] ?>:</label><select onchange="gen()" id="bathrooms" class="rc" req><option value="">0</option><?php
            for ($i=1; $i<11;$i++) {
                echo '<option value="'.$i.'"'.($bathrooms==$i?' selected="selected"':'').'>'.$i.'</option>';
            }
            ?></select></li><?php
        ?><li><label><?= $this->lang['parking'] ?>:</label><select onchange="gen()" id="parking" class="rc"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<7;$i++) {
                echo '<option id="lgParking'.$i.'" value="lgParking'.$i.'"'.($parking=='lgParking'.$i?' selected="selected"':'').'>'.$this->lang['lgParking'.$i].'</option>';
            }
            ?></select></li><?php
        }
        if (!in_array($this->sectionId,array(7,9))) {
        ?><li><label><?= $this->lang['condition'] ?>:</label><select onchange="gen()" id="cond" class="rc" name="cond"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<4;$i++) {
                echo '<option id="lgCond'.$i.'" value="lgCond'.$i.'"'.($cond=='lgCond'.$i?' selected="selected"':'').'>'.$this->lang['lgCond'.$i].'</option>';
            }
            ?></select></li><?php
        }
        ?><li><label class="tp"><?= $this->lang['other'] ?>:</label><textarea <?= $this->user->info['level']==9 ? '':'maxlength="150" '?>onchange="gen()" id="other" class="rc"><?= $other ?></textarea></li><?php
        echo '</ul></div>';
        $this->sectionNameSingle=$this->sectionName;
        $section=$this->urlRouter->db->queryResultArray(
                    "select single,plural
                    from naming
                    where type_id=? and origin_id=? and lang=? and (country_id=0 or country_id=?) order by country_id desc",array(2,$this->sectionId,$this->urlRouter->siteLanguage,$this->countryId));
        if (!empty($section)) {
            $this->sectionName=$section[0]['PLURAL'];
            $this->sectionNameSingle=$section[0]['SINGLE'];
        }
                $this->pageInline.='ckMax();var gx=0;var pv=$("#pv");var unit="'.$this->getCountryUnit().'";var se="'.$this->sectionName.'";var ses="'.$this->sectionNameSingle.'";var pu="'.strtolower($this->purposes[$this->purposeId][$this->fieldNameIndex]).'";gen=function(){gx++;collect();var ps=params;var c=ses;var tmp="",tmp2="";var tt=se;switch('.$this->purposeId.'){case 1:case 2:c=c+" "+pu;tt=tt+" "+pu;break;case 6:case 7:c=pu+" "+c;tt=pu+" "+tt;break;};if (ps["locale"]){if (tmp=ps["locale"].attr("value"))c+=" "+tmp;};if(aoc && sloc)c+=(aoc==" "?" ":" "+aoc+" ")+sloc;title=tt+" '.$this->lang['in'].' '.$this->user->pending['post']['gloc'].' '.$this->user->pending['post']['dcni'].' ";if (ps["floor"]) {if (tmp=ps["floor"].attr("value")) {tmp=parseInt(tmp);if (tmp==0){c+=" - '.$this->lang['groundFloor'].'";}else {';
                if ($this->urlRouter->siteLanguage=='ar')
                        $this->pageInline.='c+=" - '.$this->lang['floorLabel'].' "+tmp;';
                else
                        $this->pageInline.='if(tmp>3)tmp+=lang["nth4"];else tmp+=lang["nth"+tmp];c+=" - "+tmp+" '.$this->lang['floorLabel'].'";';
                $this->pageInline.='};ps["floor"].prev().removeClass("req");}};if (ps["floors"]) {if (tmp=ps["floors"].attr("value"))c+=" - "+tmp+" '.strtolower($this->lang['floors']).'";};if (ps["rooms"]) {if (tmp=ps["rooms"].attr("value")) {if (tmp<3) tmp=lang["room"+tmp];else tmp=tmp+" "+lang["room3"];c+=" - "+tmp;ps["rooms"].prev().removeClass("req");}};if (ps["bedrooms"]) {if (tmp=ps["bedrooms"].attr("value")) {if (tmp<3) tmp=lang["bedroom"+tmp];else tmp=tmp+" "+lang["bedroom3"];c+=" - "+tmp;ps["bedrooms"].prev().removeClass("req");}};if (ps["salons"]) {if (tmp=ps["salons"].attr("value")) {if (tmp<3) tmp=lang["salon"+tmp];else tmp=tmp+" "+lang["salon3"];c+=" - "+tmp;}};if (ps["bathrooms"]) {if (tmp=ps["bathrooms"].attr("value")) {if (tmp<3) tmp=lang["bathroom"+tmp];else tmp=tmp+" "+lang["bathroom3"];c+=" - "+tmp;ps["bathrooms"].prev().removeClass("req");}};if (ps["parking"]) {if (tmp=ps["parking"].attr("value"))c+=" - "+$("#"+tmp).html();};if (ps["cond"]) {if (tmp=ps["cond"].attr("value"))c+=" - "+$("#"+tmp).html();};if (ps["area"]) {if (tmp=parseInt(ps["area"].attr("value"))) {c+=" - "+tmp+" "+$("#"+ps["areau"].attr("value")).html();ps["area"].prev().removeClass("req");}else ps["area"].attr("value","");};if (ps["price"]) {if (tmp=parseInt(ps["price"].attr("value"))) {if(ps["pty"]){var tmp2=$("#"+ps["pty"].attr("value")).html();tt=" "+(ps["pty"].attr("value")=="lgPty1"? "'.strtolower($this->lang['price']).'" : "'.$this->lang['zprice'].' "+tmp2)+" "+tmp+unit;}else {tt=" '.strtolower($this->lang['price']).' "+tmp+unit;}c+=" -"+tt;title+=tt;if(ps["neg"]){ps["neg"].attr("disabled",false);if(tmp=ps["neg"].attr("value")) c+=" "+$("#"+tmp).html();};if(ps["per"]){ps["per"].attr("disabled",false);if(tmp=ps["per"].attr("value")) c+=" '.$this->lang['per'].' "+$("#"+tmp).html();};ps["price"].prev().removeClass("req");}else ps["price"].attr("value","");};if (ps["pgs"]){tmp=parseInt(ps["pgs"].attr("value"));tmp2=parseInt(ps["pge"].attr("value"));if (tmp && tmp2){if (tmp>tmp2) {tt=tmp;tmp=tmp2;tmp2=tt;};tt=" '.$this->lang['price_between'].' "+tmp+unit+" '.$this->lang['and'].' "+tmp2+unit;c+=" -"+tt;title+=tt;}else if (tmp2){tt=" '.$this->lang['price_over'].' "+tmp2+unit;c+=" -"+tt;title+=tt;}else if (tmp){tt=" '.$this->lang['price_less'].' "+tmp+unit;c+=" -"+tt;title+=tt;}};if (tmp=ps["other"].attr("value")) {tmp=fit(tmp);if (tmp) c+=" - "+tmp;ps["other"].attr("value",tmp);};text=c+getContact();pv.prev().html(title);pv.html(text);collectData(true);};gen();';
        $lingo=array('bedroom','bathroom','salon','room');
        $this->pageGlobal.='var lang=[];';
        foreach ($lingo as $val){
            for ($i=1;$i<4;$i++){
                $this->pageGlobal.='lang["'.$val.$i.'"]="'.$this->lang['mx'.$val.$i].'";';
            }
        }
        if ($this->urlRouter->siteLanguage!="ar"){
            for($i=1;$i<5;$i++){
                $this->pageGlobal.='lang["nth'.$i.'"]="'.$this->lang['nth'.$i].'";';
            }
        }
    }

    function carsView(){

        $this->hintForm(0);

        $year=0;
        $model='';
        $color='';
        $trans='';
        $cond='';
        $price='';
        $other='';

        if (isset ($this->adContent['fields']['year']) && $this->adContent['fields']['year'])$year=$this->adContent['fields']['year'];
        if (isset ($this->adContent['fields']['model']) && $this->adContent['fields']['model'])$model=$this->adContent['fields']['model'];
        if (isset ($this->adContent['fields']['color']) && $this->adContent['fields']['color'])$color=$this->adContent['fields']['color'];
        if (isset ($this->adContent['fields']['trans']) && $this->adContent['fields']['trans'])$trans=$this->adContent['fields']['trans'];
        if (isset ($this->adContent['fields']['cond']) && $this->adContent['fields']['cond'])$cond=$this->adContent['fields']['cond'];
        if (isset ($this->adContent['fields']['price']) && $this->adContent['fields']['price'])$price=$this->adContent['fields']['price'];
        if (isset ($this->adContent['fields']['other']) && $this->adContent['fields']['other'])$other=$this->adContent['fields']['other'];
        echo '<div class="sum els rc form m_b"><form id="wz" method="post"><div class="dtl"><ul>';
        ?><li><label><?= $this->lang['model'] ?>:</label><input onchange="gen()" id="model" class="rc" type="text" name="model" value="<?= $model ?>" placeholder="<?= $this->lang['hint_type'] ?>" <?= $this->user->info['level']==9 ? '':'maxlength="30" '?>req/></li><?php
        ?><li><label><?= $this->lang['year'] ?>:</label><select onchange="gen()" id="year" class="rc" name="year" <?= ($this->purposeId==1 || $this->purposeId==2 ? 'req':'') ?>><option value=""><?= $this->lang['hint_year'] ?></option><?php for($i=(int)date('Y');$i>1944;$i--){echo '<option value="',$i,'"',($year==$i?' selected="selected"':''),'>',$i,'</option>';} ?></select></li><?php
        ?><li><label><?= $this->lang['color'] ?>:</label><input onchange="gen()" id="color" class="rc" type="text" name="color" value="<?= $color ?>" placeholder="<?= $this->lang['hint_color'] ?>" <?= $this->user->info['level']==9 ? '':'maxlength="30" '?><?= (in_array($this->purposeId,array(1,2))?'req':'') ?>/></li><?php
        ?><li><label><?= $this->lang['transmission'] ?>:</label><select onchange="gen()" id="trans" class="rc" <?= (in_array($this->purposeId,array(1,2))?'req':'') ?>><option value=""><?= $this->lang['specify'] ?></option><?php
            for ($i=1; $i<4;$i++) {
                echo '<option id="lgTrans'.$i.'" value="lgTrans'.$i.'"'.($trans=='lgTrans'.$i?' selected="selected"':'').'>'.$this->lang['lgTrans'.$i].'</option>';
            }
            ?></select></li><?php
        ?><li><label><?= $this->lang['condition'] ?>:</label><select onchange="gen()" id="cond" class="rc" name="cond"><option value=""><?= $this->lang['optional'] ?></option><?php
            for ($i=1; $i<4;$i++) {
                echo '<option id="lgCond'.$i.'" value="lgCond'.$i.'"'.($cond=='lgCond'.$i?' selected="selected"':'').'>'.$this->lang['lgCond'.$i].'</option>';
            }
            ?></select></li><?php
        switch($this->purposeId){
            case 1:
                $mg='';
                $mgu='';
                $neg='';
                if (isset ($this->adContent['fields']['mg']) && $this->adContent['fields']['mg'])$mg=$this->adContent['fields']['mg'];
                if (isset ($this->adContent['fields']['mgu']) && $this->adContent['fields']['mgu'])$mgu=$this->adContent['fields']['mgu'];
                if (isset ($this->adContent['fields']['neg']) && $this->adContent['fields']['neg'])$neg=$this->adContent['fields']['neg'];
                ?><li><label><?= $this->lang['mileage'] ?>:</label><input onchange="gen()" id="mg" class="rc num" type="text" value="<?= $mg ?>" placeholder="<?= $this->lang['hint_mileage'] ?>" /> <select id="mgu" onchange="gen()" class="aw rc"><option id="lgMgu1" value="lgMgu1"<?= $mgu=='lgMgu1'?' selected="selected"':'' ?>><?= $this->lang['KM'] ?></option><option id="lgMgu2" value="lgMgu2"<?= $mgu=='lgMgu2'?' selected="selected"':'' ?>><?= $this->lang['MILE'] ?></option></select></li><?php
                ?><li><label><?= $this->lang['price'] ?>:</label><input onchange="gen()" id="price" class="rc num" type="text" value="<?= $price ?>" placeholder="<?= $this->lang['hint_price'] ?>"/><span class="pch"><?= $this->getCountryUnit() ?></span><?php
                ?><select onchange="gen()" id="neg" class="rc short" disabled="disabled"><?php
                        ?><option value=""><?= $this->lang['optional'] ?></option><?php
                        for ($i=1; $i<5;$i++) {
                            echo '<option id="lgNeg'.$i.'" value="lgNeg'.$i.'"'.($neg=='lgNeg'.$i?' selected="selected"':'').'>'.$this->lang['lgNeg'.$i].'</option>';
                        }
                        ?></select></li><?php
                break;
            case 2:
                $per='';
                if (isset ($this->adContent['fields']['per']) && $this->adContent['fields']['per'])$per=$this->adContent['fields']['per'];
                        ?><li><label><?= $this->lang['price'] ?>:</label><input onchange="gen()" id="price" class="rc num" type="text" value="<?= $price ?>" placeholder="<?= $this->lang['hint_price'] ?>"/><span class="pch"><?= $this->getCountryUnit().'</span> '.$this->lang['per'].' ' ?><?php
                        ?><select onchange="gen()" id="per" class="rc short" disabled="disabled"><?php

                        for ($i=1; $i<6;$i++) {
                            echo '<option id="lgPer'.$i.'" value="lgPer'.$i.'"'.($per=='lgPer'.$i?' selected="selected"':'').'>'.$this->lang['lgPer'.$i].'</option>';
                        }
                        ?></select></li><?php
                break;
            case 6:
                break;
            case 7:
            $pgs='';
            $pge='';
            if (isset ($this->adContent['fields']['pgs']) && $this->adContent['fields']['pgs'])$pgs=$this->adContent['fields']['pgs'];
            if (isset ($this->adContent['fields']['pge']) && $this->adContent['fields']['pge'])$pge=$this->adContent['fields']['pge'];
            $unit=$this->getCountryUnit();
            ?><li><label><?= $this->lang['price_range'] ?>:</label><?php
                ?><input onchange="gen()" id="pgs" class="rc num" type="text" value="<?= $pgs ?>" placeholder="<?= $this->lang['minimum'] ?>"/><span class="pch"><?= $unit ?></span><?php
                ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $this->lang['and'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
                ?><input onchange="gen()" id="pge" class="rc num" type="text" value="<?= $pge ?>" placeholder="<?= $this->lang['maximum'] ?>"/><span class="pch"><?= $unit ?></span><?php
            ?></li><?php
            break;
        }
        ?><li><label class="tp"><?= $this->lang['other'] ?>:</label><textarea <?= $this->user->info['level']==9 ? '':'maxlength="150" '?>onchange="gen()" id="other" class="rc"><?= $other ?></textarea></li><?php
        echo '</ul></div>';
        $this->pageInline.='ckMax();var gx=0;var pv=$("#pv");var unit="'.$this->getCountryUnit().'";var se="'.$this->sectionName.'";var pu="'.strtolower($this->purposes[$this->purposeId][$this->fieldNameIndex]).'";gen=function(){gx++;collect();var ps=params,c=se,tmp="",tt="";if (tmp=ps["model"].attr("value")) {tmp=fit(tmp);c+=" "+tmp;ps["model"].attr("value",tmp);ps["model"].prev().removeClass("req");};switch('.$this->purposeId.'){case 1:case 2:c=c+" "+pu;break;case 6:case 7:c=pu+" "+c;break;};title=c+" '.$this->lang['in'].' '.$this->user->pending['post']['tloc'].' '.$this->user->pending['post']['dcni'].'";if (tmp=ps["year"].attr("value")) {tt=" '.strtolower($this->lang['model']).' "+tmp;c+=" -"+tt;title+=tt;ps["year"].prev().removeClass("req");};if (tmp=ps["color"].attr("value")){tmp=fit(tmp);c+=" - "+tmp;ps["color"].attr("value",tmp);ps["color"].prev().removeClass("req");};if (tmp=ps["trans"].attr("value")){c+=" - "+$("#"+tmp).html();ps["trans"].prev().removeClass("req");};if (tmp=ps["cond"].attr("value"))c+=" - "+$("#"+tmp).html();if (ps["mg"]) {if (tmp=parseInt(ps["mg"].attr("value"))) {c+=" - "+tmp+" "+$("#"+ps["mgu"].attr("value")).html();}else ps["mg"].attr("value","");};if (ps["price"]) {if (tmp=parseInt(ps["price"].attr("value"))) {tt=" '.strtolower($this->lang['price']).' "+tmp+unit;c+=" -"+tt;title+=tt;if(ps["neg"]){ps["neg"].attr("disabled",false);if(tmp=ps["neg"].attr("value")) c+=" "+$("#"+tmp).html();};if(ps["per"]){ps["per"].attr("disabled",false);if(tmp=ps["per"].attr("value")) c+=" '.$this->lang['per'].' "+$("#"+tmp).html();};ps["price"].prev().removeClass("req");}else ps["price"].attr("value","");};if (ps["pgs"]){tmp=parseInt(ps["pgs"].attr("value"));tmp2=parseInt(ps["pge"].attr("value"));if (tmp && tmp2){if (tmp>tmp2) {tt=tmp;tmp=tmp2;tmp2=tt;};tt=" '.$this->lang['price_between'].' "+tmp+unit+" '.$this->lang['and'].' "+tmp2+unit;c+=" -"+tt;}else if (tmp2){tt=" '.$this->lang['price_over'].' "+tmp2+unit;c+=" -"+tt;}else if (tmp){tt=" '.$this->lang['price_less'].' "+tmp+unit;c+=" -"+tt;}};if (tmp=ps["other"].attr("value")) {tmp=fit(tmp);if (tmp) c+=" - "+tmp;ps["other"].attr("value",tmp);};text=c+getContact();pv.prev().html(title);pv.html(text);collectData(true);};gen();';
    }

}
?>