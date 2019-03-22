var ALT = false, MULTI = false, socket;
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
                        if (holder && data.RP === 1) {
                            this.parentElement.remove();
                            if (holder.childNodes.length === 0) {
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
    script.src="/web/js/1.0/socket.io.js";           
    $$.append(script);
    script.onload=function(){ reqSIO(); }
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
    let editable = $.querySelectorAll("[contenteditable=true]");
    if (editable && editable.length > 0) {
        editable[0].setAttribute("contenteditable", false);
        d.normalize(editable[0]);
    }
    d.setId(0);
    let f = $.getElementById('fixForm');
    if (f && window.getComputedStyle(f).visibility !== "hidden") {
        f.style.display = 'none';
    }
};

var d = {
    currentId: 0, n: 0, panel: null, ad: null, slides: null, roots: null,
    KUID: $.body.dataset.key,
    pixHost: $.body.dataset.repo,
    su: parseInt($.body.dataset.level) === 90,
    level: this.parseInt($.body.dataset.level) === 90 ? 9 : parseInt($.body.dataset.level),
    nodes: $.querySelectorAll("article"),
    editors: $.getElementById('editors'),
    ar: $.body.dir === 'rtl',
    count: (typeof $.querySelectorAll("article") === 'object') ? $.querySelectorAll("article").length : 0,
    isAdmin: function () {
        return this.level >= 9;
    },
    setId: function (kId) {
        if (this.ad) {
            this.ad.unselect();
        }
        ;
        this.currentId = kId;
        this.ad = new Ad(this.currentId);
        this.ad.select();
    },
    getName: function (kUID) {
        var x = this.editors.getElementsByClassName(kUID);
        return (x && x.length) ? x[0].innerText : 'Anonymous/' + kUID;
    },
    inc: function () {
        this.n++;
        if (this.panel === null) {
            this.panel = $.querySelector('.adminNB');
            if (this.panel === null) {
                this.panel = createElem("div", 'adminNB');
                $.body.append(this.panel);
            }
        }
        if (this.panel) {
            this.panel.innerHTML = this.n + (this.ar ? ' اعلان جديد' : ' new ad');
            this.panel.onclick = function (e) {
                document.location = '';
            };
        }
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
        } else if (document.selection) {
            selection = document.selection.createRange().text;
        }
        if (selection) {
            let revise =  e.article().query('button#revise');
            if (revise) {
                let q = revise.dataset.contact;
                if (selection.split(' ').length > 1) {
                    q += ' "' + selection + '"';
                } else {
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
        } else if (document.selection) {
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
    
    lookFor:function(e){
        let url=(d.ar?'/':'/en/')+'?cmp='+e.article().id+'&q='+e.dataset.contact;
        d.openWindow(url, '_similar');
    },
    
    edit: function(e) {
        if(this.level===9){
            
        }
        console.log('edit button', this, e);        
        var form = createElem("form");
        form.target = '';
        form.method = "POST"; // or "post" if appropriate
        form.action = '/post'+(d.ar?'/':'/en/');
        var input = createElem("input");
        input.type = "hidden";
        input.name = "ad";
        input.value = e.article().id;
        
        form.append(input);        
        $.body.append(form);               
        form.submit();
    },
    
    
    approve: function (e, rtpFlag) {
        if (this.currentId != e.article().id) {
            return;
        }
        var data = {i: parseInt(this.currentId)};
        if (typeof rtpFlag !== 'undefined') {
            data['rtp'] = rtpFlag
        }
        let ad = new Ad(this.currentId).mask(true);
        fetch('/ajax-approve/', _options('POST', data))
                .then(res => res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response));
                    if (response.RP === 1) {
                        ad.approved().removeMask();
                    }
                })
                .catch(error => {
                    console.log('Error:', error);
                    ad.removeMask();
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
        if (text) {
            text.value = ''
        }
        if (cancel && typeof cancel !== 'function') {
            cancel.onclick = function () {
                form.style.display = 'none'
            }
        }
        if (moveTo) {
            moveTo.append(form)
        }
        return {'form': form, 'select': select, 'text': text, 'ok': ok,
            'show': function () {
                form.style.display = 'block'
            },
            'hide': function () {
                form.style.display = 'none'
            }
        };
    },

    rtp: function (e) {
        var answer = window.confirm("Do you really want to ask Real Time Password verification?");
        if (answer) {
            this.approve(e, 2);
        }
    },

    reject: function (e, uid) {
        let article = e.article();
        if (this.currentId != article.id) {
            return;
        }
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
            if (!uid)
                uid = 0;
            let ad = new Ad(article.id, inline.text.value);

            fetch('/ajax-reject/', _options('POST', {i: parseInt(article.id), msg: inline.text.value, w: uid}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if (response.RP == 1) {
                            //let ad=new Ad(e.parentElement.parentElement.id);
                            //ad.approved();
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
        if (this.currentId !== article.id) {
            return;
        }
        var inline = this.getForm('susp', article);
        if (inline.select.childNodes.length === 0) {
            let o = createElem('option', '', d.ar ? 'ساعة' : '1 hour');
            o.setAttribute('value', 1);
            inline.select.append(o);
            for (var i = 6; i <= 72; i = i + 6) {
                if (i > 48 && d.su) {
                    break;
                }
                let o = createElem('option', '', i + (d.ar ? ' ساعة' : ' hour'));
                o.setAttribute('value', i);
                inline.select.append(o);
            }
        }

        inline.ok.onclick = function () {
            if (!uid)
                uid = 0;
            let ad = new Ad(article.id, inline.text.value);

            fetch('/ajax-ususpend/', _options('POST', {i: uid, v: inline.select.value, m: inline.text.value ? inline.text.value : ''}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if (response.RP === 1) {
                            //let ad=new Ad(e.parentElement.parentElement.id);
                            //ad.approved();
                        }
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
        if (this.currentId != article.id) {
            return;
        }
        var inline = this.getForm('ban', article);
        inline.ok.onclick = function () {
            if (!uid)
                uid = 0;
            let ad = new Ad(article.id, inline.text.value);
            fetch('/ajax-ublock/', _options('POST', {i: uid, msg: inline.text.value}))
                    .then(res => res.json())
                    .then(response => {
                        console.log('Success:', JSON.stringify(response));
                        if (response.RP === 1) {
                            ad.maskText('User Account Blocked');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ad.removeMask();
                    });
            inline.hide();
        }
        inline.show();
    },

    slideView: function (img) {
        let i=0, n=0;
        let p=img.parentElement;
        img.parentElement.parentElement.childNodes.forEach(function(spx){
            i++;
            if(spx===p){ n=i; }
        });
        this.slides = new SlideShow(new Ad(img.parentElement.parentElement.parentElement.id), n);
    },
    
    ipCheck: function (e) {
        if (e.dataset.fetched) {
            return;
        }
        let id = e.parentElement.parentElement.parentElement.parentElement.id;
        fetch('/ajax-changepu/?fraud=' + id, {method: 'GET', mode: 'same-origin', credentials: 'same-origin'})
                .then(res => res.json())
                .then(response => {
                    console.log('Success:', JSON.stringify(response, undefined, 2));
                    let t = e.innerText === '...' ? '' : e.innerText + '<br>';
                    t += 'Score: ' + response['fraud_score'];
                    if (response['mobile'])
                        t += ' | mobile';
                    if (response['recent_abuse'])
                        t += ' | abuse';
                    if (response['proxy'])
                        t += ' | proxy';
                    if (response['vpn'])
                        t += ' | VPN';
                    if (response['tor'])
                        t += ' | TOR';
                    t += '<br>Country: ' + response['country_code'] + ', ' + response['city'];
                    t += '<br>Coordinate: ' + response['latitude'] + ', ' + response['longitude'];
                    t += '<br>IP: ' + response['host'] + ', ' + response['ISP'];
                    if (response['region'])
                        t += '<br>Region: ' + response['region'];
                    if (response['timezone'])
                        t += '<br>Timezone: ' + response['timezone'];
                    if (response['ttl'])
                        t += '<br>TTL: ' + response['ttl'];
                    e.innerHTML = t;
                    e.dataset.fetched = 1;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
    },

    normalize: function (e) {
        let data = {dx: e.dataset.foreign ? 2 : 1, rtl: e.classList.contains('ar') ? 1 : 0, t: e.innerText};
        this.updateAd(e, d.currentId, 0, 0, 0, data);
    },

    updateAd: function (e, adId, ro, se, pu, dat) {
        let ad = new Ad(adId);
        ad.mask(true).opacity(0.3);
        let data = dat ? dat : {r: ro, s: se, p: pu, hl: (this.ar ? 'ar' : 'en')};

        fetch('/ajax-changepu/?i=' + adId, _options('POST', data))
                .then(res => res.json())
                .then(response => {
                    console.log('updateAd', response);
                    if (response.RP === 1) {
                        if (dat) {
                            if (response.DATA.dx === 1 && response.DATA.t) {
                                e.innerHTML = response.DATA.t;
                            }
                        }
                    }
                    ad.removeMask();
                })
                .catch(error => {
                    console.log(error);
                    ad.maskText(error);
                });
    },

    quick(e) {
        let article = e.article();
        if (d.currentId !== article.id) { return; }
        
        console.log(d.currentId);
        console.log(d);
        
        var inline = d.getForm('fix', article);
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
                //console.log('rid', rId);
                
                d.roots[rId].sindex.forEach(function (sid) {
                    console.log(typeof sid, typeof article.dataset.se);
                    let li = createElem('li', (sid.toString()===article.dataset.se ? 'cur' : ''), d.roots[rId]['sections'][sid]);
                    li.dataset.id = sid;
                    li.onclick = function (e) {
                        let p=e.target.article();
                        let pu = p.dataset.pu;
                        if (!d.roots[rId].purposes[pu]) {
                            pu = d.roots[rId].purposes[Object.keys(d.roots[rId]['purposes'])[0]];
                        }
                        console.log('ad id', p.id);
                        d.updateAd(e.target, p.id, rId, e.target.dataset.id, pu);
                    };
                    ul.append(li);
                });

                aUL.innerHTML = '';
                for (let i in d.roots[rId]['purposes']) {
                    let li = createElem('li', i === article.dataset.pu ? 'cur' : '', d.roots[rId]['purposes'][i]);
                    li.dataset.id = i;
                    li.onclick = function (e) {
                        let p=e.target.article();
                        d.updateAd(e.target, p.id, rId, p.dataset.se, e.target.dataset.id);
                    };
                    aUL.append(li);
                }
                aUL.append(createElem('li', '', '&nbsp;', true));

                if (typeof d.secSwitches[article.dataset.se] === 'object') {
                    for (i in d.secSwitches[article.dataset.se]) {
                        let ss = d.secSwitches[article.dataset.se][i];
                        let li = createElem('li', '', ss[3]);
                        li.dataset.ro = ss[0];
                        li.dataset.se = ss[1];
                        li.dataset.pu = ss[2];
                        li.onclick = function (e) {
                            d.updateAd(article, article.id, rId, e.target.dataset.se, e.target.dataset.pu);
                        };
                        aUL.append(li);
                    }
                }

                rDIV.dataset.rootId = rId;
            }
        };
        
        window.scrollTo(0, article.offsetTop);
        const request = async () => {
            if (!d.sections) {
                const response = await fetch('/ajax-menu/?sections=' + (d.ar ? 'ar' : 'en'), _options('GET'));
                const json = await response.json();
                d.roots = json.DATA.roots;
                d.secSwitches = json.DATA.sswitch;
                d.rootSwitches = json.DATA.rswitch;
            }

            if (rUL.childNodes.length === 0) {
                for (var i in d.roots) {
                    let li = createElem('li', '', d.roots[i]['name']);
                    li.dataset.id = i;
                    li.onclick = function (e) {
                        fillSections(e.target.dataset.id);
                    };
                    rUL.append(li);
                }
            }

            fillSections(article.dataset.ro);

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
        var slides = this.container.getElementsByClassName("mySlides");
        var dots = this.container.getElementsByClassName("dot");
        if (n > this.ad.mediaCount) {
            this.index = 1
        }
        if (n < 1) {
            this.index = this.ad.mediaCount
        }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
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
        this.ok = false;
        this.id = parseInt(kId);
        this._node = $.getElementById(kId);
        if (this._node !== null) {
            this._header = this._node.queryAll('header')[0];
            this._editor = this._header.query('.alloc');
            this._message = this._header.query('.msg');
            this.dataset = this._node.dataset;
            this.mediaCount = 0;
            var wrp = this._node.query('p.pimgs');
            if (wrp) {
                this.pixSpans = wrp.queryAll('span');
                if (this.pixSpans && this.pixSpans.length) {
                    this.mediaCount = this.pixSpans.length;
                }
            }
            if (kMaskMessage) {
                this.mask();
                this.maskText(kMaskMessage);
            }
            this.ok = true;
        }
    }
    
    exists() {
        return this._node !== null;
    }
    
    dataset() {
        return this._node.dataset;
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
    }
    
    replName(v) {
        this._editor.innerHTML = v;
    }
    
    getMessage() {
        return this._message.innerText;
    }
    
    setMessage(v) {
        this._message.innerHTML = v;
    }
    
    select() {
        if (this.exists()) {
            this.setAs('selected');
            socket.emit("touch", [this.id, d.KUID]);
            let f = $.getElementById('fixForm');
            if (f && window.getComputedStyle(f).visibility !== "hidden") {
                f.style.display = 'none';
            }
        }
    }
    
    unselect() {
        if (this.exists()) {
            this.unsetAs('selected');
            socket.emit("release", [this.id, d.KUID]);
        }
    }
    
    lock() {
        this.setAs('locked');
        this.mask();
        this.opacity(0.25);
    }
    
    release() {
        if (this.ok) {
            this.unsetAs('locked');
            this.removeMask();
        }
    }
    
    setAs(c) {
        this._node.classList.add(c);
    }
    
    unsetAs(c) {
        this._node.classList.remove(c);
    }
    
    rejected(t) {
        this.unsetAs('approved');
        this.setAs('rejected');
        this.setMessage(t);
        this._node.dataset.status = 3;
    }
    
    approved() {
        this.unsetAs('rejected');
        this.setAs('approved');
        this._node.dataset.status = 2;
        return this;
    }
    
    mask(loader) {
        var _ = this;
        _._m = _._node.query('div.mask');
        if (_._m === null) {
            _._m = createElem("div", 'mask');
            _._node.append(_._m);
        }
        if (loader)
            this.showLoader();
        return this;
    }
    
    removeMask() {
        this._m = this._node.query('div.mask');
        if (this._m) {
            this._m.remove();
            this._m = null;
        }
        return this;
    }
    
    maskText(t) {
        this._m = this._node.query('div.mask');
        if (this._m) { this._m.innerHTML = t; }
        return this;
    }
    
    showLoader() {
        this._m = this._node.query('div.mask');
        if (this._m) {
            this._m.innerHTML = '<div class=loader></div>';
        }
        return this;
    }
    hideLoader() {
        this._m = this._node.query('div.mask');
        if (this._m) {
            this._m.innerHTML = '';
        }
        return this;
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

    if (typeof data.b === 'object') {
        for (let uid in data.b) {
            if (data.b[uid] === 0) {
                continue;
            }
            let ad = new Ad(data.b[uid]);
            if (ad.exists()) {
                ad.replName(d.getName(uid));
                if (uid === d.KUID) {
                    ad.select();
                } else {
                    ad.lock();
                }
            }
        }
    }
});
socket.on("ad_touch", function (data) {
    if (data.hasOwnProperty('x')) {
        if (data.hasOwnProperty('i') && data.i > 0) {
            let ad = new Ad(data.i);
            if (ad.exists()) {
                ad.setName(d.getName(data.x));
                ad.lock();
            }
        }
        if (data.hasOwnProperty('o') && data.o > 0) {
            let ad = new Ad(data.o);
            if (ad.exists()) {
                ad.release();
            }
        }
    }
});
socket.on("ad_release", function (data) {//console.log('releasing', data);
    if (data.hasOwnProperty('i') && data.i > 0) {
        let ad = new Ad(data.i);
        if (ad.exists()) {
            ad.release();
        }
    }
});
socket.on('superAdmin', function (data) { console.log(typeof data, data);
    if (typeof data !== 'undefined' && data.id && data.id > 0) {
        let ad = new Ad(data.id);
        if (ad.ok) {
            ad.mask();
            ad.maskText('Sent To Super Admin');
        }
    }
});
socket.on('editorialUpdate', function (data) { console.log(data);
    if (typeof data === 'object' && data.id) {
        let ad = new Ad(data.id);
        if (ad.ok) {
            ad._node.dataset.ro = data.ro;
            ad._node.dataset.se = data.se;
            ad._node.dataset.pu = data.pu;
            ad._node.query('div.note').innerHTML = data.label;
        }
    }
});
socket.on('editorialImg', function (data) {
    if (typeof data === 'object') {
        let ad = new Ad(data.id);
        if (ad.ok) {
            let p = ad._node.query('p.pimgs');
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
    //console.log('editorialText', data);
    if (typeof data === 'object') {
        let ad = new Ad(data.id);
        if (!ad.ok) {
            return;
        }
        let arText = ad._node.query('section.ar');
        let enText = ad._node.query('section.en');
        if (data.rtl === 1) {
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
            if (arText.classList.contains('ar')) arText.classList.remove('ar');
            if (!arText.classList.contains('en')) { arText.classList.add('en'); }
            arText.innerHTML = data.t;
        }
    }
});
socket.on("ads", function (data) {
    if (typeof data.c === 'undefined') { return; }
    data.c = parseInt(data.c);
    let ad = new Ad(data.id);
    if (ad.exists()) {//console.log('ads', data);             
        var t;
        if (ad.dataset.status >= 0 || c.data === -1) {
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
                    ad.mask();
                    ad.opacity(0.75);
                    var link;
                    if (d.isAdmin()) {
                        var lnks = ad._node.queryAll('div.user > a');
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
    
    socket.on('superAdmin', function (data) {console.log(data);if (typeof data !== 'undefined') {}});
    socket.on('reconnect', function () { console.log('Reconnnect to ws'); });
    socket.on('connect', function () {console.log('connnect to ws');if (d.KUID) { this.emit("hook_myads", [d.KUID, d.level]); }});
    socket.on('disconnect', function () { console.log('disconnect from ws'); });
    socket.on('event', function (data) { console.log('event'); });
};

for (var x = 0; x < d.count; x++) {
    //d.nodes[x].oncontextmenu=function(e){e.preventDefault();};
    d.nodes[x].onclick = function (e) {
        if (this.id == d.currentId) {
            var tagName = e.target.tagName;
            var parent = e.target.parentElement;
            if (tagName === 'A') {
                if ((e.target.className === '' && parent.className === 'mask') || e.target.target === '_similar') {
                    e.stopPropagation();
                    return;
                }
                if (parent.tagName === 'FOOTER') {
                    e.stopPropagation();
                    return;
                }

            } else if (tagName === 'DIV') {
                if (e.target.className === 'mask' || this.classList.contains('locked')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
            if (tagName === 'SECTION') {
                if (ALT && !e.target.isContentEditable) {
                    var re = /\u200b/;
                    var parts = e.target.innerText.split(re);
                    if (parts.length === 2) {
                        e.target.dataset.contacts = parts[1];
                        e.target.contentEditable = "true";
                        e.target.innerHTML = parts[0].trim();
                        e.target.focus();
                    }
                }
                e.preventDefault();
                e.stopPropagation();
                return;
            }
        }
        if (d.currentId !== this.id) {
            d.setId(this.id);
        }
        let editable = $.querySelectorAll("[contenteditable=true]");
        if (editable && editable.length > 0) {
            editable[0].setAttribute("contenteditable", false);
            d.normalize(editable[0]);
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
    };
}
