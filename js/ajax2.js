// JavaScript Document
// ajax.js
// la communication avec les serveurs

let myList = document.getElementById('mylist'); // La liste des sites à charger
let tFichiers=[]; // tableau des fichiers proposés au chargement

let boueesMobiles=[];
let boueesFixesParcours=[];



let myInitGet = {
    method: "GET",
    headers: {"Content-Type": "application/json;charset=UTF-8"},
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

// ----------------------- 
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
}

// ----------------------- 
function ajax_GetSite(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.text())  // Le retour est aussi une chaîne
        .then(response => {
            setDataSite(response);         
            // Puisque le site est chargé on peut sauvegarder les cookies 
            setCookies(); // nomDuSite, longitudeDuSite, latitudeDuSite, fichierACharger 
            document.getElementById("mapinfo").innerHTML=infoSite;
            document.getElementById("datawind").innerHTML="pour un vent de direction <b>"+twd+"°</b>";
            getDataWindBoueeSite(twd,nomDuSite);        
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


/***********************************************
 * Récupère les données de position des bouées
 * du dossier ./data/
 **********************************************/



//-----------------
function proposeChoixWindData(){
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("tFichiers 2 : "+ tFichiers);
        document.getElementById("myListWindData").innerHTML=""; // Sinon les items fichiers sont en doublés 
                                                            // et je n'ai toujours pas compris pourquoi
        for (var i=0; i<tFichiers.length; i++){     
            //console.debug("tFichiers i:"+i+" => "+ tFichiers[i]);
            const listItem = document.createElement("li");   
            var b = document.createElement('button');
            b.setAttribute('name',tFichiers[i]);
            b.setAttribute('onclick','getThatData("'+encodeURI(tFichiers[i])+'");');
            b.textContent=tFichiers[i];
            // append the anchor to the dom element
            listItem.appendChild(b);
            myListWindData.appendChild(listItem);
        }
    }                     
}

// ----------------------- 
function ajax_GetDataWindSite(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        .then((data) => {
            //console.debug(data);
            if (data.ok==1){
                tFichiers.length=0;    // Evite le doublonnage dû à deux appels successifs rapprochés de l'appel Ajax !  
                for (const fichier of data.fichiers) {
                        //console.debug(fichier);
                        tFichiers.push(fichier);
                }  
                //console.debug("tFichiers : "+ tFichiers);
                proposeChoixWindData();
            }   
        })                          
        .catch(error => console.debug("Erreur : "+error));
    }
}

// Recherche les fichiers de forme "robonav_NomDuSite_45*.json" 
// ------------------------
function getDataWindBoueeSite(twd,site) {
    if ((site!==undefined) && (site.length>0)){
        //console.debug("Chargement du plan d'eau\n"+ nomDuSite);
        //console.debug("Fichier\n"+ fichierACharger);
        let expressionACharger= "robonav_"+site.replace(/\s+/g, '')+"_"+twd;       
        let url= url_serveur+'getdata.php';
        let myfile="expression="+expressionACharger;    
        //console.debug(expressionACharger);        
        ajax_GetDataWindSite(url, myfile);
    }        
}


//-----------------
function proposeChoixData(){    // Tous les fichiers de data sauf 45°
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("tFichiers 2 : "+ tFichiers);
        document.getElementById("myListData").innerHTML=""; // Sinon les items fichiers sont en doublés 
                                                            // et je n'ai toujours pas compris pourquoi
        for (var i=0; i<tFichiers.length; i++){     
            //console.debug("tFichiers i:"+i+" => "+ tFichiers[i]);
            const listItem = document.createElement("li");   
            var b = document.createElement('button');
            b.setAttribute('name',tFichiers[i]);
            b.setAttribute('onclick','getThatData("'+encodeURI(tFichiers[i])+'");');
            b.textContent=tFichiers[i];
            // apend the anchor to the dom element
            listItem.appendChild(b);
            myListData.appendChild(listItem);
        }
    }                     
}

// ----------------------- 
function ajax_GetDataSite(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        .then((data) => {
            //console.debug(data);
            if (data.ok==1){
                tFichiers.length=0;    // Evite le doublonnage dû à deux appels successifs rapprochés de l'appel Ajax !  
                for (const fichier of data.fichiers) {
                        //console.debug(fichier);
                        tFichiers.push(fichier);
                }  
                //console.debug("tFichiers : "+ tFichiers);
                proposeChoixData();
            }   
        })                          
        .catch(error => console.debug("Erreur : "+error));
    }
}


// Recherche les fichiers de forme "robonav_NomDuSite*.json" 
// ------------------------
function getDataBoueeSite(twd, site) {
    if ((site!==undefined) && (site.length>0)){
        //console.debug("Chargement du plan d'eau\n"+ nomDuSite);
        //console.debug("Fichier\n"+ fichierACharger);
        let expressionACharger= "robonav_"+site.replace(/\s+/g, '');       
        let url= url_serveur+'getdata.php';
        let myfile="expression="+expressionACharger+"&nottwd="+twd;    
        //console.debug(expressionACharger);        
        ajax_GetDataSite(url, myfile);
    }        
}


// ----------------------- 
function ajax_GetData(url, mystr){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        .then((data) => {
                siteCharge=data.site;
                twdChargee=data.twd;
                boueesFixesParcours=data.boueesfixes;
                addBoueesFixesParcours2Map();
                
                boueesMobiles=data.boueesmobiles;
                addBoueesMobiles2Map();
                document.getElementById("mapinfo").innerHTML=siteCharge+" TWD:"+ twdChargee;
/*
                if (boueesMobiles.length>0){
                    for (var item=0; item<boueesMobiles.length; item++){
                        console.debug("Données objet index "+item);
                        console.debug(boueesMobiles[item].id);
                        console.debug(boueesMobiles[item].lon);
                        console.debug(boueesMobiles[item].lat);
                        console.debug(boueesMobiles[item].color);
                        console.debug(boueesMobiles[item].fillcolor);
                    } 
                }    
*/               

                
            }
        )           
        .catch(error => console.debug("Erreur : "+error));
    }
}

    
// ----------------------- 
function getThatData(nf){  
    //console.debug("Chargement des bouées\n");
    //console.debug("Fichier\n"+ nf);
    boueesMobiles.length=0;
    boueesFixesParcours.length=0;
    var url= url_serveur+'getdata.php';
    let myfile="file="+nf;  
    ajax_GetData(url,  myfile);     
}


//--------------------------
function majDataBouees(){
    document.getElementById("datawind").innerHTML="pour un vent de direction <b>"+twd+"°</b>";
    getDataWindBoueeSite(twd,nomDuSite);
    document.getElementById("datanotwind").innerHTML="pour les autres directions du vent";
    getDataBoueeSite(twd,nomDuSite);
}

