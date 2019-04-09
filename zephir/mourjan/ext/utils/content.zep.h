
extern zend_class_entry *utils_content_ce;

ZEPHIR_INIT_CLASS(Utils_Content);

PHP_METHOD(Utils_Content, getContent);
PHP_METHOD(Utils_Content, getProfile);
PHP_METHOD(Utils_Content, getLatitude);
PHP_METHOD(Utils_Content, __construct);
PHP_METHOD(Utils_Content, setID);
PHP_METHOD(Utils_Content, getID);
PHP_METHOD(Utils_Content, setUID);
PHP_METHOD(Utils_Content, getUID);
PHP_METHOD(Utils_Content, setState);
PHP_METHOD(Utils_Content, getSectionID);
PHP_METHOD(Utils_Content, getPurposeID);
PHP_METHOD(Utils_Content, setPurposeID);
PHP_METHOD(Utils_Content, setApp);
PHP_METHOD(Utils_Content, setVersion);
PHP_METHOD(Utils_Content, setUserAgent);
PHP_METHOD(Utils_Content, setBudget);
PHP_METHOD(Utils_Content, getIpAddress);
PHP_METHOD(Utils_Content, setIpAddress);
PHP_METHOD(Utils_Content, setIpScore);
PHP_METHOD(Utils_Content, addPhone);
PHP_METHOD(Utils_Content, setEmail);
PHP_METHOD(Utils_Content, setUserLanguage);
PHP_METHOD(Utils_Content, setUserLevel);
PHP_METHOD(Utils_Content, setUserLocation);
PHP_METHOD(Utils_Content, rtl);
PHP_METHOD(Utils_Content, setNativeText);
PHP_METHOD(Utils_Content, getNativeRTL);
PHP_METHOD(Utils_Content, setForeignText);
PHP_METHOD(Utils_Content, setPictures);
PHP_METHOD(Utils_Content, addRegion);
PHP_METHOD(Utils_Content, addRegions);
PHP_METHOD(Utils_Content, setCoordinate);
PHP_METHOD(Utils_Content, setLocation);
PHP_METHOD(Utils_Content, setQualified);
PHP_METHOD(Utils_Content, getData);
PHP_METHOD(Utils_Content, toJsonString);
PHP_METHOD(Utils_Content, getAsVersion);
PHP_METHOD(Utils_Content, getAsVersion3);
PHP_METHOD(Utils_Content, getAsVersion2);
PHP_METHOD(Utils_Content, save);

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setid, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setid, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, id, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, id)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getid, 0, 0, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getid, 0, 0, IS_LONG, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setuid, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setuid, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, uid, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, uid)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getuid, 0, 0, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getuid, 0, 0, IS_LONG, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setstate, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setstate, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, state, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, state)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getsectionid, 0, 0, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getsectionid, 0, 0, IS_LONG, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getpurposeid, 0, 0, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getpurposeid, 0, 0, IS_LONG, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setpurposeid, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setpurposeid, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, id, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, id)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setapp, 0, 2, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setapp, 0, 2, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, name, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, name)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, version, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, version)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setversion, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setversion, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, version, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, version)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setuseragent, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setuseragent, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, user_agent, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, user_agent)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setbudget, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setbudget, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, budget, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, budget)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getipaddress, 0, 0, IS_STRING, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getipaddress, 0, 0, IS_STRING, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setipaddress, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setipaddress, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, ip, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, ip)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setipscore, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setipscore, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, score, IS_DOUBLE, 0)
#else
	ZEND_ARG_INFO(0, score)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_addphone, 0, 5, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_addphone, 0, 5, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, country_callkey, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, country_callkey)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, country_iso_code, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, country_iso_code)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, raw_number, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, raw_number)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, number_type, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, number_type)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, international_number, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, international_number)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setemail, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setemail, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, email, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, email)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setuserlanguage, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setuserlanguage, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, language, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, language)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setuserlevel, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setuserlevel, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, level, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, level)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setuserlocation, 0, 0, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setuserlocation, 0, 0, IS_OBJECT, "Utils\\Content", 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_rtl, 0, 1, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_rtl, 0, 1, IS_LONG, NULL, 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, text, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, text)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setnativetext, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setnativetext, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, text, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, text)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getnativertl, 0, 0, IS_LONG, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getnativertl, 0, 0, IS_LONG, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setforeigntext, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setforeigntext, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, text, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, text)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setpictures, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setpictures, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
	ZEND_ARG_ARRAY_INFO(0, pictures, 0)
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_addregion, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_addregion, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, region, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, region)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_addregions, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_addregions, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
	ZEND_ARG_ARRAY_INFO(0, regions, 0)
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setcoordinate, 0, 2, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setcoordinate, 0, 2, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, lat, IS_DOUBLE, 0)
#else
	ZEND_ARG_INFO(0, lat)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, lng, IS_DOUBLE, 0)
#else
	ZEND_ARG_INFO(0, lng)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setlocation, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setlocation, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, location, IS_STRING, 0)
#else
	ZEND_ARG_INFO(0, location)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_utils_content_setqualified, 0, 1, Utils\\Content, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_setqualified, 0, 1, IS_OBJECT, "Utils\\Content", 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
#else
	ZEND_ARG_INFO(0, value)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getdata, 0, 0, IS_ARRAY, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getdata, 0, 0, IS_ARRAY, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_tojsonstring, 0, 1, IS_STRING, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_tojsonstring, 0, 1, IS_STRING, NULL, 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, options, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, options)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion, 0, 1, IS_ARRAY, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion, 0, 1, IS_ARRAY, NULL, 0)
#endif
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, version, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, version)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion3, 0, 0, IS_ARRAY, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion3, 0, 0, IS_ARRAY, NULL, 0)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion2, 0, 0, IS_ARRAY, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utils_content_getasversion2, 0, 0, IS_ARRAY, NULL, 0)
#endif
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_utils_content_save, 0, 0, 1)
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, state, IS_LONG, 0)
#else
	ZEND_ARG_INFO(0, state)
#endif
	ZEND_ARG_OBJ_INFO(0, pdo, PDO, 0)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(utils_content_method_entry) {
	PHP_ME(Utils_Content, getContent, NULL, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getProfile, NULL, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getLatitude, NULL, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, __construct, NULL, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
	PHP_ME(Utils_Content, setID, arginfo_utils_content_setid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getID, arginfo_utils_content_getid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setUID, arginfo_utils_content_setuid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getUID, arginfo_utils_content_getuid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setState, arginfo_utils_content_setstate, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getSectionID, arginfo_utils_content_getsectionid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getPurposeID, arginfo_utils_content_getpurposeid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setPurposeID, arginfo_utils_content_setpurposeid, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setApp, arginfo_utils_content_setapp, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setVersion, arginfo_utils_content_setversion, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setUserAgent, arginfo_utils_content_setuseragent, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setBudget, arginfo_utils_content_setbudget, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getIpAddress, arginfo_utils_content_getipaddress, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setIpAddress, arginfo_utils_content_setipaddress, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setIpScore, arginfo_utils_content_setipscore, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, addPhone, arginfo_utils_content_addphone, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setEmail, arginfo_utils_content_setemail, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setUserLanguage, arginfo_utils_content_setuserlanguage, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setUserLevel, arginfo_utils_content_setuserlevel, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setUserLocation, arginfo_utils_content_setuserlocation, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, rtl, arginfo_utils_content_rtl, ZEND_ACC_PRIVATE)
	PHP_ME(Utils_Content, setNativeText, arginfo_utils_content_setnativetext, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getNativeRTL, arginfo_utils_content_getnativertl, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setForeignText, arginfo_utils_content_setforeigntext, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setPictures, arginfo_utils_content_setpictures, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, addRegion, arginfo_utils_content_addregion, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, addRegions, arginfo_utils_content_addregions, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setCoordinate, arginfo_utils_content_setcoordinate, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setLocation, arginfo_utils_content_setlocation, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, setQualified, arginfo_utils_content_setqualified, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getData, arginfo_utils_content_getdata, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, toJsonString, arginfo_utils_content_tojsonstring, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getAsVersion, arginfo_utils_content_getasversion, ZEND_ACC_PUBLIC)
	PHP_ME(Utils_Content, getAsVersion3, arginfo_utils_content_getasversion3, ZEND_ACC_PRIVATE)
	PHP_ME(Utils_Content, getAsVersion2, arginfo_utils_content_getasversion2, ZEND_ACC_PRIVATE)
	PHP_ME(Utils_Content, save, arginfo_utils_content_save, ZEND_ACC_PUBLIC)
	PHP_FE_END
};
