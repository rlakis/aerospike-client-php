<?php
require_once 'deps/autoload.php';
require_once 'Page.php';


class Signin extends Page {

    function __construct(Core\Model\Router $router) {
        parent::__construct($router);
        if ($this->user()->id()) {
            $this->user()->redirectTo( $this->router()->getURL($this->router()->countryId, $this->router()->cityId) );
        }
                
        $this->forceNoIndex=true;
        $this->title=$this->lang['title_sign_in'];
        $this->router()->config()->disableAds();
        //$this->urlRouter->cfg['enabled_sharing']=0;
        $this->render();
    }


    function main_pane() {
        $this->renderLoginPage();
    }
        
}
