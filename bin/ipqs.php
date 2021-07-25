<?php
include_once __DIR__.'/../core/lib/IPQuality.php';

$ip=\filter_input(\INPUT_GET, 'ip');
$kc='C'.$ip;
$user_agent=\filter_input(\INPUT_GET, 'ua');
$language=\filter_input(\INPUT_GET, 'ul');

$key='5tk9uBbLgGqd1joZgGdnqfKcBVwM8KnF';

$whitelist=['2a01:4f8:211:7aa::2', '2a01:4f8:172:3981::2', '2a01:4f8:211:149::2', '2a01:4f8:171:3064::2',
    '2a01:4f8:10a:348e::2', '2a01:4f8:172:b9d::2', '2a01:4f8:231:1d1e::2', '2a01:4f8:13b:3bec::2',
    '2a01:4f9:2a:288b::2', '2a01:4f8:c17:3cae::2', '2a01:4f8:1c0c:5a54::2', '2a01:4f8:c17:a56::2'];




    $ipqs=new IPQuality;
    
    if (\is_string($ip)) {
        if ($ipqs->incIpHits($ip, $returned)===\Aerospike::OK) {
            if ($returned['hits']>40 && $returned['time_span']<5) {
                $status=$ipqs->get_from_cache($ip, $result);
                
                //if ($status===\Aerospike::ERR_RECORD_NOT_FOUND) {
                if ($status!==\Aerospike::OK) {
                    $url=sprintf('https://www.ipqualityscore.com/api/json/ip/%s/%s?user_agent=%s&user_language=%s&strictness=%s&allow_public_access_points=false', $key, $ip, $user_agent, $language, 3);
                    $status=$ipqs->get_IPQ_URL($url, $ip, $result);
                }
                
                $connection_type=$result['connection_type']??'';
                if ($connection_type==='Data Center') {
                    $isp=\strtolower($result['ISP']??'');
                    $org=\strtolower($result['organization']??'');
                    if ($isp==='googlebot' && \substr($result['host'], -13)==='googlebot.com') {
                        return;
                    }
                    
                    if ($isp==='microsoft bingbot'||$org==='microsoft bingbot') {
                        //\error_log("{$ip}: isp: {$isp}");
                        //\error_log(\json_encode($result).PHP_EOL);
                        return;                        
                    }
                    else if ($isp==='amazon.com') {
                        
                    }
                    else if ($isp==='google proxy') {
                        if ($result['browser']==='Google Mediapartners' && ($result['bot_status']===1 || $result['is_crawler']===1)) {
                            //\error_log("{$ip}: browser: {$result['browser']}, bot: {$result['bot_status']}, crawler: {$result['is_crawler']}");
                            return;
                        }
                    }                                        
                }
                
                $result['requests']=$returned['hits'];
                $result['time_span']=$returned['time_span']??0;
                //if ($status===\Aerospike::OK) {  
                if ($result['requests']%20===0) {
                    $validator=new Validity($result['browser']??'', $result['operating_system']??'');
                    //if ($validator->extracted() && ($validator->osVersion<=0 || $validator->browserVersion<=0)) {
                        $result['desc']=$validator->toString();
                    //}
                    
                    
                    if ($result['fraud_score']===0 && $result['time_span']>3.0) {
                        return;
                    }
                    
                    if ($result['time_span']<3.0 || $result['requests']>500) {
                        \error_log(__LINE__.PHP_EOL.$ip.PHP_EOL.$user_agent.PHP_EOL.\json_encode($result).PHP_EOL, 3, "/var/log/mourjan/ip.log"); 
                    }
                    
                    if ($validator->extracted()) {
                        
                        if ($validator->isBadBrowser()) {
                            blockIP($ip, $result, __LINE__);
                            /*
                            \error_log(PHP_EOL."suggestion (".__LINE__."): firewall-cmd --zone=block --add-source={$ip}".PHP_EOL);
                            $filename="/var/log/mourjan/incoming/{$ip}.ip";
                            if (!file_exists($filename)) {
                                $result['cmd']="firewall-cmd --zone=block --add-source={$ip}";
                                \error_log(\json_encode($result, JSON_PRETTY_PRINT).PHP_EOL, 3, $filename);                                
                            }
                            return;
                             * 
                             */
                        }
                        
                        if ($validator->browserName==='googlebot') {
                            if ($result['country_code']==='SN') {
                                blockIP($ip, $result, __LINE__);
                            }
                            \error_log($user_agent.PHP_EOL.\substr($result['host'], -13).PHP_EOL, 3, "/var/log/mourjan/ip.log");
                            \error_log(PHP_EOL."suggestion (".__LINE__."): firewall-cmd --zone=block --add-source={$ip}".PHP_EOL, 3, "/var/log/mourjan/ip.log"); 
                        }
                    }
                    
                    if (($result['country_code']==='UA' || $result['country_code']==='PK')  && $result['requests']>100 && $result['time_span']<1) {
                        blockIP($ip, $result, __LINE__);
                    }
                    
                    if ($result['requests']>1000 && $result['time_span']<1) {
                        blockIP($ip, $result, __LINE__);
                    }
                    
                    
                }
                //}
                
            }
        }
        else {
            \error_log("incIpHits({$ip}) failed!".PHP_EOL, 3, "/var/log/mourjan/ip.log"); 
        }
    }              


function blockIP(string $ip, array $info, int $lineNo) : void {
    //\error_log(PHP_EOL."suggestion ({$lineNo}): firewall-cmd --zone=block --add-source={$ip}".PHP_EOL);
    \error_log(PHP_EOL."suggestion ({$lineNo}): sudo fail2ban-client set mourjan banip {$ip}".PHP_EOL);
    $filename="/var/log/mourjan/incoming/{$ip}.ip";
    if (!file_exists($filename)) {
        //$info['cmd']="firewall-cmd --zone=block --add-source={$ip}";
        $info['cmd']="/usr/bin/sudo /usr/bin/fail2ban-client set mourjan banip {$ip}";
        \error_log(\json_encode($info, JSON_PRETTY_PRINT).PHP_EOL, 3, $filename);                                
    }
    exit(0);                        
}



class Validity {
    public string $osName;
    public float $osVersion;
    public string $browserName;
    public float $browserVersion;
    public bool $undefinedBrowser;
    public bool $undefinedOS;
    
    
    function __construct(string $browser, string $os) {
        $this->browserName='';
        $this->browserVersion=0;
        $this->osName='';
        $this->osVersion=0;
        
        if ($browser!=='') {
            if (\preg_match('/(.*)\s(\d+\.\d+)/', $browser, $matches)) {
                $this->browserName=\strtolower($matches[1]);
                $this->browserVersion=\floatval($matches[2]);
            }
            else if (\preg_match('/(.*)\s(\d+)$/', $browser, $matches)) {
                $this->browserName=\strtolower($matches[1]);
                $this->browserVersion=\floatval($matches[2]);
            }
            else {
                $this->browserName=\trim(\strtolower($browser));
                $this->undefinedBrowser=($this->browserName==='unk unk');
            }            
        }
        
        if ($os!=='') {
            if (\preg_match('/(.*)\s(\d+\.\d+)/', $os, $matches)) {
                $this->osName=\strtolower($matches[1]);
                $this->osVersion=\floatval($matches[2]);
            }
            else if (\preg_match('/(.*)\s(\d+)$/', $os, $matches)) {
                $this->osName=\strtolower($matches[1]);
                $this->osVersion=\floatval($matches[2]);
            }
            else {
                $this->osName=\trim(\strtolower($os));
                $this->undefinedOS=($this->osName==='unk unk');
            }
        }        
    }
    
    
    public function extracted() : bool {
        return ($this->osName!==''||$this->browserName!=='');        
    }
    
    
    public function isBadBrowser() : bool {
        if ($this->browserName==='unk unk') {
            return true;
        }
        
        if ($this->browserName==='java') {
            return true;
        }
        
        if ($this->browserName==='internet explorer' && $this->browserVersion<=8) {
            return true;
        }
        
        if ($this->browserName==='firefox' && $this->browserVersion>0) {
            if ($this->browserVersion<=40.0) {
                return true;
            }
        }
                
        
        if ($this->browserName==='chrome' && $this->browserVersion>0) {
            if ($this->osName==='windows' && $this->osVersion===10.0) {
                if ($this->browserVersion===69.0) {
                    return true;
                }
            }
            if ($this->osName==='windows') {
                if ($this->browserVersion===41.0) {
                    return true;
                }
            }
            if ($this->browserVersion<=36.0) {
                return true;
            }
        }
        
        return false;
    }
    
    
    public function toString() : string {
        if ($this->extracted()) {
            return "Browser: {$this->browserName} ({$this->browserVersion}), OS: {$this->osName} ({$this->osVersion})";
        }
        return '';
    }
}