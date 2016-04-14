<?php
include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting City Switch...',"\n";

$origin_country=2;

$cities=array(
    array(436,'Ajman','ajman|=عجمان',55),
    array(333,'Sharjah','sharja|sharjah|الشارقة',54),
    array(2609,'Quwain','quwain|=القيوين|=القوين',56)
);

$index = $config['search_index'];
$total=0;

$db=new DB($config);
$stmt = $db->prepareQuery('update ad set city_id=?,title=\'\' where id=?');
$ad_stmt = $db->prepareQuery('update ad_city set city_id=? where ad_id=? and city_id=?');
$select_stmt = $db->prepareQuery('select city_id from ad where id = ?');
//$stmtNoPurpose = $db->prepareQuery('update ad set section_id=?,purpose_id=999,title=\'\' where id=?');

$sphinx = new SphinxClient();
$sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
$sphinx->SetConnectTimeout(1000);
$sphinx->SetServer($config['search_host'], $config['search_port']);
$sphinx->SetLimits(0, 1000, 1000);

foreach ($cities as $rec){
    switchCity($rec);
}
/*
//20
$x++;
$q='ترخيص|رخصة|رخصه';
switchCategory($q, 99, 63, 124);
switchCategory($q, 99, 114, 124);
*/

//$q='"ارض" -"قسيمة" -"جهاز"';
//switchCategory($q, 99, 63, 7);
//
//
//$q='محل';
//switchCategory($q, 99, 63, 5);

//$q='عماره';
//switchCategory($q, 99, 63, 8);

//$q='"منزل"|"بيت" -"خادم" -"كاميرا" -"شاب" -"عفش" -"ستائر" -"ستارة" -"متعهد"';
//switchCategory($q, 99, 63, 121);

//$q='"فيلتين"|"فلل"|"فيلل"|"فيلا"|"villa" -"قسيمة" -"طلب"';
//switchCategory($q, 99, 63, 131);



function switchCity($rec){
    global $stmt, $select_stmt, $ad_stmt, $sphinx, $index, $total, $origin_country;
    $sphinx->ResetFilters();
    $sphinx->SetFilter('hold', array(0));
    $sphinx->SetFilter('canonical_id', array(0));
    $sphinx->SetFilter('publication_id', array(1),true);
    $sphinx->SetFilter('country_id', array($origin_country));
    $sphinx->SetFilter('city_id', array(436,333,2609), true);
    echo "\n\n", "\tProcessing\t{$rec[1]}\t", $rec[0],"\n";
    $i=0;$j=0;
    $rs=$sphinx->Query("{$rec[2]}", $index);
    if (isset($rs['matches']) && count($rs['matches'])) {
        foreach ($rs['matches'] as $id => $match){
            echo $id, ' ', $rec[0],"\n";
            $select_stmt->execute(array($id));
            if( ($ad = $select_stmt->fetch(PDO::FETCH_ASSOC)) !== false){
                if ($stmt->execute(array($rec[0],$id))) {
                    if($ad_stmt->execute(array($rec[0],$id,$ad['CITY_ID']))){
                        echo 'pass';
                        $i++;
                    }else{
                        $j++;
                        echo '--fail';
                    }
                }
                else {
                    $j++;
                    echo '--fail';
                }
            }
            echo "\n";
        }
    }
    echo "\n", 'Processed: ',$i;
    echo "\n", 'Failed:    ',$j;
    echo "\n-------------------------------------------------------------";
    $total+=$i;
    
}


$db->close();
echo "\n\n",'Ad city switch Done with total: ', $total," changes\n\n";
?>
