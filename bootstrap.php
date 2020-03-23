<?php

$oldsite=\filter_input(\INPUT_GET, 'oldlook', \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>-1]])+0;

if ($oldsite===1) {
    \chroot("/home/www/mourjan");
}
else {
    \chroot("/var/www/mourjan");    
}
error_log(__DIR__);
include_once 'index.php';