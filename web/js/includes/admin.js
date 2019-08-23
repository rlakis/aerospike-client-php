let $=document;
if(typeof userRaw==='string'){
    var wrapper = $.getElementById("userDIV");
    try {
        var data = JSON.parse(userRaw); 
        console.log(data);
        setTimeout(function(){
            var tree = jsonTree.create(data, wrapper);
            tree.expand();
        }, 100);
        if(typeof jsonTree==='undefined'){
            
        }
        
    } catch (e) {
        console.log(e);
    }
}


suspend=function(u,e){
    console.log(u, e);
};

block=function(u,e){
    let modal=$.querySelector('div.body');    
    modal.classList.add('flex');
    console.log(modal.querySelector('style'));
};