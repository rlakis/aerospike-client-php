
save=function(btn){
    let container=btn.closest('div.acct'), data={}, oldLanguage='en', newLanguage='en';
    container.queryAll('input').forEach(e=>{        
        if (e.id==='lngar') {
            if (e.checked) newLanguage='ar';
            oldLanguage=e.closest('div.row').dataset.value;
        }
        else if (e.id==='lngen' && e.checked) {
            newLanguage='en';            
        }
        else if (e.id==='name' && e.closest('div.row').dataset.value!==e.value) {
            data['name']=e.value;
        }
        else if (e.id==='email' && e.closest('div.row').dataset.value!==e.value) {
            data['email']=e.value;
        }
        else if (e.type==='checkbox') {
            if (e.checked && e.dataset.value!=='1') {
                data[e.id]=1;
            }
            else if (e.checked===false && e.dataset.value==='1') {
                data[e.id]=0;
            }
        }
    });
    if (newLanguage!==oldLanguage) data['lang']=newLanguage;

    if (Object.entries(data).length>0) {
        console.log(data);
        let opt={
            method:'POST',
            mode:'same-origin', 
            credentials:'same-origin',
            headers:{'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify(data)
        };
    
        fetch('/ajax-account/', opt).then(res => res.json()).then(response => {
            console.log('Success:', response);
            if (response.success===1) {
                Swal.fire('Saved!', 'Your user setiings has been saved.', 'success' )
            }
            else {
                Swal.fire('Failed!', response.error, 'error');
            }
        })
        .catch(error => {
            console.log('Error:', error);
        });
    }
};

