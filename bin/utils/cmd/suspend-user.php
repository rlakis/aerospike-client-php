<?php

if (PHP_SAPI!=='cli') {
    return;
}

$parameters = ['u:'=>'uid:', 't:'=>'unixtime:'];
$options = getopt(implode('', array_keys($parameters)), $parameters);

$uid = 0;
$unixtime = -1;

if (isset($options['u']))
{
    $uid = intval($options['u'], 10);
}
elseif (isset($options['uid']))
{
    $uid = intval($options['uid'], 10);
}

if (isset($options['t']))
{
    $unixtime = intval($options['t'], 10);
}
elseif (isset($options['unixtime']))
{
    $unixtime = intval($options['unixtime'], 10);
}

if ($uid > 0 && $unixtime >= -1)
{
    include get_cfg_var("mourjan.path") . '/config/cfg.php';
    $db = new DB($config);
    $rs = $db->queryResultArray("select * from web_users where id=?", [$uid]);
    //var_dump($rs);
    if (!empty($rs) && count($rs)===1)
    {
        if ($rs[0]['LVL']==='5')
        {
            echo "Blocked user!\n";
            $db->close();
            exit(1);
        }
        $opts = json_decode($rs[0]['OPTS']);
        //print_r($opts);
        if (!isset($opts->suspend))
        {
            $opts->suspend = 0;
        }

        $suspendTill = new DateTime();
        $suspendTill->setTimezone(new DateTimeZone('UTC'));
        $suspendTill->setTimestamp($opts->suspend);
        $suspendTill->setTimezone(new DateTimeZone("Asia/Beirut"));
        if ($suspendTill->getTimestamp()>time())
        {
            echo "Suspended till: ", $suspendTill->format("Y-m-d H:i:s.u"), "\n";
            if ($unixtime===0)
            {
                $db->queryResultArray("delete from log_account_action where uid={$uid}");
            }
            if ($unixtime!=-1)
            {
                $opts->suspend = $unixtime;
                $db->queryResultArray("update web_users set opts = ? where id={$uid}", [json_encode($opts)]);
                $db->commit();
                echo "Done\n";
            }
        } else {
            echo "Suspend is expired: ", $suspendTill->format("Y-m-d H:i:s.u"), "\n";
            echo "Nothing to do\n";
        }
    }
} else {
    echo "usage: " . __FILE__ . " --uid userid --unixtime suspendtill", "\n";
}



