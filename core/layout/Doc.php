<?php
\Config::instance()->incLayoutFile('Page')->incLibFile('IPQuality');

class Doc extends Page{

    private string $countryCode='XX';
    private string $currency_id='USD';
    private int $activation_phone_number=0;
    
    function __construct() {
        header('Vary: User-Agent');
        parent::__construct(); 
        
        if ($this->router->module==='buy' || $this->router->module==='buyu') {
            if ($this->router->config->get('active_maintenance')) {
                $this->user()->redirectTo($this->router->getLanguagePath('/maintenance/'));
            }
            $this->checkBlockedAccount();
            
            $allow=false;
            $ip=IPQuality::fetchJson(false);
            if (isset($ip['ipquality'])) {
                $tor=($ip['ipquality']['tor']??1)+0;
                $vpn=($ip['ipquality']['vpn']??1)+0;
                $this->countryCode=$ip['ipquality']['country_code']??'XX'; 
                
                if ($this->router->module==='buy') {
                    $allow=($tor===0 && $vpn===0 && \in_array($this->countryCode, ['AE', 'SA', 'BH', 'KW', 'QA']));
                }
                else {
                    $allow=($tor===0 && \in_array($this->countryCode, ['AE', 'SA', 'BH', 'KW', 'QA', 'EG', 'JO', 'LB', 'MA', 'SD', 'OM']));
                }
                
                if ($allow===false) {
                    $extraRe='';
                    if ($tor===1) {
                        $extraRe.='tor=1';
                    }
                    if($vpn===1) {
                        if ($extraRe) {
                            $extraRe.='&';
                        }
                        $extraRe.='vpn=1';
                    }
                    if ($extraRe) {
                        $extraRe='?'.$extraRe;
                    }
                    $this->user->redirectTo($this->router->getLanguagePath('/disallowed/').$extraRe);
                }
            }                        
        }
                           
/*
        if ($this->router->module=='premium') {
            $this->inlineCss.='
                .uln{
                        list-style-type: none!important;  
                    }
                    .uln ul{
                        list-style:none;
                        margin:0
                    }
                    .uld{
                        list-style:disc inside!important;
                        margin:0
                    }
                    .doc li{padding: 5px 10px}
                    .alt{background-color:#ececec;}
                    li.clr{list-style:none;padding:0;padding-top:15px;margin-bottom:30px}
                    li.clr ul{display:inline-block}
                    li.clr li{padding:0}
                    ul.g3 li{width:210px}
                    ul.g2 li{width:315px}
                    ul.g1 li{width:630px}
                    li.clr li{float:left;text-align:center}
                    .ar li.clr li{float:right}
                    .vpdi{vertical-align:bottom}
                    .btH{text-align:center;margin-top:20px}
                    .bt{color:#FFF!important}
                    .bt:hover{text-decoration:none!important}
                    .ar{line-height:25px}
                        ';
        }

        if ($this->router->module=='buy' || $this->router->module=='buyu'){
            $this->inlineCss.='
                    .prices{margin:0!important;list-style:disc inside!important;padding:0 40px;}
                    .prices ul{display:inline-block;line-height:1em;margin:0!important}
                    .prices ul li{float:left;width:100px;list-style:none}
                    p.pad{margin:0;padding:5px 10px}
                    .ar .prices ul li{float:right}
                    li.ctr{width:50px!important}
                    .alt{background-color:#ececec;}
                    .alinks{overflow:hidden;margin:0!important;list-style:none!important}
                    .alinks li{float:left;width:50%;text-align:center;}
                    .android{margin:0}
                    .ar{line-height:25px}
                    .btH{text-align:center;margin-top:20px}
                    .bt{color:#FFF!important}
                    .bt:hover{text-decoration:none!important}
                    .table{list-style:none!important;overflow:hidden}
                    .table li{float:left;width:203px;height:60px;line-height:60px;
                    border-bottom:1px solid seagreen;padding:0 10px}
                    .ar .table li{float:right;text-align:right}
                    .table input{vertical-align:middle}
                    .tt{text-align:right!important}
                    .ar .tt{text-align:left!important}
                ';    
        }            

                 */                              
        if ($this->router->module==='iguide') { $this->forceNoIndex=true; }
        
        $this->hasLeadingPane=true;
        $this->router->config->disableAds();        
        $this->router->config->setValue('enabled_sharing', 0);

        if ($this->router->module==='about') {
            $this->lang['title']        = 'About mourjan.com';
            $this->lang['description']  = 'mourjan.com is an online classifieds search engine that helps you search and browse ads listed in major classifieds newspapers, websites and user submitted free ads';
        }
        elseif($this->router->module==='advertise'){ 
            if ($this->router->isArabic()) {
                $this->title='أعلن مع مرجان';
                $this->lang['description']='قم بتسويق شركتك، منتجاتك أو خدماتك بأسلوب متميز مستفيداً من أكثر من 3.5 مليون انطباع وأكثر من 250،000 زائر فريد شهريا على موقع مرجان';
            }
            else {
                $this->title='Advertise with mourjan.com';
                $this->lang['description']=  'Market your online business with style and benefit from over 3.5 million impressions and over 250,000 unique visitors per month';
            }
        }
        elseif ($this->router->module==='gold') {
            $this->title=$this->lang['gold_title'];
            $this->lang['description']= $this->lang['gold_desc'];
        }
        elseif ($this->router->module==='buy' || $this->router->module==='buyu') {
            $this->forceNoIndex=true;
        }
        
        if (($this->router->module==='buy' || $this->router->module==='buyu') && !$this->user()->isLoggedIn()) {
            $this->hasLeadingPane=false;
        }
        
        $this->render();
    }
    
    
    function header() : void {       
        parent::header();
        $this->inlineJS('buy.js');
    }

    
    function side_pane() {
        if (($this->router->module!=='buy' && $this->router->module!=='buyu') || (($this->router->module==='buyu' || $this->router->module==='buy') && $this->user()->isLoggedIn())) {
            $this->renderSideSite();
        }
        //$this->renderSideUserPanel();
    }
    
    
    function formatWord($num){
        if ($this->urlRouter->siteLanguage=='ar'){
            if($num==1) return 'كلمة واحدة';
            elseif($num==2) return 'كلمتين';
            elseif($num<11) return $num.' كلمات';
            else return $num.' كلمة';
        }else {
            if ($num==1) return '1 word';
            else return $num.' words';
        }
    }
    
    function formatPubPeriod($num, $pubPeriod, $lang){
        if ($this->urlRouter->siteLanguage=='ar'){
            if($num==1) return $lang[$pubPeriod][0];
            elseif($num==2) return $lang[$pubPeriod][1];
            elseif($num<11) return $num.' '.$lang[$pubPeriod][2];
            else return $num.' '.$lang[$pubPeriod][3];
        }else {
            if ($num==1) return '1 '.$lang[$pubPeriod][0];
            else return $num.' '.$lang[$pubPeriod][1];
        }
    }
    
    
    function mainMobile() {
        $this->main_pane();
    }

    
    private function payfortButton(int $product_id, int $mcu, float $price) : void {
        $price_label=($this->currency_id==='AED')?(\number_format($price, 2).' <small>'. $this->lang['uae_dirhams'].'</small>'):('$'.\number_format($price, 2));

        if ($this->user->isLoggedIn()) {
            if ($this->activation_phone_number>0) {
                $buy="buy({$product_id}, '{$this->currency_id}', this)";
            }
            else {
                $buy="javascript:showMessage('Purchase is not allowed for unknown user\'s mobile number!');";
            }
        }
        else {
            $buy="redirect('{$this->router->getLanguagePath('/signin/')}')";
            //$buy="redirect('{$this->router->getLanguagePath('/post/')}')";
        }
        
        /*?><form method=post name=payment action="javascript:void(0);" onsubmit="buy(<?=$product_id?>, '<?=$this->currency_id?>', this)"><?php*/
        ?><form method=post name=payment action="javascript:void(0);" onsubmit="<?=$buy?>"><?php
        ?><button type=submit class="btn buy EN<?=$mcu===1?' one':''?>"><?=$mcu?></button><span><?=$price_label?></span><?php 
        ?></form><?php
    }
    
    /*
    public function calculateSignature($arrData, $signType = 'request') {
        $shaString = '';
        ksort($arrData);
        foreach ($arrData as $k => $v) {
            $shaString .= "$k=$v";
        }
        if ($signType == 'request') {
            $shaString = $this->urlRouter->cfg['payfor_pass_phrase_out'] . $shaString . $this->urlRouter->cfg['payfor_pass_phrase_out'];
        }
        else {
            $shaString = $this->urlRouter->cfg['payfor_pass_phrase_in'] . $shaString . $this->urlRouter->cfg['payfor_pass_phrase_in'];
        }
        $signature = hash('sha256', $shaString);
        return $signature;
    }
    */
    
    private function paypalButton($name, $price) {
        $sandbox = $this->urlRouter->cfg['server_id']==99 ? true : false;
        $business = $sandbox ? 'nooralex-facilitator@gmail.com' : 'nooralex@gmail.com';
        $webscr = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $logo = $this->urlRouter->cfg['url_resources'] . '/img/mourjan-logo-120'.$this->urlRouter->_png;
        $return_url = $this->urlRouter->cfg['host'] . '/buy/' . ($this->urlRouter->siteLanguage!='ar' ? $this->urlRouter->siteLanguage . '/' : '') . '?paypal=success&item='.$name;
        $notify_url = $this->urlRouter->cfg['host'] . '/bin/ppipn.php';
        $cancel_url = $this->urlRouter->cfg['host'] . '/buy/' . ($this->urlRouter->siteLanguage!='ar' ? $this->urlRouter->siteLanguage . '/' : '') . '?paypal=cancel';
        
        echo "<form action='{$webscr}' method='post'>";
        
        // Identify your business so that you can collect the payments
        echo "<input type='hidden' name='business' value='{$business}'>";
        
        // Specify a Buy Now button
        echo "<input type='hidden' name='cmd' value='_xclick'>";
        echo "<input type='hidden' name='image_url' value='{$logo}'>";
        echo "<input type='hidden' name='return' value='{$return_url}'>";
        echo "<input type='hidden' name='notify_url' value='{$notify_url}'>";
        echo "<input type='hidden' name='cancel_return' value='{$cancel_url}'>";
        echo "<input type='hidden' name='cbt' value='return to mourjan'>";
        echo "<input type='hidden' name='custom' value='{$this->user->info['id']}'>";
        
        //  Specify details about the item that buyers will purchase.
        echo "<input type='hidden' name='item_name' value='{$name}'>";
        echo "<input type='hidden' name='currency_code' value='USD'>";
        echo "<input type='hidden' name='amount' value='{$price}'>";
        echo "<input type='hidden' name='no_note' value='1'>";
        echo "<input type='hidden' name='no_shipping' value='1'>";
        
        echo "<input type=image name=submit border='0' src='https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif' alt='PayPal - The safer, easier way to pay online'>";
        echo "<img alt='' border='0' width='1' height='1' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif'></form>";
    }
    
    
    function main_pane() {
        $adLang='';
        if (!$this->router->isArabic()) { $adLang=$this->router->language.'/'; }
        
        ?><div class="row viewable"><div class="row pc mt-32"></div><?php
        switch ($this->router->module) {
            case 'buyu':
                $this->renderBuyU();                
                break;
                
            case 'buy':
                if (!$this->user()->isLoggedIn()) { 
                    echo '<div>';
                    if (!$this->isMobile) { $this->renderLoginPage(); }
                }
                else {
                    if ($this->router->isMobile) {                        
                        $uid=$this->user->id();
                        $data = $this->user->getStatement($uid, 0, false, null, $this->router->language);
                        $hasError = 0;
                        if($data && $data['balance']!==null){
                            $subHeader = '<span class="mc24"></span>'.$data['balance'].' '.$this->lang['gold'];
                        }else{
                            $subHeader = '<br />';
                            $hasError = 1;
                        }
                        ?><p class="ph phb db bph"><?php
                            echo $subHeader.' ';
                        ?></p><?php 
                    }
                    if ($this->router->language==='ar') {
                        echo '<div class="doc ar">';
                    }
                    else {
                        echo '<div class="doc en">';
                    }
                    //if(isset($this->user->pending['PAYPAL_OK'])){
                    if(isset($_GET['paypal']) && $_GET['paypal']=='success'){
                        $goldCount = preg_replace('/\..*/', '', $_GET['item']);
                        $msg = preg_replace('/{gold}/', $goldCount, $this->lang['paypal_ok']);
                        echo "<div class='mnb rc'><p><span class='done'></span> {$msg}</p></div>";
                        //unset($this->user->pending['PAYPAL_OK']);
                        $this->user->update();
                    }
                    /*if(isset($this->user->pending['PAYPAL_FAIL'])){
                        echo "<div class='mnb rc'><p><span class='fail'></span> {$this->lang['paypal_fail']}</p></div>";
                        unset($this->user->pending['PAYPAL_FAIL']);
                        $this->user->update();
                    }
                    if(isset($this->user->pending['PAYPAL_OLD'])){
                        echo "<div class='mnb rc'><p><span class='fail'></span> {$this->lang['paypal_old']}</p></div>";
                        unset($this->user->pending['PAYPAL_OLD']);
                        $this->user->update();
                    }*/
                    
                
                    $products = $this->urlRouter->db->queryCacheResultSimpleArray("products",
                                        "select product_id, name_ar, name_en, usd_price, mcu  
                                        from product 
                                        where iphone=0 
                                        and blocked=0 
                                        and usd_price > 3 
                                        order by mcu asc",
                                        null, 0, $this->urlRouter->cfg['ttl_long'], TRUE);
                    
                    //$products['100.gold'] = ['100.gold', '100 ذهبية', '100 gold', 49.99, 100];
                    echo '<ul class="table">';
                    $i=1;$j=0;
                    foreach($products as $product){
                        $alt = $i++%2;
                        //echo "<li>{$product[ $this->urlRouter->siteLanguage == 'ar' ? 1 : 2]}</li><li>{$product[3]} USD</li><li class='tt'>
                        //<form action='/checkout/' METHOD='POST'><input type='image' name='paypal_submit' id='sub{$j}'  
                        //src='https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif' border='0' align='top' alt='Pay with PayPal'/>
                        //<input type='hidden' name='product' value='{$product[0]}' /></form></li>";
                        echo "<li>{$product[ $this->urlRouter->siteLanguage == 'ar' ? 1 : 2]}</li><li>".number_format($product[3],2)." USD</li><li class='tt'>";
                        $this->paypalButton($product[0], $product[3]);
                        echo "</li>";
                        $j++;
                    }
                    
                    echo '</ul>';
                    
                    ?><br /><div class="bth ctr"><img width="319" height="110" src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" alt="Buy now with PayPal"></div><?php
                    
                    ?><!--<div class="htf db">< //$this->lang['paypal_suspended'] </div>--><?php
                }
                break;
                
            case 'iguide':
                echo '<div class=row><div class="col-2 side">', $this->side_pane(), '</div><div class=col-10><div class="card card-doc">';
                $imgPath = $this->router->config->imgURL.'/presentation2/';
                $this->lang['guide_apple'] = preg_replace(
                ['/{IMG0}/','/{IMG01}/','/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/','/{IMG7}/','/{IMG8}/'], 
                [
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'settings-icon'.$this->router->_jpg.'" />',
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'home-icon'.$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-home'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-settings'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-account'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-activate'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-activated'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-balance'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-buy'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-coins'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                ], 
                $this->lang['guide_apple']);
                echo '<p>', $this->lang['guide_apple_skip'], '</p>';
                echo '<div class=uld>', $this->lang['guide_apple'], '</div>';
                echo '</div></div></div>';
                break;
                
            case 'guide':
                echo '<div class=row><div class="col-2 side">', $this->side_pane(), '</div><div class=col-10><div class="card card-doc">';
                
                $imgPath = $this->router->config->imgURL.'/presentation2/';
                                
                $this->lang['guide_droid'] = preg_replace(
                ['/{IMG0}/','/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/','/{IMG7}/'], 
                [
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'settings-icon'.$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-lang'.$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-country'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-home'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-settings'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-connect'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-connected'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-purchase'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" />',
                ], 
                $this->lang['guide_droid']);
                echo '<p>', $this->lang['guide_droid_skip'], '</p>';
                echo '<ul class=uld>', $this->lang['guide_droid'], '</ul>';
                echo '</div></div></div>';
                break;
                
            case 'gold':
                $this->renderGold();                
                break;
                
            case 'premium':
                $this->renderPremium();
                break;
            
            case 'advertise':
                if ($this->urlRouter->siteLanguage=='ar') {
                    echo '<div class="doc ar">';
                    ?><h1>أعلن مع مرجان</h1>
                <p>قم بتسويق شركتك، منتجاتك أو خدماتك بأسلوب متميز مستفيداً من أكثر من 3.5 مليون انطباع وأكثر من 250،000 زائر فريد شهريا على موقع مرجان نسبةً لإحصائيات Google Analytics.</p>
                <p><b>١ CPM = ١٠٠٠ إنطباع</b><br /><b>$ = دولار أميركي</b></p>
                <ul>
                    <li class="h v1">
                        نوع الإعلان
                    </li><li class="h v2">
                        عرض
                    </li><li class="h v3">
                        طول
                    </li><li class="h v4">
                        CPM
                    </li><li class="h v5">
                        CPM للقسم
                    </li><li class="h v6">
                        200 CPM
                    </li><li class="h v7">
                        500 CPM
                    </li><li class="h v8">
                        1000 CPM
                    </li>
                    
                    <li class="v10">
                        مربع كبير
                    </li><li class="v11">336px</li><li class="v12">280px</li><li class="v13">9$</li><li class="v14">-</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/large_rectangle_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/large_rectangle_ad'.$this->urlRouter->_png ?>" alt="large rectangle ad thumb" /></a>
                        <p>أفضل خانة لتسويق منتجك على الصفحة الأولى لموقع مرجان من خلال مربع كبير يعكس تميز شكل وأداء الموقع على منتجك</p>
                        <p><b>متوفر فقط على الصفحة الأولى لموقع مرجان</b></p>
                    </li>
                    
                    <li class="v10">
                        مربع متوسط أعلى الصفحة
                    </li><li class="v11">*300px</li><li class="v12">250px</li><li class="v13">9$</li><li class="v14">11$</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/top_medium_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/top_medium_ad'.$this->urlRouter->_png ?>" alt="top medium rectangle ad thumb" /></a>
                        <p>انطباعات أقل، فعالية أكثر. اعلانك لن يفوت في أهم خانة لعرض الإعلان</p>
                        <p><b>متوفر فقط على الصفحات الخاصة بتفاصيل الإعلانات</b></p>
                        <p><b>*هذه الخانة تتطلب تأمين حجمين من الإعلان 300px*250px و 250px*250px</b></p>
                    </li>
                    
                    <li class="v10">
                        راية
                    </li><li class="v11">728px</li><li class="v12">90px</li><li class="v13">7$</li><li class="v14">9$</li><li class="v15">5.6$/CPM</li><li class="v16">4.9$/CPM</li><li class="v17">4.2$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/leaderboard_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/leaderboard_ad'.$this->urlRouter->_png ?>" alt="leaderboard ad thumb" /></a>
                        <p>سوق منتجك في أعلى خانة على أكثر الصفحات مشاهدةً على موقع مرجان</p>
                        <p><b>متوفر فقط من ثاني صفحة وما فوق لصفحات عرض الإعلانات</b></p>
                    </li>
                    
                    
                    <li class="v10">
                        مربع متوسط
                    </li><li class="v11">300px</li><li class="v12">250px</li><li class="v13">6$</li><li class="v14">8$</li><li class="v15">4.8$/CPM</li><li class="v16">4.2$/CPM</li><li class="v17">3.6$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad'.$this->urlRouter->_png ?>" alt="medium rectangle ad thumb" /></a>
                        <p>موقع مميز مع عدد انطباعات يضمن تسويق منتجك بسرعة وفعالية</p>
                        <p><b>متوفر على كافة صفحات عرض الإعلانات</b></p>                        
                    </li>
                    
                    <li class="v10">
                        مربع صغير
                    </li><li class="v11">200px</li><li class="v12">200px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/CPM</li><li class="v16">2.8$/CPM</li><li class="v17">2.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad'.$this->urlRouter->_png ?>" alt="small square ad thumb" /></a>
                        <p>أفضل صفقة مع سعر تشجيعي، خانة إعلان في أعلى القسم الجانبي لصفحات عرض الإعلانات</p>
                        <p><b>متوفر فقط من ثاني صفحة وما فوق لصفحات عرض الإعلانات</b></p>
                    </li>
                </ul>
                <a class="bt rc sh" href="/contact/<?= $this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/' ?>" rel="nofollow">تواصل معنا للإستفسار والحجز</a><?php
                }else {
                echo '<div class="doc en">';
                ?><h1>Advertise with Mourjan.com</h1>
                <p>Market your online business with style and benefit from over 3.5 million impressions and over 250,000 unique visitors per month based on Google Analytics.</p>
                <p><b>1 CPM = 1000 impressions</b><br /><b>$ = USD</b></p>
                <ul>
                    <li class="h v1">Ad Zone</li><li class="h v2">Width</li><li class="h v3">Height</li><li class="h v4">CPM</li><li class="h v5">CPM by section</li><li class="h v6">200 CPM</li><li class="h v7">500 CPM</li><li class="h v8">1000 CPM</li>
                    
                    <li class="v10">Large Rectangle</li><li class="v11">336px</li><li class="v12">280px</li><li class="v13">9$</li><li class="v14">NA</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/large_rectangle_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/large_rectangle_ad'.$this->urlRouter->_png ?>" alt="large rectangle ad thumb" /></a>
                        <p>The best placement to brand your business on Mourjan.com's homepage with a viewable Large rectangle ad that will reflect Mourjan.com's powerful performance and style onto your business identity</p>
                        <p><b>Available only at the homepage of Mourjan.com</b></p>
                    </li>
                    
                    <li class="v10">Top Medium Rectangle</li><li class="v11">300px*</li><li class="v12">250px</li><li class="v13">9$</li><li class="v14">11$</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/top_medium_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/top_medium_ad'.$this->urlRouter->_png ?>" alt="top medium rectangle ad thumb" /></a>
                        <p>Fewer impressions, more clicks. The focus zone, your ad will not be missed in the most powerful ad zone at Mourjan.com</p>
                        <p><b>Available on all Mourjan.com's ad detail pages</b></p>
                        <p><b>*This ad zone serves 2 ad sizes: 300px*250px and 250px*250px</b></p>
                    </li>
                    
                    <li class="v10">Leaderboard</li><li class="v11">728px</li><li class="v12">90px</li><li class="v13">7$</li><li class="v14">9$</li><li class="v15">5.6$/CPM</li><li class="v16">4.9$/CPM</li><li class="v17">4.2$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/leaderboard_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/leaderboard_ad'.$this->urlRouter->_png ?>" alt="leaderboard ad thumb" /></a>
                        <p>Be on top of Mourjan.com's most viewed pages for maximum and yet powerful exposure for your business</p>
                        <p><b>Available only from the second page and up while browsing search result pages</b></p>
                    </li>
                    
                    
                    <li class="v10">Medium Rectangle</li><li class="v11">300px</li><li class="v12">250px</li><li class="v13">6$</li><li class="v14">8$</li><li class="v15">4.8$/CPM</li><li class="v16">4.2$/CPM</li><li class="v17">3.6$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad'.$this->urlRouter->_png ?>" alt="medium rectangle ad thumb" /></a>
                        <p>Perfect for maximum exposure while benefitting from thousands of search result pages in a catchy ad placement zone that guarantees exposure for your business</p>
                        <p><b>Available on all Mourjan.com's search result pages</b></p>                        
                    </li>
                    
                    <li class="v10">Small Square</li><li class="v11">200px</li><li class="v12">200px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/CPM</li><li class="v16">2.8$/CPM</li><li class="v17">2.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad'.$this->urlRouter->_png ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad'.$this->urlRouter->_png ?>" alt="small square ad thumb" /></a>
                        <p>Deal of the day, the small square ad zone benefits a top placement on the side bar of search result pages for maximum exposure with an encouraging price</p>
                        <p><b>Available only from the second page and up while browsing search result pages</b></p>
                    </li>
                </ul>
                <a class="bt rc sh" href="/contact/<?= $this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/' ?>" rel="nofollow">Contact us for more information</a>
                <?php 
                }
                /*
                    
                    <li class="vd"><a href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad'.$this->urlRouter->_png ?>"><img src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad'.$this->urlRouter->_png ?>" alt="medium rectangle ad thumb" /></a><p></p></li>
                    <li class="v10">Mobile</li><li class="v11">300px</li><li class="v12">50px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/cpm</li><li class="v16">2.8$/cpm</li><li class="v17">2.4$/cpm</li>
                    <li class="vd"><a href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad'.$this->urlRouter->_png ?>"><img src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad'.$this->urlRouter->_png ?>" alt="small square ad thumb" /></a><p></p></li>
                <?php */ break;
          
                
            case 'about':
                $this->renderAbout();               
                break;
            
            case 'terms':
                $this->renderTerms();
                break;
            
            case 'privacy':
                $this->renderPrivacy();
                break;
                       
            case 'faq':
                $this->renderFAQ();
                break;
            
            default:
                break;
        }
        ?></div><?php
        
    }
    
    
    private function renderGold() : void {
        ?><script>function chapter(n){
            let c=document.querySelector('div#chapter'+n);
            let li=c.closest('li');
            for(const i of c.closest('ul').querySelectorAll('li')) {if (i!==li)i.classList.remove('open');}
            li.classList.toggle('open');li.scrollIntoView(true);}</script><?php
            
        $rtl=$this->router->isArabic();
        
        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        
        ?><div class=cw10><div class="card doc"><div class="view gold"><?php
        ?><h2 class=title><?=$rtl?'كل ما تريد ان تعرفه عن':'Get your ad<br>featured and visible with'?><img alt="mourjan" style="" src="<?=$this->router->config->cssURL?>/1.0/assets/premium-en-v1.svg" /></h2><?php
                
        $imgPath=$this->router->config->imgURL.'/presentation2/';
        
        if ($this->user->isLoggedIn()) {
            $this->activation_phone_number=$this->user->getProfile()->getMobileNumber();
            if ($this->activation_phone_number>0) {
                $this->currency_id=(\substr(\strval($this->activation_phone_number), 0, 3)==='971')?'AED':'USD';
            }
            else if ($this->router->countryId===2) {
                $this->currency_id='AED';  
            }
        }
        else if ($this->router->countryId===2) {
            $this->currency_id='AED';
        }
        
        
        ?><div class="inline-flex w100 ff-cols"><?php
        ?><ul class=gm><?php
        
        ?><li><a href="javascript:chapter(1)"><?=$rtl?'كيف يعمل؟ وما هو؟':'How it works'?><span class=disclosure>›</span></a><?php
        ?><div id=chapter1><?php
        /*?><p><?=$this->lang['gold_p2']?></p><?php*/
        ?><p><span class=fw-500>Buy days of</span> <span class=fw-500 style="color:var(--premium);text-decoration:underline">premium ad listing</span> and make your ad more prominent and highlighted to attract a greater level of interest and make it easier to be found.</p><?php
        ?><p class=fw-500>You can buy here more days and benefit from exclusive packages:</p><?php        
        
        $products=$this->router->db->queryResultArray("select product_id, name_ar, name_en, usd_price, aed_price, mcu, id from product where web=1 and blocked=0 order by mcu asc");
        foreach ($products as $product) {        
                $this->payfortButton($product['ID'], \intval($product['MCU']), $product[$this->currency_id.'_PRICE']);
        }
        if ($this->currency_id==='AED') {
            ?><p class="tfoot small"><?=$rtl?'الاسعار بالدرهم الاماراتي شاملة الضريبة على القيمة المضافة ٥٪':'Prices are in AED, Value-added tax 5% price inclusive.'?></p><?php            
        }
        else {
            ?><p class="tfoot small"><?=$rtl?'الأسعار بالدولار الأمريكي قد تخضع لضريبة القيمة المضافة':'Prices are in US dollar may be subject to VAT (value-added tax).'?></p><?php
        }
    
        ?><p class="flex va-center mt-32"><span>After you buy days of&nbsp;</span><span class=fw-500>mourjan <span style="color:var(--premium)">PREMIUM</span></span><span>,&nbsp;</span><span class=empty-coin style="font-weight:700;font-size:1.25em;color:var(--premium)">1</span><span>&nbsp;day will be deducted daily until:</span></p><?php
        //echo "<p>{$this->lang['gold_p2_0']}</p>";
        ?><div class="flex fw-500 va-center" style="height:32px"><span>&bullet;&nbsp;The specified days have expired</span></div><?php
        ?><div class="flex fw-500 va-center" style="height:32px"><span>&bullet;&nbsp;You have&nbsp;</span><span class=empty-coin style="font-weight:700;font-size:1.25em;color:var(--premium)">0</span><span>&nbsp;days left in your balance</span></div><?php
        ?><div class="flex fw-500 va-center" style="height:32px"><span>&bullet;&nbsp;You chose to end the premium ad listing</span></div><?php
        ?><div class="flex fw-500 va-center" style="height:32px"><span>&bullet;&nbsp;You chose to completely stop the ad</span></div><?php
        ?><p class="tfoot" style="margin-bottom:64px">You can choose to stop or cancel your ad at any time.</p><?php
        ?></div></li><?php
        
        
        ?><li id="how-to"><a href="javascript:chapter(2)"><?=$rtl?'كيفية الشراء':'How to buy it'?><span class=disclosure>›</span></a><?php
        ?><div id=chapter2><?php
        ?><h4 class=mb-16>A) Using your credit card</h4><?php
        ?><p><span class=fw-500>mourjan <span style="color:var(--premium)">PREMIUM DAYS</span></span> can be purchased directly using your credit card. Choose the card that is best suitable for you or keep on reading to find out other payment options.</p><?php
        ?><div class=fw-500>BUY NOW WITH:</div><?php
        ?><div class=flex><a class="btn buy type visa" href="<?=$this->router->getLanguagePath('/buyu/')?>"></a><a class="btn buy type master" href="<?=$this->router->getLanguagePath('/buyu/')?>"></a></div><?php
        ?><div class=mb-32 style="width:60px;height:2px;background: var(--premium);margin-top:40px"></div><?php
        
        ?><h4 class=mb-16>B) Using PAYPAL</h4><?php
        ?><p>If you have a PayPal account and would like to purchase <span class=fw-500>mourjan <span style="color:var(--premium)">PREMIUM</span></span> now, click on the button below or continue reading for alternatives</p><?php
        ?><div class=fw-500>BUY NOW WITH:</div><?php
        ?><div class=flex><a href="<?=$this->router->getLanguagePath('/buy')?>" class="btn buy type paypal"></a></div><?php
        ?><div class=mb-32 style="width:60px;height:2px;background: var(--premium);margin-top:40px"></div><?php
        /*?><p><?=$this->lang['gold_p2_5_0']?></p><?php
        ?><p><?=$this->lang['gold_p2_5']?></p><?php
        ?><p><?=$this->lang['gold_p2_6'.($this->router->isMobile ? '_m':'')]?></p><?php
        ?><div class=btH><?php
        ?><a href="<?=$this->router->getLanguagePath('/buy')?>"><img width="228" height="44" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-large.png" alt="Buy now with PayPal" /></a><br /><?php 
        ?><a href="<?=$this->router->getLanguagePath('/buy')?>"><img width="319" height="110" src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" alt="Buy now with PayPal" /></a><br /><?php 
        ?></div><?php*/
        //echo '<p>', $this->lang['buy_gold_0'], '</p>';
        ?><h4 class="mb-16">C) Downloading the APP</h4><?php
        ?><p>As an alternative, <span class="fw-500">mourjan <span style="color:var(--premium)">PREMIUM</span></span> can be purchased through mourjan app for Apple and Android. If you do not have mourjan app yet and would like to download it, go to the Google Play Store or the Apple App Store on your mobile and search for mourjan. You can also click on one of the below to go directly to the app page.</p><?php
        
        /*?><p><?=$this->lang['gold_p2_4']?></p><?php*/
        ?><div class="fw-500">DOWNLOAD NOW ON:</div><?php
        ?><div class="flex"><a class="btn buy type android" target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"></a><a class="btn buy type ios" target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"></a></div><?php
        ?><div class="mb-32"></div><?php
        ?></div></li><?php
        
        
        ?><li><a href="javascript:chapter(3)"><?=$rtl?'أهمية الإعلانات المميزة':'<span>Why <span style="color:var(--premium)">PREMIUM</span> ads matter</span>'?><span class=disclosure>›</span></a><?php
        ?><div id=chapter3><?php
        echo \file_get_contents($this->router->config->baseDir.'/web/doc/premium-'.$this->router->language.'.html');
        //echo '<div class=uld>', $this->lang['gold_p1_desc'], '</div></li>';
        ?></div></li><?php
        ?></ul><?php
        
        //echo "<ul class='prices alt rc'>{$this->lang['gold_p2_2']}</ul>";
        //echo "<p class='pad alt rc'>{$this->lang['gold_p2_3']}</p>";
        //echo '<hr><h4 id=how-to>', $this->lang['buy_gold'], '</h4>';
        
        
        

        /*
        echo "<p>{$this->lang['buy_gold_1']}</p>";
        echo '<ul class="alinks"><li><a href="'. $this->router->getLanguagePath('/guide/') .'">', 
            '<img width=119 height=230 src="'.$imgPath.'guide'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" /></a></li>', 
            '<li><a href="/iguide/'.($this->router->isArabic()?'':$this->router->language.'/' ).'"><img width=119 height=230 src="'.$imgPath.'iguide'.($this->router->isArabic()?'-ar':'').$this->router->_jpg.'" /></a></li></ul>';
        */        
        ?></div></div><?php
        $this->docFooter();
        ?></div></div><?php
    }
    
    
    private function renderPremium() : void {
        echo '<div class=row><div class="col-2 side">', $this->side_pane(), '</div><div class=col-10><div class="card doc">';
        echo '<h2 class="card-title">',$this->lang['gold_p1_title'], '</h2>';
        echo '<div class="col-12 block"><div><p>';
        $imgPath = $this->router->config->imgURL.'/presentation2/';
        $this->lang['gold_p1_desc']=\preg_replace(['/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/'], 
                        [
                            '<img width=200 height=150 src="'.$imgPath.'desktop-premium'.$this->router->_jpg.'" />',
                            '<img width=74 height=150 src="'.$imgPath.'mobile-site-premium'.$this->router->_jpg.'" />',
                            '<img width=74 height=150 src="'.$imgPath.'app-premium'.$this->router->_jpg.'" />',
                            '<img width=300 height=228 src="'.$imgPath.'desktop-side-premium'.($this->router->isArabic()?'-ar':'').$this->router->_png.'" />',
                            '<img width=300 height=228 src="'.$imgPath.'desktop-side-hover-premium'.($this->router->isArabic()?'-ar':'').$this->router->_png.'" />',
                            '<img width=74 height=150 src="'.$imgPath.'mobile-bottom-premium'.$this->router->_jpg.'" />'
                        ], 
                        $this->lang['gold_p1_desc']);
                        
        echo '<div class=uld>', $this->lang['gold_p1_desc'], '</div></li></div>';                    
        echo '<div class=btH><a class=bt href="', $this->router->getLanguagePath('/gold/'), '">', $this->lang['back_to_gold'], '</a></div>';
        echo '</div></div></div>';        
    }
    
    
    private function renderBuyU() : void {        
        if (!$this->user->isLoggedIn()) {
            ?></div><?php
            $this->renderLoginPage();
            return;
        }

        ?><div class=row><?php
        ?><div class="col-2 side"><?=$this->side_pane()?></div><?php
        ?><div class=col-10><div class="card doc"><div class="view"><?php
        ?><h2 class=title>Pay with credit card</h2><?php
        $alert='';
        $alert_class='alert';
        $rtl=$this->router->isArabic();
        $this->activation_phone_number=$this->user->getProfile()->getMobileNumber();   
        if (!$this->user->info['email']) {
            $message=$this->lang['requireEmailPay'];
            if ((isset($this->user->info['options']['email']) && isset($this->user->info['options']['emailKey']))) {
                $message=preg_replace('/{email}/', $this->user->info['options']['email'], $this->lang['validateEmailPay']);                    
            }
            ?><div class="alert alert-warning"><span><?=$message?></span></div><?php
        }
        else if ($this->user->getProfile()->isMobileVerified()===false) {
            $alert_class.=' alert-danger';
            if ($number>0) {
                $alert="Your mobile number +".$number." is expired!<br>Please verify your ownership of this number or change it to new one.";
            }
            else {
                $alert="Your account is not verified";                    
            }
        }
        else {                        
            if (!empty( \filter_input_array(\INPUT_POST) ) ) {                
                $response_code=\filter_input(\INPUT_POST, 'response_code', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $response_amount=\filter_input(\INPUT_POST, 'amount', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $response_currency=\filter_input(\INPUT_POST, 'currency', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $response_message=\filter_input(\INPUT_POST, 'response_message', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $response_description=\filter_input(\INPUT_POST, 'order_description', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $response_status=\filter_input(\INPUT_POST, 'status', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                
                if ($response_status==='14') {
                    $alert_class.=' alert-success ff-cols';
                    $alert=$response_message.'<br>'.number_format(floatval($response_amount)/100,2).$response_currency.'<br>'.$response_description.'<br>';
                    $alert.='<a href="'.$this->router->getLanguagePath('/myads/').'">'.$this->lang['MyAccount'].'</a>';

                }
                else {
                    $alert_class.=' alert-warning';
                    $alert=\filter_input(\INPUT_POST, 'response_message', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                }
            }
            
            $products=$this->router->db->queryResultArray("select product_id, name_ar, name_en, usd_price, aed_price, mcu, id from product where web=1 and blocked=0 order by mcu asc");
            if ($this->user->getProfile()->isMobileVerified()||$this->user->level()===9) {
                $this->currency_id=(\substr(\strval($this->activation_phone_number), 0, 3)==='971')?'AED':'USD';
                foreach ($products as $product) {            
                    $this->payfortButton($product['ID'], \intval($product['MCU']), $product[$this->currency_id.'_PRICE']);
                }
                if ($this->currency_id==='AED') {
                    ?><p class="tfoot small"><?=$rtl?'الاسعار بالدرهم الاماراتي شاملة الضريبة على القيمة المضافة ٥٪':'Prices are in AED, Value-added tax 5% price inclusive.'?></p><?php            
                }
                else {
                    ?><p class="tfoot small"><?=$rtl?'الأسعار بالدولار الأمريكي قد تخضع لضريبة القيمة المضافة':'Prices are in US dollar may be subject to VAT (value-added tax).'?></p><?php
                }                
            }
             
        }
        ?></div><?php
        ?><div id="alert" class="<?=$alert_class?>"><?=empty($alert)?'':$alert?></div><?php
        ?><div style="margin:0 0 32px;width:288px;margin-top:40px"><img src="<?=$this->router->config->cssURL?>/1.0/assets/payfort.svg" alt="Verified by PAYFORT" /></div><?php
        $this->docFooter();
        ?></div></div></div><?php
    }     
    
    
    private function docFooter() : void {
        ?><div class=page-footer><?php
        ?><div<?=$this->router->module==='about'?' class="sep cw6"':''?>><img alt="mourjan" style="width:148px;margin-top:80px" src="<?=$this->router->config->cssURL?>/1.0/assets/domain.svg" /></div><?php
        ?><div class=cw12 style="justify-content:flex-end;overflow:hidden"><img style="position:relative;top:56px;width:206px;transform:rotateX(180deg);filter:invert(36%) sepia(39%) saturate(7153%) hue-rotate(200deg) brightness(102%) contrast(106%);" src="<?=$this->router->config->cssURL?>/1.0/assets/emblem.svg"/></div><?php
        ?></div><?php
    }
    
    
    private function renderAbout() : void {
        $ar=$this->router->isArabic();
        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        ?><div class=cw10><div class="card doc"><div class=view><?php
        if ($ar) {
            ?><h2 class=title>كل ما<br>تحتاج ان تعرفه عن<span style="display:inherit;margin-top:20px"><s style="line-height:49px;text-decoration:none">com.</s><img alt="mourjan" style="width:208px;margin-top:6px" src="<?=$this->router->config->cssURL?>/1.0/assets/inline-logo-en.svg" /></span></h2><?php            
        }
        else {
            ?><h2 class=title><span style="display: block;">Everything</span><span>you need to know about</span><img class=domain alt="mourjan" src="<?=$this->router->config->cssURL?>/1.0/assets/domain.svg" /></h2><?php
        }
        ?><div class="cw12 ff-cols va-start"><?php
        if ($this->router->isArabic()) {
            ?><p>في عام ٢٠١٠ تم إنشاء&nbsp;<span itemscope itemtype="https://schema.org/LocalBusiness">موقع مرجان</span> الذي تملكه وتديره شركة مرجان كلاسيفايدس.</p><?php
            ?><p>مع أكثر من ١٥ عامًا من الخبرة في مجال الإعلانات المبوبة وحلول تكنولوجيا المعلومات، كنا نحن فريق العمل  وراء موقع مرجان (<span>mourjan.com</span>) - نبحث عن مشروع جديد يتماشى مع التطور السريع  في شبكة الويب العالمية.</p><?php
            ?><p>وعلى الرغم من أن الإعلانات المبوبة على الإنترنت لم تكن شيئًا جديدًا، ومع وجود العديد من مواقع الإعلانات المبوبة، فقد أدركنا أنه لتحقيق النجاح، كان علينا تقديم شيء جديد. لذلك، بدأنا العمل على <span>موقع مرجان</span> باهتمام وكان هدفنا الرئيسي يتمثل في عمل موقع ويب سريع الأداء من خلال محرك بحث عربي يوفر تجربة ممتعة للمستخدمين الذين يبحثون عن شقة للإيجار أو شراء سيارة.</p><?php
            ?><p>في منتصف عام ٢٠١٢م، كان موقع مرجان (<span>Mourjan.com</span>) يخطو بخطى سريعة أسرع من أي وقت مضى، واستجابة لآراء وتعليقات المستخدمين، مكّن الموقع مستخدميه من نشر إعلانات على الإنترنت مجانًا في بلدانهم المختارة مع تبني دائمًا أحدث التقنيات والاتجاهات في تطوير الموقع و وضع مصلحة المستخدمين نصب أعيننا في المقام اﻷول.</p><?php
            ?><p>حاليا، ما زلنا نعمل على تحسين <span>موقع مرجان</span> وتقديم خدمات جديدة وسنستمر على هذا طوال الوقت. كما نعمل على إضافة بعض الخدمات التي نرى أنها مفيدة وغيرها من الخدمات التي قد تطلب منا ببساط من خلال آراء وتعليقات مستخدمين موقع مرجان اﻷعزاء ﻷنهم هم سبب نجاحنا وتقدمنا على مدار هذه اﻷعوام.
</p><?php
        } 
        else {
            /*
            ?><p>In July 2010, <span itemscope itemtype="https://schema.org/LocalBusiness">mourjan.com</span> was founded.</p><?php
            ?><p>With over 15 years of experience in the field of classifieds and IT solutions, we - the team behind <span>Mourjan.com</span> - were looking for a new venture and specifically in the fast evolving World Wide Web.</p><?php
            ?><p>While online classifieds was not something new and with many top of mind classifieds websites, we knew that in order to succeed we had to deliver something new. Therefore, we started working on <span>Mourjan.com</span> with a main concern of achieving a fast performing website with an Arabic oriented search engine which would deliver a pleasant experience for users who are seeking an apartment to rent or a car to buy.</p><?php
            ?><p>In mid-2012, <span>mourjan.com</span> was faster than ever and in response to the overwhelming users’ feedbacks, the site enabled its users with free online ad posting in their countries of choice while always adopting the latest techniques and trends in website development and having users’ best interest at heart.</p><?php
            ?><p>Currently, we are still working on improving <span>mourjan.com</span> and providing new services. Some services that we see to be helpful and other services that you might simply ask us for. <a href="<?= $this->router->getLanguagePath('/contact/') ?>">Let us know your opinion</a>.</p><?php
             * */            
            ?><p>In 2010, <span itemscope itemtype="https://schema.org/LocalBusiness">mourjan.com</span>, owned and managed by Mourjan Classifieds FZ-LLC, was founded.</p><?php 
            ?><p>With over 15 years of experience in the field of classifieds and IT solutions, we - the team behind <span>mourjan.com</span> - were looking for a new venture and specifically in the fast evolving World Wide Web.</p><?php
            ?><p>While online classifieds was not something new and with many top of mind classifieds websites, we knew that in order to succeed we had to deliver something new. Therefore, we started working on <span>mourjan.com</span> with a main concern of achieving a fast performing website with an Arabic oriented search engine which would deliver a pleasant experience for users who are seeking an apartment to rent or a car to buy.</p><?php
            ?><p>In mid-2012, <span>mourjan.com</span> was faster than ever and in response to the overwhelming users’ feedback, the site enabled its users with free online ad posting in their countries of choice while always adopting the latest techniques and trends in website development and having users’ best interest at heart.</p><?php
            ?><p>Currently, we are still working on improving <span>mourjan.com</span> and providing new services. Some services that we see to be helpful and other services that you might simply ask us for. <a href="<?= $this->router->getLanguagePath('/contact/') ?>">Let us know your opinion</a>.</p><?php     
        }
        ?></div><?php
        
        $streetAddress=$ar?'سنتر دكوانة 1044، الطابق الرابع، السلاف العريض':'4th Floor, Dekwaneh 1044 bldg, New Slav Street';
        $addressLocality=$this->router->isArabic()?'الدكوانة، المتن':'Dekwaneh';
        $comma=$this->router->isArabic()?'، ':', ';
        $addressCountry=$this->router->isArabic()?'لبنان':'Lebanon';
        $egypt=$this->router->isArabic()?'مصر':'Egypt';
        $openingHours=$this->router->isArabic()?'دوام العمل':'Office hours';
        $days=$this->router->isArabic()?'الإثنين الى الجمعة':'Monday to Friday';
        $hours=$this->router->isArabic()?'7:00 ص إلى 3:00 م GMT':'7:00AM to 3:00PM GMT';
        
        ?><div class="cw12 va-start wrap mt-32" itemscope itemtype="https://schema.org/LocalBusiness"><?php
        ?><div class="cw4 va-start ff-cols mb-64"  itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"><div class=i90><img src="<?=$this->router->config->cssURL?>/1.0/assets/about-location.svg"/></div><?php
            ?><div class=addr><?php
            ?><span itemprop="streetAddress"><?=$ar?'مركز الاعمال راكز':'Business Center RAKEZ'?></span><span><span itemprop="addressLocality"><?=$ar?'رأس الخيمة':'Ras Al Khaimah'?></span><br><span itemprop="addressCountry"><?=$ar?'الامارات العربية المتحدة':'United Arab Emirates'?></span></span><?php
            ?></div><?php        
            ?><div class=addr><?php
            ?><span itemprop="streetAddress"><?=$streetAddress?></span><span><span itemprop="addressLocality"><?=$addressLocality?></span><?=$comma?><span itemprop="addressCountry"><?=$addressCountry?></span></span><?php
            ?></div><?php        
        ?></div><?php

        ?><div class="cw4 va-start ff-cols mb-64"><div class=i90><img src="<?=$this->router->config->cssURL?>/1.0/assets/about-phone.svg"/></div><?php
        ?><div class="addr"><span><?=$ar?'الامارات العربية المتحدة':'United Arab Emirates'?></span><span class="fw-500 tel" itemprop="telephone">+971 7 204 8438</span></div><?php
        ?><div class="addr"><span><?=$addressCountry?></span><span class="tel fw-500" itemprop="telephone">+961 70 424 018</span></div><?php
        ?><div class="addr"><span><?=$egypt?></span><span class="tel fw-500" itemprop="telephone">+20 109 136 5353</span></div><?php
        ?></div><?php

        ?><div class="cw4 va-start ff-cols mb-32"><div class=i90><img src="<?=$this->router->config->cssURL?>/1.0/assets/about-time.svg"/></div><?php
            ?><div class="addr"><span class=fw-500><?=$openingHours?></span><span itemprop="openingHours"><?=$days?><br /><?=$hours?></span></div><?php
        ?></div><?php
        ?></div><?php
        
        ?></div><?php
        $this->docFooter();
        ?></div></div><?php
    }
 
    
    private function renderTerms() : void {
        ?><style>.ul{margin:0 40px;list-style:disc outside;display:list-item !important} .ul li{line-height:1.5em;margin-bottom:16px;border:none} .ul li:hover{background-color:initial;color:var(--mdc70)}</style><?php
        
        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        
        ?><div class=cw10><div class="card doc en"><div class=view><h2 class=title>Mourjan Terms of Use</h2><?php                    
        ?><h3>Introduction</h3><?php
        ?><p>Welcome to <span>www.mourjan.com</span> ("Mourjan"). By accessing <span>Mourjan</span> you are agreeing to the following terms, which are designed to make sure that Mourjan works for everyone. Mourjan is provided to you by Mourjan Classifieds FZ-LLC, Business Center RAKEZ, Ras Al Khaimah, registered in United Arab Emirates with number 45000209. This policy is effective January 1st, 2012.</p><?php
?><h3>Using Mourjan</h3><?php
?><p>As a condition of your use of Mourjan you agree that you will not:</p><?php
?><ul class=ul>
    <li>violate any laws;</li>
    <li>violate the Posting Rules;</li>
    <li>post any threatening, abusive, defamatory, obscene or indecent material;</li>
    <li>be false or misleading;</li>
    <li>infringe any third-party right;</li>
    <li>distribute or contain spam, chain letters, or pyramid schemes;</li>
    <li>distribute viruses or any other technologies that may harm Mourjan or the interests or property of Mourjan users;</li>
    <li>impose an unreasonable load on our infrastructure or interfere with the proper working of Mourjan;</li>
    <li>copy, modify, or distribute any other person's content without their consent;</li>
    <li>use any robot spider, scraper or other automated means to access Mourjan and collect content for any purpose without our express written permission;</li>
    <li>harvest or otherwise collect information about others, including email addresses, without their consent;</li>
    <li>bypass measures used to prevent or restrict access to Mourjan.</li>
</ul>
<p>You are solely responsible for all information that you submit to Mourjan and any consequences that may result from your post. We reserve the right at our discretion to refuse or delete content that we believe is inappropriate or breaching the above terms. We also reserve the right at our discretion to restrict a user's usage of the site either temporarily or permanently, or refuse a user's registration.</p>
<h3>Abusing Mourjan</h3>
<p>Mourjan and the Mourjan community work together to keep the site working properly and the community safe. Please report problems, offensive content and policy breaches to us using the reporting system.</p>
<p>Without limiting other remedies, we may issue warnings, limit or terminate our service, remove hosted content and take technical and legal steps to keep users off Mourjan if we think that they are creating problems or acting inconsistently with the letter or spirit of our policies. However, whether we decide to take any of these steps, remove hosted content or keep a user off Mourjan or not, we do not accept any liability for monitoring Mourjan or for unauthorized or unlawful content on Mourjan or use of Mourjan by users.</p>
<h3>Global Marketplace</h3>
<p>Some of Mourjan's features may display your ad on other sites such search engines or our classifieds sites in other countries. By using Mourjan, you agree that your ads can be displayed on these other sites. The terms for our other sites are similar to these terms, but you may be subject to additional laws or other restrictions in the countries where your ad is posted. When you choose to post your ad on another site, you may be responsible for ensuring that it does not violate our other site policies. We may remove your ad if it is reported on any our sites, or if we believe it causes problems or violates any law or policy.</p>
<h3>Fees and Services</h3>
<p>Using Mourjan is generally free, but we sometimes charge a fee for certain services. If the service you use incurs a fee, you'll be able to review and accept terms that will be clearly disclosed at the time you post your ad. Our fees are quoted in your local currency and/or US Dollar, and we may change them from time to time. We'll notify you of changes to our fee policy by posting such changes on the site. We may choose to temporarily change our fees for promotional events or new services; these changes are effective when we announce the promotional event or new service.</p>
<p>Our fees are non-refundable, and you are responsible for paying them when they're due. If you don't, we may limit your ability to use the services. If your payment method fails or your account is past due, we may collect fees owed using other collection mechanisms.</p>
<h3>Content</h3>
<p>Mourjan contains content from us, you, and other users. Mourjan is protected by copyright laws and international treaties. Content displayed on or via Mourjan is protected as a collective work and/or compilation, pursuant to copyrights laws and international conventions. You agree not to copy, distribute or modify content from Mourjan without our express written consent. You may not disassemble or decompile, reverse engineer or otherwise attempt to discover any source code contained in Mourjan. Without limiting the foregoing, you agree not to reproduce, copy, sell, resell, or exploit for any purposes any aspect of Mourjan (other than your own content). When you give us content, you are granting us and representing that you have the right to grant us, a non-exclusive, worldwide, perpetual, irrevocable, royalty-free, sub-licensable right to exercise the copyright, publicity, and database rights to that content.</p><?php

?><p>By using Mourjan and any of it's paid services, you acknowledge that:</p><?php
?><ul class=ul>
    <li>Your age is 18 or above.</li>
    <li>If you make a payment for our products or services on our website, the details you are asked to submit will be provided directly to our payment provider via a secured connection.</li>
    <li>The cardholder must retain a copy of transaction records and Merchant policies and rules.</li>
    <li>We accept payments online using Visa and MasterCard credit/debit card in AED and USD currencies.</li>
    <li>Multiple transactions may result in multiple postings to the cardholder’s monthly statement.</li>
    <li>Mourjan will NOT deal or provide any services or products to any of OFAC (Office of Foreign Assets Control) sanctions countries in accordance with the law of United Arab Emirates.</li>
</ul><?php

?><h3>Infringement</h3>
<p>Do not post content that infringes the rights of third parties, This includes, but is not limited to, content that infringes on intellectual property rights such as copyright and trademark (e.g. offering counterfeit items for sale). A large number of very varied products are offered on Mourjan by private individuals. Entitled parties, in particular the owners of copyright, trademark rights or other rights owned by third parties can report any offers which many infringe on their rights, and submit a request for this offer to be removed. If a legal representative of the entitled party reports this to us in the correct manner, products infringing on the intellectual property rights will be removed by Mourjan.</p>
<h3>Liability</h3>
<p>Nothing in these terms shall limit our liability for fraudulent misrepresentation, for death or personal injury resulting from our negligence or the negligence of our agents or employees. You agree not to hold us responsible for things other users post or do.</p>
<p>We do not review users' postings and are not involved in the actual transactions between users. As most of the content on Mourjan comes from other users, we do not guarantee the accuracy of postings or user communications or the quality, safety, or legality of what's offered.</p>
<p>In no event do we accept liability of any description for the posting of any unlawful, threatening, abusive, defamatory, obscene or indecent information, or material of any kind which violates or infringes upon the rights of any other person, including without limitation any transmissions constituting or encouraging conduct that would constitute a criminal offence, give rise to civil liability or otherwise violate any applicable law.</p>
<p>We cannot guarantee continuous, error-free or secure access to our services or that defects in the service will be corrected. While we will use reasonable efforts to maintain an uninterrupted service, we cannot guarantee this and we do not give any promises or warranties (whether express or implied) about the availability of our services.</p>
<p>Accordingly, to the extent legally permitted we expressly disclaim all warranties, representations and conditions, express or implied, including those of quality, merchantability, merchantable quality, durability, fitness for a particular purpose and those arising by statute. We are not liable for any loss, whether of money (including profit), goodwill, or reputation, or any special, indirect, or consequential damages arising out of your use of Mourjan, even if you advise us or we could reasonably foresee the possibility of any such damage occurring. Some jurisdictions do not allow the disclaimer of warranties or exclusion of damages, so such disclaimers and exclusions may not apply to you.</p>
<p>Despite the previous paragraph, if we are found to be liable, our liability to you or any third party (whether in contract, tort, negligence, strict liability in tort, by statute or otherwise) is limited to the greater of (a) the total fees you pay to us in the 12 months prior to the action giving rise to liability, and (b) 100 US Dollar.</p>
<p>Refunds will be done only through the Original Mode of Payment.</p>
<h3>Personal Information</h3>
<p>By using Mourjan, you agree to the collection, transfer, storage and use of your personal information by Mourjan on servers located in the Germany, and Lebanon as further described in our <a href="<?=$this->router->getLanguagePath("/privacy/")?>">Privacy Policy</a>. You also agree to receive marketing communications from us unless you tell us that you prefer not receive such communications.</p>
<h3>Account Termination/Delete</h3>
<p>By using Mourjan, you agree that your account and any collected data cannot and will not be deleted for the sole reason of having the required material to respond to a claim or resolve a dispute that might rise in the future.</p>
<h3>Resolution of disputes</h3>
<p>If a dispute arises between you and Mourjan, we strongly encourage you to first contact us directly to seek a resolution by going to the Mourjan <a href="<?=$this->router->getLanguagePath("/contact/")?>">contact page</a>. We will consider reasonable requests to resolve the dispute through alternative dispute resolution procedures, such as mediation or arbitration, as alternatives to litigation.</p>
<h3>General</h3>
<p>These terms and the other policies posted on Mourjan constitute the entire agreement between Mourjan and you, superseding any prior agreements.</p>
<p>This Agreement shall be governed and construed in all respects by the laws of United Arab Emirates. You agree that any claim or dispute you may have against Mourjan Classifieds FZ-LLC must be resolved by the courts of United Arab Emirates. You and Mourjan both agree to submit to the exclusive jurisdiction of the United Arab Emirates Courts.</p>
<p>If we don't enforce any particular provision, we are not waiving our right to do so later. If a court strikes down any of these terms, the remaining terms will survive. We may automatically assign this agreement in our sole discretion in accordance with the notice provision below.</p>
<p>Except for notices relating to illegal or infringing content, your notices to us must be sent by registered mail to Mourjan Classifieds FZ-LLC, Business Center RAKEZ, Ras Al Khaimah, registered in United Arab Emirates with number 45000209, P.O. Box No. 294474. We will send notices to you via the email address you provide, or by registered mail. Notices sent by registered mail will be deemed received five days following the date of mailing.</p>
<p>Mourjan Policies and Terms & Conditions may be changed or updated occasionally to meet the requirements and standards. Therefore Users are encouraged to frequently visit these sections in order to be updated about the changes on the website. Modifications will be effective on the day they are posted.</p>
<p><b>Terms Of Use updated 29 Apr 2020</b></p><?php
?></div><?php
        $this->docFooter();
        ?></div></div><?php
    }
    
    
    private function renderPrivacy() : void {
        ?><style>.ul{margin:0 40px;list-style:disc outside;display:list-item !important} .ul li{line-height:1.5em;margin-bottom:16px;border:none} .ul li:hover{background-color:initial;color:var(--mdc70)}</style><?php

        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        ?><div class=cw10><div class="card doc en"><div class=view><?php
        ?><h2 class=title>Privacy policy</h2><?php
?><p>This privacy policy describes how we handle your personal information. We collect, use, and share personal information to help the Mourjan website ('Mourjan') work and to keep it safe (details below). In formal terms, Mourjan Classifieds FZ-LLC, Business Center RAKEZ, Ras Al Khaimah, registered in United Arab Emirates with number 45000209, acting itself and through its subsidiaries, is the 'data controller' of your personal information. This policy is effective 1 Jan 2012.</p>
<h3>Collection</h3>
<p>Information posted on Mourjan is obviously publicly available. Our servers are located in Germany and Lebanon. Mourjan will hold and transmit your information in a safe, confidential and secure environment. If you choose to provide us with personal information, you are consenting to the transfer and storage of that information on our servers in Germany and Lebanon. We collect and store the following personal information:</p>
<ul class=ul>
    <li>email address, physical contact information;</li>
    <li>computer sign-on data, statistics on page views, traffic to and from Mourjan and ad data (all through cookies - you can take steps to disable the cookies on your browser although this is likely to affect your ability to use the site);</li>
    <li>other information, including users IP address and standard web log information.</li>
    <li>Google Analytics data such as age, gender and interests based on Display Advertising (e.g., Remarketing, Google Display Network Impression Reporting, the DoubleClick Campaign Manager integration, or Google Analytics Demographics and Interest Reporting).</li>
    <li>Visitors can opt-out of Google Analytics for Display Advertising and customize Google Display Network ads using the <a href='https://www.google.com/settings/ads'>Ads Settings</a> or by downloading and installing <a href='https://tools.google.com/dlpage/gaoptout/'>Google Analytics opt-out browser add-on</a>.</li>
    <li>All credit/debit cards details and personally identifiable information will <b>NOT</b> be stored, sold, shared, rented or leased to any third parties.</li>
</ul>
<h3>Use</h3>
<p>We use users' personal and collected information to:</p>
<ul class=ul>
    <li>provide our services;</li>
    <li>resolve disputes and troubleshoot problems;</li>
    <li>encourage safe trading and enforce our policies;</li>
    <li>customize users' experience, measure interest in our services, and inform users about services and updates;</li>
    <li>communicate marketing and promotional offers to you;</li>
    <li>do other things for users as described when we collect the information.</li>
</ul>
<h3>Disclosure</h3>
<p>We don't sell or rent your personal information to third parties for their marketing purposes without your explicit consent. We may disclose personal information to respond to legal requirements, enforce our policies, respond to claims that a posting or other content violates others' rights, or protect anyone's rights, property, or safety (for example, if you submit false contact details or impersonate another person, we may pass your personal information to any aggrieved third party, their agent or to any law enforcement agency). We may also share personal information with service providers who help with our business operations.</p>
<h3>Cookies</h3>
<ul class=ul>
    <li>Our website uses cookies, web beacons, and third-parties to provide you with services that support your buying and selling activities within our online marketplace. To protect your privacy, use of these tools is limited.</li>
    <li>Mourjan and third-party vendors, including Google, use first-party cookies (such as the Google Analytics cookies) and third-party cookies (such as the DoubleClick cookie) together to report how Mourjan's ad impressions, other uses of ad services, and interactions with these ad impressions and ad services are related to visits to Mourjan.</li>
</ul>
<h3>About cookies</h3>
<p>Cookies are small files placed on the hard drive of your computer. Mourjan uses both persistent/permanent and session cookies to provide services to you and help ensure account security. Most cookies are 'session cookies', meaning that they are automatically deleted from your hard drive once you end your session (log out or close your browser).</p>
<h3>Mourjan uses cookies on certain pages of the website to:</h3>
<ul class=ul>
    <li>Enable you to enter your password less frequently during a session.</li>
    <li>Provide information that is targeted to your interests.</li>
    <li>Promote and enforce trust and safety.</li>
    <li>Offer certain features that are only available through the use of cookies.</li>
    <li>Measure promotional effectiveness.</li>
    <li>Analyse our site traffic.</li>
</ul>
<h3>Cookies we use for trust and safety</h3>
<p>Mourjan uses cookies, to help ensure that your account security is not compromised and to spot irregularities in behaviour to prevent your account from being fraudulently taken over.</p>
<h3>Your choices about cookies</h3>
<p>We offer certain features that are only available through the use of a cookie. You're always free to decline cookies if your browser permits. However, if you decline cookies, you may not be able to use certain features on the website, and you may be required to re-enter your password more frequently during a session.</p>
<h3>Web beacons</h3>
<p>A web beacon is an electronic image, called a single-pixel (1x1) or clear GIF placed in the web page code. Web beacons serve many of the same purposes as cookies. In addition, web beacons are used to track the traffic patterns of users from one page to another in order to maximise web traffic flow.</p>
<h3>Use of cookies and web beacons by third parties</h3>
<p>We may work with other companies who place cookies or web beacons on our websites. These service providers help operate our websites, by for example compiling anonymous site metrics and analytics. We require these companies to use the information they collect only to provide us with these services under contract, and not for their own purposes.</p>
<p>Some of the advertisements you see on the Site are selected and delivered by third parties, such as ad networks, advertising agencies, advertisers, and audience segment providers. These third parties may collect information about you and your online activities, either on the Site or on other websites, through cookies, web beacons, and other technologies in an effort to understand your interests and deliver to you advertisements that are tailored to your interests.</p>
<p>Please remember that we do not have access to, or control over, the information these third parties may collect therefore the information practices of these third parties are not covered by this privacy policy.</p>
<p>We do not permit third-party content on Mourjan (such as classifieds item listings) to include cookies or web beacons. If you believe a listing might be collecting personal information or using cookies, please report it to support@mourjan.com.</p>
<h3>Using Information from Mourjan</h3>
<p>You may use personal information gathered from Mourjan only to follow up with another user about a specific posting, not to send spam or collect personal information from someone who has not agreed to that.</p>
<h3>Marketing</h3>
<p>If you do not wish to receive marketing communications from us, you can simply email us at any time.</p>
<h3>Remarketing with Google Analytics</h3>
<ul class=ul>
    <li>Mourjan uses Re-marketing with Google Analytics to advertise online.</li>
    <li>Third-party vendors, including Google, show Mourjan ads on sites across the Internet.</li>
    <li>Mourjan and third-party vendors, including Google, use first-party cookies (such as the Google Analytics cookie) and third-party cookies (such as the DoubleClick cookie) together to inform, optimize, and serve ads based on someone's past visits to Mourjan.</li>
</ul>
<h3>Security</h3>
<p>We use many tools to protect your personal information against unauthorized access and disclosure, but as you probably know, nothing's perfect, so we make no guarantees.</p>
<h3>General</h3>
<p>If we or our corporate affiliates are involved in a merger or acquisition, we may share personal information with another company, and this other company shall be entitled to share your personal information with other companies but at all times otherwise respecting your personal information in accordance with this policy.</p>
<p>Mourjan Policies and Terms & Conditions may be changed or updated occasionally to meet the requirements and standards. Therefore Users are encouraged to frequently visit these sections in order to be updated about the changes on the website. Modifications will be effective on the day they are posted.</p>
<p><b>Privacy Policy updated 20 Feb 2020</b></p><?php
        ?></div><?php
        $this->docFooter();
        ?></div></div><?php
    }
    
    
    public function renderFAQ() : void {
        ?><aside class=cw2><?=$this->side_pane()?></aside><?php
        
        ?><div class=cw10><div class="card doc en"><div class=view><?php
        ?><h2 class=title>FAQ / Help Center</h2><?php
        ?></div><?php
        $this->docFooter();
        ?></div></div><?php
    }
}
?>
