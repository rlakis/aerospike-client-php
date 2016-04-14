<?php

if (get_cfg_var('mourjan.server_id') == '1') {
    require_once '/var/www/dev.mourjan/config/cfg.php';
} else {
    require_once '/home/www/mourjan/config/cfg.php';
}

include_once $config['dir'].'/core/model/Db.php';

require_once("lib/class.upload.php");

$uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;
$uuid = filter_input(INPUT_GET, 'uuid', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
$ft = filter_input(INPUT_GET, 'ft', FILTER_VALIDATE_INT)+0;
/*
 * 1: ad thumbnail
 * 
 * 100: profile thumbnail
 * 101: profile high resolution
 */

if ($uid==0) {
    echo "Invalid user id";
    return;
}

if (empty($uuid)) {
    echo "Invalid identifier";
    return;    
}


$path="/var/www/dev.mourjan/web/repos/pp";

$success = false;

if (isset($_FILES['userfile'])) {

        
    $handle = new Upload($_FILES['userfile']);
    
    if ($handle->uploaded) {
        
        //$filename = time();\
        //var_dump($_FILES);
        $filename = $uid . '-' . $ft . '-' . time();
    	$handle->file_new_name_body = $filename;
    	$handle->file_overwrite = true;
        $handle->Process($path);
        
        // we check if everything went OK
        if ($handle->processed) {
            $success = true;
            $extension=".".$handle->file_src_name_ext;
            //list($image_width, $image_height) = getimagesize($path."/L".$filename.$extension);
            
            if ($ft==100 || $ft==101) {
                $db = new DB($config);
                $uq=$db->queryResultArray("select * from web_users where id = {$uid}");
                if ($uq && is_array($uq) && !empty($uuid) && count($uq)==1) {
                    $opts = json_decode($uq[0]['OPTS']);
                    if (!isset($opts->iphone)) {
                        $opts->iphone = new stdClass();
                    }
                    if ($uuid==$uq[0]['USER_EMAIL'] || $uuid==$uq[0]['IDENTIFIER'] || (isset($opts->iphone->uuid) && $opts->iphone->uuid==$uuid) ) {
                        if ($ft==100) {
                            $opts->iphone->profile_thumbnail=$filename;
                        } else {
                            $opts->iphone->profile_image=$filename;
                        }
                        
                        $db->queryResultArray(
                        "update web_users set opts=? where id=?", [json_encode($opts), $uid], TRUE);
                    } else {
                        echo "Invalid user info!!!";
                        
                    }                    
                }
            }
        }

    } 
}

if ($success) {  
    echo "{$filename}{$extension}";
}
else {
    echo $handle->error;    
}

?>