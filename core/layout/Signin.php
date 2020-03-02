<?php
require_once 'deps/autoload.php';
require_once 'Page.php';


class Signin extends Page {

    function __construct() {
        parent::__construct();
        if ($this->user->id()) {
            $this->user->redirectTo( $this->router->getURL($this->router->countryId, $this->router->cityId) );
        }
                
        $this->forceNoIndex=true;
        $this->title=$this->lang['title_sign_in'];
        $this->router->config->disableAds();       
        $this->render();
    }


    function main_pane() {
        $this->renderLoginPage();
    }
        
}
