function ado(e){
    var li=$p(e,2);
    if (!leb){
        leb=$('#aopt')[0];
    }else if($p(leb)){
        var l=$p(leb);
        if (l!=li) {
            fdT(l,1,'edit');
            $(peb).removeClass('aup');
            //peb.className="adn";
            acls(leb)
        }
        l.removeChild(leb);
        leb.style.display="none";
    }
    //var cn=li.className;
    peb=e;
    var z=$(li);
    if(z.hasClass('edit')){
        fdT(li,1,'edit');
        $(e).removeClass('aup');
        //e.className="adn";
    }else{
        fdT(li,0,'edit');
        $(e).addClass('aup');
        //e.className="adn aup";
        var c=$c($f(leb));
        if (z.hasClass('fav')){
            c[0].style.display='none';
            c[1].style.display='block';
        }else{
            c[1].style.display='none';
            c[0].style.display='block';
        }
        li.appendChild(leb);
        leb.style.display="block";
        aht(li)
    }
}
function aht(e){
    var b=e.offsetTop+e.offsetHeight;
    var d=document.body;
    var wh=window.innerHeight;
    var t=wh+d.scrollTop;
    if (b>t){
        window.scrollTo(0,b-wh);
    }
}
function bshare(s,x){
    var dl=serv.length;
    var l,a,f,n;
    for (var i=0;i<dl;i++){
        l=document.createElement('div');
        a=document.createElement('a');
        l.appendChild(a);
        s.appendChild(l);
        f=document.createElement('label');
        f.innerHTML=serv[i];
        l.appendChild(f);
        n={
            "service":serv[i],
            "element":a,
            "url":x.l,
            "title":x.t,
            "type":"large",
            "summary":x.c
        };
        if(x.p){
            n['image']=x.p;
        }
        stWidget.addEntry(n)
    }
}
var dsh,dele;
function share(e){
    if(dsh){
    if(!e)e=dele;
    if(e){
    var z=$(e);
    var d=$p(e,2);
    var li=$p(d);
    var ok=1;
    if(stWidget && stWidget.addEntry){
        var s=$c(d,1);
        if(!cli || (cli && cli!=li)){
            s.innerHTML='';
            if (cad[li.id]){
                bshare(s,cad[li.id]);
            }else{                
                ok=0;
                sLD(e);
                $.ajax({
                    type:'POST',
                    url:'/ajax-ads/',        
        dataType:'json',
                    data:{
                        id:li.id,
                        l:lang
                    },
                    success:function(rp){
                        if(rp && rp.RP){
                            var x=rp.DATA.i;
                            cad[li.id]=x;
                            bshare(s,x);
                            eLD(e);
                            acls(d);
                            z.addClass('on');
                            //e.className='on';
                            s.style.display='block';
                            aht(li)
                        }else {
                            eLD(e);
                            sms(d,xF,2)
                        }
                    }
                });
                /*posA('/ajax-ads/','id='+li.id+'&l='+lang,function(rp){
                    if(rp && rp.RP){
                        var x=rp.DATA.i;
                        cad[li.id]=x;
                        bshare(s,x);
                        eLD(e);
                        acls(d);
                        e.className='on';
                        s.style.display='block';
                        aht(li)
                    }else {
                        eLD(e);
                        sms(d,xF,2)
                    }
                })*/
            }
        }
        if(z.hasClass('on')){
            s.style.display='none';
            //e.className='';
            z.removeClass('on');
        }else if(ok){
            acls(d);
            z.addClass('on');
            //e.className='on';
            s.style.display='block';
            aht(li)
        }
        cli=li;
    }}
    }else {
        dele=e;
        sLD(e);
        if(dsh==null){
            setTimeout("(function() {var sh=document.createElement('script');sh.type= 'text/javascript';sh.async=true;sh.src='http://w.sharethis.com/button/buttons.js';sh.onload=sh.onreadystatechange=function(){if(!dsh &&(!this.readyState||this.readyState=='loaded'||this.readyState=='complete')){dsh=1;stLight.options({publisher:'74ad18c8-1178-4f31-8122-688748ba482a',onhover:false,theme:'5',async:true});share()}};var s = document.getElementsByTagName('head')[0];s.appendChild(sh);})();",100);
            dsh=0;
        }
    }
}
function sms(d,m,s){
    acls(d);
    $c(d,3).innerHTML='<h2>'+(s? '<span class="'+(s==1?'done':'fail')+'"></span>':'')+m+'</h2>';
    $c(d,3).style.display='block'
}
function rpA(e){
    var d=$p(e,2);
    var s=$c(d,2);
    var z=$(e);
    if(z.hasClass('on')){
        s.style.display='none';
        //e.className='';
        z.removeClass('on');
    }else{
        acls(d);
        z.addClass('on');
        //e.className='on';
        s.style.display='block';
        $c(s,1).focus();
        aht(d.parentNode)
    }
}
function rpS(e){
    var m=$b(e).value;
    var d=$p(e,2);
    var id=$p(d).id;
    if(m.length>0){
        $.ajax({
            type:'POST',
            url:'/ajax-report/',        
        dataType:'json',
            data:{
                id:id,
                msg:m
            }
        });
        //posA('/ajax-report/','id='+id+'&msg='+ppf(m),function(){});
        $b(e).value='';
        sms(d,xAOK,1)
    }
}
function acls(d){
    dele=null;
    var l=$cL(d);
    var n=$c(d);
    n[1].style.display='none';
    n[2].style.display='none';
    n[3].style.display='none';
    if(l==5){
        n[4].style.display='none';
    }
    var t=$c($f(d));
    var c=t.length;
    for (var i=0;i<c;i++){
        $(t[i]).removeClass('on');
        //t[i].className='';
        eLD(t[i]);
    }
}
function pF(e){
    if(!sif)sif=$("#sif")[0];    
    var d=$p(e,2);
    var s=$c(d,4);
    var z=$(e);
    if(z.hasClass('on')){
        s.style.display='none';
        sif.style.display='none';
        //e.className='';
        z.removeClass('on');
    }else{
        acls(d);
        var t=$p(sif);
        if(s!=t){
            if(sif.style.display=='block') {
                if(t==document.body) uPO(0,0);
                else {
                    acls($p(t))
                }
            }
            s.appendChild(sif);
        }
        var li=$p(d);
        var id=li.id;
        sif.style.display='block';
        //e.className='on';
        z.addClass('on');
        s.style.display='block';
        $.ajax({
            type:'POST',
            url:'/ajax-favorite/',        
        dataType:'json',
            data:{
                id:id
            }
        });
        //posA('/ajax-favorite/','id='+id,function(){})
    }
}
function aF(e){
    var z=$(e);
    if(!z.hasClass('load')){
        sLD(e);
        var p=$p(e);
        var li=$p(p,2);
        var id=li.id;
        $.ajax({
            type:'POST',
            url:'/ajax-favorite/',        
        dataType:'json',
            data:{
                id:id,
                s:0
            },
            success:function(rp){
                e.style.display='none';
                $c(p,1).style.display='block';
                eLD(e);
                var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));
                var t='<span class="k fav on"></span>';
                sc.innerHTML+=t;
                //li.className+=" fav";
                $(li).addClass('fav');
                if(mod=='detail'){
                    var d=$('#d'+id)[0];
                    if(d){
                        $c(d,1).innerHTML+=t
                    }
                }
            }
        });
        /*posA('/ajax-favorite/','s=0&id='+id,function(){
            e.style.display='none';
            $c(p,1).style.display='block';
            eLD(e);
            var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));
            var t='<span class="k fav on"></span>';
            sc.innerHTML+=t;
            li.className+=" fav";
            if(mod=='detail'){
                var d=$('d'+id);
                if(d){
                    $c(d,1).innerHTML+=t
                }
            }
        });*/
    }
}
function rF(e){
    var z=$(e);
    if(!z.hasClass('load')){
        sLD(e);
        var p=$p(e);
        var li=$p(p,2);
        var id=li.id;
        $.ajax({
            type:'POST',
            url:'/ajax-favorite/',        
        dataType:'json',
            data:{
                id:id,
                s:1
            },
            success:function(rp){
                e.style.display='none';
                $c(p,0).style.display='block';
                eLD(e);
                var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));
                var c=sc.innerHTML;
                var t='<span class="k fav on"></span>';
                sc.innerHTML=c.replace(t,'');
                fdT(li,1,'fav');
                if(mod=='detail'){
                    var d=$('#d'+id)[0];
                    if(d){
                        fdT($c(d,1),1,t);
                    }
                }
            }
        });
        /*posA('/ajax-favorite/','s=1&id='+id,function(){
            e.style.display='none';
            $c(p,0).style.display='block';
            eLD(e);
            var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));
            var c=sc.innerHTML;
            var t='<span class="k fav on"></span>';
            sc.innerHTML=c.replace(t,'');
            fdT(li,1,'fav');
            if(mod=='detail'){
                var d=$('d'+id);
                if(d){
                    fdT($c(d,1),1,t);
                }
            }
        });*/
    }
}
function sLD(e){
    var c=$c(e);
    var l=$cL(e);
    if(l==3){
        e.removeChild(c[2]);
        l=2;
    }
    for(var i=0;i<l;i++){
        c[i].style.visibility='hidden'
    }
    var d=document.createElement('span');
    d.className='load';
    //$(d).addClass('load');
    e.appendChild(d);
    fdT(e,0,'on');
}
function eLD(e){
    fdT(e,1,'on');
    var c=$c(e);
    var l=$cL(e);
    if(l==3){
        e.removeChild(c[2]);
        l=2;
    }
    for(var i=0;i<l;i++){
        c[i].style.visibility='visible'
    }
}

function owt(e){
    e=$(e);
    e.css('visibility','hidden');
    var p=e.parent();
    p.addClass('load embg');
    var b=p.parent();
    var pt='/watchlist/'+(lang=='ar'?'':'en/')+(typeof UIDK !== 'undefined' ? '?u='+UIDK : '');
    $.ajax({
        type:'POST',
        url:'/ajax-watch/',        
        dataType:'json',
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
        success:function(rp){
            p.removeClass('load');
            if(uid){
            if(rp.RP && rp.DATA.id){
                var m='<span class="lnk"><span class="done"></span>';
                if(lang=='ar'){
                    m+='تمت الإضافة الى لائحة المتابعة';
                }else{    
                    m+='search added to watchlist';
                }
                m+='</span><span class="to"></span>';
                e.html(m);
                e[0].onclick=function(){
                    document.location=pt;
                };
                e.css('visibility','visible');
            }else{
                $('.to',e)[0].className='et fail';
                e.css('visibility','visible');
            }
        }else{
            document.location=pt;
        }},
        error:function(rp){
            p.removeClass('load');
            $('.to',e)[0].className='et fail';
            e.css('visibility','visible');
        }
    })
}

/*executable code*/
if (stat){
    tmp=true;
    if(typeof document.webkitHidden!="undefined" && document.webkitHidden) tmp=false;
    if(tmp){
        $.ajax({
            type:'POST',
            url:'/ajax-stat/',        
        dataType:'json',
            data:{
                a:stat,
                l:page
            }
        });
        //posA("/ajax-stat/",'a='+stat+'&l='+page,function(){})
    }
}
if (mod=='search' || mod=='detail'){
    var ts=document.getElementsByTagName('time');
    var ln=ts.length;
    var time=parseInt((new Date().getTime())/1000);
    var d=0,dy=0,rt='';
    for(var i=0;i<ln;i++){
        d=time-parseInt(ts[i].getAttribute('st'));
        dy=Math.floor(d/86400);
        if(dy<=31){
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
            ts[i].innerHTML=rt
        }
    }
}
/*if(sic.length){
    for(i in sic){
        var e=$('#i'+i)[0];
        e.innerHTML=sic[i];
    }
}  
*/
var li=$('.rsl li'),lip,ptm,atm={},liCnt,aCnt=0;
function rePic(){
    var h=$(window).height();
    if(!lip){
        lip=$(".thb",li);
        liCnt=lip.length;
    }
    lip.each(function(i,e){
        var c=$p(e,2).id;
        if(c[0]=='d'){
            c=c.substring(1,c.length);
        }
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
/*var lastScrollTop = 0,
    isMenuUp = 0,
    isMenuOpen = 0,
    isSearchOpen = 0,
    dontBlur = 0,
    menu = $("#top"),
    menuHeight = menu.height();
$(window).scroll(function() {
    if (!isMenuOpen) {
        var e = $(this).scrollTop(),
            n = $("#bottomFtr");
        n && (e >= 60 ? n.hide(600) : n.show(600)), e > lastScrollTop && e > 40 ? isMenuUp || (menu.addClass("up"), isMenuUp = 1) : isMenuUp && (menu.removeClass("up"), isMenuUp = 0), lastScrollTop = e
    }
});*/
var _title,_scroll;
function subList(e){
    var d=$('#sublist');
    var e=$(e);
    var f=function(){    
        $('#menu').css('display','block');
        $('#main').css('display','block');
        d.removeClass('on');
        $('h1').html(_title);
        window.removeEventListener("popstate", f, true);
        setTimeout(function(){            
            window.scrollTo(0,_scroll);
        },10);
        e.removeClass('spin').addClass('rspin');
        setTimeout(function(){e.removeClass('on')},250);
    };
    var z=function(){        
        f();
        history.back()
    };
    if(d.hasClass('on')){
        z();
    }else{
        $('.on',d)[0].onclick=z;
        var h1 = $('h1');
        _title=h1.html();
        h1.html($('h2',d).html());
        d.addClass('on');
        $('#menu').css('display','none');
        $('#main').css('display','none');
        _scroll=window.pageYOffset;
        window.scrollTo(0,0);
        var stateObj = {
            list: 1
        };
        history.pushState(stateObj, document.title, document.location);
        window.addEventListener("popstate", f, true);
        e.removeClass('rspin').addClass('spin');
        setTimeout(function(){e.addClass('on')},250);
        /*if(e.hasClass("loc")){
            $.ajax({
                type:'GET',
                url:'/api/',        
                dataType:'json',
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
                success:function(rp){},
                error:function(rp){}
            });
        }*/
    }
}