<?php

if (PHP_SAPI!=='cli') {
    return;
}

$home=dirname(__DIR__,3);
include_once $home.'/config/cfg.php';
Config::instance()->incModelFile('Db')->incModelFile('NoSQL')->incLibFile('MCUser');

$content=file_get_contents($home.'/bin/number-list.json');
$numbers=json_decode($content);

$as=\Core\Model\NoSQL::instance();
/*
foreach ($numbers as $number => $date) {
    $black_listed=$as->isBlacklistedContacts([$number]);
    if (!$black_listed) {
        $as->blacklistInsert($number, "Disposable number, reported on {$date}");
    }
}
*/

$uidList=[];
foreach ($numbers as $number=>$date) {
    $where=\Aerospike::predicateEquals(\Core\Model\ASD\USER_MOBILE_NUMBER, intval($number));
    $status=$as->getConnection()->query(
            \Core\Model\NoSQL::NS_USER, \Core\Model\ASD\TS_MOBILE, 
            $where, 
            function ($record) use (&$uidList) {
                //print_r($record);
                $uidList[]=$record['bins'][Core\Model\ASD\USER_UID];
            }, 
            [Core\Model\ASD\USER_UID]);    
}


foreach ($uidList as $uid) {
    $user=new Core\Lib\MCUser($uid);
    //$user=$as->fetchUser($uid);
    if ($user->getBalance()!=0) {
        print_r($user);            
    }
    else {
        if (!$user->isBlocked()) {
            $as->setUserLevel($uid, 5);
        }
    }    
}

