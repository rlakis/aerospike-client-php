<?php
require_once __DIR__.'/../../deps/autoload.php';
require_once __DIR__.'/../model/NoSQL.php';
define('STORE_ON_DISK', true, false);
define('TOKEN_FILENAME', 'tokens.dat', false);

use Core\Model\NoSQL;

class MCGoogleAnalytics {
    private $client;
    private $service;
    private $adClientId;
    private $accountId;       
    
    public function __construct() {
        // Set up authentication.
        $this->client = new Google_Client();
        $this->client->setApplicationName("Berysoft");
        $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->client->setAuthConfigFile('/opt/client_secrets.json');
        
        //$this->client->setAccessType('offline');
        //$this->client->setPrompt('select_account consent');
       // https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A19776330&start-date=30daysAgo&end-date=yesterday&metrics=ga%3Apageviews&dimensions=ga%3ApagePath&sort=-ga%3Apageviews
        $tokenPath = '/opt/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
        
        $this->service = new Google_Service_Analytics($this->client);
        
        //$accounts = $this->service->management_accounts->listManagementAccounts();

        //var_dump($accounts);
    }
    
    
    public function parseTsvFile(string $file='/opt/analytics.tsv') : void {
        $totals=[];
        $handle = fopen($file, 'r');
        if ($handle) {
            while (($row = fgetcsv($handle, 0, "\t")) !== false) {
                if (\count($row)===2) {
                    $row[0]=preg_replace('/[\x00-\x1F\x7F]/u', '', $row[0]);
                    $path=\rtrim(\str_replace('www.mourjan.com', '', \trim($row[0])), "/");
                    $path=\preg_replace('/\/\d+$/', '', $path);
                    $path=\preg_replace('/\/en$/', '', $path);
                    $views=\preg_replace('/\D/', '',$row[1])+0;
                    
                    $pk=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_URL_PATH, $path);
                    $result=NoSQL::instance()->getBins($pk);
                    if (empty($result)) {
                        //echo $path, "\t", $views,  "\tinvalid", "\n";
                    }
                    else if ($result['country_id']<=0) {
                        continue;
                    }
                    else if ($result['section_id']===0) {
                        continue;
                    }
                    else if ($result['purpose_id']===0) {
                        continue;
                    }
                    else {
                        if (!isset($totals[$path])) {
                            $totals[$path]=['views'=>$views, 'country'=>$result['country_id'], 'city'=>$result['city_id'], 'section'=>$result['section_id'], 'purpose'=>$result['purpose_id']];
                        }
                        else {
                            $totals[$path]['views']+=$views;
                        }
                    }
                }
            }
        }
        fclose($handle);
                        
        //var_dump($totals);
        $partitions=[];
        $countries=[];
        $cities=[];
        foreach ($totals as $k => $v) {
            $cn=$v['country'];
            $cc=$v['city'];
            if (!isset($countries[$cn])) {  $countries[$cn]=[];  }            
            if ($cc>0 && !isset($cities[$cc])) {  $cities[$cc]=[];  }
            
            $sp=$v['section'].'-'.$v['purpose'];
            
            
            if ($cc>0) {
                if (!isset($cities[$cc][$sp])) {
                    $cities[$cc][$sp]=$v['views'];
                }
                else {
                    $cities[$cc][$sp]+=$v['views'];
                }
            }
            
            if (!isset($countries[$cn][$sp])) {
                $countries[$cn][$sp]=$v['views'];
            }
            else {
                $countries[$cn][$sp]+=$v['views'];
            }                            
        }
        
        
        foreach ($countries as $cn => $arr) {
            arsort($arr);
            $i=0;
            $countries[$cn]=[];
            foreach ($arr as $k => $v) {
                $countries[$cn][$k]=$v;
                $i++;
                if ($i>4) { break; }
            }
        }
        
        foreach ($cities as $cc => $arr) {
            arsort($arr);
            $i=0;
            $cities[$cc]=[];
            foreach ($arr as $k => $v) {
                $cities[$cc][$k]=$v;
                $i++;
                if ($i>4) { break; }
            }
        }
        //var_dump($cities);
        
        
        foreach ($countries as $cn => $arr) {
            $res=[];
            foreach ($arr as $k => $v) {
                $p=\explode('-', $k);
                echo $cn, "\t", $p[0], ' ', $p[1], "\t", $v, "\n";
                $res[]=['se'=>$p[0]+0, 'pu'=>$p[1]+0, 'vw'=>$v];
            }
            $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'top-'.$cn);            
            NoSQL::instance()->setBins($key, ['data'=>$res]);
        }
        
        foreach ($cities as $cc => $arr) {
            $res=[];
            foreach ($arr as $k => $v) {
                $p=\explode('-', $k);
                echo $cc, "\t", $p[0], ' ', $p[1], "\t", $v, "\n";
                $res[]=['se'=>$p[0]+0, 'pu'=>$p[1]+0, 'vw'=>$v];
            }
            $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'top-city-'.$cc); 
            NoSQL::instance()->setBins($key, ['data'=>$res]);
        }
        
    }
    
    

}

$mcAnalytics = new MCGoogleAnalytics;
$mcAnalytics->parseTsvFile();
//$mcAnalytics->setAdClientId("313743502213-delb6cit3u4jrjvrsb4dsihpsoak2emm.apps.googleusercontent.com")
//        ->setAccountId("pub-2427907534283641")
//        ->earnings();