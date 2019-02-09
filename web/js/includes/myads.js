<script type="text/javascript">

var articleId=0;
var articles = document.querySelectorAll("article");
var len=articles.length;
for (var x=0; x<len; x++) {
    articles[x].addEventListener("click", function(e){        
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

//var ws = new WebSocket("wss://ws.mourjan.com:1414");
let editors=document.getElementById('editors');
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
            //console.log(uid);
            let article=document.getElementById(data.b[uid]);
            if(article==null){ continue; }
            let header=article.querySelectorAll('header')[0];
            let allocatedTo=header.querySelector('.alloc');
            if(allocatedTo){
                var editor=editors.querySelector('.'+uid);
                if(editor && allocatedTo.innerText!=editor.innerText){
                    allocatedTo.innerHTML='<b>'+editor.innerText+'</b>';
                }                
            }
            
            if(uid==document.body.dataset.key){
            }
            else{
                console.log(data.b[uid]);                
                article.classList.add('locked');
                /*
                if(allocatedTo){                        
                    if(editor){
                        console.log(editor.innerText);
                        allocatedTo.innerHTML=editor.innerText;
                    }
                }*/
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


function mask(e, r){
    var exists=e.querySelector('.mask');
    if(exists){e.removeChild(exists);}
    var d=document.createElement("div");
    d.style.width='100%';//e.offsetWidth+'px';
    d.style.height='100%'; //e.offsetHeight+'px';
    d.style.lineHeight=e.offsetHeight+'px';
    d.className='mask';
    e.appendChild(d);    
    return d;
}


</script>

