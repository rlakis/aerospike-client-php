window['_s']=function(a,v){
    window[a]=v;
};
function pi(){
    $.ajax({type:'POST',url:'/ajax-pi/'});
}
if(UID)setInterval(pi,300000);
function cl(u){
    if(lang!='ar')u+=lang+'/';
    document.location=u
}
function idir(e,t){
    var v=e.value;
    if(t){
        v=v.replace(/^\s+|\s+$/g, '');
        e.value=v;
    }
    if(v==''){
        e.className='';
    }else{
        var y=v.replace(/[^\u0621-\u064a\u0750-\u077f]|[:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        var z=v.replace(/[\u0621-\u064a\u0750-\u077f:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        if(y.length > z.length*0.5){
            e.className='ar';
        }else{
            e.className='en';
        }
    }
}
var cs=document.createElement("link");cs.setAttribute("rel", "stylesheet");cs.setAttribute("type", "text/css");cs.setAttribute("href", ucss+"/imgs.css");document.getElementsByTagName("head")[0].appendChild(cs);

menu=$('#menu');
mp=$(menu.children()[0]).offset();
$(window).bind('resize',function() {
    mp=$(menu.children()[0]).offset()
});
var load_menu=1;
if(STO){
    if(typeof(sessionStorage.MENU)!=="undefined"){
        sessionStorage.removeItem('MENU');
    }
    if(typeof(sessionStorage.LSM)!=="undefined" && sessionStorage.LSM==LSM){
        if(typeof(sessionStorage['MENU'+lang])!=="undefined"){
            $('body').append(sessionStorage['MENU'+lang]);
            mul=$(".mul");
            load_menu=0;
        }
    }
}
/*ITC="ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch;*/
if(load_menu){
$.ajax({
    type:'GET',
    url:'/ajax-menu/',
    data:{
        h:ICH,
        _t:LSM,
        c:hasCvs
    },
    dataType:'html',
    success:function(d){
        if(d){ 
            $('body').append(d);
            mul=$(".mul");
            if(STO){
                try{
                    sessionStorage.setItem('LSM', LSM);
                    sessionStorage.setItem('MENU'+lang, d);
                }catch(ex){}
            }
            /*if(ITC){
                
            }else{
                mul.hover(function(e){
                    if (tmr) {
                        clearTimeout(tmr);
                        func=null
                    }
                },
                function(e){
                    $(this).slideUp(200)
                });
            }*/
        }
    }
});
}
var aim=$("a[id],.c",menu);
    aim.click(function(e){
        e.preventDefault();
        e.stopPropagation();
        aim.removeClass('on');
        if(tmu){
            $("#u"+tmu).slideUp(400);
        }
        if(tmu==this.id){
            tmu=null;
        }else{
            $(this).addClass('on');
            var u=$("#u"+this.id);
            if(u.length>0 && (typeof u[0] !== 'undefined')){
                u[0].style.top=(mp.top+30)+"px";
                u[0].style.left=mp.left+"px";
                var id=this.id;
                tmu=id;
                u.slideDown(400);
            }
        }
    });     
$(document.body).click(function(e){
    if(tmu){
        $("#u"+tmu).slideUp(400);
        tmu=null;
        aim.removeClass('on');
    }
});
/*}else{
    $("a[id],.c",menu).hover(function(e){
        tmd=setTimeout('sldown("'+this.id+'")',200);
    },function(e){
        if(tmu)clearTimeout(tmu);
        if(tmd)clearTimeout(tmd);
        var id=this.id;
        func=function(){
            $("#u"+id).slideUp(400);
        };
        tmr=setTimeout("if(func)func();",300);
    });  
}
function sldown(id){
    if (tmr) {
        clearTimeout(tmr);
        tmr=null;
        func=null;
    }
    for(var i in DDM){
        DDM[i].slideUp(400);
    }
    var u=$("#u"+id);
    DDM[id]=u;
    if(u.length>0 && (typeof u[0] !== 'undefined')){
        u[0].style.top=(mp.top+30)+"px";
        u[0].style.left=mp.left+"px";
        fupc=function(){
            u.slideDown(400)
        };
        tmu=setTimeout("fupc();",100);
    }
}*/
/*(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();*/
if(jsLog)
window.onerror = function(m, url, ln) {
    if(m!='Script error.')
    $.ajax({
        url:'/ajax-js-error/',
        type:'POST',
        data:{
            e:m,
            u:url,
            ln:ln
        }
    });
};

function wo(u){if(u)document.location=u};

if( (typeof showBalance !== 'undefined') && showBalance==1){
    var bLink="/statement/";
    if(lang!="ar"){
        bLink+=lang+"/";
    }
    $.ajax({
           type:'GET',
            url:'/ajax-balance/',
            data:{
                u: (typeof uuid!=='undefined' && uuid)?uuid:UID
            },
            dataType:'json',
            success:function(rp){
                var bc = $("#balanceCounter");
                if(rp.RP){ 
                    msg="";
                    suffix="";
                    if(lang=="ar"){
                        msg = "الرصيد الحالي";
                        suffix="ذهبية";
                    }else{
                        msg = "Current Balance";
                        suffix="gold";
                    }
                    msg += ": <span class='mc24'></span>"+rp.DATA.balance+" "+suffix;
                    if(MOD=="statement")bc.html(msg);
                    else
                    bc.html("<a href='"+bLink+"'>"+msg+"</a>");
                }else{
                    msg="";
                    if(lang=="ar"){
                        msg="معلومات الرصيد غير متوفرة حالياً";
                    }else{
                        msg="Balance info is currently not available";
                    }
                    if(MOD=="statement")bc.html(msg);
                    else
                    bc.html("<a href='"+bLink+"'>"+msg+"</a>");
                }
            },
            error:function(){
                var bc = $("#balanceCounter");
                msg="";
                if(lang=="ar"){
                    msg="معلومات الرصيد غير متوفرة حالياً";
                }else{
                    msg="Balance info is currently not available";
                }
                if(MOD=="statement")bc.html(msg);
                else
                bc.html("<a href='"+bLink+"'>"+msg+"</a>");
            }
       });
}

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

function fsub(e){
    e.parentNode.submit();
}

var li=$("ul.ls li"),lip,ptm,atm={},liCnt,aCnt=0;
function rePic(){
    var h=$(window).height();
    if(!lip){
        lip=$(".ig",li);
        liCnt=lip.length;
    }
    lip.each(function(i,e){
        if(!atm[i]){
            var r = e.getBoundingClientRect();
            var k=r.top;
            if(k>=-100 && k<=h+100){
                e.innerHTML=sic[e.parentNode.parentNode.id];
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
                u: (typeof uuid!=='undefined' && uuid)?uuid:UID,
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



if(PEND && WSO){
    var wio = io.connect("ws.mourjan.com:1313", {'force new connection':false, transports: ['websocket', 'xhr-polling', 'polling', 'htmlfile', 'flashsocket']});

    wio.on("ads", function(data) {
    if(typeof data.c !== 'undefined'){        
        data.c=parseInt(data.c);
        var id=data.id;
        var p=li.parent();
        var ei=$('#'+id,p);
        var o=parseInt(ei.attr('status'));
        if(isNaN(o) || o>0){
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
            }
        }
    });
        
    wio.emit("hook_myads",[UIDK]);
    wio.on('reconnect', function () {
        wio.emit("hook_myads",[UIDK,ULV]);
    });

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
function errDialog(){
    Dialog.show('alert_dialog','<span class="fail"></span>'+(lang=='ar'?'فشل محرك مرجان باتمام العملية<br />يرجى المحاولة مجدداً<br />او <a href="/contact/">اطلعنا بالامر</a>':'Your request has failed<br />please try again<br />or <a href="/contact/en/">Tell us about it</a>'));
}