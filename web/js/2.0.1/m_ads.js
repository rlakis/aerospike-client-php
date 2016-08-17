var eid = 0;

function ado(e, i, v) {
    se(v);
    eid = i;
    if (!statDiv) statDiv = $('#statAiv');
    var li = e;
    var e = $c($c(li, 1), 2);
    if (!leb) {
        leb = $('#aopt')[0]
    } else if ($p(leb)) {
        var l = $p(leb);
        if (l != li) {
            fdT(l, 1, 'edit');
            $(peb).removeClass('aup');
            statDiv.parent().css('display', 'none');
            statDiv.addClass('load');
            statDiv.html('');
            acls(leb)
        }
        l.removeChild(leb);
        leb.style.display = "none"
    }
    peb = e;
    var z = $(e);
    if ($(li).hasClass('edit')) {
        fdT(li, 1, 'edit');
        z.removeClass('aup')
    } else {
        fdT(li, 0, 'edit');
        z.addClass('aup');
        li.appendChild(leb);
        leb.style.display = "block";
        aht(li)
    }
};

function aht(e) {
    var b = e.offsetTop + e.offsetHeight;
    var d = document.body;
    var wh = window.innerHeight;
    var t = wh + d.scrollTop;
    if (b > t) {
        window.scrollTo(0, b - wh)
    }
};

function acls(d) {
    var t = $c($f(d));
    var c = t.length;
    for (var i = 0; i < c; i++) {
        $(t[i]).removeClass('on')
    }
}

function mask(e, d, m) {
    if (!d) d = document.createElement('div');
    d.style.display = 'block';
    if (m) {
        d.innerHTML = m;
        d.className = 'mask';
        d.style.paddingTop = (e.offsetHeight / 2 - 10) + 'px'
    } else {
        d.className = 'mask load'
    }
    e.appendChild(d);
    return d
}

function adel(e, h, v) {
    se(v);
    if (confirm(lang == 'ar' ? 'حذف هذا الإعلان؟' : 'Delete this ad?')) {
        if (!h) h = 0;
        var l = $p(e, 2);
        var s = $p(l);
        fdT(l, 1, 'adn');
        s.removeChild(l);
        var d = mask(s);
        $.ajax({
            type: 'POST',
            url: '/ajax-adel/',
            dataType: 'json',
            data: {
                i: eid,
                h: h
            },
            success: function(rp) {
                if (rp.RP) {
                    var m = (lang == 'ar' ? 'تم الحذف' : 'Deleted');
                    mask(s, d, m)
                } else {
                    s.removeChild(d)
                }
            }
        });
    }
}

function are(e, v) {
    se(v);
    if (confirm(lang == 'ar' ? 'تجديد هذا الإعلان؟' : 'Renew this ad?')) {
        var l = $p(e, 2);
        var s = $p(l);
        fdT(l, 1, 'adn');
        s.removeChild(l);
        var d = mask(s);
        $.ajax({
            type: 'POST',
            url: '/ajax-arenew/',
            dataType: 'json',
            data: {
                i: eid
            },
            success: function(rp) {
                if (rp.RP) {
                    var m = (lang == 'ar' ? 'تم تحويل الإعلان للائحة إنتظار النشر' : 'Ad is pending to be re-published');
                    mask(s, d, m)
                } else {
                    s.removeChild(d)
                }
            }
        });
    }
}

function ahld(e, v) {
    console.log(v);
    se(v);
    if (confirm(lang == 'ar' ? 'إيقاف عرض هذا الإعلان؟' : 'Stop this ad?')) {
        var l = $p(e, 2);
        var s = $p(l);
        fdT(l, 1, 'adn');
        s.removeChild(l);
        var d = mask(s);
        $.ajax({
            type: 'POST',
            url: '/ajax-ahold/',
            dataType: 'json',
            data: {
                i: eid
            },
            success: function(rp) {
                if (rp.RP) {
                    var m = (lang == 'ar' ? 'تم تحويل الإعلان إلى الأرشيف' : 'Ad is moved to archive');
                    mask(s, d, m)
                } else {
                    s.removeChild(d)
                }
            }
        })
    }
}

function sLD(e) {
    var c = e.childNodes;
    var l = c.length;
    if (l == 3) {
        e.removeChild(c[2]);
        l = 2
    }
    for (var i = 0; i < l; i++) {
        c[i].style.visibility = 'hidden'
    }
    var d = document.createElement('span');
    d.className = 'load';
    e.appendChild(d);
    $(e).addClass('on')
}

function eLD(e) {
    $(e).removeClass('on');
    var c = e.childNodes;
    var l = c.length;
    if (l == 3) {
        e.removeChild(c[2]);
        l = 2
    }
    for (var i = 0; i < l; i++) {
        c[i].style.visibility = 'visible'
    }
};
var ts = document.getElementsByTagName('time');
var ln = ts.length;
var time = parseInt((new Date().getTime()) / 1000);
var d = 0,
    dy = 0,
    rt = '';
for (var i = 0; i < ln; i++) {
    d = time - parseInt(ts[i].getAttribute('st'));
    dy = Math.floor(d / 86400);
    rt = '';
    if (lang == 'ar') {
        rt = since + ' ';
        if (dy) {
            rt += (dy == 1 ? "يوم" : (dy == 2 ? "يومين" : dy + ' ' + (dy < 11 ? "أيام" : "يوم")))
        } else {
            dy = Math.floor(d / 3600);
            if (dy) {
                rt += (dy == 1 ? "ساعة" : (dy == 2 ? "ساعتين" : dy + ' ' + (dy < 11 ? "ساعات" : "ساعة")))
            } else {
                dy = Math.floor(d / 60);
                if (dy == 0) dy = 1;
                rt += (dy == 1 ? "دقيقة" : (dy == 2 ? "دقيقتين" : dy + ' ' + (dy < 11 ? "دقائق" : "دقيقة")))
            }
        }
    } else {
        if (dy) {
            rt = (dy == 1 ? '1 day' : dy + ' days')
        } else {
            dy = Math.floor(d / 3600);
            if (dy) {
                rt = (dy == 1 ? '1 hour' : dy + ' hours')
            } else {
                dy = Math.floor(d / 60);
                if (dy == 0) dy = 1;
                rt = (dy == 1 ? '1 minute' : dy + ' minutes')
            }
        }
        rt += ' ' + ago
    }
    ts[i].innerHTML = ' ' + rt
}
var statDiv;

function aStat(e, d) {
    se(d);
    var d = $p(e, 2);
    var s = $c(d, 1);
    var z = $(e);
    if (z.hasClass('on')) {
        s.style.display = 'none';
        z.removeClass('on')
    } else {
        acls(d);
        z.addClass('on');
        s.style.display = 'block';
        aht(d.parentNode);
        var y = statDiv;
        y.addClass('load');
        $('.sopt', s).remove();
        var ix = $p(d).id;
        $.ajax({
            type: 'POST',
            url: '/ajax-ga/',
            data: {
                u: uid,
                a: ix
            },
            dataType: 'json',
            success: function(sp) {
                if (sp.RP) {
                    if (sp.DATA.d) {
                        var gSA = {
                            chart: {
                                spacingRight: 0,
                                spacingLeft: 0,
                                renderTo: 'statAiv'
                            },
                            title: {
                                text: sp.DATA.t + (lang == 'ar' ? ' مشاهدة لهذا الإعلان' : ' impressions for this ad'),
                                style: {
                                    'font-weight': 'bold',
                                    'font-family': (lang == 'ar' ? 'tahoma,arial' : 'verdana,arial'),
                                    'direction': (lang == 'ar' ? 'rtl' : 'ltr')
                                }
                            },
                            xAxis: {
                                type: 'datetime',
                                title: {
                                    text: null
                                }
                            },
                            yAxis: {
                                title: {
                                    text: null
                                }
                            },
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                enabled: false
                            },
                            colors: ['#2f7ed8', '#ff9000']
                        };
                        var seriesA;
                        if (typeof(sp.DATA.k) === 'undefined') {
                            seriesA = [{
                                type: 'line',
                                name: 'Impressions',
                                pointInterval: 24 * 3600 * 1000,
                                pointStart: sp.DATA.d,
                                data: sp.DATA.c
                            }]
                        } else {
                            seriesA = [{
                                type: 'line',
                                name: 'Impressions',
                                pointInterval: 24 * 3600 * 1000,
                                pointStart: sp.DATA.d,
                                data: sp.DATA.c
                            }, {
                                type: 'line',
                                name: 'Interactions',
                                pointInterval: 24 * 3600 * 1000,
                                pointStart: sp.DATA.d,
                                data: sp.DATA.k
                            }]
                        }
                        gSA['series'] = seriesA;
                        var chart = new Highcharts.Chart(gSA);
                        if (!isAc) {
                            var tm = $('<span class="sopt"><span class="bt fb"><span class="k refr"></span></span></span>');
                            y.parent().append(tm);
                            tm.click(function(e) {
                                se(e);
                                var b = $(this);
                                b.css('display', 'none');
                                y.addClass('load');
                                $.ajax({
                                    type: 'POST',
                                    url: '/ajax-ga/',
                                    data: {
                                        u: uid,
                                        a: ix
                                    },
                                    dataType: 'json',
                                    success: function(bp) {
                                        y.removeClass('load');
                                        b.css('display', 'block');
                                        if (bp.RP) {
                                            if (bp.DATA.d) {
                                                gSA.title.text = bp.DATA.t + (lang == 'ar' ? ' مشاهدة لهذا الإعلان' : ' impressions for this ad');
                                                if (typeof(bp.DATA.k) === 'undefined') {
                                                    gSA.series = [{
                                                        type: 'line',
                                                        name: 'Impressions',
                                                        pointInterval: 24 * 3600 * 1000,
                                                        pointStart: bp.DATA.d,
                                                        data: bp.DATA.c
                                                    }]
                                                } else {
                                                    gSA.series = [{
                                                        type: 'line',
                                                        name: 'Impressions',
                                                        pointInterval: 24 * 3600 * 1000,
                                                        pointStart: bp.DATA.d,
                                                        data: bp.DATA.c
                                                    }, {
                                                        type: 'line',
                                                        name: 'Interactions',
                                                        pointInterval: 24 * 3600 * 1000,
                                                        pointStart: bp.DATA.d,
                                                        data: bp.DATA.k
                                                    }]
                                                }
                                                chart = new Highcharts.Chart(gSA)
                                            }
                                        }
                                    },
                                    error: function(bp) {
                                        y.removeClass('load');
                                        b.css('display', 'block')
                                    }
                                })
                            })
                        }
                    } else {
                        y.addClass('hxf');
                        y.html(lang == 'ar' ? 'لا يوجد إحصائية عدد مشاهدات للعرض' : 'No impressions data to display')
                    }
                } else {
                    y.addClass('hxf');
                    y.html('<span class="fail"></span>' + (lang == 'ar' ? 'فشل محرك مرجان بالحصول على إحصائيات إعلانك' : 'Mourjan system failed to load your ad statistics'))
                }
                y.removeClass('load')
            }
        })
    }
}
var isAc = document.location.search.match('archive') !== null ? 1 : 0;
var li = $('li', $('#resM')),
    lip, ptm, atm = {},
    liCnt, aCnt = 0;

function rePic() {
    var h = $(window).height();
    if (!lip) {
        lip = $(".thb", li);
        liCnt = lip.length
    }
    lip.each(function(i, e) {
        var c = $p(e, 2).id;
        if (!atm[i]) {
            var r = e.getBoundingClientRect();
            var k = r.top;
            if (k >= -100 && k <= h + 100) {
                e.innerHTML = sic[c];
                atm[i] = 1;
                aCnt++
            }
        }
    });
    if (liCnt == aCnt) {
        $(window).unbind('scroll', trePic);
        $(window).unbind('scroll', trePic)
    }
}

function trePic() {
    if (ptm) {
        clearTimeout(ptm);
        ptm = null
    }
    ptm = setTimeout('rePic()', 100)
}
$(window).bind('scroll', trePic);
$(window).bind('resize', trePic);
trePic();
if (uhc) {
    var HSLD = 0;
    (function() {
        var sh = document.createElement('script');
        sh.type = 'text/javascript';
        sh.async = true;
        sh.src = uhc + '/mob.js';
        sh.onload = sh.onreadystatechange = function() {
            if (!HSLD && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                HSLD = 1;
                $.ajax({
                    type: 'POST',
                    url: '/ajax-ga/',
                    data: {
                        u: (typeof uuid !== 'undefined' && uuid) ? uuid : uid,
                        x: isAc
                    },
                    dataType: 'json',
                    success: function(rp) {
                        if (rp.RP) {
                            if (!isAc) {
                                if (rp.DATA.d) {
                                    var gS = {
                                        chart: {
                                            spacingRight: 0,
                                            spacingLeft: 0,
                                            renderTo: 'statDv'
                                        },
                                        title: {
                                            text: rp.DATA.t + (lang == 'ar' ? ' إجمالي مشاهدات إعلاناتي' : ' overall impressions'),
                                            style: {
                                                'font-weight': 'bold',
                                                'font-family': (lang == 'ar' ? 'tahoma,arial' : 'verdana,arial'),
                                                'direction': (lang == 'ar' ? 'rtl' : 'ltr')
                                            }
                                        },
                                        xAxis: {
                                            type: 'datetime',
                                            title: {
                                                text: null
                                            }
                                        },
                                        yAxis: {
                                            title: {
                                                text: null
                                            }
                                        },
                                        tooltip: {
                                            shared: true
                                        },
                                        legend: {
                                            enabled: false
                                        },
                                        colors: ['#2f7ed8', '#ff9000']
                                    };
                                    var series;
                                    if (typeof(rp.DATA.k) === 'undefined') {
                                        series = [{
                                            type: 'line',
                                            name: 'Impressions',
                                            pointInterval: 24 * 3600 * 1000,
                                            pointStart: rp.DATA.d,
                                            data: rp.DATA.c
                                        }]
                                    } else {
                                        series = [{
                                            type: 'line',
                                            name: 'Impressions',
                                            pointInterval: 24 * 3600 * 1000,
                                            pointStart: rp.DATA.d,
                                            data: rp.DATA.c
                                        }, {
                                            type: 'line',
                                            name: 'Interactions',
                                            pointInterval: 24 * 3600 * 1000,
                                            pointStart: rp.DATA.d,
                                            data: rp.DATA.k
                                        }]
                                    }
                                    gS['series'] = series;
                                    var chart = new Highcharts.Chart(gS);
                                    var x = $('#statDv');
                                    var tm = $('<div class="sopt"><span class="bt fb"><span class="k refr"></span></span></div>');
                                    x.parent().append(tm);
                                    tm.click(function(e) {
                                        var b = $(this);
                                        b.css('display', 'none');
                                        x.addClass('load');
                                        $.ajax({
                                            type: 'POST',
                                            url: '/ajax-ga/',
                                            data: {
                                                u: uuid ? uuid : uid,
                                                x: isAc
                                            },
                                            dataType: 'json',
                                            success: function(bp) {
                                                x.removeClass('load');
                                                b.css('display', 'block');
                                                if (bp.RP) {
                                                    if (bp.DATA.d) {
                                                        gS.title.text = bp.DATA.t + (lang == 'ar' ? ' إجمالي مشاهدات إعلاناتي' : ' overall impressions');
                                                        if (typeof(bp.DATA.k) === 'undefined') {
                                                            gS.series = [{
                                                                type: 'line',
                                                                name: 'Impressions',
                                                                pointInterval: 24 * 3600 * 1000,
                                                                pointStart: bp.DATA.d,
                                                                data: bp.DATA.c
                                                            }]
                                                        } else {
                                                            gS.series = [{
                                                                type: 'line',
                                                                name: 'Impressions',
                                                                pointInterval: 24 * 3600 * 1000,
                                                                pointStart: bp.DATA.d,
                                                                data: bp.DATA.c
                                                            }, {
                                                                type: 'line',
                                                                name: 'Interactions',
                                                                pointInterval: 24 * 3600 * 1000,
                                                                pointStart: bp.DATA.d,
                                                                data: bp.DATA.k
                                                            }]
                                                        }
                                                        chart = new Highcharts.Chart(gS)
                                                    }
                                                }
                                            },
                                            error: function(bp) {
                                                x.parent().removeClass('load');
                                                b.css('display', 'block')
                                            }
                                        })
                                    })
                                } else {
                                    var x = $('#statDv');
                                    x.removeClass('load');
                                    x.addClass('hxf');
                                    x.html(lang == 'ar' ? 'لا يوجد إحصائية عدد مشاهدات للعرض' : 'No impressions data to display');
                                    trePic()
                                }
                            }
                            li.each(function(i, e) {
                                var s = $('.ata', e);
                                s.removeClass('load');
                                if (typeof rp.DATA.a[e.id] !== 'undefined') {
                                    s.html('<span class="k stat"></span>' + rp.DATA.a[e.id] + (lang == 'ar' ? ' مشاهدة' : ' imp'))
                                } else {
                                    s.html('<span class="k stat"></span> NA')
                                }
                            })
                        } else {
                            var x = $('#statDv');
                            x.removeClass('load');
                            x.addClass('hxf');
                            x.html('<span class="fail"></span>' + (lang == 'ar' ? 'فشل محرك مرجان بالحصول على إحصائيات حسابك' : 'Mourjan system failed to load your statistics'));
                            trePic()
                        }
                    }
                })
            }
        };
        head.insertBefore(sh, head.firstChild)
    })()
} else if (ustats) {
    $.ajax({
        type: 'POST',
        url: '/ajax-ga/',
        data: {
            u: (typeof uuid !== 'undefined' && uuid) ? uuid : uid,
            x: isAc
        },
        dataType: 'json',
        success: function(rp) {
            if (rp.RP) {
                li.each(function(i, e) {
                    var s = $('.ata', e);
                    s.removeClass('load');
                    if (typeof rp.DATA.a[e.id] !== 'undefined') {
                        s.html('<span class="k stat"></span>' + rp.DATA.a[e.id] + (lang == 'ar' ? ' مشاهدة' : ' imp'))
                    } else {
                        s.html('<span class="k stat"></span> NA')
                    }
                })
            }
        }
    })
}