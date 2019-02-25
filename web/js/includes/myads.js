<script>
var $=document,ALT=false,MULTI=false;
$.onkeydown=function(e){
    ALT=(e.which=="18");
    MULTI=(e.which=="90");
    if(e.key==='Escape'&&d.slides){d.slides.destroy();d.slides=null;}
}
$.onkeyup=function(){
    ALT=false;MULTI=false;
}
$.body.onclick=function(e){
    let editable=$.querySelectorAll("[contenteditable=true]");
    if(editable&&editable.length>0){
        editable[0].setAttribute("contenteditable", false);
        d.normalize(editable[0]);   
    }
    d.setId(0);  
}
createElem=function(tag, className, content, isHtml) {
    var el = document.createElement(tag);
    el.className = className;
    if (typeof content !== 'undefined')
        el[isHtml || false ? 'innerHTML' : 'innerText'] = content;
    return el;
}
dirElem=function(e){
    var v=e.value;
    if(!v){e.className='';}else{e.className=(v.match(/[\u0621-\u064a\u0750-\u077f]/))?'ar':'en';}
}
_options=function(m,d){
    if(m.toUpperCase()==='POST'){
        return {method: 'POST', mode: 'same-origin', credentials: 'same-origin',
            body:JSON.stringify(d), headers: {'Accept':'application/json','Content-Type':'application/json'}
        };
    }
}


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

    lookup:function(e){       
        var selection=null;
        if (window.getSelection) {
            selection=window.getSelection().toString();
        } else if (document.selection) {
            selection=document.selection.createRange().text;
        }
        if(selection){
            let revise=$.getElementById('revise');
            if(revise){                
                let q=revise.dataset.contact;
                if(selection.split(' ').length>1) {
                    q+=' "'+selection+'"';
                }
                else {
                    q+=' '+selection;
                }
                let url=(this.ar?'/':'/en/')+'?cmp='+e.parentElement.id+'&q='+q;
                window.open(url,'_similar');
            }
        }
    },
            
    textSelected:function(e){
        if (window.getSelection) {
            selection=window.getSelection().toString();
        } else if (document.selection) {
            selection=document.selection.createRange().text;
        }
    },
            
    // ad actions
    approve:function(e,rtpFlag){if(this.currentId!=e.parentElement.parentElement.id){return;}
        var data={i:parseInt(this.currentId)};
        if(typeof rtpFlag!=='undefined'){data['rtp']=rtpFlag}
        fetch('/ajax-approve/', _options('POST', data))
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
            
    getForm:function(prefix, moveTo){
        var form=$.getElementById(prefix+'Form');
        let select=form.querySelector('select');
        if(prefix==='rej'){select.innerHTML='';}
        let text=$.getElementById(prefix+'T');
        let ok=form.querySelector('input.btn.ok');
        let cancel=form.querySelector('input.btn.cancel');        
        if(text){text.value=''}        
        if(typeof cancel!=='function'){cancel.onclick=function(){form.style.display='none'}}
        if(moveTo){moveTo.appendChild(form)}
        return {'form':form, 'select':select, 'text':text, 'ok':ok, 
            'show':function(){form.style.display='block'}, 
            'hide':function(){form.style.display='none'}
        };        
    },
    
    rtp:function(e){
        var answer=window.confirm("Do you really want to ask Real Time Password verification?");
        if(answer){
            this.approve(e,2);
        }
    },
    
    reject:function(e,uid){let article=e.parentElement.parentElement;if(this.currentId!=article.id){return;}
        var inline=this.getForm('rej', article);
        var cn='en';
        if(article.querySelector('section.ar')){cn='ar';}
        inline.select.className=cn;
        var os=rtMsgs[cn];var len=os.length;
        var g=null;
        for(var i=0;i<len;i++){
            if(os[i].substr(0,6)=='group='){
                g=createElem('optgroup');g.setAttribute('label', os[i].substr(6));
                inline.select.appendChild(g);
            }
            else{
                var o=createElem('option', '', os[i]);o.setAttribute('value', i);
                if(g!=null){g.appendChild(o);}else{inline.select.appendChild(o);}
            }
        }
        if(g!=null){inline.select.appendChild(g);}
        
        inline.select.onchange=function(e){
            if(cn=='ar'||cn=='en'){
                var v=parseInt(this.value);
                inline.text.value='';
                if(v){
                    inline.text.value=rtMsgs[cn][v];
                    inline.text.className=(rtMsgs[cn][v].match(/[\u0621-\u064a\u0750-\u077f]/))?'ar':'en';
                }
            }
        };
        inline.ok.onclick=function(){
            if(!uid)uid=0;            
            let ad=new Ad(article.id, inline.text.value);
            
            fetch('/ajax-reject/', _options('POST', {i:parseInt(article.id),msg:inline.text.value,w:uid}))
                .then(res=>res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    if(response.RP==1){
                        //let ad=new Ad(e.parentElement.parentElement.id);
                        //ad.approved();
                    }
                })
                .catch(error => { 
                    console.error('Error:', error); 
                    ad.removeMask();
                });
            inline.hide();
        }
        inline.show();
    },
            
    suspend:function(e,uid){let article=e.parentElement.parentElement;if(this.currentId!=article.id){return;}
        var inline=this.getForm('susp', article);
        if(inline.select.childNodes.length===0){
            let o=createElem('option', '', d.ar?'ساعة':'1 hour');o.setAttribute('value',1);
            inline.select.appendChild(o);
            for(var i=6;i<=72;i=i+6){
                if(i>48 && d.su){ break; }
                let o=createElem('option', '', i+(d.ar?' ساعة':' hour'));o.setAttribute('value',i);
                inline.select.appendChild(o);
            }
        }
        
        inline.ok.onclick=function(){
            if(!uid)uid=0;
            let ad=new Ad(article.id,inline.text.value);
                
            fetch('/ajax-ususpend/', _options('POST', {i:uid,v:inline.select.value,m:inline.text.value?inline.text.value:''}))
                .then(res=>res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    if(response.RP==1){
                        //let ad=new Ad(e.parentElement.parentElement.id);
                        //ad.approved();
                    }
                })
                .catch(error => { 
                    console.error('Error:', error); 
                    ad.removeMask();
                });
            inline.hide();
        }
        inline.show();
    },
    
    ban:function(e,uid){let article=e.parentElement.parentElement;if(this.currentId!=article.id){return;}
        var inline=this.getForm('ban',article);
        inline.ok.onclick=function(){
            if(!uid)uid=0;            
            let ad=new Ad(article.id,inline.text.value);
            fetch('/ajax-ublock/', _options('POST', {i:uid,msg:inline.text.value}))
                .then(res=>res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    if(response.RP==1){ad.maskText('User Account Blocked');}
                })
                .catch(error => { 
                    console.error('Error:', error); 
                    ad.removeMask();
                });
            inline.hide();
        }
        inline.show();
    },
    
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
    
    normalize:function(e){
        let data={dx:e.dataset.foreign?2:1, rtl:e.classList.contains('ar')?1:0, t:e.innerText};
        console.log(data);
        fetch('/ajax-changepu/?i='+this.currentId, _options('POST', data)).then(res=>res.json())
            .then(response => {
                console.log(response);
                if(response.DATA.dx==1 && response.DATA.t){
                    e.innerHTML=response.DATA.t;
                    console.log('done');
                }
            })
            .catch(error => { 
                console.log(error); 
            });
    }
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


class Ad{    
    constructor(kId, kMaskMessage){
        this._m=null;
        this.ok=false;
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
            if(kMaskMessage){this.mask();this.maskText(kMaskMessage);}
            this.ok=true;
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
    release(){if(this.ok){this.unsetAs('locked');this.removeMask()}}
    setAs(c){this._node.classList.add(c);}
    unsetAs(c){this._node.classList.remove(c);}
    rejected(t){this.unsetAs('approved');this.setAs('rejected');this.setMessage(t);}
    approved(){this.unsetAs('rejected');this.setAs('approved');}
    fetchPics(){if(this.mediaCount>0){
            let _=this;
            for(var i=0;i<_.mediaCount;i++){                
                if(typeof _.pixSpans[i].dataset.pix==='string'){
                    try{
                        let j=JSON.parse(_.pixSpans[i].dataset.pix);
                        _.pixSpans[i].dataset.path=j.p;
                        _.pixSpans[i].dataset.width=j.w;
                        _.pixSpans[i].dataset.height=j.h;
                    }
                    catch(error){
                        console.error(error);
                        continue;
                    }
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
    mask(){var _=this;_._m=_._node.querySelector('div.mask');if(_._m===null){_._m=createElem("div", 'mask');_._node.appendChild(_._m);}}
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
        //console.log(data.b);
        for(uid in data.b){if(data.b[uid]===0){continue;}
            let ad=new Ad(data.b[uid]);
            if(ad.exists()){
                ad.replName(d.getName(uid));            
                if(uid==d.KUID){ad.select();}else{ad.lock();}
            }
        }
    }
});
socket.on("ad_touch",function(data){//console.log('touched', data);
    if(data.hasOwnProperty('x')){
        if(data.hasOwnProperty('i')&&data.i>0){
            let ad=new Ad(data.i);if(ad.exists()){ad.setName(d.getName(data.x));ad.lock();}                        
        }
        if(data.hasOwnProperty('o')&&data.o>0){let ad=new Ad(data.o);if(ad.exists()){ad.release();}}
    } 
});
socket.on("ad_release",function(data){//console.log('releasing', data);
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

for(var x=0;x<d.count;x++){
    //d.nodes[x].oncontextmenu=function(e){e.preventDefault();};
    d.nodes[x].onclick=function(e){
        if(this.id==d.currentId){
            var tagName=e.target.tagName;var parent=e.target.parentElement;
            if(tagName==='A'){
                if((e.target.className===''&&parent.className==='mask')||e.target.target=='_similar'){
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
            if(tagName==='SECTION'){
                console.log('section clicked');
                if(ALT&&!e.target.isContentEditable){
                    var re=/\u200b/;var parts=e.target.innerText.split(re);
                    if(parts.length===2){
                        e.target.dataset.contacts=parts[1];
                        e.target.contentEditable="true";
                        e.target.innerHTML=parts[0].trim();
                        e.target.focus();
                    }                
                }
                e.preventDefault();
                e.stopPropagation();                
                return;
            }
        }
        if(d.currentId!=this.id){d.setId(this.id);}
        let editable=$.querySelectorAll("[contenteditable=true]");
        if(editable&&editable.length>0){
            editable[0].setAttribute("contenteditable", false);
            d.normalize(editable[0]);   
        }
        e.preventDefault();e.stopPropagation();
        return false;
    };
}

</script>