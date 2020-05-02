buy=function(g,e) {
    let ready=e.getAttribute("ready");
    console.log(ready);
    if (ready && ready==="true") {
        return true;
    }
    fetch('/ajax-pay/', {
        method:'POST', 
        mode:'same-origin', 
        credentials:'same-origin', 
        body:JSON.stringify({i:parseInt(g)}), 
        headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', response);
                if(response.success===1){
                    e.parentElement.parentElement.style.backgroundColor='lightgray';
                }
                else {
                    window.alert(response.error);
                }
            })
            .catch(error => { 
                console.log('Error:', error); 
            });
}

