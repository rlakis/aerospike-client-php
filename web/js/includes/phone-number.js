$.addEventListener("DOMContentLoaded", function () {
    let container=$.querySelector('div#pin');
    for (const pd of container.querySelectorAll("input[type=number]")) {
        pd.addEventListener('keydown', function(e) {
            if (this.value.length===0) {
               return true;
            }
            e.preventDefault();
        });
        pd.addEventListener('keyup', function(e) {
            let index=parseInt(this.dataset.index);
            if (e.key==='Backspace' && index>1) {
                e.path[1].querySelector('input#d'+(index-1)).focus();
                return;
            }
            if (this.value.length===1) {
                if (index<4) {
                    e.path[1].querySelector('input#d'+(index+1)).focus();
                }
                else {
                    let code='';
                    for (const d of container.querySelectorAll("input[type=number]")) {
                        code+=d.value;
                    }                    
                    validate(container, code);
                }
            }
        });
    }
});

keyChanged=function(e){
    let card=e.closest('div.card');
    card.dataset.e164='';
};

numberCheck=function(e) {
    let card=e.closest('div.card');
    let alert=card.query('div.alert');
    alert.innerHTML='';
    
    let code=card.query('select#code');
    let num=card.query('input#number');

    let resetAlert=function(){
        alert.innerHTML='';
        alert.className='alert';
    }
    let showAlert=function(m, c, focus) {
        resetAlert();
        alert.innerHTML=m;
        alert.classList.add(c);
        if (focus) {
            code.disabled=false;
            num.disabled=false;
            num.focus();
        }
    }
    
    resetAlert();
    code.disabled=true;
    num.disabled=true;
    fetch('/ajax-number-info/?key='+code.value+'&num='+num.value, 
            {method: 'GET', mode: 'same-origin', credentials: 'same-origin', 
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json'}})
            .then(response => { return response.json(); })
            .then(data => {  
                if (data.success===1) {
                    let rs=data.result;
                    if (!rs.valid) {
                        showAlert('Not a valid phone number', 'alert-danger', true);
                    }
                    else if (!rs.mobile) {
                        showAlert('Not a valid mobile phone number', 'alert-danger', true);
                    }
                    else if (rs.disposable) {
                        showAlert('Fraudulent phone number!', 'alert-danger', true);
                    }
                    else {
                        num.value=code.value===rs.region?rs.national:rs.intl;
                        card.dataset.e164=rs.e164;
                        num.disabled=true;
                        showAlert('<span>Mobile Number&nbsp;<b>'+rs.intl+'</b> Verification</span>', 'alert-success');
                        e.closest('div.group').classList.add('none');
                        card.query('div#via').classList.remove('none');                     
                    }
                }
                else {
                    showAlert(data.error, 'alert-danger', true);                    
                }
            }).catch(err => { 
                showAlert(err, 'alert-danger', true); 
            });
};

verify=function(e) {
    let card=e.closest('div.card');
    let params={method:parseInt(e.dataset.method), tel:parseInt(card.dataset.e164)};    
    
    fetch('/ajax-mobile/', {method:'POST',mode:'same-origin',credentials:'same-origin',
                     body:JSON.stringify(params),
                     headers:{'Accept':'application/json','Content-Type':'application/json'}})
        .then(res=>res.json())
        .then(response => {
            console.log('Success:', response);
            if (response.success===1) {
                let rs=response.result;
                let via=card.query('div#via'), pin=card.query('div#pin'), hint=pin.query('div#hint');
                if (typeof(rs['old-sms'])!=='undefined' && rs['old-sms']===1) {
                    via.classList.add('none');
                    pin.classList.remove('none');                    
                    hint.innerHTML=hint.dataset.sms;
                }
                else if (rs.number===parseInt(card.dataset.e164)) {
                    via.classList.add('none');
                    card.dataset.method=e.dataset.method;
                    pin.classList.remove('none');                    
                    hint.innerHTML=(e.dataset.method==="1")?hint.dataset.rvc:hint.dataset.sms;                    
                }
            }
            else {
                Swal.fire('Failed Request!', response.error, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Failed Request!', error.toString(), 'error');
        });
}

validate=function(container, pin){
    let card=container.closest('div.card');
    let params={method:-1, tel:parseInt(card.dataset.e164), pin:parseInt(pin)};
    
    fetch('/ajax-mobile/', {method:'POST',mode:'same-origin',credentials:'same-origin',
                     body:JSON.stringify(params),
                     headers:{'Accept':'application/json','Content-Type':'application/json'}})
        .then(res=>res.json())
        .then(response => {
            console.log('Success:', response);
    
            if (response.success===1) {
                if (response.result.verified===1) {
                    let timerInterval;
                    Swal.fire({
                        icon:'sucess',
                        title:'Thank You',
                        timer:3000,
                        html:'You mobile number is validated',
                        timerProgressBar: true,
                        onBeforeOpen: () => {
                            Swal.showLoading()
                            timerInterval = setInterval(() => {
                                const content = Swal.getContent()
                                if (content) {
                                    const b = content.querySelector('b')
                                    if (b) {
                                        b.textContent = Swal.getTimerLeft()
                                    }
                                }
                            }, 250)
                    },
                    onClose: () => {
                        clearInterval(timerInterval);
                        location.reload();
                    }
                    }).then((result) => {
                        /* Read more about handling dismissals below */
                        if (result.dismiss === Swal.DismissReason.timer) {
                            console.log('I was closed by the timer')
                            
                        }
                    });
                }
                else {
                    Swal.fire('Failed!', response.error, 'error');
                }
            }   
        })
        .catch(error => {
            Swal.fire('Failed Request!', error.toString(), 'error');
        });
}