<?php
Config::instance()->incLayoutFile('Page');

define ('MOURJAN_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB');

class Balance extends Page{
    
    private $statementMode = false;
    private $downloadLinkPath = '/web/invoice.php';

    function __construct(){
        parent::__construct();
        if ($this->router->config->get('active_maintenance')) {
            $this->user()->redirectTo($this->router->getLanguagePath('/maintenance/'));
        }
        
        $title = $this->lang['myBalance'];
        
        if (isset($_GET['list']) && $_GET['list']==1) {
            $this->statementMode = true;
            $title = $this->lang['account_balance'];
        }
        
        $this->forceNoIndex=true;
        $this->title=$title;
        $this->requireLogin = true;
        $this->router->config->disableAds();
                
        if($this->isMobile){
            $this->inlineCss.='.ph{background-color:#FFF;padding:15px 10px;border-bottom:1px solid #afafaf}.ph a{float:left}'
                    . '.htf.db{background-color:#FFF;text-align:center;padding:20px 10px;}'
                    . '.stmt li{border:0!important}
                        .stmt .dt0{width:230px;float:right!important;text-align:inherit!important}
            .stmt .et0,.stmt .et1{float:right!important;width:230px;font-size:15px;line-height:1.5em}
            .stmt .et1{width:190px}
            .stmt .ct0{width:90px;text-align:center;float:left}
            .stmt .xt0{border:0!important;border-top:1px solid #aaa!important;width:auto!important;display:none}
            .doc .bt{width:70%;margin:25px}
            .stmt .dwt{float:right!important}';
            if($this->urlRouter->siteLanguage!='ar'){
                $this->inlineCss.='.stmt .et0,.stmt .et1{font-size:13px}.stmt .et0,.stmt .et1,.stmt .dt0{float:left!important}';
                $this->inlineCss.='.stmt .ct0{float:right!important}.stmt .dwt{float:left!important}';
            }
        }
        
        $this->inlineQueryScript.='
            $("ul.ck").click(function(){
                $(this).toggleClass("exp");
            });
                ';
        
        $this->render();
    }
    
    
    function mainMobile() {
        if ($this->user->info['id']) {

            if (!$this->urlRouter->cfg['enabled_post']) {
                $this->renderDisabledPage();
                return;
            }
            $lang= $this->urlRouter->siteLanguage;
            switch($this->urlRouter->module){
                case 'statement':
                    if($this->statementMode){
                        $this->showMobileStatement();
                    }else{
                        $uid = $this->user->info['id'];
                        $data = $this->user->getStatement($uid, 0, false, null, $this->urlRouter->siteLanguage);
                        $hasError = 0;
                        if($data && $data['balance']!==null){
                            $subHeader = '<span class="mc24"></span>'.$data['balance'].' '.$this->lang['gold'];
                        }else{
                            $subHeader = '<br />';
                            $hasError = 1;
                        }

                        ?><p class="ph phb db bph"><?php
                            echo $subHeader.' ';
                            ?><a class="buL" href="/gold/<?= $lang=='ar' ? '':$lang.'/' ?>"><span class="rj add"></span><?= $this->lang['get_gold'] ?></a><?php
                        ?></p><?php 
                        ?><div class="doc ctr"><br /><?php
                        ?><a class="bt go" href="?list=1"><?= $this->lang['account_balance'] ?></a><?php
                        ?><br /><?php
                        ?><a class="bt mj" href="/buyu/<?= $lang=='ar' ? '':$lang.'/' ?>"><?= $this->lang['buy_credit'] ?></a><?php
                        ?><br /><?php
                        ?><a class="bt mj" href="/buy/<?= $lang=='ar' ? '':$lang.'/' ?>"><?= $this->lang['buy_paypal'] ?></a><?php
                        ?></div><?php
                    }
                    break;
                default:
                    break;
            }

        }
        else{
            //$this->renderLoginPage();
        }
    }
    
    
    function main_pane() {
        if ($this->user()->id()) {
            if (!$this->router->config->get('enabled_post') && $this->topMenuIE) {
                $this->renderDisabledPage();
                return;
            }

            switch ($this->router->module) {
                case 'statement':
                    $this->showStatement();
                    break;
                
                default:
                    break;
            }
            $this->inlineJS('balance.js');
        }
        else {
            $this->renderLoginPage();
        }
    } 
    
    
    function showMobileStatement(){ 
        $lang = $this->urlRouter->siteLanguage;
        $uid = 0;
        if(isset($_GET['u']) && is_numeric($_GET['u'])){
            $uid = $_GET['u'];
        }
        $data = $this->user->getStatement($uid, 0, false, null, $this->urlRouter->siteLanguage);
        $hasError = 0;
        if($data && $data['balance']!==null){
            $subHeader = $this->lang['current_balance'].'<span class="mc24"></span>'.$data['balance'].' '.$this->lang['gold'];
        }else{
            $subHeader = '<br />';
            $hasError = 1;
        }
        
        ?><p class="ph phb db bph"><?php
            echo $subHeader;
        ?></p><?php 
        if($hasError){
            ?><div class="htf db"><?= $this->lang['get_balance_error'] ?></div><?php
        }else{
            $pass=false;
            if(isset($data['recs'])){
                $fieldId        = 0;
                $fieldDated     = 1;
                $fieldCredit    = 2;
                $fieldDebit     = 3;
                $fieldBalance   = 4;
                $fieldTitle     = 5;
                $fieldSection   = 6;
                $fieldDesc      = 7;
                $fieldRtl       = 8;
                $fieldState     = 9;
                $fieldCurrency  = 10;
                $fieldPlatform  = 11;
                $fieldTID  = 12;
                $count = count($data['recs']);
                if($count){
                    $pass=true;
                    
                    $startDate = preg_split('/ /', $data['recs'][0][$fieldDated]);
                    $startDate = $startDate[0];
                    
                    $canFilter = false;
                    
//                    $startTime = strtotime($startDate);
//                    $ago31DaysTime = time()-2678400;
//                    if($startTime < $ago31DaysTime){
//                        $canFilter = true;
//                    }
                    
                    $endDate = date("Y-m-d");
                    
                    echo '<div class="stmt'.($lang=='ar' ? ' ar':'').'">';
                    //echo '<div class="filters">'.$this->lang['from'].': <input value="'.$startDate.'" name="from" type="date" '.($canFilter ? '':'disabled="disabled"').' /><span class="sep"></span> '.$this->lang['till'].': <input value="'.$endDate.'" name="till" type="date" '.($canFilter ? '':'disabled="disabled"').' /></div>';
                    //echo '<ul class="hdr"><li class="dt0">'.$this->lang['date'].'</li>';
                    //echo '<li class="ct0">Amount</li>';
                    //echo '<li class="ct0">'.$this->lang['balance'].'</li><li class="ct0">'.$this->lang['credit'].'</li><li class="ct0">'.$this->lang['debit'].'</li><li class="et0">'.(ucfirst($this->lang['detailMore_'.$lang])).'</li></ul>';
                    
                    $alt = 0;
                    for($i=0;$i<$count;$i++){
                        echo '<ul class="'.($data['recs'][$i][$fieldCredit] > 0 ? 'ps': ($alt ? 'ck a':'ck') ).'">';
                        
                        if($data['recs'][$i][$fieldCredit]==0){
                            $alt++;
                            if($alt > 1){
                                $alt = 0;
                            }
                        }
                        if($this->isMobile){
                            
                            $hasDownload = false;
                            if($data['recs'][$i][$fieldCredit] > 0 
                                    && $data['recs'][$i][$fieldCurrency] == 'USD' 
                                    && $data['recs'][$i][$fieldPlatform] == 'PAYFORT'){
                                
                                $signature = base64_encode(strtoupper(hash_hmac('sha1', $data['recs'][$i][$fieldTID], MOURJAN_KEY)));
                                
                                $hasDownload = "<li class='dwt'><a href='{$this->downloadLinkPath}?tid={$data['recs'][$i][$fieldTID]}&signature={$signature}' target='_blank'></a></li>";
                                echo $hasDownload;
                            }
                            
                            echo '<li class="et'.($hasDownload ? 1 : 0).'">';
                            //'.($data['recs'][$i][$fieldState]==8 ? ' sj':'').'
                            echo '<b>';
                            if( $data['recs'][$i][$fieldCredit] > 0){
                                if($data['recs'][$i][$fieldCurrency] == 'MCU'){
                                    echo $this->lang['collection_of'];
                                }else{
                                    echo $this->lang['purchase_of'];
                                }
                                echo ' ';
                            }
                            echo ($data['recs'][$i][$fieldTitle]);
                            echo '</b>';
                            echo '</li>';
                            if ($data['recs'][$i][$fieldCredit]) {
                                echo '<li class=ct0>', ($data['recs'][$i][$fieldCredit]==0 ? '<ct>-</ct>' : '+'.((int)$data['recs'][$i][$fieldCredit]).'<i class="icn icnsmall icn-coin"></i>'), '</li>';
                            }
                            else {
                                echo '<li class=ct0>', ($data['recs'][$i][$fieldDebit]==0 ? '<ct>-</ct>':'-'.((int)$data['recs'][$i][$fieldDebit])), '</li>';
                            }
                            echo '<li class="dt0">'.date('G:i T d/m/Y',$data['recs'][$i][$fieldDated]).'</li>';
                            echo '<li class=ct0>', $data['recs'][$i][$fieldBalance], '</li>';
                            
                        }else{
                            echo '<li class="dt0">'.date('G:i T d/m/Y',$data['recs'][$i][$fieldDated]).'</li>';
                            //echo '<li class="ct0">'.( $data['recs'][$i]['AMOUNT']==0 ? '<ct>-</ct>' : (int)$data['recs'][$i]['AMOUNT'].' '.$data['recs'][$i]['CURRENCY_ID']).'</li>';
                            echo '<li class="ct0">'.$data['recs'][$i][$fieldBalance].'</li>';
                            echo '<li class=ct0>', ($data['recs'][$i][$fieldCredit]==0 ? '<ct>-</ct>' : '+'.((int)$data['recs'][$i][$fieldCredit]).'<i class="icn icnsmall icn-coin"></i>'), '</li>';
                            echo '<li class="ct0">'.($data['recs'][$i][$fieldDebit]==0 ? '<ct>-</ct>':'-'.((int)$data['recs'][$i][$fieldDebit])).'</li>';
                            echo '<li class="et0">';
                            //'.($data['recs'][$i][$fieldState]==8 ? ' sj':'').'
                            echo '<b>';
                            if( $data['recs'][$i][$fieldCredit] > 0){
                                if($data['recs'][$i][$fieldCurrency] == 'MCU'){
                                    echo $this->lang['collection_of'];
                                }else{
                                    echo $this->lang['purchase_of'];
                                }
                                echo ' ';
                            }
                            echo ($data['recs'][$i][$fieldTitle]);
                            echo '</b>';
                            echo '</li>';
                        }
                        if($data['recs'][$i][$fieldDebit] > 0){
                            if($data['recs'][$i][$fieldId] && $data['recs'][$i][$fieldDesc]=='NA' || $data['recs'][$i][$fieldDesc]==''){  
                                echo '<li class="xt0 '.($this->urlRouter->siteLanguage ? 'ar':'en' ).'">';                              
                                echo $this->lang['unavailable_balance_detail'];
                            }else{
                                echo '<li class="xt0 '.($data['recs'][$i][$fieldRtl] ? 'ar':'en' ).'">';
                                switch($data['recs'][$i][$fieldState]){
                                    case 7:
                                        echo '<a class="a7" href="/myads/#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_active'].'</a> ';
                                        break;
                                    case 9:
                                        echo '<a class="a9" href="/myads/?sub=archive#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_archive'].'</a> ';
                                        break;
                                    case 6:
                                    case 8:
                                        echo '<span class="a8">'.$this->lang['st_deleted'].'</span> ';
                                        break;
                                    case 1:
                                        echo '<a class="a1" href="/myads/?sub=pending#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_pending'].'</a> ';
                                        break;
                                    case 0:
                                        echo '<a class="a0" href="/myads/?sub=drafts#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_draft'].'</a> ';
                                        break;
                                }
                                echo $data['recs'][$i][$fieldDesc];
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</div>';
                }
            }
            if(!$pass){
                ?><div class="htf db"><?= $this->lang['no_statement_history'] ?><br /><br /><input onclick="document.location='/gold/<?= $lang=='ar' ? '':$lang.'/' ?>'" class="bt" type="button" value="<?= $this->lang['get_gold'] ?>" /></div><?php
            }
        }
    }
    
    
    function showStatement() : void { 
        $lang = $this->router->language;
        $uid = 0;
        if (isset($_GET['u']) && is_numeric($_GET['u'])) { $uid = $_GET['u']; }
        $data = $this->user()->getStatement($uid, 0, false, null, $this->router->language);
        $hasError = 0;
        if ($data && $data['balance']!==null) {
            $subHeader = $this->lang['current_balance'].'&nbsp;'.$data['balance'].' '.'<span class="icn icnsmall icn-coin"></span>&nbsp;'.$this->lang['gold'];
        }
        else {
            $subHeader = '<br />';
            $hasError = 1;
        }
        
        echo '<div class=row><div class=col-12><div class=card>';
        
        ?><p class="card-content"><?php
            echo $subHeader.' ';
            ?><a class=float-right href="/gold/<?= $lang=='ar' ? '':$lang.'/' ?>"><span class="rj add"></span><?= $this->lang['get_gold'] ?></a><?php
        ?></p><?php 
        
        if ($hasError) {
            ?><div class="htf db"><?= $this->lang['get_balance_error'] ?></div><?php
            echo '</div></div></div>';
            return;
        }
        echo '</div></div></div>';
        
        $pass=false;
        if (isset($data['recs'])) {
            $fieldId        = 0;
            $fieldDated     = 1;
            $fieldCredit    = 2;
            $fieldDebit     = 3;
            $fieldBalance   = 4;
            $fieldTitle     = 5;
            $fieldSection   = 6;
            $fieldDesc      = 7;
            $fieldRtl       = 8;
            $fieldState     = 9;
            $fieldCurrency  = 10;
            $fieldPlatform  = 11;
            $fieldTID  = 12;
            $count = count($data['recs']);
            if ($count) {
                $pass=true;
                    
                $startDate = preg_split('/ /', $data['recs'][0][$fieldDated]);
                $startDate = $startDate[0];
                    
                $canFilter = false;                    
                $endDate = date("Y-m-d");
                echo '<div class=row><div class=col-12>';
                echo '<div class="stmt'.($lang=='ar' ? ' ar':'').'">';
                echo '<ul class=hdr><li class=dt0>', $this->lang['date'], '</li>';
                echo '<li class=ct0>', $this->lang['balance'], '</li><li class=ct0>', $this->lang['credit'], '</li><li class=ct0>', $this->lang['debit'], '</li><li class=et0>', (ucfirst($this->lang['detailMore_'.$lang])), '</li></ul>';
                    
                $alt = 0;
                for ($i=0; $i<$count; $i++) {
                    echo '<ul class="'.($data['recs'][$i][$fieldCredit] > 0 ? 'ps': ($alt ? 'ck a':'ck') ).'">';
                        
                    if ($data['recs'][$i][$fieldCredit]==0) {
                        $alt++;
                        if ($alt>1) { $alt = 0; }
                    }
                        
                    echo '<li class=dt0>', date('G:i T d/m/Y', $data['recs'][$i][$fieldDated]), '</li>';
                    //echo '<li class="ct0">'.( $data['recs'][$i]['AMOUNT']==0 ? '<ct>-</ct>' : (int)$data['recs'][$i]['AMOUNT'].' '.$data['recs'][$i]['CURRENCY_ID']).'</li>';
                    echo '<li class=ct0>', $data['recs'][$i][$fieldBalance], '</li>';
                    echo '<li class=ct0>'.($data['recs'][$i][$fieldCredit]==0 ? '<ct>-</ct>' : '+'.((int)$data['recs'][$i][$fieldCredit]).'&nbsp;<i class="icn icnsmall icn-coin"></i>'), '</li>';
                    echo '<li class=ct0>'.($data['recs'][$i][$fieldDebit]==0 ? '<ct>-</ct>':'-'.((int)$data['recs'][$i][$fieldDebit])), '</li>';
                        
                    $hasDownload = false;
                    if ($data['recs'][$i][$fieldCredit]>0 && $data['recs'][$i][$fieldCurrency]==='USD' && $data['recs'][$i][$fieldPlatform]==='PAYFORT') {
                        $signature = base64_encode(strtoupper(hash_hmac('sha1', $data['recs'][$i][$fieldTID], MOURJAN_KEY)));
                        $hasDownload = "<li class='dwt'><a href='{$this->downloadLinkPath}?tid={$data['recs'][$i][$fieldTID]}&signature={$signature}' target=_blank><i class='icn icnsmall icn-invoice'></i></a></li>";
                    }
                        
                    echo '<li class=et'.($hasDownload ? 1 : 0).'>';
                       
                    echo '<b>';
                    if ($data['recs'][$i][$fieldCredit]>0) {
                        echo ($data['recs'][$i][$fieldCurrency]==='MCU') ? $this->lang['collection_of'] : $this->lang['purchase_of'];
                        echo ' ';
                    }
                    echo ($data['recs'][$i][$fieldTitle]);
                    echo '</b>';
                    echo '</li>';
                        
                    if ($hasDownload) { echo $hasDownload; }
                    
                    if ($data['recs'][$i][$fieldDebit]>0) {
                        if ($data['recs'][$i][$fieldId] && $data['recs'][$i][$fieldDesc]=='NA' || $data['recs'][$i][$fieldDesc]=='') {  
                            echo '<li class="xt0 '.($this->router->language ? 'ar':'en' ).'">';                              
                            echo $this->lang['unavailable_balance_detail'];
                        }
                        else {
                            echo '<li class="xt0 '.($data['recs'][$i][$fieldRtl] ? 'ar':'en' ).'">';
                            switch ($data['recs'][$i][$fieldState]) {
                                case 7:
                                    echo '<a class=a7 href="/myads/#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_active'].'</a> ';
                                    break;
                                case 9:
                                    echo '<a class=a9 href="/myads/?sub=archive#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_archive'].'</a> ';
                                    break;
                                case 6:
                                case 8:
                                    echo '<span class=a8>'.$this->lang['st_deleted'].'</span> ';
                                    break;
                                case 1:
                                    echo '<a class=a1 href="/myads/?sub=pending#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_pending'].'</a> ';
                                    break;
                                case 0:
                                    echo '<a class=a0 href="/myads/?sub=drafts#'.$data['recs'][$i][$fieldId].'">'.$this->lang['st_draft'].'</a> ';
                                    break;
                            }
                            echo $data['recs'][$i][$fieldDesc];
                        }
                        echo '</li>';
                    }
                                               
                    echo '</ul>';
                }
                echo '</div>';
                echo '</div></div>';
            }
        }
            
        if (!$pass) {
            ?><div class="htf db"><?= $this->lang['no_statement_history'] ?><br /><br /><input onclick="document.location='/gold/<?= $lang=='ar' ? '':$lang.'/' ?>'" class=btn type=button value="<?= $this->lang['get_gold'] ?>" /></div><?php
        }
        
    }
    
}
