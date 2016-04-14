<?php
die();
include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting Tag Fix process...',"\n";
$db=new DB($config);
$tags = $db->queryResultArray('select * from section_tag');
$stmt=$db->prepareQuery(
    "update section_tag set query_term=? where id=?"
    );
foreach ($tags as $tag){
    $result='';
    $terms=$tag['QUERY_TERM'];
    $terms=  explode('|', $terms);
    $i=0;
    foreach ($terms as $term){
        $term=trim($term);
        if($term) {
            if($i) $result.='|';
            if (substr($term, 0,1)!='"') $term='"'.$term.'"';
            $result.=$term;
            $i++;
        }
    }
    $stmt->execute(array($result,$tag['ID']));
}

$db->close();
echo 'Tag Fix process Done!',"\n\n";
?>
