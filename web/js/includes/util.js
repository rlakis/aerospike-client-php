var $=document;
var byId=function(id){return $.getElementById(id);}

createElem=function(tag, className, content, isHtml) {
    var el=$.createElement(tag);
    if(className){el.className=className;}
    if (typeof content!=='undefined')
        el[isHtml||false?'innerHTML':'innerText']=content;
    return el;
};

dirElem=function(e) {
    if(e.target){
        e=e.target;
    }
    var v=e.value;
    e.className=(!v)?'':((v.match(/[\u0621-\u064a\u0750-\u077f]/)) ? 'ar' : 'en');
};

_options=function(m, dat){
    m=m.toUpperCase();
    let opt={method: m, mode: 'same-origin', credentials: 'same-origin', headers:{'Accept':'application/json','Content-Type':'application/json'}};
    if(m==='POST'){opt['body']=JSON.stringify(dat);}
    return opt;
};
