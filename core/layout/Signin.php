<?php
require_once 'deps/autoload.php';
require_once 'Page.php';

class Signin extends Page
{

    function __construct($router)
    {
        parent::__construct($router);
       
        if ($this->user->info['id'])
        {
            $this->user->redirectTo($this->urlRouter->getURL($this->urlRouter->countryId, $this->urlRouter->cityId));
        }
        
        if ($this->isMobile) 
        {
            $this->inlineCss.='
            .str ul{margin:10px 20px}
            .str li{margin-bottom:10px}
            label{font-weight:bold}
            ';
        }
        
        $this->forceNoIndex=true;
        $this->title=$this->lang['title_sign_in'];
        $this->urlRouter->cfg['enabled_ads']=0;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->render();
    }


    function main_pane()
    {
        $this->renderLoginPage();
    }
    
    
    function mainMobile()
    {         
        $this->renderLoginPage();
    }
    
}
?>
