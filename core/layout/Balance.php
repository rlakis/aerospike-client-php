<?php
require_once 'Page.php';

class Balance extends Page{

    function __construct($router){
        parent::__construct($router);
        if($this->isMobile){
            if (!$this->user->info['id']) {
                $this->user->redirectTo($this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId));
            }
        }
        if($this->urlRouter->cfg['active_maintenance']){
            $this->user->redirectTo('/maintenance/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }
        $title = $this->lang['account_balance'];
        $this->forceNoIndex=true;
        $this->title=$title;
        $this->urlRouter->cfg['enabled_ads']=0;
        
        $this->inlineCss.='
            ct{text-align:center;display:block}
            .stmt{
                width:100%;DIRECTION:LTR;font-size:13px;
                min-height:300px
            }            
            .stmt ul{
                display:block;
                overflow:hidden;
                border-bottom:1px solid #999;
                border-right:1px solid #999;
            }
            .stmt.ar ul{border-right:0;border-left:1px solid #999}
            .stmt li{float:right;width:200px;padding:5px;height:30px;line-height:30px;border-left:1px solid #999}
            .stmt .hdr{
                background-color:teal;
                font-weight:bold;
                border:1px solid #999;
                border-left:0;
                color:#FFF
            }
            .stmt .dt0{width:180px;text-align:center}
            .stmt .et0{width:463px;text-align:left}
            .stmt .ct0{width:90px;text-align:center}
            .stmt .xt0{width:957px;border-top:1px solid #aaa;display:none}
            ul.ps{background-color:#D9FAC8}
            ul.a{background-color:#EFEFEF}
            ul.ck{cursor:pointer}
            ul.ck:hover{background-color:#ffffbf}
            ul.exp .xt0{display:block!important}
            
            .a0,.a1,.a7,.a8,.a9{
                display:inline-block;
                padding:0 5px;
                -webkit-border-radius:8px;
                -moz-border-radius:8px;
                -o-border-radius:8px;
                border-radius:8px;
                color:#FFF!important
            }
            .a0:hover,.a1:hover,.a7:hover,.a9:hover{
                background-color:#FF9000;
                color:#FFF!important
            }
            .a8{
                background-color:darkslategray
            }
            .a9{
                background-color:firebrick
            }
            .a7{
                background-color:darkseagreen;
            }
            .a1{
                background-color:goldenrod
            }
            .a0{
                background-color:slategray
            }
            
            .stmt.ar li{float:left;border-left:0;border-right:1px solid #999}
            .ar .et0{text-align:right;direction:rtl}
            .ar .hdr{font-size:16px}
        ';
        
        if($this->isMobile){
            $this->inlineCss.='.ph{background-color:#FFF;padding:15px 10px;border-bottom:1px solid #afafaf}.ph a{float:left}'
                    . '.htf.db{background-color:#FFF;text-align:center;padding:20px 10px;}'
                    . '.stmt .dt0{width:180px;float:right!important}
            .stmt .et0{float:right!important}
            .stmt .ct0{width:90px;text-align:center}
            .stmt .xt0{width:957px;border-top:1px solid #aaa;display:none}';
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

            switch($this->urlRouter->module){
                case 'statement':
                    $this->showMobileStatement();
                    break;
                default:
                    break;
            }

        }else{
            //$this->renderLoginPage();
        }
    }
    
    function main_pane(){
        if ($this->user->info['id']) {

            if (!$this->urlRouter->cfg['enabled_post'] && $this->topMenuIE) {
                $this->renderDisabledPage();
                return;
            }

            switch($this->urlRouter->module){
                case 'statement':
                    $this->showStatement();
                    break;
                default:
                    break;
            }

        }else{
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
            echo $subHeader.' ';
            ?><a class="buL" href="/gold/<?= $lang=='ar' ? '':$lang.'/' ?>"><span class="rj add"></span><?= $this->lang['get_gold'] ?></a><?php
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
                    echo '<ul class="hdr"><li class="dt0">'.$this->lang['date'].'</li>';
                    //echo '<li class="ct0">Amount</li>';
                    echo '<li class="ct0">'.$this->lang['balance'].'</li><li class="ct0">'.$this->lang['credit'].'</li><li class="ct0">'.$this->lang['debit'].'</li><li class="et0">'.(ucfirst($this->lang['detailMore_'.$lang])).'</li></ul>';
                    
                    $alt = 0;
                    for($i=0;$i<$count;$i++){
                        echo '<ul class="'.($data['recs'][$i][$fieldCredit] > 0 ? 'ps': ($alt ? 'ck a':'ck') ).'">';
                        
                        if($data['recs'][$i][$fieldCredit]==0){
                            $alt++;
                            if($alt > 1){
                                $alt = 0;
                            }
                        }
                        
                        echo '<li class="dt0">'.date('G:i T d/m/Y',$data['recs'][$i][$fieldDated]).'</li>';
                        //echo '<li class="ct0">'.( $data['recs'][$i]['AMOUNT']==0 ? '<ct>-</ct>' : (int)$data['recs'][$i]['AMOUNT'].' '.$data['recs'][$i]['CURRENCY_ID']).'</li>';
                        echo '<li class="ct0">'.$data['recs'][$i][$fieldBalance].'</li>';
                        echo '<li class="ct0">'.($data['recs'][$i][$fieldCredit] == 0 ? '<ct>-</ct>' : '+'.((int)$data['recs'][$i][$fieldCredit]).'<span class="mc24"></span>').'</li>';
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
    
    function showStatement(){ 
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
            echo $subHeader.' ';
            ?><a class="buL" href="/gold/<?= $lang=='ar' ? '':$lang.'/' ?>"><span class="rj add"></span><?= $this->lang['get_gold'] ?></a><?php
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
                    echo '<ul class="hdr"><li class="dt0">'.$this->lang['date'].'</li>';
                    //echo '<li class="ct0">Amount</li>';
                    echo '<li class="ct0">'.$this->lang['balance'].'</li><li class="ct0">'.$this->lang['credit'].'</li><li class="ct0">'.$this->lang['debit'].'</li><li class="et0">'.(ucfirst($this->lang['detailMore_'.$lang])).'</li></ul>';
                    
                    $alt = 0;
                    for($i=0;$i<$count;$i++){
                        echo '<ul class="'.($data['recs'][$i][$fieldCredit] > 0 ? 'ps': ($alt ? 'ck a':'ck') ).'">';
                        
                        if($data['recs'][$i][$fieldCredit]==0){
                            $alt++;
                            if($alt > 1){
                                $alt = 0;
                            }
                        }
                        
                        echo '<li class="dt0">'.date('G:i T d/m/Y',$data['recs'][$i][$fieldDated]).'</li>';
                        //echo '<li class="ct0">'.( $data['recs'][$i]['AMOUNT']==0 ? '<ct>-</ct>' : (int)$data['recs'][$i]['AMOUNT'].' '.$data['recs'][$i]['CURRENCY_ID']).'</li>';
                        echo '<li class="ct0">'.$data['recs'][$i][$fieldBalance].'</li>';
                        echo '<li class="ct0">'.($data['recs'][$i][$fieldCredit] == 0 ? '<ct>-</ct>' : '+'.((int)$data['recs'][$i][$fieldCredit]).'<span class="mc24"></span>').'</li>';
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
                        
                        /*if($data['recs'][$i][$fieldId]===null){
                            if(trim($data['recs'][$i]['CURRENCY_ID'])=='MCU'){
                                echo '<b>Redeem of '.((int)$data['recs'][$i]['CREDIT']).' Gold<span class="mc16"></span></b>';
                            }else{
                                echo '<b>Purchase of '.((int)$data['recs'][$i]['CREDIT']).' Gold<span class="mc16"></span></b>';
                            }
                        }else{
                            $content = json_decode($data['recs'][$i]['CONTENT'], true);
                            
                            $text = $content['other'];
                            $rtl = $content['rtl'];
                            if($lang!='' && $content['extra']['t']!=2 && isset($content['altother']) && $content['altother']!=''){
                                $text = $content['altother'];
                                $rtl = $content['altRtl'];
                            }
                            $text = $this->BuildExcerpts($text, 35);
                            $link='';
                            $class='';
                            switch($data['recs'][$i]['STATE']){
                                case 9:
                                    $link = '/myads/'.$lang.'?sub=archive#'.$data['recs'][$i]['ID'];
                                    break;
                                case 7:
                                    $class=' class="a"';
                                    $link = '/myads/'.$lang.'#'.$data['recs'][$i]['ID'];
                                    break;
                                case 3:
                                case 2:
                                case 1:
                                    $class=' class="b"';
                                    $link = '/myads/'.$lang.'?sub=pending#'.$data['recs'][$i]['ID'];
                                    break;
                                case 0:
                                default:
                                    $class=' class="r"';
                                    $link = '/myads/'.$lang.'?sub=drafts#'.$data['recs'][$i]['ID'];
                                    break;
                            }
                            $closeLink=0;
                            if($link){
                                $closeLink=1;
                                echo '<a'.$class.' href="'.$link.'">';
                            }
                            echo 'Premium listing: <span class="'.($rtl ? 'ar':'en').'">'.$text.'</span>';                     
                            if($closeLink){
                                echo '</a>';
                            }
                        }*/
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
    
}
?>
