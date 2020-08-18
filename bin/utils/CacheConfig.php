<?php

include_once dirname(dirname(__DIR__)).'/config/cfg.php';
include_once dirname(dirname(__DIR__)).'/core/model/NoSQL.php';

$as=Core\Model\NoSQL::instance();
$globalSettings=$as->getBins($as->getConnection()->initKey(Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'settings'));
if ($globalSettings!==FALSE && isset($globalSettings['data'])) {
    foreach ($globalSettings['data'] as $key => $value) {
        $config[$key]=$value;
    }
    $config['modules']['ajax-number-info']=array('Bin',0);
}
$strFileContent='<?php' . PHP_EOL . '$config=' . var_export($config, true) . ';' . PHP_EOL;
file_put_contents(dirname(dirname(__DIR__)).'/config/shared.php', $strFileContent);