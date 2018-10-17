function are(e){
    var d=mask(e);
    var i=e.parentNode.parentNode.id;
    $.ajax({
        type:"POST",
        url:"/ajax-arenew/",
        data:{i:i},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html(lang=='ar'?'تم تجديد الإعلان وبإنتظار معالجة محرك مرجان لإعادة النشر':'Ad is renewed and pending Mourjan system processing');
            }else {
                d.remove()
            }
        },
        error:function(){
            d.remove()
        }
    })
}

function mask(e,r){
    var a;
    if(r)a=$(e);
    else a=$(e.parentNode.parentNode);
    var d=$("<div class=\'od load\'></div>");
    var w=a.outerHeight();
    d.height(w);
    d.width(a.outerWidth());
    d.css("line-height",w+"px");
    d.css('top',0);
    a.append(d);
    return d
}

var li=$("ul.ls li"),lip,ptm,atm={},liCnt,aCnt=0;

function rePic(){
    var h=$(window).height();
    if(!lip){
        lip=$(".ig",li);
        liCnt=lip.length;
    }
    lip.each(function(i,e){
        if(!atm[i] && e.parentNode){
            var r = e.getBoundingClientRect();
            var k=r.top;
            if(k>=-100 && k<=h+100){
                var id=e.parentNode.parentNode.id;
                if(sic[id].indexOf('||')>-1){
                    var us=sic[id].split('||');
                    var idx=0;
                    var hd=0;
                    if(e.parentNode.className=='pimgs'){
                        idx=$(e).index();
                        hd=1;
                    }else{
                        idx=us.length-1;
                    }
                    e.innerHTML=us[idx];
                    var rxp=new RegExp("/repos/");
                    if(hd && rxp.test(us[idx])){
                    var d=$('<span class="del"></span>');
                    var im=$(e);
                    im.append(d);
                    im.click(function(){
                        var m = $('img',im);
                        var s=m.attr('src').replace('/s/','/d/');
                        var pr = $('<div class="iprev"><div id="dialog-mask"></div><img src="'+s+'" /></div>');
                        var i = $('img',pr);
                        if(m.width()>m.height()){
                            i.css('width','100%');
                        }else{
                            i.css('height','100%');
                        }
                        $('body').append(pr);
                        pr.click(function(){
                            pr.remove()
                        });
                    });
                    d.click(function(){
                        var g=$(this).parent();
                        var p=g.parent();
                        var idx=g.index();
                        g.css('opacity','0.6');
                        $.ajax({
                            type: "GET",
                            url: "/ajax-changepu/",
                            data: {i:id,ix:idx,img:1},
                            dataType: "json",
                            success: function (rp) {
                                if (rp.RP) {
                                    sic[rp.DATA.id]=rp.DATA.sic;
                                    g.remove();
                                    if(p.children().length==0){
                                        p.remove()
                                    }
                                } else {
                                    g.css('opacity','1');
                                }
                            },
                            error: function () {
                                g.css('opacity','1');
                            }
                        })
                    }).hover(function(){
                        $(this).prev().addClass("on")
                    },function(){
                        $(this).prev().removeClass("on")
                    });
                    }
                }else{
                    e.innerHTML=sic[id];
                }
                atm[i]=1;
                aCnt++;
            }
        }
    });
    if(liCnt==aCnt){
        $(window).unbind('scroll',trePic);
        $(window).unbind('scroll',trePic);
    }
}
function trePic(){    
    if(ptm){
        clearTimeout(ptm);
        ptm=null;
    }
    ptm=setTimeout('rePic()',100);
}
$(window).bind('scroll',trePic);
$(window).bind('resize',trePic);
trePic();


_s('rejForm',null);
_s('banForm',null);
_s('suspForm',null);
function help(e){
    if(confirm("هل تود فعلاً تحويل هذا الاعلان للمراجعة؟")){
        var d=mask(e);
        var i=e.parentNode.parentNode.id;
        $.ajax({
            type:"POST",
            url:"/ajax-help/",
            data:{i:i},
            dataType:"json",
            success:function(rp){
                if (rp.RP) {
                    d.removeClass("load");
                    d.html('Referring To Super Admin');
                }else {
                    d.remove()
                }
            },
            error:function(){
                d.remove()
            }
        });
    }
}
function app(e,rtp){
    var d=mask(e);
    var i=e.parentNode.parentNode.id;
    var dt = {i:i};
    if(typeof rtp !== 'undefined'){
        dt['rtp'] = rtp;
    }
    $.ajax({
        type:"POST",
        url:"/ajax-approve/",
        data:dt,
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html('Approved');
            }else {
                d.remove()
            }
        },
        error:function(){
            d.remove()
        }
    })
}
function rtp(e){
    Dialog.show('rtp_dialog',null, function(){
        app(e,2);
    });
    var dg = $('#rtp_dialog');
    var ap = $('.approve', dg);
    ap.click(function(x){
        app(e,1);
        Dialog.hide();
    });
}
function rejF(e,usr){
    var di=e.parentNode.parentNode;
    var id=di.id;
    if (!rejForm) rejForm=$("#rejForm");
    var r=rejForm;
    var c=r.children();
    
    var sel=$(c[0]);
    var s=sel.children();
    if(s.length){
        s.each(function(i){$(this).remove()});
    }
    var cn='';
    var et = $(di.childNodes[2]);
    if(et.hasClass('pimgs')){
        et = $(di.childNodes[3]);
    }
    if(typeof et.attr("lang")!=='undefined'){
        cn=et.attr('lang');
    }else{
        cn=et[0].className;
    }
    if(cn=='en'||cn=='ar'){
        sel[0].className=cn;
        var os=rtMsgs[cn];
        var len=os.length;
        var g=null;
        for(var i=0;i<len;i++){
            if(os[i].substr(0,6)=='group='){
                g=$('<optgroup label="'+os[i].substr(6)+'"></optgroup>');
                sel.append(g);
            }else{
                var o=$('<option class="ww" value="'+i+'">'+os[i]+'</option>');
                if(g!=null){
                    g.append(o);
                }else{
                    sel.append(o);
                }
            }
        }
        if(g!=null){
            sel.append(g);
        }
    }
    
    c[1].value="";
    var p=$(e.parentNode);
    p.after(r);
    r.css('display','block');
    p.parent('li').addClass('activeForm');
    
    r.removeClass("hid");
    c[2].onclick=function(){
        arej(id,e,c[1])
    };
    /*c[4].onclick=function(){
        arej(id,e,c[1],usr)
    };*/
    c[3].onclick=function(){
        crej(e)
    }
}
function psrej(e){
    var cn=e.className;
    if(cn=='ar'||cn=='en'){
        var v=parseInt(e.value);
        var t=$(e).next();
        if(v){
            t[0].value=rtMsgs[cn][v];
            idir(t[0]);
        }else {
            t[0].value='';
        }
    }
}
    
function crej(e){
    var r=rejForm;
    var p=r.prev();
    r.css('display','none');
    p.parent('li').removeClass('activeForm');
    
}
function srej(e){
    var p=$(e.parentNode);
    var n=p.next();
    if(n && n.attr('id')=='rejForm'){
        p.css('display','none');
        p.parent('li').removeClass('activeForm');
    }
}
function arej(i,e,ta,usr){
    if(!usr)usr=0;
    crej(e);
    var d=mask(e);
    $.ajax({
        type:"POST",
        url:"/ajax-reject/",
        data:{i:i,msg:ta.value,w:usr},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html('Rejected');
            }else {
                d.remove();
                srej(e);
            }
        },
        error:function(){
            d.remove();
            srej(e);
        }
    })
}

function suspF(e,usr){
    var init=0;
    if (!suspForm) {
        suspForm=$("#suspForm");
        init=1;
    }
    var r=suspForm;
    var c=r.children();
    var p=$(e.parentNode);
    p.after(r);
    r.css('display','block');
    p.parent('li').addClass('activeForm');
    if(init){
        var ta=$(c[0]),o;
        o=$('<option value="1">'+(lang=='ar'?'ساعة':'1 hour')+'</option>');
        ta.append(o);
        for (var i=6;i<=72;i=i+6){
            if(i > 48 && SU==0){
                break;
            }
            o=$('<option value="'+i+'">'+i+' '+(lang=='ar'?'ساعة':'hours')+'</option>');
            ta.append(o);
        }
    }
    r.removeClass("hid");
    c[2].onclick=function(){
        suspA(e,c[0],usr,$(c[1]).val())
    };    
    c[3].onclick=function(){
        suspC(e)
    }
}
function suspA(e,ta,usr,reason){
    suspC(e);
    var d=mask(e);
    $.ajax({
        type:"POST",
        url:"/ajax-ususpend/",
        data:{i:usr,v:ta.value,m:reason?reason : ''},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.remove();
                var ei=$(e.parentNode.parentNode);
                if(lang=='ar'){
                    t='تمت تعليق الحساب لمدة '+ta.value+' ساعة';
                }else{
                    t='User Account Suspended for '+ta.value+' hours';
                }
                var dc=$('<div class="nb nbg"><span class="done"></span>'+t+'</div>');
                $('.nb',ei).remove();
                ei.prepend(dc);
            }else {
                d.remove();
            }
        },
        error:function(){
            d.remove();
        }
    });
}
function suspC(e){
    var r=suspForm;
    var p=r.prev();    
    r.css('display','none');
    p.parent('li').removeClass('activeForm');
}

function banF(e,usr){
    if (!banForm) banForm=$("#banForm");
    var r=banForm;
    var c=r.children();
    c[0].value="";
    var p=$(e.parentNode);
    p.after(r);
    r.css('display','block');
    p.parent('li').addClass('activeForm');
    
    r.removeClass("hid");
    c[1].onclick=function(){
        aban(e,c[0],usr)
    };
    c[2].onclick=function(){
        cban(e)
    }
}
    
function cban(e){
    var r=banForm;
    var p=r.prev();
    r.css('display','none');
    p.parent('li').removeClass('activeForm');
    
}
function sban(e){
    var p=$(e.parentNode);
    var n=p.next();
    if(n && n.attr('id')=='banForm'){
        p.css('display','none');
        p.parent('li').removeClass('activeForm');
    }
}
function aban(e,ta,usr){
    cban(e);
    var d=mask(e);
    $.ajax({
        type:"POST",
        url:"/ajax-ublock/",
        data:{i:usr,msg:ta.value},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html('User Account Blocked');
            }else {
                d.remove();
                sban(e);
            }
        },
        error:function(){
            d.remove();
            sban(e);
        }
    });
}


var isAc=document.location.search.match('archive')!==null?1:0;
if(uhc){
    var HSLD=0;
    (function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src=uhc+'/min.js';
        sh.onload=sh.onreadystatechange=function(){
        if (!HSLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){
            HSLD=1;  
       $.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                u:uuid?uuid:UID,
                x:isAc
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    if(!isAc){
                        if(rp.DATA.d){
                            var x =$('#statDv');
                            var gS={
                                chart: {
                                    spacingRight:0,
                                    spacingLeft:0
                                },
                                title: {
                                    text: rp.DATA.t+(lang=='ar'?' إجمالي مشاهدات إعلاناتي':' overall impressions'),
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
                            var series;
                            if(typeof(rp.DATA.k) === 'undefined'){
                                series = [{
                                    type: 'line',
                                    name: 'Impressions',
                                    pointInterval:24 * 3600 * 1000,
                                    pointStart: rp.DATA.d,
                                    data: rp.DATA.c
                                }];
                            }else{
                                series = [{
                                    type: 'line',
                                    name: 'Impressions',
                                    pointInterval:24 * 3600 * 1000,
                                    pointStart: rp.DATA.d,
                                    data: rp.DATA.c
                                },
                                {
                                    type: 'line',
                                    name: 'Interactions',
                                    pointInterval:24 * 3600 * 1000,
                                    pointStart: rp.DATA.d,
                                    data: rp.DATA.k
                                }
                                ];
                            }
                            gS['series']=series;
                            x.highcharts(gS);
                            x.after('<div class="sopt"><span class="bt"><span class="rj ren"></span></span></div>');
                            $(':first',x.next()).click(function(e){
                                var b=$(this);
                                    b.css('display','none');
                                    x.parent().addClass('load');
                                    $.ajax({
                                        type:'POST',
                                         url:'/ajax-ga/',
                                         data:{
                                             u:uuid?uuid:UID,
                                             x:isAc
                                         },
                                         dataType:'json',
                                         success:function(bp){
                                             x.parent().removeClass('load');
                                             b.css('display','block');
                                             if(bp.RP){
                                                 if(!isAc){
                                                     if(bp.DATA.d){
                                                         gS.title.text=bp.DATA.t+(lang=='ar'?' إجمالي مشاهدات إعلاناتي':' overall impressions');
                                                         if(typeof(bp.DATA.k) === 'undefined'){
                                                            gS.series=[{
                                                               type: 'line',
                                                               name: 'Impressions',
                                                               pointInterval:24 * 3600 * 1000,
                                                               pointStart: bp.DATA.d,
                                                               data: bp.DATA.c
                                                           }];
                                                         }else{
                                                            gS.series=[{
                                                               type: 'line',
                                                               name: 'Impressions',
                                                               pointInterval:24 * 3600 * 1000,
                                                               pointStart: bp.DATA.d,
                                                               data: bp.DATA.c
                                                           },{
                                                               type: 'line',
                                                               name: 'Interactions',
                                                               pointInterval:24 * 3600 * 1000,
                                                               pointStart: bp.DATA.d,
                                                               data: bp.DATA.k
                                                           }];                                                             
                                                         }
                                                        x.highcharts(gS);
                                                     }
                                                 }
                                             }
                                         },error:function(bp){
                                             x.parent().removeClass('load');
                                             b.css('display','block');
                                         }
                                    });
                            });
                        }else{
                            var x=$('#statDv');
                            x.removeClass('load');
                            x.addClass('hxf');
                            x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                            trePic();
                        }
                    }
                    li.each(function(i,e){
                        var o=$("div:last",e);
                        var s=$('span:last',o);
                        s.removeClass('load');
                        if(typeof rp.DATA.a[e.id] !== 'undefined'){
                            s.html('<span class="rj stat"></span>'+rp.DATA.a[e.id]+(lang=='ar'?' مشاهدة':' imp'));
                            s.addClass('lnk');
                            s.click(function(i){
                                var c=$(this);
                                var std=$('.statDiv',e);
                                if(std.length){
                                    if(std.hasClass('hid')){
                                        s.addClass('on');
                                        std.removeClass('hid');
                                    }else{
                                        std.addClass('hid');
                                        s.removeClass('on');
                                    }
                                }else{
                                    var d=$('<div class="statDiv load"><div class="hld"></div></div>');
                                    o.before(d);
                                    s.addClass('on');
                                    var ix=e.id;
                                    $.ajax({
                                        type:'POST',
                                         url:'/ajax-ga/',
                                         data:{
                                             u:UID,
                                             a:e.id
                                         },
                                         dataType:'json',
                                         success:function(sp){
                                             if(sp.RP){
                                                 if(sp.DATA.d){
                                                     var gSA={
                                                        chart: {
                                                            spacingRight:0,
                                                            spacingLeft:0
                                                        },
                                                        title: {
                                                            text: sp.DATA.t+(lang=='ar'?' مشاهدة لهذا الإعلان':' impressions for this ad'),
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
                                                    var seriesA;
                                                    if(typeof(sp.DATA.k) === 'undefined'){
                                                        seriesA = [{
                                                            type: 'line',
                                                            name: 'Impressions',
                                                            pointInterval:24 * 3600 * 1000,
                                                            pointStart: sp.DATA.d,
                                                            data: sp.DATA.c
                                                        }]
                                                    }else{
                                                        seriesA = [{
                                                            type: 'line',
                                                            name: 'Impressions',
                                                            pointInterval:24 * 3600 * 1000,
                                                            pointStart: sp.DATA.d,
                                                            data: sp.DATA.c
                                                        },{
                                                            type: 'line',
                                                            name: 'Interactions',
                                                            pointInterval:24 * 3600 * 1000,
                                                            pointStart: sp.DATA.d,
                                                            data: sp.DATA.k
                                                        }];
                                                    }
                                                    gSA['series']=seriesA;
                                                    var x=$(':first',d);
                                                    x.highcharts(gSA);
                                                    if(!isAc){
                                                    x.after('<div class="sopt"><span class="bt"><span class="rj ren"></span></span></div>');
                                                    $(':first',x.next()).click(function(e){
                                                        var b=$(this);
                                                            b.css('display','none');
                                                            x.parent().addClass('load');
                                                            $.ajax({
                                                                type:'POST',
                                                                 url:'/ajax-ga/',
                                                                 data:{
                                                                    u:UID,
                                                                    a:ix
                                                                 },
                                                                 dataType:'json',
                                                                 success:function(bp){
                                                                     x.parent().removeClass('load');
                                                                     b.css('display','block');
                                                                     if(bp.RP){
                                                                             if(bp.DATA.d){
                                                                                 gSA.title.text=bp.DATA.t+(lang=='ar'?' مشاهدة لهذا الإعلان':' impressions for this ad');
                                                                                if(typeof(bp.DATA.k) === 'undefined'){                                                                                    
                                                                                    gSA.series=[{
                                                                                        type: 'line',
                                                                                        name: 'Impressions',
                                                                                        pointInterval:24 * 3600 * 1000,
                                                                                        pointStart: bp.DATA.d,
                                                                                        data: bp.DATA.c
                                                                                    }];
                                                                                }else{
                                                                                    gSA.series=[{
                                                                                        type: 'line',
                                                                                        name: 'Impressions',
                                                                                        pointInterval:24 * 3600 * 1000,
                                                                                        pointStart: bp.DATA.d,
                                                                                        data: bp.DATA.c
                                                                                    },
                                                                                    {
                                                                                        type: 'line',
                                                                                        name: 'Interactions',
                                                                                        pointInterval:24 * 3600 * 1000,
                                                                                        pointStart: bp.DATA.d,
                                                                                        data: bp.DATA.k
                                                                                    }];                                                                                    
                                                                                } 
                                                                                x.highcharts(gSA);
                                                                             }                                                                         
                                                                     }
                                                                 },error:function(bp){
                                                                     x.parent().removeClass('load');
                                                                     b.css('display','block');
                                                                 }
                                                            });
                                                    });
                                                    }
                                                 }else{
                                                    var x=d;
                                                    x.removeClass('load');
                                                    x.addClass('hxf');
                                                    x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                                                 }
                                             }else{
                                                var x=d;
                                                x.removeClass('load');
                                                x.addClass('hxf');
                                                x.html(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات إعلانك':'Mourjan system failed to load your ad statistics');
                                             }
                                         }
                                     });
                                }
                            });
                        }else{
                            s.html('<span class="rj stat"></span> NA');
                        }
                    });
                }else{
                    var x=$('#statDv');
                    x.removeClass('load');
                    x.addClass('hxf');
                    x.html(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات حسابك':'Mourjan system failed to load your statistics');
                    trePic();
                    li.each(function(i,e){
                        var o=$("div:last",e);
                        var s=$('span:last',o);
                        s.remove();
                    });
                }
            }
       });
            
            
            
            
            
            
    }};head.insertBefore(sh,head.firstChild)})();
}else if(ustats){
    $.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                u: (typeof uuid!=='undefined' && uuid)?uuid:UID,
                x:isAc
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    li.each(function(i,e){
                        var o=$("div:last",e);
                        var s=$('span:last',o);
                        s.removeClass('load');
                        if(typeof rp.DATA.a[e.id] !== 'undefined'){
                            s.html('<span class="rj stat"></span>'+rp.DATA.a[e.id]+(lang=='ar'?' مشاهدة':' imp'));
                        }else{
                            s.html('<span class="rj stat"></span> NA');
                        }
                    });
                }else{
                    li.each(function(i,e){
                        var o=$("div:last",e);
                        var s=$('span:last',o);
                        s.remove();
                    });
                }
            }
       });
}
function fsub(e){
    if(WSO && PEND && ULV==9){
        wio.emit("sess_lock",UIDK);
    }
    e.parentNode.submit();
}
function se(e){
    var v=e || window.event;
    if(v){
        if(v.preventDefault)
            v.preventDefault();
        if(v.stopPropagation){
            v.stopPropagation();
        }else {
            v.cancelBubble = true;
        }
    }
    return false;
}
if(PEND){
    var curLink=null;
    li.each(function(i,e){
        var o=$("div:last",e);
        var a=$('a:last',o);
        if(a && a.length){
            var p=$("p,.ocl",e);
            p.mousedown(function(k){
                switch (k.which) {
                    case 1:
                        curLink=a;
                        break;
                    case 3:
                        se(k);
                        var t = "";
                        if (window.getSelection) {
                            t = window.getSelection().toString();
                        } else if (document.selection && document.selection.type != "Control") {
                            t = document.selection.createRange().text;
                        }
                        if(t){
                            t=$.trim(t);
                            if(t.match(/ /)){
                                t='"'+t+'"';
                            }
                            var u=curLink.attr('href')+encodeURIComponent(' '+t);
                            var nw=window.open(u,'blank');
                            if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                                nw.close();
                                nw=window.open(u,'blank');
                            }else{
                                if (window.focus) {nw.focus()}
                                if (!nw.closed) {nw.focus()}
                            }
                            return false;
                        }
                        break;
                    default:
                        break;
                }
            });
        }
    });

    if(WSO){
        var active_admins=0,hasTouch=0,ownad=0,newad=0,newd,title_tag,adSound;
        var options = {transports: ['websocket'], 'force new connection': false};

        var wio = io.connect("ws.mourjan.com:1313", options);
        wio.on('admins',function(data){
            active_admins=data.a;
            if(isNaN(active_admins)){
                var dm=$('#adminList ul');
                var dn=$('li',dm);
                dn.each(function(){ 
                   var c=$(this).children();
                   if(c.length == 2){
                       $(c[1]).remove();
                   }
                });
                for(var i=0,l=active_admins.length;i<l;i++){
                    var adminNode = $('.'+active_admins[i],dm);
                    var span=$('.rj1', adminNode);
                    if(span.length == 0){
                        adminNode.append('<span class="rj rj1"></span>');
                    }
                }
                console.log('on<admins>: Active Admins:'+active_admins.length);
            }else{
                console.log('on<admins>: Active Admins:'+active_admins);
            }
            
            if(typeof data.b !== 'undefined'){
                var p=li.parent();
                var cn,g;
                for (var i in data.b){
                    g=$('#'+data.b[i],p);
                    if(i==UIDK) {
                        cn='owned';
                        ownad=data.b[i];
                    }
                    else {
                        cn='used';
                    }
                    g.addClass(cn);
                    if(i!=UIDK){
                        var n=data.c[i]?data.c[i]:'Anonymous '+i;
                        setOwner(n,g);
                    }
                }
            }
        });


        wio.on('ad_touch',function(dt){   
            var p=li.parent(),g;
            if(dt.o){
                g=$('#'+dt.o,p);
                g.removeClass('used');
                setOwner(0,g);
            }
            g=$('#'+dt.i,p);
            if(dt.a){      
                if(dt.x==UIDK){
                    g.addClass('owned');
                    setOwner(dt.n,g);
                }else{                   
                    g.addClass('used');
                    setOwner(dt.n,g);
                }
            }else{
                g.removeClass('used');
                setOwner(0,g);
            }                
        });
        
        function setOwner(n,e){            
            var c=$('.cct',e);
            var o=$('.owner',c);
            if(n){
                if(o.length){
                    o.html('<b>'+n+'</b>');
                }else{
                    c.append($('<span style="background-color:#ff9000;color:#fff;padding:0 5px;line-height:30px;position:static" class="fl owner"><b>'+n+'</b></span>'));
                }
            }else{
                if(o.length){
                    o.remove();
                }
            }
        }
        
        wio.on('superAdmin',function(data){console.log(data);
            if(typeof data !== 'undefined'){
                var li=$('#'+data.id);
                if(li.length){
                    var d=mask(li[0],1);
                    d.html('Sent To Super Admin');
                }
            }
        });
        
        wio.on('editorialUpdate',function(data){
            if(typeof data !== 'undefined'){
                var li=$('#'+data.id);
                if(li.length){
                    li.attr('ro',data.ro);
                    li.attr('se',data.se);
                    li.attr('pu',data.pu);
                    $('.cct',li).html('<span class="done"></span>'+data.label);
                }
            }
        });
        
        wio.on('editorialImg',function(data){
            if(typeof data !== 'undefined'){
                var li=$('#'+data.id);
                if(li.length && data.sic !=sic[data.id]){
                    sic[data.id]=data.sic;
                    var p=$('p.pimgs',li);
                    var dx=parseInt(data.dx);
                    $(p[0].childNodes[dx]).remove();
                    if(p.children().length==0){
                        p.remove()
                    }
                }
            }
        });
        
        wio.on('editorialText',function(data){
            if(typeof data !== 'undefined'){
                var li=$('#'+data.id),
                idx=2,
                dt=data;  
                if(li.length){
                    if(li[0].childNodes[idx].className=="pimgs"){
                        idx++;
                    }
                    var p=$(li[0].childNodes[idx]);
                    var sp=p[0].childNodes[0];
                    if(dt.rtl=="1"){
                        p.addClass("ar");
                        p.removeClass("en");
                    }else{                                                    
                        p.addClass("en");
                        p.removeClass("ar");
                    }
                    var tx=dt.t;
                    if(dt.dx=="1"){
                        tx="<span class=\'done\'></span>"+tx;
                    }
                    p.html(tx);
                    p.prepend(sp);

                    idx++;
                    p=$(li[0].childNodes[idx]);
                    if(p[0].tagName=="P"){
                        if(dt.t2.length){
                            if(dt.rtl2=="1"){
                                p.addClass("ar");
                                p.removeClass("en");
                            }else{                                                    
                                p.addClass("en");
                                p.removeClass("ar");
                            }
                            tx=dt.t2;
                            if(dt.dx=="2"){
                                tx="<span class=\'done\'></span>"+tx;
                            }
                            p.html(tx);
                            p.prepend($(sp).clone());
                        }else{
                            p.remove();
                        }
                    }else{
                        if(dt.t2.length){
                            p=$("<p></p>");
                            if(dt.rtl2=="1"){
                                p.addClass("ar");
                                p.removeClass("en");
                            }else{                                                    
                                p.addClass("en");
                                p.removeClass("ar");
                            }
                            tx=dt.t2;
                            if(dt.dx=="2"){
                                tx="<span class=\'done\'></span>"+tx;
                            }
                            p.html(tx);
                            p.prepend($(sp).clone());
                            p.insertAfter(li[0].childNodes[idx-1]);
                        }
                    }
                }
            }
        });

        wio.on("ads", function(data) {
            if(typeof data.c !== 'undefined'){        
                data.c=parseInt(data.c);
                var id=data.id;
                var p=li.parent();
                var ei=$('#'+id,p);
                var o=parseInt(ei.attr('status'));
                if(isNaN(o) || o>0 || data.c==-1){
                    var m,t,d;
                    $('.od',ei).remove();
                    if(typeof ei[0] !== 'undefined'){
                        switch(data.c){
                            case 6:
                            case -1:
                                ei.attr('status',-1);
                                m=mask(ei[0],1);
                                m.removeClass('load');
                                if(lang=='ar'){
                                    t='تم الحذف';
                                }else{
                                    t='Deleted';
                                }                        
                                m.html(t);
                                break;
                            case 0:
                                ei.attr('status',0);
                                ei.removeClass('approved');
                                m=mask(ei[0],1);
                                m.removeClass('load');
                                if(lang=='ar'){
                                    t='جاري التعديل، يجب تحديث الصفحة';
                                }else{
                                    t='editting in progress, refresh page';
                                }                        
                                m.html(t);
                                break;
                            case 1:
                                if(lang=='ar'){
                                    t='بإنتظار موافقة النشر من قبل محرري الموقع';
                                }else{
                                    t='Waiting for Editorial approval';
                                }
                                d=$('<div class="nb nbw"><span class="wait"></span>'+t+'</div>');
                                $('.nb',ei).remove();
                                ei.prepend(d);
                                ei.attr('status',1);
                                break;
                            case 2:
                                if(lang=='ar'){
                                    t='تمت الموافقة وبإنتظار العرض من قبل محرك مرجان';
                                }else{
                                    t='Approved and pending Mourjan system processing';
                                }
                                d=$('<div class="nb nbg"><span class="done"></span>'+t+'</div>');
                                $('.nb',ei).remove();
                                ei.prepend(d);
                                ei.addClass('approved');
                                ei.attr('status',2);
                                break;
                            case 3:                        
                                if(lang=='ar'){
                                    t='تم رفض عرض هذا الإعلان';
                                }else{
                                    t='Rejected By Admin';
                                } 
                                if(typeof data.m !== 'undefined'){
                                    t+=': '+data.m;
                                }
                                d=$('<div class="nb nbr"><span class="fail"></span>'+t+'</div>');
                                $('.nb',ei).remove();
                                ei.prepend(d);
                                ei.addClass('approved');
                                ei.attr('status',3);
                                break;
                            case 7:
                                m=mask(ei[0],1);
                                ei.attr('status',7);
                                m.removeClass('load');
                                var lnk;
                                if(ULV==9){
                                    lnk=$('.oct',ei).children().get(1).href+'#'+id;
                                }else{
                                    lnk='/myads/'+(lang=='ar'?'':'en/')+'#'+id;
                                }
                                if(lang=='ar'){
                                    t='الإعلان أصبح فعالاً، <a href="'+lnk+'">انقر(ي) هنا</a> لتفقد الإعلانات الفعالة';
                                }else{
                                    t='Ad is online now, <a href="'+lnk+'">click here</a> to view Active Ads';
                                }                        
                                m.html(t);
                                break;                 
                        }
                    }
                    if(data.c==1){
                        newad++;
                        if(!newd){
                            newd=$('<div class="rct adminNB">'+newad+' new ad'+(newad>1?'s':'')+'</div>');
                            newd.click(function(){document.location=''});
                            $('p.ph').append(newd);
                        }else{
                            newd.html(newad+' new ad'+(newad>1?'s':''));
                        }
                        var h;
                        if(!title_tag){
                            title_tag=$('title');
                            h=title_tag.html()+' ('+newad+')';
                        }else{
                            h=title_tag.html().replace(/\(.*?\)/,'('+newad+')');
                        }
                        title_tag.html(h);
                        if(!MUTE){
                            if(!adSound)adSound = new Audio(ucss+'/s/'+SOUND);
                            adSound.play();
                        }
                    }
                }
            }
        });
        
        wio.emit("hook_myads",[UIDK,ULV]);
        
        wio.on('reconnect', function () {
            console.log('Reconnnect to ws');
            li.removeClass('owned used');
            wio.emit("hook_myads",[UIDK,ULV]);
        });
        
        var lastT=0,lockTouch=0;
        function touch(e,i){
            lockTouch=1;
            lastT=new Date().getTime();
            if(ownad!=this.id){
                var d=$(this);
                if(!d.hasClass('used')){
                    wio.emit("touch",[this.id,UIDK]);        
                    if(ownad){
                        $('#'+ownad,li.parent()).removeClass('owned');
                    }
                    ownad=this.id;
                    d.addClass('owned');
                }
            }
        }

        li.bind('touchstart click',touch);
        li.bind('contextmenu', function(e) {
            e.preventDefault();
        });
        
        $(document).bind('click',function(e){
            if(e.which==1 && !lockTouch && ownad){
                var a=$('#banForm'),b=$('#suspForm'),c=$('#rejForm'),d=$('#textForm');
                if(a.css('display')!='block' && b.css('display')!='block' && c.css('display')!='block' && d.css('display')!='block'){
                    wio.emit("release",[ownad,UIDK]); 
                    $('#'+ownad).removeClass('owned');
                    ownad=0;
                }
            }
            lockTouch=0;
        });
        
        wio.on('ad_release',function(dt){ 
            if(dt.i){
                var g=$('#'+dt.i);
                g.removeClass('used');
                setOwner(0,g);
            }                
        });
        
        setInterval(function(){
            if(lastT && ownad){
                var t=new Date().getTime();
                if(t-lastT>180000){
                    wio.emit("release",[ownad,UIDK]);
                    $('#'+ownad).removeClass('owned');
                    ownad=0;
                }
            }
        },60000);
    }
    
}

function askPremium(e){
    var sp = $('#spinner').SelectNumber();
    Dialog.show("make_premium",null,function(){confirmPremium(sp,e)});
}
function confirmPremium(sp,e){
    var str='',x='',y='';
    var v = sp.val();
    if(lang=='ar'){
        str='تمييز الاعلان لمدة ';
        if(v==1){
            x='ذهبية واحدة';
            y='يوم واحد';
        }else if(v == 2){            
            x='ذهبيتين';
            y='يومين';
        }else if(v < 11){
            x=v+' ذهبيات';
            y=v+' ايام';            
        }else{
            x=v+' ذهبية';
            y=v+'يوم ';
        }
        str+=y;
        str+=' لقاء ';
        str+=x+'؟';
    }else{
        str='Activate premium listing for ';
        if(v==1){
            x='1 Gold';
            y='1 Day';
        }else{
            x=v+' Golds';
            y=v+' Days';
        }
        str+=y;
        str+=' for the value of ';
        str+=x+'?';
    }
    Dialog.show("confirm_premium",str,function(){
        makePremium(v,e);
    },function(){
        askPremium(e);
    });
}
function makePremium(c,e){
    var d=mask(e);    
    var i=e.parentNode.parentNode.id;
    var div = $(e.parentNode.parentNode);
    $.ajax({
        type:"POST",
        url:"/ajax-mpre/",
        data:{i:i,c:c,u:UIDK},
        dataType:"json",
        success:function(rp){
            d.remove();
            if (rp.RP) {
                if(div.hasClass('vp')){                    
                    Dialog.show('alert_dialog','<span class="done"></span>'+(lang=='ar'?'تم تجديد فترة تمييز الاعلان بنجاح':'Premium listing of this ad has been renewed successfully'));
                }else{
                    Dialog.show('alert_dialog','<span class="done"></span>'+(lang=='ar'?'سيتم تمييز هذا الإعلان خلال لحظات':'This ad will be premium in few moments'));                    
                }
                div.addClass('vp');
                var w=$(e);
                w[0].onclick=function(){
                    cancelPremium(e);
                };
                w.html('<span class="mc24"></span>'+(lang=='ar'?'ايقاف التمييز':'stop premium'));
            }else{
                errDialog();
            }
        },
        error:function(){
            d.remove();
            errDialog();
        }
    });
};
function cancelPremium(e){
    Dialog.show("stop_premium",null,function(){cancelPremiumOK(e)});
}
function cancelPremiumOK(e){
    var d=mask(e);    
    var i=e.parentNode.parentNode.id;
    var div = $(e.parentNode.parentNode);
    $.ajax({
        type:"POST",
        url:"/ajax-spre/",
        data:{i:i,u:UIDK},
        dataType:"json",
        success:function(rp){
            d.remove();
            if (rp.RP){
                var msg='<span class="done"></span>'+(lang=='ar'?'تم الغاء تمييز الاعلان بنجاح':'Premium listing of this ad has been cancelled successfully');
                if(isDefined(rp.DATA.end) && rp.DATA.end!=''){
                    msg+= (lang=='ar'?'<br />ولكن الاعلان سيبقى مميز للفترة المكتسبة وهي':'<br />but the ad will remain premium for the earned period of');
                    msg+=':<br />'+rp.DATA.end;
                }else{                    
                    div.removeClass('vp');
                }
                Dialog.show('alert_dialog',msg);
                
                var w=$(e);
                w[0].onclick=function(){
                    askPremium(e);
                };
                w.html('<span class="mc24"></span>'+(lang=='ar'?'تمييز الاعلان':'make premium'));
            }else{
                errDialog();
            }
        },
        error:function(){
            d.remove();
            errDialog();
        }
    });
}
function noPremium(){
    Dialog.show("what_premium");
};

function ahld(e){
    Dialog.show("stop_ad",null,function(){
        ahldOK(e);
    });        
}
function ahldOK(e){
    var d=mask(e);    
    var i=e.parentNode.parentNode.id;
    $.ajax({
        type:"POST",
        url:"/ajax-ahold/",
        data:{i:i},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html(lang=='ar'?'سيتم وقف عرض هذا الإعلان خلال لحظات':'Display of this ad will stop in few moments');
            }else {
                d.remove();
                errDialog();
            }
        },
        error:function(){
            d.remove();
            errDialog();
        }
    });
}
function adel(e,h){
    Dialog.show("delete_ad",null,function(){
        adelOK(e,h);
    });   
}
function adelOK(e,h){
    var i=e.parentNode.parentNode.id;
    if(!h)h=0;
    var d=mask(e);
    $.ajax({
        type:"POST",
        url:"/ajax-adel/",
        data:{i:i,h:h},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html(lang=='ar'?'تم الحذف':'Deleted');
            }else {
                d.remove();
                errDialog();
            }
        },
        error:function(){
            d.remove();
            errDialog();
        }
    })
}
var load_sections=1,SECTIONS,ROOTS;
if(STO){
    if(typeof(sessionStorage['SECTIONS'+lang])!=="undefined"){
        eval(sessionStorage['SECTIONS'+lang]);
        load_sections=0;
    }
}
var loadSections=function(){
    $.ajax({
        type:'GET',
        url:'/ajax-menu/',
        data:{
            sections:lang
        },
        dataType:'html',
        success:function(d){
            if(d.length){ 
                try{
                    sessionStorage.setItem('SECTIONS'+lang, d);
                    eval(d);
                }catch(ex){}
            }
        }
    });
};
if(load_sections){
    loadSections();
}
var renderPU=function(){
    $('.b_pu',AZONE).remove();
    if(ARO!=4){
        for (var i in ROPU[ARO]) {
            var p = ROPU[ARO][i];
            if (p[0] != APU) {
                var a = $("<div class=\'btrk bt b_pu\' pu=\'" + p[0] + "\' aid=\'" + AID + "\'>" + p[1] + "</div>");
                a.click(function (ck) {
                    ck.preventDefault();
                    ck.stopPropagation();
                    var b = $(this);
                    preClick(AE, b);
                    changePu(AE, b.attr("aid"), ARO, ASE, b.attr("pu"));
                });
                AZONE.prepend(a);
            }else{
                var a = $("<div class=\'btrk bt on b_pu\'>" + p[1] + "</div>");
                AZONE.prepend(a);
            }
        }
    }
};
var TON;
var renderSecs=function(ro,e){   
    e.empty();
    $('#fiput',TON).val("");
    ARO = ro;
    $('.b_ro',AZONE).remove();
    
    var tmp=$('.on',TON);
    tmp.removeClass('on');
    tmp=$('#r'+ro,TON);
    tmp.addClass('on');
    
    var ul=[$('<ul></ul>'),$('<ul></ul>'),$('<ul></ul>'),$('<ul></ul>')];
    var j=0;
    var cs= (ro==1?'x x':(ro==2?'z z':(ro==3?'v v':(ro==4?'y y':(ro==99?'u u':'')))));   
    var secs=[],k=0;
    for(var i in SECTIONS){
        if(ro==SECTIONS[i][2]){
            secs[k++]=SECTIONS[i];
        }
    }
    k=0;
    var splitter = Math.ceil(secs.length / 4);
    for(var i in secs){
        var r=$("<li id='s"+secs[i][0]+"'"+(ASE==secs[i][0]?' class=\'on\'':'')+"><span><span class='"+cs+secs[i][0]+"'></span>"+secs[i][1]+"</span></li>");
        r.click(function(ck){
            ck.preventDefault();
            ck.stopPropagation();
            $('.on',e).removeClass('on');
            var sid=$(this).attr('id').substring(1);
            if($('.b_pu.on',AZONE).length || ro==4){
                if(ro==4){
                    APU=5;
                }
                var b = $(this);
                preClick(AE, b);
                changePu(AE, AID, ARO, sid, APU);
            }else{
                ASE=sid;
                $(this).addClass('on');
                renderPU();
                $('.b_pu',AZONE).css('background-color','red');
            }
        });
        ul[j].append(r);
        k++;
        if(k%splitter===0)j++;
    }
    e.append(ul[0]);
    e.append(ul[1]);
    e.append(ul[2]);    
    e.append(ul[3]);    
    
    renderPU();
    
    for (var i in ROTN) {
        if (i == ro) {
            var mxn = ROTN[i];
            for (var j in mxn) {
                var p = mxn[j];
                var a = $("<div class=\'btrk bt gold b_ro\' ro=\'" + p[0] + "\' se=\'" + p[1] + "\' pu=\'" + p[2] + "\' aid=\'" + AID + "\'>" + p[3] + "</div>");
                a.click(function (ck) {
                    ck.preventDefault();
                    ck.stopPropagation();
                    var b = $(this);
                    preClick(AE, b);
                    changePu(AE, b.attr("aid"), b.attr("ro"), b.attr("se"), b.attr("pu"));
                });
                AZONE.append(a);
            }
        }
    }
};
var AID=0,ARO=0,ASE=0,APU=0,AE,ALI,AZONE,ADSK;
var quickSwitch = function (e) {
    AE=e;
    var li = $(e.parentNode);
    ALI=li;
    if (li.hasClass("focus")) {
        return;
    }
    var id = li.attr("id"),
            ro = li.attr("ro"),
            se = li.attr("se"),
            pu = li.attr("pu");
    AID = id;
    ARO=ro;
    ASE=se;
    APU=pu;
    
    li.addClass("focus");
    var co = li.offset();
    window.scrollTo(0,co.top);
    var bdy = $("body");
    var dsk = $("<div id=\'dialog-mask\'></div>");
    var zone = $("<div class=\'btzone\'></div>");
    AZONE=zone;
    ADSK=dsk;
    if(!TON){
        TON=$("<div class='ton "+lang+"'><div style='text-align:center;padding:5px;background-color:darkkhaki'><input id='fiput' type='text' onkeyup='idir(this);filterSections(this)' onchange='idir(this,1);' placeholder='"+(lang=='ar'?'بحث عن قسم':'filter by section')+"' style='padding:5px 10px;width:450px;font-size:20px;font-family:arial' /></div><ul><li class='roots'><ul></ul></li><li class='sections'></li></ul></div>");
        var tmp=$('.roots ul',TON);
        var secs=$('.sections',TON);
        var ph=Math.floor(100/ROOTS.length);
        for(var i in ROOTS){
            var r=$("<li id='r"+ROOTS[i][0]+"'><span>"+ROOTS[i][1]+"</span></li>");
            tmp.append(r);
            r.click(function(){
                renderSecs(this.id.substring(1),secs);
            });
        }
    }else{
        TON.show();
    }
    renderSecs(ro,$('.sections',TON));
    
    zone.css("top", co.top + "px");
    TON.css("top", co.top + "px");
    if (lang == "ar") {
        zone.css("left", (co.left - 330) + "px");
        TON.css("left", (co.left-182) + "px");
    } else {
        zone.css("left", (co.left + 836) + "px");
        TON.css("left", (co.left) + "px");
    }
    
    for (var i in SETN) {
        if (i == se) {
            var mxn = SETN[i];
            for (var j in mxn) {
                var p = mxn[j];
                var a = $("<div class=\'btrk bt gold b_se\' ro=\'" + p[0] + "\' se=\'" + p[1] + "\' pu=\'" + p[2] + "\' aid=\'" + id + "\'>" + p[3] + "</div>");
                a.click(function (ck) {
                    ck.preventDefault();
                    ck.stopPropagation();
                    var b = $(this);
                    preClick(e, b, li, zone, dsk);
                    changePu(e, b.attr("aid"), b.attr("ro"), b.attr("se"), b.attr("pu"));
                });
                zone.append(a);
            }
        }
    }
    dsk.first().click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        li.removeClass("focus");
        zone.remove();
        dsk.remove();
        TON.hide();
    });
    bdy.append(dsk);
    bdy.append(zone);
    bdy.append(TON);
};
var filterSections=function(e){
    var s=e.value;
    var secs=$('.sections',TON);
    var li=$('li',secs);
    li.each(function(i,p){
        p=$(p);
        if(p[0].innerText.match(new RegExp(s))){
            p.css("display","block");            
        }else{
            p.css("display","none");
        }
    });
};
var preClick = function (e, b) {
    ALI.removeClass("focus");
    AZONE.remove();
    ADSK.remove();
    TON.hide();
    if ($(e.childNodes[0]).hasClass("k")) {
        $(e).prepend($("<div class=\'loads load\'></div>"));
    } else {
        e.childNodes[0].className = "loads load";
    }
};
var changePu = function (e, id, ro, se, pu) {
    $.ajax({
        type: "GET",
        url: "/ajax-changepu/",
        data: {i: id, r: ro, s: se, p: pu, hl: lang},
        dataType: "json",
        success: function (rp) {
            if (rp.RP) {
                var dt = rp.DATA,
                li = $("#" + id);
                li.attr("ro", dt.ro);
                li.attr("se", dt.se);
                li.attr("pu", dt.pu);
                $(e).html("<span class=\'done\'></span>" + rp.DATA.label);
            } else {
                failChange(e);
            }
        },
        error: function () {
            failChange(e);
        }
    })
};
var failChange = function (e) {
    e.childNodes[0].className = "fail";
};

function tglSound(e,r){
    var e=$(e);
    if(MUTE){
        MUTE=0;
        e.removeClass('off');
    }else{
        MUTE=1;
        e.addClass('off');
    }
    if(typeof r ==='undefined'){
        $.ajax({
            type: "GET",
            url: "/ajax-mute/",
            data: {s:MUTE},
            dataType: "json",
            success: function (rp) {
                if (!rp.RP) {
                    tglSound(e[0],1);
                } 
            },
            error: function () {
                tglSound(e[0],1);
            }
        });
    }
}

function errDialog(){
    Dialog.show('alert_dialog','<span class="fail"></span>'+(lang=='ar'?'فشل محرك مرجان باتمام العملية<br />يرجى المحاولة مجدداً<br />او <a href="/contact/">اطلعنا بالامر</a>':'Your request has failed<br />please try again<br />or <a href="/contact/en/">Tell us about it</a>'));
};
 
var ALT = 0,MULTI=0,pext="";
$(document).keydown(function(e){
    if(e.which=="18")
        ALT=1;
    if(e.which=="90")
        MULTI=1;
});

$(document).keyup(function(){
    ALT=0;
    MULTI=0;
});

function MSAD(e){
    console.log(e);
};

function EAD(e,idx){
    if(ALT){
    var li = $(e.parentNode),
    id = li.attr("id");
    e=$(e);
    var co=e.offset();
    var bdy = $("body");
    var dsk = $("<div id=\'dialog-mask\'></div>");
    var ae=$("<textarea id=\'textForm\' class=\'tapl "+e[0].className+"\' onkeydown=\'idir(this)\' onchange=\'idir(this,1)\'></textarea>");
    ae.height(e.height()+"px");
    ae.css("top",co.top+"px");
    ae.css("left",co.left+"px");

    pext=e.html();
    pext=pext.replace(/\u200B.*$/,"");
    pext=pext.replace(/<.*?>.*?<\/.*?>/g,""); 
    var org=pext;
    ae.val(pext);

    dsk.first().click(function () {
        if(pext!=ae.val()){
            if(e[0].childNodes[1].tagName=="SPAN"){
               e[0].childNodes[1].className="loads load";
            }else{
                $("<span class=\'load loads\'></span>").insertAfter(e[0].childNodes[0]);
            }
            var c=ae.hasClass("ar")?1:0; 
            $.ajax({
                type: "POST",
                url: "/ajax-changepu/?i="+id,
                data: {t:ae.val(),dx:idx,rtl:c},
                dataType: "json",
                success: function (rp) {
                    if (rp.RP) {
                        var dt = rp.DATA;
                        var idx=2;
                        if(li[0].childNodes[idx].className=="pimgs"){
                            idx++;
                        }
                        var p=$(li[0].childNodes[idx]);
                        var sp=p[0].childNodes[0];
                        if(dt.rtl=="1"){
                            p.addClass("ar");
                            p.removeClass("en");
                        }else{                                                    
                            p.addClass("en");
                            p.removeClass("ar");
                        }
                        var tx=dt.t;
                        if(dt.dx=="1"){
                            tx="<span class=\'done\'></span>"+tx;
                        }
                        p.html(tx);
                        p.prepend(sp);

                        idx++;
                        p=$(li[0].childNodes[idx]);
                        if(p[0].tagName=="P"){
                            if(dt.t2.length){
                                if(dt.rtl2=="1"){
                                    p.addClass("ar");
                                    p.removeClass("en");
                                }else{                                                    
                                    p.addClass("en");
                                    p.removeClass("ar");
                                }
                                tx=dt.t2;
                                if(dt.dx=="2"){
                                    tx="<span class=\'done\'></span>"+tx;
                                }
                                p.html(tx);
                                p.prepend($(sp).clone());
                            }else{
                                p.remove();
                            }
                        }else{
                            if(dt.t2.length){
                                p=$("<p></p>");
                                if(dt.rtl2=="1"){
                                    p.addClass("ar");
                                    p.removeClass("en");
                                }else{                                                    
                                    p.addClass("en");
                                    p.removeClass("ar");
                                }
                                tx=dt.t2;
                                if(dt.dx=="2"){
                                    tx="<span class=\'done\'></span>"+tx;
                                }
                                p.html(tx);
                                p.prepend($(sp).clone());
                                p.insertAfter(li[0].childNodes[idx-1]);
                            }
                        }
                    } else {
                        e[0].childNodes[1].className="fail";
                    }
                },
                error: function () {
                    e[0].childNodes[1].className="fail";
                }
            })
        }
        ae.remove();
        dsk.remove();
    });
    bdy.append(dsk);
    bdy.append(ae);
    }
};

function openW(href){
    var nw=window.open(href,'blank');
    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
        nw.close();
        nw=window.open(u,'blank');
    }else{
        if (window.focus) {nw.focus()}
        if (!nw.closed) {nw.focus()}
    }
};