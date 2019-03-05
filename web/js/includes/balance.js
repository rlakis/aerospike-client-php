
document.addEventListener("DOMContentLoaded", function(event) {
    let lines = document.querySelectorAll('ul.ck');
    lines.forEach(function(item){
        item.onclick=function(e){
            console.log(this);
            if(this.classList.contains('exp')){
                this.classList.remove('exp');
            }
            else {
                this.classList.add('exp');
            }
        }
    });
});
    