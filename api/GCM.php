<?php
class PRIORITY {
    const normal = "normal";
    const high = "high";
}
class GCM {
    
    const NOTIFICATION_WATCHLIST = 1;
    const NOTIFICATION_MESSAGE = 2;
    const NOTIFICATION_AD_STAGE = 3;
    const NOTIFICATION_USER_CREDENTIALS = 4;
    const NOTIFICATION_USER_BALANCE = 5;
    const NOTIFICATION_GRANT_GOLD = 6;
    const NOTIFICATION_SYNC_FAV = 7;
    const NOTIFICATION_SYNC_WATCH = 8;
    const NOTIFICATION_SYNC_NOTE = 9;

    var $ids = [];
    var $data = [];
    var $priority = PRIORITY::normal;
    var $isFCMRequest = false;
    
    
    var $checkCanonical = 0;

    function __construct($checkCanonical = 0) {
        $this->checkCanonical = $checkCanonical;
    }
    
    function setPriority($priority){
        $this->priority = $priority;
    }
    
    function setFCM($isFCM){
        $this->isFCMRequest = $isFCM;
    }
    
    function setSendTo($id){
        $this->ids[] = $id;
    }
    
    function setData($data){
        $this->data = ['message'=>$data];
    }
    
    function clearAll(){
        $this->ids=[];
        $this->data=[];
    }
    
    function clearIds(){
        $this->ids=[];
    }

    function send() {
        if(count($this->ids) == 0) return null;
        
        $apiKey = 'AAAA7N45tgU:APA91bE4DdGy-wZ0zZQoTW0BX-oofkt38_DFWeJMoHgjz_5fvCBTjV7EnE-OzOM91AtKeBuxxAPvtsAD6Ikqx33wXECVTBNJkcmdyOV6iWzJq8GpAA0AeXiWL-TYCRpYBw9pmjQOunw29J-_LTO0SfQ5EIH43nHK5Q';
        $url = 'https://android.googleapis.com/gcm/send';
        if($this->isFCMRequest){
            $url = 'https://fcm.googleapis.com/fcm/send';
        }
        $post = array(
            "collapse_key" => "mourjan",
            "delay_while_idle" => true,
            'registration_ids' => $this->ids,
            'data' => $this->data,
            'priority'  =>  $this->priority
        );



        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json',
            'Connection: Keep-Alive',
            'Keep-Alive: 300'
        );


        $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


        $raw_res = $result = curl_exec($ch);

        $pass = 1;
        if ($result == NULL || curl_errno($ch)) {
            $pass = 0;
            //echo 'GCM error: ' . curl_error($ch)."\n";
            error_log('Android PUSH curl Error: '.curl_error($ch).PHP_EOL);
            //syslog(LOG_ERR, 'Android PUSH curl Error: '.curl_error($ch).PHP_EOL);
        }else{
            //echo 'Android PUSH Error: '.$raw_res.PHP_EOL;
            $result = json_decode($result, TRUE);
            if($result['failure']!=0){
                $pass = 0;
                if(isset($result['results'][0]['error']) && $result['results'][0]['error']=="NotRegistered"){
                    $pass = -1;
                }
                error_log('Android PUSH Error: '.$raw_res.PHP_EOL);
                //syslog(LOG_ERR, 'Android PUSH Error: '.$raw_res.PHP_EOL);
                //syslog(LOG_ERR, var_export($this->data,true).PHP_EOL);
            }else if($this->checkCanonical && $result["canonical_ids"]==1){
                $pass = -1;
            }
        }

        //curl_close($ch);
        return $pass;
    }

}
