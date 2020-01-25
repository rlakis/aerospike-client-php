<?php
namespace Core\Data;

include_once dirname(__DIR__) . '/model/Singleton.php';

const GENERIC_ID            = 'id';
const GENERIC_NAME_AR       = 'name_ar';
const GENERIC_NAME_EN       = 'name_en';
const GENERIC_BLOCKED       = 'blocked';
const GENERIC_READONLY      = 'locked';
const GENERIC_LATITUDE      = 'latitude';
const GENERIC_LONGITUDE     = 'longitude';
const GENERIC_CURRENCY      = 'currency_id';
const GENERIC_LUT           = 'unixtime';
const COUNTRY_ALPHA_ID      = 'id_2';
const COUNTRY_CODE          = 'code';

class Schema extends \Core\Model\Singleton {
    const TYPE_INTEGER      = 0;
    const TYPE_STRING       = 1;   
    const TYPE_BOOLEAN      = 2;   
    const TYPE_DOUBLE       = 3;   
    const TYPE_LONG         = 4;   
    
    const FIELD_TYPE        = 0;
    const FIELD_LENGTH      = 1;
    const FIELD_NOTNULL     = 2;
    const FIELD_DEFAULT     = 3;
    const FIELD_DESC        = 4;
    
    protected array $tables;
    
    
    public static function instance() : Schema {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        parent::__construct();
        $this->tables = [
            'country' => [
                'ns'    => 'mourjan',
                'bins'  => [
                    GENERIC_ID              => [static::TYPE_INTEGER,   4,  1,  0, $this->positiveInt($v),   'country id'],
                    GENERIC_NAME_AR         => [static::TYPE_STRING,    50, 1,  '',     'arabic name'],
                    GENERIC_NAME_EN         => [static::TYPE_STRING,    50, 1,  '',     'english name'],
                    COUNTRY_ALPHA_ID        => [static::TYPE_STRING,    2,  1,  0,   'iso 2 letters code'],                         
                    GENERIC_BLOCKED         => [static::TYPE_BOOLEAN,   1,  1,  1,      'not active for publishing and listing'],
                    GENERIC_LATITUDE        => [static::TYPE_DOUBLE,    8,  1,  0,      'geo latitude'],
                    GENERIC_LONGITUDE       => [static::TYPE_DOUBLE,    8,  1,  0,      'geo longitude'],
                    COUNTRY_CODE            => [static::TYPE_INTEGER,   4,  1,  0,      'iso digits code'],
                    GENERIC_CURRENCY        => [static::TYPE_STRING,    3,  1,  0,   'iso currency 3 letters code'],
                    GENERIC_READONLY        => [static::TYPE_BOOLEAN,   1,  1,  0,      'allow changes or not'],
                    GENERIC_LUT             => [static::TYPE_LONG,      8,  1,  0,      'last update unixtime']
                ],
                'pk'=>['id'],
                'uk'=>['id_2'],
                'autogen'=>'country_id',
                            
                'prepare'=>function(array &$bins, array $errors) {            
                    if ($this->validConstraints('country', $bins, $errors)) {
                        if (isset($bins[COUNTRY_ALPHA_ID])) {
                            $bins[COUNTRY_ALPHA_ID]=\strtoupper(\trim($bins[COUNTRY_ALPHA_ID]));
                        }
                        if (isset($bins[GENERIC_CURRENCY])) {
                            $bins[GENERIC_CURRENCY]=\strtoupper(\trim($bins[GENERIC_CURRENCY]));
                        }
                    }
                },
                        
                'insert'=>function(array &$bins) {
                    $this->tables['country']['prepare']($bins, $errors);
                },
                        
                'update'=>function(array &$bins, array $matching) {
                    
                }
            ],
            
            'city' => [
                'ns'    => 'mourjan',
                'bins'  => [
                ]
            ]
        ];       
    }
    
    
    public function metadata() : array {
        return $this->tables;
    }
    
    
    private function positiveInt(int $value) : bool {
        return ($value>0);
    }
    
    
    private function validConstraints(string $table, array $bins, array &$result) : bool {
        $result=[];
        foreach ($bins as $k => $v) {
            $constraint = $this->tables[$table]['bins'][$k];
            switch ($constraint[static::FIELD_TYPE]) {
                case static::TYPE_INTEGER:
                    if (!is_int($v)) {
                        $result[$k]='Invalid int data type';
                    }
                    break;
                    
                case static::TYPE_LONG:
                    if (is_long($v)) {
                        $result[$k]='Invalid long data type';
                    }
                    break;
                    
                case static::TYPE_DOUBLE:
                    if (is_double($v)) {
                        $result[$k]='Invalid double data type';
                    }
                    break;
                    
                case static::TYPE_BOOLEAN:
                    if ($v!==0 && $v!==1) {
                        $result[$k]='Invalid boolean data type';
                    }
                    break;
                    
                case static::TYPE_STRING:
                    if (!is_string($v)) {
                        $result[$k]='Invalid string data type';
                    }
                    if (mb_strlen($v)>$constraint[static::FIELD_LENGTH]) {
                        $result[$k]='String overflow required '.$constraint[static::FIELD_LENGTH].' given '.mb_strlen($v);
                    }
                    break;
                default:
                    $result[$k]='Undefined bin';
                    break;
            }
        }
        return empty($result);
    }
    
    
    public function beforeInsert(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
    public function beforeUpdate(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
}