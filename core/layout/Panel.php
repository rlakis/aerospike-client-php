<?php
\Config::instance()->incLayoutFile('Page');

class Panel extends Page {

    function __construct(Core\Model\Router $router) {
        parent::__construct($router);
        if ($this->router()->config()->get('active_maintenance')) {
            $this->user->redirectTo($this->router()->getLanguagePath('/maintenance/'));
        }
        $this->forceNoIndex=true;        
        $this->router()->config()->disableAds();
        
        $this->render();
    }
    
    
    function mainMobile() {
    }
    
    
    function main_pane() { 
        echo '<div class=row><div class=col-12><div class=card>';
        $this->renderBalanceBar();
        $this->inlineQueryScript.='
            $.ajax({
                type:"POST",
                url:"/ajax-home/",
                dataType:"json",
                data:{hl:lang},
                success:function(rp){
                    if (rp.RP) {
                        var note=$("#note");
                        if(typeof rp.DATA.balance !== "undefined"){
                            var t=$("#coins");
                            t.html(t.html()+": "+rp.DATA.balance);
                        }
                        if(typeof rp.DATA.ads  !== "undefined"){
                            var a=rp.DATA.ads;
                            reCount(a[0],0);
                            reCount(a[1],1);
                            reCount(a[2],2);
                            reCount(a[3],3);
                            if(a[4]){
                                var e=$("<li><a class=\'shake warn\' href=\'/myads/"+(lang=="ar"?"":lang+"/")+"?sub=pending\'><span class=\'fail\'></span>"+(lang=="ar"? "هنالك "+( a[4] == 1 ? "اعلان قد تم رفض عرضه" : (a[4] == 2 ? "اعلانين قد تم رفض عرضهما" : (a[4] < 11 ? a[4]+" اعلانات قد تم رفض عرضها" : a[4]+" اعلان قد تم رفض عرضهم" )))+". انقر(ي) هنا للمراجعة":"You have "+(a[4] > 1 ? a[4]+" ads that were rejected.":"1 ad that was rejected.")+" Click here to review")+"</a></li>");
                                note.prepend(e);
                            }
                        }
                        if(typeof rp.DATA.props  !== "undefined"){
                            qprop(rp.DATA.props);
                        }else{
                            prini();
                        }
                        if(typeof rp.DATA.favs  !== "undefined"){
                            $("#favorite").append($("<span class=\'notifier\'>"+rp.DATA.favs+"</span>"));
                        }
                        if(typeof rp.DATA.watch  !== "undefined"){
                            $("#watchlist").append($("<span class=\'notifier\'>"+rp.DATA.watch+"</span>"));
                        }
                        if(typeof rp.DATA.shouts  !== "undefined"){
                            var s=rp.DATA.shouts;
                            var n;
                            for(var i in s){
                                if(s[i].L.length){
                                    n=$("<li id=\'"+s[i].ID+"\'><span class=\'close\'></span><a class=\'shake\' href=\'"+s[i].L+"\'>"+s[i].T+"</a></li>");
                                }else{
                                    n=$("<li id=\'"+s[i].ID+"\'><span class=\'close\'></span><p class=\'shake\'>"+s[i].T+"</p></li>");
                                }
                                var g=$(".close",n).hover(function(){$(this).next().addClass("on")},function(){$(this).next().removeClass("on")});
                                g.click(function(){
                                    var id = $(this).parent().attr("id");
                                    $(this).parent().fadeOut(400, function(){$(this).remove()});
                                    $.ajax({
                                        type:"POST",
                                        url:"/ajax-delshout/",
                                        dataType:"json",
                                        data:{i:id}
                                    });
                                });
                                note.append(n);
                            }
                        }
                    }
                }
            });
            function reCount(c,i){
                var u=$("<span class=\'notifier\'>"+c+"</span>");
                switch(i){
                    case 0:
                        $("#active").append(u);
                        break;
                    case 1:
                        $("#pending").append(u);
                        break;
                    case 2:
                        $("#draft").append(u);
                        break;
                    case 3:
                        $("#archive").append(u);
                        break;
                }
            };
            $(".close").click(function(){$(this).parent().remove()}).hover(function(){$(this).next().addClass("on")},function(){$(this).next().removeClass("on")});
            
        ';
        
        if ($this->user()->id() && $this->user()->level()!=5) {
                $this->globalScript .= 'function prini(){var t=$(\'<div id="prob" class="account '. $this->router()->language .'">         
                <a href="javascript:void(0)" onclick="prop()" class="option full settings"><span class="j prop"></span> '. $this->lang['myPropspace'] .'</a>
                </div><div id="prop_dialog" class="dialog dlg-fix"><div class="dialog-box"><div><input id="purl" onfocus="mprop(\\\'\\\')" class="prop" type="text" placeholder="http://xml.propspace.com/feed/xml.php" /></div><div class="msg inf err ctr"></div></div> 
                    <div class="dialog-action"><input type="button" class="cl" value="'. $this->lang['cancel'] .'" /><input type="button" value="'. $this->lang['connect'] .'" /></div> 
                </div>
                <div id="prop_load" class="dialog">
                    <div class="dialog-box"><div class="ldl ctr"><span class="load loads"></span> '. $this->lang['prop_reading_ads'] .'</div></div>
                    <div class="dialog-action"><input type="button" class="cl" value="'. $this->lang['cancel'] .'" /></div>
                </div>
                <div id="prop_done" class="dialog">
                    <div class="dialog-box"><div class="ldl ctr"><span class="done"></span> '. $this->lang['prop_added'] .'</div></div> 
                    <div class="dialog-action"><input type="button" value="'. $this->lang['continue'] .'" /></div>
                </div>\');
                $(".col1").append(t)};
                ';
        }
        else {
            $this->globalScript .='function prini(){};';
        }
        
        $this->globalScript.='
            function prop(){
                $("#purl").val("");
                mprop("");
                Dialog.show(
                    \'prop_dialog\',
                    null,
                    uprop
                )
            };
            var REQ=null,TEQ=null;
            function qprop(k){ 
                if(k.length==0)return;
                var d=$("#prob");
                d.after().remove();
                d=$("#props");
                if(d.length==0){
                    d=$("<ul class=\'account seli h sh rct en\'><li><b>'.$this->lang['prop_title'].'</b></li></ul><ul id=\'props\' class=\'account seli sh\'></ul>");
                    $(".col1").append(d);
                }
                d=$("#props");
                for(var i in k){
                    var s="<span title=\''.$this->lang['link_ok'].'\' class=\'done\'></span>";
                    if(0 && k[i][2]==null){
                        s="<span title=\''.$this->lang['link_no'].'\' class=\'fail\'></span>";
                    }
                    var o=$("<li id=\'p"+k[i][0]+"\'>"+s+" "+k[i][1]+" <span onclick=\'dprop(this)\' class=\'lnk\'>'.strtolower($this->lang['remove']).'</span></li>");
                    d.append(o);
                }
            };
            function xprop(v){
                REQ=$.ajax({
                    type:"GET",
                    url:"/ajax-propspace/",
                    dataType:"json",
                    data:{hl:lang,url:v},
                    success:function(rp){
                        REQ=null;
                        if(rp.RP){
                            qprop([rp.DATA.feed]);
                            Dialog.show(
                                \'prop_done\'
                            );
                        }else{
                            fprop(rp.MSG);
                        }
                    },
                    error:function(rp){
                        REQ=null;
                        var m=\'<span class="fail"></span> '.$this->lang['sys_error'].'\';
                        fprop(m);
                    }
                });
            };
            function dprop(e){
                if(confirm("'.$this->lang['prop_delete'].'")){
                    
                    var li=$(e.parentNode);
                    e=$(e);
                    var i=li.attr("id").substring(1);
                    var ld=$("<span class=\'load loads\'></span>");
                    e.hide();
                    e.after(ld);
                    REQ=$.ajax({
                        type:"GET",
                        url:"/ajax-propspace/",
                        dataType:"json",
                        data:{del:i},
                        success:function(rp){
                            ld.remove();
                            if(rp.RP){
                                $("span:first-child",li).css("visibility","hidden");
                                li.css("text-decoration", "line-through");
                                li.parent().prev().remove();
                                li.parent().remove();
                                prini();
                            }else{
                                e.show();
                            }
                        },
                        error:function(rp){
                            ld.remove();
                            e.show()
                        }
                    });
                }
            };
            function uprop(){
                var v = $("#purl").val().trim();
                mprop("");
                if(v.match(/^(?:http(?:s|):\/\/|)(?:me|)xml\.propspace\.com\/(?:me|)feed\/xml\.php/)){
                    Dialog.show(
                        \'prop_load\',
                        null,
                        null,
                        clprop
                    );
                    if(REQ && REQ.readyState != 4){
                        REQ.abort();
                    }
                    TEQ=setTimeout(function(){xprop(v)},1000);
                }else{
                    var m="<span class=\'fail\'></span> "+(lang=="ar"?"رابط PropSpace الالكتروني المدخل غير صحيح":"provided link is not a valid PropSpace XML feed link");
                    mprop(m);
                }
            };
            function fprop(m){
                mprop(m);
                Dialog.show(
                    \'prop_dialog\',
                    null,
                    uprop
                )
            };
            function mprop(m){
                $("#prop_dialog .msg").html(m);
            };
            function clprop(){
                mprop("");
                if(REQ && REQ.readyState != 4){
                    REQ.abort();
                }
                if(TEQ){
                    clearTimeout(TEQ);
                    TEQ=null
                }
                var v=$("#purl").val();
                prop();
                $("#purl").val(v);
            };';
        
        if ($this->user()->id()) {
            echo '<div class="account">';
            echo '<a href="', $this->router()->getLanguagePath('/post/'), '" class="btn half"><span class="j pub"></span>', $this->lang['button_ad_post_m'], '</a>';
            echo '<a id=active href="', $this->router()->getLanguagePath('/myads/'), '" class="btn option quarter active"><span class="pj ads1"></span>', $this->lang['ads_active'], '</a>';
            echo '<a id=pending href="', $this->router()->getLanguagePath('/myads/'), '?sub=pending" class="btn option quarter pending"><span class="pj ads2"></span>', $this->lang['home_pending'], '</a>';
            echo '<a id=draft href="', $this->router()->getLanguagePath('/myads/'), '?sub=drafts" class="btn option draft"><span class="pj ads3"></span>', $this->lang['home_drafts'], '</a>';
            echo '<a id=archive href="', $this->router()->getLanguagePath('/myads/'), '?sub=archive" class="btn option archive"><span class="pj ads4"></span>', $this->lang['home_archive'], '</a>';
            echo '<a id=favorite href="', $this->router()->getLanguagePath('/favorites/'), '?u=', $this->user->info['idKey'], '" class="btn option half favorite"><span class="j fva"></span>', $this->lang['myFavorites'], '</a>';
            echo '<a href="', $this->router()->getLanguagePath('/statement/'), '" class="btn half balance"><span class="pj coin"></span><span id=coins>', $this->lang['myBalance'], '</span></a>';
            echo '<a href="', $this->router()->getLanguagePath('/account/'), '" class="btn option full settings"><span class="j sti"></span>', $this->lang['myAccount'], '</a>';
            echo '</div>';
            

            echo '<div class=card-footer>';
            echo '<a href="', $this->router()->getLanguagePath('/gold/'), '"><span class="rj add"></span>', $this->lang['get_gold'], '</a>';
            echo '<div class=float-right style="padding-bottom:12px"><a href="', '/web/lib/hybridauth/?logout=', $this->user()->provider(), '">Sign out</a></div>';
            echo '</div>';
           /* if($this->user->info['level']!=5){
                ?><div id="prob" class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
                ?><a href="javascript:void(0)" onclick="prop()" class="option full settings"><span class="j prop"></span> <?= $this->lang['myPropspace'] ?></a><?php
                ?></div><?php
            }*/
            
        }
        else {
            $this->renderLoginPage();
        }
        
        echo '</div></div></div>';
    }
    
}