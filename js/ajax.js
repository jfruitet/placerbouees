// JavaScript Document
// ajax.js
// la communication avec les serveurs

let myList = document.getElementById('mylist');
let url_serveur = 'http://localhost/placerbouees/php/'; 
let url_data = 'http://localhost/placerbouees/data/';

let myInitGet = {
    method: "GET",
    headers: {"Content-Type": "application/json;charset=UTF-8"},
    referrer: "about:client", //ou "" (pas de réferant) ou une url de l'origine
    referrerPolicy: "no-referrer-when-downgrade", //ou no-referrer, origin, same-origin...
    mode: "cors", //ou same-origin, no-cors
    credentials: "same-origin", //ou omit, include
    cache: "default", //ou no-store, reload, no-cache, force-cache, ou only-if-cached
    redirect: "follow", //ou manual ou error
    integrity: "", //ou un hash comme "sha256-abcdef1234567890"
    keepalive: false, //ou true pour que la requête survive à la page
    signal: undefined //ou AbortController pour annuler la requête            
};



/*************************************************
 * Charger les caractéristiques d'un plan d'eau
 * **********************************************/
/* 
const zonenav_lat=[];
//47.243830107759834,47.24306984068747,47.24323699253995,47.24364139000113,47.24402062214875,47.24396131113892,47.243830107759834
const zonenav_lon=[];
//-1.4746555619712751,-1.4733794850961033,-1.4732841764495106,-1.4733636003215622,-1.4737871943051744,-1.4743378664837508,-1.4746555619712751             

const zoneconc_lon= [];
//-1.4747445028388313,-1.4744189545636743,-1.4741350461847276,-1.4739079194809506,-1.4736921491121393,-1.4734763787433565,-1.4733401027220054
const zoneconc_lat = [];
//47.2439195874486,47.24402752137087,47.2440943374981,47.244138024920545,47.24416372338749,47.24415601384845,47.24410975659259
*/



// ----------------------- 
function sauveBouees(){
// envoie le fichier JSON des bouées au serveur pour l'enregistrer dans le dossier ./data
    var myjsonboueesfixes='"boueesfixes":[';
    var myjson='"boueesmobiles":[';
    var compteurfixe=0;
    var compteurmobile=0;
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            if (bouees[index].idfixe>=0){
                if (compteurfixe==0){ 
                    myjsonboueesfixes = myjsonboueesfixes+'{"id":'+bouees[index].idfixe+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                
                }
                else{
                    myjsonboueesfixes = myjsonboueesfixes+',{"id":'+bouees[index].idfixe+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';
                } 
                compteurfixe++;
            }
            else{
                if (compteurmobile==0){ 
                   myjson = myjson+'{"id":'+compteurmobile+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                
                }
                else{
                    myjson = myjson+',{"id":'+compteurmobile+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                  
                }             
                compteurmobile++;
            }
        } 
        myjsonboueesfixes = myjsonboueesfixes+']';           
        myjson = myjson+']';
        
        var mystrjson='{"twd":'+twd+',' + myjsonboueesfixes+','+myjson+'}';
        // console.debug("Bouees Fixes JSON:"+myjsonboueesfixes+"\n");
        // console.debug("Bouees JSON:"+myjson+"\n");
        // console.debug("JSON:"+mystrjson+"\n");
        
        var url= url_serveur+'sauverbouees.php';
        ajax_post(url, mystrjson);
    }
 }


// ----------------------- 
function ajax_post(url, mystrjson){    
    if ((url !== undefined) && (url.length>0) && (mystrjson !== undefined) && (mystrjson.length>0)){        
        // POST avec fetch()
        fetch(url, { // let url_serveur = 'http://localhost/placerbouees/php/sauverbouees.php';
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=UTF-8',
            },
            //body: JSON.stringify(myjson), // turn the JS object literal into a JSON string
            body: mystrjson, // mystrjson est déjà une chaîne
            referrer: "about:client", //ou "" (pas de réferanr) ou une url de l'origine
            referrerPolicy: "no-referrer-when-downgrade", //ou no-referrer, origin, same-origin...
            mode: "cors", //ou same-origin, no-cors
            credentials: "same-origin", //ou omit, include
            cache: "default", //ou no-store, reload, no-cache, force-cache, ou only-if-cached
            redirect: "follow", //ou manual ou error
            integrity: "", //ou un hash comme "sha256-abcdef1234567890"
            keepalive: false, //ou true pour que la requête survive à la page
            signal: undefined //ou AbortController pour annuler la requête            
        })
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => console.debug(response))
        .catch(error => console.debug("Erreur : "+error));
    }
}
    
    

// ----------------------- 
function ajax_get(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => console.debug(response))
        .catch(error => console.debug("Erreur : "+error));
    }
}
  
/*
      var linkText = document.createTextNode("my title text");
      a.appendChild(linkText);
      a.title = "my title text";
      a.href = "http://example.com";
      document.body.appendChild(a);
*/      
// ----------------------- 
function ajax_get_json(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        // .then(response => console.debug(response))
        .then((data) => {
            for (const site of data.site) {
                const listItem = document.createElement("li");
                listItem.appendChild(document.createElement("strong")).textContent = site.id;
                listItem.append(`  ${site.name}, `);
                listItem.appendChild(
                    document.createElement("i"),
                ).textContent = `${site.city} (${site.zipcode}) `;
                var a = document.createElement('a');
                a.setAttribute('href',url_data+`${site.json}`);
                a.setAttribute('target','_blank');
                a.innerHTML = `${site.json}`;
                // apend the anchor to the dom element
                a.title = `${site.json}`;
                listItem.appendChild(a);
                myList.appendChild(listItem);
            }
        })            
        .catch(error => console.debug("Erreur : "+error));
    }
}
  
    
// ----------------------- 
function ajax_get_array(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        //.then(response => response.array())  // Le retour est un tableau
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => console.debug(response))
        .catch(error => console.debug("Erreur : "+error));
    }
}
    
// ----------------------- 
function getListePlansEau(){         
    var url= url_serveur+'plans_eau.php';
    var myget="all=1&id=";
    myList.innerHTML = "";
    myList.style.visibility="visible";    
    //ajax_get(url, myget);     
    ajax_get_json(url, myget);
}

    
// ----------------------- 
function getThatPlansEau(id_plan_eau){         
    var url= url_serveur+'plans_eau.php';
    var myget="all=0&id="+id_plan_eau;
    ajax_get(url, myget);     
}

