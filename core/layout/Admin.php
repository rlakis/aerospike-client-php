<?php
Config::instance()->incLayoutFile('Page');

class Admin extends Page {

    var $action = '', $liOpen = '';
    private $uid = 0;
    private $aid = 0;
    private $userdata = 0;
    private $multipleAccounts = [];

    function __construct(\Core\Model\Router $router) {
        parent::__construct($router);
        $this->uid = 0;
        $this->sub = $_GET['sub'] ?? '';
        $this->mobile_param = $_GET['t'] ?? '';
        $this->aid = filter_input(INPUT_GET, 'r', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 0]]);

        $this->hasLeadingPane = true;

        if ($this->isMobile || !$this->user->isSuperUser()) {
            $this->user->redirectTo($this->router()->getLanguagePath('/notfound/'));
        }

        $this->load_lang(array("account"));      
                
        
        //$this->set_require('css', 'account');
        $this->title = $this->lang['title'];
        $this->description = $this->lang['description'];
        $this->forceNoIndex = true;
        $this->router()->config()->setValue('enabled_sharing', 0);
        $this->router()->config()->disableAds();


        $parameter = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_STRING);
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

        if ($action) {
            $redirectWenDone = true;

            switch ($action) {
                case 'blacklist':
                    $_GET['t'] = $_GET['p'];
                    $reason = $_GET['reason'];
                    unset($_GET['p']);
                    unset($_GET['reason']);

                    \Core\Model\NoSQL::getInstance()->blacklistInsert($parameter, $reason);
                    break;
                
                case 'unlist':
                    \Core\Model\NoSQL::getInstance()->removeNumberFromBlacklist($parameter);
                    $_GET['t'] = $_GET['p'];
                    unset($_GET['p']);
                    break;
                
                case 'unblock':
                    $unblockNumbers = [];
                    $userdata = [$this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($parameter))];

                    if (isset($userdata[0]['mobiles'])) {
                        $accounts = [];
                        foreach ($userdata[0]['mobiles'] as $number) {
                            $uids = [];
                            if (\Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($number['number'] + 0, $uids) == Core\Model\NoSQL::OK) {
                                foreach ($uids as $bins) {
                                    $accounts[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
                                }
                            }
                        }

                        $uids = [$userdata[0]['id']];
                        $numbers = [];
                        foreach ($accounts as $account) {
                            $uids[] = $account['id'];

                            foreach ($account['mobiles'] as $number) {
                                $numbers[$number['number']] = $number['id'];
                            }
                        }
                        $this->user->unblock($uids, $numbers);
                    }
                    break;
                    
                default:
                    break;
            }

            if ($redirectWenDone) {
                $url = "";
                unset($_GET['action']);

                foreach ($_GET as $key => $value) {
                    if ($url) {
                        $url .= '&';
                    }
                    $url .= $key . '=' . $value;
                }
                if ($url)
                    $url = '?' . $url;

                header('Location: ' . $url);
            }
        }

        $release = intval(filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT));

        if ($parameter) {
            $this->uid = 0;
            $isEmail = preg_match('/@/', $parameter);
            $email = '';

            $date = new DateTime();

            $len = strlen($parameter);
            $uuid = '';
            if (!$isEmail && preg_match('/[^0-9]/', $parameter)) {
                $record = [];
                $status = [$this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUserByUUID($parameter, $record))];

                if (count($record)) {
                    $this->uid = $record['id'];
                    $uuid = $parameter;
                }
            } elseif ($isEmail) {
                $email = $parameter;
                $user = $this->user->getUserByEmail($parameter);
                if ($user && count($user)) {
                    $selected = $this->uid = $user[0]['ID'];

                    if (isset($_GET['selected'])) {
                        $selected = intval($_GET['selected']);
                    }

                    if (count($user) > 1) {
                        foreach ($user as $rec) {
                            $this->multipleAccounts[] = $rec['ID'];
                            if ($selected == $rec['ID']) {
                                $this->uid = $rec['ID'];
                            }
                        }
                    }
                }
            } else {
                $this->uid = intval($parameter);
            }


            $this->userdata = [$this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($this->uid))];

            if ($uuid) { $this->uid = $uuid; }
            if ($isEmail) { $this->uid = $email; }
        } 
        else {
            $parameter = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 0]]);
            if ($parameter) {
                $this->userdata = [];
                if (\Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($parameter, $uids) == Core\Model\NoSQL::OK) {
                    if (count($uids)) {

                        $selected = 0;

                        if (isset($_GET['selected'])) {
                            $selected = intval($_GET['selected']);
                        }

                        $users = [];
                        foreach ($uids as $bins) {
                            $data = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));

                            if ($data && is_array($data)) {
                                $users[] = $data;

                                if ($selected == $bins['uid']) {
                                    $this->uid = $selected;
                                    $this->userdata[] = $data;
                                }
                            }
                        }
                        usort($users, function($a, $b) {
                            return -1 * strcmp($a['last_visited'], $b['last_visited']);
                        });
                        foreach ($users as $user) {
                            if (!$this->uid) {
                                $this->uid = $user['id'];
                                $this->userdata[] = $user;
                            }
                            $this->multipleAccounts[] = $user['id'];
                        }
                    }
                }
            } else {
                if ($this->aid) {
                    $this->userdata = [];
                    Config::instance()->incLibFile('MCSaveHandler');
                    $handler = new MCSaveHandler();
                    $this->userdata = $handler->checkFromDatabase($this->aid);
                    //$uids = \Core\Model\NoSQL::getInstance()->mobileGetLinkedUIDs($parameter);
                    //foreach ($uids as $bins) 
                    {
                        //$this->userdata[] = $this->parseUserBins(\Core\Model\NoSQL::getInstance()->fetchUser($bins[Core\Model\ASD\USER_UID]));
                    }
                }
            }
        }

        if ($release === -1) {
            unset($_GET['a']);
            $url = '';

            foreach ($_GET as $key => $value) {
                if ($url) {
                    $url .= '&';
                }
                $url .= $key . '=' . $value;
            }
            if ($url)
                $url = '?' . $url;

            header('Location: ' . $url);
        }

        $this->render();
        $this->inlineJS('admin.js');
    }

    private function parseUserBins($bins) {
        if ($bins && count($bins)) {
            $release = intval(filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT));

            $bins[Core\Model\ASD\USER_DATE_ADDED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_DATE_ADDED]);
            $bins[Core\Model\ASD\USER_LAST_VISITED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_LAST_VISITED]);
            $bins[Core\Model\ASD\USER_PRIOR_VISITED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_PRIOR_VISITED]);

            if (isset($bins[Core\Model\ASD\USER_LAST_AD_RENEWED])) {
                $bins[Core\Model\ASD\USER_LAST_AD_RENEWED] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_LAST_AD_RENEWED]);
            }

            if (isset($bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS])) {
                $bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS] = $this->unixTimestampToDateTime($bins[Core\Model\ASD\USER_OPTIONS][Core\Model\ASD\USER_OPTIONS_CTS]);
            }

            unset($bins['mobile']);
            $_mobiles = \Core\Model\NoSQL::getInstance()->mobileFetchByUID($bins[\Core\Model\ASD\USER_PROFILE_ID]);
            $_devices = \Core\Model\NoSQL::getInstance()->getUserDevices($bins[\Core\Model\ASD\USER_PROFILE_ID]);
            for ($i=0; $i<count($_devices); $i++) {
                $_device=$_devices[$i];
                if (isset($_device['app_prefs'])) {
                    $_devices[$i]['app_prefs']= \json_decode($_devices[$i]['app_prefs'], true);
                }
                
            }

            for ($i = 0; $i < count($_mobiles); $i++) {
                unset($_mobiles[$i][\Core\Model\ASD\USER_UID]);
                $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_REQUESTED] = $this->unixTimestampToDateTime($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_REQUESTED]);
                if (isset($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED] > 0) {
                    $_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED] = $this->unixTimestampToDateTime($_mobiles[$i][Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]);
                }

                switch ($_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG]) {
                    case 0:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'Android app';
                        break;
                    case 1:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'Website';
                        break;
                    case 2:
                        $_mobiles[$i][Core\Model\ASD\USER_MOBILE_FLAG] = 'IOS app';
                        break;
                    default:
                        break;
                }

                $ttl = MCSessionHandler::checkSuspendedMobile($_mobiles[$i][Core\Model\ASD\USER_MOBILE_NUMBER], $reason);
                if ($ttl) {
                    if ($release === -1) {
                        $_mobiles[$i]['suspended']['release'] = 'within 60 seconds';
                        $bins['suspended'] = '60s';
                        MCSessionHandler::setSuspendMobile($bins[\Core\Model\ASD\USER_PROFILE_ID], $_mobiles[$i][Core\Model\ASD\USER_MOBILE_NUMBER], 60, TRUE, $_mobiles[$i]['suspended']['release']);
                    } else {
                        $_mobiles[$i]['suspended']['till'] = gmdate("Y-m-d H:i:s T", time() + $ttl);
                        $_mobiles[$i]['suspended']['reason'] = strpos($reason, ':') ? trim(substr($reason, strpos($reason, ':') + 1)) : $reason;
                        //var_dump($_mobiles[$i]['suspended']['reason']);
                        $bins['suspended'] = 'YES';
                        $bins['suspended_reason'] = $_mobiles[$i]['suspended']['reason'];
                    }
                }
            }

            for ($i = 0; $i < count($_devices); $i++) {
                unset($_devices[$i][\Core\Model\ASD\USER_UID]);
                $_devices[$i][Core\Model\ASD\USER_DEVICE_DATE_ADDED] = $this->unixTimestampToDateTime($_devices[$i][Core\Model\ASD\USER_DEVICE_DATE_ADDED]);
                $_devices[$i][Core\Model\ASD\USER_DEVICE_LAST_VISITED] = $this->unixTimestampToDateTime($_devices[$i][Core\Model\ASD\USER_DEVICE_LAST_VISITED]);
                if (isset($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS]) && isset($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS][0]) && $_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS][0] != '{') {
                    $_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS] = base64_decode($_devices[$i][Core\Model\ASD\USER_DEVICE_APP_SETTINGS]);
                }
            }

            $bins['mobiles'] = $_mobiles;
            $bins['devices'] = $_devices;

            if (isset($bins['password'])) {
                unset($bins['password']);
            }
            if (isset($bins['jwt'])) {
                unset($bins['jwt']);
            }
        } else {
            $bins = '';
        }
        return $bins;
    }

    
    function header() {
        echo '<link href="/web/css/1.0/jsonTree.css" rel="stylesheet" /> ';
    }
    
    
    function side_pane() {
        $this->renderSideAdmin();
        //$this->renderSideUserPanel();
    }

    function renderSideAdmin() {
        $sub = $this->sub;
        $lang = $this->urlRouter->siteLanguage == 'ar' ? '' : $this->urlRouter->siteLanguage . '/';
        ?><h4><?= $this->lang['myPanel'] ?></h4><?php
        echo '<ul class=sm>';

        if ($sub == '')
            echo '<li class=on><b>', $this->lang['label_users'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '\'>', $this->lang['label_users'], '</a></li>';

        if ($sub == 'areas')
            echo '<li class=on><b>', $this->lang['label_areas'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=areas\'>', $this->lang['label_areas'], '</a></li>';               

        if ($sub == 'dic')
            echo '<li class=on><b>', $this->lang['label_dic'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=dic\'>', $this->lang['label_dic'], '</a></li>';
        
        if ($sub == 'synonym')
            echo '<li class=on><b>', $this->lang['synonyms'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=synonym\'>', $this->lang['synonyms'], '</a></li>'; 

        if ($sub == 'ads')
            echo '<li class=on><b>', $this->lang['label_ads_monitor'], '</b></li>';
        else
            echo '<li><a href=\'/admin/', $lang, '?sub=ads\'>', $this->lang['label_ads_monitor'], '</a></li>';       
        echo '</ul>';
    }

    function unixTimestampToDateTime(int $ts): string {
        $date = new DateTime();
        $date->setTimestamp($ts);
        return $date->format("Y-m-d H:i:s T");
    }

    function mainMobile() {
        
    }

    function main_pane() {

        switch ($this->sub) {
            case 'ads':
                $this->renderStatisticsPanel();
                break;

            case 'areas':
                $this->renderAreasAdminPanel();
                break;

            case 'dic':
                $this->renderDictionaryPanel();
                break;

            case 'synonym':
                $this->renderSynonymPanel();
                break;

            default:
                $this->renderUserAdminPanel();
                break;
        }
    }
    
    function renderSynonymPanel(){
        ?><div><?php
            ?><ul class="ts"><?php
                ?><li><?php
                    ?><div class="lm"><?php
                        ?><label for="keyword"><?= $this->lang['keyword'] ?></label><?php
                        ?><input type="text" id="keyword" autocomplete="off" onfocus="setFocus(this,event);" onkeydown="idir(this);navList(event)" onkeyup="load(this,event);" onchange="idir(this, 1);" /><?php
                     /*   ?><input id="add" type="button" class="bt" onclick="save(this)" style="margin:0 25px" value="<?= $this->lang['new_key'] ?>" /><?php */
                        ?><input id="rotate" type="button" class=btn onclick="rotate(this)" style="margin:0 25px" value="Rotate" /><?php 
                    ?></div><?php
                ?></li><?php
            ?></ul><?php
        ?></div><?php
        ?><div id="msg"></div><?php
        ?><div id="related"></div><?php
        
        $this->inlineQueryScript .= '$("body").click(function(e){if(e.target.id!="keyword")clear()});';
        $this->globalScript .= '
            function rotate(e){
                e = $(e);
                e.css("display", "none");
                $.ajax({
                    type:"GET",
                    url: "/ajax-keyword/",
                    data:{rotate:2},
                    success: function(rp) {
                        e.css("display", "inline-block");
                    },
                    error:function(){
                        e.css("display", "inline-block");
                    }
                });
            };
            var INDEX=-1,arrowAction=false,focusAction=false;
            function setFocus(x,e){
                arrowAction=false;
                focusAction=true;
                if(x.value!=""){
                    load(x,e);
                }
            };
            function navList(e){
                focusAction=false;
                e = e || window.event;
                if (e.keyCode == "38") {
                    INDEX--;
                    arrowAction=true;
                }
                else if (e.keyCode == "40") {                
                    INDEX++;
                    arrowAction=true;
                }else if(e.keyCode == "37" || e.keyCode == "39"){
                    arrowAction = true;
                    return;
                }else{
                    arrowAction=false;
                }
                if(arrowAction){
                    e.stopPropagation();
                    e.preventDefault();
                    if(INDEX > locs.length -1){
                        INDEX = locs.length - 1;
                    }
                    if(INDEX < 0){
                        INDEX = 0;
                    }
                    var list = $("#list");
                    $("li", list).removeClass("focus");
                    $("li:nth-child("+(INDEX + 1)+")", list).addClass("focus");
                }else if(e.keyCode == "13"){
                    arrowAction = true;
                    if(INDEX > -1){
                        edit(INDEX);
                        clear();
                    }else{
                        var v = $("#keyword").val().trim().toLowerCase();
                        for(var i in locs){
                            if(locs[i].CONTENT.toLowerCase() == v){
                                edit(i);
                                clear();
                            }
                        }
                    }
                }else{
                    INDEX = 0;
                    var list = $("#list");
                    $("li", list).removeClass("focus");
                }
            };
                    var xhr=null,locs=[];
                    function load(e,event){
                        event = event || window.event;
                    if(!arrowAction){
                        if(!focusAction)
                            newForm();
                        var v=e.value;
                        if(v!=""){
                            if(xhr && xhr.readyState != 4){
                                xhr.abort();
                            }
                            clear();
                            xhr = $.ajax({
                                type:"GET",
                                url: "/ajax-keyword/",
                                data:{k:v},
                                success: function(rp) {
                                    if(rp && rp.RP){
                                        locs=rp.DATA.keys;
                                        if(typeof nop==="undefined"){
                                            build(e);
                                        }
                                    }else{
                                        clear();
                                    }
                                },
                                error:function(rc) {
                                    clear();
                                }
                            });
                        }
                        }else{
                        event.stopPropagation();
                        event.preventDefault();
                        }
                    };
                    function build(e){  
                        if(typeof e === "undefined"){
                            e=("#keyword");
                        }else{
                            e=$(e);
                        }
                        INDEX=-1;
                        if(locs.length>0){
                            var dv=$("<ul id=\'list\' class=\'options\'></ul>");
                            for(var i in locs){
                                var o=$("<li class=\'"+locs[i].LANG+"\' onclick=\'edit("+i+")\'>"+locs[i].CONTENT+"</li>");
                                dv.append(o);
                            }
                            $("body").append(dv);
                            var p=e.offset();
                            var t=p.top+e.height()+8;
                            dv.css({top:t+"px", left: p.left+"px"});
                        }else{
                            clear();
                        }
                    };
                    function newForm(){
                        SELECTED=0;
                        CURRENT="";
                        rmsg();                        
                        $("#related").html("");
                    }
                    function clear(){
                        $("#list").remove();
                    };
                    function rmsg(){
                        $("#msg").html("");
                    };
                    var SELECTED=0;
                    function edit(i){
                        var o=locs[i];
                        $("#msg").html(o.CONTENT);
                        SELECTED = o;
                        $("#keyword").val(SELECTED.CONTENT);                       
                        
                        $("#related").html("");
                        $.ajax({
                            type:"GET",
                            url: "/ajax-keyword/",
                            data:{k:SELECTED.ID,synonym:1},
                            success: function(rp) {
                                if(rp && rp.RP){
                                    render(rp.DATA.keys);
                                }
                            }
                        });
                    };
                    function render(words){
                        var ul = $("<ul></ul>");
                        var li = $("<li onclick=\'addR(this)\' id=\'-1\'>Add Word</li>");
                        ul.append(li);
                        if(words.length){
                            for(var i in words){
                                var li = $("<li onclick=\'addR(this)\' id=\'"+words[i].ID+"\'>"+words[i].CONTENT+"</li>");
                                ul.append(li);
                            }
                        }
                        $("#related").append(ul);
                    }
                    var CURRENT="";
                    function addR(e){
                        e = $(e);
                        if(!e.hasClass("edit")){
                            e.addClass("edit");
                            var box=$("<input type\'text\' value=\'"+(e.html() == \'Add Word\' ? \'\':e.html())+"\' onkeydown=\'idir(this)\' onchange=\'idir(this, 1)\' /><a class=\'link\' href=\'javascript:void(0);\' onclick=\'updateR(this,event)\'>save</a><a class=\'link\' href=\'javascript:void(0);\' onclick=\'cancelR(this, event)\'>cancel</a>");
                            CURRENT=e.html();
                            e.html("");
                            e.append(box);
                            box.first().focus();
                        }
                    }
                    function cancelR(e, ex){
                        var event = ex || window.event;
                        event.stopPropagation();
                        e=$(e);
                        var li=e.parent();
                        li.html(CURRENT);
                        li.removeClass("edit");
                    }
                    function updateR(e, ex){
                        var event = ex || window.event;
                        event.stopPropagation();
                        e=$(e);
                        var li=e.parent();
                        var input=e.prev();
                        var v =input.val().trim();
                        li.html(v);
                        li.removeClass("edit");
                        if(v != CURRENT){
                            var del = 0;
                            $.ajax({
                                type:"POST",
                                url: "/ajax-keyword/",
                                data:{rid:li[0].id,wid:SELECTED.ID,content:v,synonym:1},
                                success: function(rp) {
                                    CURRENT = v;
                                    $.ajax({
                                        type:"GET",
                                        url: "/ajax-keyword/",
                                        data:{k:SELECTED.ID,synonym:1},
                                        success: function(rp) {
                                            if(rp && rp.RP){
                                                $("#related").html("");
                                                render(rp.DATA.keys);
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    };
                    function fail(m){
                        var msg = "<span class=\'fail\'></span> failed to save";
                        if(m){
                            msg = m +"<br />"+ msg;
                        }
                        $("#msg").html(msg);
                    }
                    ';
    }

    function renderDictionaryPanel() {
        ?><div><?php
            ?><ul class="ts"><?php
                ?><li><?php
                    ?><div class="lm"><?php
                        ?><label for="keyword"><?= $this->lang['keyword'] ?></label><?php
                        ?><input type="text" id="keyword" autocomplete="off" onkeydown="idir(this);navList(event)" onfocus="setFocus(this,event);" onkeyup="load(this,event);" onchange="idir(this, 1);" /><?php
                        ?><input id="add" type="button" class=btn onclick="save(this)" style="margin:0 25px" value="<?= $this->lang['new_key'] ?>" /><?php
                        ?><input id="rotate" type="button" class=btn onclick="rotate(this)" style="margin:0 25px" value="Rotate" /><?php
                    ?></div><?php
                ?></li><?php
            ?></ul><?php
        ?></div><?php
        ?><div id="msg"></div><?php
        ?><div id="delHolder"><?php
            ?><a class="link" href="javascript:void(0);" onclick="deleteKey()">delete</a><?php
        ?></div><?php
        ?><div><?php
            ?><ul class="ul_checkbox"><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="regular" onchange="updateBox(this)" type="checkbox"><label for="regular">Regular</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="abbreviation" onchange="updateBox(this)" type="checkbox"><label for="abbreviation">Abbrev</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="proper" onchange="updateBox(this)" type="checkbox"><label for="proper">Proper</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="region" onchange="updateBox(this)" type="checkbox"><label for="region">Region</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="brand" onchange="updateBox(this)" type="checkbox"><label for="brand">Brand</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="sentence" onchange="updateBox(this)" type="checkbox"><label for="sentence">Sentence</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="car" onchange="updateBox(this)" type="checkbox"><label for="car">Car</label></div><?php
                ?></li><?php
                ?><li><?php 
                    ?><div class="md-checkbox"><input id="off" onchange="updateBox(this)" type="checkbox"><label for="off">OFF</label></div><?php
                ?></li><?php
            ?></ul><?php
        ?></div><?php
        ?><div id="related"></div><?php
        
        $this->inlineQueryScript .= '$("body").click(function(e){if(e.target.id!="keyword")clear()});';
        $this->globalScript .= '
            function rotate(e){
                e = $(e);
                e.css("display", "none");
                $.ajax({
                    type:"GET",
                    url: "/ajax-keyword/",
                    data:{rotate:2},
                    success: function(rp) {
                        e.css("display", "inline-block");
                    },
                    error:function(){
                        e.css("display", "inline-block");
                    }
                });
            };            
            function setFocus(x,e){
                arrowAction=false;
                focusAction=true;
                if(x.value!=""){
                    load(x,e);
                }
            };
            var INDEX=-1,arrowAction=false,focusAction=false;
            function navList(e){
                focusAction=false;
                e = e || window.event;
                if (e.keyCode == "38") {
                    INDEX--;
                    arrowAction=true;
                }
                else if (e.keyCode == "40") {                
                    INDEX++;
                    arrowAction=true;
                }else if(e.keyCode == "37" || e.keyCode == "39"){
                    arrowAction = true;
                    return;
                }else{
                    arrowAction=false;
                }
                if(arrowAction){
                    e.stopPropagation();
                    e.preventDefault();
                    if(INDEX > locs.length -1){
                        INDEX = locs.length - 1;
                    }
                    if(INDEX < 0){
                        INDEX = 0;
                    }
                    var list = $("#list");
                    $("li", list).removeClass("focus");
                    $("li:nth-child("+(INDEX + 1)+")", list).addClass("focus");
                }else if(e.keyCode == "13"){
                    arrowAction = true;
                    if(INDEX > -1){
                        edit(INDEX);
                        clear();
                    }else{
                        var v = $("#keyword").val().trim().toLowerCase();
                        for(var i in locs){
                            if(locs[i].CONTENT.toLowerCase() == v){
                                edit(i);
                                clear();
                            }
                        }
                    }
                }else{
                    INDEX = 0;
                    var list = $("#list");
                    $("li", list).removeClass("focus");
                }
            };
                    var xhr=null,locs=[];
                    function load(e,event){
                        event = event || window.event;
                    if(!arrowAction){
                        if(!focusAction)
                        newForm();
                        var v=e.value;
                        if(v!=""){
                            if(xhr && xhr.readyState != 4){
                                xhr.abort();
                            }
                            clear();
                            xhr = $.ajax({
                                type:"GET",
                                url: "/ajax-keyword/",
                                data:{k:v},
                                success: function(rp) {
                                    if(rp && rp.RP){
                                        locs=rp.DATA.keys;
                                        if(typeof nop==="undefined"){
                                            build(e);
                                        }
                                    }else{
                                        clear();
                                    }
                                },
                                error:function(rc) {
                                    clear();
                                }
                            });
                        }
                        }else{
                        event.stopPropagation();
                        event.preventDefault();
                        }
                    };
                    function build(e){  
                        if(typeof e === "undefined"){
                            e=("#keyword");
                        }else{
                            e=$(e);
                        }
                        INDEX=-1;
                        if(locs.length>0){
                            var dv=$("<ul id=\'list\' class=\'options\'></ul>");
                            for(var i in locs){
                                var o=$("<li class=\'"+locs[i].LANG+"\' onclick=\'edit("+i+")\'>"+locs[i].CONTENT+"</li>");
                                dv.append(o);
                            }
                            $("body").append(dv);
                            var p=e.offset();
                            var t=p.top+e.height()+8;
                            dv.css({top:t+"px", left: p.left+"px"});
                        }else{
                            clear();
                        }
                    };
                    function newForm(){
                        SELECTED=0;
                        CURRENT="";
                        rmsg();                        
                        $("#proper").prop("checked", 0);
                        $("#regular").prop("checked", 0);
                        $("#abbreviation").prop("checked", 0);
                        $("#region").prop("checked", 0);
                        $("#off").prop("checked", 0);
                        $("#brand").prop("checked", 0);
                        $("#car").prop("checked", 0);
                        $("#sentence").prop("checked", 0);
                        $("#related").html("");
                        $("#delHolder").css("display","none");
                    }
                    function clear(){
                        $("#list").remove();
                    };
                    function rmsg(){
                        $("#msg").html("");
                    };
                    var SELECTED=0;
                    function edit(i){
                        var o=locs[i];
                        $("#msg").html(o.CONTENT);
                        SELECTED = o;
                        $("#keyword").val(SELECTED.CONTENT);
                        $("#proper").prop("checked", o.PROPER);
                        $("#regular").prop("checked", o.REGULAR);
                        $("#abbreviation").prop("checked", o.ABBREVIATION);
                        $("#region").prop("checked", o.REGION);
                        $("#off").prop("checked", o.OFF);
                        $("#brand").prop("checked", o.BRAND);
                        $("#car").prop("checked", o.CAR);
                        $("#sentence").prop("checked", o.SENTENCE);
                        
                        $("#delHolder").css("display","block");
                        
                        $("#related").html("");
                        $.ajax({
                            type:"GET",
                            url: "/ajax-keyword/",
                            data:{k:SELECTED.ID,related:1},
                            success: function(rp) {
                                if(rp && rp.RP){
                                    render(rp.DATA.keys);
                                }
                            }
                        });
                    };
                    function render(words){
                        var ul = $("<ul></ul>");
                        var li = $("<li onclick=\'addR(this)\' id=\'-1\'>Add Word</li>");
                        ul.append(li);
                        if(words.length){
                            for(var i in words){
                                var li = $("<li onclick=\'addR(this)\' id=\'"+words[i].ID+"\'>"+words[i].CONTENT+"</li>");
                                ul.append(li);
                            }
                        }
                        $("#related").append(ul);
                    }
                    function deleteKey(){
                        if(SELECTED != 0){
                            $.ajax({
                                type:"POST",
                                url: "/ajax-keyword/",
                                data:{did:SELECTED.ID},
                                success: function(rp) {
                                    newForm();
                                    $("#keyword").val("");
                                }
                            });
                        }
                    }
                    var CURRENT="";
                    function addR(e){
                        e = $(e);
                        if(!e.hasClass("edit")){
                            e.addClass("edit");
                            var box=$("<input type\'text\' value=\'"+(e.html() == \'Add Word\' ? \'\':e.html())+"\' onkeydown=\'idir(this)\' onchange=\'idir(this, 1)\' /><a class=\'link\' href=\'javascript:void(0);\' onclick=\'updateR(this,event)\'>save</a><a class=\'link\' href=\'javascript:void(0);\' onclick=\'cancelR(this, event)\'>cancel</a>");
                            CURRENT=e.html();
                            e.html("");
                            e.append(box);
                            box.first().focus();
                        }
                    }
                    function cancelR(e, ex){
                        var event = ex || window.event;
                        event.stopPropagation();
                        e=$(e);
                        var li=e.parent();
                        li.html(CURRENT);
                        li.removeClass("edit");
                    }
                    function updateR(e, ex){
                        var event = ex || window.event;
                        event.stopPropagation();
                        e=$(e);
                        var li=e.parent();
                        var input=e.prev();
                        var v =input.val().trim();
                        li.html(v);
                        li.removeClass("edit");
                        if(v != CURRENT){
                            var del = 0;
                            $.ajax({
                                type:"POST",
                                url: "/ajax-keyword/",
                                data:{rid:li[0].id,wid:SELECTED.ID,content:v},
                                success: function(rp) {
                                    CURRENT = v;
                                    $.ajax({
                                        type:"GET",
                                        url: "/ajax-keyword/",
                                        data:{k:SELECTED.ID,related:1},
                                        success: function(rp) {
                                            if(rp && rp.RP){
                                                $("#related").html("");
                                                render(rp.DATA.keys);
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                    function updateBox(e){
                        if(SELECTED!=0){
                            var ckd=e.checked ? 1 : 0;
                            $.ajax({
                                type:"POST",
                                url: "/ajax-keyword/",
                                data:{kid:SELECTED.ID,field:e.id,checked:ckd},
                                success: function(rp) {
                                    $("#msg").html(rp.DATA.content);
                                },
                                error:function(rc) {
                                    fail(SELECTED.CONTENT);
                                }
                            });
                        }
                    }
                    function save(){
                        if(SELECTED == 0){
                        var d={
                            kid:-1,
                            content:$("#keyword").val(),
                            off:$("#off")[0].checked ? 1 : 0,
                            car:$("#car")[0].checked ? 1 : 0,
                            sentence:$("#sentence")[0].checked ? 1 : 0,
                            region:$("#region")[0].checked ? 1 : 0,
                            proper:$("#proper")[0].checked ? 1 : 0,
                            regular:$("#regular")[0].checked ? 1 : 0,
                            abbreviation:$("#abbreviation")[0].checked ? 1 : 0,
                            brand:$("#brand")[0].checked ? 1 : 0,
                        };
                        $.ajax({
                            type:"POST",
                            url: "/ajax-keyword/",
                            data:d,
                            success: function(rp) {
                                if(rp && rp.RP){
                                    SELECTED = rp.DATA.keyword;
                                    $("#msg").html(SELECTED.CONTENT);
                                    $("#delHolder").css("display","block");
                                }else{
                                    fail();
                                }
                            },
                            error:function(rc) {
                                fail();
                            }
                        });
                        }
                    };
                    function fail(m){
                        var msg = "<span class=\'fail\'></span> failed to save";
                        if(m){
                            msg = m +"<br />"+ msg;
                        }
                        $("#msg").html(msg);
                    }
                    ';
    }

    function renderAreasAdminPanel() {
        ?><div><?php
        ?><ul class="ts"><?php
        ?><li><?php
        ?><div class="lm"><label><?= $this->lang['country'] ?></label><select onchange="CC()" id="country"><?php
        foreach ($this->urlRouter->countries as $country) {
            echo "<option value=" . strtoupper($country['uri']) . ">{$country['name']}</option>";
        }
        ?></select><input id=rotate type=button class=btn onclick="rotate(this)" style="margin:0 30px" value="<?= $this->lang['rotate'] ?>" /></div><?php
        ?></li><?php
        ?><li><?php
        ?><div class="lm"><label><?= $this->lang['keyword'] ?></label><input onfocus="build()" onkeydown="idir(this)" onkeyup="load(this)" onchange="idir(this, 1)" id="keyword" type="text" /><?php
        ?><input onclick="nek(this)" type=button class=btn style="margin:0 30px" value="<?= $this->lang['new_key'] ?>" /><?php
        ?></div></li><?php
        ?></ul><?php
        ?><form onsubmit="save();return false"><input id="id" type="hidden" value="0" /><?php
        ?><ul class="ts hy"><?php
        ?><li id="msg" class="action">كلمة جديدة</li><?php
        ?><li><label>عربي</label><input onchange="rmsg()" id="ar" class="ar" type="text" /></li><?php
        ?><li><label>English</label><input onchange="rmsg()" id="en" class="en" type="text" /></li><?php
        ?><li><textarea onchange="rmsg()" id="tar" class="ar"></textarea></li><?php
        ?><li><textarea onchange="rmsg()" id="ten" class="en"></textarea></li><?php
        ?><li class="action"><input id="submit" type="submit" class=btn value="<?= $this->lang['save'] ?>" disabled="true"/></li><?php
        ?></ul><?php
        ?></form><?php
        ?></div><?php
        $this->inlineQueryScript .= '$("body").click(function(e){if(e.target.id!="keyword")clear()});';
        $this->globalScript .= '
                    var xhr=null,locs=[];
                    function nek(e){
                        $("#id").val(0);
                        $("#ar").val("");
                        $("#en").val("");
                        $("#ten").val("");
                        $("#tar").val("");
                        $("#submit").removeAttr("disabled");
                        $("#msg").html("كلمة جديدة");
                    };
                    function rotate(e){
                        e=$(e);
                        var c=$("#country").val();
                        e.next().remove();
                        e.css("visibility","hidden");
                        e.parent().append("<span class=\'load\'></span>");
                        xhr = $.ajax({
                            type:"GET",
                            url: "/ajax-keyword/",
                            data:{rotate:1,c:c},
                            success: function(rp) {
                                if(rp && rp.RP){
                                    var d=("<span class=\'done\'></span>");
                                    e.next().remove();
                                    e.css("visibility","visible");
                                    e.parent().append(d);
                                }else{
                                    frot(e);
                                }
                            },
                            error:function(rc) {
                                frot(e);
                            }
                        });
                    };
                    function frot(e){                    
                        var d=("<span class=\'fail\'></span>");
                        e.next().remove();
                        e.css("visibility","visible");
                        e.parent().append(d);
                    }
                    function CC(){
                        clear();
                        rmsg();
                        $("#id").val(0);
                        $("#ar").val("");
                        $("#en").val("");
                        $("#ten").val("");
                        $("#tar").val("");
                        $("#submit").removeAttr("disabled");
                        $("#rotate").next().remove();
                        locs=[];
                        load($("#keyword")[0],1);
                        $("#msg").html("كلمة جديدة");
                    };
                    function load(e,nop){
                        var c=$("#country").val();
                        var v=e.value;
                        if(v!=""){
                            if(xhr && xhr.readyState != 4){
                                xhr.abort();
                            }
                            clear();
                            xhr = $.ajax({
                                type:"GET",
                                url: "/ajax-keyword/",
                                data:{k:v,c:c},
                                success: function(rp) {
                                    if(rp && rp.RP){
                                        locs=rp.DATA.keys;
                                        if(typeof nop==="undefined"){
                                            build(e);
                                        }
                                    }else{
                                        clear();
                                    }
                                },
                                error:function(rc) {
                                    clear();
                                }
                            });
                        }
                    };
                    function build(){  
                        if(typeof e === "undefined"){
                            e=("#keyword");
                        }else{
                            e=$(e);
                        }
                        if(locs.length>0){
                            var dv=$("<ul id=\'list\' class=\'options\'></ul>");
                            for(var i in locs){
                                var o=$("<li onclick=\'edit("+i+")\'>"+locs[i].KEYWORD+"</li>");
                                dv.append(o);
                                if(i>6)break;
                            }
                            $("body").append(dv);
                            var p=e.offset();
                            var t=p.top+e.height()+8;
                            dv.css({top:t+"px", left: p.left+"px"});
                        }else{
                            clear();
                        }
                    };
                    function clear(){
                        $("#list").remove();
                    };
                    function rmsg(){
                        $("#msg").html("");
                    };
                    function edit(i){
                        rmsg();
                        var o=locs[i];
                        $("#id").val(o.ID);
                        $("#ar").val(o.KEYWORD);
                        $("#en").val(o.EN);
                        $("#ten").val(o.EN_FORM);
                        $("#tar").val(o.AR_FORM);
                        $("#submit").removeAttr("disabled");
                        $("#msg").html("");
                    };
                    function save(){
                        var d={
                            id:$("#id").val(),
                            ar:$("#ar").val(),
                            en:$("#en").val(),
                            ten:$("#ten").val(),
                            tar:$("#tar").val(),
                            cc:$("#country").val()
                        };
                        $.ajax({
                            type:"POST",
                            url: "/ajax-keyword/",
                            data:d,
                            success: function(rp) {
                                if(rp && rp.RP){
                                    $("#rotate").next().remove();
                                    $("#msg").html("<span class=\'done\'></span> تم الحفظ");
                                    load($("#keyword")[0],1);
                                }else{
                                    fail();
                                }
                            },
                            error:function(rc) {
                                fail();
                            }
                        });
                    };
                    function fail(){
                        $("#msg").html("<span class=\'fail\'></span> فشلت عملية الحفظ");
                    }
                    ';
    }

    function renderStatisticsPanel() {
                                $langIndex = $this->urlRouter->siteLanguage == 'ar' ? 1 : 2;
                                ?><div class="filters"><?php
                                ?><select onchange="fetchStat()" id="pubId"><?php
                                ?><option value="0"><?= $this->lang['all_pubs'] ?></option><?php
                                ?><option value="1"><?= $this->lang['mourjan'] ?></option><?php
                                ?><option value="2"><?= $this->lang['other_sources'] ?></option><?php
                                ?></select><?php
                                ?><select onchange="fetchStat()" id="cnId"><?php
                                ?><option value="0"><?= $this->lang['opt_all_countries'] ?></option><?php
        foreach ($this->urlRouter->countries as $id => $pub) {
            ?><option value="<?= $id ?>"><?= $pub['name'] ?></option><?php
        }
        ?></select><?php
        ?><select onchange="fetchStat()" id="cId" style="display:none"><?php
        ?><option value="0"><?= $this->lang['all_cities'] ?></option><?php
        foreach ($this->urlRouter->cities as $id => $pub) {
            ?><option value="<?= $pub[4] . '_' . $id ?>"><?= $pub[$langIndex] ?></option><?php
        }
        ?></select><?php
        ?><select onchange="fetchStat()" id="secId"><?php
        ?><option value="0"><?= $this->lang['opt_all_categories'] ?></option><?php
        foreach ($this->urlRouter->sections as $id => $section) {
            ?><option value="<?= $id ?>"><?= $section[$langIndex] ?></option><?php
        }
        ?></select><?php
        ?></div><?php
        ?><div id="cron"><a href="javascript:fetchStat(0);">today</a><a href="javascript:fetchStat(7);">7 days</a><a href="javascript:fetchStat(15);">15 days</a><a href="javascript:fetchStat(30);">30 days</a><a href="javascript:fetchStat(90);">3 months</a><a href="javascript:fetchStat(180);">6 months</a><a href="javascript:fetchStat(365);">1 year</a></div><?php
        ?><div id="statDv" class="load"></div><?php
        $this->globalScript .= "
var HSLD=0,span=30; 
var fetchStat = function(x){
if(typeof x === 'undefined')x=span;
span=x;
var sec=$('#secId').val();
var pub=$('#pubId').val();
var cn=$('#cnId');
var c=$('#cId');

    cn.css('display','inline-block');
    var j=0,cnv=cn.val(),cnl=cnv.length+1;
    c.children().each(function(i,e){
        var v=e.value;
        if(v=='0' || v.substring(0,cnl)==cnv+'_'){
            e.style.display='block';
            j++;
        }else{
            e.style.display='none';
            if(e.selected){
                c.val(0);
            }
        } 
    });
    if(j>2){
        c.css('display','inline-block');
    }else{
        c.css('display','none');
    }

var cnh=cn.val();
$.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                ads:1,
                sec:sec,
                pub:pub,
                cn:cnh,
                c:c.val().substring(cnh.length+1),
                span:x
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    if(rp.DATA.d){
                        var x =$('#statDv');
                        x.removeClass('hxf');
                        var gS={
                            chart: {
                                spacingRight:0,
                                spacingLeft:0
                            },
                            title: {
                                text: (lang=='ar'?'الاعلانات الواردة':'incoming ads'),
                                style:{
                                    'font-weight':'bold',
                                    'font-family':(lang=='ar'?'tahoma,arial':'verdana,arial'),
                                    'direction':(lang=='ar'?'rtl':'ltr')
                                }
                            },
                            xAxis: {
                                type: 'datetime',
                                title: {
                                    text: null
                                }
                            },
                            yAxis: {
                                title: {
                                    text: null
                                }
                            },
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                enabled: false
                            },    
                            colors:[
                                '#2f7ed8',
                                '#ff9000'
                            ]
                        };
                        var series = [{
                            type: 'line',
                            name: 'ads',
                            pointInterval:24 * 3600 * 1000,
                            pointStart: rp.DATA.d,
                            data: rp.DATA.c
                        }];
                        
                        gS['series']=series;
                        x.highcharts(gS);
                    }else{
                        var x=$('#statDv');
                        x.removeClass('load');
                        x.addClass('hxf');
                        x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                    }
                }else{
                    var x=$('#statDv');
                    x.removeClass('load');
                    x.addClass('hxf');
                    x.html(lang=='ar'?'فشل محرك مرجان بالحصول على الاحصائيات':'Mourjan system failed to load statistics');
                }
            }
       });
};
(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='" . $this->urlRouter->cfg['url_highcharts'] . "/min.js';
        sh.onload=sh.onreadystatechange=function(){
        if (!HSLD && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete')){
            HSLD=1;  
       fetchStat();
   }};head.insertBefore(sh,head.firstChild)})();
                                
                        ";
    }

    function renderUserAdminPanel() {
        ?><div class=row><?php
        ?><div class="col-12"><?php
        ?><div class="card admin"><?php
        ?><ul class=ts><?php
        ?><form method=get><?php
        
        ?><li><?php
        ?><div class="lm"><label>UID/UUID/EMAIL:</label><input name="p" type="text" value="<?= $this->uid ? $this->uid : '' ?>" /><?php
        ?><input type=submit class=btn value="<?= $this->lang['review'] ?>" /><?php
        ?></div><?php
        
        ?></form><?php
        ?></li><?php
        
        if (isset($_GET['p']) && count($this->multipleAccounts)) {
            $lang = $this->router()->isArabic() ? '' : $this->router()->language . '/';

            $selected = $this->get('selected', 'uint') ? $this->get('selected', 'uint') : $this->multipleAccounts[0];
            ?><ul class="ts multi"><?php
            foreach ($this->multipleAccounts as $acc) {
                if ($selected == $acc) {
                    echo "<li><b>{$acc}</b></li>";
                } else {
                    echo "<li><a href='/admin/{$lang}?p={$this->uid}&selected={$acc}'>{$acc}</a></li>";
                }
            }
            ?></ul><?php
        }

        
        ?><li><?php        
        ?><form method=get><?php
        ?><div class="lm"><label><?= $this->lang['labelP0'] ?>:</label><input name="t" type=tel value="<?= $this->mobile_param ?>" /><?php
        ?><input type=submit class=btn value="<?= $this->lang['review'] ?>" /><?php
        ?></div><?php
        ?></form><?php
        ?></li><?php
        
        
        if (isset($_GET['t']) && count($this->multipleAccounts)) {
            $usersHTML=[];
            $lang = $this->router()->isArabic() ? '' : $this->router()->language . '/';
            $parameter = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 0]]);
            $selected = $this->get('selected', 'uint') ? $this->get('selected', 'uint') : $this->multipleAccounts[0];
            $usersHTML[]='<ul class="ts multi">';
            foreach ($this->multipleAccounts as $acc) {
                if ($selected==$acc) {
                    $usersHTML[]="<li><b>{$acc}</b></li>";
                } else {
                    $usersHTML[]="<li><a href='/admin/{$lang}?t={$parameter}&selected={$acc}'>{$acc}</a></li>";
                }
            }
            $usersHTML[]='</ul>';
        }
        
        ?><li><?php        
        ?><form method=get><?php
        ?><div class="lm"><label>AID:</label><input name="r" type="ad" value="<?= $this->aid ? $this->aid : '' ?>" /><?php
        ?><input type=submit class=btn value="<?= $this->lang['review'] ?>" /><?php
        ?></div><?php
        ?></form><?php
        ?></li><?php
        ?></ul><?php

        if ($this->userdata && count($this->userdata) && (($this->aid == 0 && $this->userdata[0]) || ($this->aid))) {
            if ($this->aid==0) {
                foreach ($this->userdata as $record) {
                    $this->parseUserRecordData($record);
                    echo '<br/>';
                }
            } 
            else {
                
                $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', 
                            function ($match) {
                                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
                            }, 
                            json_encode($this->userdata, JSON_FORCE_OBJECT));

                $this->userdata = json_decode(str_replace('class=\"pn\"', 'class=pn', $str));
                if (isset($this->userdata->TITLE)) { unset($this->userdata->TITLE); }
                ?><script>
                    var userRaw='<?= json_encode($this->userdata) ?>';
                </script><?php
                //echo json_encode($this->userdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

                $this->globalScript .= '
                            var block=function(id,e){
                                e=$(e).parent().parent();
                                if(e.next().hasClass("rpd")){
                                    e.next().remove()
                                }else{
                                    var d=$("<div class=\'rpd cct\'><b>سبب الايقاف؟</b><textarea onkeydown=\'idir(this)\' onchange=\'idir(this,1)\'></textarea><input type=\'button\' onclick=\'rpa("+id+",this)\' class=\'bt\' value=\'ايقاف\'><input type=\'button\' onclick=\'closeA(this)\' class=\'bt cl\' value=\'إلغاء\'></div>");                                
                                    d.insertAfter(e);
                                }
                            };
                            var suspend=function(id,e){
                                Dialog.show("susp_dialog",null,function(){suspA(e,id)});  
                            };
                            var suspA=function(e,usr){
                                e=$(e);
                                e.addClass("load");
                                $.ajax({
                                    type:"POST",
                                    url:"/ajax-ususpend/",
                                    data:{i:usr,v:$("#suspT").val(),m:$("#suspM").val()},
                                    dataType:"json",
                                    success:function(rp){
                                        e.removeClass("load");
                                        if (rp.RP) {
                                            e.html("Release");
                                            e[0].onclick=function(){};
                                            e.attr("href","?p="+usr+"&a=-1");
                                            e.parent().css("float","left");
                                        }
                                    },
                                    error:function(){
                                        e.removeClass("load");
                                    }
                                });
                            };
                            var closeA=function(e){
                                e=$(e).parent().remove()
                            };
                            var rpa=function(id,e){
                                e=$(e);
                                var m=$("textarea",e.parent()).val();
                                e.parent().remove();
                                if(m.length>0){
                                    $.ajax({
                                        type:"POST",
                                        url:"/ajax-ublock/",
                                        data:{
                                            i:id,
                                            msg:"Blocked From Admin Panel By Admin "+UID+" reason <<"+m+">>"
                                        },
                                        dataType:"json",
                                        success:function(rp){
                                            document.location="";
                                        }
                                    });
                                }
                            };
                        ';
            } 
            else {
                ?><div class=ctr><?php
            if ($this->uid || $this->mobile_param != '') {
                ?><h4>NO USER DATA FOUND</h4><?php
            }
            ?></div><?php
            if (isset($_GET['t']) && $_GET['t']) {
                echo '<br />';
                if (!($this->userdata && count($this->userdata))) {
                    if (Core\Model\NoSQL::getInstance()->isBlacklistedContacts([$_GET['t']])) {
                        ?><div style="margin:5px;padding:10px;direction:ltr;overflow:hidden;display:block"><?php
                        ?><p>This number is blacklisted for the following reason:</p><br /><?php
                        ?><p style="text-align:center;color:red;font-size:16px;font-weight:bold"><?php
                        echo Core\Model\NoSQL::getInstance()->getBlackListedReason($_GET['t']);
                        ?></p><?php
                        ?></div><div style="background-color:darkkhaki;margin:5px;padding:10px;direction:ltr;overflow:hidden;display:block"><?php
                        ?><h4 style="float:left">Would you like to remove number from blacklist?</h4><?php
                        ?><a class="lnk" style="float:right" href="?p=<?= $_GET['t'] ?>&action=unlist">remove</a><?php
                        ?></div><br /><?php
                    } else {
                        if(substr($_GET['t'],0,2) != '00' && substr($_GET['t'],0,1) != '+'){
                            $number = '+'.$_GET['t'];
                        }else{
                            $number = $_GET['t'];
                        }
                        $validator = libphonenumber\PhoneNumberUtil::getInstance();
                        $num = $validator->parse($number, 'LB');

                        if ($validator->isValidNumber($num)) {
                            ?><div style="background-color:darkkhaki;margin:5px;padding:10px;direction:ltr;overflow:hidden;display:block"><?php
                            ?><h4 >Would you like to blacklist this number?</h4><?php
                            ?><form method="GET" onsubmit="return black();"><?php
                            ?><textarea onkeydown="idir(this)" onchange="idir(this, 1)" name="reason" id="breason" style="padding:10px;margin:20px;width:660px;height:100px" placeholder="please specify a reason"></textarea><?php
                            ?><input type="hidden" name="p" value="<?= $_GET['t'] ?>" /><?php
                            ?><input type="hidden" name="action" value="blacklist" /><?php
                            ?><p style="text-align:center"><input class=btn type="submit" value="blacklist"/></p><?php
                            ?></form></div><br /><?php
                            $this->globalScript .= '
                                                var black=function(){
                                                    var e=$("#breason");
                                                    if(e.val().length < 3){
                                                        e.addClass("err");
                                                        return false;
                                                    }else{
                                                        return true;
                                                    }
                                                };
                                            ';
                        } else {
                            ?><div class="ctr"><?php
                            ?><h4 style="color:red">MOBILE NUMBER IS NOT VALID</h4><?php
                            ?></div><?php
                        }
                    }
                    echo '<br />';
                }
            }
        }
        ?></ul><?php
        if(isset($usersHTML)){
            echo implode('', $usersHTML);
        }
        echo '</div>';
        ?></div></div></div><?php
        echo '<div id=userDIV dir=ltr></div>';
    }

    function parseUserRecordData($record) {
        if (is_array($record) && count($record)) {
            echo '<ul class=tbs>';
            echo '<li><a class=btn href="/myads/?sub=drafts&u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Drafts</a></li>';
            echo '<li><a class=btn href="/myads/?sub=pending&u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Pending</a></li>';
            echo '<li><a class=btn href="/myads/?u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Active</a></li>';
            echo '<li><a class=btn href="/myads/?sub=archive&u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Archived</a></li>';
            echo '<li><a class=btn href="/myads/?sub=deleted&u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Deleted</a></li>';
            echo '<li><a class=btn href="/statement/?u=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '">Balance</a></li>';
            if (isset($record['suspended']) && $record['suspended'] == 'YES') {
                echo '<li><a class=btn href="/admin/?p=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '&a=-1">Release</a></li>';
            }
            if ($record[\Core\Model\ASD\USER_LEVEL]==5) {
                echo '<li><a class=btn style="border-left:1px solid #CCC" href="?p=' . $record[\Core\Model\ASD\SET_RECORD_ID] . '&action=unblock">Unblock</a></li>';
            } else {
                echo '<li><a class=btn onclick="block(' . $record[\Core\Model\ASD\SET_RECORD_ID] . ',this)" href="javascript:void(0);">Block</a></li>';
                if (!(isset($record['suspended']) && $record['suspended']=='YES')) {
                    echo '<li><a class=btn onclick="suspend(' . $record[\Core\Model\ASD\SET_RECORD_ID] . ',this)" href="javascript:void(0);">Suspend</a></li>';
                }
            }
            echo '</ul>';
            
            //echo json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            ?><div id="susp_dialog" class="dialog"><?php
            ?><div class="dialog-box ctr"><select id="suspT" style="direction:ltr;width:200px"><option value="1">1 hour</option><option value="6">6 hours</option><option value="12">12 hours</option><option value="18">18 hours</option><option value="24">24 hours</option><option value="30">30 hours</option><option value="36">36 hours</option><option value="42">42 hours</option><option value="48">48 hours</option><option value="54">54 hours</option><option value="60">60 hours</option><option value="66">66 hours</option><option value="72">72 hours</option></select><?php
            ?><br /><br /><textarea style="height:100px" onkeydown="idir(this)" onchange="idir(this, 1)" id="suspM" placeholder="<?= $this->lang['reason_suspension'] ?>"></textarea><?php
            ?></div><?php
            ?><div class="dialog-action"><?php
            ?><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['suspend'] ?>" /><?php
            ?></div><?php
            ?></div><?php
            
            
            ?><script>var userRaw='<?= json_encode($record, JSON_FORCE_OBJECT) ?>';</script><?php
        }
    }
}
