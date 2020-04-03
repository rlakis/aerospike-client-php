<?php
namespace Core\Data;

include_once dirname(__DIR__) . '/model/Singleton.php';
include_once 'BinField.php';
include_once 'TableMetadata.php';

const NS_MOURJAN            = 'mourjan';
const NS_USER               = 'users';
const TS_COUNTRY            = 'country';
const TS_CITY               = 'city';
const TS_ROOT               = 'root';
const TS_SECTION            = 'section';
const TS_PURPOSE            = 'purpose';
const TS_URL_PATH           = 'urlpath';

const TS_CACHE              = 'cache';

class Schema extends \Core\Model\Singleton {
    const TS_PROFILE        = 'profile';
    
    const TYPE_INTEGER      = 0;
    const TYPE_STRING       = 1;   
    const TYPE_BOOLEAN      = 2;   
    const TYPE_DOUBLE       = 3;   
    const TYPE_LONG         = 4;  
    const TYPE_LIST         = 5;
    
    const FIELD_TYPE        = 0;
    const FIELD_LENGTH      = 1;
    const FIELD_NOTNULL     = 2;
    const FIELD_DEFAULT     = 3;
    const FIELD_DESC        = 4;
    
    /* bin names                */
    const GENERIC_ID            = 'id';
    const BIN_NAME_AR           = 'name_ar';
    const BIN_NAME_EN           = 'name_en';
    const GENERIC_READONLY      = 'locked';
    const BIN_LATITUDE          = 'latitude';
    const BIN_LONGITUDE         = 'longitude';
    const BIN_CURRENCY          = 'currency_id';
    const GENERIC_COUNTRY       = 'country_id';
    const BIN_URI               = 'uri';
    
    
    const COUNTRY_ALPHA_ID      = 'id_2';
    const COUNTRY_CODE          = 'code';
    const COUNTRY_CITIES        = 'cities';
    
    const CITY_PARENT_ID        = 'parent_id';
    const CITY_AR_LOCALITY_ID   = 'loc_ar_id';
    const CITY_EN_LOCALITY_ID   = 'loc_en_id';
        
    const ROOT_DIFFER_SECTION_ID= 'differ_section';
    
    const BIN_ID                = 'id';
    const BIN_PATH              = 'path';
    const BIN_LUT               = 'unixtime';
    const BIN_ROOT_ID           = 'root_id';
    const BIN_SECTION_ID        = 'section_id';
    const BIN_PURPOSE_ID        = 'purpose_id';
    const BIN_COUNTRY_ID        = 'country_id';
    const BIN_CITY_ID           = 'city_id';
    const BIN_MODULE            = 'module';
    const BIN_BLOCKED           = 'blocked';
    
    public TableMetadata $countryMeta;
    public TableMetadata $cityMeta;
    public TableMetadata $rootMeta;
    public TableMetadata $sectionMeta;
    public TableMetadata $purposeMeta;
    public TableMetadata $urlPathMeta;
    
    public static function instance() : Schema {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        parent::__construct();
        $this->countryMeta=TableMetadata::create(NS_MOURJAN, TS_COUNTRY)->setPrimaryKey([static::BIN_ID])->setUniqueKey([static::COUNTRY_ALPHA_ID])
                ->addField(BinField::create(static::BIN_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::COUNTRY_ALPHA_ID)->setDescription('iso 2 letters code')->setDataType(static::TYPE_STRING)->setLength(2)->setMinStringLength(2)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(static::BIN_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::BIN_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::BIN_CURRENCY)->setDescription('iso currency 3 letters code')->setDataType(static::TYPE_STRING)->setLength(3)->setMinStringLength(3)->setRequired(true)->setToUpperCase(true))
                ->addField(BinField::create(static::COUNTRY_CODE)->setDescription('Country iso digits code')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::COUNTRY_CITIES)->setDescription('active cities list')->setDataType(static::TYPE_LIST)->setRequired(true))                
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(1))
                ->addField(BinField::create(static::GENERIC_READONLY)->setDescription('allow changes or not')->setDataType(static::TYPE_BOOLEAN)->setRequired(true))
                ->addField(BinField::create(static::BIN_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));    

        $this->cityMeta=TableMetadata::create(NS_MOURJAN, TS_CITY)->setPrimaryKey([static::GENERIC_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_URI)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(32)->setMinStringLength(0)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::CITY_PARENT_ID)->setDescription('parent city id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::GENERIC_COUNTRY)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_LATITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::BIN_LONGITUDE)->setDescription('geo latitude')->setDataType(static::TYPE_DOUBLE)->setRequired(true))
                ->addField(BinField::create(static::CITY_AR_LOCALITY_ID)->setDescription('arabic locality id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::CITY_EN_LOCALITY_ID)->setDescription('english locality id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(1))
                ->addField(BinField::create(static::BIN_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));
        
        $this->rootMeta=TableMetadata::create(NS_MOURJAN, TS_ROOT)->setPrimaryKey([static::GENERIC_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('root id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_URI)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(32)->setMinStringLength(0)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::ROOT_DIFFER_SECTION_ID)->setDescription('differ section id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(0))
                ->addField(BinField::create(static::BIN_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));        

        $this->sectionMeta=TableMetadata::create(NS_MOURJAN, TS_SECTION)->setPrimaryKey([static::GENERIC_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('Section id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_URI)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(32)->setMinStringLength(0)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::BIN_ROOT_ID)->setDescription('Root id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(0))
                ->addField(BinField::create(static::BIN_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));        

        $this->purposeMeta=TableMetadata::create(NS_MOURJAN, TS_PURPOSE)->setPrimaryKey([static::GENERIC_ID])
                ->addField(BinField::create(static::GENERIC_ID)->setDescription('Purpose id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(1))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English name')->setDataType(static::TYPE_STRING)->setLength(50)->setRequired(true))
                ->addField(BinField::create(static::BIN_URI)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(32)->setMinStringLength(0)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(0))
                ->addField(BinField::create(static::BIN_LUT)->setDescription('last update unixtime')->setDataType(static::TYPE_LONG)->setRequired(true));        

        $this->urlPathMeta=TableMetadata::create(NS_MOURJAN, TS_URL_PATH, false)->setPrimaryKey([static::BIN_PATH])
                ->addField(BinField::create(static::BIN_PATH)->setDescription('url path')->setDataType(static::TYPE_STRING)->setLength(128)->setRequired(true)->setToLowerCase(true)->setHidden(true))
                ->addField(BinField::create(static::BIN_COUNTRY_ID)->setDescription('Country id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_CITY_ID)->setDescription('City id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_ROOT_ID)->setDescription('Root id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_SECTION_ID)->setDescription('Section id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))
                ->addField(BinField::create(static::BIN_PURPOSE_ID)->setDescription('Purpose id')->setDataType(static::TYPE_INTEGER)->setRequired(true)->setMinIntValue(0))                
                ->addField(BinField::create(static::BIN_MODULE)->setDescription('module name')->setDataType(static::TYPE_STRING)->setLength(24)->setRequired(true)->setToLowerCase(true))
                ->addField(BinField::create(static::BIN_NAME_EN)->setDescription('English title')->setDataType(static::TYPE_STRING)->setLength(128)->setRequired(true))
                ->addField(BinField::create(static::BIN_NAME_AR)->setDescription('Arabic title')->setDataType(static::TYPE_STRING)->setLength(128)->setRequired(true))                
                ->addField(BinField::create(static::BIN_BLOCKED)->setDescription('not active for publishing and listing')->setDataType(static::TYPE_BOOLEAN)->setRequired(true)->setDefaultInt(0));        
        
    }           
    
    
    public function beforeInsert(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
    public function beforeUpdate(string $table, array &$bins, array &$result) : bool {
        return empty($result);
    }
    
    
}