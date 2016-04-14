<?php
require_once 'Page.php';

class Profile extends Page{
    
    var $action='',$liOpen='';

    function Profile($router){
        parent::Page($router);
        $this->checkBlockedAccount();      
        $this->load_lang(array("account","profile"));  
        $this->set_require('css', 'account');
        if (isset ($this->user->params['visit'])){
            $this->cssImgsLoaded=true;
            $this->set_require('css', array('imgs'));
        }
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->forceNoIndex=true;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->render();
    }
   

    function side_pane(){
        if ($this->user->info['id']) {
            $this->renderSideUserPanel();
        }else {
            $this->renderSideSite();
        }
    }

    function main_pane(){
        if ($this->user->info['id']) {
            if (in_array($this->user->info['level'], array(1,2,3,9)) ) {
                $this->pageHeader();
                $this->generalList();
            }else{
                $this->renderDisabledPage();
            }
        }else {
                $this->renderLoginPage();
        }
    }

    function pageHeader(){
        ?><div class='sum rc'><div class="brd"><?php
        //echo "<a href='{$this->urlRouter->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1><?= $this->lang['title'] ?></h1></div><?= $this->lang['header'] ?></div><?php 
        
        if (isset($_GET['action'])) $this->action=$this->get('action', 'filter');
        switch ($this->action){
            case 'verify':
                if (isset($this->user->info['options']['bmailKey'])) {
                    if (isset($_GET['key']) && $_GET['key']==$this->user->info['options']['bmailKey']){
                        if ($this->user->pageEmailVerified()) { 
                            ?><div class="sum rc na ok"><p class="iok"><?php 
                                echo $this->lang['bmailVerificationOk'];
                            ?></span></div><?php 
                        }else {
                            
                            ?><div class="sum rc nb"><p class="ino"><?php 
                                echo $this->lang['emailVerificationSno'];
                            ?></span></div><?php
                        }
                    }else {
                        ?><div class="sum rc nb"><p class="ino"><?php 
                            echo $this->lang['emailVerificationNo'];
                        ?></span></div><?php
                    }
                }
                break;
            default:
                break;
        }
        if (isset($_GET['edit'])) $this->action=$this->get('edit', 'filter');
        switch ($this->action){
            case 'website':
                $this->liOpen='website';
                break;
            case 'desc':
                $this->liOpen='desc';
                break;
            case 'contact':
                $this->liOpen='contact';
                break;
            case 'address':
                $this->liOpen='address';
                break;
            case 'email':
                $this->liOpen='email';
                break;
            case 'title':
                $this->liOpen='title';
                break;
            case 'links':
                $this->liOpen='links';
                break;
            default:
                break;
        }
        
    }
    
    function generalList(){
        $titleAlert=false;
        $titleAlertEn=true;
        $titleAlertAr=true;
        $addressEn='';
        $descEn='';
        $addressAr='';
        $descAr='';
        
        $website='';
        $numbers='';
        $social=array();
        if (isset($this->user->info['options']['page'])) {
            $data=$this->user->info['options']['page'];
            if (isset($data['t'])){
                $titleAlertEn=!(isset($data['t']['en']) && $data['t']['en']) ;
                $titleAlertAr=!(isset($data['t']['ar']) && $data['t']['ar']) ;
            }
            if (isset($data['adrEn']))
                $addressEn=isset($data['adrEn']) ? preg_replace('/<br \/>/u','',$data['adrEn']) : '' ;
            if (isset($data['adrAr']))
                $addressAr=isset($data['adrAr']) ?  preg_replace('/<br \/>/u','',$data['adrAr']) : '' ;
            
            if (isset($data['descEn']))
                $descEn=isset($data['descEn']) ? preg_replace('/<br \/>/u','',$data['descEn']) : '' ;
            if (isset($data['descAr']))
                $descAr=isset($data['descAr']) ?  preg_replace('/<br \/>/u','',$data['descAr']) : '' ;
            
            if (isset($data['url']))
                $website=$data['url'];
            
            if (isset($data['contact'])){
                $numbers=$this->user->formatContactNumbers($data['contact'],$this->lang);
            }
            
            if (isset($data['links'])){
                $social=$data['links'];
            }
        }
        /****Title Start****/
        $pageTitle='';
        if ($this->urlRouter->siteLanguage=='ar'){
            if (!$titleAlertAr)
                $pageTitle.=$data['t']['ar'];
            if (!$titleAlertEn && $pageTitle!=$data['t']['en']){
                if ($pageTitle) $pageTitle.=' - ';
                $pageTitle.=$data['t']['en'];
            }
        }else {            
            if (!$titleAlertEn)
                $pageTitle.=$data['t']['en'];
            if (!$titleAlertAr && $pageTitle!=$data['t']['ar']){
                if ($pageTitle) $pageTitle.=' - ';
                $pageTitle.=$data['t']['ar'];
            }
        }
        $titleAlert=$pageTitle ? false : true;
        /*******Title End********/
        /****Address Start****/
        /*$pageAddress='';
        if ($this->urlRouter->siteLanguage=='ar'){
            if ($addressAr)
                $pageAddress.=$data['a']['ar'];
            if ($addressEn && $pageAddress!=$addressEn){
                if ($pageAddress) $pageAddress.=' - ';
                $pageAddress.=$addressEn;
            }
        }else {            
            if ($addressEn)
                $pageAddress.=$data['a']['en'];
            if ($addressAr && $pageAddress!=$addressAr){
                if ($pageAddress) $pageAddress.=' - ';
                $pageAddress.=$addressAr;
            }
        }*/
        //$addressAlert=$pageAddress ? false : true;
        /*******Address End********/
        $emailAlert=false;
        $email='';
        $emailMsg='';
        if (isset($this->user->info['options']['bmail'])) {
            $email=$this->user->info['options']['bmail'];
            if ($this->action=='verify') {
                $emailMsg= '<i>'.$this->lang['emailFail'].'<b>'.$email.'</b></i>';                
            }else {
                $emailMsg= '<ok class="'.$this->urlRouter->siteLanguage.'">'. preg_replace('/{email}/', $email, $this->lang['emailSent']).'</ok>';
            }
        }else {
            $emailAlert=( isset($this->user->info['options']['page']['email']) && $this->user->info['options']['page']['email'] ) ?false:true;
            if (!$emailAlert) {
                $emailMsg=$email=$this->user->info['options']['page']['email'];
            }
        }
        /****Social Start****/
        $socialString='';
        if (isset($this->user->info['options']['page']['links']) && is_array($this->user->info['options']['page']['links'])) {
            
            foreach ($this->user->info['options']['page']['links'] as $id => $value){
                if ($id && $value){
                    switch($id){
                        case 'fb':
                            if ($socialString) $socialString.=' - ';
                            $socialString.="<a target='blank' href='http://www.facebook.com/".$value."'>Facebook</a>";
                            break;
                        case 'tw':
                            if ($socialString) $socialString.=' - ';
                            $socialString.="<a target='blank' href='http://twitter.com/".$value."'>Twitter</a>";
                            break;
                        case 'gp':
                            if ($socialString) $socialString.=' - ';
                            $socialString.="<a target='blank' href='http://plus.google.com/".$value."'>Google+</a>";
                            break;
                        case 'lk':
                            if ($socialString) $socialString.=' - ';
                            $socialString.="<a target='blank' href='http://www.linkedin.com/in/".$value."'>LinkedIn</a>";
                            break;                                        
                    }
                }
            }
            //if ($socialString) $socialString=  htmlentities ($socialString, ENT_QUOTES);
        }
        /****Social End****/
        $actionDiv='<div class="am"><input class="bt rc" type="submit" value="'.$this->lang['saveChanges'].'" /><input class="bt rc" type="reset" onclick="clsOpen()" value="'.$this->lang['cancel'].'" /><span class="notice"></span></div>';
        if (!$this->liOpen || $this->liOpen=='contact') echo $actionDiv;
        ?><ul class="ts"><?php
        /*
            ?><li id="logo"><?php 
                ?><div class="lm"><label><?= $this->lang['logo'] ?></label><div class="info" label="logo"></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"><form target="upload" id="picF" action="/ajax-logo/" enctype="multipart/form-data" method="post"><input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->urlRouter->cfg['max_upload'] ?>" /><?php 
                    ?><label><?= $this->lang['logo'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><input id="picB" name="pic" class="rc en upd" type="file" value="" placeholder="<?= $this->lang['specifyLogo'] ?>" /><?php 
                    ?><input type="button" onclick="clsOpen()" class="bt rc ccl cccl" value="<?= $this->lang['doneEdit'] ?>" /><?php
                    ?><input id="picU" class="bt bta rc hid" type="submit" value="<?= $this->lang['upload'] ?>" /><?php
                    ?></p><?php
                    ?><p class="lnb"><?= $this->lang['logoHint'] ?></p><?php
                    ?></div><?php 
                    ?></form><iframe class="hid" id="upload" name="upload" src="/web/blank.html"></iframe></div><?php
            ?></li><?php
                $this->inlineScript.='
                    $("#picB").change(function(){
                        var v=$(this).attr("value");
                        var p=$(this.parentNode).next();
                        resP(p);
                        if (v){
                            var i=v.lastIndexOf("\\\");
                            if (i>0) {v=v.substr(i+1)}
                            else {
                                i=v.lastIndexOf("/");
                                if (i>0) v=v.substr(i+1)
                            };
                            $("#picU",this.parentNode).removeClass("hid")
                        }else {
                            $("#picU",this.parentNode).addClass("hid")
                        };
                    });
                    $("#picU").click(function(){
                        if (cSize('.$this->urlRouter->cfg['max_upload'].',"#picB")){
                            var p =$(this.parentNode).next();
                            p.html("'.$this->lang['upload_wait'].'");
                            p.addClass("loading");
                            $(this).addClass("hid");
                            return true;
                        }
                        return false
                     });';
                $this->globalScript.='
                    var resP=function(p){
                        p.removeClass("notice err");
                        p.html("'.$this->lang['logoHint'].'");
                    };
                    var cSize=function(max,field){
                        var f=$(field)[0];
                        if(f.files && f.files.length == 1){
                            if (f.files[0].size > max){
                                alert("'.$this->lang['errFileSize'].' " + (max/1024/1024) + "MB");
                                return false;
                            }
                        }
                        return true
                    };
                    var gdel=function(e,path){
                        if(confirm("'.$this->lang['ask_gdel'].'")){
                            var s=$(e.parentNode);
                            p=$(e.parentNode.parentNode);
                            resP(p);
                            $.ajax({
                                type:"POST",
                                url:path,
                                dataType:"json",
                                success:function(rp){},
                                error:function(){}
                            });
                        }
                    }
                    var uploadCallback=function(fn,field){
                        var f=$(field);
                        f[0].reset();
                        var n=$(".lnb",f);
                        n.removeClass("loading");
                        if(fn) {
                            var s=$("<span class=\'img\'></span>");
                            if(field=="#picF")
                                var m=$("<img class=\'imgT\' src=\''.$this->urlRouter->cfg['url_resources'].'/usr/logo/"+fn+"\' />");
                            else var m=$("<img class=\'imgT\' src=\''.$this->urlRouter->cfg['url_resources'].'/usr/banner/"+fn+"\' />");
                            m.appendTo(s);
                            var x=$("<span title=\"'.$this->lang['delLogo'].'\"></span>");
                            x.click(function(e){
                                if(field=="#picF")
                                    gdel(e.target,"/ajax-gdel/");
                                else gdel(e.target,"/ajax-bdel/");
                            });
                            x.appendTo(s);
                            n.empty();
                            s.appendTo(n);
                        }else {
                            n.html("'.$this->lang['upload_fail'].'");
                                n.addClass("notice err");
                        }
                    };
                    ';
                
            //banner
            ?><li id="banner"><?php 
                ?><div class="lm"><label><?= $this->lang['banner'] ?></label><div class="info" label="banner"></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"><form target="upload" id="picT" action="/ajax-banner/" enctype="multipart/form-data" method="post"><input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->urlRouter->cfg['max_upload'] ?>" /><?php 
                    ?><label><?= $this->lang['banner'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><input id="picX" name="pic" class="rc en upd" type="file" value="" placeholder="<?= $this->lang['specifyLogo'] ?>" /><?php 
                    ?><input type="button" onclick="clsOpen()" class="bt rc ccl cccl" value="<?= $this->lang['doneEdit'] ?>" /><?php
                    ?><input id="picY" class="bt bta rc hid" type="submit" value="<?= $this->lang['upload'] ?>" /><?php
                    ?></p><?php
                    ?><p class="lnb"><?= $this->lang['logoHint'] ?></p><?php
                    ?></div><?php 
                    ?></form><iframe class="hid" id="uploadb" name="upload" src="/web/blank.html"></iframe></div><?php
            ?></li><?php
                $this->inlineScript.='
                    $("#picX").change(function(){
                        var v=$(this).attr("value");
                        var p=$(this.parentNode).next();
                        resP(p);
                        if (v){
                            var i=v.lastIndexOf("\\\");
                            if (i>0) {v=v.substr(i+1)}
                            else {
                                i=v.lastIndexOf("/");
                                if (i>0) v=v.substr(i+1)
                            };
                            $("#picY",this.parentNode).removeClass("hid")
                        }else {
                            $("#picY",this.parentNode).addClass("hid")
                        };
                    });
                    $("#picY").click(function(){
                        if (cSize('.$this->urlRouter->cfg['max_upload'].',"#picX")){
                            var p =$(this.parentNode).next();
                            p.html("'.$this->lang['upload_wait'].'");
                            p.addClass("loading");
                            $(this).addClass("hid");
                            return true;
                        }
                        return false
                     });';
            */
        ?><form onsubmit="save();return false"><?php 
            $tClass='bnot';
            $tClass=$titleAlert ? $tClass.' alert':$tClass;
            $tClass=($tClass ? $tClass.' ':'').($this->liOpen=='title'?'open':'');
            ?><li id="title"<?= $tClass?' class="'.$tClass.'"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='title'?' style="display:none"':'' ?>><label><?= $this->lang['pageTitle'] ?></label><div class="info" label="title"><?= $titleAlertEn && $titleAlertAr ? '<i>'.$this->lang['missingTitle'].'</i>':$pageTitle ?></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='title'?' style="display:block"':'' ?>><?php 
                if ($this->urlRouter->siteLanguage=='ar'){
                    ?><label><?= $this->lang['pageTitleAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="titleAr" class="rc ar" maxlength="128" type="text" value="<?= $titleAlertAr ? '': $data['t']['ar'] ?>" placeholder="<?= $this->lang['specifyTitleAr'] ?>" regexMatch="false" regex="[^\s0-9a-zA-Z.\-\u0621-\u0669]" vErr="<?= $this->lang['validTitle'] ?>" yErr="<?= $this->lang['missingTitleLength'] ?>" /></p><?php
                    ?></div><?php 
                    ?><hr /><input name="title" type="hidden" value="<?= $pageTitle ?>" /><?php                    
                    ?><label><?= $this->lang['pageTitleEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="titleEn" class="rc en" maxlength="128" type="text" value="<?= $titleAlertEn ? '': $data['t']['en'] ?>" placeholder="<?= $this->lang['specifyTitleEn'] ?>" regexMatch="false" regex="[^\s0-9a-zA-Z.\-\u0621-\u0669]" vErr="<?= $this->lang['validTitle'] ?>" yErr="<?= $this->lang['missingTitleLength'] ?>" /></p><?php
                    ?></div><?php  
                }else {
                    ?><label><?= $this->lang['pageTitleEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="titleEn" class="rc en" maxlength="128" type="text" value="<?= $titleAlertEn ? '': $data['t']['en'] ?>" placeholder="<?= $this->lang['specifyTitleEn'] ?>" regexMatch="false" regex="[^\s0-9a-zA-Z.\-\u0621-\u0669]" vErr="<?= $this->lang['validTitle'] ?>" yErr="<?= $this->lang['missingTitleLength'] ?>" /></p><?php
                    ?></div><?php 
                    ?><hr /><input name="title" type="hidden" value="<?= $pageTitle ?>" /><?php 
                    ?><label><?= $this->lang['pageTitleAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="titleAr" class="rc ar" maxlength="128" type="text" value="<?= $titleAlertAr ? '': $data['t']['ar'] ?>" placeholder="<?= $this->lang['specifyTitleAr'] ?>" regexMatch="false" regex="[^\s0-9a-zA-Z.\-\u0621-\u0669]" vErr="<?= $this->lang['validTitle'] ?>" yErr="<?= $this->lang['missingTitleLength'] ?>" /></p><?php
                    ?></div><?php 
                }
                if ($this->liOpen=='title') echo $actionDiv;
                ?></div><?php
            ?></li><?php
                        
            ?><li id="desc"<?= $this->liOpen=='desc'?' class="open"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='desc'?' style="display:none"':'' ?>><label><?= $this->lang['desc'] ?></label><div class="info" label="desc"></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='desc'?' style="display:block"':'' ?>><?php 
                if ($this->urlRouter->siteLanguage=='ar'){
                    ?><label><?= $this->lang['descAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="descAr" class="rc ar" maxlength="512"><?= $descAr  ?></textarea></p><?php
                    ?></div><?php 
                    ?><hr /><?php 
                    ?><label><?= $this->lang['descEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="descEn" class="rc en" maxlength="512"><?= $descEn  ?></textarea></p><?php
                    ?></div><?php 
                }else {
                    ?><label><?= $this->lang['descEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="descEn" class="rc en" maxlength="512"><?= $descEn  ?></textarea></p><?php
                    ?></div><?php 
                    ?><hr /><?php 
                    ?><label><?= $this->lang['descAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="descAr" class="rc ar" maxlength="512"><?= $descAr  ?></textarea></p><?php
                    ?></div><?php 
                }
                if ($this->liOpen=='desc') echo $actionDiv;
                ?></div><?php
            ?></li><?php 
            
            ?><li id="website"<?= $this->liOpen=='website'?' class="open"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='website'?' style="display:none"':'' ?>><label><?= $this->lang['website'] ?></label><div class="info en" label="website"><?= $website ?></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='website'?' style="display:block"':'' ?>><?php 
                    ?><label><?= $this->lang['website'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="website" maxlength="80" class="rc en" type="text" value="<?= $website ?>" placeholder="<?= $this->lang['specifyWebsite'] ?>" /></p><?php
                    ?></div><?php 
                    if ($this->liOpen=='website') echo $actionDiv;
                ?></div><?php
            ?></li><?php
            
           ?><li id="email"<?= $this->liOpen=='email'?' class="open"':'' ?>><?php
                ?><div class="lm"<?= $this->liOpen=='email'?' style="display:none"':'' ?>><label><?= $this->lang['email'] ?></label><div class="info en" label="email"><?= $emailAlert ? '':$emailMsg ?></div><div class="lnk edit" title="<?= $this->lang['editEmail'] ?>"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='email'?' style="display:block"':'' ?>><label><?= $this->lang['email'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><input name="email" class="rc en" maxlength="128" type="text" value="<?= $emailAlert ? '': $email ?>" placeholder="<?= $this->lang['specifyEmail'] ?>" regexMatch="true" regex="^$|[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)\b" yErr="<?= $this->lang['validEmail'] ?>" /></p><?php
                    ?></div><?php   
                    if ($this->liOpen=='email') echo $actionDiv;
                ?></div><?php
            ?></li><?php
            
            ?><li id="links"<?= $this->liOpen=='links'?' class="open"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='links'?' style="display:none"':'' ?>><label><?= $this->lang['social'] ?></label><div class="info en" label="social"><?= $socialString ?></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='links'?' style="display:block"':'' ?>><?php 
                    ?><label><?= $this->lang['social_gp'] ?></label><?php
                    ?><div class="bm sm en"><?php
                        ?><p>http://plus.google.com/ <input name="gp" maxlength="40" class="rc en" type="text" value="<?= isset($social['gp'])?$social['gp']:'' ?>" placeholder="<?= $this->lang['specifyGp'] ?>" /></p><?php
                    ?></div><?php 
                    ?><hr /><input name="social" type="hidden" value="<?= $socialString ?>" /><?php
                    ?><label><?= $this->lang['social_fb'] ?></label><?php
                    ?><div class="bm sm en"><?php
                        ?><p>http://www.facebook.com/ <input name="fb" maxlength="40" class="rc en" type="text" value="<?= isset($social['fb'])?$social['fb']:'' ?>" placeholder="<?= $this->lang['specifyFb'] ?>" /></p><?php
                    ?></div><?php 
                    ?><hr /><?php
                    ?><label><?= $this->lang['social_tw'] ?></label><?php
                    ?><div class="bm sm en"><?php
                        ?><p>http://twitter.com/ <input name="tw" maxlength="40" class="rc en" type="text" value="<?= isset($social['tw'])?$social['tw']:'' ?>" placeholder="<?= $this->lang['specifyTw'] ?>" /></p><?php
                    ?></div><?php
                    ?><hr /><?php
                    ?><label><?= $this->lang['social_lk'] ?></label><?php
                    ?><div class="bm sm en"><?php
                        ?><p>http://www.linkedin.com/in/ <input name="lk" maxlength="40" class="rc en" type="text" value="<?= isset($social['lk'])?$social['lk']:'' ?>" placeholder="<?= $this->lang['specifyLk'] ?>" /></p><?php
                    ?></div><?php
                    if ($this->liOpen=='links') echo $actionDiv;
                ?></div><?php
            ?></li><?php
            
            $this->countryCodes = $this->urlRouter->db->queryCacheResultSimpleArray(
                'country_codes',
                'select lower(trim(id_2)) URI, code
                from country
                order by 1',
        null, 0, $this->urlRouter->cfg['ttl_long']);
            $dCode='lb';
            
            ?><li id="contact"<?= $this->liOpen=='contact'?' class="open"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='contact'?' style="display:none"':'' ?>><label><?= $this->lang['contact'] ?></label><div class="info" label="website"></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='contact'?' style="display:block"':'' ?>><?php 
                    ?><label><?= $this->lang['contactAdd'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><?php 
                    ?><select class="rc en csel"><?php 
                    ?><option value="0"><?= $this->lang['labelP0'] ?></option><?php
                    ?><option value="1"><?= $this->lang['labelP1'] ?></option><?php
                    ?><option value="2"><?= $this->lang['labelP2'] ?></option><?php
                    ?></select><?php 
                    ?><select class="rc en csel"><?php 
                    foreach($this->countryCodes as $country){
                        $code=$country[0].'|+'.$country[1];
                            echo '<option value="'.$code.'"',($dCode==$country[0]?' selected="selected"':''),'>'.strtoupper($country[0]).' +'.$country[1].'</option>';
                        }
                    ?></select><?php
                    ?><input maxlength="12" class="fnum rc en" onfocus="if($){$(this).css('color','inherit')}" type="text" value="" placeholder="<?= $this->lang['pnum'] ?>" /><?php
                    ?><input type="button" onclick="clsOpen()" class="bt rc ccl" value="<?= $this->lang['doneEdit'] ?>" /><input type="button" onclick="addp(this)" class="bt rc" value="<?= $this->lang['add'] ?>" /><?php
                    ?></p></div><div class="bm cm"><?= $numbers ?></div><?php
                   /* ?><label><?= $this->lang['website'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><input name="website" maxlength="60" class="rc en" type="text" value="<?= $website ?>" placeholder="<?= $this->lang['specifyWebsite'] ?>" /></p><?php
                    ?></div><?php */
                ?></div><?php
            ?></li><?php            
            
            ?><li id="address"<?= $this->liOpen=='address'?' class="open"':'' ?>><?php 
                ?><div class="lm"<?= $this->liOpen=='address'?' style="display:none"':'' ?>><label><?= $this->lang['address'] ?></label><div class="info" label="address"></div><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"<?= $this->liOpen=='address'?' style="display:block"':'' ?>><?php 
                if ($this->urlRouter->siteLanguage=='ar'){
                    ?><label><?= $this->lang['addressAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="addressAr" class="rc ar" maxlength="512"><?= $addressAr  ?></textarea></p><?php
                    ?></div><?php 
                    ?><hr /><?php 
                    ?><label><?= $this->lang['addressEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="addressEn" class="rc en" maxlength="512"><?= $addressEn  ?></textarea></p><?php
                    ?></div><?php 
                }else {
                    ?><label><?= $this->lang['addressEn'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="addressEn" class="rc en" maxlength="512"><?= $addressEn  ?></textarea></p><?php
                    ?></div><?php 
                    ?><hr /><?php 
                    ?><label><?= $this->lang['addressAr'] ?></label><?php
                    ?><div class="bm"><?php
                        ?><p><textarea name="addressAr" class="rc ar" maxlength="512"><?= $addressAr  ?></textarea></p><?php
                    ?></div><?php 
                }
                if ($this->liOpen=='address') echo $actionDiv;
                ?></div><?php
            ?></li><?php 
            //if ($pageTitle) {
                $locName='';
                $latitude=0;
                $longitude=0;
                if (isset($data['loc'])) {
                    $locName=$data['loc']['name'];
                    $latitude=$data['loc']['lat'];
                    $longitude=$data['loc']['lon'];
                }
            
            ?><br /><li id="mapd"><?php 
                ?><div class="lm"><label><?= $this->lang['map'] ?></label><div class="info" label="map"><?= $locName ?></div></div><?php
                ?><div class="fm" style="display:block"><?php 
                ?><div class="map" id="mapo"></div><?php
                ?></div><?php 
            ?></li><?php
            $this->inlineScript.='var script = document.createElement("script");script.type = "text/javascript";script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initializeMap&language='.$this->urlRouter->siteLanguage.'";document.body.appendChild(script);';
            $this->globalScript.='
            var map,marker,geocoder,infoWindow,mapQ,cnF,cF,mxr,mlbl;
            var pttl="'.$pageTitle.'";
            function initializeMap() {
                geocoder = new google.maps.Geocoder();
                infowindow = new google.maps.InfoWindow();
                var myOptions = {zoom: 13,mapTypeId: google.maps.MapTypeId.HYBRID};
                map = new google.maps.Map(document.getElementById("mapo"), myOptions);
                google.maps.event.addDomListener(map, "click", mapClick);
                marker = new google.maps.Marker({map: map,animation: google.maps.Animation.DROP});
                defView();
                myloc();
            };    
            var mapClick=function(e){
                geocoder.geocode({"latLng": e.latLng},
                    function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                loc=results;
                                marker.setPosition(e.latLng);
                                setInfo(results,e.latLng);
                            }
                        }
                    });
            };
            var setInfo=function(results,e){
                var idx=0;
                if (results.length>1 && results[0].types[0]=="route" && results[0]["address_components"][1]["short_name"]!=results[1]["address_components"][0]["short_name"]){idx++}
                var adc=results[idx].address_components;
                var len=adc.length;
                if (len==1 && adc[len-1]["short_name"]!="IL") {
                    infowindow.setContent("'.$this->lang['be_specific'].'"+"<b>"+adc[0].long_name+"</b>");
                    infowindow.open(map, marker);
                    return true;
                };
                var tmp="",res="";
                for (var i=len-1;i>=0;i--) {
                    if (tmp!=adc[i].long_name && adc[i].types[0]!="locality"){
                        if(res) {res=", "+res;};
                        res=adc[i].long_name+res;
                        tmp=adc[i].long_name;
                    }
                };
                if (adc[len-1]["short_name"]!="IL")
                    setZoom(results[idx].types[0]);
                if(res) {
                    res="<b>"+pttl+"</b><br />"+res+"<br /><input class=\"bt rc'.($latitude || $longitude ? ' unv':'').'\" type=\"button\" value=\"'.$this->lang['setLoc'].'\" onclick=\'setLoc(\""+res+"\","+e.Ya+","+e.Za+",this)\' /><input class=\"bt rc ccl'.($latitude || $longitude ? '':' unv').'\" type=\"button\" value=\"'.$this->lang['remove'].'\" onclick=\"setLoc(\'\',0,0,this)\" />";
                }
                infowindow.setContent(res);
                infowindow.open(map, marker);
                return true;
            };
            function setLoc(loc,lat,lon,e){
                e=$(e);
                if (lat || lon){
                    e.addClass("unv");
                    e.next().removeClass("unv");
                }else {
                    e.addClass("unv");
                    e.prev().removeClass("unv");
                }
                if(!mlbl){
                    mlbl=$(".info",$("#mapd"));
                }
                mlbl.html(loc);
                $.ajax({
                    type:"POST",
                    url:"/ajax-page-loc/",
                    cache:false,
                    data:{
                        loc:loc,
                        lat:lat,
                        lon:lon
                    },
                    dataType:"json",
                    success:function(rp){
                        if(!rp.RP)failLoc(lat,lon,e);
                    },
                    error:function(){failLoc(lat,lon,e)}
                });
            };
            function failLoc(lat,lon,e){
                if (lat || lon){
                    e.removeClass("unv");
                    e.next().addClass("unv");
                }else {
                    e.removeClass("unv");
                    e.prev().addClass("unv");
                }
                mlbl.html("<i>'.$this->lang['locationFailure'].'</i>");
            };
            function myloc(){
                var pos;
                '.($longitude || $latitude ?'pos = new google.maps.LatLng('.$latitude.','.$longitude.');
                    map.setCenter(pos);
                    mapClick({latLng:pos});':'
                    if(navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        pos = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
                        map.setCenter(pos);
                        mapClick({latLng:pos});
                    });
                };').'                
                return true;
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
            var defView=function(){
                var df=new google.maps.LatLng('.($latitude || $longitude ? $latitude : (isset($_GET['lat'])?$_GET['lat']:'33.8852793')).','.($longitude || $latitude ? $longitude : (isset($_GET['lon'])?$_GET['lon']:'35.5055758') ).');
                map.setZoom(4);
                map.setCenter(df);
            };';
            
                 
                 
            /*?><li id="email"<?= $emailAlert?' class="alert"':'' ?>><?php
                ?><div class="lm"><label><?= $this->lang['email'] ?></label><div class="info" label="email"><?= $emailAlert ? '<i>'.$this->lang['missingEmail'].'</i>':$emailMsg ?></div><div class="lnk edit" title="<?= $emailAlert ? $this->lang['editEmailAlert']:$this->lang['editEmail'] ?>"><?= $this->lang['edit'] ?></div></div><?php
                ?><div class="fm"><label><?= $this->lang['email'] ?></label><?php
                    ?><div class="bm"><?php
                    ?><p><input name="email" class="rc en" maxlength="128" type="text" value="<?= $emailAlert ? '': $email ?>" placeholder="<?= $this->lang['specifyEmail'] ?>" regexMatch="true" regex="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)\b" yErr="<?= $this->lang['validEmail'] ?>" req="1"/></p><?php
                    ?></div><?php                    
                ?></div><?php
            ?></li><?php
            if (!$emailAlert) { 
                $notifications=array('ads'=>0,'news'=>0,'third'=>0);
                if (isset($this->user->info['options']['nb'])) $notifications=$this->user->info['options']['nb'];
                ?><li id="notifications"<?= $this->liOpen=='notifications'?' class="open"':'' ?>><?php
                    ?><div class="lm"<?= $this->liOpen=='notifications'?' style="display:none"':'' ?>><label><?= $this->lang['notifications'] ?></label><div class="lnk edit"><?= $this->lang['edit'] ?></div></div><?php
                    ?><div class="fm"<?= $this->liOpen=='notifications'?' style="display:block"':'' ?>><label><?= $this->lang['notifications'] ?></label><?php
                        ?><div class="bm"><?php
                        ?><p><input type="checkbox" name="ads" <?= $notifications['ads']?'checked="checked" ':'' ?>/><?= $this->lang['nb_ads'] ?></p><?php
                        ?><p><input type="checkbox" name="news" <?= $notifications['news']?'checked="checked" ':'' ?>/><?= $this->lang['nb_news'] ?></p><?php
                        ?><p><input type="checkbox" name="third" <?= $notifications['third']?'checked="checked" ':'' ?>/><?= $this->lang['nb_third'] ?></p><?php
                        ?></div><?php  
                        if ($this->liOpen=='notifications') echo $actionDiv;
                    ?></div><?php
                ?></li><?php             
            } */
        ?></form><?php
        ?></ul><?php 
        $this->inlineScript.='
            actBx=$(".am");
            $(".lnk.edit").click(function(){
                clsOpen();
                e=$(this);
                var p=e.parent();
                var li=p.parent();
                curForm=li.attr("id");
                p.css("display","none");
                p.next().css("display","block");
                if(curForm!="contact" && curForm!="logo" && curForm!="banner")p.next().append(actBx);
                li.addClass("open")
            });
            $("[name]").focus(function(i,e){
                var n=$(this).next();
                if(n.length)
                    n.remove()
            });';
        $this->globalScript.='
            var curForm="'.$this->liOpen.'",actBx;
            var addp=function(e){
                e=$(e);
                var n=e.prev().prev();
                var c=n.prev();
                var m=c.prev();
                var nv=n.attr("value");
                if (nv=="" || nv.match(/[^0-9]/)){
                    n.css("color", "red");
                }else {
                    var vals={
                        n:nv,
                        c:c.attr("value"),
                        m:m.attr("value")
                    };
                    $.ajax({
                        type:"POST",
                        url:"/ajax-page/",
                        cache:false,
                        data:{
                            form:curForm,
                            fields:vals,
                            lang:"'.$this->urlRouter->siteLanguage.'"
                        },
                        dataType:"json",
                        success:function(rp){
                            n.attr("value","");
                            e.parent().parent().next().html(rp.DATA.nums);
                        }
                    });
                }
            };
            var rmN=function(e,i){
                e=$(e);
                var vals={idx:i};
                $.ajax({
                    type:"POST",
                    url:"/ajax-page/",
                    cache:false,
                    data:{
                        form:"rmnum",
                        fields:vals,
                        lang:"'.$this->urlRouter->siteLanguage.'"
                    },
                    dataType:"json",
                    success:function(rp){
                        e.parent().parent().html(rp.DATA.nums);
                    }
                });
            };
            var clsOpen=function(){
                var li=$("ul.ts li.open");
                if (li.length){
                    $(".fm",li).css("display","none");
                    $(".lm",li).css("display","block");
                    li.removeClass("open");
                    if (curForm!="logo" && curForm!="banner"){
                    $("[name]",$("#"+curForm)).each(function(i,e){
                        if (e.type!="hidden"){
                        var n=$(e).next();
                        if(n.length) n.remove()
                        }
                    });
                    }
                    var nb=$(".notice",actBx);
                    nb.html("");
                    nb.removeClass("err")
                }};
            var save=function(){
                var nb=$(".notice",actBx);
                nb.html("");
                nb.removeClass("err");
                nb.addClass("loading");
                var f=$("#"+curForm);
                var d=$("[name]",f);
                if (validate(d)) {
                    var vals={};
                    try{
                        d.each(function(i,e){
                            e=$(e);
                            if (e.attr("type")=="checkbox"){
                                vals[e.attr("name")]=e.attr("checked")
                            }else {
                                vals[e.attr("name")]=e.attr("value")
                            }
                        });
                    $.ajax({
                        type:"POST",
                        url:"/ajax-page/",
                        cache:false,
                        data:{
                            form:curForm,
                            fields:vals,
                            lang:"'.$this->urlRouter->siteLanguage.'"},
                            dataType:"json",
                            success:function(rp){
                                d.each(function(i,e){
                                    $(e).attr("disabled",false)
                                });
                                $("input",actBx).attr("disabled",false);
                                if (rp.RP) {
                                    if(rp.DATA.fields){
                                        var es=rp.DATA.fields;
                                        var li=d.parents("li");
                                        console.log(li);
                                        var p=$(".lm",li);
                                        var cls=1;
                                        var pass=false;
                                        d.each(function(i,e){
                                            e=$(e);
                                            if (es[e.attr("name")]){
                                                e=$(e);
                                                var v=es[e.attr("name")];
                                                var c=$(\'[label="\'+e.attr("name")+\'"]\',p);
                                                if (c.length){
                                                    c.html(v[2] ? v[2] : v[1]);
                                                }
                                                if (e.attr("type")!="hidden"){
                                                    if (v[0]=="checked") {
                                                        if (v[1]=="checked"){
                                                            e.attr("checked","checked");
                                                        }else {
                                                            e.attr("checked",false);
                                                        }
                                                        var h=e.parent().html();
                                                        e.replaceWith($(h));
                                                    }else if(v[0]=="error"){
                                                        setFN(e[0],v[1]);
                                                        cls=0;
                                                    }else{
                                                        if (e[0].type=="textarea") e[0].innerHTML=v[1];
                                                        else e.attr(v[0],v[1]);
                                                        var h=e.parent().html();
                                                        e.replaceWith($(h));
                                                        if (v[1]) pass=true;
                                                    }
                                                }
                                            }
                                        });
                                        if(li.hasClass("bnot")){
                                            if (pass) li.removeClass("alert");
                                            else li.addClass("alert");
                                        }
                                        if(cls)clsOpen();
                                }}else {
                                    if (isNaN(rp.MSG)){
                                        nb.addClass("err");
                                        nb.html(rp.MSG);
                                    }
                                    if(rp.DATA.fields){
                                        var es=rp.DATA.fields;
                                        d.each(function(i,e){
                                            e=$(e);
                                            if (es[e.attr("name")]){
                                                setFN(e,es[e.attr("name")]);
                                            }
                                        });
                                    }
                                }
                                nb.removeClass("loading");
                            },
                            error:function(){
                                nb.removeClass("loading");
                                nb.addClass("err");
                                nb.html("'.$this->lang['systemErr'].'");
                                d.each(function(i,e){
                                    $(e).attr("disabled",false)
                                });
                                $("input",actBx).attr("disabled",false);
                            }});
                    }catch(e){console.log(e)}
            }else {
                nb.removeClass("loading");
                d.each(function(i,e){
                    $(e).attr("disabled",false)
                });
                $("input",actBx).attr("disabled",false);
            }
            return false;
        };
        var setFN=function(e,msg){
            var s=$("<span class=\'notice alert err inv\'>"+msg+"</span>");
            $(e).after(s);
            if(s.height()<20) {
                s.css("margin-top","8px");
            }
            s.removeClass("inv");
        };
        var validate=function(d){
            var r=true;
            d.each(function(i,e){
                if(e.type!="hidden"){
                var ps=false,em="";
                e=$(e);
                var n=e.next();
                if(n.length) n.remove();
                var v=e.attr("value");
                var min=e.attr("minLength") ? parseInt(e.attr("minLength")):0;
                if(e.attr("req") && (v.length==0 || v.length<min)) ps=true;
                em=e.attr("yErr");
                if (!ps){
                    var re=e.attr("regex");
                    if (re){
                    try{
                        re=new RegExp(re);
                        var o=e.attr("regexMatch")=="true"?true:false;
                        var f=re.exec(v);
                        if (f == null) {
                            if (o) ps=true;
                        }else {
                            if(!o)ps=true;
                        }
                        if(ps){
                            var tm=e.attr("vErr");
                            if(tm)em=tm;
                        }
                    }catch(x){console.log(x)}
                    }
                }
                if (ps){
                    r=false;
                    setFN(e,em);
                }}
                });
            if (r){
                d.each(function(i,e){$(e).attr("disabled",true)
        });
        $("input",actBx).attr("disabled",true);
        }return r;};';
    }
    
}
?>
