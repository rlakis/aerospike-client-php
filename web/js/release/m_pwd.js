(function(h){h.fn.passStrengthify=function(a){var q=h(this),m,r=[ [0,8,16,32,48,64,72],[0,16,32,48,64,78,92],[0,32,64,78,92,108,128] ],s=["Very weak","Very weak","Weak","Weak","Moderate","Good","Strong","Very strong"],n=["gray","red","red","#C00000","orange","#0099FF","blue","green"],i=r[0],u=h("<span>").css("margin-left","1em"),t=[],e=0,v=0,w=false,B=function(b){var c=0,d={"[a-z]":26,"[A-Z]":26,"(\\d[^\\d])|(^\\d+$)":10,"[\\W_]":32};b=b.replace(/(.)(\1)(\1)+/gi,"$1$2");b=b.replace(/(a)(b(c(d(e(f(g(h(i(j(k(l(m(n(o(p(q(r(u(v(w(x(y(z)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?)?/gi,"$1");b=b.replace(/(0)(1(2(3(4(5(6(7(8(9)?)?)?)?)?)?)?)?)?/g,"$1");b=b.replace(/(1)(2(3(4(5(6(7(8(9(0)?)?)?)?)?)?)?)?)?/g,"$1");b=b.replace(/([^\d])(\d)(\d)+$/,"$1$2");if(!b.length)return 0;for(var j in d)if(b.search(RegExp(j))!=-1)c +=d[j];if(!c)return 0;for(j=d=0;j < b.length;j++){var f;var k=b,o=j;f=c;var l=k.charAt(o),y=[0.08064249900208098,0.015373768624831691,0.026892340312538593,0.04328667139002636,0.1288623426065769,0.0244847137116921,0.019625534749730816,0.06098726796371807,0.06905550211598431,0.0011176940633901926,0.006252182367878119,0.04101676132771116,0.02500971934780021,0.06984975410235668,0.07378315126621263,0.017031440203182008,0.0010648594165322703,0.06156572691936394,0.063817324270356,0.09024664994930598,0.0278568510204016,0.010257964235274787,0.021192261444145363,0.0016941732664605912,0.01806326249861108,9.695838238376564E-4],z=[0.11617102232902775,0.04708120556723741,0.035155702413137084,0.02673475518173626,0.020026033843997197,0.0378391909482327,0.01952538299789727,0.07241413837989387,0.06294182437168319,0.006318213677781116,0.006908981676179033,0.027085210774006212,0.04379693601682187,0.02368078502052669,0.06272153799939922,0.025483128066486435,4.305597276459397E-4,0.016551516972063685,0.07765094623009913,0.16692700510663863,0.014889356163011918,0.006198057474717133,0.06669670571743266,5.0065084609993E-5,0.01622108741363773,5.0065084609993E-4],A=[2.835E-4,0.0228302,0.0369041,0.042629,0.0012216,0.0075739,0.0171385,0.0014659,0.0372661,2.353E-4,0.0110124,0.0778259,0.0260757,0.2145354,5.459E-4,0.0195213,1.749E-4,0.110477,0.093429,0.131796,0.0098029,0.0306574,0.0088799,9.562E-4,0.0233701,0.0018701,0.0580027,0.0058699,7.91E-5,0.0022625,0.3416714,2.057E-4,4.272E-4,3.639E-4,0.0479084,0.0076894,0,0.115056,0.0012816,3.481E-4,0.0966553,1.58E-5,0,0.0740301,0.0226884,0.010743,0.1196127,0.001155,3.16E-5,0,0.0864502,0,0.1229841,2.71E-5,0.0215451,5.246E-4,0.1715916,9.0E-6,0,0.1701716,0.056549,0,0.0453966,0.0488879,0,3.62E-5,0.1759242,9.0E-6,0.0017185,0.0376812,0.0010492,0.0906756,0.0358361,0,0,0,0.0041969,9.0E-6,0.0280345,5.057E-4,2.585E-4,0.0081086,0.1224833,6.799E-4,0.0054844,7.08E-4,0.0794902,3.484E-4,1.911E-4,0.0092662,0.0021466,0.0030456,0.0397283,1.63E-4,2.25E-5,0.0178918,0.0307037,9.159E-4,0.0178805,0.0027759,0.0013655,0,0.0076478,0,0.0545873,0.0012798,0.0224322,0.0843434,0.0317097,0.008564,0.0052834,0.0017762,0.0127186,2.605E-4,0.0010967,0.0339975,0.0186268,0.0815271,0.0032334,0.0101307,0.0021424,0.1307517,0.0712793,0.0241537,0.0014289,0.0157312,0.0070879,0.0105139,0.0125997,1.831E-4,0.0638579,2.384E-4,3.179E-4,2.086E-4,0.0928264,0.0500293,1.99E-5,9.93E-5,0.0820576,0,1.99E-5,0.0266638,3.97E-5,8.94E-5,0.1545186,1.689E-4,9.9E-6,0.0825344,0.0039539,0.034194,0.0334986,9.9E-6,1.987E-4,0,0.00152,0,0.0592435,3.842E-4,5.205E-4,0.0020078,0.1482326,2.727E-4,0.0101631,0.1420108,0.0501091,2.48E-5,3.72E-5,0.0395122,0.002987,0.0127906,0.0573224,5.577E-4,0,0.0884686,0.0261142,0.0062466,0.0256309,3.72E-5,3.47E-4,0,0.003272,1.363E-4,0.1580232,7.737E-4,0.002046,5.185E-4,0.4597035,4.627E-4,3.59E-5,7.18E-5,0.1252667,0,4.0E-6,0.0014278,0.0013042,0.0012922,0.0700557,4.39E-5,3.191E-4,0.0117178,0.0022056,0.0297253,0.0131497,0,0.001029,0,0.0072309,0,0.0166996,0.0069144,0.0486793,0.0363474,0.0480664,0.0271435,0.0307856,7.75E-5,4.826E-4,3.5E-6,0.0073125,0.0526842,0.0412929,0.2618995,0.0497818,0.0062698,4.333E-4,0.043762,0.1157982,0.1198384,7.01E-4,0.0235788,2.11E-5,0.001881,0,0.0032265,0.2106638,0,0,0,0.190642,0,0,0,4.353E-4,0,0,0,0,0,0.2644178,0,0,0,0,0,0.3299238,0,0,0,2.176E-4,0,0.0169234,0.0011671,5.058E-4,0.0017118,0.3321662,0.0041628,4.669E-4,7.781E-4,0.1300965,0,3.112E-4,0.0185963,9.726E-4,0.100957,0.0113601,0.001206,0,4.279E-4,0.0613523,0.0022954,0.0029956,0,0.0041239,0,0.0086757,0,0.10168,5.515E-4,0.0020459,0.0668636,0.1657445,0.0134024,0.0011801,1.542E-4,0.1107889,1.19E-5,0.0053728,0.135518,0.0055389,9.726E-4,0.0826499,0.0022654,5.9E-6,0.0018443,0.0230153,0.0180635,0.0144461,0.004163,0.0025797,0,0.0968765,2.37E-5,0.1539307,0.0285939,1.653E-4,0.0025384,0.2496134,0.0017798,1.95E-5,3.015E-4,0.0877464,1.95E-5,0,0.0015756,0.0221846,0.0029567,0.1098532,0.0485124,0,0.016991,0.0249954,8.461E-4,0.0385435,2.92E-5,1.167E-4,0,0.0505257,0,0.0240107,5.432E-4,0.0423173,0.1767352,0.0849166,0.0053036,0.1188694,0.0028799,0.0295789,0.0012223,0.0071353,0.0087755,6.582E-4,0.0085073,0.0653564,3.343E-4,9.716E-4,4.144E-4,0.0427003,0.0956004,0.0093814,0.00335,8.497E-4,3.343E-4,0.012115,1.288E-4,0.0083175,0.0072923,0.0127087,0.0203076,0.0029439,0.1135873,0.0060659,0.0018527,0.0087857,1.978E-4,0.0106912,0.0268647,0.0580447,0.1459838,0.0330625,0.0138659,2.308E-4,0.1175433,0.032268,0.0492657,0.1337201,0.0164801,0.0488371,5.374E-4,0.0033923,8.571E-4,0.1284508,4.427E-4,4.427E-4,4.713E-4,0.2213542,1.428E-4,8.57E-5,0.0221226,0.0538854,2.86E-5,1.143E-4,0.0957597,0.0010854,5.856E-4,0.1212242,0.0607692,0,0.1362487,0.0222939,0.0408603,0.0270926,0,0.0011711,0,0.0042274,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2.284E-4,2.284E-4,0,0.9949749,0,0,0,0,0,0.0733524,0.0032081,0.0116789,0.028407,0.234553,0.0056616,0.0107385,0.0026432,0.0792432,4.35E-5,0.0087196,0.0117263,0.0192448,0.0221961,0.0919374,0.0048043,3.16E-5,0.0189406,0.0459213,0.0421561,0.0173721,0.0070603,0.0019873,4.0E-6,0.0284504,0.0055945,0.0349781,6.441E-4,0.0157796,0.0015208,0.1179849,0.0010558,4.688E-4,0.0569819,0.0506053,4.95E-5,0.005378,0.0114497,0.006552,0.0022488,0.0491264,0.0287844,8.309E-4,1.906E-4,0.0463897,0.1269191,0.0330152,8.0E-5,0.0053856,0,0.0020925,0,0.0393295,1.59E-4,0.0037195,6.74E-5,0.0892434,9.218E-4,4.04E-5,0.3352928,0.0666758,5.4E-6,1.62E-5,0.0146273,9.11E-4,0.0011051,0.0913053,8.09E-5,2.7E-6,0.0310281,0.0245378,0.0171177,0.0185732,2.7E-6,0.0078702,0,0.0121422,2.776E-4,0.0261517,0.0181796,0.0459729,0.0223272,0.0308931,0.0058765,0.0505571,6.99E-5,0.0298191,8.7E-6,1.572E-4,0.1066327,0.0308669,0.1156002,0.002017,0.0448465,1.746E-4,0.1626908,0.1207345,0.1249869,3.49E-5,9.343E-4,2.008E-4,8.819E-4,2.969E-4,0.0010042,0.1022242,0,0,0.0049559,0.6796927,0,0,2.371E-4,0.1467561,0,0,1.423E-4,0,0.0128284,0.0429195,0,0,8.299E-4,3.083E-4,0,0.0025847,5.928E-4,0,0,0.0038888,0,0.1832539,3.329E-4,2.984E-4,0.0018938,0.1605624,0.0013085,3.44E-5,0.1893372,0.1788924,0,5.05E-4,0.0089412,2.755E-4,0.0372798,0.0933831,8.03E-5,1.15E-5,0.0082066,0.0126485,0.0018135,0.0011707,0,3.214E-4,0,6.887E-4,0,0.0600144,0,0.1573582,0.001005,0.05542,0,1.436E-4,0.0132089,0.1122757,0,0,0.0014358,1.436E-4,0,0.0055994,0.2157933,0.0031587,0,0.0027279,0.2360373,0.0195262,0.0051687,1.436E-4,0.0093324,0.0020101,0,0.0072178,0.0039321,0.0011985,0.0020738,0.0562745,0.0015217,3.097E-4,7.137E-4,0.0141393,1.35E-5,2.69E-5,0.0031914,0.0039051,0.0022488,0.1205478,0.0027875,0,0.0048882,0.0324935,0.0109613,5.925E-4,6.73E-5,0.0016025,1.347E-4,9.43E-5,2.02E-4,0.4219769,7.526E-4,0.0060211,0.0067737,0.3038133,0,0,5.018E-4,0.0709985,2.509E-4,0,0.0198194,0,0,0.0730055,0,0,2.509E-4,0.0017561,5.018E-4,0.0037632,0.0010035,0,0,0.0100351,0.026844],p={"1":"l","3":"e","4":"a","5":"s","7":"t","@":"a",$:"s"};if(typeof p[l] !="undefined")l=p[l];if(l.match(/^[a-zA-Z]$/)){l=l.toLowerCase().charCodeAt(0)- "a".charCodeAt(0);var g=void 0;g=null;if(o){g=k.charAt(o - 1);if(typeof p[g] !="undefined")g=p[g]}if(g !=null&&g.match(/^[a-zA-Z]$/)){k=void 0;k=g.toLowerCase().charCodeAt(0)- "a".charCodeAt(0);g=A[l + 26 * k]}else g=(o?y:z)[l];if(f >=26)g *=26 / f;f=g}else f=1 / f;d +=Math.max(-7,Math.log(f)/ 0.6931471805599453)}return -1 * d},C=function(b){var c=0,d=0;d=B(b);if(i.length&&i[0] instanceof RegExp)jQuery.each(i,function(j,f){c +=b.search(f)!=-1});else for(e=0;e < i.length;e++){if(d < i[e])break;c=e + 1}return [d,c]},x=function(){var b=h(this).val(),c=!b.length||b.length < v,d;b=C(b);d=b[0];b=c?0:b[1];var j=t.length,f,k;k=c?a.labels.tooShort:w?Math.round(d * 100)/ 100 + " bits":s[b];f=n[b];c=n[b];d=n[0];u.text(k).css("color",f);for(e=0;e < j;e++)t[e].css("background-color",e < b?c:d)};if(typeof a=="undefined")a={};if(typeof a.labels==="undefined")a.labels={};a.labels=h.extend({tooShort:"Too short",passwordStrength:"Password strength:"},a.labels);m=h("<span>").css("display","inline-block").addClass("passStrengthify");return h(this).each(function(){a.element?a.element.append(m):h(this).parent().append(m);if(a.minimum)v=a.minimum;if(typeof a.security=="undefined")a.security=1;if(a.security >=0&&a.security < r.length)i=r[a.security];if(!a.levels)a.levels=s;if(!a.colours)a.colours=n;if(!a.tests)a.tests=i;if(a.levels&&a.colours&&a.tests)if(a.levels.length==a.colours.length&&a.colours.length==a.tests.length + 1){s=a.levels;n=a.colours;i=a.tests}if(a.rawEntropy)w=true;m.append(h("<div>").append(h("<span>").css("font-size","smaller").text(a.labels.passwordStrength).append(u)));var b=Math.round((125 - 3 * i.length)/ i.length);for(e=0;e < i.length;e++){var c=h("<span>").css("height","3px").css("width",b + "px").css("margin-right","3px").css("max-height","3px").css("font-size","1px").css("float","left");t.push(c);m.append(c)}q.keypress(x);q.keyup(x);q.trigger("keyup")})}})(jQuery);var pOpts={minimum:6,labels:{tooShort:(lang=='ar'?'الحد الأدنى 6 أحرف':'minimum of 6 characters'),passwordStrength:""},element:$('#pout')};if(lang=='ar'){pOpts['levels']=['ضعيف جداً','ضعيف جداً','ضعيف','ضعيف','لا بأس','جيد','قوي','قوي جداً']}$("#pwd").passStrengthify(pOpts);