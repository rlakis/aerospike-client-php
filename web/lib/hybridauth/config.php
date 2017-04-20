<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2011 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/
// ----------------------------------------------------------------------------------------
//      HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------
return [
        "base_url"      => "https://dv.mourjan.com/web/lib/hybridauth/",
    "proxy"             => "",
        "providers"     => [
                "OpenID" => ["enabled" => true],

                "Yahoo" => [
                        "enabled" => true,
            "wrapper" => [ "path" => "vendor/hybridauth/hybridauth/additional-providers/hybridauth-yahoo-openid/Providers/Yahoo.php", "class" => "Hybrid_Providers_Yahoo" ]
                ],

                "Google" => [
                        "enabled" => true,
                        "keys"    => [
                                "id" => "1017340605957-a5e8e6b12v4o5t8v2hjh16i140ju873u.apps.googleusercontent.com",
                                "secret" => "Ar4wtlv1pkVm1gi3fo2us110"],
                        "scope"   => ""
                ],

                "Facebook" => [
                        "enabled" => true,
                        "keys"    => [
                "id" => "184370954908428",
                 "secret" => "e52e8d321c6b9dd828c8e0504b1ec5bd"],

                        // A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
                        "scope"   => "email",
                        // The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
                        "display" => "popup"
                ],

                "Twitter" => [
                        "enabled" => true,
                        "keys"    => [
                  "key" => "cNlPKmwj28nhGldSWj8gyLLge",
                   "secret" => "Smxh9tO3kh2LlMfsKJ1dfitbPKvVy2KjAyrwyJJPVSdQte9V5J"]
                ],

                "Live" => [
                        "enabled" => true,
                        "keys"    => [
                    "id" => "00000000400CDFD6",
                     "secret" => "m8pGBdgDo-IatzHhGJGBMXPbG3bLhBb6" ]
                ],

                "LinkedIn" => [
                        "enabled" => true,
                        "keys"    => [
                "key" => "aq7mdwotf1x6",
                "secret" => "Fut9rptNojy8NSct"]
        ],

        "AOL"  => ["enabled" => false],
                "MySpace" => ["enabled" => false, "keys" => ["key" => "", "secret" => ""]],
                "Foursquare" => ["enabled" => FALSE, "keys" => ["id" => "", "secret" => ""]],
        ],

        // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
        "debug_mode"    => true,
        "debug_file"    => "/var/log/mourjan/auth.log",
];