<?php
include_once get_cfg_var("mourjan.path") .'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';
include_once $config['dir'].'/core/model/NoSQL.php';

class Administration 
{
    
    private $ACTION = ''; 
    private $args=null,$db=null;
    
            
    function __construct($argv)
    {
        global $config;
        $this->args = $argv;
        $this->db = new \Core\Model\DB($config);
        if(isset($argv[1]) && $argv[1])
        {
            $this->ACTION = $argv[1];
        }
        $this->_run();
    }

    
    function _run()
    {
        global $config;
        //include_once $config['dir'].'/core/model/User.php';
        //$USER = new User($this->db, $config, null, false);
        $start = time();
        $pass= true;
        switch($this->ACTION)
        {
            case "grant":
                if(isset($this->args[2]) && $this->args[2])
                {
                    if(isset($this->args[3]) && $this->args[3])
                    {
                        $userId = $this->args[2];
                        $amount = $this->args[3];

                        echo "granting '{$userId}' > {$amount} gold".PHP_EOL;
                        
                        $users = $this->db->queryResultArray('select id from web_users where id in ('.$userId.') and lvl != 5');

                        if($users!==false)
                        {
                            $updateStmt = $this->db->prepareQuery("insert into t_tran (uid,currency_id,amount,debit,credit,usd_value) values ({$userId},'MCU',{$amount},0,{$amount},{$amount})");
                             if($updateStmt->execute())
                             {
                                unset($updateStmt);
                                echo "{$userId} was granted {$amount} gold successfully".PHP_EOL;
                             }
                             else
                             {
                                $failure[]=$user['ID'];
                             }
                        }
                        else
                        {
                            echo "system error: failed to grant gold".PHP_EOL;
                        }
                    }
                    else
                    {
                        echo "grant gold error: missing amount".PHP_EOL;
                    }                    
                }
                else
                {
                    echo "grant gold error: missing id".PHP_EOL;
                }
                break;
                
            case "block":
                if(isset($this->args[2]) && $this->args[2])
                {
                    if(isset($this->args[3]) && $this->args[3]){
                        $userId = $this->args[2];
                        $msg = $this->args[3];

                        echo "blocking '{$userId}' because of {$msg}".PHP_EOL;
                        
                        $users = $this->db->queryResultArray('select id, opts from web_users where id in ('.$userId.') and lvl != 5');

                        if($users!==false){
                            $updateStmt = $this->db->prepareQuery('update web_users set lvl = 5, opts = ? where id = ?');
                            $success = [];
                            $failure = [];
                            foreach($users as $user){
                                $opts = json_decode($user['OPTS'],true);
                                if(isset($opts['block']) && is_array($opts['block'])){
                                    $opts['block'][] = $msg;
                                }else{  
                                    $opts['block'] = [$msg];
                                }
                                
                                if($updateStmt->execute([json_encode($opts),$user['ID']])){
                                    $success[]=$user['ID'];
                                }else{
                                    $failure[]=$user['ID'];
                                }
                            }
                            unset($updateStmt);
                            echo "blocked successfully: ".implode(",", $success).PHP_EOL;
                            echo "failed to block: ".implode(",", $failure).PHP_EOL;
                        }else{
                            echo "system error: failed to fetch accounts".PHP_EOL;
                        }
                    }else{
                        echo "unblock error: missing \"message for why blocked\"".PHP_EOL;
                    }                    
                }else{
                    echo "block error: missing  \"id1,id2,...\"".PHP_EOL;
                }
                break;
                
            case "unblock":
                if(isset($this->args[2]) && is_numeric($this->args[2]))
                {
                    $userId = $this->args[2];

                    $accounts = [
                        $userId => ['LVL'=>0]
                    ];

                    echo "unblocking {$userId}..".PHP_EOL;

                    $this->getUserAccounts($accounts);
                    $ids=  array_keys($accounts);

                    $q='update web_users set lvl=0 where id in ('.  implode(',', $ids).')';

                    if($this->db->get($q))
                    {
                        echo "{$userId} unblocked successfully".PHP_EOL;
                    }
                    else
                    {
                        echo "system error: failed to unblock".PHP_EOL;
                    }
                }
                else
                {
                    echo "unblock error: missing {web_user_id}".PHP_EOL;
                }
                break;
                
            case "yBlocked":
                if(isset($this->args[2]) && is_numeric($this->args[2])){
                    $userId = $this->args[2];
                    $q = 'select id,lvl, opts from web_users where id = '.$userId;                    
                    $user = $this->db->queryResultArray($q);
                    if($user && isset($user[0]['ID']) && $user[0]['ID']>0){
                        $uObj = ['LVL'=>$user[0]['LVL']];
                        $opts = json_decode($user[0]['OPTS'],true);
                        if($user[0]['LVL']==5 && isset($opts['block'])){
                            $uObj['block']=$opts['block'];
                        }
                        $accounts = [
                            $userId => $uObj
                        ];

                        echo "checking why account {$userId} was blocked..".PHP_EOL;

                        $this->getUserAccounts($accounts);

                        $this->displayUserAccounts($accounts);
                    }else{
                        echo "yBlocked error: user does not exist".PHP_EOL;
                    }                    
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
                echo "3-\tblock \"id1,id2,...\" \"message for why blocked\"".PHP_EOL;
                echo "4-\tgrant \"id\" \"amount\"".PHP_EOL;
                break;
        }
        if($pass){
            $time = time() - $start;
            echo "Runtime: {$time}ms".PHP_EOL;
        }
        echo "------------------------------------------------------".PHP_EOL;
    }
    
    
    function getUserAccounts(&$accounts)
    {
        $ids = array_keys($accounts);        
        $accountCount = count($accounts);
        
        $q = 'select * from ad_user where web_user_id in (';
        $i=0;
        foreach ($ids as $id){
            if($i){
                $q .= ',';
            }
            $q .= $id;
            $i++;
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
        $accounts=json_decode($accounts,true);
        
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
