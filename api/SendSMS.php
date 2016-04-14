<?php

include get_cfg_var("mourjan.path") . "/config/cfg.php";
include_once $config['dir'].'/core/lib/nexmo/NexmoMessage.php';

$arguments = getopt("i::n::t::");
print_r($arguments);

if ($argc<2) 
{
    echo 'Usage php SendSMS.php -iWEB_USERS_MOBILE.ID -nMobileNumber -tSMSTextMessage', "\n";
    return;
}

$id = isset($arguments['i']) ? intval($arguments['i'], 10) : 0;


$sms = new NexmoMessage('8984ddf8', 'CVa3tHey3js6');

$text = "";
$mobile_number = "";

if ($id)
{
	$db = new DB($config);

	$rs = $db->queryResultArray(
    	"SELECT UID, MOBILE, CODE, STATUS, REQUEST_TIMESTAMP, 
    	ACTIVATION_TIMESTAMP, DELETE_TIMESTAMP, MOVED_TO, DELIVERED, SECRET, SMS_COUNT
    	FROM WEB_USERS_MOBILE
    	WHERE ID=?", [$id]);

	if ($rs) 
	{
    	$rs=$rs[0];
    	print_r($rs);
    	$pin = $rs['CODE'];
    	if ($pin<1000) {
        	$pin = mt_rand(1000, 9999);
    	}

    	$from = ($rs['MOBILE'][0]=='1')?'12165044111':'Mourjan';
    
    	$response = $sms->sendText( "+{$rs['MOBILE']}", $from,
                            "Your Mourjan code is:\n{$pin}\nClose this message and enter the code into Mourjan to activate your account.",
                            $id);
    	print_r($response); 
    
    	if ($response) {
        	$db->queryResultArray("update WEB_USERS_MOBILE set status=0, sms_count=sms_count+1 where id=?", [$id], TRUE);
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
		$from = ($mobile_number[0]=='1')?'12165044111':'Mourjan';
 	  	$response = $sms->sendText("+{$mobile_number}", $from, $text, 0);
 	  	print_r($response); 
 	
	}
}
