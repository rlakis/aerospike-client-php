if(typeof userRaw==='string'){
    var wrapper=$.getElementById("userDIV");
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
    let val=prompt("How many hours would you suspend this user?", 48); 
    if (val!==null){
        let hours=parseInt(val);
        if(Number.isInteger(hours) && hours<5*24){
            let reason=prompt('Please enter user suspension reason:');
            if(confirm('Are you sure you want to suspend this user for '+hours+' hours?')){
                fetch('/ajax-ususpend/', _options('POST', {i:u, v:hours, m:reason===null?'':reason}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if(response.RP===1){location.reload();}
                    })
                    .catch(error => {
                        alert('Error: '+error);
                    });
            }
        }
        else alert('Invalid suspension hours values!');
    }
};


block=function(u,e){
    let modal=$.querySelector('div.body');    
    modal.classList.add('flex');
    console.log(modal.querySelector('style'));
};