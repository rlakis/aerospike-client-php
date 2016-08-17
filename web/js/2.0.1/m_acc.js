function ckO(e) {
    var z = $(e);
    if (z.hasClass('on')) {
        z.removeClass('on')
    } else {
        z.addClass('on')
    }
    var c = $c($p(e));
    $.ajax({
        type: 'POST',
        url: '/ajax-account/',
        dataType: 'json',
        data: {
            form: 'notifications',
            lang: lang,
            fields: {
                ads: ($(c[1]).hasClass('on') ? 1 : 0),
                coms: ($(c[2]).hasClass('on') ? 1 : 0),
                news: ($(c[3]).hasClass('on') ? 1 : 0)
            }
        }
    })
}

function elg(e) {
    var p = $p(e);
    var o = p.className.match(/pi/);
    if ($(e).hasClass('h')) {
        if (o) olg(p)
    } else {
        if (o) {
            olg(p)
        } else {
            var c = $c(p);
            var v = e.getAttribute('val');
            if (v == 'ar') {
                $(c[2]).addClass('hid');
                $(c[1]).removeClass('hid')
            } else {
                $(c[1]).addClass('hid');
                $(c[2]).removeClass('hid')
            }
            fdT(p, 0, 'pi');
            $.ajax({
                type: 'POST',
                url: '/ajax-account/',
                dataType: 'json',
                data: {
                    form: 'lang',
                    lang: lang,
                    fields: {
                        lang: v
                    }
                }
            })
        }
    }
}

function olg(u) {
    var c = $c(u);
    fdT(u, 1, 'pi');
    fdT(c[1], 1);
    fdT(c[2], 1)
}

function enm(e) {
    var p = $p(e);
    if (p.className.match(/pi/)) {
        var c = $c(p);
        fdT(p, 1, 'pi');
        fdT(c[1]);
        fdT(c[2], 1);
        $f(c[2], 4).focus()
    }
}
var bt, btt, tlg;

function initB(e) {
    bt = e;
    e.onpaste = function() {
        setTimeout('capk();', 10)
    };
    btt = e.getAttribute('name')
}

function capk(e) {
    if (!e) e = bt;
    var v = e.value;
    if (btt == 'name') {
        if (v.match(/[\u0621-\u064a\u0750-\u077f]/)) {
            tlg = e.className = 'ar'
        } else {
            tlg = e.className = 'en'
        }
    }
    setTimeout('tgl();', 100)
}

function tgl() {
    var v = bt.value;
    var bu = $f($a($p(bt, 2), 2), 2);
    if (btt == 'name') {
        if (v.length >= 3) {
            fdT(bu, 1, 'off')
        } else {
            fdT(bu, 0, 'off')
        }
    } else if (btt == 'email') {
        if (v.match(/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/)) {
            fdT(bu, 1, 'off')
        } else {
            fdT(bu, 0, 'off')
        }
    }
}

function clF(e) {
    if (!bt) bt = $f($b($p(e, 2), 2), 2);
    bt.value = '';
    var p = $p(e, 3);
    var c = $c(p);
    fdT(c[0], 1);
    fdT(c[1]);
    fdT($f(c[2], 2), 0, 'off');
    p = $p(p, 2);
    c = $c(p);
    fdT(c[1], 1);
    fdT(c[2], 0);
    fdT(p, 0, 'pi')
}

function savN(e) {
    if (!bt) bt = $f($b($p(e, 2), 2), 2);
    var v = bt.value;
    v = v.replace(/^\s+|\s+$/g, '');
    bt.value = v;
    if (!e.className.match(/off/)) {
        if (v < 3) {
            fdT(e, 0, 'off')
        } else {
            if (v == uname) {
                clF($f($a($p(bt, 2), 2), 2))
            } else {
                if (v.match(/[^a-zA-Z\u0621-\u064a\u0750-\u077f\s]/)) {
                    var m = (lang == 'ar' ? 'الإسم لايمكن أن يحتوي سوى على أحرف الأبجدية' : 'Name can only contain alphabet characters');
                    setE(m)
                } else {
                    setE();
                    $.ajax({
                        type: 'POST',
                        url: '/ajax-account/',
                        dataType: 'json',
                        data: {
                            form: 'name',
                            lang: lang,
                            fields: {
                                name: v
                            }
                        },
                        success: function(rp) {
                            if (rp.RP) {
                                var t = rp.DATA.fields.name[1];
                                var l = $b($p(e, 4));
                                l.className = tlg;
                                var c = $f(l);
                                c.innerHTML = t + '<span class="et"></span>';
                                uname = t;
                                clF(e)
                            } else {
                                setE(rp.MSG)
                            }
                        }
                    })
                }
            }
        }
    }
}

function savM(e) {
    if (!bt) bt = $f($b($p(e, 2), 2), 2);
    if (!e.className.match(/off/)) {
        var v = bt.value;
        if (v.value == uemail) {
            clF($f($a($p(bt, 2), 2), 2))
        } else {
            setE();
            $.ajax({
                type: 'POST',
                url: '/ajax-account/',
                dataType: 'json',
                data: {
                    form: 'email',
                    lang: lang,
                    fields: {
                        email: v
                    }
                },
                success: function(rp) {
                    if (rp.RP) {
                        var i = rp.DATA.fields.email;
                        var t;
                        if (i[2]) {
                            t = rp.DATA.fields.email[2]
                        } else {
                            t = rp.DATA.fields.email[1]
                        }
                        var c = $f($b($p(e, 4)));
                        c.innerHTML = t + '<span class="et"></span>';
                        uemail = t;
                        clF(e)
                    } else {
                        setE(rp.MSG)
                    }
                }
            })
        }
    }
}

function setE(m) {
    var l = $a($p(bt, 2));
    if (m) {
        fdT(l, 0, 'liw');
        l.innerHTML = '<b>' + m + '</b>';
        fdT($b(l), 1)
    } else {
        fdT(l, 1, 'liw');
        l.innerHTML = '<b class="load"></b>';
        fdT($b(l))
    }
    fdT(l, 1)
}