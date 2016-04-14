<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

function printAd($text){
    echo "\n-------------------------------------------------------------------------------------------------\n";
    echo "Text:\n",$text,"\n\n";
}

echo "\n-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "\n",'Here are the ads that are not tagged',"\n";
$db=new DB($config);

$sectionId=10;

$qTag='
select a.id, a.content from ad a
left join ad_tag t on t.ad_id = a.id
where a.hold = 0 and a.section_id = ? and a.canonical_id=0 and t.id is null
';
$tag_stmt=$db->prepareQuery($qTag);
$counter=0;
try{
    $tag_stmt->execute(array($sectionId));
    while (($row = $tag_stmt->fetch(PDO::FETCH_NUM)) !== false) {
        printAd($row[1]);
        $counter++;
    }
}catch(Exception $ex){
    echo $ex->getMessage(),"\n";
}
echo "-------------------------------------------------------------------------------------------------\n";            
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo 'Fetched ',$counter," records\n";
echo "-------------------------------------------------------------------------------------------------\n";
$db->close();
?>