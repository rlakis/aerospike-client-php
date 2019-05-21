<?php
require_once 'Page.php';

class Contact extends Page{

    function __construct(){
        parent::__construct();
        $this->hasLeadingPane=true;
        $this->urlRouter->cfg['enabled_ads']=false;
        if ($this->isMobile) {
            $this->inlineCss.='label{display:block;margin:0}input,textarea{width:95%;margin-bottom:15px}#nb{display:none}';
        }
        //$this->set_ad(array("Leaderboard"=>array("/1006833/Leaderboard", 728, 90, "div-gpt-ad-1319709425426-0")));
        $this->load_lang(array("contact"));
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->render();
    }
    
    function mainMobile(){
        $name="";
        $email="";
        if ($this->user->info['id']) {
            $name=$this->user->info['name'];
            if (isset ($this->user->info['email']) && strpos($this->user->info['email'], "@")!==false) $email=$this->user->info['email'];
        }
        ?><div class="str ctr"><form onsubmit="vf(this);return false;"><label><?= $this->lang['name'] ?></label><?php
        ?><input onkeyup="idir(this)" tabindex="1" type="text" id="name" value="<?= $name ?>" title="<?= $this->lang['name'] ?>" req="<?= $this->lang['errName'] ?>" placeholder="<?= $this->lang['hint_name'] ?>" <?= $name ? "disabled='disabled'":"" ?> /><?php
        ?><label><?= $this->lang['email'] ?></label><?php
        ?><input tabindex="2" type="email" class="ltr" id="email" value="<?= $email ?>" title="<?= $this->lang['email'] ?>" req="<?= $this->lang['errMail'] ?>" placeholder="<?= $this->lang['hint_email'] ?>" <?= $email ? "disabled='disabled'":"" ?> /><?php
        ?><label><?= $this->lang['message'] ?></label><?php
        ?><textarea onkeyup="idir(this)" tabindex="3" id="msg" title="<?= $this->lang['message'] ?>" req="<?= $this->lang['errMsg'] ?>"></textarea><?php 
        ?><input tabindex="4" class="button bt hf" type="submit" value="<?= $this->lang['send'] ?>" /></form></div><?php
        
       /* ?><div class="loader"><div class='load'><?= $this->lang['aSending'] ?></div></div><?php */
        
        $this->globalScript.='
            function vf(e){
                var data=psf(e);
                if(data){
                    dsl(e.parentNode,"'.$this->lang['aSending'].'",1,0,getWSct());
                    data["lang"]=lang;
                    $.ajax({
                        type:"POST",
                        url:"/ajax-contact/",
                        data:data,        
                        dataType:"json",
                        success:function(rp){
                            if(rp && rp.RP){
                                e.childNodes[5].value="";
                                dsl(e.parentNode,"<span class=\'done\'></span>"+rp.MSG,0,1,0);
                            }else {
                                dsl(e.parentNode,"<span class=\'fail\'></span>"+rp.MSG,0,1);
                            }
                        }
                    });
                }
        };';
    }
   

    function side_pane(){
        $this->renderSideSite();
        //$this->renderSideUserPanel();
        //$this->renderSideLike();
    }

    function main_pane(){
        //$this->contactHeader();
        $this->contactForm();
    }

    function contactForm(){
        $name="";
        $email="";
        $message = '';
        if(isset($_GET['payfort']) && is_numeric($_GET['payfort'])){
            $message = $this->lang['payfort_fail_msg'].'#'.$_GET['payfort'].'#';
        }
        
        if ($this->user->info['id']) {
            $name=$this->user->info['name'];
            if (isset ($this->user->info['email']) && strpos($this->user->info['email'], "@")!==false) $email=$this->user->info['email'];
        }
        ?><p class="ph phm"><?= $this->lang['header'] ?></p><div class="form rc"><form onsubmit="vf(this);return false;"><ul><?php
            ?><li><label><?= $this->lang['name'] ?>:</label><input onkeydown="idir(this)" onchange="idir(this,1)" type="text" id="name" value="<?= $name ?>" placeholder="<?= $this->lang['hint_name'] ?>" <?= $name ? "disabled='disabled'":"" ?> /></li><?php
            ?><li><label><?= $this->lang['email'] ?>:</label><input onkeydown="idir(this)" onchange="idir(this,1)" type="text" id="email" value="<?= $email ?>" placeholder="<?= $this->lang['hint_email'] ?>" <?= $email ? "disabled='disabled'":"" ?> /></li><?php
            ?><li><label class="ta"><?= $this->lang['message'] ?>:</label><textarea onkeydown="idir(this)" onchange="idir(this,1)" rows="10" id="msg"><?= $message ?></textarea></li><?php
        ?><li class="ctr"><input class="bt" type="submit" value="<?= $this->lang['send'] ?>" /></li><?php
        ?></ul></form><span class="omail <?= $this->urlRouter->siteLanguage ?>"></span><span class="nb"></span></div><?php
        $this->inlineScript.='
            window["pla"]=function(e){
                e.prev().append("<span class=\'fia\'></span>");
            };
            window["vf"]=function(form){
                var e=false;
                var form=$(form);
                var im=form.next();
                var p=form.parent();
                var data={};
                var name=$("#name",form);
                var email=$("#email",form);
                var msg=$("#msg",form);
                var nb=$("span.nb",p);
                name.removeClass("err");
                email.removeClass("err");
                msg.removeClass("err");
                $(".fia",form).remove();
                nb.css("display","none");
                nb.html("");
                data.name=name.val();
                if (data.name.length < 2){
                    name.addClass("err");
                    pla(name);
                    e=true;
                }
                data.email=email.val();
                if (!isEmail(data.email)){
                    email.addClass("err");
                    pla(email);
                    e=true;
                }
                data.msg=msg.val();
                if (data.msg.length < 10){
                    msg.addClass("err");
                    pla(msg);
                    e=true;
                }
                if (e) {
                    return false;
                }
                
                form.animate({
                    opacity:0
                },200);
                im.animate({
                    opacity:0
                },200);
                p.addClass("load");
                nb.html("'.$this->lang['sendingMsg'].'");
                nb.fadeIn(); 
                
                data["lang"]=lang;
                $.ajax({url: "/ajax-contact/",
                    cache:false,
                    data:data,
                    dataType:"json",
                    type:"POST",
                    success:function(res){
                        p.removeClass("load");
                        nb.html(res.MSG+"<br /><br /><br /><br /><br /><br /><br /><br /><input type=\'button\' class=\'bt\' onclick=\'shwF(this,1)\' value=\''.$this->lang['continue'].'\' />");
                        if (res.RP) {
                            msg.val("");
                            im.addClass("osent "+lang);
                            im.animate({
                                opacity:1
                            },200);
                        }else{
                            nb.html("'.$this->lang['errSys'].'<br /><br /><input type=\'button\' class=\'bt\' onclick=\'shwF(this)\' value=\''.$this->lang['tryAgain'].'\' />");
                        }
                        nb.fadeIn();
                    },
                    error:function(){
                        p.removeClass("load");
                        nb.html("'.$this->lang['errSys'].'<br /><br /><input type=\'button\' class=\'bt\' onclick=\'shwF(this)\' value=\''.$this->lang['tryAgain'].'\' />");
                        nb.fadeIn();
                    }
                  });};
                  window["shwF"]=function(e,c){
                        nb=$(e.parentNode);
                        nb.fadeOut();
                        var im=nb.prev();
                        var f=im.prev();
                        f.animate({
                            opacity:1
                        },200);
                        im.animate({
                            opacity:1
                        },200);
                        im.removeClass("osent");
                        if(c){
                            $("textarea",f).val("");
                        }
                  };
            ';
    }

    function contactHeader(){
        $countryId=0;
        $cityId=0;
        $countryName=$this->countryName;
        if (isset($this->user->params["country"]) && $this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        ?><div class='sum rc'><div class="brd"><?php
        echo "<a href='{$this->urlRouter->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1><?= $this->lang['title'] ?></h1></div><?= $this->lang['header'] ?></div><?php
    }
    
}
?>
