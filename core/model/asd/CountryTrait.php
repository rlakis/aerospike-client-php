<?php

namespace Core\Model\ASD;

const TS_COUNTRY = 'country';

//const TS_COUNTRY_ID     = 'id';
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
    abstract public function exists($pk) : int;
    abstract public function write(array $pk, array $bins) : int;
    abstract protected function beforeInsert(string $table, array &$bins, array &$errors) : bool;

    
    private function asCountryKey(int $id) : array {
        $metadata=$this->metadata();
        return $this->getConnection()->initKey($metadata->namespace(), $metadata->name(), $id);
    }
    
    
    private function metadata() : \Core\Data\TableMetadata {
        return \Core\Data\Schema::instance()->countryMeta;
    }
    
    
    public function addCountry(array &$bins) : int {
        $id=0;
        $metadata=$this->metadata();
        if ($metadata->beforeInsert($bins) && $metadata->prepare($bins)) {
            
            if (isset($bins[\Core\Data\Schema::GENERIC_ID]) && $bins[\Core\Data\Schema::GENERIC_ID]>0) {
                $id=$bins[\Core\Data\Schema::GENERIC_ID];
            }
            else {
                $status=$this->genId('country_id', $id);
                if ($status===\Aerospike::OK) {
                    $bins[\Core\Data\Schema::GENERIC_ID]=$id;
                }
                
            }
            
            if ($id>0) {
                $pk=$this->asCountryKey($bins[\Core\Data\Schema::GENERIC_ID]);
                
                echo $bins[\Core\Data\Schema::GENERIC_ID], "\n";
                echo \json_encode($bins, JSON_PRETTY_PRINT);
                
                $status=$this->write($pk, $bins, 0, [\Aerospike::OPT_MAX_RETRIES => 2/*, \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_CREATE*/]);
            }
        }
        else {
            $status=\Aerospike::ERR_REQUEST_INVALID;
            echo $metadata->lastError, "\n";            
        }
                
        return $status;
    }
    
    //private function beforeInsert(string $table, array &$bins, array &$errors) : bool {
    //    parent::
    //}
}
