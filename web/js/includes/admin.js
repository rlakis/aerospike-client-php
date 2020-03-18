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
    Swal.fire({
        title: 'Block User',
        text: 'You will ban this user from posting any advertisement!',
        input: 'textarea',
        inputPlaceholder: 'Type the purpose of banning here...',       
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, block him!',
        cancelButtonText: 'No, dismiss',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to write something!'
            }
        }
    }).then((result) => {
        if (result.value) {
            console.log(result);
            
            fetch('/ajax-ublock/', _options('POST', {i:u, msg:result.value}))
                .then(res => res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    //if(response.RP===1){location.reload();}
                })
                .catch(error => {
                    Swal.fire('Error', error, 'error');
                });
            //Swal.fire('Deleted!', 'Your imaginary file has been deleted.', 'success' )
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            //Swal.fire('Cancelled', 'Your imaginary file is safe :)', 'error')
        }
    });
};