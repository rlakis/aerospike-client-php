<script type="text/javascript">

var $=document;
var articleId=0;
var articles = document.querySelectorAll("article");
var len=articles.length;
for (var x=0; x<len; x++) {
    articles[x].addEventListener("click", function(e){          
        //console.log(this.classList.contains('locked'));
        
        if(e.target.tagName==='A'&&e.target.className===''&&e.target.parentElement.className==='mask'){            
            console.log('article '+e.target.parentElement.nodeName+' > '+e.target.parentElement.className);
            e.stopPropagation();
            return;
        }
        if((e.target.tagName==='DIV'&&e.target.className==='mask')||this.classList.contains('locked')){
            console.log("sdgkjfskdjhfkjsdhfkjshfjkd");
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(articleId!=this.id){
            //console.log('article '+e.target.tagName+', '+e.target.className+', parent: '+e.target.parentElement.nodeName);
            if(e.target.tagName==='A'&&e.target.className===''){
                console.log('article '+e.target.parentElement.nodeName+' > '+e.target.parentElement.className);
            }
            
            if(articleId>0){
                document.getElementById(articleId).classList.remove('selected');
            }
            articleId=this.id;
            this.classList.add("selected");
        }
        
        e.preventDefault();   
        e.stopPropagation();
        return false;
    });        
}

document.body.addEventListener("click", function(e){
    //console.log('body '+e.target.tagName)
    if (articleId>0) {
        document.getElementById(articleId).classList.remove('selected');
        articleId=0;
    }
});

function EAD(){}

function getArticle(kID){
    return $.getElementById(kID);
}


function Ad(kID){
    this.node=$.getElementById(kID);
    this.headerNode=null;
    this.editorNode=null;
}
Ad.prototype = {
    exists:function(){return(this.node!==null);},
    header:function(){        
        if(this.headerNode===null){
            this.headerNode=this.node.querySelectorAll('header')[0];
            this.editorNode=this.headerNode.querySelector('.alloc');
        }
        return this.headerNode;
    },
    editor:function(){
        if(this.editorNode===null){this.header();}
        return this.editorNode;
    },
    getName:function(){
        return this.editor().innerText;
    },
    setName: function(value){        
        if(this.editor().innerText!=value){
            this.editor().innerHTML=value+'/'+this.editor().innerText;
        }
    },
    replName:function(value){
        if(this.editor().innerText!=value){
            this.editor().innerHTML='<b>'+value+'</b>';
        }
    },
    setSelected:function(){this.node.classList.add('selected');},
    setLocked:function(){
        this.node.classList.add('locked');
        this.addMask();
        
    },
    setUnLocked:function(){
        this.node.classList.remove('locked');
        this.removeMask();
    },
    addMask:function(){
        var mask=this.node.querySelector('.mask');
        if (mask==null){
            mask=document.createElement("div");
            mask.style.lineHeight=this.node.offsetHeight+'px';
            mask.className='mask';
            this.node.appendChild(mask);    
        }
        return mask;
    },
    removeMask:function(){
        var mask=this.node.querySelector('.mask');
        if (mask){this.node.removeChild(mask);}
    },
    
}

var editors=document.getElementById('editors');
const options = {transports: ['websocket'], 'force new connection': false};
const socket = io.connect("ws.mourjan.com:1313", options);
socket.on('admins',function(data){
    active_admins=data.a;
    if(isNaN(active_admins)){
        let len=active_admins.length;
        let matched=[];
        console.log('on<admins>/isNaN: Active Admins:'+len);
        for(var i=0;i<len;i++){
            var x=editors.getElementsByClassName(active_admins[i]);
            if(x && x.length){
                matched.push(x[0].className);
                x[0].firstChild.style.setProperty('color', 'green', 'important');
            }
        }
        len=editors.childNodes.length;
        for(var i=0;i<len;i++){            
            if(matched.indexOf(editors.childNodes[i].className)==-1){
                editors.childNodes[i].firstChild.style.removeProperty('color');
            }
        }
        matched=null;        
    }
    else {
        console.log('on<admins>: Active Admins:'+active_admins);
    }
    
    if(typeof data.b!=='undefined'){
        console.log('data.b');
        console.log(data.b);
        for(uid in data.b){
            if(data.b[uid]===0){continue;}
            let ad=new Ad(data.b[uid]);
            if(!ad.exists()){continue;}
            ad.replName(editorName(uid));            
            
            if(uid==document.body.dataset.key){
                ad.setSelected();
            }
            else{
                console.log(data.b[uid]);  
                ad.setLocked();
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


socket.on("ad_touch",function(data){
    console.log('touched ', data);
    if(data.hasOwnProperty('x')){
        if(data.hasOwnProperty('i') && data.i>0){
            console.log('touched by', editorName(data.x));
            let ad=new Ad(data.i);
            if(ad.exists()){
                ad.setName(editorName(data.x));
                ad.setLocked();
            }
        }
        if(data.hasOwnProperty('o') && data.o>0){
            console.log('release touched by', editorName(data.x));
            let ad=new Ad(data.o);
            if(ad.exists()){
                //ad.setName(editorName(data.x));
                ad.setUnLocked();
            }
        }

    }
    
});

socket.on("ad_release",function(data){
    console.log('releasing ', data);
    if(data.hasOwnProperty('i') && data.i>0){
        let ad=new Ad(data.i);
        if(ad.exists()){ad.setUnLocked();}
    }
});

socket.on("ads",function(data){
    if(typeof data.c=='undefined'){ return; }
    data.c=parseInt(data.c);
    let article=document.getElementById(data.id);
    let ar=(document.body.dir==='rtl');
    if(article){        
        console.log(data);
        let header=article.querySelectorAll('header')[0];        
        article.dataset.allocatedTo=header.lastChild.innerText;
        var t;
        var hmsg=header.querySelectorAll('.msg')[0];
        if (article.dataset.status>=0||c.data==-1){
            switch(data.c){
                case -1:
                case 6:
                    t=ar?'تم الحذف':'Deleted';
                    break;
                case 0:
                    t=ar?'جاري التعديل، يجب تحديث الصفحة':'editting in progress, refresh page';
                    break;
                case 1:
                    t=ar?'بإنتظار موافقة النشر من قبل محرري الموقع':'Waiting for Editorial approval';
                    break;
                case 2:
                    t=ar?'تمت الموافقة وبإنتظار العرض من قبل محرك مرجان':'Approved and pending Mourjan system processing';
                    hmsg.textContent=t;
                    article.classList.add('approved');
                    break;
                case 3:              
                    t=ar?'تم رفض عرض هذا الإعلان':'Rejected By Admin';
                    if(typeof data.m!=='undefined'){t+=': '+data.m;}
                    hmsg.innerHTML=t;
                    article.classList.add('rejected');
                    break;
                case 7:
                    var lockedMask=mask(article,1);
                    var link;
                    if(document.body.dataset.level==9){
                        var lnks=article.querySelectorAll('div.user > a');
                        if(lnks && lnks.length>1){link=lnks[1].href+'#'+article.id;}
                    }
                    else{
                        link='/myads/'+(ar?'':'en/')+'#'+article.id;
                    }
                    t=ar?'الإعلان أصبح فعالاً، <a href="'+link+'">انقر(ي) هنا</a> لتفقد الإعلانات الفعالة':'Ad is online now, <a href="'+link+'">click here</a> to view Active Ads';
                    lockedMask.innerHTML=t;
                    break;
            }
        }
        else if(data.c===1){
        }
    }
});

socket.on('superAdmin',function(data){
    console.log(data);
    if(typeof data!=='undefined'){
        //var li=$('#'+data.id);
        //if(li.length){
        //    var d=mask(li[0],1);
        //    d.html('Sent To Super Admin');
        //}
    }
});

socket.on('reconnect',function(){
    console.log('Reconnnect to ws');
});
      
socket.on('connect',function(){
    console.log('connnect to ws');
    if(document.body.dataset.key){this.emit("hook_myads",[document.body.dataset.key,document.body.dataset.level]);}
});

socket.on('disconnect',function(){
    console.log('disconnect from ws');
    //li.removeClass('owned used');
    //wio.emit("hook_myads",[UIDK,ULV]);
});
socket.on('event', function(data){console.log('event')});




function editorName(kUID){
    var x=editors.getElementsByClassName(kUID);
    if(x && x.length){
        return x[0].innerText;
    }
    return 'Anonymous/'+kUID;
}


function mask(e,r){
    var exists=e.querySelector('.mask');
    if(exists){e.removeChild(exists);}
    var d=document.createElement("div");
    d.style.lineHeight=e.offsetHeight+'px';
    d.className='mask';
    e.appendChild(d);    
    return d;
}


</script>

