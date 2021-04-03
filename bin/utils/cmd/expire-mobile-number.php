<?php
use \Core\Model;

if (PHP_SAPI!=='cli') {  return;  }
if ($argc!==2) {
    echo 'Usage: /var/www/mourjan/bin/utils/cmd/expire-mobile-number.php MOBILE', "\n";
    return;
}

include_once __DIR__.'/../../../config/cfg.php';
Config::instance()->incModelFile('NoSQL');

$as=Core\Model\NoSQL::instance()->getConnection();
$number=\intval($argv[1]);
date_default_timezone_set("UTC"); 
echo $number, "\t", time(), "\n";

$records=[];
$where=\Aerospike::predicateEquals(Model\ASD\USER_MOBILE_NUMBER, $number);
$status=$as->query(\Core\Model\NoSQL::NS_USER, Model\ASD\TS_MOBILE, $where, 
        function ($record) use (&$matches, &$keys, &$records) {
            if (!isset($record['bins'][Model\ASD\USER_MOBILE_DATE_ACTIVATED])) {
                $record['bins'][Model\ASD\USER_MOBILE_DATE_ACTIVATED]=0;
            }
            if (!isset($record['bins'][Model\ASD\USER_DEVICE_UNINSTALLED])) {
                $record['bins'][Model\ASD\USER_DEVICE_UNINSTALLED]=0;
            }
                       
            $date=strtotime(date("Y-m-d H:i:s", $record['bins'][Model\ASD\USER_MOBILE_DATE_ACTIVATED]) . " +1 year");
            //echo $date, "\n";
            if ($date>time() && $record['bins'][Model\ASD\USER_DEVICE_UNINSTALLED]===0) {
                echo "Readable date: ".date("Y-m-d H:i:s",$date)."\n ";
                $records[]=$record['bins'];
            }
        });
        
print_r($records);

//NoSQL::instance()->mobileUpdate($this->getUID(), $phone_number, [Core\Model\ASD\USER_DEVICE_UNINSTALLED=>1]);
foreach ($records as $bins) {
    if ($bins[Core\Model\ASD\USER_DEVICE_UNINSTALLED]===0) {
        echo $bins[Model\ASD\USER_UID], "\t", $bins[Model\ASD\USER_MOBILE_NUMBER], "\n";
        
        var_dump(Core\Model\NoSQL::instance()->mobileUpdate($bins[Model\ASD\USER_UID], $bins[Model\ASD\USER_MOBILE_NUMBER], [Core\Model\ASD\USER_DEVICE_UNINSTALLED=>1]));
    }
}