<?php
include_once get_cfg_var("mourjan.path") . '/config/cfg.php';
include_once $config['dir']. '/core/model/Db.php';
include_once $config['dir']. '/core/lib/MCSessionHandler.php';
include_once $config['dir'].'/core/model/User.php';
new MCSessionHandler();
$db = new DB($config);
$user = new User($db, $config, null, 0);
$user->populate();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>mourjan chat</title>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/ico" href="../favicon.ico"/>
    <link type="text/css" rel="stylesheet" media="screen" href="https://dv.mourjan.com/web/converse/2.0.6/css/theme.min.css" />
    <link type="text/css" rel="stylesheet" media="screen" href="https://dv.mourjan.com/web/converse/2.0.6/css/converse.min.css" />
    <script src="https://dv.mourjan.com/web/converse/2.0.6/converse.min.js"></script>
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom">
</body>

<script>
    require(['converse'], function (converse) {
        converse.initialize({
            locked_domain:'mourjan.com',
            message_archiving:'always',
            visible_toolbar_buttons:{
                call: true,
                clear: true,
                emoticons: true,
                toggle_occupants: true
            },
            allow_logout: true, // No point in logging out when we have auto_login as true.
            allow_muc_invitations: false, // Doesn't make sense to allow because only
                                          // roster contacts can be invited
            allow_muc:false,
            allow_registration:false,
            cache_otr_key:true,
            message_carbons:true,
            forward_messages:true,
            allow_contact_requests: false,// Contacts from other servers cannot,
                                          // be added and anonymous users don't
                                          // know one another's JIDs, so disabling.
            authentication: 'login',
            //prebind_url: 'https://dv.mourjan.com:5223/api/prebind',
            auto_login: true,
            sounds_path:'https://dv.mourjan.com/web/converse/2.0.6/sounds/',
            /*auto_join_rooms: [
                'anonymous@conference.nomnom.im',
            ],
            notify_all_room_messages: [
                'anonymous@conference.nomnom.im',
            ],*/
            message_storage:'local',
            websocket_url:'wss://dv.mourjan.com:5280/websocket',
            //bosh_service_url: 'https://dv.mourjan.com:5280/http-bind', // Please use this connection manager only for testing purposes
            jid: '<?= $user->info['id'] ?>@mourjan.com', // XMPP server which allows anonymous login (doesn't
                              // allow chatting with other XMPP servers).
            password: '<?= $user->info['data']->getToken() ?>',
            keepalive: true,
            hide_muc_server: true, // Federation is disabled, so no use in
                                   // showing the MUC server.
            play_sounds: true,
            show_controlbox_by_default: true,
            strict_plugin_dependencies: false,
            debug: true
        });
    });
</script>
</html>
