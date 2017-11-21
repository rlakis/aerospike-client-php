<?php
require_once 'Page.php';

class Admin extends Page
{
    
    var $action='',$liOpen='';
    private $uid = 0;
    private $aid = 0;
    private $userdata = 0;
    
    function __construct($router)
    {
        parent::__construct($router);
        $this->uid = 0;
        $this->sub = $_GET['sub'] ?? '';
        $this->mobile_param = $_GET['t'] ?? '';
        $this->aid = filter_input(INPUT_GET, 'r', FILTER_SANITIZE_NUMBER_INT,['options'=>['default'=>0]]);
        
        $this->hasLeadingPane=true;
        
        if($this->isMobile || !$this->user->isSuperUser())
        {
            $this->user->redirectTo('/notfound/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }     
        
        $this->load_lang(array("account"));
        
        $this->inlineCss .= 
                '.ts .bt{width:auto;padding:5px 30px!important}'
                . '#cron{direction:ltr;margin-top:5px}#cron a{color:#00e;margin-right:30px}#cron a:hover{text-decoration:underline}'
                . '#statDv{width:760px}'
                . '.ts .lm{overflow:visible}'
                . '.ts label{vertical-align:middle}'
                . '.hy li{float:right;width:370px;border:0!important}'
                . '.hy label{margin-bottom:10px}'
                . 'textarea{width:300px;height:200px;padding:3px}'
                . '.action{width:800px!important;text-align:center}'
                . '.options{position:absolute;border:1px solid #aaa;border-bottom:0;width:306px;background-color:#FFF}'
                . '.options li{cursor:pointer;border-bottom:1px solid #aaa;direction:rtl;text-align:right;padding:10px;}'
                . '.options li:hover{background-color:#00e;color:#FFF}'
                . '#msg{height:40px;display:block}'
                . '.rpd{display:block}.rpd textarea{width:740px}'
                . '.tbs{width:750px}.tbs li{float:left;width:80px}'
                . '.load{width: 30px;height: 30px;display: inline-block;vertical-align: middle}'
                . '.filters{background-color:#ECECEC}.filters select{padding:2px 10px;margin:10px 20px}';
        
        $this->set_require('css', 'account');
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->forceNoIndex=true;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->urlRouter->cfg['enabled_ads']=0;
        
        
        $parameter = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);
        
        
        
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
        
        if($action)
        {
            $redirectWenDone=true;
            
            switch ($action)
            {
                case 'unblock':
                    $unblockNumbers = [];
                    $userdata = [$this->parseUserBins( \Core\Model\NoSQL::getInstance()->fetchUser($parameter)) ];
                    
                    
                    if (isset($userdata[0]['mobiles']))
                    {
                        $accounts=[];
                        foreach($userdata[0]['mobiles'] as $number)
                        {
                            $uids=[];
                            if (\Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($number['number']+0, $uids)== Core\Model\NoSQL::OK)
                            {
                                foreach ($uids as $bins) 
                                {
                                    $accounts[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
                                }
                            }
                        }
                        
                        $uids = [$userdata[0]['id']];
                        $numbers = [];
                        foreach($accounts as $account)
                        {
                            $uids[] = $account['id'];
                            
                            foreach($account['mobiles'] as $number)
                            {
                                $numbers[$number['number']] = $number['id']; 
                            }
                        }
                        $this->user->unblock($uids,$numbers);
                    }
                    break;
                    default:
                        break;
            }
            
            if($redirectWenDone){
                $url = "";
                unset($_GET['action']);
                
                foreach ($_GET as $key => $value){
                    if($url){
                        $url .= '&';
                    }
                    $url .= $key.'='.$value;
                }
                if($url) $url = '?'.$url;
                
                header('Location: '. $url);
            }
        }
        
        if ($parameter)
        {
            $date = new DateTime();
            
            $len = strlen($parameter);
            $this->uid = intval($parameter);

            $this->userdata = [$this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($this->uid))];
           
        }
        else
        {
            $parameter = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT,['options'=>['default'=>0]]);
            if ($parameter)
            {
                $this->userdata = [];
                if (\Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($parameter, $uids)== Core\Model\NoSQL::OK)
                {
                    foreach ($uids as $bins) 
                    {
                        $this->userdata[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
                    }
                }                
            }
            else 
            {
                if ($this->aid)
                {
                    $this->userdata = [];
                    include_once $this->urlRouter->cfg['dir'].'/core/lib/MCSaveHandler.php';
                    $handler = new MCSaveHandler($this->urlRouter->cfg);
                    $this->userdata = $handler->checkFromDatabase($this->aid);
                    //$uids = \Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($parameter);
                    //foreach ($uids as $bins) 
                    {
                        //$this->userdata[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
                    }                
                }
            }
        }
        
        $this->render();
    }    
    
    
    private function parseUserBins($bins)
    {
        if($bins && count($bins))
        {            
            $release = intval(filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT));
        
            $bins[Core\Model\ASD\USER_DATE_ADDED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_DATE_ADDED]);
            $bins[Core\Model\ASD\USER_LAST_VISITED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_LAST_VISITED]);
            $bins[Core\Model\ASD\USER_PRIOR_VISITED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_PRIOR_VISITED]);
            
            if (isset($bins[Core\Model\ASD\USER_LAST_AD_RENEWED]))
            {
                $bins[Core\Model\ASD\USER_LAST_AD_RENEWED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_LAST_AD_RENEWED]);
            }
            
            if (isset($bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS]))
            {
                $bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS]);
            }

            unset($bins['mobile']);
            $_mobiles = \Core\Model\NoSQL::getInstance()->mobileFetchByUID($bins[\Core\Model\ASD\USER_PROFILE_ID]);
            $_devices = \Core\Model\NoSQL::getInstance()->getUserDevices($bins[\Core\Model\ASD\USER_PROFILE_ID]);
            
            
            for ($i=0; $i<count($_mobiles); $i++)
            {
                unset($_mobiles[$i][\Core\Model\ASD\USER_UID]);
                $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_REQUESTED] = $this->unixTimestampToDateTime($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_REQUESTED]);
                if (isset($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]>0)
                {
                    $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED] = $this->unixTimestampToDateTime($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]);
                }

                switch ($_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG]) 
                {
                    case 0:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'Android app';
                        break;
                    case 1:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'Website';
                        break;
                    case 2:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'IOS app';
                        break;
                    default:
                        break;
                }

                $ttl = MCSessionHandler::checkSuspendedMobile($_mobiles[$i][Core\Model\ASD\USER_MOBILE_NUMBER], $reason);
                if ($ttl)
                {
                    if ($release===-1)
                    {
                        MCSessionHandler::setSuspendMobile($bins[\Core\Model\ASD\USER_PROFILE_ID], $_mobiles[$i][Core\Model\ASD\USER_MOBILE_NUMBER], 60, TRUE);
                        $_mobiles[$i]['suspended']['realease']='within 60 seconds';
                        $bins['suspended']='60s';
                    }
                    else 
                    {
                        $_mobiles[$i]['suspended']['till'] = gmdate("Y-m-d H:i:s T", time()+$ttl); 
                        $_mobiles[$i]['suspended']['reason'] = strpos($reason, ':') ? trim(substr($reason, strpos($reason, ':')+1)) : $reason;      
                        $bins['suspended']='YES';
                    }
                }
            }

            for ($i=0; $i<count($_devices); $i++)
            {
                unset($_devices[$i][\Core\Model\ASD\USER_UID]);
                $_devices[$i][Core\Model\ASD\USER_DEVICE_DATE_ADDED] = $this->unixTimestampToDateTime($_devices[$i][Core\Model\ASD\USER_DEVICE_DATE_ADDED]);
                $_devices[$i][Core\Model\ASD\USER_DEVICE_LAST_VISITED] = $this->unixTimestampToDateTime($_devices[$i][Core\Model\ASD\USER_DEVICE_LAST_VISITED]);
                if (isset($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS])  && isset($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS][0]) && $_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS][0]!='{')
                {
                    $_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS] = base64_decode($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS]);
                }
            }

            $bins['mobiles'] = $_mobiles;
            $bins['devices'] = $_devices;
               
            if (isset($bins['password']))
            {
                unset($bins['password']);
            }
            if (isset($bins['jwt']))
            {
                unset($bins['jwt']);
            }
        }
        else
        {
            $bins = '';
        }
        return $bins;        
    }
    
    
    function side_pane()
    {
        $this->renderSideAdmin();
        //$this->renderSideUserPanel();
    }
    
    
    function renderSideAdmin(){
        $sub = $this->sub;
        $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
        ?><h4><?= $this->lang['myPanel'] ?></h4><?php
        echo '<ul class=\'sm\'>';
        //echo '<li><a href=\'', $this->urlRouter->getURL($countryId,$cityId), '\'>', $this->lang['homepage'], '</a></li>';

        if ($sub=='')
            echo '<li class=\'on\'><b>', $this->lang['label_users'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '\'>', $this->lang['label_users'], '</a></li>';
        
        if ($sub=='areas')
            echo '<li class=\'on\'><b>', $this->lang['label_areas'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=areas\'>', $this->lang['label_areas'], '</a></li>';
        if ($sub=='ads')
            echo '<li class=\'on\'><b>', $this->lang['label_ads_monitor'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=ads\'>', $this->lang['label_ads_monitor'], '</a></li>';
        /*
        if ($this->urlRouter->module=='about')
            echo '<li class=\'on\'><b>', $this->lang['aboutUs'], '</b></li>';
        else
            echo '<li><a href=\'/about/', $lang, '\'>', $this->lang['aboutUs'], '</a></li>';
        if ($this->urlRouter->module=='contact')
            echo '<li class=\'on\'><b>', $this->lang['contactUs'], '</b></li>';
        else
            echo '<li><a href=\'/contact/', $lang, '\'>', $this->lang['contactUs'], '</a></li>';
        if ($this->urlRouter->module=='gold')
            echo '<li class=\'on\'><b>', $this->lang['gold_title'], '</b></li>';
        else
            echo '<li><a href=\'/gold/', $lang, '\'>', $this->lang['gold_title'], '</a></li>';
        if ($this->urlRouter->module=='privacy')
            echo '<li class=\'on\'><b>', $this->lang['privacyPolicy'], '</b></li>';
        else
            echo '<li><a href=\'/privacy/', $lang, '\'>', $this->lang['privacyPolicy'], '</a></li>';
        if ($this->urlRouter->module=='terms')
            echo '<li class=\'on\'><b>', $this->lang['termsConditions'], '</b></li>';
        else
            echo '<li><a href=\'/terms/', $lang, '\'>', $this->lang['termsConditions'], '</a></li>';
        /*if ($this->urlRouter->module=='advertise')
            echo '<li class=\'on\'><b>', $this->lang['advertiseUs'], '</b></li>';
        else
            echo '<li><a href=\'/advertise/', $lang, '\'>', $this->lang['advertiseUs'], '</a></li>';
        if ($this->urlRouter->module=='publication-prices')
            echo '<li class=\'on\'><b>', $this->lang['pricelist'], '</b></li>';
        else
            echo '<li><a href=\'/publication-prices/', $lang, '\'>', $this->lang['pricelist'], '</a></li>';*/ 
        echo "</ul><br />";
    }
   
    
    function unixTimestampToDateTime(int $ts) : string
    {
        $date = new DateTime();
        $date->setTimestamp($ts);
        return $date->format("Y-m-d H:i:s T");
    }
    
    
    function mainMobile()
    {
    }
    
    
    function main_pane()
    {
        $language = 'en';
        
        switch ($this->sub)
        {
            case 'ads':
                $langIndex = $this->urlRouter->siteLanguage == 'ar' ? 1 : 2;
                ?><div class="filters"><?php
                    ?><select onchange="fetchStat()" id="pubId"><?php
                        ?><option value="0"><?= $this->lang['all_pubs'] ?></option><?php
                        ?><option value="1"><?= $this->lang['mourjan'] ?></option><?php
                        ?><option value="2"><?= $this->lang['other_sources'] ?></option><?php
                    ?></select><?php
                    ?><select onchange="fetchStat()" id="cnId"><?php
                        ?><option value="0"><?= $this->lang['opt_all_countries'] ?></option><?php
                        foreach($this->urlRouter->countries as $id => $pub){
                            ?><option value="<?= $id ?>"><?= $pub['name'] ?></option><?php
                        }
                    ?></select><?php
                    ?><select onchange="fetchStat()" id="cId" style="display:none"><?php
                        ?><option value="0"><?= $this->lang['all_cities'] ?></option><?php
                        foreach($this->urlRouter->cities as $id => $pub){
                            ?><option value="<?= $pub[4].'_'.$id ?>"><?= $pub[$langIndex] ?></option><?php
                        }
                    ?></select><?php
                    ?><select onchange="fetchStat()" id="secId"><?php
                        ?><option value="0"><?= $this->lang['opt_all_categories'] ?></option><?php
                        foreach($this->urlRouter->sections as $id => $section){
                            ?><option value="<?= $id ?>"><?= $section[$langIndex] ?></option><?php
                        }
                    ?></select><?php
                ?></div><?php
                ?><div id="cron"><a href="javascript:fetchStat(0);">today</a><a href="javascript:fetchStat(7);">7 days</a><a href="javascript:fetchStat(15);">15 days</a><a href="javascript:fetchStat(30);">30 days</a><a href="javascript:fetchStat(90);">3 months</a><a href="javascript:fetchStat(180);">6 months</a><a href="javascript:fetchStat(365);">1 year</a></div><?php
                ?><div id="statDv" class="load"></div><?php
                
                        $this->globalScript.="
var HSLD=0,span=30; 
var fetchStat = function(x){
if(typeof x === 'undefined')x=span;
span=x;
var sec=$('#secId').val();
var pub=$('#pubId').val();
var cn=$('#cnId');
var c=$('#cId');

    cn.css('display','inline-block');
    var j=0,cnv=cn.val(),cnl=cnv.length+1;
    c.children().each(function(i,e){
        var v=e.value;
        if(v=='0' || v.substring(0,cnl)==cnv+'_'){
            e.style.display='block';
            j++;
        }else{
            e.style.display='none';
            if(e.selected){
                c.val(0);
            }
        } 
    });
    if(j>2){
        c.css('display','inline-block');
    }else{
        c.css('display','none');
    }

var cnh=cn.val();
$.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                ads:1,
                sec:sec,
                pub:pub,
                cn:cnh,
                c:c.val().substring(cnh.length+1),
                span:x
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    if(rp.DATA.d){
                        var x =$('#statDv');
                        x.removeClass('hxf');
                        var gS={
                            chart: {
                                spacingRight:0,
                                spacingLeft:0
                            },
                            title: {
                                text: (lang=='ar'?'الاعلانات الواردة':'incoming ads'),
                                style:{
                                    'font-weight':'bold',
                                    'font-family':(lang=='ar'?'tahoma,arial':'verdana,arial'),
                                    'direction':(lang=='ar'?'rtl':'ltr')
                                }
                            },
                            xAxis: {
                                type: 'datetime',
                                title: {
                                    text: null
                                }
                            },
                            yAxis: {
                                title: {
                                    text: null
                                }
                            },
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                enabled: false
                            },    
                            colors:[
                                '#2f7ed8',
                                '#ff9000'
                            ]
                        };
                        var series = [{
                            type: 'line',
                            name: 'ads',
                            pointInterval:24 * 3600 * 1000,
                            pointStart: rp.DATA.d,
                            data: rp.DATA.c
                        }];
                        
                        gS['series']=series;
                        x.highcharts(gS);
                    }else{
                        var x=$('#statDv');
                        x.removeClass('load');
                        x.addClass('hxf');
                        x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                    }
                }else{
                    var x=$('#statDv');
                    x.removeClass('load');
                    x.addClass('hxf');
                    x.html(lang=='ar'?'فشل محرك مرجان بالحصول على الاحصائيات':'Mourjan system failed to load statistics');
                }
            }
       });
};
(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='".$this->urlRouter->cfg['url_highcharts']."/min.js';
        sh.onload=sh.onreadystatechange=function(){
        if (!HSLD && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete')){
            HSLD=1;  
       fetchStat();
   }};head.insertBefore(sh,head.firstChild)})();
                                
                        ";
                        
                break;
            
            case 'areas':            
                ?><div><?php        
                ?><ul class="ts"><?php
                ?><li><?php 
                ?><div class="lm"><label><?= $this->lang['country'] ?></label><select onchange="CC()" id="country"><?php 
                foreach($this->urlRouter->countries as $country)
                {
                    echo "<option value=". strtoupper($country['uri']).">{$country['name']}</option>";
                }
                ?></select><input id="rotate" type="button" class="bt" onclick="rotate(this)" style="margin:0 30px" value="<?= $this->lang['rotate']?>" /></div><?php
                 ?></li><?php
                ?><li><?php 
                ?><div class="lm"><label><?= $this->lang['keyword'] ?></label><input onfocus="build()" onkeydown="idir(this)" onkeyup="load(this)" onchange="idir(this,1)" id="keyword" type="text" /><?php
                ?><input onclick="nek(this)" type="button" class="bt" style="margin:0 30px" value="<?= $this->lang['new_key']?>" /><?php 
                ?></div></li><?php
                ?></ul><?php 
                ?><form onsubmit="save();return false"><input id="id" type="hidden" value="0" /><?php
                ?><ul class="ts hy"><?php
                ?><li id="msg" class="action">كلمة جديدة</li><?php
                ?><li><label>عربي</label><input onchange="rmsg()" id="ar" class="ar" type="text" /></li><?php
                ?><li><label>English</label><input onchange="rmsg()" id="en" class="en" type="text" /></li><?php
                ?><li><textarea onchange="rmsg()" id="tar" class="ar"></textarea></li><?php
                ?><li><textarea onchange="rmsg()" id="ten" class="en"></textarea></li><?php
                ?><li class="action"><input id="submit" type="submit" class="bt" value="<?= $this->lang['save']?>" disabled="true"/></li><?php
                ?></ul><?php
                ?></form><?php
                ?></div><?php 
                $this->inlineQueryScript.='$("body").click(function(e){if(e.target.id!="keyword")clear()});';
                $this->globalScript.='
                    var xhr=null,locs=[];
                    function nek(e){
                        $("#id").val(0);
                        $("#ar").val("");
                        $("#en").val("");
                        $("#ten").val("");
                        $("#tar").val("");
                        $("#submit").removeAttr("disabled");
                        $("#msg").html("كلمة جديدة");
                    };
                    function rotate(e){
                        e=$(e);
                        var c=$("#country").val();
                        e.next().remove();
                        e.css("visibility","hidden");
                        e.parent().append("<span class=\'load\'></span>");
                        xhr = $.ajax({
                            type:"GET",
                            url: "/ajax-keyword/",
                            data:{rotate:1,c:c},
                            success: function(rp) {
                                if(rp && rp.RP){
                                    var d=("<span class=\'done\'></span>");
                                    e.next().remove();
                                    e.css("visibility","visible");
                                    e.parent().append(d);
                                }else{
                                    frot(e);
                                }
                            },
                            error:function(rc) {
                                frot(e);
                            }
                        });
                    };
                    function frot(){                    
                        var d=("<span class=\'fail\'></span>");
                        e.next().remove();
                        e.css("visibility","visible");
                        e.parent().append(d);
                    }
                    function CC(){
                        clear();
                        rmsg();
                        $("#id").val(0);
                        $("#ar").val("");
                        $("#en").val("");
                        $("#ten").val("");
                        $("#tar").val("");
                        $("#submit").removeAttr("disabled");
                        $("#rotate").next().remove();
                        locs=[];
                        load($("#keyword")[0],1);
                        $("#msg").html("كلمة جديدة");
                    };
                    function load(e,nop){
                        var c=$("#country").val();
                        var v=e.value;
                        if(v!=""){
                            if(xhr && xhr.readyState != 4){
                                xhr.abort();
                            }
                            clear();
                            xhr = $.ajax({
                                type:"GET",
                                url: "/ajax-keyword/",
                                data:{k:v,c:c},
                                success: function(rp) {
                                    if(rp && rp.RP){
                                        locs=rp.DATA.keys;
                                        if(typeof nop==="undefined"){
                                            build(e);
                                        }
                                    }else{
                                        clear();
                                    }
                                },
                                error:function(rc) {
                                    clear();
                                }
                            });
                        }
                    };
                    function build(){  
                        if(typeof e === "undefined"){
                            e=("#keyword");
                        }else{
                            e=$(e);
                        }
                        if(locs.length>0){
                            var dv=$("<ul id=\'list\' class=\'options\'></ul>");
                            for(var i in locs){
                                var o=$("<li onclick=\'edit("+i+")\'>"+locs[i].KEYWORD+"</li>");
                                dv.append(o);
                                if(i>6)break;
                            }
                            $("body").append(dv);
                            var p=e.offset();
                            var t=p.top+e.height()+8;
                            dv.css({top:t+"px", left: p.left+"px"});
                        }else{
                            clear();
                        }
                    };
                    function clear(){
                        $("#list").remove();
                    };
                    function rmsg(){
                        $("#msg").html("");
                    };
                    function edit(i){
                        rmsg();
                        var o=locs[i];
                        $("#id").val(o.ID);
                        $("#ar").val(o.KEYWORD);
                        $("#en").val(o.EN);
                        $("#ten").val(o.EN_FORM);
                        $("#tar").val(o.AR_FORM);
                        $("#submit").removeAttr("disabled");
                        $("#msg").html("");
                    };
                    function save(){
                        var d={
                            id:$("#id").val(),
                            ar:$("#ar").val(),
                            en:$("#en").val(),
                            ten:$("#ten").val(),
                            tar:$("#tar").val(),
                            cc:$("#country").val()
                        };
                        $.ajax({
                            type:"POST",
                            url: "/ajax-keyword/",
                            data:d,
                            success: function(rp) {
                                if(rp && rp.RP){
                                    $("#rotate").next().remove();
                                    $("#msg").html("<span class=\'done\'></span> تم الحفظ");
                                    load($("#keyword")[0],1);
                                }else{
                                    fail();
                                }
                            },
                            error:function(rc) {
                                fail();
                            }
                        });
                    };
                    function fail(){
                        $("#msg").html("<span class=\'fail\'></span> فشلت عملية الحفظ");
                    }
                    ';
                    
                break;
                                         
                
                default:
                    
                    ?><form method="get"><?php
                    ?><ul class="ts"><?php                                
                    ?><li><?php 
                    ?><div class="lm"><label>UID:</label><input name="p" type="text" value="<?= $this->uid ? $this->uid : '' ?>" /><?php
                    ?><input type="submit" class="bt" style="margin:0 30px" value="<?= $this->lang['review']?>" /><?php 
                    ?></div></li><?php
                    ?></ul><?php
                    ?></form><?php

                    ?><form method="get"><?php
                    ?><ul class="ts"><?php                                
                    ?><li><?php 
                    ?><div class="lm"><label><?= $this->lang['labelP0'] ?>:</label><input name="t" type="tel" value="<?= $this->mobile_param ?>" /><?php
                    ?><input type="submit" class="bt" style="margin:0 30px" value="<?= $this->lang['review']?>" /><?php 
                    ?></div></li><?php
                    ?></ul><?php
                    ?></form><?php
                    
                    ?><form method="get"><?php
                    ?><ul class="ts"><?php                                
                    ?><li><?php 
                    ?><div class="lm"><label>AID:</label><input name="r" type="ad" value="<?= $this->aid?$this->aid:'' ?>" /><?php
                    ?><input type="submit" class="bt" style="margin:0 30px" value="<?= $this->lang['review']?>" /><?php 
                    ?></div></li><?php
                    ?></ul><?php
                    ?></form><?php
                    
                    if ($this->userdata && count($this->userdata) && (($this->aid==0 && $this->userdata[0]) || ($this->aid)))
                    {
                        if ($this->aid==0)
                        {
                        foreach($this->userdata as $record)
                        {
                            $this->parseUserRecordData($record);
                            echo '<br/>';
                        }
                        }
                        else
                        {
                            echo '<div dir="ltr">';
                            echo '<pre style="font-size:12pt;font-family:arial;line-height:18pt;">';
                            $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
                                    }, json_encode($this->userdata));
                                    
                            $this->userdata = json_decode($str);
                            if (isset($this->userdata->TITLE))
                            {
                                unset($this->userdata->TITLE);
                            }
                            echo json_encode($this->userdata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                            echo '</pre></div>';
                        }
                        
                        $this->globalScript .= '
                            var block=function(id,e){
                                e=$(e).parent().parent();
                                if(e.next().hasClass("rpd")){
                                    e.next().remove()
                                }else{
                                    var d=$("<div class=\'rpd cct\'><b>سبب الايقاف؟</b><textarea onkeydown=\'idir(this)\' onchange=\'idir(this,1)\'></textarea><input type=\'button\' onclick=\'rpa("+id+",this)\' class=\'bt\' value=\'ايقاف\'><input type=\'button\' onclick=\'closeA(this)\' class=\'bt cl\' value=\'إلغاء\'></div>");                                
                                    d.insertAfter(e);
                                }
                            };
                            var suspend=function(id,e){
                                Dialog.show("susp_dialog",null,function(){suspA(e,id)});  
                            };
                            var suspA=function(e,usr){
                                e=$(e);
                                e.addClass("load");
                                $.ajax({
                                    type:"POST",
                                    url:"/ajax-ususpend/",
                                    data:{i:usr,v:$("#suspT").val()},
                                    dataType:"json",
                                    success:function(rp){
                                        e.removeClass("load");
                                        if (rp.RP) {
                                            e.html("Release");
                                            e[0].onclick=function(){};
                                            e.attr("href","?p="+usr+"&a=-1");
                                            e.parent().css("float","left");
                                        }
                                    },
                                    error:function(){
                                        e.removeClass("load");
                                    }
                                });
                            };
                            var closeA=function(e){
                                e=$(e).parent().remove()
                            };
                            var rpa=function(id,e){
                                e=$(e);
                                var m=$("textarea",e.parent()).val();
                                e.parent().remove();
                                if(m.length>0){
                                    $.ajax({
                                        type:"POST",
                                        url:"/ajax-ublock/",
                                        data:{
                                            i:id,
                                            msg:"Blocked From Admin Panel By Admin "+UID+" reason <<"+m+">>"
                                        },
                                        dataType:"json",
                                        success:function(rp){
                                            document.location="";
                                        }
                                    });
                                }
                            };
                        ';
                        
                    }
                    else
                    {
                        ?><div class="ctr"><?php
                        if($this->uid || $this->mobile_param!='')
                        {
                            ?><h4>NO DATA FOUND</h4><?php
                        }
                        ?></div><?php 
                    }
        
                    break;
        }
    }
    
    
    function parseUserRecordData($record)
    {
        echo '<ul class="tbs">';
        echo '<li><a href="/myads/?sub=drafts&u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Drafts</a></li>';
        echo '<li><a href="/myads/?sub=pending&u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Pending</a></li>';
        echo '<li><a href="/myads/?u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Active</a></li>';
        echo '<li><a href="/myads/?sub=archive&u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Archived</a></li>';
        echo '<li><a href="/myads/?sub=deleted&u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Deleted</a></li>';
        echo '<li><a href="/statement/?u='. $record[\Core\Model\ASD\SET_RECORD_ID] . '">Balance</a></li>';
        if (isset($record['suspended']) && $record['suspended']=='YES')
        {
            echo '<li><a href="/admin/?p='. $record[\Core\Model\ASD\SET_RECORD_ID] . '&a=-1">Release</a></li>';
        }
        if($record[\Core\Model\ASD\USER_LEVEL]==5)
        {
            echo '<li style="float:right"><a style="border-left:1px solid #CCC" href="?p='.$record[\Core\Model\ASD\SET_RECORD_ID].'&action=unblock">Unblock</a></li>';
        }
        else
        {
            echo '<li style="float:right"><a style="border-left:1px solid #CCC" onclick="block('.$record[\Core\Model\ASD\SET_RECORD_ID].',this)" href="javascript:void(0);">Block</a></li>';
            if ( !(isset($record['suspended']) && $record['suspended']=='YES'))
            {
                echo '<li style="float:right"><a style="border-left:1px solid #CCC" onclick="suspend('.$record[\Core\Model\ASD\SET_RECORD_ID].',this)" href="javascript:void(0);">Suspend</a></li>';
            }
        }
        echo '</ul>';
        echo '<div dir="ltr">';
        echo '<pre style="font-size:12pt;font-family:arial;line-height:18pt;">';
        echo json_encode($record, JSON_PRETTY_PRINT);
        echo '</pre></div>';
        ?><div id="susp_dialog" class="dialog"><?php
        ?><div class="dialog-box ctr"><select id="suspT" style="direction:ltr;width:200px"><option value="1">1 hour</option><option value="6">6 hours</option><option value="12">12 hours</option><option value="18">18 hours</option><option value="24">24 hours</option><option value="30">30 hours</option><option value="36">36 hours</option><option value="42">42 hours</option><option value="48">48 hours</option><option value="54">54 hours</option><option value="60">60 hours</option><option value="66">66 hours</option><option value="72">72 hours</option></select></div><?php
        ?><div class="dialog-action"><?php
        ?><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['suspend'] ?>" /><?php
        ?></div><?php
        ?></div><?php
    }

    
}
?>
