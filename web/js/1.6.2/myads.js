window['_s']=function(a,v){window[a]=v};function pi(){$.ajax({type:'POST',url:'/ajax-pi/'});setTimeout('pi();',300000)}if(UID)setTimeout('pi();',300000);function cl(u){if(lang!='ar')u+=lang+'/';document.location=u}function idir(e,t){var v=e.value;if(t){v=v.replace(/^\s+|\s+$/g,'');e.value=v}if(v==''){e.className=''}else{if(v.match(/[\u0621-\u064a]/)){e.className='ar'}else{e.className='en'}}}var cs=document.createElement("link");cs.setAttribute("rel","stylesheet");cs.setAttribute("type","text/css");cs.setAttribute("href",ucss+"/imgs.css");document.getElementsByTagName("head")[0].appendChild(cs);menu=$('#menu');mp=$(menu.children()[0]).offset();$(window).bind('resize',function(){mp=$(menu.children()[0]).offset()});var load_menu=1;if(STO){if(typeof(sessionStorage.MENU)!=="undefined"){sessionStorage.removeItem('MENU')}if(typeof(sessionStorage.LSM)!=="undefined"&&sessionStorage.LSM==LSM){if(typeof(sessionStorage['MENU'+lang])!=="undefined"){$('body').append(sessionStorage['MENU'+lang]);mul=$(".mul");load_menu=0}}}if(load_menu){$.ajax({type:'GET',url:'/ajax-menu/',data:{h:ICH,_t:LSM},dataType:'html',success:function(d){if(d){$('body').append(d);mul=$(".mul");if(STO){try{sessionStorage.setItem('LSM',LSM);sessionStorage.setItem('MENU'+lang,d)}catch(ex){}}}}})}var aim=$("a[id],.c",menu);aim.click(function(e){e.preventDefault();e.stopPropagation();aim.removeClass('on');if(tmu){$("#u"+tmu).slideUp(400)}if(tmu==this.id){tmu=null}else{$(this).addClass('on');var u=$("#u"+this.id);if(u.length>0&&(typeof u[0] !=='undefined')){u[0].style.top=(mp.top+30)+"px";u[0].style.left=mp.left+"px";var id=this.id;tmu=id;u.slideDown(400)}}});$(document.body).click(function(e){if(tmu){$("#u"+tmu).slideUp(400);tmu=null;aim.removeClass('on')}});window.onerror=function(m,url,ln){$.ajax({url:'/ajax-js-error/',type:'POST',data:{e:m,u:url,ln:ln}})};function wo(u){if(u)document.location=u};function are(e){var d=mask(e);var i=e.parentNode.parentNode.id;$.ajax({type:"POST",url:"/ajax-arenew/",data:{i:i},dataType:"json",success:function(rp){if(rp.RP){d.removeClass("load");d.html(lang=='ar'?'تم تجديد الإعلان وبإنتظار معالجة محرك مرجان لإعادة النشر':'Ad is renewed and pending Mourjan system processing')}else{d.remove()}},error:function(){d.remove()}})}function adel(e,h){if(confirm(lang=='ar'?'هل أنت متأكد(ة)من حذف هذا الإعلان؟':'Are you sure that you want to delete this ad?')){var i=e.parentNode.parentNode.id;if(!h)h=0;var d=mask(e);$.ajax({type:"POST",url:"/ajax-adel/",data:{i:i,h:h},dataType:"json",success:function(rp){if(rp.RP){d.removeClass("load");d.html(lang=='ar'?'تم الحذف':'Deleted')}else{d.remove()}},error:function(){d.remove()}})}}function mask(e,r){var a;if(r)a=$(e);else a=$(e.parentNode.parentNode);var d=$("<div class=\'od load\'></div>");var w=a.outerHeight();d.height(w);d.width(a.outerWidth());d.css("line-height",w+"px");d.css('top',0);a.append(d);return d}function ahld(e){if(confirm(lang=='ar'?'هل أنت متأكد(ة)من إيقاف هذا الإعلان؟':'Are you sure that you want to stop this ad?')){var d=mask(e);var i=e.parentNode.parentNode.id;$.ajax({type:"POST",url:"/ajax-ahold/",data:{i:i},dataType:"json",success:function(rp){if(rp.RP){d.removeClass("load");d.html(lang=='ar'?'سيتم وقف عرض هذا الإعلان خلال لحظات':'Display of this ad will stop in few moments')}else{d.remove()}},error:function(){d.remove()}})}}function fsub(e){e.parentNode.submit()}var li=$("ul.ls li"),lip,ptm,atm={},liCnt,aCnt=0;function rePic(){var h=$(window).height();if(!lip){lip=$(".ig",li);liCnt=lip.length}lip.each(function(i,e){var c=e.parentNode.parentNode.id;if(!atm[i]){var r=e.getBoundingClientRect();var k=r.top;if(k>=-100&&k<=h+100){e.innerHTML=sic[e.parentNode.parentNode.id];atm[i]=1;aCnt++}}});if(liCnt==aCnt){$(window).unbind('scroll',trePic);$(window).unbind('scroll',trePic)}}function trePic(){if(ptm){clearTimeout(ptm);ptm=null}ptm=setTimeout('rePic()',100)}$(window).bind('scroll',trePic);$(window).bind('resize',trePic);trePic();if(uhc){var HSLD=0;(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src=uhc+'/min.js';sh.onload=sh.onreadystatechange=function(){if(!HSLD&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){HSLD=1;var isAc=document.location.search.match('archive')!==null?1:0;$.ajax({type:'POST',url:'/ajax-ga/',data:{u:(typeof uuid!=='undefined')?uuid:UID,x:isAc},dataType:'json',success:function(rp){if(rp.RP){if(!isAc){if(rp.DATA.d){var x=$('#statDv');var gS={chart:{spacingRight:0,spacingLeft:0},title:{text: rp.DATA.t+(lang=='ar'?' مشاهدة لإعلاناتي الفعالة':' impressions for my active ads'),style:{'font-weight':'bold','font-family':(lang=='ar'?'tahoma,arial':'verdana,arial'),'direction':(lang=='ar'?'rtl':'ltr')}},xAxis:{type: 'datetime',title:{text: null}},yAxis:{title:{text: null}},tooltip:{shared: true},legend:{enabled: false},series: [{type: 'line',name: 'Impressions',pointInterval:24 * 3600 * 1000,pointStart: rp.DATA.d,data: rp.DATA.c}]};x.highcharts(gS);x.after('<div class="sopt"><span class="bt"><span class="rj ren"></span></span></div>');$(':first',x.next()).click(function(e){var b=$(this);b.css('display','none');x.parent().addClass('load');$.ajax({type:'POST',url:'/ajax-ga/',data:{u:uuid?uuid:UID,x:isAc},dataType:'json',success:function(bp){x.parent().removeClass('load');b.css('display','block');if(bp.RP){if(!isAc){if(bp.DATA.d){gS.title.text=bp.DATA.t+(lang=='ar'?' مشاهدة لإعلاناتي الفعالة':' impressions for my active ads');gS.series=[{type: 'line',name: 'Impressions',pointInterval:24 * 3600 * 1000,pointStart: bp.DATA.d,data: bp.DATA.c}];x.highcharts(gS)}}}},error:function(bp){x.parent().removeClass('load');b.css('display','block')}})})}else{var x=$('#statDv');x.removeClass('load');x.addClass('hxf');x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');trePic()}}li.each(function(i,e){var o=$("div:last",e);var s=$('span:last',o);s.removeClass('load');if(typeof rp.DATA.a[e.id] !=='undefined'){s.html('<span class="rj stat"></span>'+rp.DATA.a[e.id]+(lang=='ar'?' مشاهدة':' imp'));s.addClass('lnk');s.click(function(i){var c=$(this);var std=$('.statDiv',e);if(std.length){if(std.hasClass('hid')){s.addClass('on');std.removeClass('hid')}else{std.addClass('hid');s.removeClass('on')}}else{var d=$('<div class="statDiv load"><div class="hld"></div></div>');o.before(d);s.addClass('on');var ix=e.id;$.ajax({type:'POST',url:'/ajax-ga/',data:{u:UID,a:e.id},dataType:'json',success:function(sp){if(sp.RP){if(sp.DATA.d){var gSA={chart:{spacingRight:0,spacingLeft:0},title:{text: sp.DATA.t+(lang=='ar'?' مشاهدة لهذا الإعلان':' impressions for this ad'),style:{'font-weight':'bold','font-family':(lang=='ar'?'tahoma,arial':'verdana,arial'),'direction':(lang=='ar'?'rtl':'ltr')}},xAxis:{type: 'datetime',title:{text: null}},yAxis:{title:{text: null}},tooltip:{shared: true},legend:{enabled: false},series: [{type: 'line',name: 'Impressions',pointInterval:24 * 3600 * 1000,pointStart: sp.DATA.d,data: sp.DATA.c}]};var x=$(':first',d);x.highcharts(gSA);x.after('<div class="sopt"><span class="bt"><span class="rj ren"></span></span></div>');$(':first',x.next()).click(function(e){var b=$(this);b.css('display','none');x.parent().addClass('load');$.ajax({type:'POST',url:'/ajax-ga/',data:{u:UID,a:ix},dataType:'json',success:function(bp){x.parent().removeClass('load');b.css('display','block');if(bp.RP){if(!isAc){if(bp.DATA.d){gSA.title.text=bp.DATA.t+(lang=='ar'?' مشاهدة لهذا الإعلان':' impressions for this ad');gSA.series=[{type: 'line',name: 'Impressions',pointInterval:24 * 3600 * 1000,pointStart: bp.DATA.d,data: bp.DATA.c}];x.highcharts(gSA)}}}},error:function(bp){x.parent().removeClass('load');b.css('display','block')}})})}else{var x=d;x.removeClass('load');x.addClass('hxf');x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display')}}else{var x=d;x.removeClass('load');x.addClass('hxf');x.html(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات إعلانك':'Mourjan system failed to load your ad statistics')}}})}})}else{s.html('<span class="rj stat"></span> NA')}})}else{var x=$('#statDv');x.removeClass('load');x.addClass('hxf');x.html(lang=='ar'?'فشل محرك مرجان بالحصول على إحصائيات حسابك':'Mourjan system failed to load your statistics');trePic();li.each(function(i,e){var o=$("div:last",e);var s=$('span:last',o);s.remove()})}}})}};head.insertBefore(sh,head.firstChild)})()}if(PEND&&WSO){var wio=io.connect("db.mourjan.com:1313");wio.on("ads",function(data){if(typeof data.c !=='undefined'){data.c=parseInt(data.c);var id=data.id;var p=li.parent();var ei=$('#'+id,p);var o=parseInt(ei.attr('status'));if(isNaN(o)||o>0){var m,t,d;$('.od',ei).remove();if(typeof ei[0] !=='undefined'){switch(data.c){case -1: ei.attr('status',-1);m=mask(ei[0],1);m.removeClass('load');if(lang=='ar'){t='تم الحذف'}else{t='Deleted'}m.html(t);break;case 0: ei.attr('status',0);m=mask(ei[0],1);m.removeClass('load');if(lang=='ar'){t='جاري التعديل، يجب تحديث الصفحة'}else{t='editting in progress,refresh page'}m.html(t);break;case 1: if(lang=='ar'){t='بإنتظار موافقة النشر من قبل محرري الموقع'}else{t='Waiting for Editorial approval'}d=$('<div class="nb nbw"><span class="wait"></span>'+t+'</div>');$('.nb',ei).remove();ei.prepend(d);ei.attr('status',1);break;case 2: if(lang=='ar'){t='تمت الموافقة وبإنتظار العرض من قبل محرك مرجان'}else{t='Approved and pending Mourjan system processing'}d=$('<div class="nb nbg"><span class="done"></span>'+t+'</div>');$('.nb',ei).remove();ei.prepend(d);ei.attr('status',2);break;case 3: if(lang=='ar'){t='تم رفض عرض هذا الإعلان'}else{t='Rejected By Admin'}if(typeof data.m !=='undefined'){t+=': '+data.m}d=$('<div class="nb nbr"><span class="fail"></span>'+t+'</div>');$('.nb',ei).remove();ei.prepend(d);ei.attr('status',3);break;case 7: m=mask(ei[0],1);ei.attr('status',7);m.removeClass('load');var lnk;if(ULV==9){lnk=$('.oct',ei).children().get(1).href+'#'+id}else{lnk='/myads/'+(lang=='ar'?'':'en/')+'#'+id}if(lang=='ar'){t='الإعلان أصبح فعالاً، <a href="'+lnk+'">انقر(ي)هنا</a> لتفقد الإعلانات الفعالة'}else{t='Ad is online now,<a href="'+lnk+'">click here</a> to view Active Ads'}m.html(t);break}}}}});wio.emit("hook_myads",[UIDK]);wio.on('reconnect',function(){wio.emit("hook_myads",[UIDK,ULV])})}