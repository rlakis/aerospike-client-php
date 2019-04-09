<?php
\Config::instance()->incLayoutFile('Page');

class MyAds extends Page {
    
    var $subSection='',$userBalance=0, $redis = null, $admins_online=[];
    
    var $editors = [
        1 => 'Bassel', 43905 => 'Bassel',
        2 => 'Robert', 69905 => 'Robert',
        2100 => 'Nooralex',
        477618 => 'Samir',
        38813 => 'Editor 1',
        44835 => 'Editor 2',
        53456 => 'Editor 3',
        166772 => 'Editor 4',
        516064 => 'Editor 5',
        897143 => 'Editor 6',
        897182 => 'Editor 7',
        1028732 => 'Editor 8'
    ];

    
    function __construct() {
        parent::__construct();       
        
        if ($this->router()->config()->isMaintenanceMode()) {
            $this->user->redirectTo($this->router()->getLanguagePath('/maintenance/'));
        }

        $this->checkBlockedAccount();
        $this->forceNoIndex=true;
        $this->router()->config()->disableAds();
        $this->title=$this->lang['myAds'];
        
        if (isset ($this->user->pending['post'])) {
            unset($this->user->pending['post']);
            $this->user->update();
        }
        
        $sub = filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                
        if ($sub=='deleted' && $this->user()->level()!=9) { $sub = ''; }

        $this->globalScript.='var SOUND="beep.mp3",';

        if (isset($this->user->params['mute'])&&$this->user->params['mute']) {
            $this->globalScript.='MUTE=1';
        }
        else {
            $this->globalScript.='MUTE=0';
        }
            
        $this->globalScript.=';';
            
        if ($this->user()->isLoggedIn(9)) {                                
                                         
            $this->globalScript.='
                    var setUT=function(e,id){
                        if(e.value>0){
                            $.ajax({
                                url:"/ajax-user-type/",
                                type:"GET",
                                data:{
                                    u:id,
                                    t:e.value
                                },
                                success:function(rp){
                                    if(!rp.RP){ 
                                        e.value=0;
                                    }
                                }
                            });
                        }
                    };
                ';
                
            if ($sub==='') {
                $this->globalScript.='
                        function mCPrem(){
                            Dialog.show("alert_dialog",\'<span class="fail"></span>'.$this->lang['multi_premium_no'].'\');
                        };
                    ';
            }
        }
            
        $this->set_ad(array('zone_0'=>array('/1006833/PublicRelation', 728, 90, 'div-gpt-ad-1319709425426-0-'.$this->router()->config()->serverId)));
                            
        
        if ($this->user()->isLoggedIn(9) && ($sub=='pending')) {
            $this->redis = $redis = new Redis();
            $redis->connect("p1.mourjan.com", 6379, 1, NULL, 100);
            $redis->select(5);
            
            if (!$this->user->isSuperUser()) {
                $redis->setex('ADMIN-'.$this->user->info['id'], 300, $this->user->info['id']);
            }
            $this->admins_online = $redis->keys('ADMIN-*');
            
            //error_log(json_encode($this->admins_online));
        }
        
        $this->render();
                
        if (isset($this->user->params['hold'])) {
            unset($this->user->params['hold']);
            $this->user->update();
        }
        
    }    

    
    function assignAdToAdmin($ad_id, $admin_id) {
        $admin_id = 0;
        if ($this->redis && count($this->admins_online)) {
            $redis = $this->redis;
            $ad = $redis->mGet(array('AD-'.$ad_id));
            if ($ad[0]===false) {
                
                $lastIndex = $redis->mGet(array('LAST_IDX'));
                if ($lastIndex[0]===false) {
                    $lastIndex = 0;
                }
                else {
                    $lastIndex = $lastIndex[0];
                }
                
                if ($lastIndex+1 < count($this->admins_online)) {
                    $lastIndex++;
                }
                else {
                    $lastIndex = 0;
                }
                
                $admin_id = $this->admins_online[$lastIndex];
                
                $redis->setex('AD-'.$ad_id, 120, $admin_id);
                $redis->setex('LAST_IDX', 86400, $lastIndex);                
            }
            else {
                $admin_id = $ad[0];
            }
            $admin_id = substr($admin_id,6)+0;
        }
        return $admin_id;
    }
    
    
    function getAssignedAdmin($ad_id){
        $admin = 0;
        if ($this->redis) {
            $redis = $this->redis;
            $ad = $redis->mGet(array('AD-'.$ad_id));
            if ($ad[0] !== false) {                
                $admin = substr($ad[0],6)+0;
            }
        }
        return $admin;
    }
    
    
    function _body() : void {
        parent::_body();        
        if ($this->redis) { $this->redis->close(); }
    }        
    
    
    function summerizeAds($count){
        $formatted=number_format($count);
        $bread= "";
        if ($this->router()->siteLanguage=="ar") {
            if ($count>10) {
                $bread.= $formatted." ".$this->lang['ads'];
            }
            elseif ($count>2) {
                $bread.= $formatted." ".$this->lang['3ad'];
            }
            else if ($count==1) {
                $bread.= $this->lang['ad'];
            }
            else {
                $bread.= $this->lang['2ad'];
            }
        }
        else {
            $bread.= $this->formatPlural($count, "ad");
        }
        $bread.=" ";
        if ($this->router()->siteLanguage=='ar') {
            $bread.='ضمن ';
        }
        else {
            $bread.='in ';
        }
        return $bread.' '.$this->title;
    }

    
    function side_pane() {
        $this->renderSideSite();
    }
    
    
    function main_pane() : void {
        if ($this->user()->isLoggedIn()) {
            if (!$this->router()->config()->get('enabled_post') && $this->topMenuIE) {
                $this->renderDisabledPage();
                return;
            }

            $sub= filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
            if ($sub=='deleted' && $this->user()->level()!=9) { $sub = ''; }
            switch($sub) {
                case '':
                    $this->pendingAds(7);
                    break;
                
                case 'pending':
                    $this->pendingAds(1);
                    if ($this->user()->level()===9) {
                        ?><script>var rtMsgs={
    'ar':[
        'إختر السبب...',
        'group=نص الاعلان',
        'يرجى تصحيح نص الاعلان والفصل بين العربية والانجليزية',
        'يرجى النشر في قسم العقارات الدولية',
        'يرجى النشر في قسم خدمات - استقدام العمالة',
        'يرجى النشر في قسم خدمات - خدمات عقارية',
        'نص الاعلان لا يمكن ان يحتوي على او ان يشير الى رابط لموقع الكتروني',
        'يرجى اعادة ادخال نص الاعلان',
        'يرجى تصحيح نص الاعلان',
        'نص الإعلان غير صالح للنشر',
        'نص الإعلان غير صالح للنشر في هذا القسم',
        'group=تفاصيل ناقصة',
        'يرجى تحديد المنطقة',        
        'يرجى تحديد المدينة أو المنطقة التي تتوفر فيها فرصة العمل ضمن نص الاعلان',        
        'group=وساءل التواصل',
        'يرجى اضافة رقم الهاتف للتواصل معك ضمن خانة معلومات التواصل',
        'يرجى تصحيح رقم الهاتف',
        'يرجى تصحيح رقم الهاتف أو تحديد رمز المنطقة إن لزم',
        'group=تكرار',
        'تكرار',
        'اعلان مكرر',
        'يرجى عدم التكرار لتجنب تعليق حسابك بشكل آلي',
        'لقد تم نشر الإعلان في القسم الأنسب ونرجو عدم التكرار',
        'لقد تخطيت الحد الاقصى لنشر اعلانات طلب العمل',
        'group=البلد',
        'يرجى النشر ضمن قسم العقارات الدولية',
        'لا يمكن نشر هذا الاعلان سوى في البلد الذي تتواجد فيه مكاتبكم وخدماتكم',
        'لا يمكن نشر هذا الإعلان سوى في البلد حيث يتواجد فيه العقار، السيارة أو السلعة',
        'group=سياسة الموقع',
        'لا يمكن نشر اعلانات مماثلة دون ادراج رقم الموبايل المستخدم لتفعيل حسابك مع مرجان (فقط) ضمن وسائل التواصل',
        'اعلانات زواج المسيار والمتعة مخالفة لسياسة الموقع ولا يمكن نشرها',
        'لا يمكن نشر إعلانات مماثلة طبقاً لسياسة الموقع',
        'اعلانات المتاجرة بالتأشيرات والإقامات مجرمة قانونيا',
        'مضمون هذا الاعلان يتعارض مع مضمون اعلاناتك المنشورة وهذا تحذير قبل ايقاف حسابك بسبب الاحتيال',
        'يحظر نشر هذا الاعلان ما دمت تتصل بالانترنت بواسطة بروكسي'
    ],
    'en':[
        'specify the reason...',
        'group=Ad Text',
        'please correct the ad text and seperate Arabic from English',
        'please edit and change section to International Real Estate',
        'please edit and change section to Services - Labor Recruitment',
        'please edit and change section to Services - Real estate services',
        'website links are not allowed within the ad text',
        'please re-enter the ad text',
        'please correct the ad text',
        'the ad text is not suitable for publishing',
        'the ad text is not suitable for publishing in this section',
        'group=Ad Details',
        'please specify the location',
        'please specify the city or the place within the ad text where the work opportunity is located',
        'group=Contact Info',
        'please specify a phone number within the contact info',
        'please correct the phone number',
        'please correct the phone number or specify the area code if applicable',
        'group=Repetition',
        'repetition',
        'repeated ad',
        'please do not repeat ads so that your account does not get suspended automatically',
        'this ad has already been published in a more suitable section',
        'you have reached the maximum number of ads that can be posted under LOOKING FOR A JOB',
        'group=Inapplicable Country',
        'please choose \"international real estate\" section to publish your ad',
        'this ad can only be published in countries where your offices and services are located',
        'this ad cannot be published in countries other than the country of origin (cars, real estate, goods)',
        'group=Website Policy',        
        'cannot publish similar ads unless you add ONLY the mobile number (used to activate your mourjan account) to the contact information',
        'Temporary marriages (Mesyar, Muta\') ads are against the website policy and caanot be published',
        'This type of ads is against the website policy and cannot be published',
        'Selling Visas and work permits is against the law',
        'This ad contradicts with the sense of your other published ads, and this is a warning before blocking your account for fraud',
        'The user behind proxy ad posting is prohibited'
    ]
};</script><?php
                    }
                    break;
                case 'drafts':
                    $this->pendingAds();
                    break;
                case 'archive':
                    $this->pendingAds(9);
                    break;
                case 'deleted':
                    $this->pendingAds(8);
                    break;
                default:
                    $this->pendingAds(7);
                break;
            }        
        }
        else {
            $this->renderLoginPage();
        }
    }    
    
    
    function renderEditorsBox($state=0, $standalone=false) {
        $isSuperUser = $this->user()->isSuperUser();
        if ($isSuperUser) {
            $filters = $this->user()->getAdminFilters();
        }
        
        if ($this->user()->isLoggedIn(9)) {
            ?><style><?php
            ?>.stin,.phc{display:none}.prx h4{margin-bottom:5px}.prx{display:block;clear:both;width:300px}.prx a{color:#00e}.prx a:hover{text-decoration:underline}<?php
            ?>.pfrx{height:auto}.prx select{width:260px;padding:3px 5px;margin:10px}.pfrx input{padding:5px 20px;margin:5px 0 10px}<?php            
            if (isset($filters['active']) && $filters['active']) {
                ?>.pfrx{background-color:#D9FAC8}<?php
            }
            ?></style><?php
        }
        
        if ($isSuperUser) {
            if ($standalone) { echo '<div class=fl>'; }
            $link='';
            switch ($state) {
                case 9:
                    $link='?sub=archive&a=';
                    break;
                case 7:
                    $link='?a=';
                    break;
                case 1:
                case 2:
                case 3:
                    $link='?sub=pending&a=';
                    break;
                case 0:
                default:
                    return;
            }
            
            if ($state==1) {
                $baseUrl = $this->router()->getLanguagePath('/myads/');
                echo '<div class=col-12>';
                echo '<form action="', $baseUrl, '" method=GET class=account><input type=hidden name=sub value=pending />';
                if ($filters['uid']) {
                    echo '<input type=hidden name=fuid value="', $filters['uid'], ' />', $this->router()->isArabic()?'مستخدم':'user', ': <b>', $filters['uid'], '</b>';                    
                }
                
                echo '<select name=fhl onchange="this.form.submit()">';
                echo '<option value=0', $filters['lang']==0?' selected':'', '>', $this->lang['lg_sorting_0'],'</option>';
                echo '<option value=1', $filters['lang']==1?' selected':'', '>العربي فقط</option>';
                echo '<option value=2', $filters['lang']==2?' selected':'', '>الانجليزي فقط</option>';
                echo '</select>';

                echo '<select name=fro onchange="this.form.submit()">';
                echo '<option value=0', $filters['root']==0 ? ' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                foreach ($this->router()->pageRoots as $id=>$root) {
                    echo '<option value=', $id, $filters['root']==$id ? ' selected':'', '>', $root['name'], '</option>';
                }
                echo '</select>';
                
                if ($filters['root']==3){
                    echo '<select name=fpu onchange="this.form.submit()">';
                    echo '<option value=0', $filters['purpose']==0?' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                    foreach ($this->router()->pageRoots[3]['purposes'] as $id=>$purpose) {
                        echo '<option value=', $id, $filters['purpose']==$id?' selected>':'>', $purpose['name'], '</option>';
                    }
                    echo '</select>';
                }
                
                if ($filters['active']) {
                    echo '<input type=reset onclick="location.href=\'', $baseUrl, '?sub=pending\'" value="', $this->lang['search_cancel'], '" />';
                }
                echo '</form></div>';
            }
        }

        if (($this->user()->isLoggedIn(9) && filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING)==='pending') || $isSuperUser) {
            if (!isset($filters) || !$filters['active']) {   
                echo '<div id=editors class=account style="padding-top:12px">';
                ?><span class="hvn50okt2 d2d9s5pl1g n2u2hbyqsn"><?= $isSuperUser ? '<a href="'. $link .'69905">Robert</a>' : 'Robert' ?></span><?php
                ?><span class="f3iw09ojp5 a1zvo4t2vk"><?= $isSuperUser ? '<a href="'. $link .'1">Bassel</a>' : 'Bassel' ?></span><?php
                ?><span class="a1zvo4t4b8"><?= $isSuperUser ? '<a href="'. $link .'2100">Nooralex</a>':'Nooralex' ?></span><?php
                ?><span class="n2u2hc8xil"><?= $isSuperUser ? '<a href="'. $link .'477618">Samir</a>':'Samir'?></span><?php
                ?><span class="x1arwhzqsl"><?= $isSuperUser ? '<a href="'. $link .'38813">Editor 1</a>':'Editor 1'?></span><?php
                ?><span class="d2d9s5p1p2"><?= $isSuperUser ? '<a href="'. $link .'44835">Editor 2</a>':'Editor 2'?></span><?php
                ?><span class="b2ixe8tahr"><?= $isSuperUser ? '<a href="'. $link .'53456">Editor 3</a>':'Editor 3'?></span><?php
                ?><span class="hvn50s5hk"><?= $isSuperUser ? '<a href="'. $link .'166772">Editor 4</a>':'Editor 4'?></span><?php
                ?><span class="j1nz09nf5t"><?= $isSuperUser ? '<a href="'. $link .'516064">Editor 5</a>':'Editor 5'?></span><?php
                ?><span class="x1arwii533"><?= $isSuperUser ? '<a href="'. $link .'897143">Editor 6</a>':'Editor 6'?></span><?php
                ?><span class="hvn517t2q"><?= $isSuperUser ? '<a href="'. $link .'897182">Editor 7</a>':'Editor 7'?></span><?php
                ?><span class="hvn51amkw"><?= $isSuperUser ? '<a href="'. $link .'1028732">Editor 8</a>':'Editor 8'?></span><?php
                echo '</div>';                               
            }
            
            if ($standalone) { echo '</div>'; }
        }
    }

    
    function getAdSection($ad, int $rootId=0, &$isMultiCountry=false) : string {
        $section='';
        switch($ad['PURPOSE_ID']) {
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex].' '.$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].' '.$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].'/', $this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex])){
                    $section=$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                else {
                    $in=' ';
                    if ($this->router()->language=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->router()->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].$in.$this->router()->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                break;
        }
           
       $adContent = json_decode($ad['CONTENT'], true);
       $countries = $this->router()->db->getCountriesDictionary(); // $this->router()->countries;
       if (isset($adContent['pubTo'])) {
            $fieldIndex=2;
            $comma=',';
            if ($this->router()->isArabic()){
                $fieldIndex=1;
                $comma='،';
            }
            $countriesArray=array();
            $cities = $this->router()->cities;
                
            $content='';
            foreach ($adContent['pubTo'] as $city => $value) {                    
                if (isset($cities[$city]) && isset($cities[$city][4])) {
                    $country_id=$cities[$city][4];
                        
                    if (!isset($countriesArray[$cities[$city][4]])) { 
                        $ccs = $countries[$country_id][6];
                        if ($ccs && count($ccs)>0) {
                            $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],array());
                        }
                        else {
                            $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],false);
                        }
                    }
                    if ($countriesArray[$country_id][1]!==false) $countriesArray[$country_id][1][]=$cities[$city][$fieldIndex];
                }
            }
   
            $i=0;
            foreach ($countriesArray as $key => $value) {
                if ($i) {
                    $content.=' - ';
                    $isMultiCountry = true;
                }
                $content.=$value[0];
                if ($value[1]!==false) $content.=' ('.implode ($comma, $value[1]).')';
                $i++;
            }
                
            if ($content) {
                $section=$section.' '.$this->lang['in'].' '.$content;
            }
        }
        elseif(isset ($countries[$ad['COUNTRY_ID']])) {
            $countryId=$ad['COUNTRY_ID']; 
            $countryCities=$countries[$countryId][6];
            if (count($countryCities)>0 && isset($this->router()->cities[$ad['CITY_ID']])) {
                $section=$section.' '.$this->lang['in'].' '.$this->router()->cities[$ad['CITY_ID']][$this->fieldNameIndex].' '.$countries[$countryId][$this->fieldNameIndex];
            }
            else {
                $section=$section.' '.$this->lang['in'].' '.$countries[$countryId][$this->fieldNameIndex];
            }
        }

        if ($section) {
            //if ($this->isMobile) {
            //    $section='<b class="ah">'.$section.'</b>';
            //}
            //else {
            $section='<span>'.$section.' - <b>'.$this->formatSinceDate(strtotime($ad['LAST_UPDATE'])).'</b></span>';
            //}
        }
        return $section;
    }
    
    
    private function accountButton($href, $text, $active, $count) : void {
        echo '<a href="', $href, '" class="btn', $active?' current">':'">', $text, $active?' ('.$count.')':'', '</a>';
    }
    
    
    function pendingAds($state=0) {
        $isAdmin = false;
        $isAdminOwner = false;
        $isSuperAdmin = $this->user->isSuperUser();
        $current_time = time();
        
        if ($this->user()->level()===9) {
            $isAdmin = true;
            $mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
        }
        
        $filters = $this->user()->getAdminFilters();
        $sub = filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);  

        $ads = $this->user()->getPendingAds(0, $state, true);
        $count= !empty($ads) ? count($ads) : 0; 
        $allCounts = $count>0 ? $this->user()->getPendingAdsCount($state) : 0;
        $this->user()->update();
        
        
        echo '<div class=row><div class=col-12><div class=card>';
        $this->renderBalanceBar();
        echo '<div class=account>';
        echo '<a href="', $this->router()->getLanguagePath('/post/'), '" class="btn half"><span class="j pub"></span>', $this->lang['button_ad_post_m'], '</a>';
        echo '<a id=active href="', $this->router()->getLanguagePath('/myads/'), '" class="btn active', $sub==''?' current':'', '"><span class="pj ads1"></span>', $this->lang['ads_active'], '</a>';

        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=pending', $this->lang['home_pending'], $sub=='pending', $allCounts);
        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=drafts', $this->lang['home_drafts'], $sub=='drafts', $allCounts);
        $this->accountButton($this->router()->getLanguagePath('/myads/').'?sub=archive', $this->lang['home_archive'], $sub=='archive', $allCounts);
            
        echo '<a id=favorite href="', $this->router()->getLanguagePath('/favorites/'), '?u=', $this->user->info['idKey'], '" class="btn half favorite', $sub=='favorite'?' current':'', '"><span class="j fva"></span>', $this->lang['myFavorites'], '</a>';
        echo '<a href="', $this->router()->getLanguagePath('/statement/'), '" class="btn half balance"><span class="pj coin"></span>', $this->lang['myBalance'], '</a>';
        echo '<a href="', $this->router()->getLanguagePath('/account/'), '" class="btn full settings"><span class="j sti"></span>', $this->lang['myAccount'], '</a>';
        echo '</div>';
                   
        echo '<div class=card-footer><div class=account>';
        $this->renderEditorsBox($state);
        echo '</div></div></div></div></div>',"\n";
        
        
        if ($count) {            
            $currentOffset = $this->get('o', 'uint');
            $hasNext=false;
            $recNum=25;
            
            $hasPrevious = (is_numeric($currentOffset) && $currentOffset);
            
            if ($count==26) {
                $hasNext=true;
                $count=25;
            }
            
            //$allCounts = $this->user()->getPendingAdsCount($state);            
            $renderAssignedAdsOnly = false;
        
            ?><p class="ph phb"><?php
            
            switch ($state) {
                case 8:
                    ?><?= $this->lang['ads_deleted'].($allCounts ? ' ('.$allCounts.')':'').' '.$this->renderUserTypeSelector() ?></p><div class="fl"><p class="phc"><?php
                    break;
                
                case 7:                    
                    $this->userBalance = $this->user->getStatement(0, 0, true);
                    if (isset($this->userBalance['balance'])) {
                        $this->userBalance = $this->userBalance['balance'];
                    }
                    ?><?= $this->lang['ads_active'].($allCounts ? ' ('.$allCounts.')':'').' '.$this->renderUserTypeSelector() ?></p><div class="fl"><p class="phc"><?= $this->lang['ads_active_desc'] ?><?php
                    break;
                    
                case 1:
                case 2:
                case 3:
                    $renderAssignedAdsOnly = true;
                    break;

                default:
                    break;
            }
            ?></p><?php
            $isAdminProfiling = (boolean)($this->get('a') && $this->user->info['level']==9);
            if ($isAdminProfiling) {
                $renderAssignedAdsOnly = false;
            }           
            
            if ($state==7) {
                if ($this->router()->config()->get('enabled_charts') && !$isAdminProfiling) {                    
                    ?><div class="stin <?= $this->router()->language ?>"></div><?php                    
                    $this->renderEditorsBox($state);
                    ?></div><?php
                    ?><div class="phld"><?php
                        ?><div id="statDv" class="load"></div><?php
                    ?></div><?php
                } 
                else {
                    $this->renderEditorsBox($state);
                    ?></div><?php
                }
            } 
            else {
                ?></div><?php
            }

            echo '<div class=row><div class="col-12 myadls">';            
            $idx=0;
            $linkLang = $this->router()->language=='ar' ? '':$this->router()->language.'/';
            
            $displayIdx = 0;
            for ($i=0; $i<$count; $i++) {
                $phoneValidErr=false;
                $link='';
                $altlink='';
                $ad=$ads[$i];
                $liClass='';
                $textClass='en';
                    
                if ($isAdmin) {
                    $isAdminOwner = ($ad['WEB_USER_ID']==$this->user()->id() ? true : false );
                }
                
                $assignedAdmin = '';
                if ($isAdmin && $renderAssignedAdsOnly && !$isAdminOwner) {
                    $assignedAdmin = $this->assignAdToAdmin($ad['ID'], $this->user()->id());
                    if (!$isSuperAdmin && $assignedAdmin && $assignedAdmin!=$this->user()->id()) {
                        continue;
                    }
                    if ($isSuperAdmin && $assignedAdmin) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin = '<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin = '';
                    }
                    $displayIdx++;
                }
                
                if ($ad['RTL']) { $textClass='ar'; }

                $content=\json_decode($ad['CONTENT'], true);  
                                        
                $isFeatured = isset($ad['FEATURED_DATE_ENDED']) && $ad['FEATURED_DATE_ENDED'] ? ($current_time < $ad['FEATURED_DATE_ENDED']) : false;
                $isFeatureBooked = isset($ad['BO_DATE_ENDED']) && $ad['BO_DATE_ENDED'] ? ($current_time < $ad['BO_DATE_ENDED']) : false;
                    
                if (!$isFeatureBooked && ($ad['STATE']==4 || (isset($content['budget']) && $content['budget']>0) )) {
                    $isFeatureBooked = true;
                }
                                                 
                if (!isset($content['ro'])) { $content['ro']=0; }
                if ($ad['SECTION_ID']>0) {
                    $content['ro']=$this->router()->sections[$ad['SECTION_ID']][4];
                    $content['se']=$ad['SECTION_ID'];
                }
                    

                $altText='';
                $text = (isset($content['text']) && \trim($content['text'])) ? $content['text'] : ($content['other']??'');                
                if (isset($content['extra']['t']) && $content['extra']['t']!=2 && isset($content['altother']) && $content['altother']) {
                    $altText=$content['altother'];
                }
                    
                $pic=false;
                $picCount='';
                
                $thumbs='';
                $hasAdminImgs = 0;
                
                if ($isAdmin) {
                    $images='';
                    if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                        foreach($content['pics'] as $img => $dim){
                            if ($images) { $images.="||"; }
                            $images.='<img width=\"118\" src=\"'.$this->router()->config()->adImgURL.'/repos/s/' . $img . '\" />';
                            $thumbs.='<span class=ig data-w='.$dim[0].' data-h='.$dim[1].'><img class=lazy data-path="'.$img.'" /></span>';
                            $hasAdminImgs = 1;
                        }
                    }
                    
                    if ($images) { $images.="||"; }
                    $images.='<img class=\"ir\" src=\"'.$this->router()->config()->imgURL.'/90/' . $ad['SECTION_ID'] . $this->router()->_png .'\" />';
                    $pic = '<img class=ir src="'.$this->router()->config()->imgURL.'/90/'.$ad['SECTION_ID'].$this->router()->_png.'" />';
                    
                    $this->globalScript.='sic[' . $ad['ID'] . ']="'.$images.'";';                    
                }
                else {
                    if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])>0) {
                        $picCount=count($content['pics']);
                        $pic = isset($content['pic_def']) ? $content['pic_def'] : array_keys($content['pics'])[0];
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img width=\"120\" src=\"'.$this->router()->cfg['url_ad_img'].'/repos/s/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'<span class=\"i sp\"></span></span>";';
                        $pic = '<span class=ig></span>';
                    } 
                    else {
                        $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\"ir\" src=\"'.$this->router()->cfg['url_img'].'/90/' . $ad['SECTION_ID'] .$this->router()->_png. '\" />";';
                        $pic = '<span class=ig><img class=ir src="'.$this->router()->config()->imgURL.'/90/'. $ad['SECTION_ID'] .$this->router()->_png .'"</span>';
                    }
                }
                
                if ($this->user()->isLoggedIn(9)) {
                    $onlySuper = (isset($ad['SUPER_ADMIN']) && ($ad['SUPER_ADMIN']) ? $ad['SUPER_ADMIN']+0 : 0);
                    if ($onlySuper) {
                        if ($onlySuper && $onlySuper < 1000) {                        
                            switch($onlySuper) {
                                case 998:
                                    $onlySuper = "email contains + sign";
                                    break;
                                case 997:
                                    $onlySuper = "email contains hotel word";
                                    break;
                                case 996:
                                    $onlySuper = "email contains more than one dot";
                                    break;
                                case 995:
                                    $onlySuper = "user verified number is from other country";
                                    break;
                                case 999:
                                    $onlySuper = "System General";
                                    break;
                                case 1:
                                    $onlySuper = "contains stop words";
                                    break;
                                default:
                                    $onlySuper = "not specified";
                                    break;
                            }

                        }
                        else {
                            if (isset($this->editors[$onlySuper])) {
                                $onlySuper = "requested by ".$this->editors[$onlySuper];
                            }
                            else {
                                $onlySuper = "requested by user #".$onlySuper."#";
                            }
                        }
                    }                    
                                                                
                    $mcUser = new MCUser((int)$ad['WEB_USER_ID']);
                    $userMobile = $mcUser->getMobile(TRUE)->getNumber();                    
                    $needNumberDisplayFix=(!\preg_match('/span class=?pn/u', $text));
                    
                    if (isset($content['cui']['p']) && \is_array($content['cui']['p'])) {
                        foreach ($content['cui']['p'] as $p) { 
                            $isUserMobile = false;
                            try {
                                $num = $mobileValidator->parse($p['v'],$p['i']);
                                if ($num && $mobileValidator->isValidNumber($num)) {
                                    if ($userMobile && '+'.$userMobile == $p['v']) {
                                        $isUserMobile=true;
                                    }
                                
                                    $type=$mobileValidator->getNumberType($num);  
                                    $phoneValidErr=0;
                                    switch((int)$p['t']){
                                        case 1:
                                        case 2:
                                        case 3:
                                        case 4:
                                        case 5:
                                        case 13:
                                            if ($type!==1 && $type!==2) {
                                                $phoneValidErr=1;
                                            }
                                            break;
                                        case 7:
                                        case 8:
                                        case 9:
                                            if($type!==0 && $type!==2) {
                                                $phoneValidErr=1;
                                            }
                                            break;
                                        default:
                                            $phoneValidErr=2;
                                            break;
                                    }
                                }
                                else {
                                    $phoneValidErr=2;
                                }
                            }
                            catch (Exception $ex) {
                                $phoneValidErr=2;
                            }
                            
                            if ($needNumberDisplayFix) {
                                if (\strlen($p['v'])===0) { $p['v'] = $p['r']; }
                                if (\strlen($p['v'])>0) {
                                    $text = \preg_replace('/\\'.$p['v'].'/', '<span class="pn">'.$p['v'].'</span>', $text);
                                    if ($altText){
                                        $altText = preg_replace('/\\'.$p['v'].'/', '<span class="pn">'.$p['v'].'</span>', $altText);
                                    }
                                }
                            }
                            
                            if ($isUserMobile) {
                                $text = preg_replace('/\<span class="pn">\\'.$p['v'].'\<\/span\>/', '<span class="pn png">'.$p['v'].'</span>', $text);
                                if($altText){
                                    $altText = preg_replace('/\<span class="pn">\\'.$p['v'].'\<\/span\>/', '<span class="pn png">'.$p['v'].'</span>', $altText);
                                }
                            }
                            
                            if ($phoneValidErr) {
                                $text = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">\\'.$p['v'].'\<\/span\>/', '<span class="vn">'.$p['v'].'</span>', $text);
                                if ($altText) {
                                    $altText = preg_replace('/\<span class="pn(?:[\sa-z0-9]*)">\\'.$p['v'].'\<\/span\>/', '<span class="vn">'.$p['v'].'</span>', $altText);
                                }
                            }
                        }
                    }
                        
                    $name=$ad['WEB_USER_ID'].'#'.($ad['FULL_NAME']?$ad['FULL_NAME']:$ad['DISPLAY_NAME']);
                    $style='';
                    if ($ad['LVL']==4) $style=' style="color:orange"';
                    elseif ($ad['LVL']==5) $style=' style="color:red"';
                    
                    $profileLabel =  isset($ad['PROVIDER']) ? $ad['PROVIDER']:'profile';
                    if ($userMobile) {
                        $unum = $mobileValidator->parse('+'.$userMobile,'LB');
                        $XX = $mobileValidator->getRegionCodeForNumber($unum);
                        $profileLabel = '+'.$userMobile;
                        if ($XX) { $profileLabel = '('.$XX. ')' . $profileLabel; }
                    }
                    
                    $title='<div class=user><a target=_similar href="'.
                            ($isSuperAdmin ? $this->router()->getLanguagePath('/admin/').'?p='.$ad['WEB_USER_ID'] : $ad['PROFILE_URL']).
                            '">'.$profileLabel.'</a><a target=_similar'.$style.' href="'.
                            $this->router()->getLanguagePath('/myads/').'?u='.$ad['WEB_USER_ID'].'">'.$name.'</a>';
                    if (isset($content['userLOC'])) {
                        $geo = preg_replace('/[0-9\.]|(?:^|\s|,)[a-zA-Z]{1,3}\s/','',$content['userLOC']);
                        $geo = preg_replace('/,/', '' , $geo);
                        $title.='<span class=inf>'.$geo.'</span>';
                    }
                    else {
                        $title.='<span class="inf err">No Geo</span>';
                    }
                    
                    if ($state==1 && $ad['DATE_ADDED']==$ad['LAST_UPDATE']) {
                        $title.='<span class=inf><span class="rj ren"></span></span>';
                    }
                    
                    $title.=($phoneValidErr!==false ? ($phoneValidErr==0 ? '<span class=inf>T<i class="icn m icn-done"></i></span>' : '<span class=inf>T<i class="icn m icn-fail"></i></span>'):'' );
                    $class= '';
                    
                    if($isFeatured) {
                        $class = ' style="color:green"';
                    }
                    else if($isFeatureBooked) {
                        $class = ' style="color:blue"';
                    }
                    
                    $ss = 'W';
                    if (isset($content['app'])) {
                        if ($content['app']=='ios') {
                            $ss = 'I';
                        }
                        else if ($content['app']=='android') {
                            $ss = 'A';
                        }
                    }
                    
                    if (isset($content['ipfs']) && $content['ipfs']>=50) {
                        $ss.="/{$content['ipfs']}";
                    }
                   
                    $title.='<b'.$class.'>#'.$ad['ID'].'#' . $ss. '</b>';
                    $title.='</div>';
                
                } // here
                    

                if ($state==7) {
                    $liClass.='atv';
                    $link=($ad['RTL']?'/':'/en/').$ad['ID'].'/';
                    if($altText) $altlink='/en/'.$ad['ID'].'/';                        
                        
                    if ($isFeatured || $isFeatureBooked) {
                        $liClass.= ' vp';
                    }
                }
                
                if($state>6) {
                    $ad['CITY_ID']=$ad['ACTIVE_CITY_ID'];
                    $ad['COUNTRY_ID']=$ad['ACTIVE_COUNTRY_ID'];
                }
                                
                $adClass='card myad';
                
                if ($isFeatured||$isFeatureBooked) {
                    $adClass.=' feature';
                }
                
                if ($ad['STATE']==1||$ad['STATE']==4) {
                    $adClass.=' pending';
                }
                elseif ($ad['STATE']==2) {
                    $adClass.=' approved';
                }
                if ($onlySuper) {
                    $adClass.=' alert';
                }
                
                // new look
                echo "\n",'<article id=', $ad['ID'], ' class="', $adClass, '" data-status=', $ad['STATE'], ' data-fetched=0';
                
                if ($this->user()->level()==9) { echo ' data-ro=', $this->router()->getRootId($ad['SECTION_ID']), ' data-se=', $ad['SECTION_ID'], ' data-pu='.$ad['PURPOSE_ID']; }
                if (isset($content['hl']) && in_array($content['hl'], ['en','ar'])) { echo ' data-hl="',$content['hl'], '"'; }
                echo '>';
                echo '<header>';//, $ad['STATE']==2?' class=approved>':'>';
                switch ($ad['STATE']) {
                    case 1:
                    case 4:
                        echo '<div><div class=tooltip><i class="icn m icon-state"></i>';
                        if ($onlySuper) {
                            echo '<span class=tooltiptext onmouseover="d.ipCheck(this)">', $onlySuper, '</span>';
                        }
                        elseif ($this->user()->level()===9) {
                            echo '<span class=tooltiptext onmouseover="d.ipCheck(this)">...</span>';
                        }
                        echo '</div>';
                        echo '<span class=msg>', $this->lang['pendingMsg'],'</span></div>';
                        echo '<span class=alloc>', ($assignedAdmin?$assignedAdmin:''), '</span>';
                        break;

                    case 2:
                        echo '<div><i class="icn m icon-state"></i><span class=msg>', $this->lang['approvedMsg'], '</span></div>';
                        if ($assignedAdmin) { echo '<span class=alloc>', $assignedAdmin, '</span>'; }
                        break;
                    
                    case 3:
                        echo '<div class="nb nbr"><span class="fail"></span>',$this->lang['rejectedMsg'],(isset($content['msg']) && $content['msg']? ': '.$content['msg']:''),($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                        break;
                    
                    default:
                        break;
                }
                
                echo '</header>';
                
                if ($this->user()->level()===9) { echo $title; }
                
                $userLang = '';
                if (isset($content['hl']) && in_array($content['hl'], ['en','ar'])) {
                    $userLang = $content['hl'];
                }
                
                if ($hasAdminImgs) { echo '<p class=pimgs>', $thumbs, '</p>'; }
                
                echo '<section class="card-content ', $ad['RTL']?'ar"':'en"';
                echo $link?' onclick="wo('.$link.')"' : '';
                if ($isAdmin) { echo ' onmouseup="d.textSelected(this);"'; }
                if ($isAdmin) { echo ' oncontextmenu="d.lookup(this);return false;"'; }
                echo '>', ($pic ? $pic :'').$text;
                echo '</section>';
                
                if ($altText) {
                    echo '<hr /><section class="card-content en" data-foreign=1';
                    if ($altlink) {
                        echo ' onclick="wo(', $altlink, ')"';
                    }
                    elseif ($isAdmin) {
                        echo ' onselect="MSAD(this)" ';
                    }
                    echo '>',  ($pic ? $pic :''), $altText;
                    echo '</section>';
                }

                if (isset($content['extra']['m']) && $content['extra']['m']!=2 && ($content['lat']||$content['lon']) && isset($content['loc'])) {
                    echo '<hr>';
                    ?><div class='oc ocl'><span class="i loc"></span><?= $content['loc'] ?></div><?php
                }
                
                echo '<div class=note';
                if (!$isAdminProfiling && $this->user()->level()==9 && in_array($state,[1,2,3])) { echo ' onclick="d.quick(this)"'; }
                $isMultiCountry = false;
                echo '>', $this->getAdSection($ad, $content['ro'], $isMultiCountry), '</div>';
                //if ($state>6) {
                //    echo '<a class=com href="'.$link.'#disqus_thread" data-disqus-identifier="'.$ad['ID'].'" rel="nofollow"></a>';
                //}

                $isSuspended = $this->user->getProfile() ? $this->user->getProfile()->isSuspended() : FALSE;
                if (!$this->user->getProfile()) {
                    error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                }
                
                $isSystemAd = (isset($ad['DOC_ID']) && $ad['DOC_ID']) ? true : false;

                echo '<footer>';                
                if ($state<7) {                                        
                    if (!$isSystemAd) {
                        if(!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method=post><?php
                            ?><input type=hidden name=ad value="<?= $ad['ID'] ?>" /><?php
                            ?><button onclick="d.edit(this)"><span class="rj edi"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></button><?php
                            ?></form><?php
                        }
                    
                        if (!$isAdmin || ($isAdmin && $isAdminOwner)) {
                            ?><a onclick="adel(this)" href='javascript:void(0)'><span class="rj del"></span><?= $this->lang['delete'] ?></a><?php
                        }
                    }
                }
                elseif ($state==7) {
                    $ad_hold=0;
                    if (isset($this->user->params['hold']) && $this->user->params['hold']==$ad['ID']) {
                        if ($this->user->holdAd($ad['ID'])) {
                            $ad_hold=1;
                            ?><b class=anb><span class="done"></span><?= $this->lang['retired'] ?></b><?php
                        }
                    }
                    
                    if (!$ad_hold) {
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && (!$isFeatured || !$isFeatureBooked)) {
                            ?><span class="lnk" onclick="<?= $isMultiCountry ? 'mCPrem()' : ($this->userBalance ? 'askPremium(this)':'noPremium()') ?>"><span class=mc24></span><?= $this->lang['make_premium'] ?></span><?php                                    
                        }
                        if ((!$isAdmin || ($isAdmin && $isAdminOwner)) && $isFeatureBooked) {
                            ?><span class="lnk" onclick="cancelPremium(this)"><span class="mc24"></span><?= $this->lang['stop_premium_bt'] ?></span><?php                                    
                        }                        
                        if (!$isSystemAd && (!$isAdmin || ($isAdmin && !$isFeatured && !$isFeatureBooked) || ($isAdmin && $isAdminOwner))) {
                            ?><span class="lnk" onclick="ahld(this)"><span class="rj hod"></span><?= $this->lang['hold'] ?></span><?php                                    
                        }
                        if (!$isSystemAd) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_ad'] ?></span><?php
                            ?></form><?php 
                        }
                        if ($this->router()->config()->get('enabled_ad_stats') && !$isAdminProfiling) {
                            ?><span class="stad load"></span><?php
                        }
                    }                            
                }
                elseif ($state==9) {
                    if (!$isSystemAd) {
                        if (!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $ad['ID'] ?>" /><?php
                            ?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_republish'] ?></span><?php
                            ?></form><?php 
                            if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                ?><span class="lnk" onclick="are(this)"><span class="rj ren"></span><?= $this->lang['renew'] ?></span><?php
                            }
                        }
                    }
                    if (!$isSystemAd && (!$isAdmin || ($isAdmin && $isAdminOwner))) {
                        ?><span class="lnk" onclick="adel(this,1)"><span class="rj del"></span><?= $this->lang['delete'] ?></span><?php 
                    }
                    if ($this->router()->config()->get('enabled_ad_stats') && !$isAdminProfiling) {
                        ?><span class="stad load"></span><?php
                    }
                }
                
               
                if ($this->user()->level()===9) {
                    if ($ad['STATE']==2 && (!$isSystemAd || $isSuperAdmin)) {
                        ?><input type="button" class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)" value="<?= $this->lang['reject'] ?>" /><?php
                        
                        if ($isSuperAdmin) {
                            ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                        }
                        $contactInfo=$this->getContactInfo($content);
                        if (!$isSystemAd || $isSuperAdmin) {
                            if ($contactInfo) {                        
                                ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router()->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                            }
                        }
                    }
                    else {
                        if ($state>0 && $state<7) {
                            if (!$isSystemAd || $isSuperAdmin) {         
                                ?><button onclick="d.approve(this)"><?= $this->lang['approve'] ?></button><?php
                                if ($isSuperAdmin) {
                                    ?><button onclick="d.rtp(this)">RTP</button><?php                                    
                                }
                                ?><button onclick="d.reject(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['reject'] ?></button><?php 
                            }
                            if (!$isSuperAdmin && !$onlySuper && !$isSystemAd) {
                                ?><span class="lnk" onclick="help(this)"><?= $this->lang['ask_help'] ?></span><?php
                            }                            
                            if ($isSuperAdmin && $ad['USER_RANK'] < 2) {
                                ?><button onclick="d.ban(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['block'] ?></button><?php 
                            }
                            if (!$isSystemAd && $ad['USER_RANK']<3) {
                                ?><button onclick="d.suspend(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['suspend'] ?></button><?php
                            }
                            if ($isSuperAdmin && $filters['uid']==0) {
                                ?><button onclick="d.userads(this,<?= $ad['WEB_USER_ID'] ?>)"><?= $this->lang['user_type_option_1'] ?></button><?php
                            }
                            
                            $contactInfo=$this->getContactInfo($content);                          
                            if ($isSuperAdmin) {
                                ?><button onclick=d.similar(this)><?= $this->lang['similar'] ?></button><?php
                            }
                            if ((!$isSystemAd || $isSuperAdmin) && $contactInfo) {
                                ?><button id=revise data-contact="<?= $contactInfo ?>" onclick=d.lookFor(this)><?= $this->lang['lookup'] ?></button><?php
                            }                            
                        }
                    }
                }     
                               
                echo '</footer>';
                echo '</article>';                           
                $idx++;
            }
            echo "\n";
            
            if ($state==7) {
                if($this->userBalance){
                    ?><div id="make_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['balance'].': '.$this->userBalance ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-hint"><?= $this->lang['premium_hint'] ?></div><?php 
                            ?><div class="dialog-box"><?php 
                                ?><ul><?php
                                ?><li><?= $this->lang['premium_days'] ?>:</li><?php
                                ?><li><select id="spinner" max="<?= $this->userBalance ?>"></select></li><?php
                                ?></ul><?php
                            ?></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['make'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="confirm_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['please_confirm'] ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-box"></div><?php 
                            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['deal'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="stop_premium" class="dialog premium"><?php
                        ?><div class="dialog-box"><?= $this->lang['stop_premium'] ?></div><?php 
                        ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                }else{                
                    ?><div id="what_premium" class="dialog premium"><?php
                            ?><div class="dialog-title"><?= $this->lang['make_premium'] ?> <span class='mc24'></span></div><?php
                            ?><div class="dialog-box"><?= $this->lang['no_balance_dialog'] ?></div><?php 
                            ?><div class="dialog-action"><input type="button" value="<?= $this->lang['back'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="stop_premium" class="dialog premium"><?php
                        ?><div class="dialog-box"><?= $this->lang['stop_premium'] ?></div><?php 
                        ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                    ?></div><?php
                    ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                }
                ?><div id="stop_ad" class="dialog"><?php
                    ?><div class="dialog-box"><?= $this->lang['stop_ad'] ?></div><?php 
                    ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['stop'] ?>" /></div><?php 
                ?></div><?php
            }
            
            ?><div id=delete_ad class=dialog style="display:none"><?php
                ?><div class=dialog-box><?= $this->lang['delete_ad'] ?></div><?php 
                ?><div class=dialog-action><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= ucfirst($this->lang['delete']) ?>" /></div><?php 
            ?></div><?php
                
            if ($isSuperAdmin) { 
                ?><div id=rtp_dialog class=dialog style="display:none"><?php
                    ?><div class="dialog-box ctr"><input type="button" class="approve bt" value="<?= ucfirst($this->lang['approve']) ?>" /></div><?php 
                    ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= ucfirst($this->lang['reject']) ?>" /></div><?php 
                ?></div><?php
            }    
            
            if($hasNext || $hasPrevious){
                ?><div class="pgn"><div class="card"><?php 
                echo ($currentOffset+1).' '.$this->lang['of'].' '.ceil($allCounts/$recNum);
                $appendOp = '?';
                $link = $this->router()->uri.($this->router()->isArabic()?'':$this->router()->language.'/');
                
                
                $sub=filter_input(INPUT_GET, 'sub', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                switch ($sub) {
                    case 'pending':
                        $link.='?sub=pending';
                        $appendOp='&';
                        break;
                    
                    case 'drafts':
                        $link.='?sub=drafts';
                        $appendOp='&';
                        break;
                    
                    case 'archive':
                        $link.='?sub=archive';
                        $appendOp='&';
                        break;
                    
                    case '':
                    default:
                        break;
                }
                
                if(isset($_GET['u']) && $_GET['u']){
                    $link.=$appendOp.'u='.$_GET['u'];
                    $appendOp='&';
                }
                
                if(isset($_GET['a']) && $_GET['a']){
                    $link.=$appendOp.'a='.$_GET['a'];
                    $appendOp='&';
                }
                
                if($filters['uid']){
                    $link.=$appendOp.'fuid='.$filters['uid'];
                    $appendOp='&';
                }                
                if($filters['root']){
                    $link.=$appendOp.'fro='.$filters['root'];
                    $appendOp='&';
                }                
                if($filters['purpose']){
                    $link.=$appendOp.'fpu='.$filters['purpose'];
                    $appendOp='&';
                }               
                if($filters['lang']){
                    $link.=$appendOp.'fhl='.$filters['lang'];
                    $appendOp='&';
                }
                
                if($hasPrevious){
                    $offset = $currentOffset - 1;
                    ?><a class=float-left href='<?= $link.($offset ? $appendOp.'o='.$offset : '') ?>'><?= $this->lang['prev_25'] ?></a><?php
                }
                if($hasNext){
                    ?><a class=float-right href='<?= $link.$appendOp.'o='.($currentOffset + 1)  ?>'><?= $this->lang['next_25'] ?></a><?php
                }
                ?></div></div><?php
            }
            
            if ($this->user()->level()==9 && $state<7) {
                ?><div id=rejForm class=inline><select id=rejS></select><?php
                echo '<textarea id=rejT onkeydown="dirElem(this)"></textarea>';
                echo '<input type=button class="btn ok" value="', $this->lang['reject'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" />';
                ?></div><?php
                                
                ?><div id=suspForm class=inline><select id=suspS></select><?php
                echo '<textarea id=suspT onkeydown="dirElem(this)" placeholder="', $this->lang['reason_suspension'], '"></textarea>';
                echo '<input type=button class="btn ok" value="', $this->lang['suspend'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" />';
                ?></div><?php
                
                ?><div id=banForm class=inline><?php
                echo '<textarea id=banT onkeydown="dirElem(this)"></textarea>';
                echo '<input type=button class="btn ok" value="', $this->lang['block'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" />';
                ?></div><?php
                echo "\n";
                ?><div id=fixForm class=inline><?php
                echo '<div class=col-12><input id=fixT type=text onkeydown="dirElem(this)" style="width:100%" /></div>';
                echo '<div id=qRoot class="float-left col-2"><ul></ul></div>';                
                echo '<div id=qSec class="float-left col-8 sections"><ul></ul></div>';
                echo '<div id=qAlt class="float-left col-2"><ul></ul></div>';
                ?></div><?php
            }
        } // end ad count>0
        else {
            ?><p class="ph phb db"><?php
            $msg='';
            $mcUser = null;
            switch ($state){
                case 9:
                    $msg=  $this->lang['no_archive'];
                    $this->user->info['archive_ads']=$count;
                    echo $this->lang['ads_archive'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 7:
                    $msg=  $this->lang['no_active'];
                    $this->user->info['active_ads']=$count;
                    echo $this->lang['ads_active'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 1:
                case 2:
                case 3:
                    $msg=  $this->lang['no_pending'];
                    $this->user->info['pending_ads']=$count;
                    echo $this->lang['ads_pending'].($count ? ' ('.$count.')':'');
                    break;
                case 0:
                default:
                    $msg=  $this->lang['no_drafts'];
                    $this->user->info['draft_ads']=$count;
                    echo $this->lang['ads_drafts'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
            }
            ?></p><?php            
            $this->renderEditorsBox($state, true);
            
            if($this->user->info['level']==9 && $mcUser && $mcUser->isBlocked()) {
                $msg = 'User is Blocked';
                $reason = preg_replace(['/\</','/\>/'],['&#60;','&#62;'], Core\Model\NoSQL::getInstance()->getBlackListedReason($mcUser->getMobileNumber()));
                if ($reason) {
                    $msg = $msg.'<br />'.$reason;
                }
            }
            
            ?><div class="htf db"><?= $msg ?><br /><br /><?php
            ?><input onclick="document.location='<?= $this->router()->getLanguagePath('/post/') ?>'" class=bt type=button value="<?= $this->lang['create_ad'] ?>" /><?php
            ?></div><?php
        }
        $this->inlineJS('util.js')->inlineJS('myads.js');
    }
    
    
    function getContactInfo($content) {
        $contactInfo='';
        if (isset($content['cui'])) {
            if (isset($content['cui']['p'])) { 
                $phone=$content['cui']['p'];
                if (count($phone)) {
                    foreach ($phone as $p) {
                        if($contactInfo) $contactInfo.='|';
                        $contactInfo.=urlencode('"'.substr($p['v'],1).'"');
                    }
                }
            }

            if(isset($content['cui']['e']) && $content['cui']['e']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['e'].'"');
            }
            if(isset($content['cui']['b']) && $content['cui']['b']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['b'].'"');
            }
            if(isset($content['cui']['t']) && $content['cui']['t']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['t'].'"');
            }
            if(isset($content['cui']['s']) && $content['cui']['s']){
                if($contactInfo) $contactInfo.='|';
                $contactInfo.=urlencode('"'.$content['cui']['s'].'"');
            }
        }
        return $contactInfo;
    }

    
    function renderUserTypeSelector(&$user=null) {
        if ($this->user->info['id'] && $this->user->info['level']==9) {
            $userId = $this->user->info['id'];
            $type = 0;
            if (isset($_GET['u']) && is_numeric($_GET['u']) && $_GET['u']) {
                $userId = $_GET['u'];
                $type = \Core\Model\NoSQL::getInstance()->getUserPublisherStatus($userId); 
            }
            $user = new MCUser($userId);
            if ($user->isSuspended()) {
                $time = MCSessionHandler::checkSuspendedMobile($user->getMobileNumber());
                $hours=0;
                $lang=$this->router()->siteLanguage;
                if ($time) {
                    $hours = $time / 3600;
                    if (ceil($hours)>1) {
                        $hours = ceil($hours);
                        if ($lang=='ar') {
                            if ($hours==2) {
                                $hours='ساعتين';
                            }
                            elseif ($hours>2 && $hours<11) {
                                $hours=$hours.' ساعات';
                            }
                            else {
                                $hours = $hours.' ساعة';
                            }
                        }
                        else {
                            $hours = $hours.' hours';
                        }
                    }
                    else {
                        $hours = ceil($time / 60);
                        if ($lang=='ar') {
                            if ($hours==1) {
                                $hours='دقيقة';
                            }
                            elseif ($hours==2) {
                                $hours='دقيقتين';
                            }
                            elseif ($hours>2 && $hours<11) {
                                $hours=$hours.' دقائق';
                            } 
                            else {
                                $hours = $hours.' دقيقة';
                            }
                        }
                        else {
                            if ($hours>1) {  
                                $hours = $hours.' minutes';
                            }
                            else {                        
                                $hours = $hours.' minute';
                            }
                        }
                    }
                }
                echo '<span class="fl"><span class="wait"></span>'.$hours.'</span>';
            }
            echo '<span class="fl">'.$this->lang['user_type_label'].': <select onchange="setUT(this,'.$userId.')"><option value="0">'.$this->lang['user_type_option_0'].'</option><option value="1"'.($type == 1 ? ' selected':'').'>'.$this->lang['user_type_option_1'].'</option><option value="2"'.($type == 2 ? ' selected':'').'>'.$this->lang['user_type_option_2'].'</option></select></span>';            
        }
    }
    
    
    function drawSlideShow() : void {
        ?><div class="slideshow-container"></div>                                
        <?php        
    }
    
}
            

?>