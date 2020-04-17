<?php
require_once 'Page.php';

class Contact extends Page {

    function __construct() {
        parent::__construct();
        $this->hasLeadingPane=true;
        $this->router->config->disableAds();
        $this->router->config->setValue('enabled_sharing', 0);
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
   

    function side_pane() {      
        $this->renderSideSite();
        //$this->renderSideUserPanel();
        //$this->renderSideLike();
    }

    
    function main_pane(){
        echo '<div class=row><div class="col-2 side">', $this->side_pane(), '</div><div class=col-10><div class="card card-doc">';
        //$this->renderSideSite();
        //$this->contactHeader();
        $this->contactForm();
        echo '</div></div></div>';
    }

    
    function contactForm() {
        $name=''; $email=''; $message='';
        if (isset($_GET['payfort']) && is_numeric($_GET['payfort'])) {
            $message=$this->lang['payfort_fail_msg'].'#'.$_GET['payfort'].'#';
        }
        
        if ($this->user()->isLoggedIn()) {
            $name=$this->user->info['name'];
            if (isset ($this->user->info['email']) && strpos($this->user->info['email'], "@")!==false) $email=$this->user->info['email'];
        }
        
        ?><p class="ph phm"><?= $this->lang['header'] ?></p><div class="card-content"><form onsubmit="vf(this);return false;"><?php
        ?><div class=row><div class=group>
            <input type=text required onkeydown="dirElem(this)" onchange="dirElem(this)" type=text id=name value="<?= $name ?>" <?= $name ? "readonly":"" ?> /><?php
            if (!$this->user()->isLoggedIn()) {
                echo '<label>', $this->lang['hint_name'], '</label>';
            }
            ?><span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
            
        ?><div class=row><div class=group>
            <input type=email required onkeydown="dirElem(this)" onchange="dirElem(this)" onkeyup="this.setAttribute('value', this.value);" id=email value="<?= $email ?>" <?= $email ? "disabled='disabled'":"" ?> /><?php
            if (!$this->user()->isLoggedIn()) {
                echo '<label>', $this->lang['hint_email'], '</label>';
            }
            ?><span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
        
        ?><div class=row><div class=group>
            <textarea required onkeydown="dirElem(this)" onchange="dirElem(this,1)" rows=10 id=msg><?= $message ?></textarea>
            <label><?= $this->lang['message'] ?></label>
            <span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
        ?><button class=btn type=submit style="float: right;background-color:#5bc236;color:white;"><?= $this->lang['send'] ?></button><?php
        ?></form><span class="omail <?= $this->router->language ?>"></span><span class="nb"></span></div><?php
        ?><script>dirElem=function(e){if(e.target){e=e.target;}var v=e.value;e.className=(!v)?'':((v.match(/[\u0621-\u064a\u0750-\u077f]/))?'ar':'en');};
        vf=function(e){
            let data={name:e.querySelector('#name').value, email:e.querySelector('#email').value, msg:e.querySelector('#msg').value, lang:'<?=$this->router->language?>'};
            console.log(data);
            if(data.name.length<3){alert('name is too short!');return false;}
            if(data.msg.length<10){alert('message is too short!');return false;}            
            
            let opt={method:'POST',mode:'same-origin',credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json'}};
            opt['body']=JSON.stringify(data);
            fetch('/ajax-contact/', opt).then(res => res.json()).then(response => {
                console.log('Success:', response);
                if (response.success===1) {
                    alert(response.result);
                    history.go(-1);
                }
                else {
                    alert(response.error);
                }
            })
            .catch(error => {
                console.log('Error:', error);
            });
        };
        </script><?php
        
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

    
    function contactHeader() {
        $countryId=0;
        $cityId=0;
        $countryName=$this->countryName;
        if (isset($this->user->params["country"]) && $this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->router->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        
        ?><div class='sum rc'><div class="brd"><?php
        echo "<a href='{$this->router->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1><?= $this->lang['title'] ?></h1></div><?= $this->lang['header'] ?></div><?php
    }
    
}
