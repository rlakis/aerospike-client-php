var eid=0;function ado(e,i){eid=i;var li=e;var e=$c($c(li,1),2);if(!leb){leb=$('aopt')}else if($p(leb)){var l=$p(leb);if(l!=li){fdT(l,1,'edit');peb.className="adn";acls(leb)}l.removeChild(leb);leb.style.display="none"}peb=e;if(li.className.match(/edit/)){fdT(li,1,'edit');e.className="adn"}else{fdT(li,0,'edit');e.className="adn aup";li.appendChild(leb);leb.style.display="block";aht(li)}};function aht(e){var b=e.offsetTop+e.offsetHeight;var d=document.body;var wh=window.innerHeight;var t=wh+d.scrollTop;if(b>t){window.scrollTo(0,b-wh)}};function acls(d){var t=$c($f(d));var c=t.length;for(var i=0;i<c;i++){t[i].className=''}}function mask(e,d,m){if(!d)d=document.createElement('div');d.style.display='block';if(m){d.innerHTML=m;d.className='mask';d.style.paddingTop=(e.offsetHeight/2-10)+'px'}else{d.className='mask load'}e.appendChild(d);return d}function adel(e,h){se();if(confirm(lang=='ar'?'حذف هذا الإعلان؟':'Delete this ad?')){if(!h)h=0;var l=$p(e,2);var s=$p(l);fdT(l,1,'adn');s.removeChild(l);var d=mask(s);posA('/ajax-adel/','i='+eid+'&h='+h,function(rp){if(rp.RP){var m=(lang=='ar'?'تم الحذف':'Deleted');mask(s,d,m)}else{s.removeChild(d)}})}}function are(e){se();if(confirm(lang=='ar'?'تجديد هذا الإعلان؟':'Renew this ad?')){var l=$p(e,2);var s=$p(l);fdT(l,1,'adn');s.removeChild(l);var d=mask(s);posA('/ajax-arenew/','i='+eid,function(rp){if(rp.RP){var m=(lang=='ar'?'تم تحويل الإعلان للائحة إنتظار النشر':'Ad is pending to be re-published');mask(s,d,m)}else{s.removeChild(d)}})}}function ahld(e){se();if(confirm(lang=='ar'?'إيقاف عرض هذا الإعلان؟':'Stop this ad?')){var l=$p(e,2);var s=$p(l);fdT(l,1,'adn');s.removeChild(l);var d=mask(s);posA('/ajax-ahold/','i='+eid,function(rp){if(rp.RP){var m=(lang=='ar'?'تم تحويل الإعلان إلى الأرشيف':'Ad is moved to archive');mask(s,d,m)}else{s.removeChild(d)}})}}function sLD(e){var c=e.childNodes;var l=c.length;if(l==3){e.removeChild(c[2]);l=2}for(var i=0;i<l;i++){c[i].style.visibility='hidden'}var d=document.createElement('span');d.className='load';e.appendChild(d);e.className+=' on'}function eLD(e){e.className=e.className.replace(/ on/g,"");var c=e.childNodes;var l=c.length;if(l==3){e.removeChild(c[2]);l=2}for(var i=0;i<l;i++){c[i].style.visibility='visible'}};var ts=document.getElementsByTagName('time');var ln=ts.length;var time=parseInt((new Date().getTime())/1000);var d=0,dy=0,rt='';for(var i=0;i<ln;i++){d=time-parseInt(ts[i].getAttribute('st'));dy=Math.floor(d/86400);rt='';if(lang=='ar'){rt=since+' ';if(dy){rt+=(dy==1?"يوم":(dy==2?"يومين":dy+' '+(dy<11?"أيام" : "يوم")))}else{dy=Math.floor(d/3600);if(dy){rt+=(dy==1?"ساعة":(dy==2?"ساعتين":dy+' '+(dy<11?"ساعات" : "ساعة")))}else{dy=Math.floor(d/60);if(dy==0)dy=1;rt+=(dy==1?"دقيقة":(dy==2?"دقيقتين":dy+' '+(dy<11?"دقائق" : "دقيقة")))}}}else{if(dy){rt=(dy==1?'1 day':dy+' days')}else{dy=Math.floor(d/3600);if(dy){rt=(dy==1?'1 hour':dy+' hours')}else{dy=Math.floor(d/60);if(dy==0)dy=1;rt=(dy==1?'1 minute':dy+' minutes')}}rt+=' '+ago}ts[i].innerHTML=' '+rt}pi();