<?php

class IPQuality {
    private $key = '5tk9uBbLgGqd1joZgGdnqfKcBVwM8KnF';
    private $fraudScoreMinBlock = 75; // Minimum Fraud Score to determine a fraudulent or high risk user
    private $fraudScoreMinBlockForMobiles = 75; // Minimum Fraud Score to determine a fraudulent or high risk user for MOBILE Devices
    private $lowerPenaltyForMobiles = false; // Prevents false positives for mobile devices - if set to true, this will only block VPN connections, Tor connections, and Fraud Scores greater than the minimum values set above for mobile devices. This setting is meant to provide greater accuracy for mobile devices due to mobile carriers frequently recycling and sharing mobile IP addresses. Please be sure to pass the "user_agent" (browser) for this feature to work. This setting ensures that the riskiest mobile connections are still blacklisted.
    private $allow_public_access_points = 'false'; // Bypasses certain checks for IP addresses from education and research institutions, schools, and some corporate connections to better accommodate audiences that frequently use public connections. This value can be set to true to make the service less strict while still catching the riskiest connections.

    function __construct() {
        ;
    }
    
    
    function checkIP(string $IP, int $strictness=1) {
	// $strictness = 1; This optional parameter controls the level of strictness for the lookup. Setting this option higher will increase the chance for false-positives as well as the time needed to perform the IP analysis. Increase this setting if you still continue to see fraudulent IPs with our base setting (level 1 is recommended) or decrease this setting for faster lookups with less false-positives. Current options for this parameter are 0 (fastest), 1 (recommended), 2 (more strict), or 3 (strictest).
        
	if (empty($IP)) {
            $IP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_CLIENT_IP'];
            // If you use cloudflare use this line instead to get the IP:
            // $IP = (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"]);
	}

        try {
            $redis = new Redis();                
            if ($redis->connect('h5.mourjan.com', 6379, 1, NULL, 50)) {
                $redis->select(3);
                $redis->setOption(Redis::OPT_PREFIX, 'IP');                        
                $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);
                $raw = $redis->get($IP);
                $redis->close();                                
            }
        } catch (RedisException $re) {
            print_r($re);
        }
        
        if (isset($raw)) {
            $result = json_decode($raw, true);
            if ($result!==null) {
                print_r($result);
                return $result;
            } 
            else {
		// Throw error, no response received.
            }
        }
        
	$user_agent = urlencode($_SERVER['HTTP_USER_AGENT']??''); // User Browser (optional) - provides better forensics for our algorithm to enhance fraud scores.
	$language = urlencode($_SERVER['HTTP_ACCEPT_LANGUAGE']??''); // User System Language (optional) - provides better forensics for our algorithm to enhance fraud scores.
	$raw = $this->get_IPQ_URL(sprintf('https://www.ipqualityscore.com/api/json/ip/%s/%s?user_agent=%s&user_language=%s&strictness=%s&allow_public_access_points=%s', $this->key, $IP, $user_agent, $language, $strictness, $this->allow_public_access_points));
	echo "raw: ", $raw;
        $result = json_decode($raw, true);
	if ($result!==null) {
            try {
                $redis = new Redis();                
                if ($redis->connect('h5.mourjan.com', 6379, 1, NULL, 50)) {
                    $redis->select(3);
                    $redis->setOption(Redis::OPT_PREFIX, 'IP');                        
                    $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);
                    $redis->setex($IP, 604800, json_encode($result));
                    $redis->close();
                }
            } catch (RedisException $re) {
                print_r($re);
            }
            return $result;
	} 
        else {
		// Throw error, no response received.
	}
    }

    
    private function get_IPQ_URL($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
    }

    
    function validIP($IP) {	
	$IPResult = $this->checkIP($IP);	
	if ($IPResult !== null) {
            print_r($IPResult);
            if($IPResult['mobile']===true && $this->lowerPenaltyForMobiles===true) {
                if (isset($IPResult['fraud_score']) && $IPResult['fraud_score'] >= $this->fraudScoreMinBlockForMobiles) {
                    return true;
                } 
                elseif (isset($IPResult['vpn']) && $IPResult['vpn']===true) {
                    return $IPResult['vpn'];
                } 
                elseif (isset($IPResult['tor']) && $IPResult['tor']===true) {
                    return $IPResult['tor'];
                } 
                else {
                    return false;
                }	
            } 
            else {
                if (isset($IPResult['fraud_score']) &&  $IPResult['fraud_score'] >= $this->fraudScoreMinBlock) {
                    return true;
                } 
                elseif (isset($IPResult['proxy'])) {
                    return $IPResult['proxy'];
                } 
                else {
                    // Throw error, response is invalid.
                }
            }
        } 
        else {
            return false;
        }
    }

    
    public static function ipScore($mobile=null) : float {
        $ip = static::getClientIP();
        
        //$ip = $ipq->get_client_ip_server();
        if ($ip!='UNKNOWN') {
            $redis = new Redis();
            try {                              
                if ($redis->connect('h5.mourjan.com', 6379, 1, NULL, 50)) {
                    $redis->select(3);
                    $redis->setOption(Redis::OPT_PREFIX, 'IP:');  
                    $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);
                    $raw = $redis->get($ip);                    
                    
                    if (!empty($raw)) {
                        $result = json_decode($raw, true);
                        if ($result!==null) {                            
                            if (isset($result['fraud_score'])) {
                                $redis->close(); 
                                return $result['fraud_score']+0.0;
                            } 
                        }
                        else {
                            error_log(__FUNCTION__ . ' error json decode!!!');
                        }
                    }
                }
            } 
            catch (RedisException $re) {}
        
            $ipq = new IPQuality;
            if ($mobile===TRUE) {
                $raw = $ipq->get_IPQ_URL(sprintf('https://www.ipqualityscore.com/api/json/ip/%s/%s?strictness=%s&allow_public_access_points=%s&mobile=%s', $ipq->key, $ip, 3, 'false', 'true')); 
            }
            else {
                $user_agent = urlencode($_SERVER['HTTP_USER_AGENT']??''); // User Browser (optional) - provides better forensics for our algorithm to enhance fraud scores.
                $language = urlencode($_SERVER['HTTP_ACCEPT_LANGUAGE']??''); // User System Language (optional) - provides better forensics for our algorithm to enhance fraud scores.
                $raw = $ipq->get_IPQ_URL(sprintf('https://www.ipqualityscore.com/api/json/ip/%s/%s?user_agent=%s&user_language=%s&strictness=%s&allow_public_access_points=%s', $ipq->key, $ip, $user_agent, $language, 3, 'false'));                
            }
            $result = json_decode($raw, true);
            if ($result!==null) {
                try {
                    $redis->setex($ip, 604800, json_encode($result));
                } 
                catch (RedisException $re) {}
                if (isset($result['fraud_score'])) {
                    $redis->close();
                    return $result['fraud_score']+0.0;
                } 
            } 
            $redis->close();
        }
        return -1;
    }
    
    
    function checkProxy($ip=null){
        $contactEmail="admin@mourjan.com"; //you must change this to your own email address
        $timeout=5; //by default, wait no longer than 5 secs for a response
        $banOnProbability=0.99; //if getIPIntel returns a value higher than this, function returns true, set to 0.99 by default
		
        if ($ip==null) {
            $ip = IPQuality::getClientIP();
        }
        if ($ip=='UNKNOWN') {
            return -1;
        }
        //init and set cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //if you're using custom flags (like flags=m), change the URL below
        curl_setopt($ch, CURLOPT_URL, "http://check.getipintel.net/check.php?ip=$ip&contact={$contactEmail}");
        $response=curl_exec($ch);
		
        curl_close($ch);
				
        if ($response > $banOnProbability) {
            return 100*$response;
            //return true;
        } 
        else {
            if ($response < 0 || strcmp($response, "") == 0 ) {
                //The server returned an error, you might want to do something
                //like write to a log file or email yourself
                //This could be true due to an invalid input or you've exceeded
                //the number of allowed queries. Figure out why this is happening
                //because you aren't protected by the system anymore
                //Leaving this section blank is dangerous because you assume
                //that you're still protected, which is incorrect
                //and you might think GetIPIntel isn't accurate anymore
                //which is also incorrect.
                //failure to implement error handling is bad for the both of us
            }
            return false;
        }
    }


    public static function getClientIP() {
        if ($_SERVER['REMOTE_ADDR']) { return $_SERVER['REMOTE_ADDR']; }
	if ($_SERVER['HTTP_CLIENT_IP']) { return $_SERVER['HTTP_CLIENT_IP']; }
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) { return $_SERVER['HTTP_X_FORWARDED_FOR']; }
        if ($_SERVER['HTTP_X_FORWARDED']) { return $_SERVER['HTTP_X_FORWARDED']; }
        if ($_SERVER['HTTP_FORWARDED_FOR']) { return $_SERVER['HTTP_FORWARDED_FOR']; }
        if ($_SERVER['HTTP_FORWARDED']) { return $_SERVER['HTTP_FORWARDED']; }        
        return 'UNKNOWN';
    }
    
    
    function get_client_ip_server() {
        $ipaddress = 'UNKNOWN';
        if ($_SERVER['REMOTE_ADDR']) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
	else if ($_SERVER['HTTP_CLIENT_IP']) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if($_SERVER['HTTP_X_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        else if($_SERVER['HTTP_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        else if($_SERVER['HTTP_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        }        
 
        return $ipaddress;
    }
		
}
