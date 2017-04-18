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
                . '.load{width: 30px;height: 30px;display: inline-block;vertical-align: middle}';
        
        $this->set_require('css', 'account');
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->forceNoIndex=true;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->urlRouter->cfg['enabled_ads']=0;
        $parameter = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);
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
                $uids = \Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($parameter);
                foreach ($uids as $bins) 
                {
                    $this->userdata[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
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
            $bins[Core\Model\ASD\USER_LAST_AD_RENEWED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_LAST_AD_RENEWED]);
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
                if (isset($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS]) && $_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS][0]!='{')
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
        if(0 && $record[\Core\Model\ASD\USER_LEVEL]==5)
        {
            echo '<li style="float:right"><a style="border-left:1px solid #CCC" onclick="unblock('.$record[\Core\Model\ASD\SET_RECORD_ID].',this)" href="javascript:void(0);">Unblock</a></li>';
        }
        else
        {
            echo '<li style="float:right"><a style="border-left:1px solid #CCC" onclick="block('.$record[\Core\Model\ASD\SET_RECORD_ID].',this)" href="javascript:void(0);">Block</a></li>';
        }
        echo '</ul>';
        echo '<div dir="ltr">';
        echo '<pre style="font-size:12pt;font-family:arial;line-height:18pt;">';
        echo json_encode($record, JSON_PRETTY_PRINT);
        echo '</pre></div>';
    }

    
}
?>
