<?php

namespace Core\Data;
include_once 'Schema.php';

class BinField {
    protected string $name;
    protected int $dataType;
    protected int $length;
    protected bool $required;
    protected bool $nullable;
    protected bool $toUpper;
    protected string $description;
    
    private int $defaultInt;
    private float $defaultDouble;
    private string $defaultString;

    private int $minIntValue;
    private int $maxIntValue;

    private float $minDoubleValue;
    private float $maxDoubleValue;
    
    private int $minStringLength;
    
    public string $lastError;


    private function __construct(string $binName) {
        $this->name=\trim($binName);
        $this->nullable=false;
        $this->required=false;
        $this->toUpper=false;
        $this->defaultInt=0;
        $this->defaultDouble=0;
        $this->defaultString='';
        
        $this->minIntValue=PHP_INT_MIN;
        $this->maxIntValue=PHP_INT_MAX;
        
        $this->minDoubleValue=-PHP_FLOAT_MAX;
        $this->maxDoubleValue=PHP_FLOAT_MAX;
        
        $this->minStringLength=0;
    }
    
    
    public static function create(string $binName) : BinField {
        return new BinField($binName);
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
            $this->maxIntValue=1;
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
    
    
    public function setNullable(bool $value) : BinField {
        $this->nullable=$value;
        return $this;
    }
    

    public function setRequired(bool $value) : BinField {
        $this->required=$value;
        return $this;
    }

    
    public function setDefaultInt(int $value=0) : BinField {
        $this->defaultInt=$value;
        return $this;
    }
    
    
    public function setDefaultString(string $value='') : BinField {
        $this->defaultString=$value;
        return $this;
    }
    
    
    public function setMinIntValue(int $min) : BinField {
        $this->minIntValue=$min;
        return $this;
    }
    
    
    public function setValidIntRange(int $min, int $max) : BinField {
        $this->minIntValue=$min;
        $this->maxIntValue=$max;
        return $this;
    }

    
    public function setValidFloadRange(float $min, float $max) : BinField {
        $this->minDoubleValue=$min;
        $this->maxDoubleValue=$max;
        return $this;
    }


    public function setMinStringLength(int $min) : BinField {
        $this->minStringLength=$min;
        return $this;
    }
    
    public function setToUpperCase(bool $value) : BinField {
        $this->toUpper=$value;
        return $this;
    }
    
    public function setDescription(string $desc) : BinField {
        $this->description=$desc;
        return $this;
    }
    
    
    public function name() : string {
        return $this->name;
    }


    public function prepare(&$value) : bool {
        $this->lastError='';
        switch ($this->dataType) {
            case Schema::TYPE_INTEGER:
                if (!is_int($value)) {
                    $this->lastError='Not a valid int type value for '.$this->name;
                    return false;
                }
                if ($value<$this->minIntValue||$value>$this->maxIntValue) {
                    $this->lastError='value is out of range given ' .$value. ' valid range ' .$this->minIntValue.'..'.$this->maxIntValue.' for int '.$this->name;
                    return false;
                }
                return true;
                
            case Schema::TYPE_LONG:
                if (!is_long($value)) {
                    $this->lastError='Not a valid long type value for '.$this->name;
                    return false;
                }
                if ($value<$this->minIntValue||$value>$this->maxIntValue) {
                    $this->lastError='value is out of range given ' .$value. ' valid range ' .$this->minIntValue.'..'.$this->maxIntValue.' for long '.$this->name;
                    return false;
                }
                return true;

            case Schema::TYPE_BOOLEAN:
                if (!is_int($value)) {
                    $this->lastError='Not a valid boolean type value for '.$this->name;
                    return false;
                }
                if ($value<$this->minIntValue||$value>$this->maxIntValue) {
                    $this->lastError='value is out of range given ' .$value. ' valid range ' .$this->minIntValue.'..'.$this->maxIntValue.' for boolean '.$this->name;
                    return false;
                }
                return true;

            case Schema::TYPE_DOUBLE:
                if (!is_double($value)) {
                    if (!is_numeric($value)) {
                        $this->lastError='Not a valid double/float type value for '.$this->name;
                        return false;
                    }
                    $value= floatval($value);
                }
                
                if ($value<$this->minDoubleValue) {
                    $this->lastError= $this->name.'(double) value ' . $value . ' is less than ' . $this->minDoubleValue;
                    return false;
                }
                
                if ($value<$this->minDoubleValue||$value>$this->maxDoubleValue) {
                    $this->lastError='value is out of range given ' .$value. ' valid range ' .$this->minDoubleValue.'..'.$this->maxDoubleValue.' for double '.$this->name;
                    return false;
                }
                
                return true;
                
            case Schema::TYPE_STRING:
                if (!is_string($value)) {
                    $this->lastError='Not a valid string type value for '.$this->name;
                    return false;
                }
                $value=\trim($value);
                $len=\mb_strlen($value);
                if ($len<$this->minStringLength) {
                    $this->lastError='Not a valid string length, given ' . $len . ' min required ' . $this->minStringLength . ' for '.$this->name;
                    return false;                    
                }
                if ($this->toUpper) { $value=\strtoupper($value); }
                return true;                
        }
        $this->lastError='Undefined data type for bin '.$this->name;
        return false;
        
    }
}
