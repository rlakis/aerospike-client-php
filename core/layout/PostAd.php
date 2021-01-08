<?php
Config::instance()->incLayoutFile('Page');

class PostAd extends Page {
    private \Core\Model\Ad $ad;
    private int $userBalance=0;
        
    
    function __construct() {
        parent::__construct();
        
        if ($this->router->config->isMaintenanceMode()) {
            $this->user()->redirectTo($this->router->getLanguagePath('/maintenance/'));
        }
        
        $this->checkBlockedAccount();
        $this->checkSuspendedAccount();
                 
        $this->router->config->setValue('enabled_sharing', 0);
        $this->router->config->disableAds();               
        
        $this->load_lang(array("post"));
        //$this->set_require('css', array('post'));
        $this->title=$this->lang['title'];

        if ($this->user->params['country']) {
            $this->router->countryId=$this->user->params['country'];
        }
        if ($this->user->params['city']) {
            $this->router->cityId=$this->user->params['city'];
        }
        
        $this->ad=new Core\Model\Ad();
        $this->hasLeadingPane=$this->user()->isLoggedIn() && !$this->isUserMobileVerified;
        
        if ($this->user->isLoggedIn()) {
            if (!$this->isUserMobileVerified) {
                $this->title=$this->lang['verify_mobile'];
                //$this->set_require('css', array('select2'));                
            }
            else {
                $id=\filter_input(\INPUT_POST, 'ad' , \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]);
                if ($id>0) {
                    $this->ad->getAdFromAdUserTableForEditing($id);     
                }

                if (isset($_REQUEST['adr']) && is_numeric($_REQUEST['adr'])) {                    
                    //$res = $this->user->renewAd($_POST['adr'],0);
                    $id = $_REQUEST['adr'];
                    if ($id) {
                        $this->user->holdAd($id);
                        $this->ad=$this->user->loadAdToSession($id);
                        $this->id=$this->user->pending['post']['id']; 
                        $this->user->pending['post']['state']=0;
                        $this->countryId=$this->user->pending['post']['cn'];
                        $this->cityId=$this->user->pending['post']['c'];
                        $this->sectionId=$this->user->pending['post']['se'];
                        $this->purposeId=$this->user->pending['post']['pu'];
                        $this->rootId=$this->user->pending['post']['ro'];
                        $this->adContent=\json_decode($this->user->pending['post']['content'],true);                       
                        $this->user->update();
                    }
                }
            }

            //$stmt = $this->user()->getStatement(0, 0, true);
            //if (isset($stmt['balance'])) {
            //    $this->userBalance=$stmt['balance'];
            //}
            $this->userBalance=$this->ad->profile()->getBalance();
        }

        $this->render();
    }  
    
    
    protected function ad() : Core\Model\Ad {
        return $this->ad;
    }
    
        
    function mainMobile() {
        if (!$this->user()->isLoggedIn()) { return; }
            
        if ($this->user->getProfile()->isMobileVerified()) {
            $activation_country_code='';
            $number=$this->user()->getProfile()->getMobileNumber();
            if ($number>0) {
                $numberValidator=\libphonenumber\PhoneNumberUtil::getInstance();
                $num=$numberValidator->parse($number, 'LB');
                $activation_country_code=$numberValidator->getRegionCodeForNumber($num);
            }
           
            
            $current_country_code=isset($this->router->countries[$this->router->countryId]['uri']) ? \strtoupper($this->router->countries[$this->router->countryId]['uri']) : '';
            
            $ip=IPQuality::fetchJson(false)['ipquality'];
            $data_attrs ="data-id={$this->ad()->id()} data-ip-country={$ip['country_code']} data-cur-country={$current_country_code} ";
            $data_attrs.="data-act-country={$activation_country_code} data-recent-abuse={$ip['recent_abuse']} data-proxy={$ip['proxy']} ";
            $data_attrs.="data-tor={$ip['tor']} data-vpn={$ip['vpn']} data-score={$ip['fraud_score']}";
                
            ?><div class="row viewable"><?php
            ?><form id=adForm action="" onsubmit="window.event.preventDefault(); return false;" method=post <?=$data_attrs?>><?php
      
            
            if ($this->user->level()===9) {                    
                ?><div class=col-12><div class=card><?php
                ?><div class=card-content><a class="btn blue float-right" href="javascript:void(0)" onclick="toLower()">Lower case</a></div><?php  
                ?></div></div><?php
            }
                
            //pictures
            ?><div class=col-12><div class=card><div class="card-content pictures"><?php
            for ($i=0; $i<5; $i++){
                ?><span class=pix data-index="<?=$i?>"><progress max=100 value=0></progress></span><?php
            }
            ?></div></div></div><?php
            
            ?><div class=col-12><div class=card><div class=card-content><?php
            ?><textarea id=natural placeholder="Enter ad text"></textarea><?php
            ?></div></div></div><?php
            
            ?><div class=col-12><div class=card><div class="card-content"><?php
            ?><textarea id=foreign placeholder=""></textarea><?php
            ?></div></div></div><?php

            echo '<div class=col-12><div id=ad-class class=card><ul>';
            echo '<li><a class=ro href="javascript:void(0)" onclick="UI.chooseRootPurpose()">Choose listing section</a></li>';
            echo '<li><a class=se href="javascript:void(0)" onclick="UI.chooseSection()">Choose section</a></li>';
            echo '<li><a class=lc href="javascript:void(0)" onclick="UI.openMap()">Map Address/Location</a></li>';
            echo '<li><a class=rg href="javascript:void(0)" onclick="UI.chooseRegions()">', $this->lang['m_h_city'], '</a></li>';
            echo '</ul></div></div>';
            
            ?><div class=col-12><div class=card><?php
            ?><ul>
                <li>
                <div class=select>
                <select name=cui class=select-text>
                    <option value=1><?= $this->lang['contact_h_1'] ?></option>
                    <option value=3><?= $this->lang['contact_h_3'] ?></option>
                    <option value=5><?= $this->lang['contact_h_5'] ?></option>
                    <option value=7><?= $this->lang['contact_h_7'] ?></option>
                </select>
                </div>
                <input type=tel class=field name=phone placeholder="+961 3 287 nnn" maxlength=22 data-no=1>
                </li>

                <li>
                <div class=select>
                <select name=cui class=select-text>
                    <option value=1><?= $this->lang['contact_h_1'] ?></option>
                    <option value=3><?= $this->lang['contact_h_3'] ?></option>
                    <option value=5><?= $this->lang['contact_h_5'] ?></option>
                    <option value=7><?= $this->lang['contact_h_7'] ?></option>
                </select>
                </div>                                        
                <input type=tel class=field name=phone placeholder="+966 55 123 nnnn" maxlength=22 data-no=2>
                </li>
                <li><span style="width:180px"><?= $this->lang['contact_h_10'] ?></span><input type=email class=field id=email placeholder="name@gmail.com" data-value-missing=”This field is required"></li>
            </ul><?php
            ?></div></div><?php
            
            ?><div class=col-12><div class=card><?php
            ?><ul class=buttons><?php
                //<!--<li><button name="submit" type="submit" id="ad-submit" class="btn blue" onclick="return UI.submit(this)" data-submit="...Sending">Submit</button></li>-->
                ?><li><button onclick=Ad.save() class="btn blue" data-state=0>Save Changes</button></li><?php                
                if ($this->userBalance>0 && ($this->user()->level()===9 || ($this->ad()->uid()===$this->user()->id()) )){
                    $max=min([60, $this->userBalance]);
                    ?><li><?php
                    ?><div class="welcome va-center" style="width:-webkit-fill-available;padding:16px 32px 0;margin:0;background:white"><?php
                    ?><div class="flex va-center fw-300"><span class="empty-coin two"><?=$this->userBalance?></span>DAYS LEFT</div><?php
                    ?><div><?php
                    ?><input id=budget name=budget type=range min=0 max="<?=$max?>" value=<?=$this->ad->dataset()->getBudget()?> style="width:300px;margin-inline-end:8px;" oninput="this.nextElementSibling.value=this.value+' day(s)';Ad.content.budget=parseInt(this.value)"><output><?=$this->ad->dataset()->getBudget()?> day(s)</output><?php
                    ?></div></div><?php
                        
                    ?><div id="make_premium" class="dialog premium"><?php                       
                    ?><div class="dialog-hint"><?= $this->lang['premium_hint'] ?></div><?php                                                 
                    ?></div><?php
                        
                    ?><button onclick=Ad.save() class="btn blue" data-state=4><?=$this->lang['publish_ad_premium']?></button></li><?php
                }

                
                ?><li><button onclick=Ad.save() class="btn blue" data-state=1>Publish Regular</button><span><?= $this->lang['ad_review'] ?></span></li><?php
                if($this->user()->level()===9){
                    ?><li class=approve><button onclick=Ad.save() class="btn blue" data-state=2><?= $this->lang['approve'] ?></button></li><?php
                }
                ?><li><button class=btn onclick="javascript:history.back();">Cancel</button></li>
            </ul><?php
            ?></div></div><?php
            
            ?></form><?php
            ?></div><?php
            
        }
        else { // unverified user
            ?>
<style>
.group{position:relative;margin-bottom:36px;flex-grow: 1}
.group input,.group select{font-size:1rem;padding:10px 10px 10px 5px;display:block;width:100%;border:1px solid var(--mdc12);resize:none;outline:none;color:var(--mdc70)}
.group .btn{color: white}
.group>input:focus{outline:none;}
.group label{color:var(--mdc70);font-size:16px;font-weight:300;position:absolute;pointer-events:none;margin-inline-start:5px;top:8px;transition:0.2s ease all;-moz-transition:0.2s ease all;-webkit-transition:0.2s ease all;}
.group input:focus ~ label, input:valid ~ label, input:disabled ~ label {top:-24px;font-size:15px;color:#5264AE;}
.group .bar{position:relative; display:block; width:100%;}
.group .bar:before, .bar:after{content:'';height:2px;width:0;bottom:1px;position:absolute;background:var(--mlc);transition:0.2s ease all;-moz-transition:0.2s ease all;-webkit-transition:0.2s ease all;}
.group .bar:before{left:50%;}
.group .bar:after{right:50%;}
.group input:focus ~ .bar:before, input:focus ~ .bar:after{width:50%;}
.group .highlight{position:absolute;height:60%;width:100%;top:25%;left:0;pointer-events:none;opacity:0.5;}
.group input:focus ~ .highlight{-webkit-animation:inputHighlighter 0.3s ease;-moz-animation:inputHighlighter 0.3s ease;animation:inputHighlighter 0.3s ease;}
input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {-webkit-appearance: none;margin:0;}
input[type=number]{-moz-appearance: textfield;}
.digit{width:18px;border:0; border-bottom:1px solid var(--mdc50);height:32px;outline:none;font-size:16px;font-weight:500; color:var(--mdc70);text-align:center;margin:0;padding:0}
.digit + .digit{margin-inline-start: 8px;}
.card.holder{padding:32px 64px 64px;color: inherit}
@media only screen and (max-width:768px) {
    .card{box-shadow: none}
    .card.holder{padding:8px 12px 64px;color: inherit}
    .card-content{padding:8px 0}
}
</style>
            <?php
            $this->inlineJS('phone-number');
            ?><div class="viewable ha-center"><div class="col-10"><?php
            $q='select code, id, name_'.$this->router->language.', locked, trim(id_2) from country where id!=109 order by locked desc, name_'.$this->router->language;
            $cc=$this->router->db->queryCacheResultSimpleArray('country_codes_req_'.strtolower($this->router->language), $q);
            
            ?><div class="card holder"><div class="alert"></div><div class="card-content ff-cols"><?php
           
            ?><p class="mb-32"><?=$this->lang['notice_mobile_required']?></p><?php 
            
            ?><div id=mb_notice class=ff-cols style="display:flex;align-self:center"><?php 
            
            ?><div class=group><?php
            ?><select id=code style="height:42px;font-family:inherit;"><?php 
                foreach($cc as $country){
                    $country[2]=preg_replace('/\x{200E}/u','',trim($country[2]));
                    ?><option value="<?=$country[4]?>"<?=$this->user->params['country']==$country[1]?' selected':''?>><?=$country[2]?> (<?=($this->router->language==='ar'?'':'+').$country[0].($this->router->language==='ar'?'+':'')?>)</option><?php
                }
            ?></select><?php
            ?></div><?php
            
            ?><div class=group><input class=en type=tel id=number oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');" onkeyup="keyChanged(this);" required value="<?= isset($this->user->pending['mobile']) ? '+'.$this->user->pending['mobile'] : '' ?>"><label><?=$this->lang['your_mobile']?></label><span class=highlight></span><span class=bar></span></div><?php                                                                    
            ?><div class=group><input class=btn type=button onclick="numberCheck(this)" value="<?=$this->lang['continue']?>" /></div><?php
            
            
            ?></div><?php
            
            ?><div id=via class="row ff-cols none"><?php
                ?><p class="alert alert-info"><?=$this->lang['choose_mobile_validation']?></p><?php 
                ?><div><?php
                    ?><p>1. <?=$this->lang['validate_mobile_by_call']?></p><?php
                    ?><p class=group><input type=button onclick="verify(this)" value="<?=$this->lang['call_me']?>" class=btn data-method="1" /></p><?php
                ?></div><?php
                ?><div><?php
                    ?><p>2. <?=$this->lang['validate_mobile_by_sms']?></p><?php
                    ?><p class=group><input type=button onclick="verify(this)" value="<?=$this->lang['send_code']?>" class=btn data-method="0" /></p><?php
                ?></div><?php
            ?></div><?php
            
            ?><div id=pin class="row ff-cols va-center none"><?php
            ?><div id=hint style="font-weight:300" data-sms="Enter the code has been sent by SMS" data-rvc="Enter the last four digits of the phone number which called you"></div><?php
                ?><div><?php
                    ?><input id=d1 class="digit" type="number" maxlength="1" min="0" max="9" data-index="1" /><?php
                    ?><input id=d2 class="digit" type="number" maxlength="1" min="0" max="9" data-index="2" /><?php
                    ?><input id=d3 class="digit" type="number" maxlength="1" min="0" max="9" data-index="3" /><?php
                    ?><input id=d4 class="digit" type="number" maxlength="1" min="0" max="9" data-index="4" /><?php
                ?></div><?php
            ?></div><?php
            
            
            ?></div></div><?php
            
            ?></div></div><?php         
        }                                           
    }
        
    
    function side_pane(){
        //$this->renderSideUserPanel();
        //if (!$this->user->info['id'] || !$this->isUserMobileVerified){
            $this->renderSideSite();
        //}
    }

    
    function main_pane() {
        if ($this->user()->isLoggedIn()) {
            if (!$this->router->config->get('enabled_post')) {
                $this->renderDisabledPage();
                return;
            }
            
            ?><div class=row><div class=col-12><div id=main><?php
            $this->mainMobile();    
            ?></div></div></div><?php
            
            ?><div id=adLocation class=row style="display:none;height:100%;flex-flow:column"><?php
            ?><div class=col-12 style="padding:0 0 4px;height:46px"><?php
            ?><div class=search><?php
            ?><form onsubmit="event.preventDefault(); return MAP.search(this);"><?php
            ?><input id=q name=q class=searchTerm type=search placeholder="enter location name are you looking for?"><?php
            ?><button class=searchButton type=submit><i class="icn icnsmall icn-search invert"></i></button><?php
            ?><button class=searchButton type=button style="margin:0 0 0 8px;"><i class="icn icnsmall icn-map-marker" onclick="MAP.myLocation();"></i></button><?php
            ?><button class="btn blue" type=button style="margin:0 0 0 8px;height:36px" onclick="MAP.confirm();">Confirm</button><?php
            ?><button class="btn blue" type=button style="margin:0 8px;height:36px" onclick="MAP.remove();">Remove</button><?php
            ?><button class="btn blue" type=button style="margin:0;height:36px" onclick="UI.close();">Cancel</button><?php
            ?></form><?php
            ?></div><?php
            ?></div><?php
            ?><div class=col-12 style="padding:0;height:100%"><div id=gmapView style="width:100%;height:100%"></div></div><?php
            ?></div><?php
            
            $this->inlineJS('util.js')->inlineJS('post.js');
        }
        else {
            $this->renderLoginPage();
        }
    }

}
