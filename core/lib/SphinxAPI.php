<?php
namespace Core\Lib;


include_once __DIR__.'/../model/Singleton.php';

class SphinxAPI extends \Core\Model\Singleton {
    const ID                = 'id';
    const UID               = 'user_id';
    const HOLD              = 'hold';
    const COUNTRY           = 'country';
    const CITY              = 'city';
    const CANONICAL         = 'canonical_id';

    const ROOT              = 'root_id';
    const SECTION           = 'section_id';
    const PURPOSE           = 'purpose_id';
    const LOCALITY          = 'locality_id';
    const TAG               = 'section_tag_id';
    const RTL               = 'rtl';
    const MEDIA             = 'media';
    const STARRED           = 'starred';
    const PUBLISHER_TYPE    = 'publisher_type';
    const FEATURED_TTL      = 'featured_date_ended';
    
    private \Manticoresearch\Client $client;
    
    protected function __construct() {
        $params=['host'=>'138.201.50.158', 'port'=>8308, 'retries'=>2, 'timeout'=>5, 'connection_timeout'=>1, 'persistent'=>true, 'transport'=>'Http'];
        $this->client=new \Manticoresearch\Client($params);  
        $search=$this->createSearch();
        //$rs=$search->search('hamra')->get();
        //print_r($rs->getTotal());
    }
    
    
    public function createSearch(string $index='ad') : \Manticoresearch\Search {
        $search=new \Manticoresearch\Search($this->client);
        $search->setIndex($index)->setSource([self::ID])->notFilter(self::HOLD, 'equals', 1);
        /*
                ->filter(SphinxAPI::HOLD, 'equals', [0])
                ->filter(self::CANONICAL, 'equals', [0])
                ->limit(25)
                ;
     */
        //var_dump($search);
        return $search;
    }
    
    
    public static function instance() : SphinxAPI {
        return static::getInstance();
    }
    
    
    
}