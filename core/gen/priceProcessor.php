<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

$priceRegex=array(
    1           =>  array(
        '/([0-9,\.]*?)(?:\s|)(?:\$|usd)/iu',//2000$|2000 $
        '/(?:\$)(?:\s|)([0-9,\.]*?)\s/u',//$2000|$ 2000
        '/(?:سعر|price)(?:\s|)([0-9,\.]*?)\s/iu'
    ),
    4           =>  array(
        '/(?:سعر|price|مطلوب|سوم|حد)(?:\s|)([0-9,\.]*?(?:الف|\sالف))\s/iu',
        '/([0-9,\.]*?(?:الف|\sالف|))(?:\s|)(?:\ريال|sar)/iu',
        '/([0-9,\.]*?(?:الف|\sالف))/iu',
    )
);

function detectPrice($text,$countryId){
    $price=0;
    global $priceRegex;
    if(isset($priceRegex[$countryId])){
        $matches=null;
        foreach($priceRegex[$countryId] as $rx){
            preg_match_all($rx,$text,$matches);
            if(isset($matches[1]) && count($matches[1])){
                foreach ($matches[1] as $pr){
                    if($pr>$price){
                        $price=$pr;
                    }
                }
                break;
            }
        }
    }
    if($price){
        if(preg_match('/الف/', $price)){
            $price=trim(preg_replace('/الف/', '',$price));
            $price=preg_replace('/(\.00$)/', '', $price);
            $price=preg_replace('/([^0-9])/i', '', $price);
            if($price<1000)$price*=1000;
        }else{
            $price=preg_replace('/(\.00$)/', '', $price);
            $price=preg_replace('/([^0-9])/i', '', $price);
        }
    }
    if($price){
        switch ($countryId){
            case 4:
                $price*=0.27;
                break;
            case 1:
            default:
                break;
        }
    }
    return (int)$price;
}
function detectYear($text){
    $year=0;
    $matches=null;
    preg_match_all('/\s(?:mod|model|modl|year|م|مودل|موديل|مودال|مدل|مديل|مدال)(?:\s|)([0-9]{2,4})\s/u', $text, $matches);
    if(isset($matches[1]) && count($matches[1])){
        foreach ($matches[1] as $yr){
            if($yr && strlen($yr)==3) $yr=0;
            if($yr && strlen($yr)==2) $yr='19'.$yr;
            $yr=(int)$yr;
            if($yr>=1980 && $yr<=2013){
                $year=$yr;
                break;
            }
        }
    }
    if(!$year){
        preg_match_all('/\s([0-9]{2,4})(?:\s|)(?:mod|model|modl|year|م|مودل|موديل|مودال|مدل|مديل|مدال)/u', $text, $matches);
        if(isset($matches[1]) && count($matches[1])){
            foreach ($matches[1] as $yr){
                if($yr && strlen($yr)==3) $yr=0;
                if($yr && strlen($yr)==2) $yr='19'.$yr;
                $yr=(int)$yr;
                if($yr>=1980 && $yr<=2013){
                    $year=$yr;
                    break;
                }
            }
        }
    }
    if(!$year){
        preg_match_all('/^([0-9]{2,4})\s/u', $text, $matches);
        if(isset($matches[1]) && count($matches[1])){
            foreach ($matches[1] as $yr){
                if($yr && strlen($yr)==3) $yr=0;
                if($yr && strlen($yr)==2) $yr='19'.$yr;
                $yr=(int)$yr;
                if($yr>=1980 && $yr<=2013){
                    $year=$yr;
                    break;
                }
            }
        }
    }
    if(!$year){
        preg_match_all('/\s([0-9]{4})\s/u', $text, $matches);
        if(isset($matches[1]) && count($matches[1])){
            foreach ($matches[1] as $yr){
                $yr=(int)$yr;
                if($yr>=1980 && $yr<=2013){
                    $year=$yr;
                    break;
                }
            }
        }
    }
    return $year;
}
function printAd($text,$price,$year){
    echo "\n-------------------------------------------------------------------------------------------------\n";
    echo "Text:\n",$text,"\n\n";
    echo "\tPrice:",$price;
    echo "\tYear:", $year;
}

echo "\n-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "\n",'Starting Price Processing on sample of ads...',"\n";
$db=new DB($config);

$countryId=1;
$debug=0;
$validHits=10;

$qInsert = 'update or insert into section_tag_price_range (country_id,section_tag_id,year_make,lower_price,upper_price,samples) values (?,?,?,?,?,?) matching(country_id,section_tag_id,year_make)';
$insert_stmt=$db->prepareQuery($qInsert);


$qTag='
select t.section_tag_id,s.name from ad_tag t 
left join section_tag s on s.id = t.section_tag_id 
where t.ad_id=?
';
$tag_stmt=$db->prepareQuery($qTag);

$sTag='
select id,query_term,name from section_tag where section_id=?
';
$get_tag_stmt=$db->prepareQuery($sTag);

$tagNaming=array();
$sectionTag = array();

$q='select a.id,a.content,a.section_id from ad a left join section s on s.id = a.section_id where a.country_id=? and s.root_id=2 and a.purpose_id=1 and a.canonical_id=0 ';
    //and date_added > current_date - 370';
$query_stmt=$db->prepareQuery($q);
$query_stmt->bindValue(1,$countryId,PDO::PARAM_INT);

$processed=0;
$collected=0;
$failed=0;
$errorPrice=0;
$notag=0;
try{
    $query_stmt->execute();
    if (($row = $query_stmt->fetch(PDO::FETCH_NUM)) !== false) {
        do {
            //$row[1]=  preg_replace('/[^0-9]\.[^0-9]/', ' . ', $row[1]);
            $price=detectPrice($row[1],$countryId);
            $year=detectYear($row[1]);
            if($price && $year){
                if($price < 1000){
                    //printAd($row[1], $price, $year);
                    $errorPrice++;
                }else{
                    $nPrice = $price % 500;
                    $price = $price - $nPrice;
                    $tag_stmt->execute(array($row[0]));
                    if (($tag = $tag_stmt->fetch(PDO::FETCH_NUM)) !== false) {
                        do{
                            $tagNaming[$tag[0]]=$tag[1];
                                    
                            if(!isset($sectionTag[$tag[0]])){
                                $sectionTag[$tag[0]]=array();
                            }
                            if(!isset($sectionTag[$tag[0]][$year])){
                                $sectionTag[$tag[0]][$year]=array();
                            }
                            if(!isset($sectionTag[$tag[0]][$year][$price])){
                                $sectionTag[$tag[0]][$year][$price]=0;
                            }
                            $sectionTag[$tag[0]][$year][$price]++;
                        }while( ($tag = $tag_stmt->fetch(PDO::FETCH_NUM)) !== false );
                        $collected++;
                    }else{
                        //printAd($row[1], $price, $year);
                        $notag++;
                        $get_tag_stmt->execute(array($row[2]));
                        if (($qt = $get_tag_stmt->fetch(PDO::FETCH_NUM)) !== false) {
                            $isMatch=0;
                            do{
                                $term = preg_replace('/"/', '\b', $qt[1]);
                                if(preg_match('/'.$term.'/iu', $row[1])){                                    
                                    $tagNaming[$qt[0]]=$qt[2];

                                    if(!isset($sectionTag[$qt[0]])){
                                        $sectionTag[$qt[0]]=array();
                                    }
                                    if(!isset($sectionTag[$qt[0]][$year])){
                                        $sectionTag[$qt[0]][$year]=array();
                                    }
                                    if(!isset($sectionTag[$qt[0]][$year][$price])){
                                        $sectionTag[$qt[0]][$year][$price]=0;
                                    }
                                    $sectionTag[$qt[0]][$year][$price]++;
                                    $isMatch++;
                                    if($isMatch==2)break;
                                }
                            }while( ($qt = $get_tag_stmt->fetch(PDO::FETCH_NUM)) !== false );
                            if($isMatch){
                                $notag--;  
                                $collected++;
                            }else{
                                //printAd($row[1], $price, $year);
                            }
                        }
                    }
                }
            }else{
                //printAd($row[1], $price, $year);
                $failed++;
            }
            //echo "\tPrice:",detectPrice('BMw model 2005 for sale price 20000$ and other price 38000$ for more info',1);
            $processed++;
        }while( ($row = $query_stmt->fetch(PDO::FETCH_NUM)) !== false );
    }
}catch(Exception $ex){
    echo $ex->getMessage(),"\n";
}

echo "\n-------------------------------------------------------------------------------------------------\n";            

foreach ($sectionTag as $tag => $years){
    ksort($years,SORT_ASC);
    foreach ($years as $year => $prices){
        $res='';
        
        $rePrices=array();
        foreach($prices as $key=>$hits){
            if($hits>=$validHits){
                $rePrices[$key]=$hits;
            }
        }
        
        $pcs = array_keys($rePrices);
        $cnt = count($pcs);
        
        $samples = 0;
        $lower=0;
        $upper=0;
        if($cnt>1){ 
            foreach ($rePrices as $vals){
                $samples += $vals;
            }
            sort($pcs,SORT_ASC);
            $lower=$pcs[0];
            $upper=$pcs[$cnt-1]+500;
        }elseif($cnt){
            $samples = $rePrices[$pcs[0]];
            $lower=$pcs[0]-500;
            $upper=$pcs[0]+500;
        }
        $res.="\t".$year.": ".$lower."$ - ".$upper."$\n";
        $res.="\t\tsamples: ".$samples."\n";
        if($debug && $samples && $lower && $upper){
            echo $tagNaming[$tag].":\n";
            echo $res;
        }
        if(!$debug && $samples && $lower && $upper){
            $insert_stmt->bindValue(1,$countryId,PDO::PARAM_INT);
            $insert_stmt->bindValue(2,$tag,PDO::PARAM_INT);
            $insert_stmt->bindValue(3,$year,PDO::PARAM_INT);
            $insert_stmt->bindValue(4,$lower,PDO::PARAM_INT);
            $insert_stmt->bindValue(5,$upper,PDO::PARAM_INT);
            $insert_stmt->bindValue(6,$samples,PDO::PARAM_INT);
            $insert_stmt->execute();
        }
    }
}


echo "\n-------------------------------------------------------------------------------------------------\n";            
echo "\n",'Processed ',$processed,"\n";
echo 'Collected ',$collected,"\n";
echo 'Failed ',$failed,"\n";
echo 'Price Err ',$errorPrice,"\n";
echo 'NoTag ',$notag,"\n\n";
echo "-------------------------------------------------------------------------------------------------\n";            
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";
echo "-------------------------------------------------------------------------------------------------\n";

$db->close();
?>