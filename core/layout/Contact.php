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
    }
   

    function side_pane() {      
        $this->renderSideSite();
    }

    
    function main_pane(){            
        $rtl=$this->router->isArabic();
        $name=''; $email=''; $message='';
        if (isset($_GET['payfort']) && is_numeric($_GET['payfort'])) {
            $message=$this->lang['payfort_fail_msg'].'#'.$_GET['payfort'].'#';
        }
        
        if ($this->user()->isLoggedIn()) {
            $name=$this->user->info['name'];
            if (isset ($this->user->info['email']) && strpos($this->user->info['email'], "@")!==false) $email=$this->user->info['email'];
        }
        ?><div class="row viewable"><div class="row pc mt-32"></div><?php
        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        
        ?><div class=cw10><div class="card doc"><div class=view><?php
        ?><h2 class=title><?=$rtl?'راسلنا ولَو بسطر!':'Drop us a line!'?></h2><?php
        // $this->lang['header']
        $subTitle=$rtl?'عندك سؤال؟ تعليق؟ لم تجد ما كنت تبحث عنه؟ دعنا نعرف...':"Have a question? Feedback? Haven't found what you've been looking for? Let us know...";
        ?><p class=fw-300 style="font-size:23px;line-height:1.5em;margin:-40px 0 16px"><?=$subTitle?></p><div class="flex w100"><?php
        ?><form class="w100 mb-32" onsubmit="vf(this);return false;"><?php
        ?><div class=row style="margin-top:1em"><div class="bluebar group mw"><?php
            ?><input type=text required onkeydown="dirElem(this)" onchange="dirElem(this)" type=text id=name value="<?= $name ?>" <?= $name ? "readonly":"" ?> /><?php
            if (!$this->user()->isLoggedIn()) {
                echo '<label>', $this->lang['hint_name'], '</label>';
            }
            ?><span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
            
        ?><div class=row><div class="bluebar group mw">
            <input type=email required onkeydown="dirElem(this)" onchange="dirElem(this)" onkeyup="this.setAttribute('value', this.value);" id=email value="<?= $email ?>" <?= $email ? "disabled='disabled'":"" ?> /><?php
            if (!$this->user()->isLoggedIn()) {
                echo '<label>', $this->lang['hint_email'], '</label>';
            }
            ?><span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
        
        ?><div class=row><div class="group mw">
            <textarea required onkeydown="dirElem(this)" onchange="dirElem(this,1)" rows=10 id=msg><?= $message ?></textarea>
            <label><?= $this->lang['message'] ?></label>
            <span class=highlight></span>
            <span class=bar></span>
        </div></div><?php
        
        ?><button class=btn type=submit style="min-width:160px;height:60px;font-weight:500;font-size:18px"><?=$this->lang['send']?></button><?php
        ?></form><span class="omail <?= $this->router->language ?>"></span><span class="nb"></span></div><?php
        
        ?></div><?php
        ?><div class=page-footer><?php
        ?><div><img alt="mourjan" style="width:148px;margin-top:80px" src="<?=$this->router->config->cssURL?>/1.0/assets/domain.svg" /></div><?php
        ?><div class=col-12 style="flex-flow:row;justify-content:flex-end;padding:0;margin:0;overflow:hidden"><img style="position:relative;top:56px;width:206px;transform:rotateX(180deg);filter:invert(36%) sepia(39%) saturate(7153%) hue-rotate(200deg) brightness(102%) contrast(106%);" src="<?=$this->router->config->cssURL?>/1.0/assets/emblem.svg"/></div><?php
        ?></div><?php
        ?></div></div></div><?php
        
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
                    Swal.fire('Done!', response.result, 'success' ).then((result)=>{history.go(-1);});
                }
                else {
                    Swal.fire('Error', response.error, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', error, 'error');
            });
        };
        </script><?php               
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
