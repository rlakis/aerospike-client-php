<?php
require_once 'Page.php';

use Core\Model\DB;
use Core\Model\NoSQL;

class Monitor extends Page
{
    
    var $action='',$liOpen='';
    private $uid = 0;
    private $aid = 0;
    private $userdata = 0;
    
    function __construct($router)
    {
        parent::__construct($router);
        $this->uid = 0;
        $this->sub = $_GET['sub'] ?? '';
        $this->hasLeadingPane=false;
        
        if($this->isMobile || !$this->user->isSuperUser())
        {
            $this->user->redirectTo('/notfound/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }     
        
        $this->load_lang(array("account"));
        
        $this->inlineCss .= 
                '.ts .bt{width:auto;padding:5px 30px!important}'
                . '#cron{direction:ltr;margin-top:5px}#cron a{color:#00e;margin-right:30px}#cron a:hover{text-decoration:underline}'
                . '#statDv{width:760px}'
                . '.ts .lm{overflow:visible}'
                . '.ts label{vertical-align:middle}'
                . '.hy li{float:right;width:370px;border:0!important}'
                . '.hy label{margin-bottom:10px}'
                . 'textarea{width:300px;height:200px;padding:3px}'
                . '.action{width:800px!important;text-align:center}'
                . '.options{position:absolute;border:1px solid #aaa;border-bottom:0;width:306px;background-color:#FFF}'
                . '.options li{cursor:pointer;border-bottom:1px solid #aaa;direction:rtl;text-align:right;padding:10px;}'
                . '.options li:hover{background-color:#00e;color:#FFF}'
                . '#msg{height:40px;display:block}'
                . '.rpd{display:block}.rpd textarea{width:740px}'
                . '.tbs{width:750px}.tbs li{float:left;width:80px}'
                . '.load{width: 30px;height: 30px;display: inline-block;vertical-align: middle}'
                . '.filters{background-color:#ECECEC}.filters select{padding:2px 10px;margin:10px 20px}';
        
        $this->set_require('css', 'account');
        $this->title=$this->lang['title'];
        $this->description=$this->lang['description'];
        $this->forceNoIndex=true;
        $this->urlRouter->cfg['enabled_sharing']=0;
        $this->urlRouter->cfg['enabled_ads']=0;
        
        
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
        
        if($action)
        {
            $redirectWenDone=true;
            
            switch ($action)
            {
                    default:
                        break;
            }
            
            if($redirectWenDone){
                $url = "";
                unset($_GET['action']);
                
                foreach ($_GET as $key => $value){
                    if($url){
                        $url .= '&';
                    }
                    $url .= $key.'='.$value;
                }
                if($url) $url = '?'.$url;
                
                header('Location: '. $url);
            }
        }
        
        
        $this->render();
    }    
    
    public function getData()
    {
        ?><br /><br /><table dir="ltr" width="100%" padding="5px" margin="5px"><?php
        NoSQL::getInstance()->getConnection()->scan("users", "services", function ($record) {
            
            $bins = $record['bins'];
            
            $since = $this->formatSinceDate($bins['last_completed']);
            $success = isset($bins['success']) ? $bins['success'] : -1;
            $failure = isset($bins['failure']) ? $bins['failure'] : -1;
            
            echo "<tr><td>{$bins['task']}</td><td>server {$bins['server_id']}</td><td>{$since}</td></tr>";
            if($success > -1 || $failure > -1){
                echo "<tr><td></td><td>success {$success}</td><td>failures {$failure}</td></tr>";
            }
            echo "<tr><td colspan=3><br /></td></tr>";
        });
        ?></table><?php
    }
    /*
    function side_pane()
    {
        $this->renderSideAdmin();
        //$this->renderSideUserPanel();
    }
    
    function renderSideAdmin(){
        $sub = $this->sub;
        $lang=$this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/';
        ?><h4><?= $this->lang['myPanel'] ?></h4><?php
        echo '<ul class=\'sm\'>';
        //echo '<li><a href=\'', $this->urlRouter->getURL($countryId,$cityId), '\'>', $this->lang['homepage'], '</a></li>';

        if ($sub=='')
            echo '<li class=\'on\'><b>', $this->lang['label_users'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '\'>', $this->lang['label_users'], '</a></li>';
        
        if ($sub=='areas')
            echo '<li class=\'on\'><b>', $this->lang['label_areas'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=areas\'>', $this->lang['label_areas'], '</a></li>';
        if ($sub=='ads')
            echo '<li class=\'on\'><b>', $this->lang['label_ads_monitor'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=ads\'>', $this->lang['label_ads_monitor'], '</a></li>';
       echo "</ul><br />";
    }*/
    
    
    function mainMobile()
    {
    }
    
    
    function main_pane()
    {
        $language = 'en';
        
        switch ($this->sub)
        {
                default:
                    //html goes here
                    $this->getData();
                    break;
        }
    }
    

    
}
?>
