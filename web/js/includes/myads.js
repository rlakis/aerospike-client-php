var ALT=false, MULTI=false, socket, $$=$.body;
const channel = new BroadcastChannel('admin');
channel.onmessage = function(e) {
    const message=e.data;
    console.log(message, d.currentId);
    if (message.hasOwnProperty('articleId') && d.isSafe(message.articleId)) {
        let cad=byId(d.currentId);
        d.reject(cad, d.ad.dataset.uid);
        let rta=cad.querySelector('textarea#rejT');
        rta.focus();
        rta.value=message.rejectURL;
    }
};
window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};

Element.prototype.article=function(){ let i=this; return i.closest('article'); };

$.addEventListener("DOMContentLoaded", function () {
    var lazyloadImages;
    var delImage=function(){
        var answer = window.confirm(d.ar ? "هل انت اكيد من حذف هذه الصورة من الاعلان؟" : "Do you really want to remove this picture?");
        if (answer) {
            let aa=this.article();
            fetch('/ajax-changepu/?img=1&i=' +aa.id + '&pix=' + this.parentElement.query('img').dataset.path, {method: 'GET', mode: 'same-origin', credentials: 'same-origin', headers: {'Accept': 'application/json', 'Content-Type': 'application/json'}})
                .then(response => { return response.json(); })
                .then(data => {                    
                    if(aa){
                        let holder = aa.query('p.pimgs'); 
                        if (holder && data.success===1) {
                            this.parentElement.remove();
                            if (holder.childNodes.length===0) {
                                holder.remove();
                            }
                        }
                    }
                }).catch(err => { console.log(err); });
        }        
    };
    
    var initImage=function(img){
        img.src = d.pixHost + '/repos/s/' + img.dataset.path;
        img.classList.remove("lazy");
        img.onclick = function () {
            d.slideView(this);
        };                    
        var del = createElem('div', 'del', '<i class="icn icnsmall icn-minus-circle"></i>', 1);
        del.onclick = delImage;
        img.closest('span').append(del);
    };
    
    if ("IntersectionObserver" in window) {
        lazyloadImages = document.querySelectorAll(".lazy");
        var imageObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var image = entry.target;
                    initImage(image);
                    imageObserver.unobserve(image);
                }
            });
        });

        lazyloadImages.forEach(function (image) {
            imageObserver.observe(image);
        });
    }
    else {
        var lazyloadThrottleTimeout;
        lazyloadImages = $.querySelectorAll(".lazy");

        function lazyload() {
            if (lazyloadThrottleTimeout) {
                clearTimeout(lazyloadThrottleTimeout);
            }

            lazyloadThrottleTimeout = setTimeout(function () {                
                var scrollTop = window.pageYOffset || $.documentElement.scrollTop;
                lazyloadImages.forEach(function (img) {
                    var top=img.getBoundingClientRect().top+scrollTop;
                    if (top < (window.innerHeight + scrollTop)) {
                        initImage(img);                        
                    }                  
                });
                if (lazyloadImages.length === 0) {
                    $.removeEventListener("scroll", lazyload);
                    window.removeEventListener("resize", lazyload);
                    window.removeEventListener("orientationChange", lazyload);
                }
                lazyloadImages = $.querySelectorAll(".lazy");
            }, 20);
        }
        $.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
        lazyload();
    }
    
    var script=$.createElement('script');script.type="text/javascript";
    script.src="https://dev.mourjan.com/js/2020/1.0/socket.io.js";           
    $.body.append(script);
    script.onload=function(){ reqSIO(); }
    
    location.search.substr(1).split("&").forEach(function(part) {
        var item=part.split("=");
        d.queryParams[item[0]]=decodeURIComponent(item[1]);
    });
    
    if (d.queryParams.u) {
        d.userStatistics(+d.queryParams.u);
    }
});

$.onkeydown = function (e) {
    ALT = (e.which === 18);
    MULTI = (e.which === 90);
    if (e.key === 'Escape' && d.slides) {
        d.slides.destroy();
        d.slides = null;
    }
};

$.onkeyup = function () {
    ALT = false;
    MULTI = false;
};

$$.onclick = function (e) {    
    let editable=$$.queryAll("section[contenteditable=true]");
    if (editable&&editable.length> 0) {
        editable[0].setAttribute("contenteditable", false);        
        d.normalize(editable[0]);
    }
    
    if (e.target.closest('div.swal2-container')===null) {
        d.setId(0);
        let f=byId('fixForm');
        if (f && window.getComputedStyle(f).visibility!=='hidden') {
            f.style.display='none'; 
        }
    }
};

var d = {
    currentId: 0, n: 0, panel: null, ad: null, slides: null, roots: null,
    KUID: $$.dataset.key,
    pixHost: $$.dataset.repo,
    su: $$.dataset.level==='90',
    level: $$.dataset.level==='90' ? 9 : parseInt($$.dataset.level),
    items:{},
    queryParams:{},
    nodes: $$.queryAll("article"),
    editors: byId('editors'),
    ar: $$.dir === 'rtl',
    count: (typeof $$.queryAll("article")==='object') ? $$.queryAll("article").length : 0,
    isAdmin: function () { return this.level>=9; },
    setId: function (kId) {
        console.log('setId', kId, typeof this.items[kId]);
        
        if(this.ad) this.ad.unselect();
        this.currentId=kId;
        this.ad=this.items[kId];
        console.log("setId", this.ad);
        if(this.ad)this.ad.select();
    },
    
    getName: function (kUID) {
        if(this.editors){
            var x = this.editors.getElementsByClassName(kUID);
            return (x&&x.length)?x[0].innerText:'Anonymous/'+kUID;
        }
    },
    
    inc: function () {
        let _=this;
        this.n++;
        if (_.panel===null) {
            _.panel=$$.query('.adminNB');
            if (_.panel===null) {
                _.panel = createElem('div', 'adminNB');
                $$.append(_.panel);
            }
        }
        if (_.panel) {
            _.panel.innerHTML = _.n + (_.ar ? ' اعلان جديد' : ' new ad');
            if(typeof _.panel.onclick!=='function'){_.panel.onclick=function(){location.reload();};}
        }
    },
    
    isSafe(adId){
        return parseInt(adId)===parseInt(this.currentId);
    },
    
    inViewport(e) {
        var cr = e.getBoundingClientRect();
        return(cr.top >= 0 && cr.left >= 0 && cr.top <= (window.innerHeight || $.documentElement.clientHeight));
    },
    
    visibleAds(f) {
        var v = [];
        var max = (window.innerHeight || $.documentElement.clientHeight);
        for (var x = 0; x < this.count; x++) {
            var cr = this.nodes[x].getBoundingClientRect();
            if (cr.top > max) {
                break;
            }
            if (cr.top >= 0) {
                if (f >= 0) {
                    if (this.nodes[x].dataset.fetched == f) {
                        v.push(this.nodes[x].id);
                    }
                } else {
                    v.push(this.nodes[x].id);
                }
            }
        }
        return v;
    },

    lookup: function (e) {
        var selection = null;
        if (window.getSelection) {
            selection = window.getSelection().toString();
        } 
        else if (document.selection) {
            selection = document.selection.createRange().text;
        }
        if (selection) {
            let revise =  e.article().query('button#revise');
            if (revise) {
                let q = revise.dataset.contact;
                if (selection.split(' ').length > 1) {
                    q += ' "' + selection + '"';
                } 
                else {
                    q += ' ' + selection;
                }
                let url = (this.ar ? '/' : '/en/') + '?cmp=' + e.article().id + '&q=' + q;
                d.openWindow(url, '_similar');
            }
        }
    },

    textSelected: function (e) {
        if (window.getSelection) {
            selection = window.getSelection().toString();
        } 
        else if (document.selection) {
            selection = document.selection.createRange().text;
        }
    },

    openWindow:function(url, name){
        let win;
        if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){            
            win = window.open(url, name, 'width=1024, height='+(window.innerHeight));
        }
        else {
            win = window.open(url, name);
        }
        win.focus();
    },
    
    // ad actions
    similar:function(e){
        let url=(d.ar?'/':'/en/')+'?aid='+e.article().id+'&q=';
        d.openWindow(url, '_similar');
    },
    
    userads:function(e, uid){
        window.location='/myads'+(d.ar?'/':'/en/')+'?sub=pending&fuid='+uid;
    },
    
    lookFor:function(e){
        let url=(d.ar?'/':'/en/')+'?cmp='+e.article().id+'&q='+e.dataset.contact;
        d.openWindow(url, '_similar');
    },
    
    edit: function(e) {
        //if (this.level===9) {            
        //}
        //console.log('edit button', this, e);        
        var form = createElem("form");
        form.target = '';
        form.method = "POST";
        form.action = '/post'+(d.ar?'/':'/en/');
        var input = createElem("input");
        input.type = "hidden";
        input.name = "ad";
        input.value = e.article().id;
        
        form.append(input);        
        $$.append(form);               
        form.submit();
    },
    
    unpublish: function(e) {
        if(confirm("Hold this ad?")){
            console.log(e.article().id);
            fetch('/ajax-report/',{method:'POST',mode:'same-origin',credentials:'same-origin',
                     body:JSON.stringify({id:parseInt(e.article().id)}),
                     headers:{'Accept':'application/json','Content-Type':'application/json'}})
            .then(res=>res.json())
            .then(response => {
                console.log('Success:', response);
                if(response.success===1){
                    e.parentElement.parentElement.style.backgroundColor='lightgray';
                }
                else {
                    window.alert(response.error);
                }
            })
            .catch(error => { 
                console.log('Error:', error); 
            });
        }
    },
    
    approve: function (e, rtpFlag) {        
        if(!this.isSafe(e.article().id))return;
        var data={i: parseInt(this.currentId)};
        if (typeof rtpFlag!=='undefined') { data['rtp']=rtpFlag; }
        this.ad.mask(true);
        fetch('/ajax-approve/', _options('POST', data))
            .then(res => res.json())
            .then(response => {
                console.log('Success:', JSON.stringify(response));
                if (response.success===1) {
                    this.ad.approved().removeMask();
                }
                else {
                    console.log(response.error);                        
                    d.items[e.article().id].mask().maskText(response.error);
                }
            })
            .catch(error => {
                console.log('Error:', error);
                this.ad.removeMask();
            });
    },

    getForm: function (prefix, moveTo) {
        var form = $.getElementById(prefix + 'Form');
        let select = form.query('select');
        if (prefix === 'rej') {
            select.innerHTML = '';
        }
        let text = $.getElementById(prefix + 'T');
        let ok = form.query('input.btn.ok');
        let cancel = form.query('input.btn.cancel');
        if (text) { text.value = ''; }
        if (cancel && typeof cancel!=='function') {
            cancel.onclick=function(){form.style.display='none';};
        }
        if (moveTo) { moveTo.append(form); }
        return {'form': form, 'select': select, 'text': text, 'ok': ok,
            'show': function () {form.style.display='block';},
            'hide': function () {form.style.display='none';}
        };
    },

    rtp: function (e) {
        if(!this.isSafe(e.article().id))return;
        Swal.fire({
            title: 'Ask Mobile Verification?',
            text: 'You will send an SMS to advertiser mobile number for verification!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, send it!',
            cancelButtonText: 'No, dismiss'
        }).then((result) => {
            if (result.value) {
                this.approve(e, 2);
                //Swal.fire('Deleted!', 'Your imaginary file has been deleted.', 'success' )
            //} else if (result.dismiss === Swal.DismissReason.cancel) {
                //Swal.fire('Cancelled', 'Your imaginary file is safe :)', 'error')
            }
        });
    },

    reject: function (e, uid) {
        let article = e.article();
        if(!this.isSafe(article.id))return;
        var inline = this.getForm('rej', article);
        var cn = article.query('section.ar') ? 'ar' : 'en';
        inline.select.className = cn;
        var os = rtMsgs[cn];
        var len = os.length;
        var g = null;
        for (var i = 0; i < len; i++) {
            if (os[i].substr(0, 6) === 'group=') {
                g = createElem('optgroup');
                g.setAttribute('label', os[i].substr(6));
                inline.select.append(g);
            } else {
                var o = createElem('option', '', os[i]);
                o.setAttribute('value', i);
                if (g !== null) {
                    g.append(o);
                } else {
                    inline.select.append(o);
                }
            }
        }
        if (g !== null) {
            inline.select.append(g);
        }

        inline.select.onchange = function (e) {
            if (cn === 'ar' || cn === 'en') {
                var v = parseInt(this.value);
                inline.text.value = '';
                if (v) {
                    inline.text.value = rtMsgs[cn][v];
                    inline.text.className = (rtMsgs[cn][v].match(/[\u0621-\u064a\u0750-\u077f]/)) ? 'ar' : 'en';
                }
            }
        };
        
        inline.ok.onclick = function () {
            if (!uid) uid=0;
            let ad=d.items[article.id].mask().maskText(inline.text.value);
            fetch('/ajax-reject/', _options('POST', {i: parseInt(article.id), msg: inline.text.value, w: uid}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if (response.success==1) {
                            //let ad=new Ad(e.parentElement.parentElement.id);
                            //ad.approved();
                        }
                        else {
                            d.items[article.id].mask().maskText(response.error);
                        }
                    })
                    .catch(error => {
                        console.log('Error:', error);
                        ad.removeMask();
                    });
            inline.hide();
        };
        inline.show();
    },

    suspend: function (e, uid) {
        let article = e.article();
        if(!this.isSafe(article.id))return;
        var inline = this.getForm('susp', article);
        if (inline.select.childNodes.length === 0) {
            let o = createElem('option', '', d.ar ? 'ساعة' : '1 hour');
            o.setAttribute('value', 1);
            inline.select.append(o);
            for (var i = 6; i <= 72; i = i + 6) {
                if (i > 48 && !d.su) {
                    break;
                }
                let o = createElem('option', '', i + (d.ar ? ' ساعة' : ' hour'));
                o.setAttribute('value', i);
                inline.select.append(o);
            }
        }

        inline.ok.onclick = function () {
            if(!uid)uid=0;
            let ad=d.items[article.id].mask().maskText(inline.text.value);           

            fetch('/ajax-ususpend/', _options('POST', {i: uid, v: inline.select.value, m: inline.text.value ? inline.text.value : ''}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        //if (response.success===1) {
                            //let ad=new Ad(e.parentElement.parentElement.id);
                            //ad.approved();
                        //}
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ad.removeMask();
                    });
            inline.hide();
        }
        inline.show();
    },

    ban: function (e, uid) {
        let article = e.article();
        if(!this.isSafe(article.id))return;
        var inline = this.getForm('ban', article);
        inline.ok.onclick = function () {
            if(!uid)uid=0;
            let ad=d.items[article.id].mask().maskText(inline.text.value);
            //let ad = new Ad(article.id, inline.text.value);
            fetch('/ajax-ublock/', _options('POST', {i: uid, msg: inline.text.value}))
                .then(res => res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    if (response.success===1) {
                        ad.maskText('User Account Blocked');
                    }
                    else {
                        ad.maskText(response.error);
                    }
                })
                .catch(error => {
                    console.log('Error:', error);
                    ad.removeMask();
                });
            inline.hide();
        };
        inline.show();
    },
    
    setUserType: function(e, uid) {
        let v=parseInt(e.value);
        if (uid>0 && (v===1 || v===2)) {
            fetch('/ajax-user-type/?u='+uid+'&t='+v, {method: 'GET', mode: 'same-origin', credentials: 'same-origin'})
                .then(res => res.json())
                .then(response => {                
                    console.log('Success:', response);
            })
            .catch(error => {
                console.log('Error:', error);
            });
        }
    },

    chart: function(e) {
        let article = e.article();
        let inline = this.getForm('chart', article);
        if (inline===null) { return; }
        if (inline.form.style.display!=='none') {
            inline.hide();
            return;
        }
        fetch('/ajax-ga/', _options('POST', {u: article.uid, a: article.id})).then(res => res.json())
            .then(response => {                
                if (response.success===1) {
                    response.result.d/=1000;
                    let monthNames=["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    let point=new Date(0), dates=[];
                    point.setUTCSeconds(response.result.d);
                    response.result.c.forEach(function(){                            
                        dates.push(point.getUTCDate()+" "+monthNames[point.getMonth()]);
                        point.setDate(point.getDate()+1);
                    });
                    
                    let config={
                        type: 'line',
                        data: {
                            labels:dates,
                            datasets:[{
                                    label: 'Impressions',
                                    backgroundColor: window.chartColors.red,
                                    borderColor: window.chartColors.red,
                                    data: response.result.c,                                       
                                    fill: false,                                
                                }, {
                                    label: 'Interactions',
                                    fill: false,
                                    backgroundColor: window.chartColors.blue,
                                    borderColor: window.chartColors.blue,
                                    data: response.result.k,
                                }
                            ],
                        },
                        options: {
                            responsive:true,
                            title: {
                                display:true,
                                position:'top',
                                text:response.result.t.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+' overall impressions'
                            },
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true
                            },
                            scales: {
                                xAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Day'
                                    }
                                }],
                                yAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Value'
                                    }
                                }]
                            }
                        }                            
                    };
                    
                    //console.log(response);
                    
                    if (inline) {
                        let canvas=inline.form.querySelector('canvas#chart');
                        let ctx=canvas.getContext('2d');
                        if (dates.length>0){
                            window.adchart=new Chart(ctx, config);
                            inline.show();
                        }
                    }
                }
            });
    },
    
    userStatistics: function(uid) {
        fetch('/ajax-ga/', _options('POST', {u: uid, x: 0}))
            .then(res => res.json())
            .then(response => {
                console.log(response);
                if (response.success===1) {
                    let monthNames=["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    let canvas=document.getElementById('canvas');
                    let ctx=canvas.getContext('2d');
                                                
                    response.result.d/=1000;
                    let point=new Date(0);
                    point.setUTCSeconds(response.result.d);                        
                        
                    let dates=[];
                    response.result.c.forEach(function(){                            
                        dates.push(point.getUTCDate()+" "+monthNames[point.getMonth()]);
                        point.setDate(point.getDate()+1);
                    });
                        
                    let config={
                        type: 'line',
                        data: {
                            labels:dates,
                            datasets:[{
                                    label: 'Impressions',
                                    backgroundColor: window.chartColors.red,
                                    borderColor: window.chartColors.red,
                                    data: response.result.c,                                       
                                    fill: false,                                
                                }, {
                                    label: 'Interactions',
                                    fill: false,
                                    backgroundColor: window.chartColors.blue,
                                    borderColor: window.chartColors.blue,
                                    data: response.result.k,
                                }
                            ],
                        },
                        options: {
                            responsive:true,
                            title: {
                                display:true,
                                position:'top',
                                text:response.result.t.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+' overall impressions'
                            },
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true
                            },
                            scales: {
                                xAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Day'
                                    }
                                }],
                                yAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Value'
                                    }
                                }]
                            }
                        }                            
                    };
                    
                    if (dates.length>0){
                        window.statictics=new Chart(ctx, config);
                        let rbt=document.getElementById('refreshChart');
                        if(rbt){
                            rbt.style.display='inline';
                            rbt.style.top=(canvas.offsetTop+8)+"px";
                            rbt.style.left=(canvas.offsetLeft+canvas.offsetWidth-68)+"px";
                        }
                    }
                    
                    if (response.result.a) {
                        for (k in response.result.a) {
                            let ad=d.items[k];
                            if (ad) {                                
                                ad.hits(response.result.a[k]);
                            }                                
                        }                            
                    }
                }                
            })
            .catch(error => {
                console.log('Error:', error);
            });
    },
    
    slideView: function (img) {
        let i=0, n=0;
        let p=img.parentElement;
        img.closest('p.pimgs').childNodes.forEach(function(s){i++;if(s===p)n=i;});
        //img.parentElement.parentElement.childNodes.forEach(function(spx){
        //    i++;
        //    if(spx===p){ n=i; }
        //});
        this.slides = new SlideShow(d.items[img.article().id]/* new Ad(img.parentElement.parentElement.parentElement.id)*/, n);
    },
    
    ipCheck: function (e) {
        if (e.dataset.fetched) { return; }
        let id = e.parentElement.parentElement.parentElement.parentElement.id;
        fetch('/ajax-changepu/?fraud=' + id, {method: 'GET', mode: 'same-origin', credentials: 'same-origin'})
            .then(res => res.json())
            .then(response => {                
                console.log('Success:', JSON.stringify(response, undefined, 2));
                let rs=response.result;
                let t = e.innerText === '...' ? '' : e.innerText + '<br>';
                t += 'Score: ' + rs.fraud_score;
                if (rs.mobile) t += ' | mobile';
                if (rs.recent_abuse) t += ' | abuse';
                if (rs.proxy) t += ' | proxy';
                if (rs.vpn) t += ' | VPN';
                if (rs.tor) t += ' | TOR';
                t += '<br>Country: ' + rs.country_code + ', ' + rs.city;
                t += '<br>Coordinate: ' + rs.latitude + ', ' + rs.longitude;
                t += '<br>IP: ' + rs.host + ', ' + rs.ISP;
                if (rs.region) t += '<br>Region: ' + rs.region;
                if (rs.timezone) t += '<br>Timezone: ' + rs.timezone;
                if (rs.ttl) t += '<br>TTL: ' + rs.ttl;
                e.innerHTML = t;
                e.dataset.fetched = 1;
            })
            .catch(error => {
                console.log('Error:', error);
            });
    },

    normalize: function(e){
        console.log(e.tagName,  e.innerText);
        let data = {dx: e.dataset.foreign ? 2 : 1, rtl: e.classList.contains('ar') ? 1 : 0, t: e.innerText};
        if(e.tagName==='DIV' && e.parentElement.tagName==='SECTION'){
            if (data.dx===2||(data.dx===1&&data.t.length>30)){
                this.updateAd(e, e.article().id, 0, 0, 0, data);
            }
        }      
        else console.log('data not suitable', data);
    },

    updateAd: function (e, adId, ro, se, pu, dat) {
        let ad=this.items[adId].mask(true).opacity(0.3);
        let data=(dat ? dat : {r: ro, s: se, p: pu, hl: (this.ar ? 'ar' : 'en')});

        fetch('/ajax-changepu/?i=' + adId, _options('POST', data))
            .then(res => res.json()).then(response => {
                console.log('updateAd', response);
                if (response.success===1 && response.result) {
                    let rs=response.result;
                    if (dat) {
                        if (response.result.index===1 && response.result.native) {
                            e.innerHTML = response.result.native;
                        }
                    }
                    if(rs.label){
                        ad.node.query('div.note').innerHTML=rs.label;
                        ad.node.dataset.ro = rs.ro;
                        ad.node.dataset.se = rs.se;
                        ad.node.dataset.pu = rs.pu;
                    }
                }
                else { window.alert(response.error); }
                ad.removeMask();
            })
            .catch(error => {
                console.log(error);
                ad.maskText(error);
            });
    },
    
    clickedAd:function(e){
        let ee=e.target, pp=ee.parentElement;
        let prevent=function(){e.preventDefault();e.stopPropagation();return false;};
        
        if (this.getStatus()>=7) { 
            d.setId(this.id);
            if (ee.closest('div.user')===null && ee.closest('footer')===null) {
                return prevent();                 
            }
        }
        
        switch(ee.tagName) {
            case 'A':
                if ((ee.className===''&&pp.className==='mask')||ee.target==='_similar') {
                    e.stopPropagation();return;
                }
                break;
            
            case 'DIV':                
                if (ee.className==='mask'||ee.classList.contains('locked')){
                    return prevent();
                }
                if (ALT && pp.tagName==='SECTION' && !ee.isContentEditable) {
                    var re = /\u200b/;
                    var parts = ee.innerText.split(re);
                    if (parts.length===2) {
                        ee.dataset.contacts = parts[1];
                        ee.contentEditable = "true";
                        ee.innerHTML = parts[0].trim();
                        ee.focus();
                    }
                    return prevent();
                }
                break;
                
            case 'SECTION':
                if (ALT) {
                    let ed=ee.query('div');
                    console.log('section', ed);
                    if (ed && !ed.isContentEditable) {
                        var re = /\u200b/;
                        var parts = ed.innerText.split(re);
                        if (parts.length===2) {
                            ed.dataset.contacts = parts[1];
                            ed.contentEditable = "true";
                            ed.innerHTML = parts[0].trim();
                            ed.focus();
                        }
                        return prevent();
                    }
                    
                }
                /*
                if (ALT && !ee.isContentEditable) {
                    var re = /\u200b/;
                    var parts = ee.innerText.split(re);
                    if (parts.length===2) {
                        ee.dataset.contacts = parts[1];
                        ee.contentEditable = "true";
                        ee.innerHTML = parts[0].trim();
                        ee.focus();
                    }
                    return prevent();
                }*/
                break;
                
            case 'G':
                if (ee.dataset.grId){return prevent();}
                break;
            default:
                console.log(ee.tagName+' is not supported!');
                break;
        }

        
        if(!d.isSafe(this.id)){
            d.setId(this.id);
        }
        
        let editable=$$.queryAll("section div[contenteditable=true]");
        if (editable && editable.length>0 &&editable[0]!==ee && editable[0].article()) {
            console.log('catched editables');
            editable[0].setAttribute("contenteditable",false);
            d.normalize(editable[0]);
        }
     
        return prevent();
    },

    quick(e) {
        let a=e.article();
        if(!this.isSafe(a.id))return;
                
        console.log(d);
        
        var inline = d.getForm('fix', a);
        let rDIV = inline.form.query('#qRoot');
        let rUL = rDIV.query('ul');
        let sDIV = inline.form.query('#qSec');
        let aDIV = inline.form.query('#qAlt');
        let aUL = aDIV.query('ul');
        
        var fillSections = function (rId) {
            if (!rDIV.dataset.rootId || rDIV.dataset.rootId !== rId) {
                let rr = rDIV.queryAll('li');                
                rr.forEach(function (item) {                    
                    if (item.dataset.id===rId) {
                        item.classList.add('cur');
                    } else if (item.classList.contains('cur')) {
                        item.classList.remove('cur');
                    }
                });
                
                let ul = sDIV.query('ul');ul.innerHTML = '';                
                d.roots[rId].sindex.forEach(function (sid) {                    
                    let li = createElem('li', (sid.toString()===a.dataset.se ? 'cur' : ''), d.roots[rId]['sections'][sid]);
                    li.dataset.id = sid;
                    li.dataset.ro = rId;
                    li.onclick = function (e) {
                        let t=e.target;
                        t.classList.add('cur');
                        let p=t.article();
                        let pu=p.dataset.pu;
                        if (!d.roots[t.dataset.ro].purposes[pu]) {
                            pu = Object.keys(d.roots[t.dataset.ro].purposes)[0];
                        }
                        d.updateAd(t, p.id, rId, e.target.dataset.id, pu);
                    };
                    ul.append(li);
                });

                aUL.innerHTML = '';
                for (let i in d.roots[rId]['purposes']) {
                    let li = createElem('li', i === a.dataset.pu ? 'cur' : '', d.roots[rId]['purposes'][i]);
                    li.dataset.id = i;
                    li.onclick = function (e) {
                        let p=e.target.article();
                        
                        d.updateAd(e.target, p.id, rId, p.dataset.se, e.target.dataset.id);
                    };
                    aUL.append(li);
                }
                aUL.append(createElem('li', '', '&nbsp;', true));

                if (typeof d.secSwitches[a.dataset.se] === 'object') {
                    for (i in d.secSwitches[a.dataset.se]) {
                        let ss = d.secSwitches[a.dataset.se][i];
                        let li = createElem('li', '', ss[3]);
                        li.dataset.ro = ss[0];
                        li.dataset.se = ss[1];
                        li.dataset.pu = ss[2];
                        li.onclick = function (e) {
                            d.updateAd(a, a.id, rId, e.target.dataset.se, e.target.dataset.pu);
                        };
                        aUL.append(li);
                    }
                }

                rDIV.dataset.rootId = rId;
            }
        };
        
        window.scrollTo(0, a.offsetTop);
        const request = async () => {
            if (!d.sections) {
                const response = await fetch('/ajax-menu/?sections=' + (d.ar ? 'ar' : 'en'), _options('GET'));
                const json = await response.json();
                d.roots = json.result.roots;
                d.secSwitches = json.result.sswitch;
                d.rootSwitches = json.result.rswitch;
            }

            if (rUL.childNodes.length === 0) {
                for (var i in d.roots) {
                    let li = createElem('li', '', d.roots[i]['name']);
                    li.dataset.id = i;
                    li.onclick = function (e) { fillSections(e.target.dataset.id); };
                    rUL.append(li);
                }
            }

            fillSections(a.dataset.ro);

            inline.show();
        };
        request();

    }
};

class SlideShow {
    constructor(kAd, _n) {
        this.ad = kAd;
        this.index = parseInt(_n);
        let self = this;
        this.container = createElem('DIV', 'slideshow');
        let h = createElem('HEADER');
        this.container.append(h);
        let dots = $.createElement('FOOTER');

        let close = createElem('SPAN', 'close', '×');
        close.onclick = function () { self.destroy(); };

        h.append(close);
        for (var i = 0; i < this.ad.mediaCount; i++) {
            let t=this.ad.pixSpans[i].query('img');
            let slide = createElem('DIV', 'mySlides fade', '<img src="' + d.pixHost + '/repos/d/' + t.dataset.path + '">', 1);
            this.container.append(slide);

            let dot = createElem('SPAN', 'dot');
            dot.onclick = function () { self.current(i + 1); };
            dots.append(dot);
        }
        let prev = createElem('A', 'prev', '&#10094;', 1);
        prev.onclick = function () { self.plus(-1); };
        let next = createElem('A', 'next', '&#10095;', 1);
        next.onclick = function () { self.plus(1); };
        this.container.append(prev, next, dots);
        $.body.append(this.container);
        this.show(this.index);
    }
    current(n) { this.show(this.index = n); }
    plus(n) { this.show(this.index += n); }
    show(n) {
        var i;
        var slides = this.container.queryAll(".mySlides");
        var dots = this.container.queryAll(".dot");
        if (n > this.ad.mediaCount) {
            this.index = 1
        }
        if (n < 1) {
            this.index = this.ad.mediaCount
        }
        slides.forEach(function(s){s.style.display='none';});
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[this.index - 1].style.display = "flex";
        dots[this.index - 1].className += " active";
    }
    destroy() { this.container.remove(); }
}


class Ad {
    constructor(kId, kMaskMessage) {
        this._m = null;
        this.id = parseInt(kId);
        this.node = $.getElementById(kId);
        if (this.node !== null) {
            this._header = this.node.queryAll('header')[0];
            this._editor = this._header.query('.alloc');
            this._message = this._header.query('.msg');
            this.dataset = this.node.dataset;
            this.mediaCount = 0;
            var wrp = this.node.query('p.pimgs');
            if (wrp) {
                this.pixSpans = wrp.queryAll('span');
                if (this.pixSpans && this.pixSpans.length) {
                    this.mediaCount = this.pixSpans.length;
                }
            }
            if(kMaskMessage)this.mask().maskText(kMaskMessage);
            //this.node.oncontextmenu=function(e){e.preventDefault();};
        }
    }
    
    dataset() {
        return this.node.dataset;
    }
    
    header() {
        return this._header;
    }
    
    getName() {
        return this._editor.innerText;
    }
    
    setName(v) {
        if (!this._editor.innerText.includes(v)) {
            this._editor.innerHTML = v + '/' + this._editor.innerText;
        }
        return this;
    }
    
    replName(v) {
        if (this.getStatus()<7 && this._editor) {
            this._editor.innerHTML = v;
        }
    }
    
    getMessage() {
        return this._message.innerText;
    }
    
    setMessage(v) {
        this._message.innerHTML = v;
    }
        
    getStatus() {
        return parseInt(this.node.dataset.status);
    }
    
    
    select() {
        if (!this.node.classList.contains('selected')) {
            this.setAs('selected');
            if (this.getStatus()<7) {
                socket.emit("touch", [this.id, d.KUID]);
            }
            let f=byId('fixForm');
            if(f&&window.getComputedStyle(f).visibility!=='hidden'){f.style.display='none';}
        }
    }
    
    unselect() {
        this.unsetAs('selected');
        if (this.getStatus()<7) {
            socket.emit("release", [this.id, d.KUID]);
        }
    }
    
    lock() {
        if (this.getStatus()<7) {
            this.setAs('locked');
            this.mask().opacity(0.25);
        }
    }
    
    release() {
        if (this.getStatus()<7) {
            this.unsetAs('locked');
            this.removeMask();
        }
    }
    
    setAs(c) {
        this.node.classList.add(c);
    }
    
    unsetAs(c) {
        this.node.classList.remove(c);
    }
    
    rejected(t) {
        if (this.getStatus()<7) {
            this.unsetAs('approved');
            this.setAs('rejected');
            this.setMessage(t);
            this.node.dataset.status = 3;
        }
    }
    
    approved() {
        if (this.getStatus()<7) {
            this.unsetAs('rejected');
            this.setAs('approved');
            this.node.dataset.status = 2;
        }
        return this;
    }
    
    mask(loader) {
        var _=this;
        _._m = _.node.query('div.mask');
        if (_._m === null) {
            _._m = createElem("div", 'mask');
            _.node.append(_._m);
        }
        if (loader)
            this.showLoader();
        return this;
    }
    
    removeMask() {
        this._m = this.node.query('div.mask');
        if (this._m) {
            this._m.remove();
            this._m = null;
        }
        if(d.isSafe(this.id)){
            let f=byId('fixForm');
            if (f&& window.getComputedStyle(f).visibility!=='hidden')f.style.display='none';
        }
        return this;
    }
    
    maskText(t) {
        this._m = this.node.query('div.mask');
        if (this._m) { this._m.innerHTML = t; }
        return this;
    }
    
    showLoader() {
        this._m = this.node.query('div.mask');
        if (this._m) {
            this._m.innerHTML = '<div class=loader></div>';
        }
        return this;
    }
    
    hideLoader() {
        this._m = this.node.query('div.mask');
        if (this._m) {
            this._m.innerHTML = '';
        }
        return this;
    }
    
    hits(v) {
        let stat=this.node.query('button.stad');
        if (stat) {
            this.node.query('button.stad').innerHTML=v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'&nbsp;<i class="icn i16 icn-chart-line"></i>';
        }
    }
    
    opacity(v) {
        this._m.style.opacity = v;
        return this;
    }
}


const reqSIO = async () => {
    socket = io.connect('ws.mourjan.com:1313', {transports: ['websocket'], 'force new connection': false});
    socket.on('admins', function (data) {
        active_admins = data.a;
        if (isNaN(active_admins)) {
            let len = active_admins.length;
            let matched = [];
            for (var i = 0; i < len; i++) {
                if (d.editors) {
                    var x = d.editors.getElementsByClassName(active_admins[i]);
                    if (x && x.length > 0) {
                        matched.push(x[0].className);
                        if (d.su) {
                            x[0].firstChild.style.setProperty('color', 'green', 'important');
                        } else
                            x[0].style.setProperty('color', 'green', 'important');
                    }
                }
            }
            len = d.editors ? d.editors.childNodes.length : 0;
            for (var i = 0; i < len; i++) {
                if (matched.indexOf(d.editors.childNodes[i].className) === -1) {
                    d.editors.childNodes[i].style.removeProperty('color');
                }
            }
            matched = null;
        } else {
            console.log('on<admins>: Active Admins:' + active_admins);
        }

        if (d.editors && typeof data.b==='object') {
            for (let uid in data.b) {
                if (data.b[uid]===0) { continue; }
                let ad=d.items[data.b[uid]];
                if (ad) {
                    ad.replName(d.getName(uid));
                    if(uid===d.KUID)ad.select();else ad.lock();
                }
            }
        }
    });
    socket.on("ad_touch", function (data) {
        if (data.hasOwnProperty('x')) {
            if (data.hasOwnProperty('i') && data.i > 0) {
                let ad=d.items[data.i];
                if(ad)ad.setName(d.getName(data.x)).lock();
            }
            if (data.hasOwnProperty('o') && data.o > 0) {
                let ad=d.items[data.o];
                if(ad)ad.release();
            }
        }
    });
    socket.on("ad_release", function (data) {//console.log('releasing', data);
        if (data.hasOwnProperty('i') && data.i > 0) {
            let ad=d.items[data.i];
            if (ad)ad.release();
        }
    });
    socket.on('superAdmin', function (data) { console.log(typeof data, data);
        if (typeof data !== 'undefined' && data.id && data.id > 0) {
            let ad = d.items[data.i];
            if(ad)ad.mask().maskText('Sent To Super Admin');
        }
    });
    socket.on('editorialUpdate', function (data) { console.log(data);
        if (typeof data==='object'&&data.id) {
            let ad=d.items[data.id];
            if(ad){
                ad.node.dataset.ro = data.ro;
                ad.node.dataset.se = data.se;
                ad.node.dataset.pu = data.pu;
                ad.node.query('div.note').innerHTML = data.label;
            }
        }
    });
    socket.on('editorialImg', function (data) {
        if (typeof data === 'object') {
            let ad = d.items[data.id];
            if (ad) {
                let p=ad.node.query('p.pimgs');
                if(p){
                    p.childNodes.forEach(function(spx){
                        if(spx.query('img').dataset.path===data.removed){
                            spx.remove();
                        }                
                    });          
                    if (p.childNodes.length === 0) {
                        p.remove();
                    }
                }
            }
        }
    });
    socket.on('editorialText', function (data) {
        if (typeof data==='object') {
            let ad = d.items[data.id];
            if (!ad) { return; }
            let arText = ad.node.query('section.ar');
            let enText = ad.node.query('section.en');
            if (data.rtl===1) {
                if (arText.classList.contains('en')) {
                    arText.classList.remove('en');
                    arText.dataset.foreign = 0;
                }
                if (!arText.classList.contains('ar')) {
                    arText.classList.add('ar');
                }
                arText.innerHTML = data.t;
                if (data.t2 && data.t2.length > 0) {
                    if (!enText) {
                        enText = createElem('section', 'card-content en', data.t2, true);
                        enText.dataset.foreign = 1;
                        arText.parentElement.append(enText);
                    } else {
                        enText.innerHTML = data.t2;
                    }
                }
            } else {
                if (arText) {
                    if (arText.classList.contains('ar')) arText.classList.remove('ar');
                    if (!arText.classList.contains('en')) arText.classList.add('en');
                    arText.innerHTML = data.t;
                }
                else if(enText) {
                    if (enText.classList.contains('ar')) enText.classList.remove('ar');
                    if (!enText.classList.contains('en')) enText.classList.add('en');

                    enText.innerHTML = data.t;
                }
                else {
                    console.log('arText', arText, 'enText', enText);
                }

            }
        }
    });
    socket.on("ads", function (data) {
        if(typeof data.c==='undefined')return;
        data.c = parseInt(data.c);
        let ad = d.items[data.id];
        if (ad) {
            var t;
            if (parseInt(ad.dataset.status)>=0||c.data===-1) {
                switch (data.c) {
                    case - 1:
                    case 6:
                        t = d.ar ? 'تم الحذف' : 'Deleted';
                        ad.setMessage(t);
                        break;
                    case 0:
                        t = d.ar ? 'جاري التعديل، يجب تحديث الصفحة' : 'editting in progress, refresh page';
                        ad.maskText(t);
                        break;
                    case 1:
                        t = d.ar ? 'بإنتظار موافقة النشر من قبل محرري الموقع' : 'Waiting for Editorial approval';
                        ad.setMessage(t);
                        break;
                    case 2:
                        t = d.ar ? 'تمت الموافقة وبإنتظار العرض من قبل محرك مرجان' : 'Approved and pending Mourjan system processing';                    
                        ad.setMessage(t);
                        ad.setAs('approved');
                        break;
                    case 3:
                        t = d.ar ? 'تم رفض عرض هذا الإعلان' : 'Rejected By Admin';
                        if (typeof data.m !== 'undefined') {
                            t += ': ' + data.m;
                        }
                        ad.rejected(t);
                        break;
                    case 7:
                        ad.dataset.status = 7;
                        ad.mask().opacity(0.75);
                        var link;
                        if (d.isAdmin()) {
                            var lnks = ad.node.queryAll('div.user > a');
                            if (lnks && lnks.length > 1) {
                                link = lnks[1].href + '#' + ad.id;
                            }
                        } else {
                            link = '/myads/' + (d.ar ? '' : 'en/') + '#' + ad.id;
                        }
                        t = d.ar ? 'الإعلان أصبح فعالاً، <a href="' + link + '">انقر(ي) هنا</a> لتفقد الإعلانات الفعالة' : 'Ad is online now, <a href="' + link + '">click here</a> to view Active Ads';
                        ad.maskText(t);
                        break;
                }
            }
        }
        if (data.c===1) { d.inc(); }
    });

    socket.on('superAdmin', function (data) {console.log(data);/*if (typeof data !== 'undefined') {}*/});
    socket.on('reconnect', function () { console.log('Reconnnect to ws'); });
    socket.on('connect', function () {console.log('connnect to ws');if (d.KUID) { this.emit("hook_myads", [d.KUID, d.level]); }});
    socket.on('disconnect', function () { console.log('disconnect from ws'); });
    socket.on('event', function (data) { console.log('event'); });
};

d.items.length=0;
d.nodes.forEach(function(node){
    d.items[node.id]=new Ad(node.id);
    node.onclick=d.clickedAd.bind(d.items[node.id]); 
});

console.log("finishied");