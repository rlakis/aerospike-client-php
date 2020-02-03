<?php

include_once dirname(__DIR__) . '/../config/cfg.php';
include_once dirname(__DIR__) . '/../deps/autoload.php';

include_once 'Schema.php';
Config::instance()->incModelFile('Db')->incModelFile('NoSQL');

$schema = \Core\Data\Schema::instance();
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
//countriesDictionary($db);
asRoots();
        
function countries(\Core\Model\DB $db) : void {
    global $schema;
    $rs=$db->get('select * from country');
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        $bins[$schema::COUNTRY_CITIES]=[];
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


function roots(Core\Model\DB $db) : void {
    global $schema;
    $rs=$db->get("SELECT ID, NAME_AR, NAME_EN, URI, DIFFER_SECTION_ID DIFFER_SECTION, BLOCKED, UNIXTIME FROM ROOT order by 1");
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        if (\Core\Model\NoSQL::instance()->insertRecord($schema->rootMeta, $bins)!==\Aerospike::OK) {            
            die($schema->rootMeta->lastError);
        }
    }     
}


function sections(Core\Model\DB $db) : void {
    global $schema;
    $rs=$db->get("SELECT ID, NAME_AR, NAME_EN, URI, ROOT_ID, BLOCKED,  UNIXTIME FROM SECTION order by 1");
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        if (\Core\Model\NoSQL::instance()->insertRecord($schema->sectionMeta, $bins)!==\Aerospike::OK) {            
            die($schema->sectionMeta->lastError);
        }
    }     
}


function purposes(Core\Model\DB $db) : void {
    global $schema;
    $table=$schema->purposeMeta;
    $rs=$db->get("SELECT ID, NAME_AR, NAME_EN, URI, BLOCKED, UNIXTIME FROM PURPOSE order by 1");
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        if (\Core\Model\NoSQL::instance()->insertRecord($table, $bins)!==\Aerospike::OK) {            
            die($table->lastError);
        }
    }     
}


function urls(Core\Model\DB $db) : void {
    global $schema;
    $table=$schema->urlPathMeta;
    $rs=$db->get("SELECT r.PATH, r.COUNTRY_ID, r.CITY_ID, r.ROOT_ID, r.SECTION_ID, r.PURPOSE_ID, r.MODULE,
                trim(iif(r.TITLE_EN>'', r.TITLE_EN, SUBSTRING(r.title from POSITION(ascii_char(9) , r.title)+1 for 128))) name_en,
                trim(iif(r.TITLE_AR>'', r.title_ar, SUBSTRING(r.title from 1 for POSITION(ascii_char(9), r.title)))) name_ar
                FROM URI r
                where r.REFERENCE=0
                and r.BLOCKED=0");
    foreach ($rs as $data) {    
        $bins=[];
        foreach ($data as $name=>$value) { $bins[strtolower($name)]=$value; }
        $bins['name_en']=trim(preg_replace('/\t+/', '', $bins['name_en']));
        $bins['name_ar']=trim(preg_replace('/\t+/', '', $bins['name_ar']));
        if (\Core\Model\NoSQL::instance()->insertRecord($table, $bins)!==\Aerospike::OK) {            
            die($table->lastError);
        }
    }     
}


function countriesDictionary(Core\Model\DB $db) : void {
    global $schema;
    $as=Core\Model\NoSQL::instance();
    $rs=[];
    $bins=[];
    $status=$as->getConnection()->query(Core\Data\NS_MOURJAN, Core\Data\TS_COUNTRY, [], 
            function($row) use(&$rs) {        
                if ($row['bins'][\Core\Data\Schema::BIN_BLOCKED]===0) {
                    unset($row['bins'][\Core\Data\Schema::BIN_BLOCKED]);
                    $row['bins'][\Core\Data\Schema::COUNTRY_CITIES]=[];
                    $rs[]=$row['bins'];
                }        
            }, $bins );
    if ($status===\Aerospike::OK) {
        for ($i=0; $i<\count($rs); $i++) {
            $where=\Aerospike::predicateEquals(\Core\Data\Schema::BIN_COUNTRY_ID, $rs[$i][\Core\Data\Schema::BIN_ID]);
            $status=$as->getConnection()->query(Core\Data\NS_MOURJAN, Core\Data\TS_CITY, $where, 
                function($row) use(&$rs, $i) {
                    if ($row['bins'][\Core\Data\Schema::BIN_BLOCKED]===0) {
                        $rs[$i][\Core\Data\Schema::COUNTRY_CITIES][]=$row['bins'][\Core\Data\Schema::BIN_ID];
                    }
                }, [\Core\Data\Schema::BIN_ID, \Core\Data\Schema::BIN_BLOCKED]);
        }        
        //var_dump($rs);
        
        foreach ($rs as $bins) {
            $pk=$as->initLongKey(Core\Data\NS_MOURJAN, Core\Data\TS_COUNTRY, $bins[\Core\Data\Schema::BIN_ID]);
            $operations=[
                /*['op'=>\Aerospike::OP_LIST_CLEAR, 'bin'=>'cities'],*/
                ['op'=>\Aerospike::OP_LIST_INSERT_ITEMS, 'bin'=>\Core\Data\Schema::COUNTRY_CITIES, 'val'=>$bins[\Core\Data\Schema::COUNTRY_CITIES]]
            ];
            $ret=[];
            $status = $as->setBins($pk, [\Core\Data\Schema::COUNTRY_CITIES=>$bins[\Core\Data\Schema::COUNTRY_CITIES]]);
            echo $status, "\t", $as->getConnection()->error(), "\n";
            //if ($as->insertRecord($table, $bins)!==\Aerospike::OK) {            
            //   die($table->lastError);
            //}
        }
    }
    
    

    //$status = $this->getConnection()->query(\Core\Model\NoSQL::NS_USER, TS_PROFILE, $where, function ($_record) use (&$record) {$record=$_record;}, $bins);
}



function asRoots() : void {
    $as=Core\Model\NoSQL::instance();
    $rs=[];
    $status=$as->getConnection()->query(Core\Data\NS_MOURJAN, Core\Data\TS_ROOT, [], 
            function($row) use(&$rs) {        
                if ($row['bins'][\Core\Data\Schema::BIN_BLOCKED]===0) {
                    unset($row['bins'][\Core\Data\Schema::BIN_BLOCKED]);
                    $rs[$row['bins'][\Core\Data\Schema::BIN_ID]]=$row['bins'];
                }        
            });
    if ($status===\Aerospike::OK) {
        \asort($rs);
        $pk=$as->initStringKey(Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'roots');
        $as->setBins($pk, ['data'=>$rs]);
    }
    
}