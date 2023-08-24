// JavaScript Document
// ajax.js
// la communication avec les serveurs

let myList = document.getElementById('mylist'); // La liste des sites à charger
let tFichiers=[]; // tableau des fichiers proposés au chargement


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
    document.getElementById("info").innerHTML=infoSite;
    // On peut lancer le chargement des données du site
    setSiteBoueesMobile(objSite.nom); 
}



/***********************************************
 * Récupère les données de position des bouées
 * du dossier ./data/
 **********************************************/



//-----------------
//function proposeChoixWindData(){
function afficheWindData(){
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("tFichiers 2 : "+ tFichiers);
        document.getElementById("myListWindData").innerHTML=""; // Sinon les items fichiers sont en doublés 
                                                            // et je n'ai toujours pas compris pourquoi
        for (var i=0; i<tFichiers.length; i++){     
            //console.debug("tFichiers i:"+i+" => "+ tFichiers[i]);
            const listItem = document.createElement("li");   
            var t = tFichiers[i];
            // append the anchor to the dom element
            listItem.appendChild(t);
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
                afficheWindData();
            }   
        })                          
        .catch(error => console.debug("Erreur : "+error));
    }
}


//-----------------
// function proposeChoixData(){    // Tous les fichiers de data
function afficheDataDispo(){    // Tous les fichiers de data
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("tFichiers 2 : "+ tFichiers);
        document.getElementById("myListData").innerHTML=""; // Sinon les items fichiers sont en doublés 
                                                            // et je n'ai toujours pas compris pourquoi
        for (var i=0; i<tFichiers.length; i++){     
            //console.debug("tFichiers i:"+i+" => "+ tFichiers[i]);
            document.getElementById("myListData").innerHTML+="<li>"+tFichiers[i]+"</li>";
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
                //proposeChoixData();
                afficheDataDispo();
            }   
        })                          
        .catch(error => console.debug("Erreur : "+error));
    }
}


// Recherche les fichiers de forme "robonav_NomDuSite*.json" 
// ------------------------
function getDataSite(site) {
    if ((site!==undefined) && (site.length>0)){
        //console.debug("Chargement du plan d'eau\n"+ nomDuSite);
        //console.debug("Fichier\n"+ fichierACharger);
        let expressionACharger= "robonav_"+site.replace(/\s+|'/g, '');       
        let url= url_serveur+'getdata.php';
        let myfile="expression="+expressionACharger;    
        //console.debug(expressionACharger);        
        ajax_GetDataSite(url, myfile);
    }        
}

/*
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
                
                if (mode_debug===true){
                    // Test1 : fantôme du parcours
                    if (data.fantome !== undefined){
                        //console.debug(data.fantome);
                        addFantomeParcours2Map(data.fantome);
                    }
                    
                    // Test2
                    if (data.rectangle !== undefined){
                        //console.debug(data.rectangle);
                        addRectangleParcours2Map(data.rectangle);
                    }
                }
                
                boueesMobiles=data.boueesmobiles;
                addBoueesMobiles2Map();
                document.getElementById("mapinfo").innerHTML=siteCharge+" TWD:"+ twdChargee;               
            }
        )           
        .catch(error => console.debug("Erreur : "+error));
    }
}
*/
    
// ----------------------- 
function setThatData(nf){  
    //console.debug("Chargement des bouées\n");
    //console.debug("Fichier\n"+ nf);
    var url= url_serveur+'placer_bouees.php';
    let myfile="file="+nf;  
    ajax_SetData(url,  myfile);     
}


//--------------------------
function majDataBouees(){
    //console.debug(nomDuSite);
    getDataSite(nomDuSite);
}


// ----------------------- 
function ajax_SetSite(url, mystr, nomDuSite){ 
    if ((url !== undefined) && (url.length>0) && (mystr !== undefined) && (mystr.length>0)){        
        // GET avec fetch()
        fetch(url+"?"+mystr, myInitGet)
        .then(response => response.json()) 
        .then((data) => {
            //console.debug(data);
            if (data.ok==1){
                console.debug("Traitement "+data.site+" OK");  
                getDataSite(nomDuSite);              
            }   
        })                          
        .catch(error => console.debug("Erreur : "+error));
    }
}
//-----------------------
function setSiteBoueesMobile(site){
    document.getElementById("myListData").innerHTML=""; 
    console.debug("Lancement du programme de génération des fichiers robonav_site_twd_date.json");
    if ((site!==undefined) && (site.length>0)){
        console.debug("Génération pour le du plan d'eau\n"+ site);    
        var url= url_serveur+'setsite.php';
        var mystr="site="+encodeURI(site);    
        ajax_SetSite(url, mystr, site);
    }       

} 

