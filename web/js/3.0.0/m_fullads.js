var switchTo5x=true,cad=[],serv=['email','facebook','twitter','googleplus','linkedin','sharethis'],servd=[],tmp,fbx,leb,peb,cli,sif;
var fbf=function(){};
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
function pi(){
    posA("/ajax-pi/",null,function(){});
    setTimeout('pi();',300000);
}

function gto(e){
    var r = e.getBoundingClientRect();
    var doc = document.documentElement, body = document.body;
    var top = (doc && doc.scrollTop  || body && body.scrollTop  || 0);
    window.scrollTo(0,r.top+top);
}
function $(i){
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
function idir(e){
    var v=e.value;
    if(v.match(/[\u0621-\u064a\u0750-\u077f]/)){
        e.className='ar';
    }else{
        e.className='en';
    }
}
function ose(e){
    var o=(e.className=="srch" ? 0:1);
    var d=e.parentNode.nextSibling.firstChild;
    if (o){
        if (hasQ)
            document.location=d.firstChild.action;
        else {
            e.className="srch";
            d.className="sef"
        }
    }else {
        e.className="srch on";
        d.className="sef on";
        document.getElementById('q').focus();
    }
};
function wsp(){
    posA('/ajax-screen/','w='+document.body.clientWidth+'&h='+document.body.clientHeight,function(){})
};
function uPO(d,o){
    if(!d)d=document.getElementById("sil");
    if(!sif)sif=document.getElementById("sif");
    var e=sif;
    var s=e.style.display;
    if(!uid && e.parentNode!=document.body){
        if(s=='block'){            
            if(sif.parentNode && sif.parentNode.parentNode && sif.parentNode.parentNode.parentNode && sif.parentNode.parentNode.parentNode.tagName=='DIV'){
                pF(document.getElementById('dFB')) 
            }else {
                pF(document.getElementById('pFB'))            
            }
        }
        document.body.insertBefore(e, document.getElementById('main'));
    }
    s=e.style.display;
    if(s=="block") {
        e.style.display="none";
        d.firstChild.className="k log"+(o?" on":"")
    }else {
        e.style.display="block";
        d.firstChild.className="k log"+(o?" on":"")+" op"
    }
    e.className="si"
};
function ppf(s){
    return encodeURIComponent(s)
};
function csif(){
    if(sif.parentNode==document.body){
        uPO(0,0);
        window.scrollTo(0,0)
    }else{
        if(sif.parentNode && sif.parentNode.parentNode && sif.parentNode.parentNode.parentNode && sif.parentNode.parentNode.parentNode.tagName=='DIV'){
            pF(document.getElementById('dFB')) 
        }else {
            pF(document.getElementById('pFB'))            
        }
    }
};
/*
var h1,p1,m1;
function tof(){
    if (!h1){
        h1=document.getElementById("title");
        p1=h1.parentNode
    }
    var c=p1.lastChild;
    if(c.tagName=='H1'){
        if (c.clientWidth < c.scrollWidth || c.clientHeight < c.scrollHeight){
            if (!m1){
                m1=document.createElement("marquee");
                m1.className=hd;
                m1.behavior="scroll";
                m1.innerHTML=h1.innerHTML
            }
            p1.removeChild(h1);
            p1.appendChild(m1)
        }
    }else if(c.tagName=='MARQUEE'){
        p1.removeChild(m1);
        p1.appendChild(h1);
        tof()
    }
};
tof();
*/
function getWSct(){
    var d = document.documentElement, b = document.body;
    return (d && d.scrollTop  || b && b.scrollTop  || 0)
}
function wo(u){if(u)document.location=u};
/*
function hbr(){
    var t=window.pageYOffset || document.documentElement.scrollTop;
    if(t<=1)setTimeout(function(){window.scrollTo(0,1)},1);
}
*/

/*executable code*/
wsp();

var cs=document.createElement("link");cs.setAttribute("rel", "stylesheet");cs.setAttribute("type", "text/css");cs.setAttribute("href", ucss+"/mms.css");document.getElementsByTagName("head")[0].appendChild(cs);
/*
if (mod=='index' && !ro && cn){
    fbx=document.getElementById('fb-box');
    fbx.setAttribute('data-width', (document.body.clientWidth-20));
    (function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s);js.id = id;js.onload=function(){fbf=function(){var s=fbx.firstChild;if(s){var f=s.firstChild;var w=document.body.clientWidth;s.style.width=(w-20)+'px';f.style.width=(w-20)+'px';f.width=(w-20)}};fbf()};js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=215939228482851";fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));
}

var standalone=(window.navigator.userAgent.indexOf('iPhone')!=-1 && window.navigator.standalone==true);
if (standalone) {
    var a=document.getElementsByTagName("a");
    for(var i=0;i<a.length;i++){
        var href=this.getAttribute("href");
        a[i].onclick=function(){
            window.location=this.getAttribute("href");
            return false
        }
    }
};
*/
window.onresize=function(){fbf();wsp()};
if(jsLog)
window.onerror = function(m, url, ln) {
    posA('/ajax-js-error/','e='+ppf(m)+'&u='+ppf(url)+'&ln='+ln,function(){});
};


var eid=0;
function ado(e,i){
    eid=i;
    var li=e;
    var e=$c($c(li,1),2);
    if (!leb){
        leb=$('aopt');
    }else if($p(leb)){
        var l=$p(leb);
        if (l!=li) {
            fdT(l,1,'edit');
            peb.className="adn";
            acls(leb)
        }
        l.removeChild(leb);
        leb.style.display="none";
    }
    peb=e;
    if(li.className.match(/edit/)){
        fdT(li,1,'edit');
        e.className="adn";
    }else{
        fdT(li,0,'edit');
        e.className="adn aup";
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
        t[i].className='';
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
function adel(e,h){
    se();
    if(confirm(lang=='ar'?'حذف هذا الإعلان؟':'Delete this ad?')){
        if(!h)h=0;
        var l=$p(e,2);
        var s=$p(l);
        fdT(l,1,'adn');
        s.removeChild(l);
        var d=mask(s);
        posA('/ajax-adel/','i='+eid+'&h='+h,function(rp){
            if(rp.RP){
                var m=(lang=='ar'?'تم الحذف':'Deleted');
                mask(s,d,m);
            }else{
                s.removeChild(d);
            }
        });
    }
}
function are(e){
    se();
    if(confirm(lang=='ar'?'تجديد هذا الإعلان؟':'Renew this ad?')){
        var l=$p(e,2);
        var s=$p(l);
        fdT(l,1,'adn');
        s.removeChild(l);
        var d=mask(s);
        posA('/ajax-arenew/','i='+eid,function(rp){
            if(rp.RP){
                var m=(lang=='ar'?'تم تحويل الإعلان للائحة إنتظار النشر':'Ad is pending to be re-published');
                mask(s,d,m);
            }else{
                s.removeChild(d);
            }
        });
    }
}
function ahld(e){
    se();
    if(confirm(lang=='ar'?'إيقاف عرض هذا الإعلان؟':'Stop this ad?')){
        var l=$p(e,2);
        var s=$p(l);
        fdT(l,1,'adn');
        s.removeChild(l);
        var d=mask(s);
        posA('/ajax-ahold/','i='+eid,function(rp){
            if(rp.RP){
                var m=(lang=='ar'?'تم تحويل الإعلان إلى الأرشيف':'Ad is moved to archive');
                mask(s,d,m);
            }else{
                s.removeChild(d);
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
    e.className+=' on';
}
function eLD(e){
    e.className=e.className.replace(/ on/g,"");
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
    //ping server
    pi();