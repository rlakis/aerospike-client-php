if (typeof $==='undefined') {
    let $=document; 
}
$.addEventListener("DOMContentLoaded", function () {
    for (const select of $.querySelectorAll("div.select-wrapper")) {
        let search=select.closest('div.search');
        if (search) {
            select.querySelector('.options').style.width=(search.offsetWidth+search.offsetLeft-select.offsetLeft)+'px';
        }            
        select.addEventListener('click', function() {            
            select.querySelector('.select-box').classList.toggle('open');
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
    
    for (const option of $.querySelectorAll("span.option")) {
        option.addEventListener('click', function() {
            if (!this.classList.contains('selected')) {
                this.parentNode.querySelector('.option.selected').classList.remove('selected');
                this.classList.add('selected');
                this.closest('.select-box').querySelector('.select__trigger span').textContent=this.textContent;
                let f=this.closest('form');
                if (f && this.dataset.value) {                   
                    if(f['ro'])f['ro'].value=this.dataset.value;;
                    if(f['cn'])f['cn'].value=this.dataset.value;;
                    if(f['q'])f['q'].focus();
                }
            }
        });
    }

});
