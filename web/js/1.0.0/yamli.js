// Copyright (c) 2010 Language Analytics LLC //
// Build 5457 //
window.Yamli || function() {
    var G = function(a) {
        return document.createElement(a)
    }, ha = encodeURIComponent, Xb = window.setInterval, Ya = document.documentElement, Ob = Ya.ownerDocument, ia, hc, Yb = {STYLE: 1, SCRIPT: 1}, i = {}, l;
    window.Yamli = i;
    i.I = {};
    i.I.buildNumber = "5447";
    i.isDebug = true;
    i.debug = function(a) {
        typeof window.debug != "undefined" && window.debug(a);
        typeof console != "undefined" && console.log(a)
    };
    i.error = function(a) {
        typeof window.error != "undefined" && window.error(a);
        typeof console != "undefined" && console.error(a)
    };
    i.assert =
            function(a, c) {
                a || i.error("Assert failed: " + c)
            };
    var ic, Za, Eb, Zb, da, jc, ub, Pb, kc, $b, Wa = 0, vb = 0, Gc = {en: {dir: "ltr", show_more: "show more", report_word: "report word", report_word_hint: "Click to report that your word choice doesn't appear", powered: 'Powered by <a class="yamliapi_anchor" target="yamli_win" href="http://www.yamli.com">Yamli.com</a>', settings_link: "Yamli 3arabi", settings_link_hint: "Change Yamli Arabic typing settings", quick_toggle_on: "on", quick_toggle_on_hint: "Click to enable Arabic conversion",
            quick_toggle_off: "off", quick_toggle_off_hint: "Click to disable Arabic conversion", settings_title: "Yamli Settings", close: "close", settings_close_hint: "Close settings", enable_link: "Turn Arabic on", enable_link_hint: "Click to enable Arabic conversion", disable_link: "Turn Arabic off", disable_link_hint: "Click to disable Arabic conversion", align_left_link: "Type left to right", align_left_link_hint: "Click if you are writing mostly in English", align_right_link: "Type right to left", align_right_link_hint: "Click if you are writing mostly in Arabic",
            tips_link: "Quick Tips", tips_link_hint: "Quick tips to get your started", help_link: "Tutorial", help_link_hint: "Learn how to use Yamli", tips_title: "Yamli Quick Tips", tips_close_hint: "Close quick tips", hint_content_start: '<span style=\'font-weight:bold\'>Type Arabic</span> using <a class="yamliapi_anchor_y" target="yamli_win" href="http://www.yamli.com">Yamli</a> !', hint_content_try: "<span style='text-decoration:underline'>Try it!</span>", hint_content_notry: "<span style='text-decoration:underline'>Turn off for now</span>",
            hint_content_end: "For more options, click on [Y]", loading: "Loading...", sponsored: "Sponsored by", def_pick_hint: "Just press <span style='font-weight:bold'>SPACE</span><br/>after typing a word"}, fr: {dir: "ltr", show_more: "plus de choix", report_word: "mot manquant", report_word_hint: "Cliquez pour nous informer que le mot voulu manque", powered: 'motoris\u00e9 par <a class="yamliapi_anchor" target="yamli_win" href="http://www.yamli.com">Yamli.com</a>', settings_link: "Yamli 3arabi", settings_link_hint: "Changez vos pr\u00e9f\u00e9rences",
            quick_toggle_on: "actif", quick_toggle_on_hint: "Activez la conversion en Arabe", quick_toggle_off: "inactif", quick_toggle_off_hint: "D\u00e9sactivez la conversion en Arabe", settings_title: "Pr\u00e9f\u00e9rences Yamli", close: "fermer", settings_close_hint: "Fermez les pr\u00e9f\u00e9rences", enable_link: "Activer l'Arabe", enable_link_hint: "Activez la conversion en Arabe", disable_link: "D\u00e9sactivez l'Arabe", disable_link_hint: "D\u00e9sactivez la conversion en Arabe", align_left_link: "Taper de gauche \u00e0 droite",
            align_left_link_hint: "Cliquez si vous \u00e9crivez surtout en Fran\u00e7ais", align_right_link: "Taper de droite \u00e0 gauche", align_right_link_hint: "Cliquez si vous \u00e9crivez surtout en Arabe", tips_link: "Aide rapide", tips_link_hint: "Conseils rapides pour rapidement utiliser Yamli", help_link: "Aide detail\u00e9e", help_link_hint: "Apprenez \u00e0 utiliser Yamli", tips_title: "Aide Rapide pour Yamli", tips_close_hint: "Fermer l'aide rapide", hint_content_start: '<span style=\'font-weight:bold\'>Tape en Arabe</span> avec <a class="yamliapi_anchor_y" target="yamli_win" href="http://www.yamli.com/fr/">Yamli</a> !',
            hint_content_try: "<span style='text-decoration:underline'>Essaye!</span>", hint_content_notry: "<span style='text-decoration:underline'>Pas maintenant</span>", hint_content_end: "Pour plus d'options, clique sur [Y]", loading: "Chargement...", sponsored: "Sponsoris\u00e9 par", def_pick_hint: "Tape <span style='font-weight:bold'>ESPACE</span> pour<br/>choisir le meilleur mot"}, ar: {dir: "rtl", show_more: "\u0627\u0639\u0631\u0636 \u0627\u0644\u0645\u0632\u064a\u062f", report_word: "\u0643\u0644\u0645\u0629 \u0646\u0627\u0642\u0635\u0629",
            report_word_hint: "\u0627\u0646\u0642\u0631 \u0625\u0630\u0627 \u0644\u0645 \u062a\u062c\u062f \u0643\u0644\u0645\u062a\u0643", powered: 'Powered by <a class="yamliapi_anchor" target="yamli_win" href="http://www.yamli.com">Yamli.com</a>', settings_link: "Yamli 3arabi", settings_link_hint: "\u062e\u064a\u0627\u0631\u0627\u062a", quick_toggle_on: "\u0646\u0634\u0637", quick_toggle_on_hint: "\u0627\u0646\u0642\u0631 \u0644\u062a\u0641\u0639\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629", quick_toggle_off: "\u063a\u064a\u0631 \u0646\u0634\u0637",
            quick_toggle_off_hint: "\u0627\u0646\u0642\u0631 \u0644\u062a\u0639\u0637\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629", settings_title: "Yamli \u062e\u064a\u0627\u0631\u0627\u062a", close: "\u0623\u063a\u0644\u0642", settings_close_hint: "\u0623\u063a\u0644\u0642 \u0627\u0644\u062e\u064a\u0627\u0631\u0627\u062a", enable_link: '<span style="display:block;text-align:right">\u062a\u0641\u0639\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629</span>', enable_link_hint: "\u0627\u0646\u0642\u0631 \u0644\u062a\u0641\u0639\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629 ",
            disable_link: '<span style="display:block;text-align:right">\u062a\u0639\u0637\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629</span>', disable_link_hint: "\u0627\u0646\u0642\u0631 \u0644\u062a\u0639\u0637\u064a\u0644 \u0627\u0644\u062e\u062f\u0645\u0629", align_left_link: '<span style="display:block;text-align:right">\u0645\u0646 \u0627\u0644\u064a\u0633\u0627\u0631 \u0625\u0644\u0649 \u0627\u0644\u064a\u0645\u064a\u0646</span>', align_left_link_hint: "\u0627\u0646\u0642\u0631 \u0647\u0646\u0627 \u0625\u0646 \u0643\u0646\u062a \u062a\u0643\u062a\u0628 \u0645\u0639\u0638\u0645\u0627\u064b \u0628\u0627\u0644\u0644\u063a\u0629 \u0627\u0644\u0625\u0646\u0643\u0644\u064a\u0632\u064a\u0629",
            align_right_link: '<span style="display:block;text-align:right">\u0645\u0646 \u0627\u0644\u064a\u0645\u064a\u0646 \u0625\u0644\u0649 \u0627\u0644\u064a\u0633\u0627\u0631</span>', align_right_link_hint: "\u0627\u0646\u0642\u0631 \u0647\u0646\u0627 \u0625\u0646 \u0643\u0646\u062a \u062a\u0643\u062a\u0628 \u0645\u0639\u0638\u0645\u0627\u064b \u0628\u0627\u0644\u0644\u063a\u0629 \u0627\u0644\u0639\u0631\u0628\u064a\u0629", tips_link: '<span style="display:block;text-align:right">\u0646\u0635\u0627\u0626\u062d \u0633\u0631\u064a\u0639\u0629</span>',
            tips_link_hint: "\u0646\u0635\u0627\u0626\u062d \u0633\u0631\u064a\u0639\u0629", help_link: '<span style="display:block;text-align:right">\u0645\u0633\u0627\u0639\u062f\u0629</span>', help_link_hint: "Yamli \u062a\u0639\u0644\u0645 \u0643\u064a\u0641\u064a\u0629 \u0625\u0633\u062a\u0639\u0645\u0627\u0644", tips_title: "\u0646\u0635\u0627\u0626\u062d \u0633\u0631\u064a\u0639\u0629", tips_close_hint: "\u0623\u063a\u0644\u0642 \u0646\u0627\u0641\u0630\u0629 \u0627\u0644\u0646\u0635\u0627\u0626\u062d \u0627\u0644\u0633\u0631\u064a\u0639\u0629",
            hint_content_start: '<span style=\'font-weight:bold\'>\u0627\u0643\u062a\u0628 \u0639\u0631\u0628\u064a</span> \u0645\u0639 <a class="yamliapi_anchor_y" target="yamli_win" href="http://www.yamli.com/ar">Yamli</a> !', hint_content_try: "<span style='text-decoration:underline'>\u062c\u0631\u0651\u0628 \u0627\u0644\u0622\u0646!</span>", hint_content_notry: "<span style='text-decoration:underline'>\u0644\u064a\u0633 \u0627\u0644\u0622\u0646</span>", hint_content_end: "\u0644\u0645\u0632\u064a\u062f \u0645\u0646 \u0627\u0644\u062e\u064a\u0627\u0631\u0627\u062a\u060c \u0627\u0646\u0642\u0631 [Y]",
            loading: "Loading...", sponsored: "\u0628\u0631\u0639\u0627\u064a\u0629", def_pick_hint: "\u0625\u0636\u063a\u0637 \u0639\u0644\u0649 <span style='font-weight:bold;white-space:nowrap'>\u0627\u0644\u0645\u0633\u0627\u0641\u0629 (SPACE)</span><br/>\u0644\u0625\u062e\u062a\u064a\u0627\u0631 \u0627\u0644\u0643\u0644\u0645\u0629 \u0627\u0644\u0623\u0641\u0636\u0644 \u062a\u0644\u0642\u0627\u0626\u064a\u0627\u064b"}}, ac;
    i.I.setUnsupported = function(a) {
        Za = 5;
        ac = a;
        if (a === void 0)
            ac = 'Yamli is not supported on this web browser.<br />We recommend using <a href="http://getfirefox.com/">Firefox</a>.'
    };
    var lc = function() {
        ic = document.compatMode == "BackCompat";
        Za = 0;
        var a = navigator.userAgent.toLowerCase(), c;
        if (c = a.match(/msie ([0-9])\.([0-9])/i)) {
            Wa = parseInt(c[1], 10);
            vb = parseInt(c[2], 10);
            if (Wa < 6 || a.indexOf("iemobile") != -1) {
                i.I.setUnsupported();
                return
            }
            da = Za = 1;
            if (Wa == 6)
                jc = 1
        }
        if (c = a.match(/safari\/([0-9]+)\.([0-9]+)/i)) {
            Wa = parseInt(c[1], 10);
            vb = parseInt(c[2], 10);
            if (Wa < 525)
                i.I.setUnsupported('Yamli requires <a href="http://www.apple.com/safari/">Safari 3.1</a> or better');
            else {
                Za = 3;
                ub = 1;
                if (a.indexOf("chrome") !=
                        -1)
                    $b = 1;
                else
                    kc = 1
            }
        } else if (c = a.match(/firefox\/([0-9]+)\.([0-9]+)/i)) {
            Wa = parseInt(c[1], 10);
            vb = parseInt(c[2], 10);
            Zb = Eb = 1;
            Za = 2
        } else if (a.indexOf("gecko") != -1)
            if (a.indexOf("gecko/20020924 aol/7.0") != -1)
                i.I.setUnsupported();
            else {
                Za = 2;
                Eb = 1
            }
        else if (c = a.match(/opera\/([0-9]+)\.([0-9]+)/i)) {
            Wa = parseInt(c[1], 10);
            vb = parseInt(c[2], 10);
            if (Wa < 9 || Wa == 9 && vb <= 2)
                i.I.setUnsupported('Yamli is not supported on Opera version earlier than 9.5.<br />Please upgrade to the <a href="http://www.opera.com/download/">latest Opera</a>, or use <a href="http://getfirefox.com/">Firefox</a>.');
            else {
                Za = 4;
                Pb = 1
            }
        }
    };
    i.DomReady = function() {
        var a = false, c = [], b, q = function() {
            if (!a) {
                a = true;
                for (var v = 0; v < c.length; ++v)
                    c[v]();
                c = void 0
            }
        };
        this.addCallback = function(v) {
            a ? v() : c.push(v)
        };
        this.unload = function() {
            if (da) {
                if (b) {
                    clearInterval(b);
                    b = void 0
                }
            } else
                i.I.removeEvent(document, "DOMContentLoaded", q)
        };
        (function() {
            if (da)
                b = Xb(function() {
                    var v = G("p");
                    try {
                        v.doScroll("left")
                    } catch (C) {
                        return
                    }
                    clearInterval(b);
                    b = void 0;
                    q()
                }, 50);
            else
                i.I.addEvent(document, "DOMContentLoaded", q)
        })()
    };
    var bc = function(a, c, b) {
        b = a.slice((b ||
                c) + 1 || a.length);
        a.length = c < 0 ? a.length + c : c;
        return a.push.apply(a, b)
    }, mc = function(a, c) {
        var b;
        for (b in c)
            if (c.hasOwnProperty(b))
                a.style[b] = c[b]
    }, Hc = function(a) {
        try {
            for (; ; ) {
                if (a == ia)
                    return true;
                if (!a.parentNode)
                    if (a.parentWindow && a.parentWindow.frameElement)
                        a = a.parentWindow.frameElement;
                    else if (a.defaultView && a.defaultView.frameElement)
                        a = a.defaultView.frameElement;
                    else 
                        return false;
                a = a.parentNode
            }
        } catch (c) {
            return false
        }
    }, wb = function(a) {
        return a.contentEditable == "true"
    }, cc = function(a) {
        return{on: 1, On: 1}[a.designMode] ||
                wb(a.body)
    };
    i.I.setCookie = function(a, c, b, q, v) {
        var C = new Date;
        C.setDate(C.getDate() + b);
        document.cookie = ha(a) + "=" + ha(c) + (";expires=" + C.toGMTString()) + (";path=" + (v ? v : "")) + (q ? ";domain=" + q : "")
    };
    i.I.clearCookie = function(a, c, b) {
        i.I.setCookie(a, "", -1, c, b)
    };
    i.I.getCookie = function(a) {
        if (document.cookie.length > 0) {
            var c = ha(a);
            a = document.cookie.indexOf(c + "=");
            if (a != -1) {
                a = a + c.length + 1;
                c = document.cookie.indexOf(";", a);
                if (c == -1)
                    c = document.cookie.length;
                return decodeURIComponent(document.cookie.substring(a, c))
            }
        }
    };
    var nc = function(a) {
        var c = void 0;
        if (typeof a == "string") {
            c = document.getElementById(a);
            if (!c) {
                a = document.getElementsByName(a);
                if (a.length == 1)
                    c = a[0]
            }
        }
        return c
    }, oc = function(a, c) {
        for (; a; ) {
            if (a == c)
                return true;
            a = a.parentNode
        }
        return false
    }, Ic = function() {
        var a = [], c = location.hash.substr(1);
        if (c.length === 0)
            return a;
        c = c.replace(/\+/g, " ");
        c = c.split("&");
        var b;
        for (b = 0; b < c.length; ++b) {
            var q = c[b].split("="), v = decodeURIComponent(q[0]), C = v;
            if (q.length == 2)
                C = decodeURIComponent(q[1]);
            a[v] = C
        }
        return a
    }, pc = function() {
        if (typeof window.pageYOffset ==
                "number")
            return window.pageXOffset;
        else if (ia && (ia.scrollLeft || ia.scrollTop))
            return ia.scrollLeft;
        return Ya.scrollLeft
    }, qc = function(a) {
        var c = G("style");
        c.type = "text/css";
        if (c.styleSheet)
            c.styleSheet.cssText = a;
        else {
            a = document.createTextNode(a);
            c.appendChild(a)
        }
        document.getElementsByTagName("head")[0].appendChild(c);
        return c
    }, rc = function(a) {
        var c = document.getElementsByTagName("head")[0];
        a.parentNode == c && c.removeChild(a)
    }, sc = {A: 1, ABBR: 1, ACRONYM: 1, B: 1, BASEFONT: 1, BDO: 1, BIG: 1, EM: 1, FONT: 1, I: 1, S: 1, SMALL: 1,
        SPAN: 1, STRIKE: 1, STRONG: 1, TT: 1, U: 1}, dc = function(a) {
        var c, b, q, v, C;
        try {
            c = a.ownerDocument.defaultView
        } catch (K) {
            return
        }
        if (c) {
            c = c.getSelection();
            if (!c || !c.rangeCount)
                return;
            c = c.getRangeAt(0);
            if (wb(a) && !oc(c.commonAncestorContainer, a))
                return;
            b = c.startContainer;
            b = b.nodeType == 3 || b.childNodes.length == c.startOffset ? b : c.startContainer.childNodes[c.startOffset]
        } else if (a.ownerDocument.body.createTextRange) {
            if (a.tagName == "BODY")
                c = document.activeElement == a.ownerDocument.parentWindow.frameElement ? a.document.selection.createRange() :
                        a.createTextRange();
            else {
                if (document.activeElement != a)
                    return;
                c = a.document.selection.createRange()
            }
            if (!c.parentElement)
                return;
            b = c.parentElement();
            if (b == a) {
                c.moveStart("character", -10000000);
                c.moveEnd("character", 1E3);
                if (c.htmlText.length === 0)
                    if (a.firstChild)
                        b = a.firstChild
            }
        } else
            return;
        for (; b; ) {
            if (b == a)
                break;
            if ({DIV: 1, P: 1, BLOCKQUOTE: 1, UL: 1, OL: 1}[b.tagName]) {
                q = b;
                if (q.style.direction || q.dir)
                    break
            }
            b = b.parentNode
        }
        if (q)
            if (q.style.direction) {
                v = q.style.direction;
                C = "style"
            } else {
                v = q.dir;
                C = "dir"
            }
        return{block: q,
            dir: v, attr: C}
    }, tc = function(a) {
        if (a.nextSibling)
            return sc[a.nodeName] === void 0 ? 1 : 0;
        return 0
    }, uc = function(a) {
        if (a.nodeType === 3)
            return a.data;
        if (a.nodeType !== 1 || Yb[a.nodeName])
            return"";
        for (var c = "", b = 0; b < a.childNodes.length; ++b)
            c += uc(a.childNodes[b]);
        c += a.nextSibling ? sc[a.nodeName] === void 0 ? "\r" : "" : "";
        return c
    }, Qb = function(a, c) {
        if (c.nodeType === 3)
            return c.data.length;
        if (c.nodeType !== 1 || Yb[c.nodeName])
            return 0;
        for (var b = 0, q = 0; q < c.childNodes.length; ++q)
            b += Qb(a, c.childNodes[q]);
        b += c == a ? 0 : tc(c);
        return b
    },
            Rb = function(a, c) {
        var b = 0, q = c;
        if (q == a)
            return 0;
        for (; q.previousSibling; ) {
            q = q.previousSibling;
            if (q == a)
                break;
            b += Qb(a, q)
        }
        return b + (q == a ? 0 : Rb(a, q.parentNode))
    }, vc = function(a, c, b) {
        if (c.nodeType === 3) {
            if (b.remaining <= c.data.length) {
                b.node = c;
                b.offset = b.remaining
            }
            b.remaining -= c.data.length
        } else if (c.nodeType !== 1 || Yb[c.nodeName])
            return false;
        else {
            if (!b.remaining && (!b.node || b.node.nodeType !== 3))
                if (c == a) {
                    b.node = a;
                    for (b.offset = 0; b.node.firstChild && b.node.firstChild.tagName != "BR"; )
                        b.node = b.node.firstChild
                } else {
                    b.node =
                            c.parentNode;
                    b.offset = 0;
                    for (var q = c; q = q.previousSibling; )
                        ++b.offset
                }
            for (q = 0; q < c.childNodes.length; ++q)
                if (vc(a, c.childNodes[q], b))
                    return true;
            a = tc(c);
            b.remaining -= a;
            if (!b.remaining && a) {
                b.node = c;
                b.offset = c.childNodes.length
            }
        }
        return b.remaining < 0
    }, Fb = function(a, c) {
        var b = {node: void 0, offset: void 0, remaining: c};
        vc(a, a, b);
        if (!b.node) {
            b.node = a;
            b.offset = a.childNodes.length
        }
        return b
    }, wc = function(a, c, b) {
        var q = Fb(a, b), v;
        a = a.ownerDocument.createElement("span");
        var C = v = false;
        b = q.offset;
        if (q.node.nodeType == 3) {
            q =
                    q.node.splitText(b);
            C = true
        } else if (b)
            for (q = q.node.firstChild; b; ) {
                if (q.nextSibling)
                    q = q.nextSibling;
                else {
                    i.assert(b == 1, "i_rteGetNodeOffsetDims - using split after, offset should be 1, instead: " + b);
                    v = true
                }
                --b
            }
        else
            q = q.node;
        b = q.parentNode;
        a.innerHTML = "\u200a";
        v ? b.appendChild(a) : b.insertBefore(a, q);
        v = i.I.getDimensions(a);
        v.left -= c.left;
        v.top -= c.top;
        v.bottom -= c.bottom;
        v.right -= c.right;
        b.removeChild(a);
        C && b.normalize();
        return v
    }, xc = function(a, c, b) {
        if (c.nodeType == 3)
            return b + Rb(a, c);
        if (c.childNodes.length ==
                b)
            return Qb(a, c) + Rb(a, c);
        return Rb(a, c.childNodes[b])
    }, xb = function(a) {
        if (a.createTextRange)
            return a.createTextRange();
        var c = a.ownerDocument.body.createTextRange();
        c.moveToElementText(a);
        return c
    }, ca = function(a, c) {
        var b;
        if (a.tagName == "BODY" || wb(a)) {
            if (da) {
                b = [];
                var q = xb(a), v = q.duplicate(), C = q.text.indexOf("\r") || 1, K;
                if (C == -1)
                    C = q.text.length;
                for (q.collapse(); ; ) {
                    K = q.moveEnd("character", C);
                    if (!K)
                        break;
                    if (K == q.text.length)
                        b.push(q.text);
                    else if (C == 1)
                        b.push(" ");
                    else {
                        q.collapse();
                        K = 0;
                        C = Math.floor(C / 2)
                    }
                    if (K) {
                        q.moveStart("character",
                                K);
                        q.setEndPoint("EndToEnd", v);
                        C = q.text.indexOf("\r") || 1;
                        if (C == -1)
                            C = q.text.length;
                        q.collapse()
                    }
                }
                return b.join("")
            }
            b = c ? a.textContent === void 0 ? a.innerText : a.textContent : uc(a)
        } else
            b = a.value;
        return b
    }, Sb = function(a) {
        for (var c = /\r/, b = a.length; c.test(a.charAt(--b)); )
            ;
        return a.slice(0, b + 1)
    }, Kc = function(a) {
        if (a.match(Jc)) {
            for (var c = "", b, q = 0; q < a.length; ++q) {
                b = a.charCodeAt(q);
                if (b > 1548)
                    b -= 1500;
                c += String.fromCharCode(b)
            }
            return"s" + ha(c)
        } else
            return"u" + ha(a)
    };
    i.I.trimString = function(a) {
        a = a.replace(/^\s\s*/, "");
        for (var c = /\s/, b = a.length; c.test(a.charAt(--b)); )
            ;
        return a.slice(0, b + 1)
    };
    i.I.addEvent = function(a, c, b, q) {
        if (a.addEventListener) {
            if (a.tagName == "BODY")
                a = a.ownerDocument;
            a.addEventListener(c, b, q ? true : false);
            return true
        } else if (a.attachEvent)
            return a.attachEvent("on" + c, b)
    };
    i.I.removeEvent = function(a, c, b, q) {
        try {
            if (a.removeEventListener) {
                if (a.tagName == "BODY")
                    a = a.ownerDocument;
                a.removeEventListener(c, b, q ? true : false);
                return true
            } else if (a.detachEvent)
                return a.detachEvent("on" + c, b)
        } catch (v) {
            return true
        }
    };
    var xa =
            function(a) {
                if (a.preventDefault) {
                    a.preventDefault();
                    a.stopPropagation && a.stopPropagation()
                } else if (window.event)
                    window.event.returnValue = false;
                else
                    return false
            }, ec = function(a, c) {
        var b;
        if (a.fireEvent)
            if (a.tagName == "BODY" && c == "change")
                a.onchange && a.onchange();
            else {
                b = a.ownerDocument.createEventObject();
                a.fireEvent("on" + c, b)
            }
        else if (a.dispatchEvent) {
            b = (b = {change: "HTMLEvents"}[c]) ? b : "Events";
            b = a.ownerDocument.createEvent(b);
            b.initEvent(c, true, true);
            a.dispatchEvent(b)
        }
    }, yc = function(a) {
        a = a ? a : window.event;
        a = a.charCode ? a.charCode : a.keyCode ? a.keyCode : a.which ? a.which : 0;
        if (ub)
            switch (a) {
                case 63232:
                    return 38;
                case 63233:
                    return 40;
                case 63234:
                    return 37;
                case 63235:
                    return 39
            }
        return a
    }, yb = function(a) {
        var c = {x: 0, y: 0};
        if (typeof a.pageX != "undefined") {
            c.x = a.pageX;
            c.y = a.pageY
        } else if (typeof a.clientX != "undefined") {
            c.x = a.clientX + Ya.scrollLeft || ia.scrollLeft || 0;
            c.y = a.clientY + Ya.scrollTop || ia.scrollTop || 0
        }
        return c
    }, zc = function(a) {
        if (!Hc(a))
            return false;
        if (!a.offsetWidth)
            return false;
        a = ab(a);
        if (!a)
            return false;
        return a
    };
    i.I.WordType = {EMPTY: 0, PURE_ROMAN: 1, PURE_NUMBER: 2, PURE_ARABIC: 3, ROMAN_ARABIC: 4, EXCLUDED: 5, MIXED: 6};
    var Ac = {" ": " ", "\u00a0": "\u00a0", ",": "\u060c", ";": "\u061b", "?": "\u061f", "\u060c": "\u060c", "\u061b": "\u061b", "\u061f": "\u061f", ".": ".", '"': '"', "(": "(", ")": ")", "{": "{", "}": "}", "[": "[", "]": "]", "!": "!", $: "$", "&": "&", "*": "*", "+": "+", "-": "-", "/": "/", ":": ":", "<": "<", ">": ">", "@": "@", "~": "~", "`": "`", "^": "^", "=": "=", "\\": "\\", "\r": "\r", "\n": "\n"}, Tb = {"0": "\u0660", "1": "\u0661", "2": "\u0662", "3": "\u0663", "4": "\u0664",
        "5": "\u0665", "6": "\u0666", "7": "\u0667", "8": "\u0668", "9": "\u0669", "%": "\u066a", ".": "\u066b", ",": "\u066c"}, Bc = /^['0-9a-zA-Z\xc0-\xd6\xd8-\xf6\xf8-\xff]*$/, Lc = /^(([0-9,]*)|([0-9]*\.[0-9]+))%?$/, Mc = RegExp("^['0-9a-zA-Z\\xc0-\\xd6\\xd8-\\xf6\\xf8-\\xff\\u0600-\\u06ff]*$"), Jc = RegExp("^[\\x20-\\x2a\\u060c-\\u065a]*$"), Cc = RegExp("^(AND|OR)$|^http:|^ftp:|@|^www.|^site:|^cache:|^link:|^related:|^info:|^stocks:|^allintitle:|^intitle:|^allinurl:|^inurl:"), Nc = RegExp("^(al|Al|AL|el|El|EL|il|Il|IL|wa|Wa|WA|w|W|be|Be|BE|bi|Bi|BI|b|B|l|L|lel|lil|ll|fa|wal|wel|sa|bel|bil|le|li|fal|fil|fel)$"),
            Oc = /^\s$/, Pc = /\s/, Qc = /(<[^<>]*)$|(\[[^\[\]]*)$/, Rc = / /;
    i.I.HasPunctuationRegexp = RegExp('[ \\r\\n,;\\.\\"\\?\\(\\){}\\[\\]!\\$&\\*\\+\\-/:<>@~`\\^=\\\\\\u060c\\u061b\\u061f\\u06d4\\xa0]');
    i.I.PureArabicRegexp = RegExp("^[\\u0600-\\u06ff]*$");
    var zb = function(a) {
        if (a.length === 0)
            return i.I.WordType.EMPTY;
        if (a.match(Cc))
            return i.I.WordType.EXCLUDED;
        if (a.match(Lc))
            return i.I.WordType.PURE_NUMBER;
        if (a.match(i.I.PureArabicRegexp))
            return i.I.WordType.PURE_ARABIC;
        if (a.match(Bc))
            return i.I.WordType.PURE_ROMAN;
        if (a.match(Mc))
            return i.I.WordType.ROMAN_ARABIC;
        return i.I.WordType.MIXED
    };
    i.I.loadScript = function(a, c, b, q) {
        var v = G("script");
        v.type = "text/javascript";
        v.charset = "utf-8";
        if (c)
            if (da)
                v.text = a;
            else
                v.appendChild(document.createTextNode(a));
        else
            v.src = a;
        var C = function() {
            v.onload = null;
            b || v.parentNode.removeChild(v);
            v = void 0;
            q && q()
        };
        switch (Za) {
            case 2:
            case 3:
                v.onload = C;
                break;
            default:
                v.onreadystatechange = function() {
                    if (v.readyState == "loaded" || v.readyState == "complete") {
                        v.onreadystatechange = null;
                        C()
                    }
                };
                break
        }
        document.getElementsByTagName("head")[0].appendChild(v)
    };
    i.I.SXHRData = {_pendingRequests: {}, _counter: 1, reserveId: function() {
            return this._counter++
        }, start: function(a, c, b) {
            if (b === void 0)
                b = this._counter++;
            this._pendingRequests[b] = a;
            i.I.loadScript(c.replace("%ID%", b))
        }, dataCallback: function(a) {
            if (!(!a || !a.hasOwnProperty("id") || !a.hasOwnProperty("data"))) {
                var c = this._pendingRequests[a.id];
                delete this._pendingRequests[a.id];
                c._responseCallback(a.data)
            }
        }, unload: function() {
            for (var a = 0; a < this._pendingRequests.length; ++a)
                this._pendingRequests[a].unload();
            this._pendingRequests =
                    void 0
        }};
    i.I.SXHR = function() {
        var a, c = "&sxhr_id=%ID%", b;
        this.reserveId = function() {
            return b = i.I.SXHRData.reserveId()
        };
        this.open = function(q, v) {
            a = q;
            if (v === false)
                c = "";
            else if (v)
                c = v
        };
        this.onreadystatechange = function() {
        };
        this.send = function() {
            i.I.SXHRData.start(this, a + c, b)
        };
        this.status = this.readyState = 0;
        this.responseText = void 0;
        this._responseCallback = function(q) {
            this.responseText = q;
            this.status = 200;
            this.readyState = 4;
            this.onreadystatechange()
        };
        this.unload = function() {
            this._responseCallback = function() {
            };
            this.onreadystatechange =
                    void 0
        }
    };
    i.I.createSXHR = function() {
        return new i.I.SXHR
    };
    i.I.createXHR = function() {
        if (typeof XMLHttpRequest != "undefined")
            return new XMLHttpRequest;
        else if (typeof window.ActiveXObject != "undefined")
            return new window.ActiveXObject("Microsoft.XMLHTTP");
        else
            throw Error("XMLHttpRequest not supported");
    };
    i.I.getDimensions = function(a) {
        var c = 0, b = 0, q = a.offsetWidth, v = a.offsetHeight, C = 0, K = a.ownerDocument, D = K.body, B = K.documentElement, P;
        if (a.getBoundingClientRect) {
            C = B.clientLeft || D.clientLeft || 0;
            b = B.clientTop ||
                    D.clientTop || 0;
            var x = B.scrollTop || D.scrollTop || 0, J = B.scrollLeft || D.scrollLeft || 0, Q = B.clientWidth ? B.scrollWidth : D.scrollWidth, Y = a.tagName == "IFRAME", z = !cc(K) && K.compatMode == "BackCompat", E = a.getBoundingClientRect();
            c = E.left - (Y ? 0 : C) + J;
            b = E.top - (Y ? 0 : b) + x;
            C = (z ? Q : B.scrollWidth) - (E.right - C + J)
        } else if (a.offsetParent) {
            for (J = a; J.offsetParent; ) {
                if (J != ia && J != a) {
                    c -= J.scrollLeft;
                    b -= J.scrollTop
                }
                if (J.tagname === "table" || ab(J).display == "table") {
                    Q = window.getComputedStyle(J, "");
                    c += parseFloat(Q.borderLeftWidth) || 0;
                    b += parseFloat(Q.borderTopWidth) ||
                    0
                }
                c += J.offsetLeft;
                b += J.offsetTop;
                J = J.offsetParent
            }
            if (J !== void 0) {
                c += J.offsetLeft;
                b += J.offsetTop
            }
        } else if (a.x) {
            c += a.x;
            b += a.y
        }
        if (K != Ob && (P = K.parentWindow ? K.parentWindow.frameElement : K.defaultView.frameElement)) {
            P = i.I.getDimensions(P);
            c += P.left;
            b += P.top;
            C += P.right
        }
        if ((da || a.nodeName != "BODY") && Ob != K) {
            c -= D.scrollLeft + B.scrollLeft;
            b -= D.scrollTop + B.scrollTop
        }
        return{left: c, top: b, width: q, height: v, right: C}
    };
    var Dc = function(a, c, b, q, v, C) {
        for (; c > 0 && !a.charAt(c - 1).match(C); )
            c--;
        for (; b < a.length && !a.charAt(b).match(C); )
            b++;
        q = a.substring(c, b);
        if (!(q.length === 0 || q.match(C) || v && a.substr(0, c).match(Qc)))
            return{word: q, wordType: zb(q), start: c, end: b}
    }, rb = function(a, c, b, q) {
        var v = Dc(a, c.start, c.end, b, q, Rc);
        if (v) {
            if (v.wordType == i.I.WordType.PURE_NUMBER || v.wordType == i.I.WordType.EXCLUDED)
                b = true;
            else {
                v = Dc(a, c.start, c.end, b, q, i.I.HasPunctuationRegexp);
                if (!v)
                    return
            }
            a = {word: v.word, start: v.start, end: v.end, selectionOffset: c.start - v.start, selectionStart: c.start, selectionEnd: c.end, isEdge: v.start == c.start || v.end == c.end, wordType: v.wordType};
            b || (a = l.convertDashedWordSelection(a));
            return a
        }
    }, Gb = function(a) {
        var c = {value: 0, unit: ""};
        if (a.length === 0)
            return c;
        var b = a.substr(a.length - 2);
        if (b.charAt(1) == "%")
            b = "%";
        c.unit = b;
        c.value = a.substr(0, a.length - b.length);
        return c
    }, Ec = function(a) {
        return parseFloat(Gb(a).value, 10)
    }, Fc = function(a, c) {
        a.innerHTML = c
    }, ab = function(a) {
        var c = a.ownerDocument.defaultView;
        if (c)
            return c.getComputedStyle(a, void 0);
        if (a.currentStyle)
            return a.currentStyle;
        return document.defaultView.getComputedStyle(a, void 0)
    }, Sc = function() {
        var a =
                0, c = -1, b = function() {
            if (c == -1)
                return 0;
            return(new Date).getTime() - c
        };
        this.start = function() {
            if (c == -1)
                c = (new Date).getTime()
        };
        this.stop = function() {
            a += b();
            c = -1
        };
        this.reset = function() {
            c = -1;
            a = 0
        };
        this.getElapsed = function() {
            return a + b()
        }
    };
    i.I.Draggable = function(a, c, b) {
        var q, v, C, K, D = {IDLE: 0, DRAGGING: 1}, B = D.IDLE, P = b ? "bottom" : "top", x = b ? -1 : 1, J = function(z) {
            var E = z.srcElement || z.target;
            if (E) {
                for (; E && E != a; ) {
                    if (E.className.indexOf("yamliNoDrag") != -1)
                        return;
                    E = E.parentNode
                }
                if ((z.which || z.button) == 1) {
                    E = yb(z);
                    q = E.x;
                    v = E.y;
                    E = l.useRightLayout() ? c.style.right : c.style.left;
                    C = parseInt(Gb(E).value, 10);
                    K = parseInt(Gb(c.style[P]).value, 10);
                    B = D.DRAGGING;
                    i.I.addEvent(document, "mousemove", Q);
                    return xa(z)
                }
            }
        }, Q = function(z) {
            if (B == D.DRAGGING) {
                if ((z.which || z.button) != 1) {
                    Y();
                    return xa(z)
                }
                var E = yb(z);
                if (E.x < 0)
                    E.x = 0;
                if (E.y < 0)
                    E.y = 0;
                var ea = E.x - q;
                E = E.y - v;
                if (l.useRightLayout())
                    c.style.right = C - ea + "px";
                else
                    c.style.left = C + ea + "px";
                c.style[P] = K + x * E + "px";
                return xa(z)
            }
        }, Y = function(z) {
            if (B == D.DRAGGING)
                if (z === void 0 || (z.which || z.button) == 1) {
                    B =
                            D.IDLE;
                    i.I.removeEvent(document, "mousemove", Q);
                    if (z)
                        return xa(z)
                }
        };
        this.unload = function() {
            i.I.removeEvent(a, "mousedown", J);
            i.I.removeEvent(a, "mouseup", Y)
        };
        a.style.cursor = "move";
        c.style.position = "absolute";
        i.I.addEvent(a, "mousedown", J);
        i.I.addEvent(a, "mouseup", Y)
    };
    var Tc = function(a) {
        var c = {x: 0, xr: 0, y: 0}, b = {x: 0, xr: 0, y: 0, useRtlMenu: false}, q, v, C, K, D, B, P, x, J, Q = {x: 0, xr: 0, y: 0}, Y, z, E, ea = {x: 0, xr: 0, y: 0}, O, oa, W, Aa, T, Ea, Ba, Fa, Ha, Ga, fa, pa = {x: 0, xr: 0, y: 0, yr: 0}, ja, Na, ka, va, Oa = [], L = [], Pa = [], bb = [], V, U, w = a, X, Ia, Qa =
                false, Ca, ya, cb = false, Xa = true, Ra, sa, Sa = "yamliapi_inst_ff_" + a.getInput().id, gb, hb = this, ta = function() {
            var h = w.getSettingsMenuFontSize(), m = w.getCloseFontSize(), o;
            ga(Y, "fontSize", w.getSettingsFontSize());
            for (o = 0; o < L.length; ++o)
                ga(L[o], "fontSize", h);
            for (o = 0; o < Pa.length; ++o)
                ga(Pa[o], "fontSize", m)
        }, ga = function(h, m, o) {
            if (h.style[m] != o) {
                if (h.style[m].indexOf("!important") == -1)
                    h.style[m] = o;
                for (var u = 0; u < h.childNodes.length; ++u)
                    h.childNodes[u].nodeType == 1 && ga(h.childNodes[u], m, o)
            }
        }, ua = function(h) {
            if (da && Wa ==
                    6 && document.getElementsByTagName("select").length > 0) {
                var m = G("iframe");
                if (l.useRightLayout()) {
                    m.style.right = "0";
                    m.style.marginRight = "-5px"
                } else {
                    m.style.left = "0";
                    m.style.marginLeft = "-5px"
                }
                m.className = "yamli_iframeselectfix";
                h.appendChild(m)
            }
        }, $a = function(h, m) {
            if (l.useRightLayout())
                h.style.right = m + "px";
            else
                h.style.left = m + "px"
        }, Da = function() {
            for (; P.lastChild; )
                P.removeChild(P.lastChild)
        }, Ab = function() {
            var h = G("div");
            h.className = "yamliapi_clear";
            return h
        }, Ja = function(h) {
            var m = G("span");
            m.style.whiteSpace =
                    "nowrap";
            h = w.lang(h);
            m.innerHTML = h;
            return m
        }, la = function(h) {
            var m = G("span");
            h = w.lang(h);
            m.innerHTML = h;
            return m
        }, za = function(h, m, o, u) {
            var y = G("a");
            y.className = "yamliapi_simpleLink";
            if (da)
                y.href = "javascript:void(0);";
            Fc(y, w.lang(h));
            if (m)
                y.title = w.lang(m);
            if (o)
                i.I.addEvent(y, u ? u : "click", function(H) {
                    o(H);
                    return xa(H)
                });
            i.I.addEvent(y, "mousedown", $);
            return y
        }, Ka = function(h, m, o, u, y, H, M, Z) {
            var F = G("div");
            F.style.textAlign = M == "rtl" ? "right" : "left";
            F.style.padding = "3px";
            F.style.color = o;
            F.style.backgroundColor =
                    u;
            F.itemType = h;
            F.yamliTextColor = o;
            F.yamliHighlightColor = y;
            F.yamliMenuBackgroundColor = u;
            F.yamliMenuHighlightColor = H;
            F.style.cursor = h == 1 ? "default" : "pointer";
            F.style.whiteSpace = "nowrap";
            F.style.fontSize = Math.max(Z ? w.getMenuFontSize() * Z : w.getMenuFontSize(), 9) + "px";
            F.style.direction = M;
            F.innerHTML = m;
            i.I.addEvent(F, "mousedown", $);
            i.I.addEvent(F, "click", aa);
            i.I.addEvent(F, "mouseover", R);
            return F
        }, wa = function(h, m) {
            var o = G("div");
            o.style.whiteSpace = "nowrap";
            o.style.padding = m ? m : "3px";
            o.style.backgroundColor =
                    h ? h : "#f5f9ff";
            i.I.addEvent(o, "mousedown", $);
            return o
        }, ib = function() {
            var h = l.getAdInfo();
            if (!h)
                return G("div");
            var m = wa("#dde4ee");
            if (h.showSponsored) {
                h = G("div");
                h.style.marginBottom = "1px";
                h.style.textAlign = "center";
                h.style.whiteSpace = "nowrap";
                m.appendChild(h);
                var o = w.lang("sponsored");
                h.innerHTML = o;
                Pa.push(h)
            }
            h = G("div");
            m.appendChild(h);
            h.style.textAlign = "center";
            m.ad = h;
            return m
        }, db = function(h) {
            if (l.getShowPowered()) {
                var m = wa("#dde4ee", "0");
                m.style.textAlign = "center";
                m.style.padding = "0 1em";
                m.appendChild(Ja("powered"));
                l.getAdInfo() && Pa.push(m);
                h.appendChild(m)
            } else {
                m = G("div");
                m.style.width = "100px";
                m.style.height = "1px";
                m.style.fontSize = "0px";
                m.style.lineHeight = "0px";
                h.appendChild(m)
            }
        }, lb = function(h, m) {
            if (h.ad) {
                l.isAdLoaded() ? f(h.ad) : bb.push(h.ad);
                h.ad = void 0
            }
            m && l.adViewInc()
        }, f = function(h) {
            var m = l.getAdInfo();
            if (m) {
                var o = G("div"), u = m.width, y = m.height, H;
                if (!da)
                    o.style.margin = "0 auto";
                o.style.width = u + "px";
                o.style.textAlign = "center";
                h.appendChild(o);
                switch (m.adType) {
                    case "image":
                        var M = G("a");
                        o.appendChild(M);
                        M.href =
                                l.makeUrl("/sp_click.ashx?sp_id=" + ha(m.adId) + l.getReferrerInfo("&"), "&");
                        M.target = "_blank";
                        i.I.addEvent(M, "mouseup", function() {
                            var Z = M.href.indexOf("&click_ms=");
                            if (Z != -1)
                                M.href = M.href.substr(0, Z);
                            M.href += "&click_ms=" + l.getAdViewElapsed();
                            l.adClickCountInc()
                        });
                        o = G("img");
                        M.appendChild(o);
                        o.src = m.url;
                        break;
                    case "iframe":
                        H = k(u, y);
                        o.appendChild(H);
                        H.src = m.url;
                        break;
                    case "gam":
                        h = location.hash.indexOf("yamli_googleDebug") != -1 ? "&google_debug" : "";
                        u = w.getOption("tool");
                        H = u.indexOf("_");
                        y = w.getOption("xfPage");
                        if (H > 0)
                            u = u.substr(0, H);
                        u = u.replace(/\./g, "-");
                        y || (y = "");
                        H = k(1, 0, l.getAdIframeId());
                        o.appendChild(H);
                        H.src = m.url + (m.url.indexOf("#") == -1 ? "#" : "&") + "load=1&debugAds=" + encodeURIComponent(l.debugAds) + "&hostname=" + encodeURIComponent(window.location.hostname) + "&port=" + window.location.port + "&tool=" + ha(u) + "&pageLang=" + w.getOption("uiLanguage") + "&xfPage=" + encodeURIComponent(y) + h;
                        break
                    }
            }
        }, j = function() {
            var h = G("div");
            if (da) {
                h.innerHTML = "&nbsp;";
                h.style.fontSize = "0px";
                h.style.lineHeight = "0px"
            } else {
                h.style.width =
                        "100%";
                h.style.height = "1px"
            }
            h.style.borderTop = "solid 1px #e0e0e0";
            h.style.backgroundColor = "#f5f9ff";
            h.style.margin = "0px 0px 0px 0px";
            h.itemType = 2;
            return h
        }, k = function(h, m, o) {
            var u = G("iframe");
            u.style.display = "block";
            u.width = h + "px";
            u.height = m + "px";
            u.frameBorder = "0";
            u.scrolling = "no";
            u.allowTransparency = "true";
            u.src = "about:blank";
            if (o) {
                u.name = o;
                u.id = o
            }
            return u
        }, n = function(h, m, o, u, y) {
            var H = G("div");
            H.style.whiteSpace = "normal";
            if (o)
                H.style.width = o + "px";
            H.style.height = "auto";
            Fc(H, '<div style="margin:1em;text-align:center">' +
                    w.lang("loading") + "</div>");
            var M = i.I.createSXHR();
            h = m ? h : l.makeUrl("/ajax.ashx?path=" + ha(h), "&");
            M.open(h);
            M.onreadystatechange = function() {
                H.innerHTML = M.responseText;
                u && ga(H, "fontSize", u);
                y && y()
            };
            M.send();
            return H
        }, A = function(h) {
            i.I.removeEvent(h.srcElement || h.target, "mousemove", R);
            h = yb(h);
            var m = i.I.getDimensions(P);
            if (h.x < m.left || h.x > m.left + m.width || h.y < m.top || h.y > m.top + m.height)
                a:if (X) {
                    h = X.getSelectedIndex();
                    m = P.firstChild;
                    do {
                        if (m.transliterationIndex === h) {
                            ma(m);
                            break a
                        }
                        m = m.nextSibling
                    } while (m)
                }
        },
                R = function(h) {
            h = h.srcElement || h.target;
            i.I.removeEvent(h, "mousemove", R);
            if (l.didMouseMove()) {
                for (; h.itemType === void 0; )
                    h = h.parentNode;
                ma(h)
            } else
                i.I.addEvent(h, "mousemove", R)
        }, $ = function(h) {
            cb = false;
            if (da) {
                var m = w.getInput();
                cb = m == m.document.activeElement
            }
            return xa(h)
        }, aa = function(h) {
            for (h = h.srcElement || h.target; h.itemType === void 0; )
                h = h.parentNode;
            switch (parseInt(h.itemType, 10)) {
                case 4:
                    eb();
                    break;
                case 5:
                    Hb();
                    break;
                case 3:
                    Ta(h.transliterationIndex, true);
                    La();
                    break;
                case 6:
                    sb(true);
                    Ua();
                    break;
                case 7:
                    sb(false);
                    Ua();
                    break
                }
        }, La = function() {
            if (mb()) {
                D.style.display = "none";
                Ib();
                Qa && l.reportTransliterationSelection(X, U.start, ca(w.getInput()), false) && w.setWasUsed();
                l.adViewDec()
            }
        }, mb = function() {
            return D.style.display != "none"
        }, Ta = function(h, m) {
            if (w.validateWordSelection(U)) {
                X.setSelectedIndex(h);
                U.wordType == i.I.WordType.PURE_NUMBER && !h != l.readGlobalPref("useRomanNum") && l.saveGlobalPref("useRomanNum", !h);
                var o = X.getSelectedTransliteration();
                h !== 0 && l.registerArabicToRoman(o, Ia);
                var u = w.getInput();
                l.registerDefaultPick(m &&
                        h == 1 && U.selectionEnd == Sb(ca(u)).length);
                w.replaceInputValue(o, U.start, U.end, true, true);
                o = U.start + 1;
                U = rb(ca(u), {start: o, end: o}, false, w.getOption("disableInMarkup"));
                Qa = true
            }
        }, ma = function(h) {
            if (V != h) {
                if (V !== void 0) {
                    V.style.backgroundColor = V.yamliMenuBackgroundColor;
                    V.style.color = V.yamliTextColor;
                    if (V.yamliChildBackground)
                        V.yamliChildBackground.style.backgroundColor = V.style.backgroundColor
                }
                V = h;
                if (V !== void 0) {
                    V.style.backgroundColor = V.yamliMenuHighlightColor;
                    V.style.color = V.yamliHighlightColor;
                    if (V.yamliChildBackground)
                        V.yamliChildBackground.style.backgroundColor =
                                V.style.backgroundColor
                }
            }
        }, eb = function() {
            for (var h, m, o = 1; o < P.childNodes.length; ++o) {
                h = P.childNodes[o];
                if (parseInt(h.itemType, 10) == 3 && h.style.display == "none") {
                    if (m === void 0) {
                        m = h;
                        Ta(m.transliterationIndex, false)
                    }
                    h.style.display = "block"
                }
            }
            Ca.style.display = "none";
            if (ya)
                ya.style.display = "block";
            return m
        }, Hb = function() {
            Ta(0, false);
            Qa = false;
            Da();
            var h = "/not_found." + w.getOption("uiLanguage") + ".htm";
            P.appendChild(n(l.makeUrl("/not_found.ashx?path=" + ha(h) + "&word=" + ha(Ia), "&"), true, w.adjustAjaxWidth(160), w.getSettingsMenuFontSize()));
            Xa = false
        }, Bb = function() {
            if (w.getEnabled()) {
                Ba.style.display = "none";
                Fa.style.display = "block";
                var h = w.lang("quick_toggle_on");
                E.innerHTML = h;
                E.title = w.lang("quick_toggle_on_hint");
                if (T)
                    T.style.backgroundPosition = "left 0px"
            } else {
                Fa.style.display = "none";
                Ba.style.display = "block";
                h = w.lang("quick_toggle_off");
                E.innerHTML = h;
                E.title = w.lang("quick_toggle_off_hint");
                if (T)
                    T.style.backgroundPosition = "left -" + Ea + "px"
            }
            if (Ga)
                if (w.getDirection() == "rtl") {
                    Ha.style.display = "none";
                    Ga.style.display = "block"
                } else {
                    Ga.style.display =
                            "none";
                    Ha.style.display = "block"
                }
            Y.style.display = w.getOption("settingsPlacement") == "inside" ? "none" : ""
        }, Ua = function() {
            if (O.style.display != "none") {
                O.style.display = "none";
                l.adViewDec()
            }
        }, jb = function(h) {
            La();
            pb(true);
            if (O.style.display == "block")
                Ua();
            else {
                O.style.display = "block";
                tb();
                lb(Aa, true)
            }
            xa(h);
            w.focusInput()
        }, tb = function() {
            if (O.style.display == "block") {
                $a(O, nb(O, ea.x, ea.xr, false));
                O.style[v] = C * ea.y + "px"
            }
        }, fc = function() {
            w.focusInput();
            pb(true);
            sb(!w.getEnabled());
            Ua()
        }, Jb = function() {
            ma(void 0)
        }, Ub =
                function() {
                    Ua()
                }, qb = function() {
            sb(!w.getEnabled());
            Ua();
            w.focusInput()
        }, Va = function() {
            w.toggleDir();
            Ua();
            w.focusInput()
        }, Db = function() {
            if (fa.style.display == "none") {
                if (fa.style.display != "block") {
                    if (!ja) {
                        var h = w.getSettingsMenuFontSize(), m = w.adjustAjaxWidth(240);
                        ja = G("div");
                        ja.style.width = m + "px";
                        ja.style[v] = 0;
                        fa.appendChild(ja);
                        ja.className = "yamliapi_menuBorder";
                        ua(fa);
                        var o = G("div");
                        o.className = "yamliapi_menuPanel";
                        ja.appendChild(o);
                        m = G("div");
                        m.className = "yamliapi_menuContent";
                        o.appendChild(m);
                        var u =
                                w.lang("dir");
                        o = wa("#e1e4ea");
                        o.style.textAlign = u == "rtl" ? "right" : "left";
                        m.appendChild(o);
                        Na = new i.I.Draggable(o, ja, q);
                        var y = G("div");
                        y.style.position = "absolute";
                        if (u == "ltr")
                            y.style.right = "0";
                        else
                            y.style.left = "0";
                        y.style.top = "0";
                        y.style.padding = "3px";
                        o.appendChild(y);
                        u = za("close", "tips_close_hint", Kb);
                        u.style.verticalAlign = "top";
                        u.className += " yamliNoDrag";
                        y.appendChild(u);
                        y = Ja("tips_title");
                        y.style.fontWeight = "bold";
                        o.appendChild(y);
                        o = j();
                        m.appendChild(o);
                        m.appendChild(n("/tips." + w.getOption("uiLanguage") +
                                ".htm", false, null, h, Cb));
                        L.push(m);
                        Pa.push(u);
                        ta()
                    }
                    fa.style.display = "block";
                    Cb()
                }
            } else
                ob();
            Ua();
            w.focusInput()
        }, Kb = function() {
            ob();
            w.focusInput()
        }, ob = function() {
            if (fa.style.display != "none")
                fa.style.display = "none"
        }, Cb = function() {
            if (fa.style.display == "block") {
                $a(fa, nb(ja, pa.x, pa.xr, false));
                fa.style[v] = C * (l.useRightLayout() ? pa.yr : pa.y) + "px"
            }
        }, Vb = function() {
            pb(true);
            w.getOption("hintMode") == "startModeOff" && l.setInstancesEnabled(true);
            jb()
        }, Wb = function() {
            pb(true);
            l.setInstancesEnabled(false);
            jb()
        }, pb =
                function(h) {
                    ka.style.display = "none";
                    h && l.setShowedHint()
                }, Lb = function() {
            if (ka.style.display == "block") {
                var h = w.getDirection(), m = w.getInputDims(), o = ka.offsetWidth, u = c.x, y = c.xr;
                if (h == "rtl")
                    u += m.width - o;
                else
                    y += m.width - o;
                $a(ka, nb(ka, u, y, w.useRtlMenu()));
                ka.style[v] = w.isTextBox() ? C * (m.top + (q ? 0 : m.height)) + "px" : C * (m.top + (q ? 0 : 1.5 * w.getInputFontSize())) + "px"
            }
        }, gc = function() {
            l.hideDefaultPickHint();
            hb.setTransliterations(X, U)
        }, nb = function(h, m, o, u) {
            if (!h)
                return m;
            i.assert(h.style.display != "none", "Using _avoidEdges with invisible block");
            h = h.offsetWidth + 2;
            var y = ia;
            if (da)
                y = Ya && Ya.clientWidth === 0 ? ia : Ya;
            var H = l.getClientWidth(), M = pc();
            if (l.useRightLayout()) {
                M = o - (y.scrollWidth - M - H);
                y = H - M - h;
                if (u) {
                    y -= h;
                    M += h
                }
                if (y < 0)
                    return o + y;
                if (M < 0)
                    return o - M;
                return o
            } else {
                y = m - M;
                M = H - y - h;
                if (u) {
                    y -= h;
                    M += h
                }
                if (y < 0)
                    return m - y;
                if (M < 0)
                    return m + M;
                return m
            }
        }, sb = function(h) {
            l.getOption("toggleAffectsAll") ? l.setInstancesEnabled(h) : w.setEnabled(h)
        }, Ib = function() {
            if (sa) {
                clearTimeout(sa);
                sa = void 0
            }
        };
        this.show = function() {
            mb() || lb(x, true);
            Ua();
            D.style.display = "block";
            D.style.position =
                    "absolute";
            if (b.useRtlMenu) {
                D.style.right = "0";
                D.style.left = "auto"
            } else {
                D.style.left = "0";
                D.style.right = "auto"
            }
            $a(K, nb(D, c.x + b.x, c.xr + b.xr, b.useRtlMenu));
            K.style[v] = C * (c.y + b.y) + "px"
        };
        this.hide = function(h) {
            La();
            h && Ua()
        };
        this.isMenuVisible = mb;
        this.hideAndSelectCurrent = function(h) {
            var m = V;
            if (U && m)
                switch (parseInt(m.itemType, 10)) {
                    case 4:
                        ma(eb());
                        break;
                    case 5:
                        Hb();
                        break;
                    case 3:
                        Ta(m.transliterationIndex, h);
                        La();
                        break
                    }
        };
        this.preloadAd = function() {
            lb(x, false)
        };
        this.isVisible = function() {
            return D.style.display ==
                    "block"
        };
        this.isVisibleAndNotLoading = function() {
            return D.style.display == "block" && X != void 0
        };
        this.isLoading = function() {
            return X === void 0 && this.isVisible()
        };
        this.processUpKey = function() {
            if (Xa && X) {
                for (var h = V; ; ) {
                    h = h.previousSibling;
                    if (!h)
                        for (h = P.lastChild; !h.itemType; )
                            h = h.previousSibling;
                    var m = parseInt(h.itemType, 10);
                    if (m != 2 && m != 1 && h.style.display != "none")
                        break
                }
                switch (parseInt(h.itemType, 10)) {
                    case 4:
                        ma(h);
                        break;
                    case 5:
                        ma(h);
                        break;
                    case 3:
                        Ta(h.transliterationIndex, false);
                        ma(h);
                        break
                    }
            }
        };
        this.processDownKey =
                function() {
                    if (Xa && U) {
                        for (var h = V; ; ) {
                            h = h.nextSibling;
                            if (!h || !h.itemType)
                                h = P.firstChild;
                            var m = parseInt(h.itemType, 10);
                            if (m != 2 && m != 1 && h.style.display != "none")
                                break
                        }
                        switch (parseInt(h.itemType, 10)) {
                            case 4:
                                ma(h);
                                break;
                            case 5:
                                ma(h);
                                break;
                            case 3:
                                Ta(h.transliterationIndex, false);
                                ma(h);
                                break
                            }
                    }
                };
        this.setTransliterations = function(h, m) {
            X = h;
            U = m;
            Ib();
            var o = w.getOption("maxResults"), u = w.getOption("showReportWord"), y = [], H = -1, M;
            if (!U || !Ia || U.word.substr(0, Ia.length) != Ia)
                Ra = l.getAdInfo() ? 4 : 2;
            Ia = U.word;
            if (X) {
                y = X.getTransliterationsArray();
                H = X.getSelectedIndex();
                Ia = h.getRomanWord()
            }
            Da();
            var Z = y.length, F = Z > 0 ? y[0].type : 0;
            if (l.showDefaultPickHint() && U.selectionEnd == Sb(ca(w.getInput())).length) {
                var kb = w.lang("dir"), d = Ka(1, w.lang("def_pick_hint"), "#333", "#FFFFDF", "#333", "#FFFFDF", kb, 0.9);
                d.style.border = "red solid 2px";
                d.style.margin = "4px";
                d.style.padding = kb == "ltr" ? "3px 6px 3px 22px" : "3px 22px 3px 6px";
                d.style.background = "#FFFFDF url(" + l.makeUrl("/cache_safe/bulb.gif", "?") + ") no-repeat top " + (kb == "ltr" ? "left" : "right");
                var g = G("div");
                g.style.textAlign =
                        kb == "ltr" ? "right" : "left";
                kb = za("close", "", gc);
                kb.style.fontSize = "80%";
                g.appendChild(kb);
                d.appendChild(g);
                P.appendChild(d)
            }
            d = Ia.replace("-", "\u2011");
            d = Ka(X ? 3 : 1, d, "black", "#f5f9ff", X ? "black" : "black", X ? "#c6d8ff" : "#f5f9ff", w.useRtlMenu() ? "rtl" : "ltr");
            d.transliterationIndex = 0;
            d.style.fontStyle = "italic";
            P.appendChild(d);
            if (H === 0)
                M = d;
            if (o && o < Z)
                Z = o;
            for (o = 1; o < Z; ++o) {
                if (o >= 2)
                    break;
                if (y[o].type > F) {
                    o + 1 < Z && ++o;
                    break
                }
            }
            d = o;
            d == Z && d--;
            F = true;
            if (H - 1 > d || d == Z - 1 || !Z)
                F = false;
            for (o = 0; o < Z; ++o) {
                g = Ka(3, y[o].trans, "black",
                        "#f5f9ff", "black", "#c6d8ff", "rtl");
                g.transliterationIndex = o + 1;
                if (F && o > d)
                    g.style.display = "none";
                P.appendChild(g);
                if (H === o + 1)
                    M = g
            }
            Ca = Ka(4, w.lang("show_more"), "#112abb", "#f5f9ff", "0000cc", "#c6d8ff", "ltr", 0.8);
            Ca.style.textAlign = "right";
            Ca.style.display = "none";
            P.appendChild(Ca);
            if (u) {
                ya = Ka(5, w.lang("report_word"), "#112abb", "#f5f9ff", "0000cc", "#c6d8ff", "ltr", 0.8);
                ya.title = w.lang("report_word_hint");
                ya.style.textAlign = "right";
                ya.style.display = "none";
                P.appendChild(ya)
            }
            u = 0;
            if (Z > 0) {
                u = 0;
                if (F) {
                    Ca.style.display =
                            "block";
                    ++u
                } else if (ya) {
                    ya.style.display = "block";
                    ++u
                }
                Ra = Math.max(Ra, Math.min(4, Z + u));
                u = Math.max(0, Ra - Z - 1)
            } else
                u = Ra;
            var e;
            for (o = 0; o < u; ++o) {
                y = o === 0 && Z === 0;
                H = Z === 0 && o == 3;
                F = y ? "#c6d8ff" : "#f5f9ff";
                H = Ka(1, H ? "&nbsp;" : "\u0623", F, F, F, F, "rtl", H ? 0.8 : void 0);
                if (y) {
                    e = H;
                    H.style.textAlign = "right"
                }
                P.appendChild(H)
            }
            if (e)
                sa = setTimeout(function() {
                    var t = e.style.fontSize, r = w.getSettingsButtonSize(), s = r > 18 ? "16x16" : "12x12";
                    r = r > 18 ? 'width="24" height="16"' : 'width="18" height="12"';
                    var p = da ? "\u0623" : "&nbsp;";
                    s = "/cache_safe/spinner3_" +
                            s + ".gif";
                    t = '<span style="vertical-align: middle;font-size:' + t + ';color:#c6d8ff;white-space:pre">' + p + '<img style="visibility:hidden" ' + r + ' src="' + l.makeUrl(s, "?") + '" onload="this.style.visibility=\'visible\'" /></span>';
                    e.innerHTML = t
                }, 400);
            ma(M);
            Qa = false;
            Xa = true
        };
        this.updateFontSizes = ta;
        this.setMenuOffset = function(h, m, o, u) {
            b.x = h;
            b.xr = o;
            b.y = m;
            b.useRtlMenu = u
        };
        this.setMainOffset = function(h, m, o) {
            c.x = h;
            c.y = m;
            c.xr = o
        };
        this.clearTestDivs = function() {
            for (var h = 0; h < Oa.length; ++h)
                l.removeChild(Oa[h]);
            Oa = []
        };
        this.setTestDiv =
                function(h, m) {
                    var o = i.I.getDimensions(h), u = G("div");
                    Oa.push(u);
                    u.style.display = "none";
                    u.style.position = "absolute";
                    u.style.border = "1px dashed red";
                    u.style.padding = "0";
                    u.style.margin = "0";
                    u.style.zIndex = l.getOption("zIndexBase") + 10;
                    u.innerHTML = m;
                    l.appendChild(u);
                    u.style.display = "block";
                    $a(u, l.useRightLayout() ? o.right - 1 : o.left - 1);
                    u.style.top = o.top - 1 + "px";
                    u.style.width = o.width + "px";
                    u.style.height = o.height + "px"
                };
        this.setYamliSettingsPosition = function(h, m, o) {
            Q.x = c.x + h;
            Q.xr = c.xr + o;
            Q.y = c.y + m;
            $a(J, l.useRightLayout() ?
                    Q.xr : Q.x);
            J.style.top = Q.y + "px"
        };
        this.hideYamliSettings = function() {
            J.style.visibility = "hidden"
        };
        this.showYamliSettings = function() {
            J.style.visibility = "visible"
        };
        this.setYamliSettingsMenuPosition = function(h, m, o) {
            ea.x = Q.x + h;
            ea.xr = Q.xr + o;
            ea.y = Q.y + m;
            tb();
            Lb()
        };
        this.setYamliTipsPosition = function(h, m, o, u) {
            pa.x = c.x + h;
            pa.xr = c.xr + o;
            pa.y = c.y + m;
            pa.yr = c.y + u;
            Cb()
        };
        this.getYamliSettingsDims = function() {
            return i.I.getDimensions(J)
        };
        this.hasSelectionChanged = function() {
            return Qa
        };
        this.checkCancelBlur = function() {
            var h =
                    cb;
            cb = false;
            return h
        };
        this.getWordSelection = function() {
            return U
        };
        this.hideSettings = Ua;
        this.resetSettingsUI = Bb;
        this.activateAds = function() {
            for (var h in bb)
                bb.hasOwnProperty(h) && f(bb[h]);
            bb = void 0
        };
        this.showHint = function() {
            if (!va) {
                var h = w.lang("dir"), m = h == "rtl";
                va = G("div");
                va.style.width = "210px";
                ka.appendChild(va);
                va.style.border = "#ddd solid 1px";
                va.style.margin = "4px";
                va.style.padding = h == "ltr" ? "3px 6px 3px 22px" : "3px 22px 3px 6px";
                va.style.background = "#FFFFDF url(" + l.makeUrl("/cache_safe/bulb.gif",
                        "?") + ") no-repeat top " + (m ? "right" : "left");
                ua(va);
                va[v] = 0;
                var o = G("div");
                va.appendChild(o);
                o.appendChild(la("hint_content_start"));
                var u = G("div"), y = l.makeUrl("/cache_safe/marhaban_movie_small.gif", "?");
                u.style.padding = "10px 6px 0 6px";
                var H = m ? "left" : "right";
                if (da)
                    u.style.styleFloat = H;
                else
                    u.style.cssFloat = H;
                u.innerHTML = '<img style="border: 1px solid #888;height:60px" src="' + y + '" />';
                o.appendChild(u);
                y = G("div");
                y.style.paddingTop = "0.5em";
                u = za("hint_content_try", "", Vb);
                H = za("hint_content_notry", "",
                        Wb);
                y.appendChild(u);
                y.appendChild(H);
                o.appendChild(y);
                o.appendChild(Ab());
                y = G("div");
                H = w.lang("hint_content_end");
                var M = l.makeUrl("/cache_safe/logo_y_14.gif", "?"), Z = l.makeUrl("/cache_safe/transparent_pixel.gif", "?");
                y.style.padding = "0.3em 0";
                H = H.replace("[Y]", '<img src="' + Z + '" style="width:14px;height:14px;background:url(\'' + M + "') no-repeat;background-position:left 0px\" />");
                y.innerHTML = H;
                o.appendChild(y);
                ga(o, "direction", h);
                ga(o, "textAlign", m ? "right" : "left");
                ga(o, "fontSize", "11px");
                ga(u, "fontSize",
                        "15px");
                ga(u, "padding", "0.4em 0");
                ga(u, "display", "block")
            }
            ka.style.display = "block";
            Lb()
        };
        this.hideHint = pb;
        this.isEltDescendant = function(h) {
            return oc(h, K)
        };
        this.unload = function() {
            Ib();
            i.I.removeEvent(P, "mouseout", A);
            i.I.removeEvent(W, "mouseout", Jb);
            l.removeChild(K);
            K = D = B = P = x = x.ad = Ca = ya = void 0;
            i.I.removeEvent(z, "dblclick", qb);
            l.removeChild(J);
            z = J = Y = Aa = Aa.ad = E = void 0;
            l.removeChild(O);
            O = oa = W = T = Ba = Fa = Ha = Ga = void 0;
            Na && Na.unload();
            l.removeChild(fa);
            fa = ja = Pa = L = Na = void 0;
            l.removeChild(ka);
            ka = va = void 0;
            V = U =
                    w = X = Ia = void 0;
            rc(gb);
            gb = void 0
        };
        (function() {
            var h = l.getOption("zIndexBase");
            if (q = a.getOption("popupDirection") == "up") {
                v = "bottom";
                C = -1
            } else {
                v = "top";
                C = 1
            }
            J = G("div");
            J.className = "yamliapi_settingsDiv";
            mc(J, {position: "absolute", visibility: "hidden"});
            l.appendChild(J);
            Ea = w.getSettingsButtonSize();
            var m = Ea + "px";
            J.style.paddingLeft = m;
            z = za("", "settings_link_hint", jb);
            i.I.addEvent(z, "dblclick", qb);
            J.appendChild(z);
            if (Ea) {
                var o = l.makeUrl("/cache_safe/logo_y_" + Ea + (jc ? ".gif" : ".png"), "?");
                T = G("div");
                mc(T, {height: m,
                    width: m, display: "block", position: "absolute", background: "url('" + o + "') no-repeat", backgroundPosition: "left 0px", left: 0, top: w.getSettingsButtonOffset() + 2 + "px"});
                z.appendChild(T)
            }
            Y = G("span");
            J.appendChild(Y);
            Y.appendChild(Ja("&nbsp;"));
            m = za("", "settings_link_hint", jb);
            Y.appendChild(m);
            m.appendChild(Ja("settings_link"));
            Y.appendChild(Ja("&nbsp;("));
            E = za("", "", fc);
            Y.appendChild(E);
            Y.appendChild(Ja(")"));
            ga(Y, "fontSize", w.getSettingsFontSize());
            ga(Y, "color", w.getOption("settingsColor"));
            ga(E, "color", w.getOption("settingsLinkColor"));
            ga(m, "color", w.getOption("settingsLinkColor"));
            O = G("div");
            O.className = "yamliapi_menuBorder";
            O.style.position = "absolute";
            O.style.zIndex = h + 5;
            O.style.display = "none";
            l.appendChild(O);
            ua(O);
            oa = G("div");
            oa.className = "yamliapi_menuPanel";
            O.appendChild(oa);
            W = G("div");
            W.className = "yamliapi_menuContent";
            oa.appendChild(W);
            o = w.lang("dir");
            var u = wa("#edf1f7");
            u.style.textAlign = o == "rtl" ? "right" : "left";
            W.appendChild(u);
            var y = G("div");
            y.style.position = "absolute";
            if (o == "ltr")
                y.style.right = "0";
            else
                y.style.left = "0";
            y.style.top = "0";
            y.style.padding = "3px";
            m = za("close", "settings_close_hint", Ub);
            m.style.verticalAlign = "top";
            y.appendChild(m);
            u.appendChild(y);
            y = Ja("close");
            y.style.visibility = "hidden";
            o == "rtl" && u.appendChild(y);
            var H = Ja("settings_title");
            H.style.fontWeight = "bold";
            u.appendChild(H);
            o == "ltr" && u.appendChild(y);
            o = j();
            W.appendChild(o);
            Ba = wa();
            W.appendChild(Ba);
            Ba.appendChild(za("enable_link", "enable_link_hint", qb));
            Fa = wa();
            W.appendChild(Fa);
            Fa.appendChild(za("disable_link", "disable_link_hint", qb));
            if (w.getOption("showDirectionLink")) {
                Ga =
                        wa();
                W.appendChild(Ga);
                Ga.appendChild(za("align_left_link", "align_left_link_hint", Va));
                Ha = wa();
                W.appendChild(Ha);
                Ha.appendChild(za("align_right_link", "align_right_link_hint", Va))
            }
            o = j();
            W.appendChild(o);
            o = wa();
            o.appendChild(za("tips_link", "tips_link_hint", Db));
            W.appendChild(o);
            if (w.getOption("showTutorialLink")) {
                o = wa();
                u = w.getOption("tutorialUrl");
                if (u === void 0) {
                    u = "http://www.yamli.com/help/";
                    y = w.getOption("uiLanguage");
                    if (y != "en")
                        u += y + "/";
                    u += "?autolang=false"
                }
                u = u;
                y = G("a");
                y.className = "yamliapi_anchor";
                H = w.lang("help_link");
                y.innerHTML = H;
                y.title = w.lang("help_link_hint");
                y.href = u;
                y.target = "yamli_win";
                o.appendChild(y);
                W.appendChild(o)
            }
            L.push(W);
            Pa.push(m);
            Aa = ib();
            W.appendChild(Aa);
            db(W);
            fa = G("div");
            fa.style.position = "absolute";
            fa.style.zIndex = h + 2;
            if (l.useRightLayout())
                fa.style.direction = "rtl";
            fa.style.display = "none";
            l.appendChild(fa);
            ka = G("div");
            ka.style.position = "absolute";
            ka.style.zIndex = h + 4;
            if (l.useRightLayout())
                ka.style.direction = "rtl";
            ka.style.display = "none";
            l.appendChild(ka);
            Bb();
            K = G("div");
            K.style.position = "absolute";
            K.style.zIndex = h + 3;
            l.appendChild(K);
            D = G("div");
            D.className = "yamliapi_menuBorder";
            D.style.display = "none";
            D.style[v] = 0;
            K.appendChild(D);
            ua(D);
            B = G("div");
            B.className = "yamliapi_menuPanel";
            D.appendChild(B);
            P = G("div");
            P.className = "yamliapi_menuContent";
            B.appendChild(P);
            x = ib();
            B.appendChild(x);
            db(B);
            gb = qc("." + Sa + " *{font-family:" + w.getOption("uiFontFamily") + ";}\n");
            J.className += " " + Sa;
            O.className += " " + Sa;
            P.className += " " + Sa;
            fa.className += " " + Sa;
            ta();
            i.I.addEvent(P, "mouseout",
                    A);
            i.I.addEvent(W, "mouseout", Jb)
        })()
    }, Uc = function(a, c) {
        var b = 1;
        this.getTransliterationsArray = function() {
            return c
        };
        this.getRomanWord = function() {
            return a
        };
        this.getSelectedIndex = function() {
            return b
        };
        this.setSelectedIndex = function(q) {
            b = q
        };
        this.setSelectedWord = function(q) {
            for (var v = 0; v < c.length; ++v)
                if (c[v].trans == q) {
                    b = v + 1;
                    return
                }
        };
        this.getTransliterationCount = function() {
            return c.length
        };
        this.getSelectedTransliteration = function() {
            if (c.length !== 0) {
                if (b === 0)
                    return a;
                return c[b - 1].trans
            }
        }
    };
    hc = function(a, c) {
        var b =
                a, q = l.makeInstanceId(), v = false, C = false, K = false, D = false, B = false, P, x, J, Q = [], Y, z, E = {x: 0, y: 0}, ea = false, O = 0, oa = this, W, Aa, T, Ea, Ba, Fa, Ha, Ga, fa, pa, ja, Na, ka, va, Oa, L = {fontFamily: "", fontStyle: "", fontWeight: "", paddingLeft: "", paddingRight: "", paddingTop: "", paddingBottom: "", borderLeftWidth: "", borderRightWidth: "", borderTopWidth: "", borderBottomWidth: "", fontSize: "", direction: ""}, Pa, bb, V, U, w, X, Ia, Qa = 1, Ca = false, ya = false, cb = false, Xa = false, Ra = false, sa = false, Sa = true, gb, hb, ta, ga = -1, ua = b.ownerDocument, $a = ua.parentWindow ?
                ua.parentWindow : ua.defaultView, Da = ua.activeElement == b, Ab, Ja = false, la, za = {}, Ka = false, wa = function(d) {
            if ((Da || d) && F("notifyStateChanges")) {
                d = b;
                if (C)
                    d = b.ownerDocument.defaultView.frameElement;
                ec(d, "yamliStateChange")
            }
        }, ib = function(d) {
            if (sa != d) {
                (sa = d) || z.hide(true);
                db();
                if (B)
                    b.setAttribute("autocomplete", sa ? "off" : Pa);
                R(true);
                wa()
            }
        }, db = function() {
            var d = "", g, e;
            if (!F("overrideDirection"))
                return g;
            if (D) {
                d = b;
                if (F("rteDirectionMode") == "block")
                    d = (e = dc(b)) && e.block ? e.block : b;
                d = d.textContent === void 0 ? d.innerText :
                        d.textContent
            } else
                d = b.value;
            if (d.length < 20 && d.split(Pc).length <= 1) {
                g = sa ? "rtl" : va;
                H(g, e);
                return g
            }
        }, lb = function(d, g) {
            g = g || ab(b);
            if (!d && L.fontFamily === g.fontFamily && L.fontStyle === g.fontStyle && L.fontWeight === g.fontWeight && L.lineHeight === g.lineHeight && L.paddingLeft === g.paddingLeft && L.paddingRight === g.paddingRight && L.paddingTop === g.paddingTop && L.paddingBottom === g.paddingBottom && L.borderLeftWidth === g.borderLeftWidth && L.borderRightWidth === g.borderRightWidth && L.borderTopWidth === g.borderTopWidth && L.borderBottomWidth ===
                    g.borderBottomWidth && L.fontSize === g.fontSize && L.direction === g.direction)
                return false;
            L.fontFamily = g.fontFamily;
            L.fontStyle = g.fontStyle;
            L.fontWeight = g.fontWeight;
            L.direction = g.direction;
            L.lineHeight = g.lineHeight;
            Ea = f(L.paddingLeft = g.paddingLeft);
            Ba = f(L.paddingRight = g.paddingRight);
            Ga = f(L.paddingTop = g.paddingTop);
            fa = f(L.paddingBottom = g.paddingBottom);
            if (C)
                pa = ja = Na = ka = 0;
            else {
                pa = f(L.borderLeftWidth = g.borderLeftWidth);
                ja = f(L.borderRightWidth = g.borderRightWidth);
                Na = f(L.borderTopWidth = g.borderTopWidth);
                ka = f(L.borderBottomWidth = g.borderBottomWidth)
            }
            T = l.fixTextZoom(f(L.fontSize = g.fontSize, true));
            if (Fa === void 0) {
                Fa = Ea;
                Ha = Ba
            }
            bb = T;
            var e = U = F("settingsFontSize");
            if (U == "auto") {
                var t = Math.max(T, 9);
                e = t + "px";
                if (t >= 24)
                    t -= 6;
                else if (t >= 20)
                    t -= 4;
                else if (t >= 18)
                    t -= 3;
                else if (t >= 16)
                    t -= 2;
                else if (t >= 14)
                    t -= 1;
                U = t + "px"
            }
            t = Gb(U);
            V = U;
            Ia = Math.max(9, parseFloat(t.value, 10) * 0.7) + t.unit;
            e = l.getFontHeight(L.fontFamily, e) - 2;
            e = parseInt(e / 2, 10) * 2;
            X = w = 0;
            if (F("settingsPlacement") != "hide") {
                w = Math.min(Math.max(e, 14), 32);
                X = Math.floor((e -
                        w) / 2)
            }
            Qa = l.getFontHeight(F("uiFontFamily"), V) / 12;
            z && z.updateFontSizes();
            return true
        }, f = function(d, g) {
            if (d.indexOf("px") != -1)
                return parseInt(Gb(d).value, 10);
            if (g)
                if (B)
                    return b.clientHeight - Ga - fa - 3;
                else {
                    var e = xb(b);
                    e.collapse();
                    return e.boundingHeight - 3
                }
            e = b.style.left;
            var t = b.runtimeStyle.left;
            b.runtimeStyle.left = b.currentStyle.left;
            try {
                b.style.left = d || 0
            } catch (r) {
            }
            var s = b.style.pixelLeft;
            b.style.left = e;
            b.runtimeStyle.left = t;
            return s
        }, j = function(d) {
            i.assert(!D, "_getInputTextWidth should not be used with RTEs");
            var g = G("div");
            g.style.position = "absolute";
            g.style.visibility = "hidden";
            g.style.fontFamily = L.fontFamily;
            g.style.fontSize = T + "px";
            g.style.fontStyle = L.fontStyle;
            g.style.fontWeight = L.fontWeight;
            d = d.replace(/ /g, "&nbsp;");
            g.innerHTML = d;
            l.appendChild(g);
            d = g.offsetWidth;
            l.removeChild(g);
            return d
        }, k = function() {
            if (B || Pb)
                return 0;
            var d = b.offsetWidth - b.scrollWidth - pa - ja;
            if (!da && !ub)
                d -= Ea + Ba;
            return d
        }, n = function() {
            var d;
            switch (Za) {
                case 2:
                    d = b.scrollWidth;
                    if (Wa >= 3)
                        d -= 2;
                    break;
                case 4:
                    d = b.scrollWidth;
                    break;
                case 3:
                    d =
                            b.scrollWidth - 6;
                    if ($b)
                        d = b.scrollWidth - Ea - Ba - 17 + ga;
                    break;
                default:
                    d = b.scrollWidth - Ea - Ba;
                    break
            }
            return d
        }, A = 0, R = function(d) {
            if (da && C && F("rteIEFocusBugFix")) {
                var g = b.innerHTML.length;
                !Da && sa && g && A && g != A && setTimeout(function() {
                    b.focus()
                }, 0);
                A = g
            }
            g = zc(b);
            if (!g) {
                z.hideYamliSettings();
                x.width = 0;
                return true
            }
            var e = i.I.getDimensions(P), t = x && x.width === 0, r = k();
            if ((C || K && Da) && F("overrideDirection")) {
                var s = M();
                if (ub || s != Ab) {
                    var p = db();
                    Ab = p ? p : s
                }
            }
            if (lb(t, g))
                d = true;
            if (ga != r) {
                ga = r;
                d = true
            }
            if (!(!d && x && e.left == x.left && e.width ==
                    x.width && e.top == x.top && e.height == x.height)) {
                x = e;
                d = C && !da ? i.I.getDimensions(b) : x;
                z.resetSettingsUI();
                g = z.getYamliSettingsDims();
                var N = p = s = e = 0, S = 0, I = la ? -2 : g.height + 2, ba = 0, qa = 0;
                t = F("insidePlacementPadding");
                r = F("useMinPadding") && F("tool").indexOf("ffext") == -1;
                minPadding = w + 2 + t;
                if (B) {
                    z.setMainOffset(x.left, x.top, x.right);
                    switch (F("settingsPlacement")) {
                        case "bottomRight":
                            e = x.width - g.width - 1;
                            s = 1;
                            p = x.height + 2;
                            if (la)
                                I = -p;
                            break;
                        case "bottomLeft":
                            e = 1;
                            s = x.width - g.width - 1;
                            p = x.height + 2;
                            if (la)
                                I = -p;
                            break;
                        case "topRight":
                            e =
                                    x.width - g.width - 1;
                            s = 1;
                            p = -g.height;
                            la || (I = g.height + x.height + 1);
                            break;
                        case "topLeft":
                            e = 1;
                            s = x.width - g.width - 1;
                            p = -g.height;
                            la || (I = g.height + x.height + 1);
                            break;
                        case "rightTop":
                            e = x.width + 2;
                            s = -g.width - 2;
                            p = 1;
                            break;
                        case "rightCenter":
                            e = x.width + 2;
                            s = -g.width - 2;
                            p = Math.floor((x.height - g.height) / 2) + 1;
                            break;
                        case "rightBottom":
                            e = x.width + 2;
                            s = -g.width - 2;
                            p = x.height - g.height + 1;
                            break;
                        case "leftTop":
                            e = -g.width - 2;
                            s = x.width + 2;
                            break;
                        case "leftCenter":
                            e = -g.width - 2;
                            s = x.width + 2;
                            p = Math.floor((x.height - g.height) / 2) - 1;
                            break;
                        case "leftBottom":
                            e =
                                    -g.width - 2;
                            s = x.width + 2;
                            p = x.height - g.height - 2;
                            break;
                        case "inside":
                            if (!da && r) {
                                b.style.paddingLeft = Fa + "px";
                                b.style.paddingRight = Ha + "px"
                            }
                            if (M() == "rtl") {
                                if (!da && r && Fa < minPadding)
                                    b.style.paddingLeft = minPadding + "px";
                                e = pa + 1 + t;
                                s = x.width - g.width - ja - 1 - t
                            } else {
                                if (!da && r && Ha < minPadding)
                                    b.style.paddingRight = minPadding + "px";
                                e = x.width - g.width - ja - 1 - t;
                                s = pa + 1 + t
                            }
                            p = Na + Math.floor((x.height - Na - ka - w) / 2) - X - 1;
                            ba = -w - 2;
                            qa = la ? -2 : w + 2;
                            N = -e;
                            I = -p + (la ? -2 : x.height + 2);
                            S = -s;
                            break
                        }
                } else {
                    z.setMainOffset(d.left, d.top, d.right);
                    switch (F("settingsPlacement")) {
                        case "bottomRight":
                            e =
                                    x.width - g.width - 1;
                            s = 1;
                            p = x.height + 1;
                            if (la)
                                I = -p;
                            break;
                        case "bottomLeft":
                            e = 1;
                            s = x.width - g.width - 1;
                            p = x.height + 1;
                            if (la)
                                I = -p;
                            break;
                        case "topRight":
                            e = x.width - g.width - 1;
                            s = 1;
                            p = -g.height - 2;
                            N = -x.width + g.width + 2;
                            S = -s + 2;
                            la || (I = g.height + x.height + 2);
                            break;
                        case "topLeft":
                            e = 1;
                            s = x.width - g.width - 1;
                            p = -g.height - 2;
                            S = -s + 2;
                            la || (I = g.height + x.height + 2);
                            break;
                        case "rightTop":
                            e = x.width + 2;
                            s = -g.width - 2;
                            p = 2;
                            N = -x.width + 2;
                            S = -s + 2;
                            la || (I = x.height + 2);
                            break;
                        case "rightBottom":
                            e = x.width + 2;
                            s = -g.width - 2;
                            p = x.height - g.height - 1;
                            break;
                        case "leftTop":
                            e =
                                    -g.width - 2;
                            s = x.width + 2;
                            p = 2;
                            N = g.width + 2;
                            S = -x.width + 2;
                            la || (I = x.height + 2);
                            break;
                        case "leftBottom":
                            e = -g.width - 2;
                            s = x.width + 2;
                            p = x.height - g.height - 2;
                            break;
                        case "inside":
                            if (r) {
                                b.style.paddingLeft = Fa + "px";
                                b.style.paddingRight = Ha + "px"
                            }
                            s = da || !r ? ga : 0;
                            if (M() == "rtl") {
                                if (!r && (Eb || ub))
                                    s = 0;
                                if (r && Fa < minPadding)
                                    b.style.paddingLeft = minPadding + "px";
                                e = pa + s + 1 + t;
                                s = x.width - g.width - ja - 1 - s - t
                            } else {
                                if (ub && !D)
                                    s = ga;
                                if (r && Ha < minPadding)
                                    b.style.paddingRight = minPadding + "px";
                                e = x.width - g.width - ja - 1 - s - t;
                                s = pa + 1 + s + t
                            }
                            p = Na;
                            ba = -w - 2;
                            qa = la ?
                                    -2 : w + 2;
                            N = -e;
                            S = -s;
                            I = -p + (la ? 0 : x.height + 2);
                            break
                    }
                    e -= d.left - x.left;
                    s -= d.right - x.right;
                    p -= d.top - x.top
                }
                z.setYamliSettingsPosition(e + F("settingsXOffset"), p + F("settingsYOffset"), s - F("settingsXOffset"));
                z.setYamliSettingsMenuPosition(w + ba + 2, (la ? -2 : g.height + 2) + qa, 0);
                z.setYamliTipsPosition(e + N, p + I, s + S, p + I);
                F("settingsPlacement") == "hide" ? z.hideYamliSettings() : z.showYamliSettings()
            }
        }, $ = function() {
            var d = Va();
            if (!(!d || d.start != d.end))
                return rb(ca(b), d, false, F("disableInMarkup"))
        }, aa = function(d) {
            if (d != Y) {
                ma(false);
                J = l.createTransliterationRequest(d);
                Y = d;
                J.onreadystatechange = function() {
                    var g = l.processTransliterationResponse(J);
                    if (g >= 0) {
                        J = void 0;
                        g == 1 && jb(true, false)
                    }
                };
                J.send()
            }
        }, La = function(d) {
            ma(false);
            var g = {request: void 0, wordSelection: d}, e = l.createTransliterationRequest(d.word);
            g.request = e;
            e.onreadystatechange = function() {
                var t = l.processTransliterationResponse(e);
                if (t >= 0) {
                    for (var r = 0; r < Q.length; ++r)
                        Q[r] == g && Q.splice(r, 1);
                    t == 1 && Bb(d, false)
                }
            };
            Q.push(g);
            e.send()
        }, mb = function() {
            b.spellcheck = M() == "rtl" ? false : true
        },
                Ta = function(d, g, e) {
            if (g != e) {
                g = e - g;
                for (e = 0; e < Q.length; ++e) {
                    var t = Q[e];
                    if (!(t.wordSelection.end <= d)) {
                        t.wordSelection.end += g;
                        t.wordSelection.start += g
                    }
                }
            }
        }, ma = function(d) {
            d && z.hide(false);
            nb();
            Y = void 0;
            if (J !== void 0) {
                var g = J;
                J = void 0;
                g.onreadystatechange = function() {
                    l.processTransliterationResponse(g)
                }
            }
        }, eb = function(d) {
            return J !== void 0 || Q.length > 0 || d && W
        }, Hb = function(d) {
            for (var g = [], e, t = 0, r = 0, s = 0; s < d.length; ++s) {
                e = d.charAt(s);
                g.push(Tb[e]);
                if (e == ",")
                    ++t;
                else
                    e == "." && ++r
            }
            g = g.join("");
            e = [];
            e.push({trans: g,
                type: 1});
            t === 1 && r === 0 && e.push({trans: g.replace(Tb[","], Tb["."]), type: 1});
            l.registerTransliteration(d, e, l.readGlobalPref("useRomanNum") ? 0 : 1)
        }, Bb = function(d, g) {
            var e = l.getTransliterationsFromWordSelection(d);
            if (e) {
                if (Ua(d))
                    if (e.getTransliterationCount() > 0) {
                        var t = e.getSelectedTransliteration();
                        if (t != d.word) {
                            l.registerArabicToRoman(t, e.getRomanWord());
                            l.registerDefaultPick(g && e.getSelectedIndex() == 1 && d.selectionEnd == Sb(ca(b)).length);
                            var r = qb(t, d.start, d.end, false, true);
                            gb = t;
                            Ja |= l.reportTransliterationSelection(e,
                                    r.start, ca(b), true)
                        }
                    }
            } else
                switch (d.wordType) {
                    case i.I.WordType.PURE_ROMAN:
                    case i.I.WordType.ROMAN_ARABIC:
                        La(d);
                        break;
                    case i.I.WordType.PURE_NUMBER:
                        Hb(d.word);
                        Bb(d, false);
                        break
                    }
        }, Ua = function(d) {
            var g = false, e;
            var t = b;
            if (t.tagName == "BODY" || wb(t)) {
                if (da) {
                    e = xb(t);
                    if (t.tagName == "BODY") {
                        e.collapse();
                        e = e.moveEnd("character", 1E7)
                    } else {
                        t = e.duplicate();
                        var r = 0;
                        for (e.collapse(); e.compareEndPoints("StartToEnd", t) < 0; ) {
                            ++r;
                            e.moveStart("character", 1)
                        }
                        e = r
                    }
                } else
                    e = Qb(t, t);
                e = e
            } else
                e = t.value.length;
            e = e;
            if (e >= d.end)
                g =
                        d.word == ca(b).substring(d.start, d.end);
            return g
        }, jb = function(d, g) {
            ma(false);
            var e = $();
            if (e === void 0)
                z.hide();
            else {
                if (!d && e.isEdge)
                    if (e.word.length > 1 || e.selectionOffset !== 0) {
                        z.hide();
                        return
                    }
                var t = l.getTransliterationsFromWordSelection(e);
                if (t && t.getTransliterationCount() === 0)
                    z.hide();
                else {
                    if (t !== void 0 || e.wordType == i.I.WordType.PURE_ROMAN || e.wordType == i.I.WordType.ROMAN_ARABIC) {
                        var r;
                        a:{
                            var s = {l: 0, r: 0, t: 0, b: 0, l2: 0, r2: 0};
                            if (window.getSelection)
                                if (D) {
                                    var p;
                                    r = i.I.getDimensions(b);
                                    p = wc(b, r, e.start);
                                    s.l =
                                            p.left;
                                    s.t = p.top - 2;
                                    s.b = p.top + p.height * 1.1;
                                    p = wc(b, r, e.end);
                                    s.r = p.left + p.width;
                                    if (s.r < s.l) {
                                        p = s.r;
                                        s.r = s.l;
                                        s.l = p
                                    }
                                    Db(e.selectionStart, e.selectionEnd)
                                } else {
                                    if (v) {
                                        var N = ca(b);
                                        p = {x: 0, b: 0, t: 0};
                                        var S = M(), I = S == "rtl";
                                        (r = document.getElementById("yamli_temp")) && l.removeChild(r);
                                        var ba = Ea + pa, qa = Ga + Na;
                                        if (I && (Pb || Zb && Wa < 3))
                                            ba += k();
                                        if (I && $b && k() === 0)
                                            ba += 17;
                                        if (kc)
                                            ba += 3;
                                        r = G("div");
                                        r.id = "yamli_temp";
                                        r.style.top = x.top + qa + "px";
                                        r.style.left = x.left + ba + "px";
                                        I = G("pre");
                                        r.appendChild(I);
                                        r.style.position = "absolute";
                                        r.style.visibility =
                                                "hidden";
                                        I.style.margin = "0";
                                        I.style.border = "0";
                                        I.style.padding = "0";
                                        I.style.lineHeight = L.lineHeight;
                                        I.style.whiteSpace = "pre-wrap";
                                        if (Zb && Wa <= 3 && vb === 0)
                                            I.style.whiteSpace = "-moz-pre-wrap";
                                        I.style.fontFamily = L.fontFamily;
                                        I.style.fontSize = T + "px";
                                        I.style.fontStyle = L.fontStyle;
                                        I.style.fontWeight = L.fontWeight;
                                        I.style.direction = S;
                                        I.style.textAlign = S == "rtl" ? "right" : "left";
                                        S = n();
                                        r.style.width = S + "px";
                                        S = ua.createTextNode(N.substr(0, e.start));
                                        var ra = G("span"), na = ua.createTextNode(N.substr(e.end, 100));
                                        ra.setAttribute("style",
                                                I.getAttribute("style"));
                                        N = N.substring(e.start, e.end);
                                        ra.innerHTML = N;
                                        I.appendChild(S);
                                        I.appendChild(ra);
                                        I.appendChild(na);
                                        l.appendChild(r);
                                        p.x = ra.offsetLeft + ba;
                                        p.t = ra.offsetTop - b.scrollTop + qa - 2;
                                        p.b = p.t + ra.offsetHeight + 1;
                                        l.removeChild(r);
                                        s.l = p.x;
                                        s.t = p.t;
                                        s.b = p.b
                                    } else {
                                        p = e.wordType == i.I.WordType.PURE_ARABIC ? e.word.substr(e.selectionOffset) : e.word.substr(0, e.selectionOffset);
                                        p = j(p);
                                        s.l = E.x - p
                                    }
                                    s.r = s.l + j(e.word)
                                }
                            else {
                                r = ua.selection.createRange();
                                p = P.getClientRects()[0];
                                r.move("character", -e.selectionOffset);
                                r.moveEnd("character", e.word.length);
                                r = r.getClientRects()[0];
                                if (!r || !p) {
                                    r = void 0;
                                    break a
                                }
                                s.l = r.left - p.left;
                                s.r2 = p.right - r.right;
                                if (!B) {
                                    s.t = r.top - p.top;
                                    s.b = r.bottom - p.top
                                }
                                if (s.l < 0 || s.b < 0) {
                                    r = void 0;
                                    break a
                                }
                                s.r = s.l + r.right - r.left;
                                s.l2 = s.r2 + r.right - r.left
                            }
                            if (B)
                                s.b = x.height;
                            r = s
                        }
                        if (r === void 0) {
                            z.hide();
                            return
                        }
                        s = Z() ? r.r : r.l;
                        p = Z() ? r.r2 : r.l2;
                        r = la ? r.t : r.b;
                        if (ea)
                            s = Ub(s);
                        z.setTransliterations(t, e);
                        z.setMenuOffset(s, r, p, Z());
                        z.show()
                    } else
                        z.hide();
                    if (!t) {
                        if (!g)
                            return true;
                        switch (e.wordType) {
                            case i.I.WordType.PURE_ROMAN:
                            case i.I.WordType.ROMAN_ARABIC:
                                aa(e.word);
                                break;
                            case i.I.WordType.PURE_NUMBER:
                                Hb(e.word);
                                jb(true, false);
                                break
                            }
                    }
                }
            }
        }, tb = function() {
            ma(true)
        }, fc = function(d) {
            E = d
        }, Jb = function(d) {
            d = E.x + d;
            var g = 0;
            if (O > 0) {
                var e = Va();
                if (d < 0) {
                    g = ca(b).substr(0, e.start);
                    g = j(g);
                    g = g > O ? O : g
                } else if (d > x.width) {
                    g = ca(b).slice(e.end);
                    g = j(g);
                    g = g > O ? -O : -g
                }
            }
            d += g;
            E.x = Ub(d)
        }, Ub = function(d) {
            if (j(ca(b)) < x.width) {
                var g = $();
                if (!g)
                    return d;
                d = M() == "rtl";
                var e;
                e = ca(b);
                for (var t = g.end, r = [], s = [], p = [], N, S, I = false, ba = 0; ba < e.length; ++ba) {
                    S = e.charAt(ba);
                    N = e.charCodeAt(ba);
                    if (ba == t)
                        if (I) {
                            p.push(S);
                            s = [];
                            s.push(p.join(""));
                            p = [];
                            continue
                        } else {
                            r.push(S);
                            break
                        }
                    if (N == 32)
                        if (I) {
                            s.push(p.join(""));
                            p = [];
                            s.push(S)
                        } else
                            r.push(S);
                    else if ((N = N >= 1536 && N <= 1791) && !d || !N && d) {
                        p.push(S);
                        I = true
                    } else {
                        if (I) {
                            s.push(p.join(""));
                            p = [];
                            for (s.reverse(); s.length > 0; )
                                r.push(s.pop());
                            I = false
                        }
                        if (!I && ba > t)
                            break;
                        r.push(S)
                    }
                }
                if (I) {
                    if (ba == t)
                        s = [];
                    s.push(p.join(""));
                    for (s.reverse(); s.length > 0; )
                        r.push(s.pop())
                }
                e = r.join("");
                e = j(e);
                if (d) {
                    d = x.width - e - Ba - ja;
                    if (Z())
                        d += j(g.word)
                } else {
                    d = e + Ea + pa;
                    Z() || (d -= j(g.word))
                }
            }
            if (d < 0)
                d = 0;
            else if (d >
                    x.width)
                d = x.width;
            return d
        }, qb = function(d, g, e, t, r) {
            var s = Va(), p = ca(b).substr(0, g), N = ca(b).slice(e), S = ca(b).substring(g, e), I = zb(d), ba = zb(S), qa = 0, ra = 0, na;
            if (r) {
                r = rb(ca(b), {start: g, end: g}, true, F("disableInMarkup"));
                r = l.isRegisteredDashedWord(r.word);
                if (I === i.I.WordType.PURE_ROMAN || I === i.I.WordType.MIXED) {
                    if (r) {
                        if (p.length > 0 && !p.substr(p.length - 1).match(i.I.HasPunctuationRegexp)) {
                            d = "-" + d;
                            qa = 1
                        }
                        if (N.length > 0 && !N.substr(0, 1).match(i.I.HasPunctuationRegexp)) {
                            d += "-";
                            ra = 1
                        }
                    }
                } else if (I === i.I.WordType.PURE_ARABIC) {
                    var Ma,
                            Mb;
                    if (p.length > 0 && (p.charAt(p.length - 1) == "-" || r)) {
                        I = p.length - 1;
                        Mb = rb(p, {start: I, end: I}, true, F("disableInMarkup"));
                        if (Mb !== void 0)
                            Ma = Mb.word
                    }
                    var fb, Nb;
                    if (N.length > 0 && (N.charAt(0) == "-" || r)) {
                        Nb = rb(N, {start: 1, end: 1}, true, F("disableInMarkup"));
                        if (Nb !== void 0)
                            fb = Nb.word
                    }
                    if (Ma || fb) {
                        if (ba === i.I.WordType.PURE_ROMAN || ba === i.I.WordType.MIXED) {
                            if (Mb && Mb.wordType === i.I.WordType.PURE_ARABIC) {
                                p = p.substr(0, p.length - 1);
                                qa = -1;
                                S = "-" + S
                            }
                            if (Nb && Nb.wordType === i.I.WordType.PURE_ARABIC) {
                                N = N.substr(1);
                                ra = -1;
                                S += "-"
                            }
                        }
                        ba = {wordArray: []};
                        Kb(ba, Ma);
                        Kb(ba, d);
                        Kb(ba, fb);
                        l.registerDashedWord(ba.wordArray)
                    }
                }
            }
            Ma = e - g;
            fb = d.length;
            if (qa < 0) {
                g += qa;
                Ma -= qa
            } else if (qa > 0)
                fb += qa;
            if (ra < 0) {
                e -= ra;
                Ma -= ra
            } else if (ra > 0)
                fb += ra;
            Ta(g, Ma, fb);
            if (fb != Ma) {
                if (s.start >= g + Ma)
                    s.start += fb - Ma;
                if (s.end >= g + Ma)
                    s.end += fb - Ma
            }
            if (t) {
                s.start = g + fb;
                s.end = s.start
            }
            t = b.scrollTop;
            if (D)
                if (b.ownerDocument.createRange) {
                    p = b.ownerDocument.createRange();
                    na = Fb(b, g);
                    p.setStart(na.node, na.offset);
                    na = Fb(b, e);
                    p.setEnd(na.node, na.offset);
                    i.assert(p.toString() == S, "_replaceText - range content should be: " +
                            S + ", instead is " + p.toString());
                    p.deleteContents();
                    p.insertNode(b.ownerDocument.createTextNode(d === " " ? "&nbsp;" : d));
                    p.detach();
                    b.normalize()
                } else {
                    p = xb(b);
                    p.collapse();
                    p.moveStart("character", g);
                    p.moveEnd("character", e - g);
                    N = p.parentElement();
                    p.collapse();
                    for (p.text = "\ufeff"; N; ) {
                        na = N.innerHTML.indexOf("\ufeff");
                        if (na != -1)
                            break;
                        N = N.parentNode
                    }
                    i.assert(N, "BIG BUG");
                    qa = N.innerHTML.substr(0, na);
                    na = N.innerHTML.substr(na + 1);
                    for (Ma = true; ; ) {
                        ra = na.indexOf(S);
                        if (ra == -1)
                            break;
                        if (!na.substr(0, ra).match(/(<[^<>]*)$/)) {
                            Ma =
                                    false;
                            break
                        }
                        qa += na.substr(0, ra + S.length);
                        na = na.substr(ra + S.length)
                    }
                    if (Ma) {
                        N.innerHTML = qa + na;
                        p.moveStart("character", -10000000);
                        p.collapse();
                        p.moveStart("character", g);
                        p.moveEnd("character", e - g);
                        i.assert(p.text == S, "_replaceText - range content should be: " + S + ", instead is " + p.text);
                        p.text = d
                    } else
                        N.innerHTML = qa + na.replace(S, d)
                }
            else
                b.value = p + d + N;
            b.scrollTop = t;
            Db(s.start, s.end);
            F("generateOnChangeEvent") && ec(b, "change");
            return{start: g, end: e}
        }, Va = function() {
            var d = {start: 0, end: 0};
            if (D && b.ownerDocument.defaultView) {
                if (K &&
                        !Da)
                    return;
                var g = b.ownerDocument.defaultView.getSelection();
                if (g.rangeCount != 1)
                    return;
                g = g.getRangeAt(0);
                d.start = xc(b, g.startContainer, g.startOffset);
                d.end = xc(b, g.endContainer, g.endOffset)
            } else if (b.setSelectionRange) {
                d.start = b.selectionStart;
                d.end = b.selectionEnd
            } else if (document.selection && document.selection.createRange) {
                b.document.activeElement != b && b.focus();
                var e;
                try {
                    e = b.document.selection.createRange();
                    g = e.duplicate()
                } catch (t) {
                    return
                }
                if (v) {
                    e = ia.createTextRange();
                    e.moveToElementText(b);
                    e.setEndPoint("EndToStart",
                            g);
                    var r = ia.createTextRange();
                    r.moveToElementText(b);
                    r.setEndPoint("StartToEnd", g);
                    var s = false, p = false, N = false, S, I, ba, qa, ra, na;
                    S = I = e.text;
                    ba = qa = g.text;
                    ra = na = r.text;
                    do {
                        if (!s)
                            if (e.compareEndPoints("StartToEnd", e) === 0)
                                s = true;
                            else {
                                e.moveEnd("character", -1);
                                if (e.text == S)
                                    I += "\r\n";
                                else
                                    s = true
                            }
                        if (!p)
                            if (g.compareEndPoints("StartToEnd", g) === 0)
                                p = true;
                            else {
                                g.moveEnd("character", -1);
                                if (g.text == ba)
                                    qa += "\r\n";
                                else
                                    p = true
                            }
                        if (!N)
                            if (r.compareEndPoints("StartToEnd", r) === 0)
                                N = true;
                            else {
                                r.moveEnd("character", -1);
                                if (r.text ==
                                        ra)
                                    na += "\r\n";
                                else
                                    N = true
                            }
                    } while (!s || !p || !N);
                    d.start = I.length;
                    d.end = d.start + qa.length
                } else if (D) {
                    if (C)
                        d.start = 0 - g.moveStart("character", -10000000);
                    else {
                        r = xb(b);
                        s = 0;
                        for (d.start = 0; g.compareEndPoints("StartToStart", r) > 0; ) {
                            g.moveStart("character", -1);
                            ++s
                        }
                        d.start = s
                    }
                    r = d.start;
                    g = e.duplicate();
                    g.collapse(false);
                    if (g.moveStart("character", -1))
                        for (; e.inRange(g); ) {
                            ++r;
                            if (!g.moveStart("character", -1))
                                break
                        }
                    d.end = r
                } else {
                    d.start = 0 - g.moveStart("character", -10000000);
                    d.end = d.start + e.text.length
                }
            }
            return d
        }, Db = function(d,
                g) {
            var e;
            if (b.setSelectionRange)
                b.setSelectionRange(d, g);
            else if (b.ownerDocument.body.createTextRange) {
                e = xb(b);
                if (D) {
                    e.moveStart("character", d);
                    e.collapse();
                    e.moveEnd("character", g - d);
                    e.select();
                    var t = Va();
                    if (d - t.start == 1 && d == g) {
                        e.moveStart("character", 1);
                        e.select();
                        e.moveStart("character", -1);
                        e.collapse();
                        e.select()
                    }
                } else {
                    t = ca(b).substr(0, d).split("\r").length - 1;
                    var r = t + ca(b).substring(d, g).split("\r").length - 1;
                    d -= t;
                    g -= r;
                    e.moveStart("character", -10000000);
                    e.moveEnd("character", -10000000);
                    e.collapse();
                    e.moveStart("character", d);
                    e.moveEnd("character", g - d);
                    e.select()
                }
            } else if (D) {
                t = Fb(b, d);
                i.assert(!C || !(t.node.nodeType == 1 && t.offset == t.node.childNodes.length), "WILL FAIL");
                e = b.ownerDocument.createRange();
                e.setStart(t.node, t.offset);
                if (d == g)
                    e.collapse(true);
                else {
                    t = Fb(b, g);
                    e.setEnd(t.node, t.offset)
                }
                t = b.ownerDocument.defaultView.getSelection();
                t.removeAllRanges();
                t.addRange(e)
            }
        }, Kb = function(d, g) {
            if (g !== void 0) {
                var e = l.getDashedWordArray(g);
                if (e)
                    d.wordArray = d.wordArray.concat(e);
                else
                    d.wordArray.push(g)
            }
        },
                ob = function() {
            tb()
        }, Cb = function() {
            if (!Da) {
                Da = true;
                K && db();
                wa()
            }
        }, Vb = function(d) {
            if (Da) {
                l.sync();
                if (z.checkCancelBlur() || document.activeElement && document.activeElement.tagName == "IFRAME" && document.activeElement.id.substr(0, 5) == "yamli")
                    xa(d);
                else {
                    Da = false;
                    wa(true);
                    if (sa) {
                        if (ta && ta.yamliFocused) {
                            if (eb(true)) {
                                b.focus();
                                return
                            }
                            if (z.isVisible()) {
                                z.hideAndSelectCurrent();
                                return
                            }
                        }
                        da ? Wb() : setTimeout(Wb, 300)
                    }
                }
            }
        }, Wb = function() {
            if (!Da) {
                tb();
                z.hideSettings();
                z.hideHint()
            }
        }, pb = function(d) {
            l.reportTyped();
            if (sa) {
                cb =
                        true;
                ya = Ca = false;
                l.clearMouseMoved();
                Ra = Xa = false;
                var g = yc(d);
                if (D) {
                    if (Aa) {
                        clearTimeout(Aa);
                        Aa = void 0
                    }
                    if (g == 13)
                        db();
                    else
                        Aa = setTimeout(Ib, 300)
                }
                if (hb && g == 13)
                    if (eb(true))
                        Ca = true;
                    else {
                        nb();
                        ya = true
                    }
                else
                    nb();
                switch (g) {
                    case 33:
                    case 34:
                    case 35:
                    case 36:
                    case 45:
                    case 46:
                        ya = true;
                        ma(true);
                        return
                }
                if (ea) {
                    var e = Va();
                    if (g == 37) {
                        e = ca(b).substr(e.start - 1, 1);
                        e = j(e);
                        Jb(-e)
                    } else if (g == 39) {
                        e = ca(b).substr(e.end, 1);
                        e = j(e);
                        Jb(e)
                    }
                }
                if (e = g == 32) {
                    e = d ? d : window.event;
                    e = typeof e.shiftKey != "undefined" && e.shiftKey
                }
                if (e)
                    Ra = true;
                if (z.isVisibleAndNotLoading())
                    switch (g) {
                        case 9:
                            z.hideAndSelectCurrent();
                            return;
                        case 13:
                            z.hideAndSelectCurrent(true);
                            Ca = Xa = true;
                            return xa(d);
                        case 38:
                            z.processUpKey();
                            return xa(d);
                        case 40:
                            z.processDownKey();
                            return xa(d);
                        case 32:
                            if (z.hasSelectionChanged()) {
                                e = $();
                                if (e.start + e.selectionOffset < e.end) {
                                    z.hideAndSelectCurrent();
                                    Ca = Xa = true;
                                    return xa(d)
                                }
                            }
                    }
                if (z.isVisible())
                    switch (g) {
                        case 9:
                            z.hide();
                            La($());
                            return;
                        case 27:
                            z.hide(true);
                            Xa = true;
                            return xa(d);
                        case 37:
                        case 39:
                            z.hide(true);
                            break
                    }
                else if (g == 13) {
                    l.registerDefaultPick(false);
                    if (!B) {
                        Xa = true;
                        ma(true)
                    }
                }
            }
        }, Lb = function(d) {
            if (sa)
                if (cb) {
                    cb =
                            false;
                    if (!ya) {
                        if (Ca)
                            return xa(d);
                        var g = yc(d), e = 150;
                        Sa = true;
                        switch (g) {
                            case 38:
                                if (z.isVisibleAndNotLoading())
                                    return xa(d);
                                z.hide();
                                B || (e = 2E3);
                                break;
                            case 40:
                                if (z.isVisibleAndNotLoading())
                                    return xa(d);
                                z.hide();
                                B || (e = 2E3);
                                break;
                            case 37:
                            case 39:
                                d = Va();
                                if (d.start != d.end)
                                    return;
                                if (!B) {
                                    d = $();
                                    d !== void 0 && gb == d.word || (e = 700)
                                }
                                break;
                            case 252:
                                ya = true;
                                return;
                            case 13:
                                Ka = B;
                                l.sync();
                                return;
                            case 190:
                                l.sync();
                                break
                        }
                        if (!Xa) {
                            ma(false);
                            d = true;
                            if (e == 150)
                                d = jb(Sa, false);
                            if (d)
                                W = setTimeout(gc, e)
                        }
                    }
                } else
                    Ca = true
        }, gc = function() {
            W =
                    void 0;
            jb(Sa, true)
        }, nb = function() {
            if (W) {
                clearTimeout(W);
                W = void 0
            }
        }, sb = function(d) {
            var g, e = d ? d : window.event, t;
            if (Pb)
                t = e.which;
            else {
                t = e.charCode !== void 0 ? e.charCode : e.keyCode !== void 0 ? e.keyCode : e.which !== void 0 ? e.which : 0;
                if (t === 0 && e.keyCode === 13)
                    t = 13
            }
            g = t;
            t = e = String.fromCharCode(g);
            if (e.match(Bc))
                if (l.getShowHint()) {
                    var r = F("hintMode");
                    if (r == "startModeOff" && !sa || r == "startModeOnOrOff")
                        z.showHint();
                    ca(b).length > 30 && z.hideHint(false)
                }
            if (sa)
                if (!ya) {
                    if (Ca)
                        return xa(d);
                    r = Va();
                    var s = ca(b), p = rb(s, r, false, F("disableInMarkup"));
                    if (e === " ") {
                        if (r.end == s.length || s.substr(r.end, 1).match(Oc))
                            if (p !== void 0 && p.word.match(Nc))
                                e = "-"
                    } else if (p !== void 0 && p.selectionEnd == p.end && (p.word + t).match(Cc))
                        return;
                    if (!Ra && e.match(i.I.HasPunctuationRegexp))
                        if (!(B && g == 13)) {
                            var N = false;
                            r || (r = Va());
                            s || (s = ca(b));
                            p = r.start;
                            if (g == 13 && D)
                                for (; p > 0 && s.substr(p - 1, 1) == "\r"; )
                                    p--;
                            if (p > 0) {
                                g = {start: p - 1, end: p};
                                p = rb(ca(b), g, false, F("disableInMarkup"));
                                if (p !== void 0 && p.isEdge) {
                                    N = p.wordType == i.I.WordType.PURE_NUMBER;
                                    if (p.wordType == i.I.WordType.PURE_ROMAN || p.wordType ==
                                            i.I.WordType.ROMAN_ARABIC || N && Tb[e] === void 0) {
                                        Bb(p, false);
                                        r = Va()
                                    }
                                }
                            }
                            g = Ac[e];
                            if (g !== void 0 && !N && e != "\r" && g != t) {
                                qb(g, r.start, r.end, true, false);
                                return xa(d)
                            }
                        }
                }
        }, Ib = function() {
            Aa = void 0
        }, h = function(d) {
            if (sa) {
                if (z.isVisible()) {
                    var g = z.getWordSelection(), e = $();
                    if (e !== void 0 && g.word == e.word && g.start == e.start && g.end == e.end) {
                        z.hide(false);
                        return
                    }
                }
                R(false);
                if (ea) {
                    d = yb(d);
                    E = {x: d.x - x.left, y: d.y - x.top}
                }
                jb(true, true)
            }
        }, m = function(d) {
            if (sa) {
                var g = yb(d);
                d = n();
                var e = x.width - d;
                g = g.x - x.left;
                if (M() == "rtl" && g <= e || M() ==
                        "ltr" && g > d)
                    tb();
                z.hideSettings()
            }
        }, o = function() {
            ta.yamliFocused = false
        }, u = function() {
            ta.yamliFocused = true
        }, y = function() {
            ta.yamliFocused = true
        }, H = function(d, g) {
            if (D && F("rteDirectionMode") == "block") {
                var e = g ? g : dc(b);
                if (e) {
                    if (e.block)
                        if (e.attr == "style")
                            e.block.style.direction = d;
                        else
                            e.block.dir = d;
                    else {
                        e = "<" + Oa + ' dir="' + d + '">' + (b.innerHTML.length ? b.innerHTML : "<br>") + "</" + Oa + ">";
                        var t = Da ? Va() : void 0;
                        b.innerHTML = e;
                        b.normalize();
                        Da && Db(t.start, t.end)
                    }
                    wa()
                }
            } else {
                b.style.direction = d;
                b.dir = d;
                mb();
                wa()
            }
        }, M = function() {
            var d;
            if (D) {
                d = dc(b);
                d = !d || !d.dir ? ab(b).direction : d.dir
            } else
                d = ab(b).direction;
            return d
        }, Z = function() {
            return M() == "rtl"
        }, F = function(d) {
            if (c.hasOwnProperty(d))
                return c[d];
            return l.getOption(d)
        }, kb = function(d) {
            if (eb(true)) {
                Da || b.focus();
                return xa(d)
            }
            z.isVisible() && z.hideAndSelectCurrent()
        };
        this.adjustReplaceRequests = Ta;
        this.validateWordSelection = Ua;
        this.replaceInputValue = qb;
        this.getInputDims = function() {
            return x
        };
        this.getInputFontSize = function() {
            return T
        };
        this.setDirection = function(d) {
            H(d);
            R(true)
        };
        this.getDirection =
                M;
        this.useRtlMenu = Z;
        this.toggleDir = function() {
            H(M() == "rtl" ? "ltr" : "rtl");
            R(true)
        };
        this.hasFocus = function() {
            return Da
        };
        this.setEnabled = ib;
        this.getEnabled = function() {
            return sa
        };
        this.resetLayout = R;
        this.getMenuFontSize = function() {
            return bb
        };
        this.getSettingsMenuFontSize = function() {
            return V
        };
        this.getSettingsFontSize = function() {
            return U
        };
        this.getCloseFontSize = function() {
            return Ia
        };
        this.adjustAjaxWidth = function(d) {
            return parseInt(d * Qa, 10)
        };
        this.getSettingsButtonSize = function() {
            return w
        };
        this.getSettingsButtonOffset =
                function() {
                    return X
                };
        this.getOption = F;
        this.isTextArea = function() {
            return v
        };
        this.isTextBox = function() {
            return B
        };
        this.isRTE = function() {
            return D
        };
        this.getInput = function() {
            return b
        };
        this.focusInput = function() {
            b.focus()
        };
        this.setSelection = Db;
        this.getSelection = function() {
            return Va()
        };
        this.lang = function(d) {
            var g = Gc[F("uiLanguage")][d];
            if (g === void 0)
                return d;
            return g
        };
        this.areRequestsPending = eb;
        this.isMenuVisible = function() {
            return z.isMenuVisible()
        };
        this.hideTransliterations = tb;
        this.activateAds = function() {
            z.activateAds()
        };
        this.setForm = function(d) {
            if (B) {
                hb = d;
                i.I.addEvent(hb, "submit", kb)
            }
        };
        this.setSubmitButton = function(d) {
            if (ta = d) {
                i.I.addEvent(ta, "blur", o);
                i.I.addEvent(ta, "focus", u);
                i.I.addEvent(ta, "mousedown", y);
                ta.yamliFocused = false
            }
        };
        this.getId = function() {
            return q
        };
        this.setWasUsed = function() {
            Ja = true
        };
        this.getSyncData = function() {
            if (!Ja || !F("sy"))
                return"";
            var d = {}, g = ca(b).split(/[.\n]/), e, t = 0, r;
            for (e = 0; e < g.length; ++e) {
                r = Sb(g[e]);
                r = r.length ? r.replace(/\*/g, " ") : void 0;
                if (r !== void 0) {
                    d["_" + r] = {n: t, s: 0};
                    ++t
                }
            }
            t = [];
            var s =
                    r = 0;
            t.push(B ? "tb" : v ? "ta" : "rt");
            t.push(0);
            t.push(Ka ? 1 : 0);
            Ka = false;
            for (var p in d)
                if (d.hasOwnProperty(p)) {
                    ++s;
                    e = d[p];
                    if ((g = za[p]) && g.n == e.n && g.s)
                        e.s = 1;
                    else if (r < 1900) {
                        t.push(e.n + "-" + Kc(p.substr(1)));
                        r += t[t.length - 1].length + 1;
                        e.s = 1
                    }
                }
            za = d;
            if (t.length == 3 && !t[2])
                return"";
            t[1] = s.toString(10);
            return t.join("*")
        };
        this.getScrollbarWidth = k;
        this.unload = function() {
            ib(false);
            i.I.removeEvent(b, "blur", Vb);
            i.I.removeEvent(b, "keydown", pb, true);
            i.I.removeEvent(b, "keyup", Lb);
            i.I.removeEvent(b, "keypress", sb);
            i.I.removeEvent(b,
                    "click", h);
            i.I.removeEvent(b, "mousedown", m);
            i.I.removeEvent(b, "focus", Cb);
            D && i.I.removeEvent(b, "scroll", ob);
            if (C) {
                i.I.removeEvent(ua, "mousewheel", ob);
                i.I.removeEvent(ua, "DOMMouseScroll", ob)
            }
            hb && i.I.removeEvent(hb, "submit", kb);
            if (ta) {
                i.I.removeEvent(ta, "blur", o);
                i.I.removeEvent(ta, "focus", u);
                i.I.removeEvent(ta, "mousedown", y)
            }
            nb();
            if (Aa) {
                clearTimeout(Aa);
                Aa = void 0
            }
            z.unload();
            var d = function() {
            };
            if (J)
                J.onreadystatechange = d;
            for (var g = 0; g < Q.length; ++g)
                Q[g].onreadystatechange = d;
            Q = void 0;
            try {
                b.yamliManager =
                        void 0
            } catch (e) {
            }
            b = P = x = z = oa = hb = ta = L = ua = $a = void 0
        };
        this.getText = function() {
            return ca(b)
        };
        b.yamliManager = oa;
        v = b.type == "textarea";
        C = b.tagName == "BODY";
        K = !C && wb(b);
        D = C || K;
        ea = (B = b.type == "text") && !da;
        la = F("popupDirection") == "up";
        P = C ? $a.frameElement : b;
        Pa = b.getAttribute("autocomplete");
        va = ab(b).direction;
        if (D)
            switch (F("rteBlockType")) {
                case "p":
                    Oa = "p";
                    break;
                case "div":
                    Oa = "div";
                    break;
                default:
                    Oa = b.firstChild && b.firstChild.tagName == "P" ? "p" : "div"
            }
        if (!D || F("rteDirectionMode") != "block") {
            b.dir = va;
            b.style.direction =
            va
        }
        lb();
        if (B && navigator.userAgent.indexOf("Firefox") != -1)
            O = j("MMMMMMMMNNNN");
        z = new Tc(oa);
        mb();
        i.I.addEvent(b, "blur", Vb);
        i.I.addEvent(b, "keydown", pb, true);
        i.I.addEvent(b, "keyup", Lb);
        i.I.addEvent(b, "keypress", sb);
        i.I.addEvent(b, "click", h);
        i.I.addEvent(b, "mousedown", m);
        i.I.addEvent(b, "focus", Cb);
        D && i.I.addEvent(b, "scroll", ob);
        if (C) {
            i.I.addEvent(ua, "mousewheel", ob);
            i.I.addEvent(ua, "DOMMouseScroll", ob)
        }
        ib(l.shouldEnableOnStart(F("startMode")));
        R(true);
        ea && fc({x: x.width, y: x.height})
    };
    i.isSupported = function() {
        lc();
        return Za != 5
    };
    i.sendXHR = function(a, c, b) {
        var q = i.I.createXHR();
        q.open("POST", a);
        q.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        q.setRequestHeader("Content-length", c.length);
        q.setRequestHeader("Connection", "close");
        q.send(c);
        q.onreadystatechange = function() {
            if (q.readyState == 4)
                if (b)
                    q.status == 200 ? b(true, q.responseText) : b(false)
        }
    };
    i.init = function(a, c, b) {
        if (typeof a == "string") {
            c || (c = {});
            c.accountId = a
        } else
            c = a;
        return l.setInitParams(c, b)
    };
    i.unload = function() {
        l.resetYamli()
    };
    i.yamlify =
            function(a, c) {
                if (l.isInitialized())
                    if (typeof a == "string")
                        typeof c != "object" && typeof c != "undefined" || l.setupInstancesById(a, c, true)
            };
    i.deyamlify = function(a) {
        l.isInitialized() && typeof a == "string" && l.removeInstanceById(a, true)
    };
    i.yamlifyClass = function(a, c) {
        if (l.isInitialized())
            if (typeof a == "string")
                typeof c != "object" && typeof c != "undefined" || l.setupInstancesByClass(a, c, true)
    };
    i.deyamlifyClass = function(a) {
        l.isInitialized() && typeof a == "string" && l.removeInstancesByClass(a, true)
    };
    i.yamlifyType = function(a,
            c) {
        if (l.isInitialized())
            if (typeof a == "string")
                if (!(typeof c != "object" && typeof c != "undefined"))
                    if (a == "any") {
                        l.setupInstancesByType("textbox", c, true);
                        l.setupInstancesByType("textarea", c, true);
                        l.setupInstancesByType("rte", c, true)
                    } else
                        l.setupInstancesByType(a, c, true)
    };
    i.deyamlifyType = function(a) {
        if (l.isInitialized())
            if (typeof a == "string")
                if (a == "any") {
                    l.removeInstancesByType("textbox", true);
                    l.removeInstancesByType("textarea", true);
                    l.removeInstancesByType("rte", true)
                } else
                    l.removeInstancesByType(a, true)
    };
    i.setGlobalOptions = function(a) {
        if (l.isInitialized())
            typeof a != "object" && typeof a != "undefined" || l.setGlobalOptions(a, false, true)
    };
    i.setClassOptions = function(a, c) {
        if (l.isInitialized())
            if (typeof a == "string")
                typeof c != "object" && typeof c != "undefined" || l.setupInstancesByClass(a, c, false)
    };
    i.setIdOptions = function(a, c) {
        if (l.isInitialized())
            if (typeof a == "string")
                typeof c != "object" && typeof c != "undefined" || l.setupInstancesById(a, c, false)
    };
    i.getInstances = function() {
        return l.getInstances()
    };
    i.I.getStackTrace = function() {
        try {
            (void 0).z()
        } catch (a) {
            try {
                if (a.stack)
                    return a.stack.replace(/^.*?\n/,
                            "").replace(/(?:\n@:0)?\s+$/m, "").replace(/^\(/gm, "{anonymous}(").split("\n");
                else {
                    for (var c = arguments.callee.caller, b = 10, q = /function\s*([\w\-$]+)?\s*\(/i, v = [], C = 0, K, D, B; c && b; ) {
                        --b;
                        K = q.test(c.toString()) ? RegExp.$1 || "{anonymous}" : "{anonymous}";
                        D = v.slice.call(c.arguments);
                        for (B = D.length; B--; )
                            switch (typeof D[B]) {
                                case "string":
                                    D[B] = '"' + D[B].replace(/"/g, '\\"') + '"';
                                    break;
                                case "function":
                                    D[B] = "function";
                                    break
                            }
                        v[C++] = K + "(" + D.join() + ")";
                        c = c.caller
                    }
                    return v
                }
            } catch (P) {
                return[]
            }
        }
        return[]
    };
    i.global = l = new (function() {
        var a,
                c = {}, b = {}, q = {}, v = [], C = [], K = [], D = "http://", B = [], P = 0, x = false, J = false, Q, Y = [], z = [], E, ea, O, oa, W, Aa = false, T = void 0, Ea = false, Ba = false, Fa = false, Ha = 0, Ga, fa, pa = new Sc, ja = 0, Na = 0, ka = false, va = false, Oa = false, L = {x: -1, y: -1}, Pa = 0, bb = 0, V = {}, U, w = {useRomanNum: 1}, X, Ia = {}, Qa, Ca, ya = function(f, j) {
            var k = l.createTransliterationRequest(f);
            k.onreadystatechange = function() {
                if (l.processTransliterationResponse(k) == 1) {
                    var n = zb(f);
                    l.getTransliterations(n, f).setSelectedWord(j);
                    l.registerArabicToRoman(j, f)
                }
            };
            k.send()
        }, cb = function(f) {
            Q =
                    f;
            f = Q.prefs ? Q.prefs : w;
            for (var j in w)
                if (w.hasOwnProperty(j))
                    f.hasOwnProperty(j) || (f[j] = w[j]);
            j = Q.options;
            for (var k in j)
                if (j.hasOwnProperty(k))
                    O[k] = j[k];
            Xa()
        }, Xa = function() {
            X = window.clearInterval;
            if (/\.facebook\.com/i.test(Ob.location.host))
                window.clearInterval = function(A) {
                    A == Ga || A == U || X(A)
                };
            var f = ab(ia), j, k, n;
            if (da)
                Aa = f.direction == "rtl";
            f = O.zIndexBase;
            fa = qc(".yamliapi_parent {width: 100%;}\n.yamliapi_parent *{line-height:normal;font-size:11px;font-style:normal;font-weight:normal;font-family:" + O.uiFontFamily +
                    ";direction:ltr;color:black;letter-spacing:normal;text-align:left;text-decoration:none;text-indent:0;text-transform:none;white-space:normal;word-spacing:normal;padding:0;margin:0;border-width:0;}\n.yamliapi_settingsDiv,.yamliapi_settingsDiv *{z-index:" + (f + 1) + ";white-space:nowrap;}\na.yamliapi_simpleLink, a.yamliapi_simpleLink *{color:#112abb;white-space:nowrap;cursor:pointer;}\na.yamliapi_simpleLink:hover, a.yamliapi_simpleLink:hover *{text-decoration:underline!important;background-color:transparent!important;}\n.yamliapi_button {border: solid 1px #dde4ee;background-color: #4d679a!important;cursor:pointer;font-weight:bold;padding:1px 0.3em;color: white;text-decoration: none;text-align: center;white-space:nowrap;display:inline;}\n.yamliapi_anchor, .yamliapi_anchor *,.yamliapi_anchor:link, .yamliapi_anchor:link *,.yamliapi_anchor:visited, .yamliapi_anchor:visited *,.yamliapi_anchor:hover, .yamliapi_anchor:hover *,.yamliapi_anchor:active, .yamliapi_anchor:active *{color:#112abb!important;background-color:transparent!important;text-decoration:none!important;white-space:nowrap!important;cursor:pointer!important;}\n.yamliapi_anchor_y {color:#112abb!important;font-weight: bold; }\n.yamliapi_anchor:hover, .yamliapi_anchor:hover *{text-decoration:underline!important;}\n.yamliapi_menuBorder {border:1px solid #a0a0a0;}\n.yamliapi_menuPanel {padding:1px;background-color:#e0e0e0;}\n.yamliapi_menuContent {padding:1px;background-color:#f5f9ff;}\n.yamliapi_rtlContent, yamliapi_rtlContent *{direction:rtl;text-align:right;}\n.yamliapi_clear {clear: both;height: 1px;line-height: 1px;font-size: 1px;}\niframe.yamli_iframeselectfix {position:absolute;top:0;z-index:-1;filter:mask();width:400px;height:800px;}\n");
            E = G("div");
            E.id = "yamli_div";
            E.className = "yamliapi_parent";
            E.style.position = "absolute";
            E.style.top = "0";
            E.style.overflow = "visible";
            E.style.zIndex = f;
            E.innerHTML = '<div id="yamliapi_dyn_div" style="position:absolute;top:0;width:100%"></div>';
            ia.appendChild(E);
            ea = document.getElementById("yamliapi_dyn_div");
            oa = G("div");
            oa.style.fontSize = "10px";
            oa.style.position = "absolute";
            oa.style.visibility = "hidden";
            oa.innerHTML = "Yamli rocks";
            oa.style.top = "0";
            if (Aa)
                oa.style.right = ea.style.right = E.style.right = "0";
            else
                oa.style.left =
                        ea.style.left = E.style.left = "0";
            E.appendChild(oa);
            W = Ec(ab(oa).fontSize) / 10;
            ta();
            (Ba = i.I.getCookie("yamli_showed_hint") != "true" && Q.showHint) && hb();
            Fa = Q.showPowered;
            for (j in c)
                c.hasOwnProperty(j) && Ka(j, c[j]);
            for (k in b)
                b.hasOwnProperty(k) && wa(k, b[k]);
            for (n in q)
                q.hasOwnProperty(n) && ib(n, q[n]);
            Ga = Xb(Ab, 1E3);
            i.I.addEvent(window, "resize", Ra);
            i.I.addEvent(window, "load", Ra);
            i.I.addEvent(document, "mousewheel", Sa);
            i.I.addEvent(document, "DOMMouseScroll", Sa);
            i.I.addEvent(window, "unload", gb);
            for (j = 0; j < Y.length; ++j)
                Y[j]();
            Y = void 0
        }, Ra = function() {
            for (var f = 0; f < B.length; ++f)
                B[f].resetLayout()
        }, sa = function(f) {
            f = yb(f);
            if (f.x != L.x || f.y != L.y) {
                Oa = true;
                L = f
            }
        }, Sa = function() {
            for (var f = 0; f < B.length; ++f)
                B[f].hideTransliterations()
        }, gb = function() {
            i.I.removeEvent(window, "unload", gb);
            for (var f = 0; f < z.length; ++f)
                z[f]();
            l.sync();
            l.resetYamli(false)
        }, hb = function() {
            var f = G("img");
            f.width = f.height = "0px";
            i.I.addEvent(f, "load", function() {
                ea.removeChild(f);
                f = void 0
            });
            ea.appendChild(f);
            f.src = l.makeUrl("/cache_safe/marhaban_movie_small.gif",
                    "?")
        }, ta = function() {
            var f = Q.adInfo;
            if (f) {
                switch (f.adType) {
                    case "image":
                        T = G("img");
                        break;
                    case "iframe":
                        T = G("iframe");
                        T.frameBorder = "0";
                        T.scrolling = "no";
                        T.src = "about:blank";
                        break;
                    case "gam":
                        f.url = ua(f.url) + "&u=" + encodeURIComponent(document.location);
                        T = G("iframe");
                        T.frameBorder = "0";
                        T.scrolling = "no";
                        break
                }
                if (T) {
                    T.width = T.height = "0px";
                    i.I.addEvent(T, "load", ga);
                    var j = T;
                    ea.appendChild(T);
                    j.src = f.url
                }
            }
        }, ga = function() {
            if (T) {
                i.I.removeEvent(T, "load", ga);
                var f = ea, j = T;
                setTimeout(function() {
                    f.removeChild(j)
                },
                        0);
                T = void 0
            }
            Ea = true;
            for (var k = 0; k < B.length; ++k)
                B[k].activateAds()
        }, ua = function(f, j) {
            var k = D + "api.yamli.com" + f;
            if (j)
                k += j + "build=" + i.I.buildNumber;
            var n = O.extraHttpParams;
            if (n.length > 0)
                k += (k.indexOf("?") == -1 ? "?" : "&") + n;
            return k
        }, $a = function(f, j) {
            w[f] = j;
            var k = l.makeUrl("/set_preferences.ashx?" + ha("apipref_" + f) + "=" + ha(j), "&"), n = i.I.createSXHR();
            n.open(k);
            n.send()
        }, Da = function() {
            var f = 0, j, k;
            for (var n in V)
                if (V.hasOwnProperty(n)) {
                    ++f;
                    try {
                        if (window.frames[n].frames.length == 2) {
                            k = window.frames[n].frames[1];
                            j = k.document.location.hash;
                            if (j == "#stop") {
                                delete V[n];
                                --f;
                                Eb && k.document.location.replace("about:blank")
                            } else if (j.length > 1) {
                                var A = j.substr(1).split(","), R = document.getElementById(n);
                                R.parentNode.style.width = A[0] + "px";
                                R.width = A[0] + "px";
                                R.height = A[1] + "px";
                                delete V[n];
                                --f;
                                Eb && k.document.location.replace("about:blank")
                            }
                        }
                    } catch ($) {
                    }
                }
            if (!f) {
                X(U);
                U = void 0
            }
        }, Ab = function() {
            E.parentNode != ia && ia.appendChild(E);
            var f = Ec(ab(oa).fontSize) / 10, j = pc(), k = f != W || j != Ha, n = ab(document.body), A = n.position != "static" && !(da &&
                    ic), R, $, aa;
            W = f;
            Ha = j;
            E.style.marginTop = A ? "-" + n.marginTop : 0;
            E.style.marginLeft = A ? "-" + n.marginLeft : 0;
            E.style.marginRight = A ? "-" + n.marginRight : 0;
            for (f = B.length - 1; f >= 0; --f) {
                j = B[f];
                if (j.resetLayout(k)) {
                    B.splice(f, 1);
                    j.unload()
                }
            }
            for (R in c)
                c.hasOwnProperty(R) && Ka(R, c[R]);
            for ($ in b)
                b.hasOwnProperty($) && wa($, b[$]);
            for (aa in q)
                q.hasOwnProperty(aa) && ib(aa, q[aa])
        }, Ja = function() {
            la(5)
        }, la = function(f) {
            ia = document.body;
            if (!ab(ia) || !ia.scrollWidth || !ia.scrollHeight) {
                if (f < 5)
                    Qa = setTimeout(function() {
                        la(f + 1)
                    }, 2E3)
            } else {
                Qa =
                        void 0;
                if (O.checkin == "auto") {
                    var j = {};
                    if (O.tool == "ff_plugin")
                        j.zIndexBase = 2E6;
                    cb({adInfo: {adId: "gam_max_200x200", adType: "gam", height: 0, showSponsored: false, url: "/static/gam_iframe.htm?build=4073#slot=api_max_200x200&w=1&h=0", width: 1}, serverBuild: (new Date).getTime(), showHint: false, showPowered: false, options: j})
                } else {
                    j = l.makeUrl("/checkin.ashx" + l.getReferrerInfo("?"), "&");
                    var k = i.I.createSXHR();
                    k.open(j);
                    k.onreadystatechange = function() {
                        var n;
                        try {
                            n = eval("(" + k.responseText + ")")
                        } catch (A) {
                            n = void 0
                        }
                        if (n)
                            if (n.authorization ==
                                    "staleClient")
                                lb(true, n.serverBuild);
                            else if (n.authorization == "userBlacklisted")
                                document.location.hostname.toLowerCase().indexOf("yamli.com") != -1 && alert("Your IP address has been blacklisted due to violations to the Yamli Terms of Service (http://www.yamli.com/terms/).\n\nPlease contact Yamli at info@yamli.com for more information.");
                            else
                                n.authorization == "authorized" && cb(n)
                    };
                    k.send()
                }
            }
        }, za = function(f, j, k) {
            if (f === void 0)
                f = {};
            if (j)
                O = {};
            j = {accountId: "", assumeDomReady: false, checkin: void 0, disableInMarkup: true,
                extraHttpParams: "", generateOnChangeEvent: false, hintMode: "startModeOff", insidePlacementPadding: 0, maxResults: void 0, notifyStateChanges: false, overrideDirection: true, toggleAffectsAll: true, uiFontFamily: "tahoma,sans-serif", uiLanguage: "en", useMinPadding: true, rteIEFocusBugFix: false, rteDirectionMode: "block", rteBlockType: "auto", settingsColor: "black", settingsLinkColor: "#112abb", settingsFontSize: "auto", settingsPlacement: "bottomRight", settingsXOffset: 0, settingsYOffset: 0, popupDirection: "down", showDirectionLink: true,
                showReportWord: true, showTutorialLink: true, staleClientBehavior: "reload", startMode: "onOrUserDefault", sy: true, tool: "api", tutorialUrl: void 0, zIndexBase: 1E3};
            for (var n in j)
                if (j.hasOwnProperty(n))
                    if (f.hasOwnProperty(n) && typeof f[n] != "undefined")
                        O[n] = f[n];
                    else
                        O.hasOwnProperty(n) || (O[n] = j[n]);
            if (k && Q !== void 0)
                for (var A in c)
                    if (c.hasOwnProperty(A)) {
                        this.removeInstanceById(A, false);
                        Ka(A, c[A])
                    }
        }, Ka = function(f, j) {
            var k = nc(f);
            if (k = db(k, j))
                k.yamlifyId = f
        }, wa = function(f, j) {
            var k = [], n, A, R = document.getElementsByTagName("textarea"),
                    $ = document.getElementsByTagName("input"), aa = RegExp("(^|\\s)" + f.replace(/\-/g, "\\-") + "(\\s|$)");
            for (n = 0; n < R.length; ++n) {
                A = R[n];
                aa.test(A.className) && k.push(A)
            }
            for (n = 0; n < $.length; ++n) {
                A = $[n];
                A.type == "text" && aa.test(A.className) && k.push(A)
            }
            for (n = 0; n < k.length; ++n) {
                A = k[n];
                if (A = db(A, j))
                    A.yamlifyClass = f
            }
        }, ib = function(f, j) {
            var k = {textbox: "input", textarea: "textarea", rte: "iframe"}[f];
            if (k !== void 0) {
                k = document.getElementsByTagName(k);
                var n, A;
                if (f == "rte" && document.querySelectorAll) {
                    A = document.querySelectorAll("div[contenteditable=true]");
                    var R = [];
                    for (n = 0; n < k.length; ++n)
                        R.push(k[n]);
                    for (n = 0; n < A.length; ++n)
                        R.push(A[n]);
                    k = R
                }
                for (n = 0; n < k.length; ++n) {
                    A = k[n];
                    if (A = db(A, j))
                        A.yamlifyType = f
                }
            }
        }, db = function(f, j) {
            var k;
            if (!(!f || typeof f != "object")) {
                switch (f.tagName) {
                    case "TEXTAREA":
                        break;
                    case "INPUT":
                        if (f.type != "text")
                            return;
                        break;
                    case "IFRAME":
                        var n;
                        try {
                            var A = f.contentWindow.document;
                            if (cc(A))
                                n = f;
                            else {
                                var R = A.getElementsByTagName("iframe");
                                for (k = 0; k < R.length; ++k)
                                    if (cc(R[k].contentWindow.document))
                                        n = R[k]
                            }
                        } catch ($) {
                            return
                        }
                        if (!n)
                            return;
                        f = n.contentWindow.document.body;
                        break;
                    default:
                        if (!wb(f))
                            return
                }
                if (f.yamliManager === void 0)
                    if (zc(f)) {
                        n = new hc(f, j);
                        B.push(n);
                        var aa;
                        if (j.formOverride)
                            aa = nc(j.formOverride);
                        else
                            for (k = f; ; ) {
                                k = k.parentNode;
                                if (!k)
                                    break;
                                if (k.tagName && k.tagName.toLowerCase() == "form") {
                                    aa = k;
                                    break
                                }
                            }
                        if (aa) {
                            n.setForm(aa);
                            var La;
                            A = false;
                            for (k = 0; k < aa.length; ++k)
                                if (aa[k] == f)
                                    A = true;
                                else if (aa[k].type == "submit" || aa[k].type == "image")
                                    La = aa[k];
                            A || (La = void 0);
                            n.setSubmitButton(La)
                        }
                        return n
                    }
            }
        }, lb = function(f, j) {
            if (X) {
                if (window.clearInterval != X)
                    window.clearInterval = X;
                X = void 0
            }
            j && i.debug("detected old Yamli client, client build is: " + i.I.buildNumber + ", server build is : " + j);
            clearInterval(Ga);
            Ga = void 0;
            if (U) {
                clearInterval(U);
                U = void 0
            }
            if (Qa) {
                clearTimeout(Qa);
                Qa = void 0
            }
            i.I.SXHRData.unload();
            i.I.SXHRData = void 0;
            i.I.removeEvent(window, "resize", Ra);
            i.I.removeEvent(window, "load", Ra);
            i.I.removeEvent(document, "mousewheel", Sa);
            i.I.removeEvent(document, "DOMMouseScroll", Sa);
            i.I.removeEvent(window, "unload", gb);
            for (var k = 0; k < B.length; ++k)
                B[k].unload();
            B = void 0;
            v = C = K = Y = z = void 0;
            E && ia.removeChild(E);
            E = ea = oa = void 0;
            T && i.I.removeEvent(T, "load", ga);
            T = void 0;
            fa && rc(fa);
            if (i.domReady) {
                i.domReady.unload();
                i.domReady = void 0
            }
            var n, A = O, R = c, $ = b;
            k = q;
            var aa = false;
            O = c = b = k = void 0;
            ha = Ya = Ob = undefined;
            window.Yamli = void 0;
            if (f && A.staleClientBehavior == "sendEvent") {
                aa = true;
                f = false
            }
            if (f) {
                if (document.getElementById("yamli_reload_script")) {
                    i = void 0;
                    return
                }
                A.assumeDomReady = true;
                n = G("script");
                n.id = "yamli_reload_script";
                n.src = "http://api.yamli.com/js/yamli_api.js?" + j;
                var La = function() {
                    var Ta = window.Yamli;
                    if (typeof Ta == "object" && Ta.init(A)) {
                        for (var ma in R)
                            R.hasOwnProperty(ma) && Ta.yamlify(ma, R[ma]);
                        for (var eb in $)
                            $.hasOwnProperty(eb) && Ta.yamlifyClass(eb, $[eb])
                    }
                    A = $ = R = void 0
                };
                switch (Za) {
                    case 2:
                    case 3:
                        n.onload = La;
                        break;
                    case 4:
                        var mb = 0;
                        n.onreadystatechange = function() {
                            if (n.readyState == "loaded") {
                                ++mb;
                                if (mb == 2) {
                                    n.onreadystatechange = null;
                                    La()
                                }
                            }
                        };
                        break;
                    default:
                        n.onreadystatechange = function() {
                            if (n.readyState == "loaded" || n.readyState == "complete") {
                                n.onreadystatechange = null;
                                La()
                            }
                        }
                    }
            }
            i = void 0;
            if (n)
                document.getElementsByTagName("head")[0].appendChild(n);
            else
                aa && ec(document, "yamliStaleClient")
        };
        this.sendOneWay = function(f) {
            var j = i.I.createSXHR();
            j.open(f);
            j.onreadystatechange = function() {
            };
            j.send()
        };
        this.getOption = function(f) {
            return O[f]
        };
        this.readGlobalPref = function(f) {
            return w[f]
        };
        this.saveGlobalPref = $a;
        this.registerTransliteration = function(f, j, k) {
            j = new Uc(f, j);
            v["_" + f] = j;
            k !== void 0 && j.setSelectedIndex(k)
        };
        this.getTransliterations = function(f, j) {
            switch (f) {
                case i.I.WordType.PURE_ROMAN:
                case i.I.WordType.PURE_NUMBER:
                case i.I.WordType.ROMAN_ARABIC:
                    return v["_" +
                    j];
                case i.I.WordType.PURE_ARABIC:
                    var k = C["_" + j];
                    if (k === void 0)
                        return;
                    return v["_" + k]
                }
        };
        this.getTransliterationsFromWordSelection = function(f) {
            return this.getTransliterations(f.wordType, f.word)
        };
        this.registerArabicToRoman = function(f, j) {
            var k = "_" + f;
            C[k] = j;
            delete K[k]
        };
        this.registerDashedWord = function(f) {
            for (var j = 0; j < f.length - 1; ++j)
                for (var k = j + 2; k <= f.length; ++k) {
                    var n = f.slice(j, k);
                    K["_" + n.join("")] = n
                }
        };
        this.registerDefaultPick = function(f) {
            Pa = f ? ++Pa : 0
        };
        this.hideDefaultPickHint = function() {
            $a("hide_default_pick_hint",
                    "1")
        };
        this.showDefaultPickHint = function() {
            return Pa > 1 && w.hide_default_pick_hint != 1
        };
        this.isRegisteredDashedWord = function(f) {
            return K["_" + f] !== void 0
        };
        this.getDashedWordArray = function(f) {
            return K["_" + f]
        };
        this.convertDashedWordSelection = function(f) {
            var j = K["_" + f.word];
            if (j === void 0)
                return f;
            var k = -1, n = 0;
            do {
                ++k;
                n += j[k].length
            } while (n < f.selectionOffset && k < j.length - 1);
            j = j[k];
            k = f.start + n - j.length;
            var A = f.start + n;
            n = f.selectionOffset + j.length - n;
            var R = zb(j);
            return{word: j, start: k, end: A, selectionOffset: n, selectionStart: f.selectionStart,
                selectionEnd: f.selectionEnd, isEdge: n === 0 || n === j.length, wordType: R}
        };
        this.createTransliterationRequest = function(f) {
            var j = i.I.createSXHR();
            f = ua("/transliterate.ashx?word=" + ha(f) + l.getReferrerInfo("&"), "&");
            j.open(f);
            j.onreadystatechange = function() {
                l.processTransliterationResponse(j)
            };
            return j
        };
        this.processTransliterationResponse = function(f) {
            if (f.readyState != 4)
                return-1;
            if (f.status != 200)
                return 0;
            var j = eval("(" + f.responseText + ")");
            if (j.staleClient)
                l.resetYamli(true, j.serverBuild);
            else {
                f = j.w;
                j = j.r.split("|");
                if (j.length === 1 && j[0].length === 0)
                    j = [];
                for (var k = [], n = 0; n < j.length; ++n) {
                    var A = j[n].split("/");
                    k.push({trans: A[0], type: A[1]})
                }
                this.registerTransliteration(f, k);
                return 1
            }
        };
        this.savePrecache = function(f) {
            f = f.split(i.I.HasPunctuationRegexp);
            for (var j = ["1"], k = 0; k < f.length; ++k) {
                var n = f[k], A = zb(n);
                A = l.getTransliterations(A, n);
                if (A !== void 0) {
                    A = A.getRomanWord();
                    n != A && j.push(A + "|" + n)
                }
            }
            if (j.length != 1)
                return ha(j.join("/"))
        };
        this.loadPrecache = function(f) {
            if (!(!f || f.length === 0)) {
                f = decodeURIComponent(f).split("/");
                if (!(f[0] < 1))
                    for (var j = 1; j < f.length; ++j) {
                        var k = f[j].split("|");
                        ya(k[0], k[1])
                    }
            }
        };
        this.setInstancesEnabled = function(f) {
            for (var j = 0; j < B.length; ++j)
                B[j].setEnabled(f);
            i.I.setCookie("yamli_startMode", f ? "on" : "off", 1825)
        };
        this.getReferrerInfo = function(f) {
            var j = document.location;
            return f + "tool=" + ha(O.tool) + "&account_id=" + O.accountId + "&prot=" + ha(j.protocol) + "&hostname=" + ha(j.hostname) + "&path=" + ha(j.pathname)
        };
        this.isAdLoaded = function() {
            return Ea
        };
        this.getAdIframeId = function() {
            var f = "yamli_spid_" + bb;
            ++bb;
            V[f] =
                    1;
            U || (U = Xb(Da, 100));
            return f
        };
        this.debugAds = Ic().yamli_debugAds;
        this.debugAds = this.debugAds === void 0 ? "" : this.debugAds;
        this.shouldEnableOnStart = function(f) {
            switch (f) {
                case "on":
                    return true;
                case "off":
                    return false
            }
            switch (i.I.getCookie("yamli_startMode")) {
                case "on":
                    return true;
                case "off":
                    return false
            }
            return f == "offOrUserDefault" ? false : true
        };
        this.getAdInfo = function() {
            return Q.adInfo
        };
        this.reportTyped = function() {
            if (!J) {
                J = true;
                this.sendOneWay(l.makeUrl("/report_typed.ashx" + l.getReferrerInfo("?"), "&"))
            }
        };
        this.reportUsed = function() {
            if (!x) {
                x = true;
                this.sendOneWay(l.makeUrl("/report_used.ashx" + l.getReferrerInfo("?"), "&"))
            }
        };
        this.reportImpression = function() {
            if (!ka) {
                ka = true;
                var f = l.getAdInfo();
                this.sendOneWay(l.makeUrl("/report_impression.ashx?sp_id=" + ha(f ? f.adId : "NONE") + l.getReferrerInfo("&"), "&"))
            }
        };
        this.reportImpressionTime = function() {
            if (!va) {
                va = true;
                var f = l.getAdViewElapsed();
                if (f !== 0) {
                    var j = l.getAdInfo();
                    this.sendOneWay(l.makeUrl("/report_impression_time.ashx?sp_id=" + ha(j ? j.adId : "NONE") + "&viewed_ms=" +
                            f + "&click_count=" + Na + l.getReferrerInfo("&"), "&"))
                }
            }
        };
        this.reportTransliterationSelection = function(f, j, k, n) {
            var A = f.getSelectedTransliteration();
            if (A !== void 0) {
                f = f.getRomanWord();
                for (var R = "", $ = 0, aa = j - 1; aa >= 0; --aa) {
                    j = k.charAt(aa);
                    if (Ac.hasOwnProperty(j)) {
                        if (j !== " " && j != "-")
                            break;
                        ++$;
                        if ($ > 6)
                            break
                    }
                    R = j + R
                }
                this.sendOneWay(l.makeUrl("/report_selection.ashx?roman_word=" + ha(f) + "&selection=" + ha(A) + "&auto=" + (n ? "true" : "false") + "&prevText=" + ha(R) + l.getReferrerInfo("&"), "&"));
                this.reportUsed();
                return true
            }
        };
        this.makeInstanceId =
                function() {
                    ++P;
                    return P
                };
        this.sync = function() {
            for (var f = [], j, k = 0; k < B.length; ++k) {
                j = B[k].getSyncData();
                j.length && f.push(B[k].getId() + "-" + j)
            }
            if (f.length) {
                f = this.makeUrl("/sy.ashx?v=3&sd=" + f.join("**") + l.getReferrerInfo("&"), "&");
                if (f != Ca) {
                    this.sendOneWay(f);
                    Ca = f
                }
            }
        };
        this.setShowedHint = function() {
            if (Ba) {
                Ba = false;
                i.I.setCookie("yamli_showed_hint", "true", 1825)
            }
        };
        this.getShowHint = function() {
            return Ba
        };
        this.getShowPowered = function() {
            return Fa
        };
        this.appendChild = function(f) {
            ea.appendChild(f)
        };
        this.removeChild =
                function(f) {
                    ea.removeChild(f)
                };
        this.appendVisChild = function(f) {
            E.appendChild(f)
        };
        this.removeVisChild = function(f) {
            E.removeChild(f)
        };
        this.makeUrl = function(f, j) {
            return ua(f, j)
        };
        this.useRightLayout = function() {
            return Aa
        };
        this.fixTextZoom = function(f) {
            return f / W
        };
        this.getClientWidth = function() {
            if (typeof window.innerWidth == "number")
                return E.clientWidth;
            if (Ya && (Ya.clientWidth || Ya.clientHeight))
                return Ya.clientWidth;
            return ia.clientWidth
        };
        this.adViewInc = function() {
            if (ja === 0) {
                pa.start();
                i.I.addEvent(ia, "mousemove",
                        sa)
            }
            ja++;
            l.reportImpression()
        };
        this.adViewDec = function() {
            ja--;
            if (ja === 0) {
                pa.stop();
                i.I.removeEvent(ia, "mousemove", sa)
            }
        };
        this.getAdViewElapsed = function() {
            return pa.getElapsed()
        };
        this.adClickCountInc = function() {
            ++Na
        };
        this.addCheckinCallback = function(f) {
            Q ? f() : Y.push(f)
        };
        this.addWindowUnloadCallback = function(f) {
            z.push(f)
        };
        this.didMouseMove = function() {
            return Oa
        };
        this.clearMouseMoved = function() {
            Oa = false
        };
        this.isInitialized = function() {
            return a === true
        };
        this.getFontHeight = function(f, j) {
            var k = (f + "," + j).toLowerCase(),
                    n = Ia[k];
            if (n === void 0) {
                var A = G("div");
                A.innerHTML = "AM(LZ";
                A.style.position = "absolute";
                A.style.top = "0";
                A.style.left = "0";
                A.style.fontFamily = f;
                A.style.fontSize = j;
                l.appendVisChild(A);
                n = A.offsetHeight;
                l.removeVisChild(A);
                Ia[k] = n
            }
            return n
        };
        this.setInitParams = function(f, j) {
            if (a !== void 0)
                return a;
            D = document.location.protocol == "https:" ? "https://" : "http://";
            za(f, true, false);
            lc();
            if (Za == 5) {
                if (j) {
                    var k = document.getElementById(j);
                    if (k) {
                        k.innerHTML = ac;
                        k.style.display = "block"
                    }
                }
                return a = false
            }
            i.domReady = new i.DomReady;
            1 || O.assumeDomReady ? Ja() : i.domReady.addCallback(Ja);
            return a = true
        };
        this.setGlobalOptions = za;
        this.setupInstancesById = function(f, j, k) {
            if (k)
                c[f] = {formOverride: void 0};
            else if (!c.hasOwnProperty(f))
                return;
            k = c[f];
            if (j === void 0)
                j = {};
            for (var n in j)
                if (j.hasOwnProperty(n))
                    k[n] = j[n];
            this.removeInstanceById(f, false);
            Q={};
            Q !== void 0 && Ka(f, k)
        };
        this.removeInstanceById = function(f, j) {
            for (var k = 0, n; k < B.length; ) {
                n = B[k];
                if (n.yamlifyId == f) {
                    n.unload();
                    bc(B, k)
                } else
                    ++k
            }
            j && c.hasOwnProperty(f) && delete c[f]
        };
        this.setupInstancesByClass =
                function(f, j, k) {
                    if (k)
                        b[f] = {formOverride: void 0};
                    else if (!b.hasOwnProperty(f))
                        return;
                    k = b[f];
                    if (j === void 0)
                        j = {};
                    for (var n in j)
                        if (j.hasOwnProperty(n))
                            k[n] = j[n];
                    this.removeInstancesByClass(f, false);
                    Q !== void 0 && wa(f, k)
                };
        this.removeInstancesByClass = function(f, j) {
            for (var k = 0, n; k < B.length; ) {
                n = B[k];
                if (n.yamlifyClass == f) {
                    n.unload();
                    bc(B, k)
                } else
                    ++k
            }
            j && b.hasOwnProperty(f) && delete b[f]
        };
        this.setupInstancesByType = function(f, j, k) {
            if (k)
                q[f] = {formOverride: void 0};
            else if (!q.hasOwnProperty(f))
                return;
            k = q[f];
            if (j ===
                    void 0)
                j = {};
            for (var n in j)
                if (j.hasOwnProperty(n))
                    k[n] = j[n];
            this.removeInstancesByType(f, false);
            Q !== void 0 && ib(f, k)
        };
        this.removeInstancesByType = function(f, j) {
            for (var k = 0, n; k < B.length; ) {
                n = B[k];
                if (n.yamlifyType == f) {
                    n.unload();
                    bc(B, k)
                } else
                    ++k
            }
            j && q.hasOwnProperty(f) && delete q[f]
        };
        this.getInstances = function() {
            return B.slice()
        };
        this.resetYamli = lb
    })
}();
