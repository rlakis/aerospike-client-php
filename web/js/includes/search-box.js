document.addEventListener("DOMContentLoaded", function () {
    let select=this.querySelector('div.select-wrapper');
    select.addEventListener('click', function() {
        select.querySelector('.select-box').classList.toggle('open');
    });
    
    for (const option of document.querySelectorAll("span.option")) {
        option.addEventListener('click', function() {
            if (!this.classList.contains('selected')) {
                this.parentNode.querySelector('.option.selected').classList.remove('selected');
                this.classList.add('selected');
                this.closest('.select-box').querySelector('.select__trigger span').textContent=this.textContent;
            }
        });
    }

});
