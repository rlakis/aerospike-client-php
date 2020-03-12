<?php
\Config::instance()->incLayoutFile('Site');

use Core\Model\Classifieds;

class Page extends Site {
    const SearchEngineLegitimateEntries = 21;
        
    protected $action='';
    protected array $requires=['js'=>[], 'css'=>[]];
    protected string $title='', $description='';
    public bool $isUserMobileVerified = false;
    public array $stat;
    var $pageUri = '';
    var $googleAds=array();
    var $fieldNameIndex;
    var $deprecated=false,$topMenuIE=false;    
    var $appendLocation=true;
    var $hasLeadingPane=false;
    var $png_fix=false,$renderAdSense=0;
    var $hasCities=false; 
    var $notifications=array();
    var $blocks=array();
    var $partnerInfo=null,$hasPartnerInfo=false,$pagePreview=false, $userFavorites=false;
    public $searchResults=false;
    var $extended=null,$extendedId=0,$localities=null,$parentLocalities=null,$cityParentLocalityId=0,$localityId=0,$localityParentId=0,$extended_uri='';
    var $isMobileCssLegacy=true;            
    var $countryCounter='',$inlineScript='',$inlineQueryScript='',$globalScript='',$cssImgsLoaded=false, $inlineCss='';
    var $isNewMobile=false,$requireLogin=false,$forceNoIndex=false,$isAdminSearch=0;
    var $cityName='',$rootName='', $countryName='', $categoryName='', $sectionName='', $purposeName='',$backLink='';
    
    public Core\Model\Ad $detailAd;
    public bool $detailAdExpired=false;
    var $pageItemScope='itemscope itemtype="https://schema.org/WebPage"';
    
    public string $name;
    
    private array $included = [];
    
    function __construct() {
        parent::__construct(); 
        $this->name='name_'.$this->router->language;
        if ($this->user()->id()) {
            if ($this->user()->level()===9 && $this->user()->id()!==1 && $this->user()->id()!==2) {
                $this->isUserMobileVerified = true;
            }
            else {
                $this->isUserMobileVerified = (isset($this->user->info['verified']) && $this->user->info['verified']);
            }
        }
        
        //$this->user()->sysAuthById(1932896);
                
        $cdn = $this->router->config->assetsURL;        
        if ($this->router->module=='myads' || $this->router->module=='post') {
            $this->router->config->adImgURL = 'https://www.mourjan.com';
        }
        
        if (strpos($this->router->config->imgURL, 'http')===false) {            
            $this->router->config->imgURL = $cdn.$this->router->config->imgURL;
        }
        
        if (strpos($this->router->config->jsURL, 'http')===false) {
            $this->router->config->jsURL = $cdn.$this->router->config->jsURL;
        }
        
        if (strpos($this->router->config->get('url_js_mobile'), 'http')===false) {
            $this->router->config->setValue('url_js_mobile', $cdn.$this->router->config->get('url_js_mobile'));
        }
        
        if (strpos($this->router->config->cssURL, 'http')===false) {
            $this->router->config->cssURL = $cdn.$this->router->config->cssURL;
        }
        
        if (strpos($this->router->config->get('url_css_mobile'), 'http')===false) {
            $this->router->config->setValue('url_css_mobile', $cdn.$this->router->config->get('url_css_mobile'));
        }
        
        if (strpos($this->router->config->imgLibURL, 'http')===false) {
            $this->router->config->imgLibURL = $cdn.$this->router->config->imgLibURL;
        }
        
        if (strpos($this->router->config->get('url_highcharts'), 'http')===false) {
            $this->router->config->setValue('url_highcharts', $cdn.$this->router->config->get('url_highcharts'));
        }        
        
        if (!$this->router->config->enabledUsers()) {
            if ($this->router->isArabic()) {
                $this->setNotification('مرجان يواجه بعض المشاكل التقنية والتي يتم معالجتها حالياً. شكراً لتحليكم بالصبر.');
            }
            else {
                $this->setNotification('mourjan is facing a technical problem which we are working to resolve. Thank you for being patient.');
            }
        }
        
        if(isset($this->user->params['hasCanvas']) && $this->user->params['hasCanvas']==0){
            $this->router->cfg['enabled_charts']=0;
        }
        
        
        if ($this->router->isMobile) {
            $this->includeCssByCountry();
            if($this->router->module=='signin'){
                $this->inlineCss.='.g-recaptcha{display:inline-block;min-height:78px}li.recap{text-align:center}';
            }
        }        

        if (!in_array($this->router->countryId, $this->router->config->get('iso_countries'))) {
            $this->router->countryId=0;
            $this->router->cityId=0;
            if ($this->router->module!='index') { $this->forceNoIndex=1; }
        }
        
        if ($this->router->module=='watchlist'||$this->router->module=='favorites'){
            if ($this->user->info['id']) {
                $this->pageUserId = $this->user->info['id'];
            }
            elseif($tmp==$this->get('u')){
                $this->pageUserId = $this->user->decodeId($tmp);
            }
        }
        
        if ($this->router->siteTranslate) {
            if (in_array($this->router->siteTranslate, array('ar','en'))) {
                $this->router->language=$this->router->siteTranslate;
            }
        }
        
        if ($this->router->module!='post' && isset($this->user->pending['post'])) {
            unset($this->user->pending['post']);
        }

        $this->load_lang(['main']);
        $this->title = $this->router->pageTitle[$this->router->language];
        if (!$this->title) $this->title = $this->lang['title_full'];        
        
        $this->fieldNameIndex=1+$this->lnIndex;

        $this->checkUserData();
        if ($this->router->isMobile)  {
            $this->set_require('css', array('main'));
            //$this->set_require('css', array('mob'));
            $this->isMobileAd=true;
            $this->isMobile=true;
            $this->appendLang=$this->router->getLanguagePath();                    
        }
        else {   
            $width = 0;
            if (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) {
                $width = $this->user->params['screen'][0];
            }
            
            if ($this->router->module!='myads' && $this->router->module!='post'){                 
                if ($width >= 1250) {
                    $this->inlineCss.='@media all and (max-width:1249px){.w970{display:none}}';
                    $this->set_ad(array('zone_0'=>array('/1006833/Leaderboard-970', 970, 90, 'div-gpt-ad-1497965856593-0-'.$this->router->config->serverId)));
                }
                else {
                    $this->set_ad(array('zone_0'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0-'.$this->router->config->serverId)));
                }
            }

            $this->set_require('css', array('gen'));
        }
               
        if ($this->user->info['id'] && $this->router->module!='account' && $this->user->info['app-user']==0){
            if (!$this->user->info['email']) {
                if ((isset($this->user->info['options']['email']) && isset($this->user->info['options']['emailKey']) )) {
                    $this->setNotification(preg_replace('/{email}/', $this->user->info['options']['email'], $this->lang['validateEmail']));                    
                }else {
                    $this->setNotification($this->lang['requireEmail']);
                }
            }
        }
        
        if($this->router->userId && $this->router->userId==$this->user->info['id']  && $this->router->module!='detail'){
            $this->setNotification($this->lang['specialNB']);
        }
        else {
            if(isset($_GET['signin']) && $_GET['signin']=='error'){
                $this->setNotification($this->lang['failedLogin']);
            }
            elseif(isset($this->user->params['browser_alert']) && $this->user->params['browser_alert']){
                $this->setNotification(preg_replace('/{chrome_link}/', $this->user->params['browser_link'], $this->lang['browserUpdate']));
            }
        }

        if ($this->router->watchId || $this->router->userId || ($this->router->module!='index' && $this->router->module!='detail' && $this->router->module!='search')){
            if (isset($this->user->params['country']) && $this->user->params['country']) {
                $this->router->countryId=$this->user->params['country'];
            }
            if (isset($this->user->params['city']) && $this->user->params['city']) {
                $this->router->cityId=$this->user->params['city'];
            }
        }

        if ($this->router->module=='favorites') {
            $this->userFavorites=true;
            $this->lang['description']=$this->lang['home_description'];
            $this->router->module='search';
        }
        
        if ($this->router->module=='watchlist') {
            $this->router->module='search';
            $this->router->watchId=$this->pageUserId ? $this->pageUserId : -1;
        }

        if (!$this->router->isMobile) {
            $match=null;
            if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && preg_match('/(MSIE 6|MSIE 7)/', $_SERVER['HTTP_USER_AGENT'], $match)) {
                $version=((int)substr($match[0], -1));
                if ($version<7) {
                    $this->router->cfg['enabled_disqus']=false;
                }
            }
        }        

        if ($this->router->module==='detail') {            
            $this->detailAd=$this->classifieds->getAd($this->router->id);
            if ($this->detailAd->id()>0) {
                if ($this->detailAd->hasAltContent()) {                    
                    if ($this->router->language==='en' && $this->detailAd->rtl()) {
                        $this->detailAd[Classifieds::TITLE]=$this->detailAd[Classifieds::ALT_TITLE];
                        $this->detailAd[Classifieds::CONTENT]=$this->detailAd[Classifieds::ALT_CONTENT];
                        $this->detailAd->setLTR();                        
                        $this->appendLocation=false;
                    } 
                    elseif ($this->router->language==='ar' && !$this->detailAd->rtl()) {
                        $this->detailAd[Classifieds::TITLE] = $this->detailAd[Classifieds::ALT_TITLE];
                        $this->detailAd[Classifieds::CONTENT] = $this->detailAd[Classifieds::ALT_CONTENT];
                        $this->detailAd->setRTL();
                        $this->appendLocation=false;
                    }
                }
                $this->router->cityId=$this->detailAd->cityId();
                $this->router->countryId=$this->detailAd->countryId();
                $this->router->rootId=$this->detailAd->rootId();
                $this->router->sectionId=$this->detailAd->sectionId();
                $this->router->purposeId=$this->detailAd->purposeId();
                $this->lang['description']=\preg_replace('/"/', '', $this->detailAd->text());
                
                if ($this->detailAd->expired()) $this->detailAdExpired=true;
                
            }
            else {
                $this->detailAdExpired=true;
            }

            if (isset($this->user->params['search']['cn']) && $this->router->internal_referer && !isset($_GET['ref'])) {
                if(isset($this->user->params['search']['uId']))$this->router->userId=$this->user->params['search']['uId'];
                if(isset($this->user->params['search']['wId']))$this->router->watchId=$this->user->params['search']['wId'];
                $this->router->countryId=$this->user->params['search']['cn'];
                $this->router->cityId=$this->user->params['search']['c'];
                $this->router->rootId=$this->user->params['search']['ro'];
                $this->router->sectionId=$this->user->params['search']['se'];
                $this->router->purposeId=$this->user->params['search']['pu'];
                $this->router->params['q']=$this->user->params['search']['q'];
                if (array_key_exists('exId', $this->user->params['search']))
                    $this->router->params['tag_id']=$this->user->params['search']['exId'];
                if (array_key_exists('locId', $this->user->params['search']))
                $this->router->params['loc_id']=$this->user->params['search']['locId'];
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
                $this->router->params['start']=$this->user->params['search']['start'];

            }
            $this->router->cacheExtension();
        } 
        elseif ($this->router->module!=='search') {
            $last_query_time=isset($this->user->params['search']['time']) ? $this->user->params['search']['time'] : 0;
            unset ($this->user->params['search']);
            $this->user->params['search']=['time'=>$last_query_time];
        }
        
        if ($this->router->module==='search') {
            if(isset($this->user->params['last_root']) && $this->user->params['last_root'] != $this->router->rootId){
                $this->user->params['list_publisher'] = 0;
                $this->publisherTypeSorting = 0;
            }
            $this->user->params['last_root'] = $this->router->rootId;
            $this->user->update();
            
            if($this->publisherTypeSorting){
                $this->forceNoIndex = 1;
            }
        }

        if ($this->router->countryId) {
            $this->countryName=$this->router->countries[$this->router->countryId]['name']; 
            if (\count($this->router->countries[$this->router->countryId]['cities'])>0) {
                $this->hasCities=true;
                if ($this->router->cityId && isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])){
                    $this->cityName = $this->router->countries[$this->router->countryId]['cities'][$this->router->cityId]['name'];
                    //$this->cityName=$this->router->cities[$this->router->cityId][$this->fieldNameIndex];
                }
                else {
                    $this->router->cityId=0;
                }
            }
            else {
                $this->router->cityId=0;
            }
            if ($this->router->cityId) {
                $this->countryCounter=$this->router->isArabic() ? number_format($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId]['counter']) :$this->router->countries[$this->router->countryId]['cities'][$this->router->cityId]['counter'];
            }
            else {
                $this->countryCounter=$this->router->isArabic() ? number_format($this->router->countries[$this->router->countryId]['counter']) :$this->router->countries[$this->router->countryId]['counter'];
            }
        }
        else {
            if (!$this->router->isMobile) {
                $this->countryName=$this->lang['opt_all_countries'];
                $counts=0;
                foreach ($this->router->countries as $country) {
                    $counts+=$country['counter'];
                }
                $this->countryCounter = $this->router->isArabic() ? number_format($counts) : $counts;
            }
        }
        $this->user->params['country']=$this->router->countryId;
        if ($this->hasCities) $this->user->params['city']=$this->router->cityId;
        else $this->user->params['city']=0;

        if ($this->countryCounter) {
            if ($this->router->isArabic()) {
                $this->countryCounter.=' '.$this->lang['ads'];
            }
            else {
                $this->countryCounter=$this->formatPlural($this->countryCounter, 'ad');
            }
        }
        $lang='en';
        if ($this->router->siteTranslate) {
            if ($this->router->siteTranslate=='ar') $lang='ar';
        }
        else {
            $lang=$this->router->language;
        }
        if(!$this->router->isMobile) {
            $cntLink='<b>'.($this->router->cityId ? $this->router->countries[$this->router->countryId]['cities'][$this->router->cityId]['name'] : ($this->router->countryId ? $this->router->countries[$this->router->countryId]['name']:'') ).'</b><span class="cf c'.($this->router->countryId).'"></span><b>'.$this->countryCounter.'</b>';
            $this->countryCounter=$cntLink;
        }
        
        $this->includeHash=($this->router->countryId?$this->router->countries[$this->router->countryId]['uri']:'zz').'-'.$lang.'-'.($this->router->countryId?$this->router->cityId:'0').'-';
        if ($this->router->params['start'] && $this->router->params['start']>100) {
            $this->router->params['start']=0;
            $this->forceNoIndex=true;
        }
        if ($this->router->params['q']) {
            $this->router->params['q'] = \preg_replace('/\//','', $this->router->params['q']);
            $this->router->params['q'] = \trim(\preg_replace('/\s+/',' ', $this->router->params['q']));
        }

        if (in_array($this->user->info['id'],array(1,2,2100,38813,44835,53456))) {
            $this->router->cfg['enabled_sharing']=false;
            $this->router->config->disableAds();
        }
        
        if ($this->router->params['q']) {
            $query = \trim($this->router->params['q']);
            if($query === 'مساج'){
                $this->router->config->disableAds();
            }
        }
        
        if (!$this->router->isMobile) { $this->router->cfg['enabled_sharing']=false; }
        
        
        $this->user->update();
    }
    
    
    function renderBalanceBar() {        
        if ($this->user()->id()) {            
            echo '<div id=balance class=balc>',       
                    '<a style="display:inherit;background-color:var(--premium);width:80%;padding:8px;margin:-20px auto 20px auto;border-bottom-left-radius:0;border-bottom-right-radius:0;" class=btn href="', $this->router->getLanguagePath('/gold/'), '#how-to">', 
                    '<span style="font-size:2em;text-transform:none;">Current balance is ', $this->user()->getBalance(),' coins</span><br><b>',
                    $this->lang['buy_gold_bt'], '</b></a></div>';
        }
    }
    
    
    function renderLoginBox() {
        $lang='';
        if($this->router->language=='en')$lang='en/';
        if($this->user->info['id']){  
            ?><div class='lgb <?= $this->router->module != 'premium' ? $this->router->module : '' ?>'><?php
                ?><ul class="hoz"><?php
                    ?><li><a href="?logout=<?= $this->user->info['provider'] ?>"><span class="j out"></span><?= $this->lang['signout'] ?></a></li><?php
                    if ($this->router->module=='home') {
                        ?><li class="on"><span class="j home"></span></li><?php
                    }else{
                        ?><li><a href="/home/<?= $lang ?>"><span class="j home"></span><?= $this->lang['myPanel'] ?></a></li><?php
                    }
                    if ($this->userFavorites) {
                        ?><li class="on"><span class="j fva"></span></li><?php
                    }else{
                        ?><li><a href="/favorites/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>"><span class="j fva"></span><?= $this->lang['myFavorites'] ?></a></li><?php
                    }
                    if ($this->router->watchId) {
                        ?><li class="on"><span class="j eye"></span></li><?php
                    }else{
                        ?><li><a href="/watchlist/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>"><span class="j eye"></span><?= $this->lang['myList'] ?></a></li><?php
                    }
                    if ($this->router->module=='myads') {
                        ?><li class="on"><span class="j ads"></span></li><?php
                    }else{
                        ?><li><a href="/myads/<?= $lang ?>"><span class="j ads"></span><?= $this->lang['myAds'] ?></a></li><?php
                    }
                    if ($this->router->module=='post') {
                        ?><li class="on"><span class="j pub"></span></li><?php
                    }else{
                        ?><li><a href="/post/<?= $lang ?>"><span class="j pub"></span><?= $this->lang['button_ad_post_m'] ?></a></li><?php
                    }
                ?></ul><?php
            ?></div><?php
        }elseif( (!$this->userFavorites && !$this->router->watchId && !in_array ($this->router->module,array('myads','account','post','profile','signin','password'))) 
                || ($this->pageUserId)){
            ?><div class='lgb <?= $this->router->module ?>'><?php
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

    
    function checkBlockedAccount(int $level=0) : void {
        if ($this->user()->isLoggedIn()) {
            if ((!$level || ($level && $level==5)) && $this->user()->level()==5) {
                $this->user->redirectTo('/blocked/'.($this->router->isArabic()?'':$this->router->language.'/'));
            }
            elseif((!$level || ($level && $level==6)) && $this->user->info['level']==6) {
                $this->user->redirectTo('/suspended/'.($this->router->language=='ar'?'':$this->router->language.'/'));
            }
        }
    }
    
    
    function checkSuspendedAccount() {
        $isSuspended = $this->user->getProfile() ? $this->user->getProfile()->isSuspended() : FALSE;
        if ($isSuspended) { 
            $this->user->redirectTo('/held/'.($this->router->language=='ar'?'':$this->router->language.'/'));
        }
    }

    
    function checkUserData() {
        if ($this->user->info['id']) {
            $this->user->loadFavorites();
        }
    }

    
    function renderLoginPage() : void {        
        \Config::instance()->incLibFile('phpqrcode');
        if (!isset($this->included['doc'])) {
            echo '<style>';
            $this->css('doc');
            echo '</style>';
        }
        echo '<div class="row viewable"><div class="col-12 sign">';
        $keepme_in = (isset($this->user->params['keepme_in']) && $this->user->params['keepme_in']==0)?0:1;
        
        if (isset($this->user->pending['login_attempt'])) {
            if ($this->router->isArabic()) {
                ?><style type="text/css">.lgs .br{margin-top:20px}</style><?php
            }
            else {
                ?><style type="text/css">.lgs .br{margin-top:32px}</style><?php
            }
        }
        
        $qrfile = \dirname( $this->router->config->get('dir')).'/tmp/qr/'.\session_id().'.png';
        QRcode::png('mourjan:login:'.\session_id().\str_pad($this->router->config->serverId, 4, '0', \STR_PAD_LEFT) . \str_pad(time(),16, '0', \STR_PAD_LEFT), $qrfile, QR_ECLEVEL_L, 5);

        $sh = '0';
        $mu = filter_input(INPUT_COOKIE, 'mourjan_user', FILTER_DEFAULT);
        if ($mu) {            
            $cook = json_decode($mu);            
            if (is_object($cook) && isset($cook->mu)) {
                $sh='1';
            }
        }

        $redis = new Redis();
        $redis->connect($this->router->config->get('rs-host'), $this->router->config->get('rs-port'), 1, NULL, 100); 
        $redis->setOption(Redis::OPT_PREFIX, 'SESS:');
        $redis->select(1);
        $redis->setex(session_id(), 300, $this->router->config->serverId.':'.$sh);
        $redis->close();

        $data = file_get_contents($qrfile);
        $type = pathinfo($qrfile, PATHINFO_EXTENSION);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);                
        
        ?><div class="card card-doc"><?php
        ?><div class=title><h5><?= $this->lang['signin_mourjan'] ?></h5></div><?php
        ?><form method="post" action="<?= $this->router->getLanguagePath('/a/') ?>" onsubmit="lgi(this);return false;"><?php 
        ?><div class="card-content"><?php
        ?>
            <br><div class=group><input class="en" name="u" type=email required><span class=highlight></span><span class=bar></span><label><?= $this->lang['email'] ?></label></div>
            <div class=group><input name="p" type=password required><span class=highlight></span><span class=bar></span><label><?= $this->lang['password'] ?></label></div>
            <label class=chkbox style="padding-bottom:8px"><input name=o type=checkbox <?= $keepme_in ? 'checked':'' ?>><span><?= $this->lang['keepme_in'] ?></span></label>
            <div class="g-recaptcha" data-sitekey="<?= $this->router->config->get('recap-key') ?>"></div>
            
            <?php           
            if (isset($this->user->pending['login_attempt'])) {
                ?><p class=nl><span><span class=fail></span><?= $this->lang['login_error'] ?></span></p><?php                    
            }
            elseif (isset($this->user->pending['login_attempt_captcha'])) {
                ?><p class=nl><span><span class=fail></span><?= $this->lang['login_error_captcha'] ?></span></p><?php                    
            }
            $uri = $this->router->uri;
            if (preg_match('/signin/',$this->router->uri)) {
                $uri = $this->router->getLanguagePath('/home/');
            }
            ?><div class=group style="margin:0"><input name=r type=hidden value="<?= $uri ?>" /><i class="icn icn-sign-in"></i><input type="submit" class=btn value="<?= $this->lang['signin'] ?>" /></div><?php            
          
            echo '<div class=group style="margin:0"><a class=btn href="', $this->router->getLanguagePath('/signup/'), '">', $this->lang['create_account'], '<i class="icn icn-sign-up"></i></a></div>';
        ?>
        <p><a class=lnk href="<?= $this->router->getLanguagePath('/password/') ?>"><?= $this->lang['forgot_pass'] ?></a></p>
        </div><?php                
        ?></form></div><?php
        
        ?><div class="card card-doc"><div class=title><h5><?= $this->lang['signin_m'] ?></h5></div><?php
        ?><div class=card-content><?php            
        ?><a class=btn style="background-color:#3b5998" href="/web/lib/hybridauth/?provider=facebook">Facebook<i class="icn icn-facebook"></i></a><?php
        ?><a class=btn style="background-color:#4285F4" href="/web/lib/hybridauth/?provider=google">Google<i class="icn icn-google"></i></a><?php
        ?><a class=btn style="background-color:#1da1f2" href="/web/lib/hybridauth?provider=twitter">Twitter<i class="icn icn-twitter"></i></a><?php
        ?><a class=btn style="background-color:#410093" href="/web/lib/hybridauth?provider=yahoo">Yahoo<i class="icn icn-yahoo"></i></a><?php
        ?><a class=btn style="background-color:#0075b5" href="/web/lib/hybridauth?provider=linkedin">LinkedIn<i class="icn icn-linkedin"></i></a><?php
        ?><a class=btn style="background-color:#7fba00;" href="/web/lib/hybridauth?provider=live">Windows Live<i class="icn icn-microsoft"></i></a><?php
        ?></div></div><?php
        
        
        echo '<div class="card card-doc"><div class=title><h5>Mourjan iPhone App</h5></div>';
        echo '<div class=card-content style="text-align:center; padding:36px 0">', '<img width=200 height=200 src="', $base64, '" />';
        echo '<p style="padding-top:30px;><span class="bt scan"><span class=apple></span><span class="apple up"></span> ', 
            $this->lang['hint_login_signin'],
            ' <span class="apple up"></span><span class=apple></span></span></p>';        
        echo '</div></div>';
        
        echo '</div></div>'; // close signin div
        
        ?><div class="row viewable"><div class=col-12><div class="card card-doc"><div class=card-title><h4><?= $this->lang['NB'] ?></h4></div><?php 
        ?><div class=card-content><p>&bull;&nbsp;<?=$this->lang['disclaimer']?></p><p>&bull;&nbsp;<?= $this->lang['disclaimer_social'] ?></p></div><?php
        ?></div></div></div><?php                         
                               
        $this->requireLogin=true;
           
        if (isset($this->user->pending['login_attempt'])) {
            unset($this->user->pending['login_attempt']);
            $this->user->update();
        }
        if (isset($this->user->pending['login_attempt_captcha'])) {
            unset($this->user->pending['login_attempt_captcha']);
            $this->user->update();
        }
        
        echo '</div></div>';
    }

    
    
    function renderDisabledPage() {
        ?><div class="sum rc"><div class="brd"><h1><?= $this->lang['title_not_supported'] ?></h1></div><p><?= $this->lang['hint_not_supported']; ?></p></div><div class="fake"></div><?php
    }

    
    function setNotification($note, $type='')
    {
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

    
    function renderNotificationsMobile($containerTag='p')
    {
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

    function clear_require($type){
        if(isset($this->requires[$type])){
            $this->requires[$type]=[];
        }
    }
    
    function set_require($type, $str){
        if (is_array($str)) {
            $this->requires[$type]=array_merge($this->requires[$type], $str);
        }
        else {
            $this->requires[$type][]=$str;
        }
    }
    
    
    function prepare_css(){
        $addOn='';
        $mobileDir='';
        $source=$this->router->config->cssURL;
        $sourceFile = '/home/www/css/5.4.2';
        $sourceFile = $this->router->config->cssDir.substr($source,strlen($this->router->config->assetsURL));
        if ($this->isMobile) {
            $addOn.='_m';
            $source=$this->router->cfg['url_css_mobile'];
            $sourceFile = '/home/www/css/5.2.8g';
            $sourceFile = $this->router->cfg['dir_css'].substr($source,strlen($this->router->cfg['url_resources']));
            if($this->isMobileCssLegacy){
                $this->requires['css'][]='mms';
            }
        }
        else {
            $this->requires['css'][]='imgs';
        }
        if ($this->router->siteTranslate) {
            if ($this->router->siteTranslate=='ar') $addOn.='_ar';
        }
        elseif ($this->router->language=='ar') $addOn.='_ar'; 
        $fAddon=$addOn;
        $csFile = '';
        $toRequire = [];
        foreach ($this->requires['css'] as $css) {
            if (substr($css, 0, 7)=='s_root_' || $css=='ie6' || $css=='ie7' || $css=='imgs' || $css=='mms' || $css == 'home' || $css == 'select2') $addOn='';
            else $addOn=$fAddon;
            if( ($css == 'mms' ||$css=='imgs' ) && $this->router->isAcceptWebP){
                $addOn = '_wbp';
            }
            $toRequire[] = $source . '/' . $css . $addOn . '.css';
        }
        $this->requires['css'] = $toRequire;
    }

    protected function load_css(){
        foreach ($this->requires['css'] as $css) {
            echo '<link rel=\'stylesheet\' type=\'text/css\' href=\'', $css ,'\' />';
        }
       
        $csFile = '';
        
        if ($this->inlineCss){
            $this->inlineCss= preg_replace('/\n/','',$this->inlineCss);
            $this->inlineCss= preg_replace('/\s+/',' ',$this->inlineCss);
            $this->inlineCss=preg_replace('/;}/','}',$this->inlineCss);
            $this->inlineCss=preg_replace('/ {/','{',$this->inlineCss);
            $this->inlineCss=preg_replace('/} /','}',$this->inlineCss);
            $this->inlineCss=preg_replace('/, /',',',$this->inlineCss);
            $this->inlineCss=trim($this->inlineCss);
        }else{
            $this->inlineCss='';
        }
        if($csFile || $this->inlineCss){            
            echo '<style type="text/css">',$csFile, $this->inlineCss, '</style>';
        }
       
        if (!$this->isMobile) {
            if ($this->router->isArabic()) {
                ?><!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie7_ar.css"><![endif]--><?php
                ?><!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie8_ar.css"><![endif]--><?php
                ?><!--[if IE 9]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie9_ar.css"><![endif]--><?php
            }
            else {
                ?><!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie7.css"><![endif]--><?php
                ?><!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie8.css"><![endif]--><?php
                ?><!--[if IE 9]><link rel="stylesheet" type="text/css" href="<?= $this->router->config->cssDir ?>/ie9.css"><![endif]--><?php
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
      
    }


    // wanted by robert
    function renderSideSite() : void {
        $countryId=$this->user->params['country']??0;
        $cityId=$this->user->params['city']??0;

        $lang=$this->router->language==='ar'?'':$this->router->language.'/';
        echo '<div class=title><h5>', $this->lang['mourjan'], '</h5></div>';
        
        echo '<ul class=sm>';
        echo '<li><a href=\'', $this->router->getURL($countryId, $cityId), '\'>', $this->lang['homepage'], '</a></li>';

        if ($this->router->module==='about')
            echo '<li class=on><b>', $this->lang['aboutUs'], '</b></li>';
        else
            echo '<li><a href=\'/about/', $lang, '\'>', $this->lang['aboutUs'], '</a></li>';
        if ($this->router->module==='contact')
            echo '<li class=on><b>', $this->lang['contactUs'], '</b></li>';
        else
            echo '<li><a href=\'/contact/', $lang, '\'>', $this->lang['contactUs'], '</a></li>';
        if ($this->router->module==='gold')
            echo '<li class=on><b>', $this->lang['gold_title'], '</b></li>';
        else
            echo '<li><a href=\'/gold/', $lang, '\'>', $this->lang['gold_title'], '</a></li>';
        if ($this->router->module==='privacy')
            echo '<li class=on><b>', $this->lang['privacyPolicy'], '</b></li>';
        else
            echo '<li><a href=\'/privacy/', $lang, '\'>', $this->lang['privacyPolicy'], '</a></li>';
        if ($this->router->module==='terms')
            echo '<li class=on><b>', $this->lang['termsConditions'], '</b></li>';
        else
            echo '<li><a href=\'/terms/', $lang, '\'>', $this->lang['termsConditions'], '</a></li>';
        echo "</ul>";
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
            $lang=$this->router->language=='ar'?'':$this->router->language.'/';
            ?><h4><?= $this->user->info['name'] ?></h4><?php
            echo "<ul class='sm'>";
            $renderedFirst=false;

            if ($this->router->watchId) {
                echo "<li class='on'><b><span class='eye on'></span>{$this->lang['myList']}</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li><a href='/watchlist/".$lang."'><span class='eye on'></span>{$this->lang['myList']}</a></li>";
                $renderedFirst=true;
            }
            
            if ($this->userFavorites) {
                echo "<li class='on'><b><span class='fav on'></span>{$this->lang['myFavorites']}</b></li>";
                $renderedFirst=true;
            }
            else {
                echo "<li><a href='/favorites/".$lang."'><span class='fav on'></span>{$this->lang['myFavorites']}</a></li>";
                $renderedFirst=true;
            }
            if ($this->router->module=='account'){
                echo "<li class='on'><b><span class='usr'></span>{$this->lang['myAccount']}".( (!$this->user->info['email'] || !$this->user->info['name']) ? ' <span class="anb us"></span>':'' )."</b></li>";
            }else {
                echo "<li><a href='/account/".$lang."'><span class='usr'></span>{$this->lang['myAccount']}</a></li>";
                $renderedFirst=true;
            }
            echo '</ul><h4>'.$this->lang['myAds'].'</h4><ul class="sm">';
            if ($this->router->cfg['enabled_post']) {
                $renderedFirst=true;
                $sub='';
                if (isset ($_GET['sub']) && $_GET['sub']) $sub=$_GET['sub'];
                //echo '<li class="cty"><ul>';
                echo '<li><a href="/post/'.$lang.'?clear=true">'.$this->lang['create_ad'].'</a></li>';
                if ($this->router->module=="myads" && $sub=='') echo '<li><b>'.$this->lang['ads_active'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'">'.$this->lang['ads_active'].'</a></li>';
                if ($this->router->module=="myads" && $sub=='pending') echo '<li><b>'.$this->lang['ads_pending'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=pending">'.$this->lang['ads_pending'].'</a></li>';
                if ($this->router->module=="myads" && $sub=='drafts') echo '<li><b>'.$this->lang['ads_drafts'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=drafts">'.$this->lang['ads_drafts'].'</a></li>';
                if ($this->router->module=="myads" && $sub=='archive') echo '<li><b>'.$this->lang['ads_archive'].'</b></li>';
                else echo '<li><a href="/myads/'.$lang.'?sub=archive">'.$this->lang['ads_archive'].'</a></li>';
            }
            echo '</ul>';
        }
    }

    
    function renderPageSideSections() : void {
        if ($this->userFavorites) { return; }
        if (!isset($this->router->pageRoots[$this->router->rootId])) { return; }
        if (\count($this->router->pageSections)<=1) { return; }
        $cityId=$this->router->cityId;
        $countryId=$this->router->countryId;
        switch ($this->router->module) {
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
        if ($this->router->params['q']) {
            $hasQuery=true;
            $q='?q='.urlencode($this->router->params['q']);
        }
        
        
        ?><div class=asrch><header>SECTION LIST</header><ul><?php
        
        $pId=$this->router->rootId===3?3:0;        
        foreach ($this->router->pageSections as $k=>$v) {
            $selected=$this->router->sectionId==$k?true:false;
            if ($selected && ($this->extendedId||$this->localityId)) { $selected = false; }
            $purposeId=0;
            
            if ($this->router->rootId===3) {
                $purposeId=3;
            }
            elseif (\count($v['purposes'])===1) {
                $purposeId=\array_keys($v['purposes'])[0];
            }
            elseif ($this->router->purposeId && isset($v['purposes'][$this->router->purposeId])) {
                $purposeId=$this->router->purposeId;
            }          

            if ($hasQuery) {
                ?><li><a href=<?=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section_id, $purposeId).$q?>><img style="width:25px;height:25px;margin-inline-end:8px" src="/web/css/1.0/assets/se/<?=$k?>.svg"><?=$v['name']?></a></li><?php
            }
            else {
                if ($purposeId>0 && $purposeId===$this->router->purposeId) {
                    $v['counter']=$v['purposes'][$purposeId]['counter'];
                }
                $isNew=(!$selected && $this->checkNewUserContent($v['unixtime']));
                $name=$v['name'].'&nbsp<span'.($isNew?' class=hot>':'>').\number_format($v['counter']).'</span>';
                ?><li><a href=<?=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $k, $purposeId)?>><img class=se src="/web/css/1.0/assets/se/<?=$k?>.svg"><?=$name?></a></li><?php
            }
        }
        ?></ul></div><?php

        /*
            
            if ($hasQuery) {                                                
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, 0, $pId) . $q, $this->router->sectionId == 0,'ovh'), '</li>';
                foreach ($this->router->pageSections as $section_id=>$section) {
                    
                    echo '<li>', $this->renderSubListLink($sectionClass.$section_id.'"></span>'.$section['name'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section_id, $purposeId) . $q, $selected), '</li>';
                    $i++;
                }              
            }
            else {                
                echo '<li>', $this->renderSubListLink($this->lang['opt_all_categories'], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, 0, $pId).$q, $this->router->sectionId==0, 'ovh'), '</li>';
                
                foreach ($this->router->pageSections as $section_id=>$section) {
                    
                    $isNew = (!$selected && $this->checkNewUserContent($section['unixtime']));
                    $iTmp=$sectionClass.$section_id.'"></span>';
                    echo '<li', ($isNew ? ' class="nl">' : '>'),
                    $this->renderSubListLink($iTmp.$section['name'].'&nbsp;<span class=c>('.$section['counter'].')</span>', $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $section_id, $purposeId), $selected), '</li>';
                    $i++;
                }
            }            */
    }
    
    
    function renderSideRoots() : void {
        if (!$this->userFavorites) {
            $cityId=$this->router->cityId;
            $countryId=$this->router->countryId;
            switch ($this->router->module) {
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
            if ($this->router->params['q']) {
                $hasQuery=true;
                $q='?q='.urlencode($this->router->params['q']);
            }
            echo '<div class="title"><h5>', $this->lang['specify_category'], '</h5></div>', '<ul>';
            $i=0;
            foreach ($this->router->pageRoots as $rid=>$root) {
                $selected = ($this->router->rootId==$rid);
                $purposeId = 0;
                if ($rid==3) {
                    $purposeId=3;
                } 
                else {
                    if (count($root['purposes'])==1) {
                        $purposeId = array_keys($root['purposes'])[0];
                    }
                }
                //echo '<li ', ($selected?'class="on big"':'class="big"'), '>',
                echo '<li>',
                        $this->renderListLink('<i class="icn icn-'.$rid.' fill-'.$rid.'"></i>'.$root['name'], $this->router->getURL($countryId, $cityId, $rid, 0, $purposeId).$q, $selected),
                        '</li>';
                if ($this->router->rootId==$rid) {
                    echo '<li class="sub">';
                    $this->renderSubSections();
                    echo '</li>';
                }
                $i++;
            }
            echo '</ul>';
        }
    }
    
    
    function renderSideCountries() {
        if($this->userFavorites || ($this->router->module!='index' && $this->router->countryId && !$this->hasCities)) return;
        
        $hasQuery=false;
        $q='';
        if ($this->router->params['q']) {
            $hasQuery=true;
            $q='?q='.urlencode($this->router->params['q']);
        }
        if($this->router->countryId && $this->hasCities){
            ?><h4><?= $this->lang['specify_city'] ?></h4><?php
        }else {
            ?><h4><?= $this->lang['specify_location'] ?></h4><?php
        }
        echo '<ul class=\'list\'>';
        if ($this->router->module=='index' || !$this->router->countryId){
        echo '<li class=\'f\'>', 
            $this->renderListLink(
                '<span class=\'cn c0\'></span>'.$this->lang['opt_all_countries'], 
                $this->router->getURL(0, 0, 
                                $this->router->rootId, 
                                $this->router->sectionId,
                                $this->router->purposeId, true, true).$q, 
                $this->router->countryId==0),
            '</li>';
        }
        foreach ($this->router->countries as $country) {
            if (isset($country[0])) {
            if ($this->router->module=='index' || !$this->router->countryId || $this->router->countryId == $country[0]){
            $selected = ($this->router->countryId == $country[0] && !($this->hasCities && $this->router->cityId)) ? true : false;
            echo '<li>',
                $this->renderListLink(
                    "<span class='cn c{$country[0]}'></span>".$country[$this->fieldNameIndex], 
                    $this->router->getURL($country[0], 0, 
                            $this->router->rootId,
                            $this->router->sectionId,
                            $this->router->purposeId, true, true).$q, 
                    $selected), 
                '</li>';
            }
            if ($this->router->countryId == $country[0] && $this->hasCities) {
                echo "<li class='cty'><ul>";
                foreach ($this->router->countryCities as $id=>$value) {
                    if (array_key_exists($id, $this->router->cities) && ((int)$this->router->cities[$id][4])>0) {
                        $selected = $this->router->cityId == $this->router->cities[$id][0] ? true : false;
                        echo '<li',($selected?' class=\'on\'>':'>'),
                            $this->renderListLink(
                                $this->router->cities[$id][$this->fieldNameIndex], 
                                $this->router->getURL($country[0], $this->router->cities[$id][0], 
                                        $this->router->rootId,
                                        $this->router->sectionId,
                                        $this->router->purposeId, true, true).$q, 
                                $selected), 
                            '</li>';
                    }
                }
                echo '</ul></li>';
            }
            }
        }
        
        echo '</ul>';         
    }

    
    
    function composeListLink(string $label, string $link, bool $selected=false, string $icon='') : string {
        if ($selected) {
            $result='<b>'.$label.'</b>';
        }
        else {
            if (empty($icon)) {
                $result="<a href={$link}>{$label}</a>";
            }
            else {
                $result="<a href={$link}><img class=se src={$icon}>{$label}</a>";
            }
        }
        return $result;
    }
    
    
    // deprecated
    function renderListLink(string $label, string $link, bool $selected=false, string $className='') : string {
        //$class='link'.($className?' '.$className:'');
        $result='';
        if ($selected) {
            if ($this->router->isMobile) {
                $result = $label;
            }
            else {
                $result = '<b>'.$label.'</b>';
            }
        }
        else {
            $result = "<a href='{$link}'>{$label}</a>";
        }
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
    

    function getPageUri() {
        if($this->pageUri){
            return $this->pageUri;
        }
        
        $this->field_name='NAME_'.strtoupper($this->router->language);
        
        $uri='/';
        if ($this->extendedId){
            if (isset($this->router->countries[$this->router->countryId])) {
                $uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                    $uri.=$this->router->cities[$this->router->cityId][3].'/';
                }
            }
            $uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
            if ($this->router->purposeId)$uri.=$this->router->purposes[$this->router->purposeId][3].'/';
            $uri.=($this->router->language!='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId ? ($this->hasCities && $this->router->cityId ? 3:2) :1).'/';
        }elseif($this->localityId){
            if (isset($this->router->countries[$this->router->countryId])) {
                $uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
            }
            $uri.=$this->localities[$this->localityId]['uri'].'/';
            $uri.=$this->router->sections[$this->router->sectionId]['uri'].'/';
            if ($this->router->purposeId)$uri.=$this->router->purposes[$this->router->purposeId][3].'/';
            $uri.=($this->router->language!='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId ? 3:2).'/';
        }else {
            $uri=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId);
        }
        $this->pageUri = $uri;
        return $uri;
    }
    
    
    function search_bar(){
        
        $q=$this->router->params['q'];
        $uri = $this->getPageUri();
                
        ?><div class="srch w"><?php
        ?><form onsubmit="if(document.getElementById('q').value)return true;return false" action="<?= $uri ?>" method="get"><?php 
            ?><div class='q'><?php 
                ?><input id="q" name='q' value="<?= htmlspecialchars($q,ENT_QUOTES) ?>" type='text' placeholder='<?= $this->lang['search_what'] ?>' /><?php 
                if ($this->router->params['q']) {
                    echo "<a class='qx' href='",$uri,"'></a>";
                }
            ?></div><?php 
                ?><input class='bt' type="submit" value="<?=  $this->lang['search'] ?>" /><?php 

        ?></form><?php 



        if($this->router->module=='myads' && $this->user->info['id'] && $this->user->info['level']==9) {            
            ?><span class="sndTgl<?= isset($this->user->params['mute'])&&$this->user->params['mute']?' off':'' ?>" onclick="tglSound(this)"></span><?php            
        }


        $this->renderLoginBox(); 
        ?></div><?php 
        
        echo $this->filter_purpose();
    }
    
    
    function parsePageLabel($rname='',$sname='', $rootId, $sectionId, $purposeId=0){
        if($sname || $purposeId || $rname) {
            
            if(!$sname) $sname = $rname;
            
            if($purposeId){
                $pname=$this->router->purposes[$purposeId][$this->fieldNameIndex];
                switch($purposeId){
                    case 1://for sale
                    case 2://for rent
                    case 8://for trade
                        $sname = $sname ? $sname.' '.$pname : $sname;
                        break;
                    case 999://various
                        if($this->router->language=='ar'){                    
                            $sname = ($sname == 'متفرقات' ? $sname : ($sname=='' ? 'إعلانات متفرقة' : $sname.' متفرقة'));
                        }else{
                            $sname = ( (strpos($sname,'Misc.')===false || $sname == 'Miscellaneous') ? $sname : ($sname=='' ? 'Misc. Ads' : 'Misc. '.$sname));
                        }
                        break;
                    case 6://to rent
                    case 7://to buy
                        if($this->router->language=='ar'){                    
                            $sname = $sname ? $pname.' '.$sname : $pname;
                        }else{
                            $sname = $sname ? 'Looking '.$pname.' '.$sname : 'Looking '.$pname;
                        }
                        break;
                    case 3://vacancies
                        if($this->router->language=='ar'){                    
                            $sname = $sname ? $pname.' '.$sname : $pname;
                        }else{
                            $sname = $sname ? $sname.' '.$pname : $pname;
                        }
                        break;
                    case 4://seeking work
                        $in='';
                        if ($this->router->language=="en")$in=" {$this->lang['in']}";
                        $sname= $sname ? $pname.$in.' '.$sname : $pname;
                        break;
                    case 5://services
                        if($this->router->language=='ar'){                    
                            $sname = $sname ? ( strpos($sname,$pname)===false ? $pname .' '.$sname : $sname) : $pname;
                        }else{
                            $sname = $sname ? ( strpos($sname,$pname)===false ? $sname.' '.$pname : $sname) : $pname;
                        }                        
                        break;
                }
            }else{
                if($sname=='متفرقات')$sname = $rname .' متفرقة';
                if($rootId==4 && $sname){
                    if($this->router->language=='ar'){                    
                        $sname = $sname ? ( strpos($sname,'خدمات')===false ? 'خدمات '.$sname : $sname) : 'خدمات';
                    }else{
                        $sname = $sname ? ( strpos($sname,$pname)===false ? $sname.' Services' : $sname) : 'Services';
                    }
                }
            }
        }
        return $sname;
    }
    
    
    function filter_purpose() : string {
        if ($this->router->isPriceList) return '';
        $str='';
        
        if (($this->router->module=='search') && ($this->router->rootId || $this->router->sectionId) && !$this->userFavorites && !$this->router->watchId) {
            if ($this->router->rootId!=4 && count($this->router->purposes)>0 && count($this->router->pagePurposes)) {
                $i=0;            
                $hasQuery=false;
                $q="";
                if ($this->router->params['q']) {
                    $hasQuery=true;
                    $q='?q='.urlencode($this->router->params['q']);
                }

                if ($hasQuery) {
                    if ($this->extendedId || $this->localityId) {
                        $append_uri='';
                        $extended_uri='';
                        if ($this->extendedId && isset($this->router->countries[$this->router->countryId])) {
                            $append_uri='/'.($this->router->language!='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId?($this->hasCities && $this->router->cityId?3:2):1);
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                            if ($this->hasCities && $this->router->cityId && $this->router->cities[$this->router->cityId]) {
                                $extended_uri.=$this->router->cities[$this->router->cityId][3].'/';
                            }
                            $extended_uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';                        
                        }
                        elseif ($this->localityId && isset($this->router->countries[$this->router->countryId])) {
                            $append_uri='/'.($this->router->language!='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId?3:2);
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';                        
                            $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                            $extended_uri.=$this->router->sections[$this->router->sectionId][3].'/';
                        }
                        
                        foreach ($this->router->pagePurposes as $pid=>$purpose) {
                            if ((int)$purpose['counter']>0) {
                                $isNew=false;
                                $selected=($this->router->purposeId==$pid);
                                $str.= "<li>".
                                $this->renderListLink($purpose['name'], $extended_uri.$this->router->purposes[$pid][3].$append_uri.'/'.$q, $selected)."</li>";                            
                                $i++;
                            }
                        }
                    }
                    else {
                        foreach ($this->router->pagePurposes as $pid=>$purpose) {
                            if ((int)$pid>0) {
                                $pname = $this->extendedId?$this->extended[$this->extendedId]['name']:($this->router->sectionId?$this->router->sections[$this->router->sectionId][$this->fieldNameIndex]:($this->router->rootId?$this->router->roots[$this->router->rootId][$this->fieldNameIndex]:''));                            
                                switch ($pid) {
                                    case 1:
                                    case 2:
                                    case 8:
                                    case 999:
                                        $pname = $pname.' '. $purpose['name']; //$this->router->purposes[$pid][$this->fieldNameIndex];
                                        break;
                                    case 6:
                                    case 7:
                                        $pname = $purpose['name'] . ' ' . $pname;// $this->router->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                        break;
                                    case 3:
                                    case 4:
                                    case 5:
                                        $in="";
                                        if ($this->router->language=="en") { $in=" {$this->lang['in']}"; }                                        
                                        $pname=($this->router->sectionId) ? $purpose['name'].$in." ".$pname : $purpose['name'];
                                        break;
                                }
                                $selected=($this->router->purposeId==$pid);
                                $str.= "<li>".$this->renderListLink($pname, $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,
                                                                    $this->router->sectionId,$pid).$q, $selected)."</li>";
                                $i++;
                            }
                        }
                        
                        if (isset($this->router->sections[$this->router->sectionId][5]) && $this->router->sections[$this->router->sectionId][5]) {
                            $secId = $this->router->sections[$this->router->sectionId][5];
                            $str.= "<li>".$this->renderListLink($this->router->sections[$secId][$this->fieldNameIndex], $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->sections[$secId][4],
                                                                $secId,$this->router->sections[$this->router->sectionId][9]).$q, false)."</li>";
                            $i++;
                        }
                    }
                }
                else {
                    $append_uri='';
                    $extended_uri='';
                    if ($this->extendedId) {
                        $append_uri='/'.($this->router->language!='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId ? ($this->hasCities && $this->router->cityId?3:2):1);
                        if ($this->router->countryId) {
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                            if ($this->hasCities && $this->router->cityId) {
                                $extended_uri.=$this->router->cities[$this->router->cityId][3].'/';
                            }
                        }
                        else {
                            $extended_uri .= '/';
                        }
                        $extended_uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                    }
                    elseif ($this->localityId) {
                        $append_uri='/'.($this->router->language!='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId?3:2);
                        $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                        $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                        $extended_uri.=$this->router->sections[$this->router->sectionId][3].'/';
                    }

                    $base_name = ($this->extendedId && isset($this->extended[$this->extendedId]))?$this->extended[$this->extendedId]['name']:
                        (($this->router->sectionId && isset($this->router->pageSections[$this->router->sectionId]))?$this->router->pageSections[$this->router->sectionId]['name']:
                        (($this->router->rootId && isset($this->router->pageRoots[$this->router->rootId]))?$this->router->pageRoots[$this->router->rootId]['name']:''));
                    foreach ($this->router->pagePurposes as $pid=>$purpose) {
                        $pname = $base_name;
                        switch($pid){
                            case 1:
                            case 2:
                            case 8:
                            case 999:
                                $pname = $pname.' '.$purpose['name'];// $this->router->purposes[$purpose[0]][$this->fieldNameIndex];
                                break;
                            
                            case 6:
                            case 7:
                                $pname = $purpose['name'].' '.$pname;// $this->router->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                break;
                            
                            case 3:
                            case 4:
                            case 5:
                                $in="";
                                if ($this->router->language=="en")$in=" {$this->lang['in']}";
                                if ($this->router->sectionId) {
                                    $pname=$purpose['name'].$in." ".$pname;
                                }else {
                                    $pname=$purpose['name'];
                                }
                                break;
                        }
                        $isNew=false;
                        $selected=($this->router->purposeId==$pid /*$purpose[0]*/);
                        if ($this->extendedId || $this->localityId) {
                            $str.= "<li>".
                            $this->renderListLink($pname, $extended_uri.$this->router->purposes[$pid][3].$append_uri.'/', $selected)."</li>";
                        } 
                        else {
                            if (!$selected && $this->checkNewUserContent($purpose['unixtime'])) { $isNew=true; }
                            $str.= "<li".($isNew?" class='nl'":"").">".
                            $this->renderListLink('<span>'.$purpose['counter'].'&nbsp;</span>'. $pname,
                                                $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,
                                                $this->router->sectionId, $pid), $selected)."</li>";
                        }
                        $i++;
                    }
                    
                    if (isset($this->router->sections[$this->router->sectionId][5]) && $this->router->sections[$this->router->sectionId][5]) {
                        $secId = $this->router->sections[$this->router->sectionId][5];
                        $str.= "<li>".$this->renderListLink($this->router->sections[$secId][$this->fieldNameIndex], $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->sections[$secId][4],
                                                            $secId,$this->router->sections[$this->router->sectionId][9]), false)."</li>";
                        $i++;
                    }
                }
                
                if ($str && $i>1) {
                    $str='<div class="row reorder"><div class="col-12 prps"><ul>'.$str.'</ul></div></div>';
                }
                else {
                    $str='';
                }
            }        
        }
        return $str;
    }
    

    function filterPurposesArray() : array {
        $result=[];
        if ($this->router->isPriceList) {  return $result;  }
        
        if (($this->router->module==='search') && ($this->router->rootId>0||$this->router->sectionId>0) && !$this->userFavorites && !$this->router->watchId) {
            if ($this->router->rootId!==4 && \count($this->router->purposes)>0 && \count($this->router->pagePurposes)>0) {
                $hasQuery=false;
                $q='';
                if ($this->router->params['q']) {
                    $hasQuery=true;
                    $q='?q='.urlencode($this->router->params['q']);
                }

                if ($hasQuery) {
                    if ($this->extendedId>0 || $this->localityId>0) {
                        $append_uri='';
                        $extended_uri='';
                        if ($this->extendedId && isset($this->router->countries[$this->router->countryId])) {
                            $append_uri='/'.($this->router->language!=='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId?($this->hasCities && $this->router->cityId?3:2):1);
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                            if ($this->hasCities && $this->router->cityId && $this->router->cities[$this->router->cityId]) {
                                $extended_uri.=$this->router->cities[$this->router->cityId][3].'/';
                            }
                            $extended_uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';                        
                        }
                        elseif ($this->localityId && isset($this->router->countries[$this->router->countryId])) {
                            $append_uri='/'.($this->router->language!=='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId?3:2);
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';                        
                            $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                            $extended_uri.=$this->router->sections[$this->router->sectionId][3].'/';
                        }
                        
                        foreach ($this->router->pagePurposes as $pid=>$purpose) {
                            if ((int)$purpose['counter']>0) {
                                $isNew=false;
                                $selected=($this->router->purposeId==$pid);
                                $result[]='<li>'.$this->renderListLink($purpose['name'], $extended_uri.$this->router->purposes[$pid][3].$append_uri.'/'.$q, $selected).'</li>';                            
                            }
                        }
                    }
                    else {
                        foreach ($this->router->pagePurposes as $pid=>$purpose) {
                            if ((int)$pid>0) {
                                $pname=$this->extendedId>0?$this->extended[$this->extendedId]['name']:($this->router->sectionId?$this->router->sections[$this->router->sectionId][$this->fieldNameIndex]:($this->router->rootId?$this->router->roots[$this->router->rootId][$this->fieldNameIndex]:''));                            
                                switch ($pid) {
                                    case 1:
                                    case 2:
                                    case 8:
                                    case 999:
                                        $pname = $pname.' '. $purpose['name']; //$this->router->purposes[$pid][$this->fieldNameIndex];
                                        break;
                                    case 6:
                                    case 7:
                                        $pname = $purpose['name'] . ' ' . $pname;// $this->router->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                        break;
                                    case 3:
                                    case 4:
                                    case 5:
                                        $in="";
                                        if ($this->router->language==='en') { $in=" {$this->lang['in']}"; }                                        
                                        $pname=($this->router->sectionId>0) ? $purpose['name'].$in.' '.$pname : $purpose['name'];
                                        break;
                                }
                                $selected=($this->router->purposeId==$pid);
                                $result[]='<li>'.$this->renderListLink($pname, $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId,
                                                                    $this->router->sectionId, $pid).$q, $selected).'</li>';
                            }
                        }
                        
                        if (isset($this->router->sections[$this->router->sectionId][5]) && $this->router->sections[$this->router->sectionId][5]) {
                            $secId = $this->router->sections[$this->router->sectionId][5];
                            $result[]='<li>'.$this->renderListLink($this->router->sections[$secId][$this->fieldNameIndex], $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->sections[$secId][4],
                                                                $secId, $this->router->sections[$this->router->sectionId][9]).$q, false).'</li>';
                        }
                    }
                }
                else {
                    $append_uri='';
                    $extended_uri='';
                    if ($this->extendedId>0) {
                        $append_uri='/'.($this->router->language!=='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId ? ($this->hasCities && $this->router->cityId?3:2):1);
                        if ($this->router->countryId) {
                            $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                            if ($this->hasCities && $this->router->cityId) {
                                $extended_uri.=$this->router->cities[$this->router->cityId][3].'/';
                            }
                        }
                        else {
                            $extended_uri.='/';
                        }
                        $extended_uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                    }
                    elseif ($this->localityId>0) {
                        $append_uri='/'.($this->router->language!=='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId?3:2);
                        $extended_uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                        $extended_uri.=$this->localities[$this->localityId]['uri'].'/';
                        $extended_uri.=$this->router->sections[$this->router->sectionId][\Core\Data\Schema::BIN_URI].'/';
                    }

                    $base_name = ($this->extendedId>0 && isset($this->extended[$this->extendedId]))?$this->extended[$this->extendedId]['name']:
                        (($this->router->sectionId && isset($this->router->pageSections[$this->router->sectionId]))?$this->router->pageSections[$this->router->sectionId]['name']:
                        (($this->router->rootId && isset($this->router->pageRoots[$this->router->rootId]))?$this->router->pageRoots[$this->router->rootId]['name']:''));
                    
                    foreach ($this->router->pagePurposes as $pid=>$purpose) {
                        $pname = $base_name;
                        switch($pid){
                            case 1:
                            case 2:
                            case 8:
                            case 999:
                                $pname = $pname.' '.$purpose['name'];// $this->router->purposes[$purpose[0]][$this->fieldNameIndex];
                                break;
                            
                            case 6:
                            case 7:
                                $pname = $purpose['name'].' '.$pname;// $this->router->purposes[$purpose[0]][$this->fieldNameIndex].' '.$pname;
                                break;
                            
                            case 3:
                            case 4:
                            case 5:
                                $in="";
                                if ($this->router->language==='en') {  $in=" {$this->lang['in']}";  }
                                if ($this->router->sectionId) {
                                    $pname=$purpose['name'].$in.' '.$pname;
                                }
                                else {
                                    $pname=$purpose['name'];
                                }
                                break;
                        }
                        $isNew=false;
                        $selected=($this->router->purposeId==$pid /*$purpose[0]*/);
                        if ($this->extendedId>0 || $this->localityId>0) {
                            $result[]='<li>'.$this->renderListLink($pname, $extended_uri.$this->router->purposes[$pid]['uri'].$append_uri.'/', $selected).'</li>';
                        } 
                        else {
                            if (!$selected && $this->checkNewUserContent($purpose['unixtime'])) { $isNew=true; }
                            
                            $result[]='<li'.($isNew?" class=nl":"").'>'.
                                    $this->composeListLink(
                                            $pname.'<small>&nbsp;('.\number_format($purpose['counter']).')</small>',
                                            $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $pid), 
                                            $selected, "/web/css/1.0/assets/pu/{$pid}.svg").'</li>';
                        }
                    }
                    
                    if (isset($this->router->sections[$this->router->sectionId][5]) && $this->router->sections[$this->router->sectionId][5]>0) {
                        $secId=$this->router->sections[$this->router->sectionId][5];
                        $result[]='<li data-v=r>'.$this->renderListLink($this->router->sections[$secId][$this->fieldNameIndex], $this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->sections[$secId][4],
                                                            $secId, $this->router->sections[$this->router->sectionId][9]), false).'</li>';
                    }
                }
            }        
        }
        return $result;
    }
    
    
    function top() : void {
        $cityId=$this->router->cityId;
        if ($this->router->cityId) {
            if ($this->router->countryId===0) {
                $cityId = 0;
            }
            elseif (empty($this->router->countries[$this->router->countryId]['cities'])) {
                $cityId = 0;
            }
        }
        
        $url='';
        switch ($this->router->module) {
            case 'detail':
                if (!empty($this->detailAd)) {
                    $url=$this->detailAd->url();
                    //$url=sprintf($this->detailAd[Classifieds::URI_FORMAT], $this->router->isArabic()?'en/':'', $this->detailAd[Classifieds::ID]);
                    break;
                }
            case 'search':
            case 'index':
                if ($this->router->userId>0) { $url='/'.($this->partnerInfo['uri']).'/'; }
                elseif ($this->router->watchId) { $url='/watchlist/'; }
                elseif ($this->userFavorites) { $url='/favorites/'; }
                else {
                    $url=$this->router->getURL($this->router->countryId, $cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId, false);
                }

                if ($this->router->isArabic()) { $url.='en/'; }
                if ($this->router->params['start']) { $url.=$this->router->params['start'].'/'; }
                if ($this->pageUserId) {
                    $url.='?u='.$this->user->encodeId($this->pageUserId);
                }
                elseif ($this->router->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->router->params['q']) {
                        $params.='q='.urlencode($this->router->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;                
            case 'myads':
                $url='/myads/';
                if ($this->router->isArabic()) { $url.='en/'; }
                $sub=$this->get('sub');
                if (in_array($sub, ['pending','archive','drafts'])) {
                    $url.='?sub='.$sub;
                }
                break;
            default:
                $url='/'.$this->router->module.'/';
                if ($this->router->isArabic()) { $url.='en/'; }
                break;
        }
        $slogan=$this->router->isArabic()?'كل ما كنت تبحث عنه':'EVERYTHING YOU\'VE BEEN LOOKING FOR';
        ?>
        <div class="row top-header"><div class="viewable full-height ff-cols">
            <ul><?php
                if ($this->router->countryId>0 && isset($this->router->countries[$this->router->countryId])) {
                    ?><li><a id=regions href="javascript:regionWidget()" data-regions='<?=\json_encode($this->supportedCountries())?>'><?php
                    if ($this->router->cityId===0) {
                        echo $this->router->countries[$this->router->countryId]['name'];
                    }
                    else {
                        echo $this->router->cities[$this->router->cityId][$this->name];                        
                    }
                    ?><i class="icn icnsmall icn-<?=$this->router->countries[$this->router->countryId]['uri']?> iborder"></i></a></li><?php
                }
                else {
                    echo '<li><a href="#"><i class="icn icnsmall icn-globe invert"></i></a></li>';
                }?>
                <li>&vert;</li>
                <li><a href="<?= $this->router->getLanguagePath($this->user->isLoggedIn() ? '/myads/' : '/signin/') ?>"><?php 
                echo $this->user->isLoggedIn()?$this->lang['MyAccount']:$this->lang['signin'];?><i class="icn icn-user i20 icolor"></i></a></li>
                <li>&vert;</li>
                <li><a href="<?= $url ?>"><?= ($this->router->isArabic()?'English':'العربية') ?><i class="icn i20 icn-language icolor"></i></a></li>
            </ul>
            <div id='rgns'></div>
        </div></div>
        
        <header><div class="viewable ff-rows full-height sp-between">    
            <div><a class=half-height href="<?= $this->router->getURL($this->router->countryId, $cityId) ?>" title="<?= $this->lang['mourjan'] ?>"><i class=ilogo></i></a></div>
            <a class="btn " href='#'><?=$this->lang['placeAd']?></a>
        </div></header>

    <section class=search-box><div class="viewable ha-center"><div class=search>
        <form onsubmit="if(document.getElementById('q').value)return true;return false;" action="<?= $this->router->getLanguagePath('/'. $this->router->countryId>0?$this->router->countries[$this->router->countryId]['uri']:'' ) ?>"> 
        <div class=sbw><div class=sbe>
                <div class=strg><?php 
                    $selected=$this->router->params['ro']?$this->router->roots[$this->router->params['ro']][$this->name]:$this->lang['all_categories'];
                    ?>
                    <span><?=$selected?></span>
                    <div class=arrow></div>
                </div>
                <div class=options><?php
                    ?><div class="option<?=(!$this->router->params['ro']?' selected':'');?>" data-value="0"><?=$this->lang['all_categories']?></div><?php                   
                    foreach ($this->router->roots as $root) {
                        ?><div class="option<?=$this->router->params['ro']===$root[\Core\Data\Schema::BIN_ID]?' selected':''?>" <?php
                        ?>data-value="<?=$root[\Core\Data\Schema::BIN_ID]?>"><?php
                        ?><span><?=$root[$this->name]?></span><?php
                        ?><i class="icn ro i<?=$root[\Core\Data\Schema::BIN_ID]?>"></i><?php
                        ?></div><?php
                        //echo '<span class="option', $this->router->params['ro']===$root[\Core\Data\Schema::BIN_ID]?' selected"':'"', ' data-value="', $root[\Core\Data\Schema::BIN_ID], '">', $root['name_'.$this->router->language], '</span>';
                    }
                ?></div>
            </div>
        </div>
                   
        <?php
        if ($this->user->isLoggedIn(9)) {
            $selected=$this->router->params['cn']?$this->router->countries[$this->router->params['cn']]['name']:$this->lang['opt_all_countries'];
            ?>
            <div class=sbw><div class=sbe>
                <div class=strg><span><?= $selected?></span><div class=arrow></div></div>
                <div class=options><?php
                echo '<span class="option', (!$this->router->params['cn']?' selected" ':'" '), 'data-value="0">', $this->lang['opt_all_countries'], '</span>';                
                foreach ($this->router->countries as $cn=>$country) {
                    echo '<span class="option', $this->router->params['cn']===$cn?' selected"':'"', ' data-value="',$cn, '">', $country['name'], '</span>';
                }
                ?></div>
            </div></div>
            <input id=cn name=cn type=hidden value="0">
            <?php
        }
        ?>
        <?php
        $module=$this->router->module;
        if (\in_array($module, ['search', 'contact', 'about', 'terms', 'privacy'])) {
            ?><button class="mibtn mcolor"><i id=ibars class="icn icnsmall icn-bars invert"></i></button><?php
        }
        
        ?>        
        <input id=q name=q class=searchTerm type=search placeholder="<?=$this->lang['search_what']; ?>">
        <input id=ro name=ro type=hidden value="0">
        <button class=searchButton type=submit><i class="icn icnsmall icn-search invert"></i></button>
    </form>
    </div></div></section><?php
        if ($this->router->module!=='index') {
            echo '<main>';
        }
        //echo $this->filter_purpose();
    }
    
    
    
    
    function top_old() {
        $url='';
        $cityId=$this->router->cityId;
        if ($cityId) {
            if ($this->router->countryId==0) {
                $cityId=0;            
            } 
            else {
                if (count($this->router->countries[$this->router->countryId]['cities'])==0) {
                    $city_id=0;
                }
            }
        }
               
        switch ($this->router->module){
            case 'detail':
                if (!empty($this->detailAd)){
                    if ($this->router->language=='ar') {
                        $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                    } 
                    else {
                        $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                    }
                    break;
                }
            case 'search':
            case 'index':
                if($this->router->userId) $url='/'.($this->partnerInfo['uri']).'/';
                elseif($this->router->watchId) $url='/watchlist/';
                elseif ($this->userFavorites) $url='/favorites/';
                else $url=$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId,false);

                if ($this->router->language=='ar') $url.='en/';
                if ($this->router->params['start']) $url.=$this->router->params['start'].'/';
                if($this->pageUserId){
                    $url.='?u='.$this->user->encodeId($this->pageUserId);
                }
                elseif ($this->router->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->router->params['q']) {
                        $params.='q='.urlencode($this->router->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;                
            case 'myads':
                $url='/myads/';
                if ($this->router->language=='ar') $url.='en/';
                $sub=$this->get('sub');
                if(in_array($sub,array('pending','archive','drafts'))){
                    $url.='?sub='.$sub;
                }
                break;
            default:
                $url='/'.$this->router->module.'/';
                if ($this->router->language=='ar') $url.='en/';
                break;
        }
        
        $adLang='';
        if (!$this->router->isArabic()) {
            $adLang=$this->router->language.'/';
        }
        ?><div class='top'><?php
            ?><h1><?= $this->title ?></h1><?php  
            ?><div class="tob"><?php 
                if($this->router->module!='index'){
                    ?><a class="lg" title="<?= $this->lang['mourjan'] ?>" href="<?= $this->router->getURL($this->router->countryId,$cityId) ?>"><?php
                    ?><span class="i h"></span><?php
                    ?><img width="100px" height="30px" src="<?= $this->router->cfg['url_img']?>/msl<?= $this->router->_png ?>" alt="<?= $this->lang['mourjan'] ?>" /><?php
                    ?></a><?php
                }
                if (!$this->router->userId){
                    if ($this->router->language=='ar') {
                        ?><a class="gl" href="<?= $url ?>">English</a><?php
                        ?><span class="gr">عربي</span><?php
                    } 
                    else {
                        ?><span class="gl">English</span><?php
                        ?><a class="gr" href="<?= $url ?>">عربي</a><?php
                    }
                }
            ?></div><?php
            if($this->user->info['id'] && $this->user->isSuperUser() && $this->router->module!='admin'){
                ?><a class="pb" style="right:<?= $this->router->language == 'ar' ? '97px' : '165px' ?>" href="/monitor/<?= $adLang ?>">monitor</a><?php
                ?><a class="pb" style="border-radius:0" href="/admin/<?= $adLang ?>"><span class="i p"></span><?= $this->lang['administration'] ?></a><?php
            }
            else{
                if (!$this->router->userId && $this->router->module!='post'){ 
                    ?><a class="pb" href="/post/<?= $adLang ?>"><span class="i p"></span><?= $this->lang['postFree'] ?></a><?php
                }
            }
            if ($this->router->userId){
                if ($this->router->language=='ar') {
                    ?><a class="gl" href="<?= $url ?>">English</a><?php
                    ?><span class="gr">عربي</span><?php
                } 
                else {
                    ?><span class="gl">English</span><?php
                    ?><a class="gr" href="<?= $url ?>">عربي</a><?php
                }
            }

            ?></div><?php  
            if(!$this->router->userId && $this->router->config->enabledAds()){
                ?><div class="w tpb"><?php 
                ?><a class="lg" href="<?= $this->router->getURL($this->router->countryId,$cityId) ?>" title="<?= $this->lang['mourjan'] ?>"><img height="90" width="130" src="<?= $this->router->config->cssURL ?>/i/logo<?= $this->router->_jpg ?>" alt="<?= $this->lang['mourjan'] ?>" /></a><?php 
                    echo $this->fill_ad('zone_0', 'ad_t');
                ?></div><?php
            }
            else{
                if(!$this->notifications) {
                    ?><div class="tps"></div><?php
                }
            }
            $this->renderNotifications();
    }


    function topMobile(){
        $q=$this->get('q', 'filter');
        if (!$this->user->info['id']){
            if ($this->userFavorites) {
                $this->lang['hint_login']=$this->lang['hint_login_favorites'];
                $this->requireLogin=true;
            }elseif($this->router->watchId) {
                $this->lang['hint_login']=$this->lang['hint_login_watch'];
                $this->requireLogin=true;
            }else if($this->router->module=='myads' || $this->router->module=='post' || $this->router->module=='account' || $this->router->module=='home'){
                $this->requireLogin=true;
            }
        }
        //$backButton="<div class='bt btBack rc'><div><div></div></div></div>";
        $backButton='<span class="bt-back"></span>';
        $hasBack=false;
        $cityId=$this->router->cityId;
        if ($cityId) {
            if (empty($this->router->countries[$this->router->countryId]['cities'])) {
                $cityId=0;
            }
        }
        $headTitle='Mourjan.com';
        switch ($this->router->module) {
            case 'buy':
            case 'buyu':            
            case 'statement':            
            case 'gold':            
            case 'premium':            
                $backButton = '<a class="back" href="javascript:history.back()"></a>';
                $hasBack=true;
                break;
            case "detail":
                $tmpUrl='';
                if (isset($this->router->params['tag_id']) && $this->router->params['tag_id']){
                    $tmpUrl = $this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
                }elseif(isset($this->router->params['loc_id']) && $this->router->params['loc_id']){
                    if (isset($this->localities[$this->router->params['loc_id']]) && $this->localities[$this->router->params['loc_id']]['parent_geo_id']){
                        $tmpId=$this->router->params['loc_id'];
                        $tmpUrl = '/'.$this->router->countries[$this->router->countryId]['uri'].'/'.$this->localities[$tmpId]['uri'].'/'.$this->router->sections[$this->router->sectionId][3].'/'.($this->router->purposeId ? $this->router->purposes[$this->router->purposeId][3].'/' : '').($this->router->language!='ar' ? $this->router->language.'/':'').'c-'.$tmpId.'-2/';
                    }else {
                        $tmpUrl = $this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
                    }
                }else {
                    $tmpUrl = $this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
                }
                if ($this->router->params['start']>1){
                    $tmpUrl.=$this->router->params['start'].'/';
                }
                if ($this->router->params['q']){
                    $tmpUrl.='?q='.urlencode($this->router->params['q']);
                }
                $backButton = '<a class="back" href="'.$tmpUrl.'"></a>';
                $hasBack=true;
                break;
            case "search":
                if ($this->router->sectionId) {
                    if ($this->extendedId){
                        $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId).'"></a>';
                    }elseif($this->localityId){
                        $this->localityId = $this->localityId+0;

                        if (isset($this->localities[$this->localityId]) && isset($this->localities[$this->localities[$this->localityId]['parent_geo_id']+0])){
                            $tmpId=$this->localities[$this->localityId]['parent_geo_id']+0;
                            $backButton = '<a class="back" href="/'.$this->router->countries[$this->router->countryId]['uri'].'/'.$this->localities[$tmpId]['uri'].'/'.$this->router->sections[$this->router->sectionId][3].'/'.($this->router->purposeId ? $this->router->purposes[$this->router->purposeId][3].'/' : '').($this->router->language!='ar' ? $this->router->language.'/':'').'c-'.$tmpId.'-2/"></a>';
                        }else {
                            $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId).'"></a>';
                        }
                    }else {
                        $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId).'"></a>';
                    }
                    $hasBack=true;
                }elseif ($this->router->rootId || $this->router->watchId || $this->userFavorites) {
                    $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId).'"></a>';
                    $hasBack=true;
                }
                break;
            case "index":
            case "search":
                if ($this->router->sectionId) {
                    $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId).'"></a>';
                    $hasBack=true;
                }elseif ($this->router->rootId || $this->router->watchId || $this->userFavorites) {
                    $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId).'"></a>';
                    $hasBack=true;
                }
                break;
            default:
                $backButton = '<a class="back" href="'.$this->router->getURL($this->router->countryId,$cityId).'"></a>';
                $hasBack=true;
                break;
        }
        switch ($this->router->module) {
            case 'index':
                if ($this->router->rootId) {
                    $headTitle=$this->router->roots[$this->router->rootId][$this->fieldNameIndex];
                }elseif($this->router->cityId){
                    $headTitle=$this->lang['mourjan'].' '.$this->cityName;
                }elseif($this->router->countryId){
                    $headTitle=$this->lang['mourjan'].' '.$this->countryName;
                }
                break;
            case 'detail':
                $headTitle='';
                break;
            case 'search':
                $headTitle=  $this->title;
                break;
            case 'contact':
                $headTitle=$this->lang['title'];
                break;
            default:
                $headTitle=  $this->title;
                break;
        }
        
        if (!$this->router->isApp) {
            $url='';
            switch ($this->router->module){
                case 'detail':
                    if (!empty($this->detailAd)){
                        if ($this->router->language=='ar') {
                            //$url = $this->router->cfg['url_base'].sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                            $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], 'en/', $this->detailAd[Classifieds::ID]);
                        } else {
                            //$url = $this->router->cfg['url_base'].sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                            $url = sprintf($this->detailAd[Classifieds::URI_FORMAT], '', $this->detailAd[Classifieds::ID]);
                        }
                        break;
                    }
            case 'search':
                //if($this->router->userId) $url='/'.($this->partnerInfo['uri']).'/';
                if($this->router->watchId) $url='/watchlist/';
                elseif ($this->userFavorites) $url='/favorites/';
                else $url=$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId,false);

                if ($this->router->language=='ar') $url.='en/';
                if ($this->router->params['start']) $url.=$this->router->params['start'].'/';
                if($this->pageUserId){
                    $url.='?u='.$this->user->encodeId($this->pageUserId);
                }elseif ($this->router->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->router->params['q']) {
                        $params.='q='.urlencode($this->router->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;
            case 'myads':
                $url='/myads/';
                if ($this->router->language=='ar') $url.='en/';
                $sub=$this->get('sub');
                if(in_array($sub,array('pending','archive','drafts'))){
                    $url.='?sub='.$sub;
                }
                break;
            case 'index':
                $url=$this->router->getURL($this->router->countryId,$cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId,false);
                if ($this->router->language=='ar') $url.='en/';
                if ($this->router->params['start']) $url.="{$this->router->params['start']}/";
                if ($this->router->isDynamic) {
                    $url.='?';
                    $params='';
                    $append=false;
                    if ($this->router->params['q']) {
                        $params.='q='.urlencode($this->router->params['q']);
                        $append=true;
                    }
                    $url.=$params;
                }
                break;
            default:
                $url='/'.$this->router->module.'/';
                if ($this->router->language=='ar') $url.='en/';
                break;
        }
        $this->switchLangUrl = $url;
        $searchButton = '';
        if ($this->router->countryId) {
            if (0 && $this->router->params['q']) {
                $searchButton = '<div onclick="ose(this)" class="button srch on"><span class="k"></span></div>';
            }else {
                $searchButton = '<div onclick="ose(this)" class="button srch"><span class="k"></span></div>';
            }
        }
        ?><div class='top<?= ($hasBack ?' hasB':'').($searchButton ?' hasS':'') ?>'><?php
        ?><span onclick="side(this)" class="k home"></span><?php
        if ($hasBack) { echo $backButton; }
        echo $searchButton;
        /* if ($this->router->module!='index' || ($this->router->module=='index' && $this->router->rootId)) {
            ?><a class="bt fl nsh" href="<?= $this->router->getURL($this->router->countryId,$this->router->cityId) ?>"><span class="bt-home"></span></a>
            <?php } */ ?><h1 id="title" class="<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->router->language=='en'?'e':'a') ?>"><?php
        echo $headTitle;
        ?></h1></div><?php   
        
        $menu='';
        $menuIdx=0;
        $loginErr = (isset($_GET['login']) && $_GET['login']=='error') ? 1:0;
        $loginECode = ($loginErr && isset($_GET['code']) && $_GET['code']) ? 1:0;
        $lang='';
        if (!$this->router->isArabic()) $lang=$this->router->language.'/';
        
        /*
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
        
        if ( ($this->router->module!='index') || ($this->router->module=='index' && ($this->router->sectionId || $this->router->rootId) ) ) {
            $menu .= "<li><a href='{$this->router->getURL($this->user->params['country'],$this->user->params['city'])}'><span class='k home'></span></a></li>";
            $menuIdx++;
        }elseif($this->router->countryId) {
            //$menu .= "<li><span class='k home on'></span></li>";
            $menu .= "<li><span class='ps'><span class='k home on'></span></span></li>";
            $menuIdx++;
        }
        
        if ($this->user->info['id']) {
            
            if ($this->router->module=="search" && $this->router->watchId) {
                $menu .= "<li><span class='ps'><span class='k eye on'></span></span></li>";
            }else {
                $menu .= "<li><a href='/watchlist/{$lang}'><span class='k eye'></span></a></li>";
            }
            $menuIdx++;
            if ($this->router->module=="search" && $this->userFavorites) {
                $menu .= "<li><span class='ps'><span class='k fav on'></span></span></li>";
            }else {
                $menu .= "<li><a href='/favorites/{$lang}'><span class='k fav'></span></a></li>";
            }
            $menuIdx++;
        }
        
        
        if ($this->router->language=='ar') {
            $menu .= "<li><a href='{$url}'>English</a></li>";
            $menuIdx++;
        } else {
            $menu .= "<li><a href='{$url}'><b>عربي</b></a></li>";
            $menuIdx++;
        }
        ?><div><?php */
        }
        
        if (0 && $this->router->countryId) {
            $uri='';
            if ($this->extendedId){
                $uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                    $uri.=$this->router->cities[$this->router->cityId][3].'/';
                }
                $uri.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                if ($this->router->purposeId)$uri.=$this->router->purposes[$this->router->purposeId][3].'/';
                $uri.=($this->router->language!='ar'?$this->router->language.'/':'').'q-'.$this->extendedId.'-'.($this->router->countryId ? ($this->hasCities && $this->router->cityId ? 3:2) :1).'/';
            }elseif($this->localityId){
                $uri='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                $uri.=$this->localities[$this->localityId]['uri'].'/';
                $uri.=$this->router->sections[$this->router->sectionId][3].'/';
                if ($this->router->purposeId)$uri.=$this->router->purposes[$this->router->purposeId][3].'/';
                $uri.=($this->router->language!='ar'?$this->router->language.'/':'').'c-'.$this->localityId.'-'.($this->hasCities && $this->router->cityId ? 3:2).'/';
            }else {
                $uri=$this->router->getURL($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
            }
        ?><div class="sef<?= $this->router->params['q'] ? ' on':'' ?>"><?php
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
        //error_log("isApp: {$this->router->isApp}", 0 );
        if (0 && !$this->router->isApp) {
        ?><ul class="menu f<?= $menuIdx ?>"><?= $menu ?></ul><?php
        ?></div><?php
        if ($this->requireLogin){
            if($this->router->module=='account' && isset($this->user->pending['email_validation']) && $this->user->pending['email_validation']==2){
                $this->lang['hint_login']=$this->lang['login_email_verify'];
            }
                
            ?><div class="str ctr"><?= $this->lang['hint_login'] ?></div><br /><?php
        }
        ?><div id="sif" class='si<?= ($loginErr || $this->requireLogin ? ' blk':'') ?>'><?php 
        if($this->user->info['id']) {
            if($this->router->module != 'index' || ($this->router->module == 'index' && $this->router->rootId) || ($this->router->module == 'index' && !$this->router->countryId)) {
            
            ?><ul class="ls us br"><?php 
            if($this->router->module != 'post') {
                   ?><li><a href="/post/<?= $lang ?>"><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post_m'] ?></b></li><?php 
            }
            ?></ul><?php
            ?><ul class="ls us br"><?php 
            if($this->router->module != 'search' || !$this->userFavorites) {
                   ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></b></li><?php 
            }
            /*if($this->router->module != 'search' || !$this->router->watchId) {
                   ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?><span class="to"></span></a></li><?php 
            }else{
                ?><li class="on"><b><span class="ic k eye on"></span><?= $this->lang['myList'] ?></b></li><?php 
            }*/
            ?></ul><?php
            ?><ul class="ls us br"><?php
                ?><li class="h"><b><?= $this->lang['myAds'] ?></b></li><?php
            $sub=(isset($_GET['sub']) && $_GET['sub'] ? $_GET['sub']:'');
            if($this->router->module != 'myads' || ($this->router->module == 'myads' && $sub!='') ) {
                    ?><li><a href="/myads/<?= $lang ?>"><span class="ic aon"></span><?= $this->lang['ads_active'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic aon"></span><?= $this->lang['ads_active'] ?></b></li><?php
            }
            if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='pending') ) {
                    ?><li><a href="/myads/<?= $lang ?>?sub=pending"><span class="ic apd"></span><?= $this->lang['ads_pending'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></b></li><?php
            }
            if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='drafts'))  {
                ?><li><a href="/myads/<?= $lang ?>?sub=drafts"><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></b></li><?php
            }
            if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='archive'))  {
                ?><li><a href="/myads/<?= $lang ?>?sub=archive"><span class="ic afd"></span><?= $this->lang['ads_archive'] ?><span class="to"></span></a></li><?php
            }else{
                ?><li class="on"><b><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></b></li><?php
            }
            ?></ul><?php
            
            ?><ul class="ls us br"><?php 
                if($this->router->module != 'account') {
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
            
        }
    }
    /* end not isApp */
        /* ?></div><?php */
        if (!$this->router->isApp) {
            if($this->user->info['id']==0 && $this->requireLogin){
                ?><div class="si blk"><h2 class="ctr"><?= $this->lang['signin_m'] ?></h2><?php
                ?><ul><?php
                ?><li><a class="bt mj" href="/signin/<?= $lang ?>">Mourjan</a></li><?php
                ?><li><a class="bt fb" href="?provider=facebook">Facebook</a></li><?php
                ?><li><a class="goobt" href="?provider=google"><img src="<?= $this->router->cfg['url_img']?>/google-login-m<?= $this->router->_png ?>" /></a></li><?php
                ?><li><a class="bt tw" href="?provider=twitter">Twitter</a></li><?php
                ?><li><a class="bt ya" href="?provider=yahoo">Yahoo</a></li><?php
                ?><li><a class="bt lk" href="?provider=linkedin">LinkedIn</a></li><?php
                ?><li><a class="bt wi" href="?provider=live">Windows Live</a></li><?php
                ?></ul></div><?php 
            }
            $this->renderNotificationsMobile();
            
        }
      
       $isNotSearch = preg_match('/\/(?:watchlist|favorites)\//iu', $_SERVER['REQUEST_URI']);
        if (!$this->router->isApp && $this->router->module=='search' && !$isNotSearch && $this->router->rootId!=4 /*&& ($this->router->rootId || $this->router->sectionId)*/ && count($this->router->purposes)>1 && !($this->router->purposeId && count($this->router->pagePurposes)==1)) {
            $q = '';
            $i = 0;
            $hasQuery = false;
            if ($this->router->params['q']) {
                $hasQuery = true;
                $q = '?q=' . urlencode($this->router->params['q']);
            }
            
            echo "<div id='menu' class='menu'>";
            
            if ($hasQuery) {
                foreach ($this->router->pagePurposes as $pid=>$purpose) {
                    if ($this->router->purposeId == $pid) {
                        echo '<b>', $purpose['name'], '</b>';
                        //echo '<li><span class="bt">', $purpose['name'], '</span></li>';
                    } else {
                        //echo '<li><a class="bt" href="' . $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $pid) . $q . '">', $purpose['name'], '</a></li>';
                        echo '<a  href="'.$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $pid) . $q . '">', $purpose['name'],'</a>';
                    }                    
                }
            }else {
                $append_uri = '';
                $extended_uri = '';
                if ($this->extendedId) {
                    $append_uri = '/' . ($this->router->language != 'ar' ? $this->router->language . '/' : '') . 'q-' . $this->extendedId . '-' . ($this->router->countryId ? ($this->hasCities && $this->router->cityId ? 3 : 2) : 1);
                    $extended_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';
                    if ($this->hasCities && $this->router->cityId) {
                        $extended_uri.=$this->router->cities[$this->router->cityId][3] . '/';
                    }
                    $extended_uri.=$this->router->sections[$this->router->sectionId][3] . '-' . $this->extended[$this->extendedId]['uri'] . '/';
                } elseif ($this->localityId) {
                    $append_uri = '/' . ($this->router->language != 'ar' ? $this->router->language . '/' : '') . 'c-' . $this->localityId . '-' . ($this->hasCities && $this->router->cityId ? 3 : 2);
                    $extended_uri = '/' . $this->router->countries[$this->router->countryId]['uri'] . '/';
                    $extended_uri.=$this->localities[$this->localityId]['uri'] . '/';
                    $extended_uri.=$this->router->sections[$this->router->sectionId][3] . '/';
                }
                
                foreach ($this->router->pagePurposes as $pid=>$purpose) {
                    $selected = ($this->router->purposeId == $pid);
                    
                    if ($this->extendedId || $this->localityId) {
                        if ($selected) {
                            echo '<b>', $purpose['name'], ' <span>('.$purpose['counter'].')</span></b>';
                        } else {
                            echo '<a href="'. $extended_uri . $this->router->purposes[$pid][3] . $append_uri . '/' . '">', $purpose['name'], ' <span>('.$purpose['counter'].')</span></a>';
                        }                        
                    } else {
                        if ($selected) {
                            echo '<b>', $purpose['name'], ' <span>('.$purpose['counter'].')</span></b>';
                        } else {
                            echo '<a href="' . $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $pid) . $q . '">', $purpose['name'], ' <span>('.$purpose['counter'].')</span></a>';
                        }
                    }
                    $i++;
                }
            }
            echo '</div>';
        }
    }

    function paginationMobile(){      
        $qtotal_found = $this->searchResults['body']['total_found'];
        $appendLang=($this->router->language=='ar'?'/':'/'.$this->router->language.'/');
        if (!$this->paginationString) {
            
            if ($qtotal_found>0) {
                $pages = ceil($qtotal_found/$this->num);
                $tmp=ceil($this->router->cfg['search_results_max']/$this->num);
                if ($pages>$tmp) $pages=(int)$tmp;
                if ($pages>1) {
                    
                    if ($this->userFavorites) $link='/favorites'.$appendLang.'%s';
                    elseif ($this->router->watchId)
                        $link='/watchlist'.$appendLang.'%s';  
                    elseif ($this->extendedId) {
                        $idx=1;
                        $link='/';
                        if ($this->router->countryId) {
                            $idx=2;
                            $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                        }
                        if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                            $link.=$this->router->cities[$this->router->cityId][3].'/';
                            $idx=3;
                        }
                        $link.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                        if ($this->router->purposeId)
                            $link.=$this->router->purposes[$this->router->purposeId][3].'/';
                        if ($this->router->language!='ar')$link.=$this->router->language.'/';
                            $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
                    }elseif ($this->localityId) {
                        $idx=2;
                        $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                        /*if ($this->hasCities && $this->router->cityId) {
                            $link.=$this->router->cities[$this->router->cityId][3].'/';
                            $idx=3;
                        }*/
                        $link.=$this->localities[$this->localityId]['uri'].'/';
                        if ($this->router->sectionId) 
                            $link.=$this->router->sections[$this->router->sectionId][3].'/';
                        else 
                            $link.=$this->router->pageRoots[$this->router->rootId]['uri'].'/';
                        if ($this->router->purposeId)
                            $link.=$this->router->purposes[$this->router->purposeId][3].'/';
                        if ($this->router->language!='ar')$link.=$this->router->language.'/';
                            $link.='c-'.$this->localityId.'-'.$idx.'/%s';
                        }else 
                            $link=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId).'%s';
                    $uri_query='';
                    $linkAppend='?';
                    if($this->pageUserId){
                        $uri_query=$linkAppend.'u='.$this->user->encodeId($this->pageUserId);
                        $linkAppend='&';
                    }
                    if ($this->router->params['q']) {
                        $uri_query=$linkAppend.'q='.urlencode($this->router->params['q']);
                        $linkAppend='&';
                    }

                    $result='';
                    $currentPage=($this->router->params['start']?$this->router->params['start']:1);
                    
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
                        $offset=$this->router->params['start']+1;
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

    
    function pagination($link=null) : string {
        if (!$this->paginationString||$link) {
            $appendLang=$this->router->getLanguagePath();
            $result='';
            if ($this->router->userId) {
                $link='/'.$this->partnerInfo['uri'].$appendLang.'%s';                
            }
            elseif ($this->router->watchId) {
                $link='/watchlist'.$appendLang.'%s';                
            }
            elseif ($this->userFavorites) {
                $link='/favorites'.$appendLang.'%s';                
            }
            elseif ($this->extendedId) {
                $idx=1;
                $link='/';
                if ($this->router->countryId) {
                    $idx=2;
                    $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                }
                if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                    $link.=$this->router->cities[$this->router->cityId][3].'/';
                    $idx=3;
                }
                $link.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
                if ($this->router->purposeId)
                $link.=$this->router->purposes[$this->router->purposeId][3].'/';
                if ($this->router->language!='ar')$link.=$this->router->language.'/';
                $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
            }
            elseif ($this->localityId) {
                $idx=2;
                $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
                $link.=$this->localities[$this->localityId]['uri'].'/';
                if ($this->router->sectionId) 
                    $link.=$this->router->sections[$this->router->sectionId][3].'/';
                else 
                    $link.=$this->router->pageRoots[$this->router->rootId]['uri'].'/';
                if ($this->router->purposeId)
                    $link.=$this->router->purposes[$this->router->purposeId][3].'/';
                if ($this->router->language!='ar')$link.=$this->router->language.'/';
                $link.='c-'.$this->localityId.'-'.$idx.'/%s';
            }
            else {
                $link=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId).'%s';                
            }

            $uri_query='';
            $linkAppend='?';
            if ($this->pageUserId) {
                $uri_query=$linkAppend.'u='.$this->user->encodeId($this->pageUserId);
                $linkAppend='&';
            }
            
            if ($this->router->params['q']) {
                $uri_query=$linkAppend.'q='.urlencode($this->router->params['q']);
                $linkAppend='&';
            }
            
            $qtotal_found = $this->searchResults['body']['total_found'];
            if ($qtotal_found>0) {
                $pages = ceil($qtotal_found/$this->num);
                $tmp=$this->router->config->get('search_results_max')/$this->num;
                if ($pages>$tmp) { $pages=$tmp; }
                if ($pages>1) {    
                    $currentPage = ($this->router->params['start']?$this->router->params['start']:1);
                    $isFirst=true;
                    if ($currentPage>1) {
                        $result.='<li class="prev"><a target="_self" href="';
                        
                        $page_no= $currentPage-1;
                        if ($page_no>1) {
                            $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        } 
                        else {
                            $result.=sprintf ($link, $uri_query);
                        }
                        
                        $result.='">';
                        $result.=$this->lang['previous'];
                        $result.='</a>';
                        $result.='</li>';
                        $isFirst=false;
                    }
                    $pageMargin=1;
                    $startPage=$currentPage-$pageMargin;
                    if ($startPage<=0) $startPage=1;
                    $endPage=$currentPage+$pageMargin;
                    if ($endPage>$pages) $endPage=$pages;
                    while ($startPage<=$endPage) {
                        if ($startPage==$currentPage) {
                            $result.='<li class="disabled"><span>'.$startPage.'</span></li>';
                        } 
                        else {
                            $page_no=$startPage-1;
                            $result.='<li><a target="_self" href="';
                            
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
                        $offset=$this->router->params['start']+$this->num;
                        $result.='<a target="_self" href="';                        
                        $page_no=$currentPage+1;
                        $result.=sprintf ($link, "{$page_no}/{$uri_query}");
                        $result.='">';
                        $result.=$this->lang['next'];
                        $result.='</a></li>';
                        $result.= '</ul>';
                        
                        $result= '<div class=row><div class=col-12><ul class="pagination">'.$result.'</div></div>';
                    }
                    else { 
                        $result.= '</ul>';
                        $result= '<div class=row><div class=col-12><ul class="pagination">'.$result.'</div></div>';
                    }
                    $this->paginationString=$result;
                }
            }
        }
        return $this->paginationString;
    }
    
    
    function mt_pagination($link=null) : string {
        $appendLang=$this->router->getLanguagePath();
        $result='';
        if ($this->router->userId) {
            $link='/'.$this->partnerInfo['uri'].$appendLang.'%s';                
        }
        elseif ($this->router->watchId) {
            $link='/watchlist'.$appendLang.'%s';                
        }
        elseif ($this->userFavorites) {
            $link='/favorites'.$appendLang.'%s';                
        }
        elseif ($this->extendedId) {
            $idx=1;
            $link='/';
            if ($this->router->countryId) {
                $idx=2;
                $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
            }
            if (isset($this->router->countries[$this->router->countryId]['cities'][$this->router->cityId])) {
                $link.=$this->router->cities[$this->router->cityId][3].'/';
                $idx=3;
            }
            $link.=$this->router->sections[$this->router->sectionId][3].'-'.$this->extended[$this->extendedId]['uri'].'/';
            if ($this->router->purposeId) { $link.=$this->router->purposes[$this->router->purposeId]['uri'].'/'; }
            if ($this->router->language!=='ar')$link.=$this->router->language.'/';
                $link.='q-'.$this->extendedId.'-'.$idx.'/%s';
        }
        elseif ($this->localityId) {
            $idx=2;
            $link='/'.$this->router->countries[$this->router->countryId]['uri'].'/';
            $link.=$this->localities[$this->localityId]['uri'].'/';
            if ($this->router->sectionId) 
                $link.=$this->router->sections[$this->router->sectionId]['uri'].'/';
            else 
                $link.=$this->router->pageRoots[$this->router->rootId]['uri'].'/';
            if ($this->router->purposeId)
                $link.=$this->router->purposes[$this->router->purposeId]['uri'].'/';
            if ($this->router->language!=='ar')$link.=$this->router->language.'/';
            $link.='c-'.$this->localityId.'-'.$idx.'/%s';
        }
        else {
            $link=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId).'%s';                
        }

        $uri_query='';
        $linkAppend='?';
        if ($this->pageUserId) {
            $uri_query=$linkAppend.'u='.$this->user->encodeId($this->pageUserId);
            $linkAppend='&';
        }
            
        if ($this->router->params['q']) {
            $uri_query=$linkAppend.'q='.urlencode($this->router->params['q']);
            $linkAppend='&';
        }
            
        $qtotal_found=$this->searchResults['body']['total_found'];
        if ($qtotal_found>0) {
            $pages=\ceil($qtotal_found/$this->num);                
            $tmp=$this->router->config->get('search_results_max')/$this->num;
                
            if ($pages>$tmp) { $pages=$tmp; }
                
            if ($pages>1) {
                $currentPage=($this->router->params['start']?$this->router->params['start']:1);
                $isFirst=true;
                if ($currentPage>1) {
                    $result.='<li><a target="_self" href="';
                        
                    $page_no=$currentPage-1;
                    if ($page_no>1) {
                        $result.=\sprintf($link, "{$page_no}/{$uri_query}");
                    }
                    else {
                        $result.=\sprintf($link, $uri_query);
                    }
                 
                    $result.='"><b style="margin-left:-9px;">&#x3008</b></a></li>';
                    $isFirst=false;
                }
                $pageMargin=2;
                $startPage=$currentPage-$pageMargin;
                if ($startPage<=0) $startPage=1;
                $endPage=$currentPage+$pageMargin;
                if ($endPage>$pages) $endPage=$pages;
                while ($startPage<=$endPage) {
                    if ($startPage==$currentPage) {
                        $result.='<li class="active"><span>'.$startPage.'</span></li>';
                    } 
                    else {
                        $page_no=$startPage-1;
                        $result.='<li><a target="_self" href="';
            
                        if ($page_no)
                            $result.=\sprintf($link, "{$startPage}/{$uri_query}");
                        else 
                            $result.=\sprintf($link, $uri_query);
                            
                        $result.='">'.$startPage.'</a></li>';
                    }
                    $isFirst=false;
                    $startPage++;
                }
                    
                if ($currentPage<$pages) {
                    $result.='<li>';
                    $offset=$this->router->params['start']+$this->num;
                    $result.='<a target="_self" href="';                        
                    $page_no=$currentPage+1;
                    $result.=\sprintf($link, "{$page_no}/{$uri_query}");
                    $result.='"><b style="margin-left:9px;">&#x3009;</b></a></li></ul>';
                        
                    $result='<div class=row><div class=col-12><ul class=pgn>'.$result.'</div></div>';
                }
                else { 
                    $result.= '</ul>';
                    $result='<div class=row><div class=col-12><ul class=pgn>'.$result.'</div></div>';
                }
                $this->paginationString=$result;
            }
        }
        return $this->paginationString;
    }
   
    /********************************************************************/
    /*                           abstract functions                     */
    /********************************************************************/

    function header() : void {
        ?><link rel='preconnect' href='https//c6.mourjan.com' /><?php    
        ?><link rel='preconnect' href='https://www.googletagmanager.com' /><?php
        ?><link rel='preconnect' href='https://pagead2.googlesyndication.com' /><?php
        ?><link rel='preconnect' href='https://googleads.g.doubleclick.net' /><?php
        ?><link rel="preconnect" href="https://www.google-analytics.com"><?php
        ?><link rel="preconnect" href="https://adservice.google.com"><?php
        ?><link rel="preconnect" href="https://fonts.googleapis.com"><?php
        
        ?><meta name="google-site-verification" content="v7TrImfR7LFmP6-6qV2eXLsC1qJSZAeKx2_4oFfxwGg" /><?php
        if ($this->userFavorites){
            $this->lang['description']=$this->lang['home_description'].$this->lang['home_description_all'];
        }
        if ($this->lang['description']) {             
            ?><meta name="description" content="<?= \preg_replace("/<.*?>/", "", $this->lang['description']) ?>" /><?php 
        } 
        if ($this->router->config->get('enabled_sharing') && $this->router->module!=="detail") {
            $sharingUrl=$this->router->config->baseURL.'/';
            if ($this->router->userId){
                $sharingUrl.=$this->partnerInfo['uri'].'/';
            }
            if ($this->extendedId || $this->localityId){
                $sharingUrl.=$this->extended_uri ? \substr($this->extended_uri, 1, \strlen($this->extended_uri)) : '';
            }
            elseif($this->router->module==='index' || ($this->router->module==='search' && !($this->router->watchId || $this->userFavorites))){
                $sharingUrl.=$this->router->uri ? \substr($this->router->uri, 1, \strlen($this->router->uri)) : '';
            }
            if ($this->router->language!=='ar') {
                $sharingUrl.=$this->router->language.'/';
            }
            if ($this->router->module==='search'){
                if ($this->router->params['start']) {
                    $sharingUrl.=$this->router->params['start'].'/';
                }
                if ($this->router->params['q']) {
                    $sharingUrl.='?q='.\urlencode($this->router->params['q']);
                }
            }
            $pageThumb=$this->router->config->imgURL.'/mourjan-icon'.$this->router->_png;
            if($this->router->sectionId && isset($this->router->sections[$this->router->sectionId])){
                $pageThumb=$this->router->config->imgURL.'/200/'.$this->router->sectionId.$this->router->_png;
            }
            elseif ($this->router->rootId && isset($this->router->pageRoots[$this->router->rootId])){
                $pageThumb=$this->router->config->imgURL.'/'.$this->router->rootId.$this->router->_png;
            }
            
            ?><meta property="og:title" content="<?= ($this->router->watchId || $this->userFavorites) ? $this->lang['title_full']:$this->title ?>" /><meta property="og:description" content="<?= $this->lang['description'] ?>" /><meta property="og:type" content="website" /><?php
            ?><meta property="og:url" content="<?= $sharingUrl ?>" /><?php 
            ?><meta property="og:image" content="<?= $pageThumb ?>" /><?php 
            ?><meta property="og:site_name" content="Mourjan.com" /><?php 
            ?><meta property="fb:app_id" content="184370954908428"/><?php
        }
        
        ?><meta name="msapplication-config" content="<?= $this->router->config->host ?>/browserconfig.xml" /><?php 
        if($this->user->info['id']==0 && \in_array($this->router->module,['home','signin','favorites','account','myads','post','statement','watchlist','signup','password','buy','buyu'])) {
            ?><script async="true" defer="true" src='https://www.google.com/recaptcha/api.js<?= $this->router->isArabic()?'?hl=ar':'' ?>'></script><?php
        }                
    }

    
    function supportedCountries() : array {
        $cc=['ae'=>null, 'sa'=>null, 'kw'=>null, 'bh'=>null, 'qa'=>null, 'om'=>null, 
             'lb'=>null, 'jo'=>null, 'iq'=>null, 
             'eg'=>null, 'ma'=>null, 'tn'=>null, 'dz'=>null];
        $result=[];     
        foreach ($this->router->countries as $id => $cn) {
            if ($cn['uri']==='ye') {  continue;  }
            if (!isset($cc[$cn['uri']])) { $cc['uri']=null; }
            
            if ($cn['uri'] && $cc[$cn['uri']]===null) {
                $cc[$cn['uri']] = ['p'=>$this->router->getURL($id), 'n'=>$cn['name'], 'c'=>[]];
                //$cc[$cn['uri']] = "<dt><a href={$this->router->getURL($id)}><i class=\"icn icn-{$cn['uri']}\"></i><span>{$cn['name']}</span></a></dt>\n";
            }
            
            foreach ($cn['cities'] as $cid=>$city) {
                $href = $this->router->getURL($id, $cid);
                $cc[$cn['uri']]['c'][]=['p'=>$href, 'n'=>$city['name']];
                //$cc[$cn['uri']].= "<dd><a href={$href}>{$city['name']}</a></dd>\n";
            }
            if ($cc[$cn['uri']]) {
                $result[$cn['uri']]=$cc[$cn['uri']];
            }
        }
        $this->router->logger()->info(\json_encode($result, JSON_PRETTY_PRINT));        
        return $result;
    }
    
    
    function footer() : void {
        $year = date('Y');
        if ($this->router->module==='index') {
        $words=['sell'=>['ar'=>'بع', 'en'=>'SELL'],'car'=>['ar'=>'سيارتك','en'=>'YOUR CAR'],
            'find'=>['ar'=>'إحصل', 'en'=>'FIND'], 'job'=>['ar'=>'على عمل', 'en'=>'A JOB'],
            'advert'=>['ar'=>'أعلن', 'en'=>'ADVERTISE'], 'business'=>['ar'=>'عن أعمالك', 'en'=>'YOUR BUSINESS'],
            'buy'=>['ar'=>'إشتري', 'en'=>'BUY'], 'house'=>['ar'=>'منزل', 'en'=>'A HOUSE'],
            'promote'=>['ar'=>'سّوق', 'en'=>'PROMOTE'], 'service'=>['ar'=>'خدماتك', 'en'=>'YOUR SERVICES'],
            ];
        $ln=$this->router->language;
        ?><div class="row ff-cols viewable" style="box-shadow:0 -5px 5px -5px var(--mColor10);">            
            <div class="col-12 mhbanner" style="margin-top:0">
                <img src="/web/css/1.0/assets/emblem.svg" />
                <div class="p1">                    
                    <div><span class="um"><?=$words['sell'][$ln]?></span><span class="sm l1"><?=$words['car'][$ln]?></span></div>
                    <div><span class="um"><?=$words['find'][$ln]?></span><span class="sm l2"><?=$words['job'][$ln]?></span></div>
                    <div><span class="um"><?=$words['advert'][$ln]?></span><span class="sm l3"><?=$words['business'][$ln]?></span></div>
                    <div><span class="um"><?=$words['buy'][$ln]?></span><span class="sm l4"><?=$words['house'][$ln]?></span></div>
                    <div><span class="um"><?=$words['promote'][$ln]?></span><span class="sm l5"><?=$words['service'][$ln]?></span></div>
                </div>                       
            </div>                        
            <div class="col-12 mfbanner"><span style="width:177px"></span><div class=slogan><?=$this->lang['slogan']?>.</div><a class="btn" href='#'><?=$this->lang['placeAd']?></a></div>
        </div><?php
        }
        
        ?><div class="row">
            <div class="col-12" style="background-color:white;height: 90px;align-items: center;justify-content: center;line-height: 1.8em">
                <img src="/web/css/1.0/assets/premium-en.svg" width="284"/>
                <span style="height:43px;width:2px;background-color:var(--mColor03);margin: 0 24px"></span>
                <div style="color: var(--mColor03);">
                <span style="font-size:20pt;font-weight:bold"><?=$this->lang['go_premium']?>!</span><br>
                <span style="font-size:16pt;"><?=$this->lang['gold_note']?>.&nbsp;&nbsp;<a href="#"><?=$this->lang['learn_more']?></a></span>
                </div>
            </div>            
        </div><?php
        
               
        $scn=$this->supportedCountries();
        $cc=[];
        foreach ($scn as $k=>$v) {
            $cc[$k]="<dt><a href={$v['p']}><i class=\"icn icn-{$k}\"></i><span>{$v['n']}</span></a></dt>";    
            foreach ($v['c'] as $cts) {
                $cc[$k].= "<dd><a href={$cts['p']}>{$cts['n']}</a></dd>";
            }
        }
        
        echo '</main>';
        
        ?><footer class=row><div class="viewable ff-rows"><?php
        ?><div class="col-4 ff-cols"><?php
        ?><img class=invert src="/web/css/1.0/assets/mc-en.svg" width=160/><?php
        //<!--<div class="apps bold" style="margin-inline-start:40px;">24/7 Customer Service<br/>+961-70-424-018</div>-->
        if ($this->router->isArabic()) {
            ?><p style="margin-inline-start:40px;">مركز الأعمال راكز<br/>رأس الخيمة<br/>الامارات العربية المتحدة</br/>صندوق بريد: 294474</p><?php
            ?><p style="margin-inline-start:40px;margin-top:0">بيريسوفت، الطابق الرابع ، سنتر 1044 الدكوانة<br/>شارع السلاف العريض، الدكوانة، لبنان</p><?php
            
        }
        else {
            ?><p style="margin-inline-start:40px;">Business Center RAKEZ<br/>Ras Al Khaimah<br/>United Arab Emirates</br/>P.O. Box: 294474</p><?php
            ?><p style="margin-inline-start:40px;margin-top:0">Berysoft, 4th floor, Dekwaneh 1044 center<br/>New Slav Street, Dekwaneh, Lebanon</p><?php
        }
        ?><p class=ha-start style="margin-inline-start:40px;margin-top:8px">© 2010-<?=$year?> <?=$this->lang['mc']?><br/><?=$this->lang['all_rights']?>.</p><?php
        ?></div><?php
        
        ?><div class="col-4 va-start"><ul><?php
        //if ($this->user()->id()) {
        //    $balance_label= $this->lang['myBalance']. ' is '.$this->user()->getBalance() . ' coins';
        //    echo '<li><a href="', $this->router->getLanguagePath('/statement/'), '"><i class="icn icnsmall icn-84"></i><span>', $balance_label, '</span></a></li>';
        //}
        ?><li><a href="<?=$this->router->getLanguagePath('/about/')?>"><span><?=$this->lang['aboutUs']?></span></a></li><?php
        ?><li><a href="<?=$this->router->getLanguagePath('/contact/')?>"><span><?=$this->lang['contactUs']?></span></a></li><?php
        ?><li><a href="<?=$this->router->getLanguagePath('/contact/')?>"><span><?=$this->lang['faqhc']?></span></a></li><?php
        ?><li><a href="<?=$this->router->getLanguagePath('/terms/')?>"><span><?=$this->lang['termsConditions']?></span></a></li><?php
        ?><li><a href="<?=$this->router->getLanguagePath('/privacy/')?>"><span><?=$this->lang['privacyPolicy']?></span></a></li><?php
        ?></ul></div><?php
        
        ?><div class="col-4 ff-cols"><ul><?php
        ?><li class="bold"><?=$this->lang['ex_deals_app']?>:</li>
            <li><div class=apps>
                <a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class=mios></span></a>
                <a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class=mandroid></span></a>
            </div></li>
            <li class="bold" style="border-bottom:none"><?=$this->lang['followUs']?> @mourjan&nbsp;&nbsp;&nbsp;
                <img class="invert" src="/web/css/1.0/fa/brands/facebook.svg" style="margin: 0 6px; width:30px"/>
                <img class="invert" src="/web/css/1.0/fa/brands/twitter.svg" style="margin: 0 6px; width:30px"/>
                <img class="invert" src="/web/css/1.0/fa/brands/instagram.svg" style="margin: 0 6px; width:30px"/>
            </li>            
            </ul></div></div><?php
            
        if ($this->router->module==='index') {
            ?><div class="row viewable"><div class=col-12><div class="card regions"><?php
            ?><header><i class="icn icn-region invert"></i><h4><span style="color:white;font-size:36px"><?=$this->lang['mourjan']?></span> <?=$this->lang['around_mst']?></h4></header><?php
            ?><div class=card-content><div class=row><?php
            echo '<dl class="dl col-4">', $cc['ae'], $cc['bh'], $cc['qa'], $cc['kw'], '</dl>', "\n"; 
            echo '<dl class="dl col-4">', $cc['sa'], $cc['om'], $cc['iq'], '</dl>', "\n"; 
            echo '<dl class="dl col-4">', $cc['lb'], $cc['jo'], $cc['eg'], $cc['ma'], $cc['tn'], $cc['dz'], '</dl>', "\n"; 
        ?></div></div></div></div></div><?php
        }

        ?></footer><?php
                       

       
        
        if (1) { return; }
        
        
        $adLang='';
        if ($this->router->language!="ar") $adLang=$this->router->language.'/';
        if ($this->router->module=='about') $this->router->cfg['enabled_sharing']=true;
        if ((!$this->user->info['id'] ||  ($this->user->info['id'] && $this->user->info['level']!=9)) && $this->router->module=='search' && !$this->userFavorites && !$this->router->watchId && !$this->router->userId) {
            $this->globalScript.='var upem=1;';
            if (isset($this->searchResults['media']) && $this->searchResults['media']['total_found']>0) {
                if ($this->searchResults['media']['total_found']>2) {
                    $k=0;
                    $images_widths = array();
                    $j=4;
                    $ad_cache = $this->router->db->getCache()->getMulti($this->searchResults['media']['matches']);
                    $ad_count = count($this->searchResults['media']['matches']);
                    if($j > $ad_count) $j = $ad_count;
                    if (!isset($this->stat['ad-imp'])) { $this->stat['ad-imp'] = []; }
                    ?><h4 class="peh w"><?= $this->lang['we_suggest'] ?></h4><?php
                    ?><ul class="pe pe<?= $j ?> w"><?php
                    
                    for ($ptr = 0; $ptr < $j; $ptr++) {
                        $id = $this->searchResults['media']['matches'][$ptr];
                        $ad = $this->classifieds->getById($id,false,$ad_cache);
                        if(is_null($ad[Classifieds::PICTURES]) || count($ad[Classifieds::PICTURES])==0){
                            continue;
                        }
                        if (isset($this->user->info['level'])) {
                            if (!($this->user->info['level'] == 9 || $this->user->info['id'] == $ad[Classifieds::USER_ID])) {
                                $this->stat['ad-imp'][]=$id;
                            }
                        } 
                        else {
                            if(isset($this->mediaResults["matches"][$id])){
                                $this->stat['ad-imp'][]=$id;
                            }
                        }
                        if (!empty($ad[Classifieds::ALT_CONTENT])) {
                            if ($this->router->language == "en" && $ad[Classifieds::RTL]) {
                                $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 0;
                            } elseif ($this->router->language == "ar" && $ad[Classifieds::RTL] == 0) {
                                $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 1;
                            }
                        }

                        $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit'] && $this->user->params['last_visit'] < $ad[Classifieds::UNIXTIME]);
                        $newSpan = '';
                        if ($isNewToUser) {
                            $newSpan.="<span class='nw'></span>";
                        }
                        $_link = sprintf($ad[Classifieds::URI_FORMAT], ($this->router->language == 'ar' ? '' : $this->router->language . '/'), $ad[Classifieds::ID]).'?ref=mediabox';
                        
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
                                    if($this->router->isAcceptWebP){
                                        $pic = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pic);
                                    }
                                    $this->globalScript.='sic["e' . $ad[Classifieds::ID] . '"]="<img class=\"ik'.$k.'\" src=\"' . $this->router->cfg['url_ad_img'] . '/repos/m/' . $pic . '\" />";';
                                }
                            }
                        }
                        
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
                    ?><br /><div class="adLnk"><a href="/post/<?= $this->router->language=='ar'?'':'en/' ?>" class="bt"><?= $this->lang['addAd'] ?></a></div><?php
                }
            }
            
        }
        else {
            $this->globalScript.='var upem=0;';
        }
        
        if ( ($this->router->module=='index' || $this->router->module=='about' || ($this->router->module=='search' && isset($this->searchResults['body']['total_found']) && $this->searchResults['body']['total_found']) ) && !$this->userFavorites && !$this->router->watchId && !$this->router->userId && $this->router->cfg['enabled_sharing']){
            ?><div class="sha sh <?= $this->router->language ?> rc w"><?php
                ?><div class="fr"><?php 
                    ?><label><?= $this->router->module=='search' ? $this->lang['shareUsSearch']:$this->lang['shareUs'] ?></label><?php 
                     ?><span class='st_facebook_hcount'></span><span class='st_twitter_hcount'></span><span class='st_googleplus_hcount'></span><span class='st_linkedin_hcount'></span><span class='st_email_hcount'></span><span class='st_sharethis_hcount'></span><?php 
                ?></div><?php
                ?><div class="fl"><?php
                    ?><label><?= $this->lang['followUs'] ?></label><a href="https://www.facebook.com/pages/Mourjan/318337638191015" target="_blank"><span class="fb-link"></span></a><a href="https://twitter.com/MourjanWeb" target="blank"><span class="tw-link"></span></a><a href="https://plus.google.com/104043262417362495551" rel="publisher" target="blank"><span class="gp-link"></span></a><?php
                ?></div><?php
            ?></div><?php 
        }elseif($this->router->module!='signin'){
            ?><br /><?php
        }
        
        if ($this->router->userId) {
            $year = date('Y');
            ?><div class="ftr"><div class="cr">© 2010-<?= $year ?> Mourjan.com Classifieds Aggregator - All Rights Reserved.<?php        
            if (!isset($this->user->info['level']) || $this->user->info['level']!=9){
                ?><br /><?php
                ?><script language="JavaScript" type="text/javascript">TrustLogo("https://www.mourjan.com/img/1.0.3/comodo.png", "CL1", "none");</script><?php
            }
        }
        else {
            ?><div class="ftr"><div class="w"><?php
            ?><div class="q0 q1 fl"><?php
            ?><b class="h"><?= $this->lang['mourjan'] ?></b><?php
            if ($this->router->config->get('enabled_post') && $this->router->module!='post') {
                if ($this->user->info['id']){
                    echo '<a class="nt" href="/post/'.$adLang.'">'.$this->lang['button_ad_post'].'</a>';
                }else {
                    echo '<a class="login nt" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['button_ad_post'].'</a>';
                }
            }
            if ($this->router->module!='about') {
                ?><a href="/about/<?= $adLang ?>"><?= $this->lang['aboutUs'] ?></a><?php
            }else {
                ?><b><?= $this->lang['aboutUs'] ?></b><?php
            }
            if ($this->router->module!='contact') {
                ?><a href="/contact/<?= $adLang ?>"><?= $this->lang['contactUs'] ?></a><?php
            }else {
                ?><b><?= $this->lang['contactUs'] ?></b><?php
            }
            if ($this->router->module!='gold') {
                ?><a href="/gold/<?= $adLang ?>"><?= $this->lang['gold_title'] ?></a><?php
            }else {
                ?><b><?= $this->lang['gold_title'] ?></b><?php
            }
            if ($this->router->module!='privacy') {
                ?><a href="/privacy/<?= $adLang ?>"><?= $this->lang['privacyPolicy'] ?></a><?php
            }else {
                ?><b><?= $this->lang['privacyPolicy'] ?></b><?php
            }
            if ($this->router->module!='terms') {
                ?><a itemprop="publishingPrinciples" href="/terms/<?= $adLang ?>"><?= $this->lang['termsConditions'] ?></a><?php
            }else {
                ?><b><?= $this->lang['termsConditions'] ?></b><?php
            }
            
            ?><form action="<?= $this->router->getURL($this->router->countryId,$this->router->cityId) ?>" method="post"><input type="hidden" name="mobile" value="1" /><a href='#' onclick="this.parentNode.submit();"><?= $this->lang['mobile'] ?></a></form><?php
       
            ?></div><?php 
            
            ?><div class="q1"><?php
            ?><b class="h"><?= $this->lang['pclassifieds_'.$this->router->language] ?></b><?php
            $cityId=$this->user->params['city'];
            $countryId=$this->user->params['country'];
            $currentRoot='';
            foreach ($this->router->pageRoots as $rid=>$root) {
                if($rid==$this->router->rootId)$currentRoot=($this->router->language=='ar' ? 'أقسام ال'.$root['name'] : $root['name'].'\''.($rid==1 ? 's':'').' Sections');
                $purposeId=0;
                ?><a href="<?= $this->router->getURL($countryId,$cityId,$rid,0,$purposeId) ?>"><span class='i i<?= $rid ?>'></span><?= ($this->router->language=='ar' ? 'إعلانات ال'.$root['name'] : $root['name']) ?></a><?php
            }
            ?></div><?php
            
            if($this->router->module=='search' || $this->router->module=='detail'){
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
          
            }else {
            
            
                ?><div class="q2"><?php
                ?><b class="h"><?= $this->lang['countries'] ?></b><?php                
                if($this->router->language=='en'){                
                    $index_1=6;
                    $index_2=13;
                } else {
                    $index_1=5;
                    $index_2=9;
                }
           
                $countryIDX=0;
                foreach ($this->router->countries as $country_id => $country) {
                    $countryIDX++;
                    if($countryIDX==$index_1 || $countryIDX==$index_2){
                        ?></div><div class="q2 qp"><?php
                    }
                    ?><a href="<?= $this->router->getURL($country_id, 0) ?>"><span class="cf c<?= $country_id ?>"></span><?= $country['name'] ?></a><?php
                    foreach ($country['cities'] as $city_id=>$city) {
                        echo '<a class="ct" href="'. $this->router->getURL($country_id, $city_id) .'">'.$city['name'].'</a>';                            
                    }
                    
                }
          
                ?></div><?php
                }

                $year = date('Y');
                ?><div class="cr">© 2010-<?= $year ?> Mourjan.com Classifieds Aggregator - All Rights Reserved.<?php 
                if (!isset($this->user->info['level']) || $this->user->info['level']!=9){
                    ?><br /><?php
                    ?><script language="JavaScript" type="text/javascript">TrustLogo("https://www.mourjan.com/img/1.0.3/comodo.png", "CL1", "none");</script><?php
                }
                ?></div><?php                    
            
        }
        ?></div></div><?php 
    }

    
    function _leading_pane(){
        ?><div class='col4'><?php $this->leading_pane() ?></div><?php
    }
    
    
    function leading_pane(){
    }
        
    
    function _main_pane(){
        ?><div class='col1'><?php
            $this->main_pane();
        ?></div><?php 
    }
    
    
    function main_pane(){}
    
    
    function _side_pane(){
        echo '<!--googleoff: snippet-->';
        ?><div class='col3'><?php $this->side_pane() ?></div><?php
        echo '<!--googleon: snippet-->';
    }
    
    
    function side_pane(){}
    
    
    function side_app_banner() {
        ?><div class="aps"><?php
        ?><h3><?= ($this->router->language == 'en' ? 'Download <span class="og">mourjan</span> App':'تحميل تطبيق <span class="og">مرجان</span>' ) ?></h3><?php
        ?><a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="ios"></span></a><?php
        ?><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="android"></span></a><?php
        ?></div><?php
    }
    
    
    function menu_app_banner() {
        ?><div class="mps"><?php
        ?><a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="mios"></span></a><?php
        ?><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="mandroid"></span></a><?php
        ?></div><?php
    }

    
    function body() : void {
        $this->main_pane();
    }
    
    
    function body_OLD() {
        $colSpread='col2w';
        if(!$this->hasLeadingPane){
            $colSpread='colw';
        }
        ?><div class="<?= $colSpread ?>"><?php   
        if(($this->router->module == 'buy'||$this->router->module=='buyu') && $this->user->info['id']){
            $this->renderBalanceBar();
        }  
        if ($this->router->userId) 
            $this->partnerHeader(); 
        /*if ($this->router->module=='index') {
            echo $this->fill_ad("zone_2", "adc");
        }*/
        /*elseif ($this->router->module=='detail' && !$this->detailAdExpired){
            echo $this->fill_ad("zone_8",'ad_det');
            echo $this->fill_ad("zone_9",'ad_det adx');
        }*/
        $this->_main_pane();
        if ($this->hasLeadingPane) {
            $this->_side_pane();
        }
      
        ?></div><?php
        //$this->renderNotifications();

        $this->footer();
    }

    function bodyMobile(){
        echo "<div id='main' class='main'>";
        $this->mainMobile();
       
        echo '</div>';
    }

    function mainMobile(){}

    /********************************************************************/
    /*                           rendering components                   */
    /********************************************************************/

    protected function set_analytics_header() {
        if (isset($this->user->info['level']) && $this->user->info['level']==9) {
            return;
        }
        
        if (preg_match('/Firefox\/27\.0/ui', $_SERVER['HTTP_USER_AGENT'])) {
            $this->router->config->disableAds();
        }
        if (0) {
        ?><script async src="https://www.googletagmanager.com/gtag/js?id=UA-435731-13"></script><?php
        ?><script type='text/javascript'><?php
        if ($this->router->config->enabledAds() && count($this->googleAds)) {
            ?>var googletag=googletag||{};googletag.cmd=googletag.cmd||[];(function(){var gads=document.createElement('script');gads.async=true;gads.type='text/javascript';var useSSL='https:'==document.location.protocol;gads.src=(useSSL?'https:':'http:')+'//www.googletagservices.com/tag/js/gpt.js';var node=document.getElementsByTagName('script')[0];node.parentNode.insertBefore(gads, node);})();googletag.cmd.push(function(){<?php
            
            $slot=0;
            foreach ($this->googleAds as $ad) {
                $slot++;
                echo "var slot{$slot}=googletag.defineSlot('{$ad[0]}',[{$ad[1]},{$ad[2]}],'{$ad[3]}').addService(googletag.pubads());";
            }
            //echo "googletag.pubads().collapseEmptyDivs();";
            
            if ($this->router->countryId) {
                echo "googletag.pubads().setTargeting('country_id','{$this->router->countryId}');";
            }
            
            if ($this->router->rootId) {
                echo "googletag.pubads().setTargeting('root_id','{$this->router->rootId}');";
            }
            
            if ($this->router->sectionId) {
                echo "googletag.pubads().setTargeting('section_id','{$this->router->sectionId}');";
            }
            
            if ($this->router->purposeId) {
                echo "googletag.pubads().setTargeting('purpose_id','{$this->router->purposeId}');";
            }
            else {
                echo "googletag.pubads().setTargeting('purpose_id','999');";                
            }
            
            ?>googletag.pubads().enableSingleRequest();googletag.enableServices()});<?php
        }
        
        $module = $this->router->module;
        if ($module=='search') {
            if  ($this->router->userId) {
                $module = 'user_page_'.$this->router->userId;
            }
            elseif ($this->userFavorites) {
                $module = 'favorites';
            }
            elseif ($this->router->watchId) {
                $module = 'watchlist';
            }
            elseif($this->router->isPriceList) {
                $module = 'pricelist';
            }
        }
        //echo "</script>\n\n";
            ?>window.dataLayer = window.dataLayer || [];<?php
            ?>function gtag(){dataLayer.push(arguments);}<?php
            ?>gtag('js', new Date());<?php

            ?>gtag('config', 'UA-435731-13', {<?php
                ?>'custom_map': {'dimension1': 'module', 'dimension2': 'root', 'dimension3': 'section', 'dimension4': 'country', 'dimension5': 'city'}<?php
            ?>});<?php
            ?>gtag('event', 'dimension_event', {<?php
                ?>'module': "<?php echo $module ?>",<?php
                ?>'root': "<?php echo $this->router->rootId?$this->router->roots[$this->router->rootId][2]:'AnyRoot';?>",<?php
                ?>'section': "<?php echo ($this->router->sectionId && isset($this->router->sections[$this->router->sectionId]))?$this->router->sections[$this->router->sectionId][2]:'AnySection'; ?>",<?php
                ?>'country': "<?php echo ($this->router->countryId && isset($this->router->countries[$this->router->countryId]))?$this->router->countries[$this->router->countryId]['uri']:'Global';?>",<?php
                ?>'city': "<?php echo ($this->router->cityId && isset($this->router->cities[$this->router->cityId]))?$this->router->cities[$this->router->cityId][3]:(($this->router->countryId && isset($this->router->countries[$this->router->countryId]))?$this->router->countries[$this->router->countryId]['uri'].'all cities':'Global');?>"<?php
            ?>});<?php
        ?></script><?php
           
        }
        
        
        if ($this->router->config->get('enabled_ads') && in_array($this->router->module,['search','detail'])) {
            ?><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><?php
        } 
        
        /*
        //if (!$this->isMobile){
        ?><script type="text/javascript"> //<![CDATA[ 
document.write(unescape("%3Cscript src='https://secure.comodo.com/trustlogo/javascript/trustlogo.js' type='text/javascript'%3E%3C/script%3E"));
//]]></script><?php
        //}*/
    }
    
      
    function renderMobileLinks(){
        if (!$this->router->countryId || $this->router->rootId) return;
        $lang=$this->router->language=='ar'?'':$this->router->language.'/';
        if ($this->router->rootId) return;
        ?><ul class="ls br"><?php
         ?><li><a href="/about/<?= $lang ?>"><span class="ic r102"></span><?= $this->lang['aboutUs'] ?><span class="to"></span></a></li><? 
         ?><li><a href="/contact/<?= $lang ?>"><span class="ic r100"></span><?= $this->lang['contactUs'] ?><span class="to"></span></a></li><? 
         ?><li><a href="/gold/<?= $lang ?>"><span class="mc24"></span><?= $this->lang['gold_title'] ?><span class="to"></span></a></li><? 
        ?></ul><?php
        ?><ul class="ls br"><?php

        //var_dump($this->router->isApp);

        if (!$this->router->isApp) {
            ?><li onclick="this.childNodes[0].submit()"><form action="<?= $this->router->getURL($this->router->countryId,$this->router->cityId) ?>" method="post"><input type="hidden" name="mobile" value="0" /><span class="ilnk"><span class="ic r101"></span><?= $this->lang['full_site'] ?></span><span class="to"></span></form></li><?php

        }
        ?></ul><?php  
        ?><ul class="ls br"><?php 
         ?><li><a itemprop="publishingPrinciples" href="/terms/<?= $lang ?>"><?= $this->lang['termsConditions'] ?><span class="to"></span></a></li><?php
         ?><li><a href="/privacy/<?= $lang ?>"><?= $this->lang['privacyPolicy'] ?><span class="to"></span></a></li><?php
        ?></ul><?php 
        
    }

    
    function loadMobileJs_classic() {
        if ($this->globalScript) {
            $this->globalScript=preg_replace('/\s+/', ' ', $this->globalScript);
            $this->globalScript=preg_replace("/[\n\t\r]/", '', $this->globalScript);
            $this->globalScript.=';';
            $this->globalScript=preg_replace("/;;/", ';', $this->globalScript);
        }
        
        if ($this->inlineScript) {
            $this->inlineScript=preg_replace('/\s+/', ' ', $this->inlineScript);
            $this->inlineScript=preg_replace("/[\n\t\r]/", '', $this->inlineScript);
            $this->inlineScript.=';';
            $this->inlineScript=preg_replace("/;;/", ';', $this->inlineScript);
        }
        
        
        ?><script type="text/javascript"><?php
        if ($this->renderAdSense && $this->router->config->enabledAds() 
                && in_array($this->router->module,['search','detail']) 
                /*&& (!isset($this->user->params['screen'][0]) || $this->user->params['screen'][0]<745)*/){
            /* ?>(adsbygoogle = window.adsbygoogle || []).push({google_ad_client: "ca-pub-2427907534283641",enable_page_level_ads: true,vignettes: {google_ad_channel: 'mourjan-vignette'},overlays: {google_ad_channel: 'mourjan-overlay'}});<?php */
            for($i=0; $i < $this->renderAdSense;$i++){
                ?>(adsbygoogle = window.adsbygoogle || []).push({});<?php
            }
        }
        //has Query Parameter
        ?>var head = document.getElementsByTagName("head")[0] || document.documentElement;<?php
        /* ?>function loadCss(fn,cb){var s=document.getElementsByTagName("link"),l=s.length-1,p=0,e;for(i=l;i>=0;i--){if(s[i].rel=='stylesheet'){e=s[i];break;}}if(typeof e==='undefined'){p=1;e=head.firstChild}var l=document.createElement('link');l.rel='stylesheet';l.type="text/css";l.media='all';l.href=fn;e.parentNode.insertBefore(l,e.nextSibling)}<?php */
        ?>function addEvent(e, en, fn){if (e.addEventListener)e.addEventListener(en, fn, false);else if(e.attachEvent)e.attachEvent('on' + en, fn)}<?php
        if($this->router->isApp)
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
        
        
        
        ?>var SCLD,lang='<?= $this->router->language ?>',<?php
        ?>hasQ=<?= $this->router->params['q'] ? 1:0 ?>,canSh=<?= $this->router->cfg['enabled_sharing']?1:0 ?>,<?php
        ?>sic=[],<?php
        ?>isApp=<?= $this->router->isApp ? "'".$this->router->isApp."'":0 ?>,<?php
        ?>uid=<?= $this->user->info['id'] ?>,<?php
        ?>mod='<?= $this->router->module ?>',<?php
        ?>jsLog=<?= $this->router->cfg['enabled_js_log'] ?>,<?php 
        ?>uimg='<?= $this->router->cfg['url_ad_img'] ?>',<?php 
        if(isset($this->user->params['hasCanvas'])){            
            ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
        }else{
            ?>tmp=document.createElement('canvas'),<?php
            ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
        }
        if($this->user->info['id']){
            ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
        }
        if($this->user->info['id'] && $this->router->module=='post'){
            ?>UP_URL='<?= $this->router->cfg['url_uploader'] ?>',<?php 
            ?>USID='<?= session_id() ?>',<?php 
            ?>uixf='<?= $this->router->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
        }
        if ($this->stat && !$this->router->isBot()){
            $this->stat['page']=($this->router->params['start']) ? $this->router->params['start'] : 1;
            $this->stat['num']=$this->num;
            ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
            $page=array(
                'cn'=>$this->router->countryId,
                'c'=>$this->router->cityId,
                'se'=>$this->router->sectionId,
                'pu'=>$this->router->purposeId,
            );
            ?>page='<?= json_encode($page) ?>',<?php
        }else {
            ?>stat=0,<?php
        }
        if($this->user->info['id'] && $this->router->module=='myads'){
            if ($this->router->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                ?>uhc='<?= $this->router->cfg['url_highcharts'] ?>',<?php 
                if($this->user->info['level']==9){
                    ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                }
            }else{
                ?>uhc=0,<?php 
            }
            if ($this->router->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
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
        if ($this->router->module == 'search' && !$this->userFavorites && !$this->router->watchId) {
            $key = $this->router->countryId . '-' . $this->router->cityId . '-' . $this->router->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->router->purposeId . '-' . crc32($this->router->params['q']);
            if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                    && ( ($this->router->countryId && $this->router->sectionId && $this->router->purposeId) 
                    || ($this->router->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) {
                ?>_cn=<?= $this->router->countryId ?>,<?php
                ?>_c=<?= $this->router->cityId ?>,<?php
                ?>_se=<?= $this->router->sectionId ?>,<?php
                ?>_pu=<?= $this->router->purposeId ?>,<?php
                ?>_ext=<?= $this->extendedId ?>,<?php
                ?>_loc=<?= $this->localityId ?>,<?php
                ?>_ttl='<span class="<?= $this->router->language ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                ?>_q='<?= $this->router->params['q'] ? addcslashes($this->router->params['q'], "'") :'' ?>',<?php
            }
        }
        ?>hrs=<?= isset($this->searchResults['body']['total_found']) && $this->searchResults['body']['total_found']>0 ? 1:0 ?>,<?php
        ?>hd='<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->router->language=='en'?'e':'a') ?>',<?php 
        ?>ucss='<?= $this->router->cfg['url_css_mobile'] ?>',<?php
        
        if ($this->router->module=='search' || $this->router->module=='detail' || $this->router->module=='myads'){
        /* ?>xCancel='<?= $this->lang['cancel'] ?>',<?php */
        ?>xAOK='<?= $this->lang['abuseReported'] ?>',<?php
        ?>xF='<?= $this->lang['sys_error'] ?>',<?php
        ?>since='<?= $this->lang['since'] ?>',<?php
        ?>ago='<?= $this->lang['ago'] ?>',<?php
        }
        if ($this->router->module=='account'){
            ?>xSaving='<?= $this->lang['savingProgress'] ?>',<?php
        }
        ?>_wsp=<?= (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) ? 0 : 1  ?>,<?php
        ?>ro=<?= $this->router->rootId ?>,<?php
        ?>cn=<?= $this->router->countryId ?>,<?php
        ?>c=<?= $this->router->cityId ?>,<?php
        ?>se=<?= $this->router->sectionId ?>,<?php
        ?>pu=<?= $this->router->purposeId ?>;<?php
        echo $this->globalScript;        
        echo $this->inlineScript;       
        ?>function inlineQS(){<?= $this->inlineQueryScript; ?>}<?php
        
        
        
        
        /*
        switch($this->router->module){
            case 'myads':
                if($this->user->info['id']) {
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_ads.js'<?php
                    ?>]);<?php
                }else{
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js'<?php
                    ?>]);<?php
                }
                break;
            case 'post':
                if($this->user->info['id']) {
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_post.js'<?php
                    ?>]);<?php
                }else{
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js'<?php
                    ?>]);<?php
                }
                break;
            case 'detail':
            case 'search':                
                ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                    ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                    ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_srh.js'<?php
                ?>]);<?php
                break;
            case 'account':
                if(!$this->user->info['id']) {
                    
                    ?>(function () {<?php
                        ?>var s=document.createElement('script');<?php
                        ?>s.type='text/javascript';<?php
                        ?>s.async=true;<?php
                        ?>s.defer=true;<?php
                        ?>s.src='<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js';<?php
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
                    ?>s.src='<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js';<?php
                    ?>var x=document.getElementsByTagName('script')[0];<?php
                    ?>x.parentNode.insertBefore(s,x);<?php
                ?>})();<?php 
                break;
        }*/
        ?></script><?php
        $renderMobileVerifyPage = $this->router->module=='password' || ($this->router->module=='post' && $this->user->info['id'] && !$this->isUserMobileVerified);
        if(!$renderMobileVerifyPage){
            ?><script type="text/javascript" onload="inlineQS()" defer="true" src="<?= $this->router->cfg['url_jquery_mobile'] ?>zepto.min.js"></script><?php
        }
        switch($this->router->module){
            case 'myads':
                if($this->user->info['id']) {
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_ads.js"></script><?php
                }
                break;
            case 'post':
                if($this->user->info['id'] && $this->isUserMobileVerified) {
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_post.js"></script><?php
                }elseif($renderMobileVerifyPage){
                    ?><script defer="true" type="text/javascript" onload="inlineQS()" src="<?= $this->router->cfg['url_jquery_mobile'] ?>jquery.mob.min.js"></script><?php
                    ?><script defer="true" type="text/javascript" onload="$('#code').select2({language:'<?= $this->router->language ?>',dir:'<?= $this->router->language=='ar'?'rtl':'ltr' ?>'})" src="<?= $this->router->cfg['url_jquery'] ?>select2.min.js"></script><?php
                }
                break;
            case 'detail':
            case 'search':
                ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_srh.js"></script><?php
                break;
            case 'account':
                if($this->user->info['id']) {
                ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_acc.js"></script><?php
                }
                break;
            case 'contact':
                ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_cnt.js"></script><?php
                break;
            case 'password':
                ?><script defer="true" type="text/javascript" onload="inlineQS()" src="<?= $this->router->cfg['url_jquery_mobile'] ?>jquery.mob.min.js"></script><?php
                ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js_mobile'] ?>/m_pwd.js"></script><?php
                break;

            case 'index':
            default:
                break;
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
        
        if ($this->router->module=='index' && !$this->router->rootId && $this->router->countryId) 
        { 
            ?><div id="fb-root"></div><?php
        }
        
        ?><script type="text/javascript"><?php
        //has Query Parameter
        ?>var head=document.getElementsByTagName("head")[0]||document.documentElement;<?php
        
        if($this->router->isApp)
        {
            ?>function addEvent(d,e,c){if(d.addEventListener){d.addEventListener(e,c,false);}else if(d.attachEvent){d.attachEvent(e,c);}};<?php
            ?>addEvent(document,'DOMContentLoaded',function(){parent.postMessage('DOMContentLoaded','*');});
            window.onpagehide=function(){parent.postMessage('pageHide','*');return null;};<?php
        }
                
        
        ?>var SCLD,lang='<?= $this->router->language ?>',<?php
        ?>hasQ=<?= $this->router->params['q'] ? 1:0 ?>,canSh=<?= $this->router->cfg['enabled_sharing']?1:0 ?>,<?php
        ?>sic=[],<?php
        ?>isApp=<?= $this->router->isApp ? "'".$this->router->isApp."'":0 ?>,<?php
        ?>uid=<?= $this->user->info['id'] ?>,<?php
        ?>mod='<?= $this->router->module ?>',<?php
        ?>jsLog=<?= $this->router->cfg['enabled_js_log'] ?>,<?php 
        ?>uimg='<?= $this->router->cfg['url_ad_img'] ?>',<?php 
        
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
        
        if($this->user->info['id'] && $this->router->module=='post')
        {
            ?>UP_URL='<?= $this->router->cfg['url_uploader'] ?>',<?php 
            ?>USID='<?= session_id() ?>',<?php 
            ?>uixf='<?= $this->router->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
        }
        
        if ($this->stat)
        {
            $this->stat['page']=($this->router->params['start']) ? $this->router->params['start'] : 1;
            $this->stat['num']=$this->num;
            ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
            $page=array(
                'cn'=>$this->router->countryId,
                'c'=>$this->router->cityId,
                'se'=>$this->router->sectionId,
                'pu'=>$this->router->purposeId,
            );
            ?>page='<?= json_encode($page) ?>',<?php
        } else {
            ?>stat=0,<?php
        }
        
        if($this->user->info['id'] && $this->router->module=='myads')
        {
            if ($this->router->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
            {
                ?>uhc='<?= $this->router->cfg['url_highcharts'] ?>',<?php 
                if($this->user->info['level']==9){
                    ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                }
            }
            else
            {
                ?>uhc=0,<?php 
            }
            
            if ($this->router->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
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
        
        if ($this->router->module == 'search' && !$this->userFavorites && !$this->router->watchId) 
        {
            $key = $this->router->countryId . '-' . $this->router->cityId . '-' . $this->router->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->router->purposeId . '-' . crc32($this->router->params['q']);
            if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                    && ( ($this->router->countryId && $this->router->sectionId && $this->router->purposeId) 
                    || ($this->router->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) 
            {
                ?>_cn=<?= $this->router->countryId ?>,<?php
                ?>_c=<?= $this->router->cityId ?>,<?php
                ?>_se=<?= $this->router->sectionId ?>,<?php
                ?>_pu=<?= $this->router->purposeId ?>,<?php
                ?>_ext=<?= $this->extendedId ?>,<?php
                ?>_loc=<?= $this->localityId ?>,<?php
                ?>_ttl='<span class="<?= $this->router->language ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                ?>_q='<?= $this->router->params['q'] ? addcslashes($this->router->params['q'], "'") :'' ?>',<?php
            }
        }
        
        ?>hrs=<?= isset($this->searchResults['body']['total_found']) && $this->searchResults['body']['total_found']>0 ? 1:0 ?>,<?php
        ?>hd='<?= ($this->detailAd && !$this->detailAdExpired) ? ($this->detailAd[Classifieds::RTL]==0 ? 'e':'a'):($this->router->language=='en'?'e':'a') ?>',<?php 
        ?>ucss='<?= $this->router->cfg['url_css_mobile'] ?>',<?php
        
        if ($this->router->module=='search' || $this->router->module=='detail' || $this->router->module=='myads')
        {
            /* ?>xCancel='<?= $this->lang['cancel'] ?>',<?php */
            ?>xAOK='<?= $this->lang['abuseReported'] ?>',<?php
            ?>xF='<?= $this->lang['sys_error'] ?>',<?php
            ?>since='<?= $this->lang['since'] ?>',<?php
            ?>ago='<?= $this->lang['ago'] ?>',<?php
        }
        
        if ($this->router->module=='account'){
            ?>xSaving='<?= $this->lang['savingProgress'] ?>',<?php
        }
        
        ?>ro=<?= $this->router->rootId ?>,<?php
        ?>cn=<?= $this->router->countryId ?>,<?php
        ?>c=<?= $this->router->cityId ?>,<?php
        ?>se=<?= $this->router->sectionId ?>,<?php
        ?>pu=<?= $this->router->purposeId ?>;<?php
        
        echo $this->globalScript;
        ?>
        head.addEventListener("load",function(event){if(event.target.nodeName==="SCRIPT"){
            if(event.target.getAttribute("src").includes("jquery.min.js")||event.target.getAttribute("src").includes("zepto.min.js")){
                var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;<?php
                if ($this->router->cfg['site_production']) 
                {
                    switch($this->router->module)
                    {
                        case 'myads':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_fullads.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'post':
                            if ($this->user->info['id'] && $this->isUserMobileVerified) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_fullpost.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_search.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'account':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_account.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'contact':
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_contact.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'password':
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_password.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'index':
                        default:
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            break;
                    }
                } 
                else 
                {
                    switch($this->router->module)
                    {
                        case 'myads':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_ads.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_srh.js';document.body.appendChild(sh);<?php 
                            break;

                        case 'post':
                            if ($this->user->info['id'] && $this->isUserMobileVerified) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_post.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            }
                            break;
                            
                        case 'account':
                            if ($this->user->info['id']) {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_acc.js';document.body.appendChild(sh);<?php 
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh)<?php 
                            }
                            break;
                            
                        case 'contact':
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                            ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_cnt.js';document.body.appendChild(sh);<?php 
                            break;
                        
                        case 'password':
                            if ($this->include_password_js) 
                            {
                                ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php 
                                ?>var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_pwd.js';document.body.appendChild(sh);<?php 
                                break;
                            }
                            
                        case 'index':
                        default:
                            ?>sh.src='<?= $this->router->cfg['url_js_mobile'] ?>/m_gen.js';document.body.appendChild(sh);<?php  
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
        
        if (isset($this->user->params['include_JSON']) && $this->router->module=='post')
        {
            ?><script type="text/javascript" src="<?= $this->router->cfg['url_jquery'] ?>json2.js"></script><?php
        }         

        ?><script type="text/javascript">
            var head = document.getElementsByTagName("head")[0] || document.documentElement;            
            var ucss='<?= $this->router->cfg['url_css'] ?>',uimg='<?= $this->router->cfg['url_ad_img'] ?>',<?php
            
            if(isset($this->user->params['hasCanvas']))
            {            
                ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
            }
            else
            {
                ?>tmp=document.createElement('canvas'),<?php
                ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
            }
            
            if($this->user->info['id'] && $this->router->module=='myads')
            {
                if ($this->router->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
                {
                    ?>uhc='<?= $this->router->cfg['url_highcharts'] ?>',<?php 
                    if($this->user->info['level']==9)
                    {
                        ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                    }
                }
                else
                {
                    ?>uhc=0,<?php 
                }
                
                if ($this->router->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) 
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
            
            if($this->router->module=='search')
            {
                if($this->userFavorites || $this->router->watchId)
                {
                    ?>ubs='',<?php
                }else{
                    $tmp=$this->router->language;
                    $this->router->language='ar';
                    ?>ubs='<?= $this->router->getURL($this->router->countryId,$this->router->cityId) ?>',<?php                 
                    $this->router->language=$tmp;
                }
            }
            
            ?>AID=<?= (isset($this->detailAd[Classifieds::ID]) && !$this->detailAdExpired ? $this->detailAd[Classifieds::ID] : 0) ?>,<?php 
            ?>UID=<?= $this->user->info['id'] ?>,<?php             
            
            if($this->user->info['id']) 
            { 
                ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
                ?>JWT='<?= $this->user->data ? $this->user->data->getToken():'' ?>',<?php
                ?>SSID='<?= md5($this->user->info['idKey'].'nodejs.mourjan') 
            ?>',<?php             
            }
            
            ?>PID=<?= $this->router->userId ? 1:0 ?>,<?php 
            ?>ULV=<?= isset($this->user->info['level']) ? $this->user->info['level'] : 0 ?>,<?php 
            ?>ujs='<?= $this->router->cfg['url_js'] ?>',<?php 
            
            if($this->user->info['id'] && $this->router->module=='post')
            {
                ?>UP_URL='<?= $this->router->cfg['url_uploader'] ?>',<?php 
                ?>USID='<?= session_id() ?>',<?php 
                ?>uixf='<?= $this->router->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
            }
            
            ?>lang='<?= $this->router->language ?>',<?php
            ?>share=<?= $this->router->cfg['enabled_sharing'] ? 1:0 ?>,<?php
            ?>hads=<?= $this->router->config->enabledAds() ? 1:0 ?>,<?php
            ?>SCLD=0,<?php //script loading var
            ?>ITC=0,<?php //is touch flag 
            ?>jsLog=<?= $this->router->cfg['enabled_js_log'] ?>,<?php 
            ?>MOD="<?= $this->router->module ?>",<?php
            ?>STO=(typeof(Storage)==="undefined"?0:1),<?php
            ?>WSO=(typeof(WebSocket)==="undefined"?0:1),<?php
            
            if($this->router->module=='detail' && !$this->detailAdExpired && ( ($this->detailAd[Classifieds::LATITUDE]  || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]) )  ) 
            {
                ?>hasMap=1,LAT=<?= $this->detailAd[Classifieds::LATITUDE] ?>,LON=<?= $this->detailAd[Classifieds::LONGITUDE] ?>,<?php
                ?>DTTL="<?= htmlspecialchars($this->title, ENT_QUOTES) ?>",<?php
            }
            
            //menu slider vars
            ?>tmr,tmu,tmd,func,fupc,mul,menu,mp,<?php
            
            if ($this->stat) {
                $this->stat['page']=($this->router->params['start']) ? $this->router->params['start'] : 1;
                $this->stat['num']=$this->num;
                ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
                $page=array(
                    'cn'=>$this->router->countryId,
                    'c'=>$this->router->cityId,
                    'se'=>$this->router->sectionId,
                    'pu'=>$this->router->purposeId,
                );
                ?>page='<?= json_encode($page) ?>',<?php
            }
            else 
            {
                ?>stat=0,<?php
            }
            
            if ($this->router->module == 'search' && !$this->userFavorites && !$this->router->watchId) 
            {
                $key = $this->router->countryId . '-' . $this->router->cityId . '-' . $this->router->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->router->purposeId . '-' . crc32($this->router->params['q']);
                if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                        && ( ($this->router->countryId && $this->router->sectionId && $this->router->purposeId) 
                        || ($this->router->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) 
                {
                    ?>_cn=<?= $this->router->countryId ?>,<?php
                    ?>_c=<?= $this->router->cityId ?>,<?php
                    ?>_se=<?= $this->router->sectionId ?>,<?php
                    ?>_pu=<?= $this->router->purposeId ?>,<?php
                    ?>_ext=<?= $this->extendedId ?>,<?php
                    ?>_loc=<?= $this->localityId ?>,<?php
                    ?>_ttl='<span class="<?= $this->router->language ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                    ?>_q='<?= $this->router->params['q'] ? addcslashes($this->router->params['q'], "'") :'' ?>',<?php
                }
            }
            
            ?>ICH='<?= $this->includeHash ?>',<?php
            ?>LSM='<?= $this->router->last_modified ?>';<?php
            echo $this->globalScript;
            ?>            
            head.addEventListener("load",function(event){if(event.target.nodeName==="SCRIPT"){
                if (event.target.getAttribute("src").includes("jquery.min.js")||event.target.getAttribute("src").includes("zepto.min.js")){
                    var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;
                    <?php
                    switch($this->router->module)
                    {
                        case 'signin':
                            ?>sh.src='<?= $this->router->cfg['url_js'] ?>/signin.js';head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'detail':
                        case 'search':
                            ?>sh.src='<?= $this->router->cfg['url_js'] ?>/search.js';head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'myads':
                            if($this->user->info['id']){
                                if($this->user->info['level']==9){     
                                    if($this->router->cfg['site_production']){
                                        ?>sh.src='https://h5.mourjan.com/js/3.5.1/myadsad.js';<?php
                                    }else{
                                        ?>sh.src='<?= $this->router->cfg['url_js'] ?>/myadsad.js';<?php 
                                    }
                                }else{
                                    ?>sh.src='<?= $this->router->cfg['url_js'] ?>/myads.js';<?php                                            
                                }
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
                
                        case 'account':
                            if($this->user->info['id']) {
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/account.js';<?php                                            
                            }else{
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
            
                        case 'post':
                            if($this->user->info['id'] && $this->isUserMobileVerified) {
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/post.js';<?php                                            
                            }
                            else {
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/gen.js';<?php
                            }
                            ?>head.insertBefore(sh,head.firstChild);<?php 
                            break;
                            
                        case 'password':
                            if($this->include_password_js) {
                                ?>sh.src='<?= $this->router->cfg['url_js'] ?>/pwd.js';head.insertBefore(sh,head.firstChild);<?php 
                            }
                            break;
                                
                        case 'index':
                        default:
                            ?>(function(){sh.src='<?= $this->router->cfg['url_js'] ?>/gen.js';<?php 
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
                /*
                ?>var xm=document.createElement('script'); po.type='text/javascript'; xm.async=true;<?php
                ?>xm.src='<?=$this->router->cfg['url_js']?>/strophe.js';<?php
                ?>var sx=document.getElementsByTagName('script')[0]; sx.parentNode.insertBefore(xm,sx);<?php
                */
            ?>})();<?php
        
        ?></script><?php
    }
    
    
    function prepare_js() {
        $requires = [];        
        if ($this->router->isMobile) {               
            $renderMobileVerifyPage = $this->router->module=='password' || ($this->router->module=='post' && $this->user->info['id'] && !$this->isUserMobileVerified);
            if (!$renderMobileVerifyPage) {
                $requires[] = $this->router->config->get('url_jquery_mobile') . 'zepto.min.js';
            }
            switch($this->router->module){
                case 'myads':
                    if($this->user->info['id']) {
                        $requires[] = $this->router->cfg['url_js_mobile'] . '/m_ads.js';
                    }
                    break;
                case 'post':
                    if($this->user->info['id'] && $this->isUserMobileVerified) {
                        $requires[] = $this->router->cfg['url_js_mobile'] . '/m_post.js';
                    }elseif($renderMobileVerifyPage){
                        $requires[] = $this->router->cfg['url_jquery_mobile'] . '/jquery.mob.min.js';
                        $requires[] = $this->router->cfg['url_jquery'] . '/select2.min.js';
                    }
                    break;
                case 'detail':
                case 'search':
                    $requires[] = $this->router->cfg['url_js_mobile'] . '/m_srh.js';
                    break;
                case 'account':
                    if($this->user->info['id']) {
                    $requires[] = $this->router->cfg['url_js_mobile'] . '/m_acc.js';
                    }
                    break;
                case 'contact':
                    $requires[] = $this->router->cfg['url_js_mobile'] . '/m_cnt.js';
                    break;
                case 'password':
                    $requires[] = $this->router->cfg['url_jquery_mobile'] . '/jquery.mob.min.js';
                    $requires[] = $this->router->cfg['url_js_mobile'] . '/m_pwd.js';
                    break;

                case 'index':
                default:
                    break;
            }            
        }
        else {       
            switch ($this->router->module) {
                case 'signin':
                    $requires[] = $this->router->cfg['url_jquery'] . 'socket.io-1.4.5.js';
                    $requires[] =  $this->router->cfg['url_js'] . '/signin.js';
                    break;
                case 'detail':
                case 'search':
                    $requires[] = $this->router->cfg['url_js'] . '/search.js';
                    break;
                case 'myads':
                    if($this->user->info['id']){
                        $requires[] = $this->router->cfg['url_jquery'] . 'socket.io-1.4.5.js';
                        if($this->user->info['level']==9){     
                            if($this->router->cfg['site_production']){
                                $requires[] = 'https://h5.mourjan.com/js/3.5.1/myadsad.js';
                            }
                            else{
                                $requires[] = $this->router->cfg['url_js'] . '/myadsad.js';
                            }
                        }
                        else{
                            $requires[] = $this->router->cfg['url_js'] . '/myads.js';                                          
                        }
                    }
                    break;
                case 'account':
                    if($this->user->info['id']){
                        $requires[] = $this->router->cfg['url_js'] . '/account.js';
                    }
                    break;
                case 'post':
                    if($this->user->info['id'] && $this->isUserMobileVerified){                    
                        $requires[] = $this->router->cfg['url_js'] . '/post.js';
                    }elseif($this->user->info['id']){
                        $requires[] = $this->router->cfg['url_jquery'] . 'select2.min.js';
                    }
                    break;
                case 'password':
                    if($this->include_password_js){
                        $requires[] = $this->router->cfg['url_js'] . '/pwd.js';
                    }
                    break;
                case 'index':                
                default:
                    break;
            }
        }
        
        $this->requires['js'] = $requires;
    }
    
    
    function load_js_classic() {
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
        if(isset($this->user->params['include_JSON']) && $this->router->module=='post'){
            ?><script type="text/javascript" async="true" defer="true" src="<?= $this->router->cfg['url_jquery'] ?>json2.js"></script><?php
        }         
        /*
        if($this->router->module=='buy' && $this->user->info['id']){
            ?><script src='https://www.paypalobjects.com/js/external/dg.js' type='text/javascript'></script><?php
        }
         * 
        */
        ?><script type="text/javascript"><?php 
        /*
            if($this->router->module=='buy' && $this->user->info['id']){
                ?>var dg = new PAYPAL.apps.DGFlow({trigger: ['sub0','sub1','sub2','sub3'],expType: 'instant'});<?php
            }
         * 
         */
            ?>var head = document.getElementsByTagName("head")[0] || document.documentElement;<?php
            ?>function addEvent(e, en, fn){if (e.addEventListener)e.addEventListener(en, fn, false);else if(e.attachEvent)e.attachEvent('on' + en, fn)}<?php
        
            ?>var ucss='<?= $this->router->config->cssURL ?>',<?php 
            ?>uimg='<?= $this->router->config->adImgURL ?>',<?php 
            if(isset($this->user->params['hasCanvas'])){            
                ?>hasCvs=<?= $this->user->params['hasCanvas'] ?>,<?php 
            }else{
                ?>tmp=document.createElement('canvas'),<?php
                ?>hasCvs=!!(tmp.getContext && tmp.getContext('2d')),<?php
            }
            if($this->user->info['id'] && $this->router->module=='myads'){
                if ( $this->router->cfg['enabled_charts'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
                    ?>uhc='<?= $this->router->cfg['url_highcharts'] ?>',<?php 
                    if($this->user->info['level']==9){
                        ?>uuid=<?= (isset($_GET['u']) && is_numeric($_GET['u'])) ? (int)$_GET['u'] : 0 ?>,<?php
                    }
                }else{
                    ?>uhc=0,<?php 
                }
                if ($this->router->cfg['enabled_ad_stats'] && (!isset($_GET['sub']) || (isset($_GET['sub']) && $_GET['sub']=='archive') )) {
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
            if($this->router->module=='search'){
                if($this->userFavorites || $this->router->watchId){
                    ?>ubs='',<?php
                }else{
                    $tmp=$this->router->language;
                    $this->router->language='ar';
                    ?>ubs='<?= $this->router->getURL($this->router->countryId,$this->router->cityId) ?>',<?php                 
                    $this->router->language=$tmp;
                }
            }
            ?>AID=<?= (isset($this->detailAd[Classifieds::ID]) && !$this->detailAdExpired ? $this->detailAd[Classifieds::ID] : 0) ?>,<?php 
            ?>UID=<?= $this->user->info['id'] ?>,<?php 
            if($this->user->info['id']) { 
                ?>UIDK='<?= $this->user->info['idKey'] ?>',<?php 
                ?>JWT='<?= $this->user->data ? $this->user->data->getToken():'' ?>',<?php
                ?>SSID='<?= md5($this->user->info['idKey'].'nodejs.mourjan') 
            ?>',<?php }
            ?>PID=<?= $this->router->userId ? 1:0 ?>,<?php 
            ?>ULV=<?= isset($this->user->info['level']) ? $this->user->info['level'] : 0 ?>,<?php 
            ?>ujs='<?= $this->router->config->jsURL ?>',<?php 
            
            if($this->user->info['id'] && $this->router->module=='post'){
                ?>UP_URL='<?= $this->router->cfg['url_uploader'] ?>',<?php 
                ?>USID='<?= session_id() ?>',<?php 
                ?>uixf='<?= $this->router->cfg['url_image_lib'] ?>/load-image.all.min.js',<?php 
            }
            
            ?>lang='<?= $this->router->language ?>',<?php
            ?>share=<?= $this->router->config->get('enabled_sharing') ? 1:0 ?>,<?php
            ?>hads=<?= $this->router->config->enabledAds() ? 1:0 ?>,<?php
            ?>SCLD=0,<?php //script loading var
            ?>ITC=0,<?php //is touch flag 
            ?>jsLog=<?= $this->router->config->get('enabled_js_log') ?>,<?php 
            ?>MOD="<?= $this->router->module ?>",<?php
            ?>STO=(typeof(Storage)==="undefined"?0:1),<?php
            ?>WSO=(typeof(WebSocket)==="undefined"?0:1),<?php
            if($this->router->module=='detail' && !$this->detailAdExpired && ( ($this->detailAd[Classifieds::LATITUDE]  || $this->detailAd[Classifieds::LONGITUDE]) && is_numeric($this->detailAd[Classifieds::LATITUDE]) && is_numeric($this->detailAd[Classifieds::LONGITUDE]) )  ) {
                ?>hasMap=1,LAT=<?= $this->detailAd[Classifieds::LATITUDE] ?>,LON=<?= $this->detailAd[Classifieds::LONGITUDE] ?>,<?php
                ?>DTTL="<?= htmlspecialchars($this->title, ENT_QUOTES) ?>",<?php
            }
            //menu slider vars
            ?>tmr,tmu,tmd,func,fupc,mul,menu,mp,<?php
            //if ($this->router->cfg['enabled_disqus'] && $this->router->module=='detail' && !$this->detailAdExpired && $this->detailAd[Classifieds::PUBLICATION_ID]==1) {
            //    disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},disqus_identifier = '<?= $this->detailAd[Classifieds::ID]',
            //}elseif ($this->router->cfg['enabled_disqus'] && $this->router->module=='myads'){
            //    disqus_shortname = 'mourjan',disqus_config=function(){this.language = 'en'},
            //}
            if ($this->stat && $this->router->isBot()){
                $this->stat['page']=($this->router->params['start']) ? $this->router->params['start'] : 1;
                $this->stat['num']=$this->num;
                ?>stat='<?= isset($this->stat) ? json_encode($this->stat):'' ?>',<?php
                $page=array(
                    'cn'=>$this->router->countryId,
                    'c'=>$this->router->cityId,
                    'se'=>$this->router->sectionId,
                    'pu'=>$this->router->purposeId,
                );
                ?>page='<?= json_encode($page) ?>',<?php
            }else {
                ?>stat=0,<?php
            }
            if ($this->router->module == 'search' && !$this->userFavorites && !$this->router->watchId) {
                $key = $this->router->countryId . '-' . $this->router->cityId . '-' . $this->router->sectionId . '-' . $this->extendedId . '-' . $this->localityId . '-' . $this->router->purposeId . '-' . crc32($this->router->params['q']);
                if ( (!$this->user->info['id'] || ($this->user->info['id'] && !isset($this->user->info['options']['watch'][$key])) ) 
                        && ( ($this->router->countryId && $this->router->sectionId && $this->router->purposeId) 
                        || ($this->router->params['q'] && $this->searchResults['body']['total_found'] < 100) ) ) {
                    ?>_cn=<?= $this->router->countryId ?>,<?php
                    ?>_c=<?= $this->router->cityId ?>,<?php
                    ?>_se=<?= $this->router->sectionId ?>,<?php
                    ?>_pu=<?= $this->router->purposeId ?>,<?php
                    ?>_ext=<?= $this->extendedId ?>,<?php
                    ?>_loc=<?= $this->localityId ?>,<?php
                    ?>_ttl='<span class="<?= $this->router->language ?>"><?= addcslashes(preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$this->title), "'") ?></span>',<?php
                    ?>_q='<?= $this->router->params['q'] ? addcslashes($this->router->params['q'], "'") :'' ?>',<?php
                }
            }
            ?>_wsp=<?= (isset($this->user->params['screen'][0]) && $this->user->params['screen'][0]) ? 0 : 1  ?>,<?php
            ?>ICH='<?= $this->includeHash ?>',<?php
            ?>LSM='<?= $this->router->last_modified ?>';<?php
            if(0 && in_array($this->router->module,['index','search','detail'])){ ?>loadCss(ucss+"/gen<?= $this->router->language=='ar'?'_ar':'' ?>.css");<?php }
            echo $this->globalScript;
            
                
                /*
                switch($this->router->module){
                case 'myads':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_ads.js'<?php
                        ?>])<?php
                    }else{

                    }
                    break;
                case 'post':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_post.js'<?php
                        ?>])<?php
                    }else{

                    }
                    break;
                case 'detail':
                case 'search':                
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                        ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_srh.js'<?php
                    ?>]);<?php
                    break;
                case 'account':
                    if($this->user->info['id']) {
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_acc.js'<?php
                        ?>]);<?php
                    }else{

                    }
                    break;
                case 'contact':
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_cnt.js'<?php
                        ?>]);<?php
                    break;
                case 'password':
                        ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                            ?>'<?= $this->router->cfg['url_jquery_mobile'] ?>/zepto.min.js',<?php
                            ?>'<?= $this->router->cfg['url_js_mobile'] ?>/m_pwd.js'<?php
                        ?>]);<?php
                    break;

                case 'index':
                default:
                    ?>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[<?php
                        ?>'<?= $this->router->cfg['url_jquery'] ?>/jquery.min.js'<?php
                    ?>]);<?php

                    break;
            }*/
        
                if($this->router->cfg['enabled_sharing'] && in_array($this->router->module,['index','search','detail'])){ 
                    ?>addEvent(window,'load',function(){<?php
                        ?>var po = document.createElement('script');<?php
                        ?>po.type = 'text/javascript';<?php 
                        ?>po.async = true;<?php
                        ?>po.src = 'https://apis.google.com/js/platform.js';<?php
                        ?>var s = document.getElementsByTagName('script')[0];<?php
                        ?>s.parentNode.insertBefore(po,s);<?php                         
                      ?>});<?php 
                }
        
            
        /*?>(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://':'http://')+'stats.g.doubleclick.net/dc.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();<?php*/
                ?>function inlineQS(){<?= $this->inlineQueryScript; ?>}<?php
        ?></script><?php
        ?><script type="text/javascript" onload="inlineQS()" defer="true" src="<?= $this->router->config->jQueryURL ?>jquery.min.js"></script><?php
        switch($this->router->module){
            case 'signin':
                ?><script type="text/javascript" src="<?= $this->router->cfg['url_jquery'] ?>socket.io-1.4.5.js"></script><?php
                ?><script type="text/javascript" src="<?= $this->router->cfg['url_js'] ?>/signin.js"></script><?php
                break;
            case 'detail':
            case 'search':
                ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/search.js"></script><?php
                /*?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/strophe.js"></script><?php*/
                break;
            case 'myads':
                if($this->user->info['id']){
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_jquery'] ?>socket.io-1.4.5.js"></script><?php
                    if($this->user->info['level']==9){     
                        if($this->router->cfg['site_production']){
                            ?><script type="text/javascript" defer="true" src="https://h5.mourjan.com/js/3.5.1/myadsad.js"></script><?php
                        }else{
                            ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/myadsad.js"></script><?php
                        }
                    }else{
                        ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/myads.js"></script><?php                                          
                    }
                }
                break;
            case 'account':
                if($this->user->info['id']){
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/account.js"></script><?php
                }
                break;
            case 'post':
                if($this->user->info['id'] && $this->isUserMobileVerified){                    
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/post.js"></script><?php
                }elseif($this->user->info['id']){
                    ?><script type="text/javascript" onload="$('#code').select2({language:'<?= $this->router->language ?>',dir:'<?= $this->router->language=='ar'?'rtl':'ltr' ?>'})" defer="true" src="<?= $this->router->cfg['url_jquery'] ?>select2.min.js"></script><?php
                }
                break;
            case 'password':
                if($this->include_password_js){
                    ?><script type="text/javascript" defer="true" src="<?= $this->router->cfg['url_js'] ?>/pwd.js"></script><?php
                }
                break;
            case 'index':                
            default:
                break;
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
        if ($this->router->config->enabledAds() && isset ($this->googleAds[$name])) {
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

    
    protected function render() {
        if ($this->router->isAMP && $this->router->module=='search') {
            
            ?><!doctype html><?php
            ?><html amp lang="<?= $this->router->language ?>"><?php
                $this->_headerAMP();
                $this->_bodyAMP();
            ?></html><?php
            /*echo '<!doctype html>', PHP_EOL;
            echo '<html amp lang="', $this->router->language, '" dir="', $this->router->isArabic() ? 'rtl' : 'ltr', '">', PHP_EOL, '<head>';
            echo '<meta charset="utf-8">', PHP_EOL,'<script async src="https://cdn.ampproject.org/v0.js"></script>', PHP_EOL;
            echo '<link rel="shortcut icon" href="https://www.mourjan.com/img/1.0.3/favicon.ico">', PHP_EOL;
            echo '<title>', $this->title, '</title>', PHP_EOL;
            echo '<link rel="canonical" href="https://www.mourjan.com', $this->router->uri, '">', PHP_EOL;
            echo '<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">', PHP_EOL;
            echo '<script type="application/ld+json">', PHP_EOL,
                '{', PHP_EOL,
                '"@context": "http://schema.org",', PHP_EOL,
                '"@type": "NewsArticle",', PHP_EOL,
                '"headline": "Open-source framework for publishing content",', PHP_EOL,
                '"datePublished": "2015-10-07T12:02:41Z",', PHP_EOL,
                '"image": [', PHP_EOL,
                  '"logo.jpg"', PHP_EOL,
                ']', PHP_EOL,
                '}', PHP_EOL,
            '</script>', PHP_EOL;
            echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>', PHP_EOL, 
                '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>', PHP_EOL;
            echo '<style amp-custom>', PHP_EOL;
            echo 'body{width: auto;margin: 0;padding:0;font-family: verdana, arial;}', PHP_EOL;
            echo 'header{background:#143D55;color:white;font-size: 1.5em;text-align: center;}', PHP_EOL;
            echo 'h1 {margin: 0;padding: 0.5em;background: white;box-shadow: 0px 3px 5px grey;}', PHP_EOL;
            echo '</style>', PHP_EOL;
            echo '</head>', PHP_EOL, '<body>';
            //var_export($this->searchResults);
            $this->ampBody();
            echo '</body></html>';*/
            return;
        }
        
        //if ($this->rss) {
        //    $this->_rss();
        //} 
        //else {
        $this->_header();
        $this->_body();
        $this->user->setStats();
        //}
    }
    

    function _headerAMP(){
        ?><head><?php
            ?><meta lang="<?= $this->router->language ?>"><?php
            ?><meta charset="utf-8"><?php
            ?><link rel="canonical" href="https://www.mourjan.com<?= $this->router->uri ?>"><?php
            ?><meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1"><?php
            ?><style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript><?php
            ?><script async src="https://cdn.ampproject.org/v0.js"></script><?php
            ?><script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script><?php
            ?><link href="https://fonts.googleapis.com/css?family=Cairo" rel="stylesheet"><?php
            $this->headerAMP();
        ?></head><?php
    }
    
    
    function _bodyAMP(){
        ?><body><?php
            $this->titleHeaderAMP();
            $this->sidebarAMP();
            $this->bodyAMP();
        ?></body><?php
    }
    
    
    function headerAMP(){
        
    }
    
    
    function bodyAMP(){
        
    }
    
    
    function titleHeaderAMP(){
        ?><header><?php
            ?><div role="button" on="tap:sidebar.toggle" tabindex="0">☰</div><?php
        ?></header><?php
    }
    
    
    function sidebarAMP(){        
        $lang=$this->router->language=='ar'?'':$this->router->language.'/';
        
        ?><amp-sidebar id="sidebar" layout="nodisplay" side="<?= $this->router->language == 'ar' ? 'right':'left' ?>"><?php
            
        ?><ul><?php            
                        
            ?><li><?php 
            if($lang){
                ?><a class="ar ctr" href="<?= $this->switchLangUrl ?>">تصفح باللغة العربية</a><?php
            }else{
                ?><a class="en ctr" href="<?= $this->switchLangUrl ?>">switch to English</a><?php
            }
            ?></li><?php
            
            $headTitle = $this->lang['mourjan'];
            if($this->router->cityId){
                $headTitle=$this->lang['mourjan'].' '.$this->cityName;
            }elseif($this->router->countryId){
                $headTitle=$this->lang['mourjan'].' '.$this->countryName;
            }
            if($this->router->module=='index' && !$this->router->rootId && $this->router->countryId){
                ?><li class="on"><b><span class="k home"></span><?= $headTitle ?></b></li><?php
            }else{
                ?><li><a href="<?= $this->router->getURL($this->router->countryId, $this->router->cityId); ?>"><span class="k home"></span><?= $headTitle ?></a></li><?php
            }
            if($this->user->info['id']){                
                if($this->router->module=='post'){    
                    ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></b></li><?php
                }else{
                    ?><li><a href="/post/<?= $lang ?>?clear=true"><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></a></li><?php
                }     
                ?><li class="sep"></li><?php   
                if($this->router->module != 'search' || !$this->userFavorites) {
                   ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></b></li><?php 
                }
                /*if($this->router->module != 'search' || !$this->router->watchId) {
                       ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="ic k eye on"></span><?= $this->lang['myList'] ?></b></li><?php 
                }*/
                ?><li class="sep"></li><?php
                if($this->router->module != 'balance') {
                    ?><li><a href="/statement/<?= $lang ?>"><span class="mc24"></span><?= $this->lang['myBalance'] ?><span class="n"></span></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="mc24"></span><?= $this->lang['myBalance'] ?></b></li><?php 
                }
                ?><li class="sep"></li><?php
                
                $sub=(isset($_GET['sub']) && $_GET['sub'] ? $_GET['sub']:'');
                if($this->router->module != 'myads' || ($this->router->module == 'myads' && $sub!='') ) {
                        ?><li><a href="/myads/<?= $lang ?>"><span class="ic aon"></span><?= $this->lang['ads_active'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic aon"></span><?= $this->lang['ads_active'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='pending') ) {
                        ?><li><a href="/myads/<?= $lang ?>?sub=pending"><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='drafts'))  {
                    ?><li><a href="/myads/<?= $lang ?>?sub=drafts"><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='archive'))  {
                    ?><li><a href="/myads/<?= $lang ?>?sub=archive"><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></b></li><?php
                }
            
            }else{
                if($this->router->module=='post'){    
                    ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></b></li><?php
                }else{
                    ?><li><a href="/post/<?= $lang ?>?clear=true"><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></a></li><?php
                }
            }
            ?><li class="sep"></li><?php            
            
            if($this->user->info['id']==0){
                if($this->router->module=='home'){
                    ?><li class="on"><b><span class="k log"></span><?= $this->lang['signin'] ?></b></li><?php 
                }else{
                    ?><li><a href="/home/<?= $lang ?>"><span class="k log"></span><?= $this->lang['signin'] ?></a></li><?php
                }
                ?><li class="sep"></li><?php
            }
            
            /*if($this->user->info['id']){
                ?><li class="sep"></li><?php
            }*/
            $countryId = $this->router->countryId;
            $cityId = $this->router->cityId; 
            if (isset($this->user->params['country']) && $this->user->params['country'])
                $countryId=$this->router->countryId=$this->user->params['country'];
            if (isset($this->user->params['city']) && $this->user->params['city'])
                $cityId=$this->router->cityId=$this->user->params['city'];
            //$this->router->pageRoots = $this->router->db->getRootsData($countryId, $cityId, $this->router->language);
            $this->router->pageRoots = $this->router->db->asRootsData($countryId, $cityId, $this->router->language);
            //roots
            $i=0;
            foreach ($this->router->pageRoots as $key=>$root) {
                $count=$this->checkNewUserContent($root['unixtime']) ? '<b>'.$root['counter'].'</b>' : $root['counter'];
                if($this->router->module=='index' && $this->router->rootId==$key){
                    echo '<li class="on"><b><span class="ic r',$key,'"></span>',
                    $root['name'], ($countryId ? '<span class="n">'. $count. '</span>':'') ,'</b></li>';
                }else{
                $_link = $this->router->getURL($countryId, $cityId, $key);        
                echo '<li><a href="', $_link, '"><span class="ic r',$key,'"></span>',
                    $root['name'], ($countryId ? '<span class="n">'. $count. '</span>':'') ,'</a></li>';
                }
                $i++;
            }            
            
            ?><li class="sep"></li><?php
            if($this->router->module=='index' && $this->router->countryId==0){
                echo '<li class="on"><b><span class="cf c', $this->router->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></b></li>';
            }else{
            //country change
            echo '<li><a href="/', $this->appendLang ,'"><span class="cf c', $this->router->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></a></li>';
            }
            
            
            ?><li class="sep"></li><?php
            
            //contact us
            if($this->router->module == 'contact') {
                ?><li class="on"><b><span class="ic r100"></span><?= $this->lang['contactUs'] ?></b></li><?php 
            }else{
                ?><li><a href="/contact/<?= $lang ?>"><span class="ic r100"></span><?= $this->lang['contactUs'] ?></a></li><?php 
            }
            
            ?><li class="sep"></li><?php 
            if($this->user->info['id']){                
                if($this->router->module != 'account') {
                    ?><li><a href="/account/<?= $lang ?>"><span class="et etr"></span><?= $this->lang['myAccount'] ?></a></li><?php            
                }else{
                    ?><li class="on"><b><?= $this->lang['myAccount'] ?></b></li><?php
                }
                ?><li><a href="?logout=<?= $this->user->info['provider'] ?>"><span class="k log on"></span><?= $this->lang['signout'] ?></a></li><?php
            }    
                
            ?></ul><?php
        
        ?></amp-sidebar><?php
    }
    
    protected function ampBody()
    {
        echo '<header>', $this->title, '</header>', PHP_EOL;
        $store = new Classifieds($this->router->db);
        echo '<ul>', PHP_EOL;
        foreach ($this->searchResults['body']['matches'] as $id) 
        {
            //echo $id, PHP_EOL;            
            $ad = $store->getById($id, false);
            //var_export($ad);
            echo '<li>', PHP_EOL;
            if ($ad[Classifieds::PICTURES])
            {               
                echo '<amp-img src="', $this->router->cfg['url_ad_img'], '/repos/d/',  $ad[Classifieds::PICTURES][0], '" alt="" height="122" width="122"></amp-img>';
                
            }
            echo '</li>', PHP_EOL;
            //echo  $ad[]
        }
        echo '</ul>', PHP_EOL;
    }
    
    
    function includeMetaKeywords(){
        $keywords='';
        $keywords.=  $this->lang['mourjan'].',';
        $keywords.=  $this->lang['pclassifieds'].',';
        if ($this->router->language=='ar') {
            $keywords.='نشر إعلان,إعلان,';
        }
        else {
            $keywords.='ad,post ad,';
        }
        if ($this->router->cityId) {
            $keywords.= $this->router->countries[$this->router->countryId]['cities'][$this->router->cityId]['name'];// $this->router->cities[$this->router->cityId][$this->fieldNameIndex].',';
            $keywords.= $this->router->countries[$this->router->countryId]['name'].',';
        }
        elseif($this->router->countryId) {
            $keywords.= $this->router->countries[$this->router->countryId]['name'].',';
        }
        if ($this->router->module=='index') {
            foreach ($this->router->pageRoots as $rid=>$root) {
                $keywords.= $root['name'].',';
            }
            foreach ($this->router->purposes as $ro) {
                if($ro[0]!=999 && $ro[0]!=5)
                $keywords.= $ro[$this->fieldNameIndex].',';
            }
            $keywords=substr($keywords,0,-1);
            ?><meta name="keywords" content="<?= $keywords ?>"><?php
        }
        elseif($this->router->module=='search' && !$this->userFavorites && !$this->router->watchId && !$this->router->userId) {
            if($this->router->rootId){
                $keywords.= $this->router->roots[$this->router->rootId][$this->name].',';
            }else{
                foreach($this->router->pageRoots as $ro){
                    $keywords.= $this->router->roots[$ro[0]][$this->name].',';
                }
            }
            if($this->router->sectionId){
                $keywords.= $this->router->sections[$this->router->sectionId][$this->name].',';
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
            
            if ($this->router->purposeId) {
                $keywords.= $this->router->purposes[$this->router->purposeId][$this->name].',';
            }
            else {                             
                foreach ($this->router->pagePurposes as $pId=>$purpose) {
                    if ($pId!=999 && $pId!=5) {
                        $keywords.= $purpose['name'].',';
                    }
                }             
            }
            $keywords=substr($keywords,0,-1);
            ?><meta name="keywords" content="<?= $keywords ?>"><?php
        }
    }
    
    
    public function css(string $filename) : Page {
        if (!isset($this->included[$filename])) {
            $cssfile=$this->router->config->baseDir.'/web/css/includes/'.$filename.'.css';
            if (\file_exists($cssfile)) {                
                $mincss=$this->router->config->baseDir.'/web/css/includes/min/'.$filename.'.css';
                
                if (!\file_exists($mincss) || (\file_exists($mincss) && (filemtime($cssfile)>filemtime($mincss)))) {
                    $cssContents = \file_get_contents($cssfile);
                    file_put_contents($mincss, $this->router->minifyCss($cssContents));
                }
                include $mincss;
                //include $this->router->config->baseDir.'/web/css/includes/'.$filename.'.css';
            }
            
            $this->included[$filename]=1;
        }
        return $this;
    }
    
    
    public function inlineJS(string $filename) : Page {
        if (!isset($this->included[$filename])) {
            $jsfile=$this->router->config->baseDir.'/web/js/includes/'.$filename;
            if (\file_exists($jsfile)) {
                $minjs=$this->router->config->baseDir.'/web/js/includes/min/'.$filename;
                if (!\file_exists($minjs) || (\file_exists($minjs) && (\filemtime($jsfile)>\filemtime($minjs)))) {
                    $minifiedCode = \JShrink\Minifier::minify(\file_get_contents($jsfile));
                    file_put_contents($minjs, $minifiedCode);
                    //error_log($minifiedCode);
                }
                echo '<script>';
                //include $minjs;
                include $jsfile;                
                echo '</script>';
                //\error_log($filename);
            }
            $this->included[$filename]=1;
        }
        return $this;
    }
    
    
    protected function _header() : void {
        //error_log(__CLASS__ . '.' . __FUNCTION__ . '.' .$this->router->module);
        if ($this->router->module==='myads') {
            \header("Link: </web/js/1.0/socket.io.js>; rel=preload; as=script;", false);
        }
        else if ($this->router->module==='admin') {
            \header("Link: </web/js/1.0/jsonTree.js>; rel=preload; as=script;", false);
        }        
        else if ($this->router->module==='post') {            
            \header("Link: </web/js/1.0/libphonenumber-min-1.7.10.js>; rel=preload; as=script;", false);
            \header("Link: </web/js/1.0/load-image-scale.js>; rel=preload; as=script;", false);
        }
        
        
        $country_code='';
        if ($this->router->countryId && \array_key_exists($this->router->countryId, $this->router->countries)) {
            $country_code = '-'.$this->router->countries[$this->router->countryId]['uri'];
        }
        echo '<!doctype html>';
        echo '<html lang="', $this->router->language, $country_code,'" xmlns:og="http://ogp.me/ns#"';
        echo '><head><meta charset="utf-8">';        
        if ($this->router->module==='myads') {
            ?><script async src=/web/js/1.0/chart-2.9.3/Chart.min.js></script><?php
        }
        if ($this->router->module==='admin') {
            echo '<script async src=/web/js/1.0/jsonTree.js></script>';
            
        }
        if ($this->router->module==='post') {
            echo '<script async src=/web/js/1.0/libphonenumber-min-1.7.10.js></script>';
            echo '<script async src=/web/js/1.0/load-image-scale.js></script>';
        }
        
        echo "<style>\n";
        
        //if ($this->router->module==='index') {
        //    $this->css('uhome'); 
        //}
        //else {
        $this->css('main')->css($this->router->module.'-trans'); 
        //}
       
        switch ($this->router->module) {
            case 'home':
                break;
            
            case 'search':
                $this->css('breadcrumb')->css('ad-view');
                break;
            
            case 'terms':
            case 'privacy':
            case 'premium':
                $this->css('doc');
                break;
                
            case 'about':
                $this->css('doc')->css('about');               
                break;
            
            case 'gold':
                $this->css('doc')->css('gold');               
                break;
            
            case 'guide':
            case 'iguide':
                $this->css('doc')->css('guide');
                break;
            
            case 'buyu':
            case 'signin':
                $this->css('doc');          
                break;
            
            case 'contact':
                $this->css('doc'); 
                break;
            
            case 'signup':
            case 'password':
                $this->css('doc');
                break;
            
            case 'myads':
                $this->css('myads');          
                break;
            
            case 'post':
                $this->css('post');
                break;
            
            case 'admin':
                $this->css('admin')->css('popup');  
                break;
            
            case 'statement':
                $this->css('balance');  
                break;
            
            default:
                break;
        }
        
        echo "\n</style>\n";
        
        $this->header();
        echo '<title>', $this->title, '</title>';
        $imgURL = $this->router->config->imgURL;
        ?><meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, maximum-scale=5.0, user-scalable=1" name="viewport">
        <link rel="apple-touch-icon" sizes="57x57" href="<?= "{$imgURL}/mourjan-icon-114.png" ?>" />
        <link rel="apple-touch-icon" sizes="114x114" href="<?= "{$imgURL}/mourjan-icon-114.png" ?>" />
        <link rel="apple-touch-icon" sizes="72x72" href="<?= "{$imgURL}/mourjan-icon-144.png" ?>" />
        <link rel="apple-touch-icon" sizes="144x144" href="<?= "{$imgURL}/mourjan-icon-144.png" ?>" />            
        <link rel="apple-touch-startup-image" href="<?= "{$imgURL}/mourjan-splash.png"?>" />
        <meta name="format-detection" content="telephone=no">
        <link rel="manifest" href="/manifest.json"><?php
        
        if ($this->forceNoIndex) {
            echo '<meta name="robots" content="noindex,nofollow,noarchive" />';
        }
        else {
            switch ($this->router->module) {
                case 'detail':
                    echo '<meta name="robots" content="noindex" />';
                    break;

                case 'search':
                    if ($this->userFavorites) {
                        echo '<meta name="robots" content="noindex, nofollow" />';
                    } 
                    else {
                        if ($this->searchResults && 
                            !empty($this->searchResults['body']['matches']) && 
                            !(isset($this->router->params['tag_id']) && !$this->extendedId) && 
                            (!(isset ($this->router->params['loc_id']) && !$this->localityId) || ($this->localityId && \in_array($this->router->countryId, [1,2,3,7,8,9]))) ) 
                        {
                            $qTotal = $this->searchResults['body']['total_found'];
                            $__fpages=$qTotal/$this->num;
                            $qPages = ($__fpages<1) ? 0 : ceil($__fpages);
                            $qTmp=\ceil($this->router->config->get('search_results_max')/$this->num);
                            if ($qPages>$qTmp) $qPages=(int)$qTmp;
                        
                            if (\array_key_exists('q', $_GET)) {
                                echo '<meta name="robots" content="noindex, follow" />';
                                $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);                                                        
                            
                                /*
                                if ($this->router->params['start']<$qPages && !$this->isMobile) {
                                    $next = $this->router->params['start']==0 ? 2 : $this->router->params['start']+1;
                                    echo '<link rel="prerender" href="', $this->router->config->baseURL, $currentUrl, $next,'/?q=',urlencode($this->router->params['q']), '" />';
                                    echo '<link rel="prefetch" href="', $this->router->config->baseURL, $currentUrl, $next,'/?q=',urlencode($this->router->params['q']), '" />';
                                } 
                                 * 
                                 */                           
                            }
                            else {                            
                                $this->includeMetaKeywords();                            
                                $startLink='';
                                if ($this->extendedId || $this->localityId) {
                                    $currentUrl=$this->extended_uri;
                                } 
                                else {
                                    $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
                                }
                            
                                if ($this->router->params['start']>1) {
                                    $startLink=$this->router->params['start'].'/';
                                }
                            
                                $link = 'https://www.mourjan.com'.$currentUrl.$startLink;
                                $canonical_link=$link;
                            
                                // page is not qualified to be multi language indexable
                                if ($qTotal<static::SearchEngineLegitimateEntries && !$this->router->isArabic()) {
                                    if ($this->extendedId || $this->localityId) {
                                        $canonicalCurrentUrl=\preg_replace("/\/{$this->router->language}\//", "/", $this->extended_uri);
                                    
                                        if ($this->localityId) {
                                            $alter = $this->router->db->index()
                                                    ->directQuery("select id, locality_id from locality_counts where city_id={$this->localities[$this->localityId]['city_id']} and section_id={$this->router->sectionId} and lang='ar'");
                                        
                                            if ($alter && \count($alter)==1) {
                                                foreach ($alter as $value) {
                                                    $canonicalCurrentUrl=\preg_replace("/\/c\-{$this->localityId}\-/", "/c-{$value[1]}-", $canonicalCurrentUrl);
                                                }
                                            }
                                        }
                                    
                                        if ($this->extendedId) {
                                            $alter = $this->router->db->index()
                                                    ->directQuery("select id, section_tag_id from section_tag_counts where country_id={$this->router->countryId} and section_id={$this->router->sectionId} and lang='ar' and uri='{$this->extended[$this->extendedId]['uri']}'");
                                            if ($alter && \count($alter)==1) {
                                                foreach ($alter as $value) {
                                                    $canonicalCurrentUrl=\preg_replace("/\/q\-{$this->extendedId}\-/", "/q-{$value[1]}-", $canonicalCurrentUrl);
                                                }
                                            }                                
                                        }                            
                                    } 
                                    else {
                                        $canonicalCurrentUrl=$this->router->getUrl($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId, FALSE);
                                    }
                                    $canonical_link = 'https://www.mourjan.com'.$canonicalCurrentUrl.$startLink;                                
                                }
                                // end of page is not qualified to be multi language indexable
                            
                                if ($link==$this->router->config->host.$_SERVER['REQUEST_URI']) {
                                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                                    
                                    if($this->router->countryId && $this->router->sectionId && $this->router->purposeId && $this->router->params['start']<=1){
                                        echo '<link rel="alternate" href="android-app://com.mourjan.classifieds/mourjan/list/';
                                        echo '?';
                                        echo "cn={$this->router->countryId}&";
                                        echo "c={$this->router->cityId}&";
                                        echo "ro={$this->router->rootId}&";
                                        echo "se={$this->router->sectionId}&";
                                        echo "pu={$this->router->purposeId}&";
                                        echo "tx={$this->extendedId}&";
                                        echo "gx={$this->localityId}&";
                                        echo "hl={$this->router->language}";
                                        echo '" />';
                                    }
                                }
                                else {
                                    echo '<meta name="robots" content="noindex, follow" />';
                                }
                                    
                                echo '<link rel="canonical" href="',$canonical_link, '" />';
                            
                                if ($this->router->params['start']>1) {
                                    $prev=$this->router->params['start']-1;
                                    echo "<link rel='prev' href='", $this->router->config->baseURL, $currentUrl;
                                    if ($prev>1) {
                                        echo $prev, '/';
                                    }
                                    echo "' />";
                                }
                            
                                if ($this->router->params['start']<$qPages && !$this->router->isMobile) {
                                    $next = $this->router->params['start']+1;
                                    if ($next==1) $next=2;
                                    echo "<link rel='next' href='", $this->router->config->baseURL, $currentUrl, $next, "/' />";
                                    //echo '<link rel="prerender" href="', $this->router->config->baseURL, $currentUrl, $next, '/" />';
                                    //echo '<link rel="prefetch" href="', $this->router->config->baseURL, $currentUrl, $next, '/" />';
                                }
                            }
                        }
                        else {
                            echo '<meta name="robots" content="noindex, follow" />';
                        }
                    }

                    //if (!$this->router->isMobile) {
                    //    echo '<link href="', $this->router->config->baseURL, $this->router->uri, $this->router->getLanguagePath(), '?rss=1" rel="alternate" type="application/rss+xml" title="', $this->title, '" />';
                    //}

                    break;
                
                case 'index':
                    $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId);
                    $link='https://www.mourjan.com'.$currentUrl;
                    if ($link===$this->router->config->host.$_SERVER['REQUEST_URI']) { 
                        $this->includeMetaKeywords();
                        echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                    }
                    else {
                        echo '<meta name="robots" content="noindex, follow" />';
                    }
                    echo '<link rel="canonical" href="', $link, '" />';
                
                    $__cn=null;
                    $__cc=null;
                    if ($this->router->countryId && isset($this->router->countries[$this->router->countryId])) {
                        $__cn = $this->router->countries[$this->router->countryId]['uri'];
                    }
                    $__name = $__cn ? 
                        (
                         ($this->router->isArabic() ? 'مرجان ' : 'Mourjan ').                        
                         ($this->router->isArabic() ? 
                            $this->router->countries[$this->router->countryId]['name'] : 
                            $this->router->countries[$this->router->countryId]['name']
                         )
                        ) : $this->title;   
                
                ?><script type="application/ld+json">
{"@context": "https://schema.org",
 "@type": "WebSite",
 "name": "<?= $__name ?>",
 "alternateName": "mourjan",
 "url": "https://www.mourjan.com/<?= ($__cn?$__cn.'/':'').$this->router->getLanguagePath() ?>",
 "potentialAction":
 {"@type": "SearchAction",
  "target": "https://www.mourjan.com/<?= ($__cn?$__cn.'/':'').$this->router->getLanguagePath() ?>?q={search_term_string}",
  "query-input": "required name=search_term_string"
 }
}
</script><?php
                break;
                
            default:
                if ($this->router->module==='notfound') {
                    echo '<meta name="robots" content="noindex, nofollow" />';
                } 
                elseif ($this->router->module==='privacy'||$this->router->module==='terms'||$this->router->module==='about'||$this->router->module==='advertise') {
                    if ($this->router->language==='en') {
                        echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                    }
                    else {
                        echo '<meta name="robots" content="noindex, nofollow" />';
                    }
                }
                else {
                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                }
                break;
            }
        }
                
        ?><link rel="icon" href="<?= $this->router->config->imgURL ?>/favicon.ico" type="image/x-icon" /><?php 
        $this->set_analytics_header();
        
        //echo PHP_EOL;
        
        $this->inlineJS('util.js');
        $this->inlineJS('search-box.js');        
        
        echo '</head>', "\n";
        flush();
        echo '<body dir="', $this->router->isArabic()?'rtl':'ltr', '" class="',$this->router->isArabic()?'rtl':'ltr','" data-ads='.($this->router->config->enabledAds()?1:0),' ', $this->pageItemScope;
        if ($this->isAdminSearch) {
            echo ' oncontextmenu="return false;"';
        }
        if ($this->router->isAcceptWebP) {
            echo ' class="wbp"';
        }
        if ($this->user()->id()) {
            echo ' data-key="', $this->user->info['idKey'], '" data-level=', $this->user()->level()*($this->user()->isSuperUser()?10:1);
        }
        echo ' data-repo="', $this->router->config->adImgURL, '">', '<meta itemprop="isFamilyFriendly" content="true" />', "\n"; 

        //error_log('key '.$this->user->info['idKey']. ' decoded ' . $this->user()->decodeId($this->user->info['idKey']) . ' uid '.$this->user()->id());
        if (1) {  return; }
        
        
        /*-------------- OLD code --------------------------*/
        
        $this->prepare_css();
        $this->prepare_js();
        
        foreach($this->requires['css'] as $css){
            header("Link: <{$css}>; rel=preload; as=style;", false);
        }
        
        if ($this->router->isMobile) {     
            header("Link: <{$this->router->config->get('url_css_mobile')}/i/main_m{$this->router->_png}>; rel=preload; as=image;", false);
            
            switch($this->router->module){
                case 'search':
                    if($this->router->sectionId){
                        header("Link: <{$this->router->cfg['url_img']}/90/{$this->router->sectionId}{$this->router->_png}>; rel=preload; as=image;", false);
                    }
                    break;
                case 'detail':
                    header("Link: <{$this->router->cfg['url_css_mobile']}/i/share{$this->router->_png}>; rel=preload; as=image;", false);
                    break;
                case 'index':
                    switch($this->router->rootId){
                        case 1:
                            header("Link: <{$this->router->cfg['url_css_mobile']}/i/realestate{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 2:
                            header("Link: <{$this->router->cfg['url_css_mobile']}/i/cars{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 3:
                            header("Link: <{$this->router->cfg['url_css_mobile']}/i/jobs{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 4:
                            header("Link: <{$this->router->cfg['url_css_mobile']}/i/service{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 99:
                            header("Link: <{$this->router->cfg['url_css_mobile']}/i/misc{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            foreach($this->requires['js'] as $js){
                header("Link: <{$js}>; rel=preload; as=script;", false);
            }
            
        } 
        else {
            //logos
            header("Link: <{$this->router->config->cssURL}/i/logo{$this->router->_jpg}>; rel=preload; as=image;", false);
            header("Link: <{$this->router->config->cssURL}/i/gen{$this->router->_png}>; rel=preload; as=image;", false);
            header("Link: <{$this->router->config->cssURL}/i/f/all{$this->router->_png}>; rel=preload; as=image;", false);
            if($this->user->info['id']) {
                header("Link: <{$this->router->cfg['url_css']}/i/geni{$this->router->_png}>; rel=preload; as=image;", false);
            }
            
            switch ($this->router->module) {
                case 'search':
                    if ($this->router->sectionId) {
                        header("Link: <{$this->router->cfg['url_img']}/90/{$this->router->sectionId}{$this->router->_png}>; rel=preload; as=image;", false);
                    }
                case 'detail':
                    switch($this->router->rootId){
                        case 1:
                            header("Link: <{$this->router->cfg['url_css']}/i/realestate{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 2:
                            header("Link: <{$this->router->cfg['url_css']}/i/cars{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 3:
                            header("Link: <{$this->router->cfg['url_css']}/i/jobs{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 4:
                            header("Link: <{$this->router->cfg['url_css']}/i/service{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        case 99:
                            header("Link: <{$this->router->cfg['url_css']}/i/misc{$this->router->_png}>; rel=preload; as=image;", false);
                            break;
                        default:
                            break;
                    }
                    break;
                
                case 'index':
                    header("Link: <{$this->router->config->cssURL}/i/realestate{$this->router->_png}>; rel=preload; as=image;", false);
                    header("Link: <{$this->router->config->cssURL}/i/jobs{$this->router->_png}>; rel=preload; as=image;", false);
                    header("Link: <{$this->router->config->cssURL}/i/service{$this->router->_png}>; rel=preload; as=image;", false);
                    header("Link: <{$this->router->config->cssURL}/i/cars{$this->router->_png}>; rel=preload; as=image;", false);
                    header("Link: <{$this->router->config->cssURL}/i/misc{$this->router->_png}>; rel=preload; as=image;", false);
                    
                    if (isset($this->user->params['screen'][0])) {
                        if ($this->user->params['screen'][0] > 1249) {
                            header("Link: <{$this->router->config->cssURL}/i/wbl{$this->router->_jpg}>; rel=preload; as=image;", false);
                        }
                        else {
                            header("Link: <{$this->router->config->cssURL}/i/bl{$this->router->_jpg}>; rel=preload; as=image;", false);
                        }
                    }
                    break;
                    
                default:
                    header("Link: <{$this->router->config->imgURL}/msl{$this->router->_png}>; rel=preload; as=image;", false);
                    break;
            }
            
            header("Link: <{$this->router->config->jQueryURL}jquery.min.js>; rel=preload; as=script;", false);
            foreach($this->requires['js'] as $js){
                header("Link: <{$js}>; rel=preload; as=script;", false);
            }
        }
        echo '<!doctype html>';
        //if ($this->ampEnabled)
        //{
        //    echo '<html amp lang="', $this->router->language, '"><head>';
        //    echo '<meta charset="utf-8"><script async src="https://cdn.ampproject.org/v0.js"></script>'; 
        //    echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
        //}
        //else
        //{
        echo '<html lang="', $this->router->language, $country_code,'" xmlns:fb="http://ogp.me/ns/fb#" xmlns:og="http://ogp.me/ns#"';
        if (isset($this->detailAd[Classifieds::VIDEO]) && $this->detailAd[Classifieds::VIDEO]) {
            echo ' xmlns:video="http://ogp.me/ns/video#"';
        }
        echo '><head><meta charset="utf-8">';
        //}
        $this->load_css();
        $this->header();

        echo '<title>', $this->title, '</title>';
        
        if ($this->isMobile) {            
            ?><meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, maximum-scale=5.0, user-scalable=1" name="viewport"><?php
            ?><link rel="apple-touch-icon" sizes="57x57" href="<?= $this->router->cfg["url_img"]."/mourjan-icon-114.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="114x114" href="<?= $this->router->cfg["url_img"]."/mourjan-icon-114.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="72x72" href="<?= $this->router->cfg["url_img"]."/mourjan-icon-144.png" ?>" /><?php
            ?><link rel="apple-touch-icon" sizes="144x144" href="<?= $this->router->cfg["url_img"]."/mourjan-icon-144.png" ?>" /><?php
            
            ?><link rel="apple-touch-startup-image" href="<?= $this->router->cfg["url_img"]."/mourjan-splash.png"?>" /><?php
            ?><meta name="format-detection" content="telephone=no"><?php
            ?><link rel="manifest" href="/manifest.json"><?php
        }

        if ($country_code && isset($this->router->cities)) {
            echo '<meta http-equiv="content-language" content="', $this->router->language, $country_code, '">';
        } 
        else {
            echo '<meta http-equiv="content-language" content="', $this->router->language, $country_code,'" />';
        }
        
        if ($this->forceNoIndex) {
            echo '<meta name="robots" content="noindex,nofollow,noarchive" />';
        }
        else {
            switch ($this->router->module) {
                case "detail":
                    echo '<meta name="robots" content="noindex" />';
                break;

            case 'search':
                if ($this->userFavorites) {
                    echo '<meta name="robots" content="noindex, nofollow" />';
                } 
                else {
                    if ($this->searchResults && 
                        !empty($this->searchResults['body']['matches']) && 
                        !(isset($this->router->params['tag_id']) && !$this->extendedId) && 
                        (!(isset ($this->router->params['loc_id']) && !$this->localityId) || ($this->localityId && in_array($this->router->countryId, [1,2,3,7,8,9]))) ) 
                    {
                        $qTotal = $this->searchResults['body']['total_found'];
                        $__fpages=$qTotal/$this->num;
                        $qPages = ($__fpages<1) ? 0 : ceil($__fpages);
                        $qTmp=ceil($this->router->cfg['search_results_max']/$this->num);
                        if ($qPages>$qTmp) $qPages=(int)$qTmp;
                        
                        if (array_key_exists('q', $_GET)) {
                            echo '<meta name="robots" content="noindex, follow" />';
                            $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);                                                        
                            
                            if ($this->router->params['start']<$qPages && !$this->isMobile) {
                                $next = $this->router->params['start']==0 ? 2 : $this->router->params['start']+1;
                                //echo '<link rel="prerender" href="', $this->router->config->baseURL, $currentUrl, $next,'/?q=',urlencode($this->router->params['q']), '" />';
                                //echo '<link rel="prefetch" href="', $this->router->config->baseURL, $currentUrl, $next,'/?q=',urlencode($this->router->params['q']), '" />';
                            }                            
                        }
                        else {
                            
                            $this->includeMetaKeywords();
                            
                            $startLink='';
                            if ($this->extendedId || $this->localityId) {
                                $currentUrl=$this->extended_uri;
                            } 
                            else {
                                $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId,$this->router->rootId,$this->router->sectionId,$this->router->purposeId);
                            }
                            
                            if ($this->router->params['start']>1) {
                                $startLink=$this->router->params['start'].'/';
                            }
                            
                            $link = 'https://www.mourjan.com'.$currentUrl.$startLink;
                            $canonical_link=$link;
                            
                            // page is not qualified to be multi language indexable
                            if ($qTotal<static::SearchEngineLegitimateEntries && !$this->router->isArabic()) {
                                if ($this->extendedId || $this->localityId) {
                                    $canonicalCurrentUrl= preg_replace("/\/{$this->router->language}\//", "/", $this->extended_uri);
                                    
                                    if ($this->localityId) {
                                        $alter = $this->router->db->index()
                                                ->directQuery("select id, locality_id from locality_counts where city_id={$this->localities[$this->localityId]['city_id']} and section_id={$this->router->sectionId} and lang='ar'");
                                        
                                        if ($alter && count($alter)==1) {
                                            foreach ($alter as $value) {
                                                $canonicalCurrentUrl= preg_replace("/\/c\-{$this->localityId}\-/", "/c-{$value[1]}-", $canonicalCurrentUrl);
                                            }
                                        }
                                    }
                                    
                                    if ($this->extendedId) {
                                        $alter = $this->router->db->index()
                                                ->directQuery("select id, section_tag_id from section_tag_counts where country_id={$this->router->countryId} and section_id={$this->router->sectionId} and lang='ar' and uri='{$this->extended[$this->extendedId]['uri']}'");
                                        if ($alter && count($alter)==1) {
                                            foreach ($alter as $value) {
                                                $canonicalCurrentUrl= preg_replace("/\/q\-{$this->extendedId}\-/", "/q-{$value[1]}-", $canonicalCurrentUrl);
                                            }
                                        }                                        
                                    }                                    
                                } 
                                else {
                                    $canonicalCurrentUrl=$this->router->getUrl($this->router->countryId, $this->router->cityId, $this->router->rootId, $this->router->sectionId, $this->router->purposeId, FALSE);
                                }
                                $canonical_link = 'https://www.mourjan.com'.$canonicalCurrentUrl.$startLink;                                
                            }
                            // end of page is not qualified to be multi language indexable
                            
                            if ($link==$this->router->config->host.$_SERVER['REQUEST_URI']) {
                                echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                                    
                                if($this->router->countryId && $this->router->sectionId && $this->router->purposeId && $this->router->params['start']<=1){
                                    echo '<link rel="alternate" href="android-app://com.mourjan.classifieds/mourjan/list/';
                                    echo '?';
                                    echo "cn={$this->router->countryId}&";
                                    echo "c={$this->router->cityId}&";
                                    echo "ro={$this->router->rootId}&";
                                    echo "se={$this->router->sectionId}&";
                                    echo "pu={$this->router->purposeId}&";
                                    echo "tx={$this->extendedId}&";
                                    echo "gx={$this->localityId}&";
                                    echo "hl={$this->router->language}";
                                    echo '" />';
                                }
                            }
                            else {
                                echo '<meta name="robots" content="noindex, follow" />';
                            }
                                    
                            echo '<link rel="canonical" href="',$canonical_link, '" />';
                            

                            if ($this->router->params['start']>1) {
                                $prev=$this->router->params['start']-1;
                                echo "<link rel='prev' href='", $this->router->config->baseURL, $currentUrl;
                                if ($prev>1) {
                                    echo $prev, '/';
                                }
                                echo "' />";
                            }
                            
                            if ($this->router->params['start']<$qPages && !$this->isMobile) {
                                $next = $this->router->params['start']+1;
                                if ($next==1) $next=2;
                                echo "<link rel='next' href='", $this->router->config->baseURL, $currentUrl, $next, "/' />";
                                //echo '<link rel="prerender" href="', $this->router->config->baseURL, $currentUrl, $next, '/" />';
                                //echo '<link rel="prefetch" href="', $this->router->config->baseURL, $currentUrl, $next, '/" />';
                            }
                        }
                    }
                    else {
                        echo '<meta name="robots" content="noindex, follow" />';
                    }
                }

                //if (!$this->router->isMobile) {
                //    echo '<link href="', $this->router->config->baseURL, $this->router->uri, $this->router->getLanguagePath(), '?rss=1" rel="alternate" type="application/rss+xml" title="', $this->title, '" />';
                //}

                break;
                
            case 'index':
                $currentUrl=$this->router->getUrl($this->router->countryId,$this->router->cityId);
                $link=  'https://www.mourjan.com'.$currentUrl;
                if ($link == $this->router->config->host.$_SERVER['REQUEST_URI']) { 
                    $this->includeMetaKeywords();
                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                }
                else {
                    echo '<meta name="robots" content="noindex, follow" />';
                }
                echo '<link rel="canonical" href="', $link, '" />';
                
                $__cn=null;
                $__cc=null;
                if ($this->router->countryId && isset($this->router->countries[$this->router->countryId])) {
                    $__cn = $this->router->countries[$this->router->countryId]['uri'];
                }
                $__name = $__cn ? 
                        (
                         ($this->router->language=='ar' ? 'مرجان ' : 'Mourjan ').
                        
                         ($this->router->language=='ar' ? 
                            $this->router->countries[$this->router->countryId]['name'] : 
                            $this->router->countries[$this->router->countryId]['name']
                         )
                        ) : 
                        $this->title;   
                
                ?><script type="application/ld+json">
{"@context": "https://schema.org",
 "@type": "WebSite",
 "name": "<?= $__name ?>",
 "alternateName": "mourjan",
 "url": "https://www.mourjan.com/<?= ($__cn?$__cn.'/':'').$this->router->getLanguagePath() ?>",
 "potentialAction":
 {"@type": "SearchAction",
  "target": "https://www.mourjan.com/<?= ($__cn?$__cn.'/':'').$this->router->getLanguagePath() ?>?q={search_term_string}",
  "query-input": "required name=search_term_string"
 }
}
</script><?php
        
        
                break;
                
            default:
                if ($this->router->module=='notfound') {
                    echo '<meta name="robots" content="noindex, nofollow" />';
                } 
                elseif($this->router->module=='privacy' || $this->router->module=='terms' || $this->router->module=='about' || $this->router->module=='advertise') {
                    if ($this->router->language=='en'){
                        echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                    }
                    else {
                        echo '<meta name="robots" content="noindex, nofollow" />';
                    }
                }
                else {
                    echo '<meta name="robots" content="noodp, noydir, index, follow" />';
                }
                break;
        }
        }

        ?><link rel="icon" href="<?= $this->router->config->imgURL ?>/favicon.ico" type="image/x-icon" /><?php 
            $this->set_analytics_header();
            
        ?></head><?php flush() ?><body<?= $this->isAdminSearch ? ' oncontextmenu="return false;"':'' ?> class="<?= ($this->router->isAcceptWebP ? 'wbp':'') ?>" <?= $this->pageItemScope ?>><meta itemprop="isFamilyFriendly" content="true" /><?php
    }


    
    function footerMobile(){
        $lang=$this->router->language=='ar'?'':$this->router->language.'/';
        if(!$this->router->isApp){
            ?> <!--googleoff: index --> <?php
            ?><div id="side" class="side"><ul class="ls"><?php            
                        
            ?><li><?php 
            if($lang){
                ?><a class="ar ctr" href="<?= $this->switchLangUrl ?>">تصفح باللغة العربية</a><?php
            }else{
                ?><a class="en ctr" href="<?= $this->switchLangUrl ?>">switch to English</a><?php
            }
            ?></li><?php
            
            $headTitle = $this->lang['mourjan'];
            if($this->router->cityId){
                $headTitle=$this->lang['mourjan'].' '.$this->cityName;
            }elseif($this->router->countryId){
                $headTitle=$this->lang['mourjan'].' '.$this->countryName;
            }
            if($this->router->module=='index' && !$this->router->rootId && $this->router->countryId){
                ?><li class="on"><b><span class="k home"></span><?= $headTitle ?></b></li><?php
            }else{
                ?><li><a href="<?= $this->router->getURL($this->router->countryId, $this->router->cityId); ?>"><span class="k home"></span><?= $headTitle ?></a></li><?php
            }
            if($this->user->info['id']){                
                if($this->router->module=='post'){    
                    ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></b></li><?php
                }else{
                    ?><li><a href="/post/<?= $lang ?>?clear=true"><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></a></li><?php
                }     
                ?><li class="sep"></li><?php   
                if($this->router->module != 'search' || !$this->userFavorites) {
                   ?><li><a href="/favorites/<?= $lang ?>"><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="ic k fav on"></span><?= $this->lang['myFavorites'] ?></b></li><?php 
                }
                /*if($this->router->module != 'search' || !$this->router->watchId) {
                       ?><li><a href="/watchlist/<?= $lang ?>"><span class="ic k eye on"></span><?= $this->lang['myList'] ?></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="ic k eye on"></span><?= $this->lang['myList'] ?></b></li><?php 
                }*/
                ?><li class="sep"></li><?php
                if($this->router->module != 'balance') {
                    ?><li><a href="/statement/<?= $lang ?>"><span class="mc24"></span><?= $this->lang['myBalance'] ?><span class="n"></span></a></li><?php 
                }else{
                    ?><li class="on"><b><span class="mc24"></span><?= $this->lang['myBalance'] ?></b></li><?php 
                }
                ?><li class="sep"></li><?php
                
                $sub=(isset($_GET['sub']) && $_GET['sub'] ? $_GET['sub']:'');
                if($this->router->module != 'myads' || ($this->router->module == 'myads' && $sub!='') ) {
                        ?><li><a href="/myads/<?= $lang ?>"><span class="ic aon"></span><?= $this->lang['ads_active'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic aon"></span><?= $this->lang['ads_active'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='pending') ) {
                        ?><li><a href="/myads/<?= $lang ?>?sub=pending"><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic apd"></span><?= $this->lang['ads_pending'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='drafts'))  {
                    ?><li><a href="/myads/<?= $lang ?>?sub=drafts"><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic aedi"></span><?= $this->lang['ads_drafts'] ?></b></li><?php
                }
                if($this->router->module != 'myads'  || ($this->router->module == 'myads' &&  $sub!='archive'))  {
                    ?><li><a href="/myads/<?= $lang ?>?sub=archive"><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></a></li><?php
                }else{
                    ?><li class="on"><b><span class="ic afd"></span><?= $this->lang['ads_archive'] ?></b></li><?php
                }
            
            }else{
                if($this->router->module=='post'){    
                    ?><li class="on"><b><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></b></li><?php
                }else{
                    ?><li><a href="/post/<?= $lang ?>?clear=true"><span class="ic apb"></span><?= $this->lang['button_ad_post'] ?></a></li><?php
                }
            }
            ?><li class="sep"></li><?php            
            
            if($this->user->info['id']==0){
                if($this->router->module=='home'){
                    ?><li class="on"><b><span class="k log"></span><?= $this->lang['signin'] ?></b></li><?php 
                }else{
                    ?><li><a href="/home/<?= $lang ?>"><span class="k log"></span><?= $this->lang['signin'] ?></a></li><?php
                }
                ?><li class="sep"></li><?php
            }
                      
            $countryId = $this->router->countryId;
            $cityId = $this->router->cityId; 
            if (isset($this->user->params['country']) && $this->user->params['country'])
                $countryId=$this->router->countryId=$this->user->params['country'];
            if (isset($this->user->params['city']) && $this->user->params['city'])
                $cityId=$this->router->cityId=$this->user->params['city'];
            //$this->router->pageRoots = $this->router->db->getRootsData($countryId, $cityId, $this->router->language);
            $this->router->pageRoots = $this->router->db->asRootsData($countryId, $cityId, $this->router->language);
            //roots
            $i=0;
            foreach ($this->router->pageRoots as $key=>$root) {
                $count=$this->checkNewUserContent($root['unixtime']) ? '<b>'.$root['counter'].'</b>' : $root['counter'];
                if($this->router->module=='index' && $this->router->rootId==$key){
                    echo '<li class="on"><b><span class="ic r',$key,'"></span>',
                    $root['name'], ($countryId ? '<span class="n">'. $count. '</span>':'') ,'</b></li>';
                }else{
                $_link = $this->router->getURL($countryId, $cityId, $key);        
                echo '<li><a href="', $_link, '"><span class="ic r',$key,'"></span>',
                    $root['name'], ($countryId ? '<span class="n">'. $count. '</span>':'') ,'</a></li>';
                }
                $i++;
            }            
            
            ?><li class="sep"></li><?php
            if($this->router->module=='index' && $this->router->countryId==0){
                echo '<li class="on"><b><span class="cf c', $this->router->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></b></li>';
            }else{
            //country change
            echo '<li><a href="/', $this->appendLang ,'"><span class="cf c', $this->router->countryId, '"></span>',
                $this->countryCounter, ' ',$this->lang['in'],' ',($this->router->cityId?$this->cityName:$this->countryName),
                '<span class="et"></span></a></li>';
            }
            
            
            ?><li class="sep"></li><?php
            
            //contact us
            if($this->router->module == 'contact') {
                ?><li class="on"><b><span class="ic r100"></span><?= $this->lang['contactUs'] ?></b></li><?php 
            }else{
                ?><li><a href="/contact/<?= $lang ?>"><span class="ic r100"></span><?= $this->lang['contactUs'] ?></a></li><?php 
            }
            
            ?><li class="sep"></li><?php 
            if($this->user->info['id']){                
                if($this->router->module != 'account') {
                    ?><li><a href="/account/<?= $lang ?>"><span class="et etr"></span><?= $this->lang['myAccount'] ?></a></li><?php            
                }else{
                    ?><li class="on"><b><?= $this->lang['myAccount'] ?></b></li><?php
                }
                ?><li><a href="?logout=<?= $this->user->info['provider'] ?>"><span class="k log on"></span><?= $this->lang['signout'] ?></a></li><?php
            }    
                
            ?></ul></div><?php
            ?> <!--googleon: index --> <?php
            if( in_array($this->router->module,['index','search','detail','contact']) &&  
                   (( (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1) ||
                  (isset($this->user->params['mobile_android_app_bottom_banner']) && $this->user->params['mobile_android_app_bottom_banner']==1) )))
            {
                ?> <!--googleoff: index --> <?php

                if (isset($this->user->params['mobile_ios_app_bottom_banner']) && $this->user->params['mobile_ios_app_bottom_banner']==1){

                    ?><br /><ul class="install rc sh"><?php
                    ?><li><span class="ilogo" /></li><?php
                    ?><li><div><?php
                        ?><h5><?= $this->lang['mourjan_app'] ?></h5><?php
                        ?><p><?= $this->lang['app_desc'] ?></p><?php
                        ?><span class='rating ios'/><?php
                    ?></div></li><?php
                    ?><li><a type="button" href='https://itunes.apple.com/us/app/mourjan-mrjan/id876330682?ls=1&mt=8' class="bt"><?= $this->lang['install'] ?></a></li><?php
                    ?></ul><br /><?php

                }else{

                    ?><br /><ul class="install rc sh"><?php
                    ?><li><span class="ilogo" /></li><?php
                    ?><li><div><?php
                        ?><h5><?= $this->lang['mourjan_app'] ?></h5><?php
                        ?><p><?= $this->lang['app_desc'] ?></p><?php
                        ?><span class='rating <?= $this->router->language ?>'>(7,562)</span><?php
                    ?></div></li><?php
                    ?><li><a type="button" href='https://play.google.com/store/apps/details?id=com.mourjan.classifieds' class="bt"><?= $this->lang['install'] ?></a></li><?php
                    ?></ul><br /><?php

                }
                ?> <!--googleon: index --> <?php 
            }
            $year = date('Y');
            ?><div id="footer" class="copy"><span>© 2010-<?= $year ?> mourjan.com Classifieds </span><span class="sep"> - </span><span>All Rights Reserved</span></div><?php 
        
        }
    }

    
    function _body() : void {
        echo '<div id=wrapper class=wrapper>', "\n";
        $this->top();
        $this->body();
        $this->footer();
        echo '</div></body>';        
        echo '</html>';
        if (1) { return; }
        /*--------------------------- Old Code ---------------------------*/
        
        if ($this->isMobile) {
            $this->topMobile();
            $this->bodyMobile();
            if (!$this->router->isApp) {
                $this->footerMobile();
            }
            $this->loadMobileJs_classic();
        } 
        else {
            //echo '<!--googleoff: snippet-->';
            $this->top();            
            if (!$this->router->userId) {
                //include_once dirname( $this->router->cfg['dir'] ) . '/tmp/gen/' . $this->includeHash.'0.php';  
                include_once dirname( '/home/www/mourjan' ) . '/tmp/gen/' . $this->includeHash.'0.php';  
                $this->search_bar();
            }

            //echo '<!--googleon: snippet-->';
            $this->body();
            $this->load_js_classic();
            
        }
        ?></body></html><?php
    }
    
    
    function partnerHeader(){
        $rc=$this->router->module=='detail'?'':' rct';
        $isOwner=  ($this->router->userId==$this->user->info['id'] && $this->router->module!='detail' && !$this->pagePreview);
        $isOwner=0;
        if($isOwner) {
            $this->inlineScript.='
                var lj=document.createElement("script");
                lj.type="text/javascript";
                lj.async=true;
                lj.src="'.$this->router->cfg['url_jquery'].'jquery.colorbox.js";
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
                    if (cSize('.$this->router->cfg['max_upload'].',e.prev())){
                        var p =e.parent().prev();
                        p.html("'.$this->lang['upload_wait'].'");
                        p.addClass("loading");
                        e.addClass("unv");
                        return true;
                    }
                    return false
                });
           ';

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
        $editPageLink='/page/'.($this->router->language == 'ar' ? '':'en/');
        ?><div class='w'><?php
            ?><div class="tbn"<?php
                if (isset($this->partnerInfo['banner'][0])){
                    ?>><img src="<?= $this->router->cfg['url_ad_img'] ?>/usr/banner/<?= $this->partnerInfo['banner'][0] ?>" width="<?= $this->partnerInfo['banner'][1] ?>" height="<?= $this->partnerInfo['banner'][2] ?>" /><?php
                }else {
                    ?> id="uTX"><?php
                }
            ?></div><?php
        ?></div><?php
    }
    
}
