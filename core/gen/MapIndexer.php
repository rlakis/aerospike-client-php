<?php
$root_path = dirname(dirname(dirname(__FILE__)));
include $root_path.'/config/cfg.php';
require_once $config['dir'] . '/core/model/Db.php';

class MapIndexer{
    
    var $lang = '';
    
    function MapIndexer($lang='ar'){
        global $config;
        $this->lang=$lang;
        
        $db = new DB($config);
        $db_instance = $db->getInstance();
        $stmt = $db_instance->prepare('select c.id,c.name_'.($this->lang=='ar' ? 'en':'ar').',c.latitude,c.longitude from city c left join country d on d.id=c.country_id where c.name_'.$this->lang.' = \'\' and d.blocked=0 and c.id > 7715 order by c.id');
        $stmt->execute();
        ?><html><head>
                <script src="http://dev.mourjan.com/web/jquery/1.10.2/js/jquery.min.js"></script>
                <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBNSe6GcNXfaOM88_EybNH6Y6ItEE8t3EA&v=3.exp&sensor=false&language=<?= $this->lang ?>"></script>
                <script type="text/javascript">
                    var running=1;
                    var cities=[<?php
                    $i=0;
                    while (($city = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
                        if($i++)echo ',';
                        echo '['.$city[0].','.$city[2].','.$city[3].']';
                    }
            
                    ?>];
                    var geo = new google.maps.Geocoder();
                    var fail=0;
                    var idx=0;
                    function pego(){
                        if (typeof cities[idx] === 'undefined'){
                            console.log(' ');
                            console.log('------------------------------');
                            console.log('Processed: '+idx);
                            console.log('Failed: '+fail);
                            return 0;
                        }
                        var lat = cities[idx][1];
                        var lon = cities[idx][2];
                        var pos=new google.maps.LatLng(lat,lon);
                        geo.geocode(
                            {latLng:pos},
                            function(res, status) {
                                if (status == google.maps.GeocoderStatus.OK && res[0]) {
                                    console.log(cities[idx][0]+' Pass');
                                    cacheLoc(res);  
                                 }else{
                                    console.log(cities[idx][0]+' Fail');
                                    fail++;
                                    idx++;
                                    //if(running)setTimeout('pego();',2000);
                                 }
                            }
                        );
                    }
                    function cacheLoc(loc){
                        var obj=[];
                        var l=loc.length;
                        var k=0;
                        for (var i=l-1;i>=0;i--) {
                            obj[k]={latitude:loc[i].geometry.location.lat(),longitude:loc[i].geometry.location.lng(),type:loc[i].types[0],name:loc[i].address_components[0].long_name,short:loc[i].address_components[0].short_name,formatted:loc[i].formatted_address};
                            k++;
                        }
                        $.ajax({
                            url:'/ajax-location/',
                            data:{
                                lang:'<?= $this->lang ?>',
                                loc:obj
                            },
                            type:'POST',
                            success:function(rp){
                                idx++;
                                if(running)setTimeout('pego();',2000);
                            }
                        });
                    }
                    if(cities && cities.length){
                        pego();
                    }
        </script>
            </head><body><button onclick="running=0;">Stop</button></body>
        </html><?php
        $db->close();
    }
}
?>