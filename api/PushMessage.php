<?php
const PUSH_GENERAL_MSG  = 0;

const PUSH_USER_HELD    = 10;
const PUSH_USER_BLOCKED = 11;
const PUSH_USER_ACTIVE  = 12;

const PUSH_AD_EXPIRED   = 100;
const PUSH_AD_APPROVED  = 101;
const PUSH_AD_PUBLISHED = 102;
const PUSH_AD_REJECTED  = 103;
const PUSH_AD_DELETED   = 104;

const PUSH_WATCHLIST    = 200;


if ($argc < 3) {
	echo "Usage: iPush.php dev|pro arnId", "\n";
	exit;
}
$sandbox = ($argv[1]==='dev');

require 'lib/vendor/autoload.php';

use Aws\Common\Enum\Region;
use Aws\Sns\SnsClient;


$client = SnsClient::factory(array(
                'key'=>'AKIAJZPQH5KMMPJNAAXQ', 
                'secret'=>'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', 
                'region'=> Region::US_EAST_1
            ));

$title = "Hello from AWI api push notification";

$arnPrefix = "arn:aws:sns:us-east-1:189564462502:endpoint/" . ($sandbox ? "APNS_SANDBOX/Mourjan-Sandbox/" : "APNS/Mourjan/");

$endpoint_id = $argv[2];// "45354df6-a617-3737-bb66-5b4479740c72";

$aps = ['alert'=>$title . " Json format", 'sound'=>'default', 'badge'=>'1'];

$body['aps'] = array(
	'alert' => $title,
	'sound' => 'default',
        'badge' => 1
	);
$body['id'] = 0;
$body['type'] = PUSH_WATCHLIST;



$message = ['default'=>$title];
if ($sandbox) {
    $message['APNS_SANDBOX']= json_encode($body);
} else {
    $message['APNS']=json_encode($body);
}

/*
{
 "APNS_SANDBOX":"{\"aps\":{\"alert\":\"<message>\"}}"
}
*/


$payload = json_encode($message);

echo $payload, "\n\n";

$sns = $client->publish(array(
    'TargetArn'=>$arnPrefix . $endpoint_id, 
    'MessageStructure' => 'json',
    'Message' => $payload));

print_r($sns);

//print_r($client->listEndpointsByPlatformApplication(array('PlatformApplicationArn'=>'arn:aws:sns:us-east-1:189564462502:app/APNS/Mourjan')));

//$model = $client->addEndPoint('9410126dd2fff510f6cd9a9c888b0f1642da3d8ed1838769f5a8976d186204de', '95532');
//$m = new Model();
