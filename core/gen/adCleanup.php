<?php
include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting Ad cleanup...',"\n";

$index = $config['search_index'];
$total=0;

$db=new DB($config);
$stmt = $db->prepareQuery('update ad set section_id=?,title=\'\' where id=?');
//$stmtNoPurpose = $db->prepareQuery('update ad set section_id=?,purpose_id=999,title=\'\' where id=?');

$sphinx = new SphinxClient();
$sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
$sphinx->SetConnectTimeout(1000);
$sphinx->SetServer($config['search_host'], $config['search_port']);
$sphinx->SetLimits(0, 1000, 1000);
$sphinx->setSelect('purpose_id');

$rs=$db->queryResultArray('select * from classifier');
foreach ($rs as $rec){
    if ($rec['SECTION_ID'] && $rec['TO_SECTION_ID'] && $rec['TO_PURPOSE_ID']==0)
        switchCategory($rec);
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



function switchCategory($rec){
    global $stmt, $sphinx, $index, $total, $x;
    $sphinx->ResetFilters();
    $sphinx->SetFilter('hold', array(0));
    if($rec['ROOT_ID'])
        $sphinx->SetFilter('root_id', array($rec['ROOT_ID']));
    if($rec['SECTION_ID'])
        $sphinx->SetFilter('section_id', array($rec['SECTION_ID']));
    echo "\n\n", $rec['ID'], "\tProcessing\tSection\t", $rec['SECTION_ID'],
            "\n\tTo\t\tSection\t",$rec['TO_SECTION_ID'],"\n";
    $i=0;$j=0;
    $rs=$sphinx->Query("(@content {$rec['TERMS']})", $index);
    if (isset($rs['matches']) && count($rs['matches'])) {
        foreach ($rs['matches'] as $id => $match){
            echo $id, ' ',"\n";
            //echo $match['attrs']['purpose_id'];
            if ($stmt->execute(array($rec['TO_SECTION_ID'], $id))) {
                echo 'pass';
                $i++;
            }
            else {
                $j++;
                echo '--fail';
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
echo "\n\n",'Ad cleanup process Done with total: ', $total," changes\n\n";
?>
