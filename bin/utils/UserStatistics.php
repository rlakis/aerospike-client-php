<?php

include_once 'Util.php';

class UserStatistics extends Util {

    public function ads() {
        $ad_ids = $this->fb->queryAsHashMap("select id, 'adimp-'||id ikey from ad where hold=0");
        $cache=array();
        foreach ($ad_ids as $id => $values) {
            $cache[] = $values['IKEY'];
            if (count($cache)==100) {
                $res = $this->fb->mc->GetMulti($cache);
                if ($this->fb->mc->getResultCode()==Memcached::RES_SUCCESS) {
                    print_r($res);
                }
                $cache = array();
            }
        }
    }
}

$mem2db = new UserStatistics();
$mem2db->ads();

?>
