<?php
require_once 'Page.php';

class Panel extends Page{

    function __construct($router){
        parent::__construct($router);
        if($this->urlRouter->cfg['active_maintenance']){
            $this->user->redirectTo('/maintenance/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }        
        $this->forceNoIndex=true;        
        $this->urlRouter->cfg['enabled_ads']=0;
        $this->set_require('css', array('home'));
        
        $this->inlineCss.='input.prop{    
            width: 400px;
            padding: 5px 10px;
            direction: ltr;
            text-align: left;
        }';
        
        $this->render();
    }
    
    function mainMobile() {
        
    }
    
    function main_pane(){ 
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
        $this->globalScript.='
            function prop(){
                Dialog.show(
                    \'prop_dialog\',
                    \'<input class="prop" type="text" placeholder="http://xml.propspace.com/feed/xml.php" />\'
                )
            };';
        $lang = $this->urlRouter->siteLanguage == 'ar' ? '':$this->urlRouter->siteLanguage.'/';
        if($this->user->info['id']){
            ?><ul id="note" class='note <?= $this->urlRouter->siteLanguage ?>'></ul><?php
            ?><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php 
            ?><a href="/post/<?= $lang ?>" class="option half"><span class="j pub"></span> <?= $this->lang['button_ad_post_m'] ?></a><?php
                ?><a href="/statement/<?= $lang ?>" class="option half balance"><span class="pj coin"></span> <span id="coins"><?= $this->lang['myBalance'] ?></span></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php     
                ?><a id="active" href="/myads/<?= $lang ?>" class="option quarter active"><span class="pj ads1"></span><br /><?= $this->lang['ads_active'] ?></a><?php
                ?><a id="pending" href="/myads/<?= $lang ?>?sub=pending" class="option quarter pending"><span class="pj ads2"></span><br /><?= $this->lang['home_pending'] ?></a><?php
                ?><a id="draft" href="/myads/<?= $lang ?>?sub=drafts" class="option quarter draft"><span class="pj ads3"></span><br /><?= $this->lang['home_drafts'] ?></a><?php
                ?><a id="archive" href="/myads/<?= $lang ?>?sub=archive" class="option quarter archive"><span class="pj ads4"></span><br /><?= $this->lang['home_archive'] ?></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
                ?><a id="favorite" href="/favorites/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>" class="option half favorite"><span class="j fva"></span> <?= $this->lang['myFavorites'] ?></a><?php
                ?><a id="watchlist" href="/watchlist/<?= $lang ?>?u=<?= $this->user->info['idKey'] ?>" class="option half watchlist"><span class="j eye"></span> <?= $this->lang['myList'] ?></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
                ?><a href="/account/<?= $lang ?>" class="option full settings"><span class="j sti"></span> <?= $this->lang['myAccount'] ?></a><?php
            ?></div><div class="account <?= $this->urlRouter->siteLanguage ?>"><?php         
            ?><a href="javascript:void(0)" onclick="prop()" class="option full settings"><span class="j prop"></span> <?= $this->lang['myPropspace'] ?></a><?php
            ?></div><?php
            
            
            ?><div id="prop_dialog" class="dialog"><?php
                ?><div class="dialog-box"></div><?php 
                ?><div class="dialog-action"><input type="button" class="cl" value="<?= $this->lang['cancel'] ?>" /><input type="button" value="<?= $this->lang['connect'] ?>" /></div><?php 
            ?></div><?php
        }else{
            $this->renderLoginPage();
        }
    }
    
}
?>
