<script>
var $=document,ALT=false,MULTI=false;
$.onkeydown=function(e){ALT=(e.which=="18");MULTI=(e.which=="90");if(e.key==='Escape'&&d.slides){d.slides.destroy();d.slides=null;}};
$.onkeyup=function(){ALT=false;MULTI=false;}
$.body.onclick=function(e){d.setId(0);/*let ss=$.querySelector('.slideshow-container');if(ss){ss.parentElement.removeChild(ss);}*/}
createElem=function(tag, className, content, isHtml) {
    var el = document.createElement(tag);
    el.className = className;
    if (typeof content !== 'undefined')
        el[isHtml || false ? 'innerHTML' : 'innerText'] = content;
    return el;
};

var d={
    currentId:0,n:0,panel:null,ad:null,slides:null,
    KUID:$.body.dataset.key,
    pixHost:$.body.dataset.repo,
    su:parseInt($.body.dataset.level)===90,
    level:this.parseInt($.body.dataset.level)===90?9:parseInt($.body.dataset.level),    
    nodes:$.querySelectorAll("article"),
    editors:$.getElementById('editors'),
    ar:$.body.dir==='rtl',
    count:(typeof $.querySelectorAll("article")==='object')?$.querySelectorAll("article").length:0,
    isAdmin:function(){return this.level>=9;},
    setId:function(kId){if(this.ad){this.ad.unselect();};this.currentId=kId;this.ad=new Ad(this.currentId);this.ad.select();},
    getName:function(kUID){var x=this.editors.getElementsByClassName(kUID);return (x && x.length)?x[0].innerText:'Anonymous/'+kUID;},
    inc:function(){this.n++;        
        if(this.panel===null){this.panel=$.querySelector('.adminNB');
            if(this.panel==null){this.panel=createElem("div", 'adminNB');$.body.appendChild(this.panel);}
        }
        if(this.panel){this.panel.innerHTML=this.n+(this.ar?' اعلان جديد':' new ad');this.panel.onclick=function(e){document.location=''};}
    },
    inViewport(e){var cr=e.getBoundingClientRect();return(cr.top>=0&&cr.left>=0&&cr.top<=(window.innerHeight||$.documentElement.clientHeight));},
    visibleAds(f){var v=[];var max=(window.innerHeight||$.documentElement.clientHeight);
        for(var x=0;x<this.count;x++){var cr=this.nodes[x].getBoundingClientRect();if(cr.top>max){break;}
            if(cr.top>=0){if(f>=0){if(this.nodes[x].dataset.fetched==f){v.push(this.nodes[x].id);}}else{v.push(this.nodes[x].id);}}
        }
        return v;
    },

    // ad actions
    approve:function(e){if(this.currentId!=e.parentElement.parentElement.id){return;}
        fetch('/ajax-approve/',{method: 'POST',mode:'same-origin',credentials:'same-origin',body:JSON.stringify({i:parseInt(this.currentId)}),headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', JSON.stringify(response));
                if(response.RP==1){
                    let ad=new Ad(e.parentElement.parentElement.id);
                    ad.approved();
                }
            })
            .catch(error => { console.error('Error:', error); });
    },
    reject:function(e,uid){let article=e.parentElement.parentElement;if(this.currentId!=article.id){return;}
        var form=$.getElementById('rejForm');
        var children=form.children;
        var select=children[0];
        select.innerHTML='';
        //var options=select.children;
        //while(options.firstChild) { options.removeChild(options.firstChild); }
        var cn='en';
        if(article.querySelector('section.ar')){cn='ar';}
        select.className=cn;
        var os=rtMsgs[cn];var len=os.length;
        var g=null;
        for(var i=0;i<len;i++){
            if(os[i].substr(0,6)=='group='){
                g=createElem('optgroup');g.setAttribute('label', os[i].substr(6));
                select.appendChild(g);
            }
            else{
                var o=createElem('option', 'ww', os[i]);o.setAttribute('value', i);
                if(g!=null){g.appendChild(o);}else{select.appendChild(o);}
            }
        }
        if(g!=null){select.appendChild(g);}
        children[1].innerHTML='';
        article.appendChild(form);
        
        select.onchange=function(e){
            if(cn=='ar'||cn=='en'){
                var v=parseInt(this.value);
                var t=$.getElementById('rejT');
                console.log(rtMsgs[cn][v]);
                console.log(t);
                if(v){
                    t.innerHTML=rtMsgs[cn][v];
                    if(rtMsgs[cn][v].match(/[\u0621-\u064a\u0750-\u077f]/)){
                        t.className='ar';
                    }else{
                        t.className='en';
                    }
                }
                else {
                    t.value='';
                }
            }
        };
        form.querySelector('input.btn.cancel').onclick=function(){form.style.display='none';}
        form.querySelector('input.btn.ok').onclick=function(){
            if(!uid)uid=0;            
            console.log(uid);
            console.log(article.id);
            let ad=new Ad(article.id);
            let txt=$.getElementById('rejT').value;
            ad.mask();ad.maskText(txt);
            console.log(txt);
            
            fetch('/ajax-reject/',{method:'POST',mode:'same-origin',credentials:'same-origin',body:JSON.stringify({i:parseInt(article.id),msg:txt,w:uid}),headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', JSON.stringify(response));
                if(response.RP==1){
                    //let ad=new Ad(e.parentElement.parentElement.id);
                    //ad.approved();
                }
            })
            .catch(error => { console.error('Error:', error); });

            form.style.display='none';
            
        }
        
        form.style.display='block';
    },
      /*      
            function arej(i,e,ta,usr){
    if(!usr)usr=0;
    crej(e);
    var d=mask(e);
    $.ajax({
        type:"POST",
        url:"/ajax-reject/",
        data:{i:i,msg:ta.value,w:usr},
        dataType:"json",
        success:function(rp){
            if (rp.RP) {
                d.removeClass("load");
                d.html('Rejected');
            }else {
                d.remove();
                srej(e);
            }
        },
        error:function(){
            d.remove();
            srej(e);
        }
    })
}*/
    slideShow:function(ad,n){
        this.slides=new SlideShow(ad,n);
    },
    ipCheck:function(e){if(e.dataset.fetched){return;}
        let id=e.parentElement.parentElement.parentElement.parentElement.id;        
        fetch('/ajax-changepu/?fraud='+id, {method:'GET',mode:'same-origin',credentials:'same-origin'})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', JSON.stringify(response, undefined, 2));
                let t=e.innerText==='...'?'':e.innerText+'<br>';
                t+='Score: '+response['fraud_score'];
                if(response['mobile'])t+=' | mobile';
                if(response['recent_abuse'])t+=' | abuse';
                if(response['proxy'])t+=' | proxy';
                if(response['vpn'])t+=' | VPN';
                if(response['tor'])t+=' | TOR';
                t+='<br>Country: '+response['country_code']+', '+response['city'];
                t+='<br>Coordinate: '+response['latitude']+', '+response['longitude'];
                t+='<br>IP: '+response['host']+', '+response['ISP'];
                if(response['region'])t+='<br>Region: '+response['region'];
                if(response['timezone'])t+='<br>Timezone: '+response['timezone'];
                if(response['ttl'])t+='<br>TTL: '+response['ttl'];
                e.innerHTML=t;
                e.dataset.fetched=1;
            })
            .catch(error => { console.error('Error:', error); });        
    },
}

class SlideShow{    
    constructor(kAd,_n){        
        this.ad=kAd;
        this.index=parseInt(_n);        
        let self=this;
        this.container=createElem('DIV', 'slideshow');
        let h=createElem('HEADER');
        this.container.appendChild(h);
        let dots=$.createElement('FOOTER');
        
        let close=createElem('SPAN', 'close', '×');
        close.onclick=function(){self.destroy();};
        
        h.appendChild(close);
        for(var i=0;i<this.ad.mediaCount;i++){
            let slide=createElem('DIV', 'mySlides fade', '<img src="'+d.pixHost+'/repos/d/'+this.ad.pixSpans[i].dataset.path+'">', 1);
            this.container.appendChild(slide);
            
            let dot=createElem('SPAN', 'dot');
            dot.onclick=function(){self.current(i+1);};
            dots.appendChild(dot);
        }
        let prev=createElem('A', 'prev', '&#10094;', 1);prev.onclick=function(){self.plus(-1);};
        let next=createElem('A', 'next', '&#10095;', 1);next.onclick=function(){self.plus(1);};
        this.container.appendChild(prev);
        this.container.appendChild(next);
        
        this.container.appendChild(dots);
        $.body.appendChild(this.container);
        this.show(this.index);
    }
    current(n){this.show(this.index=n);}
    plus(n){this.show(this.index+=n);}
    show(n){
        var i;
        var slides=this.container.getElementsByClassName("mySlides");
        var dots=this.container.getElementsByClassName("dot");
        if(n>this.ad.mediaCount){this.index=1}
        if(n<1){this.index=this.ad.mediaCount}
        for(i=0;i<slides.length;i++){slides[i].style.display="none";}
        for(i=0;i<dots.length;i++){dots[i].className=dots[i].className.replace(" active", "");}
        slides[this.index-1].style.display="flex";
        dots[this.index-1].className += " active";
    }
    destroy(){$.body.removeChild(this.container);}
}

for(var x=0;x<d.count;x++){
    //d.nodes[x].oncontextmenu=function(e){e.preventDefault();};
    d.nodes[x].onclick=function(e){
        //console.log(e.target.tagName + ' ['+this.id+' vs '+ d.currentId+']');
        if(this.id==d.currentId){
            var tagName=e.target.tagName;var parent=e.target.parentElement;
            if(tagName==='A'){
                if(e.target.className===''&&parent.className==='mask'){
                    e.stopPropagation();
                    return;
                }
                if(parent.tagName==='FOOTER'){e.stopPropagation();return;}
            }
            else if(tagName==='DIV'){
                if(e.target.className==='mask'||this.classList.contains('locked')){
                    e.preventDefault();e.stopPropagation();
                    return false;
                }
            }
        }
        if(d.currentId!=this.id){d.setId(this.id);}
        e.preventDefault();e.stopPropagation();
        return false;
    };       
}

class Ad{    
    constructor(kId){
        this._m=null;
        this.id=parseInt(kId);
        this._node=$.getElementById(kId);        
        if(this._node!==null){
            this._header=this._node.querySelectorAll('header')[0];
            this._editor=this._header.querySelector('.alloc');
            this._message=this._header.querySelector('.msg');            
            this.dataset=this._node.dataset;
            this.mediaCount=0;
            var wrp=this._node.querySelector('p.pimgs');
            if(wrp){this.pixSpans=wrp.querySelectorAll('span');
                if(this.pixSpans&&this.pixSpans.length){this.mediaCount=this.pixSpans.length;}
            }
        }
    }
    exists(){return this._node!==null;}
    dataset(){return this._node.dataset;}
    header(){return this._header;}    
    getName(){return this._editor.innerText;}
    setName(v){if(!this._editor.innerText.includes(v)){this._editor.innerHTML=v+'/'+this._editor.innerText;}}
    replName(v){this._editor.innerHTML=v;}
    getMessage(){return this._message.innerText;}
    setMessage(v){this._message.innerHTML=v;}
    select(){if(this.exists()){this.setAs('selected');socket.emit("touch",[this.id,d.KUID]);}}
    unselect(){if(this.exists()){this.unsetAs('selected');socket.emit("release",[this.id,d.KUID]);}}
    lock(){this.setAs('locked');this.mask();this.opacity(0.25);}
    release(){this.unsetAs('locked');this.removeMask();}
    setAs(c){this._node.classList.add(c);}
    unsetAs(c){this._node.classList.remove(c);}
    rejected(t){this.unsetAs('approved');this.setAs('rejected');this.setMessage(t);}
    approved(){this.unsetAs('rejected');this.setAs('approved');}
    fetchPics(){if(this.mediaCount>0){
            let _=this;
            for(var i=0;i<_.mediaCount;i++){                
                if(typeof _.pixSpans[i].dataset.pix==='string'){
                    let j=JSON.parse(_.pixSpans[i].dataset.pix);
                    _.pixSpans[i].dataset.path=j.p;
                    _.pixSpans[i].dataset.width=j.w;
                    _.pixSpans[i].dataset.height=j.h;
                }
                
                var img=new Image();
                img.src = d.pixHost+'/repos/s/'+_.pixSpans[i].dataset.path;
                img.dataset.n=i+1;
                img.onclick=function(){d.slideShow(_,this.dataset.n);}
                _.pixSpans[i].appendChild(img);
                var del=createElem("div", 'del', '<i class="icn icnsmall icn-minus-circle"></i>', 1);
                del.onclick=function(){                        
                    var answer=window.confirm(d.ar?"هل انت اكيد من حذف هذه الصورة من الاعلان؟":"Do you really want to remove this picture?");
                    if(answer){                                       
                        fetch('/ajax-changepu/?img=1&i='+_.id+'&pix='+this.parentElement.dataset.path,{method:'GET',mode:'same-origin',credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json'}})
                            .then(response=>{return response.json();}).then(data=>{
                                let holder=this.parentElement.parentElement;
                                if(data.RP===1){
                                    holder.removeChild(this.parentElement);
                                    if(holder.childNodes.length==0){holder.parentElement.removeChild(holder);}
                                }
                            }).catch(err=>{
                                console.log(err);
                            });
                    }
                }
                _.pixSpans[i].appendChild(del);
            }
            
        }
        this.dataset.fetched=1;
    }
    mask(){var _=this;
        _._m=_._node.querySelector('div.mask');
        if(_._m===null){
            _._m=createElem("div", 'mask');
            var h=_._node.offsetHeight-60;
            _._m.style.lineHeight=h+'px';
            _._node.appendChild(this._m);
        }
    }
    removeMask(){this._m=this._node.querySelector('div.mask');if(this._m){this._node.removeChild(this._m);this._m=null;}}
    maskText(t){this._m.innerHTML=t;}
    opacity(v){this._m.style.opacity=v;}
}

const socket=io.connect('ws.mourjan.com:1313',{transports:['websocket'],'force new connection':false});
socket.on('admins',function(data){
    active_admins=data.a;
    if(isNaN(active_admins)){
        let len=active_admins.length;let matched=[];
        //console.log('on<admins>/isNaN: Active Admins:'+len);
        for(var i=0;i<len;i++){
            if(d.editors){
                var x=d.editors.getElementsByClassName(active_admins[i]);
                if(x&&x.length>0){
                    matched.push(x[0].className);
                    if(d.su){
                        x[0].firstChild.style.setProperty('color', 'green', 'important');
                    }
                    else x[0].style.setProperty('color', 'green', 'important');
                }
            }
        }
        len=d.editors?d.editors.childNodes.length:0;
        for(var i=0;i<len;i++){
            if(matched.indexOf(d.editors.childNodes[i].className)==-1){
                editors.childNodes[i].style.removeProperty('color');
            }
        }
        matched=null;        
    }
    else {
        console.log('on<admins>: Active Admins:'+active_admins);
    }
    
    if(typeof data.b==='object'){
        console.log(data.b);
        for(uid in data.b){if(data.b[uid]===0){continue;}
            let ad=new Ad(data.b[uid]);
            if(ad.exists()){
                ad.replName(d.getName(uid));            
                if(uid==d.KUID){ad.select();}else{ad.lock();}
            }
        }
    }
});
socket.on("ad_touch",function(data){console.log('touched', data);
    if(data.hasOwnProperty('x')){
        if(data.hasOwnProperty('i')&&data.i>0){
            let ad=new Ad(data.i);if(ad.exists()){ad.setName(d.getName(data.x));ad.lock();}                        
        }
        if(data.hasOwnProperty('o')&&data.o>0){let ad=new Ad(data.o);if(ad.exists()){ad.release();}}
    } 
});
socket.on("ad_release",function(data){console.log('releasing', data);
    if(data.hasOwnProperty('i')&&data.i>0){let ad=new Ad(data.i);if(ad.exists()){ad.release();}}
});
socket.on("ads",function(data){
    if(typeof data.c=='undefined'){return;}
    data.c=parseInt(data.c);    
    let ad=new Ad(data.id);
    if(ad.exists()){//console.log('ads', data);             
        var t;
        if (ad.dataset.status>=0||c.data==-1){
            switch(data.c){
                case -1:
                case 6:
                    t=d.ar?'تم الحذف':'Deleted';
                    ad.setMessage(t);
                    break;
                case 0:
                    t=d.ar?'جاري التعديل، يجب تحديث الصفحة':'editting in progress, refresh page';
                    break;
                case 1:
                    t=d.ar?'بإنتظار موافقة النشر من قبل محرري الموقع':'Waiting for Editorial approval';
                    break;
                case 2:
                    t=d.ar?'تمت الموافقة وبإنتظار العرض من قبل محرك مرجان':'Approved and pending Mourjan system processing';
                    ad.setMessage(t);ad.setAs('approved');
                    break;
                case 3:              
                    t=d.ar?'تم رفض عرض هذا الإعلان':'Rejected By Admin';
                    if(typeof data.m!=='undefined'){t+=': '+data.m;}                    
                    ad.rejected(t);
                    break;
                case 7:
                    ad.mask();ad.opacity(0.75);
                    var link;
                    if(d.isAdmin()){
                        var lnks=ad._node.querySelectorAll('div.user > a');
                        if(lnks && lnks.length>1){link=lnks[1].href+'#'+ad.id;}
                    }
                    else{link='/myads/'+(d.ar?'':'en/')+'#'+ad.id;}
                    t=d.ar?'الإعلان أصبح فعالاً، <a href="'+link+'">انقر(ي) هنا</a> لتفقد الإعلانات الفعالة':'Ad is online now, <a href="'+link+'">click here</a> to view Active Ads';
                    ad.maskText(t);
                    break;
            }
        }        
    }
    if(data.c===1){d.inc();}
});
socket.on('superAdmin',function(data){console.log(data);if(typeof data!=='undefined'){}});
socket.on('reconnect',function(){console.log('Reconnnect to ws');});   
socket.on('connect',function(){console.log('connnect to ws');if(d.KUID){this.emit("hook_myads",[d.KUID,d.level]);}});
socket.on('disconnect',function(){console.log('disconnect from ws');});
socket.on('event',function(data){console.log('event')});

window.onscroll=function(){var nodes=d.visibleAds(0);let len=nodes.length;for(var i=0;i<len;i++){let ad=new Ad(nodes[i]);ad.fetchPics();}}
window.onload=function(){this.onscroll();}

</script>