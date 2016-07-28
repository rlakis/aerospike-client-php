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









var ase=[],ccN,ccD,ccA,ccF,tmpT=0,btwT=0,tar,ctac=0,tlen,edN,uForm,vForm,txt,atxt,FLK=1,map,vmap,mapD,mpU;
function se(){
    var v=window.event;
    if(v){
        if(v.preventDefault)
            v.preventDefault();
        if(v.stopPropagation)
            v.stopPropagation();
    }
};
function spe(){
    var v=window.event;
    if(v){
        if(v.stopPropagation)
            v.stopPropagation();
    }
};
function pds(e){
    e.addEventListener('touchmove',ds);
};
function ds(e){
    e.preventDefault();
};

function gto(e){
    var r = e.getBoundingClientRect();
    var doc = document.documentElement, body = document.body;
    var top = (doc && doc.scrollTop  || body && body.scrollTop  || 0);
    window.scrollTo(0,r.top+top);
}
function $n(i){
    return document.getElementById(i)
}
function $p(e,n){
    if(!n)n=1;
    while(n--){
        e=e.parentNode;
    }
    return e
}
function $b(e,n){
    if(!n)n=1;
    while(n--){
        e=e.previousSibling;
    }
    return e
}
function $a(e,n){
    if(!n)n=1;
    while(n--){
        e=e.nextSibling;
    }
    return e
}
function $f(e,n){
    if(!n)n=1;
    while(n--){
        e=e.firstChild;
    }
    return e
}
function $c(e,n){
    e=e.childNodes;
    if(n==null){
        return e;
    }else{
        return e[n];
    }
}
function $cL(e,n){
    return e.childNodes.length;
}
function fdT(u,s,c){
    if(u.tagName==='DIV') return;
    if(!c)c='hid';
    if(s){
        var q=new RegExp('\s'+c+'|'+c,'ig');
        var k=u.className;
        k=k.replace(q,'');
        k=k.replace(/^\s+|\s+$/g,'');
        u.className=k;
    }else{
        u.className+=' '+c;
    }
}
function aR(){
    var xhr=false;
    if(typeof XMLHttpRequest !== 'undefined') xhr = new XMLHttpRequest();  
    else {  
        var versions = ["MSXML2.XmlHttp.5.0",   
                        "MSXML2.XmlHttp.4.0",  
                        "MSXML2.XmlHttp.3.0",   
                        "MSXML2.XmlHttp.2.0",  
                        "Microsoft.XmlHttp"];  

         for(var i = 0, len = versions.length; i < len; i++) {  
            try {  
                xhr = new ActiveXObject(versions[i]);  
                break;  
            }  
            catch(e){}  
         } 
    }
    return xhr;
};
function posA(url,data,cbk){
    var ar=new aR();
    ar.onreadystatechange=function(){
        if (ar.readyState==4){
            if (ar.status==200 || window.location.href.indexOf("http")==-1){
                var r;
                try{
                    r=JSON.parse(ar.responseText);
                }catch(e){console.log(e)}
                cbk(r);
            }
        }
    };
    ar.open("POST", url, true);
    ar.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ar.send(data)
};
function ppf(s){
    return encodeURIComponent(s)
}
function toLower(){
    var t=document.getElementsByTagName('textarea');
    var p;
    if(t[0].value){
        t[0].value=t[0].value.toLowerCase();
        p=$p(t[0],2);
        setTC(p);
    }
    if(t[1].value){
        t[1].value=t[1].value.toLowerCase();
        p=$p(t[1],4);
        setTC(p,1);
    }
}
var saveXHR;
function savAd(p, clr){
    if(saveXHR){
        saveXHR.abort();
        saveXHR=null;
    }
    if(!clr){
        ad.budget=0;
    }
    ad.extra=extra;
    if(cut.t<0)cut.t=0;
    ad.cut=cut;
    ad.cui=cui;
    ad.ro=pro;
    ad.pu=ppu;
    ad.se=pse;
    ad.rtl=artl;
    ad.altRtl=brtl;
    ad.lat=lat;
    ad.lon=lon;
    //setting pubTo
    var t={};
    for(var i in pc){
        t[i]=i;
    }
    ad.pubTo=t;
    //setting text if changed
    if(txt!=null){
        ad.other=txt;
    }
    if(atxt!=null){
        ad.altother=atxt;
    }
    //var dt='o='+ppf(JSON.stringify(ad));
    var dt={
        o:ad
    };
    var m;
    if(p){
        window.scrollTo(0,0);
        //dt+='&pub='+p;
        dt.pub=(p==-1 ? 0 : p);
        if(p==1 || p==-1){
            m=(lang=='ar'?'يرجى الإنتظار في حين يتم حفظ الإعلان':'Saving ad, please wait');
            dsl($n('main'),m,1);
        }
    }
    saveXHR = $.ajax({
        url:"/ajax-adsave/",
        data:dt,
        type:'POST',
        success:function(rp){
            if(rp.RP){
                atxt=null;
                txt=null;
                delete ad.other;
                delete ad.altother;
                var r=rp.DATA.I;
                ad.id=r.id;
                ad.user=r.user;
                ad.state=r.state;
                if(p==1){
                    m='<span class="done"></span>'+(lang=='ar'?'لقد تم حفظ الإعلان وتحويله للمراجعة من قبل محرري الموقع للموافقة والنشر، وسيتم ابلاغك برسالة على عنوان بريدك الإلكتروني فور نشره':'Your ad was successfully saved and is now pending administrator approval to be published');
                    dsl($n('main'),m,0,1);
                }else if (p==2){
                    document.location='/myads/'+(lang=='ar'?'':'en/')+'?sub=pending';
                }else if(p==-1){
                    m='<span class="done"></span>'+(lang=='ar'?'لقد تم حفظ الإعلان في لائحة المسودات للتعديل لاحقاً':'Your ad was successfully saved to your drafts for later editing');
                    dsl($n('main'),m,0,-1);
                }
            }else{
                if(p==1){
                    var m=(lang=='ar'?'فشل محرك مرجان بحفظ الإعلان، يرجى المحاولة مجدداً':'Mourjan system failed to save your ad, please try again');
                    dsl($n('main'),m,0,0,1);
                }
            }
        }
    });
}
function dsl(e,m,l,c,o){
    var d=$n('loader');
    if(d) d.innerHTML='';
    else d=document.createElement('div');
    d.className='loader '+(lang=='ar'?'':' lde');
    d.id='loader';
    var i=document.createElement('div');
    i.className='in';
    i.innerHTML=m;
    var b;
    if(l){
        b=document.createElement('div');
        b.className='load';
        i.appendChild(b)
    }
    if(o){
        b=document.createElement('input');
        b.type='button';
        b.className='bt sh rc';
        b.value=(lang=='ar'?'نشر الإعلان':'Publish Ad');
        b.onclick=function(){savAd(1)};
        i.appendChild(b);
        b=document.createElement('span');
        b.innerHTML=(lang=='ar'?'أو اعلامنا بالأمر في حال تكرار الفشل':'or let us know if the problem persists');
        i.appendChild(b);
        b=document.createElement('input');
        b.type='button';
        b.className='bt sh rc';
        b.value=(lang=='ar'?'تبليغ بوجود عطل':'Report Problem');
        b.onclick=function(){supp()};
        i.appendChild(b);
    }else if(c){
        if(c==-1){
            b=document.createElement('input');
            b.type='button';
            b.className='bt sh rc';
            b.value=(lang=='ar'?'تفقد لائحة المسودات':'View Drafts');
            b.onclick=function(){document.location='/myads/'+(lang=='ar'?'':'en/')+'?sub=drafts'};
            i.appendChild(b);
        }else{            
            b=document.createElement('input');
            b.type='button';
            b.className='bt sh rc';
            b.value=(lang=='ar'?'تفقد لائحة الإنتظار':'View Pending Ads');
            b.onclick=function(){document.location='/myads/'+(lang=='ar'?'':'en/')+'?sub=pending'};
            i.appendChild(b);
        }
        b=document.createElement('span');
        b.innerHTML=(lang=='ar'?'أو':'or');
        i.appendChild(b);
        b=document.createElement('input');
        b.type='button';
        b.className='bt sh rc';
        b.value=(lang=='ar'?'نشر إعلان آخر':'Post Another Ad');
        b.onclick=function(){document.location='?ad=new'};
        i.appendChild(b);
    }else if(!l){
        b=document.createElement('input');
        b.type='button';
        b.className='bt sh rc';
        b.value=(lang=='ar'?'متابعة':'continue');
        b.onclick=function(){document.location=HOME};
        i.appendChild(b)
    }
    
    d.appendChild(i);
    e.style.display='none';
    e.parentNode.insertBefore(d,e);
}
function supp(){
    var m=(lang=='ar'?'يتم إرسال بلاغك بالعطل، يرجى الإنتظار':'Sending your problem report, please wait');
    dsl($n('main'),m,1);
    $.ajax({
        url:'/ajax-support/',
        data:{lang:lang,obj:ad},
        type:'POST',
        success:function(rp){
            m=(lang=='ar'?'شكراً لك، لقد تم إرسال البلاغ وسيتم العمل على إصلاح العطل في أقرب وقت ممكن':'Thank you, the problem was reported and will be investigated as soon as possible');
            dsl($n('main'),m);
        }
    });
}
function tNext(e,s){
    var n=$a(e);
    if(s){
        while(n){
            if(!n.className.match(/off/)){
                fdT(n,s);
                if(!n.className.match(/pi/)) {
                    break;
                }
            }
            n=$a(n);
        }
        gto($a(e));
    }else{       
        while(n){
            fdT(n,s);
            n=$a(n);
        }
        if(e.id=='xct'){
            gto($b(e));
        }else{
            gto(e); 
        }
    }
}
function liC(e){
    var id=$p(e).id;
    switch(id){
        case 'rou':
            roC(e);
            break;
        case 'seu':
            seC(e);
            break;
        case 'puu':
            puC(e);
            break;
        default:
            break;
    }
}
function roC(e){
    var v=parseInt(e.getAttribute('val'));
    var p=$p(e);
    var ih=(e.className=='h'?1:0);
    var pu=$a(p);
    var se=$a(pu);
    var cn=$a(se);
    if(!ih && v && v!=pro){
        pro=v;
        var dif=0;
        if(ppro!=pro){
            ppro=pro;
            pse=0;
            ppu=0;
            dif=1;
        }
        switch(v){
            case 1:
            case 2:
            case 99:
                cnU(cn,0);
                break;
            case 3:
            case 4:
            default:
                cnU(cn,1);
                break;
        }
        liT(p,v);
        if(v==4){
            ppu=5;
            liT(pu);
            roP(pu);
            if(dif || !pse){
                lSE(v,se);
                liT(se,0);
            }else{
                liT(se,pse);
                if(pse)
                    cnT(cn,null,1);                
            }
            gto(se);
        }else{
            if(dif || !ppu){
                liT(pu,ppu,pro);
                roP(pu); 
                if(dif)
                    lSE(v,se);
            }else{
                roP(pu);       
                liT(pu,ppu,pro);
                if(ppu){
                    liT(se,pse);
                    if(pse)
                        cnT(cn,null,1);
                }
            }
            gto(pu);
        }
    }else {
        pro=0;
        liT(p,0);
        liT(pu);
        roP(pu);
        liT(se);
        cnT(cn,0);
        gto(p);
    }
}
function puC(e){
    var v=e.getAttribute('val');
    var r=e.getAttribute('ro');
    var p=$p(e);
    var ih=(e.className=='h'?1:0);
    var se=$a(p);
    var cn=$a(se);
    if(!ih && v && v!=ppu){
        ppu=v;
        roP(p);
        liT(p,v,r);
        liT(se,pse);
        if(pse)
            cnT(cn,null,1);
        else cnT(cn,0);
        gto(se);
    }else {
        ppu=0;
        liT(p,0);
        roP(p);
        liT(se);
        cnT(cn,0);
        gto(p);        
    }
}
function seC(e){
    var v=e.getAttribute('val');
    var p=$p(e);
    var ih=(e.className=='h'?1:0);
    var cn=$a(p);
    if(!ih && v && v!=pse){
        if(pse==748 || pse==766 || pse==223 || pse==924)cnU(cn,0);
        pse=v;
        if(pse==748 || pse==766 || pse==223 || pse==924)cnU(cn,1);
        liT(p,v);
        cnT(cn,null,1);
    }else {
        pse=0;
        liT(p,0);
        cnT(cn,0);
        gto(p);
    }
}
function cnH(e,s){
    var l=$cL(e);
    var x,y;
    x=$c(e,l-2);
    y=$c(e,l-1);
    if(s==0 && pcl){
        fdT(e,0,'pi');
        for (var j=1;j<l-2;j++){
            fdT($c(e,j),0);
        }  
        var k=0,tm='';
        for (var i in pc){
            if(pc[i]){
                if(k)tm+=' - ';
                tm+=pc[i];
                k++;
            }
        }
        pcl=k;
        x.innerHTML='<b class="ah">'+tm+'</b>'; 
        fdT(x,1);
        fdT(y,0);
        tNext(e,1);
        if(SAVE)savAd();
    }else {
        fdT(e,1,'pi');
        for (var j=1;j<l-2;j++){
            fdT($c(e,j),1);
        }  
        fdT(x,0);
        if(!e.className.match(/uno/))
            fdT(y,pcl);
        tNext(e,0);
    }
}
function cnT(e,s,f){
    if(s){
        cnH(e,s);
        fdT(e,s);
    }else if(s==0){     
        fdT(e,0);
        tNext(e,0);
    }else{
        if(f){
            fdT(e,1); 
            cnH(e,0);           
        }else{
            if(e.className.match(/pi/)) {
                s=1;
            }else {
                s=0;
            }
            cnH(e,s);
        }
    }
}
function lSE(r,n){
    if(ase[r]){
        rSE(ase[r],n);
    }else{
        n.innerHTML='<li class="h"><b>'+(lang=='ar'?'جاري التحميل...':'Loading...')+'</b></li><li><b class="load"></b></li>';
        var load_section = 1;
        if(STO){
            if(typeof(sessionStorage['postSe'+r])!=="undefined"){
                sessionStorage.removeItem('postSe'+r);
            }
            if(typeof(sessionStorage['postSe'+lang+r])!=="undefined"){
                ase[r]=JSON.parse(sessionStorage['postSe'+lang+r]);
                rSE(ase[r],n);
                load_section=0;
            }
        }
        if(load_section){
            $.ajax({
                url:'/ajax-post-se/',
                data:{
                    r:r,
                    lang:lang
                },
                type:'POST',
                success:function(rp){
                    if(rp.RP){
                        ase[r]=rp.DATA.s;
                        rSE(ase[r],n);
                        if(STO){
                            var s=JSON.stringify(ase[r]);
                            sessionStorage.setItem('postSe'+lang+r,s);
                        }
                    }
                }
            })
        }
    }
}
function rSE(s,n){
    n.innerHTML='<li class="h"><b>'+s.m+'<span class="et"></span></b></li>';
    var a=s.i;
    var l=a.length;
    var csp='';
    switch(pro){
        case 1:
            csp='x x';
            break;
        case 2:
            csp='z z';
            break;
        case 3:
            csp='v v';
            break;
        case 4:
            csp='y y';
            break;
        case 99:
            csp='u u';
            break;
    }
    for(var j=0;j<l;j++){
        n.innerHTML+='<li val="'+a[j][0]+'"><b><span class="'+csp+a[j][0]+'"></span>'+a[j][1]+'</b></li>';
    }
    iniC('seu');
}
function cnU(u,s){
    var m=u.className.match(/uno/);
    if(s){
        if(m)
            fdT(u,1,'uno');
    }else{
        if (!m)
            fdT(u,0,'uno');
    }
    if( (m && s) || (!m && !s)){
        pc=[];
        pcl=0;
        var n=$c(u);
        var l=$cL(u);
        var x,y,z;
        for (var j=1;j<l-2;j++){
            x=n[j];
            if($cL(x)>1){
                y=$c($c(x,1));
                z=y.length;
                for (var i=0;i<z;i++){
                    y[i].className='';
                }
            }else {
                x.className='';
            }
        }
    }
}
function cnC(e){
    var un=0;
    var p=$p(e);
    if(p.className=='sls')p=$p(p,2);
    var l=$cL(p);
    if(p.className.match(/uno/)){
        un=1;
        var x,m,n;
        for (var j=1;j<l-2;j++){
            x=$c(p,j);
            if($cL(x)>1){
                m=$c($c(x,1));
                n=m.length;
                for (var i=0;i<n;i++){
                    m[i].className='';
                }
            }else {
                x.className='';
            }
        }        
    }
    var v=e.getAttribute('val');
    if (e.className.match(/on/)){
        if (un){
            pc=[];
            pcl=0;
        }else {
            if(pc[v]) {
                delete pc[v];
                pcl--;
            }
        }
        fdT(e,1,'on');
    }else {
        if (un){
            pc=[];
            pc[v]=$f(e).innerHTML.replace(/<.*?>/g,'');
            pcl=1;
            cnT(p);
        }else {
            pc[v]=$f(e).innerHTML.replace(/<.*?>/g,'');
            pcl++;
        }
        fdT(e,0,'on');      
    }
    var x=$c(p,l-1);
    if(un){
        fdT(x,0);
    }else {
        fdT(x,pcl);        
    }
}
function liT(u,s,r){
    var c=$c(u);
    var l=$cL(u);
    if(s){
        var x;
        for(var i=0;i<l;i++){
            x=c[i].getAttribute('val');
            if(r && r!=c[i].getAttribute('ro'))x=0;
            c[i].className=(c[i].className=='h'? 'h':(x==s ? '':'hid'));
        }  
        fdT(u,0,'pi');
        fdT(u,1);
    }else if(s==0){
        fdT(u,1,'pi');
        for(var i=0;i<l;i++){
            c[i].className=(c[i].className=='h'? 'h':'');
        }
        fdT(u,1);
    }else {
        fdT(u,0);
    }
}
function roP(u){
    var v=pro;
    var c=$c(u);
    var l=$cL(u);
    var x,h;
    for(var i=0;i<l;i++){
        x=c[i].getAttribute('ro');
        h=c[i].className;
        c[i].className=(h=='h'?'h':(x==v ? '':'hid'));
    }
}
function pz(e,c,v){
    if(e.className=='alt'){
        rpz(e,1,1);
    }else{
        ccF=parseInt(e.getAttribute('val'));
        var p=$p(e);
        var n=$c(p);
        var k=$cL(p);
        for (var i=0;i<k;i++){
            n[i].className='hid';
        }
        e.className='alt';
        var d=$a($p(p));
        if(c) {
            var o=$f(d,2);
            o.innerHTML='<b>'+(ccv['n']?'<span class="cf c'+ccv['n']+'"></span>':'')+ccv[lang]+' <span class="pn">+'+ccv['c']+'</span><span class="et"></span></b>';
            fdT(o,1);
            if(!ccA){
                ldCN();  
            }
        }else {
            fdT($f(d,2),0);
        }
        var t=$f($c($f(d),1),2);
        if(edN!=null && cui.p[edN]) v=cui.p[edN]['r'];
        if(v)t.value=v;
        else t.value='';
        fdT(t,1,'err');
        fdT(d,1);
        gto($p(e,3));
    }
}
function ldCN(){
    var load=1;
    if(STO){
        if(typeof(localStorage.CC)!=="undefined"){
            localStorage.removeItem('CC');
        }
        if(typeof(localStorage['CC'+lang])!=="undefined"){
            localStorage.removeItem('CC'+lang);
        }
        if(typeof(sessionStorage['CC'+lang])!=="undefined"){
            ccA=JSON.parse(sessionStorage['CC'+lang]);
            ccB();
            load=0;
        }
    }
    if(load){
        $.ajax({
            url:'/ajax-code-list/',
            data:{lang:lang},
            type:'POST',
            success:function(rp){
            if(rp.RP){
                ccA=rp.DATA.l;
                ccB();
                var s=JSON.stringify(ccA);
                sessionStorage.setItem('CC'+lang,s);
            }
        }})
    }
}
function wpz(e){
    if(!ccA){
        ldCN();  
    }
    var u=$p(e);
    if(e.className!='h') 
        u=$p(u,2);
    if(u.className.match(/pi/)){
        fdT(u,1,'pi');
        tNext(u,0);
        gto(u);
    }else{
        var v=e.getAttribute('val');
        if(v!=null){
            var tv=v;
            var tedN=-1;
            var raw='';
            var hs=0;
            if(isNaN(v)){
                switch(v){
                    case 'e':
                        raw=cui[tv];
                        v=10;
                        break;
                    case 'b':
                        raw=cui[tv];
                        v=6;
                        break;
                    case 's':
                        raw=cui[tv];
                        v=11;
                        break;
                    case 't':
                        raw=cui[tv].replace(/@/g,'');
                        v=12;
                        break;
                    default:
                        v=null;
                        break;
                }
            }else {
                var k=ccA.length;
                var y;
                for(var i=0;i<k;i++){
                    y=ccA[i];
                    if(y[0]==cui.p[v].c) break;
                }
                setCC(y);
                raw=cui.p[v].r;
                v=cui.p[v].t;
                tedN=tv;
                hs=1;
            }
            var n=$c($f($c(u,1)));
            var l=n.length;
            var c;
            var x=v;
            for(var i=0;i<l;i++){
                if(n[i].getAttribute('val')==x){
                    c=n[i];
                    break;
                }
            }
            if(c){
                rpz(e,1);
                if(tedN>-1)edN=tedN;
                pz(c,hs,raw);
            }
        }
    }
}
function rpz(e,f,h){
    var p=$p(e,3);
    if(!f)p=$p(p,2);
    var u=$c($c(p,1),0);
    var c=$c(p);
    if(!h)edN=null;
    if(!f && pzv){        
        fdT(c[1],0);
        fdT(c[2],0);
        fdT(c[3],1);
    }else{
        fdT(c[2],0);
        var r=c[3];
        fdT(r,0);
        if($cL($f(r))==2){
            pzv=0;
        }else {
            pzv=1;
        }
        fdT(c[1],1);
        spz(u);
    }
    gto(p);
}
function spz(e){
    var n=$c(e);
    var k=$cL(e);
    for (var i=0;i<k-1;i++){
        n[i].className='';
    }
    if(pzv){
        n[i].className='';
    }else{
        n[i].className='hid';
    }
}
function pzc(e){
    ccN=e;
    var d=$n('main');
    d.style.display='none';
    var r;
    var p=$p(d);
    if(ccD){
        r=ccD;
        p.insertBefore(r,d);
    }else{
        r=document.createElement('DIV');
        r.className='ccd load';
        p.insertBefore(r,d);
        ccD=r;
    }
    window.scrollTo(0,0);
    ccB();
}
function npz(e){
    var u=$p(e,5);
    fdT(u,0,'pi');
    var c=$a(u);
    if(cui.p.length){
        fdT(c,1,'off');
    }else {
        fdT(c,0,'off');
    }
    setTC($a(u,2));
    setTC($a(u,4),1);
    tNext(u,1);
    if(SAVE)savAd();
}
function ccB(){
    if(ccD && ccD.innerHTML=='' && ccA){
        var ul=document.createElement('ul');
        var ul2=document.createElement('ul');
        ul.className='ls po hvr';
        ul2.className='ls po br hvr';
        var o;
        var r=ccA;
        var l=r.length;
        for(var i=0;i<l;i++){
            o=document.createElement('li');
            o.onclick=(function(y){return function(){setCC(y);}})(r[i]);
            if(r[i][3]){
                o.innerHTML='<b><span class="pn">+'+r[i][0]+'</span><span class="cf c'+r[i][1]+'"></span>'+r[i][2]+'</b>';
                ul.appendChild(o);
            }else{
                o.innerHTML='<b><span class="pn">+'+r[i][0]+'</span>'+r[i][2]+'</b>';
                ul2.appendChild(o);
            }
        }
        ccD.style.height='auto';
        ccD.innerHTML='<h2 class="ctr">'+(lang=='en'?'Choose Country Code':'إختر رمز البلد')+'</h2>';
        ccD.appendChild(ul);
        ccD.appendChild(ul2);
    }
}
function setCC(r){
    ccv={c:r[0],n:0,en:'',ar:'',i:r[4]};
    if(r[3])ccv['n']=r[1];
    ccv[lang]=r[2];
    if(!ccN)ccN=$n('CCodeLi');
    ccN.innerHTML='<b>'+(ccv['n']?'<span class="cf c'+ccv['n']+'"></span>':'')+ccv[lang]+' <span class="pn">+'+ccv['c']+'</span><span class="et"></span></b>';
    if(ccD && $p(ccD)){
        $p(ccD).removeChild(ccD);
        $n('main').style.display='block';
        gto($p(ccN,3));
    }
}
function savC(e){
    var p=$p(e,2);
    var t=$f($b(p),2);
    fdT(t,1,'err');
    var v=t.value;
    v=v.replace(/<.*?>/g,'');
    t.value=v;
    var x=0;
    var raw=0;
    if(v){
        switch(ccF){
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 7:
            case 8:
            case 9:
            case 13:
                v=v.replace(/[\(\)\-\/\\.\s*#_ـ]/g,'');
                v=v.replace(/^\+/,'00');
                v=v.replace(new RegExp("^00"+ccv.c,""),'');
                t.value=v;
                if(v.length>6)
                    x=v.match(/[^0-9]/);
                else x=1;
                raw=v;
                v='+'+ccv.c+parseInt(v,10);
                break;
            //bbm
            case 6:
                v=v.toUpperCase();
                t.value=v;
                if(v.length==8)
                    x=v.match(/[^a-zA-Z0-9]/);
                else x=1;
                break;
            //email
            case 10:
                v=v.toLowerCase();
                t.value=v;
                if(!v.match(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/))
                    x=1;
                break;
            //skype
            case 11:
            case 12:
            default:
                if(v.match(/ /))
                    x=1;
                break;
        }
    }else{
        x=1;
    }
    if(x)fdT(t,0,'err');
    else{
        switch(ccF){
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 7:
            case 8:
            case 9:
            case 13:
                var m=cui.p;
                var l=m.length;
                if(edN!=null){
                    var a=-1;
                    for(var i=0;i<l;i++){
                        if(i!=edN && v==m[i]['v']){
                            a=i;
                            break;
                        }
                    }
                    m[edN]={v:v,t:ccF,c:ccv.c,r:raw,i:ccv.i};
                    if(a>-1){
                        var tm=[],j=0;
                        for(var i=0;i<l;i++){
                            if(i!=a){
                                tm[j++]=m[i];
                            }
                        }
                        m=cui.p=tm;
                        l--;
                    }
                }else {
                    var a=1;
                    for(var i=0;i<l;i++){
                        if(v==m[i]['v']){
                            m[i]['t']=ccF;
                            a=0;
                            break;
                        }
                    }
                    if(a){
                        if(l===3) l=2;
                        m[l]={v:v,t:ccF,c:ccv.c,r:raw,i:ccv.i};
                    }
                }
                cui.p=m;
                break;
            //bbm
            case 6:
                cui['b']=v;
                break;
            //email
            case 10:
                cui['e']=v;
                break;
            //twitter
            case 12:                
                v=v.replace(/@/g,'');                
                v=v.replace(/(?:https:\/\/|http:\/\/|)twitter.com/,'');
                if(v[0]=='/'){
                    v=v.substring(1);
                }                
                t.value=v;
                cui['t']='@'+v;
                break;
            //skype
            case 11:
            default:
                cui['s']=v;
                break;
        }
        t.value='';
        p=$p(p,2);
        var d=$f($a(p));
        var n=$c(d);
        var l=$cL(d);
        var y=n[l-2].cloneNode(true);
        var z=n[l-1].cloneNode(true);
        d.innerHTML='';
        var m=cui.p;
        l=m.length;
        var o,s,g,k;
        var ttl='title="'+(lang=='ar'?'إزالة وسيلة الإتصال':'remove contact')+'"';
        for(var i=0;i<l;i++){
            g=m[i]['v'];
            k=m[i]['t'];
            if ((typeof PVC) !== 'undefined') {
                var rp=phoneNumberParser(m[i]['r'],m[i]['i'],m[i]['t']);
                if(rp.p){
                    if(rp.v){
                        if(rp.t){
                            if(lang=='ar')
                                g='Valid '+g;
                            else g+=' Valid';
                        }else{
                            if(lang=='ar')
                                g='TYPE ERROR '+g;
                            else g+=' TYPE ERROR';
                        }
                    }else {
                        if(lang=='ar')
                            g='INVALID '+g;
                        else g+=' INVALID';
                    }
                }else{
                    if(lang=='ar')
                        g=rp.e+' '+g;
                    else g+=' '+rp.e;
                }
            }
            s='<b>';
            if(k<6 || k==13){
                if((k>2 && k<6) || k==13)
                    s+='<span class="pz pz3"></span>';
                if(k==2 || k==4 || k==13)
                    s+='<span class="pz pz2"></span>';
                if(k<5)
                    s+='<span class="pz pz1"></span>';
            }else {
                if(k==7)
                    s+='<span class="pz pz5"></span>';
                else if(k==8 || k==9)
                    s+='<span class="pz pz6"></span>';
            }   
            s+='<span '+ttl+' class="pz pzd"></span>'+g;
            s+='</b>';
            _aliu(d,s,i);
        }
        if(cui.b){
            s='<b><span class="pz pz4"></span><span '+ttl+' class="pz pzd"></span>'+cui.b+'</b>';
            _aliu(d,s,'b');
        }
        if(cui.t){
            s='<b><span class="pz pz9"></span><span '+ttl+' class="pz pzd"></span>'+cui.t+'</b>';
            _aliu(d,s,'t');
        }
        if(cui.s){
            s='<b><span class="pz pz8"></span><span '+ttl+' class="pz pzd"></span>'+cui.s+'</b>';
            _aliu(d,s,'s');
        }
        if(cui.e){
            s='<b><span class="pz pz7"></span><span '+ttl+' class="pz pzd"></span>'+cui.e+'</b>';
            _aliu(d,s,'e');
        }
        d.appendChild(y);
        d.appendChild(z);
        fdT(p,0);
        fdT($b(p),0);
        fdT($p(d),1);
        edN=null;
        gto($p(d,2));
    }
}
function _aliu(u,s,v){
    var o=document.createElement('LI');
    o.setAttribute('val',v);
    o.className='pn';
    o.onclick=(function(e){return function(){wpz(e)}})(o);
    o.innerHTML=s;
    var z=$f(o);
    z=$c(z,($cL(z)-2));
    z.onclick=(function(w,e){return function(){delC(w,e)}})(v,z);
    u.appendChild(o);
}
function delC(w,e){
    se();
    if(isNaN(w)){
        cui[w]='';
    }else{
        var a=[],k=0;
        var n=cui.p;
        var l=n.length;
        for(var i=0;i<l;i++){
            if(w!=i){
                a[k++]=n[i];
            }
        }
        cui.p=a;
    }    
    var p=$p(e,2);
    var u=$p(p);
    u.removeChild(p);
    var l=$cL(u);
    var c=$c(u);
    var k=0,a;
    for (var i=0;i<l;i++){
        a=c[i].getAttribute('val');
        if(!isNaN(a)){
            c[i].setAttribute('val',k++);
        }
    }
    if(l==2){
        rpz($f(u),1);
    }    
}



function stm(e){
    var p=$p(e);
    tNext(p,0);
    if(e.className=='h'){
        if(!p.className.match(/ pi/)) return;
    }
    var n=$c(p);
    fdT(p,1,'pi');
    fdT(n[1],0);
    fdT(n[3],0);
    fdT(n[4],0);
    fdT(n[2],1);
    gto(p);
}
function savT(e,o){
    var u=$p(e,3);
    var n=$c(u);
    var f=$f(n[3]);
    var b=tmpT.b,a=tmpT.a,x='';
    if(tmpT.t==2){
        if(btwT==1 && b==a){ 
            var y=$c($f(n[2]),0);
            cut={t:0,b:24,a:6};
            btwT=0;
            n[1].innerHTML='<b>'+y.innerHTML.replace(/<.*?>/g,'')+'</b>';
            fdT(n[2],0);
            fdT(n[3],0);
            fdT(n[4],1);
        }else {
            if(btwT==0){
                x=' '+gttv(f,a);
                btwT=1;
                $f(n[1]).innerHTML+=x;
            }else{
                cut={t:tmpT.t,b:b,a:a};
                if (a>b){
                    var g=b;
                    b=a;
                    a=g;
                    cut.a=a;
                    cut.b=b;
                }
                x=$c($f(n[2]),2).innerHTML.replace(/<.*?>/g,'');
                x+=' '+gttv(f,a);
                x+=' '+(lang=='ar'?'و':'and')+' ';
                x+=gttv(f,b);
                btwT=0;
                $f(n[1]).innerHTML=x;
                cutS=x;
                fdT(n[2],0);
                fdT(n[3],0);
                fdT(n[4],1);
            }
        }
    }else{
        var t=tmpT.t;
        if(t==1){
            a=0;
            v=b;
        }else {
            b=0;
            v=a;
        }
        cut={t:t,b:b,a:a};
        x=' '+gttv(f,v);
        
        $f(n[1]).innerHTML+=x;
        cutS=$f(n[1]).innerHTML;
        fdT(n[2],0);
        fdT(n[3],0);
        fdT(n[4],1);
    }
    gto(u);
}
function ctm(e){
    var v=parseInt(e.getAttribute('val'));
    var t=tmpT.t;
    if(t==2){
        if(btwT==0)
            tmpT['a']=v;
        else tmpT['b']=v; 
        savT(e);
    }else if(t==1){
        tmpT.b=v;
        tmpT.a=0;
        savT(e);
    }else if(t==3){
        tmpT.a=v;
        tmpT.b=0;
        savT(e);
    }
}
function ttm(s,e){
    var x=$p(e,3);
    if(!s)x=$p(x,2);
    var n=$c(x);
    if(s){
        var t=s-1;
        n[1].innerHTML='<b>'+e.innerHTML.replace(/<.*?>/g,'')+'<span class="et"></span></b>';
        fdT(n[2],0);
        fdT(n[1],1);
        if(t==0){
            fdT(n[4],1);
            tmpT=cut={t:t,b:24,a:6};
            btwT=0;
            cutS=$f(n[1]).innerHTML;
        }else{
            tmpT={t:t,b:cut.b,a:cut.a};
            fdT(n[2],0);
            fdT(n[3],1);
        }
    }else{
        btwT=0;
        var y=$c($f(n[2]),cut.t);
        n[1].innerHTML='<b>'+cutS+'</b>';
        fdT(n[4],1);
        fdT(n[3],0);
        fdT(n[2],0);
        fdT(n[1],1);
    }
    gto(x);
}
function ntm(e){
    var p=$p(e,3);
    fdT(p,0,'pi');
    setTC($a(p));
    setTC($a(p,3),1);
    tNext(p,1);
    if(SAVE)savAd();
}
function gttv(u,v){
    var n=$c(u);
    for(var i=0;i<19;i++){
        if(v==n[i].getAttribute("val")){
            return $f(n[i]).innerHTML.replace(/<.*?>/g,'');
        }
    }
}


function initT(e){
    tar=e;
    tlen=$c($f($b($p(e))),1);
    var j=$(e);
    j.on('paste',function(){setTimeout('var k=getAP();rdrT();capk();if(k){setCP(tar,k,1);}',10)});
    j.on('keyup',function(){capk()});
    var l=$a($p(tar));
    hidTB=0;
    ctac=(tar.id == 'mText' ? 0:1);
    fdT($a(l),0);
    fdT(l,1);
}
function capk(){
    var e=tar;
    var m=maxC;
    var v=e.value.length;
    if (v>=m){
        e.onkeydown=function(e){
            var cd=(typeof e.ctrlKey !== 'undefined' && e.ctrlKey !== null ? e.ctrlKey : e.metaKey);
            var hs=0;
            if(document.selection!=undefined){
                var sel = document.selection.createRange();
                hs=sel.text.length;
            }else if(tar.selectionStart != undefined && tar.selectionStart!=tar.selectionEnd){
                hs=1;
            }
            if(
                    !hs &&
                    e.keyCode!=8 && 
                    e.keyCode!=46 && 
                    e.keyCode!=37 && 
                    e.keyCode!=38 && 
                    e.keyCode!=39 && 
                    e.keyCode!=34 && 
                    !(cd && (e.keyCode==86 || e.keyCode==88 || e.keyCode==65 || e.keyCode==67) ) )  
                return false;
        };
        if(v>m){
            var k=getAP();
            v=m;
            e.value=e.value.substr(0,m);
            if(k)setCP(tar,k,1);
        }
    }else{
        e.onkeydown=function(){};
    }
    tlen.innerHTML=v+' / '+m;
    idir(e);
    /*if(e.value.match(/[\u0621-\u064a\u0750-\u077f]/)){
        e.className='ar';
    }else{
        e.className='en';
    }*/
    setTimeout('tglTB('+v+');',100);
}
function getAP(){
    var k=0;
    if (tar.selectionStart) {
        k=tar.selectionStart;
    } else if (document.selection) {
        var c = "\001",
        sel = document.selection.createRange(),
        dul = sel.duplicate(),
        len = 0;
        dul.moveToElementText(tar);
        sel.text = c;
        len = dul.text.indexOf(c);
        sel.moveStart('character',-1);
        sel.text = "";
        k=len;
    }
    return k;
}
function tglTB(v){
    var l=$a($p(tar));
    var b=$f(l,2);
    var hasC=(ctac ? hasAT:hasT);
    if (v>=minC){
        if(!hasC){
            if(ctac) hasAT=1;
            else hasT=1;
            fdT(b,1,'off');
        }
    }else{
        if(hasC){
            if(ctac) hasAT=0;
            else hasT=0;
            fdT(b,0,'off');
        }
    }
    if(hidTB){        
        fdT($a(l),0);
        fdT(l,1);
    }
}
function hidNB(e){
    fdT(e,0);
    fdT($b(e),1);
}
function rdrT(){
    var e=tar;
    var irtl=0;
    var v=e.value;
    var l=e.value.length;
    var y,z;
    if(l>=minC){
        var r=v;
        //cleanup tag insertions
        r=r.replace(/<.*?>/g,'');
        //convert arabic numbers to english
        r=r.replace(/\u0660/g,0);
        r=r.replace(/\u0661/g,1);
        r=r.replace(/\u0662/g,2);
        r=r.replace(/\u0663/g,3);
        r=r.replace(/\u0664/g,4);
        r=r.replace(/\u0665/g,5);
        r=r.replace(/\u0666/g,6);
        r=r.replace(/\u0667/g,7);
        r=r.replace(/\u0668/g,8);
        r=r.replace(/\u0669/g,9);
        //check if ad is arabic
        irtl=(e.className=='ar'?1:0);
        //replace commas after numbers
        if(irtl){
            r=r.replace(/,/g,'،');
        }else {
            r=r.replace(/،/g,',');
        }
        //replace arabic commas between numbers
        r=r.replace(/([0-9])،([0-9])/g,'$1,$2');
        //cleanup email insertion
        r=r.replace(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/g,' ');
        //cleanup url insertion
        //r=r.replace(/\b((http(s?):\/\/)?([a-z0-9\-]+\.)+(MUSEUM|TRAVEL|AERO|ARPA|ASIA|EDU|GOV|MIL|MOBI|COOP|INFO|NAME|BIZ|CAT|COM|INT|JOBS|NET|ORG|PRO|TEL|A[CDEFGILMNOQRSTUWXZ]|B[ABDEFGHIJLMNORSTVWYZ]|C[ACDFGHIKLMNORUVXYZ]|D[EJKMOZ]|E[CEGHRSTU]|F[IJKMOR]|G[ABDEFGHILMNPQRSTUWY]|H[KMNRTU]|I[DELMNOQRST]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKRSTUVY]|M[ACDEFGHKLMNOPQRSTUVWXYZ]|N[ACEFGILOPRUZ]|OM|P[AEFGHKLMNRSTWY]|QA|R[EOSUW]|S[ABCDEGHIJKLMNORTUVYZ]|T[CDFGHJKLMNOPRTVWZ]|U[AGKMSYZ]|V[ACEGINU]|W[FS]|Y[ETU]|Z[AMW])(:[0-9]{1,5})?((\/([a-z0-9_\-\.~]*)*)?((\/)?\?[a-z0-9+_\-\.%=&amp;]*)?)?(#[a-zA-Z0-9!$&'()*+.=-_~:@/?]*)?)/gi,'');
        //cleanup @ joints
        r=r.replace(/[a-zA-Z0-9]*?@[a-zA-Z0-9]*?(?:\s|$)/g,'');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //cleanup long dash (madde) from arabic text
        r=r.replace(/([\u0600-\u06ff])\u0640+([\u0600-\u06ff])/g,'$1$2');
        
        //replace unwanted chars with no space
        r=r.replace(/[\u0600-\u060B\u060D-\u061a\u064b-\u065f\u06d6-\u06ed\ufe70-\ufe7f]/g,'');
        
        //cleanup unwanted chars with space
        r=r.replace(/[^\s0-9a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc!£¥$%&*()×—ـ\-_+=\[\]{}'",.;:|\/\\?؟؛،]/g,' ');
        //cleanup repeated chars
        r=r.replace(/([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc])\1{2,}/g,'$1');
        //r=r.replace(/([.,:;(){}|?!*&\%\-_=+~\[\]\\\/"'؟؛،])\1{1,}/g,'$1');
        r=r.replace(/([^a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc0-9])\1{1,}/g,'$1');
        //remove spaced patterns
        r=r.replace(/([^a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc0-9])\s\1{1,}/g,'$1 ');
        //unify parenthesis
        r=r.replace(/[{\[]/g,'(');
        r=r.replace(/[\]}]/g,')');
        //unify separators
        r=r.replace(/[_|—|ـ]/g,'-');
        //seperate numbers from text
        r=r.replace(/([0-9])([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})/g,'$1 $2');
        r=r.replace(/([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})([0-9])/g,'$1 $2');
        //remove space before and after parenthesis and seperators
        r=r.replace(/\s([\)+×\(\/\\\-])/g,'$1');
        r=r.replace(/([\(\)+×\/\\\-])\s/g,'$1');
        //surround seperators with space if not numeric        
        r=r.replace(/([^0-9])([\\\/\-])([0-9])/g,'$1 $2 $3');
        r=r.replace(/([0-9])([\\\/\-])([^0-9])/g,'$1 $2 $3');
        //precede parenthesis and seperators by outer space
        r=r.replace(/([^\s])([\(\\\/\-+])/g,'$1 $2');
        r=r.replace(/([\)\\\/\-+])([^\s])/g,'$1 $2');
        //add space after commas and periods if after is alphabet
        r=r.replace(/([?!:;,.؟؛،])([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})/g,'$1 $2');
        //add space after commas and periods if after is numerical
        r=r.replace(/([^0-9][?!:;؟؛])([0-9])/g,'$1 $2');
        //remove spaces preceeding commas, periods, etc
        r=r.replace(/\s([?!:;,.؟؛،])/g,'$1');
        //remove commas, periods, etc followed by special chars
        r=r.replace(/[.](?:\s|)([\(\)\-])/g,'$1');
        //remove commas, periods, etc preceeded by special chars
        r=r.replace(/([\(\)\-])[:;,.؛،]/g,'$1');
        //remove special chars that do not match ex: : -
        r=r.replace(/([:\(\)\\\/\-;.,؛،?!؟])(?:\s|)[:\\\/\-;.,؛،?!؟]/g,'$1');
        //cleanup phone insertion
        if(pse!=113 && pse!=291 && pse!=325){
            var m =Math.floor(r.length/2);
            y = r.substring(0,m);
            z = r.substring(m);
            z=z.replace(/([0-9])\s(?:(-)(?:\s|)|)([0-9])/g,'$1$2$3');
            z=z.replace(/(\+|)(?:\d{8,}|\d{2}[-\\\/]\d{6,})/g,' ');
            r=y+z;
        }
        //cleanup words repetition
        r=r.replace(/(^|\s)([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]*?)\s\2(\s|$)/g,'$1$2$3');
        //append el waw to text
        r=r.replace(/\sو\s([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc])/g,' و$1');
        //append el م to numbers
        r=r.replace(/([0-9,.])\sم\s/g,'$1م ');
        //seperate numbers from text
        r=r.replace(/([0-9]{2,})([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})/g,'$1 $2');
        r=r.replace(/([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})([0-9]{2,})/g,'$1 $2');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        //replace \ with /
        r=r.replace(/\\/g, '/');
        //remove unwanted string starters
        r=r.replace(/^[-_+=,.;:"'*|\/\\~؛،]/g,'');
        //remove unwanted string endings
        r=r.replace(/(?:[-_+=,.;:*|\/\\~؛،]|\sت|تلفون|هاتف|موبايل|جوال)$/g,'');
        //cleanup repeated spaces
        r=r.replace(/\(\)/g,' ');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        if(ctac) brtl=irtl;
        else artl=irtl;
        tglTB(r.length);
        if(e.value!=r){
            e.value=r;
            idir(e);
            rdrT()
        }else{
            y=r.replace(/[a-z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
            z=r.replace(/[A-Z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');            
            if(y.length > z.length*0.5){
                e.value = r.toLowerCase();
            }
        }
    }
}
function nxt(e,c){
    var p=$p(e,3);
    var n=$c(p);
    ctac=c;
    var hs=hasT;
    var tl=artl;
    if(c){
        hs=hasAT;
        tl=brtl;
    }
    if(hs){
        if(ctac){
            if(artl==brtl){
                hidTB=1;
                var q=$f(n[3]);
                if(lang=='ar'){
                    if(artl){
                        q.innerHTML='يرجى إدخال التفاصيل باللغة الإنجليزية فقط';
                    }else{
                        q.innerHTML='يرجى إدخال التفاصيل باللغة العربية فقط';
                    }
                }else{
                    if(artl){
                        q.innerHTML='The Description should be English only';
                    }else{
                        q.innerHTML='The Description should be Arabic only';
                    }
                }
                fdT(n[2],0);       
                fdT(n[3],1);
                return;
            }
        }
        var pv,b;
        if(ctac){            
            fdT(n[0],0);
            fdT(n[1],0);
            fdT(n[2],0);
            fdT(n[3],0);
            pv=n[4];
            b=$f(n[4]);
            tar=$f(n[1]);
            p=$p(p,2);
            extra['t']=1;
            //atxt=tar.value;
            var s=prepT(tar.value,brtl);
            atxt=s;
        }else {
            fdT(n[0],0);
            fdT(n[2],0);
            fdT(n[3],0);
            fdT(n[4],0);
            fdT(n[5],0);
            pv=n[6];
            b=$f(n[6]);
            tar=$f(n[3]);
            var h=$f($a(p,2),2);
            var s;
            if(lang=='ar'){
                if(artl)s='هل تريد إدخال تفاصيل الإعلان بالإنجليزية؟';
                else s='هل تريد إدخال تفاصيل الإعلان بالعربية؟';
            }else{
                if(artl)s='Do you want to enter Description in English?';
                else s='Do you want to enter Description in Arabic?';
            }
            h.innerHTML=s+'<span class="et"></span>';
            //txt=tar.value;
            var s=prepT(tar.value,artl);
            txt=s;
        }
        b.className=tl?'ah ar':'ah en';
        b.innerHTML=prepT(tar.value,tl);
        if(extra['t']==1 && !ctac){
            var tr=$a(p,2);
            fdT(tr,1,'pi');
        }
        fdT(pv,1);
        fdT(p,0,'pi');
        tNext(p,1);
        if(extra['t']==1 && !ctac){edOT($f(tr),1);}
        SAVE=1;
        savAd();
    }else {
        hidTB=1;
        if(ctac){
            var q=$f(n[3]);
            if(lang=='ar'){
                q.innerHTML='30 حرف هو الحد الادنى لنص الإعلان';
            }else{
                q.innerHTML='Minimum of 30 characters is required';    
            }
            fdT(n[2],0);       
            fdT(n[3],1);
        }else {
            fdT(n[4],0);       
            fdT(n[5],1);
        }
    }
}
function etxt(e){
    var p=$p(e);
    if(p.className.match(/pi/)){
        var n=$c(p);
        fdT(n[6],0);
        fdT(p,1,'pi');
        fdT(n[0],1);
        fdT(n[2],1);
        fdT(n[3],1);
        fdT(n[4],1);
        fdT(n[5],0);
        tNext(p,0);
        var t=$f(n[3]);
        setCP(t,t.value.length);
    }
}
function setCP(e,pos,s) {
    if(!e)e=tar;
    if(e.setSelectionRange){
        e.focus();
	e.setSelectionRange(pos,pos);
    }else if(e.createTextRange) {
        var range = e.createTextRange();
	range.collapse(true);
	range.moveEnd('character', pos);
	range.moveStart('character', pos);
	range.select();
    }
    if(!s)e.scrollTop=9999;
}
function setTC(e,alt){
    var tl,n,t,p;
    if(alt){
        tl=brtl;
        n=$c($f($c(e,1)));
        t=$f(n[1]);
        p=$f(n[4]);
    }else {
        tl=artl;
        n=$c(e);
        t=$f(n[3]);
        p=$f(n[6]);
    }
    if(t.value.length){
        var s=prepT(t.value,tl);
        p.innerHTML=s;
        if(alt)atxt=s;
        else txt=s;
    }
}
function parseDT(v,d){
    var n=v;
    if(d){
        if (n<12){
            n+=' صباحاً';
        }else if (n==12){
            n+=' ظهراً';
        }else if (n<16){
            n=(n-12)+' بعد الظهر';
        }else if (n<18){
            n=(n-12)+' عصراً';
        }else{
            n=(n-12)+' مساءً';
        }
    }else {
        if(n<12){
            n+=' AM';
        }else{
            n=(n>12 ? (n-12) : n)+' PM';
        }
    }
    return n;
}
function prepT(v,d){
    var l=cui.p.length;
    var t='';
    //v+='\u200B'+(d?' / للتواصل،':' / for contact and enquiries,');
    v+='\u200B / ';
    if(l){
        if(cut.t){
            cut.t=parseInt(cut.t);
            cut.b=parseInt(cut.b);
            cut.a=parseInt(cut.a);
            switch(cut.t){
                case 1:
                    v+=(d?' يرجى الإتصال قبل ':' please call before ')+parseDT(cut.b,d)+' -';
                    break;
                case 2:
                    if(d)
                        v+=' يرجى الإتصال بين '+parseDT(cut.a,d)+' و '+parseDT(cut.b,d)+' -';
                    else 
                        v+=' please call between '+parseDT(cut.a,d)+' and '+parseDT(cut.b,d)+' -';
                    break;
                case 3:
                    v+=(d?' يرجى الإتصال بعد ':' please call after ')+parseDT(cut.a,d)+' -';
                    break;
                default:
                    break;
            }
        }
        var s,g,k,r={};
        for(var i=0;i<l;i++){
            g=cui.p[i]['v'];
            k=parseInt(cui.p[i]['t']);
            s='';
            if(!r[k]){
                switch(k){
                    case 1:
                        s=(d?'موبايل':'Mobile');
                        break;
                    case 2:
                        s=(d?'موبايل + فايبر':'Mobile + Viber');
                        break;
                    case 3:
                        s=(d?'موبايل + واتساب':'Mobile + Whatsapp');
                        break;
                    case 4:
                        s=(d?'موبايل + فايبر + واتساب':'Mobile + Viber + Whatsapp');
                        break;
                    case 5:
                        s=(d?'واتساب فقط':'Whatsapp only');
                        break;
                    case 7:
                        s=(d?'هاتف':'Phone');
                        break;
                    case 8:
                        s=(d?'تلفاكس':'Telefax');
                        break;
                    case 9:
                        s=(d?'فاكس':'Fax');
                        break;
                    case 13:
                        s=(d?'فايبر + واتساب فقط':'Viber + Whatsapp only');
                        break;
                    default:
                        break;
                }
                r[k]={
                    s:' - '+s+': ',
                    c:0
                }
            }  
            if(r[k].c)
                r[k].s+=d?' او ':' or ';
            r[k].s+='<span class="pn o'+k+'">'+g+'</span>';
            r[k].c++;
        }
        for (var i in r){
            t+=r[i].s;
        }
    }
    if(cui.b){
        t+=' - '+(d?'بلاكبيري مسنجر':'BBM pin')+': <span class="pn ob">'+cui.b+'</span>';
    }
    if(cui.t){
        t+=' - '+(d?'تويتر':'Twitter')+': <span class="pn ot">'+cui.t+'</span>';
    }
    if(cui.s){
        t+=' - '+(d?'سكايب':'Skype')+': <span class="pn os">'+cui.s+'</span>';
    }
    if(cui.e){
        t+=' - '+(d?'البريد الإلكتروني':'Email')+': <span class="pn oe">'+cui.e+'</span>';
    }
    v+=t.substr(2);
    return v;
}
function edOT(e,fl){
    var p=$p(e);
    if(p.id!='xct')p=$p(p,2);
    var n=$c($f($c(p,1)));
    fdT(n[4],0);
    fdT(n[3],0);
    fdT(n[0],1);
    fdT(n[1],1);
    fdT(n[2],1);
    if(fl || p.className.match(/pi/)) {  
        edO(e,p);        
    }
    var t=$f(n[1]);
    setCP(t,t.value.length);
}
function edOP(e,fl){
    var p=$p(e);
    if(p.id!='xpc')p=$p(p,2);
    if(fl || p.className.match(/pi/)) {
        var n=$c(p);
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],0);
        fdT(p,1,'pi');
        tNext(p,0); 
    }
}
function edO(e,p){  
    var n=$c(p);
    fdT(p,1,'pi');
    fdT(n[3],0);
    fdT(n[2],0);
    fdT(n[1],1);
    tNext(p,0);
}
function noO(e,xI){
    var p=$p(e,3);
    var n=$c(p);
    fdT(p,0,'pi');
    extra[xI]=2;
    fdT(n[1],0);
    fdT(n[2],0);
    fdT(n[3],1);
    tNext(p,1);
    if(SAVE)savAd()
}
function xcnl(e){
    var p=$p(e,5);
    var n=$c(p);
    fdT(n[1],0);
    if(extra['t']==2){
        fdT(n[2],0);
        fdT(n[3],1);
        fdT(p,0,'pi');
        tNext(p,1);
    }else{
        fdT(n[3],0);
        fdT(n[2],1);        
    }
}
/*
function noUp(e){
    var u,n;
    if(uplp){
        document.getElementById('upForm').src='/web/blank.html';
        uplp=0;
    }
    if(picL){
        u=$p(e,6);
        n=$c(u);
        fdT(n[4],0);
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],1);
    }else{
        if(extra.p==2){
            u=$p(e,5);
            noO(u,'p');
        }else{
            u=$p(e,8);
            n=$c(u);
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
    fdT($b(e),0,'off');
    $p(e,4).reset();
}
*/

function dataURLtoBlob(dataURL,typo) { 
  var binary = atob(dataURL.split(',')[1]);
  var array = [];
  for(var i = 0; i < binary.length; i++) {
      array.push(binary.charCodeAt(i));
  }
  return new Blob([new Uint8Array(array)],{type:typo});
}

var uprog,uproh,uproi,perr=0,uplp=0,idata,itype;
function hasCanvas(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}
var hasC = hasCanvas();
var canA = ((typeof Blob !== "undefined") && (typeof FormData !== "undefined"));
if (!(hasC && canA)){
    $('#picB')[0].multiple=false;
}
$('#picB').on('change',function(e){set2File(this)});
document.domain = 'mourjan.com';

/*
function uprof(size){
    var m;
    perr=1;
    if(uplp){
        document.getElementById('upForm').src='/web/blank.html';
        uplp=0;
    }
    if(size){
        m = lang=='ar' ? 'فشلت عملية رفع الصورة لكون حجم الملف كبير جداً، <span class="lnk" onclick="uForm.reset();reUp()">انقر(ي) هنا</span> لرفع صورة اصغر حجماً':'upload failed because the image size is too large, <span class="lnk" onclick="uForm.reset();reUp()">click here</a> to upload a smaller image';
    }else{
        m = lang=='ar' ? 'فشلت عملية رفع الصورة، <span class="lnk" onclick="reUp()">انقر(ي) هنا</span> للمحاولة مجدداً':'upload failed, <span class="lnk" onclick="reUp()">click here</a> to try again';
    }
    var n=$c($f(uForm));
    fdT(n[2],0,'liw');
    n[2].innerHTML='<b class="ah">'+m+'</b>';        
}
function dpic(e){    
    var p=$p(e,5);
    var n=$c(p);
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
}
function setFile(e){
    e.nextSibling.style.display='none';
    var o=$a($p(e,2));
    var b=$f($a(o,2),2);
    if(e.value){
        var v=e.value;
        if(v.match(/\.(?:png|jpg|jpeg|gif)$/i)){
            fdT(b,1,'off');
            fdT(o,0);
        }else{
            fdT(b,0,'off');
            fdT(o,1);
        }
    }else{
        fdT(b,0,'off');
        fdT(o,0);
    }
}
function formatFileSize(bytes){
    if(bytes){
        if(parseInt(bytes / 1048576)){
            return parseFloat(Math.round((bytes / 1048576) * 100) / 100).toFixed(2)+"mb";
        }else if(parseInt(bytes / 1024)){
            return parseFloat(Math.round((bytes / 1024) * 100) / 100).toFixed(2)+"kb";
        }else{
            return bytes+"bytes";
        }
    }
    return "0kb";
}

function upPic(e){
    uForm=e;
    var b=$f($c($f(e),3),2);
    if(!b.className.match(/off/g)){     
        var u=$f(e);
        var n=$c(u);
        fdT(n[0],0);
        fdT(n[1],0);
        fdT(b,0,'off');
        fdT(n[2],1,'liw');
        var st= (lang =='ar'?'جاري الرفع':'uploading');
        
        
        n[2].innerHTML='<b class="ctr h_37"><span id="uproh">'+st+' <span id="uprog">0%</span></span></b>';
        uprog=$('#uprog',n[2]);
        uproh=$('#uproh',n[2]);
        
        fdT(n[2],1);
        FLK=0;
        
        
        var uuid = UID;
        for (var i = 0; i < 32; i++) {
         uuid += Math.floor(Math.random() * 16).toString(16);
        }
        
        if((typeof Blob !== "undefined") && (typeof FormData !== "undefined") && idata){
            var f= dataURLtoBlob(idata);
            var fd = new FormData();
            fd.append('pic',f);
            var t=itype.replace(rg,'');
            $.ajax({
               url:UP_URL+'/x-upload/?X-Progress-ID='+uuid+'&t='+t,
               type: "POST",
               data: fd,
               processData: false,
               contentType: false,
               success:function(rp){
                   if(rp=='0')
                    uploadCallback('');
                   else uploadCallback(rp);
               },error:function(rp){
                   uploadCallback();
               }
            });
        }else{
            uForm.action = UP_URL+'/x-upload/?X-Progress-ID='+uuid;
            uplp=1;
            uForm.submit();
        }
        uproi = window.setInterval(
            function () {
              upro(uuid);
            },
            1000
        );
            
        FLK=1;
    }
}
*/


var uptimers={};
function checkUploadLock(n){
    var len=0;
    for (var i in uptimers){
        len++;
    }
    if(len==0){
        FLK=1;
        var btn=$f($a(n),2);
        if(picL<5){
            $(btn).removeClass('hid');
        }
    }
}
function uproF(uid,uog,uoh){
    var w = 0;
    console.log(uog);
    console.log(uoh);

    $.ajax({
        url:UP_URL+'/upload/progress.php',
        data:{
            UPLOAD_IDENTIFIER:1,
            s:USID},
        type:'POST',
        success:function(rp){
            rp = eval(rp);
            if(rp.state == 'uploading'){
                w = Math.floor(100 * rp.received / rp.size);
                uog.html(w+'%');
                uoh.css('background-size',w+'% 100%');
            }else if(rp.state == 'done'){
            }else if (rp.state == 'error') {
                //window.clearTimeout(uptimers[uid]);
                clearInterval(uptimers[uid]);
                delete uptimers[uid];
                checkUploadLock($p(uoh[0],5));
                if(rp.status==413){
                    uproFail(1,uoh,uog);
                }else{
                    uproFail(0,uoh,uog);
                }
            }
        },
        error:function(rp){
            uproFail(0,uoh,uog);
            //window.clearTimeout(uptimers[uid]);
            clearInterval(uptimers[uid]);
            delete uptimers[uid];
            checkUploadLock($p(uoh[0],5));
        }
    });
}
function uproFail(size,uoh,uog){
    var m;
    if(uplp){
        $('#upForm').src='/web/blank.html';
        uplp=0;
    }
    if(size){
        m = lang=='ar' ? 'فشلت عملية رفع الصورة لكون حجم الملف كبير جداًً':'Upload failed because the image size is too large';
    }else{
        m = lang=='ar' ? 'فشلت عملية رفع الصورة':'Image upload failed';
    }      
    $($p(uoh[0],2)).addClass('error');
    $p(uoh[0]).innerHTML=m;
}
function renderImage(rp,li){
    li.innerHTML='<b class="ah ctr"><span title="'+(lang=='ar'?'إزالة الصورة':'remove picture')+'" class="pz pzd"></span><img src="'+isrc+'d/'+rp+'"/></b>';
    var s=$f(li,2);
    s.onclick=(function(i,e){return function(){delP(i,e)}})(rp,s);
}
var imgCounter=1;
function uploadFile(data,type,prog){
    var uuid = UID;
    for (var i = 0; i < 32; i++) {
        uuid += Math.floor(Math.random() * 16).toString(16);
    }
    uuid+=new Date().getTime()+imgCounter;
    var uprog=$('.uprog',prog);
    var uproh=$('.uproh',prog);
    var f= dataURLtoBlob(data,type);
    var fd = new FormData();
    //fd.append('upload_progress_PHP_SESSION_UPLOAD_PROGRESS',1);
    fd.append('UPLOAD_IDENTIFIER',1);
    fd.append('pic',f);
    var rg=new RegExp('.*/');
    var t=type.replace(rg,'');
    $.ajax({
       url:UP_URL+'/upload/?t='+t+'&s='+USID,
       type: "POST",
       data: fd,
       processData: false,
       contentType: false,
       success:function(rp){
           if(rp && rp!='0'){
               uploadCB(rp,$p(prog,2),uuid);
           }else{
            uproFail(0,uproh,uprog);   
           clearInterval(uptimers[uuid]);
           delete uptimers[uuid];
           checkUploadLock($p($p(prog,2),2));
           }
           //if(rp=='0') uploadCB('',$prog);
       },error:function(rp){
           uproFail(0,uproh,uprog);  
           clearInterval(uptimers[uuid]);
           delete uptimers[uuid];
           checkUploadLock($p($p(prog,2),2));
           //uploadCB('',prog);
       }
    });
    
    uptimers[uuid] = window.setInterval(
        function () {
            uproF(uuid,uprog,uproh);
        },
        1000
    );
    imgCounter++;
}
var curLi,curUid,iframeIdx=0;
function setFileRow(tul,type){
    var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"><img class="nopic" src="'+ucss+'/i/photo.png" /></li><li class="li2"></li></ul></li>');
    curLi=li[0];
    var cols = $('ul li',li);
    tul.appendChild(li[0]);
    var st= (lang =='ar'?'جاري الرفع':'uploading');
    cols[1].innerHTML = '<span class="uproh">'+st+' <span class="uprog">0%</span></span>';
    
    var uuid = UID;
    for (var i = 0; i < 32; i++) {
        uuid += Math.floor(Math.random() * 16).toString(16);
    }
    uuid+=new Date().getTime()+imgCounter;
    curUid = uuid;
    var uprog=$('.uprog',cols[1]);
    var uproh=$('.uproh',cols[1]);
    if(!uForm){
        uForm = document.getElementById('picF');
    }
    
    uForm.action = UP_URL+'/upload/?s='+USID;
    uplp=1;
    uForm.submit();
    
    uptimers[uuid] = window.setInterval(
        function () {
            uproF(uuid,uprog,uproh);
        },
        1000
    );
    imgCounter++;
}
function uploadCB(rp,li,uid){
    if(!rp)rp='';
    if(rp && rp != "0"){
        picL++;
        $c($f($p(li),2),1).innerHTML=picL+' / '+5;
        renderImage(rp,li);
        clearInterval(uptimers[uid]);
        delete uptimers[uid];
        checkUploadLock($p(li,2));
    }else{
        var uprog=$('.uprog',li);
        var uproh=$('.uproh',li);
        uproFail(0,uproh,uprog);  
        clearInterval(uptimers[uid]);
        delete uptimers[uid];
        checkUploadLock($p(li,2));
    }
}
function uploadCallback(rp){
    if(!rp)rp='';
    if(rp && rp != "0"){
        picL++;
        $c($f($p(curLi),2),1).innerHTML=picL+' / '+5;
        renderImage(rp,curLi);
        clearInterval(uptimers[curUid]);
        delete uptimers[curUid];
        checkUploadLock($p(curLi,2));
    }else{
        var uprog=$('.uprog',curLi);
        var uproh=$('.uproh',curLi);
        uproFail(0,uproh,uprog);  
        clearInterval(uptimers[curUid]);
        delete uptimers[curUid];
        checkUploadLock($p(curLi,2));
    }
}
function setFileCanvasRow(file,tul,submit){    
    var opt={maxWidth:650,canvas:true};
    loadImage.parseMetaData(file,function(dt){
        if (dt.exif) {
            opt.orientation = dt.exif.get('Orientation');
        }
        var img = loadImage(
            file,
            function(ig){                
                var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"></li><li class="li2"></li></ul></li>');
                var cols = $('ul li',li);
                tul.appendChild(li[0]);
                var durl = ig.toDataURL(file.type);
                loadImage(
                    durl,
                    function(it){
                        cols[0].appendChild(it);
                    },
                    {maxWidth:190,maxHeight:140,canvas:true}
                );
                if(ig.width < 200 || ig.height < 100){
                    $(cols[0].parentNode).addClass("error");
                    cols[1].innerHTML = (lang == "ar" ? "لم يتم رفع الصورة، يرجى رفع صورة بعرض لا يقل عن 200px وطول لا يقل عن 100px":"Image upload aborted. Please upload an image that has a minimum width of 200px and a minimum height of 100px");
                    checkUploadLock($p(li[0],2));
                }else{
                    var st= (lang =='ar'?'جاري الرفع':'uploading');
                    cols[1].innerHTML = '<span class="uproh">'+st+' <span class="uprog">0%</span></span>';
                        uploadFile(durl,file.type,cols[1]);
                }
            },
            opt
        );
    });
}
/*function setFormCanvasRow(file,tul,submit){    
    var opt={maxWidth:650,canvas:true};
    loadImage.parseMetaData(file,function(dt){
        if (dt.exif) {
            opt.orientation = dt.exif.get('Orientation');
        }
        var img = loadImage(
            file,
            function(ig){                
                var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"></li><li class="li2"></li></ul></li>');
                curLi = li;
                var cols = $('ul li',li);
                tul.appendChild(li[0]);
                var durl = ig.toDataURL(file.type);
                loadImage(
                    durl,
                    function(it){
                        cols[0].appendChild(it);
                    },
                    {maxWidth:190,maxHeight:140,canvas:true}
                );
                if(ig.width < 200 || ig.height < 100){
                    $(cols[0].parentNode).addClass("error");
                    cols[1].innerHTML = (lang == "ar" ? "لم يتم رفع الصورة، يرجى رفع صورة بعرض لا يقل عن 200px وطول لا يقل عن 100px":"Image upload aborted. Please upload an image that has a minimum width of 200px and a minimum height of 100px");
                }else{
                    var st= (lang =='ar'?'جاري الرفع':'uploading');
                    cols[1].innerHTML = '<span class="uproh">'+st+' <span class="uprog">0%</span></span>';
                    
                    var uuid = UID;
                    for (var i = 0; i < 32; i++) {
                        uuid += Math.floor(Math.random() * 16).toString(16);
                    }
                    uuid+=new Date().getTime()+imgCounter;
                    curUid = uuid;
                    var uprog=$('.uprog',cols[1]);
                    var uproh=$('.uproh',cols[1]);
                    if(!uForm){
                        uForm = document.getElementById('picF');
                    }

                    uForm.action = UP_URL+'/x-upload/?X-Progress-ID='+uuid;
                    uplp=1;
                    uForm.submit();

                    uptimers[uuid] = window.setInterval(
                        function () {
                            uproF(uuid,uprog,uproh);
                        },
                        1000
                    );
                    imgCounter++;
                    
                }
            },
            opt
        );
    });
}*/
function noPO(e){
    var p=$p(e,3);
    var n=$c(p);
    if(picL>0){
        extra['p']=1;
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],0);
    }else {
        extra['p']=2;
        fdT(n[1],0);
        fdT(n[2],0);
        fdT(n[3],1);
    }
    fdT(p,0,'pi');
    tNext(p,1);
    if(SAVE)savAd()
}
function set2File(e){
    if(FLK){
        idata=null;
        FLK = 0;
        var fo=$($p(e,2));
        fo.addClass('hid');
        
        var p = $p(e,4);
        var tul = $f($b(p));
        var t=$('#noPBT',$p(fo[0]));
        t.removeClass('cl');
        t.html(lang == 'ar' ? 'التالي':'Next');
        if(typeof e.files !== "undefined" && e.files.length){
            
            pass=0;
            var j=e.files.length;
            if(j > 5-picL){
                j=5-picL;
            }
            for(var i=0; i<j; i++){
                var f=e.files[i];
                if(f.type.match(/image\/(?:png|jpg|jpeg|gif)$/i)){
                    pass=1;
                    if(canA && hasC){
                        setFileCanvasRow(f,tul);
                    }else{             
                        setFileRow(tul,f.type);
                    }
                }
            }
            if(!pass){
                fo.removeClass('hid');
            }
        }else{
            var matches=null;
            if(matches = e.value.match(/\.(png|jpg|jpeg|gif)$/i)){
                setFileRow(tul,matches[1]);
            }
        }
        fo[0].reset();
    }
}




function delP(id,e){
    se();
    var m=(lang=='en' ? 'Delete this image?':'حذف هذه الصورة؟');
    if(confirm(m)){
        var li=$p(e,2);
        fdT(li,0);
        //var t=$p(li,2);
        var btd=$a($p(li,2));
        var b=$c($f($p(li),2),1);
        $.ajax({
            url:'/ajax-idel/',
            data:{i:id},
            type:'POST',
            success:function(rp){
                if(rp.RP){
                    picL--;
                    b.innerHTML=picL+' / '+5;
                    //fdT(p,1);
                    $p(li).removeChild(li);
                    if(!picL){
                        var t=$('#noPBT',btd);
                        t.addClass('cl');
                        t.html(lang == 'ar' ? 'كلا':'No');
                    }
                    checkUploadLock($b(btd));
                    /*if(!picL){
                        edOP(t,1);
                    }*/
                }else{
                    fdT(li,1);
                }
            },
            error:function(){
                fdT(li,1);
            }
        });
    }
}
function reUp(){
    if(uForm){
        perr=0;
        var e=uForm;
        var n=$c($f(uForm));
        var b=$f($c($f(e),3),2);
        fdT(b,1,'off');
        fdT(n[0],1);
        fdT(n[1],0);
        fdT(n[2],0);
    }
}





function edOV(e,fl){
    var p=$p(e);
    if(p.id!='xvd')p=$p(p,2);
    if(fl || p.className.match(/pi/)) { 
        var n=$c($f($c(p,1)));
        if(hasVd){
            n[3].innerHTML=tvl;
            fdT(n[2],0); 
            fdT(n[4],0);
            fdT(n[5],0);
            fdT(n[0],1);
            fdT(n[1],1);              
            fdT(n[3],1);
            fdT(n[6],1);
        }else{        
            fdT(n[3],0);
            fdT(n[4],0);
            fdT(n[5],0);
            fdT(n[6],0);
            fdT(n[0],1);
            fdT(n[1],1);
            fdT(n[2],1);
        }
        edO(e,p);    
    }
}
function shV(e,f){
    var p=$p(e);
    var c=$c(p);
    fdT(c[0],0);
    fdT(c[1],0);
    fdT(c[2],0);
    fdT(c[3],0);
    fdT(c[6],0);
    if(f){ 
        c[5].innerHTML='<b class="load"></b>';
        fdT(c[4],0);
        fdT(c[5],1);
        $.ajax({
            url:'/ajax-video-upload/',
            data:{
                action:0,
                lang:lang
            },
            type:'POST',
            success:function(rp){
                if(rp.RP){
                    c[5].innerHTML=rp.DATA.form;
                }else{                
                    fdT(c[0],1);
                    fdT(c[1],1);
                    fdT(c[2],1);
                    fdT(c[3],0);
                    fdT(c[4],0);
                    fdT(c[5],0);
                    fdT(c[6],0);
                }
            },
            error:function(){                
                fdT(c[0],1);
                fdT(c[1],1);
                fdT(c[2],1);
                fdT(c[3],0);
                fdT(c[4],0);
                fdT(c[5],0);
                fdT(c[6],0);
            }
        });
    }else{
        fdT(c[5],0);
        fdT(c[4],1);
    }
}
function linkVd(e){
    var f=$f($b($p(e,2),2),2);
    var v=f.value;
    var m;
    try{
        var re=new RegExp("(?:youtube\.com|youtu\.be).*?v=(.*?)(?:$|&)","gi");
        m=re.exec(v);
    }catch(g){}
    if (m==null || !m[1]){
        fdT(f,0,'err');
    }else {
        var c=$c($p(e,3));
        fdT(c[0],0);
        var n=c[1];
        fdT(n,1,'liw');
        n.innerHTML='<b class="load h_49"></b>';
        fdT(n,1);
        $.ajax({
            url:'/ajax-video-link/',
            data:{lang:lang,id:m[1]},
            type:'POST',
            success:function(rp){
                if (rp.RP) {
                   hasVd=1;
                   var u=$p(e,5);
                   var k=$c(u);
                   k[3].innerHTML=rp.DATA.video;
                   fdT(k[4],0);
                   fdT(k[5],0);
                   fdT(k[0],1);
                   fdT(k[1],1);
                   fdT(k[3],1);
                   fdT(k[6],1);
                   fdT(n,0);
                   fdT(c[0],1);
                   f.value='';
                   //$(".play",vl).colorbox({iframe:true, innerWidth:430, innerHeight:250});
                }else {
                   hasVd=0;
                   n.innerHTML='<b class="ah">'+rp.MSG+'</b>';
                   fdT(n,0,'liw');
                   fdT(c[1],1);
                }
            }
        });
    }
}
function delV(e){
    se();
    var m=(lang=='en' ? 'Delete this video?':'حذف هذا الفيديو؟');
    if (confirm(m)){
        var u=$p(e,3);
        var c=$c(u);
        fdT(c[3],0);
        fdT(c[6],0);
        fdT(c[2],1);
        $.ajax({
            url:'/ajax-video-delete/',
            type:'POST',
            success:function(rp){
                if (rp.RP) {
                    hasVd=0;
                }
            }
        });
    }
}
function cVUp(e,s){
    var u=$p(e,6);
    if(s)u=$p(u);
    edOV(u,1);
    if(s){
        $p(e,4).reset();
        fdT($b(e),0,'off');
    }else $f($b($p(e,2),2),2).value='';
}
function noVUp(e,s){
    var u,n;
    u=$p(e,3);
    n=$c(u);
    if(hasVd){
    }else{
        if(extra.v==2){
            noO($f(u),'v');
        }else{
            u=$p(u,2);
            n=$c(u);
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
}
function setVideo(e){
    var o=$a($p(e,2));
    var b=$f($a(o,2),2);
    if(e.value){
        var v=e.value;
        if(v.match(/\.(?:mov|mpeg4|avi|wmv|mpegps|flv|3gpp|webm|mp4|mpeg|3gp)$/i)){
            fdT(b,1,'off');
            fdT(o,0);
        }else{
            fdT(b,0,'off');
            fdT(o,1);
        }
    }else{
        fdT(b,0,'off');
        fdT(o,0);
    }
}

function upVid(e){
    vForm=e;
    var b=$f($c($f(e),3),2);
    if(!b.className.match(/off/g)){     
        var u=$f(e);
        var n=$c(u);
        fdT(n[0],0);
        fdT(n[1],0);
        fdT(b,0,'off');
        fdT(n[2],1,'liw');
        n[2].innerHTML='<b class="load h_43"></b>';
        fdT(n[2],1);
        FLK=0;
        vForm.submit();
        FLK=1;
    }
}
function updVd(ok,res){
    uploaded=1;
    if (ok) {
        hasVd=1;
        setTimeout('updCK("'+res+'");',5000);
    }else {
        hasVd=0;
        var c=$c($f(vForm));
        fdT(c[2],0,'liw');
        c[2].innerHTML=res;
    }
}
function updCK(res){
    $.ajax({
        url:'/ajax-upload-check/',
        data:{lang:lang},
        type:'POST',
        success:function(rp){
            if (rp.RP) {
                if(rp.DATA.P) {                                
                    setTimeout('updCK("'+res+'");',5000);
                }else {
                    hasVd=1;
                    var u=$p(vForm,2);
                    var k=$c(u);
                    k[3].innerHTML=rp.DATA.video;
                    fdT(k[4],0);
                    fdT(k[5],0);
                    fdT(k[0],1);
                    fdT(k[1],1);
                    fdT(k[3],1);
                    fdT(k[6],1);
                    vForm.reset();
                    fdT($f($c($f(vForm),3),2),0,'off');
                }
            }else {
                hasVd=0;
                var c=$c($f(vForm));
                fdT(c[2],0,'liw');
                c[2].innerHTML=rp.MSG;
            }
        }
    });
}
function dvid(e){    
    var p=$p(e,5);
    var n=$c(p);
    if(hasVd){
        var d=$c($f(n[1]),3);
        var a=$f(d);
        tvl=d.innerHTML;
        d.innerHTML='<b class="ctr ah">'+a.innerHTML+'</b>';
    }
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
}




function edOM(e,fl){
    var p=$p(e);
    if(p.id!='xmp')p=$p(p,2);
    mpU=p;
    if(p.className.match(/pi/)) {
        if(!hasM){
            initMap();
        }
        edO(e,p);    
    }else{
        if(fl){
            initMap();
        }
    }
}
function initMap(){
    window.scrollTo(0,0);
    var lm=0;
    var c;
    lat=ad.lat;
    lon=ad.lon;
    if(!mapD){
        var s1,s2,s3;
        if(lang=='ar'){
            s1='أضف';
            s2='إلغاء';
            s3='بلد، مدينة، شارع';
        }else{
            s1='Add';
            s2='Cancel';
            s3='Country, City, Street';
        }
        mapD=document.createElement('ul');
        mapD.className='ls po mapD nsh';
        mapD.innerHTML='<li class="rct"><form onsubmit="mapSrch(this);return false;"><span title="'+(lang=='ar'?'استخدام موقعي':'use my location')+'" onclick="myLoc(1)" class="pz pzl"></span><div class="ipt"><input onfocus="fdT(this,1,\'err\')" class="qi" type="text" placeholder="'+s3+'" /></div><input type="submit" onclick="if(this.previousSibling.firstChild.value!=\'\')return true;else return false" class="qb" value=""></li><li class="map load"></li><li><b class="ah ctr act2"><input onclick="savLoc(this)" class="bt ok off" type="button" value="'+s1+'" /><span onclick="clMap()" class="bt cl">'+s2+'</span></b></form></li>'; 
        c=$c(mapD);
        lm=1;
        map=c[1];
        $(window).bind('resize',function() {
            setMapR()
        });
    }
    var d=$n('main');
    d.style.display='none';
    $p(d).insertBefore(mapD,d);
    setMapR();
    if(lm){        
        var sh=document.createElement('script');
        sh.type= 'text/javascript';
        sh.async=true;
        sh.src='//maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=startMap&language='+lang;
        document.getElementsByTagName('HEAD')[0].appendChild(sh);
    }
}
function setMapR(){
    if(map){
        map.style.height=($(window).height()-117)+'px';
    }
}
var loc,marker,geo,infoW;
function startMap(){
    geo = new google.maps.Geocoder();
    infoW = new google.maps.InfoWindow({maxWidth: 400});
    var o={zoom:13,mapTypeId:google.maps.MapTypeId.HYBRID};
    vmap = new google.maps.Map(map,o);
    google.maps.event.addDomListener(vmap, "click", mapC);
    marker = new google.maps.Marker({map:vmap});
    google.maps.event.addListener(marker, "click", function() {
        infoW.open(vmap, marker);
    });
    myLoc();
}
function mapC(e){
    geo.geocode(
        {latLng:e.latLng},
        function(res, status) {
            if (status == google.maps.GeocoderStatus.OK && res[0]) {
                    lat=e.latLng.lat();
                    lon=e.latLng.lng();
                    marker.setMap(vmap);
                    marker.setPosition(e.latLng);
                    
                    setMI(res);
                    
                    cacheLoc(res);    
                    eMB(1);
             }else{
                 failM();
             }
        }
    );
}
function setMI(res){
    var v='',s='',t='';
    var a=res[0]['address_components'];
    var l=a.length;
    for(var i=0;i<l;i++){
        v=a[i]['long_name'];
        if(t!=v){
            t=v;
            if(s)s+=' - ';
            s+=v;
        }
    }
    infoW.setContent(s);
    infoW.open(vmap, marker);
}
function failM(){
    lat=0;
    lon=0;
    eMB(0);
}
function cacheLoc(loc,isSearch){
    if(!isSearch)isSearch=0;
    var obj=[];
    var l=loc.length;
    var k=0;
    for (var i=l-1;i>=0;i--) {
        obj[k]={latitude:loc[i].geometry.location.lat(),longitude:loc[i].geometry.location.lng(),type:loc[i].types[0],name:loc[i].address_components[0].long_name,short:loc[i].address_components[0].short_name,formatted:loc[i].formatted_address,vw_ne_lat:loc[i].geometry.viewport.getNorthEast().lat(),vw_ne_lon:loc[i].geometry.viewport.getNorthEast().lng(),vw_sw_lat:loc[i].geometry.viewport.getSouthWest().lat(),vw_sw_lon:loc[i].geometry.viewport.getSouthWest().lng()};
        k++;
    }
    $.ajax({
        url:'/ajax-location/',
        data:{
            lang:lang,
            loc:obj,
            search:isSearch
        },
        type:'POST'
    });
}
function mapSrch(e){
    var c=$c(e);
    var q=$f(c[1]);
    var val=q.value;
    if (val) {
        geo.geocode({address:val},function(res, status) {
            if (status == google.maps.GeocoderStatus.OK && res[0]) {
                setMI(res);
                cacheLoc(res,1);
                setPos(getPos(res[0].geometry.location.lat(),res[0].geometry.location.lng()));
            }else{
                fdT(q,0,'err');
                failM();
            }
        });
    }
    return false;
}
function eMB(s){
    if(mapD){
    var b=$f($c(mapD,2),2);
    if(s) fdT(b,1,'off');
    else {
        fdT(b,0,'off');
        marker.setMap();
    }
    }
}
function savLoc(){
    if(lat && lon){
        hasM=1;
        var pos=getPos(lat,lon);
        geo.geocode(
            {latLng:pos},
            function(res,status) {
                if (status == google.maps.GeocoderStatus.OK && res[0]) {
                    var u=mpU;
                    var c=$c(u);
                    var g=$f(c[1],2);
                    g.innerHTML='<b class="load"></b>';
                    var s='',t='',v='',a=res[0]['address_components'];
                    var l=a.length;
                    for(var i=0;i<l;i++){
                        v=a[i]['long_name'];
                        if(t!=v){
                            t=v;
                            if(s)s+=' - ';
                            s+=v;
                        }
                    }
                    if(s){
                        ad.lat=lat;
                        ad.lon=lon;
                        ad.loc=s;
                        extra.m=1;
                        g.innerHTML='<b class="ah"><span onclick="clearLoc(event)" title="'+(lang=='ar'?'إزالة الموقع':'remove location')+'" class="pz pzd"></span>'+s+'</b>';
                    }
                }else{
                     hasM=0;
                     clearLoc();
                }
            }
        );
    }else{
        hasM=0;
    }
    clMap();
}
function setPos(pos,z){
    if(z)vmap.setZoom(15);
    vmap.setCenter(pos);
    marker.setMap(vmap);
    marker.setPosition(pos);
    eMB(1);
}
function getPos(la,lo){
    lat=la;
    lon=lo;
    return new google.maps.LatLng(lat,lon);
}
function myLoc(f){
    if(!f && ad.lat && ad.lon){
        marker.setMap(vmap);
        vmap.setZoom(14);
        infoW.setContent(ad.loc);
        infoW.open(vmap, marker);
        setPos(getPos(ad.lat,ad.lon));
    }else {
        failM();
        var df=new google.maps.LatLng(33.8852793,35.5055758);
        vmap.setZoom(14);
        vmap.setCenter(df);
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos=getPos(position.coords.latitude,position.coords.longitude);
                geo.geocode(
                    {latLng:pos},
                    function(res, status) {
                        if (status == google.maps.GeocoderStatus.OK && res[0]) {                            
                            marker.setMap(vmap);
                            setPos(pos);
                            setMI(res);
                         }else{
                             failM();
                         }
                    }
                );
            });
        }else{
            vmap.setZoom(4);
        }
    }
}
function clMap(){
    $p(mapD).removeChild(mapD);
    var d=$n('main');
    d.style.display='block';
    noMap();
    gto(mpU);
}
function noMap(){
    var u,n;
    u=mpU;
    n=$c(u);
    if(hasM){
        fdT(n[3],0);
        fdT(n[2],0);
        fdT(n[1],1);
    }else{
        if(extra.m==2){
            noO($f(n[2],2),'m');
        }else{
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
}
function clearLoc(e){
    se(e);
    lat=0;
    lon=0;
    ad.lat=0;
    ad.lon=0;
    ad.loc='';
    hasM=0;
    if(marker)marker.setMap();
    eMB(0);
    noMap();
}
function dmp(){
    var p=mpU;
    var n=$c(p);
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
    savAd();
}


if(typeof imgs!=="undefined"){
    var d=$('#pics');var spim,dtm=null,dam=[],dCnt=0;
    function dePic(){
        var h=$(window).height();
        if(!spim){
            spim=$("span.load",d);
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

function iniC(p){
    var a=['rou','puu','seu'];
    if(p)a=[p];
    var u,l;
    for (var i in a){
        u=$n(a[i]);
        if(u){
            l=$cL(u);
            if(l){
                for (var j=0;j<l;j++){
                    $c(u,j).onclick=function(){liC(this)}
                }
            }
        }
    }
    u=$n('cnu');
    l=$cL(u);
    var x,m,n;
    x=$c(u,0);
    x.onclick=function(){cnT(u)};
    x=$c(u,l-2);
    x.onclick=function(){cnT(u)};
    for (var j=1;j<l-2;j++){
        x=$c(u,j);
        if($cL(x)>1){
            m=$c($c(x,1));
            n=m.length;
            for (var i=0;i<n;i++){
                m[i].onclick=function(){cnC(this)}
            }
        }else {
            x.onclick=function(){cnC(this)}
        }
    }
    
    if((typeof PVC) !== 'undefined' && cui.p.length){
        var n=$n('phL');
        var c=$c(n);
        var pl=cui.p;
        var l=cui.p.length;
        for(var i=0;i<l;i++){
            var rp=phoneNumberParser(pl[i]['r'],pl[i]['i'],pl[i]['t']);
            var b=$f(c[i]);
            var s=b.lastChild;
            var g=s.nodeValue;
            if(rp.p){
                if(rp.v){
                    if(rp.t){
                        if(lang=='ar')
                            g='Valid '+g;
                        else g+=' Valid';
                    }else{
                        if(lang=='ar')
                            g='TYPE ERROR: '+rp.e+' '+g;
                        else g+=' TYPE ERROR: '+rp.e;
                    }
                }else {
                    if(lang=='ar')
                        g='INVALID '+g;
                    else g+=' INVALID';
                }        
            }else{
                if(lang=='ar')
                    g=rp.e+' '+g;
                else g+=' '+rp.e;
            }
            s.nodeValue=g;
        }
    }
}
iniC();
//load country codes if ad already have contact numbers
if(hNum)
    ldCN();


(function(){
    var sh=document.createElement('script');
    sh.type='text/javascript';
    sh.async=true;
    sh.src=uixf;
    var s = document.getElementsByTagName('head')[0];
    s.appendChild(sh);
})();


function savAdP(){
    var sp = $('#spinner').SelectNumber();
    Dialog.show("make_premium",null,function(){confirmPremium(sp)});
}
function confirmPremium(sp){
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
    },function(){
        savAdP();
    });
}
function makePremium(c){
    ad.budget = c;
    savAd(1, true);
}