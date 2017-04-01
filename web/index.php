<?php

$sess_id = filter_input(INPUT_GET, 'sid', FILTER_SANITIZE_STRING);
$sh = filter_input(INPUT_GET, 'sh', FILTER_VALIDATE_INT)+0;
$uid = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT)+0;

session_id($sess_id);

include_once get_cfg_var("mourjan.path") . '/config/cfg.php';
include_once get_cfg_var("mourjan.path") . '/core/model/User.php';
include_once get_cfg_var("mourjan.path") . '/core/model/Db.php';
include_once get_cfg_var("mourjan.path") . '/core/lib/MCSessionHandler.php';

use Core\Model\DB;

$handler = new MCSessionHandler($sh);
//session_set_save_handler($handler, true);
//session_start();

//error_log('SESSION ' . var_export($_SESSION, TRUE));

$db = new DB($config);

$user = new User($db, $config, NULL, 0);

if ($user->sysAuthById($uid)) 
{
    $user->info['app-user']=1;
    $user->update();

    $fp = stream_socket_client("tcp://io.mourjan.com:1515", $errno, $errstr, 30);
    if (!$fp) {
        error_log( "$errstr ($errno)<br />");
    } else {
        $out="bclogin|$sess_id";
        fwrite($fp, $out);
        fclose($fp);
    }
}


?>