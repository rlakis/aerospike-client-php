<script type="text/javascript">
let D=class{
    div() => 
};

class SlideShow {
    container=document.createElement('DIV');
    header=document.createElement('HEADER');
    
    _constructor(kAd,_n) {
        this.ad=kAd;
        this.index=parseInt(_n);
        let self=this;
        this.container=$.createElement('DIV');this.container.className='slideshow';
        let h=$.createElement('HEADER');this.container.appendChild(h);
        this.dots=$.createElement('FOOTER');
        let close=$.createElement('SPAN');close.className='close';close.innerHTML='×';close.onclick=function(){self.destroy();};h.appendChild(close);
        for(var i=0;i<this.ad.mediaCount;i++){
            let slide=$.createElement('DIV');slide.className='mySlides fade';            
            slide.innerHTML='<img src="'+d.pixHost+'/repos/d/'+this.ad.pixSpans[i].dataset.path+'">';
            this.container.appendChild(slide);
            
            let dot=$.createElement('SPAN');
            dot.className='dot'; 
            dot.onclick=function(){self.current(i+1);};
            dots.appendChild(dot);
        }
        let prev=$.createElement('A');prev.className='prev';prev.innerHTML='&#10094;';prev.onclick=function(){self.plus(-1);};
        let next=$.createElement('A');next.className='next';next.innerHTML='&#10095;';next.onclick=function(){self.plus(1);};
        this.container.appendChild(prev);
        this.container.appendChild(next);
        
        this.container.appendChild(dots);
        $.body.appendChild(this.container);
        this.show(this.index);
    }
    current(n){this.show(this.index=n);}
    plus(n){this.show(this.index+=n);}
    show(n){
        var i;
        var slides=this.container.getElementsByClassName("mySlides");
        var dots=this.container.getElementsByClassName("dot");
        if(n>this.ad.mediaCount){this.index=1}
        if(n<1){this.index=this.ad.mediaCount}
        for(i=0;i<slides.length;i++){slides[i].style.display="none";}
        for(i=0;i<dots.length;i++){dots[i].className=dots[i].className.replace(" active", "");}
        slides[this.index-1].style.display="flex";
        dots[this.index-1].className += " active";
    }
    destroy(){$.body.removeChild(this.container);}
}

</script>