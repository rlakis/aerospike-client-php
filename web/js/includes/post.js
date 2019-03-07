var $=document;
$.addEventListener("DOMContentLoaded", function(e) {
    console.log('document loaded');
    UI.init();
});
$.onkeydown=function(e){
    if(e.key==='Escape'){console.log('esc');Ed.close()}
}
createElem=function(tag, className, content, isHtml) {
    var el=document.createElement(tag);
    if(className){el.className = className;}
    if (typeof content !== 'undefined')
        el[isHtml || false ? 'innerHTML' : 'innerText'] = content;
    return el;
}

_options=function(m, dat){
    m=m.toUpperCase();
    let opt={method: m, mode: 'same-origin', credentials: 'same-origin', headers:{'Accept':'application/json','Content-Type':'application/json'}};
    if(m==='POST'){opt['body']=JSON.stringify(dat);}
    return opt;
}

var UI={
    ar:$.body.dir==='rtl',
    dic:null,
    prefs:null,
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
                _.prefs=response.DATA.prefs;
                console.log(_.prefs);
                
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
            }
        })
        .catch(error => { 
            console.log(error);
        });
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
            card.style.setProperty('margin-top', (card.offsetHeight + 48 - dialog.clientHeight) + 'px');
        }
    },
    
    chooseRootPurpose:function(){
        let _=this, dialog, card;
        if(!_.dialogs.roots){
            dialog=_.createDialog('roots');
            card=dialog.querySelector('div.card');
            for(i in _.dic) {
                card.appendChild(createElem('h6','',_.dic[i].name));
                let ul=createElem('ul');
                for(j in _.dic[i].menu){
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
        for(i in UI.dialogs){
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
    pictures:[],
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

