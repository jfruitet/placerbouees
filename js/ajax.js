// JavaScript Document
// ajax.js
// la communication avec les serveurs

let myList = document.getElementById('mylist'); // La liste des sites à charger

let myInitGet = {
    method: "GET",
    headers: {"Content-Type": "application/json;charset=UTF-8",  "Access-Control-Allow-Origin" : "*"},
    referrer: "about:client", //ou "" (pas de réferant) ou une url de l'origine
    referrerPolicy: "no-referrer-when-downgrade", //ou no-referrer, origin, same-origin...
    mode: "cors", //ou same-origin, no-cors
    credentials: "include", //same-origin, ou omit, ou include
    cache: "default", //ou no-store, reload, no-cache, force-cache, ou only-if-cached
    redirect: "follow", //ou manual ou error
    integrity: "", //ou un hash comme "sha256-abcdef1234567890"
    keepalive: false, //ou true pour que la requête survive à la page
    signal: undefined //ou AbortController pour annuler la requête            
};



/*************************************************
 * Charger les caractéristiques d'un plan d'eau
 * **********************************************/

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
                    myjsonboueesfixes = myjsonboueesfixes+'{"boueefixe":'+true+',"id":'+bouees[index].idfixe+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                
                }
                else{
                    myjsonboueesfixes = myjsonboueesfixes+',{"boueefixe":'+true+',"id":'+bouees[index].idfixe+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';
                } 
                compteurfixe++;
            }
            else{
                if (compteurmobile==0){ 
                   myjson = myjson+'{"boueefixe":'+false+',"id":'+compteurmobile+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                
                }
                else{
                    myjson = myjson+',{"boueefixe":'+false+',"id":'+compteurmobile+',"lon":'+bouees[index].lon+',"lat":'+bouees[index].lat+',"color":"'+bouees[index].color+'","fillcolor":"'+bouees[index].flag+'"}';                  
                }             
                compteurmobile++;
            }
        } 
        myjsonboueesfixes = myjsonboueesfixes+']';           
        myjson = myjson+']';
        
        var mystrjson='{"site":"'+nomDuSite.replace(/\s+/g, '')+'","twd":'+twd+',' + myjsonboueesfixes+','+myjson+'}';
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
            credentials: "include", //ou same-origin ou omit, include
            cache: "default", //ou no-store, reload, no-cache, force-cache, ou only-if-cached
            redirect: "follow", //ou manual ou error
            integrity: "", //ou un hash comme "sha256-abcdef1234567890"
            keepalive: false, //ou true pour que la requête survive à la page
            signal: undefined //ou AbortController pour annuler la requête            
        })
        .then(response => response.json())  // Le retour est aussi une chaîne
        .then(response => {
                console.debug(response);
                if (response.ok==1){
                    //document.getElementById("consigne").innerHTML='Données sauvegardées'; 
                    document.getElementById("consigne").innerHTML="Transfert vers le serveur <span class=\"surligne\"><i>"+url_serveur+"</i></span> effectué. ";
                }
            })
        .catch(error => console.debug("Erreur : "+error));
    }
}
    
    

// ----------------------- 
function ajax_SetPlanEau(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => initPlanEau(response))  // tout le boulot se fait ici                
        .catch(error => console.debug("Erreur : "+error));
    }
}
  
    
// ----------------------- 
// Essai de création d'un tableau avec ascenseur
function ajax_getDisplaySitesAsTable(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        // .then(response => console.debug(response))
        .then((data) => {
            for (const site of data.site) {
                const listItem = document.createElement("li");
                var bouton = document.createElement('button');
                bouton.setAttribute('name', `${site.id}`);                
                bouton.setAttribute('onclick', `getThatPlansEau(${site.id})`);
                bouton.textContent = site.id;
                listItem.appendChild(bouton);
                // listItem.appendChild(document.createElement("strong")).textContent = site.id;
                listItem.append(` ${site.name},  ${site.club}, `);
                listItem.appendChild(
                    document.createElement("i"),
                ).textContent = `${site.city} (${site.zipcode}) `;
                var a = document.createElement('a');
                a.setAttribute('href',url_json+`${site.json}`);
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
function ajax_getDisplaySites(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // POST avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        // .then(response => console.debug(response))
        .then((data) => {
            for (const site of data.site) {
                const listItem = document.createElement("li");
                var bouton = document.createElement('button');
                bouton.setAttribute('name', `${site.id}`);                
                bouton.setAttribute('onclick', `getThatPlansEau(${site.id})`);
                bouton.textContent = site.id;
                listItem.appendChild(bouton);
                // listItem.appendChild(document.createElement("strong")).textContent = site.id;
                listItem.append(` ${site.name},  ${site.club}, `);
                listItem.appendChild(
                    document.createElement("i"),
                ).textContent = `${site.city} (${site.zipcode}) `;
                var a = document.createElement('a');
                a.setAttribute('href',url_json+`${site.json}`);
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
function getListePlansEau(){         
    var url= url_serveur+'plans_eau.php';
    var myget="all=1&id=";
    myList.innerHTML = "";
    myList.style.visibility="visible";    
    //ajax_get(url, myget);     
    ajax_getDisplaySites(url, myget);
}

    
// ----------------------- 
function getThatPlansEau(id_plan_eau){  
    //console.debug("getThatPlansEau("+id_plan_eau+")"); 
    var url= url_serveur+'plans_eau.php';
    var myget="all=0&id="+id_plan_eau;
    ajax_SetPlanEau(url, myget);     
}


function initPlanEau(response) {
    // console.debug("Traitement de la réponse\n"+ response);
    // Traitement de la réponse 
    // {"id":1,"nom":"Le Plessis","ville":"Sainte-Luce-sur-Loire","zipcode":"44980","pays":"France","jsonfile":"leplessis.json"}
    const objSite = JSON.parse(response);
    //console.debug("id: "+ objSite.id + " Nom: "+objSite.nom+" Longitude: "+objSite.lon+" Latitude: "+objSite.lat+" JSON: "+objSite.jsonfile+"\n");
    nomDuSite=objSite.nom;
    longitudeDuSite=objSite.lon;
    latitudeDuSite=objSite.lat;
    fichierACharger=objSite.jsonfile;      
    infoSite=objSite.nom+', '+objSite.zipcode+' '+objSite.ville+'.<br>Club de radiomodélisme <a target="_blank" href="'+objSite.url+'">'+objSite.club+'</a>.';
    document.getElementById("mapinfo").innerHTML=infoSite;
    // On peut lancer le chargement des données du site
    getSite(); 
    if (deplacer_bouees) {  // La fonctionnalité du déplacement est ativée
        reloadDataFiles();
    }
}

// ----------------------- 
function ajax_GetSite(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => {
            setDataSite(response);         
            // Calculer l'emprise de la zone à afficher sur le canvas 
            rectangle_englobantZN();
            document.getElementById("transfert").style.visibility="hidden";
            // En cas de changement de site il faut réinitialiser la collecte des balises
            nbouees=0;
            bouees.length=0;  
            // Puisque le site est chargé on peut sauvegarder les cookies 
            setCookies(); // nomDuSite, longitudeDuSite, latitudeDuSite, fichierACharger     
            drawAll();      // Afficher le canvas            
        })  // tout le boulot se fait ici  dans le script  sitenavigation.js              
        .catch(error => console.debug("Erreur : "+error));
    }
}

// ------------------------
function getSite() {
    if ((fichierACharger!==undefined) && (fichierACharger.length>0)){
        //console.debug("Chargement du plan d'eau\n"+ nomDuSite);
        //console.debug("Fichier\n"+ fichierACharger);       
        var url= url_serveur+'getsite.php';
        var myfile="file="+fichierACharger;    
        ajax_GetSite(url, myfile);
    }        
}




