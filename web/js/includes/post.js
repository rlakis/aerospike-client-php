var $=document,Ed;
$.addEventListener("DOMContentLoaded", function(e) {
    console.log('document loaded');
    UI.init();
});
$.onkeydown=function(e){
    if(e.key==='Escape'){console.log('esc');Ed.close();}
};
createElem=function(tag, className, content, isHtml) {
    var el=document.createElement(tag);
    if(className){el.className = className;}
    if (typeof content !== 'undefined')
        el[isHtml || false ? 'innerHTML' : 'innerText'] = content;
    return el;
};

_options=function(m, dat){
    m=m.toUpperCase();
    let opt={method: m, mode: 'same-origin', credentials: 'same-origin', headers:{'Accept':'application/json','Content-Type':'application/json'}};
    if(m==='POST'){opt['body']=JSON.stringify(dat);}
    return opt;
};

var UI={
    ar:$.body.dir==='rtl',
    dic:null,
    rootId:0,    
    purposeId:0,
    
    dialogs:{},
    init:function(){
        let _=this;
        fetch('/ajax-menu/?sections='+(_.ar?'ar':'en'), _options('GET'))
        .then(res=>res.json())
        .then(response => {
            if(response.RP && response.RP===1){
                _.dic=response.DATA.roots;
                Prefs.init(response.DATA.prefs);
                
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
                
                $.querySelectorAll('span.pix').forEach(function(pix){pix.onclick=_.openImage;pix.classList.add('icn-camera')});
            }
        })
        .catch(error => { 
            console.log(error);
        });
    },
    
    openImage:function(e){
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            let curr=e.target.closest('span').dataset.index;
            let spans=$.querySelector('div.pictures').childNodes;
            
            if(e.target.closest('span').querySelector('img')){
                let dialog, card, image;
                if(!UI.dialogs.pix){
                    dialog=UI.createDialog('pix');
                    card=dialog.querySelector('div.card');
                    image=new Image();
                    card.appendChild(image);
                    let f=createElem('div', 'card-footer');
                    
                    let btnRotate=createElem('a', 'btn blue', 'Rotate');
                    btnRotate.onclick=function(){
                        if(!Ad.pictures[curr].rotate){ Ad.pictures[curr].rotate=0; }
                        Ad.pictures[curr].rotate+=90;
                        if(Ad.pictures[curr].rotate>=360){ Ad.pictures[curr].rotate=0; }
                        Ad.pictures[curr].image.style.setProperty('transform', 'rotate('+Ad.pictures[curr].rotate+'deg)');
                        image.style.setProperty('transform', 'rotate('+Ad.pictures[curr].rotate+'deg)');
                        let h=Ad.pictures[curr].image.closest('span').offsetHeight;
                        let w=Ad.pictures[curr].image.closest('span').offsetWidth;
                        let hh=image.offsetHeight;
                        let ww=image.offsetWidth;
                        let portrait=(Ad.pictures[curr].rotate===90||Ad.pictures[curr].rotate===270);                        
                        Ad.pictures[curr].image.style.setProperty('width', (portrait?h:w)+'px', 'important');
                        Ad.pictures[curr].image.style.setProperty('height', (portrait?w:h)+'px', 'important');
                        //image.style.setProperty('width', portrait?hh+'px':'100%');
                        //image.style.setProperty('height', portrait?'100%':hh+'px');
                    };
                    f.appendChild(btnRotate);
                    
                    let btnRemove=createElem('a', 'btn blue', 'Remove');
                    btnRemove.onclick=function(){
                        Ad.pictures[curr].image.style.display='none';
                        Ad.pictures[curr].image.parentElement.classList.add('icn-camera');
                        Ad.pictures[curr].image.remove();
                        Ad.pictures[curr]={};
                        UI.close();
                    };
                    f.appendChild(btnRemove);
                    
                    let btnReplace=createElem('a', 'btn blue', 'Replace');
                    btnReplace.onclick=function(){
                        console.log('replace');
                    };
                    f.appendChild(btnReplace);
                    card.appendChild(f);
                } 
                else {
                    dialog=UI.dialogs.pix;
                    image=dialog.querySelector('img');
                }
                image.src=Ad.pictures[curr].image.src;
                UI.showDialog(dialog);
                return;
            }
            
            return new Promise(resolve => {
                let input = document.createElement('input');
                input.type = 'file';
                input.multiple = true;
                input.accept = 'image/*';
                input.onchange = ee => {
                    let files = Array.from(input.files);
                    resolve(files);
                    files.forEach(function(file){
                               
                        if ( /\.(jpe?g|png|gif|webp)$/i.test(file.name) ) {
                            var reader = new FileReader();
                            reader.onload = readerEvent => {
                                let img=spans[curr].querySelector('img');
                                if(!img){
                                    img=new Image();
                                    spans[curr].appendChild(img);
                                    spans[curr].classList.remove('icn-camera');
                                }
                                img.onload=function(){
                                    //var height = img.naturalHeight;
                                    //var width = img.naturalWidth;
                                    Ad.pictures[img.closest('span').dataset.index]={'image':img};
                                    //console.log('The image size is '+width+'*'+height);
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
          
        } 
        else {
            alert('The File APIs are not fully supported in this browser.');
        }
    },
    
    createDialog:function(name){
        let dialog=createElem('div', 'modal');
        dialog.setAttribute('id', name);
        let card=createElem('div', 'card col-6');
        let X = createElem('span', 'close', '&times;', true);
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
        $.body.appendChild(dialog);
        dialog.style.display = "flex";
        let card=dialog.querySelector('div.card');
        if(dialog.clientWidth<1200){card.className='card col-8';}

        if(card.offsetWidth+30>dialog.clientWidth){
            dialog.style.display = "block";
        }
        else if (card.offsetHeight+16>dialog.clientHeight) {
            //card.style.setProperty('margin-top', (card.offsetHeight + 48 - dialog.clientHeight) + 'px');
        }
        let img=card.querySelector('img');
        if(img){
            img.style.width='100%';
            img.style.height=Math.round(img.width/1.5)+'px';
            img.style.setProperty('object-fit', 'cover');
            img.style.setProperty('object-position', 'center');            
        }
    },
    
    chooseRootPurpose:function(){
        let _=this, dialog, card;
        if(!_.dialogs.roots){
            dialog=_.createDialog('roots');
            card=dialog.querySelector('div.card');
            for(let i in _.dic) {
                card.appendChild(createElem('h6','',_.dic[i].name));
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
                card.appendChild(ul);
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
            dialog=_.createDialog(ref);
            card=dialog.querySelector('div.card');
            let ul=createElem('ul');
            
            
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
    
    close:function(e){
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