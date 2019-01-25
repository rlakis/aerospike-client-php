<script>
var wrapperTop=0;
var $=document;
var byId=function(id){return $.getElementById(id); }

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

        this._modal=this.newTag('div', 'modal'); this._modal.setAttribute('id', 'adScreen');
        this._card=this.newTag('div', 'card col-6');
        if (this.pics.length===0||this.fullWidth) {
            this._top = this.newTag('div', 'top'); 
            this._card.appendChild(this._top);
        }
        this._media = this.newTag('div', 'card-image');
        this._body = this.newTag('div', 'card-content');
        this._footer = this.newTag('div', 'card-footer');
        this._close = this.newTag('span', 'close');
        this._close.onclick = this.close;
        if (this._top) {
            this._close.className = 'close nopix';
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
        this._ad_slot = this.newTag('ins', 'adsbygoogle');
        this._ad_slot.setAttribute('data-ad-client', 'ca-pub-2427907534283641');
        this._ad_slot.setAttribute('data-ad-slot', '7030570808');
        this._ad_slot.setAttribute('data-ad-format', 'auto');
        this._ad_slot.setAttribute('data-full-width-responsive', 'true');
        if (this.pics.length) {
            var src=ad.querySelectorAll('img')[0].src;            
            this.host=src.substring(0, src.indexOf('/repos/'));
        }

        var ul = ad.querySelectorAll('.card-footer>ul');
        if (ul.length){ this._links=ul[0].cloneNode(true); }

        if (ad.querySelectorAll('.cbox.cbl').length){
            this._since = ad.querySelectorAll('.cbox.cbl')[0].textContent;
        }

        if (ad.querySelectorAll('.cbox.cbr').length){
            this._pubType = ad.querySelectorAll('.cbox.cbr')[0].textContent;
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
            console.log(typeof(href));
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
            var img = new Image();
            img.src = this.host + '/repos/d/' + this.pics[0];
            this._media.appendChild(img);
            img.onload = function () {
                console.log('size: '+img.naturalWidth+'x'+img.naturalHeight);
                console.log(img);
                //var hh = img.offsetHeight;
                //if (hh > window.innerHeight / 3){hh = Math.round(window.innerHeight / 3)};
                //this.parentElement.style.setProperty('min-height', hh + 'px');
            }
        }
        else {
            this._media.className = 'card-media';
            this._media.style.setProperty('top', '8px');
            this._media.appendChild(this._ad_slot);
        }

        var ch = this.newTag('div', 'contact');
        if (this.cui.p) {
            for (var i in this.cui.p) {
                var btn;
                if (this.cui.p[i].t===5) {
                    btn = this.btn(this.cui.p[i].v, 'https://api.whatsapp.com/send?phone='+this.cui.p[i].n, 'whatsapp', '_blank');
                }
                else {
                    btn = this.btn(this.cui.p[i].v, 'tel:' + this.cui.p[i].v.replace(/\s/g, ''), 'phone');
                }
                ch.appendChild(btn);
                if (this.cui.p[i].t===3) {
                    btn = this.btn(this.cui.p[i].v, 'https://api.whatsapp.com/send?phone=' + this.cui.p[i].n, 'whatsapp', '_blank');
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
        var dv = this.newTag('div',null,'display:block;font-size:1.0rem;padding:20px 0;');
        var btn = this.inlineBtn(rtl?'شارك الاصدقاء':'Share', '#3b5998'); dv.appendChild(btn);
        var btn = this.inlineBtn(rtl?'تبليغ':'Report', 'red'); dv.appendChild(btn);
        this._footer.appendChild(dv);
        
        if(this.pics.length){
            if(!this.premuim){
                var adH = this.newTag('div', 'card-media');
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
        var dv = this.newTag('div', null, 'display:flex;justify-content:space-between;font-size:0.9rem;padding:20px 0;');
        if(this._since){
            var st = this.newTag('span'); st.textContent = this._since;
            dv.appendChild(st);
        }
        if (this._pubType){
            var pt = this.newTag('span'); pt.textContent = this._pubType;
            dv.appendChild(pt);
        }
        this._footer.appendChild(dv);
        
        this.stopBodyScrolling(true);
        $.body.appendChild(this._modal);
        this._modal.style.display = "block";
        this._modal.style.display = "flex";
        if(this._modal.clientWidth<1200){this._card.className='card col-8';}
        //if(this._modal.clientWidth<=1024){this._card.className='card col-12';}

        if(this._card.offsetWidth+30>this._modal.clientWidth){
            this._modal.style.display = "block";
        }
        else if (this._card.offsetHeight+16>this._modal.clientHeight) {
            this._card.style.setProperty('margin-top', (this._card.offsetHeight + 48 - this._modal.clientHeight) + 'px');
        }
        if (!this.premuim) {
            this._ad_slot.style.cssText='display:inline-block;width:'+this._media.offsetWidth+'px;';
            (adsbygoogle = window.adsbygoogle || []).push({});
        }
    }

    close(){
        adScreen._modal.style.display = 'none';
        $.body.removeChild(adScreen._modal);
        adScreen._modal = null;
        adScreen.stopBodyScrolling(false);
        adScreen = null;
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

function sorting(o){
    var idx = o.selectedIndex; 
    console.log(idx);
    console.log(o.value);
    location.href = o.value;
}


window.onclick=function(event){
    if (adScreen && event.target === byId('adScreen'))adScreen.close();
};

window.onpopstate=function(event){
    console.log($.body.dataset.detail);
    if ($.body.hasAttribute('data-detail')) {
        modal.style.display = "none";
        $.body.style.setProperty('overflow', 'scroll');
        $.body.removeAttribute('data-detail');
    }
    else {
        window.history.back();
    }
}


/*
 adScreen._modal.addEventListener("touchstart", function(e){
 if(touchY===null){
 touchY = e.touches[0].pageY;
 }
 });
 adScreen._modal.addEventListener("touchmove", function(e){
 var t=adScreen._modal;
 var delta = touchY - e.touches[0].pageY;
 console.log(delta);
 if(delta>0){
 if(t.scrollHeight <= t.offsetHeight + t.scrollTop + delta){
 console.log("limit down "+(t.scrollHeight - t.offsetHeight - 1));
 t.scrollTop = (t.scrollHeight - t.offsetHeight - 1)+"px";
 e.preventDefault();
 e.stopPropagation();
 }
 }else{
 if(t.scrollTop > 1){
 var y = t.scrollTop + delta;
 if(y < 1) {
 console.log("limit up");
 t.scrollTop = "1px";
 e.preventDefault();
 e.stopPropagation();
 }
 }else{
 e.preventDefault();
 e.stopPropagation();
 }
 }
 return;
 });*/

</script>