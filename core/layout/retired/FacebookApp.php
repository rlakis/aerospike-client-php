<?php
//include $config['dir'].'/core/layout/Page.php';
include $config['dir'].'/core/layout/Search.php';
include 'lib/fb/facebook.php';

class FacebookApp extends Search {
    private $facebook;
    private $fbUser;
    private $fbUserProfile;
    
    
    function FacebookApp($router) {        
        parent::Page($router);
        $this->signup();
        if ((isset($this->user->info['id']) && $this->user->info['id']>0) || $this->signup()) {        
            $this->lang['description']=$this->lang['home_description'];
            if (!$this->isMobile) {
                $this->set_ad(array(
                    'VerticalBanner'=>array('/1006833/LargeRectangle', 336, 280, 'div-gpt-ad-1319707248075-0'),
                    'Leaderboard'=>array('/1006833/Leaderboard', 728, 90, 'div-gpt-ad-1319709425426-0')));
            }
            $this->render();
        }     
    }

    
    function signup() {
        $this->facebook = new Facebook(array(
          'appId'  => '417396264941578',
          'secret' => '4a030ecd19f83435ffb2f912b89bb132',
        ));
        
        //$this->facebook->makeRequest("https://graph.facebook.com/apprequests", 
        //        array('ids'=>'rlakis','message'=>'mourjan notification','access_token'=>  $this->facebook->getAccessToken()));
        $apprequest_url ="https://graph.facebook.com/" .
            "/apprequests?ids=rlakis&message='INSERT_UT8_STRING_MSG'" . 
            "&data='INSERT_STRING_DATA'&access_token="  .   
            $this->facebook->getAccessToken() . "&method=post";

  $result = file_get_contents($apprequest_url);
  
        die("user");
        $this->fbUser = $this->facebook->getUser();

        if ($this->fbUser) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $this->fbUserProfile = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $this->fbUser = null;
            }
        }

        // Login or logout url will be needed depending on current user state.
        if ($this->fbUser) {
            $logoutUrl = $this->facebook->getLogoutUrl();
        } else {
            $loginUrl = $this->facebook->getLoginUrl(array('scope' => 'email'));
        }
        
        if ($this->fbUser) {
            //var_dump($this->fbUserProfile);
            
            //echo '<a href="', $logoutUrl, '">Logout</a>';
            $fbInfo = array(
                'identifier'=>"http://www.facebook.com/profile.php?id={$this->fbUserProfile['id']}",
                'providerName'=>'Facebook',
                'name'=>array('formatted'=>$this->fbUserProfile['name']),
                'displayName' => $this->fbUserProfile['name'],
                'url'=>  $this->fbUserProfile['link'],
                'email'=>''
                );
            $this->user->updateUserRecord($fbInfo);
        }else{
            echo '<div>Login using OAuth 2.0 handled by the PHP SDK:<a href="', $loginUrl, '">Login with Facebook</a></div>';
        }

        if (!$this->fbUser) { 
            echo '<strong><em>You are not Connected.</em></strong>';
            return false;
        } else return true;
    }
    
    function top(){
        $url='';
        $cityId=$this->urlRouter->cityId;
        if (count($this->urlRouter->countryCities)<2)$cityId=0;
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
                if ($this->userFavorites) $url='/favorites/';
                else $url=$this->urlRouter->getURL($this->urlRouter->countryId,$cityId,$this->urlRouter->rootId,$this->urlRouter->sectionId,$this->urlRouter->purposeId,false);

                if ($this->urlRouter->siteLanguage=='ar') $url.='en/';
                if ($this->urlRouter->params['start']) $url.=$this->urlRouter->params['start'].'/';
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
        $adLang='';
        if ($this->urlRouter->siteLanguage=='ar') {
            $link = '<a class="en" href="'.$url.'">English</a>';
        } else {
            $adLang=$this->urlRouter->siteLanguage.'/';
            $link = '<a class="ar" href="'.$url.'">عربي</a>';
        }
        
        $mobileLink='<form action="'.$this->urlRouter->getURL($this->urlRouter->countryId,$cityId).'" method="post"><input type="hidden" name="mobile" value="1" /><a onclick="this.parentNode.submit();">'.$this->lang['mobile'].'</a></form>';
        ?><div class='top'><?php
        if ($this->topMenuIE) {
            ?><table><tr><td width="auto"><a class="lg" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>">Mourjan.com</a></td><td width="80px"><span><?= $link ?></span></td><td width="<?= $this->urlRouter->siteLanguage=="ar"? '120px' :'130px' ?>"><span><?= $mobileLink ?></span></td><td width="<?= $this->urlRouter->siteLanguage=="ar"? '135px' :'80px' ?>"><span><?= $this->user->info['id']? "<a class='nt' href=''>{$this->lang['signout']}</a>":"<a class='janrainEngage nt' href='#'>{$this->lang['signin']}</a>" ?></span></td></tr></table><?php
        }else {
            ?><a class="lg" href="<?= $this->urlRouter->getURL($this->urlRouter->countryId,$cityId) ?>">Mourjan.com<?= $this->urlRouter->cfg['slogan'] ?></a>
            <span><?= $link ?></span>
            <span><?= $mobileLink ?></span>
            <span><?=
            ($this->urlRouter->module!='post' ? 
                    '<a class="janrainEngage nt" href="#"><p onclick="pi(\'/post/'.$adLang.'\')">'.$this->lang['button_ad_post'].'</p></a>' 
                    : '') 
                ?></span>
                <?php
        } ?></div><?php
    }
    
    
    function header(){
        ?><style type="text/css">
            body{width:760px;min-width:760px;margin:auto; padding:0px;}
            .w{width: auto;}.srch{width: auto;}.col2w{width: auto;}
            .top{background-color: white !important;background: none !important}
            .lg{color: #1D4088 !important; letter-spacing: 1.1px !important}
            .menu{margin-top: 4px !important}
        </style>
        <?php

        if ($this->lang['description']) { 
            ?><meta name="description" content="<?= preg_replace("/<.*?>/", "", $this->lang['description']) ?>" /><?php 
        } 
        if ($this->urlRouter->module!="detail") {
            ?><meta property="og:title" content="<?= $this->title ?>" /><meta property="og:description" content="<?= $this->lang['description'] ?>" /><meta property="og:type" content="website" /><meta property="og:url" content="http://www.mourjan.com" /><meta property="og:image" content="<?= $this->urlRouter->cfg["url_resources"]."/img/mourjan-icon.png" ?>" /><meta property="og:site_name" content="Mourjan.com" /><meta property="og:locale" content="<?= ($this->urlRouter->siteLanguage=="ar" ? "ar_LB" : "en_US" ) ?>"/><meta property="fb:app_id" content="184370954908428"/><?php
        }
    }

}

?>
