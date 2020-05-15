buy=function(productId, currencyId, form) {
    //console.log(form.id);
    //f=document.querySelector('form#'+form.id);
    //console.log(f);
    
    let ready=form.getAttribute("ready");
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
                //console.log('Success:', response);
                if (response.success===1) {
                    form.setAttribute('ready', true);
                    form.setAttribute('action', response.result.url);
                    
                    response.result.params.forEach(param=>{
                        let input=document.createElement("input");
                        input.type='hidden';
                        input.name=param.name;
                        input.value=param.value;
                        form.insertBefore(input, form.querySelector('button'));
                    });
                    Swal.fire({
                        position: 'center',
                        icon: 'info',
                        title: 'Redirecting...',
                        text: 'Please while while redirecting to credit card page',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        timer: 20000,
                        onBeforeOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    //console.log('form', form);
                    form.submit();
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


redirect=function(url) {
    location.href=url;
}

showMessage=function(message) {
    Swal.fire({
        position: 'center',
        icon: 'info',
        text: message,
        showConfirmButton: true,
        allowOutsideClick: false
    });
}