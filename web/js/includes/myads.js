<script type="text/javascript">

var articleId=0;
var articles = document.querySelectorAll("article");
var len=articles.length;
for (var x=0; x<len; x++) {
    articles[x].addEventListener("click", function(e){        
        if(articleId!=this.id){
            console.log('article '+e.target.tagName);
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
    console.log('body '+e.target.tagName)
    if (articleId>0) {
        document.getElementById(articleId).classList.remove('selected');
        articleId=0;
    }
});

function EAD(){}

//var ws = new WebSocket("wss://ws.mourjan.com:1414");

const options = {transports: ['websocket'], 'force new connection': false};
const socket = io.connect("ws.mourjan.com:1313", options);
socket.on('admins',function(data){
    active_admins=data.a;
    if(isNaN(active_admins)){        
        let editors=document.getElementById('editors');
        let len=active_admins.length;
        let matched=[];
        console.log('on<admins>/isNaN: Active Admins:'+len);
        for(var i=0;i<len;i++){
            var x = editors.getElementsByClassName(active_admins[i]);            
            if (x && x.length) {
                matched.push(x[0].className);
                x[0].firstChild.style.setProperty('color', 'green', 'important');
            }
        }
        len=editors.childNodes.length;
        for(var i=0; i<len; i++) {            
            if (matched.indexOf(editors.childNodes[i].className)==-1) {
                editors.childNodes[i].firstChild.style.removeProperty('color');
            }
        }
        matched=null;        
    }
    else {
        console.log('on<admins>: Active Admins:'+active_admins);
    }
    
    if(typeof data.b!=='undefined'){
        console.log(data.b);
        for(uid in data.b){
            console.log(uid);
            if(uid==document.body.dataset.key){
            }
            else{
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
    //li.removeClass('owned used');
    //wio.emit("hook_myads",[UIDK,ULV]);
});
      
socket.on('connect',function(){
    console.log('connnect to ws');
    //li.removeClass('owned used');
    if (document.body.dataset.key) {
        this.emit("hook_myads", [document.body.dataset.key, document.body.dataset.level]);
    }
});

socket.on('disconnect',function(){
    console.log('disconnect from ws');
    //li.removeClass('owned used');
    //wio.emit("hook_myads",[UIDK,ULV]);
});
socket.on('event', function(data){console.log('event')});


</script>

