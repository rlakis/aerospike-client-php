<?php
\Config::instance()->incLayoutFile('UserPage')->incModelFile('AdList')->incLibFile('MCPermission');

use Core\Model\Ad;
use Core\Model\AdList;
use Core\Lib\MCUser;
use Core\Lib\MCPermission;

/* TODO *
 * 
 * delete picture index mismatch on click 
 */

class MyAds extends UserPage {
    
    private AdList $adList;
    private array $admins_online=[];
    private ?\Redis $redis=null;
    private int $showApproved=0;
        
    private array $editors = [
        1 => 'Bassel', 43905 => 'Bassel',
        2 => 'Robert', 69905 => 'Robert',
        2100 => 'Nooralex', 123391 => 'Moe',
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

    
    private array $adminReasons = [
        1 => 'contains stop words',
        994 => 'fraudulant with hidden activated mobile number',
        995 => 'user verified number is from other country',        
        996 => 'email contains more than one dot',        
        997 => 'email contains hotel word',        
        998 => 'email contains + sign',        
        999 => 'system general'        
    ];
    
            
    function __construct() {
        parent::__construct();
        $sub=$this->getGetString('sub');
        
        if ($this->user->isLoggedIn(9)) {
            MCPermission::instance()->setUser($this->user->getProfile());
            if ($sub==='pending') {
                $this->redis=$redis=new Redis;
                $redis->connect("p1.mourjan.com", 6379, 1, NULL, 100);
                $redis->select(5);
            
                if (!MCPermission::instance()->isSuperAdmin()) {
                    $redis->setex('ADMIN-'.$this->user()->id(), 300, $this->user->id());
                }
                $this->admins_online=$redis->keys('ADMIN-*');                
            }
        }
        
        $this->showApproved=\filter_input(\INPUT_GET, 'approved', \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]);
        
        $this->adList=new AdList();
        $this->adList->cacheProfile($this->user()->data);
        
        $this->forceNoIndex=true;
        $this->router->config->disableAds();
        $this->title=$this->lang['myAds'];
        
        if (isset($this->user->pending['post'])) {
            unset($this->user->pending['post']);
            $this->user->update();
        }
        
        if ($sub==='deleted' && $this->user->level()!==9) { $sub = ''; }
                                    
        $this->set_ad(['zone_0'=>['/1006833/PublicRelation', 728, 90, 'div-gpt-ad-1319709425426-0-'.$this->router->config->serverId]]);
          
        if ($this->user->level()===9 && $sub==='pending') {
        
            //if ($userLevel===9 && $sub==='pending') {
            $this->redis=$redis=new Redis();
            $redis->connect("p1.mourjan.com", 6379, 1, NULL, 100);
            $redis->select(5);
            
            if (!$this->user->isSuperUser()) {
                $redis->setex('ADMIN-'.$this->user()->id(), 300, $this->user()->id());
            }
            $this->admins_online=$redis->keys('ADMIN-*');            
        }
        
        
        $this->render();
                
        if (isset($this->user->params['hold'])) {
            unset($this->user->params['hold']);
            $this->user->update();
        }
        
    }    

    
    function assignAdToAdmin(int $ad_id, int $admin_id) : int {
        $admin_id=0;
        if ($this->redis && !empty($this->admins_online)) {
            $redis=$this->redis;
            $ad=$redis->mGet(array('AD-'.$ad_id));
            if ($ad[0]===false) {
                
                $lastIndex=$redis->mGet(array('LAST_IDX'));
                if ($lastIndex[0]===false) {
                    $lastIndex=0;
                }
                else {
                    $lastIndex=$lastIndex[0];
                }
                
                if ($lastIndex+1<\count($this->admins_online)) {
                    $lastIndex++;
                }
                else {
                    $lastIndex=0;
                }
                
                $admin_id=$this->admins_online[$lastIndex];
                
                $redis->setex('AD-'.$ad_id, 120, $admin_id);
                $redis->setex('LAST_IDX', 86400, $lastIndex);                
            }
            else {
                $admin_id=$ad[0];
            }
            $admin_id=substr($admin_id,6)+0;
        }
        return $admin_id;
    }
    
    
    function getAssignedAdmin(int $ad_id) : int {
        $admin=0;
        if ($this->redis) {
            $redis=$this->redis;
            $ad=$redis->mGet(array('AD-'.$ad_id));
            if ($ad[0]!==false) {                
                $admin=\intval(\substr($ad[0],6));
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
        if ($this->router->siteLanguage=="ar") {
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
        if ($this->router->siteLanguage=='ar') {
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
        if (!$this->user()->isLoggedIn()) {
            $this->renderLoginPage();
            return;            
        }
        
        if (!$this->router->config->get('enabled_post') && $this->topMenuIE) {
            $this->renderDisabledPage();
            return;
        }

        $sub=$this->getGetString('sub');
        if ($sub==='deleted' && $this->user()->level()!==9) { $sub = ''; }
            
        switch($sub) {
            case '':
                $this->pendingAds(7); // active ads
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
        'يرجى تحديد المنطقة ضمن نص الاعلان',
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
        ' لا يمكن نشر هذا الاعلان سوى في صفحة المدينة الذي يتواجد فيه العقار',
        'يرجى النشر ضمن قسم العقارات الدولية',
        'لا يمكن نشر هذا الاعلان سوى في البلد الذي تتواجد فيه مكاتبكم وخدماتكم',
        'لا يمكن نشر هذا الاعلان في دولة وانت متواجد في دولة أخرى او قد يتم ايقاف حسابك',
        'لا يمكن نشر هذا الإعلان سوى في البلد حيث يتواجد فيه العقار، السيارة أو السلعة',
        'لا توجد نتيجة من نشر الاعلان في ظل توقف السفر بشكل شبه كامل',
        'group=سياسة الموقع',
        'يرجى اضافة المعلومات الخاصة بمؤسسة التنظيم العقاري ريرا',
        'لا يمكن نشر هكذا إعلان دون ادراج رقم الموبايل المستخدم لتفعيل حسابك مع مرجان (فقط) تفادياً للخطأ وتحمل المسؤولية القانونية في حال حدوث ذلك',
        'لا يمكن نشر إعلانات لها علاقة بالتدخين ولوازمه',
        'للأسف هذا القسم قد تم الغاؤه',
        'لا يمكن نشر إعلانات مماثلة طبقاً لسياسة الموقع',
        'اعلانات المتاجرة بالتأشيرات والإقامات مجرمة قانونيا',
        'مضمون هذا الاعلان يتعارض مع مضمون اعلاناتك المنشورة وهذا تحذير قبل ايقاف حسابك بسبب الاحتيال',
        'لا يمكن إضافة صور لاشخاص عشوائياً توافقاً مع قوانين حقوق الملكية وخصوصية الأفراد',
        'يحظر نشر هذا الاعلان ما دمت تتصل بالانترنت بواسطة بروكسي وفي بي ان',
        'الاعلان مرسل من نطاق انترنت مصنف احتيالي من قبل الوكالات الامنية المختصة'
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
        'This ad can on be published in the suitable city section',
        'please choose \"international real estate\" section to publish your ad',
        'this ad can only be published in countries where your offices and services are located',
        'this ad cannot be published in a country while you reside in a different country or your account might be blocked',
        'this ad cannot be published in countries other than the country of origin (cars, real estate, goods)',
        'worthless seeking work ad posting while travel is prohibited',
        'group=Website Policy',      
        'please add missing details concerning RERA',       
        'cannot publish this ad unless you add ONLY the mobile number (used to activate your mourjan account) to the contact information to avoid misusage and be held responsible in case of legal dispute',
        'All ads related to smoking are against the website policy and cannot be published',
        'This type of ads is against the website policy and cannot be published',
        'Sorry but this ad section is not supported anymore',
        'Selling Visas and work permits is against the law',
        'This ad contradicts with the sense of your other published ads, and this is a warning before blocking your account for fraud',
        'Cannot add pictures of random people due to copyrights and legal privacy policies',
        'The user behind VPN/PROXY ad posting is prohibited',
        'Posting ad from fraudulent connection is not accepted'
    ]
};</script><?php
                }
                break;
            case 'drafts':
                $this->pendingAds();
                break;
            
            case 'rejected':
                $this->pendingAds(3);
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
    
    
    function renderEditorsBox(int $state=0, bool $standalone=false) : string {
        \ob_start();
        $isSuperUser=$this->user->isSuperUser();
        $isAdminUser=$this->user->isLoggedIn(9);
        if ($isSuperUser||$this->user->isLoggedIn(9)) {
            $filters=$this->user->getAdminFilters();
        }
        
        //if ($this->user->isLoggedIn(9)) {
            /*
            ?><style><?php
            ?>.stin,.phc{display:none}.prx h4{margin-bottom:5px}.prx{display:block;clear:both;width:300px}.prx a{color:#00e}.prx a:hover{text-decoration:underline}<?php
            ?>.pfrx{height:auto}.prx select{width:260px;padding:3px 5px;margin:10px}.pfrx input{padding:5px 20px;margin:5px 0 10px}<?php            
            if (isset($filters['active']) && $filters['active']) {
                ?>.pfrx{background-color:#D9FAC8}<?php
            }
            ?></style><?php
             * 
             */
        //}
        
        
        if ($isSuperUser || $this->user->isAdvancedUser()) {
            //if ($standalone) { echo '<div class=fl>'; }
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
                    return '';
            }
            
            if ($state===1) {
                $baseUrl=$this->router->getLanguagePath('/myads/');
                ?><div id=filters><form action="<?=$baseUrl?>" method=GET class=account><?php
                ?><input type=hidden name=sub value=pending /><?php
                
                ?><span class="maw mb-16"><?php
                ?><input type=checkbox name=approved value=1<?=$filters['approved']==1?' checked':''?> style="margin-inline-end:5px" onchange="this.form.submit()"><?php
                ?><label for=approved class=fw-300>Show Approved</label></span><?php
                
                
                if ($filters['uid']) {
                    echo '<input type=hidden name=fuid value="', $filters['uid'], '" />', $this->router->isArabic()?'مستخدم':'user', ': <b>', $filters['uid'], '</b>';                    
                }
                /*
                if ($filters['active']) {
                    ?><select name=fh onchange="this.form.submit()"></select><?php
                }
                */
                
                echo '<select name=fhl onchange="this.form.submit()">';
                echo '<option value=0', $filters['lang']==0?' selected':'', '>', $this->lang['lg_sorting_0'],'</option>';
                echo '<option value=1', $filters['lang']==1?' selected':'', '>العربي فقط</option>';
                echo '<option value=2', $filters['lang']==2?' selected':'', '>الانجليزي فقط</option>';
                echo '</select>';

                ?><select name=fro onchange="this.form.submit()"><?php
                echo '<option value=0', $filters['root']==0 ? ' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                foreach ($this->router->pageRoots as $id=>$root) {
                    echo '<option value=', $id, $filters['root']==$id ? ' selected':'', '>', $root['name'], '</option>';
                }
                ?></select><?php
                
                if ($filters['root']==3){
                    ?><select name=fpu onchange="this.form.submit()"><?php
                    echo '<option value=0', $filters['purpose']==0?' selected':'', '>', $this->lang['opt_all_sections'], '</option>';
                    foreach ($this->router->pageRoots[3]['purposes'] as $id=>$purpose) {
                        echo '<option value=', $id, $filters['purpose']==$id?' selected>':'>', $purpose['name'], '</option>';
                    }
                    ?></select><?php
                }
                
                if ($filters['active']) {
                    ?><input class="maw" style="margin-top:8px;padding-inline-start:4px;border:1px solid var(--mdc12);border-radius:4px;font-size:15px;color:var(--mdc80)" type=reset onclick="location.href='<?=$baseUrl?>?sub=pending'" value="<?=$this->lang['search_cancel']?>" /><?php
                }
                ?></form></div><?php
            }
        }

        if (($this->user->isLoggedIn(9) && $this->getGetString('sub')==='pending') || $isSuperUser) {
            //if (!isset($filters) || !$filters['active']) {   
                echo '<div id=editors class=account>';
                ?><span class="hvn50okt2 d2d9s5pl1g n2u2hbyqsn"><?= $isSuperUser ? '<a href="'.$link.'69905">Robert</a>':'Robert'?></span><?php
                ?><span class="f3iw09ojp5 a1zvo4t2vk"><?= $isSuperUser ? '<a href="'.$link.'1">Bassel</a>':'Bassel'?></span><?php
                ?><span class="a1zvo4t4b8"><?=$isSuperUser ? '<a href="'.$link.'2100">Nooralex</a>':'Nooralex'?></span><?php
                ?><span class="f3iw09r6wn"><?=$isSuperUser ? '<a href="'.$link.'2100">Moe</a>':'Moe' ?></span><?php
                ?><span class="n2u2hc8xil"><?=$isSuperUser ? '<a href="'.$link.'477618">Samir</a>':'Samir'?></span><?php
                ?><span class="x1arwhzqsl"><?=$isSuperUser ? '<a href="'.$link.'38813">Editor 1</a>':'Editor1'?></span><?php
                ?><span class="d2d9s5p1p2"><?=$isSuperUser ? '<a href="'.$link.'44835">Editor 2</a>':'Editor2'?></span><?php
                ?><span class="b2ixe8tahr"><?=$isSuperUser ? '<a href="'.$link.'53456">Editor 3</a>':'Editor3'?></span><?php
                ?><span class="hvn50s5hk"><?=$isSuperUser  ? '<a href="'.$link.'166772">Editor 4</a>':'Editor4'?></span><?php
                ?><span class="j1nz09nf5t"><?=$isSuperUser ? '<a href="'.$link.'516064">Editor 5</a>':'Editor5'?></span><?php
                ?><span class="x1arwii533"><?=$isSuperUser ? '<a href="'.$link.'897143">Editor 6</a>':'Editor6'?></span><?php
                ?><span class="hvn517t2q"><?=$isSuperUser  ? '<a href="'.$link.'897182">Editor 7</a>':'Editor7'?></span><?php
                ?><span class="hvn51amkw"><?=$isSuperUser  ? '<a href="'.$link.'1028732">Editor 8</a>':'Editor8'?></span><?php
                echo '</div>';                               
            //}
            
            //if ($standalone) { echo '</div>'; }
        }
        $result=\ob_get_contents();
        \ob_end_clean();
        return $result;
    }

    
    function getAdSection(Ad $ad, int $rootId=0, &$isMultiCountry=false) : string {
        $section='';
        
        //$name='name_'.$this->router->language;
        switch($ad->purposeId()) {
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->router->sections[$ad->sectionId()][$this->name].' '.$this->router->purposes[$ad->purposeId()][$this->name];
                break;
            case 6:
            case 7:
                $section=$this->router->purposes[$ad->purposeId()][$this->name].' '.$this->router->sections[$ad->sectionId()][$this->name]??'';
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->router->purposes[$ad->purposeId()][$this->name].'/', $this->router->sections[$ad->sectionId()][$this->name]??'')){
                    $section=$this->router->sections[$ad->sectionId()][$this->name];
                }
                else {
                    $in=' ';
                    if ($this->router->language==='en') { $in=' '.$this->lang['in'].' '; }
                    $section=$this->router->purposes[$ad->purposeId()][$this->name].$in.($this->router->sections[$ad->sectionId()][$this->name]??'');
                }
                break;
        }

        $cndic=$this->router->db->asCountriesDictionary();

        $comma=$this->router->isArabic()?'،':',';        
        $countriesArray=[];
        $cities=$this->router->cities;
                
        $content='';
        foreach ($ad->dataset()->getRegions() as $city) {                    
            if (isset($cities[$city]) && isset($cities[$city][\Core\Data\Schema::BIN_COUNTRY_ID])) {
                $country_id=$cities[$city][\Core\Data\Schema::BIN_COUNTRY_ID];
                        
                if (!isset($countriesArray[$cities[$city][\Core\Data\Schema::BIN_COUNTRY_ID]])) { 
                    $ccs=$cndic[$country_id][\Core\Data\Schema::COUNTRY_CITIES];
                    if ($ccs && \count($ccs)>1) {
                        $countriesArray[$country_id]=[$cndic[$country_id][$this->name], []];
                    }
                    else {
                        $countriesArray[$country_id]=[$cndic[$country_id][$this->name], false];
                    }
                }
                
                if ($countriesArray[$country_id][1]!==false) {
                    $countriesArray[$country_id][1][]=$cities[$city][$this->name];
                }
            }
        }
        
        $i=0;
        foreach ($countriesArray as $key => $value) {
            if ($i) {
                $content.=' - ';
                $isMultiCountry=true;
            }
            $content.=$value[0];
            if ($value[1]!==false) { $content.=' ('.\implode ($comma, $value[1]).')'; }
            $i++;
        }
                
        if ($content) {
            $section=$section.' '.$this->lang['in'].' '.$content;
        }

        if ($section) {
            $section='<span>'.$section.'</span>{}<b>'.$this->formatSinceDate($ad->getDateModified()).'</b>';
        }
        return $section;
    }
    
    
    private function accountButton(string $href, string $text, bool $active, int $count, string $class="btn") : void {
        ?><a href="<?=$href?>" class="<?=$class?><?=$active?' current':''?>"><?php
        echo $text;
        if ($active && $count>0) {  echo ' (', $count, ')';  }
        ?></a><?php        
    }
    
    
    function pendingAds(int $state=0) : void {       
        $isAdmin=$isAdminOwner=false;
        $isAdvancedAdmin=$this->user()->isAdvancedUser();
        $isSuperAdmin=$this->user()->isSuperUser(); //||$isAdvancedAdmin;        
        $isAdmin=($this->user->level()===9);
        if ($isAdmin && $state===1) {
            $this->user->info['lft']=time();
        }
        
        $sub=$this->getGetString('sub');
        $uid=$isAdmin?$this->getGetInt('u'):0;

        $this->adList->setState($state)->fetchFromAdUser();
        $count=$this->adList->count();
        $dbCount=$this->adList->dbCount();

       
        ?><div class="row viewable"><div class=col-12><?php

        $this->side->avatar()->menu();

        if ($isAdmin) {
            $this->side->addBlock('editors', $this->renderEditorsBox($state));
        }
        echo $this->side->build();
     
        ?><div class="col-10 ff-cols body mw"><?php
        $this->welcome();
        ?><div class="adstatus mb-64"><?php
        $this->accountButton($this->router->getLanguagePath('/myads/').($uid>0?'?u='.$uid:''), $this->lang['ads_active'], $sub==='', $dbCount, 'btn state online');
        $this->accountButton($this->router->getLanguagePath('/myads/').'?sub=pending'.($uid>0?'&u='.$uid:''), $this->lang['home_pending'], $sub==='pending', $dbCount, 'btn state penging');
        $this->accountButton($this->router->getLanguagePath('/myads/').'?sub=drafts'.($uid>0?'&u='.$uid:''), $this->lang['home_drafts'], $sub==='drafts', $dbCount, 'btn state draft');
        $this->accountButton($this->router->getLanguagePath('/myads/').'?sub=archive'.($uid>0?'&u='.$uid:''), $this->lang['home_archive'], $sub==='archive', $dbCount, 'btn state archive');
        ?></div><?php

        
        if ($this->adList->count()>0) {
            $as=\Core\Model\NoSQL::instance();
            $ips=[];
            $hasPrevious=($this->adList->page()>0);
            $hasNext=((($this->adList->page()+1)*$this->adList->limit())<$this->adList->dbCount());
            //\error_log("{$this->adList->page()}*{$this->adList->limit()}<{$this->adList->dbCount()}");
            $renderAssignedAdsOnly=($state>0 && $state<5);
                               
            $isAdminProfiling=(boolean)($this->get('a') && $this->user()->level()===9);
            if ($isAdminProfiling) { $renderAssignedAdsOnly=false; }           
            
            if ($state===7) {
                if ($this->router->config->get('enabled_charts') && !$isAdminProfiling) {
                    ?><button id="refreshChart"><a href="javascript:void(0);" onclick="d.userStatistics(<?=$this->uid?>);"><i class="icn icn-sync"></i></a></button><?php
                    ?><div class=row><canvas id=canvas class=col-12></canvas></div><?php
                }                
            }
            
            ?><div class=row><div class=mls><?php
            $linkLang=$this->router->language==='ar'?'':$this->router->language.'/';
            
            $permission=MCPermission::instance();
            
            $this->adList->rewind();
            while ($this->adList->valid()) {
                $phoneValidErr=false;
                $link=$altlink=$liClass='';
                
                $cad=$this->adList->current();
                $textClass=$cad->rtl()?'ar':'en';
                    
                if ($isAdmin) { 
                    $isAdminOwner=($cad->uid()===$this->user->id()?true:false);
                    $isAdOwner=($cad->uid()===$this->user->id()?true:false);
                    if (!isset($ips[$cad->dataset()->getIpAddress()])) {
                        $pk=$as->getConnection()->initKey('mccache', 'ipqs', $cad->dataset()->getIpAddress());
                        if ($as->getRecord($pk, $record, ['info'])===\Aerospike::OK) {
                            $ips[$cad->dataset()->getIpAddress()]=\json_decode($record['info'], true);
                        }
                    }
                }
                
                $assignedAdmin='';
                if ($isAdOwner===false && !$permission->isSuperAdmin()) {
                    $assignedAdmin=$this->assignAdToAdmin($cad->id(), $this->user()->id());
                    
                    if ($cad->getSuperAdmin()>0 && !$permission->canSeeAdsSentToAdmin()) {
                        $this->adList->next();
                        continue;
                    }
                    
                    if ($cad->getSuperAdmin()===0 && $assignedAdmin!==$this->user->id() && $this->adList->userId()===0) {
                        $this->adList->next();
                        continue;
                    }
                    
                    if ($permission->canSeeAdsSentToAdmin() && $assignedAdmin>0) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin='<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin='';
                    }
                }
                
                if ($permission->isSuperAdmin()) {
                    $assignedAdmin=$this->getAssignedAdmin($cad->id());
                    if ($assignedAdmin>0) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin='<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin='';
                    }
                }
                
                /*
                if ($isAdOwner===false && $permission->isSuperAdmin()) {
                    
                    $assignedAdmin=$this->assignAdToAdmin($cad->id(), $this->user()->id());
                    if ($permission->canSeeAdsSentToAdmin() && $assignedAdmin>0) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin='<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin='';
                    }
                    
                }
                *
                 * 
                 */
                
                if ($isAdmin && $renderAssignedAdsOnly && !$isAdminOwner) {
                    /*
                    $assignedAdmin=$this->assignAdToAdmin($cad->id(), $this->user()->id());
                    if (!$isSuperAdmin && $assignedAdmin>0 && $assignedAdmin!==$this->user->id()) {
                        
                        
                        if ($cad->getSuperAdmin()==0 && !$permission->canSeeAdsSentToAdmin()) {
                            $this->adList->next();
                            continue;                            
                        }
                    }
                    
                    if ($isSuperAdmin && $assignedAdmin) {
                        $__e=$this->editors[$assignedAdmin]??$assignedAdmin;
                        $assignedAdmin='<span style="padding:0 5px;">'.$__e.'</span>';
                    }
                    else {
                        $assignedAdmin='';
                    }
                    */
                }
                
                $isFeatured=$cad->isFeatured(); 
                $isFeatureBooked=$cad->isBookedFeature();
                    
                if (!$isFeatureBooked && ($cad->state()===4 || ($cad->dataset()->getBudget()>0))) {
                    $isFeatureBooked=true;
                }
                
                $text=$cad->dataset()->getNativeText();
                $altText=$cad->dataset()->getForeignText();
                    
                $pic=false;                
                $thumbs='';
                $hasAdminImgs=$onlySuper=0;
                
                if ($isAdmin) {
                    $images='';
                    foreach ($cad->dataset()->getPictures() as $img=>$dim) {
                        if ($images) { $images.='||'; }
                        $images.='<img width=118 src=\"'.$this->router->config->adImgURL.'/repos/s/'.$img.'\" />';
                        $thumbs.='<span class=ig data-w='.$dim[0].' data-h='.$dim[1].'><img class=lazy data-path="'.$img.'" /></span>';
                        $hasAdminImgs=1;
                    }
                    
                    if ($images) { $images.='||'; }
                    
                    $images.='<img src="'.$this->router->config->imgURL.'/se/'.$cad->sectionId().'.svg" />';                  
                }
                else {                    
                    //if (!empty($cad->dataset()->getPictures()) /*isset($content['pics']) && is_array($content['pics']) && count($content['pics'])>0*/) {
                    //    $picCount=count($content['pics']);
                    //    $pic = isset($content['pic_def']) ? $content['pic_def'] : array_keys($content['pics'])[0];
                    //    $this->globalScript.='sic[' . $ad['ID'] . ']="<img width=\"120\" src=\"'.$this->router->cfg['url_ad_img'].'/repos/s/' . $pic . '\" /><span class=\"cnt\">'.$picCount.'<span class=\"i sp\"></span></span>";';
                    //    $pic = '<span class=ig></span>';
                    //} 
                    //else {
                    //    $this->globalScript.='sic[' . $ad['ID'] . ']="<img class=\"ir\" src=\"'.$this->router->config->imgURL.'/90/' . $cad->sectionId() .$this->router->_png. '\" />";';
                    //$pic='<span class=ig><img src="'.$this->router->config->imgURL.'/se/'. $cad->sectionId() .'.svg" /></span>';
                    //}
                }
                
                if ($this->user()->isLoggedIn(9)) {
                    $onlySuper=$cad->getSuperAdmin()>0?$cad->getSuperAdmin():0;
                    if ($onlySuper) {
                        if ($onlySuper>0 && $onlySuper<1000) {                                              
                            switch($cad->getSuperAdmin()) {
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
                    
                    $userMobile=$cad->profile()->getMobile(TRUE)->getNumber();                    
                    $needNumberDisplayFix=(!\preg_match('/span class=?pn/u', $text));

                    $cui=$cad->dataset()->getContactInfo();
                    if (isset($cui['p']) && \is_array($cui['p'])) {
                        foreach ($cui['p'] as $p) { 
                            $isUserMobile = false;
                            try {
                                $num=$this->phoneUtil->parse($p['v'],$p['i']);
                                if ($num && $this->phoneUtil->isValidNumber($num)) {
                                    if ($userMobile && '+'.$userMobile == $p['v']) {
                                        $isUserMobile=true;
                                    }
                                
                                    $type=$this->phoneUtil->getNumberType($num);  
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
                    
                        
                    $name=$cad->uid() . '#' . ($cad->profile()->getFullName() ? $cad->profile()->getFullName() : $cad->profile()->getDisplayName());
                    $style='';
                    if ($cad->profile()->getLevel()===4) {
                        $style=' style="color:orange"';
                    }
                    elseif ($cad->profile()->getLevel()===5) {
                        $style=' style="color:red"';
                    }
                    
                    $profileLabel=$cad->profile()->getProvider()?$cad->profile()->getProvider():'profile';
                    if ($userMobile) {
                        $unum=$this->phoneUtil->parse('+'.$userMobile, 'LB');
                        $XX=$this->phoneUtil->getRegionCodeForNumber($unum);
                        $profileLabel = '+'.$userMobile;
                        
                        if ($XX!=='') {
                            $devices=$cad->profile()->getDevices();
                            if ( \count($devices)>0 && $XX!==$devices[0]->getCarrierCountryCode()) {                           
                                $profileLabel='" style="color:#F1948A">('.$XX. ')'.$profileLabel;
                            }
                            else {
                                $profileLabel='">('.$XX. ')'.$profileLabel;
                            }
                        }
                    }
                    
                    $title='<div class=user><a target=_similar href="'.
                            ($isSuperAdmin ? $this->router->getLanguagePath('/admin/').'?p=' . $cad->uid() : $cad->profile()->getProfileURL()).
                            $profileLabel.'</a><a target=_similar'.$style.' href="'.
                            $this->router->getLanguagePath('/myads/').'?u='.$cad->uid().'">'.$name.'</a>';
                    
                    $geo = preg_replace('/,/', '' , preg_replace('/[0-9\.]|(?:^|\s|,)[a-zA-Z]{1,3}\s/', '', $cad->dataset()->getUserLocation()));
                    $title.='<span>' . $cad->dataset()->getUserLocation() . '</span>';
                    
                    if ($state===1 && $cad->getDateAdded()===$cad->getDateModified()) {
                        $title.='<span><span class="rj ren"></span></span>';
                    }
                    
                    //$title.=($phoneValidErr!==false ? ($phoneValidErr==0 ? '<span>T<i class="icn m icn-done"></i></span>' : '<span>T<i class="icn m icn-fail"></i></span>'):'' );
                    $title.=($phoneValidErr!==false ? ($phoneValidErr==0 ? '' : '<span>T<i class="icn m icn-fail"></i></span>'):'' );
                   
                    
                    $ss = $cad->dataset()->getAppShortName();
                    if ($cad->dataset()->getIpScore()>=50) {
                        $ss.='/'.$cad->dataset()->getIpScore();
                    }
                   
                    $class= '';                    
                    if ($isFeatured) {
                        $class=' style="color:green"';
                    }
                    else if($isFeatureBooked) {
                        $class=' style="color:blue"';
                    }
                    
                    $pt='';
                    switch ($cad->publisherType()) {
                        case 1:
                            if (!$cad->isJob()) {
                                $pt='Owner';
                            }
                            break;
                        case 3:
                            switch ($cad->rootId()) {
                                case 1:
                                    $pt='Broker';
                                    break;
                                case 2:
                                    $pt='Dealership';
                                    break;
                                case 3:
                                    $pt='Agency';
                                    break;
                                default:
                                    $pt='Business';
                                    break;
                            }
                            break;
                    }
                    if (empty($pt) && $cad->dataset()->getORN()>0) {
                        $pt='Broker';                        
                    }
                    $title.='<b'.$class.'>'.$pt.'#'.$cad->id().'#'.$ss.'</b>';
                    $title.='</div>';
                
                } // here
                    

                if ($state===7) {
                    // after long idle time, refresh passed here
                    $liClass.='atv';
                    $link=($cad->rtl()?'/':'/en/').$cad->id().'/';
                    if($altText) $altlink='/en/'.$cad->id().'/';                        
                        
                    if ($isFeatured || $isFeatureBooked) { $liClass.=' vp'; }
                }
                
                //if ($state>6) {
                    //$ad['CITY_ID']=$ad['ACTIVE_CITY_ID'];
                    //$ad['COUNTRY_ID']=$ad['ACTIVE_COUNTRY_ID'];
                //}
                                
                $adClass='myad';
                
                if ($isFeatured||$isFeatureBooked) { $adClass.=' feature'; }
                
                switch ($cad->state()) {
                    case 1:
                    case 4:
                        $adClass.=' pending';
                        break;
                    case 2:
                        $adClass.=' approved';
                        break;
                }
                               
                if ($onlySuper) { 
                    $adClass.=' warn';                
                }
                
                // new look
                ?><article id=<?php                
                echo $cad->id(), ' class="', $adClass, '" data-status=', $cad->state(), ' data-fetched=0';
                
                if ($isAdmin) { 
                    echo ' data-ro=', $cad->rootId(), ' data-se=', $cad->sectionId(), ' data-pu='.$cad->purposeId(), ' data-uid='.$cad->uid();                     
                }
                echo ' data-hl="', $cad->dataset()->getUserLanguage(), '"'; 
                
                
                if ($permission->isSuperAdmin() /*$this->user->isLoggedIn(9*/ ) {
                    //if ($this->user->isSuperUser()) {
                    echo ' style="color:var(--mdc80)"';
                }
                else {
                    echo ' style="color:var(--mdc90)"';
                }
                //}
               
                ?>><header><?php
                switch ($cad->state()) {
                    case 1:
                    case 4:
                        ?><div><div class=tooltip><i class="icn m icon-state"></i><span class=tooltiptext onmouseover=d.ipCheck(this)><?php
                        echo $onlySuper?$onlySuper:'...';
                        ?></span></div><?php
                        echo '<span class=msg>', $this->lang['pendingMsg'],'</span></div>';
                        echo '<span class=alloc>';
                        if ($cad->getSuperAdmin()>0) {
                            echo 'Help: ';
                            if (isset($this->adminReasons[$cad->getSuperAdmin()])) {
                                echo $this->adminReasons[$cad->getSuperAdmin()];
                            }
                            else if (isset ($this->editors[$cad->getSuperAdmin()])) {
                                echo $this->editors[$cad->getSuperAdmin()];                                
                            }
                            else {
                                echo $cad->getSuperAdmin(), ' Undefined!';
                            }                            
                        }
                        else {
                            echo ($assignedAdmin?$assignedAdmin:'');
                        }
                        echo '</span>';
                        break;

                    case 2:
                        echo '<div><i class="icn m icon-state"></i><span class=msg>', $this->lang['approvedMsg'], '</span></div>';
                        if ($assignedAdmin) { echo '<span class=alloc>', $assignedAdmin, '</span>'; }
                        break;
                    
                    case 3:
                        echo '<div class="nb nbr"><span class=fail></span>', $this->lang['rejectedMsg'], ($cad->dataset()->getMessage() ? ': ' . $cad->dataset()->getMessage() : ''),($assignedAdmin ? $assignedAdmin:'') ,'</div>';
                        break;
                    
                    default:
                        break;
                }
                
                ?></header><?php
                
                
                if ($isAdmin) {
                    echo $title;
                    if (isset($ips[$cad->dataset()->getIpAddress()])) {
                        $ip=$ips[$cad->dataset()->getIpAddress()];
                        $showIPQS=($ip['vpn']||$ip['active_vpn']||$ip['tor']||$ip['active_tor']);
//                        if ($ip['vpn']||$ip['active_vpn'])
                        if ($showIPQS) {
                            ?><div class=ipqs><?php
                            ?><span>VPN: <?=$ip['vpn'].'/'.$ip['active_vpn']?></span><?php
                            ?><span>TOR: <?=$ip['tor'].'/'.$ip['active_tor']?></span><?php
                            ?></div><?php
                        }
                    }
                }
                
                $userLang=$cad->dataset()->getUserLanguage();             
                $pc=\count($cad->dataset()->getPictures());
                //if ($hasAdminImgs) { echo '<p class=pimgs>', $thumbs, '</p>'; }
                if ($pc>0 && !empty($altText)) {
                    $bdclass='adbody a';
                }
                else if ($pc>0) {
                    $bdclass='adbody b';
                }
                else if (!empty($altText)) {
                    $bdclass='adbody c';                    
                }
                else {
                    $bdclass='adbody';
                }
                ?><div class="<?=$bdclass?>"><img class=icon src=<?=$this->router->config->imgURL.'/se/'.$cad->sectionId().'.svg'?> /><?php
                ?><div class=wording><?php
                ?><section class="<?=$cad->rtl()?'ar':'en'?>"<?php
                //echo $link?' onclick="wo('.$link.')"' : '';
                
                if ($isAdmin) { echo ' onmouseup="d.textSelected(this);"'; }
                if ($isAdmin) { echo ' oncontextmenu="d.lookup(this);return false;"'; }
                //echo '>', ($pic ? $pic :''), '<div>',$text,'</div>';
                ?>><div><?=$text?></div><?php
                ?></section><?php
                
                if ($altText) {
                    echo '<section class=en data-foreign=1';
                    /*
                    if ($altlink) {
                        echo ' onclick="wo(', $altlink, ')"';
                    }
                    elseif ($isAdmin) {
                        echo ' onselect="MSAD(this)" ';
                    }*/
                    echo '><div>', $altText, '</div>';
                    echo '</section>';
                }
                
                ?></div><?php
                
                if ($pc>0) {
                    ?><div class="photos<?=$pc===1?' double':''?>"><?=$thumbs?></div><?php
                }
                ?></div><?php
                
                
                if (($cad->latitude()>0||$cad->longitude()>0) && $cad->dataset()->getLocation()) {
                    echo '<div class="oc ocl"';
                    if ($isAdmin) { echo ' onmouseup="d.textSelected(this);"'; }
                    if ($isAdmin) { echo ' oncontextmenu="d.lookup(this);return false;"'; }
                    echo '><span class="i loc"></span>', $cad->dataset()->getLocation();
                    
                    echo '</div>';
                }
                
                ?><div class=note<?php
                if (!$isAdminProfiling && $isAdmin && \in_array($state, [1,2,3])) { echo ' onclick="d.quick(this)"'; }
                $isMultiCountry=false;
                ?>><?php
                //, $this->getAdSection($cad, $cad->rootId(), $isMultiCountry);
                
                if ($cad->rootId()===1 && \in_array($cad->purposeId(), [1,2,8]) && $cad->countryId()===2 && $cad->cityId()===14) {
                    echo \preg_replace('/\{\}/', '<span>ORN: '.$cad->dataset()->getORN().', BRN: '.$cad->dataset()->getBRN().', Permit: '.$cad->dataset()->getPermit().'</span>', $this->getAdSection($cad, $cad->rootId(), $isMultiCountry));
                    //echo '<span>ORN: ', $cad->dataset()->getORN(), ', BRN: ', $cad->dataset()->getBRN(), ', Permit: ', $cad->dataset()->getPermit(), '</span>';
                }
                else {
                    echo \preg_replace('/\{\}/', '', $this->getAdSection($cad, $cad->rootId(), $isMultiCountry));
                }
                
                ?></div><?php
                
                //if ($state>6) {
                //    echo '<a class=com href="'.$link.'#disqus_thread" data-disqus-identifier="'.$ad['ID'].'" rel="nofollow"></a>';
                //}

                $isSuspended=$cad->profile()?$cad->profile()->isSuspended():false; //$this->user->getProfile() ? $this->user->getProfile()->isSuspended() : FALSE;
                if (!$this->user->getProfile()) {
                    \error_log("this->user->data is null for user: ".$this->user->info['id'] . ' at line '.__LINE__);
                }
                
                $isSystemAd=($cad->documentId()>0);

                ?><footer><?php
                if ($state<7) {                 
                    if ($isSuperAdmin) {
                        ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method=post><?php
                        ?><input type=hidden name=ad value="<?= $cad->id() ?>" /><?php
                        ?><button onclick="d.edit(this)"><span class="rj edi"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></button><?php
                        ?></form><?php
                    }
                    else if (!$isSystemAd) {
                        if(!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?ad='.$ad['ID'] : '') ?>" method=post><?php
                            ?><input type=hidden name=ad value="<?= $cad->id() ?>" /><?php
                            ?><button onclick="d.edit(this)"><span class="rj edi"></span><?= $state ? $this->lang['edit_ad']:$this->lang['edit_publish'] ?></button><?php
                            ?></form><?php
                        }                    
                        if (!$isAdmin || ($isAdmin && $isAdminOwner)) {
                            ?><a onclick="adel(this)" href='javascript:void(0)'><span class="rj del"></span><?= $this->lang['delete'] ?></a><?php
                        }
                    }
                }
                elseif ($state===7) {
                    $ad_hold=0;
                    if (isset($this->user->params['hold']) && $this->user->params['hold']==$cad->id()) {
                        if ($this->user->holdAd($cad->id())) {
                            $ad_hold=1;
                            ?><b class=anb><span class="done"></span><?= $this->lang['retired'] ?></b><?php
                        }
                    }
                    
                    if (!$ad_hold) {
                        if (/*(!$isAdmin || ($isAdmin && $isAdminOwner)) &&*/ ($cad->isFeatured()===false && $cad->isBookedFeature()===false)) {
                            ?><button onclick="<?=$isMultiCountry?'mCPrem()':($this->balance>0?'d.doPremium(this)':'noPremium()')?>"><?=$this->lang['make_premium']?></button><?php                                    
                        }

                        if (/*($isAdmin===false || ($isAdmin && $isAdminOwner)) &&*/ $cad->isFeatured()) {
                            ?><button onclick="d.unpublish(this, true)"><?=$this->lang['stop_premium_bt']?></button><?php                                    
                            /*?><button onclick="cancelPremium(this)"><?=$this->lang['stop_premium_bt']?></button><?php*/
                        }
                        
                        if (!$isSystemAd && (!$isAdmin || ($isAdmin && $cad->isBookedFeature()===false && $cad->isFeatured()===false) || ($isAdmin && $isAdminOwner))) {
                            ?><button onclick="d.unpublish(this, false)"><?=$this->lang['hold']?></button><?php             
                        }
                        
                        if (!$isSystemAd) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$ad['ID'] : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $cad->id() ?>" /><?php
                            ?><button onclick="d.edit(this)"><?= $this->lang['edit_ad'] ?></button><?php
                            ?></form><?php 
                        }
                        if ($this->router->config->get('enabled_ad_stats') && !$isAdminProfiling) {
                            ?><button onclick="d.chart(this)" class=stad></button><?php
                        }
                    }                            
                }
                elseif ($state===9) {
                    if (!$isSystemAd) {
                        if (!$isSuspended) {
                            ?><form action="/post/<?= $linkLang.(!$this->isUserMobileVerified ?'?adr='.$cad->id() : '') ?>" method="post"><?php
                            ?><input type="hidden" name="adr" value="<?= $cad->id() ?>" /><?php
                            ?><button onclick="d.edit(this)"><?= $this->lang['edit_republish'] ?></button><?php
                            /*?><span class="lnk" onclick="fsub(this)"><span class="rj edi"></span><?= $this->lang['edit_republish'] ?></span><?php*/
                            ?></form><?php 
                            if($this->isUserMobileVerified && isset($content['version']) && $content['version']==2) {
                                ?><span class="lnk" onclick="are(this)"><span class="rj ren"></span><?= $this->lang['renew'] ?></span><?php
                            }
                        }
                    }
                    if (!$isSystemAd && (!$isAdmin || ($isAdmin && $isAdminOwner))) {
                        ?><button onclick="d.del(this)"><?= $this->lang['delete'] ?></button><?php
                    }
                    if ($this->router->config->get('enabled_ad_stats') && !$isAdminProfiling) {
                        ?><span class="stad load"></span><?php
                    }
                }
                
               
                if ($this->user()->level()===9) {
                    if ($cad->state()===2 && (!$isSystemAd || $isSuperAdmin)) {
                        ?><input type="button" class="lnk" onclick="rejF(this,<?= $ad['WEB_USER_ID'] ?>)" value="<?= $this->lang['reject'] ?>" /><?php
                        
                        if ($isSuperAdmin) {
                            ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router->isArabic()?'':'/en' ?>/?aid=<?= $ad['ID'] ?>&q="><?= $this->lang['similar'] ?></a><?php
                        }
                        $contactInfo=$this->getContactInfo($content);
                        if (!$isSystemAd || $isSuperAdmin) {
                            if ($contactInfo) {                        
                                ?><a target="blank" class="lnk" onclick="openW(this.href);return false" href="<?= $this->router->isArabic()?'':'/en' ?>/?cmp=<?= $ad['ID'] ?>&q=<?= $contactInfo ?>"><?= $this->lang['lookup'] ?></a><?php
                            }
                        }
                    }
                    else {
                        if ($state>0 && $state<7) {
                            
                            $rank=$cad->profile()->getRank();
                            if (!$isSystemAd || $isSuperAdmin) {         
                                ?><button onclick="d.approve(this)"><?= $this->lang['approve'] ?></button><?php
                                if ($isSuperAdmin) {
                                    ?><button onclick="d.rtp(this)">RTP</button><?php                                    
                                }
                                ?><button onclick="d.reject(this,<?= $cad->uid() ?>)"><?= $this->lang['reject'] ?></button><?php 
                            }
                            
                            if (!$isSuperAdmin && !$onlySuper && !$isSystemAd) {
                                ?><button onclick="d.help(this,<?= $cad->uid() ?>)"><?= $this->lang['ask_help'] ?></button><?php 
                            }     
                            
                            if ($permission->canBlockUser() && $rank<3) {
                                //if (($isSuperAdmin||$isAdvancedAdmin) && $rank<2) {
                                ?><button onclick="d.ban(this,<?= $cad->uid() ?>)"><?= $this->lang['block'] ?></button><?php 
                            }
                            
                            if (!$isSystemAd && $rank<3) {
                                ?><button onclick="d.suspend(this,<?= $cad->uid() ?>)"><?= $this->lang['suspend'] ?></button><?php
                            }
                            
                            
                            if ($permission->canFilterPendingUserAds()/* ($isSuperAdmin||$isAdvancedAdmin)*/ && $this->adList->userId()===0) {
                                ?><button onclick="d.userads(this,<?= $cad->uid() ?>)"><?= $this->lang['user_type_option_1'] ?></button><?php
                            }
                            
                            
                            $contactInfo=$this->getContactInfo($cad->dataset()->getData());
                            if ($permission->canSeeSimilarAds()/* $isSuperAdmin||$isAdvancedAdmin*/) {
                                ?><button onclick=d.similar(this)><?= $this->lang['similar'] ?></button><?php
                            }
                            
                            if ((!$isSystemAd || $isSuperAdmin) && $contactInfo) {
                                ?><button id=revise data-contact="<?= $contactInfo ?>" onclick=d.lookFor(this)><?= $this->lang['lookup'] ?></button><?php
                            }                            
                        }
                    }
                } 
                               
                ?></footer></article><?php
                $this->adList->next();
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
            
            if ($hasNext||$hasPrevious) {
                ?><div class=pgn><?php 
                
                $appendOp = '?';
                $link = $this->router->uri.($this->router->isArabic()?'':$this->router->language.'/');
                
                
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
                
                if ($this->showApproved===1) {
                    $link.=$appendOp.'approved=1';
                    $appendOp='&';
                } 
                
                if(isset($_GET['u']) && $_GET['u']){
                    $link.=$appendOp.'u='.$_GET['u'];
                    $appendOp='&';
                }
                
                if(isset($_GET['a']) && $_GET['a']){
                    $link.=$appendOp.'a='.$_GET['a'];
                    $appendOp='&';
                }
                
                if ($this->adList->userId()>0) {
                    $link.=$appendOp.'fuid='.$this->adList->userId();
                    $appendOp='&';
                }
                
                if ($this->adList->rootId()>0) {
                    $link.=$appendOp.'fro='.$this->adList->rootId();
                    $appendOp='&';
                }
                
                if ($this->adList->purposeId()>0) {
                    $link.=$appendOp.'fpu='.$this->adList->purposeId();
                    $appendOp='&';
                }
                
                if ($this->adList->languageFilter()>0) {
                    $link.=$appendOp.'fhl='.$this->adList->languageFilter();
                    $appendOp='&';
                }
                
                if ($hasPrevious) {
                    $offset=$this->adList->page()-1;
                    ?><a href='<?=$link.($offset?$appendOp.'o='.$offset:'')?>'><?=$this->lang['prev_25']?></a><?php
                }
                echo '<span>'.($this->adList->page()+1).' '.$this->lang['of'].' '.ceil($dbCount/$this->adList->limit()).'</span>';
                if ($hasNext) {
                    //\error_log('has next '.$this->adList->dbCount());
                    ?><a href='<?=$link.$appendOp.'o='.($this->adList->page()+1)?>'><?=$this->lang['next_25']?></a><?php
                }
                
                ?></div><?php
            }
            
            if ($isAdmin && $state<7) {
                ?><div id=rejForm class="inside ff-cols"><select id=rejS></select><?php
                echo '<textarea id=rejT onkeydown="dirElem(this)"></textarea>';
                echo '<div><input type=button class="btn ok" value="', $this->lang['reject'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" /></div>';
                ?></div><?php
                                
                ?><div id=suspForm class="inside ff-cols"><select id=suspS></select><?php
                echo '<textarea id=suspT onkeydown="dirElem(this)" placeholder="', $this->lang['reason_suspension'], '"></textarea>';
                echo '<div><input type=button class="btn ok" value="', $this->lang['suspend'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" /></div>';
                ?></div><?php
                
                ?><div id=banForm class="inside ff-cols"><?php
                echo '<textarea id=banT onkeydown="dirElem(this)"></textarea>';
                echo '<div><input type=button class="btn ok" value="', $this->lang['block'], '" />';
                echo '<input type=button class="btn cancel" value="', $this->lang['cancel'], '" /></div>';
                ?></div><?php
                echo "\n";
                ?><div id=fixForm class=inside><?php
                echo '<div class="col-12 flex ff-rows" style="align-items:flex-start">';
                echo '<div id=qRoot class=col-2><ul class=ff-cols></ul></div>';    
                echo '<div id=qSec class="col-8 sections"><ul></ul></div>';
                echo '<div id=qAlt class="col-2"><ul class=ff-cols></ul></div></div>';
                ?></div><?php
            }
            
            if ($state>=7) {
                ?><div id=chartForm class=inside><div class=row><canvas id=chart class=col-12></canvas></div></div><?php
            }
        } // end ad count>0
        else {
            
            /*?><p class="ph phb db"><?php*/
            $msg='';
            $mcUser=null;
            //$this->renderUserTypeSelector($mcUser);
            
            switch ($state) {
                case 9:
                    $msg = $this->lang['no_archive'];
                    //$this->user->info['archive_ads']=$count;
                    //echo $this->lang['ads_archive'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 7:
                    $msg = $this->lang['no_active'];
                    //$this->user->info['active_ads']=$count;
                    //echo $this->lang['ads_active'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
                case 1:
                case 2:
                case 3:
                    $msg = $this->lang['no_pending'];
                    //$this->user->info['pending_ads']=$count;
                    //echo $this->lang['ads_pending'].($count ? ' ('.$count.')':'');
                    break;
                case 0:
                default:
                    $msg = $this->lang['no_drafts'];
                    //$this->user->info['draft_ads']=$count;
                    //echo $this->lang['ads_drafts'].($count ? ' ('.$count.')':'').' '.$this->renderUserTypeSelector($mcUser);
                    break;
            }
            /*?></p><?php*/     
            //$this->renderEditorsBox($state, true);
           
            if ($isAdmin && $mcUser && $mcUser->isBlocked()) {
                $msg = 'User is Blocked';
                $reason = \preg_replace(['/\</','/\>/'],['&#60;','&#62;'], Core\Model\NoSQL::instance()->getBlackListedReason($mcUser->getMobileNumber()));
                if ($reason) {
                    $msg.='<br />'.$reason;
                }
            }
            
            ?><div class="viewable alert alert-danger"><?= $msg ?></div><?php
            if ($state===7) {                
                if ($this->router->config->get('enabled_charts')) {
                    ?><div class=row><canvas id=canvas class=col-12></canvas></div><?php
                }                
            } 
        }
        ?></div></div></div></div></div><?php
        $this->inlineJS('util.js')->inlineJS('myads.js');
    }
    
    
    function getContactInfo(array $content) : string {
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

    /*
    function renderUserTypeSelector(&$user=null) {
        $userId=$this->user->id();
        $type=0;
        $uid=$this->getGetInt('u');
        if ($uid>0) {
            $userId=$uid;
            $type=\Core\Model\NoSQL::instance()->getUserPublisherStatus($userId); 
            $user=new MCUser($userId);
        }
    
        if ($user!==null) {
            if ($user->isSuspended()) {
                $time=MCSessionHandler::checkSuspendedMobile($user->getMobileNumber());
                $hours=0;
                $lang=$this->router->language;
                if ($time) {
                    $hours=$time/3600;
                    if (\ceil($hours)>1) {
                        $hours=\ceil($hours);
                        if ($lang==='ar') {
                            if ($hours==2) {
                                $hours='ساعتين';
                            }
                            elseif ($hours>2 && $hours<11) {
                                $hours=$hours.' ساعات';
                            }
                            else {
                                $hours=$hours.' ساعة';
                            }
                        }
                        else {
                            $hours=$hours.' hours';
                        }
                    }
                    else {
                        $hours=\ceil($time/60);
                        if ($lang==='ar') {
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
                                $hours=$hours.' دقيقة';
                            }
                        }
                        else {
                            if ($hours>1) {  
                                $hours=$hours.' minutes';
                            }
                            else {                        
                                $hours=$hours.' minute';
                            }
                        }
                    }
                }
                echo '<span class="alert alert-warning" style="align-self:center;width:auto"><span class="wait"></span>'.$hours.'</span>';
            }
            
            echo '<span class="alert alert-info" style="align-self:center;width:auto">', $this->lang['user_type_label'], '&nbsp;<select onchange="d.setUserType(this,'.$userId.')">',
                    '<option value=0>', $this->lang['user_type_option_0'], '</option>',
                    '<option value=1', ($type==1?' selected':''), '>', $this->lang['user_type_option_1'].'</option>',
                    '<option value=2', ($type==2?' selected':''), '>', $this->lang['user_type_option_2'].'</option></select></span>';
        }
    }
    */
    
    function drawSlideShow() : void {
        ?><div class="slideshow-container"></div>                                
        <?php        
    }
}
            
?>