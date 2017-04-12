<?php
include $config['dir']. '/core/layout/Page.php';
class Doc extends Page{

    function __construct($router){
        header('Vary: User-Agent');
        parent::__construct($router); 
        if($this->urlRouter->module =='buy' || $this->urlRouter->module == 'buyu'){
            if($this->urlRouter->cfg['active_maintenance']){
                $this->user->redirectTo('/maintenance/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
            }
            $this->checkBlockedAccount();
            /*if(!$this->user->info['id']){
                $this->user->redirectTo('/signin/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
            }*/
        }
        if(!$this->isMobile) {
            if ($this->urlRouter->module=='about') { 
                $this->inlineCss.='span.ctr{display:block}.cb,.cb ul{list-style:none!important;margin:0 !important;overflow:hidden}.cb label{font-weight:bold}.cb .cb1{width:515px;float:left}.cb .cb2{width:200px;float:right;color:#666}.cb2 li{padding:0px 5px;background-color:#FFFFBF}h2 {display:block;margin-bottom:15px !important}.pf{font-size:12px;line-height:20px;margin-bottom:15px;overflow:hidden;width:260px;padding:5px;border:1px solid #CEDAF4;color:#333 !important}.pf img{float:left}.pf ul{float:left;margin:0 0 0 10px;list-style:none}.pf li{padding:2px 0}.pf p{font-style:italic;background-color:#ECECEC;padding:5px;clear:both;margin:0}.pf:hover {-moz-box-shadow:3px 3px 3px #999;-o-box-shadow:3px 3px 3px #999;-webkit-box-shadow:3px 3px 3px #999;box-shadow:3px 3px 3px #999}.pfa{float:left;margin-right:50px}.pfa:hover{text-decoration:none!important}';
            } 
            if ($this->urlRouter->module=='publication-prices'){
                if($this->urlRouter->siteLanguage=='ar') { 
                    $this->inlineCss.='.doc ul{list-style:none;margin:0 !important;overflow:hidden}.doc li{float:right;padding:5px;border-left:1px solid #CCC;border-bottom:1px solid #CCC}.doc li.h{font-weight:bold;background-color:#143D55 !important;color:#fff;font-size:13px;border-left:1px solid #fff}.h.v4{border-left:1px solid #CCC !important}li.v1,li.v2,li.v3,li.v4{border-top:1px solid #ccc;background-color:#143D55;color:#FFF}li.h.v1,li.h.v2,li.h.v3,li.h.v4{border-bottom:0}li.v1{width:247px;border-right:1px solid #ccc}li.v2{width:89px;text-align:center}li.v3{width:109px;text-align:center}li.v4{width:259px}ul a{color:#FFF}li.v10,li.v11,li.v12,li.v13,li.v14,li.v15,li.v16{text-align:center;width:85px;font-size:11.5px !important}li.h.v10,li.h.v11,li.h.v12,li.h.v13,li.h.v14,li.h.v15,li.h.v16{background-color:#3087B4 !important;border-top:0}li.v10{margin-right:50px;width:197px;text-align:right;border-right:1px solid #CCC}li.v11{width:70px}li.v12{width:74px}li.v15{width:121px}li.v15,li.v14{direction:ltr}li.h.v15,li.h.v14{direction:rtl}.h.v15{border-left:1px solid #CCC !important}li.br{width:100%;clear:both;border:0;height:25px;}li.v20{margin-right:50px;width:687px;border-right:1px solid #CCC;text-align:center;border-bottom:1px solid #369}.bv{background-color:#F8F8F8}';
                }else {
                    $this->inlineCss.='.doc ul{list-style:none;margin:0 !important;overflow:hidden}.doc li{float:left;padding:5px;border-right:1px solid #CCC;border-bottom:1px solid #CCC}.doc li.h{font-weight:bold;background-color:#143D55 !important;color:#fff;font-size:11px;border-right:1px solid #fff}.h.v4{border-right:1px solid #CCC !important}li.v1,li.v2,li.v3,li.v4{border-top:1px solid #ccc;background-color:#143D55;color:#FFF}li.h.v1,li.h.v2,li.h.v3,li.h.v4{border-bottom:0}li.v1{width:247px;border-left:1px solid #ccc}li.v2{width:89px;text-align:center}li.v3{width:109px;text-align:center}li.v4{width:259px}ul a{color:#FFF}li.v10,li.v11,li.v12,li.v13,li.v14,li.v15,li.v16{text-align:center;width:85px;font-size:11px !important}li.h.v10,li.h.v11,li.h.v12,li.h.v13,li.h.v14,li.h.v15,li.h.v16{background-color:#3087B4 !important;border-top:0}li.v10{margin-left:50px;width:197px;text-align:left;border-left:1px solid #CCC}li.v11{width:70px}li.v12{width:84px}li.v14{width:75px}li.v15{width:121px}.h.v15{border-right:1px solid #CCC !important}li.br{width:100%;clear:both;border:0;height:25px;}li.v20{margin-left:50px;width:687px;border-left:1px solid #CCC;text-align:center;border-bottom:1px solid #369}.bv{background-color:#F8F8F8}'; 
                }
            }

            if($this->urlRouter->module=='premium'){
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

            if($this->urlRouter->module=='buy' || $this->urlRouter->module=='buyu'){
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

            if($this->urlRouter->module=='gold'){
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
                    .ick{vertical-align:middle}
                    hr{display: block; height: 1px;border: 0; border-top: 1px solid powderblue;margin:20px 0}
                    .credits{width:338px;height:110px;margin-bottom:20px;display:inline-block;background:url('.$this->urlRouter->cfg['url_css'].'/i/creditcards.jpg) no-repeat center}                
                ';            
            }

            if($this->urlRouter->module=='guide' || $this->urlRouter->module=='iguide'){
                $this->inlineCss.='
                    .uld{list-style:none!important;margin:0!important;}
                    .uld > li{padding:5px;border-bottom:1px solid #CCC;padding-top:30px}
                    .uld > li:last-child{border:0}
                    .alt{background-color:#ececec;}
                    .uld > li > ul{list-style:none;overflow:hidden;margin:0!important}
                    .uld > li > ul > li{float:left;width:210px;text-align:center;margin-bottom:30px}
                    .uld > li > ul > li:first-child{margin-top:30px;margin-right:10px;width:50px;height:50px;font-size:40px;color:#FFF;line-height:46px;text-shadow: 2px 2px #666;background:url('.$this->urlRouter->cfg['url_img'].'/presentation2/radio.jpg'.') no-repeat top left}  
                    .uld > li > ul > li.t{margin-top:30px;width:250px;margin-right:10px;text-align:left}
                    .tld{list-style-type:decimal!important;margin:15px 0 10px 30px!important}
                    .tld li{width:100%;height:auto;margin-bottom:15px}
                    .seic{vertical-align:middle}
                    .tlong{height:500px}
                    .itlong{height:900px}
                    .ar .uld > li > ul > li{float:right}
                    .ar .uld > li > ul > li:first-child{margin-right:0;margin-left:10px}
                    .ar .uld > li > ul > li.t{margin-right:0;margin-left:10px;text-align:right}
                    .ar .tld{margin:15px 30px 10px 0!important}
                    .ar{line-height:25px}
                ';            
            }
        }else{
            $this->inlineCss.='h2{margin-top:10px;display:inline-block}'
                    . '.doc{margin:10px;padding:5px;background-color:#FFF}'
                    . 'p{padding:10px 0;}';
            if($this->urlRouter->module=='gold'){
                
                $this->inlineCss.=
                        '.prices{padding-bottom:20px;list-style:none}'
                        . '.prices li{overflow:hidden;padding:0px 5px}'
                        . '.prices ul li{float:right;list-style:none;padding:5px 20px}'
                        . '.pad{text-align:right;padding:5px 10px;margin:15px 0 0}'
                        . '.alt{background-color:#ececec}'
                        . 'ul{list-style: disc inside}'
                        . '.credits {
    width: 300px;
    height: 110px;
    margin-bottom: 20px;
    display: inline-block;
    background: url('.$this->urlRouter->cfg['url_css'].'/i/creditcards.jpg) no-repeat center;
}';
            }
            
            if($this->urlRouter->module=='buy' || $this->urlRouter->module=='buyu'){
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
                    .table li{float:left;width:25%;height:60px;line-height:60px;white-space:nowrap;
                    border-bottom:1px solid seagreen;padding:0 10px}
                    .ar .table li{float:right;text-align:right}
                    .table input{vertical-align:middle;padding:0;border:0}
                    .tt{text-align:right!important}
                    .ar .tt{text-align:left!important}
                    .dialog-box{text-align:center}
                    .dialog .load{display:inline-block}
                ';    
            }
            
            
            if($this->urlRouter->module=='premium'){
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
                    li.clr li{float:left;text-align:center}
                    .ar li.clr li{float:right}
                    .vpdi{vertical-align:bottom}
                    .btH{text-align:center;margin-top:20px}
                    .bt{color:#FFF!important}
                    .bt:hover{text-decoration:none!important}
                    .ar{line-height:25px}
                        ';
            }
        }
        if($this->urlRouter->module=='iguide'){
            $this->forceNoIndex = true;
        }
        
            $this->hasLeadingPane=true;     
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->urlRouter->cfg['enabled_ads']=0;
        if ($this->urlRouter->module=='about') {
            $this->lang['title']    =   'About Mourjan.com';
            $this->lang['description']=  'Mourjan.com is an online classifieds search engine that helps you search and browse ads listed in major classifieds newspapers, websites and user submitted free ads';
        }elseif ($this->urlRouter->module=='publication-prices'){
            $this->load_lang(array('pricing'));
            $this->title=$this->lang['header'];
            $this->lang['description']=  $this->lang['desc'];
        }elseif($this->urlRouter->module=='advertise'){ 
            if ($this->urlRouter->siteLanguage=='ar') {
                $this->title='أعلن مع مرجان';
                $this->lang['description']='قم بتسويق شركتك، منتجاتك أو خدماتك بأسلوب متميز مستفيداً من أكثر من 3.5 مليون انطباع وأكثر من 250،000 زائر فريد شهريا على موقع مرجان';
            }else {
                $this->title='Advertise with Mourjan.com';
                $this->lang['description']=  'Market your online business with style and benefit from over 3.5 million impressions and over 250,000 unique visitors per month';
            }
        }elseif($this->urlRouter->module=='gold'){
                $this->title=$this->lang['gold_title'];
                $this->lang['description']= $this->lang['gold_desc'];
        }elseif($this->urlRouter->module=='buy' || $this->urlRouter->module == 'buyu'){
            $this->forceNoIndex=true;
                //$this->lang['description']= $this->lang['buy_desc'];
        }
        
        if(($this->urlRouter->module == 'buy' || $this->urlRouter->module == 'buyu') && $this->user->info['id']==0){
            $this->hasLeadingPane = false;
        }
        
        $this->render();
    }
    
    function header() {     
        
        if ($this->urlRouter->module=='advertise'){
            if ($this->urlRouter->siteLanguage=='ar') {
                ?><style type="text/css">
                    .doc ul{list-style:none;margin:0 !important;overflow:hidden}
                    .doc li{float:right;padding:5px;border-left:1px solid #CCC;border-bottom:1px solid #CCC}
                    .doc li.h{font-weight:bold;background-color:#143D55 !important;color:#fff;font-size:13px !important;border-left:1px solid #fff}
                    .h.v8{border-left:1px solid #CCC !important}
                    li.v1,li.v2,li.v3,li.v4,li.v5,li.v6,li.v7,li.v8{border-top:1px solid #ccc;background-color:#143D55;color:#FFF;border-bottom:0;text-align:center}
                    li.v1{width:200px;border-left:1px solid #ccc;text-align:right}
                    li.v2{width:55px}
                    li.v3{width:55px}
                    li.v4{width:55px}
                    li.v6{width:61px}
                    li.v7{width:61px}
                    li.v8{width:64px}
                    li.v5{width:110px}
                    li.v10,li.v11,li.v12,li.v13,li.v14,li.v15,li.v16,li.v17{background-color:#FFFF8F;text-align:center;width:85px;font-size:13px !important}
                    li.v10{width:200px;text-align:right !important;border-right:1px solid #CCC}
                    li.v11{width:55px}
                    li.v12{width:55px}
                    li.v13{width:55px}
                    li.v14{width:110px}
                    li.v15{width:61px;text-align:left}
                    li.v16{width:61px;text-align:left}
                    li.v17{width:64px;text-align:left}
                    li.br{width:100%;clear:both;border:0;height:25px;}
                    li.vd{background-color:#F8F8F8;width:738px;border-right:1px solid #CCC}
                    .vd img{width:200px;height:139px;border:1px solid #3087B4;margin:5px 0 5px 10px;float:right}
                    .doc .bt{float:none;display:inline-block;margin:30px 225px 20px;width:300px;text-align:center;font-size:14px;line-height:33px}
                    .doc .bt:hover{text-decoration:none}
                    .doc .bt:active{border:0;-moz-box-shadow:none;-o-box-shadow:none;-webkit-box-shadow:none;box-shadow:none}
                </style><?php 
            }else {
                ?><style type="text/css">
                    .doc ul{list-style:none;margin:0 !important;overflow:hidden}
                    .doc li{float:left;padding:5px;border-right:1px solid #CCC;border-bottom:1px solid #CCC}
                    .doc li.h{font-weight:bold;background-color:#143D55 !important;color:#fff;font-size:11px;border-right:1px solid #fff}
                    .h.v8{border-right:1px solid #CCC !important}
                    li.v1,li.v2,li.v3,li.v4,li.v5,li.v6,li.v7,li.v8{border-top:1px solid #ccc;background-color:#143D55;color:#FFF;border-bottom:0;text-align:center}
                    li.v1{width:200px;border-left:1px solid #ccc;text-align:left}
                    li.v2{width:55px}
                    li.v3{width:55px}
                    li.v4{width:55px}
                    li.v6{width:61px}
                    li.v7{width:61px}
                    li.v8{width:64px}
                    li.v5{width:110px}
                    li.v10,li.v11,li.v12,li.v13,li.v14,li.v15,li.v16,li.v17{background-color:#FFFF8F;text-align:center;width:85px;font-size:11px !important}
                    li.v10{width:200px;text-align:left;border-left:1px solid #CCC}
                    li.v11{width:55px}
                    li.v12{width:55px}
                    li.v13{width:55px}
                    li.v14{width:110px}
                    li.v15{width:61px;text-align:right}
                    li.v16{width:61px;text-align:right}
                    li.v17{width:64px;text-align:right}
                    li.br{width:100%;clear:both;border:0;height:25px;}
                    li.vd{background-color:#F8F8F8;width:738px;border-left:1px solid #CCC}
                    .vd img{width:200px;height:139px;border:1px solid #3087B4;margin:5px 10px 5px 0;float:left}
                    .doc .bt{float:none;display:inline-block;margin:30px 225px 20px;width:300px;text-align:center;font-size:14px;line-height:33px}
                    .doc .bt:hover{text-decoration:none}
                    .doc .bt:active{border:0;-moz-box-shadow:none;-o-box-shadow:none;-webkit-box-shadow:none;box-shadow:none}
                </style><?php 
            }
        }
        parent::header();
    }

    function side_pane(){
        if( ($this->urlRouter->module != 'buy' && $this->urlRouter->module != 'buyu') || (($this->urlRouter->module == 'buyu' || $this->urlRouter->module == 'buy') && $this->user->info['id'])){            
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

    
    private function payforButton($product) 
    {
        echo '<form method="post" onsubmit="buy('.$product[5].',this);" action="javascript:void(0);" name="payment">';
        
        echo "<input type=image name=submit border='0' src='https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif' alt='Visa/Mastercard'>";
        
        echo "</form>";
        
    }
    
    
    public function calculateSignature($arrData, $signType = 'request')
    {
        $shaString             = '';
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
    
    private function paypalButton($name, $price) 
    {
        $sandbox = $this->urlRouter->cfg['server_id']==99 ? true : false;
        $business = $sandbox ? 'nooralex-facilitator@gmail.com' : 'nooralex@gmail.com';
        $webscr = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $logo = $this->urlRouter->cfg['url_resources'] . '/img/mourjan-logo-120.png';
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
    
    
    function main_pane(){
        $adLang='';
        if ($this->urlRouter->siteLanguage!='ar') $adLang=$this->urlRouter->siteLanguage.'/';
        switch ($this->urlRouter->module){
            case 'buyu':
                if($this->user->info['id']==0){ 
                    echo '<div>';
                    $this->renderLoginPage();
                }else{
                    if ($this->urlRouter->siteLanguage=='ar') {
                        echo '<div class="doc ar">';
                    }else{
                        echo '<div class="doc en">';
                    }
                    
                    
                    if ( !$this->user->info['email']) {
                        $message = $this->lang['requireEmailPay'];
                        if ( (isset($this->user->info['options']['email']) && isset($this->user->info['options']['emailKey']) )) {
                            $message = preg_replace('/{email}/', $this->user->info['options']['email'], $this->lang['validateEmailPay']);                    
                        }
                        echo '<div class="htf">'.$message.'</div>';
                    }else{
                    
                    if(isset($_GET['payfort']) && $_GET['payfort']=='process'){
                        require_once $this->urlRouter->cfg['dir'].'/core/lib/PayfortIntegration.php';
                        
                        $payFort = new PayfortIntegration();
                        $payFort->setLanguage($this->urlRouter->siteLanguage);
                        $payment = $payFort->processResponse();
                        
                        $success = true;
                        $internalError = false;
                        if(isset($payment['error_msg'])){
                            $success = false;
                        }
                        
                        $orderId = 0;
                        if(isset($payment['merchant_reference'])){
                            $orderId = preg_split('/-/', $payment['merchant_reference']);
                            if($orderId && count($orderId)==2 && is_numeric($orderId[0]) && is_numeric($orderId[1])){
                                if($orderId[0] == $this->user->info['id']){
                                    $orderId = (int)$orderId[1];
                                }else{
                                    $orderId=0;
                                }
                            }else{
                                $orderId=0;
                            }
                        }
                        if($orderId){
                            if($success){
                                $res = $this->urlRouter->db->queryResultArray(
                                            "update t_order set state = ?, msg = ? where id = ? and uid = ? and state = 0 returning id",
                                            [2, $payment['fort_id'], $orderId, $this->user->info['id']], TRUE);
                                if($res !== false){
                                    $goldCount = preg_replace('/[^0-9]/', '', $payment['order_description']);
                                    $msg = preg_replace('/{gold}/', $goldCount, $this->lang['paypal_ok']);
                                    echo "<div class='mnb rc'><p><span class='done'></span> {$msg}</p></div>";
                                }else{
                                    $msg = preg_replace('/{payfort}/', $payment['fort_id'], $this->lang['payfort_fail']);
                                    echo "<div class='mnb rc'><p><span class='fail'></span> {$msg}</p></div>";
                                }
                                
                                $this->user->update();
                            }else{
                                $state = 3;
                                if( ($error_code = substr($payment['response_code'],-3))=="072"){
                                    $state = 1;
                                }
                                echo "<div class='mnb rc'><p><span class='fail'></span> {$this->lang['paypal_failure']} {$payment['error_msg']}</p></div>";

                                $res = $this->urlRouter->db->queryResultArray(
                                            "update t_order set state = ?, msg = ? where id = ? and uid = ? and state = 0 returning id",
                                            [$state, $payment['error_msg'], $orderId, $this->user->info['id']], TRUE);

                                $this->user->update();
                            }
                        }
                    }                    
                
                    $products = $this->urlRouter->db->queryCacheResultSimpleArray("products_payfort",
                                        "select product_id, name_ar, name_en, usd_price, mcu, id  
                                        from product 
                                        where iphone=0 
                                        and blocked=0 
                                        and usd_price > 3 
                                        order by mcu asc",
                                        null, 0, $this->urlRouter->cfg['ttl_long'], TRUE);
                    
                    echo '<ul class="table">';
                    $i=1;$j=0;
                    foreach($products as $product){
                        $alt = $i++%2;
                        $product[3] = number_format($product[3],2);
                        echo "<li>{$product[ $this->urlRouter->siteLanguage == 'ar' ? 1 : 2]}</li><li>{$product[3]} USD</li><li class='tt'>";
                        $this->payforButton($product);
                        echo "</li>";
                        $j++;
                    }
                    
                    echo '</ul>';
                    
                    ?><br /><br /><div class="bth ctr"><img width="288" height="60" src="<?= $this->urlRouter->cfg['url_css'] ?>/i/payfort.jpg" alt="Verified by PAYFORT"></div><br /><?php
                    
        $this->globalScript .= '
                var xhr;
                function buy(i,f){                    
                    f=$(f);
                    var r=f.attr("ready");
                    if(r=="true"){
                        return true;
                    }else{
                        try{
                            Dialog.show("paypro",null,function(){
                                if(xhr && xhr.readyState != 4){
                                    xhr.abort();
                                }
                            });
                            xhr = $.ajax({
                                type:"POST",
                                url:"/ajax-pay/",
                                data:{i:i,hl:lang},
                                dataType:"json",
                                success:function(rp){
                                    if (rp.RP) {
                                        f.attr("ready","true");
                                        f.attr("action",rp.DATA.U);
                                        f.prepend(rp.DATA.D);
                                        f.submit();
                                    }else {
                                        errDialog();
                                    }
                                },
                                error:function(){
                                    errDialog();
                                }
                            })
                        }catch(e){}
                        return false;
                    }
                };
                function errDialog(){
                    Dialog.show("alert_dialog",\''.$this->lang['payment_redirect_fail'].'\');
                };
                ';
        
        ?><div id="paypro" class="dialog"><?php
        ?><div class="dialog-box"><span class="loads load"></span><?= $this->lang['payment_redirect'] ?></div><?php 
            ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /></div><?php 
        ?></div><?php
        ?><div id="alert_dialog" class="dialog"><?php
                        ?><div class="dialog-box ctr"></div><?php 
                        ?><div class="dialog-action"><input type="button" value="<?= $this->lang['continue'] ?>" /></div><?php 
                    ?></div><?php
                    }
                }
                break;
            case 'buy':
                if($this->user->info['id']==0){ 
                    echo '<div>';
                    $this->renderLoginPage();
                }else{
                    if ($this->urlRouter->siteLanguage=='ar') {
                        echo '<div class="doc ar">';
                    }else{
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
                if ($this->urlRouter->siteLanguage=='ar') {
                    echo '<div class="doc ar">';
                    //echo '<div class="ctr" style="font-size:48px;line-height:48px;color:#999;margin-top:130px;">سيكون متوفر قريباً</div>';
                }else{
                    echo '<div class="doc en">';
                    //echo '<div class="ctr" style="font-size:48px;line-height:48px;color:#999;margin-top:130px;">Available Soon</div>';                
                }
                $imgPath = $this->urlRouter->cfg['url_img'].'/presentation2/';
                $this->lang['guide_apple'] = preg_replace(
                ['/{IMG0}/','/{IMG01}/','/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/','/{IMG7}/','/{IMG8}/'], 
                [
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'settings-icon.jpg" />',
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'home-icon.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-home'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-settings'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-account'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-activate'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-activated'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-balance'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-buy'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'iguide-coins'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                ], 
                $this->lang['guide_apple']);
                echo '<p>'.$this->lang['guide_apple_skip'].'</p>';
                echo '<ul class="uld">'.$this->lang['guide_apple'].'</ul>';
                break;
            case 'guide':
                if ($this->urlRouter->siteLanguage=='ar') {
                    echo '<div class="doc ar">';
                }else{
                    echo '<div class="doc en">';
                }
                $imgPath = $this->urlRouter->cfg['url_img'].'/presentation2/';
                $this->lang['guide_droid'] = preg_replace(
                ['/{IMG0}/','/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/','/{IMG7}/'], 
                [
                    '<img class="seic" width=24 height=24 src="'.$imgPath.'settings-icon.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-lang.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-country'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-home'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-settings'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-connect'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-connected'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                    '<img width=180 height=348 src="'.$imgPath.'guide-purchase'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" />',
                ], 
                $this->lang['guide_droid']);
                echo '<p>'.$this->lang['guide_droid_skip'].'</p>';
                echo '<ul class="uld">'.$this->lang['guide_droid'].'</ul>';
                break;
            case 'gold':
                if ($this->urlRouter->siteLanguage=='ar') {
                    echo '<div class="doc ar">';
                }else{
                    echo '<div class="doc en">';
                }
                $imgPath = $this->urlRouter->cfg['url_img'].'/presentation2/';
                echo "<h2>{$this->lang['gold_subtitle']}</h2><br />";
                echo "<p>{$this->lang['gold_p2']}</p>";
                echo "<p class='pad alt'>{$this->lang['gold_p2_1']}</p>";
                echo "<ul class='prices alt'>{$this->lang['gold_p2_2']}</ul>";
                echo "<p class='pad alt'>{$this->lang['gold_p2_3']}</p>";
                echo "<br /><p>{$this->lang['gold_p2_0']}</p>";
                /* ?><div class="btH"><a href="/buy/<?= $this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ?>" class="bt"><?= substr($this->lang['buy_gold'],0,-1)  ?></a></div><?php */
                echo "<hr /><h2 id='how-to'>{$this->lang['buy_gold']}</h2><br />";
                echo "<p>{$this->lang['gold_p2_5_0']}</p>";
                echo "<p>{$this->lang['gold_p2_5']}</p>";
                echo "<p>".$this->lang['gold_p2_6'.($this->isMobile ? '_m':'')]."</p>";
                ?><div class="btH"><a href="/buy/<?= $this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ?>"><img width="228" height="44" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-large.png" alt="Buy now with PayPal" /></a></div><br /><?php 
                ?><div class="btH"><a href="/buy/<?= $this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ?>"><img width="319" height="110" src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" alt="Buy now with PayPal" /></a></div><br /><?php 
                if(!$this->isMobile){
                    echo "<p>{$this->lang['gold_p2_4']}</p>";
                    echo '<ul class="alinks"><li><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="android"></span></a></li><li><a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="ios"></span></a></li></ul>';
                    echo "<br /><h2>{$this->lang['buy_gold_0']}</h2>";
                    echo "<p>{$this->lang['buy_gold_1']}</p>";
                    echo '<ul class="alinks"><li><a href="/guide/'.($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ).'"><img width=119 height=230 src="'.$imgPath.'guide'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" /></a></li><li><a href="/iguide/'.($this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ).'"><img width=119 height=230 src="'.$imgPath.'iguide'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.jpg" /></a></li></ul>';
                }
                break;
            case 'premium':
                if ($this->urlRouter->siteLanguage=='ar') {
                    echo '<div class="doc ar">';
                }else{
                    echo '<div class="doc en">';
                }
                echo "<h2>{$this->lang['gold_p1_title']}</h2>";
                ?><ul class="uln"><?php 
                        ?><li><?php
                        $imgPath = $this->urlRouter->cfg['url_img'].'/presentation2/';
                        $this->lang['gold_p1_desc'] = preg_replace(
                                ['/{IMG1}/','/{IMG2}/','/{IMG3}/','/{IMG4}/','/{IMG5}/','/{IMG6}/'], 
                                [
                                    '<img width=200 height=150 src="'.$imgPath.'desktop-premium.jpg" />',
                                    '<img width=74 height=150 src="'.$imgPath.'mobile-site-premium.jpg" />',
                                    '<img width=74 height=150 src="'.$imgPath.'app-premium.jpg" />',
                                    '<img width=300 height=228 src="'.$imgPath.'desktop-side-premium'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.png" />',
                                    '<img width=300 height=228 src="'.$imgPath.'desktop-side-hover-premium'.($this->urlRouter->siteLanguage=='ar'?'-ar':'').'.png" />',
                                    '<img width=74 height=150 src="'.$imgPath.'mobile-bottom-premium.jpg" />'
                                ], 
                                $this->lang['gold_p1_desc']);
                        
                        ?><ul class="uld"><?= $this->lang['gold_p1_desc'] ?></ul><?php
                        ?></li><?php 
                    ?></ul><?php 
                    ?><div class="btH"><a href="/gold/<?= $this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage.'/' ?>" class="bt"><?= $this->lang['back_to_gold']  ?></a></div><?php
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
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/large_rectangle_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/large_rectangle_ad.png' ?>" alt="large rectangle ad thumb" /></a>
                        <p>أفضل خانة لتسويق منتجك على الصفحة الأولى لموقع مرجان من خلال مربع كبير يعكس تميز شكل وأداء الموقع على منتجك</p>
                        <p><b>متوفر فقط على الصفحة الأولى لموقع مرجان</b></p>
                    </li>
                    
                    <li class="v10">
                        مربع متوسط أعلى الصفحة
                    </li><li class="v11">*300px</li><li class="v12">250px</li><li class="v13">9$</li><li class="v14">11$</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/top_medium_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/top_medium_ad.png' ?>" alt="top medium rectangle ad thumb" /></a>
                        <p>انطباعات أقل، فعالية أكثر. اعلانك لن يفوت في أهم خانة لعرض الإعلان</p>
                        <p><b>متوفر فقط على الصفحات الخاصة بتفاصيل الإعلانات</b></p>
                        <p><b>*هذه الخانة تتطلب تأمين حجمين من الإعلان 300px*250px و 250px*250px</b></p>
                    </li>
                    
                    <li class="v10">
                        راية
                    </li><li class="v11">728px</li><li class="v12">90px</li><li class="v13">7$</li><li class="v14">9$</li><li class="v15">5.6$/CPM</li><li class="v16">4.9$/CPM</li><li class="v17">4.2$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/leaderboard_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/leaderboard_ad.png' ?>" alt="leaderboard ad thumb" /></a>
                        <p>سوق منتجك في أعلى خانة على أكثر الصفحات مشاهدةً على موقع مرجان</p>
                        <p><b>متوفر فقط من ثاني صفحة وما فوق لصفحات عرض الإعلانات</b></p>
                    </li>
                    
                    
                    <li class="v10">
                        مربع متوسط
                    </li><li class="v11">300px</li><li class="v12">250px</li><li class="v13">6$</li><li class="v14">8$</li><li class="v15">4.8$/CPM</li><li class="v16">4.2$/CPM</li><li class="v17">3.6$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad.png' ?>" alt="medium rectangle ad thumb" /></a>
                        <p>موقع مميز مع عدد انطباعات يضمن تسويق منتجك بسرعة وفعالية</p>
                        <p><b>متوفر على كافة صفحات عرض الإعلانات</b></p>                        
                    </li>
                    
                    <li class="v10">
                        مربع صغير
                    </li><li class="v11">200px</li><li class="v12">200px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/CPM</li><li class="v16">2.8$/CPM</li><li class="v17">2.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad.png' ?>" alt="small square ad thumb" /></a>
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
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/large_rectangle_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/large_rectangle_ad.png' ?>" alt="large rectangle ad thumb" /></a>
                        <p>The best placement to brand your business on Mourjan.com's homepage with a viewable Large rectangle ad that will reflect Mourjan.com's powerful performance and style onto your business identity</p>
                        <p><b>Available only at the homepage of Mourjan.com</b></p>
                    </li>
                    
                    <li class="v10">Top Medium Rectangle</li><li class="v11">300px*</li><li class="v12">250px</li><li class="v13">9$</li><li class="v14">11$</li><li class="v15">7.2$/CPM</li><li class="v16">6.3$/CPM</li><li class="v17">5.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/top_medium_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/top_medium_ad.png' ?>" alt="top medium rectangle ad thumb" /></a>
                        <p>Fewer impressions, more clicks. The focus zone, your ad will not be missed in the most powerful ad zone at Mourjan.com</p>
                        <p><b>Available on all Mourjan.com's ad detail pages</b></p>
                        <p><b>*This ad zone serves 2 ad sizes: 300px*250px and 250px*250px</b></p>
                    </li>
                    
                    <li class="v10">Leaderboard</li><li class="v11">728px</li><li class="v12">90px</li><li class="v13">7$</li><li class="v14">9$</li><li class="v15">5.6$/CPM</li><li class="v16">4.9$/CPM</li><li class="v17">4.2$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/leaderboard_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/leaderboard_ad.png' ?>" alt="leaderboard ad thumb" /></a>
                        <p>Be on top of Mourjan.com's most viewed pages for maximum and yet powerful exposure for your business</p>
                        <p><b>Available only from the second page and up while browsing search result pages</b></p>
                    </li>
                    
                    
                    <li class="v10">Medium Rectangle</li><li class="v11">300px</li><li class="v12">250px</li><li class="v13">6$</li><li class="v14">8$</li><li class="v15">4.8$/CPM</li><li class="v16">4.2$/CPM</li><li class="v17">3.6$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad.png' ?>" alt="medium rectangle ad thumb" /></a>
                        <p>Perfect for maximum exposure while benefitting from thousands of search result pages in a catchy ad placement zone that guarantees exposure for your business</p>
                        <p><b>Available on all Mourjan.com's search result pages</b></p>                        
                    </li>
                    
                    <li class="v10">Small Square</li><li class="v11">200px</li><li class="v12">200px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/CPM</li><li class="v16">2.8$/CPM</li><li class="v17">2.4$/CPM</li>
                    <li class="vd"><a target="blank" href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad.png' ?>"><img width="200px" height="139px" src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad.png' ?>" alt="small square ad thumb" /></a>
                        <p>Deal of the day, the small square ad zone benefits a top placement on the side bar of search result pages for maximum exposure with an encouraging price</p>
                        <p><b>Available only from the second page and up while browsing search result pages</b></p>
                    </li>
                </ul>
                <a class="bt rc sh" href="/contact/<?= $this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/' ?>" rel="nofollow">Contact us for more information</a>
                <?php 
                }
                /*
                    
                    <li class="vd"><a href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/medium_rectangle_ad.png' ?>"><img src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/medium_rectangle_ad.png' ?>" alt="medium rectangle ad thumb" /></a><p></p></li>
                    <li class="v10">Mobile</li><li class="v11">300px</li><li class="v12">50px</li><li class="v13">4$</li><li class="v14">5$</li><li class="v15">3.2$/cpm</li><li class="v16">2.8$/cpm</li><li class="v17">2.4$/cpm</li>
                    <li class="vd"><a href="<?= $this->urlRouter->cfg['url_css'].'/ad/pic/small_square_ad.png' ?>"><img src="<?= $this->urlRouter->cfg['url_css'].'/ad/thumb/small_square_ad.png' ?>" alt="small square ad thumb" /></a><p></p></li>
                <?php */ break;
            case 'publication-prices':
                echo '<div class="doc '.$this->urlRouter->siteLanguage.'">';
                if ($this->urlRouter->siteLanguage=='ar'){
                    $pubLangs=array('عربي','إنجليزي','عربي/إنجليزي');
                    $pubPeriods=array('اسبوعي','مرتين في اسبوع','يومي','شهري');
                    $pubPublishPeriod=array(
                        array('اسبوع','اسبوعين','اسابيع','اسبوع'),
                        array('اسبوع','اسبوعين','اسابيع','اسبوع'),
                        array('يوم', 'يومين','ايام','يوم'),
                        array('شهر', 'شهرين','اشهر','شهر'));
                }else {
                    $pubLangs=array('Arabic','English','Arabic/English');
                    $pubPeriods=array('Weekly','Bi-Weekly','Daily','Monthly');
                    $pubPublishPeriod=array(
                        array('week','weeks'),
                        array('week','weeks'),
                        array('day', 'days'),
                        array('month', 'months'));
                }
                $publications = $this->urlRouter->db->queryCacheResultSimpleArray(
                'publications_pricelist_'.$this->urlRouter->siteLanguage, 
                'SELECT r.ID,p1.name_'.$this->urlRouter->siteLanguage.' as pname,
                    p1.BRAND_'.$this->urlRouter->siteLanguage.' as bname, p1.WEBSITE, p1.URL,
                    p1.country_id, p1.city_id, p1.language, p1.period,style.NAME_'.$this->urlRouter->siteLanguage.' as sname,
                    r.PUBLICATION_ID master_publication_id, s.PUBLICATION_ID, r.STYLE_ID, p.CURRENCY_ID, r.PRICE, r.BUNDLE_WORD_COUNT,
                    r.GRACE_WORD_COUNT, r.WORD_CHARGE, r.MAX_WORD_COUNT, p.name_'.$this->urlRouter->siteLanguage.' as ssname,s.insertions 
                  FROM STYLE_PRICELIST r
                  left join STYLE on style.ID=r.STYLE_ID
                  left join STYLE_SCHEDULE s on s.PRICELIST_ID=r.ID
                  left join publication p on p.ID=s.PUBLICATION_ID
                  left join publication p1 on p1.id=r.publication_id 
                  where r.BLOCKED=0 and p.name_'.$this->urlRouter->siteLanguage.' is not null order by p1.name_'.$this->urlRouter->siteLanguage.', style.name_'.$this->urlRouter->siteLanguage,
                null, 0, $this->urlRouter->cfg['ttl_long']);
                
                ?><h1><?= $this->lang['header'] ?></h1>
                <p><?= $this->lang['desc'] ?></p><br />
                <ul>
                    <li class="h v1"><?= $this->lang['label_pub'] ?></li><li class="h v2"><?= $this->lang['label_lang'] ?></li><li class="h v3"><?= $this->lang['label_period'] ?></li><li class="h v4"><?= $this->lang['label_loc'] ?></li>
                    <?php
                    $pubId=0;
                    $styleId=0;
                    $alt=1;
                    $altClass='';
                    foreach ($publications as $pub){
                        if ($pubId!=$pub[10]) {
                            if ($pubId) echo '<li class="br"></li>';
                            $styleId=0;
                            $pubId=$pub[10];
                            $alt=1;
                            ?><li class="v1"><a href="<?= $pub[4] ?>"><span class="cn c<?= $pub[5] ?>"></span><?= $pub[1] ?></a></li><li class="v2"><?= $pubLangs[$pub[7]] ?></li><li class="v3"><?= $pubPeriods[$pub[8]] ?></li><li class="v4"><?= $this->urlRouter->cities[$pub[6]][$this->fieldNameIndex].',  '.$this->urlRouter->countries[$pub[5]][$this->fieldNameIndex] ?></li><?php 
                            ?><li class="h v10"><?= $this->lang['label_style'] ?></li><li class="h v11"><?= $this->lang['label_words'] ?></li><li class="h v12"><?= $this->lang['label_grace_words'] ?></li><li class="h v13"><?= $this->lang['label_max_words'] ?></li><li class="h v14"><?= $this->lang['label_price'] ?></li><li class="h v15"><?= $this->lang['label_plus_word'] ?></li><?php
                            
                            
                        }
                        if ($pubId==$pub[10] && $styleId!=$pub[0]) {
                            $alt=++$alt%2;
                            if ($alt) $altClass=' bv';
                            else $altClass='';
                            ?><li class="v10<?= $altClass ?>"><?= $pub[9] ?></li><li class="v11<?= $altClass ?>"><?= $this->formatWord($pub[15]) ?></li><li class="v12<?= $altClass ?>"><?= $this->formatWord($pub[16]) ?></li><li class="v13<?= $altClass ?>"><?= $this->formatWord($pub[18]) ?></li><li class="v14<?= $altClass ?>"><?= $pub[14].' '. $pub[13]  ?></li><li class="v15<?= $altClass ?>"><?= ($pub[17]?$pub[17].' '.$pub[13]:'-') ?></li><?php
                            $styleId=$pub[0];
                        }
                        ?><li class="v20<?= $altClass ?>"><?= $this->lang['published'].' '.$this->formatPubPeriod($pub[20],$pub[8],$pubPublishPeriod).' '.$this->lang['in'].' '.$pub[19] ?></li><?php
                    }
                    ?></ul><br /><?php 
                break;
            case 'about':            
     if(!$this->isMobile){    
        echo '<div class="doc en">';
    ?> <h2>About Mourjan.com</h2><?php     
    }else {
        echo '<div class="str en">';
} ?>
<ul class="cb"><li class="cb1"><?php
       /* <p>Founded by <span itemscope itemtype="http://schema.org/LocalBusiness"><a itemprop="url" href="http://www.berysoft.com">Berysoft SARL</a>, <span itemprop="description">a press software solution provider located in Lebanon</span></span>, Mourjan.com came to bytes in 2008 as an additional free service provided by Berysoft to its affiliated classifieds publications to empower their online presence with a powerful Arabic search engine.</p><?php 
        ?><p>Mourjan.com came to bytes in 2008 as an additional free service provided by Berysoft to its affiliated classifieds publications to empower their online presence with a powerful Arabic search engine.</p><?php
?><p>Along two years, Mourjan.com proved itself to be a reliable search engine for those classifieds and nothing more was needed to be done. Until one day, Berysoft’s top minds were challenged by how fast the web is evolving and how fast websites are becoming.  Driven by curiosity and a passion to learn something new, the journey to deliver a world class fast website began in late 2011 and Mourjan.com seemed the right choice for this venture especially that it would not only satisfy the knowledge thirst but it would also deliver reliable and trusted ads for job, real estate and car seekers / hunters suffering from slow internet service providers and websites in the MENA region which in turn would expand the reach of Berysoft’s affiliated classifieds.</p>
<p>In mid 2012, Mourjan.com was faster than ever and in response to the overwhelming users’ feedbacks, the site enabled its users with free online ad posting in their countries of choice.  Currently, Berysoft continues to support, improve and develop new services on Mourjan.com.  Some services that we see to be helpful and other services that you might simply ask us for. <a href="/contact/<?= $adLang ?>">Let us know your opinion</a>.</p><?php */
        ?><p>In 2010, <span itemscope itemtype="http://schema.org/LocalBusiness">Mourjan.com</span> was founded.</p><?php 
        ?><p>With over 15 years of experience in the field of classifieds and IT solutions, we - the team behind Mourjan.com - were looking for a new venture and specifically in the fast evolving World Wide Web.</p><?php
?><p>While online classifieds was not something new and with many top of mind classifieds websites, we knew that in order to succeed we had to deliver something new. Therefore, we started working on Mourjan.com with a main concern of achieving a fast performing website with an Arabic oriented search engine which would deliver a pleasant experience for users who are seeking an apartment to rent or a car to buy.</p>
<p>In mid-2012, Mourjan.com was faster than ever and in response to the overwhelming users’ feedbacks, the site enabled its users with free online ad posting in their countries of choice while always adopting the latest techniques and trends in website development and having users’ best interest at heart.</p>
<p>Currently, we are still working on improving Mourjan.com and providing new services. Some services that we see to be helpful and other services that you might simply ask us for. <a href="/contact/<?= $adLang ?>">Let us know your opinion</a>.</p><?php 
    ?></li><li class="cb2" itemscope itemtype="http://schema.org/LocalBusiness"><?php
        /* <img itemprop="image" width="200px" height="54px" src="<?= $this->urlRouter->cfg['url_img'] ?>/berysoft.png" alt="Berysoft logo" /></a> */
        if(!$this->isMobile) { ?><img itemprop="image" width="130px" height="90px" src="<?= $this->urlRouter->cfg['url_css'] ?>/i/logo.jpg" alt="Berysoft logo" /></a><?php }
        ?><ul><li><p><b itemprop="name"><?php /* Berysoft SARL */ ?>Mourjan.com</b>, <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">Le Point Center, Fouad Shehab Street, Real Estate #273 Section B21</span>, <span itemprop="addressLocality">Dekwaneh</span>, <?php /*registered in */ ?><span itemprop="addressCountry">Lebanon</span></span><?php /* with number 2013375-Baabda */ ?></p></li><?php
            /* <li><p><label>Website:</label> <a itemprop="url" href="http://www.berysoft.com">www.berysoft.com</a></p></li> */ 
    ?><li><p><label>Phone:</label> <span itemprop="telephone">+961 148 7521</span></p></li>
    <li><p><label>Office hours:</label><br /><span class="ctr" itemprop="openingHours">Monday to Friday<br />7:00AM to 3:00PM GMT</span></p></li></ul>
</li></ul><?php /*
<h2>Join us on Google+</h2>
<a class="pfa" target="blank" href="https://plus.google.com/115595321578758914729"><div itemscope itemtype="http://schema.org/Person" class="pf"><img itemprop="image" height="80px" width="80px" src="https://plus.google.com/s2/photos/profile/115595321578758914729" /><ul>
    <li><b itemprop="name">Robert Lakis</b></li>
    <li itemprop="jobTitle">CEO of Berysoft</li>
    <li>Technical Project Manager</li>
    </ul><p class="ctr">“In Google we trust!”</p></div></a>
<a class="pfa" target="blank" href="https://plus.google.com/117418991820151780412"><div itemscope itemtype="http://schema.org/Person" class="pf"><img itemprop="image" height="80px" width="80px" src="https://plus.google.com/s2/photos/profile/117418991820151780412" /><ul>
    <li><b itemprop="name">Bassel Mourjan</b></li>
    <li itemprop="jobTitle">Project Manager</li>
    <li>Senior Web Developer</li>
    </ul><p class="ctr">“Keep a positive thinking!”</p></div></a>
*/ 
                break;
            case 'terms':             
 if(!$this->isMobile){    
        echo '<div class="doc en">';
    ?> <h2>Mourjan Terms of Use</h2><?php     
    }else {
        echo '<div class="str en">';
} ?>
<h2>Introduction</h2>
<p>Welcome to www.mourjan.com ("Mourjan"). By accessing Mourjan you are agreeing to the following terms, which are designed to make sure that Mourjan works for everyone. Mourjan is provided to you by Berysoft SARL, Le Point Center, Fouad Shehab Street, Dekwaneh, registered in Lebanon with number 2013375-Baabda. This policy is effective January 1st, 2012.</p>
<h2>Using Mourjan</h2>
<p>As a condition of your use of Mourjan you agree that you will not:</p>
<ul>
    <li><p>violate any laws;</p></li>
	<li><p>violate the Posting Rules;</p></li>
	<li><p>post any threatening, abusive, defamatory, obscene or indecent material;</p></li>
	<li><p>be false or misleading;</p></li>
	<li><p>infringe any third-party right;</p></li>
	<li><p>distribute or contain spam, chain letters, or pyramid schemes;</p></li>
	<li><p>distribute viruses or any other technologies that may harm Mourjan or the interests or property of Mourjan users;</p></li>
	<li><p>impose an unreasonable load on our infrastructure or interfere with the proper working of Mourjan;</p></li>
	<li><p>copy, modify, or distribute any other person's content without their consent;</p></li>
	<li><p>use any robot spider, scraper or other automated means to access Mourjan and collect content for any purpose without our express written permission;</p></li>
	<li><p>harvest or otherwise collect information about others, including email addresses, without their consent;</p></li>
	<li><p>bypass measures used to prevent or restrict access to Mourjan.</p></li>
</ul>
<p>You are solely responsible for all information that you submit to Mourjan and any consequences that may result from your post. We reserve the right at our discretion to refuse or delete content that we believe is inappropriate or breaching the above terms. We also reserve the right at our discretion to restrict a user's usage of the site either temporarily or permanently, or refuse a user's registration.</p>
<h2>Abusing Mourjan</h2>
<p>Mourjan and the Mourjan community work together to keep the site working properly and the community safe. Please report problems, offensive content and policy breaches to us using the reporting system.</p>
<p>Without limiting other remedies, we may issue warnings, limit or terminate our service, remove hosted content and take technical and legal steps to keep users off Mourjan if we think that they are creating problems or acting inconsistently with the letter or spirit of our policies. However, whether we decide to take any of these steps, remove hosted content or keep a user off Mourjan or not, we do not accept any liability for monitoring Mourjan or for unauthorized or unlawful content on Mourjan or use of Mourjan by users.</p>
<h2>Global Marketplace</h2>
<p>Some of Mourjan's features may display your ad on other sites such search engines or our classifieds sites in other countries. By using Mourjan, you agree that your ads can be displayed on these other sites. The terms for our other sites are similar to these terms, but you may be subject to additional laws or other restrictions in the countries where your ad is posted. When you choose to post your ad on another site, you may be responsible for ensuring that it does not violate our other site policies. We may remove your ad if it is reported on any our sites, or if we believe it causes problems or violates any law or policy.</p>
<h2>Fees and Services</h2>
<p>Using Mourjan is generally free, but we sometimes charge a fee for certain services. If the service you use incurs a fee, you'll be able to review and accept terms that will be clearly disclosed at the time you post your ad. Our fees are quoted in your local currency and/or US Dollar, and we may change them from time to time. We'll notify you of changes to our fee policy by posting such changes on the site. We may choose to temporarily change our fees for promotional events or new services; these changes are effective when we announce the promotional event or new service.</p>
<p>Our fees are non-refundable, and you are responsible for paying them when they're due. If you don't, we may limit your ability to use the services. If your payment method fails or your account is past due, we may collect fees owed using other collection mechanisms.</p>
<h2>Content</h2>
<p>Mourjan contains content from us, you, other users, and other classifieds publications affiliates. Mourjan is protected by copyright laws and international treaties. Content displayed on or via Mourjan is protected as a collective work and/or compilation, pursuant to copyrights laws and international conventions. You agree not to copy, distribute or modify content from Mourjan without our express written consent. You may not disassemble or decompile, reverse engineer or otherwise attempt to discover any source code contained in Mourjan. Without limiting the foregoing, you agree not to reproduce, copy, sell, resell, or exploit for any purposes any aspect of Mourjan (other than your own content). When you give us content, you are granting us and representing that you have the right to grant us, a non-exclusive, worldwide, perpetual, irrevocable, royalty-free, sub-licensable right to exercise the copyright, publicity, and database rights to that content.</p>
<h2>Infringement</h2>
<p>Do not post content that infringes the rights of third parties, This includes, but is not limited to, content that infringes on intellectual property rights such as copyright and trademark (e.g. offering counterfeit items for sale). A large number of very varied products are offered on Mourjan by private individuals. Entitled parties, in particular the owners of copyright, trademark rights or other rights owned by third parties can report any offers which many infringe on their rights, and submit a request for this offer to be removed. If a legal representative of the entitled party reports this to us in the correct manner, products infringing on the intellectual property rights will be removed by Mourjan.</p>
<h2>Liability</h2>
<p>Nothing in these terms shall limit our liability for fraudulent misrepresentation, for death or personal injury resulting from our negligence or the negligence of our agents or employees. You agree not to hold us responsible for things other users post or do.</p>
<p>We do not review users' postings and are not involved in the actual transactions between users. As most of the content on Mourjan comes from other users, we do not guarantee the accuracy of postings or user communications or the quality, safety, or legality of what's offered.</p>
<p>In no event do we accept liability of any description for the posting of any unlawful, threatening, abusive, defamatory, obscene or indecent information, or material of any kind which violates or infringes upon the rights of any other person, including without limitation any transmissions constituting or encouraging conduct that would constitute a criminal offence, give rise to civil liability or otherwise violate any applicable law.</p>
<p>We cannot guarantee continuous, error-free or secure access to our services or that defects in the service will be corrected. While we will use reasonable efforts to maintain an uninterrupted service, we cannot guarantee this and we do not give any promises or warranties (whether express or implied) about the availability of our services.</p>
<p>Accordingly, to the extent legally permitted we expressly disclaim all warranties, representations and conditions, express or implied, including those of quality, merchantability, merchantable quality, durability, fitness for a particular purpose and those arising by statute. We are not liable for any loss, whether of money (including profit), goodwill, or reputation, or any special, indirect, or consequential damages arising out of your use of Mourjan, even if you advise us or we could reasonably foresee the possibility of any such damage occurring. Some jurisdictions do not allow the disclaimer of warranties or exclusion of damages, so such disclaimers and exclusions may not apply to you. Despite the previous paragraph, if we are found to be liable, our liability to you or any third party (whether in contract, tort, negligence, strict liability in tort, by statute or otherwise) is limited to the greater of (a) the total fees you pay to us in the 12 months prior to the action giving rise to liability, and (b) 100 US Dollar.</p>
Personal Information
<p>By using Mourjan, you agree to the collection, transfer, storage and use of your personal information by Mourjan on servers located in the Germany, and Lebanon as further described in our Privacy Policy. You also agree to receive marketing communications from us unless you tell us that you prefer not receive such communications.</p>
<h2>Resolution of disputes</h2>
<p>If a dispute arises between you and Mourjan, we strongly encourage you to first contact us directly to seek a resolution by going to the Mourjan contact page. We will consider reasonable requests to resolve the dispute through alternative dispute resolution procedures, such as mediation or arbitration, as alternatives to litigation.</p>
General
<p>These terms and the other policies posted on Mourjan constitute the entire agreement between Mourjan and you, superseding any prior agreements.</p>
<p>This Agreement shall be governed and construed in all respects by the laws of Lebanon. You agree that any claim or dispute you may have against Berysoft SARL must be resolved by the courts of Lebanon. You and Mourjan both agree to submit to the exclusive jurisdiction of the Lebanese Courts.</p>
<p>If we don't enforce any particular provision, we are not waiving our right to do so later. If a court strikes down any of these terms, the remaining terms will survive. We may automatically assign this agreement in our sole discretion in accordance with the notice provision below.</p>
<p>Except for notices relating to illegal or infringing content, your notices to us must be sent by registered mail to Berysoft SARL, Le Point Center, Fouad Shehab Street, Dekwaneh, registered in Lebanon with number 2013375-Baabda. We will send notices to you via the email address you provide, or by registered mail. Notices sent by registered mail will be deemed received five days following the date of mailing.</p>
<p>We may update this agreement at any time, with updates taking effect when you next post or 30 days after we post the updated policy on the site, whichever is sooner. No other amendment to this agreement will be effective unless made in writing, signed by users and by us.</p>

<?php
                break;
            case 'privacy':                
 if(!$this->isMobile){    
        echo '<div class="doc en">';
    ?><h2>Privacy policy</h2><?php     
    }else {
        echo '<div class="str en">';
        ?><h2>Privacy policy</h2><?php
} ?>

<p>This privacy policy describes how we handle your personal information. We collect, use, and share personal information to help the Mourjan website ('Mourjan') work and to keep it safe (details below). In formal terms, Berysoft SARL, Le Point Center, Fouad Shehab Street, Dekwaneh, registered in Lebanon with number 2013375-Baabda, acting itself and through its subsidiaries, is the 'data controller' of your personal information. This policy is effective 1 Jan 2012.</p>
<h2>Collection</h2>
<p>Information posted on Mourjan is obviously publicly available. Our servers are located in Germany and Lebanon. Mourjan will hold and transmit your information in a safe, confidential and secure environment. If you choose to provide us with personal information, you are consenting to the transfer and storage of that information on our servers in Germany and Lebanon. We collect and store the following personal information:</p>
<ul>
    <li><p>email address, physical contact information, and (depending on the service used) sometimes financial information;</p></li>
    <li><p>computer sign-on data, statistics on page views, traffic to and from Mourjan and ad data (all through cookies - you can take steps to disable the cookies on your browser although this is likely to affect your ability to use the site);</p></li>
    <li><p>other information, including users IP address and standard web log information.</p></li>
    <li><p>Google Analytics data such as age, gender and interests based on Display Advertising (e.g., Remarketing, Google Display Network Impression Reporting, the DoubleClick Campaign Manager integration, or Google Analytics Demographics and Interest Reporting).</p></li>
    <li><p>Visitors can opt-out of Google Analytics for Display Advertising and customize Google Display Network ads using the <a href='https://www.google.com/settings/ads'>Ads Settings</a> or by downloading and installing <a href='https://tools.google.com/dlpage/gaoptout/'>Google Analytics opt-out browser add-on</a>.</p></li>
</ul>
<h2>Use</h2>
<p>We use users' personal and collected information to:</p>
<ul>
    <li><p>provide our services;</p></li>
    <li><p>resolve disputes and troubleshoot problems;</p></li>
    <li><p>encourage safe trading and enforce our policies;</p></li>
    <li><p>customize users' experience, measure interest in our services, and inform users about services and updates;</p></li>
    <li><p>communicate marketing and promotional offers to you;</p></li>
    <li><p>do other things for users as described when we collect the information.</p></li>
</ul>
<h2>Disclosure</h2>
<p>We don't sell or rent your personal information to third parties for their marketing purposes without your explicit consent. We may disclose personal information to respond to legal requirements, enforce our policies, respond to claims that a posting or other content violates others' rights, or protect anyone's rights, property, or safety (for example, if you submit false contact details or impersonate another person, we may pass your personal information to any aggrieved third party, their agent or to any law enforcement agency). We may also share personal information with service providers who help with our business operations.</p>
<h2>Cookies</h2>
<ul>
    <li><p>Our website uses cookies, web beacons, and third-parties to provide you with services that support your buying and selling activities within our online marketplace. To protect your privacy, use of these tools is limited.</p></li>
    <li><p>Mourjan and third-party vendors, including Google, use first-party cookies (such as the Google Analytics cookies) and third-party cookies (such as the DoubleClick cookie) together to report how Mourjan's ad impressions, other uses of ad services, and interactions with these ad impressions and ad services are related to visits to Mourjan.</p></li>
</ul>
<h2>About cookies</h2>
<p>Cookies are small files placed on the hard drive of your computer. Mourjan uses both persistent/permanent and session cookies to provide services to you and help ensure account security. Most cookies are 'session cookies', meaning that they are automatically deleted from your hard drive once you end your session (log out or close your browser).</p>
<h2>Mourjan uses cookies on certain pages of the website to:</h2>
<ul>
    <li><p>Enable you to enter your password less frequently during a session.</p></li>
    <li><p>Provide information that is targeted to your interests.</p></li>
    <li><p>Promote and enforce trust and safety.</p></li>
    <li><p>Offer certain features that are only available through the use of cookies.</p></li>
    <li><p>Measure promotional effectiveness.</p></li>
    <li><p>Analyse our site traffic.</p></li>
</ul>
<h2>Cookies we use for trust and safety</h2>
<p>Mourjan uses cookies, to help ensure that your account security is not compromised and to spot irregularities in behaviour to prevent your account from being fraudulently taken over.</p>
<h2>Your choices about cookies</h2>
<p>We offer certain features that are only available through the use of a cookie. You're always free to decline cookies if your browser permits. However, if you decline cookies, you may not be able to use certain features on the website, and you may be required to re-enter your password more frequently during a session.</p>
<h2>Web beacons</h2>
<p>A web beacon is an electronic image, called a single-pixel (1x1) or clear GIF placed in the web page code. Web beacons serve many of the same purposes as cookies. In addition, web beacons are used to track the traffic patterns of users from one page to another in order to maximise web traffic flow.</p>
<h2>Use of cookies and web beacons by third parties</h2>
<p>We may work with other companies who place cookies or web beacons on our websites. These service providers help operate our websites, by for example compiling anonymous site metrics and analytics. We require these companies to use the information they collect only to provide us with these services under contract, and not for their own purposes.</p>
<p>We don't permit third-party content on Mourjan (such as item listings) to include cookies or web beacons. If you believe a listing might be collecting personal information or using cookies, please report it to support@berysoft.com.</p>
<h2>Using Information from Mourjan</h2>
<p>You may use personal information gathered from Mourjan only to follow up with another user about a specific posting, not to send spam or collect personal information from someone who hasn't agreed to that.</p>
<h2>Marketing</h2>
<p>If you do not wish to receive marketing communications from us, you can simply email us at any time.</p>
<h2>Remarketing with Google Analytics</h2>
<ul>
    <li><p>Mourjan uses Remarketing with Google Analytics to advertise online.</p></li>
    <li><p>Third-party vendors, including Google, show Mourjan ads on sites across the Internet.</p></li>
    <li><p>Mourjan and third-party vendors, including Google, use first-party cookies (such as the Google Analytics cookie) and third-party cookies (such as the DoubleClick cookie) together to inform, optimize, and serve ads based on someone's past visits to Mourjan.</p></li>
</ul>
<h2>Security</h2>
<p>We use many tools to protect your personal information against unauthorized access and disclosure, but as you probably know, nothing's perfect, so we make no guarantees.</p>
<h2>General</h2>
<p>We may update this policy at any time, with updates taking effect when you next post or after 30 days, whichever is sooner. If we or our corporate affiliates are involved in a merger or acquisition, we may share personal information with another company, and this other company shall be entitled to share your personal information with other companies but at all times otherwise respecting your personal information in accordance with this policy.</p>
<p><b>Privacy Policy updated 4 Oct 2013</b></p><?php
                break;
            default:
                break;
        }
        echo "</div>";
    }
    
}
?>
