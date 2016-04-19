var switchTo5x=true,cad=[],serv=['email','facebook','twitter','googleplus','linkedin','sharethis'],servd=[],tmp,fbx,leb,peb,cli,sif;
var fbf=function(){};
function se(e){
    var v=e || window.event;
    if(v){
        if(v.preventDefault){
            v.preventDefault();
        }
        if(v.stopImmediatePropagation){
            v.stopImmediatePropagation();
        }
        if(v.stopPropagation){
            v.stopPropagation();
        }else {
            v.cancelBubble = true;
        }
    }
}
function spe(e){
    var v=e || window.event;
    if(v){
        if(v.stopPropagation){
            v.stopPropagation();
        }else {
            v.cancelBubble = true;
        }
    }
}
function ds(e){
    var v=e || window.event;
    if(v){
        if(v.preventDefault)
            v.preventDefault();
    }
}
function skO(e){
    var z=$(e);
    if(z.hasClass('on')){
        z.removeClass('on');
        z[0].firstChild.value=0;
    }else {
        z.addClass('on');
        z[0].firstChild.value=1;
    }
}
function pi(){
    $.ajax({
        type:'POST',
        url:'/ajax-pi/'
    })
}
if(uid)setInterval(pi,300000);

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
    var c=e.childNodes;
    if(n==null){
        return c;
    }else if(n==-1){
        return e.lastChild;
    }else{
        return c[n];
    }
}
function $cL(e,n){
    return e.childNodes.length;
}
function fdT(u,s,c){
    if(!c)c='hid';
    if(s){
        /*var q=new RegExp('\s'+c+'|'+c,'ig');
        var k=u.className;
        k=k.replace(q,'');
        k=k.replace(/^\s+|\s+$/g,'');
        u.className=k;*/
        $(u).removeClass(c);
    }else{
        //u.className+=' '+c;
        $(u).addClass(c);
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
    if(v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/))
        return true;
    return false;
}
function ose(e){
    var z=$(e);
    var o=(z.hasClass('on') ? 1:0);
    var d=$f($a($p(e)));
    if (o){
        if (hasQ)
            document.location=$f(d).action;
        else {
            z.removeClass('on');
            $(d).removeClass('on');
        }
    }else {
        console.log('bass');
        z.addClass('on');
        $(d).addClass('on');
        $('#q').focus();
    }
};
function wsp(){
    $.ajax({
        type:'POST',
        url:'/ajax-screen/',        
        dataType:'json',
        data:{
            w:document.body.clientWidth,
            h:document.body.clientHeight,
            c:hasCvs
        }
    })
};
function uPO(d,o){
    if(!d)d=$("#sil")[0];
    if(!sif)sif=$("#sif")[0];
    var e=sif;
    var s=e.style.display;
    if(!uid && e.parentNode!=document.body){
        if(s=='block'){            
            if($p(sif) && $p(sif,2) && $p(sif,3) && $p(sif,4).tagName=='DIV'){
                pF($('#dFB')[0]) 
            }else {
                pF($('#pFB')[0])            
            }
        }
        document.body.insertBefore(e,$('#main')[0]);
    }
    s=e.style.display;
    if(s=="block") {
        e.style.display="none";
        $f(d).className="k log"+(o?" on":"")
    }else {
        e.style.display="block";
        $f(d).className="k log"+(o?" on":"")+" op"
    }
    $(e).addClass("si")
};
function csif(){
    if(sif.parentNode==document.body){
        uPO(0,0);
        window.scrollTo(0,0)
    }else{
        if($p(sif) && $p(sif,2) && $p(sif,3) && $p(sif,4).tagName=='DIV'){
            pF($('#dFB')[0]);
            gto($p(sif,2));
        }else {
            pF($('#pFB')[0]);
            gto($p(sif,3));
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

function closeBanner(event,e,banner,rmFootPad){
    se(event);
    $(e).parent().remove();
    if(rmFootPad){
        $("#footer").css("margin-bottom","10px");
    }
    $.ajax({
        type:'POST',
        url:'/ajax-close-banner/',           
        dataType:'json',
        data:{
            id:banner
        }
    })
}

/*executable code*/
wsp();

var cs=document.createElement("link");cs.setAttribute("rel", "stylesheet");cs.setAttribute("type", "text/css");cs.setAttribute("href", ucss+"/mms.css");document.getElementsByTagName("head")[0].appendChild(cs);


window.onresize=function(){fbf();wsp()};
if(jsLog){
    window.onerror = function(m,url,ln) {
        if(m!='Script error.')
        $.ajax({
            type:'POST',
            url:'/ajax-js-error/',           
            dataType:'json',
            data:{
                e:m,
                u:url,
                ln:ln
            }
        })
    }
}

var lastScrollTop = 0, isMenuUp=0, isMenuOpen=0, isSearchOpen=0, dontBlur=0;
var menu=$("#top");
var menuHeight=menu.height();
$(window).scroll(function(event){
    if(isMenuOpen) return;
    var st = $(this).scrollTop();
    var bf = $("#bottomFtr");
    if(bf){
        if(st>=60){
            bf.hide(600);
        }else{
            bf.show(600);
        }
    }
    if (st > lastScrollTop && st > 40){
        if (!isMenuUp){
            menu.addClass("up");
            isMenuUp=1;
        }
    } else {
       if(isMenuUp){
            menu.removeClass("up");
            isMenuUp=0;
        }
    }
    lastScrollTop = st;
});
function toggleMenu(e){
    se();
    e=$(e);
    if(isMenuOpen){
        e.removeClass("on");    
        isMenuOpen=0;
    }else{
        e.addClass("on");
        isMenuOpen=1;
    }
};
function showSearch(e){
    se();
    var h=$(".qHolder",menu);
    h.addClass("on");
    isSearchOpen=1;
    setTimeout(function(){
        $("#q", h).focus();
    }, 0);
};
function hideSearch(e){
    if(!dontBlur){
    se();
    $(".qHolder",menu).removeClass("on");    
    isSearchOpen=0;
    }
};

/**************************************************/
new SearchBox('#searchBox');
new TopBox('.top');
new ActionBox('.actionBox');
//$('.actionBox').actionBox().showBox();
/*
$(window).on('scroll touchmove', function() {
    if(deltaY != 0 && deltaY == this.scrollY)return;
    if((animDown || animUp) && this.scrollY  > 100)return;
    var isDown = false;
    if (deltaY - this.scrollY < 0) {
        isDown = true;
    }
    deltaY = this.scrollY;
    if (isDown && topShown!=0) {
        var h = header.height();
        if (deltaY >= 100) {
            header.css({
                "-webkit-transform": "translate(0px,-" + h + "px)",
                "-ms-transform": "translate(0px,-" + h + "px)",
                "transform": "translate(0px,-" + h + "px)"
            });
            animUp = 1;
            animDown = 0;
            topShown=0;
            setTimeout(function() {
                animUp = 0;
            }, 1000);
        }
    } else if (!isDown && topShown!=1) {
        header.css({
            "-webkit-transform": "translate(0px,0px)",
            "-ms-transform": "translate(0px,0px)",
            "transform": "translate(0px,0px)"
        });
        animDown = 1;
        animUp = 0;
        topShown=1;
        setTimeout(function() {
            animDown = 0;
        }, 1000);
    }
});*/