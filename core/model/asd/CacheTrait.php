<?php

namespace Core\Model\ASD;

//const TS_CACHE = 'cache';

const CACHE_DATA    = 'data';

trait CacheTrait {
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, int &$sequence) : int;
    abstract public function getRecord(array $pk, ?array &$record, array $bins=[]) : int;
    
    private array $cache;
    
    private function initCacheKey(string $label) : array {
        return $this->getConnection()->initKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
    }

    
    public function getCacheData(string $label, ?array &$record) : int {
        if (isset($this->cache[$label])) {
            $record=$this->cache[$label];
            return \Aerospike::OK;
        }
        $pk=$this->initCacheKey($label);
        $status=$this->getRecord($pk, $record, [CACHE_DATA]);
        if ($status===\Aerospike::OK) {
            $record=$record['data'];
            $this->cache[$label]=$record;
        }
        return $status;
    }
    
    
    public function getCacheMulti(array $labels, ?array &$records) : int {
        $keys=[];
        $rKeys=[];
        $records=[];
        foreach ($labels as $k=>$label) {
            if (isset($this->cache[$label])) {
                $records[$k]=$this->cache[$label];
                continue;
            }
            $keys[$k]=$this->initCacheKey($label);    
            $rKeys[$label]=$k;
        }
        if (!empty($keys)) {
            $status=$this->getConnection()->getMany(\array_values($keys), $recs);
            if ($status===\Aerospike::OK) {            
                foreach ($recs as $record) {
                    if (isset($record['bins']['data'])) {
                        $id=$record['key']['key'];                    
                        $records[$rKeys[$id]]=$record['bins']['data'];
                        $this->cache[$id]=$record['bins']['data'];
                    }
                }
            }
            return $status;
        }
        
        return \Aerospike::OK;
    }

}
