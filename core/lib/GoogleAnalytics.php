<?php
require_once __DIR__.'/../../deps/autoload.php';
require_once __DIR__.'/../model/NoSQL.php';
//define('STORE_ON_DISK', true, false);
//define('TOKEN_FILENAME', 'tokens.dat', false);

use Core\Model\NoSQL;

class MCGoogleAnalytics {
    private $client;
    private $service;
    private $adClientId;
    private $accountId;       
    
    private $view_id_site = '19776330';
    
    public function __construct() {
        // Set up authentication.
        $this->client = new Google_Client();
        $this->client->setApplicationName("Berysoft");
        $this->client->setAuthConfig('/opt/php-service-key.json');
        $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        
        //$this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        //$this->client->setAuthConfigFile('/opt/client_secrets.json');
        
        //$this->client->addScope('https://www.googleapis.com/auth/analytics.readonly');
        
        //$this->client->setAccessType('offline');
        //$this->client->setPrompt('select_account consent');
       // https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A19776330&start-date=30daysAgo&end-date=yesterday&metrics=ga%3Apageviews&dimensions=ga%3ApagePath&sort=-ga%3Apageviews
        
        /*$tokenPath = '/opt/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }*/
        
        $analytics = new Google_Service_AnalyticsReporting($this->client);       
        
        //$request = $this->getTestQuery();
        $request = $this->getDimension();

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        $res = $analytics->reports->batchGet( $body );
        
        for ( $reportIndex = 0; $reportIndex < count( $res ); $reportIndex++ ) {
            $report = $res[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            print_r($rows);

        }
       
        
    }
    
    public function getDimension(){
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");
        
        $dimensionCountry = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCountry->setName("ga:dimension4");
        
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName("ga:sessions");
        $ordering->setOrderType("VALUE");   
        $ordering->setSortOrder("DESCENDING");
       
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($dimensionCountry));
        $request->setMetrics(array($sessions));
        $request->setOrderBys($ordering);
        $request->setPageSize(100);
        return $request;
    }
    
    public function getTestQuery(){
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:pageviews");
        $sessions->setAlias("pageviews");
        
        $dimensionCountry = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCountry->setName("ga:Country");
        
        $dimensionPath = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionPath->setName("ga:pagePath");
        
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName("ga:pageviews");
        $ordering->setOrderType("VALUE");   
        $ordering->setSortOrder("DESCENDING");
        
        // Create the DimensionFilter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:pagePath');
        $dimensionFilter->setOperator('REGEXP');
        $dimensionFilter->setExpressions(array('[^?]'));

        // Create the DimensionFilterClauses
        $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setFilters(array($dimensionFilter));
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($dimensionCountry, $dimensionPath));
        $request->setMetrics(array($sessions));
        $request->setDimensionFilterClauses(array($dimensionFilterClause));
        $request->setOrderBys($ordering);
        $request->setPageSize(100);
        return $request;
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
//$mcAnalytics->parseTsvFile();
//$mcAnalytics->setAdClientId("313743502213-delb6cit3u4jrjvrsb4dsihpsoak2emm.apps.googleusercontent.com")
//        ->setAccountId("pub-2427907534283641")
//        ->earnings();