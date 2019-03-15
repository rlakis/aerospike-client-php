var $=document,Ed;
$.addEventListener("DOMContentLoaded", function(e) {
    UI.init();
});
$.onkeydown=function(e){
    if(e.key==='Escape'){UI.close();}
};
var MAP={
    view:null,
    marker:null,
    coder:null,
    infoWindow:null,
    result:null,
    
    init:function(){
        let _=this;
        _.view = new google.maps.Map($.querySelector('#gmapView'), {
            center: {lat: parseFloat(UI.ip.ipquality.latitude), lng: parseFloat(UI.ip.ipquality.longitude)},
            zoom: 12
        });
        google.maps.event.addDomListener(_.view, "click", function(e){
            _.coder.geocode({"latLng": e.latLng},
                function(results, status) {
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
        _.myLocation();
    },
    
    handleLocationError:function(browserHasGeolocation, infoWindow, pos){
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation?'Error: The Geolocation service failed.':'Error: Your browser doesn\'t support geolocation.');
            infoWindow.open(this.view);
    },
    
    myLocation:function(){
        let _=this;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let pos = {lat: position.coords.latitude, lng: position.coords.longitude};
                _.coder = new google.maps.Geocoder();
                _.marker = new google.maps.Marker({position: pos, map: _.view, animation: google.maps.Animation.DROP, title:'My Location'});
                _.view.setCenter(pos);
                _.getCoordAddress(pos, true);
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
        if(zoom){this.setZoom(15);}
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
        let _=MAP, q=e.querySelector('input.searchTerm');
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
    ar:$.body.dir==='rtl',
    ip:null,
    dic:null,
    map:null,
    geocoder:null,
    infoWindow:null,
    rootId:0,    
    purposeId:0,
    pixIndex:0,
    
    numbers:{1:null, 2:null},
    dialogs:{},
    
    init:function(){
        let _=this;
        fetch('/ajax-menu/?sections='+(_.ar?'ar':'en'), _options('GET'))
        .then(res=>res.json())
        .then(response => {
            if(response.RP && response.RP===1){
                _.dic=response.DATA.roots;
                Prefs.init(response.DATA.prefs);
                _.ip=response.DATA.ip;
                console.log(_.ip);
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
                console.log(_.dic);
                
                $.querySelectorAll('span.pix').forEach(function(pix){
                    pix.onclick=_.openImage;
                    pix.classList.add('icn-camera');
                });
                $.querySelectorAll('textarea').forEach(function(txt){
                    txt.oninput=dirElem;
                });
                $.querySelectorAll('input[type=tel]').forEach(function(tel){                    
                    _.numbers[tel.dataset.no] = new ContactNumber(tel);
                });
            }
        })
        .catch(error => { 
            console.log(error);
        });
       
        
        //_.validator=new FormValidator(  );
    },
    
    submit:function(e){
        let form=e.closest('form');
        //form.querySelectorAll('input[type=tel]')[0].setCustomValidity('Invalid phone number');
        console.log(form.checkValidity());
        if(!form.querySelector('input[type=email]').checkValidity()){            
            form.querySelector('input[type=email]').reportValidity();
        }
        
        form.querySelectorAll('input[type=tel]').forEach(function(tel){
            let telType=tel.closest('li').querySelector('select.select-text');
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
          

    openImage:function(e){
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            UI.pixIndex=e.target.closest('span').dataset.index;
            
            let spans=$.querySelector('div.pictures').childNodes;
            let cw=$.body.clientWidth;
            let openFileDialog=function(multiple, largeImage){
                return new Promise(resolve => {
                    let input=$.createElement('input');
                    input.type='file';
                    input.multiple=multiple;
                    input.accept='image/*';
                    input.onchange = ee => {
                        let files = Array.from(input.files);
                        resolve(files);
                        let curr=UI.pixIndex;
                        files.forEach(function(file){
                            if ( /\.(jpe?g|png|gif|webp)$/i.test(file.name) ) {
                                var reader = new FileReader();
                                reader.onload = readerEvent => {
                                    let img=spans[curr].querySelector('img');
                                    if(!img){
                                        img=new Image();                                        
                                        spans[curr].appendChild(img);
                                        spans[curr].classList.remove('icn-camera');
                                        img.setAttribute('id', 'picture');
                                    }
                                    img.onload=function(){                              
                                        Ad.pictures[img.closest('span').dataset.index]={'image':img, 'rotate':0, 'width':img.naturalWidth, 'height':img.naturalHeight};
                                        img.style.setProperty('transform', 'rotate(0deg)');
                                        if(largeImage){
                                            largeImage.src=Ad.pictures[UI.pixIndex].image.src;
                                            let hh=largeImage.closest('span').offsetHeight;
                                            let ww=largeImage.closest('span').offsetWidth;
                                            largeImage.style.setProperty('width', ww+'px');
                                            largeImage.style.setProperty('height', hh+'px');
                                            largeImage.style.setProperty('transform', 'rotate(0deg)');
                                        }
                                    };
                                    img.src=readerEvent.target.result; 

                                    curr++;
                                    if(curr>4){curr=0;}                                              

                                };
                                reader.readAsDataURL(file);                                                        
                            }
                        });
                    };
                    input.click();
                });
            };
            
            if(e.target.closest('span').querySelector('img')){
                let dialog, card, image;
                if(!UI.dialogs.pix){
                    dialog=UI.createDialog('pix');
                    card=dialog.querySelector('div.card');
                    let span=createElem('span', 'pix');
                    span.style.height=Math.round((cw/2)/1.5)+'px';
                    image=new Image();
                    span.appendChild(image);
                    card.appendChild(span);
                    let f=createElem('div', 'card-footer');
                    f.style.position='absolute';
                    f.style.bottom=0;
                    f.style.setProperty('width', 'calc(100% - 52px)');
                    
                    let btnRotate=createElem('a', 'btn blue', 'Rotate');
                    btnRotate.onclick=function(){
                        let curr=UI.pixIndex;
                        
                        Ad.pictures[curr].rotate+=90;
                        if(Ad.pictures[curr].rotate>=360){ Ad.pictures[curr].rotate=0; }
                        Ad.pictures[curr].image.style.setProperty('transform', 'rotate('+Ad.pictures[curr].rotate+'deg)');
                        image.style.setProperty('transform', 'rotate('+Ad.pictures[curr].rotate+'deg)');
                        let h=Ad.pictures[curr].image.closest('span').offsetHeight;
                        let w=Ad.pictures[curr].image.closest('span').offsetWidth;
                        let hh=image.closest('span').offsetHeight;
                        let ww=image.closest('span').offsetWidth;
                        let portrait=(Ad.pictures[curr].rotate===90||Ad.pictures[curr].rotate===270);
                        Ad.pictures[curr].image.style.setProperty('width', (portrait?h:w)+'px', 'important');
                        Ad.pictures[curr].image.style.setProperty('height', (portrait?w:h)+'px', 'important');
                        
                        image.style.setProperty('width', (portrait?hh:ww)+'px', 'important');
                        image.style.setProperty('height', (portrait?ww:hh)+'px', 'important');
                    };
                    f.appendChild(btnRotate);
                    
                    let btnRemove=createElem('a', 'btn blue', 'Remove');
                    btnRemove.onclick=function(){
                        Ad.pictures[UI.pixIndex].image.style.display='none';
                        Ad.pictures[UI.pixIndex].image.parentElement.classList.add('icn-camera');
                        Ad.pictures[UI.pixIndex].image.remove();
                        Ad.pictures[UI.pixIndex]={};
                        UI.close();
                    };
                    f.appendChild(btnRemove);
                    
                    let btnReplace=createElem('a', 'btn blue', 'Replace');
                    btnReplace.onclick=function(){
                        openFileDialog(false, image);                        
                    };
                    f.appendChild(btnReplace);
                    card.appendChild(f);
                } 
                else {
                    dialog=UI.dialogs.pix;
                    image=dialog.querySelector('img');
                }
                image.src=Ad.pictures[UI.pixIndex].image.src;
                UI.showDialog(dialog);
                return;
            }
            openFileDialog(true);
            
          
        } 
        else {
            alert('The File APIs are not fully supported in this browser.');
        }
    },    
    
    createDialog:function(name, fullW, fullH){
        let dialog=createElem('div', 'modal');
        dialog.setAttribute('id', name);
        dialog.dataset.fullWidth=fullW;
        dialog.dataset.fullHeight=fullH;
        let card=createElem('div', 'card col-'+((dialog.dataset.fullWidth==='true'&&dialog.dataset.fullHeight==='true')?'12':'8'));
        if(dialog.dataset.fullHeight==='true'){
            card.style.setProperty('padding-top', '0');
            card.style.setProperty('padding-bottom', '0');
            card.style.setProperty('height', window.innerHeight+'px');
        }
        if(dialog.dataset.fullWidth==='true'){
            card.style.setProperty('padding-left', '0');
            card.style.setProperty('padding-right', '0');            
        }
        let X = createElem('span', 'close', '&times;', true);
        if(name==='map'){                      
            var script=$.createElement('script');script.type="text/javascript";
            script.src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCXdUTLoKUM4Dc8LtMYQM-otRB2Rn59xXk&sensor=true&callback=MAP.init&language="+(UI.ar?'ar':'en');           
            $.body.appendChild(script);
            X.style.display='none';
        }
        X.onclick = this.close;
        if (this._top) {
            X.className = 'close nopix';
            this._top.appendChild(X);
        }
        else {
            card.appendChild(X);
        }
        dialog.appendChild(card);
        this.dialogs[name]=dialog;
        return dialog;
    },
    
    showDialog:function(dialog){
        $.body.classList.add('modal-open');
        $.body.appendChild(dialog);  
        dialog.style.display = "flex";
        dialog.style.setProperty('max-height', window.innerHeight+'px');
        let card=dialog.querySelector('div.card');
        console.log('before', dialog.dataset);
        if(!dialog.dataset.fullWidth!=='true' && dialog.dataset.fullHeight==='true'){
            console.log('after', dialog.dataset);
            let X=card.querySelector('span.close');
            console.log(X);
            X.style.setProperty('top', '0px');
        }
               
        let img=card.querySelector('span.pix img');
        if(img){
            if(!Ad.pictures[UI.pixIndex].rotate){ Ad.pictures[UI.pixIndex].rotate=0; }
            let cw=$.body.clientWidth;
            let portrait=(Ad.pictures[UI.pixIndex].rotate===90||Ad.pictures[UI.pixIndex].rotate===270);
            img.closest('span').style.height=Math.round((cw/2)/1.5)+'px';
            let hh=img.closest('span').offsetHeight;
            let ww=img.closest('span').offsetWidth;
            img.style.setProperty('width', (portrait?hh:ww)+'px');
            img.style.setProperty('height', (portrait?ww:hh)+'px');
            img.style.setProperty('transform', 'rotate('+Ad.pictures[UI.pixIndex].rotate+'deg)');
        }
    },
    
    chooseRootPurpose:function(){
        let _=this, dialog, card;
        if(!_.dialogs.roots){
            dialog=_.createDialog('roots', false, false);
            card=dialog.querySelector('div.card');
            for(let i in _.dic) {
                let div=createElem('div');
                div.appendChild(createElem('h6','',_.dic[i].name));
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
                    ul.appendChild(li);
                }
                div.appendChild(ul);
                card.appendChild(div);
            }
            _.dialogs.roots=dialog;
        }
        else {
            dialog=_.dialogs.roots;            
        }
        _.showDialog(dialog);        
    },
    
    chooseSection:function(){
        let _=this, dialog, card, ref='sec-'+_.rootId;
        let r=_.dic[_.rootId];
        if(!r){return;}
        _.close();
        if(!_.dialogs[ref]){
            dialog=_.createDialog(ref, false, true);
            card=dialog.querySelector('div.card');                      
            let ul=createElem('ul');
            ul.style.setProperty('height', window.innerHeight+'px');   
            for(var i in r.sindex){
                if(r.sections[r.sindex[i]]){
                    let li=createElem('li', '', r.sections[r.sindex[i]]);
                    li.dataset.se=r.sindex[i];
                    li.onclick=function(e){
                        Ad.setClassification(_.rootId, e.target.dataset.se, _.purposeId);
                        _.close();
                        Ad.log();
                    };
                    ul.appendChild(li);
                }
            }
            card.appendChild(ul);
        }
        else {
            dialog=_.dialogs[ref];
        }
        _.showDialog(dialog);
    },
    
    openMap:function(){
        let _=this, dialog, card;
        _.close();
        if(!_.dialogs.map){
            dialog=_.createDialog('map',true,true);
            card=dialog.querySelector('div.card');
            let b=$.querySelector('#adLocation');
            card.appendChild(b);
            b.style.display='flex';
        }
        else {
            dialog=_.dialogs.map;
        }
        _.showDialog(dialog);
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
    
    rootChanged:function(ro, pu){
        $.querySelector('#ad-class').querySelector('a.ro').innerHTML=this.getRootName(ro) + ' / ' + this.getPurposeName(ro, pu);
    },
    
    cuiChanged:function(e){
        console.log(e);
    },
    
    addressChanged:function(addr){
        Ad.setGMapAddr(addr);
        let node=$.querySelector('#ad-class').querySelector('a.lc');
        let p=node.innerText.split(': ');
        if(addr){
            node.innerHTML=p[0]+': '+MAP.getText(addr);
        }
        else {
            node.innerHTML=p[0];
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

var Ad={    
    rootId:0,
    sectionId:0,
    purposeId:0,
    natural:"",
    foreign:"",
    address:null,
    pictures:{},
    regions:[],
    phone1:null,
    phone2:null,
    email:null,
    userLocation:null,
    location:null,
    lat:0,
    lon:0,
    sloc:'',
    
    
    init:function(){
        
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
        if(this.sectionId!==se){
            this.sectionId=parseInt(se);
            $.querySelector('#ad-class').querySelector('a.se').innerHTML=this.getSectionName();
        }        
    },
    
    setPurposeId:function(pu){
        if(this.purposeId!==pu){
            this.purposeId=parseInt(pu);
            UI.rootChanged(this.rootId, this.purposeId);
        }
    },        

    setGMapAddr(addr, user){
        console.log('address', addr);
        if(user){
            this.userLocation=addr;
        }
        else {
            this.location=addr;
            if(this.location){
                for(let i in this.location){
                    if(this.location[i].geometry){
                        this.lat=this.location[i].geometry.location.lat();
                        this.lon=this.location[i].geometry.location.lng();
                        break;
                    }
                }
            }
            else {
                this.lat=0;
                this.lon=0;
            }
            
            
            this.sloc=(this.location)?MAP.getText(addr):'';
            console.log(this.sloc);
        }
        console.log(this);
    },
    
    getSectionName:function(){return UI.dic[this.rootId] && UI.dic[this.rootId].sections[this.sectionId] ? UI.dic[this.rootId].sections[this.sectionId] : '';},
    getPurposeName:function(){return UI.dic[this.rootId] && UI.dic[this.rootId].purposes[this.purposeId] ? UI.dic[this.rootId].purposes[this.purposeId] : '';},
    
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
                        conosle.log('invalid publish level');
                    }
                }
                
                //rules[rootId][kPublishLevel]=dict[kPublishLevel];
                
                for (let i in dict[kTail]) {
                    rules[rootId][kTail].push(dict[kTail][i]);
                }
                
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
                                sectionRules[kPublishLevel][secDict[kPublishLevel][j][k]]=secDict[kPublishLevel][j];
                            }
                        }
                        else {
                            console.log('publish level problem');
                        }
                    }
                    
                    for (let j in secDict[kAllow]) {
                        if(secDict[kAllow][j][kConstraintKey]){
                            let filter=new Filter(secDict[kAllow][j][kConstraintKey]);
                            sectionRules[kAllow].push(filter);
                        }
                    }
                    
                    for (let j in secDict[kDeny]) {
                        if(secDict[kDeny][j][kConstraintKey]){
                            let filter=new Filter(secDict[kDeny][j][kConstraintKey]);
                            sectionRules[kDeny].push(filter);
                        }
                    }
                    
                    rules[rootId][kSections][secId]=sectionRules;
                }
            }
        }
        console.log(_);
    },
    
    
    getRootPrefs:function(se){
        if (this.chains[kIndex][se]) {
            return this.chains[kRule][this.chains[kIndex][se]];
        }
        return null;        
    },
    
    isBlockedSection:function(se){
        let rootPrefs=this.getRootPrefs(se);
        if(rootPrefs && rootPrefs[kSections][se]){
            for(let i in rootPrefs[kSections][se]){
                let filter=rootPrefs[kSections][se][i];
                if(filter.isBlocked()){
                    if(filter.purposes.length===0||filter.hasPurpose(Ad.purposeId)){
                        return true;
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
        return [];
    },
    
    getPublishLevel:function(){
        if(Ad.sectionId>0 && Ad.purposeId>0){
            let rootPrefs=this.getRootPrefs(Ad.sectionId);
            if(rootPrefs){
                let res = parseInt(rootPrefs[kSections][Ad.sectionId][kPublishLevel][Ad.purposeId]);
                if(res>0){ return res; }
            }
        }
        return PublishLevel.Intl;
    },

    getAllowedCountriesForUserSource:function(){
        let _=this;
        let rootSource = RegionType.Country;
        let sectionSource = RegionType.Country;
        let result=[];
        let level=_.getPublishLevel();
        if(level===PublishLevel.Intl){
            result=[..._.countries];
            return result;
        }
        
        if(_.countries.indexOf(_.carrierCountryId)!==-1){
            result.push(_.carrierCountryId);
        }
        
        if(_.countries.indexOf(_.activationCountryId)!==-1 && result.indexOf(_.activationCountryId)===-1){
            result.push(_.activationCountryId);
        }
        
        if(Ad.sectionId>0 && Ad.purposeId>0){
            let rootPrefs=this.getRootPrefs(Ad.sectionId);
            if(rootPrefs){
                _.countries.forEach(function(cn){
                    rootSource=RegionType.Country;
                    for (let i in rootPrefs[kAllow]) {
                        let filter=rootPrefs[kAllow][i];
                        if(filter.hasPurpose(Ad.purposeId)||filter.hasCountry(cn)){
                            rootSource=filter.source;
                        }
                    }
                    
                    let allow=rootPrefs[kSections][Ad.sectionId][kAllow];
                    sectionSource=rootSource;
                    for (let i in allow) {
                        let filter=allow[i];
                        if(filter.hasPurpose(Ad.purposeId)||filter.hasCountry(cn)){
                            sectionSource=filter.source;
                        }
                    }
                    
                    if (sectionSource==RegionType.Any||sectionSource==RegionType.MultiCountry) {
                        if(result.indexOf(cn)===-1){
                            result.push(cn);
                        }
                    }
                    else {
                        if(_.carrierCountryId===cn||_.activationCountryId===cn){
                            if(result.indexOf(cn)===-1){
                                result.push(cn);
                            }
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
        this.kind=elem.closest('li').querySelector('select.select-text');
        this.phoneNumber=null;
        this.tel.addEventListener('keydown', enforceFormat);
        this.tel.addEventListener('keyup', formatToPhone);
        this.kind.onchange=this.changed;
        this.tel.onchange=this.changed;
    }
    
    getType(){
        return parseInt(this.kind.value);
    }
    
    changed(e){
        UI.numbers[e.target.closest('li').querySelector('input[type=tel]').dataset.no].verify();
        return true;
    }
    
    verify(){
        let _=this;
        _.tel.setCustomValidity('');
        if(_.tel.value){            
            let num=_.tel.value.replace(/\s/g, '');
            _.phoneNumber=new libphonenumber.parsePhoneNumberFromString(num, 'LB');
            if(_.phoneNumber){
                
                //console.log(_.phoneNumber);
                //console.log('national:', _.phoneNumber.formatNational());
                console.log('intl:', _.phoneNumber.formatInternational());
                console.log('type:', _.phoneNumber.getType(), _.getType());
                console.log('valid:', _.phoneNumber.isValid());
                console.log('possible:', _.phoneNumber.isPossible());
                
                _.tel.value=_.phoneNumber.formatNational();
                if(_.phoneNumber.isValid()){
                    let tp=_.phoneNumber.getType();
                    switch(_.getType()){
                        case ContactNumberType.Landline:
                            if(tp!=='FIXED_LINE'&&tp!=='FIXED_LINE_OR_MOBILE'){
                                _.tel.setCustomValidity('This number is not a fixed/land phone!');
                            }
                            break;
                        case ContactNumberType.Mobile:
                        case ContactNumberType.Whatsapp:
                        case ContactNumberType.MobileWhatsapp:
                            if(tp!=='MOBILE'&&tp!=='FIXED_LINE_OR_MOBILE'){
                                _.tel.setCustomValidity('This number is not a mobile!');
                            }
                            break;
                    }                    
                }
                else {
                    _.tel.setCustomValidity('This number is not a valid phone mobile!');
                }
            }
            else {
                _.tel.setCustomValidity('Could not read this number!');
            }
        }
        else {
            _.phoneNumber=null;
        }
        console.log(_.tel.validity);
        _.tel.reportValidity();
        return false;
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



