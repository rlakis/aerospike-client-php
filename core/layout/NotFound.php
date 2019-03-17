<?php
require_once 'Page.php';

class NotFound extends Page{

    function __construct() {
        parent::__construct();
        $this->hasLeadingPane=true;
        $this->forceNoIndex=true;
        if ($this->router()->module=='invalid') {
            $this->lang['title_404']=$this->lang['title_invalid'];
            $this->lang['desc_404']=$this->lang['desc_invalid'];
        }
        elseif ($this->router()->module=='nonetwork') {
            $this->lang['title_404']=$this->lang['title_network'];
            $this->lang['desc_404']=$this->lang['desc_network'];
        }
        elseif ($this->router()->module=='maintenance') {
            $this->lang['title_404']=$this->lang['title_site_maintenance'];
            $this->lang['desc_404']=$this->lang['desc_site_maintenance'];
        }
        $this->title=$this->lang['title_404'];
        $this->render();
    }

    
    function mainMobile(){
        echo '<p class="ctr">'.$this->lang['desc_404'].'</p>';
        echo '<p class="ctr"><span class="na"></span></p>';
    }
    
    function side_pane(){
        $this->renderSideRoots();
        //$this->renderSideLike();
    }

    function main_pane(){
        //$this->pageHeader();
        $this->pageBody();
    }

    function pageBody(){
        ?><div class='htf'><?= $this->lang['desc_404'] ?></div><?php
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
