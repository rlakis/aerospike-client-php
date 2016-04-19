window['_s']=function(a,v){
    window[a]=v;
};
function pi(){
    $.ajax({type:'POST',url:'/ajax-pi/'});
}
if(UID)setInterval(pi,300000);
function cl(u){
    if(lang!='ar')u+=lang+'/';
    document.location=u
}
function idir(e,t){
    var v=e.value;
    if(t){
        v=v.replace(/^\s+|\s+$/g, '');
        e.value=v;
    }
    if(v==''){
        e.className='';
    }else{
        var y=v.replace(/[^\u0621-\u064a\u0750-\u077f]|[:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        var z=v.replace(/[\u0621-\u064a\u0750-\u077f:\\\/\-;.,؛،?!؟*@#$%^&_+'"|0-9\s]/g,'');
        if(y.length > z.length*0.5){
            e.className='ar';
        }else{
            e.className='en';
        }
    }
}
function isEmail(v){
    if(v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/))
        return true;
    return false;
}

var cs=document.createElement("link");cs.setAttribute("rel", "stylesheet");cs.setAttribute("type", "text/css");cs.setAttribute("href", ucss+"/imgs.css");document.getElementsByTagName("head")[0].appendChild(cs);

menu=$('#menu');
mp=$(menu.children()[0]).offset();
$(window).bind('resize',function() {
    mp=$(menu.children()[0]).offset()
});
var load_menu=1;
if(STO){
    if(typeof(sessionStorage.MENU)!=="undefined"){
        sessionStorage.removeItem('MENU');
    }
    if(typeof(sessionStorage.LSM)!=="undefined" && sessionStorage.LSM==LSM){
        if(typeof(sessionStorage['MENU'+lang])!=="undefined"){
            $('body').append(sessionStorage['MENU'+lang]);
            mul=$(".mul");
            load_menu=0;
        }
    }
}
/*ITC="ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch;*/
if(load_menu){
$.ajax({
    type:'GET',
    url:'/ajax-menu/',
    data:{
        h:ICH,
        _t:LSM,
        c:hasCvs
    },
    dataType:'html',
    success:function(d){
        if(d){ 
            $('body').append(d);
            mul=$(".mul");
            if(STO){
                try{
                    sessionStorage.setItem('LSM', LSM);
                    sessionStorage.setItem('MENU'+lang, d);
                }catch(ex){}
            }
            /*if(ITC){
                
            }else{
                mul.hover(function(e){
                    if (tmr) {
                        clearTimeout(tmr);
                        func=null
                    }
                },
                function(e){
                    $(this).slideUp(200)
                });
            }*/
        }
    }
});
}
var aim=$("a[id],.c",menu);
    aim.click(function(e){
        e.preventDefault();
        e.stopPropagation();
        aim.removeClass('on');
        if(tmu){
            $("#u"+tmu).slideUp(400);
        }
        if(tmu==this.id){
            tmu=null;
        }else{
            $(this).addClass('on');
            var u=$("#u"+this.id);
            if(u.length>0 && (typeof u[0] !== 'undefined')){
                u[0].style.top=(mp.top+30)+"px";
                u[0].style.left=mp.left+"px";
                var id=this.id;
                tmu=id;
                u.slideDown(400);
            }
        }
    });  
    
$(document.body).click(function(e){
    if(tmu){
        $("#u"+tmu).slideUp(400);
        tmu=null;
        aim.removeClass('on');
    }
});
/*}else{
    $("a[id],.c",menu).hover(function(e){
        tmd=setTimeout('sldown("'+this.id+'")',200);
    },function(e){
        if(tmu)clearTimeout(tmu);
        if(tmd)clearTimeout(tmd);
        var id=this.id;
        func=function(){
            $("#u"+id).slideUp(400);
        };
        tmr=setTimeout("if(func)func();",300);
    });  
}
function sldown(id){
    if (tmr) {
        clearTimeout(tmr);
        tmr=null;
        func=null;
    }
    for(var i in DDM){
        DDM[i].slideUp(400);
    }
    var u=$("#u"+id);
    DDM[id]=u;
    if(u.length>0 && (typeof u[0] !== 'undefined')){
        u[0].style.top=(mp.top+30)+"px";
        u[0].style.left=mp.left+"px";
        fupc=function(){
            u.slideDown(400)
        };
        tmu=setTimeout("fupc();",100);
    }
}*/
/*(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();*/
if(jsLog)
window.onerror = function(m, url, ln) {
    if(m!='Script error.')
    $.ajax({
        url:'/ajax-js-error/',
        type:'POST',
        data:{
            e:m,
            u:url,
            ln:ln
        }
    });
};


function clsOpen(){
    var li=$("ul.ts li.open");
    if (li.length){
        $(".fm",li).css("display","none");
        $(".lm",li).css("display","block");
        li.removeClass("open");
        $("[name]",$("#"+curForm)).each(function(i,e){
            var n=$(e).next();
            if(n.length) n.remove();
        });
        var nb=$(".notice",actBx);
        nb.html("");
    }
}
function save(){
    var nb=$(".notice",actBx);
    nb.html("");
    nb.addClass("load");
    var f=$("#"+curForm);
    var d=$("[name]",f);
    if (validate(d)) {
        var vals={};
        try{
            d.each(function(i,e){
                e=$(e);
                if (e.attr("type")=="checkbox"){
                    if(e.prop("checked")){
                        vals[e.attr("name")]=1;
                    }else{
                        vals[e.attr("name")]=0;
                    }
                }else {
                    vals[e.attr("name")]=e.val();
                }
            });
            $.ajax({
                type:"POST",
                url:"/ajax-account/",
                cache:false,
                data:{
                    form:curForm,
                    fields:vals,
                    lang:lang
                },
                dataType:"json",
                success:function(rp){
                    d.each(function(i,e){
                        $(e).attr("disabled",false)
                    });
                    $("input",actBx).attr("disabled",false);
                    if (rp.RP) {
                        if(rp.DATA.fields){
                            var es=rp.DATA.fields;
                            var p=$(".lm",d.parents("li"));
                            d.each(function(i,e){
                                    e=$(e);
                                    if (es[e.attr("name")]){
                                        e=$(e);
                                        var v=es[e.attr("name")];
                                        var c=$('[label="'+e.attr("name")+'"]',p);
                                        if (c.length){
                                            c.html(v[2] ? v[2] : v[1]);
                                        }
                                        if (v[0]=="checked") {
                                            if (v[1]=="checked"){
                                                e.attr("checked",true);
                                            }else {
                                                e.attr("checked",false);
                                            }
                                            var h=e.parent().html();
                                        }else {
                                            e.attr(v[0],v[1]);
                                        }
                                        var h=e.parent().html();
                                        e.replaceWith($(h));
                                    }
                                });
                                clsOpen();
                            }
                        }else {
                            if (isNaN(rp.MSG)){
                                nb.html('<span class="fail"></span>'+rp.MSG);
                            }
                            if(rp.DATA.fields){
                                var es=rp.DATA.fields;
                                d.each(function(i,e){
                                    e=$(e);
                                    if (es[e.attr("name")]){
                                        setFN(e,es[e.attr("name")]);
                                    }
                                });
                            }
                        }
                        nb.removeClass("load");
                    },
                    error:function(){
                        nb.removeClass("load");
                        nb.html('<span class="fail"></span>'+(lang=='ar'? 'فشل محرك مرجان في تحديث المعلومات الخاصة بك - <a href=\'/contact/\'>اطلعنا بالأمر</a>':'Mourjan system failed to update your info - <a href=\'/contact/en/\'>Tell us about it</a>'));
                        d.each(function(i,e){
                            $(e).attr("disabled",false)
                        });
                        $("input",actBx).attr("disabled",false);
                    }
                });
            }catch(e){
                console.log(e)
            }
        }else {
            nb.removeClass("load");
            d.each(function(i,e){
                $(e).attr("disabled",false)
            });
            $("input",actBx).attr("disabled",false);
        }
        return false;
    };
function setFN(e,msg){
    var s=$("<span class='notice'><span class='fail'></span>"+msg+"</span>");
    $(e).after(s);
};
function validate(d){
    var r=true;
    d.each(function(i,e){
        var ps=false,em="";
        e=$(e);
        var n=e.next();
        if(n.length) n.remove();
        var v=e.val();
        var min=e.attr("minLength") ? parseInt(e.attr("minLength")):0;
        if(e.attr("req") && (v.length==0 || v.length<min)) ps=true;
        em=e.attr("yErr");
        if (!ps){
            var re=e.attr("regex");
            if (re){
                if(re=='email'){
                    ps=!isEmail(v);
                }else{
                    try{
                        re=new RegExp(re);
                        var o=e.attr("regexMatch")=="true"?true:false;
                        var f=re.exec(v);
                        if (f == null) {
                            if (o) ps=true;
                        }else {
                            if(!o)ps=true;
                        }
                        if(ps){
                            var tm=e.attr("vErr");
                            if(tm)em=tm;
                        }
                    }catch(x){console.log(x)}
                }
            }
        }
        if (ps){
            r=false;
            setFN(e,em);
        }
    });
    if (r){
        d.each(function(i,e){
            $(e).attr("disabled",true)
        });
        $("input",actBx).attr("disabled",true);
    }return r;
};



_s('actBx',null);
$(".lnk.edit").click(function(){
    if(!actBx)actBx=$(".am");
    clsOpen();
    var e=$(this);
    var p=e.parent();
    var li=p.parent();
    curForm=li.attr("id");
    p.css("display","none");
    p.next().css("display","block");
    p.next().append(actBx);
    li.addClass("open");
});
$("[name]").focus(function(i,e){
    var n=$(this).next();
    if(n.length)n.remove();
});