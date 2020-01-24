<?php

$dir = get_cfg_var('mourjan.path');
opcache_compile_file($dir.'/deps/autoload.php');
opcache_compile_file($dir.'/deps/composer/ClassLoader.php');
opcache_compile_file($dir.'/deps/composer/autoload_classmap.php');
opcache_compile_file($dir.'/deps/composer/autoload_files.php');
opcache_compile_file($dir.'/deps/composer/autoload_namespaces.php');
opcache_compile_file($dir.'/deps/composer/autoload_psr4.php');
opcache_compile_file($dir.'/deps/composer/autoload_real.php');
opcache_compile_file($dir.'/deps/composer/autoload_static.php');

opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/AlternateFormatsCountryCodeSet.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/AsYouTypeFormatter.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/CountryCodeSource.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/CountryCodeToRegionCodeMap.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/DefaultMetadataLoader.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/MetadataLoaderInterface.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/PhoneNumber.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/PhoneNumberUtil.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/MetadataSourceInterface.php');
opcache_compile_file($dir.'/deps/giggsey/libphonenumber-for-php/src/MultiFileMetadataSourceImpl.php');

opcache_compile_file('/var/www/mourjan/index.php');
opcache_compile_file('/var/www/mourjan/config/cfg.php');

opcache_compile_file('/var/www/mourjan/core/model/Singleton.php');
#opcache_compile_file('/var/www/mourjan/core/model/NoSQL.php');
#opcache_compile_file('/var/www/mourjan/core/model/asd/UserTrait.php');
#opcache_compile_file('/var/www/mourjan/core/model/asd/MobileTrait.php');
#opcache_compile_file('/var/www/mourjan/core/model/asd/DeviceTrait.php');
#opcache_compile_file('/var/www/mourjan/core/model/asd/BlackListTrait.php');
#opcache_compile_file('/var/www/mourjan/core/model/asd/CallTrait.php');

opcache_compile_file('/var/www/mourjan/core/lib/logger/LoggerInterface.php');
opcache_compile_file('/var/www/mourjan/core/lib/logger/AbstractLogger.php');
opcache_compile_file('/var/www/mourjan/core/lib/logger/LogLevel.php');
opcache_compile_file('/var/www/mourjan/core/lib/Logger.php');


opcache_compile_file('/var/www/mourjan/core/lib/MCUser.php');
#opcache_compile_file('/var/www/mourjan/core/lib/MCSessionHandler.php');
#opcache_compile_file('/var/www/mourjan/core/lib/SphinxQL.php');
//opcache_compile_file('/var/www/mourjan/core/lib/MCCache.php');

opcache_compile_file('/var/www/mourjan/core/model/Content.php');
opcache_compile_file('/var/www/mourjan/core/model/Ad.php');
opcache_compile_file('/var/www/mourjan/core/model/AdList.php');
if (0) {
    opcache_compile_file('/var/www/mourjan/core/model/Db.php');
    opcache_compile_file('/var/www/mourjan/core/model/User.php');
    opcache_compile_file('/var/www/mourjan/core/model/Classifieds.php');
    opcache_compile_file('/var/www/mourjan/core/model/Router.php');
    opcache_compile_file('/var/www/mourjan/core/layout/Site.php');
    opcache_compile_file('/var/www/mourjan/core/layout/Page.php');
    opcache_compile_file('/var/www/mourjan/core/layout/Home.php');
    opcache_compile_file('/var/www/mourjan/core/layout/Search.php');
    opcache_compile_file('/var/www/mourjan/core/layout/Bin.php');
}

opcache_compile_file('/var/www/mourjan/core/lang/main.php');
opcache_compile_file('/var/www/mourjan/core/lang/ar/main.php');

