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

    
    private function database() : \Core\Model\DB
    {
        return $this->db;
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
            case "refund":
                $days = 0;
                if(isset($this->args[2]) && is_numeric($this->args[2]))
                {
                    $days = (int)$this->args[2];
                    if($days > 2){
                        echo 'date span is too much, you will have to code it by hand'."\n";
                        break;
                    }
                }
                    
                $users = $this->db->queryResultArray('select uid, sum(t.debit) from t_Tran t where t.debit > 0 and t.dated >= current_date group by 1');
                
                $usersCount = 0;
                $usersRefunded = 0;
                $refunds = 0;
                foreach ($users as $user){
                    $uid = $user['UID'];
                    $coins = (int)$user['SUM'];
                    $usersCount++;
                    $mobiles = $this->db->queryResultArray('select first 1 *  from web_users_linked_mobile t where t.uid = ?  order by t.activation_timestamp desc', [$uid]);
                    if(count($mobiles)){
                        $mobile = $mobiles[0]['MOBILE'];
                        $updateStmt = $this->db->prepareQuery("insert into t_tran (uid,currency_id,amount,debit,credit,usd_value) values ({$uid},'MCU',{$coins},0,{$coins},{$coins})");
                        if($updateStmt->execute()){
                            $usersRefunded++;
                            $refunds+=$coins;
                            include_once $config['dir'].'/core/lib/MourjanNexmo.php';
                            $sent = ShortMessageService::send($mobile, "mourjan encountered some technical issues affecting some premium ads not showing. Therefore your account is refunded with {$coins} mourjan gold and we apologize for the inconvenience");                                            
                        }
                    }
                }
                echo "\n";
                echo "users affected\t\t{$usersCount}\n";
                echo "total refunds\t\t{$refunds}\n";
                break;
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
                
            case "redirect":
                $redis = new \Redis();
                $redis->connect('p1.mourjan.com', 6379, 1, NULL, 100);
                $redis->setOption(\Redis::OPT_SERIALIZER, $config['memstore']['serializer']);
                $redis->setOption(\Redis::OPT_PREFIX, $config['memstore']['prefix']);
                $redis->select(0);
                var_dump($this->args);
                //$path = $redis->get($this->args[3]);
                
                $destination = $this->database()->queryResultArray("
                    SELECT COUNTRY_ID, CITY_ID, ROOT_ID, SECTION_ID, PURPOSE_ID, trim(MODULE),
                    iif(TITLE_EN>'', TITLE_EN, SUBSTRING(title from POSITION(ascii_char(9) , title) for 128)),
                    iif(TITLE_AR>'', title_ar, SUBSTRING(title from 1 for POSITION(ascii_char(9), title))),
                    REFERENCE, REDIRECT_TO
                    FROM URI
                    where PATH=?
                    and BLOCKED=0
                    ", [$this->args[3]], FALSE, PDO::FETCH_NUM);
                
                if ($destination && is_array($destination) && count($destination)==1)
                {
                    $from = preg_replace("/\/en\/$|\/en$|\/$/", "", $this->args[2]);
                    
                    echo $from, "\n";
                    $destination[9] = $this->args[3];
                    var_dump($destination);
                    echo "\n\n";
                    
                    $redis->set($from, $destination);
                    
                    
                    //$object = [$destination['COUNTRY_ID'], $destination['CITY_ID'], $destination['ROOT_ID'], $destination[]];
                }
                else
                {
                    echo "Invalid destination url {$this->args[3]}", "\n";
                }
                
                
                break;
                
            case 'ga':
                break;
            
            default:
                $pass=false;
                echo "command not found".PHP_EOL;
                echo "available commands:".PHP_EOL;
                echo "1-\tyBlocked {web_user_id}".PHP_EOL;
                echo "2-\tunblock {web_user_id}".PHP_EOL;
                echo "3-\tblock \"id1,id2,...\" \"message for why blocked\"".PHP_EOL;
                echo "4-\tgrant \"id\" \"amount\"".PHP_EOL;
                echo "5-\trefund \"{days[0,1,2,...]}\" (ex: 0 for current_date, 1 for current_date -1)".PHP_EOL;
                echo "6-\tredirect from_path to_path (301 html redirect for deleted pages)".PHP_EOL;
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
