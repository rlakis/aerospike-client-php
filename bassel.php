<?php
exit(0);
require_once 'vendor/autoload.php';
require_once get_cfg_var('mourjan.path'). '/config/cfg.php';
include_once $config['dir'].'/core/lib/MourjanNexmo.php';

$sms = new MourjanNexmo();
$sent = $sms->sendSMS('+96171750413', "test");
var_dump($sent);
exit(0);
function maskEmail($email){
    $tmp = preg_split('/@/',$email);
    if(is_array($tmp) && count($tmp)){
        $email = $tmp[0];
        $pos = ceil(strlen($email)/3);
        $email = substr($email, 0, $pos);
        $diff = strlen($tmp[0]) - strlen($email);
        for($i=0;$i<$diff;$i++){
            $email.='*';
        }
        $email.='@'.$tmp[1];
        return $email;
    }
    return '';
}

function maskName($name){
    if($name){
        $org = $name;
        $pos = ceil(strlen($name)/2);
        $name = substr($name, 0, $pos);
        $diff = strlen($org) - strlen($name);
        for($i=0;$i<$diff;$i++){
            $name.='*';
        }
        return $name;
    }
    return '';
}

//echo maskEmail('basselmourjan@hotmail.com');
echo maskName('alaa ali');

