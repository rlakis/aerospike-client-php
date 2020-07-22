rootWidget=function(e){
    console.log('rootWidget', e);
    let ses=JSON.parse(e.dataset.sections), assetsURL="https://dev.mourjan.com/css/2020/1.0/assets";
    let h=$$.query('div#rs.lrs');
    if (!e.classList.contains('open')) {       
        for (const i of e.closest('div.roots').querySelectorAll('div.large')) {
            i.classList.remove('open'); 
        }
        
        let rootId=parseInt(e.dataset.ro); sb=['<div class=card><div class="col-8 ls"><ul>'];
        for (var s in ses) {
            let ss=ses[s];
            sb.push('<li id=');
            sb.push(ss[0]);
            sb.push('><a href="');
            sb.push(ss[4]);
            sb.push('">');
            sb.push('<img src="'+assetsURL+'/se/');
            sb.push(ss[0]);
            sb.push('.svg" /><div style="display:inline-flex;flex:1;justify-content:space-between">');
            sb.push(ss[1]);
            sb.push('<span ');
            if(ss[3]===1){sb.push('class="hot" ')}
            sb.push('>');
            sb.push(ss[2].toLocaleString('en-US'));
            sb.push('</span></div></a></li>');
        }
        sb.push("</ul></div>");
        
        sb.push("<div class='col-4 ff-cols'>");
        //if (rootId===1) {
        //    
        //}
        sb.push('<a class=btn href="#"><img src="'+assetsURL+'/action-2.svg" />Sell your car</a>');
        sb.push('<span class="m0 m1">FIND EVERYTHING</span>');
        sb.push('<span class="m0 m2">YOU\'RE LOOKING FOR</span>');
        sb.push('<ul>');
        sb.push('<li><a href="#">Check all used car</a><span class=mi>&rsaquo;</span></li>');
        sb.push('<li><a href="#">Explore other vehicles</a><span class=mi>&rsaquo;</span></li>');
        sb.push('</ul>');
        sb.push("</div>");

        sb.push("</div>");
        //h.style.display='none';
        h.innerHTML=sb.join('');
        //h.style.display='block';
        h.style.opacity=1;
    }
    else {
        h.innerHTML='';
        h.style.opacity=0;
    }
}


let mainMenu=null, bodyPadding=null;
menu=function() {    
    let footer=$$.query('footer'), header=$$.query('header'), html=$.documentElement, i=$$.query('i#burger');
    if (mainMenu==null) {
        let apps=footer.query('#mcapps'), info=footer.query('#mcinfo');
        mainMenu=info.cloneNode(true);
        mainMenu.id='mmenu';
        aw=apps.query('ul').cloneNode(true);
        aw.removeAttribute('id');
        aw.removeChild(aw.query('li#rwdgt')); 
        mainMenu.classList.add('menu');        
        window.scrollTo(0,0);
        mainMenu.appendChild(aw);
        header.after(mainMenu);    
    }

    if (footer.style.display=='none') {
        mainMenu.style.display='none';
        footer.style.display='flex';
        html.style.setProperty('overflow', 'auto');
        $$.style.padding=bodyPadding;
    }
    else {
        bodyPadding=$$.style.padding;
        footer.style.display='none';
        html.style.setProperty('overflow', 'hidden');
        mainMenu.style.display='flex';
        $$.style.padding='0px';        
        
        $$.querySelectorAll('ins.adsbygoogle').forEach(gad=>{
            if (gad.dataset.anchorStatus=='displayed') {
                //console.log(gad, gad.querySelectorAll('span'));
                let ctrl=gad.query('span');
                if (ctrl) {
                    $$.style.padding='30px 0 0'; 
                }
            }
        });
    }
    i.classList.toggle('burger');
    i.classList.toggle('close2');
}
