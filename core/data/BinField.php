<?php

namespace Core\Data;
include_once 'Schema.php';

class BinField {
    protected string $name;
    protected int $dataType;
    protected int $length;
    protected bool $required;
    protected bool $nullable;
    protected bool $nullTerminated;
    protected string $description;
    
    private int $defaultInt;
    private float $defaultDouble;
    private string $defaultString;
    
    private function __construct() {
        $this->nullTerminated=true;       
    }
    
    
    public static function create() : BinField {
        return new BinField;
    }
    
    
    public function setDataType(int $type) : BinField {
        $this->dataType=$type;
        if ($type===Schema::TYPE_INTEGER) {
            $this->length=4;
        }
        else if ($type===Schema::TYPE_LONG) {
            $this->length=8;
        }
        else if ($type===Schema::TYPE_BOOLEAN) {
            $this->length=1;
        }
        else if ($type===Schema::TYPE_DOUBLE) {
            $this->length=16;
        }
        return $this;
    }
    
    
    public function setLength(int $len) : BinField {
        $this->length=$len;
        return $this;
    }
    
    
    public function setDefaultInt(int $value) : BinField {
        return $this;
    }
    
    
    public function setDescription(string $desc) : BinField {
        $this->description=$desc;
        return $this;
    }
}
