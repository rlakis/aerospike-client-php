<?php

include_once dirname(__DIR__) . '/../config/cfg.php';
include_once dirname(__DIR__) . '/../deps/autoload.php';

include_once 'Schema.php';
Config::instance()->incModelFile('Db')->incModelFile('NoSQL')->incDataTableFile('Country');

$schema = \Core\Data\Schema::instance();

//echo \json_encode($schema->metadata(), JSON_PRETTY_PRINT);

$db=new \Core\Model\DB();
$rs=$db->get('select * from country');
//echo \json_encode($rs, JSON_PRETTY_PRINT);
echo $schema->metadata()['country']['bins']['id_2']['before']('lb');