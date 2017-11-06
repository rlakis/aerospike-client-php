var eid=0;
function ado(e,i,v){
    se(v);
    eid=i;
    
    var s=$('#'+eid);
    
    var subj=(lang=='ar'?'وجدت هذا الاعلان على مرجان':'found this ad on mourjan');
    var ctx=subj+' https://www.mourjan.com/'+(lang=='ar'?'':lang+'/')+i+'/';
    var msg=encodeURIComponent(ctx+'?utm_source=whatsapp');
        
    var d=$('#ad_options');
    if(s.hasClass("vp")){
        $('#ad_cancel_pre').parent().show();
        $('#ad_make_pre').parent().hide();
    }else{        
        $('#ad_cancel_pre').parent().hide();
        $('#ad_make_pre').parent().show();
    }
    
    $('#ad_detail',d).attr("href","/"+(lang=='ar'?'':lang+'/')+i);
    
    $('#ad_wats',d).attr("href","whatsapp://send?text=" + msg);
    msg=encodeURIComponent(ctx+'?utm_source=viber');
    $('#ad_viber',d).attr("href","viber://forward?text=" + msg);
    
    Dialog.show("ad_options", null, null, function(){
        window.removeEventListener("popstate", f, true);
        history.back();
    });  
    
    var f=function(o){
        if(o.state === null || (o.state !== null && typeof o.state.adOpts === 'undefined')){
            window.removeEventListener("popstate", f, true);
            Dialog.hide();
        }
    };
    
    var stateObj = {
        adOpts: 1
    };
    history.pushState(stateObj, document.title, document.location);
    window.addEventListener("popstate", f, true);
    
    return;
    
    
    
    if(!statDiv)statDiv=$('#statAiv');
    var li=e;
    var e=$c($c(li,1),2);
    if (!leb){
        leb=$('#aopt')[0];
    }else if($p(leb)){
        var l=$p(leb);
        if (l!=li) {
            fdT(l,1,'edit');
            $(peb).removeClass('aup');
            //peb.className="adn";
            statDiv.parent().css('display','none');
            statDiv.addClass('load');
            statDiv.html('');
            acls(leb)
        }
        l.removeChild(leb);
        leb.style.display="none";
    }
    peb=e;
    var z=$(e);
    if($(li).hasClass('edit')){
        fdT(li,1,'edit');
        //e.className="adn";
        z.removeClass('aup');
    }else{
        fdT(li,0,'edit');
        //e.className="adn aup";
        z.addClass('aup');
        li.appendChild(leb);
        leb.style.display="block";
        aht(li)
    }
};
function aht(e){
    var b=e.offsetTop+e.offsetHeight;
    var d=document.body;
    var wh=window.innerHeight;
    var t=wh+d.scrollTop;
    if (b>t){
        window.scrollTo(0,b-wh);
    }
};
function acls(d){
    var t=$c($f(d));
    var c=t.length;
    for (var i=0;i<c;i++){
        $(t[i]).removeClass('on')
    }
}
function mask(e,d,m){
    if(!d)d=document.createElement('div');
    d.style.display='block';
    if(m){
        d.innerHTML=m;
        d.className='mask';
        d.style.paddingTop=(e.offsetHeight/2-10)+'px';
    }
    else {
        d.className='mask load';
    }
    e.appendChild(d);
    return d;
}
function adel(e,h,v){
    se(v);
    Dialog.hide();
    if(confirm(lang=='ar'?'حذف هذا الإعلان؟':'Delete this ad?')){
        if(!h)h=0;
        //var l=$p(e,2);
        var s=$('#'+eid);
        //s.css('border','2px solid red');
        var d=mask(s[0]);
        $.ajax({
            type:'POST',
            url:'/ajax-adel/',        
        dataType:'json',
            data:{
                i:eid,
                h:h
            },
            success:function(rp){
                if(rp.RP){
                    var m=(lang=='ar'?'تم الحذف':'Deleted');
                    mask(s[0],d,m);
                }else{
                    s[0].removeChild(d);
                }
            }
        });
    }
}
function are(e,v){
    se(v);
    Dialog.hide();
    if(confirm(lang=='ar'?'تجديد هذا الإعلان؟':'Renew this ad?')){
        //var l=$p(e,2);
        //var s=$p(l);
        var s=$('#'+eid);
        //fdT(l,1,'adn');
        //s.removeChild(l);
        var d=mask(s[0]);
        $.ajax({
            type:'POST',
            url:'/ajax-arenew/',        
        dataType:'json',
            data:{
                i:eid
            },
            success:function(rp){
                if(rp.RP){
                    var m=(lang=='ar'?'تم تحويل الإعلان للائحة إنتظار النشر':'Ad is pending to be re-published');
                    mask(s[0],d,m);
                }else{
                    s[0].removeChild(d);
                }
            }
        });
    }
}
function ahld(e,v){
    se(v);
    Dialog.hide();
    if(confirm(lang=='ar'?'إيقاف عرض هذا الإعلان؟':'Stop this ad?')){
        //var l=$p(e,2);
        //var s=$p(l);
        var s=$('#'+eid);
        //fdT(l,1,'adn');
        //s.removeChild(l);
        var d=mask(s[0]);
        $.ajax({
            type:'POST',
            url:'/ajax-ahold/',        
        dataType:'json',
            data:{
                i:eid
            },
            success:function(rp){
                if(rp.RP){
                    var m=(lang=='ar'?'تم تحويل الإعلان إلى الأرشيف':'Ad is moved to archive');
                    mask(s[0],d,m);
                }else{
                    s[0].removeChild(d);
                }
            }
        });
    }
}
function sLD(e){
    var c=e.childNodes;
    var l=c.length;
    if(l==3){
        e.removeChild(c[2]);
        l=2;
    }
    for(var i=0;i<l;i++){
        c[i].style.visibility='hidden'
    }
    var d=document.createElement('span');
    d.className='load';
    e.appendChild(d);
    //e.className+=' on';
    $(e).addClass('on');
}
function eLD(e){
    //e.className=e.className.replace(/ on/g,"");
    $(e).removeClass('on');
    var c=e.childNodes;
    var l=c.length;
    if(l==3){
        e.removeChild(c[2]);
        l=2;
    }
    for(var i=0;i<l;i++){
        c[i].style.visibility='visible'
    }
};
//render time spans
    var ts=document.getElementsByTagName('time');
    var ln=ts.length;
    var time=parseInt((new Date().getTime())/1000);
    var d=0,dy=0,rt='';
    for(var i=0;i<ln;i++){
        d=time-parseInt(ts[i].getAttribute('st'));
        dy=Math.floor(d/86400);
        //if(dy<=31){
            rt='';
            if(lang=='ar'){
                rt=since+' ';
                if(dy){
                    rt+=(dy==1?"يوم":(dy==2?"يومين":dy+' '+(dy<11?"أيام" : "يوم")))
                }else{
                    dy=Math.floor(d/3600);
                    if(dy){
                        rt+=(dy==1?"ساعة":(dy==2?"ساعتين":dy+' '+(dy<11?"ساعات" : "ساعة")))
                    }else{
                        dy=Math.floor(d/60);
                        if(dy==0) dy=1;
                        rt+=(dy==1?"دقيقة":(dy==2?"دقيقتين":dy+' '+(dy<11?"دقائق" : "دقيقة")))
                    }
                }
            }else {
                if(dy){
                    rt=(dy==1?'1 day':dy+' days')
                }else{
                    dy=Math.floor(d/3600);
                    if(dy){
                        rt=(dy==1?'1 hour':dy+' hours')
                    }else{
                        dy=Math.floor(d/60);
                        if(dy==0)dy=1;
                        rt=(dy==1?'1 minute':dy+' minutes')
                    }
                }
                rt+=' '+ago;
            }
            ts[i].innerHTML=' '+rt;
        //}
    }
var statDiv;
function aStat(e,d){
    se(d);   
    var f=function(o){
        window.removeEventListener("popstate", f, true);
        statDiv.parent().css('display','none');
    };   
    
    /*var d=$p(e,2);
    var s=$c(d,1);
    var z=$(e);
    if(z.hasClass('on')){
        s.style.display='none';
        z.removeClass('on');
    }else{*/
    if(!statDiv){
        statDiv=$('#statAiv');
        $(".close",statDiv.parent()).click(function(){
            f();
            history.back();
        });
    }
        //acls(d);
        //z.addClass('on');
        //s.style.display='block';
        //aht(d.parentNode);
        
        var y=statDiv;
        y.addClass('load');
        y.html("");
        y.parent().css('display', 'block');
         
    var stateObj = {
        stats: 1
    };
    history.pushState(stateObj, document.title, document.location);
    window.addEventListener("popstate", f, true);
    
        $.ajax({
            type:'POST',
             url:'/ajax-ga/',
             data:{
                 u:uid,
                 a:eid
             },
             dataType:'json',
             success:function(sp){
                 if(sp.RP){
                     if(sp.DATA.d){
                         var gSA={
                            chart: {
                                spacingRight:0,
                                spacingLeft:0,
                                renderTo: 'statAiv'
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
                        var chart = new Highcharts.Chart(gSA);
                        //y.highcharts(gSA);
                        /*
                         if(!isAc){
                            var tm=$('<span class="sopt"><span class="bt fb"><span class="k refr"></span></span></span>');
                        y.parent().append(tm);
                        tm.click(function(e){
                            se(e);
                            var b=$(this);
                            b.css('display','none');
                            y.addClass('load');
                            $.ajax({
                                type:'POST',
                                 url:'/ajax-ga/',
                                 data:{
                                    u:uid,
                                    a:ix
                                 },
                                 dataType:'json',
                                 success:function(bp){
                                     y.removeClass('load');
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
                                                chart = new Highcharts.Chart(gSA);
                                                //y.highcharts(gSA);
                                             }
                                     }
                                 },error:function(bp){
                                     y.removeClass('load');
                                     b.css('display','block');
                                 }
                            });
                        });
                        }*/
                     }else{
                        y.addClass('hxf');
                        y.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                     }
                 }else{
                    y.addClass('hxf');
                    y.html('<span class="fail"></span>'+(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات إعلانك':'Mourjan system failed to load your ad statistics'));
                 }
                 y.removeClass('load');
             }
         });
    //}
}
var isAc=document.location.search.match('archive')!==null?1:0;
var li=$('li',$('#resM')),lip,ptm,atm={},liCnt,aCnt=0;
function rePic(){
    var h=$(window).height();
    if(!lip){
        lip=$(".thb",li);
        liCnt=lip.length;
    }
    lip.each(function(i,e){
        var c=$p(e,2).id;
        if(!atm[i]){
            var r = e.getBoundingClientRect();
            var k=r.top;
            if(k>=-100 && k<=h+100){
                e.innerHTML=sic[c];
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


if(uhc){
    var HSLD=0;
    (function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src=uhc+'/mob.js';
        sh.onload=sh.onreadystatechange=function(){
        if (!HSLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){
            HSLD=1;  
       
       $.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                u: (typeof uuid!=='undefined' && uuid)?uuid:uid,
                x:isAc
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    if(!isAc){
                        if(rp.DATA.d){
                            var gS={
                                chart: {
                                    spacingRight:0,
                                    spacingLeft:0,
                                    renderTo: 'statDv'
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
                            var chart = new Highcharts.Chart(gS);
                            //x.highcharts(gS);
                            var x=$('#statDv');
                            var tm=$('<div class="sopt"><span class="bt fb"><span class="k refr"></span></span></div>');
                            //x.after('<div class="sopt"><span class="bt fb"><span class="k refr"></span></span></div>');
                            x.parent().append(tm);
                            tm.click(function(e){
                                var b=$(this);
                                    b.css('display','none');
                                    x.addClass('load');
                                    $.ajax({
                                        type:'POST',
                                         url:'/ajax-ga/',
                                         data:{
                                             u:(typeof uuid!=='undefined' && uuid)?uuid:uid,
                                             x:isAc
                                         },
                                         dataType:'json',
                                         success:function(bp){
                                             x.removeClass('load');
                                             b.css('display','block');
                                             if(bp.RP){
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
                                                        //x.highcharts(gS);
                                                        chart = new Highcharts.Chart(gS);
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
                            x.html( lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display' );
                            trePic();
                        }
                    }
                    li.each(function(i,e){
                        var s=$('.ata',e);
                        s.removeClass('load');
                        if(typeof rp.DATA.a[e.id] !== 'undefined'){
                            s.html('<span class="k stat"></span>'+rp.DATA.a[e.id]+(lang=='ar'?' مشاهدة':' imp'));
                        }else{
                            s.html('<span class="k stat"></span> NA');
                        }
                    });
                }else{
                    var x=$('#statDv');
                    x.removeClass('load');
                    x.addClass('hxf');
                    x.html('<span class="fail"></span>'+(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات حسابك':'Mourjan system failed to load your statistics'));
                    trePic();
                }
            }
       });
    }};head.insertBefore(sh,head.firstChild)})();
}else if(ustats){
    $.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                u: (typeof uuid!=='undefined' && uuid)?uuid:uid,
                x:isAc
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    li.each(function(i,e){
                        var s=$('.ata',e);
                        s.removeClass('load');
                        if(typeof rp.DATA.a[e.id] !== 'undefined'){
                            s.html('<span class="k stat"></span>'+rp.DATA.a[e.id]+(lang=='ar'?' مشاهدة':' imp'));
                        }else{
                            s.html('<span class="k stat"></span> NA');
                        }
                    });
                }
            }
       });
}
function makePre(){
    var li = $('#'+eid);
    if(li.hasClass('multi')){
        mCPrem();
    }else{
        var sp = $('#spinner').SelectNumber();
        Dialog.show("make_premium",null,function(){confirmPre(sp)});
    }
}
function confirmPre(sp){
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
        makePremium(v);
    });
}
function makePremium(c){
    var li = $('#'+eid);
    var d=mask(li[0]);    
    var i=li.attr("id");
    $.ajax({
        type:"POST",
        url:"/ajax-mpre/",
        data:{i:i,c:c,u:UIDK},
        dataType:"json",
        success:function(rp){
            d.remove();
            if (rp.RP) {
                Dialog.show('alert_dialog','<span class="done"></span>'+(lang=='ar'?'سيتم تمييز هذا الإعلان خلال لحظات':'This ad will be premium in few moments'));                    
                li.addClass('vp');
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
function cancelPremium(e){
    Dialog.show("stop_premium",null,function(){cancelPremiumOK(e)});
}
function cancelPremiumOK(e){
    var li=$('#'+eid);
    var d=mask(li[0]);    
    var i=li.attr("id");
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
function errDialog(){
    Dialog.show('alert_dialog','<span class="fail"></span>'+(lang=='ar'?'فشل محرك مرجان باتمام العملية<br />يرجى المحاولة مجدداً<br />او <a class="lnk" href="/contact/">اطلعنا بالامر</a>':'Your request has failed<br />please try again<br />or <a class="lnk" href="/contact/en/">Tell us about it</a>'));
}