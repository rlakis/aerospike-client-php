var ase=[],ccN,ccD,ccA,ccF,tmpT=0,btwT=0,tar,ctac=0,tlen,edN,uForm,vForm,txt,atxt,FLK=1,map,vmap,mapD,mpU;
function hasCanvas(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
};
var saveXHR;
function savAd(p,clr){  
    if(saveXHR){
        saveXHR.abort();
        saveXHR=null;
    }
    if(typeof(clr)!=='undefined' && clr){
        ad.budget=0;
    }
    ad.extra=extra;
    if(cut.t<0)cut.t=0;
    ad.cut=cut;
    ad.cui=cui;
    ad.ro=pro;
    ad.pu=ppu;
    ad.se=pse;
    ad.rtl=artl;
    ad.altRtl=brtl;
    ad.lat=lat;
    ad.lon=lon;
    //setting pubTo
    var t={};
    for(var i in pc){
        t[i]=i;
    }
    ad.pubTo=t;
    /*
     * if ad is premium, double check for multiple countries
     * TO DO BASSEL
    if(){
        
    }*/
    //setting text if changed
    if(txt!=null){
        ad.other=txt;
        ad.rawOther=$('#mText').val();
        ad.rawOther=ad.rawOther.replace(/^\s+|\s+$/g,'');
    }
    if(atxt!=null){
        ad.altother=atxt;
        ad.rawAltOther=$('#altText').val();
        ad.rawAltOther=ad.rawAltOther.replace(/^\s+|\s+$/g,'');
    }
    //var dt='o='+ppf(JSON.stringify(ad));
    var dt = {
        o:ad 
    };
    var m;
    if(p){
        //dt+='&pub='+(p==-1 ? 0 : p);
        dt['pub']=(p==-1 ? 0 : p);
        if(p==1 || p==-1){
            if(!isApp || isApp < '1.0.5'){
                m=(lang=='ar'?'يرجى الإنتظار في حين يتم حفظ الإعلان':'Saving ad, please wait');
                dsl($('#main')[0],m,1);
            }else{
                Dialog.show("loading_dialog","<div class='ctr'>"+(lang=='ar'?'جاري نشر الاعلان':'publishing ad')+"</div><span class='load'></span>");
            }
        }
    }
    saveXHR = $.ajax({
        type:'POST',
        url:'/ajax-adsave/',        
        dataType:'json',
        data:dt,
        success:function(rp){
            if(rp.RP){
                atxt=null;
                txt=null;
                delete ad.other;
                delete ad.altother;
                delete ad.rawOther;
                delete ad.rawAltOther;
                var r=rp.DATA.I;
                ad.id=r.id;
                ad.user=r.user;
                ad.state=r.state;          
          
                var obj = rp.DATA.ad;
                if(typeof obj.other !== 'undefined' && obj.other.length > 0){
                    processedText = obj.other.replace(/\u200B.*$/, '');
                    var textarea = $('#mText');
                    textarea.val(processedText);
                    idir(textarea[0]);
                    var label=$("#mPreview");
                    label.html(obj.other);
                }
                if(typeof obj.altother !== 'undefined' && obj.altother.length > 0){
                    processedAltText = obj.altother.replace(/\u200B.*$/, '');
                    var textarea = $('#altText');
                    textarea.val(processedAltText);
                    idir(textarea[0]);
                    var label=$("#mAltPreview");
                    label.html(obj.altother);
                }
                
                if(p==1){
                    if(isApp>'1.0.4'){
                        window.location = 'ios:pending';                        
                    }else{
                        if(isApp){
                            m=(lang=='ar'?'لقد تم حفظ الإعلان وتحويله للمراجعة من قبل محرري الموقع للموافقة والنشر، وسيتم ابلاغك فور نشره':'Your ad was successfully saved and is now pending administrator approval. You will be notified once your ad is approved and published.');
                        }else{
                            m=(lang=='ar'?'لقد تم حفظ الإعلان وتحويله للمراجعة من قبل محرري الموقع للموافقة والنشر، وسيتم ابلاغك برسالة على عنوان بريدك الإلكتروني فور نشره':'Your ad was successfully saved and is now pending administrator approval. You will be notified by email once your ad is approved and published.');
                        }
                        dsl($('#main')[0],m,0,1);
                    }
                }else if (p==2){
                    if(isApp>'1.0.4'){
                        window.location = 'ios:approved';
                    }else{
                        document.location='/myads/?sub=pending';
                    }
                }else if(p==-1){
                    if(isApp>'1.0.4'){
                        window.location = 'ios:draft';
                    }else{
                        m='<span class="done"></span>'+(lang=='ar'?'لقد تم حفظ الإعلان في لائحة المسودات للتعديل لاحقاً':'Your ad was successfully saved to your drafts for later editing');
                        dsl($('#main')[0],m,0,-1);
                    }
                }
            }else{
                if(p==1){
                    if(isApp>'1.0.4'){
                        Dialog.show("alert_dialog","<span class='fail'></span> "+(lang=='ar'?'فشلت عملية نشر الاعلان، يرجى المحاولة مجدداً':'Failed to publish ad, please try again'));
                        //window.location = 'ios:failure';
                    }else{
                        var m=(lang=='ar'?'فشل محرك مرجان بحفظ الإعلان، يرجى المحاولة مجدداً':'Mourjan system failed to save your ad, please try again');
                        dsl($('#main')[0],m,0,0,1);
                    }
                }
            }
        },error:function(rp){
            if(p==1){
                if(isApp>'1.0.4'){
                    Dialog.show("alert_dialog","<span class='fail'></span> "+(lang=='ar'?'فشلت عملية نشر الاعلان، يرجى المحاولة مجدداً':'Failed to publish ad, please try again'));
                    //window.location = 'ios:failure';
                }else{
                    var m=(lang=='ar'?'فشل محرك مرجان بحفظ الإعلان، يرجى المحاولة مجدداً':'Mourjan system failed to save your ad, please try again');
                    dsl($('#main')[0],m,0,0,1);
                }
            }
        }
    });
}
function dsl(e,m,l,c,o){
    var d=$('#loader')[0];
    if(d) d.innerHTML='';
    else d=document.createElement('div');
    d.className='loader';
    d.id='loader';
    var i=document.createElement('div');
    i.className='in';
    i.innerHTML=m;
    var b;
    if(l){
        b=document.createElement('div');
        b.className='load';
        i.appendChild(b)
    }
    if(o){
        b=document.createElement('input');
        b.type='button';
        b.className='button bt';
        b.value=(lang=='ar'?'نشر الإعلان':'Publish Ad');
        b.onclick=function(){savAd(1)};
        i.appendChild(b);
        b=document.createElement('span');
        b.innerHTML=(lang=='ar'?'أو اعلامنا بالأمر في حال تكرار الفشل':'or let us know if the problem persists');
        i.appendChild(b);
        b=document.createElement('input');
        b.type='button';
        b.className='button bt';
        b.value=(lang=='ar'?'تبليغ بوجود عطل':'Report Problem');
        b.onclick=function(){supp()};
        i.appendChild(b);
    }else if(c){
        if(c==-1){
            b=document.createElement('input');
            b.type='button';
            b.className='button bt ah';
            b.value=(lang=='ar'?'تفقد لائحة المسودات':'View Drafts');
            b.onclick=function(){document.location='/myads/'+(lang=='ar'?'':'en/')+'?sub=drafts'};
            i.appendChild(b);
        }else{ 
            b=document.createElement('input');
            b.type='button';
            b.className='button bt ah';
            b.value=(lang=='ar'?'تفقد لائحة الإنتظار':'View Pending Ads');
            b.onclick=function(){document.location='/myads/'+(lang=='ar'?'':'en/')+'?sub=pending'};
            i.appendChild(b);
        }
        b=document.createElement('span');
        b.innerHTML=(lang=='ar'?'أو':'or');
        i.appendChild(b);
        b=document.createElement('input');
        b.type='button';
        b.className='button bt ah';
        b.value=(lang=='ar'?'نشر إعلان آخر':'Post Another Ad');
        b.onclick=function(){document.location='?ad=new'};
        i.appendChild(b);
    }else if(!l){
        b=document.createElement('input');
        b.type='button';
        b.className='button bt';
        b.value=(lang=='ar'?'متابعة':'continue');
        b.onclick=function(){document.location=HOME};
        i.appendChild(b)
    }
    
    d.appendChild(i);
    e.style.display='none';
    e.parentNode.insertBefore(d,e);
}
function supp(){
    var m=(lang=='ar'?'يتم إرسال بلاغك بالعطل، يرجى الإنتظار':'Sending your problem report, please wait');
    dsl($('#main')[0],m,1);
    $.ajax({
        type:'POST',
        url:'/ajax-support/',        
        dataType:'json',
        data:{lang:lang,obj:ad},
        success:function(rp){
            m=(lang=='ar'?'شكراً لك، لقد تم إرسال البلاغ وسيتم العمل على إصلاح العطل في أقرب وقت ممكن':'Thank you, the problem was reported and will be investigated as soon as possible');
            dsl($('#main')[0],m);
        }
    });
    /*posA('/ajax-support/','lang='+lang,function(rp){
        m=(lang=='ar'?'شكراً لك، لقد تم إرسال البلاغ وسيتم العمل على إصلاح العطل في أقرب وقت ممكن':'Thank you, the problem was reported and will be investigated as soon as possible');
        dsl($('#main')[0],m);
    });*/
}
function tNext(e,s){
    var n=$a(e);
    if(s){
        while(n){
            if(!n.className.match(/off/)){
                fdT(n,s);
                if(!n.className.match(/pi/)) {
                    break;
                }
            }
            n=$a(n);
        }
        gto($a(e));
    }else{       
        while(n){
            fdT(n,s);
            n=$a(n);
        }        
        if(e.id=='xct'){
            gto($b(e));
        }else{
            gto(e); 
        }
    }
}
function liC(e){
    var id=$p(e).id;
    switch(id){
        case 'rou':
            roC(e);
            break;
        case 'seu':
            seC(e);
            break;
        case 'puu':
            puC(e);
            break;
        default:
            break;
    }
}
function roC(e){
    var v=parseInt(e.getAttribute('val'));
    var p=$p(e);
    //var ih=(e.className=='h'?1:0);
    var ih=($(e).hasClass('h')?1:0);
    var pu=$a(p);
    var se=$a(pu);
    var cn=$a(se);
    if(!ih && v && v!=pro){
        pro=v;
        var dif=0;
        if(ppro!=pro){
            ppro=pro;
            pse=0;
            ppu=0;
            dif=1;
        }
        switch(v){
            case 1:
            case 2:
            case 99:
                cnU(cn,0);
                break;
            case 3:
            case 4:
            default:
                cnU(cn,1);
                break;
        }
        liT(p,v);
        if(v==4){
            ppu=5;
            liT(pu);
            roP(pu);
            if(dif || !pse){
                lSE(v,se);
                liT(se,0);
            }else{
                liT(se,pse);
                if(pse)
                    cnT(cn,null,1);                
            }
            gto(se);
        }else{
            if(dif || !ppu){
                liT(pu,ppu,pro);
                roP(pu); 
                if(dif)
                    lSE(v,se);
            }else{
                roP(pu);       
                liT(pu,ppu,pro);
                if(ppu){
                    liT(se,pse);
                    if(pse)
                        cnT(cn,null,1);
                }
            }
            gto(pu);
        }
    }else {
        pro=0;
        liT(p,0);
        liT(pu);
        roP(pu);
        liT(se);
        cnT(cn,0);
        gto(p);
    }
}
function puC(e){
    var v=e.getAttribute('val');
    var r=e.getAttribute('ro');
    var p=$p(e);
    //var ih=(e.className=='h'?1:0);
    var ih=($(e).hasClass('h')?1:0);
    var se=$a(p);
    var cn=$a(se);
    if(!ih && v && v!=ppu){
        ppu=v;
        roP(p);
        liT(p,v,r);
        liT(se,pse);
        if(pse)
            cnT(cn,null,1);
        else cnT(cn,0);
        gto(se);
    }else {
        ppu=0;
        liT(p,0);
        roP(p);
        liT(se);
        cnT(cn,0);
        gto(p);        
    }
}
function seC(e){
    var v=e.getAttribute('val');
    var p=$p(e);
    //var ih=(e.className=='h'?1:0);
    var ih=($(e).hasClass('h')?1:0);
    var cn=$a(p);
    if(!ih && v && v!=pse){
        if(pse==748 || pse==766 || pse==223 || pse==924)cnU(cn,0);
        pse=v;
        if(pse==748 || pse==766 || pse==223 || pse==924)cnU(cn,1);
        liT(p,v);
        cnT(cn,null,1);
    }else {
        pse=0;
        liT(p,0);
        cnT(cn,0);
        gto(p);
    }
}
function cnH(e,s){
    var l=$cL(e);
    var x,y;
    x=$c(e,l-2);
    y=$c(e,l-1);
    if(s==0 && pcl){
        fdT(e,0,'pi');
        for (var j=1;j<l-2;j++){
            fdT($c(e,j),0);
        }  
        var k=0,tm='';
        for (var i in pc){
            if(pc[i]){
                if(k)tm+=' - ';
                tm+=pc[i];
                k++;
            }
        }
        pcl=k;
        x.innerHTML='<b class="ah">'+tm+'</b>'; 
        fdT(x,1);
        fdT(y,0);
        tNext(e,1);
        if(SAVE)savAd();
    }else {
        fdT(e,1,'pi');
        for (var j=1;j<l-2;j++){
            fdT($c(e,j),1);
        }  
        fdT(x,0);
        if(!e.className.match(/uno/))
            fdT(y,pcl);
        tNext(e,0);
    }
}
function cnT(e,s,f){
    if(s){
        cnH(e,s);
        fdT(e,s);
    }else if(s==0){     
        fdT(e,0);
        tNext(e,0);
    }else{
        if(f){
            fdT(e,1); 
            cnH(e,0);           
        }else{
            if(e.className.match(/pi/)) {
                s=1;
            }else {
                s=0;
            }
            cnH(e,s);
        }
    }
}
function lSE(r,n){
    if(ase[r]){
        rSE(ase[r],n);
    }else{
        n.innerHTML='<li class="h"><b>'+(lang=='ar'?'جاري التحميل...':'Loading...')+'</b></li><li><b class="load"></b></li>';
        $.ajax({
            type:'POST',
            url:'/ajax-post-se/',        
        dataType:'json',
            data:{lang:lang,r:r},
            success:function(rp){
                if(rp.RP){
                    ase[r]=rp.DATA.s;
                    rSE(ase[r],n);
                }
            }
        });
        /*posA('/ajax-post-se/','r='+r+'&lang='+lang,function(rp){
            if(rp.RP){
                ase[r]=rp.DATA.s;
                rSE(ase[r],n);
            }
        })*/
    }
}
function rSE(s,n){
    n.innerHTML='<li class="h"><b>'+s.m+'<span class="et"></span></b></li>';
    var a=s.i;
    var l=a.length;
    var csp='';
    switch(pro){
        case 1:
            csp='x x';
            break;
        case 2:
            csp='z z';
            break;
        case 3:
            csp='v v';
            break;
        case 4:
            csp='y y';
            break;
        case 99:
            csp='u u';
            break;
    }
    for(var j=0;j<l;j++){
        n.innerHTML+='<li val="'+a[j][0]+'"><b><span class="'+csp+a[j][0]+'"></span>'+a[j][1]+'</b></li>';
    }
    iniC('seu');
}
function cnU(u,s){
    var m=u.className.match(/uno/);
    if(s){
        if(m)
            fdT(u,1,'uno');
    }else{
        if (!m)
            fdT(u,0,'uno');
    }
    if( (m && s) || (!m && !s)){
        pc=[];
        pcl=0;
        var n=$c(u);
        var l=$cL(u);
        var x,y,z;
        for (var j=1;j<l-2;j++){
            x=n[j];
            if($cL(x)>1){
                y=$c($c(x,1));
                z=y.length;
                for (var i=0;i<z;i++){
                    //y[i].className='';
                    $(y[i]).removeClass('on');
                }
            }else {
                //x.className='';
                $(x).removeClass('on');
            }
        }
    }
}
function cnC(e){
    var un=0;
    var p=$p(e);
    if($(p).hasClass('sls'))p=$p(p,2);
    var l=$cL(p);
    if(p.className.match(/uno/)){
        un=1;
        var x,m,n;
        for (var j=1;j<l-2;j++){
            x=$c(p,j);
            if($cL(x)>1){
                m=$c($c(x,1));
                n=m.length;
                for (var i=0;i<n;i++){
                    //m[i].className='';
                    $(m[i]).removeClass('on')
                }
            }else {
                //x.className='';
                $(x).removeClass('on')
            }
        }        
    }
    var v=e.getAttribute('val');
    if ($(e).hasClass('on')){
        if (un){
            pc=[];
            pcl=0;
        }else {
            if(pc[v]) {
                delete pc[v];
                pcl--;
            }
        }
        fdT(e,1,'on');
    }else {
        if (un){
            pc=[];
            pc[v]=$f(e).innerHTML.replace(/<.*?>/g,'');
            pcl=1;
            cnT(p);
        }else {
            pc[v]=$f(e).innerHTML.replace(/<.*?>/g,'');
            pcl++;
        }
        fdT(e,0,'on');      
    }
    var x=$c(p,l-1);
    if(un){
        fdT(x,0);
    }else {
        fdT(x,pcl);        
    }
}
function liT(u,s,r){
    var c=$c(u);
    var l=$cL(u);
    if(s){
        var x;
        for(var i=0;i<l;i++){
            x=c[i].getAttribute('val');
            if(r && r!=c[i].getAttribute('ro'))x=0;
            //c[i].className=(c[i].className=='h'? 'h':(x==s ? '':'hid'));
            var w=$(c[i]);
            if(!w.hasClass('h')){
                if(x==s){
                    w.removeClass('hid')
                }else{
                    w.addClass('hid')
                }
            }
        }  
        fdT(u,0,'pi');
        fdT(u,1);
    }else if(s==0){
        fdT(u,1,'pi');
        for(var i=0;i<l;i++){
            //c[i].className=(c[i].className=='h'? 'h':'');
            $(c[i]).removeClass('hid')
        }
        fdT(u,1);
    }else {
        fdT(u,0);
    }
}
function roP(u){
    var v=pro;
    var c=$c(u);
    var l=$cL(u);
    var x,h;
    for(var i=0;i<l;i++){
        x=c[i].getAttribute('ro');
        //h=c[i].className;
        //c[i].className=(h=='h'?'h':(x==v ? '':'hid'));
        var w=$(c[i]);
        if(!w.hasClass('h')){
            if(x==v){
                w.removeClass('hid')
            }else{
                w.addClass('hid')
            }
        }
    }
}
function pz(e,c,v){
    if($(e).hasClass('alt')){
        rpz(e,1,1);
    }else{
        ccF=parseInt(e.getAttribute('val'));
        var p=$p(e);
        var n=$c(p);
        var k=$cL(p);
        for (var i=0;i<k;i++){
            $(n[i]).addClass('hid');
            $(n[i]).removeClass('alt');
        }
        $(e).addClass('alt');
        $(e).removeClass('hid');
        var d=$a($p(p));
        if(c) {
            var o=$f(d,2);
            o.innerHTML='<b>'+(ccv['n']?'<span class="cf c'+ccv['n']+'"></span>':'')+ccv[lang]+' <span class="pn">+'+ccv['c']+'</span><span class="et"></span></b>';
            fdT(o,1);
            if(!ccA){
                ldCN();  
            }
        }else {
            fdT($f(d,2),0);
        }
        var t=$f($c($f(d),1),2);
        if(edN!=null && cui.p[edN]) v=cui.p[edN]['r'];
        if(v)t.value=v;
        else t.value='';
        fdT(t,1,'err');
        fdT(d,1);
        gto($p(e,3));
    }
}
function ldCN(){
    $.ajax({
        type:'POST',
        url:'/ajax-code-list/',        
        dataType:'json',
        data:{lang:lang},
        success:function(rp){
            if(rp.RP){
                ccA=rp.DATA.l;
                ccB();
            }
        }
    });
    /*posA('/ajax-code-list/','lang='+lang,function(rp){
        if(rp.RP){
            ccA=rp.DATA.l;
            ccB();
        }
    })*/
}
function wpz(e){
    if(!ccA){
        ldCN();  
    }
    var u=$p(e);
    if(!$(e).hasClass('h')) 
        u=$p(u,2);
    if($(u).hasClass('pi')){
        fdT(u,1,'pi');
        tNext(u,0);
        gto(u);
    }else{
        var v=e.getAttribute('val');
        if(v!=null){
            var tv=v;
            var tedN=-1;
            var raw='';
            var hs=0;
            if(isNaN(v)){
                switch(v){
                    case 'e':
                        raw=cui[tv];
                        v=10;
                        break;
                    case 'b':
                        raw=cui[tv];
                        v=6;
                        break;
                    case 's':
                        raw=cui[tv];
                        v=11;
                        break;
                    case 't':
                        raw=cui[tv].replace(/@/g,'');
                        v=12;
                        break;
                    default:
                        v=null;
                        break;
                }
            }else {
                var k=ccA.length;
                var y;
                for(var i=0;i<k;i++){
                    y=ccA[i];
                    if(y[0]==cui.p[v].c) break;
                }
                setCC(y);
                raw=cui.p[v].r;
                v=cui.p[v].t;
                tedN=tv;
                hs=1;
            }
            var n=$c($f($c(u,1)));
            var l=n.length;
            var c;
            var x=v;
            for(var i=0;i<l;i++){
                if(n[i].getAttribute('val')==x){
                    c=n[i];
                    break;
                }
            }
            if(c){
                rpz(e,1);
                if(tedN>-1)edN=tedN;
                pz(c,hs,raw);
            }
        }
    }
}
function rpz(e,f,h){
    var p=$p(e,3);
    if(!f)p=$p(p,2);
    var u=$c($c(p,1),0);
    var c=$c(p);
    if(!h)edN=null;
    if(!f && pzv){       
        fdT(c[1],0);
        fdT(c[2],0);
        fdT(c[3],1);
    }else{    
        fdT(c[2],0);
        var r=c[3];
        fdT(r,0);
        if($cL($f(r))==2){
            pzv=0;
        }else {
            pzv=1;
        }
        fdT(c[1],1);
        spz(u);
    }
    gto(p);
}
function spz(e){
    var n=$c(e);
    var k=$cL(e);
    for (var i=0;i<k-1;i++){
        //n[i].className='';
        $(n[i]).removeClass('hid');
        $(n[i]).removeClass('alt');
    }
    if(pzv){
        //n[i].className='';
        $(n[i]).removeClass('hid');
        $(n[i]).removeClass('alt');
    }else{
        //n[i].className='hid';
        $(n[i]).addClass('hid')
    }
}
function pzc(e){
    ccN=e;
    var d=$('#main')[0];
    d.style.display='none';
    var r;
    if(ccD){
        r=ccD;
        document.body.insertBefore(r,d);
    }else{
        r=document.createElement('DIV');
        r.className='ccd load';
        document.body.insertBefore(r,d);
        ccD=r;
    }
    window.scrollTo(0,0);
    ccB();
}
function npz(e){
    var u=$p(e,5);
    fdT(u,0,'pi');
    var c=$a(u);
    if(cui.p.length){
        fdT(c,1,'off');
    }else {
        fdT(c,0,'off');
    }
    setTC($a(u,2));
    setTC($a(u,4),1);
    tNext(u,1);
    if(SAVE)savAd();
}
function ccB(){
    if(ccD && ccD.innerHTML=='' && ccA){
        var ul=document.createElement('ul');
        var ul2=document.createElement('ul');
        ul.className='ls po';
        ul2.className='ls po br';
        var o;
        var r=ccA;
        var l=r.length;
        for(var i=0;i<l;i++){
            o=document.createElement('li');
            o.className='button';
            o.onclick=(function(y){return function(){setCC(y);}})(r[i]);
            if(r[i][3]){
                o.innerHTML='<b><span class="cf c'+r[i][1]+'"></span>'+r[i][2]+' <span class="pn">+'+r[i][0]+'</span></b>';
                ul.appendChild(o);
            }else{
                o.innerHTML='<b>'+r[i][2]+' <span class="pn">+'+r[i][0]+'</span></b>';
                ul2.appendChild(o);
            }
        }
        ccD.style.height='auto';
        ccD.innerHTML='<h2 class="ctr">'+(lang=='en'?'Choose Country Code':'إختر رمز البلد')+'</h2>';
        ccD.appendChild(ul);
        ccD.appendChild(ul2);
    }
}
function setCC(r){
    ccv={c:r[0],n:0,en:'',ar:'',i:r[4]};
    if(r[3])ccv['n']=r[1];
    ccv[lang]=r[2];
    if(!ccN)ccN=$('#CCodeLi')[0];
    ccN.innerHTML='<b>'+(ccv['n']?'<span class="ct c'+ccv['n']+'"></span>':'')+ccv[lang]+' <span class="pn">+'+ccv['c']+'</span><span class="et"></span></b>';
    if(ccD && $p(ccD)){
        $p(ccD).removeChild(ccD);
        $('#main')[0].style.display='block';
        gto($p(ccN,3));
    }
}
function savC(e){
    var p=$p(e,2);
    var t=$f($b(p),2);
    fdT(t,1,'err');
    var v=t.value;
    v=v.replace(/<.*?>/g,'');
    t.value=v;
    var x=0;
    var raw=0;
    if(v){
        switch(ccF){
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 7:
            case 8:
            case 9:
            case 13:
                v=v.replace(/[\(\)\-\/\\.\s*#_ـ]/g,'');
                v=v.replace(/^\+/,'00');
                v=v.replace(new RegExp("^00"+ccv.c,""),'');
                t.value=v;
                if(v.length>6)
                    x=v.match(/[^0-9]/);
                else x=1;
                raw=v;
                v='+'+ccv.c+parseInt(v,10);
                break;
            //bbm
            case 6:
                v=v.toUpperCase();
                t.value=v;
                if(v.length==8)
                    x=v.match(/[^a-zA-Z0-9]/);
                else x=1;
                break;
            //email
            case 10:
                v=v.toLowerCase();
                t.value=v;
                if(!v.match(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/))
                    x=1;
                break;
            //skype
            case 11:
            case 12:
            default:
                if(v.match(/ /))
                    x=1;
                break;
        }
    }else{
        x=1;
    }
    if(x)fdT(t,0,'err');
    else{
        switch(ccF){
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 7:
            case 8:
            case 9:
            case 13:
                var m=cui.p;
                var l=m.length;
                if(edN!=null){
                    var a=-1;
                    for(var i=0;i<l;i++){
                        if(i!=edN && v==m[i]['v']){
                            a=i;
                            break;
                        }
                    }
                    m[edN]={v:v,t:ccF,c:ccv.c,r:raw,i:ccv.i};
                    if(a>-1){
                        var tm=[],j=0;
                        for(var i=0;i<l;i++){
                            if(i!=a){
                                tm[j++]=m[i];
                            }
                        }
                        m=cui.p=tm;
                        l--;
                    }
                }else {
                    var a=1;
                    for(var i=0;i<l;i++){
                        if(v==m[i]['v']){
                            m[i]['t']=ccF;
                            a=0;
                            break;
                        }
                    }
                    if(a){
                        if(l===3) l=2;
                        m[l]={v:v,t:ccF,c:ccv.c,r:raw,i:ccv.i};
                    }
                }
                cui.p=m;
                break;
            //bbm
            case 6:
                cui['b']=v;
                break;
            //email
            case 10:
                cui['e']=v;
                break;
            //twitter
            case 12:                
                v=v.replace(/@/g,'');                              
                v=v.replace(/(?:https:\/\/|http:\/\/|)twitter.com/,'');
                if(v[0]=='/'){
                    v=v.substring(1);
                }  
                t.value=v;
                cui['t']='@'+v;
                break;
            //skype
            case 11:
            default:
                cui['s']=v;
                break;
        }
        t.value='';
        p=$p(p,2);
        var d=$f($a(p));
        var n=$c(d);
        var l=$cL(d);
        var y=n[l-2].cloneNode(true);
        var z=n[l-1].cloneNode(true);
        d.innerHTML='';
        var m=cui.p;
        l=m.length;
        var o,s,g,k;
        for(var i=0;i<l;i++){
            g=m[i]['v'];
            k=m[i]['t'];
            s='<b>';
            if(k<6 || k==13){
                if((k>2 && k<6) || k==13)
                    s+='<span class="pz pz3"></span>';
                if(k==2 || k==4 || k==13)
                    s+='<span class="pz pz2"></span>';
                if(k<5)
                    s+='<span class="pz pz1"></span>';
            }else {
                if(k==7)
                    s+='<span class="pz pz5"></span>';
                else if(k==8 || k==9)
                    s+='<span class="pz pz6"></span>';
            }   
            s+='<span class="button pz pzd"></span>'+g;
            s+='</b>';
            _aliu(d,s,i);
        }
        if(cui.b){
            s='<b><span class="pz pz4"></span><span class="button pz pzd"></span>'+cui.b+'</b>';
            _aliu(d,s,'b');
        }
        if(cui.t){            
            s='<b><span class="pz pz9"></span><span class="button pz pzd"></span>'+cui.t+'</b>';
            _aliu(d,s,'t');
        }
        if(cui.s){
            s='<b><span class="pz pz8"></span><span class="button pz pzd"></span>'+cui.s+'</b>';
            _aliu(d,s,'s');
        }
        if(cui.e){
            s='<b><span class="pz pz7"></span><span class="button pz pzd"></span>'+cui.e+'</b>';
            _aliu(d,s,'e');
        }
        d.appendChild(y);
        d.appendChild(z);
        fdT(p,0);
        fdT($b(p),0);
        fdT($p(d),1);
        edN=null;
        gto($p(d,2));
    }
}
function _aliu(u,s,v){
    var o=document.createElement('LI');
    o.setAttribute('val',v);
    o.className='button pn';
    o.onclick=(function(e){return function(){wpz(e)}})(o);
    o.innerHTML=s;    
    var z=$f(o);
    z=$c(z,($cL(z)-2));
    z.onclick=(function(w,e){return function(){delC(w,e)}})(v,z);
    u.appendChild(o);
}
function delC(w,e){
    se();
    if(isNaN(w)){
        cui[w]='';
    }else{
        var a=[],k=0;
        var n=cui.p;
        var l=n.length;
        for(var i=0;i<l;i++){
            if(w!=i){
                a[k++]=n[i];
            }
        }
        cui.p=a;
    }    
    var p=$p(e,2);
    var u=$p(p);
    u.removeChild(p);
    var l=$cL(u);
    var c=$c(u);
    var k=0,a;
    for (var i=0;i<l;i++){
        a=c[i].getAttribute('val');
        if(!isNaN(a)){
            c[i].setAttribute('val',k++);
        }
    }
    if(l==2){
        rpz($f(u),1);
    }   
}




function stm(e){
    var p=$p(e);
    tNext(p,0);
    if($(e).hasClass('h')){
        if(!p.className.match(/ pi/)) return;
    }
    var n=$c(p);
    fdT(p,1,'pi');
    fdT(n[1],0);
    fdT(n[3],0);
    fdT(n[4],0);
    fdT(n[2],1);
    gto(p);
}
function savT(e,o){
    var u=$p(e,3);
    var n=$c(u);
    var f=$f(n[3]);
    var b=tmpT.b,a=tmpT.a,x='';
    if(tmpT.t==2){
        if(btwT==1 && b==a){ 
            var y=$c($f(n[2]),0);
            cut={t:0,b:24,a:6};
            btwT=0;
            n[1].innerHTML='<b>'+y.innerHTML.replace(/<.*?>/g,'')+'</b>';
            fdT(n[2],0);
            fdT(n[3],0);
            fdT(n[4],1);
        }else {
            if(btwT==0){
                x=' '+gttv(f,a);
                btwT=1;
                $f(n[1]).innerHTML+=x;
            }else{
                cut={t:tmpT.t,b:b,a:a};
                if (a>b){
                    var g=b;
                    b=a;
                    a=g;
                    cut.a=a;
                    cut.b=b;
                }
                x=$c($f(n[2]),2).innerHTML.replace(/<.*?>/g,'');
                x+=' '+gttv(f,a);
                x+=' '+(lang=='ar'?'و':'and')+' ';
                x+=gttv(f,b);
                btwT=0;
                $f(n[1]).innerHTML=x;
                cutS=x;
                fdT(n[2],0);
                fdT(n[3],0);
                fdT(n[4],1);
            }
        }
    }else{
        var t=tmpT.t;
        if(t==1){
            a=0;
            v=b;
        }else {
            b=0;
            v=a;
        }
        cut={t:t,b:b,a:a};
        x=' '+gttv(f,v);
        
        $f(n[1]).innerHTML+=x;
        cutS=$f(n[1]).innerHTML;
        fdT(n[2],0);
        fdT(n[3],0);
        fdT(n[4],1);
    }
    gto(u);
}
function ctm(e){
    var v=parseInt(e.getAttribute('val'));
    var t=tmpT.t;
    if(t==2){
        if(btwT==0)
            tmpT['a']=v;
        else tmpT['b']=v; 
        savT(e);
    }else if(t==1){
        tmpT.b=v;
        tmpT.a=0;
        savT(e);
    }else if(t==3){
        tmpT.a=v;
        tmpT.b=0;
        savT(e);
    }
}
function ttm(s,e){
    var x=$p(e,3);
    if(!s)x=$p(x,2);
    var n=$c(x);
    if(s){
        var t=s-1;
        n[1].innerHTML='<b>'+e.innerHTML.replace(/<.*?>/g,'')+'<span class="et"></span></b>';
        fdT(n[2],0);
        fdT(n[1],1);
        if(t==0){
            fdT(n[4],1);
            tmpT=cut={t:t,b:24,a:6};
            btwT=0;
            cutS=$f(n[1]).innerHTML;
        }else{
            tmpT={t:t,b:cut.b,a:cut.a};
            fdT(n[2],0);
            fdT(n[3],1);
        }
    }else{
        btwT=0;
        var y=$c($f(n[2]),cut.t);
        n[1].innerHTML='<b>'+cutS+'</b>';
        fdT(n[4],1);
        fdT(n[3],0);
        fdT(n[2],0);
        fdT(n[1],1);
    }
    gto(x);
}
function ntm(e){
    var p=$p(e,3);
    fdT(p,0,'pi');
    setTC($a(p));
    setTC($a(p,3),1);
    tNext(p,1);
    if(SAVE)savAd();
}
function gttv(u,v){
    var n=$c(u);
    for(var i=0;i<19;i++){
        if(v==n[i].getAttribute("val")){
            return $f(n[i]).innerHTML.replace(/<.*?>/g,'');
        }
    }
}


function initT(e){
    tar=e;
    tlen=$c($f($b($p(e))),1);
    var j=$(e);
    j.on('paste',function(){setTimeout('var k=getAP();rdrT();capk();if(k){setCP(tar,k,1);}',10)});
    j.on('keyup',function(){capk()});
    var l=$a($p(tar));
    hidTB=0;
    if(tar.id == 'mText'){
        ctac = 0;
        processedText = e.value;
    }else{
        ctac = 1;
        processedAltText = e.value;
    }
    fdT($a(l),0);
    fdT(l,1);
}
function capk(){
    var e=tar;
    var m=maxC;
    var v=e.value.length;
    if (v>=m){
        e.onkeydown=function(e){
            var cd=(typeof e.ctrlKey !== 'undefined' && e.ctrlKey !== null ? e.ctrlKey : e.metaKey);
            var hs=0;
            if(document.selection!=undefined){
                var sel = document.selection.createRange();
                hs=sel.text.length;
            }else if(tar.selectionStart != undefined && tar.selectionStart!=tar.selectionEnd){
                hs=1;
            }
            if(
                    !hs &&
                    e.keyCode!=8 && 
                    e.keyCode!=46 && 
                    e.keyCode!=37 && 
                    e.keyCode!=38 && 
                    e.keyCode!=39 && 
                    e.keyCode!=34 && 
                    !(cd && (e.keyCode==86 || e.keyCode==88 || e.keyCode==65 || e.keyCode==67) ) )  
                return false;
        };
        if(v>m){
            var k=getAP();
            if (tar.selectionStart) {
                k=tar.selectionStart;
            } else if (document.selection) {
                var c = "\001",
                sel = document.selection.createRange(),
                dul = sel.duplicate(),
                len = 0;
                dul.moveToElementText(node);
                sel.text = c;
                len = dul.text.indexOf(c);
                sel.moveStart('character',-1);
                sel.text = "";
                k=len;
            }
            v=m;
            e.value=e.value.substr(0,m);
            if(k)setCP(tar,k,1);
        }
    }else{
        e.onkeydown=function(){};
    }
    tlen.innerHTML=v+' / '+m;
    idir(e);
    /*if(e.value.match(/[\u0621-\u064a\u0750-\u077f]/)){
        e.className='ar';
    }else{
        e.className='en';
    }*/
    setTimeout('tglTB('+v+');',100);
}
function getAP(){
    var k=0;
    if (tar.selectionStart) {
        k=tar.selectionStart;
    } else if (document.selection) {
        var c = "\001",
        sel = document.selection.createRange(),
        dul = sel.duplicate(),
        len = 0;
        dul.moveToElementText(tar);
        sel.text = c;
        len = dul.text.indexOf(c);
        sel.moveStart('character',-1);
        sel.text = "";
        k=len;
    }
    return k;
}
function tglTB(v){
    var l=$a($p(tar));
    var b=$f(l,2);
    var hasC=(ctac ? hasAT:hasT);
    if (v>=minC){
        if(!hasC){
            if(ctac) hasAT=1;
            else hasT=1;
            fdT(b,1,'off');
        }
    }else{
        if(hasC){
            if(ctac) hasAT=0;
            else hasT=0;
            fdT(b,0,'off');
        }
    }
    if(hidTB){        
        fdT($a(l),0);
        fdT(l,1);
    }
}
function hidNB(e){
    fdT(e,0);
    fdT($b(e),1);
}

function getTextHex(text) {
    var code;
    for(var i = 0, l = text.length; i < l; i++){
        code = text.charCodeAt(i).toString(16).toUpperCase();
        while (code.length < 4) {
            code = "0" + code;
        }
    }
}

/*function rdrT(){
    var e=tar;
    var irtl=0;
    var v=e.value;
    var l=e.value.length;
    var y,z;
    if(l>=minC){
        var r=v;
        
        //cleanup tag insertions
        r=r.replace(/<.*?>/g,'');
        //convert arabic numbers to english
        r=r.replace(/\u0660/g,0);
        r=r.replace(/\u0661/g,1);
        r=r.replace(/\u0662/g,2);
        r=r.replace(/\u0663/g,3);
        r=r.replace(/\u0664/g,4);
        r=r.replace(/\u0665/g,5);
        r=r.replace(/\u0666/g,6);
        r=r.replace(/\u0667/g,7);
        r=r.replace(/\u0668/g,8);
        r=r.replace(/\u0669/g,9);
        //check if ad is arabic
        irtl=(e.className=='ar'?1:0);
        //replace commas after numbers
        if(irtl){
            r=r.replace(/,/g,'،');
        }else {
            r=r.replace(/،/g,',');
        }
        //replace arabic commas between numbers
        r=r.replace(/([0-9])،([0-9])/g,'$1,$2');
        //cleanup email insertion
        r=r.replace(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/g,' ');
        //cleanup url insertion
        //r=r.replace(/\b((http(s?):\/\/)?([a-z0-9\-]+\.)+(MUSEUM|TRAVEL|AERO|ARPA|ASIA|EDU|GOV|MIL|MOBI|COOP|INFO|NAME|BIZ|CAT|COM|INT|JOBS|NET|ORG|PRO|TEL|A[CDEFGILMNOQRSTUWXZ]|B[ABDEFGHIJLMNORSTVWYZ]|C[ACDFGHIKLMNORUVXYZ]|D[EJKMOZ]|E[CEGHRSTU]|F[IJKMOR]|G[ABDEFGHILMNPQRSTUWY]|H[KMNRTU]|I[DELMNOQRST]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKRSTUVY]|M[ACDEFGHKLMNOPQRSTUVWXYZ]|N[ACEFGILOPRUZ]|OM|P[AEFGHKLMNRSTWY]|QA|R[EOSUW]|S[ABCDEGHIJKLMNORTUVYZ]|T[CDFGHJKLMNOPRTVWZ]|U[AGKMSYZ]|V[ACEGINU]|W[FS]|Y[ETU]|Z[AMW])(:[0-9]{1,5})?((\/([a-z0-9_\-\.~]*)*)?((\/)?\?[a-z0-9+_\-\.%=&amp;]*)?)?(#[a-zA-Z0-9!$&'()*+.=-_~:@/?]*)?)/gi,'');
        //cleanup @ joints
        r=r.replace(/[a-zA-Z0-9]*?@[a-zA-Z0-9]*?(?:\s|$)/g,'');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //cleanup long dash (madde) from arabic text
        r=r.replace(/([\u0600-\u06ff])\u0640+([\u0600-\u06ff])/g,'$1$2');
        
        //replace unwanted chars with no space
        r=r.replace(/[\u0600-\u060B\u060D-\u061a\u064b-\u065f\u06d6-\u06ed\ufe70-\ufe7f]/g,'');
        
        //cleanup unwanted chars
        r=r.replace(/[^\s0-9a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc!£¥$%&*()×—ـ\-_+=\[\]{}'",،.;:|\/\\?؟؛،]/g,' ');
        
        //cleanup repeated chars
        r=r.replace(/([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc\u00C0-\u00FF\u0100-\u017F])\1{2,}/g,'$1');
        //r=r.replace(/([.,:;(){}|?!*&\%\-_=+~\[\]\\\/"'؟؛،])\1{1,}/g,'$1');
        r=r.replace(/([^a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc\u00C0-\u00FF\u0100-\u017F0-9])\1{1,}/g,'$1');
        //remove spaced patterns
        r=r.replace(/([^a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc\u00C0-\u00FF\u0100-\u017F0-9])\s\1{1,}/g,'$1 ');
        //unify parenthesis
        r=r.replace(/[{\[]/g,'(');
        r=r.replace(/[\]}]/g,')');
        //unify separators
        r=r.replace(/[_|—|ـ]/g,'-');
        //seperate numbers from text
        r=r.replace(/([0-9])([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})/g,'$1 $2');
        r=r.replace(/([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})([0-9])/g,'$1 $2');
        //remove space before and after parenthesis and seperators
        r=r.replace(/\s([\)+×\(\/\\\-])/g,'$1');
        r=r.replace(/([\(\)+×\/\\\-])\s/g,'$1');
        //surround seperators with space if not numeric
        r=r.replace(/([^0-9])([\\\/\-])([0-9])/g,'$1 $2 $3');
        r=r.replace(/([0-9])([\\\/\-])([^0-9])/g,'$1 $2 $3');
        //precede parenthesis and seperators by outer space
        r=r.replace(/([^\s])([\(\\\/\-+])/g,'$1 $2');
        r=r.replace(/([\)\\\/\-+])([^\s])/g,'$1 $2');
        //add space after commas and periods if after is alphabet
        r=r.replace(/([?!:;,.؟؛،])([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc\u00C0-\u00FF\u0100-\u017F]{2,})/g,'$1 $2');
        //add space after commas and periods if after is numerical
        r=r.replace(/([^0-9][?!:;؟؛])([0-9])/g,'$1 $2');
        //remove spaces preceeding commas, periods, etc
        r=r.replace(/\s([?!:;,.؟؛،])/g,'$1');
        //remove commas, periods, etc followed by special chars
        r=r.replace(/[.](?:\s|)([\(\)\-])/g,'$1');
        //remove commas, periods, etc preceeded by special chars
        r=r.replace(/([\(\)\-])[:;,.؛،]/g,'$1');
        //remove special chars that do not match ex: : -
        r=r.replace(/([:\(\)\\\/\-;.,؛،?!؟])(?:\s|)[:\\\/\-;.,؛،?!؟]/g,'$1');
        //cleanup phone insertion
        if(pse!=113 && pse!=291 && pse!=325){
            var m =Math.floor(r.length/2);
            y = r.substring(0,m);
            z = r.substring(m);
            z=z.replace(/([0-9])\s(?:(-)(?:\s|)|)([0-9])/g,'$1$2$3');
            z=z.replace(/(\+|)(?:\d{8,}|\d{2}[-\\\/]\d{6,})/g,' ');
            r=y+z;
        }
        //cleanup words repetition
        r=r.replace(/(^|\s)([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]*?)\s\2(\s|$)/g,'$1$2$3');
        //append el waw to text
        r=r.replace(/\sو\s([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc])/g,' و$1');
        //append el م to numbers
        r=r.replace(/([0-9,.])\sم\s/g,'$1م ');
        //seperate numbers from text
        r=r.replace(/([0-9]{2,})([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})/g,'$1 $2');
        r=r.replace(/([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})([0-9]{2,})/g,'$1 $2');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        //replace \ with /
        r=r.replace(/\\/g, '/');
        //remove unwanted string starters
        r=r.replace(/^[-_+=,.;:"'*|\/\\~؛،]/g,'');
        //remove unwanted string endings
        r=r.replace(/(?:[-_+=,.;:*|\/\\~؛،]|\sت|تلفون|هاتف|موبايل|جوال|tel)$/g,'');
        //cleanup repeated spaces
        r=r.replace(/\(\)/g,' ');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        if(ctac) brtl=irtl;
        else artl=irtl;
        tglTB(r.length);
        if(e.value!=r){      
            e.value=r;
            idir(e);
            rdrT()
        }else{
            y=r.replace(/[a-z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
            z=r.replace(/[A-Z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');            
            if(y.length > z.length*0.5){
                e.value = r.toLowerCase();
            }
        }
    }
}*/
                                                                                                                                    
                                                                                                                                                                                                                                                                        
var processedText='';processedAltText='';

function rdrT(callback){       
    var e=tar; 
    var isAlt = true;
    if(e.id === 'mText'){
        isAlt = false;
        processedText = e.value;
    }else{
        processedAltText = e.value;        
    }
    rdrTFilter(isAlt, callback);
}


function rdrTFilter(isAlt, callback){
    var value=processedText;
    var textarea;
    if(isAlt){        
        value=processedAltText;
        textarea = $('#altText');
    }else{
        textarea = $('#mText');
    }
    var r = value.replace(/\s+/,' ');
        r=r.replace(/^\s+|\s+$/g, '');
    if(isAlt){
        processedAltText = r;
    }else{
        processedText = r;
    }    
    
    var irtl=(textarea[0].className=='ar'?1:0);
    if(ctac) brtl=irtl;
    else artl=irtl;
    
    y=r.replace(/[a-z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
    z=r.replace(/[A-Z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');    

    if(y.length > z.length*0.5){
        r = r.toLowerCase();
    }            
    if(isAlt){
        processedAltText = r;
        atxt=prepT(processedAltText,brtl);
    }else{
        processedText = r;
        txt=prepT(processedText,artl);
    }
    if(callback){
        callback();
    }
}
/*
function rdrTFilter(isAlt, callback){    
    var irtl=0;
    var value=processedText;
    if(isAlt){        
        value=processedAltText;
    }
    var l=value.length;
    var y,z;
    if(l>=minC){
        var r=value;
        //cleanup tag insertions
        r=r.replace(/<.*?>/g,'');
        //convert arabic numbers to english
        r=r.replace(/\u0660/g,0);
        r=r.replace(/\u0661/g,1);
        r=r.replace(/\u0662/g,2);
        r=r.replace(/\u0663/g,3);
        r=r.replace(/\u0664/g,4);
        r=r.replace(/\u0665/g,5);
        r=r.replace(/\u0666/g,6);
        r=r.replace(/\u0667/g,7);
        r=r.replace(/\u0668/g,8);
        r=r.replace(/\u0669/g,9);
        //check if ad is arabic
        var textarea;
        if(isAlt){
            textarea = $('#altText');
        }else{
            textarea = $('#mText');            
        }
        irtl=(textarea[0].className=='ar'?1:0);
        //replace commas after numbers
        if(irtl){
            r=r.replace(/,/g,'،');
        }else {
            r=r.replace(/،/g,',');
        }
        //replace arabic commas between numbers
        r=r.replace(/([0-9])،([0-9])/g,'$1,$2');
        //cleanup email insertion
        r=r.replace(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/g,' ');
        //cleanup url insertion
        //r=r.replace(/\b((http(s?):\/\/)?([a-z0-9\-]+\.)+(MUSEUM|TRAVEL|AERO|ARPA|ASIA|EDU|GOV|MIL|MOBI|COOP|INFO|NAME|BIZ|CAT|COM|INT|JOBS|NET|ORG|PRO|TEL|A[CDEFGILMNOQRSTUWXZ]|B[ABDEFGHIJLMNORSTVWYZ]|C[ACDFGHIKLMNORUVXYZ]|D[EJKMOZ]|E[CEGHRSTU]|F[IJKMOR]|G[ABDEFGHILMNPQRSTUWY]|H[KMNRTU]|I[DELMNOQRST]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKRSTUVY]|M[ACDEFGHKLMNOPQRSTUVWXYZ]|N[ACEFGILOPRUZ]|OM|P[AEFGHKLMNRSTWY]|QA|R[EOSUW]|S[ABCDEGHIJKLMNORTUVYZ]|T[CDFGHJKLMNOPRTVWZ]|U[AGKMSYZ]|V[ACEGINU]|W[FS]|Y[ETU]|Z[AMW])(:[0-9]{1,5})?((\/([a-z0-9_\-\.~]*)*)?((\/)?\?[a-z0-9+_\-\.%=&amp;]*)?)?(#[a-zA-Z0-9!$&'()*+.=-_~:@/?]*)?)/gi,'');
        //cleanup @ joints
        r=r.replace(/[a-zA-Z0-9]*?@[a-zA-Z0-9]*?(?:\s|$)/g,'');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //cleanup long dash (madde) from arabic text
        r=r.replace(/([\u0600-\u06ff])\u0640+([\u0600-\u06ff])/g,'$1$2');
        
        //replace unwanted chars with no space
        r=r.replace(/[\u0600-\u060B\u060D-\u061a\u064b-\u065f\u06d6-\u06ed\ufe70-\ufe7f]/g,'');
        
        //cleanup unwanted chars with space
        r=r.replace(/[^\s0-9a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc!£¥$%&*()×—ـ\-_+=\[\]{}'",.;:|\/\\?؟؛،]/g,' ');
        //cleanup repeated chars
        r=r.replace(/([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc])\1{2,}/g,'$1');
        //r=r.replace(/([.,:;(){}|?!*&\%\-_=+~\[\]\\\/"'؟؛،])\1{1,}/g,'$1');
        r=r.replace(/([^a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc0-9])\1{1,}/g,'$1');
        //remove spaced patterns
        r=r.replace(/([^a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc0-9])\s\1{1,}/g,'$1 ');
        //unify parenthesis
        r=r.replace(/[{\[]/g,'(');
        r=r.replace(/[\]}]/g,')');
        //unify separators
        r=r.replace(/[_|—|ـ]/g,'-');
        //seperate numbers from text
        r=r.replace(/([0-9])([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})/g,'$1 $2');
        r=r.replace(/([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})([0-9])/g,'$1 $2');
        //remove space before and after parenthesis and seperators
        r=r.replace(/\s([\)+×\(\/\\\-])/g,'$1');
        r=r.replace(/([\(\)+×\/\\\-])\s/g,'$1');
        //surround seperators with space if not numeric        
        r=r.replace(/([^0-9])([\\\/\-])([0-9])/g,'$1 $2 $3');
        r=r.replace(/([0-9])([\\\/\-])([^0-9])/g,'$1 $2 $3');
        //precede parenthesis and seperators by outer space
        r=r.replace(/([^\s])([\(\\\/\-+])/g,'$1 $2');
        r=r.replace(/([\)\\\/\-+])([^\s])/g,'$1 $2');
        //add space after commas and periods if after is alphabet
        r=r.replace(/([?!:;,.؟؛،])([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{2,})/g,'$1 $2');
        //add space after commas and periods if after is numerical
        r=r.replace(/([^0-9][?!:;؟؛])([0-9])/g,'$1 $2');
        //remove spaces preceeding commas, periods, etc
        r=r.replace(/\s([?!:;,.؟؛،])/g,'$1');
        //remove commas, periods, etc followed by special chars
        r=r.replace(/[.](?:\s|)([\(\)\-])/g,'$1');
        //remove commas, periods, etc preceeded by special chars
        r=r.replace(/([\(\)\-])[:;,.؛،]/g,'$1');
        //remove special chars that do not match ex: : -
        r=r.replace(/([:\(\)\\\/\-;.,؛،?!؟])(?:\s|)[:\\\/\-;.,؛،?!؟]/g,'$1');
        //cleanup phone insertion
        if(pse!=113 && pse!=291 && pse!=325){
            var m =Math.floor(r.length/2);
            y = r.substring(0,m);
            z = r.substring(m);
            z=z.replace(/([0-9])\s(?:(-)(?:\s|)|)([0-9])/g,'$1$2$3');
            z=z.replace(/(\+|)(?:\d{8,}|\d{2}[-\\\/]\d{6,})/g,' ');
            r=y+z;
        }
        //cleanup words repetition
        r=r.replace(/(^|\s)([a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]*?)\s\2(\s|$)/g,'$1$2$3');
        //append el waw to text
        r=r.replace(/\sو\s([\u0621-\u064a\u0750-\u077f\ufe81-\ufefc])/g,' و$1');
        //append el م to numbers
        r=r.replace(/([0-9,.])\sم\s/g,'$1م ');
        //seperate numbers from text
        r=r.replace(/([0-9]{2,})([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})/g,'$1 $2');
        r=r.replace(/([a-zA-Z\u0621-\u064a\u0750-\u077f\ufe81-\ufefc]{3,})([0-9]{2,})/g,'$1 $2');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        //replace \ with /
        r=r.replace(/\\/g, '/');
        //remove unwanted string starters
        r=r.replace(/^[-_+=,.;:"'*|\/\\~؛،]/g,'');
        //remove unwanted string endings
        r=r.replace(/(?:[-_+=,.;:*|\/\\~؛،]|\sت|تلفون|هاتف|موبايل|جوال)$/g,'');
        //cleanup repeated spaces
        r=r.replace(/\(\)/g,' ');
        //cleanup repeated spaces
        r=r.replace(/\s+/g,' ');
        //string trimmming
        r=r.replace(/^\s+|\s+$/g, '');
        if(ctac) brtl=irtl;
        else artl=irtl;
        tglTB(r.length);
        
        if(isAlt){
            processedAltText = r;
        }else{
            processedText = r;
        }            
        if(value!=r){
            idir(textarea[0]);
            rdrTFilter(isAlt, callback)
        }else{
            y=r.replace(/[a-z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
            z=r.replace(/[A-Z:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');    
            
            if(y.length > z.length*0.5){
                r = r.toLowerCase();
            }            
            if(isAlt){
                processedAltText = r;
                atxt=prepT(processedAltText,brtl);
            }else{
                processedText = r;
                txt=prepT(processedText,artl);
            }
            if(callback){
                callback();
            }
        }
    }
}*/
                                                                                                                                    
function nxt(e,c){
    var p=$p(e,3);
    var n=$c(p);
    ctac=c;
    var hs=hasT;
    var tl=artl;
    if(c){
        hs=hasAT;
        tl=brtl;
    }
    if(hs){
        if(ctac){
            if(artl==brtl){
                hidTB=1;
                var q=$f(n[3]);
                if(lang=='ar'){
                    if(artl){
                        q.innerHTML='يرجى إدخال التفاصيل باللغة الإنجليزية فقط';
                    }else{
                        q.innerHTML='يرجى إدخال التفاصيل باللغة العربية فقط';
                    }
                }else{
                    if(artl){
                        q.innerHTML='The Description should be English only';
                    }else{
                        q.innerHTML='The Description should be Arabic only';
                    }
                }
                fdT(n[2],0);       
                fdT(n[3],1);
                return;
            }
        }
        var pv,b;
        if(ctac){            
            fdT(n[0],0);
            fdT(n[1],0);
            fdT(n[2],0);
            fdT(n[3],0);
            pv=n[4];
            b=$f(n[4]);
            
            tar=$('textarea',$(p))[0];
            p=$p(p,2);
            extra['t']=1;
            
            /*var s=prepT(tar.value,brtl);
            atxt=s;*/
            rdrT(function(){
                atxt=prepT(processedAltText,brtl);
            });       
        }else {
            fdT(n[0],0);
            fdT(n[2],0);
            fdT(n[3],0);
            fdT(n[4],0);
            fdT(n[5],0);
            pv=n[6];
            b=$f(n[6]);
            
            tar=$('textarea',$(p))[0];
            var h=$f($a(p,2),2);
            var s;
            if(lang=='ar'){
                if(artl)s='هل تريد إدخال تفاصيل الإعلان بالإنجليزية؟';
                else s='هل تريد إدخال تفاصيل الإعلان بالعربية؟';
            }else{
                if(artl)s='Do you want to enter Description in English?';
                else s='Do you want to enter Description in Arabic?';
            }
            h.innerHTML=s+'<span class="et"></span>';
            
            /*var s=prepT(tar.value,artl);
            txt=s;*/
            rdrT(function(){
                txt=prepT(processedText,artl);
            }); 
        }
        b.className=tl?'ah ar':'ah en';
        b.innerHTML=prepT(tar.value,tl);
        if(extra['t']==1 && !ctac){
            var tr=$a(p,2);
            fdT(tr,1,'pi');
        }
        fdT(pv,1);
        fdT(p,0,'pi');
        tNext(p,1);
        if(extra['t']==1 && !ctac){edOT($f(tr),1);}
        SAVE=1;
        savAd();
    }else {
        hidTB=1;
        if(ctac){
            var q=$f(n[3]);
            if(lang=='ar'){
                q.innerHTML='30 حرف هو الحد الادنى لنص الإعلان';
            }else{
                q.innerHTML='Minimum of 30 characters is required';    
            }
            fdT(n[2],0);       
            fdT(n[3],1);
        }else {
            fdT(n[4],0);       
            fdT(n[5],1);
        }
    }
}
function etxt(e){
    var p=$p(e);
    if(p.className.match(/pi/)){
        var n=$c(p);
        fdT(n[6],0);
        fdT(p,1,'pi');
        fdT(n[0],1);
        fdT(n[2],1);
        fdT(n[3],1);
        fdT(n[4],1);
        fdT(n[5],0);
        tNext(p,0);
        var t=$f(n[3]);
        setCP(t,t.value.length);
    }
}
function setCP(e,pos,s) {
    if(e.setSelectionRange){
        e.focus();
	e.setSelectionRange(pos,pos);
    }else if(e.createTextRange) {
        var range = e.createTextRange();
	range.collapse(true);
	range.moveEnd('character', pos);
	range.moveStart('character', pos);
	range.select();
    }
    if(!s)e.scrollTop=9999;
}
function setTC(e,alt){
    var tl,n,t,p;
    if(alt){
        tl=brtl;
        n=$c($f($c(e,1)));
        t=$f(n[1]);
        p=$f(n[4]);
    }else {
        tl=artl;
        n=$c(e);
        t=$f(n[3]);
        p=$f(n[6]);
    }
    if(t.value.length){
        var s=prepT(t.value,tl);
        p.innerHTML=s;
        
        /*if(alt)atxt=s;
        else txt=s;*/
        
        if(alt){
            if(processedAltText.length > 0){
                rdrT(function(){
                    atxt= prepT(processedAltText,tl);                    
                });
            }else{
                tar=$('#altText')[0];
                hidTB=0;
                rdrT(function(){
                    setTC(e,alt);
                });
            }
        }else{
            if(processedText.length > 0){
                rdrT(function(){
                    txt= prepT(processedText,tl);                    
                });
            }else{
                tar=$('#mText')[0];
                hidTB=0;
                rdrT(function(){
                    setTC(e,alt);
                });    
            }
        }
    }
}
function parseDT(v,d){
    var n=v;
    if(d){
        if (n<12){
            n+=' صباحاً';
        }else if (n==12){
            n+=' ظهراً';
        }else if (n<16){
            n=(n-12)+' بعد الظهر';
        }else if (n<18){
            n=(n-12)+' عصراً';
        }else{
            n=(n-12)+' مساءً';
        }
    }else {
        if(n<12 || n==24){
            n=(n>12 ? (n-12) : n)+' AM';
        }else{
            n=(n>12 ? (n-12) : n)+' PM';
        }
    }
    return n;
}
function prepT(v,d){
    var l=cui.p.length;
    var t='';
    //v+='\u200B'+(d?' / للتواصل،':' / for contact and enquiries,');
    v+='\u200B / ';
    if(l){
        if(cut.t){            
            cut.t=parseInt(cut.t);
            cut.b=parseInt(cut.b);
            cut.a=parseInt(cut.a);
            switch(cut.t){
                case 1:
                    v+=(d?' يرجى الإتصال قبل ':' please call before ')+parseDT(cut.b,d)+' -';
                    break;
                case 2:
                    if(d)
                        v+=' يرجى الإتصال بين '+parseDT(cut.a,d)+' و '+parseDT(cut.b,d)+' -';
                    else 
                        v+=' please call between '+parseDT(cut.a,d)+' and '+parseDT(cut.b,d)+' -';
                    break;
                case 3:
                    v+=(d?' يرجى الإتصال بعد ':' please call after ')+parseDT(cut.a,d)+' -';
                    break;
                default:
                    break;
            }
        }
        var s,g,k,r={};
        for(var i=0;i<l;i++){
            g=cui.p[i]['v'];
            k=parseInt(cui.p[i]['t']);
            s='';
            if(!r[k]){
                switch(k){
                    case 1:
                        s=(d?'موبايل':'Mobile');
                        break;
                    case 2:
                        s=(d?'موبايل + فايبر':'Mobile + Viber');
                        break;
                    case 3:
                        s=(d?'موبايل + واتساب':'Mobile + Whatsapp');
                        break;
                    case 4:
                        s=(d?'موبايل + فايبر + واتساب':'Mobile + Viber + Whatsapp');
                        break;
                    case 5:
                        s=(d?'واتساب فقط':'Whatsapp only');
                        break;
                    case 7:
                        s=(d?'هاتف':'Phone');
                        break;
                    case 8:
                        s=(d?'تلفاكس':'Telefax');
                        break;
                    case 9:
                        s=(d?'فاكس':'Fax');
                        break;
                    case 13:
                        s=(d?'فايبر + واتساب فقط':'Viber + Whatsapp only');
                        break;
                    default:
                        break;
                }
                r[k]={
                    s:' - '+s+': ',
                    c:0
                }
            }  
            if(r[k].c)
                r[k].s+=d?' او ':' or ';
            r[k].s+='<span class="pn o'+k+'">'+g+'</span>';
            r[k].c++;
        }
        for (var i in r){
            t+=r[i].s;
        }
    }
    if(cui.b){
        t+=' - '+(d?'بلاكبيري مسنجر':'BBM pin')+': <span class="pn ob">'+cui.b+'</span>';
    }
    if(cui.t){
        t+=' - '+(d?'تويتر':'Twitter')+': <span class="pn ot">'+cui.t+'</span>';
    }
    if(cui.s){
        t+=' - '+(d?'سكايب':'Skype')+': <span class="pn os">'+cui.s+'</span>';
    }
    if(cui.e){
        t+=' - '+(d?'البريد الإلكتروني':'Email')+': <span class="pn oe">'+cui.e+'</span>';
    }
    v+=t.substr(2);
    return v;
}
function edOT(e,fl){
    var p=$p(e);
    if(p.id!='xct')p=$p(p,2);
    var n=$c($f($c(p,1)));
    fdT(n[4],0);
    fdT(n[3],0);
    fdT(n[0],1);
    fdT(n[1],1);
    fdT(n[2],1);
    if(fl || p.className.match(/pi/)) {  
        edO(e,p);        
    }
    var t=$f(n[1]);
    setCP(t,t.value.length);
}
function edOP(e,fl){
    var p=$p(e);
    if(p.id!='xpc')p=$p(p,2);
    if(fl || p.className.match(/pi/)) {
        var n=$c(p);
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],0);
        fdT(p,1,'pi');
        tNext(p,0); 
    }
}
function edO(e,p){  
    var n=$c(p);
    fdT(p,1,'pi');
    fdT(n[3],0);
    fdT(n[2],0);
    fdT(n[1],1);
    tNext(p,0);
}
function noO(e,xI){
    var p=$p(e,3);
    var n=$c(p);
    fdT(p,0,'pi');
    extra[xI]=2;
    fdT(n[1],0);
    fdT(n[2],0);
    fdT(n[3],1);
    tNext(p,1);
    if(SAVE)savAd()
}
function xcnl(e){
    var p=$p(e,5);
    var n=$c(p);
    fdT(n[1],0);
    if(extra['t']==2){
        fdT(n[2],0);
        fdT(n[3],1);
        fdT(p,0,'pi');
        tNext(p,1);
    }else{
        fdT(n[3],0);
        fdT(n[2],1);        
    }
}
/*
function noUp(e){
    var u,n;
    if(uplp){
        document.getElementById('upForm').src='/web/blank.html';
        uplp=0;
    }
    if(picL){
        u=$p(e,6);
        n=$c(u);
        fdT(n[4],0);
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],1);
    }else{
        if(extra.p==2){
            u=$p(e,5);
            noO(u,'p');
        }else{
            u=$p(e,8);
            n=$c(u);
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
    fdT($b(e),0,'off');
    $p(e,4).reset();
}

function upPic(e){
    uForm=e;
    var b=$f($c($f(e),3),2);
    if(!b.className.match(/off/g)){     
        var u=$f(e);
        var n=$c(u);
        fdT(n[0],0);
        fdT(n[1],0);
        fdT(b,0,'off');
        fdT(n[2],1,'liw');
        var st= (lang =='ar'?'جاري الرفع':'uploading');
        
        //n[2].innerHTML='<b class="load h_43"></b>';
        
        n[2].innerHTML='<b class="ctr h_43"><span id="uproh">'+st+' <span id="uprog">0%</span></span></b>';
        uprog=$('#uprog')[0];
        uproh=$('#uproh')[0];
        
        fdT(n[2],1);
        FLK=0;
        
        var uuid = uid;
        for (var i = 0; i < 32; i++) {
         uuid += Math.floor(Math.random() * 16).toString(16);
        }
        
        if((typeof Blob !== "undefined") && (typeof FormData !== "undefined") && idata){
            var f= dataURLtoBlob(idata);
            var fd = new FormData();
            fd.append('pic',f);
            var t=itype.replace(rg,'');
            var q='*'+'/'+'*';
            $.ajax({
               url:'/x-upload/?X-Progress-ID='+uuid+'&t='+t,
               type: "POST",
               data: fd,
               processData: false,
               contentType: false,
               success:function(rp){
                   if(rp=='0')
                    uploadCallback('');
                   else uploadCallback(rp);
               },error:function(rp){
                   uploadCallback();
               }
            });
            /*var ar=new aR();
            ar.onreadystatechange=function(){
                if (ar.readyState==4){
                    if (ar.status==200 || window.location.href.indexOf("http")==-1){
                        if(ar.responseText=='0')
                                uploadCallback('');
                               else uploadCallback(ar.responseText);
                    }else{
                        uploadCallback();
                    }
                }
            };
            ar.open("POST",'/x-upload/?X-Progress-ID='+uuid+'&t='+t, true);
            ar.send(fd);
        }else{
            uForm.action = '/x-upload/?X-Progress-ID='+uuid;
            uplp=1;
            uForm.submit();
        }
        uproi = window.setInterval(
            function () {
              upro(uuid);
            },
            1000
        );
        
        FLK=1;
    }
}
*/
function dataURLtoBlob(dataURL) { 
  var binary = atob(dataURL.split(',')[1]);
  var array = [];
  for(var i = 0; i < binary.length; i++) {
      array.push(binary.charCodeAt(i));
  }
  return new Blob([new Uint8Array(array)],{type:itype});
}
var uprog,uproh,uproi,perr=0,uplp=0,idata,itype;

var hasC = hasCanvas();
var canA = ((typeof Blob !== "undefined") && (typeof FormData !== "undefined"));
if (!(hasC && canA)){
    $('#picB')[0].multiple=false;
}
$('#picB').on('change',function(e){set2File(this)});
document.domain = 'mourjan.com';


var uptimers={};
function checkUploadLock(n){
    var len=0;
    for (var i in uptimers){
        len++;
    }
    if(len==0){
        FLK=1;
        var btn=$f($a(n),2);
        if(picL<5){
            $(btn).removeClass('hid');
        }
    }
}
function uproF(uid,uog,uoh){
    var w = 0;
    $.ajax({
        url:UP_URL+'/upload/progress.php',
        data:{
            UPLOAD_IDENTIFIER:uid,
            s:USID
        },
        type:'POST',
        success:function(rp){
            rp = eval(rp);
            if(rp.state == 'uploading'){
                w = Math.floor(100 * rp.received / rp.size);
                uog.html(w+'%');
                uoh.css('background-size',w+'% 100%');
            }else if(rp.state == 'done'){
            }else if (rp.state == 'error') {
                clearInterval(uptimers[uid]);
                delete uptimers[uid];
                checkUploadLock($p(uoh[0],5));
                if(rp.status==413){
                    uproFail(1,uoh,uog);
                }else{
                    uproFail(0,uoh,uog);
                }
            }
        },
        error:function(rp){
            uproFail(0,uoh,uog);
            clearInterval(uptimers[uid]);
            delete uptimers[uid];
            checkUploadLock($p(uoh[0],5));
        }
    });
}
function uproFail(size,uoh,uog){
    var m;
    if(uplp){
        $('#upForm').src='/web/blank.html';
        uplp=0;
    }
    if(size){
        m = lang=='ar' ? 'فشلت عملية رفع الصورة لكون حجم الملف كبير جداًً':'Upload failed because the image size is too large';
    }else{
        m = lang=='ar' ? 'فشلت عملية رفع الصورة':'Image upload failed';
    }      
    $($p(uoh[0],2)).addClass('error');
    $p(uoh[0]).innerHTML=m;
}
function renderImage(rp,li){
    li.innerHTML='<b class="ah ctr"><span title="'+(lang=='ar'?'إزالة الصورة':'remove picture')+'" class="pz pzd"></span><img src="'+isrc+'m/'+rp+'"/></b>';
    var s=$f(li,2);
    s.onclick=(function(i,e){return function(){delP(i,e)}})(rp,s);
}
var imgCounter=1;

function uploadFile(data, type, prog, file) {
    var uuid = uid;
    for (var i = 0; i < 32; i++) {
        uuid += Math.floor(Math.random() * 16).toString(16);
    }
    uuid+=new Date().getTime()+imgCounter;
    var uprog=$('.uprog',prog);
    var uproh=$('.uproh',prog);
    
    var rg=new RegExp('.*/');
    var t=type.replace(rg,'');
    
    var formdata = new FormData();
    formdata.append('UPLOAD_IDENTIFIER',uuid);
    formdata.append("pic", data);
    formdata.append("type", type);
    var ajax = new XMLHttpRequest();
    ajax.upload.addEventListener("progress", function(event){progressHandler(event,uproh,uprog)}, false);
    ajax.addEventListener("load", function(event){completeHandler(this.responseText,$p(prog,2),uuid)}, false);
    ajax.addEventListener("error", function(event){errorHandler(event,uuid,uproh,uprog,$p(prog,2))}, false);
    ajax.addEventListener("abort", function(event){abortHandler(event,uuid,uproh,uprog,$p(prog,2))}, false);
    ajax.open("POST", UP_URL+'/upload/?t='+t+'&s='+USID);
    ajax.send(formdata);
    
    uptimers[uuid]=1;
    imgCounter++;
}

function progressHandler(event,uproh,uprog) {
  var percent = Math.round((event.loaded / event.total) * 100);
  uprog.html(percent+'%');
  uproh.css('background-size',percent+'% 100%');
}

function completeHandler(event,p2prog,uuid) {
    uploadCB(event,p2prog,uuid);
    delete uptimers[uuid];
    checkUploadLock($p(p2prog,2));
}

function errorHandler(event,uuid,uproh,uprog,p2prog) {
    uproFail(0,uproh,uprog); 
    delete uptimers[uuid];
    checkUploadLock($p(p2prog,2));
}

function abortHandler(event,uuid,uproh,uprog,p2prog) {
    uproFail(0,uproh,uprog);
    delete uptimers[uuid]; 
    checkUploadLock($p(p2prog,2));
}

var curLi,curUid,iframeIdx=0;
function setFileRow(tul,type){
    var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"><img class="nopic" src="'+ucss+'/i/photo.png" /></li><li class="li2"></li></ul></li>');
    curLi=li[0];
    var cols = $('ul li',li);
    tul.appendChild(li[0]);
    var st= (lang =='ar'?'جاري الرفع':'uploading');
    cols[1].innerHTML = '<span class="uproh">'+st+'</span>';
    
    $(cols[1]).width( ($(cols[1].parentNode).width()-230) );
    
    var uuid = uid;
    for (var i = 0; i < 32; i++) {
        uuid += Math.floor(Math.random() * 16).toString(16);
    }
    uuid+=new Date().getTime()+imgCounter;
    curUid = uuid;
    var uprog=$('.uprog',cols[1]);
    var uproh=$('.uproh',cols[1]);
    if(!uForm){
        uForm = document.getElementById('picF');
    }
    
    uForm.action = UP_URL+'/upload/?s='+USID;
    $('#upKey').val(uuid);
    uplp=1;
    uForm.submit();
    /*
    uptimers[uuid] = window.setInterval(
        function () {
            uproF(uuid,uprog,uproh);
        },
        1000
    );*/
    imgCounter++;
}
function uploadCB(rp,li,uid){
    if(!rp)rp='';
    if(rp && rp != "0"){
        picL++;
        $c($f($p(li),2),1).innerHTML=picL+' / '+5;
        renderImage(rp,li);
        clearInterval(uptimers[uid]);
        delete uptimers[uid];
        checkUploadLock($p(li,2));
    }else{
        var uprog=$('.uprog',li);
        var uproh=$('.uproh',li);
        uproFail(0,uproh,uprog);  
        clearInterval(uptimers[uid]);
        delete uptimers[uid];
        checkUploadLock($p(li,2));
    }
}
function uploadCallback(rp){
    if(!rp)rp='';
    if(rp && rp != "0" && rp != ''){
        picL++;
        $c($f($p(curLi),2),1).innerHTML=picL+' / '+5;
        renderImage(rp,curLi);
        clearInterval(uptimers[curUid]);
        delete uptimers[curUid];
        checkUploadLock($p(curLi,2));
    }else{
        var uprog=$('.uprog',curLi);
        var uproh=$('.uproh',curLi);
        uproFail(0,uproh,uprog);  
        clearInterval(uptimers[curUid]);
        delete uptimers[curUid];
        checkUploadLock($p(curLi,2));
    }
}

navigator.browserSpecs = (function(){
    var ua= navigator.userAgent, tem, 
    M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
        return {name:'IE',version:(tem[1] || '')};
    }
    if(M[1]=== 'Chrome'){
        tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
        if(tem!= null) return {name:tem[1].replace('OPR', 'Opera'),version:tem[2]};
    }
    M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
    return {name:M[0],version:M[1]};
})();

function setFileCanvasRow(file,tul,submit){
    var opt={maxWidth:1024,canvas:true};
    loadImage.parseMetaData(file,function(dt){
        if (dt.exif) {
            opt.orientation = dt.exif.get('Orientation');
        }
        var img = loadImage(
            file,
            function(ig){                
                var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"></li><li class="li2"></li></ul></li>');
                var cols = $('ul li',li);
                tul.appendChild(li[0]);
                
                var durl,type;
                if (typeof navigator !=='undefined' 
                        && typeof navigator.browserSpecs !=='undefined'
                        && typeof navigator.browserSpecs.name !=='undefined'
                        && navigator.browserSpecs.name.toLowerCase() == 'chrome' && navigator.browserSpecs.version >= 50) {
                    type = 'image/webp';
                    durl = ig.toDataURL("image/webp");
                }else{
                    type = file.type;
                    durl = ig.toDataURL(file.type);                    
                }
                
                loadImage(
                    durl,
                    function(it){
                        cols[0].appendChild(it);
                    },
                    {maxWidth:190,maxHeight:140,canvas:true}
                );
                var ratio = (ig.width >= ig.height ? ig.width/ig.height : ig.height/ig.width);
                if(ig.width*ig.height < 640*480 || ratio < 1.3){
                    $(cols[0].parentNode).addClass("error");
                    cols[1].innerHTML = (lang == "ar" ? "حجم الصورة اما صغير او غير مناسب النشر":"Image size is too small or not suitable for publishing");
                    checkUploadLock($p(li[0],2));
                }else{
                    var st= (lang =='ar'?'جاري الرفع':'uploading');
                    cols[1].innerHTML = '<span class="uproh">'+st+' <span class="uprog">0%</span></span>';
                        uploadFile(durl,type,cols[1],file);
                }
                $(cols[1]).width( ($(cols[1].parentNode).width()-230) );
            },
            opt
        );
    });
}
/*function setFormCanvasRow(file,tul){
    var opt={maxWidth:650,canvas:true};
    loadImage.parseMetaData(file,function(dt){
        if (dt.exif) {
            opt.orientation = dt.exif.get('Orientation');
        }
        var img = loadImage(
            file,
            function(ig){                
                var li=$('<li onclick="edOP($p(this,2));" class="button"><ul class="imgRow"><li class="li1"></li><li class="li2"></li></ul></li>');
                curLi = li;
                var cols = $('ul li',li);
                tul.appendChild(li[0]);
                var durl = ig.toDataURL(file.type);
                loadImage(
                    durl,
                    function(it){
                        cols[0].appendChild(it);
                    },
                    {maxWidth:190,maxHeight:140,canvas:true}
                );
                if(ig.width < 200 || ig.height < 100){
                    $(cols[0].parentNode).addClass("error");
                    cols[1].innerHTML = (lang == "ar" ? "لم يتم رفع الصورة، يرجى رفع صورة بعرض لا يقل عن 200px وطول لا يقل عن 100px":"Image upload aborted. Please upload an image that has a minimum width of 200px and a minimum height of 100px");
                }else{
                    var st= (lang =='ar'?'جاري الرفع':'uploading');
                    cols[1].innerHTML = '<span class="uproh">'+st+' <span class="uprog">0%</span></span>';
                    
                    var uuid = uid;
                    for (var i = 0; i < 32; i++) {
                        uuid += Math.floor(Math.random() * 16).toString(16);
                    }
                    uuid+=new Date().getTime()+imgCounter;
                    curUid = uuid;
                    var uprog=$('.uprog',cols[1]);
                    var uproh=$('.uproh',cols[1]);
                    if(!uForm){
                        uForm = document.getElementById('picF');
                    }

                    uForm.action = UP_URL+'/x-upload/?X-Progress-ID='+uuid;
                    uplp=1;
                    uForm.submit();
                    
                    uptimers[uuid] = window.setInterval(
                        function () {
                            uproF(uuid,uprog,uproh);
                        },
                        1000
                    );
                    imgCounter++;
                    
                }
            },
            opt
        );
    });
}*/
function noPO(e){
    var p=$p(e,3);
    var n=$c(p);
    if(picL>0){
        extra['p']=1;
        fdT(n[1],1);
        fdT(n[2],1);
        fdT(n[3],0);
    }else {
        extra['p']=2;
        fdT(n[1],0);
        fdT(n[2],0);
        fdT(n[3],1);
    }
    fdT(p,0,'pi');
    tNext(p,1);
    if(SAVE)savAd()
}
function set2File(e){
    if(FLK){
        idata=null;
        FLK = 0;
        var fo=$($p(e,2));
        fo.addClass('hid');
        
        var p = $p(e,4);
        var tul = $f($b(p));
        var t=$('#noPBT',$p(fo[0]));
        t.removeClass('cl');
        t.html(lang == 'ar' ? 'التالي':'Next');
        if(typeof e.files !== "undefined" && e.files.length){
            pass=0;
            var j=e.files.length;
            if(j > 5-picL){
                j=5-picL;
            }
            for(var i=0; i<j; i++){
                var f=e.files[i];
                if(typeof f.type === 'undefined'){
                    f.type = 'image/'+f.name.match(/(?:png|jpg|jpeg|gif)$/gi);
                }
                if(f.type.match(/image\/(?:png|jpg|jpeg|gif)$/i)){
                    pass=1;
                    if(canA && hasC){
                        setFileCanvasRow(f,tul);
                    }else{              
                        setFileRow(tul,f.type);
                    }
                }
            }
            if(!pass){
                fo.removeClass('hid');
            }
        }else{
            var matches=null;
            if(matches = e.value.match(/\.(png|jpg|jpeg|gif)$/i)){
                setFileRow(tul,matches[1]);
            }
        }
        fo[0].reset();
    }
}

function delP(id,e){
    se();
    var m=(lang=='en' ? 'Delete this image?':'حذف هذه الصورة؟');
    if(confirm(m)){
        var li=$p(e,2);
        fdT(li,0);
        //var t=$p(li,2);
        var btd=$a($p(li,2));
        var b=$c($f($p(li),2),1);
        $.ajax({
            url:'/ajax-idel/',
            data:{i:id},
            type:'POST',
            success:function(rp){
                if(rp.RP){
                    picL--;
                    b.innerHTML=picL+' / '+5;
                    //fdT(p,1);
                    $p(li).removeChild(li);
                    if(!picL){
                        var t=$('#noPBT',btd);
                        t.addClass('cl');
                        t.html(lang == 'ar' ? 'كلا':'No');
                    }
                    checkUploadLock($b(btd));
                    /*if(!picL){
                        edOP(t,1);
                    }*/
                }else{
                    fdT(li,1);
                }
            },
            error:function(){
                fdT(li,1);
            }
        });
    }
}
function reUp(){
    if(uForm){
        perr=0;
        var e=uForm;
        var n=$c($f(uForm));
        var b=$f($c($f(e),3),2);
        fdT(b,1,'off');
        fdT(n[0],1);
        fdT(n[1],0);
        fdT(n[2],0);
    }
}

/*
function upro(uid){
    $.ajax({
        url:'/x-progress/',
        data:{'X-Progress-ID':uid},
        type:'GET',
        success:function(rp){
            rp = eval(rp);
            if (rp.state == 'done' || rp.state == 'uploading') {
                w = Math.floor(100 * rp.received / rp.size);
                uprog.innerHTML=w+'%';
                uproh.style.backgroundSize=w+'% 100%';
            }
            if (rp.state == 'done') {
                window.clearTimeout(uproi);
                uprog.innerHTML='100%';
                uproh.style.backgroundSize='100% 100%';
            }
            if (rp.state == 'error') {
                window.clearTimeout(uproi);
                if(rp.status==413){
                    uprof(1);
                }else{
                    uprof();
                }
            }
        },
        error:function(rp){
            uprof();
            window.clearTimeout(uproi);
        }
    });
}
function uprof(size){
    var m;
    perr=1;
    if(uplp){
        document.getElementById('upForm').src='/web/blank.html';
        uplp=0;
    }
    if(size){
        m = lang=='ar' ? 'فشلت عملية رفع الصورة لكون حجم الملف كبير جداً، <span class="button lnk" onclick="uForm.reset();reUp()">انقر(ي) هنا</span> لرفع صورة اصغر حجماً':'upload failed because the image size is too large, <span class="button lnk" onclick="uForm.reset();reUp()">click here</a> to upload a smaller image';
    }else{
        m = lang=='ar' ? 'فشلت عملية رفع الصورة، <span class="button lnk" onclick="reUp()">انقر(ي) هنا</span> للمحاولة مجدداً':'upload failed, <span class="button lnk" onclick="reUp()">click here</a> to try again';
    }
    var n=$c($f(uForm));
    fdT(n[2],0,'liw');
    n[2].innerHTML='<b class="ah">'+m+'</b>';
}
function dpic(e){    
    var p=$p(e,5);
    var n=$c(p);
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
}
function setFile(e){
    e.nextSibling.style.display='none';
    var o=$a($p(e,2));
    var b=$f($a(o,2),2);
    if(e.value){
        var v=e.value;
        if(v.match(/\.(?:png|jpg|jpeg|gif)$/i)){
            fdT(b,1,'off');
            fdT(o,0);
        }else{
            fdT(b,0,'off');
            fdT(o,1);
        }
    }else{
        fdT(b,0,'off');
        fdT(o,0);
    }
}
function set2File(e){    
    idata=null;
    var x;
    if (typeof e.target != 'undefined'){
        x = e.target;
    }else{
        x = e.srcElement;
    }
    if(x.files){
        var f=x.files[0];
        if(x.value.match(/\.(?:png|jpg|jpeg|gif)$/i)){
            x.nextSibling.style.display='block';
            var opt={maxWidth:650,canvas:true};
            if(!loadImage.parseMetaData(f,function(dt){
                if (dt.exif) {
                    opt.orientation = dt.exif.get('Orientation');
                }
                if (!loadImage(
                        f,
                        function(ig){
                            itype=f.type;
                            idata=ig.toDataURL(f.type);
                            setFile(x);
                        },
                        opt
                    )) {
                            setFile(x);
                }
            })){
                if (!loadImage(
                        f,
                        function(ig){
                            itype=f.type;
                            idata=ig.toDataURL(f.type);
                            setFile(x);
                        },
                        opt
                    )) {
                            setFile(x);
                }
            }
        }else{
            setFile(x);
        }
    }else{
        setFile(x);
    }
}

function uploadCallback(rp){
    if(!rp)rp='';
    if(uForm){
        var e=uForm;
        var n=$c($f(uForm));
        if(rp){
            var p=$c($p(e,2));
            
            picL++;
            
            var l=$c($f(p[0]),1);
            l.innerHTML=picL+' / '+5;
            
            var u=$f(p[1]);
            //var id=rp.replace(/\..* /,'');
            var li=document.createElement('LI');
            li.className='button';
            li.onclick=(function(i){return function(){edOP($b($p(i,4)))}})(li);
            li.innerHTML='<b class="ah ctr"><span class="button pz pzd"></span><img src="'+isrc+'m/'+rp+'"/></b>';
            u.appendChild(li);
            var s=$f(li,2);
            s.onclick=(function(i,e){return function(){delP(i,e)}})(rp,s);
            
            fdT(p[4],0);
            fdT(p[1],1);
            if(picL==5){
                fdT(p[2],0);
            }else {
                fdT(p[2],1);
            }
            fdT(p[3],1);
            e.reset();
            var b=$f($c($f(e),3),2);
            fdT(b,0,'off');
            fdT(n[0],1);
            fdT(n[1],0);
            fdT(n[2],0);
            
            gto(li);
        }else{
            uprof();
        }
    }
}
                                                                                                                                                                                        
function delP(id,e){
    se();
    var m=(lang=='en' ? 'Delete this image?':'حذف هذه الصورة؟');
    if(confirm(m)){
        var li=$p(e,2);
        fdT(li,0);
        var t=$p(li,2);
        var p=$a(t);
        var b=$c($f($b(t)),1);
        $.ajax({
            type:'POST',
            url:'/ajax-idel/',        
        dataType:'json',
            data:{i:id},
            success:function(rp){
                if(rp.RP){
                    picL--;
                    b.innerHTML=picL+' / '+5;
                    fdT(p,1);
                    $p(li).removeChild(li);
                    if(!picL){
                        edOP(t,1);
                    }
                }else{
                    fdT(li,1);
                }
            }
        });
    }
}

function reUp(){
    if(uForm){
        perr=0;
        var e=uForm;
        var n=$c($f(uForm));
        var b=$f($c($f(e),3),2);
        fdT(b,1,'off');
        fdT(n[0],1);
        fdT(n[1],0);
        fdT(n[2],0);
    }
}
function addPic(e){
    gto($p(e,3));
    var u=$c($p(e,1));
    fdT(u[1],0);
    fdT(u[2],0);    
    fdT(u[3],0);
    fdT(u[4],1);
}
*/


function edOV(e,fl){
    var p=$p(e);
    if(p.id!='xvd')p=$p(p,2);
    if(fl || p.className.match(/pi/)) { 
        var n=$c($f($c(p,1)));
        if(hasVd){
            n[3].innerHTML=tvl;
            fdT(n[2],0); 
            fdT(n[4],0);
            fdT(n[5],0);
            fdT(n[0],1);
            fdT(n[1],1);              
            fdT(n[3],1);
            fdT(n[6],1);
        }else{        
            fdT(n[3],0);
            fdT(n[4],0);
            fdT(n[5],0);
            fdT(n[6],0);
            fdT(n[0],1);
            fdT(n[1],1);
            fdT(n[2],1);
        }
        edO(e,p);    
    }
}
function shV(e,f){
    var p=$p(e);
    var c=$c(p);
    fdT(c[0],0);
    fdT(c[1],0);
    fdT(c[2],0);
    fdT(c[3],0);
    fdT(c[6],0);
    if(f){ 
        c[5].innerHTML='<b class="load"></b>';
        fdT(c[4],0);
        fdT(c[5],1);
        $.ajax({
            type:'POST',
            url:'/ajax-video-upload/',        
        dataType:'json',
            data:{action:0,lang:lang},
            success:function(rp){
                if(rp.RP){
                    c[5].innerHTML=rp.DATA.form;
                }else{                
                    fdT(c[0],1);
                    fdT(c[1],1);
                    fdT(c[2],1);
                    fdT(c[3],0);
                    fdT(c[4],0);
                    fdT(c[5],0);
                    fdT(c[6],0);
                }
            }
        });
    }else{
        fdT(c[5],0);
        fdT(c[4],1);
    }
}
function linkVd(e){
    var f=$f($b($p(e,2),2),2);
    var v=f.value;
    var m;
    try{
        var re=new RegExp("(?:youtube\.com|youtu\.be).*?v=(.*?)(?:$|&)","gi");
        m=re.exec(v);
    }catch(g){}
    if (m==null || !m[1]){
        fdT(f,0,'err');
    }else {
        var c=$c($p(e,3));
        fdT(c[0],0);
        var n=c[1];
        fdT(n,1,'liw');
        n.innerHTML='<b class="load h_49"></b>';
        fdT(n,1);
        $.ajax({
            type:'POST',
            url:'/ajax-video-link/',        
        dataType:'json',
            data:{id:m[1],lang:lang},
            success:function(rp){
                if (rp.RP) {
                   hasVd=1;
                   var u=$p(e,5);
                   var k=$c(u);
                   k[3].innerHTML=rp.DATA.video;
                   fdT(k[4],0);
                   fdT(k[5],0);
                   fdT(k[0],1);
                   fdT(k[1],1);
                   fdT(k[3],1);
                   fdT(k[6],1);
                   fdT(n,0);
                   fdT(c[0],1);
                   f.value='';
                   //$(".play",vl).colorbox({iframe:true, innerWidth:430, innerHeight:250});
                }else {
                   hasVd=0;
                   n.innerHTML='<b class="ah">'+rp.MSG+'</b>';
                   fdT(n,0,'liw');
                   fdT(c[1],1);
                }
            }
        });
        /*posA('/ajax-video-link/','lang='+lang+'&id='+m[1],function(rp){
            if (rp.RP) {
               hasVd=1;
               var u=$p(e,5);
               var k=$c(u);
               k[3].innerHTML=rp.DATA.video;
               fdT(k[4],0);
               fdT(k[5],0);
               fdT(k[0],1);
               fdT(k[1],1);
               fdT(k[3],1);
               fdT(k[6],1);
               fdT(n,0);
               fdT(c[0],1);
               f.value='';
               //$(".play",vl).colorbox({iframe:true, innerWidth:430, innerHeight:250});
            }else {
               hasVd=0;
               n.innerHTML='<b class="ah">'+rp.MSG+'</b>';
               fdT(n,0,'liw');
               fdT(c[1],1);
            }
        });*/
    }
}
function delV(e){
    se();
    var m=(lang=='en' ? 'Delete this video?':'حذف هذا الفيديو؟');
    if (confirm(m)){
        var u=$p(e,3);
        var c=$c(u);
        fdT(c[3],0);
        fdT(c[6],0);
        fdT(c[2],1);
        $.ajax({
            type:'POST',
            url:'/ajax-video-delete/',        
        dataType:'json',
            success:function(rp){
                if (rp.RP) {
                    hasVd=0;
                }
            }
        });
        /*posA('/ajax-video-delete/',null,function(rp){
            if (rp.RP) {
                hasVd=0;
            }
        });*/
    }
}
function cVUp(e,s){
    var u=$p(e,6);
    if(s)u=$p(u);
    edOV(u,1);
    if(s){
        $p(e,4).reset();
        fdT($b(e),0,'off');
    }else $f($b($p(e,2),2),2).value='';
}
function noVUp(e,s){
    var u,n;
    u=$p(e,3);
    n=$c(u);
    if(hasVd){
    }else{
        if(extra.v==2){
            noO($f(u),'v');
        }else{
            u=$p(u,2);
            n=$c(u);
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
}
function setVideo(e){
    var o=$a($p(e,2));
    var b=$f($a(o,2),2);
    if(e.value){
        var v=e.value;
        if(v.match(/\.(?:mov|mpeg4|avi|wmv|mpegps|flv|3gpp|webm|mp4|mpeg|3gp)$/i)){
            fdT(b,1,'off');
            fdT(o,0);
        }else{
            fdT(b,0,'off');
            fdT(o,1);
        }
    }else{
        fdT(b,0,'off');
        fdT(o,0);
    }
}

function upVid(e){
    vForm=e;
    var b=$f($c($f(e),3),2);
    if(!b.className.match(/off/g)){     
        var u=$f(e);
        var n=$c(u);
        fdT(n[0],0);
        fdT(n[1],0);
        fdT(b,0,'off');
        fdT(n[2],1,'liw');
        n[2].innerHTML='<b class="load h_43"></b>';
        fdT(n[2],1);
        FLK=0;
        vForm.submit();
        FLK=1;
    }
}
function updVd(ok,res){
    uploaded=1;
    if (ok) {
        hasVd=1;
        setTimeout('updCK("'+res+'");',5000);
    }else {
        hasVd=0;
        var c=$c($f(vForm));
        fdT(c[2],0,'liw');
        c[2].innerHTML=res;
    }
}
function updCK(res){
    $.ajax({
        type:'POST',
        url:'/ajax-upload-check/',        
        dataType:'json',
        data:{
            lang:lang
        },
        success:function(rp){
            if (rp.RP) {
                if(rp.DATA.P) {                                
                    setTimeout('updCK("'+res+'");',5000);
                }else {
                    hasVd=1;
                    var u=$p(vForm,2);
                    var k=$c(u);
                    k[3].innerHTML=rp.DATA.video;
                    fdT(k[4],0);
                    fdT(k[5],0);
                    fdT(k[0],1);
                    fdT(k[1],1);
                    fdT(k[3],1);
                    fdT(k[6],1);
                    vForm.reset();
                    fdT($f($c($f(vForm),3),2),0,'off');
                }
            }else {
                hasVd=0;
                var c=$c($f(vForm));
                fdT(c[2],0,'liw');
                c[2].innerHTML=rp.MSG;
            }
        }
    });
    /*posA('/ajax-upload-check/','lang='+lang,function(rp){
        if (rp.RP) {
            if(rp.DATA.P) {                                
                setTimeout('updCK("'+res+'");',5000);
            }else {
                hasVd=1;
                var u=$p(vForm,2);
                var k=$c(u);
                k[3].innerHTML=rp.DATA.video;
                fdT(k[4],0);
                fdT(k[5],0);
                fdT(k[0],1);
                fdT(k[1],1);
                fdT(k[3],1);
                fdT(k[6],1);
                vForm.reset();
                fdT($f($c($f(vForm),3),2),0,'off');
            }
        }else {
            hasVd=0;
            var c=$c($f(vForm));
            fdT(c[2],0,'liw');
            c[2].innerHTML=rp.MSG;
        }
    });*/
}
function dvid(e){    
    var p=$p(e,5);
    var n=$c(p);
    if(hasVd){
        var d=$c($f(n[1]),3);
        var a=$f(d);
        tvl=d.innerHTML;
        d.innerHTML='<b class="ctr ah">'+a.innerHTML+'</b>';
    }
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
}




function edOM(e,fl){
    var p=$p(e);
    if(p.id!='xmp')p=$p(p,2);
    mpU=p;
    if(p.className.match(/pi/)) {
        if(!hasM){
            initMap();
        }
        edO(e,p);    
    }else{
        if(fl){
            initMap();
        }
    }
}
function initMap(){
    window.scrollTo(0,0);
    var lm=0;
    var c;
    lat=ad.lat;
    lon=ad.lon;
    if(!mapD){
        var s1,s2,s3;
        if(lang=='ar'){
            s1='أضف';
            s2='إلغاء';
            s3='بلد، مدينة، شارع';
        }else{
            s1='Add';
            s2='Cancel';
            s3='Country, City, Street';
        }
        mapD=document.createElement('ul');
        mapD.className='ls po mapD nsh';
        mapD.innerHTML='<li><form onsubmit="mapSrch(this);return false;"><span onclick="myLoc(1)" class="button pz pzl"></span><div class="ipt"><input onfocus="fdT(this,1,\'err\')" class="qi" type="text" placeholder="'+s3+'" /></div><input type="submit" onclick="if(this.previousSibling.firstChild.value!=\'\')return true;else return false" class="button qb" value=""></li><li class="map load"></li><li><b class="ah ctr act2"><input onclick="savLoc(this)" class="button bt ok off" type="button" value="'+s1+'" /><span onclick="clMap()" class="button bt cl">'+s2+'</span></b></form></li>'; 
        c=$c(mapD);
        lm=1;
        map=c[1];
        window.onresize=function(){wsp();setMapR()};
    }
    var d=$('#main')[0];
    d.style.display='none';
    $('#footer')[0].style.display='none';
    document.body.insertBefore(mapD,d);
    setMapR();
    if(lm){        
        var sh=document.createElement('script');
        sh.type= 'text/javascript';
        sh.async=true;
        sh.src='//maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&sensor=true&callback=startMap&language='+lang;
        document.getElementsByTagName('HEAD')[0].appendChild(sh);
    }
}
function setMapR(){
    if(map){
        map.style.height=(window.innerHeight-117)+'px';
    }
}
var loc,marker,geo,infoW;
function startMap(){
    geo = new google.maps.Geocoder();
    infoW = new google.maps.InfoWindow({maxWidth: 200});
    var o={zoom:13,mapTypeId:google.maps.MapTypeId.HYBRID};
    vmap = new google.maps.Map(map,o);
    google.maps.event.addDomListener(vmap, "click", mapC);
    marker = new google.maps.Marker({map:vmap});
    google.maps.event.addListener(marker, "click", function() {
        infoW.open(vmap, marker);
    });
    myLoc();
}
function mapC(e){
    geo.geocode(
        {latLng:e.latLng},
        function(res, status) {
            if (status == google.maps.GeocoderStatus.OK && res[0]) {
                    lat=e.latLng.lat();
                    lon=e.latLng.lng();
                    marker.setMap(vmap);
                    marker.setPosition(e.latLng);
                    
                    setMI(res);
                    
                    cacheLoc(res);    
                    eMB(1);
             }else{
                 failM();
             }
        }
    );
}
function setMI(res){
    var v='',s='',t='';
    var a=res[0]['address_components'];
    var l=a.length;
    for(var i=0;i<l;i++){
        v=a[i]['long_name'];
        if(t!=v){
            t=v;
            if(s)s+=' - ';
            s+=v;
        }
    }
    infoW.setContent(s);
    infoW.open(vmap, marker);
}
function failM(){
    lat=0;
    lon=0;
    eMB(0);
}
function cacheLoc(loc,isSearch){
    if(!isSearch)isSearch=0;
    var obj=[];
    var l=loc.length;
    var k=0;
    for (var i=l-1;i>=0;i--) {
        obj[k]={latitude:loc[i].geometry.location.lat(),longitude:loc[i].geometry.location.lng(),type:loc[i].types[0],name:loc[i].address_components[0].long_name,short:loc[i].address_components[0].short_name,formatted:loc[i].formatted_address,vw_ne_lat:loc[i].geometry.viewport.getNorthEast().lat(),vw_ne_lon:loc[i].geometry.viewport.getNorthEast().lng(),vw_sw_lat:loc[i].geometry.viewport.getSouthWest().lat(),vw_sw_lon:loc[i].geometry.viewport.getSouthWest().lng()};
        if(loc[i].types[0]=='country' && obj[k].short.length>2){
            obj[k].short=loc[0].address_components[loc[0].address_components.length-1].short_name
        }
        k++;
    }
    $.ajax({
        type:'POST',
        url:'/ajax-location/',        
        dataType:'json',
        data:{
            lang:lang,
            loc:obj,
            search:isSearch
        }
    });
    //posA('/ajax-location/','lang='+lang+'&loc='+ppf(JSON.stringify(obj)),function(){});
}
function mapSrch(e){
    var c=$c(e);
    var q=$f(c[1]);
    var val=q.value;
    if (val) {
        geo.geocode({address:val},function(res, status) {
            if (status == google.maps.GeocoderStatus.OK && res[0]) {
                setMI(res);
                cacheLoc(res,1);
                setPos(getPos(res[0].geometry.location.lat(),res[0].geometry.location.lng()));
            }else{
                fdT(q,0,'err');
                failM();
            }
        });
    }
    return false;
}
function eMB(s){
    if(mapD){
    var b=$f($c(mapD,2),2);
    if(s) fdT(b,1,'off');
    else {
        fdT(b,0,'off');
        marker.setMap();
    }
    }
}
function savLoc(){
    if(lat && lon){
        hasM=1;
        var pos=getPos(lat,lon);
        geo.geocode(
            {latLng:pos},
            function(res,status) {
                if (status == google.maps.GeocoderStatus.OK && res[0]) {
                    var u=mpU;
                    var c=$c(u);
                    var g=$f(c[1],2);
                    g.innerHTML='<b class="load"></b>';
                    var s='',t='',v='',a=res[0]['address_components'];
                    var l=a.length;
                    for(var i=0;i<l;i++){
                        v=a[i]['long_name'];
                        if(t!=v){
                            t=v;
                            if(s)s+=' - ';
                            s+=v;
                        }
                    }
                    if(s){
                        ad.lat=lat;
                        ad.lon=lon;
                        ad.loc=s;
                        extra.m=1;
                        g.innerHTML='<b class="ah"><span onclick="clearLoc()" class="button pz pzd"></span>'+s+'</b>';
                    }
                }else{
                     hasM=0;
                     clearLoc();
                }
            }
        );
    }else{
        hasM=0;
    }
    clMap();
}
function setPos(pos,z){
    if(z)vmap.setZoom(15);
    vmap.setCenter(pos);
    marker.setMap(vmap);
    marker.setPosition(pos);
    eMB(1);
}
function getPos(la,lo){
    lat=la;
    lon=lo;
    return new google.maps.LatLng(lat,lon);
}
function myLoc(f){
    if(!f && ad.lat && ad.lon){
        marker.setMap(vmap);
        vmap.setZoom(14);
        infoW.setContent(ad.loc);
        infoW.open(vmap, marker);
        setPos(getPos(ad.lat,ad.lon));
    }else {
        failM();
        var df=new google.maps.LatLng(33.8852793,35.5055758);
        vmap.setZoom(14);
        vmap.setCenter(df);
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos=getPos(position.coords.latitude,position.coords.longitude);
                geo.geocode(
                    {latLng:pos},
                    function(res, status) {
                        if (status == google.maps.GeocoderStatus.OK && res[0]) {                            
                            marker.setMap(vmap);
                            setPos(pos);
                            setMI(res);
                         }else{
                             failM();
                         }
                    }
                );
            });
        }else{
            vmap.setZoom(4);
        }
    }
}
function clMap(){
    $p(mapD).removeChild(mapD);
    var d=$('#main')[0];
    d.style.display='block';
    $('#footer')[0].style.display='block';
    noMap();
    gto(mpU);
}
function noMap(){
    var u,n;
    u=mpU;
    n=$c(u);
    if(hasM){
        fdT(n[3],0);
        fdT(n[2],0);
        fdT(n[1],1);
    }else{
        if(extra.m==2){
            noO($f(n[2],2),'m');
        }else{
            fdT(n[1],0);
            fdT(n[3],0);
            fdT(n[2],1);
        }      
    }
}
function clearLoc(){
    se();
    lat=0;
    lon=0;
    ad.lat=0;
    ad.lon=0;
    ad.loc='';
    hasM=0;
    if(marker)marker.setMap();
    eMB(0);
    noMap();
}
function dmp(){
    var p=mpU;
    var n=$c(p);
    fdT(p,0,'pi');
    fdT(n[2],0);
    fdT(n[3],0);
    fdT(n[1],1);
    tNext(p,1);
    savAd();
}


function iniC(p){
    var a=['rou','puu','seu'];
    if(p)a=[p];
    var u,l;
    for (var i in a){
        u=$('#'+a[i])[0];
        if(u){
            l=$cL(u);
            if(l){
                for (var j=0;j<l;j++){
                    var w=$c(u,j);
                    $(w).addClass('button');
                    w.onclick=function(){liC(this)}
                }
            }
        }
    }
    u=$('#cnu')[0];
    l=$cL(u);
    var x,m,n;
    x=$c(u,0);
    $(x).addClass('button');
    x.onclick=function(){cnT(u)};
    x=$c(u,l-2);
    $(x).addClass('button');
    x.onclick=function(){cnT(u)};
    for (var j=1;j<l-2;j++){
        x=$c(u,j);
        if($cL(x)>1){
            m=$c($c(x,1));
            n=m.length;
            for (var i=0;i<n;i++){
                $(m[i]).addClass('button');
                m[i].onclick=function(){cnC(this)}
            }
        }else {
            $(x).addClass('button');
            x.onclick=function(){cnC(this)}
        }
    }        

    if(typeof imgs!=="undefined"){
        for(var i in imgs){
            var s=$('#sp'+i)[0];
            s.innerHTML='<img src="'+uimg+'/repos/m/'+imgs[parseInt(s.className.replace(/[a-z]/g,''))]+'" />';
        }
    }
}
iniC();
//load country codes if ad already have contact numbers
if(hNum)
    ldCN();

(function(){
    var sh=document.createElement('script');
    sh.type='text/javascript';
    sh.async=true;
    sh.src=uixf;
    var s = document.getElementsByTagName('head')[0];
    s.appendChild(sh);
})();

function savAdP(){
    //check if multi country selected
    var u=$('#cnu > li'),mu=0,ck=0,i=0,j=0,k=0,l=0,n;
    l=u.length;
    for(i=0;i<l;i++){
        if($(u[i]).hasClass("on")){
            if(ck){
                mu=1;
                break;
            }else{
                ck=1;
            }
        }
    }
    if(!mu){
        u=$('.sls');
        l=u.length;
        for(i=0;i<l;i++){
            n=u[i].childNodes;
            k=n.length;
            for(j=0;j<k;j++){
                if($(n[j]).hasClass("on")){
                    if(ck){
                        mu=1;
                        break;
                    }else{
                        ck=1;
                        break;
                    }
                }
            }
            if(mu){
                break;
            }
        }
    }
    if(mu){
        mCPrem()
    }else{
        var sp = $('#spinner').SelectNumber();
        Dialog.show("make_premium",null,function(){confirmPremium(sp)});
    }
}
function confirmPremium(sp){
    var str='',x='',y='';
    var v = sp.val();
    if(lang=='ar'){
        str='تمييز الاعلان لمدة ';
        if(v==1){
            x='ذهبية واحدة';
            y='يوم واحد';
        }else if(v == 2){            
            x='ذهبيتين';
            y='يومين';
        }else if(v < 11){
            x=v+' ذهبيات';
            y=v+' ايام';            
        }else{
            x=v+' ذهبية';
            y=v+'يوم ';
        }
        str+=y;
        str+=' لقاء ';
        str+=x+'؟';
    }else{
        str='Activate premium listing for ';
        if(v==1){
            x='1 Gold';
            y='1 Day';
        }else{
            x=v+' Golds';
            y=v+' Days';
        }
        str+=y;
        str+=' for the value of ';
        str+=x+'?';
    }
    Dialog.show("confirm_premium",str,function(){
        makePremium(v);
    },function(){
        savAdP();
    });
}
function makePremium(c){
    ad.budget = c;
    savAd(1);
}