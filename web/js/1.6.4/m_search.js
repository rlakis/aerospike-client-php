var switchTo5x=true,cad=[],serv=['email','facebook','twitter','googleplus','linkedin','sharethis'],servd=[],tmp,fbx,leb,peb,cli,sif;var fbf=function(){};function se(){var v=window.event;if(v){if(v.preventDefault)v.preventDefault();if(v.stopPropagation)v.stopPropagation()}};function spe(){var v=window.event;if(v){if(v.stopPropagation)v.stopPropagation()}};function pds(e){e.addEventListener('touchmove',ds)};function ds(e){e.preventDefault()};function pi(){posA("/ajax-pi/",null,function(){});setTimeout('pi();',300000)}function gto(e){var r=e.getBoundingClientRect();var doc=document.documentElement,body=document.body;var top=(doc&&doc.scrollTop||body&&body.scrollTop||0);window.scrollTo(0,r.top+top)}function $(i){return document.getElementById(i)}function $p(e,n){if(!n)n=1;while(n--){e=e.parentNode}return e}function $b(e,n){if(!n)n=1;while(n--){e=e.previousSibling}return e}function $a(e,n){if(!n)n=1;while(n--){e=e.nextSibling}return e}function $f(e,n){if(!n)n=1;while(n--){e=e.firstChild}return e}function $c(e,n){e=e.childNodes;if(n==null){return e}else{return e[n]}}function $cL(e,n){return e.childNodes.length}function fdT(u,s,c){if(!c)c='hid';if(s){var q=new RegExp('\s'+c+'|'+c,'ig');var k=u.className;k=k.replace(q,'');k=k.replace(/^\s+|\s+$/g,'');u.className=k}else{u.className+=' '+c}}function aR(){var amds=["Msxml2.XMLHTTP","Microsoft.XMLHTTP"];if(window.XMLHttpRequest){return new XMLHttpRequest()}else if(window.ActiveXObject){for(var i=0;i<amds.length;i++){try{return new ActiveXObject(activexmodes[i])}catch(e){}}}else return false};function posA(url,data,cbk){var ar=new aR();ar.onreadystatechange=function(){if(ar.readyState==4){if(ar.status==200||window.location.href.indexOf("http")==-1){var r;try{r=JSON.parse(ar.responseText)}catch(e){console.log(e)}cbk(r)}}};ar.open("POST",url,true);ar.setRequestHeader("Content-type","application/x-www-form-urlencoded");ar.send(data)};function idir(e){var v=e.value;if(v.match(/[\u0621-\u064a]/)){e.className='ar'}else{e.className='en'}}function ose(e){var o=(e.className=="srch"?0:1);var d=e.parentNode.nextSibling.firstChild;if(o){if(hasQ)document.location=d.firstChild.action;else{e.className="srch";d.className="sef"}}else{e.className="srch on";d.className="sef on";document.getElementById('q').focus()}};function wsp(){posA('/ajax-screen/','w='+document.body.clientWidth+'&h='+document.body.clientHeight,function(){})};function uPO(d,o){if(!d)d=document.getElementById("sil");if(!sif)sif=document.getElementById("sif");var e=sif;var s=e.style.display;if(!uid&&e.parentNode!=document.body){if(s=='block'){if(sif.parentNode&&sif.parentNode.parentNode&&sif.parentNode.parentNode.parentNode&&sif.parentNode.parentNode.parentNode.tagName=='DIV'){pF(document.getElementById('dFB'))}else{pF(document.getElementById('pFB'))}}document.body.insertBefore(e,document.getElementById('main'))}s=e.style.display;if(s=="block"){e.style.display="none";d.firstChild.className="k log"+(o?" on":"")}else{e.style.display="block";d.firstChild.className="k log"+(o?" on":"")+" op"}e.className="si"};function ppf(s){return encodeURIComponent(s)};function csif(){if(sif.parentNode==document.body){uPO(0,0);window.scrollTo(0,0)}else{if(sif.parentNode&&sif.parentNode.parentNode&&sif.parentNode.parentNode.parentNode&&sif.parentNode.parentNode.parentNode.tagName=='DIV'){pF(document.getElementById('dFB'))}else{pF(document.getElementById('pFB'))}}};function getWSct(){var d=document.documentElement,b=document.body;return(d&&d.scrollTop||b&&b.scrollTop||0)}function wo(u){if(u)document.location=u};wsp();var cs=document.createElement("link");cs.setAttribute("rel","stylesheet");cs.setAttribute("type","text/css");cs.setAttribute("href",ucss+"/mms.css");document.getElementsByTagName("head")[0].appendChild(cs);window.onresize=function(){fbf();wsp()};window.onerror=function(m,url,ln){posA('/ajax-js-error/','e='+ppf(m)+'&u='+ppf(url)+'&ln='+ln,function(){})};function ado(e){var li=$p(e,2);if(!leb){leb=$('aopt')}else if($p(leb)){var l=$p(leb);if(l!=li){fdT(l,1,'edit');peb.className="adn";acls(leb)}l.removeChild(leb);leb.style.display="none"}var cn=li.className;peb=e;if(cn.match(/edit/)){fdT(li,1,'edit');e.className="adn"}else{fdT(li,0,'edit');e.className="adn aup";var c=$c($f(leb));if(cn.match(/fav/)){c[0].style.display='none';c[1].style.display='block'}else{c[1].style.display='none';c[0].style.display='block'}li.appendChild(leb);leb.style.display="block";aht(li)}}function aht(e){var b=e.offsetTop+e.offsetHeight;var d=document.body;var wh=window.innerHeight;var t=wh+d.scrollTop;if(b>t){window.scrollTo(0,b-wh)}}function bshare(s,x){var dl=serv.length;var l,a,f,n;for(var i=0;i<dl;i++){l=document.createElement('div');a=document.createElement('a');l.appendChild(a);s.appendChild(l);f=document.createElement('label');f.innerHTML=serv[i];l.appendChild(f);n={"service":serv[i],"element":a,"url":x.l,"title":x.t,"type":"large","summary":x.c};if(x.p){n['image']=x.p}stWidget.addEntry(n)}}var dsh,dele;function share(e){if(dsh){if(!e)e=dele;if(e){var d=$p(e,2);var li=$p(d);var ok=1;if(stWidget&&stWidget.addEntry){var s=$c(d,1);if(!cli||(cli&&cli!=li)){s.innerHTML='';if(cad[li.id]){bshare(s,cad[li.id])}else{ok=0;sLD(e);posA('/ajax-ads/','id='+li.id+'&l='+lang,function(rp){if(rp&&rp.RP){var x=rp.DATA.i;cad[li.id]=x;bshare(s,x);eLD(e);acls(d);e.className='on';s.style.display='block';aht(li)}else{eLD(e);sms(d,xF,2)}})}}if(e.className=='on'){s.style.display='none';e.className=''}else if(ok){acls(d);e.className='on';s.style.display='block';aht(li)}cli=li}}}else{dele=e;sLD(e);if(dsh==null){setTimeout("(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src='http://w.sharethis.com/button/buttons.js';sh.onload=sh.onreadystatechange=function(){if(!dsh&&(!this.readyState||this.readyState=='loaded'||this.readyState=='complete')){dsh=1;stLight.options({publisher:'74ad18c8-1178-4f31-8122-688748ba482a',onhover:false,theme:'5',async:true});share()}};var s=document.getElementsByTagName('head')[0];s.appendChild(sh)})();",100);dsh=0}}}function sms(d,m,s){acls(d);$c(d,3).innerHTML='<h2>'+(s?'<span class="'+(s==1?'done':'fail')+'"></span>':'')+m+'</h2>';$c(d,3).style.display='block'}function rpA(e){var d=$p(e,2);var s=$c(d,2);if(e.className=='on'){s.style.display='none';e.className=''}else{acls(d);e.className='on';s.style.display='block';$c(s,1).focus();aht(d.parentNode)}}function rpS(e){var m=$b(e).value;var d=$p(e,2);var id=$p(d).id;if(m.length>0){posA('/ajax-report/','id='+id+'&msg='+ppf(m),function(){});$b(e).value='';sms(d,xAOK,1)}}function acls(d){dele=null;var l=$cL(d);var n=$c(d);n[1].style.display='none';n[2].style.display='none';n[3].style.display='none';if(l==5){n[4].style.display='none'}var t=$c($f(d));var c=t.length;for(var i=0;i<c;i++){t[i].className='';eLD(t[i])}}function pF(e){if(!sif)sif=$("sif");var d=$p(e,2);var s=$c(d,4);if(e.className=='on'){s.style.display='none';sif.style.display='none';e.className=''}else{acls(d);var t=$p(sif);if(s!=t){if(sif.style.display=='block'){if(t==document.body)uPO(0,0);else{acls($p(t))}}s.appendChild(sif)}var li=$p(d);var id=li.id;sif.style.display='block';e.className='on';s.style.display='block';posA('/ajax-favorite/','id='+id,function(){})}}function aF(e){if(!e.className.match(/load/)){sLD(e);var p=$p(e);var li=$p(p,2);var id=li.id;posA('/ajax-favorite/','s=0&id='+id,function(){e.style.display='none';$c(p,1).style.display='block';eLD(e);var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));var t='<span class="k fav on"></span>';sc.innerHTML+=t;li.className+=" fav";if(mod=='detail'){var d=$('d'+id);if(d){$c(d,1).innerHTML+=t}}})}}function rF(e){if(!e.className.match(/load/)){sLD(e);var p=$p(e);var li=$p(p,2);var id=li.id;posA('/ajax-favorite/','s=1&id='+id,function(){e.style.display='none';$c(p,0).style.display='block';eLD(e);var sc=(li.tagName=='LI'?$c(li,1):$a($p(p)));var c=sc.innerHTML;var t='<span class="k fav on"></span>';sc.innerHTML=c.replace(t,'');fdT(li,1,'fav');if(mod=='detail'){var d=$('d'+id);if(d){fdT($c(d,1),1,t)}}})}}function sLD(e){var c=$c(e);var l=$cL(e);if(l==3){e.removeChild(c[2]);l=2}for(var i=0;i<l;i++){c[i].style.visibility='hidden'}var d=document.createElement('span');d.className='load';e.appendChild(d);fdT(e,0,'on')}function eLD(e){fdT(e,1,'on');var c=$c(e);var l=$cL(e);if(l==3){e.removeChild(c[2]);l=2}for(var i=0;i<l;i++){c[i].style.visibility='visible'}}if(stat){tmp=true;if(typeof document.webkitHidden!="undefined"&&document.webkitHidden)tmp=false;if(tmp){posA("/ajax-stat/",'a='+stat+'&l='+page,function(){})}}if(mod=='search'||mod=='detail'){var ts=document.getElementsByTagName('time');var ln=ts.length;var time=parseInt((new Date().getTime())/1000);var d=0,dy=0,rt='';for(var i=0;i<ln;i++){d=time-parseInt(ts[i].getAttribute('st'));dy=Math.floor(d/86400);if(dy<=31){rt='';if(lang=='ar'){rt=since+' ';if(dy){rt+=(dy==1?"يوم":(dy==2?"يومين":dy+' '+(dy<11?"أيام" : "يوم")))}else{dy=Math.floor(d/3600);if(dy){rt+=(dy==1?"ساعة":(dy==2?"ساعتين":dy+' '+(dy<11?"ساعات" : "ساعة")))}else{dy=Math.floor(d/60);if(dy==0)dy=1;rt+=(dy==1?"دقيقة":(dy==2?"دقيقتين":dy+' '+(dy<11?"دقائق" : "دقيقة")))}}}else{if(dy){rt=(dy==1?'1 day':dy+' days')}else{dy=Math.floor(d/3600);if(dy){rt=(dy==1?'1 hour':dy+' hours')}else{dy=Math.floor(d/60);if(dy==0)dy=1;rt=(dy==1?'1 minute':dy+' minutes')}}rt+=' '+ago}ts[i].innerHTML=rt}}}if(sic.length){for(i in sic){var e=$('i'+i);e.innerHTML=sic[i]}}