<?php

include_once '../../../config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

class Administration {
    
    private $ACTION = ''; 
    private $args=null,$db=null;
    
            
    function __construct($argv) {
        global $config;
        $this->args = $argv;
        $this->db = new DB($config);
        if(isset($argv[1]) && $argv[1]){
            $this->ACTION = $argv[1];
        }
        $this->_run();
    }
    
    function _run(){
        global $config;
        //include_once $config['dir'].'/core/model/User.php';
        //$USER = new User($this->db, $config, null, false);
        $start = time();
        $pass= true;
        switch($this->ACTION){
            case "unblock":
                if(isset($this->args[2]) && is_numeric($this->args[2])){
                    $userId = $this->args[2];
                    
                    $accounts = [
                        $userId => ['LVL'=>0]
                    ];
                    
                    echo "unblocking {$userId}..".PHP_EOL;
                    
                    $this->getUserAccounts($accounts);
                    $ids=  array_keys($accounts);
                    
                    $q='update web_users set lvl = 0 where id in ('.  implode(',', $ids).')';
                    
                    if($this->db->queryResultArray($q)){
                        echo "{$userId} unblocked successfully".PHP_EOL;
                    }else{
                        echo "system error: failed to unblock".PHP_EOL;
                    }
                    
                }else{
                    echo "unblock error: missing {web_user_id}".PHP_EOL;
                }
                break;
            case "yBlocked":
                if(isset($this->args[2]) && is_numeric($this->args[2])){
                    $userId = $this->args[2];
                    
                    $accounts = [
                        $userId => ['LVL'=>0]
                    ];
                    
                    echo "checking why account {$userId} was blocked..".PHP_EOL;
                    
                    $this->getUserAccounts($accounts);
                    
                    $this->displayUserAccounts($accounts);
                    
                }else{
                    echo "yBlocked error: missing {web_user_id}".PHP_EOL;
                }
                break;
            default:
                $pass=false;
                echo "command not found".PHP_EOL;
                echo "available commands:".PHP_EOL;
                echo "1-\tyBlocked {web_user_id}".PHP_EOL;
                echo "2-\tunblock {web_user_id}".PHP_EOL;
                break;
        }
        if($pass){
            $time = time() - $start;
            echo "Runtime: {$time}ms".PHP_EOL;
        }
        echo "------------------------------------------------------".PHP_EOL;
    }
    
    function getUserAccounts(&$accounts){
        $ids = array_keys($accounts);        
        $accountCount = count($accounts);
        
        $q = 'select * from ad_user where web_user_id in (';
        $i=0;
        foreach ($ids as $id){
            if($i){
                $q .= ',';
            }
            $q .= $id;
        }
        $q.=')';
        
        $phones = [];
        $emails = [];
                    
        $ads = $this->db->queryResultArray($q);
        $this->_getContactInfo($ads, $phones, $emails);
        
        if(count($phones) || count($emails)){
                        
            $q = 'select distinct u.id,u.lvl,u.opts 
                from ad_attribute t
                left join ad_user a on a.id = t.ad_id
                left join web_users u on u.id = a.web_user_id
                where
                a.id is not null and (';
            $i=0;
            foreach ($phones as $num => $boolean){
                if($i){
                    $q .= ' or ';
                }
                $q .= "(t.attr_id = 1 and t.attr_value = '{$num}')";
                $i++;
            }
            foreach ($emails as $num => $boolean){
                if($i){
                    $q .= ' or ';
                }
                $q .= "(t.attr_id = 2 and t.attr_value = '{$num}')";
                $i++;
            }
            $q .= ')';

            $others = $this->db->queryResultArray($q);
            foreach ($others as $other){
                $accounts[$other['ID']] = [
                    'LVL'=>$other['LVL']
                ];
                $opts = json_decode($other['OPTS'],true);
                if($other['LVL']==5 && isset($opts['block'])){
                    $accounts[$other['ID']]['block']=$opts['block'];
                }
            }
        }
        
        if(count($accounts)!=$accountCount){
            $this->getUserAccounts($accounts);
        }
    }
    
    function displayUserAccounts($accounts){
        $q = 'select id, email, user_email from web_users where lvl = 9';
        $admins = $this->db->queryResultArray($q);
        $ids=[];
        $labels=[];
        foreach($admins as $admin){
            $ids[]='/\s'.$admin['ID'].'/';
            $labels[]=' '.(($admin['USER_EMAIL']!='')?$admin['USER_EMAIL']:$admin['EMAIL']);
        }
        $accounts=json_encode($accounts);        
        $accounts=preg_replace($ids, $labels, $accounts);
        $accounts=  json_decode($accounts,true);
        
        print_r($accounts);
    }
    
    function _getContactInfo($ads, &$phones, &$emails){        
        if($ads && count($ads)){
            foreach ($ads as $ad){
                $content = json_decode($ad['CONTENT'],true);
                if(!empty($content['cui']['p'])){
                    foreach ($content['cui']['p'] as $number){
                        $phones[$number['v']] = 1;
                    }
                }
            }
            if(!empty($content['cui']['e'])){
                $emails[$content['cui']['e']] = 1;
            }
        }
    }
}

$run = new Administration($argv);
