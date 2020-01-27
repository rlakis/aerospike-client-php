<?php
namespace Core\Data;

include_once dirname(__DIR__) . '/model/Singleton.php';
include_once 'BinField.php';
include_once 'TableMetadata.php';

const NS_MOURJAN            = 'mourjan';
const TS_COUNTRY            = 'country';
const TS_CITY               = 'city';


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
    
    /* bin names                */
    const GENERIC_ID            = 'id';
    const GENERIC_NAME_AR       = 'name_ar';
    const GENERIC_NAME_EN       = 'name_en';
    const GENERIC_BLOCKED       = 'blocked';
    const GENERIC_READONLY      = 'locked';
    const GENERIC_LATITUDE      = 'latitude';
    const GENERIC_LONGITUDE     = 'longitude';
    const GENERIC_CURRENCY      = 'currency_id';
    const GENERIC_COUNTRY       = 'country_id';
    const GENERIC_LUT           = 'unixtime';
    const GENERIC_PATH          = 'uri';
    
    
    const COUNTRY_ALPHA_ID      = 'id_2';
    const COUNTRY_CODE          = 'code';
    
    const CITY_PARENT_ID        = 'parent_id';
    const CITY_AR_LOCALITY_ID   = 'loc_ar_id';
    const CITY_EN_LOCALITY_ID   = 'loc_en_id';
        
    
    public TableMetadata $countryMeta;
    public TableMetadata $cityMeta;
    
    public static function instance() : Schema {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        parent::__construct();
        $this->countryMeta=TableMetadata::create(NS_MOURJAN, TS_COUNTRY)->setPrimaryKey([static::GENERIC_ID])->setUniqueKey([static::COUNTRY_ALPHA_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::GENERIC_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::COUNTRY_ALPHA_ID)->setDescription('iso 2 letters code')->setDataType(static::TYPE_STRING)->setLength(2)->setMinStringLength(2)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(static::GENERIC_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_CURRENCY)->setDescription('iso currency 3 letters code')->setDataType(static::TYPE_STRING)->setLength(3)->setMinStringLength(3)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(static::COUNTRY_CODE)->setDescription('Country iso digits code')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::GENERIC_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(1))
                ->addField(BinField::create(static::GENERIC_READONLY)->setDescription('allow changes or not')->setDataType(static::TYPE_BOOLEAN)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));    

        $this->cityMeta=TableMetadata::create(NS_MOURJAN, TS_CITY)->setPrimaryKey([static::GENERIC_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::GENERIC_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_PATH)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(24)->setMinStringLength(2)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::CITY_PARENT_ID)->setDescription('parent city id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::GENERIC_COUNTRY)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::GENERIC_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::GENERIC_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::CITY_AR_LOCALITY_ID)->setDescription('arabic locality id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::CITY_EN_LOCALITY_ID)->setDescription('english locality id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::GENERIC_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(1))
                ->addField(BinField::create(static::GENERIC_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));    
        
        
    }           
    
    
    public function beforeInsert(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
    public function beforeUpdate(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
}