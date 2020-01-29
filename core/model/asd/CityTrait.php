<?php

namespace Core\Model\ASD;

trait CityTrait {
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, int &$sequence) : int;
    abstract public function exists($pk) : int;
    abstract public function write(array $pk, array $bins) : int;
    abstract protected function beforeInsert(string $table, array &$bins, array &$errors) : bool;

    private function asCityKey(int $id) : array {
        $metadata=$this->cityMetadata();
        return $this->getConnection()->initKey($metadata->namespace(), $metadata->name(), $id);
    }
    
    
    private function cityMetadata() : \Core\Data\TableMetadata {
        return \Core\Data\Schema::instance()->cityMeta;
    }
    
    
    public function addCity(array &$bins) : int {
        $id=0;
        $metadata=$this->cityMetadata();
        if ($metadata->beforeInsert($bins) && $metadata->prepare($bins)) {
            
            if (isset($bins[\Core\Data\Schema::GENERIC_ID]) && $bins[\Core\Data\Schema::GENERIC_ID]>0) {
                $id=$bins[\Core\Data\Schema::GENERIC_ID];
            }
            else {
                $status=$this->genId($metadata->sequenceKey(), $id);
                if ($status===\Aerospike::OK) {
                    $bins[\Core\Data\Schema::GENERIC_ID]=$id;
                }
                
            }
            
            if ($id>0) {
                $pk=$this->asCityKey($bins[\Core\Data\Schema::GENERIC_ID]);                
                //echo $bins[\Core\Data\Schema::GENERIC_ID], "\n";                               
                $status=$this->write($pk, $bins, 0, [\Aerospike::OPT_MAX_RETRIES => 2/*, \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_CREATE*/]);
            }
        }
        else {
            echo \json_encode($bins, JSON_PRETTY_PRINT);
            $status=\Aerospike::ERR_REQUEST_INVALID;
            echo $metadata->lastError, "\n";            
        }
                
        return $status;
    }
    
}
