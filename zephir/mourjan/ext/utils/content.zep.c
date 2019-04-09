
#ifdef HAVE_CONFIG_H
#include "../ext_config.h"
#endif

#include <php.h>
#include "../php_ext.h"
#include "../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/object.h"
#include "kernel/memory.h"
#include "kernel/array.h"
#include "kernel/operators.h"
#include "kernel/fcall.h"
#include "kernel/concat.h"
#include "kernel/variables.h"


ZEPHIR_INIT_CLASS(Utils_Content) {

	ZEPHIR_REGISTER_CLASS(Utils, Content, utils, content, utils_content_method_entry, 0);

	zend_declare_property_null(utils_content_ce, SL("content"), ZEND_ACC_PRIVATE TSRMLS_CC);

	zend_declare_property_null(utils_content_ce, SL("profile"), ZEND_ACC_PRIVATE TSRMLS_CC);

	zend_declare_property_null(utils_content_ce, SL("latitude"), ZEND_ACC_PRIVATE TSRMLS_CC);

	zephir_declare_class_constant_long(utils_content_ce, SL("VERSION_NUMBER"), 3);

	zephir_declare_class_constant_string(utils_content_ce, SL("ID"), "id");

	zephir_declare_class_constant_string(utils_content_ce, SL("STATE"), "state");

	zephir_declare_class_constant_string(utils_content_ce, SL("ROOT_ID"), "ro");

	zephir_declare_class_constant_string(utils_content_ce, SL("SECTION_ID"), "se");

	zephir_declare_class_constant_string(utils_content_ce, SL("PURPOSE_ID"), "pu");

	zephir_declare_class_constant_string(utils_content_ce, SL("APP_NAME"), "app");

	zephir_declare_class_constant_string(utils_content_ce, SL("APP_VERSION"), "app_v");

	zephir_declare_class_constant_string(utils_content_ce, SL("USER_AGENT"), "agent");

	zephir_declare_class_constant_string(utils_content_ce, SL("NATIVE_RTL"), "rtl");

	zephir_declare_class_constant_string(utils_content_ce, SL("FOREIGN_RTL"), "altRtl");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTRIBUTES"), "attrs");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_NATIVE"), "ar");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_FOREIGN"), "en");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_GEO_KEYS"), "geokeys");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_LOCALES"), "locales");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_LOCALITY"), "locality");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_LOCALITY_CITIES"), "cities");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_LOCALITY_ID"), "id");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_PHONES"), "phones");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_PHONES_NUMBERS"), "n");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_PHONES_TYPES"), "t");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_PRICE"), "price");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_SPACE"), "space");

	zephir_declare_class_constant_string(utils_content_ce, SL("ATTR_ROOMS"), "rooms");

	zephir_declare_class_constant_string(utils_content_ce, SL("BUDGET"), "budget");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO"), "cui");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_BLACKBERRY"), "b");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_EMAIL"), "e");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE"), "p");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_COUNTRY_CODE"), "c");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_COUNTRY_ISO"), "i");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_RAW_NUMBER"), "r");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_TYPE"), "t");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_INTERNATIONAL"), "v");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_PHONE_X"), "x");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_SKIPE"), "s");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_INFO_TWITTER"), "t");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_TIME"), "cut");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_TIME_AFTER"), "a");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_TIME_BEFORE"), "b");

	zephir_declare_class_constant_string(utils_content_ce, SL("CONTACT_TIME_HOUR"), "t");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_CONTROL"), "extra");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_CONTROL_MAP"), "m");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_CONTROL_PICTURES"), "p");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_CONTROL_VIDEO"), "v");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_CONTROL_TRANSLATION"), "t");

	zephir_declare_class_constant_string(utils_content_ce, SL("UI_LANGUAGE"), "hl");

	zephir_declare_class_constant_string(utils_content_ce, SL("IP_ADDRESS"), "ip");

	zephir_declare_class_constant_string(utils_content_ce, SL("IP_SCORE"), "ipfs");

	zephir_declare_class_constant_string(utils_content_ce, SL("NATIVE_TEXT"), "other");

	zephir_declare_class_constant_string(utils_content_ce, SL("FOREIGN_TEXT"), "altother");

	zephir_declare_class_constant_string(utils_content_ce, SL("LATITUDE"), "lat");

	zephir_declare_class_constant_string(utils_content_ce, SL("LONGITUDE"), "lon");

	zephir_declare_class_constant_string(utils_content_ce, SL("LOCATION"), "loc");

	zephir_declare_class_constant_string(utils_content_ce, SL("LOCATION_ARABIC"), "loc_ar");

	zephir_declare_class_constant_string(utils_content_ce, SL("LOCATION_ENGLISH"), "loc_en");

	zephir_declare_class_constant_string(utils_content_ce, SL("MEDIA"), "media");

	zephir_declare_class_constant_string(utils_content_ce, SL("PICTURE_INDEX"), "pix_idx");

	zephir_declare_class_constant_string(utils_content_ce, SL("DEFAULT_PICTURE"), "pix_def");

	zephir_declare_class_constant_string(utils_content_ce, SL("PICTURES"), "pics");

	zephir_declare_class_constant_string(utils_content_ce, SL("REGIONS"), "pubTo");

	zephir_declare_class_constant_string(utils_content_ce, SL("UID"), "user");

	zephir_declare_class_constant_string(utils_content_ce, SL("USER_LEVEL"), "userLvl");

	zephir_declare_class_constant_string(utils_content_ce, SL("USER_LOCATION"), "userLOC");

	zephir_declare_class_constant_string(utils_content_ce, SL("QUALIFIED"), "qualified");

	zephir_declare_class_constant_string(utils_content_ce, SL("VERSION"), "version");

	return SUCCESS;

}

PHP_METHOD(Utils_Content, getContent) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "content");

}

PHP_METHOD(Utils_Content, getProfile) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "profile");

}

PHP_METHOD(Utils_Content, getLatitude) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "latitude");

}

PHP_METHOD(Utils_Content, __construct) {

	zval _0, _1, _3;
	zval __$false, _2;
	zval *this_ptr = getThis();

	ZVAL_BOOL(&__$false, 0);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_3);

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(&_0);
	zephir_create_array(&_0, 37, 0 TSRMLS_CC);
	add_assoc_long_ex(&_0, SL("id"), 0);
	add_assoc_long_ex(&_0, SL("user"), 0);
	add_assoc_long_ex(&_0, SL("state"), 0);
	add_assoc_long_ex(&_0, SL("ro"), 0);
	add_assoc_long_ex(&_0, SL("se"), 0);
	add_assoc_long_ex(&_0, SL("pu"), 0);
	add_assoc_stringl_ex(&_0, SL("app"), SL(""));
	add_assoc_stringl_ex(&_0, SL("app_v"), SL(""));
	add_assoc_long_ex(&_0, SL("version"), 3);
	add_assoc_stringl_ex(&_0, SL("agent"), SL(""));
	add_assoc_stringl_ex(&_0, SL("ip"), SL(""));
	add_assoc_double_ex(&_0, SL("ipfs"), 0.0);
	add_assoc_long_ex(&_0, SL("budget"), 0);
	ZEPHIR_INIT_VAR(&_1);
	zephir_create_array(&_1, 5, 0 TSRMLS_CC);
	ZEPHIR_INIT_VAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_1, SL("p"), &_2, PH_COPY | PH_SEPARATE);
	add_assoc_stringl_ex(&_1, SL("e"), SL(""));
	add_assoc_stringl_ex(&_1, SL("b"), SL(""));
	add_assoc_stringl_ex(&_1, SL("s"), SL(""));
	add_assoc_stringl_ex(&_1, SL("t"), SL(""));
	zephir_array_update_string(&_0, SL("cui"), &_1, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_1);
	zephir_create_array(&_1, 3, 0 TSRMLS_CC);
	add_assoc_long_ex(&_1, SL("b"), 6);
	add_assoc_long_ex(&_1, SL("a"), 24);
	add_assoc_long_ex(&_1, SL("t"), 0);
	zephir_array_update_string(&_0, SL("cut"), &_1, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_1);
	zephir_create_array(&_1, 4, 0 TSRMLS_CC);
	add_assoc_long_ex(&_1, SL("m"), 2);
	add_assoc_long_ex(&_1, SL("p"), 2);
	add_assoc_long_ex(&_1, SL("t"), 2);
	add_assoc_long_ex(&_1, SL("v"), 2);
	zephir_array_update_string(&_0, SL("extra"), &_1, PH_COPY | PH_SEPARATE);
	add_assoc_stringl_ex(&_0, SL("hl"), SL("ar"));
	add_assoc_stringl_ex(&_0, SL("other"), SL(""));
	add_assoc_long_ex(&_0, SL("rtl"), 0);
	add_assoc_stringl_ex(&_0, SL("altother"), SL(""));
	add_assoc_long_ex(&_0, SL("altRtl"), 0);
	add_assoc_long_ex(&_0, SL("media"), 0);
	add_assoc_long_ex(&_0, SL("pix_def"), 0);
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_0, SL("pics"), &_2, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_0, SL("pubTo"), &_2, PH_COPY | PH_SEPARATE);
	add_assoc_double_ex(&_0, SL("lat"), 0.0);
	add_assoc_double_ex(&_0, SL("lon"), 0.0);
	add_assoc_stringl_ex(&_0, SL("loc"), SL(""));
	add_assoc_stringl_ex(&_0, SL("loc_ar"), SL(""));
	add_assoc_stringl_ex(&_0, SL("loc_en"), SL(""));
	add_assoc_long_ex(&_0, SL("userLvl"), 0);
	add_assoc_stringl_ex(&_0, SL("userLOC"), SL(""));
	ZEPHIR_INIT_NVAR(&_1);
	zephir_create_array(&_1, 6, 0 TSRMLS_CC);
	add_assoc_stringl_ex(&_1, SL("ar"), SL(""));
	add_assoc_stringl_ex(&_1, SL("en"), SL(""));
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_1, SL("geokeys"), &_2, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_1, SL("locales"), &_2, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_VAR(&_3);
	zephir_create_array(&_3, 2, 0 TSRMLS_CC);
	add_assoc_long_ex(&_3, SL("id"), 0);
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_3, SL("cities"), &_2, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_1, SL("locality"), &_3, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_2);
	array_init(&_2);
	zephir_array_update_string(&_1, SL("phones"), &_2, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_0, SL("attrs"), &_1, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_0, SL("qualified"), &__$false, PH_COPY | PH_SEPARATE);
	zephir_update_property_zval(this_ptr, SL("content"), &_0);
	ZEPHIR_MM_RESTORE();

}

PHP_METHOD(Utils_Content, setID) {

	zval *id_param = NULL, _0, _1;
	zend_long id;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &id_param);

	id = zephir_get_intval(id_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "id");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, id);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getID) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("id"), PH_NOISY | PH_READONLY, "utils/Content.zep", 145 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, setUID) {

	zval *uid_param = NULL, __$null, _0, _1, _2, _3;
	zend_long uid;
	zval *this_ptr = getThis();

	ZVAL_NULL(&__$null);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &uid_param);

	uid = zephir_get_intval(uid_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("user"), PH_NOISY | PH_READONLY, "utils/Content.zep", 150 TSRMLS_CC);
	if (!ZEPHIR_IS_LONG_IDENTICAL(&_1, uid)) {
		zephir_update_property_zval(this_ptr, SL("profile"), &__$null);
	}
	ZEPHIR_INIT_VAR(&_2);
	ZVAL_STRING(&_2, "user");
	ZEPHIR_INIT_VAR(&_3);
	ZVAL_LONG(&_3, uid);
	zephir_update_property_array(this_ptr, SL("content"), &_2, &_3);
	ZEPHIR_INIT_NVAR(&_3);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getUID) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("user"), PH_NOISY | PH_READONLY, "utils/Content.zep", 159 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, setState) {

	zval *state_param = NULL, _0, _1;
	zend_long state;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &state_param);

	state = zephir_get_intval(state_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "state");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, state);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getSectionID) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("se"), PH_NOISY | PH_READONLY, "utils/Content.zep", 180 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, getPurposeID) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("pu"), PH_NOISY | PH_READONLY, "utils/Content.zep", 198 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, setPurposeID) {

	zval *id_param = NULL, _0, _1;
	zend_long id;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &id_param);

	id = zephir_get_intval(id_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "pu");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, id);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setApp) {

	zval *name_param = NULL, *version_param = NULL, _0, _1;
	zval name, version;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&name);
	ZVAL_UNDEF(&version);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &name_param, &version_param);

	zephir_get_strval(&name, name_param);
	zephir_get_strval(&version, version_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "app");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &name);
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_STRING(&_1, "app_v");
	zephir_update_property_array(this_ptr, SL("content"), &_1, &version);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setVersion) {

	zval *version_param = NULL, _0, _1, _2$$3, _3$$3;
	zend_long version;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2$$3);
	ZVAL_UNDEF(&_3$$3);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &version_param);

	version = zephir_get_intval(version_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("version"), PH_NOISY | PH_READONLY, "utils/Content.zep", 216 TSRMLS_CC);
	if (!ZEPHIR_IS_LONG_IDENTICAL(&_1, version)) {
		ZEPHIR_INIT_VAR(&_2$$3);
		ZVAL_STRING(&_2$$3, "version");
		ZEPHIR_INIT_VAR(&_3$$3);
		ZVAL_LONG(&_3$$3, version);
		zephir_update_property_array(this_ptr, SL("content"), &_2$$3, &_3$$3);
		ZEPHIR_INIT_NVAR(&_3$$3);
	}
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setUserAgent) {

	zval *user_agent_param = NULL, _0;
	zval user_agent;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&user_agent);
	ZVAL_UNDEF(&_0);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &user_agent_param);

	zephir_get_strval(&user_agent, user_agent_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "agent");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &user_agent);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setBudget) {

	zval *budget_param = NULL, _0, _1;
	zend_long budget;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &budget_param);

	budget = zephir_get_intval(budget_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "budget");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, budget);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getIpAddress) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("ip"), PH_NOISY | PH_READONLY, "utils/Content.zep", 239 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, setIpAddress) {

	zval *ip_param = NULL, _0;
	zval ip;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&ip);
	ZVAL_UNDEF(&_0);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &ip_param);

	zephir_get_strval(&ip, ip_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "ip");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &ip);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setIpScore) {

	zval *score_param = NULL, ip_score, _0;
	double score;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&ip_score);
	ZVAL_UNDEF(&_0);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &score_param);

	score = zephir_get_doubleval(score_param);


	ZEPHIR_INIT_VAR(&ip_score);
	ZVAL_DOUBLE(&ip_score, score);
	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "ipfs");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &ip_score);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, addPhone) {

	zval _0;
	zval country_iso_code, raw_number, international_number;
	zval *country_callkey_param = NULL, *country_iso_code_param = NULL, *raw_number_param = NULL, *number_type_param = NULL, *international_number_param = NULL, _1;
	zend_long country_callkey, number_type;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&country_iso_code);
	ZVAL_UNDEF(&raw_number);
	ZVAL_UNDEF(&international_number);
	ZVAL_UNDEF(&_0);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 5, 0, &country_callkey_param, &country_iso_code_param, &raw_number_param, &number_type_param, &international_number_param);

	country_callkey = zephir_get_intval(country_callkey_param);
	zephir_get_strval(&country_iso_code, country_iso_code_param);
	zephir_get_strval(&raw_number, raw_number_param);
	number_type = zephir_get_intval(number_type_param);
	zephir_get_strval(&international_number, international_number_param);


	ZEPHIR_INIT_VAR(&_0);
	zephir_create_array(&_0, 5, 0 TSRMLS_CC);
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, country_callkey);
	zephir_array_update_string(&_0, SL("c"), &_1, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_0, SL("i"), &country_iso_code, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_0, SL("r"), &raw_number, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_NVAR(&_1);
	ZVAL_LONG(&_1, number_type);
	zephir_array_update_string(&_0, SL("t"), &_1, PH_COPY | PH_SEPARATE);
	zephir_array_update_string(&_0, SL("v"), &international_number, PH_COPY | PH_SEPARATE);
	zephir_update_property_array_multi(this_ptr, SL("content"), &_0 TSRMLS_CC, SL("ssa"), 5, SL("cui"), SL("p"));
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setEmail) {

	zval *email_param = NULL;
	zval email;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&email);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &email_param);

	zephir_get_strval(&email, email_param);


	zephir_update_property_array_multi(this_ptr, SL("content"), &email TSRMLS_CC, SL("ss"), 4, SL("cui"), SL("e"));
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setUserLanguage) {

	zval _1;
	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *language_param = NULL, _0, _2, _3;
	zval language;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&language);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &language_param);

	zephir_get_strval(&language, language_param);


	ZEPHIR_INIT_VAR(&_0);
	ZEPHIR_INIT_VAR(&_1);
	zephir_create_array(&_1, 2, 0 TSRMLS_CC);
	ZEPHIR_INIT_VAR(&_2);
	ZVAL_STRING(&_2, "ar");
	zephir_array_fast_append(&_1, &_2);
	ZEPHIR_INIT_NVAR(&_2);
	ZVAL_STRING(&_2, "en");
	zephir_array_fast_append(&_1, &_2);
	ZEPHIR_CALL_FUNCTION(&_3, "\in_array", NULL, 1, &language, &_1);
	zephir_check_call_status();
	if (zephir_is_true(&_3)) {
		ZEPHIR_CPY_WRT(&_0, &language);
	} else {
		ZEPHIR_INIT_NVAR(&_0);
		ZVAL_STRING(&_0, "ar");
	}
	ZEPHIR_INIT_NVAR(&_2);
	ZVAL_STRING(&_2, "hl");
	zephir_update_property_array(this_ptr, SL("content"), &_2, &_0);
	ZEPHIR_INIT_NVAR(&_0);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setUserLevel) {

	zval *level_param = NULL, _0, _1;
	zend_long level;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &level_param);

	level = zephir_get_intval(level_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "userLvl");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_LONG(&_1, level);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setUserLocation) {

	zval *this_ptr = getThis();


	RETURN_THISW();

}

PHP_METHOD(Utils_Content, rtl) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *text_param = NULL, success, _0, spaces, _1, _2, _3;
	zval text;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&text);
	ZVAL_UNDEF(&success);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&spaces);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &text_param);

	zephir_get_strval(&text, text_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "/\\p{Arabic}/u");
	ZEPHIR_CALL_FUNCTION(&success, "\preg_match_all", NULL, 2, &_0, &text);
	zephir_check_call_status();
	ZEPHIR_INIT_NVAR(&_0);
	ZVAL_STRING(&_0, "/\\s/u");
	ZEPHIR_CALL_FUNCTION(&spaces, "\preg_match_all", NULL, 2, &_0, &text);
	zephir_check_call_status();
	ZEPHIR_CALL_FUNCTION(&_1, "\mb_strlen", NULL, 3, &text);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_2);
	zephir_sub_function(&_2, &_1, &spaces);
	ZEPHIR_SINIT_VAR(_3);
	div_function(&_3, &success, &_2 TSRMLS_CC);
	if (ZEPHIR_GE_LONG(&_3, 0.5)) {
		RETURN_MM_LONG(1);
	}
	RETURN_MM_LONG(0);

}

PHP_METHOD(Utils_Content, setNativeText) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *text_param = NULL, _0, _1, _2, _3, _4;
	zval text;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&text);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &text_param);

	zephir_get_strval(&text, text_param);


	ZEPHIR_CALL_FUNCTION(&_0, "\trim", NULL, 4, &text);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_STRING(&_1, "other");
	zephir_update_property_array(this_ptr, SL("content"), &_1, &_0);
	ZEPHIR_INIT_NVAR(&_0);
	zephir_read_property(&_2, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_3, &_2, SL("other"), PH_NOISY | PH_READONLY, "utils/Content.zep", 304 TSRMLS_CC);
	ZEPHIR_CALL_METHOD(&_0, this_ptr, "rtl", NULL, 5, &_3);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_4);
	ZVAL_STRING(&_4, "rtl");
	zephir_update_property_array(this_ptr, SL("content"), &_4, &_0);
	ZEPHIR_INIT_NVAR(&_0);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getNativeRTL) {

	zval _0, _1;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("rtl"), PH_NOISY | PH_READONLY, "utils/Content.zep", 310 TSRMLS_CC);
	RETURN_CTORW(&_1);

}

PHP_METHOD(Utils_Content, setForeignText) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *text_param = NULL, _0, _1, _2, _3, _4;
	zval text;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&text);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &text_param);

	zephir_get_strval(&text, text_param);


	ZEPHIR_CALL_FUNCTION(&_0, "\trim", NULL, 4, &text);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_STRING(&_1, "altother");
	zephir_update_property_array(this_ptr, SL("content"), &_1, &_0);
	ZEPHIR_INIT_NVAR(&_0);
	zephir_read_property(&_2, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_3, &_2, SL("altother"), PH_NOISY | PH_READONLY, "utils/Content.zep", 316 TSRMLS_CC);
	ZEPHIR_CALL_METHOD(&_0, this_ptr, "rtl", NULL, 5, &_3);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_4);
	ZVAL_STRING(&_4, "altRtl");
	zephir_update_property_array(this_ptr, SL("content"), &_4, &_0);
	ZEPHIR_INIT_NVAR(&_0);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setPictures) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *pictures_param = NULL, _0, _1, _2, _3;
	zval pictures;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&pictures);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &pictures_param);

	zephir_get_arrval(&pictures, pictures_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "pics");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &pictures);
	ZEPHIR_INIT_VAR(&_1);
	ZEPHIR_CALL_FUNCTION(&_2, "\count", NULL, 6, &pictures);
	zephir_check_call_status();
	if (ZEPHIR_GT_LONG(&_2, 0)) {
		ZEPHIR_INIT_NVAR(&_1);
		ZVAL_LONG(&_1, 1);
	} else {
		ZEPHIR_INIT_NVAR(&_1);
		ZVAL_LONG(&_1, 0);
	}
	ZEPHIR_INIT_VAR(&_3);
	ZVAL_STRING(&_3, "media");
	zephir_update_property_array(this_ptr, SL("content"), &_3, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, addRegion) {

	zval *region_param = NULL, _0, _1, _2, _3, _4$$3;
	zend_long region, ZEPHIR_LAST_CALL_STATUS;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4$$3);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &region_param);

	region = zephir_get_intval(region_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("pubTo"), PH_NOISY | PH_READONLY, "utils/Content.zep", 329 TSRMLS_CC);
	ZVAL_LONG(&_2, region);
	ZEPHIR_CALL_FUNCTION(&_3, "\in_array", NULL, 1, &_2, &_1);
	zephir_check_call_status();
	if (!zephir_is_true(&_3)) {
		ZEPHIR_INIT_VAR(&_4$$3);
		ZVAL_LONG(&_4$$3, region);
		zephir_update_property_array_multi(this_ptr, SL("content"), &_4$$3 TSRMLS_CC, SL("sa"), 3, SL("pubTo"));
	}
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, addRegions) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *regions_param = NULL, _0, _1, _2, _3, _4;
	zval regions;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&regions);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &regions_param);

	zephir_get_arrval(&regions, regions_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("pubTo"), PH_NOISY | PH_READONLY, "utils/Content.zep", 337 TSRMLS_CC);
	ZEPHIR_CALL_FUNCTION(&_2, "\array_values", NULL, 7, &regions);
	zephir_check_call_status();
	ZEPHIR_CALL_FUNCTION(&_3, "\array_merge", NULL, 8, &_1, &_2);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_4);
	ZVAL_STRING(&_4, "pubTo");
	zephir_update_property_array(this_ptr, SL("content"), &_4, &_3);
	ZEPHIR_INIT_NVAR(&_3);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setCoordinate) {

	zval *lat_param = NULL, *lng_param = NULL, longitude, latitude, _0, _1;
	double lat, lng;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&longitude);
	ZVAL_UNDEF(&latitude);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &lat_param, &lng_param);

	lat = zephir_get_doubleval(lat_param);
	lng = zephir_get_doubleval(lng_param);


	ZEPHIR_INIT_VAR(&longitude);
	ZVAL_DOUBLE(&longitude, lng);
	ZEPHIR_INIT_VAR(&latitude);
	ZVAL_DOUBLE(&latitude, lat);
	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "lat");
	zephir_update_property_array(this_ptr, SL("content"), &_0, &latitude);
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_STRING(&_1, "lon");
	zephir_update_property_array(this_ptr, SL("content"), &_1, &longitude);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setLocation) {

	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *location_param = NULL, _0, _1, _2$$3, _3$$4, _4$$5, _5$$6;
	zval location;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&location);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2$$3);
	ZVAL_UNDEF(&_3$$4);
	ZVAL_UNDEF(&_4$$5);
	ZVAL_UNDEF(&_5$$6);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &location_param);

	zephir_get_strval(&location, location_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_1, &_0, SL("app"), PH_NOISY | PH_READONLY, "utils/Content.zep", 352 TSRMLS_CC);
	if (ZEPHIR_IS_STRING_IDENTICAL(&_1, "web")) {
		ZEPHIR_INIT_VAR(&_2$$3);
		ZVAL_STRING(&_2$$3, "loc");
		zephir_update_property_array(this_ptr, SL("content"), &_2$$3, &location);
	} else {
		ZEPHIR_CALL_METHOD(&_3$$4, this_ptr, "rtl", NULL, 5, &location);
		zephir_check_call_status();
		if (zephir_is_true(&_3$$4)) {
			ZEPHIR_INIT_VAR(&_4$$5);
			ZVAL_STRING(&_4$$5, "loc_ar");
			zephir_update_property_array(this_ptr, SL("content"), &_4$$5, &location);
		} else {
			ZEPHIR_INIT_VAR(&_5$$6);
			ZVAL_STRING(&_5$$6, "loc_en");
			zephir_update_property_array(this_ptr, SL("content"), &_5$$6, &location);
		}
	}
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, setQualified) {

	zval *value_param = NULL, _0, _1;
	zend_bool value;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &value_param);

	value = zephir_get_boolval(value_param);


	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "qualified");
	ZEPHIR_INIT_VAR(&_1);
	ZVAL_BOOL(&_1, value);
	zephir_update_property_array(this_ptr, SL("content"), &_0, &_1);
	ZEPHIR_INIT_NVAR(&_1);
	RETURN_THIS();

}

PHP_METHOD(Utils_Content, getData) {

	zval _0;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_unset_string(&_0, SL("attrs"), PH_SEPARATE);
	RETURN_MEMBER(getThis(), "content");

}

PHP_METHOD(Utils_Content, toJsonString) {

	zval *options_param = NULL, _0, _1, _2;
	zend_long options, ZEPHIR_LAST_CALL_STATUS;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &options_param);

	options = zephir_get_intval(options_param);


	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_unset_string(&_0, SL("attrs"), PH_SEPARATE);
	zephir_read_property(&_1, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZVAL_LONG(&_2, options);
	ZEPHIR_RETURN_CALL_FUNCTION("\json_encode", NULL, 9, &_1, &_2);
	zephir_check_call_status();
	RETURN_MM();

}

PHP_METHOD(Utils_Content, getAsVersion) {

	zval *version_param = NULL;
	zend_long version, ZEPHIR_LAST_CALL_STATUS;
	zval *this_ptr = getThis();


	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 0, &version_param);

	version = zephir_get_intval(version_param);


	do {
		if (version == 2) {
			ZEPHIR_RETURN_CALL_METHOD(this_ptr, "getasversion2", NULL, 10);
			zephir_check_call_status();
			RETURN_MM();
		}
		if (version == 3) {
			ZEPHIR_RETURN_CALL_METHOD(this_ptr, "getasversion3", NULL, 11);
			zephir_check_call_status();
			RETURN_MM();
		}
	} while(0);

	array_init(return_value);
	RETURN_MM();

}

PHP_METHOD(Utils_Content, getAsVersion3) {

	zval rs, _0, _1, _2, _3, _4, _5, _6, _7, _8, _9, _10, _11, _12, _13, _14, _15, _16, _17, _18, _19, _20, _21, _22, _27, _28, _31, _32, _35, _36, _39, _40, _43, _44, _47, _48, _23$$3, _24$$3, _25$$3, _26$$3, _29$$4, _30$$4, _33$$5, _34$$5, _37$$6, _38$$6, _41$$7, _42$$7, _45$$8, _46$$8, _49$$9, _50$$9;
	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&rs);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4);
	ZVAL_UNDEF(&_5);
	ZVAL_UNDEF(&_6);
	ZVAL_UNDEF(&_7);
	ZVAL_UNDEF(&_8);
	ZVAL_UNDEF(&_9);
	ZVAL_UNDEF(&_10);
	ZVAL_UNDEF(&_11);
	ZVAL_UNDEF(&_12);
	ZVAL_UNDEF(&_13);
	ZVAL_UNDEF(&_14);
	ZVAL_UNDEF(&_15);
	ZVAL_UNDEF(&_16);
	ZVAL_UNDEF(&_17);
	ZVAL_UNDEF(&_18);
	ZVAL_UNDEF(&_19);
	ZVAL_UNDEF(&_20);
	ZVAL_UNDEF(&_21);
	ZVAL_UNDEF(&_22);
	ZVAL_UNDEF(&_27);
	ZVAL_UNDEF(&_28);
	ZVAL_UNDEF(&_31);
	ZVAL_UNDEF(&_32);
	ZVAL_UNDEF(&_35);
	ZVAL_UNDEF(&_36);
	ZVAL_UNDEF(&_39);
	ZVAL_UNDEF(&_40);
	ZVAL_UNDEF(&_43);
	ZVAL_UNDEF(&_44);
	ZVAL_UNDEF(&_47);
	ZVAL_UNDEF(&_48);
	ZVAL_UNDEF(&_23$$3);
	ZVAL_UNDEF(&_24$$3);
	ZVAL_UNDEF(&_25$$3);
	ZVAL_UNDEF(&_26$$3);
	ZVAL_UNDEF(&_29$$4);
	ZVAL_UNDEF(&_30$$4);
	ZVAL_UNDEF(&_33$$5);
	ZVAL_UNDEF(&_34$$5);
	ZVAL_UNDEF(&_37$$6);
	ZVAL_UNDEF(&_38$$6);
	ZVAL_UNDEF(&_41$$7);
	ZVAL_UNDEF(&_42$$7);
	ZVAL_UNDEF(&_45$$8);
	ZVAL_UNDEF(&_46$$8);
	ZVAL_UNDEF(&_49$$9);
	ZVAL_UNDEF(&_50$$9);

	ZEPHIR_MM_GROW();

	ZEPHIR_INIT_VAR(&rs);
	zephir_create_array(&rs, 12, 0 TSRMLS_CC);
	zephir_read_property(&_0, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_VAR(&_1);
	zephir_array_fetch_string(&_1, &_0, SL("cui"), PH_NOISY, "utils/Content.zep", 396 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("cui"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_2, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_2, SL("userLvl"), PH_NOISY, "utils/Content.zep", 397 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("userLvl"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_3, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_3, SL("userLOC"), PH_NOISY, "utils/Content.zep", 398 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("userLOC"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_4, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_4, SL("agent"), PH_NOISY, "utils/Content.zep", 399 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("agent"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_5, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_5, SL("hl"), PH_NOISY, "utils/Content.zep", 400 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("hl"), &_1, PH_COPY | PH_SEPARATE);
	ZEPHIR_CALL_METHOD(&_6, this_ptr, "getipaddress", NULL, 0);
	zephir_check_call_status();
	zephir_array_update_string(&rs, SL("ip"), &_6, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_7, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_7, SL("ipfs"), PH_NOISY, "utils/Content.zep", 402 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("ipfs"), &_1, PH_COPY | PH_SEPARATE);
	ZEPHIR_INIT_VAR(&_8);
	zephir_read_property(&_9, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_10, &_9, SL("qualified"), PH_NOISY | PH_READONLY, "utils/Content.zep", 403 TSRMLS_CC);
	if (zephir_is_true(&_10)) {
		ZEPHIR_INIT_NVAR(&_8);
		ZVAL_LONG(&_8, 1);
	} else {
		ZEPHIR_INIT_NVAR(&_8);
		ZVAL_LONG(&_8, 0);
	}
	zephir_array_update_string(&rs, SL("qualified"), &_8, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_11, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_11, SL("budget"), PH_NOISY, "utils/Content.zep", 404 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("budget"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_12, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	ZEPHIR_OBS_NVAR(&_1);
	zephir_array_fetch_string(&_1, &_12, SL("other"), PH_NOISY, "utils/Content.zep", 405 TSRMLS_CC);
	zephir_array_update_string(&rs, SL("other"), &_1, PH_COPY | PH_SEPARATE);
	zephir_read_property(&_13, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_14, &_13, SL("app"), PH_NOISY | PH_READONLY, "utils/Content.zep", 406 TSRMLS_CC);
	zephir_array_fetch_long(&_15, &_14, 0, PH_NOISY | PH_READONLY, "utils/Content.zep", 406 TSRMLS_CC);
	zephir_read_property(&_16, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_17, &_16, SL("app_v"), PH_NOISY | PH_READONLY, "utils/Content.zep", 406 TSRMLS_CC);
	ZEPHIR_INIT_LNVAR(_8);
	ZEPHIR_CONCAT_VSV(&_8, &_15, "-", &_17);
	zephir_array_update_string(&rs, SL("app"), &_8, PH_COPY | PH_SEPARATE);
	add_assoc_long_ex(&rs, SL("version"), 3);
	zephir_array_fetch_string(&_18, &rs, SL("cui"), PH_NOISY | PH_READONLY, "utils/Content.zep", 410 TSRMLS_CC);
	zephir_array_unset_string(&_18, SL("b"), PH_SEPARATE);
	zephir_array_fetch_string(&_19, &rs, SL("cui"), PH_NOISY | PH_READONLY, "utils/Content.zep", 411 TSRMLS_CC);
	zephir_array_unset_string(&_19, SL("t"), PH_SEPARATE);
	zephir_array_fetch_string(&_20, &rs, SL("cui"), PH_NOISY | PH_READONLY, "utils/Content.zep", 412 TSRMLS_CC);
	zephir_array_unset_string(&_20, SL("s"), PH_SEPARATE);
	zephir_read_property(&_21, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_22, &_21, SL("altother"), PH_NOISY | PH_READONLY, "utils/Content.zep", 414 TSRMLS_CC);
	if (zephir_is_true(&_22)) {
		zephir_read_property(&_23$$3, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_24$$3, &_23$$3, SL("altother"), PH_NOISY | PH_READONLY, "utils/Content.zep", 415 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("altother"), &_24$$3, PH_COPY | PH_SEPARATE);
		zephir_read_property(&_25$$3, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_26$$3, &_25$$3, SL("altRtl"), PH_NOISY | PH_READONLY, "utils/Content.zep", 416 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("altRtl"), &_26$$3, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_27, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_28, &_27, SL("pubTo"), PH_NOISY | PH_READONLY, "utils/Content.zep", 419 TSRMLS_CC);
	ZEPHIR_CALL_FUNCTION(&_6, "\count", NULL, 6, &_28);
	zephir_check_call_status();
	if (zephir_is_true(&_6)) {
		zephir_read_property(&_29$$4, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_30$$4, &_29$$4, SL("pubTo"), PH_NOISY | PH_READONLY, "utils/Content.zep", 420 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("pubTo"), &_30$$4, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_31, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_32, &_31, SL("pics"), PH_NOISY | PH_READONLY, "utils/Content.zep", 423 TSRMLS_CC);
	if (zephir_is_true(&_32)) {
		zephir_read_property(&_33$$5, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_34$$5, &_33$$5, SL("pics"), PH_NOISY | PH_READONLY, "utils/Content.zep", 424 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("pics"), &_34$$5, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_35, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_36, &_35, SL("loc"), PH_NOISY | PH_READONLY, "utils/Content.zep", 427 TSRMLS_CC);
	if (zephir_is_true(&_36)) {
		zephir_read_property(&_37$$6, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_38$$6, &_37$$6, SL("loc"), PH_NOISY | PH_READONLY, "utils/Content.zep", 428 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("loc"), &_38$$6, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_39, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_40, &_39, SL("loc_ar"), PH_NOISY | PH_READONLY, "utils/Content.zep", 431 TSRMLS_CC);
	if (zephir_is_true(&_40)) {
		zephir_read_property(&_41$$7, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_42$$7, &_41$$7, SL("loc_ar"), PH_NOISY | PH_READONLY, "utils/Content.zep", 432 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("loc_ar"), &_42$$7, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_43, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_44, &_43, SL("loc_en"), PH_NOISY | PH_READONLY, "utils/Content.zep", 435 TSRMLS_CC);
	if (zephir_is_true(&_44)) {
		zephir_read_property(&_45$$8, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_46$$8, &_45$$8, SL("loc_en"), PH_NOISY | PH_READONLY, "utils/Content.zep", 436 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("loc_en"), &_46$$8, PH_COPY | PH_SEPARATE);
	}
	zephir_read_property(&_47, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_48, &_47, SL("state"), PH_NOISY | PH_READONLY, "utils/Content.zep", 439 TSRMLS_CC);
	if (ZEPHIR_GT_LONG(&_48, 0)) {
		zephir_read_property(&_49$$9, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
		zephir_array_fetch_string(&_50$$9, &_49$$9, SL("attrs"), PH_NOISY | PH_READONLY, "utils/Content.zep", 440 TSRMLS_CC);
		zephir_array_update_string(&rs, SL("attrs"), &_50$$9, PH_COPY | PH_SEPARATE);
	}
	RETURN_CCTOR(&rs);

}

PHP_METHOD(Utils_Content, getAsVersion2) {

	zval *this_ptr = getThis();


	array_init(return_value);
	return;

}

PHP_METHOD(Utils_Content, save) {

	zephir_fcall_cache_entry *_1 = NULL;
	zval *state_param = NULL, *pdo, pdo_sub, q, _0, st, adContent, _2, _3, _4, _5, _6, _7, _8, _9, _10, _11, _12, _13, _14, _15, _16, _17, _18, _19, _20, result$$5, _21$$5, _22$$6, _23$$7, _24$$8;
	zend_long state, ZEPHIR_LAST_CALL_STATUS;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&pdo_sub);
	ZVAL_UNDEF(&q);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&st);
	ZVAL_UNDEF(&adContent);
	ZVAL_UNDEF(&_2);
	ZVAL_UNDEF(&_3);
	ZVAL_UNDEF(&_4);
	ZVAL_UNDEF(&_5);
	ZVAL_UNDEF(&_6);
	ZVAL_UNDEF(&_7);
	ZVAL_UNDEF(&_8);
	ZVAL_UNDEF(&_9);
	ZVAL_UNDEF(&_10);
	ZVAL_UNDEF(&_11);
	ZVAL_UNDEF(&_12);
	ZVAL_UNDEF(&_13);
	ZVAL_UNDEF(&_14);
	ZVAL_UNDEF(&_15);
	ZVAL_UNDEF(&_16);
	ZVAL_UNDEF(&_17);
	ZVAL_UNDEF(&_18);
	ZVAL_UNDEF(&_19);
	ZVAL_UNDEF(&_20);
	ZVAL_UNDEF(&result$$5);
	ZVAL_UNDEF(&_21$$5);
	ZVAL_UNDEF(&_22$$6);
	ZVAL_UNDEF(&_23$$7);
	ZVAL_UNDEF(&_24$$8);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 1, 1, &state_param, &pdo);

	if (!state_param) {
		state = 0;
	} else {
		state = zephir_get_intval(state_param);
	}


	ZEPHIR_CALL_METHOD(&_0, this_ptr, "getid", &_1, 0);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&q);
	if (ZEPHIR_GT_LONG(&_0, 0)) {
		ZVAL_STRING(&q, "UPDATE ad_user SET /* Utils/Content *\ ");
		zephir_concat_self_str(&q, SL("content=?, purpose_id=?, section_id=?, rtl=?, country_id=?, city_id=?, latitude=?, longitude=?, state=?, media=? ") TSRMLS_CC);
		zephir_concat_self_str(&q, SL("where id=? returning state") TSRMLS_CC);
	} else {
		ZVAL_STRING(&q, "INSERT INTO ad_user (content, purpose_id, section_id, rtl, country_id, city_id, latitude, longitude, state, media, web_user_id) ");
		zephir_concat_self_str(&q, SL("VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) returning ID") TSRMLS_CC);
	}
	zend_print_zval(&q, 0);
	php_printf("%s", "\n");
	ZEPHIR_CALL_METHOD(&st, pdo, "prepare", NULL, 0, &q);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&adContent);
	ZVAL_STRING(&adContent, "");
	ZEPHIR_CALL_FUNCTION(&_2, "\json_encode", NULL, 9, &adContent);
	zephir_check_call_status();
	ZVAL_LONG(&_3, 1);
	ZVAL_LONG(&_4, 2);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_3, &_2, &_4);
	zephir_check_call_status();
	ZEPHIR_CALL_METHOD(&_5, this_ptr, "getpurposeid", NULL, 0);
	zephir_check_call_status();
	ZVAL_LONG(&_3, 2);
	ZVAL_LONG(&_4, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_3, &_5, &_4);
	zephir_check_call_status();
	ZEPHIR_CALL_METHOD(&_6, this_ptr, "getsectionid", NULL, 0);
	zephir_check_call_status();
	ZVAL_LONG(&_3, 3);
	ZVAL_LONG(&_4, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_3, &_6, &_4);
	zephir_check_call_status();
	ZEPHIR_CALL_METHOD(&_7, this_ptr, "getnativertl", NULL, 0);
	zephir_check_call_status();
	ZVAL_LONG(&_3, 4);
	ZVAL_LONG(&_4, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_3, &_7, &_4);
	zephir_check_call_status();
	zephir_read_property(&_3, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_8, &_3, SL("lat"), PH_NOISY | PH_READONLY, "utils/Content.zep", 475 TSRMLS_CC);
	ZVAL_LONG(&_4, 7);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_4, &_8);
	zephir_check_call_status();
	zephir_read_property(&_4, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_9, &_4, SL("lon"), PH_NOISY | PH_READONLY, "utils/Content.zep", 476 TSRMLS_CC);
	ZVAL_LONG(&_10, 8);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_10, &_9);
	zephir_check_call_status();
	zephir_read_property(&_10, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_11, &_10, SL("state"), PH_NOISY | PH_READONLY, "utils/Content.zep", 477 TSRMLS_CC);
	ZVAL_LONG(&_12, 9);
	ZVAL_LONG(&_13, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_12, &_11, &_13);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_14);
	zephir_read_property(&_12, this_ptr, SL("content"), PH_NOISY_CC | PH_READONLY);
	zephir_array_fetch_string(&_15, &_12, SL("pics"), PH_NOISY | PH_READONLY, "utils/Content.zep", 478 TSRMLS_CC);
	ZEPHIR_CALL_FUNCTION(&_16, "\count", NULL, 6, &_15);
	zephir_check_call_status();
	if (ZEPHIR_GT_LONG(&_16, 0)) {
		ZEPHIR_INIT_NVAR(&_14);
		ZVAL_LONG(&_14, 1);
	} else {
		ZEPHIR_INIT_NVAR(&_14);
		ZVAL_LONG(&_14, 0);
	}
	ZVAL_LONG(&_13, 10);
	ZVAL_LONG(&_17, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_13, &_14, &_17);
	zephir_check_call_status();
	ZEPHIR_INIT_VAR(&_18);
	ZEPHIR_CALL_METHOD(&_19, this_ptr, "getid", &_1, 0);
	zephir_check_call_status();
	if (ZEPHIR_GT_LONG(&_19, 0)) {
		ZEPHIR_CALL_METHOD(&_18, this_ptr, "getid", &_1, 0);
		zephir_check_call_status();
	} else {
		ZEPHIR_CALL_METHOD(&_18, this_ptr, "getuid", NULL, 0);
		zephir_check_call_status();
	}
	ZVAL_LONG(&_13, 11);
	ZVAL_LONG(&_17, 1);
	ZEPHIR_CALL_METHOD(NULL, &st, "bindvalue", NULL, 0, &_13, &_18, &_17);
	zephir_check_call_status();
	ZEPHIR_CALL_METHOD(&_20, &st, "execute", NULL, 0);
	zephir_check_call_status();
	if (zephir_is_true(&_20)) {
		ZVAL_LONG(&_21$$5, 2);
		ZEPHIR_CALL_METHOD(&result$$5, &st, "fetch", NULL, 0, &_21$$5);
		zephir_check_call_status();
		if (!ZEPHIR_IS_FALSE_IDENTICAL(&result$$5)) {
			ZEPHIR_CALL_METHOD(&_22$$6, this_ptr, "getid", &_1, 0);
			zephir_check_call_status();
			if (ZEPHIR_GT_LONG(&_22$$6, 0)) {
				zephir_array_fetch_string(&_23$$7, &result$$5, SL("STATE"), PH_NOISY | PH_READONLY, "utils/Content.zep", 484 TSRMLS_CC);
				ZEPHIR_CALL_METHOD(NULL, this_ptr, "setstate", NULL, 0, &_23$$7);
				zephir_check_call_status();
			} else {
				zephir_array_fetch_string(&_24$$8, &result$$5, SL("ID"), PH_NOISY | PH_READONLY, "utils/Content.zep", 487 TSRMLS_CC);
				ZEPHIR_CALL_METHOD(NULL, this_ptr, "setid", NULL, 0, &_24$$8);
				zephir_check_call_status();
			}
		}
		ZEPHIR_CALL_METHOD(NULL, &st, "close", NULL, 0);
		zephir_check_call_status();
		ZEPHIR_CALL_METHOD(NULL, pdo, "commit", NULL, 0);
		zephir_check_call_status();
	} else {
		ZEPHIR_CALL_METHOD(NULL, &st, "close", NULL, 0);
		zephir_check_call_status();
		ZEPHIR_CALL_METHOD(NULL, pdo, "rollback", NULL, 0);
		zephir_check_call_status();
	}
	zephir_var_dump(&st TSRMLS_CC);
	ZEPHIR_MM_RESTORE();

}

