var $=document, $$=$.body;
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
