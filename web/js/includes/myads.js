<script type="text/javascript">

var $=document;
var ALT=false,MULTI=false;
$.body.onkeydown=function(e){ALT=(e.which=="18");MULTI=(e.which=="90");};
$.body.onkeyup=function(){ALT=false;MULTI=false;}
$.body.onclick=function(e){d.setId(0);}

var d={
    currentId:0,n:0,newPanel:null,ad:null,
    KUID:$.body.dataset.key,
    su:parseInt($.body.dataset.level)===90,
    level:this.parseInt($.body.dataset.level)==90?9:parseInt($.body.dataset.level),    
    nodes:$.querySelectorAll("article"),
    editors:$.getElementById('editors'),
    ar:$.body.dir==='rtl',
    count:(typeof $.querySelectorAll("article")==='object')?$.querySelectorAll("article").length:0,
    isAdmin:function(){return this.level>=9;},
    setId:function(kId){
        if(this.ad){this.ad.unselect();}
        this.currentId=kId;
        this.ad=new Ad(this.currentId);
        this.ad.select();
    },
    getName:function(kUID){
        var x=this.editors.getElementsByClassName(kUID);
        return (x && x.length)?x[0].innerText:'Anonymous/'+kUID;
    },
    inc:function(){
        this.n++;        
        if(this.newPanel===null){
            this.newPanel=$.querySelector('.adminNB');
            if(this.newPanel==null){
                this.newPanel=$.createElement("div");
                this.newPanel.className='adminNB';
                $.body.appendChild(this.newPanel);    
            }
        }
        if(this.newPanel){this.newPanel.innerHTML=this.n+(this.ar?' اعلان جديد':' new ad');this.newPanel.onclick=function(e){document.location=''};}
    }
}

for(var x=0;x<d.count;x++){
    d.nodes[x].oncontextmenu=function(e){e.preventDefault();};
    d.nodes[x].onclick=function(e){          
        if(e.target.tagName==='A'&&e.target.className===''&&e.target.parentElement.className==='mask'){            
            e.stopPropagation();
            return;
        }
        if((e.target.tagName==='DIV'&&e.target.className==='mask')||this.classList.contains('locked')){
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(d.currentId!=this.id){d.setId(this.id);}        
        e.preventDefault();   
        e.stopPropagation();
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
    mask(){this._m=this._node.querySelector('div.mask');
        if(this._m===null){
            this._m=$.createElement("div");var h=this._node.offsetHeight-60;
            this._m.style.lineHeight=h+'px';this._m.style.height=h+'px';
            this._m.className='mask';this._node.appendChild(this._m);
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
            var x=d.editors.getElementsByClassName(active_admins[i]);
            if(x&&x.length>0){
                matched.push(x[0].className);
                if(d.su){
                    x[0].firstChild.style.setProperty('color', 'green', 'important');
                }
                else x[0].style.setProperty('color', 'green', 'important');
            }
        }
        len=editors.childNodes.length;
        for(var i=0;i<len;i++){
            if(d.editors.childNodes.length>0 && matched.indexOf(d.editors.childNodes[i].className)==-1){
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
        for(uid in data.b){
            if(data.b[uid]===0){continue;}
            let ad=new Ad(data.b[uid]);
            if(ad.exists()){
                ad.replName(d.getName(uid));            
                if(uid==d.KUID){
                    ad.select();
                }
                else{
                    //console.log(data.b[uid]);  
                    ad.lock();
                }
            }
        }
        /*
        var p=li.parent();
        var cn,g;
        for (var i in data.b){
            g=$('#'+data.b[i],p);
            if(i==UIDK) {
                cn='owned';
                ownad=data.b[i];
            }
            else {
                cn='used';
            }
            g.addClass(cn);
            if(i!=UIDK){
                var n=data.c[i]?data.c[i]:'Anonymous '+i;
                setOwner(n,g);
            }
        }*/
    }
});
socket.on("ad_touch",function(data){console.log('touched', data);
    if(data.hasOwnProperty('x')){
        if(data.hasOwnProperty('i') && data.i>0){
            let ad=new Ad(data.i);
            if(ad.exists()){
                ad.setName(d.getName(data.x));
                ad.lock();
            }                        
        }
        if(data.hasOwnProperty('o') && data.o>0){
            let ad=new Ad(data.o);
            if(ad.exists()){ad.release();}
        }
    } 
});
socket.on("ad_release",function(data){console.log('releasing', data);
    if(data.hasOwnProperty('i')&&data.i>0){let ad=new Ad(data.i);if(ad.exists()){ad.release();}}
});
socket.on("ads",function(data){
    if(typeof data.c=='undefined'){return;}
    data.c=parseInt(data.c);    
    let ad=new Ad(data.id);
    if(ad.exists()){
        //console.log('ads', data);             
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
                    ad.setMessage(t);
                    ad.setAs('approved');
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
                        if(lnks && lnks.length>1){link=lnks[1].href+'#'+ad._node.id;}
                    }
                    else{
                        link='/myads/'+(d.ar?'':'en/')+'#'+ad._node.id;
                    }
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
socket.on('event', function(data){console.log('event')});

</script>