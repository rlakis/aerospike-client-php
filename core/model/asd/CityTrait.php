<?php

namespace Core\Model\ASD;

trait CityTrait {
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, int &$sequence) : int;
    abstract public function exists($pk) : int;
    abstract public function write(array $pk, array $bins) : int;
    abstract public function initLongKey(string $ns, string $set, int $id) : array;
    abstract public function initStringKey(string $ns, string $set, string $id) : array;
    abstract protected function beforeInsert(string $table, array &$bins, array &$errors) : bool;
    
    private function asCityKey(int $id) : array {
        $metadata=$this->cityMetadata();
        return $this->getConnection()->initKey($metadata->namespace(), $metadata->name(), $id);
    }
    
    
    private function cityMetadata() : \Core\Data\TableMetadata {
        return \Core\Data\Schema::instance()->cityMeta;
    }
        

    private function rootMetadata() : \Core\Data\TableMetadata {
        return \Core\Data\Schema::instance()->rootMeta;
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
                $pk=$this->initLongKey($metadata->namespace(), $metadata->name(), $bins[\Core\Data\Schema::GENERIC_ID]);                
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
    
    
    public function insertRecord(\Core\Data\TableMetadata $metadata, array &$bins) : int {
        $id=null;        
        if ($metadata->beforeInsert($bins) && $metadata->prepare($bins)) {
            $pkName=$metadata->primaryKeyBinNames()[0];
            if ($metadata->sequenceKey()!==''){                
                if (isset($bins[$pkName]) && $bins[$pkName]>0) {
                    $id=$bins[$pkName];
                }
                else {
                    $status=$this->genId($metadata->sequenceKey(), $id);
                    if ($status===\Aerospike::OK) {
                        $bins[$pkName]=$id;
                    }
                }
            }
            else {
                $id=$bins[$pkName];
            }

            if ($metadata->binField($pkName)->isHidden()) {
                unset($bins[$pkName]);
            }
            
            $pk=[];
            if (is_integer($id) && $id>0) {
                $pk=$this->initLongKey($metadata->namespace(), $metadata->name(), $id); 
            }
            else if (is_string($id) && !empty($id)) {
                $pk=$this->initStringKey($metadata->namespace(), $metadata->name(), $id); 
            }
            
            /*
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
                $pk=$this->initLongKey($metadata->namespace(), $metadata->name(), $bins[\Core\Data\Schema::GENERIC_ID]);                
                $status=$this->write($pk, $bins, 0, [\Aerospike::OPT_MAX_RETRIES => 2]);
            }*/
            if (!empty($pk)) {
                $status=$this->write($pk, $bins, 0, [\Aerospike::OPT_MAX_RETRIES => 2]);
            }
            else {
                $status=\Aerospike::ERR_REQUEST_INVALID;
                echo \json_encode($bins, JSON_PRETTY_PRINT);
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
