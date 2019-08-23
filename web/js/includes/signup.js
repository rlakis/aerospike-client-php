var strongRegex=new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
var mediumRegex=new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");
var ar=($$.dir==='rtl');
register=function(){
    let e=$.querySelector('#email'), f=$.querySelector('div.card-footer');
    f.innerHTML='';
    if(e && validateEmail(e.value)){
        console.log(e.value);
        let opt=_options('POST',{v:e.value,l:ar?'ar':'en'});
        fetch('/ajax-'+(window.location.pathname.startsWith('/password/')?'preset':'register/'), opt).then(res => res.json()).then(response => {
            console.log('Success:', response);
            if (response.success===1) {
                f.style.color='#28a745';
                f.innerHTML='<i class="icn icnsmall icn-thumbs-up"></i>&nbsp;&nbsp;<span>'+response.result.ok+'</span>';
            }
            else {
                f.style.color='#D8000C';                
                f.innerHTML='<i class="icn icnsmall icn-alert"></i>&nbsp;&nbsp;<span>'+response.error+'</span>';
            }
        })
        .catch(error => {
            console.log('Error:', error);
        });
    }
    else {
        confirm('Invalid email address!');
        if(e)e.focus();
    }
};

const sleep = (milliseconds) => { return new Promise(resolve => setTimeout(resolve, milliseconds)) };

pswdSave=function(){
    let e=$.querySelector('#pwd'), p=$.querySelector('#pwd2'), f=$.querySelector('div.card-footer');
    f.innerHTML='';
    if(e && p){
        if(e.value===p.value){
            if(e.value.length<6){                            
                f.style.color='#D8000C';
                f.innerHTML='<i class="icn icnsmall icn-alert"></i>&nbsp;&nbsp;<span>'+(ar?'يجب توفير 6 أحرف على الأقل لكلمة المرور.':'You must provide at least six characters for password.')+'</span>';
                return;
            }
            let opt=_options('POST',{v:e.value,l:ar?'ar':'en'});
            fetch('/ajax-password/', opt).then(res => res.json()).then(response => {
                if (response.success===1) {
                    f.style.color='#28a745';
                    f.innerHTML='<i class="icn icnsmall icn-thumbs-up"></i>&nbsp;&nbsp;<span>'+response.result.ok+'</span>';
                    sleep(500).then(() => { $.location=response.result.redirect; });                    
                }
                else {
                    f.style.color='#D8000C';                
                    f.innerHTML='<i class="icn icnsmall icn-alert"></i>&nbsp;&nbsp;<span>'+response.error+'</span>';
                }
            })
            .catch(error => {
                console.log('Error:', error);
            });
        }
        else {
            f.style.color='#D8000C';
            f.innerHTML='<i class="icn icnsmall icn-alert"></i>&nbsp;&nbsp;<span>'+(ar?'كلمة المرور وتأكيد كلمة المرور غير متطابقة.':'Your password and confirmation password do not match.')+'</span>';
        }
        
    }
};

validateEmail=function(e) {
    if(typeof e==='object'){e=e.value;};
    var re=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(e).toLowerCase());
};

pswdStrength=function(p){
    let g=p.closest('div.group'), c=g.querySelector('div.bar').classList, w='weak', m='medium', s='strong'; 
    
    if(strongRegex.test(p.value)) {
        if(c.contains(w)){c.remove(w)}
        if(c.contains(m)){c.remove(m)}        
        if(!c.contains(s)){c.add(s)}
    } else if(mediumRegex.test(p.value)) {
        if(c.contains(w)){c.remove(w)}
        if(c.contains(s)){c.remove(s)}        
        if(!c.contains(m)){c.add(m)}
    } else {
        if(c.contains(s)){c.remove(s)}
        if(c.contains(m)){c.remove(m)}        
        if(!c.contains(w)){c.add(w)}
    }
    console.log(g.querySelector('div.bar'));
};