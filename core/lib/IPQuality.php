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

    
    function get_client_ip_server() {
        $ipaddress = '';
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
        else {
            $ipaddress = 'UNKNOWN';
        }
 
        return $ipaddress;
    }
		
}


$ipQuality = new IPQuality;
$IP = '185.17.149.151'; //$ipQuality->get_client_ip_server();

if ($ipQuality->ValidIP($IP)===true){
    // User is using a proxy or is high risk!
    // Optionally redirect the user with the code below
    // header('Location: https://www.google.com');
    echo 'Proxy, VPN, or Risky User';
} 
else {
    // User appears to be clean!
    // header('Location: https://www.mysite.com');
    echo 'Clean IP/User';
}
