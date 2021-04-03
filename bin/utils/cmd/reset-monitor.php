<?php
if (PHP_SAPI!=='cli') {
    return;
}

include_once __DIR__.'/../../../config/cfg.php';
Config::instance()->incModelFile('NoSQL')->incModelFile('Db')->incLibFile('MCSessionHandler')->incLibFile('Logger');

$as=Core\Model\NoSQL::instance()->getConnection();
$as->scan(Core\Model\NoSQL::NS_USER, 'services', function ($record) use ($as) {
    $pk=$as->initKey($record['key']['ns'], $record['key']['set'], $record['key']['digest'], true);
    $as->put($pk, ['message'=>'', 'success'=>0, 'failure'=>0]);
});
$as->close();