<?php
require_once 'Page.php';

class Account extends Page{
    
    var $action='',$liOpen='';

    function __construct($router){
        parent::__construct($router);
        $this->checkBlockedAccount(5);        
        $this->load_lang(array("account"));
        if($this->isMobile) {
            $this->inlineCss.='
.po .et{display:inline-block}
.pi .et{display:none}
.po .h .et{display:none}
.pi .h .et{display:inline-block}
.pi .h{background-color:#666}
.ls li.on{background-color:#FFF;color:#333}
.pi li{background-color:#FFFFBF}
.pi li.on{background-color:#FFFFBF!important}
.pi .cbx{display:none}
.uno .cbx{background-position:0px 0px}
.uno .on .cbx{background-position:0px -25px}
.btw{width:50%}
.bt{margin-top:10px !important;margin-bottom:10px !important}
.liw{background-color:#F7E741!important}
.act2,.nobd,input.bt{border:0!important}
form{height:auto!important;padding:0!important}
            ';
        }else{
            $this->set_require('css', 'account');
            $this->inlineCss.='.acc{width:660px;padding-left:0;padding-right:0;clear:none;display:inline-block}
                    .merge{float:'.($this->urlRouter->siteLanguage=='ar'?'left':'right').';text-align:center;padding-top:10px;}
                    .bt.scan{margin:15px 0 20px}
                    ';
            if(!$this->user->info['id']){
                $this->inlineCss.='.ph{width:650px}.acc{height:auto}';
            }
        }
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->forceNoIndex=true;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->urlRouter->cfg['enabled_ads']=0;
        $this->render();
    }
   
    function mainMobile(){
        if ($this->user->info['id']) {
            $language=$this->urlRouter->siteLanguage;
            if (isset($this->user->info['options']['lang']))$language=$this->user->info['options']['lang'];
            $nameAlert=( isset($this->user->info['name']) && $this->user->info['name'] ) ?false:true;
            $emailAlert=false;
            $emailMsg='';
            $email='';
            if (isset($this->user->info['options']['email'])) {
                $email=$this->user->info['options']['email'];
                if ($this->action=='verify') {
                    $emailMsg= '<i>'.$this->lang['emailFail'].'<b>'.$email.'</b></i>';                
                }else {
                    $emailMsg= '<ok>'. preg_replace('/{email}/', $email, $this->lang['emailSent']).'</ok>';
                }
                $email=$this->user->info['email'];
                $email=htmlspecialchars($email,ENT_QUOTES);
            }else {
                $emailAlert=( isset($this->user->info['email']) && $this->user->info['email'] ) ?false:true;
                if (!$emailAlert) {
                    $email=$this->user->info['email'];
                    $email=htmlspecialchars($email,ENT_QUOTES);
                }
            }
            $name='';
            $nameDir=$this->urlRouter->siteLanguage;
            if(!$nameAlert){
                $name=$this->user->info['name'];
                $name=htmlspecialchars($name,ENT_QUOTES);
                $nameDir=preg_match('/[\x{0621}-\x{064a}]/u', $name) ? 'ar' : 'en';
            }
            if(isset($this->user->pending['email_validation'])){
                if($this->user->pending['email_validation']==2){
                    if (isset($this->user->info['options']['emailKey'])) {
                        if($this->user->pending['email_key'] == $this->user->info['options']['emailKey']){
                            if ($this->user->emailVerified()){
                                $this->user->pending['email_validation']=1;
                                unset($this->user->pending['email_key']);
                            }
                        }else{
                            $this->user->pending['email_validation']=3;
                            unset($this->user->pending['email_key']);
                        }
                    }else{
                        $this->user->pending['email_validation']=3;
                        unset($this->user->pending['email_key']);
                    }
                }
                switch($this->user->pending['email_validation']){
                    case 1:
                        ?><ul class="ls po"><?php
                        ?><li><b class="ah"><span class="done"></span><ok><?php 
                        echo $this->lang['emailVerificationMOk'];
                        ?></ok></b></li><?php 
                        ?></ul><?php
                        $emailMsg='';
                        $email=$this->user->info['email'];
                        unset($this->user->pending['email_validation']);
                        break;
                    case 3:
                        ?><ul class="ls po"><?php
                        ?><li><b class="ah err"><span class="fail"></span><?php 
                        echo $this->lang['emailVerificationNo'];
                        ?></b></li><?php 
                        ?></ul><?php
                        unset($this->user->pending['email_validation']);
                        break;
                    case 2:
                    case 0:
                        ?><ul class="ls po"><?php
                        ?><li><b class="ah err"><span class="fail"></span><?php 
                        echo $this->lang['emailVerificationSno'];
                        ?></b></li><?php 
                        ?></ul><?php
                        break;
                }
                $this->user->update();
            }
            /*
            if (isset($_GET['action'])) $this->action=$this->get('action', 'filter');
            $this->action='verify';
            switch ($this->action){
                case 'verify':
                    if (isset($this->user->info['options']['emailKey'])) {
                        if ((isset($_GET['key']) && $_GET['key']==$this->user->info['options']['emailKey'])){
                            if ($this->user->emailVerified()) {
                                ?><ul class="ls po"><?php
                                ?><li><b class="ah"><span class="done"></span><ok><?php 
                                echo $this->lang['emailVerificationMOk'];
                                ?></ok></b></li><?php 
                                ?></ul><?php
                                $emailMsg='';
                                $email=$this->user->info['email'];
                            }else {
                                ?><ul class="ls po"><?php
                                ?><li><b class="ah err"><span class="fail"></span><?php 
                                echo $this->lang['emailVerificationSno'];
                                ?></b></li><?php 
                                ?></ul><?php
                            }
                        }else {
                            ?><ul class="ls po"><?php
                            ?><li><b class="ah err"><span class="fail"></span><?php 
                            echo $this->lang['emailVerificationNo'];
                            ?></b></li><?php 
                            ?></ul><?php 
                        }
                    }
                    break;
                default:
                    break;
            }
             * */
            $this->globalScript.='var ulg="'.$language.'",uname="'.$name.'",uemail="'.$email.'";';
            ?><ul class="ls po uno pi"><?php
                ?><li onclick="elg(this)" class="button h"><b><?= $this->lang['language'] ?><span class="et"></span></b></li><?php 
                ?><li onclick="elg(this)" class="button <?= ($language=='ar' ? 'on':'hid') ?>" val="ar"><b>العربية<span class="cbx"></span></b></li><?php
                ?><li onclick="elg(this)" class="button <?= ($language=='en' ? 'on':'hid') ?>" val="en"><b>English<span class="cbx"></span></b></li><?php
            ?></ul><?php
            ?><ul class="ls po pi"><?php
                ?><li onclick="enm(this)" class="button h"><b><?= $this->lang['name'] ?><span class="et"></span></b></li><?php 
                ?><li onclick="enm(this)" class="button <?= $nameDir ?>"><b><?= $nameAlert ? '<span class="fail"></span><i>'.$this->lang['missingName'].'</i>':$name ?></b></li><?php
                ?><li class="nobd hid"><ul><?php
                    ?><li><div class="ipt"><input type="text" name="name" class="<?= $nameDir ?>" onfocus="initB(this)" onkeyup="capk(this)" onchange="capk(this)" /></div></li><?php
                    ?><li class="liw hid"></li><?php 
                    ?><li><b class="ah ctr act2"><span onclick="savN(this)" class="button bt ok off"><?= $this->lang['save'] ?></span><span onclick="clF(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                ?></ul></li><?php
            ?></ul><?php
            ?><ul class="ls po pi"><?php
                ?><li <?= ($this->user->info['provider']!='mourjan' ? 'onclick="enm(this)" ':'') ?>class="button h"><b><?= $this->lang['email'] ?><?= ($this->user->info['provider']!='mourjan' ? '<span class="et"></span>':'') ?></b></li><?php 
                ?><li <?= ($this->user->info['provider']!='mourjan' ? 'onclick="enm(this)" ':'') ?>class="button <?= $emailAlert || $emailMsg ? $this->urlRouter->siteLanguage : 'en' ?>"><b class="ah"><?= $emailAlert ? '<span class="fail"></span><i>'.$this->lang['missingEmail'].'</i>':($emailMsg ? $emailMsg : $email) ?></b></li><?php
                if($this->user->info['provider']!='mourjan'){
                    ?><li class="nobd hid"><ul><?php
                        ?><li><div class="ipt"><input type="text" name="email" class="en" onfocus="initB(this)" onkeyup="capk(this)" onchange="capk(this)" /></div></li><?php
                        ?><li class="liw hid"></li><?php 
                        ?><li><b class="ah ctr act2"><span onclick="savM(this)" class="button bt ok off"><?= $this->lang['save'] ?></span><span onclick="clF(this)" class="button bt cl"><?= $this->lang['cancel'] ?></span></b></li><?php
                    ?></ul></li><?php
                }
            ?></ul><?php
            if (!$emailAlert) { 
                ?><ul class="ls po ck"><?php
                $notifications=array('ads'=>1,'coms'=>1,'news'=>1,'third'=>1);
                if (isset($this->user->info['options']['nb']) && is_array($this->user->info['options']['nb'])) $notifications=  array_merge ($notifications,$this->user->info['options']['nb']);
                    ?><li onclick="enm(this)" class="button h"><b><?= $this->lang['notifications'] ?></b></li><?php 
                    ?><li onclick="ckO(this)" class="button <?= isset($notifications['ads']) && $notifications['ads'] ? 'on':'' ?>"><b class="ah"><?= $this->lang['nb_ads'] ?><span class="cbx"></span></b></li><?php
                    ?><li onclick="ckO(this)" class="button <?= isset($notifications['coms']) && $notifications['coms'] ? 'on':'' ?>"><b class="ah"><?= $this->lang['nb_comments'] ?><span class="cbx"></span></b></li><?php
                    ?><li onclick="ckO(this)" class="button <?=  isset($notifications['news']) && $notifications['news'] ? 'on':'' ?>"><b class="ah"><?= $this->lang['nb_news'] ?><span class="cbx"></span></b></li><?php
                    /* ?><li onclick="ckO(this)"<?= $notifications['third'] ? ' class="on"':'' ?>><b class="ah"><?= $this->lang['nb_third'] ?><span class="cbx"></span></b></li><?php */
                ?></ul><?php
            }
            ?><br /><?php
        }
    }
/*
    function side_pane(){
        $this->renderSideUserPanel();
    }
*/
    function main_pane(){
        if ($this->user->info['id']) {
        //$this->pageHeader();
        $this->generalList();
        }else{
            if(isset($this->user->pending['email_validation']) && $this->user->pending['email_validation']==2){
                $this->lang['hint_login']=$this->lang['login_email_verify'];
            }
            $this->renderLoginPage();
        }
    }

    function pageHeader(){
        ?><div class='sum rc'><div class="brd"><?php
        //echo "<a href='{$this->urlRouter->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1><?= $this->lang['title'] ?></h1></div><?= $this->lang['header'] ?></div><?php 
        
        
    }
    
    function generalList(){
        $language=$this->urlRouter->siteLanguage;
        if (isset($this->user->info['options']['lang']))$language=$this->user->info['options']['lang'];
        
        
        if (isset($_GET['action'])) $this->action=$this->get('action', 'filter');
        switch ($this->action){
            case 'notifications':
                $this->liOpen='notifications';
                break;
            case 'email':
                if($this->user->info['provider']!='mourjan')
                    $this->liOpen='email';
                break;
            /*
            case 'verify':
                if (isset($this->user->info['options']['emailKey'])) {
                    if (isset($_GET['key']) && $_GET['key']==$this->user->info['options']['emailKey']){
                        if ($this->user->emailVerified()) {
                            ?><div class="ph pho"><span class="done"></span><?php 
                                echo $this->lang['emailVerificationOk'];
                            ?></div><?php 
                            $this->liOpen='notifications';
                        }else {
                            ?><div class="ph phe"><span class="fail"></span><?php 
                                echo $this->lang['emailVerificationSno'];
                            ?></div><?php
                        }
                    }else {
                        ?><div class="ph phe"><span class="fail"></span><?php 
                            echo $this->lang['emailVerificationNo'];
                        ?></div><?php
                    }
                }
                break;
             * 
             */
            default:
                break;
        }
        if(isset($this->user->pending['email_validation'])){
            $this->action='verify';
            if($this->user->pending['email_validation']==2){
                    if (isset($this->user->info['options']['emailKey'])) {
                        if($this->user->pending['email_key'] == $this->user->info['options']['emailKey']){
                            if ($this->user->emailVerified()){
                                $this->user->pending['email_validation']=1;
                                unset($this->user->pending['email_key']);
                            }
                        }else{
                            $this->user->pending['email_validation']=3;
                            unset($this->user->pending['email_key']);
                        }
                    }else{
                        $this->user->pending['email_validation']=3;
                        unset($this->user->pending['email_key']);
                    }
            }
            switch($this->user->pending['email_validation']){
                    case 1:
                        ?><div class="ph pho"><span class="done"></span><?php 
                        echo $this->lang['emailVerificationOk'];
                        ?></div><?php 
                        $this->liOpen='notifications';
                        unset($this->user->pending['email_validation']);
                        break;
                    case 3:
                        ?><div class="ph phe"><span class="fail"></span><?php 
                        echo $this->lang['emailVerificationNo'];
                        ?></div><?php
                        unset($this->user->pending['email_validation']);
                        break;
                    case 2:
                    case 0:
                        ?><div class="ph phe"><span class="fail"></span><?php 
                        echo $this->lang['emailVerificationSno'];
                        ?></div><?php
                        break;
            }
            $this->user->update();
        }
        
        $nameAlert=( isset($this->user->info['name']) && $this->user->info['name'] ) ?false:true;
        $emailAlert=false;
        if (isset($this->user->info['options']['email'])) {
            $email=$this->user->info['options']['email'];
            if ($this->action=='verify') {
                $emailMsg= '<i>'.$this->lang['emailFail'].'<b>'.$email.'</b></i>';                
            }else {
                $emailMsg= '<ok>'. preg_replace('/{email}/', $email, $this->lang['emailSent']).'</ok>';
            }
            $email=$this->user->info['email'];
        }else {
            $emailAlert=( isset($this->user->info['email']) && $this->user->info['email'] ) ?false:true;
            if (!$emailAlert) {
                $emailMsg=$email=$this->user->info['email'];
            }
        }
        $actionDiv='<div class="am"><input class="bt" type="submit" value="'.$this->lang['saveChanges'].'" /><input class="bt cl" type="reset" onclick="clsOpen()" value="'.$this->lang['cancel'].'" /><span class="notice"></span></div>';
        if (!$this->liOpen) echo $actionDiv;
        ?><div class="acc"><?php
        ?><form onsubmit="save();return false"><?php
        ?><ul class="ts"><?php
            ?><li id="lang"><?php 
                ?><div class="lm"><label><?= $this->lang['language'] ?></label><div class="info" label="lang"><?= $language=='ar' ? 'العربية':'English' ?></div><span class="lnk edit"><?= $this->lang['edit'] ?></span></div><?php
                ?><div class="fm"><label><?= $this->lang['language'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><select name="lang"><option<?= $language=='ar'?' selected':'' ?> value="ar">العربية</option><option<?= $language=='en'?' selected':'' ?> value="en">English</option></select></p><?php
                    ?></div><?php                    
                ?></div><?php
            ?></li><?php
            ?><li id="name"><?php 
                ?><div class="lm"><label><?= $this->lang['name'] ?></label><div class="info" label="name"><?= $nameAlert ? '<i><span class="fail"></span>'.$this->lang['missingName'].'</i>':$this->user->info['name'] ?></div><span class="lnk edit"><?= $this->lang['edit'] ?></span></div><?php
                ?><div class="fm"><label><?= $this->lang['name'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><input name="name" onkeydown="idir(this)" onchange="idir(this,1)" minLength="2" maxlength="128" type="text" value="<?= $nameAlert ? '': $this->user->info['name'] ?>" placeholder="<?= $this->lang['specifyName'] ?>" regexMatch="false" regex="[0-9]|[\,\.\'\{}\[\]\@\#\$\%\^\&\*\-\_\+\=\(\)\~\`\?\/\\]" vErr="<?= $this->lang['validName'] ?>" yErr="<?= $this->lang['missingNameLength'] ?>" req="1"/></p><?php
                    ?></div><?php                    
                ?></div><?php
            ?></li><?php
            $tclass= $this->liOpen=='email' ? 'open':'';
            ?><li id="email"<?= $tclass?' class="'.$tclass.'"':'' ?>><?php
                ?><div class="lm"<?= $this->liOpen=='email'?' style="display:none"':'' ?>><label><?= $this->lang['email'] ?></label><div class="info" label="email"><?= $emailAlert ? '<i><span class="fail"></span>'.$this->lang['missingEmail'].'</i>':$emailMsg ?></div><?= ($this->user->info['provider']!='mourjan' ? ('<span class="lnk edit" title="'. ($emailAlert ? $this->lang['editEmailAlert']:$this->lang['editEmail']) .'">'.$this->lang['edit'].'</span>') : '' ) ?></div><?php
                if($this->user->info['provider']!='mourjan'){
                    ?><div class="fm"<?= $this->liOpen=='email'?' style="display:block"':'' ?>><label><?= $this->lang['email'] ?></label><?php
                        ?><div class="bm"><?php
                        ?><p><input name="email" class="en" maxlength="128" type="text" value="<?= $emailAlert ? '': $email ?>" placeholder="<?= $this->lang['specifyEmail'] ?>" regexMatch="true" regex="email" yErr="<?= $this->lang['validEmail'] ?>" req="1"/></p><?php
                        ?></div><?php   
                        if ($this->liOpen=='email') echo $actionDiv;
                    ?></div><?php
                }
            ?></li><?php
            if (!$emailAlert) { 
                $notifications=array('ads'=>1,'coms'=>1, 'news'=>1,'third'=>1);
                if (isset($this->user->info['options']['nb']) && is_array($this->user->info['options']['nb'])) $notifications = array_merge($notifications,$this->user->info['options']['nb']);
                ?><li id="notifications"<?= $this->liOpen=='notifications'?' class="open"':'' ?>><?php
                    ?><div class="lm"<?= $this->liOpen=='notifications'?' style="display:none"':'' ?>><label><?= $this->lang['notifications'] ?></label><span class="lnk edit"><?= $this->lang['edit'] ?></span></div><?php
                    ?><div class="fm"<?= $this->liOpen=='notifications'?' style="display:block"':'' ?>><label><?= $this->lang['notifications'] ?></label><?php
                        ?><div class="bm"><?php
                        ?><p><input type="checkbox" name="ads" <?= $notifications['ads']?'checked="checked" ':'' ?>/><?= $this->lang['nb_ads'] ?></p><?php
                        ?><p><input type="checkbox" name="coms" <?= $notifications['coms']?'checked="checked" ':'' ?>/><?= $this->lang['nb_comments'] ?></p><?php
                        ?><p><input type="checkbox" name="news" <?= $notifications['news']?'checked="checked" ':'' ?>/><?= $this->lang['nb_news'] ?></p><?php
                        /* ?><p><input type="checkbox" name="third" <?= $notifications['third']?'checked="checked" ':'' ?>/><?= $this->lang['nb_third'] ?></p><?php */
                        ?></div><?php  
                        if ($this->liOpen=='notifications') echo $actionDiv;
                    ?></div><?php
                ?></li><?php             
            }
        ?></ul><?php 
        ?></form><?php
        include_once $this->urlRouter->cfg['dir']. '/core/lib/phpqrcode.php';
        $qrfile = $this->urlRouter->cfg['dir']."/web/qr/m-".  session_id() . ".png";
        QRcode::png("mourjan:merge:".  session_id() . str_pad($this->urlRouter->cfg['server_id'],4,'0', STR_PAD_LEFT) . str_pad(time(),16,'0', STR_PAD_LEFT), $qrfile, QR_ECLEVEL_L, 5 );
        $redis = new Redis();
        $redis->connect($this->urlRouter->cfg['rs-host'], $this->urlRouter->cfg['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
        $redis->setOption(Redis::OPT_PREFIX, 'SESS:');
        $redis->select(1);
        $redis->setex('m-'.session_id(), 300, $this->urlRouter->cfg['server_id'].':1:'.$this->user->info['id']);
        $redis->close();
            //error_log(var_export($this->user->info, TRUE));
        
        ?></div><?php 
        
        echo "<div class='merge'>";
        echo "<img width=185 height=185 src='{$this->urlRouter->cfg['host']}/web/qr/m-".session_id().".png'></img>";
        echo '<br /><span class="bt scan"><span class="apple"></span><span class="apple up"></span> '.$this->lang['hint_merge_Account'].' <span class="apple up"></span><span class="apple"></span></span>';                    
        echo '</div>';
        $this->globalScript.='var curForm="'.$this->liOpen.'";';
        //$this->inlineScript.='actBx=$(".am");$(".lnk.edit").click(function(){clsOpen();e=$(this);var p=e.parent();var li=p.parent();curForm=li.attr("id");p.css("display","none");p.next().css("display","block");p.next().append(actBx);li.addClass("open");});$("[name]").focus(function(i,e){var n=$(this).next();if(n.length)n.remove();});';
        //$this->globalScript.='var curForm="'.$this->liOpen.'",actBx;var clsOpen=function(){var li=$("ul.ts li.open");if (li.length){$(".fm",li).css("display","none");$(".lm",li).css("display","block");li.removeClass("open");$("[name]",$("#"+curForm)).each(function(i,e){var n=$(e).next();if(n.length) n.remove();});var nb=$(".notice",actBx);nb.html("");nb.removeClass("err");}};var save=function(){var nb=$(".notice",actBx);nb.html("");nb.removeClass("err");nb.addClass("loading");var f=$("#"+curForm);var d=$("[name]",f);if (validate(d)) {var vals={};try{d.each(function(i,e){e=$(e);if (e.attr("type")=="checkbox"){vals[e.attr("name")]=e.attr("checked");}else {vals[e.attr("name")]=e.attr("value");}});$.ajax({type:"POST",url:"/ajax-account/",cache:false,data:{form:curForm,fields:vals,lang:"'.$this->urlRouter->siteLanguage.'"},dataType:"json",success:function(rp){d.each(function(i,e){$(e).attr("disabled",false)});$("input",actBx).attr("disabled",false);if (rp.RP) {if(rp.DATA.fields){var es=rp.DATA.fields;var p=$(".lm",d.parents("li"));d.each(function(i,e){e=$(e);if (es[e.attr("name")]){e=$(e);var v=es[e.attr("name")];var c=$(\'[label="\'+e.attr("name")+\'"]\',p);if (c.length){c.html(v[2] ? v[2] : v[1]);}if (v[0]=="checked") {if (v[1]=="checked"){e.attr("checked","checked");}else {e.attr("checked",false);}var h=e.parent().html();}else {e.attr(v[0],v[1]);}var h=e.parent().html();e.replaceWith($(h));}});clsOpen();}}else {if (isNaN(rp.MSG)){nb.addClass("err");nb.html(rp.MSG);}if(rp.DATA.fields){var es=rp.DATA.fields;d.each(function(i,e){e=$(e);if (es[e.attr("name")]){setFN(e,es[e.attr("name")]);}});}}nb.removeClass("loading");},error:function(){nb.removeClass("loading");nb.addClass("err");nb.html("'.$this->lang['systemErr'].'");d.each(function(i,e){$(e).attr("disabled",false)});$("input",actBx).attr("disabled",false);}});}catch(e){console.log(e)}}else {nb.removeClass("loading");d.each(function(i,e){$(e).attr("disabled",false)});$("input",actBx).attr("disabled",false);}return false;};var setFN=function(e,msg){var s=$("<span class=\'notice alert err inv\'>"+msg+"</span>");$(e).after(s);if(s.height()<20) {s.css("margin-top","8px");}s.removeClass("inv");};var validate=function(d){var r=true;d.each(function(i,e){var ps=false,em="";e=$(e);var n=e.next();if(n.length) n.remove();var v=e.attr("value");var min=e.attr("minLength") ? parseInt(e.attr("minLength")):0;if(e.attr("req") && (v.length==0 || v.length<min)) ps=true;em=e.attr("yErr");if (!ps){var re=e.attr("regex");if (re){try{re=new RegExp(re);var o=e.attr("regexMatch")=="true"?true:false;var f=re.exec(v);if (f == null) {if (o) ps=true;}else {if(!o)ps=true;}if(ps){var tm=e.attr("vErr");if(tm)em=tm;}}catch(x){console.log(x)}}}if (ps){r=false;setFN(e,em);}});if (r){d.each(function(i,e){$(e).attr("disabled",true)});$("input",actBx).attr("disabled",true);}return r;};';
    }
    
}
?>
