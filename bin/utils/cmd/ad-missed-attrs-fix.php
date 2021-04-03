<?php
use Core\Model\DB;

if (PHP_SAPI!=='cli') { return; }

include_once __DIR__.'/../../../config/cfg.php';
include_once __DIR__.'/../../../deps/autoload.php';
Config::instance()->incModelFile('Db')->incModelFile('Ad')->incModelFile('Content')->incLibFile('MCSaveHandler')->incLibFile('Logger');


$bad=[];
$fb=new DB(false);
$st=$fb->prepareQuery("select id, section_id, purpose_id, state, web_user_id, content from ad_user where state=7");
$st->execute();
if (($row=$st->fetch(\PDO::FETCH_NUM)) !== false) {
    do {
        $content=\json_decode($row[5], true);
        if (!isset($content['attrs'])) {
            $content[Core\Model\Content::ID]=$row[0];
            $content[Core\Model\Content::SECTION_ID]=$row[1];
            $content[Core\Model\Content::PURPOSE_ID]=$row[2];
            $content[Core\Model\Content::STATE]=$row[3];
            $content[Core\Model\Content::UID]=$row[4];
            
            $bad[$row[0]]=$content;
        }
        if (count($bad)===100)            break;
    } while($row=$st->fetch(\PDO::FETCH_NUM));
}

$fb->closeStatement($st);

$st=$fb->prepareQuery('UPDATE ad_user set content=? where id=?');

$normalizer=new MCSaveHandler;
foreach ($bad as $id => $content) {
    $normalized=$normalizer->getFromContentObject($content, false);
    if ($normalized!==false) {
        $content[Core\Model\Content::ATTRIBUTES]=$normalized[Core\Model\Content::ATTRIBUTES];
        $content[Core\Model\Content::USER_LOCATION]=$normalized[Core\Model\Content::USER_LOCATION];
        $content[Core\Model\Content::AD_COUNTRY]=$normalized[Core\Model\Content::AD_COUNTRY];
        $content[Core\Model\Content::IP_COUNTRY]=$normalized[Core\Model\Content::IP_COUNTRY];
        $content[Core\Model\Content::MOBILE_COUNTRY]=$normalized[Core\Model\Content::MOBILE_COUNTRY];
        
        var_dump($content);
        $fb->executeStatement($st, [\json_encode($content), $id]);
        //$st->bindValue(1, \json_encode($content), \PDO::PARAM_STR);
        //$st->bindValue(2, $id, \PDO::PARAM_INT);
        //$st->execute();
    }
}
$st=null;
$fb->commit();
$fb->close();