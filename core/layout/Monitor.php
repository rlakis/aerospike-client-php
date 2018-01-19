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
                  'th{padding:10px 5px;color:#FFF;background-color:#143D55}'
                . 'td{padding:10px 5px;border-bottom:1px solid forestgreen}';
        
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
        $tasks=[];
        NoSQL::getInstance()->getConnection()->scan("users", "services", function ($record) use (&$tasks) {
            $tasks[$record['bins']['task'].$record['bins']['server_id']]=$record['bins'];
        });
        $keys = array_keys($tasks);
        asort($keys);
        
        ?><br /><br /><table dir="ltr" width="100%"><?php
        echo '<tr><th>Task</th><th>host/sid</th><th>datetime</th><th>status</th><th>success</th><th>failure</th><th>message</th><th>since</th></tr>';
        //NoSQL::getInstance()->getConnection()->scan("users", "services", function ($record) {
        foreach ($keys as $key) 
        {
            
            
            //$bins = $record['bins'];
            $bins = $tasks[$key];
            
            $since = $this->formatSinceDate($bins['last_completed']);
            $success = isset($bins['success']) ? $bins['success'] : '-';
            $failure = isset($bins['failure']) ? $bins['failure'] : '-';
            
            echo '<tr><td>', $bins['task'], '</td>';
            echo '<td class="ctr">', $bins['host'],'/', $bins['server_id'],'</td>';
            echo '<td class="ctr">', $bins['datetime'], '</td>';
            echo '<td class="ctr">', $bins['status'], '</td>';
            echo '<td align="right">', $success, '</td>';
            echo '<td align="right">', $failure, '</td>';
            echo '<td>', $bins['message'], '</td>';
            echo '<td class="ctr">', $since, '</td></tr>';
        }
        //});
        ?></table><?php
    }
    function formatSinceDate($seconds) {
        $stamp='';
        $seconds=time()-$seconds;
        if ($seconds<0) {
            return $stamp;
        }
        $days = floor($seconds/86400);
        if ($days) {
            $stamp=$days.'d';
        }else {
            $hours=floor($seconds/3600);
            if ($hours){
                    $stamp=$hours.'h';
            }else {
                $minutes=floor($seconds/60);
                if (!$minutes) $minutes=1;
                $stamp=$minutes.'m';
            }
        }
        return $stamp;
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
