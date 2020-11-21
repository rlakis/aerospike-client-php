<?php

$hybridConfig = [
    'callback' => "https://".($_SERVER['HTTP_HOST'] ?? 'h1.mourjan.com')."/web/lib/hybridauth/",    
    "providers" => [
        
        /*"Yahoo" => [
            "enabled" => true,
            "keys"    => array(
                "id" => "dj0yJmk9U1NFQzBQUXRoV1lTJnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PTNm", 
                "secret" => "9341a1c046f012557d9f2c80389584c3d3557f68"),
            "scope"   => 'sdps-r'
        ],
        /*"Yahoo" => [
            "enabled" => true,
            "keys"    => array(
                "id" => "dj0yJmk9VnNHQ1cyVGNUZ3VMJnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PWUx", 
                "secret" => "51881a865ddd067582ad90010e912809fb726751"),
            "scope"   => 'sdps-r'
        ],*/
        "YahooOpenID" => [
            "enabled" => true,
            //"wrapper" => [ "path" => get_cfg_var('mourjan.path')."/deps/hybridauth/hybridauth/src/Provider/YahooOpenID.php", "class" => "YahooOpenID" ] 
        ],
        "Google" => [
            "enabled" => true,
            "keys" => [
                "id" => "1017340605957-a5e8e6b12v4o5t8v2hjh16i140ju873u.apps.googleusercontent.com",
                "secret" => "Ar4wtlv1pkVm1gi3fo2us110"],
            "scope" => "email"
        ],
        /*"Facebook" => [
            "enabled" => true,
            "keys" => [
                "id" => "184370954908428",
                "secret" => "e52e8d321c6b9dd828c8e0504b1ec5bd"
            ],
            // A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
            "scope" => "email",
            // The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
            "display" => "popup"
        ],*/
        "Facebook" => [//dev
            "enabled" => true,
            "keys" => [
                "id" => "225134451713462",
                "secret" => "503bd119d9d8f0a80d3a736db8002683"
            ],
            // A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
            "scope" => "email, public_profile",
            // The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
            "display" => "popup"
        ],
        "Twitter" => [
            "enabled" => true,
            "keys" => [
                "key" => "cNlPKmwj28nhGldSWj8gyLLge",
                "secret" => "Smxh9tO3kh2LlMfsKJ1dfitbPKvVy2KjAyrwyJJPVSdQte9V5J"]
        ],
        "WindowsLive" => [
            "enabled" => true,
            "keys" => [
                "id" => "000000004418151A",
                "secret" => "fLOSwG3UFEnmLe2P8ytUVbzguD8mBGbb"],
            "scope" => "wl.emails"
        ],
        "WindowsLive" => [
            "enabled" => true,
            "keys" => [
                "id" => "000000004825624E",
                "secret" => "txsnfcPPRO020)bSBO89_})"],
            "scope" => "wl.emails"
        ],
        "LinkedIn" => [
            "enabled" => true,
            "keys" => [
                "id" => "86sbzgo4pvhdm2",
                "secret" => "2lHQ5KTZs8cFx9Qb"
            ],
            "scope"   => array("r_liteprofile", "r_emailaddress"), 
        ]
    ]
];

