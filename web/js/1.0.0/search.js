window['_s']=function(a,v){
    window[a]=v;
};
function pi(){
    $.ajax({type:'POST',url:'/ajax-pi/'});
}
if(UID)setInterval(pi,300000);
function cl(u){
    if(MOD=='detail'){
        var p=document.location.pathname;
        p=p.replace(/^\/[a-zA-Z]*/,'');
        p=p.replace(/^\/[a-zA-Z]*/,'');
        p=p.substring(1);
        var rg=new RegExp('/[0-9]*/','g');
        p=p.replace(rg,'/');
        document.location=u+p
    }else if(ubs){
        var r=new RegExp("^"+ubs);
        var p=document.location.pathname.replace(r,'');
        var rg=new RegExp('/[0-9]*/','g');
        p=p.replace(rg,'/');
        document.location=u+p+document.location.search;
    }else{
        if(lang!='ar')u+=lang+'/';
        document.location=u
    }
}
function tglLG(e){
    e=$(e);
    if(e.hasClass('on')){   
        e.parent().animate({
            height:'60px'
        }, 500);     
        e.removeClass('on');
    }else{
        e.parent().animate({
            height:'560px'
        }, 500);
        e.addClass('on');
    }
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
function isEmail(v){
    if(v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/))
        return true;
    return false;
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
}

if(hads){
    (function(){var gads=document.createElement('script');gads.async=true;gads.type='text/javascript';var useSSL='https:'==document.location.protocol;gads.src=(useSSL?'https:':'http:')+'//www.googletagservices.com/tag/js/gpt.js';head.appendChild(gads);})();
}
*/
/*(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();*/
//loading facebook plugin
if(share){
(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=184370954908428";fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));

    _s('serv',['email','facebook','twitter','googleplus','linkedin','sharethis']);
    _s('_STL',false);
    (function(){
        var sh=document.createElement('script');
        sh.type='text/javascript';
        sh.async=true;
        sh.src='https://ws.sharethis.com/button/buttons.js';
        sh.onload=sh.onreadystatechange=function(){
            if(!_STL && (!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){
                _STL=true;
                if(typeof stLight !== 'undefined'){
                    stLight.options({publisher:'74ad18c8-1178-4f31-8122-688748ba482a',onhover:false,theme:'1',async:'true',embeds:'true',headerbg:'#3087B4'});
                }
            }
        };
        var s = document.getElementsByTagName('head')[0];
        s.appendChild(sh);
    })();
}
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
function wn(u){if(u)window.open(u,'_blank')};

var time=parseInt((new Date().getTime())/1000);
$("b[st]").each(function(i,e){
    var d=time-parseInt($(e).attr("st"));
    var dy=Math.floor(d/86400);
    if(dy<=31){
        var rt='';
        if(lang=='ar'){
            rt="منذ ";
            if(dy){
                rt+=(dy==1?"يوم":(dy==2?"يومين":dy+' '+(dy<11?"أيام" : "يوم")))
            }else{
                dy=Math.floor(d/3600);
                if(dy){
                    rt+=(dy==1?"ساعة":(dy==2?"ساعتين":dy+' '+(dy<11?"ساعات" : "ساعة")))
                }else{
                    dy=Math.floor(d/60);
                    if(dy==0)dy=1;
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
            rt+=" ago";
        }
        e.innerHTML=rt
    }
});
if ( (typeof (disqus_shortname)!=="undefined") && MOD=='detail') {
    (function() {var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);})();
}
/*
if ( (typeof (disqus_shortname)!=="undefined") && (MOD=='detail' || MOD=='search' || MOD=='myads')){
     (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = '//' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
}
*/
_s('hvt',null);
_s('hve',null);
_s('ms',null);
_s('cli',null);
_s('sar',[]);
function hva(e){
    e=hve;
    var ce=$(e);
    if(cli!=e || !ce.hasClass('hover')){
        ms.empty();
        sid=e.id;
        var n=$('.cct',ce)[0];
        if (cli){
            var t=$(cli);
            if (!t.hasClass("fon")) $(".fav,.ab", $(cli)).remove();
        };
        ftxt=(lang=='ar'?'أضف إلى مفضلتي':'add to my favorites');
        atxt=(lang=='ar'?'تبليغ عن إساءة':'report abuse');
        if (UID) { 
            if(!ce.hasClass("fon")){
                var fv=$("<span onclick='fv(this)' class='i fav' title='"+ftxt+"'></span>");
                n.appendChild(fv[0]);
                fv=$("<span onclick='rpa(this)' class='i ab' title='"+atxt+"'></span>");
                n.appendChild(fv[0]);
            }
        }else{
            var fv=$("<span onclick='fv(this);' class='i fav' title='"+ftxt+"'></span>");
            n.appendChild(fv[0]);
            fv=$("<span onclick='rpa(this)' class='i ab' title='"+atxt+"'></span>");
            n.appendChild(fv[0]);
        }
        n.appendChild(ms[0]);
        ce.addClass('hover');
        cli=e;
        /*if(0 && share){
            if(sar[sid]) {
                rha(sar[sid]);
            }else {
                $.ajax({
                    type:'POST',
                    url:'/ajax-ads/',
                    data:{id:sid},
                    dataType:"json",
                    success:function(rp){
                        if(rp && rp.RP && rp.DATA.i){
                            sar[sid]=rp.DATA.i;
                            rha(sar[sid]);
                        }
                    }
                });
            }
        }*/
    }
}
function rha(r){
    if(serv && _STL && r.i==cli.id && (typeof stWidget !== 'undefined')){
        ms.css("display","inline-block");
        $.each(serv,function(i){
            var s=document.createElement("span");
            s.className='st_'+serv[i];
            ms[0].appendChild(s);
            var n={
                service:serv[i],
                element:s,
                url:r.l,
                title:r.t,
                type:"chicklet",
                summary:r.c
            };
            if(r.p){
                n['image']=r.p
            }
            stWidget.addEntry(n)
        })
    }
}

function fv(e,dt){
    e=$(e);
    var p,id,w;
    if(dt){
        p=e.closest('.dt');
        id=AID;
        if(UID && MOD=='detail'){
            w=$('#'+id);
        }
    }else{
        p=e.closest('li');
        id=p.attr('id');
        if(UID && MOD=='detail' && id==AID){
            w=$('.dt');
        }
    }
    if(UID){
        var s=0,m,f;
        if(dt)f=e.children();
        if( (f && $(f[0]).hasClass("on") ) || e.hasClass("on")) {
            s=1;
            m=(lang=='ar'?'أضف إلى مفضلتي':'add to my favorites');
            if(dt){
                f=e.children();
                $(f[0]).removeClass("on");
                $(f[1]).html(m);
                var t=$("<div onclick='rpa(this,0,"+id+")'><span class='i ab'></span><span>"+(lang=='ar'?'تبليغ عن إساءة':'report abuse')+"</span></div>");
                e.after(t);
                
                if(w && w.length){
                    w.removeClass('fon');
                    w.attr('title',m);
                    cli=null;
                    $(".ab",w[0]).remove();
                    $(".fav",w[0]).remove();
                    ms.empty();
                }
            }else{
                p.removeClass("fon");
                e.removeClass("on");
                e.title=m;
                var t=$("<span onclick='rpa(this)' class='i ab' title='"+(lang=='ar'?'تبليغ عن إساءة':'report abuse')+"'></span>");
                e.after(t);
                
                if(w && w.length){
                    var o=$('.opt',w[0]);
                    var c=o.children();
                    var f=$(c[0]).children();
                    $(f[0]).removeClass("on");
                    $(f[1]).html(m);
                    t=$("<div onclick='rpa(this,0,"+id+")'><span class='i ab'></span><span>"+(lang=='ar'?'تبليغ عن إساءة':'report abuse')+"</span></div>");
                    $(c[0]).after(t);
                }
            }
        }else {
            m=(lang=='ar'?"إزالة من مفضلتي":'remove from my favorites');
            if(dt){
                f=e.children();
                $(f[0]).addClass("on");
                $(f[1]).html(m);
                $(".ab",e.parent()[0]).parent().remove();
                
                if(w && w.length){
                    w.addClass('fon');
                    w.attr('title',m);
                    ms.empty();
                    $(".ab",w[0]).remove();
                    $(".fav",w[0]).remove();
                    $(".cct",w[0]).append("<span onclick='fv(this)' class='i fav on' title='"+m+"'></span>");
                    cli=null;
                }
            }else{
                p.addClass("fon");
                e.addClass("on");
                e.title=m;
                $(".ab",e.parent()[0]).remove();
                
                if(w && w.length){
                    var o=$('.opt',w[0]);
                    var c=o.children();
                    var f=$(c[0]).children();
                    $(f[0]).addClass("on");
                    $(f[1]).html(m);
                    $(c[1]).remove();
                }
            }
        }
        $.ajax({
            type:'POST',
            url:'/ajax-favorite/',
            data:{id:id,s:s},
            dataType:"json"
        });
    }else{
        $.ajax({
            type:'POST',
            url:'/ajax-favorite/',
            data:{id:id},
            dataType:"json",
            success:function(rp){
                document.location='/favorites/'+(lang=='ar'?'':'en/');
            }
        })
    }
}

_s('rpd',null);
function rpa(e,o,dt){
    e=$(e);
    if(o){
        if(o==1){
            var p=e.parent();
            var b=p.prev();
            var t=$('textarea',p);
            t.val('');
            var li=p.parent();
            if(li.hasClass('dt'))dt=1;
            if(dt){
                b.css('display','block');
                p.remove();
            }else{
                li.animate({
                    height:130
                },100,function(){
                    b.css('display','block');
                    p.remove();
                });
            }
        }else if(o==2){
            var t=e.prev();
            var msg=t.val();
            if(msg.length>0){
                var p=e.parent();
                var b=p.prev();
                var li=p.parent();
                if(li.hasClass('dt'))dt=1;
                $.ajax({
                    type:"POST",
                    url:"/ajax-report/",
                    data:{
                        id:(dt?AID:li.attr("id")),
                        msg:msg
                    },
                    dataType:"json"
                });
                t.val('');
                if(!dt){
                    li.animate({
                        height:130
                    },100);
                    li.unbind('mouseenter mouseleave');
                }
                var m='<b class="anb"><span class="done"></span>'+(lang=='ar'?'تم تبليغ شكواك وسيتم مراجعتها قريباً':'Your complaint has been reported and will be reviewed soon')+'</b>';
                b.css('background-color','#fbe385');
                b.html(m);
                b.css('display','block');
                p.remove();
                if(MOD=='detail'){
                    var w;
                    if(dt){
                        w=$('#'+AID);
                        w.unbind('mouseenter mouseleave');
                        if(w.length){
                            w=$('.cct',w[0]);
                            w.css('background-color','#fbe385');
                            w.html(m);
                            w.css('display','block');
                        }
                    }else{
                        w=$('.dt');
                        w=$('.opt',w[0]);
                        w.css('background-color','#fbe385');
                        w.html(m);
                        w.css('display','block');
                    }
                }
            }else{
                e.prev().addClass("err")
            }
        }
    }else{
        if(ULV==9){
                var p=e.parent();
                var li=p.parent();
            if(ULV==9 && (li.hasClass("vpd") || li.hasClass("vpz"))){
                alert(lang=="ar"?"هذا اعلان مميز ولا يمكن ايقافه":"This is a premium ad and it cannot be stopped");
            }else if(confirm("Hold this ad?")){
                e.addClass("load");
                $.ajax({
                    type:"POST",
                    url:"/ajax-report/",
                    data:{id:(dt?AID:li.attr("id"))},
                    dataType:"json",
                    success:function(rp){
                        if(rp.RP){
                            e.remove();
                            p.css("background-color", "#fbe385");
                            p.addClass('ctr');
                            p.html('stopped');
                            li.unbind('mouseenter mouseleave');
                        }
                    },
                    error:function(rp){
                        e.removeClass("load")
                    }
                });
            }
        }else{
            if(!rpd)rpd=$("#rpd");
            var r=rpd;
            var x=r.parent();
            if(x && x.prop('tagName')==='LI'){
                var xc=r.prev();
                var t=$('textarea',r);
                t.val('');
                x.animate({
                    height:130
                },100,function(){
                    xc.css('display','block');
                });
            }else if(x && x.hasClass('dt')){
                var xc=r.prev();
                xc.css('display','block');
            }
            var c=e.parent();
            c.css('display','none');
            c.after(r);
            if(!dt){
                var li=r.parent();
                li.animate({
                    height:249
                },100);
            }
            r.css('display','block');
        }
    }
}

ms=$('#mis');
var li=$("ul.ls li"),pli=$('ul.pe'),sli=1,lip,ptm,atm={},liCnt,aCnt=0;
function rePic(){
    if(sli){
        sli=0;
        var sf=$('#sideFtr');
        var id=$(":first-child",sf).attr("id");
        if(id){
            $('.ig', sf).html(sic[id.substring(2)]);
        }
    }
    var h=$(window).height();
    if(!lip){
        lip=$(".ig",li);
        liCnt=lip.length;
    }
    lip.each(function(i,e){
        var c=e.parentNode.parentNode.id;
        if(!atm[i]){
            var r = e.getBoundingClientRect();
            var k=r.top;
            var b=r.bottom;
            if( (k>=-100 && k<=h+100) || (b>=-100 && b<=h+100)){
                e.innerHTML=sic[e.parentNode.parentNode.id];
                atm[i]=1;
                aCnt++;
            }
        }
    });
    if(upem && pli.length){
        var r = pli[0].getBoundingClientRect();
        var k=r.top;
        var b=r.bottom;
        if( (k>=-100 && k<=h+100) || (b>=-100 && b<=h+100)){
            var l = $('.ik',pli);
            l.each(function(i,e){
                e.innerHTML = sic[e.parentNode.parentNode.id];
            });
        }
    }else{
        upem=0
    }
    if(liCnt==aCnt && !upem){
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

li.hover(function(e){
    hve=this;
    if(hvt)clearTimeout(hvt);
    hvt=setTimeout('hva();',300)
},function(e){$(this).removeClass('hover')});

$('#ewd').bind('click',function(e,i){
    var d=$(this);
    var o=d.parent();
    var p=d.prev();
    var t=p.prev();
    if(o.hasClass('on')){
        p.stop().fadeOut(200,function(){
            t.stop().fadeIn(200,function(){
                t.css('opacity',1);
            });            
        });
        o.removeClass('on');
    }else{            
        p.css('display','none');
        p.removeClass('hid');
        t.stop().fadeOut(200,function(){
            p.stop().fadeIn(200);
        });
        o.addClass('on');
    }
});
function owt(e){
    e=$(e);
    e.css('visibility','hidden');
    var p=e.parent();
    p.addClass('load');
    var b=p.parent();
    var pt='/watchlist/'+(lang=='ar'?'':'en/')+(typeof UIDK !== 'undefined' ? '?u='+UIDK : '');
    $.ajax({
        type:'POST',
        url:'/ajax-watch/',
        data:{
            lang:lang,
            cn:_cn,
            c:_c,
            s:_se,
            e:_ext,
            l:_loc,
            p:_pu,
            q:_q,
            t:_ttl
        },
        dataType:"json",
        success:function(rp){
            p.removeClass('load');
            if(UID){
            var btv='<span class="db ctr"><a class="bt" href="/watchlist/en/?u='+UIDK+'">view my watchlist</a></span>';
            if(lang=='ar'){
                btv='<span class="db ctr"><a class="bt" href="/watchlist/?u='+UIDK+'">';
                btv+='تصفح لائحتي للمتابعة';
                btv+='</a></span>';
            }
            if(rp.RP && rp.DATA.id){
                _ttl=_ttl.replace(/\<.*?\>/g,'');
                var m='';
                if(lang=='ar'){
                    m+='تم إضافة ';
                    m+='<b>'+_ttl+'</b> ';
                    m+='إلى لائحة المتابعة';
                    m+=btv;
                }else{    
                    m+='<b>'+_ttl+'</b> ';
                    m+='is added to watchlist';
                    m+=btv;
                }
                b.html(m);
                b.next().addClass('ekon');
            }else{
                p.css('visibility','hidden');
                e.css('visibility','visible');
                var c=p.html();
                p.html('<span class="fail"></span>'+rp.MSG);
                var x=$('.refresh',p);
                var y=$('.retry',p);
                x.click(function(){
                    document.location='';
                });
                y.click(function(){
                    p.html(c);
                });
                if(x.length==0 && y.length==0){
                    p.html('<span class="fail"></span>'+rp.MSG+btv);
                }
                if(y.length==0){
                    var z=p.parent();
                    z.html(p.html());
                }
                p.css('visibility','visible');
            }
        }else{
            document.location=pt;
        }},
        error:function(rp){
            p.removeClass('load');
            e.css('visibility','visible');
        }
    })
}

//updating stats
if(stat && !(typeof document.webkitHidden!=="undefined" && document.webkitHidden)){
    $.ajax({
        type:'POST',
        url:'/ajax-stat/',
        data:{a:stat,l:page},
        dataType:'json'
    });
}
//rendering detail images
if(typeof imgs!=="undefined"){
    var d=$('#pics');var spim,dtm=null,dam=[],dCnt=0;
    function dePic(){
        var h=$(window).height();
        if(!spim){
            spim=$("span",d);
        }
        spim.each(function(i,e){
            if(!dam[i]){
                var r = e.getBoundingClientRect();
                var k=r.top;
                var b=r.bottom;
                if( (k>=-100 && k<=h+100) || (b>=-100 && b<=h+100)){
                    e.innerHTML='<img src="'+uimg+'/repos/d/'+imgs[parseInt(e.className.replace(/[a-z]/g,''))]+'" />';
                    dam[i]=1;
                    dCnt++;
                }
            }
        });
        if(spim.length==dCnt){
            $(window).unbind('scroll',drePic);
            $(window).unbind('scroll',drePic);
        }
    }
    function drePic(){    
        if(dtm){
            clearTimeout(dtm);
            dtm=null;
        }
        dtm=setTimeout('dePic()',100);
    }
    $(window).bind('scroll',drePic);
    $(window).bind('resize',drePic);
    drePic();
}

$('.ms.ut',li).each(function(){
    var e=$(this);
    var v = e.attr('value');
    var m='';
    if(lang=='ar'){
        m='هذا المعلن مصنف من قبل مرجان ك';
        switch(v){
            case 'a1':
                m+='مكتب عقاري';
                break;
            case 'a2':
                m+='معرض سيارات';
                break;
            case 'a3':
                m+='مكتب توظيف';
                break;
            case 'p0':
                m+='معلن فردي وعلى الارجح المالك';
                break;
            case 'p1':
                m+='معلن فردي وعلى الارجح شركة او مؤسسة او طالب العمل';
                break;
        }
        m+=' - هذا التصنيف لا زال قيد التطوير وقد لا يكون دقيق في بعض الحالات';
    }else{
        m='This advertiser has been classified by mourjan as ';
        switch(v){
            case 'a1':
                m+='a real estate broker';
                break;
            case 'a2':
                m+='a car dealership';
                break;
            case 'a3':
                m+='a recruiting agency';
                break;
            case 'p0':
                m+='an individual and most likely the owner';
                break;
            case 'p1':
                m+='an individual and most likely the employer or employee';
                break;
        }
        m+=' - this classification is still under development and may not be 100% accurate';
    }
    e.attr('title',m);
});

if(MOD=='detail'){
    var vid=$('#vid'),vide=$('embed',vid);
    if(vid.length){
        $(window).bind('resize',reVid);   
        function reVid(){
            var r = vid[0].getBoundingClientRect();
            if(r.width>648){
                vide.attr('width',648);
            }else{
                vide.attr('width',450);
            }
        }
        reVid();
    }
}
//rendering map in detail page
if(typeof hasMap!=="undefined"){
    var map,mapd,marker,rmap=0,geocoder,infoWindow;
    _s('infoWindow',null);
    _s('geocoder',null);
    _s('marker',null);
    _s('map',null);
    var s=document.createElement("script");
    s.type="text/javascript";
    s.src = "//maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=initMap&language="+lang;
    document.body.appendChild(s);
    
    function initMap() {
        geocoder = new google.maps.Geocoder();
        infowindow = new google.maps.InfoWindow();
        var opt = {
            zoom:17,
            mapTypeId: google.maps.MapTypeId.HYBRID
        };
        map = new google.maps.Map($('#map')[0], opt);
        marker = new google.maps.Marker({
            map: map,
            icon:ucss+'/i/loc.png',
            animation: google.maps.Animation.DROP
        });
        google.maps.event.addListener(marker, "click", function() {
            infowindow.open(map, marker);
        });
        pos = new google.maps.LatLng(LAT,LON);
        map.setCenter(pos);
        marker.setPosition(pos);
        infowindow.setContent(DTTL);
        infowindow.open(map, marker);
    }
}

var curWID=0,curF=null;
function mask(e){
    e=$(e);
    var d=$("<div class=\'od load\'></div>");
    var w=e.outerHeight();
    d.height(w);
    d.width(e.outerWidth());
    d.css("line-height",w+"px");
    var o=e.offset();
    d.offset(e.offset());
    $("body").append(d);
    return d
}
function eW(e,id){
    curWID=id;
    var n;
    var li=$(e).closest('li');
    var f=li.first();
    var m=$('.mail',f).length;
    var ttl=f.html().replace(/\<.*?\>/g,'');
    if(curF){
        if(curF.parent().length){
            var di=curF.closest('li');
            n=di.children();
            curF.remove();
            di.css('list-style-type','inherit');
            $(n[0]).css('display','inline-block');
            $(n[1]).css('display','inline-block');
        }
    }else{
        curF=$('<div class="form forw"><ul><li><label>'+(lang=='ar'?'تسمية':'Label')+':</label><input onkeydown="idir(this)" onchange="idir(this,1)" type="text" name="label"></li><li><label>'+(lang=='ar'?'اشعار برسالة إلكترونية':'Email Notifications')+'</label><input type="radio" name="notify" value="1" />'+(lang=='ar'?'فعال':'on')+'<input type="radio" class="mr50" name="notify" value="0" />'+(lang=='ar'?'غير فعال':'off')+'</li><li class="ctr"><span class="bt" onclick="eWS(this)">'+(lang=='ar'?'حفظ':'save')+'</span><span class="bt" onclick="eWC(this)">'+(lang=='ar'?'إلغاء':'cancel')+'</span><span class="bt cl" onclick="eWD(this)">'+(lang=='ar'?'حذف':'delete')+'</span></li></ul></div>');
    }
    var c=curF;
    var t=$('input[name="label"]',c);
    t.attr("value",ttl);
    var r0=$('input[name="notify"][value="0"]',c);
    var r1=$('input[name="notify"][value="1"]',c);
    if(m){
        r0.attr('checked',false);
        r1.attr('checked',true);
    }else{        
        r0.attr('checked',true);
        r1.attr('checked',false);
    }
    li.css('list-style-type','none');
    li.append(c);
    n=li.children();
    $(n[0]).css('display','none');
    $(n[1]).css('display','none');
}
function eWC(e){
    var li=$(e).parent().parent().parent().parent();
    var n=li.children();
    curF.remove();
    li.css('list-style-type','decimal-leading-zero');
    li.css('list-style-position','inside');
    $(n[0]).css('display','inline-block');
    //$(n[0]).css('float','right');
    $(n[1]).css('display','inline-block');
    $(n[1]).css('float','none');
    $(n[1]).css('float','left');
}
function eWD(e){
    if(confirm(lang=='ar'?'هل أنت متأكد أنك تريد(ين) إزالة هذا البحث من لائحة المتابعة؟':'Are you sure that you want to remove this search from watchlist?')){
        var li=$(e).parent().parent().parent().parent();
        var d=mask(li);
        $.ajax({
            type:"POST",
            url:"/ajax-remove-watch/",
            data:{id:curWID},
            dataType:"json",
            success:function(rp){
                if (rp.RP) {
                    d.removeClass('load');
                    d.html(lang=='ar'?'تمت الإزالة':'removed');   
                    var n=li.children();                 
                    curF.remove();
                    li.css('list-style-type','inherit');
                    $(n[0]).css('display','inline-block');
                    $(n[1]).css('display','inline-block');
                    var h=li.outerHeight();
                    d.height(h);
                    d.css("line-height",h+"px");
                }else {
                    d.remove()
                }
            },
            error:function(){
                d.remove()
            }
        })
    }
}
function eWS(e){
    var f=$(e).parent().parent();
    var v=$('input[name=label]',f).val();
    v=v.replace(/^\s+|\s+$/g, '');
    if(v.length){
        var li=f.parent().parent();
        var d=mask(li);   
        var m=parseInt($('input[name=notify]:checked', f).val());
        $.ajax({
            type:"POST",
            url:"/ajax-watch-update/",
            data:{
                id:curWID,
                t:v,
                e:m
            },
            dataType:"json",
            success:function(rp){
                if (rp.RP) {
                    d.remove();
                    var n=li.children();
                    var x=$(n[0]);
                    x.html(x.html().replace(/\<span class=\"d mail\"\>\<\/span\>/ig,''));
                    var k=x.children();
                    $(k[k.length-1]).replaceWith(rp.DATA.T);
                    if(m){
                        $(k[0]).after($('<span class="d mail"></span>'));
                    }
                    curF.remove();
                    li.css('list-style-type','inherit');
                    x.css('display','inline-block');
                    $(n[1]).css('display','inline-block');
                }else {
                    d.remove()
                }
            },
            error:function(){
                d.remove()
            }
        })
    }
}
function oFtr(e,key){
    var li=$(e);
    var ul=li.parent();
    li[0].onclick=function(){
        cFtr(e,key);
    };
    li.addClass('von');
    $('span',li).addClass('arrowU');
    $('.'+key,ul).css('display','none').removeClass('hid').slideToggle();
}
function cFtr(e,key){  
    var ul=$(e).parent();
    var li = $('.Z'+key,ul);
    li[0].onclick=function(){
        oFtr(e,key);
    };
    li.removeClass('von');
    $('span',li).removeClass('arrowU');
    $('.'+key,ul).slideToggle();
}