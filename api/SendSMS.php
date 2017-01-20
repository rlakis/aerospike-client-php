<?php
include get_cfg_var("mourjan.path") . "/config/cfg.php";
include_once $config['dir'].'/core/lib/MourjanNexmo.php';

$arguments = getopt("i::n::t::");
print_r($arguments);

if ($argc<2) 
{
    echo 'Usage php SendSMS.php -iWEB_USERS_MOBILE.ID -nMobileNumber -tSMSTextMessage', "\n";
    return;
}
$linked=FALSE;
$id = isset($arguments['i']) ? intval($arguments['i'], 10) : 0;
if ($id<0)
{
    $id=abs($id);
    $linked=true;
}


$sms = new MourjanNexmo();

$text = "";
//$mobile_number = "+97455127794";


if ($id)
{
    $db = new DB($config);

    if ($linked)
    { 
        $rs = $db->queryResultArray(
            "SELECT ID, UID, MOBILE, CODE, ACTIVATION_TIMESTAMP, DELIVERED, REQUEST_TIMESTAMP, SMS_COUNT
            FROM WEB_USERS_LINKED_MOBILE 
            WHERE ID=?", [$id]);
    }
    else
    {
        $rs = $db->queryResultArray(
            "SELECT UID, MOBILE, CODE, STATUS, REQUEST_TIMESTAMP, 
            ACTIVATION_TIMESTAMP, DELETE_TIMESTAMP, MOVED_TO, DELIVERED, SECRET, SMS_COUNT
            FROM WEB_USERS_MOBILE
            WHERE ID=?", [$id]);
    }
    

    if ($rs) 
    {
        $rs=$rs[0];
        print_r($rs);
        $pin = $rs['CODE'];
        if ($pin<1000) 
        {
            $pin = mt_rand(1000, 9999);
    	}
        $mobile_number = $rs['MOBILE'];
    	
        
    	$response = $sms->sendSMS( "+{$mobile_number}", 
                            $pin." is your mourjan confirmation code",
                            ($linked?"m":"").$id);
    	print_r($response); 
    
    	if ($response) 
        {           
            $db->queryResultArray($linked ? "update WEB_USERS_LINKED_MOBILE set sms_count=sms_count+1 where id=?" : "update WEB_USERS_MOBILE set status=0, sms_count=sms_count+1 where id=?", [$id], TRUE);
    	}
    }
    $db->close();
}
elseif (isset($arguments['n']) && isset($arguments['t']))
{
    $text = $arguments['t'];
    $mobile_number = intval($arguments['n'], 10);
    if ($text && $mobile_number)
    {
        $response = $sms->sendSMS("+{$mobile_number}", $text, 0);
        print_r($response); 
    }
}
