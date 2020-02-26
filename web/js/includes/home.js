rootWidget=function(e){
    let ses=JSON.parse(e.dataset.sections);
    //console.log(ses);
    let h=$.querySelector('div#rs.lrs');
    if (!e.classList.contains('open')) {
        for (const i of e.closest('div.roots').querySelectorAll('div.large')) { i.classList.remove('open'); }
        let sb=['<div class=card>'];

        sb.push('<div class="col-8 ls">');
        sb.push('<ul>')
        for (var s in ses) {
            let ss=ses[s];
            sb.push('<li id=');
            sb.push(ss[0]);
            sb.push('><a href="');
            sb.push(ss[4]);
            sb.push('">');
            sb.push('<img src="/web/css/1.0/assets/se/');
            sb.push(ss[0]);
            sb.push('.svg" />');
            sb.push(ss[1]);
            sb.push('<span ');
            if(ss[3]===1){sb.push('class="hot" ')}
            sb.push('style="margin-inline-start:6px;font-weight:normal;font-size:revert">');
            sb.push(ss[2].toLocaleString('en-US'));
            sb.push('</span></a></li>');
        }
        sb.push("</ul>");
        sb.push("</div>");
        sb.push("<div class='col-4 ff-cols va-center'>");
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