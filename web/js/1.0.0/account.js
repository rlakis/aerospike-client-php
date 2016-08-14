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