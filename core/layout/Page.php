<?php
include_once $config['dir'].'/core/layout/Site.php';


class Page extends Site{
    protected $action='';
    protected $requires=array('js'=>array(),'css'=>array());
    protected $title='',$description='';
    protected $rss = false;
    var $isUserMobileVerified = false;
    var $stat;
    var $pageUri = '';
    var $googleAds=array();
    var $fieldNameIndex;
    var $deprecated=false,$topMenuIE=false;    
    var $appendLocation=true;
    var $hasLeadingPane=false;
    var $png_fix=false;
    var $hasCities=false; 
    var $notifications=array();
    var $blocks=array();
    var $partnerInfo=null,$hasPartnerInfo=false,$pagePreview=false, $searchResults=false, $userFavorites=false;
    var $extended=null,$extendedId=0,$localities=null,$parentLocalities=null,$cityParentLocalityId=0,$localityId=0,$localityParentId=0,$extended_uri='';

    var $countryCounter='',$inlineScript='',$inlineQueryScript='',$globalScript='',$cssImgsLoaded=false, $inlineCss='';
    var $detailAd, $isNewMobile=false, $detailAdExpired=false,$requireLogin=false,$forceNoIndex=false,$isAdminSearch=0;
    var $cityName='',$rootName='', $countryName='', $categoryName='', $sectionName='', $purposeName='',$backLink='';
    
    var $pageItemScope='itemscope itemtype="http://schema.org/WebPage"';

    function __construct($router){
        parent::__construct($router); 
        if($this->user->info['id']){
            if($this->urlRouter->isApp){
                $this->isUserMobileVerified = true;
            }elseif ($this->user->info['level']==9 && $this->user->info['id']!=1 && $this->user->info['id']!=2){
                $this->isUserMobileVerified = true;
            }else{
                $this->isUserMobileVerified = (isset($this->user->info['verified']) && $this->user->info['verified']);
            }
        }
        
        /*$row = $this->urlRouter->db->queryResultArray(
                                "select * from web_users_device where uuid = ?", ['FB963563-7201-4E44-BEFD-D44BC32BA3DB']);
        var_dump($row);*/
        
        $cdn = $this->urlRouter->cfg['url_resources'];
        
        if (isset($this->user->params['user_country']))
        {
            if ($this->user->params['user_country']==='lb'||
                $this->user->params['user_country']==='ae'||
                $this->user->params['user_country']==='bh'||
                $this->user->params['user_country']==='sa'||
                $this->user->params['user_country']==='eg'||                    
                $this->user->params['user_country']==='us')
            {

                //$cdn = "https://mourjan.r.worldssl.net";
                $cdn = "https://cdn.mourjan.com";
                //$cdn = "https://www.mourjan.com";
                //$cdn = "https://dv.mourjan.com";
            }
        }        
        $this->urlRouter->cfg['url_resources']      = $cdn;
        $this->urlRouter->cfg['url_ad_img']         = $cdn;
        if(strpos($this->urlRouter->cfg['url_img'], 'http')===false){
            $this->urlRouter->cfg['url_img']            = $cdn.$this->urlRouter->cfg['url_img'];
        }
        if(strpos($this->urlRouter->cfg['url_js'], 'http')===false){
            $this->urlRouter->cfg['url_js']             = $cdn.$this->urlRouter->cfg['url_js'] ;
        }
        if(strpos($this->urlRouter->cfg['url_js_mobile'], 'http')===false){
            $this->urlRouter->cfg['url_js_mobile']      = $cdn.$this->urlRouter->cfg['url_js_mobile'];
        }
        if(strpos($this->urlRouter->cfg['url_css'], 'http')===false){
            $this->urlRouter->cfg['url_css']            = $cdn.$this->urlRouter->cfg['url_css'];
        }
        if(strpos($this->urlRouter->cfg['url_css_mobile'], 'http')===false){
            $this->urlRouter->cfg['url_css_mobile']     = $cdn.$this->urlRouter->cfg['url_css_mobile'];
        }
        if(strpos($this->urlRouter->cfg['url_jquery'], 'http')===false){
            $this->urlRouter->cfg['url_jquery']         = $cdn.$this->urlRouter->cfg['url_jquery'] ;
        }
        if(strpos($this->urlRouter->cfg['url_jquery_mobile'], 'http')===false){
            $this->urlRouter->cfg['url_jquery_mobile']  = $cdn.$this->urlRouter->cfg['url_jquery_mobile'];
        }
        if(strpos($this->urlRouter->cfg['url_image_lib'], 'http')===false){
            $this->urlRouter->cfg['url_image_lib']      = $cdn.$this->urlRouter->cfg['url_image_lib'];
        }
        if(strpos($this->urlRouter->cfg['url_highcharts'], 'http')===false){
            $this->urlRouter->cfg['url_highcharts']     = $cdn.$this->urlRouter->cfg['url_highcharts'];
        }
        
        
        
        //$this->urlRouter->cfg['url_js'] = 'https://dv.mourjan.com/web/js/1.0.0';
        //$this->urlRouter->cfg['url_css'] = 'https://dv.mourjan.com/web/css/1.0.0';
        //$this->urlRouter->cfg['url_jquery'] = 'https://dv.mourjan.com/web/jquery/3.1.0/js/';
        ////$this->urlRouter->cfg['url_js_mobile'] = 'https://dv.mourjan.com/web/js/2.0.0';
        //$this->urlRouter->cfg['url_js_mobile'] = 'https://dv.mourjan.com/web/js/release';
        ////$this->urlRouter->cfg['url_css_mobile'] = 'https://dv.mourjan.com/web/css/1.0.1';
        ////$this->urlRouter->cfg['url_jquery_mobile'] = 'https://dv.mourjan.com/web/jquery/4.0.0/js/';
        
        //$this->urlRouter->cfg['url_css'] = '/web/css/release';
        //header("Link: <{$this->urlRouter->cfg['url_css']}/gen_ar.css>; rel=preload; as=style;", false);
        //header("Link: <{$this->urlRouter->cfg['url_css']}/imgs.css>; rel=preload; as=style;", false);
        //header("Link: </web/css/release/imgs.css>; rel=preload; as=stylesheet;", false);
        //header("Link '<{$this->urlRouter->cfg['url_css']}/imgs.css>; rel=preload; as=stylesheet';");        
        
        //$this->user->sysAuthById(515496);
        if(!$this->urlRouter->cfg['enabled_users']){
            if($this->urlRouter->siteLanguage == 'ar'){
                $this->setNotification('مرجان يواجه بعض المشاكل التقنية والتي يتم معالجتها حالياً. شكراً لتحليكم بالصبر.');
            }else{
                $this->setNotification('mourjan is facing a technical problem which we are working to resolve. Thank you for being patient.');
            }
        }
        if(isset($this->user->params['hasCanvas']) && $this->user->params['hasCanvas']==0){
            $router->cfg['enabled_charts']=0;
        }
        if(($this->urlRouter->module=='search' || $this->urlRouter->module=='detail') && !$this->isMobile){
            $this->inlineCss.='
            .big{font-size:20px;height:auto!important}
            .big a,.big b{padding:10px 5px !important;font-weight:normal!important;}
                ';
        }
        if ($this->urlRouter->isMobile) {
            $this->inlineCss.='.g-recaptcha{display:inline-block;min-height:78px}li.recap{text-align:center}';
            if(date('d-m')=='14-02'){
                if($this->urlRouter->module=='index' || $this->urlRouter->module=='search' || $this->urlRouter->module=='detail'){
                    $this->inlineCss.='body{background:url('.$this->urlRouter->cfg['url_css'].'/i/iv.png) repeat top left}';
                }
            }
        }else{
            if(date('d-m')=='14-02'){
                if($this->urlRouter->module=='index' || $this->urlRouter->module=='search' || $this->urlRouter->module=='detail'){
                    $this->inlineCss.='.ad,.aps,.dt{background-color:#FFF}.colw,.col2w,.tpb{background:url('.$this->urlRouter->cfg['url_css'].'/i/iv.png) repeat top left}';
                }
            }
            if($this->urlRouter->siteLanguage=='ar'){
                $this->inlineCss.='.g-recaptcha{float:right}';
            }else{
                $this->inlineCss.='.g-recaptcha{float:left}';
            }
            $this->inlineCss.='.lgs .cap{padding:0;margin:10px 0;min-height: 73px}ul.dpr, ul.drp{width:302px}';
        }
//        if ($this->user->info['id'] && ($this->user->info['id']==38813 || $this->user->info['id']==2100)){
//        if ($this->user->info['id'] && ($this->user->info['id']==1)){
//            $this->user->info['level']=0;
//            $this->user->update();
//        }
        if(!in_array($this->urlRouter->countryId,$this->urlRouter->cfg['iso_countries'])){
            $this->urlRouter->countryId=0;
            $this->urlRouter->cityId=0;
            if($this->urlRouter->module!='index'){
                $this->forceNoIndex=1;
            }
        }
        if ($this->urlRouter->params['rss'] && ($this->urlRouter->module=='search' || $this->urlRouter->module=='watchlist')) {
            $this->rss = TRUE;
        }
        if ($this->urlRouter->module=='watchlist' || $this->urlRouter->module=='favorites'){
            if($this->user->info['id']){
                //if (!$this->urlRouter->isMobile) $this->inlineCss.='.list{padding-top:5px}';
                $this->pageUserId = $this->user->info['id'];
            }elseif($tmp = $this->get('u')){
                //if (!$this->urlRouter->isMobile) $this->inlineCss.='.list{padding-top:5px}';
                $this->pageUserId = $this->user->decodeId($tmp);
            }
        }
        if ($this->urlRouter->siteTranslate) {
            if (in_array($this->urlRouter->siteTranslate,array('ar','en'))) {
                $this->urlRouter->siteLanguage=$this->urlRouter->siteTranslate;
            }
        }
        
        if($this->urlRouter->module!='post' && isset($this->user->pending['post']))
            unset($this->user->pending['post']);

        $this->load_lang(array('main'));
        $this->title = $router->pageTitle[$router->siteLanguage];
        if (!$this->title) $this->title = $this->lang['title_full'];        
        
        $this->fieldNameIndex=1+$this->lnIndex;

        $this->checkUserData();
        if ($this->urlRouter->isMobile) {
            $this->set_require('css', array('main'));
            //$this->set_require('css', array('mob'));
            $this->isMobileAd=true;
            $this->isMobile=true;
            $this->appendLang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
            
            /*
            if(in_array($this->urlRouter->module,array('index','search','detail','contact'))){
                if ( (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1)
                        || (isset($this->user->params['mobile_android_app_bottom_banner']) && $this->user->params['mobile_android_app_bottom_banner']==1) ){
                    $this->inlineCss.='#footer{margin-bottom:70px}';
                }
            }
            
             * 
             */
            
            
            //$this->globalScript.="function FastClick(layer){'use strict';var oldOnClick,self=this;this.trackingClick=false;this.trackingClickStart=0;this.targetElement=null;this.touchStartX=0;this.touchStartY=0;this.lastTouchIdentifier=0;this.touchBoundary=10;this.layer=layer;if(!layer||!layer.nodeType){throw new TypeError('Layer must be a document node')}this.onClick=function(){return FastClick.prototype.onClick.apply(self,arguments)};this.onMouse=function(){return FastClick.prototype.onMouse.apply(self,arguments)};this.onTouchStart=function(){return FastClick.prototype.onTouchStart.apply(self,arguments)};this.onTouchMove=function(){return FastClick.prototype.onTouchMove.apply(self,arguments)};this.onTouchEnd=function(){return FastClick.prototype.onTouchEnd.apply(self,arguments)};this.onTouchCancel=function(){return FastClick.prototype.onTouchCancel.apply(self,arguments)};if(FastClick.notNeeded(layer)){return}if(this.deviceIsAndroid){layer.addEventListener('mouseover',this.onMouse,true);layer.addEventListener('mousedown',this.onMouse,true);layer.addEventListener('mouseup',this.onMouse,true)}layer.addEventListener('click',this.onClick,true);layer.addEventListener('touchstart',this.onTouchStart,false);layer.addEventListener('touchmove',this.onTouchMove,false);layer.addEventListener('touchend',this.onTouchEnd,false);layer.addEventListener('touchcancel',this.onTouchCancel,false);if(!Event.prototype.stopImmediatePropagation){layer.removeEventListener=function(type,callback,capture){var rmv=Node.prototype.removeEventListener;if(type==='click'){rmv.call(layer,type,callback.hijacked||callback,capture)}else{rmv.call(layer,type,callback,capture)}};layer.addEventListener=function(type,callback,capture){var adv=Node.prototype.addEventListener;if(type==='click'){adv.call(layer,type,callback.hijacked||(callback.hijacked=function(event){if(!event.propagationStopped){callback(event)}}),capture)}else{adv.call(layer,type,callback,capture)}}}if(typeof layer.onclick==='function'){oldOnClick=layer.onclick;layer.addEventListener('click',function(event){oldOnClick(event)},false);layer.onclick=null}}FastClick.prototype.deviceIsAndroid=navigator.userAgent.indexOf('Android')> 0;FastClick.prototype.deviceIsIOS=/iP(ad|hone|od)/.test(navigator.userAgent);FastClick.prototype.deviceIsIOS4=FastClick.prototype.deviceIsIOS&&(/OS 4_\d(_\d)?/).test(navigator.userAgent);FastClick.prototype.deviceIsIOSWithBadTarget=FastClick.prototype.deviceIsIOS&&(/OS([6-9]|\d{2})_\d/).test(navigator.userAgent);FastClick.prototype.needsClick=function(target){'use strict';switch(target.nodeName.toLowerCase()){case 'button':case 'select':case 'textarea':if(target.disabled){return true}break;case 'input':if((this.deviceIsIOS&&target.type==='file')||target.disabled){return true}break;case 'label':case 'video':return true}return(/\bneedsclick\b/).test(target.className)};FastClick.prototype.needsFocus=function(target){'use strict';switch(target.nodeName.toLowerCase()){case 'textarea':return true;case 'select':return !this.deviceIsAndroid;case 'input':switch(target.type){case 'button':case 'checkbox':case 'file':case 'image':case 'radio':case 'submit':return false}return !target.disabled&&!target.readOnly;default:return(/\bneedsfocus\b/).test(target.className)}};FastClick.prototype.sendClick=function(targetElement,event){'use strict';var clickEvent,touch;if(document.activeElement&&document.activeElement !==targetElement){document.activeElement.blur()}touch=event.changedTouches[0];clickEvent=document.createEvent('MouseEvents');clickEvent.initMouseEvent(this.determineEventType(targetElement),true,true,window,1,touch.screenX,touch.screenY,touch.clientX,touch.clientY,false,false,false,false,0,null);clickEvent.forwardedTouchEvent=true;targetElement.dispatchEvent(clickEvent)};FastClick.prototype.determineEventType=function(targetElement){'use strict';if(this.deviceIsAndroid&&targetElement.tagName.toLowerCase()==='select'){return 'mousedown'}return 'click'};FastClick.prototype.focus=function(targetElement){'use strict';var length;if(this.deviceIsIOS&&targetElement.setSelectionRange&&targetElement.type.indexOf('date')!==0&&targetElement.type !=='time'){length=targetElement.value.length;targetElement.setSelectionRange(length,length)}else{targetElement.focus()}};FastClick.prototype.updateScrollParent=function(targetElement){'use strict';var scrollParent,parentElement;scrollParent=targetElement.fastClickScrollParent;if(!scrollParent||!scrollParent.contains(targetElement)){parentElement=targetElement;do{if(parentElement.scrollHeight > parentElement.offsetHeight){scrollParent=parentElement;targetElement.fastClickScrollParent=parentElement;break}parentElement=parentElement.parentElement}while(parentElement)}if(scrollParent){scrollParent.fastClickLastScrollTop=scrollParent.scrollTop}};FastClick.prototype.getTargetElementFromEventTarget=function(eventTarget){'use strict';if(eventTarget.nodeType===Node.TEXT_NODE){return eventTarget.parentNode}return eventTarget};FastClick.prototype.onTouchStart=function(event){'use strict';var targetElement,touch,selection;if(event.targetTouches.length > 1){return true}targetElement=this.getTargetElementFromEventTarget(event.target);touch=event.targetTouches[0];if(this.deviceIsIOS){selection=window.getSelection();if(selection.rangeCount&&!selection.isCollapsed){return true}if(!this.deviceIsIOS4){if(touch.identifier===this.lastTouchIdentifier){event.preventDefault();return false}this.lastTouchIdentifier=touch.identifier;this.updateScrollParent(targetElement)}}this.trackingClick=true;this.trackingClickStart=event.timeStamp;this.targetElement=targetElement;this.touchStartX=touch.pageX;this.touchStartY=touch.pageY;if((event.timeStamp - this.lastClickTime)< 200){event.preventDefault()}return true};FastClick.prototype.touchHasMoved=function(event){'use strict';var touch=event.changedTouches[0],boundary=this.touchBoundary;if(Math.abs(touch.pageX - this.touchStartX)> boundary||Math.abs(touch.pageY - this.touchStartY)> boundary){return true}return false};FastClick.prototype.onTouchMove=function(event){'use strict';if(!this.trackingClick){return true}if(this.targetElement !==this.getTargetElementFromEventTarget(event.target)||this.touchHasMoved(event)){this.trackingClick=false;this.targetElement=null}return true};FastClick.prototype.findControl=function(labelElement){'use strict';if(labelElement.control !==undefined){return labelElement.control}if(labelElement.htmlFor){return document.getElementById(labelElement.htmlFor)}return labelElement.querySelector('button,input:not([type=hidden]),keygen,meter,output,progress,select,textarea')};FastClick.prototype.onTouchEnd=function(event){'use strict';var forElement,trackingClickStart,targetTagName,scrollParent,touch,targetElement=this.targetElement;if(!this.trackingClick){return true}if((event.timeStamp - this.lastClickTime)< 200){this.cancelNextClick=true;return true}this.cancelNextClick=false;this.lastClickTime=event.timeStamp;trackingClickStart=this.trackingClickStart;this.trackingClick=false;this.trackingClickStart=0;if(this.deviceIsIOSWithBadTarget){touch=event.changedTouches[0];targetElement=document.elementFromPoint(touch.pageX - window.pageXOffset,touch.pageY - window.pageYOffset)||targetElement;targetElement.fastClickScrollParent=this.targetElement.fastClickScrollParent}targetTagName=targetElement.tagName.toLowerCase();if(targetTagName==='label'){forElement=this.findControl(targetElement);if(forElement){this.focus(targetElement);if(this.deviceIsAndroid){return false}targetElement=forElement}}else if(this.needsFocus(targetElement)){if((event.timeStamp - trackingClickStart)> 100||(this.deviceIsIOS&&window.top !==window&&targetTagName==='input')){this.targetElement=null;return false}this.focus(targetElement);if(!this.deviceIsIOS4||targetTagName !=='select'){this.targetElement=null;event.preventDefault()}return false}if(this.deviceIsIOS&&!this.deviceIsIOS4){scrollParent=targetElement.fastClickScrollParent;if(scrollParent&&scrollParent.fastClickLastScrollTop !==scrollParent.scrollTop){return true}}if(!this.needsClick(targetElement)){event.preventDefault();this.sendClick(targetElement,event)}return false};FastClick.prototype.onTouchCancel=function(){'use strict';this.trackingClick=false;this.targetElement=null};FastClick.prototype.onMouse=function(event){'use strict';if(!this.targetElement){return true}if(event.forwardedTouchEvent){return true}if(!event.cancelable){return true}if(!this.needsClick(this.targetElement)||this.cancelNextClick){if(event.stopImmediatePropagation){event.stopImmediatePropagation()}else{event.propagationStopped=true}event.stopPropagation();event.preventDefault();return false}return true};FastClick.prototype.onClick=function(event){'use strict';var permitted;if(this.trackingClick){this.targetElement=null;this.trackingClick=false;return true}if(event.target.type==='submit'&&event.detail===0){return true}permitted=this.onMouse(event);if(!permitted){this.targetElement=null}return permitted};FastClick.prototype.destroy=function(){'use strict';var layer=this.layer;if(this.deviceIsAndroid){layer.removeEventListener('mouseover',this.onMouse,true);layer.removeEventListener('mousedown',this.onMouse,true);layer.removeEventListener('mouseup',this.onMouse,true)}layer.removeEventListener('click',this.onClick,true);layer.removeEventListener('touchstart',this.onTouchStart,false);layer.removeEventListener('touchmove',this.onTouchMove,false);layer.removeEventListener('touchend',this.onTouchEnd,false);layer.removeEventListener('touchcancel',this.onTouchCancel,false)};FastClick.notNeeded=function(layer){'use strict';var metaViewport;var chromeVersion;if(typeof window.ontouchstart==='undefined'){return true}chromeVersion=+(/Chrome\/([0-9]+)/.exec(navigator.userAgent)||[,0])[1];if(chromeVersion){if(FastClick.prototype.deviceIsAndroid){metaViewport=document.querySelector('meta[name=viewport]');if(metaViewport){if(metaViewport.content.indexOf('user-scalable=no')!==-1){return true}if(chromeVersion > 31&&window.innerWidth <=window.screen.width){return true}}}else{return true}}if(layer.style.msTouchAction==='none'){return true}return false};FastClick.attach=function(layer){'use strict';return new FastClick(layer)};if(typeof define !=='undefined'&&define.amd){define(function(){'use strict';return FastClick})}else if(typeof module !=='undefined'&&module.exports){module.exports=FastClick.attach;module.exports.FastClick=FastClick}else{window.FastClick=FastClick};window.addEventListener('load',function(){FastClick.attach(document.body)}, false);";
        }else {   
            $this->inlineCss.='.balc{overflow:hidden}';
            //if($this->urlRouter->module=='index' || $this->urlRouter->module=='detail')
            if($this->urlRouter->module!='myads' && $this->urlRouter->module!='post')    
            $this->set_ad(array('zone_0'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0-'.$this->urlRouter->cfg['server_id'])));
//            elseif ($this->urlRouter->module=='search')
//                $this->set_ad(array('zone_5'=>array('/1006833/pf-banner', 468, 60, 'div-gpt-ad-1380370257319-0'.$this->urlRouter->cfg['server_id'])));
            /*switch ($this->urlRouter->module){
                case 'search':
                    $this->set_require('css', array('gencs'));
                    break;
                case 'detail':
                    $this->set_require('css', array('gencd'));
                    break;
                case 'index':
                    $this->set_require('css', array('genc'));
                    break;
                default:
                    $this->set_require('css', array('gen'));
                    break;
            }*/
            $this->set_require('css', array('gen'));
            
//            if (isset ($this->user->params['visit']) && !in_array ($this->urlRouter->module,array('post','myads','account','page'))){
//                $this->cssImgsLoaded=true;
//                $this->set_require('css', array('imgs'));
//            }
        }
               
        if ($this->user->info['id'] && $this->urlRouter->module!='account' && $this->user->info['app-user']==0){
            if (!$this->user->info['email']) {
                if ((isset($this->user->info['options']['email']) && isset($this->user->info['options']['emailKey']) )) {
                    $this->setNotification(preg_replace('/{email}/', $this->user->info['options']['email'], $this->lang['validateEmail']));                    
                }else {
                    $this->setNotification($this->lang['requireEmail']);
                }
            }
        }
        if($this->urlRouter->userId && $this->urlRouter->userId == $this->user->info['id']  && $this->urlRouter->module!='detail'){
            $this->setNotification($this->lang['specialNB']);
        }else{
            if(isset($_GET['signin']) && $_GET['signin']=='error'){
                $this->setNotification($this->lang['failedLogin']);
            }elseif(isset($this->user->params['browser_alert']) && $this->user->params['browser_alert']){
                $this->setNotification(preg_replace('/{chrome_link}/', $this->user->params['browser_link'], $this->lang['browserUpdate']));
            }
        }

        if ($this->urlRouter->watchId || $this->urlRouter->userId || ($this->urlRouter->module!='index' && $this->urlRouter->module!='detail' && $this->urlRouter->module!='search')){
            if (isset($this->user->params['country']) && $this->user->params['country'])
                $this->urlRouter->countryId=$this->user->params['country'];
            if (isset($this->user->params['city']) && $this->user->params['city'])
                $this->urlRouter->cityId=$this->user->params['city'];
        }

        if ($this->urlRouter->module=='favorites'){
            $this->userFavorites=true;
            $this->lang['description']=$this->lang['home_description'];
            $this->urlRouter->module='search';
        }
        
        if ($this->urlRouter->module=='watchlist'){
            $this->urlRouter->module='search';
            $this->urlRouter->watchId=$this->pageUserId ? $this->pageUserId : -1;
        }

        if (!$this->isMobile) {
            $match=null;
            /*if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && preg_match('/(MSIE 6|MSIE 7|MSIE 8|MSIE 9)/', $_SERVER['HTTP_USER_AGENT'], $match)) {*/
            if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && preg_match('/(MSIE 6|MSIE 7)/', $_SERVER['HTTP_USER_AGENT'], $match)) {
                $version=((int)substr($match[0], -1));
                /*if ($version<8) {
                    $this->topMenuIE=true;
                    $this->urlRouter->cfg['enabled_post']=false;
                }*/
                if ($version<7) {
                    $this->urlRouter->cfg['enabled_disqus']=false;
                    //$this->png_fix=true;
                    //$this->deprecated=true;
                    //$this->set_require('css', array('ie6'));
                }
          /*      if ($version>6 && $version<8){
                    $this->set_require('css', array('ie7'));
                }
                if ($version<8 && $this->urlRouter->module!='detail') {
                    $this->urlRouter->cfg['enabled_sharing']=0;
                }*/
            }
        }
        

        if ($this->urlRouter->module=='detail') {
            $this->detailAd = $this->classifieds->getById($this->urlRouter->id);
            if (!empty ($this->detailAd)) {
                if (!empty($this->detailAd[Classifieds::ALT_CONTENT])) {
                    if ($this->urlRouter->siteLanguage=="en" && $this->detailAd[Classifieds::RTL]) {
                        $this->detailAd[Classifieds::TITLE] = $this->detailAd[Classifieds::ALT_TITLE];
                        $this->detailAd[Classifieds::CONTENT] = $this->detailAd[Classifieds::ALT_CONTENT];
                        $this->detailAd[Classifieds::RTL] = 0;
                        $this->appendLocation=false;
                    } elseif ($this->urlRouter->siteLanguage=="ar" && $this->detailAd[Classifieds::RTL]==0) {
                        $this->detailAd[Classifieds::TITLE] = $this->detailAd[Classifieds::ALT_TITLE];
                        $this->detailAd[Classifieds::CONTENT] = $this->detailAd[Classifieds::ALT_CONTENT];
                        $this->detailAd[Classifieds::RTL] = 1;          
                        $this->appendLocation=false;
                    }
                }
		  //$this->detailAd[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$this->detailAd[Classifieds::CONTENT]));

                //$this->detailAd=reset($this->detailAd);
                $this->urlRouter->cityId=$this->detailAd[Classifieds::CITY_ID];
                $this->urlRouter->countryId=$this->detailAd[Classifieds::COUNTRY_ID];
                $this->urlRouter->rootId=$this->detailAd[Classifieds::ROOT_ID];
                $this->urlRouter->sectionId=$this->detailAd[Classifieds::SECTION_ID];
                $this->urlRouter->purposeId=$this->detailAd[Classifieds::PURPOSE_ID];
                $this->lang['description']=preg_replace('/"/', '', $this->detailAd[Classifieds::CONTENT]);
                if ($this->detailAd[Classifieds::HELD]!=0) $this->detailAdExpired=true;
            }else {
                $this->detailAdExpired=true;
            }

            if (isset($this->user->params['search']['cn']) && $this->urlRouter->internal_referer && !isset($_GET['ref'])) {
                if(isset($this->user->params['search']['uId']))$this->urlRouter->userId=$this->user->params['search']['uId'];
                if(isset($this->user->params['search']['wId']))$this->urlRouter->watchId=$this->user->params['search']['wId'];
                $this->urlRouter->countryId=$this->user->params['search']['cn'];
                $this->urlRouter->cityId=$this->user->params['search']['c'];
                $this->urlRouter->rootId=$this->user->params['search']['ro'];
                $this->urlRouter->sectionId=$this->user->params['search']['se'];
                $this->urlRouter->purposeId=$this->user->params['search']['pu'];
                $this->urlRouter->params['q']=$this->user->params['search']['q'];
                if (array_key_exists('exId', $this->user->params['search']))
                    $this->urlRouter->params['tag_id']=$this->user->params['search']['exId'];
                if (array_key_exists('locId', $this->user->params['search']))
                $this->urlRouter->params['loc_id']=$this->user->params['search']['locId'];
                if (isset ($_GET['p'])){
                    $p=$_GET['p'];
                    if ($p=='prev'){
                        if ($this->user->params['search']['start']>1)
                            $this->user->params['search']['start']--;
                        else $this->user->params['search']['start']=0;
                    }
                    if ($p=='next'){
                        if ($this->user->params['search']['start']>1)
                            $this->user->params['search']['start']++;
                        else $this->user->params['search']['start']=2;
                    }
                }
                $this->urlRouter->params['start']=$this->user->params['search']['start'];

            }
            $this->urlRouter->cacheExtension();
        } elseif($this->urlRouter->module!='search') {
            $last_query_time=  isset($this->user->params['search']['time']) ? $this->user->params['search']['time'] : 0;
            unset ($this->user->params['search']);
            $this->user->params['search']=array('time'=>$last_query_time);
        }
        
        if ($this->urlRouter->module=='search') {
            if(isset($this->user->params['last_root']) && $this->user->params['last_root'] != $this->urlRouter->rootId){
                $this->user->params['list_publisher'] = 0;
                $this->publisherTypeSorting = 0;
            }
            $this->user->params['last_root'] = $this->urlRouter->rootId;
            $this->user->update();
            
            if($this->publisherTypeSorting){
                $this->forceNoIndex = 1;
            }
        }

        if ($this->urlRouter->countryId) {
            //if ($this->urlRouter->countryId==1) $this->urlRouter->cfg['slogan']=' <span>made in Lebanon</span>';
            $this->countryName=$this->urlRouter->countries[$this->urlRouter->countryId]['name']; //$this->fieldNameIndex];
            /*
            $this->urlRouter->countryCities=$this->urlRouter->db->queryCacheResultSimpleArray(
                "cities_{$this->urlRouter->countryId}",
                "select c.ID from city c
                where c.country_id={$this->urlRouter->countryId} and c.blocked=0",
                null, 0, $this->urlRouter->cfg['ttl_long']);
            */    
            if (count($this->urlRouter->countries[$this->urlRouter->countryId]['cities'])>0) {
                $this->hasCities=true;
                if ($this->urlRouter->cityId && isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])){
                    $this->cityName = $this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId]['name'];
                    //$this->cityName=$this->urlRouter->cities[$this->urlRouter->cityId][$this->fieldNameIndex];
                }else{
                    $this->urlRouter->cityId=0;
                }
            }else {
                $this->urlRouter->cityId=0;
            }
            //if ($this->urlRouter->module=='index') {
                if ($this->urlRouter->cityId) {
//                    $this->countryCounter=$this->urlRouter->siteLanguage=='ar'? number_format($this->urlRouter->cities[$this->urlRouter->cityId][4]) :$this->urlRouter->cities[$this->urlRouter->cityId][4];
                    $this->countryCounter=$this->urlRouter->siteLanguage=='ar'? number_format($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId]['counter']) :$this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId]['counter'];

                }else {
                    $this->countryCounter=$this->urlRouter->siteLanguage=='ar'? number_format($this->urlRouter->countries[$this->urlRouter->countryId]['counter']) :$this->urlRouter->countries[$this->urlRouter->countryId]['counter'];
                }
            //}
        }else {
            if (!$this->isMobile) {
                $this->countryName=$this->lang['opt_all_countries'];
                $counts=0;
                foreach ($this->urlRouter->countries as $country) {
                    $counts+=$country['counter'];
                }
                $this->countryCounter = $this->urlRouter->siteLanguage=='ar' ? number_format($counts) : $counts;
            }
        }
        $this->user->params['country']=$this->urlRouter->countryId;
        if ($this->hasCities) $this->user->params['city']=$this->urlRouter->cityId;
        else $this->user->params['city']=0;

        if ($this->countryCounter) {
            if ($this->urlRouter->siteLanguage=='ar') {
                $this->countryCounter.=' '.$this->lang['ads'];
            }else {
                $this->countryCounter=$this->formatPlural($this->countryCounter, 'ad');
            }
        }
        $lang='en';
        if ($router->siteTranslate) {
            if ($router->siteTranslate=='ar') $lang='ar';
        }else {
            $lang=$router->siteLanguage;
        }
        if(!$this->isMobile) {
            $cntLink='<b>'.($this->urlRouter->cityId ? $this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId]['name'] : ($this->urlRouter->countryId ? $this->urlRouter->countries[$this->urlRouter->countryId]['name']:'') ).'</b><span class="cf c'.($this->urlRouter->countryId).'"></span><b>'.$this->countryCounter.'</b>';
            //if($this->urlRouter->module!='index'){
            //    $cntLink='<a href="'.$this->urlRouter->getURL($this->urlRouter->countryId).'">'.$cntLink.'</a>';
            //}
            $this->countryCounter=$cntLink;
        }
        /*'<select id="country"><option>'.$this->lang['change_country'].'</option><option value="0">'.$this->lang['opt_all_countries'].'</option>';
                foreach ($this->urlRouter->countries as $country) {
                    if (isset($country[$this->fieldNameIndex]))
                        $this->countryCounter.='<option value="'.$country[3].'">'.$country[$this->fieldNameIndex].'</option>';
                } 
        $this->countryCounter.='</select>';        */
        
        $this->includeHash=($router->countryId?$router->countries[$router->countryId]['uri']:'zz').'-'.
                $lang.'-'.($router->countryId?$router->cityId:'0').'-';
        if($this->urlRouter->params['start'] && $this->urlRouter->params['start']>100){
            $this->urlRouter->params['start']=0;
            $this->forceNoIndex=true;
        }
        if ($this->urlRouter->params['q']){
            $this->urlRouter->params['q'] = preg_replace('/\//','',$this->urlRouter->params['q']);
            $this->urlRouter->params['q'] = trim(preg_replace('/\s+/',' ',$this->urlRouter->params['q']));
        }
        
        if (in_array($this->user->info['id'],array(1,2,2100,38813,44835,53456))) {
            $this->urlRouter->cfg['enabled_sharing']=false;
            $this->urlRouter->cfg['enabled_ads']=false;
        }
        if(!$this->isMobile){
            $this->urlRouter->cfg['enabled_sharing']=false;
        }
        /*if(!isset($this->user->params['visit'])){
            $this->urlRouter->cfg['enabled_sharing']=false;
        }*/
        
        
        $this->user->update();
        //var_dump(isset($this->user->params['visit']));
        //$this->urlRouter->cfg['enabled_sharing']=false;
        //$this->urlRouter->cfg['enabled_ads']=false;
    }
    /*
    if ($this->urlRouter->watchId) {
                echo "<li class='".($renderedFirst ? '':'f ')."on'><b><span class='eye on'></span>{$this->lang['myList']}</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li".($renderedFirst ? '':' class=\'f\'')."><a href='/watchlist/".$lang."'><span class='eye on'></span>{$this->lang['myList']}</a></li>";
                $renderedFirst=true;
            }
            
            if ($this->userFavorites) {
                echo "<li class='".($renderedFirst ? '':'f ')."on'><b><span class='fav on'></span>{$this->lang['myFavorites']} (<span id='uifc'>{$this->user->info['favCount']}</span>)</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li".($renderedFirst ? '':' class=\'f\'')."><a href='/favorites/".$lang."'><span class='fav on'></span>{$this->lang['myFavorites']} (<span id='uifc'>{$this->user->info['favCount']}</span>)</a></li>";
                $renderedFirst=true;
            }

            if ($this->urlRouter->module=='page'){
                echo "<li class='".($renderedFirst ? '':'f ')."on'><b><span class='sdi'></span>{$this->lang['myPage']}".( (!$this->user->info['email'] || !$this->user->info['name']) ? ' <span class="anb us"></span>':'' )."</b></li>";
            }else {
                echo "<li".($renderedFirst ? '':' class=\'f\'')."><a href='/page/".$lang."'><span class='sdi'></span>{$this->lang['myPage']}</a></li>";
                $renderedFirst=true;
            }

            if ($this->urlRouter->module=='account'){
                echo "<li class='".($renderedFirst ? '':'f ')."on'><b><span class='usr'></span>{$this->lang['myAccount']}".( (!$this->user->info['email'] || !$this->user->info['name']) ? ' <span class="anb us"></span>':'' )."</b></li>";
            }else {
                echo "<li".($renderedFirst ? '':' class=\'f\'')."><a href='/account/".$lang."'><span class='usr'></span>{$this->lang['myAccount']}</a></li>";
                $renderedFirst=true;
            }
            
            if ($this->urlRouter->cfg['enabled_post']) {
            $renderedFirst=true;
            echo "<li class='".($renderedFirst ? '':'f ')."sub'><b><span class='adi'></span>{$this->lang['myAds']}</b></li>";
                $sub='';
                if (isset ($_GET['sub']) && $_GET['sub']) $sub=$_GET['sub'];
                echo '<li class="cty"><ul>';
                echo '<li><a href="/post/'.$lang.'">'.$this->lang['create_ad'].'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='') echo '<li><b class="hdrAct">'.$this->lang['ads_active'].($this->user->info['active_ads']? ' ('.$this->user->info['active_ads'].')':'').'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'" class="hdrAct">'.$this->lang['ads_active'].($this->user->info['active_ads']? ' ('.$this->user->info['active_ads'].')':'').'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='pending') echo '<li><b class="hdrPen">'.$this->lang['ads_pending'].($this->user->info['pending_ads']? ' ('.$this->user->info['pending_ads'].')':'').'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=pending" class="hdrPen">'.$this->lang['ads_pending'].($this->user->info['pending_ads']? ' ('.$this->user->info['pending_ads'].')':'').'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='drafts') echo '<li><b class="hdrDr">'.$this->lang['ads_drafts'].($this->user->info['draft_ads']? ' ('.$this->user->info['draft_ads'].')':'').'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=drafts" class="hdrDr">'.$this->lang['ads_drafts'].($this->user->info['draft_ads']? ' ('.$this->user->info['draft_ads'].')':'').'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='archive') echo '<li><b class="hdrArc">'.$this->lang['ads_archive'].($this->user->info['archive_ads']? ' ('.$this->user->info['archive_ads'].')':'').'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=archive" class="hdrArc">'.$this->lang['ads_archive'].($this->user->info['archive_ads']? ' ('.$this->user->info['archive_ads'].')':'').'</a></li>';
                echo '</ul></li>';
            }*/
    
    function renderBalanceBar(){
        
        /*<script type="text/javascript" src="https://seal.thawte.com/getthawteseal?host_name=www.mourjan.com&amp;size=S&amp;lang=en"></script> */
        if($this->user->info['id']){
        echo '<div id="balance" class="balc"><div id="balanceCounter"></div><a class="buL" href="/gold/'.($this->urlRouter->siteLanguage=='ar' ? '':$this->urlRouter->siteLanguage.'/').'#how-to"><span class="mc24"></span>'.$this->lang['buy_gold_bt'].'</a><a class="buL" href="/gold/'.($this->urlRouter->siteLanguage=='ar' ? '':$this->urlRouter->siteLanguage.'/').'"><span class="rj add"></span>'.$this->lang['get_gold'].'</a></div>';
        $this->globalScript.="var showBalance=1;";
        }
    }
    
    function renderLoginBox(){
        $lang='';
        if($this->urlRouter->siteLanguage=='en')$lang='en/';
        if($this->user->info['id']){  
            ?><div class='lgb <?= $this->urlRouter->module != 'premium' ? $this->urlRouter->module : '' ?>'><?php
                ?><ul class="hoz"><?php
                    ?><li><a href="?logout=<?= $this->user->info['provider'] ?>"><span class="j out"></span><?= $this->lang['signout'] ?></a></li><?php
                    if ($this->urlRouter->module=='home') {
                        ?><li class="on"><span class="j home"></span></li><?php
                    }else{
                        ?><li><a href="/home/<?= $lang ?>"><span class="j home"></span><?= $this->lang['myPanel'] ?></a></li><?php
                    }
                    if ($this->userFavorites) {
                        ?><li class="on"><span class="j fva"></span></li><?php
                    }else{
                        ?><li><a href="/favorites/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>"><span class="j fva"></span><?= $this->lang['myFavorites'] ?></a></li><?php
                    }
                    if ($this->urlRouter->watchId) {
                        ?><li class="on"><span class="j eye"></span></li><?php
                    }else{
                        ?><li><a href="/watchlist/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>"><span class="j eye"></span><?= $this->lang['myList'] ?></a></li><?php
                    }
                    if ($this->urlRouter->module=='myads') {
                        ?><li class="on"><span class="j ads"></span></li><?php
                    }else{
                        ?><li><a href="/myads/<?= $lang ?>"><span class="j ads"></span><?= $this->lang['myAds'] ?></a></li><?php
                    }
                    if ($this->urlRouter->module=='post') {
                        ?><li class="on"><span class="j pub"></span></li><?php
                    }else{
                        ?><li><a href="/post/<?= $lang ?>"><span class="j pub"></span><?= $this->lang['button_ad_post_m'] ?></a></li><?php
                    }
                ?></ul><?php
            ?></div><?php
        }elseif( (!$this->userFavorites && !$this->urlRouter->watchId && !in_array ($this->urlRouter->module,array('myads','account','post','profile','signin','password'))) 
                || ($this->pageUserId)){
            ?><div class='lgb <?= $this->urlRouter->module ?>'><?php
                ?><a class='h' href="/signin/<?= $lang ?>" onclick="tglLG(this)"><span class='i sn'></span><?= $this->lang['signin'] ?></a><?php
                /* ?><div class='h' onclick="tglLG(this)"><span class='i sn'></span><?= $this->lang['signin'] ?></div><?php
                    ?><ul class="drp"><?php
                    ?><li><p class="ctr"><b><?= $this->lang['signin_m'] ?></b></p></li><?php
                    ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
                    ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
                    ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
                    ?><li><a class="bt go" href="?provider=google">Google</a></li><?php
                    ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
                    ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
                    ?><li><p class="nb"><?= $this->lang['disclaimer'] ?></p></li><?php
                ?></ul><?php */
            ?></div><?php
        }
    }

    function checkBlockedAccount($level=0){
        if ($this->user->info['id']){
            if((!$level || ($level && $level==5)) && $this->user->info['level']==5) {
                $this->user->redirectTo('/blocked/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
            }elseif((!$level || ($level && $level==6)) && $this->user->info['level']==6) {
                $this->user->redirectTo('/suspended/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
            }
        }
    }
    
    function checkSuspendedAccount(){
        if ($this->user->info['id'] && isset($this->user->info['options']['suspend']) && $this->user->info['options']['suspend']>time()){            
            $this->user->redirectTo('/held/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }
    }

    function checkUserData(){
        if ($this->user->info['id']) {
            $this->user->loadFavorites();
        }
    }
/*
    function renderLoginPage(){
        ?><div class="lgb"><ul class="drp"><?php
                    ?><li><p class="ctr"><b><?= $this->lang['signin_m'] ?></b></p></li><?php
                    ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
                    ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
                    ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
                    ?><li><a class="bt go" href="?provider=google">Google</a></li><?php
                    ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
                    ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
                    ?><li><p class="nb"><?= $this->lang['disclaimer'] ?></p></li><?php
        ?></ul></div><?php
        ?><p class="ph phb"><b><?= $this->lang['loginTo'].$this->title ?></b></p><?php
        ?><p class="htn"><?= $this->lang['hint_login'] ?></p><?php 
        $this->requireLogin=true;
    } */

    function renderLoginPage(){
        $lang='';
        
        $keepme_in = (isset($this->user->params['keepme_in']) && $this->user->params['keepme_in']==0) ? 0: 1;
        
        if ($this->urlRouter->siteLanguage != 'ar') $lang = $this->urlRouter->siteLanguage.'/';
        if(isset($this->user->pending['login_attempt'])){
            if($this->urlRouter->siteLanguage=='ar'){
                ?><style type="text/css">.lgs .br{margin-top:20px}</style><?php
            }else{
                ?><style type="text/css">.lgs .br{margin-top:32px}</style><?php
            }
        }
        /* ?><div class="lgb"><ul class="drp"><?php
                    ?><li><p class="ctr"><b><?= $this->lang['signin_m'] ?></b></p></li><?php
                    ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
                    ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
                    ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
                    ?><li><a class="bt go" href="?provider=google">Google</a></li><?php
                    ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
                    ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
                    ?><li><p class="nb"><?= $this->lang['disclaimer'] ?></p></li><?php
        ?></ul></div><?php */
        if(!$this->isMobile){
            //if(!$this->urlRouter->cfg['site_production']){
                include_once $this->urlRouter->cfg['dir']. '/core/lib/phpqrcode.php';
                $qrfile = dirname( $this->urlRouter->cfg['dir'] ) . "/tmp/qr/".  session_id() . ".png";
                QRcode::png("mourjan:login:".  session_id() . str_pad($this->urlRouter->cfg['server_id'],4,'0', STR_PAD_LEFT) . str_pad(time(),16,'0', STR_PAD_LEFT), $qrfile, QR_ECLEVEL_L, 5 );

                $sh = '0';
                if (isset($_COOKIE['mourjan_user'])) {            
                    $cook = json_decode($_COOKIE['mourjan_user']);            
                    //error_log(var_export($_COOKIE['mourjan_user'], TRUE)."\n\n".$savePath);

                    if (is_object($cook) && isset($cook->mu)) {
                        $sh='1';
                    }
                }
            
                $redis = new Redis();
                $redis->connect($this->urlRouter->cfg['rs-host'], $this->urlRouter->cfg['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
                $redis->setOption(Redis::OPT_PREFIX, 'SESS:');
                $redis->select(1);
                $redis->setex(session_id(), 300, $this->urlRouter->cfg['server_id'].':'.$sh);
                $redis->close();
            //}
                
            //if($this->urlRouter->module=='signin'){
                //if(!$this->urlRouter->cfg['site_production']){
                $data = file_get_contents($qrfile);
                $type = pathinfo($qrfile, PATHINFO_EXTENSION);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                //echo "<div class='list htn'><img width='185' height='185' src='{$this->urlRouter->cfg['host']}/qr/".session_id().".png' />";
                echo "<div class='list htn'><img width='185' height='185' src='{$base64}' />";
                    echo '<span class="bt scan"><span class="apple"></span><span class="apple up"></span> '.$this->lang['hint_login_signin'].' <span class="apple up"></span><span class="apple"></span></span>';                    
                /*    
                }else{
                    echo "<div class='list htn'>";
                }*/
                ?><a href="/signup/<?= $lang ?>" class="bt"><?= $this->lang['create_account'] ?></a></div><?php
            /*}else{
                ?><div class='list htn'><?= $this->lang['hint_login'] ?><a href="/signup/<?= $lang ?>" class="bt"><?= $this->lang['create_account'] ?></a></div><?php
            }*/
            ?><div class="lgs"><?php 
        }
            ?><form method="post" action="/a/<?= $lang ?>" onsubmit="lgi(this);return false;"><?php 
                if(!$this->isMobile){
                    ?><ul class="dpr"><?php
                        ?><li class="h"><?= $this->lang['signin_mourjan'] ?></li><?php
                        ?><li class="lbl"><?= $this->lang['email'] ?></li><?php
                        ?><li class="fld"><input name="u" onfocus="cle(this)" class="en" type="email" /></li><?php
                        ?><li class="lbl"><?= $this->lang['password'] ?></li><?php
                        ?><li class="fld"><input name="p" onfocus="cle(this)" type="password" /></li><?php
                        ?><li class="lbl"><input name="o" type="checkbox" <?= $keepme_in ? 'checked="checked"':'' ?> /> <?= $this->lang['keepme_in'] ?></li><?php
                        ?><li class="cap"><div class="g-recaptcha" data-sitekey="<?= $this->urlRouter->cfg['recap-key'] ?>"></div></li><?php
                        if(isset($this->user->pending['login_attempt'])) {
                            ?><li class="nl"><span><span class="fail"></span><?= $this->lang['login_error'] ?></span></li><?php                    
                        }elseif(isset($this->user->pending['login_attempt_captcha'])) {
                            ?><li class="nl"><span><span class="fail"></span><?= $this->lang['login_error_captcha'] ?></span></li><?php                    
                        }
                        $uri = $this->urlRouter->uri;
                        if(preg_match('/signin/',$this->urlRouter->uri)){
                            $uri = '/home/'.$lang;
                        }
                        ?><li class="ctr"><input name='r' type="hidden" value="<?= $uri ?>" /><input type="submit" class="bt" value="<?= $this->lang['signin'] ?>" /></li><?php
                        ?><li class="<?= $this->urlRouter->siteLanguage ?> nobr"><a class="lnk" href="/password/<?= $lang ?>"><?= $this->lang['forgot_pass'] ?></a></li><?php
                        ?><li class="<?= $this->urlRouter->siteLanguage ?> nobr"><a class="lnk" href="/signup/<?= $lang ?>"><?= $this->lang['create_account'] ?></a></li><?php
                        
                    ?></ul><?php
                }else{
                    ?><ul class="ls po"><?php
                        ?><li class="h"><b><?= $this->lang['email'] ?></b></li><?php
                        ?><li><div class="ipt"><input name="u" onfocus="cle(this)" class="en" type="email" /></div></li><?php
                        ?><li class="h"><b><?= $this->lang['password'] ?></b></li><?php
                        ?><li><div class="ipt"><input name="p" onfocus="cle(this)" type="password" /></div></li><?php
                        ?><li onclick="skO(this)" class="ckn button<?= $keepme_in ? ' on':'' ?>"><input name="o" type="hidden" value="<?= $keepme_in ?>" /><b class="ah"><?= $this->lang['keepme_in'] ?><span class="cbx"></span></b></li><?php  
                        ?><li class="recap"><div class="g-recaptcha" data-sitekey="<?= $this->urlRouter->cfg['recap-key'] ?>"></div></li><?php
                        if(isset($this->user->pending['login_attempt'])) {
                            ?><li class="nl"><b><span class="fail"></span><?= $this->lang['login_error'] ?></b></li><?php                    
                        }elseif(isset($this->user->pending['login_attempt_captcha'])) {
                            ?><li class="nl"><span><span class="fail"></span><?= $this->lang['login_error_captcha'] ?></span></li><?php                    
                        }
                        ?><li><b class="ah ctr act"><input name='r' type="hidden" value="<?= $this->urlRouter->uri ?>" /><input type="submit" class="bt" value="<?= $this->lang['signin'] ?>" /></b></li><?php
                        ?><li class="<?= $this->urlRouter->siteLanguage ?> br"><a class="lnk" href="/password/<?= $lang ?>"><?= $this->lang['forgot_pass'] ?></a></li><?php
                        ?><li class="<?= $this->urlRouter->siteLanguage ?> br"><a href="/signup/<?= $lang ?>" class="lnk"><?= $this->lang['create_account'] ?></a></li><?php
                    ?></ul><?php
                }
             ?></form><?php 
            if(!$this->isMobile){
                ?><ul class="drp"><?php
                    ?><li class="h"><?= $this->lang['signin_m'] ?></li><?php
                    ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
                    ?><li><a class="bt go" href="?provider=google">Google</a></li><?php
                    ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
                    ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
                    ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
                    ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
                ?></ul><?php
                ?></div><?php
                ?><div class="sha shau sh <?= $this->urlRouter->siteLanguage ?> rc w"><div class="fr"><label><?= $this->lang['NB'] ?></label><?php 
                    ?><ul><?php 
                        ?><li><?= $this->lang['disclaimer'] ?></li><?php
                        ?><li><?= $this->lang['disclaimer_social'] ?></li><?php
                    ?></ul><?php
                ?></div></div><?php
                
                
            }else{
                ?><div class="str <?= $this->urlRouter->siteLanguage ?>"><br /><label><?= $this->lang['NB'] ?></label><?php 
                    ?><ul><?php 
                        ?><li><?= $this->lang['disclaimer'] ?></li><?php
                        ?><li><?= $this->lang['disclaimer_social'] ?></li><?php
                    ?></ul><?php
                ?></div><?php
            }
            
            
        $this->globalScript.='var lgi,uin,pin,cle;';
        $this->inlineScript.='lgi=function(e){
                if(!uin){
                    uin=$("input[name=u]",e);
                    pin=$("input[name=p]",e);
                }
                var f=1;
                if(!isEmail(uin.val())){
                    f=0;
                    uin.addClass("err");
                }
                if(pin.val().length<6){
                    f=0;
                    pin.addClass("err");
                }
                if(f){
                    e.submit();
                }
            };
            cle=function(e){$(e).removeClass("err")};';
        /* ?><div class="sum rc"><div class="brd"><h1><?= $this->lang['loginTo'].$this->title ?></h1></div><p><?= $this->lang['hint_login']; ?></p></div><br /><br /><div class="fake"></div><?php */
           $this->requireLogin=true;
        if(isset($this->user->pending['login_attempt'])){
            unset($this->user->pending['login_attempt']);
            $this->user->update();
        }
        if(isset($this->user->pending['login_attempt_captcha'])){
            unset($this->user->pending['login_attempt_captcha']);
            $this->user->update();
        }
    }

    function renderDisabledPage(){
        ?><div class="sum rc"><div class="brd"><h1><?= $this->lang['title_not_supported'] ?></h1></div><p><?= $this->lang['hint_not_supported']; ?></p></div><div class="fake"></div><?php
    }

    function setNotification($note, $type=''){
        $this->notifications[]=array(
            'msg'=>$note,
            'type'=>$type
        );
    }

    
    function renderNotifications(){
        $open=false;
        $lastType='';
        $res='';
        foreach ($this->notifications as $note){
            if ($open && $lastType!=$note['type']){
                $res .= '</div>';
                $open=false;
            }
            $type = $note['type']?' '.$note['type']:'';
            $lastType=$note['type'];
            if (!$open) {
                $res .= "<div class='mnb w rc{$type}'>";
                $open=true;
            }
            $res .= "<p>{$note['msg']}</p>";
        }
        if ($open) {
            $res .= '</div>';
        }
        if ($res) $res ='<!--googleoff: snippet-->'.$res.'<!--googleon: snippet-->';
        echo $res;
    }

    function renderNotificationsMobile($containerTag='p'){
        $open=false;
        $lastType='';
        $res='';
        foreach ($this->notifications as $note){
            if ($open && $lastType!=$note['type']){
                $res .= '</div>';
                $open=false;
            }
            $type = $note['type']?' '.$note['type']:'';
            $lastType=$note['type'];
            if (!$open) {
                $res .= "<div class='nfr{$type}'>";
                $open=true;
            }
            if ($containerTag)
                $res .= "<{$containerTag}>{$note['msg']}</{$containerTag}>";
            else
                $res .= $note['msg'];
        }
        if ($open) {
            $res .= '</div>';
        }
        if ($res) $res ='<!--googleoff: snippet-->'.$res.'<!--googleon: snippet-->';
        echo $res;
    }

    function set_require($type, $str){
        if (is_array($str)) {
            $this->requires[$type]=array_merge($this->requires[$type], $str);
        }else $this->requires[$type][]=$str;
    }

    protected function load_css(){
        $addOn='';
        $mobileDir='';
        $source=$this->urlRouter->cfg['url_css'];
        $sourceFile = '/home/www/css/5.4.2';
        $sourceFile = $this->urlRouter->cfg['dir_css'].substr($source,strlen($this->urlRouter->cfg['url_resources']));
        if ($this->isMobile) {
            $addOn.='_m';
            $source=$this->urlRouter->cfg['url_css_mobile'];
            $sourceFile = '/home/www/css/5.2.8g';
            $sourceFile = $this->urlRouter->cfg['dir_css'].substr($source,strlen($this->urlRouter->cfg['url_resources']));
            $this->requires['css'][]='mms';
        }else{
            $this->requires['css'][]='imgs';
        }
        if ($this->urlRouter->siteTranslate) {
            if ($this->urlRouter->siteTranslate=='ar') $addOn.='_ar';
        }elseif ($this->urlRouter->siteLanguage=='ar') $addOn.='_ar'; 
        $fAddon=$addOn;
        $csFile = '';
        $toRequire = [];
        foreach ($this->requires['css'] as $css) {
            if ($css=='ie6' || $css=='ie7' || $css=='imgs' || $css=='mms' || $css == 'home' || $css == 'select2') $addOn='';
            else $addOn=$fAddon;
            //if($css == 'main' && $this->isMobile){
            //if (!isset($this->user->params['visit']) || $this->user->params['visit']<2) {
            //if (0 && $this->isMobile) {
            if (strpos($source,'dv.mourjan.com')===false) {
                $toRequire[]='/'.$css.$addOn.'.css';
                $csFile .= preg_replace('/url\((?:\.\/|)i/', 'url('.$source.'/i', file_get_contents($sourceFile. '/'.$css.$addOn. '.css'));
            }else{
                echo '<link rel=\'stylesheet\' type=\'text/css\' href=\'', $source, '/',$css,$addOn, '.css'.'\' />';
            }
        }
        if($csFile){
            $this->requires['css'] = $toRequire;
        }else{
            unset ($this->requires['css']);
        }
        if ($this->inlineCss){
            $this->inlineCss= preg_replace('/\n/','',$this->inlineCss);
            $this->inlineCss= preg_replace('/\s+/',' ',$this->inlineCss);
        }else{
            $this->inlineCss='';
        }
        if($csFile || $this->inlineCss){            
            echo '<style type="text/css">',$csFile, $this->inlineCss, '</style>';
        }
        /*if (isset($this->user->params['visit']) && $this->user->params['visit']>= 2) {
            if($this->isMobile){
                echo '<link rel=\'stylesheet\' type=\'text/css\' href=\'', $source, '/mms.css\' />';
            }else{
                echo '<link rel=\'stylesheet\' type=\'text/css\' href=\'', $source, '/imgs.css\' />';
            }
        }*/
        if(!$this->isMobile){
            if ($this->urlRouter->siteLanguage=='ar'){
                ?><!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie7_ar.css"><![endif]--><?php
                ?><!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie8_ar.css"><![endif]--><?php
                ?><!--[if IE 9]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie9_ar.css"><![endif]--><?php
            }else{
                ?><!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie7.css"><![endif]--><?php
                ?><!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie8.css"><![endif]--><?php
                ?><!--[if IE 9]><link rel="stylesheet" type="text/css" href="<?= $this->urlRouter->cfg['url_css'] ?>/ie9.css"><![endif]--><?php
            }
        }
    }
    
    function renderMobileLike(){
        ?><div class="fb-like-box fbb" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-show-faces="true" data-stream="false" data-border-color="#E7E9F0" data-header="true"></div><?php 
    }
    
    function renderUserAlert(){
        ?><div class="fb-like-box fbb" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-show-faces="true" data-stream="false" data-border-color="#E7E9F0" data-header="true"></div><?php 
    }

    function renderSideLike(){
        
        ?><div class="fb-like-box fb-like-side" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="200" data-show-faces="true" data-stream="false" data-show-border="false" data-header="false"></div><?php 
        /*if (false && $this->urlRouter->cfg['enabled_sharing']) {
        ?><h4><?= $this->lang['askUserLike'] ?></h4><?php
        ?><ul class='list ssh'>
            <li class="fbl"><span class='st_fblike_hcount'></span></li>
            <li><span class='st_plusone_hcount'></span></li>
            <li><span class='st_google_translate_large'></span></li>
        </ul><?php 
        }*/
    }


    function renderSideSite(){
        $countryId=0;
        $cityId=0;
        if ($this->user->params['country']) {
            $countryId=$this->user->params['country'];
        }
        if ($this->user->params['city']) {
            $cityId=$this->user->params['city'];
        }
        $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
        ?><h4><?= $this->lang['mourjan'] ?></h4><?php
        echo '<ul class=\'sm\'>';
        echo '<li><a href=\'', $this->urlRouter->getURL($countryId,$cityId), '\'>', $this->lang['homepage'], '</a></li>';

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
        
        $this->menu_app_banner();
    }


    function renderSideUserPanel(){
        if ($this->user->info['id'] && $this->user->info['level']!=5) {
            $countryId=0;
            $cityId=0;
            if ($this->user->params['country']) {
                $countryId=$this->user->params['country'];
            }
            if ($this->user->params['city']) {
                $cityId=$this->user->params['city'];
            }
            $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
            ?><h4><?= $this->user->info['name'] ?></h4><?php
            echo "<ul class='sm'>";
            $renderedFirst=false;
            /*if(in_array($this->user->info['level'],array(1,2,3,9))){
                echo '<li><a class="imp" href=\'/',( isset($this->user->info['options']['page']['uri']) && $this->user->info['options']['page']['uri'] ? $this->user->info['options']['page']['uri'] : $this->urlRouter->basePartnerId+$this->user->info['id']), '/'.$lang.'\'><span class="im"></span>', $this->lang['myPageLink'], '</a></li>';
                $renderedFirst=true;
            }
            if ($this->urlRouter->module!="index") {
                echo '<li><a href=\'', $this->urlRouter->getURL($countryId,$cityId), '\'><span class="hom"></span>', $this->lang['homepage'], '</a></li>';
                $renderedFirst=true;
            }*/

            if ($this->urlRouter->watchId) {
                echo "<li class='on'><b><span class='eye on'></span>{$this->lang['myList']}</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li><a href='/watchlist/".$lang."'><span class='eye on'></span>{$this->lang['myList']}</a></li>";
                $renderedFirst=true;
            }
            
            /*if ($this->userFavorites) {
                echo "<li class='on'><b><span class='fav on'></span>{$this->lang['myFavorites']} (<span id='uifc'>{$this->user->info['favCount']}</span>)</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li><a href='/favorites/".$lang."'><span class='fav on'></span>{$this->lang['myFavorites']} (<span id='uifc'>{$this->user->info['favCount']}</span>)</a></li>";
                $renderedFirst=true;
            }*/
            if ($this->userFavorites) {
                echo "<li class='on'><b><span class='fav on'></span>{$this->lang['myFavorites']}</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li><a href='/favorites/".$lang."'><span class='fav on'></span>{$this->lang['myFavorites']}</a></li>";
                $renderedFirst=true;
            }
/*
            if ($this->urlRouter->module=='page'){
                echo "<li class='on'><b><span class='sdi'></span>{$this->lang['myPage']}".( (!$this->user->info['email'] || !$this->user->info['name']) ? ' <span class="anb us"></span>':'' )."</b></li>";
            }else {
                echo "<li><a href='/page/".$lang."'><span class='sdi'></span>{$this->lang['myPage']}</a></li>";
                $renderedFirst=true;
            }
*/
            if ($this->urlRouter->module=='account'){
                echo "<li class='on'><b><span class='usr'></span>{$this->lang['myAccount']}".( (!$this->user->info['email'] || !$this->user->info['name']) ? ' <span class="anb us"></span>':'' )."</b></li>";
            }else {
                echo "<li><a href='/account/".$lang."'><span class='usr'></span>{$this->lang['myAccount']}</a></li>";
                $renderedFirst=true;
            }
            echo '</ul><h4>'.$this->lang['myAds'].'</h4><ul class="sm">';
            if ($this->urlRouter->cfg['enabled_post']) {
            $renderedFirst=true;
            //echo "<li class='sub'><b><span class='adi'></span>{$this->lang['myAds']}</b></li>";
                $sub='';
                if (isset ($_GET['sub']) && $_GET['sub']) $sub=$_GET['sub'];
                //echo '<li class="cty"><ul>';
                echo '<li><a href="/post/'.$lang.'?clear=true">'.$this->lang['create_ad'].'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='') echo '<li><b>'.$this->lang['ads_active'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'">'.$this->lang['ads_active'].'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='pending') echo '<li><b>'.$this->lang['ads_pending'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=pending">'.$this->lang['ads_pending'].'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='drafts') echo '<li><b>'.$this->lang['ads_drafts'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=drafts">'.$this->lang['ads_drafts'].'</a></li>';
                if ($this->urlRouter->module=="myads" && $sub=='archive') echo '<li><b>'.$this->lang['ads_archive'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=archive">'.$this->lang['ads_archive'].'</a></li>';
//                if ($this->urlRouter->module=="myads" && $sub=='') echo '<li><b>'.$this->lang['ads_active'].($this->user->info['active_ads']? ' ('.$this->user->info['active_ads'].')':'').'</b></li>';
//                else echo '<li><a href="/myads/'.$lang.'">'.$this->lang['ads_active'].($this->user->info['active_ads']? ' ('.$this->user->info['active_ads'].')':'').'</a></li>';
//                if ($this->urlRouter->module=="myads" && $sub=='pending') echo '<li><b>'.$this->lang['ads_pending'].($this->user->info['pending_ads']? ' ('.$this->user->info['pending_ads'].')':'').'</b></li>';
//                else echo '<li><a href="/myads/'.$lang.'?sub=pending" class="hdrPen">'.$this->lang['ads_pending'].($this->user->info['pending_ads']? ' ('.$this->user->info['pending_ads'].')':'').'</a></li>';
//                if ($this->urlRouter->module=="myads" && $sub=='drafts') echo '<li><b class="hdrDr">'.$this->lang['ads_drafts'].($this->user->info['draft_ads']? ' ('.$this->user->info['draft_ads'].')':'').'</b></li>';
//                else echo '<li><a href="/myads/'.$lang.'?sub=drafts" class="hdrDr">'.$this->lang['ads_drafts'].($this->user->info['draft_ads']? ' ('.$this->user->info['draft_ads'].')':'').'</a></li>';
//                if ($this->urlRouter->module=="myads" && $sub=='archive') echo '<li><b class="hdrArc">'.$this->lang['ads_archive'].($this->user->info['archive_ads']? ' ('.$this->user->info['archive_ads'].')':'').'</b></li>';
//                else echo '<li><a href="/myads/'.$lang.'?sub=archive" class="hdrArc">'.$this->lang['ads_archive'].($this->user->info['archive_ads']? ' ('.$this->user->info['archive_ads'].')':'').'</a></li>';
                //echo '</ul></li>';
            }
            echo '</ul>';
        }
    }

    function renderSideRoots(){
        if(!$this->userFavorites) {
        $cityId=$this->urlRouter->cityId;
        $countryId=$this->urlRouter->countryId;
        switch($this->urlRouter->module){
            case 'detail':
            case 'search':
                break;
            default:
                $cityId=$this->user->params['city'];
                $countryId=$this->user->params['country'];
                break;
        }
        $hasQuery=false;
        $q='';
        if ($this->urlRouter->params['q']) {
            $hasQuery=true;
            $q='?q='.urlencode($this->urlRouter->params['q']);
        }
        ?><h4><?= $this->lang['specify_category'] ?></h4><?php
            echo "<ul class='sm'>";
            //echo "<li class='f'>".$this->renderListLink($this->lang['opt_all_categories'], $this->urlRouter->getURL($countryId,$cityId).$q, $this->urlRouter->rootId==0)."</li>";
            $i=0;
            foreach ($this->urlRouter->pageRoots as $rid=>$root) {
                $selected = ($this->urlRouter->rootId == $rid ? true : false);
                $purposeId = 0;
                if ($rid==3) {
                    $purposeId=3;
                } else {
                    if (count($root['purposes'])==1) {
                        $purposeId = array_keys($root['purposes'])[0];
                    }
                }
                //$purposeId=is_numeric($root[3]) ? (int) $root[3]: ($root[0]==3 ? 3: 0);
                echo '<li'.($selected ? ' class="on big"':' class="big"').'>', $this->renderListLink('<span class="i i'.$rid.'"></span>'.$root['name'], $this->urlRouter->getURL($countryId, $cityId, $rid, 0, $purposeId).$q, $selected),'</li>';
                if($this->urlRouter->rootId == $rid){
                    echo '<li class="sub">';
                    $this->renderSubSections();
                    echo '</li>';
                }
                $i++;
            }
            echo '</ul>';
        }
    }
    
    function renderSideCountries(){
        if($this->userFavorites || ($this->urlRouter->module!='index' && $this->urlRouter->countryId && !$this->hasCities)) return;
        
        $hasQuery=false;
        $q='';
        if ($this->urlRouter->params['q']) {
            $hasQuery=true;
            $q='?q='.urlencode($this->urlRouter->params['q']);
        }
        if($this->urlRouter->countryId && $this->hasCities){
            ?><h4><?= $this->lang['specify_city'] ?></h4><?php
        }else {
            ?><h4><?= $this->lang['specify_location'] ?></h4><?php
        }
        echo '<ul class=\'list\'>';
        if ($this->urlRouter->module=='index' || !$this->urlRouter->countryId){
        echo '<li class=\'f\'>', 
            $this->renderListLink(
                '<span class=\'cn c0\'></span>'.$this->lang['opt_all_countries'], 
                $this->urlRouter->getURL(0, 0, 
                                $this->urlRouter->rootId, 
                                $this->urlRouter->sectionId,
                                $this->urlRouter->purposeId, true, true).$q, 
                $this->urlRouter->countryId==0),
            '</li>';
        }
        foreach ($this->urlRouter->countries as $country) {
            if (isset($country[0])) {
            if ($this->urlRouter->module=='index' || !$this->urlRouter->countryId || $this->urlRouter->countryId == $country[0]){
            $selected = ($this->urlRouter->countryId == $country[0] && !($this->hasCities && $this->urlRouter->cityId)) ? true : false;
            echo '<li>',
                $this->renderListLink(
                    "<span class='cn c{$country[0]}'></span>".$country[$this->fieldNameIndex], 
                    $this->urlRouter->getURL($country[0], 0, 
                            $this->urlRouter->rootId,
                            $this->urlRouter->sectionId,
                            $this->urlRouter->purposeId, true, true).$q, 
                    $selected), 
                '</li>';
            }
            if ($this->urlRouter->countryId == $country[0] && $this->hasCities) {
                echo "<li class='cty'><ul>";
                foreach ($this->urlRouter->countryCities as $id=>$value) {
                    if (array_key_exists($id, $this->urlRouter->cities) && ((int)$this->urlRouter->cities[$id][4])>0) {
                        $selected = $this->urlRouter->cityId == $this->urlRouter->cities[$id][0] ? true : false;
                        echo '<li',($selected?' class=\'on\'>':'>'),
                            $this->renderListLink(
                                $this->urlRouter->cities[$id][$this->fieldNameIndex], 
                                $this->urlRouter->getURL($country[0], $this->urlRouter->cities[$id][0], 
                                        $this->urlRouter->rootId,
                                        $this->urlRouter->sectionId,
                                        $this->urlRouter->purposeId, true, true).$q, 
                                $selected), 
                            '</li>';
                    }
                }
                echo '</ul></li>';
            }
            }
        }
        
        echo '</ul>'; 
        
        //if ($this->urlRouter->countryId==1) {
            //echo "<a href='http://www.new961.com/book.aspx' target=_blank><span class='a961'></span></a>";
        //}
    }

    
    function renderListLink($label, $link, $selected=false, $className=''){
        $class='link'.($className?' '.$className:'');
        $result='';
        if ($selected) {
            if ($this->isMobile) $result = $label;
            else $result = '<b>'.$label.'</b>';
        }
        else $result = "<a href='{$link}'>{$label}</a>";
        return $result;
    }
    
    function renderSubListLink($label, $link, $selected=false, $className=''){
        $result='';
        if ($selected) {
            if($className)$className=' '.$className;
            $label = preg_replace('/\ <b\>.*?<\/b>/','',$label);
            $result = '<span class="ov'.$className.'">'.$label.'</span>';
        }
        else $result = "<a".($className ? ' class="'.$className.'"':'')." href='{$link}'>{$label}</a>";
        return $result;
    }
    
/*
    function search_bar(){
        $q=$this->urlRouter->params['q'];
        $this->field_name='NAME_'.strtoupper($this->urlRouter->siteLanguage);
        
        $uri='';
        if ($this->extendedId){
            $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
            if ($this->hasCities && $this->urlRouter->cityId) {
                $uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
            }
            $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId][3].'/';
            if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
            $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1).'/';
        }elseif($this->localityId){
            $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
            $uri.=$this->localities[$this->localityId][3].'/';
            $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
            if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
            $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2).'/';
        }else {
            $uri=$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
        }
                
        ?><div class="srch"><form onsubmit="if(document.getElementById('q').value)return true;return false" action="<?= $uri ?>" method="get"><input id="q" class='q rc' name='q' value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='<?= $this->lang['search_what'] ?>' /><?php if (!$this->urlRouter->watchId) { ?><input class='bt rc' type="submit" value="<?= ( (($this->urlRouter->module!="search" && $this->urlRouter->module!="detail")|| $this->userFavorites) ? $this->lang['search']:$this->lang['search_within']) ?>" /><?php } ?></form><?php 
        if (($this->urlRouter->module=="search" && !$this->userFavorites) || $this->urlRouter->module=="detail") { 
            ?><form onsubmit="if(document.getElementById('q2').value=document.getElementById('q').value)return true;return false" action="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>" method="get"><input class='bt rc' type="submit" value="<?= $this->lang['search'] ?>" /><?php
            ?><input type="hidden" name="q" id="q2" value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" /></form><?php
            if ($this->urlRouter->params['q']) {
                //$uri=  preg_replace('/\/[0-9]{1,3}\//','/',$this->urlRouter->uri);
                //$uri=  $this->urlRouter->uri;
                echo "<a class='x' href='",$uri,"'></a>";
            }
        }
        ?><div class='cnd'><a href='<?= $this->urlRouter->getURL($this->urlRouter->countryId) ?>'><span class='ct c<?= $this->urlRouter->countryId==0?0:$this->urlRouter->countryId ?>'></span></a><?php if (!$this->urlRouter->siteTranslate){ ?><select id='country'><option><?= $this->lang['change_country'] ?></option><option value='0'><?= $this->lang['opt_all_countries'] ?></option><?php
                foreach ($this->urlRouter->countries as $country) {
                    if (isset($country[$this->fieldNameIndex]))
                        echo "<option value='".$country[3], "'>{$country[$this->fieldNameIndex]}</option>";
                } ?></select><?php
                    /*if (!$this->rss && $this->urlRouter->module=='search') {
                        echo '<span class="', $this->urlRouter->siteLanguage=='ar'?'fl':'fr' ,' gp">',
                            '<a href="', $this->urlRouter->cfg['url_base'], 
                                $this->urlRouter->uri,  $this->urlRouter->siteLanguage=='ar'?'':'en/' , '?rss=1" id="rss-link">',
                            '<img alt="RSS ', $this->title,'" src="', $this->urlRouter->cfg['url_resources'], '/img/rss.gif">',
                            '</a></span>';
                    }*/
            /*    } ?></div><?php 
                ?></div><?php 
    }*/
    
    function getPageUri(){
        if($this->pageUri){
            return $this->pageUri;
        }
        
        $this->field_name='NAME_'.strtoupper($this->urlRouter->siteLanguage);
        
        $uri='/';
        if ($this->extendedId){
            if (isset($this->urlRouter->countries[$this->urlRouter->countryId])) {
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                    $uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                }
            }
            $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
            if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
            $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1).'/';
        }elseif($this->localityId){
            if (isset($this->urlRouter->countries[$this->urlRouter->countryId])) {
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
            }
            $uri.=$this->localities[$this->localityId]['uri'].'/';
            $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
            if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
            $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2).'/';
        }else {
            $uri=$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId);
        }
        $this->pageUri = $uri;
        return $uri;
    }
    
    function search_bar(){
        
        $q=$this->urlRouter->params['q'];
        $uri = $this->getPageUri();
                
        ?><div class="srch w"><?php
        /*
        ?><div class='cnd'><a href='<?= $this->urlRouter->getURL($this->urlRouter->countryId) ?>'><span class='ct c<?= $this->urlRouter->countryId==0?0:$this->urlRouter->countryId ?>'></span></a><?php if (!$this->urlRouter->siteTranslate){ ?><select id='country'><option><?= $this->lang['change_country'] ?></option><option value='0'><?= $this->lang['opt_all_countries'] ?></option><?php
                foreach ($this->urlRouter->countries as $country) {
                    if (isset($country[$this->fieldNameIndex]))
                        echo "<option value='".$country[3], "'>{$country[$this->fieldNameIndex]}</option>";
                } ?></select><?php
                    /*if (!$this->rss && $this->urlRouter->module=='search') {
                        echo '<span class="', $this->urlRouter->siteLanguage=='ar'?'fl':'fr' ,' gp">',
                            '<a href="', $this->urlRouter->cfg['url_base'], 
                                $this->urlRouter->uri,  $this->urlRouter->siteLanguage=='ar'?'':'en/' , '?rss=1" id="rss-link">',
                            '<img alt="RSS ', $this->title,'" src="', $this->urlRouter->cfg['url_resources'], '/img/rss.gif">',
                            '</a></span>';
                    }
                } ?></div><?php */
        ?><form onsubmit="if(document.getElementById('q').value)return true;return false" action="<?= $uri ?>" method="get"><?php 
            ?><div class='q'><?php 
                ?><input id="q" name='q' value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='<?= $this->lang['search_what'] ?>' /><?php 
                if ($this->urlRouter->params['q']) {
                    //$uri=  preg_replace('/\/[0-9]{1,3}\//','/',$this->urlRouter->uri);
                    //$uri=  $this->urlRouter->uri;
                    echo "<a class='qx' href='",$uri,"'></a>";
                }
            ?></div><?php 
                /* ?><input class='bt rc bt1' type="submit" value="<?= ( (($this->urlRouter->module!="search" && $this->urlRouter->module!="detail")|| $this->userFavorites) ? $this->lang['search']:$this->lang['search_within']) ?>" /><?php */
                ?><input class='bt' type="submit" value="<?=  $this->lang['search'] ?>" /><?php 

        ?></form><?php 



        if($this->urlRouter->module=='myads' && $this->user->info['id'] && $this->user->info['level']==9) {
            
            ?><span class="sndTgl<?= isset($this->user->params['mute'])&&$this->user->params['mute']?' off':'' ?>" onclick="tglSound(this)"></span><?php
            
        }


        /*if ($this->urlRouter->module=="search" || $this->urlRouter->module=="detail") {
            ?><span class="sse"><span class="oi"></span><div class="fltr"></div></span><?php
        }*/
        /* if (($this->urlRouter->module=="search" && !$this->userFavorites) || $this->urlRouter->module=="detail") { 
            ?><form onsubmit="if(document.getElementById('q2').value=document.getElementById('q').value)return true;return false" action="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>" method="get"><?php 
                ?><input class='bt rc bt2' type="submit" value="<?= $this->lang['search'] ?>" /><?php
                ?><input type="hidden" name="q" id="q2" value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" /><?php 
            ?></form><?php
        }*/
            $this->renderLoginBox(); 
        ?></div><?php 
        
        echo $this->filter_purpose();
    }
    
    
    function parsePageLabel($rname='',$sname='', $rootId, $sectionId, $purposeId=0){
        if($sname || $purposeId || $rname) {
            
            if(!$sname) $sname = $rname;
            
            if($purposeId){
                $pname=$this->urlRouter->purposes[$purposeId][$this->fieldNameIndex];
                switch($purposeId){
                    case 1://for sale
                    case 2://for rent
                    case 8://for trade
                        $sname = $sname ? $sname.' '.$pname : $sname;
                        break;
                    case 999://various
                        if($this->urlRouter->siteLanguage=='ar'){                    
                            $sname = ($sname == 'متفرقات' ? $sname : ($sname=='' ? 'إعلانات متفرقة' : $sname.' متفرقة'));
                        }else{
                            $sname = ( (strpos($sname,'Misc.')===false || $sname == 'Miscellaneous') ? $sname : ($sname=='' ? 'Misc. Ads' : 'Misc. '.$sname));
                        }
                        break;
                    case 6://to rent
                    case 7://to buy
                        if($this->urlRouter->siteLanguage=='ar'){                    
                            $sname = $sname ? $pname.' '.$sname : $pname;
                        }else{
                            $sname = $sname ? 'Looking '.$pname.' '.$sname : 'Looking '.$pname;
                        }
                        break;
                    case 3://vacancies
                        if($this->urlRouter->siteLanguage=='ar'){                    
                            $sname = $sname ? $pname.' '.$sname : $pname;
                        }else{
                            $sname = $sname ? $sname.' '.$pname : $pname;
                        }
                        break;
                    case 4://seeking work
                        $in='';
                        if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                        $sname= $sname ? $pname.$in.' '.$sname : $pname;
                        break;
                    case 5://services
                        if($this->urlRouter->siteLanguage=='ar'){                    
                            $sname = $sname ? ( strpos($sname,$pname)===false ? $pname .' '.$sname : $sname) : $pname;
                        }else{
                            $sname = $sname ? ( strpos($sname,$pname)===false ? $sname.' '.$pname : $sname) : $pname;
                        }                        
                        break;
                }
            }else{
                if($sname=='متفرقات')$sname = $rname .' متفرقة';
                if($rootId==4 && $sname){
                    if($this->urlRouter->siteLanguage=='ar'){                    
                        $sname = $sname ? ( strpos($sname,'خدمات')===false ? 'خدمات '.$sname : $sname) : 'خدمات';
                    }else{
                        $sname = $sname ? ( strpos($sname,$pname)===false ? $sname.' Services' : $sname) : 'Services';
                    }
                }
            }
        }
        return $sname;
    }
    
    function filter_purpose(){
        if ($this->urlRouter->isPriceList) return '';
        $str='';
        
        if (($this->urlRouter->module=='search') && ($this->urlRouter->rootId || $this->urlRouter->sectionId) && !$this->userFavorites && !$this->urlRouter->watchId) {            

        if ($this->urlRouter->rootId!=4 && count($this->urlRouter->purposes)>0 && count($this->urlRouter->pagePurposes)) {
            //$str.= "<ul class='tbs w'>";
            $i=0;
            
            $hasQuery=false;
            $q="";
            if ($this->urlRouter->params['q']) {
                $hasQuery=true;
                $q='?q='.urlencode($this->urlRouter->params['q']);
            }

            if ($hasQuery) {
                if ($this->extendedId || $this->localityId) {
                    $append_uri='';
                    $extended_uri='';
                    if ($this->extendedId && isset($this->urlRouter->countries[$this->urlRouter->countryId])){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1);
                        $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                        if ($this->hasCities && $this->urlRouter->cityId && $this->urlRouter->cities[$this->urlRouter->cityId]) {
                            $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        }
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                        //echo "<b>", $extended_uri, "</b></br>";
                        
                    }elseif($this->localityId && isset($this->urlRouter->countries[$this->urlRouter->countryId])){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2);
                        $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                        
                        $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                    }
                    foreach ($this->urlRouter->pagePurposes as $pid=>$purpose) {
                        if ((int)$purpose['counter']>0) {
                            $isNew=false;
                            // $this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex]
                            $selected=($this->urlRouter->purposeId==$pid);
                                $str.= "<li>".
                                $this->renderListLink($purpose['name'],
                                    $extended_uri.$this->urlRouter->purposes[$pid][3].$append_uri.'/'.$q, $selected)."</li>";
                            
                            $i++;
                        }
                    }
                }else {
                    foreach ($this->urlRouter->pagePurposes as $pid=>$purpose) {
                        if ((int)$pid>0) {
                            $pname=  $this->extendedId ? $this->extended[$this->extendedId]['name'] :  ($this->urlRouter->sectionId ? $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex] : ($this->urlRouter->rootId ? $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex] : ''));
                            /*switch($purpose[0]){
                                case 1:
                                case 2:
                                case 8:
                                case 999:
                                    $pname = $pname.' '.$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex];
                                    break;
                                case 6:
                                case 7:
                                    $pname =$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                    break;
                                case 3:
                                    if ($this->urlRouter->sectionId) {
                                        if ($this->urlRouter->siteLanguage=="ar")
                                            $pname= 'مطلوب ' .$pname;
                                        else
                                            $pname= $pname.' '.$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex];
                                    }else {
                                        $pname=$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex];
                                    }

                                    break;
                                case 4:
                                case 5:
                                    $in="";
                                    if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                                    if ($this->urlRouter->sectionId) {
                                        $pname=$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].$in." ".$pname;
                                    }else {
                                        $pname=$this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex];
                                    }
                                    break;
                            }*/
                            switch($pid){
                                case 1:
                                case 2:
                                case 8:
                                case 999:
                                    $pname = $pname.' '. $purpose['name']; //$this->urlRouter->purposes[$pid][$this->fieldNameIndex];
                                    break;
                                case 6:
                                case 7:
                                    $pname = $purpose['name'] . ' ' . $pname;// $this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                    break;
                                case 3:
                                case 4:
                                case 5:
                                    $in="";
                                    if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                                    if ($this->urlRouter->sectionId) {
                                        $pname=$purpose['name'].$in." ".$pname;
                                    }else {
                                        $pname=$purpose['name'];
                                    }
                                    break;
                            }
                        $selected=($this->urlRouter->purposeId==$pid);
                        $str.= "<li>".$this->renderListLink($pname, $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,
                            $this->urlRouter->sectionId,$pid).$q, $selected)."</li>";
                        $i++;
                        }
                    }
                    if(isset($this->urlRouter->sections[$this->urlRouter->sectionId][5]) && $this->urlRouter->sections[$this->urlRouter->sectionId][5]){
                        $secId = $this->urlRouter->sections[$this->urlRouter->sectionId][5];
                        $str.= "<li>".$this->renderListLink($this->urlRouter->sections[$secId][$this->fieldNameIndex], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->sections[$secId][4],
                            $secId,$this->urlRouter->sections[$this->urlRouter->sectionId][9]).$q, false)."</li>";
                        $i++;
                    }
                }
            }else {
                    $append_uri='';
                    $extended_uri='';
                    if ($this->extendedId){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1);
                        if($this->urlRouter->countryId){
                            $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                            if ($this->hasCities && $this->urlRouter->cityId) {
                                $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                            }
                        }
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                        //echo "<b>", $extended_uri, "</b></br>";
                        
                    }elseif($this->localityId){
                        $append_uri='/'.($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2);
                        $extended_uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                        /*if ($this->hasCities && $this->urlRouter->cityId) {
                            $extended_uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                        }*/
                        $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                        $extended_uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                    }

                    $base_name = ($this->extendedId && isset($this->extended[$this->extendedId])) ? $this->extended[$this->extendedId]['name'] : (($this->urlRouter->sectionId && isset($this->urlRouter->pageSections[$this->urlRouter->sectionId])) ? $this->urlRouter->pageSections[$this->urlRouter->sectionId]['name'] : (($this->urlRouter->rootId && isset($this->urlRouter->pageRoots[$this->urlRouter->rootId])) ? $this->urlRouter->pageRoots[$this->urlRouter->rootId]['name'] : ''));
                    foreach ($this->urlRouter->pagePurposes as $pid=>$purpose) {
                        $pname = $base_name;
                        switch($pid){
                            case 1:
                            case 2:
                            case 8:
                            case 999:
                                $pname = $pname.' '.$purpose['name'];// $this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex];
                                break;
                            
                            case 6:
                            case 7:
                                $pname = $purpose['name'].' '.$pname;// $this->urlRouter->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                break;
                            
                            case 3:
                            case 4:
                            case 5:
                                $in="";
                                if ($this->urlRouter->siteLanguage=="en")$in=" {$this->lang['in']}";
                                if ($this->urlRouter->sectionId) {
                                    $pname=$purpose['name'].$in." ".$pname;
                                }else {
                                    $pname=$purpose['name'];
                                }
                                break;
                        }
                        $isNew=false;
                        $selected=($this->urlRouter->purposeId==$pid /*$purpose[0]*/);
                        if ($this->extendedId || $this->localityId) {
                            $str.= "<li>".
                            $this->renderListLink($pname, $extended_uri.$this->urlRouter->purposes[$pid][3].$append_uri.'/', $selected)."</li>";
                        } else {
                            if (!$selected && $this->checkNewUserContent($purpose['unixtime'])) $isNew=true;
                                $str.= "<li".($isNew?" class='nl'":"").">".
                                $this->renderListLink($pname . " <span>(" . $purpose['counter'] . ")</span>",
                                        $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,
                                                        $this->urlRouter->sectionId,$pid /* $purpose[0]*/), $selected)."</li>";
                        }
                        $i++;
                    }
                    
                    if(isset($this->urlRouter->sections[$this->urlRouter->sectionId][5]) && $this->urlRouter->sections[$this->urlRouter->sectionId][5]){
                        $secId = $this->urlRouter->sections[$this->urlRouter->sectionId][5];
                        $str.= "<li>".$this->renderListLink($this->urlRouter->sections[$secId][$this->fieldNameIndex], $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->sections[$secId][4],
                            $secId,$this->urlRouter->sections[$this->urlRouter->sectionId][9]), false)."</li>";
                        $i++;
                    }
            }
            if($str && $i>1) {
                $str='<ul class="tbs w t'.$i.'">'.$str.'</ul>';
            }else{
                $str='';
            }
        }
        
        }
        return $str;
    }
    

    function top(){
        $url='';
        $cityId=$this->urlRouter->cityId;
        if ($cityId) {
            if ($this->urlRouter->countryId==0) {
                $cityId=0;            
            } else {
                if (count($this->urlRouter->countries[$this->urlRouter->countryId]['cities'])==0) {
                    $city_id=0;
                }
            }
        }
        
        //if (count($this->urlRouter->countryCities)<2)$cityId=0;
        
            switch ($this->urlRouter->module){
                case 'detail':
                    if (!empty($this->detailAd)){
                        if ($this->urlRouter->siteLanguage=='ar') {
                            $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                        } else {
                            $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                        }
                        break;
                    }
                case 'search':
                case 'index':
                    if($this->urlRouter->userId) $url='/'.($this->partnerInfo['uri']).'/';
                    elseif($this->urlRouter->watchId) $url='/watchlist/';
                    elseif ($this->userFavorites) $url='/favorites/';
                    else $url=$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId,false);

                    if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                    if ($this->urlRouter->params['start']) $url.=$this->urlRouter->params['start'].'/';
                    if($this->pageUserId){
                        $url.='?u='.$this->user->encodeId($this->pageUserId);
                    }elseif ($this->urlRouter->isDynamic) {
                        $url.='?';
                        $params='';
                        $append=false;
                        if ($this->urlRouter->params['q']) {
                            $params.='q='.urlencode($this->urlRouter->params['q']);
                            $append=true;
                        }
                        $url.=$params;
                    }
                    break;                
                case 'myads':
                    $url='/myads/';
                    if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                    $sub=$this->get('sub');
                    if(in_array($sub,array('pending','archive','drafts'))){
                        $url.='?sub='.$sub;
                    }
                    break;
                default:
                    $url='/'.$this->urlRouter->module.'/';
                    if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                    break;
            }
            $adLang='';
            if ($this->urlRouter->siteLanguage=='ar') {
                //$link = '<a class="en" href="'.$url.'">English</a>';
            } else {
                $adLang=$this->urlRouter->siteLanguage.'/';
                //$link = '<a class="ar" href="'.$url.'">عربي</a>';
            }

            //$mobileLink='<form action="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'" method="post"><input type="hidden" name="mobile" value="1" /><a onclick="this.parentNode.submit();">'.$this->lang['mobile'].'</a></form>';
            /* not isApp */
            //if (!$this->urlRouter->isApp) {
            ?><div class='top'><?php
            /*if ($this->topMenuIE) {
                ?><table><tr><td width="auto"><a class="lg" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>"><img src="<?= $this->urlRouter->cfg['url_resources']?>/img/msl.png" width="100px" height="30px" alt="Mourjan.com" /></a></td><td width="80px"><span><?= $link ?></span></td><?php if (!$this->urlRouter->userId){ ?><td width="<?= $this->urlRouter->siteLanguage=="ar"? '120px' :'130px' ?>"><span><?= $mobileLink ?></span></td><td width="<?= $this->urlRouter->siteLanguage=="ar"? '135px' :'80px' ?>"><span><?= $this->user->info['id']? "<a class='nt' href='?logout=".$this->user->info['provider']."'>{$this->lang['signout']}</a>":"<a class='login nt' href='' rel='nofollow'>{$this->lang['signin']}</a>" ?></span></td><?php } ?></tr></table><?php
            }else {*/  
                ?><h1><?= $this->title ?></h1><?php  
                /*
                if(0 && $this->urlRouter->module=='index'){
                    ?><span class="lg"><?php
                    ?><span class="i h"></span><?php
                    ?><img width="100px" height="30px" src="<?= $this->urlRouter->cfg['url_resources']?>/img/msl.png" alt="Mourjan.com" /><?php
                    ?></span><?php
                }else {
                    ?><a class="lg" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>"><?php
                    ?><span class="i h"></span><?php
                    ?><img width="100px" height="30px" src="<?= $this->urlRouter->cfg['url_resources']?>/img/msl.png" alt="Mourjan.com" /><?php
                    ?></a><?php
                }
                 * 
                 */
                ?><div class="tob"><?php 
                    //if(!$this->urlRouter->cfg['enabled_ads']){
                    if($this->urlRouter->module!='index'){
                        if(0 && $this->urlRouter->module=='index'){
                            ?><span class="lg"><?php
                            ?><span class="i h"></span><?php
                            ?><img width="100px" height="30px" src="<?= $this->urlRouter->cfg['url_img']?>/msl.png" alt="<?= $this->lang['mourjan'] ?>" /><?php
                            ?></span><?php
                        }else {
                            ?><a class="lg" title="<?= $this->lang['mourjan'] ?>" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>"><?php
                            ?><span class="i h"></span><?php
                            ?><img width="100px" height="30px" src="<?= $this->urlRouter->cfg['url_img']?>/msl.png" alt="<?= $this->lang['mourjan'] ?>" /><?php
                            ?></a><?php
                        }
                    }
                    if (!$this->urlRouter->userId){
                        if ($this->urlRouter->siteLanguage=='ar') {
                            ?><a class="gl" href="<?= $url ?>">English</a><?php
                            ?><span class="gr">عربي</span><?php
                        } else {
                            ?><span class="gl">English</span><?php
                            ?><a class="gr" href="<?= $url ?>">عربي</a><?php
                        }
                    }
                ?></div><?php
                if (!$this->urlRouter->userId && $this->urlRouter->module!='post'){ 
                    ?><a class="pb" href="/post/<?= $adLang ?>"><span class="i p"></span><?= $this->lang['postFree'] ?></a><?php
                }
                if ($this->urlRouter->userId){
                        if ($this->urlRouter->siteLanguage=='ar') {
                            ?><a class="gl" href="<?= $url ?>">English</a><?php
                            ?><span class="gr">عربي</span><?php
                        } else {
                            ?><span class="gl">English</span><?php
                            ?><a class="gr" href="<?= $url ?>">عربي</a><?php
                        }
                }
                /*
                 ?><span><?= $link ?></span><?php if (!$this->urlRouter->userId){ ?><span><?= $mobileLink ?></span><span><?= $this->user->info['id']? '<a class="nt" href="?logout='.$this->user->info['provider'].'">'.$this->lang['signout'].'</a>'.($this->urlRouter->module!='post' && $this->urlRouter->cfg['enabled_post'] ?'<a class="nt" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['button_ad_post'].'</a>':''):'<a class="login nt" href="/watchlist/'.$adLang.'" rel="nofollow">'.$this->lang['signin'].'</a>'.($this->urlRouter->module!='post'?'<a class="login nt" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['button_ad_post'].'</a>':'') ?></span><?php }else {
                if($this->urlRouter->userId && $this->urlRouter->userId==$this->user->info['id']) {
                        $lang=$this->urlRouter->siteLanguage=='ar'?'':'en/';
                    if (isset($_GET['preview'])){
                        ?><span><a class="nt" href="/<?= $this->partnerInfo['uri'] ?>/<?= $lang ?>"><?= $this->lang['backEditPage'] ?></a></span><?php
                    }else {
                        ?><span><a class="nt" href="/<?= $this->partnerInfo['uri'] ?>/<?= $lang ?>?preview=true"><?= $this->lang['previewPage'] ?></a></span><?php
                    }
                }
            //}
        }*/ ?></div><?php  
           // } /* end not isApp */
        if(!$this->urlRouter->userId && $this->urlRouter->cfg['enabled_ads']){
        ?><div class="w tpb"><?php 
        ?><a class="lg" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>" title="<?= $this->lang['mourjan'] ?>"><img height="90" width="130" src="<?= $this->urlRouter->cfg['url_css'] ?>/i/logo.jpg" alt="<?= $this->lang['mourjan'] ?>" /></a><?php 
            echo $this->fill_ad('zone_0', 'ad_t');
        ?></div><?php
        }else{
            if(!$this->notifications) {
                ?><div class="tps"></div><?php
            }
        }
        $this->renderNotifications();
        /*if ($this->urlRouter->siteTranslate){
            ?><div class='top'><?= $this->lang['title_translate'] ?><?php
            ?></div><?php
        }*/
    }


    function topMobile(){
        $q=$this->get('q', 'filter');
        if (!$this->user->info['id']){
            if ($this->userFavorites) {
                $this->lang['hint_login']=$this->lang['hint_login_favorites'];
                $this->requireLogin=true;
            }elseif($this->urlRouter->watchId) {
                $this->lang['hint_login']=$this->lang['hint_login_watch'];
                $this->requireLogin=true;
            }else if($this->urlRouter->module=='myads' || $this->urlRouter->module=='post' || $this->urlRouter->module=='account'){
                $this->requireLogin=true;
            }
        }
        //$backButton="<div class='bt btBack rc'><div><div></div></div></div>";
        $backButton='<span class="bt-back"></span>';
        $hasBack=false;
        $cityId=$this->urlRouter->cityId;
        if ($cityId) {
            if (empty($this->urlRouter->countries[$this->urlRouter->countryId]['cities'])) 
                $cityId=0;
        }
        //if (count($this->urlRouter->countryCities)<2)$cityId=0;
        //$headTitle='<a href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'">Mourjan.com</a>';
        $headTitle='Mourjan.com';
        switch ($this->urlRouter->module) {
            case "detail":
                $tmpUrl='';
                if (isset($this->urlRouter->params['tag_id']) && $this->urlRouter->params['tag_id']){
                    $tmpUrl = $this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
                }elseif(isset($this->urlRouter->params['loc_id']) && $this->urlRouter->params['loc_id']){
                    if (isset($this->localities[$this->urlRouter->params['loc_id']]) && $this->localities[$this->urlRouter->params['loc_id']]['parent_geo_id']){
                        $tmpId=$this->urlRouter->params['loc_id'];
                        $tmpUrl = '/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/'.$this->localities[$tmpId]['uri'].'/'.$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/'.($this->urlRouter->purposeId ? $this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/' : '').($this->urlRouter->siteLanguage!='ar' ? $this->urlRouter->siteLanguage.'/':'').'c-'.$tmpId.'-2/';
                    }else {
                        $tmpUrl = $this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
                    }
                }else {
                    $tmpUrl = $this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
                }
                if ($this->urlRouter->params['start']>1){
                    $tmpUrl.=$this->urlRouter->params['start'].'/';
                }
                if ($this->urlRouter->params['q']){
                    $tmpUrl.='?q='.urlencode($this->urlRouter->params['q']);
                }
                $backButton = '<a class="back" href="'.$tmpUrl.'"></a>';
                $hasBack=true;
                break;
            case "search":
                if ($this->urlRouter->sectionId) {
                    if ($this->extendedId){
                        $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).'"></a>';
                    }elseif($this->localityId){
                        $this->localityId = $this->localityId+0;
                        if (isset($this->localities[$this->localityId]) && isset($this->localities[$this->localities[$this->localityId]['parent_geo_id']+0])){
                            $tmpId=$this->localities[$this->localityId]['parent_geo_id']+0;
                            $backButton = '<a class="back" href="/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/'.$this->localities[$tmpId]['uri'].'/'.$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/'.($this->urlRouter->purposeId ? $this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/' : '').($this->urlRouter->siteLanguage!='ar' ? $this->urlRouter->siteLanguage.'/':'').'c-'.$tmpId.'-2/"></a>';
                        }else {
                            $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId).'"></a>';
                        }
                    }else {
                        $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId).'"></a>';
                    }
                    $hasBack=true;
                }elseif ($this->urlRouter->rootId || $this->urlRouter->watchId || $this->userFavorites) {
                    $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'"></a>';
                    $hasBack=true;
                }
                break;
            case "index":
            case "search":
                if ($this->urlRouter->sectionId) {
                    $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId).'"></a>';
                    $hasBack=true;
                }elseif ($this->urlRouter->rootId || $this->urlRouter->watchId || $this->userFavorites) {
                    $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'"></a>';
                    $hasBack=true;
                }
                break;
            default:
                $backButton = '<a class="back" href="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'"></a>';
                $hasBack=true;
                break;
        }
        switch ($this->urlRouter->module) {
            case 'index':
                if ($this->urlRouter->rootId) {
                    $headTitle=$this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex];
                }elseif($this->urlRouter->cityId){
                    $headTitle=$this->lang['mourjan'].' '.$this->cityName;
                }elseif($this->urlRouter->countryId){
                    $headTitle=$this->lang['mourjan'].' '.$this->countryName;
                }
                break;
            case 'detail':
                if (!$this->detailAdExpired) {
                    $headTitle=$this->title;
                    break;
                }
            case 'search':
                $headTitle=  $this->title;
                /*
                if ($this->urlRouter->sectionId) {
                    $headTitle=$this->sectionName;
                }elseif ($this->urlRouter->rootId){
                    $headTitle=$this->rootName;
                }elseif($this->urlRouter->cityId){
                    $headTitle=$this->cityName;
                }elseif($this->urlRouter->countryId){
                    $headTitle=$this->countryName;
                }*/
                break;
            case 'contact':
                $headTitle=$this->lang['title'];
                break;
            default:
                $headTitle=  $this->title;
                break;
        }
        /* ?><div class='top'><?= $headTitle ?></div><div id="srch" class='srch sh'><form action="<?= $this->urlRouter->uri ?>" method="get"><input id="q" name="q" class="q rc" value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='<?= $this->lang['search_what'] ?>' /><div class="bt qSrch rc" onclick="s()" ontouchstart="s()"><div><div></div></div></div><?php
        ?></form></div><div class="bt btSrch rc" onclick="ts(this,'c')" ontouchstart="ts(this,'t')"><div></div></div><?php */
        /* not isApp */
        if (!$this->urlRouter->isApp) {
        ?><div class='top'><?php
        $url='';
        switch ($this->urlRouter->module){
            case 'detail':
                if (!empty($this->detailAd)){
                    if ($this->urlRouter->siteLanguage=='ar') {
                        //$url = $this->urlRouter->cfg['url_base'].sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                        $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                    } else {
                        //$url = $this->urlRouter->cfg['url_base'].sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                        $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                    }
                    break;
                }
            case 'search':
                //if($this->urlRouter->userId) $url='/'.($this->partnerInfo['uri']).'/';
                if($this->urlRouter->watchId) $url='/watchlist/';
                elseif ($this->userFavorites) $url='/favorites/';
                else $url=$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId,false);

                if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                if ($this->urlRouter->params['start']) $url.=$this->urlRouter->params['start'].'/';
                if($this->pageUserId){
                    $url.='?u='.$this->user->encodeId($this->pageUserId);
                }elseif ($this->urlRouter->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->urlRouter->params['q']) {
                        $params.='q='.urlencode($this->urlRouter->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;
            case 'myads':
                $url='/myads/';
                if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                $sub=$this->get('sub');
                if(in_array($sub,array('pending','archive','drafts'))){
                    $url.='?sub='.$sub;
                }
                break;
            case 'index':
                $url=$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId,false);
                if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                if ($this->urlRouter->params['start']) $url.="{$this->urlRouter->params['start']}/";
                if ($this->urlRouter->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->urlRouter->params['q']) {
                        $params.='q='.urlencode($this->urlRouter->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;
            default:
                $url='/'.$this->urlRouter->module.'/';
                if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                break;
        }
        if ($hasBack) { echo $backButton; } 
        if ($this->urlRouter->countryId) {
            if ($this->urlRouter->params['q']) {
                echo '<div onclick="ose(this)" class="button srch on"><span class="k"></span></div>';
            }else {
                echo '<div onclick="ose(this)" class="button srch"><span class="k"></span></div>';
            }
        }
        /* if ($this->urlRouter->module!='index' || ($this->urlRouter->module=='index' && $this->urlRouter->rootId)) {
            ?><a class="bt fl nsh" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>"><span class="bt-home"></span></a>
            <?php } */ ?><h1 id="title" class="<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->urlRouter->siteLanguage=='en'?'e':'a') ?>"><?php
        echo $headTitle;
        ?></h1></div><?php   
        
        $menu='';
        $menuIdx=0;
        $loginErr = (isset($_GET['login']) && $_GET['login']=='error') ? 1:0;
        $loginECode = ($loginErr && isset($_GET['code']) && $_GET['code']) ? 1:0;
        $lang='';
        if ($this->urlRouter->siteLanguage!='ar') $lang=$this->urlRouter->siteLanguage.'/';
        
        
        if ($this->user->info['id']) {
            $menu .= "<li id='sil' onclick='uPO(this,1)' class='button'><span class='k log on'></span></li>";
            $menuIdx++;
        }else {
            if ($this->requireLogin)
                $menu .= "<li id='sil'><span class='k log op'></span></li>";
            else
                $menu .= "<li id='sil' onclick='uPO(this)' class='button'><span class='k log".($loginErr ? ' op':'')."'></span></li>";
            $menuIdx++;
        }
        
        if ( ($this->urlRouter->module!='index') || ($this->urlRouter->module=='index' && ($this->urlRouter->sectionId || $this->urlRouter->rootId) ) ) {
            $menu .= "<li><a href='{$this->urlRouter->getURL($this->user->params['country'],$this->user->params['city'])}'><span class='k home'></span></a></li>";
            $menuIdx++;
        }elseif($this->urlRouter->countryId) {
            //$menu .= "<li><span class='k home on'></span></li>";
            $menu .= "<li><span class='ps'><span class='k home on'></span></span></li>";
            $menuIdx++;
        }
        
        if ($this->user->info['id']) {
            
            if ($this->urlRouter->module=="search" && $this->urlRouter->watchId) {
                $menu .= "<li><span class='ps'><span class='k eye on'></span></span></li>";
            }else {
                $menu .= "<li><a href='/watchlist/{$lang}'><span class='k eye'></span></a></li>";
            }
            $menuIdx++;
            if ($this->urlRouter->module=="search" && $this->userFavorites) {
                $menu .= "<li><span class='ps'><span class='k fav on'></span></span></li>";
            }else {
                $menu .= "<li><a href='/favorites/{$lang}'><span class='k fav'></span></a></li>";
            }
            $menuIdx++;
        }
        
        
        if ($this->urlRouter->siteLanguage=='ar') {
            $menu .= "<li><a href='{$url}'>English</a></li>";
            $menuIdx++;
        } else {
            $menu .= "<li><a href='{$url}'><b>عربي</b></a></li>";
            $menuIdx++;
        }
        ?><div><?php
        } /* End not is App */
        if ($this->urlRouter->countryId) {
            $uri='';
            if ($this->extendedId){
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                    $uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                }
                $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1).'/';
            }elseif($this->localityId){
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                $uri.=$this->localities[$this->localityId]['uri'].'/';
                $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2).'/';
            }else {
                $uri=$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
            }
        ?><div class="sef<?= $this->urlRouter->params['q'] ? ' on':'' ?>"><?php
            ?><form action="<?= $uri ?>" method="get"><?php
                    ?><div class="dq"><?php
                    ?><input id="q" name="q" onkeyup="idir(this)" onfocus="this.select()" value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='<?= $this->lang['search_what'] ?>' /><?php
                    ?></div><?php
                    ?><div class="dqb"><?php
                    ?><input type="submit" onclick="if(this.parentNode.previousSibling.firstChild.value!='')return true;else return false" class="qb button" value="" /><?php
                    ?></div><?php 
            ?></form><?php
        ?></div><?php
        }
        /* Start not isApp */
        //error_log("isApp: {$this->urlRouter->isApp}", 0 );
        if (!$this->urlRouter->isApp) {
        ?><ul class="menu f<?= $menuIdx ?>"><?= $menu ?></ul><?php
        ?></div><?php
        if ($this->requireLogin){
            if($this->urlRouter->module=='account' && isset($this->user->pending['email_validation']) && $this->user->pending['email_validation']==2){
                $this->lang['hint_login']=$this->lang['login_email_verify'];
            }
                
            ?><div class="str ctr"><?= $this->lang['hint_login'] ?></div><br /><?php
        }
        ?><div id="sif" class='si<?= ($loginErr || $this->requireLogin ? ' blk':'') ?>'><?php 
        if($this->user->info['id']) {
            if($this->urlRouter->module != 'index' || ($this->urlRouter->module == 'index' && $this->urlRouter->rootId) || ($this->urlRouter->module == 'index' && !$this->urlRouter->countryId)) {
            
            ?><ul class="ls us br"><?php 
            if($this->urlRouter->module != 'post') {
                   ?><li><a href="/post/<?= $lang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?></b></li><?php 
            }
            ?></ul><?php
            ?><ul class="ls us br"><?php 
            if($this->urlRouter->module != 'search' || !$this->userFavorites) {
                   ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></b></li><?php 
            }
            if($this->urlRouter->module != 'search' || !$this->urlRouter->watchId) {
                   ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic k eye on"></span><?= $this->lang['myList'] ?></b></li><?php 
            }
            ?></ul><?php
            ?><ul class="ls us br"><?php
                ?><li class="h"><b><?= $this->lang['myAds'] ?></b></li><?php
            $sub=(isset($_GET['sub']) && $_GET['sub'] ? $_GET['sub']:'');
            if($this->urlRouter->module != 'myads' || ($this->urlRouter->module == 'myads' && $sub!='') ) {
                    ?><li><a href="/myads/<?= $lang ?>"><span class="ic aon"></span><?= $this->lang['ads_active'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic aon"></span><?= $this->lang['ads_active'] ?></b></li><?php
            }
            if($this->urlRouter->module != 'myads'  || ($this->urlRouter->module == 'myads' &&  $sub!='pending') ) {
                    ?><li><a href="/myads/<?= $lang ?>?sub=pending"><span class="ic apd"></span><?= $this->lang['ads_pending'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></b></li><?php
            }
            if($this->urlRouter->module != 'myads'  || ($this->urlRouter->module == 'myads' &&  $sub!='drafts'))  {
                ?><li><a href="/myads/<?= $lang ?>?sub=drafts"><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></b></li><?php
            }
            if($this->urlRouter->module != 'myads'  || ($this->urlRouter->module == 'myads' &&  $sub!='archive'))  {
                ?><li><a href="/myads/<?= $lang ?>?sub=archive"><span class="ic afd"></span><?= $this->lang['ads_archive'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></b></li><?php
            }
            ?></ul><?php
            
            ?><ul class="ls us br"><?php 
                if($this->urlRouter->module != 'account') {
                    ?><li><a href="/account/<?= $lang ?>"><?= $this->lang['myAccount'] ?><span class="et"></span></a></li><?php            
                }else{
                    ?><li class="on"><b><?= $this->lang['myAccount'] ?></b></li><?php
                }
            ?></ul><?php
            ?><ul class="br"><?php
            }else{
               ?><ul><?php 
            }
            ?><li><a class="bt cl" href="?logout=<?= $this->user->info['provider'] ?>"><?= $this->lang['signout'] ?></a></li><?php
            ?></ul><?php
            
        }else {
            if ($loginErr) {
                ?><div class="nb ctr err"><?= $this->lang['signin_error'] ?></div><?php
            }
            ?><h2 class="ctr"><?= $this->lang['signin_m'] ?></h2><?php
            ?><ul><?php
            ?><li><a class="bt mj" href="/signin/<?= $lang ?>">Mourjan</a></li><?php
            ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
            ?><li><a class="bt go" href="?provider=google">Google</a></li><?php
            ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
            ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
            ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
            ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
            if (!$this->requireLogin){ ?><li><span onclick="csif()" class="button bt cl"><?= $this->lang['cancel'] ?></span></li><?php } 
            else { ?><br /><?php }
            ?></ul><?php 
        }
    }
    /* end not isApp */
        ?></div><?php 
        if (!$this->urlRouter->isApp) {
            $this->renderNotificationsMobile();
        }
        /* if ($this->urlRouter->module!='contact') {
            $uri='';
            if ($this->extendedId){
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                if ($this->hasCities && $this->urlRouter->cityId) {
                    $uri.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                }
                $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId][3].'/';
                if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'q-'.$this->extendedId.'-'.($this->urlRouter->countryId ? ($this->hasCities && $this->urlRouter->cityId ? 3:2) :1).'/';
            }elseif($this->localityId){
                $uri='/'.$this->urlRouter->countries[$this->urlRouter->countryId][3].'/';
                $uri.=$this->localities[$this->localityId][3].'/';
                $uri.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                if ($this->urlRouter->purposeId)$uri.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                $uri.=($this->urlRouter->siteLanguage!='ar'?$this->urlRouter->siteLanguage.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->urlRouter->cityId ? 3:2).'/';
            }else {
                $uri=$this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
            }
        }
        
        if ($this->urlRouter->countryId) {
            ?><div class='srch'><?php
                ?><form action="<?= $uri ?>" method="get"><?php
                    ?><div class="dq"><?php
                    ?><input id="q" name="q" class="q" value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='&nbsp;<?= $this->lang['search_what'] ?>' /><?php
                    ?></div><?php
                    ?><div class="dqb"><?php
                    ?><input type="submit" class="qb" value="" /><?php
                    ?></div><?php
                ?></form><?php
              ?></div><?php
        } */
    }

    function paginationMobile(){      
        $qtotal_found = $this->searchResults['body']['total_found'];
        $appendLang=($this->urlRouter->siteLanguage=='ar'?'/':'/'.$this->urlRouter->siteLanguage.'/');
        if (!$this->paginationString) {
            
            if ($qtotal_found>0) {
                $pages = ceil($qtotal_found/$this->num);
                $tmp=ceil($this->urlRouter->cfg['search_results_max']/$this->num);
                if ($pages>$tmp) $pages=(int)$tmp;
                if ($pages>1) {
                    
                    if ($this->userFavorites) $link='/favorites'.$appendLang.'%s';
                    elseif ($this->urlRouter->watchId)
                        $link='/watchlist'.$appendLang.'%s';  
                    elseif ($this->extendedId) {
                        $idx=1;
                        $link='/';
                        if ($this->urlRouter->countryId) {
                            $idx=2;
                            $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                        }
                        if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                            $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                            $idx=3;
                        }
                        $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                        if ($this->urlRouter->purposeId)
                            $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                            $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
                    }elseif ($this->localityId) {
                        $idx=2;
                        $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                        /*if ($this->hasCities && $this->urlRouter->cityId) {
                            $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                            $idx=3;
                        }*/
                        $link.=$this->localities[$this->localityId]['uri'].'/';
                        if ($this->urlRouter->sectionId) 
                            $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                        else 
                            $link.=$this->urlRouter->pageRoots[$this->urlRouter->rootId]['uri'].'/';
                        if ($this->urlRouter->purposeId)
                            $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                        if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                            $link.='c-'.$this->localityId.'-'.$idx.'/%s';
                        }else 
                            $link=$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId).'%s';
                    $uri_query='';
                    $linkAppend='?';
                    if($this->pageUserId){
                        $uri_query=$linkAppend.'u='.$this->user->encodeId($this->pageUserId);
                        $linkAppend='&';
                    }
                    if ($this->urlRouter->params['q']) {
                        $uri_query=$linkAppend.'q='.urlencode($this->urlRouter->params['q']);
                        $linkAppend='&';
                    }

                    $result='';
                    $currentPage=($this->urlRouter->params['start']?$this->urlRouter->params['start']:1);
                    
                    $result.= '<div class="nav sh">';
                    $result.="<span class='num'>{$currentPage} {$this->lang['of']} {$pages}</span>";
                    if ($currentPage>1) {
                        //$first="<a href='".sprintf($link,'')."'><span class='bt pp nsh'><span></span></span></a>";
                        $offset=$currentPage-1;
                        $last='<a class="p" href="';
                        if ($offset>1)
                            $last.=sprintf ($link, "{$offset}/{$uri_query}");
                        else 
                            $last.=sprintf ($link, $uri_query);
                        //$last .='"><span class="bt p nsh"><span></span></span></a>';
                        $last .='"><span class="k"></span>'.$this->lang['prev'].'</a>';
                        $result.=$last;
                        //$result.=$first.$last;
                    }
                    if ($currentPage<$pages) {
                        $offset=$this->urlRouter->params['start']+1;
                        if ($offset==1) $offset++;
                        $first='<a class="n" href="';
                        $first.=sprintf ($link, "{$offset}/{$uri_query}");
                        //$first.='"><span class="bt n nsh"><span></span></span></a>';
                        $first .='">'.$this->lang['next'].'<span class="k"></span></a>';
                        $offset=$pages;
                        $result.=$first;
                        /*$last='<a href="';
                        $last.=sprintf ($link, "{$offset}/$uri_query");
                        $last.='"><span class="bt nn nsh"><span></span></span></a>';
                        $result.=$last.$first;*/
                    }
                    $result.='</div>';
                    $this->paginationString=$result;
                }else{
                    $this->paginationString=$result = '<br />';
                }
            }
        }
        return $this->paginationString;
    }

    
    function pagination($link=null){
        if (!$this->paginationString || $link) {
            $appendLang=($this->urlRouter->siteLanguage=='ar'?'/':'/'.$this->urlRouter->siteLanguage.'/');
            $result='';
            if ($this->urlRouter->userId){
                $link='/'.$this->partnerInfo['uri'].$appendLang.'%s';                
            }elseif ($this->urlRouter->watchId){
                $link='/watchlist'.$appendLang.'%s';                
            }elseif ($this->userFavorites) $link='/favorites'.$appendLang.'%s';
            elseif ($this->extendedId) {
                $idx=1;
                $link='/';
                if ($this->urlRouter->countryId) {
                    $idx=2;
                    $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                }
                if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                    $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    $idx=3;
                }
                $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                if ($this->urlRouter->purposeId)
                $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
            }elseif ($this->localityId) {
                $idx=2;
                $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                /*if ($this->hasCities && $this->urlRouter->cityId) {
                    $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    $idx=3;
                }*/
                $link.=$this->localities[$this->localityId]['uri'].'/';
                if ($this->urlRouter->sectionId) 
                    $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                else 
                    $link.=$this->urlRouter->pageRoots[$this->urlRouter->rootId]['uri'].'/';
                if ($this->urlRouter->purposeId)
                    $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                $link.='c-'.$this->localityId.'-'.$idx.'/%s';
            }
            else $link=$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId).'%s';

            $uri_query='';
            $linkAppend='?';
            if($this->pageUserId){
                $uri_query=$linkAppend.'u='.$this->user->encodeId($this->pageUserId);
                $linkAppend='&';
            }
            if ($this->urlRouter->params['q']) {
                $uri_query=$linkAppend.'q='.urlencode($this->urlRouter->params['q']);
                $linkAppend='&';
            }
            
            $qtotal_found = $this->searchResults['body']['total_found'];
            if ($qtotal_found>0) {
                $pages = ceil($qtotal_found/$this->num);
                
                $tmp=$this->urlRouter->cfg['search_results_max']/$this->num;
                if ($pages>$tmp) $pages=$tmp;
                if ($pages>1) {
                    //$currentPage=ceil(($this->urlRouter->params['start']+1)/$this->num);
                    
                    $currentPage = ($this->urlRouter->params['start']?$this->urlRouter->params['start']:1);
                    $isFirst=true;
                    if ($currentPage>1) {
                        $result.='<li class="prev">';
                        $result.='<a target="_self" href="';
                        
                        $page_no= $currentPage-1;
                        if ($page_no>1)
                            $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        else 
                            $result.=sprintf ($link, $uri_query);
                        
                        $result.='">';
                        $result.='< '.$this->lang['previous'];
                        $result.='</a>';
                        $result.='</li>';
                        $isFirst=false;
                    }
                    $pageMargin=3;
                    //if($this->urlRouter->userId) $pageMargin=4;
                    $startPage=$currentPage-$pageMargin;
                    if ($startPage<=0) $startPage=1;
                    $endPage=$currentPage+$pageMargin;
                    if ($endPage>$pages) $endPage=$pages;
                    while ($startPage<=$endPage) {
                        if ($startPage==$currentPage) {
                            $result.='<li class="'.($isFirst ? 'fst ':'').'op">'.$startPage.'</li>';
                        } else {
                            $page_no=$startPage-1;
                            $result.='<li'.($isFirst ? ' class="fst"':'').'><a target="_self" href="';
                            
                            if ($page_no)
                                $result.=sprintf ($link, "{$startPage}/{$uri_query}");
                            else 
                                $result.=sprintf($link, $uri_query);
                            
                            $result.='">'.$startPage.'</a></li>';
                        }
                        $isFirst=false;
                        $startPage++;
                    }
                    if ($currentPage<$pages) {
                        $result.='<li class="next">';
                        $offset=$this->urlRouter->params['start']+$this->num;
                        $result.='<a target="_self" href="';
                        
                        //$result.=$link.$linkAppend.'start='.$offset;
                        $page_no=$currentPage+1;
                        $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        $result.='">';
                        $result.=$this->lang['next'].' >';
                        $result.='</a></li>';
                        $result.= '</ul>';
                        $result= '<ul class="nav">'.$result;
                    }else{                        
                        $result.= '</ul>';
                        $result= '<ul class="nav nev">'.$result;
                    }
                    $this->paginationString=$result;
                }

            }
        }
        return $this->paginationString;
    }
    
    function pagination_bk($link=null){
        if (!$this->paginationString || $link) {
            $rightCB='rbrc';
            $leftCB='rblc';
            $appendLang='/';
            if ($this->urlRouter->siteLanguage=='en') {
                $rightCB='rblc';
                $leftCB='rbrc';
                $appendLang='/en/';
            }
            $result='';
            if ($this->urlRouter->userId){
                $link='/'.$this->partnerInfo['uri'].$appendLang.'%s';                
            }elseif ($this->urlRouter->watchId){
                $link='/watchlist'.$appendLang.'%s';                
            }elseif ($this->userFavorites) $link='/favorites'.$appendLang.'%s';
            elseif ($this->extendedId) {
                $idx=1;
                $link='/';
                if ($this->urlRouter->countryId) {
                    $idx=2;
                    $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                }
                if (isset($this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId])) {
                    $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    $idx=3;
                }
                $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                if ($this->urlRouter->purposeId)
                $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
            }elseif ($this->localityId) {
                $idx=2;
                $link='/'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].'/';
                /*if ($this->hasCities && $this->urlRouter->cityId) {
                    $link.=$this->urlRouter->cities[$this->urlRouter->cityId][3].'/';
                    $idx=3;
                }*/
                $link.=$this->localities[$this->localityId]['uri'].'/';
                if ($this->urlRouter->sectionId) 
                    $link.=$this->urlRouter->sections[$this->urlRouter->sectionId][3].'/';
                else 
                    $link.=$this->urlRouter->pageRoots[$this->urlRouter->rootId]['uri'].'/';
                if ($this->urlRouter->purposeId)
                    $link.=$this->urlRouter->purposes[$this->urlRouter->purposeId][3].'/';
                if ($this->urlRouter->siteLanguage!='ar')$link.=$this->urlRouter->siteLanguage.'/';
                $link.='c-'.$this->localityId.'-'.$idx.'/%s';
            }
            else $link=$this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->urlRouter->purposeId).'%s';

            $uri_query='';
            $linkAppend='?';
            if ($this->urlRouter->params['q']) {
                $uri_query=$linkAppend.'q='.urlencode($this->urlRouter->params['q']);
                $linkAppend='&';
            }
            
            $qtotal_found = $this->searchResults['body']['total_found'];
            if ($qtotal_found>0) {
                
                $pages = ceil($qtotal_found/$this->num);
                
                $tmp=$this->urlRouter->cfg['search_results_max']/$this->num;
                if ($pages>$tmp) $pages=$tmp;
                if ($pages>1) {
                    //$currentPage=ceil(($this->urlRouter->params['start']+1)/$this->num);
                    
                    $currentPage = ($this->urlRouter->params['start']?$this->urlRouter->params['start']:1);
                    
                    if ($currentPage>1) {
                        $result.='<td class="prev">';
                        $result.='<a target="_self" class="'.$rightCB.'" href="';
                        
                        $page_no= $currentPage-1;
                        if ($page_no>1)
                            $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        else 
                            $result.=sprintf ($link, $uri_query);
                        
                        $result.='">';
                        $result.=$this->lang['previous'];
                        $result.='</a>';
                        $result.='</td>';
                    }
                    $pageMargin=5;
                    //if($this->urlRouter->userId) $pageMargin=4;
                    $startPage=$currentPage-$pageMargin;
                    if ($startPage<=0) $startPage=1;
                    $endPage=$currentPage+$pageMargin;
                    if ($endPage>$pages) $endPage=$pages;
                    while ($startPage<=$endPage) {
                        if ($startPage==$currentPage) {
                            $result.='<td><span'.($startPage==1 ? ' class="'.$rightCB.'"' : ($startPage==$endPage ? ' class="'.$leftCB.'"' :'')).'>'.$startPage.'</span></td>';
                        } else {
                            $page_no=$startPage-1;
                            $result.='<td><a target="_self" href="';
                            
                            if ($page_no)
                                $result.=sprintf ($link, "{$startPage}/{$uri_query}");
                            else 
                                $result.=sprintf($link, $uri_query);
                            
                            $result.='">'.$startPage.'</a></td>';
                        }
                        $startPage++;
                    }
                    if ($currentPage<$pages) {
                        $result.='<td class="next">';
                        $offset=$this->urlRouter->params['start']+$this->num;
                        $result.='<a target="_self" class="'.$leftCB.'" href="';
                        
                        //$result.=$link.$linkAppend.'start='.$offset;
                        $page_no=$currentPage+1;
                        $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        $result.='">';
                        $result.=$this->lang['next'];
                        $result.='</a></td>';
                    }
                    $result.= '</tr></table></div>';
                    $result= '<div class="nav rcb"><table><tr>'.$result;
                    $this->paginationString=$result;
                }

            }
        }
        return $this->paginationString;
    }

    /********************************************************************/
    /*                           abstract functions                     */
    /********************************************************************/

    function header(){
        ?><meta name="google-site-verification" content="v7TrImfR7LFmP6-6qV2eXLsC1qJSZAeKx2_4oFfxwGg" /><?php
        if ($this->userFavorites){
            $this->lang['description']=$this->lang['home_description'].$this->lang['home_description_all'];
        }
        if ($this->lang['description']) {             
            ?><meta name="description" content="<?= preg_replace("/<.*?>/", "", $this->lang['description']) ?>" /><?php 
        } 
        if ($this->urlRouter->cfg['enabled_sharing'] && $this->urlRouter->module!="detail") {
            $sharingUrl=$this->urlRouter->cfg['host'].'/';
            if ($this->urlRouter->userId){
                $sharingUrl.=$this->partnerInfo['uri'].'/';
            }
            if ($this->extendedId || $this->localityId){
                $sharingUrl.=$this->extended_uri ? substr($this->extended_uri, 1, strlen($this->extended_uri)) : '';
            }elseif($this->urlRouter->module=='index' || ($this->urlRouter->module=='search' && !($this->urlRouter->watchId || $this->userFavorites))){
                $sharingUrl.=$this->urlRouter->uri ? substr($this->urlRouter->uri, 1, strlen($this->urlRouter->uri)) : '';
            }
            if ($this->urlRouter->siteLanguage!='ar') {
                $sharingUrl.=$this->urlRouter->siteLanguage.'/';
            }
            if ($this->urlRouter->module=='search'){
            if ($this->urlRouter->params['start']) {
                $sharingUrl.=$this->urlRouter->params['start'].'/';
            }
            if ($this->urlRouter->params['q']) {
                $sharingUrl.='?q='.urlencode($this->urlRouter->params['q']);
            }
            }
            $pageThumb=$this->urlRouter->cfg["url_img"].'/mourjan-icon.png';
            if($this->urlRouter->sectionId && isset($this->urlRouter->sections[$this->urlRouter->sectionId])){
                $pageThumb=$this->urlRouter->cfg["url_img"].'/200/'.$this->urlRouter->sectionId.'.png';
            }elseif ($this->urlRouter->rootId && isset($this->urlRouter->pageRoots[$this->urlRouter->rootId])){
                $pageThumb=$this->urlRouter->cfg["url_img"].'/'.$this->urlRouter->rootId.'.png';
            }
            
            ?><meta property="og:title" content="<?= ($this->urlRouter->watchId || $this->userFavorites) ? $this->lang['title_full']:$this->title ?>" /><meta property="og:description" content="<?= $this->lang['description'] ?>" /><meta property="og:type" content="website" /><?php
            ?><meta property="og:url" content="<?= $sharingUrl ?>" /><?php 
            ?><meta property="og:image" content="<?= $pageThumb ?>" /><?php 
            ?><meta property="og:site_name" content="Mourjan.com" /><?php 
            ?><meta property="fb:app_id" content="184370954908428"/><?php
        }
        ?><meta name="msapplication-config" content="<?= $this->urlRouter->cfg['host'] ?>/browserconfig.xml" /><?php 
        if($this->user->info['id']==0 && in_array($this->urlRouter->module,['home','signin','favorites','account','myads','post','statement','watchlist','signup','password','buy','buyu'])){
            ?><script async="true" defer="true" src='https://www.google.com/recaptcha/api.js<?= $this->urlRouter->siteLanguage=='ar'?'?hl=ar':'' ?>'></script><?php
        }
        
        /*
        if ($this->isMobile) {
            echo "\n<script src='{$this->urlRouter->cfg['url_jquery']}zepto.min.js' async></script>";
        } else {
            echo "\n<script src='{$this->urlRouter->cfg['url_jquery']}jquery.min.js' async></script>";
        }
        
        if ($this->urlRouter->module==='myads' || $this->urlRouter->module==='signin' || $this->urlRouter->module==='home')
        {
            echo "\n<script src='{$this->urlRouter->cfg['url_jquery']}socket.io-1.4.5.js' async></script>\n";
        }
        */
    }

    function footer(){
        $adLang='';
        if ($this->urlRouter->siteLanguage!="ar") $adLang=$this->urlRouter->siteLanguage.'/';
        if($this->urlRouter->module=='about')$this->urlRouter->cfg['enabled_sharing']=true;
        if ((!$this->user->info['id'] ||  ($this->user->info['id'] && $this->user->info['level']!=9)) && $this->urlRouter->module=='search' && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId){
            $this->globalScript.='var upem=1;';
            //$this->getMediaAds();
            if (isset($this->searchResults['media']) && $this->searchResults['media']['total_found']>0) {
                //$count = count($this->mediaResults['matches']);
                if($this->searchResults['media']['total_found']>2){
                    $k=0;
                    $images_widths = array();
                    $j=4;
                    //$ad_keys = array_keys($this->mediaResults["matches"]);
                    $ad_cache = $this->urlRouter->db->getCache()->getMulti($this->searchResults['media']['matches']);
                    $ad_count = count($this->searchResults['media']['matches']);
                    if($j > $ad_count) $j = $ad_count;
                    if (!isset($this->stat['ad-imp']))
                        $this->stat['ad-imp'] = array();
                    ?><h4 class="peh w"><?= $this->lang['we_suggest'] ?></h4><?php
                    ?><ul class="pe pe<?= $j ?> w"><?php
                   /* 
                    
        ?><ins class="adsbygoogle"
     style="display:block;width:728px;height:90px;margin-left:auto;margin-right:auto"
     data-ad-client="ca-pub-2427907534283641"
     data-ad-slot="5039560620"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
        </script><br /><?php */
                    
                    for ($ptr = 0; $ptr < $j; $ptr++) {
                        $id = $this->searchResults['media']['matches'][$ptr];
                        $ad = $this->classifieds->getById($id,false,$ad_cache);
                        if (isset($this->user->info['level'])) {
                            if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                                //if(isset($this->mediaResults["matches"][$id])){
                                    $this->stat['ad-imp'][]=$id;
                                //}
                            }
                        } else {
                            if(isset($this->mediaResults["matches"][$id])){
                                $this->stat['ad-imp'][]=$id;
                            }
                        }
                        if (!empty($ad[Classifieds::ALT_CONTENT])) {
                            if ($this->urlRouter->siteLanguage == "en" && $ad[Classifieds::RTL]) {
                                $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 0;
                            } elseif ($this->urlRouter->siteLanguage == "ar" && $ad[Classifieds::RTL] == 0) {
                                $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 1;
                            }
                        }
			//$ad[Classifieds::CONTENT]=trim(preg_replace('/^"(.*)"$/u','$1',$ad[Classifieds::CONTENT]));

                        $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit'] < $ad[Classifieds::UNIXTIME]);
                        $newSpan = '';
                        if ($isNewToUser) {
                            $newSpan.="<span class='nw'></span>";
                        }
                        $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/'), $ad[Classifieds::ID]).'?ref=mediabox';
                        
                        if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO]){
                            $images_widths[$k]=array(400,300);
                            $pic = $ad[Classifieds::VIDEO][2];
                            $this->globalScript.='sic["e' . $ad[Classifieds::ID] . '"]="<img class=\"ik'.$k.'\" src=\"' . $ad[Classifieds::VIDEO][2] .'&amp;autohide=1\" />";';
                        }else{
                            $pics = $ad[Classifieds::PICTURES];
                            $picsCount=count($ad[Classifieds::PICTURES]);

                            if (isset($ad[Classifieds::PICTURES_DIM])) {
                                $oPics= $ad[Classifieds::PICTURES_DIM];

                            } else {
                                $oPics= array(0=>array(400,300));
                                $picsCount = 1;

                            }
                            $widths=array();

                            for($i=0;$i<$picsCount;$i++){
                                    $oPics[$i][2]=$pics[$i];
                                    $widths[$i]=$oPics[$i][0];
                            }
                            array_multisort($widths, SORT_DESC, $oPics);

                            for($i=0;$i<$picsCount;$i++){
                                if(isset($oPics[$i][0]) && $oPics[$i][1]){
                                    $images_widths[$k]=array($oPics[$i][0],$oPics[$i][1]);
                                    $pic = $oPics[$i][2];
                                    $this->globalScript.='sic["e' . $ad[Classifieds::ID] . '"]="<img class=\"ik'.$k.'\" src=\"' . $this->urlRouter->cfg['url_ad_img'] . '/repos/m/' . $pic . '\" />";';
                                }
                            }
                        }
                        
                        //$caption = $this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', '', array("limit" => 25,'chunk_separator'=>'..'));
                        $caption = $this->BuildExcerpts($ad[Classifieds::CONTENT],25,'..');
                        
                        ?><li id="e<?= $ad[Classifieds::ID] ?>"><a class="<?= $ad[Classifieds::RTL] ? 'ar':'en' ?>" href="<?= $_link ?>"><span class="ik load ik<?= $k ?>"></span><br /><?= $caption ?></a></li><?php 
                        $k++;
                    }
                    ?></ul><?php
                    $i=0;
                    ?><style><?php
                    foreach ($images_widths as $dim){
                        $width = $dim[0];
                        $height = $dim[1];
                        $margin=0;
                        if($width >= $height){
                            if($j==4){
                                if($width>210){
                                    $height = floor(210 * $height / $width);
                                    $width = 210;
                                }
                                if($height < 210){
                                    $margin = ceil((210 - $height)/2);
                                }
                            }else{
                                if($width>290){
                                    $height = floor(290 * $height / $width);
                                    $width = 290;
                                }
                                if($height < 290){
                                    $margin = ceil((290 - $height)/2);
                                }
                            }
                        }else{
                            if($j==4){
                                if($height > 210){
                                    $width = floor(210 * $width / $height);
                                    $height = 210;
                                }
                                if($height < 210){
                                    $margin = ceil((210 - $height)/2);
                                }
                            }else{                                
                                if($height > 290){
                                    $width = floor(290 * $width / $height);
                                    $height = 290;
                                }
                                if($height < 290){
                                    $margin = ceil((290 - $height)/2);
                                }
                            }
                        }
                        ?>.ik<?= $i ?>{width:<?= $width  ?>px;height:<?= $height ?>px;margin:<?= $margin ?>px 0}<?php
                        $i++;
                    }
                    ?></style><?php
                    ?><br /><div class="adLnk"><a href="/post/<?= $this->urlRouter->siteLanguage=='ar'?'':'en/' ?>" class="bt"><?= $this->lang['addAd'] ?></a></div><?php
                }
            }
            
        }else{
            $this->globalScript.='var upem=0;';
        }
        if ( ($this->urlRouter->module=='index' || $this->urlRouter->module=='about' || ($this->urlRouter->module=='search' && $this->searchResults['body']['total_found']) ) && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId && $this->urlRouter->cfg['enabled_sharing']){
            ?><div class="sha sh <?= $this->urlRouter->siteLanguage ?> rc w"><?php
                ?><div class="fr"><?php 
                    ?><label><?= $this->urlRouter->module=='search' ? $this->lang['shareUsSearch']:$this->lang['shareUs'] ?></label><?php 
                     ?><span class='st_facebook_hcount'></span><span class='st_twitter_hcount'></span><span class='st_googleplus_hcount'></span><span class='st_linkedin_hcount'></span><span class='st_email_hcount'></span><span class='st_sharethis_hcount'></span><?php 
                ?></div><?php
                ?><div class="fl"><?php
                    ?><label><?= $this->lang['followUs'] ?></label><a href="https://www.facebook.com/pages/Mourjan/318337638191015" target="_blank"><span class="fb-link"></span></a><a href="https://twitter.com/MourjanWeb" target="blank"><span class="tw-link"></span></a><a href="https://plus.google.com/104043262417362495551" rel="publisher" target="blank"><span class="gp-link"></span></a><?php
                ?></div><?php
            ?></div><?php 
        }elseif($this->urlRouter->module!='signin'){
            ?><br /><?php
        }
        if ($this->urlRouter->userId) {
            $year = date('Y');
            ?><div class="ftr"><div class="cr">© 2010-2016<?= $year ?> Mourjan.com Classifieds Aggregator - All Rights Reserved.<?php        
        }else {
            ?><div class="ftr"><div class="w"><?php
            ?><div class="q0 q1 fl"><?php
            ?><b class="h"><?= $this->lang['mourjan'] ?></b><?php
            if ($this->urlRouter->cfg['enabled_post'] && $this->urlRouter->module!='post') {
                if ($this->user->info['id']){
                    echo '<a class="nt" href="/post/'.$adLang.'">'.$this->lang['button_ad_post'].'</a>';
                }else {
                    echo '<a class="login nt" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['button_ad_post'].'</a>';
                }
            }
            if ($this->urlRouter->module!='about') {
                ?><a href="/about/<?= $adLang ?>"><?= $this->lang['aboutUs'] ?></a><?php
            }else {
                ?><b><?= $this->lang['aboutUs'] ?></b><?php
            }
            if ($this->urlRouter->module!='contact') {
                ?><a href="/contact/<?= $adLang ?>"><?= $this->lang['contactUs'] ?></a><?php
            }else {
                ?><b><?= $this->lang['contactUs'] ?></b><?php
            }
            if ($this->urlRouter->module!='gold') {
                ?><a href="/gold/<?= $adLang ?>"><?= $this->lang['gold_title'] ?></a><?php
            }else {
                ?><b><?= $this->lang['gold_title'] ?></b><?php
            }
            if ($this->urlRouter->module!='privacy') {
                ?><a href="/privacy/<?= $adLang ?>"><?= $this->lang['privacyPolicy'] ?></a><?php
            }else {
                ?><b><?= $this->lang['privacyPolicy'] ?></b><?php
            }
            if ($this->urlRouter->module!='terms') {
                ?><a itemprop="publishingPrinciples" href="/terms/<?= $adLang ?>"><?= $this->lang['termsConditions'] ?></a><?php
            }else {
                ?><b><?= $this->lang['termsConditions'] ?></b><?php
            }
            
            ?><form action="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>" method="post"><input type="hidden" name="mobile" value="1" /><a href='#' onclick="this.parentNode.submit();"><?= $this->lang['mobile'] ?></a></form><?php
            /*
            if ($this->urlRouter->module!='advertise') {
                ?><a href="/advertise/<?= $adLang ?>"><?= $this->lang['advertiseUs'] ?></a><?php
            }else {
                ?><b><?= $this->lang['advertiseUs'] ?></b><?php
            }
             * 
             */
            /*
            if ($this->urlRouter->module!='publication-prices') {
                ?><a href="/publication-prices/<?= $adLang ?>"><?= $this->lang['pricelist'] ?></a><?php
            }else {
                ?><b><?= $this->lang['pricelist'] ?></b><?php
            }
             * 
             */
            ?></div><?php 
            
            ?><div class="q1"><?php
            ?><b class="h"><?= $this->lang['pclassifieds_'.$this->urlRouter->siteLanguage] ?></b><?php
            $cityId=$this->user->params['city'];
            $countryId=$this->user->params['country'];
            $currentRoot='';
            foreach ($this->urlRouter->pageRoots as $rid=>$root) {
                if($rid==$this->urlRouter->rootId)$currentRoot=($this->urlRouter->siteLanguage=='ar' ? 'أقسام ال'.$root['name'] : $root['name'].'\''.($rid==1 ? 's':'').' Sections');
                //$purposeId=is_numeric($root[3]) ? (int) $root[3]: ($root[0]==3 ? 3: 0);
                $purposeId=0;
                ?><a href="<?= $this->urlRouter->getURL($countryId,$cityId,$rid,0,$purposeId) ?>"><span class='i i<?= $rid ?>'></span><?= ($this->urlRouter->siteLanguage=='ar' ? 'إعلانات ال'.$root['name'] : $root['name']) ?></a><?php
            }
            ?></div><?php
            
            if($this->urlRouter->module=='search' || $this->urlRouter->module=='detail'){
                ?><div class="qw"><?php
                    ?><div><?php
                    ?><h4><?= $this->lang['safety_tip'] ?></h4><?php 
                    ?><ul><?php
                        ?><li><?php
                        echo $this->lang['safety_tip_1'] 
                        ?></li><?php
                        ?><li><?php
                        echo $this->lang['safety_tip_2'] 
                        ?></li><?php
                        ?><li><?php
                        echo $this->lang['safety_tip_3'] 
                        ?></li><?php
                        ?><li><?php
                        echo $this->lang['safety_tip_4'] 
                        ?></li><?php
                    ?></ul><?php
                    ?></div><?php
                ?></div><?php
            /*
            $sectionCount=count($this->urlRouter->pageSections);
                
            if( ($this->urlRouter->module=='search' || $this->urlRouter->module=='detail' ) && $this->urlRouter->rootId && !$this->userFavorites && !$this->urlRouter->watchId && $sectionCount ){
                $divide=3;
                if($this->urlRouter->siteLanguage=='en' && $this->urlRouter->rootId!=2)$divide=2;
                $perColumn=ceil($sectionCount/$divide);
                $idx=0;
                ?><div class="q2<?= $divide==3 ? '':' q3' ?>"><?php
                ?><b class="h"><?= $currentRoot ?></b><?php 
                $cssPre='z z';
                switch($this->urlRouter->rootId){
                    case 1:
                        $cssPre='x x';
                        break;
                    case 2:
                        $cssPre='z z';
                        break;
                    case 3:
                        $cssPre='v v';
                        break;
                    case 4:
                        $cssPre='y y';
                        break;
                    case 99:
                        $cssPre='u u';
                        break;
                }
                foreach($this->urlRouter->pageSections as $section){
                    if($idx && $idx==$perColumn){
                        $idx=0;
                        ?></div><div class="q2 qp<?= $divide==3 ? '':' q3' ?>"><?php
                    }
                    $idx++;
                    $purposeId=is_numeric($section[3]) ? (int) $section[3]: 0;
                    ?><a href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$section[0],$purposeId) ?>"><span class="<?= $cssPre.$section[0] ?>"></span><?= $this->urlRouter->sections[$section[0]][$this->fieldNameIndex] ?></a><?php
                }
                ?></div><?php */
            }else {
            
            
                ?><div class="q2"><?php
                ?><b class="h"><?= $this->lang['countries'] ?></b><?php                
                if($this->urlRouter->siteLanguage=='en'){                
                    $index_1=6;
                    $index_2=13;
                } else {
                    $index_1=5;
                    $index_2=9;
                }
                //$tmp='';
                //$countryId=0;
                $countryIDX=0;
                foreach ($this->urlRouter->countries as $country_id => $country) {
                    $countryIDX++;
                    if($countryIDX==$index_1 || $countryIDX==$index_2){
                        ?></div><div class="q2 qp"><?php
                    }
                    ?><a href="<?= $this->urlRouter->getURL($country_id, 0) ?>"><span class="cf c<?= $country_id ?>"></span><?= $country['name'] ?></a><?php
                    foreach ($country['cities'] as $city_id=>$city) {
                        echo '<a class="ct" href="'. $this->urlRouter->getURL($country_id, $city_id) .'">'.$city['name'].'</a>';                            
                    }
                    
                }
                /*
                $countryId=0;
                $tmp='';
                $countryIDX=0;
                $cities=$this->urlRouter->db->queryCacheResultSimpleArray(
                    "ftr_cities_{$this->urlRouter->siteLanguage}",
                    "select c.ID,c.country_id from city c 
                        left join country d on c.country_id=d.id 
                        where d.blocked=0 and c.blocked=0 
                        order by d.name_{$this->urlRouter->siteLanguage}, c.name_{$this->urlRouter->siteLanguage}",
                    null, 0, $this->urlRouter->cfg['ttl_long']);
                $cityPerCountry=0;
                $index_1=5;
                $index_2=9;
                if($this->urlRouter->siteLanguage=='en'){                
                    $index_1=6;
                    $index_2=13;
                }
                foreach ($cities as $city) {
                    if($countryId==$city[1]){
                        $cityPerCountry++;
                        $tmp.='<a class="ct" href="'.$this->urlRouter->getSilentURL($countryId,$city[0]).'">'.$this->urlRouter->cities[$city[0]][$this->fieldNameIndex].'</a>';
                    }else{
                        $countryIDX++;

                        $countryId=$city[1];
                        if($cityPerCountry>1){
                            echo $tmp;
                        }                    
                        if($countryIDX==$index_1 || $countryIDX==$index_2){
                            ?></div><div class="q2 qp"><?php
                        }
                        $tmp='<a class="ct" href="'.$this->urlRouter->getSilentURL($countryId,$city[0]).'">'.$this->urlRouter->cities[$city[0]][$this->fieldNameIndex].'</a>';
                        $cityPerCountry=1;
                        ?><a href="<?= $this->urlRouter->getSilentURL($countryId,0) ?>"><span class="cf c<?= $countryId ?>"></span><?= $this->urlRouter->countries[$city[1]]['name'] ?></a><?php

                    }
                }
                if($cityPerCountry>1){
                    echo $tmp;
                }*/
                ?></div><?php
                }

                /* ?><div class="fr"><div id="google_translate_element"></div></div><?php */
                $year = date('Y');
                ?><div class="cr">© 2010-2016<?= $year ?> Mourjan.com Classifieds Aggregator - All Rights Reserved.</div><?php                    
            //}
            
        }
        ?></div></div><?php 
        /* ?><div class="fb-recommendations-bar" data-href="http://www.mourjan.com<?= $this->urlRouter->uri ?>" data-action="recommend" data-site="mourjan.com"></div><?php */        
    }
    
    function _leading_pane(){
        ?><div class='col4'><?php $this->leading_pane() ?></div><?php
    }
    
    function leading_pane(){
    }
    
    function _main_pane(){
        ?><div class='col1'><?php
            $this->main_pane();
//            if ( ($this->urlRouter->module=='detail' || $this->urlRouter->params['start']<2) && $this->searchResults!==false && !($this->urlRouter->watchId && !$this->searchResults['total_found']) )
//                echo $this->fill_ad('zone_4', 'ad_w');
        ?></div><?php 
    }
    function main_pane(){
    }
    function _side_pane(){
        echo '<!--googleoff: snippet-->';
        ?><div class='col3'><?php $this->side_pane() ?></div><?php
        echo '<!--googleon: snippet-->';
    }
    function side_pane(){
    }
    
    function side_app_banner(){
        ?><div class="aps"><?php
        ?><h3><?= ($this->urlRouter->siteLanguage == 'en' ? 'Download <span class="og">mourjan</span> App':'تحميل تطبيق <span class="og">مرجان</span>' ) ?></h3><?php
        ?><a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="ios"></span></a><?php
        ?><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="android"></span></a><?php
        ?></div><?php
    }
    
    function menu_app_banner(){
        ?><div class="mps"><?php
        ?><a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="mios"></span></a><?php
        ?><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="mandroid"></span></a><?php
        ?></div><?php
    }

    function body() {
        $colSpread='col2w';
        if(!$this->hasLeadingPane){
            $colSpread='colw';
        }
        ?><div class="<?= $colSpread ?>"><?php   
        if(($this->urlRouter->module == 'buy'||$this->urlRouter->module=='buyu') && $this->user->info['id']){
            $this->renderBalanceBar();
        }  
        if ($this->urlRouter->userId) 
            $this->partnerHeader(); 
        /*if ($this->urlRouter->module=='index') {
            echo $this->fill_ad("zone_2", "adc");
        }*/
        /*elseif ($this->urlRouter->module=='detail' && !$this->detailAdExpired){
            echo $this->fill_ad("zone_8",'ad_det');
            echo $this->fill_ad("zone_9",'ad_det adx');
        }*/
        $this->_main_pane();
        if ($this->hasLeadingPane) {
            $this->_side_pane();
        }
        /*
        if (!$this->urlRouter->userId || $this->hasPartnerInfo)
            $this->_side_pane();
        //if ($this->urlRouter->module=='index') 
        //    echo '<div class="btm na rc">', $this->lang['notify_new_ads'], '</div>';
        
        if ($this->urlRouter->module=='search' && !$this->urlRouter->userId && !$this->urlRouter->userId && !$this->userFavorites){
        if (($this->urlRouter->countryId && $this->urlRouter->sectionId  && $this->urlRouter->purposeId) || ($this->urlRouter->params['q'] && $this->searchResults['total_found']<100)){
            if ($this->user->info['id']) {
                $key=$this->urlRouter->countryId.'-'.$this->urlRouter->cityId.'-'.$this->urlRouter->sectionId.'-'.$this->extendedId.'-'.$this->localityId.'-'.$this->urlRouter->purposeId.'-'.crc32($this->urlRouter->params['q']);
                if (!isset($this->user->info['options']['watch'][$key])){
                    ?><div class="btm na rc dsom"><?php
                    ?><div onclick="ti(true)" class="eck"><?= $this->lang['watchLink'].'<b>'.$this->title.'</b>' ?></div><?php
                    ?><b class="sfl"><?= $this->lang['w_slogan'] ?></b><?php
                    ?><div class="som"></div><?php
                    //echo $this->lang['w_content'];
                    echo '<p>', $this->lang['w2_slogan'],'</p>';
                    ?></div><?php
                }
            }else {
                ?><div class="btm na rc dsom"><?php
                ?><div onclick="ti()" class="eck"><?= $this->lang['watchLink'].'<b>'.$this->title.'</b>' ?></div><?php
                ?><b class="sfl"><?= $this->lang['w_slogan'] ?></b><?php
                ?><div class="som"></div><?php
                //echo $this->lang['w_content'];
                echo '<p>', $this->lang['w2_slogan'],'</p>';
                ?></div><?php
            }
        }
        }*/
        ?></div><?php
        //$this->renderNotifications();

        $this->footer();
    }

    function bodyMobile(){
        echo "<div id='main' class='main'>";
        $this->mainMobile();
        /*if(in_array($this->urlRouter->module,array('index','search','detail','contact'))){
            if (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1){
                ?><a href="https://itunes.apple.com/us/app/mourjan-mrjan/id876330682?ls=1&mt=8"><div class="bottom-banner"><div onclick="closeBanner(event,this,'mobile_ios_app_bottom_banner',1)" class="banner-close"></div><div class="ios-banner"></div></div></a><?php
            }
            if (isset($this->user->params['mobile_android_app_bottom_banner']) && $this->user->params['mobile_android_app_bottom_banner']==1){
                ?><a href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><div class="bottom-banner"><div onclick="closeBanner(event,this,'mobile_android_app_bottom_banner',1)" class="banner-close"></div><div class="android-banner"></div></div></a><?php
            }
        }*/
        echo '</div>';
    }

    function mainMobile(){

    }

    /********************************************************************/
    /*                           rendering components                   */
    /********************************************************************/

    
    protected function set_analytics_header(){
       /* ?><script type="text/javascript"><?php
        ?>
        var canImp=0;
        var head = document.getElementsByTagName("head")[0] || document.documentElement;
        if('import' in document.createElement('link')){
            canImp=1;
            var l = document.createElement('link');        
            l.rel = 'import';
            l.href = 'https://h5.mourjan.com/imp/1.0.0/index_ar.html';
            l.setAttribute('async', 'true');
            l.onload = function(e) {
                console.log('loaded');
            }
            head.appendChild(l);
        }
                <?php
        ?></script><?php */
        //error_log($_SERVER['HTTP_USER_AGENT']);
        if (isset($this->user->info['level']) && $this->user->info['level']==9){
            return;
        }
        $banAd = 0;
        if (preg_match('/Firefox\/27\.0/ui', $_SERVER['HTTP_USER_AGENT'])) {
            //error_log("Hala " . $_SERVER['HTTP_USER_AGENT']);
            $banAd = 1;
        }
        ?><script type='text/javascript'><?php
        if ($banAd==0 && $this->urlRouter->cfg['enabled_ads'] && count($this->googleAds)) {
                ?>var googletag = googletag||{};googletag.cmd=googletag.cmd||[];(function(){var gads=document.createElement('script');gads.async=true;gads.type='text/javascript';var useSSL='https:'==document.location.protocol;gads.src=(useSSL?'https:':'http:')+'//www.googletagservices.com/tag/js/gpt.js';var node=document.getElementsByTagName('script')[0];node.parentNode.insertBefore(gads, node);})();googletag.cmd.push(function(){<?php
            
            $slot=0;
            foreach ($this->googleAds as $ad) {
                $slot++;
                echo "var slot{$slot}=googletag.defineSlot('{$ad[0]}',[{$ad[1]},{$ad[2]}],'{$ad[3]}').addService(googletag.pubads());";
            }
            echo "googletag.pubads().collapseEmptyDivs();";
            if ($this->urlRouter->countryId)
                echo "googletag.pubads().setTargeting('country_id', '{$this->urlRouter->countryId}');";
            if ($this->urlRouter->rootId)
                echo "googletag.pubads().setTargeting('root_id', '{$this->urlRouter->rootId}');";
            if ($this->urlRouter->sectionId)
                echo "googletag.pubads().setTargeting('section_id', '{$this->urlRouter->sectionId}');";
            if ($this->urlRouter->purposeId)
                echo "googletag.pubads().setTargeting('purpose_id', '{$this->urlRouter->purposeId}');";
            else
                echo "googletag.pubads().setTargeting('purpose_id', '999');";
            ?>googletag.pubads().enableSingleRequest();googletag.enableServices()});<?php
        }
        
        $module = $this->urlRouter->module;
        if($module=='search'){
            if  ($this->urlRouter->userId){
                $module = 'user_page_'.$this->urlRouter->userId;
            }elseif($this->userFavorites){
                $module = 'favorites';
            }elseif($this->urlRouter->watchId){
                $module = 'watchlist';
            }elseif($this->urlRouter->isPriceList){
                $module = 'pricelist';
            }
        }
        
        ?>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){<?php
        ?>(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),<?php
        ?>m=s.getElementsByTagName(o)[0];a.async=true;a.src=g;m.parentNode.insertBefore(a,m)<?php
        ?>})(window,document,'script','//www.google-analytics.com/analytics.js','ga');<?php

        ?>ga('create','UA-435731-13','mourjan.com');<?php
        ?>ga('set','dimension1',"<?php echo $module ?>");<?php
        ?>ga('set','dimension2',"<?php echo $this->urlRouter->rootId?$this->urlRouter->roots[$this->urlRouter->rootId][2]:'AnyRoot';?>");<?php
        ?>ga('set','dimension3',"<?php echo ($this->urlRouter->sectionId && isset($this->urlRouter->sections[$this->urlRouter->sectionId]))?$this->urlRouter->sections[$this->urlRouter->sectionId][2]:'AnySection'; ?>");<?php
        ?>ga('set','dimension4',"<?php echo ($this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId]))?$this->urlRouter->countries[$this->urlRouter->countryId]['uri']:'Global';?>");<?php
        ?>ga('set','dimension5',"<?php echo ($this->urlRouter->cityId && isset($this->urlRouter->cities[$this->urlRouter->cityId]))?$this->urlRouter->cities[$this->urlRouter->cityId][3]:(($this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId]))?$this->urlRouter->countries[$this->urlRouter->countryId]['uri'].' all cities':'Global');?>");<?php
        if(isset($this->user->pending['email_watchlist'])){
            ?>ga('set','dimension6','watchlist');<?php
        }
        ?>ga('send','pageview');<?php 
        
        /*
        $module = $this->urlRouter->module;
            if($module=='search'){
                if  ($this->urlRouter->userId){
                    $module = 'user_page_'.$this->urlRouter->userId;
                }elseif($this->userFavorites){
                    $module = 'favorites';
                }elseif($this->urlRouter->watchId){
                    $module = 'watchlist';
                }elseif($this->urlRouter->isPriceList){
                    $module = 'pricelist';
                }
            }
            ?>var _gaq=_gaq||[];_gaq.push(['_setAccount','UA-435731-13']);
         * _gaq.push(['_setDomainName','mourjan.com']);
         * _gaq.push(['_setCustomVar', 1, 'Module', <? echo "'{$module}'";?>, 3]);
         * _gaq.push(['_setCustomVar', 2,'Root', '<? echo $this->urlRouter->rootId?$this->urlRouter->roots[$this->urlRouter->rootId][2]:'AnyRoot';?>', 3]);
         * _gaq.push(['_setCustomVar', 3,'Section', '<? echo ($this->urlRouter->sectionId && isset($this->urlRouter->sections[$this->urlRouter->sectionId]))?$this->urlRouter->sections[$this->urlRouter->sectionId][2]:'AnySection'; ?>', 3]);
         * _gaq.push(['_setCustomVar', 4, 'Country', '<? echo $this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId])?$this->urlRouter->countries[$this->urlRouter->countryId][3]:'Global';?>', 3]);<?php 
            if(isset($this->user->pending['email_watchlist'])){
                ?>_gaq.push(['_setCustomVar', 5, 'Campaign', 'watchlist', 3]);<?php
            }
            ?>_gaq.push(['_trackPageview']);<?php 
            ?>(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();<?php 
        */
        
        
          ?></script><?php
    }

    function renderMobileLinks(){
        if (!$this->urlRouter->countryId || $this->urlRouter->rootId) return;
        $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
        if ($this->urlRouter->rootId) return;
        ?><ul class="ls br"><?php
         ?><li><a href="/about/<?= $lang ?>"><span class="vpdi <?= $this->urlRouter->siteLanguage ?>"></span><?= $this->lang['aboutUs'] ?><span class="to"></span></a></li><? 
         ?><li><a href="/contact/<?= $lang ?>"><span class="ic r100"></span><?= $this->lang['contactUs'] ?><span class="to"></span></a></li><? 
        ?></ul><?php
        ?><ul class="ls br"><?php

        //var_dump($this->urlRouter->isApp);

        if (!$this->urlRouter->isApp) {
            ?><li onclick="this.childNodes[0].submit()"><form action="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>" method="post"><input type="hidden" name="mobile" value="0" /><span class="ilnk"><span class="ic r101"></span><?= $this->lang['full_site'] ?></span><span class="to"></span></form></li><?php

        }
        ?></ul><?php  
        ?><ul class="ls br"><?php 
         ?><li><a itemprop="publishingPrinciples" href="/terms/<?= $lang ?>"><?= $this->lang['termsConditions'] ?><span class="to"></span></a></li><?php
         ?><li><a href="/privacy/<?= $lang ?>"><?= $this->lang['privacyPolicy'] ?><span class="to"></span></a></li><?php
        ?></ul><?php 
        
    }

    
    function loadMobileJs_classic()
    {
        if ($this->globalScript) 
        {
            $this->globalScript=preg_replace('/\s+/', ' ', $this->globalScript);
            $this->globalScript=preg_replace("/[\n\t\r]/", '', $this->globalScript);
            $this->globalScript.=';';
            $this->globalScript=preg_replace("/;;/", ';', $this->globalScript);
        }
        
        if ($this->inlineScript)
        {
            $this->inlineScript=preg_replace('/\s+/', ' ', $this->inlineScript);
            $this->inlineScript=preg_replace("/[\n\t\r]/", '', $this->inlineScript);
            $this->inlineScript.=';';
            $this->inlineScript=preg_replace("/;;/", ';', $this->inlineScript);
        }
        
        if ($this->urlRouter->module=='index' && !$this->urlRouter->rootId && $this->urlRouter->countryId) 
        { 
            ?><div id="fb-root"></div><?php
        }
        
        ?><script type="text/javascript"><?php
        //has Query Parameter
        ?>var head = document.getElementsByTagName("head")[0] || document.documentElement;<?php
        /* ?>function loadCss(fn,cb){var s=document.getElementsByTagName("link"),l=s.length-1,p=0,e;for(i=l;i>=0;i--){if(s[i].rel=='stylesheet'){e=s[i];break;}}if(typeof e==='undefined'){p=1;e=head.firstChild}var l=document.createElement('link');l.rel='stylesheet';l.type="text/css";l.media='all';l.href=fn;e.parentNode.insertBefore(l,e.nextSibling)}<?php */
        ?>function addEvent(e, en, fn){if (e.addEventListener)e.addEventListener(en, fn, false);else if(e.attachEvent)e.attachEvent('on' + en, fn)}<?php
        if($this->urlRouter->isApp)
        {
            ?>addEvent(document,'DOMContentLoaded',function(){parent.postMessage('DOMContentLoaded','*')});<?php
            //if(typeof window.onbeforeunload !== 'undefined'){
                /*window.onbeforeunload=function(){
                    parent.postMessage('DOMContentBeforeUnload','*');
                    return null;
                };*/
                /*window.onunload=function(){
                    parent.postMessage('DOMContentUnload','*');
                    return null;
                };*/
                ?>window.onpagehide=function(){<?php
                    ?>parent.postMessage('pageHide','*');<?php
                    ?>return null;<?php
                ?>};<?php
            /*} else if(typeof window.onpagehide !== 'undefined'){
                addEvent(window,'pagehide',function(){
                    parent.postMessage('DOMContentUnloaded','*');
                    return null;
                });
            } else {
                addEvent(window,'unload',function(){
                    parent.postMessage('DOMContentUnloaded','*');
                    return null;
                });
            }addEvent(window,'popstate',function(){
                parent.postMessage('popstate','*');
            });<?php
             ?>window.onbeforeunload=function(){<?php
                ?>parent.postMessage('DOMContentUnloading','*');<?php
                ?>return null;<?php
            ?>};<?php 
            ?>window.onunload=function(){<?php
                ?>parent.postMessage('DOMContentUnloaded','*');<?php
                ?>return null;<?php
            ?>};<?php */
        }
        
        
        
        ?>var SCLD,lang='<?= $this->urlRouter->siteLanguage ?>',<?php
        ?>hasQ=<?= $this->urlRouter->params['q'] ? 1:0 ?>,canSh=<?= $this->urlRouter->cfg['enabled_sharing']?1:0 ?>,<?php
        ?>sic=[],<?php
        ?>isApp=<?= $this->urlRouter->isApp ? "'".$this->urlRouter->isApp."'":0 ?>,<?php
        ?>uid=<?= $this->user->info['id'] ?>,<?php
        ?>mod='<?= $this->urlRouter->module ?>',<?php
        ?>jsLog=<?= $this->urlRouter->cfg['enabled_js_log'] ?>,<?php 
        ?>uimg='<?= $this->urlRouter->cfg['url_ad_img'] ?>',<?php 
        if(isset($this->user->params['hasCanvas'])){            
            ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
        }else{
            ?>tmp=document.createElement('canvas'),<?php
            ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
        }
        if($this->user->info['id']){
            ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
        }
        if($this->user->info['id'] && $this->urlRouter->module=='post'){
            ?>UP_URL='<?= $this->urlRouter->cfg['url_uploader'] ?>',<?php 
            ?>USID='<?= session_id() ?>',<?php 
            ?>uixf='<?= $this->urlRouter->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
        }
        if ($this->stat){
            $this->stat['page']=($this->urlRouter->params['start']) ? $this->urlRouter->params['start'] : 1;
            $this->stat['num']=$this->num;
            ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
            $page=array(
                'cn'=>$this->urlRouter->countryId,
                'c'=>$this->urlRouter->cityId,
                'se'=>$this->urlRouter->sectionId,
                'pu'=>$this->urlRouter->purposeId,
            );
            ?>page='<?= json_encode($page) ?>',<?php
        }else {
            ?>stat=0,<?php
        }
        if($this->user->info['id'] && $this->urlRouter->module=='myads'){
            if ($this->urlRouter->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                ?>uhc='<?= $this->urlRouter->cfg['url_highcharts'] ?>',<?php 
                if($this->user->info['level']==9){
                    ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                }
            }else{
                ?>uhc=0,<?php 
            }
            if ($this->urlRouter->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                ?>ustats=1,<?php
            }else{
                ?>ustats=0,<?php
            }
            if (isset($_GET['sub']) && $_GET['sub']=='pending') {
                ?>PEND=1,<?php
            }else{
                ?>PEND=0,<?php
            }
        }
        if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) {
            $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
            if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                    && ( ($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) 
                    || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) {
                ?>_cn=<?= $this->urlRouter->countryId ?>,<?php
                ?>_c=<?= $this->urlRouter->cityId ?>,<?php
                ?>_se=<?= $this->urlRouter->sectionId ?>,<?php
                ?>_pu=<?= $this->urlRouter->purposeId ?>,<?php
                ?>_ext=<?= $this->extendedId ?>,<?php
                ?>_loc=<?= $this->localityId ?>,<?php
                ?>_ttl='<span class="<?= $this->urlRouter->siteLanguage ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                ?>_q='<?= $this->urlRouter->params['q'] ? addcslashes($this->urlRouter->params['q'], "'") :'' ?>',<?php
            }
        }
        ?>hrs=<?= isset($this->searchResults['body']['total_found']) && $this->searchResults['body']['total_found']>0 ? 1:0 ?>,<?php
        ?>hd='<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->urlRouter->siteLanguage=='en'?'e':'a') ?>',<?php 
        ?>ucss='<?= $this->urlRouter->cfg['url_css_mobile'] ?>',<?php
        
        if ($this->urlRouter->module=='search' || $this->urlRouter->module=='detail' || $this->urlRouter->module=='myads'){
        /* ?>xCancel='<?= $this->lang['cancel'] ?>',<?php */
        ?>xAOK='<?= $this->lang['abuseReported'] ?>',<?php
        ?>xF='<?= $this->lang['sys_error'] ?>',<?php
        ?>since='<?= $this->lang['since'] ?>',<?php
        ?>ago='<?= $this->lang['ago'] ?>',<?php
        }
        if ($this->urlRouter->module=='account'){
            ?>xSaving='<?= $this->lang['savingProgress'] ?>',<?php
        }
        ?>ro=<?= $this->urlRouter->rootId ?>,<?php
        ?>cn=<?= $this->urlRouter->countryId ?>,<?php
        ?>c=<?= $this->urlRouter->cityId ?>,<?php
        ?>se=<?= $this->urlRouter->sectionId ?>,<?php
        ?>pu=<?= $this->urlRouter->purposeId ?>;<?php
        /*if (isset($this->requires['css'])) {
            foreach ($this->requires['css'] as $css) {
                ?>loadCss(ucss+'<?= $css ?>');<?php  
            }            
        } */
        /*?>loadCss(ucss+"/mms.css");<?php  */
        /*?>addEvent(window,'load',function(){loadCss(ucss+"/mms.css")});<?php */
        /* ?>loadCss(ucss+"/mms.css");<?php  */
        echo $this->globalScript;        
        echo $this->inlineScript;       
        ?>function inlineQS(){<?= $this->inlineQueryScript; ?>}<?php
        
        
        
        
        /*
        switch($this->urlRouter->module){
            case 'myads':
                if($this->user->info['id']) {
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_ads.js'<?php
                    ?>]);<?php
                }else{
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js'<?php
                    ?>]);<?php
                }
                break;
            case 'post':
                if($this->user->info['id']) {
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_post.js'<?php
                    ?>]);<?php
                }else{
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js'<?php
                    ?>]);<?php
                }
                break;
            case 'detail':
            case 'search':                
                ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                    ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                    ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_srh.js'<?php
                ?>]);<?php
                break;
            case 'account':
                if(!$this->user->info['id']) {
                    
                    ?>(function () {<?php
                        ?>var s=document.createElement('script');<?php
                        ?>s.type='text/javascript';<?php
                        ?>s.async=true;<?php
                        ?>s.defer=true;<?php
                        ?>s.src='<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js';<?php
                        ?>var x=document.getElementsByTagName('script')[0];<?php
                        ?>x.parentNode.insertBefore(s,x);<?php
                    ?>})();<?php 
                }
                break;
            case 'contact':
            case 'password':
                break;
            case 'index':
            default:
                ?>(function () {<?php
                    ?>var s=document.createElement('script');<?php
                    ?>s.type='text/javascript';<?php
                    ?>s.async=true;<?php
                    ?>s.defer=true;<?php
                    ?>s.src='<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js';<?php
                    ?>var x=document.getElementsByTagName('script')[0];<?php
                    ?>x.parentNode.insertBefore(s,x);<?php
                ?>})();<?php 
                break;
        }*/
        ?></script><?php
        $renderMobileVerifyPage = ($this->urlRouter->module=='post' && $this->user->info['id'] && !$this->isUserMobileVerified);
        if(!$renderMobileVerifyPage){
            ?><script type="text/javascript" onload="inlineQS()" defer="true" src="<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>zepto.min.js"></script><?php
        }
        switch($this->urlRouter->module){
            case 'myads':
                if($this->user->info['id']) {
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_ads.js"></script><?php
                }
                break;
            case 'post':
                if($this->user->info['id'] && $this->isUserMobileVerified) {
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_post.js"></script><?php
                }elseif($renderMobileVerifyPage){
                    ?><script defer="true" type="text/javascript" onload="inlineQS()" src="<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>jquery.mob.min.js"></script><?php
                    ?><script defer="true" type="text/javascript" onload="$('#code').select2({language:'<?= $this->urlRouter->siteLanguage ?>',dir:'rtl'})" src="<?= $this->urlRouter->cfg['url_jquery'] ?>select2.min.js"></script><?php
                }
                break;
            case 'detail':
            case 'search':
                ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_srh.js"></script><?php
                break;
            case 'account':
                if($this->user->info['id']) {
                ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_acc.js"></script><?php
                }
                break;
            case 'contact':
                ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_cnt.js"></script><?php
                break;
            case 'password':
                ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_pwd.js"></script><?php
                break;

            case 'index':
            default:
                break;
        }
        
         
        if($this->urlRouter->module == 'index'){
            $country = '';
            if ($this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId])) {
                $country = $this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
            }
            ?><script type="application/ld+json"><?php
                ?>{"@context": "http://schema.org",<?php
                ?>"@type": "WebSite",<?php
                ?>"url": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>",<?php
                ?>"potentialAction":{<?php
                ?>"@type": "SearchAction",<?php
                ?>"target": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>?q={search_term_string}",<?php
                ?>"query-input": "required name=search_term_string"<?php
                ?>}<?php
                ?>}<?php
            ?></script><?php
        }
    }


    function loadMobileJs()
    {
        if ($this->globalScript) 
        {
            $this->globalScript=preg_replace('/\s+/', ' ', $this->globalScript);
            $this->globalScript=preg_replace("/[\n\t\r]/", '', $this->globalScript);
            $this->globalScript.=';';
            $this->globalScript=preg_replace("/;;/", ';', $this->globalScript);
        }
        
        if ($this->inlineScript)
        {
            $this->inlineScript=preg_replace('/\s+/', ' ', $this->inlineScript);
            $this->inlineScript=preg_replace("/[\n\t\r]/", '', $this->inlineScript);
            $this->inlineScript.=';';
            $this->inlineScript=preg_replace("/;;/", ';', $this->inlineScript);
        }
        
        if ($this->urlRouter->module=='index' && !$this->urlRouter->rootId && $this->urlRouter->countryId) 
        { 
            ?><div id="fb-root"></div><?php
        }
        
        ?><script type="text/javascript"><?php
        //has Query Parameter
        ?>var head=document.getElementsByTagName("head")[0]||document.documentElement;<?php
        
        if($this->urlRouter->isApp)
        {
            ?>function addEvent(d,e,c){if(d.addEventListener){d.addEventListener(e,c,false);}else if(d.attachEvent){d.attachEvent(e,c);}};<?php
            ?>addEvent(document,'DOMContentLoaded',function(){parent.postMessage('DOMContentLoaded','*');});
            window.onpagehide=function(){parent.postMessage('pageHide','*');return null;};<?php
        }
                
        
        ?>var SCLD,lang='<?= $this->urlRouter->siteLanguage ?>',<?php
        ?>hasQ=<?= $this->urlRouter->params['q'] ? 1:0 ?>,canSh=<?= $this->urlRouter->cfg['enabled_sharing']?1:0 ?>,<?php
        ?>sic=[],<?php
        ?>isApp=<?= $this->urlRouter->isApp ? "'".$this->urlRouter->isApp."'":0 ?>,<?php
        ?>uid=<?= $this->user->info['id'] ?>,<?php
        ?>mod='<?= $this->urlRouter->module ?>',<?php
        ?>jsLog=<?= $this->urlRouter->cfg['enabled_js_log'] ?>,<?php 
        ?>uimg='<?= $this->urlRouter->cfg['url_ad_img'] ?>',<?php 
        
        if(isset($this->user->params['hasCanvas']))
        {
            ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
        }else{
            ?>tmp=document.createElement('canvas'),<?php
            ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
        }
        
        if($this->user->info['id'])
        {
            ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
        }
        
        if($this->user->info['id'] && $this->urlRouter->module=='post')
        {
            ?>UP_URL='<?= $this->urlRouter->cfg['url_uploader'] ?>',<?php 
            ?>USID='<?= session_id() ?>',<?php 
            ?>uixf='<?= $this->urlRouter->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
        }
        
        if ($this->stat)
        {
            $this->stat['page']=($this->urlRouter->params['start']) ? $this->urlRouter->params['start'] : 1;
            $this->stat['num']=$this->num;
            ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
            $page=array(
                'cn'=>$this->urlRouter->countryId,
                'c'=>$this->urlRouter->cityId,
                'se'=>$this->urlRouter->sectionId,
                'pu'=>$this->urlRouter->purposeId,
            );
            ?>page='<?= json_encode($page) ?>',<?php
        } else {
            ?>stat=0,<?php
        }
        
        if($this->user->info['id'] && $this->urlRouter->module=='myads')
        {
            if ($this->urlRouter->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
            {
                ?>uhc='<?= $this->urlRouter->cfg['url_highcharts'] ?>',<?php 
                if($this->user->info['level']==9){
                    ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                }
            }
            else
            {
                ?>uhc=0,<?php 
            }
            
            if ($this->urlRouter->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                ?>ustats=1,<?php
            }else{
                ?>ustats=0,<?php
            }
            
            if (isset($_GET['sub']) && $_GET['sub']=='pending') {
                ?>PEND=1,<?php
            }else{
                ?>PEND=0,<?php
            }
        }
        
        if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) 
        {
            $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
            if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                    && ( ($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) 
                    || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) 
            {
                ?>_cn=<?= $this->urlRouter->countryId ?>,<?php
                ?>_c=<?= $this->urlRouter->cityId ?>,<?php
                ?>_se=<?= $this->urlRouter->sectionId ?>,<?php
                ?>_pu=<?= $this->urlRouter->purposeId ?>,<?php
                ?>_ext=<?= $this->extendedId ?>,<?php
                ?>_loc=<?= $this->localityId ?>,<?php
                ?>_ttl='<span class="<?= $this->urlRouter->siteLanguage ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                ?>_q='<?= $this->urlRouter->params['q'] ? addcslashes($this->urlRouter->params['q'], "'") :'' ?>',<?php
            }
        }
        
        ?>hrs=<?= isset($this->searchResults['body']['total_found']) && $this->searchResults['body']['total_found']>0 ? 1:0 ?>,<?php
        ?>hd='<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->urlRouter->siteLanguage=='en'?'e':'a') ?>',<?php 
        ?>ucss='<?= $this->urlRouter->cfg['url_css_mobile'] ?>',<?php
        
        if ($this->urlRouter->module=='search' || $this->urlRouter->module=='detail' || $this->urlRouter->module=='myads')
        {
            /* ?>xCancel='<?= $this->lang['cancel'] ?>',<?php */
            ?>xAOK='<?= $this->lang['abuseReported'] ?>',<?php
            ?>xF='<?= $this->lang['sys_error'] ?>',<?php
            ?>since='<?= $this->lang['since'] ?>',<?php
            ?>ago='<?= $this->lang['ago'] ?>',<?php
        }
        
        if ($this->urlRouter->module=='account'){
            ?>xSaving='<?= $this->lang['savingProgress'] ?>',<?php
        }
        
        ?>ro=<?= $this->urlRouter->rootId ?>,<?php
        ?>cn=<?= $this->urlRouter->countryId ?>,<?php
        ?>c=<?= $this->urlRouter->cityId ?>,<?php
        ?>se=<?= $this->urlRouter->sectionId ?>,<?php
        ?>pu=<?= $this->urlRouter->purposeId ?>;<?php
        
        echo $this->globalScript;
        ?>
        head.addEventListener("load",function(event){if(event.target.nodeName==="SCRIPT"){
            if(event.target.getAttribute("src").includes("jquery.min.js")||event.target.getAttribute("src").includes("zepto.min.js")){
                var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;<?php
                if ($this->urlRouter->cfg['site_production']) 
                {
                    switch($this->urlRouter->module)
                    {
                        case 'myads':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_fullads.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'post':
                            if ($this->user->info['id'] && $this->isUserMobileVerified) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_fullpost.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_search.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'account':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_account.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'contact':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_contact.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'password':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_password.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'index':
                        default:
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            break;
                    }
                } 
                else 
                {
                    switch($this->urlRouter->module)
                    {
                        case 'myads':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_ads.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_srh.js';document.body.appendChild(sh);<?php 
                            break;

                        case 'post':
                            if ($this->user->info['id'] && $this->isUserMobileVerified) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_post.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'account':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_acc.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh)<?php 
                            }
                            break;
                            
                        case 'contact':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_cnt.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'password':
                            if ($this->include_password_js) 
                            {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_pwd.js';document.body.appendChild(sh);<?php 
                                break;
                            }
                            
                        case 'index':
                        default:
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php  
                            break;
                    }                    
                }                
                ?>                
            }
        }}, true);<?php
        ?>window.onload=function(){<?php      
            echo $this->inlineScript;
        ?>};<?php        
        ?></script><?php 
         
        if($this->urlRouter->module == 'index')
        {
            ?><script type="application/ld+json"><?php
                ?>{"@context": "http://schema.org",<?php
                ?>"@type": "WebSite",<?php
                ?>"url": "https://www.mourjan.com/",<?php
                ?>"potentialAction":{<?php
                ?>"@type": "SearchAction",<?php
                ?>"target": "https://www.mourjan.com/?q={search_term_string}",<?php
                ?>"query-input": "required name=search_term_string"<?php
                ?>}<?php
                ?>}<?php
            ?></script><?php
        }
    }


    
    function load_js() 
    {
        if ($this->globalScript) 
        {
            $this->globalScript=preg_replace('/\s+/', ' ', $this->globalScript);
            $this->globalScript=preg_replace("/[\n\t\r]/", '', $this->globalScript);
            $this->globalScript.=';';
            $this->globalScript=preg_replace("/;;/", ';', $this->globalScript);
        }
        if ($this->inlineScript)
        {
            $this->inlineScript=preg_replace('/\s+/', ' ', $this->inlineScript);
            $this->inlineScript=preg_replace("/[\n\t\r]/", '', $this->inlineScript);
            $this->inlineScript.=';';
            $this->inlineScript=preg_replace("/;;/", ';', $this->inlineScript);
        }
        
        if($this->inlineQueryScript)
        {
            $this->inlineQueryScript=preg_replace('/\s+/', ' ', $this->inlineQueryScript);
            $this->inlineQueryScript=preg_replace("/[\n\t\r]/", '', $this->inlineQueryScript);
            $this->inlineQueryScript.=';';
            $this->inlineQueryScript=preg_replace("/;;/", ';', $this->inlineQueryScript);
        }
        
        echo '<div id="fb-root"></div>';
        
        if (isset($this->user->params['include_JSON']) && $this->urlRouter->module=='post')
        {
            ?><script type="text/javascript" src="<?= $this->urlRouter->cfg['url_jquery'] ?>json2.js"></script><?php
        }         

        ?><script type="text/javascript">
            var head = document.getElementsByTagName("head")[0] || document.documentElement;            
            var ucss='<?= $this->urlRouter->cfg['url_css'] ?>',uimg='<?= $this->urlRouter->cfg['url_ad_img'] ?>',<?php
            
            if(isset($this->user->params['hasCanvas']))
            {            
                ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
            }
            else
            {
                ?>tmp=document.createElement('canvas'),<?php
                ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
            }
            
            if($this->user->info['id'] && $this->urlRouter->module=='myads')
            {
                if ($this->urlRouter->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
                {
                    ?>uhc='<?= $this->urlRouter->cfg['url_highcharts'] ?>',<?php 
                    if($this->user->info['level']==9)
                    {
                        ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                    }
                }
                else
                {
                    ?>uhc=0,<?php 
                }
                
                if ($this->urlRouter->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
                {
                    ?>ustats=1,<?php
                }else{
                    ?>ustats=0,<?php
                }
                
                if (isset($_GET['sub']) && $_GET['sub']=='pending') 
                {
                    ?>PEND=1,<?php
                }else{
                    ?>PEND=0,<?php
                }
                if($this->user->info['level']==9){
                    ?>SU=<?= $this->user->isSuperUser()?1:0 ?>,<?php
                }
            }
            
            if($this->urlRouter->module=='search')
            {
                if($this->userFavorites || $this->urlRouter->watchId)
                {
                    ?>ubs='',<?php
                }else{
                    $tmp=$this->urlRouter->siteLanguage;
                    $this->urlRouter->siteLanguage='ar';
                    ?>ubs='<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>',<?php                 
                    $this->urlRouter->siteLanguage=$tmp;
                }
            }
            
            ?>AID=<?= (isset($this->detailAd[Classifieds::ID]) && !$this->detailAdExpired ? $this->detailAd[Classifieds::ID] : 0) ?>,<?php 
            ?>UID=<?= $this->user->info['id'] ?>,<?php 
            
            if($this->user->info['id']) 
            { 
                ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
                ?>SSID='<?= md5($this->user->info['idKey'].'nodejs.mourjan') 
            ?>',<?php             
            }
            
            ?>PID=<?= $this->urlRouter->userId ? 1:0 ?>,<?php 
            ?>ULV=<?= isset($this->user->info['level']) ? $this->user->info['level'] : 0 ?>,<?php 
            ?>ujs='<?= $this->urlRouter->cfg['url_js'] ?>',<?php 
            
            if($this->user->info['id'] && $this->urlRouter->module=='post')
            {
                ?>UP_URL='<?= $this->urlRouter->cfg['url_uploader'] ?>',<?php 
                ?>USID='<?= session_id() ?>',<?php 
                ?>uixf='<?= $this->urlRouter->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
            }
            
            ?>lang='<?= $this->urlRouter->siteLanguage ?>',<?php
            ?>share=<?= $this->urlRouter->cfg['enabled_sharing'] ? 1:0 ?>,<?php
            ?>hads=<?= $this->urlRouter->cfg['enabled_ads'] ? 1:0 ?>,<?php
            ?>SCLD=0,<?php //script loading var
            ?>ITC=0,<?php //is touch flag 
            ?>jsLog=<?= $this->urlRouter->cfg['enabled_js_log'] ?>,<?php 
            ?>MOD="<?= $this->urlRouter->module ?>",<?php
            ?>STO=(typeof(Storage)==="undefined"?0:1),<?php
            ?>WSO=(typeof(WebSocket)==="undefined"?0:1),<?php
            
            if($this->urlRouter->module=='detail' && !$this->detailAdExpired && ( ($this->detailAd[Classifieds::LATITUDE]  || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]) )  ) 
            {
                ?>hasMap=1,LAT=<?= $this->detailAd[Classifieds::LATITUDE] ?>,LON=<?= $this->detailAd[Classifieds::LONGITUDE] ?>,<?php
                ?>DTTL="<?= htmlspecialchars($this->title, ENT_QUOTES) ?>",<?php
            }
            
            //menu slider vars
            ?>tmr,tmu,tmd,func,fupc,mul,menu,mp,<?php
            if ($this->urlRouter->cfg['enabled_disqus'] && $this->urlRouter->module=='detail' && !$this->detailAdExpired && $this->detailAd[Classifieds::PUBLICATION_ID]==1) 
            {
                ?>disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},disqus_identifier = '<?= $this->detailAd[Classifieds::ID] ?>',<?php
            }elseif ($this->urlRouter->cfg['enabled_disqus'] && $this->urlRouter->module=='myads'){
                ?>disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},<?php
            }
            
            if ($this->stat)
            {
                $this->stat['page']=($this->urlRouter->params['start']) ? $this->urlRouter->params['start'] : 1;
                $this->stat['num']=$this->num;
                ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
                $page=array(
                    'cn'=>$this->urlRouter->countryId,
                    'c'=>$this->urlRouter->cityId,
                    'se'=>$this->urlRouter->sectionId,
                    'pu'=>$this->urlRouter->purposeId,
                );
                ?>page='<?= json_encode($page) ?>',<?php
            }
            else 
            {
                ?>stat=0,<?php
            }
            
            if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) 
            {
                $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
                if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                        && ( ($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) 
                        || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) 
                {
                    ?>_cn=<?= $this->urlRouter->countryId ?>,<?php
                    ?>_c=<?= $this->urlRouter->cityId ?>,<?php
                    ?>_se=<?= $this->urlRouter->sectionId ?>,<?php
                    ?>_pu=<?= $this->urlRouter->purposeId ?>,<?php
                    ?>_ext=<?= $this->extendedId ?>,<?php
                    ?>_loc=<?= $this->localityId ?>,<?php
                    ?>_ttl='<span class="<?= $this->urlRouter->siteLanguage ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                    ?>_q='<?= $this->urlRouter->params['q'] ? addcslashes($this->urlRouter->params['q'], "'") :'' ?>',<?php
                }
            }
            
            ?>ICH='<?= $this->includeHash ?>',<?php
            ?>LSM='<?= $this->urlRouter->last_modified ?>';<?php
            echo $this->globalScript;
            ?>            
            head.addEventListener("load",function(event){if(event.target.nodeName==="SCRIPT"){
                if (event.target.getAttribute("src").includes("jquery.min.js")||event.target.getAttribute("src").includes("zepto.min.js")){
                    var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;
                    <?php
                    switch($this->urlRouter->module)
                    {
                        case 'signin':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/signin.js';head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/search.js';head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'myads':
                            if($this->user->info['id']){
                                if($this->user->info['level']==9){     
                                    if($this->urlRouter->cfg['site_production']){
                                        ?>sh.src='https://h5.mourjan.com/js/3.1.5/myadsad.js';<?php
                                    }else{
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/myadsad.js';<?php 
                                    }
                                }else{
                                    ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/myads.js';<?php                                            
                                }
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
                
                        case 'account':
                            if($this->user->info['id'])
                            {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/account.js';<?php                                            
                            }else{
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
            
                        case 'post':
                            if($this->user->info['id'] && $this->isUserMobileVerified)
                            {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/post.js';<?php                                            
                            }
                            else
                            {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'password':
                            if($this->include_password_js)
                            {
                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/pwd.js';head.insertBefore(sh,head.firstChild);<?php 
                            }
                            break;
                                
                        case 'index':
                        default:
                            ?>(function(){sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php 
                            echo "\n",$this->inlineQueryScript;
                            ?>head.insertBefore(sh,head.firstChild)})();<?php
                            break;
                    }
            ?>}}}, true);<?php
            ?>window.onload=function(){<?php echo $this->inlineScript;?>};<?php                        
            ?>(function(){<?php
                ?>var po=document.createElement('script'); po.type='text/javascript'; po.async=true;<?php
                ?>po.src = 'https://apis.google.com/js/platform.js';<?php
                ?>var s=document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po,s);<?php
            ?>})();<?php
        
        ?></script><?php
        
        if($this->user->info['id'] && $this->user->info['level']==9 && $this->urlRouter->module=='post')
        {
            if($this->urlRouter->cfg['site_production'])
            {
                ?><script type="text/javascript" async="true" src="https://h5.mourjan.com/js/3.0.7/pvc.js"></script><?php
            }else{
                ?><script type="text/javascript" async="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/pvc.js"></script><?php
            }
        }
        
        if($this->urlRouter->module == 'index'){
            $country = '';
            if ($this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId])) {
                $country = $this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
            }
            ?><script type="application/ld+json"><?php
                ?>{"@context": "http://schema.org",<?php
                ?>"@type": "WebSite",<?php
                ?>"url": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>",<?php
                ?>"potentialAction":{<?php
                ?>"@type": "SearchAction",<?php
                ?>"target": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>?q={search_term_string}",<?php
                ?>"query-input": "required name=search_term_string"<?php
                ?>}<?php
                ?>}<?php
            ?></script><?php
        }
    }
    
    
    
    function load_js_classic()
    {
        if ($this->globalScript) {
            $this->globalScript=preg_replace('/\s+/', ' ', $this->globalScript);
            $this->globalScript=preg_replace("/[\n\t\r]/", '', $this->globalScript);
            $this->globalScript.=';';
            $this->globalScript=preg_replace("/;;/", ';', $this->globalScript);
        }
        if ($this->inlineScript){
            $this->inlineScript=preg_replace('/\s+/', ' ', $this->inlineScript);
            $this->inlineScript=preg_replace("/[\n\t\r]/", '', $this->inlineScript);
            $this->inlineScript.=';';
            $this->inlineScript=preg_replace("/;;/", ';', $this->inlineScript);
        }
        
        if($this->inlineQueryScript){            
            $this->inlineQueryScript=preg_replace('/\s+/', ' ', $this->inlineQueryScript);
            $this->inlineQueryScript=preg_replace("/[\n\t\r]/", '', $this->inlineQueryScript);
            $this->inlineQueryScript.=';';
            $this->inlineQueryScript=preg_replace("/;;/", ';', $this->inlineQueryScript);
        }
        
        ?><div id="fb-root"></div><?php 
        if(isset($this->user->params['include_JSON']) && $this->urlRouter->module=='post'){
            ?><script type="text/javascript" async="true" defer="true" src="<?= $this->urlRouter->cfg['url_jquery'] ?>json2.js"></script><?php
        }         
        /*
        if($this->urlRouter->module=='buy' && $this->user->info['id']){
            ?><script src='https://www.paypalobjects.com/js/external/dg.js' type='text/javascript'></script><?php
        }
         * 
        */
        ?><script type="text/javascript"><?php 
        /*
            if($this->urlRouter->module=='buy' && $this->user->info['id']){
                ?>var dg = new PAYPAL.apps.DGFlow({trigger: ['sub0','sub1','sub2','sub3'],expType: 'instant'});<?php
            }
         * 
         */
            ?>var head = document.getElementsByTagName("head")[0] || document.documentElement;<?php
            ?>function addEvent(e, en, fn){if (e.addEventListener)e.addEventListener(en, fn, false);else if(e.attachEvent)e.attachEvent('on' + en, fn)}<?php
            /* ?>function loadCss(fn,cb){var s=document.getElementsByTagName("link"),l=s.length-1,p=0,e;for(i=l;i>=0;i--){if(s[i].rel=='stylesheet'){e=s[i];break;}}if(typeof e==='undefined'){p=1;e=head.firstChild}var l=document.createElement('link');l.rel='stylesheet';l.type="text/css";l.media='all';l.href=fn;e.parentNode.insertBefore(l,e.nextSibling)}<?php */
            /*?>function loadCss(fn,cb){var e=document.getElementsByTagName('script')[0];var l=document.createElement('link');l.rel='stylesheet';l.type="text/css";l.media='all';l.href=fn;e.parentNode.insertBefore(l,e.nextSibling)}<?php */
            
            /*function AJAXLoad(type,url) {
                    var ext;
                    if (type == "js") {
                        ext = document.createElement('script');
                        ext.setAttribute("type","text/javascript");
                    }
                    if (type == "css") {
                        ext = document.createElement('style');
                        ext.rel= "stylesheet";
                        ext.type="text/css";
                        ext.media="all";
                    }
                    var xr;
                    if (window.XMLHttpRequest) {xr=new XMLHttpRequest()}
                    else {xr=new ActiveXObject("Microsoft.XMLHTTP")}
                    xr.onreadystatechange=function(){
                        if (xr.readyState==4) {
                            if (xr.status == 200) {
                                if (type == "css") {
                                    var reg=new RegExp('url\\(','g');
                                    var x=xr.responseText;
                                    x=x.replace(reg,'url(<?= $this->urlRouter->cfg['url_css'] ?>/');
                                    ext.innerHTML=x;
                                    document.body.appendChild(ext);
                                }else{
                                    ext.innerHTML=xr.responseText;
                                    head.appendChild(ext);
                                }
                            } else {
                                console.log('cannot load external file :'+url);
                            }
                        }
                    }
                    xr.open("GET",url,true);
                    xr.send();
                }*/
            /*
            if ($this->urlRouter->cfg['enabled_ads'] && count($this->googleAds)) {
                ?>(function(){var gads=document.createElement('script');gads.async=true;gads.type='text/javascript';var useSSL='https:'==document.location.protocol;gads.src=(useSSL?'https:':'http:')+'//www.googletagservices.com/tag/js/gpt.js';head.appendChild(gads);})();<?php
            }
             * 
             */
            ?>var ucss='<?= $this->urlRouter->cfg['url_css'] ?>',<?php 
            ?>uimg='<?= $this->urlRouter->cfg['url_ad_img'] ?>',<?php 
            if(isset($this->user->params['hasCanvas'])){            
                ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
            }else{
                ?>tmp=document.createElement('canvas'),<?php
                ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
            }
            if($this->user->info['id'] && $this->urlRouter->module=='myads'){
                if ( $this->urlRouter->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                    ?>uhc='<?= $this->urlRouter->cfg['url_highcharts'] ?>',<?php 
                    if($this->user->info['level']==9){
                        ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                    }
                }else{
                    ?>uhc=0,<?php 
                }
                if ($this->urlRouter->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                    ?>ustats=1,<?php
                }else{
                    ?>ustats=0,<?php
                }
                if (isset($_GET['sub']) && $_GET['sub']=='pending') {
                    ?>PEND=1,<?php
                }else{
                    ?>PEND=0,<?php
                }
                if($this->user->info['level']==9){
                    ?>SU=<?= $this->user->isSuperUser()?1:0 ?>,<?php
                }
            }
            if($this->urlRouter->module=='search'){
                if($this->userFavorites || $this->urlRouter->watchId){
                    ?>ubs='',<?php
                }else{
                    $tmp=$this->urlRouter->siteLanguage;
                    $this->urlRouter->siteLanguage='ar';
                    ?>ubs='<?= $this->urlRouter->getURL($this->urlRouter->countryId,$this->urlRouter->cityId) ?>',<?php                 
                    $this->urlRouter->siteLanguage=$tmp;
                }
            }
            ?>AID=<?= (isset($this->detailAd[Classifieds::ID]) && !$this->detailAdExpired ? $this->detailAd[Classifieds::ID] : 0) ?>,<?php 
            ?>UID=<?= $this->user->info['id'] ?>,<?php 
            if($this->user->info['id']) { 
                ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
                ?>SSID='<?= md5($this->user->info['idKey'].'nodejs.mourjan') 
            ?>',<?php }
            ?>PID=<?= $this->urlRouter->userId ? 1:0 ?>,<?php 
            ?>ULV=<?= isset($this->user->info['level']) ? $this->user->info['level'] : 0 ?>,<?php 
            ?>ujs='<?= $this->urlRouter->cfg['url_js'] ?>',<?php 
            
            if($this->user->info['id'] && $this->urlRouter->module=='post'){
                ?>UP_URL='<?= $this->urlRouter->cfg['url_uploader'] ?>',<?php 
                ?>USID='<?= session_id() ?>',<?php 
                ?>uixf='<?= $this->urlRouter->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
            }
            
            ?>lang='<?= $this->urlRouter->siteLanguage ?>',<?php
            ?>share=<?= $this->urlRouter->cfg['enabled_sharing'] ? 1:0 ?>,<?php
            ?>hads=<?= $this->urlRouter->cfg['enabled_ads'] ? 1:0 ?>,<?php
            ?>SCLD=0,<?php //script loading var
            ?>ITC=0,<?php //is touch flag 
            ?>jsLog=<?= $this->urlRouter->cfg['enabled_js_log'] ?>,<?php 
            ?>MOD="<?= $this->urlRouter->module ?>",<?php
            ?>STO=(typeof(Storage)==="undefined"?0:1),<?php
            ?>WSO=(typeof(WebSocket)==="undefined"?0:1),<?php
            if($this->urlRouter->module=='detail' && !$this->detailAdExpired && ( ($this->detailAd[Classifieds::LATITUDE]  || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]) )  ) {
                ?>hasMap=1,LAT=<?= $this->detailAd[Classifieds::LATITUDE] ?>,LON=<?= $this->detailAd[Classifieds::LONGITUDE] ?>,<?php
                ?>DTTL="<?= htmlspecialchars($this->title, ENT_QUOTES) ?>",<?php
            }
            //menu slider vars
            ?>tmr,tmu,tmd,func,fupc,mul,menu,mp,<?php
            if ($this->urlRouter->cfg['enabled_disqus'] && $this->urlRouter->module=='detail' && !$this->detailAdExpired && $this->detailAd[Classifieds::PUBLICATION_ID]==1) {
                ?>disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},disqus_identifier = '<?= $this->detailAd[Classifieds::ID] ?>',<?php
            }elseif ($this->urlRouter->cfg['enabled_disqus'] && $this->urlRouter->module=='myads'){
                ?>disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},<?php
            }
            if ($this->stat){
                $this->stat['page']=($this->urlRouter->params['start']) ? $this->urlRouter->params['start'] : 1;
                $this->stat['num']=$this->num;
                ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
                $page=array(
                    'cn'=>$this->urlRouter->countryId,
                    'c'=>$this->urlRouter->cityId,
                    'se'=>$this->urlRouter->sectionId,
                    'pu'=>$this->urlRouter->purposeId,
                );
                ?>page='<?= json_encode($page) ?>',<?php
            }else {
                ?>stat=0,<?php
            }
            if ($this->urlRouter->module == 'search' && !$this->userFavorites && !$this->urlRouter->watchId) {
                $key = $this->urlRouter->countryId . '-' . $this->urlRouter->cityId . '-' . $this->urlRouter->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->urlRouter->purposeId . '-' . crc32($this->urlRouter->params['q']);
                if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                        && ( ($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId) 
                        || ($this->urlRouter->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) {
                    ?>_cn=<?= $this->urlRouter->countryId ?>,<?php
                    ?>_c=<?= $this->urlRouter->cityId ?>,<?php
                    ?>_se=<?= $this->urlRouter->sectionId ?>,<?php
                    ?>_pu=<?= $this->urlRouter->purposeId ?>,<?php
                    ?>_ext=<?= $this->extendedId ?>,<?php
                    ?>_loc=<?= $this->localityId ?>,<?php
                    ?>_ttl='<span class="<?= $this->urlRouter->siteLanguage ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                    ?>_q='<?= $this->urlRouter->params['q'] ? addcslashes($this->urlRouter->params['q'], "'") :'' ?>',<?php
                }
            }
            ?>ICH='<?= $this->includeHash ?>',<?php
            ?>LSM='<?= $this->urlRouter->last_modified ?>';<?php
            if(0 && in_array($this->urlRouter->module,['index','search','detail'])){ ?>loadCss(ucss+"/gen<?= $this->urlRouter->siteLanguage=='ar'?'_ar':'' ?>.css");<?php }
            /*if (isset($this->requires['css'])) {
                foreach ($this->requires['css'] as $css) {
                    ?>loadCss(ucss+'<?= $css ?>');<?php  
                } 
            } */            
            /*?>addEvent(window,'load',function(){loadCss(ucss+"/imgs.css")});<?php */
            /*?>if(!canImp){loadCss(ucss+"/imgs.css")}<?php */
            /*?>loadCss(ucss+"/imgs.css");<?php */
            echo $this->globalScript;
            /* ?>function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:lang, layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false, multilanguagePage: true, gaTrack: true, gaId: 'UA-435731-13'}, 'google_translate_element');}<?php */
            
            
            /* ?>window.onload=function(){<?php */
            
            /*
                    switch($this->urlRouter->module){
                        case 'signin':
                            ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/signin.js';head.insertBefore(sh,head.firstChild);<?php 
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                        case 'detail':
                        case 'search':
                             ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/search.js';head.insertBefore(sh,head.firstChild);<?php 
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                        case 'myads':
                            ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;<?php 
                                    if($this->user->info['id']){
                                        if($this->user->info['level']==9){     
                                            if($this->urlRouter->cfg['site_production']){
                                                ?>sh.src='https://h5.mourjan.com/js/3.0.8/myadsad.js';<?php
                                            }else{
                                                ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/myadsad.js';<?php 
                                            }
                                        }else{
                                            ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/myads.js';<?php                                            
                                        }
                                    }else{
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                                    }
                                    ?>head.insertBefore(sh,head.firstChild);<?php 
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                        case 'account':
                            ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;<?php 
                                    if($this->user->info['id']){
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/account.js';<?php                                            
                                    }else{
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                                    }
                                    ?>head.insertBefore(sh,head.firstChild);<?php 
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                        case 'post':
                            ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;<?php 
                                    if($this->user->info['id']){
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/post.js';<?php                                            
                                    }else{
                                        ?>sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';<?php
                                    }
                                    ?>head.insertBefore(sh,head.firstChild);<?php 
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                        case 'password':
                            if($this->include_password_js){
                                ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                                ?>sh.onload=sh.onreadystatechange=function(){<?php
                                    ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                        ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/pwd.js';head.insertBefore(sh,head.firstChild);<?php 
                                    ?>}<?php 
                                ?>};<?php
                                ?>head.insertBefore(sh,head.firstChild)})();<?php 
                                break;
                            }
                        case 'index':
                        default:
                            ?>(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js';<?php 
                            ?>sh.onload=sh.onreadystatechange=function(){<?php
                                ?>if (!SCLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){<?php
                                    ?>SCLD=true;var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->urlRouter->cfg['url_js'] ?>/gen.js';head.insertBefore(sh,head.firstChild);<?php 
                                    echo $this->inlineQueryScript;
                                ?>}<?php 
                            ?>};<?php
                            ?>head.insertBefore(sh,head.firstChild)})();<?php 
                            break;
                    } */
                echo $this->inlineScript;
            /* ?>};<?php */
                
                /*
                switch($this->urlRouter->module){
                case 'myads':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_ads.js'<?php
                        ?>])<?php
                    }else{

                    }
                    break;
                case 'post':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_post.js'<?php
                        ?>])<?php
                    }else{

                    }
                    break;
                case 'detail':
                case 'search':                
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_srh.js'<?php
                    ?>]);<?php
                    break;
                case 'account':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_acc.js'<?php
                        ?>]);<?php
                    }else{

                    }
                    break;
                case 'contact':
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_cnt.js'<?php
                        ?>]);<?php
                    break;
                case 'password':
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->urlRouter->cfg['url_js_mobile'] ?>/m_pwd.js'<?php
                        ?>]);<?php
                    break;

                case 'index':
                default:
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->urlRouter->cfg['url_jquery'] ?>/jquery.min.js'<?php
                    ?>]);<?php

                    break;
            }*/
        
                if($this->urlRouter->cfg['enabled_sharing'] && in_array($this->urlRouter->module,['index','search','detail'])){ 
                    ?>addEvent(window,'load',function(){<?php
                        ?>var po = document.createElement('script');<?php
                        ?>po.type = 'text/javascript';<?php 
                        ?>po.async = true;<?php
                        ?>po.src = 'https://apis.google.com/js/platform.js';<?php
                        ?>var s = document.getElementsByTagName('script')[0];<?php
                        ?>s.parentNode.insertBefore(po,s);<?php 
                        //pagead2.googlesyndication.com/pagead/js/adsbygoogle.js
                        if(in_array($this->urlRouter->module,['search','detail'])){
                        ?>po = document.createElement('script');<?php
                        ?>po.type = 'text/javascript';<?php 
                        ?>po.async = true;<?php
                        ?>po.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';<?php
                        ?>s.parentNode.insertBefore(po,s);<?php 
                        }
                      ?>});<?php 
                }
        
            
        /*?>(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://':'http://')+'stats.g.doubleclick.net/dc.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();<?php*/
                ?>function inlineQS(){<?= $this->inlineQueryScript; ?>}<?php
        ?></script><?php
        ?><script type="text/javascript" onload="inlineQS()" defer="true" src="<?= $this->urlRouter->cfg['url_jquery'] ?>jquery.min.js"></script><?php
        switch($this->urlRouter->module){
            case 'signin':
                ?><script type="text/javascript" src="<?= $this->urlRouter->cfg['url_jquery'] ?>socket.io-1.4.5.js"></script><?php
                ?><script type="text/javascript" src="<?= $this->urlRouter->cfg['url_js'] ?>/signin.js"></script><?php
                break;
            case 'detail':
            case 'search':
                ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/search.js"></script><?php
                break;
            case 'myads':
                if($this->user->info['id']){
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_jquery'] ?>socket.io-1.4.5.js"></script><?php
                    if($this->user->info['level']==9){     
                        if($this->urlRouter->cfg['site_production']){
                            ?><script type="text/javascript" defer="true" src="https://h5.mourjan.com/js/3.1.5/myadsad.js"></script><?php
                        }else{
                            ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/myadsad.js"></script><?php
                        }
                    }else{
                        ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/myads.js"></script><?php                                          
                    }
                }
                break;
            case 'account':
                if($this->user->info['id']){
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/account.js"></script><?php
                }
                break;
            case 'post':
                if($this->user->info['id'] && $this->isUserMobileVerified){                    
                    if($this->user->info['id'] && $this->user->info['level']==9 && $this->urlRouter->module=='post'){
                        if($this->urlRouter->cfg['site_production']){
                            ?><script type="text/javascript" src="https://h5.mourjan.com/js/3.0.7/pvc.js"></script><?php
                        }else{
                            ?><script type="text/javascript" src="<?= $this->urlRouter->cfg['url_js'] ?>/pvc.js"></script><?php
                        }
                    }
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/post.js"></script><?php
                }elseif($this->user->info['id']){
                    ?><script type="text/javascript" onload="$('#code').select2({language:'<?= $this->urlRouter->siteLanguage ?>',dir:'rtl'})" defer="true" src="<?= $this->urlRouter->cfg['url_jquery'] ?>select2.min.js"></script><?php
                }
                break;
            case 'password':
                if($this->include_password_js){
                    ?><script type="text/javascript" defer="true" src="<?= $this->urlRouter->cfg['url_js'] ?>/pwd.js"></script><?php
                }
                break;
            case 'index':                
            default:
                break;
        }
        
        if($this->urlRouter->module == 'index'){
            $country = '';
            if ($this->urlRouter->countryId && isset($this->urlRouter->countries[$this->urlRouter->countryId])) {
                $country = $this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
            }
            ?><script type="application/ld+json"><?php
                ?>{"@context": "http://schema.org",<?php
                ?>"@type": "WebSite",<?php
                ?>"url": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>",<?php
                ?>"potentialAction":{<?php
                ?>"@type": "SearchAction",<?php
                ?>"target": "https://www.mourjan.com/<?= ($country ? $country.'/' :'').($this->urlRouter->siteLanguage=='ar' ?'':$this->urlRouter->siteLanguage.'/') ?>?q={search_term_string}",<?php
                ?>"query-input": "required name=search_term_string"<?php
                ?>}<?php
                ?>}<?php
            ?></script><?php
        }
        /*
        ?><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script><?php
         * 
         */

        
        
    }

    
    function set_ad($ad_names) {
        $this->googleAds = array_merge($this->googleAds, $ad_names);
    }

    
    function fill_ad($name, $className='ad') {
        $str='';
        if ($this->urlRouter->cfg['enabled_ads'] && isset ($this->googleAds[$name])) {
            if ($className!='ad') $className='ad '.$className;
            
            $ad = $this->googleAds[$name];
            $className.=' w'.$ad[1];
            
            $str .= "<div class='{$className}'>";
            $str .= "<div id='{$ad[3]}'>";
            /*if (strstr($className,'ad_s')){
                $str.='Small Square';
            }
            if (strstr($className,'adc')){
                $str.='Large Rectangle';
            }
            if (strstr($className,'ad_m')){
                $str.='Medium Rectangle';
            }
            if (strstr($className,'ad_w')){
                $str.='Leaderboard';
            }
            if (strstr($className,'ad_det')){
                $str.='Top Medium Rectangle';
            }*/
            $str .= "<script type='text/javascript'>";
            $str .= "googletag.cmd.push(function() {googletag.display('{$ad[3]}');});";
            $str .= '</script></div>';
            //$str .= '</div>';
            //$str.='<div style="line-height:'.$ad[2].'px">'.$name.' '.$ad[1].'x'.$ad[2].'</div>';
            $str .= '</div>';
        }
        return $str;
    }

    protected function render(){
        if ($this->rss) {
            $this->_rss();
        } else {
            $this->_header();
            $this->_body();
            $this->user->setStats();
        }
    }
    
    function includeMetaKeywords(){
        $keywords='';
        $keywords.=  $this->lang['mourjan'].',';
        $keywords.=  $this->lang['pclassifieds'].',';
        if($this->urlRouter->siteLanguage=='ar'){
            $keywords.='نشر إعلان,إعلان,';
        }else{
            $keywords.='ad,post ad,';
        }
        if($this->urlRouter->cityId){
            $keywords.= $this->urlRouter->countries[$this->urlRouter->countryId]['cities'][$this->urlRouter->cityId]['name'];// $this->urlRouter->cities[$this->urlRouter->cityId][$this->fieldNameIndex].',';
            $keywords.= $this->urlRouter->countries[$this->urlRouter->countryId]['name'].',';
        }elseif($this->urlRouter->countryId){
            $keywords.= $this->urlRouter->countries[$this->urlRouter->countryId]['name'].',';
        }
        if($this->urlRouter->module=='index'){
            foreach($this->urlRouter->pageRoots as $rid=>$root){
                $keywords.= $root['name'].',';
            }
            foreach($this->urlRouter->purposes as $ro){
                if($ro[0]!=999 && $ro[0]!=5)
                $keywords.= $ro[$this->fieldNameIndex].',';
            }
            $keywords=substr($keywords,0,-1);
            ?><meta name="keywords" content="<?= $keywords ?>"><?php
        }elseif($this->urlRouter->module=='search' && !$this->userFavorites && !$this->urlRouter->watchId && !$this->urlRouter->userId){
            if($this->urlRouter->rootId){
                $keywords.= $this->urlRouter->roots[$this->urlRouter->rootId][$this->fieldNameIndex].',';
            }else{
                foreach($this->urlRouter->pageRoots as $ro){
                    $keywords.= $this->urlRouter->roots[$ro[0]][$this->fieldNameIndex].',';
                }
            }
            if($this->urlRouter->sectionId){
                $keywords.= $this->urlRouter->sections[$this->urlRouter->sectionId][$this->fieldNameIndex].',';
                if($this->extended){
                    if($this->extendedId){
                        $keywords.= $this->extended[$this->extendedId]['name'].',';
                    }else{
                        $i=0;
                        foreach ($this->extended as $sub) {
                            $keywords.= $sub['name'].','; //$sub[1].',';
                            if(++$i==6)break;
                        }
                    }
                }
                if($this->localities){
                    $i=0;
                    foreach ($this->localities as $sub) {
                        if ($sub['parent_geo_id'] == $this->localityId) {
                            if ($this->localityId && ($sub['counter'] >= $this->localities[$this->localityId]['counter'] - 2 && $sub['counter'] <= $this->localities[$this->localityId]['counter']))
                                continue;
                            $keywords.= $sub['name'].',';
                            if(++$i==6)break;
                        }
                    }
                }
            }
            if($this->urlRouter->purposeId){
                $keywords.= $this->urlRouter->purposes[$this->urlRouter->purposeId][$this->fieldNameIndex].',';
            }else{                             
                foreach($this->urlRouter->pagePurposes as $pId=>$purpose){
                    if($pId!=999 && $pId!=5)
                    $keywords.= $purpose['name'].',';
                }
             
                /*
                foreach($this->urlRouter->pagePurposes as $ro){
                    if($ro[0]!=999 && $ro[0]!=5)
                    $keywords.= $this->urlRouter->purposes[$ro[0]][$this->fieldNameIndex].',';
                }
                 * 
                 */
            }
            $keywords=substr($keywords,0,-1);
            ?><meta name="keywords" content="<?= $keywords ?>"><?php
        }
    }

    
    protected function _header(){
        $country_code="";
        if ($this->urlRouter->countryId && array_key_exists($this->urlRouter->countryId, $this->urlRouter->countries)) {
            $country_code = '-'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
        }
        
        ?><!doctype html><html lang="<?= $this->urlRouter->siteLanguage . $country_code ?>" xmlns:fb="http://ogp.me/ns/fb#" xmlns:og="http://ogp.me/ns#"<?= (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]) ? ' xmlns:video="http://ogp.me/ns/video#"':'' ?>><head><meta charset="UTF-8"><?php 
        $this->load_css();
        $this->header();

        echo '<title>', $this->title, '</title>';

        if ($this->isMobile) {
            
            ?><meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0"><?php
            /* ?><link rel="apple-touch-icon-precomposed" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon.png" ?>"/><?php
            ?><link rel="apple-touch-icon" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon.png" ?>"/><?php */
            ?><link rel="apple-touch-icon" sizes="57x57" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon-114.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="114x114" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon-114.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="72x72" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon-144.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="144x144" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-icon-144.png" ?>" /><?php
            
            ?><link rel="apple-touch-startup-image" href="<?= $this->urlRouter->cfg["url_img"]."/mourjan-splash.png"?>" /><?php
            ?><meta name="format-detection" content="telephone=no"><?php
           /* ?><meta name="apple-mobile-web-app-capable" content="yes"><?php
            ?><meta name="apple-mobile-web-app-status-bar-style" content="black"><?php */
        }
        //echo '<meta name="google-translate-customization" content="9d4f90b5d120d88a-90a8c23848bfb466-gb59ab1efd8ce3495-f"></meta>';

        if ($country_code && isset($this->urlRouter->cities)) {
/*
            if ($this->urlRouter->cityId>0 && isset($this->urlRouter->cities[$this->urlRouter->cityId])) {
                $geo = $this->urlRouter->cities[$this->urlRouter->cityId];
                $geo[2] = ucfirst($geo[2]);
                echo '<meta name="geo.placename" content="', $geo[2], ' - ', $this->urlRouter->countries[$this->urlRouter->countryId][2], '" />';
                echo '<meta name="geo.position" content="', $geo[7],';', $geo[8],'" />';
                echo '<meta name="geo.region" content="',  strtoupper($this->urlRouter->countries[$this->urlRouter->countryId][3]) ,'-', $geo[2], '" />';
                echo '<meta name="ICBM" content="', $geo[7], ', ', $geo[8], '" />';
            } else {
                $geo = NULL;
                if (!empty($this->urlRouter->countryCities)) {
                    $__firstCityId = current(reset($this->urlRouter->countryCities));
                    $geo = isset($this->urlRouter->cities[$__firstCityId]) ? $this->urlRouter->cities[$__firstCityId] : NULL;
                }
                echo '<meta name="geo.placename" content="', $this->urlRouter->countries[$this->urlRouter->countryId][2], '" />';
                if ($geo) echo '<meta name="geo.position" content="', $geo[7],';', $geo[8],'" />';
                echo '<meta name="geo.region" content="" />';
                if ($geo) echo '<meta name="ICBM" content="', $geo[7], ', ', $geo[8], '" />';                       
            }*/
            echo '<meta http-equiv="content-language" content="', $this->urlRouter->siteLanguage, $country_code, '">';
        } else {
            echo '<meta http-equiv="content-language" content="', $this->urlRouter->siteLanguage, $country_code,'" />';
        }
        if ($this->forceNoIndex) {
            echo '<meta name="robots" content="noindex,nofollow,noarchive" />';

        }else {
        switch ($this->urlRouter->module) {
            case "detail":
                echo '<meta name="robots" content="noindex" />';
                /*
                if ($this->detailAd && $this->detailAd[Classifieds::ID] && !$this->detailAdExpired) {
                    if (in_array($this->detailAd[Classifieds::SECTION_ID],array(29,63,105,117))) {
                        $time = time()-strtotime($this->detailAd[Classifieds::DATE_ADDED]);
                        //within 7 days no index
                        if ($time<604800) {
                            echo '<meta name="robots" content="noindex, follow" />';
                            break;
                        }
                    }
                    
                    echo '<meta name="expires" content="',date('D, d M Y H:i:s T', strtotime($this->detailAd[Classifieds::EXPIRY_DATE])),'" />';
                    $_cid_ = (int)$this->detailAd[Classifieds::CANONICAL_ID];
                    $_rtl = (int)$this->detailAd[Classifieds::RTL];
                    $_uri = $this->detailAd[Classifieds::URI_FORMAT];
                    if ($_cid_) {
                        $row = $this->urlRouter->db->queryResultArray(
                                "select a.rtl, a.canonical_id, a.hold,
                                '/'||lower(c.ID_2)||'/'||cy.uri||'/'||s.uri||'/'||pu.uri||'/%s%d/' URI
                                from ad a
                                left join publication p on a.publication_id=p.id
                                left join country c on c.id=p.country_id
                                left join city cy on cy.id=p.city_id
                                left join section s on s.id=a.section_id
                                left join category r on r.id=s.category_id
                                left join purpose pu on pu.id=a.purpose_id
                                where a.id = {$_cid_} ");
                        $_rtl = (int)$row[0]['RTL'];
                        $_uri = $row[0]['URI'];
                    } else {
                        $_cid_ = $this->detailAd[Classifieds::ID];
                    }
                    echo '<link rel="canonical" href="http://www.mourjan.com', sprintf($_uri, ($_rtl?'':'en/'), $_cid_), '" />';

                    if (($_rtl==1 && $this->urlRouter->siteLanguage!='ar')||($_rtl==0 && $this->urlRouter->siteLanguage=='ar')) {
                        echo '<meta name="robots" content="noindex, follow" />';
                        echo '<meta name="mourjan" content="'. $_rtl . ", {$this->urlRouter->siteLanguage}" , '" />';
                    }else {
                        echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                    }
                    echo '<meta name="googlebot" content="unavailable_after: ', date('d-M-Y H:i:s T',strtotime($this->detailAd[Classifieds::EXPIRY_DATE])),'"/>';
                    echo '<meta name="bingbot" content="noindex" />';
                    echo '<meta name="msnbot" content="noindex" />';
                } else {
                    echo '<meta name="robots" content="noindex, follow" />';
                }*/
                // end of detail page part
                break;

            case 'search':
                if ($this->userFavorites) {
                    echo '<meta name="robots" content="noindex, nofollow" />';
                } else {
                    if ($this->searchResults && !empty($this->searchResults['body']['matches']) && !(isset ($this->urlRouter->params['tag_id']) && !$this->extendedId) && ( !(isset ($this->urlRouter->params['loc_id']) && !$this->localityId) || ($this->localityId && in_array($this->urlRouter->countryId,array(1,2,4,7,9)))) ) {
                        
                        if (array_key_exists('q', $_GET)) {
                            echo '<meta name="robots" content="noindex, follow" />';
                            $currentUrl=$this->urlRouter->getUrl($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
                            $qTotal = $this->searchResults['body']['total_found'];
                            $qPages=$qTotal/$this->num;
                            if ($qPages<1) $qPages=0;
                            else $qPages=ceil($qPages);
                            $qTmp=ceil($this->urlRouter->cfg['search_results_max']/$this->num);
                            if ($qPages>$qTmp) $qPages=(int)$qTmp;
                            if ($this->urlRouter->params['start']<$qPages) {
                                $next = $this->urlRouter->params['start']+1;
                                if ($next==1) $next=2;
                                echo '<link rel="prerender" href="', $this->urlRouter->cfg['url_base'], $currentUrl, $next,'/?q=',urlencode($this->urlRouter->params['q']), '" />';
                                echo '<link rel="prefetch" href="', $this->urlRouter->cfg['url_base'], $currentUrl, $next,'/?q=',urlencode($this->urlRouter->params['q']), '" />';
                            }
                        }else {
                            
                            $this->includeMetaKeywords();
                            
                            $startLink='';
                            if ($this->extendedId || $this->localityId) {
                                $currentUrl=$this->extended_uri;
                            }else {
                                $currentUrl=$this->urlRouter->getUrl($this->urlRouter->countryId,$this->urlRouter->cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId);
                            }
                            if ($this->urlRouter->params['start']>1) $startLink=$this->urlRouter->params['start'].'/';
                            $link=  $this->urlRouter->cfg['host'].$currentUrl.$startLink;
                            
                            if ($link == $this->urlRouter->cfg['host'].$_SERVER['REQUEST_URI']){
                                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                                    
                                    if($this->urlRouter->countryId && $this->urlRouter->sectionId && $this->urlRouter->purposeId && $this->urlRouter->params['start']<=1){
                                        echo '<link rel="alternate" href="android-app://com.mourjan.classifieds/mourjan/list/';
                                        echo '?';
                                        echo "cn={$this->urlRouter->countryId}&";
                                        echo "c={$this->urlRouter->cityId}&";
                                        echo "ro={$this->urlRouter->rootId}&";
                                        echo "se={$this->urlRouter->sectionId}&";
                                        echo "pu={$this->urlRouter->purposeId}&";
                                        echo "tx={$this->extendedId}&";
                                        echo "gx={$this->localityId}&";
                                        echo "hl={$this->urlRouter->siteLanguage}";
                                        echo '" />';
                                    }
                            }else {
                                echo '<meta name="robots" content="noindex, follow" />';
                            }
                                    
                            echo '<link rel="canonical" href="',$link, '" />';
                            

                            if ($this->urlRouter->params['start']>1) {
                                $prev=$this->urlRouter->params['start'] - 1;
                                if ($prev>1)
                                    echo "<link rel='prev' href='", $this->urlRouter->cfg['url_base'], $currentUrl, $prev, '/';
                                else
                                    echo "<link rel='prev' href='", $this->urlRouter->cfg['url_base'], $currentUrl;
                                echo "' />";
                            }

                            $qTotal = $this->searchResults['body']['total_found'];
                            
                            
                            $qPages=$qTotal/$this->num;
                            if ($qPages<1) $qPages=0;
                            else $qPages=ceil($qPages);

                            $qTmp=ceil($this->urlRouter->cfg['search_results_max']/$this->num);

                            if ($qPages>$qTmp) $qPages=(int)$qTmp;

                            if ($this->urlRouter->params['start']<$qPages) {
                                $next = $this->urlRouter->params['start']+1;
                                if ($next==1) $next=2;
                                echo "<link rel='next' href='", $this->urlRouter->cfg['url_base'], $currentUrl, $next, '/';
                                echo "' />";
                                echo '<link rel="prerender" href="', $this->urlRouter->cfg['url_base'], $currentUrl, $next, '/" />';
                                echo '<link rel="prefetch" href="', $this->urlRouter->cfg['url_base'], $currentUrl, $next, '/" />';
                            }
                        }
                    }else {
                        echo '<meta name="robots" content="noindex, follow" />';
                    }
                }

                echo '<link href="', $this->urlRouter->cfg['url_base'], $this->urlRouter->uri,($this->urlRouter->siteLanguage=='ar' ? '':'en/'), '?rss=1" rel="alternate" type="application/rss+xml" title="', $this->title, '" />';

                break;
            case 'index':
                $currentUrl=$this->urlRouter->getUrl($this->urlRouter->countryId,$this->urlRouter->cityId);
                $link=  $this->urlRouter->cfg['host'].$currentUrl;
                if ($link == $this->urlRouter->cfg['host'].$_SERVER['REQUEST_URI']) { 
                    $this->includeMetaKeywords();
                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                }else echo '<meta name="robots" content="noindex, follow" />';                                    
                echo '<link rel="canonical" href="',$link, '" />';
                break;
            default:
                //echo '<meta name="expires" content="never" />';
                if ($this->urlRouter->module=='notfound') {
                    echo '<meta name="robots" content="noindex, nofollow" />';
                } elseif($this->urlRouter->module=='privacy' || $this->urlRouter->module=='terms' || $this->urlRouter->module=='about' || $this->urlRouter->module=='advertise') {
                    if ($this->urlRouter->siteLanguage=='en'){
                        echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                    }else {
                        echo '<meta name="robots" content="noindex, nofollow" />';
                    }
                }else {
                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                }
                break;
        }
        }

        ?><link rel="icon" href="<?= $this->urlRouter->cfg['url_img'] ?>/favicon.ico" type="image/x-icon" /><?php 
        /* if (!$this->isMobile) {?><meta http-equiv="X-UA-Compatible" content="IE=8" /><?php } */
            $this->set_analytics_header();
            /*$valentine_day = date('j');
             *  <script async src="<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/jq.min.js"></script>
            ?></head><?php flush() ?><body class="<?= ($this->urlRouter->userId ? 'partner':'').
                    ( ($valentine_day==14 && in_array($this->urlRouter->countryId,array(1,2,5,6,10,11,15,122,145))) ? ' valentine':'' ) 
                    ?>" <?= $this->pageItemScope ?>><meta itemprop="isFamilyFriendly" content="true" /><?php
             * 
             */
            
        ?></head><?php flush() ?><body<?= $this->isAdminSearch ? ' oncontextmenu="return false;"':'' ?> class="<?= ($this->urlRouter->userId ? 'partner':'') ?>" <?= $this->pageItemScope ?>><meta itemprop="isFamilyFriendly" content="true" /><?php
    }


    protected function _rss() {
        include($this->urlRouter->cfg['dir']. "/core/lib/rss/FeedTypes.php");

        //Creating an instance of RSS2FeedWriter class.
        //The constant RSS2 is passed to mention the version
        $feed = new RSS2FeedWriter();

        //Setting the channel elements
        //Use wrapper functions for common channel elements
        //$feed->setTitle(trim($this->urlRouter->pageTitle[$this->urlRouter->siteLanguage]));
        $feed->setTitle($this->title);
        $feed->setLink($this->urlRouter->cfg['host'] . $this->urlRouter->uri);
        if ($this->lang['description']) {
            $feed->setDescription(preg_replace("/<.*?>/", "", $this->lang['description']));
        }

        //Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
        //$TestFeed->setImage('Testing the RSS writer class','http://www.ajaxray.com/projects/rss','http://www.rightbrainsolution.com/_resources/img/logo.png');

        //Use core setChannelElement() function for other optional channels
        $country_code="";
        if ($this->urlRouter->countryId && array_key_exists($this->urlRouter->countryId, $this->urlRouter->countries)) {
            $country_code = '-'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
        }

        $feed->setChannelElement('language', $this->urlRouter->siteLanguage.$country_code);
        $feed->setChannelElement('pubDate', date(DATE_RSS, time()));
        return $feed;
    }
    
    function footerMobile(){
        if( ( (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1) ||
              (isset($this->user->params['mobile_android_app_bottom_banner']) && $this->user->params['mobile_android_app_bottom_banner']==1) ))
        {
            ?> <!--googleoff: index --> <?php
                        
            ?><br /><ul class="ls"><li class="app_li"><?php 
            if (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1){
                            
                ?><a href="https://itunes.apple.com/us/app/mourjan-mrjan/id876330682?ls=1&mt=8"><p><?php                         
                ?><span class="tha"></span><?php 
                echo $this->lang['download_ios_app'];
                ?></p></a><?php
                            
            }else{
                            
                ?><a href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><p><?php                         
                ?><span class="tha"></span><?php 
                echo $this->lang['download_android_app'];
                ?></p></a><?php
                            
            }
            ?></li></ul><?php 
            ?> <!--googleon: index --> <?php 
        }
        $year = date('Y');
        ?><div id="footer" class="copy"><span>© 2010-<?= $year ?> Mourjan.com Classifieds Aggregator</span><span class="sep"> - </span><span>All Rights Reserved</span></div><?php 
    }

    function _body(){
        if ($this->isMobile) {
            //echo '<div id="apper"><div id="scroller">';
            $this->topMobile();
            $this->bodyMobile();
            if (!$this->urlRouter->isApp) 
                $this->footerMobile();
            $this->loadMobileJs_classic();
            //echo "</div></div>";
            //echo "<div id='adft'>Featured ad placement</div>";
        } else {
            //echo '<!--googleoff: snippet-->';
            $this->top();
            /*if ($this->urlRouter->cfg['enabled_sharing'] && (!$this->urlRouter->userId || ($this->urlRouter->userId && 
                    $this->urlRouter->module!='detail')) ) {
                $fl='fls';
                $fr='frs';
                if ($this->urlRouter->siteLaguage='ar'){
                    $fl='frs';
                    $fr='fls';
                }
                ?><div class="shh"><?php
            /*?><div class="shf w"><div class="addthis_toolbox addthis_default_style"><a class="addthis_button_google_plusone" g:plusone:count="false" g:plusone:size="medium"></a><a class="addthis_button_facebook_like" fb:like:layout="button_count"></a><a class="addthis_button_facebook_send"></a><a class="addthis_button_tweet" tw:lang="en" tw:count="none"></a><a class="addthis_button_linkedin_counter"></a><a class="addthis_counter addthis_pill_style"></a></div><div class="addthis_toolbox addthis_32x32_style addthis_default_style"><?= ($this->urlRouter->siteLanguage=='ar' ? '<p>'.$this->lang['followUs'].'</p>':'')  ?><a class="addthis_button_twitter_follow" addthis:userid="MourjanWeb"></a><a class="addthis_button_facebook_follow" addthis:userid="bmourjan"></a><?= ($this->urlRouter->siteLanguage=='en' ? '<p>'.$this->lang['followUs'].'</p>':'')  ?></div></div><?php */
          /*      ?><div class="shf w"><?php
                    ?><div class="<?= $fl ?>"><?php 
                           ?><label><?= $this->lang['shareUs'] ?></label><span class='st_plusone_hcount'></span><span class='st_facebook_hcount'></span><span class='st_twitter_hcount'></span><span class='st_linkedin_hcount'></span><span class='st_email_hcount'></span><span class='st_sharethis_hcount'></span><?php
                    
                    ?></div><?php
                    if (!$this->urlRouter->userId){
                        ?><div class="<?= $fr ?>"><?php
                            ?><label><?= $this->lang['followUs'] ?></label><a href="https://www.facebook.com/pages/Mourjan/318337638191015" target="_blank"><span class="fb-link"></span></a><a href="https://twitter.com/MourjanWeb" target="blank"><span class="tw-link"></span></a><?php
                        ?></div><?php
                    }
                ?></div><?php 
                //<a><span class="gp-link"></span></a><a><span class="yt-link"></span></a>https://www.facebook.com/bmourjan
        ?></div><?php 
            }else {
                ?><br /><?php
            }
           * */
            
            //    echo $this->fill_ad('zone_1', 'ad_w');
            
            if (!$this->urlRouter->userId) 
            {
            	include_once dirname( $this->urlRouter->cfg['dir'] ) . '/tmp/gen/' . $this->includeHash.'0.php';  
                $this->search_bar();
            }

            //echo '<!--googleon: snippet-->';
            $this->body();
            $this->load_js_classic();
            
        }
        ?></body></html><?php
    }
    
    function partnerHeader(){
        $rc=$this->urlRouter->module=='detail'?'':' rct';
        $isOwner=  ($this->urlRouter->userId==$this->user->info['id'] && $this->urlRouter->module!='detail' && !$this->pagePreview);
        $isOwner=0;
        if($isOwner) {
            $this->inlineScript.='
                var lj=document.createElement("script");
                lj.type="text/javascript";
                lj.async=true;
                lj.src="'.$this->urlRouter->cfg['url_jquery'].'jquery.colorbox.js";
                lj.onload=lj.onreadystatechange=function(){
                    if(!dLj&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){
                        dLj=true;
                        initCBX();
                    }
                };
                s.appendChild(lj);
                $("#picBF, #picLF").change(function(){
                    var e=$(this);
                    var v=e.attr("value");
                    var p=e.parent().parent();
                    var id=p.attr("id");
                    curForm[id]=p;
                    resP(e.parent().prev(),id);
                    if (v){
                        var i=v.lastIndexOf("\\\");
                        if (i>0) {v=v.substr(i+1)}
                        else {
                            i=v.lastIndexOf("/");
                            if (i>0) v=v.substr(i+1)
                        };
                        e.next().removeClass("unv")
                    }else {
                        e.next().addClass("unv")
                    };
                });
                $("#picBS, #picLS").click(function(){
                    var e=$(this);
                    if (cSize('.$this->urlRouter->cfg['max_upload'].',e.prev())){
                        var p =e.parent().prev();
                        p.html("'.$this->lang['upload_wait'].'");
                        p.addClass("loading");
                        e.addClass("unv");
                        return true;
                    }
                    return false
                });
           ';
            /*
             
                    $(".elnk.lglk").colorbox({
                        inline:true,
                        title:"'.(isset($this->partnerInfo['logo'][0]) ? $this->lang['editLogo']:$this->lang['addLogo']).'",
                        innerWidth:430,
                        innerHeight:130
                    });
             
                var gdel=function(e,path){
                    if(confirm("'.$this->lang['askDelImage'].'")){
                        $.ajax({
                            type:"POST",
                            url:path,
                            dataType:"json",
                            success:function(rp){
                                var d,e;
                                if(path=="/ajax-gdel/"){  
                                    d=$(".logo");
                                    d.html("<div class=\'dlogo\'></div><div class=\'ebx rcb\'><a class=\'elnk lglk\' href=\'#uploadLogo\'><span class=\'ek\'></span>'.$this->lang['addLogo'].'</a></div>");
                                }else {
                                    d=$(".tbn");
                                    d.attr("id","uTX");
                                    d.html("<div class=\'ebx bxmin rcb\'><a class=\'elnk banlk\' href=\'#uploadBanner\'><span class=\'ek\'></span>'.$this->lang['addBanner'].'</a></div>");
                                    d.append(e);
                                }
                                initCBX();
                            },
                            error:function(){}
                        });
                    }
                };
             
                var uploadCallback=function(fn,fp,field,w,h){
                    var f=curForm[field];
                    var n=$(".lnb",f);
                    n.removeClass("loading");
                    if(fn) {
                        f[0].reset();
                        var d;
                        if(field=="picBU"){
                            d=$(".tbn");
                            d.attr("id","");
                            d.html("<img width=\'"+w+"px\' height=\'"+h+"px\' src=\'"+fp+"\' /><div class=\'ebx bxmax rcb\'><a class=\'elnk banlk\' href=\'#uploadBanner\'><span class=\'ek\'></span>'.$this->lang['editBanner'].'</a><span class=\'sep\'>|</span><span class=\'lnk elnk\' onclick=\'gdel(this,\"/ajax-bdel/\")\'><span class=\'ek dk\'></span>'.$this->lang['delImage'].'</span></div>");
                        }else{
                            d=$(".logo");
                            d.html("<img width=\'"+w+"px\' height=\'"+h+"px\' src=\'"+fp+"\' /><div class=\'e2bx ebx rcb\'><a class=\'elnk lglk\' href=\'#uploadLogo\'><span class=\'ek\'></span>'.$this->lang['editLogo'].'</a><span class=\'sep\'></span><span class=\'lnk elnk\' onclick=\'gdel(this,\"/ajax-gdel/\")\'><span class=\'ek dk\'></span>'.$this->lang['delImage'].'</span></div>");
                        }
                        resP(n,f.attr("id"));
                        $.colorbox.close();
                        initCBX();
                    }else {
                        n.html("'.$this->lang['upload_fail'].'");
                        n.addClass("notice err");
                    }
                };
             */
            $this->globalScript.='
                var curForm=[];
                var initCBX=function(){                
                    $(".elnk.banlk").colorbox({
                        inline:true,
                        title:"'.(isset($this->partnerInfo['banner'][0]) ? $this->lang['editBanner']:$this->lang['addBanner']).'",
                        innerWidth:430,
                        innerHeight:130
                    });
                };
                var gdel=function(e,path){
                    if(confirm("'.$this->lang['askDelImage'].'")){
                        $.ajax({
                            type:"POST",
                            url:path,
                            dataType:"json",
                            success:function(rp){
                                var d,e;
                                    d=$(".tbn");
                                    d.attr("id","uTX");
                                    d.html("<div class=\'ebx bxmin rcb\'><a class=\'elnk banlk\' href=\'#uploadBanner\'><span class=\'ek\'></span>'.$this->lang['addBanner'].'</a></div>");
                                    d.append(e);
                                initCBX();
                            },
                            error:function(){}
                        });
                    }
                };
                var resP=function(p,id){
                    p.removeClass("notice err");
                    p.html(id=="picBU"?"'.$this->lang['bannerHint'].'":"'.$this->lang['logoHint'].'");
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
                var uploadCallback=function(fn,fp,field,w,h){
                    var f=curForm[field];
                    var n=$(".lnb",f);
                    n.removeClass("loading");
                    if(fn) {
                        f[0].reset();
                        var d;
                        if(field=="picBU"){
                            d=$(".tbn");
                            d.attr("id","");
                            d.html("<img width=\'"+w+"px\' height=\'"+h+"px\' src=\'"+fp+"\' /><div class=\'ebx bxmax rcb\'><a class=\'elnk banlk\' href=\'#uploadBanner\'><span class=\'ek\'></span>'.$this->lang['editBanner'].'</a><span class=\'sep\'>|</span><span class=\'lnk elnk\' onclick=\'gdel(this,\"/ajax-bdel/\")\'><span class=\'ek dk\'></span>'.$this->lang['delImage'].'</span></div>");
                        }
                        resP(n,f.attr("id"));
                        $.colorbox.close();
                        initCBX();
                    }else {
                        n.html("'.$this->lang['upload_fail'].'");
                        n.addClass("notice err");
                    }
                };
            ';
        }
        $editPageLink='/page/'.($this->urlRouter->siteLanguage == 'ar' ? '':'en/');
        ?><div class='w'><?php
            ?><div class="tbn"<?php
                if (isset($this->partnerInfo['banner'][0])){
                    ?>><img src="<?= $this->urlRouter->cfg['url_ad_img'] ?>/usr/banner/<?= $this->partnerInfo['banner'][0] ?>" width="<?= $this->partnerInfo['banner'][1] ?>" height="<?= $this->partnerInfo['banner'][2] ?>" /><?php
                }else {
                    ?> id="uTX"><?php
                }
            ?></div><?php
        ?></div><?php
        /* ?><div class="w"><div id="menu" class="menu sh<?= $rc ?>"><div class="row<?= $rc ?>"><?php 
                    ?><h1><?= $this->title ?></h1><?= ($isOwner ? ' <a class="elnk" href="'.$editPageLink.'?edit=title#title"><span class="ek"></span>'.$this->lang['editPageTitle'].'</a>':'') ?><?php 
                    $socialString='';
                    if (isset($this->partnerInfo['links']) && is_array($this->partnerInfo['links'])) {

                        foreach ($this->partnerInfo['links'] as $id => $value){
                            if ($id && $value){
                                switch($id){
                                    case 'fb':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://www.facebook.com/".$value."'><span class='fb-link'></span></a>";
                                        break;
                                    case 'tw':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://twitter.com/".$value."'><span class='tw-link'></span></a>";
                                        break;
                                    case 'gp':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://plus.google.com/".$value."'><span class='gp-link'></span></a>";
                                        break;
                                    case 'lk':
                                        //if ($socialString) $socialString.=' - ';
                                        $socialString.="<a target='blank' href='http://www.linkedin.com/in/".$value."'><span class='lk-link'></span></a>";
                                        break;                                        
                                }
                            }
                        }
                        if ($socialString) {                            
                            $res='<div class="pis">';
                            if($isOwner) $res.='<a class="elnk" href="'.$editPageLink.'?edit=links#links"><span class="ek"></span>'.$this->lang['editSocialLinks'].'</a>';
                            $res.='<label>'.$this->lang['followUs'].'</label>'.$socialString."</div>";
                            $socialString=$res;
                        }elseif($isOwner) $socialString='<div class="pis"><a class="elnk" href="'.$editPageLink.'?edit=links#links"><span class="ek"></span>'.$this->lang['addSocialLinks'].'</a></div>';
                        
                        //if ($socialString) $socialString=  htmlentities ($socialString, ENT_QUOTES);
                    }elseif($isOwner) $socialString='<div class="pis"><a class="elnk" href="'.$editPageLink.'?edit=links#links"><span class="ek"></span>'.$this->lang['addSocialLinks'].'</a></div>';
                    echo $socialString;
                ?></div><?php 
                echo '<div class="tbn"';
                if (isset($this->partnerInfo['banner'][0])){
                    echo '><img src="'.$this->urlRouter->cfg['url_ad_img'].'/usr/banner/'.$this->partnerInfo['banner'][0].'" width="'.$this->partnerInfo['banner'][1].'px" height="'.$this->partnerInfo['banner'][2].'px" />';
                }else {
                    echo ' id="uTX">';
                }
                if ($isOwner) {
                    if (isset($this->partnerInfo['banner'][0]))
                        echo '<div class="ebx bxmax rcb"><a class="elnk banlk" href="#uploadBanner"><span class="ek"></span>'.$this->lang['editBanner'].'</a><span class="sep">|</span><span class="lnk elnk" onclick="gdel(this,\'/ajax-bdel/\')"><span class="ek dk"></span>'.$this->lang['delImage'].'</span></div>';
                    else     
                        echo '<div class="ebx bxmin rcb"><a class="elnk banlk" href="#uploadBanner"><span class="ek"></span>'.$this->lang['addBanner'].'</a></div>';
                }
                
                echo '</div>';
                if ($isOwner) {
                    ?><div class="hid"><div class="uForm" id="uploadBanner"><?php
                        ?><form target="uploadifb" id="picBU" action="/ajax-banner/" enctype="multipart/form-data" method="post"><?php 
                            ?><input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->urlRouter->cfg['max_upload'] ?>" /><?php 
                           ?><h2><?= $this->lang['choosePicture'] ?></h2><?php
                                ?><p class="lnb"><?= $this->lang['bannerHint'] ?></p><?php
                                ?><p class="frm rc"><?php 
                                    ?><input id="picBF" name="pic" class="rc en" type="file" value="" /><?php 
                                    ?><input id="picBS" class="bt rc unv" type="submit" value="<?= $this->lang['upload'] ?>" /><?php
                                ?></p><?php 
                        ?></form><iframe class="hid" id="uploadifb" name="uploadifb" src="/web/blank.html"></iframe><?php 
                    ?></div></div><?php  
                    
                    ?><div class="hid"><div class="uForm" id="uploadLogo"><?php
                        ?><form target="uploadif" id="picLU" action="/ajax-logo/" enctype="multipart/form-data" method="post"><?php 
                            ?><input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->urlRouter->cfg['max_upload'] ?>" /><?php 
                                ?><h2><?= $this->lang['choosePicture'] ?></h2><?php
                                ?><p class="lnb"><?= $this->lang['logoHint'] ?></p><?php
                                ?><p class="frm rc"><?php 
                                    ?><input id="picLF" name="pic" class="rc en" type="file" value="" /><?php 
                                    ?><input id="picLS" class="bt rc unv" type="submit" value="<?= $this->lang['upload'] ?>" /><?php
                                ?></p><?php 
                        ?></form><iframe class="hid" id="uploadif" name="uploadif" src="/web/blank.html"></iframe><?php 
                    ?></div></div><?php                       
                }
                
                ?></div></div><?php */
                
                
    }
    

}

?>
