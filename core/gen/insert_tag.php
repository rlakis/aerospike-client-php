<?php
include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting Tag Insertion process...',"\n";
$db=new DB($config);
/*
$ads=$db->queryResultArray('select a.* from ad a left join ad_user u on a.id = u.id where u.web_user_id=3165');
foreach($ads as $ad){
    $ad['CONTENT']= mb_ereg_replace("<span class='pn'>\+96170748724</span> ", '', $ad['CONTENT']);
    //echo $ad['CONTENT'];
    if ($db->queryResultArray('update ad set content = ? where id = ?', array($ad['CONTENT'], $ad['ID']))){
        echo 'pass',"\n";
        
    }    
}*/

$tagsToInsert=array(  
    array(49, 'en',
        'Peugeot RCZ'
        ,'rcz',
        '"rcz"|"ار سى زد"|"rtz"'
        ),
    array(49, 'ar',
        'بيجو ار سى زد'
        ,'rcz',
        '"rcz"|"ار سى زد"|"rtz"'
        )
    
    /*
    array(35, 'en',
        'Hyundai IX35'
        ,'ix35',
        '"ix35"|"ix 35"|"اي اكس 35"'
        ),
    array(35, 'ar',
        'هونداي اي اكس 35'
        ,'ix35',
        '"ix35"|"ix 35"|"اي اكس 35"'
        ),
    array(35, 'en',
        'Hyundai Veloster'
        ,'veloster',
        '"veloster"|"فيلوستر"|"فيلستر"|"فلوستر"|"فلستر"|"فوليستر"|"بولستر"'
        ),
    array(35, 'ar',
        'هيونداي فيلوستر'
        ,'veloster',
        '"veloster"|"فيلوستر"|"فيلستر"|"فلوستر"|"فلستر"|"فوليستر"|"بولستر"'
        ),      
    array(35, 'en',
        'Hyundai Visto'
        ,'visto',
        '"visto"|"فيستو"|"فستو"|"fisto"|"vesto"'
        ),
    array(35, 'ar',
        'هيونداي فيستو'
        ,'visto',
        '"visto"|"فيستو"|"فستو"|"fisto"|"vesto"'
        )
     * 
     */
    /*
    array(15, 'en',
        'Mercedes 600'
        ,'600',
        '"600"'
        ),
    array(15, 'ar',
        'مرسيدس 600'
        ,'600',
        '"600"'
        ),
    array(15, 'en',
        'Mercedes E63'
        ,'e63',
        '"e63"|"63"'
        ),
    array(15, 'ar',
        'مرسيدس اي 63'
        ,'e63',
        '"e63"|"63"'
        ),
    */
    
   /* array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(10, 'en',
        'BMW '
        ,'',
        ''
        ),
    array(10, 'ar',
        'بي ام '
        ,'',
        ''
        ),
    array(35, 'en',
        ''
        ,'',
        ''
        ),
    array(35, 'ar',
        ''
        ,'',
        ''
        ),*/
    
);
$stmt=$db->prepareQuery(
    "insert into section_tag (section_id, lang, name, uri, query_term, blocked) values (?,?,?,?,?,0)"
    );
    foreach ($tagsToInsert as $tag){
        if ($stmt->execute($tag)) 
            echo 'pass',"\n";
        else echo 'fail',"\n";
    }

$db->close();
echo 'Tags Insertion process Done!',"\n\n";
?>
