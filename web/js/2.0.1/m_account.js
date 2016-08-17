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
}
function spe(){
    var v=window.event;
    if(v){
        if(v.stopPropagation)
            v.stopPropagation();
    }
}
function ds(e){
    e.preventDefault();
}
function pi(){
    $.ajax({
        type:'POST',
        url:'/ajax-pi/'
    })
}
setInterval(pi,300000);

function gto(e){
    var r = e.getBoundingClientRect();
    var doc = document.documentElement, body = document.body;
    var top = (doc && doc.scrollTop  || body && body.scrollTop  || 0);
    window.scrollTo(0,r.top+top);
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
    $.ajax({
        type:'POST',
        url:'/ajax-screen/',
        data:{
            w:document.body.clientWidth,
            h:document.body.clientHeight
        }
    })
};
function uPO(d,o){
    if(!d)d=document.getElementById("sil");
    if(!sif)sif=document.getElementById("sif");
    var e=sif;
    var s=e.style.display;
    if(!uid && e.parentNode!=document.body){
        if(s=='block'){            
            if($p(sif) && $p(sif,2) && $p(sif,3) && $p(sif,4).tagName=='DIV'){
                pF(document.getElementById('dFB')) 
            }else {
                pF(document.getElementById('pFB'))            
            }
        }
        document.body.insertBefore(e, $('main')[0]);
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
function csif(){
    if(sif.parentNode==document.body){
        uPO(0,0);
        window.scrollTo(0,0)
    }else{
        if($p(sif) && $p(sif,2) && $p(sif,3) && $p(sif,4).tagName=='DIV'){
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


/*executable code*/
wsp();

var cs=document.createElement("link");cs.setAttribute("rel", "stylesheet");cs.setAttribute("type", "text/css");cs.setAttribute("href", ucss+"/mms.css");document.getElementsByTagName("head")[0].appendChild(cs);


window.onresize=function(){fbf();wsp()};
if(jsLog){
    window.onerror = function(m,url,ln) {
        $.ajax({
            type:'POST',
            url:'/ajax-js-error/',
            data:{
                e:m,
                u:url,
                ln:ln
            }
        })
    }
}


function ckO(e){
    var cn=e.className;
    if(cn=='on'){
        e.className='';
    }else {
        e.className='on';
    }
    var c=$c($p(e));
    $.ajax({
        type:'POST',
        url:'/ajax-account/',
        data:{
            form:'notifications',
            lang:lang,
            fields:{
                ads:(c[1].className=='on'?1:0),
                coms:(c[2].className=='on'?1:0),
                news:(c[3].className=='on'?1:0)
            }
        }
    });
}
function elg(e){
    var p=$p(e);
    var o=p.className.match(/pi/);
    if(e.className=='h'){
        if(o)olg(p);
    }else{
        if(o){            
            olg(p);
        }else{
            var c=$c(p);
            var v=e.getAttribute('val');
            if(v=='ar'){
                c[1].className='on';
                c[2].className='hid';
            }else{
                c[1].className='hid';
                c[2].className='on';
            }
            fdT(p,0,'pi');
            $.ajax({
                type:'POST',
                url:'/ajax-account/',
                data:{
                    form:'lang',
                    lang:lang,
                    fields:{
                        lang:v
                    }
                }
            });
        }
    }
}
function olg(u){
    var c=$c(u);
    fdT(u,1,'pi');
    fdT(c[1],1);
    fdT(c[2],1);
}
function enm(e){
    var p=$p(e);
    if(p.className.match(/pi/)){
        var c=$c(p);
        fdT(p,1,'pi');
        fdT(c[1]);
        fdT(c[2],1);
        $f(c[2],4).focus();
    }
}
var bt,btt,tlg;
function initB(e){
    bt=e;
    e.onpaste=function(){setTimeout('capk();',10)};
    btt=e.getAttribute('name');
}
function capk(e){
    if(!e)e=bt;
    var v=e.value;
    if(btt=='name'){
        if(v.match(/[\u0621-\u064a\u0750-\u077f]/)){
            tlg=e.className='ar';
        }else{
            tlg=e.className='en';
        }
    }
    setTimeout('tgl();',100);
}
function tgl(){
    var v=bt.value;
    var bu=$f($a($p(bt,2),2),2);
    if(btt=='name'){
        if(v.length>=3){
            fdT(bu,1,'off');
        }else{
            fdT(bu,0,'off');
        }
    }else if(btt=='email'){
        if(v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/)){
            fdT(bu,1,'off');
        }else{
            fdT(bu,0,'off');
        }
    }
}
function clF(e){
    if(!bt)bt=$f($b($p(e,2),2),2);
    bt.value='';
    var p=$p(e,3);
    var c=$c(p);
    fdT(c[0],1);
    fdT(c[1]);
    fdT($f(c[2],2),0,'off');
    p=$p(p,2);
    c=$c(p);
    fdT(c[1],1);
    fdT(c[2],0);
    fdT(p,0,'pi');
}
function savN(e){
    if(!bt)bt=$f($b($p(e,2),2),2);
    var v=bt.value;
    v=v.replace(/^\s+|\s+$/g, '');
    bt.value=v;
    if(!e.className.match(/off/)){
        if(v<3){
            fdT(e,0,'off');
        }else {
            if(v==uname){
                clF($f($a($p(bt,2),2),2));
            }else{
                if(v.match(/[^a-zA-Z\u0621-\u064a\u0750-\u077f\s]/)){
                    var m=(lang=='ar'?'الإسم لايمكن أن يحتوي سوى على أحرف الأبجدية':'Name can only contain alphabet characters');
                    setE(m);
                }else{
                    setE();
                    $.ajax({
                        type:'POST',
                        url:'/ajax-account/',
                        data:{
                            form:'name',
                            lang:lang,
                            fields:{
                                name:v
                            }
                        },
                        success:function(rp){
                            if(rp.RP){
                                var t=rp.DATA.fields.name[1];
                                var l=$b($p(e,4));
                                l.className=tlg;
                                var c=$f(l);
                                c.innerHTML=t+'<span class="et"></span>';
                                uname=t;
                                clF(e);
                            }else{
                                setE(rp.MSG);
                            }
                        }
                    });
                }
            }
        }
    }
}
function savM(e){
    if(!bt)bt=$f($b($p(e,2),2),2);
    if(!e.className.match(/off/)){
        var v=bt.value;
        if(v.value==uemail){
            clF($f($a($p(bt,2),2),2));
        }else{
            setE();
            $.ajax({
                type:'POST',
                url:'/ajax-account/',
                data:{
                    form:'email',
                    lang:lang,
                    fields:{
                        email:v
                    }
                },
                success:function(rp){
                    if(rp.RP){
                        var i=rp.DATA.fields.email;
                        var t;
                        if(i[2]){
                            t=rp.DATA.fields.email[2];
                        }else{
                            t=rp.DATA.fields.email[1];
                        }
                        var c=$f($b($p(e,4)));
                        c.innerHTML=t+'<span class="et"></span>';
                        uemail=t;
                        clF(e);
                    }else{
                        setE(rp.MSG);
                    }
                }
            });
        }
    }
}
function setE(m){
    var l=$a($p(bt,2));
    if(m){
        fdT(l,0,'liw');
        l.innerHTML='<b>'+m+'</b>'; 
        fdT($b(l),1);
    }else{
        fdT(l,1,'liw');
        l.innerHTML='<b class="load"></b>';
        fdT($b(l));
    }
    fdT(l,1);
}