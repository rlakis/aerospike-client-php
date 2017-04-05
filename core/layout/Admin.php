<?php
require_once 'Page.php';

class Admin extends Page
{
    
    var $action='',$liOpen='';
    private $uid = 0;
    private $userdata = 0;
    
    function __construct($router)
    {
        parent::__construct($router);
        
        if($this->isMobile || !$this->user->isSuperUser())
        {
            $this->user->redirectTo('/notfound/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }     
        
        $this->load_lang(array("account"));
        
        $this->inlineCss .= 
                '.ts .bt{width:auto;padding:5px 30px!important}'
                . '.ts .lm{overflow:visible}'
                . '.hy li{float:right;width:475px;border:0!important}'
                . '.hy label{margin-bottom:10px}'
                . 'textarea{width:300px;height:200px;padding:3px}'
                . '.action{width:800px!important;text-align:center}'
                . '.options{position:absolute;border:1px solid #aaa;border-bottom:0;width:306px;background-color:#FFF}'
                . '.options li{cursor:pointer;border-bottom:1px solid #aaa;direction:rtl;text-align:right;padding:10px;}'
                . '.options li:hover{background-color:#00e;color:#FFF}'
                . '#msg{height:40px;display:block}'
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

            $this->userdata = \Core\Model\NoSQL::getInstance()->fetchUser($this->uid);
            $release = intval(filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT));
        
            $this->userdata[Core\Model\ASD\USER_DATE_ADDED] = $this->unixTimestampToDateTime($this->userdata[Core\Model\ASD\USER_DATE_ADDED]);
            $this->userdata[Core\Model\ASD\USER_LAST_VISITED] = $this->unixTimestampToDateTime($this->userdata[Core\Model\ASD\USER_LAST_VISITED]);
            $this->userdata[Core\Model\ASD\USER_PRIOR_VISITED] = $this->unixTimestampToDateTime($this->userdata[Core\Model\ASD\USER_PRIOR_VISITED]);
            $this->userdata[Core\Model\ASD\USER_LAST_AD_RENEWED] = $this->unixTimestampToDateTime($this->userdata[Core\Model\ASD\USER_LAST_AD_RENEWED]);
            $this->userdata[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS] = $this->unixTimestampToDateTime($this->userdata[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS]);

            unset($this->userdata['mobile']);
            $_mobiles = \Core\Model\NoSQL::getInstance()->mobileFetchByUID($this->uid);
            $_devices = \Core\Model\NoSQL::getInstance()->getUserDevices($this->uid);
            
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
                        MCSessionHandler::setSuspendMobile($this->uid, $_mobiles[$i][Core\Model\ASD\USER_MOBILE_NUMBER], 60, TRUE);
                        $_mobiles[$i]['suspended']['realease']='within 60 seconds';
                        $this->userdata['suspended']='60s';
                    }
                    else 
                    {
                        $_mobiles[$i]['suspended']['till'] = gmdate("Y-m-d H:i:s T", time()+$ttl); 
                        $_mobiles[$i]['suspended']['reason'] = strpos($reason, ':') ? trim(substr($reason, strpos($reason, ':')+1)) : $reason;      
                        $this->userdata['suspended']='YES';
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
            
            $this->userdata['mobiles'] = $_mobiles;
            $this->userdata['devices'] = $_devices;
            if (isset($this->userdata['password']))
            {
                unset($this->userdata['password']);
            }
            if (isset($this->userdata['jwt']))
            {
                unset($this->userdata['jwt']);
            }
            
        }
        
        $this->render();
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
        
        ?><div><?php
        if ($this->userdata)
        {
            echo '<div dir="ltr">';
            echo '<ul>';
            echo '<li style="float:left;width:80px;"><a href="/myads/?sub=drafts&u='. $this->userdata[\Core\Model\ASD\SET_RECORD_ID] . '">Drafts</a></li>';
            echo '<li style="float:left;width:80px;"><a href="/myads/?sub=pending&u='. $this->userdata[\Core\Model\ASD\SET_RECORD_ID] . '">Pending</a></li>';
            echo '<li style="float:left;width:80px;"><a href="/myads/?u='. $this->userdata[\Core\Model\ASD\SET_RECORD_ID] . '">Active</a></li>';
            echo '<li style="float:left;width:80px;"><a href="/myads/?sub=archive&u='. $this->userdata[\Core\Model\ASD\SET_RECORD_ID] . '">Archived</a></li>';
            if (isset($this->userdata['suspended']) && $this->userdata['suspended']=='YES')
            {
                echo '<li style="float:left;width:80px;"><a href="/admin/?p='. $this->userdata[\Core\Model\ASD\SET_RECORD_ID] . '&a=-1">Release</a></li>';
            }
            echo '</ul><br/>';
            echo '<pre style="font-size:12pt;font-family:arial;line-height:18pt;">';
            echo json_encode($this->userdata, JSON_PRETTY_PRINT);
            echo '</pre></div></div>';
            return;
        }
        ?><ul class="ts"><?php
            ?><li><?php 
            ?><div class="lm"><label><?= $this->lang['country'] ?></label><select onchange="CC()" id="country"><?php 
                foreach($this->urlRouter->countries as $country){
                    echo "<option value=".  strtoupper($country['uri']).">{$country['name']}</option>";
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
    }

    
}
?>
