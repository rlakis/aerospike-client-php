function psf(e) {
    var n = $c(e);
    var l = $cL(e);
    var data = {};
    var u, v, w, s, err = 0;
    for (var i = 0; i < l; i++) {
        if (n[i].id) {
            w = n[i].value;
            u = n[i].getAttribute('req');
            if (u) {
                v = n[i].getAttribute('mins');
                if (!v) v = 0;
                if (w.length == 0 || w.length < v) {
                    if (!s) s = n[i];
                    err = 1;
                    ssf(n[i])
                }
            }
            data[n[i].id] = w
        }
    }
    if (err) {
        data = false;
        if (s) {
            var p = $b(s);
            if (p.tagName == 'LABEL') {
                window.scrollTo(0, p.offsetTop)
            }
        }
    }
    return data
}

function ssf(e) {
    fdT(e, 0, 'err');
    var p = $b(e);
    if (p && p.tagName == 'LABEL') {
        p.className = ' err';
        p.innerHTML = e.getAttribute('req')
    }
    e.onfocus = function() {
        usf(e)
    }
}

function usf(e) {
    fdT(e, 1, 'err');
    var p = $b(e);
    if (p && p.tagName == 'LABEL') {
        p.className = '';
        p.innerHTML = e.title
    }
    e.onfocus = function() {}
}

function dsl(e, m, l, c, o) {
    var d = $('#loader')[0];
    if (d) d.innerHTML = '';
    else d = document.createElement('div');
    d.className = 'loader';
    d.id = 'loader';
    var i = document.createElement('div');
    i.className = 'in';
    i.innerHTML = m;
    var b;
    if (l) {
        b = document.createElement('div');
        b.className = 'load';
        i.appendChild(b)
    }
    if (c) {
        b = document.createElement('input');
        b.type = 'button';
        b.className = 'button bt';
        b.value = (lang == 'ar' ? 'متابعة' : 'continue');
        b.onclick = function() {
            del()
        };
        i.appendChild(b)
    }
    d.appendChild(i);
    if (o) e.setAttribute('sct', o);
    e.style.display = 'none';
    $p(e).insertBefore(d, e)
}

function del() {
    var d = $('#loader')[0];
    var e = $a(d);
    $p(d).removeChild(d);
    var o = e.getAttribute('sct');
    e.style.display = 'block';
    if (o) {
        window.scrollTo(0, o);
        e.setAttribute('sct', 0)
    }
}