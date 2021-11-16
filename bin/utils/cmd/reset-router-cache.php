<?php

if (PHP_SAPI!=='cli') {
    return;
}

include_once __DIR__.'/../../../config/cfg.php';
Config::instance()->incModelFile('Db')->incModelFile('Router');
\Core\Model\Router::instance()->cache(true);