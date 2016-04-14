<?php

if (get_cfg_var('mourjan.server_id') == '1') {
    require_once '/var/www/dev.mourjan/config/cfg.php';
} else {
    require_once '/home/www/mourjan/config/cfg.php';
}

include_once $config['dir'].'/core/model/Db.php';

require 'lib/vendor/autoload.php';
use Aws\Sns\SnsClient;
use Aws\Common\Enum\Region;

class SNS {
    var $db = null;
    var $client = null;
    var $appName = null;
    var $serviceName = null;
    var $platformApplicationArn;
  
    function __construct($appName='Mourjan', $service='APNS') {
        global $config;
        $this->db = new DB($config);
        $this->client = SnsClient::factory(
                array(
                'key'=>'AKIAJZPQH5KMMPJNAAXQ', 
                'secret'=>'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', 
                'region'=> Region::US_EAST_1
                ));
        $this->appName = $appName;
        $this->serviceName = $service;
        $this->platformApplicationArn = "arn:aws:sns:us-east-1:189564462502:app/{$service}/{$appName}";
    }
    
 
    function addEndPoint($token, $description) {
        try {
            $model = $this->client->createPlatformEndpoint(
                array(
                    'PlatformApplicationArn'=>$this->platformApplicationArn, 
                    'Token'=>$token, 
                    'CustomUserData'=>$description, 
                    'Attributes'=>array()
                ));
            $result = $model->toArray();
            if (isset($result['EndpointArn'])) {
                $parts = explode("/", $result['EndpointArn']);
                $size = count($parts);
                if ($size>0) {
                    $arn_id = $parts[$size-1];
                    $this->db->queryResultArray("update WEB_USERS_DEVICE set SNS_ID=? where PUSH_ID=?", [$arn_id, $token], TRUE);
                    $this->db->close();
                }
            } 
        } catch (Exception $e) {
            //echo $e->getMessage();
        }
    }
    
    function syncEndPoints() {
        $devices = $this->db->queryResultArray("select * from web_users_device where SNS_ID='' and push_id>''", NULL, TRUE, PDO::FETCH_ASSOC);
        $this->db->close();
        if ($devices) {
            foreach ($devices as $device) {
                $this->addEndPoint($device['PUSH_ID'], $device['DEVICE_MODEL'] .' - ' . $device['UID'] . ': ' . $device['UUID']);
            }              
        }
    }
    
    
}


$sns = new SNS();
$sns->syncEndPoints();
