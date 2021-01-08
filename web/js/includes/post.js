var Ed, HAS_WEBP=hasWebP();
Element.prototype.query=function(sel){return this.querySelector(sel);};
Element.prototype.queryAll=function(sel){return this.querySelectorAll(sel);};
$.addEventListener("DOMContentLoaded",function(e){
    if (typeof $$==='undefined') { let $$=document.body; }
    UI.adForm=$.body.query('form#adForm');
    UI.adClass=$.body.query('div#ad-class');
    UI.ar=$.body.dir==='rtl';
    UI.init();
});
$.onkeydown=function(e){if(e.key==='Escape'){UI.close();}};

String.prototype.howArabic = function () {
    var result, match, str = this;
    // strip punctuation, digits and spaces
    str = str.replace(/[\u0021-\u0040\s]/gm, '');
    match = str.match(/[\u0621-\u0652]/gm) || [];
    result =  match.length / str.length;
    return result;
};

String.prototype.howNotArabic = function () {
    var result, match, str = this;
    // strip punctuation, digits and spaces
    str = str.replace(/[\u0021-\u0040\s]/gm, '');
    match = str.match(/[^\u0621-\u0652]/gm) || [];	
    result =  match.length / str.length;
    return result;
};


String.prototype.isArabic = function (threshold) {	
    threshold = threshold || 0.79;
    return this.howArabic() >= threshold;
};

String.prototype.hasArabic = function () {
  return /[\u0621-\u064A]/.test(this);
};

String.prototype.removeTashkel = function () {
    return this.replace(/[\u064B-\u0652]/gm, '');
};

String.prototype.removeNonArabic = function () {
    return this.replace(/[^\u0621-\u0652]/gm, '');
};

String.prototype.removeArabic = function () {
    return this.replace(/[\u0621-\u0652]/gm, '');
};


var MAP={
    view:null,
    marker:null,
    coder:null,
    infoWindow:null,
    result:null,
    
    init:function(){
        let _=this;
        _.view = new google.maps.Map($.query('#gmapView'), {
            center: {lat: parseFloat(UI.ip.ipquality.latitude), lng: parseFloat(UI.ip.ipquality.longitude)},
            zoom: 12
        });
        _.coder = new google.maps.Geocoder();
        google.maps.event.addDomListener(_.view, "click", function(e) {
            _.coder.geocode({"latLng": e.latLng}, function(results, status) {
                if (status===google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        _.result=results;
                        _.marker.setPosition(e.latLng);
                        _.setInfo(results);
                        //cacheLoc(results);
                    }
                } 
                else {
                    //mapQ.css("color","#ff0000");
                }
            });
        });
                        
        _.infoWindow = new google.maps.InfoWindow;
        _.marker =  new google.maps.Marker();
        _.marker.setMap(_.view);
        _.marker.setAnimation(google.maps.Animation.DROP);
        if (Ad.content.lat!==0||Ad.content.lon!==0){
            _.adLocation();
        }
        else {
            _.myLocation();
        }
    },
    
    handleLocationError:function(browserHasGeolocation, infoWindow, pos){
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation?'Error: The Geolocation service failed.':'Error: Your browser doesn\'t support geolocation.');
        infoWindow.open(this.view);
    },
    
    adLocation:function(){
        let pos = {lat: Ad.content.lat, lng: Ad.content.lon};        
        //this.coder = new google.maps.Geocoder();
        //if(this.marker){this.marker.setMap(null);}
        
        //this.marker = new google.maps.Marker({position: pos, map: this.view, animation: google.maps.Animation.DROP, title:Ad.content.loc});
        
        //this.marker.setMap(this.view);
        this.view.setCenter(pos);
        this.getCoordAddress(pos, this);
        this.marker.setPosition(pos);
        this.marker.setTitle(Ad.content.loc);
    },
    
    myLocation:function(){
        let _=this;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let pos = {lat: position.coords.latitude, lng: position.coords.longitude};
                //if(this.marker){this.marker.setMap(null);}
                //_.marker = new google.maps.Marker({position: pos, map: _.view, animation: google.maps.Animation.DROP, title:'My Location'});
                _.view.setCenter(pos);
                _.getCoordAddress(pos, _);
                _.marker.setPosition(pos);
            }, function() { _.handleLocationError(true, _.infoWindow, _.view.getCenter()); });
        }
    },
    
    getText:function(results){
        if(typeof results==='object'){
            let i=(results.length>1&&results[0].types[0]==="route"&&results[0]["address_components"][1]["short_name"]!==results[1]["address_components"][0]["short_name"])?1:0;
            let adc=results[i].address_components;
            let len=adc.length;
            if(len>1&&adc[len-1]["short_name"]!=="IL"){
                let tmp="",res="";
                for (var j=len-1;j>=0;j--) {
                    let name=adc[j].long_name?adc[j].long_name:adc[j].short_name;
                    if (tmp!==name && adc[j].types[0]!=="locality"){
                        if(res){res=", "+res;};
                        res=name+res;
                        tmp=name;
                    }
                }
                if(results[0].formatted_address && results[0].formatted_address.length>res.length){
                    return results[0].formatted_address;
                }
                return res.length>0?res:null;
            }
        }
        return null;
    },
    
    setInfo:function(results){
        this.infoWindow.setContent(this.getText(results));
        this.infoWindow.open(this.view, this.marker);
        return true;
    },
               
    setZoom:function(type){
        let _=this;
        if(Number.isInteger(type)){
            _.view.setZoom(parseInt(type));
            return;
        }
        var cz=_.view.getZoom();
        switch(type){
            case "route":
                if(cz<15)_.view.setZoom(15);
                break;
            case "country":
                if(cz<7)_.view.setZoom(7);
                break;
            case "sublocality":
                if(cz<14)_.view.setZoom(14);
                break;
            default:
                if(cz<13)_.view.setZoom(13);
                break;
        }
    },
    
    setPosition:function(pos, zoom){
        this.setZoom((zoom?zoom:14));
        this.view.setCenter(pos);
        //this.marker.setMap(this.map);
        //let mpos = {lat: pos.lat(), lng: pos.lng()};
        this.marker.setPosition(pos);
    },

    getCoordAddress:function(latlng, current){
        let _=this;
        _.coder.geocode({'location': latlng}, function(results, status) {
            if (status==='OK') {
                if (results[0]) {
                    _.setZoom(15);
                    _.result=results;
                    _.setPosition(latlng);
                    _.setInfo(results);
                    if(current){ Ad.userLocation=results[0]; }
                } 
                else {
                    window.alert('No results found');
                }
            } 
            else {
                window.alert('Geocoder failed due to: ' + status);
            }
        });
    },
    
    confirm:function(){
        UI.addressChanged(this.result);
        UI.close();
    },
    
    remove:function(){
        this.result=null;
        UI.addressChanged(this.result);
        UI.close();
    },
    
    search:function(e){
        let _=MAP, q=e.query('input.searchTerm');
        if(q&&q.value){
            _.coder.geocode({address:q.value}, function(res, status) {
                if (status===google.maps.GeocoderStatus.OK&&res[0]) {
                    console.log(_.getText(res));
                    _.result=res;
                    _.setInfo(res);
                    //cacheLoc(res,1);
                    _.setPosition(new google.maps.LatLng(res[0].geometry.location.lat(), res[0].geometry.location.lng()));
                }
                else{
                    //fdT(q,0,'err');
                    //failM();
                }
            });
        }        
        return false;
    }
};

var UI={
    adForm:null,
    adClass:null,
    ar:true,
    ip:null,
    dic:null,
    region:null,
    map:null,
    photos:[],
    rootId:0,    
    purposeId:0,
    pixIndex:0,
    sessionID:null,
    version:'1.0.0',
    
    numbers:{1:null, 2:null},
    dialogs:{},
    
    init:function(){
        let _=this;
        if (_.adForm) {
            //console.log('/ajax-menu/?sections='+(_.ar?'ar':'en')+(_.adForm.dataset.id?'&aid='+_.adForm.dataset.id:''));       
            fetch('/ajax-menu/?sections='+(_.ar?'ar':'en')+(_.adForm.dataset.id?'&aid='+_.adForm.dataset.id:''), _options('GET'))
                .then(res=>res.json())
                .then(response => {
                    console.log(response);
                    if(response.success===1){
                        Ad.init();
                        let rs=response.result;
                        _.region=rs.regions;_.dic=rs.roots;_.ip=rs.ip;
                        if(rs.ad && rs.ad.hasOwnProperty('umc')){_.adForm.dataset.actCountry=rs.ad.umc;};
                        Prefs.init(rs.prefs);
                        for(i in _.dic){
                            _.dic[i].menu=[];
                            let m=_.dic[i].menu;
                            switch(i){
                            case '1':                            
                                m.push({'id':1, 'en':'Sell a property', 'ar':'عرض عقار للبيع'});
                                m.push({'id':2, 'en':'Offer a property for rent', 'ar':'عرض عقار للايجار'});
                                m.push({'id':8, 'en':"Offer a property for exchange", 'ar':"عرض عقار للمبادلة"});
                                m.push({'id':7, 'en':"Looking to buy a property", 'ar':"أبحث عن عقار للشراء"});
                                m.push({'id':6, 'en':"Looking to rent a property", 'ar':"أبحث عن عقار للإستئجار"});
                                break
                            case '2':
                                m.push({'id':1, 'en':"Sell a car", 'ar':"عرض سيارة للبيع"});
                                m.push({'id':7, 'en':"Looking to buy a car", 'ar':"أبحث عن سيارة للشراء"});
                                break;
                            case '3':
                                m.push({'id':3, 'en':"Place a job vacancy", 'ar':"إعلان عن وظيفة شاغرة"});
                                m.push({'id':4, 'en':"Looking for work", 'ar':"أبحث عن عمل"});
                                m.push({'id':5, 'en':"Offer special service", 'ar':"عرض خدمة خاصة"});
                                break;
                            case '4':
                                m.push({'id':5, 'en':"Service Advert", 'ar':"الإعلان عن خدمة"});
                                break;
                            case '99':
                                m.push({'id':1, 'en':"Sell an item" ,'ar':"عرض سلعة للبيع"});
                                m.push({'id':2, 'en':"Offer an item for rent", 'ar':"عرض سلعة للايجار"});
                                m.push({'id':8, 'en':"Offer an item for exchange", 'ar':"عرض سلعة للمبادلة"});
                                m.push({'id':7, 'en':"Looking to buy an item", 'ar':"أبحث عن سلعة للشراء"});
                                m.push({'id':6, 'en':"Looking to rent an item", 'ar':"أبحث عن سلعة للإستئجار"});
                                break;
                        }
                    }
                    console.log('UI.dic', _.dic);
                
                    let dc = decodeURIComponent($.cookie);
                    let ca = dc.split(';');
                    for (let i in ca) {
                        var c=ca[i];
                        while(c.charAt(0)===' '){c=c.substring(1);}
                        if(c.indexOf('PHPSESSID=')===0){_.sessionID=c.substring(10,c.length);break;}
                    }
                    console.log('key', $.body.dataset.key, 'sid', _.sessionID);
                    $.body.queryAll('span.pix').forEach(function(pix){_.photos.push(new Photo(pix));});
                    $.body.queryAll('textarea').forEach(function(txt){
                        txt.oninput=function(e){
                            console.log(e);
                            if(e.target)e=e.target;
                            let v=e.value;
                            if(v){
                                if(v.toString().isArabic(0.5)){
                                    if(e.className!=='ar'){ e.className='ar'; }
                                }
                                else{
                                    if(e.className!=='en'){ e.className='en'; }                                
                                }
                            }
                            else { 
                                e.className='';
                            }
                            e.style.height = 'auto';
                            e.style.height = (e.scrollHeight+20) + 'px';
                        };
                        txt.onchange=function(){
                            console.log('textarea.onchange', this);
                            if(this.id==='natural'){
                                Ad.natural=this.value;
                            }
                            else {
                                Ad.foreign=this.value;
                            }
                            console.log(Ad);
                        };
                    });
                    
                    $.body.queryAll('input[type=tel]').forEach(function(t){_.numbers[t.dataset.no]=new ContactNumber(t);});
                
                    let mail=$.body.query('input[type=email]');
                    mail.onchange=function(){
                        if(this.checkValidity()){
                            Ad.email=this.value;
                        }
                        else {
                            console.log('invalid email');
                        }
                    };
                
                    if(rs.ad){Ad.parse(rs.ad);}
                }
            })
            .catch(error => { 
                console.log(error);
            });
        }
    },
    
    submit:function(e){
        let form=e.closest('form');
        console.log(form.checkValidity());
        if(!form.query('input[type=email]').checkValidity()){            
            form.query('input[type=email]').reportValidity();
        }
        
        form.queryAll('input[type=tel]').forEach(function(tel){
            let telType=tel.closest('li').query('select.select-text');
            console.log(tel.value, tel.validity, telType.value);
        });
        
        
        /*
        if (!form.checkValidity()) {
            form.insertAdjacentHTML( "afterbegin", "<ul class='error-messages'></ul>" );
            var invalidFields = form.querySelectorAll( ":invalid" ),
            listHtml = "",
            errorMessages = form.querySelector( ".error-messages" ),
            label;

            for ( var i = 0; i < invalidFields.length; i++ ) {
                console.log(invalidFields[i]);
                let label = invalidFields[i];//form.querySelector( "label[for=" + invalidFields[ i ].id + "]" );
                listHtml += "<li>" + 
                    label.innerHTML +
                    " " +
                    invalidFields[ i ].validationMessage +
                    "</li>";
            }

            // Update the list with the new error messages
            errorMessages.innerHTML = listHtml;

            // If there are errors, give focus to the first invalid field and show
            // the error messages container
            if ( invalidFields.length > 0 ) {
                invalidFields[ 0 ].focus();
                errorMessages.style.display = "block";
            }
        }*/
        return false;
    },
          

    guid:function(){
        function s4(){return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);}
        return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
    },
        
    
    createDialog:function(name, fullW, fullH){
        let dialog=createElem('div', 'modal');
        dialog.setAttribute('id', name);
        dialog.dataset.views='0';
        dialog.dataset.fullWidth=fullW;
        dialog.dataset.fullHeight=fullH;
        let card=createElem('div', 'card col-'+((dialog.dataset.fullWidth==='true')?'12':'8'));
        if(dialog.dataset.fullHeight==='true'){
            card.style.setProperty('padding-top', '0');
            card.style.setProperty('padding-bottom', '0');
            card.style.setProperty('height', window.innerHeight+'px');
        }
        if(dialog.dataset.fullWidth==='true' && dialog.id!=='regions'){
            card.style.setProperty('padding-left', '0');
            card.style.setProperty('padding-right', '0');            
        }
        let X = createElem('span', 'close', '&times;', true);
        if(name==='map'){                      
            var script=$.createElement('script');script.type="text/javascript";
            script.src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCXdUTLoKUM4Dc8LtMYQM-otRB2Rn59xXk&sensor=true&callback=MAP.init&language="+(UI.ar?'ar':'en');           
            $.body.append(script);
            X.style.display='none';
        }
        X.onclick = this.close;
        if (this._top) {
            X.className = 'close nopix';
            this._top.append(X);
        }
        else {
            card.append(X);
        }
        dialog.append(card);
        this.dialogs[name]=dialog;
        return dialog;
    },
    
    showDialog:function(dialog, photo){
        $.body.classList.add('modal-open');
        $.body.append(dialog);  
        dialog.style.display="flex";
        dialog.dataset.views=parseInt(dialog.dataset.views)+1;
        dialog.style.setProperty('max-height', window.innerHeight+'px');
        let card=dialog.query('div.card');
        let fw=(dialog.dataset.fullWidth==='true');
        let fh=(dialog.dataset.fullHeight==='true');
        let X=card.query('span.close');
        if(!fw && fh){            
            X.style.setProperty('top', '0px');
        }
        else if(fw && !fh){
            X.style.setProperty('top', '-42px');
            X.style.setProperty('right', '8px');
        }
        

        if(dialog.id==='map' && (Ad.content.lat!==0||Ad.content.lon!==0) && dialog.dataset.views>'1'){ MAP.adLocation(); }
        if(photo){
            let cw=$.body.clientWidth;            
            let img=card.query('span.pix img');
            let spn=img.closest('span');
            spn.style.height=Math.round((cw/2)/1.5)+'px';
            let hh=spn.offsetHeight, ww=spn.offsetWidth;
            img.style.setProperty('width', (photo.isPortrait()?hh:ww)+'px');
            img.style.setProperty('height', (photo.isPortrait()?ww:hh)+'px');
            img.style.setProperty('transform', 'rotate('+photo.rotation+'deg)');
        }
    },
    
    chooseRootPurpose:function(){
        let _=this, dialog, card;
        if(!_.dialogs.roots){
            dialog=_.createDialog('roots', false, false);
            card=dialog.query('div.card');
            for(let i in _.dic) {
                let div=createElem('div');
                div.append(createElem('h6','',_.dic[i].name));
                let ul=createElem('ul');
                for(let j in _.dic[i].menu){
                    let item= _.dic[i].menu[j];                
                    let li=createElem('li', '', item[_.ar?'ar':'en']);
                    li.dataset.ro=i;li.dataset.pu=item.id;
                    li.onclick=function(e){
                        _.rootId=e.target.dataset.ro;
                        _.purposeId=e.target.dataset.pu;
                        _.chooseSection();
                    };
                    ul.append(li);
                }
                div.append(ul);
                card.append(div);
            }
            _.dialogs.roots=dialog;
        }
        else {
            dialog=_.dialogs.roots;            
        }
        _.showDialog(dialog);        
    },
    
    chooseSection:function(){
        let _=this, dialog, card, ref='sec-'+_.rootId, r=_.dic[_.rootId];
        if(!r){return;}
        _.close();
        if(!_.dialogs[ref]){
            console.log('chooseSection', Ad.purposeId, _.purposeId);
            let tail=[];
            dialog=_.createDialog(ref, false, true);
            card=dialog.query('div.card');                      
            let ul=createElem('ul');
            ul.style.setProperty('height', window.innerHeight+'px');   
            for(var i in r.sindex){
                let se=r.sindex[i];
                if(!Prefs.isBlockedSection(se) && r.sections[se]){
                    if(!Prefs.canPostToCountry(Prefs.carrierCountryId, se, _.purposeId)&&!Prefs.canPostToCountry(Prefs.activationCountryId, se, _.purposeId))continue;
                    if(Prefs.getMovedSection(se))continue;
                    let li=createElem('li', '', r.sections[se]);
                    li.dataset.se=r.sindex[i];
                    li.onclick=function(e){
                        Ad.setClassification(_.rootId, e.target.dataset.se, _.purposeId);
                        _.close();
                        Ad.log();
                    };
                    if(Prefs.isTailSection(se)){tail.push(li)}else{ul.append(li);}
                }
            }
            tail.forEach(function(li){ul.append(li)});
            card.append(ul);
        }
        else {
            dialog=_.dialogs[ref];
        }
        _.showDialog(dialog);
    },
    
    openMap:function(){
        let _=this, dialog, card; _.close();
        if(!_.dialogs.map){
            dialog=_.createDialog('map',true,true);
            card=dialog.query('div.card');
            let b=$.body.query('#adLocation');
            card.append(b);
            b.style.display='flex';
        }
        else { dialog=_.dialogs.map; }
        _.showDialog(dialog);
    },
    
    
    chooseRegions:function(){
        if(!(Ad.sectionId>0)){return;}
        let _=this, dialog, card; _.close();
        if(!_.dialogs.regions){
            dialog=_.createDialog('regions',true,false);
            card=dialog.query('div.card');
            let blocks={cn:createElem('ul'), sa:createElem('ul'), ae:createElem('ul'), kj:createElem('ul'), ot:createElem('ul')};
            let ct1=createElem('div'); ct1.style.cssText='display:inline-flex;width:100%;align-items:flex-start;';
            card.append(ct1);
            ct1.append(blocks.cn, blocks.ae, blocks.sa, blocks.kj, blocks.ot);
                        
            let onf=function(e,all){
                console.log('Publish Level', Prefs.getPublishLevel(), 'regions', _.dialogs.regions);
                let c=e.classList, wasOn=c.contains('on');
                switch(Prefs.getPublishLevel()){
                    case PublishLevel.Single:
                        if(all===true){return;}
                        console.log('li', dialog.queryAll('li.on'), 'e', e);
                        dialog.queryAll('li.on').forEach(function(r){r.classList.remove('on')});
                        if(wasOn)c.remove('on');else c.add('on');
                        //e.closest('ul').query('ul').childNodes.forEach(function(ct){if(ct!==c)ct.classList.remove('on');});
                        break;
                    case PublishLevel.Country:
                        if(wasOn)c.remove('on');else c.add('on');
                        if(all===true){e.closest('ul').query('ul').childNodes.forEach(function(ct){if(wasOn){ct.classList.remove('on');}else{ct.classList.add('on');}});}
                        break;
                    default:                        
                        if(wasOn)c.remove('on');else c.add('on');
                        if(all===true){
                            e.closest('ul').query('ul').childNodes.forEach(function(ct){if(wasOn){ct.classList.remove('on');}else{ct.classList.add('on');}});
                        }
                        break;                            
                }
            };
            //const keys = Object.keys(_.region);
            //for (const key of keys) {
            Prefs.getAllowedCountriesForUserSource().forEach(function(key){
                console.log('country', key);
                let li=createElem('li', '', '<i class="icn icnsmall icn-'+_.region[key].c+'"></i><span>'+_.region[key][_.ar?'ar':'en']+'</span>', 1);
                li.dataset.countryId=key;
                
                const ckeys=Object.keys(_.region[key].cc);
                if(ckeys.length===1){
                    li.dataset.cityId=ckeys[0];
                    li.onclick=function(){onf(this);};
                    blocks.cn.append(li);
                }
                else {
                    li.onclick=function(){onf(this,true);};
                    let ul=createElem('ul'); ul.append(li); 
                    let cul=createElem('ul');cul.id='cn'+key;
                    for(const cityId of ckeys){                        
                        let ci=createElem('li', '', _.region[key].cc[cityId][_.ar?'ar':'en']);
                        ci.dataset.countryId=key;ci.dataset.cityId=cityId;
                        ci.onclick=function(){ onf(this); };
                        cul.append(ci);
                    }
                    
                    let p=null;
                    switch(parseInt(key)){
                        case 2: p=blocks.ae; break;
                        case 4: p=blocks.sa; break;
                        case 7: case 8: p=blocks.kj; break;
                        default: p=blocks.ot; break;                            
                    }                    
                    
                    let cli=createElem('li'); ul.append(cli);
                    if(p.childNodes.length>0){p.childNodes[p.childNodes.length-1].append(createElem('li','','&nbsp;',1));}
                    cli.append(cul);
                    p.append(ul);
                }
            });
            let c=0;
            for (var k in blocks){
                if(blocks[k].childNodes.length===0){
                    blocks[k].remove();
                }else c++;                
            };
            c*=3;if(c>12)c=12;            
            card.className='card col-'+c;
            let toolbar=createElem('div', 'card-footer');
            toolbar.style.cssText='position:absolute;bottom:0px;width:calc(100% - 52px);';
            card.append(toolbar);
            
            let okBtn=createElem('button', 'btn blue', 'Confirm');
            okBtn.onclick=function(){                
                let selected=$.body.query('div#regions').queryAll('li.on');
                Ad.regions.length=0;
                selected.forEach(function(i){if(i.dataset.cityId){Ad.regions.push(parseInt(i.dataset.cityId));}});
                UI.regionChanged();
                _.close();
            };
            let cancelBtn=createElem('button', 'btn blue', 'Cancel');
            cancelBtn.onclick=_.close;
            toolbar.append(okBtn, cancelBtn);

        }
        else { dialog=_.dialogs.regions; }
        
        let lis=dialog.queryAll('li.on');
        lis.forEach(function(i){i.classList.remove('on');});lis.length=0;
        lis=dialog.queryAll('li');
        lis.forEach(function(i){if(i.dataset.cityId){if(Ad.regions.indexOf(parseInt(i.dataset.cityId))>=0){i.classList.add('on');}}});
        
        for(let i in _.region){
            let t=dialog.query('ul#cn'+i);
            if(t){
                if(t.queryAll('li').length===t.queryAll('li.on').length){
                    t.parentElement.closest('ul').firstChild.classList.add('on');
                }
            }
        }

        _.showDialog(dialog);                
    },
    
    getCountryByCityId:function(cc){
        for(let i in this.region){if(this.region[i].cc[cc]){return this.region[i];}}
        return null;
    },
    
    getRootName:function(ro){
        return this.dic[ro] ? this.dic[ro].name : '';
    },
    
    getSectionName:function(ro, se){
        return UI.dic[ro] && UI.dic[ro].sections[se] ? UI.dic[ro].sections[se] : '';
    },
    
    getPurposeName:function(ro, pu){
        return UI.dic[ro] && UI.dic[ro].purposes[pu] ? UI.dic[ro].purposes[pu] : '';
    },
        
    getPhone:function(n){        
        let e=$.body.queryAll('input[type="tel"]')[n];
        console.log('e', e);
        let p=e.closest('li');
        console.log('p',p);
        let t=p.query('select');
        console.log(t);
        let phone={c:0, i:'', r:0, t:parseInt(t.value), v:e.value};     
        return phone;
    },
    
    getEmail:function(){
        return $.body.query('input[type=email]').value;
    },
    
    setEmail:function(v){
        $.body.query('input[type=email]').value=v;
        $.body.query('input[type=email]').checkValidity;
    },
    
    rootChanged:function(ro, pu){
        this.adClass.query('a.ro').innerHTML=this.getRootName(ro) + ' / ' + this.getPurposeName(ro, pu);
    },
    
    textChanged:function(text, tag){
        let ta=$.body.query('textarea#'+tag);
        ta.value=text;
        ta.onchange(ta);
        ta.oninput(ta);
    },
    
    cuiChanged:function(e){
        console.log(e);
    },
    
    addressChanged:function(addr){        
        let node=this.adClass.query('a.lc');
        let p=node.innerText.split(': ');
        if(addr){
            Ad.setGMapAddr(addr);
            node.innerHTML=p[0]+': '+MAP.getText(addr);
        }
        else {
            if(typeof Ad.content.loc==='string' && Ad.content.loc.length>0) {
                node.innerHTML=p[0]+': '+Ad.content.loc;
            }
            else
            node.innerHTML=p[0];
        }
    },
        
    regionChanged:function(){
        let rg=this.adClass.query('a.rg');
        if(!this.regionLabel){this.regionLabel=rg.innerText;}
        
        if(Ad.regions.length>0) {
            let lbl='';
            Ad.regions.forEach(function(c){
                if(lbl.length>0)lbl+=', ';
                let country=UI.getCountryByCityId(c);
                lbl+=country.cc[c][UI.ar?'ar':'en'];
            });
            rg.innerHTML=lbl;
        }
        else {
            rg.innerHTML=this.regionLabel;
        }
    },
    
    close:function(e){
        $.body.classList.remove('modal-open');
        for(let i in UI.dialogs){
            UI.dialogs[i].style.display='none';
            if(UI.dialogs[i].parentElement){
                $.body.removeChild(UI.dialogs[i]);
            }
        }
    }
};


class Photo {
    constructor(elem) {
        let _=this;
        _.e=elem;
        _.e.onclick=_.open.bind(_);
        _.e.classList.add('icn-camera');
        _.p=_.e.query('progress');
        _.file=null;
        _.image=null;
        _.rotation=0;
        _.natWidth=0;
        _.natHeight=0;
        _.index=parseInt(_.e.dataset.index);
        _.path=null;
    }
    
    isPortrait(){return (this.rotation===90||this.rotation===270);}
    transform(img){img.style.setProperty('transform', 'rotate('+this.rotation+'deg)');}
    
    clear(){
        let _=this;
        _.image=null;
        _.file=null;
        _.rotation=0;
        _.natWidth=0;
        _.natHeight=0;
        _.path=null;
    }
    
    setImage(result){
        let _=this;
        _.image=_.e.query('img');
        if(!_.image){
            _.image=new Image();                                        
            _.e.append(_.image);
            _.e.classList.remove('icn-camera');
            _.image.setAttribute('id','picture');  
            _.image.onload=function(){
                _.natWidth=_.image.naturalWidth;_.natHeight=_.image.naturalHeight;_.rotation=0;
                Ad.pictures[_.index]=_; 
                _.transform(_.image);
            };
        }
        _.image.src=result;
        return _;
    }
    
    upload(result){
        let _=this; 
        var opt={maxWidth:1024, canvas:true};
        var loadingImage = loadImage(
            result, 
            function(img){
                var type = img.type;
                var data;
                if(HAS_WEBP){ type='image/webp'; }
                var data = img.toDataURL(type);
                console.log("image mime type", type);
                _.uploadData(data, type);
            },
            opt
        );                         
    }
    
    uploadData(data, type){
        let _=this;        
        _.image=_.e.query('img');
                                    
        let onprogressHandler=function(ev){
            _.p.value= Math.floor(ev.loaded/ev.total*100);
        };
        let onloadstartHandler=function(){                                   
            _.p.value=0;_.p.max=100;
            _.image.style.height=(_.image.offsetHeight-8)+'px';
            _.p.style.display='block';
        };
        let onloadHandler=function(){
            console.log('File uploaded. Waiting for response.');
            _.p.style.display='none';
            _.image.style.height='100%';
        };
        let onErrorHandler=function(){
            //TO DO
        }
        let onreadystatechangeHandler=function(ev){
            var status, text, readyState;
            try {
                readyState = ev.target.readyState;
                text = ev.target.responseText;
                status = ev.target.status;
            }
            catch(e) {
                return;
            }
            if (readyState===4 && status===200 && ev.target.responseText) {
                if(ev.target.responseText === "0"){
                    onErrorHandler();
                    console.log('Failure!', _);
                }else{
                    _.path = ev.target.responseText;
                    console.log('Success!', _);
                }
            }
            else{
                onErrorHandler();
            }
        };
        
        let rg=new RegExp('.*/');
        let ext=type.replace(rg,'');
        
        let form = new FormData();
        const UUID=UI.guid();
        form.append('UPLOAD_IDENTIFIER', UUID);
        form.append('pic', data);
        form.append('type', type);
        form.append('sid', UI.sessionID);
        form.append('aid', Ad.id);
        form.append('ext', ext);
        
        console.log(type, UI.sessionID, Ad.id, ext);
                                    
        let xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('loadstart', onloadstartHandler, false);
        xhr.upload.addEventListener('progress', onprogressHandler, false);
        xhr.upload.addEventListener('load', onloadHandler, false);
        xhr.upload.addEventListener('error', onErrorHandler, false);
        xhr.addEventListener('readystatechange', onreadystatechangeHandler, false);
        xhr.open('POST', '/upload/', true);
        //xhr.open('POST', '/upload/?t='+t+'&s='+UI.sessionID, true);
        xhr.send(form);  
    }
    
    open(){
        if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
            alert('The File APIs are not fully supported in this browser.');
            return;
        }
        let _=this, cw=$.body.clientWidth;
        UI.pixIndex=_.index;            
        
        let openFileDialog=function(multiple, isReplace){
            return new Promise(resolve => {
                let inp=createElem('input');
                inp.type='file';
                inp.value='';
                inp.style.display='none';
                inp.multiple=multiple;
                inp.accept='image/*';
                inp.onchange = ee => {
                    UI.close();
                    
                    var availableSpots = 0;
                    for(var i in UI.photos){
                        if(UI.photos[i].file === null){
                            availableSpots++;
                        }
                    }
                    if(isReplace) availableSpots = 1;
                    
                    var BreakException = {};
                    
                    let files = Array.from(inp.files);
                    resolve(files);
                    
                    inp.remove();
                    inp = null;
                    let curr=UI.pixIndex;
                    try{
                        files.forEach(function(file){

                            //check if image is already selected
                            var alreadyAdded = false;
                            for(var i in UI.photos){
                                if(UI.photos[i].file && UI.photos[i].file.name === file.name){
                                    alreadyAdded = true;
                                }
                            }
                            if(alreadyAdded) return;

                            if (/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
                                var reader = new FileReader();
                                reader.onload = readerEvent => {
                                };
                                reader.onloadend = readerEvent => {
                                    if(readerEvent.target.readyState!==FileReader.DONE){return;}
                                    
                                    UI.photos[curr].setImage(readerEvent.target.result);
                                    UI.photos[curr].file=file;
                                    UI.photos[curr].upload(readerEvent.target.result);                                                               
                                    if(multiple){
                                        for(var i=0, l=UI.photos.length; i < l; i++){
                                            if(UI.photos[i].file === null){
                                                curr = i;
                                                break;
                                            }
                                        }
                                    }
                                };
                                reader.readAsDataURL(file);

                                availableSpots--;

                                if (availableSpots === 0) throw BreakException;
                            }
                        });
                        
                    } catch (e) {
                        if (e !== BreakException) throw e;
                    }
                };
                $.body.append(inp); 
                inp.click();
            });
        };
            
        if (_.image) {
            let dialog, card, img;
            if(!UI.dialogs.pix){
                dialog=UI.createDialog('pix');
                card=dialog.query('div.card');
                let span=createElem('span', 'pix');
                span.style.height=Math.round((cw/2)/1.5)+'px';
                img=new Image();
                span.append(img);
                card.append(span);
                let f=createElem('div', 'card-footer');
                f.style.cssText='position:absolute;bottom:0;width:calc(100% - 52px)';
                    
                let btnRotate=createElem('a', 'btn blue', 'Rotate');
                btnRotate.onclick=function(){
                    let pp=UI.photos[UI.pixIndex];
                    pp.rotation+=90;if(pp.rotation>=360){pp.rotation=0;}
                    pp.transform(pp.image);pp.transform(img);
                    let h=pp.e.offsetHeight, w=pp.e.offsetWidth;                    
                    pp.image.style.setProperty('width', (pp.isPortrait()?h:w)+'px', 'important');
                    pp.image.style.setProperty('height', (pp.isPortrait()?w:h)+'px', 'important'); 
                    let hh=img.closest('span').offsetHeight, ww=img.closest('span').offsetWidth;
                    img.style.setProperty('width', (pp.isPortrait()?hh:ww)+'px', 'important');
                    img.style.setProperty('height', (pp.isPortrait()?ww:hh)+'px', 'important');
                };
                f.append(btnRotate);
                    
                let btnRemove=createElem('a', 'btn blue', 'Remove');
                btnRemove.onclick=function(){
                    let pp=UI.photos[UI.pixIndex];
                    pp.image.style.display='none';
                    pp.e.classList.add('icn-camera');
                    pp.image.remove();pp.image=null;
                    UI.close();
                    console.log(_);
                };
                f.append(btnRemove);
                    
                let btnReplace=createElem('a', 'btn blue', 'Replace');
                btnReplace.onclick=function(){openFileDialog(false, true);};
                f.append(btnReplace);
                card.append(f);
            } 
            else {
                dialog=UI.dialogs.pix;
                img=dialog.query('img');
            }
            img.src=_.image.src;
            UI.showDialog(dialog, _);
            return;
        }
        openFileDialog(true);      
    }
};



var Ad={
    id:0,
    dateAdded:null,
    purposeId:0,
    sectionId:0,
    rtl:0,
    state:0,
    lastUpdate:null,
    countryId:0,
    cityId:0,
    uid:0,
    activeCountryId:0,
    activeCityId:0,
    media:0,
    docId:null,
    level:0,
    featuredDateEnded:0,
    boDateEnded:0,
    
    
    content:{
        id:0,
        user:0,
        hl:'',
        state:0,
        lat:0,
        lon:0,
        loc:'',
        budget:0,
    },
    rootId:0,
    
    natural:null,
    foreign:null,
    
    address:null,
    pictures:{},
    regions:[],
    //phone1:null,
    //phone2:null,
    email:null,
    userLocation:null,
    location:null,   
    sloc:'',
        
    
    init:function(){
        let _=this;
        _.id=0;
        _.state=0;
        _.rootId=0;
        _.sectionId=0;
        _.purposeId=0;
        _.natural=null;
        _.foreign=null;  
        _.email=null;
        _.regions.length=0;
    },
    
    parse:function(ad){        
        console.log('ad', ad);
        let _=this; _.init();
        
        _.id=ad.id;
        _.state=ad.state;        
        _.content.id=_.id;
        _.content.user=ad.user?ad.user:0;
        _.content.hl=((ad.hl)?ad.hl:(UI.ar?'ar':'en'));        
        _.content.lat=ad.lat;
        _.content.lon=ad.lon;
        _.content.loc=ad.loc;
        _.content.budget=ad.budget;
        
        UI.addressChanged();        
        UI.photos.forEach(function(p){ p.clear(); });
        
        if(ad.pics){
            UI.pixIndex=0;
            for(var i in ad.pics){
                UI.photos[UI.pixIndex++].setImage($.body.dataset.repo+'/repos/m/'+i).path=i;
                if (UI.pixIndex===5) break;
            }
        }
        

        let re=/\u200b/;
        let parts;
        if(typeof ad.other==='string'){
            parts = ad.other.split(re);
            if (parts.length>0) {
                _.natural=parts[0];
                UI.textChanged(_.natural, 'natural');
            }
        }
        if(typeof ad.altother==='string'){
            parts = ad.altother.split(re);
            if (parts.length>0) {
                _.foreign=parts[0];
                UI.textChanged(_.foreign, 'foreign');
            }
        }
                
        if(ad.pu && ad.se && ad.pu>0 && ad.se>0){
            let ro;
            for (let i in UI.dic) {
                if (UI.dic[i].sections[ad.se]) { ro=i; }
                if(ro>0){
                    _.setClassification(ro, ad.se, ad.pu);
                    break;
                }
            }
        }
        
        if (ad.cui) {
            if (ad.cui.e) {
                _.email=ad.cui.e;
                UI.setEmail(_.email);
            }
            if (ad.cui.p) {
                let i=1;
                ad.cui.p.forEach(function(p){
                    console.log(p);
                    if(i<3){
                        UI.numbers[i].kind.value=p.t;
                        UI.numbers[i].tel.value=p.v;
                        UI.numbers[i].verify();
                    }
                    i++;
                });
            }
        }
        //console.log(typeof ad.pubTo);
        if(typeof ad.pubTo==='object'){
            _.regions=Object.values(ad.pubTo);
            console.log(UI.getCountryByCityId(_.regions[0]));
            UI.regionChanged();
        }
        console.log('Ad', this);   
    },
    
    
    setClassification:function(ro, se, pu){
        this.setRootId(ro);
        this.setSectionId(se);
        this.setPurposeId(pu);
    },
    
    setRootId:function(ro){        
        if(ro!==this.rootId) {
            this.rootId=parseInt(ro);
            this.setSectionId(0);
            if(!UI.dic[this.rootId].purposes[this.purposeId]){
                this.setPurposeId(0);
            }
            UI.rootChanged(this.rootId, this.purposeId);
        }        
    },
    
    setSectionId:function(se){
        console.log('setSectionId', Prefs.getRootPrefs(se));
        if(this.sectionId!==se){
            this.sectionId=parseInt(se);
            $.body.query('#ad-class').query('a.se').innerHTML=this.getSectionName();
        }        
    },
    
    setPurposeId:function(pu){
        if(this.purposeId!==pu){
            this.purposeId=parseInt(pu);
            UI.rootChanged(this.rootId, this.purposeId);
        }
    },        

    setGMapAddr(addr, user){        
        if(user){
            this.userLocation=addr;
        }
        else {
            this.location=addr;
            if(this.location){
                for(let i in this.location){
                    if(this.location[i].geometry){
                        this.content.lat=this.location[i].geometry.location.lat();
                        this.content.lon=this.location[i].geometry.location.lng();
                        break;
                    }
                }
            }
            else {
                this.content.lat=0;
                this.content.lon=0;
            }
                        
            this.content.loc=(this.location)?MAP.getText(addr):'';
        }        
    },
        
    
    
    getSectionName:function(){return UI.dic[this.rootId] && UI.dic[this.rootId].sections[this.sectionId] ? UI.dic[this.rootId].sections[this.sectionId] : '';},
    getPurposeName:function(){return UI.dic[this.rootId] && UI.dic[this.rootId].purposes[this.purposeId] ? UI.dic[this.rootId].purposes[this.purposeId] : '';},
    
    save:function(){
        let _=this;
        console.log('window.event', window.event.target.dataset);
        
        if(!(_.rootId)) {
            window.alert(UI.ar ? 'فئة الاعلان غير محددة' : 'Please choose listing section?');
            return;
        }
        let status=parseInt(window.event.target.dataset.state);
        console.log("Status", status, "Budget", _.content.budget);
        let ad={
            hl:(UI.ar?'ar':'en'),
            id:_.id, 
            state:parseInt(window.event.target.dataset.state), 
            user:_.content.user, 
            lat:_.content.lat, 
            lon:_.content.lon, 
            loc:_.content.loc, 
            budget:_.content.budget, 
            version:2, 
            app:'web', app_v:UI.version,
            cui:{p:[], e:''},
            ro:_.rootId,
            pu:_.purposeId,
            se:_.sectionId,
            rtl:0,
            altRtl:0,
            other:'',
            altother:'',
            pics:{},
            pubTo:{}
        };
        
        UI.photos.forEach(function(p){
            console.log(p);
            if(p.image !== null){
                ad.pics[p.path]=[
                    p.natWidth,
                    p.natHeight
                ];
            }
        });

        ad.cui.p=[];
        for (let i in UI.numbers) {
            if (!UI.numbers[i].valid()) {
               window.alert('[ '+UI.numbers[i].tel.value+ ' ] '+UI.numbers[i].error);
               UI.numbers[i].tel.focus;
               return;
            }
            else if (!UI.numbers[i].empty()) {
                ad.cui.p.push(UI.numbers[i].getPostData());            
            }            
        }
        
        if(_.natural) _.natural=_.natural.trim();
        if(_.foreign) _.foreign=_.foreign.trim();
        
        if (_.natural && _.foreign && _.natural.length>0 && _.foreign.length>0) {
            if (_.natural.isArabic(0.5)) {
                ad.other=_.natural;
                if (!_.foreign.isArabic(0.5)) {
                    ad.altother=_.foreign;
                }
            }
            else {
                if (_.foreign.isArabic(0.5)) {
                   ad.other=_.foreign;
                   ad.altother=_.natural;
                }
                else {
                   ad.other=_.natural; 
                }
            }
        }
        else if (_.natural && _.natural.length>0 && (_.foreign===null || _.foreign.length===0)) {
            ad.other=_.natural; 
        }
        else if (_.foreign && _.foreign.length>0 && (_.natural===null || _.natural.length===0)) {
            ad.other=_.foreign; 
        }
        
        ad.cui.e = UI.getEmail();
        
        _.regions.forEach(function(r){ad.pubTo[r]=r;});
        
        console.log("ADDDDDDD", ad);
        
        let data={o:ad};
        fetch('/ajax-adsave/', _options('POST', data))
            .then(res => res.json())
            .then(response => {
                console.log('Success:', response);
                if (response.success===1) {
                    console.log(response.result);
                }
                if (response.RP===1) {
                    if (typeof response.DATA.ad==='object') {
                        console.log("RP", response.DATA.ad);
                        _.parse(response.DATA.ad);
                    }
                }
                if(status===1||status===2||status===4){
                    history.go(-1);
                }
            })
            .catch(error => {
                console.log('Error:', error);
            });                                
    },
    
    log:function(){console.log(this);}
};


function toLower(){
    console.log(this);
}


var RegionType={
    Single:0, Country: 1, MultiCountry: 2, Any: 9, 
    isValid:function(v){
        v=parseInt(v);
        return (v===this.Single||v===this.Country||v===this.MultiCountry||v===this.Any);
    }
};
var PublishLevel={Single: 1, Country: 2, Intl: 3};

class Filter{
    constructor(dictionary) {
        this.source=RegionType.Country;
        this.countries=[];
        this.cities=[];
        this.purposes=[];
        this.movedTo=0;
        if(dictionary){this.setConstraints(dictionary);}
    }
    
    setConstraints(dict){
        let _=this;
        if (dict.mv && Number.isInteger(dict.mv)) {
            _.movedTo=parseInt(dict.mv);
        }
        if (dict.src && Number.isInteger(dict.src) && RegionType.isValid(dict.src)) {
            _.source=parseInt(dict.src);
        }
        if (dict.p && Array.isArray(dict.p)) {
            dict.p.forEach(function(pu){_.purposes.push(pu);});
        }
        if (dict.cn && Array.isArray(dict.cn)) {
            dict.cn.forEach(function(cn){_.countries.push(cn);});
        }
        if (dict.ct && Array.isArray(dict.ct)) {
            dict.ct.forEach(function(cc){_.cities.push(cc);});
        }
    }
    
    isBlocked(){
        return (this.movedTo>0);
    }
    
    hasPurpose(pu){
        return this.purposes.indexOf(pu)>=0;
    }
    
    hasCountry(co){
        return this.countries.indexOf(co)>=0;
    }
    
    hasCity(cc){
        return this.countries.indexOf(cc)>=0;
    }
    
    isMovedTo(){
        return this.movedTo;
    }        
    
};

const kConstraintKey = "constraints";
const kAllow = "allow";
const kDeny = "deny";
const kSections = "sections";
const kIndex = "index";
const kRule = "rules";
const kTail = "tail";
const kPublishLevel = "level";


var Prefs={
    major:0,
    minor:0,
    countries:null,
    chains:null,
    activationCountryCode:null,
    activationCountryId:0,
    carrierCountryCode:null,
    carrierCountryId:0,
    
    init:function(data){        
        let _=this, rules={}, index={};
        _.countries=[];
        _.chains={[kRule]:rules, [kIndex]:index};
        for (var key in data) {
            let dict=data[key];
            if(key==='version'){
                _.major=dict.major;
                _.minor=dict.minor;
            }
            else if(key==='countries'){
                _.countries=[...dict];
            }
            else {
                let rootId = dict.id;
                rules[rootId]={[kAllow]:[], [kDeny]:[], [kSections]:{}, [kTail]:[], [kPublishLevel]:{}};
                
                for(let level in dict[kPublishLevel]){                    
                    if (dict[kPublishLevel][level]){
                        for(let pu in dict[kPublishLevel][level]){
                            rules[rootId][kPublishLevel][dict[kPublishLevel][level][pu]]=parseInt(level);
                        }
                    }
                    else {
                        console.log('invalid publish level');
                    }
                }
                
                //rules[rootId][kPublishLevel]=dict[kPublishLevel];
                
                for(let i in dict[kTail]){rules[rootId][kTail].push(dict[kTail][i]);}
                
                for (let i in dict[kAllow]){
                    if(dict[kAllow][i][kConstraintKey]){
                        let filter = new Filter();
                        filter.setConstraints(dict[kAllow][i][kConstraintKey]);
                        rules[rootId][kAllow].push(filter);
                    }
                }
                
                for (let i in dict[kDeny]){
                    if(dict[kDeny][i][kConstraintKey]){
                        let filter = new Filter();
                        filter.setConstraints(dict[kDeny][i][kConstraintKey]);
                        rules[rootId][kDeny].push(filter);
                    }
                }
                
                for (let i in dict[kSections]) {
                    let secDict=dict[kSections][i];
                    let secId=secDict.id;
                    index[secId]=rootId;
                    
                    let sectionRules={[kAllow]:[], [kDeny]:[], [kPublishLevel]:{}};
                    for (let j in secDict[kPublishLevel]) {
                        if(secDict[kPublishLevel][j]){
                            for (let k in secDict[kPublishLevel][j]) {
                                sectionRules[kPublishLevel][secDict[kPublishLevel][j][k]]=parseInt(j);
                            }
                        }
                        else {
                            console.log('publish level problem');
                        }
                    }
                    
                    for (let j in secDict[kAllow]) {
                        if(secDict[kAllow][j][kConstraintKey]){
                            let filter=new Filter(secDict[kAllow][j][kConstraintKey]);
                            //console.log(j, filter);
                            sectionRules[kAllow].push(filter);
                        }
                    }
                    
                    for (let j in secDict[kDeny]) {
                        if(secDict[kDeny][j][kConstraintKey]){
                            let filter=new Filter(secDict[kDeny][j][kConstraintKey]);
                            //console.log(j, filter);
                            sectionRules[kDeny].push(filter);
                        }
                    }
                    
                    rules[rootId][kSections][secId]=sectionRules;
                }
            }
        }
        let rs=UI.adForm.dataset;
        
        if(rs.actCountry.length===2){_.activationCountryCode=rs.actCountry;}        
        if(rs.ipCountry.length===2 && rs.ipCountry===rs.curCountry && rs.tor==='0' && rs.vpn==='0' && rs.proxy==='0'){_.carrierCountryCode=rs.ipCountry;}
        if(_.activationCountryCode===null&&($.body.dataset.level==='90'||$.body.dataset.level==='9')){_.activationCountryCode=rs.ipCountry;}
        
        for(let i in UI.region){
            if(UI.region[i].c.toUpperCase()===_.carrierCountryCode){_.carrierCountryId=parseInt(i);}
            if(UI.region[i].c.toUpperCase()===_.activationCountryCode){_.activationCountryId=parseInt(i);}
        }
        //console.log('UI.adForm.dataset', rs, _.activationCountryCode, _.activationCountryId);
        console.log('Prefs', _);
    },
    
    
    getRootPrefs:function(se){
        if (this.chains[kIndex][se]) {
            return this.chains[kRule][this.chains[kIndex][se]];
        }
        return null;        
    },
    
    isBlockedSection:function(se){
        //console.log('isBlockedSection', se);
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs && rootPrefs[kSections][se]){
            //console.log('rootPrefs[kSections][se]', rootPrefs[kSections][se]);
            for(let i in rootPrefs[kSections][se]){
                //console.log('i', i);
                let filter=rootPrefs[kSections][se][i];
                if(filter instanceof Filter){
                    console.log('filter', filter);
                    if(filter.isBlocked()){
                        if(filter.purposes.length===0||filter.hasPurpose(Ad.purposeId)){
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    },
    
    getMovedSection:function(se){
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs && rootPrefs[kSections][se]){
            for(let i in rootPrefs[kSections][se][kDeny]){
                let filter=rootPrefs[kSections][se][kDeny][i];
                if(filter.isBlocked()){
                    return filter.isMovedTo;
                }
            }
        }
        return 0;
    },
    
    isTailSection:function(se){
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs && rootPrefs[kSections][se]){
            return rootPrefs[kTail].indexOf(se)>-1;
        }
        return false;
    },
    
    getRootTail:function(ro){
        return this.chains[kRule][ro][kTail];
    },
    
    getPublishLevel:function(){
        if(Ad.sectionId>0 && Ad.purposeId>0){
            let rootPrefs=this.getRootPrefs(Ad.sectionId);
            if(rootPrefs){
                //console.log('getPublishLevel', rootPrefs[kSections][Ad.sectionId][kPublishLevel], 'se', Ad.sectionId, 'pu', Ad.purposeId);
                let res=parseInt(rootPrefs[kSections][Ad.sectionId][kPublishLevel][Ad.purposeId]);
                if(res>0){return res;}
            }
        }
        return PublishLevel.Intl;
    },

    getAllowedCountriesForUserSource:function(){
        let _=this, rootSource=RegionType.Country, sectionSource=RegionType.Country;
        let result=[], level=_.getPublishLevel();
        if(level===PublishLevel.Intl){return[..._.countries];}
        
        if(_.countries.indexOf(_.carrierCountryId)!==-1){result.push(_.carrierCountryId);}
        if(_.countries.indexOf(_.activationCountryId)!==-1 && result.indexOf(_.activationCountryId)===-1){result.push(_.activationCountryId);}
        
        if(Ad.sectionId>0 && Ad.purposeId>0){
            let rPrefs=this.getRootPrefs(Ad.sectionId);
            if(rPrefs){
                _.countries.forEach(function(cn){
                    rootSource=RegionType.Country;                    
                    for (let i in rPrefs[kAllow]) {
                        let f=rPrefs[kAllow][i];
                        if(f.hasPurpose(Ad.purposeId)||f.hasCountry(cn)){
                            rootSource=f.source;
                        }
                    }
                    
                    let allow=rPrefs[kSections][Ad.sectionId][kAllow];
                    sectionSource=rootSource;
                    for (let i in allow) {
                        let filter=allow[i];
                        if(filter.hasPurpose(Ad.purposeId)||filter.hasCountry(cn)){
                            sectionSource=filter.source;
                        }
                    }
                    
                    if (sectionSource===RegionType.Any||sectionSource===RegionType.MultiCountry) {
                        console.log('RegionType', sectionSource, cn);
                        if(result.indexOf(cn)===-1){result.push(cn);}
                    }
                    else {
                        if(_.carrierCountryId===cn){console.log('carrier', cn, _.carrierCountryId)}
                        if(_.activationCountryId===cn){console.log('activation', cn, _.activationCountryId)}
                        if(_.carrierCountryId===cn||_.activationCountryId===cn){
                            if(result.indexOf(cn)===-1){result.push(cn);}
                        }
                    }
                    
                });
            }
        }
        else {
            result=[..._.countries];
        }
        return result;
    },
    
    isSourceAllowForCountry:function(cn, se, pu){
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs){
            let rootSource=RegionType.Country;
            for (let i in rootPrefs[kAllow]) {
                let filter=rootPrefs[kAllow][i];
                if(filter.hasPurpose(pu)){
                    rootSource=filter.source;
                }
                if(filter.hasCountry(cn) && filter.source!==RegionType.Any){
                    rootSource=filter.source;
                }
            }
            
            let allow=rootPrefs[kSections][se][kAllow];
            sectionSource=rootSource;
            for (let i in allow) {
                let filter=allow[i];
                if(filter.hasPurpose(pu)){
                    sectionSource=filter.source;
                }
                if(filter.hasCountry(cn) && filter.source!==RegionType.Any){
                    sectionSource=filter.source;
                }
            }
            
            if(sectionSource===RegionType.Country){
                if ((this.carrierCountryId>0 && cn!==this.carrierCountryId) && (this.activationCountryId>0 && cn!==this.activationCountryId) /* and not equal selected website country id*/) {
                    return false;
                }
            }
        }
        return true;
    },

    canPostToCountry:function(cn, se, pu){
        if(cn<1)return false;
        pu=parseInt(pu,10);
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs){
            console.log(cn,se,pu);
            let rootSource=RegionType.Country;
            for (let i in rootPrefs[kAllow]) {
                let filter=rootPrefs[kAllow][i];
                if(filter.hasPurpose(pu)){
                    rootSource=filter.source;
                }
                if(filter.hasCountry(cn) && filter.source!==RegionType.Any){
                    rootSource=filter.source;
                }
            }
            
            let allow=rootPrefs[kSections][se][kAllow];
            sectionSource=rootSource;
            for (let i in allow) {
                let filter=allow[i];
                if(filter.hasPurpose(pu)){
                    sectionSource=filter.source;
                }
                if(filter.hasCountry(cn) && filter.source!==RegionType.Any){
                    sectionSource=filter.source;
                }
            }
            
            if(sectionSource===RegionType.Country){
                if(this.carrierCountryId>0){}
                if(this.activationCountryId>0){}
            }
            
            let deny=rootPrefs[kSections][se][kDeny];
            for (let i in deny) {
                let filter=deny[i];
                if(filter.hasCountry(cn) && (filter.purposes.length===0||filter.hasPurpose(pu))){
                    return false;
                }
                else if(filter.hasPurpose(pu) && filter.countries.length===0){
                    return false;
                }
            }
        }
        return true;
    },
    
    getCarrierCountryCode:function(){
        return this.carrierCountryCode;
    },
    
    getActivationCountryCode:function(){
        return this.activationCountryCode;
    }
    
};

var ContactNumberType={Mobile: 1, MobileWhatsapp: 3, Whatsapp: 5, Landline: 7};
class ContactNumber{
    constructor(elem) {
        this.tel=elem;
        this.kind=elem.closest('li').query('select.select-text');
        this.phoneNumber=null;
        this.tel.addEventListener('keydown', enforceFormat);
        this.tel.addEventListener('keyup', formatToPhone);
        this.kind.onchange=this.changed;
        this.tel.onchange=this.changed;
        this.error='';
    }
    
    getType(){
        return parseInt(this.kind.value);
    }
    
    changed(e){
        UI.numbers[e.target.closest('li').query('input[type=tel]').dataset.no].verify();
        return true;
    }
    
    verify(){
        let _=this;
        _.error='';
        let num=_.tel.value.replace(/\s/g, '');
        console.log('num', num);
        
        if(num && num.length>3){
            try {
                _.phoneNumber=new libphonenumber.parsePhoneNumberFromString(num, Prefs.activationCountryCode);
                //console.log(typeof _.phoneNumber);
                if(_.phoneNumber && _.phoneNumber.hasOwnProperty('metadata')){
                    console.log('phoneNumber', _.phoneNumber, _.phoneNumber.isValid(), _.phoneNumber.getType()); 
                    
                    if(_.phoneNumber.isValid()){
                        _.tel.value=_.phoneNumber.country===Prefs.activationCountryCode ? _.phoneNumber.formatNational() : _.phoneNumber.formatInternational();
                        let tp=_.phoneNumber.getType();
                        if (typeof tp === 'undefined') { tp='FIXED_LINE_OR_MOBILE'; }
                        switch(_.getType()){
                            case ContactNumberType.Landline:
                                if(tp!=='FIXED_LINE'&&tp!=='FIXED_LINE_OR_MOBILE'){
                                    _.error='This number is not a fixed/land phone!';
                                }
                                break;
                            case ContactNumberType.Mobile:
                            case ContactNumberType.Whatsapp:
                            case ContactNumberType.MobileWhatsapp:
                                if(tp!=='MOBILE'&&tp!=='FIXED_LINE_OR_MOBILE'){
                                    _.error='This number is not a mobile!';
                                }
                                break;
                        }    
                    }
                    else {
                        _.error='This number is not a valid phone number!';
                    }
                }
                else {
                    _.error='Could not read this number!';
                }
            }
            catch(e){
                console.log('error', e.message);
                _.error=e.message;
            }
        }
        else if(num.length>0){
            _.error='Phone number is too short';
        }
        else {
            _.phoneNumber=null;
        }
        
        //console.log('num', num, _.error);
        
        _.tel.setCustomValidity(_.error);
        if(_.error!==''){ _.phoneNumber=null; }
        
        _.tel.checkValidity();        
        //console.log('validity', _.tel.validity, typeof _.phoneNumber);
        _.tel.reportValidity();
        return _.tel.validity.valid;
    }
    
    
    valid(){
        this.verify();
        console.log('valid:', this.error);
        return (typeof this.phoneNumber==='object') && this.tel.validity.valid;
    }
    
    
    empty(){
        return (this.phoneNumber===null||typeof this.phoneNumber!=='object');
    }
    
    
    getPostData(){
        if((typeof this.phoneNumber!=='object')) return {};
        return {c:parseInt(this.phoneNumber.countryCallingCode), i:this.phoneNumber.country, r:this.phoneNumber.nationalNumber, t:this.getType(), v:this.phoneNumber.number};
    }
};


const isNumericInput = (event) => {
    const key = event.keyCode;
    return ((key >= 48 && key <= 57) || // Allow number line
        (key >= 96 && key <= 105) // Allow number pad
    );
};

const isModifierKey = (event) => {
    const key = event.keyCode;
    return (event.shiftKey === true || key === 35 || key === 36) || // Allow Shift, Home, End
        (key === 8 || key === 9 || key === 13 || key === 46) || // Allow Backspace, Tab, Enter, Delete
        (key > 36 && key < 41) || // Allow left, up, right, down
        (
            // Allow Ctrl/Command + A,C,V,X,Z
            (event.ctrlKey===true || event.metaKey===true) &&
            (key === 65 || key === 67 || key === 86 || key === 88 || key === 90)
        );
};

const enforceFormat = (event) => {
    // Input must be of a valid number format or a modifier key, and not longer than ten digits
    if(!isNumericInput(event) && !isModifierKey(event)){
        event.preventDefault();
    }
};

const formatToPhone = (event) => {
    if(isModifierKey(event)) {return;}

    const target = event.target;
    const input = target.value.replace(/[^+0-9]/g,'');//.substring(0,16);
    
    try {
        //const asYouType = new libphonenumber.AsYouType('LB')
        //target.value = asYouType.input(input);
        UI.numbers[target.dataset.no].verify();
    } catch (error){
        console.log(error);
    }
};

