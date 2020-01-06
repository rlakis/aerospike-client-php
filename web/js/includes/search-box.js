dropdown=function(e){
    let opt=e.querySelector('div.select-box');
    console.log(e, opt);
    if(opt){       
        opt.classList.toggle('open');
    }
}
/*
console.log(document.querySelector('div.select-wrapper'));

document.querySelector('div.select-wrapper').addEventListener('click', function() {
    this.querySelector('div.select').classList.toggle('open');
})
*/