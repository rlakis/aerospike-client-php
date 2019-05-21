if(typeof userRaw==='string'){
    var wrapper = document.getElementById("userDIV");
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
