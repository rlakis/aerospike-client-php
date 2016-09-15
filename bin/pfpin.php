<?php
ini_set('log_errors_max_len', 0);
$sandbox = (get_cfg_var('mourjan.server_id')=='99');
$logfile = '/var/log/mourjan/payfort.log';

if (!file_exists($logfile)) 
{
    $fh = @fopen($logfile, 'w');
    fclose($fh);
}
error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), json_encode($_POST).PHP_EOL), 3, $logfile);
?>