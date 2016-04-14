<?php
require_once 'Page.php';

class Register extends Page{
    
    var $include_password_js = false;

    function __construct($router){
        parent::__construct($router);
        if($this->urlRouter->cfg['active_maintenance']){
            $this->user->redirectTo('/maintenance/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }
        $title = $this->lang['create_account'];
        if(!$this->isMobile && $this->user->info['id'] && (
                $this->urlRouter->module=='welcome' || 
                isset($this->user->pending['password_new']) || 
                isset($this->user->pending['password_reset']) || 
                isset($this->user->pending['social_new']))){
            
                $this->set_require('css', 'home');
                $this->inlineCss.='
                    h2 .j.home{display: inline-block;
                    vertical-align: middle;
                    margin-top: -15px;}
                    h2 {
  font-size: '.($this->urlRouter->siteLanguage == 'ar' ? '16':'14').'px;
   '.($this->urlRouter->siteLanguage == 'ar' ? 'text-align:right;direction:rtl;':'text-align:left;direction:ltr;').'
  padding: 0 10px;
}
                        ';
                }
        if($this->user->info['id'] && $this->urlRouter->module=='welcome' && (isset($this->user->pending['password_new']) || isset($this->user->pending['social_new']) )){
            $title = $this->lang['welcome_mourjan'];
        }elseif($this->user->info['id'] && !isset($this->user->pending['password_reset']) && !isset($this->user->pending['password_new'])){
            $this->user->redirectTo($this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId));
        }else{
            if($this->urlRouter->module=='password' && !isset($this->user->pending['password_new'])){
                $title = $this->lang['title_pass_reset'];
            }
        }
        $this->forceNoIndex=true;
        $this->title=$title;
        $this->urlRouter->cfg['enabled_ads']=0;
        if(!$this->isMobile){
            $this->inlineCss.='.account{overflow:hidden!important;margin:0!important}';
            if($this->urlRouter->siteLanguage=='ar'){
            $this->inlineCss.='.htu{width:300px;float:left;display:block;height:400px;margin-top:5px;}.lgt{margin:10px 1px;line-height:30px;color:#143D55;text-align:center;font-size:18px;background-color:#efefef;border:1px solid #ccc;position:relative;}.lgt ul{width:100%;}.lgt li{padding:10px 0}.lgt label{font-size:24px;color:#999;}.lgt label.sm{font-size:20px;}.lgt .fld{width:500px;font-size:22px;padding:5px;}.lgt .fldp{width:250px;}#eWait{width:630px;position:absolute;top:0;left:10px;margin-top:60px;display:none;}';
            }else{
                $this->inlineCss.='.htu{width:300px;float:right;display:block;height:400px;margin-top:5px;}.lgt{margin:10px 1px;line-height:30px;color:#143D55;text-align:center;font-size:18px;background-color:#efefef;border:1px solid #ccc;position:relative;}.lgt ul{width:100%;}.lgt li{padding:10px 0}.lgt label{font-size:22px;color:#999;}.lgt label.sm{font-size:18px;}.lgt .fld{width:500px;font-size:20px;padding:5px;}.lgt .fldp{width:250px;}#eWait{width:630px;position:absolute;top:0;left:10px;margin-top:60px;display:none;}';
            }
            if($this->urlRouter->module=='password'){
                if($this->urlRouter->siteLanguage=='ar'){
                    $this->inlineCss.='#pout{position:absolute;top:73px;right:470px;text-align:right;font-size:16px}';
                }else{
                    $this->inlineCss.='#pout{position:absolute;top:73px;left:470px;text-align:left;font-size:14px;}';
                }
            }
        }else{
            $this->inlineCss.='.str p{margin-bottom:5px}';
            if($this->urlRouter->module=='password'){
                if($this->urlRouter->siteLanguage=='ar'){
                    $this->inlineCss.='#pout{text-align:center;font-size:22px;height:30px;margin:5px 10px}';
                }else{
                    $this->inlineCss.='#pout{text-align:center;font-size:20px;height:30px;margin:5px 10px}';
                }
            }
        }
        $this->render();
    }
    
    function mainMobile() {
        $notFound=0;
        $lang = $this->urlRouter->siteLanguage == 'ar' ? '':$this->urlRouter->siteLanguage.'/';
        if($this->user->info['id']){
            $msg = '';
            if(isset($this->user->pending['password_new'])){
                $msg = $this->lang['congrats_account'];
            }elseif(isset($this->user->pending['password_reset'])){
                $msg = $this->lang['congrats_password'];
            }else{
                $msg = $this->lang['congrats_social'].' '.$this->lang['mobile_welcome'];
            }
            echo '<div class="str"><span class="done"></span>'.$msg.'<br /><br /><p class="ctr"><a class="bt btw ok" href="/post/">'.$this->lang['start_publish'].'</a></p><br /></div>';
            if(isset($this->user->pending['password_reset']))unset($this->user->pending['password_reset']);
            if(isset($this->user->pending['password_new']))unset($this->user->pending['password_new']);
            if(isset($this->user->pending['social_new']))unset($this->user->pending['social_new']);
        }elseif($this->urlRouter->module=='signup'){
                ?><div id="eWait" class="dlg ctr rc sh"></div><?php
                ?><div id="eform"><?php
                    ?><h2 class='ctr'><?= $this->lang['hint_regm_1'] ?></h2><?php
                    ?><div class="str <?= $this->urlRouter->siteLanguage ?>"><p><?= $this->lang['account_email'] ?></p></div><?php
                    ?><ul class="ls po"><?php
                        ?><li class="h"><b>1 - <?= $this->lang['your_email'] ?></b></li><?php
                        ?><li><div class="ipt"><input onkeyup="ivf(this)" class="fld en" type="email" /></div></li><?php
                        ?><li><b class="ah ctr act2"><input type="button" onclick="reg(this)" class="bt ok off" value="<?= $this->lang['signup'] ?>" /></b></li><?php
                    ?></ul><?php
                ?></div><?php
            $this->globalScript.='var reg,ivf,rst,ivo=0;';
            $this->inlineScript.='
            reg=function(e){
                if(ivo){
                    var p=$p(e,3);
                    var d=$($p(p));
                    d.css("display","none");
                    var dn=d.prev();
                    dn.html("'.$this->lang['creating_account'].'<br /><span class=\'loads load\'></span>");
                    dn.css("display","block");
                    var f=$(".fld",p);
                    var v=f.val();
                    $.ajax({
                        type:"POST",
                        url:"/ajax-register/",
                        data:{
                            v:v,
                            lang:lang
                        },
                        dataType:"json",
                        success:function(rp){
                            if(rp && rp.RP){
                                dn.html("<span class=\'done\'></span> '.$this->lang['created_account'].'".replace("{email}",v));                    
                            }else{
                                switch(rp.MSG){
                                    case "103":
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['used_account'].'".replace("{email}",v));
                                        break;
                                    case "104":
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['emailed_account'].'".replace("{email}",v));
                                        break;
                                    default:
                                        failAc(dn);
                                        break;
                                }
                            }
                        },
                        error:function(rp){
                            failAc(dn);
                        }
                    });
                }
            };
            ivf=function(e){
                var v=e.value;
                if(isEmail(v)){
                    if(ivo==0){
                        tglEB(e,1);
                    }
                }else{
                    if(ivo==1){
                        tglEB(e,0);
                    }
                }
            };
            function tglEB(e,s){
                var p=$p(e,3);
                if(s) {
                    $(".bt",p).removeClass("off");
                    ivo=1;
                }else{
                    $(".bt",p).addClass("off");
                    ivo=0;
                }
            };
            function failAc(dn){
                dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
            };
            rst=function(e){
                var p=$($p(e));
                p.css("display","none");
                p.next().css("display","block");
            };';
        }elseif($this->urlRouter->module=='password'){
            if(isset($this->user->pending['password_new']) || isset($this->user->pending['password_reset'])){
                $this->include_password_js = true;
                $step = isset($this->user->pending['password_reset']) ? 1 : 0;
                 ?><div id="eWait" class="dlg ctr rc sh"></div><?php
                 ?><div id="eform"><?php
                    if($step){                    
                        /* ?><div class='list'><span class="naf"></span></div><?php */
                    }else{
                        ?><h2 class='ctr'><?= $this->lang['hint_reg_2'] ?></h2><?php
                    }
                    ?><div class="str <?= $this->urlRouter->siteLanguage ?>"><p><?= $this->lang['account_password'] ?></p></div><?php
                    ?><ul class="ls po"><?php
                        ?><li class="h"><b><?= $step ? $this->lang['your_new_password'] : '2 - '.$this->lang['your_password'] ?></b></li><?php
                        ?><li><div class="ipt"><input onkeyup="ivf(this)" id="pwd" class="fld fldp" type="password" value="" /></div></li><?php
                        ?><li><div id="pout"></div></li><?php
                        ?><li class="h"><b><?= $this->lang['re_your_password'] ?></b></li><?php
                        ?><li><div class="ipt"><input onkeyup="ivf(this)" id="pwd2" class="fld fldp" type="password" value="" /></div></li><?php
                        ?><li><b class="ah ctr act2"><input type="button" id="cont" onclick="save(this)" class="bt ok off" value="<?= $this->lang['save'] ?>" /></b></li><?php
                    ?></ul><?php
                        /*?><div id="pout"></div><?php*/
                    ?></div><?php
                
                $this->globalScript.='var save,ivf,p1,p2,bt,rst,ivo=0;';
                $this->inlineScript.='
                    ivf=function(e){
                        if(!p1){
                            var p=$p(e,3);
                            p1=$("#pwd",p);
                            p2=$("#pwd2",p);
                            bt=$("#cont",p);
                        }
                        if(p1.val().length>=6 && p1.val()==p2.val()){
                            ivo=1;
                            bt.removeClass("off");
                        }else{
                            ivo=0;
                            bt.addClass("off");
                        }
                    };
                    save=function(e){
                        if(ivo){
                            var p=$p(e,3);
                            var d=$($p(p));
                            d.css("display","none");
                            var dn=d.prev();
                            dn.html("'.$this->lang['saving_pass'].'<br /><span class=\'loads load\'></span>");
                            dn.css("display","block");
                            
                            $.ajax({
                                type:"POST",
                                url:"/ajax-password/",
                                data:{
                                    v:p1.val(),
                                    lang:lang
                                },
                                dataType:"json",
                                success:function(rp){
                                    if(rp && rp.RP){
                                        document.location="/'.(isset($this->user->pending['password_new']) ? 'welcome':'password').'/"+(lang=="ar"?"":lang+"/");
                                    }else{
                                        failAc(dn);
                                    }
                                },
                                error:function(rp){
                                    failAc(dn);
                                }
                            });
                        }
                    };                    
                    function failAc(dn){
                        dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
                    };
                    rst=function(e){
                        var p=$($p(e));
                        p.css("display","none");
                        p.next().css("display","block");
                    };
                ';
            }else{
                $notFound=1;
            }
        }else{
            $notFound=1;
        }
        if($notFound){
            //$this->user->redirectTo('/invalid/'.$lang);
            ?><div id="eWait" class="dlg ctr rc sh"></div><?php
            ?><div id="eform"><?php
                ?><div class="str <?= $this->urlRouter->siteLanguage ?>"><p><?= $this->lang['account_pass_reset'] ?></p></div><?php
                ?><ul class="ls po"><?php
                    ?><li class="h"><b><?= $this->lang['your_email'] ?></b></li><?php
                    ?><li><div class="ipt"><input onkeyup="ivf(this)" class="fld en" type="email" /></div></li><?php
                    ?><li><b class="ah ctr act2"><input type="button" onclick="reg(this)" class="bt ok off" value="<?= $this->lang['pass_reset_bt'] ?>" /></b></li><?php
                ?></ul><?php
            ?></div><?php
            $this->globalScript.='var reg,ivf,rst,ivo=0;';
            $this->inlineScript.='
            reg=function(e){
                if(ivo){
                    var p=$p(e,3);
                    var d=$($p(p));
                    d.css("display","none");
                    var dn=d.prev();
                    dn.html("'.$this->lang['emailing_preset'].'<br /><span class=\'loads load\'></span>");
                    dn.css("display","block");
                    var f=$(".fld",p);
                    var v=f.val();
                    $.ajax({
                        type:"POST",
                        url:"/ajax-preset/",
                        data:{
                            v:v,
                            lang:lang
                        },
                        dataType:"json",
                        success:function(rp){
                            if(rp && rp.RP){
                                dn.html("<span class=\'done\'></span> '.$this->lang['sent_preset'].'".replace("{email}",v));                    
                            }else{
                                switch(rp.MSG){
                                    case "104":
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['account_notfound'].'".replace("{email}",v));
                                        break;
                                    case "105":
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['emailed_preset'].'".replace("{email}",v));
                                        break;
                                    case "106":
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['emailed_account'].'".replace("{email}",v));
                                        break;
                                    default:
                                        failAc(dn);
                                        break;
                                }
                            }
                        },
                        error:function(rp){
                            failAc(dn);
                        }
                    });
                }
            };
            ivf=function(e){
                var v=e.value;
                if(isEmail(v)){
                    if(ivo==0){
                        tglEB(e,1);
                    }
                }else{
                    if(ivo==1){
                        tglEB(e,0);
                    }
                }
            };
            function tglEB(e,s){
                var p=$p(e,3);
                if(s) {
                    $(".bt",p).removeClass("off");
                    ivo=1;
                }else{
                    $(".bt",p).addClass("off");
                    ivo=0;
                }
            };
            function failAc(dn){
                dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
            };
            rst=function(e){
                var p=$($p(e));
                p.css("display","none");
                p.next().css("display","block");
            };';
        }
    }
    
    function main_pane(){ 
        $notFound=0;
        $lang = $this->urlRouter->siteLanguage == 'ar' ? '':$this->urlRouter->siteLanguage.'/';
        if($this->user->info['id']){
            $msg = '';
            if(isset($this->user->pending['password_new'])){
                $msg = $this->lang['congrats_account'];
            }elseif(isset($this->user->pending['password_reset'])){
                $msg = $this->lang['congrats_password'];
            }else{
                $msg = $this->lang['congrats_social'];
            }
            ?><div class='list htu'></div><?php
            ?><div class="lgt rc sh"><?php 
                ?><h2><span class="done"></span> <?= $msg ?></h2><?php
            ?></div><?php 
            
            
            ?><ul id="note" class='note <?= $this->urlRouter->siteLanguage ?>'></ul><?php
            ?><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php 
            ?><a href="/post/<?= $lang ?>" class="option half"><span class="j pub"></span> <?= $this->lang['button_ad_post_m'] ?></a><?php
                ?><a href="/statement/<?= $lang ?>" class="option half balance"><span class="pj coin"></span> <span id="coins"><?= $this->lang['myBalance'] ?></span></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php     
                ?><a id="active" href="/myads/<?= $lang ?>" class="option quarter active"><span class="pj ads1"></span><br /><?= $this->lang['ads_active'] ?></a><?php
                ?><a id="pending" href="/myads/<?= $lang ?>?sub=pending" class="option quarter pending"><span class="pj ads2"></span><br /><?= $this->lang['home_pending'] ?></a><?php
                ?><a id="draft" href="/myads/<?= $lang ?>?sub=drafts" class="option quarter draft"><span class="pj ads3"></span><br /><?= $this->lang['home_drafts'] ?></a><?php
                ?><a id="archive" href="/myads/<?= $lang ?>?sub=archive" class="option quarter archive"><span class="pj ads4"></span><br /><?= $this->lang['home_archive'] ?></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
                ?><a id="favorite" href="/favorites/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>" class="option half favorite"><span class="j fva"></span> <?= $this->lang['myFavorites'] ?></a><?php
                ?><a id="watchlist" href="/watchlist/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>" class="option half watchlist"><span class="j eye"></span> <?= $this->lang['myList'] ?></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
                ?><a href="/account/<?= $lang ?>" class="option full settings"><span class="j sti"></span> <?= $this->lang['myAccount'] ?></a><?php
            ?></div><?php
            
            /* ?><br /><br /><br /><p class="ctr"><a class="bt btw ok" href="/post/"><?= $this->lang['start_publish'] ?></a></p><?php */
            if(isset($this->user->pending['password_reset']))unset($this->user->pending['password_reset']);
            if(isset($this->user->pending['password_new']))unset($this->user->pending['password_new']);
            if(isset($this->user->pending['social_new']))unset($this->user->pending['social_new']);
            $this->user->update();
        }elseif($this->urlRouter->module=='signup'){
            ?><div class='list htn'><?= $this->lang['hint_reg_1'] ?></div><?php
            ?><br /><p><?= $this->lang['account_email'] ?></p><?php
            ?><div class="lgt rc sh"><?php 
                ?><div id="eform"><?php
                    ?><ul><?php
                        ?><li><label>1 - <?= $this->lang['your_email'] ?></label></li><?php
                        ?><li><input onkeyup="ivf(this)" class="fld en" type="email" /></li><?php
                        ?><li class="ctr"><input type="button" onclick="reg(this)" class="bt ok off" value="<?= $this->lang['signup'] ?>" /></li><?php
                    ?></ul><?php
                ?></div><?php
                ?><div id="eWait"></div><?php
            ?></div><?php
            $this->globalScript.='var reg,ivf,rst,ivo=0;';
            $this->inlineScript.='
            reg=function(e){
                if(ivo){
                    var p=e.parentNode.parentNode;
                    var d=$(p.parentNode);
                    d.css("visibility","hidden");
                    var dn=d.next();
                    dn.html("'.$this->lang['creating_account'].'<br /><span class=\'loads load\'></span>");
                    dn.css("margin-top","60px");
                    dn.css("display","block");
                    var f=$(".fld",p);
                    var v=f.val();
                    $.ajax({
                        type:"POST",
                        url:"/ajax-register/",
                        data:{
                            v:v,
                            lang:lang
                        },
                        dataType:"json",
                        success:function(rp){
                            if(rp && rp.RP){
                                dn.css("margin-top","40px");
                                dn.html("<span class=\'done\'></span> '.$this->lang['created_account'].'".replace("{email}",v));                    
                            }else{
                                switch(rp.MSG){
                                    case "103":
                                        dn.css("margin-top","40px");
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['used_account'].'".replace("{email}",v));
                                        break;
                                    case "104":
                                        dn.css("margin-top","40px");
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['emailed_account'].'".replace("{email}",v));
                                        break;
                                    default:
                                        failAc(dn);
                                        break;
                                }
                            }
                        },
                        error:function(rp){
                            failAc(dn);
                        }
                    });
                }
            };
            ivf=function(e){
                var v=e.value;
                if(isEmail(v)){
                    if(ivo==0){
                        tglEB(e,1);
                    }
                }else{
                    if(ivo==1){
                        tglEB(e,0);
                    }
                }
            };
            function tglEB(e,s){
                var p=e.parentNode.parentNode;
                if(s) {
                    $(".bt",p).removeClass("off");
                    ivo=1;
                }else{
                    $(".bt",p).addClass("off");
                    ivo=0;
                }
            };
            function failAc(dn){
                dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
            };
            rst=function(e){
                var p=$(e.parentNode);
                p.css("display","none");
                p.prev().css("visibility","visible");
            };';
        }elseif($this->urlRouter->module=='password'){
            if(isset($this->user->pending['password_new']) || isset($this->user->pending['password_reset'])){
                $this->include_password_js = true;
                $step = isset($this->user->pending['password_reset']) ? 1 : 0;
                if($step){                    
                    ?><div class='list'><span class="naf"></span></div><?php
                }else{
                    ?><div class='list htn'><?= $this->lang['hint_reg_2'] ?></div><?php
                }
                ?><br /><p><?= $this->lang['account_password'] ?></p><?php
                ?><div class="lgt rc sh"><?php 
                    ?><div id="eform"><?php
                        ?><ul><?php
                            ?><li><label><?= $step ? $this->lang['your_new_password'] : '2 - '.$this->lang['your_password'] ?></label></li><?php
                            ?><li><input onkeyup="ivf(this)" id="pwd" class="fld fldp" type="password" value="" /></li><?php                            
                            ?><li><label class="sm"><?= $this->lang['re_your_password'] ?></label></li><?php
                            ?><li><input onkeyup="ivf(this)" id="pwd2" class="fld fldp" type="password" value="" /></li><?php
                            ?><li class="ctr"><input type="button" id="cont" onclick="save(this)" class="bt ok off" value="<?= $this->lang['save'] ?>" /></li><?php
                        ?></ul><?php
                        ?><div id="pout"></div><?php
                    ?></div><?php
                    ?><div id="eWait"></div><?php
                ?></div><?php
                
                $this->globalScript.='var save,ivf,p1,p2,bt,rst,ivo=0;';
                $this->inlineScript.='
                    ivf=function(e){
                        if(!p1){
                            var p=e.parentNode.parentNode;
                            p1=$("#pwd",p);
                            p2=$("#pwd2",p);
                            bt=$("#cont",p);
                        }
                        if(p1.val().length>=6 && p1.val()==p2.val()){
                            ivo=1;
                            bt.removeClass("off");
                        }else{
                            ivo=0;
                            bt.addClass("off");
                        }
                    };
                    save=function(e){
                        if(ivo){
                            var p=e.parentNode.parentNode;
                            var d=$(p.parentNode);
                            d.css("visibility","hidden");
                            var dn=d.next();
                            dn.html("'.$this->lang['saving_pass'].'<br /><span class=\'loads load\'></span>");
                            dn.css("margin-top","110px");
                            dn.css("display","block");
                            
                            $.ajax({
                                type:"POST",
                                url:"/ajax-password/",
                                data:{
                                    v:p1.val(),
                                    lang:lang
                                },
                                dataType:"json",
                                success:function(rp){
                                    if(rp && rp.RP){
                                        document.location="/'.(isset($this->user->pending['password_new']) ? 'welcome':'password').'/"+(lang=="ar"?"":lang+"/");
                                    }else{
                                        failAc(dn);
                                    }
                                },
                                error:function(rp){
                                    failAc(dn);
                                }
                            });
                        }
                    };                    
                    function failAc(dn){
                        dn.css("margin-top","110px");
                        dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
                    };
                    rst=function(e){
                        var p=$(e.parentNode);
                        p.css("display","none");
                        p.prev().css("visibility","visible");
                    };
                ';
            }else{
                $notFound=1;
            }
        }else{
            $notFound=1;
        }
        if($notFound){
            //$this->user->redirectTo('/invalid/'.$lang);
            ?><div class='list'><span class="naf"></span></div><?php
            ?><br /><p><?= $this->lang['account_pass_reset'] ?></p><?php
            ?><div class="lgt rc sh"><?php 
                ?><div id="eform"><?php
                    ?><ul><?php
                        ?><li><label><?= $this->lang['your_email'] ?></label></li><?php
                        ?><li><input onkeyup="ivf(this)" class="fld en" type="email" /></li><?php
                        ?><li class="ctr"><input type="button" onclick="reg(this)" class="bt ok off" value="<?= $this->lang['pass_reset_bt'] ?>" /></li><?php
                    ?></ul><?php
                ?></div><?php
                ?><div id="eWait"></div><?php
            ?></div><?php
            $this->globalScript.='var reg,ivf,rst,ivo=0;';
            $this->inlineScript.='
            reg=function(e){
                if(ivo){
                    var p=e.parentNode.parentNode;
                    var d=$(p.parentNode);
                    d.css("visibility","hidden");
                    var dn=d.next();
                    dn.html("'.$this->lang['emailing_preset'].'<br /><span class=\'loads load\'></span>");
                    dn.css("margin-top","60px");
                    dn.css("display","block");
                    var f=$(".fld",p);
                    var v=f.val();
                    $.ajax({
                        type:"POST",
                        url:"/ajax-preset/",
                        data:{
                            v:v,
                            lang:lang
                        },
                        dataType:"json",
                        success:function(rp){
                            if(rp && rp.RP){
                                dn.css("margin-top","40px");
                                dn.html("<span class=\'done\'></span> '.$this->lang['sent_preset'].'".replace("{email}",v));                    
                            }else{
                                switch(rp.MSG){
                                    case "104":
                                        dn.css("margin-top","10px");
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['account_notfound'].'".replace("{email}",v));
                                        break;
                                    case "105":
                                        dn.css("margin-top","40px");
                                        dn.html("<span class=\'fail\'></span> '.$this->lang['emailed_preset'].'".replace("{email}",v));
                                        break;
                                    default:
                                        failAc(dn);
                                        break;
                                }
                            }
                        },
                        error:function(rp){
                            failAc(dn);
                        }
                    });
                }
            };
            ivf=function(e){
                var v=e.value;
                if(isEmail(v)){
                    if(ivo==0){
                        tglEB(e,1);
                    }
                }else{
                    if(ivo==1){
                        tglEB(e,0);
                    }
                }
            };
            function tglEB(e,s){
                var p=e.parentNode.parentNode;
                if(s) {
                    $(".bt",p).removeClass("off");
                    ivo=1;
                }else{
                    $(".bt",p).addClass("off");
                    ivo=0;
                }
            };
            function failAc(dn){
                dn.html("<span class=\'fail\'></span> '.$this->lang['accError'].'");
            };
            rst=function(e){
                var p=$(e.parentNode);
                p.css("display","none");
                p.prev().css("visibility","visible");
            };';
        }
    }
    
}
?>
