window['_s']=function(a,v){window[a]=v};function pi(){$.ajax({type:'POST',url:'/ajax-pi/'});setTimeout('pi();',300000)}if(UID)setTimeout('pi();',300000);function cl(u){if(lang!='ar')u+=lang+'/';document.location=u}function tglLG(e){e=$(e);if(e.hasClass('on')){e.parent().animate({height:'60px'},500);e.removeClass('on')}else{e.parent().animate({height:'560px'},500);e.addClass('on')}}function idir(e,t){var v=e.value;if(t){v=v.replace(/^\s+|\s+$/g,'');e.value=v}if(v==''){e.className=''}else{if(v.match(/[\u0621-\u064a]/)){e.className='ar'}else{e.className='en'}}}function isEmail(v){if(v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/))return true;return false}var cs=document.createElement("link");cs.setAttribute("rel","stylesheet");cs.setAttribute("type","text/css");cs.setAttribute("href",ucss+"/imgs.css");document.getElementsByTagName("head")[0].appendChild(cs);menu=$('#menu');mp=$(menu.children()[0]).offset();$(window).bind('resize',function(){mp=$(menu.children()[0]).offset()});var load_menu=1;if(STO){if(typeof(sessionStorage.MENU)!=="undefined"){sessionStorage.removeItem('MENU')}if(typeof(sessionStorage.LSM)!=="undefined"&&sessionStorage.LSM==LSM){if(typeof(sessionStorage['MENU'+lang])!=="undefined"){$('body').append(sessionStorage['MENU'+lang]);mul=$(".mul");load_menu=0}}}if(load_menu){$.ajax({type:'GET',url:'/ajax-menu/',data:{h:ICH,_t:LSM},dataType:'html',success:function(d){if(d){$('body').append(d);mul=$(".mul");if(STO){sessionStorage.setItem('LSM',LSM);sessionStorage.setItem('MENU'+lang,d)}}}})}var aim=$("a[id],.c",menu);aim.click(function(e){e.preventDefault();e.stopPropagation();aim.removeClass('on');if(tmu){$("#u"+tmu).slideUp(400)}if(tmu==this.id){tmu=null}else{$(this).addClass('on');var u=$("#u"+this.id);if(u.length>0&&(typeof u[0] !=='undefined')){u[0].style.top=(mp.top+30)+"px";u[0].style.left=mp.left+"px";var id=this.id;tmu=id;u.slideDown(400)}}});$(document.body).click(function(e){if(tmu){$("#u"+tmu).slideUp(400);tmu=null;aim.removeClass('on')}});(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s)})();if(share){(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/en_US/all.js#xfbml=1&appId=184370954908428";fjs.parentNode.insertBefore(js,fjs)}(document,'script','facebook-jssdk'));_s('serv',['email','facebook','twitter','googleplus','linkedin','sharethis']);_s('_STL',false);(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='https://ws.sharethis.com/button/buttons.js';sh.onload=sh.onreadystatechange=function(){if(!_STL&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){_STL=true;if(typeof stLight !=='undefined'){stLight.options({publisher:'74ad18c8-1178-4f31-8122-688748ba482a',onhover:false,theme:'1',async:'true',embeds:'true',headerbg:'#3087B4'})}}};var s=document.getElementsByTagName('head')[0];s.appendChild(sh)})()}window.onerror=function(m,url,ln){$.ajax({url:'/ajax-js-error/',type:'POST',data:{e:m,u:url,ln:ln}})};