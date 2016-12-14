<?php
include_once get_cfg_var('mourjan.path'). '/core/lib/MCSessionHandler.php';
$handler = new MCSessionHandler(true);
session_set_save_handler($handler, true);

session_start();

include_once '../config/cfg.php';
include_once '../core/model/Db.php';
require_once("../core/lib/class.upload.php");

$image_ok = false;
$thumb_ok = false;
$mobile_ok = false;
$widget_ok = false;
$handle = null;

$SKEY = session_id().$config['site_key'];

$ext = ( (isset($_GET['t'])) ? strtolower($_GET['t']) : '');
//if($ext && !in_array($ext, array('png','jpg','jpeg','gif')))
//    $_POST['pic_size'] = 0;

if ($_SESSION[$SKEY]['info']['id'] && (isset($_POST['pic_size']) && $_POST['pic_size']) && isset($_SESSION[$SKEY]['pending']['post']['id']) && $_SESSION[$SKEY]['pending']['post']['id']) {
    $pic_name = $_POST['pic_name'];
    if($ext) $pic_name.='.'.$ext;
    $pic_upload_file = $_POST['pic_path'];
    
    $userId = $_SESSION[$SKEY]['info']['id'];
    
    $groupId = floor($userId/10000);
    
    $userId = 'p'.$groupId .'/' . $userId;
    
    $id = $_SESSION[$SKEY]['pending']['post']['id'];
    $adContent = json_decode($_SESSION[$SKEY]['pending']['post']['content'], true);
    if (isset($adContent['pics']) && count($adContent['pics']) > 4) {
        //do nothing
    } else {
                
        $picIndex = 0;
        if (isset($adContent['pic_idx'])) {
            $picIndex = $adContent['pic_idx'];
        }
        $path = $config['dir'] . '/web/repos/';
        
        
        $tempName = $pic_upload_file;        
        $_size = @getimagesize($tempName);
        
        
        if (is_array($_size) && $_size[0] && $_size[1]) {
            
            
            $handle = new Upload($pic_upload_file);
            $handle->mime_check = true;
            $handle->allowed = array('image/*');
            $handle->forbidden = array('application/*');
            
            if ($handle->uploaded) {
                if(!file_exists($path.'l/'.$userId.'/')){
                    mkdir($path.'l/'.$userId.'/',0777, true);
                    mkdir($path.'d/'.$userId.'/',0777, true);
                    mkdir($path.'m/'.$userId.'/',0777, true);
                    mkdir($path.'s/'.$userId.'/',0777, true);
                }                
                
                $filename = $id . "_" . $picIndex++;
                $handle->file_new_name_body = $filename;
                $handle->file_overwrite = true;
                if($handle->file_src_name_ext==''){
                    $ectension = '';
                    preg_match('/\.([^\.]*$)/', $pic_name, $extension);
                    if (is_array($extension) && sizeof($extension) > 0) {
                        $handle->file_src_name_ext = strtolower($extension[1]);
                    }
                }
                
                $handle->Process($path . 'l/'.$userId.'/');
                if ($handle->processed) {
                    $image_ok = true;
                    $extension = '.' . $handle->file_src_name_ext;
                    list($image_width, $image_height) = getimagesize($path . 'l/'.$userId.'/' . $filename . $extension);
                } else {
                    @unlink($tempName);
                }
                if ($image_ok) {
                    $handle->file_new_name_body = $filename;
                    $handle->file_overwrite = true;
                    $handle->image_resize = true;
                    if ($image_width > 120) {
                        $handle->image_ratio_y = true;
                        $handle->image_x = 120;
                    } else {
                        $handle->image_ratio_y = true;
                        $handle->image_x = $image_width;
                    }
                    $handle->Process($path . 's/'.$userId.'/');
                    if ($handle->processed) {
                        $thumb_ok = true;
                    }

                    $handle->file_new_name_body = $filename;
                    $handle->file_overwrite = true;
                    $handle->image_resize = true;
                    if ($image_width > 300) {
                        $handle->image_ratio_y = true;
                        $handle->image_x = 300;
                    } else {
                        $handle->image_ratio_y = true;
                        $handle->image_x = $image_width;
                    }
                    $handle->Process($path . 'm/'.$userId.'/');
                    if ($handle->processed) {
                        $mobile_ok = true;
                    }

                    $handle->file_new_name_body = $filename;
                    $handle->file_overwrite = true;
                    $handle->image_resize = true;
                    if ($image_width > 648) {
                        $handle->image_ratio_y = true;
                        $handle->image_x = 648;
                    } else {
                        $handle->image_ratio_y = true;
                        $handle->image_x = $image_width;
                    }
                    $handle->Process($path . 'd/'.$userId.'/');
                    if ($handle->processed) {
                        list($image_width, $image_height) = getimagesize($path . 'd/'.$userId.'/' . $filename . $extension);
                        $widget_ok = true;
                    }
                }
            } else {
                @unlink($tempName);
            }
        } elseif ($tempName) {
            @unlink($tempName);
        }
    }
}
    
if ($image_ok && $thumb_ok && $mobile_ok && $widget_ok) {

    $filename .= $extension;
    
    if ($config['server_id'] > 1) {
        $ssh = ssh2_connect('h1.mourjan.com', 22);
        if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
            
            $sftp = ssh2_sftp($ssh);
            $isdir = is_dir('ssh2.sftp://'.$sftp.'/var/www/mourjan/web/repos/l/'.$userId.'/');
            if(!$isdir){
                ssh2_sftp_mkdir($sftp,'/var/www/mourjan/web/repos/l/'.$userId.'/',0777, true);
                ssh2_sftp_mkdir($sftp,'/var/www/mourjan/web/repos/d/'.$userId.'/',0777, true);
                ssh2_sftp_mkdir($sftp,'/var/www/mourjan/web/repos/m/'.$userId.'/',0777, true);
                ssh2_sftp_mkdir($sftp,'/var/www/mourjan/web/repos/s/'.$userId.'/',0777, true);
            }
            
            if (!ssh2_scp_send($ssh, $path . 'l/'.$userId.'/' . $filename, '/var/www/mourjan/web/repos/l/'.$userId.'/' . $filename, 0664)) {
                $image_ok = FALSE;
                @unlink($path . 'l/'.$userId.'/' . $filename);
            } elseif (!ssh2_scp_send($ssh, $path . 's/'.$userId.'/' . $filename, '/var/www/mourjan/web/repos/s/'.$userId.'/' . $filename, 0664)) {
                $image_ok = FALSE;
                @unlink($path . 'l/'.$userId.'/' . $filename);
                @unlink($path . 's/'.$userId.'/' . $filename);
            } elseif (!ssh2_scp_send($ssh, $path . 'd/'.$userId.'/' . $filename, '/var/www/mourjan/web/repos/d/'.$userId.'/' . $filename, 0664)) {
                $image_ok = FALSE;
                @unlink($path . 'd/'.$userId.'/' . $filename);
                @unlink($path . 'l/'.$userId.'/' . $filename);
                @unlink($path . 's/'.$userId.'/' . $filename);
            } elseif (!ssh2_scp_send($ssh, $path . 'm/'.$userId.'/' . $filename, '/var/www/mourjan/web/repos/m/'.$userId.'/' . $filename, 0664)) {
                $image_ok = FALSE;
                @unlink($path . 'm/'.$userId.'/' . $filename);
                @unlink($path . 'd/'.$userId.'/' . $filename);
                @unlink($path . 'l/'.$userId.'/' . $filename);
                @unlink($path . 's/'.$userId.'/' . $filename);
            }
        } else {
            $image_ok = FALSE;
        }
    }
}

if ($image_ok && $thumb_ok && $mobile_ok && $widget_ok) {
    if (!isset($adContent['pics'])) {
        $adContent['pics'] = array();
    }
    $adContent['pics'][$userId.'/'.$filename] = array($image_width, $image_height);
    $adContent['pic_idx'] = $picIndex;
    $isDefault = false;
    if (count($adContent['pics']) == 1) {
        $adContent['pic_def'] = $userId.'/'.$filename;
        $isDefault = true;
    }
    if (isset($adContent['extra']['p']))
        $adContent['extra']['p'] = 1;
    
    $hasVideo=(isset($adContent['video']) && is_array($adContent['video']) && count($adContent['video'])) ?  1 : 0;
    $hasPics=(isset($adContent['pics']) && is_array($adContent['pics']) && count($adContent['pics'])) ?  1 : 0;
    $media=0;
    if ($hasVideo && $hasPics){
        $media=3;
    }elseif($hasVideo){
        $media=2;
    }elseif($hasPics){
        $media=1;
    }    

    $_SESSION[$SKEY]['pending']['post']['content'] = json_encode($adContent);
    
    $db = new DB($config);
    $q='update ad_user set
        content=?,media=? where id=? ';
    if ($_SESSION[$SKEY]['info']['level']!=9) $q.='and web_user_id+0=?';
    $stmt=$db->getInstance()->prepare($q);
    $stmt->bindValue(1,$_SESSION[$SKEY]['pending']['post']['content'],PDO::PARAM_STR);
    $stmt->bindValue(2,$media,PDO::PARAM_INT);
    $stmt->bindValue(3,$_SESSION[$SKEY]['pending']['post']['id'],PDO::PARAM_INT);    
    if ($_SESSION[$SKEY]['info']['level']!=9)$stmt->bindValue(4,$_SESSION[$SKEY]['info']['id'], PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        if($ext){
            echo $userId.'/'.$filename;
        }else{
            ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(<?= '"' . $userId.'/'.$filename . '"' ?>);</script><?php
        }
    
    }else{
        if($ext){
            ?>0<?php
        }else{            
            ?><script type="text/javascript" language="javascript" defer>top.uploadCallback();</script><?php
        }
        
    }
}
else {
    if ($handle){
        error_log($handle->error);
    }else{
        error_log('no user session');
    }
    if($ext){
        ?>0<?php
    }else{    
        ?><script type="text/javascript" language="javascript" defer>top.uploadCallback();</script><?php    
    }
}
?>