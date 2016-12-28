<?php
include_once get_cfg_var('mourjan.path') . '/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';
include_once 'GCM.php';

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

class Messenger {
    private $messenger, $db;
    
    function __construct($config) {
        $this->db = new DB($config, TRUE);
        $this->messenger = new GCM(1);     
    }
    
    public function notifyByUserId($id, $message){
        //api less than 1.3.2
        $data=[
            'type'  =>  GCM::NOTIFICATION_MESSAGE,
            'msg'   =>  $message
        ];
        
        $devices = $this->db->queryResultArray(
                "SELECT * from WEB_USERS_DEVICE d
                    where d.uid = ? and d.removed = 0 "
                            . "and d.app_version > '1.1.0' ", [$id], true);
        
        $sent_messages = 0;
        $pass = 0;
        foreach($devices as $device){ 
            $this->messenger->clearAll();
            $this->messenger->setData($data);
            if ($device['APP_VERSION'] > '1.2.0' && $device['APP_VERSION'] != '1.8.8'){ 
                $this->messenger->setFCM(true); 
            }else{
                $this->messenger->setFCM(false); 
            }
            $this->messenger->setSendTo($device['PUSH_ID']); 
            $pass = $this->messenger->send();
            if($pass>0){
                $sent_messages++;
            }
        }
    }
    
}
$hashKey = 'AAAA7N45tgU:APA91bE4DdGy-wZ0zZQoTW0BX-oofkt38_DFWeJMoHgjz_5fvCBTjV7EnE-OzOM91AtKeBuxxAPvtsAD6Ikqx33wXECVTBNJkcmdyOV6iWzJq8GpAA0AeXiWL-TYCRpYBw9pmjQOunw29J-_LTO0SfQ5EIH43nHK5Q';

$message = '';
$uid = 1;
$key='';
$pass = '';

if(isset($_GET['uid']) && is_numeric($_GET['uid']) && $_GET['uid']){
    $uid = $_GET['uid']+0;
}
if(isset($_GET['msg']) && $_GET['msg']){
    $message = urldecode($_GET['msg']);
}
if(isset($_GET['key']) && $_GET['key']){
    $key = $_GET['key'];
}
if($uid > 0 && $message && md5($uid.$message.$hashKey) == $key){
    $notifier = new Messenger($config);
    $notifier->notifyByUserId($uid, $message);
}else{
    echo "unauthorized access";
}
