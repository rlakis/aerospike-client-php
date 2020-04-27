<?php
\Config::instance()->incLayoutFile('Page');

class Register extends Page{
    
    var $include_password_js = false;

    function __construct(){
        parent::__construct();
        if ($this->router()->config()->isMaintenanceMode()) {
            $this->user->redirectTo($this->router()->getLanguagePath('/maintenance/'));
        }
        $title = $this->lang['create_account'];
        if ($this->user->info['id'] && $this->router()->module=='welcome' && (isset($this->user->pending['password_new']) || isset($this->user->pending['social_new']))) {
            $title = $this->lang['welcome_mourjan'];
        }
        elseif ($this->user->info['id'] && !isset($this->user->pending['password_reset']) && !isset($this->user->pending['password_new'])) {
            $this->user->redirectTo($this->router()->getURL($this->router()->countryId, $this->router()->cityId));
        }
        else {
            if ($this->router()->module=='password' && !isset($this->user->pending['password_new'])) {
                $title = $this->lang['title_pass_reset'];
            }
        }
        $this->forceNoIndex=true;
        $this->title=$title;
        $this->router()->config()->disableAds();                    
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
    
    
    function main_pane() : void { 
        $notFound=0;
        if ($this->user()->isLoggedIn()) {
            $msg='';
            if (isset($this->user->pending['password_new'])) {
                $msg=$this->lang['congrats_account'];
            }
            elseif (isset($this->user->pending['password_reset'])) {
                $msg=$this->lang['congrats_password'];
            }
            else {
                $msg=$this->lang['congrats_social'];
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
        }
        elseif ($this->router()->module==='signup') {
            ?><div class=row><div class=col-12><div class="card card-doc"><?php
            ?><div class='card-title'><?= $this->lang['hint_reg_1'] ?></div><?php
            ?><div class=card-content><p><?= $this->lang['account_email'] ?></p><br><?php
            ?><div class="lgt rc sh"><?php 
                ?><div id="eform"><?php
                    ?><div class="row"><div class="group"><?php
                        ?><input type="email" id="email" required onkeyup="validateEmail(this);"><?php                                        
                        ?><label><?= $this->lang['your_email'] ?></label><?php
                    ?></div></div><?php
                    ?><button class="btn" type="submit" style="float: right;background-color:#5bc236;color:white;" onclick="register()"><?= $this->lang['signup'] ?></button><?php
                ?></div><?php
                ?><div id="eWait"></div><?php
            ?></div><?php
            ?></div><div class="card-footer" style="padding:10px">&nbsp;</div></div></div></div>'<?php                        
        }
        elseif ($this->router()->module==='password') {
            
            if (isset($this->user->pending['password_new'])||isset($this->user->pending['password_reset'])) {
                ?><style>
                    .weak{width:33.3% !important;background-color:#e74c3c !important;}
                    .weak:before, .weak:after{width:100% !important; background:#e74c3c !important;}                    
                    .weak:after{left:0;right:33.3% !important;}
                    
                    .medium{width:66.6% !important;background-color:#e67e22 !important;}
                    .medium:before, .medium:after{width:100% !important; background:#e67e22 !important;}
                    .medium:after{left:0;right:66.6% !important;}
                                                            
                    .strong{width:100% !important;background-color:#2ecc71 !important;}
                    .strong:before, .strong:after{width:100% !important; background-color:#2ecc71 !important;}
                    .strong:after{left:0;right:100% !important;}
                    
                    .weak:before, .medium:before, .strong:before{left:0% !important;}
                </style><?php
                
                $this->include_password_js=true;
                $step=isset($this->user->pending['password_reset']) ? 1 : 0;
                ?><div class=row><div class=col-12><div class="card card-doc"><?php
                if ($step) {                    
                    ?><div class=card-title><span class="naf"></span></div><?php
                }
                else {                    
                    ?><div class=card-title><?= $this->lang['hint_reg_2'] ?></div><?php
                }
                ?><div class=card-content><p><?= $this->lang['account_password'] ?></p><br><?php                
                    ?><div id="eform"><?php
                    ?><div class=row><div class=group><?php
                    ?><input type="password" id="pwd" required value="" onkeyup="pswdStrength(this)" onkeypress="return (event.charCode>32);"><div class=bar></div><?php
                    ?><label><?= ($step?$this->lang['your_new_password']:$this->lang['your_password']) ?></label><?php
                    
                    ?></div></div><?php
                    ?><div class=row><div class=group><?php
                    ?><input type="password" id="pwd2" required value="" onkeyup="pswdStrength(this)"><div class=bar></div><?php
                    ?><label><?= $this->lang['re_your_password'] ?></label><?php
                    ?></div></div><?php
                    ?><button class=btn type=submit style="float: right;background-color:#5bc236;color:white;" onclick="pswdSave()"><?= $this->lang['save'] ?></button><?php
                    ?></div><?php
                ?></div><?php
                ?><div class=card-footer style="padding:10px">&nbsp;</div><?php
                ?></div></div></div><?php                                
            }
            else {
                $notFound=1;
            }
        }
        else {
            $notFound=1;
        }
        
        if ($notFound) {
            /*
            ?><script>
                var reg,ivf,rst,ivo=0;
                let isEmail=function(v){
                    return v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
                };
                ivf=function(e){
                    var v=e.value;                    
                    if(isEmail(v)){
                        if(ivo===0){tglEB(e,1);}
                    }else{
                        if(ivo===1){tglEB(e,0);}
                    }
                };
                function tglEB(e,s){
                    var p=e.parentNode.parentNode;
                    let b=document.querySelector('input[type=button].bt');                    
                    if(s){
                        b.classList.remove('off');ivo=1;
                    }else{
                        b.classList.add('off');ivo=0;
                    }
                };
            reg=function(e){
                if(ivo){
                    let p=e.parentNode.parentNode;
                    let d=p.parentNode;
                    d.style.setProperty('visibility', 'hidden');
                    let dn=d.nextSibling;
                    dn.innerHTML='<?=$this->lang['emailing_preset']?>'+"<br /><span class=\'loads load\'></span>";
                    dn.style.setProperty("margin-top","60px");
                    dn.style.setProperty("display","block");
                    var f=p.querySelector('input[type=email].fld');
                    var v=f.value;
                    let opt={method: 'POST', mode: 'same-origin', credentials: 'same-origin',
                        body:JSON.stringify({v:f.value, lang:(document.body.dir==='rtl'?'ar':'en')}),
                        headers:{'Accept':'application/json','Content-Type':'application/json'}};

                    fetch("/ajax-preset/", opt)
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if (response.RP===1) {
                            //ad.approved().removeMask();
                        }
                    })
                    .catch(error => {
                        console.log('Error:', error);
                    });
                       
                }
            };               
            </script><?php
            */
            ?><div class=row style="align-items: center; justify-content: center"><div class=col-8><div class="card doc"><?php
            ?><div class=card-title><p style="line-height: 1.5em"><?= $this->lang['title_pass_reset'] ?></p></div><?php
            ?><div class=card-content><p style="line-height: 1.5em"><?= $this->lang['account_pass_reset'] ?></p><br><br><div id="eform"><?php
            ?><div class=row><div class=group><?php
            ?><input type="email" id="email" required onkeyup="validateEmail(this);"><?php                                        
            ?><label><?= $this->lang['your_email'] ?></label><?php
            ?></div></div><?php
            ?><button class=btn type=submit style="float: right;background-color:#5bc236;color:white;" onclick="register()"><?= $this->lang['pass_reset_bt'] ?></button><?php
            ?></div><?php 
            ?></div><?php
            ?><div class=card-footer style="padding:10px">&nbsp;</div><?php
            ?></div></div></div><?php            
            ?></div></div></div><?php
        }
        $this->inlineJS('util.js')->inlineJS('signup.js');
    }
    
}