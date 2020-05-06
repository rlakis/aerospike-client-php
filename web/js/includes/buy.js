buy=function(productId, currencyId, e) {
    let ready=e.getAttribute("ready");
    console.log(ready);
    if (ready && ready==="true") {
        return true;
    }
    
    let params={product:parseInt(productId), currency:currencyId};
    fetch('/ajax-pay/', {
        method:'POST', 
        mode:'same-origin', 
        credentials:'same-origin', 
        body:JSON.stringify(params),
        headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', response);
                if (response.success===1) {                    
                    e.parentElement.parentElement.style.backgroundColor='lightgray';
                }
                else {                    
                    Swal.fire({
                        icon:'error',
                        title:'Failed',
                        html:response.error
                    });
                }
            })
            .catch(error => { 
                Swal.fire('Failed Request!', error.toString(), 'error');
            });
}

