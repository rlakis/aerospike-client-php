
var $=document;
var $$=$.body;
var ar=false;
$.addEventListener("DOMContentLoaded",function(e){
    $$=$.body;
    ar=($$.dir==='rtl');
    $.documentElement.setAttribute('data-useragent', navigator.userAgent);
});

var byId=function(id){return $.getElementById(id);}

createElem=function(tag, className, content, isHtml) {
    var el=$.createElement(tag);
    if(className){el.className=className;}
    if (typeof content!=='undefined')
        el[isHtml||false?'innerHTML':'innerText']=content;
    return el;
};

dirElem=function(e) {
    if(e.target){e=e.target;}
    var v=e.value;
    e.className=(!v)?'':((v.match(/[\u0621-\u064a\u0750-\u077f]/)) ? 'ar' : 'en');
};

_options=function(m, dat){
    m=m.toUpperCase();
    let opt={method: m, mode: 'same-origin', credentials: 'same-origin', headers:{'Accept':'application/json','Content-Type':'application/json'}};
    if(m==='POST'){opt['body']=JSON.stringify(dat);}
    return opt;
};

Node.prototype.append=function(){
    for (let i = 0; i < arguments.length; i++) {
        this.appendChild(arguments[i]);
    }
};

Element.prototype.query=function(selector){
    return this.querySelector(selector);
};

Element.prototype.queryAll=function(selector){
    return this.querySelectorAll(selector);
};

function hasWebP() {
    var e = createElem('canvas');
    if (!!(e.getContext && e.getContext('2d'))) {
        return e.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }
    return false;
}

regionWidget=function(e){
    let a=$.querySelector('a#regions'), r=JSON.parse(a.dataset.regions), d=$.querySelector('div#rgns');
    if (d.innerHTML>''){d.innerHTML='';return;}
    th=$.querySelector('div.top-header');
    let y=th.offsetHeight+th.offsetTop+8;    
    let s='<div style="display:flex;position:absolute;flex-flow:column;align-self:center;top:'+y+'px;left:0px;width:100%;height:auto;z-index:9;"><div class="row viewable"><div class=col-12 style="padding:0"><div class="card regions">';
    s+='<header><i class="icn icn-region invert"></i><h4><span style="color:white;font-size:36px">mourjan</span> around The Middle East</h4></header>';
    s+='<div class=card-content><div class=row>';
    let aa=[['ae', 'qa', 'om'], ['sa', 'bh'], ['kw', 'jo','ma', 'tn'], ['lb','eg','iq','dz']];
    aa.forEach(g=>{
        s+='<dl class="dl col-4">';
        g.forEach(c=>{
            let v=r[c];
            s+='<dt><a href='+v.p+'><i class="icn icn-'+c+'"></i><span>'+v.n+'</span></a></dt>';
            v.c.forEach(t=>{
                s+='<dd><a href='+t.p+'>'+t.n+'</a></dd>';
            });
        
        });
        s+='</dl>'
    });
    s+='</div></div></div></div></div>';
    d.innerHTML=s;
    var bounding=d.getBoundingClientRect();
    if (!(bounding.top >= 0 &&
	bounding.left >= 0 &&
	bounding.right <= (window.innerWidth || document.documentElement.clientWidth) &&
	bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight))) {
        window.scrollTo(0, 0);
        //d.scrollIntoView(true);
    }
   
};