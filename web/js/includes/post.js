var $=document;
$.addEventListener("DOMContentLoaded", function(e) {
    console.log('document loaded');
    Ed.init();
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

var Ed={
    ar:$.body.dir==='rtl',
    dic:null,
    dialogs:{},
    init:function(){
        let _=this;
        fetch('/ajax-menu/?sections='+(_.ar?'ar':'en'), _options('GET'))
        .then(res=>res.json())
        .then(response => {
            if(response.RP && response.RP===1){
                _.dic=response.DATA.roots;
                
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
                        Ad.setRootId(e.target.dataset.ro, e.target.dataset.pu);
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
        let _=this, dialog, card, ref='sec-'+Ad.rootId;
        _.close();
        if(!_.dialogs[ref]){
            dialog=_.createDialog(ref);
            card=dialog.querySelector('div.card');
            let ul=createElem('ul');
            let r=_.dic[Ad.rootId];
            
            for(i in r.sindex){
                if(r.sections[r.sindex[i]]){
                    let li=createElem('li', '', r.sections[r.sindex[i]]);
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
    
    close:function(e){
        for(i in Ed.dialogs){
            Ed.dialogs[i].style.display='none';
            if(Ed.dialogs[i].parentElement){
                $.body.removeChild(Ed.dialogs[i]);
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
    setRootId:function(ro, pu){
        if(this.rootId!==ro){
            this.rootId=parseInt(ro);
            this.sectionId=0;
        }
        this.purposeId=pu?parseInt(pu):0;
        console.log(this);
        console.log(this.getRootName());
    },
    getRootName:function(){return Ed.dic[this.rootId].name;},
    getSectionName:function(){return Ed.dic[this.rootId].sections[this.sectionId];}
};

function toLower(){
    console.log(this);
}

