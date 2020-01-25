<?php

namespace Core\Model\ASD;

const TS_COUNTRY = 'country';

const TS_COUNTRY_ID     = 'id';
const COUNTRY_NAME_AR   = 'name_ar';
const COUNTRY_NAME_EN   = 'name_en';
const COUNRTY_ID_ALPHA  = 'id_2';
const COUNTRY_BLOCKED   = 'blocked';
const COUNTRY_LATITUDE  = 'latitude';
const COUNTRY_LONGITUDE = 'longitude';
const COUNTRY_CODE      = 'code';
const COUNTRY_CURRENCY  = 'currency_id';
const COUNTRY_LOCKED    = 'locked';
const COUNTRY_LUT       = 'unixtime';

const COUNTRY_PK        = 'id';

trait CountryTrait {
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, int &$sequence) : int;
    abstract protected function beforeInsert(string $table, array &$bins, array &$errors) : bool;


    private function asKey(int $id) : array {
        return $this->getConnection()->initKey(\Core\Model\NoSQL::NS_USER, TS_COUNTRY, $id);
    }
    
    
    public function addCountry(array &$bins) : int {
        $id=0;
        
        $status=$this->genId('country_id', $id);
        if ($status===\Aerospike::OK) {
            
        }
        return $status;
    }
    
    //private function beforeInsert(string $table, array &$bins, array &$errors) : bool {
    //    parent::
    //}
}
