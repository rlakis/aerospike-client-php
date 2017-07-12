(function(){var sh=document.createElement('script');sh.type='text/javascript';sh.async=true;sh.src=uhc+'/min.js';
        sh.onload=sh.onreadystatechange=function(){
        if (!HSLD && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete')){
            HSLD=1;  
       $.ajax({
           type:'POST',
            url:'/ajax-ga/',
            data:{
                ads:1
            },
            dataType:'json',
            success:function(rp){
                if(rp.RP){ 
                    if(rp.DATA.d){
                        var x =$('#statDv');
                        var gS={
                            chart: {
                                spacingRight:0,
                                spacingLeft:0
                            },
                            title: {
                                text: (lang=='ar'?'الاعلانات الفعالة':'active ads'),
                                style:{
                                    'font-weight':'bold',
                                    'font-family':(lang=='ar'?'tahoma,arial':'verdana,arial'),
                                    'direction':(lang=='ar'?'rtl':'ltr')
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
                            colors:[
                                '#2f7ed8',
                                '#ff9000'
                            ]
                        };
                        var series;
                        if(typeof(rp.DATA.k) === 'undefined'){
                            series = [{
                                type: 'line',
                                name: 'Impressions',
                                pointInterval:24 * 1000,
                                pointStart: rp.DATA.d,
                                data: rp.DATA.c
                            }];
                        }else{
                            series = [{
                                type: 'line',
                                name: 'Impressions',
                                pointInterval:24 * 3600 * 1000,
                                pointStart: rp.DATA.d,
                                data: rp.DATA.c
                            },
                            {
                                type: 'line',
                                name: 'Interactions',
                                pointInterval:24 * 3600 * 1000,
                                pointStart: rp.DATA.d,
                                data: rp.DATA.k
                            }
                            ];
                        }
                        gS['series']=series;
                        x.highcharts(gS);
                    }else{
                        var x=$('#statDv');
                        x.removeClass('load');
                        x.addClass('hxf');
                        x.html(lang=='ar'?'لا يوجد إحصائية عدد مشاهدات للعرض':'No impressions data to display');
                        trePic();
                    }
                }else{
                    var x=$('#statDv');
                    x.removeClass('load');
                    x.addClass('hxf');
                    x.html(lang=='ar'?'فشل محرك مرجان بالحصول على الاحصائيات':'Mourjan system failed to load statistics');
                }
            }
       });
   }};head.insertBefore(sh,head.firstChild)})();