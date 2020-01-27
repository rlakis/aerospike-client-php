<?php
namespace Core\Data;

include_once dirname(__DIR__) . '/model/Singleton.php';
include_once 'BinField.php';
include_once 'TableMetadata.php';

const NS_MOURJAN            = 'mourjan';
const TS_COUNTRY            = 'country';

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
    
    public TableMetadata $countryMeta;
    
    public static function instance() : Schema {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        parent::__construct();
        $this->countryMeta=TableMetadata::create(NS_MOURJAN, TS_COUNTRY)->setPrimaryKey([GENERIC_ID])->setUniqueKey([COUNTRY_ALPHA_ID])
                ->addField(BinField::create(GENERIC_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(GENERIC_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(GENERIC_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(COUNTRY_ALPHA_ID)->setDescription('iso 2 letters code')->setDataType(static::TYPE_STRING)->setLength(2)->setMinStringLength(2)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(GENERIC_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(GENERIC_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(GENERIC_CURRENCY)->setDescription('iso currency 3 letters code')->setDataType(static::TYPE_STRING)->setLength(3)->setMinStringLength(3)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(COUNTRY_CODE)->setDescription('Country iso digits code')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(GENERIC_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(1))
                ->addField(BinField::create(GENERIC_READONLY)->setDescription('allow changes or not')->setDataType(static::TYPE_BOOLEAN)->setRequired(true))
                ->addField(BinField::create(GENERIC_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));
        
        
        
        $this->tables = [
            'country' => [
                'ns'    => 'mourjan',
                'bins'  => [
                    GENERIC_ID              => BinField::create(GENERIC_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)
                                                ->setRequired(true)->setMinIntValue(1),
                    GENERIC_NAME_AR         => BinField::create(GENERIC_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)
                                                ->setLength(50)->setRequired(true),
                    GENERIC_NAME_EN         => BinField::create(GENERIC_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)
                                                ->setLength(50)->setRequired(true),
                    COUNTRY_ALPHA_ID        => BinField::create(COUNTRY_ALPHA_ID)->setDescription('iso 2 letters code')->setDataType(static::TYPE_STRING)
                                                ->setLength(2)->setMinStringLength(2)->setRequired(true)->setToUpperCase(true),
                    GENERIC_BLOCKED         => BinField::create(GENERIC_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)
                                                ->setRequired(true)->setDefaultInt(1),
                    GENERIC_LATITUDE        => BinField::create(GENERIC_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)
                                                ->setRequired(true),
                    GENERIC_LONGITUDE       => BinField::create(GENERIC_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)
                                                ->setRequired(true),
                    COUNTRY_CODE            => BinField::create(COUNTRY_CODE)->setDescription('Country iso digits code')->setDataType(static::TYPE_INTEGER)
                                                ->setRequired(true)->setMinIntValue(1),
                    GENERIC_CURRENCY        => BinField::create(GENERIC_CURRENCY)->setDescription('iso currency 3 letters code')->setDataType(static::TYPE_STRING)
                                                ->setLength(3)->setMinStringLength(3)->setRequired(true)->setToUpperCase(true),
                    GENERIC_READONLY        => BinField::create(GENERIC_READONLY)->setDescription('allow changes or not')->setDataType(static::TYPE_BOOLEAN)
                                                ->setRequired(true),
                    GENERIC_LUT             => BinField::create(GENERIC_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)
                                                ->setRequired(true)
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
    
    
    public function fieldMetadata(string $table, string $name) : BinField {
        return $this->tables[$table]['bins'][$name];
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