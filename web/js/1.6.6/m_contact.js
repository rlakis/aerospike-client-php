var switchTo5x=true,cad=[],serv=['email','facebook','twitter','googleplus','linkedin','sharethis'],servd=[],tmp,fbx,leb,peb,cli,sif;var fbf=function(){};function se(){var v=window.event;if(v){if(v.preventDefault)v.preventDefault();if(v.stopPropagation)v.stopPropagation()}};function spe(){var v=window.event;if(v){if(v.stopPropagation)v.stopPropagation()}};function pds(e){e.addEventListener('touchmove',ds)};function ds(e){e.preventDefault()};function pi(){posA("/ajax-pi/",null,function(){});setTimeout('pi();',300000)}function gto(e){var r=e.getBoundingClientRect();var doc=document.documentElement,body=document.body;var top=(doc&&doc.scrollTop||body&&body.scrollTop||0);window.scrollTo(0,r.top+top)}function $(i){return document.getElementById(i)}function $p(e,n){if(!n)n=1;while(n--){e=e.parentNode}return e}function $b(e,n){if(!n)n=1;while(n--){e=e.previousSibling}return e}function $a(e,n){if(!n)n=1;while(n--){e=e.nextSibling}return e}function $f(e,n){if(!n)n=1;while(n--){e=e.firstChild}return e}function $c(e,n){e=e.childNodes;if(n==null){return e}else{return e[n]}}function $cL(e,n){return e.childNodes.length}function fdT(u,s,c){if(!c)c='hid';if(s){var q=new RegExp('\s'+c+'|'+c,'ig');var k=u.className;k=k.replace(q,'');k=k.replace(/^\s+|\s+$/g,'');u.className=k}else{u.className+=' '+c}}function aR(){var amds=["Msxml2.XMLHTTP","Microsoft.XMLHTTP"];if(window.XMLHttpRequest){return new XMLHttpRequest()}else if(window.ActiveXObject){for(var i=0;i<amds.length;i++){try{return new ActiveXObject(activexmodes[i])}catch(e){}}}else return false};function posA(url,data,cbk){var ar=new aR();ar.onreadystatechange=function(){if(ar.readyState==4){if(ar.status==200||window.location.href.indexOf("http")==-1){var r;try{r=JSON.parse(ar.responseText)}catch(e){console.log(e)}cbk(r)}}};ar.open("POST",url,true);ar.setRequestHeader("Content-type","application/x-www-form-urlencoded");ar.send(data)};function idir(e){var v=e.value;if(v.match(/[\u0621-\u064a\u0750-\u077f]/)){e.className='ar'}else{e.className='en'}}function ose(e){var o=(e.className=="srch"?0:1);var d=e.parentNode.nextSibling.firstChild;if(o){if(hasQ)document.location=d.firstChild.action;else{e.className="srch";d.className="sef"}}else{e.className="srch on";d.className="sef on";document.getElementById('q').focus()}};function wsp(){posA('/ajax-screen/','w='+document.body.clientWidth+'&h='+document.body.clientHeight,function(){})};function uPO(d,o){if(!d)d=document.getElementById("sil");if(!sif)sif=document.getElementById("sif");var e=sif;var s=e.style.display;if(!uid&&e.parentNode!=document.body){if(s=='block'){if(sif.parentNode&&sif.parentNode.parentNode&&sif.parentNode.parentNode.parentNode&&sif.parentNode.parentNode.parentNode.tagName=='DIV'){pF(document.getElementById('dFB'))}else{pF(document.getElementById('pFB'))}}document.body.insertBefore(e,document.getElementById('main'))}s=e.style.display;if(s=="block"){e.style.display="none";d.firstChild.className="k log"+(o?" on":"")}else{e.style.display="block";d.firstChild.className="k log"+(o?" on":"")+" op"}e.className="si"};function ppf(s){return encodeURIComponent(s)};function csif(){if(sif.parentNode==document.body){uPO(0,0);window.scrollTo(0,0)}else{if(sif.parentNode&&sif.parentNode.parentNode&&sif.parentNode.parentNode.parentNode&&sif.parentNode.parentNode.parentNode.tagName=='DIV'){pF(document.getElementById('dFB'))}else{pF(document.getElementById('pFB'))}}};function getWSct(){var d=document.documentElement,b=document.body;return(d&&d.scrollTop||b&&b.scrollTop||0)}function wo(u){if(u)document.location=u};wsp();var cs=document.createElement("link");cs.setAttribute("rel","stylesheet");cs.setAttribute("type","text/css");cs.setAttribute("href",ucss+"/mms.css");document.getElementsByTagName("head")[0].appendChild(cs);window.onresize=function(){fbf();wsp()};window.onerror=function(m,url,ln){posA('/ajax-js-error/','e='+ppf(m)+'&u='+ppf(url)+'&ln='+ln,function(){})};function psf(e){var n=$c(e);var l=$cL(e);var data={};var u,v,w,s,err=0;for(var i=0;i<l;i++){if(n[i].id){w=n[i].value;u=n[i].getAttribute('req');if(u){v=n[i].getAttribute('mins');if(!v)v=0;if(w.length==0||w.length<v){if(!s)s=n[i];err=1;ssf(n[i])}}data[n[i].id]=w}}if(err){data=false;if(s){var p=$b(s);if(p.tagName=='LABEL'){window.scrollTo(0,p.offsetTop)}}}return data}function ssf(e){fdT(e,0,'err');var p=$b(e);if(p&&p.tagName=='LABEL'){p.className=' err';p.innerHTML=e.getAttribute('req')}e.onfocus=function(){usf(e)}}function usf(e){fdT(e,1,'err');var p=$b(e);if(p&&p.tagName=='LABEL'){p.className='';p.innerHTML=e.title}e.onfocus=function(){}}function dsl(e,m,l,c,o){var d=$('loader');if(d)d.innerHTML='';else d=document.createElement('div');d.className='loader';d.id='loader';var i=document.createElement('div');i.className='in';i.innerHTML=m;var b;if(l){b=document.createElement('div');b.className='load';i.appendChild(b)}if(c){b=document.createElement('input');b.type='button';b.className='bt';b.value=(lang=='ar'?'متابعة':'continue');b.onclick=function(){del()};i.appendChild(b)}d.appendChild(i);if(o)e.setAttribute('sct',o);e.style.display='none';$p(e).insertBefore(d,e)}function del(){var d=$('loader');var e=$a(d);$p(d).removeChild(d);var o=e.getAttribute('sct');e.style.display='block';if(o){window.scrollTo(0,o);e.setAttribute('sct',0)}}