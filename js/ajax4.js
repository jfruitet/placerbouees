// JavaScript Document
// ajax4.js
// Edition d'un fichier de position

let tFichiers=[]; // tableau des fichiers proposés au chargement

let boueesMobiles=[];   // Pour la map
let boueesFixesParcours=[]; // Pour la map

/***********************************************
 * Récupère les données de position des bouées
 * du dossier ./data/
 **********************************************/

//-----------------
function proposeChoixWindData(){
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("proposeChoixWindData :: tFichiers 2 : "+ tFichiers);
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
        let expressionACharger= "robonav_"+site.replace(/\s+|'/g, '')+"_"+twd;   
        //console.debug(expressionACharger);    
        let url= url_serveur+'getdata.php';
        let myfile="expression="+expressionACharger;    
        //console.debug(expressionACharger);        
        ajax_GetDataWindSite(url, myfile);
    }        
}


//-----------------
function afficheListeData(){    // Affiche la liste sans possibilité de choix
    if ((tFichiers !== undefined) && (tFichiers.length>0)){ 
        //console.debug("tFichiers 2 : "+ tFichiers);
        document.getElementById("myListData").innerHTML=""; // Sinon les items fichiers sont en doublés 
                                                            // et je n'ai toujours pas compris pourquoi
        for (var i=0; i<tFichiers.length; i++){     
            //console.debug("tFichiers i:"+i+" => "+ tFichiers[i]);
            document.getElementById("myListData").innerHTML+="<li>"+tFichiers[i]+"</li>\n";
        }
    }                     
}

// ----------------------- 
function ajax_GetDataSite(url, mystr, choix=true){ 
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
                afficheListeData();
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
        let expressionACharger= "robonav_"+site.replace(/\s+|'/g, '');       
        let url= url_serveur+'getdata.php';
        let myfile="expression="+expressionACharger+"&nottwd="+twd;    
        //console.debug(expressionACharger);        
        ajax_GetDataSite(url, myfile);
    }        
}


// Affichage des bouees sur la map
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
                
                if (data.boueesmobiles !== undefined){
                    boueesMobiles=data.boueesmobiles;
                    addBoueesMobiles2Map(); // Affichage des bouées sur la carte
                    document.getElementById("mapinfo").innerHTML=siteCharge+" TWD:"+ twdChargee;                    
                }
                else{
                    document.getElementById("mapinfo").innerHTML=siteCharge+": Données indisponibles pour  TWD:"+ twdChargee;
                }

                // Affichage des bouées mobiles sur le canvas
                if (boueesMobiles.length>0){
                    // Debug
                    /*
                    for (var item=0; item<boueesMobiles.length; item++){
                        console.debug("Données objet index "+item);
                        console.debug(boueesMobiles[item].id);
                        console.debug(boueesMobiles[item].lon);
                        console.debug(boueesMobiles[item].lat);
                        console.debug(boueesMobiles[item].color);
                        console.debug(boueesMobiles[item].fillcolor);
                    } 
                    */
                    drawAll2();
                }                                 
            }
        )           
        .catch(error => console.debug("Erreur : "+error));
    }
}

    
// Affiche la map pour ce fichier
// ----------------------- 
function getThatData(nf){  
    //console.debug("getThatData :: Chargement des bouées\n");
    //console.debug("Fichier\n"+ nf);
    document.getElementById("fileslist").style.display="none";
    deplacementBouees();   
    boueesMobiles.length=0;
    boueesFixesParcours.length=0;
    var url= url_serveur+'getdata.php';
    let myfile="file="+nf;  
    ajax_GetData(url,  myfile);     
}

// Affiche les fichiers disponibles pour le site donné
//--------------------------
function majDataBouees(){
    document.getElementById("datawind").innerHTML="pour un vent de direction <b>"+twd+"°</b>";
    //console.debug(nomDuSite);
    getDataWindBoueeSite(twd,nomDuSite);
    document.getElementById("datanotwind").innerHTML="pour les autres directions du vent";
    getDataBoueeSite(twd,nomDuSite);
}

