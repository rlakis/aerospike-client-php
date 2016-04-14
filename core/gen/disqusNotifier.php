<?php
$root_path=dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
require_once $config['dir'].'/bin/utils/DisqusNotifier.php';

$dsq=new Disqus($config);
$dsq->process();
?>