var wrapperTop=0,CTRL=false;

const preventEventProp = (e) => { e.preventDefault(); e.stopPropagation(); return false; };
const preventEventPropagation = (e) => {e.stopPropagation()};
const preventModalTouch = (e) => { e.preventDefault(); e.stopPropagation(); return false; };

var supportsOrientationChange = "onorientationchange" in window, orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
window.addEventListener(orientationEvent, function() {
    if (adScreen){
        console.log(window.innerWidth);
        adScreen.fullWidth=(window.innerWidth<=768);
        adScreen._card.className='card col-'+(adScreen._modal.clientWidth<1200)?'8':'6';
    }
});
/*
document.addEventListener('contextmenu', function(e){preventEventProp(e);
if(CTRL && e.target.classList.contains('card-description')){
                console.log('click with console');    
});
*/
$.onkeydown = function (e) { CTRL=e.ctrlKey; }
$.onkeyup = function() { CTRL=false; }
$.addEventListener("DOMContentLoaded", function () {
    $$=$.body;
    let c=$$.query('div#cards');
    if(c){
        let a=c.querySelectorAll('div.ad:last-child');
        if(a){
            let n=$$.query('ul.pgn');
            if(n){
                let r=n.closest('div.row');
                if(r.offsetTop-a[0].offsetTop-a[0].offsetHeight>r.offsetHeight){
                    let p=c.closest('div.row');
                    p.style.setProperty('flex-flow', 'column');
                    p.appendChild(r);
                }
            }
        }
        
    }
});

class AdScreen {
    constructor(ad){    
        wrapperTop=0;        
        this.pixIndex=0;
        this.fullWidth=(window.innerWidth<=768);
        this.cui=JSON.parse(ad.dataset.cui);
        this.coord=ad.dataset.coord;
        this.premuim=ad.dataset.premuim;
        this.pics=[];
        if (ad.dataset.pics){this.pics = ad.dataset.pics.split(','); }

        this._modal=this.newTag('div', 'modal'); 
        this._modal.setAttribute('id', 'adScreen');
        this._modal.dataset.id=ad.query('div.card-product').id;
        this._card=this.newTag('div', (window.matchMedia('(max-width: 1200px)').matches)?'card col-8':'card col-6' /*'card col-6'*/);
        
        if (this.pics.length===0||this.fullWidth) {
            this._top=this.newTag('div', 'top'); 
            this._card.appendChild(this._top);
        }
        this._media=this.newTag('div', 'card-image');
        this._body=this.newTag('div', 'card-content');
        this._footer=this.newTag('div', 'card-footer');
        this._close=this.newTag('span', 'close');
        this._close.onclick=this.close;
        if (this._top) {
            this._close.className='close nopix';
            this._top.appendChild(this._close);
        }
        else {
            this._card.appendChild(this._close);
        }
        this.cardImage=this.newTag('div', 'col-12');
        this.cardImage.style.display='block';
        this.cardImage.style.position='relative';
        this.cardImage.appendChild(this._media);
        
        this._card.appendChild(this.cardImage);
        this._card.appendChild(this._body);
        this._card.appendChild(this._footer);
        this._modal.appendChild(this._card);
        this._body.appendChild(ad.querySelectorAll('.adc')[0].cloneNode(true));
        
        this._ad_slot=this.newTag('ins', 'adsbygoogle');
        this._ad_slot.setAttribute('data-ad-client', 'ca-pub-2427907534283641');
        this._ad_slot.setAttribute('data-ad-slot', '7030570808');
        this._ad_slot.setAttribute('data-ad-format', 'auto');
        this._ad_slot.setAttribute('data-full-width-responsive', 'true');
        if (this.pics.length) {
            var src=ad.querySelectorAll('img')[0].src;            
            this.host=src.substring(0, src.indexOf('/repos/'));
        }

        var ul=ad.querySelectorAll('.card-footer>ul');
        if (ul.length){ this._links=ul[0].cloneNode(true); }

        if (ad.querySelectorAll('.cbox.cbl').length){
            this._since=ad.querySelectorAll('.cbox.cbl')[0].textContent;
        }

        if (ad.querySelectorAll('.cbox.cbr').length){
            this._pubType=ad.querySelectorAll('.cbox.cbr')[0].textContent;
        }
        
        
        this._card.onclick=function(e) {            
            console.log('click ', CTRL);
            if(CTRL && e.target.classList.contains('card-description')){
                console.log('click with console');
                let cmp=$.querySelector('div.compare');
                if (cmp && cmp.id>0) {
                    const channel=new BroadcastChannel('admin');
                    channel.postMessage({articleId:cmp.id, rejectURL:'https://www.mourjan.com/'+e.target.closest('div#adScreen').dataset.id});
                    channel.close();
                }
            }
        }
        this._card.oncontextmenu=function(e) {
            preventEventProp(e);
            if(CTRL && e.target.classList.contains('card-description')){
                console.log('click with console');
                let cmp=$.querySelector('div.compare');
                if (cmp && cmp.id>0) {
                    const channel=new BroadcastChannel('admin');
                    channel.postMessage({articleId:cmp.id, rejectURL:'https://www.mourjan.com/'+e.target.closest('div#adScreen').dataset.id});
                    channel.close();
                }
            }
        }
        
    }

    stopBodyScrolling(bool){
        var k = byId('wrapper');
        if (bool) {
            $.body.style.setProperty('overflow', 'hidden', 'important');            
            if(this.fullWidth){
                //wrapperTop=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
                wrapperTop=document.documentElement.scrollTop;
                k.style.display='none';
            }
        }
        else {
            $.body.style.overflow='auto';
            if(this.fullWidth){
                k.style.display='flex';
                document.documentElement.scrollTop=wrapperTop;
            }
        }
    }

    newTag(tag,cls,stl){var t=$.createElement(tag);
        if(cls){t.className=cls;}
        if(stl){t.style.cssText=stl;}
        return t;
    }

    button(tag, text, href, icon, target){
        var a=this.newTag(tag, 'btn');
        a.text=text;
        if(href){
            if (typeof(href)==='function'){
                a.onclick=function(){href();}
            }
            else {
                a.href=href;
            }
        }
        
        if (icon)a.appendChild(this.newTag('i','icn icn-'+icon));
        
        if (target)a.target=target;
        return a;
    }
    
    btn(text,href,icon,target){
        return this.button('a', text,href,icon,target);
    }

    inlineBtn(text,bgc){
        var a=this.btn(text,'#');
        a.style.backgroundColor = bgc;
        a.style.setProperty("color", "white", "important");
        a.style.setProperty("display", "inline-block", "important");
        a.style.setProperty("margin", "0 12px");
        a.style.setProperty("width", "140px");
        a.style.setProperty("font-weight", "bold");
        return a;
    }
    
    prevPix(){
        if(adScreen.pixIndex>0){
            adScreen.pixIndex--;
            adScreen._media.firstChild.src=adScreen.host + '/repos/d/' + adScreen.pics[adScreen.pixIndex];   
            if (adScreen.nextDiv.style.display==='none'){adScreen.nextDiv.style.display='initial';}
        }        
        adScreen.prevDiv.style.display=adScreen.pixIndex===0?'none':'initial';        
    }
    
    nextPix(){
        if(adScreen.pixIndex<adScreen.pics.length-1){
            adScreen.pixIndex++;
            adScreen._media.firstChild.src=adScreen.host + '/repos/d/' + adScreen.pics[adScreen.pixIndex];
            if (adScreen.prevDiv.style.display==='none'){adScreen.prevDiv.style.display='initial';}
        }
        adScreen.nextDiv.style.display=adScreen.pixIndex===adScreen.pics.length-1?'none':'initial';
    }

    open(){
        this._close.innerHTML='&times;';
        this._footer.style.setProperty("text-align", "center");
        if (this.pics.length) {
            var img=new Image();
            img.src=this.host + '/repos/d/' + this.pics[0];
            this._media.appendChild(img);
            img.onload=function () {
                console.log('size: '+img.naturalWidth+'x'+img.naturalHeight);
                console.log(img);
            }
        }
        else {
            this._media.className='card-media';
            this._media.style.setProperty('top', '8px');
            this._media.appendChild(this._ad_slot);
        }

        var ch=this.newTag('div', 'contact');
        if (this.cui.p) {
            for (var i in this.cui.p) {
                var btn;
                if (this.cui.p[i].t===5) {
                    btn=this.btn(this.cui.p[i].v, 'https://api.whatsapp.com/send?phone='+this.cui.p[i].n, 'whatsapp', '_blank');
                }
                else {
                    btn=this.btn(this.cui.p[i].v, 'tel:' + this.cui.p[i].v.replace(/\s/g, ''), 'phone');
                }
                ch.appendChild(btn);
                if (this.cui.p[i].t===3) {
                    btn=this.btn(this.cui.p[i].v, 'https://api.whatsapp.com/send?phone=' + this.cui.p[i].n, 'whatsapp', '_blank');
                    ch.appendChild(btn);
                }
            }
        }
        var rtl=($.body.dir==='rtl');
        if(this.cui.e){ch.appendChild(this.btn(this.cui.e, 'mailto:' + this.cui.e, 'email'));}
        if(this.coord){
            var btn=this.btn(rtl?'عرض على الخريطة':'View on map', 'https://maps.google.com/maps/?saddr=My+location&z=14&daddr=' + this.coord, 'map-marker', '_blank');
            btn.querySelectorAll('.icn')[0].style.backgroundColor = 'white';
            ch.appendChild(btn);
        }
        this._body.appendChild(ch);
        var dv=this.newTag('div',null,'display:block;font-size:1.0rem;padding:20px 0;');
        var btn=this.inlineBtn(rtl?'شارك الاصدقاء':'Share', '#3b5998'); dv.appendChild(btn);
        var btn=this.inlineBtn(rtl?'تبليغ':'Report', 'red'); dv.appendChild(btn);
        this._footer.appendChild(dv);
        
        if(this.pics.length){
            if(!this.premuim){
                var adH=this.newTag('div', 'card-media');
                adH.style.setProperty('margin', '24px 0');
                adH.appendChild(this._ad_slot);
                adH.appendChild(this.newTag('br'));
                this._footer.appendChild(adH);
            }
            if(this.pics.length>1){                
                this.prevDiv=this.button('span', '', this.prevPix, 'chevron-left');
                this.prevDiv.style.cssText='position:absolute;display:none;top:0;bottom:0;left:0;right:auto;margin:auto;width:60px;height:60px;padding:14px;opacity:0.6;z-index:9999';
                this.cardImage.appendChild(this.prevDiv);

                this.nextDiv=this.button('span', '', this.nextPix, 'chevron-right');
                this.nextDiv.style.cssText='position:absolute;display:inline-block;top:0;bottom:0;left:auto;right:0;margin:auto;width:60px;height:60px;padding:14px;opacity:0.6;z-index:9999';
                this.cardImage.appendChild(this.nextDiv);
            }
        }

        if(this._links){this._footer.appendChild(this._links);}
        var dv=this.newTag('div', null, 'display:flex;justify-content:space-between;font-size:0.9rem;padding:20px 0;');
        if(this._since){
            var st=this.newTag('span'); st.textContent = this._since;
            dv.appendChild(st);
        }
        if (this._pubType){
            var pt=this.newTag('span'); pt.textContent = this._pubType;
            dv.appendChild(pt);
        }
        this._footer.appendChild(dv);
        
        this.stopBodyScrolling(true);
        $.body.appendChild(this._modal);
        this._modal.style.display='block';
        
        if (!this.premuim && window.adsbygoogle) {            
            this._ad_slot.style.cssText='display:inline-block;height:auto;width:'+this._media.offsetWidth+'px;';
            (adsbygoogle = window.adsbygoogle || []).push({});
        }       
    }

    close(){
        adScreen._modal.style.display='none';
        adScreen._modal.remove();
        adScreen._modal=null;
        adScreen.stopBodyScrolling(false);
        adScreen=null;
    }
}

var adScreen=null;

function oad(ad){
    adScreen = new AdScreen(ad);
    adScreen.open();
    if (1) return;
    var state = {'detail':1};
    window.history.pushState(state, $.title, $.location.href);
    $.body.setAttribute('data-detail', 1);
}

function report(e){
    let level=parseInt($.body.dataset.level);
    if(level===9||level===90){
        if(confirm("Hold this ad?")){
            fetch('/ajax-report/',{method:'POST',mode:'same-origin',credentials:'same-origin',
                     body:JSON.stringify({id:parseInt(e.parentElement.parentElement.id)}),
                     headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', response);
                if(response.success===1){
                    e.parentElement.parentElement.style.backgroundColor='lightgray';
                }
                else {
                    window.alert(response.error);
                }
            })
            .catch(error => { 
                console.log('Error:', error); 
            });
        }
        //var rpa=function(id,e){if(confirm("Hold this ad?")){e=$(e);if (!e.hasClass("loading")){e.addClass("loading");
        //$.ajax({type:"POST",url:"/ajax-report/",data:{id:id},dataType:"json",success:function(rp){if(rp.RP){e.click=function(){};e.css("background", "0");e.html("Done")}e.removeClass("loading")},error:function(rp){e.removeClass("loading")}})}}};';
    }    
}


function sorting(o){
    console.log(o);
    var idx = o.selectedIndex; 
    console.log(idx);
    console.log(o.value);
    //location.href = o.value;
}


function searching(as) {
    console.log(as);
}


window.onclick=function(e){if(adScreen&&e.target===byId('adScreen')){adScreen.close();}};


window.onpopstate=function(event){
    console.log($.body.dataset.detail);
    if ($.body.hasAttribute('data-detail')) {
        AdScreen._modal.style.display = "none";
        $.body.style.setProperty('overflow', 'scroll');
        $.body.removeAttribute('data-detail');
    }
    else {
        window.history.back();
    }
};

const ibars=byId('ibars');
if(ibars){
    ibars.onclick=function(e){
        let side=$$.query('div.side'), cards=$$.query('div#cards');
        let v=(side.style.width===''||side.style.width==='0px');
        let mw=(window.matchMedia('(max-width: 500px)').matches)?'100%':'50%';
        side.style.minWidth=v?mw:'';
        side.style.width=v?'auto':'0px';
        side.style.opacity=v?1:0;
        cards.style.display=v?'none':'flex';
        e.stopPropagation();
    };
}