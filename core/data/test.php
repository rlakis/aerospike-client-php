<?php

include_once dirname(__DIR__) . '/../config/cfg.php';
include_once dirname(__DIR__) . '/../deps/autoload.php';

include_once 'Schema.php';
Config::instance()->incModelFile('Db')->incModelFile('NoSQL');

$pk=\Core\Model\NoSQL::instance()->getConnection()->initKey('users', 'country', 2);
$bins=["id"=>2,"name_ar"=>"UAE","name_en"=>"Emirates","id_2"=>"AE","blocked"=>0,"longitude"=>53.847818,"latitude"=>23.424076,"code"=>971,"currency_id"=>"AED","locked"=>1,"unixtime"=>1580135779];
//return;



$schema = \Core\Data\Schema::instance();

//echo \json_encode($schema->metadata(), JSON_PRETTY_PRINT);

$db=new \Core\Model\DB();
/*
$rs=$db->get('select * from country');
$i=0;
foreach ($rs as $data) {    
    $bins=[];
    foreach ($data as $name=>$value) {
        $bins[strtolower($name)]=$value;
    }
    if (!$schema->countryMeta->prepare($bins)) {
        echo \json_encode ($bins), "\n", $schema->countryMeta->lastError, "\n";                
    }
    else {        
        //\Core\Model\NoSQL::instance()->addCountry($bins);
    }    
    $i++;
}
*/
cities($db);

function countries(\Core\Model\DB $db) : void {
    global $schema;
    $rs=$db->get('select * from country');
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        if (\Core\Model\NoSQL::instance()->addCountry($bins)!==\Aerospike::OK) {
            die($schema->countryMeta->lastError);
        }
    }    
}


function cities(Core\Model\DB $db) : void {
    global $schema;
    $rs=$db->get("SELECT r.ID, n.NAME name_ar, r.NAME name_en, r.URI, r.PARENT_ID, r.COUNTRY_ID, r.LATITUDE, r.LONGITUDE, r.LOC_AR_ID, r.LOC_EN_ID, r.BLOCKED, r.UNIXTIME 
                FROM F_CITY r
                left join NLANG n on n.ID=r.ID  and n.LANG='ar'");
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        if (\Core\Model\NoSQL::instance()->addCity($bins)!==\Aerospike::OK) {
            
            die($schema->cityMeta->lastError);
        }
    } 
    
    
}