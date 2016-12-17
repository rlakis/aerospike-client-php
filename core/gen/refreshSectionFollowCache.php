<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting Section Follow Cache Refresh...',"\n";
$db=new DB($config);

$q='
select s.country_id, s.city_id, s.section_id, s.purpose_id
from section_follow s
where s.counter > 50
group by 1,2,3,4    
';

$follow=$db->queryResultArray($q, null,TRUE,PDO::FETCH_NUM);
echo 'Processing ',count($follow), " entry\n";
foreach($follow as $f){
    $db->getSectionFollowUp($f[0],$f[1],$f[2],$f[3],1);
}

$db->close();
echo 'Section Follow Cache Refresh Done!',"\n\n";
?>
