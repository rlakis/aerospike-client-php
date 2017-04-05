<?php
require_once 'Page.php';

class Blocked extends Page
{    

    function __construct($router)
    {
        parent::__construct($router);
        $this->forceNoIndex=true;
        $this->title=$this->lang['title_blocked'];
        $this->urlRouter->cfg['enabled_ads']=0;
        
        if($this->isMobile)
        {
            $this->inlineCss.='.nost{list-style:none!important;margin:0!important}.nost .ctr{padding-top:20px}';
        }
        else
        {
            $this->inlineCss.='.hbn{padding-top:30px;}.hbn p{width:630px;float:right;margin:0 10px 20px;}.hbn ul{float:right;width:580px;list-style:disc inside;padding:10px 30px;margin:0 10px;line-height:30px;background-color:#ececec;}.hbn a{color:#00e}.hbn a:hover{text-decoration:underline}.hbn .om{width:250px;height:330px;margin:0 30px;display:inline-block;}.nost{list-style:none!important}.nost .ctr{padding-top:20px;}.nost a{color:#fff;text-decoration:none!important}';
        }
        
        if($this->urlRouter->module=='held')
        {
            $this->title=$this->lang['title_held'];
            $hours = '24';
            if ($this->user->getProfile()->isSuspended()) 
            {
                $time = $this->user->getProfile()->getOptions()->getSuspensionTime()-time(); 
                if($time>0)
                {
                    $hours = $time / 3600;
                    if(ceil($hours)>1)
                    {
                        $hours = ceil($hours);
                        if($this->urlRouter->siteLanguage=='ar')
                        {
                            if($hours==2)
                            {
                                $hours='ساعتين';
                            }
                            elseif($hours>2 && $hours<11)
                            {
                                $hours=$hours.' ساعات';
                            }
                            else
                            {
                                $hours = $hours.' ساعة';
                            }
                        }
                        else
                        {
                            $hours = $hours.' hours';
                        }
                    }
                    else
                    {
                        $hours = ceil($time / 60);
                        if($this->urlRouter->siteLanguage=='ar'){
                            if($hours==1){
                                $hours='دقيقة';
                            }elseif($hours==2){
                                $hours='دقيقتين';
                            }elseif($hours>2 && $hours<11){
                                $hours=$hours.' دقائق';
                            }else{
                                $hours = $hours.' دقيقة';
                            }
                        }else{
                            if($hours>1){                                
                                $hours = $hours.' minutes';
                            }else{                                
                                $hours = $hours.' minute';
                            }
                        }
                    }
                    
                    $this->lang['desc_held_reasons']=  preg_replace('/{hours}/', $hours, $this->lang['desc_held_reasons']);
                    $this->lang['desc_held']=  preg_replace('/{hours}/', $hours, $this->lang['desc_held']);
                }else{
                    $this->user->redirectTo('/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
                }
            }else{
                $this->user->redirectTo('/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
            }
        }
        $this->render();
    }

    function mainMobile(){
        echo '<div class="str"><img class="ina" height="168px" width="100px" src="'.$this->urlRouter->cfg['url_css_mobile'].'/i/na.jpg" />'.($this->urlRouter->module=='held'? $this->lang['desc_held'].preg_replace('/rc sh/','',$this->lang['desc_held_reasons']) : ($this->urlRouter->module=='suspended' ? $this->lang['desc_suspended'].preg_replace('/rc sh/','',$this->lang['desc_suspended_reasons']) : $this->lang['desc_blocked'].preg_replace('/rc sh/','',$this->lang['desc_blocked_reasons']))).'</div>';
    }
    
    function side_pane(){
        //$this->renderSideUserPanel();
        $this->renderSideRoots();
        //$this->renderSideLike();
    }

    function main_pane(){
        //$this->pageHeader();
        $this->pageBody();
    }

    function pageBody(){
        ?><div class="hbn"><span class="om fl"></span><?php
        if($this->urlRouter->module=='held'){
            ?><p><?= $this->lang['desc_held'] ?></p><?php
            echo $this->lang['desc_held_reasons'];
        }elseif($this->urlRouter->module=='suspended'){
            ?><p><?= $this->lang['desc_suspended'] ?></p><?php
            echo $this->lang['desc_suspended_reasons'];
        }else{
            ?><p><?= $this->lang['desc_blocked'] ?></p><?php
            echo $this->lang['desc_blocked_reasons'];
        }
        ?></div><?php
    }

    function pageHeader(){
        $countryId=0;
        $cityId=0;
        $countryName=$this->countryName;
        if ($this->user->params["country"]) {
            $countryId=$this->user->params["country"];
            $countryName=$this->urlRouter->countries[$this->user->params["country"]][$this->fieldNameIndex];
        }
        ?><div class='sum rc'><div class="brd"><?php
        echo "<a href='{$this->urlRouter->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1><?= $this->title ?></h1></div></div><?php
    }
    
}
?>
