let lrid=0;
rootWidget=function(e){
    let ses=JSON.parse(e.dataset.sections);
    console.log(ses);
    let h=$.querySelector('div#rs');
    if (h.innerHTML==='' || lrid!==e.dataset.ro) {
        lrid=e.dataset.ro
        let sb=['<div class=card>'];

        sb.push('<div class="col-8 ls">');
        sb.push('<ul>')
        for (var s in ses) {
            sb.push('<li id='+s+'><a href="#"><img src="/web/css/1.0/assets/se/'+s+'.svg" />'+ses[s].name+'<span style="margin-inline-start:6px;font-weight:normal;font-size:revert">'+ses[s].counter+'</span></a></li>');
        }
        sb.push("</ul>");
        sb.push("</div>");
        sb.push("<div class='col-4 ff-cols'>");
        sb.push('<a class=btn href="#"><img src="/web/css/1.0/assets/action-2.svg" />Sell your car</a>');
        sb.push('<span class="m0 m1">FIND EVERYTHING</span>');
        sb.push('<span class="m0 m2">YOU\'RE LOOKING FOR</span>');
        sb.push('<ul>');
        sb.push('<li><a href="#">Check all used car</a><span class=mi>&rsaquo;</span></li>');
        sb.push('<li><a href="#">Explore other vehicles</a><span class=mi>&rsaquo;</span></li>');
        sb.push('</ul>');
        sb.push("</div>");

        sb.push("</div>");
        h.innerHTML=sb.join('');
    }
    else {
        h.innerHTML='';        
    }
}