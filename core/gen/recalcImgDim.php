<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "\n",'Starting Image dimensions recalculations...',"\n";
flush();

$db=new DB($config);
$q='
select a.id,a.content,a.media from ad_user a 
left join web_users u on u.id = a.web_user_id 
where u.lvl != 5 and a.state=9
';


try{
$ads=$db->queryResultArray($q, null,TRUE,PDO::FETCH_NUM);

$update_stmt=$db->prepareQuery("update ad_user set content = ?, media=? where id = ?");

$len=  count($ads);
echo 'Processing ',$len, " entry\n";
flush();

$path_m = '/var/www/mourjan/web/repos/m/';
$path_d = '/var/www/mourjan/web/repos/d/';
$path_s = '/var/www/mourjan/web/repos/s/';
$path_l = '/var/www/mourjan/web/repos/l/';
$counter=0;
$resized=0;
$unset=0;
$mobile=0;
for($i=0;$i<$len;$i++){
    $content=json_decode($ads[$i][1],true);
    if(isset($content['pics']) && is_array($content['pics']) && count($content['pics'])){
        $dirty=0;
        $media=$ads[$i][2];
        foreach ($content['pics'] as $pic => $set){
            $s_check=file_exists($path_s.$pic)?1:0;
            $d_check=file_exists($path_d.$pic)?1:0;
            $l_check=file_exists($path_l.$pic)?1:0;
            if(!is_array($set)){
                if($l_check && $d_check && $s_check){
                    list($image_width, $image_height) = getimagesize($path_d.$pic);
                    if($image_height && $image_width){
                        echo $pic,' w=',$image_width,' h=',$image_height,' ad=',$ads[$i][0],"\n";
                        if($image_width < 648){
                            list($l_width, $l_height) = getimagesize($path_l.$pic);
                            if($l_height && $l_width && $l_width>648){
                                $new_height = 648 * $l_height / $l_width;
                                system('convert '.$path_l.$pic.' -resize 648x'.floor($new_height).'\! '.$path_d.$pic);
                                list($image_width, $image_height) = getimagesize($path_d.$pic);
                                echo "\t",'resized to '.$image_width.'x',$image_height,"\n";
                                $resized++;
                            }
                        }
                        $content['pics'][$pic]=array($image_width,$image_height);
                        $dirty=1;
                    }else{
                        echo $pic,' listing failed',"\n";
                    }
                }else {
                    echo $pic.' not found >> s=',$s_check,', d=',$d_check,', l=',$l_check,', ad=',$ads[$i][0],"\n";
                    $dirty=1;
                    unset($content['pics'][$pic]);
                } 
            }elseif($l_check){
                $m_check=file_exists($path_m.$pic)?1:0;
                $need_resize=0;
                if($m_check){
                    list($l_width, $l_height) = getimagesize($path_m.$pic);
                    if($l_width<300)$need_resize=1;
                }
                if(!$m_check || $need_resize){
                    list($l_width, $l_height) = getimagesize($path_l.$pic);
                    echo $pic,' w=',$l_width,' h=',$l_height,' ad=',$ads[$i][0],"\n";
                    if($l_height && $l_width && $l_width>300){
                        $new_height = 300 * $l_height / $l_width;
                        system('convert '.$path_l.$pic.' -resize 300x'.floor($new_height).'\! '.$path_m.$pic);
                        echo "\t",'mobile resize',"\n";
                        $mobile++;
                    }else{
                        system('cp -f '.$path_l.$pic.' '.$path_m.$pic);
                    }
                }
            }
        }
        if($dirty){
            $counter++;
            if(count($content['pics'])==0){
                unset($content['pics']);
                if(isset($content['pic_def'])) unset ($content['pic_def']);
                if($media==3)$media=2;
                $unset++;
            }
            $content = json_encode($content);
            $update_stmt->bindValue(1, $content);
            $update_stmt->bindValue(2, $media,PDO::PARAM_INT);
            $update_stmt->bindValue(3, $ads[$i][0], PDO::PARAM_INT);
            
            if ($update_stmt->execute()) {
                echo "\tFixed\n";
            }else{
                echo "\tFailed\n";
            }
            flush();
        }
    }
}

}catch(Exception $ex){
    echo $ex->getMessage(),"\n";
}


$db->close();
echo 'Resized: ', $resized,"\n";
echo 'Unset: ', $unset,"\n";
echo 'Mobile: ', $mobile,"\n";
echo 'Updated: ', $counter,"\n";
echo 'Done!',"\n\n";
?>
