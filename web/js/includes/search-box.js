if (typeof $==='undefined') {
    let $=document; 
}
$.addEventListener("DOMContentLoaded", function () {
    for (const select of $.querySelectorAll("div.sbw")) {
        let search=select.closest('div.search');
        if (search) {
            select.querySelector('.options').style.width=(search.offsetWidth-3)+'px';
        }            
        select.addEventListener('click', function() {            
            select.querySelector('.sbe').classList.toggle('open');
        });
    }

    let roots=$.querySelector('div.roots');
    if (roots) {
        for (const root of roots.querySelectorAll("div.large")) {        
            root.addEventListener('click', function() {            
                this.classList.toggle('open');
            });
        }
    }
    
    for (const option of $.querySelectorAll("div.option")) {
        option.addEventListener('click', function() {
            console.log('catched');
            if (!this.classList.contains('selected')) {
                
                this.parentNode.querySelector('.option.selected').classList.remove('selected');
                this.classList.add('selected');
                this.closest('.sbe').querySelector('.strg span').textContent=this.textContent;                
                let f=this.closest('form');
                if (f && this.dataset.value) {                   
                    if(f['ro'])f['ro'].value=this.dataset.value;;
                    if(f['cn'])f['cn'].value=this.dataset.value;;
                    if(f['q'])f['q'].focus();
                }
            }
        });
    }

    $.body.addEventListener('click', function(e){
        if(e.target.tagName==='A'){ return; }
        if(e.target.closest('div.sbw')){ return; }
        for (const select of $.querySelectorAll("div.sbw")) {
            select.querySelector('.sbe').classList.remove('open');  
        }
        if (e.target.closest('div.large')===null) {
            let rs=$.querySelector('div#rs.lrs');
            if(rs && rs.innerHTML!=='') {
                rs.innerHTML='';
                let o=$.querySelector('div.roots').querySelector('div.open');
                if (o) {o.classList.remove('open');}            
            }
        }
    }, true); 


});