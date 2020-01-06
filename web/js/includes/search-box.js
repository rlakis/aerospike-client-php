dropdown=function(e){
    let opt=e.querySelector('select');
    console.log(opt);
    if(opt){
        opt.style.opacity=1;
        opt.style.offsetTop=e.style.offsetTop+e.style.offsetHeight+1;
    }
}

document.querySelector('.select-wrapper').addEventListener('click', function() {
    this.querySelector('.select').classList.toggle('open');
})
